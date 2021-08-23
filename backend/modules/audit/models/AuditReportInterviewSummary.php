<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_audit_report_interview_summary".
 *
 * @property int $id
 * @property int $audit_plan_id
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class AuditReportInterviewSummary extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_interview_summary';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
        ];
    }

    
    public function rules()
    {
        return [
            [['audit_id'], 'integer'],
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
        ];
    }
    /*
    public function getAuditReportInterviewSummaryplan()
    {
        return $this->hasMany(AuditReportInterviewSummaryPlan::className(), ['audit_plan_inspection_id' => 'id']);
    }
    */
}
