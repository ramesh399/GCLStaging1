<?php

namespace app\modules\certificate\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;

/**
 * This is the model class for table "tbl_audit_reviewer_review".
 *
 * @property int $id
 * @property int $certificate_id
 * @property int $user_id
 * @property string $comment
 * @property string $extension_date
 * @property int $status  0=Suspension,1=Cancellation,2=Withdrawn,3=Extension,4=Certificate Reinstate 
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class CertificateStatusReview extends \yii\db\ActiveRecord
{
    public $arrStatus=array('0'=>'Suspension','1'=>'Cancellation','2'=>'Withdrawn','3'=>'Extension','4'=>'Certificate Reinstate','5'=>'Éxpired');
    public $arrEnumStatus=array('suspension'=>'0','cancellation'=>'1','withdrawn'=>'2','extension'=>'3','certificate_reinstate'=>'4','expired'=>'5'); 
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_certificate_status_review';
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
            [['certificate_id', 'user_id', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['comment'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'certificate_id' => 'Certificate ID',
            'user_id' => 'User ID',
            'comment' => 'Comment',
            'extension_date' => 'Extension Date',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getCreatedby()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

}
