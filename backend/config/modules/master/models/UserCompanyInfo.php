<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_user_company_info".
 *
 * @property int $id
 * @property int $user_id
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
 * @property string $company_number
 */
class UserCompanyInfo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_company_info';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_name', 'contact_name', 'company_telephone', 'company_email', 'company_address1','company_city','company_zipcode','company_country_id'], 'required'],
            [['user_id', 'company_country_id', 'company_state_id', 'number_of_employees', 'number_of_sites'], 'integer'],
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
            'user_id' => 'User ID',
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
        ];
    }
	
	public function getCompanycountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'company_country_id']);
    }
    public function getCompanystate()
    {
        return $this->hasOne(State::className(), ['id' => 'company_state_id']);
    }

    public function getMandaycost()
    {
		return $this->hasOne(Mandaycost::className(), ['country_id' => 'company_country_id']);
    }
}
