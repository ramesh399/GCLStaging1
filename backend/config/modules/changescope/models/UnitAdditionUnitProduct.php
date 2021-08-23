<?php

namespace app\modules\changescope\models;

use Yii;

use app\modules\application\models\ApplicationProductStandard;
/**
 * This is the model class for table "tbl_cs_unit_addition_unit_product".
 *
 * @property int $id
 * @property int $unit_addition_unit_id
 * @property int $application_product_standard_id
 */
class UnitAdditionUnitProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_unit_addition_unit_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['unit_addition_unit_id', 'application_product_standard_id'], 'integer'],
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
            'application_product_standard_id' => 'Process ID',
        ];
    }
	
    public function getProduct()
    {
        return $this->hasOne(ApplicationProductStandard::className(), ['id' => 'application_product_standard_id']);
    }
    
}
