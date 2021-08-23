<?php

namespace app\modules\audit\models;

use Yii;
use app\modules\master\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_audit_plan_unit_execution_checklist_remediation_file".
 *
 * @property int $id
 * @property int $checklist_remediation_id
 * @property string $filename
 */
class AuditPlanUnitExecutionChecklistRemediationFile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_unit_execution_checklist_remediation_file';
    }

    public function behaviors()
    {
        return [];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['checklist_remediation_id'], 'integer'],
            [['filename'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'checklist_remediation_id' => 'Checklist Remediation ID',
            'filename' => 'filename',
        ];
    }
}
