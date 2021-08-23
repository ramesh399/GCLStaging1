<?php
namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

use app\modules\application\models\Application;
use app\modules\offer\models\Offer;
use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\AuditPlanUnitAuditor;
use app\modules\audit\models\AuditPlanReviewer;

class UserRoleComponent extends Component
{ 
	public $user_id,$user_type,$role,$rules,$franchiseid,$resource_access,$is_headquarters,$role_chkid;	
	public function __construct()
	{ 


		$jwt = Yii::$app->jwt;		
		$token = $jwt->getToken();		
		$this->user_id = $token->getClaim('uid');				
		$this->user_type = $token->getClaim('user_type');
		$this->role = $token->getClaim('role');
		$this->rules = $token->getClaim('rules');
		$this->franchiseid = $token->getClaim('franchiseid');
		$this->resource_access = $token->getClaim('resource_access');
		$this->is_headquarters = $token->getClaim('is_headquarters');
		$this->role_chkid = $token->getClaim('role_chkid');

		
	}
	
    public function hasRights($accessRights)
	{
		$PermissionDenial=false;	
		// print_r($this->rules);						
		if($this->isAdmin()){
			$PermissionDenial=true;
		}elseif(is_array($this->rules) && is_array($accessRights) && $this->user_type==Yii::$app->params['user_type']['user'] && array_intersect($accessRights,$this->rules)){
			$PermissionDenial=true;
		}	
		return $PermissionDenial;	
	}


	public function hasRightsWithFranchise($accessRights,$franchise_id)
	{
		$PermissionDenial=false;							
		if($this->isAdmin()){
			$PermissionDenial=true;
		}elseif(is_array($this->rules) 
			&& is_array($accessRights) 
			&& $this->user_type==Yii::$app->params['user_type']['user'] && array_intersect($accessRights,$this->rules)
			){
				if($this->is_headquarters !=1){
					if($this->franchiseid == $franchise_id){
						$PermissionDenial=true;
					}
				}else{
					$PermissionDenial=true;
				}
			
		}	
		return $PermissionDenial;	
	}

	public function hasOssRights($franchise_id)
	{
		$PermissionDenial = false;
		if($this->user_type==Yii::$app->params['user_type']['franchise']  ){
			if($this->resource_access == '5' && $franchise_id == $this->franchiseid ){
				$PermissionDenial = true;
			}else if($franchise_id == $this->user_id){
				$PermissionDenial = true;
			}
		}	
		return $PermissionDenial;	
	}
	
	public function isAdmin()
	{
		$PermissionDenial=false;				
		if($this->resource_access==1)
		{
			$PermissionDenial=true;	
		}
		return $PermissionDenial;	
	}
	
	public function isOSSAdmin()
	{
		$PermissionDenial=false;				
		if($this->resource_access==5)
		{
			$PermissionDenial=true;	
		}
		return $PermissionDenial;	
	}	
	
	public function isUser()
	{
		$PermissionDenial=false;				
		if($this->user_type==1)
		{
			$PermissionDenial=true;	
		}
		return $PermissionDenial;
	}
	
	public function isCustomer()
	{
		$PermissionDenial=false;				
		if($this->user_type==2)
		{
			$PermissionDenial=true;	
		}
		return $PermissionDenial;
	}
	
	public function isOSS()
	{
		$PermissionDenial=false;				
		if($this->user_type==3)
		{
			$PermissionDenial=true;	
		}
		return $PermissionDenial;	
	}
	
	public function isOSSUser()
	{
		$PermissionDenial=false;
		if($this->user_type== Yii::$app->params['user_type']['user'] && $this->is_headquarters!=1 )
		{	
			$PermissionDenial=true;		
		}
		return $PermissionDenial;
	}
	
	public function canDoCommonUpdate($status,$type)
	{
		$PermissionDenial=false;	
		if($status==0){
			if($this->hasRights(array('activate_'.$type)))
			{
				$PermissionDenial=true;
			}				
		}elseif($status==1){
			if($this->hasRights(array('deactivate_'.$type)))
			{
				$PermissionDenial=true;
			}				
		}elseif($status==2){
			if($this->hasRights(array('delete_'.$type)))
			{
				$PermissionDenial=true;
			}
		}else{
			$PermissionDenial=false;
		}
		return $PermissionDenial;
	}
	
	public function isValidApplication($appID)
	{
		$PermissionDenial=false;
		$model = Application::find()->alias('app')->where(['app.id'=>$appID]);
		$model = $model->join('inner join', 'tbl_certificate as cert','cert.parent_app_id=app.id and cert.certificate_status=0');
		if($this->user_type==2)
		{
			$model = $model->andWhere(['app.customer_id'=>$this->user_id]);			
		}
		$model = $model->one();
		if($model!==null)
		{
			$PermissionDenial=true;
		}		
		return $PermissionDenial;
	}

