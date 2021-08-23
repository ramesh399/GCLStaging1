<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_audit_plan_review_history".
 *
 * @property int $id
 * @property int $audit_plan_history_id
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
class AuditPlanReviewHistory extends \yii\db\ActiveRecord
{
    public $arrStatus=array('0'=>'Open','1'=>"Review in Process",'2'=>'Review Completed','3'=>'Rejected');

    public $arrReviewAnswer=array('1'=>'Accepted','2'=>'Rejected','3'=>'Change Audit Plan');
    public $arrReviewerStatus=array('1'=>'Accept','2'=>'Reject','3'=>'Change Audit Plan');
    
    public $arrReviewStatus=array('0'=>'Open','1'=>'Review in Process','2'=>'Review Completed','3'=>'Rejected');
    public $arrReviewResult=[6=>'Critical',5=>'High',4=>'Medium',3=>'Low',2=>'Very Low',1=>'N/A'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_plan_review_history';
    }

    /**
     * {@inheritdoc}
     */
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

    
    public function rules()
    {
        return [
            [['audit_plan_history_id', 'user_id', 'review_result', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['comment'], 'string'],
            [['review_result'], 'required'],
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
            'audit_plan_history_id' => 'Audit Plan ID',
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

    

    public function getAuditplanreviewchecklistcommenthistory()
    {
        return $this->hasMany(AuditPlanReviewChecklistCommentHistory::className(), ['audit_plan_review_history_id' => 'id']);
    }
    public function getAuditplanunitreviewcommenthistory()
    {
        return $this->hasMany(AuditPlanUnitReviewChecklistCommentHistory::className(), ['audit_plan_review_history_id' => 'id']);
    }
    public function getReviewer()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
