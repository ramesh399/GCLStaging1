<?php

namespace app\modules\application\models;
use app\modules\master\models\BusinessSector;
use Yii;

/**
 * This is the model class for table "tbl_application_unit_business_sector".
 *
 * @property int $id
 * @property int $unit_id
 * @property int $business_sector_id
 */
class ApplicationUnitBusinessSector extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_unit_business_sector';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['unit_id', 'business_sector_id'], 'integer'],
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
            'business_sector_id' => 'Business Sector ID',
        ];
    }

    public function getBusinesssector()
    {
        return $this->hasOne(BusinessSector::className(), ['id' => 'business_sector_id']);
    }

    public function getUnitbusinesssectorgroup()
    {
        return $this->hasMany(ApplicationUnitBusinessSectorGroup::className(), ['unit_business_sector_id' => 'id']);
    }

}
