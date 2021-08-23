<?php

namespace app\modules\library\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\Country;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_library_approved_suppliers".
 *
 * @property int $id
 * @property int $country_id
 * @property string $supplier_name
 * @property string $address
 * @property string $contact_person
 * @property string $email
 * @property string $phone
 * @property string $accreditation
 * @property string $certificate_no
 * @property string $scope_of_accreditation
 * @property string $supplier_file
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class LibraryApprovedsuppliers extends \yii\db\ActiveRecord
{
    public $arrStatus=array('1'=>'Active','2'=>'InActive');
    public $arrEnumStatus=array('active'=>'1','inactive'=>'2');

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_approved_suppliers';
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
            [['supplier_name','address','contact_person','email','phone','accreditation','certificate_no','scope_of_accreditation','supplier_file'], 'string'],
            [['country_id','status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'country_id' => 'Country ID',
            'supplier_name' => 'Supplier Name',
            'address' => 'Address',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }
    public function getCreateduser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
