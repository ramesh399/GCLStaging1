<?php

namespace app\modules\audit\models;
use app\modules\application\models\ApplicationUnit;
use app\modules\master\models\User;
use Yii;

/**
 * This is the model class for table "tbl_audit_plan_unit_history".
 *
 * @property int $id
 * @property int $audit_plan_id
 * @property int $app_id
 * @property int $unit_id
 * @property string $quotation_manday
 * @property string $actual_manday
 * @property int $status 0=Open,1=In Process,2=Rejected,3=Finalized
 */
class AuditPlanUnitHistory extends \yii\db\ActiveRecord
{
	public $arrStatus=array('0'=>'Open','1'=>'Review Completed','2'=>'Inspection Plan in Process','3'=>'Inspection Plan Completed','4'=>'Awaiting for Customer Approval','5'=>'Approved','6'=>'Rejected');
	public $arrEnumStatus=array('open'=>'0','review_completed'=>'1','inspection_plan_in_process'=>'2','inspection_plan_completed'=>'3','awaiting_for_customer_approval'=>'4','approved'=>'5','rejected'=>'6');
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_unit_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['audit_plan_id', 'app_id', 'unit_id', 'status'], 'integer'],
            //[['quotation_manday', 'actual_manday'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_plan_id' => 'Audit ID',
            'app_id' => 'App ID',
            'unit_id' => 'Unit ID',
            'quotation_manday' => 'Original Manday',
            'actual_manday' => 'Actual Manday',
            'status' => 'Status',
        ];
    }

    public function getUnitstandardhistory()
    {
        return $this->hasMany(AuditPlanUnitStandardHistory::className(), ['audit_plan_unit_history_id' => 'id']);
    }
    
    public function getUnitauditorshistory()
    {
        return $this->hasMany(AuditPlanUnitAuditorHistory::className(), ['audit_plan_unit_history_id' => 'id']);
    }
    public function getUnitdata()
    {
        return $this->hasOne(ApplicationUnit::className(), ['id' => 'unit_id']);
    }
    public function getAuditplanunitdatehistory()
    {
        return $this->hasMany(AuditPlanUnitDateHistory::className(), ['audit_plan_unit_history_id' => 'id']);
    }
    public function getUnitleadauditor()
    {
        return $this->hasOne(User::className(), ['id' => 'unit_lead_auditor']);
    }
    public function getUnittechnicalexpert()
    {
        return $this->hasOne(User::className(), ['id' => 'technical_expert']);
    }
    public function getUnittranslator()
    {
        return $this->hasOne(User::className(), ['id' => 'translator']);
    }
}
