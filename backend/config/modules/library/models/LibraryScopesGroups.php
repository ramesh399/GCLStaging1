<?php

namespace app\modules\library\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use app\modules\master\models\BusinessSector;
use app\modules\master\models\BusinessSectorGroup;
use app\modules\master\models\Standard;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_library_scopes_groups".
 *
 * @property int $id
 * @property string $risk
 * @property string $description
 * @property string $process
 * @property string $controls
 * @property int $standard_id
 * @property int $business_group_id
 * @property int $business_group_code_id
 * @property int $scope
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class LibraryScopesGroups extends \yii\db\ActiveRecord
{
    public $arrStatus=array('1'=>'Active','2'=>'Inactive');
	public $enumStatus=array('active'=>'1','inactive'=>'2');
    public $arrAccreditation=array('1'=>'IOAS','2'=>'NONE');
    public $arrScope=array('1'=>'SCOPE 1','2'=>'SCOPE 2','3'=>'SCOPE 3','4'=>'SCOPE 4','5'=>'N/A');
	public $arrRisk=array('1'=>'Low','2'=>'Medium','3'=>'High');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_scopes_groups';
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
    public function rules()
    {
        return [
            [['description','process','controls'], 'string'],
            [[ 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'risk' => 'Risk',
            'description' => 'Description',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
    
    public function getLibraryfaqaccess()
    {
        return $this->hasMany(LibraryFaqAccess::className(), ['library_faq_id' => 'id']);
    }
	
    public function getBusinesssector()
    {
        //return $this->hasOne(AuditPlanReviewer::className(), ['audit_plan_id' => 'id'],['reviewer_status'=>'1']);
        return $this->hasOne(BusinessSector::className(), ['id' => 'business_group_id']);
    }
    public function getBusinesssectorgroup()
    {
        //return $this->hasOne(AuditPlanReviewer::className(), ['audit_plan_id' => 'id'],['reviewer_status'=>'1']);
        return $this->hasOne(BusinessSectorGroup::className(), ['id' => 'business_group_code_id']);
    }
    public function getStandard()
    {
        //return $this->hasOne(AuditPlanReviewer::className(), ['audit_plan_id' => 'id'],['reviewer_status'=>'1']);
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
