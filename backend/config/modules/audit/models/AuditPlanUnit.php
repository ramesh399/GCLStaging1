<?php

namespace app\modules\audit\models;
use app\modules\application\models\ApplicationUnit;
use app\modules\master\models\User;
use Yii;

/**
 * This is the model class for table "tbl_audit_plan_unit".
 *
 * @property int $id
 * @property int $audit_plan_id
 * @property int $app_id
 * @property int $unit_id
 * @property string $quotation_manday
 * @property string $actual_manday
 * @property int $status 0=Open,1=In Process,2=Rejected,3=Finalized
 */
class AuditPlanUnit extends \yii\db\ActiveRecord
{
    // inspection 
    //10 for main unit, from 11=> followup
    
    /*
    From local may152020
	public $arrStatus=array('0'=>'Open','1'=>'In-Process','2'=>'Reviewer Reinitiated','3'=>'Awaiting for Unit Lead Auditor Approval','4'=>'Awaiting for Lead Auditor Approval','5'=>'Awaiting for Reviewer Approval','6'=>'Review Completed','7'=>'Audit Completed','8'=>'Remediation in Progress','9'=>'Remediation Completed','10'=>'Followup Open','11'=>'Followup In-Process', '12'=>'Followup Lead Auditor Reinitiated','13'=>'Followup Awaiting for Unit Lead Auditor Approval', '14'=>'Followup Reviewer Reinitated', '15'=>'Followup Awaiting for Lead Auditor Approval','16'=>'Followup Awaiting for Reviewer Approval','17'=>'Followup Completed');
    //already status and this will not conflict
   
	public $arrEnumStatus=array('open'=>'0','in_progress'=>'1','reviewer_reinititated'=>'2','awaiting_for_unit_lead_auditor_approval'=>'3','awaiting_for_lead_auditor_approval'=>'4','awaiting_for_reviewer_approval'=>'5','review_completed'=>'6','audit_completed'=>'7','remediation_in_progress'=>'8','remediation_completed'=>'9','followup_open' => '10','followup_inprocess'=>'11','followup_lead_auditor_reinitiated'=>'12','followup_awaiting_unit_lead_auditor_approval'=>'13','followup_reviewer_reinitated'=>'14','followup_awaiting_lead_auditor_approval'=>'15','followup_awaiting_reviewer_approval'=>'16','followup_completed'=>'17');
    */

    public $arrStatus=array('0'=>'Open','1'=>'In-Process','2'=>'Reviewer Reinitiated','3'=>'Awaiting for Unit Lead Auditor Approval','4'=>'Awaiting for Lead Auditor Approval','5'=>'Awaiting for Reviewer Approval','6'=>'Review Completed','7'=>'Audit Completed','8'=>'Remediation in Progress','9'=>'Remediation Completed','10'=>'Followup Open','11'=>'Followup In-Process', '12'=>'Followup Lead Auditor Reinitiated','13'=>'Followup Awaiting for Unit Lead Auditor Approval','14'=>'Followup Awaiting for Lead Auditor Approval', '15'=>'Followup Reviewer Reinitated', '16'=>'Followup Awaiting for Reviewer Approval','17'=>'Followup Completed','18'=>'NC Over Due');
    
    public $arrEnumStatus=array('open'=>'0','in_progress'=>'1','reviewer_reinititated'=>'2','awaiting_for_unit_lead_auditor_approval'=>'3','awaiting_for_lead_auditor_approval'=>'4','awaiting_for_reviewer_approval'=>'5','review_completed'=>'6','audit_completed'=>'7','remediation_in_progress'=>'8','remediation_completed'=>'9','followup_open' => '10','followup_inprocess'=>'11','followup_lead_auditor_reinitiated'=>'12','followup_awaiting_unit_lead_auditor_approval'=>'13','followup_awaiting_lead_auditor_approval'=>'14','followup_reviewer_reinitated'=>'15','followup_awaiting_reviewer_approval'=>'16','followup_completed'=>'17','nc_overdue'=>'18');


	
    /*
    public $arrStatus=array('0'=>'Open','1'=>'In-Process','2'=>'Reviewer Reinitiated','3'=>'Awaiting for Unit Lead Auditor Approval','4'=>'Awaiting for Lead Auditor Approval','5'=>'Awaiting for Reviewer Approval','6'=>'Review Completed','7'=>'Audit Completed','8'=>'Remediation in Progress','9'=>'Remediation Completed');
    //already status and this will not conflict
   
    public $arrEnumStatus=array('open'=>'0','in_progress'=>'1','reviewer_reinititated'=>'2','awaiting_for_unit_lead_auditor_approval'=>'3','awaiting_for_lead_auditor_approval'=>'4','awaiting_for_reviewer_approval'=>'5','review_completed'=>'6','audit_completed'=>'7','remediation_in_progress'=>'8','remediation_completed'=>'9','followup_audit'=>'10' );
    */

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_unit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_plan_id', 'app_id', 'unit_id', 'status'], 'integer'],
            [['quotation_manday', 'actual_manday'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_plan_id' => 'Audit ID',
            'app_id' => 'App ID',
            'unit_id' => 'Unit ID',
            'quotation_manday' => 'Original Manday',
            'actual_manday' => 'Actual Manday',
            'status' => 'Status',
        ];
    }

