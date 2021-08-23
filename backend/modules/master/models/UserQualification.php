<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_user_qualification".
 *
 * @property int $id
 * @property int $user_id
 * @property string $qualification
 * @property string $board_university
 * @property string $subject
 * @property string $passing_year
 * @property string $percentage
 */
class UserQualification extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_qualification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['qualification', 'board_university', 'start_year', 'end_year'], 'string', 'max' => 255],
            [['subject'], 'string', 'max' => 50],
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
            'qualification' => 'Qualification',
            'board_university' => 'Board University',
            'subject' => 'Subject',
            'passing_year' => 'Passing Year',
            'percentage' => 'Percentage',
        ];
    }
}
