<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_audit_reviewer_review".
 *
 * @property int $id
 * @property int $audit_plan_id
 * @property int $user_id
 * @property string $comment
 * @property string $answer
 * @property int $status 0=Open,1=Review in Process,2=Review Completed,3=Rejected
 * @property int $review_result 1=> Send Audit Plan, 2=>Don’t Send Audit Plan
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class AuditReviewerReview extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_reviewer_review';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at','updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_plan_id', 'user_id', 'status', 'review_result', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['comment'], 'string'],
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
            'audit_plan_id' => 'Audit ID',
            'user_id' => 'User ID',
            'comment' => 'Comment',
            'answer' => 'Answer',
            'status' => 'Status',
            'review_result' => 'Review Result',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public function getAuditreviewerreview()
    {
        return $this->hasMany(AuditReviewerReviewChecklistComment::className(), ['audit_reviewer_review_id' => 'id']);
    }
}
