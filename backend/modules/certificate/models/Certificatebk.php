<?php

namespace app\modules\certificate\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use app\modules\master\models\User;
use app\modules\master\models\Standard;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationUnitStandard;
use app\modules\application\models\ApplicationCertifiedByOtherCB;

use app\modules\audit\models\Audit;

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
	public $arrStatus=array('0'=>'Open','1'=>'Certification In-Process','2'=>'Certified','3'=>'Declined','4'=>'Suspension','5'=>'Cancellation','6'=>'Withdrawn','7'=>'Extension','8'=>'Certificate Reinstate','9'=>'Certified by Other CB yet to be expired');
    public $arrEnumStatus=array('open'=>'0','certification_in_process'=>'1','certificate_generated'=>'2','declined'=>'3','suspension'=>'4','cancellation'=>'5','withdrawn'=>'6','extension'=>'7','certificate_reinstate'=>'8','certified_by_other_cb'=>'9');    
    public $arrStatusColor=array('0'=>'#4572A7','1'=>"#DB843D",'2'=>'#5f79fa','3'=>'#ff0000','4'=>'#4572A7','5'=>'#DB843D','6'=>'#5f79fa','7'=>'#457222','8'=>'#eeeeee','9'=>'#DB843D');
    
    public $arrCertificateStatus=array('0'=>'Valid','1'=>'In-Valid');
	public $arrEnumCertificateStatus=array('valid'=>'0','invalid'=>'1');
	
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
        return $this->hasOne(CertificateReviewer::className(), ['certificate_id' => 'id']);
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
				if($getCertifiedDateModel !== null && $audit_type!=$applicationmodel->arrEnumAuditType['renewal'])
				{
					$certificate_generate_date = date('d/F/Y',strtotime($certificate_generate_date));
					$certificate_expiry_date = date('d/F/Y', strtotime($getCertifiedDateModel->certificate_valid_until));
					$certificateDraftNo = $getCertifiedDateModel->version+1;
				}else{
					$futureDate = date('Y-m-d', strtotime('+1 year', strtotime($certificate_generate_date)) );
					$certificate_generate_date = date('d/F/Y',strtotime($certificate_generate_date));					
					$certificate_expiry_date = date('d/F/Y', strtotime('-1 day', strtotime($futureDate)));						
				}	
			}else{				
				$certificate_generate_date = date('d/F/Y', strtotime($model->certificate_generated_date));
				$certificate_expiry_date = date('d/F/Y', strtotime($model->certificate_valid_until));
			}		
				
			//$applicationID = $model->audit->application->id;
			$applicationID = $model->application->id;
						
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
				$certificateNumber = "GCL-".$customeroffernumber."/".$standardCode."-".date("Y")."/".$certificateDraftNo;
				
				
				$productsQry = 'SELECT prd.name AS product,prdtype.name AS product_type,GROUP_CONCAT(DISTINCT apm.percentage, \'% \', ptm.`name` SEPARATOR \' + \') AS material_composition,slg.name AS product_code,slg.id as product_label_grade_id  FROM `tbl_application_product` AS ap
				INNER JOIN `tbl_application_product_material` AS apm ON apm.app_product_id = ap.id AND ap.app_id='.$applicationID.'
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
						$productContent.='<tr>
							<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px;background-color:#ffffff;" valign="middle">'.$vals['product_type'].'</td>
							<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px;background-color:#ffffff;" valign="middle">'.$vals['material_composition'].'</td>	
							<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px;background-color:#ffffff;" valign="middle">'.$vals['product_code'].'</td>		
						</tr>';
						
						if (!in_array($vals['product'], $arrProductCategories))
						{
							$arrProductCategories[]=$vals['product'];
						}
						
						$arrCertificateCoveredProducts[]=array('name_of_product'=>$vals['product_type'],'material_composition'=>$vals['material_composition'],'label_grade'=>$vals['product_code']);
						
						$arrLabelGrade[$labelGradeCnt]=$vals['product_code'];
						$labelGradeCnt++;
					}
				}					
												
				$arrCertificateLogo=array();
				$certificationStd='';
				$standard_code_lower = strtolower($standardCode);
				if($standard_code_lower=='gots'){
					$certificationStd=$standard_code_lower;
				//}elseif($standard_code_lower=='ocs'){
					//$certificationStd=$standard_code_lower;
				}elseif($standard_code_lower=='grs'){
					$certificationStd=$standard_code_lower;
				//}elseif($standard_code_lower=='rcs'){
					//$certificationStd=$standard_code_lower;
				//}elseif($standard_code_lower=='ccs'){
					//$certificationStd=$standard_code_lower;
				}
				
				$arrCertificateLogo[]=$standard_code_lower.'_logo.png';
				
				if(is_array($arrLabelGrade) && count($arrLabelGrade)>0)
				{
					$resArray = array_filter($arrLabelGrade, function($value) {
						return strpos($value, 'blended') !== false;
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
					$processQry = 'SELECT appunit.id as unit_id,prs.name as process_name,appunit.unit_type as unit_type FROM `tbl_application_unit` AS appunit
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
								$arrProcess[$processRes['unit_type']][]=$processRes['process_name'];
							}
							if(!isset($arrUnitWiseProcess[$processRes['unit_id']]) || !in_array($processRes['process_name'],$arrUnitWiseProcess[$processRes['unit_id']])){
								$arrUnitWiseProcess[$processRes['unit_id']][]=$processRes['process_name'];
							}
						}
					}
				}

				$subContractorORfacilityQry = 'SELECT appunit.id as unit_id,appunit.name as unit_name,appunit.address as unit_address,appunit.zipcode as unit_zipcode,appunit.city as unit_city,state.name as unit_state,country.name as unit_country FROM `tbl_application_unit` AS appunit
				INNER JOIN `tbl_application_unit_standard` AS appunitstd ON appunitstd.unit_id=appunit.id AND appunitstd.unit_standard_status=0 AND appunit.status=0 AND appunit.app_id='.$applicationID.' AND appunit.unit_type!=1 AND appunitstd.standard_id='.$standard_id.'
				INNER JOIN `tbl_state` AS state ON state.id=appunit.state_id
				INNER JOIN `tbl_country` AS country ON country.id=appunit.country_id
				GROUP BY appunit.id';	
				$command = $connection->createCommand($subContractorORfacilityQry);
				$subContractResult = $command->queryAll();			
				
				$unitWiseSubContractorContent='';
				$arrSubContractor=array();
				if(count($subContractResult)>0)
				{
					foreach($subContractResult as $subContractRes)
					{
						$subContractName = $subContractRes['unit_name'];
						$subContractAddress = $subContractRes['unit_address'].', ';
						$subContractAddress .= $subContractRes['unit_city'].' - '.$subContractRes['unit_zipcode'].' ';
						$subContractAddress .= $subContractRes['unit_state'].' ';
						$subContractAddress .= $subContractRes['unit_country'];					
						
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
						
						$unitWiseSubContractorContent.='<tr>
							<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px;background-color:#ffffff;" valign="middle">'.$subContractName.'<br>'.$subContractAddress.'</td>
							<td class="productDetails" style="text-align:left;font-size:14px;padding-top:5px;background-color:#ffffff;" valign="middle">'.$unitProcess.'</td>	
						</tr>';
						
						$arrSubContractor[]=array('name_of_operation'=>$subContractName,'address_of_operation'=>$subContractAddress,'processing_steps'=>$unitProcess);
							
					}
				}
				
				$date_format = Yii::$app->globalfuns->getSettings('date_format');
			
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
				$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8',
					'margin_left' => 10,
					'margin_right' => 10,
					'margin_top' => 40,
					'margin_bottom' => 15,
					'margin_header' => 0,
					'margin_footer' => 3
				]);
				$mpdf->SetDisplayMode('fullwidth');
				
				if($draftText!='')
				{
					$mpdf->SetWatermarkText('DRAFT');
					$mpdf->showWatermarkText = true;
				}
				
				$headerContent = '<div style="padding-top:15px;">
						<div style="width:80%;text-align: left;float:left;font-size:12px;">
							<img src="'.Yii::$app->params['image_files'].'header-img.jpg" border="0">						
						</div>
						<div style="width:20%;float:right;font-size:12px;font-family:Arial;">
							<img src="'.Yii::$app->params['image_files'].'qrcode-img.png" style="width: 85px;margin-left: 45px;">
						</div>
					</div>';
				
				$footerContent='<tr>
						<td style="text-align:center;font-size:14px;padding-top:5px;" valign="middle" class="reportDetailLayoutInner">Place and Date of Issue <br>London, '.$certificate_generate_date.'</td>
						<td style="text-align:center;font-size:14px;padding-top:5px;" valign="middle" class="reportDetailLayoutInner">Stamp of the issuing body</td>	
						<td style="text-align:center;font-size:14px;padding-top:5px;" valign="middle" class="reportDetailLayoutInner">'.$standardCode.' Logo</td>		
					</tr>
					<tr>
						<td style="text-align:center;font-size:14px;padding-top:5px;width:33%;" valign="middle" class="reportDetailLayoutInner">
							<img style="width:120px;" src="'.Yii::$app->params['image_files'].'certificate-sign.png" border="0">
							<p>Name of the authorized person:<br>Gary Jones, Chief Executive Officer<br>GCL International Ltd</p>
						</td>
						<td style="text-align:center;font-size:14px;padding-top:5px;width:33%;" valign="middle" class="reportDetailLayoutInner">
							<img style="width:100px;" src="'.Yii::$app->params['image_files'].'gcl-stamp.png" border="0">
						</td>
						<td style="text-align:center;font-size:14px;padding-top:5px;width:33%;" valign="middle" class="reportDetailLayoutInner">';
						if(is_array($arrCertificateLogo) && count($arrCertificateLogo)>0)
						{
							foreach($arrCertificateLogo as $certLogo)
							{
								$footerContent.='<img style="width:80px;" src="'.Yii::$app->params['image_files'].''.$certLogo.'" border="0">';
							}
						}						
				$footerContent.='</td>						
				</tr>';
				
				$html='
				<style>
				table {
				border-collapse: collapse;
				}

				@page {  
					header: html_otherpageheader;
					footer: html_otherpagesfooter;
				}

				@page :first {    
					header: html_firstpageheader;
					footer: html_firstpagefooter;
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
					border: 1px solid #4e85c8;
					width:100%;
					font-size:12px;
					font-family:Arial;
					text-align: left;
					margin-bottom:5px;
					margin-top:5px;
				}

				td.productDetails {
					text-align: center;
					border: 1px solid #4e85c8;
					font-size:12px;
					font-family:Arial;
					text-align: left;
					background-color:#DFE8F6;
					padding:3px;
				}

				td.reportDetailLayout {
					text-align: center;
					font-size:12px;
					font-family:Arial;
					text-align: left;
					background-color:#DFE8F6;
					padding:3px;
				}
				td.reportDetailLayoutHead {
					text-align: center;
					font-size:12px;
					font-family:Arial;
					text-align: left;
					/*background-color:#4e85c8;*/
					background-color:#006fc0;
					padding:3px;
					color:#FFFFFF;
				}

				td.reportDetailLayoutInner {
					text-align: center;
					font-size:12px;
					font-family:Arial;
					text-align: left;
					background-color:#ffffff;
					padding:3px;
				}
				</style>
				
				<htmlpageheader name="firstpageheader" style="display:none">
					'.$headerContent.'	
				</htmlpageheader>

				<htmlpagefooter name="firstpagefooter" style="display:none">
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout">
						'.$footerContent.'					
						<tr>
							<td colspan="3" style="text-align:left;font-size:14px;padding-top:10px;" valign="middle" class="reportDetailLayoutInner">
							This Certificate of Compliance provides no proof that any goods delivered by its holder are '.$standardCode.' certified. Proof of '.$standardCode.' certification of goods delivered is provided by a valid Transaction Certificate (TC) covering them. 
							The issuing body can withdraw this certificate before it expires if the declared compliance is no longer guaranteed.
							Accredited/Licensed by: International Organic Accreditation Services (IOAS), Contract No: 125
							</td>						
						</tr>
						<tr>
							<td colspan="3" style="text-align:right;font-size:14px;padding-top:10px;" valign="middle" class="reportDetailLayoutInner">
							<img style="width:120px;" src="'.Yii::$app->params['image_files'].'ioas.png" border="0">
							</td>
						</tr>					
					</table>
				</htmlpagefooter>

				<htmlpageheader name="otherpageheader" style="display:none">
					'.$headerContent.'
					<div style="margin-top:0px;">
						<table cellpadding="0" cellspacing="0" border="0" class="reportDetailLayout">
						<tr><td>Annex to certificate no: '.$certificateNumber.'</td></tr>
						<tr><td>'.$companyName.'</td></tr>
						<tr><td>'.$standardName.'</td></tr>
						</table>
					</div>				
				</htmlpageheader>

				<htmlpagefooter name="otherpagesfooter" style="display:none">
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout">
						'.$footerContent.'					
					</table>
				</htmlpagefooter>
										
				<div>
					
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:40px;">	
						<tr>
							<td style="text-align:center;font-weight:bold;font-size:23px;" valign="middle" class="reportDetailLayoutInner">CERTIFICATE OF COMPLIANCE</td>					  
						</tr>
						<tr>
							<td style="text-align:center;font-weight:bold;font-size:16px;" valign="middle" class="reportDetailLayoutInner">(Scope Certificate)</td>					  
						</tr>
						<tr>
							<td style="text-align:center;font-weight:bold;font-size:14px;padding-top:12px;" valign="middle" class="reportDetailLayoutInner">Registration No: '.$RegistrationNo.'</td>
						</tr>
						<tr>
							<td style="text-align:center;font-weight:bold;font-size:14x;padding-top:12px;" valign="middle" class="reportDetailLayoutInner">GCL INTERNATIONAL LTD declares that</td>	  
						</tr>
						<tr>
							<td style="text-align:center;font-weight:bold;font-size:18px;padding-top:12px;" valign="middle" class="reportDetailLayoutInner">'.$companyName.'</td>	  
						</tr>
						<tr>
							<td style="text-align:center;font-weight:bold;font-size:16px;" valign="middle" class="reportDetailLayoutInner">'.$companyAddress.'</td>	  
						</tr>
						<tr>
							<td style="text-align:center;font-size:14px;" valign="middle" class="reportDetailLayoutInner">has been inspected and assessed according to the</td>	 
						</tr>
						<tr>
							<td style="text-align:center;font-size:18px;padding-top:12px;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">'.$standardName.'</td>	  
						</tr>
						<tr>
							<td style="text-align:center;font-size:16px;padding-top:5px;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">- Version '.$standardVersion.' -</td>	  
						</tr>
						<tr>
							<td style="text-align:center;font-size:14px;" valign="middle" class="reportDetailLayoutInner">and that products of the categories as mentioned below (and further specified in the annex) comply with this standard:</td>	  
						</tr>
						<tr>
							<td style="text-align:center;font-size:14px;padding-top:5px;" valign="middle" class="reportDetailLayoutInner">Product categories: <b>'.implode(', ',$arrProductCategories).'</b></td>	  
						</tr>
						<tr>
							<td style="text-align:center;font-size:14px;" valign="middle" class="reportDetailLayoutInner">Processing steps / activities carried out under responsibility of the above mentioned company (by the operations as detailed in the annex) for the certified products:</td>	  
						</tr>
						<tr>
							<td style="text-align:center;font-size:14px;padding-top:15px;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">'.implode(', ', $arrProcess['1']).'';
							if(array_key_exists('2', $arrProcess) && is_array($arrProcess['2']) && count($arrProcess['2'])>0)
							{
								$html.=' Facility: '.implode(', ', $arrProcess['2']).'';
							}
							if(array_key_exists('3', $arrProcess) && is_array($arrProcess['3']) && count($arrProcess['3'])>0)
							{
								$html.=' Sub-Contract: '.implode(', ', $arrProcess['3']).'';
							}					
							$html.='</td>	  
						</tr>
						<tr>
							<td style="text-align:left;font-size:14px;padding-top:15px;padding-left:10px;" valign="middle" class="reportDetailLayoutInner">This Certificate is valid until: <b>'.$certificate_expiry_date.'</b></td>	  
						</tr>
					</table>

					<pagebreak />

					<div style="font-size:12px;font-family:Arial;padding-top:35px;">In specific the certificate covers the following products:</div>
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="productDetails" style="margin-top:5px;">	
						<tr>
							<td style="text-align:left;font-size:14px;padding-top:5px;" valign="middle" class="productDetails">Name of product</td>
							<td style="text-align:left;font-size:14px;padding-top:5px;" valign="middle" class="productDetails">Material composition</td>	
							<td style="text-align:left;font-size:14px;padding-top:5px;" valign="middle" class="productDetails">Product code</td>		
						</tr>';
						
					$html.=$productContent;
						
					$html.='</table>						
					
					<pagebreak />
					
					<div style="font-size:12px;font-family:Arial;padding-top:15px;">Under the scope of this certificate the following facilities / subcontractors have been inspected and assessed. The listed processing steps/activities comply with the corresponding criteria of the '.$standardName.' for the certified products:</div>
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="productDetails">	
						<tr>
							<td style="text-align:left;font-size:14px;padding-top:5px;" valign="middle" class="productDetails">Name and address of operation</td>
							<td style="text-align:left;font-size:14px;padding-top:5px;" valign="middle" class="productDetails">Processing steps / activities</td>	
						</tr>';
					$html.=$unitWiseSubContractorContent;	
				$html.='</table>';

				$html.='</div>';				
										
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
							$productstandard->product_standard_status = 1;
							$productstandard->save();
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

							$unitappstandard=$unit->unitappstandard;
							if(count($unitappstandard)>0)
							{
								foreach($unitappstandard as $unitstd)
								{
									if($unitstd->standard_id == $standard_id){
										$unitstd->unit_standard_status = $change_status;
										$unitstd->save();
									}
								}
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
