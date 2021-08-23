<?php

namespace app\modules\offer\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use app\modules\application\models\Application;
use app\modules\master\models\Mandaycost;
use app\modules\master\models\Country;
use app\modules\master\models\State;
use app\modules\audit\models\Audit;
use app\modules\master\models\User;

use app\modules\invoice\models\Invoice;
use app\modules\invoice\models\InvoiceStandard;
use app\modules\invoice\models\InvoiceDetails;
use app\modules\invoice\models\InvoiceDetailsStandard;
use app\modules\invoice\models\InvoiceTax;

/**
 * This is the model class for table "tbl_offer".
 *
 * @property int $id
 * @property int $app_id
 * @property string $offer_code
 * @property string $standard
 * @property string $subcontractor_name
 * @property int $noof_subcontractor
 * @property int $status 0=Open,1=In Process,2=Waiting for Customer Approval,3=Approved,4=Negotiated,5=Rejected,6=Finalized
 * @property int $inspection_type
 * @property int $review_count
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class Offer extends \yii\db\ActiveRecord
{

    //public $arrStatus=array('0'=>'Open','1'=>"In Process",'2'=>'Waiting for Customer Approval','3'=>'Customer Approved','4'=>'Customer Rejected','5'=>'Rejected','6'=>'Finalized','7'=>'Waiting for Client Information');
    //public $enumStatus=array('open'=>'0','in-progress'=>"1",'waiting-for-customer-approval'=>'2','customer_approved'=>'3','customer_rejected'=>'4','rejected'=>'5','finalized'=>'6','waiting_for_audit_report'=>'7');
	
	public $arrStatus=array('0'=>'Open','1'=>"In Process",'2'=>'Waiting for OSS Approval','3'=>'Waiting for Send to Customer','4'=>'Re-Initiated to OSS','5'=>'Waiting for Customer Approval','6'=>'Customer Approved','7'=>'Customer Rejected','8'=>'Rejected','9'=>'Finalized','10'=>'Waiting for Client Information');
    public $enumStatus=array('open'=>'0','in-progress'=>"1",'waiting-for-oss-approval'=>"2",'waiting-for-send-to-customer'=>"3",'re-initiated-to-oss'=>"4",'waiting-for-customer-approval'=>'5','customer_approved'=>'6','customer_rejected'=>'7','rejected'=>'8','finalized'=>'9','waiting_for_audit_report'=>'10');
    public $arrStatusColor=array('0'=>'#4572A7','1'=>"#DB843D",'2'=>'#3D96AE','3'=>"#DB843D",'4'=>'#3D96AE','5'=>'#80699B','6'=>'#3D96AE','7'=>'#ff0000','8'=>'#89A54E','9'=>'#89A54E','10'=>'#89A54E');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_offer';
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
            [['app_id', 'noof_subcontractor', 'status', 'inspection_type', 'review_count', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['standard', 'subcontractor_name'], 'string'],
            [['offer_code'], 'string', 'max' => 255],
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
            'offer_code' => 'Offer Code',
            'standard' => 'Standard',
            'subcontractor_name' => 'Subcontractor Name',
            'noof_subcontractor' => 'Noof Subcontractor',
            'status' => 'Status',
            'inspection_type' => 'Inspection Type',
            'review_count' => 'Review Count',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
	
	public function getOfferlist()
    {
        return $this->hasOne(OfferList::className(), ['offer_id' => 'id'])->andOnCondition(['is_latest' => 1]);
    }
		
	public function getApplication()
    {
        return $this->hasOne(Application::className(), ['id' => 'app_id']);
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }
	
	public function getState()
    {
        return $this->hasOne(State::className(), ['id' => 'state_id']);
    }

    public function getMandaycost()
    {
        return $this->hasOne(Mandaycost::className(), ['id' => 'country_id']);
    }

    public function getApplicationstandard()
    {
        return $this->hasMany(ApplicationStandard::className(), ['app_id' => 'id']);
    }

    public function getApplicationunit()
    {
        return $this->hasMany(ApplicationUnit::className(), ['app_id' => 'id']);
    }
	
	public function getInvoice()
    {
        return $this->hasOne(Invoice::className(), ['offer_id' => 'id']);
    }
	
	public function getAudit()
    {
        return $this->hasOne(Audit::className(), ['offer_id' => 'id']);
    }

    public function getReinitiatecomment()
    {
        return $this->hasMany(OfferReinitiateComment::className(), ['offer_id' => 'id']);
    }

    public function getUsername()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
	
	public function generateInvoice($offermodel,$type)
	{
		$offerlist = $offermodel->offerlist;
		if($offerlist!==null)
		{	
			$appObj = $offermodel->application;
			$ospid = $appObj->franchise_id;			
			$appUnitObj = $appObj->applicationunit;
			
			$franchiseID = $appObj->franchise_id;
			
			$invoiceCount = 0;
			$connection = Yii::$app->getDb();

			$command = $connection->createCommand("SELECT COUNT(invoice.id) AS invoice_count FROM `tbl_invoice` AS invoice
			INNER JOIN `tbl_offer` AS offer ON offer.id=invoice.offer_id
			INNER JOIN `tbl_application` AS app ON app.id = offer.app_id AND app.franchise_id='$ospid' 
			GROUP BY app.franchise_id");
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
			$invoicecode = "SY-".$offermodel->application->franchise->usercompanyinfo->osp_number."-".$maxid."/".date("Y");
									
			$invoicemodel=new Invoice();	
			$invoicemodel->app_id=$offermodel->app_id;
			$invoicemodel->offer_id=$offerlist->offer_id;						
			$invoicemodel->invoice_number=$invoicecode;
            $invoicemodel->franchise_id=$ospid;
			$invoicemodel->customer_id=$offermodel->application->customer_id;
			
			$invoicemodel->currency_code=$offerlist->conversion_currency_code ? $offerlist->conversion_currency_code : $offerlist->currency;		
			
			$invoicemodel->total_fee=$offerlist->total;
			$invoicemodel->discount=$offerlist->discount;
			$invoicemodel->grand_total_fee=$offerlist->grand_total_fee;
			$invoicemodel->tax_amount=$offerlist->tax_amount;
			$invoicemodel->tax_percentage=$offerlist->tax_percentage;
			
			$invoicemodel->total_payable_amount=$offerlist->total_payable_amount;									
			$invoicemodel->invoice_type=$type;
			if($invoicemodel->save())
			{
				$invoiceID = $invoicemodel->id;
				
				if($type==1 || $type==2)
				{
					$this->storeAppStandard($appObj,$invoiceID);					
				}
				
				// -------- Invoice to Client based on Certification Fee Code Start Here ----------
				if($type==1)
				{					
					$this->storeCertificateFees($offermodel,1,$invoiceID);
				}	
				// -------- Invoice to Client based on Certification Fee Code End Here ----------
				
								
				// -------- Invoice to OSS Get the Royalty Fee based on the Standard Code Start Here ----------
				$arrApplicationUnitStandardIDs=array();
				$arrApplicationUnitStandardCode=array();
				$arrScopeHolderStandards=array();
				$arrFacilityStandards=array();
				$arrSubContractorStandards=array();				
				$RoyaltyFee=0;				
				$RoyaltyTotalFee=0;								
				
				//if($type==2)
				//{
					// ---- Store the Application Unit Standard in Invoice Code Start Here -------
					if(count($appUnitObj)>0)
					{
						foreach($appUnitObj as $appUnit)
						{
							$appUnitType = $appUnit->unit_type;									
							if($appUnitType==1 || $appUnitType==2)
							{
								$arrUnitStd=array();
								$appUnitStdObj = $appUnit->unitappstandard;							
								if(count($appUnitStdObj)>0)
								{
									foreach($appUnitStdObj as $appUnitStd)
									{
										$arrUnitStd[]=$appUnitStd->standard_id;
									}																									
									
									sort($arrUnitStd);
									if($type==2)
									{
										$connection = Yii::$app->getDb();								
										$command = $connection->createCommand("SELECT comb.*,GROUP_CONCAT(combstd.standard_id ORDER BY combstd.standard_id ASC ) AS standardids FROM `tbl_certificate_royalty_fee` AS comb INNER JOIN `tbl_certificate_royalty_fee_cs` AS combstd ON comb.id=combstd.certificate_royalty_fee_id WHERE comb.franchise_id='".$franchiseID."' GROUP BY comb.id HAVING standardids ='".implode(',',$arrUnitStd)."'");
										$result = $command->queryOne();
										if($result !== false)
										{
											$RoyaltyFee=0;										
											if($appUnitType==1)
											{
												$RoyaltyFee=$result['scope_holder_fee'];																						
											}elseif($appUnitType==2){
												$RoyaltyFee=$result['facility_fee'];											
											}
											
											/*
											elseif($appUnitType==3){
												$RoyaltyFee=$result['sub_contractor_fee'];											
											}
											*/												
											
											$RoyaltyTotalFee = $RoyaltyTotalFee + $RoyaltyFee;
										}
									}	
									
									if($appUnitType==1)
									{
										if(count($arrScopeHolderStandards)>0)
										{
											$arrScopeHolderStandards = array_merge($arrScopeHolderStandards,$arrUnitStd);
										}else{
											$arrScopeHolderStandards = $arrUnitStd;
										}										
									}elseif($appUnitType==2){										
										if(count($arrFacilityStandards)>0)
										{
											$arrFacilityStandards = array_merge($arrFacilityStandards,$arrUnitStd);
										}else{
											$arrFacilityStandards = $arrUnitStd;
										}										
									}
									
									/*
									elseif($appUnitType==3){																				
										if(count($arrSubContractorStandards)>0)
										{
											$arrSubContractorStandards = array_merge($arrSubContractorStandards,$arrUnitStd);
										}else{
											$arrSubContractorStandards = $arrUnitStd;
										}										
									}
									*/																			
								}
								
							}elseif($appUnitType==3){
								
								$arrApplicationUnitStandardCode=array();
								$arrApplicationUnitStandardIDs=array();
								$appUnitStdObj = $appUnit->unitappstandard;	
								if(count($appUnitStdObj)>0)
								{
									foreach($appUnitStdObj as $appUnitStd)
									{
										$stdcode = $appUnitStd->standard->code;
										$arrApplicationUnitStandardCode[]=$stdcode;
										$arrApplicationUnitStandardIDs[$stdcode]=$appUnitStd->standard->id;
									}
								}
								
								// -------------- Certified Sub Con Fees Code Start Here --------------
								$arrUnitCertifiedStd=array();
								$arrUnitCerfifiedStdCode=array();
								$appUnitCertifiedStdObj = $appUnit->unitstandard;							
								if(count($appUnitCertifiedStdObj)>0)
								{
									foreach($appUnitCertifiedStdObj as $appUnitCertStd)
									{
										if(in_array($appUnitCertStd->standard->code,$arrApplicationUnitStandardCode))
										{
											//$arrUnitCertifiedStd[]=$appUnitCertStd->standard_id;
											$arrUnitCertifiedStd[]=$arrApplicationUnitStandardIDs[$appUnitCertStd->standard->code];
											$arrUnitCerfifiedStdCode[]=$appUnitCertStd->standard->code;	
										}	
									}
									
									if(count($arrUnitCertifiedStd)>0)
									{
										sort($arrUnitCertifiedStd);
										if($type==2)
										{
											$connection = Yii::$app->getDb();								
											$command = $connection->createCommand("SELECT comb.*,GROUP_CONCAT(combstd.standard_id ORDER BY combstd.standard_id ASC ) AS standardids FROM `tbl_certificate_royalty_fee` AS comb INNER JOIN `tbl_certificate_royalty_fee_cs` AS combstd ON comb.id=combstd.certificate_royalty_fee_id WHERE comb.franchise_id='".$franchiseID."' GROUP BY comb.id HAVING standardids ='".implode(',',$arrUnitCertifiedStd)."'");
											$result = $command->queryOne();
											if($result !== false)
											{							
												$RoyaltyFee=$result['sub_contractor_fee'];																				
												$RoyaltyTotalFee = $RoyaltyTotalFee + $RoyaltyFee;
											}
										}																			
										$arrSubContractorStandards = array_merge($arrSubContractorStandards,$arrUnitCertifiedStd);
									}			
								}							
								// -------------- Certified Sub Con Fees Code End Here --------------
								
								// -------------- Non Certified Sub Con Fees Code Start Here --------------
								$arrUnitStd=array();								
								if(count($appUnitStdObj)>0)
								{
									foreach($appUnitStdObj as $appUnitStd)
									{
										if(!in_array($appUnitStd->standard->code,$arrUnitCerfifiedStdCode))
										{
											$arrUnitStd[]=$appUnitStd->standard_id;
										}
									}
									
									if(count($arrUnitStd)>0)
									{
										sort($arrUnitStd);
										if($type==2)
										{
											$connection = Yii::$app->getDb();								
											$command = $connection->createCommand("SELECT comb.*,GROUP_CONCAT(combstd.standard_id ORDER BY combstd.standard_id ASC ) AS standardids FROM `tbl_certificate_royalty_fee` AS comb INNER JOIN `tbl_certificate_royalty_fee_cs` AS combstd ON comb.id=combstd.certificate_royalty_fee_id WHERE comb.franchise_id='".$franchiseID."' GROUP BY comb.id HAVING standardids ='".implode(',',$arrUnitStd)."'");
											$result = $command->queryOne();
											if($result !== false)
											{							
												$RoyaltyFee=$result['non_certified_subcon_fee'];																				
												$RoyaltyTotalFee = $RoyaltyTotalFee + $RoyaltyFee;
											}
										}										
										$arrSubContractorStandards = array_merge($arrSubContractorStandards,$arrUnitStd);	
									}	
								}									
								// -------------- Non Certified Sub Con Fees Code End Here --------------							
								
							}								
						}
					}						
					// ---- Store the Application Standard in Invoice Code End Here -------	
					
					if($type==2)
					{					
						$invoiceDetailsModel=new InvoiceDetails();
						$invoiceDetailsModel->invoice_id=$invoiceID;
						$invoiceDetailsModel->activity='Royalty Fees';
						$invoiceDetailsModel->description='Royalty Fees';
						$invoiceDetailsModel->amount=$RoyaltyTotalFee;					
						$invoiceDetailsModel->type='1';									
						$invoiceDetailsModel->entry_type=0;
						$invoiceDetailsModel->save();
						
						if(count($arrScopeHolderStandards)>0)
						{
							array_unique($arrScopeHolderStandards);
							foreach($arrScopeHolderStandards as $arrScopeHolderStd)
							{
								$invoiceDetailsStdModel=new InvoiceDetailsStandard();
								$invoiceDetailsStdModel->invoice_detail_id=$invoiceDetailsModel->id;
								$invoiceDetailsStdModel->standard_id=$arrScopeHolderStd;
								$invoiceDetailsStdModel->save();
							}
						}
					}
				//}	
				// -------- Invoice to OSS Get the Royalty Fee based on the Standard Code End Here ----------
				
				// -------- Invoice to Client & OSS based on Other Expenses Code End Here ----------
				$totalOfferOtherExpenses=0;
				$offerotherexpense = $offerlist->offerotherexpenses;
				if(count($offerotherexpense)>0)
				{
					$arrOE=array();

					$licensefeeTotal = 0;
					$handlingfeeTotal = 0;
					foreach($offerotherexpense as $otherE)
					{
						if($otherE->type==1)
						{
							$licensefeeTotal += $otherE->amount;
							$totalOfferOtherExpenses = $totalOfferOtherExpenses+$otherE->amount;	
						}elseif($otherE->type==2){
							$handlingfeeTotal += $otherE->amount;
							$totalOfferOtherExpenses = $totalOfferOtherExpenses+$otherE->amount;	
						}						
					}
					
					$invoiceDetailsModel=new InvoiceDetails();
					$invoiceDetailsModel->invoice_id=$invoiceID;
					$invoiceDetailsModel->activity= 'Licensee Fees';
					$invoiceDetailsModel->description='Licensee Fees';
					$invoiceDetailsModel->amount=$licensefeeTotal;
					$invoiceDetailsModel->type='2';									
					$invoiceDetailsModel->entry_type=0;
					$invoiceDetailsModel->save();
					
					// ---- Store the Application Standard in Invoice Details Code Start Here -------
					//$arrScopeHolderStandards=array();									
					if(count($arrScopeHolderStandards)>0)
					{
						array_unique($arrScopeHolderStandards);
						foreach($arrScopeHolderStandards as $arrScopeHolderStd)
						{
							$invoiceDetailsStdModel=new InvoiceDetailsStandard();
							$invoiceDetailsStdModel->invoice_detail_id=$invoiceDetailsModel->id;
							$invoiceDetailsStdModel->standard_id=$arrScopeHolderStd;
							$invoiceDetailsStdModel->save();
						}
					}
					//print_r($arrScopeHolderStandards);
					
					if($handlingfeeTotal>0)
					{
						$invoiceDetailsModel=new InvoiceDetails();
						$invoiceDetailsModel->invoice_id=$invoiceID;
						$invoiceDetailsModel->activity= 'Handling Fees';
						$invoiceDetailsModel->description='Handling Fees';
						$invoiceDetailsModel->amount=$handlingfeeTotal;
						$invoiceDetailsModel->type='2';									
						$invoiceDetailsModel->entry_type=0;
						$invoiceDetailsModel->save();
						
						if(count($arrSubContractorStandards)>0)
						{
							array_unique($arrSubContractorStandards);
							foreach($arrSubContractorStandards as $arrInvoiceDetailsStd)
							{
								$invoiceDetailsStdModel=new InvoiceDetailsStandard();
								$invoiceDetailsStdModel->invoice_detail_id=$invoiceDetailsModel->id;
								$invoiceDetailsStdModel->standard_id=$arrInvoiceDetailsStd;
								$invoiceDetailsStdModel->save();
							}
						}
					}					
					
					// ---- Store the Application Standard in Invoice Details Code End Here -------
					

					foreach($offerotherexpense as $otherE)
					{												
						//if(($type==1) || ($type==2 && $otherE->entry_type==0))
						if($type==1 && $otherE->type!=1 && $otherE->type!=2)
						{
							$invoiceDetailsModel=new InvoiceDetails();
							$invoiceDetailsModel->invoice_id=$invoiceID;
							$invoiceDetailsModel->activity=$otherE->activity;
							$invoiceDetailsModel->description=$otherE->description;
							$invoiceDetailsModel->amount=$otherE->amount;
							//$invoiceDetailsModel->conversion_amount=$otherE->conversion_amount;
							$invoiceDetailsModel->type='2';									
							$invoiceDetailsModel->entry_type=0;
							$invoiceDetailsModel->save();
	
							$totalOfferOtherExpenses = $totalOfferOtherExpenses+$invoiceDetailsModel->amount;						
						}
					}					
				}
				// -------- Invoice to Client & OSS based on Other Expenses Code End Here ----------
				
				// -------- Invoice to OSS Calculation based Royalty Fee & Other Expenses Code Start Here ----------
				if($type==2)
				{										
					$royaltyTotal=$RoyaltyTotalFee+$totalOfferOtherExpenses;
					$offerDiscount = $offerlist->discount;
					$royaltyGrandTotalFee=$royaltyTotal-$offerDiscount;					
					
					$royalBasedTaxAmount = 0;
					$royalBasedTotalTaxAmount = 0;
					$ofrtax = $offerlist->offertax;
					$taxpercentage=0;
					if(count($ofrtax)>0)
					{
						
						foreach($ofrtax as $ofrT)
						{
							$taxpercentage=$taxpercentage+$ofrT->tax_percentage;
							
							$royalBasedTaxAmount=0;
							$royalBasedTaxAmount=($royaltyGrandTotalFee*$ofrT->tax_percentage/100);
							$royalBasedTotalTaxAmount = $royalBasedTotalTaxAmount+$royalBasedTaxAmount;	
							
							$invoiceListTax=new InvoiceTax();
							$invoiceListTax->invoice_id=$invoiceID;							
							$invoiceListTax->tax_name=$ofrT->tax_name;	
							$invoiceListTax->tax_percentage=$ofrT->tax_percentage;
							$invoiceListTax->amount=$royalBasedTaxAmount;							
							$invoiceListTax->save();
						}							
					}
					
					$invoicemodel->total_fee=$royaltyTotal;					
					$invoicemodel->grand_total_fee=$royaltyGrandTotalFee;
					$invoicemodel->tax_amount=$royalBasedTotalTaxAmount;
					$invoicemodel->tax_percentage=$taxpercentage;
					
					$royaltyTotalPayableAmount=$royaltyGrandTotalFee+$royalBasedTotalTaxAmount;
					$invoicemodel->total_payable_amount=$royaltyTotalPayableAmount;
					$invoicemodel->save();
				}
				// -------- Invoice to OSS Calculation based Royalty Fee & Other Expenses Code End Here ----------
				
				// -------- Invoice to Client Tax Code Start Here ----------
				if($type==1)
				{
					$this->storeCertificateFees($offermodel,2,$invoiceID);
					$this->storeCertificateFees($offermodel,3,$invoiceID);
					
					$ofrtax = $offerlist->offertax;
					if(count($ofrtax)>0)
					{
						$taxpercentage=0;
						foreach($ofrtax as $ofrT)
						{														
							$invoiceListTax=new InvoiceTax();
							$invoiceListTax->invoice_id=$invoiceID;							
							$invoiceListTax->tax_name=$ofrT->tax_name;	
							$invoiceListTax->tax_percentage=$ofrT->tax_percentage;
							$invoiceListTax->amount=$ofrT->amount;							
							$invoiceListTax->save();
						}							
					}
				}
				// -------- Invoice to Client Tax Code End Here ----------				
			}	
		}
	}
	
	public function storeCertificateFees($offermodel,$feeType,$invoiceID)
	{
		$certificationfee=$offermodel->offerlist->offercertificationfee;
		if(count($certificationfee)>0)
		{
			$arrOE=array();
			foreach($certificationfee as $certF)
			{
				if($certF->type != $feeType)
				{
					continue;
				}
				
				$invoiceDetailsModel=new InvoiceDetails();
				$invoiceDetailsModel->invoice_id=$invoiceID;
				$invoiceDetailsModel->activity=$certF->activity;
				$description = $certF->description;
				if($certF->type == 1){
					$description = 'Certification Fees for '.$offermodel->manday.' Manday(s) (including sub contractor)';
				}
				$invoiceDetailsModel->description=$description;//$certF->description;
				$invoiceDetailsModel->amount=$certF->amount;
				//$invoiceDetailsModel->conversion_amount=$certF->conversion_amount;
				$invoiceDetailsModel->type='1';									
				$invoiceDetailsModel->entry_type=0;
				$invoiceDetailsModel->save();
				
				if($certF->type == 1)
				{
					$this->storeAppStandard($offermodel->application,$invoiceDetailsModel->id,2);
				}	
				
				if($certF->type == $feeType && $certF->type == 1)
				{
					break;
				}	
			}						
		}
	}
	
	public function storeAppStandard($appObj,$parentID,$insertType=1)
	{
		// ---- Store the Application Standard in Invoice Code Start Here -------
		if($appObj!==null)
		{
			$appStdObj = $appObj->applicationstandard;
			if(count($appStdObj)>0)
			{
				foreach($appStdObj as $appStd)
				{
					if($insertType==1)
					{
						$invoiceStdModel=new InvoiceStandard();
						$invoiceStdModel->invoice_id=$parentID;
					}else{
						$invoiceStdModel=new InvoiceDetailsStandard();
						$invoiceStdModel->invoice_detail_id=$parentID;
					}
					$invoiceStdModel->standard_id=$appStd->standard_id;
					$invoiceStdModel->save();
				}
			}	
		}
		// ---- Store the Application Standard in Invoice Code End Here -------
	}
}
