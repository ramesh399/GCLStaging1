<?php

namespace app\modules\master\models;

use Yii;
use yii\base\Model;

use app\modules\application\models\Application;
use app\modules\offer\models\Offer;
use app\modules\invoice\models\Invoice;
use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditPlanUnit;
use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\AuditPlanUnitExecutionChecklist;
use app\modules\changescope\models\ProductAddition;
use app\modules\changescope\models\Withdraw;
use app\modules\certificate\models\Certificate; 

use app\modules\transfercertificate\models\Request;
use app\modules\transfercertificate\models\RequestStandard;

use app\modules\master\models\UserStandard;

class UserDashboard extends Model
{
    /**
     * @return array the validation rules.
    */
	
	public $is_headquarters;
    public $resource_access;
	public $date_format;
	public $rules;
	public $user_type;
	public $role;
	
	public $arrRenewalAudit=array('0'=>'Less than 90','1'=>'90 - 120','2'=>'121 - 150','3'=>'151 - 200');
	public $arrEnumRenewalAudit=array(0=>'0 - 90',1=>'91 - 120',2=>'121 - 150',3=>'151 - 200');
	public $arrDueCertificate=array('0'=>'0 - 30','1'=>'30 - 60','2'=>'60 - 90','3'=>'90 - 120');
	//public $arrEnumDueCertificate=array(0=>'0 - 90',1=>'31 - 60',2=>'61 - 90',3=>'91 - 120');
	public $arrEnumDueCertificate=array(0=>'0 - 89',1=>'90 - 120',2=>'121 - 150',3=>'151 - 200');
	public $arrColor=array('0'=>'#FF0000','1'=>'#F79647','2'=>'#4572A7','3'=>'#00B050');	
	
	//public $arrColor=array('0'=>'#FF0000','1'=>'#F79647','2'=>'#4572A7','3'=>'#00B050');	
    public function rules()
    {
        return [
            
        ];
    }
	
	public function applicationSubmitForReview($franchiseid='')
	{
		$resultarr=array();
		$appmodel = Application::find()->where(['status'=> '1']);
        if($this->resource_access != 1)
        {
            $appmodel = $appmodel->andWhere('franchise_id="'.$franchiseid.'"');
        }        
        
        $appmodel = $appmodel->all();	
        if($appmodel !== null)
        {
            if(count($appmodel)>0)
            {
                foreach($appmodel as $model)
                {
                    $data=array();
                    $data['id']=$model->id;
                    $data['company_name']=$model->companyname;
                    $data['contact_name']=$model->contactname;

                    $appstdarr=[];     
                    $appStandard=$model->applicationstandard;
                    if(count($appStandard)>0)
                    {
                        foreach($appStandard as $std)
                        {
                            $appstdarr[]=($std->standard?$std->standard->code:'');	
                        }
                    }
                    $data["standards"]=$appstdarr;
                    $data['created_at']=date($this->date_format,$model->created_at);
            
            
                    $resultarr['pending_actions'][]=$data;
                }
            }
        }
		return $resultarr;
		
	}

