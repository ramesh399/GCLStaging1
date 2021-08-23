<?php
namespace app\modules\changescope\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use app\modules\certificate\models\Certificate;

use app\modules\master\models\Standard;
use app\modules\master\models\Process;
use app\modules\master\models\State;
use app\modules\master\models\UserCompanyInfo;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;

use app\modules\changescope\models\Withdraw;
use app\modules\changescope\models\WithdrawReviewer;
use app\modules\changescope\models\WithdrawReviewerComment;
use app\modules\changescope\models\WithdrawUnit;
/*
use app\modules\changescope\models\WithdrawProductMaterial;
use app\modules\changescope\models\WithdrawProduct;
use app\modules\changescope\models\WithdrawProductStandard;
*/
use app\modules\changescope\models\WithdrawUnitProduct;
use app\modules\changescope\models\WithdrawFranchiseComment;


use app\modules\certificate\models\CertificateReviewer;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationUnitProduct;
use app\modules\application\models\ApplicationUnitStandard;
use app\modules\application\models\ApplicationStandard;

use app\modules\audit\models\Audit;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * WithdrawController implements the CRUD actions for Product model.
 */
class WithdrawController extends \yii\rest\Controller
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
		
		$WithdrawModel = new Withdraw();

		$model = Withdraw::find()->alias('t');
		$model = $model->innerJoinWith(['application as app']);	
		if($resource_access != '1')
		{

			if($user_type== Yii::$app->params['user_type']['customer']){
				$model = $model->andWhere('app.customer_id="'.$userid.'"');
			}else if($user_type== Yii::$app->params['user_type']['user']){
				$model = $model->joinWith(['reviewer as reviewer']);	
				$model = $model->andWhere('(t.status= "'.$WithdrawModel->arrEnumStatus['waiting_for_review'].'" or reviewer.user_id="'.$userid.'")');
			}else if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$model = $model->andWhere('t.status>0 and app.franchise_id="'.$userid.'"');
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
				
				$data['status']=$question->status;
				$data['status_name']=$question->arrStatus[$question->status];
				$data['company_name']=$question->applicationaddress->company_name;
				$showedit = 0;
				if(($question->status == $question->arrEnumStatus['open'] || $question->status == $question->arrEnumStatus['pending_with_customer']) && ($user_type==2 || $resource_access==1)){
					$showedit = 1;
				}
				/*
				if(($question->status == $question->arrEnumStatus['waiting_for_osp_review'] || $question->status == $question->arrEnumStatus['pending_with_osp']) && ($user_type==3 || $resource_access==1)){
					$showedit = 1;
				}
				*/
				$data['showedit']=($showedit==1)?1:0;
				$data['showdelete']=($question->status==$question->arrEnumStatus['open'])?1:0;
				$data['created_by_label']=$question->createdbydata->first_name.' '.$question->createdbydata->last_name;
				$data['created_at']=date($date_format,$question->created_at);
				//$data['created_by_label']=$question->createdbydata->first_name.' '.$question->createdbydata->last_name;
				
				$unitproductcount=0;
				
				$arrAppUnit=array();
				$appUnit = $question->withdrawunit;
				$no_of_unit = count($appUnit);
				if($no_of_unit>0)
				{	
					foreach($appUnit as $app_unit)
					{
						$arrAppUnit[]=$app_unit->applicationunit?$app_unit->applicationunit->name:'';
					}
				}					
				$data['application_unit'] = implode(', ',$arrAppUnit);
				$data['no_of_unit'] = $no_of_unit;
				$question_list[]=$data;
			}
		}

		return ['withdrawunits'=>$question_list,'total'=>$totalCount];
	}

	public function actionGetProductstd()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		$resultarr = [];
		if($data)
		{
			$appModel = ApplicationUnit::find()->where(['id' => $data['unit_id']])->one();
			if($appModel!==null)
			{
				$arrstandardids=[];
				$appStandard=$appModel->unitappstandard;
				if(count($appStandard)>0)
				{
					foreach($appStandard as $std)
					{
						$arrstandards = [];
						$arrstandards['name']=($std->standard?$std->standard->name:'');	
						$arrstandards['id']=$std->standard_id;
						$resultarr[] = $arrstandards;
					}
				}
				$responsedata=array('status'=>1,'data'=>$resultarr);
			}
		}
		return $responsedata;
	}

	public function actionAddOspReview()
    {
		if(!Yii::$app->userrole->isOSS() && !Yii::$app->userrole->isAdmin()){
			return false;
		}
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];

		
		if($data)
		{
			//User Access Condition Starts Here
			$WithdrawModel = new Withdraw();
			$productmodelCheck = Withdraw::find()->alias('t');
			$productmodelCheck = $productmodelCheck->innerJoinWith(['application as app'])->where(['t.id' => $data['id']]);	
			$checkStatus = [$WithdrawModel->arrEnumStatus['waiting_for_osp_review'],$WithdrawModel->arrEnumStatus['pending_with_osp']];
			$productmodelCheck = $productmodelCheck->andWhere(['t.status'=>$checkStatus]);
			if($resource_access != '1')
			{
				if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
					if($resource_access == '5'){
						$userid = $franchiseid;
					}
					$productmodelCheck = $productmodelCheck->andWhere(['app.franchise_id'=>$userid]);
				}
			}
			$productmodelCheck = $productmodelCheck->one();
			if($productmodelCheck===null){
				return false;
			}
			//User Access Condition Ends Here
			
			$ospreviewmodel = new WithdrawFranchiseComment();
			$ospreviewmodel->withdraw_id = $data['id'];
			$ospreviewmodel->status = $data['status'];
			$ospreviewmodel->comment = isset($data['comment'])?$data['comment']:'';
			$ospreviewmodel->created_by = $userid;
			$ospreviewmodel->created_at = time();
			if($ospreviewmodel->validate() && $ospreviewmodel->save())
			{
				$productmodel = Withdraw::find()->where(['id' => $data['id']])->one();
				if($productmodel!==null)
				{
					if($data['status']=='1')
					{
						$WithdrawReviewer = WithdrawReviewer::find()->where(['withdraw_id'=>$data['id']])->one();
						if($WithdrawReviewer!==null){
							$productmodel->status = $productmodel->arrEnumStatus['review_in_process'];//4;
						}else{
							$productmodel->status = $productmodel->arrEnumStatus['waiting_for_review'];//4;
						}
						
					}
					else if($data['status']=='2')
					{
						$productmodel->status = $productmodel->arrEnumStatus['pending_with_customer'];//1;
					}
					else if($data['status']=='3')
					{
						$productmodel->status = $productmodel->arrEnumStatus['rejected'];//7;
					}
					$productmodel->save();
					$responsedata=array('status'=>1,'message'=>"Review Saved Successfully!");
				}
				
			}
			
		}
		return $responsedata;
	}

	public function actionAddReviewerReview()
    {
		if(!Yii::$app->userrole->hasRights(['application_review'])){
			return false;
		}
		//
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data)
		{


			//User Access Condition Starts Here
			$WithdrawModel = new Withdraw();
			$WithdrawCheck = Withdraw::find()->where(['id' => $data['id'],'status'=>$WithdrawModel->arrEnumStatus['review_in_process'] ])->one();
			if($WithdrawCheck === null){
				return false;
			}
			if(!Yii::$app->userrole->isAdmin()){
				
				$WithdrawReviewer = WithdrawReviewer::find()->where(['withdraw_id'=>$data['id'],'reviewer_status'=>1])->one();
				if($WithdrawReviewer!==null){
					if($WithdrawReviewer->user_id != $userid){
						return false;
					}
				}else{
					return false;
				}
			}
			//User Access Condition Ends Here


			$ospreviewmodel = new WithdrawReviewerComment();
			$ospreviewmodel->withdraw_id = $data['id'];
			$ospreviewmodel->withdraw_reviewer_id = $userid;
			$ospreviewmodel->status = $data['status'];
			$ospreviewmodel->comment = isset($data['comment'])?$data['comment']:'';
			$ospreviewmodel->created_by = $userid;
			$ospreviewmodel->created_at = time();
			if($ospreviewmodel->validate() && $ospreviewmodel->save())
			{
				$productmodel = Withdraw::find()->where(['id' => $data['id']])->one();
				if($productmodel!==null)
				{
					if($data['status']=='1')
					{
						$productmodel->status =  $productmodel->arrEnumStatus['approved'];//6;

						if(count($productmodel->withdrawunit)>0){
							$standardIds= [];
							foreach($productmodel->withdrawunit as $withdrawunits){
								$unit_id = $withdrawunits->unit_id;
								$ApplicationUnit = ApplicationUnit::find()->where(['id' => $unit_id ])->one();
								if($ApplicationUnit!==null)
								{

									$unitappstandard = $ApplicationUnit->unitappstandard;
									if(count($unitappstandard)>0){
										foreach($unitappstandard as $unitstandards){
											$standardIds[] = $unitstandards->standard_id;
										}
									}
									
									$ApplicationUnit->status = $ApplicationUnit->enumUnitStatus['deleted'];
									$ApplicationUnit->save();
									
								}
								
							}
							$uniquestandardIds = array_unique($standardIds);
							//print_r($uniquestandardIds);
							$app_id = $productmodel->app_id;
							
							$certdata = ['withdraw_id'=>$productmodel->id, 'app_id'=>$app_id,'standard_ids'=>$uniquestandardIds];
							$Certificate = new Certificate();
							$this->getStandardsCertificate($certdata);
							
						}
						/*
						$audit = Audit::find()->where(['app_id'=> $productmodel->app_id])->one();
						if($audit !== null){
							$audit_id = $audit->id;
						}
						*/
						 
					}
					else if($data['status']=='2')
					{
						$productmodel->status =  $productmodel->arrEnumStatus['pending_with_osp'];//3;
					}
					else if($data['status']=='3')
					{
						$productmodel->status =  $productmodel->arrEnumStatus['rejected'];//7;
					}
					$productmodel->save();
					$responsedata=array('status'=>1,'message'=>"Review Saved Successfully!");
				}
				
			}
			
		}
		return $responsedata;
	}

	public function getStandardsCertificate($data){
		$uniquestandardIds = $data['standard_ids'];
		$app_id = $data['app_id'];
		$withdraw_id = $data['withdraw_id'];
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		//echo count($uniquestandardIds);
		if(is_array($uniquestandardIds) && count($uniquestandardIds)>0){
			foreach($uniquestandardIds as $certstandardid)
			{			
				$certificatemodel = Certificate::find()->where(['certificate_status'=>0,'parent_app_id'=>$app_id,'standard_id'=>$certstandardid])->one();
				if($certificatemodel!==null)
				{
					$certificatemodel->certificate_status =1;
					$certificatemodel->save();
					
					$NewCertificate = new Certificate();
					$NewCertificate->audit_id = $certificatemodel->audit_id;
					$NewCertificate->withdraw_id = $withdraw_id;
					$NewCertificate->parent_app_id = $certificatemodel->parent_app_id;
					$NewCertificate->standard_id = $certificatemodel->standard_id;
					$NewCertificate->product_addition_id = '';
					$NewCertificate->certificate_status = $certificatemodel->arrEnumCertificateStatus['valid'];//0;
					$NewCertificate->type = $certificatemodel->arrEnumType['withdraw_unit'];
					$NewCertificate->status = $certificatemodel->arrEnumStatus['certificate_generated'];
					$NewCertificate->version = ($certificatemodel->version + 1);
					$NewCertificate->certificate_generated_date = date('Y-m-d');//$certModel->certificate_generated_date;					
					$NewCertificate->certificate_valid_until = $certificatemodel->certificate_valid_until;
					$NewCertificate->actual_certificate_valid_until = $certificatemodel->actual_certificate_valid_until;
					$NewCertificate->created_by = $userid;
					$NewCertificate->certificate_generated_by = $userid;
					
					if($NewCertificate->save()){
						$certdata = [];
						$certdata['standard_id'] = $NewCertificate->standard_id;
						$certdata['certificate_id'] = $NewCertificate->id;
						$certdata['audit_id'] = $NewCertificate->audit_id;
						$certdata['app_id'] = $app_id;
						
						$NewCertificate->generateCertificate($certdata['certificate_id'],true);					
					}				
				}
			}
		}
		return true;
	}
	
	public function actionGetUnit()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		$resultarr = [];
		if($data)
		{
			$appModel = Application::find()->where(['id' => $data['id']])->one();
			if($appModel!==null)
			{
				$units = $appModel->applicationunit;
				if(count($units)>0)
				{
					$unitarr = [];
					foreach($units as $val)
					{
						$units = [];
						$units['id'] = $val['id'];
						$units['name'] = $val['name'];
						$unitarr[]=$units;
					}
					$resultarr['unitlist'] = $unitarr;
				}
				$responsedata=array('status'=>1,'data'=>$resultarr);
			}
		}
		return $responsedata;
	}

	public function actionGetAppdata()
	{
		$prdmodel = new Withdraw();
		$ospreviewmodal = new WithdrawFranchiseComment();
		$reviewerreviewmodal = new WithdrawReviewerComment();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$Certificatemodel = new Certificate();
		$appmodel = Application::find()->alias('t');
		$appmodel = $appmodel->join('inner join', 'tbl_audit as audit','audit.app_id =t.id');
		$appmodel = $appmodel->join('inner join', 'tbl_certificate as cert','audit.id =cert.audit_id and cert.status="'.$Certificatemodel->arrEnumStatus['certificate_generated'].'" and cert.certificate_valid_until>="'.date('Y-m-d').'" ');
		if($resource_access != 1){
			if($user_type==2){
				$appmodel = $appmodel->andWhere(['t.customer_id' => $userid]);
			}else if($user_type==3 && $is_headquarters!='1'){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$appmodel = $appmodel->andWhere(['t.franchise_id' => $userid]);
			}else if($user_type==1){
				if(!Yii::$app->userrole->hasRights(['application_review'])){
					return false;
				}
				if( $is_headquarters!='1'){
					$appmodel = $appmodel->andWhere(['t.franchise_id' => $franchiseid]);
				}
			} 
		}

		$appmodel = $appmodel->all();
		$resultarr = [];
		$reviewarr = [];
		$resultarr['units'] = [];
		$appdetails = [];
		if(count($appmodel)>0)
		{
			$apparr = array();			
			/*
			foreach($appmodel as $app)
			{
				$apparr[] = ['id'=> $app->id, 'company_name' => $app->company_name];
			}
			*/
			$unitwithdrawdetails = [];
		}
		if($data)
		{
			if(isset($data['id']) && $data['id']>0)
			{				
				$appprdarr_details=[];
				$appprdarr = [];
				$model = Withdraw::find()->where(['id' => $data['id']])->one();
				if($model!==null)
				{
					if($resource_access != 1 && $user_type==1 && Yii::$app->userrole->hasRights(['application_review'])){
						if($model->reviewer && $model->reviewer->user_id!='' && $model->reviewer->user_id != $userid){
							return false;
						}
					}

					$audit = Audit::find()->where(['app_id'=> $model->app_id])->one();
					if($audit !== null){
						$unitwithdrawdetails['audit_id'] = $audit->id;
					}

					$unitwithdrawdetails['app_id'] = $model->app_id;
					///$unitwithdrawdetails['new_app_id'] = $model->new_app_id;
					$unitwithdrawdetails['status'] = $model->status;
					$unitwithdrawdetails['withdraw_status'] = $model->status;
					
					$unitwithdrawdetails['reviewer_id'] =$model->reviewer?$model->reviewer->user_id:'';
					$unitwithdrawdetails['reason'] = $model->reason;

					

					$applicationmodel = $model->application;
		 			$exclude = 1;
					$excludeunits = [];
					
					if(count($model->withdrawunit)>0){
						foreach($model->withdrawunit as $unitsdata){
							$excludeunits[] = $unitsdata->unit_id;
							$unit_names[] = $unitsdata->applicationunit->name;
							

						}
					}

					$appdetails = $this->getApplicationDetail($applicationmodel,$excludeunits,$model);

					$reviewarr['company_name'] = $model->applicationaddress->company_name;
					$reviewarr['units'] = implode(", ",$unit_names);
					$reviewarr['status'] = $model->status;
					$reviewarr['status_name'] = $prdmodel->arrStatus[$model->status];
					$reviewarr['created_by'] = $model->createdbydata->first_name." ".$model->createdbydata->last_name;
					$reviewarr['created_at'] = date($date_format,$model->created_at);

					$ospmodal = $model->franchisecmt;
					if(count($ospmodal)>0)
					{
						$ospcmts = [];
						foreach($ospmodal as $res)
						{
							$cmt = [];
							$cmt['status'] = $res->status;
							$cmt['status_label'] = $ospreviewmodal->arrStatus[$res->status];
							$cmt['comment'] = $res->comment;
							$cmt['created_at'] = date($date_format,$res->created_at);
							$cmt['created_by'] = $res->createdbydata->first_name." ".$res->createdbydata->last_name;
							$ospcmts[] = $cmt;
						}
						$reviewarr['osp_reviews'] = $ospcmts;
					}


					$reviewermodal = $model->reviewercmt;
					if(count($reviewermodal)>0)
					{
						$reviewercmts = [];
						foreach($reviewermodal as $res)
						{
							$cmt = [];
							$cmt['status'] = $res->status;
							$cmt['status_label'] = $reviewerreviewmodal->arrStatus[$res->status];
							$cmt['comment'] = $res->comment;
							$cmt['created_at'] = date($date_format,$res->created_at);
							$cmt['created_by'] = $res->createdbydata->first_name." ".$res->createdbydata->last_name;
							$reviewercmts[] = $cmt;
						}
						$reviewarr['reviewer_reviews'] = $reviewercmts;
					}

					$withdrawreviewermodal = $model->reviewer;
					if($withdrawreviewermodal!==null)
					{
						$reviewerdata = [];
						$reviewerdata['reviewer'] = $withdrawreviewermodal->user->first_name." ".$withdrawreviewermodal->user->last_name;
						$reviewerdata['assigned_date'] = date($date_format,$withdrawreviewermodal->created_at);
						$reviewarr['reviewer'] = $reviewerdata;
					}			 		
				}			
			}

			$WithdrawModel = new Withdraw();
			$responsedata=array('status'=>1,'appdata'=>$apparr,'units'=>$resultarr["units"],'unitwithdrawdetails'=>$unitwithdrawdetails,'reviewdetails'=>$reviewarr,'appdetails'=>$appdetails,'enumstatus' => $WithdrawModel->arrEnumStatus );

		}
		//$WithdrawModel = new Withdraw();
		$responsedata['status']=1;
		$responsedata['appdata']=$apparr;

			//array('status'=>1,'appdata'=>$apparr,'units'=>$resultarr["units"],'productdetails'=>$productdetails,'reviewdetails'=>$reviewarr,'appdetails'=>$appdetails,'enumstatus' => $WithdrawModel->arrEnumStatus );

		 
		return $this->asJson($responsedata);
	}
	
	public function actionGetAppcompanydata()
	{
		if(!Yii::$app->userrole->isCustomer() && !Yii::$app->userrole->isAdmin() ){
			return false;
		}
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];
		$data = Yii::$app->request->post();
		

		$reason = '';
		$apparr = Yii::$app->globalfuns->getAppList();
		if(count($apparr)>0){
			$units = [];
			$app_id = 0;
			if(isset($data['id']) && $data['id']>0){
				$pmodel = Withdraw::find()->where(['id'=>$data['id']])->one();
				if($pmodel !== null){
					$app_id = $pmodel->app_id;
					$reason  = $pmodel->reason;
					if(count($pmodel->withdrawunit)>0){
						foreach($pmodel->withdrawunit as $additionunit){
							$units[] = $additionunit->unit_id;
						}
					}
				}
			}
			
			$responsedata=array('status'=>1,'appdata'=>$apparr,'units'=>$units,'app_id'=>$app_id,'reason'=>$reason);

		}
		return $this->asJson($responsedata);
	}
	
	public function actionCreate()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data  = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data){
			if(!Yii::$app->userrole->isValidApplication($data['app_id']))
			{
				return false;
			}
			//$data = json_decode($datapost['formvalues'],true);
			$update =0;
			if(isset($data['id']))
			{
				$model = Withdraw::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new Withdraw();
					$model->created_by = $userid;
				}else{
					

					$model->updated_by = $userid;
					$update =1;
				}
			}else{
				$model = new Withdraw();
				$model->created_by = $userid;
				$model->status = 0;	
			}

			 
			$model->app_id= $data['app_id'];
			$model->address_id = Yii::$app->globalfuns->getAppCurrentAddressId($data['app_id']);
			$model->reason= $data['reason'];
			
			if($data['type']=='2'){
				$model->status = $model->arrEnumStatus['waiting_for_osp_review'];
			}
			
			$new=0;
			if($model->validate() && $model->save())
			{
				$modelID = $model->id;


				if(is_array($data['unit_id']) && count($data['unit_id'])>0)
				{
					WithdrawUnit::deleteAll(['withdraw_id' => $modelID]);

					foreach ($data['unit_id'] as $value)
					{
						$unitmodel=new WithdrawUnit();
						$unitmodel->unit_id = $value;
						$unitmodel->withdraw_id = $modelID;
						//$appunitmodel->unit_type = $value['unit_type'];
						$unitmodel->save();
					}
				}				

				if($update==1){
					$responsedata=array('status'=>1,'message'=>'Withdraw updated successfully' ,'app_id'=>$data['app_id'],'id'=>$modelID);
				}else{
					$responsedata=array('status'=>1,'message'=>'Withdraw added successfully','app_id'=>$data['app_id'],'id'=>$modelID );
				}
			}
		}
		
		return $this->asJson($responsedata);
	}

	public function actionAssignReviewer()
	{
		if(!Yii::$app->userrole->hasRights(['application_review'])){
			return false;
		}

		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data  = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data)
		{

			//User Access Condition Starts Here
			$WithdrawModel = new Withdraw();
			$WithdrawCheck = Withdraw::find()->where(['id' => $data['id'],'status'=>$WithdrawModel->arrEnumStatus['waiting_for_review'] ])->one();
			if($WithdrawCheck === null){
				return false;
			}else{
				$modelWithdrawReviewer = WithdrawReviewer::find()->where(['withdraw_id'=>$data['id'],'reviewer_status'=>1])->one();
				if($modelWithdrawReviewer!==null){
					return false;
				}
			}
			//User Access Condition Ends Here
			

			$reviewermodel = new WithdrawReviewer();
			$reviewermodel->withdraw_id = $data['id'];
			$reviewermodel->user_id = $userid;
			$reviewermodel->created_by = $userid;
			if($reviewermodel->validate() && $reviewermodel->save())
			{
				$productmodel = Withdraw::find()->where(['id' => $data['id']])->one();
				if($productmodel!==null)
				{
					$productmodel->status = $productmodel->arrEnumStatus['review_in_process'];
					$productmodel->save();
					$responsedata=array('status'=>1,'message'=>"Assigned Successfully!",'withdraw_status'=>$productmodel->status);
				}

			}
			
		}
		return $responsedata;
	}

	public function actionGetStatus()
	{
		$data  = Yii::$app->request->post();
		if($data)
		{
			if($data['status']==5)
			{
				$model = new WithdrawReviewerComment();
			}
			else
			{
				$model = new WithdrawFranchiseComment();
			}
			
			return ['data'=>$model->arrStatus];
		}		
	}	
	
	public function getApplicationDetail($model,$excludeunits,$currentmodel)
	{
		$resultarr = [];
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		 
		if ($model !== null)
		{		
			$resultarr=array();

			$resultarr = Yii::$app->globalfuns->getApplicationAddressDetails($currentmodel->applicationaddress);
			$resultarr["id"]=$model->id;
			$resultarr["code"]=$model->code;
			$resultarr["company_file"]=$model->company_file;
			$resultarr['created_at']=date($date_format,$model->created_at);
			$resultarr["created_by"]=($model->created_by!="")?$model->username->first_name.' '.$model->username->last_name:"";
			$resultarr["certification_status"]=$model->certification_status;

			$resultarr["reject_comment"]=$model->reject_comment;
			$resultarr["rejected_date"]=date($date_format,strtotime($model->rejected_date));

			//$resultarr["preferred_partner_id"]=$model->preferred_partner_id;
			//$resultarr["preferred_partner_id_name"]=($model->preferredpartner?$model->preferredpartner->name:'');
			
			$resultarr["app_status"]=$model->status;
			$resultarr["status"]=$model->arrStatus[$model->status];
			$resultarr["franchise_id"]=$model->franchise_id;

			if($model->franchise){
				$resultarr["franchise"]= $model->franchise->usercompanyinfo->toArray();
				$resultarr["franchise"]['company_country_name']= $model->franchise->usercompanyinfo->companycountry->name;
				$resultarr["franchise"]['company_state_name']= $model->franchise->usercompanyinfo->companystate?$model->franchise->usercompanyinfo->companystate->name:'';
			}

			$appstdarr=[];
			$arrstandardids=[];
			$appStandard=$model->applicationstandard;
			if(count($appStandard)>0)
			{
				foreach($appStandard as $std)
				{
					$appstdarr[]=($std->standard?$std->standard->name:'');	
					$arrstandardids[]=$std->standard_id;
				}
			}
			$resultarr["standards"]=$appstdarr;
			$resultarr["standard_ids"]=$arrstandardids;
			
			 
			$appprdarr=[];
			$appprdarr_details=[];
			$appProduct=$model->applicationproduct;
			if(count($appProduct)>0)
			{
				$pdt_index = 0;
				foreach($appProduct as $prd)
				{
					
					$productMaterialList = [];
					$materialcompositionname = '';
					if(is_array($prd->productmaterial) && count($prd->productmaterial)>0){
						foreach($prd->productmaterial as $productmaterial){
							$productMaterialList[]=[
								'app_product_id'=>$productmaterial->app_product_id,
								'material_id'=>$productmaterial->material_id,
								'material_name'=>$productmaterial->material->name,
								'material_type_id'=>$productmaterial->material_type_id,
								'material_type_name'=> $productmaterial->material->material_type[$productmaterial->material_type_id],
								'material_percentage'=>$productmaterial->percentage
							];
							$materialcompositionname = $materialcompositionname.$productmaterial->percentage.'% '.$productmaterial->material->name.' + ';

						}
						$materialcompositionname = rtrim($materialcompositionname," + ");
					}

					
					//ApplicationProductStandard::find()->where(['application_product_id' =>  ])->all();
					$arrsForPdtDetails=array(
						'id'=>$prd->product_id,
						'autoid'=>$prd->id,
						'pdt_index'=>$pdt_index,
						'name'=> $prd->product_name,//($prd->product?$prd->product->name:''),
						'wastage'=>$prd->wastage,
						'product_type_name' => $prd->product_type_name,//isset($prd->producttype)?$prd->producttype->name:'',
						'product_type_id'=>isset($prd->producttype)?$prd->producttype->id:'',
						'productMaterialList' => $productMaterialList,
						'materialcompositionname' => $materialcompositionname,
					);	


					$productStandardList = [];
					$arrpdtDetails = [];
					if(is_array($prd->productstandard) && count($prd->productstandard)>0){
						$i=0;
						foreach($prd->productstandard as $productstandard){
							$productStandardList[] = [
								'id' => $productstandard->id,
								'standard_id' => $productstandard->standard_id,
								'standard_name' => $productstandard->standard->name,
								'label_grade' => $productstandard->label_grade_id,
								'label_grade_name' => $productstandard->label_grade_name
							];

							
							$arrsForPdtDetails['standard_id'] = $productstandard->standard_id;
							$arrsForPdtDetails['standard_name'] = $productstandard->standard->name;
							$arrsForPdtDetails['label_grade'] = $productstandard->label_grade_id;
							$arrsForPdtDetails['label_grade_name'] = $productstandard->label_grade_name;
							$arrsForPdtDetails['pdtListIndex'] = $i;
							

							$appprdarr_details[$productstandard->id]= $arrsForPdtDetails;
							$i++;
						}
					}
					


					$materialcompositionname = rtrim($materialcompositionname,' + ');
					$pdt_index_list[$prd->id] = $pdt_index;
					$arrs=array(
						'id'=>$prd->product_id,
						'autoid'=>$prd->id,
						'pdt_index'=>$pdt_index,
						'name'=>$prd->product_name,//($prd->product?$prd->product->name:''),
						'wastage'=>$prd->wastage,
						'product_type_name' => $prd->product_type_name,//isset($prd->producttype)?$prd->producttype->name:'',
						'product_type_id'=>isset($prd->producttype)?$prd->producttype->id:'',
						//'standard_id'=>$prd->standard_id,
						//'label_grade'=>$prd->label_grade_id,
						//'standard_name' => $prd->standard->name,
						//'label_grade_name' => $prd->standardlabelgrade->name,
						'productStandardList' => $productStandardList,
						'productMaterialList' => $productMaterialList,
						'materialcompositionname' => $materialcompositionname,
					);	
					$appprdarr[] = $arrs;


					
					$pdt_index++;
					
				}
			}
			$resultarr["products"]=$appprdarr;

			foreach($appprdarr_details as $pdtDetailsDt){
				$resultarr["productDetails"][] = $pdtDetailsDt;
			}
			//$appprdarr_details;
			 

			$unitarr=array();
			$unitnamedetailsarr=array();
			//$appUnit=$model->applicationunit;
			$appUnit=$model->applicationunitall;
			
			$unitdetailsarr = [];
			if(count($appUnit)>0)
			{
				foreach($appUnit as $unit)
				{
					 
					if(is_array($excludeunits) && count($excludeunits)>0){
						//print_r($excludeunits);
						if( !in_array($unit->id, $excludeunits)){

							continue;
						}
					}				
					
					$unitarr = $unit->toArray();
					$unitarr["unit_type_name"]=$unit->unit_type_list[$unit->unit_type];
					
					$unitarr["state_id_name"]=($unit->state_id!="")?$unit->state->name:"";
					$unitarr["country_id_name"]=($unit->country_id!="")?$unit->country->name:"";
					//$unitarr["unit_type"]=$unit->unit_type;

					$unitnamedetailsarr[$unit->id] = $unit->name;

					$unitprd=$unit->unitproduct;
					if(count($unitprd)>0)
					{
						$unitprdidsarr=array();						
						foreach($unitprd as $unitP)
						{
							$unitprdarr=array();
							//$unitprdarr[]=($unitP->product?$unitP->product->name:'');
							//$unitprdarr['pdt_index']=$pdt_index_list[$unitP->product_id];
							$unitprdarr['pdt_id']=$unitP->application_product_standard_id;
							$unitprdidsarr[]=$unitP->application_product_standard_id;							

							$unitarr["products"][]=$unitprdarr;
							$unitarr["product_details"][]=(isset($appprdarr_details[$unitP->application_product_standard_id]) ? $appprdarr_details[$unitP->application_product_standard_id] : '');
						}
						//pdt_index
												
						$unitarr["product_ids"]=$unitprdidsarr;
					}	
					
					//standards
					$unitstdidsarr=array();
					$unitstddetailssarr=array();
					$unitappstandard=$unit->unitappstandard;
					if(count($unitappstandard)>0)
					{
						foreach($unitappstandard as $unitstd)
						{
							$unitstddetailssarrtemp = [];
							$unitstdidsarr[]=$unitstd->standard_id;
							
							$unitstddetailssarrtemp['id']=$unitstd->standard_id;
							$unitstddetailssarrtemp['name']=$unitstd->standard->name;

							$unitstddetailssarr[]=$unitstddetailssarrtemp;
						}
					}
					$unitarr["standards"]=$unitstdidsarr;
					$unitarr["standarddetails"]=$unitstddetailssarr;					
 					
					$unitdetailsarr[]=$unitarr;
				}
				$resultarr["units"]=$unitdetailsarr;
			}
			
		}

		return $resultarr;
	}
	
	public function actionGetAppunitdata()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$data['unit_type'] = [2,3];
			$unitarr = Yii::$app->globalfuns->getAppunitdata($data);
			$responsedata=array('status'=>1,'unitdata'=>$unitarr);
		}
		return $this->asJson($responsedata);
	}

	public function actionCommonUpdate()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data)
		{
			if(isset($data['id']) && $data['id']>0){
				$Withdraw = Withdraw::find()->where(['id'=>$data['id']])->one();

				if($Withdraw !== null){
					if(!Yii::$app->userrole->isValidApplication($Withdraw->app_id))
					{
						return false;
					}
					$additiounit = WithdrawUnit::find()->where(['withdraw_id' => $Withdraw->id])->all();
					foreach($additiounit  as $unitobj){
						WithdrawUnitProduct::deleteAll(['withdraw_unit_id'=>$unitobj->id]);
						$unitobj->delete();
					}
					$Withdraw->delete();
					$responsedata=array('status'=>1,'message'=>'Product deleted successfully');
				}				
			}
		}		
		return $this->asJson($responsedata);
	}	 	
}
