<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_product_type_material_composition_standard".
 *
 * @property int $id
 * @property int $material_composition_id
 * @property int $standard_id
 * @property string $label_grade_id
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class ProductTypeMaterialCompositionStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_product_type_material_composition_standard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['material_composition_id', 'standard_id', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['label_grade_id'], 'string', 'max' => 255],
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
            'material_composition_id' => 'Material Composition ID',
            'standard_id' => 'Standard ID',
            'label_grade_id' => 'Label Grade ID',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
}
