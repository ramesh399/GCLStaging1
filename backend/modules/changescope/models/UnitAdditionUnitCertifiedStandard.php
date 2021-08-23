<?php

namespace app\modules\changescope\models;

use Yii;
use app\modules\master\models\Standard;
use app\modules\master\models\ReductionStandard;
/**
 * This is the model class for table "tbl_cs_unit_addition_unit_certified_standard".
 *
 * @property int $id
 * @property int $unit_addition_id
 * @property int $standard_id
 */
class UnitAdditionUnitCertifiedStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_unit_addition_unit_certified_standard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
           // [['unit_addition_id', 'standard_id'], 'integer'],
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
        ];
    }
	
    public function getUnitstandardfile()
    {
        return $this->hasMany(UnitAdditionUnitCertifiedStandardFile::className(), ['unit_addition_unit_certified_standard_id' => 'id']);
    }

    public function getStandard()
    {
        return $this->hasOne(ReductionStandard::className(), ['id' => 'standard_id']);
    }


    
}
