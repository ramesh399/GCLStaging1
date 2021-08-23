<?php

namespace app\modules\transfercertificate\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_tc_raw_material_label_grade".
 *
 * @property int $id
 * @property int $tc_raw_material_standard_id
 * @property string $name
 * @property string $code
 * @property string $description
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class TcStandardLabelGrade extends \yii\db\ActiveRecord
{

    public $arrStatus=array('0'=>'Approved','1'=>'Archived');
    public $enumStatus=array('approved'=>'0','archived'=>'1');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_standard_label_grade';
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
            [['status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255],		
			['name', 'unique', 'targetAttribute' => ['name'],'filter' => ['!=','status' ,1]],
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
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }	
	
	public function getStandard()
    {
        return $this->hasOne(TcStandard::className(), ['id' => 'tc_standard_id']);
    }
	
	public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
	
}
