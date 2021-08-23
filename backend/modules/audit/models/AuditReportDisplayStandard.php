<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;

/**
 * This is the model class for table "tbl_audit_report_display_standard".
 *
 * @property int $id
 */
class AuditReportDisplayStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_display_standard';
    }

    public function behaviors()
    {
        return [
        
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
           // [['audit_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
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
