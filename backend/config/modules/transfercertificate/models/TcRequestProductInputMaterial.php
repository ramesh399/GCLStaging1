<?php

namespace app\modules\transfercertificate\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_tc_request_product_input_material".
 *
 * @property int $id
 * @property string $supplier_name
 * @property string $trade_name
 * @property string $lot_number
 * @property string $net_weight
  * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class TcRequestProductInputMaterial extends \yii\db\ActiveRecord
{
	public $arrStatus=array('0'=>'Open','1'=>'Approved','2'=>'Archived');
	public $enumStatus=array('open'=>'0','approved'=>'1','archived'=>'2');	
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_request_product_input_material';
    }

    public function behaviors()
    {
        return [];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tc_request_product_id'], 'required']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
           
        ];
    }	
	
	public function getRawmaterial()
    {
        return $this->hasOne(RawMaterial::className(), ['id' => 'tc_raw_material_id']);
    }
    
    public function getRawmaterialproduct()
    {
        return $this->hasOne(RawMaterialProduct::className(), ['id' => 'tc_raw_material_product_id']);
    }
    /*
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
    */
}
