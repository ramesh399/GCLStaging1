<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_audit_plan_unit_execution".
 *
 * @property int $id
 * @property int $audit_plan_unit_id
 * @property int $sub_topic_id
 * @property int $user_id
 * @property int $executed_by
 * @property int $executed_date
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class AuditPlanUnitExecution extends \yii\db\ActiveRecord
{
    public $arrStatus = [0=>'Open',1=>'Waiting for Unit Lead Auditor Approval',2=>'Approved',3=>'Correction needed'];
    public $arrEnumStatus = ['open'=>0,'waiting_for_unit_lead_auditor_approval'=>1,'completed'=>2,'reintiate'=>3];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_unit_execution';
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
            [['audit_plan_unit_id', 'sub_topic_id', 'executed_by', 'executed_date', 'created_by', 'created_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_plan_unit_id' => 'Audit Plan Unit ID',
            'sub_topic_id' => 'Sub Topic ID',
            'user_id' => 'User ID',
            'executed_by' => 'Executed By',
            'executed_date' => 'Executed Date',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
	
	public function getExecutionlistall()
    {
        return $this->hasMany(AuditPlanUnitExecutionChecklist::className(), ['audit_plan_unit_execution_id' => 'id']);
    }
	
	public function getExecutionlistnoncomformity()
    {
        return $this->hasMany(AuditPlanUnitExecutionChecklist::className(), ['audit_plan_unit_execution_id' => 'id'])->andOnCondition(['answer' => 2]);
    }
	
	
	public function getExecutedby()
    {
        return $this->hasOne(User::className(), ['id' => 'executed_by']);
    }	

    public function getAuditplanunit()
    {
        return $this->hasOne(AuditPlanUnit::className(), ['id' => 'audit_plan_unit_id']);
    }	
}
