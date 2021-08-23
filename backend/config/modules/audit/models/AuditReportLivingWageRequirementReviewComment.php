<?php

namespace app\modules\audit\models;

use Yii;

/**
 * This is the model class for table "tbl_audit_report_living_wage_requirement_review_comment".
 *
 * @property int $id
 * @property int $living_wage_requirement_checklist_review_id
 * @property int $unit_id
 * @property int $category_id
 * @property string $category
 * @property string $answer
 * @property string $comment
 */
class AuditReportLivingWageRequirementReviewComment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_living_wage_requirement_review_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['living_wage_requirement_checklist_review_id', 'category_id'], 'integer'],
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
            'living_wage_requirement_checklist_review_id' => 'Review ID',
            'category_id' => 'category ID',
            'answer' => 'Answer',
            'comment' => 'Comment',
        ];
    }
	
	
}