	public function applicationWaitingforReview($franchiseid='')
	{
		$resultarr=array();
		$ApplicationModel = new Application();
		//waiting_for_review
		$appmodel = Application::find()->where(['status'=> $ApplicationModel->arrEnumStatus['waiting_for_review']]);
		if($this->is_headquarters!=1)
		{
			$appmodel = $appmodel->andWhere('franchise_id="'.$franchiseid.'"');
		}				
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['id']=$model->id;
					$data['company_name']=$model->companyname;
					$data['contact_name']=$model->contactname;

					$appstdarr=[];     
					$appStandard=$model->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->created_at);
			
			
					$resultarr['application_waiting_for_review'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
	public function applicationReviewInProgress($franchiseid='',$userid='')
	{
		$resultarr=array();
		$appmodel = Application::find()->alias('t')->where(['t.status'=> '3']);
		$appmodel->joinWith(['applicationreview as appreview']);
		
		if($userid!='')
		{
			$appmodel = $appmodel->andWhere('appreview.status in (0,1) and appreview.user_id='.$userid);
		}
		
		if($this->is_headquarters!=1)
		{
			$appmodel = $appmodel->andWhere('t.franchise_id="'.$franchiseid.'"');
		}				
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['id']=$model->id;
					$data['company_name']=$model->companyname;
					$data['contact_name']=$model->contactname;

					$appstdarr=[];     
					$appStandard=$model->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->created_at);
			
			
					$resultarr['application_review_in_process'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
	public function applicationWaitingforApproval($franchiseid='')
	{
		$resultarr=array();
		$appmodel = Application::find()->where(['status'=> '4']);
		if($this->is_headquarters!=1)
		{
			$appmodel = $appmodel->andWhere('franchise_id="'.$franchiseid.'"');
		}				
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['id']=$model->id;
					$data['company_name']=$model->companyname;
					$data['contact_name']=$model->contactname;

					$appstdarr=[];     
					$appStandard=$model->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->created_at);
			
			
					$resultarr['application_waiting_for_approval'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
	public function applicationApprovalInProgress($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		
		$resultarr=array();
		$appmodel = Application::find()->alias('t')->where(['t.status'=> $modelApplication->arrEnumStatus['approval_in_process']]);
		$appmodel->joinWith(['applicationapproval as appapproval']);
		if($userid!='')
		{
			$appmodel = $appmodel->andWhere('appapproval.status in (0) and appapproval.user_id='.$userid);
		}
		if($this->is_headquarters!=1)
		{
			$appmodel = $appmodel->andWhere('t.franchise_id="'.$franchiseid.'"');
		}				
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['id']=$model->id;
					$data['company_name']=$model->companyname;
					$data['contact_name']=$model->contactname;

					$appstdarr=[];     
					$appStandard=$model->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->created_at);
			
			
					$resultarr['application_approval_in_process'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
	public function generateOffer($franchiseid='')
	{
		$modelApplication = new Application();
		$modelOffer = new Offer();
		
		$resultarr=array();
		$appmodel = Application::find()->alias('t');
		$appmodel->joinWith(['offer as ofr']);
					
		if($this->is_headquarters!=1){
			$appmodel = $appmodel->where('t.franchise_id="'.$franchiseid.'"');
		}
		$appmodel = $appmodel->andWhere('t.status='.$modelApplication->arrEnumStatus['approved'].' and (ofr.status='.$modelOffer->enumStatus['open'].' or ofr.status='.$modelOffer->enumStatus['in-progress'].' or ofr.status is null)');
					
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['id']=$model->id;
					$data['offer_id']=$model->offer?$model->offer->id:0;
					$data['company_name']=$model->companyname;
					$data['contact_name']=$model->contactname;

					$appstdarr=[];     
					$appStandard=$model->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->created_at);
			
			
					$resultarr['waiting_for_offer_generation'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
	
	public function waitingForOfferSendToCustomer($franchiseid='', $userid='')
	{
		$modelApplication = new Application();
		$modelOffer = new Offer();
		
		$resultarr=array();
		$appmodel = Offer::find()->alias('t');
		$appmodel->joinWith(['application as app']);
					
		if($this->is_headquarters!=1){
			$appmodel = $appmodel->where('app.franchise_id="'.$franchiseid.'"');
		}
		$appmodel = $appmodel->andWhere(' t.status='.$modelOffer->enumStatus['waiting-for-send-to-customer'].' and (t.created_by='.$userid.' or t.updated_by='.$userid.') ');
					
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['offer_id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					$data['contact_name']=$model->application->contactname;
					$data['currency']=$model->offerlist->currency;
                    $data['grand_total_fee']=$model->offerlist->grand_total_fee;
					$appstdarr=[];     
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->created_at);
			
			
					$resultarr['waiting_for_offer_send_to_customer'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}

	public function waitingUserOssOfferApproval($franchiseid='')
	{
		$modelApplication = new Application();
		$modelOffer = new Offer();
		
		$resultarr=array();
		
		$appmodel = Offer::find()->alias('t');
		$appmodel->joinWith(['application as app']);
		if($this->is_headquarters!=1){
			$appmodel = $appmodel->where(' app.franchise_id="'.$franchiseid.'" ');
		}
		$appmodel = $appmodel->andWhere(' t.status='.$modelOffer->enumStatus['waiting-for-oss-approval'].'  ');
		$appmodel = $appmodel->all();	

		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['offer_id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					$data['contact_name']=$model->application->contactname;
                    $data['currency']=$model->offerlist->currency;
                    $data['grand_total_fee']=$model->offerlist->grand_total_fee;
					$appstdarr=[];     
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->created_at);
			
			
					$resultarr['waiting_for_user_oss_offer_approval'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}

	public function waitingUserOssReinitiatedOfferApproval($franchiseid='')
	{
		$modelApplication = new Application();
		$modelOffer = new Offer();
		
		$resultarr=array();
		
		$appmodel = Offer::find()->alias('t');
		$appmodel->joinWith(['application as app']);
		if($this->is_headquarters!=1){
			$appmodel = $appmodel->where(' app.franchise_id="'.$franchiseid.'" ');
		}
		$appmodel = $appmodel->andWhere(' t.status='.$modelOffer->enumStatus['re-initiated-to-oss'].' ');
		$appmodel = $appmodel->all();	

		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['offer_id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					$data['contact_name']=$model->application->contactname;
                    $data['currency']=$model->offerlist->currency;
                    $data['grand_total_fee']=$model->offerlist->grand_total_fee;
					$appstdarr=[];     
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->created_at);
			
			
					$resultarr['waiting_for_user_oss_reinitated_offer_approval'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}


	public function offerApproval($franchiseid='')
	{
		$modelApplication = new Application();
		$modelOffer = new Offer();
		
		$resultarr=array();
		$appmodel = Application::find()->alias('t');
		$appmodel->joinWith(['offer as ofr']);
					
		if($this->is_headquarters!=1){
			$appmodel = $appmodel->where('t.franchise_id="'.$franchiseid.'"');
		}
		$appmodel = $appmodel->andWhere('t.status='.$modelApplication->arrEnumStatus['approved'].' and (ofr.status='.$modelOffer->enumStatus['customer_approved'].')');
		/*if($is_headquarters!=1)
		{
			$appmodel = $appmodel->andWhere('franchise_id="'.$franchiseid.'"');
		}
		*/				
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['id']=$model->id;
					$data['offer_id']=$model->offer->id;
					$data['company_name']=$model->companyname;
					$data['contact_name']=$model->contactname;

					$appstdarr=[];     
					$appStandard=$model->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->created_at);
			
			
					$resultarr['waiting_for_offer_approval'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
	public function generateInvoice($franchiseid='',$userid='')
	{
		$modelInvoice = new Invoice();		
		$resultarr=array();
		
		/*
		$appmodel = Offer::find()->where(['t.status'=>$modelOffer->enumStatus['finalized']])->alias('t');
		$appmodel = $appmodel->joinWith(['application']);
		$appmodel = $appmodel->join('left join', 'tbl_invoice as invoice','invoice.offer_id=t.id');
		
		$appmodel = $appmodel->where('(invoice.status in (0) or invoice.id IS NULL) 
		or (invoice.status in (1) and  (invoice.created_by='.$userid.' or invoice.updated_by='.$userid.' )) ');
		if($this->is_headquarters!=1){
			$appmodel = $appmodel->andWhere('tbl_application.franchise_id="'.$franchiseid.'"');
		}
		//$appmodel = $model->andWhere('t.status='.$modelApplication->arrEnumStatus['approved'].' and (ofr.status='.$modelOffer->enumStatus['customer_approved'].')');
		*/
		
		$invoiceModel = Invoice::find()->alias('t');
		$invoiceModel = $invoiceModel->where('(t.status in ('.$modelInvoice->enumStatus['open'].') or (t.status in ('.$modelInvoice->enumStatus['in-progress'].' and (t.created_by='.$userid.' or t.updated_by='.$userid.' ))))'); 
		$invoiceModel = $invoiceModel->all();			
		if(count($invoiceModel)>0)
		{
			foreach($invoiceModel as $invoice)
			{
				$data=array();
				$data['id']=$invoice->id;				
				$invoiceType = $invoice->invoice_type;
				$data['type']=$invoiceType;
				if($invoiceType==1 || $invoiceType==2)
				{
					$data['company_name']=$invoice->application->companyname;
					$data['contact_name']=$invoice->application->contactname;												
				}elseif($invoiceType==3){					
					$userCompanyInfo = $invoice->customer->usercompanyinfo;
					$data['company_name']=$userCompanyInfo->company_name;					
					$data['contact_name']=$userCompanyInfo->contact_name;
				}elseif($invoiceType==4){
					$franchiseCompanyInfo = $invoice->franchise->usercompanyinfo;
					$data['company_name']=$franchiseCompanyInfo->company_name;					
					$data['contact_name']=$franchiseCompanyInfo->contact_name;
				}					
				$data['total_amount']=$invoice->total_payable_amount;
				$data['currency']=$invoice->currency_code;									
				$data['created_at']=date($this->date_format,$invoice->created_at);
					
				$resultarr['waiting_for_invoice_generation'][]=$data;
			}
		}		
		return $resultarr;
		
	}
	
	public function invoiceApproval($franchiseid='')
	{
		$modelInvoice = new Invoice();		
		$resultarr=array();		
		/*
		$appmodel = Offer::find()->where(['t.status'=>$modelOffer->enumStatus['finalized']])->alias('t');
		$appmodel = $appmodel->joinWith(['application']);
		$appmodel = $appmodel->join('join', 'tbl_invoice as invoice','invoice.offer_id=t.id');
		$appmodel = $appmodel->where('invoice.status ='.$modelInvoice->enumStatus['approval_in_process'].'');
		if($this->is_headquarters!=1){
			$appmodel = $appmodel->andWhere('tbl_application.franchise_id="'.$franchiseid.'"');
		}
		$appmodel = $appmodel->all();
		*/
		
		$invoiceModel = Invoice::find()->alias('t');
		$invoiceModel = $invoiceModel->where('(t.status in ('.$modelInvoice->enumStatus['approval_in_process'].'))'); 
		$invoiceModel = $invoiceModel->all();				
		if(count($invoiceModel)>0)
		{
			foreach($invoiceModel as $invoice)
			{
				$data=array();			
				$data['id']=$invoice->id;				
				$invoiceType = $invoice->invoice_type;
				$data['type']=$invoiceType;
				if($invoiceType==1 || $invoiceType==2)
				{
					$data['company_name']=$invoice->application->companyname;
					$data['contact_name']=$invoice->application->contactname;												
				}elseif($invoiceType==3){					
					$userCompanyInfo = $invoice->customer->usercompanyinfo;
					$data['company_name']=$userCompanyInfo->company_name;					
					$data['contact_name']=$userCompanyInfo->contact_name;
				}elseif($invoiceType==4){
					$franchiseCompanyInfo = $invoice->franchise->usercompanyinfo;
					$data['company_name']=$franchiseCompanyInfo->company_name;					
					$data['contact_name']=$franchiseCompanyInfo->contact_name;
				}					
				$data['total_amount']=$invoice->total_payable_amount;
				$data['currency']=$invoice->currency_code;									
				$data['created_at']=date($this->date_format,$invoice->created_at);									
		
				$resultarr['waiting_for_invoice_approval'][]=$data;
			}
		}		
		return $resultarr;		
	}
	
	
	public function generateAuditPlan($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelOffer = new Offer();
		$modelInvoice = new Invoice();
		$modelAudit = new Audit();
		
		$resultarr=array();		
		
		//$appmodel = Invoice::find()->where(['t.status'=>$modelInvoice->enumStatus['finalized']])->alias('t');
		$appmodel = Audit::find()->where(['t.status'=>[$modelAudit->arrEnumStatus['open'],$modelAudit->arrEnumStatus['submitted']] ])->alias('t');
		$appmodel = $appmodel->joinWith(['application']);
		/*$appmodel = $appmodel->andWhere('(invoice.status in (0) or invoice.id IS NULL) 
				or (invoice.status in (1) and  (invoice.created_by='.$userid.' or invoice.updated_by='.$userid.' )) ');
				*/
		//$model->joinWith(['offer as ofr','application as app']);
		//$appmodel->joinWith(['audit']);
		$appmodel = $appmodel->join('left join', 'tbl_audit_plan as audit_plan','audit_plan.audit_id=t.id');
		$appmodel = $appmodel->andWhere('(t.status='.$modelAudit->arrEnumStatus['open'].' or (audit_plan.created_by='.$userid.' or audit_plan.updated_by='.$userid.' ))');

		//(tbl_audit.status in ('.$modelAudit->arrEnumStatus['open'].') or tbl_audit.id IS NULL) 
		//or (tbl_audit.status in ('.$modelAudit->arrEnumStatus['submitted'].') and 
		//$appmodel = $model->andWhere('t.status='.$modelApplication->arrEnumStatus['approved'].' and (ofr.status='.$modelOffer->enumStatus['customer_approved'].')');
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					//$data['invoice_id']=$model->id;
					$data['id']=$model->id;
					
					$data['audit_type']=$model->audit_type;
					if($model->application){
						$data['app_id']=$model->application->id;
						$data['company_name']=$model->application->companyname;
						$data['contact_name']=$model->application->contactname;
						$data['created_at']=date($this->date_format,$model->application->created_at);

						$appstdarr=[];     

						$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
						$appstdarr = $auditstandard['stdcode'];
						$data["standards"]=$appstdarr;
					}
					
					//$data['total_amount']=$model->offerlist->conversion_total_payable;
					//$data['currency']=$model->offerlist->currency;
					//$data['man_day']=$model->manday;
					

					
					/*
					$appStandard=$model->application->applicationstandard;
					if($model->application){
						if(count($appStandard)>0)
						{
							foreach($appStandard as $std)
							{
								$appstdarr[]=($std->standard?$std->standard->code:'');	
							}
						}
					}
					*/
					
					
			
			
					$resultarr['waiting_for_audit_plan_generation'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
	
	public function rejectedAuditPlan($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelOffer = new Offer();
		$modelInvoice = new Invoice();
		$modelAudit = new Audit();
		
		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>$modelAudit->arrEnumStatus['rejected']])->alias('t');
		$appmodel = $appmodel->joinWith(['application','auditplan','offer as ofr']);
		$appmodel = $appmodel->andWhere('(tbl_audit_plan.created_by='.$userid.' or tbl_audit_plan.updated_by='.$userid.')');

		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['id']=$model->id;
					$data['offer_id']=$model->offer_id;
					$data['app_id']=$model->app_id;

					
					if($model->offer){
						$data['total_amount']=$model->offer->offerlist->conversion_total_payable;
						$data['currency']=$model->offer->offerlist->currency;
						$data['man_day']=$model->offer->manday;
					}
					
					$data['contact_name']=$model->application->contactname;
					$data['company_name']=$model->application->companyname;
					$appstdarr=[];     
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
			
			
					$resultarr['waiting_for_rejected_audit_plan_generation'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
	
	public function waitingForAuditReview($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelOffer = new Offer();
		$modelInvoice = new Invoice();
		$modelAudit = new Audit();	
		
		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>$modelAudit->arrEnumStatus['review_in_process']])->alias('t');
		$appmodel = $appmodel->joinWith(['application','auditplan','offer as ofr']);
		$appmodel = $appmodel->andWhere('tbl_audit_plan.application_lead_auditor='.$userid);
		$appmodel = $appmodel->all();
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					
					/*$application = $model->audit->application;
					$applicationstandard = $application->applicationstandard;
					if(count($applicationstandard)>0){
						foreach($applicationstandard as $appstandard){
							$standardIds[] = $appstandard->standard_id;
						}
					}
					$reviewer_canassign=0;
					$UserStandard = UserStandard::find()->where(['user_id'=>$userid, 'standard_id'=>$standardIds,'approval_status'=>2 ])->all();
					 
					if(count($UserStandard) == count($standardIds)>0 ){
						$reviewer_canassign=1;
					}
					if(!$reviewer_canassign){
						continue;
					}
					*/
					$data=array();
					$data['audit_id']=$model->id;
					
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					if($model->offer){
						$data['offer_id']=$model->offer->id;
						$data['total_amount']=$model->offer->offerlist->conversion_total_payable;
						$data['currency']=$model->offer->offerlist->currency;
						$data['man_day']=$model->offer->manday;
					}
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];    
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/* 
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
			
			
					$resultarr['waiting_for_audit_review'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}


	public function waitingForAuditExecution($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelOffer = new Offer();
		$modelInvoice = new Invoice();
		$modelAudit = new Audit();
		$modelAuditPlanUnit = new AuditPlanUnit();	
		
		$resultarr=array();		
		$statusArr = [$modelAuditPlanUnit->arrEnumStatus['open'],$modelAuditPlanUnit->arrEnumStatus['in_progress']];
		
		$appmodel = Audit::find()->where(['t.status'=>[$modelAudit->arrEnumStatus['audit_in_progress'],$modelAudit->arrEnumStatus['approved']] ])->alias('t');
		$appmodel = $appmodel->innerJoinWith(['application as application','auditplan']);
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit as plan_unit','plan_unit.audit_plan_id=tbl_audit_plan.id');
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit_auditor as unit_auditor','plan_unit.id=unit_auditor.audit_plan_unit_id');
		$appmodel = $appmodel->andWhere('unit_auditor.user_id='.$userid.' AND (plan_unit.status='.$modelAuditPlanUnit->arrEnumStatus['in_progress'].' or plan_unit.status='.$modelAuditPlanUnit->arrEnumStatus['open'].' )');

		if($franchiseid!='')
		{
			$appmodel = $appmodel->andWhere('application.franchise_id="'.$franchiseid.'"');
		}

		$appmodel = $appmodel->groupBy(['t.id']);
		$appmodel = $appmodel->all();
		
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['audit_id']=$model->id;
					//$data['offer_id']=$model->offer->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					//$data['total_amount']=$model->offer->offerlist->conversion_total_payable;
					//$data['currency']=$model->offer->offerlist->currency;
					//$data['man_day']=$model->offer->manday;
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];     
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
			
			
					$resultarr['waiting_for_audit_execution'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}

	public function waitingForReportCorrection($franchiseid='',$userid='')
	{
		$resultarr=array();	
		$resultarr['waiting_for_report_correction'][]=[];
				
		return $resultarr;
	}

	public function waitingForFollowupUnitLeadAuditorApproval($franchiseid='',$userid='')
	{
		$resultarr=array();	
		$resultarr['waiting_for_followup_unit_lead_auditor_approval'][]=[];
				
		return $resultarr;
	}

	public function waitingForUnitLeadAuditorApproval($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelAudit = new Audit();
		$modelAuditPlanUnit = new AuditPlanUnit();	
		
		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>$modelAudit->arrEnumStatus['audit_in_progress']])->alias('t');
		$appmodel = $appmodel->joinWith(['application','auditplan']);
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit as plan_unit','plan_unit.audit_plan_id=tbl_audit_plan.id');
		$appmodel = $appmodel->andWhere('plan_unit.unit_lead_auditor='.$userid.' AND plan_unit.status ='.$modelAuditPlanUnit->arrEnumStatus['awaiting_for_unit_lead_auditor_approval'].'');
		$appmodel = $appmodel->groupBy(['t.id']);
		$appmodel = $appmodel->all();
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['audit_id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];     
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
					$resultarr['waiting_for_unit_lead_auditor_approval'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}

	public function waitingForAuditLeadAuditorApproval($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelAudit = new Audit();
		$modelAuditPlanUnit = new AuditPlanUnit();
		$modelAuditPlan = new AuditPlan();	
		
		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>$modelAudit->arrEnumStatus['audit_in_progress']])->alias('t');
		$appmodel = $appmodel->joinWith(['application','auditplan']);
		//$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit as plan_unit','plan_unit.audit_plan_id=tbl_audit_plan.id');
		$appmodel = $appmodel->andWhere('tbl_audit_plan.application_lead_auditor='.$userid.' AND tbl_audit_plan.status ='.$modelAuditPlan->arrEnumStatus['waiting_for_lead_auditor'].'');
		$appmodel = $appmodel->groupBy(['t.id']);
		$appmodel = $appmodel->all();
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['audit_id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];     
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
					$resultarr['waiting_for_audit_lead_auditor_approval'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}

	public function waitingForNCoverdueApproval($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelAudit = new Audit();
		$modelAuditPlanUnit = new AuditPlanUnit();
		$modelAuditPlan = new AuditPlan();	
		
		$resultarr=array();		
		$appmodel = Audit::find()->where(['t.status'=>[
			$modelAudit->arrEnumStatus['audit_completed'],$modelAudit->arrEnumStatus['remediation_in_progress']
		]])->alias('t');
		$appmodel = $appmodel->joinWith(['application','auditplan']);
		//$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit as plan_unit','plan_unit.audit_plan_id=tbl_audit_plan.id');
		$appmodel = $appmodel->andWhere('tbl_audit_plan.application_lead_auditor='.$userid.' AND t.overdue_status=2 ');
		$appmodel = $appmodel->groupBy(['t.id']);
		$appmodel = $appmodel->all();
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['audit_id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];     
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
					$resultarr['waiting_for_audit_nc_overdue_approval'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}

	public function waitingForAuditReviewer($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelAudit = new Audit();
		$modelAuditPlanUnit = new AuditPlanUnit();
		$modelAuditPlan = new AuditPlan();	
		
		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>$modelAudit->arrEnumStatus['audit_in_progress']])->alias('t');
		$appmodel = $appmodel->joinWith(['application','auditplan']);
		$appmodel = $appmodel->andWhere('tbl_audit_plan.status ='.$modelAuditPlan->arrEnumStatus['waiting_for_review'].'');
		$appmodel = $appmodel->all();
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					
					$standardIds = [];
					$application = $model->application;

					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					if(count($auditstandard['standards'])>0){
						foreach($auditstandard['standards'] as $appstandard){
							$standardIds[] = $appstandard['id'];
						}
					}
					/*
					$applicationstandard = $application->applicationstandard;
					if(count($applicationstandard)>0){
						foreach($applicationstandard as $appstandard){
							$standardIds[] = $appstandard->standard_id;
						}
					}
					*/
					$reviewer_canassign=0;
					$UserStandard = UserStandard::find()->where(['user_id'=>$userid, 'standard_id'=>$standardIds,'approval_status'=>2 ])->all();
					if(count($UserStandard) == count($standardIds)){
						$reviewer_canassign=1;
					}
					
					if(!$reviewer_canassign){
						continue;
					}
					 
					
					$data=array();
					$data['audit_id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];   
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*  
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
					$resultarr['waiting_for_audit_reviewer'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}

	public function waitingForAuditReviewerReview($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelAudit = new Audit();
		$modelAuditPlanUnit = new AuditPlanUnit();
		$modelAuditPlan = new AuditPlan();	
		
		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>$modelAudit->arrEnumStatus['audit_in_progress']])->alias('t');
		$appmodel = $appmodel->joinWith(['application','auditplan']);
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_reviewer as plan_reviewer','plan_reviewer.audit_plan_id=tbl_audit_plan.id');
		$appmodel = $appmodel->andWhere('plan_reviewer.reviewer_id='.$userid.' AND plan_reviewer.reviewer_status=1 AND tbl_audit_plan.status ='.$modelAuditPlan->arrEnumStatus['review_in_progress'].'');
		$appmodel = $appmodel->groupBy(['t.id']);
		$appmodel = $appmodel->all();
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['audit_id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];   
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*  
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
					$resultarr['waiting_for_audit_reviewer_review'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}

	public function waitingForNCoverdueReview($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelAudit = new Audit();
		$modelAuditPlanUnit = new AuditPlanUnit();
		$modelAuditPlan = new AuditPlan();	
		
		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>$modelAudit->arrEnumStatus['nc_overdue_waiting_for_review']])->alias('t');
		$appmodel = $appmodel->joinWith(['application','auditplan']);
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_reviewer as plan_reviewer','plan_reviewer.audit_plan_id=tbl_audit_plan.id');
		$appmodel = $appmodel->andWhere('plan_reviewer.reviewer_id='.$userid.' AND plan_reviewer.reviewer_status=1 AND tbl_audit_plan.status ='.$modelAuditPlan->arrEnumStatus['nc_overdue_waiting_for_review'].'');
		$appmodel = $appmodel->groupBy(['t.id']);
		$appmodel = $appmodel->all();
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['audit_id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];   
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*  
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
					$resultarr['waiting_for_nc_overdue_review'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}

	public function waitingForLeadAuditorRemediationApproval($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelAudit = new Audit();
		$modelAuditPlanUnit = new AuditPlanUnit();
		$modelAuditPlan = new AuditPlan();	
		
		$modelExecutionChecklist = new AuditPlanUnitExecutionChecklist();

		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>$modelAudit->arrEnumStatus['remediation_in_progress']])->alias('t');
		$appmodel = $appmodel->joinWith(['application','auditplan']);
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit as plan_unit','plan_unit.audit_plan_id=tbl_audit_plan.id');
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit_execution as execution','execution.audit_plan_unit_id=plan_unit.id');
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit_execution_checklist as execution_checklist','execution_checklist.audit_plan_unit_execution_id=execution.id');

		$appmodel = $appmodel->andWhere('tbl_audit_plan.application_lead_auditor='.$userid.' 
		AND  execution_checklist.status ='.$modelExecutionChecklist->arrEnumStatus['in_progress'].'');
		$appmodel = $appmodel->groupBy(['t.id']);
		$appmodel = $appmodel->all();
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['audit_id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];     
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
					$resultarr['waiting_for_lead_auditor_remediation_approval'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}

	public function waitingForReviewerRemediationApproval($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelAudit = new Audit();
		$modelAuditPlanUnit = new AuditPlanUnit();
		$modelAuditPlan = new AuditPlan();	
		
		$modelExecutionChecklist = new AuditPlanUnitExecutionChecklist();

		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>$modelAudit->arrEnumStatus['remediation_in_progress']])->alias('t');
		$appmodel = $appmodel->joinWith(['application','auditplan']);
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit as plan_unit','plan_unit.audit_plan_id=tbl_audit_plan.id');
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit_execution as execution','execution.audit_plan_unit_id=plan_unit.id');
		//$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit_execution_checklist as execution_checklist','execution_checklist.audit_plan_unit_execution_id=execution.id');

		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_reviewer as plan_reviewer','plan_reviewer.audit_plan_id=tbl_audit_plan.id');
		$appmodel = $appmodel->andWhere('plan_reviewer.reviewer_id='.$userid.' AND plan_reviewer.reviewer_status=1 
		AND tbl_audit_plan.status='.$modelAuditPlan->arrEnumStatus['reviewer_review_in_progress'].' ');
		// /AND  execution_checklist.status ='.$modelExecutionChecklist->arrEnumStatus['waiting_for_approval'].'
		$appmodel = $appmodel->groupBy(['t.id']);
		$appmodel = $appmodel->all();
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['audit_id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];   
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*  
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
					$resultarr['waiting_for_reviewer_remediation_approval'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
	public function waitingForAuditorRejectedRemediationCorrection($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelAudit = new Audit();
		$modelAuditPlanUnit = new AuditPlanUnit();
		$modelAuditPlan = new AuditPlan();	
		
		$modelExecutionChecklist = new AuditPlanUnitExecutionChecklist();

		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>$modelAudit->arrEnumStatus['remediation_in_progress']])->alias('t');
		$appmodel = $appmodel->joinWith(['application','auditplan']);
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit as plan_unit','plan_unit.audit_plan_id=tbl_audit_plan.id');
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit_execution as execution','execution.audit_plan_unit_id=plan_unit.id');
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit_execution_checklist as execution_checklist','execution_checklist.audit_plan_unit_execution_id=execution.id');

		$appmodel = $appmodel->andWhere('tbl_audit_plan.application_lead_auditor='.$userid.' 
		AND  execution_checklist.status ='.$modelExecutionChecklist->arrEnumStatus['reviewer_change_request'].'');
		$appmodel = $appmodel->groupBy(['t.id']);
		$appmodel = $appmodel->all();
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['audit_id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*     
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
					$resultarr['waiting_for_auditor_rejected_remediation_correction'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}



	
	
	public function waitingForAuditInspectionPlan($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelAudit = new Audit();	
		
		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>[
			$modelAudit->arrEnumStatus['review_completed'],
			$modelAudit->arrEnumStatus['inspection_plan_in_process']
			]])->alias('t');
		$appmodel = $appmodel->joinWith(['application','auditplan','offer as ofr']);
		$appmodel = $appmodel->andWhere('tbl_audit_plan.application_lead_auditor='.$userid);
		$appmodel = $appmodel->all();
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['audit_id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					if($model->offer){
						$data['total_amount']=$model->offer->offerlist->conversion_total_payable;
						$data['man_day']=$model->offer->manday;
					}
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];  
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*   
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
			
			
					$resultarr['waiting_for_audit_inspection_plan'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}	
	

	public function waitingForDeclarationApproval($franchiseid='',$user_id='')
	{
		$resultarr=array();
		$appmodel = User::find()->where(['t.status'=> '0'])->alias('t');
		$appmodel = $appmodel->innerJoinWith(['userdeclaration as declaration']);
		$appmodel = $appmodel->andWhere('declaration.status="1"');
		/*if($this->is_headquarters!=1)
		{
			$appmodel = $appmodel->andWhere('franchise_id="'.$franchiseid.'"');
		}*/				
		$appmodel = $appmodel->groupBy(['t.id']);
		//$appmodel = $appmodel->all();
		
		$appmodel = $appmodel->all();	
		
		if(count($appmodel)>0)
		{
			foreach($appmodel as $model)
			{
				$data=array();
				$data['id']=$model->id;
				$data['first_name']=$model->first_name;
				$data['last_name']=$model->last_name;
				$data['email']=$model->email;
				$data['country']=$model->country->name;

				
				$data['created_at']=date($this->date_format,$model->created_at);
		
		
				$resultarr['waiting_for_declaration_approval'][]=$data;
			}
		}
		
		return $resultarr;
		
	}


	public function waitingForBusinessGroupApproval($franchiseid='',$user_id='')
	{
		$resultarr=array();
		$appmodel = User::find()->where(['t.status'=> '0'])->alias('t');
		$appmodel = $appmodel->innerJoinWith(['userbusinessgroup as bgroup']);
		$appmodel = $appmodel->join('inner join', 'tbl_user_business_group_code as gcode','bgroup.id=gcode.business_group_id');
		$appmodel = $appmodel->andWhere('gcode.status="1"');
		/*if($this->is_headquarters!=1)
		{
			$appmodel = $appmodel->andWhere('franchise_id="'.$franchiseid.'"');
		}*/				
		$appmodel = $appmodel->groupBy(['t.id']);
		//$appmodel = $appmodel->all();
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['id']=$model->id;
					$data['first_name']=$model->first_name;
					$data['last_name']=$model->last_name;
					$data['email']=$model->email;
					$data['country']=$model->country->name;

					
					$data['created_at']=date($this->date_format,$model->created_at);
			
			
					$resultarr['waiting_for_business_group_approval'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}

	public function waitingForUserStandardApproval($franchiseid='',$user_id='')
	{
		$resultarr=array();
		$appmodel = User::find()->where(['t.status'=> '0'])->alias('t');
		$appmodel = $appmodel->innerJoinWith(['userstandard as standard']);
		$appmodel = $appmodel->andWhere('standard.approval_status="1"');
		/*if($this->is_headquarters!=1)
		{
			$appmodel = $appmodel->andWhere('franchise_id="'.$franchiseid.'"');
		}*/				
		$appmodel = $appmodel->groupBy(['t.id']);
		//$appmodel = $appmodel->all();
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$code= '';
					foreach($model->userstandard as $std){
						$code = $std->standard->code;
					}
					$data=array();
					$data['id']=$model->id;
					$data['first_name']=$model->first_name;
					$data['last_name']=$model->last_name;
					$data['standard_code']=$code;
					$data['country']=$model->country->name;
					$data['email']=$model->email;
					//if(count(userstandard))
					
					$data['created_at']=date($this->date_format,$model->created_at);
			
			
					$resultarr['waiting_for_standard_approval'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
	public function standardWitnessDateDue($franchiseid='',$user_id='')
	{
		$resultarr=array();
		$appmodel = User::find()->where(['t.status'=> '0'])->alias('t');
		$appmodel = $appmodel->innerJoinWith(['userstandard as standard']);
		$appmodel = $appmodel->andWhere('DATEDIFF(standard.witness_valid_until,NOW()) <=90 AND standard.witness_valid_until IS NOT NULL AND standard.witness_valid_until!=\'0000-00-00\'');
		/*if($this->is_headquarters!=1)
		{
			$appmodel = $appmodel->andWhere('franchise_id="'.$franchiseid.'"');
		}*/				
		$appmodel = $appmodel->groupBy(['t.id']);
		//$appmodel = $appmodel->all();
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$code= '';
					foreach($model->userstandard as $std){
						$code = $std->standard->code;
					}
					$data=array();
					$data['id']=$model->id;
					$data['first_name']=$model->first_name;
					$data['last_name']=$model->last_name;
					$data['standard_code']=$code;
					$data['country']=$model->country->name;
					$data['email']=$model->email;
					//if(count(userstandard))
					
					$data['created_at']=date($this->date_format,$model->created_at);
			
			
					$resultarr['witness_due_date_for_standard'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
			
	public function productAdditionWaitingforReview($franchiseid='',$userid='')
	{
		$modelProductAddition = new ProductAddition();
		
		$resultarr=array();
		$appmodel = ProductAddition::find()->joinWith('application')->alias('t');

		//->where(['t.status'=> $modelProductAddition->arrEnumStatus['waiting_for_review']]);

		$appmodel->joinWith(['reviewer as reviewer']);
		
		if($userid!='')
		{
			$appmodel = $appmodel->where('t.status = "'.$modelProductAddition->arrEnumStatus['waiting_for_review'].'" or (t.status = "'.$modelProductAddition->arrEnumStatus['review_in_process'].'" and reviewer.user_id="'.$userid.'")');
		}
		//$appmodel = ProductAddition::find()->joinWith('application');
		if($this->is_headquarters!=1)
		{
			$appmodel = $appmodel->andWhere('tbl_application.franchise_id="'.$franchiseid.'"');
		}				
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];     
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->created_at);
			
			
					$resultarr['product_addition_waiting_for_review'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
	public function auditWaitingforCertificateGeneration($franchiseid='',$userid='')
	{
		$modelCertificate = new Certificate();
				
		$resultarr=array();
		$appmodel = Certificate::find()->alias('t');
		$appmodel->joinWith(['reviewer as reviewer']);		
		if($userid!='')
		{
			$appmodel = $appmodel->where('t.status = "'.$modelCertificate->arrEnumStatus['open'].'" or (t.status = "'.$modelCertificate->arrEnumStatus['certification_in_process'].'" and reviewer.user_id="'.$userid.'")');
		}	
		$appmodel = $appmodel->andWhere(('t.product_addition_id is null or t.product_addition_id=\'\' or t.product_addition_id=\'0\''));
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['id']=$model->id;
					$data['audit_id']=$model->audit->id;
					$data['app_id']=$model->audit->application->id;
					$data['company_name']=$model->audit->application->companyname;
					$data['contact_name']=$model->audit->application->firstname." ".$model->audit->application->lastname;

					$appstdarr=[];   
					/*  
					$appStandard=$model->audit->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$appstdarr[] = $model->standard->code;
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->created_at);
			
					$resultarr['audit_waiting_for_certificate_generation'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
	public function productAdditionWaitingforCertificateGeneration($franchiseid='',$userid='')
	{
		$modelCertificate = new Certificate();
				
		$resultarr=array();
		$appmodel = Certificate::find()->alias('t');
		$appmodel->joinWith(['reviewer as reviewer']);
		if($userid!='')
		{
			$appmodel = $appmodel->where('t.status = "'.$modelCertificate->arrEnumStatus['open'].'" or (t.status = "'.$modelCertificate->arrEnumStatus['certification_in_process'].'" and reviewer.user_id="'.$userid.'")');
		}	
		$appmodel = $appmodel->andWhere(('t.product_addition_id is not null and t.product_addition_id!=\'\''));	
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['id']=$model->id;
					$data['audit_id']=$model->audit?$model->audit->id:'';
					$data['app_id']=$model->audit?$model->audit->application->id:'';
					$data['company_name']=$model->audit?$model->audit->application->companyname:'';
					$data['contact_name']=$model->audit?$model->audit->application->firstname." ".$model->audit->application->lastname:'';

					$appstdarr=[];  
					/*
					if($model->audit){
						$appStandard=$model->audit->application->applicationstandard;
						if(count($appStandard)>0)
						{
							foreach($appStandard as $std)
							{
								$appstdarr[]=($std->standard?$std->standard->code:'');	
							}
						}
					}
					*/
					$appstdarr[] =$model->standard?$model->standard->code:'';
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->created_at);
			
					$resultarr['product_addition_waiting_for_certificate_generation'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
	public function tcWaitingforOssReview($franchiseid='',$userid='')
	{
		$modelRequest = new Request();
		
		$resultarr=array();
		$appmodel = Request::find()->joinWith('application as application')->alias('t');		
		//$appmodel->joinWith(['reviewer as reviewer']);	
		$appmodel = $appmodel->where('(t.status = "'.$modelRequest->arrEnumStatus['waiting_for_osp_review'].'" or t.status = "'.$modelRequest->arrEnumStatus['pending_with_osp'].'"  ) and application.franchise_id="'.$franchiseid.'" ');	
		/*
		if($userid!='')
		{
			$appmodel = $appmodel->where('t.status = "'.$modelRequest->arrEnumStatus['waiting_for_review'].'" or (t.status = "'.$modelRequest->arrEnumStatus['review_in_process'].'" and reviewer.user_id="'.$userid.'")');
		}		
		*/		
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
                    $data['id']=$model->id;
					$data['app_id']=$model->application->id;
                    $data['company_name']=$model->application->companyname;
                    $data['contact_name']=$model->application->contactname;

                    $reqstdarr=[];     
                    $reqStandard=$model->standard;
                    if(count($reqStandard)>0)
                    {
                        foreach($reqStandard as $std)
                        {
                            $reqstdarr[]=($std->standard?$std->standard->code:'');	
                        }
                    }
					
                    $data["standards"]=$reqstdarr;
                    $data['created_at']=date($this->date_format,$model->updated_at);			
			
					$resultarr['tc_waiting_for_oss_review'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}

	public function tcWaitingforReview($franchiseid='',$userid='')
	{
		$modelRequest = new Request();
		
		$resultarr=array();
		$appmodel = Request::find()->joinWith('application')->alias('t');		
		$appmodel->joinWith(['reviewer as reviewer']);		
		if($userid!='')
		{
			$appmodel = $appmodel->where('t.status = "'.$modelRequest->arrEnumStatus['waiting_for_review'].'" or (t.status = "'.$modelRequest->arrEnumStatus['review_in_process'].'" and reviewer.user_id="'.$userid.'")');
		}				
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
                    $data['id']=$model->id;
					$data['app_id']=$model->application->id;
                    $data['company_name']=$model->application->companyname;
                    $data['contact_name']=$model->application->contactname;

                    $reqstdarr=[];     
                    $reqStandard=$model->standard;
                    if(count($reqStandard)>0)
                    {
                        foreach($reqStandard as $std)
                        {
                            $reqstdarr[]=($std->standard?$std->standard->code:'');	
                        }
                    }
					
                    $data["standards"]=$reqstdarr;
                    $data['created_at']=date($this->date_format,$model->created_at);			
			
					$resultarr['tc_waiting_for_review'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}

	public function waitingForAuditNCDue($userid='')
	{			
		$modelApplication = new Application();
		$modelAudit = new Audit();		
		$modelAuditPlan = new AuditPlan();	
		$modelCertificate = new Certificate();	
				
		$resultarr=array();

		$arrStatuslist = [$modelAudit->arrEnumStatus['audit_completed'], $modelAudit->arrEnumStatus['remediation_in_progress'], $modelAudit->arrEnumStatus['remediation_completed']];

		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();

		$appmodel = Audit::find()->select('*,t.id as id,DATEDIFF(NOW(),`auditp`.`audit_completed_date` ) as due_days')->where(['t.status'=>$arrStatuslist])->alias('t');
		$appmodel = $appmodel->innerJoinWith(['auditplan as auditp']);		
		$appmodel = $appmodel->andWhere('DATEDIFF(NOW(),auditp.audit_completed_date) >=0  AND auditp.audit_completed_date IS NOT NULL AND auditp.audit_completed_date!=\'0000-00-00\'');
		
		$appmodel = $appmodel->orderBy(['due_days' => SORT_DESC]);

		$appmodel = $appmodel->groupBy(['t.id']);
		$appmodel = $appmodel->all();
		//echo $fromDays.'=='.$toDays;
		if(count($appmodel)>0)
		{
			foreach($appmodel as $model)
			{
				$appID = $model->application->id;
				/*
				if($model->application->id!='')
				{
					$ApplicationObj = Application::find()->where(['t.parent_app_id'=>$appID])->alias('t');
					$ApplicationObj = $ApplicationObj->andWhere('t.customer_id='.$userid.' and t.overall_status not in('.$modelApplication->arrEnumOverallStatus['open'].','.$modelApplication->arrEnumOverallStatus['application_rejected'].','.$modelApplication->arrEnumOverallStatus['quotation_rejected'].','.$modelApplication->arrEnumOverallStatus['audit_rejected'].','.$modelApplication->arrEnumOverallStatus['certificate_declined'].')');
					$ApplicationObj = $ApplicationObj->orderBy(['t.id' => SORT_DESC]);
					$ApplicationObj = $ApplicationObj->one();
					if($ApplicationObj !== null)
					{
						//continue;
					}	
				}		
				*/																						
				
				$data=array();
				$data['audit_id']=$model->id;
				$data['app_id']=$model->application->id;
				$data['company_name']=$model->application->companyname;
				$data['contact_name']=$model->application->contactname;
				$data['state']=$model->application->statename;
				$data['country']=$model->application->countryname;
				$data['city']=$model->application->city;
				$data['telephone']=$model->application->telephone;
				$data['audit_completed_date']=$model->auditplan?date($this->date_format,strtotime($model->auditplan->audit_completed_date)):'';
				$data['due_days']=$model->due_days;

				if($model->due_days>=0 && $model->due_days <= 30 ){
					$duecolor = '#00b050';
				}else if($model->due_days>=31 && $model->due_days <= 45 ){
					$duecolor = '#f79647';
				}else{
					$duecolor = '#ff0000';
				}
				$data['due_days_color']=$duecolor;

				$appstdarr=[];   
				$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
				$appstdarr = $auditstandard['stdcode'];
				/*
				$appStandard=$model->application->applicationstandard;
				if(count($appStandard)>0)
				{
					foreach($appStandard as $std)
					{
						$appstdarr[]=($std->standard?$std->standard->code:'');	
					}
				}
				*/
				$data["standards"]=$appstdarr;
				$data['created_at']=date($this->date_format,$model->application->created_at);
				$resultarr[]=$data;
			}
		}
				
		return $resultarr;		
	}
	
	public function waitingForAuditRenewal($userid='')
	{			
		$modelApplication = new Application();
		$modelAudit = new Audit();		
		$modelAuditPlan = new AuditPlan();	
		$modelCertificate = new Certificate();	
				
		$resultarr=array();			
		$enumRenewalAudit = $this->arrEnumRenewalAudit;
		if(count($enumRenewalAudit)>0)
		{
			foreach($enumRenewalAudit as $raKey=>$raVal)
			{
				$eRA=explode('-',$raVal);
				if(is_array($eRA) && count($eRA)>0)
				{
					$fromDays=trim($eRA[0]);
					$toDays=trim($eRA[1]);
					
					/*					
					$appmodel = Audit::find()->select('*,DATEDIFF(cert.certificate_valid_until,NOW()) as due_days,cert.certificate_valid_until as certificate_valid_until,t.id as audit_id')->where(['cert.status'=>$modelCertificate->arrEnumStatus['certificate_generated'],'app.audit_type'=>$modelApplication->arrEnumAuditType['normal']])->alias('t');
					//$appmodel = $appmodel->innerJoinWith(['application as app','auditplan as auditp','certificate as cert']);		
					$appmodel = $appmodel->innerJoinWith(['application as app','auditplan as auditp','certificate as cert']);		
					//$appmodel = $appmodel->andWhere('DATEDIFF(cert.certificate_valid_until,NOW()) >='.$fromDays.' AND DATEDIFF(cert.certificate_valid_until,NOW()) <='.$toDays.' AND cert.certificate_valid_until IS NOT NULL AND cert.certificate_valid_until!=\'0000-00-00\'');
					if($fromDays==0){
						$appmodel = $appmodel->andWhere('DATEDIFF(cert.certificate_valid_until,NOW()) <='.$toDays.' AND cert.certificate_valid_until IS NOT NULL AND cert.certificate_valid_until!=\'0000-00-00\'');
					}else{
						$appmodel = $appmodel->andWhere('DATEDIFF(cert.certificate_valid_until,NOW()) >='.$fromDays.' AND DATEDIFF(cert.certificate_valid_until,NOW()) <='.$toDays.' AND cert.certificate_valid_until IS NOT NULL AND cert.certificate_valid_until!=\'0000-00-00\'');
					}
					*/
					


					//$appmodel = $appmodel->andWhere('DATEDIFF(auditp.audit_valid_until,NOW()) >='.$fromDays.' AND DATEDIFF(auditp.audit_valid_until,NOW()) <='.$toDays.' AND auditp.audit_valid_until IS NOT NULL AND auditp.audit_valid_until!=\'0000-00-00\'');
					
					
					//$appmodel = $appmodel->andWhere('DATEDIFF(auditp.audit_completed_date,NOW()) >='.$fromDays.' AND DATEDIFF(auditp.audit_completed_date,NOW()) <='.$toDays.'');
					//$appmodel = $appmodel->andWhere('auditp.status ='.$modelAuditPlan->arrEnumStatus['finalized'].'');
					//$appmodel = $appmodel->orderBy(['app.id' => SORT_DESC]);
					
					$appmodel = Certificate::find()->select('t.parent_app_id,DATEDIFF(t.certificate_valid_until,NOW()) as due_days,t.certificate_valid_until as certificate_valid_until')->where(['t.status'=>array($modelCertificate->arrEnumStatus['certificate_generated'],$modelCertificate->arrEnumStatus['extension'],$modelCertificate->arrEnumStatus['expired'],$modelCertificate->arrEnumStatus['cancellation'])])->alias('t');
					$appmodel = $appmodel->innerJoinWith(['application as app']);		
					if($fromDays==0){
						$appmodel = $appmodel->andWhere('DATEDIFF(t.certificate_valid_until,NOW()) <='.$toDays.' AND t.certificate_valid_until IS NOT NULL AND t.certificate_valid_until!=\'0000-00-00\'');
					}else{
						$appmodel = $appmodel->andWhere('DATEDIFF(t.certificate_valid_until,NOW()) >='.$fromDays.' AND DATEDIFF(t.certificate_valid_until,NOW()) <='.$toDays.' AND t.certificate_valid_until IS NOT NULL AND t.certificate_valid_until!=\'0000-00-00\'');
					}
					
					$appmodel = $appmodel->orderBy(['due_days' => SORT_ASC]);
					$appmodel = $appmodel->groupBy(['t.parent_app_id']);
					$appmodel = $appmodel->all();
					//echo $fromDays.'=='.$toDays;
					if(count($appmodel)>0)
					{
						foreach($appmodel as $model)
						{
							$appID = $model->parent_app_id;
							if($appID!='')
							{
								$ApplicationObj = Application::find()->where(['t.id'=>$appID])->alias('t')->one();
								if($ApplicationObj !== null)
								{
									$objAddress = $ApplicationObj->currentaddress;
									if($objAddress){
										$data=array();
										$data['audit_id']=$model->id;
										$data['app_id']=$ApplicationObj->id;
										$data['company_name']=$objAddress->company_name;
										$data['contact_name']=$objAddress->first_name.' '.$objAddress->last_name;
										$data['state']=$objAddress->state->name;
										$data['country']=$objAddress->country->name;
										$data['city']=$objAddress->city;
										$data['telephone']=$objAddress->telephone;
										$data['due_days']=$model->due_days;

										$appstdarr=[];     
										$appStandard=$ApplicationObj->applicationstandard;
										if(count($appStandard)>0)
										{
											foreach($appStandard as $std)
											{
												$appstdarr[]=($std->standard?$std->standard->code:'');	
											}
										}
										$data["standards"]=$appstdarr;
										$data['created_at']=date($this->date_format,$ApplicationObj->created_at);
										$resultarr[$raKey][]=$data;	
									}
																	
								}
							}
									
									
							//if($model->application!==null)
							//{
								/*
								$appID = $model->application->id;
								if($model->application->id!='')
								{
									$ApplicationObj = Application::find()->where(['t.parent_app_id'=>$appID])->alias('t');
									$ApplicationObj = $ApplicationObj->andWhere('t.customer_id='.$userid.' and t.overall_status not in('.$modelApplication->arrEnumOverallStatus['open'].','.$modelApplication->arrEnumOverallStatus['application_rejected'].','.$modelApplication->arrEnumOverallStatus['quotation_rejected'].','.$modelApplication->arrEnumOverallStatus['audit_rejected'].','.$modelApplication->arrEnumOverallStatus['certificate_declined'].')');
									$ApplicationObj = $ApplicationObj->orderBy(['t.id' => SORT_DESC]);
									$ApplicationObj = $ApplicationObj->one();
									if($ApplicationObj !== null)
									{
										//continue;
									}	
								}
								*/								
								
								
							//}	
						}						
					}else{
						$resultarr[$raKey]= [];
					}
				}		
			}	
		}	
		return $resultarr;		
	}
	
	public function dueCertificate($userid='')
	{			
		$modelApplication = new Application();
		$modelAudit = new Audit();		
		$modelAuditPlan = new AuditPlan();	
		$modelCertificate = new Certificate();	
				
		$resultarr=array();			
		$enumDueCertificate = $this->arrEnumDueCertificate;
		if(count($enumDueCertificate)>0)
		{
			foreach($enumDueCertificate as $raKey=>$raVal)
			{
				$eRA=explode('-',$raVal);
				if(is_array($eRA) && count($eRA)>0)
				{
					
					$fromDays=trim($eRA[0]);
					$toDays=trim($eRA[1]);
							
					$appmodel = Audit::find()->select('*,DATEDIFF(cert.certificate_valid_until,NOW()) as due_days,cert.certificate_valid_until as certificate_valid_until,cert.id as certificate_id,t.id as audit_id')->where(['cert.status'=>array($modelCertificate->arrEnumStatus['certificate_generated'],$modelCertificate->arrEnumStatus['extension'])])->alias('t');
					//$appmodel = $appmodel->innerJoinWith(['application as app','auditplan as auditp','certificate as cert']);		
					$appmodel = $appmodel->innerJoinWith(['auditplan as auditp','certificate as cert']);
					
					if($fromDays==0){
						$appmodel = $appmodel->andWhere('DATEDIFF(cert.certificate_valid_until,NOW()) <='.$toDays.' AND cert.certificate_valid_until IS NOT NULL AND cert.certificate_valid_until!=\'0000-00-00\'');
					}else{
						$appmodel = $appmodel->andWhere('DATEDIFF(cert.certificate_valid_until,NOW()) >='.$fromDays.' AND DATEDIFF(cert.certificate_valid_until,NOW()) <='.$toDays.' AND cert.certificate_valid_until IS NOT NULL AND cert.certificate_valid_until!=\'0000-00-00\'');
					}
					$appmodel = $appmodel->andWhere(' cert.certificate_status=0');
					//$appmodel = $appmodel->andWhere('DATEDIFF(cert.certificate_valid_until,NOW()) >='.$fromDays.' AND DATEDIFF(cert.certificate_valid_until,NOW()) <='.$toDays.' AND cert.certificate_valid_until IS NOT NULL AND cert.certificate_valid_until!=\'0000-00-00\'');
					//$appmodel = $appmodel->andWhere('auditp.status ='.$modelAuditPlan->arrEnumStatus['finalized'].'');
					//$appmodel = $appmodel->orderBy(['app.id' => SORT_DESC]);
					$appmodel = $appmodel->groupBy(['cert.parent_app_id']);
					$appmodel = $appmodel->orderBy(['due_days' => SORT_ASC]);
					$appmodel = $appmodel->all();
					
					if(count($appmodel)>0)
					{
						foreach($appmodel as $model)
						{
							if($model->application!==null)
							{
								$appID = $model->application->id;
								if($model->application->id!='')
								{
									$ApplicationObj = Application::find()->where(['t.parent_app_id'=>$appID])->alias('t');
									$ApplicationObj = $ApplicationObj->andWhere('t.customer_id='.$userid.' and t.overall_status not in('.$modelApplication->arrEnumOverallStatus['open'].','.$modelApplication->arrEnumOverallStatus['application_rejected'].','.$modelApplication->arrEnumOverallStatus['quotation_rejected'].','.$modelApplication->arrEnumOverallStatus['audit_rejected'].','.$modelApplication->arrEnumOverallStatus['certificate_declined'].')');
									$ApplicationObj = $ApplicationObj->orderBy(['t.id' => SORT_DESC]);
									$ApplicationObj = $ApplicationObj->one();
									if($ApplicationObj !== null)
									{
										//continue;
									}	
								}																								
								
								$data=array();
								$data['audit_id']=$model->audit_id;
								
								$data['due_days']=$model->due_days;
								$data['certificate_id']=$model->certificate_id;


								$data['app_id']=$model->application->id;
								$data['company_name']=$model->application->companyname;
								$data['contact_name']=$model->application->contactname;
								$data['state']=$model->application->statename;
								$data['country']=$model->application->countryname;
								$data['city']=$model->application->city;
								$data['telephone']=$model->application->telephone;
								$appstdarr=[];     
								$appStandard=$model->application->applicationstandard;
								if(count($appStandard)>0)
								{
									foreach($appStandard as $std)
									{
										$appstdarr[]=($std->standard?$std->standard->code:'');	
									}
								}
								$data["standards"]=$appstdarr;

								
								$data['certificate_valid_until']=date($this->date_format,strtotime($model->certificate_valid_until));
								
								
								$data['created_at']=date($this->date_format,$model->application->created_at);
								$resultarr[$raKey][]=$data;
							}	
						}
					}else{
						$resultarr[$raKey]= [];
					}
				}		
			}	
		}	
		return $resultarr;		
	}
	
	public function unitWithdrawWaitingforReview($franchiseid='',$userid='')
	{
		$modelWithdraw = new Withdraw();
		
		$resultarr=array();
		$appmodel = Withdraw::find()->joinWith('application')->alias('t');
		$appmodel->joinWith(['reviewer as reviewer']);		
		if($userid!='')
		{
			$appmodel = $appmodel->where('t.status = "'.$modelWithdraw->arrEnumStatus['waiting_for_review'].'" or (t.status = "'.$modelWithdraw->arrEnumStatus['review_in_process'].'" and reviewer.user_id="'.$userid.'")');
		}
		
		if($this->is_headquarters!=1)
		{
			$appmodel = $appmodel->andWhere('tbl_application.franchise_id="'.$franchiseid.'"');
		}				
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];     
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->created_at);
			
			
					$resultarr['unit_withdraw_waiting_for_review'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
	// ------------ Follow Up Audit Code Start Here ----------------
	public function waitingForFollowupAuditExecution($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelOffer = new Offer();
		$modelAudit = new Audit();
		$modelAuditPlanUnit = new AuditPlanUnit();	
		
		$resultarr=array();		
		$statusArr = [$modelAuditPlanUnit->arrEnumStatus['followup_open'],$modelAuditPlanUnit->arrEnumStatus['followup_inprocess']];
		
		$appmodel = Audit::find()->where(['t.status'=>[$modelAudit->arrEnumStatus['followup_audit_in_progress'],$modelAudit->arrEnumStatus['followup_booked']] ])->alias('t');
		$appmodel = $appmodel->innerJoinWith(['application','auditplan']);
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit as plan_unit','plan_unit.audit_plan_id=tbl_audit_plan.id');
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit_auditor as unit_auditor','plan_unit.id=unit_auditor.audit_plan_unit_id');
		$appmodel = $appmodel->andWhere('unit_auditor.user_id='.$userid.' AND (plan_unit.status='.$modelAuditPlanUnit->arrEnumStatus['followup_inprocess'].' or plan_unit.status='.$modelAuditPlanUnit->arrEnumStatus['followup_open'].' )');
		$appmodel = $appmodel->groupBy(['t.id']);
		$appmodel = $appmodel->all();
		
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['audit_id']=$model->id;
					//$data['offer_id']=$model->offer->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];     
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
			
			
					$resultarr['waiting_for_followup_audit_execution'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}


	public function generateFollowUpAuditPlan($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelOffer = new Offer();
		$modelInvoice = new Invoice();
		$modelAudit = new Audit();
		
		$resultarr=array();		
		
		//$appmodel = Invoice::find()->where(['t.status'=>$modelInvoice->enumStatus['finalized']])->alias('t');
		$appmodel = Audit::find()->where(['t.status'=>[ $modelAudit->arrEnumStatus['followup_open'],$modelAudit->arrEnumStatus['followup_submitted']] ])->alias('t');
		$appmodel = $appmodel->joinWith(['application']);
		/*$appmodel = $appmodel->andWhere('(invoice.status in (0) or invoice.id IS NULL) 
				or (invoice.status in (1) and  (invoice.created_by='.$userid.' or invoice.updated_by='.$userid.' )) ');
				*/
		//$model->joinWith(['offer as ofr','application as app']);
		//$appmodel->joinWith(['audit']);
		$appmodel = $appmodel->join('left join', 'tbl_audit_plan as audit_plan','audit_plan.audit_id=t.id');

		$appmodel = $appmodel->andWhere(' (t.status='.$modelAudit->arrEnumStatus['followup_open'].' and audit_plan.followup_created_by is null) or (t.status='.$modelAudit->arrEnumStatus['followup_submitted'].' and (audit_plan.followup_created_by='.$userid.' or audit_plan.followup_updated_by='.$userid.' )) ');

		//(tbl_audit.status in ('.$modelAudit->arrEnumStatus['followup_open'].')) 
		//or (tbl_audit.status in ('.$modelAudit->arrEnumStatus['followup_submitted'].') and
		//$appmodel = $model->andWhere('t.status='.$modelApplication->arrEnumStatus['approved'].' and (ofr.status='.$modelOffer->enumStatus['customer_approved'].')');
		
		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					//$data['invoice_id']=$model->id;
					$data['id']=$model->id;
					$data['offer_id']=$model->offer_id;
					$data['app_id']=$model->app_id;
					$data['audit_type']=$model->audit_type;
					$data['company_name']=$model->application->companyname;
					if($model->audit_type ==2){
						$data['man_day']=$model->auditplan && $model->auditplan->followup_actual_manday?$model->auditplan->followup_actual_manday:'NA';
					}else{
						$data['man_day']=$model->offer?$model->offer->manday:'';
					}
					
					$data['contact_name']=$model->application->contactname;

					//$data['total_amount']=$model->offerlist->conversion_total_payable;
					//$data['currency']=$model->offerlist->currency;

					$appstdarr=[];     
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*
					$appStandard=$model->application->applicationstandard;
					if($model->application){
						if(count($appStandard)>0)
						{
							foreach($appStandard as $std)
							{
								$appstdarr[]=($std->standard?$std->standard->code:'');	
							}
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
			
			
					$resultarr['waiting_for_followup_audit_plan_generation'][]=$data;
				}
			}
		}
		return $resultarr;		
	}	
	
	public function rejectedFollowUpAuditPlan($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelOffer = new Offer();
		$modelInvoice = new Invoice();
		$modelAudit = new Audit();
		
		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>$modelAudit->arrEnumStatus['followup_rejected_by_customer']])->alias('t');
		$appmodel = $appmodel->joinWith(['application','auditplan','offer as ofr']);
		$appmodel = $appmodel->andWhere('(tbl_audit_plan.followup_created_by='.$userid.' or tbl_audit_plan.followup_updated_by='.$userid.')');

		$appmodel = $appmodel->all();	
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					
					$data['id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['audit_type']=$model->audit_type;
					$data['company_name']=$model->application->companyname;
					if($model->offer && $model->audit_type ==1){
						$data['offer_id']=$model->offer->id;
						$data['total_amount']=$model->offer->offerlist->conversion_total_payable;
						$data['currency']=$model->offer->offerlist->currency;
						$data['man_day']=$model->offer->manday;
					}else if($model->audit_type ==2){
						$data['man_day']=$model->auditplan?$model->auditplan->followup_actual_manday:'';
					}
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];     
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
			
			
					$resultarr['waiting_for_rejected_followup_audit_plan_generation'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
	
	public function waitingForFollowUpAuditReview($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelOffer = new Offer();
		$modelInvoice = new Invoice();
		$modelAudit = new Audit();	
		
		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>$modelAudit->arrEnumStatus['followup_review_in_process']])->alias('t');
		$appmodel = $appmodel->joinWith(['application','auditplan','offer as ofr']);
		$appmodel = $appmodel->andWhere('tbl_audit_plan.followup_application_lead_auditor='.$userid);
		$appmodel = $appmodel->all();
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					
					/*$application = $model->audit->application;
					$applicationstandard = $application->applicationstandard;
					if(count($applicationstandard)>0){
						foreach($applicationstandard as $appstandard){
							$standardIds[] = $appstandard->standard_id;
						}
					}
					$reviewer_canassign=0;
					$UserStandard = UserStandard::find()->where(['user_id'=>$userid, 'standard_id'=>$standardIds,'approval_status'=>2 ])->all();
					 
					if(count($UserStandard) == count($standardIds)>0 ){
						$reviewer_canassign=1;
					}
					if(!$reviewer_canassign){
						continue;
					}
					*/
					$data=array();
					$data['audit_id']=$model->id;
					
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					if($model->offer){
						$data['offer_id']=$model->offer->id;
						$data['total_amount']=$model->offer->offerlist->conversion_total_payable;
						$data['currency']=$model->offer->offerlist->currency;
						$data['man_day']=$model->offer->manday;
					}
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[]; 
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*    
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
			
			
					$resultarr['waiting_for_followup_audit_review'][]=$data;
				}
			}
		}
		return $resultarr;		
	}	
		
	public function waitingForFollowUpAuditInspectionPlan($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelAudit = new Audit();	
		
		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>[
			$modelAudit->arrEnumStatus['followup_review_completed'],
			$modelAudit->arrEnumStatus['followup_inspection_plan_inprocess']
			]])->alias('t');
		$appmodel = $appmodel->joinWith(['application','auditplan','offer as ofr']);
		$appmodel = $appmodel->andWhere('tbl_audit_plan.followup_application_lead_auditor='.$userid);
		$appmodel = $appmodel->all();
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['audit_id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					if($model->offer){
						$data['total_amount']=$model->offer->offerlist->conversion_total_payable;
						$data['man_day']=$model->offer->manday;
					}
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];  
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*   
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
			
			
					$resultarr['waiting_for_followup_audit_inspection_plan'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
	public function waitingForFollowUpAuditReviewerReview($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelAudit = new Audit();
		$modelAuditPlanUnit = new AuditPlanUnit();
		$modelAuditPlan = new AuditPlan();	
		
		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>$modelAudit->arrEnumStatus['followup_audit_in_progress']])->alias('t');
		$appmodel = $appmodel->joinWith(['application','auditplan']);
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_reviewer as plan_reviewer','plan_reviewer.audit_plan_id=tbl_audit_plan.id');
		$appmodel = $appmodel->andWhere('plan_reviewer.reviewer_id='.$userid.' AND plan_reviewer.reviewer_status=1 AND tbl_audit_plan.status ='.$modelAuditPlan->arrEnumStatus['followup_reviewinprogress'].'');
		$appmodel = $appmodel->groupBy(['t.id']);
		$appmodel = $appmodel->all();
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['audit_id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
					$resultarr['waiting_for_followup_audit_reviewer_review'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
	
	public function waitingForFollowUpAuditLeadAuditorApproval($franchiseid='',$userid='')
	{
		$modelApplication = new Application();
		$modelAudit = new Audit();
		$modelAuditPlanUnit = new AuditPlanUnit();
		$modelAuditPlan = new AuditPlan();	
		
		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>$modelAudit->arrEnumStatus['followup_audit_in_progress']])->alias('t');
		$appmodel = $appmodel->joinWith(['application','auditplan']);
		//$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit as plan_unit','plan_unit.audit_plan_id=tbl_audit_plan.id');
		$appmodel = $appmodel->andWhere('tbl_audit_plan.followup_application_lead_auditor='.$userid.' AND tbl_audit_plan.status ='.$modelAuditPlan->arrEnumStatus['followup_waiting_for_lead_auditor'].'');
		$appmodel = $appmodel->groupBy(['t.id']);
		$appmodel = $appmodel->all();
		if($appmodel !== null)
		{
			if(count($appmodel)>0)
			{
				foreach($appmodel as $model)
				{
					$data=array();
					$data['audit_id']=$model->id;
					$data['app_id']=$model->application->id;
					$data['company_name']=$model->application->companyname;
					$data['contact_name']=$model->application->contactname;

					$appstdarr=[];
					$auditstandard = Yii::$app->globalfuns->getAuditStandard($model->id);
					$appstdarr = $auditstandard['stdcode'];
					/*     
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					*/
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
					$resultarr['waiting_for_followup_audit_lead_auditor_approval'][]=$data;
				}
			}
		}
		return $resultarr;		
	}
	// ------------ Follow Up Audit Code End Here ------------------
}
