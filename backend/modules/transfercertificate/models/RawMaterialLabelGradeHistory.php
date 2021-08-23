<?php

namespace app\modules\transfercertificate\models;

use Yii;
/**
 * This is the model class for table "tbl_tc_raw_material_label_grade_history".
 *
 * @property int $id
 * @property string $raw_material_history_id
 * @property int $label_grade_id
 */
class RawMaterialLabelGradeHistory extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_raw_material_label_grade_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['raw_material_history_id', 'label_grade_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'raw_material_history_id' => 'Name',          				
            'label_grade_id' => 'Label Grade Id',
        ];
    }	
    
    public function getLabelgrade()
    {
        return $this->hasOne(TcStandardLabelGrade::className(), ['id' => 'label_grade_id']);
    }
}