    public function getUnitstandard()
    {
        return $this->hasMany(AuditPlanUnitStandard::className(), ['audit_plan_unit_id' => 'id']);
    }
    
    public function getUnitallauditors()
    {
        return $this->hasMany(AuditPlanUnitAuditor::className(), ['audit_plan_unit_id' => 'id']);
    }

    public function getUnitauditors()
    {
        return $this->hasMany(AuditPlanUnitAuditor::className(), ['audit_plan_unit_id' => 'id'])->andOnCondition(['audit_type' => 1]);
    }
    public function getFollowupunitauditors()
    {
        return $this->hasMany(AuditPlanUnitAuditor::className(), ['audit_plan_unit_id' => 'id'])->andOnCondition(['audit_type' => 2]);
    }

    public function getUnitdata()
    {
        return $this->hasOne(ApplicationUnit::className(), ['id' => 'unit_id']);
    }
    public function getAuditplanunitdate()
    {
        return $this->hasMany(AuditPlanUnitDate::className(), ['audit_plan_unit_id' => 'id'])->andOnCondition(['audit_type' => 1]);
    }

    public function getLastunitdate()
    {
        return $this->hasOne(AuditPlanUnitDate::className(), ['audit_plan_unit_id' => 'id'])->andOnCondition(['audit_type' => 1])->orderBy(['date' => SORT_DESC]);
    }

    public function getFirstunitdate()
    {
        return $this->hasOne(AuditPlanUnitDate::className(), ['audit_plan_unit_id' => 'id'])->andOnCondition(['audit_type' => 1])->orderBy(['date' => SORT_ASC]);
    }

    public function getFollowupauditplanunitdate()
    {
        return $this->hasMany(AuditPlanUnitDate::className(), ['audit_plan_unit_id' => 'id'])->andOnCondition(['audit_type' => 2]);
    }

    public function getUnitleadauditor()
    {
        return $this->hasOne(User::className(), ['id' => 'unit_lead_auditor']);
    }

    public function getAuditunitncn()
    {
        return $this->hasOne(AuditReportNcnReport::className(), ['unit_id' => 'unit_id']);
    }	

    public function getUnittechnicalexpert()
    {
        return $this->hasOne(User::className(), ['id' => 'technical_expert']);
    }
    public function getUnittranslator()
    {
        return $this->hasOne(User::className(), ['id' => 'translator']);
    }

    public function getFollowupunittechnicalexpert()
    {
        return $this->hasOne(User::className(), ['id' => 'followup_technical_expert']);
    }
    public function getFollowupunittranslator()
    {
        return $this->hasOne(User::className(), ['id' => 'followup_translator']);
    }
    public function getFollowupunitleadauditor()
    {
        return $this->hasOne(User::className(), ['id' => 'followup_unit_lead_auditor']);
    }
    
    public function getUnitexecution()
    {
        return $this->hasMany(AuditPlanUnitExecution::className(), ['audit_plan_unit_id' => 'id']);
    }
	
	public function getExecutionlistall()
    {
        return $this->hasMany(AuditPlanUnitExecutionChecklist::className(), ['audit_plan_unit_id' => 'id']);
    }
	
	public function getExecutionlistnoncomformity()
    {
        return $this->hasMany(AuditPlanUnitExecutionChecklist::className(), ['audit_plan_unit_id' => 'id'])->andOnCondition(['answer' => 2]);
    }

    public function getNccritical()
    {
        return $this->hasOne(AuditPlanUnitExecutionChecklist::className(), ['audit_plan_unit_id' => 'id'])->andOnCondition(['answer' => 2,'severity'=>1]);
    }

    public function getFollowupexecutionlistnoncomformity()
    {
        return $this->hasMany(AuditPlanUnitExecutionChecklist::className(), ['audit_plan_unit_id' => 'id'])->andOnCondition(['answer' => 2]);
    }

    public function getFollowupunitexecution()
    {
        return $this->hasMany(AuditPlanUnitExecutionFollowup::className(), ['audit_plan_unit_id' => 'id']);
    }

    public function getAuditplan()
    {
        return $this->hasOne(AuditPlan::className(), ['id' => 'audit_plan_id']);
    }
}
