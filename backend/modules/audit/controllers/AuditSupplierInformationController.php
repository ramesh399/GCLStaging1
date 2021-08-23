<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditReportApplicableDetails;
use app\modules\audit\models\AuditReportClientInformationSupplierInformation;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditSupplierInformationController implements the CRUD actions for Product model.
 */
class AuditSupplierInformationController extends \yii\rest\Controller
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
	
	public function actionGetSupplierInformation()
    {
		$post = yii::$app->request->post();
		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		$suppliermodel = new AuditReportClientInformationSupplierInformation();
		$model = AuditReportClientInformationSupplierInformation::find();

		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];

		$data = [];
		$data['app_id']= isset($post['app_id'])?$post['app_id']:'';
		$data['audit_id']= isset($post['audit_id'])?$post['audit_id']:'';
		if(!Yii::$app->userrole->canViewAuditReport($data)){
			return false;
		}
		
		$sufficient_access = 1;
		if($user_type=='2' || $user_type=='3'){
			$sufficient_access = 0;
		}
		if(!isset($post['audit_id']) || $post['audit_id']<=0){
			$sufficient_access = 0;
		}

		if(isset($post['audit_id']) && $post['audit_id']>0){
			//$model = $model->andWhere(['audit_id'=>$post['audit_id'] ]);
		}
		if(isset($post['app_id']) && $post['app_id'] !=''){
			$model = $model->andWhere(['app_id'=>$post['app_id'] ]);
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
				$data['supplier_name']=$value->supplier_name;
				$data['supplier_address']=$value->supplier_address;
				$data['products_composition']=$value->products_composition;
				//$data['validity']=$value->validity;
				//$data['available_in_gots_database']=$value->available_in_gots_database;
				//$data['is_applicable']=$value->is_applicable!=0?$value->is_applicable:'';
				//$data['is_applicable_label'] = $value->is_applicable!=0?$suppliermodel->arrApplicable[$value->is_applicable]:'';
				$data['sufficient']=$value->sufficient;
				//$data['available_label'] = $suppliermodel->arrAvailable[$value->available_in_gots_database];
				$data['sufficient_label'] = $value->sufficient?$suppliermodel->arrSufficient[$value->sufficient]:'';
				$data['created_at']=date($date_format,$value->created_at);
				$data['created_by_label']=$value->createdbydata->first_name.' '.$value->createdbydata->last_name;
				$supplier_list[]=$data;
			}
		}

		return ['suppliers'=>$supplier_list,'sufficient_access'=>$sufficient_access];
	}
	
	public function actionGetOptionList()
    {
		$suppliermodel = new AuditReportClientInformationSupplierInformation();
		return ['availablelist'=>$suppliermodel->arrAvailable,'applicablelist'=>$suppliermodel->arrApplicable,'sufficientlist'=>$suppliermodel->arrSufficient];
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
				$model = AuditReportClientInformationSupplierInformation::find()->where(['id' => $data['id']])->one();
				if($model===null)
				{
					$model = new AuditReportClientInformationSupplierInformation();
					$model->created_by = $userData['userid'];
				}
				else
				{
					$model->updated_by = $userData['userid'];
				}
			}
			else
			{
				$model = new AuditReportClientInformationSupplierInformation();
				$model->created_by = $userData['userid'];
			}

			$model->audit_id = isset($data['audit_id'])?$data['audit_id']:'';
			$model->unit_id = isset($data['unit_id'])?$data['unit_id']:'';
			if(isset($data['app_id'])){
				$model->app_id = $data['app_id'];
			}
			$model->supplier_name = $data['supplier_name'];
			$model->supplier_address = $data['supplier_address'];
			$model->products_composition = $data['products_composition'];
			//$model->validity = $data['validity'];
			//$model->is_applicable = $data['is_applicable'];
			//$model->available_in_gots_database = $data['available_in_gots_database'];
			$model->sufficient = $data['sufficient'];
			
			
			
			if($model->validate() && $model->save())
			{	
				
				$model = AuditReportApplicableDetails::find()->where(['app_id'=> $data['app_id']])->andWhere(['report_name'=> $data['type']])->andWhere(['status'=> 2])->one();
				if($model !== null)
				{
					$model->status = 1;
					$model->comments = '';
					$model->save();
				}
				
				if(isset($data['audit_id']) && $data['audit_id']!='')
				{
					$responsedata=array('status'=>1,'message'=>'Supplier Information has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Supplier Information has been created successfully');
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

			$model = AuditReportClientInformationSupplierInformation::find()->where(['id' => $data['id']])->one();
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
			$modelchk = AuditReportClientInformationSupplierInformation::find()->where(['id' => $data['id']])->one();
			if($modelchk!== null){
				$data['app_id']= $modelchk->app_id;
				if($modelchk->audit_id !=''){
					$data['audit_id']= $modelchk->audit_id;
				}
				if(!Yii::$app->userrole->canEditAuditReport($data)){
					return false;
				}
	
				$model = AuditReportClientInformationSupplierInformation::deleteAll(['id' => $data['id']]);
				$responsedata=array('status'=>1,'message'=>'Deleted successfully');
			}
			
		}
		return $this->asJson($responsedata);
	}

	
}
