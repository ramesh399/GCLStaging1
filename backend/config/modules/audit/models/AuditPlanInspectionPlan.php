<?php

namespace app\modules\audit\models;

use Yii;

use app\modules\audit\models\AuditPlanInspectionPlanInspector;
use app\modules\application\models\ApplicationUnit;

/**
 * This is the model class for table "tbl_audit_plan_unit_inspection_plan".
 *
 * @property int $id
 * @property int $audit_plan_inspection_id
 * @property string $activity
 * @property string $inspector
 * @property string $date
 * @property string $start_time
 * @property string $end_time
 * @property string $person_need_to_be_present
 */
class AuditPlanInspectionPlan extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_inspection_plan';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_plan_inspection_id'], 'integer'],
            [['activity', 'inspector', 'person_need_to_be_present'], 'string'],
            [['date', 'start_time', 'end_time'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_plan_inspection_id' => 'Audit Plan Unit Inspection ID',
            'activity' => 'Activity',
            'inspector' => 'Inspector',
            'date' => 'Date',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'person_need_to_be_present' => 'Person Need To Be Present',
        ];
    }
	
	public function getAuditplaninspectionplaninspector()
    {
        return $this->hasMany(AuditPlanInspectionPlanInspector::className(), ['inspection_plan_id' => 'id']);
    }
	
	public function getApplicationunit()
    {
        return $this->hasOne(ApplicationUnit::className(), ['id' => 'application_unit_id']);
    }

    public function getAuditplanunit()
    {
        return $this->hasOne(AuditPlanUnit::className(), ['unit_id' => 'application_unit_id']);
    }
}
