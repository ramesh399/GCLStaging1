<?php

namespace app\modules\application\models;

use Yii;
use app\modules\master\models\Product;
/**
 * This is the model class for table "tbl_application_unit_product".
 *
 * @property int $id
 * @property int $unit_id
 * @property int $product_id
 */
class ApplicationUnitProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_unit_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['unit_id', 'application_product_standard_id'], 'integer'],
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
            'application_product_standard_id' => 'Product ID',
        ];
    }

    //public function getProduct()
    //{
    //    return $this->hasOne(Product::className(), ['id' => 'application_product_standard_id']);
   // }
     public function getProduct()
    {
        return $this->hasOne(ApplicationProductStandard::className(), ['id' => 'application_product_standard_id']);
    }
}
