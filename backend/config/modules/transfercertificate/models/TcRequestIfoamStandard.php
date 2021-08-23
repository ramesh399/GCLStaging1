<?php

namespace app\modules\transfercertificate\models;

use Yii;
use app\modules\master\models\Standard;
/**
 * This is the model class for table "tbl_tc_request_ifoam_standard".
 *
 * @property int $id
 * @property string $tc_request_id
 * @property int $ifoam_standard_id
 */
class TcRequestIfoamStandard extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_request_ifoam_standard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tc_request_id', 'ifoam_standard_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tc_request_id' => 'Name',          				
            'ifoam_standard_id' => 'Standard Id',
        ];
    }	
    
    public function getIfoamStd()
    {
        return $this->hasOne(TcIfoamStandard::className(), ['id' => 'ifoam_standard_id']);
    }
}
