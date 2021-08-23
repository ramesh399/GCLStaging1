<?php

namespace app\modules\audit\models;


use Yii;

/**
 * This is the model class for table "tbl_audit_plan_execution_checklist_review_comment".
 *
 * @property int $id
 * @property int $audit_plan_execution_checklist_review_id
 * @property int $user_id
 * @property string $answer
 * @property string $comment
 * @property int $finding_type
 */
class AuditPlanExecutionChecklistReviewComment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_execution_checklist_review_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_plan_execution_checklist_review_id'], 'integer'],
            [['comment'], 'string'],
           // [['answer'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_plan_execution_checklist_review_id' => 'Audit Plan Execution Checklist Review ID',
            'answer' => 'Answer',
            'comment' => 'Comment',
            'finding_type' => 'Finding Type',
        ];
    }

    
}
