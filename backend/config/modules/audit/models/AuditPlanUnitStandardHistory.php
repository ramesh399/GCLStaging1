<?php

namespace app\modules\audit\models;
use app\modules\master\models\Standard;

use Yii;

/**
 * This is the model class for table "tbl_audit_plan_unit_standard_history".
 *
 * @property int $id
 * @property int $audit_plan_unit_history_id
 * @property int $standard_id
 */
class AuditPlanUnitStandardHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_unit_standard_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_plan_unit_history_id', 'standard_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_plan_unit_history_id' => 'Audit Plan ID',
            'standard_id' => 'Standard ID',
        ];
    }
    /*
    public function getUnitstandardauditor()
    {
        return $this->hasMany(AuditPlanUnitStandardAuditor::className(), ['audit_plan_standard_id' => 'id']);
    }
    */
    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
    
}
