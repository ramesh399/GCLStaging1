<?php

namespace app\modules\certificate\controllers;

use Yii;

use yii\web\NotFoundHttpException;
use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\AuditPlanUnit;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationUnitStandard;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationCertifiedByOtherCB;

use app\modules\master\models\User;
use app\modules\master\models\UserCompanyInfo;

use app\modules\master\models\Standard;

use app\modules\offer\models\Offer;
use app\modules\invoice\models\Invoice;

use app\modules\audit\models\AuditPlanUnitExecution;
use app\modules\audit\models\AuditPlanReviewer;
use app\modules\audit\models\AuditPlanUnitDate;
use app\modules\audit\models\AuditPlanUnitAuditor;
use app\modules\audit\models\AuditPlanUnitStandard;
use app\modules\audit\models\AuditPlanUnitAuditorDate;
use app\modules\audit\models\AuditPlanUnitStandardAuditor;
use app\modules\audit\models\AuditPlanInspection;
use app\modules\audit\models\AuditPlanInspectionPlan;
use app\modules\master\models\BusinessSectorGroup;

use app\modules\audit\models\AuditPlanHistory;
use app\modules\audit\models\AuditPlanUnitHistory;
use app\modules\audit\models\AuditPlanUnitDateHistory;
use app\modules\audit\models\AuditPlanUnitStandardHistory;
use app\modules\audit\models\AuditPlanUnitAuditorHistory;
use app\modules\audit\models\AuditPlanUnitAuditorDateHistory;
use app\modules\audit\models\AuditPlanInspectionHistory;
use app\modules\audit\models\AuditPlanInspectionPlanHistory;
use app\modules\audit\models\AuditPlanReviewHistory;
use app\modules\audit\models\AuditPlanReviewChecklistCommentHistory;
use app\modules\audit\models\AuditPlanUnitReviewChecklistCommentHistory;
use app\modules\application\models\ApplicationUnitBusinessSector;
use app\modules\application\models\ApplicationUnitBusinessSectorGroup;
use app\modules\application\models\ApplicationProductCertificateTemp;
use app\modules\application\models\ApplicationProductHistory;
use app\modules\application\models\ApplicationProductMaterialHistory;
use app\modules\application\models\ApplicationProductStandardHistory;


use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;

use app\modules\certificate\models\Certificate;
use app\modules\certificate\models\CertificateFiles;
use app\modules\certificate\models\CertificateReviewer;
use app\modules\certificate\models\CertificateStatusReview;

use app\modules\changescope\models\ProductAddition;


class GenerateCertificateController extends \yii\rest\Controller
{
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
		$data = Yii::$app->request->post();		
		if($data)
		{
			$certificatemodel = new Certificate();
			$applicationmodel  = new Application();
			
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$is_headquarters =$userData['is_headquarters'];
			$franchiseid=$userData['franchiseid'];
			
			$audit_plan_id=isset($data['audit_plan_id'])?$data['audit_plan_id']:''; //$data['audit_plan_id'];
			$audit_id=$data['audit_id'];
			$standard_id=$data['standard_id'];
			$type=$data['type'];
			$certificate_id=$data['certificate_id'];
			
			$connection = Yii::$app->getDb();
			$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
			
			$model = Certificate::find()->alias('cert')->where(['cert.id' => $certificate_id,'cert.standard_id'=>$standard_id]);
			$model = $model->join('left join', 'tbl_certificate_reviewer as cert_reviewer','cert_reviewer.certificate_id=cert.id');
			$model = $model->join('left join', 'tbl_audit as t','t.id =cert.audit_id');
			$model = $model->join('inner join', 'tbl_application as app','t.app_id=app.id');		
						
			if($resource_access != 1){
				if($user_type== 1  && ! in_array('certification_management',$rules)){
					return $responsedata;
				}else if($user_type==3 && $is_headquarters!=1){
					if($resource_access == '5'){
						$userid = $franchiseid;
					}
					$model = $model->andWhere('((app.franchise_id="'.$userid.'" or app.created_by="'.$userid.'" ) and cert.certificate_status=0 )');
				}else if($user_type==2){
					$model = $model->andWhere(' app.customer_id="'.$userid.'"  and cert.certificate_status=0 ');	
				}			
			}		
			
			if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1)
			{
				$model = $model->andWhere('(app.franchise_id="'.$franchiseid.'")');
			}		
						
