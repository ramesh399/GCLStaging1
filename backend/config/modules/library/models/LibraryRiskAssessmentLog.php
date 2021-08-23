<?php

namespace app\modules\library\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_library_risk_assessment_log".
 *
 * @property int $id
 * @property string $question
 * @property string $answer
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class LibraryRiskAssessmentLog extends \yii\db\ActiveRecord
{

    public $arrUpdated=array('1'=>'IC Meeting','2'=>'MRM Meeting','3'=>"Other");
    public $arrReason=array('1'=>'Action','2'=>'Review','3'=>"Closure");

   // public $arrUpdatedEnum=array('ic_meeting'=>'1','mrm_meeting'=>'2',"other"=>'3');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_risk_assessment_log';
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
            //[['description'], 'string'],
            [['library_risk_assessment_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
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
    
    

}
