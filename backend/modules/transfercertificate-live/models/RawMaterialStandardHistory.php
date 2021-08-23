<?php

namespace app\modules\transfercertificate\models;

use Yii;
use app\modules\master\models\Standard;
/**
 * This is the model class for table "tbl_tc_raw_material_standard_history".
 *
 * @property int $id
 * @property string $raw_material_history_id
 * @property int $standard_id
 */
class RawMaterialStandardHistory extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_raw_material_standard_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['raw_material_history_id', 'standard_id'], 'integer'],
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
            'standard_id' => 'Standard Id',
        ];
    }	
    
    public function getStandard()
    {
        return $this->hasOne(TcStandard::className(), ['id' => 'standard_id']);
    }
}