	public function canViewApplication($appID)
	{
		$PermissionDenial=false;		
		if($this->isAdmin()){
			$PermissionDenial=true;
		}else {
			$model = Application::find()->alias('app')->where(['app.id'=>$appID]);
			if($this->user_type==2){
				$model = $model->andWhere(['app.customer_id'=>$this->user_id]);			
			}else if($this->user_type==3 && $this->is_headquarters!='1'){
				$userid = $this->user_id;
				if($this->resource_access == '5'){
					$userid = $this->franchiseid;
				}
				$model = $model->andWhere(['app.franchise_id' => $userid]);
			}
			$model = $model->one();
			if($model!==null)
			{
				$PermissionDenial=true;
			}
		}	
		return $PermissionDenial;
	}
	
	public function canViewAuditReport($resdata)
	{
		$PermissionDenial=false;				
		if($this->isAdmin())
		{
			$PermissionDenial=true;
		}else{

			$offerID = isset($resdata['offer_id'])?$resdata['offer_id']:'';
			$app_id = isset($resdata['app_id'])?$resdata['app_id']:'';
			$audit_id = isset($resdata['audit_id'])?$resdata['audit_id']:'';


			$model = Offer::find()->alias('ofr');
			$model = $model->join('inner join', 'tbl_application as app','app.id=ofr.app_id');
			if($app_id =='' && $audit_id=='' && $offerID==''){

				return false;
			}
			if($app_id !=''){


				$model = $model->andWhere(['app.id'=>$app_id]);
			}
			if($offerID !=''){

				$model = $model->andWhere(['ofr.id'=>$offerID]);
			}

			//->where(['ofr.id'=>$offerID])
			if($this->user_type==2){


				$model = $model->andWhere(['app.customer_id'=>$this->user_id]);			
			}else if($this->user_type==3 && $this->is_headquarters!='1'){
				$userid = $this->user_id;
				if($this->resource_access == '5'){


					$userid = $this->franchiseid;
				}
				$model = $model->andWhere(['app.franchise_id' => $userid]);
			}else if($this->user_type==1){
				$userid = $this->user_id;
				//$conditions = ' (ofr.created_by='.$userid.' or ofr.updated_by='.$userid.') ';
				$conditions = '';
				$sqlcondition = [];
				if($this->user_type== 1  && in_array('generate_offer',$this->rules)){

					$model = $model->join('left join', 'tbl_offer_list as list','list.offer_id=ofr.id and list.is_latest=1');
					$sqlcondition[] = ' (ofr.created_by ='.$userid.' or ofr.updated_by ='.$userid.' or list.created_by='.$userid.' or list.updated_by='.$userid.' ) ';
				}
				if($this->user_type== 1  && in_array('offer_approvals',$this->rules)){

					$model = $model->join('left join', 'tbl_offer_comment as comment','comment.offer_id=ofr.id');
					$sqlcondition[] = ' ( comment.created_by = '.$userid.' ) ';
				}
				if(count($sqlcondition)>0){


					$conditions = implode(' OR ',$sqlcondition);
				}
				


				if( in_array('oss_quotation_review',$this->rules) && $this->is_headquarters!='1' ){


					$conditions .= ' or (app.franchise_id='.$this->franchiseid.') ';
				}else if($this->is_headquarters!='1'){

					$model = $model->andWhere(['app.franchise_id' => $this->franchiseid]);
				}
				
				//Show Client information to reviewer
				//$model = $model->andWhere($conditions);


				if($audit_id !=''){
					$Audit = Audit::find()->where(['id'=>$audit_id])->one();
					if($Audit !== null){
						$auditorList = [];
						if($Audit->auditplan !== null){
							$auditplanunit = $Audit->auditplan->auditplanunit;
							if(count($auditplanunit) >0){
								foreach($auditplanunit as $plaunit){
									$unitauditors = $plaunit->unitallauditors;
									if(count($unitauditors)>0){
										foreach($unitauditors as $auditorobj){
											$auditorList[] = $auditorobj->user_id;
										}
									}
								}
							}
						}
						if(count($auditorList)>0){
							if(in_array($this->user_id ,$auditorList)){


								$PermissionDenial=true;
							}
							
						}
					}
				}
				
				
			}

			//$model = $model->andWhere(['app.franchise_id' => $userid]);
			$model = $model->one();
			if($model!==null)
			{


				$PermissionDenial=true;
			}			
		}
		return $PermissionDenial;	
	}
	

