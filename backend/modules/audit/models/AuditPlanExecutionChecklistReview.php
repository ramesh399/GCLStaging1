<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_audit_plan_execution_checklist_review".
 *
 * @property int $id
 * @property int $audit_plan_execution_checklist
 * @property int $reviewer_id
 * @property int $created_by
 * @property int $created_at
 */
class AuditPlanExecutionChecklistReview extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_execution_checklist_review';
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
            [['audit_plan_id', 'reviewer_id', 'created_by', 'created_at'], 'integer'],
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
            'created_by' => 'Created By',
            'created_at' => 'Created At',
        ];
    }
}
