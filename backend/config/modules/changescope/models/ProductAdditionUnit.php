<?php

namespace app\modules\changescope\models;

use Yii;
use app\modules\application\models\ApplicationUnit;
/**
 * This is the model class for table "tbl_cs_product_addition_unit".
 *
 * @property int $id
 * @property int $product_addition_id
 * @property int $unit_id
 */
class ProductAdditionUnit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_product_addition_unit';
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
            'product_addition_id' => 'Product Addition ID',
        ];
    }
	
	public function getApplicationunit()
    {
        return $this->hasOne(ApplicationUnit::className(), ['id' => 'unit_id']);
    }
    
    public function getUnitproduct()
    {
        return $this->hasMany(ProductAdditionUnitProduct::className(), ['product_addition_unit_id' => 'id']);
    }
}
