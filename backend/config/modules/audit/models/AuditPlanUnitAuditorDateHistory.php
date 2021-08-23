<?php

namespace app\modules\audit\models;

use Yii;

/**
 * This is the model class for table "tbl_audit_plan_unit_auditor_date_history".
 *
 * @property int $id
 * @property int $audit_plan_unit_auditor_history_id
 * @property string $date
 */
class AuditPlanUnitAuditorDateHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_unit_auditor_date_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_plan_unit_auditor_history_id'], 'integer'],
            [['date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_plan_unit_auditor_history_id' => 'Audit Plan Unit Auditor ID',
            'date' => 'Date',
        ];
    }
}
