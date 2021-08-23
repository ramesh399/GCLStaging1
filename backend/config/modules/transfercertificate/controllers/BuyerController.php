<?php
namespace app\modules\transfercertificate\controllers;

use Yii;
use app\modules\transfercertificate\models\Buyer;
use app\modules\transfercertificate\models\Transport;
use app\modules\transfercertificate\models\TcIfoamStandard;

use app\modules\application\models\Application;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * BuyerController implements the CRUD actions for Product model.
 */
class BuyerController extends \yii\rest\Controller
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
		$modelObj = new Buyer();		
		if($post)
		{
			if(!Yii::$app->userrole->isCustomer() && !Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->hasRights([$post['type']]))
			{
				return false;
			}

			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$is_headquarters =$userData['is_headquarters'];
			$franchiseid=$userData['franchiseid'];
			$role_chkid=$userData['role_chkid'];
			$BuyerModel = new Buyer();
			$model = Buyer::find()->where(['!=', 't.status',$BuyerModel->enumStatus['archived']])->alias('t');	
			//$model = Createdbydata
			$model = $model->innerJoinWith(['createdbydata as createdbydata']);	
			if($resource_access != '1')
			{
				if($user_type== Yii::$app->params['user_type']['customer']){
					$model = $model->andWhere('t.created_by="'.$userid.'"');
				}	
				if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1 ){
					$model = $model->andWhere(' createdbydata.franchise_id="'.$franchiseid.'" ');
				}
			}
			if(isset($post['type']) && $post['type'] !='')
			{
				$model = $model->andWhere(['t.type'=> $post['type']]);				
			}
			if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
			{
				$page = ($post['page'] - 1)*$post['pageSize'];
				$pageSize = $post['pageSize']; 
				$typearray=array_map('strtolower', $modelObj->arrType);
				$statusarray=array_map('strtolower', $modelObj->arrStatus);
				if(isset($post['statusFilter']) && $post['statusFilter']>='0')
				{
					$model->andWhere(['t.status'=> $post['statusFilter']]);
				}
				if(isset($post['searchTerm']))
				{
					$searchTerm = $post['searchTerm'];					
					$search_status = array_search(strtolower($searchTerm),$statusarray);
					
					if($search_status===false)
					{
						$search_status='';
					}
					
					$model = $model->andFilterWhere([
						'or',
						['like', 't.name', $searchTerm],
						['like', 't.client_number', $searchTerm],
						['like', 't.address', $searchTerm],
						['like', 't.city', $searchTerm],
						['like', 'date_format(FROM_UNIXTIME(t.`created_at` ), \'%b %d, %Y\' )', $searchTerm],
						['status'=>$search_status]
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
					$model = $model->orderBy(['t.created_at' => SORT_DESC]);
				}
				
				$model = $model->limit($pageSize)->offset($page);
			}
			else
			{
				$totalCount = $model->count();
			}
			
			$list=array();
			$model = $model->all();		
			if(count($model)>0)
			{
				foreach($model as $modelData)
				{	
					$data=array();
					$data['id']=$modelData->id;
					$data['name']=$modelData->name;
					$data['client_number']=$modelData->client_number?$modelData->client_number:'';
					$data['address']=$modelData->address;
					$data['email']=$modelData->email;

					$data['country_id']=$modelData->country_id;
					$data['state_id']=$modelData->state_id;
					$data['zipcode']=$modelData->zipcode;
					
					$data['country_id_label']=($modelData->country)?$modelData->country->name:'';
					$data['state_id_label']=($modelData->state)?$modelData->state->name:'';

					$data['phonenumber']=$modelData->phonenumber;
					$data['city']=$modelData->city;					
					$data['created_by_label']=$modelData->createdbydata->first_name.' '.$modelData->createdbydata->last_name;					
					$data['status']=$modelData->status;
					$data['status_label']=$modelObj->arrStatus[$modelData->status];
					$data['created_at']=date($date_format,$modelData->created_at);				

					$list[]=$data;
				}
			}
		}
		return ['buyer'=>$list,'total'=>$totalCount, 'typelist'=>$modelObj->arrType, 'statuslist'=>$modelObj->arrStatus];
    }

    public function actionCreate()
	{			
		$modelBuyer = new Buyer();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		//$data = Yii::$app->request->post();
		$datapost = Yii::$app->request->post();
		if ($datapost) 
		{	
			$data =json_decode($datapost['formvalues'],true);			
			if(isset($data['id']) && Yii::$app->userrole->isUser())
			{
				if($data['type']=='consignee')
				{
					if(!Yii::$app->userrole->hasRights(['edit_consignee']))
					{
						return false;
					}
				}elseif($data['type']=='buyer'){
					if(!Yii::$app->userrole->hasRights(['edit_buyer']))
					{
						return false;
					}
				}			
			}elseif(!Yii::$app->userrole->isCustomer()){			
				return false;
			}
			
			$userData = Yii::$app->userdata->getData();
			$editStatus=1;

			if(isset($data['id']) && $data['id']>0)
			{
				$model = Buyer::find()->where(['id' => $data['id']]);				
				$franchiseid=$userData['franchiseid'];
				$userid=$userData['userid'];
				if(Yii::$app->userrole->isOSSUser())
				{
					$model = $model->innerJoinWith(['createdbydata as createdbydata']);	
					$model = $model->andWhere(' createdbydata.franchise_id="'.$franchiseid.'" ');
				}elseif(Yii::$app->userrole->isCustomer())
				{
					$model = $model->andWhere(['created_by' => $userid]);
				}
				$model=$model->one();
				if($model===null)
				{
					$model = new Buyer();
					$editStatus=0;
					$model->created_by = $userData['userid'];	
				}else{
					$model->updated_by = $userData['userid'];	
				}
			}else{
				$editStatus=0;
				$model = new Buyer();
				$model->created_by = $userData['userid'];	
			}	
			
			$model->name = $data['name'];	
			$model->client_number = isset($data['client_number'])?$data['client_number']:'';	
			$model->address = $data['address'];	
			$model->email = $data['email'];	

			$model->country_id = $data['country_id'];	
			$model->state_id = $data['state_id'];	
			$model->zipcode = $data['zipcode'];	

			$model->phonenumber = $data['phonenumber'];	
			$model->city = $data['city'];
			$model->type = $data['type'];		
			
					
			if($model->validate() && $model->save())
			{	
				$manualID = $model->id;
								
				$userMessage = $modelBuyer->arrTypeData[$model->type].' has been created successfully';
				if($editStatus==1)
				{
					$userMessage = $modelBuyer->arrTypeData[$model->type].' has been updated successfully';
				}				
				$responsedata=array('status'=>1,'message'=>$userMessage);	
			}else{
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}		
		}
		return $this->asJson($responsedata);
	}

	public function actionGetData()
	{
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];
		$role_chkid=$userData['role_chkid'];

		$modelBuyer = [];
		$modelSeller = [];
		$modelConsignee = [];

		$data = Yii::$app->request->post();

		if($user_type ==1 || $user_type==3){
			$userid = 0;
			if(isset($data['app_id']) && $data['app_id'] !='' ){
				$Application = Application::find()->where(['id'=>$data['app_id']])->one();
				$userid = $Application->customer_id;
			}
		}
		
		$Buyer = new Buyer();
		$modelBuyer = Buyer::find()->select('id,name')->alias('t')->where(['type' => 'buyer','created_by'=>$userid,'status'=>$Buyer->enumStatus['approved'] ]);
		//$modelSeller = Buyer::find()->select('id,name')->alias('t')->where(['type' => 'seller','created_by'=>$userid]);
		$modelConsignee = Buyer::find()->select('id,name')->alias('t')->where(['type' => 'consignee','created_by'=>$userid,'status'=>$Buyer->enumStatus['approved'] ]);

		$modelIfoamStandard = TcIfoamStandard::find()->select('id,name');

		//$modelBuyer = Buyer::find()->select('id,name')->alias('t')->where(['type' => 'buyer','created_by'=>$userid,'status'=>$Buyer->enumStatus['approved'] ]);
		$modelBuyerConsignee = Buyer::find()->select("id,name,city")->alias('t')->where(['created_by'=>$userid,'status'=>$Buyer->enumStatus['approved'] ])->all();
		if($resource_access != '1')
		{
			/*if($user_type== Yii::$app->params['user_type']['customer']){
				$modelBuyer = $modelBuyer->andWhere('t.created_by="'.$userid.'"');
				$modelSeller = $modelSeller->andWhere('t.created_by="'.$userid.'"');
				$modelConsignee = $modelConsignee->andWhere('t.created_by="'.$userid.'"');
			}	
			*/
		}
		$modelBuyer = $modelBuyer->asArray()->all();
		$modelIfoamStandard = $modelIfoamStandard->asArray()->all();
		//$modelSeller = $modelSeller->asArray()->all();
		$modelConsignee = $modelConsignee->asArray()->all();

		$modelBuyerConsigneedata = [];
		if(count($modelBuyerConsignee)>0){
			foreach($modelBuyerConsignee as $buyerconsingeedata){
				$modelBuyerConsigneedata[] = [
					'id' => $buyerconsingeedata->id,
					'name' => $buyerconsingeedata->name.' - '.$buyerconsingeedata->city
				];
			}
		}
		
		$modelTransport = Transport::find()->select('id,name')->asArray()->all();
		return['sellerlist'=>[],'buyerlist'=>$modelBuyer,'consigneelist'=>$modelConsignee,'buyerconsigneelist'=>$modelBuyerConsigneedata,'transportlist'=>$modelTransport,'ifoamstdlist'=>$modelIfoamStandard];
	}
	
	
	public function getaccessdata(){
		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		
		$resource_access=$userData['resource_access'];
		$accessdata = [];
		$data = Yii::$app->request->post();


		if($resource_access ==1){
			$accessdata['canEditData'] =1;
			$accessdata['canDeleteData'] =1;
			$accessdata['canAddData'] =1;
		}else{
			if(isset($data['type']) && $data['type'] == 'buyer'){
				if(in_array('edit_buyer',$rules)){
					$accessdata['canEditData'] =1;
					$accessdata['canDeleteData'] =1;
					$accessdata['canAddData'] =1;
				}
				if(in_array('view_buyer',$rules)){
	
				}
				if(in_array('delete_buyer',$rules)){
	
				}
			}
			
		}


	}


	public function actionView()
    {
		$data = Yii::$app->request->post();
		if($data)
		{
			if(!Yii::$app->userrole->isCustomer() && !Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->hasRights([$data['type']]) )
			{
				return false;
			}
			
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			$modelObj = new Buyer();

			$model = Buyer::find()->where(['id' => $data['id'],'type'=>$data['type']]);		
			$userData = Yii::$app->userdata->getData();
			$franchiseid=$userData['franchiseid'];
			$userid=$userData['userid'];
			if(Yii::$app->userrole->isOSSUser())
			{
				$model = $model->innerJoinWith(['createdbydata as createdbydata']);	
				$model = $model->andWhere(' createdbydata.franchise_id="'.$franchiseid.'" ');
			}elseif(Yii::$app->userrole->isCustomer())
			{
				$model = $model->andWhere(['created_by' => $userid]);
			}		
			$model = $model->one();
			if ($model !== null)
			{
				$resultarr=array();
				$resultarr["id"]=$model->id;
				$resultarr["name"]=$model->name;
				$resultarr["address"]=$model->address;
				$resultarr["client_number"]=$model->client_number?$model->client_number:'';
				$resultarr['email']=$modelData->email;
				$resultarr['phonenumber']=$modelData->phonenumber;
				$resultarr["city"]=$model->city;			
				return ['data'=>$resultarr];
			}
		}
	}   

    protected function findModel($id)
    {
        if (($model = Buyer::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	public function actionDeletedata()
	{				
		$data = Yii::$app->request->post();
		if($data && isset($data['id']))
		{	
			$id = $data['id'];
			if(Yii::$app->userrole->isUser())
			{
				if($data['type']=='consignee')
				{
					if(!Yii::$app->userrole->hasRights(['delete_consignee']))
					{
						return false;
					}
				}elseif($data['type']=='buyer'){
					if(!Yii::$app->userrole->hasRights(['delete_buyer']))
					{
						return false;
					}
				}			
			}elseif(!Yii::$app->userrole->isCustomer()){			
				return false;
			}	
						
			$BuyerModel = Buyer::find()->where(['id'=>$id,'type'=>$data['type']]);
			$userData = Yii::$app->userdata->getData();
			$franchiseid=$userData['franchiseid'];
			$userid=$userData['userid'];
			if(Yii::$app->userrole->isOSSUser())
			{
				$BuyerModel = $BuyerModel->innerJoinWith(['createdbydata as createdbydata']);	
				$BuyerModel = $BuyerModel->andWhere(' createdbydata.franchise_id="'.$franchiseid.'" ');
			}elseif(Yii::$app->userrole->isCustomer())
			{
				$BuyerModel = $BuyerModel->andWhere(['created_by' => $userid]);
			}			
			$BuyerModel=$BuyerModel->one();

			if($BuyerModel!==null)
			{
				//$BuyerModel->delete();
				$BuyerModel->status = $BuyerModel->enumStatus['archived'];
				$BuyerModel->save();
			}	
		}
		return $responsedata=array('status'=>1,'message'=>'Data deleted successfully');
	}
	
	
}
