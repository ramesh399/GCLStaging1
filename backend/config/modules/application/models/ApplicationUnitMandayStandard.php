<?php

namespace app\modules\application\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_application_unit_manday_standard".
 *
 * @property int $id
 * @property string $application_unit_manday_id
 * @property string $unit_id
 * @property string $standard_id
 * @property string $inspector_days
 * @property string $inspection_time_type
 */
class ApplicationUnitMandayStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_unit_manday_standard';
    }

    public function behaviors()
    {
        return [

        ];
    }
    

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['application_unit_manday_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'application_unit_manday_id' => 'Unit Manday ID',
            'unit_id' => 'Unit ID',
            'standard_id' => 'Standard ID',
            'inspector_days' => 'Inspector Days',
            'inspection_time_type' => 'Inspection Time Type',
        ];
    }
	
    public function getManday()
    {
        return $this->hasOne(ApplicationUnitManday::className(), ['id' => 'application_unit_manday_id']);
    }
    public function getUnitmandaystandarddiscount()
    {
        return $this->hasMany(ApplicationUnitMandayStandardDiscount::className(), ['application_unit_manday_standard_id' => 'id']);
    }
}
