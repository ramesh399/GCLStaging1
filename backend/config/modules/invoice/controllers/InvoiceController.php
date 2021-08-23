<?php
namespace app\modules\invoice\controllers;

use Yii;
use app\models\EnquiryStandard;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationProduct;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationUnitProduct;
use app\modules\application\models\ApplicationUnitProcess;
use app\modules\application\models\ApplicationUnitCertifiedStandard;
use app\modules\application\models\ApplicationUnitCertifiedStandardFile;
use app\modules\master\models\StandardInspectionTime;
use app\modules\master\models\StandardReduction;
use app\modules\master\models\StandardReductionRate;
use app\modules\master\models\User;
use app\modules\master\models\UserCompanyInfo;
use app\modules\master\models\Mandaycost;
use app\modules\master\models\Country;
use app\modules\master\models\State;
use app\modules\offer\models\Offer;
use app\modules\offer\models\OfferList;
use app\modules\offer\models\OfferListCertificationFee;
use app\modules\offer\models\OfferComment;
use app\modules\offer\models\OfferListOtherExpenses;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;
use app\modules\master\models\Settings;

use app\modules\invoice\models\Invoice;
use app\modules\invoice\models\InvoiceTax;
use app\modules\invoice\models\InvoiceDetails;

use app\modules\invoice\models\InvoiceStandard;

use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\Audit;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * InvoiceController implements the CRUD actions for Process model.
 */
