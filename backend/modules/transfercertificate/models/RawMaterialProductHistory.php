<?php

namespace app\modules\transfercertificate\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_tc_raw_material_product".
 *
 * @property int $id
 * @property int $raw_material_id
 * @property string $product_name
 * @property string $trade_name
 * @property string $net_weight
 * @property string $gross_weight
  * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class RawMaterialProduct extends \yii\db\ActiveRecord
{
	public $arrStatus=array('0'=>'Approved' ,'1'=>'Archived');
	public $enumStatus=array('approved'=>'0' ,'archived'=>'1');	
	
    public $arrcertifiedStatus=array('1'=>'Yes','2'=>'No','3'=>'Reclaim');
    public $enumcertifiedStatus=array('yes'=>'1','no'=>'2','reclaim'=>'3');

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_raw_material_product';
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
            [['trade_name'], 'string'],
            [['status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }	
	

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'supplier_name' => 'Supplier Name',
            'trade_name' => 'Trade Name',
			'lot_number' => 'Label Grade',
			'tc_number' => 'TC Number',
			'net_weight' => 'Net Weight',
			'is_certified' => 'Is Certified',			
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

    
    public function getLabelgrade()
    {
        return $this->hasMany(RawMaterialLabelGrade::className(), ['raw_material_product_id' => 'id']);
    }

    
}
