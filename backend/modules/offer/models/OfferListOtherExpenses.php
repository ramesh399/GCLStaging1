<?php

namespace app\modules\offer\models;

use Yii;

/**
 * This is the model class for table "tbl_offer_list_other_expenses".
 *
 * @property int $id
 * @property int $offer_list_id
 * @property string $activity
 * @property string $description
 * @property string $amount
 */
class OfferListOtherExpenses extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_offer_list_other_expenses';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['offer_list_id'], 'integer'],
            [['amount'], 'number'],
            [['activity', 'description'], 'string', 'max' => 255],
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
            'activity' => 'Activity',
            'description' => 'Description',
            'amount' => 'Amount',
        ];
    }
}
