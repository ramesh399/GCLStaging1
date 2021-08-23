<?php

namespace app\modules\master\models;

use Yii;
use yii\base\Model;

use app\modules\application\models\Application;
use app\modules\offer\models\Offer;

use app\modules\changescope\models\ProductAddition;
use app\modules\changescope\models\Withdraw;
use app\modules\transfercertificate\models\Request;
use app\modules\transfercertificate\models\RequestStandard;

class FranchiseDashboard extends Model
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

	public function productAdditionForReview($franchiseid='')
	{
		$modelProductAddition = new ProductAddition();
		$resultarr=array();
		$appmodel = ProductAddition::find()->joinWith('application')->alias('t')->where(['t.status'=> $modelProductAddition->arrEnumStatus['waiting_for_osp_review']]);
		//$appmodel = ProductAddition::find()->joinWith('application');
		if($this->resource_access != 1)
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
            
            
                    $resultarr['product_addition_review'][]=$data;
                }
            }
        }
		return $resultarr;
		
	}
	
	
	public function productAdditionForReviewReAssign($franchiseid='')
	{
		$modelProductAddition = new ProductAddition();
		$resultarr=array();
		$appmodel = ProductAddition::find()->joinWith('application')->alias('t')->where(['t.status'=> $modelProductAddition->arrEnumStatus['pending_with_osp']]);
		//$appmodel = ProductAddition::find()->joinWith('application');
		if($this->resource_access != 1)
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
            
            
                    $resultarr['product_addition_review_reassign'][]=$data;
                }
            }
        }
		return $resultarr;
		
	}

    public function tcWaitingForReview($franchiseid='')
	{
		$modelRequest = new Request();
		$resultarr=array();
		$appmodel = Request::find()->joinWith('application')->alias('t')->where(['t.status'=> $modelRequest->arrEnumStatus['waiting_for_osp_review']]);
		//$appmodel = ProductAddition::find()->joinWith('application');
		if($this->resource_access != 1)
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
	
	public function tcWaitingForReviewReAssign($franchiseid='')
	{
		$modelRequest = new Request();
		$resultarr=array();
		$appmodel = Request::find()->joinWith('application')->alias('t')->where(['t.status'=> $modelRequest->arrEnumStatus['pending_with_osp']]);
		//$appmodel = ProductAddition::find()->joinWith('application');
		if($this->resource_access != 1)
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
            
                    $resultarr['tc_waiting_for_review_reassign'][]=$data;
                }
            }
        }
		return $resultarr;
		
	}	
	
	public function unitWithdrawWaitingForReview($franchiseid='')
	{
		$modelWithdraw = new Withdraw();
		$resultarr=array();
		$appmodel = Withdraw::find()->joinWith('application')->alias('t')->where(['t.status'=> $modelWithdraw->arrEnumStatus['waiting_for_osp_review']]);
		if($this->resource_access != 1)
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
            
                    $resultarr['unit_withdraw_review'][]=$data;
                }
            }
        }
		return $resultarr;
		
	}
	
	
	public function unitWithdrawWaitingForReviewReAssign($franchiseid='')
	{
		$modelWithdraw = new Withdraw();
		$resultarr=array();
		$appmodel = Withdraw::find()->joinWith('application')->alias('t')->where(['t.status'=> $modelWithdraw->arrEnumStatus['pending_with_osp']]);
		if($this->resource_access != 1)
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
            
            
                    $resultarr['unit_withdraw_review_reassign'][]=$data;
                }
            }
        }
		return $resultarr;
		
	}	
	
    
    public function waitingForOssofferApproval($franchiseid='')
	{
		$modelApplication = new Application();
		$modelOffer = new Offer();
		
		$resultarr=array();
		$appmodel = Offer::find()->alias('t');
		$appmodel->joinWith(['application as app']);
					
		if($this->is_headquarters!=1){
			$appmodel = $appmodel->where('app.franchise_id="'.$franchiseid.'"');
		}
		$appmodel = $appmodel->andWhere(' t.status='.$modelOffer->enumStatus['waiting-for-oss-approval'].' ');
		
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
			
			
					$resultarr['waiting_for_oss_offer_approval'][]=$data;
				}
			}
		}
		return $resultarr;
		
    }
    
    public function waitingForOssReinitiatedofferApproval($franchiseid='')
	{
		$modelApplication = new Application();
		$modelOffer = new Offer();
		
		$resultarr=array();
		$appmodel = Offer::find()->alias('t');
		$appmodel->joinWith(['application as app']);
					
		if($this->is_headquarters!=1){
			$appmodel = $appmodel->where('app.franchise_id="'.$franchiseid.'"');
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
			
			
					$resultarr['waiting_for_oss_reinitated_offer_approval'][]=$data;
				}
			}
		}
		return $resultarr;
		
	}
}
