<?php

namespace app\modules\master\models;

use Yii;
use yii\base\Model;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\offer\models\Offer;
use app\modules\offer\models\OfferList;
use app\models\Enquiry;
use app\models\EnquiryStandard;

use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditPlanUnit;
use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\AuditPlanUnitExecutionChecklist;

use app\modules\changescope\models\ProductAddition;
use app\modules\changescope\models\Withdraw;
use app\modules\changescope\models\StandardAddition;
use app\modules\certificate\models\Certificate;

use app\modules\transfercertificate\models\Request;
use app\modules\transfercertificate\models\RequestStandard;

class CustomerDashboard extends Model
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
	
    public function rules()
    {
        return [            
        ];
    }

	public function applicationWaitingForSubmission($userid='')
	{
		$resultarr=array();
		///$model = Enquiry::find()->where(['or',['app_id'=>null],['app_id'=>'']]);
		$model = Enquiry::find()->where('app_id=\'\' or app_id=\'null\' or  app_id is null');
        //if($model!== null)
        //{
		if($this->resource_access != 1)
		{
			if($this->user_type==3){
				$model = $model->andWhere('franchise_id="'.$userid.'"');
			}else if($this->user_type==2){
				$model = $model->andWhere('customer_id="'.$userid.'"');
			}
		}
		
		$model = $model->all();	
		if(count($model)>0)
		{
			foreach($model as $enq)
			{
				$data=array();
				$data['id']=$enq->id;
				$data['contact_name']=(isset($enq->contact_name))?$enq->contact_name:"";
				$data['company_name']=(isset($enq->company_name))?$enq->company_name:"";        

				$standards='';
				$es=$enq->enquirystandard; 
				$eStandardArr=array();
				if(count($es)>0)
				{
					foreach($es as $enquirystandard)
					{
						$eStandardArr[]=$enquirystandard->standard->code;
					}
				}
				
				$data['standards']=$eStandardArr;
				$data['customer_id']=(isset($enq->customer_id))?$enq->customer_id:"";
				$data['franchise_id']=(isset($enq->franchise_id))?$enq->franchise_id:"";
				$data['created_at']=date($this->date_format,$enq->created_at);
				$resultarr['pendingactions'][]=$data;
			}
		}
            
        //}
		return $resultarr;		
	}
	
	public function applicationReAssign($userid='')
	{
		$resultarr=array();
		$ApplicationModel = new Application();
        $appmodel = Application::find()->where(['status'=> $ApplicationModel->arrEnumStatus['pending_with_customer']]);
        //$appmodel = $appmodel->andWhere(['!=','audit_type',$ApplicationModel->arrEnumAuditType['standard_addition']]);
        if($this->resource_access != 1)
        {
            if($this->user_type==2)
            {
                $appmodel = $appmodel->andWhere('customer_id="'.$userid.'"');
            }elseif($this->user_type==3){
                $appmodel = $appmodel->andWhere('franchise_id="'.$userid.'"');
            }
        }       
        
        $appmodel = $appmodel->all();	
        if($appmodel !== null)
        {
            if(count($appmodel)>0)
            {
                foreach($appmodel as $model)
                {
                    $data=array();
                    $data['addition_id']='';
                    if($model->audit_type == 4){
                        $addition = StandardAddition::find()->where(['app_id'=>$model->parent_app_id,'new_app_id'=>$model->id])->one();
                        if($addition!==null){
                            $data['addition_id']=$addition->id;
                        }
                    }

                   
                    $data['id']=$model->id;
                    $data['parent_app_id']=$model->parent_app_id;
                    $data['company_name']=$model->companyname;
                    $data['contact_name']=$model->contactname;
                    $data['audit_type']=$model->audit_type;

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
            
            
                    $resultarr['re_initiate_pending_actions'][]=$data;
                }
            }
        }
		return $resultarr;		
	}
	
	public function offerWaitingForApproval($userid='')
	{
		$resultarr=array();
		$Offer = new Offer();
		$offermodel = Offer::find();
        $offermodel = $offermodel->joinWith('application')->where(['tbl_offer.status'=> $Offer->enumStatus['waiting-for-customer-approval']]);
        if($this->resource_access != 1)
        {
            if($this->user_type==2)
            {
                //$offermodel = $offermodel->andWhere('tbl_application.created_by="'.$userid.'"');
				$offermodel = $offermodel->andWhere('tbl_application.customer_id="'.$userid.'"');
            }elseif($this->user_type==3){                
				$offermodel = $offermodel->andWhere('tbl_application.franchise_id="'.$userid.'"');
            }
        }
        $offermodel = $offermodel->all();

        if($offermodel !== null)
        {
            if(count($offermodel)>0)
            {
                foreach($offermodel as $model)
                {
                    $data=array();
                    $data['id']=$model->id;
                    $data['app_id']=$model->app_id;
                    $data['offer_code']=$model->offer_code;
                    $data['manday']=$model->manday;
                    $data['currency']=$model->offerlist->currency;
                    $data['grand_total_fee']=$model->offerlist->grand_total_fee;
                    $data['company_name']=$model->application->companyname;
                    $data['contact_name']=$model->application->contactname;
                    $data['created_at']=date($this->date_format,$model->created_at);
                    $resultarr['offer_waiting_for_approvals'][]=$data;
                }
            }
		}
		return $resultarr;		
	}
	
	
	public function auditReportWaitingForSubmission($userid='')
	{
		$resultarr=array();
		$Offer = new Offer();
		$offermodel = Offer::find();
        $offermodel = $offermodel->joinWith('application')->where(['tbl_offer.status'=> $Offer->enumStatus['waiting_for_audit_report'] ]);
        if($this->resource_access != 1)
        {
            if($this->user_type==2)
            {
                $offermodel = $offermodel->andWhere('tbl_application.customer_id="'.$userid.'"');
            }elseif($this->user_type==3){                
				$offermodel = $offermodel->andWhere('tbl_application.franchise_id="'.$userid.'"');
            }
        }
        $offermodel = $offermodel->all();

        if($offermodel !== null)
        {
            if(count($offermodel)>0)
            {
                foreach($offermodel as $model)
                {
                    $data=array();
                    $data['id']=$model->id;
                    $data['app_id']=$model->app_id;
                    $data['offer_code']=$model->offer_code;
                    $data['manday']=$model->manday;
                    $data['currency']=$model->offerlist->currency;
                    $data['grand_total_fee']=$model->offerlist->grand_total_fee;
                    $data['company_name']=$model->application->companyname;
                    $data['contact_name']=$model->application->contactname;
                    $data['created_at']=date($this->date_format,$model->created_at);
                    $resultarr['waiting_for_audit_report'][]=$data;
                }
            }
		}
		return $resultarr;		
	}
	
	
	
	public function auditPlanWaitingForApprovals($userid='')
	{
		$resultarr=array();
		
		$auditModelEnum = new Audit();
		$auditmodel = Audit::find();
		
        $auditmodel = $auditmodel->joinWith(['application','offer','invoice'])->where(['tbl_audit.status'=> $auditModelEnum->arrEnumStatus['awaiting_for_customer_approval'] ]);
        if($this->resource_access != 1)
        {
            if($this->user_type==2)
            {
                //$auditmodel = $auditmodel->andWhere('tbl_application.created_by="'.$userid.'"');
				$auditmodel = $auditmodel->andWhere('tbl_application.customer_id="'.$userid.'"');
            }elseif($this->user_type==3){
               $auditmodel = $auditmodel->andWhere('tbl_application.franchise_id="'.$userid.'"');
            }
			
			
        }
        $auditmodel = $auditmodel->all();

        if($auditmodel !== null)
        {
            if(count($auditmodel)>0)
            {
                foreach($auditmodel as $model)
                {
                    $data=array();
                    $data['id']=$model->id;
					$data['app_id']=$model->app_id;
					$data['offer_id']=$model->offer_id;
                    $data['invoice_id']=$model->invoice_id;
                    if($model->offer){
                        $data['offer_code']=$model->offer->offer_code;
                        $data['currency']=$model->offer->offerlist->currency;
                        $data['manday']=$model->offer->manday;
                        $data['grand_total_fee']=$model->offer->offerlist->grand_total_fee;
                    }
                    
                    $data['company_name']=$model->application->companyname;
                    $data['contact_name']=$model->application->contactname;
                    $data['created_at']=date($this->date_format,$model->created_at);
                    $resultarr['audit_plan_waiting_for_approvals'][]=$data;
                }
            }
        }
		return $resultarr;		
    }

    public function followupAuditPlanWaitingForApprovals($userid='')
	{
		$resultarr=array();
		
		$auditModelEnum = new Audit();
		$auditmodel = Audit::find();
		
        $auditmodel = $auditmodel->joinWith(['application','offer'])->where(['tbl_audit.status'=> $auditModelEnum->arrEnumStatus['awaiting_followup_customer_approval'] ]);
        if($this->resource_access != 1)
        {
            if($this->user_type==2)
            {
                //$auditmodel = $auditmodel->andWhere('tbl_application.created_by="'.$userid.'"');
				$auditmodel = $auditmodel->andWhere('tbl_application.customer_id="'.$userid.'"');
            }elseif($this->user_type==3){
               $auditmodel = $auditmodel->andWhere('tbl_application.franchise_id="'.$userid.'"');
            }
			
			
        }
        $auditmodel = $auditmodel->all();

        if($auditmodel !== null)
        {
            if(count($auditmodel)>0)
            {
                foreach($auditmodel as $model)
                {
                    $data=array();
                    $data['id']=$model->id;
					$data['app_id']=$model->app_id;
					
                    if($model->audit_type ==2){
						$data['manday']=$model->auditplan && $model->auditplan->followup_actual_manday?$model->auditplan->followup_actual_manday:'NA';
					}else if($model->offer){
                        $data['offer_id']=$model->offer_id;
                        $data['invoice_id']=$model->invoice_id;

                        $data['offer_code']=$model->offer->offer_code;
                        $data['currency']=$model->offer->offerlist->currency;
                        $data['manday']=$model->offer->manday;
                        $data['grand_total_fee']=$model->offer->offerlist->grand_total_fee;
                    }
                    
                    $data['company_name']=$model->application->companyname;
                    $data['contact_name']=$model->application->contactname;
                    $data['created_at']=date($this->date_format,$model->created_at);
                    $resultarr['followup_audit_plan_waiting_for_approvals'][]=$data;
                }
            }
        }
		return $resultarr;		
    }
    

    public function auditWaitingForRemediation($userid='')
	{
		$modelApplication = new Application();
		$modelAudit = new Audit();
		$modelAuditPlanUnit = new AuditPlanUnit();
		$modelAuditPlan = new AuditPlan();	
		
		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>[$modelAudit->arrEnumStatus['remediation_in_progress'],$modelAudit->arrEnumStatus['audit_completed']] ])->alias('t');
		$appmodel = $appmodel->innerJoinWith(['application','auditplan']);
		//$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit as plan_unit','plan_unit.audit_plan_id=tbl_audit_plan.id');
        //$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit_execution as plan_execution','plan_execution.audit_plan_unit_id=plan_unit.id');
        if($this->user_type==2)
        {
            $appmodel = $appmodel->andWhere('tbl_application.customer_id='.$userid);
        }elseif($this->user_type==3){
            $appmodel = $appmodel->andWhere('tbl_application.franchise_id='.$userid);
        }
        $appmodel = $appmodel->andWhere('tbl_audit_plan.status IN('.$modelAuditPlan->arrEnumStatus['remediation_in_progress'].','.$modelAuditPlan->arrEnumStatus['audit_completed'].')');
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
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
					$resultarr['audit_waiting_for_remediation'][]=$data;
				}
			}
		}
		return $resultarr;
		
    }
    
    public function waitingForCustomerRejectedRemediationCorrection($userid='')
	{
		$modelApplication = new Application();
		$modelAudit = new Audit();
		$modelAuditPlanUnit = new AuditPlanUnit();
		$modelAuditPlan = new AuditPlan();	
		
		$modelExecutionChecklist = new AuditPlanUnitExecutionChecklist();

		$resultarr=array();		
		
		$appmodel = Audit::find()->where(['t.status'=>$modelAudit->arrEnumStatus['remediation_in_progress']])->alias('t');
		$appmodel = $appmodel->innerJoinWith(['application','auditplan']);
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit as plan_unit','plan_unit.audit_plan_id=tbl_audit_plan.id');
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit_execution as execution','execution.audit_plan_unit_id=plan_unit.id');
		$appmodel = $appmodel->join('inner join', 'tbl_audit_plan_unit_execution_checklist as execution_checklist','execution_checklist.audit_plan_unit_execution_id=execution.id');

        if($this->user_type==2)
        {
            $appmodel = $appmodel->andWhere('tbl_application.customer_id='.$userid);
        }elseif($this->user_type==3){
            $appmodel = $appmodel->andWhere('tbl_application.franchise_id='.$userid);
        }

		$appmodel = $appmodel->andWhere('execution_checklist.status ='.$modelExecutionChecklist->arrEnumStatus['auditor_change_request'].'');
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
					$appStandard=$model->application->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{
							$appstdarr[]=($std->standard?$std->standard->code:'');	
						}
					}
					$data["standards"]=$appstdarr;
					$data['created_at']=date($this->date_format,$model->application->created_at);
					$resultarr['waiting_for_customer_rejected_remediation_correction'][]=$data;
				}
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
		
		/*
		application_rejected
		quotation_rejected
		audit_rejected
		certificate_declined
		*/
		/*
		$appmodel = Audit::find()->where(['cert.status'=>$modelCertificate->arrEnumStatus['certificate_generated']])->alias('t');
		$appmodel = $appmodel->innerJoinWith(['application as app','auditplan as auditp','certificate as cert']);		
		$appmodel = $appmodel->andWhere('app.customer_id='.$userid);
        $appmodel = $appmodel->andWhere('DATEDIFF(auditp.audit_completed_date,NOW()) <=90 AND auditp.audit_completed_date IS NOT NULL AND auditp.audit_completed_date!=\'0000-00-00\'');
		$appmodel = $appmodel->andWhere('auditp.status ='.$modelAuditPlan->arrEnumStatus['finalized'].'');
		$appmodel = $appmodel->orderBy(['app.id' => SORT_DESC]);
		*/


		$appmodel = Audit::find()->where(['cert.status'=>array($modelCertificate->arrEnumStatus['certificate_generated'],$modelCertificate->arrEnumStatus['extension'],$modelCertificate->arrEnumStatus['expired'])])->alias('t');
		$appmodel = $appmodel->innerJoinWith(['application as app','auditplan as auditp','certificate as cert']);		
		$appmodel = $appmodel->andWhere('app.customer_id='.$userid);
		$appmodel = $appmodel->andWhere('DATEDIFF(cert.certificate_valid_until,NOW()) <=90 AND cert.certificate_valid_until IS NOT NULL AND cert.certificate_valid_until!=\'0000-00-00\'');
		$appmodel = $appmodel->andWhere('auditp.status ='.$modelAuditPlan->arrEnumStatus['finalized'].'');
		$appmodel = $appmodel->orderBy(['app.id' => SORT_DESC]);
		//$appmodel = $appmodel->groupBy(['t.id']);

		//$appmodel = Audit::find()->select('*,DATEDIFF(cert.certificate_valid_until,NOW()) as due_days,cert.certificate_valid_until as certificate_valid_until,t.id as audit_id')->where(['cert.status'=>$modelCertificate->arrEnumStatus['certificate_generated']])->alias('t');
		//$appmodel = $appmodel->innerJoinWith(['auditplan as auditp','certificate as cert']);		
		
		


		$appmodel = $appmodel->groupBy(['t.id']);
		$model = $appmodel->one();
		if($model !== null)
		{
			//if(count($appmodel)>0)
			//{
				//foreach($appmodel as $model)
				//{
					$appID = $model->application->id;
					if($model->application->id!='')
					{
						$ApplicationObj = Application::find()->where(['t.parent_app_id'=>$appID])->alias('t');
						$ApplicationObj = $ApplicationObj->andWhere('t.customer_id='.$userid.' and t.audit_type in ('.$modelApplication->arrEnumAuditType['renewal'].') and t.overall_status not in('.$modelApplication->arrEnumOverallStatus['open'].','.$modelApplication->arrEnumOverallStatus['application_rejected'].','.$modelApplication->arrEnumOverallStatus['quotation_rejected'].','.$modelApplication->arrEnumOverallStatus['audit_rejected'].','.$modelApplication->arrEnumOverallStatus['certificate_declined'].')');
						$ApplicationObj = $ApplicationObj->orderBy(['t.id' => SORT_DESC]);
						$ApplicationObj = $ApplicationObj->one();
						if($ApplicationObj !== null)
						{
							return $resultarr;
						}	
					}																								
					
					$data=array();
					$data['audit_id']=$model->id;
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
					$data['created_at']=date($this->date_format,$model->application->created_at);
					$resultarr['waiting_for_customer_audit_renewal'][]=$data;
				//}
			//}
		}
		return $resultarr;		
	}

	public function productAdditionReAssign($userid='')
	{
		$modelProductAddition = new ProductAddition();
		
		$resultarr=array();		
		$appmodel = ProductAddition::find()->joinWith('application')->alias('t')->where(['t.status'=> $modelProductAddition->arrEnumStatus['pending_with_customer']]);
		//$appmodel = ProductAddition::find()->joinWith('application');
        if($this->resource_access != 1)
        {
            if($this->user_type==2)
            {
                $appmodel = $appmodel->andWhere('tbl_application.customer_id="'.$userid.'"');
            }elseif($this->user_type==3){
                $appmodel = $appmodel->andWhere('tbl_application.franchise_id="'.$userid.'"');
            }
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
            
            
                    $resultarr['product_addition_reassign'][]=$data;
                }
            }
        }
		return $resultarr;		
	}
	
	public function tcReAssign($userid='')
	{
		$modelRequest = new Request();
		$resultarr=array();
		
		$appmodel = Request::find()->joinWith('application')->alias('t')->where(['t.status'=> $modelRequest->arrEnumStatus['pending_with_customer']]);		
        if($this->resource_access != 1)
        {
            if($this->user_type==2)
            {
                $appmodel = $appmodel->andWhere('tbl_application.customer_id="'.$userid.'"');
            }elseif($this->user_type==3){
                $appmodel = $appmodel->andWhere('tbl_application.franchise_id="'.$userid.'"');
            }
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
            
            
                    $resultarr['tc_reassign'][]=$data;
                }
            }
        }
		return $resultarr;		
	}
	
	
	public function unitWithdrawReAssign($userid='')
	{
		$modelWithdraw = new Withdraw();
		
		$resultarr=array();		
		$appmodel = Withdraw::find()->joinWith('application')->alias('t')->where(['t.status'=> $modelWithdraw->arrEnumStatus['pending_with_customer']]);
		if($this->resource_access != 1)
        {
            if($this->user_type==2)
            {
                $appmodel = $appmodel->andWhere('tbl_application.customer_id="'.$userid.'"');
            }elseif($this->user_type==3){
                $appmodel = $appmodel->andWhere('tbl_application.franchise_id="'.$userid.'"');
            }
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
            
            
                    $resultarr['unit_withdraw_reassign'][]=$data;
                }
            }
        }
		return $resultarr;		
	}
	
	public function activeStandardList($userid='')
	{
		$modelWithdraw = new Withdraw();
		
		$resultarr=array();		
		$appmodel = Withdraw::find()->joinWith('application')->alias('t')->where(['t.status'=> $modelWithdraw->arrEnumStatus['pending_with_customer']]);
		if($this->resource_access != 1)
        {
            if($this->user_type==2)
            {
                $appmodel = $appmodel->andWhere('tbl_application.customer_id="'.$userid.'"');
            }elseif($this->user_type==3){
                $appmodel = $appmodel->andWhere('tbl_application.franchise_id="'.$userid.'"');
            }
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
            
            
                    $resultarr['unit_withdraw_reassign'][]=$data;
                }
            }
        }
		return $resultarr;		
	}
}
