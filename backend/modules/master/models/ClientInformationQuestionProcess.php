<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_audit_report_client_information_question_process".
 *
 * @property int $id
 * @property int $audit_report_client_information_question_id
 * @property int $process_id
 */
class ClientInformationQuestionProcess extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_client_information_question_process';
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
            'process_id' => 'Audit Reviewer Risk Category ID',
        ];
    }

    public function getProcess()
    {
        return $this->hasOne(Process::className(), ['id' => 'process_id']);
    }
}
