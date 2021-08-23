<?php

namespace app\modules\changescope\models;

use Yii;
use app\modules\master\models\Process;

/**
 * This is the model class for table "tbl_cs_unit_addition_unit_process".
 *
 * @property int $id
 * @property int $unit_addition_unit_id
 * @property int $process_id
 */
class UnitAdditionUnitProcess extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_unit_addition_unit_process';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['unit_addition_unit_id', 'process_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'unit_addition_unit_id' => 'Unit Addition Unit ID',
            'process_id' => 'Process ID',
        ];
    }
	
	public function getProcess()
    {
        return $this->hasOne(Process::className(), ['id' => 'process_id']);
    }

    
}
