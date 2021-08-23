<?php

namespace app\modules\audit\models;

use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_audit_report_sampling_list".
 *
 * @property int $id
 * @property int $audit_report_sampling_id
 * @property string $sample_number
 * @property string $taken_from
 */
class AuditReportSamplingList extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_sampling_list';
    }

   
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sample_number', 'taken_from'], 'string'],
            [['audit_report_sampling_id'], 'integer'],
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
    
    
}
