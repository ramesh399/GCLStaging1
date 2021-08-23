<?php

namespace app\modules\offer\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_offer_reinitiate_comment".
 *
 * @property int $id
 * @property int $offer_id 
 * @property string $comment
 * @property int $created_by
 * @property int $created_at
 */
class OfferReinitiateComment extends \yii\db\ActiveRecord
{
	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_offer_reinitiate_comment';
    }
	
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['offer_id', 'created_by', 'created_at'], 'integer'],
            [['comment'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'offer_id' => 'Offer ID',            
            'comment' => 'Comment',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

}
