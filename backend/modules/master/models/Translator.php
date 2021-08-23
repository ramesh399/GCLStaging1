<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_translator".
 *
 * @property int $id
 * @property string $country
 * @property string $surname
 * @property string $employment
 * @property string $language1
 * @property string $language2
 * @property string $language3
 * @property string $language4
 * @property string $email
 * @property string $phone
 * @property string $status
 * @property string $filename
 */
class Translator extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_translator';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['country', 'surname', 'employment', 'language1', 'email', 'phone', 'status', 'filename'], 'required'],
            [['country', 'surname', 'employment', 'language1', 'language2', 'language3', 'language4', 'email', 'phone', 'status', 'filename'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'country' => 'Country',
            'surname' => 'Surname',
            'employment' => 'Employment',
            'language1' => 'Language1',
            'language2' => 'Language2',
            'language3' => 'Language3',
            'language4' => 'Language4',
            'email' => 'Email',
            'phone' => 'Phone',
            'status' => 'Status',
            'filename' => 'Filename',
        ];
    }
}
