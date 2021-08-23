<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tbl_user_role_business_group_code".
 *
 * @property int $id
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class UserRoleBusinessGroupCode extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_role_business_group_code';
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
            //[['status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status'
        ];
    }
    
    public function getSectorgroup()
    {
        return $this->hasOne(BusinessSectorGroup::className(), ['id' => 'business_sector_group_id']);
    }
    
    public function getBusinesssector()
    {
        return $this->hasOne(BusinessSector::className(), ['id' => 'business_group_id']);
    }

    public function getRolebusinessgroup()
    {
        return $this->hasOne(UserRoleBusinessGroup::className(), ['id' => 'business_group_id']);
    }
}
