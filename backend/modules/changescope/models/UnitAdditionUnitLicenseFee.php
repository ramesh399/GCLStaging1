<?php

namespace app\modules\changescope\models;

use Yii;
/**
 * This is the model class for table "tbl_cs_unit_addition_unit_license_fee".
 *
 * @property int $id
 * @property int $unit_addition_id
 * @property int $standard_id
 * @property float $license_fee
 * @property float $subsequent_license_fee
 */
class UnitAdditionUnitLicenseFee extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_unit_addition_unit_license_fee';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['unit_addition_id', 'standard_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'unit_addition_id' => 'Unit Addition ID',
            'standard_id' => 'Standard ID',
            'license_fee' => 'License Fee',
            'subsequent_license_fee' => 'Subsequent License Fee',
        ];
    }
	
    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }


    
}
