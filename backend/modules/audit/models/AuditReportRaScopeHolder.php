<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_audit_report_ra_scope_holder".
 *
 * @property int $id
 * @property int $audit_id
 * @property int $type_of_risk_id
 * @property int $audit_type_id
 * @property string $description_of_risk
 * @property string $potential_risks
 * @property string $measures_for_risk_reduction
 * @property string $frequency_of_risk
 * @property string $probability_rate
 * @property string $responsible_person
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class AuditReportRaScopeHolder extends \yii\db\ActiveRecord
{
    public $arrAuditType=array('1'=>'External','2'=>'Internal');
    public $arrEnumAuditType=array('external'=>'1','internal'=>'2');
    
    public $arrConformity=array('1'=>'Yes','2'=>'No','3'=>'NA');
    public $arrEnumConformity=array('yes'=>'1','no'=>'2','na'=>'3');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_ra_scope_holder';
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
            [['description_of_risk', 'potential_risks', 'measures_for_risk_reduction', 'frequency_of_risk', 'probability_rate'], 'string'],
            [['type_of_risk_id', 'audit_type_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
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
    
    
    public function getTypeofrisk()
    {
        return $this->hasOne(AuditReportTypeOfRisk::className(), ['id' => 'type_of_risk_id']);
    }

    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
