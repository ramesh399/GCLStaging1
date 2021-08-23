<?php
namespace app\modules\application\models;

use Yii;
use app\modules\master\models\ProductTypeMaterialComposition;
/**
 * This is the model class for table "tbl_application_product_material".
 *
 * @property int $id
 * @property int $app_product_id
 * @property int $material_id
 * @property int $material_type_id
 * @property string $percentage
 */
class ApplicationProductMaterial extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_product_material';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_product_id', 'material_id', 'material_type_id'], 'integer'],
            [['percentage'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_product_id' => 'App Product ID',
            'material_id' => 'Material ID',
            'material_type_id' => 'Material Type ID',
            'percentage' => 'Percentage',
        ];
    }
    public function getMaterial()
    {
        return $this->hasOne(ProductTypeMaterialComposition::className(), ['id' => 'material_id']);
    }
}
