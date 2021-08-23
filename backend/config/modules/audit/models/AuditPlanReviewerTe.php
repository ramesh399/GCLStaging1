<?php

namespace app\modules\audit\models;

use Yii;
use app\modules\master\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_audit_plan_reviewer_te".
 *
 * @property int $id
 * @property int $audit_plan_id
 */
class AuditPlanReviewerTe extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_reviewer_te';
    }

    public function behaviors()
    {
        return [];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_plan_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_plan_id' => 'Audit Plan ID'
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'technical_expert_id']);
    }
}
