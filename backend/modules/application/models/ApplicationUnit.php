<?php
namespace app\modules\application\models;

use Yii;
use app\modules\master\models\Country;
use app\modules\master\models\State;
use app\modules\master\models\Product;
use app\modules\master\models\Standard;
use app\modules\master\models\Mandaycost;
use app\modules\audit\models\AuditPlanUnit;
/**
 * This is the model class for table "tbl_application_unit".
 *
 * @property int $id
 * @property int $app_id
 * @property string $name
 * @property string $code
 * @property string $address
 * @property string $zipcode
 * @property int $state_id
 * @property int $country_id
 * @property int $no_of_employees
 * @property int $status
 */
class ApplicationUnit extends \yii\db\ActiveRecord
{
    public $unit_type_list=['1'=>'Scope Holder','2'=>'Facility','3'=>'Subcontractor'];

    public $unitStatus=['0'=>'Active','1'=>'Deleted'];
    public $enumUnitStatus=['active'=>'1','deleted'=>'1'];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_unit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_id', 'state_id', 'country_id', 'no_of_employees', 'status'], 'integer'],
            [['address'], 'string'],
            [['name', 'code'], 'string', 'max' => 255],
            [['zipcode'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App ID',
            'name' => 'Name',
            'code' => 'Code',
            'address' => 'Address',
            'zipcode' => 'Zipcode',
            'state_id' => 'State ID',
            'country_id' => 'Country ID',
            'no_of_employees' => 'No Of Employees',
            'status' => 'Status',
        ];
    }
    
    public function getAuditplanunit()
    {
        return $this->hasMany(AuditPlanUnit::className(), ['unit_id' => 'id']);
    }

    public function getCurrentaddress()
    {
        return $this->hasOne(ApplicationChangeAddress::className(), ['parent_app_id' => 'app_id'])->orderBy(['id' => SORT_DESC]);
    }
    
    public function getApplication()
    {
        return $this->hasOne(Application::className(), ['id' => 'app_id']);
    }

	public function getUnitstandard()
    {
        return $this->hasMany(ApplicationUnitCertifiedStandard::className(), ['unit_id' => 'id']);
    }
    
    public function getUnitstandardnormal()
    {
        return $this->hasMany(ApplicationUnitCertifiedStandard::className(), ['unit_id' => 'id']);
    }

	public function getUnitproduct()
    {
        return $this->hasMany(ApplicationUnitProduct::className(), ['unit_id' => 'id']);
    }

    public function getUnitproductnormal()
    {
        return $this->hasMany(ApplicationUnitProduct::className(), ['unit_id' => 'id'])->andOnCondition(['product_addition_type' => 0]);
    }
    
    public function getUnitbusinesssector()
    {
        return $this->hasMany(ApplicationUnitBusinessSector::className(), ['unit_id' => 'id'])->andOnCondition(['unit_business_sector_status' => array(0,8)]);
    }

    public function getUnitbusinesssectorgroup()
    {
        return $this->hasMany(ApplicationUnitBusinessSectorGroup::className(), ['unit_id' => 'id']);
    }

    public function getUnitbusinesssectornormal()
    {
        return $this->hasMany(ApplicationUnitBusinessSector::className(), ['unit_id' => 'id'])->andOnCondition(['addition_type' => 0]);
    }

    public function getUnitappstandardall()
    {
        return $this->hasMany(ApplicationUnitStandard::className(), ['unit_id' => 'id']);
        //->andOnCondition(['unit_standard_status' => array(0,8,5)]);
    }

    public function getUnitappstandard()
    {
        return $this->hasMany(ApplicationUnitStandard::className(), ['unit_id' => 'id'])->andOnCondition(['unit_standard_status' => array(0,8,5)]);
    }
    public function getUnitappstandardnormal()
    {
        return $this->hasMany(ApplicationUnitStandard::className(), ['unit_id' => 'id'])->andOnCondition(['addition_type' => 0]);
    }
	
	public function getUnitprocess()
    {
        return $this->hasMany(ApplicationUnitProcess::className(), ['unit_id' => 'id'])->groupBy('process_id')->andOnCondition(['unit_process_status' => array(0,8)]);
    }
    public function getUnitprocessall()
    {
        return $this->hasMany(ApplicationUnitProcess::className(), ['unit_id' => 'id'])->andOnCondition(['unit_process_status' => array(0,8)]);
    }
    public function getUnitprocessnormal()
    {
        return $this->hasMany(ApplicationUnitProcess::className(), ['unit_id' => 'id'])->andOnCondition(['process_type' => 0])->groupBy('process_id');
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
        return $this->hasMany(ApplicationUnitStandard::className(), ['unit_id' => 'id'])->andOnCondition(['unit_standard_status' => array(0,8)]);
    }
	
	public function getMandaycost()
    {
        return $this->hasOne(Mandaycost::className(), ['country_id' => 'country_id']);
    }
	
	public function getUnitmanday()
    {
        return $this->hasOne(ApplicationUnitManday::className(), ['unit_id' => 'id']);
    }

}
