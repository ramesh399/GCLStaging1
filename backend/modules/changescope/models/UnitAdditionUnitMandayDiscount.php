<?php
namespace app\modules\changescope\models;

use Yii;
use app\modules\master\models\Standard;

/**
 * This is the model class for table "tbl_cs_unit_addition_unit_manday_discount".
 *
 * @property int $id
 * @property int $unit_addition_unit_manday_id
 * @property int $standard_id
 * @property int $certificate_standard_id 
 * @property float $discount
 * @property int $status
 * @property int $same_standard_certified 
 */
class UnitAdditionUnitMandayDiscount extends \yii\db\ActiveRecord
{
	
	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_unit_addition_unit_manday_discount';
    }

    
    public function rules()
    {
        return [
            [['unit_addition_unit_manday_id', 'standard_id', 'certificate_standard_id', 'status', 'same_standard_certified'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'unit_addition_unit_manday_id' => 'Unit Addition ID',
        ];
    }
	
	public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }

     
}
