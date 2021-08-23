<?php

namespace app\modules\changescope\models;

use Yii;
use app\modules\master\models\BusinessSectorGroup;

/**
 * This is the model class for table "tbl_cs_unit_addition_unit_business_sector_group".
 *
 * @property int $id
 * @property int $unit_addition_unit_business_sector_id
 * @property int $unit_business_sector_id
 * @property int $business_sector_group_id
 */
class UnitAdditionUnitBusinessSectorGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_unit_addition_unit_business_sector_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['unit_addition_unit_business_sector_id','unit_business_sector_id', 'business_sector_group_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'unit_addition_unit_business_sector_id' => 'Unit Addition Unit Business Sector ID',
            'unit_business_sector_id' => 'Unit Business Sector ID',
            'business_sector_id' => 'Business Sector Group ID',
        ];
    }
	
    public function getBusinesssectorgroup()
    {
        return $this->hasOne(BusinessSectorGroup::className(), ['id' => 'business_sector_group_id']);
    }

    
}
