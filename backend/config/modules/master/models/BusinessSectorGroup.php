<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_business_sector_group".
 *
 * @property int $id
 * @property string $group_code
 * @property string $group_details
 * @property int $business_sector_id
 * @property int $standard_id
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class BusinessSectorGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_business_sector_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_details'], 'string'],
            [['business_sector_id', 'standard_id', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['group_code'], 'string', 'max' => 255],
            ['group_code', 'unique', 'targetAttribute' => ['group_code', 'business_sector_id','standard_id'],'filter' => ['!=','status' ,2],'message' => 'The combination of "Group Code", "Standard" and "Business Sector" has already been taken.'],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at','updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_code' => 'Group Code',
            'group_details' => 'Group Details',
            'business_sector_id' => 'Business Sector ID',
            'standard_id' => 'Standard ID',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }

    public function getBusinesssector()
    {
        return $this->hasOne(BusinessSector::className(), ['id' => 'business_sector_id']);
    }

    public function getBusinesssectorgroupprocess()
    {
        return $this->hasMany(BusinessSectorGroupProcess::className(), ['business_sector_group_id' => 'id']);
    }

    public function getGroupname()
    {
        return $this->group_code;
    }
}
