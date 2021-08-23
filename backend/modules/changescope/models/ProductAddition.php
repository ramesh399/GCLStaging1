<?php
namespace app\modules\changescope\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use app\modules\application\models\Application;
use app\modules\master\models\User;
use app\modules\application\models\ApplicationChangeAddress;
use app\modules\certificate\models\Certificate;


/**
 * This is the model class for table "tbl_cs_product_addition".
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
class ProductAddition extends \yii\db\ActiveRecord
{
	public $arrStatus=array('0'=>'Open','1'=>'Pending with Customer','2'=>'Waiting for OSP Review','3'=>'Pending with OSP','4'=>"Waiting for Review",'5'=>'Review in Process','6'=>'Approved','7'=>'Rejected','8'=>'Certification In-Process','9'=>'Certification Generated');
    public $arrEnumStatus=array('open'=>'0','pending_with_customer'=>'1','waiting_for_osp_review'=>'2',"pending_with_osp"=>'3','waiting_for_review'=>'4','review_in_process'=>'5','approved'=>'6','rejected'=>'7','certification_in_process'=>'8','certification_generated'=>'9');
	
	// public $arrStatus=array('0'=>'Open','1'=>'Submitted','2'=>'Waiting for Review','3'=>"Review in Process",'4'=>'Waiting for Approval','5'=>'Approval in Process','6'=>'Approved','7'=>'Pending with Customer','8'=>'Failed','9'=>'Re-Initiate for Review','10'=>'Rejected');
	// public $arrEnumStatus=array('open'=>'0','submitted'=>'1','waiting_for_review'=>'2',"review_in_process"=>'3','review_completed'=>'4','approval_in_process'=>'5','approved'=>'6','pending_with_customer'=>'7','failed'=>'8','re-initiate_for_review'=>'9','osp_reject'=> '10');
	
	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_product_addition';
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
            [['status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
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
	
	public function getAdditionunit()
    {
        return $this->hasMany(ProductAdditionUnit::className(), ['product_addition_id' => 'id']);
    }

    public function getFranchisecmt()
    {
        return $this->hasMany(ProductAdditionFranchiseComment::className(), ['product_addition_id' => 'id']);
    }

    public function getReviewercmt()
    {
        return $this->hasMany(ProductAdditionReviewerComment::className(), ['product_addition_id' => 'id']);
    }
   
	public function getApplication()
    {
        return $this->hasOne(Application::className(), ['id' => 'app_id']);
    }

    public function getAdditionproduct()
    {
        return $this->hasMany(ProductAdditionProduct::className(), ['product_addition_id' => 'id']);
    }

    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
    
    public function getReviewer()
    {
        return $this->hasOne(ProductAdditionReviewer::className(), ['product_addition_id' => 'id'])->andOnCondition(['reviewer_status' => 1]);
    }
	
	public function getApplicationaddress()
    {
        return $this->hasOne(ApplicationChangeAddress::className(), ['id' => 'address_id']);
	}
	
	public function getCertificate()
    {
        return $this->hasOne(Certificate::className(), ['product_addition_id' => 'id']);
    }
    
    public function getUsername()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

}
