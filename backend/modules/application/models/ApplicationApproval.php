<?php

namespace app\modules\application\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_application_approval".
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id
 * @property string $comment
 * @property int $status 0=Open,1=Approval in Progress,2=Accept,3=Reject,4=More Information
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class ApplicationApproval extends \yii\db\ActiveRecord
{
    public $statusarr = array('0'=>'Approval in Process','1'=>'Accepted','2'=>'Rejected');  //,'3'=>'More Information'
	public $arrEnumStatus = array('approval_in_process'=>'0','accepted'=>'1','rejected'=>'2'); //,'more_information'=>'3'
    
    public $approvestatusarr = array('1'=>'Approve','2'=>'Reject');

    //public $arrApprovalStatus=array( '0'=>'Open','1'=>'Approval in Process','2'=>'Accepted','3'=>'Rejected','4'=>'More Information' );

    //public $dbstatusarr = array('1'=>'Accepted','1'=>'Accepted','2'=>'Rejected','3'=>'More Information'); 
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_approval';
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
            [['app_id', 'user_id', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['comment'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App ID',
            'user_id' => 'User ID',
            'comment' => 'Comment',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
    public function getUsername()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
