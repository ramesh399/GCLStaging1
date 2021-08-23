<?php

namespace app\modules\master\models;

use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_user_qualification_review_history_rel_role_standard_comment".
 *
 * @property int $id
 * @property int $review_history_rel_role_standard_id
 * @property int $review_history_comment_id
 */
class UserQualificationReviewHistoryRelRoleStandardComment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_qualification_review_history_rel_role_standard_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['review_history_rel_role_standard_id', 'review_history_comment_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'review_history_rel_role_standard_id' => 'Role Standard ID',
            'review_history_comment_id' => 'History Comment ID'
        ];
    }
}
