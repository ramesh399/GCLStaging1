<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_qualification_question".
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $guidance
 * @property int $file_upload_required
 * @property int $recurring_period
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class QualificationQuestion extends \yii\db\ActiveRecord
{
    //public $arrRecurringPeriod=array('1'=>'Monthly','2'=>'Three Months Once','3'=>'Six Months Once','4'=>'Once in a Year');
    public $arrRecurringPeriod = ['1'=>'One Month','2'=>'Two Months','3'=>'Three Months','4'=>'Half Yearly','5'=>'Annually','6'=>'One Time'];
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_qualification_question';
    }

    /**
     * {@inheritdoc}
     */
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
    
    public function rules()
    {
        return [
            [['name', 'guidance'], 'string'],
            [['file_upload_required', 'recurring_period', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['code'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'code' => 'Code',
            'guidance' => 'Guidance',
            'file_upload_required' => 'File Upload Required',
            'recurring_period' => 'Recurring Period',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public function getQualificationquestionstandard()
    {
        return $this->hasMany(QualificationQuestionStandard::className(), ['qualification_question_id' => 'id']);
    }

    public function getQualificationquestionbusinesssector()
    {
        return $this->hasMany(QualificationQuestionBusinessSector::className(), ['qualification_question_id' => 'id']);
    }

    public function getQualificationquestionbusinesssectorgroup()
    {
        return $this->hasMany(QualificationQuestionBusinessSectorGroup::className(), ['qualification_question_id' => 'id']);
    }
	
	/*
    public function getQualificationquestionprocess()
    {
        return $this->hasMany(QualificationQuestionProcess::className(), ['qualification_question_id' => 'id']);
    }
	*/

    public function getQualificationquestionrole()
    {
        return $this->hasMany(QualificationQuestionRole::className(), ['qualification_question_id' => 'id']);
    }
}
