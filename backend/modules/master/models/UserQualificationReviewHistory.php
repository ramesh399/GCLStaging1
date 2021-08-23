<?php
namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_user_qualification_review_history".
 *
 * @property int $id
 * @property int $user_id
 * @property int $created_by
 * @property int $created_at
 */
class UserQualificationReviewHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_qualification_review_history';
    }

    /**
     * {@inheritdoc}
     */
    

    public function rules()
    {
        return [
            [['user_id', 'created_by', 'created_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
        ];
    }
    public function getCreatedby()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
