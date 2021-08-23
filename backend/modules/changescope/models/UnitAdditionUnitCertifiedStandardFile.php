<?php

namespace app\modules\changescope\models;

use Yii;
/**
 * This is the model class for table "tbl_cs_unit_addition_unit_certified_standard_file".
 *
 * @property int $id
 * @property int $unit_addition_unit_certified_standard_id
 * @property string $file
 * @property string $type
 */
class UnitAdditionUnitCertifiedStandardFile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_unit_addition_unit_certified_standard_file';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['unit_addition_unit_certified_standard_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'unit_addition_unit_certified_standard_id' => 'Unit Addition Unit Certified Standard ID',
            'file' => 'File',
            'type' => 'Type',
        ];
    }
	
    
    
}
