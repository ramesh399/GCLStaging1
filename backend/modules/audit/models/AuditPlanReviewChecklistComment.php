<?php

namespace app\modules\audit\models;

use Yii;

/**
 * This is the model class for table "tbl_audit_plan_review_checklist_comment".
 *
 * @property int $id
 * @property int $audit_plan_review_id
 * @property int $question_id
 * @property string $question
 * @property string $answer
 * @property string $comment
 */
class AuditPlanReviewChecklistComment extends \yii\db\ActiveRecord
{
    public $arrAnswer=[6=>'Critical',5=>'High',4=>'Medium',3=>'Low',2=>'Very Low',1=>'N/A'];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_review_checklist_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_plan_review_id', 'question_id'], 'integer'],
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
            'audit_plan_review_id' => 'Audit Plan Review ID',
            'question_id' => 'Question ID',
            'question' => 'Question',
            'answer' => 'Answer',
            'comment' => 'Comment',
        ];
    }

    public function getAuditplanreviewanswer()
    {
        return $this->hasOne(AuditPlanningRiskCategory::className(), ['id' => 'answer']);
    }
}