class InvoiceController extends \yii\rest\Controller
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
		$modelInvoice = new Invoice();
		
		$post = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];

		//$model = Invoice::find()->where(['!=','t.status',$modelInvoice->enumStatus['finalized']])->alias('t');
		$model = Invoice::find()->alias('t');
		//$model = Invoice::find()->alias('t');
		$model->joinWith(['application as app']);	
		$model = $model->join('left join', 'tbl_application_change_address as appaddress','appaddress.id=app.address_id');
		
		$invoicetype = $post['invoicetype'];

		if($invoicetype==3)
		{
			$model = $model->join('inner join', 'tbl_users as user','t.customer_id=user.id');
			$model = $model->join('inner join', 'tbl_user_company_info as userinfo','userinfo.user_id=user.id');
		}

		if($invoicetype==4)
		{
			$model = $model->join('inner join', 'tbl_users as user','t.franchise_id=user.id');
			$model = $model->join('inner join', 'tbl_user_company_info as userinfo','userinfo.user_id=user.id');
		}

		
		if($resource_access != 1){			
			$model = $this->canViewInvoice($model,$invoicetype);
		}		
		
		if(isset($post['invoicetype']) && $post['invoicetype'] !=''){
			$model = $model->andWhere( ' t.invoice_type='.$post['invoicetype'].' ' );
		}

		if(isset($post['creditFilter']) && $post['creditFilter'] !='')
		{
			$model = $model->andWhere(['t.credit_note_option'=> $post['creditFilter']]);			
		}

		if(isset($post['paymentFilter']) && $post['paymentFilter'] !='')
		{
			$model = $model->andWhere(['t.status'=> $post['paymentFilter']]);			
		}

		if(isset($post['franchiseFilter']) && $post['franchiseFilter'] !='')
		{
			$model = $model->andWhere(['t.franchise_id'=> $post['franchiseFilter']]);			
		}

		if(isset($post['from_date']))
		{
			$model = $model->andWhere(['>=','t.payment_date', date('Y-m-d',strtotime($post['from_date'])) ]);			
		}
		
		if(isset($post['to_date']))
		{
			$model = $model->andWhere(['<=','t.payment_date', date('Y-m-d',strtotime($post['to_date'])) ]);			
		}		

		$total_paid_amount = 0;
		$total_unpaid_amount = 0;
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			$statusarray=array_map('strtolower', $modelInvoice->arrStatus);
			$arrCreditNoteOptions=array_map('strtolower', $modelInvoice->arrCreditNoteOptions);

			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$search_status = array_search(strtolower($searchTerm),$statusarray);
				if($search_status===false)
				{
					$search_status = '';
				}

				$search_type = array_search(strtolower($searchTerm),$arrCreditNoteOptions);
				if($search_type===false)
				{
					$search_type = '';
				}

				$searchTerm = $post['searchTerm'];
				
				if($post['invoicetype']==1 || $post['invoicetype']==2)
				{	
					$model = $model->andFilterWhere([
						'or',
						['like', 't.invoice_number', $searchTerm],
						['like', 't.total_payable_amount', $searchTerm],
						['like', 'date_format(t.payment_date, \'%b %d, %Y\' )', $searchTerm],
						['like', 'appaddress.company_name', $searchTerm],
						['like', 'appaddress.telephone', $searchTerm],		
						['t.credit_note_option'=>$search_type],
						['t.status'=>$search_status]				
					]);
				}
				else
				{
					$model = $model->andFilterWhere([
						'or',
						['like', 't.invoice_number', $searchTerm],
						['like', 't.total_payable_amount', $searchTerm],
						['like', 'date_format(t.payment_date, \'%b %d, %Y\' )', $searchTerm],
						['like', 'userinfo.company_name', $searchTerm],
						['like', 'userinfo.company_telephone', $searchTerm],		
						['t.credit_note_option'=>$search_type],
						['t.status'=>$search_status]				
					]);
				}

				$countmodel = clone $model;
				$countmodel->select(' Sum(Case When t.status="5" Then total_payable_amount Else 0 End) paid_amount, SUM(Case When t.status!="3" or t.status!="4" or t.status!="5" or t.status!="6" Then total_payable_amount Else 0 End) as unpaid_amount ');
				$countmodel = $countmodel->one();
				if($countmodel !== null){
					$total_paid_amount = $countmodel->paid_amount!==null?$countmodel->paid_amount:0;
					$total_unpaid_amount = $countmodel->unpaid_amount!==null?$countmodel->unpaid_amount:0;
				}
				
				if(isset($post['paymentFilter']) && $post['paymentFilter'] !='' && $modelInvoice->enumStatus['payment_received']==$post['paymentFilter'])
				{
					$total_unpaid_amount = 0;
				}				

				$totalCount = $model->count();
			}
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
			}
			else
			{
				$model = $model->orderBy(['t.id' => SORT_DESC]);
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
			foreach($model as $invoice)
			{
				$data=array();
				$data['id']=$invoice->id;
				$data['invoice_number']=$invoice->invoice_number;
				$invoiceType = $invoice->invoice_type;
				if($invoiceType==1 || $invoiceType==2)
				{
					$data['company_name']=$invoice->application->companyname;
					$data['address']=$invoice->application->address;
					$data['zipcode']=$invoice->application->zipcode;
					$data['city']=$invoice->application->city;
					$data['telephone']=$invoice->application->telephone;
					$data['email_address']=$invoice->application->emailaddress;	
					
					if($invoice->franchise && $invoice->franchise->usercompanyinfo)
					{
						$franchiseCompanyInfo = $invoice->franchise->usercompanyinfo;						
						$data['oss_company_name']=$franchiseCompanyInfo->company_name;											
					}
					
					if($invoice->hqfranchise && $invoice->hqfranchise->usercompanyinfo)
					{
						$franchiseCompanyInfo = $invoice->hqfranchise->usercompanyinfo;						
						$data['hq_oss_company_name']=$franchiseCompanyInfo->company_name;											
					}
					
					if($invoiceType==1)
					{
						$data['invoice_to']=$data['company_name'];
					}elseif($invoiceType==2){
						$data['invoice_to']=$data['oss_company_name'];
					}
					
				}elseif($invoiceType==3){					
					$userCompanyInfo = $invoice->customer->usercompanyinfo;
					$data['company_name']=$userCompanyInfo->company_name;					
					$data['telephone']=$userCompanyInfo->company_telephone;
					$data['email_address']=$userCompanyInfo->company_email;
					$data['invoice_to']=$data['company_name'];
					
					if($invoice->franchise && $invoice->franchise->usercompanyinfo)
					{
						$franchiseCompanyInfo = $invoice->franchise->usercompanyinfo;						
						$data['oss_company_name']=$franchiseCompanyInfo->company_name;											
					}
					
				}elseif($invoiceType==4){
					$franchiseCompanyInfo = $invoice->franchise->usercompanyinfo;
					$data['company_name']=$franchiseCompanyInfo->company_name;					
					$data['telephone']=$franchiseCompanyInfo->company_telephone;
					$data['email_address']=$franchiseCompanyInfo->company_email;
					$data['invoice_to']=$data['company_name'];
				}		
				
				$data['total_payable_amount']=$invoice->total_payable_amount;	
				$data['currency']=$invoice->currency_code;				
				
				//$data['created_at']=date($date_format,$invoice->created_at);
				//$data['payment_date']=$invoice->payment_status_date?date($date_format,strtotime($invoice->payment_status_date)):"NA";
				$data['payment_date']=$invoice->payment_date?date($date_format,strtotime($invoice->payment_date)):"NA";

				$data['credit_note_option']=$invoice->credit_note_option?$modelInvoice->arrCreditNoteOptions[$invoice->credit_note_option]:"NA";
				$data['invoice_status']=$invoice->status;
				
				$data['canUpdatePayment']=$this->canUpdatePaymentStatus($invoice->id);
				
				$data['invoice_status_name']=$invoice->arrStatus[$invoice->status];
				
				$stdArr = [];
				$invoicestandard = $invoice->invoicestandard;
				if(count($invoicestandard)>0){
					foreach($invoicestandard as $istandard){
						$stdArr[] = $istandard->standard->code;
					}
				}
				$data['standard_label']=implode(', ', $stdArr);
				
				$canGenerateInvoice = 0;
				if($invoice->status == $invoice->enumStatus['open'] || $invoice->status == $invoice->enumStatus['in-progress']){
					if($resource_access == 1){
						$canGenerateInvoice = 1;
					}
					if($user_type== Yii::$app->params['user_type']['franchise'] && ($invoicetype == 1 || $invoicetype == 3) ){
						$canGenerateInvoice = 1;
					}
					if($user_type== Yii::$app->params['user_type']['user'] && in_array('generate_invoice',$rules)  ){
						if($is_headquarters ==1){
							$canGenerateInvoice = 1;
						}else if($invoicetype ==1 || $invoicetype == 3){
							$canGenerateInvoice = 1;
						}
					}
				}
				if($invoice !== null && ($invoice->status <= $invoice->enumStatus['approval_in_process'] || $invoice->status == $invoice->enumStatus['payment_pending'] ) && $resource_access==1){
					$canGenerateInvoice = 1;
				}
				$data['canGenerateInvoice']= $canGenerateInvoice;
								
				$app_list[]=$data;
			}
		}
		
		// $canShowDetails['canshowpayment']
		//$canShowDetails['currencycode']

		$canShowDetails = $this->canShowFranchiseFilter();
		return ['invoices'=>$app_list,'total'=>$totalCount,
				'invoiceamount'=>['paid'=>$total_paid_amount,'unpaid'=>$total_unpaid_amount,'currency_code'=> 'USD',
				'show_franchise_filter' => $canShowDetails['canshowfilter'],'show_payment_details' => 1  ]
				];
	}
	private function canShowFranchiseFilter()
	{
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];		
		
		$post = Yii::$app->request->post();

		$currencycode = '';
		$canshowfilter = 0;
		$canshowpayment = 0;
		$canshowpaymentupdatebutton = 0;

		if($is_headquarters==1 || $resource_access==1)
		{
			if($post['franchiseFilter'] !=''){
				$canshowpayment = 1;
				$canshowpaymentupdatebutton = 1;
				$modelUser = User::find()->where(['id'=>$post['franchiseFilter']])->one();
				if($modelUser!==null){
					$currencycode = $modelUser->usercompanyinfo->mandaycost->currency_code;
				}
				
			}
			$canshowfilter = 1;
		}else if($user_type== 3 || $user_type== 2){
			$countryuserid=$userid;
			if($resource_access ==5){
				$countryuserid=$franchiseid;
			}
			$modelUser = User::find()->where(['id'=>$countryuserid])->one();
			if($modelUser!==null){
				$currencycode = $modelUser->usercompanyinfo->mandaycost->currency_code;
			}
			$canshowpayment = 1;
			$canshowpaymentupdatebutton = 1;
		}

		return ['canshowfilter'=>$canshowfilter,'canshowpayment'=>$canshowpayment,'currencycode'=>$currencycode];
	}
		
	public function actionGetFilterOption()
    {
		$modelInvoice = new Invoice();
		$paymentarr = array_slice($modelInvoice->arrStatus,-2,3,true);
		$filterpaymentarr = array_slice($modelInvoice->arrStatus,-3,3,true);

		return ['creditOptions'=>$modelInvoice->arrCreditNoteOptions,'paymentOptions'=>$paymentarr,'filterpaymentOptions'=>$filterpaymentarr];
	}
	
	public function actionViewInvoice()
	{	
		$resultarr=array();
		$data = Yii::$app->request->post();		
		if ($data) 
		{	
			$userrole = Yii::$app->userrole;
			$resource_access=$userrole->resource_access;
			
			$offerdata=array();
			$date_format = Yii::$app->globalfuns->getSettings('date_format');			
			$offermodel = Invoice::find()->alias('t')->where(['t.id' => $data['id']]);
			$offermodel = $offermodel->joinWith(['application as app']);
			if($resource_access != 1){			
				$chkinvoicemodel = Invoice::find()->alias('t')->where(['t.id' => $data['id']])->one();
				if($chkinvoicemodel!== null){
					$offermodel = $this->canViewInvoice($offermodel,$chkinvoicemodel->invoice_type);
				}else{
					return false;
				}		
			}
			$offermodel = $offermodel->one();
			
			if($offermodel!==null)
			{
				
				$resultarr['id'] = $offermodel->id;
				$resultarr['canUpdatePaymentStatus'] = $this->canUpdatePaymentStatus($offermodel->id);
				$resultarr['canGenerateInvoice'] = $this->canGenerateInvoice($offermodel->id,$offermodel->invoice_type);
				$resultarr['canDoInvoiceApproval'] = $this->canDoInvoiceApproval($offermodel->id);

				$resultarr['canSubmitForInvoiceApproval'] = $offermodel->status==$offermodel->enumStatus['in-progress']?1:0;
				
				$resultarr['app_id'] = $offermodel->app_id;

				$resultarr['enumStatus'] = $offermodel->enumStatus;
				$resultarr['offer_id'] = $offermodel->offer_id;
				$resultarr['audit_id'] = $offermodel->audit_id;
				$resultarr['customer_id'] = $offermodel->customer_id;
				$resultarr['credit_note_option'] = $offermodel->credit_note_option;
				$resultarr['franchise_id'] = $offermodel->franchise_id;
				$resultarr['hq_franchise_id'] = $offermodel->hq_franchise_id;

				$resultarr['invoice_discount'] = $offermodel->discount?:0;	
				
				// For Invoice View				
				$resultarr['invoice_number'] = $offermodel->invoice_number?:0;	
				$resultarr['invoice_grand_total_fee'] = $offermodel->grand_total_fee?:0;	
				$resultarr['invoice_tax_amount'] = $offermodel->tax_amount?:0;	
				$resultarr['invoice_total_payable_amount'] = $offermodel->total_payable_amount?:0;					
				
				$offerdata['grand_total_fee'] = $offermodel->grand_total_fee;
				
				$offerdata['currency'] = $offermodel->currency_code;
				$offerdata['currency_code'] = $offermodel->currency_code==''?'USD':$offermodel->currency_code;
				//$offermodel['conversion_currency_code'] = $offermodel->conversion_currency_code;
				//$offermodel['conversion_rate'] = $offermodel->conversion_rate;						
				//$offermodel['certification_fee_sub_total'] = $offermodel->certification_fee_sub_total;
				$offerdata['certification_fee_sub_total'] = 0;
				//$offermodel['other_expense_sub_total'] = $offermodel->other_expense_sub_total;
				$offerdata['other_expense_sub_total'] = 0;				
				
				$offerdata['total'] = $offermodel->total_fee;
				//$offermodel['total'] = $offermodel->total;
				$offerdata['gst_rate'] = $offermodel->tax_amount;
				$offerdata['total_payable_amount'] = $offermodel->total_payable_amount;
				//$offermodel['conversion_total_payable'] = $offermodel->conversion_total_payable;
				$resultarr['invoice_status']=$offermodel->status;
				$resultarr['invoice_status_name']=$offermodel->arrStatus[$offermodel->status];
				
				$resultarr['reject_comments']=$offermodel->reject_comment;				
				$resultarr['rejected_by']=$offermodel->rejectedby ? $offermodel->rejectedby->first_name.' '.$offermodel->rejectedby->last_name : '-';
				$resultarr['rejected_date']=date($date_format,strtotime($offermodel->rejected_date));
				

				$offerdata['conversion_required_status'] = $offermodel->conversion_required_status;
				$offerdata['conversion_rate'] = $offermodel->conversion_rate;
				$offerdata['conversion_currency'] = $offermodel->conversion_currency;
				$offerdata['conversion_currency_code'] = $offermodel->conversion_currency_code;
				$offerdata['conversion_total_fee'] = $offermodel->conversion_total_fee;
				$offerdata['conversion_tax_amount'] = $offermodel->conversion_tax_amount;
				$offerdata['conversion_total_payable_amount'] = $offermodel->conversion_total_payable_amount;
				

				//$offermodel['conversion_required_status'] = $offermodel->conversion_required_status;	
							
				// ----------- Get Company based OSS Details Code Start Here --------------
				if($offermodel->invoice_type==$offermodel->enumInvoiceType['initial_invoice_to_client'] || $offermodel->invoice_type==$offermodel->enumInvoiceType['additional_invoice_to_client'])
				{
					$franchisedetails = $offermodel->franchise;
					if($franchisedetails !== null){
						$resultarr['franchise_details']=Yii::$app->globalfuns->getFranchiseDetails($franchisedetails);				
					}
					
				}elseif($offermodel->invoice_type==$offermodel->enumInvoiceType['initial_invoice_to_oss'] || $offermodel->invoice_type==$offermodel->enumInvoiceType['additional_invoice_to_oss']){
					
					$userModel = new User();
					$userModel = $userModel->hqOSS();	//Get the HQ OSS Info				
					if($userModel !== null)
					{
						$resultarr['franchise_details']=Yii::$app->globalfuns->getFranchiseDetails($userModel);						
					}
				}					
				// ----------- Get Company based OSS Details Code End Here --------------
				
				$invoicedetails = $offermodel->invoicedetails;
				
				if(count($invoicedetails)>0)
				{
					$arrOE=array();
					$totalcertExpense = 0;					
					foreach($invoicedetails as $otherE)
					{
						$entryType='old';
						if($otherE->entry_type ==1)
						{
							$entryType='new';
						}
						$detailstandard = $otherE->detailstandard;
						$standardslabel = [];
						if(count($detailstandard)>0){
							foreach($detailstandard as $standarddata){
								$standardslabel[] = $standarddata->standard->code;
							}
						}else{
							$standardslabel = ['-'];
						}
						$arrOE=array('standard_label'=>implode('/',$standardslabel), 'activity'=>$otherE->activity,'description'=>$otherE->description,'amount'=>number_format($otherE->amount, 2, '.', ''),'entry_type'=>$entryType);
						$resultarr["offer_other_expenses"][]=$arrOE;							
						
					}
					//$resultarr["offer_other_expenses"][0] = array('activity'=>'Certification Fee','description'=> $offermodel->standard,'amount'=>number_format($totalcertExpense, 2, '.', ''),'entry_type'=>'old');
				}
								
				$offerdata['taxname'] = '';					
				$offerdata["tax_percentage"] = '';
					
				$invoicetax = $offermodel->invoicetax;
				if(count($invoicetax)>0)
				{
					$taxnameArray=array();
					$taxpercentage=0;
					foreach($invoicetax as $invoiceT)
					{
						$taxnameArray[]=$invoiceT->tax_name.' @ '.$invoiceT->tax_percentage.'%';
						$taxpercentage=$taxpercentage+$invoiceT->tax_percentage;
					}	
					$offerdata['taxname'] = implode(", ",$taxnameArray);					
					$offerdata["tax_percentage"]=$taxpercentage;
				}				
				$resultarr['offer']=$offerdata;					
			}else{
				$offerdetails['discount'] = 0;				
			}	
			
			$resultarr['oss_payment_details']=[];
			$Usermodel = User::find()->where(['id'=>$offermodel->franchise_id])->one();
			if($Usermodel !== null){
				$paymentdetails=$Usermodel->userpayment;
				if(count($paymentdetails)>0)
				{
					$arrpayment=[];
					foreach($paymentdetails as $pfields)
					{
						$osspaymentdata=[];
						$osspaymentdata['payment_label']=$pfields['payment_label'];
						$osspaymentdata['payment_content']=$pfields['payment_content'];
						$arrpayment[]=$osspaymentdata;
					}	
					$resultarr['oss_payment_details']=$arrpayment;		
				}
			}
			
			$invoicedata = Invoice::find()->where(['id'=>$data['id']])->one();
			$paymentDetails = [];
			if($invoicedata !== null && ($invoicedata->status == $invoicedata->enumStatus['payment_received'] || $invoicedata->status == $invoicedata->enumStatus['payment_cancelled'])){
				$paymentDetails = ['payment_status_id'=>$invoicedata->status,'payment_comment'=>$invoicedata->payment_comment,
						'payment_date'=>date($date_format,strtotime($invoicedata->payment_date)),
						'payment_status_date'=>date($date_format,strtotime($invoicedata->payment_status_date)),
								'payment_updated_by'=>$invoicedata->paymentupdatedby->first_name.' '.$invoicedata->paymentupdatedby->last_name];
			}else{
				$invoicedata=new Invoice();
			}
			$resultarr['paymentDetails'] = $paymentDetails;
			$resultarr['paymentStatusArr'] = array_slice($invoicedata->arrStatus,-2,3,true);
			return $resultarr;				
		}		
	}
		
    public function actionGenerate()
    {
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again later');
		if ($data) 
		{	

			$invoiceType=$data['type'];

			if($invoiceType == 1 || $invoiceType == 2){
				if(!$this->canGenerateInvoice($data['id'])){
					return false;
				}
			}else if($invoiceType == 3 || $invoiceType == 4){
				if(!$this->canGenerateInvoice('',$invoiceType,$data)){
					return false;
				}
				
			}
			
			
			
			$InvoiceStatus=0;
			$AdditionalInvoiceToClientStatus=0;
			$AdditionalInvoiceToOssStatus=0;
			$OldFranchiseID=0;
			if(isset($data['id']) && $data['id']!='')
			{
				$model = Invoice::find()->where(['id' => $data['id']])->one();
				if($model!==null)
				{
					$InvoiceStatus=1;					
					if($invoiceType==3 && $model->customer_id!=$data['customer_id']){
						$AdditionalInvoiceToClientStatus=1;	
						$OldFranchiseID=$model->franchise_id;
					}elseif($invoiceType==4 && $model->franchise_id!=$data['customer_id']){
						$AdditionalInvoiceToOssStatus=1;
						$OldFranchiseID=$model->hq_franchise_id;
					}
				}
			}

			//if($InvoiceStatus==0 || ($AdditionalInvoiceToClientStatus==1 || $AdditionalInvoiceToOssStatus==1))	
			//{
				if($InvoiceStatus==0)
				{
					$model=new Invoice();
				}
				
				// $maxid = Invoice::find()->max('id');
				// if(!empty($maxid)) 
				// {
				// 	$maxid = $maxid+1;				
				// 	$offercode="SY-".date("dmY")."-".$maxid;
				// }else{
				// 	$offercode="SY-".date("dmY")."-1";
				// }
				
				$franchiseObj = '';
				if($invoiceType==3 || $invoiceType==4)
				{
					$user_type=0;
					if($invoiceType==3){
						$user_type=2;
					}elseif($invoiceType==4){
						$user_type=3;
					}
				
					$modelUser = User::find()->where(['id'=>$data['customer_id'],'user_type'=>$user_type])->one();			
					if($modelUser!==null)
					{
						$ospid = 0;						
						if($invoiceType==3){					
							$ospid=$modelUser->franchise_id;
							$model->customer_id=$modelUser->id;
							$model->franchise_id=$ospid;
						}elseif($invoiceType==4){
							$ospid=$modelUser->id;
							$model->customer_id=0;
							$model->franchise_id=$ospid;
							
							$hqOSSmodel = $modelUser->hqOSS();	//Get the HQ OSS Info						
							if($hqOSSmodel !== null)
							{
								$model->hq_franchise_id = $hqOSSmodel->id;
							}						
						}
						
						$model->app_id=0;
						$franchiseObj = $modelUser;
						$model->invoice_type=$invoiceType;
					}

					$model->credit_note_option=$data['credit_note_option'];
				}					

				$invoiceCount = 0;
				$connection = Yii::$app->getDb();
				
				/*
				$command = $connection->createCommand("SELECT COUNT(invoice.id) AS invoice_count FROM `tbl_invoice` AS invoice
				INNER JOIN `tbl_offer` AS offer ON offer.id=invoice.offer_id
				INNER JOIN `tbl_application` AS app ON app.id = offer.app_id AND app.franchise_id='$ospid' 
				GROUP BY app.franchise_id");
				*/
				
				if($InvoiceStatus==0 || ($AdditionalInvoiceToClientStatus==1 && $model->franchise_id!=$OldFranchiseID) || ($AdditionalInvoiceToOssStatus==1 && $model->hq_franchise_id!=$OldFranchiseID))
				{
					$command = $connection->createCommand("SELECT COUNT(invoice.id) AS invoice_count FROM `tbl_invoice` AS invoice where invoice.franchise_id='".$ospid."'");
					$result = $command->queryOne();
					if($result  !== false)
					{
						$invoiceCount = $result['invoice_count'];
					}

					$maxid = $invoiceCount+1;
					if(strlen($maxid)=='1')
					{
						$maxid = "0".$maxid;
					}
					$invoicecode = "SY-".$franchiseObj->usercompanyinfo->osp_number."-".$maxid."/".date("Y");
					$model->invoice_number=$invoicecode;
					
					$model->currency_code = $franchiseObj->usercompanyinfo->mandaycost->currency_code;
				}				
				
			//}	

			//$model->app_id=$data['app_id'];
			
			//$model->offer_id=$data['offer_id'];
			$model->discount=$data['discount'];
			
			//$model->certification_fee_sub_total=$data['certification_fee_sub_total'];
			//$model->other_expense_sub_total=$data['other_expense_sub_total'];
			$model->total_fee=$data['total_fee'];
			$model->grand_total_fee=$data['grand_total_fee'];
			$model->tax_amount=$data['tax_amount'];
			$model->total_payable_amount=$data['total_payable_amount'];
			
			$model->conversion_total_payable_amount=$data['conversion_total_payable'];
			$model->conversion_required_status=$data['conversion_required_status'];
			$model->conversion_rate=$data['conversion_rate'];
			$model->currency_code=$data['currency'];
			$model->conversion_currency_code=$data['conversion_currency_code'];
			$model->conversion_currency=$data['conversion_currency_code'];
			if($model->status == $model->enumStatus['open'] || $model->status == ''){
				$model->status=$model->enumStatus['in-progress'];
			}
			
			$userData = Yii::$app->userdata->getData();
			$model->created_by=$userData['userid'];
			if($model->validate() && $model->save())
			{	$invoiceID = $model->id;			
				InvoiceDetails::deleteAll(['invoice_id' => $model->id,'entry_type'=>1]);
				if(is_array($data['other_expenses']) && count($data['other_expenses'])>0)
				{
					foreach ($data['other_expenses'] as $value)
					{ 
						if($value['entry_type']=='new')
						{
							$otherExpenses=new InvoiceDetails();
							$otherExpenses->invoice_id=$invoiceID;
							$otherExpenses->activity=$value['activity'];
							$otherExpenses->description=$value['description'];	
							$otherExpenses->amount=$value['amount'];
							$otherExpenses->entry_type = 1;													
							$otherExpenses->save();
						}	
					}
				}
				
				if($invoiceType==3 || $invoiceType==4)
				{
					InvoiceTax::deleteAll(['invoice_id' => $model->id]);
					
					$grandTotalFee = $model->grand_total_fee;
					//$invoicetax = $franchiseObj->usercompanyinfo->mandaycost->mandaycosttax;

					if($invoiceType==3){
						if($modelUser->usercompanyinfo && $modelUser->franchise && $modelUser->franchise->usercompanyinfo 
						&& $modelUser->usercompanyinfo->company_state_id == $modelUser->franchise->usercompanyinfo->company_state_id){
							$invoicetax = $franchiseObj->usercompanyinfo->mandaycost->mandaycosttax;
						}else{
							$invoicetax = $franchiseObj->usercompanyinfo->mandaycost->mandaycosttaxotherstate;
						}
					}else{
						$invoicetax = $franchiseObj->usercompanyinfo->mandaycost->mandaycosttax;
					}

					if(count($invoicetax)>0)
					{
						$taxnameArray=array();
						$taxpercentage=0;
						foreach($invoicetax as $invoiceT)
						{
							$TaxAmount=0;
							$TaxAmount=($grandTotalFee*$invoiceT->tax_percentage/100);
														
							$invoiceListTax=new InvoiceTax();
							$invoiceListTax->invoice_id=$invoiceID;							
							$invoiceListTax->tax_name=$invoiceT->tax_name;	
							$invoiceListTax->tax_percentage=$invoiceT->tax_percentage;
							$invoiceListTax->amount=$TaxAmount;							
							$invoiceListTax->save();
							
						}							
					}
				}
				
				/*
				$modelApplication=Application::find()->where(['id' => $model->app_id])->one();
				$modelApplication->overall_status = $modelApplication->arrEnumOverallStatus['invoice_in_process'];
				$modelApplication->save();
				*/		

				$responsedata=array('status'=>1,'message'=>'Invoice has been created successfully','id'=>$model->id);
			}
		}
		return $this->asJson($responsedata);
	}
	
	public function actionChangeinvoicestatus()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if(Yii::$app->request->post())
		{
			$data=Yii::$app->request->post();
			$userData = Yii::$app->userdata->getData();
			
			$model = Invoice::find()->where(['id'=>$data['invoice_id']])->one();
			$mailmsg = '';
			if($model !== null)
			{		
				if($data['invoicestatus']=='finalize' || $data['invoicestatus']=='reject'){
					if(!$this->canDoInvoiceApproval($model->id) || $model->status != $model->enumStatus['approval_in_process'] ){
						return false;
					}
				}					

				if($data['invoicestatus']=='approval'){
					if($model->status != $model->enumStatus['in-progress']){
						return false;
					}
					$status = $model->enumStatus['approval_in_process'];

					//$appmodel=Application::find()->where(['id' => $model->app_id])->one();
					//$appmodel->overall_status = $appmodel->arrEnumOverallStatus['in-progress'];
					//$appmodel->save();
				}else if($data['invoicestatus']=='finalize'){

					$html = $this->generateHTMLforPDF(array('id'=>$data['invoice_id']));


					$model->invoice_approved_date = date('Y-m-d');
					$model->invoice_due = date('Y-m-d',strtotime("+1 month"));

					$fileName = 'INVOICE_'.$model->id.'_'.date('Ymdhis').'.pdf';
					$filepath=Yii::$app->params['invoice_files'].$fileName;			

					$mpdf = new \Mpdf\Mpdf();
					$mpdf->WriteHTML($html);
					$mpdf->Output($filepath,'F');												
					$model->invoice_file=$fileName;
					$model->save();	


					//$model->invoice_approved_date = date('Y-m-d');
					//$model->invoice_due = date('Y-m-d',strtotime("+1 month"));
					//$model->save();
					$status = $model->enumStatus['payment_pending'];
					
					// ------------ Code for Generate PDF and Send to Respective User ----------------
					//$html = $this->generateHTMLforPDF(array('id'=>$data['invoice_id']));

					//$fileName = Yii::$app->params['temp_files'].'_invoice_'.date('Ymdhis').'.pdf';
					//$mpdf = new \Mpdf\Mpdf();
					//$mpdf->WriteHTML($html);
					//$mpdf->Output($fileName,'F');
					$files = json_encode([$filepath]);
					
					$invoiceType = $model->invoice_type;
					if($invoiceType==1 || $invoiceType==2)
					{
						$company_name=$model->application->companyname;
						$contact_name=$model->application->contactname;				
						$company_email_address=$model->application->emailaddress;						
					}elseif($invoiceType==3){					
						$companyInfo = $model->customer->usercompanyinfo;
						$company_name=$companyInfo->company_name;		
						$contact_name=$companyInfo->contact_name;					
						$company_email_address=$companyInfo->company_email;						
					}elseif($invoiceType==4){
						$companyInfo = $model->franchise->usercompanyinfo;						
						$company_name=$companyInfo->company_name;	
						$contact_name=$companyInfo->contact_name;				
						$company_email_address=$companyInfo->company_email;						
					}
					
					$mailContent = MailNotifications::find()->select('code,subject,message')->where(['code' => 'invoice_approved'])->one();
					$mailmsg=str_replace('{USERNAME}', $company_name, $mailContent['message'] );					
					$mail_notification_code = $mailContent->code;					
					
					$MailLookupModel = new MailLookup();
					$MailLookupModel->to=$company_email_address;					
					$MailLookupModel->subject=$mailContent['subject'];
					$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
					$MailLookupModel->attachment=$files;
					$MailLookupModel->mail_notification_id='';
					$MailLookupModel->mail_notification_code=$mail_notification_code;
					$Mailres=$MailLookupModel->sendMail();
					// ------------ Code for Generate PDF and Send to Respective User ----------------					
					
					//$appmodel=Application::find()->where(['id' => $model->app_id])->one();
					//$appmodel->overall_status = $appmodel->arrEnumOverallStatus['invoice_completed'];
					//$appmodel->save();
				}else if($data['invoicestatus']=='reject'){
					$status = $model->enumStatus['rejected'];					
					$model->reject_comment = $data['comment'];					
					$model->rejected_by = $userData['userid'];
					$model->rejected_date = date('Y-m-d H:i:s');

					//$appmodel=Application::find()->where(['id' => $model->app_id])->one();
					//$appmodel->overall_status = $appmodel->arrEnumOverallStatus['invoice_rejected'];
					//$appmodel->save();
				}
				$model->status=$status;				
				$model->updated_by=$userData['userid'];
				if($model->save()){
					$responsedata=array('status'=>1,'message'=>'Updated Successfully','invoice_status'=>$status );
				}
			}
		}
		return $responsedata;
	}
	
	public function actionCreate()
	{
		$data = Yii::$app->request->post();
		if($data)
		{	
			$userrole = Yii::$app->userrole;
			$resource_access=$userrole->resource_access;			
			$invoiceModel = Invoice::find()->alias('t')->where(['t.id' => $data['id']]);
			$invoiceModel = $invoiceModel->joinWith(['application as app']);
			if($resource_access != 1){
				$chkinvoicemodel = Invoice::find()->alias('t')->where(['t.id' => $data['id']])->one();
				if($chkinvoicemodel!== null){
					$invoiceModel = $this->canViewInvoice($invoiceModel,$chkinvoicemodel->invoice_type);
				}else{
					return false;
				}

				
			}
			$invoiceModel = $invoiceModel->one();
			if($invoiceModel!==null)
			{		
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
				header('Access-Control-Max-Age: 1000');
				header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');	

				if($invoiceModel->invoice_file){
					$filepath=Yii::$app->params['invoice_files'].$invoiceModel->invoice_file;
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
				}else{
					$html = $this->generateHTMLforPDF($data);			
					$mpdf = new \Mpdf\Mpdf(['default_font' => 'arial']);
					$mpdf->WriteHTML($html);
					$mpdf->Output();
				}
					
				
			}else{
				$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
				return $responsedata;
			}	
		}			
	}
	
	public function generateHTMLforPDF($data)
	{
		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');

		$usermodel = User::find()->where(['id' => $userData['userid']])->one();
		$modelUser = new User();
					
		$invoicedetails=Invoice::find()->where(['id' => $data['id']])->one();
		$appdetails = $invoicedetails->application;
		$customermodel = null;
		if($appdetails!==null){
			$customermodel = User::find()->where(['id' => $appdetails->customer_id ])->one();
		}
		
		$arrFranchiseDetails=array();			
		if($invoicedetails->invoice_type==$invoicedetails->enumInvoiceType['initial_invoice_to_client'] || $invoicedetails->invoice_type==$invoicedetails->enumInvoiceType['additional_invoice_to_client'])
		{
			$franchisedetails = $invoicedetails->franchise;
			if($franchisedetails !== null)
			{
				$arrFranchiseDetails=Yii::$app->globalfuns->getFranchiseDetails($franchisedetails);				
			}				
		}elseif($invoicedetails->invoice_type==$invoicedetails->enumInvoiceType['initial_invoice_to_oss'] || $invoicedetails->invoice_type==$invoicedetails->enumInvoiceType['additional_invoice_to_oss']){
			
			$userModelF = $modelUser->hqOSS();	//Get the HQ OSS Info					
			if($userModelF !== null)
			{
				$arrFranchiseDetails=Yii::$app->globalfuns->getFranchiseDetails($userModelF);						
			}
		}			

		//header('Access-Control-Allow-Origin: *');
		//header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		//header('Access-Control-Max-Age: 1000');
		//header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		$html='';
		//$mpdf = new \Mpdf\Mpdf(['default_font' => 'arial']);
					
		$invoiceType = $invoicedetails->invoice_type;
		if($invoiceType==1 || $invoiceType==2)
		{
			$company_name=$invoicedetails->application->companyname;
			$contact_name=$invoicedetails->application->contactname;				
			$company_address=$invoicedetails->application->address;
			$company_zipcode=$invoicedetails->application->zipcode;
			$company_city=$invoicedetails->application->city;
			$company_telephone=$invoicedetails->application->telephone;
			$company_email_address=$invoicedetails->application->emailaddress;
			$company_gst_no=$invoicedetails->application->tax_no ? $invoicedetails->application->tax_no:'-';
			if($invoiceType==1)
			{
				$customer_number = $invoicedetails->application->customer->customer_number;
			}elseif($invoiceType==2){
				$franchisedetails = $invoicedetails->franchise;
				$arrFranchiseDetails=Yii::$app->globalfuns->getFranchiseDetails($franchisedetails);	
				
				return $html = $this->generateOssInvoice($arrFranchiseDetails,$invoicedetails);
			}
		}elseif($invoiceType==3){					
			$companyInfo = $invoicedetails->customer->usercompanyinfo;
			$company_name=$companyInfo->company_name;		
			$contact_name=$companyInfo->contact_name;				
			$company_address=$companyInfo->company_address1;
			if($company_address!='')
			{
				$company_address.=', ';
				$company_address.=$companyInfo->company_address2;
			}
			$company_zipcode=$companyInfo->company_zipcode;
			$company_city=$companyInfo->company_city;
			$company_telephone=$companyInfo->company_telephone;
			$company_email_address=$companyInfo->company_email;
			$company_gst_no=$companyInfo->gst_no ? $companyInfo->gst_no : '-';
			$customer_number = ($invoicedetails->customer?$invoicedetails->customer->customer_number:"-");			
		}elseif($invoiceType==4){
			$franchisedetails = $invoicedetails->franchise;
			//$userModelF = $modelUser->hqOSS();
			$arrFranchiseDetails=Yii::$app->globalfuns->getFranchiseDetails($franchisedetails);	
			
			return $html = $this->generateOssInvoice($arrFranchiseDetails,$invoicedetails);
			/*
			$companyInfo = $invoicedetails->franchise->usercompanyinfo;
			
			$company_name=$companyInfo->company_name;	
			$contact_name=$companyInfo->contact_name;				
			$company_address=$companyInfo->company_address1;
			if($company_address!='')
			{
				$company_address.=', ';
				$company_address.=$companyInfo->company_address2;
			}
			$company_zipcode=$companyInfo->company_zipcode;
			$company_city=$companyInfo->company_city;
			$company_telephone=$companyInfo->company_telephone;
			$company_email_address=$companyInfo->company_email;
			$company_gst_no=$companyInfo->gst_no ? $companyInfo->gst_no : '-';	
			$customer_number = $companyInfo->osp_number;
			*/
		}	

		$html='<style>
			table {
				border-collapse: collapse;
			}		
			
			table, tbody, tr, th, td{
				background-color: rgba(0, 0, 0, 0.0) !important;
			}
			
			table.reportDetailLayout {
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
				font-size:12px;
				font-family:Arial;
				text-align: left;
				/*background-color:#DFE8F6;*/
				padding:3px;
			}
			
			td.reportDetailLayoutHead {
				text-align: center;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				/*background-color:#4e85c8;*/
				/* background-color:#006fc0;*/
				padding:3px;
				color:#FFFFFF;
			}

			td.reportDetailLayoutInner {
				text-align: center;
				font-size:12px;
				font-family:Arial;
				text-align: left;
			}	
			
			table.productDetails {
				border-collapse: collapse;
				border: 1px solid #000000;
				width:100%;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				margin-bottom:5px;
				margin-top:5px;
			}

			td.productDetails {
				text-align: center;
				border: 1px solid #000000;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				/*background-color:#DFE8F6;*/
				padding:3px;
			}
			</style>
			
			<div style="font-size:18px;color:#2f4a81;text-align: center;width:100%;margin-bottom:20px;font-family:Arial;"><b>Invoice</b></div>
			
			<table cellpadding="0" cellspacing="0" border="0" class="reportDetailLayout" width="100%">
				<tr>
					<td width="70%" style="text-align:left;padding-top:12px;margin-bottom:0px;font-size:18px;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">
						'.$arrFranchiseDetails['company_name'].'
					</td>
					<td width="30%" rowspan="2" style="text-align:right;">
						<img src="'.Yii::$app->params['image_files'].'header-img.png" border="0" style="width:186px;">
					</td>
				</tr>
				<tr>
					<td class="reportDetailLayoutInner" style="text-align:left;padding-top:24px;margin-bottom:0px;padding-bottom:3px;line-height:18px;" valign="middle">
					'.$arrFranchiseDetails['address'].', '.$arrFranchiseDetails['city'].' - '.$arrFranchiseDetails['zipcode'].', '.$arrFranchiseDetails['state'].', '.$arrFranchiseDetails['country'].'<br>
					e-mail :'.$arrFranchiseDetails['email'].',&nbsp;&nbsp;Ph. '.($arrFranchiseDetails['telephone'] ? $arrFranchiseDetails['telephone'] : '-').',&nbsp;&nbsp;Mobile: '.($arrFranchiseDetails['mobile'] ? $arrFranchiseDetails['mobile'] : '-').'<br>
					GST No.&nbsp;&nbsp;'.($arrFranchiseDetails['gst_no'] ? $arrFranchiseDetails['gst_no'] : '-').'
					</td>
				</tr>				
			</table>			
							
			<table cellpadding="0" cellspacing="0" border="0" class="productDetails">
				<tr>
					<td colspan="4" class="productDetails">Operator Name : '.$company_name.'</td>
				</tr>
				<tr>	
					<td colspan="4" class="productDetails">
					
						<table cellpadding="0" cellspacing="0" border="0" class="reportDetailLayout">
							
							<tr>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner" width="15%">Operator ID</td>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner" width="2%">:</td>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner" width="61%">'.$customer_number.'</td>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner" width="10%">Date</td>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner" width="2%">:</td>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner" width="12%">'.date($date_format).'</td>
							</tr>
							
							<tr>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">Address</td>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">:</td>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">'.$company_address.','.$company_city.'-'.$company_zipcode.'</td>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">Invoice No</td>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">:</td>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">'.$invoicedetails->invoice_number.'</td>
							</tr>							
							
							<tr>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">Contact Person</td>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">:</td>
								<td colspan="4" style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">'.$contact_name.'</td>																		
							</tr>
							
							<tr>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">Mail Id</td>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">:</td>
								<td colspan="4" style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">'.$company_email_address.'</td>																		
							</tr>
							
							<tr>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">Phone No.</td>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">:</td>
								<td colspan="4" style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">'.$company_telephone.'</td>																		
							</tr>
							
							<tr>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">GST No.</td>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">:</td>
								<td colspan="4" style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">'.$company_gst_no.'</td>																		
							</tr>
							
							<tr>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">Place of supply</td>
								<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">:</td>
								<td colspan="4" style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">'.$arrFranchiseDetails['city'].'</td>																		
							</tr>							
							
						</table>													
						
					</td>
					
				</tr>
				
				<tr>	
					<td class="productDetails" style="text-align:center;font-weight:bold;">S.No</td>
					<td class="productDetails" style="text-align:center;font-weight:bold;">Description</td>
					<td class="productDetails" style="text-align:center;font-weight:bold;">Standard(s)</td>
					<td class="productDetails" style="text-align:center;font-weight:bold;">Amount in '.$invoicedetails->currency_code.'</td>
				</tr>';
				
				$inedetails = $invoicedetails->invoicedetails;
				if(count($inedetails)>0)
				{
					$invoiceCnt=1;
					$arrOE=array();										
					foreach($inedetails as $otherE)
					{

						$detailstandard = $otherE->detailstandard;
						$standardslabel = [];
						if(count($detailstandard)>0){
							foreach($detailstandard as $standarddata){
								$standardslabel[] = $standarddata->standard->code;
							}
						}else{
							$standardslabel = ['-'];
						}
						$standardlabelimploded = implode('/',$standardslabel);
						
						$html.='<tr>	
							<td class="productDetails" style="text-align:center;">'.$invoiceCnt.'</td>
							<td class="productDetails" style="text-align:center;">'.$otherE->description.'</td>
							<td class="productDetails" style="text-align:center;">'.$standardlabelimploded.'</td>
							<td class="productDetails" style="text-align:right;">'.number_format($otherE->amount, 2, '.', '').'</td>
						</tr>';
						$invoiceCnt++;
					}
				}
				
				$html.='
				<tr>
					<td colspan="3" class="productDetails" style="text-align:right;font-weight:bold;">Total Value</td>
					<td class="productDetails" style="text-align:right;font-weight:bold;">'.$invoicedetails->total_fee.'</td>						
				</tr>';
				
				if($invoicedetails->discount>0)
				{
					$html.='
					<tr>
						<td colspan="3" class="productDetails" style="text-align:right;font-weight:bold;">Discount</td>
						<td class="productDetails" style="text-align:right;font-weight:bold;">'.$invoicedetails->discount.'</td>						
					</tr>';
					
					$html.='
					<tr>
						<td colspan="3" class="productDetails" style="text-align:right;font-weight:bold;">Grand Total Value</td>
						<td class="productDetails" style="text-align:right;font-weight:bold;">'.$invoicedetails->grand_total_fee.'</td>						
					</tr>';						
				}
				
				$invoicetax = $invoicedetails->invoicetax;
				if(count($invoicetax)>0)
				{
					$invoiceTaxCnt=0;						
					foreach($invoicetax as $invoiceT)
					{														
						if($invoiceTaxCnt==0)
						{
							$html.='<tr>	
								<td rowspan="'.count($invoicetax).'" colspan="2" class="productDetails" style="text-align:center;"></td>
								<td class="productDetails" style="text-align:right;">Add '.$invoiceT->tax_name.' @ '.$invoiceT->tax_percentage.'%</td>								
								<td class="productDetails" style="text-align:right;">'.number_format($invoiceT->amount, 2, '.', '').'</td>
							</tr>';
						}else{
							$html.='<tr>										
								<td class="productDetails" style="text-align:right;">Add '.$invoiceT->tax_name.' @ '.$invoiceT->tax_percentage.'%</td>								
								<td class="productDetails" style="text-align:right;">'.number_format($invoiceT->amount, 2, '.', '').'</td>
							</tr>';
						}
						$invoiceTaxCnt++;
					}							
				}
				
			$html.='	
				<tr>
					<td colspan="3" class="productDetails" style="text-align:right;font-weight:bold;">Total Payable Value</td>
					<td class="productDetails" style="text-align:right;font-weight:bold;">'.$invoicedetails->total_payable_amount.'</td>						
				</tr>
				<tr>
					<td colspan="3" class="productDetails" style="text-align:right;font-weight:bold;">In Round</td>
					<td class="productDetails" style="text-align:right;font-weight:bold;">'.round($invoicedetails->total_payable_amount).'</td>						
				</tr>';

				if($invoicedetails->conversion_required_status==1)
				{
					$html.='
					<tr>
						<td colspan="3" align="center" style="text-align:center;font-weight:bold;" valign="middle" class="productDetails">&nbsp;</td>
						<td align="right" style="text-align:right;font-weight:bold;" valign="middle" class="productDetails">'.$invoicedetails->conversion_currency_code." ".$invoicedetails->conversion_total_payable_amount.'</td>
					</tr>';	
				}	

			$html.='</table>';				
			
			/*
			<br>
			<img src="'.Yii::$app->params['site_path'].'backend/web/images/sign.png" border="0" style="width:25%;"><br>Authorised Signature
			*/	
			
			$html.='<div style="font-family:Arial;font-size:12px;width:100%;text-align:right;font-weight:bold;margin-top:5px;">
				For GCL International Assessment Pvt Ltd.
			</div>
			<div style="width:100%;margin-top:5px;">
			
				<table cellpadding="0" cellspacing="0" border="0" class="reportDetailLayout">
					<tr >
						<td colspan="2" style="font-weight:bold;" class="reportDetailLayoutInner"><u>Payment Details</u></td>
					</tr>';
					$Usermodel = User::find()->where(['id'=>$invoicedetails->franchise_id])->one();
					if($Usermodel !== null){
						$paymentdetails=$Usermodel->userpayment;
						if(count($paymentdetails)>0)
						{
							$arrpayment=[];
							foreach($paymentdetails as $pfields)
							{
								$html.='
								<tr>
									<td style="text-align:left;padding-top:10px;margin-bottom:0px;" valign="middle" width="30%" class="reportDetailLayoutInner">'.$pfields['payment_label'].'</td>
									<td style="text-align:left;padding-top:10pxpx;margin-bottom:0px;" valign="middle" class="reportDetailLayoutInner">'.$pfields['payment_content'].'</td>
								</tr>';
							}	
							$resultarr['oss_payment_details']=$arrpayment;		
						}
					}
					
				$html.='</table>	
			</div>';
		return $html;		
	}

	private function getIndianCurrency($number)
	{
		$decimal = round($number - ($no = floor($number)), 2) * 100;
		$hundred = null;
		$digits_length = strlen($no);
		$i = 0;
		$str = array();
		$words = array(0 => '', 1 => 'ONE', 2 => 'TWO',
			3 => 'THREE', 4 => 'FOUR', 5 => 'FIVE', 6 => 'SIX',
			7 => 'SEVEN', 8 => 'EIGHT', 9 => 'NINE',
			10 => 'TEN', 11 => 'ELEVEN', 12 => 'TWELVE',
			13 => 'THIRTEEN', 14 => 'FOURTEEN', 15 => 'FIFTEEN',
			16 => 'SIXTEEN', 17 => 'SEVENTEEN', 18 => 'EIGHTEEN',
			19 => 'NINETEEN', 20 => 'TWENTY', 30 => 'THIRTY',
			40 => 'FORTY', 50 => 'FIFTY', 60 => 'SIXTY',
			70 => 'SEVENTY', 80 => 'EIGHTY', 90 => 'NINETY');
		$digits = array('', 'HUNDRED','THOUSAND','LAKH', 'CRORE');
		while( $i < $digits_length ) {
			$divider = ($i == 2) ? 10 : 100;
			$number = floor($no % $divider);
			$no = floor($no / $divider);
			$i += $divider == 10 ? 1 : 2;
			if ($number) {
				$plural = (($counter = count($str)) && $number > 9) ? 's' : null;
				$hundred = ($counter == 1 && $str[0]) ? ' AND ' : null;
				$str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
			} else $str[] = null;
		}
		$Rupees = implode('', array_reverse($str));
		$paise = ($decimal > 0) ? " " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' PAISE' : '';
		return ($Rupees ? $Rupees . 'RUPEES ' : '') . $paise;
	}	
	
	public function actionUpdatePayment(){
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data=Yii::$app->request->post();
		if($data)
		{			
			$userData = Yii::$app->userdata->getData();
			if(!$this->canUpdatePaymentStatus($data['invoice_id'])){
				return false;
			}
			$InvoiceModel =new Invoice();
			$model = Invoice::find()->where(['id'=>$data['invoice_id'],'status'=>$InvoiceModel->enumStatus['payment_pending']])->one();
			
			if($model !== null)
			{
				$model->payment_status=$data['payment_status'];
				$model->payment_comment=$data['payment_comment'];
				$model->payment_updated_by=$userData['userid'];
				$model->payment_status_date = date('Y-m-d H:i:s');				
				if($model->save()){
					$responsedata=array('status'=>1,'message'=>'Payment Updated Successfully');
				}
			}
		}
		return $responsedata;
	}

	public function actionPaymentUpdate()
	{	
		$modelInvoice = new Invoice();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data=Yii::$app->request->post();
		if($data)
		{
			$InvoiceModel = new Invoice();
			$userData = Yii::$app->userdata->getData();			
			if(is_array($data['id']) && count($data['id'])>0)
			{
				foreach ($data['id'] as $value)
				{ 
					$model = Invoice::find()->where(['id'=>$value,'status'=>$InvoiceModel->enumStatus['payment_pending']])->one();
					
					if($model !== null)
					{
						if(!$this->canUpdatePaymentStatus($model->id)){
							return false;
						}
						
						$model->status = $data['payment_status'];
						$model->payment_date=date('Y-m-d',strtotime($data['payment_date']));
						$model->payment_comment=$data['payment_comment'];
						$model->payment_updated_by=$userData['userid'];
						$model->payment_status_date = date('Y-m-d H:i:s');

						if($model->save()){
							$responsedata=array('status'=>1,'message'=>'Payments Updated Successfully');
						}
					}
				}
			}
			else
			{
				$model = Invoice::find()->where(['id'=>$data['id']])->one();			
				if($model !== null)
				{
					if(!$this->canUpdatePaymentStatus($model->id)){
						return false;
					}
					$model->status = $data['payment_status'];
					$model->payment_date=date('Y-m-d',strtotime($data['payment_date']));
					$model->payment_comment=$data['payment_comment'];
					$model->payment_updated_by=$userData['userid'];
					$model->payment_status_date = date('Y-m-d H:i:s');

					if($model->save()){
						$responsedata=array('status'=>1,'message'=>'Payment Updated Successfully');
					}
				}
			}			
		}
		return $responsedata;
	}
	
	public function actionGetAdditionalInvoice()
	{
		$data=Yii::$app->request->post();
		if($data)
		{
			$invoiceModel = new Invoice();
			$type = $data['type'];			
			$user_type=0;
			if($type==3)
			{
				$user_type=2;
			}elseif($type==4){
				$user_type=3;
			}
			
			$modelUser = User::find()->where(['id'=>$data['id'],'user_type'=>$user_type])->one();			
			if($modelUser!==null)
			{
				$offerdata=array();
				
				$resultarr['invoice_discount'] = 0;	
				
				// For Invoice View				
				$resultarr['invoice_number'] = 0;	
				$resultarr['invoice_grand_total_fee'] = 0;	
				$resultarr['invoice_tax_amount'] = 0;	
				$resultarr['invoice_total_payable_amount'] = 0;					
				
				$offerdata['grand_total_fee'] = 0;
				
				//$offerdata['currency'] = $modelUser->usercompanyinfo->mandaycost->currency_code;
				$offerdata['currency'] = 'USD';
				$offerdata['currency_code'] = 'USD';
				$offerdata['conversion_rate'] = '';

				$offerdata['certification_fee_sub_total'] = 0;				
				$offerdata['other_expense_sub_total'] = 0;				
				
				$offerdata['total'] = 0;				
				$offerdata['gst_rate'] = 0;
				$offerdata['total_payable_amount'] = 0;	
				$resultarr["offer_other_expenses"]=array();				
				// ----------- Get Company based OSS Details Code Start Here --------------
				
				if($type==$invoiceModel->enumInvoiceType['additional_invoice_to_oss']){
					
					$userModel = $modelUser->hqOSS();	//Get the HQ OSS Info						
					if($userModel !== null)
					{
						$resultarr['franchise_details']=Yii::$app->globalfuns->getFranchiseDetails($userModel);						
					}
				}					
				// ----------- Get Company based OSS Details Code End Here --------------
											
				$offerdata['taxname'] = '';					
				$offerdata["tax_percentage"] = '';
				if($type==3)
				{
					//$franchisesamestate = 0;
					if($modelUser->usercompanyinfo && $modelUser->franchise && $modelUser->franchise->usercompanyinfo 
					&& $modelUser->usercompanyinfo->company_state_id == $modelUser->franchise->usercompanyinfo->company_state_id){
						$invoicetax = $modelUser->usercompanyinfo->mandaycost->mandaycosttax;
					}else{
						$invoicetax = $modelUser->usercompanyinfo->mandaycost->mandaycosttaxotherstate;
					}
				}else{
					$invoicetax = $modelUser->usercompanyinfo->mandaycost->mandaycosttax;
				}
				
				
				if(count($invoicetax)>0)
				{
					$taxnameArray=array();
					$taxpercentage=0;
					foreach($invoicetax as $invoiceT)
					{
						$taxnameArray[]=$invoiceT->tax_name.' @ '.$invoiceT->tax_percentage.'%';
						$taxpercentage=$taxpercentage+$invoiceT->tax_percentage;
					}	
					$offerdata['taxname'] = implode(", ",$taxnameArray);					
					$offerdata["tax_percentage"]=$taxpercentage;
				}
				
				$resultarr['offer']=$offerdata;					
					
				$franchiseID=0;				
				if($type==3){					
					$franchiseID=$modelUser->franchise_id;
				}elseif($type==4){
					$franchiseID=$modelUser->id;
				}
				
				$resultarr['oss_payment_details']=[];
				$Usermodel = User::find()->where(['id'=>$franchiseID,'user_type'=>3])->one();
				if($Usermodel !== null)
				{
					if($type==$invoiceModel->enumInvoiceType['additional_invoice_to_client'])
					{
						$resultarr['franchise_details']=Yii::$app->globalfuns->getFranchiseDetails($Usermodel);									
					}
					
					$paymentdetails=$Usermodel->userpayment;
					if(count($paymentdetails)>0)
					{
						$arrpayment=[];
						foreach($paymentdetails as $pfields)
						{
							$osspaymentdata=[];
							$osspaymentdata['payment_label']=$pfields['payment_label'];
							$osspaymentdata['payment_content']=$pfields['payment_content'];
							$arrpayment[]=$osspaymentdata;
						}	
						$resultarr['oss_payment_details']=$arrpayment;		
					}
				}
				return $resultarr;
			}
		}
	}

	public function canUpdatePaymentStatus($invoice_id)
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

		$canUpdatePayment = 0;
		$InvoiceModel = new Invoice();
		$Invoice = Invoice::find()->where(['id'=>$invoice_id,'status'=>$InvoiceModel->enumStatus['payment_pending'] ])->one();
		if($Invoice !== null){
			if($Invoice->status == 4){
				//if($Invoice->payment_status != 2){
				if($resource_access==1){
					$canUpdatePayment = 1;
				}
				if($user_type == Yii::$app->params['user_type']['user'] &&  in_array('update_invoice_payment_status',$rules)){
					if($is_headquarters ==1){
						$canUpdatePayment = 1;
					}else if($Invoice->franchise_id == $franchiseid && ($Invoice->invoice_type == $Invoice->enumInvoiceType['initial_invoice_to_client'] || $Invoice->invoice_type == $Invoice->enumInvoiceType['additional_invoice_to_client'])){
						$canUpdatePayment = 1;
					}
				}
				if($Invoice->invoice_type == $Invoice->enumInvoiceType['initial_invoice_to_client'] || $Invoice->invoice_type == $Invoice->enumInvoiceType['additional_invoice_to_client']){
					if($is_headquarters ==1){
						$canUpdatePayment = 1;
					}

					if( $user_type == 3 ){
						if($resource_access == 5 && $Invoice->franchise_id == $franchiseid){
							$canUpdatePayment = 1;
						}else if($Invoice->franchise_id == $userid){
							$canUpdatePayment = 1;
						}
					}
				}				
				//}
			}
		}
		return $canUpdatePayment;		
	}

	public function canGenerateInvoice($invoice_id='',$invoice_type='',$postdata=[])
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

		$canGenerateInvoice = 0;
		$invoice = null;
		if($invoice_id !=''){
			$invoice = Invoice::find()->where(['id'=>$invoice_id])->one();
		}
		


		if($invoice_id !='' && $invoice !== null && ($invoice->invoice_type ==1 || $invoice->invoice_type ==2)){
			//$invoice = Invoice::find()->where(['id'=>$invoice_id])->one();
			if($invoice !== null){
				
				if($invoice->status == $invoice->enumStatus['open'] || $invoice->status == $invoice->enumStatus['in-progress']){
					if($resource_access == 1){
						$canGenerateInvoice = 1;
					}else{
						if($user_type== Yii::$app->params['user_type']['franchise'] && ($invoice->invoice_type == 1 || $invoice->invoice_type == 3) ){
							if($is_headquarters ==1){
								$canGenerateInvoice = 1;
							}else{
								if($resource_access == 5 && $invoice->application->franchise_id == $franchiseid) {
									$canGenerateInvoice = 1;
								}else if($invoice->application->franchise_id == $userid){
									$canGenerateInvoice = 1;
								}
								
							}
						}
						if($user_type== Yii::$app->params['user_type']['user'] && in_array('generate_invoice',$rules)  ){
							if($is_headquarters ==1){
								$canGenerateInvoice = 1;
							}else if($invoice->invoice_type ==1 || $invoice->invoice_type == 3){
								if($invoice->application->franchise_id == $franchiseid) {
									$canGenerateInvoice = 1;
								}
							}
						}
					}
					
				}
			}
		}else if($invoice_type == 3 || $invoice_type == 4){
			$cangeneratebystatus = 0;
			
			if($invoice == null || $invoice->status == $invoice->enumStatus['open'] || $invoice->status == $invoice->enumStatus['in-progress']){
				$cangeneratebystatus = 1;
			}
			
			if($invoice_type==3){
				$chkuser_type=2;
				
				if(isset($postdata['customer_id'])){
					$customer_id = $postdata['customer_id'];
				}else{
					$customer_id = $invoice->customer_id;
				}
				$modelUser = User::find()->where(['id'=>$customer_id,'user_type'=>$chkuser_type])->one();			
				if($modelUser!==null)
				{
					if($resource_access == 1){
						$canGenerateInvoice = 1;
					}else{
						if($is_headquarters ==1){
							if($user_type== Yii::$app->params['user_type']['user'] && in_array('generate_invoice',$rules)){
								$canGenerateInvoice = 1;
							}else if($user_type== Yii::$app->params['user_type']['franchise']){
								$canGenerateInvoice = 1;
							}
						}else{
							
							if($user_type== Yii::$app->params['user_type']['user'] && $modelUser->franchise_id == $franchiseid && in_array('generate_invoice',$rules)){
								$canGenerateInvoice = 1;
							}else if($user_type== Yii::$app->params['user_type']['franchise']){
								if($resource_access == 5 && $modelUser->franchise_id == $franchiseid) {
									$canGenerateInvoice = 1;
								}else if($modelUser->franchise_id == $userid){
									$canGenerateInvoice = 1;
								}
							}
						}
					}
					
					
				}
			}elseif($invoice_type==4){
				$chkuser_type=3;

				
				if(isset($postdata['customer_id'])){
					$customer_id = $postdata['customer_id'];
				}else{
					$customer_id = $invoice->franchise_id;
				}

				$modelUser = User::find()->where(['id'=>$customer_id,'user_type'=>$chkuser_type])->one();			
				if($modelUser!==null)
				{
					if($resource_access == 1){
						$canGenerateInvoice = 1;
					}else{
						if($is_headquarters ==1){
							if($user_type== Yii::$app->params['user_type']['user'] && in_array('generate_invoice',$rules)){
								$canGenerateInvoice = 1;
							}else if($user_type== Yii::$app->params['user_type']['franchise']){
								$canGenerateInvoice = 1;
							}
						}	
					}
									
				}
			}
			
			if($cangeneratebystatus && $canGenerateInvoice == 1){
				$canGenerateInvoice = 1;
			}else{
				$canGenerateInvoice = 0;
			}
			
		}
		if($invoice !== null && ($invoice->status <= $invoice->enumStatus['approval_in_process'] || $invoice->status == $invoice->enumStatus['payment_pending'] ) && $resource_access==1){
			$canGenerateInvoice = 1;
		}
		return $canGenerateInvoice;
	}

	public function canDoInvoiceApproval($invoice_id)
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

		$canDoInvoiceApproval = 0;
		$invoice = Invoice::find()->where(['id'=>$invoice_id])->one();
		if($invoice !== null){
			
			if($invoice->status == $invoice->enumStatus['approval_in_process']){
				if($resource_access == 1){
					$canDoInvoiceApproval = 1;
				}else if($user_type== Yii::$app->params['user_type']['user'] && in_array('invoice_approvals',$rules)  ){
					if($is_headquarters ==1){
						$canDoInvoiceApproval = 1;
					}else if($invoice->franchise_id == $franchiseid ){
						$canDoInvoiceApproval = 1;
					}
				}else if($user_type== Yii::$app->params['user_type']['franchise']  && ($invoice->invoice_type == 1 || $invoice->invoice_type == 3)  ){
					if($resource_access ==5 && $invoice->franchise_id == $franchiseid ){
						$canDoInvoiceApproval = 1;
					}else if($invoice->franchise_id == $userid ){
						$canDoInvoiceApproval = 1;
					}				
					$canDoInvoiceApproval = 1;
				}
			}
		}
		return $canDoInvoiceApproval;
	}
	
	public function canViewInvoice($model,$invoicetype='')
	{
		$userrole = Yii::$app->userrole;
		$userid=$userrole->user_id;				
		$user_type=$userrole->user_type;
		$role=$userrole->role;
		$rules=$userrole->rules;
		$franchiseid=$userrole->franchiseid;		
		$resource_access=$userrole->resource_access;
		$is_headquarters =$userrole->is_headquarters;
		$role_chkid=$userrole->role_chkid;
		
		$modelInvoice=new Invoice();
		if($user_type== 1 && ! in_array('invoice_management',$rules) ){
			return $responsedata;
		}else if($user_type==3 && $is_headquarters!=1){
			if($resource_access == '5'){
				$userid = $franchiseid;
			}
			if($invoicetype ==3 || $invoicetype == 1){
				$model = $model->andWhere(' t.franchise_id="'.$userid.'" ');
			}else if($invoicetype == 4 || $invoicetype == 2){
				$model = $model->andWhere(' t.franchise_id="'.$userid.'" and t.status>='.$modelInvoice->enumStatus['payment_pending'].' ');
			}				
		}else if($user_type==2){
			
			//$model = $model->andWhere(' (ofr.status!='.$modelOffer->enumStatus['in-progress'].' and ofr.status!='.$modelOffer->enumStatus['open'].') ');
			$model = $model->andWhere(' t.customer_id="'.$userid.'" and (t.invoice_type=1 or t.invoice_type=3) and t.status>='.$modelInvoice->enumStatus['payment_pending'].' ');
		}else if($user_type==1 && $is_headquarters!=1){
			//$model = $model->andWhere(' (ofr.status!='.$modelOffer->enumStatus['in-progress'].' and ofr.status!='.$modelOffer->enumStatus['open'].') ');
			$model = $model->andWhere(' (((t.invoice_type=2 or t.invoice_type=4) and t.status>='.$modelInvoice->enumStatus['payment_pending'].' ) or ((t.invoice_type=1 or t.invoice_type=3) and t.status>='.$modelInvoice->enumStatus['open'].' ))');
			$model = $model->andWhere(' (t.franchise_id="'.$franchiseid.'" or app.franchise_id="'.$franchiseid.'")');
		}
		
		$sqlcondition = [];
		if($user_type== Yii::$app->params['user_type']['user'] &&  in_array('generate_invoice',$rules))
		{
			if($is_headquarters ==1){
				$sqlcondition[] = ' (t.status = '.$modelInvoice->enumStatus['open'].' or (t.created_by ='.$userid.' or t.updated_by ='.$userid.' )) ';
			}else{
				$sqlcondition[] = ' (t.status = '.$modelInvoice->enumStatus['open'].' 
				or (t.created_by ='.$userid.' or t.updated_by ='.$userid.' )) 
				or
				(t.franchise_id="'.$franchiseid.'" or app.franchise_id="'.$franchiseid.'")';
			}			
		}
		
		if($user_type== Yii::$app->params['user_type']['user'] &&  in_array('invoice_approvals',$rules)){
			$sqlcondition[] = ' (t.status >= '.$modelInvoice->enumStatus['approval_in_process'].') ';
		}
		
		if($user_type== Yii::$app->params['user_type']['user'] &&  in_array('update_invoice_payment_status',$rules)){
			$sqlcondition[] = ' (t.status >= '.$modelInvoice->enumStatus['payment_pending'].') ';
		}

		if(count($sqlcondition)>0){
			$strSqlCondition = ' '.implode(' OR ',$sqlcondition).' ';
			$model = $model->andWhere( $strSqlCondition );
		}
		return $model;
	}

	private function generateOssInvoice($arrFranchiseDetails, $invoicedetails){
		if($invoicedetails->application){
			$company_name=$invoicedetails->application->companyname;
			$contact_name=$invoicedetails->application->contactname;				
			$company_address=$invoicedetails->application->address;
			$company_zipcode=$invoicedetails->application->zipcode;
			$company_city=$invoicedetails->application->city;
			$company_telephone=$invoicedetails->application->telephone;
			$company_email_address=$invoicedetails->application->emailaddress;
			$company_gst_no=$invoicedetails->application->tax_no ? $invoicedetails->application->tax_no:'-';
		}
		//$customer_number = $invoicedetails->franchise->usercompanyinfo->osp_number;
		$customer_number = ($invoicedetails->customer?$invoicedetails->customer->customer_number:"-");
		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$invoice_approved_date = $invoicedetails->invoice_approved_date==''? date('Y-m-d'):$invoicedetails->invoice_approved_date;
		$invoice_due = $invoicedetails->invoice_due==''? date('Y-m-d',strtotime("+1 month")):$invoicedetails->invoice_due;
		$html='<style>
			table {
				border-collapse: collapse;
			}		
			
			table, tbody, tr, th, td{
				background-color: rgba(0, 0, 0, 0.0) !important;
			}
			
			table.reportDetailLayout {
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
				font-size:12px;
				font-family:Arial;
				text-align: left;
				/*background-color:#DFE8F6;*/
				padding:3px;
			}
			
			td.reportDetailLayoutHead {
				text-align: center;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				/*background-color:#4e85c8;*/
				/* background-color:#006fc0;*/
				padding:3px;
				color:#FFFFFF;
			}

			td.reportDetailLayoutInner {
				text-align: center;
				font-size:12px;
				font-family:Arial;
				text-align: left;
			}	
			
			table.productDetails {
				border-collapse: collapse;
				border: 1px solid #000000;
				width:100%;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				margin-bottom:5px;
				margin-top:5px;
			}

			td.productDetails {
				text-align: center;
				border: 1px solid #000000;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				/*background-color:#DFE8F6;*/
				padding:3px;
			}
			</style>
			
			<div style="font-size:18px;color:#2f4a81;text-align: center;width:100%;margin-bottom:20px;font-family:Arial;"><b>Invoice</b></div>
			
			<table cellpadding="0" cellspacing="0" border="0" class="reportDetailLayout" width="100%">
				<tr>
					<td width="70%" colspan="2" class="reportDetailLayoutInner">
						&nbsp;
					</td>
					<td width="30%" rowspan="4" style="text-align:right;" valign="top">
						<img src="'.Yii::$app->params['image_files'].'header-img.png" border="0" style="width:186px;">
					</td>
				</tr>
				<tr>
					<td valign="top" width="12%">Invoiced To: </td>
					<td class="reportDetailLayoutInner" style="text-align:left;" valign="middle">
					'.$arrFranchiseDetails['company_name'].'<br>
					'.$arrFranchiseDetails['address'].'<br>
					'.$arrFranchiseDetails['city'].'<br>
					'.$arrFranchiseDetails['state'].' - '.$arrFranchiseDetails['zipcode'].'<br>
					'.$arrFranchiseDetails['country'].'.
					</td>
				</tr>	
				<tr>
					<td>Invoice No: </td>
					<td class="reportDetailLayoutInner" style="text-align:left;" valign="middle">
					'.$invoicedetails->invoice_number.'
					</td>
				</tr>
				<tr>
					<td>Invoice Date: </td>
					<td class="reportDetailLayoutInner" style="text-align:left;" valign="middle">
					'.date($date_format,strtotime($invoice_approved_date)).'
					</td>
				</tr>
				<tr>
					<td>Invoice Due: </td>
					<td class="reportDetailLayoutInner" style="text-align:left;" valign="middle">
					'.date($date_format,strtotime($invoice_due)).'
					</td>
				</tr>			
			</table>			
							
			<table cellpadding="0" cellspacing="0" border="0" class="productDetails">';
			if($invoicedetails->invoice_type == 2){
				$html.='<tr>
							<td colspan="4" class="productDetails">Operator Name : '.$company_name.'</td>
						</tr>
						<tr>	
							<td colspan="4" class="productDetails">
							
								<table cellpadding="0" cellspacing="0" border="0" class="reportDetailLayout">
									
									<tr>
										<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner" width="15%">Operator ID</td>
										<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner" width="2%">:</td>
										<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner" width="83%">'.$customer_number.'</td>
										
									</tr>
									
									<tr>
										<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">Address</td>
										<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">:</td>
										<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">'.$company_address.','.$company_city.'-'.$company_zipcode.'</td>
										
									</tr>							
									
									<tr>
										<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">Contact Person</td>
										<td style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">:</td>
										<td colspan="4" style="text-align:left;padding-bottom:5px;" valign="middle" class="reportDetailLayoutInner">'.$contact_name.'</td>																		
									</tr>
									
									
								</table>													
								
							</td>
							
						</tr>';
				}
				
				
				$html.='<tr>	
					<td class="productDetails" style="text-align:center;font-weight:bold;">S.No</td>
					<td class="productDetails" style="text-align:center;font-weight:bold;">Description</td>
					<td class="productDetails" style="text-align:center;font-weight:bold;">Standard(s)</td>
					<td class="productDetails" style="text-align:center;font-weight:bold;">Amount in '.$invoicedetails->currency_code.'</td>
				</tr>';
				
				$inedetails = $invoicedetails->invoicedetails;
				if(count($inedetails)>0)
				{
					$invoiceCnt=1;
					$arrOE=array();										
					foreach($inedetails as $otherE)
					{

						$detailstandard = $otherE->detailstandard;
						$standardslabel = [];
						if(count($detailstandard)>0){
							foreach($detailstandard as $standarddata){
								$standardslabel[] = $standarddata->standard->code;
							}
						}else{
							$standardslabel = ['-'];
						}
						$standardlabelimploded = implode('/',$standardslabel);
						
						$html.='<tr>	
							<td class="productDetails" style="text-align:center;">'.$invoiceCnt.'</td>
							<td class="productDetails" style="text-align:center;">'.$otherE->description.'</td>
							<td class="productDetails" style="text-align:center;">'.$standardlabelimploded.'</td>
							<td class="productDetails" style="text-align:right;">'.number_format($otherE->amount, 2, '.', '').'</td>
						</tr>';
						$invoiceCnt++;
					}
				}
				
				$html.='
				<tr>
					<td colspan="3" class="productDetails" style="text-align:right;font-weight:bold;">Total Value</td>
					<td class="productDetails" style="text-align:right;font-weight:bold;">'.$invoicedetails->total_fee.'</td>						
				</tr>';
				
				if($invoicedetails->discount>0)
				{
					$html.='
					<tr>
						<td colspan="3" class="productDetails" style="text-align:right;font-weight:bold;">Discount</td>
						<td class="productDetails" style="text-align:right;font-weight:bold;">'.$invoicedetails->discount.'</td>						
					</tr>';
					
					$html.='
					<tr>
						<td colspan="3" class="productDetails" style="text-align:right;font-weight:bold;">Grand Total Value</td>
						<td class="productDetails" style="text-align:right;font-weight:bold;">'.$invoicedetails->grand_total_fee.'</td>						
					</tr>';						
				}
				
				$invoicetax = $invoicedetails->invoicetax;
				if(count($invoicetax)>0)
				{
					$invoiceTaxCnt=0;						
					foreach($invoicetax as $invoiceT)
					{														
						if($invoiceTaxCnt==0)
						{
							$html.='<tr>	
								<td rowspan="'.count($invoicetax).'" colspan="2" class="productDetails" style="text-align:center;"></td>
								<td class="productDetails" style="text-align:right;">Add '.$invoiceT->tax_name.' @ '.$invoiceT->tax_percentage.'%</td>								
								<td class="productDetails" style="text-align:right;">'.number_format($invoiceT->amount, 2, '.', '').'</td>
							</tr>';
						}else{
							$html.='<tr>										
								<td class="productDetails" style="text-align:right;">Add '.$invoiceT->tax_name.' @ '.$invoiceT->tax_percentage.'%</td>								
								<td class="productDetails" style="text-align:right;">'.number_format($invoiceT->amount, 2, '.', '').'</td>
							</tr>';
						}
						$invoiceTaxCnt++;
					}							
				}
				
			$html.='	
				<tr>
					<td colspan="3" class="productDetails" style="text-align:right;font-weight:bold;">Total Payable Value</td>
					<td class="productDetails" style="text-align:right;font-weight:bold;">'.$invoicedetails->total_payable_amount.'</td>						
				</tr>
				<tr>
					<td colspan="3" class="productDetails" style="text-align:right;font-weight:bold;">In Round</td>
					<td class="productDetails" style="text-align:right;font-weight:bold;">'.round($invoicedetails->total_payable_amount).'</td>						
				</tr>';

				if($invoicedetails->conversion_required_status==1)
				{
					$html.='
					<tr>
						<td colspan="3" align="center" style="text-align:center;font-weight:bold;" valign="middle" class="productDetails">&nbsp;</td>
						<td align="right" style="text-align:right;font-weight:bold;" valign="middle" class="productDetails">'.$invoicedetails->conversion_currency_code." ".$invoicedetails->conversion_total_payable_amount.'</td>
					</tr>';	
				}	

			$html.='</table>';				
			
			/*
			<br>
			<img src="'.Yii::$app->params['site_path'].'backend/web/images/sign.png" border="0" style="width:25%;"><br>Authorised Signature
			*/	
			
			$html.='<div style="font-family:Arial;font-size:12px;width:100%;text-align:right;font-weight:bold;margin-top:5px;">
				For GCL International Assessment Pvt Ltd.
			</div>
			<div style="width:100%;margin-top:5px;">
			
				<table cellpadding="0" cellspacing="0" border="0" class="reportDetailLayout">
					<tr >
						<td colspan="2" style="font-weight:bold;" class="reportDetailLayoutInner"><u>Payment Details</u></td>
					</tr>';
					//$Usermodel = User::find()->where(['id'=>$invoicedetails->franchise_id])->one();
					$modelUser = new User();
					$Usermodel = $modelUser->hqOSS();
					if($Usermodel !== null){
						$paymentdetails=$Usermodel->userpayment;
						if(count($paymentdetails)>0)
						{
							$arrpayment=[];
							foreach($paymentdetails as $pfields)
							{
								$html.='
								<tr>
									<td style="text-align:left;padding-top:10px;margin-bottom:0px;" valign="middle" width="30%" class="reportDetailLayoutInner">'.$pfields['payment_label'].'</td>
									<td style="text-align:left;padding-top:10pxpx;margin-bottom:0px;" valign="middle" class="reportDetailLayoutInner">'.$pfields['payment_content'].'</td>
								</tr>';
							}	
							$resultarr['oss_payment_details']=$arrpayment;		
						}
					}
					
				$html.='</table>	
			</div>';

			if($Usermodel !== null){
				$company_address2 = $Usermodel->usercompanyinfo->company_address2 ? ', '.$Usermodel->usercompanyinfo->company_address2:'';
				$company_city = $Usermodel->usercompanyinfo->company_city;
				$statename = $Usermodel->usercompanyinfo->companystate->name;
				$countryname = $Usermodel->usercompanyinfo->companycountry->name;
				$company_zipcode = $Usermodel->usercompanyinfo->company_zipcode;

				$html.='<div style="width:100%;margin-top:5px;">
			
				<table cellpadding="0" cellspacing="0" border="0" class="reportDetailLayout" >
					<tr >
						<td style="text-align:center; font-size:14px;" class="reportDetailLayoutInner">'.$Usermodel->usercompanyinfo->company_name.'</td>
					</tr>
					<tr >
						<td style="text-align:center;" class="reportDetailLayoutInner">'.$Usermodel->usercompanyinfo->company_address1
						.$company_address2.', '.$company_city.', '.$statename.', '.$countryname.' - '.$company_zipcode.'</td>
					</tr>
					<tr >
						<td style="text-align:center;" class="reportDetailLayoutInner">
						Phone: '.$Usermodel->usercompanyinfo->mobile.'&nbsp;&nbsp;&nbsp;Email: '.$Usermodel->usercompanyinfo->company_email.'</td>
					</tr>';
					if($Usermodel->usercompanyinfo->company_website !=''){
						$html.='<tr >
						<td style="text-align:center;" class="reportDetailLayoutInner">
						Web Site: '.$Usermodel->usercompanyinfo->company_website.'</td>
					</tr>';
					}
					
					//$Usermodel = User::find()->where(['id'=>$invoicedetails->franchise_id])->one();
				$html.='</table>	
			</div>
			';
			}
		return $html;
	}
}
