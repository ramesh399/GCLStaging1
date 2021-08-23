<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_audit_report_interview_employees".
 *
 * @property int $id
 * @property int $audit_id
 * @property string $name
 * @property int $gender
 * @property int $migrant
 * @property string $position
 * @property int $type
 * @property string $notes
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class AuditReportInterviewEmployees extends \yii\db\ActiveRecord
{
    public $arrGender=array('1'=>'Male','2'=>'Female');
	public $arrEnumGender=array('male'=>'1','female'=>'2');
	
	public $arrType=array('1'=>'Seasonal','2'=>'Permanent Worker','3'=>'Migrant');
    public $arrEnumType=array('seasonal'=>'1','permanent_worker'=>'2','migrant'=>'3');
    
    public $arrMigrant=array('1'=>'Yes','2'=>'No');
    public $arrEnumMigrant=array('yes'=>'1','no'=>'2');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_interview_employees';
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
            [['audit_id', 'gender', 'migrant', 'type', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
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
    
    
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
