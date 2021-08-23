<?php

namespace app\modules\audit\models;

use Yii;
use app\modules\master\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_audit_plan_unit_execution_checklist_remediation_approval".
 *
 * @property int $id
 * @property int $audit_plan_unit_execution_checklist_id
 * @property int $checklist_remediation_id
 * @property int $status 0=Open,1=Approve,2=Change Request
 * @property string $comment
 * @property int $created_by
 * @property int $created_at
 */
class AuditPlanUnitExecutionChecklistRemediationApproval extends \yii\db\ActiveRecord
{
    public $arrStatus=array('0'=>'Open','1'=>'Approve','2'=>'Change Request');
    public $arrEnumStatus=array('open'=>'0','approve'=>'1','change_request'=>'2');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_unit_execution_checklist_remediation_approval';
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
            [['audit_plan_unit_execution_checklist_id', 'checklist_remediation_id', 'status', 'created_by', 'created_at'], 'integer'],
            [['comment'], 'string'],
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
            'checklist_remediation_id' => 'Checklist Remediation ID',
            'status' => 'Status',
            'comment' => 'Comment',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
