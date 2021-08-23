<?php
namespace app\modules\application\models;

use Yii;

/**
 * This is the model class for table "tbl_application_unit_review_comment".
 *
 * @property int $id
 * @property int $review_id
 * @property int $unit_id
 * @property int $question_id
 * @property string $question
 * @property string $answer
 * @property string $comment
 */
class ApplicationUnitReviewComment extends \yii\db\ActiveRecord
{
    public $arrAnswer=[6=>'Critical',5=>'High',4=>'Medium',3=>'Low',2=>'Very Low',1=>'N/A'];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_unit_review_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['review_id', 'unit_id', 'question_id'], 'integer'],
            [['question', 'comment'], 'string'],
            [['answer'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'review_id' => 'Review ID',
            'unit_id' => 'Unit ID',
            'question_id' => 'Question ID',
            'question' => 'Question',
            'answer' => 'Answer',
            'comment' => 'Comment',
        ];
    }
}
