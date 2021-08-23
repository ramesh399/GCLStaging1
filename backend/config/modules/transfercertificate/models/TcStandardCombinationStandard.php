<?php

namespace app\modules\transfercertificate\models;

use Yii;

use app\modules\master\models\User;
use app\modules\master\models\Role;
/**
 * This is the model class for table "tbl_tc_standard_combination_standard".
 *
 * @property int $id
 * @property int $tc_standard_combination_id
 * @property int $tc_standard_id
 */
class TcStandardCombinationStandard extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_standard_combination_standard';
    }

   
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tc_standard_combination_id', 'tc_standard_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tc_standard_combination_id' => 'Standard Commination ID',
            'tc_standard_id' => 'Standard ID',
        ];
    }
    
    public function getStandard()
    {
        return $this->hasOne(TcStandard::className(), ['id' => 'tc_standard_id']);
    }

}
