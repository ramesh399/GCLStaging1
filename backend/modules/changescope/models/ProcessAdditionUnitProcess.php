<?php

namespace app\modules\changescope\models;

use Yii;
use app\modules\master\models\Process;
/**
 * This is the model class for table "tbl_cs_process_addition_unit_process".
 *
 * @property int $id
 * @property int $process_addition_unit_id
 * @property int $process_id
 */
class ProcessAdditionUnitProcess extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_process_addition_unit_process';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
        
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'process_addition_unit_id' => 'Process Addition Unit',
            'process_id' => 'Process ID',
        ];
    }
	
	public function getProcess()
    {
        return $this->hasOne(Process::className(), ['id' => 'process_id']);
    }

    
}
