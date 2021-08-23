<?php

namespace app\modules\offer\models;

use Yii;

/**
 * This is the model class for table "tbl_offer_list_tax".
 *
 * @property int $id
 * @property int $offer_list_id
 * @property int $man_day_cost_tax_id
 * @property string $tax_name
 * @property string $tax_percentage
 * @property string $amount
 */
class OfferListTax extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_offer_list_tax';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['offer_list_id', 'man_day_cost_tax_id'], 'integer'],
            [['tax_percentage', 'amount'], 'number'],
            [['tax_name'], 'string', 'max' => 255],
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
            'man_day_cost_tax_id' => 'Man Day Cost Tax ID',
            'tax_name' => 'Tax Name',
            'tax_percentage' => 'Tax Percentage',
            'amount' => 'Amount',
        ];
    }
}
