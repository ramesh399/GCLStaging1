<?php

namespace app\modules\master\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tbl_reduction_standard_required_fields".
 *
 * @property int $id
 * @property int $reduction_standard_id	
 * @property int $required_field

 */
class ReductionStandardRequiredFields extends \yii\db\ActiveRecord
{
	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_reduction_standard_required_fields';
    }

    

    public function rules()
    {
        return [
            [['reduction_standard_id', 'required_field'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reduction_standard_id' => 'reduction_standard_id',
            'required_field' => 'required_field',
        ];
    }
}
