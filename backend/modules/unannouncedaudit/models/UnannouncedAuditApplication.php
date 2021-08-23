<?php

namespace app\modules\unannouncedaudit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationChangeAddress;
use app\modules\audit\models\Audit;
/**
 * This is the model class for table "tbl_unannounced_audit_application".
 *
 * @property int $id
 * @property int $app_id
 * @property int $audit_id
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class UnannouncedAuditApplication extends \yii\db\ActiveRecord
{
    public $arrStatus=array('0'=>'Open','1'=>'Audit in Process','2'=>'Audit Completed');
    public $arrEnumStatus=array('open'=>'0','audit_plan_in_process'=>'1','audit_completed'=>'2');
    public $arrStatusColor=array('0'=>'#4572A7','1'=>"#DB843D");
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_unannounced_audit_application';
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
            [['audit_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            
        ];
    }

    public function getAudit()
    {
        return $this->hasOne(Audit::className(), ['id' => 'audit_id']);
    }

    public function getApplication()
    {
        return $this->hasOne(Application::className(), ['id' => 'app_id']);
    }
    
    public function getCurrentaddress()
    {
        return $this->hasOne(ApplicationChangeAddress::className(), ['parent_app_id' => 'app_id'])->orderBy(['id' => SORT_DESC]);
    }
    
    
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
    

    public function getUnannouncedauditstandard()
    {
        return $this->hasMany(UnannouncedAuditApplicationStandard::className(), ['unannounced_audit_app_id' => 'id']);
    }

    public function getUnannouncedauditunit()
    {
        return $this->hasMany(UnannouncedAuditApplicationUnit::className(), ['unannounced_audit_app_id' => 'id']);
    }
}
