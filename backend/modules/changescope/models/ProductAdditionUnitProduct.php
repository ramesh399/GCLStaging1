<?php

namespace app\modules\changescope\models;

use Yii;

/**
 * This is the model class for table "tbl_cs_product_addition_unit_product".
 *
 * @property int $id
 * @property int $product_addition_unit_id
 * @property int $application_product_standard_id
 */
class ProductAdditionUnitProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_product_addition_unit_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['product_addition_unit_id', 'application_product_standard_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_addition_unit_id' => 'Product Addition Product ID',
            'application_product_standard_id' => 'Application Product Standard ID',
        ];
    }
	


    
}
