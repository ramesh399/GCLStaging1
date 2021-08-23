<?php

namespace app\modules\certificate\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
use app\modules\master\models\AuditReviewerRiskCategory;
/**
 * This is the model class for table "tbl_audit_reviewer_review".
 *
 * @property int $id
 * @property int $certificate_id
 * @property int $user_id
 * @property string $comment
 * @property string $answer
 * @property int $status 0=Open,1=Review in Process,2=Review Completed,3=Rejected
 * @property int $risk_category 1=> Send Audit Plan, 2=>Don’t Send Audit Plan
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class CertificateReviewerReview extends \yii\db\ActiveRecord
{
    public $arrRisk=array('0'=>'High','1'=>'Medium','2'=>'Low');
    public $arrEnumRisk=array('high'=>'0','medium'=>'1','low'=>'2'); 
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_certificate_reviewer_review';
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
            [['certificate_id', 'user_id', 'status', 'risk_category', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
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
            'certificate_id' => 'Certificate ID',
            'user_id' => 'User ID',
            'comment' => 'Comment',
            'answer' => 'Answer',
            'status' => 'Status',
            'risk_category' => 'Review Result',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public function getCertificatereviewerreview()
    {
        return $this->hasMany(CertificateReviewerReviewChecklistComment::className(), ['certificate_reviewer_review_id' => 'id']);
    }

    public function getReviewer()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getRiskcategory()
    {
        return $this->hasOne(AuditReviewerRiskCategory::className(), ['id' => 'risk_category']);
    }

}
