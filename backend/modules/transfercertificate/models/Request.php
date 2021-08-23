<?php

namespace app\modules\transfercertificate\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationChangeAddress;

use app\modules\master\models\User;
use app\modules\master\models\Country;
/**
 * This is the model class for table "tbl_tc_request".
 *
 * @property int $id
 * @property int $app_id
 * @property int $unit_id
 * @property int $buyer_id
 * @property int $consignee_id
 * @property int $standard_id
 * @property string $purchase_order_number
 * @property string $comments
 * @property int $transport_id 
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class Request extends \yii\db\ActiveRecord
{
	public $arrStatus=array('0'=>'Open','1'=>'Draft','2'=>'Pending with Customer','3'=>'Waiting for OSS Review','4'=>'Pending with OSS','5'=>"Waiting for Review",'6'=>'Review in Process','7'=>'Approved','8'=>'Rejected');
    public $arrEnumStatus=array('open'=>'0','draft'=>'1','pending_with_customer'=>'2','waiting_for_osp_review'=>'3',"pending_with_osp"=>'4','waiting_for_review'=>'5','review_in_process'=>'6','approved'=>'7','rejected'=>'8');

    public $arrStatusColor=array('0'=>'#4572A7','1'=>"#DB843D",'2'=>'#3D96AE','3'=>'#4eba8f','4'=>'#bf5aed','5'=>'#f15c80','6'=>'#89a54e','7'=>'#A47D7C','8'=>'#ff0000');
	
	public $arrOverallInputStatus=array('0'=>'Open','1'=>'Input Added');
    public $arrEnumOverallInputStatus=array('open'=>'0','input_added'=>'1');

    public $arrInvoiceOptions=array('1'=>'Free','2'=>'Cancel','3'=>'To bill');
    public $arrEnumInvoiceOptions=array('free'=>'1','cancel'=>'2','to_bill'=>'3');
	
	public $arrInvoiceOptionsLabel=array('1'=>'Free','2'=>'Cancelled','3'=>'Bill Generated');
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_request';
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
            [['app_id'], 'required'],
			[['tc_number','tc_number_cds'], 'safe'],
            
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
            'client_number' => 'Client Number',
			'address' => 'Address',
			'city' => 'City',					
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }	
    
    public function getBuyer()
    {
        return $this->hasOne(Buyer::className(), ['id' => 'buyer_id']);
    }

    public function getSeller()
    {
        return $this->hasOne(Buyer::className(), ['id' => 'seller_id']);
    }

    

    public function getInspectionbody()
    {
        return $this->hasOne(InspectionBody::className(), ['id' => 'inspection_body_id']);
    }

    public function getCertificationbody()
    {
        return $this->hasOne(InspectionBody::className(), ['id' => 'certification_body_id']);
    }

    public function getTransport()
    {
        return $this->hasOne(Transport::className(), ['id' => 'transport_id']);
    }

    public function getApplication()
    {
        return $this->hasOne(Application::className(), ['id' => 'app_id']);
    }

    public function getApplicationunit()
    {
        return $this->hasOne(ApplicationUnit::className(), ['id' => 'unit_id']);
    }

    public function getApplicationstandard()
    {
        return $this->hasOne(ApplicationStandard::className(), ['id' => 'standard_id']);
    }
    public function getProduct()
    {
        return $this->hasMany(RequestProduct::className(), ['tc_request_id' => 'id']);
    }
	
	public function getStandard()
    {
        return $this->hasMany(RequestStandard::className(), ['tc_request_id' => 'id']);
    }

    public function getIfoamstandard()
    {
        return $this->hasMany(TcRequestIfoamStandard::className(), ['tc_request_id' => 'id']);
    }

    public function getFranchisecmt()
    {
        return $this->hasMany(RequestFranchiseComment::className(), ['tc_request_id' => 'id']);
    }

    public function getReviewercmt()
    {
        return $this->hasMany(RequestReviewerComment::className(), ['tc_request_id' => 'id']);
    }
	
	public function getCurrentreviewercmt()
    {
        return $this->hasOne(RequestReviewerComment::className(), ['tc_request_id' => 'id'])->andOnCondition(['status' => 1])->orderBy(['id' => SORT_DESC]);
	}

    public function getReviewer()
    {
        return $this->hasOne(RequestReviewer::className(), ['tc_request_id' => 'id'])->andOnCondition(['reviewer_status' => 1]);
    }

    public function getEvidence()
    {
        return $this->hasMany(RequestEvidence::className(), ['tc_request_id' => 'id']);
    }

    public function getDispatchcountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_of_dispach']);
    }
	
	public function getApplicationaddress()
    {
        return $this->hasOne(ApplicationChangeAddress::className(), ['id' => 'address_id']);
	}
	
	/*
	public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
	*/
	
	public function getUsername()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getProductgroup()
    {
        return $this->hasMany(RequestProduct::className(), ['tc_request_id' => 'id'])->groupBy(['invoice_no']);
    }

    public function getTcbrandconsent()
	{
        return $this->hasOne(TcRequestBrandConsent::className(),['tc_request_id'=>'id']);
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_of_dispach']);
    }
	/*
	public function generateInvoice()
	{
		$appObj = $this->application;
		$applicationID = $appObj->id;
		$CustomerID = $appObj->customer_id;
		$ospid = $appObj->franchise_id;	
		$user_type=0;
		
		$model=new Invoice();
		$model->app_id=$applicationID;
		$model->customer_id=$CustomerID;
		$model->franchise_id=$ospid;
		$model->invoice_type=1;
					
		$invoiceCount = 0;
		$connection = Yii::$app->getDb();			
			
		$command = $connection->createCommand("SELECT COUNT(invoice.id) AS invoice_count FROM `tbl_invoice` AS invoice where invoice.franchise_id='".$ospid."'");
		$result = $command->queryOne();
		if($result  !== false)
		{
			$invoiceCount = $result['invoice_count'];
		}

		$maxid = $invoiceCount+1;
		if(strlen($maxid)=='1')
		{
			$maxid = "0".$maxid;
		}
		
		$invoicecode = "SY-".$franchiseObj->usercompanyinfo->osp_number."-".$maxid."/".date("Y");
		$model->invoice_number=$invoicecode;		
		$model->currency_code = $franchiseObj->usercompanyinfo->mandaycost->currency_code;
		
		//$model->app_id=$data['app_id'];
		
		//$model->offer_id=$data['offer_id'];
		$model->discount=$data['discount'];
		
		//$model->certification_fee_sub_total=$data['certification_fee_sub_total'];
		//$model->other_expense_sub_total=$data['other_expense_sub_total'];
		$model->total_fee=$data['total_fee'];
		$model->grand_total_fee=$data['grand_total_fee'];
		$model->tax_amount=$data['tax_amount'];
		$model->total_payable_amount=$data['total_payable_amount'];
		//$model->conversion_total_payable=$data['conversion_total_payable'];
			
			
		$model->status=$model->enumStatus['in-progress'];
		$userData = Yii::$app->userdata->getData();
		$model->created_by=$userData['userid'];
		if($model->validate() && $model->save())
		{	$invoiceID = $model->id;			
			InvoiceDetails::deleteAll(['invoice_id' => $model->id,'entry_type'=>1]);
			if(is_array($data['other_expenses']) && count($data['other_expenses'])>0)
			{
				foreach ($data['other_expenses'] as $value)
				{ 
					if($value['entry_type']=='new')
					{
						$otherExpenses=new InvoiceDetails();
						$otherExpenses->invoice_id=$invoiceID;
						$otherExpenses->activity=$value['activity'];
						$otherExpenses->description=$value['description'];	
						$otherExpenses->amount=$value['amount'];
						$otherExpenses->entry_type = 1;													
						$otherExpenses->save();
					}	
				}
			}
			
			if($invoiceType==3 || $invoiceType==4)
			{
				InvoiceTax::deleteAll(['invoice_id' => $model->id]);
				
				$grandTotalFee = $model->grand_total_fee;
				$invoicetax = $franchiseObj->usercompanyinfo->mandaycost->mandaycosttax;
				if(count($invoicetax)>0)
				{
					$taxnameArray=array();
					$taxpercentage=0;
					foreach($invoicetax as $invoiceT)
					{
						$TaxAmount=0;
						$TaxAmount=($grandTotalFee*$invoiceT->tax_percentage/100);
													
						$invoiceListTax=new InvoiceTax();
						$invoiceListTax->invoice_id=$invoiceID;							
						$invoiceListTax->tax_name=$invoiceT->tax_name;	
						$invoiceListTax->tax_percentage=$invoiceT->tax_percentage;
						$invoiceListTax->amount=$TaxAmount;							
						$invoiceListTax->save();
						
					}							
				}
			}				

			$responsedata=array('status'=>1,'message'=>'Invoice has been created successfully','id'=>$model->id);
		}
	}
	*/
}
