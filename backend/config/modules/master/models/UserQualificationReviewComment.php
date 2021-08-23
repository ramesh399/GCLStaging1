<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_user_qualification_review_comment".
 *
 * @property int $id
 * @property int $user_qualification_review_id
 * @property int $qualification_question_id
 * @property int $recurring_period
 * @property string $question
 * @property string $answer
 * @property string $comment
 * @property string $valid_until
 * @property string $file
 */
class UserQualificationReviewComment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_qualification_review_comment';
    }

    /**
     * {@inheritdoc}
     */

    
    public function rules()
    {
        return [
            [['qualification_question_id', 'recurring_period'], 'integer'],
            [['question', 'comment'], 'string'],
            [['valid_until'], 'safe'],
            [['answer', 'file'], 'string', 'max' => 255],
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
            'qualification_question_id' => 'Qualification Question ID',
            'recurring_period' => 'Recurring Period',
            'question' => 'Question',
            'answer' => 'Answer',
            'comment' => 'Comment',
            'valid_until' => 'Valid Until',
            'file' => 'File',
        ];
    }
	
	public function getQualificationquestion()
    {
        return $this->hasOne(QualificationQuestion::className(), ['id' => 'qualification_question_id']);
    }
}
