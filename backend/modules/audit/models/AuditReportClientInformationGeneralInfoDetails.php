<?php

namespace app\modules\audit\models;

use app\modules\master\models\User;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_audit_report_client_information_general_info_details".
 *
 * @property int $id
 * @property int $client_information_general_info_id
 * @property string $name
 */
class AuditReportClientInformationGeneralInfoDetails extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_client_information_general_info_details';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
        ];
    }

    
    public function rules()
    {
        return [
            [['client_information_general_info_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_information_general_info_id' => 'General Info ID',
        ];
    }
}