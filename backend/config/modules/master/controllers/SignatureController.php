<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\Signature;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class SignatureController extends \yii\rest\Controller
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
		
		$logomodel = new Signature();
		$model = Signature::find()->where(['status'=>0]);
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
			$currentAction = 'signature_master';				
			
			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}

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
				$data['title']=$question->title;
				$data['logo']=$question->logo;
				$data['description']=$question->description;
				$data['status']=$question->status;
				$data['created_at']=date($date_format,$question->created_at);
				$logo_list[]=$data;
			}
		}

		return ['signatures'=>$logo_list,'total'=>$totalCount];
	}
	
	public function actionCreate()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$datapost = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData(); 
		$target_dir = Yii::$app->params['signature_files']; 
		
		if($datapost){

			$data =json_decode($datapost['formvalues'],true);
		
			if(isset($data['id']))
			{
				$model = Signature::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new Signature();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new Signature();
				$model->created_by = $userData['userid'];				
			}

			$currentAction = 'add_signature';
			if(isset($data['id']) && $data['id']!='')
			{
				$currentAction = 'edit_signature';
			}

			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}


			$model->title = $data['title'];	
			$model->description = $data['description'];		
			if(isset($_FILES['logo']['name']))
			{
				if($model->logo != ''){
					Yii::$app->globalfuns->removeFiles($model->logo,$target_dir);
				}

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
					$responsedata=array('status'=>1,'message'=>'Signature has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Signature has been created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}

	public function actionSignaturefile(){
		$data = Yii::$app->request->post();
		$files = Signature::find()->where(['id'=>$data['id']])->one();
		//if($data['filetype']=='gisfile'){
			$filename = $files->logo;
		//}
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['signature_files'].$filename;
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

	public function actionDeletesignaturedata()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			// $model = Signature::deleteAll(['id' => $data['id']]);
			$model = Signature::find()->where(['id' => $data['id']])->one();
			if($model!==null)
			{
				$currentAction = 'delete_signature';

				if(!Yii::$app->userrole->hasRights(array($currentAction)))
				{
					return false;
				}

				$model->status = 2;
				if($model->validate() && $model->save())
				{
					$responsedata=array('status'=>1,'message'=>'Deleted successfully');
				}
			}
		}
		return $this->asJson($responsedata);
	}

}
