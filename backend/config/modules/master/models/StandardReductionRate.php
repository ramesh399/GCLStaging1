<?php

namespace app\modules\master\models;

use Yii;
/**
 * This is the model class for table "tbl_standard_reduction_rate".
 *
 * @property int $id
 * @property int $standard_reduction_id
 * @property int $standard_id
 * @property int $reduction_percentage
 */
class StandardReductionRate extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_standard_reduction_rate';
    }

    /**
     * {@inheritdoc}
     */


    public function rules()
    {
        return [
            [['standard_reduction_id', 'standard_id', 'reduction_percentage'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'standard_reduction_id' => 'Standard Reduction ID',
            'standard_id' => 'Standard ID',
            'reduction_percentage' => 'Reduction Percentage',
        ];
    }
	
	public function getStandard()
    {
        return $this->hasOne(ReductionStandard::className(), ['id' => 'standard_id']);
    }
}
