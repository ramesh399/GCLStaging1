<?php

namespace app\modules\changescope\models;

use Yii;
use app\modules\master\models\Standard;
/**
 * This is the model class for table "tbl_cs_unit_addition_unit_standard".
 *
 * @property int $id
 * @property int $unit_addition_unit_id
 * @property int $standard_id
 */
class UnitAdditionUnitStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_unit_addition_unit_standard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['unit_addition_unit_id', 'standard_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'unit_addition_unit_id' => 'Unit Addition Unit ID',
            'standard_id' => 'Standard ID',
        ];
    }

    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
	
	

    
}
