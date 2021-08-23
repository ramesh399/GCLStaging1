<?php

namespace app\modules\transfercertificate\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use app\modules\transfercertificate\models\RawMaterial;

/**
 * This is the model class for table "tbl_tc_raw_material_file_history".
 *
 * @property int $id
 * @property int $tc_raw_material_id
 * @property int $raw_material_file
 * @property ENUM $raw_material_file_type
 * @property ENUM $entry_type 
 */
class RawMaterialFileHistory extends \yii\db\ActiveRecord
{
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_raw_material_file_history';
    }

    public function behaviors()
    {
        return [];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tc_raw_material_id'], 'required'],
           
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
        ];
    }	
    

}
