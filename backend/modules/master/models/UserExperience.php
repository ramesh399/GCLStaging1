<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_user_experience".
 *
 * @property int $id
 * @property int $user_id
 * @property string $experience
 * @property string $year
 */
class UserExperience extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_experience';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['experience'], 'string', 'max' => 255],
            //[['year'], 'string', 'max' => 50],
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
            'experience' => 'Experience',
            'responsibility' => 'Responsibility',
            'from_date' => 'From Date',
			'to_date' => 'To Date',
        ];
    }
}
