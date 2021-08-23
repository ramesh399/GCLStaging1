<?php

namespace app\modules\transfercertificate\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_tc_raw_material_used_weight".
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
class TcRawMaterialUsedWeight extends \yii\db\ActiveRecord
{
	public $arrStatus=array('0'=>'Used','2'=>'Rejected');
	public $enumStatus=array('used'=>'0','rejected'=>'2');
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_raw_material_used_weight';
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
            [['tc_raw_material_id'], 'required']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }	
    
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getRawmaterial()
    {
        return $this->hasOne(RawMaterial::className(), ['id' => 'tc_raw_material_id']);
    }

    public function getRawmaterialproduct()
    {
        return $this->hasOne(RawMaterialProduct::className(), ['id' => 'tc_raw_material_product_id']);
    }
 
    public function getUnitproduct()
    {
        return $this->hasOne(ApplicationUnitProduct::className(), ['id' => 'product_id']);
    }
}
