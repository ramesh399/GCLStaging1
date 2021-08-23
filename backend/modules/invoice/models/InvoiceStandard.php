<?php

namespace app\modules\invoice\models;

use Yii;
use app\modules\master\models\Standard;
/**
 * This is the model class for table "tbl_invoice_standard".
 *
 * @property int $id
 * @property int $invoice_detail_id
 * @property string $standard_id
 */
class InvoiceStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_invoice_standard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['invoice_id'], 'integer']
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
        ];
    }

    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
}
