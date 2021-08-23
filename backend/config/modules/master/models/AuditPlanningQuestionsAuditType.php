<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_audit_planning_questions_audit_type".
 *
 * @property int $id
 * @property int $planning_question_id
 * @property int $audit_type_id
 */
class AuditPlanningQuestionsAuditType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_planning_questions_audit_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_type_id'], 'integer'],
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
}
