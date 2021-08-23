<?php

namespace app\modules\application\models;

use Yii;
use app\modules\master\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_application_renewal".
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id
 * @property int $new_app_id
 * @property int $change_status 1=No Changes in Application,2=Changes in Application
 * @property int $created_by
 * @property int $created_at
 */
class ApplicationRenewal extends \yii\db\ActiveRecord
{
	public $arrStatus=array('1'=>'No Changes in Application','2'=>'Changes in Application');
	public $arrEnumStatus=array('no_changes'=>'1','changes'=>'2');
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_renewal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_id', 'user_id', 'new_app_id', 'change_status', 'created_by', 'created_at'], 'integer'],
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
			'new_app_id' => 'New App ID',
            'change_status' => 'Change Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
        ];
    }

    public function getUsername()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getApplication()
    {
        return $this->hasOne(Application::className(), ['id' => 'app_id']);
    }

    public function getRenewalstandard()
    {
        return $this->hasMany(ApplicationRenewalStandard::className(), ['app_renewal_id' => 'id']);
    }
}
