<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_audit_planning_questions".
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $guidance
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class AuditPlanningQuestions extends \yii\db\ActiveRecord
{
    public $arrAuditTye = ['1'=>'Initial','2'=>'Followup','3'=>'Unannounced'];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_planning_questions';
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
            [['name', 'guidance'], 'string'],
            [['status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
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
            'guidance' => 'Guidance',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
	
	public function getRiskcategory()
    {
        return $this->hasMany(AuditPlanningQuestionRiskCategory::className(), ['audit_planning_question_id' => 'id']);
    }

    public function getAudittype()
    {
        return $this->hasMany(AuditPlanningQuestionsAuditType::className(), ['planning_question_id' => 'id']);
    }

}
