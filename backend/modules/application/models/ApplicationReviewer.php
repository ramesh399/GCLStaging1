<?php

namespace app\modules\application\models;
use app\modules\master\models\User;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_application_reviewer".
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id
 * @property int $reviewer_status 1=Current,2=Old
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class ApplicationReviewer extends \yii\db\ActiveRecord
{
	public $arrStatus=array('1'=>'Current','2'=>'Old');
	public $arrEnumStatus=array('current'=>'1','old'=>'2');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_reviewer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_id', 'user_id', 'reviewer_status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
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
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App ID',
            'user_id' => 'User ID',
            'reviewer_status' => 'Reviewer Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public function getReviewer()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
