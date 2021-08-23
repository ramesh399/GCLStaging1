<?php

namespace app\modules\changescope\models;

use Yii;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_cs_withdraw_reviewer_comment".
 *
 * @property int $id
 * @property int $withdraw_id
 * @property int $withdraw_reviewer_id
 * @property int $status
 * @property string $comment
 * @property int $created_by
 * @property int $created_at 
 */
class WithdrawReviewerComment extends \yii\db\ActiveRecord
{
    public $arrStatus=array('1'=>'Approve','2'=>'Send Back to OSS','3'=>'Reject');
	public $arrEnumStatus=array('forwarded_to_approval'=>'1','send_back_to_franchise'=>'2','reject'=>3);
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_withdraw_reviewer_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['withdraw_id', 'withdraw_reviewer_id', 'status', 'created_by', 'created_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'withdraw_id' => 'Product Addition ID',
            'status' => 'Status',
        ];
    }
    
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }


    
}
