<?php

namespace app\modules\application\models;



use Yii;

use app\modules\master\models\Brand;




class ApplicationBrands extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_brands';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_id', 'brand_id'], 'integer'],
            
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App ID',
            'brand_id' => 'Brand ID',
            'status' => 'Brand Status',
            'comment' => 'Approver Comment'
        ];
    }

    public function getApplication()
    {
        return $this->hasOne(Application::className(),['id'=>'app_id']);
    }

    public function getBrands()
    {
        return $this->hasOne(Brand::className(),['id'=>'brand_id']);        
    }


    
}