<?php

namespace app\modules\audit\models;

use Yii;
use app\modules\master\models\AuditReviewerFindings;
/**
 * This is the model class for table "tbl_audit_report_client_information_checklist_review_comment".
 *
 * @property int $id
 * @property int $client_information_checklist_review_id
 * @property int $unit_id
 * @property int $client_information_question_id
 * @property string $question
 * @property string $answer
 * @property string $comment
 */
class AuditReportClientInformationChecklistReviewComment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_client_information_checklist_review_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['client_information_checklist_review_id', 'client_information_question_id'], 'integer'],
            [['comment'], 'string'],
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
            'client_information_checklist_review_id' => 'Review ID',
            'client_information_question_id' => 'Question ID',
            'answer' => 'Answer',
            'comment' => 'Comment',
        ];
    }
	public function getAnswercategory()
    {
        return $this->hasOne(AuditReviewerFindings::className(), ['id' => 'answer']);
    }
	
}
