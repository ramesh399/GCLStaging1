<?php

namespace app\modules\master\models;

use Yii;
/**
 * This is the model class for table "tbl_standard_combination_standard".
 *
 * @property int $id
 * @property int $standard_combination_id
 * @property int $standard_id
 */
class StandardCombinationStandard extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_standard_combination_standard';
    }

   
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['standard_combination_id', 'standard_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'standard_combination_id' => 'Standard Commination ID',
            'standard_id' => 'Standard ID',
        ];
    }
    
    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }

}
