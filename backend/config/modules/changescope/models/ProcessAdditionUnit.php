<?php

namespace app\modules\changescope\models;

use Yii;
use app\modules\master\models\Standard;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationUnitStandard;
/**
 * This is the model class for table "tbl_cs_process_addition_unit".
 *
 * @property int $id
 * @property int $process_addition_id
 * @property int $unit_id
 */
class ProcessAdditionUnit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_process_addition_unit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
          //  [['app_id', 'unit_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'unit_id' => 'Unit ID',
            'process_addition_id' => 'Process Addition ID',
        ];
    }
	
	public function getApplicationunit()
    {
        return $this->hasOne(ApplicationUnit::className(), ['id' => 'unit_id']);
    }
    
    public function getUnitappstandard()
    {
        return $this->hasMany(ApplicationUnitStandard::className(), ['unit_id' => 'unit_id']);
    }

	public function getUnitprocess()
    {
        return $this->hasMany(ProcessAdditionUnitProcess::className(), ['process_addition_unit_id' => 'id'])->groupBy('process_id');
    }

    public function getUnitprocessall()
    {
        return $this->hasMany(ProcessAdditionUnitProcess::className(), ['process_addition_unit_id' => 'id']);
    }

    
}
