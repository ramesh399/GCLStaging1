<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_audit_execution_question_business_sector".
 *
 * @property int $id
 * @property int $audit_execution_question_id
 * @property int $business_sector_id
 */
class AuditExecutionQuestionBusinessSector extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_execution_question_business_sector';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_execution_question_id', 'business_sector_id'], 'integer'],
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
            'business_sector_id' => 'Business Sector ID',
        ];
    }

    public function getBsector()
    {
        return $this->hasOne(BusinessSector::className(), ['id' => 'business_sector_id']);
    }
}
