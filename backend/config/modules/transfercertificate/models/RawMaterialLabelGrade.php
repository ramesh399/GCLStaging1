<?php

namespace app\modules\transfercertificate\models;

use Yii;
/**
 * This is the model class for table "tbl_tc_raw_material_label_grade".
 *
 * @property int $id
 * @property string $raw_material_id
 * @property int $label_grade_id
 */
class RawMaterialLabelGrade extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_raw_material_label_grade';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['raw_material_id', 'label_grade_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'raw_material_id' => 'Name',          				
            'label_grade_id' => 'Standard Id',
        ];
    }	
    
    public function getLabelgrade()
    {
        return $this->hasOne(TcStandardLabelGrade::className(), ['id' => 'label_grade_id']);
    }
}
