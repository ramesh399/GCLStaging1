<?php
namespace app\modules\changescope\models;

use Yii;
use yii\db\ActiveRecord;
use app\modules\master\models\Country;
use app\modules\master\models\State;
use app\modules\master\models\Product;
/**
 * This is the model class for table "tbl_cs_unit_addition_unit".
 *
 * @property int $id
 * @property int $unit_addition_id
 * @property int $new_unit_id
 * @property int $unit_type
 * @property string $name
 * @property string $code
 * @property string $address
 * @property string $zipcode
 * @property string $city
 * @property int $state_id
 * @property int $country_id
 * @property int $no_of_employees

 */
class UnitAdditionUnit extends \yii\db\ActiveRecord
{
	public $unit_type_list=['1'=>'Scope Holder','2'=>'Facility','3'=>'Subcontractor'];
	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_unit_addition_unit';
    }

    /**
     * {@inheritdoc}
     */

   
    public function rules()
    {
        return [
            [['unit_addition_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'new_unit_id' => 'New Unit Id',
            'unit_type' => 'Unit Type',
            'name' => 'Name',
            'code' => 'Code',
            'address' => 'Address',
            'zipcode' => 'Zipcode',
            'city' => 'City',
        ];
    }
    
    public function getUnitaddition()
    {
        return $this->hasOne(UnitAddition::className(), ['id' => 'unit_addition_id']);
    }

    public function getUnitstandard()
    {
        return $this->hasMany(UnitAdditionUnitCertifiedStandard::className(), ['unit_addition_unit_id' => 'id']);
    }
    
    public function getUnitproduct()
    {
        return $this->hasMany(UnitAdditionUnitProduct::className(), ['unit_addition_unit_id' => 'id']);
    }

    public function getUnitbusinesssector()
    {
        return $this->hasMany(UnitAdditionUnitBusinessSector::className(), ['unit_addition_unit_id' => 'id']);
    }

    public function getUnitappstandard()
    {
        return $this->hasMany(UnitAdditionUnitStandard::className(), ['unit_addition_unit_id' => 'id']);
    }
    
    public function getUnitprocessall()
    {
        return $this->hasMany(UnitAdditionUnitProcess::className(), ['unit_addition_unit_id' => 'id']);
    }

    public function getUnitprocess()
    {
        return $this->hasMany(UnitAdditionUnitProcess::className(), ['unit_addition_unit_id' => 'id'])->groupBy('process_id');
    }
    
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }
    
    public function getState()
    {
        return $this->hasOne(State::className(), ['id' => 'state_id']);
    }

    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }
    
    public function getStandard()
    {
        return $this->hasMany(UnitAdditionUnitStandard::className(), ['unit_addition_unit_id' => 'id']);
    }
    
    
    public function getUnitmanday()
    {
        return $this->hasOne(UnitAdditionUnitManday::className(), ['unit_addition_unit_id' => 'id']);
    }
}
