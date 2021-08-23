<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use app\modules\audit\models\Audit;
//use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\AuditPlanUnit;
use app\modules\audit\models\AuditPlanUnitDate;
use app\modules\audit\models\AuditPlanUnitAuditor;
use app\modules\audit\models\AuditPlanUnitStandard;
use app\modules\audit\models\AuditPlanUnitAuditorDate;
use app\modules\audit\models\AuditPlanUnitStandardAuditor;
use app\modules\audit\models\AuditPlanInspection;
use app\modules\audit\models\AuditPlanReview;
use app\modules\audit\models\AuditPlanReviewChecklistComment;
use app\modules\audit\models\AuditPlanReviewer;


use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationUnitStandard;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_audit_plan".
 *
 * @property int $id
 * @property int $audit_id
 * @property int $application_lead_auditor
 * @property int $quotation_manday
 * @property int $actual_manday
 * @property string $comment
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class AuditPlan extends \yii\db\ActiveRecord
{
	//public $arrStatus=array('0'=>'Open','1'=>'In-Process','2'=>'Reviewer Reinitiated','3'=>'Waiting for Lead Auditor Approval','4'=>'Waiting for Review','5'=>'Review in Progress','6'=>'Review Completed','7'=>'Audit Completed','8'=>'Remediation in Progress','9'=>'Remediation Completed','10'=>'Certificate Generated');
    //public $arrEnumStatus=array('open'=>'0','in_progress'=>'1','reviewer_reinitiated'=>'2','waiting_for_lead_auditor'=>'3','waiting_for_review'=>'4','review_in_progress'=>'5','review_completed'=>'6','audit_completed'=>'7','remediation_in_progress'=>'8','remediation_completed'=>'9','generate_certificate'=>'10');
	
    //public $arrStatus=array('0'=>'Open','1'=>'In-Process','2'=>'Reviewer Reinitiated','3'=>'Waiting for Lead Auditor Approval','4'=>'Waiting for Review','5'=>'Review in Progress','6'=>'Review Completed','7'=>'Audit Completed','8'=>'Remediation in Progress','9'=>'Remediation Completed','10'=>'Audit Checklist In-Process','11'=>'Certification In-Process','12'=>'Certificate Generated','13'=>'Certificate Denied');
	//public $arrEnumStatus=array('open'=>'0','in_progress'=>'1','reviewer_reinitiated'=>'2','waiting_for_lead_auditor'=>'3','waiting_for_review'=>'4','review_in_progress'=>'5','review_completed'=>'6','audit_completed'=>'7','remediation_in_progress'=>'8','remediation_completed'=>'9','audit_checklist_inprocess'=>'10','certification_inprocess'=>'11','generate_certificate'=>'12','certificate_denied'=>'13');

	// From Audit Starts 
    //Followup
	/*
    From local may152020
    public $arrStatus=array('0'=>'Open','1'=>'In-Process','2'=>'Reviewer Reinitiated','3'=>'Waiting for Lead Auditor Approval','4'=>'Waiting for Review','5'=>'Review in Progress','6'=>'Review Completed','7'=>'Audit Completed','8'=>'Remediation in Progress','9'=>'Auditor Review in Progress','10'=>'Reviewer Review in Progress','11'=>'Remediation Completed','12'=> 'Followup Open','13'=> 'Followup In-Process','14'=> 'Followup Waiting for Lead Auditor Approval','15'=>'Followup Review in Progress' ,'16'=>'Finalized','17'=>'Certification In-Process','18'=>'Certificate Generated','19'=>'Certificate Denied');
	public $arrEnumStatus=array('open'=>'0','in_progress'=>'1','reviewer_reinitiated'=>'2','waiting_for_lead_auditor'=>'3','waiting_for_review'=>'4','review_in_progress'=>'5','review_completed'=>'6','audit_completed'=>'7','remediation_in_progress'=>'8','auditor_review_in_progress'=>'9','reviewer_review_in_progress'=>'10','remediation_completed'=>'11','followup_open'=>'12','followup_inprocess'=>'13','followup_waiting_for_leadauditor'=>'14','followup_reviewinprogress'=>'15','finalized'=>'16','certification_inprocess'=>'17','generate_certificate'=>'18','certificate_denied'=>'19');
    */
    public $arrStatus=array('0'=>'Open','1'=>'In-Process','2'=>'Reviewer Reinitiated','3'=>'Waiting for Lead Auditor Approval','4'=>'Waiting for Review','5'=>'Review in Progress','6'=>'Review Completed','7'=>'Audit Completed','8'=>'Remediation in Progress','9'=>'Auditor Review in Progress','10'=>'Reviewer Review in Progress','11'=>'Remediation Completed','12'=>'Finalized','13'=>'Certification In-Process','14'=>'Certificate Generated','15'=>'Certificate Denied','16'=> 'Followup Open','17'=> 'Followup In-Process','18'=> 'Followup Waiting for Lead Auditor Approval','19'=>'Followup Review in Progress','21'=>'NC Over due Waiting for Review','22'=>'Failed - NC Over due');
    public $arrEnumStatus=array('open'=>'0','in_progress'=>'1','reviewer_reinitiated'=>'2','waiting_for_lead_auditor'=>'3','waiting_for_review'=>'4','review_in_progress'=>'5','review_completed'=>'6','audit_completed'=>'7','remediation_in_progress'=>'8','auditor_review_in_progress'=>'9','reviewer_review_in_progress'=>'10','remediation_completed'=>'11','finalized'=>'12','certification_inprocess'=>'13','generate_certificate'=>'14','certificate_denied'=>'15','followup_open'=>'16','followup_inprocess'=>'17','followup_waiting_for_lead_auditor'=>'18','followup_reviewinprogress'=>'19','nc_overdue_waiting_for_review'=>'21','nc_overdue_failed'=>'22');
    /*
	public $arrStatus=array('0'=>'Open','1'=>'In-Process','2'=>'Reviewer Reinitiated','3'=>'Waiting for Lead Auditor Approval','4'=>'Waiting for Review','5'=>'Review in Progress','6'=>'Review Completed','7'=>'Audit Completed','8'=>'Remediation in Progress','9'=>'Auditor Review in Progress','10'=>'Reviewer Review in Progress','11'=>'Remediation Completed','12'=>'Finalized','13'=>'Certification In-Process','14'=>'Certificate Generated','15'=>'Certificate Denied');
    public $arrEnumStatus=array('open'=>'0','in_progress'=>'1','reviewer_reinitiated'=>'2','waiting_for_lead_auditor'=>'3','waiting_for_review'=>'4','review_in_progress'=>'5','review_completed'=>'6','audit_completed'=>'7','remediation_in_progress'=>'8','auditor_review_in_progress'=>'9','reviewer_review_in_progress'=>'10','remediation_completed'=>'11','finalized'=>'12','certification_inprocess'=>'13','generate_certificate'=>'14','certificate_denied'=>'15');
    */
    public $arrReviewerStatusList=array('1'=>'Approve','2'=>'Change Request');
    public $arrAuditorStatusList=array('1'=>'Acceptable','2'=>'Not Acceptable');
    
    public $arrFollowupReviewerStatusList=array('1'=>'Approve','2'=>'Not Acceptable', '3'=>'Send Back to Auditor');
    public $arrFollowupAuditorStatusList=array('1'=>'Acceptable','2'=>'Not Acceptable');
    public $arrFollowupLeadAuditorStatusList=array('1'=>'Acceptable','2'=>'Not Acceptable', '3'=>'Send Back to Auditor');

    public $arrSharePlan = array('0'=>'Do not Share Audit Plan','1'=>'Share Audit Plan by Email','2'=>'Audit Plan Approval Required');
    public $arrSharePlanEnum = array('donot_share'=>'0','share_by_email'=>'1','approval_required'=>'2');
	
	public $arrAuditPlanType=array('1'=>'Normal','2'=>'Follow Up');
	public $arrEnumAuditPlanType=array('normal'=>'1','followup'=>'2');
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at','updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_id', 'application_lead_auditor', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['comment'], 'string'],
        ];
    }

    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_id' => 'Audit ID',
            'application_lead_auditor' => 'Application Lead Auditor',
            'quotation_manday' => 'Quotation Manday',
            'actual_manday' => 'Actual Manday',
            'comment' => 'Comment',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    /*
    public function getAuditplanunit()
    {
        return $this->hasMany(AuditPlanUnit::className(), ['audit_plan_id' => 'id'])->andOnCondition(['audit_type' => 1 ]);
    }
    public function getFollowupauditplanunit()
    {
        return $this->hasMany(AuditPlanUnit::className(), ['audit_plan_id' => 'id'])->andOnCondition(['audit_type' => 2]);
    }
    */
    public function getAuditplanunit()
    {
        return $this->hasMany(AuditPlanUnit::className(), ['audit_plan_id' => 'id']);
    }
    public function getFollowupauditplanunit()
    {
        return $this->hasMany(AuditPlanUnit::className(), ['audit_plan_id' => 'id'])->andOnCondition(['followup_status' => 1]);
    }
    


    public function getAuditplanreview()
    {
        return $this->hasOne(AuditPlanReview::className(), ['audit_plan_id' => 'id'])->andOnCondition(['audit_type' => '1']);
    }

    public function getCustomerreview()
    {
        return $this->hasOne(AuditPlanCustomerReview::className(), ['audit_plan_id' => 'id'])->andOnCondition(['audit_type' => '1']);
    }

    public function getFollowupcustomerreview()
    {
        return $this->hasOne(AuditPlanCustomerReview::className(), ['audit_plan_id' => 'id'])->andOnCondition(['audit_type' => '2']);
    }

    public function getFollowupauditplanreview()
    {
        return $this->hasOne(AuditPlanReview::className(), ['audit_plan_id' => 'id'])->andOnCondition(['audit_type' => '2']);
    }

    public function getReviewer()
    {
        //return $this->hasOne(AuditPlanReviewer::className(), ['audit_plan_id' => 'id'],['reviewer_status'=>'1']);
		return $this->hasOne(AuditPlanReviewer::className(), ['audit_plan_id' => 'id'])->andOnCondition(['reviewer_status' => '1']);
    }
	
	/*
    public function getAuditplanreviewcomment()
    {
        return $this->hasMany(AuditPlanReviewChecklistComment::className(), ['audit_plan_review_id' => 'id']);
    }
	*/
	
	public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'application_lead_auditor']);
    }
    
    public function getFollowupuser()
    {
        return $this->hasOne(User::className(), ['id' => 'followup_application_lead_auditor']);
    }

    public function getFollowupcreatedbyuser()
    {
        return $this->hasOne(User::className(), ['id' => 'followup_created_by']);
    }


	public function getAuditplaninspection()
    {
        return $this->hasOne(AuditPlanInspection::className(), ['audit_plan_id' => 'id'])->andOnCondition(['audit_type' => '1']);
    }
	
    public function getFollowupauditplaninspection()
    {
        return $this->hasOne(AuditPlanInspection::className(), ['audit_plan_id' => 'id'])->andOnCondition(['audit_type' => '2']);
    }

	public function getAudit()
    {
        return $this->hasOne(Audit::className(), ['id' => 'audit_id']);
    }

    public function getAuditoroverduecomments()
    {
        return $this->hasOne(AuditOverdueComments::className(), ['audit_plan_id' => 'id'])->andOnCondition(['type' => '1']);
    }

    public function getRevieweroverduecomments()
    {
        return $this->hasOne(AuditOverdueComments::className(), ['audit_plan_id' => 'id'])->andOnCondition(['type' => '2']);
    }

}
