<?php

namespace app\modules\master\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tbl_user_payment_details".
 *
 * @property int $id
 * @property int $user_id
 * @property string $payment_label
 * @property string $payment_content
 */
class UserPaymentDetails extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_payment_details';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
