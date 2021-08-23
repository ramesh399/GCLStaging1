<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class AuditBrandConsent extends \yii\db\ActiveRecord
{
   
    public static function tableName()
    {
        return 'tbl_audit_brand_consent';
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
   
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App Id',
            'unit_id' => 'Unit Id',
            'brand_id' => 'Brand Id',
            'brand_file'=> 'Brand File',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
	
	

}
