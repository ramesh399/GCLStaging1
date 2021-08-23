<?php

namespace app\modules\certificate\models;

use Yii;
use app\modules\master\models\AuditReviewerFindings;

/**
 * This is the model class for table "tbl_audit_reviewer_review_checklist_comment".
 *
 * @property int $id
 * @property int $certificate_reviewer_review_id
 * @property int $question_id
 * @property string $question
 * @property string $answer
 * @property string $comment
 */
class CertificateReviewerReviewChecklistComment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_certificate_reviewer_review_checklist_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['certificate_reviewer_review_id', 'question_id'], 'integer'],
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
            'certificate_reviewer_review_id' => 'Certificate Reviewer Review ID',
            'question_id' => 'Question ID',
            'question' => 'Question',
            'answer' => 'Answer',
            'comment' => 'Comment',
        ];
    }

    public function getAuditrevieweranswer()
    {
        return $this->hasOne(AuditReviewerFindings::className(), ['id' => 'answer']);
    }
}
