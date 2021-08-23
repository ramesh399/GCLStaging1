<?php

namespace app\modules\master\models;

use Yii;


class Brand extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_brands';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['number'], 'string'],
            [['version'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id'=>'User Id',
            'brand_group_id'=>'Brand Group Id',
            'name' => 'Brand Name',
            'number' => 'Number',
            'version' => 'Version',
        ];
    }

    public function getBrandgroup()
    {
        return $this->hasOne(BrandGroup::className(),['id'=>'brand_group_id']);
    }
}
