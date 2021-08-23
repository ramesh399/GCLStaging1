<?php

namespace app\modules\audit\models;

use Yii;
use app\modules\master\models\User;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_audit_plan_history".
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
class AuditPlanHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_history';
    }

    public function behaviors()
    {
        return [           
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['audit_id', 'application_lead_auditor', 'quotation_manday', 'actual_manday', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
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
	
	public function getAuditplanunithistory()
    {
        return $this->hasMany(AuditPlanUnitHistory::className(), ['audit_plan_history_id' => 'id']);
    }

    public function getCustomerreviewhistory()
    {
        return $this->hasOne(AuditPlanCustomerReviewHistory::className(), ['audit_plan_history_id' => 'id']);
    }
    
    public function getAuditplanreviewhistory()
    {
        return $this->hasOne(AuditPlanReviewHistory::className(), ['audit_plan_history_id' => 'id']);
    }
	
	/*
    public function getAuditplanreviewcomment()
    {
        return $this->hasMany(AuditPlanReviewChecklistComment::className(), ['audit_plan_review_id' => 'id']);
    }
	*/
	
	public function getLeadauditor()
    {
        return $this->hasOne(User::className(), ['id' => 'application_lead_auditor']);
    }

    public function getFollowupleadauditor()
    {
        return $this->hasOne(User::className(), ['id' => 'followup_application_lead_auditor']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
	
	public function getAuditplaninspectionhistory()
    {
        return $this->hasOne(AuditPlanInspectionHistory::className(), ['audit_plan_history_id' => 'id']);		
    }


}
