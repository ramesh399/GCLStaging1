<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\db\ActiveRecord;

use app\modules\master\models\Country;
use app\modules\master\models\State;
use app\modules\master\models\Standard;
use app\modules\master\models\User;

/**
 * This is the model class for table "tbl_enquiry".
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $telephone
 * @property int $country_id
 * @property int $state_id
 * @property string $company_name
 * @property string $contact_name
 * @property string $company_telephone
 * @property string $company_email
 * @property string $company_website
 * @property string $company_address1
 * @property string $company_address2
 * @property string $company_city
 * @property string $company_zipcode
 * @property int $company_country_id
 * @property int $company_state_id
 * @property int $number_of_employees
 * @property int $number_of_sites
 * @property string $description
 * @property string $other_information
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class Enquiry extends \yii\db\ActiveRecord
{
	public $arrStatus=array('1'=>'Received','2'=>'Forwarded','3'=>'Discard');
	public $arrEnumStatus=array('received'=>'1','forwarded'=>'2','discard'=>'3');
	public $arrStatusColor=array('1'=>'#4572A7','2'=>'#89A54E','3'=>'#ff0000');
	
	public $status_updated_by_name;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_enquiry';
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
            [['company_name', 'contact_name', 'company_telephone', 'company_email', 'company_address1','company_city','company_zipcode','company_country_id'], 'required'],
            [['company_country_id', 'company_state_id', 'number_of_employees', 'number_of_sites', 'status', 'created_at', 'updated_at'], 'integer'],
            [['company_address1', 'company_address2', 'description', 'other_information'], 'string'],
            [['company_name', 'contact_name', 'company_email', 'company_website', 'company_city'], 'string', 'max' => 255],
            [['company_telephone', 'company_zipcode'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_name' => 'Company Name',
            'contact_name' => 'Contact Name',
            'company_telephone' => 'Company Telephone',
            'company_email' => 'Company Email',
            'company_website' => 'Company Website',
            'company_address1' => 'Company Address1',
            'company_address2' => 'Company Address2',
            'company_city' => 'Company City',
            'company_zipcode' => 'Company Zipcode',
            'company_country_id' => 'Company Country ID',
            'company_state_id' => 'Company State ID',
            'number_of_employees' => 'Number Of Employees',
            'number_of_sites' => 'Number Of Sites',
            'description' => 'Description',
            'other_information' => 'Other Information',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
	
	public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }
	
	public function getState()
    {
        return $this->hasOne(State::className(), ['id' => 'state_id']);
    }
	
	public function getCompanycountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'company_country_id']);
    }
	
	public function getCompanystate()
    {
        return $this->hasOne(State::className(), ['id' => 'company_state_id']);
    }
	
	public function getEnquirystandard()
    {
        return $this->hasMany(EnquiryStandard::className(), ['enquiry_id' => 'id']);
    }
	
	public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'status_updated_by']);
    }
	
	public function getCustomer()
    {
        return $this->hasOne(User::className(), ['id' => 'customer_id']);
    }
	
	public function getFranchise()
    {
        return $this->hasOne(User::className(), ['id' => 'franchise_id']);
    }
}
