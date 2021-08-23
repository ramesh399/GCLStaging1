<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_product_type_material".
 *
 * @property int $id
 * @property int $product_type_id
 * @property string $name
 * @property string $code
 * @property string $description
 * @property int $status
 * @property int $approval_status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class ProductTypeMaterialComposition extends \yii\db\ActiveRecord
{
    public $material_type= ['1'=>'Certified','2'=>'Non Certified'];
    public $arrEnumMaterialType= ['certified'=>'1','non-certified'=>'2'];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_product_type_material';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_type_id', 'status', 'approval_status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['description'], 'string'],
            [['name', 'code'], 'string', 'max' => 255],
			//[['product_id','product_type_id','name'], 'unique'],
			//['name', 'unique', 'targetAttribute' => ['product_id', 'name' => 'product_type_id']]
			['name', 'unique', 'targetAttribute' => ['name', 'product_id','product_type_id'],'filter' => ['!=','status' ,2],'message' => 'The combination of "Product Category", "Product Description" and "Material Name" has already been taken.'],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at','updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
			'product_id' => 'Product',
            'product_type_id' => 'Product Type',
            'name' => 'Material Composition Name',
            'code' => 'Code',
            'description' => 'Description',
            'status' => 'Status',
            'approval_status' => 'Approval Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
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
}
