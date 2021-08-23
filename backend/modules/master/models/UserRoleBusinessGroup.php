<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tbl_user_role_business_group".
 *
 * @property int $id
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class UserRoleBusinessGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_role_business_group';
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
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
        ];
    }
    /*
    public function getBusinesssector()
    {
        return $this->hasOne(BusinessSector::className(), ['id' => 'business_sector_id']);
    }*/

    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }

    public function getRole()
    {
        return $this->hasOne(Role::className(), ['id' => 'role_id']);
    }

    public function getBusinesssector()
    {
        return $this->hasOne(BusinessSector::className(), ['id' => 'business_sector_id']);
    }
    public function getRolegroupcode()
    {
        return $this->hasMany(UserRoleBusinessGroupCode::className(), ['business_group_id' => 'id']);
    }
}
