<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_audit_planning_question_risk_category".
 *
 * @property int $id
 * @property int $audit_planning_question_id
 * @property int $risk_category_id
 */
class AuditPlanningQuestionRiskCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_planning_question_risk_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_planning_question_id', 'risk_category_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_planning_question_id' => 'Audit Planning Question ID',
            'risk_category_id' => 'Risk Category ID',
        ];
    }
	
	public function getCategory()
    {
        return $this->hasOne(AuditPlanningRiskCategory::className(), ['id' => 'risk_category_id']);
    }
}
