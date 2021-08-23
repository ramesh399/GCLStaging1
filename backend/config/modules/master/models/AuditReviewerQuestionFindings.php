<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_audit_reviewer_question_findings".
 *
 * @property int $id
 * @property int $audit_reviewer_question_id
 * @property int $audit_reviewer_risk_category_id
 */
class AuditReviewerQuestionFindings extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_reviewer_question_findings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_reviewer_question_id'], 'integer'],
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
        return $this->hasOne(AuditReviewerFindings::className(), ['id' => 'audit_reviewer_finding_id']);
    }
}
