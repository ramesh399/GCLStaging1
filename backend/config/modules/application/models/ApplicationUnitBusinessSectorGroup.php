<?php

namespace app\modules\application\models;

use Yii;

/**
 * This is the model class for table "tbl_application_unit_business_sector_group".
 *
 * @property int $id
 * @property int $unit_id
 * @property int $unit_business_sector_id
 * @property int $business_sector_group_id
 */
use app\modules\master\models\BusinessSectorGroup;

class ApplicationUnitBusinessSectorGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_unit_business_sector_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['unit_id', 'unit_business_sector_id', 'business_sector_group_id'], 'integer'],
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
            'unit_business_sector_id' => 'Unit Business Sector ID',
            'business_sector_group_id' => 'Business Sector Group ID',
        ];
    }

    public function getGroup()
    {
        return $this->hasOne(BusinessSectorGroup::className(), ['id' => 'business_sector_group_id']);
    }
    public function getUnitbsector()
    {
        return $this->hasOne(ApplicationUnitBusinessSector::className(), ['id' => 'unit_business_sector_id']);
    }
}
