<?php

namespace app\modules\transfercertificate\models;

use Yii;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_tc_request_reviewer_comment".
 *
 * @property int $id
 * @property int $tc_request_id
 * @property int $tc_request_reviewer_id
 * @property int $status
 * @property string $comment
 * @property int $created_by
 * @property int $created_at 
 */
class RequestReviewerComment extends \yii\db\ActiveRecord
{
    public $arrStatus=array('1'=>'Approve','2'=>'Send Back to OSS','3'=>'Reject');
	public $arrEnumStatus=array('approved'=>'1','send_back_to_franchise'=>'2','reject'=>3);
	public $arrStatusLabel=array('1'=>'Approved','2'=>'Send Back to OSS','3'=>'Rejected');
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_request_reviewer_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tc_request_id', 'tc_request_reviewer_id', 'status', 'created_by', 'created_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tc_request_id' => 'TC Request ID',
            'status' => 'Status',
        ];
    }
    
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }


    
}