	/*
	edittype: empty string or "sufficient"
	*/
	public function canEditAuditReport($resdata,$edittype='')
	{
		
		//$offerModel = new Offer();
		//,'status'=>$offerModel->enumStatus['waiting_for_audit_report']
		$PermissionDenial=false;				
		if($this->isAdmin())
		{
			$PermissionDenial=true;
		}else{
			$offerID = isset($resdata['offer_id'])?$resdata['offer_id']:'';
			$app_id = isset($resdata['app_id'])?$resdata['app_id']:'';
			$audit_id = isset($resdata['audit_id'])?$resdata['audit_id']:'';

			
			

			if($this->user_type==2 && ($app_id !='' || $offerID !='')){
				$offerModel = new Offer();
				$model = Offer::find()->alias('ofr')->where(['ofr.status'=>$offerModel->enumStatus['waiting_for_audit_report'] ]);
				$model = $model->join('inner join', 'tbl_application as app','app.id=ofr.app_id');
				if($app_id !=''){
					$model = $model->andWhere(['app.id'=>$app_id]);
				}
				if($offerID !=''){
					$model = $model->andWhere(['ofr.id'=>$offerID]);
				}
				$model = $model->andWhere(['app.customer_id'=>$this->user_id]);	
				$model = $model->one();
				
				if($model!==null)
				{
					$PermissionDenial=true;
				}			
			}else if($this->user_type==3){
				$PermissionDenial=false;
			}else if($this->user_type==1){
				
				if($audit_id !=''){

					$Audit = Audit::find()->where(['id'=>$audit_id])->one();
					if($Audit !== null){
						
						$auditorList = [];
						if($Audit->auditplan !== null){
							$application_lead_auditor = $Audit->auditplan->application_lead_auditor;
							if($application_lead_auditor == $this->user_id && $Audit->status == $Audit->arrEnumStatus['review_in_process']){
								$PermissionDenial=true;
							}

							if($Audit->status >= $Audit->arrEnumStatus['approved'] && $Audit->status < $Audit->arrEnumStatus['finalized']){
								$auditplanunit = $Audit->auditplan->auditplanunit;
								if(count($auditplanunit) >0){
									foreach($auditplanunit as $plaunit){
										$unitauditors = $plaunit->unitallauditors;
										if(count($unitauditors)>0){
											foreach($unitauditors as $auditorobj){
												$auditorList[] = $auditorobj->user_id;
											}
										}
									}
								}
							}
						}
						if(count($auditorList)>0){
							if(in_array($this->user_id ,$auditorList)){
								$PermissionDenial=true;
							}
						}
					}
				}
				
			}

			//$model = $model->andWhere(['app.franchise_id' => $userid]);
					
		}
		return $PermissionDenial;
	}
	
	public function isAuditProjectLA($auditid)
	{
		$auditPlanModel=new AuditPlan();
		$PermissionDenial=false;
		$modelAudit = Audit::find()->where(['id' => $auditid])->one();
		if ($modelAudit !== null)
		{
			$auditPlanObj = $modelAudit->auditplan;
			if($auditPlanObj)
			{
				if($modelAudit->followup_status==0 && $auditPlanObj->application_lead_auditor==$this->user_id)
				{			
					$PermissionDenial=true;
				}elseif($modelAudit->followup_status==1 && $auditPlanObj->followup_application_lead_auditor==$this->user_id)
				{			
					$PermissionDenial=true;
				}
			}	
		}			
		if($this->isAdmin())
		{
			$PermissionDenial=true;
		}
		return $PermissionDenial;
	}
	
	public function isAuditReviewer($audit_plan_id)
	{
		$PermissionDenial=false;
		$AuditPlanReviewer = AuditPlanReviewer::find()->where(['audit_plan_id'=>$audit_plan_id,'reviewer_id'=>$this->user_id ,'reviewer_status'=> 1])->one();
		if($AuditPlanReviewer !== null){
			$PermissionDenial=true;
		}

		if($this->isAdmin())
		{
			$PermissionDenial=true;
		}
		return $PermissionDenial;
	}
	
	public function isAuditor($audit_plan_unit_id,$audit_type=1)
	{
		$PermissionDenial=false;
		$AuditPlanUnitAuditor = AuditPlanUnitAuditor::find()->where(['audit_plan_unit_id'=>$audit_plan_unit_id,'user_id'=>$this->user_id,'audit_type'=>$audit_type])->one();
		if($AuditPlanUnitAuditor !== null)
		{
			$PermissionDenial=true;
		}		
		if($this->isAdmin())
		{
			$PermissionDenial=true;
		}		
		return $PermissionDenial;
		
	}

	public function isAuditCustomer($auditid)
	{
		$PermissionDenial=false;
		$modelAudit = Audit::find()->where(['id' => $auditid])->one();
		if($modelAudit !== null)
		{
			$applicationObj = $modelAudit->application;
			if($applicationObj)
			{
				if($this->user_type ==2 && $applicationObj->customer_id==$this->user_id)
				{			
					$PermissionDenial=true;
				}
			}	
		}
		if($this->isAdmin())
		{
			$PermissionDenial=true;
		}		
		return $PermissionDenial;
	}	

	public function isUnitLeadAuditor($audit_plan_unit_id,$audit_type=1)
	{
		$PermissionDenial=false;
		$AuditPlanUnitAuditor = AuditPlanUnitAuditor::find()->where(['is_lead_auditor'=>1, 'audit_plan_unit_id'=>$audit_plan_unit_id,'user_id'=>$this->user_id,'audit_type'=>$audit_type])->one();
		if($AuditPlanUnitAuditor !== null)
		{
			$PermissionDenial=true;
		}		
		if($this->isAdmin())
		{
			$PermissionDenial=true;
		}		
		return $PermissionDenial;
		
	}
}    
?>