<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_audit_report_client_information_question_findings".
 *
 * @property int $id
 * @property int $audit_report_client_information_question_id
 * @property int $question_finding_id
 */
class ClientInformationQuestionFindings extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_client_information_question_findings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_report_client_information_question_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_report_client_information_question_id' => 'Audit Reviewer Question ID',
            'question_finding_id' => 'Audit Reviewer Risk Category ID',
        ];
    }

    public function getCategory()
    {
        return $this->hasOne(AuditReviewerFindings::className(), ['id' => 'question_finding_id']);
    }
}
