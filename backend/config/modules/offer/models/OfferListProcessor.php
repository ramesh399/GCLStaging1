<?php

namespace app\modules\offer\models;


use Yii;

/**
 * This is the model class for table "tbl_offer_list_processor".
 *
 * @property int $id
 * @property int $offer_list_id
 * @property int $is_latest 1=Latest,2=Rejected
 * @property int $created_by
 * @property int $created_at
 */
class OfferListProcessor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_offer_list_processor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['offer_list_id', 'is_latest', 'created_by', 'created_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'offer_list_id' => 'Offer List ID',
            'is_latest' => 'Is Latest',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
        ];
    }
	
	public function getProcessorfile()
    {
        return $this->hasMany(OfferListProcessorFile::className(), ['offer_list_processor_id' => 'id']);
    }	
}
