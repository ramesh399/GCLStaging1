<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_user_certification".
 *
 * @property int $id
 * @property int $user_id
 * @property string $certification_name
 * @property string $completed_date
 * @property string $filename
 */
class UserCertification extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_certification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['completed_date'], 'safe'],
            [['certification_name', 'filename'], 'string', 'max' => 255],
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
            'certification_name' => 'Certification Name',
            'completed_date' => 'Completed Date',
            'filename' => 'Filename',
        ];
    }
}
