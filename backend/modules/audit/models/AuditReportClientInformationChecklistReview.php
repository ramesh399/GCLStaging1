<?php

namespace app\modules\audit\models;

use app\modules\master\models\User;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_audit_report_client_information_checklist_review".
 *
 * @property int $id
 * @property int $unit_id
 * @property int $audit_id
 * @property string $comment
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class AuditReportClientInformationChecklistReview extends \yii\db\ActiveRecord
{
	// public $arrReviewAnswer=array('1'=>'Accepted','2'=>'Rejected','3'=>'More Information from customer');
    // public $arrReviewerStatus=array('1'=>'Accept','2'=>'Reject','3'=>'More Information from customer');
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_client_information_checklist_review';
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
            [['audit_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['comment'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_id' => 'Audit ID',
            'comment' => 'Comment',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
    
    public function getReviewcomment()
    {
        return $this->hasMany(AuditReportClientInformationChecklistReviewComment::className(), ['client_information_checklist_review_id' => 'id']);
    }
	
}
