<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;

/**
 * This is the model class for table "tbl_audit_report_sampling".
 *
 * @property int $id
 * @property int $audit_id
 * @property string $operator_title
 * @property string $sampling_date
 * @property string $operator_responsible_person
 * @property string $sample_no
 * @property string $staff_who_took_sample
 * @property string $type_of_samples
 * @property string $samples_were_taken_from
 * @property string $storage_room
 * @property string $processing_line
 * @property string $other_such_as_market
 * @property string $number_of_sub_samples_per_sample
 * @property string $describe_other_details_of_sampling_method
 * @property string $reason
 * @property string $further_comments
 * @property string $representative_sealed
 * @property string $representative_unsealed
 * @property string $representative_sample_bag_number
 * @property string $operator_sealed
 * @property string $operator_unsealed
 * @property string $operator_sample_bag_number
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class AuditReportSampling extends \yii\db\ActiveRecord
{
    public $arrSuspicion=array('1'=>'Yes','2'=>'No');
    public $arrEnumSuspicion=array('yes'=>'1','no'=>'2');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_sampling';
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
            [['audit_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
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
    
    public function getSamplinglist()
    {
        return $this->hasMany(AuditReportSamplingList::className(), ['audit_report_sampling_id' => 'id']);
    }

    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
