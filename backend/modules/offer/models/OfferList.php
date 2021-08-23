<?php

namespace app\modules\offer\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_offer_list".
 *
 * @property int $id
 * @property int $offer_id
 * @property string $conversion_rate
 * @property string $currency
 * @property string $conversion_currency_code
 * @property string $certification_fee_sub_total
 * @property string $other_expense_sub_total
 * @property string $total
 * @property string $gst_rate
 * @property string $total_payable_amount
 * @property string $conversion_total_payable
 * @property string $discount
 * @property int $status 0=Open,1=In Process,2=Waiting for Customer Approval,3=Approved,4=Negotiated,5=Rejected,6=Finalized
 * @property int $is_latest 1=Latest,2=Negotiate
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class OfferList extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_offer_list';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['offer_id', 'status', 'is_latest', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            //[['conversion_rate', 'certification_fee_sub_total', 'other_expense_sub_total', 'total', 'gst_rate', 'total_payable_amount', 'conversion_total_payable', 'discount'], 'number'],
            [['currency'], 'string', 'max' => 25],
            [['conversion_currency_code'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at','updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'offer_id' => 'Offer ID',
            'conversion_rate' => 'Conversion Rate',
            'currency' => 'Currency',
            'conversion_currency_code' => 'Conversion Currency Code',
            'certification_fee_sub_total' => 'Certification Fee Sub Total',
            'other_expense_sub_total' => 'Other Expense Sub Total',
            'total' => 'Total',
            'gst_rate' => 'Gst Rate',
            'total_payable_amount' => 'Total Payable Amount',
            'conversion_total_payable' => 'Conversion Total Payable',
            'discount' => 'Discount',
            'status' => 'Status',
            'is_latest' => 'Is Latest',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
	
	public function getOfferotherexpenses()
    {
        return $this->hasMany(OfferListOtherExpenses::className(), ['offer_list_id' => 'id']);
    }
	
	public function getOffercertificationfee()
    {
        return $this->hasMany(OfferListCertificationFee::className(), ['offer_list_id' => 'id']);
    }

    public function getOffertax()
    {
        return $this->hasMany(OfferListTax::className(), ['offer_list_id' => 'id']);
    }
	
	public function getCertificationfee()
    {
        return $this->hasOne(OfferListCertificationFee::className(), ['offer_list_id' => 'id'])->orderBy(['id' => SORT_ASC])->limit(1);
    }
	
	public function getOfferlistprocessor()
    {
        return $this->hasOne(OfferListProcessor::className(), ['offer_list_id' => 'id'])->andOnCondition(['is_latest' => 1]);
    }

    public function getOffer()
    {
        return $this->hasOne(Offer::className(), ['id' => 'offer_id']);
    }

}
