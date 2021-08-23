<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_user_consultancy_experience_process".
 *
 * @property int $id
 * @property int $user_consultancy_experience_id
 * @property int $process_id
 */
class UserConsultancyExperienceProcess extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_consultancy_experience_process';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_consultancy_experience_id', 'process_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_consultancy_experience_id' => 'User Consultancy Experience ID',
            'process_id' => 'Process ID',
        ];
    }

    public function getProcess()
    {
        return $this->hasOne(Process::className(), ['id' => 'process_id']);
    }
}
