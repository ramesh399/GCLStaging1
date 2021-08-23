<?php

namespace app\modules\application\models;

use Yii;
use app\modules\master\models\Standard;
use app\modules\master\models\ReductionStandard;

/**
 * This is the model class for table "tbl_application_unit_certified_standard".
 *
 * @property int $id
 * @property int $unit_id
 * @property int $standard_id
 */
class ApplicationUnitCertifiedStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_unit_certified_standard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['unit_id', 'standard_id'], 'integer'],
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
            'standard_id' => 'Standard ID',
        ];
    }

    public function getStandard()
    {
        return $this->hasOne(ReductionStandard::className(), ['id' => 'standard_id']);
        //return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
	
	public function getUnitstandardfile()
    {
        return $this->hasMany(ApplicationUnitCertifiedStandardFile::className(), ['unit_certified_standard_id' => 'id']);
    }
}
