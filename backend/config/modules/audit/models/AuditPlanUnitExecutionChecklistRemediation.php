<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_audit_plan_unit_execution_checklist_remediation".
 *
 * @property int $id
 * @property int $audit_plan_unit_execution_id
 * @property string $root_cause
 * @property string $correction
 * @property string $corrective_action
 * @property string $evidence_file
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class AuditPlanUnitExecutionChecklistRemediation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_unit_execution_checklist_remediation';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at','updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_plan_unit_execution_checklist_id', 'created_by', 'created_at'], 'integer'],
            [['root_cause', 'correction', 'corrective_action'], 'string'],
            [['evidence_file'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_plan_unit_execution_checklist_id' => 'Audit Plan Unit Execution ID',
            'root_cause' => 'Root Cause',
            'correction' => 'Correction',
            'corrective_action' => 'Corrective Action',
            'evidence_file' => 'Evidence File',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public function getReviewlatest()
    {
        return $this->hasOne(AuditPlanUnitExecutionChecklistRemediationReview::className(), ['checklist_remediation_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }

    public function getAuditorreviewlatest()
    {
        return $this->hasOne(AuditPlanUnitExecutionChecklistRemediationApproval::className(), ['checklist_remediation_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }

    public function getRemediationfile()
    {
        return $this->hasMany(AuditPlanUnitExecutionChecklistRemediationFile::className(), ['checklist_remediation_id' => 'id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
