<?php

namespace app\modules\audit\models;

use Yii;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_audit_plan_unit_auditor".
 *
 * @property int $id
 * @property int $audit_plan_standard_id
 * @property int $user_id
 * @property int $is_lead_auditor 0=No,1=Yes
 */
class AuditPlanUnitStandardAuditor extends \yii\db\ActiveRecord
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

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    public function getAuditplanunitauditordate()
    {
        return $this->hasMany(AuditPlanUnitAuditorDate::className(), ['audit_plan_unit_auditor_id' => 'id']);
    }
}
