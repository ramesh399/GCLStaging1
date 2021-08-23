<?php

namespace app\modules\invoice\models;

use Yii;
use app\modules\transfercertificate\models\Request;
/**
 * This is the model class for table "tbl_invoice_tc".
 *
 * @property int $id
 * @property int $invoice_id
 * @property int $tc_request_id
 * @property string $tc_number
 */
class InvoiceTc extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_invoice_tc';
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
			'tc_request_id' => 'TC Request ID',
        ];
    }

    public function getTcrequest()
    {
        return $this->hasMany(Request::className(), ['id' => 'tc_request_id']);
    }
}
