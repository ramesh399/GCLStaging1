<?php

namespace app\modules\library\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_library_audit_report".
 *
 * @property int $id
 * @property int $franchise_id
 * @property int $report_date
 * @property string $description
 * @property int $reviewer
 * @property int $access_id
 * @property string $source_file
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class LibraryAuditReport extends \yii\db\ActiveRecord
{
    public $arrReviewer=array('1'=>'Mahmut Sogukpinar','2'=>'Gary Jones','3'=>'Jason Wong','4'=>'Rajesh Selva');
    public $arrAccess=array('1'=>'Available to All','2'=>'Available to GCL Only');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_audit_report';
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
            [['description', 'source_file'], 'string'],
            [['franchise_id', 'reviewer','access_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'franchise_id' => 'Franchise ID',
            'report_date' => 'Report Date',
            'description' => 'Description',
            'reviewer' => 'Reviewer',
            'access_id' => 'Access ID',
            'source_file' => 'Source File',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
    
    public function getFranchise()
    {
        return $this->hasOne(User::className(), ['id' => 'franchise_id']);
    }
	
    public function getReviewerdata()
    {
        return $this->hasOne(User::className(), ['id' => 'reviewer']);
    }
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
