<?php

namespace app\modules\audit\models;

use Yii;
use app\modules\master\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_audit_plan_reviewer".
 *
 * @property int $id
 * @property int $audit_plan_id
 * @property int $reviewer_id
 * @property int $reviewer_status 1=Current,2=Old
 * @property int $created_by
 * @property int $created_at
 */
class AuditPlanReviewer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_reviewer';
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
            [['audit_plan_id', 'reviewer_id', 'reviewer_status', 'created_by', 'created_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_plan_id' => 'Audit Plan ID',
            'reviewer_id' => 'Reviewer ID',
            'reviewer_status' => 'Reviewer Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'reviewer_id']);
    }
}
