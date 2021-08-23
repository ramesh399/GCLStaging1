<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_user_audit_experience_process".
 *
 * @property int $id
 * @property int $user_audit_experience_id
 * @property int $process_id
 */
class UserAuditExperienceProcess extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_audit_experience_process';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_audit_experience_id', 'process_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_audit_experience_id' => 'User Audit Experience ID',
            'process_id' => 'Process ID',
        ];
    }

    public function getProcess()
    {
        return $this->hasOne(Process::className(), ['id' => 'process_id']);
    }
}
