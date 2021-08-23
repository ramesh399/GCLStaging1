<?php

namespace app\modules\application\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_application_approver".
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id
 * @property int $approver_status 1=Current,2=Old
 * @property int $created_by
 * @property int $created_at
 */
class ApplicationApprover extends \yii\db\ActiveRecord
{
	public $arrStatus=array('1'=>'Current','2'=>'Old');
	public $arrEnumStatus=array('current'=>'1','old'=>'2');
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_approver';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_id', 'user_id', 'approver_status', 'created_by', 'created_at'], 'integer'],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
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
            'approver_status' => 'Approver Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
        ];
    }
}
