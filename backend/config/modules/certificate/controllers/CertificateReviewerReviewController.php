<?php
namespace app\modules\certificate\controllers;

use Yii;
use app\modules\certificate\models\Certificate;
use app\modules\master\models\AuditReviewerRiskCategory;

use app\modules\certificate\models\CertificateReviewer;
use app\modules\certificate\models\CertificateReviewerReview;
use app\modules\certificate\models\CertificateReviewerReviewChecklistComment;
use app\modules\master\models\AuditReviewerQuestions;
use app\modules\master\models\AuditReviewerQuestionRiskCategory;
use app\modules\master\models\BusinessSectorGroup;

use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\Audit;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnitProcess;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationProduct;
use app\modules\application\models\ApplicationProductMaterial;
use app\modules\application\models\ApplicationProductStandard;

use app\modules\changescope\models\ProcessAddition;
use app\modules\changescope\models\UnitAddition;
use app\modules\changescope\models\ProductAddition;
use app\modules\changescope\models\ProductAdditionProductStandard;
use app\modules\changescope\models\ProductAdditionProduct;
use app\modules\changescope\models\ProductAdditionProductMaterial;

use app\modules\application\models\ApplicationUnitProduct;
use app\modules\application\models\ApplicationUnitBusinessSector;
use app\modules\application\models\ApplicationUnitBusinessSectorGroup;
use app\modules\application\models\ApplicationUnitStandard;
use app\modules\application\models\ApplicationUnitCertifiedStandard;
use app\modules\application\models\ApplicationUnitCertifiedStandardFile;
use app\modules\application\models\ApplicationChangeAddress;
use app\modules\application\models\ApplicationProductCertificateTemp;
use app\modules\application\models\ApplicationProductHistory;
use app\modules\application\models\ApplicationProductMaterialHistory;
use app\modules\application\models\ApplicationProductStandardHistory;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class CertificateReviewerReviewController extends \yii\rest\Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {

        return [
			[
				'class' => \yii\filters\ContentNegotiator::className(),
				//'only' => ['index', 'view'],
				'formats' => [
					'application/json' => \yii\web\Response::FORMAT_JSON,
				],
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
			],
			'authenticator' => ['class' => JwtHttpBearerAuth::class ]
		];        
    }
	
	

    public function actionCreate()
    {
		if(!Yii::$app->userrole->hasRights(array('certification_review')))
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again later');
		$data = Yii::$app->request->post();
        if ($data) 
		{
			/*
			$certificatemodel = Certificate::find()->where(['audit_id'=>$data['audit_id']])->one();
			if($certificatemodel === null)
			{
				$certificatemodel =new Certificate();
				$certificatemodel->audit_id=$data['audit_id'];
				$userData = Yii::$app->userdata->getData();
				$certificatemodel->status=$certificatemodel->arrEnumStatus['certification_in_process'];
				$certificatemodel->created_at=time();
				$certificatemodel->created_by=$userData['userid'];
				$certificatemodel->save();
			}else{
				$certificatemodel->status=$certificatemodel->arrEnumStatus['certification_in_process'];				
				$certificatemodel->save();
			}
			*/		
			$CertificateReviewer = CertificateReviewer::find()->where(['reviewer_status'=>1,'certificate_id'=>$data['certificate_id']])->one();
			$reviewmodel =new CertificateReviewerReview();
			$reviewmodel->certificate_id=$data['certificate_id'];
			$reviewmodel->certificate_reviewer_id=$CertificateReviewer->id;
			$reviewmodel->comment=$data['checklist_comment'];
			$reviewmodel->risk_category=$data['checklist_risk'];
			$userData = Yii::$app->userdata->getData();
			$reviewmodel->user_id=$userData['userid'];
			$reviewmodel->created_at=time();
			$reviewmodel->created_by=$userData['userid'];
						

			if($reviewmodel->validate() && $reviewmodel->save())
			{
				//generate_certificate'=>'11','certificate_denied
				
				/*
				$auditplan = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
				if($auditplan !== null){

					$audit = Audit::find()->where(['id'=>$auditplan->audit_id])->one();

					if($data['actiontype'] == 'decline'){
						$auditplan->status = $auditplan->arrEnumStatus['certificate_denied'];
						$auditplan->save();

						if($audit !== null){
							$audit->status = $audit->arrEnumStatus['certificate_denied'];
							$audit->save();
						}
					}else{
						
						$auditplan->status = $auditplan->arrEnumStatus['certification_inprocess'];
						$auditplan->save();

						if($audit !== null){
							$audit->status = $audit->arrEnumStatus['certification_inprocess'];
							$audit->save();
						}
					}					
				}
				*/
				
				// ------ Update the Risk Category to Certificate & Audit Code Start Here ------------------			
				$review_risk_category = $reviewmodel->risk_category;
				$Certificate = Certificate::find()->where(['id'=>$data['certificate_id']])->one();
				$Certificate->risk_category=$review_risk_category;
				if($Certificate->save())
				{
					// ----- Save/Update Audit Risk Category Code Start Here -------
					$auditObj = $Certificate->audit;
					if($auditObj!==null)
					{
						$audit_current_risk_category = $auditObj->risk_category;						
						if($review_risk_category<$audit_current_risk_category || $audit_current_risk_category==0)
						{
							$auditObj->risk_category = $review_risk_category;
							$auditObj->save();			
						}
					}
					// ----- Save/Update Audit Risk Category Code End Here -------
					
					
					// ----- Save/Update Parent Application Risk Category Code Start Here -------
					$parentAppObj = $Certificate->application;
					if($parentAppObj!==null)
					{						
						$parent_app_current_risk_category = $parentAppObj->risk_category;												
						if($review_risk_category<$parent_app_current_risk_category || $parent_app_current_risk_category==0)
						{
						
							$parentAppObj->risk_category = $review_risk_category;
							$parentAppObj->save();			
						}
					}
					// ----- Save/Update Parent Application Risk Category Code End Here -------
					
					// ----- Save/Update Current Application Risk Category Code Start Here -------
					$applicationObj = $auditObj->application;
					if($applicationObj!==null)
					{
						$app_current_risk_category = $applicationObj->risk_category;						
						if($review_risk_category<$app_current_risk_category || $app_current_risk_category==0)
						{
							$applicationObj->risk_category = $review_risk_category;
							$applicationObj->save();							
					
						}
					}								
					// ----- Save/Update Current Application Risk Category Code End Here -------
					
				}				
				// ------ Update the Risk Category to Certificate & Audit Code End Here ------------------
				
				if($data['actiontype'] == 'decline')
				{
					//$Certificate = Certificate::find()->where(['id'=>$data['certificate_id']])->one();
					if($Certificate !== null){
						$Certificate->status = $Certificate->arrEnumStatus['declined'];
						$Certificate->certificate_status = 1;
						$Certificate->created_by = $userData['userid'];						
						$Certificate->save();

						//$ApplicationStandard = ApplicationStandard::find()->where(['app_id'=>$Certificate->parent_app_id,'standard_id'=>$Certificate->standard_id,'standard_status'=>0])->one();
						//if($ApplicationStandard !== null){
							//$ApplicationStandard->standard_status = $ApplicationStandard->arrEnumStatus['invalid'];
							//$ApplicationStandard->save();
						//$Certificate->type == $Certificate->arrEnumType['normal']
						if(1){
							$ModelApplicationStandard = new ApplicationStandard();

							$datas= ['app_id'=>$Certificate->parent_app_id,'standard_id'=>$Certificate->standard_id,'status'=>$ModelApplicationStandard->arrEnumStatus['declined'] ];
							$Certificate->applicationStandardDecline($datas);

							$CertificateOther = Certificate::find()->where(['parent_app_id'=>$Certificate->parent_app_id,'standard_id'=> $Certificate->standard_id,'certificate_status'=>0 ])->one();
							if($CertificateOther !== null){
								$CertificateOther->certificate_status = 1;
								$CertificateOther->save();
							}
						}
							//echo 'asd';
						//}
					}
					
				}
				
				//$Certificate = Certificate::find()->where(['id'=>$data['certificate_id']])->one();
				if($Certificate !== null)
				{
					if($data['actiontype'] != 'decline')
					{
						$application = $Certificate->audit->application;
						if($application !== null){
							
							if($Certificate->product_addition_id !='' && $Certificate->product_addition_id>0){

								$productmodel = ProductAddition::find()->where(['id' => $Certificate->product_addition_id])->one();
								
								
								$this->storeApplicationProductAdditionHistory($Certificate->parent_app_id,$Certificate->id,$Certificate->product_addition_id);


								$clonedata = ['app_id'=>$Certificate->parent_app_id,'product_addition_id'=>$Certificate->product_addition_id, 'standard_id'=>$Certificate->standard_id];
								//print_r($clonedata);
								$appModelclone=new Application();
								$appModelclone->cloneApplicationProduct($clonedata);

								
								
							}elseif($application->audit_type==$application->arrEnumAuditType['change_of_address']){
								$ApplicationUpdate = Application::find()->where(['id'=>$application->id])->one();
								$address_id = $ApplicationUpdate->address_id;

								$ApplicationChangeAddress = ApplicationChangeAddress::find()->where(['id'=>$address_id])->one();
								if($ApplicationChangeAddress !== null){
									$ApplicationChangeAddress->parent_app_id = $Certificate->parent_app_id;
									$ApplicationChangeAddress->save();
								}
								//$pdata = ['app_id'=>$application->id,'parent_app_id'=>$Certificate->parent_app_id,'standard_id'=>$Certificate->standard_id];
								//$this->addProcessToApp($pdata);
								
							}elseif($application->audit_type==$application->arrEnumAuditType['process_addition']){
								
								$pdata = ['app_id'=>$application->id,'parent_app_id'=>$Certificate->parent_app_id,'standard_id'=>$Certificate->standard_id];
								$this->addProcessToApp($pdata);
								
							}elseif($application->audit_type==$application->arrEnumAuditType['unit_addition']){
								
								$pdata = ['app_id'=>$application->id,'parent_app_id'=>$Certificate->parent_app_id,'standard_id'=>$Certificate->standard_id];
								$this->addUnitToApp($pdata);
								
							}elseif($application->audit_type==$application->arrEnumAuditType['standard_addition']){
								
								$pdata = ['app_id'=>$application->id,'parent_app_id'=>$Certificate->parent_app_id,'standard_id'=>$Certificate->standard_id];
								$this->addStandardDetailsToApp($pdata);
								//echo 'asdasd';
							}elseif($application->audit_type==$application->arrEnumAuditType['change_of_address']){
								
								//$parentapplication = $Certificate->application;
								//$parentapplicationscopeholder = $Certificate->application->applicationscopeholder;
								
								$ApplicationChangeAddressModel = ApplicationChangeAddress::find()->where(['current_app_id'=>$application->id])->one();
								if($ApplicationChangeAddressModel === null)
								{								
									$modelApplicationChangeAddress=new ApplicationChangeAddress();
									$modelApplicationChangeAddress->parent_app_id=$Certificate->parent_app_id;
									$modelApplicationChangeAddress->current_app_id=$application->id;
									$modelApplicationChangeAddress->customer_id=$application->customer_id;
									$modelApplicationChangeAddress->company_name=$application->company_name;
									$modelApplicationChangeAddress->address=$application->address;
									$modelApplicationChangeAddress->zipcode=$application->zipcode;
									$modelApplicationChangeAddress->city=$application->city;
									$modelApplicationChangeAddress->state_id=$application->state_id;
									$modelApplicationChangeAddress->country_id=$application->country_id;
									
									/*
									$modelApplicationChangeAddress->unit_name=$parentapplicationscopeholder->name;
									$modelApplicationChangeAddress->unit_address=$parentapplicationscopeholder->address;
									$modelApplicationChangeAddress->unit_zipcode=$parentapplicationscopeholder->zipcode;
									$modelApplicationChangeAddress->unit_city=$parentapplicationscopeholder->city;
									$modelApplicationChangeAddress->unit_state_id=$parentapplicationscopeholder->state_id;
									$modelApplicationChangeAddress->unit_country_id=$parentapplicationscopeholder->country_id;
									*/
									
									$modelApplicationChangeAddress->salutation=$application->salutation;
									$modelApplicationChangeAddress->title=$application->title;
									$modelApplicationChangeAddress->first_name=$application->first_name;
									$modelApplicationChangeAddress->last_name=$application->last_name;
									$modelApplicationChangeAddress->job_title=$application->job_title;
									$modelApplicationChangeAddress->telephone=$application->telephone;
									$modelApplicationChangeAddress->email_address=$application->email_address;
									$modelApplicationChangeAddress->save();
								}									
							}elseif($application->audit_type==$application->arrEnumAuditType['normal'] || $application->audit_type==$application->arrEnumAuditType['renewal']){
									
								$ModelApplicationStandard = new ApplicationStandard();
								
								$changestatusval = $ModelApplicationStandard->arrEnumStatus['draft_certificate'];
								$invalidstatus = $ModelApplicationStandard->arrEnumStatus['invalid'];
								$ChangeApplicationStandard = ApplicationStandard::find()->where(['standard_id'=>$Certificate->standard_id,'app_id'=>$Certificate->parent_app_id,'standard_status'=>$invalidstatus ])->one();
								if($ChangeApplicationStandard !== null){
									$ChangeApplicationStandard->standard_status = $changestatusval;
									$ChangeApplicationStandard->save();
								}
								

								
								$this->storeApplicationProductHistory($Certificate->parent_app_id,$Certificate->id);
								
								
							}							
						}					
					}else{
						$Certificate->status=$Certificate->arrEnumStatus['declined'];
						$Certificate->certificate_status=$Certificate->arrEnumCertificateStatus['invalid'];
						$Certificate->save();
					}
				}

				if(is_array($data['review_answers']) && count($data['review_answers'])>0)
				{
					foreach ($data['review_answers'] as $value)
					{ 
						$reviewcmtmodel=new CertificateReviewerReviewChecklistComment();
						$reviewcmtmodel->certificate_reviewer_review_id=$reviewmodel->id;
						$reviewcmtmodel->question_id=isset($value['question_id'])?$value['question_id']:"";
						$reviewcmtmodel->question=isset($value['question'])?$value['question']:"";
						$reviewcmtmodel->answer=isset($value['answer'])?$value['answer']:"";
						$reviewcmtmodel->comment=isset($value['comment'])?$value['comment']:"";
						$reviewcmtmodel->save();
					}

					$responsedata=array('status'=>1,'message'=>'Audit Certification Review has been saved successfully');
					
				}
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$reviewmodel->errors);
			}
				
		}
		
		return $this->asJson($responsedata);
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
	}
	
	public function addStandardDetailsToApp($pdata){
		$connection = Yii::$app->getDb();
		$connection->createCommand("SET SESSION group_concat_max_len = 1000000;")->execute();
		$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();

		
		$standard_id = $pdata['standard_id'];
		$app_id = $pdata['app_id'];
		$parent_app_id = $pdata['parent_app_id'];

		$model = Application::find()->where(['id' => $app_id])->one();

		$parentapp_model = Application::find()->where(['id' => $parent_app_id])->one();

		if($model !== null && $parentapp_model !== null){
			$appstdarr=[];
			$arrstandardids=[];
			$appStandard=$model->applicationstandard;
			if(count($appStandard)>0)
			{
				foreach($appStandard as $std)
				{	
					
					if($standard_id != $std->standard_id){
						continue;
					}

					$appstdmodel=new ApplicationStandard();
					$appstdmodel->app_id=$parent_app_id;
					$appstdmodel->standard_id=$std->standard_id;
					$appstdmodel->version=$std->version;
					$appstdmodel->standard_addition_type = 1;
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
					

					
					$appproductmodel=new ApplicationProduct();
					$appproductmodel->app_id = $parent_app_id;
					$appproductmodel->product_id = $prd->product_id;
					$appproductmodel->product_type_id = $prd->product_type_id;
					$appproductmodel->product_name = $prd->product_name;
					$appproductmodel->product_type_name = $prd->product_type_name;
					$appproductmodel->wastage = $prd->wastage;
					$appproductmodel->product_addition_type = 1;
					$appproductmodel->save();								
					
					foreach($prd->productmaterial as $productmaterial)
					{
						$appproductmaterialmodel=new ApplicationProductMaterial();
						$appproductmaterialmodel->app_product_id=$appproductmodel->id;
						$appproductmaterialmodel->material_id=$productmaterial->material_id;
						$appproductmaterialmodel->material_type_id=$productmaterial->material_type_id;
						$appproductmaterialmodel->percentage=$productmaterial->percentage;
						
						$appproductmaterialmodel->material_name=$productmaterial->material_name;
						$appproductmaterialmodel->material_type_name=$productmaterial->material_type_name;

						$appproductmaterialmodel->save();  									
					}
					
					foreach($prd->productstandard as $productstandard)
					{

						if($productstandard->standard_id != $standard_id){
							continue;
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
				foreach($appUnit as $unit)
				{
					

					$appunitmodel= ApplicationUnit::find()->where(['id'=>$unit->parent_unit_id])->one();
					//$appunitmodel->app_id=$parent_app_id;
					//$appunitmodel->unit_type=$unit->unit_type;
					//$appunitmodel->code=$unit->code;
					
					/*
					if($unit->unit_type==1 && $model->currentaddress !== null){
						$appunitmodel->name=$currentaddress->company_name;
						$appunitmodel->address=$currentaddress->address;
						$appunitmodel->zipcode=$currentaddress->zipcode;
						$appunitmodel->city=$currentaddress->city;
						$appunitmodel->state_id=$currentaddress->state_id;
						$appunitmodel->country_id=$currentaddress->country_id;
					}else{
						$appunitmodel->name=$unit->name;
						$appunitmodel->address=$unit->address;
						$appunitmodel->zipcode=$unit->zipcode;
						$appunitmodel->city=$unit->city;
						$appunitmodel->state_id=$unit->state_id;
						$appunitmodel->country_id=$unit->country_id;
					}
					
					
					$appunitmodel->no_of_employees=$unit->no_of_employees;
					*/
					if($appunitmodel !== null)
					{
						$unitID = $appunitmodel->id;
						
						//$arrUnitIDs[$unit->id]=$unitID;
						//standards									
						$unitappstandard=$unit->unitappstandard;
						$insertunit = 0;
						if(count($unitappstandard)>0)
						{
							foreach($unitappstandard as $unitstd)
							{
								if($unitstd->standard_id != $standard_id){
									continue;
								}else{
									$insertunit = 1;
								}
								$chkApplicationUnitStandard = ApplicationUnitStandard::find()->where(['unit_id'=>$unitID,'standard_id'=>$unitstd->standard_id,'unit_standard_status'=>0])->one();
								if($chkApplicationUnitStandard === null){
									$appunitstandardmodel=new ApplicationUnitStandard();
									$appunitstandardmodel->unit_id=$unitID;
									$appunitstandardmodel->standard_id=$unitstd->standard_id;
									$appunitstandardmodel->addition_type=1;
									$appunitstandardmodel->save();
								}
								
							}
						}
						if(!$insertunit){
							continue;
						}



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
								$appunitproductmodel->product_addition_type = 1;
								$appunitproductmodel->save();										
							}										
						}	
						
															

						$unitbsector=$unit->unitbusinesssector;
						if(count($unitbsector)>0)
						{									
							foreach($unitbsector as $unitbs)
							{

								$business_sector_id = $unitbs->business_sector_id;
								/*$businessQry = 'SELECT group_concat(standard_id) as standard_ids FROM `tbl_business_sector_group` 
											WHERE `business_sector_id`="'.$business_sector_id.'" group by business_sector_id';
											$command = $connection->createCommand($businessQry);
								$result = $command->queryOne();	
								if($result !==false){
									$bsectorstandard_ids = array_unique(explode(',',$result['standard_ids']));
								}
								*/
								$chkBusiness = ['business_sector_id'=>$business_sector_id,'standard_id'=>$standard_id];
								$relatedsector = Yii::$app->globalfuns->checkBusinessSectorInStandard($chkBusiness);			
								if(!$relatedsector){
									continue;
								}



								$chkApplicationUnitBusinessSector = ApplicationUnitBusinessSector::find()->where(['unit_id'=>$unitID,'business_sector_id'=>$unitbs->business_sector_id,'unit_business_sector_status'=>0])->one();
								if($chkApplicationUnitBusinessSector === null){
									$appunitbsectorsmodel=new ApplicationUnitBusinessSector();
									$appunitbsectorsmodel->unit_id=$unitID;
									$appunitbsectorsmodel->business_sector_id=$unitbs->business_sector_id;
									$appunitbsectorsmodel->business_sector_name=$unitbs->business_sector_name;
									$appunitbsectorsmodel->addition_type = 1;
									$appunitbsectorsmodel->save(); 
								}else{
									$appunitbsectorsmodel = $chkApplicationUnitBusinessSector;
								}
								

								$unitbsectorgp=$unitbs->unitbusinesssectorgroup;
								if(count($unitbsectorgp)>0)
								{									
									foreach($unitbsectorgp as $unitbsgp)
									{
										$business_sector_group_id = $unitbsgp->business_sector_group_id;
										$chkBusiness = ['business_sector_group_id'=>$business_sector_group_id,'standard_id'=>$standard_id];
										$relatedsector = Yii::$app->globalfuns->checkBusinessSectorGroupInStandard($chkBusiness);
										if(!$relatedsector){
											continue;
										}
										
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


						
						
						
						$unitprocess=$unit->unitprocessall;
						if(count($unitprocess)>0)
						{										
							foreach($unitprocess as $unitPcs)
							{
								if($standard_id != $unitPcs->standard_id){
									continue;
								}
								$chkApplicationUnitProcess = ApplicationUnitProcess::find()->where(['unit_id'=>$unitID,'process_id'=>$unitPcs->process_id,'standard_id'=>$unitPcs->standard_id,'unit_process_status'=>0])->one();
								if($chkApplicationUnitProcess ===null){
									$appunitprocessesmodel=new ApplicationUnitProcess();
									$appunitprocessesmodel->unit_id=$unitID;
									$appunitprocessesmodel->process_id=$unitPcs->process_id;
									$appunitprocessesmodel->process_name=$unitPcs->process_name;
									$appunitprocessesmodel->standard_id=$unitPcs->standard_id;
									$appunitprocessesmodel->process_type=1;
									$appunitprocessesmodel->save(); 
								}
																		
							}									
						}						
															
						$unitstd=$unit->unitstandard;									
						if(count($unitstd)>0)
						{										
							foreach($unitstd as $unitS)
							{
								$chkApplicationUnitCertifiedStandard = ApplicationUnitCertifiedStandard::find()->where(['unit_id'=>$unitID,'standard_id'=>$unitS->standard_id])->one();
								if($chkApplicationUnitCertifiedStandard === null){

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
			}
		}


		



		
		//$standard_id = $pdata['standard_id'];
	}

	public function addUnitToApp($pdata){

		$connection = Yii::$app->getDb();
		$connection->createCommand("SET SESSION group_concat_max_len = 1000000;")->execute();
		$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();

		
		$standard_id = $pdata['standard_id'];

		$productstandardarr = [];
		$UnitAddition = UnitAddition::find()->where(['new_app_id'=>$pdata['app_id']])->one();
		if($UnitAddition !== null){
			$unitadditionunit = $UnitAddition->additionunit;
			if(count($unitadditionunit) >0 ){
				foreach($unitadditionunit as $additionunit){
					$parent_app_unit_id = $additionunit->parent_app_unit_id;
					if($parent_app_unit_id !='' && $parent_app_unit_id>0){
						$ApplicationUnit = ApplicationUnit::find()->where(['id'=>$parent_app_unit_id])->one();
					}else{
						$ApplicationUnit = new ApplicationUnit();
					}
					$insertstdstatus = 0;
					if(count($additionunit->unitappstandard) > 0){
						foreach($additionunit->unitappstandard as $objunitappstandard){
							if($standard_id != $objunitappstandard->standard_id){
								continue;
							}else if($standard_id == $objunitappstandard->standard_id){
								$insertstdstatus =1;
							}
						}
					}
					if($insertstdstatus==0){
						continue;
					}


					$ApplicationUnit->address = $additionunit->address;
					$ApplicationUnit->name=$additionunit->name;
					$ApplicationUnit->code=$additionunit->code;
					$ApplicationUnit->address=$additionunit->address;
					$ApplicationUnit->zipcode=$additionunit->zipcode;
					$ApplicationUnit->city=$additionunit->city;
					$ApplicationUnit->state_id=$additionunit->state_id;
					$ApplicationUnit->country_id=$additionunit->country_id;
					$ApplicationUnit->no_of_employees=$additionunit->no_of_employees;
					$ApplicationUnit->unit_type=$additionunit->unit_type;
					$ApplicationUnit->unit_addition_type=1;
					$ApplicationUnit->app_id= $pdata['parent_app_id'];//$new_app_id;
					
					$ApplicationUnit->save();
					$newunitID= $ApplicationUnit->id;
					
					if($parent_app_unit_id =='' || $parent_app_unit_id<=0){
						$additionunit->parent_app_unit_id = $newunitID;
						$additionunit->save();
					}

					if(count($additionunit->unitprocessall) > 0){
						foreach($additionunit->unitprocessall as $objunitprocess){
							if($standard_id != $objunitprocess->standard_id){
								continue;
							}
							$newunitprocess = new ApplicationUnitProcess();
							$newunitprocess->unit_id = $newunitID;
							$newunitprocess->standard_id = $objunitprocess->standard_id;
							$newunitprocess->process_id = $objunitprocess->process_id;
							$newunitprocess->process_name = $objunitprocess->process_name;
							$newunitprocess->process_type = 1;
							$newunitprocess->save();
						}
					}

					if(count($additionunit->unitproduct) > 0){
						foreach($additionunit->unitproduct as $objunitproduct){

							if($standard_id != $objunitproduct->product->standard_id){
								continue;
							}
							$appunitproductmodel=new ApplicationUnitProduct();
							$appunitproductmodel->unit_id=$newunitID;
							$appunitproductmodel->product_addition_type = 1;
							$appunitproductmodel->application_product_standard_id=$objunitproduct->application_product_standard_id;
							$appunitproductmodel->save();
						}
					}
					if(count($additionunit->unitbusinesssector) > 0){
						foreach($additionunit->unitbusinesssector as $objunitbusinesssector){

							$business_sector_id = $objunitbusinesssector->business_sector_id;
							//SELECT group_concat(standard_id) FROM `tbl_business_sector_group` WHERE `business_sector_id`=4 group by business_sector_id
							/*$businessQry = 'SELECT group_concat(standard_id) as standard_ids FROM `tbl_business_sector_group` 
										WHERE `business_sector_id`="'.$business_sector_id.'" group by business_sector_id';
										$command = $connection->createCommand($businessQry);
							$result = $command->queryOne();	
							if($result !==false){
								$bsectorstandard_ids = array_unique(explode(',',$result['standard_ids']));
							}
							*/
							$chkBusiness = ['business_sector_id'=>$business_sector_id,'standard_id'=>$standard_id];
							$relatedsector = Yii::$app->globalfuns->checkBusinessSectorInStandard($chkBusiness);		
							if(!$relatedsector){
								continue;
							}
							$appunitbsectorsmodel = ApplicationUnitBusinessSector::find()->where(['unit_id'=>$newunitID,'business_sector_id'=>$objunitbusinesssector->business_sector_id,'unit_business_sector_status'=>0])->one();
							if($appunitbsectorsmodel===null){
								$appunitbsectorsmodel=new ApplicationUnitBusinessSector();
								$appunitbsectorsmodel->addition_type =1;
							}

							
							$appunitbsectorsmodel->unit_id=$newunitID;
							$appunitbsectorsmodel->business_sector_id=$objunitbusinesssector->business_sector_id;
							$appunitbsectorsmodel->business_sector_name=$objunitbusinesssector->business_sector_name;
							$appunitbsectorsmodel->save(); 

							
							$ApplicationUnitBusinessSector = ApplicationUnitBusinessSector::find()->where(['unit_id'=>$additionunit->new_unit_id,'business_sector_id'=>$objunitbusinesssector->business_sector_id,'unit_business_sector_status'=>0])->one();
							//echo $additionunit->new_unit_id.'--'.$objunitbusinesssector->business_sector_id;
							if($ApplicationUnitBusinessSector !== null){
								$unitbusinesssectorgroup = $ApplicationUnitBusinessSector->unitbusinesssectorgroup;
								if(count($unitbusinesssectorgroup)>0){
									foreach($unitbusinesssectorgroup as $bgroup){

										$business_sector_group_id = $bgroup->business_sector_group_id;
										$chkBusiness = ['business_sector_group_id'=>$business_sector_group_id,'standard_id'=>$standard_id];
										$relatedsector = Yii::$app->globalfuns->checkBusinessSectorGroupInStandard($chkBusiness);
										if(!$relatedsector){
											continue;
										}

										$NewApplicationUnitBusinessSector = ApplicationUnitBusinessSectorGroup::find()->where(['unit_id'=>$newunitID,'business_sector_group_id'=>$bgroup->business_sector_group_id,'unit_business_sector_group_status'=>0])->one();
										if($NewApplicationUnitBusinessSector===null){
											$NewApplicationUnitBusinessSector=new ApplicationUnitBusinessSectorGroup();
										}
										//$NewApplicationUnitBusinessSector = new ApplicationUnitBusinessSectorGroup();
										$NewApplicationUnitBusinessSector->unit_id = $newunitID;
										$NewApplicationUnitBusinessSector->unit_business_sector_id = $appunitbsectorsmodel->id;
										$NewApplicationUnitBusinessSector->business_sector_group_id = $bgroup->business_sector_group_id;
										$NewApplicationUnitBusinessSector->business_sector_group_name = $bgroup->business_sector_group_name;
										$NewApplicationUnitBusinessSector->standard_id=$bgroup->standard_id;
										$NewApplicationUnitBusinessSector->save();
									}
								}
							}


						}
					}
					if(count($additionunit->unitappstandard) > 0){
						foreach($additionunit->unitappstandard as $objunitappstandard){
							if($standard_id != $objunitappstandard->standard_id){
								continue;
							}
							$appunitstandardmodel=new ApplicationUnitStandard();
							$appunitstandardmodel->unit_id=$newunitID;
							$appunitstandardmodel->standard_id=$objunitappstandard->standard_id;
							$appunitstandardmodel->save(); 
						}
					}

					if($parent_app_unit_id =='' || $parent_app_unit_id<=0){
						if(count($additionunit->unitstandard) > 0){
							foreach($additionunit->unitstandard as $objunitstandard){
								$appunitcertifiedstdmodel=new ApplicationUnitCertifiedStandard();
								$appunitcertifiedstdmodel->unit_id=$newunitID;
								$appunitcertifiedstdmodel->standard_id=$objunitstandard->standard_id;
								
								$appunitcertifiedstdmodel->license_number=$objunitstandard->license_number;
								$appunitcertifiedstdmodel->expiry_date=$objunitstandard->expiry_date;

								if($appunitcertifiedstdmodel->save()){
									if(count($objunitstandard->unitstandardfile)>0){
										foreach($objunitstandard->unitstandardfile as $additionstdfile){
											$appunitcertifiedstdfilemodel=new ApplicationUnitCertifiedStandardFile();
											$appunitcertifiedstdfilemodel->unit_certified_standard_id=$appunitcertifiedstdmodel->id;
											$appunitcertifiedstdfilemodel->file=$additionstdfile->file;
											$appunitcertifiedstdfilemodel->type=$additionstdfile->type;
											$appunitcertifiedstdfilemodel->save(); 
										}
										
									}
									
								}
								
							}
						}
					}
				}
			}
		}
	}
	public function addProcessToApp($pdata){
		
		$ProcessAddition = ProcessAddition::find()->where(['new_app_id'=>$pdata['app_id']])->one();
		if($ProcessAddition !== null){
			//$processadditionunit = ProcessAdditionUnit::find()->where(['process_addition_id'=>$ProcessAddition->id])->all();
			$processadditionunit = $ProcessAddition->additionunit;
			if(count($processadditionunit) >0 ){
				foreach($processadditionunit as $additionunit){
					$unit_id = $additionunit->unit_id;
					if(count($additionunit->unitprocessall) > 0){
						foreach($additionunit->unitprocessall as $objunitprocess){
							$process_id = $objunitprocess->process_id;
							$standard_id = $objunitprocess->standard_id;
							$process_name = $objunitprocess->process_name;
							if($pdata['standard_id'] == $standard_id){
								$ApplicationUnitProcess = ApplicationUnitProcess::find()->where(['standard_id'=>$pdata['standard_id'], 'unit_id'=>$unit_id,'process_id'=>$process_id,'unit_process_status'=>0])->one();
								if($ApplicationUnitProcess === null){
									$newunitprocess = new ApplicationUnitProcess();
									$newunitprocess->unit_id = $unit_id;
									$newunitprocess->process_id = $process_id;
									$newunitprocess->process_name = $process_name;
									$newunitprocess->standard_id = $pdata['standard_id'];
									$newunitprocess->process_type = 1;
									$newunitprocess->save();
								}
							}
							
							
						}
					}
				}
			}
		}
		
	}
	public function actionView()
	{
		if(!Yii::$app->userrole->hasRights(array('certification_review')))
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Review data not found');
		
        if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();
			$userData = Yii::$app->userdata->getData();


			$model = CertificateReviewerReview::find()->where(['audit_plan_id' => $data['audit_plan_id']])->one();
			if ($model !== null)
			{
				$reviewarr=[];
				$reviewcommentarr=[];
				$certificatereviewerreview=$model->certificatereviewerreview;
				if(count($certificatereviewerreview)>0)
				{
					foreach($certificatereviewerreview as $reviewComment)
					{
						$reviewcommentarr[]=array(
							'audit_plan_review_id'=>$reviewComment->audit_plan_review_id,
							'question_id'=>$reviewComment->question_id,
							'question'=>$reviewComment->question,
							'answer'=>$reviewComment->answer,
							'comment'=>$reviewComment->comment
						);
					}	
				}
				$data['auditplanreviewcomment'] = $reviewcommentarr;

				
				$data['status'] = 1;
				return $data;
			}

		}
		return $responsedata;
	}

	public function actionIndex()
	{
		if(!Yii::$app->userrole->hasRights(array('certification_review')))
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Review data not found');
		$data = Yii::$app->request->post();
		
		if($data)
		{
			$certificate_id = $data['certificate_id'];
			$modelCertificate = Certificate::find()->where(['id' => $certificate_id])->one();
			if($modelCertificate!==null)
			{			
				$reviewmodel =new CertificateReviewerReview();
				$riskoptions = AuditReviewerRiskCategory::find()->select('id,name')->where(['status'=>0])->asArray()->all();
						
				$model = AuditReviewerQuestions::find()->joinWith('questionstandard as qstd')->where(['status'=>0]);
				$model = $model->andWhere('qstd.standard_id='.$modelCertificate->standard_id);
				$model = $model->all();		
				$qdata = [];
				if(count($model)>0)
				{
					foreach($model as $obj)
					{
						$data=array();
						$data['id']=$obj->id;
						$data['name']=$obj->name;
						$data['guidance']=$obj->guidance;		
						$findings=$obj->riskcategory;
						$findingsval=[];
						foreach($findings as $val)
						{
							$opt=[];
							$opt['id']=$val->audit_reviewer_finding_id;
							$opt['name']=$val->category->name;
							$findingsval[]=$opt;
						}
						$data['findings']=$findingsval;
						$qdata[]=$data;
					}
				}
				return ['data'=>$qdata,'risklist'=>$riskoptions];
			}	
		}
		return $responsedata;		
		
	}
	

	/*
	To take history of the application product data
	*/
	public function storeApplicationProductHistory($app_id,$certificate_id){
		//return true;
		$Certificate = Certificate::find()->where(['id'=>$certificate_id])->one();
		$standard_id = $Certificate->standard_id;

		$ApplicationProductCertificateTemp = ApplicationProductCertificateTemp::find()->where(['app_id'=>$app_id,'certificate_id'=>$certificate_id])->all();
		if(count($ApplicationProductCertificateTemp)>0){
			foreach($ApplicationProductCertificateTemp as $certtemp){
				$ApplicationProductStandard = ApplicationProductStandard::find()->where(['id'=>$certtemp->application_product_standard_id,'standard_id'=>$standard_id ])->one();
				if($ApplicationProductStandard === null){
					continue;
				}


				$ApplicationProductHistory = ApplicationProductHistory::find()->where(['application_product_id'=>$certtemp->product_id])->one();

				if($ApplicationProductHistory === null && $certtemp->product !== null){
					$ApplicationProductCertificateTempIns = new ApplicationProductHistory();
					$ApplicationProductCertificateTempIns->app_id = $app_id;
					$ApplicationProductCertificateTempIns->product_id = $certtemp->product->product_id;
					$ApplicationProductCertificateTempIns->product_type_id = $certtemp->product->product_type_id;
					$ApplicationProductCertificateTempIns->product_name = $certtemp->product->product_name;
					$ApplicationProductCertificateTempIns->product_type_name = $certtemp->product->product_type_name;
					$ApplicationProductCertificateTempIns->wastage = $certtemp->product->wastage;
					$ApplicationProductCertificateTempIns->application_product_id = $certtemp->product->id;
					$ApplicationProductCertificateTempIns->certificate_id = $certificate_id;

					if($ApplicationProductCertificateTempIns->save()){
						if(count($certtemp->productmaterial)>0){
							foreach($certtemp->productmaterial as $pdtmaterial){
								$ApplicationProductMaterialHistory = new ApplicationProductMaterialHistory();
								$ApplicationProductMaterialHistory->app_product_history_id = $ApplicationProductCertificateTempIns->id;
								$ApplicationProductMaterialHistory->material_id = $pdtmaterial->material_id;
								$ApplicationProductMaterialHistory->material_type_id = $pdtmaterial->material_type_id;
								$ApplicationProductMaterialHistory->material_name = $pdtmaterial->material_name;
								$ApplicationProductMaterialHistory->material_type_name = $pdtmaterial->material_type_name;
								$ApplicationProductMaterialHistory->percentage = $pdtmaterial->percentage;
								$ApplicationProductMaterialHistory->save();
							}
							
						}
						if($certtemp->productstandard !== null){
							$productstandard = $certtemp->productstandard;
							$ApplicationProductStandardHistory = new ApplicationProductStandardHistory();
							$ApplicationProductStandardHistory->application_product_history_id = $ApplicationProductCertificateTempIns->id;
							$ApplicationProductStandardHistory->standard_id = $productstandard->standard_id;
							$ApplicationProductStandardHistory->label_grade_id = $productstandard->label_grade_id;
							$ApplicationProductStandardHistory->label_grade_name = $productstandard->label_grade_name;
							$ApplicationProductStandardHistory->save();
						}
						
					}
				}else{
					//$ApplicationProductStandardHistory= ApplicationProductStandardHistory::find()->where(['application_product_history_id'=>$ApplicationProductHistory->id])->one();
					if($certtemp->productstandard !== null){
						$productstandard = $certtemp->productstandard;
						$ApplicationProductStandardHistory = new ApplicationProductStandardHistory();
						$ApplicationProductStandardHistory->application_product_history_id =$ApplicationProductHistory->id;
						$ApplicationProductStandardHistory->standard_id = $productstandard->standard_id;
						$ApplicationProductStandardHistory->label_grade_id = $productstandard->label_grade_id;
						$ApplicationProductStandardHistory->label_grade_name = $productstandard->label_grade_name;
						$ApplicationProductStandardHistory->save();
					}
				}

				$application_product_id = $ApplicationProductStandard->application_product_id;
				$ApplicationProductStandardAll = ApplicationProductStandard::find()->where(['application_product_id'=>$application_product_id])->all();
				if(count($ApplicationProductStandardAll)<=1){
					ApplicationProduct::deleteAll(['id'=>$application_product_id]);
					ApplicationProductMaterial::deleteAll(['app_product_id'=>$application_product_id]);
					ApplicationProductStandard::deleteAll(['application_product_id'=>$application_product_id]);
				}else{
					$ApplicationProductStandard->delete();
				}

				$certtemp->delete();
			}
		}
	}


	/*
	To take history of the application product addition data
	*/
	public function storeApplicationProductAdditionHistory($app_id,$certificate_id,$product_addition_id){
		//return true;
		$Certificate = Certificate::find()->where(['id'=>$certificate_id])->one();
		$standard_id = $Certificate->standard_id;
		$ApplicationProductCertificateTemp = ApplicationProductCertificateTemp::find()->where(['product_addition_id'=>$product_addition_id, 'app_id'=>$app_id,'certificate_id'=>$certificate_id])->all();
		if(count($ApplicationProductCertificateTemp)>0){
			foreach($ApplicationProductCertificateTemp as $certtemp){
				$ProductAdditionProductStandard = ProductAdditionProductStandard::find()->where(['id'=>$certtemp->application_product_standard_id,'standard_id'=>$standard_id ])->one();
				if($ProductAdditionProductStandard === null){
					continue;
				}
				$ApplicationProductHistory = ApplicationProductHistory::find()->where(['application_product_id'=>$certtemp->product_id])->one();

				if($ApplicationProductHistory === null && $certtemp->product !== null){
					$ApplicationProductCertificateTempIns = new ApplicationProductHistory();
					$ApplicationProductCertificateTempIns->app_id = $app_id;
					$ApplicationProductCertificateTempIns->product_id = $certtemp->product->product_id;
					$ApplicationProductCertificateTempIns->product_type_id = $certtemp->product->product_type_id;
					$ApplicationProductCertificateTempIns->product_name = $certtemp->product->product_name;
					$ApplicationProductCertificateTempIns->product_type_name = $certtemp->product->product_type_name;
					$ApplicationProductCertificateTempIns->wastage = $certtemp->product->wastage;
					$ApplicationProductCertificateTempIns->application_product_id = $certtemp->product->id;
					$ApplicationProductCertificateTempIns->product_addition_type = 1;
					$ApplicationProductCertificateTempIns->product_addition_id = $product_addition_id;
					$ApplicationProductCertificateTempIns->certificate_id = $certificate_id;

					if($ApplicationProductCertificateTempIns->save()){
						if(count($certtemp->productmaterial)>0){
							foreach($certtemp->productmaterial as $pdtmaterial){
								$ApplicationProductMaterialHistory = new ApplicationProductMaterialHistory();
								$ApplicationProductMaterialHistory->app_product_history_id = $ApplicationProductCertificateTempIns->id;
								$ApplicationProductMaterialHistory->material_id = $pdtmaterial->material_id;
								$ApplicationProductMaterialHistory->material_type_id = $pdtmaterial->material_type_id;
								$ApplicationProductMaterialHistory->material_name = $pdtmaterial->material_name;
								$ApplicationProductMaterialHistory->material_type_name = $pdtmaterial->material_type_name;
								$ApplicationProductMaterialHistory->percentage = $pdtmaterial->percentage;
								$ApplicationProductMaterialHistory->save();
							}
							
						}
						if($certtemp->productstandard !== null){
							$productstandard = $certtemp->productstandard;
							$ApplicationProductStandardHistory = new ApplicationProductStandardHistory();
							$ApplicationProductStandardHistory->application_product_history_id = $ApplicationProductCertificateTempIns->id;
							$ApplicationProductStandardHistory->standard_id = $productstandard->standard_id;
							$ApplicationProductStandardHistory->label_grade_id = $productstandard->label_grade_id;
							$ApplicationProductStandardHistory->label_grade_name = $productstandard->label_grade_name;
							$ApplicationProductStandardHistory->save();
						}
						
					}
				}else{
					//$ApplicationProductStandardHistory= ApplicationProductStandardHistory::find()->where(['application_product_history_id'=>$ApplicationProductHistory->id])->one();
					if($certtemp->productstandard !== null){
						$productstandard = $certtemp->productstandard;
						$ApplicationProductStandardHistory = new ApplicationProductStandardHistory();
						$ApplicationProductStandardHistory->application_product_history_id =$ApplicationProductHistory->id;
						$ApplicationProductStandardHistory->standard_id = $productstandard->standard_id;
						$ApplicationProductStandardHistory->label_grade_id = $productstandard->label_grade_id;
						$ApplicationProductStandardHistory->label_grade_name = $productstandard->label_grade_name;
						$ApplicationProductStandardHistory->save();
					}
				}

				$product_addition_product_id = $ProductAdditionProductStandard->product_addition_product_id;
				$ProductAdditionProductStandardAll = ProductAdditionProductStandard::find()->where(['product_addition_product_id'=>$product_addition_product_id])->all();
				if(count($ProductAdditionProductStandardAll)<=1){
					//ProductAdditionProduct
					//ProductAdditionProductMaterial
					//ProductAdditionProductStandard
					//foreach($ProductAdditionProductStandard as $)
					//ProductAdditionProduct::find()->where(['id'=>$product_addition_product_id])->one();
					ProductAdditionProduct::deleteAll(['id'=>$product_addition_product_id]);
					ProductAdditionProductMaterial::deleteAll(['product_addition_product_id'=>$product_addition_product_id]);
					ProductAdditionProductStandard::deleteAll(['product_addition_product_id'=>$product_addition_product_id]);
				}else{
					$ProductAdditionProductStandard->delete();
				}

				$certtemp->delete();
				
			}
		}
	}

	
}
