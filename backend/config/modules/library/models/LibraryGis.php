<?php

namespace app\modules\library\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_library_gis".
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
class LibraryGis extends \yii\db\ActiveRecord
{
	public $arrType=array('1'=>'COMPLAINT','2'=>'APPEAL','3'=>"AB NCN",'4'=>'OSP VISIT','5'=>'INTERNAL','6'=>'OTHER');
    public $arrEnumType=array('complaint'=>'1','appeal'=>'2',"ab ncn"=>'3','osp visit'=>'4','internal'=>'5','other'=>'6');
    
    public $arrStatus=array('1'=>'OPEN','2'=>'CLOSED');
	public $arrEnumStatus=array('open'=>'1','closed'=>'2');

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_gis';
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
            [['title', 'description','gis_file'], 'string'],
            [['type','status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
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
    
    public function getLibrarygislog()
    {
        return $this->hasMany(LibraryGisLog::className(), ['library_gis_id' => 'id']);
    }
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
