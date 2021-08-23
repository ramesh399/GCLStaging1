<?php

namespace app\modules\application\models;

use Yii;

/**
 * This is the model class for table "tbl_client_logo_request_customer_checklist_comment".
 *
 * @property int $id
 * @property int $client_logo_request_id
 * @property int $question_id
 * @property string $question
 * @property string $comment
 * @property string $file_name
 */
class ClientLogoRequestCustomerChecklistComment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_client_logo_request_customer_checklist_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['client_logo_request_id', 'question_id'], 'integer'],
            [['comment'], 'string'],
            [['file_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            
        ];
    }
	
	
}
