<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_client_logo_question_customer_standards".
 *
 * @property int $id
 * @property int $client_logo_checklist_customer_question_id
 * @property int $standard_id
 */
class ClientLogoQuestionCustomerStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_client_logo_question_customer_standards';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['client_logo_checklist_customer_question_id', 'standard_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_logo_checklist_customer_question_id' => 'Audit Execution Question ID',
            'standard_id' => 'Question Finding ID',
        ];
    }

    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
}
