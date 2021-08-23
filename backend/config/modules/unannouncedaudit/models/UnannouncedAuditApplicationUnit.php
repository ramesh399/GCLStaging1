<?php

namespace app\modules\unannouncedaudit\models;

use Yii;
use yii\db\ActiveRecord;
use app\modules\application\models\ApplicationUnit;
/**
 * This is the model class for table "tbl_unannounced_audit_application_unit".
 *
 * @property int $id
 * @property int $unannounced_audit_app_id
 * @property int $unit_id
 */
class UnannouncedAuditApplicationUnit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_unannounced_audit_application_unit';
    }

   
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['unit_id'], 'integer'],
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

    public function getApplicationunit()
    {
        return $this->hasOne(ApplicationUnit::className(), ['id' => 'unit_id']);
    }

    public function getUnannouncedauditunitstandard()
    {
        return $this->hasMany(UnannouncedAuditApplicationUnitStandard::className(), ['unannounced_audit_app_unit_id' => 'id']);
    }

    public function getUnannouncedauditunitbsector()
    {
        return $this->hasMany(UnannouncedAuditApplicationUnitBusinessSector::className(), ['unannounced_audit_app_unit_id' => 'id']);
    }
    public function getUnitbsectorgroups()
    {
        return $this->hasMany(UnannouncedAuditApplicationUnitBusinessSectorGroup::className(), ['unannounced_audit_app_unit_id' => 'id']);
    }

    public function getUnannouncedauditunitprocess()
    {
        return $this->hasMany(UnannouncedAuditApplicationUnitProcess::className(), ['unannounced_audit_app_unit_id' => 'id'])->groupBy('process_id');
    }

    public function getUnannouncedauditunitprocessall()
    {
        return $this->hasMany(UnannouncedAuditApplicationUnitProcess::className(), ['unannounced_audit_app_unit_id' => 'id']);
    }

}
