<?php

namespace app\modules\changescope\models;

use Yii;
use app\modules\master\models\BusinessSector;

/**
 * This is the model class for table "tbl_cs_unit_addition_unit_business_sector".
 *
 * @property int $id
 * @property int $unit_addition_unit_id
 * @property int $business_sector_id
 */
class UnitAdditionUnitBusinessSector extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_unit_addition_unit_business_sector';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['unit_addition_unit_id', 'business_sector_id'], 'integer'],
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
            'business_sector_id' => 'Business Sector ID',
        ];
    }
	
	public function getBusinesssector()
    {
        return $this->hasOne(BusinessSector::className(), ['id' => 'business_sector_id']);
    }

    
}
