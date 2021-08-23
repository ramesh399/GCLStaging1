<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditReportClientInformationProcess;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditClientInformationProcessController implements the CRUD actions for Product model.
 */
class AuditClientInformationProcessController extends \yii\rest\Controller
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
	
	public function actionGetProcess()
    {
		$post = yii::$app->request->post();
		$data = [];
		$data['app_id']= isset($post['app_id'])?$post['app_id']:'';
		$data['audit_id']= isset($post['audit_id'])?$post['audit_id']:'';
		if(!Yii::$app->userrole->canViewAuditReport($data)){
			return false;
		}


		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		//$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];

		$sufficient_access = 1;
		if($user_type=='2' || $user_type=='3'){
			$sufficient_access = 0;
		}
		if(!isset($post['audit_id']) || $post['audit_id']<=0){
			$sufficient_access = 0;
		}

		$processmodel = new AuditReportClientInformationProcess();
		$model = AuditReportClientInformationProcess::find();//->where(['audit_id'=>$post['audit_id']]);
		if(isset($post['audit_id']) && $post['audit_id']>0){
			//$model = $model->andWhere(['audit_id'=>$post['audit_id'] ]);
		}
		if(isset($post['unit_id']) && $post['unit_id']>0){
			$model = $model->andWhere(['unit_id'=>$post['unit_id']]);
		}
		
		$supplier_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $value)
			{
				$data=array();
				$data['id']=$value->id;
				$data['audit_id']=$value->audit_id;
				$data['process']=$value->process;
				$data['sufficient']=$value->sufficient;
				$data['description']=$value->description;
				$data['sufficient']=$value->sufficient;
				$data['sufficient_label']=$value->sufficient?$processmodel->arrSufficient[$value->sufficient]:'';
				$data['created_at']=date($date_format,$value->created_at);
				$data['created_by_label']=$value->createdbydata->first_name.' '.$value->createdbydata->last_name;
				$supplier_list[]=$data;
			}
		}

		return ['processes'=>$supplier_list,'sufficient_access'=>$sufficient_access];
    }
	

	public function actionCreate()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		
		if($data)
		{	

			if(!Yii::$app->userrole->canEditAuditReport($data)){
				return false;
			}

			if(isset($data['id']))
			{
				$model = AuditReportClientInformationProcess::find()->where(['id' => $data['id']])->one();
				if($model===null)
				{
					$model = new AuditReportClientInformationProcess();
					$model->created_by = $userData['userid'];
					$model->unit_id = $data['unit_id'];
					$model->app_id = $data['app_id'];
				}
				else
				{
					$model->updated_by = $userData['userid'];
				}
			}
			else
			{
				$model = new AuditReportClientInformationProcess();
				$model->created_by = $userData['userid'];
				$model->unit_id = $data['unit_id'];
				$model->app_id = $data['app_id'];
			}
			if(isset($data['audit_id']) && $data['audit_id']>0){
				$model->audit_id = $data['audit_id'];
			}
			$model->process = $data['process'];
			$model->description = $data['description'];			
			$model->sufficient = $data['sufficient'];

			
			if($model->validate() && $model->save())
			{	
				if(isset($data['id']) && $data['id']!='')
				{
					$responsedata=array('status'=>1,'message'=>'Client Information Process has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Client Information Process has been created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}


	public function actionChangeSufficient()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{

			$model = AuditReportClientInformationProcess::find()->where(['id' => $data['id']])->one();
			if($model !== null)
			{
				$data['app_id']= $model->app_id;
				if($model->audit_id !=''){
					$data['audit_id']= $model->audit_id;
				}else{
					$model->audit_id = isset($data['audit_id'])?$data['audit_id']:'';
				}
				
				if(!Yii::$app->userrole->canEditAuditReport($data,'sufficient')){
					return false;
				}

				$model->sufficient = $data['sufficient'];
				if($model->validate() && $model->save())
				{
					$responsedata=array('status'=>1,'message'=>'Sufficient Changed successfully');
				}
			}
		}		
		return $this->asJson($responsedata);
	}


	
	public function actionDeleteData()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{
			$modelchk = AuditReportClientInformationProcess::find()->where(['id' => $data['id']])->one();
			if($modelchk!== null){
				$data['app_id']= $modelchk->app_id;
				if($modelchk->audit_id !=''){
					$data['audit_id']= $modelchk->audit_id;
				}
				if(!Yii::$app->userrole->canEditAuditReport($data)){
					return false;
				}	
				$model = AuditReportClientInformationProcess::deleteAll(['id' => $data['id']]);
				$responsedata=array('status'=>1,'message'=>'Deleted successfully');
			}
		}
		return $this->asJson($responsedata);
	}

	
}
