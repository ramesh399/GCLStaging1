<?php

namespace app\modules\unannouncedaudit\models;

use Yii;
use yii\db\ActiveRecord;
use app\modules\master\models\BusinessSector;

/**
 * This is the model class for table "tbl_unannounced_audit_application_unit_business_sector".
 *
 * @property int $id
 * @property int $unannounced_audit_app_unit_id
 * @property int $business_sector_id
 * @property string $business_sector_name
 */
class UnannouncedAuditApplicationUnitBusinessSector extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_unannounced_audit_application_unit_business_sector';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['business_sector_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            
        ];
    }
    

    public function getBsector()
    {
        return $this->hasOne(BusinessSector::className(), ['id' => 'business_sector_id']);
    }

    public function getUnannouncedauditunitbsectorgroup()
    {
        return $this->hasMany(UnannouncedAuditApplicationUnitBusinessSectorGroup::className(), ['unit_business_sector_id' => 'id']);
    }
}
