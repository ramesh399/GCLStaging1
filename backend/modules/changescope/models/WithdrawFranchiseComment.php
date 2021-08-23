<?php

namespace app\modules\changescope\models;

use Yii;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_cs_withdraw_franchise_comment".
 *
 * @property int $id
 * @property int $withdraw_id
 * @property int $status
 * @property string $comment
 * @property int $created_by
 * @property int $created_at 
 */
class WithdrawFranchiseComment extends \yii\db\ActiveRecord
{
    public $arrStatus=array('1'=>'Forwarded to Reviewer','2'=>'Send Back to Customer','3'=>'Reject');
	public $arrEnumStatus=array('forwarded_to_reviewer'=>'1','send_back_to_customer'=>'2','reject'=>3);
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_withdraw_franchise_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['withdraw_id', 'status', 'created_by', 'created_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'withdraw_id' => 'Withdraw ID',
            'status' => 'Status',
        ];
    }
	
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    
}
