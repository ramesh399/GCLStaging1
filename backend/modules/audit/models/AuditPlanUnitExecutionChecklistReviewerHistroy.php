<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class AuditPlanUnitExecutionChecklistReviewerHistroy extends \yii\db\ActiveRecord
{
    public static function tableName(){
        return 'tbl_audit_plan_unit_execution_checklist_reviwer_histroy';
    }

    public function behaviors(){
        return [];
    }

    public function rules()
    {
        return [
            [['id', 'audit_id', 'answer'], 'integer'],
            [['question','comment','created_at','sub_topic','unit_name'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_id' => 'Audit ID',
            'question' => 'Questions',
            'answer' => 'Answer',
            'comment' => 'Comment',
            'created_at' => 'Created At',
            'sub_topic' => "Sub Topic",
            'review_stage' => "Review Stage",
            'unit_name' => 'Unit Name'
        ];
    }

}