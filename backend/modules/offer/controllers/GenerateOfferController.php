<?php
namespace app\modules\offer\controllers;

use Yii;
use app\models\EnquiryStandard;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationProduct;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationManday;
use app\modules\application\models\ApplicationUnitManday;
use app\modules\application\models\ApplicationUnitMandayStandard;
use app\modules\application\models\ApplicationUnitMandayStandardDiscount;
use app\modules\application\models\ApplicationUnitProduct;
use app\modules\application\models\ApplicationUnitProcess;
use app\modules\application\models\ApplicationUnitCertifiedStandard;
use app\modules\application\models\ApplicationUnitCertifiedStandardFile;
use app\modules\application\models\ApplicationUnitSubtopic;

use app\modules\application\models\ApplicationUnitLicenseFee;
use app\modules\master\models\StandardInspectionTime;
use app\modules\master\models\StandardInspectionTimeStandard;
use app\modules\master\models\StandardInspectionTimeProcess;
use app\modules\master\models\StandardReduction;
use app\modules\master\models\StandardReductionRate;
use app\modules\master\models\UserCompanyInfo;
use app\modules\master\models\Mandaycost;
use app\modules\master\models\Country;
use app\modules\master\models\User;
use app\modules\master\models\State;
use app\modules\master\models\ReductionStandard;
use app\modules\offer\models\Offer;
use app\modules\offer\models\OfferList;
use app\modules\offer\models\OfferListCertificationFee;
use app\modules\offer\models\OfferComment;
use app\modules\offer\models\OfferListOtherExpenses;
use app\modules\offer\models\OfferListTax;
use app\modules\master\models\StandardOtherInspectionTimeProcess;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;
use app\modules\master\models\Settings;
use app\modules\master\models\Standard;
use app\modules\master\models\StandardInspectionTimeTradingProcess;

use app\modules\invoice\models\Invoice;
use app\modules\invoice\models\InvoiceDetails;
use app\modules\invoice\models\InvoiceTax;

use app\modules\offer\models\OfferReinitiateComment;

use app\modules\offer\models\OfferListProcessor;
use app\modules\offer\models\OfferListProcessorFile;
use app\modules\audit\models\AuditReportClientInformationGeneralInfo;
use app\modules\audit\models\AuditReportClientInformationSupplierInformation;
use app\modules\audit\models\AuditReportEnvironment;
use app\modules\audit\models\AuditReportClientInformationChecklistReview;
use app\modules\audit\models\AuditReportApplicableDetails;
use app\modules\audit\models\AuditReportClientInformationProcess;
use app\modules\audit\models\Audit;
use app\modules\changescope\models\UnitAddition;
use app\modules\certificate\models\Certificate;

use app\modules\library\models\LibraryDownloadFile;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * GenerateOfferController implements the CRUD actions for Process model.
 */
class GenerateOfferController extends \yii\rest\Controller
{

	// public $downloadSchemefiles=['scheme' => 'scheme-rules.pdf'];

	public $downloadProcessorfiles=['processor' => 'PCPA02_PROCESSOR_AGREEMENT_(TE).docx'];

	public $downloadOfferfiles=['risk_assessment' => 'risk_assessment.xlsx'
								, 'reconciliation_report'=>'reconciliation_report.xlsx'
								,'content_claim_standard'=>'content_claim_standard.docx'
								,'chemical_declaration'=>'chemical_declaration.xlsx'
								,'social_declaration'=>'social_declaration.xlsx'
								,'environmental_declaration'=>'environmental_declaration.xlsx'
								,'environmental_report'=>'environmental_report.xlsx'
								,'chemical_list'=>'chemical_list.xlsx'
								];
	
	public $downloadStandardfiles=['GOTS' => 'gots-file.pdf', 'GRS'=>'grs-file.pdf', 'OCS'=>'ocs-file.pdf', 'CCS'=>'ccs-file.pdf','RCS'=>'rcs-file.pdf'];

	public $downloadImplementationfiles=['GOTS' => 'gots-file.pdf', 'GRS'=>'grs-file.pdf', 'OCS'=>'ocs-file.pdf', 'CCS'=>'ccs-file.pdf','RCS'=>'rcs-file.pdf'];

	public $downloadChecklistfiles=['GOTS' => 'gots-file.pdf', 'GRS'=>'grs-file.pdf', 'OCS'=>'ocs-file.pdf', 'CCS'=>'ccs-file.pdf','RCS'=>'rcs-file.pdf'];
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

		$appsmodel=new Application();
		
		$modelOffer = new Offer();
		
		$modelApplication = new Application();
		$usermodel = new User();
		
		$model = Application::find()->alias('t');
		$model->joinWith(['offer as ofr']);				
				
