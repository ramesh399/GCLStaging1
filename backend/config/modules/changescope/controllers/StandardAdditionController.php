<?php
namespace app\modules\changescope\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use app\modules\application\models\Application;
use app\modules\certificate\models\Certificate;
use app\modules\application\models\ApplicationStandard;
use app\modules\master\models\Standard;
use app\modules\changescope\models\StandardAddition;
use app\modules\changescope\models\StandardAdditionStandard;
use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * StandardAdditionController implements the CRUD actions for Product model.
 */
class StandardAdditionController extends \yii\rest\Controller
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
		
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];
		$role_chkid=$userData['role_chkid'];
		
		$model = StandardAddition::find()->alias('t');
		$model = $model->innerJoinWith(['application as app']);	
		if($resource_access != '1')
		{

			if($user_type== Yii::$app->params['user_type']['customer']){
				$model = $model->andWhere('app.customer_id="'.$userid.'"');
			}else if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$model = $model->andWhere('app.franchise_id="'.$userid.'" or t.created_by="'.$userid.'"');
			}
			/*
			if($user_type==2)
			{
				$customer_roles=Yii::$app->globalfuns->getCustomerRoles();					
				$model = $model->andWhere('faq_access.user_access_id in("'.$customer_roles.'")');	
			}elseif($user_type==3 && $resource_access==5){	
				$model = $model->andWhere('faq_access.user_access_id ="'.$role_chkid.'"');			
			}elseif($user_type==3){			
				$osp_roles=Yii::$app->globalfuns->getOspRoles();					
				$model = $model->andWhere('faq_access.user_access_id in("'.$osp_roles.'")');	
			}else if($user_type== Yii::$app->params['user_type']['user'] && in_array('faq',$rules )){

			}else{
				$model = $model->andWhere('faq_access.user_access_id ="'.$role.'"');	
			}	
			*/		
		}
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm']!='')
			{
				$model = $model->innerJoinWith('applicationaddress as caddress');
				
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',	
					['like', 'caddress.company_name', $searchTerm],
					['like', 'caddress.telephone', $searchTerm],
					['like', 'caddress.first_name', $searchTerm],
					['like', 'caddress.last_name', $searchTerm],					
					['like', 'date_format(FROM_UNIXTIME(t.`created_at` ), \'%b %d, %Y\' )', $searchTerm],
				]);			
			}
			
			$totalCount = $model->count();			
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
		
		$question_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $question)
			{
				$data=array();
				$data['id']=$question->id;
				$data['app_id']=$question->app_id;
				$data['new_app_id']=$question->new_app_id;
				$data['status']=$question->status;
				$data['status_name']=$question->arrStatus[$question->status];
				$data['company_name']=$question->applicationaddress->company_name;
				$data['showdelete']=0;//($question->status==$question->arrEnumStatus['open'])?1:0;
				$data['showedit']=($question->status==$question->arrEnumStatus['open'] || $question->status==$question->arrEnumStatus['submitted'] || $question->status==$question->arrEnumStatus['pending_with_customer'])?1:0;
								
				$data['created_by_label']=$question->createdbydata->first_name.' '.$question->createdbydata->last_name;
				
				$data['created_at']=date($date_format,$question->created_at);
				//$data['created_by_label']=$question->createdbydata->first_name.' '.$question->createdbydata->last_name;
				
				$arrAppStd=array();
				$appStd = $question->appstandard;
				if(count($appStd)>0)
				{	
					foreach($appStd as $app_standard)
					{
						$arrAppStd[]=$app_standard->standard->code;
					}
				}					
				$data['addition_standard']=implode(', ',$arrAppStd);
				
				$data['addition_standard_count']=count($appStd);
				
				$question_list[]=$data;
			}
		}

		return ['standardadditions'=>$question_list,'total'=>$totalCount];
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

	public function actionView()
	{
		$model = new StandardAddition();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$standardmodel = StandardAddition::find()->where(['id' => $data['id']])->one();
			if($standardmodel!==null)
			{
				if(!Yii::$app->userrole->canViewApplication($standardmodel->app_id))
				{
					return false;
				}
				$resultarr = [];
				$resultarr['company_name'] = $standardmodel->application->companyname;
				$resultarr['status'] = $model->arrStatus[$standardmodel->status];
				$resultarr['created_at'] = date($date_format,$standardmodel->created_at);
				$resultarr['created_by'] = $standardmodel->createdbydata->first_name." ".$standardmodel->createdbydata->last_name;
				$standardaddition = $standardmodel->appstandard;
				if(count($standardaddition)>0)
				{
					$stdAdd = [];
					foreach($standardaddition as $stdAddition)
					{
						$stdAdd[]=$stdAddition->standard->code;
					}
					$resultarr['standards'] = implode(",",$stdAdd);
				}
				$responsedata=array('status'=>1,'data'=>$resultarr);
			}
		}
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
			//$appstdmodel = ApplicationStandard::find()->select('standard_id')->where(['app_id' => $data['id'],'standard_status'=>$ModelApplicationStandard->arrEnumStatus['valid']])->all();
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
		if(!Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->isCustomer())
		{
			return false;
		}
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data){
			
			if(!Yii::$app->userrole->isValidApplication($data['company_id']))
			{
				return false;
			}
			
			$update =0;
			if(isset($data['id']))
			{
				$model = StandardAddition::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new StandardAddition();
					$model->created_by = $userid;
				}else{
				 	StandardAdditionStandard::deleteAll(['standard_addition_id' => $punit->id]);
					
					//ProcessAdditionUnit::deleteAll(['process_addition_id' => $model->id]);
					$model->status = 1;
					$model->updated_by = $userid;
					$update =1;
				}
			}else{
				$model = new StandardAddition();
				$model->created_by = $userid;
				$model->status = 0;
			}

			 
			$model->app_id= $data['company_id'];
			$model->address_id = Yii::$app->globalfuns->getAppCurrentAddressId($data['company_id']);
			
			
			
			if($model->validate() && $model->save())
			{
				$modelID = $model->id;
				 
				if(is_array($data['standard_id']) && count($data['standard_id'])>0){
					foreach($data['standard_id'] as $standard_id){
						
						$StandardAdditionStandard = new StandardAdditionStandard();
						$StandardAdditionStandard->standard_addition_id = $modelID;
						$StandardAdditionStandard->standard_id = $standard_id;
						$StandardAdditionStandard->save();
					}
				}
				 

				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Standard updated successfully','id'=>$model->id);
				}else{
					$responsedata=array('status'=>1,'message'=>'Standard updated successfully','id'=>$model->id);
				}
			}
		}
		
		return $this->asJson($responsedata);
	}
	
	public function actionGetStandardAddition()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data)
		{			
			$stdAdd=array();
			$appstdmodel = StandardAddition::find()->where(['app_id' => $data['app_id'],'id' => $data['standard_addition_id']])->one();
			if($appstdmodel!==null)				
			{					
				$standardaddition = $appstdmodel->appstandard;
				if(count($standardaddition)>0)
				{
					foreach($standardaddition as $stdAddition)
					{
						$stdAdd[]="$stdAddition->standard_id";
					}					
				}				
			}
			$responsedata=array('status'=>1,'standardaddition'=>$stdAdd);			
		}	
		return $responsedata;
	}

	public function actionGetStandardAdditionDetails()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data)
		{			
			$app_id = $data['app_id'];
			if(!Yii::$app->userrole->canViewApplication($app_id))
			{
				return false;
			}
			$stdAdd=array();
			$CertificateModel = new Certificate();
			$appstdmodel = StandardAddition::find()->where(['app_id' => $app_id ])->all();
			if(count($appstdmodel)>0)				
			{
				foreach($appstdmodel as $stdadd){
					$standardaddition = $stdadd->appstandard;
					if(count($standardaddition)>0)
					{
						foreach($standardaddition as $stdAddition)
						{
							$Certificate = Certificate::find()->where(['certificate_status'=> $CertificateModel->arrEnumCertificateStatus['valid'],'parent_app_id'=>$app_id,'standard_id'=>$stdAddition->standard_id])->one();
							if($Certificate !== null)
							{
								$found = false;
								foreach($stdAdd as $v) {
									if ($v['id'] == $stdAddition->standard_id) {
									$found = true;
									}
								}
								if($found===false){
									$stdAdd[]=[
										'id'=>"$stdAddition->standard_id",
										'name' => $stdAddition->standard->name,
										'code' => $stdAddition->standard->code
									];
								}
							}						
						}					
					}	
				}							
			}
			$responsedata=array('status'=>1,'standardaddition'=>$stdAdd);			
		}	
		return $responsedata;
	}
	
	public function actionGetrequestedstatus()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$modelAddition = new StandardAddition();						
			$resultarr=array();			
			
			$appmodel = StandardAddition::find()->where(['t.app_id' => $data['id']])->alias('t');
			$appmodel = $appmodel->andWhere('t.status in('.$modelAddition->arrEnumStatus['approved'].','.$modelAddition->arrEnumStatus['failed'].','.$modelAddition->arrEnumStatus['osp_reject'].')');
			$appmodel = $appmodel->all();
			if(count($appmodel)>0)
			{
				$responsedata=array('status'=>1,'unitdata'=>'');
			}
			$responsedata=array('status'=>1,'unitdata'=>'');
			//$responsedata=array('status'=>0,'message'=>'Unit Addition is in progress.');
		}
		return $this->asJson($responsedata);
	}	


	public function actionCommonUpdate()
	{
		if(!Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->isCustomer())
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data){
			if(isset($data['id']) && $data['id']>0){
				$StandardAddition = StandardAddition::find()->where(['id'=>$data['id']])->one();

				if($StandardAddition !== null){
					if(!Yii::$app->userrole->isValidApplication($StandardAddition->app_id))
					{
						return false;
					}
					StandardAdditionStandard::deleteAll(['standard_addition_id'=>$StandardAddition->id]);
					
					$StandardAddition->delete();
					$responsedata=array('status'=>1,'message'=>'Standard deleted successfully');
				}				
			}
		}		
		return $this->asJson($responsedata);
	}
}
