<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_audit_report_client_information_question_standard".
 *
 * @property int $id
 * @property int $audit_report_client_information_question_id
 * @property int $standard_id
 */
class ClientInformationQuestionStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_client_information_question_standard';
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
            'standard_id' => 'Audit Reviewer Risk Category ID',
        ];
    }

    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
}
