<?php

namespace app\modules\invoice\models;

use Yii;

/**
 * This is the model class for table "tbl_invoice_other_expenses".
 *
 * @property int $id
 * @property int $invoice_id
 * @property string $activity
 * @property string $description
 * @property string $amount
 * @property string $conversion_amount
 */
class InvoiceDetails extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_invoice_details';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['invoice_id'], 'integer'],
            [['amount'], 'number'],
            //[['activity', 'description'], 'string', 'max' => 255],
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
            'activity' => 'Activity',
            'description' => 'Description',
            'amount' => 'Amount',
            'conversion_amount' => 'Conversion Amount',
        ];
    }
    public function getDetailstandard()
    {
        return $this->hasMany(InvoiceDetailsStandard::className(), ['invoice_detail_id' => 'id']);
    }
}
