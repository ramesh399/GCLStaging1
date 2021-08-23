<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_audit_execution_non_conformity".
 *
 * @property int $id
 * @property int $audit_execution_question_id
 * @property int $audit_non_conformity_timeline_id
 */
class AuditExecutionQuestionNonConformity extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_execution_question_non_conformity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_execution_question_id', 'audit_non_conformity_timeline_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_execution_question_id' => 'Audit Execution Question ID',
            'audit_non_conformity_timeline_id' => 'Audit Non Conformity Timeline ID',
        ];
    }
	
	public function getNoncomformity()
    {
        return $this->hasOne(AuditNonConformityTimeline::className(), ['id' => 'audit_non_conformity_timeline_id']);
    }
}