			if($user_type==Yii::$app->params['user_type']['user'] && (in_array('certification_review',$rules) && !in_array('view_certificate',$rules)))
			{
				$model = $model->andWhere('cert_reviewer.user_id="'.$userid.'"');
			}
			$model = $model->one();
			if($model !== null)
			{
				
				if($type=='pdf' && $model->status==$certificatemodel->arrEnumStatus['certification_in_process'])
				{
					$certificatemodel->generateCertificate($certificate_id,false);
					
				}elseif($model->status==$certificatemodel->arrEnumStatus['certificate_generated'] || $model->status==$certificatemodel->arrEnumStatus['extension'] || $model->status==$certificatemodel->arrEnumStatus['expired']){
					
					//$CertificateModel = new Certificate();				
					//$certificate_id=$data['certificate_id'];				
					//$connection = Yii::$app->getDb();		
					//$command = $connection->createCommand("SELECT cer.filename AS filename FROM `tbl_certificate` AS cer  where cer.id='".$certificate_id."' AND cer.status='".$CertificateModel->arrEnumStatus['certificate_generated']."' AND cer.standard_id='".$standard_id."'");

					//$result = $command->queryOne();
					//if($result  !== false)
					//{	
						if($model->filename!='')
						{
							header('Access-Control-Allow-Origin: *');
							header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
							header('Access-Control-Max-Age: 1000');
							header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
							
							$filepath=Yii::$app->params['certificate_files'].$model->filename;
							
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
						}	
					//}
				}
			}				
		}
		die;
    }
	
	public function auditCertRelation($model)
	{
		$model = $model->join('left join', 'tbl_audit as t','t.id =cert.audit_id');				
		$model = $model->join('inner join', 'tbl_application as app','t.app_id=app.id');
	}
	
	public function appAddressCertRelation($model)
	{
		$model = $model->join('inner join', 'tbl_application_change_address as appaddress','appaddress.id=app.address_id');
	}
	
	public function actionCertificateGenerationList()
    {
        $responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$post = yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];

		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelInvoice = new Invoice();
		$modelOffer = new Offer();
		$modelAudit = new Audit();
		$modelAuditPlan = new AuditPlan();
		$modelCertificate = new Certificate();
		$modelApplication = new Application();
		$connection = Yii::$app->getDb();
		$connection->createCommand("SET SESSION group_concat_max_len = 1000000;")->execute();
		$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();
		
		$model = Certificate::find()->alias('cert');
		$model = $model->andWhere('(cert.status<="'.$modelCertificate->arrEnumStatus['certification_in_process'].'" or cert.status="'.$modelCertificate->arrEnumStatus['certified_by_other_cb'].'")');
		
		
		$appJoinWithStatus=false;		
		if($resource_access != 1){
			$appJoinWithStatus=true;
			$this->auditCertRelation($model);
			if($user_type== 1  && ! in_array('certification_management',$rules)){
				return $responsedata;
			}else if($user_type==3 && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$model = $model->andWhere('(app.franchise_id="'.$userid.'" or app.created_by="'.$userid.'")');
			}else if($user_type==2){
				$model = $model->andWhere('app.customer_id="'.$userid.'"');	
			}			
		}
		
		if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1){
			if(!$appJoinWithStatus)
			{
				$appJoinWithStatus=true;
				$this->auditCertRelation($model);
			}
			$model = $model->andWhere('(app.franchise_id="'.$franchiseid.'")');
		}	
		
		if($user_type==Yii::$app->params['user_type']['user'] && in_array('certification_review',$rules))
		{
			$model = $model->join('left join', 'tbl_certificate_reviewer as cert_reviewer','cert_reviewer.certificate_id=cert.id');
			$model = $model->andWhere('((cert.status="'.$modelCertificate->arrEnumStatus['open'].'" or cert.status="'.$modelCertificate->arrEnumStatus['certified_by_other_cb'].'" or cert.status is null) or (cert_reviewer.user_id="'.$userid.'" and cert.status!='.$modelCertificate->arrEnumStatus['certificate_generated'].'))');
		}

		$model = $model->andWhere('(cert.status!='.$modelCertificate->arrEnumStatus['certificate_generated'].')');	
		
		if(isset($post['statusFilter'])  && $post['statusFilter']!='')
		{
			$model = $model->andWhere(['cert.status'=> $post['statusFilter']]);			
		}
		
		$appAddressJoinWithStatus=false;
		if(isset($post['countryFilter'])  && $post['countryFilter']!='' && count($post['countryFilter'])>0)
		{
			if(!$appJoinWithStatus)
			{
				$appJoinWithStatus=true;
				$this->auditCertRelation($model);
			}
			$appAddressJoinWithStatus=true;
			$this->appAddressCertRelation($model);
			
			$model = $model->andWhere(['appaddress.country_id'=> $post['countryFilter']]);			
		}
		
		if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
		{
			$model = $model->andWhere(['cert.standard_id'=> $post['standardFilter']]);		
		}			

		$model = $model->groupBy(['cert.id']);
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm'] !='')
			{	
				if(!$appJoinWithStatus)
				{					
					$this->auditCertRelation($model);
				}
				
				if(!$appAddressJoinWithStatus)
				{			
					$this->appAddressCertRelation($model);
				}
			
				$searchTerm = $post['searchTerm'];
				
				$model = $model->andFilterWhere([
					'or',
					['like', 'appaddress.company_name', $searchTerm],
					['like', 'appaddress.first_name', $searchTerm],
					['like', 'appaddress.last_name', $searchTerm],
					['like', 'appaddress.telephone', $searchTerm],						
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
				$model = $model->orderBy(['cert.id' => SORT_DESC]);
			}
			



            $model = $model->limit($pageSize)->offset($page);
		}
		else
		{
			$totalCount = $model->count();
		}
		
		$app_list=array();
		$model = $model->all();	
		if(count($model)>0)
		{
			foreach($model as $offer)
			{
				$data=array();
				
				//$data['id']=($offer->audit)?$offer->audit->id:'';
				//$data['certificate_status_name']=($offer->audit)?$offer->audit->arrStatus[$offer->audit->status]:'Open';
				//$data['certificate_status']=($offer->audit)?$offer->audit->status:0;
				
				//$data['id']=$offer->id;
				//$data['certificate_status_name']=$offer->arrStatus[$offer->status];
				//$data['certificate_status']=$offer->status;
				
				//$data['id']=($offer->audit)?$offer->audit->id:'';
				//$data['certificate_status_name']=($offer->audit)?$offer->audit->arrStatus[$offer->audit->status]:'Open';
				//$data['certificate_status']=($offer->audit)?$offer->audit->status:0;
				
				
				//$data['id']=($offer->certificate)?$offer->certificate->id:'';
				$data['id']=$offer->audit?$offer->audit->id:'';
				$data['certificate_status_name']=($offer)?$offer->arrStatus[$offer->status]:'Open';
				$data['certificate_status']=($offer)?$offer->status:0;	
				$data['certificate_id']=($offer)?$offer->id:0;
				
				$data['cb_certification_body'] = '';
				$data['cb_validity_date'] = '';
				$data['cb_certification_file'] = '';
				$data['show_certification_body'] = 0;
				
				$certificatebyotherbody = $offer->certificatebyotherbody;
				if($certificatebyotherbody && $offer->status == $offer->arrEnumStatus['certified_by_other_cb'])
				{					
					$data['cb_certification_id'] = $certificatebyotherbody->id;
					$data['cb_certification_body'] = $certificatebyotherbody->cb?$certificatebyotherbody->cb->name:'';
					$data['cb_validity_date'] = $certificatebyotherbody->validity_date!=''?date($date_format,strtotime($certificatebyotherbody->validity_date)):'';
					$data['cb_certification_file'] = $certificatebyotherbody->certification_file;
					$data['show_certification_body'] = 1;
				}		

				//$audit_type = $offer->audit->application->audit_type;
				//$additiontype = $offer->product_addition_id!='' && $offer->product_addition_id>0?'Product Addition':$modelApplication->arrAuditType[$audit_type];
				$data['type_label'] = isset($offer->arrType[$offer->type])?$offer->arrType[$offer->type]:'NA';//$additiontype;
				//$data['invoice_id']=$offer->id;
				$data['app_id']=$offer->audit?$offer->audit->app_id:'';
				//$data['offer_id']=($offer->audit)?$offer->audit->id:'';
				//$data['currency']=($offer)?$offer->offerlist->currency:'';
				$data['company_name']=($offer && $offer->audit)?$offer->audit->application->companyname:'';
				
				$data['email_address']=($offer && $offer->audit)?$offer->audit->application->emailaddress:'';
				$data['customer_number']=($offer && $offer->audit)?$offer->audit->application->customer->customer_number:'';
				
				//$data['invoice_number']=$offer->invoice_number;
				//$data['total_payable_amount']=$offer->total_payable_amount;
				//$data['tax_amount']=$offer->tax_amount;				
				//$data['creator']=$offer->username->first_name.' '.$offer->username->last_name;
				//$data['payment_status_name']=($offer->payment_status!='' )?$modelInvoice->paymentStatus[$offer->payment_status]:'Payment Pending';
				//$data['created_at']=date('M d,Y h:i A',$offer->created_at);
				$data['created_at']=date($date_format,$offer->created_at);
				$data['application_standard']=$offer->standard?$offer->standard->code:'';
				$data['version']=$offer->version;

				$arrAppStd=array();				
				if($offer && $offer->audit)
				{
					$appobj = $offer->audit->application;
					
					$data['application_unit_count']=count($appobj->applicationunit);
					$data['application_country']=$appobj->countryname;
					$data['application_city']=$appobj->city;
					
					
					/*
					$appStd = $appobj->applicationstandard;
					if(count($appStd)>0)
					{	
						foreach($appStd as $app_standard)
						{
							$arrAppStd[]=$app_standard->standard->code;
						}
					}
					
					$data['application_standard']=implode(', ',$arrAppStd);
					*/
				}			
				
				$app_list[]=$data;
			}
		}
		
		$audit = new Audit;
		return ['listauditplan'=>$app_list,'total'=>$totalCount,'arrEnumStatus'=>$audit->arrEnumStatus];
	}


	public function actionDownloadFile(){
		$data = Yii::$app->request->post();
		if($data)
		{
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$is_headquarters =$userData['is_headquarters'];
			$franchiseid=$userData['franchiseid'];
		
			$files = ApplicationCertifiedByOtherCB::find()->alias('t')->where(['t.id'=>$data['id']]);
			$model = $files->join('inner join', 'tbl_application as app','t.app_id=app.id');
			
			if($resource_access != 1){
				if($user_type== 1  && ! in_array('certification_management',$rules)){
					return $responsedata;
				}else if($user_type==3 && $is_headquarters!=1){
					if($resource_access == '5')
					{
						$userid = $franchiseid;
					}
					$model = $model->andWhere('(app.franchise_id="'.$userid.'" or app.created_by="'.$userid.'")');
				}else if($user_type==2){
					$model = $model->andWhere('app.customer_id="'.$userid.'"');	
				}			
			}
			$files = $files->one();
			if($files!==null)
			{			
				$file = $files->certification_file;
				
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
				header('Access-Control-Max-Age: 1000');
				header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

				$filepath=Yii::$app->params['certifiedbyothercb_file'].$file;
				if(file_exists($filepath)) 
				{
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
			}	
		}	
		die;
	}

	public function actionDownloadCbfile()
	{
		$data = Yii::$app->request->post();
		if($data)
		{
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$is_headquarters =$userData['is_headquarters'];
			$franchiseid=$userData['franchiseid'];
			
			$model = Certificate::find()->alias('cert')->where(['cert.id'=>$data['id']]);
			$model = $model->join('left join', 'tbl_certificate_reviewer as cert_reviewer','cert_reviewer.certificate_id=cert.id');
			$model = $model->join('left join', 'tbl_audit as t','t.id =cert.audit_id');
			$model = $model->join('inner join', 'tbl_application as app','t.app_id=app.id');		
						
			if($resource_access != 1){
				if($user_type== 1  && ! in_array('certification_management',$rules)){
					return $responsedata;
				}else if($user_type==3 && $is_headquarters!=1){
					if($resource_access == '5'){
						$userid = $franchiseid;
					}
					$model = $model->andWhere('(app.franchise_id="'.$userid.'" or app.created_by="'.$userid.'")');
				}else if($user_type==2){
					$model = $model->andWhere('app.customer_id="'.$userid.'"');	
				}			
			}		
			
			if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1)
			{
				$model = $model->andWhere('(app.franchise_id="'.$franchiseid.'")');
			}	
			$model = $model->one();		
			$file = $model->cb_file;
			if($file!==null)
			{		
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
				header('Access-Control-Max-Age: 1000');
				header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

				
				$filepath=Yii::$app->params['certifiedbyothercb_file'].$file;
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
			}	
		}	
		die;
	}

	public function actionReviewStatus()
    {
		if(!Yii::$app->userrole->hasRights(array('certification_review')))
		{
			return false;
		}
		
		
		
		$reviewarray = [];
		$reviewmodel = new CertificateStatusReview();
		$data = Yii::$app->request->post();
		if($data)
		{
			if($data['status']==4)
			{
				$reviewarray = array_slice($reviewmodel->arrStatus,4,1,true);
			}
			else 
			{
				$reviewarray = array_slice($reviewmodel->arrStatus,0,4);
			}			
			$reviewarray[$reviewmodel->arrEnumStatus['expired']]=$reviewmodel->arrStatus[$reviewmodel->arrEnumStatus['expired']];
		}		
		return ['data'=>$reviewarray];
	}
	
	public function actionSaveCb()
    {
		if(!Yii::$app->userrole->hasRights(array('certification_review')))
		{
			return false;
		}
		
		$certificatemodel = new Certificate();
		$datapost = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');

		if ($datapost) 
		{
			$data =json_decode($datapost['formvalues'],true);

			$certModel = Certificate::find()->where(['id' => $data['id']])->one();
			$certModel->cb_reason = $data['cb_reason'];
			$certModel->cb_date = date('Y-m-d',strtotime($data['cb_date']));
			$certModel->cb_reason = $data['cb_reason'];
			$certModel->status = $certificatemodel->arrEnumStatus['open'];
			$target_dir = Yii::$app->params['certifiedbyothercb_file']; 

			if(isset($_FILES['cb_file']['name']))
			{
				$tmp_name = $_FILES["cb_file"]["tmp_name"];
				$name = $_FILES["cb_file"]["name"];
				$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
			}else{
				$filename = $data['cb_file'];
			}
			$certModel->cb_file = $filename;

			if($certModel->validate() && $certModel->save())
			{
				$responsedata = array('status'=>1,'message'=>'Saved successfully');
			}	
		}
		return $this->asJson($responsedata);
	}

	public function actionView()
    {
		$certificatemodel=new Certificate();
		$certificatereviewmodel=new CertificateStatusReview();
		$auditmodel=new Audit();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$data = Yii::$app->request->post();
		
		if ($data) 
		{
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$is_headquarters =$userData['is_headquarters'];
			$franchiseid=$userData['franchiseid'];
			
			$resultarr=array();
			$certModel = Certificate::find()->alias('cert')->where(['cert.id' => $data['certificate_id'],'cert.status'=>array($certificatemodel->arrEnumStatus['certificate_generated'],$certificatemodel->arrEnumStatus['suspension'],$certificatemodel->arrEnumStatus['cancellation'],$certificatemodel->arrEnumStatus['withdrawn'],$certificatemodel->arrEnumStatus['extension'],$certificatemodel->arrEnumStatus['certificate_reinstate'],$certificatemodel->arrEnumStatus['expired'])]);
			$certModel = $certModel->join('left join', 'tbl_certificate_reviewer as cert_reviewer','cert_reviewer.certificate_id=cert.id');
			$certModel = $certModel->join('left join', 'tbl_audit as t','t.id =cert.audit_id');
			$certModel = $certModel->join('inner join', 'tbl_application as app','t.app_id=app.id');		
						
			if($resource_access != 1){
				if($user_type== 1  && ! in_array('certification_management',$rules)){
					return $responsedata;
				}else if($user_type==3 && $is_headquarters!=1){
					if($resource_access == '5'){
						$userid = $franchiseid;
					}
					$certModel = $certModel->andWhere('((app.franchise_id="'.$userid.'" or app.created_by="'.$userid.'")  and cert.certificate_status=0 )');
				}else if($user_type==2){
					$certModel = $certModel->andWhere('app.customer_id="'.$userid.'" and cert.certificate_status=0 ');	
				}			
			}		
			
			if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1)
			{
				$certModel = $certModel->andWhere('(app.franchise_id="'.$franchiseid.'")');
			}		
						
			if($user_type==Yii::$app->params['user_type']['user'] && (in_array('certification_review',$rules) && !in_array('view_certificate',$rules)))
			{
				//$certModel = $certModel->andWhere('cert_reviewer.user_id="'.$userid.'"');
				$certModel = $certModel->andWhere('((cert.status="'.$certificatemodel->arrEnumStatus['open'].'" or cert.status="'.$certificatemodel->arrEnumStatus['certified_by_other_cb'].'" or cert.status is null) or (cert_reviewer.user_id="'.$userid.'" and cert.status!='.$certificatemodel->arrEnumStatus['certificate_generated'].'))');
			}
			$certModel = $certModel->one();
			
			$resultarr['certificate_generated_date'] = 'NA';
			$resultarr['certificate_valid_until'] = 'NA';


			if ($certModel !== null)
			{
				
				//$resultarr["certificate_status_label"]=$certModel->status;
				//$resultarr["certificate_status_label"]=$certificatemodel->arrStatus[$certModel->status];
				
				$resultarr['certificate_status_name']=$certificatemodel->arrStatus[$certModel->status];
				$resultarr['certificate_status']=$certModel->status;				
															
				$resultarr['certificate_generated_date']=date($date_format,strtotime($certModel->certificate_generated_date));
				$resultarr['certificate_valid_until']=date($date_format,strtotime($certModel->certificate_valid_until));
				
				$resultarr['creator']=$certModel->generatedby->first_name.' '.$certModel->generatedby->last_name;
				$resultarr['created_at']=date($date_format,$certModel->created_at);				
			}
			if(isset($data['certificate_id']) && $data['certificate_id']>0){
				$certdetailModel = Certificate::find()->where(['id' => $data['certificate_id']])->one();
				if($certdetailModel!==null){
					$canManageproductType = [
						$certdetailModel->arrEnumType['normal']
						,$certdetailModel->arrEnumType['renewal']
						,$certdetailModel->arrEnumType['standard_addition']
						,$certdetailModel->arrEnumType['product_addition']
					];
					$resultarr["show_certificate_selection"] = in_array($certdetailModel->type,$canManageproductType)?1:0; 

					$resultarr['certificate_id']=$certdetailModel->id;
					$resultarr['product_addition_id']=$certdetailModel->product_addition_id;
					$resultarr['certificate_reviewer_status']=$certdetailModel->reviewer ? 1 : 0;	
					$resultarr['certificate_review_status']=$certdetailModel->certificatereview ? 1 : 0;	
					$resultarr['certificate_status']=$certdetailModel->status;	
					$resultarr['certificate_status_name']=$certdetailModel->arrStatus[$certdetailModel->status];	
					$resultarr["certificate_reviewer_id"]=($certdetailModel->reviewer)?$certdetailModel->reviewer->user_id:'';
					$resultarr['version']=$certdetailModel->version;
					$resultarr['extension_date']=$certdetailModel->extension_date?date($date_format,strtotime($certdetailModel->extension_date)):"";
					$resultarr['extension_by']=$certdetailModel->extension_by?$certdetailModel->extensionby->first_name." ".$certdetailModel->extensionby->last_name:"";
					$resultarr['version']=$certdetailModel->version;
					$resultarr['cb_reason']=$certdetailModel->cb_reason?$certdetailModel->cb_reason:'';	
					$resultarr['cb_date']=$certdetailModel->cb_date?date($date_format,strtotime($certdetailModel->cb_date)):'';	
					$resultarr['cb_file']=$certdetailModel->cb_file?$certdetailModel->cb_file:'';	
					
					$resultarr['standard_id']=$certdetailModel->standard_id;
					$resultarr['standard_name']=$certdetailModel->standard?$certdetailModel->standard->code:'';
					$resultarr['standard_label']=$certdetailModel->standard?$certdetailModel->standard->name:'';
					$resultarr['certificate_created_at']=$certdetailModel->created_at?date($date_format,$certdetailModel->created_at):'';
					$resultarr['type_label'] = isset($certdetailModel->arrType[$certdetailModel->type])?$certdetailModel->arrType[$certdetailModel->type]:'NA';
				}

				$reviewmodel = $certdetailModel->reviewcertificate;
				if(count($reviewmodel)>0)
				{
					$reviewarray = [];
					foreach($reviewmodel as $review)
					{
						$reviewdata = [];
						$reviewdata['status'] = $certificatereviewmodel->arrStatus[$review->status];	
						$reviewdata['comment'] = $review->comment;	
						$reviewdata['user_id_label'] = $review->user->first_name." ".$review->user->last_name;
						$reviewdata['created_by_label'] = $review->createdby->first_name." ".$review->createdby->last_name;	
						$reviewdata['extension_date'] = $review->extension_date?date($date_format,strtotime($review->extension_date)):"";
						$reviewdata['created_at'] = date($date_format,$review->created_at);
						$reviewarray[] = $reviewdata;
					}

					$resultarr["reviews"] = $reviewarray;
				}
			}
			
			
			$connection = Yii::$app->getDb();
			
			$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$franchiseid=$userData['franchiseid'];
			$is_headquarters =$userData['is_headquarters'];
			
			$modelModel = Audit::find()->where(['t.id' => $data['id']])->alias('t');			

			$modelModel = $modelModel->groupBy(['t.id']);
			$modelModel = $modelModel->one();
			if ($modelModel !== null)
			{

				$CertificateModel = new Certificate();
				$connection = Yii::$app->getDb();
				$certFileList = [];
				/*
				$command = $connection->createCommand("SELECT cer_file.standard_id,cer_file.filename AS filename FROM `tbl_certificate` AS cer 
				INNER JOIN `tbl_certificate_files` AS cer_file ON cer_file.certificate_id=cer.id AND cer.audit_id='".$data['id']."' AND cer.status='".$CertificateModel->arrEnumStatus['certificate_generated']."'");
				*/
				$command = $connection->createCommand("SELECT cer.standard_id,cer.filename AS filename FROM `tbl_certificate` AS cer where cer.id='".$data['certificate_id']."' ");
				//AND cer.status='".$CertificateModel->arrEnumStatus['certificate_generated']."'
				$result = $command->queryAll();
				if(count($result)>0)
				{	
					foreach($result as $certfile)
					{
						$certFileList[$certfile['standard_id']] = $certfile['filename'];
					}

				}
				$resultarr["certFileList"]=$certFileList;


				$resultarr["arrEnumStatus"]=$modelModel->arrEnumStatus;
				
				$resultarr["app_id"]=$modelModel->app_id;
				$resultarr["status"]=$modelModel->status;
				$resultarr["apiUrl"]=Yii::$app->params['site_path'];
				//this.auditPlanData
				

				$resultarr["status_name"]=isset($auditmodel->arrStatus[$modelModel->status])?$auditmodel->arrStatus[$modelModel->status]:'Open';
				$resultarr["created_by"]=$modelModel->created_by;
				$resultarr["created_by_name"]=$modelModel->created_by?$modelModel->user->first_name.' '.$modelModel->user->last_name:'';
				$resultarr["created_at"]=date($date_format,$modelModel->created_at);
				
				//$resultarr["certificate_status"]=($modelModel->certificate)?$modelModel->certificate->status:'0';
				//$resultarr["certificate_status_name"]=($modelModel->certificate)?$modelModel->certificate->arrStatus[$modelModel->certificate->status]:'Open';
				$resultarr["arrEnumCertificateStatus"]=$certificatemodel->arrEnumStatus;	
				
				$company_name=$modelModel->application->companyname;
				$address=$modelModel->application->address;
				$zipcode=$modelModel->application->zipcode;
				$city=$modelModel->application->city;
				$country_name=$modelModel->application->countryname;
				$state_name=$modelModel->application->statename;
				$resultarr["company_name"]=$company_name;
				$resultarr["address"]=$address;
				$resultarr["zipcode"]=$zipcode;
				$resultarr["city"]=$city;
				$resultarr["country_name"]=$country_name;
				$resultarr["state_name"]=$state_name;
				
				$resultarr["app_id"]=$modelModel->app_id;
				$resultarr["offer_id"]=$modelModel->offer_id;
				$resultarr["invoice_id"]=$modelModel->invoice_id;

				
				$auditID = $modelModel->id;
				$resultarr["audit_id"]=$auditID;
				$model = AuditPlan::find()->where(['audit_id' => $auditID]);
				/*
				$model = $model->innerJoinWith(['auditplanunit as auditplanunit']);
				$model = $model->join('inner join', 'tbl_audit_plan_unit_auditor as unit_auditor','auditplanunit.id=unit_auditor.audit_plan_unit_id');
				if($user_type== Yii::$app->params['user_type']['user']  && in_array('audit_execution',$rules) && $modelModel->auditplan->application_lead_auditor !== $userid){
					$model = $model->andWhere('unit_auditor.user_id='.$userid);
				}
				*/

				$model = $model->one();
				
				if ($model !== null)
				{

					
					$resultarr["arrEnumPlanStatus"]=$model->arrEnumStatus;
					$resultarr["plan_status"]=$model->status;
					$resultarr["plan_status_name"]=isset($model->arrStatus[$model->status])?$model->arrStatus[$model->status]:'';
					
					$resultarr["id"]=$model->id;
					$resultarr["reviewer_id"]=($model->reviewer)?$model->reviewer->reviewer_id:'';
					//$model->audit_id;
					$resultarr["application_lead_auditor_name"]=$model->application_lead_auditor?$model->user->first_name.' '.$model->user->last_name:'';

					$resultarr["application_lead_auditor"]=$model->application_lead_auditor;
					$resultarr["quotation_manday"]=$model->quotation_manday;
					$resultarr["actual_manday"]=$model->actual_manday;
					//$resultarr["status"]=$model->status;
					$resultarr["created_at"]=date($date_format,$model->created_at);
					
					
					
					$product_addition_status=0;
					$withdraw_unit_status=0;
					if($certdetailModel!==null)
					{
						if($certdetailModel->type==$certdetailModel->arrEnumType['withdraw_unit']){
							$withdraw_unit_status=1;
							$unitWithdrawObj = $certdetailModel->withdraw->applicationaddress;
							$company_name=$unitWithdrawObj->company_name;
							$address=$unitWithdrawObj->address;
							$zipcode=$unitWithdrawObj->zipcode;
							$city=$unitWithdrawObj->city;
							$country_name=$unitWithdrawObj->country->name;
							$state_name=$unitWithdrawObj->state->name;							
						}elseif($certdetailModel->type==$certdetailModel->arrEnumType['product_addition']){
							$product_addition_status=1;							
							$appAddressObj = $certdetailModel->productaddition->applicationaddress;
							$company_name=$appAddressObj->company_name;
							$address=$appAddressObj->address;
							$zipcode=$appAddressObj->zipcode;
							$city=$appAddressObj->city;
							$country_name=$appAddressObj->country->name;
							$state_name=$appAddressObj->state->name;							
						}						
					}
					
					


					$auditplanUnit=$model->auditplanunit;
					if(count($auditplanUnit)>0)
					{
						$showCertificateGenerate = 1;
						$auditexestatus = new AuditPlanUnitExecution();
						$totalAuditSubtopic = true;
						$unitarr=array();
						$unitnamedetailsarr=array();
						$unitIds = [];
						$planunitIds = [];
						foreach($auditplanUnit as $unit)
						{
							$unitIds[] = $unit->unit_id;
							$planunitIds[] = $unit->id;

							$unitsarr=array();
							if($model->status == $model->arrEnumStatus['review_completed']){
								$command = $connection->createCommand("SELECT * FROM tbl_audit_plan_unit_execution as exe INNER JOIN tbl_audit_plan_unit_execution_checklist checklist on 
								checklist.audit_plan_unit_execution_id=exe.id where exe.audit_plan_unit_id =".$unit->id." and checklist.answer=2 ");
								$result = $command->queryAll();
								if(count($result)>0){
									$showCertificateGenerate = 0;
								}
								
							}else{
								$showCertificateGenerate =0;
							}
							$chkAuditorIds = [];
							$unitauditors=$unit->unitauditors;
							if(count($unitauditors)>0)
							{
								$unitaudarr=array();
								$unitauditorsarr=array();
								$leadauditor=array();
								foreach($unitauditors as $auditors)
								{
									$audarr=array();
									$audarr['id']=$auditors->id;
									$audarr['user_id']=$auditors->user->id;
									$audarr['display_name']=$auditors->user->first_name." ".$auditors->user->last_name;
									$audarr['is_lead_auditor']=$auditors->is_lead_auditor;
									$chkAuditorIds[] = $auditors->user->id;
									$auditordate=$auditors->auditplanunitauditordate;
									if(count($auditordate)>0)
									{
										$datearr=array();
										foreach($auditordate as $stdauditordate)
										{
											if($stdauditordate->lead_auditor_other_date == 0){
												$datearr[]=date($date_format,strtotime($stdauditordate['date']));
											}
										}
										$audarr['date']=$datearr;
									}
									
									$unitaudarr[]=$audarr;
									if($auditors->is_lead_auditor==1)
									{
										$leadauditor[]=$auditors->user->id;
									}
									
									

								}
								
								$unitsarr["auditors"]=$unitaudarr;
								$unitsarr["auditorIds"]=$chkAuditorIds;
							}
							
							$auditexe = AuditPlanUnitExecution::find()->where(['audit_plan_unit_id'=>$unit->id])->all();
							$auditsubtopiccount = count($auditexe);
							$subtopicArr = [];
							
							$unitsubtopics=array();							
							
							$unitsarr['id']=$unit->id;
							$unitsarr['unit_id']=$unit->unit_id;
							$unitsarr['unit_name']=$unit->unitdata->name;
							$unitsarr['unit_lead_auditor']=$unit->unitleadauditor->first_name." ".$unit->unitleadauditor->last_name;
							$unitsarr['unit_lead_auditor_id']=$unit->unit_lead_auditor;
							$unitsarr['technical_expert_id']=$unit->technical_expert;
							$unitsarr['translator_id']=$unit->translator;
							$unitsarr['observer']=($unit->observer!='' ? $unit->observer : 'NA');
							$unitsarr['technical_expert']=($unit->unittechnicalexpert)?$unit->unittechnicalexpert->first_name." ".$unit->unittechnicalexpert->last_name:'';
							$unitsarr['translator']=($unit->unittranslator)?$unit->unittranslator->first_name." ".$unit->unittranslator->last_name:'';

							$unitsarr['quotation_manday']=$unit->quotation_manday;
							$unitsarr['actual_manday']=$unit->actual_manday;
							$unitsarr['status']=$unit->status;
							$unitsarr['subtopics']= $subtopicArr;
							$unitsarr['subtopics_count']= $auditsubtopiccount;
							
							// ------ Findings Count Start Here -------	
							$executionlistallObj = $unit->executionlistall;
							$executionlistall=count($executionlistallObj);
														
							$executionlistnoncomformityObj = $unit->executionlistnoncomformity;
							$executionlistnoncomformity=count($executionlistnoncomformityObj);
																	
							$unitsarr['total_findings']= $executionlistall;
							$unitsarr['total_non_conformity']= $executionlistnoncomformity;
							// ------ Findings Count End Here -------
							
							$unitsarr['status_label'] = $unit->arrStatus[$unit->status];
							$unitStatusChangeDate = $unit->status_change_date;
							$unitsarr["status_change_date"]= ($unitStatusChangeDate!='' ? date($date_format,$unitStatusChangeDate) : 'NA');

							if(count($unitsubtopics) != $auditsubtopiccount){
								$totalAuditSubtopic = false;
							}

							$unitnamedetailsarr[$unit->unit_id] = $unit->unitdata->name;

							$unitdate=$unit->auditplanunitdate;
							$unitdatearr=array();
							if(count($unitdate)>0)
							{	
								foreach($unitdate as $unitd)
								{
									$unitdatearr[]=date($date_format,strtotime($unitd->date));
									//echo $date_format.'--'.$unitd->date;
								}
							}
							$unitsarr["date"]=$unitdatearr;

							$unitstd=$unit->unitstandard;
							if(count($unitstd)>0)
							{	
								$unitstdarr=array();
								foreach($unitstd as $unitS)
								{
									$stdsarr=array();
									$stdsarr['id']=$unitS->id;
									$stdsarr['standard_id']=$unitS->standard_id;
									$stdsarr['standard_name']=$unitS->standard->code;
									$unitstdarr[]=$stdsarr;
								}
							}
							$unitsarr["standard"]=$unitstdarr;

							
							
							$unitarr[]=$unitsarr;
						}

						$showSubmitRemediationForAuditor = 0;
						$showSubmitRemediationForReviewer = 0;

						$showSendBackRemediationToCustomer = 0;
						$showSendBackRemediationToAuditor = 0;

						$arrChecklistStatusCnt[0] = 0;
						$arrChecklistStatusCnt[1] = 0;
						$arrChecklistStatusCnt[2] = 0;
						$arrChecklistStatusCnt[3] = 0;
						$arrChecklistStatusCnt[4] = 0;
						$arrChecklistStatusCnt[5] = 0;
						
						$command = $connection->createCommand("SELECT COUNT(checklist.id) as chkcnt,checklist.status as status FROM 
										tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.unit_id IN (".implode(',',$unitIds).") AND checklist.answer=2 GROUP BY checklist.status"); // AND checklist.status = 1
						$result = $command->queryAll();
						$totalChkFinding =0;
						
						if(count($result )>0){
							foreach($result  as $statuschklist){
								$arrChecklistStatusCnt[$statuschklist['status']] = $statuschklist['chkcnt'];
								$totalChkFinding += $statuschklist['chkcnt'];
							}
						}
						
						if($model->status == $model->arrEnumStatus['remediation_in_progress']){
							

							/*
							$command2 = $connection->createCommand("SELECT * FROM  tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.unit_id IN (".implode($unitIds).") AND checklist.answer=2 
										 AND checklist.status in (1,3,4,5) "); // AND checklist.status = 1
							$result2 = $command2->queryAll();
							*/
							$totchk = $arrChecklistStatusCnt[1] + $arrChecklistStatusCnt[3] + $arrChecklistStatusCnt[4] + $arrChecklistStatusCnt[5];

							if($totalChkFinding ==  $totchk){
								$showSubmitRemediationForAuditor = 1;
							}
							
						}

						if($model->status == $model->arrEnumStatus['auditor_review_in_progress']){
							
							/*$command2 = $connection->createCommand("SELECT * FROM  tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.unit_id IN (".implode($unitIds).") AND checklist.answer=2
										  AND checklist.status IN (3,5) "); // AND checklist.status = 1
							$result2 = $command2->queryAll();*/

							$totchk = $arrChecklistStatusCnt[3] + $arrChecklistStatusCnt[5];

							if($arrChecklistStatusCnt[1]<=0 && $arrChecklistStatusCnt[2]<=0 && $totalChkFinding ==  $totchk){
								$showSubmitRemediationForReviewer = 1;
							}
						}

						if($model->status == $model->arrEnumStatus['reviewer_review_in_progress']){
							/*$command2 = $connection->createCommand("SELECT * FROM  tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.unit_id IN (".implode($unitIds).") AND checklist.answer=2
										  AND checklist.status IN (4,5) "); // AND checklist.status = 1
							$result2 = $command2->queryAll();
							*/
							$totchk = $arrChecklistStatusCnt[4] + $arrChecklistStatusCnt[5];

							if($arrChecklistStatusCnt[4] > 0 && $totalChkFinding ==  $totchk){
								$showSendBackRemediationToAuditor = 1;
							}
						}

						if($model->status == $model->arrEnumStatus['auditor_review_in_progress']){
							
							/*$command2 = $connection->createCommand("SELECT * FROM  tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.unit_id IN (".implode($unitIds).") AND checklist.answer=2
										  AND checklist.status IN (2,3,5) "); // AND checklist.status = 1
							$result2 = $command2->queryAll();
							*/
							$totchk = $arrChecklistStatusCnt[2] + $arrChecklistStatusCnt[3] + $arrChecklistStatusCnt[5];
							if($arrChecklistStatusCnt[2] > 0 && $totalChkFinding ==  $totchk){
								$showSendBackRemediationToCustomer = 1;
							}
						}





						$resultarr["showSubmitRemediationForAuditor"]=$showSubmitRemediationForAuditor;
						$resultarr["showSubmitRemediationForReviewer"]=$showSubmitRemediationForReviewer;
						$resultarr["showSendBackRemediationToCustomer"]=$showSendBackRemediationToCustomer;
						$resultarr["showSendBackRemediationToAuditor"]=$showSendBackRemediationToAuditor;

						//$unitIds
						$resultarr["showCertificateGenerate"]=$showCertificateGenerate;
						$resultarr["units"]=$unitarr;							
						$auditplanunitmodel=new AuditPlanUnit();
						$resultarr["arrUnitEnumStatus"]=$auditplanunitmodel->arrEnumStatus;
					}
					
					$auditinspection=$model->auditplaninspection;
					if($auditinspection!==null)
					{	
						//$auditinspectionarr=array();
						$planarr=array();
						$auditinspectionplan=$auditinspection->auditplaninspectionplan;
						foreach($auditinspectionplan as $arr)
						{
							$temparr=array();
							$temparr["inspection_id"]=$arr->id;
							$temparr["application_unit_name"]=($arr->applicationunit!==null ? $arr->applicationunit->name : 'NA');
							$temparr["activity"]=$arr->activity;
							$temparr["inspector"]=$arr->inspector;
							$temparr["inspector"]=$arr->inspector;
							$temparr["date"]=date($date_format,strtotime($arr->date));
							$temparr["start_time"]=date('G:i', strtotime($arr->start_time));
							$temparr["end_time"]=date('G:i', strtotime($arr->end_time));
							$temparr["person_need_to_be_present"]=$arr->person_need_to_be_present;
							$temparr["application_unit_id"]=($arr->applicationunit!==null ? $arr->applicationunit->id : 'NA');
							$temparr["application_unit_name"]=($arr->applicationunit!==null ? $arr->applicationunit->name : 'NA');							
							$planarr[]=$temparr;
						}
						//$auditinspectionarr[]=$planarr;											
						$resultarr["inspectionplan"]=$planarr;
					}


					$auditreviews=[];
					$reviewarr=[];
					$reviewcommentarr=[];
					$review=$model->auditplanreview;
					if($review !== null)
					{
						//foreach($auditReview as $review)
						if(1)
						{
							$reviewarr=[];
							$reviewcommentarr=[];
							$auditreviewcmt=$review->auditplanreviewchecklistcomment;
							if(count($auditreviewcmt)>0)
							{
								foreach($auditreviewcmt as $reviewComment)
								{
									$reviewcommentarr[]=array('question'=>$reviewComment->question,'answer'=>$reviewComment->auditplanreviewanswer->name,'comment'=>$reviewComment->comment);
								}	
							}
							

							$unitreviews=[];
							$unitreviewarr=[];
							$unitreviewcommentarr=[];
							$unitauditreviewcmt=$review->auditplanunitreviewcomment;
							if(count($unitauditreviewcmt)>0)
							{
								foreach($unitauditreviewcmt as $unitreviewComment)
								{
									$unitreviewcommentarr[$unitreviewComment->unit_id][] = array(
											'question'=>$unitreviewComment->question,'answer'=>$unitreviewComment->auditplanreviewanswer->name,'comment'=>$unitreviewComment->comment
										);
											
											
								}	
								//print_r($unitnamedetailsarr); die;
								foreach($unitreviewcommentarr as $unitkey => $units)
								{
									if(isset($unitnamedetailsarr[$unitkey]))
									{
										$unitreviews[]=array('unit_name'=>$unitnamedetailsarr[$unitkey],'unit_id'=>$unitkey,'reviews'=>$units);
									}
								}
							}
							
							
							
							$reviewarr['reviewcomments']=$reviewcommentarr;
							$reviewarr['unitreviewcomments']=$unitreviews;
							$reviewarr['reviewer']=($review->reviewer?$review->reviewer->first_name.' '.$review->reviewer->last_name:'');
							$reviewarr['answer']=$review->answer;
							
							$reviewarr['answer_name']=$review->answer?$review->arrReviewAnswer[$review->answer]:'NA';
							
							$reviewarr['status']=$review->status;		
							$reviewarr['status_name']=$review->arrReviewStatus[$review->status];					
																	
							$reviewarr['created_at']=date($date_format,$review->created_at);

							$reviewarr['status_comments']=$review->comment;
							$reviewarr['review_result_name']=isset($review->arrReviewResult[$review->review_result])?$review->arrReviewResult[$review->review_result]:'';
							$reviewarr['review_result']=$review->review_result;

							$auditreviews[]=$reviewarr;
						}
						$resultarr["auditreviews"]=$auditreviews;
					}
					$resultarr['totalAuditSubtopicAnswered']=isset($totalAuditSubtopic)?$totalAuditSubtopic:0;					
				}

				$checklistreviews=[];
				$certificatemodel = Certificate::find()->where(['id' => $data['certificate_id']])->one();
				if ($certificatemodel !== null)
				{
					$checklistarr = [];
					$certificatereview = $certificatemodel->certificatereview;
					if($certificatereview  !== null){
						$checklistarr['reviewer'] = ($certificatereview->reviewer?$certificatereview->reviewer->first_name.' '.$certificatereview->reviewer->last_name:'');
						$checklistarr['answer']=$certificatereview->risk_category;
						$checklistarr['answer_name']=$certificatereview->risk_category?$certificatereview->riskcategory->name:'NA';
						$checklistarr['risk_comments']=$certificatereview->comment;
						$checklistarr['created_at']=date($date_format,$certificatereview->created_at);


						$reviewanswers = $certificatereview->certificatereviewerreview;

						if(count($reviewanswers)>0)
						{
							foreach($reviewanswers as $reviewComment)
							{
								$checklistcommentarr[]=array('question'=>$reviewComment->question,'answer'=>$reviewComment->auditrevieweranswer->name,'comment'=>$reviewComment->comment);
							}
							$checklistarr['checklistcomments'] = $checklistcommentarr;
						}
					}
					
					 

					

					$checklistreviews = $checklistarr;

					$resultarr["checklistreviews"]=$checklistreviews;


				}

				$arr_history_data=array();								
				$resultarr["history"]=$arr_history_data;			
			}
			return $resultarr;			
		}
	}
	
	private function getSubtopic($unit_id,$audit_plan_unit_id='',$userid=''){
		//$query = '';
		// AND aeq.sub_topic_id IS NULL
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		
		$connection = Yii::$app->getDb();
		$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();

		$condition = '';
		
		if($unit_id){
			$condition .= " AND unit.id=".$unit_id;
		}
		if($userid){
			$condition .= " AND ( execution.executed_by is null OR execution.executed_by=".$userid.")";
		}
		$command = $connection->createCommand("SELECT user.first_name,user.last_name,execution.status,execution.executed_by,execution.executed_date,subtopic.id,
			subtopic.name FROM `tbl_application_unit` AS unit 
			INNER JOIN `tbl_application_unit_process` AS uprs ON unit.id=uprs.unit_id 
			INNER JOIN `tbl_application_unit_standard` AS ustd ON unit.id=ustd.unit_id 
			INNER JOIN `tbl_audit_execution_question_process` AS aeqp ON uprs.process_id=aeqp.process_id 
			INNER JOIN `tbl_audit_execution_question_standard` AS aeqs ON ustd.standard_id=aeqs.standard_id 
			INNER JOIN `tbl_audit_execution_question` AS aeq ON aeqp.audit_execution_question_id=aeq.id 
			INNER JOIN `tbl_sub_topic` AS subtopic ON subtopic.id=aeq.sub_topic_id 

			LEFT JOIN `tbl_audit_plan_unit_execution` AS execution on execution.sub_topic_id = subtopic.id and execution.audit_plan_unit_id=".$audit_plan_unit_id." 
			LEFT JOIN `tbl_users` AS user on user.id = execution.executed_by  
			WHERE 1=1  ".$condition." and aeq.status=0
			
			GROUP BY subtopic.id");
		$result = $command->queryAll();
		//$dataArr = [];
		/*if(count($result)>0){
			foreach($result as $subdata){
				$dataArr[] =['id'=>$subdata['id'],'name'=>$subdata['name']];
			}
		}
		*/
		//$responsedata =['status'=>1,'data'=>$dataArr];
		

		return $result;

	}
	
	public function actionCertificateStatus()
	{
		$data = Yii::$app->request->post();

		$certificatemodel = new Certificate();

		if(isset($data['type']) && $data['type']=='list'){
			//$arrStatus = array_splice($certificatemodel->arrStatus,2);
			$arrStatus = $certificatemodel->arrStatus;
			unset($arrStatus['0']);
			unset($arrStatus['1']);
		}else{
			$arrStatus = $certificatemodel->arrStatus;
		}	
		
		return ['status'=>$arrStatus];
	}

	public function actionSaveReview()
	{
		if(!Yii::$app->userrole->hasRights(array('certification_review')))
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		$ModelApplicationStandard = new ApplicationStandard();
		if ($data) 
		{
			$certificatemodel = Certificate::find()->where(['id'=>$data['certificate_id']])->one();
			if($data['status']==0)
			{
				$certificatemodel->status = $certificatemodel->arrEnumStatus['suspension'];//4;
				$certificatemodel->certificate_status = 1;

				$ApplicationStandard = ApplicationStandard::find()->where(['app_id'=>$certificatemodel->parent_app_id,'standard_id'=>$certificatemodel->standard_id,'standard_status'=>0])->one();
				if($ApplicationStandard !== null)
				{
					$ApplicationStandard->standard_status = $ApplicationStandard->arrEnumStatus['suspension'];
					$ApplicationStandard->save();
				}
								

			}
			else if($data['status']==1)
			{
				$certificatemodel->status = $certificatemodel->arrEnumStatus['cancellation']; //5;
				$certificatemodel->certificate_status = 1;				

				$datas = ['standard_id'=>$certificatemodel->standard_id,'app_id'=>$certificatemodel->parent_app_id,'status'=>$ModelApplicationStandard->arrEnumStatus['cancellation'] ];
				$certificatemodel->applicationStandardDecline($datas);
			}
			else if($data['status']==2)
			{
				$certificatemodel->status = $certificatemodel->arrEnumStatus['withdrawn']; //6;
				$certificatemodel->certificate_status = 1;

				$datas = ['standard_id'=>$certificatemodel->standard_id,'app_id'=>$certificatemodel->parent_app_id,'status'=>$ModelApplicationStandard->arrEnumStatus['withdrawn'] ];
				$certificatemodel->applicationStandardDecline($datas);
			}
			else if($data['status']==3)
			{
				$certificatemodel->certificate_status = 0;
				$certificatemodel->status = $certificatemodel->arrEnumStatus['extension']; //7;
				$certificatemodel->extension_date = isset($data['extension_date']) && $data['extension_date'] !=''?date('Y-m-d',strtotime($data['extension_date'])):"";
				$certificatemodel->extension_by = $userid;
				
				// ---- Update Certificate Valid Until-----------
				$certificatemodel->certificate_valid_until = $certificatemodel->extension_date;
				$certificatemodel->save();
			}
			else if($data['status']==4)
			{
				$ModelApplicationStandard = new ApplicationStandard();
				$certificatemodel->status =  $certificatemodel->arrEnumStatus['certificate_generated'];//2;
				$certificatemodel->certificate_status = 0;
				//echo $certificatemodel->parent_app_id.'=='.$certificatemodel->standard_id.'=='.$ApplicationStandard->arrEnumStatus['suspension'];
				
				//return ['app_id'=>$certificatemodel->parent_app_id,'standard_id'=>$certificatemodel->standard_id,'standard_status'=>$ApplicationStandard->arrEnumStatus['suspension'] ];
				//print_r(['app_id'=>$certificatemodel->parent_app_id,'standard_id'=>$certificatemodel->standard_id,'standard_status'=>$ApplicationStandard->arrEnumStatus['suspension'] ]);
				$ApplicationStandard = ApplicationStandard::find()->where(['app_id'=>$certificatemodel->parent_app_id,'standard_id'=>$certificatemodel->standard_id,'standard_status'=>$ModelApplicationStandard->arrEnumStatus['suspension'] ])->one();
				if($ApplicationStandard !== null){
					$ApplicationStandard->standard_status = $ApplicationStandard->arrEnumStatus['valid'];
					$ApplicationStandard->save();
					//$ApplicationStandard->getErrors();
				}
			}else if($data['status']==5){
				$certificatemodel->status = $certificatemodel->arrEnumStatus['expired']; //8;
				$certificatemodel->certificate_status = 1;
				
				$datas = ['standard_id'=>$certificatemodel->standard_id,'app_id'=>$certificatemodel->parent_app_id,'status'=>$ModelApplicationStandard->arrEnumStatus['expired'] ];
				$certificatemodel->applicationStandardDecline($datas);
			}

			if($certificatemodel->validate() && $certificatemodel->save())
			{
				$reviewmodel = new CertificateStatusReview();
				$reviewmodel->certificate_id = $data['certificate_id'];
				$reviewmodel->user_id = $userid;
				$reviewmodel->comment = $data['comment'];
				$reviewmodel->extension_date = isset($data['extension_date']) && $data['extension_date']!=''?date('Y-m-d',strtotime($data['extension_date'])):"";
				$reviewmodel->status = $data['status'];
				$reviewmodel->created_by = $userid;
				$reviewmodel->save();

				$responsedata = array('status'=>1,'message'=>'Review Saved successfully');
			}
		}
		return $this->asJson($responsedata);
	}

	public function actionGenerateCertificate()
	{
		if(!Yii::$app->userrole->hasRights(array('certification_review')))
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		
		if ($data) 
		{
			$applicationmodel = new Application();
			$certificatemodel = new Certificate();
			
			$audit_plan_id=isset($data['audit_plan_id'])?$data['audit_plan_id']:'';

			$certificate_id=$data['certificate_id'];
			
			$audit_id=isset($data['audit_id'])?$data['audit_id']:''; //$data['audit_id'];
			$connection = Yii::$app->getDb();
			$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
			$model = Certificate::find()->where(['id'=>$certificate_id])->one();
			if ($model !== null)
			{
				/*
				$certificateModel = Certificate::find()->where(['audit_id'=>$audit_id,'status'=>$model->arrEnumStatus['certificate_generated'] ])->one();
				if ($certificateModel === null)
				{
					$certificate_generate_date = date("Y-m-d",time());					
					$futureDate = date('Y-m-d', strtotime('+1 year', strtotime($certificate_generate_date)) );
					$certificate_expiry_date = date('Y-m-d', strtotime('-1 day', strtotime($futureDate)));
				}else{
					$certificate_generate_date = $certificateModel->certificate_generated_date;					
					$certificate_expiry_date = $certificateModel->certificate_valid_until;	
				}
				*/

				$parent_app_id = $model->audit->application->parent_app_id;
				$audit_type = $model->audit->application->audit_type;

				//$parent_app_id = $model->application->parent_app_id;
				//$audit_type = $model->application->audit_type;

				
				


				$certificate_generate_date='';
				$certificate_valid_until='';
				$actual_certificate_valid_until='';
				$certificatemodel = new Certificate();
				if(($model->product_addition_id !='' && $model->product_addition_id>0) || ($audit_type!=$applicationmodel->arrEnumAuditType['standard_addition'] && $audit_type!=$applicationmodel->arrEnumAuditType['renewal'] && $audit_type!=$applicationmodel->arrEnumAuditType['normal'] ))
				{
					/*
					//$certModel = Certificate::find()->where(['audit_id' => $data['audit_id'],'status'=>$certificatemodel->arrEnumStatus['certificate_generated']])->one();
					$certModel = Certificate::find()->where(['parent_app_id' => $parent_app_id,'standard_id'=>$model->standard_id,'certificate_status'=> $model->arrEnumCertificateStatus['valid'] ])->one();
					$UpdatecertModel = Certificate::find()->where(['parent_app_id' => $parent_app_id,'standard_id'=>$model->standard_id,'certificate_status'=> $model->arrEnumCertificateStatus['valid'] ])->orderBy(['id' => SORT_DESC])->one();
					if ($certModel !== null && $product_addition_id!='')
					{					
						//$certificate_generate_date = date('d/F/Y',strtotime($certModel->certificate_generated_date));					
						//$certificate_expiry_date = date('d/F/Y', strtotime($certModel->certificate_valid_until));
						
						$certificate_generate_date = $certModel->certificate_generated_date;
						$actual_certificate_valid_until = $certModel->actual_certificate_valid_until;						
						$certificate_expiry_date = $certModel->certificate_valid_until;
						
					}elseif($parent_app_id!='' && $parent_app_id!='0'){
						
						$AuditM = Audit::find()->where(['app_id'=>$parent_app_id])->one();
						if($AuditM!== null)
						{
							
							$certModel = $AuditM->certificate;
							//$certificate_generate_date = date('d/F/Y',strtotime($certModel->certificate_generated_date));					
							//$certificate_expiry_date = date('d/F/Y', strtotime($certModel->certificate_valid_until));	
							
							$certificate_generate_date = $certModel->certificate_generated_date;
							$actual_certificate_valid_until = $certModel->actual_certificate_valid_until;							
							$certificate_expiry_date = $certModel->certificate_valid_until;
						}
					}
					*/
					
			
					$getCertifiedDateModel = Certificate::find()->where(['parent_app_id' => $model->parent_app_id,'standard_id'=>$model->standard_id,'certificate_status'=>0,'status'=>array($certificatemodel->arrEnumStatus['certificate_generated'],$certificatemodel->arrEnumStatus['extension'])])->orderBy(['id' => SORT_DESC])->one();
					if($getCertifiedDateModel !== null )
					{
						$certificate_generate_date = date("Y-m-d",time());
						$certificate_generate_date = date('Y-m-d',strtotime($certificate_generate_date));						
						$certificate_valid_until = $getCertifiedDateModel->certificate_valid_until;
						$actual_certificate_valid_until = $getCertifiedDateModel->actual_certificate_valid_until;
					}
				}				
				
				$version =1;
				$applicationdetails = $model->audit->application;
				if(($model->product_addition_id !='' && $model->product_addition_id>0) || ($applicationdetails !==null && $applicationdetails->audit_type !=$applicationdetails->arrEnumAuditType['renewal'] )){
				//&& $applicationdetails->audit_type !=$applicationdetails->arrEnumAuditType['standard_addition']
					if($model->product_addition_id !='' && $model->product_addition_id>0){
						$parent_app_id = $model->parent_app_id;
					}else{
						$parent_app_id = $applicationdetails->parent_app_id;
					}
					
					if($parent_app_id !='' && $parent_app_id>0){
						$CertificateExist = Certificate::find()->where(['parent_app_id'=>$parent_app_id,'standard_id'=>$model->standard_id ])->orderBy(['version' => SORT_DESC])->one();
						if($CertificateExist!==null){
							$version = $CertificateExist->version;
							$version = $version+1;
						}
					}
				}
								
				if($certificate_generate_date=='' && $certificate_valid_until=='')
				{
					$certificate_generate_date = date("Y-m-d",time());	
					
					$futureDate = date('Y-m-d', strtotime('+1 year', strtotime($certificate_generate_date)) );
					$certificate_generate_date = date('Y-m-d',strtotime($certificate_generate_date));
					
					$certificate_valid_until = date('Y-m-d', strtotime('-1 day', strtotime($futureDate)));	
					$actual_certificate_valid_until = $certificate_valid_until;
				}							
				
				$userData = Yii::$app->userdata->getData();	
				$model->version = $version;	
				$model->certificate_generated_date = $certificate_generate_date;
				$model->actual_certificate_valid_until = $actual_certificate_valid_until;				
				$model->certificate_valid_until = $certificate_valid_until;				
				$model->certificate_generated_by=$userData['userid'];				
				$model->updated_at=time();
				$model->updated_by=$userData['userid'];
				$model->status = $model->arrEnumStatus['certificate_generated'];
				$model->certificate_status = $model->arrEnumCertificateStatus['valid'];				
				if($model->save())
				{
					if($model->type == $model->arrEnumType['renewal'] || $model->type == $model->arrEnumType['normal'])
					{
						$ModelApplicationStandard = new ApplicationStandard();
						$changestatusval = $ModelApplicationStandard->arrEnumStatus['draft_certificate'];
						$validstatus = $ModelApplicationStandard->arrEnumStatus['valid'];
						$ChangeApplicationStandard = ApplicationStandard::find()->where(['standard_id'=>$model->standard_id,'app_id'=>$model->parent_app_id,'standard_status'=>$changestatusval ])->one();
						if($ChangeApplicationStandard !== null){
							$ChangeApplicationStandard->standard_status = $validstatus;
							$ChangeApplicationStandard->save();
						}
					}
					


					// ------ Update Application Overall Status Update Code Start Here ------
					$auditmodel = $model->audit;
					if($auditmodel !== null)
					{
						$appModel=new Application();
						Yii::$app->globalfuns->updateApplicationOverallStatus($auditmodel->app_id, $appModel->arrEnumOverallStatus['certificate_generated']);


					}
					if($model->product_addition_id !='' && $model->product_addition_id>0)
					{

						$productmodel = ProductAddition::find()->where(['id' => $model->product_addition_id])->one();
						if($productmodel !==null){
							$productmodel->status = $productmodel->arrEnumStatus['certification_generated'];
							$productmodel->save();
						}
						/*
						$clonedata = ['app_id'=>$auditmodel->app_id,'product_addition_id'=>$model->product_addition_id, 'standard_id'=>$model->standard_id];
						//print_r($clonedata);
						$appModelclone=new Application();
						$appModelclone->cloneApplicationProduct($clonedata);
						*/
					}
					
					
					// ------ Update Application Overall Status Update Code End Here ------
					
					/*
					$audit = Audit::find()->where(['id'=>$model->audit_id])->one();
					if($audit!==null){
						$audit->status = $audit->arrEnumStatus['generate_certificate'];
						$audit->updated_at = time();
						$audit->save();
					}
					*/	

					$certificatemodel->generateCertificate($model->id,true);
					
					if(isset($getCertifiedDateModel) && $getCertifiedDateModel !== null)
					{
						$getCertifiedDateModel->certificate_status = 1;
						$getCertifiedDateModel->save();
					}			
						
					$responsedata = ['status'=>1,'message'=>'Certificate can be downloaded for this audit successfully','data'=>[]];
				}
					
			}
		}
		return $responsedata;
	}
	
	public function auditRelation($model)
	{
		$model = $model->join('inner join', 'tbl_audit as audit','audit.id =t.audit_id');		
		$model = $model->join('inner join', 'tbl_application as app','audit.app_id=app.id');
	}
	
	public function appAddressRelation($model)
	{
			$model = $model->join('inner join', 'tbl_application_change_address as appaddress','appaddress.id=app.address_id');
	}
	
	public function actionCertificateList()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        $post = yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];

		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelInvoice = new Invoice();
		$modelOffer = new Offer();
		$modelAudit = new Audit();
		$modelAuditPlan = new AuditPlan();
		
		$modelCertificate = new Certificate();
		$modelApplication = new Application();
		
		$connection = Yii::$app->getDb();
		$connection->createCommand("SET SESSION group_concat_max_len = 1000000;")->execute();
		$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();

		$model = Certificate::find()->where(['t.status'=>array($modelCertificate->arrEnumStatus['certificate_generated'],$modelCertificate->arrEnumStatus['suspension'],$modelCertificate->arrEnumStatus['cancellation'],$modelCertificate->arrEnumStatus['withdrawn'],$modelCertificate->arrEnumStatus['extension'],$modelCertificate->arrEnumStatus['certificate_reinstate'],$modelCertificate->arrEnumStatus['declined'],$modelCertificate->arrEnumStatus['expired'])])->alias('t');
			
		//$model = $model->join('inner join', 'tbl_application_standard as app_standard','app_standard.app_id =app.id ');
		
		$auditJoinWithStatus=false;
		if($resource_access != 1){
			$auditJoinWithStatus=true;
			$this->auditRelation($model);
			if($user_type== 1 && ! in_array('certification_management',$rules) && ! in_array('audit_management',$rules) ){
				return $responsedata;
			}else if($user_type==3 && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$model = $model->andWhere('((app.franchise_id="'.$userid.'" or app.created_by="'.$userid.'")  and t.certificate_status=0 )');
			}else if($user_type==2){
				$model = $model->andWhere('app.customer_id="'.$userid.'" and t.certificate_status=0  ');	
			}else if($user_type==1 && in_array('view_brand',$rules) && $is_headquarters==1){
				$model = $model->join('inner join','tbl_application_brands as appbrand','app.id=appbrand.app_id');
				$model = $model->join('inner join','tbl_brands as bran','bran.id=appbrand.brand_id');
				$model = $model->andWhere(['bran.user_id'=>$userid])->andWhere(['appbrand.status'=>$modelApplication->arrBrandEnumStatus['approved']]); 
			}else if($user_type==1 && in_array('brand_report',$rules) && $is_headquarters==1){
				$model = $model->join('inner join','tbl_application_brands as appbrand','app.id=appbrand.app_id');
				$model = $model->join('inner join','tbl_brands as bran','bran.id=appbrand.brand_id');
				$model = $model->join('inner join','tbl_brand_group as bg','bran.brand_group_id=bg.id');
				$model = $model->andWhere(['bg.user_id'=>$userid])->andWhere(['appbrand.status'=>$modelApplication->arrBrandEnumStatus['approved']]); 
			}	
		}	

		if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1){
			if(!$auditJoinWithStatus){
				$this->auditRelation($model);
			}
			$model = $model->andWhere('(app.franchise_id="'.$franchiseid.'")');
		}
		
		if($user_type==Yii::$app->params['user_type']['user'] && (in_array('certification_review',$rules) && !in_array('view_certificate',$rules)))
		{
			$model = $model->join('left join', 'tbl_certificate_reviewer as cert_reviewer','cert_reviewer.certificate_id=t.id');
			$model = $model->andWhere('cert_reviewer.user_id="'.$userid.'"');
		}		
		
		$appAddressJoinWithStatus=false;
		if(isset($post['countryFilter'])  && $post['countryFilter']!='' && count($post['countryFilter'])>0)
		{
			if(!$auditJoinWithStatus)
			{
				$auditJoinWithStatus=true;
				$this->auditRelation($model);
			}
			
			$appAddressJoinWithStatus=true;
			$this->appAddressRelation($model);
			$model = $model->andWhere(['appaddress.country_id'=> $post['countryFilter']]);			
		}
		
		if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
		{
			$model = $model->andWhere(['t.standard_id'=> $post['standardFilter']]);		
		}
		
		if(isset($post['statusFilter'])  && $post['statusFilter']!='')
		{		
			$model = $model->andWhere(['t.status'=> $post['statusFilter']]);		
		}

		if(isset($post['from_date']))
		{
			$from_date = date("Y-m-d",strtotime($post['from_date']));
			$model = $model->andWhere(['>=','t.certificate_valid_until',$from_date]);			
		}
		
		if(isset($post['to_date']))
		{
			$to_date = date("Y-m-d",strtotime($post['to_date']));
			$model = $model->andWhere(['<=','t.certificate_valid_until', $to_date]);			
		}

		$model = $model->groupBy(['t.id']);
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm'] !='')
			{	
				if(!$auditJoinWithStatus)
				{
					$this->auditRelation($model);
				}
			
				if(!$appAddressJoinWithStatus){
					$this->appAddressRelation($model);
				}
			
				$searchTerm = $post['searchTerm'];

				$model = $model->andFilterWhere([
					'or',
					['like', 'appaddress.company_name', $searchTerm],
					['like', 'appaddress.first_name', $searchTerm],
					['like', 'appaddress.last_name', $searchTerm],
					['like', 'appaddress.telephone', $searchTerm],						
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
				$model = $model->orderBy(['t.updated_at' => SORT_DESC,'t.certificate_status' => SORT_ASC]);
			}
            $model = $model->limit($pageSize)->offset($page);
		}
		else
		{
			$totalCount = $model->count();
		}
		
		$app_list=array();
		$model = $model->all();	
		if(count($model)>0)
		{
			foreach($model as $offer)
			{
				$data=array();				
				
				$data['id']=$offer->audit->id;
				$data['certificate_id']=$offer->id;
				$data['code']=$offer->code;
				//$data['certificate_status_name']=$offer->arrStatus[$offer->status];
				//$data['certificate_status_name']=$offer->arrCertificateStatus[$offer->certificate_status];
				$data['certificate_status_name']=$offer->arrCertificateStatusForList[$offer->certificate_status];
				$data['certificate_status']=$offer->status;	
				$data['version']=$offer->version;				

				$data['status_label']=$offer->arrStatus[$offer->status];
				//$audit_type = $offer->audit->application->audit_type;
				//$additiontype = $offer->product_addition_id!='' && $offer->product_addition_id>0?'Product Addition':$modelApplication->arrAuditType[$audit_type];
				$data['type_label']=isset($offer->arrType[$offer->type])?$offer->arrType[$offer->type]:'NA'; //$additiontype;

				$data['app_id']=$offer->audit->app_id;
				$data['offer_id']=($offer)?$offer->id:'';
				
				$data['company_name']=($offer)?$offer->audit->application->companyname:'';
				$data['filename']=$offer->filename;
				$data['email_address']=($offer)?$offer->audit->application->emailaddress:'';
				$data['customer_number']=($offer)?$offer->audit->application->customer->customer_number:'';
								
				$data['certificate_generated_date']=$offer->status ==$modelCertificate->arrEnumStatus['declined']?'NA':date($date_format,strtotime($offer->certificate_generated_date));
				$data['certificate_valid_until']=$offer->status ==$modelCertificate->arrEnumStatus['declined']?'NA':date($date_format,strtotime($offer->certificate_valid_until));
				
				$data['creator']=$offer->generatedby?$offer->generatedby->first_name.' '.$offer->generatedby->last_name:'';
				$data['created_at']=date($date_format,$offer->created_at);
				$data['application_standard']=$offer->standard?$offer->standard->code:'';
				
				$arrAppStd=array();				
				if($offer)
				{
					$appobj = $offer->audit->application;
					
					$data['application_unit_count']=count($appobj->applicationunit);
					$data['application_country']=$appobj->countryname;
					$data['application_city']=$appobj->city;
					/*
					$appStd = $appobj->applicationstandard;
					if(count($appStd)>0)
					{	
						foreach($appStd as $app_standard)
						{
							$arrAppStd[]=$app_standard->standard->code;
						}
					}
					
					$data['application_standard']=implode(', ',$arrAppStd);
					*/
				}			
	
				$brandname=array();
				$brandgroup=array();

						$appbrandmodel = $offer->audit->application->applicationbrands;
						if(count($appbrandmodel)>0){
							foreach($appbrandmodel as $brmod){
								if($user_type==2){
									$brandname[]=$brmod->brands->name;
									$brandgroup[]=$brmod->brands->brandgroup->name;
								}else if(in_array('brand_report',$rules)){
									if($brmod->brands->brandgroup->user_id==$userid){
										$brandname[]=$brmod->brands->name;
										$brandgroup[]=$brmod->brands->brandgroup->name;
									}
								}else if(in_array('view_brand',$rules)){
									if($brmod->brands->user_id==$userid){
										$brandname[]=$brmod->brands->name;
										$brandgroup[]=$brmod->brands->brandgroup->name;
									}
								}	
							}
						}
						$brandname = implode(', ',$brandname);
						$brandgroup = implode(', ',array_unique($brandgroup));
						$data['brand_name']=$brandname!=""?$brandname:"NA";
						$data['brand_group']=$brandgroup!=""?$brandgroup:"NA";

				$app_list[]=$data;
			}
		}
		
		$audit = new Audit;
		return ['invoices'=>$app_list,'total'=>$totalCount,'arrEnumStatus'=>$audit->arrEnumStatus];
	}

	public function actionAssignCertificationReviewer()
	{
		if(!Yii::$app->userrole->hasRights(array('certification_review')))
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data  = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data)
		{
			//$productmodel = ProductAddition::find()->where(['id' => $data['id']])->one();
			$CertificateReviewer = CertificateReviewer::find()->where(['certificate_id'=>$data['certificate_id']])->one();
			
			if($CertificateReviewer===null)
			{
				$reviewermodel = new CertificateReviewer();
				$reviewermodel->certificate_id = $data['certificate_id'];
				$reviewermodel->user_id = $userid;
				$reviewermodel->created_by = $userid;
				if($reviewermodel->validate() && $reviewermodel->save())
				{
					$CertificateExist = Certificate::find()->where(['id'=>$data['certificate_id']])->one();
					if($CertificateExist !== null){
						$CertificateExist->status = $CertificateExist->arrEnumStatus['certification_in_process'];
						$CertificateExist->save();
					}
					
					// ------ Update Application Overall Status Update Code Start Here ------
					if($CertificateExist->audit !== null)
					{
						$appModel=new Application();
						Yii::$app->globalfuns->updateApplicationOverallStatus($CertificateExist->audit->app_id, $appModel->arrEnumOverallStatus['certificate_in_process']);
					}
					// ------ Update Application Overall Status Update Code End Here ------

					if(isset($data['product_addition_id']) && $data['product_addition_id']>0)
					{
						$productmodel = ProductAddition::find()->where(['id' => $data['product_addition_id']])->one();
						if($productmodel !==null){
							$productmodel->status = $productmodel->arrEnumStatus['certification_in_process'];
							$productmodel->save();
						}
						
					}
					$responsedata=array('status'=>1,'message'=>"Assigned Successfully!",'status'=>$CertificateExist->status);
				}
			}			
		}
		return $responsedata;
	}

	

	public function getCertContent(){
		$ApplicationUnit = ApplicationUnit::find()->where(['unit_type'=>[1,2]])->all();
		if(count($ApplicationUnit)>0){

		}

		$ApplicationUnit = ApplicationUnit::find()->where(['unit_type'=> 3 ])->all();
		if(count($ApplicationUnit)>0){
			//$ApplicationUnit->unitstandard->standard->code;
		}
	}

	public function actionDownloadCertificateFile()
	{
		$data = Yii::$app->request->post();
		if($data)
		{
			$files = Certificate::find()->where(['t.id' => $data['id']])->alias('t');		
			$files = $files->one();

			if($files!==null)
			{
				$filename = $files->filename;				
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
				header('Access-Control-Max-Age: 1000');
				header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
				
				$filepath=Yii::$app->params['certificate_files'].$filename;
				if(file_exists($filepath)) 
				{
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
	}
}
