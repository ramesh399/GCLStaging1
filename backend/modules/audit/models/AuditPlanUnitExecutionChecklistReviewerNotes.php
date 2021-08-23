<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class AuditPlanUnitExecutionChecklistReviewerNotes extends \yii\db\ActiveRecord
{
    public static function tableName(){
        return 'tbl_audit_plan_unit_execution_checklist_reviwer_notes';
    }

    public function behaviors(){
        return [];
    }

    public function rules()
    {
        return [
            [['id', 'audit_id','stage' ], 'integer'],
            [['notes'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_id' => 'Audit ID',
            'notes' => 'Notes',
            'stage' => 'Stage'
            
        ];
    }

}