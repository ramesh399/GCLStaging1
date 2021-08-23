<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_user_business_sector".
 *
 * @property int $id
 * @property int $user_id
 * @property int $business_sector_id
 */
class UserBusinessSector extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_business_sector';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'business_sector_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'business_sector_id' => 'Business Sector ID',
        ];
    }

    public function getBusinesssector()
    {
        return $this->hasOne(BusinessSector::className(), ['id' => 'business_sector_id']);
    }
}