		//$model->andWhere(['!=','ofr.status', 6]);
		//$model->andWhere('or',['!=','ofr.status',6],['ofr.status' => NULL]);
		if(isset($post['statusFilter'])  && $post['statusFilter']!='')
		{
			if( $post['statusFilter']>'0'){
				$model = $model->andWhere(['ofr.status'=> $post['statusFilter']]);
			}else if( $post['statusFilter']=='0'){
				$model = $model->andWhere(['ofr.status'=> null]);
			}			
		}
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		if($resource_access != 1){
			//echo $user_type.'=='.$role; die;
			if($user_type== 1 && ! in_array('offer_management',$rules) ){
				return $responsedata;
			}else if($user_type==3 && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$model = $model->andWhere('t.franchise_id="'.$userid.'"  and (ofr.status!='.$modelOffer->enumStatus['in-progress'].' and ofr.status!='.$modelOffer->enumStatus['open'].') '); //or t.created_by="'.$userid.'"
			}else if($user_type==2){
				//$model = $model->andWhere(' (ofr.status!='.$modelOffer->enumStatus['in-progress'].' and ofr.status!='.$modelOffer->enumStatus['open'].') ');
				$model = $model->andWhere('t.customer_id="'.$userid.'" and ofr.status>='.$modelOffer->enumStatus['waiting-for-customer-approval'].' ');
				
			}
		}
		if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1){
			$model = $model->andWhere('t.franchise_id="'.$franchiseid.'"');
		}

		$sqlcondition = [];
		if($user_type== Yii::$app->params['user_type']['user'] &&  in_array('generate_offer',$rules)){
			$model = $model->join('left join', 'tbl_offer_list as list','list.offer_id=ofr.id and list.is_latest=1');
			$sqlcondition[] = ' (ofr.created_by ='.$userid.' or ofr.updated_by ='.$userid.' or list.id IS NULL or list.created_by='.$userid.' )';
		}
		if($user_type== Yii::$app->params['user_type']['user'] &&  in_array('offer_approvals',$rules)){
			$sqlcondition[] = ' (ofr.status >="'.$modelOffer->enumStatus['customer_approved'].'") ';
		}
		if($user_type== Yii::$app->params['user_type']['user'] && in_array('oss_quotation_review',$rules)){
			$sqlcondition[] = ' (ofr.status >="'.$modelOffer->enumStatus['waiting-for-oss-approval'].'") ';
		}
		/// To include in condition ends here
		if(count($sqlcondition)>0){
			$strSqlCondition = ' '.implode(' OR ',$sqlcondition).' ';
			$model = $model->andWhere( $strSqlCondition );
		}

		/*
		if($user_type== Yii::$app->params['user_type']['user'] 
			&& in_array('oss_quotation_review',$rules)
			&& !in_array('generate_offer',$rules)
		){
				$model = $model->andWhere(' ofr.status >="'.$modelOffer->enumStatus['waiting-for-oss-approval'].'" ');
		}
		if($user_type== Yii::$app->params['user_type']['user'] &&  in_array('offer_approvals',$rules) && !in_array('generate_offer',$rules) && !in_array('oss_quotation_review',$rules)){			
			$model = $model->andWhere('ofr.status >="'.$modelOffer->enumStatus['customer_approved'].'"');
		}
		if($user_type== Yii::$app->params['user_type']['user'] &&  in_array('generate_offer',$rules)){
			$model = $model->andWhere('((ofr.status >="'.$modelOffer->enumStatus['in-progress'].'" and (ofr.created_by ='.$userid.' or ofr.updated_by ='.$userid.' or list.id IS NULL or list.created_by='.$userid.' )) or t.status='.$appsmodel->arrEnumStatus["approved"].') ');
		}
		*/
		
		/*
		else if($user_type==3 && $role!=0 && ! in_array('view_offer',$rules) ){
				return $responsedata;
			}
		*/
		
		$model = $model->andWhere('t.status='.$modelApplication->arrEnumStatus['approved'].' and (ofr.status!='.$modelOffer->enumStatus['finalized'].' or ofr.status is null)');
		
		/*if($user_type==2)
		{
			$model = $model->andWhere('t.created_by='.$userid.' and (ofr.status!='.$modelOffer->enumStatus['in-progress'].' and ofr.status!='.$modelOffer->enumStatus['open'].') ');
			//
		}
		*/
		
		if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
		{
			$model = $model->join('inner join', 'tbl_application_standard as app_standard','app_standard.app_id =t.id ');
			$model = $model->andWhere(['app_standard.standard_id'=> $post['standardFilter']]);			
		}
		
		if(isset($post['franchiseFilter']) && is_array($post['franchiseFilter']) && count($post['franchiseFilter'])>0)
		{
			$model = $model->andWhere(['t.franchise_id'=> $post['franchiseFilter']]);	
		}
		
        $model = $model->groupBy(['t.id']);
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
			$model->joinWith(['offer as ofr','applicationaddress as appaddress']);
			
            $page = ($post['page'] - 1)*$post['pageSize'];
			$pageSize = $post['pageSize']; 
			$statusarray=array_map('strtolower', $modelOffer->arrStatus);
			
			if(isset($post['searchTerm']) && $post['searchTerm'] !='')
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 't.code', $searchTerm],
					['like', 'appaddress.company_name', $searchTerm],
					['like', 'appaddress.first_name', $searchTerm],
					['like', 'appaddress.last_name', $searchTerm],
					['like', 'appaddress.telephone', $searchTerm],	
					['like', 'date_format(FROM_UNIXTIME(t.created_at), \'%b %d, %Y\' )', $searchTerm],
					//['like', 't.status', array_search($searchTerm,$statusarray)],											
				]);
				/*
				$search_status = array_search(strtolower($searchTerm),$statusarray);
				
				if($search_status!==false)
				{
					if($search_status=='0'){
						$model = $model->orFilterWhere([
	                        'or', 					
							['ofr.status'=>$search_status]								
						]);
					}else{
					//echo $search_status; 
						$model = $model->orFilterWhere([
	                        'or', 					
							['ofr.status'=>$search_status]								
						]);
					}
				}
				*/				
			}
			$totalCount = $model->count();
			
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				//$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
				if($post['sortColumn']=='t.company_name')
				{
					$model = $model->orderBy(['appaddress.company_name'=>$sortDirection]);
				}else{
					$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
				}
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
		
		$app_list=array();
		//$model = $model->asArray()->all();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $application)
			{
				$data=array();
				$data['id']=$application->id;
				$data['code']=$application->code;
				$data['company_name']=$application->companyname;
				$data['address']=$application->address;
				$data['zipcode']=$application->zipcode;
				$data['city']=$application->city;
				$data['title']=$application->title;
				$data['first_name']=$application->firstname;
				$data['last_name']=$application->lastname;
				$data['job_title']=$application->jobtitle;
				$data['telephone']=$application->telephone;
				$data['email_address']=$application->emailaddress;

				//$data['created_at']=date('M d,Y h:i A',$application->created_at);
				$data['created_at']=date($date_format,$application->created_at);
				$data['status']=$application->arrStatus[$application->status];
				$data['status_id']=$application->status;
				$data['offer_status']=$application->offer?$application->offer->status:'';
				$data['offer_status_name']=$application->offer?$application->offer->arrStatus[$application->offer->status]:'Open';
				$data['offer_id']=$application->offer?$application->offer->id:0;
				
				$data['application_unit_count']=count($application->applicationunit);
				$canEditOpen = 0;
				if($resource_access==1 || ($user_type==1 && in_array('generate_offer',$rules))){
					$canEditOpen = 1;
				}
				$data["can_edit_offer"]=$application->offer?$this->canEditOffer($application->offer->id,$application->id):$canEditOpen;
				$arrAppStd=array();

				$data['oss_label'] = $usermodel->ossnumberdetail($application->franchise_id);
				
				$appStd=$application->applicationstandardview;
				
				//$appStd = $application->applicationstandard;
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
		}
		
		return ['applications'=>$app_list,'total'=>$totalCount];
	}
	
	public function actionOfferList()
    {
        $post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];
		$franchiseid=$userData['franchiseid'];
		
		$appsmodel=new Application();
		$invoicemodel=new Invoice();
		$modelOffer = new Offer();
		$usermodel = new User();
		
		$model = Offer::find()->where(['t.status'=>$modelOffer->enumStatus['finalized']])->alias('t');		
		$model = $model->groupBy(['t.id']);
		if(!isset($post['listype']) || $post['listype']!='offer'){
			$model = $model->join('left join', 'tbl_invoice as invoice','invoice.offer_id=t.id');
			$model = $model->andWhere('invoice.status not in ('.$invoicemodel->enumStatus['finalized'].') or invoice.id IS NULL');
		}
		 
		$appJoinWithStatus=false; 
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		if($resource_access != 1){
			//echo $user_type.'=='.$role; die;
			if($user_type== 1 && ! in_array('invoice_management',$rules) && ! in_array('offer_management',$rules)){
				return $responsedata;
			}else if($user_type==3 && $is_headquarters!=1){
				//&& $role==0
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$appJoinWithStatus=true;	
				$model = $model->joinWith(['application as app']);	
				$model = $model->andWhere('app.franchise_id="'.$userid.'"  '); //and invoice.status not in (0,1,2)
			}else if($user_type==2){
				if(isset($post['type'])){
					//$model = $model->joinWith(['invoice as invoice']);
					$model = $model->andWhere('app.created_by="'.$userid.'" ');// and invoice.status not in (0,1,2) 
				}else{
					$model = $model->andWhere('app.created_by="'.$userid.'"  ');
				}
				//and invoice.status !="0"
			}
			/*
			else if($user_type==3 && $role!=0 && ! in_array('view_invoice',$rules) ){
				return $responsedata;
			}
			*/
		}
		
		/*
		if($user_type== Yii::$app->params['user_type']['user'] &&  in_array('generate_offer',$rules)){
			$model = $model->andWhere('((t.status >="'.$modelOffer->enumStatus['in-progress'].'" and (t.created_by ='.$userid.' or t.updated_by ='.$userid.' or list.id IS NULL or list.created_by='.$userid.' )) or app.status='.$appsmodel->arrEnumStatus["approved"].') ');
		}
		if($user_type== Yii::$app->params['user_type']['user'] &&  in_array('offer_approvals',$rules)
		 && !in_array('generate_offer',$rules)  && !in_array('oss_quotation_review',$rules)){
			$model = $model->andWhere(' t.updated_by = "'.$userid.'" ');
		}
		*/
		$sqlcondition = [];
		/// To include in condition ends here
		if($user_type== Yii::$app->params['user_type']['user']  && in_array('generate_offer',$rules)){
			$model = $model->join('left join', 'tbl_offer_list as list','list.offer_id=t.id and list.is_latest=1');
			$sqlcondition[] = ' (t.created_by ='.$userid.' or t.updated_by ='.$userid.' or list.created_by='.$userid.') ';
		}
		if($user_type== Yii::$app->params['user_type']['user']  && in_array('offer_approvals',$rules)){
			$sqlcondition[] = ' ( t.updated_by = "'.$userid.'" ) ';
		}
		if(count($sqlcondition)>0){
			$strSqlCondition = ' '.implode(' OR ',$sqlcondition).' ';
			$model = $model->andWhere( $strSqlCondition );
		}
		
		if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1){
			if(!$appJoinWithStatus)
			{	
				$appJoinWithStatus=true;
				$model = $model->joinWith(['application as app']);	
			}
			
			$model = $model->andWhere(' app.franchise_id="'.$franchiseid.'" ');
		}
		 
		/*
		if($user_type==2)
		{
			$model->andWhere('app.created_by='.$userid.' ');
		}
		*/
		//$model->andWhere(['!=','ofr.status', 6]);
		//$model->andWhere('or',['!=','ofr.status',6],['ofr.status' => NULL]);
		//$model->where('(ofr.status!='.$modelOffer->enumStatus['finalized'].' or ofr.status is null)');
		
		
		if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
		{
			if(!$appJoinWithStatus)
			{	
				$appJoinWithStatus=true;
				$model = $model->joinWith(['application as app']);	
			}
			
			$model = $model->join('left join', 'tbl_application_standard as app_standard','app_standard.app_id=app.id');
			$model = $model->andWhere(['app_standard.standard_id'=> $post['standardFilter']]);			
		}
		
		if(isset($post['franchiseFilter']) && is_array($post['franchiseFilter']) && count($post['franchiseFilter'])>0)
		{
			if(!$appJoinWithStatus)
			{	
				$appJoinWithStatus=true;
				$model = $model->joinWith(['application as app']);	
			}
			
			$model = $model->andWhere(['app.franchise_id'=> $post['franchiseFilter']]);			
		}
		
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
			if(!$appJoinWithStatus)
			{	
				$appJoinWithStatus=true;
				$model = $model->joinWith(['application as app']);	
			}
			
			$model = $model->join('inner join', 'tbl_application_change_address as appaddress','appaddress.id=app.address_id');
			
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm'] !='')
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 't.offer_code', $searchTerm],
					['like', 'appaddress.company_name', $searchTerm],	
					['like', 'appaddress.telephone', $searchTerm],
					['like', 't.manday', $searchTerm],
					//['like', 'list.total_payable_amount', $searchTerm],					
					['like', 'date_format(FROM_UNIXTIME(t.created_at), \'%b %d, %Y\' )', $searchTerm],					
				]);				
			}
			$totalCount = $model->count();
			
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				//$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
				if($post['sortColumn']=='app.company_name')
				{
					$model = $model->orderBy(['appaddress.company_name'=>$sortDirection]);
				}else{
					$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
				}
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
		
		$app_list=array();
		//$model = $model->asArray()->all();

		
		$model = $model->all();	

		//echo count($model); die;	
		if(count($model)>0)
		{
			foreach($model as $offer)
			{
				$data=array();
				$data['id']=$offer->id;
				$data['app_id']=$offer->application->id;
				$data['code']=$offer->application->code;
				$data['offer_code']=$offer->offer_code;
				//$data['created_at']=date('M d,Y h:i A',$offer->created_at);
				$data['created_at']=date($date_format,$offer->created_at);
				$data['company_name']=$offer->application->companyname;
				$data['email_address']=$offer->application->emailaddress;
				$data['customer_number']=$offer->application->customer->customer_number;				
				$data['standard']=$offer->standard;
				$data['manday']=$offer->manday;
				$data['telephone']=$offer->application->telephone;
				$data['currency']=$offer->offerlist->currency;
				$data['total_payable_amount']=$offer->offerlist->total_payable_amount;				
				$data['invoice_status']=$offer->invoice?$offer->invoice->status:'';
				$data['invoice_status_name']=$offer->invoice?$offer->invoice->arrStatus[$offer->invoice->status]:'Open';
				$data['invoice_id']=$offer->invoice?$offer->invoice->id:'';
				$data['invoice_total_payable_amount']=$offer->invoice?$offer->invoice->total_payable_amount:'';
				$data['invoice_number']=$offer->invoice?$offer->invoice->invoice_number:'';
				
				$data['offer_status']=$offer->status;
				$data['offer_status_name']=$offer->arrStatus[$offer->status];
				
				$data['application_unit_count']=count($offer->application->applicationunit);
				
				$data['oss_label'] = $offer->application ? $usermodel->ossnumberdetail($offer->application->franchise_id) : '';
				
				$arrAppStd=array();
				//$appStd = $offer->application->applicationstandard;

				
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
		}
		
		return ['offers'=>$app_list,'total'=>$totalCount];
	}
	
	public function actionGetData()
	{
		$modelOffer = new Offer();
		return ['status'=>$modelOffer->arrStatus];
	}
	/*
	public function actionUpdateOffer()
	{	
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();		
		if ($data) 
		{
			$total_discount_manday = 0;
			$total_final_manday = 0;
			$total_unit_manday_cost = 0;
			
			$appMandayModel = ApplicationManday::find()->where(['app_id' => $data['app_id']])->one();
			if($appMandayModel!==null)
			{
				$unitMandayModel=$appMandayModel->unitmanday;
				if(is_array($unitMandayModel) && count($unitMandayModel)>0)
				{
					$arrApplicationUnitMdy = $data['applicationdata']['appunitmanday'];
					foreach($unitMandayModel as $unitMandayM)
					{
						$key = array_search($unitMandayM->id, array_column($arrApplicationUnitMdy, 'unit_manday_id'));
						if($key>=0)
						{
							$app_unit_m_array=$arrApplicationUnitMdy[$key];
							
							$unitMandayM->no_of_workers_from=$app_unit_m_array['no_of_workers_from'];
							$unitMandayM->no_of_workers_to=$app_unit_m_array['no_of_workers_to'];
							$unitMandayM->manday=$app_unit_m_array['manday'];
							$unitMandayM->total_discount=$app_unit_m_array['total_discount'];
							$unitMandayM->eligible_discount=$app_unit_m_array['eligible_discount'];
							$unitMandayM->maximum_discount=$app_unit_m_array['maximum_discount'];
							$unitMandayM->discount_manday=$app_unit_m_array['discount_manday'];
							$unitMandayM->final_manday=$app_unit_m_array['final_manday'];
							$unitMandayM->manday_cost=$app_unit_m_array['manday_cost'];
							$unitMandayM->unit_manday_cost=$app_unit_m_array['unit_manday_cost'];
							
							$total_discount_manday = $total_discount_manday + $unitMandayM->discount_manday;
							$total_final_manday = $total_final_manday + $unitMandayM->final_manday;
							$total_unit_manday_cost = $total_unit_manday_cost + $unitMandayM->unit_manday_cost;
			
							if($unitMandayM->validate() && $unitMandayM->save())
							{
								$unitmandaydiscountModel = $unitMandayM->unitmandaydiscount;
								if(is_array($unitmandaydiscountModel) && count($unitmandaydiscountModel)>0)
								{
									$app_unit_m_d_array = $app_unit_m_array['manday_discount'];
									foreach($unitmandaydiscountModel as $unitmandaydiscountM)
									{
										$kkey = array_search($unitmandaydiscountM->id, array_column($app_unit_m_d_array, 'manday_discount_id'));
										if($kkey>=0)
										{
											$unit_m_d_array = $app_unit_m_d_array[$kkey];
											$unitmandaydiscountM->status = $unit_m_d_array['status'];	
											$unitmandaydiscountM->save();											
										}
									}
								}								
							}							
						}						
					}
					
					$appMandayModel->discount_manday=$total_discount_manday;
					$appMandayModel->final_manday=$total_final_manday;
					$appMandayModel->total_manday_cost=$total_unit_manday_cost;
					$appMandayModel->save();
					
					// --------------------------Update Offer Details Code Start Here----------------------------------
					if($data['offer_id']!='')
					{
						$offermodel = Offer::find()->where(['id' => $data['offer_id']])->one();
						if($offermodel!==null)
						{
							$offerlist = $offermodel->offerlist;
							if($offermodel->offerlist!=null)
							{
								$conversion_rate = $offerlist->conversion_rate;
								$tax_percentage = $offermodel->tax_percentage;								
								$offerlist->certification_fee_sub_total = $total_unit_manday_cost;								
								$other_expense_sub_total=$offerlist->other_expense_sub_total;															
								$total = $total_unit_manday_cost + $other_expense_sub_total;								
								$offerlist->total=$total;
								
								$gst_rate=0;
								if($tax_percentage>0)
								{
									$gst_rate = ($total*$tax_percentage/100);
								}	
								
								$total_payable_amount = $total+$gst_rate;	
								$offerlist->tax_amount=$gst_rate;	
								$offerlist->total_payable_amount=$total_payable_amount;																								
								$offerlist->conversion_total_payable=($offerlist->total_payable_amount*$conversion_rate);
								$offerlist->save();
								
								$offerlist->certificationfee->description=$appMandayModel->final_manday.' Manday';
								$offerlist->certificationfee->amount=$appMandayModel->total_manday_cost;
								$offerlist->certificationfee->save();
							}
						}						
					}
					// --------------------------Update Offer Details Code End Here----------------------------------
					
				}				
			}			
			$responsedata=array('status'=>1,'message'=>'Certified standard discount has been updated successfully');
		}
		return $this->asJson($responsedata);
	}
	*/
	
	public function actionGenerate()
	{	
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();

		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		
		$resource_access=$userData['resource_access'];

		$new_quotation_generate_status=0;

		$modelOffer = new Offer();
				
		if ($data) 
		{
			$canEditOfferStatus = $this->canEditOffer($data['offer_id'],$data['app_id']);
			if(!$canEditOfferStatus){
				return false;
			}
			if($data['offer_id']!='' && $data['offer_id']>0)
			{
				$offermodel = Offer::find()->where(['id' => $data['offer_id']])->one();		
				
				if($offermodel !== null)
				{
					if($offermodel->status == $modelOffer->enumStatus['customer_rejected'])
					{
						$offerlistmodel = OfferList::find()->where(['offer_id' => $data['offer_id'],'status'=>1,'is_latest'=>1])->one();					
						if($offerlistmodel !== null)
						{
							$offermodel->review_count=$offermodel->review_count+1;							
							$offerlistmodel->is_latest=2;
							$offerlistmodel->save();
						}
						
					}elseif($offermodel->status == $modelOffer->enumStatus['open'] || $offermodel->status == $modelOffer->enumStatus['in-progress']  || $offermodel->status == $modelOffer->enumStatus['waiting-for-oss-approval'] || $offermodel->status == $modelOffer->enumStatus['re-initiated-to-oss'] || $offermodel->status == $modelOffer->enumStatus['waiting-for-send-to-customer']){
						
						$modelOfferList = OfferList::find()->where(['offer_id' => $data['offer_id'],'is_latest'=>1])->all();
						if(count($modelOfferList)>0)
						{
							foreach($modelOfferList as $offerList)
							{
								OfferListCertificationFee::deleteAll(['offer_list_id' => $offerList->id]);
								OfferListOtherExpenses::deleteAll(['offer_list_id' => $offerList->id]);	
								OfferListTax::deleteAll(['offer_list_id' => $offerList->id]);
								$offerList->delete();
							}
						}					
					}
					$new_quotation_generate_status=1;
				}else{
					$offermodel = new Offer();
				}
			}
			else
			{
				$offermodel = new Offer();

				$appmodel=new Application();
				Yii::$app->globalfuns->updateApplicationOverallStatus($data['app_id'], $appmodel->arrEnumOverallStatus['quotation_in_process']);	
				//$appmodel=Application::find()->where(['id' => $data['app_id']])->one();
				//$appmodel->overall_status = $appmodel->arrEnumOverallStatus['offer_in_process'];
				//$appmodel->save();
			}	

			// --------- Code of Quotation code regeneration start here -----------
			if($new_quotation_generate_status==0)
			{
				$ApplicationModel=Application::find()->where(['id' => $data['app_id']])->one();				
				if($ApplicationModel!== null)
				{
					$ospid = $ApplicationModel->franchise_id;
					
					$offerCount = 0;
					$connection = Yii::$app->getDb();

					$command = $connection->createCommand("SELECT COUNT(offer.id) AS offer_count FROM `tbl_offer` AS offer 
					INNER JOIN `tbl_application` AS app ON app.id = offer.app_id AND app.franchise_id='$ospid' GROUP BY app.franchise_id");
					$result = $command->queryOne();
					if($result  !== false)
					{
						$offerCount = $result['offer_count'];
					}
					$maxid = $offerCount+1;
					if(strlen($maxid)=='1')
					{
						$maxid = "0".$maxid;
					}
					$offercode = "PC-".$ApplicationModel->franchise->usercompanyinfo->osp_number."-".$maxid."/".date("Y");
					$offermodel->offer_code=$offercode;					
				}
			}
			// --------- Code of Quotation code regeneration end here -----------
			
			
			$offermodel->app_id=$data['app_id'];
			//$offermodel->offer_code=$offercode;
			$offermodel->standard=str_replace(',',', ',$data['standard']);
			$offermodel->subcontractor_name=$data['subcontractor_name'];
			$offermodel->noof_subcontractor=$data['noof_subcontractor'];
			$offermodel->taxname=$data['taxname'];
			$offermodel->tax_percentage=isset($data['tax_percentage'])?$data['tax_percentage']:0;			
			$offermodel->manday=$data['manday'];

			if($offermodel->status=='' || $offermodel->status == $offermodel->enumStatus['open']){
				$offermodel->status=$offermodel->enumStatus['in-progress'];	
				$offermodel->created_by = $userid;
			}
			$offermodel->updated_by = $userid;

			// $userData = Yii::$app->userdata->getData();
			// $offermodel->updated_by=$userData['userid'];
						
			if($offermodel->validate() && $offermodel->save())
			{					
				$offerlistmodel = new OfferList();
				$offerlistmodel->updated_by = $userid;
				$offerlistmodel->offer_id=$offermodel->id;
				$offerlistmodel->conversion_rate=$data['conversion_rate'];
				$offerlistmodel->currency=$data['currency'];
				$offerlistmodel->conversion_currency_code=$data['conversion_currency_code'];
				$offerlistmodel->certification_fee_sub_total=$data['certification_fee_sub_total'];
				$offerlistmodel->other_expense_sub_total=$data['other_expense_sub_total'];
				$offerlistmodel->total=$data['total'];
				$offerlistmodel->tax_amount=$data['gst_rate'];
				$offerlistmodel->total_payable_amount=$data['total_payable_amount'];
				$offerlistmodel->conversion_total_payable=$data['conversion_total_payable'];
				$offerlistmodel->discount=$data['discount']?:0;
				$offerlistmodel->grand_total_fee=$data['grand_total_fee'];			
				$offerlistmodel->status=1;
				$offerlistmodel->created_by = $userid;
				$offerlistmodel->is_latest=1;
				$offerlistmodel->conversion_required_status=$data['conversion_required_status'];
								
				if($offerlistmodel->validate() && $offerlistmodel->save())
				{
					$conversionRate = $offerlistmodel->conversion_rate;
					$conversionRequiredStatus = $offerlistmodel->conversion_required_status;
					
					$offer_list_conversion_certification_fee_sub_total=0;
					$offer_list_conversion_other_expense_sub_total=0;
					$offer_list_conversion_total=0;
					$offer_list_conversion_tax_amount=0;
					
					if(is_array($data['unitManday']) && count($data['unitManday'])>0)
					{
						foreach ($data['unitManday'] as $value)
						{
							$unit_id = $value['unit_id'];
							$appunitmanday = ApplicationUnitManday::find()->where(['unit_id' => $unit_id])->one();
							$appunitmanday->adjusted_manday = $value['adjusted_manday'];
							$appunitmanday->translator_required = $value['translator_required'];
							$appunitmanday->final_manday_withtrans = $value['final_manday_withtrans'];
							$appunitmanday->adjusted_manday_comment = $value['adjusted_manday_comment'];
							$appunitmanday->save();
						}
					}
					if(is_array($data['certification_fee']) && count($data['certification_fee'])>0)
					{
						foreach ($data['certification_fee'] as $feekey => $value)
						{ 
							$certificationFee=new OfferListCertificationFee();
							$certificationFee->offer_list_id=$offerlistmodel->id;
							$certificationFee->activity=$value['activity'];
							$certificationFee->description=$value['description'];	
							$certificationFee->amount=$value['amount'];
							$conversionAmount = $certificationFee->amount;	
							if($conversionRequiredStatus==1)
							{
								$conversionAmount = $conversionAmount*$conversionRate;	
							}
							$certificationFee->conversion_amount=$conversionAmount;
							if($feekey == 0){
								$certificationFee->type = 1;
							}else if($feekey == 1){
								$certificationFee->type = 2;
							}else{
								$certificationFee->type = 3;
							}
							$offer_list_conversion_certification_fee_sub_total=$offer_list_conversion_certification_fee_sub_total+$conversionAmount;
							$certificationFee->save();	
						}
					}
					
					if(is_array($data['other_expenses']) && count($data['other_expenses'])>0)
					{
						foreach ($data['other_expenses'] as $value)
						{ 
							$otherExpenses=new OfferListOtherExpenses();
							$otherExpenses->offer_list_id=$offerlistmodel->id;
							$otherExpenses->activity=$value['activity'];
							$otherExpenses->description=$value['description'];	
							$otherExpenses->amount=$value['amount'];
							$conversionAmount = $otherExpenses->amount;	
							if($conversionRequiredStatus==1)
							{
								$conversionAmount = $conversionAmount*$conversionRate;	
							}
							$otherExpenses->conversion_amount=$conversionAmount;
							$offer_list_conversion_other_expense_sub_total=$offer_list_conversion_other_expense_sub_total+$conversionAmount;
							$otherExpenses->entry_type = $value['entry_type'];
							$otherExpenses->type = $value['type'];			
											
							$otherExpenses->save();
						}
					}	

					if (($modelApplication = Application::findOne($offermodel->app_id)) !== null) 
					{
						$applicationMandayCostObj = $modelApplication->applicationaddress->mandaycost;
						if($applicationMandayCostObj !== null)
						{
							$applicationMandayCostTaxObj=$applicationMandayCostObj->mandaycosttax;
							if(count($applicationMandayCostTaxObj)>0)
							{
								foreach($applicationMandayCostTaxObj as $appMandayCostTax)
								{
									$taxPercentage = $appMandayCostTax->tax_percentage;
									$offerListTax=new OfferListTax();
									$offerListTax->offer_list_id=$offerlistmodel->id;
									$offerListTax->man_day_cost_tax_id=$appMandayCostTax->id;
									$offerListTax->tax_name=$appMandayCostTax->tax_name;	
									$offerListTax->tax_percentage=$appMandayCostTax->tax_percentage;
									$offerListTax->amount=($offerlistmodel->total*$taxPercentage/100);	
									
									$conversionAmount = $offerListTax->amount;	
									if($conversionRequiredStatus==1)
									{
										$conversionAmount = $conversionAmount*$conversionRate;	
									}
									$offerListTax->conversion_amount=$conversionAmount;
									$offer_list_conversion_tax_amount=$offer_list_conversion_tax_amount+$conversionAmount;									
									$offerListTax->save();									
								}								
							}	
						}				
					}
					
					$offer_list_conversion_total=$offer_list_conversion_certification_fee_sub_total+$offer_list_conversion_other_expense_sub_total;
					$offerlistmodel->conversion_certification_fee_sub_total=$offer_list_conversion_certification_fee_sub_total;
					$offerlistmodel->conversion_other_expense_sub_total=$offer_list_conversion_other_expense_sub_total;
					$offerlistmodel->conversion_total=$offer_list_conversion_total;
					$offerlistmodel->conversion_tax_amount=$offer_list_conversion_tax_amount;
					$offerlistmodel->save();
										
					$responsedata=array('status'=>1,'message'=>'Offer has been generated successfully','offer_id'=>$offermodel->id);	
				}
				else
				{
					$responsedata=array('status'=>0,'message'=>$offerlistmodel->errors);
				}
			}				
		}
		return $this->asJson($responsedata);
	}
	

    public function actionCreate()
	{
		$data = Yii::$app->request->post();
		if ($data) 
		{
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$franchiseid=$userData['franchiseid'];
			$is_headquarters =$userData['is_headquarters'];
			$resource_access=$userData['resource_access'];

			if(!Yii::$app->userrole->isAdmin()){
				if(!Yii::$app->userrole->canViewApplication($data['app_id']) && !Yii::$app->userrole->hasRights(['offer_management'])){
					return false;
				}else{
					if($user_type ==1 && $is_headquarters !=1){
						$appmodel=Application::find()->where(['id' => $data['app_id']])->one();
						if($appmodel !== null){
							if($appmodel->franchise_id != $franchiseid){
								return false;
							}
						}else{
							return false;
						}
					}
				}
			}
			
			$date_format = Yii::$app->globalfuns->getSettings('date_format');			
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			header('Access-Control-Max-Age: 1000');
			header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
	
			$html = $this->generateHtmlOffer($data);
			$mpdf = new \Mpdf\Mpdf();
			$mpdf->WriteHTML($html);
			$mpdf->Output('offer.pdf','D');
		}
	}
	
	public function generateHtmlOffer($data){

		$appmodel=Application::find()->where(['id' => $data['app_id']])->one();
		$offerid=Offer::find()->where(['id' => $data['offer_id']])->one();
		$offerdetails=OfferList::find()->where(['offer_id' => $data['offer_id'],'is_latest'=>1])->one();
		$html = '';
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		if($appmodel !== null)
		{
			$appstds=$appmodel->applicationstandard; 
			$appStandardArr=array();
			if(count($appstds)>0)
			{
				foreach($appstds as $appstandard)
				{
					$appStandardArr[]=$appstandard->standard->code;
				}
			}

			$appunits=$appmodel->applicationunit; 
			$unitcount=count($appunits);
			$appUnitArr=array();
			//$inspectdaysArr=array();
			$numofemp=0;
			if($unitcount>0)
			{
				foreach($appunits as $units)
				{
					//$inspectmodel=new StandardInspectionTime();
					$appUnitArr[]=$units->name;
					//$inspectdaysArr[]=$inspectmodel->find()->select('inspector_days')->where(['>=', 'no_of_workers_to', $units->no_of_employees])->andWhere(['<=', 'no_of_workers_from', $units->no_of_employees])->asArray()->one();
				}
			}
			
			
			$mandays = $offerid->manday;

			
			$html='';
			

		 
			$html='
			<style>
			table {
			border-collapse: collapse;
			}

			table, td, th {
			border: 1px solid black;
			}
			
			table.reportDetailLayout {
				border: 1px solid #000000;
				border-collapse: collapse;
				width:100%;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				margin-bottom:5px;
				margin-top:5px;
			}
			td.reportDetailLayout {
				text-align: center;
				border: 1px solid #000000;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				background-color:#DFE8F6;
				padding:3px;
			}
			td.reportDetailLayoutHead {
				text-align: center;
				border: 1px solid #000000;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				/*background-color:#4e85c8;*/
				background-color:#006fc0;
				padding:3px;
				color:#FFFFFF;
			}

			td.reportDetailLayoutInner {
				text-align: center;
				border: 1px solid #000000;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				background-color:#ffffff;
				padding:3px;
			}
			</style>
			
			
			<div style="width:100%;font-size:12px;">
				<div style="text-align: left;width:48%;float:left;display: inline-block;font-size:12px;">
					<img src="'.Yii::$app->params['image_files'].'header-img.png" style="width:150px;" border="0">						
				</div>
				<div style="width:50%;float:right;display:inline-block;font-size:12px;font-family:Arial;">
					<div style="border: 1px solid #000000;padding-left:5px;padding-right:5px;">
						<p><b>GCL INTERNATIONAL LTD</b></p>
						<p>Level 1 | Devonshire House | One Mayfair Place London | W1J 8AJ | United Kingdom</p>
					</div>
					<p>Quotation Number: '.$offerid->offer_code.' <br>
					Date: '.date($date_format,$offerid->updated_at).'</p>
				
				</div>
			</div>
			
			<br>
			<h3 align="center" style="display: inline-block;font-family:Arial;font-size:18px;">QUOTATION/PROFORMA INVOICE</h3>
			<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">	
			    <tr>
				  <td colspan="6" align="left" style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Part 1 - Company Details</td>					  
				</tr>
				
				<tr>
				  <td width="18%" align="left" style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Company Name</td>
				  <td width="1%" align="center" style="text-align:center;" valign="middle" class="reportDetailLayoutInner">:</td>
				  <td width="81%" colspan="4" align="left" style="text-align:left" valign="middle" class="reportDetailLayoutInner">'.$appmodel->companyname.'</td>
				</tr>
				
				<tr>
				  <td width="18%" align="left" style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Company Address</td>
				  <td width="1%" align="center" style="text-align:center;" valign="middle" class="reportDetailLayoutInner">:</td>
				  <td width="81%" colspan="4" align="left" style="text-align:left" valign="middle" class="reportDetailLayoutInner">'.$appmodel->address.', '.$appmodel->city.' - '.$appmodel->zipcode.', '.$appmodel->statename.', '.$appmodel->countryname.'</td>
				</tr>
				
				<tr>
				  <td width="18%" align="left" style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Attention</td>
				  <td width="1%" align="center" style="text-align:center;" valign="middle" class="reportDetailLayoutInner">:</td>
				  <td width="31%" align="left" style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$appmodel->contactname.'</td>
				  <td width="18%" align="left" style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Standard(s)</td>
				  <td width="1%" align="center" style="text-align:center;" valign="middle" class="reportDetailLayoutInner">:</td>
				  <td width="31%" align="left" style="text-align:left" valign="middle" class="reportDetailLayoutInner">'.implode(", ",$appStandardArr).'</td>
				</tr>
				
				<tr>
				  <td width="18%" align="left" style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Email</td>
				  <td width="1%" align="center" style="text-align:center" valign="middle" class="reportDetailLayoutInner">:</td>
				  <td width="31%" align="left" style="text-align:left" valign="middle" class="reportDetailLayoutInner">'.$appmodel->emailaddress.'</td>
				  <td width="18%" align="left" style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Phone(s)</td>
				  <td width="1%" align="center" style="text-align:center;" valign="middle" class="reportDetailLayoutInner">:</td>
				  <td width="31%" align="left" style="text-align:left" valign="middle" class="reportDetailLayoutInner">'.$appmodel->telephone.'</td>
				</tr>
				
				
				<tr>
				  <td width="18%" align="left" style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Unit(s) Name</td>
				  <td width="1%" align="center" style="text-align:center" valign="middle" class="reportDetailLayoutInner">:</td>
				  <td width="31%" align="left" style="text-align:left" valign="middle" class="reportDetailLayoutInner">'.implode(",",$appUnitArr).'</td>
				  <td width="18%" align="left" style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">No.of Unit(s)</td>
				  <td width="1%" align="center" style="text-align:center;" valign="middle" class="reportDetailLayoutInner">:</td>
				  <td width="31%" align="left" style="text-align:left" valign="middle" class="reportDetailLayoutInner">'.$unitcount.'</td>
				</tr>
				
				<tr>
				  <td width="18%" align="left" style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Audit Type</td>
				  <td width="1%" align="center" style="text-align:center" valign="middle" class="reportDetailLayoutInner">:</td>
				  <td width="31%" align="left" style="text-align:left" valign="middle" class="reportDetailLayoutInner">Initial Audit</td>
				  <td width="18%" align="left" style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Audit Man day</td>
				  <td width="1%" align="center" style="text-align:center;" valign="middle" class="reportDetailLayoutInner">:</td>
				  <td width="31%" align="left" style="text-align:left" valign="middle" class="reportDetailLayoutInner">'.$mandays.'</td>
				</tr>				
				
			</table>';
			
			if($offerdetails !== null)
			{
				$offercertfee=OfferListCertificationFee::find()->where(['offer_list_id' => $offerdetails->id])->asArray()->all();
				$offerotherexp=OfferListOtherExpenses::find()->where(['offer_list_id' => $offerdetails->id])->asArray()->all();
				$conversion_required_status = $offerdetails->conversion_required_status;
				$html.='<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
				    <tr>
					  <td width="34%" align="left" style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Part 2 - Certification Fee</td>
					  <td width="51%" align="left" style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Description</td>
					  <td width="15%" align="right" style="text-align:right;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Amount ('.$offerdetails->currency.')</td>
					</tr>';

					if($offercertfee !== null)
					{
						if(count($offercertfee)>0)
						{
							foreach($offercertfee as $vals)
							{
								$html.='
								<tr>
								  <td width="34%" align="left" style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$vals['activity'].'</td>
								  <td width="51%" align="left" style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$vals['description'].'</td>
								  <td width="15%" align="right" style="text-align:right" valign="middle" class="reportDetailLayoutInner">'.$offerdetails->currency." ".$vals['amount'].'</td>
								</tr>';
							}
						}
					}
					

					$html.='
					<tr>
					  <td width="85%" colspan="2" align="right" style="text-align:right;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Sub-Total of Certification Fee:</td>
					  <td width="15%" align="right" style="text-align:right;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">'.$offerdetails->currency." ".$offerdetails->certification_fee_sub_total.'</td>
					</tr>
					<tr>
					  <td width="100%" colspan="3" align="left" style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Other Expenses</td>
					</tr>';

					if($offerotherexp !== null)
					{
						if(count($offerotherexp)>0)
						{
							$arrOE=array();
							$resultarr = [];
							$totalcertExpense = 0;
							$resultarr[] = array('activity'=>'License Fee','description'=> '','amount'=>number_format($totalcertExpense, 2, '.', ''));

							foreach($offerotherexp as $otherE)
							{
								//$cost=($conversion_required_status!=0)?$otherE['conversion_amount']:$otherE['amount'];
								$cost=$otherE['amount'];
								if($otherE['entry_type'] ==1)
								{

									$arrOE=array('activity'=>$otherE['activity'],'description'=>$otherE['description'],'amount'=>number_format($cost, 2, '.', ''));
									$resultarr[]=$arrOE;
								}
								else
								{
									$totalcertExpense += $cost;
								}
							}

							$resultarr[0] = array('activity'=>'License Fee','description'=> $offerid->standard,'amount'=>number_format($totalcertExpense, 2, '.', ''));
							foreach($resultarr as $vals)
							{
								$html.='
								<tr>
								  <td width="34%" align="left" style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$vals['activity'].'</td>
								  <td width="51%" align="left" style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$vals['description'].'</td>
								  <td width="15%" align="right" style="text-align:right" valign="middle" class="reportDetailLayoutInner">'.$offerdetails->currency." ".$vals['amount'].'</td>
								</tr>';
							}
						}
					}

					$html.='
					<tr>
					  <td width="85%" colspan="2" align="right" style="text-align:right;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Sub-Total of Other Expenses</td>
					  <td width="15%" align="right" style="text-align:right;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">'.$offerdetails->currency." ".$offerdetails->other_expense_sub_total.'</td>
					</tr>
					<tr>
					  <td width="85%" colspan="2" align="right" style="text-align:right;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Total</td>
					  <td width="15%" align="right" style="text-align:right;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">'.$offerdetails->currency." ".$offerdetails->total.'</td>
					</tr>

					<tr>
					  <td width="85%" colspan="2" align="right" style="text-align:right;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">'.$offerid->taxname.' Rate</td>
					  <td width="15%" align="right" style="text-align:right;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">'.$offerdetails->currency." ".$offerdetails->tax_amount.'</td>
					</tr>

					<tr>
					  <td width="85%" colspan="2" align="right" style="text-align:right;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Total Payable Amount</td>
					  <td width="15%" align="right" style="text-align:right;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">'.$offerdetails->currency." ".$offerdetails->total_payable_amount.'</td>
					</tr>';
					
					if($offerdetails->conversion_required_status==1)
					{
						/*
						$html.='
						<tr>
							<td width="85%" colspan="2" align="center" style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Conversion rate '.$offerdetails->currency.' 1.00 </u> = <u>'.$offerdetails->conversion_currency_code." ".$offerdetails->conversion_rate.'<br>Conversion rate is valid for next 30 days </td>
							<td width="15%" align="right" style="text-align:right;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">'.$offerdetails->conversion_currency_code." ".$offerdetails->conversion_total_payable.'</td>
						</tr>';
						*/
						$html.='
						<tr>
							<td width="85%" colspan="2" align="center" style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">&nbsp;</td>
							<td width="15%" align="right" style="text-align:right;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">'.$offerdetails->conversion_currency_code." ".$offerdetails->conversion_total_payable.'</td>
						</tr>';
						
					}	
				$html.='
					<tr>
					  <td width="100%" colspan="3" align="left" style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Charges of TC and LR are not included in the quotation this will be as per the GCL fee structure</td>					  
					</tr>
				</table>
				<div style="font-size:12px;font-family:Arial;"><u>Travel expenses and Accommodation fee will be born by Operator</u></div>
				<br>
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:30px;border:none;">	
						<tr>
							<td class="reportDetailLayoutInner" valign="top" style="border:none;font-weight:bold;">Agreement Acceptance:</td>
							<td class="reportDetailLayoutInner" style="border:none;">
							<p>In accepting this agreement, the Company confirms the information contained
							above is correct and agrees to comply with the <b>GCL International Ltd</b> Scheme
							Rules (http://gcl-intl.com/about-us/rules-of-registration/) and agree to pay all fees
							relating to the provision of certification services.</p>
							</td>
						</tr>
					</table>
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:50px;text-align:center;border:none;">					
						<tr style="margin-top:20px;text-align:center;">							
							<td class="reportDetailLayoutInner" style="text-align:center;border:none;">Signed: .................................</td>							
							<td class="reportDetailLayoutInner" style="text-align:center;border:none;">Date: .................................</td>
						</tr>
					</table>
				</div>';
			}
			
							
		}
		return $html;
	}
	private function calculateDiscount($appstds,$units)
	{
		$certificatediscount=0;
		if(count($appstds)>0)
		{
			$appreductionratetotal = 0;
			foreach($appstds as $stdval)
			{	
				$appreduction=StandardReduction::find()->where(['standard_id' => $stdval['standard_id']])->one();
				if($appreduction!==null)
				{
					$certified_std_ids = [];
					$appreductionrate = 0;

					if(is_array($units->unitstandard)&& count($units->unitstandard)>0){
						foreach($units->unitstandard as $unitstandards){
							$certified_std_ids[] = $unitstandards->standard_id;
						}
					}
					if(count($certified_std_ids)>0){
						$appreductionrate=StandardReductionRate::find()->select('reduction_percentage')
							->where(['standard_reduction_id' => $appreduction->id,'standard_id' => $certified_std_ids])
							->sum('reduction_percentage');
					}
					$appreductionratetotal += $appreductionrate;
					
				}
			}
			$certificatediscount=($appreductionratetotal<50)?$appreductionratetotal:50;
		}
		return $certificatediscount;
	}


	public function actionView()
	{
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		if ($data) 
		{	
			$connection = Yii::$app->getDb();
			$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();

			$offermodel = Offer::find()->where(['app_id' => $data['id']])->one();	

			if($offermodel===null){
				$canViewGenerateOffer = $this->canEditOffer(0,$data['id']);
			}else{
				$canViewGenerateOffer = $this->canEditOffer($offermodel->id,$data['id']);
			}
			
			if(!$canViewGenerateOffer){
				return false;
			}
			/*********** Application Manday Insertion code Starts here  ******************/
			
			$model = Application::find()->where(['id' => $data['id']])->one();
			$appunits = ApplicationUnit::find()->where(['app_id' => $data['id']])->all();
			$arrAppunitmanday=array();
			$arrAppunitmandaydiscount=array();
			$licenseFeeArr=array();
			if($appunits !== null && $offermodel===null )
			{
				
				$unitcount=count($appunits);
				$inspectdaysArr=array();
				$arrAppUnitStandardforSubsequentFee=array();
				$arrUnitStandardforSubsequentFee=array();				
				
				$appAuditType = $model->audit_type;
				if($appAuditType == $model->arrEnumAuditType['unit_addition'])
				{
					$parentApplicationModel = Application::find()->where(['id' => $model->parent_app_id])->one();
					if($parentApplicationModel !== null)
					{
						$appStandard = $parentApplicationModel->applicationstandard;
						if(count($appStandard)>0)
						{
							foreach($appStandard as $std)
							{
								if($std->standard && $std->standard->id!='')
								{
									$stdID = $std->standard->id;
									$arrAppUnitStandardforSubsequentFee[] = $stdID;
									$arrUnitStandardforSubsequentFee[] = $stdID;
								}						
							}
						}
					}	
				}						

				$appmandays=0;
				$appdiscountmandays=0;
				$appfinalmandays=0;
				$apptotalmandaycost=0;
				$unitappstandardCount=0;
				$appunitCount = count($model->applicationunit);
				if(is_array($model->applicationunit) && $appunitCount>0)
				{	
					foreach($model->applicationunit as $units)
					{
						$arrSameStandardCertified=array();
						$unitID = $units->id;
						$unitappstandard = $units->unitappstandard;
						$unitappstandardCount = count($unitappstandard);
						$no_of_employees = $units->no_of_employees;	
						$appreductionratetotal = 0;		
						$appreductioncombinationratetotal=0;						
												
						// ---------- Process - Trading based Manday Calcualtion Start Here -------------
						$tradingProcessManday = 0;
						$trading_manday=0;
						$tradingProcessCount = Yii::$app->globalfuns->getUnitTradingProcessCount(array('unit_id'=>$unitID));
						if($tradingProcessCount>0)
						{
							$StandardInspectionTimeTradingProcess = StandardInspectionTimeTradingProcess::find()->where(['>=', 'no_of_standard_to', $unitappstandardCount])->andWhere(['<=', 'no_of_standard_from', $unitappstandardCount])->one();
							if($StandardInspectionTimeTradingProcess!==null){
								$trading_manday = $StandardInspectionTimeTradingProcess->inspector_days;
							}
							/*$trading_manday=1;
							if($no_of_employees<=50)
							{
								$trading_manday=0.5;
							}
							*/
							$tradingProcessManday = $trading_manday;//$unitappstandardCount*$trading_manday;
						}
						// ---------- Process - Trading based Manday Calcualtion End Here -------------
						
						// --------- Application Unit Manday Save Code Start Here -------------
						$appunitmanday = ApplicationUnitManday::find()->where(['unit_id' => $units->id])->one();
						if($appunitmanday===null)
						{
							$appunitmanday=new ApplicationUnitManday();
						}					
						$appunitmanday->app_id=$model->id;
						$appunitmanday->unit_id=$units->id;
						$appunitmanday->trading_process_count = $tradingProcessCount;
						$appunitmanday->trading_process_standard_count = $unitappstandardCount;
						$appunitmanday->trading_process_inspection_manday = $trading_manday;
						$appunitmanday->trading_process_manday = $tradingProcessManday;						
						$appunitmanday->save();
						$appUnitMandayID = $appunitmanday->id;	
						$ApplicationUnitMandayStandardDel = ApplicationUnitMandayStandard::find()->where(['application_unit_manday_id' => $appUnitMandayID])->all();
						if(count($ApplicationUnitMandayStandardDel)>0){
							foreach($ApplicationUnitMandayStandardDel as $standarddel){
								ApplicationUnitMandayStandardDiscount::deleteAll(['application_unit_manday_standard_id' => $standarddel->id ]);
							}
						}
						ApplicationUnitMandayStandard::deleteAll(['application_unit_manday_id' => $appUnitMandayID]);
						// ---------- Application Unit Manday Save Code End Here ------------
						
						// ---------- No of Workers & Process based Manday Calcualtion Start Here -------------
						$NoofWorkersProcessBasedInspectionManday=0;
						$processBasedInspectionManday=0;
						$totalNoofWorkersProcessBasedInspectionManday=0;
						$totalProcessBasedInspectionManday=0;
																		
						if(is_array($unitappstandard) && count($unitappstandard)>0)
						{
							$onlytrading = 0;
							$coreProcessCount = Yii::$app->globalfuns->getUnitCoreProcessCount(array('unit_id'=>$unitID));
							if($coreProcessCount>0){
								$trading_manday = 0;
								$tradingProcessManday = 0;
								$appunitmanday->trading_process_inspection_manday = $trading_manday;
								$appunitmanday->trading_process_manday = $tradingProcessManday;						
								$appunitmanday->save();
							}else{
								if($trading_manday>0){
									$appunitmanday->final_manday = $trading_manday;
									$appunitmanday->save();
									$onlytrading = 1;
								}
								
							}

							$totalNoofWorkersStandard=0;
							$totalProcessStandard=0;
							$no_of_workers_from=0;
							$no_of_workers_to=0;
							
							$no_of_workers_process_from=0;
							$no_of_workers_process_to=0;
							
							$no_of_process_from=0;
							$no_of_process_to=0;
							$UnitStandardIDs = [];
							foreach($unitappstandard as $unitstandards)
							{
								$UnitStandardIDs[] = $unitstandards->standard_id;
								//echo $unitstandards->standard_id;
							}
							$ReductionForStandardID = '';

							if(count($UnitStandardIDs)>0){
								$MasterStandard = Standard::find()->where(['id'=>$UnitStandardIDs])->orderBy(['priority'=>SORT_ASC])->one();
								$ReductionForStandardID = $MasterStandard->id;
							}
							

							/*
							$appreduction=StandardReduction::find()->where(['standard_id' => $unitStandardID])->one();
							if($appreduction!==null)
							{
							*/

							foreach($unitappstandard as $unitstandards)
							{
								//$NoofWorkersProcessBasedInspectionManday=0;
								//$processBasedInspectionManday=0;

								$unitStandardID=$unitstandards->standard_id;
								$unitstandardcode = $unitstandards->standard->code;

								$ApplicationUnitMandayStandardID=0;
								

								
								// ------------- No of Workers & Process Based Manday Calculation Start Here ------------------
								$StandardInspectionTimeStandardModel = StandardInspectionTimeStandard::find()->where(['inspection_time_type'=>0,'standard_id' => $unitStandardID])->one();
								if($StandardInspectionTimeStandardModel!==null)
								{
									$inspectmodel=new StandardInspectionTime();
									$inspectdaysArr=$inspectmodel->find()->select(['id','inspector_days','no_of_workers_from','no_of_workers_to'])->where(['>=', 'no_of_workers_to', $no_of_employees])->andWhere(['<=', 'no_of_workers_from', $no_of_employees])->asArray()->one();
									if(count($inspectdaysArr)>0)
									{
										$NoofWorkersID = $inspectdaysArr['id'];
										
										$no_of_workers_from=$inspectdaysArr['no_of_workers_from'];
										$no_of_workers_to=$inspectdaysArr['no_of_workers_to'];
										
										$InspectionTimeProcessModel=new StandardInspectionTimeProcess();
										$processInspectdaysArr=$InspectionTimeProcessModel->find()->select(['id','inspector_days','no_of_process_from','no_of_process_to'])->where(['standard_inspection_time_id'=>$NoofWorkersID])->andWhere(['>=', 'no_of_process_to', $coreProcessCount])->andWhere(['<=', 'no_of_process_from', $coreProcessCount])->asArray()->one();
										if($processInspectdaysArr !== null)
										{
											$NoofWorkersProcessBasedInspectionManday=$processInspectdaysArr['inspector_days'];																					
											$no_of_workers_process_from=$processInspectdaysArr['no_of_process_from'];
											$no_of_workers_process_to=$processInspectdaysArr['no_of_process_to'];										
										}
									}
									$totalNoofWorkersStandard++;
									$inspection_time_type = 0;									
								}								
								//$totalNoofWorkersProcessBasedInspectionManday+=$totalNoofWorkersStandard*$NoofWorkersProcessBasedInspectionManday;															
								// ------------- No of Workers & Process Based Manday Calculation End Here ------------------
								
								
								// ------------- Process Based Manday Calculation Start Here ------------------
								$StandardInspectionTimeStandardModel = StandardInspectionTimeStandard::find()->where(['inspection_time_type'=>1,'standard_id' => $unitStandardID])->one();
								if($StandardInspectionTimeStandardModel!==null)
								{
									$InspectionTimeProcessModel=new StandardOtherInspectionTimeProcess();
									//$processInspectdaysArr=$InspectionTimeProcessModel->find()->select(['id','inspector_days','no_of_process_from','no_of_process_to'])->where(['standard_inspection_time_id'=>$NoofWorkersID])->andWhere(['>=', 'no_of_process_to', $coreProcessCount])->andWhere(['<=', 'no_of_process_from', $coreProcessCount])->asArray()->one();
									$processInspectdaysArr=$InspectionTimeProcessModel->find()->select(['id','inspector_days','no_of_process_from','no_of_process_to'])->where(['>=', 'no_of_process_to', $coreProcessCount])->andWhere(['<=', 'no_of_process_from', $coreProcessCount])->asArray()->one();
									//->where(['standard_inspection_time_id'=>$NoofWorkersID])
									if($processInspectdaysArr !== null)
									{
										$processBasedInspectionManday=$processInspectdaysArr['inspector_days'];
										
										$no_of_process_from=$processInspectdaysArr['no_of_process_from'];
										$no_of_process_to=$processInspectdaysArr['no_of_process_to'];
									}
									$totalProcessStandard++;
									$inspection_time_type = 1;
								}
								//$totalProcessBasedInspectionManday+=$totalProcessStandard*$processBasedInspectionManday;
								// ------------- Process Based Manday Calculation End Here ------------------
								
								$ApplicationUnitMandayStandardInspectionManday=0;
								//$ApplicationUnitMandayStandardInspectionManday=$NoofWorkersProcessBasedInspectionManday?:$processBasedInspectionManday;
								$ApplicationUnitMandayStandardInspectionManday=$inspection_time_type==0?$NoofWorkersProcessBasedInspectionManday:$processBasedInspectionManday;

								$ApplicationUnitMandayStandardModel=new ApplicationUnitMandayStandard();
								$ApplicationUnitMandayStandardModel->application_unit_manday_id=$appUnitMandayID;
								$ApplicationUnitMandayStandardModel->unit_id=$unitID;
								$ApplicationUnitMandayStandardModel->standard_id=$unitStandardID;
								$ApplicationUnitMandayStandardModel->inspector_days=$ApplicationUnitMandayStandardInspectionManday;
								$ApplicationUnitMandayStandardModel->inspection_time_type=$inspection_time_type;
								$ApplicationUnitMandayStandardModel->trading_process_manday=$trading_manday;

								$ApplicationUnitMandayStandardModel->save();								
								$ApplicationUnitMandayStandardID = $ApplicationUnitMandayStandardModel->id;
								
								$reductionStdIDs=array();
								
								if($onlytrading ==0 && $ReductionForStandardID!=$unitStandardID){
									// ------------- Standard Combination Discount Code Start Here ------------------
									
									$appreduction=StandardReduction::find()->where(['standard_id' => $unitStandardID])->one();
									if($appreduction!==null)
									{
										$appreductioncombinationrate=0;
										//------------------
										$arrStandardCombinationDiscountCode=array();
										foreach($unitappstandard as $ustds)
										{
											if($unitStandardID!=$ustds->standard_id)
											{
												$arrStandardCombinationDiscountCode[]=$ustds->standard->code;
											}
										}
										
										if(count($arrStandardCombinationDiscountCode)>0)
										{
											
											$rtnStandard=ReductionStandard::find()->where(['code' => $arrStandardCombinationDiscountCode])->all();
											if(count($rtnStandard)>0)
											{
												foreach($rtnStandard as $rstd)
												{
													$reductionStdIDs[]=$rstd->id;
												}										
											}
											
											if(count($reductionStdIDs)>0)
											{
												$appReductionStdRate=StandardReductionRate::find()->select('id,standard_id,reduction_percentage')->where(['standard_reduction_id' => $appreduction->id,'standard_id' => $reductionStdIDs])->all();
												if(count($appReductionStdRate)>0)
												{
													foreach($appReductionStdRate as $reductionStdRate)
													{
														$appreductioncombinationrate = $reductionStdRate->reduction_percentage;
																																						
														$appunitmandaydiscount = ApplicationUnitMandayStandardDiscount::find()->where(['application_unit_manday_standard_id' => $ApplicationUnitMandayStandardID,'certificate_standard_id'=>$reductionStdRate->standard_id])->one();
														if($appunitmandaydiscount===null)
														{
															$appunitmandaydiscount=new ApplicationUnitMandayStandardDiscount();
															

															//$appunitmandaydiscount->standard_id=$unitCertifiedStandard->standard_id;
														}
														
														$appunitmandaydiscount->application_unit_manday_standard_id=$ApplicationUnitMandayStandardID;
														$appunitmandaydiscount->certificate_standard_id=$reductionStdRate->standard_id;
														
														$appunitmandaydiscount->discount=$appreductioncombinationrate;
														
														/*
														if(in_array($unitCertifiedStandard->standard_id, $arrSameStandardCertified))
														{
															$appunitmandaydiscount->same_standard_certified=2;
														}
														*/		
														
														$appunitmandaydiscount->save();

														
														//if($appunitmandaydiscount->status!=2 && !in_array($unitCertifiedStandard->standard_id, $arrSameStandardCertified))
														//{
															$appreductioncombinationratetotal += $appreductioncombinationrate;
														//}
													}
												}
											}	
										}			
										//------------------
									}								
									// ------------- Standard Combination Discount Code End Here ------------------
								}

								
								$ApplicationUnitMandayStandardModel->total_discount = 0;
								$ApplicationUnitMandayStandardModel->maximum_discount = 0;

								if($onlytrading ==0){

									// ------------- Facility / Subcontractor already certified with the same "Standard",  100% discount will apply for that particular standard Code Start Here -------------------
									$ReductionStandardObj = ReductionStandard::find()->where(['code'=>$unitstandardcode])->one();
									$reduction_standard_id = '';
									if($ReductionStandardObj !== null){
										$reduction_standard_id = $ReductionStandardObj->id;
									}
									if($reduction_standard_id!=''){
										//$applicationUnitCertifiedStandardModel = ApplicationUnitCertifiedStandard::find()->where(['unit_id' => $unitID,'standard_id'=>$unitStandardID])->one();
										$applicationUnitCertifiedStandardModel = ApplicationUnitCertifiedStandard::find()->where(['unit_id' => $unitID,'standard_id'=>$reduction_standard_id])->one();
										if($applicationUnitCertifiedStandardModel!==null)
										{	
											//'standard_id'=>$unitStandardID RRR																	
											$appunitSameStandardCertifiedMandayDiscount = ApplicationUnitMandayStandardDiscount::find()->where(['application_unit_manday_standard_id' => $ApplicationUnitMandayStandardID,'certificate_standard_id'=>$reduction_standard_id])->one();
											if($appunitSameStandardCertifiedMandayDiscount===null)
											{
												$appunitSameStandardCertifiedMandayDiscount=new ApplicationUnitMandayStandardDiscount();
												$appunitSameStandardCertifiedMandayDiscount->application_unit_manday_standard_id=$ApplicationUnitMandayStandardID;
												$appunitSameStandardCertifiedMandayDiscount->certificate_standard_id=$reduction_standard_id;
											}	
											//$appunitSameStandardCertifiedMandayDiscount->standard_id=$uStandardID;
											$appunitSameStandardCertifiedMandayDiscount->same_standard_certified=1;
											$appunitSameStandardCertifiedMandayDiscount->discount=100;
											$appunitSameStandardCertifiedMandayDiscount->save();
											
											//if($appunitSameStandardCertifiedMandayDiscount->same_standard_certified==1)
											//{
												$arrSameStandardCertified[]=$reduction_standard_id;
											//}		
											
											$ApplicationUnitMandayStandardModel->total_discount = 100;
											$ApplicationUnitMandayStandardModel->eligible_discount = 100;
											$ApplicationUnitMandayStandardModel->maximum_discount = 100;
											//$ApplicationUnitMandayStandardModel->inspector_days = 

										}
									}
									// ------------- Facility / Subcontractor already certified with the same "Standard",  100% discount will apply for that particular standard Code End Here -------------------
								
								

									
									// ------------- Maximum discount is 50% for the Units based on the "Existing Certified Standard" with "Standard Reduction"  Code Start Here --------------
									$appreduction=StandardReduction::find()->where(['standard_id' => $unitStandardID])->one();
									if($appreduction!==null)
									{
										$certified_std_ids = [];
										$appreductionrate = 0;
										$existingStdIds = [];
										if(is_array($units->unitstandard)&& count($units->unitstandard)>0)
										{
											foreach($units->unitstandard as $unitCertifiedStandard)
											{
												$existingStdIds[] = $unitCertifiedStandard->standard_id;
												if(in_array($unitCertifiedStandard->standard_id,$reductionStdIDs)){
													continue;
												}
												
												$appreductionrate=StandardReductionRate::find()->select('id,reduction_percentage')->where(['standard_reduction_id' => $appreduction->id,'standard_id' => $unitCertifiedStandard->standard_id])->one();
												if($appreductionrate!==null)
												{
													$appreductionrate = $appreductionrate->reduction_percentage;
																																						
													$appunitmandaydiscount = ApplicationUnitMandayStandardDiscount::find()->where(['application_unit_manday_standard_id' => $ApplicationUnitMandayStandardID,'certificate_standard_id'=>$unitCertifiedStandard->standard_id])->one();
													if($appunitmandaydiscount===null)
													{
														$appunitmandaydiscount=new ApplicationUnitMandayStandardDiscount();
														$appunitmandaydiscount->application_unit_manday_standard_id=$ApplicationUnitMandayStandardID;
														$appunitmandaydiscount->certificate_standard_id=$unitCertifiedStandard->standard_id;
													}																									
													//$appunitmandaydiscount->unit_manday_id=$appUnitMandayID;
													//$appunitmandaydiscount->standard_id=$unitCertifiedStandard->standard_id;
													//$appunitmandaydiscount->certificate_standard_id=$unitStandardID; RRR
													
													$appunitmandaydiscount->standard_type = $unitCertifiedStandard->standard->type;
													$appunitmandaydiscount->discount=$appreductionrate;
													
													//Commented If on July 01,2020
													//if(in_array($unitCertifiedStandard->standard_id, $arrSameStandardCertified))
													//{
														$appunitmandaydiscount->same_standard_certified=2;
														
													//}													
													$appunitmandaydiscount->save();

													
													if($appunitmandaydiscount->status!=2 && !in_array($unitCertifiedStandard->standard_id, $arrSameStandardCertified))
													{
														$appreductionratetotal += $appreductionrate;
													}
												}																								
											}
										}

										if($model->audit_type == $model->arrEnumAuditType['standard_addition']){
											//$existingStdIds
											$parent_app_id = $model->parent_app_id;
											
											//$ApplicationStandard = ApplicationStandard::find()->where(['standard_status'=>0,'app_id'=>$parent_app_id])->all();

											//$ApplicationStandard = ApplicationStandard::find()->where(['standard_status'=>0,'app_id'=>$parent_app_id])->all();
											$customer_id = $model->customer_id;
											$connection = Yii::$app->getDb();
											$command = $connection->createCommand("SELECT standard.code as standard_code,cert.standard_id,app.id, app.customer_id,cert.certificate_status FROM tbl_application AS app INNER JOIN tbl_application_standard appstd 
													ON app.id=appstd.app_id  AND app.customer_id=".$customer_id." 
													INNER JOIN tbl_standard AS standard ON standard.id = appstd.standard_id 
													INNER JOIN `tbl_audit` AS audit ON audit.app_id = app.id 
													INNER JOIN `tbl_certificate` AS cert ON cert.audit_id = audit.id  AND cert.certificate_status = 0 
													GROUP BY cert.standard_id");
											$result = $command->queryAll();
											
												//$offerCount = $result['offer_count'];
											

											//if(count($ApplicationStandard)>0){
											if(count($result)>0)
											{
												foreach($result as $alreadyStd){

													$standard_app_id = $alreadyStd['standard_id'];
													$standard_code = $alreadyStd['standard_code'];
													//IF standard added as addition get its app id 
													/*
													$standard_addition_type = $alreadyStd->standard_addition_type;
													if($standard_addition_type == 1){
														$StandardParentApp = Application::find()->alias('t')->where(['t.parent_app_id'=>$parent_app_id,'audit_type'=>$model->arrEnumAuditType['standard_addition'] ]);
														$StandardParentApp = $StandardParentApp->innerJoinWith(['audit as audit'])->innerJoinWith(['applicationstandard as appstandard'])->andWhere('appstandard.standard_status=0 and appstandard.standard_id="'.$alreadyStd->standard_id.'"');
														$StandardParentApp = $StandardParentApp->join('inner join', 'tbl_certificate as cert','audit.id =cert.audit_id and cert.certificate_status="0" ')->one();
														if($StandardParentApp !== null){
															$standard_app_id = $StandardParentApp->id;
														}
													}else{
														$standard_app_id = $parent_app_id;
													}
													*/
													if($standard_app_id !== ''){
														$ReductionStandardExistObj = ReductionStandard::find()->where(['code'=>$standard_code])->one();
														if($ReductionStandardExistObj !== null){
															$appreductionrate=StandardReductionRate::find()->select('id,reduction_percentage')->where(['standard_reduction_id' => $appreduction->id,'standard_id' => $ReductionStandardExistObj->id])->one();
															if($appreductionrate!==null)
															{
																$appreductionrate = $appreductionrate->reduction_percentage;
																$appunitmandaydiscount = ApplicationUnitMandayStandardDiscount::find()->where(['application_unit_manday_standard_id' => $ApplicationUnitMandayStandardID,'certificate_standard_id'=>$ReductionStandardExistObj->id])->one();
																if($appunitmandaydiscount===null)
																{
																	$appunitmandaydiscount=new ApplicationUnitMandayStandardDiscount();
																	$appunitmandaydiscount->application_unit_manday_standard_id=$ApplicationUnitMandayStandardID;
																	$appunitmandaydiscount->certificate_standard_id=$ReductionStandardExistObj->id;
																}																									
																$appunitmandaydiscount->discount=$appreductionrate;
																$appunitmandaydiscount->standard_app_id=$standard_app_id;
																//if(in_array($unitCertifiedStandard->standard_id, $arrSameStandardCertified))
																//{
																	$appunitmandaydiscount->same_standard_certified=3;
																//}													
																$appunitmandaydiscount->save();
																//if($appunitmandaydiscount->status!=2 && !in_array($unitCertifiedStandard->standard_id, $arrSameStandardCertified))
																//{
																//	$appreductionratetotal += $appreductionrate;
																//}
															}
														}
													}
													
													
												}
											}
										}


									}
								}

								// Storing Calculated Discount Starts 
								/*
								$settingsmodel=Settings::find()->select(['maximum_discount'])->one();
								if($appreductionratetotal < $settingsmodel->maximum_discount)
								{
									$ApplicationUnitMandayStandardModel->eligible_discount = $appreductionratetotal;
								}
								else
								{
									$ApplicationUnitMandayStandardModel->eligible_discount = $settingsmodel->maximum_discount;
								}
								$ApplicationUnitMandayStandardModel->total_discount = $appreductionratetotal;
								$ApplicationUnitMandayStandardModel->maximum_discount = $settingsmodel->maximum_discount;
								*/

								$settingsmodel=Settings::find()->select(['maximum_discount'])->one();
								$setting_maximum_discount = $settingsmodel->maximum_discount;
								$totalappunitmandaydiscount = ApplicationUnitMandayStandardDiscount::find()->where(['application_unit_manday_standard_id' => $ApplicationUnitMandayStandardID])->all();
								$ApplicationUnitMandayStandardModel->maximum_discount = $setting_maximum_discount;

								$standardcur_totaldiscount = 0;
								$samestandarcertifiedChk = 0;
								$standard_types = [];
								$standard_type_discount_max = [];
								if(count($totalappunitmandaydiscount)>0){
									foreach($totalappunitmandaydiscount as $totdiscnt){
										
										if($totdiscnt->same_standard_certified == 1){
											$ApplicationUnitMandayStandardModel->eligible_discount = 100;
											$ApplicationUnitMandayStandardModel->maximum_discount = 100;
											$samestandarcertifiedChk = 1;
										}

										$curDiscount = $totdiscnt->discount; // To default get standard discount

										// New code july 10,2020 starts for applying 1 time discount for social,environment
										if($totdiscnt->standard_type != '1' && $totdiscnt->standard_type != '0'){

											if(in_array($totdiscnt->standard_type,$standard_types)){
												$existing_type_discount = $standard_type_discount_max[$totdiscnt->standard_type];
												if($totdiscnt->discount > $existing_type_discount){
													$standard_type_discount_max[$totdiscnt->standard_type] = $totdiscnt->discount;

													// To remove previous low discount and add highest discount will be added in flow
													$standardcur_totaldiscount = $standardcur_totaldiscount - $existing_type_discount; 
												}else{
													$curDiscount = 0; // when current discount is low then dont add any discount for same standard type
												}
												
											}else{
												$standard_types[] = $totdiscnt->standard_type;
												$standard_type_discount_max[$totdiscnt->standard_type] = $totdiscnt->discount;
											}
										}
										//print_r($standard_types);
										//print_r($standard_type_discount_max);
										//echo $curDiscount;
										// New code july 10,2020 Ends
										//echo $curDiscount.'==';
										$standardcur_totaldiscount += $curDiscount;
									}
								}
								//echo $standardcur_totaldiscount;
								//echo '<br>';
								if($samestandarcertifiedChk ==0){
									if($standardcur_totaldiscount < $settingsmodel->maximum_discount)
									{
										$ApplicationUnitMandayStandardModel->eligible_discount = $standardcur_totaldiscount;
									}else
									{
										$ApplicationUnitMandayStandardModel->eligible_discount = $setting_maximum_discount;
									}
								}
								
								/*
								else
								{
									$ApplicationUnitMandayStandardModel->eligible_discount = $settingsmodel->maximum_discount;
								}
								*/
								$ApplicationUnitMandayStandardModel->total_discount = $standardcur_totaldiscount;
								
								// Storing Calculated Discount Ends







								// = $ApplicationUnitMandayStandardModel->inspector_days;
								$discount_manday = 0;
								$calmanday = $ApplicationUnitMandayStandardModel->inspector_days + $ApplicationUnitMandayStandardModel->trading_process_manday;
								if($ApplicationUnitMandayStandardModel->eligible_discount>0){
									//$discount_manday = ($ApplicationUnitMandayStandardModel->inspector_days * $ApplicationUnitMandayStandardModel->eligible_discount)/100;
									$discount_manday = ($calmanday * $ApplicationUnitMandayStandardModel->eligible_discount)/100;
								}
								$final_manday =$calmanday - $discount_manday;
								$ApplicationUnitMandayStandardModel->discount_manday = $discount_manday;
								$ApplicationUnitMandayStandardModel->actual_final_manday = $final_manday;
								$ApplicationUnitMandayStandardModel->final_manday = ceil($final_manday * 2)/2;
								$ApplicationUnitMandayStandardModel->save();
								// ------------- Maximum discount is 50% for the Units based on the "Existing Certified Standard" with "Standard Reduction" Code End Here----------
								 

								// ------------- License Fee Code Start Here --------------------------														
								$licenseFeeArr=array();
								$unitStandardName = $unitstandards->standard->name;
								$unitStandardCode = $unitstandards->standard->code;
								$unitStdID = $unitstandards->standard->id;
			
								$licenseFee = $unitstandards->standardlicensefee->license_fee;
								$subsequentLicenseFee = $unitstandards->standardlicensefee->subsequent_license_fee;
								if (in_array($unitStdID, $arrAppUnitStandardforSubsequentFee) && $subsequentLicenseFee>=0 && strtolower($unitStandardCode)!='gots')
								{
									$licenseFee = $subsequentLicenseFee;									
								}								
								$arrAppUnitStandardforSubsequentFee[]=$unitStdID;							
								
								$appunitlicensefee = ApplicationUnitLicenseFee::find()->where(['unit_id'=>$units->id,'standard_id'=>$unitStdID])->one();
								if($appunitlicensefee===null)
								{
									$appunitlicensefee = new ApplicationUnitLicenseFee();
								}	
								$appunitlicensefee->unit_id=$units->id;
								$appunitlicensefee->standard_id=$unitStdID;
								$appunitlicensefee->license_fee=$licenseFee;
								$appunitlicensefee->subsequent_license_fee=$subsequentLicenseFee;
								$appunitlicensefee->save();		

								
								// ----------------------- License Fee Code End Here --------------------------
										
							}	
							
							$totalNoofWorkersProcessBasedInspectionManday=$totalNoofWorkersStandard*$NoofWorkersProcessBasedInspectionManday;	
							//echo $totalProcessStandard.'*'.$processBasedInspectionManday;
							$totalProcessBasedInspectionManday=$totalProcessStandard*$processBasedInspectionManday;
							
							// Application Unit Manday Update Code Start Here
							$appunitmanday->core_process_count=$coreProcessCount;
							$appunitmanday->no_of_workers_from=$no_of_workers_from;
							$appunitmanday->no_of_workers_to=$no_of_workers_to;
							$appunitmanday->no_of_workers_process_from=$no_of_workers_process_from;
							$appunitmanday->no_of_workers_process_to=$no_of_workers_process_to;
							$appunitmanday->no_of_workers_inspection_manday=$totalNoofWorkersProcessBasedInspectionManday;
							
							$appunitmanday->no_of_process_from=$no_of_workers_from;
							$appunitmanday->no_of_process_to=$no_of_workers_to;
							$appunitmanday->no_of_process_inspection_manday=$totalProcessBasedInspectionManday;
							$appunitmanday->save();	
							// Application Unit Manday Update Code End Here
							
						}	
						//Application Standard Loop Ends Here









						// Application Unit Manday Update Code Start Here	
						//$appunitmanday->manday=$appunitmanday->trading_process_manday+$appunitmanday->no_of_workers_inspection_manday+$appunitmanday->no_of_process_inspection_manday;
						//$appmandays = $appmandays + $appunitmanday->manday;
						//$appunitmanday->total_discount=$appreductionratetotal;
						/*
						$settingsmodel=Settings::find()->select(['maximum_discount'])->one();						
						if($appreductionratetotal < $settingsmodel->maximum_discount)
						{
							//$appunitmanday->eligible_discount=$appreductionratetotal;
						}
						else
						{
							//$appunitmanday->eligible_discount=$settingsmodel->maximum_discount;
						}
						*/
						//$appunitmanday->maximum_discount=$settingsmodel->maximum_discount;
						//$discountmanday=$appunitmanday->manday*$appunitmanday->eligible_discount/100;
						//$discountmanday=1;
						//$appdiscountmandays += $discountmanday;
						//$finalmanday=$appunitmanday->manday-$discountmanday;
						//$appfinalmandays += $finalmanday;
						//$apptotalmandaycost += $units->mandaycost->man_day_cost;

						$discountmanday = 0;
						$finalmanday = 0;
						$inspector_days = 0;
						
						$unitmandaystandard = $appunitmanday->unitmandaystandard;
						if(count($unitmandaystandard)>0){
							foreach($unitmandaystandard as $mandaystandard){
								$discountmanday += $mandaystandard->discount_manday;
								$finalmanday += $mandaystandard->final_manday;
								$inspector_days += $mandaystandard->inspector_days;
							}
						}
						//$finalmanday = $finalmanday + $appunitmanday->trading_process_manday;
						//$finalmanday = $finalmanday + $appunitmanday->trading_process_manday;
						//$appunitmanday->trading_process_manday+$appunitmanday->no_of_workers_inspection_manday+$appunitmanday->no_of_process_inspection_manday;

						//changed on oct 15,2020 for trading process dynamic
						if($onlytrading ==0){
							$appunitmanday->manday=$inspector_days+$appunitmanday->trading_process_manday;
							$appunitmanday->discount_manday=$discountmanday;
							$appunitmanday->final_manday=$finalmanday;
						}else{
							$appunitmanday->manday=$inspector_days+$appunitmanday->trading_process_manday;
							$appunitmanday->discount_manday=$discountmanday;
							$finalmanday = $appunitmanday->final_manday;
						}
						
						if($appunitmanday->adjusted_manday =='' || $appunitmanday->adjusted_manday ==0.00 || $appunitmanday->adjusted_manday =='0.00')
						{
							$appunitmanday->adjusted_manday=$finalmanday;
						}else{
							$finalmanday = $appunitmanday->adjusted_manday;
						}
						
						$appunitmanday->manday_cost=$units->mandaycost->man_day_cost;
						$appunitmanday->unit_manday_cost=($finalmanday*$units->mandaycost->man_day_cost);
						$apptotalmandaycost+=$appunitmanday->unit_manday_cost;
						
						$appunitmanday->save();
						// Application Unit Manday Update Code End Here
						
						// ---------- No of Workers & Process based Manday Calcualtion End Here -------------
						
						
					}					
					
					/*
					$appmandays=3;
					$appdiscountmandays=1;
					$appfinalmandays=2;
					$apptotalmandaycost=3000;
					*/	
					

					$appmandays = 0;
					$appdiscountmandays = 0;
					$appfinalmandays = 0;
					$apptotalmandaycost = 0;

					$applicationunitmanday = ApplicationUnitManday::find()->where(['app_id'=> $model->id])->all();
					if(count($applicationunitmanday)>0){
						foreach($applicationunitmanday as $unitmandaycal){
							$appmandays += $unitmandaycal->manday;
							$appdiscountmandays += $unitmandaycal->discount_manday;
							$appfinalmandays += $unitmandaycal->final_manday;
							$apptotalmandaycost += $unitmandaycal->unit_manday_cost;
						}
					}

					$applicationmanday = ApplicationManday::find()->where(['app_id'=> $model->id])->one();
					if($applicationmanday===null)
					{					
						$applicationmanday=new ApplicationManday();
					}	
					$applicationmanday->app_id=$model->id;
					$applicationmanday->manday=$appmandays;
					$applicationmanday->discount_manday=$appdiscountmandays;
					$applicationmanday->final_manday=$appfinalmandays;
					$applicationmanday->total_manday_cost=$apptotalmandaycost;
					$applicationmanday->save();					
				}
			}
			
			
			
			/*********** Application Manday Insertion code ends here  ******************/
			
			$applicationMandayDiscount=array();
			$appunits = ApplicationUnit::find()->where(['app_id' => $data['id']])->all();
			if(is_array($appunits) && count($appunits)>0)
			{
				foreach($appunits as $units)
				{
					$mandayDiscount=array();
					$unitManday=array();
					$unitManday['name']=$units->name;
					$unitManday['id']=$units->id;
					$unitmanday = $units->unitmanday;
					if($unitmanday!==null)
					{
						$unitManday['unit_manday_id']=$unitmanday->id;
						$unitManday['no_of_workers_from']=$unitmanday->no_of_workers_from;
						$unitManday['no_of_workers_to']=$unitmanday->no_of_workers_to;
						$unitManday['manday']=$unitmanday->manday;
						//$unitManday['total_discount']=$unitmanday->total_discount;
						//$unitManday['eligible_discount']=$unitmanday->eligible_discount;
						//$unitManday['maximum_discount']=$unitmanday->maximum_discount;
						$unitManday['discount_manday']=$unitmanday->discount_manday;
						$unitManday['final_manday']=$unitmanday->final_manday;
						$unitManday['adjusted_manday']= $offermodel!==null?$unitmanday->adjusted_manday:$unitmanday->final_manday;
						$unitManday['translator_required']=$unitmanday->translator_required;
						$unitManday['adjusted_manday_comment']=$unitmanday->adjusted_manday_comment;
						
						$unitManday['manday_cost']=$unitmanday->manday_cost;
						$unitManday['unit_manday_cost']=$unitmanday->unit_manday_cost;
						
						$unitManday['total_manday_cost']=$unitmanday->unit_manday_cost;
											
						//$unitmandaydiscount = $unitmanday->unitmandaydiscount;
						$unitmandaydiscount = array();
						if(is_array($unitmandaydiscount) && count($unitmandaydiscount)>0)
						{
							foreach($unitmandaydiscount as $mandaydiscount)
							{	
								/*
								if($mandaydiscount->same_standard_certified==1)
								{
									$same_standard_certified_manday_discount=array();
								}elseif($mandaydiscount->same_standard_certified==2){
									$same_standard_certified_discount=array();									
								}else{
									$mandayDiscount=array();
									$mandayDiscount['manday_discount_id']=$mandaydiscount->id;
									$mandayDiscount['standard']=$mandaydiscount->standard->name;
									$mandayDiscount['standard_id']=$mandaydiscount->standard_id;
									$mandayDiscount['discount']=$mandaydiscount->discount;
									$mandayDiscount['status']=$mandaydiscount->status;
									$unitManday['manday_discount'][]=$mandayDiscount;
								}
								*/	
								
								$mandayDiscount=array();
								$mandayDiscount['manday_discount_id']=$mandaydiscount->id;
								$mandayDiscount['standard']=$mandaydiscount->standard->name;
								$mandayDiscount['standard_id']=$mandaydiscount->standard_id;
								$mandayDiscount['discount']=$mandaydiscount->discount;
								$mandayDiscount['status']=$mandaydiscount->status;
								$mandayDiscount['same_standard_certified']=$mandaydiscount->same_standard_certified;
								$unitManday['manday_discount'][]=$mandayDiscount;
							}	
						}else{
							$unitManday['manday_discount']=$mandayDiscount;
						}						
					}else{
						$unitManday['manday_discount']=$mandayDiscount;
					}
					
					$applicationMandayDiscount[]=$unitManday;
				}				
			}
						
					
			$model = Application::find()->where(['id' => $data['id']])->one();
			$appstds=ApplicationStandard::find()->where(['app_id' => $model->id])->asArray()->all();
			$certificatediscount=0;
			
			$appunits = ApplicationUnit::find()->where(['app_id' => $data['id']])->all();
			//print_r($appunits);
			$unitcount=count($appunits);
			$inspectdaysArr=array();
			$appUnitDiscountArr=array();
			$appUnitMandayCostArr=array();
			$numofemp=0;
			if(is_array($model->applicationunit) && count($model->applicationunit)>0)
			{
				foreach($model->applicationunit as $units)
				{
					$appstds = [];
					foreach($units->unitappstandard as $unitstandards){
						$appstds[] = array('id'=>$unitstandards->id,'standard_id'=>$unitstandards->standard_id);
					}
					//print_r($appstds); die;
					$certificatediscount = $this->calculateDiscount($appstds,$units);
					$appUnitDiscountArr[$units->id] = $certificatediscount;
					
					$appUnitMandayCostArr[$units->id] = $units->mandaycost->man_day_cost;
					
					$inspectmodel=new StandardInspectionTime();
					$appUnitArr[$units->id][] =$units->name;
					$inspectdaysArr[$units->id]=$inspectmodel->find()->select('inspector_days')->where(['>=', 'no_of_workers_to', $units->no_of_employees])->andWhere(['<=', 'no_of_workers_from', $units->no_of_employees])->asArray()->one();
				}
			}elseif($model->applicationunit!==null){
				$appstds = [];
				foreach($model->applicationunit->unitappstandard as $unitstandards){
					$appstds[] = array('id'=>$unitstandards->id,'standard_id'=>$unitstandards->standard_id);
				}

				$certificatediscount = $this->calculateDiscount($appstds,$model->applicationunit);
				$appUnitDiscountArr[$units->id] = $certificatediscount;
				
				$appUnitMandayCostArr[$units->id] = $units->mandaycost->man_day_cost;

				$inspectmodel=new StandardInspectionTime();
				$appUnitArr[$units->id][] =$units->name;
				$inspectdaysArr[$units->id]=$inspectmodel->find()->select('inspector_days')->where(['>=', 'no_of_workers_to', $model->applicationunit->no_of_employees])->andWhere(['<=', 'no_of_workers_from', $model->applicationunit->no_of_employees])->asArray()->one();
			}
			
			//print_r($appUnitArr);
			//print_r($appUnitMandayCostArr);
			//die();
			
			/*			
			$mandaycostamt=0;
			$totmandays=0;
			$mandaysdiscount=0;
			foreach($inspectdaysArr as $unitid => $val)
			{
				$unitManday=0;
				$unitManday=$val['inspector_days'];
												
				$certificatediscount = 0;				
				$certificatediscount = $appUnitDiscountArr[$unitid];
				
				$unitDiscountManday=($certificatediscount * $val['inspector_days'])/100;
				
				$mandaysdiscount+=$unitDiscountManday;
				$totmandays+=$val['inspector_days'];
				
				$unitFinalManday=0;
				$unitFinalManday=$unitManday-$unitDiscountManday;
				
				$mandaycostamt+=$unitFinalManday*$appUnitMandayCostArr[$unitid];
			}
			$mandays=$totmandays-$mandaysdiscount;
			*/

			
			if ($model !== null)
			{
				$applicationmanday = ApplicationManday::find()->where(['app_id'=> $model->id])->one();

				$resultarr=array();
				$resultarr["id"]=$model->id;
				$resultarr["code"]=$model->code;
				$resultarr["company_file"]=$model->company_file;
				//$resultarr['created_at']=date('M d,Y h:i A',$model->created_at);
				$resultarr['created_at']=date($date_format,$model->created_at);
				$resultarr["company_name"]=$model->companyname;
				$resultarr["address"]=$model->address;
				$resultarr["zipcode"]=$model->zipcode;
				$resultarr["city"]=$model->city;
				$resultarr["salutation"]=($model->salutation!="")?$model->salutation:"";
				$resultarr["salutation_name"]=($model->salutation!="")?$model->arrSalutation[$model->salutation]:"";
				
				$resultarr["title"]=($model->title!="")?$model->title:"";
				$resultarr["first_name"]=($model->firstname!="")?$model->firstname:"";
				$resultarr["last_name"]=($model->lastname!="")?$model->lastname:"";
				$resultarr["job_title"]=($model->jobtitle!="")?$model->jobtitle:"";
				$resultarr["telephone"]=($model->telephone!="")?$model->telephone:"";
				$resultarr["email_address"]=($model->emailaddress!="")?$model->emailaddress:"";
							
				$resultarr["state_id_name"]=($model->statename!="")?$model->statename:"";
				$resultarr["country_id_name"]=($model->countryname!="")?$model->countryname:"";
				$resultarr["state_id"]=($model->applicationaddress!==null && $model->applicationaddress->state_id!="")?$model->applicationaddress->state_id:"";
				$resultarr["country_id"]=($model->applicationaddress!==null && $model->applicationaddress->country_id!="")?$model->applicationaddress->country_id:"";
				$resultarr["created_by"]=($model->created_by!="")?$model->username->first_name.' '.$model->username->last_name:"";
				$resultarr["certification_status"]=$model->certification_status;
				$resultarr["preferred_partner_id"]=$model->preferred_partner_id;
				$resultarr["preferred_partner_id_name"]=($model->preferredpartner?$model->preferredpartner->name:'');
				
				$resultarr["app_status"]=$model->status;
				$resultarr["status"]=$model->arrStatus[$model->status];
				$resultarr["current_date"]=date("d/m/Y");
				
				//$resultarr["mandays"]=$mandays;
				//$resultarr["totalmandays"]=$totmandays;
				$resultarr["totalmandays"]=$applicationmanday->manday;
				$resultarr["mandays"]=$applicationmanday->final_manday;
				
				
				// $maxid = Offer::find()->max('id');
				// if(!empty($maxid)) 
				// {
				// 	$maxid = $maxid+1;
					
				// 	$offercode="PC-".$maxid."-".date("m")."/".date("y");
				// }
				// else
				// {
				// 	$offercode="PC-1-".date("m")."/".date("y");
				// }

				$ospid = $model->franchise_id;
				if($ospid !== null && $offermodel===null)
				{
					$offerCount = 0;
					$connection = Yii::$app->getDb();

					$command = $connection->createCommand("SELECT COUNT(offer.id) AS offer_count FROM `tbl_offer` AS offer 
					INNER JOIN `tbl_application` AS app ON app.id = offer.app_id AND app.franchise_id='$ospid' GROUP BY app.franchise_id");
					$result = $command->queryOne();
					if($result  !== false)
					{
						$offerCount = $result['offer_count'];
					}
					$maxid = $offerCount+1;
					if(strlen($maxid)=='1')
					{
						$maxid = "0".$maxid;
					}
					$offercode = "PC-".$model->franchise->usercompanyinfo->osp_number."-".$maxid."/".date("Y");
				
					$resultarr["offercode"]=$offercode;
				}else{
					$resultarr["offercode"]=$offermodel->offer_code;
				}

				
				
				$appstdarr=[];
				$appstdcodearr=[];
				$arrstandardids=[];
				$appStandard=$model->applicationstandard;
				if(count($appStandard)>0)
				{
					foreach($appStandard as $std)
					{
						$appstdarr[]=($std->standard?$std->standard->name:'');	
						$appstdcodearr[]=($std->standard?$std->standard->code:'');
						$arrstandardids[]=$std->standard_id;
					}
				}
				$resultarr["standards"]=$appstdcodearr;
				$resultarr["standardscode"]=$appstdcodearr;
				$resultarr["standard_ids"]=$arrstandardids;
				
				
				//$offermodel = Offer::find()->where(['app_id' => $data['id']])->one();
								
				//$arrUnitStandardforSubsequentFee=array();
				$unitarr=array();
				$appUnit=$model->applicationunit;
				if(count($appUnit)>0)
				{
					$subContractorCount=0;
					$OthersFee=0;
					foreach($appUnit as $unit)
					{
						//$statelist = State::find()->alias( 't' )->select(['id','name'])->where(['t.country_id'=>$unit->country_id])->asArray()->all();
						$unitarr = $unit->toArray();
						$unitarr["unit_type_name"]=isset($unit->unit_type_list[$unit->unit_type])?$unit->unit_type_list[$unit->unit_type]:'';
						$unitarr["state_id_name"]=($unit->state_id!="")?$unit->state->name:"";
						$unitarr["country_id_name"]=($unit->country_id!="")?$unit->country->name:"";
						
						
						$unitnamedetailsarr[$unit->id] = $unit->name;
						$unitdetailsarr[]=$unitarr;


						// ------------------ Unit Standard Code Start Here -------------------
						if($offermodel===null)
						{	
							$unitstandards=$unit->standard;
							if(count($unitstandards)>0)
							{
								foreach($unitstandards as $unitstandard)
								{
									$unitStandardName = $unitstandard->standard->code;
									$unitStdID = $unitstandard->standard->id;
									
									$licenseFee = $unitstandard->standardlicensefee->license_fee;
									$subsequentLicenseFee = $unitstandard->standardlicensefee->subsequent_license_fee;
									if (in_array($unitStdID, $arrUnitStandardforSubsequentFee) && $subsequentLicenseFee>=0 && strtolower($unitStandardName)!='gots')
									{
										$licenseFee = $subsequentLicenseFee;									
									}
									
									if($unit->unit_type==3 && strtolower($unitStandardName)!='gots')
									{
										$OthersFee=$OthersFee+$licenseFee;
										$subContractorCount++;
									}else{
										$arrOtherExpenses=array('entry_type'=>0,'type'=>1,'expense_name'=>$unitStandardName.' License Fee','expense_description'=>$unitStandardName.' License fee for '.$unit->name,'expense_amount'=>number_format($licenseFee, 2, '.', ''));
										$resultarr["other_expenses"][]=$arrOtherExpenses;										
									}
									$arrUnitStandardforSubsequentFee[]=$unitStdID;
								}	
							}
						}		
						// ------------------ Unit Standard Code End Here -------------------

						
					}
					
					if($subContractorCount>0 && $OthersFee>0)
					{
						$arrOtherExpenses=array('entry_type'=>1,'type'=>2,'expense_name'=>'Other Fees','expense_description'=>'Handling fees','expense_amount'=>number_format($OthersFee, 2, '.', ''));
						$resultarr["other_expenses"][]=$arrOtherExpenses;
					}
					
					$resultarr["units"]=$unitdetailsarr;
				}	

				
				$appmdc = Mandaycost::find()->where(['country_id' => $model->applicationaddress->country_id])->one();
				
				$mdctaxArr=array();
				$mdctaxpercentArr=array();
				$appmandaycost=0;
				$adminfee=0;
				if($appmdc!==null)
				{
					$appmandaycost=$appmdc->man_day_cost;					
					$adminfee=$appmdc->admin_fee;
					if(is_array($appmdc->mandaycosttax) && count($appmdc->mandaycosttax)>0)
					{
						foreach($appmdc->mandaycosttax as $val)
						{
							$mdctaxArr[]=$val->tax_name;
							$mdctaxpercentArr[]=$val->tax_percentage;						
						}
					}elseif($appmdc->mandaycosttax!==null){
						$mdctaxArr[]=$appmdc->mandaycosttax? $appmdc->mandaycosttax->tax_name : '';
						$mdctaxpercentArr[]=$appmdc->mandaycosttax?$appmdc->mandaycosttax->tax_percentage:'0';	
					}
				}
				$resultarr["taxname"]=implode(', ',$mdctaxArr);
				
				$mdctaxpercentArr=array_sum($mdctaxpercentArr);
								
				//$resultarr["offer_currency_code"]=$appmdc->currency_code;
				$resultarr["offer_currency_code"]='USD';
				$resultarr["tax_percentage"]=$mdctaxpercentArr;				
				
				$conversion_required_status=0;
				
				$conversion_rate='';
				$currency='';
				$conversion_currency_code='';
				
				$offerStatus=0;
				if($offermodel!==null)
				{
					$offerStatus=$offermodel->status;
										
					$offerlist = $offermodel->offerlist;
					if($offerlist!==null)
					{
						$offerotherexpense = $offerlist->offerotherexpenses;
						$resultarr["discount"]=$offerlist->discount;
						
						$conversion_required_status = $offerlist->conversion_required_status;
						
						$conversion_rate = $offerlist->conversion_rate;
						$currency = $offerlist->currency;
						$conversion_currency_code = $offerlist->conversion_currency_code;
						
						$resultarr["offer_currency_code"]=$currency;
						
						if(count($offerotherexpense)>0)
						{
							$arrOE=array();
							foreach($offerotherexpense as $otherE)
							{
								$arrOE=array('type'=>$otherE->type,'entry_type'=>$otherE->entry_type,'expense_name'=>$otherE->activity,'expense_description'=>$otherE->description,'expense_amount'=>number_format($otherE->amount, 2, '.', ''));
								$resultarr["other_expenses"][]=$arrOE;
							}
							
						}
						
						$certificationfee = $offerlist->offercertificationfee;
						if(count($certificationfee)>0)
						{
							$arrOE=array();
							foreach($certificationfee as $certF)
							{
								$arrFees=array('fee_name'=>$certF->activity,'fee_description'=>$certF->description,'amount'=>number_format((float)$certF->amount, 2, '.', ''));
								$resultarr["fees"][]=$arrFees;
							}
							
						}					
					}
				}else{
					//$arrFees=array('fee_name'=>'Company Audit Fee','fee_description'=>$mandays.' Manday','amount'=>number_format((float)$mandaycostamt, 2, '.', ''));
					$arrFees=array('fee_name'=>'Company Audit Fee','fee_description'=>number_format((float)$applicationmanday->final_manday, 2, '.', '').' Manday','amount'=>number_format((float)$applicationmanday->total_manday_cost, 2, '.', ''));
					$resultarr["fees"][]=$arrFees;
					
					$arrFees=array('fee_name'=>'Admin Fee','fee_description'=>'-','amount'=>number_format((float)$adminfee, 2, '.', ''));
					$resultarr["fees"][]=$arrFees;
					
					/*
					$offerStandard = implode(', ',$resultarr["standardscode"]);
					$arrOtherExpenses=array('expense_name'=>$offerStandard.' License Fee','expense_description'=>$offerStandard.' License fee for '.count($appUnitArr).' units','expense_amount'=>number_format(600, 2, '.', ''));
					$resultarr["other_expenses"][]=$arrOtherExpenses;	
					*/					
				}
				$resultarr['conversion_required_status'] = $conversion_required_status;
				
				$resultarr['conversion_rate'] = $conversion_rate;
				$resultarr['currency'] = $currency;
				$resultarr['conversion_currency_code'] = $conversion_currency_code;
				
				$resultarr["offer_status"]=$offerStatus;
				
				//$resultarr["appunitmanday"]=$arrAppunitmanday;
				//$resultarr["appunitmandaydiscount"][]=$arrAppunitmandaydiscount;
				//$resultarr["licensefee"]=$licenseFeeArr;
				
				$resultarr["appunitmanday"]=$applicationMandayDiscount;
				$StatusOffer = new Offer();
				$resultarr['offerenumstatus']=$StatusOffer->enumStatus;
				if(!isset($resultarr["other_expenses"])){
					$resultarr["other_expenses"] = [];
				}
				return $resultarr;			
			}
		}
	}
	//(offerdata.offer.offer_status==offerdata?.offerenumstatus['customer_approved']) && (userdetails.resource_access==1 || (userType==1 && userdetails.rules.includes('offer_approvals')) )
	private function canApproveReject($offer_id){
		$offermodel = Offer::find()->where(['id'=>$offer_id])->one();

		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];

		$canApprove = 0;
		if($offermodel !== null)
		{
			
			if($offermodel->status == $offermodel->enumStatus['customer_approved'] 
				&& 
				(Yii::$app->userrole->hasRights(['offer_approvals']) || Yii::$app->userrole->isAdmin())
			){
				$canApprove = 1;
			}
		}
		return $canApprove;
	}


	//(offerdata.offer.offer_status==offerdata?.offerenumstatus['waiting-for-send-to-customer'] || offerdata.offer.offer_status==offerdata?.offerenumstatus['customer_rejected']) && (userdetails.resource_access==1 || (userType==1 && userdetails.rules.includes('generate_offer')) )
	private function canSendBackOssOrCustomer($offer_id){
		$offermodel = Offer::find()->where(['id'=>$offer_id])->one();

		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];

		$canSend = 0;
		if($offermodel !== null)
		{
			
			if(($offermodel->status == $offermodel->enumStatus['waiting-for-send-to-customer'] || 
			$offermodel->status == $offermodel->enumStatus['customer_rejected']) 
			&& 
			(Yii::$app->userrole->hasRightsWithFranchise(['generate_offer'],$offermodel->application->franchise_id)) 
			
			){
				$canSend = 1;
			}
		}
		return $canSend;
	}

	private function canSendtoHQ($offer_id){
		$userData = Yii::$app->userdata->getData();
		
		$offermodel = Offer::find()->where(['id'=>$offer_id])->one();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];

		$canSend = 0;
		if($offermodel !== null)
		{
			
			if(($offermodel->status == $offermodel->enumStatus['waiting-for-oss-approval'] || 
			$offermodel->status == $offermodel->enumStatus['re-initiated-to-oss']) 
			&& 
			(Yii::$app->userrole->hasOssRights($offermodel->application->franchise_id) || 
			Yii::$app->userrole->hasRightsWithFranchise(['oss_quotation_review'],$offermodel->application->franchise_id)
			) 
			
			){
				$canSend = 1;
			}
		}
		return $canSend;
		//(offerdata.offer.offer_status==offerdata?.offerenumstatus['in-progress'] || offerdata.offer.offer_status==offerdata?.offerenumstatus['waiting-for-oss-approval'] || offerdata.offer.offer_status==offerdata?.offerenumstatus['waiting-for-send-to-customer'] || offerdata.offer.offer_status==offerdata?.offerenumstatus['customer_rejected']) && (userdetails.resource_access==1 || (userType==3 && offerdata.offer.offer_status==offerdata?.offerenumstatus['waiting-for-oss-approval']) || (userType==1 && userdetails.rules.includes('generate_offer')) )
	}
	
	private function canEditOffer($offer_id='',$app_id=''){
		$userData = Yii::$app->userdata->getData();
		$canEdit = 0;
		$offermodel = Offer::find()->where(['t.id'=>$offer_id])->alias('t');
		$offermodel = $offermodel->innerJoinWith(['application as app']);
		$offermodel = $offermodel->one();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];

		
		
		if($offermodel !== null)
		{	 
			if((
				$offermodel->status == $offermodel->enumStatus['open'] 
				|| $offermodel->status == $offermodel->enumStatus['in-progress']
				|| $offermodel->status == $offermodel->enumStatus['waiting-for-send-to-customer']
				|| $offermodel->status == $offermodel->enumStatus['customer_rejected']
				) && (Yii::$app->userrole->hasRightsWithFranchise(['generate_offer'],$offermodel->application->franchise_id) )
			){
				$canEdit = 1;
			}
		
		
			if($offermodel->status == $offermodel->enumStatus['waiting-for-oss-approval'] || $offermodel->status == $offermodel->enumStatus['re-initiated-to-oss']){
				if(Yii::$app->userrole->hasOssRights($offermodel->application->franchise_id) ){
					$canEdit = 1;
				}else if(Yii::$app->userrole->hasRightsWithFranchise(['oss_quotation_review'],$offermodel->application->franchise_id)){
					$canEdit = 1;
				}				
			}
		}else{
			if($app_id!=''){
				$Applicationmodel = Application::find()->where(['id'=>$app_id])->one();
				if(Yii::$app->userrole->hasRights(['generate_offer']) ){
					if($is_headquarters == 1){
						$canEdit = 1;
					}else{
						if($Applicationmodel->franchise_id == $franchiseid){
							$canEdit = 1;
						}
					}
					
					
				}
			}
		}
				 
		
		return $canEdit;
		//(offerdata.offer.offer_status==offerdata?.offerenumstatus['in-progress'] || offerdata.offer.offer_status==offerdata?.offerenumstatus['waiting-for-oss-approval'] || offerdata.offer.offer_status==offerdata?.offerenumstatus['waiting-for-send-to-customer'] || offerdata.offer.offer_status==offerdata?.offerenumstatus['customer_rejected']) && (userdetails.resource_access==1 || (userType==3 && offerdata.offer.offer_status==offerdata?.offerenumstatus['waiting-for-oss-approval']) || (userType==1 && userdetails.rules.includes('generate_offer')) )
	}

	public function actionChangeStatus()
    {

		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if(Yii::$app->request->post())
		{
			$datapost = Yii::$app->request->post();
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$is_headquarters =$userData['is_headquarters'];
			$resource_access=$userData['resource_access'];
			$franchiseid=$userData['franchiseid'];


			$data=Yii::$app->request->post();
			if(isset($datapost['formvalues'])){
				$data =json_decode($datapost['formvalues'],true);
			}
			
			$target_dir = Yii::$app->params['customer_approval_files'];

			$mail_notification_code = '';
			$files ='';

			$offermodel = Offer::find()->where(['id'=>$data['offer_id']])->one();
			$mailmsg = '';
			if($offermodel !== null)
			{
				if($data['status']==$offermodel->enumStatus['finalized']){
					$data['status']=$offermodel->enumStatus['waiting_for_audit_report'];
				}


				$canupdate =0;
				if($data['status']==$offermodel->enumStatus['waiting-for-oss-approval'])
				{
					if(($offermodel->status == $offermodel->enumStatus['in-progress'] || 
					$offermodel->status == $offermodel->enumStatus['customer_rejected'] 
					) && Yii::$app->userrole->hasRights(array('generate_offer'))
					){
						$canupdate =1;
					}else{
						return false;
					}
					//(offerdata.offer.offer_status==offerdata?.offerenumstatus['in-progress'] || offerdata.offer.offer_status==offerdata?.offerenumstatus['customer_rejected']) && (userdetails.resource_access==1 || (userType==1 && userdetails.rules.includes('generate_offer')) )

				}else if($data['status']==$offermodel->enumStatus['waiting-for-send-to-customer']){
					if(!$this->canSendtoHQ($offermodel->id)){
						return false;
					}
				}else if($data['status']==$offermodel->enumStatus['re-initiated-to-oss'] || 
					$data['status']==$offermodel->enumStatus['waiting-for-customer-approval']){
					//'re-initiated-to-oss'=>"4",'waiting-for-customer-approval'
					if(!$this->canSendBackOssOrCustomer($offermodel->id)){
						return false;
					}
				}else if($data['status']==$offermodel->enumStatus['waiting_for_audit_report'] || 
					$data['status']==$offermodel->enumStatus['rejected']){
					if(!$this->canApproveReject($offermodel->id)){
						return false;
					}
				}
				
				

				$mailsettingsmodel=Settings::find()->where(['id'=>1])->one();
				
				$offermodel->status=$data['status']== $offermodel->enumStatus['rejected']?$offermodel->enumStatus['waiting-for-customer-approval']:$data['status'];
				$userData = Yii::$app->userdata->getData();
				$offermodel->updated_by=$userData['userid'];
				$offermodel->save();


				if($data['status']==$offermodel->enumStatus['re-initiated-to-oss']){
					$offercomment = new OfferReinitiateComment;
					$offercomment->created_by = $userid;
					$offercomment->created_at = time();
					$offercomment->comment = $data['comment'];
					$offercomment->offer_id = $data['offer_id'];
					$offercomment->save();

					$franchiseid = $offermodel->application->franchise_id;
					if($franchiseid !== null)
					{
						$franchise = UserCompanyInfo::find()->select('company_name,company_email')->where(['user_id' => $franchiseid])->one();

						$FranchiseMailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'mail_oss_quotation_generation_reinitiated'])->one();

						$company_name = $offermodel->application->currentaddress?$offermodel->application->currentaddress->company_name:"";
						if($FranchiseMailContent !== null && $franchise!== null)
						{
							$mailmsg=str_replace('{COMPANYNAME}', $company_name, $FranchiseMailContent['message'] );

							$MailLookupModel = new MailLookup();
							$MailLookupModel->to=$franchise['company_email'];							
							$MailLookupModel->subject=$FranchiseMailContent['subject'];
							$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
							$MailLookupModel->attachment='';
							$MailLookupModel->mail_notification_id='';
							$MailLookupModel->mail_notification_code='';
							$Mailres=$MailLookupModel->sendMail();
						}
					}
				}


				if($data['status']==$offermodel->enumStatus['waiting-for-oss-approval'])
				{

					$franchiseid = $offermodel->application->franchise_id;
					if($franchiseid !== null)
					{
						$franchise = UserCompanyInfo::find()->select('company_name,company_email')->where(['user_id' => $franchiseid])->one();

						$FranchiseMailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'mail_oss_quotation_generation'])->one();

						$company_name = $offermodel->application->currentaddress?$offermodel->application->currentaddress->company_name:"";

						if($FranchiseMailContent !== null && $franchise!== null)
						{
							$mailmsg=str_replace('{COMPANYNAME}', $company_name, $FranchiseMailContent['message'] );

							$MailLookupModel = new MailLookup();
							$MailLookupModel->to=$franchise['company_email'];							
							$MailLookupModel->subject=$FranchiseMailContent['subject'];
							$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
							$MailLookupModel->attachment='';
							$MailLookupModel->mail_notification_id='';
							$MailLookupModel->mail_notification_code='';
							$Mailres=$MailLookupModel->sendMail();
						}
					}
					
				}

				if($data['status']==$offermodel->enumStatus['waiting-for-send-to-customer']){
					

					$FranchiseMailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'mail_hq_quotation_generation_from_oss'])->one();

					$company_name = $offermodel->application->currentaddress?$offermodel->application->currentaddress->company_name:"";

					$to_email = $offermodel->username?$offermodel->username->email:"";
					$username = $offermodel->username?$offermodel->username->first_name." ".$offermodel->username->last_name:"";
					if($FranchiseMailContent !== null && $to_email!="")
					{
						$mailmsg=str_replace('{USERNAME}', $username, $FranchiseMailContent['message'] );
						$mailmsg=str_replace('{COMPANYNAME}', $company_name, $mailmsg );

						$MailLookupModel = new MailLookup();
						$MailLookupModel->to=$to_email;						
						$MailLookupModel->subject=$FranchiseMailContent['subject'];
						$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
						$MailLookupModel->attachment='';
						$MailLookupModel->mail_notification_id='';
						$MailLookupModel->mail_notification_code='';
						$Mailres=$MailLookupModel->sendMail();
					}
				}


				//if($data['status']==$offermodel->enumStatus['finalized']){
				if($data['status']==$offermodel->enumStatus['waiting_for_audit_report']){		
					//$appmodel=Application::find()->where(['id' => $offermodel->app_id])->one();
					//$appmodel->overall_status = $appmodel->arrEnumOverallStatus['offer_completed'];
					//$appmodel->save();
					//$appmodel= new Application();
					//Yii::$app->globalfuns->updateApplicationOverallStatus($offermodel->app_id, $appmodel->arrEnumOverallStatus['quotation_approved']);

					$offercomment = new OfferComment;
					$offercomment->created_by = $userid;
					$offercomment->created_at = time();

					$offercomment->comment = $data['comment'];
					$offercomment->status = 1;
					$offercomment->offer_id = $data['offer_id'];
					$offercomment->save();
					
					
					
					//Store Subtopic
					$app_id = $offermodel->app_id;
					$ApplicationUnit = ApplicationUnit::find()->where(['app_id'=>$app_id])->all();
					if(count($ApplicationUnit)>0){
						foreach($ApplicationUnit as $appunit){
							$unit_id = $appunit->id;
							$result = Yii::$app->globalfuns->getSubtopic($unit_id,'', '', 1);
							if(count($result)>0){
								foreach($result as $subdata){
									//$subtopicArr[] = $subdata['id'];
									$ApplicationUnitSubtopicExists = ApplicationUnitSubtopic::find()->where(['app_id'=>$app_id, 'unit_id'=>$unit_id, 'subtopic_id'=>$subdata['id'] ])->one();
									if($ApplicationUnitSubtopicExists === null){
										$ApplicationUnitSubtopic = new ApplicationUnitSubtopic;
										$ApplicationUnitSubtopic->app_id = $app_id;
										$ApplicationUnitSubtopic->unit_id = $unit_id;
										$ApplicationUnitSubtopic->subtopic_id = $subdata['id'];
										$ApplicationUnitSubtopic->save();
									}
										
								}
							}

						}
					}
				
										// 
										$appmodel = new Application();
										$Audit = new Audit();
										Yii::$app->globalfuns->updateApplicationOverallStatus($offermodel->app_id, $appmodel->arrEnumOverallStatus['quotation_approved']);
										$audit_status = 0;
										$direct_to_certificate = 0;
										$applicationsubtopicCheck = ApplicationUnitSubtopic::find()->where(['app_id'=> $offermodel->app_id])->one();
										$applicationdetails = Application::find()->where(['id'=>$offermodel->app_id])->one();
										if($offermodel->manday == '0.00' && $applicationdetails!==null && $applicationdetails->audit_type == $applicationdetails->arrEnumAuditType['unit_addition']){
					
											$offermodel->status = $offermodel->enumStatus['finalized'];
											$offermodel->save();
											
					
											$audit_status = $Audit->arrEnumStatus['finalized_without_audit'];
											$direct_to_certificate = 1;
					
					
					
											$Audit->app_id = $offermodel->app_id;
											$Audit->offer_id = $offermodel->id;
											$Audit->status = $audit_status;
											$Audit->followup_status = 0;
											$Audit->save();
					
					
					
					
					
											Yii::$app->globalfuns->updateApplicationOverallStatus($offermodel->app_id, $appmodel->arrEnumOverallStatus['audit_finalized']);
											$parent_app_id = '';
											//$applicationdetails = Application::find()->where(['id'=>$offermodel->app_id])->one();
											if($applicationdetails !==null && $applicationdetails->audit_type !=$applicationdetails->arrEnumAuditType['renewal']){
												$parent_app_id = $applicationdetails->parent_app_id;
												if($parent_app_id !='' && $parent_app_id>0){
												
												}else{
													$parent_app_id = $applicationdetails->id;
												}
											}
											if($applicationdetails !==null && $applicationdetails->audit_type == $applicationdetails->arrEnumAuditType['renewal']){
												$parent_app_id = $applicationdetails->id;
											}
											$StatusModelCertificate = new Certificate();
											if(count($applicationdetails->applicationstandard)>0){
												
												foreach($applicationdetails->applicationstandard as $appstandard){
						
													$standardID = $appstandard->standard_id;
													
													$Certificate = new Certificate();
													if( $applicationdetails->audit_type == $applicationdetails->arrEnumAuditType['normal']){
														$capp_id = $applicationdetails->id;
														$cstandard_id = $standardID;
														$ApplicationCertifiedByOtherCB = ApplicationCertifiedByOtherCB::find()->where(['app_id'=>$capp_id, 'standard_id'=>$cstandard_id]);
														$ApplicationCertifiedByOtherCB = $ApplicationCertifiedByOtherCB->andWhere(' validity_date >= "'.date('Y-m-d').'" ');
														$ApplicationCertifiedByOtherCB = $ApplicationCertifiedByOtherCB->one();
														if($ApplicationCertifiedByOtherCB !== null){
															$Certificate->status = $StatusModelCertificate->arrEnumStatus['certified_by_other_cb'];
														}else{
															$Certificate->status = $StatusModelCertificate->arrEnumStatus['open'];	
														}
													}else{
														$Certificate->status = $StatusModelCertificate->arrEnumStatus['open'];
													}
													
													$Certificate->audit_id = $Audit->id;
													$Certificate->parent_app_id = $parent_app_id;
													$Certificate->standard_id = $standardID;
													$Certificate->product_addition_id = '';
													$Certificate->certificate_status = $StatusModelCertificate->arrEnumCertificateStatus['invalid'];//1;
													$Certificate->type = $applicationdetails->audit_type;
													$Certificate->save();
												}
											}
										}


					









					
					
					//----Generating offer number for the user - starts here-----//
					
					$userid = $offermodel->application->customer_id;
					$usermodal = User::find()->where(['id'=>$userid,'user_type'=>2])->one(); 
					if($usermodal!==null)
					{
						if($usermodal->customer_number=='' || $usermodal->customer_number<=0)
						{
							$maxid = User::find()->max('customer_number');
							if(!empty($maxid) && $maxid>=4000) 
							{
								$maxid = $maxid+1;
							}else{
								$maxid = 4000;
							}
							$usermodal->customer_number = $maxid;
							$usermodal->save();
						}
					}
					
					
					// ------------- Insert Invoice details Code Start Here ------------------
					$offermodel->generateInvoice($offermodel,'1'); //Auto Generate Invoice for Client
					$offermodel->generateInvoice($offermodel,'2'); //Auto Generate Invoice for OSS
					// ------------- Insert Invoice details Code End Here -------------
					
					/*
					$maxid = User::find()->max('customer_number');
					//$maxid = 3;
					if(!empty($maxid)) 
					{
						$maxid = $maxid+1;
					}
					else
					{
						$maxid = "4000";
					}
					
					$userid = $offermodel->application->customer_id;
					$usermodal = User::find()->where(['id'=>$userid])->one(); 
					$usermodal->customer_number = $maxid;
					$usermodal->save();
					*/
					//----Generating offer number for the user - ends here-----//
					

				}

				if($data['status']==$offermodel->enumStatus['waiting-for-customer-approval'])
				{
					//When send to customers

					$mailContent = MailNotifications::find()->select('code,subject,message')->where(['code' => 'offer_waiting_for_customer_approval'])->one();

					$appmodel = Application::find()->where(['id'=>$data['app_id']])->one();

					$mailmsg=str_replace('{USERNAME}', $appmodel->contactname, $mailContent['message'] );
					$toemail=$appmodel->emailaddress;

					$dataoffer = ['app_id'=>$offermodel->app_id,'offer_id'=>$offermodel->id];
					$html = $this->generateHtmlOffer($dataoffer);

					$fileName = Yii::$app->params['temp_files'].str_replace(" ","-",$appmodel->companyname).'_offer_'.date('Ymdhis').'.pdf';
					$mpdf = new \Mpdf\Mpdf();
					$mpdf->WriteHTML($html);
					$mpdf->Output($fileName,'F');
					$mail_notification_code = $mailContent->code;
					//$mpdf->Output('offer.pdf','F');
					
					$files = json_encode([$fileName]);
				}
				else if($data['status']==$offermodel->enumStatus['customer_approved'])
				{
					$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'offer_approved'])->one();
					$mailmsg = $mailContent['message'];
					$toemail=$mailsettingsmodel->to_email;

					$responsedata=array('status'=>1,'message'=>'Offer Approved Successfully');
					
					$OfferList = OfferList::find()->where(['offer_id' => $data['offer_id'],'is_latest'=>1])->one();
					if($OfferList!==null){
						if(isset($_FILES['quotation_file']['name']))
						{
							/*
							$filename = $_FILES['quotation_file']['name'];
							$target_file = $target_dir . basename($filename);
							$target_file = $target_dir . basename($filename);
							$actual_name = pathinfo($filename,PATHINFO_FILENAME);
							$original_name = $actual_name;
							$extension = pathinfo($filename, PATHINFO_EXTENSION);
							$i = 1;
							$name = $actual_name.".".$extension;
							while(file_exists($target_dir.$actual_name.".".$extension))
							{           
								$actual_name = (string)$original_name.$i;
								$name = $actual_name.".".$extension;
								$i++;
							}
							if (move_uploaded_file($_FILES['quotation_file']["tmp_name"], $target_dir .$actual_name.".".$extension)) {
								$OfferList->quotation_file=isset($name)?$name:"";
							}
							*/
							
							$tmp_name = $_FILES["quotation_file"]["tmp_name"];
							$name = $_FILES["quotation_file"]["name"];
							Yii::$app->globalfuns->removeFiles($OfferList->quotation_file,$target_dir);
							$OfferList->quotation_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);	
						}
						/*
						if(isset($_FILES['scheme_rules']['name']))
						{
							$filename = $_FILES['scheme_rules']['name'];
							$target_file = $target_dir . basename($filename);
							$target_file = $target_dir . basename($filename);
							$actual_name = pathinfo($filename,PATHINFO_FILENAME);
							$original_name = $actual_name;
							$extension = pathinfo($filename, PATHINFO_EXTENSION);
							$i = 1;
							$name = $actual_name.".".$extension;
							while(file_exists($target_dir.$actual_name.".".$extension))
							{           
								$actual_name = (string)$original_name.$i;
								$name = $actual_name.".".$extension;
								$i++;
							}
							if (move_uploaded_file($_FILES['scheme_rules']["tmp_name"], $target_dir .$actual_name.".".$extension)) {
								$OfferList->scheme_rules_file=isset($name)?$name:"";
							}
						}
						*/
						
						$OfferList->save();
						if(isset($_FILES['processor_file']['name']))
						{						
							$arrProcessorFiles = $_FILES['processor_file'];
							$arrProcessorFileName=$arrProcessorFiles['name'];
							if(is_array($arrProcessorFileName) && count($arrProcessorFileName)>0)
							{
								$offerlistProcessorModel = OfferListProcessor::find()->where(['offer_list_id' => $OfferList->id,'is_latest'=>1])->one();					
								if($offerlistProcessorModel !== null)
								{
									$offerlistProcessorModel->is_latest=2;
									$offerlistProcessorModel->save();
								}
								
								$OfferListProcessorModel = new OfferListProcessor();							
								$OfferListProcessorModel->offer_list_id = $OfferList->id;							
								$OfferListProcessorModel->created_by = $userData['userid'];
								$OfferListProcessorModel->created_at = time();
								if($OfferListProcessorModel->save())
								{
									foreach($arrProcessorFileName as $processorFileKey => $processorFileKeyVal)
									{
										$filename = $processorFileKeyVal;
										$tmp_name = $arrProcessorFiles["tmp_name"][$processorFileKey];
										
										$processor_file=Yii::$app->globalfuns->postFiles($filename,$tmp_name,$target_dir);
										
										$processorModelFile = new OfferListProcessorFile();	
										$processorModelFile->unit_id = $processorFileKey;
										$processorModelFile->offer_list_processor_id = $OfferListProcessorModel->id;											
										$processorModelFile->processor_file = $processor_file;				
										$processorModelFile->save();
										
										/*
										$target_file = $target_dir . basename($filename);
										$target_file = $target_dir . basename($filename);
										$actual_name = pathinfo($filename,PATHINFO_FILENAME);
										$original_name = $actual_name;
										$extension = pathinfo($filename, PATHINFO_EXTENSION);
										$i = 1;
										$name = $actual_name.".".$extension;
										while(file_exists($target_dir.$actual_name.".".$extension))
										{           
											$actual_name = (string)$original_name.$i;
											$name = $actual_name.".".$extension;
											$i++;
										}
										
										if (move_uploaded_file($tmp_name, $target_dir .$actual_name.".".$extension))
										{
											$processorModelFile = new OfferListProcessorFile();	
											$processorModelFile->unit_id = $processorFileKey;
											$processorModelFile->offer_list_processor_id = $OfferListProcessorModel->id;											
											$processorModelFile->processor_file = isset($name)?$name:"";				
											$processorModelFile->save();										
										}
										*/								
									}
								}	
							}
						}						
						
					}
					

				}
				else if($data['status']==$offermodel->enumStatus['customer_rejected'])
				{

					$offercomment = new OfferComment;
					$offercomment->comment = $data['comment'];
					$offercomment->status = $data['status'];
					$offercomment->offer_id = $data['offer_id'];
					$offercomment->save();

					//$appmodel=Application::find()->where(['id' => $offermodel->app_id])->one();
					//$appmodel->overall_status = $appmodel->arrEnumOverallStatus['offer_rejected'];
					//$appmodel->save();
					$model= new Application();
					Yii::$app->globalfuns->updateApplicationOverallStatus($offermodel->app_id, $model->arrEnumOverallStatus['quotation_rejected']);


					$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'offer_negotiated'])->one();
					$mailmsg = $mailContent['message'];
					$toemail=$mailsettingsmodel->to_email;


				}
				else if($data['status']==$offermodel->enumStatus['rejected'])
				{
					$offercomment = new OfferComment;
					$offercomment->comment = $data['comment'];
					$offercomment->created_by = $userid;
					$offercomment->created_at = time();
					$offercomment->status = 2;
					$offercomment->offer_id = $data['offer_id'];
					$offercomment->save();
					
					//$appmodel=Application::find()->where(['id' => $offermodel->app_id])->one();
					//$appmodel->overall_status = $appmodel->arrEnumOverallStatus['offer_rejected'];
					//$appmodel->save();
					$model= new Application();
					Yii::$app->globalfuns->updateApplicationOverallStatus($offermodel->app_id, $model->arrEnumOverallStatus['quotation_rejected']);
					// if($data['user_type']=='customer')
					// {
					// 	$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'Offer_rejected_customer'])->one();
					// 	$mailmsg = $mailContent['message'];
					// 	$toemail=$mailsettingsmodel->to_email;
						
					// }
					// else
					// {
						$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'Offer_rejected_admin'])->one();
						$toemail=$mailsettingsmodel->to_email;

						$appmodel = Application::find()->where(['id'=>$data['app_id']])->one();

						$mailmsg=str_replace('{USERNAME}', $appmodel->contactname, $mailContent['message'] );

						$toemail=$appmodel->emailaddress;
						
					//}
					
				}
				if(isset($toemail)&& $toemail!=''){
					
					$MailLookupModel = new MailLookup();
					$MailLookupModel->to=$toemail;					
					$MailLookupModel->subject=$mailContent['subject'];
					$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
					$MailLookupModel->attachment=$files;
					$MailLookupModel->mail_notification_id='';
					$MailLookupModel->mail_notification_code=$mail_notification_code;
					$Mailres=$MailLookupModel->sendMail();
					
				}
				if($responsedata['status']==0){
					$responsedata=array('status'=>1,'message'=>'Status has been changed successfully');
				}
			}
		}
		return $this->asJson($responsedata);
	}


	public function actionUploadAuditReport()
	{
		if(!Yii::$app->userrole->isCustomer() && !Yii::$app->userrole->isAdmin() ){
			return false;
		}
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if(Yii::$app->request->post())
		{

			$datapost=Yii::$app->request->post();
			if(isset($datapost['formvalues'])){
				$data =json_decode($datapost['formvalues'],true);
			}

			//waiting_for_audit_report
			//print_r($_FILES);exit;
			
			$OfferModel = new Offer();
			$target_dir = Yii::$app->params['customer_approval_files'];

			$OfferList = OfferList::find()->where(['offer_id' => $data['offer_id'],'is_latest'=>1])->one();
			$Offer = Offer::find()->where(['id' => $data['offer_id'],'status'=> $OfferModel->enumStatus['waiting_for_audit_report'] ])->one();
			if($OfferList!==null && $Offer!==null)
			{
				$app_id = $Offer->app_id;
				//if($app_id)
				//if()
				if(!Yii::$app->userrole->canViewApplication($app_id)){
					return false;
				}
				if(isset($data['actiontype']) && $data['actiontype']=='audit_report_approve' && $Offer!== null){
					$Offer->status = $OfferModel->enumStatus['finalized'];
					$Offer->save();

					$appmodel= new Application();
					Yii::$app->globalfuns->updateApplicationOverallStatus($Offer->app_id, $appmodel->arrEnumOverallStatus['quotation_approved']);


					$Audit = new Audit();
					$Audit->app_id = $Offer->app_id;
					$Audit->offer_id = $Offer->id;
					$Audit->status = 0;
					$Audit->followup_status = 0;

				/*	$appMandayModel = ApplicationManday::find()->where(['app_id' => $Audit->app_id])->one();		 
					if ($appMandayModel->final_manday == "0.00")
					{
						$Audit->status = 25;
				 
					}*/
					$Audit->save();
					//$Audit->app_id = 
				}else{

					$pdata = [];
					$pdata['offer_id'] = isset($data['offer_id'])?$data['offer_id']:'';
					if(!Yii::$app->userrole->canEditAuditReport($pdata)){
						return false;
					}

					$OfferList->volume_reconciliation_formula = $data['volume_reconciliation_formula'];
					
					/*
					if(isset($_FILES['risk_assessment_file']['name']))
					{
						$risk_assessment_tmp_name = $_FILES["risk_assessment_file"]["tmp_name"];
						$risk_assessment_name = $_FILES["risk_assessment_file"]["name"];
						$OfferList->risk_assessment_file = Yii::$app->globalfuns->postFiles($risk_assessment_name,$risk_assessment_tmp_name,$target_dir);	
					}
					*/

					if(isset($_FILES['content_claim_standard_file']['name']))
					{
						$ccs_tmp_name = $_FILES["content_claim_standard_file"]["tmp_name"];
						$ccs_name = $_FILES["content_claim_standard_file"]["name"];
						Yii::$app->globalfuns->removeFiles($OfferList->content_claim_standard_file,$target_dir);
						$OfferList->content_claim_standard_file = Yii::$app->globalfuns->postFiles($ccs_name,$ccs_tmp_name,$target_dir);	
					}


					if(isset($_FILES['reconciliation_report_file']['name']))
					{
						$reconciliation_tmp_name = $_FILES["reconciliation_report_file"]["tmp_name"];
						$reconciliation_name = $_FILES["reconciliation_report_file"]["name"];
						Yii::$app->globalfuns->removeFiles($OfferList->reconciliation_report_file,$target_dir);
						$OfferList->reconciliation_report_file = Yii::$app->globalfuns->postFiles($reconciliation_name,$reconciliation_tmp_name,$target_dir);	
					}

					if(isset($_FILES['chemical_declaration_file']['name']))
					{
						$chemical_tmp_name = $_FILES["chemical_declaration_file"]["tmp_name"];
						$chemical_name = $_FILES["chemical_declaration_file"]["name"];
						Yii::$app->globalfuns->removeFiles($OfferList->chemical_declaration_file,$target_dir);
						$OfferList->chemical_declaration_file = Yii::$app->globalfuns->postFiles($chemical_name,$chemical_tmp_name,$target_dir);	
					}

					if(isset($_FILES['social_declaration_file']['name']))
					{
						$social_tmp_name = $_FILES["social_declaration_file"]["tmp_name"];
						$social_name = $_FILES["social_declaration_file"]["name"];
						Yii::$app->globalfuns->removeFiles($OfferList->social_declaration_file,$target_dir);
						$OfferList->social_declaration_file = Yii::$app->globalfuns->postFiles($social_name,$social_tmp_name,$target_dir);	
					}

					if(isset($_FILES['environmental_declaration_file']['name']))
					{
						$environmental_tmp_name = $_FILES["environmental_declaration_file"]["tmp_name"];
						$environmental_name = $_FILES["environmental_declaration_file"]["name"];
						Yii::$app->globalfuns->removeFiles($OfferList->environmental_declaration_file,$target_dir);
						$OfferList->environmental_declaration_file = Yii::$app->globalfuns->postFiles($environmental_name,$environmental_tmp_name,$target_dir);	
					}
					
					/*
					if(isset($_FILES['environmental_report_file']['name']))
					{
						$report_tmp_name = $_FILES["environmental_report_file"]["tmp_name"];
						$report_name = $_FILES["environmental_report_file"]["name"];
						$OfferList->environmental_report_file = Yii::$app->globalfuns->postFiles($report_name,$report_tmp_name,$target_dir);	
					}

					if(isset($_FILES['chemical_list_file']['name']))
					{
						$list_tmp_name = $_FILES["chemical_list_file"]["tmp_name"];
						$list_name = $_FILES["chemical_list_file"]["name"];
						$OfferList->chemical_list_file = Yii::$app->globalfuns->postFiles($list_name,$list_tmp_name,$target_dir);	
					}
					*/
					
					$OfferList->save();
				}
				
				
				
				$responsedata=array('status'=>1,'message'=>'Status has been changed successfully');

			
			
			}
		}
		return $this->asJson($responsedata);
	}


	public function actionGetAuditfiles()
	{
		if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			
			$pdata = [];
			$pdata['audit_id'] = isset($data['audit_id'])?$data['audit_id']:'';
			$pdata['offer_id'] = isset($data['offer_id'])?$data['offer_id']:'';
			if(!Yii::$app->userrole->canViewAuditReport($pdata)){
				return false;
			}

			if(isset($data['audit_id']))
			{
				$auditmodel = Audit::find()->where(['id' => $data['audit_id']])->one();
				if($auditmodel !== null)
				{
					$offer_id = $auditmodel->offer_id;
				}
			}
			else
			{
				$offer_id = $data['offer_id'];
			}

			$model=OfferList::find()->where(['offer_id' => $offer_id])->one();
			if ($model !== null)
			{
				$app_id = $model->offer!==null?$model->offer->app_id:'';
				if(!Yii::$app->userrole->canViewApplication($app_id) && !Yii::$app->userrole->hasRights(['offer_management'])){
					return false;
				}
				$resultarr=array();
				$resultarr['status'] = '1';
				$resultarr['offerlist_id'] = $model->id;
				$resultarr['quotation_file'] = $model->quotation_file;
				$resultarr['scheme_rules_file'] = $model->scheme_rules_file;
				$resultarr['risk_assessment_file'] = $model->risk_assessment_file;
				$resultarr['content_claim_standard_file'] = $model->content_claim_standard_file;
				$resultarr['chemical_declaration_file'] = $model->chemical_declaration_file;
				$resultarr['social_declaration_file'] = $model->social_declaration_file;
				$resultarr['environmental_declaration_file'] = $model->environmental_declaration_file;
				$resultarr['reconciliation_report_file'] = $model->reconciliation_report_file;
				$resultarr['volume_reconciliation_formula'] = $model->volume_reconciliation_formula;
				$resultarr['environmental_report_file'] = $model->environmental_report_file;
				$resultarr['chemical_list_file'] = $model->chemical_list_file;
				return $resultarr;
			}

		}
		return $responsedata=array('status'=>0,'message'=>'No Files for Audit Report');
	}

	public function actionViewOffer()
	{
		//$globalfuns = Yii::$app->globalfuns;
			
		if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();
			$canViewOffer = $this->canViewOffer($data['offer_id']);
			if(!$canViewOffer){
				return false;
			}


			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			$model = Application::find()->where(['id' => $data['id']])->one();
			$offerid=Offer::find()->where(['id' => $data['offer_id']])->one();

			

			$connection = Yii::$app->getDb();
			$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();

			$modelOffer = new Offer();
					
			if ($model !== null)
			{
				$resultarr=array();
				$resultarr["can_edit_offer"]=$this->canEditOffer($data['offer_id'],$data['id']);
				$resultarr["can_send_to_hq"]=$this->canSendtoHQ($data['offer_id']);
				$resultarr["can_send_back_oss_customer"]=$this->canSendBackOssOrCustomer($data['offer_id']);
				$resultarr["can_approve_reject"]=$this->canApproveReject($data['offer_id']);
				
				
				$resultarr["id"]=$model->id;
				$resultarr["code"]=$model->code;
				//$resultarr['created_at']=date('M d,Y h:i A',$model->created_at);
				$resultarr['created_at']=date($date_format,$model->created_at);
				$resultarr["company_name"]=$model->companyname;
				$resultarr["address"]=$model->address;
				$resultarr["zipcode"]=$model->zipcode;
				$resultarr["city"]=$model->city;
				$resultarr["salutation"]=($model->salutation!="")?$model->salutation:"";
				$resultarr["salutation_name"]=($model->salutation!="")?$model->arrSalutation[$model->salutation]:"";
				
				$resultarr["title"]=($model->title!="")?$model->title:"";
				$resultarr["first_name"]=($model->firstname!="")?$model->firstname:"";
				$resultarr["last_name"]=($model->lastname!="")?$model->lastname:"";
				$resultarr["job_title"]=($model->jobtitle!="")?$model->jobtitle:"";
				$resultarr["telephone"]=($model->telephone!="")?$model->telephone:"";
				$resultarr["email_address"]=($model->emailaddress!="")?$model->emailaddress:"";
							
				$resultarr["state_id_name"]=($model->statename!="")?$model->statename:"";
				$resultarr["country_id_name"]=($model->countryname!="")?$model->countryname:"";
				$resultarr["state_id"]=($model->applicationaddress!==null && $model->applicationaddress->state_id!="")?$model->applicationaddress->state_id:"";
				$resultarr["country_id"]=($model->applicationaddress!==null && $model->applicationaddress->country_id!="")?$model->applicationaddress->country_id:"";
				$resultarr["created_by"]=($model->created_by!="")?$model->username->first_name.' '.$model->username->last_name:"";
				$resultarr["certification_status"]=$model->certification_status;
				$resultarr["preferred_partner_id"]=$model->preferred_partner_id;
				$resultarr["preferred_partner_id_name"]=($model->preferredpartner?$model->preferredpartner->name:'');
				
				$resultarr["app_status"]=$model->status;
				$resultarr["status"]=$model->arrStatus[$model->status];
				$resultarr["manday"]=$model->manday;
				$resultarr["current_date"]=date("d/m/Y");
				//$resultarr["mandays"]=$mandays;
				
				/*
				$files = LibraryDownloadFile::find()->alias('t')->where(['t.type'=>'view']);
				$files->joinWith(['librarymanual as manual']);
				$files = $files->andWhere('manual.status in (1) and manual.title=\'PCSR\'');					
				$files = $files->one();
				*/
				
				$files = LibraryDownloadFile::find()->alias('t')->where(['t.type'=>'view']);
				$files->joinWith(['librarymanual as manual']);
				$files = $files->andWhere('manual.status in (1) and manual.type=\'templates\' and manual.title=\'PCSR\'');					
				$files = $files->orderBy(['manual.version'=>SORT_DESC])->one();
				$schemefile = $files->document;				

				$resultarr["scheme_files"]=array('scheme'=>$schemefile);
				$resultarr["processor_files"]=$this->downloadProcessorfiles;
				$resultarr["download_files"]=$this->downloadOfferfiles;
				$resultarr["standard_files"]=$this->downloadStandardfiles;
				$resultarr["implementation_files"]=$this->downloadImplementationfiles;
				$resultarr["checklist_files"]=$this->downloadChecklistfiles;



				$appstdarr=[];
				$arrstandardids=[];
				$appStandard=$model->applicationstandard;
				if(count($appStandard)>0)
				{
					foreach($appStandard as $std)
					{
						$appstdarr[]=($std->standard?$std->standard->name:'');	
						$arrstandardids[]=$std->standard_id;
						$arrstandardcodes[]=$std->standard->code;
					}
				}
				$resultarr["standards"]=$appstdarr;
				$resultarr["standard_ids"]=$arrstandardids;
				$resultarr["standard_codes"]=$arrstandardcodes;

				$arrstandardcodes=array_map('strtolower', $arrstandardcodes);

				$resultarr["showChemicalList"] = count(array_intersect(['grs','gots'],$arrstandardcodes))>0?1:0;
				$resultarr["showEnvironmentalReport"] = count(array_intersect(['grs','gots'],$arrstandardcodes))>0?1:0;
				$resultarr["showEnvironmentalDeclaration"] = in_array('grs',$arrstandardcodes)?1:0;
				$resultarr["showSocialDeclaration"] = in_array('grs',$arrstandardcodes)?1:0;
				$resultarr["showChemicalDeclaration"] = in_array('grs',$arrstandardcodes)?1:0;
				$resultarr["showCCS"] = count(array_intersect(['grs','ccs','rcs','ocs'],$arrstandardcodes))>0?1:0;

				
				
				
				
				
				$appprdarr=[];
				$appProduct=$model->applicationproduct;
				if(count($appProduct)>0)
				{
					foreach($appProduct as $prd)
					{
						$appprdarr[]=array('id'=>$prd->product_id,'name'=>($prd->product?$prd->product->name:''),'wastage'=>$prd->wastage);					
					}
				}
				$resultarr["products"]=$appprdarr;
				
				$unitdetailsarr=array();
				$unitarr=array();
				$appUnit=$model->applicationunit;
				if(count($appUnit)>0)
				{
					foreach($appUnit as $unit)
					{
						//Condition for enabling the Processor Agreement file upload option only for Subcontractor
						/*
						if($offerid->status==$offerid->enumStatus['waiting-for-customer-approval'] && $unit->unit_type!=3)
						{
							continue;
						}
						*/
						
						//$statelist = State::find()->alias( 't' )->select(['id','name'])->where(['t.country_id'=>$unit->country_id])->asArray()->all();
						$unitarr["id"]=$unit->id;
						$unitarr["name"]=$unit->name;
						$unitarr["address"]=$unit->address;
						$unitarr["zipcode"]=$unit->zipcode;
						$unitarr["city"]=$unit->city;
						$unitarr["state_id"]=($unit->state_id!="")?$unit->state_id:"";
						$unitarr["country_id"]=($unit->country_id!="")?$unit->country_id:"";
						$unitarr["state_id_name"]=($unit->state_id!="")?$unit->state->name:"";
						$unitarr["country_id_name"]=($unit->country_id!="")?$unit->country->name:"";
						$unitarr["no_of_employees"]=$unit->no_of_employees;
						$unitarr["state_list"]= [];//$statelist;
						$unitarr["unit_type"]=$unit->unit_type;

						$unitprd=$unit->unitproduct;
						if(count($unitprd)>0)
						{
							$unitprdidsarr=array();
							$unitprdarr=array();
							foreach($unitprd as $unitP)
							{
								//$unitprdarr[]=($unitP->product?$unitP->product->name:'');
								//$unitprdidsarr[]=$unitP->product_id;
								//$unitprdidsarr[]=$unitP->application_product_standard_id;								
							}
							$unitarr["products"]=$unitprdarr;
							$unitarr["product_ids"]=$unitprdidsarr;
						}		

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

						
						
						$unitprocessids=[];
						$unitpcsarr=[];
						$unitpcsarrobj=[];
						$unitprocess=$unit->unitprocess;
						if(count($unitprocess)>0)
						{
							$unitpcsarr=array();
							$unitpcsarrobj=array();
							$icnt=0;
							foreach($unitprocess as $unitPcs)
							{
								$unitpcsarr[]=$unitPcs->process->name;
								$unitprocessids[]=$unitPcs->process_id;

								$unitpcsarrobj[$icnt]['id']=$unitPcs->process->id;
								$unitpcsarrobj[$icnt]['name']=$unitPcs->process->name;
								$icnt++;
							}						
						}
						$unitarr["process"]=$unitpcsarr;
						$unitarr["process_ids"]=$unitprocessids;
						$unitarr["process_data"]=$unitpcsarrobj;
						
						$unitstd=$unit->unitstandard;
						unset($unitarr["certified_standard"]);
						$certstdarr= [];
						if(count($unitstd)>0)
						{
							
							foreach($unitstd as $unitS)
							{
								$unitstdfilearr=[];
								$standardfile=$unitS->unitstandardfile;
								if(count($standardfile)>0)
								{
									foreach($standardfile as $stdfile)
									{
										$unitstdfilearr[]=$stdfile->file;
									}
								}
								
								$certstdarr[]=array("id"=>$unitS->standard_id,"standard"=>($unitS->standard?$unitS->standard->name:''),"files"=>$unitstdfilearr);
							}
							$unitarr["certified_standard"]=$certstdarr;
						}

						$unitdetailsarr[]=$unitarr;
					}
					$resultarr["units"]=$unitdetailsarr;
				}
				
				$applicationreviews=[];
				$reviewarr=[];
				$reviewcommentarr=[];
				$appReview=$model->applicationreview;
				if(count($appReview)>0)
				{
					foreach($appReview as $review)
					{
						$reviewarr=[];
						$reviewcommentarr=[];
						$applicationreviewcmt=$review->applicationreviewcomment;
						if(count($applicationreviewcmt)>0)
						{
							foreach($applicationreviewcmt as $reviewComment)
							{
								$reviewcommentarr[]=array('question'=>$reviewComment->question,'answer'=>$reviewComment->arrAnswer[$reviewComment->answer],'comment'=>$reviewComment->comment);
							}	
						}
						
						$reviewarr['reviewer']=($review->reviewer?$review->reviewer->first_name.' '.$review->reviewer->last_name:'');
						$reviewarr['answer']=$review->answer;
						
						$reviewarr['answer_name']=$review->answer?$review->arrReviewAnswer[$review->answer]:'NA';
						
						$reviewarr['status']=$review->status;		
						$reviewarr['status_name']=$review->arrReviewStatus[$review->status];					
																
						//$reviewarr['created_at']=date('M d,Y h:i A',$review->created_at);
						$reviewarr['created_at']=date($date_format,$review->created_at);
						$reviewarr['reviewcomments']=$reviewcommentarr;
						$applicationreviews[]=$reviewarr;
					}
					$resultarr["applicationreviews"]=$applicationreviews;
				}
				
				/*
				$appmdc=$model->mandaycost;
				$appmandaycost=$model->mandaycost->man_day_cost;
				$mdctaxArr=array();
				$mdctaxpercentArr=array();
				if(count($appmdc->mandaycosttax)>0)
				{
					foreach($appmdc->mandaycosttax as $val)
					{
						$mdctaxArr[]=$val->tax_name;
						$mdctaxpercentArr[]=$val->tax_percentage;
					}
				}
				*/

				

				$offercomment = OfferComment::find()->where(['offer_id' => $data['offer_id']])->all();
				if($offercomment!==null)
				{
					$approvecmt=array();
					$rejectcmt=array();
					foreach($offercomment as $cmt)
					{
						if($cmt->status!='2')
						{
							$approvecmt[]=['comment'=>$cmt->comment,'created_by_name'=>$cmt->user?$cmt->user->first_name.' '.$cmt->user->last_name:'','created_at'=>date($date_format,$cmt->created_at)];
						}
						else
						{
							$rejectcmt[]=['comment'=>$cmt->comment,'created_by_name'=>$cmt->user?$cmt->user->first_name.' '.$cmt->user->last_name:'','created_at'=>date($date_format,$cmt->created_at)];
						}
					}
					$resultarr["approve_comment"]=$approvecmt;
					$resultarr["reject_comment"]=$rejectcmt;
				}



				if($model->applicationaddress){
					$appmdc = Mandaycost::find()->where(['country_id' => $model->applicationaddress->country_id])->one();
				
					$mdctaxArr=array();
					$mdctaxpercentArr=array();
					if($appmdc!==null)
					{
						$appmandaycost=$appmdc->man_day_cost;
						if(is_array($appmdc->mandaycosttax) && count($appmdc->mandaycosttax)>0)
						{
							foreach($appmdc->mandaycosttax as $val)
							{
								$mdctaxArr[]=$val->tax_name;
								$mdctaxpercentArr[]=$val->tax_percentage;						
							}
						}elseif($appmdc->mandaycosttax!==null && $appmdc->mandaycosttax){
							$mdctaxArr[]=$appmdc->mandaycosttax->tax_name;
							$mdctaxpercentArr[]=$appmdc->mandaycosttax->tax_percentage;	
						}
					}
	
					$mdctaxpercentArr=array_sum($mdctaxpercentArr);
					$resultarr["offer_currency_code"]=$appmdc->currency_code;
					
					$resultarr["tax_percentage"]=$mdctaxpercentArr;
				}
				
				 
				
				
				//$arrFees=array('fee_name'=>'Company Audit Fee','fee_description'=>$mandays.' Manday','amount'=>number_format((float)$mandaycostamt, 2, '.', ''));
				//$resultarr["fees"][]=$arrFees;
				
				//$resultarr["offer"]=array('currency_code'=>$model->mandaycost->currency_code,'mandaycost'=>$appmandaycost,'mandays'=>$mandays,'mandaycostamt'=>$mandaycostamt,'tax_nanme'=>implode(', ',$mdctaxArr),'tax_percentage'=>$mdctaxpercentArr);
				//$resultarr["offer_currency_code"]=$model->mandaycost->currency_code;
				
												
				/*
				$arr_offer_certification_fee=[];
				$offer_certification_fee = OfferListCertificationFee::find()->where(['offer_list_id' => $offerdetails['id']])->all();
				if(count($offer_certification_fee)>0)
				{
					foreach($offer_certification_fee as $certification_fee)
					{
						$arr_offer_certification_fee[]=array('activity'=>$certification_fee->activity,'description'=>$certification_fee->description,'amount'=>$certification_fee->amount);
					}
					
				}
				$resultarr['offer_certification_fee']=$arr_offer_certification_fee;
				
				
				$offer_other_expenses = OfferListOtherExpenses::find()->where(['offer_list_id' => $offerdetails['id']])->all();
				
				$arr_offer_other_expenses=[];
				$offer_other_expenses = OfferListOtherExpenses::find()->where(['offer_list_id' => $offerdetails['id']])->all();
				if(count($offer_other_expenses)>0)
				{
					foreach($offer_other_expenses as $other_expenses)
					{
						$arr_offer_other_expenses[]=array('activity'=>$other_expenses->activity,'description'=>$other_expenses->description,'amount'=>$other_expenses->amount);
					}
					
				}
				$resultarr['offer_other_expenses']=$arr_offer_other_expenses;
				*/		
				
				$offermodel=$model->offer;
				if($offermodel!==null)
				{
					//$offerdetails = OfferList::find()->where(['offer_id' => $data['offer_id'],'is_latest'=>1])->asArray()->one();
					//$offerdetails = OfferList::find()->where(['offer_id' => $data['offer_id'],'is_latest'=>1])->asArray()->one();

					
					$offerdetails['offer_status'] = $offermodel->status;
					$offerdetails['taxname'] = $offermodel->taxname;
					$offerdetails['offer_code'] = $offermodel->offer_code;
					$offerdetails['updated_at'] = date($date_format,$offermodel->updated_at);

					$resultarr["mandays"]=$offermodel->manday;
				
					$offerStatus=$offermodel->status;
					
					$offerlist = $offermodel->offerlist;
					$offerlistdetails=$offerid->offerlist;
					if($offerlist!==null)
					{
						$offerdetails['discount'] = $offerlist->discount?:0;
						$offerdetails['volume_reconciliation_formula'] = $offerlist->volume_reconciliation_formula?:'';
						$offerdetails['grand_total_fee'] = $offerlist->grand_total_fee;
						$offerdetails['offerlist_id'] = $offerlist->id;
						$offerdetails['quotation_file'] = $offerlist->quotation_file ?:'';
						//$offerdetails['risk_assessment_file'] = $offerlist->risk_assessment_file ?:'';
						$offerdetails['content_claim_standard_file'] = $offerlist->content_claim_standard_file ?:'';
						$offerdetails['chemical_declaration_file'] = $offerlist->chemical_declaration_file ?:'';
						$offerdetails['social_declaration_file'] = $offerlist->social_declaration_file ?:'';
						$offerdetails['environmental_declaration_file'] = $offerlist->environmental_declaration_file ?:'';
						$offerdetails['reconciliation_report_file'] = $offerlist->reconciliation_report_file ?:'';
						//$offerdetails['environmental_report_file'] = $offerlist->environmental_report_file ?:'';
						//$offerdetails['chemical_list_file'] = $offerlist->chemical_list_file ?:'';
						//$offerdetails['scheme_rules_file'] = $offerlist->scheme_rules_file?:'';
						
						$arrProcessorFile=array();
						$contentP='';
						$offerProcessorObj = $offerlist->offerlistprocessor;
						if($offerProcessorObj!==null)
						{
							$processorfileObj = $offerProcessorObj->processorfile;
							if(count($processorfileObj)>0)
							{
								foreach($processorfileObj as $processorF)
								{
									if($processorF->unit){
										$contentP.=$processorF->unit->name;
										$arrProcessorFile[]=array('id'=>$processorF->id,'unit_id'=>$processorF->unit_id,'unit_name'=>$processorF->unit->name,'processor_file'=>$processorF->processor_file);
									}
									
								}
							}
						}
						$offerdetails['processor_files'] = $arrProcessorFile;

						$offerdetails['currency'] = $offerlist->currency;
						$offerdetails['conversion_currency_code'] = $offerlist->conversion_currency_code;
						$offerdetails['conversion_rate'] = $offerlist->conversion_rate;						
						$offerdetails['certification_fee_sub_total'] = $offerlist->certification_fee_sub_total;
						$offerdetails['other_expense_sub_total'] = $offerlist->other_expense_sub_total;
						
						$offerdetails['total'] = $offerlist->total;
						$offerdetails['gst_rate'] = $offerlist->tax_amount;
						$offerdetails['total_payable_amount'] = $offerlist->total_payable_amount;
						$offerdetails['conversion_total_payable'] = $offerlist->conversion_total_payable;
																		
						$offerdetails['conversion_required_status'] = $offerlist->conversion_required_status;
												
						$resultarr['offer']=$offerdetails;
					
						$offerotherexpense = $offerlist->offerotherexpenses;
						$conversion_required_status = $offerlistdetails->conversion_required_status;
						
						if(count($offerotherexpense)>0)
						{
							$arrOE=array();
							$otherexpns=array();
							$otherexpnsarr = [];
							$cost=0;
							$totalcertExpense = 0;
							$otherexpnsarr[] = array('activity'=>'Licensee Fee','description'=> '','amount'=>number_format($totalcertExpense, 2, '.', ''));
							
							foreach($offerotherexpense as $otherE)
							{
								//$cost=($conversion_required_status!=0)?$otherE->conversion_amount:$otherE->amount;
								$cost=$otherE->amount;
								if($otherE->entry_type ==1)
								{

									$arrOE=array('activity'=>$otherE->activity,'description'=>$otherE->description,'amount'=>number_format($cost, 2, '.', ''));
									$otherexpnsarr[]=$arrOE;
								}
								else
								{
									$totalcertExpense += $cost;
								}
							}
							

							$otherexpnsarr[0] = array('activity'=>'Licensee Fee','description'=> $offermodel->standard,'amount'=>number_format($totalcertExpense, 2, '.', ''));
							foreach($otherexpnsarr as $otherExp)
							{
								$otherexpns=array('activity'=>$otherExp['activity'],'description'=>$otherExp['description'],'amount'=>number_format($otherExp['amount'], 2, '.', ''));
								$resultarr["offer_other_expenses"][]=$otherexpns;
							}
							
						}
						
						$certificationfee = $offerlist->offercertificationfee;
						if(count($certificationfee)>0)
						{
							$arrFees=array();
							foreach($certificationfee as $certF)
							{
								$arrFees=array('activity'=>$certF->activity,'description'=>$certF->description,'amount'=>number_format((float)$certF->amount, 2, '.', ''));
								$resultarr["offer_certification_fee"][]=$arrFees;
							}
							
						}					
					}

					$reinitiatecommentarr=[];
					$reinitiatecommentobj=$offermodel->reinitiatecomment;
					if(count($reinitiatecommentobj)>0)
					{
						foreach($reinitiatecommentobj as $reinitiatecomment)
						{
							$reinitiatecommentarr[]=array(
								'created_by_name'=>$reinitiatecomment->user?$reinitiatecomment->user->first_name.' '.$reinitiatecomment->user->last_name:'',
								'created_at'=>date($date_format,$reinitiatecomment->created_at),
								'comment'=>$reinitiatecomment->comment
							);
						}
					}
					$resultarr["reinitiate_comment"]=$reinitiatecommentarr;
				}else{
					$offerdetails['discount'] = 0;
				}
				
				
				$arrOfferHistory=array();
				$offerHistory = OfferList::find()->where(['offer_id' => $data['offer_id'],'is_latest'=>2])->all();
				if(count($offerHistory)>0)
				{
					foreach($offerHistory as $offermodel)
					{
						$arrOfferInfo=array();
						$arrOfferInfo['discount'] = $offermodel->discount?:0;
					    $arrOfferInfo['grand_total_fee'] = $offermodel->grand_total_fee;
						
						$arrOfferInfo['currency'] = $offermodel->currency;
						$arrOfferInfo['conversion_currency_code'] = $offermodel->conversion_currency_code;
						$arrOfferInfo['conversion_rate'] = $offermodel->conversion_rate;	
						$arrOfferInfo['conversion_required_status'] = $offermodel->conversion_required_status;						
						$arrOfferInfo['certification_fee_sub_total'] = $offermodel->certification_fee_sub_total;
						$arrOfferInfo['other_expense_sub_total'] = $offermodel->other_expense_sub_total;
						
						$arrOfferInfo['total'] = $offermodel->total;
						$arrOfferInfo['gst_rate'] = $offermodel->tax_amount;
						$arrOfferInfo['total_payable_amount'] = $offermodel->total_payable_amount;
						$arrOfferInfo['conversion_total_payable'] = $offermodel->conversion_total_payable;
																		
						$offerotherexpense = $offermodel->offerotherexpenses;
						if(count($offerotherexpense)>0)
						{
							$arrOE=array();
							foreach($offerotherexpense as $otherE)
							{
								$arrOE=array('activity'=>$otherE->activity,'description'=>$otherE->description,'amount'=>number_format($otherE->amount, 2, '.', ''));
								$arrOfferInfo["other_expenses"][]=$arrOE;
							}
							
						}
						
						$certificationfee = $offermodel->offercertificationfee;
						if(count($certificationfee)>0)
						{
							$arrOE=array();
							foreach($certificationfee as $certF)
							{
								$arrFees=array('activity'=>$certF->activity,'description'=>$certF->description,'amount'=>number_format((float)$certF->amount, 2, '.', ''));
								$arrOfferInfo["fees"][]=$arrFees;
							}
							
						}						
						
						$arrOfferHistory[]=$arrOfferInfo;
					}	
				}
				$resultarr['offerhistory']=$arrOfferHistory;
				$resultarr['offerenumstatus']=$modelOffer->enumStatus;
				
				return $resultarr;
				
				
				
			}
		}
	}
	

	public function actionCustomerApprovalfile(){
		$data = Yii::$app->request->post();
		//$data['id'];
		$files = OfferList::find()->where(['id'=>$data['offerlist_id']])->one();
		if($files ===null){ return false;}
		$app_id = $files->offer->app_id;
		if(!Yii::$app->userrole->canViewApplication($app_id) && !Yii::$app->userrole->hasRights(['offer_management'])){
			return false;
		}

		if($data['type'] == 'quotation'){
			$file = $files->quotation_file;
		}else if($data['type'] == 'processor'){
			$file = $files->scheme_rules_file;
		}else if($data['type'] == 'risk_assessment_file'){
			$file = $files->risk_assessment_file;
		}else if($data['type'] == 'content_claim_standard_file'){
			$file = $files->content_claim_standard_file;
		}else if($data['type'] == 'reconciliation_report_file'){
			$file = $files->reconciliation_report_file;
		}else if($data['type'] == 'chemical_declaration_file'){
			$file = $files->chemical_declaration_file;
		}else if($data['type'] == 'social_declaration_file'){
			$file = $files->social_declaration_file;
		}else if($data['type'] == 'environmental_declaration_file'){
			$file = $files->environmental_declaration_file;
		}else if($data['type'] == 'environmental_report_file'){
			$file = $files->environmental_report_file;
		}else if($data['type'] == 'chemical_list_file'){
			$file = $files->chemical_list_file;
		}
		
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['customer_approval_files'].$file;
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

	public function actionDownloadAuditFile(){
		$data = Yii::$app->request->post();
		//$data['id'];

		
		$pdata = [];
		$pdata['offer_id'] = $data['offer_id'];

		$Audit = Audit::find()->where(['offer_id'=>$data['offer_id']])->one();
		if($Audit !== null){
			$pdata['audit_id'] = $Audit->id;
		}
		if(!Yii::$app->userrole->canViewAuditReport($pdata)){
			return false;
		}
		$files = OfferList::find()->where(['offer_id'=>$data['offer_id']])->one();
		if($data['type'] == 'risk_assessment_file'){
			$file = $files->risk_assessment_file;
		}else if($data['type'] == 'content_claim_standard_file'){
			$file = $files->content_claim_standard_file;
		}else if($data['type'] == 'reconciliation_report_file'){
			$file = $files->reconciliation_report_file;
		}else if($data['type'] == 'chemical_declaration_file'){
			$file = $files->chemical_declaration_file;
		}else if($data['type'] == 'social_declaration_file'){
			$file = $files->social_declaration_file;
		}else if($data['type'] == 'environmental_declaration_file'){
			$file = $files->environmental_declaration_file;
		}else if($data['type'] == 'environmental_report_file'){
			$file = $files->environmental_report_file;
		}else if($data['type'] == 'chemical_list_file'){
			$file = $files->chemical_list_file;
		}
		
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['customer_approval_files'].$file;
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

	public function actionTemplatefile()
	{
		$data = Yii::$app->request->post();
		
		if($data['template_type']=='scheme')
		{
			$files = LibraryDownloadFile::find()->alias('t')->where(['t.type'=>'view']);
			$files->joinWith(['librarymanual as manual']);
			$files = $files->andWhere('manual.status in (1) and manual.type=\'templates\' and manual.title=\'PCSR\'');					
			$files = $files->orderBy(['manual.version'=>SORT_DESC])->one();
			$file = $files->document;			
			$target_dir = Yii::$app->params['library_files']."templates/";				
			$filepath=$target_dir.$file;					
		}
		else if($data['template_type']=='standard')
		{
			$file =$this->downloadStandardfiles[$data['standard_code']];		
		}
		else if($data['template_type']=='implementation')
		{
			$file =$this->downloadImplementationfiles[$data['standard_code']];		
		}
		else if($data['template_type']=='checklist')
		{
			$file =$this->downloadChecklistfiles[$data['standard_code']];		
		}
		else if($data['template_type'] =='risk_assessment' 
		|| $data['template_type'] =='reconciliation_report'
		|| $data['template_type'] =='content_claim_standard'
		|| $data['template_type'] =='chemical_declaration'
		|| $data['template_type'] =='social_declaration'
		|| $data['template_type'] =='environmental_declaration'
		|| $data['template_type'] =='environmental_report'
		|| $data['template_type'] =='chemical_list')
		{
			$file =$this->downloadOfferfiles[$data['template_type']];
		}
		else if($data['template_type']=='processor')
		{
			$file =$this->downloadProcessorfiles[$data['template_type']];
		}
		

		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		if($data['template_type']!='scheme')
		{
			$filepath=Yii::$app->params['template_files'].$file;
		}
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

	public function actionCustomerProcessorApprovalfile(){
		$data = Yii::$app->request->post();
		//$data['id'];
		$files = OfferListProcessorFile::find()->where(['id'=>$data['file_id'],'unit_id'=>$data['unit_id']])->one();
		if($files ===null){ return false;}
		$OfferList = OfferList::find()->where(['id'=>$data['offerlist_id']])->one();
		if($OfferList !== null){
			$app_id = $OfferList->offer->app_id;
			if(!Yii::$app->userrole->canViewApplication($app_id) && !Yii::$app->userrole->hasRights(['offer_management'])){
				return false;
			}
		}else{
			return false;
		}
		

		$file = $files->processor_file;
		
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['customer_approval_files'].$file;
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
	
	public function actionGetOffers()
    {
		$modelOffer = new Offer();
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");	
		if ($data) 
		{	
			$model = Offer::find()->where(['t.status'=>$modelOffer->enumStatus['finalized']])->alias('t');
			$model = $model->joinWith(['application as app']);
			$model = $model->join('left join', 'tbl_offer_list as list','list.offer_id=t.id and list.is_latest=1');
			$model = $model->join('left join', 'tbl_invoice as invoice','invoice.offer_id=t.id');
			$model = $model->andWhere(' app.customer_id = "'.$data['id'].'" ');
			$model = $model->all();	

			if(count($model)>0)
			{
				foreach($model as $offer)
				{			
					$data=array();
					$data['id']=$offer->id;
					$data['app_id']=$offer->application->id;
					$data['code']=$offer->application->code;
					$data['offer_code']=$offer->offer_code;
					//$data['created_at']=date('M d,Y h:i A',$offer->created_at);
					$data['created_at']=date($date_format,$offer->created_at);
					$data['company_name']=$offer->application->companyname;
					$data['standard']=$offer->standard;
					$data['manday']=$offer->manday;
					$data['telephone']=$offer->application->telephone;
					$data['currency']=$offer->offerlist->currency;
					$data['total_payable_amount']=$offer->offerlist->total_payable_amount;				
					$data['invoice_status']=$offer->invoice?$offer->invoice->status:'';
					$data['invoice_status_name']=$offer->invoice?$offer->invoice->arrStatus[$offer->invoice->status]:'Open';
					$data['invoice_id']=$offer->invoice?$offer->invoice->id:'';
					$data['invoice_total_payable_amount']=$offer->invoice?$offer->invoice->total_payable_amount:'';
					$data['invoice_number']=$offer->invoice?$offer->invoice->invoice_number:'';
					
					$data['offer_status']=$offer->status;
					$data['offer_status_name']=$offer->arrStatus[$offer->status];
					
					$data['application_unit_count']=count($offer->application->applicationunit);
					
					$arrAppStd=array();
					$appStd = $offer->application->applicationstandard;
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
				$responsedata =array('status'=>1,'offers'=>$app_list);
			}
		}
		return $this->asJson($responsedata);
	}	

	public function actionValidateAuditReport()
	{
		if(!Yii::$app->userrole->isCustomer() && !Yii::$app->userrole->isAdmin() ){
			return false;
		}
		$reportFillStatus=true;	
		$audit_report_title = 'Confirmation';

		//$responsedata=array('audit_report_valid'=>$reportFillStatus,'audit_report_title'=>$audit_report_title,'audit_report_message'=>'');
		//return $this->asJson($responsedata);

		$post = yii::$app->request->post();		
		if($post)
		{
			//$auditID = $post['audit_id'];
			//$unitID = $post['unit_id'];
			$appID = $post['app_id'];
			$offerID = $post['offer_id'];

			$innerContent='';	
			
			$total_employees=0;
			$connection = Yii::$app->getDb();

			/*
			$commandReportInterviewSummary = $connection->createCommand("SELECT SUM(total_employees) AS total_emp FROM `tbl_audit_report_interview_summary` WHERE  unit_id='".$unitID."'");
			$interviewSummaryResult = $commandReportInterviewSummary->queryOne();
			if(count($interviewSummaryResult)>0)
			{
				$total_employees = $interviewSummaryResult['total_emp'];
			}
			
			$modelAuditReportInterviewEmployees = AuditReportInterviewEmployees::find()->where(['unit_id' => $unitID])->all();
			$modelAuditReportInterviewRequirementReview = AuditReportInterviewRequirementReview::find()->where(['unit_id' => $unitID])->all();
			if(count($modelAuditReportInterviewEmployees)<=0 || count($modelAuditReportInterviewRequirementReview)<=0 || $total_employees<=0)
			{
				$innerContent.='<li>Worker Interview.</li>';
				$reportFillStatus=false;
			}		
			*/
			
			$Application = Application::find()->where(['id'=>$appID])->one();
			//$subtopicArr = Yii::$app->globalfuns->getCurrentSubtopicIds($Application->applicationscopeholder->id);
			/*
			$result = Yii::$app->globalfuns->getSubtopic($Application->applicationscopeholder->id);
			$subtopicArr = [];
			if(count($result)>0){
				foreach($result as $subdata){
					$subtopicArr[] =$subdata['id'];
				}
			}
			*/

			//$chkdata = ['unit_id'=>$Application->applicationscopeholder->id,'sub_topic_id'=>$subtopicArr,'report_name'=>'clientinformation_list'];
			
			$ApplicationUnit = ApplicationUnit::find()->where(['app_id'=>$appID])->all();
			if(count($ApplicationUnit)>0){
				$first_occurence = true;
				foreach($ApplicationUnit as $appunit)
				{
					
					$finalMandayCost = $appunitmanday = ApplicationUnitManday::find()->where(['unit_id' => $appunit->id])->one();
                    if($finalMandayCost->final_manday > 0){						
						if($first_occurence)
							{
								$first_occurence = false;
								$OfferModel = Offer::find()->where(['id' => $offerID])->one();							
								if($OfferModel!==null)
								{
									$offerlist = $OfferModel->offerlist;
									if($offerlist!==null)
									{
										//&& $offerlist->social_declaration_file==''
										if($offerlist->volume_reconciliation_formula=='' )
										{
											$innerContent.='<li>Audit Report [Reconciliation Report, Volume Reconciliation Formula & Declarations].</li>';	
											$reportFillStatus=false;
										}
									}
								}	
								
								
								$AuditReportClientInformationGeneralInfo = AuditReportClientInformationGeneralInfo::find()->where(['app_id' => $appID])->one();
								if($AuditReportClientInformationGeneralInfo === null){
									$innerContent.='<li>General Information.</li>';	
									$reportFillStatus=false;
								}

								$checksupplier = 1;
								$AuditReportApplicableDetails = AuditReportApplicableDetails::find()->where(['app_id' => $appID,'report_name'=>'supplier_list','status'=>2])->one();
								if($AuditReportApplicableDetails!==null){
									$checksupplier = 0;
								}
								if($checksupplier){
									$AuditReportClientInformationSupplierInformation = AuditReportClientInformationSupplierInformation::find()->where(['app_id' => $appID])->one();
									if($AuditReportClientInformationSupplierInformation === null){
										$innerContent.='<li>Supplier Information.</li>';	
										$reportFillStatus=false;
									}
								}
								
								$AuditReportClientInformationChecklistReview = AuditReportClientInformationChecklistReview::find()->where(['app_id' => $appID])->one();
								if($AuditReportClientInformationChecklistReview === null){
									$innerContent.='<li>Checklist.</li>';	
									$reportFillStatus=false;
								}
							}

						$unitID = $appunit->id;
						$subtopicArr = Yii::$app->globalfuns->getCurrentSubtopicIds($unitID);
						

						
						$checkenvironment = 1;
						$AuditReportApplicableDetails = AuditReportApplicableDetails::find()->where(['unit_id' => $unitID,'report_name'=>'environment_list'])->one();
						if($AuditReportApplicableDetails!==null){
							if($AuditReportApplicableDetails->status == '2'){
								$checkenvironment = 0;
							}
						}
						$chkdata = ['unit_id'=>$unitID,'sub_topic_id'=>$subtopicArr];
						$chkdata['report_name']='environment_list';
						$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);

						if($checkenvironment && $formstatus){
							$modelAuditReportEnvironment = AuditReportEnvironment::find()->where(['unit_id' => $unitID])->all();
							if(count($modelAuditReportEnvironment)<=0)
							{
								$innerContent.='<li>Environment for '.$appunit->name.'.</li>';	
								$reportFillStatus=false;
							}
						}
						
						//$chkdata = ['unit_id'=>$unitID,'report_name'=>'clientinformation_list'];
						$chkdata['report_name']='clientinformation_list';
						$clientformstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
						if($clientformstatus){
							$AuditReportClientInformationProcess = AuditReportClientInformationProcess::find()->where(['app_id' => $appID,'unit_id' => $unitID])->one();
							if($AuditReportClientInformationProcess === null){
								$innerContent.='<li>Product Controls for '.$appunit->name.'.</li>';	
								$reportFillStatus=false;
							}
						}

					}	// End IF				
					
				}
			}
				
			/*
			$modelAuditReportChemicalList = AuditReportChemicalList::find()->where(['unit_id' => $unitID])->all();
			if(count($modelAuditReportChemicalList)<=0)
			{
				$innerContent.='<li>Chemical List.</li>';
				$reportFillStatus=false;
			}
			*/						
			
			$audit_report_message='
			<div class="text-danger m-t-10 m-l-15 m-r-15">
				<strong>This Audit Report details are empty/blank. You should enter data before submitting for Audit:</strong><br>
				<ul>
					'.$innerContent.'									
				</ul>
			</div>';
		}	
		
		if(!$reportFillStatus)
		{
			$audit_report_title = 'Notification';
		}
			  
		$responsedata=array('audit_report_valid'=>$reportFillStatus,'audit_report_title'=>$audit_report_title,'audit_report_message'=>$audit_report_message);
		return $this->asJson($responsedata);
	}

	public function actionGetreportlist(){
		$data = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];

		$environmentliststatus = false;
		$app_id = $data['app_id'];
		$offer_status = isset($data['offer_status'])?$data['offer_status']:'';
		$offermodel = new Offer();
		//enumStatus
		/*
		$Offer = Offer::find()->where(['app_id'=>$app_id,'status'=>$offermodel->enumStatus['finalized'] ])->one();
		if($Offer !== null){
			$applicableforms = [];
			ClientInformationChecklist
			$applicableforms['clientinformation_list'] =
			//$applicableforms['environment_list'] = $environmentliststatus;
			return $this->asJson($applicableforms);
		}
		*/
		/*
		$ApplicationUnit = ApplicationUnit::find()->where(['app_id'=>$app_id])->all();
		$applicableforms = [];
		if(count($ApplicationUnit)>0){
			foreach($ApplicationUnit as $appunit){

				$result = Yii::$app->globalfuns->getSubtopic($appunit->id);
				$subtopicArr = [];
				if(count($result)>0){
					foreach($result as $subdata){
						$subtopicArr[] =$subdata['id'];
					}
				}

				
				$chkdata = ['unit_id'=>$appunit->id,'sub_topic_id'=>$subtopicArr];
				$chkdata['report_name']='environment_list';
				$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
				$applicableforms[$appunit->id]['environment_list'] = $formstatus;
				if($formstatus){
					$environmentliststatus = true;
				}


				//$chkdata = ['unit_id'=>$appunit->id,'report_name'=>'clientinformation_list'];
				$chkdata['report_name']='clientinformation_list';
				$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
				$applicableforms[$appunit->id]['clientinformation_list'] = $formstatus;
				
			}
		}
		$Application = Application::find()->where(['id'=>$app_id])->one();
		$result = Yii::$app->globalfuns->getSubtopic($Application->applicationscopeholder->id);
		$subtopicArr = [];
		if(count($result)>0){
			foreach($result as $subdata){
				$subtopicArr[] =$subdata['id'];
			}
		}
		$chkdata = ['unit_id'=>$Application->applicationscopeholder->id,'sub_topic_id'=>$subtopicArr,'report_name'=>'clientinformation_list'];
		$applicableforms['clientinformation_list'] = Yii::$app->globalfuns->getReportsAccessible($chkdata);
		$applicableforms['environment_list'] = $environmentliststatus;
		*/
		
		
		if($offer_status!='' && $offer_status==$offermodel->enumStatus['finalized']){
			$applicableforms= [];
			$AuditReportClientInformationGeneralInfo = AuditReportClientInformationGeneralInfo::find()->where(['app_id'=>$app_id])->one();
			if($AuditReportClientInformationGeneralInfo === null){
				$applicableforms['clientinformation_list'] = false;
			}else{
				$applicableforms['clientinformation_list'] = true;
			}
			
			$environmentliststatus = false;
			$ApplicationUnit = ApplicationUnit::find()->where(['app_id'=>$app_id])->all();
			if(count($ApplicationUnit)>0){
				foreach($ApplicationUnit as $appunit){
					$unit_id = $appunit->id;
					$AuditReportEnvironment = AuditReportEnvironment::find()->where(['app_id'=>$app_id, 'unit_id'=>$unit_id])->one();
					$AuditReportApplicableDetails = AuditReportApplicableDetails::find()->where(['app_id'=>$app_id, 'unit_id'=>$unit_id])->one();
					if($AuditReportEnvironment !== null || $AuditReportApplicableDetails!== null){
						$applicableforms[$unit_id]['environment_list'] = true;
						$environmentliststatus = true;
					}else{
						$applicableforms[$unit_id]['environment_list'] = false;
					}
					

					$AuditReportClientInformationProcess = AuditReportClientInformationProcess::find()->where(['app_id'=>$app_id, 'unit_id'=>$unit_id])->one();
					if($AuditReportClientInformationProcess !== null){
						$applicableforms[$unit_id]['clientinformation_list'] = true;
					}else{
						$applicableforms[$unit_id]['clientinformation_list'] = false;
					}
					
				}
			}

			$applicableforms['environment_list'] = $environmentliststatus;
		}else{
			$applicableforms = Yii::$app->globalfuns->getbasicreportlist($data);
		}
		return $this->asJson($applicableforms);
	}

	public function canViewGenerateOffer($offermodel){

		$hasAccess = 1;

		return $hasAccess;
	}

	public function canViewOffer($offer_id){

		$hasAccess = 0;

		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];

		$OfferStatusModel = new Offer();

		$offermodel = Offer::find()->where(['t.id' => $offer_id ])->alias('t');
		$offermodel = $offermodel->joinWith(['application as app']);
		$offermodel = $offermodel->join('left join', 'tbl_offer_list as list','list.offer_id=t.id and list.is_latest=1');

		if(Yii::$app->userrole->isUser() && !Yii::$app->userrole->hasRights(array('offer_management') )){
			return $hasAccess = 0;
		}
		
		if( Yii::$app->userrole->isOSSUser()){
			$offermodel = $offermodel->andWhere(' app.franchise_id="'.$franchiseid.'" ');
		}else if( Yii::$app->userrole->isOSS()){
			if($resource_access == '5'){
				$userid = $franchiseid;
			}
			$offermodel = $offermodel->andWhere(' app.franchise_id="'.$userid.'"  and (t.status!='.$OfferStatusModel->enumStatus['in-progress'].' and t.status!='.$OfferStatusModel->enumStatus['open'].') ');
		}else if( Yii::$app->userrole->isCustomer()){
			$offermodel = $offermodel->andWhere(' app.customer_id="'.$userid.'" and t.status>='.$OfferStatusModel->enumStatus['waiting-for-customer-approval'].' ');
		}

		//Condition for User with Each Offer Actions Starts 
		$sqlcondition = [];
		if(Yii::$app->userrole->isUser()){
			if(in_array('generate_offer',$rules)){
				$sqlcondition[] = ' (t.created_by ='.$userid.' or t.updated_by ='.$userid.' or list.id IS NULL or list.created_by='.$userid.' )';
			}
			if(in_array('offer_approvals',$rules)){
				$sqlcondition[] = ' (t.status >="'.$OfferStatusModel->enumStatus['customer_approved'].'") ';
			}
			if(in_array('oss_quotation_review',$rules)){
				$sqlcondition[] = ' (t.status >="'.$OfferStatusModel->enumStatus['waiting-for-oss-approval'].'") ';
			}
		}
		//Condition for User with Each Offer Actions Ends 


		/// To include in condition ends here
		if(count($sqlcondition)>0){
			$strSqlCondition = ' '.implode(' OR ',$sqlcondition).' ';
			$offermodel = $offermodel->andWhere( $strSqlCondition );
		}

		$offeraccessobj = $offermodel->one();
		if($offeraccessobj !==null ){
			$hasAccess = 1;
		}
		return $hasAccess;
	}
	public function canGenerateOffer($offermodel){

		$hasAccess = 1;

		return $hasAccess;
	}
}
