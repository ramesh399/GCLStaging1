<?php

namespace app\modules\changescope\models;

use Yii;
use app\modules\master\models\Standard;
use app\modules\master\models\StandardLabelGrade;
use app\modules\application\models\ApplicationProductCertificateTemp;
/**
 * This is the model class for table "tbl_cs_product_addition_product_standard".
 *
 * @property int $id
 * @property int $product_addition_product_id
 * @property int $standard_id
 * @property int $label_grade_id
 */
class ProductAdditionProductStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_product_addition_product_standard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_addition_product_id', 'standard_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_addition_product_id' => 'Unit Addition Unit ID',
            'standard_id' => 'Standard ID',
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
	
    public function getAppproducttemp()
    {
        return $this->hasOne(ApplicationProductCertificateTemp::className(), ['application_product_standard_id' => 'id']);
    }
}
