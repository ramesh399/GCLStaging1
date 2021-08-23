<?php

namespace app\modules\changescope\models;

use Yii;
use app\modules\application\models\ApplicationUnit;
/**
 * This is the model class for table "tbl_cs_withdraw_unit".
 *
 * @property int $id
 * @property int $withdraw_id
 * @property int $unit_id
 */
class WithdrawUnit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_withdraw_unit';
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
            'withdraw_id' => 'Withdraw ID',
        ];
    }
	
	public function getApplicationunit()
    {
        return $this->hasOne(ApplicationUnit::className(), ['id' => 'unit_id']);
    }
    /*
    public function getUnitproduct()
    {
        return $this->hasMany(WithdrawUnitProduct::className(), ['withdraw_unit_id' => 'id']);
    }
    */
}
