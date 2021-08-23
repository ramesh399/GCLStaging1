<?php
namespace app\modules\application\controllers;

use Yii;
use app\modules\application\models\ClientLogo;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class LogoController extends \yii\rest\Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {

        return [
			[
				'class' => \yii\filters\ContentNegotiator::className(),
				//'only' => ['index', 'view'],
				'formats' => [
					'application/json' => \yii\web\Response::FORMAT_JSON,
				],
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
			],
			'authenticator' => ['class' => JwtHttpBearerAuth::class ]
		];        
    }
	
	

    public function actionIndex()
    {
		$post = yii::$app->request->post();
		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		$logomodel = new ClientLogo();
		$model = ClientLogo::find()->where(['<>','status',2]);
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 'title', $searchTerm],
					['like', '(date_format(FROM_UNIXTIME(`created_at` ), \'%b %d, %Y\' ))', $searchTerm],
				]);

				$totalCount = $model->count();
			}
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
			}
			else
			{
				$model = $model->orderBy(['created_at' => SORT_DESC]);
			}
			



            $model = $model->limit($pageSize)->offset($page);
		}
		else
		{
			$totalCount = $model->count();
		}
		
		$logo_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $question)
			{
				$data=array();
				$data['id']=$question->id;
				$data['customer_id']=$question->customer_id;
				$data['title']=$question->title;
				$data['logo']=$question->logo;
				$data['description']=$question->description;
				$data['status']=$question->status;
				$data['created_at']=date($date_format,$question->created_at);
				$logo_list[]=$data;
			}
		}

		return ['logos'=>$logo_list,'total'=>$totalCount];
	}
	
	public function actionCreate()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$datapost = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData(); 
		$target_dir = Yii::$app->params['library_files']."client_logos/"; 
		
		if($datapost){

			$data =json_decode($datapost['formvalues'],true);
		

			if(isset($data['id']))
			{
				$model = ClientLogo::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new ClientLogo();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new ClientLogo();
				$model->created_by = $userData['userid'];
				$model->customer_id = $userData['userid'];
			}

			
			$model->title = $data['title'];	
			$model->description = $data['description'];		
			if(isset($_FILES['logo']['name']))
			{
				$tmp_name = $_FILES["logo"]["tmp_name"];
				$name = $_FILES["logo"]["name"];
				$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
			}else{
				$filename = $data['logo'];
			}
			$model->logo = $filename;
			
			if($model->validate() && $model->save())
			{	
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Logo has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Logo has been created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}

	public function actionLogofile(){
		$data = Yii::$app->request->post();
		$files = ClientLogo::find()->where(['id'=>$data['id']])->one();
		//if($data['filetype']=='gisfile'){
			$filename = $files->logo;
		//}
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['library_files']."client_logos/".$filename;
		if(file_exists($filepath)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
			header('Access-Control-Expose-Headers: Content-Length,Content-Disposition,filename,Content-Type;');
			header('Access-Control-Allow-Headers: Content-Length,Content-Disposition,filename,Content-Type');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($filepath));
			flush(); // Flush system output buffer
			readfile($filepath);
		}
		die;
	}

	public function actionSendtoapprove()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$model = ClientLogo::find()->where(['id' => $data['id']])->one();
			if($model!==null)
			{
				$model->status = 1;
				$model->save();

				$responsedata=array('status'=>1,'message'=>'Sent for Approval Successfully');
			}
		}
		return $this->asJson($responsedata);
	}

	public function actionDeletelogodata()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$model = ClientLogo::deleteAll(['id' => $data['id']]);
			$responsedata=array('status'=>1,'message'=>'Deleted successfully');
		}
		return $this->asJson($responsedata);
	}

}
