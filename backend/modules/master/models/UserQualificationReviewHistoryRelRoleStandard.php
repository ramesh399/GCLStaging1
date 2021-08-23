<?php

namespace app\modules\master\models;

use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_user_qualification_review_history_rel_role_standard".
 *
 * @property int $id
 * @property int $qualification_review_history_id
 * @property int $user_role_id
 * @property int $standard_id
 * @property int $qualification_status
 */
class UserQualificationReviewHistoryRelRoleStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_qualification_review_history_rel_role_standard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['qualification_review_history_id', 'user_role_id', 'standard_id', 'qualification_status'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'qualification_review_history_id' => 'Qualification Review History ID',
            'user_role_id' => 'User Role ID',
            'standard_id' => 'Standard ID',
            'qualification_status' => 'Qualification Status',
        ];
    }
}
