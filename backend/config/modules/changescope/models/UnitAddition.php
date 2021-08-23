<?php
namespace app\modules\changescope\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use app\modules\application\models\Application;
use app\modules\master\models\User;
use app\modules\application\models\ApplicationChangeAddress;

/**
 * This is the model class for table "tbl_cs_unit_addition".
 *
 * @property int $id
 * @property int $app_id
 * @property int $new_app_id
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class UnitAddition extends \yii\db\ActiveRecord
{
	public $arrStatus=array('0'=>'Open','1'=>'Submitted','2'=>'Waiting for Review','3'=>"Review in Process",'4'=>'Waiting for Approval','5'=>'Approval in Process','6'=>'Approved','7'=>'Pending with Customer','8'=>'Failed','9'=>'Re-Initiate for Review','10'=>'Rejected');
	public $arrEnumStatus=array('open'=>'0','submitted'=>'1','waiting_for_review'=>'2',"review_in_process"=>'3','review_completed'=>'4','approval_in_process'=>'5','approved'=>'6','pending_with_customer'=>'7','failed'=>'8','re-initiate_for_review'=>'9','osp_reject'=> '10');
	
	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_unit_addition';
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
            [['app_id', 'new_app_id', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
	
	
	public function getApplication()
    {
        return $this->hasOne(Application::className(), ['id' => 'app_id']);
    }
    
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
	
	public function getAdditionunit()
    {
        return $this->hasMany(UnitAdditionUnit::className(), ['unit_addition_id' => 'id']);
    }
	
	public function getApplicationaddress()
    {
        return $this->hasOne(ApplicationChangeAddress::className(), ['id' => 'address_id']);
	}
}
