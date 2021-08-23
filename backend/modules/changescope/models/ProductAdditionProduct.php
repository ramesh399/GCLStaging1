<?php

namespace app\modules\changescope\models;

use Yii;
use app\modules\master\models\ProductType;
use app\modules\master\models\Product;
/**
 * This is the model class for table "tbl_cs_product_addition_product".
 *
 * @property int $id
 * @property int $product_addition_id
 * @property int $product_id
 * @property int $product_type_id
 * @property float $wastage
 * @property int $standard_id
 * @property int $label_grade_id
 * @property string $material_name
 */
class ProductAdditionProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_product_addition_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_addition_id', 'product_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_addition_id' => 'Product Addition ID',
            'product_id' => 'Product ID',
        ];
    }

    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    public function getProducttype()
    {
        return $this->hasOne(ProductType::className(), ['id' => 'product_type_id']);
    }
    
    public function getProductaddition()
    {
        return $this->hasOne(ProductAddition::className(), ['id' => 'product_addition_id']);
    }

    public function getAdditionproductstandard()
    {
        return $this->hasMany(ProductAdditionProductStandard::className(), ['product_addition_product_id' => 'id']);
    }

    public function getAdditionproductmaterial()
    {
        return $this->hasMany(ProductAdditionProductMaterial::className(), ['product_addition_product_id' => 'id']);
    }
	
    public function getProductstandard()
    {
        return $this->hasMany(ProductAdditionProductStandard::className(), ['product_addition_product_id' => 'id']);
    }
    
    
}
