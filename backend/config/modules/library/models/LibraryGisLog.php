<?php

namespace app\modules\library\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_library_gis_log".
 *
 * @property int $id
 * @property string $question
 * @property string $answer
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class LibraryGisLog extends \yii\db\ActiveRecord
{
    //public $arrType=array('1'=>'COMPLAINT','2'=>'APPEAL','3'=>"AB NCN",'4'=>'OSP VISIT','5'=>'INTERNAL','6'=>'OTHER');
    //public $arrEnumType=array('complaint'=>'1','appeal'=>'2',"ab ncn"=>'3','osp visit'=>'4','internal'=>'5','other'=>'6');
	
	public $arrType=array('1'=>'GENERAL UPDATE','2'=>'DOCUMENTATION','3'=>"INVESTIGATION",'4'=>'CAP','5'=>'CLOSURE','6'=>'OTHER');
    public $arrEnumType=array('general update'=>'1','documentation'=>'2',"investigation"=>'3','cap'=>'4','closure'=>'5','other'=>'6');
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_gis_log';
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
            [['description'], 'string'],
            [['type','library_gis_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'question' => 'Question',
            'answer' => 'Answer',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
    
    

}
