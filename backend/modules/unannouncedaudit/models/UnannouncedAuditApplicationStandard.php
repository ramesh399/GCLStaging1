<?php

namespace app\modules\unannouncedaudit\models;

use Yii;
use yii\db\ActiveRecord;
use app\modules\master\models\Standard;

/**
 * This is the model class for table "tbl_unannounced_audit_application_standard".
 *
 * @property int $id
 * @property int $unannounced_audit_app_id
 * @property int $standard_id
 */
class UnannouncedAuditApplicationStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_unannounced_audit_application_standard';
    }

    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['standard_id'], 'integer'],
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
    

    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
}
