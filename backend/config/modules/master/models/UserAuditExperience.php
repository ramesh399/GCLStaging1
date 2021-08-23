<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_user_audit_experience".
 *
 * @property int $id
 * @property int $user_id
 * @property int $standard_id
 * @property string $year
 * @property string $company
 * @property string $cb
 * @property string $days
 */
class UserAuditExperience extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_audit_experience';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'standard_id'], 'integer'],
            [['days'], 'required'],
            [['days'], 'number'],
            [['year'], 'string', 'max' => 25],
            [['company'], 'string', 'max' => 255],
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
            'standard_id' => 'Standard ID',
            'year' => 'Year',
            'company' => 'Company',
            'cb' => 'Cb',
            'days' => 'Days',
        ];
    }

    public function getUserauditexperienceprocess()
    {
        return $this->hasMany(UserAuditExperienceProcess::className(), ['user_audit_experience_id' => 'id']);
    }

    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }

    public function getCbdetails()
    {
        return $this->hasOne(Cb::className(), ['id' => 'cb']);
    }
}
