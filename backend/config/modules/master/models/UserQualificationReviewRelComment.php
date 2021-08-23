<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_user_qualified_review_question".
 *
 * @property int $id
 * @property int $user_qualification_review_id
 * @property int $qualification_question_id
 * @property int $user_qualification_review_comment_id
 */
class UserQualificationReviewRelComment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_qualification_review_rel_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_qualification_review_id', 'qualification_question_id', 'user_qualification_review_comment_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_qualification_review_id' => 'User Qualification Review ID',
            'qualification_question_id' => 'Qualification Question ID',
            'user_qualification_review_comment_id' => 'User Qualification Review Comment ID',
        ];
    }
}
