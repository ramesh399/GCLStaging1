<?php
namespace app\modules\application\models;

use Yii;
//use app\modules\master\models\ProductTypeMaterialComposition;
/**
 * This is the model class for table "tbl_application_product_material".
 *
 * @property int $id
 * @property int $app_product_id
 * @property int $material_id
 * @property int $material_type_id
 * @property string $percentage
 */
class ApplicationProductCertificateTemp extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_product_certificate_temp';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_id'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App ID'
        ];
    }

    public function getProductstandard()
    {
        return $this->hasOne(ApplicationProductStandard::className(), ['id' => 'application_product_standard_id']);
    }

    public function getProduct()
    {
        return $this->hasOne(ApplicationProduct::className(), ['id' => 'product_id']);
    }

    public function getProductmaterial()
    {
        return $this->hasMany(ApplicationProductMaterial::className(), ['app_product_id' => 'product_id']);
    }
}
