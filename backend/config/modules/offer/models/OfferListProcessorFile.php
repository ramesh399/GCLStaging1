<?php

namespace app\modules\offer\models;

use app\modules\application\models\ApplicationUnit;

use Yii;

/**
 * This is the model class for table "tbl_offer_list_processor_file".
 *
 * @property int $id
 * @property int $offer_list_processor_id
 * @property string $processor_file
 */
class OfferListProcessorFile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_offer_list_processor_file';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['offer_list_processor_id'], 'integer'],
            [['processor_file'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'offer_list_processor_id' => 'Offer List Processor ID',
            'processor_file' => 'Processor File',
        ];
    }
	
	public function getUnit()
    {
        return $this->hasOne(ApplicationUnit::className(), ['id' => 'unit_id']);
    }
    public function getOfferlistprocessor()
    {
        return $this->hasOne(OfferListProcessor::className(), ['id' => 'offer_list_processor_id']);
    }
}
