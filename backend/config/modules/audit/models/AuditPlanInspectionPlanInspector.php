<?php

namespace app\modules\audit\models;
use app\modules\master\models\User;
use Yii;

/**
 * This is the model class for table "tbl_audit_plan_inspection_plan_inspector".
 *
 * @property int $id
 * @property int $inspection_plan_id
 * @property int $user_id
 */
class AuditPlanInspectionPlanInspector extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_inspection_plan_inspector';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id','inspection_plan_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'inspection_plan_id' => 'Inspection Plan ID',
            'user_id' => 'User Id',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
