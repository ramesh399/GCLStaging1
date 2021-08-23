<?php
namespace app\modules\application\controllers;

use Yii;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ClientLogoRequest;
use app\modules\application\models\ClientLogoRequestCustomerChecklistComment;
use app\modules\master\models\User;
use app\modules\master\models\Standard;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class ClientlogoRequestController extends \yii\rest\Controller
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
		
		$model = ClientLogoRequest::find();
		
		if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
		{
			$model = $model->andWhere(['standard_id'=> $post['standardFilter']]);
		}

		$model = $model->groupBy(['id']);
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 'name', $searchTerm],
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
		
		$company_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $question)
			{
				$data=array();
				$data['id']=$question->id;
				$data['company_name']=$question->currentaddress->company_name;
				$data['standard_label']=$question->standard->code;
				$data['status_label']=$question->arrStatus[$question->status];
				$data['created_at']=date($date_format,$question->created_at);
				$company_list[]=$data;
			}
		}

		return ['requests'=>$company_list,'total'=>$totalCount];
	}
	
	public function actionGetChecklist()
	{
		$responsedata=array('status'=>0,'message'=>'Question not found');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        if (Yii::$app->request->post()) 
		{
			$result = array();
			$data = Yii::$app->request->post();
			$userData = Yii::$app->userdata->getData();

			$model = ClientLogoRequest::find()->where(['id' => $data['id']])->orderBy(['id' => SORT_DESC])->one();
			if ($model !== null)
			{
				$result['app_id'] = $model->app_id;
				$result['company_name']=$model->currentaddress->company_name;
				$result['standard_label']=$model->standard->code;
				$result['status_label']=$model->arrStatus[$model->status];
				$result['created_at']=date($date_format,$model->created_at);
				$result['standard_id'] = $model->standard_id;

				$reviewcommentarr=[];
				$appReview=$model->customerchecklist;
				if(count($appReview)>0)
				{
					foreach($appReview as $reviewComment)
					{
						$reviewcommentarr[]=array(
							'id'=>$reviewComment->id,
							'client_logo_request_id'=>$reviewComment->client_logo_request_id,
							'question_id'=>$reviewComment->question_id,
							'question'=>$reviewComment->question,
							'file_name'=>$reviewComment->file_name,
							'comment'=>$reviewComment->comment
						);
					}	
				}
				$result['questions'] = $reviewcommentarr;
				
				return $result;
			}


		}
		//return $responsedata;
	}
	
	public function actionGetAppdata()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];

		$apparr = Yii::$app->globalfuns->getAppList();
		$responsedata=array('status'=>1,'appdata'=>$apparr);
		return $this->asJson($responsedata);
	}

    public function actionGetAppstddata()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$ModelApplicationStandard = new ApplicationStandard();
			$appstdmodelarr = array();
			$appstdmodel = ApplicationStandard::find()->select('standard_id')->where(['app_id' => $data['id']]);
			$appstdmodel = $appstdmodel->andWhere('standard_status not in('.$ModelApplicationStandard->arrEnumStatus['declined'].','.$ModelApplicationStandard->arrEnumStatus['cancellation'].','.$ModelApplicationStandard->arrEnumStatus['withdrawn'].')');
			$appstdmodel = $appstdmodel->all();
			if(count($appstdmodel)>0)
			{
				foreach($appstdmodel as $appstd)
				{
					$appstdmodelarr[] = $appstd->standard_id;
				}
			}
			$stdarr = array();
			$stdmodelarr = Standard::find()->where(['status' => 0])->all();
			if(count($stdmodelarr)>0)
			{
				foreach($stdmodelarr as $std)
				{
					if (!in_array($std->id, $appstdmodelarr))
					{
						$stdarr[] = ['id'=> $std->id, 'name' => $std->code];
					}
					
				}
			}
			
			$responsedata=array('status'=>1,'stdlist'=>$stdarr);
		}
		return $this->asJson($responsedata);
	}

	public function actionCreate()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$clientlogorequest = new ClientLogoRequest();
			$clientlogorequest->app_id = $data['company_id'];
			$clientlogorequest->standard_id = $data['standard_id'];
			if($clientlogorequest->validate() && $clientlogorequest->save())
			{
				$responsedata=array('status'=>1,'message'=>'Saved successfully','id'=>$clientlogorequest->id);
			}
		}
		return $this->asJson($responsedata);
	}

	public function actionSaveChecklist()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$target_dir = Yii::$app->params['customer_clientlogo_checklist_file'];
		$datapost = Yii::$app->request->post();
		if ($datapost) 
		{	
			$data = json_decode($datapost['formvalues'],true);
			$qts = $data['questions'];
			if(is_array($qts) && count($qts)>0)
			{
				$model = ClientLogoRequestCustomerChecklistComment::find()->where(['client_logo_request_id' => $data['client_logo_request_id']])->one();
				if($model!==null)
				{
					ClientLogoRequestCustomerChecklistComment::deleteAll(['client_logo_request_id' => $model->id]);
				}
				
				foreach($qts as $question)
				{
					$model = new ClientLogoRequestCustomerChecklistComment();
					$model->client_logo_request_id = $data['client_logo_request_id'];
					$model->question_id = $question['question_id'];
					$model->question = $question['question'];
					$model->comment = $question['comment'];

					if(isset($_FILES['questionfile']['name'][$question['question_id']]))
					{								
						$tmp_name = $_FILES['questionfile']["tmp_name"][$question['question_id']];
						$name = $_FILES['questionfile']['name'][$question['question_id']];
						$model->file_name = Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
					}
					else
					{
						$model->file_name = $question['file_name'];
					}
					$model->save();
					
				}
				$responsedata=array('status'=>1,'message'=>'Saved successfully');
			}
		}
		return $responsedata;
	}

	public function actionChecklistfile(){
		$data = Yii::$app->request->post();
		//$data['id'];
		$files = ClientLogoRequestCustomerChecklistComment::find()->where(['id'=>$data['id']])->one();

		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['customer_clientlogo_checklist_file'].$files->file_name;
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

}
