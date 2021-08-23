<?php

namespace app\modules\audit\models;

use Yii;

/**
 * This is the model class for table "tbl_audit_reviewer_question_risk_category".
 *
 * @property int $id
 * @property int $audit_reviewer_question_id
 * @property int $audit_reviewer_risk_category_id
 */
class AuditReviewerQuestionRiskCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_reviewer_question_risk_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_reviewer_question_id', 'audit_reviewer_risk_category_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_reviewer_question_id' => 'Audit Reviewer Question ID',
            'audit_reviewer_risk_category_id' => 'Audit Reviewer Risk Category ID',
        ];
    }

    public function getCategory()
    {
        return $this->hasOne(AuditReviewerRiskCategory::className(), ['id' => 'audit_reviewer_risk_category_id']);
    }
}
