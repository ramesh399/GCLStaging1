<?php

namespace app\modules\invoice\models;

use Yii;

/**
 * This is the model class for table "tbl_invoice_tax".
 *
 * @property int $id
 * @property int $invoice_id
 * @property string $tax_name
 * @property string $tax_percentage
 * @property string $amount
 * @property string $conversion_amount
 */
class InvoiceTax extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_invoice_tax';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['invoice_id'], 'integer'],
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
            'invoice_id' => 'Invoice ID',
            'tax_name' => 'Tax Name',
            'tax_percentage' => 'Tax Percentage',
            'amount' => 'Amount',
        ];
    }
}
