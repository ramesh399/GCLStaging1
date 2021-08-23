<?php

namespace app\modules\transfercertificate\models;

use Yii;
//use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
use app\modules\master\models\Standard;
/**
 * This is the model class for table "tbl_tc_request_standard".
 *
 * @property int $id
 * @property int $tc_request_id
 * @property int $standard_id 
 */
class RequestStandard extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_request_standard';
    }

    public function behaviors()
    {
        return [            
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tc_request_id', 'standard_id'], 'integer'],
			//['name', 'unique','filter' => ['!=','status' ,2]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tc_request_id' => 'tc_request_id',          				
            'standard_id' => 'standard_id',            
        ];
    }	
    
    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
}
