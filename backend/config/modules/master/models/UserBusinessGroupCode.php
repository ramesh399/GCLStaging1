<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_user_business_group_code".
 *
 * @property int $id
 * @property int $business_group_id
 * @property int $business_sector_group_id
 */
class UserBusinessGroupCode extends \yii\db\ActiveRecord
{

    public $arrStatus=array('0'=>'Open','1'=>'Waiting for Approval','2'=>'Approved','3'=>'Rejected');
    public $arrEnumStatus=array('open'=>'0','waiting_for_approval'=>'1','approved'=>'2','rejected'=>'3');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_business_group_code';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['business_group_id', 'business_sector_group_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'business_group_id' => 'Business Group ID',
            'business_sector_group_id' => 'Business Sector Group ID',
        ];
    }
    public function getSectorgroup()
    {
        return $this->hasOne(BusinessSectorGroup::className(), ['id' => 'business_sector_group_id']);
    }

    public function getUsersector()
    {
        return $this->hasOne(UserBusinessGroup::className(), ['id' => 'business_group_id']);
    }
}
