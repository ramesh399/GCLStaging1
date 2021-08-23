<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_audit_execution_question_standard".
 *
 * @property int $id
 * @property int $audit_execution_question_id
 * @property int $standard_id
 * @property string $clause_no
 * @property string $clause
 */
class AuditExecutionQuestionStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_execution_question_standard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_execution_question_id', 'standard_id'], 'integer'],
            [['clause'], 'string'],
            [['clause_no'], 'string', 'max' => 255],
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
            'standard_id' => 'Standard ID',
            'clause_no' => 'Clause No',
            'clause' => 'Clause',
        ];
    }

    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
}
