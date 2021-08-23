<?php

namespace app\modules\master\models;

use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_user_qualification_review_history_comment".
 *
 * @property int $id
 * @property int $review_history_id
 * @property int $review_history_rel_role_standard_id
 * @property int $qualification_question_id
 * @property int $recurring_period
 * @property string $question
 * @property int $answer 0=Not Qualified,1=Qualified,2=Approved
 * @property string $comment
 * @property string $valid_until
 * @property string $file
 */
class UserQualificationReviewHistoryComment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_qualification_review_history_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['review_history_id'], 'required'],
            [['review_history_id', 'review_history_rel_role_standard_id', 'qualification_question_id', 'recurring_period', 'answer'], 'integer'],
            [['question', 'comment'], 'string'],
            [['valid_until'], 'safe'],
            [['file'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'review_history_id' => 'Review History ID',
            'review_history_rel_role_standard_id' => 'Review History Rel Role Standard ID',
            'qualification_question_id' => 'Qualification Question ID',
            'recurring_period' => 'Recurring Period',
            'question' => 'Question',
            'answer' => 'Answer',
            'comment' => 'Comment',
            'valid_until' => 'Valid Until',
            'file' => 'File',
        ];
    }
}
