<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_user_business_sector_group".
 *
 * @property int $id
 * @property int $user_id
 * @property int $business_sector_group_id
 */
class UserBusinessSectorGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_business_sector_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'business_sector_group_id'], 'integer'],
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
            'business_sector_group_id' => 'Business Sector Group ID',
        ];
    }


    public function getBusinesssectorgroup()
    {
        return $this->hasOne(BusinessSectorGroup::className(), ['id' => 'business_sector_group_id']);
    }
    
}
