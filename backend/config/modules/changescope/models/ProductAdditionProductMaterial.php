<?php

namespace app\modules\changescope\models;

use Yii;
use app\modules\master\models\ProductTypeMaterialComposition;
/**
 * This is the model class for table "tbl_cs_product_addition_product_material".
 *
 * @property int $id
 * @property int $product_addition_product_id
 * @property int $material_id
 * @property int $material_type_id
 * @property float $percentage
 */
class ProductAdditionProductMaterial extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_product_addition_product_material';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_addition_product_id', 'material_id'], 'integer'],
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
            'material_id' => 'Material ID',
        ];
    }
	

    public function getMaterial()
    {
        return $this->hasOne(ProductTypeMaterialComposition::className(), ['id' => 'material_id']);
    }
    
}
