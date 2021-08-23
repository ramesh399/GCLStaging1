<?php

namespace app\modules\library\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
use app\modules\master\models\Country;
/**
 * This is the model class for table "tbl_library_legislation".
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
class LibraryLegislation extends \yii\db\ActiveRecord
{

    public $arrRelevant=array('1'=>'GRS','2'=>'RCS','3'=>"OCS",'4'=>'GOTS','5'=>'FSC','6'=>'CCS');
    public $arrEnumRelevant=array('grs'=>'1','rcs'=>'2',"ocs"=>'3','gots'=>'4','fsc'=>'5','ccs'=>'6');

    public $arrMethod=array('1'=>'Subscription','2'=>'Internet Search');
	public $arrEnumMethod=array('subscription'=>'1','internet_search'=>'2');
    public $first_name='';
    public $last_name = '';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_legislation';
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
            [['title', 'description'], 'string'],
            [['country_id', 'relevant_to_id','update_method_id','status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
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
    
    public function getLibrarylegislationlog()
    {
        return $this->hasMany(LibraryLegislationLog::className(), ['library_legislation_id' => 'id']);
    }
     public function getLegislationstandard()
    {
        return $this->hasMany(LibraryLegislationStandard::className(), ['library_legislation_id' => 'id']);
    }
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }

    public function getCreateduser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
	

}
