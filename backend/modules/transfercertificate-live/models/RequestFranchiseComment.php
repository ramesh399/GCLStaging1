<?php

namespace app\modules\transfercertificate\models;

use Yii;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_tc_request_franchise_comment".
 *
 * @property int $id
 * @property int $tc_request_id
 * @property int $status
 * @property string $comment
 * @property int $created_by
 * @property int $created_at 
 */
class RequestFranchiseComment extends \yii\db\ActiveRecord
{
    public $arrStatus=array('1'=>'Forward to Reviewer','2'=>'Send Back to Customer','3'=>'Reject');
	public $arrEnumStatus=array('forwarded_to_reviewer'=>'1','send_back_to_customer'=>'2','reject'=>3);
	public $arrStatusLabel=array('1'=>'Forwarded to Reviewer','2'=>'Send Back to Customer','3'=>'Rejected');
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_request_franchise_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tc_request_id', 'status', 'created_by', 'created_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tc_request_id' => 'Tc Request ID',
            'status' => 'Status',
        ];
    }
	
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    
}
