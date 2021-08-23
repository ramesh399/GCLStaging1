<?php

namespace app\modules\application\models;
use app\modules\master\models\Standard;
use app\modules\master\models\StandardLabelGrade;

use Yii;

/**
 * This is the model class for table "tbl_application_product_standard".
 *
 * @property int $id
 * @property int $application_product_id
 * @property int $standard_id
 * @property int $label_grade_id
 */
class ApplicationProductStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_product_standard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['application_product_id', 'standard_id', 'label_grade_id'], 'required'],
            [['application_product_id', 'standard_id', 'label_grade_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'application_product_id' => 'Application Product ID',
            'standard_id' => 'Standard ID',
            'label_grade_id' => 'Label Grade ID',
        ];
    }
    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
    public function getLabelgrade()
    {
        return $this->hasOne(StandardLabelGrade::className(), ['id' => 'label_grade_id']);
    }
    public function getAppproduct()
    {
        return $this->hasOne(ApplicationProduct::className(), ['id' => 'application_product_id']);
    }

    public function getProductmaterial()
    {
        return $this->hasMany(ApplicationProductMaterial::className(), ['app_product_id' => 'application_product_id']);
    }

    public function getAppproducttemp()
    {
        return $this->hasOne(ApplicationProductCertificateTemp::className(), ['application_product_standard_id' => 'id']);
    }

    
}
