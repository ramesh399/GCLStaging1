<?php

namespace app\modules\library\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_library_meeting_minutes".
 *
 * @property int $id
 * @property string $meeting_id
 * @property string $raised_id
 * @property string $class 
 * @property string $minute_date 
 * @property string $details 
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class LibraryMeetingMinutes extends \yii\db\ActiveRecord
{   
    public $arrClass=array('1'=>'COMMITTEE MEMBERS','2'=>'INTRODUCTION','3'=>'FOLLOW-UP ACTIONS FROM PREVIOUS MANAGEMENT REVIEWS', '4'=>'RESULT OF INTERNAL AND EXTERNAL AUDITS', '5'=>'FEEDBACK FROM CLIENTS AND INTERESTED PARTIES', '6'=>'SAFEGUARDING IMPARTIALITY', '7'=>'STATUS OF CORRECTIVE ACTION', '8'=>'STATUS OF ACTIONS TO ADDRESS RISKS', '9'=>'RESULT OF CONTINUOUS PERFORMANCE MONITORING', '10'=>'FULFILMENT OF OBJECTIVES', '11'=>'CHANGES THAT COULD AFFECT THE MANAGEMENT SYSTEM', '12'=>'APPEALS AND COMPLAINTS', '13'=>'DATE OF NEXT MEETING');
    public $arrEnumClass=array('class1'=>'1','class2'=>'2','class3'=>'3');

    public $arrRaised=array('1'=>'Raised 1','2'=>'Raised 2','3'=>'Raised 3');
    public $arrEnumRaised=array('raised1'=>'1','raised2'=>'2','raised3'=>'3');

    public $arrStatus=array('1'=>'OPEN','2'=>'CLOSED');
	public $arrEnumStatus=array('open'=>'1','closed'=>'2');

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_meeting_minutes';
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
            [['details'], 'string'],
            [['meeting_id','raised_id','status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public function getMinuteslogs()
    {
        return $this->hasMany(LibraryMeetingMinutesLog::className(), ['minutes_id' => 'id']);
    }
	
	public function getMeeting()
    {
        return $this->hasOne(LibraryMeeting::className(), ['id' => 'meeting_id']);
    }

}
