<?php

namespace app\modules\application\models;

use Yii;

/**
 * This is the model class for table "tbl_application_unit_certified_standard_file".
 *
 * @property int $id
 * @property int $unit_certified_standard_id
 * @property string $file
 */
class ApplicationUnitCertifiedStandardFile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_unit_certified_standard_file';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['unit_certified_standard_id'], 'integer'],
            [['file'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'unit_certified_standard_id' => 'Unit Certified Standard ID',
            'file' => 'File',
        ];
    }

    
}
