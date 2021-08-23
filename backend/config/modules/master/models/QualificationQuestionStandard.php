<?php
namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_qualification_question_standard".
 *
 * @property int $id
 * @property int $qualification_question_id
 * @property int $standard_id
 */
class QualificationQuestionStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_qualification_question_standard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['qualification_question_id', 'standard_id'], 'integer'],
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
            'standard_id' => 'Standard ID',
        ];
    }
	
	public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
}
