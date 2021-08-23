<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tbl_reduction_standard".
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $type
 * @property string $description
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class ReductionStandard extends \yii\db\ActiveRecord
{
    public $StandardType=array('1'=>'Primary','2'=>'Social','3'=>'Environment');
    public $RequiredFields=array('1'=>'License Number','2'=>'Expiry Date','3'=>'Certificate File','4'=>'Latest Audit Report');
	
	public $arrRequiredFields=array('1'=>'license_number','2'=>'expiry_date','3'=>'certificate_file','4'=>'latest_audit_report');
	public $arrEnumRequiredFields=array('license_number'=>'1','expiry_date'=>'2','certificate_file'=>'3','latest_audit_report'=>'4');
	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_reduction_standard';
    }

    /**
     * {@inheritdoc}
     */
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

    public function rules()
    {
        return [
            [['type', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['description'], 'string'],
            [['name', 'code'], 'string', 'max' => 255],
			[['short_code'], 'string', 'max' => 5],
            ['name', 'unique','filter' => ['!=','status' ,2]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'code' => 'Code',
            'type' => 'Type',
            'description' => 'Description',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public function getRequiredfields()
    {
        return $this->hasMany(ReductionStandardRequiredFields::className(), ['reduction_standard_id' => 'id']);
    }
}
