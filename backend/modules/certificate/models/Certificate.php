<?php

namespace app\modules\certificate\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use app\modules\master\models\User;
use app\modules\master\models\Standard;
use app\modules\master\models\ReductionStandard;
use app\modules\master\models\ProductTypeMaterialComposition;

use app\modules\application\models\ApplicationUnitCertifiedStandard;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationUnitStandard;
use app\modules\application\models\ApplicationCertifiedByOtherCB;
use app\modules\application\models\ApplicationProductCertificateTemp;

use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditReviewerRiskCategory;

use app\modules\changescope\models\ProductAddition;
use app\modules\changescope\models\Withdraw;

/**
 * This is the model class for table "tbl_certificate".
 *
 * @property int $id
 * @property int $audit_id
 * @property int $status 0=Open,1=Certification In-Process,2=Certificate Generated,3=Declined
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class Certificate extends \yii\db\ActiveRecord
{
	public $arrStatus=array('0'=>'Open','1'=>'Certification In-Process','2'=>'Certified','3'=>'Declined','4'=>'Suspension','5'=>'Cancellation','6'=>'Withdrawn','7'=>'Extension','8'=>'Certificate Reinstate','9'=>'Certified by Other CB yet to be expired','10'=>'Expired');
    public $arrEnumStatus=array('open'=>'0','certification_in_process'=>'1','certificate_generated'=>'2','declined'=>'3','suspension'=>'4','cancellation'=>'5','withdrawn'=>'6','extension'=>'7','certificate_reinstate'=>'8','certified_by_other_cb'=>'9','expired'=>'10');    
    public $arrStatusColor=array('0'=>'#4572A7','1'=>"#DB843D",'2'=>'#5f79fa','3'=>'#ff0000','4'=>'#4572A7','5'=>'#DB843D','6'=>'#5f79fa','7'=>'#457222','8'=>'#eeeeee','9'=>'#DB843D','10'=>'#DB843D');
    
    public $arrCertificateStatus=array('0'=>'Valid','1'=>'In-Valid');
	public $arrEnumCertificateStatus=array('valid'=>'0','invalid'=>'1');
	public $arrCertificateStatusForList=array('0'=>'Yes','1'=>'No');
	
	public $arrType=array('1'=>'Normal','2'=>'Renewal','3'=>'Process Addition','4'=>'Standard Addition','5'=>'Unit Addition','6'=>'Change of Address','7'=>'Withdraw of Unit','8'=>'Product Addition');
	public $arrEnumType=array('normal'=>'1','renewal'=>'2','process_addition'=>'3','standard_addition'=>'4','unit_addition'=>'5','change_of_address'=>'6','withdraw_unit'=>'7','product_addition'=>'8');
	public $due_days; 
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_certificate';
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
            [['audit_id', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_id' => 'Audit ID',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
	
	public function getCertificatebyotherbody()
    {
        return $this->hasOne(ApplicationCertifiedByOtherCB::className(), ['app_id' => 'parent_app_id','standard_id'=>'standard_id']);
	}
	
	public function getCertificatefiles()
    {
        return $this->hasMany(CertificateFiles::className(), ['certificate_id' => 'id']);
    }
	
	public function getAudit()
    {
        return $this->hasOne(Audit::className(), ['id' => 'audit_id']);
    }

    public function getCertificatereview()
    {
        return $this->hasOne(CertificateReviewerReview::className(), ['certificate_id' => 'id']);
    }
	
	public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getExtensionby()
    {
        return $this->hasOne(User::className(), ['id' => 'extension_by']);
    }
	
	public function getGeneratedby()
    {
        return $this->hasOne(User::className(), ['id' => 'certificate_generated_by']);
    }	
	
	public function getReviewer()
    {
        return $this->hasOne(CertificateReviewer::className(), ['certificate_id' => 'id'])->andOnCondition(['reviewer_status' => 1]);
    }

    public function getReviewcertificate()
    {
        return $this->hasMany(CertificateStatusReview::className(), ['certificate_id' => 'id']);
    }

    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }

    public function getApplication()
    {
        return $this->hasOne(Application::className(), ['id' => 'parent_app_id']);
    }
	
	public function getProductaddition()
    {
        return $this->hasOne(ProductAddition::className(), ['id' => 'product_addition_id']);
    }
	
	public function getWithdraw()
    {
        return $this->hasOne(Withdraw::className(), ['id' => 'withdraw_id']);
	}

	public function getRiskcategory()
    {
        return $this->hasOne(AuditReviewerRiskCategory::className(), ['id' => 'risk_category']);
	}

	
	
	public function sortByOrder($a, $b) {
		return $a['material_percentage'] - $b['material_percentage'];
	}
	
	public function generateCertificate($certificateID,$returnType=false)
	{
		$certificatemodel = new Certificate();
		$applicationmodel = new Application();
		
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		
		$certificateDraftNo=0;
		$model = Certificate::find()->where(['id' => $certificateID])->one();
		if($model !== null)
		{	
			$certificateDraftNo=$model->version;
			$application = $model->audit->application;
			$audit_type = $application->audit_type;
			
			$getCertifiedDateModel = Certificate::find()->where(['parent_app_id' => $model->parent_app_id,'standard_id'=>$model->standard_id,'certificate_status'=>0,'status'=>array($certificatemodel->arrEnumStatus['certificate_generated'],$certificatemodel->arrEnumStatus['extension'])])->orderBy(['id' => SORT_DESC])->one();
			if(!$returnType)
			{
				$certificate_generate_date = date("Y-m-d",time());							
				if($getCertifiedDateModel !== null)
				{
					$certificate_generate_date = date('d F Y',strtotime($certificate_generate_date));
					$certificate_expiry_date = date('d F Y', strtotime($getCertifiedDateModel->certificate_valid_until));
					$certificateDraftNo = $getCertifiedDateModel->version+1;
				}else{
					$futureDate = date('Y-m-d', strtotime('+1 year', strtotime($certificate_generate_date)) );
					$certificate_generate_date = date('d F Y',strtotime($certificate_generate_date));					
					$certificate_expiry_date = date('d F Y', strtotime('-1 day', strtotime($futureDate)));						
				}	
			}else{				
				$certificate_generate_date = date('d F Y', strtotime($model->certificate_generated_date));
				$certificate_expiry_date = date('d F Y', strtotime($model->certificate_valid_until));
			}

			if($certificateDraftNo==0)
			{
				$certificateDraftNo=1;
			}
				
			//$applicationID = $model->audit->application->id;
			$applicationID = $model->application->id;
			
			//Code to remove product unselected by the reviewer starts
			$removeproductIds = [];
			$ApplicationProductCertificateTemp = ApplicationProductCertificateTemp::find()->where(['app_id'=>$applicationID,'certificate_id'=>$certificateID])->all();
			if(count($ApplicationProductCertificateTemp) >0){
				foreach($ApplicationProductCertificateTemp as $tempproduct){
					$removeproductIds[] = $tempproduct->product_id;
				}
			}
			$removeproductCondition = '';
			if(count($removeproductIds)>0){
				$removeproductCondition = ' AND ap.id not in ('.implode(',',$removeproductIds).') ';
			}
			//Code to remove product unselected by the reviewer ends


			$ospnumber = $model->audit->application->franchise->usercompanyinfo->osp_number;
			$customeroffernumber = $model->audit->application->customer->customer_number;
			$usercompanyinfoObj = $model->audit->application->customer->usercompanyinfo;
			/*
			$applicationObj = $model->audit->application->applicationscopeholder;
			echo $applicationObj->id;
			die();
			
			// ------- Get Company Name and Company Address Code Start Here ----------						
			$companyName = $applicationObj->name;														
			$companyAddress = trim($applicationObj->address).', ';						
			$companyAddress.= $applicationObj->city.', '.$applicationObj->zipcode.', ';
			$companyAddress.= $applicationObj->state->name.', '.$applicationObj->country->name;
			// ------- Get Company Name and Company Address Code Start Here ----------
			*/
			
			// ----------- Getting the company name latest code start here  ----------------------
			$companyName='';
			$companyAddress='';			
			$applicationModelObject = $model->application->currentaddress;
			$companyName = $applicationModelObject->company_name;														
			$companyAddress = trim($applicationModelObject->address).', ';						
			$companyAddress.= $applicationModelObject->city.', '.$applicationModelObject->zipcode.', ';
			$companyAddress.= $applicationModelObject->state->name.', '.$applicationModelObject->country->name;
			
			/*
			if($applicationModelObject!==null)
			{
				$companyName = $applicationModelObject->company_name;														
				$companyAddress = trim($applicationModelObject->address).', ';						
				$companyAddress.= $applicationModelObject->city.', '.$applicationModelObject->zipcode.', ';
				$companyAddress.= $applicationModelObject->state->name.', '.$applicationModelObject->country->name;			
			}else{			
				$companyName = $model->application->company_name;														
				$companyAddress = trim($model->application->address).', ';						
				$companyAddress.= $model->application->city.', '.$model->application->zipcode.', ';
				$companyAddress.= $model->application->state->name.', '.$model->application->country->name;
				
			}	
			*/
			// ----------- Getting the company name latest code end here  ----------------------	
				
			$standardName = '';
			$standardCode = '';
			$standardScode = '';				
			$date_format = Yii::$app->globalfuns->getSettings('date_format');

			$productTypeMaterialComposition = new ProductTypeMaterialComposition(); 
			$modelApplicationStandard = new ApplicationStandard();			
			$standard_id = $model->standard_id;
			$app_id = $model->parent_app_id;
			$appstandard = ApplicationStandard::find()->where(['app_id'=>$app_id,'standard_id'=>$standard_id,'standard_status'=>array($modelApplicationStandard->arrEnumStatus['valid'],$modelApplicationStandard->arrEnumStatus['draft_certificate'])])->one();
			if($appstandard !==null)
			{
				$standard_id = $appstandard->standard->id;
				$standardName = $appstandard->standard->name.' ('.$appstandard->standard->code.')';
				$standardVersion = $appstandard->version;
				$standardCode = $appstandard->standard->code;
				$standardScode = $appstandard->standard->short_code;
				
				$RegistrationNo = "GCL-".$ospnumber.$standardScode.$customeroffernumber;
				$LicenseNo="GCL-".$customeroffernumber;
				//$certificateNumber = "GCL-".$customeroffernumber."/".$standardCode."-".date("Y")."/".$certificateDraftNo;
				$certificateNumber = $RegistrationNo."/".$standardCode."-".date("Y")."-".$certificateDraftNo;
				
				$model->code=$RegistrationNo;
				
				/*
				$productsQry = 'SELECT prd.name AS product,prdtype.name AS product_type,GROUP_CONCAT(DISTINCT apm.percentage, \'% \', ptm.`name` SEPARATOR \' + \') AS material_composition
				 ,GROUP_CONCAT(DISTINCT apm.percentage, \'@@\', ptm.`name`, \'@@\', apm.material_type_id SEPARATOR \'$$\') AS material_composition_comb ,slg.name 
				 AS product_code,slg.id as product_label_grade_id  FROM `tbl_application_product` AS ap
				INNER JOIN `tbl_application_product_material` AS apm ON apm.app_product_id = ap.id AND ap.app_id='.$applicationID.' '.$removeproductCondition.' 
				INNER JOIN `tbl_application_product_standard` AS aps
				 ON aps.application_product_id = ap.id AND aps.standard_id='.$standard_id.' AND aps.product_standard_status=0 
				INNER JOIN `tbl_product` AS prd ON prd.id = ap.product_id
				INNER JOIN `tbl_product_type` AS prdtype ON prdtype.id=ap.product_type_id
				INNER JOIN `tbl_product_type_material` AS ptm ON ptm.id=apm.material_id
				INNER JOIN `tbl_standard_label_grade` AS slg ON slg.id=aps.label_grade_id
				GROUP BY apm.app_product_id,ap.id';
				*/
				$productsQry = 'SELECT ap.product_name AS product,prd.code AS prod_code,prdtype.code AS prod_type_code,ap.product_type_name AS product_type,GROUP_CONCAT(DISTINCT apm.percentage, \'% \', ptm.`name` SEPARATOR \' + \') AS material_composition
				 ,GROUP_CONCAT(DISTINCT apm.percentage, \'@@\', apm.`material_name`, \'@@\', apm.material_type_id, \'@@\', ptm.code SEPARATOR \'$$\') AS material_composition_comb ,slg.name 
				 AS product_code,slg.id as product_label_grade_id  FROM `tbl_application_product` AS ap
				INNER JOIN `tbl_application_product_material` AS apm ON apm.app_product_id = ap.id AND ap.app_id='.$applicationID.' '.$removeproductCondition.' 
				INNER JOIN `tbl_application_product_standard` AS aps
				 ON aps.application_product_id = ap.id AND aps.standard_id='.$standard_id.' AND aps.product_standard_status=0 
				INNER JOIN `tbl_product` AS prd ON prd.id = ap.product_id
				INNER JOIN `tbl_product_type` AS prdtype ON prdtype.id=ap.product_type_id
				INNER JOIN `tbl_product_type_material` AS ptm ON ptm.id=apm.material_id
				INNER JOIN `tbl_standard_label_grade` AS slg ON slg.id=aps.label_grade_id
				GROUP BY apm.app_product_id,ap.id';
				// material_composition product_code
												
				$command = $connection->createCommand($productsQry);
				$connection->createCommand("SET SESSION group_concat_max_len = 1000000;")->execute();
				$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();
				$result = $command->queryAll();
				
				$labelGradeCnt=1;
				$arrLabelGrade=array();
				$arrCertificateCoveredProducts=array();
				$arrProductCategories=array();
				$productContent='';
				if(count($result)>0)
				{
					foreach($result as $vals)
					{						
						$arrOrganicMaterial=array();						
						$arrOtherMaterial=array();						
						$material_composition_comb = $vals['material_composition_comb'];
						$material_composition_comb_array = explode('$$',$material_composition_comb);
						if(is_array($material_composition_comb_array) && count($material_composition_comb_array)>0)
						{
							$organic_material_array_key=0;
							$other_material_array_key=0;
							
							foreach($material_composition_comb_array as $mc)
							{
								$material_composition_array = explode('@@',$mc);
								if(is_array($material_composition_array) && count($material_composition_array)>0)
								{
									$material_composition_percentage = isset($material_composition_array[0])?$material_composition_array[0]:'';
									$material_composition_name = isset($material_composition_array[1])?$material_composition_array[1]:'';
									$material_composition_type_id = isset($material_composition_array[2])?$material_composition_array[2]:'';
									$material_composition_type_code = isset($material_composition_array[3])?$material_composition_array[3]:'';
									//if(stripos($material_composition_name, 'Organic') !== false)
									if($material_composition_type_id == $productTypeMaterialComposition->arrEnumMaterialType['certified'])		
									{
										$arrOrganicMaterial[$organic_material_array_key]=array('material_name'=>$material_composition_name,'material_percentage'=>$material_composition_percentage,'material_code'=>$material_composition_type_code);
										$organic_material_array_key++;									
									}else{	
									    $arrOtherMaterial[$other_material_array_key]=array('material_name'=>$material_composition_name,'material_percentage'=>$material_composition_percentage,'material_code'=>$material_composition_type_code);
										$other_material_array_key++;					
									}										
								}
							}
							
							//print_r($arrOrganicMaterial);							
							$materialCompositionContent='';
							if(is_array($arrOrganicMaterial) && count($arrOrganicMaterial)>0)
							{
								//usort($arrOrganicMaterial, 'sortByOrder');
								usort($arrOrganicMaterial, function($a, $b) {
									return $b['material_percentage'] <=> $a['material_percentage'];
								});
								
								foreach($arrOrganicMaterial as $arrOM)
								{
									$materialCompositionContent.=$arrOM['material_percentage'].'% '.$arrOM['material_name'].' ('.$arrOM['material_code'].') + ';
								}
							}	
							
							//print_r($arrOtherMaterial);							
							if(is_array($arrOtherMaterial) && count($arrOtherMaterial)>0)
							{
								//usort($arrOtherMaterial, 'sortByOrder');
								usort($arrOtherMaterial, function($a, $b) {
									return $b['material_percentage'] <=> $a['material_percentage'];
								});
								
								foreach($arrOtherMaterial as $arrOM)
								{
									$materialCompositionContent.=$arrOM['material_percentage'].'% '.$arrOM['material_name'].' ('.$arrOM['material_code'].') + ';
								}
							}						
							$materialCompositionContent = rtrim($materialCompositionContent,' + ');							
						}
						
						
						
						$productContent.='<tr>
							<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px; " valign="middle">'.$vals['product'].' ('.$vals['prod_code'].')</td>
							<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px; " valign="middle">'.$vals['product_type'].' ('.$vals['prod_type_code'].')</td>
							<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px; " valign="middle">'.$materialCompositionContent.'</td>	
							<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px; " valign="middle">'.$vals['product_code'].'</td>		
						</tr>';				
						$stropen =' (';
						$strclose = ')';
						if (!in_array($vals['product'].$stropen.$vals['prod_code'].$strclose, $arrProductCategories))
						{
							$arrProductCategories[]=$vals['product'].$stropen.$vals['prod_code'].$strclose;
						}

						//$arrCertificateCoveredProducts[]=array('name_of_product'=>$vals['product_type'],'material_composition'=>$vals['material_composition'],'label_grade'=>$vals['product_code']);
						
						$arrLabelGrade[$labelGradeCnt]=$vals['product_code'];
						$labelGradeCnt++;
					}
				}					
												
				$arrCertificateLogo=array();
				$certificationStd='';
				$standard_code_lower = strtolower($standardCode);
				if($standard_code_lower=='gots'){
					$certificationStd=$standard_code_lower;
					$arrCertificateLogo[]=$standard_code_lower.'_logo.png';
				//}elseif($standard_code_lower=='ocs'){
					//$certificationStd=$standard_code_lower;
				}elseif($standard_code_lower=='grs'){
					$certificationStd=$standard_code_lower;
					$arrCertificateLogo[]=$standard_code_lower.'_logo.png';
				//}elseif($standard_code_lower=='rcs'){
					//$certificationStd=$standard_code_lower;
				}elseif($standard_code_lower=='ccs'){
					$certificationStd=$standard_code_lower;
					$arrCertificateLogo[]=$standard_code_lower.'_logo.png';
				}elseif($standard_code_lower=='rds' || $standard_code_lower=='rws' || $standard_code_lower=='rms'){
					$certificationStd=$standard_code_lower;
					$arrCertificateLogo[]=$standard_code_lower.'_logo.png';				
				}
				
				
				
				if(is_array($arrLabelGrade) && count($arrLabelGrade)>0)
				{
					$resArray = array_filter($arrLabelGrade, function($value) {
						return strpos($value, 'blended') !== false || strpos(strtolower($value), 'bl') !== false;
					}); 										
					if(is_array($resArray) && count($resArray)>0)
					{
						$arrCertificateLogo[]=$standard_code_lower.'_blended_logo.png';
					}
					
					$resArray = array_filter($arrLabelGrade, function($value) {
						return strpos($value, '100') !== false;
					}); 										
					if(is_array($resArray) && count($resArray)>0)
					{
						$arrCertificateLogo[]=$standard_code_lower.'_100_logo.png';
					}
				}
				
				$arrProcess = array();
				$arrUnitWiseProcess = array();
				$arrUnitType=array('1'=>'Scope Holder','2'=>'Facility','3'=>'Sub Contractor');
				foreach($arrUnitType as $unitTypeKey=>$unitTypeVal)
				{
					$processQry = 'SELECT appunit.id as unit_id,prs.name as process_name,prs.code as process_code,appunit.unit_type as unit_type FROM `tbl_application_unit` AS appunit
					INNER JOIN `tbl_application_unit_process` AS appunitprocess ON appunitprocess.unit_id=appunit.id AND appunitprocess.standard_id='.$standard_id.' AND appunit.status=0 and appunitprocess.unit_process_status=0 and appunit.app_id='.$applicationID.' AND appunit.unit_type='.$unitTypeKey.'
					INNER JOIN `tbl_process` AS prs ON prs.id=appunitprocess.process_id ';
					$command = $connection->createCommand($processQry);
					$procesResult = $command->queryAll();			
					//group by appunitprocess.process_id
					if(count($procesResult)>0)
					{
						foreach($procesResult as $processRes)
						{
							//$arrProcess[$processRes['unit_type']][]=$processRes['process_name'];
							//$arrUnitWiseProcess[$processRes['unit_id']][]=$processRes['process_name'];

							if(!isset($arrProcess[$processRes['unit_type']]) || !in_array($processRes['process_name'],$arrProcess[$processRes['unit_type']])){
								$arrProcess[$processRes['unit_type']][]=$processRes['process_name'].$stropen.$processRes['process_code'].$strclose;
							}
							if(!isset($arrUnitWiseProcess[$processRes['unit_id']]) || !in_array($processRes['process_name'],$arrUnitWiseProcess[$processRes['unit_id']])){
								$arrUnitWiseProcess[$processRes['unit_id']][]=$processRes['process_name'].$stropen.$processRes['process_code'].$strclose;
							}
						}
					}
				}

				$subContractorORfacilityQry = 'SELECT appunit.unit_type as unit_type, appunit.id as unit_id,appunit.name as unit_name,appunit.address as unit_address,appunit.zipcode as unit_zipcode,appunit.city as unit_city,state.name as unit_state,country.name as unit_country FROM `tbl_application_unit` AS appunit
				INNER JOIN `tbl_application_unit_standard` AS appunitstd ON appunitstd.unit_id=appunit.id AND appunitstd.unit_standard_status=0 AND appunit.status=0 AND appunit.app_id='.$applicationID.' AND appunitstd.standard_id='.$standard_id.'
				INNER JOIN `tbl_state` AS state ON state.id=appunit.state_id
				INNER JOIN `tbl_country` AS country ON country.id=appunit.country_id
				GROUP BY appunit.id';	
				//AND appunit.unit_type!=1 
				$command = $connection->createCommand($subContractorORfacilityQry);
				$subContractResult = $command->queryAll();			
				
				$unitWiseSubContractorContent='';
				$unitWiseSubContractorContentSub = '';
				$unitWiseSubContractorContentCertified = '';
				$arrSubContractor=array();
				if(count($subContractResult)>0)
				{
					foreach($subContractResult as $subContractRes)
					{
						$subContractName = $subContractRes['unit_name'];
						$subContractAddress = $subContractRes['unit_address'].', ';
						$subContractAddress .= $subContractRes['unit_city'].', '.$subContractRes['unit_zipcode'].', ';
						$subContractAddress .= $subContractRes['unit_state'].', ';
						$subContractAddress .= $subContractRes['unit_country'];					
						$unit_type = $subContractRes['unit_type'];
						/*
						$arrSubContractor[]['unit_name']=$subContractRes['unit_name'];
						$arrSubContractor[]['unit_address']=$subContractAddress;
						if(array_key_exists($subContractRes['unit_id'], $arrUnitWiseProcess))
						{
							$arrSubContractor[]['unit_process']=$arrUnitWiseProcess[$subContractRes['unit_id']];
						}
						*/
						
						$unitProcess='NA';
						if(array_key_exists($subContractRes['unit_id'], $arrUnitWiseProcess))
						{
							$unitProcess=implode(', ',$arrUnitWiseProcess[$subContractRes['unit_id']]);
						}
						if($unit_type ==1 || $unit_type ==2){
							$typename ='Facility';
							if($unit_type ==1){
								$typename = 'Main';							
							}							

							$unitWiseSubContractorContent.='<tr>
								<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px; " valign="middle">'.$subContractName.'</td>
								<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px; " valign="middle">'.$subContractAddress.'</td>	
								<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px; " valign="middle">'.$unitProcess.'</td>
								<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px; " valign="middle">'.$typename.'</td>	
							</tr>';
						}
						
						if($unit_type ==3){
								//$standard_id
								$alreadyapplied = 0;
								$Standard = Standard::find()->where(['id'=>$standard_id])->one();
								if($Standard !==null){
									$standard_code = $Standard->code;
									$ReductionStandard = ReductionStandard::find()->where(['code'=>$standard_code])->one();
									if($ReductionStandard!== null){
										$ApplicationUnitCertifiedStandard = ApplicationUnitCertifiedStandard::find()->where(['standard_id'=>$ReductionStandard->id,'unit_id'=>$subContractRes['unit_id'] ])->one();
										if($ApplicationUnitCertifiedStandard !== null){
											$alreadyapplied = 1;
										}
									}
								}
								//ApplicationUnitCertifiedStandard::find()->where()->one();
								//
								if($alreadyapplied==0){
									$unitWiseSubContractorContentSub.='<tr>
										<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px; " valign="middle">'.$subContractName.'</td>
										<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px; " valign="middle">'.$subContractAddress.'</td>	
										<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px;" valign="middle">'.$unitProcess.'</td>	
									</tr>';
								}else{
									$expiry_date = '';
									if($ApplicationUnitCertifiedStandard->expiry_date != ''){
										$expiry_date = date($date_format,strtotime($ApplicationUnitCertifiedStandard->expiry_date));
									}
									
									$unitWiseSubContractorContentCertified.='<tr>
										<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px;" valign="middle">'.$ApplicationUnitCertifiedStandard->license_number.'</td>
										<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px;" valign="middle">'.$expiry_date.'</td>
										<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px;" valign="middle">'.$subContractName.'</td>
										<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px;" valign="middle">'.$subContractAddress.'</td>
										<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px;" valign="middle">'.$unitProcess.'</td>
										<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px;" valign="middle">-</td>
									</tr>';
								}
								

								
								
						}
						
						$arrSubContractor[]=array('name_of_operation'=>$subContractName,'address_of_operation'=>$subContractAddress,'processing_steps'=>$unitProcess);
							
					}
				}else{
					$unitWiseSubContractorContent.='<tr>
							<td colspan="2" style="text-align:center;padding:5px;" class="reportDetailLayoutInner">No Facility/Subcontractor found</td>
						</tr>';
				}
				
				
			
				/*
				//$data = Yii::$app->request->post();
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
				header('Access-Control-Max-Age: 1000');
				header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
				*/
				
				$draftText='';
				if($model->status==$model->arrEnumStatus['certification_in_process'])
				{
					$draftText='DRAFT';
				}
				
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
				header('Access-Control-Max-Age: 1000');
				header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

				$html='';
				$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8','format' => 'A4',
					'margin_left' => 10,
					'margin_right' => 10,
					'margin_top' => 5,
					'margin_bottom' => 40,
					'margin_header' => 0,
					'margin_footer' => 3,
					'setAutoTopMargin' => 'stretch',
					'autoMarginPadding' => 0
				
				]);
				//$mpdf->SetDisplayMode('fullwidth');
				
				$qrCodeURL=Yii::$app->params['certificate_file_download_path'].'scan-certificate?code='.md5($model->code);
				
				if($draftText!='')
				{
					$mpdf->SetWatermarkText('DRAFT');
					$mpdf->showWatermarkText = true;
					$qrCodeURL=Yii::$app->params['qrcode_scan_url_for_draft'];				
				}
				
				//$mpdf->Image(Yii::$app->params['image_files'].'gcl-bg.jpg', 0, 0, 210, 297, 'jpg', '', true, false);
				//$mpdf->SetWatermarkImage(Yii::$app->params['image_files'].'gcl-bg.jpg',0.3,'P','P');
				//$mpdf->showWatermarkImage = true;
				
				
				//$mpdf->SetDefaultBodyCSS('background', "url('".Yii::$app->params['image_files'].'gcl-bg.jpg'."')");
				//$mpdf->SetDefaultBodyCSS('background-image-resize', 6);

				$qr = Yii::$app->get('qr');
				//Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;			
				//$qrCodeContent=$qr->setText($qrCodeURL)->writeDataUri();
				$qrCodeContent=$qr->setText($qrCodeURL)			
				->setLogo(Yii::$app->params['image_files']."qr-code-logo.png")			
				->setLogoWidth(85)			
				->setEncoding('UTF-8')
				->writeDataUri();			
				
				$headerContent = '<div style="padding-top:15px;">
						<div style="width:80%;text-align: left;float:left;font-size:12px;">
							<img src="'.Yii::$app->params['image_files'].'header-img.png" border="0" style="width:136px;">						
						</div>
						<div style="width:20%;float:right;font-size:12px;font-family:Arial;">
							<img src="'.$qrCodeContent.'" style="width: 85px;margin-left: 45px;">
						</div>
					</div>';
				
				$signatureContent='<tr>
						<td style="text-align:left;font-size:14px;padding-top:5px;" valign="middle" class="reportDetailLayoutInner">Place and Date of Issue <br>London, '.$certificate_generate_date.'</td>
						<td style="text-align:center;font-size:14px;padding-top:5px;" valign="middle" class="reportDetailLayoutInner">Stamp of the Issuing Body</td>	
						<td style="text-align:center;font-size:14px;padding-top:5px;" valign="middle" class="reportDetailLayoutInner">'.$standardCode.' Logo</td>		
					</tr>
					<tr>
						<td style="text-align:left;font-size:14px;padding-top:5px;width:33%;" valign="middle" class="reportDetailLayoutInner">
							<img style="width:170px;" src="'.Yii::$app->params['image_files'].'certificate-sign.png" border="0">
							<p>Name of the authorized person:<br>Mahmut Sogukpinar, COO<br>GCL International Ltd</p>
						</td>
						<td style="text-align:center;font-size:14px;padding-top:5px;width:33%;" valign="middle" class="reportDetailLayoutInner">
							<img style="width:100px;" src="'.Yii::$app->params['image_files'].'gcl-stamp.png" border="0">
						</td>
						<td style="text-align:center;font-size:14px;padding-top:5px;width:33%;" valign="middle" class="reportDetailLayoutInner">';
						if(is_array($arrCertificateLogo) && count($arrCertificateLogo)>0)
						{
							foreach($arrCertificateLogo as $certLogo)
							{
								$logoWidth='width:100px;';
								if($standard_code_lower=='grs')
								{
									$logoWidth='width:170px;';
								}elseif($standard_code_lower=='ccs'){
									$logoWidth='width:190px;';
								}
								
								$signatureContent.='<img style="'.$logoWidth.'" src="'.Yii::$app->params['image_files'].''.$certLogo.'" border="0">';									
							}
						}						
				$signatureContent.='</td>						
				</tr>';
				
				$footerInnerContent1='This Certificate remains the property of GCL International Ltd. The Registration is subject to the Scheme Rules which are published at 
					www.gcl-intl.com. Any misuse, alteration, forgery or falsification is an unlawful act. Please validate the authenticity of this certificate by visiting <span style="text-decoration: underline;">www.gcl-intl.com</span>';
					
				$footerInnerContent2='<span style="font-weight:bold;font-size:12px;">GCL INTERNATIONAL LTD.</span><br>
					Level 1, Devonshire House, One Mayfair Place, London, W1 J 8AJ, United Kingdom.';
					
				$footerInnerContent3='<span style="font-size:11px;">Scope Certificate No.</span> <span style="font-weight:bold;">'.$certificateNumber.'</span> and License Number <span style="font-weight:bold;">'.$LicenseNo.', '.date('d F Y').'</span>, Page {PAGENO} of {nbpg}';
				
				$footerContent='<tr>
					<td style="text-align:left;font-size:12px;" valign="middle" class="reportDetailLayoutInner">
					'.$footerInnerContent1.'
					</td>
				</tr>
				<tr>	
					<td style="text-align:left;font-size:12px;padding-top:5px;" valign="middle" class="reportDetailLayoutInner">
					'.$footerInnerContent2.'
					</td>
				</tr>	
				
				<tr>		
					<td style="text-align:right;font-size:11px;" valign="middle" class="reportDetailLayoutInner">		
					'.$footerInnerContent3.'
					</td>
				</tr>';
				
				/*
				@page {
					background: url(\''.Yii::$app->params['image_files'].'gcl-bg.jpg'.'\') no-repeat 0 0;
					background-image-resize: 6;
				}
				*/
				
				$html='
				<style>
				table {
					border-collapse: collapse;
				}		
				div.chapter2 {
					page-break-before: right;
					page: chapter2;
					odd-header-name: html_secondpagesheader;
        			even-header-name: html_secondpagesheader;					
				}				
								
				div.chapter1 {
					page-break-before: always;
					page: chapter1;
					odd-header-name: html_lastpagesheader;
        			even-header-name: html_lastpagesheader;
				}
									
				
				@page {  
					header: html_otherpageheader;
					footer: html_otherpagesfooter;
					background: url('.Yii::$app->params["image_files"].'gcl-bg.jpg) repeat 0 0;
					background-image-resize: 0;
					margin-top: 3cm;
				}

				@page :first {    
					header: html_firstpageheader;
					footer: html_firstpagefooter;
					margin-top: 2cm;
				}								
				
				table, tbody, tr, th, td{
					background-color: rgba(0, 0, 0, 0.0) !important;
				}
				table.reportDetailLayout {
					border-collapse: collapse;
					width:100%;
					font-size:12px;
					font-family:Arial;
					text-align: left;
					margin-bottom:5px;
					margin-top:5px;
				}

				table.productDetails {
					border-collapse: collapse;
					border: 1px solid #000000;
					width:100%;
					font-size:12px;
					font-family:Arial;
					text-align: left;
					margin-bottom:5px;
					margin-top:5px;
				}

				td.productDetails {
					text-align: center;
					border: 1px solid #000000;
					font-size:12px;
					font-family:Arial;
					text-align: left;
					/*background-color:#DFE8F6;*/
					padding:3px;
				}

				td.reportDetailLayout {
					text-align: center;
					font-size:12px;
					font-family:Arial;
					text-align: left;
					/*background-color:#DFE8F6;*/
					padding:3px;
				}
				td.reportDetailLayoutHead {
					text-align: center;
					font-size:12px;
					font-family:Arial;
					text-align: left;
					/*background-color:#4e85c8;*/
					/* background-color:#006fc0;*/
					padding:3px;
					color:#FFFFFF;
				}

				td.reportDetailLayoutInner {
					text-align: center;
					font-size:12px;
					font-family:Arial;
					text-align: left;
					 
					padding:3px;
				}
				</style>
				
				<htmlpageheader name="firstpageheader" style="display:none">
					'.$headerContent.'	
				</htmlpageheader>

				<htmlpagefooter name="firstpagefooter" style="display:none">
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout">
						'.$footerContent.'															
					</table>
				</htmlpagefooter>

				<htmlpageheader name="otherpageheader" style="display:none">
					'.$headerContent.'
					<div style="margin-top:0px;">
						<table cellpadding="0" cellspacing="0" border="0" class="reportDetailLayout">						
						<tr><td>'.$companyName.'</td></tr>
						<tr><td>'.$standardName.'</td></tr>
						</table>
					</div>
					<table cellpadding="0" cellspacing="0" border="0" class="reportDetailLayout">						
						<tr><td><span style="font-weight:bold;text-align:left;font-size:13px;font-family:Arial;">Products Appendix to Certificate No.: '.$certificateNumber.'</span></td></tr>
						<tr><td style="padding-top:8px;"><span style="text-align:left;font-size:12px;font-family:Arial;">In specific the certificate covers the following products:</span></td></tr>
					</table>	
					
				</htmlpageheader>
				
				<htmlpageheader name="secondpagesheader" style="display:none;">
					'.$headerContent.'
					<div style="margin-top:0px;">
						<table cellpadding="0" cellspacing="0" border="0" class="reportDetailLayout">						
						<tr><td>'.$companyName.'</td></tr>
						<tr><td>'.$standardName.'</td></tr>
						</table>
					</div>					
				</htmlpageheader>
				
				<htmlpageheader name="lastpagesheader" style="display:none">
					'.$headerContent.'
					<div style="margin-top:0px;">
						<table cellpadding="0" cellspacing="0" border="0" class="reportDetailLayout">						
						<tr><td>'.$companyName.'</td></tr>
						<tr><td>'.$standardName.'</td></tr>
						</table>
					</div>					
				</htmlpageheader>
				
				<htmlpagefooter name="otherpagesfooter" style="display:none">
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout">						
						<tr>
							<td style="text-align:left;font-size:12px;" valign="middle" class="reportDetailLayoutInner">
							'.$footerInnerContent1.'
							</td>							
						</tr>
						<tr>	
							<td style="text-align:left;font-size:12px;padding-top:5px;" valign="middle" class="reportDetailLayoutInner">
							'.$footerInnerContent2.'
							</td>
						</tr>						
						<tr>		
							<td style="text-align:right;font-size:11px;" colspan="2" valign="middle" class="reportDetailLayoutInner">		
							'.$footerInnerContent3.'
							</td>
						</tr>						
					</table>					
				</htmlpagefooter>
				
				
				
				<htmlpagefooter name="secondpagesfooter" style="display:none">
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout">
						'.$footerContent.'															
					</table>
				</htmlpagefooter>
				
				<htmlpagefooter name="lastpagesfooter" style="display:none">
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout">
						'.$footerContent.'															
					</table>
				</htmlpagefooter>
				
				<htmlpagefooter name="innerpagesfooter" style="display:none">
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout">
						<tr>
							<td style="text-align:center;font-size:14px;padding-top:5px;width:33%;" valign="middle" class="reportDetailLayoutInner">
							<img style="width:100px;" src="'.Yii::$app->params['image_files'].'gcl-stamp.png" border="0">							
							</td>
						</tr>
					</table>					
				</htmlpagefooter>
				
											
					
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout">	
						<tr>
							<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">
							<span style="font-size:24px;">SCOPE CERTIFICATE</span><br>
							<span style="font-size:16px;">Scope Certificate Number '.$certificateNumber.'</span>
							</td>					  
						</tr>											
						<tr>
							<td style="text-align:center;font-size:14x;padding-top:12px;" valign="middle" class="reportDetailLayoutInner">GCL INTERNATIONAL LTD<br>declares that</td>	  
						</tr>
						<tr>
							<td style="text-align:center;font-weight:bold;font-size:18px;padding-top:12px;" valign="middle" class="reportDetailLayoutInner">
							<span style="font-size:18px;">'.$companyName.'</span><br>
							<span style="font-size:15px;">License Number '.$LicenseNo.'</span><br>
							'.$companyAddress.'
							</td>	  						
						</tr>
						<tr>
							<td style="text-align:center;font-size:14px;padding-top:8px;" valign="middle" class="reportDetailLayoutInner">has been inspected and assessed according to the</td>	 
						</tr>
						<tr>
							<td style="text-align:center;font-size:20px;padding-top:12px;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">'.$standardName.'<br>- Version '.$standardVersion.' -</td>	  
						</tr>						
						<tr>
							<td style="text-align:left;font-size:14px;padding-top:12px;" valign="middle" class="reportDetailLayoutInner">and that products of the categories as mentioned below (and further specified in the product appendix) conform with this standard:</td>	  
						</tr>
						<tr>
							<td style="text-align:left;font-size:14px;padding-top:12px;margin-bottom:0px;" valign="middle" class="reportDetailLayoutInner">Product categories:</td>
						</tr>	
						<tr>
							<td style="text-align:center;font-size:12px;margin-top:0px;" valign="middle" class="reportDetailLayoutInner">
							<b>'.implode(', ',$arrProductCategories).'</b>
							</td>	  
						</tr>
						<tr>
							<td style="text-align:left;font-size:14px;padding-top:6px;" valign="middle" class="reportDetailLayoutInner">Processing steps / activities carried out under responsibility of the above mentioned company for the certified products:</td>	  
						</tr>
						<tr>
							<td style="text-align:center;font-size:12px;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">';
							
							/*							
							$arrCertificateScopeHolderFacilityProcess=array();							
							$arrCertificateScopeHolderFacilityProcess=$arrProcess['1'];							
							if(array_key_exists('2', $arrProcess) && is_array($arrProcess['2']) && count($arrProcess['2'])>0)
							{
								$arrCertificateScopeHolderFacilityProcess=array_merge($arrCertificateScopeHolderFacilityProcess,$arrProcess['2']);								
							}
							$arrCertificateScopeHolderFacilityProcess = array_unique($arrCertificateScopeHolderFacilityProcess);
							$html.=implode(', ', $arrCertificateScopeHolderFacilityProcess);
							
							$arrCertificateSubContractProcess=array();														
							if(array_key_exists('3', $arrProcess) && is_array($arrProcess['3']) && count($arrProcess['3'])>0)
							{
								$arrCertificateSubContractProcess = $arrProcess['3'];
								$arrCertificateSubContractProcess = array_unique($arrCertificateSubContractProcess);								
								$html.=' Sub-Contract: '.implode(', ', $arrCertificateSubContractProcess).'';
							}
							*/
							
							$arrCertificateScopeHolderFacilityProcess=array();	
                            if(array_key_exists('1', $arrProcess) ) {						
							$arrCertificateScopeHolderFacilityProcess=$arrProcess['1'];							
							if(array_key_exists('2', $arrProcess) && is_array($arrProcess['2']) && count($arrProcess['2'])>0)
							{
								$arrCertificateScopeHolderFacilityProcess=array_merge($arrCertificateScopeHolderFacilityProcess,$arrProcess['2']);								
							}
							$arrCertificateScopeHolderFacilityProcess = array_unique($arrCertificateScopeHolderFacilityProcess);
							$html.=implode(', ', $arrCertificateScopeHolderFacilityProcess);
							
							$arrCertificateSubContractProcess=array();														
							if(array_key_exists('3', $arrProcess) && is_array($arrProcess['3']) && count($arrProcess['3'])>0)
							{
								$arrCertificateSubContractProcess = $arrProcess['3'];
								$arrCertificateSubContractProcess = array_unique($arrCertificateSubContractProcess);								
								$html.='<br>Sub-Contract: '.implode(', ', $arrCertificateSubContractProcess).'';
							}
                        }
							$html.='</td>	  
						</tr>
						<tr>
							<td style="text-align:left;font-size:14px;padding-top:13px;" valign="middle" class="reportDetailLayoutInner">This Certificate is valid until: <b>'.$certificate_expiry_date.'</b></td>	  
						</tr>
					</table>
					
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="padding-top:5px;">
						'.$signatureContent.'						
					</table>	
					
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout">					
						<tr>
							<td colspan="2" style="text-align:left;font-size:14px;padding-top:10px;" valign="middle" class="reportDetailLayoutInner">
								This Scope Certificates provides no proof that any goods delivered by its holder are '.$standardCode.' certified. Proof of '.$standardCode.' certification of goods delivered is provided by a valid Transaction Certificate (TC) covering them. 
								The issuing body may withdraw this certificate before it expires if the declared conformity is no longer guaranteed.								
							</td>
						</tr>
						<tr>
							<td style="text-align:left;font-size:14px;padding-top:8px;width:90%;" valign="middle" class="reportDetailLayoutInner">
								Accredited/Licensed by: IOAS Inc, Contract No: 125
								<br><br>
								This electronically issued document is the valid original version
							</td>
							<td style="text-align:right;font-size:14px;margin-top:0px;width:10%;" valign="middle" class="reportDetailLayoutInner">
								<img style="width:70px;" src="'.Yii::$app->params['image_files'].'ioas.png" border="0">
							</td>
						</tr>	
					</table>	
										
					<pagebreak />
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="productDetails" style="margin-top:65cm;">	
						<thead>
							<tr>
								<td valign="middle" class="productDetails" style="text-align:left;font-size:14px;padding-top:5px;font-weight:bold;">Product Category</td>
								<td style="text-align:left;font-size:14px;padding-top:5px;font-weight:bold;width:18%;" valign="middle" class="productDetails">Product Details</td>
								<td style="text-align:left;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">Material and Materials Composition</td>	
								<td style="text-align:left;font-size:14px;padding-top:5px;font-weight:bold;width:18%;" valign="middle" class="productDetails">Label Grade</td>		
							</tr>
						</thead>';
						
					$html.=$productContent;
						
						$html.='</table>
						<table cellpadding="0" cellspacing="0" border="0" width="100%">
							<tr>
								<td style="width:100%;">
									<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:25px;">
									'.$signatureContent.'					
									</table>
								</td>	
							</tr>	
						</table>	
						
						<sethtmlpagefooter name="secondpagesfooter" value="1" />

						<div class="chapter2" style="padding-top:1px;">						
						<div style="text-align:left;font-size:13px; font-weight:bold; margin-top:27px; padding-bottom:10px;"><u>Facility Appendix to Certificate No.:</u> '.$certificateNumber.'</u></div>
						
						<div style="font-size:12px;font-family:Arial;">Under the scope of this certificate the following facilities have been inspected and assessed. The listed processing steps/activities conform with the corresponding criteria of the '.$standardName.' for the certified products:</div>
						<table cellpadding="0" cellspacing="0" border="0" width="100%" class="productDetails">	
						    <thead>
								<tr>
									<td style="text-align:left;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">Name of Facility</td>
									<td style="text-align:left;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">Address of Operation</td>	
									<td style="text-align:left;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">Processing Steps/Activities</td>	
									<td style="text-align:left;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">Type of Relation (Main/Facility)</td>	
								</tr>
							</thead>';
						$html.=$unitWiseSubContractorContent;
						
						/*
						$unitWiseSubContractorContent.=$unitWiseSubContractorContent;
						$unitWiseSubContractorContent.=$unitWiseSubContractorContent;
						$unitWiseSubContractorContent.=$unitWiseSubContractorContent;
						$unitWiseSubContractorContent.=$unitWiseSubContractorContent;
						$unitWiseSubContractorContent.=$unitWiseSubContractorContent;
						$unitWiseSubContractorContent.=$unitWiseSubContractorContent;
						$html.=$unitWiseSubContractorContent;
						*/						
						
						if($unitWiseSubContractorContent==''){
							$html.= '<tr>
								<td style="text-align:center;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">-</td>
								<td style="text-align:center;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">-</td>	
								<td style="text-align:center;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">-</td>	
								<td style="text-align:center;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">-</td>	
							</tr>';
						}				
					$html.='</table><sethtmlpageheader name="secondpagesheader" value="on"  page="ALL" show-this-page="1" />
					
						<div style="text-align:left;font-size:13px;padding-top:15px;font-weight:bold;padding-top:15px;padding-bottom:10px;"><u>Non-Certified Subcontractor Appendix to Certificate No.:</u> '.$certificateNumber.'</div>
							
						<div style="font-size:12px;font-family:Arial;">Under the scope certificate the following non-certified subcontractors have been inspected and assessed. The listed processing steps/activities conform with the corresponding criteria of the '.$standardName.' for the certified products:</div>
						<table cellpadding="0" cellspacing="0" border="0" width="100%" class="productDetails">
							<thead>	
								<tr>
									<td style="text-align:left;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">Name of Facility</td>
									<td style="text-align:left;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">Address of Operation</td>
									<td style="text-align:left;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">Processing Steps/Activities</td>								
								</tr>
							</thead>';
						$html.=$unitWiseSubContractorContentSub;
						if($unitWiseSubContractorContentSub==''){
							$html.= '<tr>
								<td style="text-align:center;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">-</td>
								<td style="text-align:center;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">-</td>
								<td style="text-align:center;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">-</td>								
							</tr>';
						}
					$html.='</table><sethtmlpageheader name="secondpagesheader" value="on"  page="ALL" show-this-page="1" />
					
					<div style="text-align:left;font-size:13px;padding-top:15px;font-weight:bold;padding-bottom:10px;"><u>Certified Subcontractor Appendix to Certificate No.:</u> '.$certificateNumber.'</u></div>
							
						<div style="font-size:12px;font-family:Arial;">The following Independently certified subcontractors are listed under this scope certificate:</div>
						<table cellpadding="0" cellspacing="0" border="0" width="100%" class="productDetails">	
							<thead>
								<tr>
									<td style="text-align:left;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">License Number</td>
									<td style="text-align:left;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">Expiry Date</td>
									<td style="text-align:left;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">Name of Facility</td>
									<td style="text-align:left;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">Address of Operation</td>
									<td style="text-align:left;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">Processing Steps/Activities</td>
									<td style="text-align:left;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">Type of Relation</td>	
								</tr>							
							</thead>';							
						$html.=$unitWiseSubContractorContentCertified;	
						if($unitWiseSubContractorContentCertified==''){
							$html.= '<tr>
								<td style="text-align:center;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">-</td>
								<td style="text-align:center;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">-</td>
								<td style="text-align:center;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">-</td>
								<td style="text-align:center;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">-</td>
								<td style="text-align:center;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">-</td>
								<td style="text-align:center;font-size:14px;padding-top:5px;font-weight:bold;" valign="middle" class="productDetails">-</td>
							</tr>';
						}
					$html.='</table><sethtmlpageheader name="secondpagesheader" value="on"  page="ALL" show-this-page="1" />';

				//$html.='</div>';	
				
				$html.='
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr>
						<td style="width:100%;">
							<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:25px;">
							'.$signatureContent.'					
							</table>
						</td>	
					</tr>
				</table>	
				<sethtmlpagefooter name="secondpagesfooter" value="1" />';
				
				//$html.='<sethtmlpagefooter name="lastpagesfooter" value="1" />';	
				
				$html.= $this->getGotsContent();
				
				$html.='</div><sethtmlpageheader name="secondpagesheader" value="on"  page="ALL" show-this-page="1" />';
										
				$mpdf->WriteHTML($html);
				
				$pdfName = $customeroffernumber.'_'.$standardCode.'_CERTIFICATE_' . date('YmdHis') . '.pdf';
				$filePath = Yii::$app->params['certificate_files'].$pdfName;
				if($returnType)
				{										
					$mpdf->Output($filePath,'F');
					
					$model->filename=$pdfName;
					$model->save();					
				}else{
					$mpdf->Output($pdfName,'D');					
				}			
			}											
		}		
	}


	private function getGotsContent(){
		$gotcontent = '';

		return $gotcontent;
	}


	public function applicationStandardDecline($pdata){
		$standard_id = $pdata['standard_id'];
		$app_id = $pdata['app_id'];
		$change_status = isset($pdata['status'])?$pdata['status']:1;


		//$parent_app_id = $pdata['parent_app_id'];
		$model = Application::find()->where(['id' => $app_id])->one();
		if($model !== null){

			$appProduct=$model->applicationproduct;
			if(count($appProduct)>0)
			{
				foreach($appProduct as $prd)
				{
					if(count($prd->productstandard)>0){
						foreach($prd->productstandard as $productstandard)
						{
							if($productstandard->standard_id == $standard_id){
								$productstandard->product_standard_status = 1;
								$productstandard->save();
							}
						}
					}
				}
			}

			$modelappstd = ApplicationStandard::find()->where(['app_id' => $app_id,'standard_id'=>$standard_id ])->one();
			if($modelappstd !== null){
				$modelappstd->standard_status = $change_status;
				if($modelappstd->save()){

					if(count($model->applicationunit)>0){
						foreach($model->applicationunit as $unit){
							$standardapplicable = 0;
							$unitappstandard=$unit->unitappstandard;
							if(count($unitappstandard)>0)
							{
								foreach($unitappstandard as $unitstd)
								{
									if($unitstd->standard_id == $standard_id){
										$standardapplicable = 1;
										$unitstd->unit_standard_status = $change_status;
										$unitstd->save();
									}
								}
							}
							if($standardapplicable == 0){
								continue;
							}

							$unitprocess=$unit->unitprocessall;
							if(count($unitprocess)>0)
							{										
								foreach($unitprocess as $unitPcs)
								{
									if($unitPcs->standard_id == $standard_id){
										$unitPcs->unit_process_status= $change_status;
										$unitPcs->save(); 
									}
								}									
							}


							$unitbsector=$unit->unitbusinesssector;
							if(count($unitbsector)>0)
							{									
								foreach($unitbsector as $unitbs)
								{

									$changeBsector = 1;
									$commonStd = [$standard_id];
									//For change of address error 
									if(count($commonStd)>0){
										$business_sector_id = $unitbs->business_sector_id;
										$chkBusiness = ['business_sector_id'=>$business_sector_id,'standard_id'=>$commonStd];
										$relatedsector = Yii::$app->globalfuns->checkBusinessSectorInStandard($chkBusiness);
										if(!$relatedsector){
											$changeBsector = 0;
										}else{
											if(count($unitappstandard)>1){
												$changeBsector = 0;
											}
										}
									}
									
									if($changeBsector){
										$unitbs->unit_business_sector_status = $change_status;
										$unitbs->save(); 
									}
									


									
									$unitbsectorgp=$unitbs->unitbusinesssectorgroup;
									if(count($unitbsectorgp)>0)
									{									
										foreach($unitbsectorgp as $unitbsgp)
										{
											if(count($commonStd)>0){
												$business_sector_group_id = $unitbsgp->business_sector_group_id;
												$chkBusiness = ['business_sector_group_id'=>$business_sector_group_id,'standard_id'=>$commonStd];
												$relatedsector = Yii::$app->globalfuns->checkBusinessSectorGroupInStandard($chkBusiness);
												if(!$relatedsector){
													continue;
												}
											}
											
											$unitbsgp->unit_business_sector_group_status = $change_status;
											$unitbsgp->save(); 
										}
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
}
