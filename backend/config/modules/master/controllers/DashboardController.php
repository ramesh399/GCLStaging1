<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationChangeAddress;
use app\modules\invoice\models\Invoice;
use app\modules\offer\models\Offer;
use app\modules\master\models\User;
use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditPlanUnit;
use app\modules\master\models\Role;
use app\modules\master\models\Standard;
use app\modules\certificate\models\Certificate;
use app\modules\transfercertificate\models\Request;

use app\modules\master\models\UserDashboard;
use app\modules\master\models\CustomerDashboard;
use app\modules\master\models\FranchiseDashboard;

use app\models\Enquiry;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;


class DashboardController extends \yii\rest\Controller
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

    public function actionCustomerDashboard()
    {
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';SET SQL_BIG_SELECTS=1")->execute();

		$appmodel=new Application();
		$invoicemodel=new Invoice();
		$certificatemodel = new certificate();
		$modelCustomerDashboard = new CustomerDashboard();
		$resultarr=array();

        $userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$is_headquarters =$userData['is_headquarters'];
        $resource_access=$userData['resource_access'];
        $date_format = Yii::$app->globalfuns->getSettings('date_format');
        		
		$modelCustomerDashboard->is_headquarters = $is_headquarters;
		$modelCustomerDashboard->resource_access = $resource_access;
		$modelCustomerDashboard->date_format = $date_format;
		$modelCustomerDashboard->rules = $rules;
		$modelCustomerDashboard->user_type = $user_type;
		$modelCustomerDashboard->role = $role;
		
		
		$res = $modelCustomerDashboard->applicationWaitingForSubmission($userid);
		if(count($res)>0)
		{
			$resultarr['pendingactions']=$res['pendingactions'];
		}
		
		$res = $modelCustomerDashboard->applicationReAssign($userid);
		if(count($res)>0)
		{
			$resultarr['re_initiate_pending_actions']=$res['re_initiate_pending_actions'];
		}
				
		$res = $modelCustomerDashboard->offerWaitingForApproval($userid);
		if(count($res)>0)
		{
			$resultarr['offer_waiting_for_approvals']=$res['offer_waiting_for_approvals'];
		}
		
		$res = $modelCustomerDashboard->auditReportWaitingForSubmission($userid);
		if(count($res)>0)
		{
			$resultarr['waiting_for_audit_report']=$res['waiting_for_audit_report'];
		}
		
		$res = $modelCustomerDashboard->auditPlanWaitingForApprovals($userid);
		if(count($res)>0)
		{
			$resultarr['audit_plan_waiting_for_approvals']=$res['audit_plan_waiting_for_approvals'];
		}

		$res = $modelCustomerDashboard->followupAuditPlanWaitingForApprovals($userid);
		if(count($res)>0)
		{
			$resultarr['followup_audit_plan_waiting_for_approvals']=$res['followup_audit_plan_waiting_for_approvals'];
		}
		
		$res = $modelCustomerDashboard->auditWaitingForRemediation($userid);
		if(count($res)>0)
		{
			$resultarr['audit_waiting_for_remediation']=$res['audit_waiting_for_remediation'];
		}


		
		$res = $modelCustomerDashboard->waitingForCustomerRejectedRemediationCorrection($userid);
		if(count($res)>0)
		{
			$resultarr['waiting_for_customer_rejected_remediation_correction']=$res['waiting_for_customer_rejected_remediation_correction'];
		}
		
		$res = $modelCustomerDashboard->waitingForAuditRenewal($userid);
		if(count($res)>0)
		{
			$resultarr['application_waiting_for_customer_audit_renewal']=$res['waiting_for_customer_audit_renewal'];
			//$resultarr['waiting_for_customer_audit_renewal']=$res['waiting_for_customer_audit_renewal'];
		}
		
		$res = $modelCustomerDashboard->productAdditionReAssign($userid);
		if(count($res)>0)
		{
			$resultarr['product_addition_reassign']=$res['product_addition_reassign'];
		}

		$res = $modelCustomerDashboard->tcReAssign($userid);
		if(count($res)>0)
		{
			$resultarr['tc_reassign']=$res['tc_reassign'];
		}

		//Unit Withdraw Re Assigned for Editing
		$res = $modelCustomerDashboard->unitWithdrawReAssign($userid);
		if(count($res)>0)
		{
			$resultarr['unit_withdraw_reassign']=$res['unit_withdraw_reassign'];
		}

		$command = $connection->createCommand("SELECT cert.id,cert.parent_app_id,cert.standard_id,cert.filename,cert.status,cert.version,cert.type,cert.certificate_status,cert.created_at FROM `tbl_certificate` AS cert
		INNER JOIN `tbl_application` AS app ON cert.certificate_status=0 AND cert.parent_app_id=app.id AND app.customer_id='$userid' GROUP BY cert.standard_id,cert.parent_app_id");
		$resultval = $command->queryAll();
		if(count($resultval)>0)
		{
			$pending_arr = array();
			foreach($resultval as $vals)
			{
				$user_arr = array();
				$user_arr['id'] = $vals['id'];
				$user_arr['standard_id'] = $vals['standard_id'];
				
				$stdmodel = Standard::find()->where(['id'=>$vals['standard_id']])->one();
				if($stdmodel!==null)
				{
					$user_arr['standard'] = $stdmodel->name." [".$stdmodel->code."]";
				}

				$user_arr['filename'] = $vals['filename'];	
				$user_arr['version']=$vals['version'];				
				$user_arr['type_label']=isset($certificatemodel->arrType[$vals['type']])?$certificatemodel->arrType[$vals['type']]:'NA';

				
				$user_arr['created_at']=date($date_format,$vals['created_at']);
				$pending_arr[] = $user_arr;
			}
			$resultarr['certificates']=$pending_arr;
		}


		$modelOffer = new Offer();
		
		$model = Offer::find()->where(['t.status'=>$modelOffer->enumStatus['finalized']])->alias('t');
		$model = $model->joinWith(['application as app']);
		$model = $model->join('inner join', 'tbl_application_change_address as appaddress','appaddress.id=app.address_id');
		$model = $model->join('left join', 'tbl_offer_list as list','list.offer_id=t.id and list.is_latest=1');
		$model = $model->join('left join', 'tbl_invoice as invoice','invoice.offer_id=t.id');
		$model = $model->join('left join', 'tbl_application_standard as app_standard','app_standard.app_id=app.id');
		$model = $model->groupBy(['t.id']);
		if(!isset($post['listype']) || $post['listype']!='offer'){
			$model = $model->andWhere('invoice.status not in ('.$invoicemodel->enumStatus['payment_pending'].') or invoice.id IS NULL');
		}
		$model = $model->andWhere('app.created_by="'.$userid.'" ');

		$app_list=array();
		
		$model = $model->all();	

		if(count($model)>0)
		{
			foreach($model as $offer)
			{
				$data=array();
				$data['id']=$offer->id;
				$data['app_id']=$offer->application->id;
				$data['company_name']=$offer->application->companyname;
				$data['standard']=$offer->standard;
				$data['invoice_id']=$offer->invoice?$offer->invoice->id:'';
				
				$arrAppStd=array();
				
				$appStd=$offer->application->applicationstandardview;
				
				if(count($appStd)>0)
				{	
					foreach($appStd as $app_standard)
					{
						$arrAppStd[]=$app_standard->standard->code;
					}
				}					
				$data['application_standard']=implode(', ',$arrAppStd);
				
				$app_list[]=$data;
			}
			$resultarr['offers']=$app_list;
		}

		$applicationmodel = Application::find()->where(['customer_id'=>$userid,'audit_type'=>$appmodel->arrEnumAuditType['normal']])->one();
		if($applicationmodel!==null)
		{
			$resultarr['app_id']= 318; //$applicationmodel->id;
		}
		

        return ['data'=>$resultarr];

    }
	
	
	public function actionUserDashboard()
    {
		$modelUserDashboard = new UserDashboard();
		$modelCustomerDashboard = new CustomerDashboard();
		$modelFranchiseDashboard = new FranchiseDashboard();
		
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
        $resource_access=$userData['resource_access'];
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelApplication = new Application();
		$modelOffer = new Offer;
		$connection = Yii::$app->getDb();
		//$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();

		$modelUserDashboard->is_headquarters = $is_headquarters;
		$modelUserDashboard->resource_access = $resource_access;
		$modelUserDashboard->date_format = $date_format;
		
		$resultarr=array();
		$enquiries=array();
		
		if($resource_access==1)
		{
			$resultarr['pending_actions_status']= false;
		}else{
			$resultarr['pending_actions_status']= true;
		}
		$resultarr['pending_actions_btn_status']= false;
		if((Yii::$app->userrole->hasRights(array('application_review')) && 
			Yii::$app->userrole->hasRights(array('audit_review')) &&
			Yii::$app->userrole->hasRights(array('certification_review')))
			|| $resource_access==1
		)
		{
			$res = $modelUserDashboard->waitingForAuditRenewal($franchiseid);
			if(count($res)>0)
			{
				$enquiries['waiting_for_customer_audit_renewal']=$res;//['waiting_for_customer_audit_renewal'];
			}
			$res = $modelUserDashboard->dueCertificate($franchiseid);
			if(count($res)>0)
			{
				$enquiries['due_certificate']=$res;//['waiting_for_customer_audit_renewal'];
			}
			$res = $modelUserDashboard->waitingForAuditNCDue($franchiseid);
			if(count($res)>0)
			{
				$enquiries['audit_nc_due']=$res;//['waiting_for_customer_audit_renewal'];
			}
			if($resource_access!=1)
			{
				$resultarr['pending_actions_btn_status']= true;
			}
		}
		
		
		//$enquiries['arr_enum_renewal_audit']=$modelUserDashboard->arrEnumRenewalAudit;
		$enquiries['arr_enum_renewal_audit']=$modelUserDashboard->arrRenewalAudit;
		
		$enquiries['arr_enum_due_certificate']=$modelUserDashboard->arrEnumDueCertificate;
		$enquiries['arr_color']=$modelUserDashboard->arrColor;
		if($resource_access==1)
		{
			
			$enquiry = new Enquiry();
			$resultarr=array();
			
			$totalenquiries=0;
			$enquirymodel = Enquiry::find()->select('status,count(*) as statusCount')->groupBy('status')->asArray()->all();
			if (count($enquirymodel)>0)
			{   
				
				foreach($enquirymodel as $enquiryStatus)
				{
					$resultarr['enquiry-count'][] = array('name'=>$enquiry->arrStatus[$enquiryStatus['status']].' ('.(int)($enquiryStatus['statusCount']).')','y'=>(int)($enquiryStatus['statusCount']),'color'=>$enquiry->arrStatusColor[$enquiryStatus['status']]);
					$totalenquiries+=$enquiryStatus['statusCount'];
				}		
				$resultarr['total-enquiry-count'] = $totalenquiries;
			}
			$enquiries['totalenquiries']=$totalenquiries;	

			

			
			
			$command = $connection->createCommand("SELECT sum(total_payable_amount) as total_payable_amount, 
			sum(total_payable_amount)/count(offer.id)  as avg_amount 
			FROM `tbl_offer` as offer inner join tbl_offer_list as offerlist on offer.id=offerlist.offer_id 
			and offer.status='".$modelOffer->enumStatus["finalized"]."' ");
			$dataarr=array();
			$resultval = $command->queryOne();
			if($resultval !== false)
			{
				$enquiries['offer_total_payable_amount']=number_format($resultval['total_payable_amount'],2);	
				$enquiries['offer_total_payable_avg_amount'] = number_format($resultval['avg_amount'],2);
			}	
			
			$usermodel = User::find()->select('count(*) as userCount')->where(['!=','status',2])->groupBy('user_type')->asArray()->all();
			if (count($usermodel)>0)
			{   
				$enquiries['totalusers']=(int)$usermodel[0]['userCount'];	
				//$enquiries['totalcustomers']=isset($usermodel[1]) ? (int)$usermodel[1]['userCount']:0;
				$enquiries['totalfranchises']=isset($usermodel[2]) ? (int)$usermodel[2]['userCount']:0;
			}
			
			$customersmodel = User::find()->select('count(*) as userCount')->where(['!=','status',2])->andWhere(['!=','customer_number',''])->asArray()->one();
			if (count($customersmodel)>0)
			{   
				$enquiries['totalcustomers']=isset($customersmodel['userCount']) ? (int)$customersmodel['userCount']:0;				
			}

			$Requestmodel = Request::find()->select('count(*) as rCount')->asArray()->all();
			if (count($Requestmodel)>0)
			{   
				$enquiries['totaltc']=(int)$Requestmodel[0]['rCount'];	
			}
			$Certificationmodel = Certificate::find()->select('count(*) as rCount')->asArray()->all();
			if (count($Certificationmodel)>0)
			{   
				$enquiries['totalcertification']=(int)$Certificationmodel[0]['rCount'];	
			}
			$Auditmodel = Audit::find()->select('count(*) as rCount')->asArray()->all();
			if (count($Auditmodel)>0)
			{   
				$enquiries['totalaudit']=(int)$Auditmodel[0]['rCount'];	
			}

			$application = new Application();
			$appmodel = Application::find()->select('status,count(*) as statusCount')->groupBy('status')->asArray()->all();
			if (count($appmodel)>0)
			{   
				$totcnt = 0;
				foreach($appmodel as $appStatus)
				{
					$resultarr['application-count'][] = array('name'=>$application->arrStatus[$appStatus['status']].' ('.(int)($appStatus['statusCount']).')','y'=>(int)($appStatus['statusCount']),'color'=>$application->arrStatusColor[$appStatus['status']]);
					$totcnt+=(int)$appStatus['statusCount'];
				}
				$resultarr['total-application-count'] = $totcnt;
			}

			$certificate = new Certificate();
			$certificatemodel = Certificate::find()->select('status,count(*) as statusCount')->groupBy('status')->asArray()->all();
			if (count($certificatemodel)>0)
			{   
				$totcnt = 0;
				foreach($certificatemodel as $certificateStatus)
				{
					$resultarr['certification-count'][] = array('name'=>$certificate->arrStatus[$certificateStatus['status']].' ('.(int)($certificateStatus['statusCount']).')','y'=>(int)($certificateStatus['statusCount']),'color'=>$certificate->arrStatusColor[$certificateStatus['status']]);
					$totcnt+=(int)$certificateStatus['statusCount'];
				}
				$resultarr['total-certification-count'] = $totcnt;
			}

			$audit = new Audit();
			$auditmodel = Audit::find()->select('status,count(*) as statusCount')
						->where(['not in','status',[$audit->arrEnumStatus['finalized_without_audit']] ])
						->groupBy('status')->asArray()->all();
			if (count($auditmodel)>0)
			{   
				$totcnt = 0;
				foreach($auditmodel as $auditStatus)
				{
					if(isset($audit->arrStatus[$auditStatus['status']])){
						$resultarr['audit-count'][] = array('name'=>$audit->arrStatus[$auditStatus['status']].' ('.(int)($auditStatus['statusCount']).')','y'=>(int)($auditStatus['statusCount']),'color'=>isset($audit->arrStatusColor[$auditStatus['status']])?$audit->arrStatusColor[$auditStatus['status']]:'');
					}
					$totcnt+=(int)$auditStatus['statusCount'];
				}
				$resultarr['total-audit-count'] = $totcnt;
			}

			$request = new Request();
			$requestmodel = Request::find()->select('status,count(*) as statusCount')->groupBy('status')->asArray()->all();
			if (count($requestmodel)>0)
			{   
				$totcnt = 0;
				foreach($requestmodel as $requestStatus)
				{
					$resultarr['tcrequest-count'][] = array('name'=>$request->arrStatus[$requestStatus['status']].' ('.(int)($requestStatus['statusCount']).')','y'=>(int)($requestStatus['statusCount']),'color'=>$request->arrStatusColor[$requestStatus['status']]);
					$totcnt+=(int)$requestStatus['statusCount'];
				}
				$resultarr['total-tcrequest-count'] = $totcnt;
			}

			
			$command = $connection->createCommand("SELECT
				SUM(CASE WHEN `certificate`.status IN('2') AND DATEDIFF(certificate.certificate_valid_until,NOW()) >=0 AND DATEDIFF(certificate.certificate_valid_until,NOW()) <=30 THEN 1 ELSE 0 END) AS RenewalLessThan0to30,
				SUM(CASE WHEN `certificate`.status IN('2') AND DATEDIFF( certificate.certificate_valid_until,NOW()) >30 AND DATEDIFF(certificate.certificate_valid_until,NOW()) <=60 THEN 1 ELSE 0 END) AS RenewalDueMoreThan30t60,
				SUM(CASE WHEN `certificate`.status IN('2') AND DATEDIFF( certificate.certificate_valid_until,NOW()) >60 AND DATEDIFF(certificate.certificate_valid_until,NOW()) <=90 THEN 1 ELSE 0 END) AS RenewalDueMoreThan60t90,
				SUM(CASE WHEN `certificate`.status IN('2') AND DATEDIFF(certificate.certificate_valid_until,NOW()) >90 AND DATEDIFF(certificate.certificate_valid_until,NOW()) <=120 THEN 1 ELSE 0 END) AS RenewalDueMoreThan90t120
				FROM `tbl_audit` AS `audit`
				INNER JOIN `tbl_certificate` AS `certificate` ON `certificate`.audit_id=`audit`.id AND `certificate`.status=2
				INNER JOIN `tbl_audit_plan` AS `auditp` ON `auditp`.audit_id=`audit`.id");
			
			$dataarr=array();
			$resultval = $command->queryOne();
			if($resultval !== false)
			{   
				//foreach($resultval as $requestStatus)
				//{
				$resultarr['renewal-audit-count'][] = array('name'=> '0 - 30 ('.(int)($resultval['RenewalLessThan0to30']).')','y'=>(int)($resultval['RenewalLessThan0to30']));
				//,'color'=>$request->arrStatusColor[$requestStatus['status']]

				$resultarr['renewal-audit-count'][] = array('name'=> '30 - 60 ('.(int)($resultval['RenewalDueMoreThan30t60']).')','y'=>(int)($resultval['RenewalDueMoreThan30t60']));
				$resultarr['renewal-audit-count'][] = array('name'=> '60 - 90 ('.(int)($resultval['RenewalDueMoreThan60t90']).')','y'=>(int)($resultval['RenewalDueMoreThan60t90']));
				$resultarr['renewal-audit-count'][] = array('name'=> '90 - 120 ('.(int)($resultval['RenewalDueMoreThan90t120']).')','y'=>(int)($resultval['RenewalDueMoreThan90t120']));

				$totcnt = $resultval['RenewalLessThan0to30']+$resultval['RenewalDueMoreThan30t60']+$resultval['RenewalDueMoreThan60t90']+$resultval['RenewalDueMoreThan90t120'];
				$resultarr['total-renewal-audit-count'] = $totcnt;
			}




			$command = $connection->createCommand("SELECT 
				SUM(CASE WHEN `certificate`.status IN(2) AND DATEDIFF(certificate.certificate_valid_until,NOW()) >=0 AND DATEDIFF(certificate.certificate_valid_until,NOW()) <=30 THEN 1 ELSE 0 END) AS CertificateDueLessThan0to30,
				SUM(CASE WHEN `certificate`.status IN(2) AND DATEDIFF(certificate.certificate_valid_until,NOW()) >30 AND DATEDIFF(certificate.certificate_valid_until,NOW()) <=60 THEN 1 ELSE 0 END) AS CertificateDueMoreThan30t60,
				SUM(CASE WHEN `certificate`.status IN(2) AND DATEDIFF(certificate.certificate_valid_until,NOW()) >60 AND DATEDIFF(certificate.certificate_valid_until,NOW()) <=90 THEN 1 ELSE 0 END) AS CertificateDueMoreThan60t90,
				SUM(CASE WHEN `certificate`.status IN(2) AND DATEDIFF(certificate.certificate_valid_until,NOW()) >90 AND DATEDIFF(certificate.certificate_valid_until,NOW()) <=120 THEN 1 ELSE 0 END) AS CertificateDueMoreThan90t120
				FROM `tbl_audit` AS `audit`
				INNER JOIN `tbl_certificate` AS `certificate` ON `certificate`.audit_id=`audit`.id AND `certificate`.status=2");
			
			$dataarr=array();
			$resultval = $command->queryOne();
			if($resultval !== false)
			{   
				$resultarr['due-certificate-count'][] = array('name'=> '0 - 30 ('.(int)($resultval['CertificateDueLessThan0to30']).')','y'=>(int)($resultval['CertificateDueLessThan0to30']));
				$resultarr['due-certificate-count'][] = array('name'=> '30 - 60 ('.(int)($resultval['CertificateDueMoreThan30t60']).')','y'=>(int)($resultval['CertificateDueMoreThan30t60']));
				$resultarr['due-certificate-count'][] = array('name'=> '60 - 90 ('.(int)($resultval['CertificateDueMoreThan60t90']).')','y'=>(int)($resultval['CertificateDueMoreThan60t90']));
				$resultarr['due-certificate-count'][] = array('name'=> '90 - 120 ('.(int)($resultval['CertificateDueMoreThan90t120']).')','y'=>(int)($resultval['CertificateDueMoreThan90t120']));


				$totcnt = $resultval['CertificateDueLessThan0to30']+$resultval['CertificateDueMoreThan30t60']+$resultval['CertificateDueMoreThan60t90']+$resultval['CertificateDueMoreThan90t120'];
				$resultarr['total-due-certificate-count'] = $totcnt;
			}
			//$resultarr['total-tcrequest-count'] = $totcnt;

			

			
			$offer = new Offer();
			$offermodel = Offer::find()->select('status,count(*) as statusCount')->groupBy('status')->asArray()->all();
			if (count($offermodel)>0)
			{   
				$totcnt = 0;
				foreach($offermodel as $offerStatus)
				{
					$resultarr['contract-count'][] = array('name'=>$offer->arrStatus[$offerStatus['status']].' ('.(int)($offerStatus['statusCount']).')','y'=>(int)($offerStatus['statusCount']),'color'=>$offer->arrStatusColor[$offerStatus['status']]);
					$totcnt+=(int)$offerStatus['statusCount'];
				}
				$resultarr['total-contract-count'] = $totcnt;
			}

			$invoice = new Invoice();
			$invoicemodel = Invoice::find()->select('status,count(*) as statusCount')->groupBy('status')->asArray()->all();
			if (count($invoicemodel)>0)
			{   
				$totcnt = 0;
				foreach($invoicemodel as $invoiceStatus)
				{
					$resultarr['invoice-count'][] = array('name'=>$invoice->arrStatus[$invoiceStatus['status']].' ('.(int)($invoiceStatus['statusCount']).')','y'=>(int)($invoiceStatus['statusCount']),'color'=>$invoice->arrStatusColor[$invoiceStatus['status']]);
					$totcnt+=(int)$invoiceStatus['statusCount'];
				}
				$resultarr['total-invoice-count'] = $totcnt;
			}

			$modelEnquiry = new Enquiry();
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			$model = Enquiry::find()->alias('t')->where(['t.status' => 1]);
			//$model = Enquiry::find();
			$model->joinWith(['companycountry as ccountry']);
			
			$enquiry_list=array();
			//$model = $model;
			$model = $model->orderBy(['id' => SORT_DESC]);
			// $model = $model->Where(['!=','status',3]);
			// $model = $model->andWhere(['!=','status',2]);	
			$model = $model->limit(10)->all();	
			if(count($model)>0)
			{
				foreach($model as $enquiry)
				{
					$data=array();
					$data['id']=$enquiry->id;
					$data['company_name']=$enquiry->company_name?:'';
					$data['contact_name']=$enquiry->contact_name?:'';
					
					$es=$enquiry->enquirystandard; 
					$eStandardArr=array();
					if(count($es)>0)
					{
						foreach($es as $enquirystandard)
						{
							$eStandardArr[]=$enquirystandard->standard->code;
						}
					}				
					$data['standards']=$eStandardArr;
					
					$data['company_telephone']=$enquiry->company_telephone;
					$data['company_email']=$enquiry->company_email;
					$data['company_country_id']=$enquiry->companycountry->name;
					$data['status']=$modelEnquiry->arrStatus[$enquiry->status];
					$data['status_label_color']=$modelEnquiry->arrStatusColor[$enquiry->status];				
					$data['created_at']=date($date_format,$enquiry->created_at);
					$enquiry_list[]=$data;   
				}
				$enquiries['enquiries']=$enquiry_list;
			}
			
			
			$command = $connection->createCommand("SELECT  user.id,user.first_name,user.last_name,company.company_name,country.name as `countryname` FROM tbl_users AS `user` LEFT JOIN tbl_user_company_info AS company ON company.user_id = user.created_by LEFT JOIN tbl_country AS `country` ON  country.id=company.company_country_id WHERE user.user_type=1 AND user.id NOT IN (SELECT user_role.user_id FROM tbl_user_role AS user_role)");
			
			$dataarr=array();
			$resultval = $command->queryAll();
			if(count($resultval)>0)
			{
				$pending_arr = array();
				foreach($resultval as $vals)
				{
					$user_arr = array();
					$user_arr['id'] = $vals['id'];
					$user_arr['name'] = $vals['first_name']." ".$vals['last_name'];
					$user_arr['franchise'] = $vals['company_name'];
					$user_arr['country'] = $vals['countryname'];
					$pending_arr[] = $user_arr;
				}
				$resultarr['pending_users']=$pending_arr;
			}


			$connection = Yii::$app->getDb();
			$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
			$command = $connection->createCommand("SELECT COUNT(id) AS enquiry_total, DATE_FORMAT(FROM_UNIXTIME(`created_at`), '%b') AS 'month',DATE_FORMAT(FROM_UNIXTIME(`created_at`), '%m') AS 'enquiry_month',DATE_FORMAT(FROM_UNIXTIME(`created_at`), '%Y') AS 'enquiry_year' FROM `tbl_enquiry` GROUP BY DATE_FORMAT(FROM_UNIXTIME(`created_at`), '%Y'),DATE_FORMAT(FROM_UNIXTIME(`created_at`), '%m') ORDER BY created_at DESC LIMIT 10");
			
			$dataarr=array();
			$result = $command->queryAll();
			if(count($result)>0)
			{
				$months=array();
				$counts=array();
				foreach($result as $values)
				{
					$counts[]=(int)$values['enquiry_total'];
					$months[]=$values['month'].' '.$values['enquiry_year'].' ('.$values['enquiry_total'].')';
				}
				$dataarr['data']=$counts;
				$dataarr['categories']=$months;
			}

			$resultarr['monthwise_enquiries']=$dataarr;

			$modelOffer = new Offer();
			$command = $connection->createCommand("SELECT sum(total_payable_amount) as total_payable_amount , DATE_FORMAT(FROM_UNIXTIME(offer.created_at), '%b') AS offer_monthname,DATE_FORMAT(FROM_UNIXTIME(offer.created_at), '%m') AS offer_month,DATE_FORMAT(FROM_UNIXTIME(offer.created_at), '%Y') AS offer_year
			FROM `tbl_offer` as offer inner join tbl_offer_list as offerlist on offer.id=offerlist.offer_id 
			and offer.status=".$modelOffer->enumStatus['finalized']."   GROUP BY DATE_FORMAT(FROM_UNIXTIME(offer.created_at), '%Y'),DATE_FORMAT(FROM_UNIXTIME(offer.created_at), '%m') ORDER BY offer.created_at DESC limit 0,6");
			$dataarr=array();
			$result = $command->queryAll();
			if(count($result)>0)
			{
				$months=array();
				$counts=array();
				foreach($result as $values)
				{
					$total_payable_amount = round($values['total_payable_amount']);
					$counts[]=$total_payable_amount;
					$months[]=$values['offer_monthname'].' '.$values['offer_year'].' ($'.$total_payable_amount.')';
				}
				$dataarr['data']=$counts;
				$dataarr['categories']=$months;
			}
			$resultarr['monthwise_offer_amount']=$dataarr;
			
			
			$modelOffer = new Offer();
			$model = Offer::find()->where(['t.status'=>$modelOffer->enumStatus['finalized']])->alias('t');			
			$model = $model->joinWith(['application as app']);			
			$model = $model->join('inner join', 'tbl_offer_list as list','list.offer_id=t.id and list.is_latest=1');	
			
			$offer_list=array();			
			$model = $model->orderBy(['t.created_at' => SORT_DESC]);				
			$model = $model->limit(10)->all();	
			if(count($model)>0)
			{
				foreach($model as $offer)
				{
					$data=array();
					$data['id']=$offer->id;
					if($offer->application){
						$data['app_id']=$offer->application->id;
						$data['company_name']=$offer->application->companyname?:'';
						$data['contact_name']=$offer->application->contactname?:'';
						
						$es=$offer->application->applicationstandard; 
						$eStandardArr=array();
						if(count($es)>0)
						{
							foreach($es as $enquirystandard)
							{
								$eStandardArr[]=$enquirystandard->standard->code;
							}
						}				
						$data['standards']=$eStandardArr;
						
						$data['company_telephone']=$offer->application->telephone;					
						$data['company_country_id']=$offer->application->countryname;
					}
						
					$data['total']=$offer->offerlist->total;
					$data['tax_amount']=$offer->offerlist->tax_amount;
					$data['total_payable_amount']=$offer->offerlist->total_payable_amount;					
					$data['created_at']=date($date_format,$offer->created_at);
					$offer_list[]=$data;   
				}
				$enquiries['offerlist']=$offer_list;
			}

			$months=array();
			$counts=array();

			$lastsixmonths[] = date('m-Y');
			$monthyear= date('M Y');

			$arrcnt[date('m-Y')] = 0;
			$counts[] = 0;
			$months[]= $monthyear.' (0)';



			for ($i = 1; $i < 4; $i++) {
			  $monthyearVal = date('m-Y', strtotime(date( 'Y-m-01' )."-$i months"));
			  $monthyear= date('M Y', strtotime(date( 'Y-m-01' )."-$i months"));

			  $lastsixmonths[] = $monthyearVal; 
			  $arrcnt[$monthyearVal] = $i;
			  $counts[]=0;
			  $months[]= $monthyear.' (0)';
			}
			//print_r($arrcnt);

			$requestmodel = new Request();
			$command = $connection->createCommand("SELECT count(*) as total,DATE_FORMAT(FROM_UNIXTIME(`created_at`), '%m-%Y') as monthyearformat, DATE_FORMAT(FROM_UNIXTIME(`created_at`), '%b') AS month,DATE_FORMAT(FROM_UNIXTIME(`created_at`), '%Y') as year   FROM `tbl_tc_request` as request WHERE DATE_FORMAT(FROM_UNIXTIME(`created_at`), '%m-%Y') in ('".implode('\',\'',$lastsixmonths)."') group by DATE_FORMAT(FROM_UNIXTIME(`created_at`), '%m-%Y')");

			// and status='".$requestmodel->arrEnumStatus['approved']."' 
			$dataarr=array();
			$result = $command->queryAll();
			if(count($result)>0)
			{
				
				foreach($result as $values)
				{

					$arrkey = $arrcnt[$values['monthyearformat']];
					$counts[(int)$arrkey]=(int)$values['total'];
					$months[(int)$arrkey]=$values['month'].' '.$values['year'].' ('.$values['total'].')';
				}
				
			}
			$dataarr['data']=$counts;
			$dataarr['categories']=$months;
			$resultarr['monthwise_tcrequest']=$dataarr;
			


			

		}else{
			
			$franchiseid=$userData['franchiseid'];
			$is_headquarters =$userData['is_headquarters'];
		
			if($role!=0 && in_array('application_review',$rules))
			{
				//Waiting for Review
				$res = $modelUserDashboard->applicationWaitingforReview($franchiseid);
				if(count($res)>0)
				{
					$resultarr['application_waiting_for_review']=$res['application_waiting_for_review'];
				}
				
				//Review in Process
				$res = $modelUserDashboard->applicationReviewInProgress($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['application_review_in_process']=$res['application_review_in_process'];
				}		
							
				
			}
			if($role!=0 && in_array('application_approval',$rules)){
				
				//Review Completed
				$res = $modelUserDashboard->applicationWaitingforApproval($franchiseid);
				if(count($res)>0)
				{
					$resultarr['application_waiting_for_approval']=$res['application_waiting_for_approval'];
				}
				
				
				//Approval in Process
				$res = $modelUserDashboard->applicationApprovalInProgress($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['application_approval_in_process']=$res['application_approval_in_process'];
				}
								
				
			}
			if($role!=0 && in_array('generate_offer',$rules)){
				
				//Generate Offer
				$res = $modelUserDashboard->generateOffer($franchiseid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_offer_generation']=$res['waiting_for_offer_generation'];
				}
				
				$res = $modelUserDashboard->waitingForOfferSendToCustomer($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_offer_send_to_customer']=$res['waiting_for_offer_send_to_customer'];
				}
			}
			if($role!=0 && in_array('oss_quotation_review',$rules)){
				
				$res = $modelUserDashboard->waitingUserOssOfferApproval($franchiseid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_user_oss_offer_approval']=$res['waiting_for_user_oss_offer_approval'];
				}
				
				$res = $modelUserDashboard->waitingUserOssReinitiatedOfferApproval($franchiseid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_user_oss_reinitated_offer_approval']=$res['waiting_for_user_oss_reinitated_offer_approval'];
				}
				
			}
			if($role!=0 && in_array('offer_approvals',$rules)){
				
				$res = $modelUserDashboard->offerApproval($franchiseid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_offer_approval']=$res['waiting_for_offer_approval'];
				}		
				
			}
			if($role!=0 && in_array('send_offer_to_customer',$rules)){
				
				
			}
			if($role!=0 && in_array('generate_invoice',$rules)){
				
				$res = $modelUserDashboard->generateInvoice($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_invoice_generation']=$res['waiting_for_invoice_generation'];
				}
				
				
			
			}
			if($role!=0 && in_array('invoice_approvals',$rules)){
				
				$res = $modelUserDashboard->invoiceApproval($franchiseid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_invoice_approval']=$res['waiting_for_invoice_approval'];
				}		
				
			}
			if($role!=0 && in_array('generate_audit_plan',$rules)){

				$res = $modelUserDashboard->generateAuditPlan($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_audit_plan_generation']=$res['waiting_for_audit_plan_generation'];
				}
				
				$res = $modelUserDashboard->rejectedAuditPlan($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_rejected_audit_plan_generation']=$res['waiting_for_rejected_audit_plan_generation'];
				}	

				// ---------- Follow Up Audit Code Start Here ----------
				$res = $modelUserDashboard->generateFollowUpAuditPlan($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_followup_audit_plan_generation']=$res['waiting_for_followup_audit_plan_generation'];
				}
				
				$res = $modelUserDashboard->rejectedFollowUpAuditPlan($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_rejected_followup_audit_plan_generation']=$res['waiting_for_rejected_followup_audit_plan_generation'];
				}
				// ---------- Follow Up Audit Code End Here ----------				
				
			}
			if($role!=0 && in_array('audit_execution',$rules)){	
				
				$res = $modelUserDashboard->waitingForAuditReview($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_audit_review']=$res['waiting_for_audit_review'];
				}				
				
				$res = $modelUserDashboard->waitingForAuditInspectionPlan($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_audit_inspection_plan']=$res['waiting_for_audit_inspection_plan'];
				}				
				
				$res = $modelUserDashboard->waitingForAuditExecution($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_audit_execution']=$res['waiting_for_audit_execution'];
				}

				$res = $modelUserDashboard->waitingForReportCorrection($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_report_correction']=$res['waiting_for_report_correction'];
				}

				$res = $modelUserDashboard->waitingForUnitLeadAuditorApproval($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_unit_lead_auditor_approval']=$res['waiting_for_unit_lead_auditor_approval'];
				}

				$res = $modelUserDashboard->waitingForFollowupUnitLeadAuditorApproval($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_followup_unit_lead_auditor_approval']=$res['waiting_for_followup_unit_lead_auditor_approval'];
				}

				$res = $modelUserDashboard->waitingForAuditLeadAuditorApproval($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_audit_lead_auditor_approval']=$res['waiting_for_audit_lead_auditor_approval'];
				}

				$res = $modelUserDashboard->waitingForNCoverdueApproval($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_audit_nc_overdue_approval']=$res['waiting_for_audit_nc_overdue_approval'];
				}

				$res = $modelUserDashboard->waitingForLeadAuditorRemediationApproval($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_lead_auditor_remediation_approval']=$res['waiting_for_lead_auditor_remediation_approval'];
				}
				
				$res = $modelUserDashboard->waitingForAuditorRejectedRemediationCorrection($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_auditor_rejected_remediation_correction']=$res['waiting_for_auditor_rejected_remediation_correction'];
				}
				/*

				$res = $modelUserDashboard->waitingForCustomerRejectedRemediationCorrection($userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_audit_correction']=$res['waiting_for_audit_correction'];
				}
				*/
				
				// ---------- Follow Up Audit Code Start Here ----------
				
				$res = $modelUserDashboard->waitingForFollowupAuditExecution($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_followup_audit_execution']=$res['waiting_for_followup_audit_execution'];
				}

				$res = $modelUserDashboard->waitingForFollowUpAuditLeadAuditorApproval($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_followup_audit_lead_auditor_approval']=$res['waiting_for_followup_audit_lead_auditor_approval'];
				}
				
				$res = $modelUserDashboard->waitingForFollowUpAuditReview($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_followup_audit_review']=$res['waiting_for_followup_audit_review'];
				}				
				
				$res = $modelUserDashboard->waitingForFollowUpAuditInspectionPlan($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_followup_audit_inspection_plan']=$res['waiting_for_followup_audit_inspection_plan'];
				}
				// ---------- Follow Up Audit Code End Here ----------
			}
			if($role!=0 && in_array('audit_review',$rules)){
				
				$res = $modelUserDashboard->waitingForAuditReviewer($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_audit_reviewer']=$res['waiting_for_audit_reviewer'];
				}

				$res = $modelUserDashboard->waitingForAuditReviewerReview($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_audit_reviewer_review']=$res['waiting_for_audit_reviewer_review'];
				}

				$res = $modelUserDashboard->waitingForNCoverdueReview($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_nc_overdue_review']=$res['waiting_for_nc_overdue_review'];
				}
				$res = $modelUserDashboard->waitingForReviewerRemediationApproval($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_reviewer_remediation_approval']=$res['waiting_for_reviewer_remediation_approval'];
				}
				
				// ---------- Follow Up Audit Code Start Here ----------
				$res = $modelUserDashboard->waitingForFollowUpAuditReviewerReview($franchiseid,$userid);
				if(count($res)>0)
				{
					$resultarr['waiting_for_followup_audit_reviewer_review']=$res['waiting_for_followup_audit_reviewer_review'];
				}
				// ---------- Follow Up Audit Code End Here ----------
			}
		}	
		
		if($resource_access==1 || ($role!=0 && in_array('declaration_approval',$rules))){
				
			$res = $modelUserDashboard->waitingForDeclarationApproval($franchiseid,$userid);
			if(count($res)>0)
			{
				$resultarr['waiting_for_declaration_approval']=$res['waiting_for_declaration_approval'];
			}
		}
		if($resource_access==1 || ($role!=0 && in_array('business_group_approval',$rules))){
				
			$res = $modelUserDashboard->waitingForBusinessGroupApproval($franchiseid,$userid);
			if(count($res)>0)
			{
				$resultarr['waiting_for_business_group_approval']=$res['waiting_for_business_group_approval'];
			}
		}
		
		if($resource_access==1 || ($role!=0 && in_array('standard_approval',$rules))){
				
			$res = $modelUserDashboard->waitingForUserStandardApproval($franchiseid,$userid);
			if(count($res)>0)
			{
				$resultarr['waiting_for_standard_approval']=$res['waiting_for_standard_approval'];
			}
		}
		
		if($resource_access==1 || ($role!=0 && in_array('standard_approval',$rules)))
		{
			$res = $modelUserDashboard->standardWitnessDateDue($franchiseid,$userid);
			if(count($res)>0)
			{
				$resultarr['witness_due_date_for_standard']=$res['witness_due_date_for_standard'];
			}			
			//unset($resultarr['witness_due_date_for_standard']);
		}
		
		if($role!=0 && in_array('application_review',$rules))
		{
			$res = $modelUserDashboard->productAdditionWaitingforReview($franchiseid,$userid);
			if(count($res)>0)
			{
				$resultarr['product_addition_waiting_for_review']=$res['product_addition_waiting_for_review'];
			}
		}	
		
		if($role!=0 && in_array('certification_review',$rules))
		{
			$res = $modelUserDashboard->auditWaitingforCertificateGeneration($franchiseid,$userid);
			if(count($res)>0)
			{
				$resultarr['audit_waiting_for_certificate_generation']=$res['audit_waiting_for_certificate_generation'];
			}
			
			$res = $modelUserDashboard->productAdditionWaitingforCertificateGeneration($franchiseid,$userid);
			if(count($res)>0)
			{
				$resultarr['product_addition_waiting_for_certificate_generation']=$res['product_addition_waiting_for_certificate_generation'];
			}
		}
		
		if($role!=0 && in_array('application_review',$rules))
		{
			$res = $modelUserDashboard->tcWaitingforReview($franchiseid,$userid);
			if(count($res)>0)
			{
				$resultarr['tc_waiting_for_review']=$res['tc_waiting_for_review'];
			}
		}
		
		if($role!=0 && in_array('application_review',$rules))
		{
			$res = $modelUserDashboard->unitWithdrawWaitingforReview ($franchiseid,$userid);
			if(count($res)>0)
			{
				$resultarr['unit_withdraw_waiting_for_review']=$res['unit_withdraw_waiting_for_review'];
			}
		}
		
		//Application Waiting for Review
		if($role!=0 && in_array('submit_for_review',$rules))
		{
			$res = $modelUserDashboard->applicationSubmitForReview($franchiseid,$userid);
			if(count($res)>0)
			{
				$resultarr['pending_actions']=$res['pending_actions'];
			}
		}	
		
		
		if($role!=0 && in_array('assign_as_oss_review_for_tc',$rules))
		{
			$res = $modelUserDashboard->tcWaitingforOssReview($franchiseid,$userid);
			if(count($res)>0)
			{
				$resultarr['tc_waiting_for_oss_review']=$res['tc_waiting_for_oss_review'];
			}
		}
		
		//$resultarr['pending_users']=[];
		if($resource_access==1){
			//$resultarr=array();
			$appmodel = User::find()->where(['t.status'=> '0'])->alias('t');
			$appmodel = $appmodel->innerJoinWith(['usersrole as roles']);
			$appmodel = $appmodel->andWhere('roles.approval_status="1"');
						
			$appmodel = $appmodel->groupBy(['t.id']);
			//$appmodel = $appmodel->all();
			
			$appmodel = $appmodel->all();	
			if($appmodel !== null)
			{
				$date_format = Yii::$app->globalfuns->getSettings('date_format');
				if(count($appmodel)>0)
				{
					foreach($appmodel as $model)
					{
						$code= '';
						$data=array();
						$data['id']=$model->id;
						$data['name']=$model->first_name.' '.$model->last_name;
						$data['country']=$model->country->name;
						$data['email']=$model->email;
						//if(count(userstandard))
						
						$data['created_at']=date($date_format,$model->created_at);
				
				
						$resultarr['pending_users'][]=$data;
					}
				}
			}
		}
		//SELECT * FROM `tbl_users` as user inner join `tbl_user_declaration` decl on user.id=decl.user_id WHERE decl.status=1 group by user.id
		
		return ['data'=>$enquiries,'chartdata'=>$resultarr];

    }

    public function actionFranchiseDashboard()
    {
		$modelUserDashboard = new UserDashboard();
		$modelCustomerDashboard = new CustomerDashboard();
		$modelFranchiseDashboard = new FranchiseDashboard();
		
		$resultarr=array();
		$resultcustomerarr = [];
        $enquiries=array();
        $totalenquiries=0;

		$userData = Yii::$app->userdata->getData();
		//print_r($userData); die;
		$userid=$userData['userid'];
		$franchiseid=$userData['franchiseid'];
		//$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$is_headquarters =$userData['is_headquarters'];
        $resource_access=$userData['resource_access'];
        $date_format = Yii::$app->globalfuns->getSettings('date_format');
		

		$Rolemodel = Role::find()->where(['id' => $role])->one();
		if($Rolemodel !== null){
			$role_name = strtolower($Rolemodel->role_name);
		}
		if($resource_access == '5'){
			$userid = $franchiseid;
		}
		/*
		$modelUserDashboard->is_headquarters = $is_headquarters;
		$modelUserDashboard->resource_access = $resource_access;
		$modelUserDashboard->date_format = $date_format;
		*/
		
		$modelFranchiseDashboard->is_headquarters = $is_headquarters;
		$modelFranchiseDashboard->resource_access = $resource_access;
		$modelFranchiseDashboard->date_format = $date_format;
		
		$modelCustomerDashboard->is_headquarters = $is_headquarters;
		$modelCustomerDashboard->resource_access = $resource_access;
		$modelCustomerDashboard->date_format = $date_format;
		$modelCustomerDashboard->rules = $rules;
		$modelCustomerDashboard->user_type = $user_type;
		$modelCustomerDashboard->role = $role;
		
		//-----------Franchise Related Pending Actions Code Start Here--------------
		
		$res = $modelFranchiseDashboard->waitingForOssofferApproval($userid);
		if(count($res)>0)
		{
			$resultarr['waiting_for_oss_offer_approval']=$res['waiting_for_oss_offer_approval'];
		}

		$res = $modelFranchiseDashboard->waitingForOssReinitiatedofferApproval($userid);
		if(count($res)>0)
		{
			$resultarr['waiting_for_oss_reinitated_offer_approval']=$res['waiting_for_oss_reinitated_offer_approval'];
		}
		
		//Application waiting for Review
		$res = $modelFranchiseDashboard->applicationSubmitForReview($userid);
		if(count($res)>0)
		{
			$resultarr['pending_actions']=$res['pending_actions'];
		}
		
		//Product waiting for Review
		$res = $modelFranchiseDashboard->productAdditionForReview($userid);
		if(count($res)>0)
		{
			$resultarr['product_addition_review']=$res['product_addition_review'];
		}

		//Product waiting for Review Re Assign
		$res = $modelFranchiseDashboard->productAdditionForReviewReAssign($userid);
		if(count($res)>0)
		{
			$resultarr['product_addition_review_reassign']=$res['product_addition_review_reassign'];
		}
		
		//TC waiting for Review
		$res = $modelFranchiseDashboard->tcWaitingForReview($userid);
		if(count($res)>0)
		{
			$resultarr['tc_waiting_for_review']=$res['tc_waiting_for_review'];
		}
		
		//TC waiting for Review Re Assign
		$res = $modelFranchiseDashboard->tcWaitingForReviewReAssign($userid);
		if(count($res)>0)
		{
			$resultarr['tc_waiting_for_review_reassign']=$res['tc_waiting_for_review_reassign'];
		}

		//Unit Withdraw Waiting for Review
		$res = $modelFranchiseDashboard->unitWithdrawWaitingForReview($userid);
		if(count($res)>0)
		{
			$resultarr['unit_withdraw_review']=$res['unit_withdraw_review'];
		}
		
		//Unit Withdraw Waiting for Review Re Assign
		$res = $modelFranchiseDashboard->unitWithdrawWaitingForReviewReAssign($userid);
		if(count($res)>0)
		{
			$resultarr['unit_withdraw_review_reassign']=$res['unit_withdraw_review_reassign'];
		}
		
		//-----------Franchise Related Pending Actions Code End Here----------------
		
		
		
		//-----------Customer Related Pending Actions Code Start Here---------------
		$res = $modelCustomerDashboard->applicationWaitingForSubmission($userid);
		if(count($res)>0)
		{
			$resultcustomerarr['pendingactions']=$res['pendingactions'];
		}
		
		$res = $modelCustomerDashboard->applicationReAssign($userid);
		if(count($res)>0)
		{
			$resultcustomerarr['re_initiate_pending_actions']=$res['re_initiate_pending_actions'];
		}
				
		$res = $modelCustomerDashboard->offerWaitingForApproval($userid);
		if(count($res)>0)
		{
			$resultcustomerarr['offer_waiting_for_approvals']=$res['offer_waiting_for_approvals'];
		}
		
		$res = $modelCustomerDashboard->auditPlanWaitingForApprovals($userid);
		if(count($res)>0)
		{
			$resultcustomerarr['audit_plan_waiting_for_approvals']=$res['audit_plan_waiting_for_approvals'];
		}

		$res = $modelCustomerDashboard->auditWaitingForRemediation($userid);
		if(count($res)>0)
		{
			$resultcustomerarr['audit_waiting_for_remediation']=$res['audit_waiting_for_remediation'];
		}
		
		$res = $modelCustomerDashboard->waitingForCustomerRejectedRemediationCorrection($userid);
		if(count($res)>0)
		{
			$resultcustomerarr['waiting_for_customer_rejected_remediation_correction']=$res['waiting_for_customer_rejected_remediation_correction'];
		}
		//-----------Customer Related Pending Actions Code End Here----------------
		
		
        return ['data'=>$resultarr,'customerdata'=>$resultcustomerarr];
	}
	
	public function actionDownloadCertificate(){
		$data = Yii::$app->request->post();
		$files = Certificate::find()->where(['id'=>$data['id']])->one();
		//if($data['filetype']=='gisfile'){
			$filename = $files->filename;
		//}
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['certificate_files'].$filename;
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

    // public function actionCheckLeftMenu()
    // {
    //     $userData = Yii::$app->userdata->getData();
    //     $enquirymodel = Enquiry::find()->where(['franchise_id'=>$userData['userid']])->count();
    //     if ($enquirymodel !== null)
	// 	{

    //     }

    // }

}
