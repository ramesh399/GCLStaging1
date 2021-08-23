<?php

namespace app\modules\transfercertificate\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_tc_raw_material_history".
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
class RawMaterialHistory extends \yii\db\ActiveRecord
{
	public $arrStatus=array('0'=>'Approved' ,'1'=>'Archived');
	public $enumStatus=array('approved'=>'0' ,'archived'=>'1');	
	
    public $arrcertifiedStatus=array('1'=>'Yes','2'=>'No');
    public $enumcertifiedStatus=array('yes'=>'1','no'=>'1');

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_raw_material_history';
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
            [['activity'], 'string']
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
        ];
    }	
    
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
	
	
	public function getRawmaterialfilehistory()
    {
        return $this->hasMany(RawMaterialFileHistory::className(), ['tc_raw_material_history_id' => 'id']);
    }
}
