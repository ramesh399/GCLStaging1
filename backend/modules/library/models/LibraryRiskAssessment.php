<?php

namespace app\modules\library\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_library_risk_assessment".
 *
 * @property int $id
 * @property int $franchise_id
 * @property int $threat_id
 * @property string $vulnerability
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class LibraryRiskAssessment extends \yii\db\ActiveRecord
{


    public $arrThreat=array('1'=>'Theft, Loss or Damage to Machines & Data','2'=>'Liability Claims','3'=>"Poor Certification Decisions", '4'=>"Audit problems", '5'=>"Loss of accreditation", '6'=>"Loss of records", '7'=>"Loss of life or serious injury", '8'=>"Impartiality Committee", '9'=>"Impartiality");
    public $arrProbability=array('1'=>0.1,'2'=>0.5,'3'=>1);
    public $arrImpact=array('1'=>10,'2'=>50,'3'=>100);

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_risk_assessment';
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
            [['vulnerability'], 'string'],
            [['franchise_id','threat_id','status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
    
    public function getLibrarylog()
    {
        return $this->hasMany(LibraryRiskAssessmentLog::className(), ['library_risk_assessment_id' => 'id']);
    }

    public function getFranchise()
    {
        return $this->hasOne(User::className(), ['id' => 'franchise_id']);
    }
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
