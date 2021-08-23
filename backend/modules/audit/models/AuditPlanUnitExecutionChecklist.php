<?php

namespace app\modules\audit\models;

use Yii;
use app\modules\master\models\AuditNonConformityTimeline;
/**
 * This is the model class for table "tbl_audit_plan_unit_execution_checklist".
 *
 * @property int $id
 * @property int $audit_plan_unit_execution_id
 * @property int $user_id
 * @property string $answer
 * @property string $comment
 * @property int $severity
 * @property int $finding_type
 * @property string $question
 * @property string $file
 */


class AuditPlanUnitExecutionChecklist extends \yii\db\ActiveRecord
{
    //0 2 customer
    //1,4 => auditor change to 3,2
    // reviewer =>5,4
    /*
    From local may152020

    public $arrStatus = [0=>'Open',1=>'In Progress',2=>'Change Request', 3=>'Closed', 4=>'Change Request', 5=>'Closed', 6=>'Followup Auditor Closed', 7=>'Followup Change Request', 8=>'Followup Lead Auditor Closed', 9=>'Followup Reviewer Change Request'];
    public $arrEnumStatus = ['open'=>0,'in_progress'=>1,'auditor_change_request'=>2, 'waiting_for_approval'=>3, 'reviewer_change_request'=>4, 'settled'=>5, 'followup_auditor_closed'=>6, 'followup_lead_auditor_change_request'=>7, 'followup_lead_auditor_closed'=>8, 'followup_reviewer_change_request'=>9];

    public $answerList = [1=>'Yes',2=>'No',3=>'Not Applicable'];
    public $statusList = [1=>'Approve',2=>'Change Request'];
    */
    public $findingTypeArr = ['1'=>'Desk Study','2'=>'Followup Audit'];
    public $enumFindingType = ['desk_study'=>'1','followup_audit'=>'2'];


    public $arrStatus = [0=>'Open',1=>'In Progress',2=>'Change Request', 3=>'Closed', 4=>'Change Request', 5=>'Closed', 6=>'Not Accepted', 7=>'Waiting for Reviewer Approval', 8=>'Not Accepted by Lead Auditor', 9=>'NC Over Due'];
    public $arrEnumStatus = ['open'=>0,'in_progress'=>1,'auditor_change_request'=>2, 'waiting_for_approval'=>3, 'reviewer_change_request'=>4, 'settled'=>5, 'not_accepted'=>6, 'followup_waiting_for_review_approval'=> 7, 'followup_lead_auditor_not_accepted'=>8,'nc_overdue'=>9  ];
    
    //public $arrFollowupStatus = [0=>'Open',1=>'In Progress',2=>'Not Accepted By Lead Auditor', 3=>'Closed', 4=>'Not Accepted By Reviewer', 5=>'Closed', 6=>'Not Accepted', 7=>'Waiting for Reviewer Approval',8=>'Waiting for Reviewer Approval'];

    public $answerList = [1=>'Yes',2=>'No',3=>'Not Applicable'];
    public $statusList = [1=>'Approve',2=>'Change Request'];


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_unit_execution_checklist';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_plan_unit_execution_id'], 'integer'],
            [['question'], 'string'],
            [['file'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_plan_unit_execution_id' => 'Audit Plan Unit Execution ID',
            'user_id' => 'User ID',
            'answer' => 'Answer',
            'finding' => 'Finding',
            'severity' => 'Severity',
            'finding_type' => 'Finding Type',
            'question' => 'Question',
            'file' => 'File',
        ];
    }

    public function getAuditnonconformitytimeline()
    {
        return $this->hasOne(AuditNonConformityTimeline::className(), ['id' => 'severity']);
    }
    
    public function getAuditplanunitexecution()
    {
        return $this->hasOne(AuditPlanUnitExecution::className(), ['id' => 'audit_plan_unit_execution_id']);
    }

	public function getAuditexecutioncheckliststandard()
    {
        return $this->hasMany(AuditPlanUnitExecutionChecklistStandard::className(), ['audit_plan_unit_execution_checklist_id' => 'id']);
    }

    public function getChecklistremediation()
    {
        return $this->hasOne(AuditPlanUnitExecutionChecklistRemediation::className(), ['audit_plan_unit_execution_checklist_id' => 'id']);
    }
	
    public function getChecklistremediationclosed()
    {
        return $this->hasOne(AuditPlanUnitExecutionChecklistRemediation::className(), ['audit_plan_unit_execution_checklist_id' => 'id'])->andOnCondition(['status' => 0]);
    }
    
    public function getChecklistremediationlatest()
    {
        return $this->hasOne(AuditPlanUnitExecutionChecklistRemediation::className(), ['audit_plan_unit_execution_checklist_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }

}
