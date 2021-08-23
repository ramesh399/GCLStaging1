<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
use app\modules\master\models\Country;

/**
 * This is the model class for table "tbl_audit_report_chemical_list".
 *
 * @property int $id
 * @property int $audit_id
 * @property string $trade_name
 * @property string $suppier
 * @property int $country_id
 * @property string $utilization
 * @property int $proof
 * @property string $type_of_conformity
 * @property string $validity_or_issue_date
 * @property int $msds_available
 * @property string $msds_issued_date
 * @property int $conformity_auditor
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class AuditReportChemicalList extends \yii\db\ActiveRecord
{
    public $arrMSDSavailable=array('1'=>'Yes','2'=>'No');
    public $arrEnumMSDSavailable=array('yes'=>'1','no'=>'2');
    
    public $arrProof=array('1'=>'Yes','2'=>'No');
    public $arrEnumProof=array('yes'=>'1','no'=>'2');
	
	public $arrColor=array('4'=>'#FF0000','2'=>'#F79647','3'=>'#00B050','1'=>'#4572A7','5'=>'#000000');	
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_chemical_list';
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
            [['trade_name', 'suppier', 'utilization', 'type_of_conformity'], 'string'],
            [['country_id', 'proof', 'msds_available', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
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
    
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }
    
    public function getAuditorconformity()
    {
        return $this->hasOne(AuditReportChemicalListAuditorConformity::className(), ['id' => 'conformity_auditor']);
    }

    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
