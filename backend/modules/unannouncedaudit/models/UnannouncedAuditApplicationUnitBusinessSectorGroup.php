<?php

namespace app\modules\unannouncedaudit\models;

use Yii;
use yii\db\ActiveRecord;
use app\modules\master\models\BusinessSectorGroup;

/**
 * This is the model class for table "tbl_unannounced_audit_application_unit_business_sector_group".
 *
 * @property int $id
 * @property int $unannounced_audit_app_unit_id
 * @property int $unit_business_sector_id
 * @property int $business_sector_group_id
 * @property string $business_sector_group_name
 */
class UnannouncedAuditApplicationUnitBusinessSectorGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_unannounced_audit_application_unit_business_sector_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['business_sector_id'], 'integer'],
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
    

    public function getBsectorgroup()
    {
        return $this->hasOne(BusinessSectorGroup::className(), ['id' => 'business_sector_group_id']);
    }
}
