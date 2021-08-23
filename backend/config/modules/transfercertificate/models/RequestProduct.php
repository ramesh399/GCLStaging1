<?php

namespace app\modules\transfercertificate\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationUnitProduct;

/**
 * This is the model class for table "tbl_tc_request_product".
 *
 * @property int $id
 * @property int $app_id
 * @property int $unit_id
 * @property int $buyer_id
 * @property int $consignee_id
 * @property int $standard_id
 * @property string $purchase_order_number
 * @property string $comments
 * @property int $transport_id 
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class RequestProduct extends \yii\db\ActiveRecord
{
	public $arrStatus=array('0'=>'Open','1'=>'Input Added');
    public $arrEnumStatus=array('open'=>'0','input_added'=>'1');
		
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_request_product';
    }

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
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tc_request_id'], 'required'],
           
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }	
    
	public function getRequest()
    {
        return $this->hasOne(Request::className(), ['id' => 'tc_request_id']);
    }
    
    public function getUnitproduct()
    {
        return $this->hasOne(ApplicationUnitProduct::className(), ['id' => 'product_id']);
    }
	
	 public function getRequestproductinputmaterial()
    {
        return $this->hasMany(TcRequestProductInputMaterial::className(), ['tc_request_product_id' => 'id']);
    }
    
     public function getUsedweight()
    {
        return $this->hasMany(TcRawMaterialUsedWeight::className(), ['tc_request_product_id' => 'id']);
    }
     public function getTransport()
    {
        return $this->hasOne(Transport::className(), ['id' => 'transport_id']);
    }
    /*
    public function getDestinationcountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_of_destination']);
    }
    */
    public function getConsignee()
    {
        return $this->hasOne(Buyer::className(), ['id' => 'consignee_id']);
    }
}
