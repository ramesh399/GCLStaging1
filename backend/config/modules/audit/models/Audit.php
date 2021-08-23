<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationUnitStandard;
use app\modules\offer\models\Offer;
use app\modules\master\models\User;
use app\modules\invoice\models\Invoice;
use app\modules\certificate\models\Certificate;

use app\modules\unannouncedaudit\models\UnannouncedAuditApplication;


/**
 * This is the model class for table "tbl_audit".
 *
 * @property int $id
 * @property int $app_id
 * @property int $offer_id
 * @property int $invoice_id
 * @property int $status 0=Open,1=In Process,3=Waiting for Review,4=Waiting for Inspection Plan,5=Inspection Plan in Progress,6=Waiting for Customer Approval,7=Finalized,8=Rejected
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class Audit extends \yii\db\ActiveRecord
{
	
	
    //For Followup Audit
    //public $arrStatus=array('0'=>'Open','1'=>'Submitted','2'=>"Review in Process",'3'=>'Waiting for Inspection Plan Generation','4'=>'Inspection Plan in Process','5'=>'Awaiting for Customer Approval','6'=>'Booked','7'=>'Rejected By Customer','8'=>'Audit in Progress','9'=>'Audit Completed','10'=>'Remediation in Progress','11'=>'Remediation Completed','12'=>'Finalized','13'=>'Certification In-Process','14'=>'Certificate Generated','15'=>'Certificate Denied');
    //public $arrEnumStatus=array('open'=>'0','submitted'=>'1','review_in_process'=>'2',"review_completed"=>'3','inspection_plan_in_process'=>'4','awaiting_for_customer_approval'=>'5','approved'=>'6','rejected'=>'7','audit_in_progress'=>'8','audit_completed'=>'9','remediation_in_progress'=>'10','remediation_completed'=>'11','finalized'=>'12','certification_inprocess'=>'13','generate_certificate'=>'14','certificate_denied'=>'15');

    //public $arrStatus=array('0'=>'Open','1'=>'Submitted','2'=>"Review in Process",'3'=>'Waiting for Inspection Plan Generation','4'=>'Inspection Plan in Process','5'=>'Awaiting for Customer Approval','6'=>'Booked','7'=>'Rejected By Customer','8'=>'Audit in Progress','9'=>'Audit Completed','10'=>'Remediation in Progress','11'=>'Remediation Completed','12'=>'Finalized','13'=>'Certification In-Process','14'=>'Certificate Generated','15'=>'Certificate Denied','16'=>'Followup Open','17'=>'Followup Submitted','18'=>'Waiting for Followup Inspection Plan','19'=>'Waiting for Followup Inspection Plan in Process','20'=>'Awaiting for Followup Customer Approval','21'=>'Followup Booked','22'=>'Followup Rejected By Customer','23'=>'Followup Audit in Progress');
    //public $arrEnumStatus=array('open'=>'0','submitted'=>'1','review_in_process'=>'2',"review_completed"=>'3','inspection_plan_in_process'=>'4','awaiting_for_customer_approval'=>'5','approved'=>'6','rejected'=>'7','audit_in_progress'=>'8','audit_completed'=>'9','remediation_in_progress'=>'10','remediation_completed'=>'11','finalized'=>'12','certification_inprocess'=>'13','generate_certificate'=>'14','certificate_denied'=>'15','followup_open'=>'16','followup_submitted'=>'17','waiting_followup_inspection_plan'=>'18','waiting_followup_inspection_plan_inprocess'=>'19','awaiting_followup_customer_approval'=>'20','followup_booked'=>'21','followup_rejected_by_customer'=>'22','followup_audit_in_progress'=>'23');
	
	public $arrStatus=array('0'=>'Open','1'=>'Submitted','2'=>"Review in Process",'3'=>'Waiting for Inspection Plan Generation','4'=>'Inspection Plan in Process','5'=>'Awaiting for Customer Approval','6'=>'Booked','7'=>'Rejected By Customer','8'=>'Audit in Progress','9'=>'Audit Completed','10'=>'Remediation in Progress','11'=>'Remediation Completed','12'=>'Finalized','16'=>'Followup Open','17'=>'Followup Submitted','18'=>'Followup Review in Process','19'=>'Waiting for Followup Inspection Plan Generation','20'=>'Followup Inspection Plan in Process','21'=>'Awaiting for Followup Customer Approval','22'=>'Followup Booked','23'=>'Followup Rejected By Customer','24'=>'Followup Audit in Progress','25'=>'Finalized Without Audit','26'=>'NC Overdue Waiting for Review','27'=>'Failed - NC Overdue');
    public $arrEnumStatus=array('open'=>'0','submitted'=>'1','review_in_process'=>'2',"review_completed"=>'3','inspection_plan_in_process'=>'4','awaiting_for_customer_approval'=>'5','approved'=>'6','rejected'=>'7','audit_in_progress'=>'8','audit_completed'=>'9','remediation_in_progress'=>'10','remediation_completed'=>'11','finalized'=>'12','followup_open'=>'16','followup_submitted'=>'17','followup_review_in_process'=>'18',"followup_review_completed"=>'19','followup_inspection_plan_inprocess'=>'20','awaiting_followup_customer_approval'=>'21','followup_booked'=>'22','followup_rejected_by_customer'=>'23','followup_audit_in_progress'=>'24','finalized_without_audit'=>'25','nc_overdue_waiting_for_review'=>'26','nc_overdue_failed'=>'27');

    public $arrStatusColor=array('0'=>'#4572A7','1'=>"#DB843D",'2'=>'#3D96AE','3'=>'#4eba8f','4'=>'#bf5aed','5'=>'#f15c80','6'=>'#89a54e','7'=>'#ff0000','8'=>'#A47D7C','9'=>'#A47D7C','10'=>'#4572A7','11'=>'#DB843D','12'=>'#3D96AE','13'=>'#4eba8f','14'=>'#bf5aed','15'=>'#f15c80','16'=>'#f15c80','17'=>'#f15c80','18'=>'#f15c80','19'=>'#f15c80','20'=>'#f15c80','21'=>'#f15c80','22'=>'#f15c80','23'=>'#f15c80');
    public $arrFindingType = ['1'=>'Desk Study','2'=>'Follow-up Audit' ];
    public $enumFindingType = ['desk_study'=>'1','followup_audit'=>'2' ];
    public $due_days,$certificate_valid_until,$certificate_id,$audit_id;
    
    public $audittypeArr = ['1'=> 'Normal','2'=>'Unannounced'];
    public $audittypeEnumArr = ['normal_audit'=> '1','unannounced_audit'=>'2'];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit';
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
            [['app_id', 'offer_id', 'invoice_id', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App ID',
            'offer_id' => 'Offer ID',
            'invoice_id' => 'Invoice ID',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
    
    
    public function getUnannouncedaudit()
    {
        return $this->hasOne(UnannouncedAuditApplication::className(), ['audit_id' => 'id']);
    }

	public function getAuditplan()
    {
        return $this->hasOne(AuditPlan::className(), ['audit_id' => 'id']);
    }
	
	public function getApplication()
    {
        return $this->hasOne(Application::className(), ['id' => 'app_id']);
    }
	
    public function getInvoice()
    {
        return $this->hasOne(Invoice::className(), ['id' => 'invoice_id']);
    }
	
    public function getOffer()
    {
        return $this->hasOne(Offer::className(), ['id' => 'offer_id']);
    }
	
	public function getAuditplanhistory()
    {
        return $this->hasMany(AuditPlanHistory::className(), ['audit_id' => 'id'])->andOnCondition(['audit_type' => '1']);
    }	

    public function getFollowupauditplanhistory()
    {
        return $this->hasMany(AuditPlanHistory::className(), ['audit_id' => 'id'])->andOnCondition(['audit_type' => '2']);
    }

    public function getAuditncn()
    {
        return $this->hasMany(AuditReportNcnReport::className(), ['audit_id' => 'id']);
    }	

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

	public function getCertificate()
    {
        return $this->hasOne(Certificate::className(), ['audit_id' => 'id']);
    }	
}
/*
public $arrStatus=array('0'=>'Open','1'=>'Submitted','2'=>"Review in Process",'3'=>'Waiting for Inspection Plan Generation','4'=>'Inspection Plan in Process','5'=>'Awaiting for Customer Approval','6'=>'Booked','7'=>'Rejected By Customer','8'=>'Audit in Progress','9'=>'Audit Completed','10'=>'Remediation in Progress','11'=>'Remediation Completed','12'=>'Finalized','13'=>'Certification In-Process','14'=>'Certificate Generated','15'=>'Certificate Denied');
public $arrEnumStatus=array('open'=>'0','submitted'=>'1','review_in_process'=>'2',"review_completed"=>'3','inspection_plan_in_process'=>'4','awaiting_for_customer_approval'=>'5','approved'=>'6','rejected'=>'7','audit_in_progress'=>'8','audit_completed'=>'9','remediation_in_progress'=>'10','remediation_completed'=>'11' ,'finalized'=>'12','certification_inprocess'=>'13','generate_certificate'=>'14','certificate_denied'=>'15');
*/
/*
From local may152020
public $arrStatus=array('0'=>'Open','1'=>'Submitted','2'=>"Review in Process",'3'=>'Waiting for Inspection Plan Generation','4'=>'Inspection Plan in Process','5'=>'Awaiting for Customer Approval','6'=>'Booked','7'=>'Rejected By Customer','8'=>'Audit in Progress','9'=>'Audit Completed','10'=>'Remediation in Progress','11'=>'Remediation Completed','12'=>'Followup Open','13'=>'Followup Submitted','14'=>'Waiting for Followup Inspection Plan','15'=>'Waiting for Followup Inspection Plan in Process','16'=>'Awaiting for Followup Customer Approval','17'=>'Followup Booked','18'=>'Followup Rejected By Customer','19'=>'Followup Audit in Progress','20'=>'Finalized','21'=>'Certification In-Process','22'=>'Certificate Generated','23'=>'Certificate Denied');
public $arrEnumStatus=array('open'=>'0','submitted'=>'1','review_in_process'=>'2',"review_completed"=>'3','inspection_plan_in_process'=>'4','awaiting_for_customer_approval'=>'5','approved'=>'6','rejected'=>'7','audit_in_progress'=>'8','audit_completed'=>'9','remediation_in_progress'=>'10','remediation_completed'=>'11' ,'followup_open'=>'12','followup_submitted'=>'13','waiting_followup_inspection_plan'=>'14','waiting_followup_inspection_plan_inprocess'=>'15','awaiting_followup_customer_approval'=>'16','followup_booked'=>'17','followup_rejected_by_customer'=>'18','followup_audit_in_progress'=>'19' ,'finalized'=>'20','certification_inprocess'=>'21','generate_certificate'=>'22','certificate_denied'=>'23');
*/
//public $arrStatus=array('0'=>'Open','1'=>'Submitted','2'=>"Review in Process",'3'=>'Waiting for Inspection Plan Generation','4'=>'Inspection Plan in Process','5'=>'Awaiting for Customer Approval','6'=>'Booked','7'=>'Rejected By Customer','8'=>'Audit in Progress','9'=>'Audit Completed','10'=>'Remediation in Progress','11'=>'Remediation Completed','12'=>'Audit Checklist In-Process','13'=>'Certification In-Process','14'=>'Certificate Generated','15'=>'Certificate Denied');
    //public $arrEnumStatus=array('open'=>'0','submitted'=>'1','review_in_process'=>'2',"review_completed"=>'3','inspection_plan_in_process'=>'4','awaiting_for_customer_approval'=>'5','approved'=>'6','rejected'=>'7','audit_in_progress'=>'8','audit_completed'=>'9','remediation_in_progress'=>'10','remediation_completed'=>'11','audit_checklist_inprocess'=>'12','certification_inprocess'=>'13','generate_certificate'=>'14','certificate_denied'=>'15');
    