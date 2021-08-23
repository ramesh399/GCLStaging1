<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_audit_report_ncn_report".
 *
 * @property int $id
 * @property int $audit_id
 * @property int $unit_id
 * @property string $effectiveness_of_corrective_actions
 * @property string $audit_team_recommendation
 * @property string $measures_for_risk_reduction
 * @property string $summary_of_evidence
 * @property string $potential_high_risk_situations
 * @property string $entities_and_processes_visited
 * @property string $people_interviewed
 * @property string $type_of_documents_reviewed
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class AuditReportNcnReport extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_ncn_report';
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
            [['effectiveness_of_corrective_actions', 'audit_team_recommendation', 'measures_for_risk_reduction', 'summary_of_evidence', 'potential_high_risk_situations', 'entities_and_processes_visited', 'people_interviewed','type_of_documents_reviewed'], 'string'],
            [['audit_id', 'unit_id',  'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            
        ];
    }
    
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
