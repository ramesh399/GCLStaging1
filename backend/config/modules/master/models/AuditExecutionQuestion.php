<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_audit_execution_question".
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $interpretation
 * @property string $expected_evidence
 * @property int $file_upload_required
 * @property string $positive_finding_default_comment
 * @property string $negative_finding_default_comment
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class AuditExecutionQuestion extends \yii\db\ActiveRecord
{
    public $arrFindingAnswer = ['1'=>'Yes','2'=>'No','3'=>'Not Applicable'];
	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_execution_question';
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
            [['name', 'interpretation', 'expected_evidence', 'positive_finding_default_comment', 'negative_finding_default_comment'], 'string'],
            [['file_upload_required', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['code'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'code' => 'Code',
            'interpretation' => 'Interpretation',
            'expected_evidence' => 'Expected Evidence',
            'file_upload_required' => 'File Upload Required',
            'positive_finding_default_comment' => 'Positive Finding Default Comment',
            'negative_finding_default_comment' => 'Negative Finding Default Comment',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public function getAuditexecutionquestionprocess()
    {
        return $this->hasMany(AuditExecutionQuestionProcess::className(), ['audit_execution_question_id' => 'id']);
    }

    public function getAuditexecutionquestionbsector()
    {
        return $this->hasMany(AuditExecutionQuestionBusinessSector::className(), ['audit_execution_question_id' => 'id']);
    }

    public function getAuditexecutionquestionstd()
    {
        return $this->hasMany(AuditExecutionQuestionStandard::className(), ['audit_execution_question_id' => 'id']);
    }

    public function getAuditexecutionquestionfindings()
    {
        return $this->hasMany(AuditExecutionQuestionFindings::className(), ['audit_execution_question_id' => 'id']);
    }
	
	public function getAuditexecutionquestionnonconformity()
    {
        return $this->hasMany(AuditExecutionQuestionNonConformity::className(), ['audit_execution_question_id' => 'id']);
    }

    public function getSubtopic()
    {
        return $this->hasOne(SubTopic::className(), ['id' => 'sub_topic_id']);
    }
}
