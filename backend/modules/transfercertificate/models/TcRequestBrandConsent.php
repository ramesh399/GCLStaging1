<?php

namespace app\modules\transfercertificate\models;
use app\modules\master\models\Brand;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;



class TcRequestBrandConsent extends \yii\db\ActiveRecord
{
	 
    public static function tableName()
    {
        return 'tbl_tc_request_brand_consent';
    }

   
    public function rules()
    {
        return [
            [['app_id'], 'required']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App Id',
            'unit_id' => 'Unit Id',
			'tc_request_id' => 'TC Request Id',
			'brand_id' => 'Brand Id',					
            
        ];
    }	

    public function getbrand()
    {
        return $this->hasOne(Brand::className(), ['id' => 'brand_id']);
    }
}