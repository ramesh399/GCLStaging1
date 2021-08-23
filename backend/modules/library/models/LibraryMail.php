<?php

namespace app\modules\library\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\Standard;
use app\modules\master\models\Signature;
/**
 * This is the model class for table "tbl_library_mail".
 *
 * @property int $id
 * @property string $subject
 * @property string $body_content
 * @property int $signature_id
 * @property string $sent_date
 * @property int $partners
 * @property int $auditors
 * @property int $clients
 * @property int $consultants
 * @property int $subscribers
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class LibraryMail extends \yii\db\ActiveRecord
{
    public $arrSignature=array('1'=>'Mahmut Sogukpinar','2'=>'Gary Jones','3'=>'Jason Wong','4'=>'Rajesh Selva');
    public $arrStatus=array('1'=>'Sent','2'=>'Not Sent');
    public $arrPartners=array('1'=>'All','2'=>'None');
    public $arrAuditors=array('1'=>'All','2'=>'None');
    public $arrConsultants=array('1'=>'Yes','2'=>'No');
    public $arrSubscribers=array('1'=>'Yes','2'=>'No');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_mail';
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
            [['subject', 'body_content'], 'string'],
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
            'subject' => 'Subject',
            'body_content' => 'Body Content',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
    
    
    // public function getStandard()
    // {
    //     return $this->hasOne(Standard::className(), ['id' => 'clients']);
    // }
    public function getLibrarymailstandard()
    {
        return $this->hasMany(LibraryMailStandard::className(), ['library_mail_id' => 'id']);
    }

    public function getSignature()
    {
        return $this->hasOne(Signature::className(), ['id' => 'signature_id']);
    }

}
