<?php

namespace app\modules\master\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_user_business_group_code_history".
 *
 * @property int $id
 * @property int $user_id
 * @property int $standard_id
 * @property int $business_sector_id
 * @property int $academic_qualification_status 1=Yes,2=No
 * @property string $exam_file
 * @property string $technical_interview_file
 * @property int $created_by
 * @property int $created_at
 */
class UserBusinessGroupCodeHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_business_group_code_history';
    }

    public function behaviors()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'standard_id', 'business_sector_id', 'academic_qualification_status', 'created_by', 'created_at'], 'integer'],
            [['exam_file', 'technical_interview_file'], 'string', 'max' => 255],
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
            'standard_id' => 'Standard ID',
            'business_sector_id' => 'Business Sector ID',
            'academic_qualification_status' => 'Academic Qualification Status',
            'exam_file' => 'Exam File',
            'technical_interview_file' => 'Technical Interview File',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
        ];
    }

    public function getGroupcode()
    {
        return $this->hasMany(UserBusinessGroupCode::className(), ['business_group_id' => 'id']);
    }
    public function getUserbusinessgroupcode()
    {
        return $this->hasOne(UserBusinessGroupCode::className(), ['id' => 'user_business_group_code_id']);
    }
    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
    public function getBusinesssector()
    {
        return $this->hasOne(BusinessSector::className(), ['id' => 'business_sector_id']);
    }
    public function getApprovaluser()
    {
        return $this->hasOne(User::className(), ['id' => 'status_change_by']);
    }
}
