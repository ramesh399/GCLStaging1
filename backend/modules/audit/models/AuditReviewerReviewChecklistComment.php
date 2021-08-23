<?php

namespace app\modules\audit\models;

use Yii;

/**
 * This is the model class for table "tbl_audit_reviewer_review_checklist_comment".
 *
 * @property int $id
 * @property int $audit_reviewer_review_id
 * @property int $question_id
 * @property string $question
 * @property string $answer
 * @property string $comment
 */
class AuditReviewerReviewChecklistComment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_reviewer_review_checklist_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_reviewer_review_id', 'question_id'], 'integer'],
            [['question', 'comment'], 'string'],
            [['answer'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_reviewer_review_id' => 'Audit Reviewer Review ID',
            'question_id' => 'Question ID',
            'question' => 'Question',
            'answer' => 'Answer',
            'comment' => 'Comment',
        ];
    }
}
