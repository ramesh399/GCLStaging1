<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_client_logo_question_hq_findings".
 *
 * @property int $id
 * @property int $client_logo_checklist_hq_question_id
 * @property int $question_finding_id
 */
class ClientLogoQuestionHqFindings extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_client_logo_question_hq_findings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['client_logo_checklist_hq_question_id', 'question_finding_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_logo_checklist_hq_question_id' => 'Audit Execution Question ID',
            'question_finding_id' => 'Question Finding ID',
        ];
    }
}
