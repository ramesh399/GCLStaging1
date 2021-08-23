<?php

namespace app\modules\offer\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_offer_comment".
 *
 * @property int $id
 * @property int $offer_id
 * @property int $status 0=Open,1=Approve,2=Negotiate,3=Reject
 * @property string $comment
 * @property int $created_by
 * @property int $created_at
 */
class OfferComment extends \yii\db\ActiveRecord
{

    public $arrStatus=array('1'=>'Approved','2'=>"Rejected");
    public $enumStatus=array('approved'=>'1','rejected'=>"2");
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_offer_comment';
    }
    /*
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at']
                    //ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }
    */
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['offer_id', 'status', 'created_by', 'created_at'], 'integer'],
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
            'status' => 'Status',
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
