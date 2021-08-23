<?php

namespace app\modules\audit\models;
use app\modules\master\models\AuditExecutionQuestionStandard;

use Yii;

/**
 * This is the model class for table "tbl_audit_plan_unit_execution_checklist_standard".
 *
 * @property int $id
 * @property int $audit_plan_unit_execution_checklist_id
 * @property int $question_id
 * @property int $question_standard_id
 */
class AuditPlanUnitExecutionChecklistStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_unit_execution_checklist_standard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_plan_unit_execution_checklist_id', 'question_id', 'question_standard_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_plan_unit_execution_checklist_id' => 'Audit Plan Unit Execution Checklist ID',
            'question_id' => 'Question ID',
            'question_standard_id' => 'Question Standard ID',
        ];
    }
	
	public function getAuditexecutionquestionstandard()
    {
        return $this->hasOne(AuditExecutionQuestionStandard::className(), ['id' => 'question_standard_id']);
    }
}
