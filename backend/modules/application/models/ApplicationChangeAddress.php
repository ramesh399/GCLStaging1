<?php
namespace app\modules\application\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\Country;
use app\modules\master\models\State;
use app\modules\master\models\User;
use app\modules\master\models\Mandaycost;
/**
 * This is the model class for table "tbl_application_change_address".
 *
 * @property int $id
 * @property int $customer_id 
 * @property string $company_name
 * @property string $address
 * @property string $zipcode
 * @property int $state_id
 * @property int $country_id
 * @property string $unit_name
 * @property string $unit_address
 * @property string $unit_zipcode
 * @property int $unit_state_id
 * @property int $unit_country_id 
 * @property string $salutation
 * @property string $title
 * @property string $first_name
 * @property string $last_name
 * @property string $job_title
 * @property string $telephone
 * @property string $email_address 
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class ApplicationChangeAddress extends \yii\db\ActiveRecord
{

    public $arrSalutation=array('1'=>'Mr','2'=>'Mrs','3'=>'Ms','4'=>'Dr');
	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_change_address';
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
    
    public function rules()
    {
        return [
            [['address'], 'string'],
            [['state_id', 'country_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['company_name', 'title', 'first_name', 'last_name', 'job_title', 'email_address'], 'string', 'max' => 255],
            [['zipcode', 'telephone'], 'string', 'max' => 50],
            [['salutation'], 'string', 'max' => 25],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
			'app_id' => 'App ID',            
            'company_name' => 'Company Name',
            'address' => 'Address',
            'zipcode' => 'Zipcode',
            'state_id' => 'State ID',
            'country_id' => 'Country ID',
			'unit_name' => 'Unit Name',
            'unit_address' => 'Unit Address',
            'unit_zipcode' => 'Unit Zipcode',
            'unit_state_id' => 'Unit State ID',
            'unit_country_id' => 'Unit Country ID',
            'salutation' => 'Salutation',
            'title' => 'Title',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'job_title' => 'Job Title',
            'telephone' => 'Telephone',
            'email_address' => 'Email Address',          
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

	public function getMandaycost()
    {
		//return $this->hasOne(Mandaycost::className(), ['id' => 'country_id']);
		
        return $this->hasOne(Mandaycost::className(), ['country_id' => 'country_id']);
    }	

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }
	
	public function getState()
    {
        return $this->hasOne(State::className(), ['id' => 'state_id']);
    }  
    
    public function getUnitcountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'unit_country_id']);
    }
	
	public function getUnitstate()
    {
        return $this->hasOne(State::className(), ['id' => 'unit_state_id']);
    }  

    public function getUsername()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }	
    
	public function getCustomer()
    {
        return $this->hasOne(User::className(), ['id' => 'customer_id']);
	}	
}	
