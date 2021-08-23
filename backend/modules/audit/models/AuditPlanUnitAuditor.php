<?php

namespace app\modules\audit\models;
use app\modules\master\models\User;
use Yii;

/**
 * This is the model class for table "tbl_audit_plan_unit_auditor".
 *
 * @property int $id
 * @property int $audit_plan_unit_id
 * @property int $user_id
 * @property int $is_lead_auditor 0=No,1=Yes
 */
class AuditPlanUnitAuditor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_unit_auditor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_plan_unit_id', 'user_id', 'is_lead_auditor'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_plan_unit_id' => 'Audit Plan Unit ID',
            'user_id' => 'User ID',
            'is_lead_auditor' => 'Is Lead Auditor',
        ];
    }

    public function getAuditplanunitauditordate()
    {
        return $this->hasMany(AuditPlanUnitAuditorDate::className(), ['audit_plan_unit_auditor_id' => 'id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    public function getAuditplanunit()
    {
        return $this->hasOne(AuditPlanUnit::className(), ['id' => 'audit_plan_unit_id']);
    }
}
