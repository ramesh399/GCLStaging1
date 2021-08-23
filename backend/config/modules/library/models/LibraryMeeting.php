<?php

namespace app\modules\library\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_library_meeting".
 *
 * @property int $id
 * @property string $type
 * @property string $meeting_date
 * @property string $attendees 
 * @property string $apologies 
 * @property string $location 
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class LibraryMeeting extends \yii\db\ActiveRecord
{
    public $arrType=array('1'=>'MANAGEMENT REVIEW','2'=>'IMPARTIALITY COMMITEE');
    public $arrEnumType=array('management Review'=>'1','impartiality commitee'=>'2');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_meeting';
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
            [['apologies', 'location','attendees'], 'string'],
            [['type','status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'meeting_date' => 'Meeting Date',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
    
    public function getLibrarymeetingminutes()
    {
        return $this->hasMany(LibraryMeetingMinutes::className(), ['meeting_id' => 'id']);
    }

}
