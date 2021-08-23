<?php
namespace app\modules\application\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use app\modules\master\models\Mandaycost;
use app\modules\master\models\Country;
use app\modules\master\models\State;
use app\modules\master\models\User;
use app\modules\offer\models\Offer;
use app\modules\certificate\models\Certificate;

use app\models\EnquiryStandard;
use app\models\Enquiry;

use app\modules\audit\models\Audit;
use app\modules\unannouncedaudit\models\UnannouncedAuditApplication;
use app\modules\transfercertificate\models\Request;

use app\modules\changescope\models\ProductAddition;
use app\modules\changescope\models\ProcessAdditionUnit;
use app\modules\changescope\models\UnitAdditionUnit;
use app\modules\changescope\models\UnitAddition;

use app\modules\master\models\AuditReviewerRiskCategory;

/**
 * This is the model class for table "tbl_application".
 *
 * @property int $id
 * @property int $customer_id
 * @property string $code
 * @property string $company_name
 * @property string $address
 * @property string $zipcode
 * @property int $state_id
 * @property int $country_id
 * @property string $salutation
 * @property string $title
 * @property string $first_name
 * @property string $last_name
 * @property string $job_title
 * @property string $telephone
 * @property string $email_address
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class Application extends \yii\db\ActiveRecord
{
	public $arrStatus=array('0'=>'Open','1'=>'Submitted','2'=>'Waiting for Review','3'=>"Review in Process",'4'=>'Waiting for Approval','5'=>'Approval in Process','6'=>'Approved','7'=>'Pending with Customer','8'=>'Failed','9'=>'Re-Initiate for Review','10'=>'Rejected');
	public $arrEnumStatus=array('open'=>'0','submitted'=>'1','waiting_for_review'=>'2',"review_in_process"=>'3','review_completed'=>'4','approval_in_process'=>'5','approved'=>'6','pending_with_customer'=>'7','failed'=>'8','re-initiate_for_review'=>'9','osp_reject'=> '10');
	public $arrBrandStatus=array('0'=>'Open','1'=>'Approved','2'=>'Rejected','3'=>'Re Assigned');
	public $arrBrandEnumStatus=array('open'=>'0','approved'=>'1','rejected'=>'2','re_assigned'=>'3');
	public $arrBrandColor=array('0'=>'#4572A7','1'=>'#89a54e','2'=>'#ff0000','3'=>'#bf5aed');
    public $arrSalutation=array('1'=>'Mr','2'=>'Mrs','3'=>'Ms','4'=>'Dr');
    public $arrStatusColor=array('0'=>'#4572A7','1'=>"#DB843D",'2'=>'#3D96AE','3'=>'#4eba8f','4'=>'#bf5aed','5'=>'#f15c80','6'=>'#89a54e','7'=>'#A47D7C','8'=>'#ff0000','9'=>'#5f79fa','10'=>'#ff0000');
    
    public $arrOverallStatus=array('0'=>'Open','1'=>"Application in Process",'2'=>'Application Approved','3'=>'Application Rejected','4'=>'Quotation in Process','5'=>'Quotation Approved','6'=>'Quotation Rejected','7'=>'Audit Plan in Progress','8'=>'Audit in Progress','9'=>'Audit Finalized','10'=>'Audit Rejected','11'=>'Certificate in Process','12'=>'Certificate Generated','13'=>'Certificate Declined');
    public $arrEnumOverallStatus=array('open'=>'0','application_in_process'=>'1','application_approved'=>'2','application_rejected'=>'3','quotation_in_process'=>'4','quotation_approved'=>'5','quotation_rejected'=>'6','audit_plan_in_progress'=>'7','audit_in_progress'=>'8','audit_finalized'=>'9','audit_rejected'=>'10','certificate_in_process'=>'11','certificate_generated'=>'12','certificate_declined'=>'13');
	
	//public $arrOverallStatus=array('0'=>'Open','1'=>"Submitted",'2'=>'Waiting for Review','3'=>'Review in Process','4'=>'Waiting for Approval','5'=>'Approval in Process','6'=>'Approved','7'=>'Failed','8'=>'Offer in Process','9'=>'Offer Completed','10'=>'Offer Rejected','11'=>'Invoice in Process','12'=>'Invoice Completed','13'=>'Invoice Rejected','14'=>'Rejected');
    //public $arrEnumOverallStatus=array('open'=>0,'submitted'=>1,'waiting_for_review'=>2,'review_in_process'=>3,'review_completed'=>4,'approval_in_process'=>5,'approved'=>6,'failed'=>7,'offer_in_process'=>8,'offer_completed'=>9,'offer_rejected'=>10,'invoice_in_process'=>11,'invoice_completed'=>12,'invoice_rejected'=>13,'osp_reject'=>14);
	
	public $arrAuditType=array('1'=>'Initial','2'=>'Renewal','3'=>'Process Addition','4'=>'Standard Addition','5'=>'Unit Addition','6'=>'Change of Address');
	public $arrEnumAuditType=array('normal'=>'1','renewal'=>'2','process_addition'=>'3','standard_addition'=>'4','unit_addition'=>'5','change_of_address'=>'6');

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application';
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
            [['code'], 'string'],
            //[['state_id', 'country_id', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
			[['status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer']
            //[['code', 'company_name', 'title', 'first_name', 'last_name', 'job_title', 'email_address'], 'string', 'max' => 255],
            //[['zipcode', 'telephone'], 'string', 'max' => 50],
            //[['salutation'], 'string', 'max' => 25],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'code' => 'Code',
            'company_name' => 'Company Name',
            'address' => 'Address',
            'zipcode' => 'Zipcode',
            'state_id' => 'State ID',
            'country_id' => 'Country ID',
            'salutation' => 'Salutation',
            'title' => 'Title',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'job_title' => 'Job Title',
            'telephone' => 'Telephone',
            'email_address' => 'Email Address',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
	
	public function getCertificate()
    {
        return $this->hasOne(Certificate::className(), ['parent_app_id' => 'id']);
	}

    public function getBrands(){
		return $this->hasOne(ApplicationBrands::className(),['app_id'=>'id']);
	}

	public function getChildapps()
    {
        return $this->hasMany(Application::className(), ['parent_app_id' => 'id']);
	}

	public function getAudit()
    {
        return $this->hasOne(Audit::className(), ['app_id' => 'id']);
	}

	public function getApplicationbrands()
	{
		return $this->hasMany(ApplicationBrands::className(),['app_id'=>'id']);
	}

	public function getUnannoucedaudit()
    {
        return $this->hasMany(Audit::className(), ['app_id' => 'id'])->andOnCondition(['audit_type' => 2]);
	}
	public function getNormalaudit()
    {
        return $this->hasOne(Audit::className(), ['app_id' => 'id'])->andOnCondition(['audit_type' => 1]);
	}

	public function getTcrequest()
    {
        return $this->hasMany(Request::className(), ['app_id' => 'id']);
	}

	public function getUnannouncedaudit()
    {
        return $this->hasOne(UnannouncedAuditApplication::className(), ['app_id' => 'id']);
	}

	public function getCurrentaddress()
    {
        return $this->hasOne(ApplicationChangeAddress::className(), ['parent_app_id' => 'id'])->orderBy(['id' => SORT_DESC]);
	}

	public function getApplicationaddress()
    {
        return $this->hasOne(ApplicationChangeAddress::className(), ['id' => 'address_id']);
	}

	public function getParentaudit()
    {
        return $this->hasOne(Audit::className(), ['app_id' => 'parent_app_id']);
	}
	
	public function getApplicationstandard()
    {
        return $this->hasMany(ApplicationStandard::className(), ['app_id' => 'id'])->andOnCondition(['standard_status' => array(0,8,5)]);
	}
	
	public function getApplicationstandardview()
    {
		if($this->audit_type==$this->arrEnumAuditType['normal']){
			return $this->hasMany(ApplicationStandard::className(), ['app_id' => 'id'])->andOnCondition(['standard_addition_type' => 0]);
		}else{
			return $this->hasMany(ApplicationStandard::className(), ['app_id' => 'id']);
		}
        
    }
	
	public function getApplicationstandardnormal()
    {
        return $this->hasMany(ApplicationStandard::className(), ['app_id' => 'id'])->andOnCondition(['standard_addition_type' => 0]);
	}
	
    public function getEnquiryStandard()
    {
        return $this->hasMany(EnquiryStandard::className(), ['enquiry_id' => 'id']);
    }

	public function getEnquirydetails()
    {
        return $this->hasOne(Enquiry::className(), ['app_id' => 'id']);
	}
	
	public function getApplicationproduct()
    {
        return $this->hasMany(ApplicationProduct::className(), ['app_id' => 'id']);
	}
	
	public function getApplicationproductnormal()
    {
        return $this->hasMany(ApplicationProduct::className(), ['app_id' => 'id'])->andOnCondition(['product_addition_type' => 0]);
    }
    
    public function getApplicationproductdetails()
    {
        return $this->hasMany(ApplicationProductDetails::className(), ['app_id' => 'id']);
    }
    
	public function getApplicationunit()
    {
        return $this->hasMany(ApplicationUnit::className(), ['app_id' => 'id'])->andOnCondition(['status' => 0]);
	}

	public function getApplicationunitall()
    {
        return $this->hasMany(ApplicationUnit::className(), ['app_id' => 'id']);
	}
	
	public function getApplicationunitremoved()
    {
        return $this->hasMany(ApplicationUnit::className(), ['app_id' => 'id'])->andOnCondition(['status' => 1]);
    }
	
	public function getApplicationscopeholder()
    {
        return $this->hasOne(ApplicationUnit::className(), ['app_id' => 'id'])->andOnCondition(['unit_type' => 1]);
    }
	public function getUnitsubcontractor()
    {
        return $this->hasMany(ApplicationUnit::className(), ['app_id' => 'id'])->andOnCondition(['unit_type' => 3]);
    }
    public function getApplicationunitnormal()
    {
        return $this->hasMany(ApplicationUnit::className(), ['app_id' => 'id'])->andOnCondition(['unit_addition_type' => 0,'status' => 0]);
    }
	public function getApplicationunitlist()
    {
        return $this->hasMany(ApplicationUnit::className(), ['app_id' => 'id'])->andOnCondition(['unit_type' => [2,3],'status' => 0]);
	}
	
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }
	
	public function getState()
    {
        return $this->hasOne(State::className(), ['id' => 'state_id']);
    }    

    public function getUsername()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
	
    public function getFranchise()
    {
        return $this->hasOne(User::className(), ['id' => 'franchise_id']);
    }
	
	public function getPreferredpartner()
    {
        return $this->hasOne(Country::className(), ['id' => 'preferred_partner_id']);
    }
	
	public function getApplicationreview()
    {
        return $this->hasMany(ApplicationReview::className(), ['app_id' => 'id']);
    }

    public function getApplicationchecklistcmt()
    {
        return $this->hasMany(ApplicationChecklistComment::className(), ['app_id' => 'id']);
    }

    public function getCertificationbody()
    {
        return $this->hasMany(ApplicationCertifiedByOtherCB::className(), ['app_id' => 'id']);
    }

    public function ApplicationUnitReviewComment()
    {
        return $this->hasMany(ApplicationUnitReviewComment::className(), ['app_id' => 'id']);
    }
	public function getOffer()
    {
        return $this->hasOne(Offer::className(), ['app_id' => 'id']);
    }

    public function getApplicationapproval()
    {
        return $this->hasMany(ApplicationApproval::className(), ['app_id' => 'id']);
    }
    
	public function getCustomer()
    {
        return $this->hasOne(User::className(), ['id' => 'customer_id']);
	}
	
	public function getRiskcategory()
    {
        return $this->hasOne(AuditReviewerRiskCategory::className(), ['id' => 'risk_category']);
    }
	
	public function getManday()
    {
        return $this->hasMany(ApplicationUnitManday::className(), ['app_id' => 'id']);
    }	
	
	public function cloneApplicationProduct($data)
	{
		$arrUnitIDs=array();
		if($data)
		{ 
			$model = Application::find()->where(['id' => $data['app_id']]);
			
			$standard_id = $data['standard_id'];
			
						
			$connection = Yii::$app->getDb();
			$model= $model->one();
			if ($model !== null)
			{
				 		
				$additionmodel = ProductAddition::find()->where(['id' => $data['product_addition_id']])->one();

				
							
				if(1)
				{
					$appID = $model->id;

					// Application Product Starts						
					$productstandardarr=[];
					$appProduct=$additionmodel->additionproduct;
					if(count($appProduct)>0)
					{
						foreach($appProduct as $prd)
						{
							//if($data['audit_type'] == $modelApplication->arrEnumAuditType['unit_addition'] || $data['audit_type'] == $modelApplication->arrEnumAuditType['process_addition']){
							$pdtstdexits =0;
							foreach($prd->productstandard as $chkproductstandard)
							{
								if($chkproductstandard->standard_id == $standard_id){
									$pdtstdexits = 1;
								}
							}
							if(!$pdtstdexits){
								continue;
							}
							//}

							$appproductmodel=new ApplicationProduct();
							$appproductmodel->app_id = $appID;
							$appproductmodel->product_id = $prd->product_id;
							$appproductmodel->product_type_id = $prd->product_type_id;
							$appproductmodel->product_name = $prd->product_name;
							$appproductmodel->product_type_name = $prd->product_type_name;
							$appproductmodel->wastage = $prd->wastage;
							$appproductmodel->product_addition_type = 1;
							$appproductmodel->save();								
							
							foreach($prd->additionproductmaterial as $productmaterial)
							{
								$appproductmaterialmodel=new ApplicationProductMaterial();
								$appproductmaterialmodel->app_product_id=$appproductmodel->id;
								$appproductmaterialmodel->material_id=$productmaterial->material_id;
								$appproductmaterialmodel->material_type_id=$productmaterial->material_type_id;
								$appproductmaterialmodel->material_name=$productmaterial->material_name;
								$appproductmaterialmodel->material_type_name=$productmaterial->material_type_name;
								$appproductmaterialmodel->percentage=$productmaterial->percentage;
								$appproductmaterialmodel->save();  									
							}
							
							foreach($prd->additionproductstandard as $productstandard)
							{
								//if($data['audit_type'] == $modelApplication->arrEnumAuditType['unit_addition'] || $data['audit_type'] == $modelApplication->arrEnumAuditType['process_addition']){
								if($productstandard->standard_id != $standard_id){
									continue;
								}
								//}

								$appproductstandardmodel=new ApplicationProductStandard();
								$appproductstandardmodel->standard_id=$productstandard->standard_id;
								$appproductstandardmodel->application_product_id=$appproductmodel->id;
								$appproductstandardmodel->label_grade_id =$productstandard->label_grade_id;
								$appproductstandardmodel->label_grade_name =$productstandard->label_grade_name;
								$appproductstandardmodel->save();
								$productstandardarr[$productstandard->id]=$appproductstandardmodel->id;
							}							
						}
					}					
					
					// Application Unit Starts
					$unitarr=array();
					$unitnamedetailsarr=array();
					$appUnit=$additionmodel->additionunit;
					if(count($appUnit)>0)
					{
						foreach($appUnit as $unit)
						{																
							$appunitmodel=ApplicationUnit::find()->where(['id'=>$unit->unit_id])->one();
							 
							if($appunitmodel !== null)
							{
								$unitID = $appunitmodel->id;
								
								$arrUnitIDs[$unit->id]=$unitID;
								
								$unitprd=$unit->unitproduct;
								if(count($unitprd)>0)
								{
									$unitprdidsarr=array();										
									foreach($unitprd as $unitP)
									{
										if(!isset($productstandardarr[$unitP->application_product_standard_id])){
											continue;
										}
										$appunitproductmodel=new ApplicationUnitProduct();
										$appunitproductmodel->unit_id=$unitID;
										$appunitproductmodel->product_addition_type = 1;
										$appunitproductmodel->application_product_standard_id=$productstandardarr[$unitP->application_product_standard_id];
										$appunitproductmodel->save();										
									}										
								}	
							}							
						}							
					}
					
							
				}
			}		
		}
		return true;
	}



	public function cloneApplication($data)
	{
		$arrUnitIDs=array();
		if($data)
		{
			$model = Application::find()->where(['id' => $data['id']]);
					
			$target_dir = Yii::$app->params['certification_standard_files'];
			$target_dir_checklist = Yii::$app->params['application_checklist_file'];
			$target_dir_company = Yii::$app->params['company_files'];
						
			$connection = Yii::$app->getDb();
			$model= $model->one();
			if ($model !== null)
			{
				$modelApplication = new Application();					
				
				$maxid = Application::find()->max('id');
				if(!empty($maxid)) 
				{
					$maxid = $maxid+1;
					$zerostr="00";
					if(strlen($maxid)>1)
					{
						$zerostr="0";
					}
					else if(strlen($maxid)>2)
					{
						$zerostr="";
					}
					$appcode="APRNO-".date("y")."-".$zerostr.$maxid;
				}
				else
				{
					$appcode="APRNO-".date("y")."-001";
				}
				$modelApplication->code=$appcode;
				
				$company_file='';
				if($model->company_file!='')
				{
					$company_file=Yii::$app->globalfuns->copyFiles($model->company_file,$target_dir_company);
				}
				
				
				$modelApplication->parent_app_id=$data['id'];		
				$modelApplication->company_file=$company_file;															
				$modelApplication->customer_id=$model->customer_id;

				$modelApplication->address_id=$model->currentaddress->id;
				/*
				if($model->currentaddress !== null){
					$currentaddress = $model->currentaddress;
				}else{
					$currentaddress = $model;
				}
				*/
				/*
				$modelApplication->company_name=$currentaddress->company_name;
				$modelApplication->address=$currentaddress->address;
				$modelApplication->zipcode=$currentaddress->zipcode;
				$modelApplication->city=$currentaddress->city;
				$modelApplication->salutation=($currentaddress->salutation!="")?$currentaddress->salutation:"";
									
				$modelApplication->title=($currentaddress->title!="")?$currentaddress->title:"";
				$modelApplication->first_name=($currentaddress->first_name!="")?$currentaddress->first_name:"";
				$modelApplication->last_name=($currentaddress->last_name!="")?$currentaddress->last_name:"";
				$modelApplication->job_title=($currentaddress->job_title!="")?$currentaddress->job_title:"";
				$modelApplication->telephone=($currentaddress->telephone!="")?$currentaddress->telephone:"";
				$modelApplication->email_address=($currentaddress->job_title!="")?$currentaddress->email_address:"";
									
				$modelApplication->state_id=($currentaddress->state_id!="")?$currentaddress->state_id:"";
				$modelApplication->country_id=($currentaddress->country_id!="")?$currentaddress->country_id:"";
				*/
				/*
				$modelApplication->company_name=$model->company_name;
				$modelApplication->address=$model->address;
				$modelApplication->zipcode=$model->zipcode;
				$modelApplication->city=$model->city;
				$modelApplication->salutation=($model->salutation!="")?$model->salutation:"";
									
				$modelApplication->title=($model->title!="")?$model->title:"";
				$modelApplication->first_name=($model->first_name!="")?$model->first_name:"";
				$modelApplication->last_name=($model->last_name!="")?$model->last_name:"";
				$modelApplication->job_title=($model->job_title!="")?$model->job_title:"";
				$modelApplication->telephone=($model->telephone!="")?$model->telephone:"";
				$modelApplication->email_address=($model->job_title!="")?$model->email_address:"";
									
				$modelApplication->state_id=($model->state_id!="")?$model->state_id:"";
				$modelApplication->country_id=($model->country_id!="")?$model->country_id:"";
				*/


				$modelApplication->created_by=$model->created_by;
				$modelApplication->certification_status=$model->certification_status;
				
				$modelApplication->audit_type=$data['audit_type'];
				//$arrEnumAuditType=array('normal'=>'1','renewal'=>'2','process_addition'=>'3','standard_addition'=>'4','unit_addition'=>'5');
				$renewal_standard_ids = [];
				$renewal_audit = 0;
				if($data['audit_type']==$modelApplication->arrEnumAuditType['renewal'])
				{
					$renewal_audit = 1;
					$modelApplication->overall_status=$modelApplication->arrEnumOverallStatus['application_approved'];;					
					$modelApplication->status=$model->status;

					if(isset($data['renewal_standard_ids']) && is_array($data['renewal_standard_ids'])){
						$renewal_standard_ids = $data['renewal_standard_ids'];
					}
				}else{
					$modelApplication->overall_status=$modelApplication->arrEnumOverallStatus['application_in_process'];					
					$modelApplication->status=$modelApplication->arrEnumStatus['submitted'];
				}				
				 
				$modelApplication->franchise_id=$model->franchise_id;
							
				if($modelApplication->save())
				{

					$activeStandards = [];
					$appStandard=$model->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{	
							if($std->standard_status != $std->arrEnumStatus['valid'] && $std->standard_status != $std->arrEnumStatus['expired']){
								continue;
							}	
							$activeStandards[] = $std->standard_id;
						}
					}

					$unitAdditionStdIds = [];
					$uniqueunitAdditionStdIds = [];
					if($data['audit_type'] == $modelApplication->arrEnumAuditType['unit_addition']){
						//return false;
						
						$UnitAdditionUnit = UnitAdditionUnit::find()->where(['unit_addition_id'=>$data['unit_addition_id']])->all();
						if(count($UnitAdditionUnit)>0){
							foreach($UnitAdditionUnit as $padditionunit){
								//echo count($padditionunit->unitappstandard); 
								if(count($padditionunit->unitappstandard)>0){
									foreach($padditionunit->unitappstandard as $standardunit){
										if(in_array($standardunit->standard_id,$activeStandards)){
											$unitAdditionStdIds[] = $standardunit->standard_id;
										}
										
									}
								}
							}
						}
						$uniqueunitAdditionStdIds = array_unique($unitAdditionStdIds);
					}
					
					if($data['audit_type'] == $modelApplication->arrEnumAuditType['process_addition']){
						//return false;
						$ProcessAdditionUnit = ProcessAdditionUnit::find()->where(['process_addition_id'=>$data['process_addition_id']])->all();
						if(count($ProcessAdditionUnit)>0){
							foreach($ProcessAdditionUnit as $padditionunit){
								if(count($padditionunit->unitappstandard)>0){
									foreach($padditionunit->unitappstandard as $standardunit){
										if(in_array($standardunit->standard_id,$activeStandards)){
											$unitAdditionStdIds[] = $standardunit->standard_id;
										}
									}
								}
							}
						}
						$uniqueunitAdditionStdIds = array_unique($unitAdditionStdIds);
					}
					if($renewal_audit && isset($data['renewal_standard_ids']) && is_array($data['renewal_standard_ids'])){
						$uniqueunitAdditionStdIds = $data['renewal_standard_ids'];
					}
					//print_r($unitAdditionStdIds);



					$appID = $modelApplication->id;
					
					// Application Standard Starts
					$appstdarr=[];
					$arrstandardids=[];
					$appStandard=$model->applicationstandard;
					if(count($appStandard)>0)
					{
						foreach($appStandard as $std)
						{	
							if($std->standard_status != $std->arrEnumStatus['valid'] && $std->standard_status != $std->arrEnumStatus['expired']){
								continue;
							}			
							if($renewal_audit || $data['audit_type'] == $modelApplication->arrEnumAuditType['unit_addition'] || $data['audit_type'] == $modelApplication->arrEnumAuditType['process_addition']){
								if(!in_array($std->standard_id,$uniqueunitAdditionStdIds)){
									continue;
								}
							}
							/*
							if($data['audit_type'] == $modelApplication->arrEnumAuditType['process_addition']){
								if(!in_array($std->standard_id,$uniqueunitAdditionStdIds)){
									continue;
								}
							}
							*/
							$appstdmodel=new ApplicationStandard();
							$appstdmodel->app_id=$appID;
							$appstdmodel->standard_id=$std->standard_id;
							$appstdmodel->save(); 						
						}
					}						
					
					// Application Product Starts						
					$productstandardarr=[];
					$appProduct=$model->applicationproduct;
					if(count($appProduct)>0)
					{
						

						foreach($appProduct as $prd)
						{
							if($renewal_audit || $data['audit_type'] == $modelApplication->arrEnumAuditType['unit_addition'] || $data['audit_type'] == $modelApplication->arrEnumAuditType['process_addition']){
								$pdtstdexits =0;
								foreach($prd->productstandard as $chkproductstandard)
								{
									if(in_array($chkproductstandard->standard_id,$uniqueunitAdditionStdIds)){
										$pdtstdexits = 1;
									}
								}
								if(!$pdtstdexits){
									continue;
								}
							}

							
							$appproductmodel=new ApplicationProduct();
							$appproductmodel->app_id = $appID;
							$appproductmodel->product_id = $prd->product_id;
							$appproductmodel->product_type_id = $prd->product_type_id;
							$appproductmodel->product_name = $prd->product_name;
							$appproductmodel->product_type_name = $prd->product_type_name;
							$appproductmodel->wastage = $prd->wastage;
							$appproductmodel->save();								
							
							foreach($prd->productmaterial as $productmaterial)
							{
								$appproductmaterialmodel=new ApplicationProductMaterial();
								$appproductmaterialmodel->app_product_id=$appproductmodel->id;
								$appproductmaterialmodel->material_id=$productmaterial->material_id;
								$appproductmaterialmodel->material_type_id=$productmaterial->material_type_id;
								$appproductmaterialmodel->material_name=$productmaterial->material_name;
								$appproductmaterialmodel->material_type_name=$productmaterial->material_type_name;
								$appproductmaterialmodel->percentage=$productmaterial->percentage;
								$appproductmaterialmodel->save();  									
							}
							
							foreach($prd->productstandard as $productstandard)
							{
								if($renewal_audit || $data['audit_type'] == $modelApplication->arrEnumAuditType['unit_addition'] || $data['audit_type'] == $modelApplication->arrEnumAuditType['process_addition']){
									if(!in_array($productstandard->standard_id,$uniqueunitAdditionStdIds)){
										continue;
									}									
								}

								$appproductstandardmodel=new ApplicationProductStandard();
								$appproductstandardmodel->standard_id=$productstandard->standard_id;
								$appproductstandardmodel->application_product_id=$appproductmodel->id;
								$appproductstandardmodel->label_grade_id =$productstandard->label_grade_id;
								$appproductstandardmodel->label_grade_name =$productstandard->label_grade_name;
								$appproductstandardmodel->save();
								$productstandardarr[$productstandard->id]=$appproductstandardmodel->id;
							}							
						}
					}					
					//print_r($productstandardarr);
					// Application Unit Starts
					$unitarr=array();
					$unitnamedetailsarr=array();
					$appUnit=$model->applicationunit;
					if(count($appUnit)>0)
					{
						$processunitIds = [];
						if($data['audit_type'] == $modelApplication->arrEnumAuditType['process_addition']){
							//return false;
							$ProcessAdditionUnit = ProcessAdditionUnit::find()->where(['process_addition_id'=>$data['process_addition_id']])->all();
							if(count($ProcessAdditionUnit)>0){
								foreach($ProcessAdditionUnit as $padditionunit){
									$processunitIds[] = $padditionunit->unit_id;
								}
							}
						}
						foreach($appUnit as $unit)
						{
							if($data['audit_type'] == $modelApplication->arrEnumAuditType['unit_addition']){
								continue;
							}
							if($data['audit_type'] == $modelApplication->arrEnumAuditType['process_addition']){
								if(!in_array($unit->id, $processunitIds)){
									continue;
								}
							}
							if($renewal_audit){
								$unitstandard_id = [];
								$unitappstandard=$unit->unitappstandard;
								if(count($unitappstandard)>0)
								{
									foreach($unitappstandard as $unitstd)
									{
										$unitstandard_id[] = $unitstd->standard_id;
									}
								}
								$commonStd = array_intersect($uniqueunitAdditionStdIds, $unitstandard_id);
								if(count($commonStd)<=0){
									continue;
								}
							}else{
								$commonStd = $uniqueunitAdditionStdIds;
							}
							$appunitmodel=new ApplicationUnit();
							$appunitmodel->app_id=$appID;
							$appunitmodel->unit_type=$unit->unit_type;
							$appunitmodel->code=$unit->code;
							
							if($unit->unit_type==1 && $model->currentaddress !== null){
								$appunitmodel->name=$model->currentaddress->unit_name;
								$appunitmodel->address=$model->currentaddress->unit_address;
								$appunitmodel->zipcode=$model->currentaddress->unit_zipcode;
								$appunitmodel->city=$model->currentaddress->unit_city;
								$appunitmodel->state_id=$model->currentaddress->unit_state_id;
								$appunitmodel->country_id=$model->currentaddress->unit_country_id;
							}else{
								$appunitmodel->name=$unit->name;
								$appunitmodel->address=$unit->address;
								$appunitmodel->zipcode=$unit->zipcode;
								$appunitmodel->city=$unit->city;
								$appunitmodel->state_id=$unit->state_id;
								$appunitmodel->country_id=$unit->country_id;
							}
							
							$appunitmodel->no_of_employees=$unit->no_of_employees;
							if($appunitmodel->save())
							{
								$unitID = $appunitmodel->id;
								
								$arrUnitIDs[$unit->id]=$unitID;
								
								$unitprd=$unit->unitproduct;
								if(count($unitprd)>0)
								{
									$unitprdidsarr=array();										
									foreach($unitprd as $unitP)
									{
										if(!isset($productstandardarr[$unitP->application_product_standard_id])){
											continue;
										}
										$appunitproductmodel=new ApplicationUnitProduct();
										$appunitproductmodel->unit_id=$unitID;
										$appunitproductmodel->application_product_standard_id=$productstandardarr[$unitP->application_product_standard_id];
										$appunitproductmodel->save();										
									}										
								}	
								
								//standards									
								$unitappstandard=$unit->unitappstandard;
								if(count($unitappstandard)>0)
								{
									foreach($unitappstandard as $unitstd)
									{
										if(!in_array($unitstd->standard_id,$activeStandards)){
											continue;
										}

										if($renewal_audit){
											if(!in_array($unitstd->standard_id,$uniqueunitAdditionStdIds)){
												continue;
											}
										}
										$appunitstandardmodel=new ApplicationUnitStandard();
										$appunitstandardmodel->unit_id=$unitID;
										$appunitstandardmodel->standard_id=$unitstd->standard_id;
										$appunitstandardmodel->save();										
									}
								}									

								$unitbsector=$unit->unitbusinesssector;
								if(count($unitbsector)>0)
								{									
									foreach($unitbsector as $unitbs)
									{
										//For change of address error 
										if(count($commonStd)>0){
											$business_sector_id = $unitbs->business_sector_id;
											$chkBusiness = ['business_sector_id'=>$business_sector_id,'standard_id'=>$commonStd];
											$relatedsector = Yii::$app->globalfuns->checkBusinessSectorInStandard($chkBusiness);
											if(!$relatedsector){
												continue;
											}
										}

										
										$appunitbsectorsmodel=new ApplicationUnitBusinessSector();
										$appunitbsectorsmodel->unit_id=$unitID;
										$appunitbsectorsmodel->business_sector_id=$unitbs->business_sector_id;
										$appunitbsectorsmodel->business_sector_name=$unitbs->business_sector_name;
										$appunitbsectorsmodel->save(); 





										if($renewal_audit || $data['audit_type'] == $this->arrEnumAuditType['process_addition'] || $data['audit_type'] == $this->arrEnumAuditType['renewal']){
											$unitbsectorgp=$unitbs->unitbusinesssectorgroup;
											if(count($unitbsectorgp)>0)
											{									
												foreach($unitbsectorgp as $unitbsgp)
												{
													//if($renewal_audit){
													if(count($commonStd)>0){
														$business_sector_group_id = $unitbsgp->business_sector_group_id;
														$chkBusiness = ['business_sector_group_id'=>$business_sector_group_id,'standard_id'=>$commonStd];
														$relatedsector = Yii::$app->globalfuns->checkBusinessSectorGroupInStandard($chkBusiness);
														if(!$relatedsector){
															continue;
														}
													}
													//}

													$appunitbsectorsgpmodel=new ApplicationUnitBusinessSectorGroup();
													$appunitbsectorsgpmodel->unit_id=$unitID;
													$appunitbsectorsgpmodel->unit_business_sector_id=$appunitbsectorsmodel->id;
													$appunitbsectorsgpmodel->business_sector_group_id=$unitbsgp->business_sector_group_id;
													$appunitbsectorsgpmodel->business_sector_group_name=$unitbsgp->business_sector_group_name;
													$appunitbsectorsgpmodel->standard_id=$unitbsgp->standard_id;
													$appunitbsectorsgpmodel->save(); 
												}
											}
										}



									}
								}


								
								
								
								$unitprocess=$unit->unitprocessall;
								if(count($unitprocess)>0)
								{										
									foreach($unitprocess as $unitPcs)
									{
										/*if($renewal_audit){
											if(!in_array($unitPcs->standard_id,$uniqueunitAdditionStdIds)){
												continue;
											}
										}*/
										if(count($uniqueunitAdditionStdIds)>0){
											if(!in_array($unitPcs->standard_id,$uniqueunitAdditionStdIds)){
												continue;
											}
										}
										

										$chkApplicationUnitProcess = ApplicationUnitProcess::find()->where(['unit_id'=>$unitID,'process_id'=>$unitPcs->process_id,'standard_id'=>$unitPcs->standard_id])->one();
										if($chkApplicationUnitProcess === null){
											$appunitprocessesmodel=new ApplicationUnitProcess();
											$appunitprocessesmodel->unit_id=$unitID;
											$appunitprocessesmodel->process_id=$unitPcs->process_id;
											$appunitprocessesmodel->process_name = $unitPcs->process_name;
											$appunitprocessesmodel->standard_id=$unitPcs->standard_id;
											$appunitprocessesmodel->process_type=0;
											$appunitprocessesmodel->save(); 
										}
																				
									}									
								}						
																	
								$unitstd=$unit->unitstandard;									
								if(count($unitstd)>0)
								{										
									foreach($unitstd as $unitS)
									{
										$appunitcertifiedstdmodel=new ApplicationUnitCertifiedStandard();
										$appunitcertifiedstdmodel->unit_id=$unitID;
										$appunitcertifiedstdmodel->standard_id=$unitS->standard_id;

										$appunitcertifiedstdmodel->license_number=$unitS->license_number;
										$appunitcertifiedstdmodel->expiry_date=$unitS->expiry_date;
										if($appunitcertifiedstdmodel->save())
										{
											$standardfile=$unitS->unitstandardfile;
											if(count($standardfile)>0)
											{												
												foreach($standardfile as $stdfile)
												{
													$filename=Yii::$app->globalfuns->copyFiles($stdfile->file,$target_dir);
													
													$appunitcertifiedstdfilemodel=new ApplicationUnitCertifiedStandardFile();
													$appunitcertifiedstdfilemodel->unit_certified_standard_id=$appunitcertifiedstdmodel->id;
													$appunitcertifiedstdfilemodel->file=$filename;
													$appunitcertifiedstdfilemodel->type=$stdfile->type;
													$appunitcertifiedstdfilemodel->save(); 
												}												
											}
										}										
									}										
								}									
							}							
						}							
					}
					
					//If audit type is renewal,the checklist comment and review details will store in database from parent application					
					if($data['audit_type']==$modelApplication->arrEnumAuditType['renewal'])
					{		

						$ApplicationChangeAddressCurrent = ApplicationChangeAddress::find()->where(['parent_app_id'=>$model->id])->orderBy(['id' => SORT_DESC])->one();
						if($ApplicationChangeAddressCurrent !== null){
							$ApplicationChangeAddress = new ApplicationChangeAddress();
							$ApplicationChangeAddress->parent_app_id = $modelApplication->id;
							$ApplicationChangeAddress->current_app_id = $modelApplication->id;
							$ApplicationChangeAddress->company_name= $ApplicationChangeAddressCurrent->company_name;
							$ApplicationChangeAddress->address=$ApplicationChangeAddressCurrent->address;
							$ApplicationChangeAddress->zipcode=$ApplicationChangeAddressCurrent->zipcode;
							$ApplicationChangeAddress->city=$ApplicationChangeAddressCurrent->city;
							$ApplicationChangeAddress->state_id=$ApplicationChangeAddressCurrent->state_id;
							$ApplicationChangeAddress->country_id=$ApplicationChangeAddressCurrent->country_id;
							$ApplicationChangeAddress->salutation=$ApplicationChangeAddressCurrent->salutation;
							$ApplicationChangeAddress->title=$ApplicationChangeAddressCurrent->title;
							$ApplicationChangeAddress->first_name=$ApplicationChangeAddressCurrent->first_name;
							$ApplicationChangeAddress->last_name=$ApplicationChangeAddressCurrent->last_name;
							$ApplicationChangeAddress->job_title=$ApplicationChangeAddressCurrent->job_title;
							$ApplicationChangeAddress->telephone=$ApplicationChangeAddressCurrent->telephone;
							$ApplicationChangeAddress->email_address=$ApplicationChangeAddressCurrent->email_address;
							$ApplicationChangeAddress->save();

							$modelApplication->address_id = $ApplicationChangeAddress->id;
							$modelApplication->save();
						}					
						
						

						
						$applicationchecklistcmt=[];
						$appchecklistcmt=$model->applicationchecklistcmt;
						if(count($appchecklistcmt)>0)
						{
							$checklistcmtarr=[];
							foreach($appchecklistcmt as $checklistcmt)
							{
								$filename='';
								if($checklistcmt->document!='')
								{
									$filename=Yii::$app->globalfuns->copyFiles($checklistcmt->document,$target_dir_checklist);
								}
								
								$checklistmodel=new ApplicationChecklistComment();
								$checklistmodel->app_id=$appID;
								$checklistmodel->question_id=$checklistcmt->question_id;
								$checklistmodel->question=$checklistcmt->question;
								$checklistmodel->answer=$checklistcmt->answer;
								$checklistmodel->comment=$checklistcmt->comment;
								$checklistmodel->document=$filename;								
								$checklistmodel->save(); 							
							}							
						}
						
						
						$applicationreview=$model->applicationreview;
						if(count($applicationreview)>0)
						{
							foreach($applicationreview as $applicationrw)
							{
								$applicationreviewmodel=new ApplicationReview();
								$applicationreviewmodel->app_id=$appID;
								$applicationreviewmodel->user_id=$applicationrw->user_id;
								$applicationreviewmodel->comment=$applicationrw->comment;
								$applicationreviewmodel->answer=$applicationrw->answer;
								$applicationreviewmodel->status=$applicationrw->status;
								$applicationreviewmodel->review_result=$applicationrw->review_result;						
								if($applicationreviewmodel->save())
								{
									$applicationReviewID=$applicationreviewmodel->id;
									
									$applicationreviewcomment=$applicationrw->applicationreviewcomment;
									if(count($applicationreviewcomment)>0)
									{
										foreach($applicationreviewcomment as $applicationreviewcmt)
										{
											$applicationreviewcommentmodel=new ApplicationReviewComment();
											$applicationreviewcommentmodel->review_id=$applicationReviewID;
											$applicationreviewcommentmodel->question_id=$applicationreviewcmt->question_id;
											$applicationreviewcommentmodel->question=$applicationreviewcmt->question;
											$applicationreviewcommentmodel->answer=$applicationreviewcmt->answer;
											$applicationreviewcommentmodel->comment=$applicationreviewcmt->comment;											
											$applicationreviewcommentmodel->save();										
										}	
									}
									
									$applicationunitreviewcomment=$applicationrw->applicationunitreviewcomment;
									if(count($applicationunitreviewcomment)>0)
									{
										foreach($applicationunitreviewcomment as $applicationunitreviewcmt)
										{
											$applicationunitreviewcommentmodel=new ApplicationUnitReviewComment();
											$applicationunitreviewcommentmodel->review_id=$applicationReviewID;
											$applicationunitreviewcommentmodel->question_id=$applicationunitreviewcmt->question_id;
											$applicationunitreviewcommentmodel->question=$applicationunitreviewcmt->question;
											$applicationunitreviewcommentmodel->answer=$applicationunitreviewcmt->answer;
											$applicationunitreviewcommentmodel->comment=$applicationunitreviewcmt->comment;											
											$applicationunitreviewcommentmodel->save();											
										}	
									}									
								}
							}
						}	
					}		
				}
			}		
		}
		return ['units'=>$arrUnitIDs,'new_app_id'=>$appID,'productstandardarr'=>$productstandardarr];
	}
	
	public function getCompanyname()
	{
		$company_name = '';
		//$obj = $this->hasOne(ApplicationChangeAddress::className(), ['id' => 'address_id']);
		//if($obj !== null){
			//$company_name = $obj->company_name;
		//}
		if($this->applicationaddress!==null)
		{
			$company_name=$this->applicationaddress->company_name;		
		}
		return $company_name;
	}
	
	public function getContactname()
	{
		$Contactname='';
		if($this->applicationaddress!==null)
		{
			$Contactname=$this->applicationaddress->first_name." ".$this->applicationaddress->last_name;
		}
		return $Contactname;
	}
	
	public function getFirstname()
	{
		$first_name='';
		if($this->applicationaddress!==null)
		{
			$first_name=$this->applicationaddress->first_name;
		}
		return $first_name;
	}
	
	public function getLastname()
	{
		$last_name='';
		if($this->applicationaddress!==null)
		{
			$last_name=$this->applicationaddress->last_name;
		}
		return $last_name;
	}
	
	public function getAddress()
	{
		$address='';
		if($this->applicationaddress!==null)
		{
			$address=$this->applicationaddress->address;
		}	
		return $address;
	}
	
	public function getCity()
	{
		$city='';
		if($this->applicationaddress!==null)
		{
			$city=$this->applicationaddress->city;
		}
		return $city;
	}
	
	public function getZipcode()
	{
		$zipcode='';
		if($this->applicationaddress!==null)
		{
			$zipcode=$this->applicationaddress->zipcode;
		}
		return $zipcode;
	}
	
	public function getCountryname()
	{
		$country='';
		if($this->applicationaddress!==null)
		{
			$country=$this->applicationaddress->country->name;
		}
		return $country;
	}
	
	public function getStatename()
	{
		$state='';
		if($this->applicationaddress!==null)
		{
			$state=$this->applicationaddress->state->name;
		}
		return $state;
	}
	
	public function getSalutation()
	{
		$salutation='';
		if($this->applicationaddress!==null)
		{
			$salutation=$this->applicationaddress->salutation;
		}
		return $salutation;
	}
	
	public function getTitle()
	{
		$title='';
		if($this->applicationaddress!==null)
		{
			$title=$this->applicationaddress->title;
		}
		return $title;
	}
	
	public function getJobtitle()
	{
		$job_title='';
		if($this->applicationaddress!==null)
		{
			$job_title=$this->applicationaddress->job_title;
		}
		return $job_title;
	}
	
	public function getTelephone()
	{
		$telephone='';
		if($this->applicationaddress!==null)
		{
			$telephone=$this->applicationaddress->telephone;
		}
		return $telephone;
	}
	
	public function getEmailaddress()
	{
		$email_address='';
		if($this->applicationaddress!==null)
		{
			$email_address= $this->applicationaddress->email_address;
		}
		return $email_address;
	}	

	// Scope holder related changes	
	public function getCurrentscopeholdername()
	{
		$unit_name='';
		if($this->currentaddress!==null)
		{
			$unit_name=$this->currentaddress->unit_name;
		}
		return $unit_name;
	}
	
	public function getCurrentscopeholderaddress()
	{
		$address='';
		if($this->currentaddress!==null)
		{
			$address=$this->currentaddress->unit_address;
		}	
		return $address;
	}
	
	public function getCurrentscopeholdercity()
	{
		$city='';
		if($this->currentaddress!==null)
		{
			$city=$this->currentaddress->unit_city;
		}
		return $city;
	}
	
	public function getCurrentscopeholderzipcode()
	{
		$zipcode='';
		if($this->currentaddress!==null)
		{
			$zipcode=$this->currentaddress->unit_zipcode;
		}
		return $zipcode;
	}
	
	public function getCurrentscopeholdercountryname()
	{
		$country='';
		if($this->currentaddress!==null)
		{
			$country=$this->currentaddress->unitcountry?$this->currentaddress->unitcountry->name:'';
		}
		return $country;
	}
	
	public function getCurrentscopeholderstatename()
	{
		$state='';
		if($this->currentaddress!==null)
		{
			$state=$this->currentaddress->unitstate?$this->currentaddress->unitstate->name:'';
		}
		return $state;
	}
	
	
	public function getScopeholdername()
	{
		$unit_name='';
		if($this->applicationaddress!==null)
		{
			$unit_name=$this->applicationaddress->unit_name;
		}
		return $unit_name;
	}
	
	public function getScopeholderaddress()
	{
		$address='';
		if($this->applicationaddress!==null)
		{
			$address=$this->applicationaddress->unit_address;
		}	
		return $address;
	}
	
	public function getScopeholdercity()
	{
		$city='';
		if($this->applicationaddress!==null)
		{
			$city=$this->applicationaddress->unit_city;
		}
		return $city;
	}
	
	public function getScopeholderzipcode()
	{
		$zipcode='';
		if($this->applicationaddress!==null)
		{
			$zipcode=$this->applicationaddress->unit_zipcode;
		}
		return $zipcode;
	}
	
	public function getScopeholdercountryname()
	{
		$country='';
		if($this->applicationaddress!==null)
		{
			$country=$this->applicationaddress->unitcountry->name;
		}
		return $country;
	}
	
	public function getScopeholderstatename()
	{
		$state='';
		if($this->applicationaddress!==null)
		{
			$state=$this->applicationaddress->unitstate->name;
		}
		return $state;
	}
	
}   

