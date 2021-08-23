<?php

namespace app\modules\application\models;

use Yii;
use app\modules\master\models\Standard;
use app\modules\master\models\StandardLicenseFee;

/**
 * This is the model class for table "tbl_application_unit_standard".
 *
 * @property int $id
 * @property int $unit_id
 * @property int $standard_id
 */
class ApplicationUnitStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_unit_standard';
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
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
	
	public function getStandardlicensefee()
    {
        return $this->hasOne(StandardLicenseFee::className(), ['standard_id' => 'standard_id']);
    }
	
	
	
}
