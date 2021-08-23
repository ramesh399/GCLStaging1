<?php

namespace app\modules\application\models;

use Yii;
use app\modules\master\models\Product;
use app\modules\master\models\ProductType;
use app\modules\master\models\ProductTypeMaterialComposition;
use app\modules\master\models\Standard;
use app\modules\master\models\StandardLabelGrade;

use app\modules\application\models\ApplicationProductStandard;
/**
 * This is the model class for table "tbl_application_product".
 *
 * @property int $id
 * @property int $app_id
 * @property int $product_id
 * @property string $wastage
 */
class ApplicationProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_id', 'product_id'], 'integer'],
            //[['wastage'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App ID',
            'product_id' => 'Product ID',
            'wastage' => 'Wastage',
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
    public function getProductmaterial()
    {
        return $this->hasMany(ApplicationProductMaterial::className(), ['app_product_id' => 'id']);
    }
    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
        //return $this->hasMany(ApplicationProductStandard::className(), ['application_product_id' => 'id']);
    }
    public function getStandardlabelgrade()
    {
        return $this->hasOne(StandardLabelGrade::className(), ['id' => 'label_grade_id']);
        //return $this->hasMany(ApplicationProductStandard::className(), ['application_product_id' => 'id']);
    }
    
    
    public function getProductstandard()
    {
        return $this->hasMany(ApplicationProductStandard::className(), ['application_product_id' => 'id']);
    }
    
}
