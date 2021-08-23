<?php
namespace app\modules\invoice\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use app\modules\master\models\User;
use app\modules\audit\models\Audit;
use app\modules\application\models\Application;

use app\modules\offer\models\Offer;
use app\modules\offer\models\OfferList;
use app\modules\offer\models\OfferListCertificationFee;
use app\modules\offer\models\OfferComment;
use app\modules\offer\models\OfferListOtherExpenses;

/**
 * This is the model class for table "tbl_invoice".
 *
 * @property int $id
 * @property int $app_id
 * @property int $offer_id
 * @property string $discount
 * @property int $status 0=Open,1=In Process,2=Rejected,3=Finalized
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class Invoice extends \yii\db\ActiveRecord
{
    public $arrStatus=array('0'=>'Open','1'=>"In Process",'2'=>"Approval in Process",'3'=>'Rejected','4'=>'Payment Pending','5'=>"Payment Received",'6'=>"Payment Cancelled");
    public $enumStatus=array('open'=>'0','in-progress'=>"1",'approval_in_process'=>"2",'rejected'=>'3','payment_pending'=>'4','payment_received'=>'5','payment_cancelled'=>'6');
    public $arrStatusColor=array('0'=>'#4572A7','1'=>"#DB843D",'2'=>"#783906",'3'=>'#ff0000','4'=>'#89A54E','5'=>'#89A54E','6'=>'#ff0000');

    //public $paymentStatus=array('1'=>'Payment Pending','2'=>"Payment Received");
    //public $enumpaymentStatus=array('1'=>'Payment Pending','2'=>"Payment Completed");
	
	public $arrInvoiceType=array('1'=>'Initial Invoice to Client','2'=>'Initial Invoice to OSS','3'=>'Additional Invoice to Client','4'=>'Additional Invoice to OSS');
	public $enumInvoiceType=array('initial_invoice_to_client'=>'1','initial_invoice_to_oss'=>'2','additional_invoice_to_client'=>'3','additional_invoice_to_oss'=>'4');
	
	public $arrCreditNoteOptions=array('1'=>'Credit Note','2'=>'Debit Note');
	public $enumCreditNoteOptions=array('credit_note'=>'1','debit_note'=>'2');
	
    public $paid_amount,$unpaid_amount;
	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_invoice';
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
            [['app_id', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['discount'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App ID',
            'offer_id' => 'Offer ID',
            'discount' => 'Discount',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
	
	public function getUsername()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
	
	public function getRejectedby()
    {
        return $this->hasOne(User::className(), ['id' => 'rejected_by']);
    }

    public function getFranchise()
    {
        return $this->hasOne(User::className(), ['id' => 'franchise_id']);
    }
	
	public function getCustomer()
    {
        return $this->hasOne(User::className(), ['id' => 'customer_id']);
    }

    public function getPaymentupdatedby()
    {
        return $this->hasOne(User::className(), ['id' => 'payment_updated_by']);
    }
	
	public function getOffer()
    {
        return $this->hasOne(Offer::className(), ['id' => 'offer_id']);
    }
    public function getAudit()
    {
        return $this->hasOne(Audit::className(), ['invoice_id' => 'id']);
    }
    public function getApplication()
    {
        return $this->hasOne(Application::className(), ['id' => 'app_id']);
    }
	
	public function getInvoicedetails()
    {
        return $this->hasMany(InvoiceDetails::className(), ['invoice_id' => 'id']);
    }

    public function getInvoicetax()
    {
        return $this->hasMany(InvoiceTax::className(), ['invoice_id' => 'id']);
    }
    public function getInvoicestandard()
    {
        return $this->hasMany(InvoiceStandard::className(), ['invoice_id' => 'id']);
    }
	
	public function getHqfranchise()
    {
        return $this->hasOne(User::className(), ['id' => 'hq_franchise_id']);
    }
}
