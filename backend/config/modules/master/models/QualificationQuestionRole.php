<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_qualification_question_role".
 *
 * @property int $id
 * @property int $qualification_question_id
 * @property int $role_id
 */
class QualificationQuestionRole extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_qualification_question_role';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['qualification_question_id', 'role_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'qualification_question_id' => 'Qualification Question ID',
            'role_id' => 'Role ID',
        ];
    }
	
	public function getRole()
    {
        return $this->hasOne(Role::className(), ['id' => 'role_id']);
    }
}
