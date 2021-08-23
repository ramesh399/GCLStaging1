<?php

namespace app\modules\changescope\models;

use Yii;
use app\modules\application\models\ApplicationUnit;
use app\modules\master\models\Country;
use app\modules\master\models\State;

/**
 * This is the model class for table "tbl_cs_change_address_unit".
 *
 * @property int $id
 * @property int $change_address_id
 * @property int $unit_id
 * @property int $unit_type
 * @property string $name
 * @property string $code
 * @property string $address
 * @property string $zipcode
 * @property string $city
 * @property int $state_id
 * @property int $country_id
 */
class ChangeAddressUnit extends \yii\db\ActiveRecord
{
    public $arrSalutation=array('0'=>'Mr','1'=>'Mrs','2'=>'Ms','3'=>'Dr');
	public $arrEnumSalutation=array('mr'=>'0','mrs'=>'1','ms'=>'2','dr'=>'3');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_change_address_unit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['unit_type', 'unit_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'unit_id' => 'Unit ID',
            'change_address_id' => 'Change Address ID',
            'unit_type' => 'Unit Type',
        ];
    }
	
	public function getApplicationunit()
    {
        return $this->hasOne(ApplicationUnit::className(), ['id' => 'unit_id']);
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }
	
	public function getState()
    {
        return $this->hasOne(State::className(), ['id' => 'state_id']);
    }

    
}
