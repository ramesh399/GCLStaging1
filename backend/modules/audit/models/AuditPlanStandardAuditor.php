<?php

namespace app\models\audit\models;

use Yii;

/**
 * This is the model class for table "tbl_audit_plan_standard_auditor".
 *
 * @property int $id
 * @property int $audit_plan_standard_id
 * @property int $user_id
 * @property int $is_lead_auditor 0=No,1=Yes
 */
class AuditPlanStandardAuditor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_standard_auditor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_plan_standard_id', 'user_id', 'is_lead_auditor'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_plan_standard_id' => 'Audit Plan Standard ID',
            'user_id' => 'User ID',
            'is_lead_auditor' => 'Is Lead Auditor',
        ];
    }
}
