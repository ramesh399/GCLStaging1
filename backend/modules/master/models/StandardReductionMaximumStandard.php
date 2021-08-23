<?php

namespace app\modules\master\models;

use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_standard_reduction_maximun_standard".
 *
 * @property int $id
 * @property int $standard_reduction_maximum_id
 * @property int $standard_id
 */
class StandardReductionMaximumStandard extends \yii\db\ActiveRecord
{
   /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_standard_reduction_maximun_standard';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['standard_reduction_maximum_id', 'standard_id'], 'integer']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'standard_id' => 'Standard',
        ];
    }

    public function getReductionstandard()
    {
        return $this->hasOne(ReductionStandard::className(), ['id' => 'standard_id']);
    }
}
