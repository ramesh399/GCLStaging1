<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_client_logo_question_hq".
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $interpretation
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class ClientLogoQuestionHq extends \yii\db\ActiveRecord
{
    public $arrFindings = ['1'=>'Critical','2'=>'High','3'=>'Medium','4'=>'Low'];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_client_logo_question_hq';
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
            [['name', 'interpretation'], 'string'],
            [['status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['code'], 'string', 'max' => 255],
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
            'interpretation' => 'interpretation',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public function getFinding()
    {
        return $this->hasMany(ClientLogoQuestionHqFindings::className(), ['client_logo_checklist_hq_question_id' => 'id']);
    }

    public function getQuestionstandard()
    {
        return $this->hasMany(ClientLogoQuestionHqStandard::className(), ['client_logo_checklist_hq_question_id' => 'id']);
    }
}
