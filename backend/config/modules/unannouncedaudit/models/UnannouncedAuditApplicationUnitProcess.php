<?php

namespace app\modules\unannouncedaudit\models;

use Yii;
use app\modules\master\models\Process;
/**
 * This is the model class for table "tbl_unannounced_audit_application_unit_process".
 *
 * @property int $id
 * @property int $unit_id
 * @property int $process_id
 */
class UnannouncedAuditApplicationUnitProcess extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_unannounced_audit_application_unit_process';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['unannounced_audit_app_unit_id', 'process_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'unannounced_audit_app_unit_id' => 'Unit ID',
            'process_id' => 'Process ID',
        ];
    }

    public function getProcess()
    {
        return $this->hasOne(Process::className(), ['id' => 'process_id']);
    }
}
