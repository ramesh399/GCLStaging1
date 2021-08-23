<?php
namespace app\modules\audit\controllers;

use Yii;

use yii\web\NotFoundHttpException;

use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\AuditPlanUnit;
use app\modules\audit\models\AuditPlanUnitExecution;
use app\modules\audit\models\AuditPlanReviewer;
use app\modules\audit\models\AuditPlanUnitDate;
use app\modules\audit\models\AuditPlanUnitAuditor;
use app\modules\audit\models\AuditPlanUnitStandard;
use app\modules\audit\models\AuditPlanUnitAuditorDate;
use app\modules\audit\models\AuditPlanUnitStandardAuditor;
use app\modules\audit\models\AuditPlanInspection;
use app\modules\audit\models\AuditPlanInspectionPlan;
use app\modules\master\models\BusinessSectorGroup;
use app\modules\master\models\UserBusinessGroupCode;
use app\modules\master\models\User;

use app\modules\audit\models\AuditPlanCustomerReviewHistory;
use app\modules\audit\models\AuditPlanHistory;
use app\modules\audit\models\AuditPlanUnitHistory;
use app\modules\audit\models\AuditPlanUnitDateHistory;
use app\modules\audit\models\AuditPlanUnitStandardHistory;
use app\modules\audit\models\AuditPlanUnitAuditorHistory;
use app\modules\audit\models\AuditPlanUnitAuditorDateHistory;
use app\modules\audit\models\AuditPlanInspectionHistory;
use app\modules\audit\models\AuditPlanInspectionPlanHistory;
use app\modules\audit\models\AuditPlanReviewHistory;
use app\modules\audit\models\AuditPlanReviewChecklistCommentHistory;
use app\modules\audit\models\AuditPlanUnitReviewChecklistCommentHistory;
use app\modules\audit\models\AuditPlanInspectionPlanInspectorHistory;

use app\modules\application\models\ApplicationUnitBusinessSector;
use app\modules\application\models\ApplicationUnitBusinessSectorGroup;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationUnitStandard;

use app\modules\unannouncedaudit\models\UnannouncedAuditApplication;
use app\modules\unannouncedaudit\models\UnannouncedAuditApplicationUnit;

use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;

use app\modules\offer\models\Offer;
use app\modules\offer\models\Invoice;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditPlanController implements the CRUD actions for Process model.
 */
class FollowupAuditPlanController extends \yii\rest\Controller
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
	
	public function actionIndex()
    {
		return ['1'=>'111']; die;
    }
	
	public function actionCreateAuditPlan()
    {
		$AuditM=new Audit();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        $data = Yii::$app->request->post();
		
		if ($data) 
		{



			 
				$auditmodel = Audit::find()->where(['id' => $data['audit_id']])->one();
				$errors = [];
				foreach ($data['units'] as $value)
				{ 
						
					$unit_id=$value['unit_id'];
					$unit_lead_auditor=$value['unit_lead_auditor'];
					$technical_expert=$value['technical_expert'];
					$translator=$value['translator'];
					$observer=isset($value['observer'])?$value['observer']:'';					
					$appunits = ApplicationUnit::find()->where(['id'=>$unit_id])->one();

					if(1)
					{
						$standard_ids= [];
						$auditorids = [];
						//$auditorids = [];
						$dateformatted= [];
						$sector_group_ids = [];
						if(is_array($value['standard']) && count($value['standard'])>0)
						{
							foreach ($value['standard'] as $stds)
							{ 
								$standard_ids[]=$stds;
							}
						}
						if(is_array($value['auditor']) && count($value['auditor'])>0)
						{
							foreach ($value['auditor'] as $auditor)
							{
								$auditorids[]=$auditor['user_id'];
							}
						}

						if(is_array($value['date']) && count($value['date'])>0)
						{
							foreach ($value['date'] as $date)
							{ 
								//$auditplanunitdatemodel=new AuditPlanUnitDate();
								//$auditplanunitdatemodel->audit_plan_unit_id=$auditPlanUnitID;
								$dateformatted[]=date("Y-m-d",strtotime($date));
								
							}
						}
					}
					$unitssectorgroups = [];
					if($auditmodel->audit_type == 2){
						$sector_group_ids = Yii::$app->globalfuns->getUnannouncedBusinessSectorGroups($auditmodel->id, $unit_id);
					}else{
						$unitbsgroup = ApplicationUnitBusinessSectorGroup::find()->where(['unit_id'=>$unit_id])->all();
					
						if(count($unitbsgroup)>0){
							foreach($unitbsgroup as $gp){
								$sector_group_ids[$gp->business_sector_group_id]=$gp->business_sector_group_id;
							}
						}
					}
					
					
					if($auditmodel !==null){
						$app_id = $auditmodel->app_id;
					}else{
						$app_id = $data['app_id'];
					}

					$auditorsList = $this->getAuditorData($dateformatted,'',$app_id,$standard_ids,$unit_id,$sector_group_ids,$data['audit_id']);
					foreach($auditorsList['sectorwiseusers'] as $userslist){
						//$userslist['userlistIds'];
						//if($auditorids)
						//userlistIds
						$matchedArr =array_intersect($auditorids,$userslist['userlistIds']);
						if(count($matchedArr)<=0){
							$errors[] = '<li>No Auditor for '.$userslist['group_code'].' in '.$appunits->name.'</li>';
						}
					}

					
				}
				if(count($errors)>0){
					return $responsedata=array('status'=>0,'message'=>$errors);
				}
				//echo 'asdf'; die;



			








			$auditInsertStatus=false;
			if(isset($data['audit_id']) && $data['audit_id']!='')
			{
				$auditmodel = Audit::find()->where(['id' => $data['audit_id']])->one();
				if($auditmodel!==null)
				{
					//---------------Store the Audit Plan related data into history table code start here ----------------------
					//$auditObj = Audit::find()->where(['id' => $data['audit_id']])->one();
					//
					$auditObj = Audit::find()->where(['id' => $data['audit_id'],'status'=>$AuditM->arrEnumStatus['followup_rejected_by_customer']])->one();
					if($auditObj!==null)
					{
						$auditPlanObj = $auditObj->auditplan;
						if($auditPlanObj!==null)
						{
							$AuditPlanHistoryModel=new AuditPlanHistory();
							$AuditPlanHistoryModel->audit_id=$auditPlanObj->audit_id;				
							$AuditPlanHistoryModel->application_lead_auditor=$auditPlanObj->application_lead_auditor;
							$AuditPlanHistoryModel->quotation_manday=$auditPlanObj->quotation_manday;
							$AuditPlanHistoryModel->actual_manday=$auditPlanObj->actual_manday;
							$AuditPlanHistoryModel->followup_actual_manday=$auditPlanObj->followup_actual_manday;	
							$AuditPlanHistoryModel->comment=$auditPlanObj->comment;
							$AuditPlanHistoryModel->audit_type=2;
							$AuditPlanHistoryModel->followup_application_lead_auditor=$auditPlanObj->followup_application_lead_auditor;
							$AuditPlanHistoryModel->created_by=$auditPlanObj->followup_created_by;
							$AuditPlanHistoryModel->created_at=$auditPlanObj->followup_created_at;
							$AuditPlanHistoryModel->updated_by=$auditPlanObj->followup_updated_by;
							$AuditPlanHistoryModel->updated_at=$auditPlanObj->followup_updated_at;
							$AuditPlanHistoryModel->save();	
											
							$AuditPlanHistoryID = $AuditPlanHistoryModel->id;
							
							$customerreview = $auditPlanObj->followupcustomerreview;
							$AuditPlanCustomerReviewHistory = new AuditPlanCustomerReviewHistory();
							$AuditPlanCustomerReviewHistory->audit_plan_history_id = $AuditPlanHistoryID;
							$AuditPlanCustomerReviewHistory->user_id = $customerreview->user_id;
							$AuditPlanCustomerReviewHistory->audit_type = 2;
							$AuditPlanCustomerReviewHistory->comment = $customerreview->comment;
							$AuditPlanCustomerReviewHistory->created_by = $customerreview->created_by;
							$AuditPlanCustomerReviewHistory->save();


							$auditPlanUnitObj = $auditPlanObj->followupauditplanunit;
							if(count($auditPlanUnitObj)>0)
							{
								foreach($auditPlanUnitObj as $auditPlanUnit)
								{
									$AuditPlanUnitHistoryModel=new AuditPlanUnitHistory();
									$AuditPlanUnitHistoryModel->audit_plan_history_id = $AuditPlanHistoryID;
									$AuditPlanUnitHistoryModel->app_id=$auditPlanUnit->app_id;
									$AuditPlanUnitHistoryModel->unit_id=$auditPlanUnit->unit_id;
									$AuditPlanUnitHistoryModel->unit_lead_auditor=$auditPlanUnit->followup_unit_lead_auditor;
									$AuditPlanUnitHistoryModel->technical_expert=$auditPlanUnit->followup_technical_expert;
									$AuditPlanUnitHistoryModel->translator=$auditPlanUnit->followup_translator;
									$AuditPlanUnitHistoryModel->observer=$auditPlanUnit->followup_observer;
									$AuditPlanUnitHistoryModel->quotation_manday=$auditPlanUnit->quotation_manday;						
									$AuditPlanUnitHistoryModel->actual_manday=$auditPlanUnit->followup_actual_manday;
									$AuditPlanUnitHistoryModel->status=$auditPlanUnit->status;
									$AuditPlanUnitHistoryModel->followup_status=1;
									$AuditPlanUnitHistoryModel->save();
									
									$auditPlanUnitHistoryID=$AuditPlanUnitHistoryModel->id;
										
									//Audit Plan  Unit Date
									$auditPlanUnitDatesObj = $auditPlanUnit->followupauditplanunitdate;
									if(count($auditPlanUnitDatesObj)>0)
									{
										foreach($auditPlanUnitDatesObj as $auditPlanUnitDate)
										{
											$AuditPlanUnitDateHistoryModel=new AuditPlanUnitDateHistory();
											$AuditPlanUnitDateHistoryModel->audit_plan_unit_history_id=$auditPlanUnitHistoryID;
											$AuditPlanUnitDateHistoryModel->date=$auditPlanUnitDate->date;
											//$AuditPlanUnitDateHistoryModel->lead_auditor_other_date=$auditPlanUnitDate->lead_auditor_other_date;
											$AuditPlanUnitDateHistoryModel->save();
																			
											$auditPlanUnitDate->delete();
										}
									}				

									//Audit Plan  Unit Standard
									$auditPlanUnitStandardsObj = $auditPlanUnit->unitstandard;
									if(count($auditPlanUnitStandardsObj)>0)
									{
										foreach($auditPlanUnitStandardsObj as $auditPlanUnitStandard)
										{
											$AuditPlanUnitStandardHistoryModel=new AuditPlanUnitStandardHistory();
											$AuditPlanUnitStandardHistoryModel->audit_plan_unit_history_id=$auditPlanUnitHistoryID;
											$AuditPlanUnitStandardHistoryModel->standard_id=$auditPlanUnitStandard->standard_id;
											$AuditPlanUnitStandardHistoryModel->save();
											
											//$auditPlanUnitStandard->delete();
										}
									}							
									
									//Audit Plan  Unit Auditors
									$auditPlanUnitAuditorsObj = $auditPlanUnit->followupunitauditors;
									if(count($auditPlanUnitAuditorsObj)>0)
									{
										foreach($auditPlanUnitAuditorsObj as $auditPlanUnitAuditor)
										{
											$AuditPlanUnitAuditorHistoryModel=new AuditPlanUnitAuditorHistory();
											$AuditPlanUnitAuditorHistoryModel->audit_plan_unit_history_id=$auditPlanUnitHistoryID;
											$AuditPlanUnitAuditorHistoryModel->user_id=$auditPlanUnitAuditor->user_id;	
											$AuditPlanUnitAuditorHistoryModel->is_lead_auditor=$auditPlanUnitAuditor->is_lead_auditor;	
											$AuditPlanUnitAuditorHistoryModel->is_justified_user=$auditPlanUnitAuditor->is_justified_user;									
											$AuditPlanUnitAuditorHistoryModel->save();
											
											$auditPlanUnitAuditorHistoryID = $AuditPlanUnitAuditorHistoryModel->id;
																						
											//Audit Plan  Unit Auditor Dates
											$auditPlanUnitAuditorDatesObj = $auditPlanUnitAuditor->auditplanunitauditordate;
											if(count($auditPlanUnitAuditorDatesObj)>0)
											{
												foreach($auditPlanUnitAuditorDatesObj as $auditPlanUnitAuditorDate)
												{
													$AuditPlanUnitAuditorDateHistoryModel=new AuditPlanUnitAuditorDateHistory();
													$AuditPlanUnitAuditorDateHistoryModel->audit_plan_unit_auditor_history_id=$auditPlanUnitAuditorHistoryID;
													$AuditPlanUnitAuditorDateHistoryModel->date=$auditPlanUnitAuditorDate->date;
													$AuditPlanUnitAuditorDateHistoryModel->lead_auditor_other_date=$auditPlanUnitAuditorDate->lead_auditor_other_date;
													$AuditPlanUnitAuditorDateHistoryModel->save();
													
													$auditPlanUnitAuditorDate->delete();
												}
											}
											$auditPlanUnitAuditor->delete();								
										}
									}								
									//$auditPlanUnit->delete();
								}					
							}
							
							//Audit Plan Inspection
							$auditPlanInspectionObj = $auditPlanObj->followupauditplaninspection;
							if($auditPlanInspectionObj!==null)
							{   
								//AuditPlanInspectionHistory  AuditPlanInspectionPlanHistory
								$AuditPlanInspectionHistoryModel=new AuditPlanInspectionHistory();
								$AuditPlanInspectionHistoryModel->audit_plan_history_id = $AuditPlanHistoryID;
								$AuditPlanInspectionHistoryModel->audit_type = 2;
								$AuditPlanInspectionHistoryModel->created_by=$auditPlanInspectionObj->created_by;
								$AuditPlanInspectionHistoryModel->created_at=$auditPlanInspectionObj->created_at;
								$AuditPlanInspectionHistoryModel->updated_by=$auditPlanInspectionObj->updated_by;
								$AuditPlanInspectionHistoryModel->updated_at=$auditPlanInspectionObj->updated_at;					
								$AuditPlanInspectionHistoryModel->save();
								$AuditPlanInspectionHistoryID = $AuditPlanInspectionHistoryModel->id;
								
								//Audit Plan Inspection Plan
								$auditPlanInspectionPlanObj = $auditPlanInspectionObj->auditplaninspectionplan;
								if(count($auditPlanInspectionPlanObj)>0)
								{
									foreach($auditPlanInspectionPlanObj as $auditInspectionPlan)
									{				
										$AuditPlanInspectionPlanHistoryModel=new AuditPlanInspectionPlanHistory();
										$AuditPlanInspectionPlanHistoryModel->audit_plan_inspection_history_id = $AuditPlanInspectionHistoryID;
										$AuditPlanInspectionPlanHistoryModel->application_unit_id=$auditInspectionPlan->application_unit_id;
										//$AuditPlanInspectionPlanHistoryModel->audit_type=$auditInspectionPlan->audit_type;
										$AuditPlanInspectionPlanHistoryModel->activity=$auditInspectionPlan->activity;
										//$AuditPlanInspectionPlanHistoryModel->inspector=$auditInspectionPlan->inspector;
										$AuditPlanInspectionPlanHistoryModel->date=$auditInspectionPlan->date;
										$AuditPlanInspectionPlanHistoryModel->start_time=$auditInspectionPlan->start_time;
										$AuditPlanInspectionPlanHistoryModel->end_time=$auditInspectionPlan->end_time;
										$AuditPlanInspectionPlanHistoryModel->person_need_to_be_present=$auditInspectionPlan->person_need_to_be_present;							
										$AuditPlanInspectionPlanHistoryModel->save();

										$auditplaninspectionplaninspector = $auditInspectionPlan->auditplaninspectionplaninspector;
										if(count($auditplaninspectionplaninspector)>0){
											foreach($auditplaninspectionplaninspector as $planinspector){
												$AuditPlanInspectionPlanInspectorHistory = new AuditPlanInspectionPlanInspectorHistory();
												$AuditPlanInspectionPlanInspectorHistory->audit_plan_inspection_plan_history_id = $AuditPlanInspectionPlanHistoryModel->id;
												$AuditPlanInspectionPlanInspectorHistory->user_id = $planinspector->user_id;
												$AuditPlanInspectionPlanInspectorHistory->save();
											}
										}
										
										
										//$auditInspectionPlan->delete();	
									}
								}
								//$auditPlanInspectionObj->delete();	
							}
							
							$auditPlanReviewObj = $auditPlanObj->followupauditplanreview;
							if($auditPlanReviewObj!==null)
							{
								$AuditPlanReviewHistoryModel=new AuditPlanReviewHistory();
								$AuditPlanReviewHistoryModel->audit_plan_history_id = $AuditPlanHistoryID;
								$AuditPlanReviewHistoryModel->user_id=$auditPlanReviewObj->user_id;
								$AuditPlanReviewHistoryModel->comment=$auditPlanReviewObj->comment;
								$AuditPlanReviewHistoryModel->audit_type=2;
								$AuditPlanReviewHistoryModel->answer=$auditPlanReviewObj->answer;
								$AuditPlanReviewHistoryModel->status=$auditPlanReviewObj->status;
								$AuditPlanReviewHistoryModel->review_result=$auditPlanReviewObj->review_result;
								$AuditPlanReviewHistoryModel->created_by=$auditPlanReviewObj->created_by;
								$AuditPlanReviewHistoryModel->created_at=$auditPlanReviewObj->created_at;
								$AuditPlanReviewHistoryModel->updated_by=$auditPlanReviewObj->updated_by;
								$AuditPlanReviewHistoryModel->updated_at=$auditPlanReviewObj->updated_at;					
								$AuditPlanReviewHistoryModel->save();
								$AuditPlanReviewHistoryID = $AuditPlanReviewHistoryModel->id;
													
								//Audit Plan Review Checklist Comment
								$auditplanreviewchecklistcommentObj = $auditPlanReviewObj->auditplanreviewchecklistcomment;
								if(count($auditplanreviewchecklistcommentObj)>0)
								{
									foreach($auditplanreviewchecklistcommentObj as $auditplanreviewchecklistcmt)
									{
										$AuditPlanReviewChecklistCommentHistoryModel=new AuditPlanReviewChecklistCommentHistory();
										$AuditPlanReviewChecklistCommentHistoryModel->audit_plan_review_history_id = $AuditPlanReviewHistoryID;
										$AuditPlanReviewChecklistCommentHistoryModel->question_id=$auditplanreviewchecklistcmt->question_id;
										$AuditPlanReviewChecklistCommentHistoryModel->question=$auditplanreviewchecklistcmt->question;
										$AuditPlanReviewChecklistCommentHistoryModel->answer=$auditplanreviewchecklistcmt->answer;
										$AuditPlanReviewChecklistCommentHistoryModel->comment=$auditPlanReviewObj->comment;												
										$AuditPlanReviewChecklistCommentHistoryModel->save();
										
										//$auditplanreviewchecklistcmt->delete();	
									}
								}
								
								//Audit Plan Unit Review Checklist Comment
								$auditplanunitreviewchecklistcommentObj = $auditPlanReviewObj->auditplanunitreviewcomment;
								if(count($auditplanunitreviewchecklistcommentObj)>0)
								{
									foreach($auditplanunitreviewchecklistcommentObj as $auditplanunitreviewchecklistcomment)
									{
										$AuditPlanUnitReviewChecklistCommentHistoryModel=new AuditPlanUnitReviewChecklistCommentHistory();
										$AuditPlanUnitReviewChecklistCommentHistoryModel->audit_plan_review_history_id = $AuditPlanReviewHistoryID;
										$AuditPlanUnitReviewChecklistCommentHistoryModel->unit_id=$auditplanunitreviewchecklistcomment->unit_id;
										$AuditPlanUnitReviewChecklistCommentHistoryModel->question_id=$auditplanunitreviewchecklistcomment->question_id;
										$AuditPlanUnitReviewChecklistCommentHistoryModel->question=$auditplanunitreviewchecklistcomment->question;
										$AuditPlanUnitReviewChecklistCommentHistoryModel->answer=$auditplanunitreviewchecklistcomment->answer;
										$AuditPlanUnitReviewChecklistCommentHistoryModel->comment=$auditplanunitreviewchecklistcomment->comment;												
										$AuditPlanUnitReviewChecklistCommentHistoryModel->save();
										
										//$auditplanunitreviewchecklistcomment->delete();	
									}
								}					
								//$auditPlanReviewObj->delete();	
							}
						}
					}	
					
					
					
					//---------------Store the Audit Plan related data into history table code end here ----------------------
		
					$auditInsertStatus=true;	
				}
			}	
			
			if(!$auditInsertStatus)
			{
				//$auditmodel = new Audit();
			}
				
			//$auditmodel->app_id=$data['app_id'];
            //$auditmodel->offer_id=$data['offer_id'];
			//$auditmodel->invoice_id=$data['invoice_id'];
			$userData = Yii::$app->userdata->getData();
			$auditmodel->status= $AuditM->arrEnumStatus['followup_submitted'];
			//$auditmodel->created_by=$userData['userid'];
			
			$appmodel = Application::find()->where(['id' => $data['app_id']])->one();
			$usercode = $appmodel->username->registration_id;
			$appuser_id =  $appmodel->username->id;

			$connection = Yii::$app->getDb();
			/*
			$command = $connection->createCommand("SELECT count(*) AS count FROM `tbl_application` WHERE DATE_FORMAT(FROM_UNIXTIME(created_at), '%Y') = '".date('Y')."' AND created_by='$appuser_id'");
			$result = $command->queryOne();
			if(count($result)>0)
			{
				$appcount = $result['count']+1;
				$usercode = $usercode."/AUD-".date('Y')."-".$appcount;
				$auditmodel->code = $usercode;
			}*/

			if($auditmodel->validate() && $auditmodel->save())
        	{  
		        $auditID = $auditmodel->id;
				//,'audit_type'=> 2
				$model = AuditPlan::find()->where(['audit_id' => $auditID])->one();
				if($model===null)
				{
					$model=new AuditPlan();
				}else{
					//Delete the unit, unit date, unit standard, unit auditor & unit auditor date
					$auditPlanUnitObj = $model->auditplanunit;

					if(count($auditPlanUnitObj)>0)
					{
						foreach($auditPlanUnitObj as $auditPlanUnit)
						{						
							//Audit Plan  Unit Date
							$auditPlanUnitDatesObj = $auditPlanUnit->followupauditplanunitdate;
							if(count($auditPlanUnitDatesObj)>0)
							{
								foreach($auditPlanUnitDatesObj as $auditPlanUnitDate)
								{																
									$auditPlanUnitDate->delete();
								}
							}				

							//Audit Plan  Unit Standard
							/*
							$auditPlanUnitStandardsObj = $auditPlanUnit->unitstandard;
							if(count($auditPlanUnitStandardsObj)>0)
							{
								foreach($auditPlanUnitStandardsObj as $auditPlanUnitStandard)
								{
									$auditPlanUnitStandard->delete();
								}
							}
							*/							
							
							//Audit Plan  Unit Auditors
							$auditPlanUnitAuditorsObj = $auditPlanUnit->followupunitauditors;
							if(count($auditPlanUnitAuditorsObj)>0)
							{
								foreach($auditPlanUnitAuditorsObj as $auditPlanUnitAuditor)
								{
									//Audit Plan  Unit Auditor Dates
									$auditPlanUnitAuditorDatesObj = $auditPlanUnitAuditor->auditplanunitauditordate;
									if(count($auditPlanUnitAuditorDatesObj)>0)
									{
										foreach($auditPlanUnitAuditorDatesObj as $auditPlanUnitAuditorDate)
										{
											$auditPlanUnitAuditorDate->delete();
										}
									}
									$auditPlanUnitAuditor->delete();								
								}
							}								
							//$auditPlanUnit->delete();
						}					
					}
				}				
				//$model->audit_type=2;
				$model->audit_id=$auditID;				
				//$model->application_lead_auditor=$data['application_lead_auditor'];
				$userData = Yii::$app->userdata->getData();
				//if($model->followup_created_by !=''){
					$model->followup_created_by = $userData['userid'];
					$model->followup_created_at = time();
				//}
				$model->followup_updated_by = $userData['userid'];
				$model->followup_updated_at = time();

				//$model->created_by=$userData['userid'];
				if($model->validate() && $model->save())
				{ 
					$auditPlanID = $model->id;
					
					$total_quotation_manday=0;
					$total_actual_manday=0;
					foreach ($data['units'] as $value)
					{ 
						
						$justifiedusersList = $this->getJustifiedUsers($appmodel->franchise_id,$value['unit_id']);
						
						//$auditunitmodel=new AuditPlanUnit();
						$auditunitmodel = AuditPlanUnit::find()->where(['audit_plan_id'=>$auditPlanID,'unit_id'=>$value['unit_id'] ])->one();
						//$auditunitmodel->audit_plan_id=$auditPlanID;
						//$auditunitmodel->audit_type=2;
						
						//auditunitmodel->app_id=$data['app_id'];
						//$auditunitmodel->unit_id=$value['unit_id'];
						$auditunitmodel->followup_unit_lead_auditor=$value['unit_lead_auditor'];
						$auditunitmodel->followup_technical_expert=$value['technical_expert'];
						$auditunitmodel->followup_translator=$value['translator'];
						$auditunitmodel->followup_observer=isset($value['observer'])?$value['observer']:'';
						//$auditunitmodel->quotation_manday=$value['quotation_manday'];
						//$total_quotation_manday+=$value['quotation_manday'];
						$auditunitmodel->followup_actual_manday=$value['actual_manday'];
						$total_actual_manday+=$value['actual_manday'];

						if($auditunitmodel->validate() && $auditunitmodel->save())
        				{
							$auditPlanUnitID = $auditunitmodel->id;
							/*
							if(is_array($value['standard']) && count($value['standard'])>0)
							{
								foreach ($value['standard'] as $stds)
								{ 
									$auditunitstdmodel=new AuditPlanUnitStandard();
									$auditunitstdmodel->audit_plan_unit_id=$auditPlanUnitID;
									$auditunitstdmodel->standard_id=$stds;
									if($auditunitstdmodel->validate() && $auditunitstdmodel->save())
									{
									}
								}
							}
							*/
							if(is_array($value['auditor']) && count($value['auditor'])>0)
							{
								foreach ($value['auditor'] as $auditor)
								{
									$auditunitauditormodel=new AuditPlanUnitAuditor();
									$auditunitauditormodel->audit_plan_unit_id=$auditPlanUnitID;
									$auditunitauditormodel->user_id=$auditor['user_id'];
									$auditunitauditormodel->audit_type=2;
									
									
									if( in_array( $auditor['user_id'], $justifiedusersList) )
									{
										$auditunitauditormodel->is_justified_user=1;
									}

									if($value['unit_lead_auditor']==$auditor['user_id'])
									{
										$auditunitauditormodel->is_lead_auditor=1;
									}

									if($auditunitauditormodel->validate() && $auditunitauditormodel->save())
									{
										$auditPlanUnitAuditorID = $auditunitauditormodel->id;

										$auditordates = [];
										$auditordates = $auditor['date'];
										
										

										if(is_array($auditordates) && count($auditordates)>0)
										{
											foreach ($auditordates as $auditordate)
											{
												$auditunitauditordatemodel=new AuditPlanUnitAuditorDate();
												$auditunitauditordatemodel->audit_plan_unit_auditor_id=$auditPlanUnitAuditorID;
												$auditunitauditordatemodel->date=date("Y-m-d",strtotime($auditordate));
												if($auditunitauditordatemodel->validate())
												{
													$auditunitauditordatemodel->save();
												}
											}
										}
										if($auditunitauditormodel->is_lead_auditor==1){
											$unitdates = $value['date'];
											foreach($unitdates as $auditdate){
												if(!in_array($auditdate,$auditordates)){
													$auditunitauditordatemodel=new AuditPlanUnitAuditorDate();
													$auditunitauditordatemodel->audit_plan_unit_auditor_id=$auditPlanUnitAuditorID;
													$auditunitauditordatemodel->date=date("Y-m-d",strtotime($auditdate));
													$auditunitauditordatemodel->lead_auditor_other_date = 1;
													if($auditunitauditordatemodel->validate())
													{
														$auditunitauditordatemodel->save();
													}
												}
											}
										}

									}
								}
							}

							if(is_array($value['date']) && count($value['date'])>0)
							{
								foreach ($value['date'] as $date)
								{ 
									$auditplanunitdatemodel=new AuditPlanUnitDate();
									$auditplanunitdatemodel->audit_plan_unit_id=$auditPlanUnitID;
									$auditplanunitdatemodel->date=date("Y-m-d",strtotime($date));
									$auditplanunitdatemodel->audit_type=2;
									if($auditplanunitdatemodel->validate())
									{
										$auditplanunitdatemodel->save();
									}
								}
							}
						}
					}

					//$model->quotation_manday=$total_quotation_manday;
					$model->followup_actual_manday=$total_actual_manday;
					$model->followup_application_lead_auditor=$data['application_lead_auditor'];
					
					$model->save();
					//$model->getErrors();
				}				
				$responsedata=array('audit_id'=>$auditmodel->id, 'status'=>1,'message'=>'Audit Plan has been created successfully');
			}
		}
		
		return $this->asJson($responsedata);
	}

	public function actionUpdate()
    {
		echo 'sdf'; die;
		/*
		$auditObj = Audit::find()->where(['id' => $data['audit_id']])->one();
		if($auditObj!==null)
		{
			$auditPlanObj = $auditObj->auditplan;
			if($auditPlanObj!==null)
			{
				$auditPlanUnitObj = $auditPlanObj->auditplanunit;
				if(count($auditPlanUnitObj)>0)
				{
					foreach($auditPlanUnitObj as $auditPlanUnit)
					{
						//Audit Plan  Unit Date
						$auditPlanUnitDatesObj = $auditPlanUnit->auditplanunitdate;
						if(count($auditPlanUnitDatesObj)>0)
						{
							foreach($auditPlanUnitDatesObj as $auditPlanUnitDate)
							{
								//$auditPlanUnitDate->delete();
							}
						}						

						//Audit Plan  Unit Standard
						$auditPlanUnitStandardsObj = $auditPlanUnit->unitstandard;
						if(count($auditPlanUnitStandardsObj)>0)
						{
							foreach($auditPlanUnitStandardsObj as $auditPlanUnitStandard)
							{
								//$auditPlanUnitStandard->delete();
							}
						}							
						
						//Audit Plan  Unit Auditors
						$auditPlanUnitAuditorsObj = $auditPlanUnit->unitauditors;
						if(count($auditPlanUnitAuditorsObj)>0)
						{
							foreach($auditPlanUnitAuditorsObj as $auditPlanUnitAuditor)
							{
								//Audit Plan  Unit Auditor Dates
								$auditPlanUnitAuditorDatesObj = $auditPlanUnitAuditor->auditplanunitauditordate;
								if(count($auditPlanUnitAuditorDatesObj)>0)
								{
									foreach($auditPlanUnitAuditorDatesObj as $auditPlanUnitAuditorDate)
									{
										//$auditPlanUnitAuditorDate->delete();
									}
								}
								//$auditPlanUnitAuditor->delete();								
							}
						}						
						
						//$auditPlanUnit->delete();
					}					
				}
				
				//Audit Plan Inspection
				$auditPlanInspectionObj = $auditPlanObj->auditplaninspection;
				if($auditPlanInspectionObj!==null)
				{
					//Audit Plan Inspection Plan
					$auditPlanInspectionPlanObj = $auditPlanInspectionObj->auditplaninspectionplan;
					if(count($auditPlanInspectionPlanObj)>0)
					{
						foreach($auditPlanInspectionPlanObj as $auditInspectionPlan)
						{
							//$auditInspectionPlan->delete();	
						}
					}
					//$auditPlanInspectionObj->delete();	
				}
				
				$auditPlanReviewObj = $auditPlanObj->auditplanreview;
				if($auditPlanReviewObj!==null)
				{
					//Audit Plan Review Checklist Comment
					$auditplanreviewchecklistcommentObj = $auditPlanReviewObj->auditplanreviewchecklistcomment;
					if(count($auditplanreviewchecklistcommentObj)>0)
					{
						foreach($auditplanreviewchecklistcommentObj as $auditplanreviewchecklistcmt)
						{
							//$auditplanreviewchecklistcmt->delete();	
						}
					}
					
					//Audit Plan Unit Review Checklist Comment
					$auditplanunitreviewchecklistcommentObj = $auditPlanReviewObj->auditplanunitreviewcomment;
					if(count($auditplanunitreviewchecklistcommentObj)>0)
					{
						foreach($auditplanunitreviewchecklistcommentObj as $auditplanunitreviewchecklistcomment)
						{
							//$auditplanunitreviewchecklistcomment->delete();	
						}
					}					
					//$auditPlanReviewObj->delete();	
				}
			}	
		}
		*/
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        $data = Yii::$app->request->post();
		if ($data) 
		{


			$auditmodel = Audit::find()->where(['id' => $data['audit_id']])->one();
			$errors = [];
			foreach ($data['units'] as $value)
			{ 
					
				$unit_id=$value['unit_id'];
				$unit_lead_auditor=$value['unit_lead_auditor'];
				$technical_expert=$value['technical_expert'];
				$translator=$value['translator'];
				$observer=isset($value['observer'])?$value['observer']:'';				
				$appunits = ApplicationUnit::find()->where(['id'=>$unit_id])->one();

				if(1)
				{
					$standard_ids= [];
					$auditorids = [];
					//$auditorids = [];
					$dateformatted= [];
					$sector_group_ids = [];
					if(is_array($value['standard']) && count($value['standard'])>0)
					{
						foreach ($value['standard'] as $stds)
						{ 
							$standard_ids[]=$stds;
						}
					}
					if(is_array($value['auditor']) && count($value['auditor'])>0)
					{
						foreach ($value['auditor'] as $auditor)
						{
							$auditorids[]=$auditor['user_id'];
						}
					}

					if(is_array($value['date']) && count($value['date'])>0)
					{
						foreach ($value['date'] as $date)
						{ 
							//$auditplanunitdatemodel=new AuditPlanUnitDate();
							//$auditplanunitdatemodel->audit_plan_unit_id=$auditPlanUnitID;
							$dateformatted[]=date("Y-m-d",strtotime($date));
							
						}
					}
				}
				$unitssectorgroups = [];
		
				$unitbsgroup = ApplicationUnitBusinessSectorGroup::find()->where(['unit_id'=>$unit_id])->all();
				
				if(count($unitbsgroup)>0){
					foreach($unitbsgroup as $gp){
						$sector_group_ids[$gp->business_sector_group_id]=$gp->business_sector_group_id;
						/*
						,'group_code'=>$gp->group->group_code,'id'=>$gp->id];
						$unitssectorgroupIds[$unit->id][]=$gp->business_sector_group_id;
						*/
					}
				}
				//$unitdata['sector_groups']=$unitssectorgroups;
				//$auditorDates = explode(" | ",$data['dates']);
				/*
				foreach($auditorDates as $auditdate){
					$dateformatted[] = date('Y-m-d', strtotime($auditdate));
				}
				*/
				$auditorsList = $this->getAuditorData($dateformatted,'',$auditmodel->app_id,$standard_ids,$unit_id,$sector_group_ids,$data['audit_id']);
				foreach($auditorsList['sectorwiseusers'] as $userslist){
					//$userslist['userlistIds'];
					//if($auditorids)
					//userlistIds
					$matchedArr =array_intersect($auditorids,$userslist['userlistIds']);
					if(count($matchedArr)<=0){
						$errors[] = '<li>No Auditor for '.$userslist['group_code'].' in '.$appunits->name.'</li>';
					}
				}

				
			}
			if(count($errors)>0){
				return $responsedata=array('status'=>0,'message'=>$errors);
			}

			




			$model = AuditPlan::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$arr_unit_ids = [];
				if(is_array($data['units']) && count($data['units'])>0)
				{
					foreach ($data['units'] as $unitkey => $value)
					{
						$arr_unit_ids[] = $value['unit_id'];
					}
				}
				
				$auditplanUnit=$model->auditplanunit;
				if(count($auditplanUnit)>0)
				{
					foreach($auditplanUnit as $unit)
					{
						$unitstd=$unit->unitstandard;
						if(count($unitstd)>0)
						{
							foreach($unitstd as $unitS)
							{
								$unitS->delete();
							}
						}
						//$unitstdauditors=$unitS->unitstandardauditor;
						if(count($auditplanUnit->unitauditors)>0)
						{
							foreach($unitauditors as $auditors)
							{
								$auditplanunitauditordate=$auditors->auditplanunitauditordate;
								if(count($auditplanunitauditordate)>0)
								{
									foreach($auditplanunitauditordate as $unitauditordate)
									{
										$unitauditordate->delete();
									}
								}
								$auditors->delete();
							}
						}
							
						$unitdate=$unit->auditplanunitdate;
						if(count($unitdate)>0)
						{
							foreach($unitdate as $unitD)
							{
								$unitD->delete();
							}
						}

						if(in_array($unit->id,$arr_unit_ids))
						{
							$unit->delete();			
						}
						
					}
					
				}

				$model->app_id=$data['app_id'];
				$model->offer_id=$data['offer_id'];
				//$model->invoice_id=$data['invoice_id'];
				$model->application_lead_auditor=$data['application_lead_auditor'];
				$userData = Yii::$app->userdata->getData();
				$model->created_by=$userData['userid'];
				if($model->validate() && $model->save())
				{  
					if(is_array($data['units']) && count($data['units'])>0)
					{
						foreach ($data['units'] as $value)
						{ 
							$auditunitmodel=new AuditPlanUnit();
							$auditunitmodel->audit_plan_id=$model->id;
							$auditunitmodel->app_id=$data['app_id'];
							$auditunitmodel->unit_id=$value['unit_id'];
							$auditunitmodel->unit_lead_auditor=$value['unit_lead_auditor'];
							$auditunitmodel->technical_expert=$value['technical_expert'];
							$auditunitmodel->translator=$value['translator'];
							$auditunitmodel->observer=isset($value['observer'])?$value['observer']:'';
							$auditunitmodel->quotation_manday=$value['quotation_manday'];
							$auditunitmodel->actual_manday=$value['actual_manday'];
							if($auditunitmodel->validate() && $auditunitmodel->save())
							{
								if(is_array($value['standard']) && count($value['standard'])>0)
								{
									foreach ($value['standard'] as $stds)
									{ 
										$auditunitstdmodel=new AuditPlanUnitStandard();
										$auditunitstdmodel->audit_plan_unit_id=$auditunitmodel->id;
										$auditunitstdmodel->standard_id=$stds;
										if($auditunitstdmodel->validate() && $auditunitstdmodel->save())
										{
										}
									}
								}
								if(is_array($value['auditor']) && count($value['auditor'])>0)
								{
									foreach ($value['auditor'] as $auditor)
									{
										$auditunitauditormodel=new AuditPlanUnitAuditor();
										$auditunitauditormodel->audit_plan_unit_id=$auditunitstdmodel->id;
										$auditunitauditormodel->user_id=$auditor['user_id'];
										if($value['unit_lead_auditor']==$auditor['user_id'])
										{
											$auditunitauditormodel->is_lead_auditor=1;
										}
	
										if($auditunitauditormodel->validate() && $auditunitauditormodel->save())
										{
											if(is_array($auditor['date']) && count($auditor['date'])>0)
											{
												foreach ($auditor['date'] as $auditordate)
												{
													$auditunitauditordatemodel=new AuditPlanUnitAuditorDate();
													$auditunitauditordatemodel->audit_plan_unit_auditor_id=$auditunitauditormodel->id;
													$auditunitauditordatemodel->date=date("Y-m-d",strtotime($auditordate));
													if($auditunitauditordatemodel->validate())
													{
														$auditunitauditordatemodel->save();
													}
												}
											}
										}
									}
								}
	
								if(is_array($value['date']) && count($value['date'])>0)
								{
									foreach ($value['date'] as $date)
									{ 
										$auditplanunitdatemodel=new AuditPlanUnitDate();
										$auditplanunitdatemodel->audit_plan_unit_id=$auditunitmodel->id;
										$auditplanunitdatemodel->date=date("Y-m-d",strtotime($date));
										if($auditplanunitdatemodel->validate())
										{
											$auditplanunitdatemodel->save();
										}
									}
								}
								
							} 
						}

						$model->quotation_manday=$total_quotation_manday;
						$model->actual_manday=$total_actual_manday;
						$model->save();
					}
					$responsedata=array('audit_plan_id'=>$model->id, 'status'=>1,'message'=>'Audit Plan has been updated successfully');
				}
			}

		}
		return $this->asJson($responsedata);	
	}

	public function generateInvoice(){
		$audit_plan_id = $data['audit_plan_id'];

		$auditplanmodel = AuditPlan::find()->where(['id'=>$audit_plan_id])->one();

		$model=new Invoice();


		$app_id=$auditplanmodel->audit->app_id;
		$appmodel = Application::find()->where(['id' => $app_id])->one();
		
		
		$ospid = $appmodel->franchise_id;

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
		$invoicecode = "SY-".$appmodel->franchise->usercompanyinfo->osp_number."-".$maxid."/".date("Y");
		

		$model->invoice_number=$invoicecode;

		$model->app_id = $app_id;
		

		//actual_manday
		/*
		$model->certification_fee_sub_total
		$model->total_fee
		$model->grand_total_fee
		$model->tax_amount
		$model->total_payable_amount
		$model->conversion_total_payable
		*/







	}

	public function actionView()
    {
		$auditmodel=new Audit();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$data = Yii::$app->request->post();
		
		if ($data) 
		{
			$connection = Yii::$app->getDb();
			
			
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$franchiseid=$userData['franchiseid'];
			$is_headquarters =$userData['is_headquarters'];


			//$chkmodelModel = Audit::find()->where(['id' => $data['id']])->one();

			$resultarr=array();
			$modelModel = Audit::find()->where(['t.id' => $data['id']])->alias('t');
			/*$modelModel->innerJoinWith(['auditplan as auditplan']);
			$modelModel = $modelModel->join('inner join', 'tbl_audit_plan_unit as plan_unit','auditplan.id =plan_unit.audit_plan_id');
			$modelModel = $modelModel->join('inner join', 'tbl_audit_plan_unit_auditor as unit_auditor','plan_unit.id=unit_auditor.audit_plan_unit_id');
			//$modelModel = $modelModel->andWhere('plan_unit.unit_lead_auditor='.$userid.' AND plan_unit.status ='.$modelAuditPlanUnit->arrEnumStatus['awaiting_for_unit_lead_auditor_approval'].'');
		

			


			if($user_type== Yii::$app->params['user_type']['user']  && in_array('audit_execution',$rules) && $chkmodelModel->auditplan->application_lead_auditor !== $userid){
				$modelModel = $modelModel->andWhere('unit_auditor.user_id='.$userid);
			}
			*/

			$modelModel = $modelModel->groupBy(['t.id']);
			$modelModel = $modelModel->one();
			if ($modelModel !== null)
			{
				$resultarr["arrEnumStatus"]=$modelModel->arrEnumStatus;
				
				
				$resultarr["status"]=$modelModel->status;

				

				$resultarr["status_name"]=$auditmodel->arrStatus[$modelModel->status];
				$resultarr["created_by"]=$modelModel->created_by;
				$resultarr["created_by_name"]=$modelModel->created_by?$modelModel->user->first_name.' '.$modelModel->user->last_name:'';
				$resultarr["created_at"]=date($date_format,$modelModel->created_at);
				
				$auditID = $modelModel->id;
				$model = AuditPlan::find()->where(['audit_id' => $auditID]);
				/*
				$model = $model->innerJoinWith(['auditplanunit as auditplanunit']);
				$model = $model->join('inner join', 'tbl_audit_plan_unit_auditor as unit_auditor','auditplanunit.id=unit_auditor.audit_plan_unit_id');
				if($user_type== Yii::$app->params['user_type']['user']  && in_array('audit_execution',$rules) && $modelModel->auditplan->application_lead_auditor !== $userid){
					$model = $model->andWhere('unit_auditor.user_id='.$userid);
				}
				*/

				$model = $model->one();
				
				if ($model !== null)
				{

					
					$resultarr["arrEnumPlanStatus"]=$model->arrEnumStatus;
					$resultarr["plan_status"]=$model->status;
					$resultarr["plan_status_name"]=$model->arrStatus[$model->status];
					
					$resultarr["id"]=$model->id;
					$resultarr["reviewer_id"]=($model->reviewer)?$model->reviewer->reviewer_id:'';
					$resultarr["audit_id"]=$model->audit_id;
					$resultarr["application_lead_auditor_name"]=$model->application_lead_auditor?$model->user->first_name.' '.$model->user->last_name:'';

					$resultarr["application_lead_auditor"]=$model->application_lead_auditor;
					$resultarr["quotation_manday"]=$model->quotation_manday;
					$resultarr["actual_manday"]=$model->actual_manday;
					//$resultarr["status"]=$model->status;
					$resultarr["created_at"]=date($date_format,$model->created_at);
					
					
					$resultarr["company_name"]=$modelModel->application->company_name;
					$resultarr["address"]=$modelModel->application->address;
					$resultarr["zipcode"]=$modelModel->application->zipcode;
					$resultarr["city"]=$modelModel->application->city;
					$resultarr["country_name"]=$modelModel->application->country->name;
					$resultarr["state_name"]=$modelModel->application->state->name;
					
					$resultarr["app_id"]=$modelModel->app_id;
					$resultarr["offer_id"]=$modelModel->offer_id;
					$resultarr["invoice_id"]=$modelModel->invoice_id;


					$auditplanUnit=$model->auditplanunit;
					if(count($auditplanUnit)>0)
					{
						$showCertificateGenerate = 1;
						$auditexestatus = new AuditPlanUnitExecution();
						$totalAuditSubtopic = true;
						$unitarr=array();
						$unitnamedetailsarr=array();
						$unitIds = [];
						$planunitIds = [];
						foreach($auditplanUnit as $unit)
						{
							$unitIds[] = $unit->unit_id;
							$planunitIds[] = $unit->id;

							$unitsarr=array();
							if($model->status == $model->arrEnumStatus['review_completed']){
								$command = $connection->createCommand("SELECT * FROM tbl_audit_plan_unit_execution as exe INNER JOIN tbl_audit_plan_unit_execution_checklist checklist on 
								checklist.audit_plan_unit_execution_id=exe.id where exe.audit_plan_unit_id =".$unit->id." and checklist.answer=2 ");
								$result = $command->queryAll();
								if(count($result)>0){
									$showCertificateGenerate = 0;
								}
								
							}else{
								$showCertificateGenerate =0;
							}
							$chkAuditorIds = [];
							$unitauditors=$unit->unitauditors;
							if(count($unitauditors)>0)
							{
								$unitaudarr=array();
								$unitauditorsarr=array();
								$leadauditor=array();
								foreach($unitauditors as $auditors)
								{
									$audarr=array();
									$audarr['id']=$auditors->id;
									$audarr['user_id']=$auditors->user->id;
									$audarr['display_name']=$auditors->user->first_name." ".$auditors->user->last_name;
									$audarr['is_lead_auditor']=$auditors->is_lead_auditor;
									$audarr['is_justified_user']=$auditors->is_justified_user;
									$chkAuditorIds[] = $auditors->user->id;
									$auditordate=$auditors->auditplanunitauditordate;
									if(count($auditordate)>0)
									{
										$datearr=array();
										foreach($auditordate as $stdauditordate)
										{
											if($stdauditordate->lead_auditor_other_date == 0){
												$datearr[]=date($date_format,strtotime($stdauditordate['date']));
											}
										}
										$audarr['date']=$datearr;
									}
									
									$unitaudarr[]=$audarr;
									if($auditors->is_lead_auditor==1)
									{
										$leadauditor[]=$auditors->user->id;
									}
									
									

								}
								
								$unitsarr["auditors"]=$unitaudarr;
								$unitsarr["auditorIds"]=$chkAuditorIds;
							}

							if($user_type== Yii::$app->params['user_type']['user']  && in_array('audit_execution',$rules) && $modelModel->auditplan->application_lead_auditor !== $userid){
								//$model = $model->andWhere('unit_auditor.user_id='.$userid);
								//$unit->
								if( !in_array($userid,$chkAuditorIds)){
									continue; 
								}
							}


							$auditexe = AuditPlanUnitExecution::find()->where(['audit_plan_unit_id'=>$unit->id])->all();
							$auditsubtopiccount = count($auditexe);
							$subtopicArr = [];
							


							$unitsubtopics = $this->getSubtopic($unit->unit_id,$unit->id);

							foreach($unitsubtopics as $subtopic){
								$subtopic['status']= $subtopic['status']?:0;
								$subtopicArr[] = [
									'id' => $subtopic['id'],
									'name' => $subtopic['name'],
									'display_name' => $subtopic['first_name']?$subtopic['first_name'].' '.$subtopic['last_name']:'NA',
									'executed_date' => $subtopic['executed_date']?date($date_format,$subtopic['executed_date']):'NA',
									'status_name' => $auditexestatus->arrStatus[$subtopic['status']]
								];
							}
							

							
							$unitsarr['id']=$unit->id;
							$unitsarr['unit_id']=$unit->unit_id;
							$unitsarr['unit_name']=$unit->unitdata->name;
							$unitsarr['unit_lead_auditor']=$unit->unitleadauditor->first_name." ".$unit->unitleadauditor->last_name;
							$unitsarr['unit_lead_auditor_id']=$unit->unit_lead_auditor;
							$unitsarr['technical_expert_id']=$unit->technical_expert;
							$unitsarr['translator_id']=$unit->translator;
							$unitsarr['observer']=($unit->observer!='' ? $unit->observer : 'NA');
							$unitsarr['technical_expert']=($unit->unittechnicalexpert)?$unit->unittechnicalexpert->first_name." ".$unit->unittechnicalexpert->last_name:'';
							$unitsarr['translator']=($unit->unittranslator)?$unit->unittranslator->first_name." ".$unit->unittranslator->last_name:'';

							$unitsarr['quotation_manday']=$unit->quotation_manday;
							$unitsarr['actual_manday']=$unit->actual_manday;
							$unitsarr['status']=$unit->status;
							$unitsarr['subtopics']= $subtopicArr;
							$unitsarr['subtopics_count']= $auditsubtopiccount;
							
							// ------ Findings Count Start Here -------	
							$executionlistallObj = $unit->executionlistall;
							$executionlistall=count($executionlistallObj);
														
							$executionlistnoncomformityObj = $unit->executionlistnoncomformity;
							$executionlistnoncomformity=count($executionlistnoncomformityObj);
																	
							$unitsarr['total_findings']= $executionlistall;
							$unitsarr['total_non_conformity']= $executionlistnoncomformity;
							// ------ Findings Count End Here -------
							
							$unitsarr['status_label'] = $unit->arrStatus[$unit->status];
							$unitStatusChangeDate = $unit->status_change_date;
							$unitsarr["status_change_date"]= ($unitStatusChangeDate!='' ? date($date_format,$unitStatusChangeDate) : 'NA');

							if(count($unitsubtopics) != $auditsubtopiccount){
								$totalAuditSubtopic = false;
							}

							$unitnamedetailsarr[$unit->unit_id] = $unit->unitdata->name;

							$unitdate=$unit->auditplanunitdate;
							$unitdatearr=array();
							if(count($unitdate)>0)
							{	
								foreach($unitdate as $unitd)
								{
									$unitdatearr[]=date($date_format,strtotime($unitd->date));
									//echo $date_format.'--'.$unitd->date;
								}
							}
							$unitsarr["date"]=$unitdatearr;

							$unitstd=$unit->unitstandard;
							if(count($unitstd)>0)
							{	
								$unitstdarr=array();
								foreach($unitstd as $unitS)
								{
									$stdsarr=array();
									$stdsarr['id']=$unitS->id;
									$stdsarr['standard_id']=$unitS->standard_id;
									$stdsarr['standard_name']=$unitS->standard->code;
									$unitstdarr[]=$stdsarr;
								}
							}
							$unitsarr["standard"]=$unitstdarr;

							
							
							$unitarr[]=$unitsarr;
						}

						$showSubmitRemediationForAuditor = 0;
						$showSubmitRemediationForReviewer = 0;

						$showSendBackRemediationToCustomer = 0;
						$showSendBackRemediationToAuditor = 0;

						$arrChecklistStatusCnt[0] = 0;
						$arrChecklistStatusCnt[1] = 0;
						$arrChecklistStatusCnt[2] = 0;
						$arrChecklistStatusCnt[3] = 0;
						$arrChecklistStatusCnt[4] = 0;
						$arrChecklistStatusCnt[5] = 0;
						
						$command = $connection->createCommand("SELECT COUNT(checklist.id) as chkcnt,checklist.status as status FROM 
										tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.unit_id IN (".implode(',',$unitIds).") AND checklist.answer=2 GROUP BY checklist.status"); // AND checklist.status = 1
						$result = $command->queryAll();
						$totalChkFinding =0;
						
						if(count($result )>0){
							foreach($result  as $statuschklist){
								$arrChecklistStatusCnt[$statuschklist['status']] = $statuschklist['chkcnt'];
								$totalChkFinding += $statuschklist['chkcnt'];
							}
						}
						
						if($model->status == $model->arrEnumStatus['remediation_in_progress']){
							

							/*
							$command2 = $connection->createCommand("SELECT * FROM  tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.unit_id IN (".implode($unitIds).") AND checklist.answer=2 
										 AND checklist.status in (1,3,4,5) "); // AND checklist.status = 1
							$result2 = $command2->queryAll();
							*/
							$totchk = $arrChecklistStatusCnt[1] + $arrChecklistStatusCnt[3] + $arrChecklistStatusCnt[4] + $arrChecklistStatusCnt[5];

							if($totalChkFinding ==  $totchk){
								$showSubmitRemediationForAuditor = 1;
							}
							
						}

						if($model->status == $model->arrEnumStatus['auditor_review_in_progress']){
							
							/*$command2 = $connection->createCommand("SELECT * FROM  tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.unit_id IN (".implode($unitIds).") AND checklist.answer=2
										  AND checklist.status IN (3,5) "); // AND checklist.status = 1
							$result2 = $command2->queryAll();*/

							$totchk = $arrChecklistStatusCnt[3] + $arrChecklistStatusCnt[5];

							if($arrChecklistStatusCnt[1]<=0 && $arrChecklistStatusCnt[2]<=0 && $totalChkFinding ==  $totchk){
								$showSubmitRemediationForReviewer = 1;
							}
						}

						if($model->status == $model->arrEnumStatus['reviewer_review_in_progress']){
							/*$command2 = $connection->createCommand("SELECT * FROM  tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.unit_id IN (".implode($unitIds).") AND checklist.answer=2
										  AND checklist.status IN (4,5) "); // AND checklist.status = 1
							$result2 = $command2->queryAll();
							*/
							$totchk = $arrChecklistStatusCnt[4] + $arrChecklistStatusCnt[5];

							if($arrChecklistStatusCnt[4] > 0 && $totalChkFinding ==  $totchk){
								$showSendBackRemediationToAuditor = 1;
							}
						}

						if($model->status == $model->arrEnumStatus['auditor_review_in_progress']){
							
							/*$command2 = $connection->createCommand("SELECT * FROM  tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.unit_id IN (".implode($unitIds).") AND checklist.answer=2
										  AND checklist.status IN (2,3,5) "); // AND checklist.status = 1
							$result2 = $command2->queryAll();
							*/
							$totchk = $arrChecklistStatusCnt[2] + $arrChecklistStatusCnt[3] + $arrChecklistStatusCnt[5];
							if($arrChecklistStatusCnt[2] > 0 && $totalChkFinding ==  $totchk){
								$showSendBackRemediationToCustomer = 1;
							}
						}





						$resultarr["showSubmitRemediationForAuditor"]=$showSubmitRemediationForAuditor;
						$resultarr["showSubmitRemediationForReviewer"]=$showSubmitRemediationForReviewer;
						$resultarr["showSendBackRemediationToCustomer"]=$showSendBackRemediationToCustomer;
						$resultarr["showSendBackRemediationToAuditor"]=$showSendBackRemediationToAuditor;

						//$unitIds
						$resultarr["showCertificateGenerate"]=$showCertificateGenerate;
						$resultarr["units"]=$unitarr;							
						$auditplanunitmodel=new AuditPlanUnit();
						$resultarr["arrUnitEnumStatus"]=$auditplanunitmodel->arrEnumStatus;
					}
					
					$auditinspection=$model->auditplaninspection;
					if($auditinspection!==null)
					{	
						//$auditinspectionarr=array();
						$planarr=array();
						$auditinspectionplan=$auditinspection->auditplaninspectionplan;
						foreach($auditinspectionplan as $arr)
						{
							$temparr=array();
							$temparr["inspection_id"]=$arr->id;
							$temparr["application_unit_name"]=($arr->applicationunit!==null ? $arr->applicationunit->name : 'NA');
							$temparr["activity"]=$arr->activity;
							$temparr["inspector"]=$arr->inspector;
							$temparr["inspector"]=$arr->inspector;
							$temparr["date"]=date($date_format,strtotime($arr->date));
							$temparr["start_time"]=date('G:i', strtotime($arr->start_time));
							$temparr["end_time"]=date('G:i', strtotime($arr->end_time));
							$temparr["person_need_to_be_present"]=$arr->person_need_to_be_present;
							$temparr["application_unit_id"]=($arr->applicationunit!==null ? $arr->applicationunit->id : 'NA');
							$temparr["application_unit_name"]=($arr->applicationunit!==null ? $arr->applicationunit->name : 'NA');							
							$planarr[]=$temparr;
						}
						//$auditinspectionarr[]=$planarr;											
						$resultarr["inspectionplan"]=$planarr;
					}


					$auditreviews=[];
					$reviewarr=[];
					$reviewcommentarr=[];
					$review=$model->auditplanreview;
					if($review !== null)
					{
						//foreach($auditReview as $review)
						if(1)
						{
							$reviewarr=[];
							$reviewcommentarr=[];
							$auditreviewcmt=$review->auditplanreviewchecklistcomment;
							if(count($auditreviewcmt)>0)
							{
								foreach($auditreviewcmt as $reviewComment)
								{
									$reviewcommentarr[]=array('question'=>$reviewComment->question,'answer'=>$reviewComment->auditplanreviewanswer->name,'comment'=>$reviewComment->comment);
								}	
							}
							

							$unitreviews=[];
							$unitreviewarr=[];
							$unitreviewcommentarr=[];
							$unitauditreviewcmt=$review->auditplanunitreviewcomment;
							if(count($unitauditreviewcmt)>0)
							{
								foreach($unitauditreviewcmt as $unitreviewComment)
								{
									$unitreviewcommentarr[$unitreviewComment->unit_id][] = array(
											'question'=>$unitreviewComment->question,'answer'=>$unitreviewComment->auditplanreviewanswer->name,'comment'=>$unitreviewComment->comment
										);
											
											
								}	
								//print_r($unitnamedetailsarr); die;
								foreach($unitreviewcommentarr as $unitkey => $units)
								{
									if(isset($unitnamedetailsarr[$unitkey]))
									{
										$unitreviews[]=array('unit_name'=>$unitnamedetailsarr[$unitkey],'unit_id'=>$unitkey,'reviews'=>$units);
									}
								}
							}
							
							
							
							$reviewarr['reviewcomments']=$reviewcommentarr;
							$reviewarr['unitreviewcomments']=$unitreviews;
							$reviewarr['reviewer']=($review->reviewer?$review->reviewer->first_name.' '.$review->reviewer->last_name:'');
							$reviewarr['answer']=$review->answer;
							
							$reviewarr['answer_name']=$review->answer?$review->arrReviewAnswer[$review->answer]:'NA';
							
							$reviewarr['status']=$review->status;		
							$reviewarr['status_name']=$review->arrReviewStatus[$review->status];					
																	
							$reviewarr['created_at']=date($date_format,$review->created_at);

							$reviewarr['status_comments']=$review->comment;
							$reviewarr['review_result_name']=isset($review->arrReviewResult[$review->review_result])?$review->arrReviewResult[$review->review_result]:'';
							$reviewarr['review_result']=$review->review_result;

							$auditreviews[]=$reviewarr;
						}
						$resultarr["auditreviews"]=$auditreviews;
					}
					$resultarr['totalAuditSubtopicAnswered']=isset($totalAuditSubtopic)?$totalAuditSubtopic:0;					
				}
				
				$arr_history_data=array();
				$auditPlanHistory = $modelModel->auditplanhistory;
				if(count($auditPlanHistory)>0)
				{
					foreach($auditPlanHistory as $auditPlan)
					{
						$arr_history=array();
						
						$arr_history["id"]=$auditPlan->id;
						$arr_history["application_lead_auditor"]=$auditPlan->application_lead_auditor;
						$arr_history["quotation_manday"]=$auditPlan->quotation_manday;
						$arr_history["actual_manday"]=$auditPlan->actual_manday;
						$arr_history["created_at"]=date($date_format,$auditPlan->created_at);
						
						
						$arr_history["company_name"]=$modelModel->application->company_name;
						$arr_history["address"]=$modelModel->application->address;
						$arr_history["zipcode"]=$modelModel->application->zipcode;
						$arr_history["city"]=$modelModel->application->city;
						$arr_history["country_name"]=$modelModel->application->country->name;
						$arr_history["state_name"]=$modelModel->application->state->name;


						$auditplanUnit=$auditPlan->auditplanunithistory;
						if(count($auditplanUnit)>0)
						{
							$unitarr=array();
							$unitnamedetailsarr=array();
							foreach($auditplanUnit as $unit)
							{
								$unitsarr=array();
								$unitsarr['id']=$unit->id;
								$unitsarr['unit_id']=$unit->unit_id;
								$unitsarr['unit_name']=$unit->unitdata->name;
								$unitsarr['unit_lead_auditor']=$unit->unitleadauditor->first_name." ".$unit->unitleadauditor->last_name;
								$unitsarr['unit_lead_auditor_id']=$unit->unit_lead_auditor;
								$unitsarr['technical_expert_id']=$unit->technical_expert;
								$unitsarr['translator_id']=$unit->translator;
								$unitsarr['observer']=$unit->observer;
								$unitsarr['technical_expert']=($unit->unittechnicalexpert)?$unit->unittechnicalexpert->first_name." ".$unit->unittechnicalexpert->last_name:'';
								$unitsarr['translator']=($unit->unittranslator)?$unit->unittranslator->first_name." ".$unit->unittranslator->last_name:'';

								$unitsarr['quotation_manday']=$unit->quotation_manday;
								$unitsarr['actual_manday']=$unit->actual_manday;
								$unitsarr['status']=$unit->status;

								$unitnamedetailsarr[$unit->unit_id] = $unit->unitdata->name;

								$unitdate=$unit->auditplanunitdatehistory;
								$unitdatearr=array();
								if(count($unitdate)>0)
								{	
									foreach($unitdate as $unitd)
									{
										$unitdatearr[]=date($date_format,strtotime($unitd->date));
										//echo $date_format.'--'.$unitd->date;
									}
								}
								$unitsarr["date"]=$unitdatearr;

								$unitstd=$unit->unitstandardhistory;
								if(count($unitstd)>0)
								{	
									$unitstdarr=array();
									foreach($unitstd as $unitS)
									{
										$stdsarr=array();
										$stdsarr['id']=$unitS->id;
										$stdsarr['standard_id']=$unitS->standard_id;
										$stdsarr['standard_name']=$unitS->standard->name;
										$unitstdarr[]=$stdsarr;
									}
								}
								$unitsarr["standard"]=$unitstdarr;

								$unitauditors=$unit->unitauditorshistory;
								if(count($unitauditors)>0)
								{
									$unitaudarr=array();
									$unitauditorsarr=array();
									$leadauditor=array();
									foreach($unitauditors as $auditors)
									{
										$audarr=array();
										$audarr['id']=$auditors->id;
										$audarr['user_id']=$auditors->user->id;
										$audarr['display_name']=$auditors->user->first_name." ".$auditors->user->last_name;
										$audarr['is_lead_auditor']=$auditors->is_lead_auditor;

										$auditordate=$auditors->auditplanunitauditordatehistory;
										if(count($auditordate)>0)
										{
											$datearr=array();
											foreach($auditordate as $stdauditordate)
											{
												$datearr[]=date($date_format,strtotime($stdauditordate['date']));
											}
											$audarr['date']=$datearr;
										}
										
										$unitaudarr[]=$audarr;
										if($auditors->is_lead_auditor==1)
										{
											$leadauditor[]=$auditors->user->id;
										}
										
										

									}
									
									$unitsarr["auditors"]=$unitaudarr;
								}
								
								$unitarr[]=$unitsarr;
							}
							$arr_history["units"]=$unitarr;							
							//$auditplanunitmodel=new AuditPlanUnit();
							//$arr_history["arrUnitEnumStatus"]=$auditplanunitmodel->arrEnumStatus;
						}
						
						
						$auditinspection=$auditPlan->auditplaninspectionhistory;
						if($auditinspection!==null)
						{	
							//$auditinspectionarr=array();
							$planarr=array();
							$auditinspectionplan=$auditinspection->auditplaninspectionplanhistory;
							foreach($auditinspectionplan as $arr)
							{
								$temparr=array();
								$temparr["inspection_id"]=$arr->id;
								$temparr["application_unit_name"]=($arr->applicationunit?$arr->applicationunit->name:'NA');
								$temparr["activity"]=$arr->activity;
								$temparr["inspector"]=$arr->inspector;
								$temparr["inspector"]=$arr->inspector;
								$temparr["date"]=date($date_format,strtotime($arr->date));
								
								$temparr["start_time"]=date('G:i', strtotime($arr->start_time));
								$temparr["end_time"]=date('G:i', strtotime($arr->end_time));
							
								$temparr["person_need_to_be_present"]=$arr->person_need_to_be_present;
								$planarr[]=$temparr;
							}
							//$auditinspectionarr[]=$planarr;											
							$arr_history["inspectionplan"]=$planarr;
						}

						
						$auditreviews=[];
						$reviewarr=[];
						$reviewcommentarr=[];
						$review=$auditPlan->auditplanreviewhistory;
						if($review !== null)
						{
							//foreach($auditReview as $review)
							if(1)
							{
								$reviewarr=[];
								$reviewcommentarr=[];
								$auditreviewcmt=$review->auditplanreviewchecklistcommenthistory;
								if(count($auditreviewcmt)>0)
								{
									foreach($auditreviewcmt as $reviewComment)
									{
										$reviewcommentarr[]=array('question'=>$reviewComment->question,'answer'=>$reviewComment->auditplanreviewanswer->name,'comment'=>$reviewComment->comment);
									}	
								}
								

								$unitreviews=[];
								$unitreviewarr=[];
								$unitreviewcommentarr=[];
								$unitauditreviewcmt=$review->auditplanunitreviewcommenthistory;
								if(count($unitauditreviewcmt)>0)
								{
									foreach($unitauditreviewcmt as $unitreviewComment)
									{
										$unitreviewcommentarr[$unitreviewComment->unit_id][] = array(
												'question'=>$unitreviewComment->question,'answer'=>$unitreviewComment->auditplanreviewanswer->name,'comment'=>$unitreviewComment->comment
											);
												
												
									}	
									//print_r($unitnamedetailsarr); die;
									foreach($unitreviewcommentarr as $unitkey => $units)
									{
										if(isset($unitnamedetailsarr[$unitkey]))
										{
											$unitreviews[]=array('unit_name'=>$unitnamedetailsarr[$unitkey],'unit_id'=>$unitkey,'reviews'=>$units);
										}
									}
								}
								
								
								$reviewarr['reviewcomments']=$reviewcommentarr;
								$reviewarr['unitreviewcomments']=$unitreviews;
								$reviewarr['reviewer']=($review->reviewer?$review->reviewer->first_name.' '.$review->reviewer->last_name:'');
								$reviewarr['answer']=$review->answer;
								
								$reviewarr['answer_name']=$review->answer?$review->arrReviewAnswer[$review->answer]:'NA';
								
								$reviewarr['status']=$review->status;		
								$reviewarr['status_name']=$review->arrReviewStatus[$review->status];					
																		
								$reviewarr['created_at']=date($date_format,$review->created_at);

								$reviewarr['status_comments']=$review->comment;
								$reviewarr['review_result_name']=isset($review->arrReviewResult[$review->review_result])?$review->arrReviewResult[$review->review_result]:'';
								$reviewarr['review_result']=$review->review_result;

								$auditreviews[]=$reviewarr;
							}
							$arr_history["auditreviews"]=$auditreviews;
						}
						
						
						
						$arr_history_data[]=$arr_history;
					}
				}
				
				$resultarr["history"]=$arr_history_data;
				
			}
			return $resultarr;			
		}
	}

	public function actionAssignReviewer()
    {
		//if()
		


		$auditreviewer=new AuditPlanReviewer();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        $data = Yii::$app->request->post();
		
		if ($data) 
		{
			$auditreviewerCheck = AuditPlanReviewer::find()->where(['audit_plan_id'=>$data['audit_plan_id'],'reviewer_status'=>1])->one();
			if($auditreviewerCheck === null)
			{
				$AuditPlanUnitStatus = new AuditPlanUnit();
				$auditreviewer->audit_plan_id=$data['audit_plan_id'];
				$auditreviewer->reviewer_id=$data['reviewer_id'];
				$auditreviewer->reviewer_status=1;
				$auditreviewer->created_at = time();
				if($auditreviewer->validate() && $auditreviewer->save())
				{   
					$auditplan = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
					if($auditplan !== null){
						$auditplan->status = $auditplan->arrEnumStatus['review_in_progress'];
						$auditplan->save();

						$auditplanunit = AuditPlanUnit::find()->where(['audit_plan_id'=>$data['audit_plan_id'],'status'=> $AuditPlanUnitStatus->arrEnumStatus['awaiting_for_unit_lead_auditor_approval'] ])->all();
						if(count($auditplanunit)>0){
							foreach($auditplanunit as $unit){
								$unit->status = $AuditPlanUnitStatus->arrEnumStatus['awaiting_for_reviewer_approval'];
								$unit->save();
							}
						}
					}
					//auditplanunit awaiting_for_reviewer_approval
					//audit plan review_in_progress
					$responsedata=array('status'=>1,'reviewer_id'=>$data['reviewer_id'],'message'=>'Reviewer has been assigned Successfully');
				}
				else
				{
					$responsedata=array('status'=>0,'message'=>$auditreviewer->errors);
				}
			}else{
				$responsedata=array('status'=>0,'message'=>'Reviewer Already Exists');
			}
		}
		return $this->asJson($responsedata);
	}
	
	public function actionViewAuditPlan(){


		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		

		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$appdata = [];
		$data = yii::$app->request->post();
        if ($data) 
		{
			$AuditPlanUnitModel = new AuditPlanUnit();
			$data = yii::$app->request->post();

			//$appdata['auditplanunits'][0] = [];
			
			
			$audit_id = '';
			
			$audit_id = $data['audit_id'];
			$auditmodel = Audit::find()->where(['id'=>$audit_id])->one();

			if($auditmodel !== null ){
				
				if($auditmodel->audit_type=='2'){
					
					$appdata = $this->getUnannouncedAuditData($audit_id);
				}else{
					$auditplan = AuditPlan::find()->where(['audit_id'=>$audit_id])->one();
					$appdata['application_lead_auditor'] = $auditplan->application_lead_auditor;
					$appdata['followup_application_lead_auditor'] = $auditplan->followup_application_lead_auditor;
					
					$appdata['audit_plan_id'] =  $auditplan->id;
					$appdata['followup_status'] =  $auditmodel->followup_status;

					$unitsIds = [];
					$AuditPlanUnit = AuditPlanUnit::find()->where(['audit_plan_id'=>$auditplan->id,'followup_status'=>1])->all();
					if(count($AuditPlanUnit)>0){
						foreach($AuditPlanUnit as $planunit){
							$unitsIds[] = $planunit->unit_id;
						}
					}
					//$appunits = ApplicationUnit::find()->where(['app_id'=>$data['app_id'],''])->all();
					$appunits = ApplicationUnit::find()->where(['id'=>$unitsIds])->all();
	
					
					
	
	
					$unitssectorgroupIds = [];
					foreach($appunits as $unit)
					{
						
						$unitdata = [];
						$unitdata['name']=$unit->name;
						$unitdata['id']=$unit->id;
						$unitdata['address']=$unit->address;
						$unitdata['zipcode']=$unit->zipcode ;
						$unitdata['city']=$unit->city ;
						$unitdata['no_of_employees']=$unit->no_of_employees;
						$unitdata['quotation_manday']=$unit->unitmanday->final_manday;
						//echo $unit->id.'=='; die;
						//echo count($unit->unitstandard); die;
						/*
						$unitssectorgroups = [];
						$unitbsgroup = ApplicationUnitBusinessSectorGroup::find()->where(['unit_id'=>$unit->id])->all();
						
						if(count($unitbsgroup)>0){
							foreach($unitbsgroup as $gp){
								$unitssectorgroups[]=['business_sector_group_id'=>$gp->business_sector_group_id
								,'group_code'=>$gp->group->group_code,'id'=>$gp->id];
								$unitssectorgroupIds[$unit->id][]=$gp->business_sector_group_id;
							}
						}
						$unitdata['sector_groups']=$unitssectorgroups;
						*/
						
						$planunit = AuditPlanUnit::find()->where(['audit_plan_id'=>$auditplan->id,'unit_id'=>$unit->id,'followup_status'=>1])->one();
	
						
						$unit_standard = [];
						foreach($planunit->unitstandard as $standard)
						{
							$standardsarr = [];
							$standardsarr['id'] = $standard->standard_id;
							$standardsarr['name'] = $standard->standard->code;
							$unitdata['standards'][] = $standardsarr;
							$unit_standard[] = $standard->standard_id;
						}
						
						$unitssectorgroups = [];
						$unitbsgroup = ApplicationUnitBusinessSectorGroup::find()->where(['unit_id'=>$unit->id,'standard_id'=>$unit_standard])->all();
						if(count($unitbsgroup)>0){
							foreach($unitbsgroup as $gp){
								$unitssectorgroups[]=['business_sector_group_id'=>$gp->business_sector_group_id
								,'group_code'=>$gp->group->group_code,'id'=>$gp->id];
								$unitssectorgroupIds[$unit->id][]=$gp->business_sector_group_id;
							}
						}
						$unitdata['sector_groups']=$unitssectorgroups;
	
						if($audit_id !=''){
							//$planunit = AuditPlanUnit::find()->where(['audit_plan_id'=>$auditplan->id,'unit_id'=>$unit->id,'followup_status'=>1])->one();
							if($planunit!==null){
	
	
								$unitdata['unit_lead_auditor'] = $planunit->followup_unit_lead_auditor;
								$unitdata['technical_expert'] = $planunit->followup_technical_expert;
								$unitdata['translator'] = $planunit->followup_translator;
								$unitdata['observer'] = $planunit->followup_observer;
								$unitdata['actual_manday'] = $planunit->followup_actual_manday;
								$unitdata['observer'] = $planunit->followup_observer;
								$unitdates = [];
								$unitauditors = [];
								$unitdatesDB = [];
	
								if(count($planunit->followupauditplanunitdate)>0){
									foreach($planunit->followupauditplanunitdate as $date){
										$unitdates[] = date($date_format,strtotime($date->date));
										$unitdatesDB[] = $date->date;
									}
								}
								if(count($planunit->followupunitauditors)>0){
									
									foreach($planunit->followupunitauditors as $unitauditor){
										$unitauditordates = [];
										if(count($unitauditor->auditplanunitauditordate)>0)
										{
											foreach($unitauditor->auditplanunitauditordate as $auditordate){
												if($auditordate->lead_auditor_other_date ==0){
													$unitauditordates[] = date($date_format,strtotime($auditordate->date));
												}
											}
										}
										$standards = [];
										if(is_array($unitauditor->user->userstandard) && count($unitauditor->user->userstandard)>0){
											foreach($unitauditor->user->userstandard as $userstd){
												$standards[] = $userstd->standard_id;
											}
										}
	
										$unitauditors[] =  [
											'user_id' => $unitauditor->user_id,
											'auditor_name' => $unitauditor->user->first_name." ".$unitauditor->user->last_name,
											'auditor_dates' => $unitauditordates,
											'standards_qual' => $standards
										];
									}
									
								}
								$unitdata['auditordetails'] = $unitauditors;
								$unitdata['unitdates'] = $unitdates;
								$auditorList = $this->getAuditorData($unitdatesDB,$unit->id,'',$unit_standard);
								$unitdata['selauditors'] = $auditorList['auditors'];
								$unitdata['seltechnicalExpert'] = $auditorList['technicalExpert'];
								$unitdata['seltranslator'] = $auditorList['translator'];
							}
							
							//auditors'=>$usersListArr,'technicalExpert'=>$expertListArr,'translator'
	
						}
						
						$appdata['units'][] = $unitdata;
	
					}
					$appdata['business_sector_groups_ids']=$unitssectorgroupIds;
				}
				
			}
			
			


			
		}
		

		return $appdata;
			

	}

	public function getUnannouncedAuditData($audit_id){
		$appdata = [];
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$auditModel = Audit::find()->where(['id'=>$audit_id])->one();

		$auditplan = AuditPlan::find()->where(['audit_id'=>$audit_id])->one();
		if($auditplan !==null){
			$appdata['application_lead_auditor'] = $auditplan->application_lead_auditor;
			$appdata['followup_application_lead_auditor'] = $auditplan->followup_application_lead_auditor;
			
			$appdata['audit_plan_id'] =  $auditplan->id;

			$appdata['unannounced_audit_reason'] =  $auditplan->unannounced_audit_reason;
			$appdata['share_plan_to_customer'] =  $auditplan->share_plan_to_customer;

			$appdata['followup_status'] =  $auditModel->followup_status;
			
		}
		
		$unitsIds = [];
		/*$AuditPlanUnit = AuditPlanUnit::find()->where(['audit_plan_id'=>$auditplan->id,'followup_status'=>1])->all();
		if(count($AuditPlanUnit)>0){
			foreach($AuditPlanUnit as $planunit){
				$unitsIds[] = $planunit->unit_id;
			}
		}
		//$appunits = ApplicationUnit::find()->where(['app_id'=>$data['app_id'],''])->all();
		$appunits = ApplicationUnit::find()->where(['id'=>$unitsIds])->all();

		
		

		$appunits = ApplicationUnit::find()->where(['id'=>$unitsIds])->all();
		share audit details: None, Approval Needed, Send mail only
		*/
		$UnannouncedAuditApplication = UnannouncedAuditApplication::find()->where(['audit_id'=>$audit_id])->one();
		if($UnannouncedAuditApplication !== null){
			$appunits = $UnannouncedAuditApplication->unannouncedauditunit;

			if($auditModel->followup_status == 1){
				$unitIds = [];
				$AuditPlanUnit = AuditPlanUnit::find()->where(['audit_plan_id'=>$auditplan->id,'followup_status'=>1])->all();
				if(count($AuditPlanUnit)>0){
					foreach($AuditPlanUnit as $planunit){
						$unitIds[] = $planunit->unit_id;
					}
				}

				$appunits = UnannouncedAuditApplicationUnit::find()->where(['unannounced_audit_app_id'=>$UnannouncedAuditApplication->id, 'unit_id'=>$unitIds ])->all();
			}



			$unitssectorgroupIds = [];
			foreach($appunits as $unannoucedunit)
			{
				$unit = $unannoucedunit->applicationunit;
				$unitdata = [];
				$unitdata['name']=$unit->name;
				$unitdata['id']=$unit->id;
				$unitdata['address']=$unit->address;
				$unitdata['zipcode']=$unit->zipcode ;
				$unitdata['city']=$unit->city ;
				$unitdata['no_of_employees']=$unit->no_of_employees;
				$unitdata['quotation_manday']= 0;//$unit->unitmanday->final_manday;
				
				
				//$planunit = AuditPlanUnit::find()->where(['audit_plan_id'=>$auditplan->id,'unit_id'=>$unit->id ])->one();

				
				$unit_standard = [];
				foreach($unannoucedunit->unannouncedauditunitstandard as $standard)
				{
					$standardsarr = [];
					$standardsarr['id'] = $standard->standard_id;
					$standardsarr['name'] = $standard->standard->code;
					$unitdata['standards'][] = $standardsarr;
					$unit_standard[] = $standard->standard_id;
				}
				
				$unitssectorgroups = [];
				/*
				$unitbsgroup = ApplicationUnitBusinessSectorGroup::find()->where(['unit_id'=>$unit->id,'standard_id'=>$unit_standard])->all();
				if(count($unitbsgroup)>0){
					foreach($unitbsgroup as $gp){
						$unitssectorgroups[]=['business_sector_group_id'=>$gp->business_sector_group_id
						,'group_code'=>$gp->group->group_code,'id'=>$gp->id];
						$unitssectorgroupIds[$unit->id][]=$gp->business_sector_group_id;
					}
				}
				*/

				//$unitbsgroup = ApplicationUnitBusinessSectorGroup::find()->where(['unit_id'=>$unit->id,'standard_id'=>$unit_standard])->all();
				if(count($unannoucedunit->unitbsectorgroups)>0){
					foreach($unannoucedunit->unitbsectorgroups as $gp){
						$unitssectorgroups[]=['business_sector_group_id'=>$gp->business_sector_group_id
						,'group_code'=>$gp->business_sector_group_name,'id'=>$gp->id];
						$unitssectorgroupIds[$unit->id][]=$gp->business_sector_group_id;
					}
				}
				$unitdata['sector_groups']=$unitssectorgroups;

				if($audit_id !='' && $auditplan!== null ){
					$planunit = AuditPlanUnit::find()->where(['audit_plan_id'=>$auditplan->id,'unit_id'=>$unit->id]);
					if($auditModel->followup_status == 1){
						$planunit = $planunit->andWhere(['followup_status' => 1]);
					}
					$planunit = $planunit->one();

					if($planunit!==null){

						if($auditModel->followup_status == 1){
							$unitdata['unit_lead_auditor'] = $planunit->followup_unit_lead_auditor;
							$unitdata['technical_expert'] = $planunit->followup_technical_expert;
							$unitdata['translator'] = $planunit->followup_translator;
							$unitdata['observer'] = $planunit->followup_observer;
							$unitdata['actual_manday'] = $planunit->followup_actual_manday;
							$unitdata['observer'] = $planunit->followup_observer;
						  
							 
						
						
							$unitdates = [];
							$unitauditors = [];
							$unitdatesDB = [];

							if(count($planunit->followupauditplanunitdate)>0){
								foreach($planunit->followupauditplanunitdate as $date){
									$unitdates[] = date($date_format,strtotime($date->date));
									$unitdatesDB[] = $date->date;
								}
							}
							if(count($planunit->followupunitauditors)>0){
								
								foreach($planunit->followupunitauditors as $unitauditor){
									$unitauditordates = [];
									if(count($unitauditor->auditplanunitauditordate)>0)
									{
										foreach($unitauditor->auditplanunitauditordate as $auditordate){
											if($auditordate->lead_auditor_other_date ==0){
												$unitauditordates[] = date($date_format,strtotime($auditordate->date));
											}
										}
									}
									$standards = [];
									if(is_array($unitauditor->user->userstandard) && count($unitauditor->user->userstandard)>0){
										foreach($unitauditor->user->userstandard as $userstd){
											$standards[] = $userstd->standard_id;
										}
									}

									$unitauditors[] =  [
										'user_id' => $unitauditor->user_id,
										'auditor_name' => $unitauditor->user->first_name." ".$unitauditor->user->last_name,
										'auditor_dates' => $unitauditordates,
										'standards_qual' => $standards
									];
								}
								
							}
						}else{
							$unitdata['unit_lead_auditor'] = $planunit->unit_lead_auditor;
							$unitdata['technical_expert'] = $planunit->technical_expert;
							$unitdata['translator'] = $planunit->translator;
							$unitdata['observer'] = $planunit->observer;
							$unitdata['actual_manday'] = $planunit->actual_manday;
							
						  
							 
						
						
							$unitdates = [];
							$unitauditors = [];
							$unitdatesDB = [];

							if(count($planunit->auditplanunitdate)>0){
								foreach($planunit->auditplanunitdate as $date){
									$unitdates[] = date($date_format,strtotime($date->date));
									$unitdatesDB[] = $date->date;
								}
							}
							if(count($planunit->unitauditors)>0){
								
								foreach($planunit->unitauditors as $unitauditor){
									$unitauditordates = [];
									if(count($unitauditor->auditplanunitauditordate)>0)
									{
										foreach($unitauditor->auditplanunitauditordate as $auditordate){
											if($auditordate->lead_auditor_other_date ==0){
												$unitauditordates[] = date($date_format,strtotime($auditordate->date));
											}
										}
									}
									$standards = [];
									if(is_array($unitauditor->user->userstandard) && count($unitauditor->user->userstandard)>0){
										foreach($unitauditor->user->userstandard as $userstd){
											$standards[] = $userstd->standard_id;
										}
									}

									$unitauditors[] =  [
										'user_id' => $unitauditor->user_id,
										'auditor_name' => $unitauditor->user->first_name." ".$unitauditor->user->last_name,
										'auditor_dates' => $unitauditordates,
										'standards_qual' => $standards
									];
								}
								
							}
						}
						$unitdata['auditordetails'] = $unitauditors;
						$unitdata['unitdates'] = $unitdates;
						$auditorList = $this->getAuditorData($unitdatesDB,$unit->id,'',$unit_standard);
						$unitdata['selauditors'] = $auditorList['auditors'];
						$unitdata['seltechnicalExpert'] = $auditorList['technicalExpert'];
						$unitdata['seltranslator'] = $auditorList['translator'];
					}
					
					//auditors'=>$usersListArr,'technicalExpert'=>$expertListArr,'translator'

				}
				
				$appdata['units'][] = $unitdata;

			}
			$appdata['business_sector_groups_ids']=$unitssectorgroupIds;
		}
		return $appdata;
	}

	public function actionViewAuditPlan18Feb2020(){
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		

		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');

		$data = yii::$app->request->post();
        if ($data) 
		{
			$data = yii::$app->request->post();

			$appdata['auditplanunits'][0] = [];
			$appdata = [];
			$appunits = ApplicationUnit::find()->where(['app_id'=>$data['app_id']])->all();
			
			foreach($appunits as $unit)
			{
				$unitdata = [];
				$unitdata['name']=$unit->name;
				$unitdata['id']=$unit->id;
				$unitdata['address']=$unit->address;
				$unitdata['zipcode']=$unit->zipcode ;
				$unitdata['city']=$unit->city ;
				$unitdata['no_of_employees']=$unit->no_of_employees;
				$unitdata['quotation_manday']=$unit->unitmanday->final_manday;
				//echo $unit->id.'=='; die;
				//echo count($unit->unitstandard); die;
				foreach($unit->unitappstandard as $standard)
				{
					$standardsarr = [];
					$standardsarr['id'] = $standard->standard_id;
					$standardsarr['name'] = $standard->standard->code;
					$unitdata['standards'][] = $standardsarr;
				}
				$appdata['units'][] = $unitdata;

			}


			return $appdata;
			
		}
		
	}


	public function actionFindingsReport()
    {
		// $auditreviewer=new AuditPlanReviewer();
		$FindingsContent='';
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        $data = Yii::$app->request->post();
		
		if ($data) 
		{
			$connection = Yii::$app->getDb();
			
			$command = $connection->createCommand("select standard.*, non_conformity.name AS severity_name, checklist.*,question_std.* from tbl_audit_plan_unit_execution_checklist as checklist inner join tbl_audit_execution_question as question on question.id = checklist.question_id inner join tbl_audit_execution_question_standard as question_std on question_std.audit_execution_question_id = question.id inner join tbl_standard as standard on standard.id = question_std.standard_id left join tbl_audit_non_conformity_timeline as non_conformity on non_conformity.id =checklist.severity where checklist.unit_id = '".$data['unit_id']."' ");
			$result = $command->queryAll();
			$usersArr = [];
			$SNo=1;
			if(count($result)>0)
			{

				$FindingsContent.='
				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
					<tr>
						<td style="text-align:center;font-weight:bold;width:20%;background:#2f4985;color: white;border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="8">Findings Report</td>
					</tr>
					<tr>
						<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">SI.No</td>
						<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Question</td>		 	
						<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Answer</td>
						<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Finding</td>
						<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Severity</td>	
						<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Standard</td>
						<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Clause No.</td>
						<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Clause</td>	 
					</tr>';
					
					foreach($result as $data)
					{
						$FindingsContent.='
						<tr>
						<td style="text-align:center;" valign="middle" class="reportDetailLayoutInner">'.$SNo.'</td>
						<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$data['question'].'</td>
						<td style="text-align:center" valign="middle" class="reportDetailLayoutInner">'.($data['answer']!='1'?"No":"Yes").'</td>
						<td style="text-align:left" valign="middle" class="reportDetailLayoutInner">'.$data['finding'].'</td>
						<td style="text-align:center" valign="middle" class="reportDetailLayoutInner">'.$data['severity_name'].'</td>
						<td style="text-align:center" valign="middle" class="reportDetailLayoutInner">'.$data['code'].'</td>
						<td style="text-align:center" valign="middle" class="reportDetailLayoutInner">'.$data['clause_no'].'</td>
						<td style="text-align:left" valign="middle" class="reportDetailLayoutInner">'.$data['clause'].'</td>
						</tr>';	
						$SNo++;
					}
					$FindingsContent.='</table>';

				
					header('Access-Control-Allow-Origin: *');
					header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
					header('Access-Control-Max-Age: 1000');
					header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

					$html='';
					$mpdf = new \Mpdf\Mpdf();

					$html='
					<style>
					table {
					border-collapse: collapse;
					}

					table, td, th {
					border: 1px solid black;
					}
					
					table.reportDetailLayout {
						border: 1px solid #4e85c8;
						border-collapse: collapse;
						width:100%;
						font-size:12px;
						font-family:Arial;
						text-align: left;
						margin-bottom:5px;
						margin-top:5px;
					}
					td.reportDetailLayout {
						text-align: center;
						border: 1px solid #4e85c8;
						font-size:12px;
						font-family:Arial;
						text-align: left;
						background-color:#DFE8F6;
						padding:3px;
					}
					td.reportDetailLayoutHead {
						text-align: center;
						border: 1px solid #4e85c8;
						font-size:12px;
						font-family:Arial;
						text-align: left;
						background-color:#006fc0;
						padding:3px;
						color:#FFFFFF;
					}

					td.reportDetailLayoutInner {
						text-align: center;
						border: 1px solid #4e85c8;
						font-size:12px;
						font-family:Arial;
						text-align: left;
						background-color:#ffffff;
						padding:3px;
					}
					</style>
					<div style="text-align: center;width:20%;display: inline-block;">
						<img src="'.Yii::$app->params['image_files'].'header-img.jpg" border="0">						
					</div>';
					
					$html.= $FindingsContent;				

					$mpdf->WriteHTML($html);
					$mpdf->Output('findings-report.pdf','D');								
					
				
			}
		}
	}
	

	private function getcauditors($from_date,$to_date,$userArr)
    {
		
        if (isset($from_date) && isset($to_date) && $from_date!='' && $to_date!='') 
		{
			$connection = Yii::$app->getDb();
			//tbl_audit_plan_unit_standard
			//tbl_audit_plan_unit_auditor
			$from_date = date('Y-m-d',strtotime($from_date));
			$to_date = date('Y-m-d',strtotime($to_date));
			
			$command = $connection->createCommand("select auditor.user_id as user_id  from `tbl_audit_plan_unit_standard` as planstandard inner join `tbl_audit_plan_unit_auditor` as auditor
			 		on planstandard.id= auditor.audit_plan_unit_id where 
			 		(from_date>='".$from_date."' AND from_date<='".$to_date."' ) or (to_date>='".$from_date."' 
					 AND to_date<='".$to_date."') group by auditor.user_id");
			$result = $command->queryAll();
			$usersArr = [];
			if(count($result)>0){
				foreach($result as $auditdata){
					if(!in_array($auditdata['user_id'],$userArr)){
						$usersArr[] = $auditdata['user_id'];
					}
				}
			}
			$conditionStr = '';
			if(count($usersArr)>0){
				$conditionStr = " and id not in (".implode(',',$usersArr).")";
			}

			$command = $connection->createCommand("SELECT id,first_name ,last_name  FROM tbl_users where user_type=1 ".$conditionStr);
			$result = $command->queryAll();
			$usersListArr = [];
			$leadauditorArr = [];
			if(count($result)>0){
				foreach($result as $userdata){
					if(in_array($userdata['id'],$userArr)){
						$leadauditorArr[]  = ['id'=>$userdata['id'],'name'=>$userdata['first_name'].' '.$userdata['last_name']];
					}
					$usersListArr[] = ['id'=>$userdata['id'],'name'=>$userdata['first_name'].' '.$userdata['last_name']];
				}
			}

			return ['auditors'=>$usersListArr,'lead_auditors'=>$leadauditorArr];
		}else{
			return [];
		}
	}


	public function actionGetauditors()
    {
		/*
		getuserid with standards qualified
		*/
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if (isset($data['dates']) && $data['dates']!='') 
		{
			$auditorDates = explode(" | ",$data['dates']);
			$dateformatted= [];
			foreach($auditorDates as $auditdate){
				$dateformatted[] = date('Y-m-d', strtotime($auditdate));
			}
			$sector_group_ids = isset($data['sector_group_ids'])?$data['sector_group_ids']:[];
			$audit_id = isset($data['audit_id'])?$data['audit_id']:'';
			return $this->getAuditorData($dateformatted,'',$data['app_id'],$data['unitstandards'],$data['unitid'],$sector_group_ids,$audit_id);
		}else{
			return $responsedata;
		}
	}

	private function getAuditorData($auditorDates,$unit_id='',$app_id='',$unitstandards=[],$applicationunitid='',$sector_group_ids=[],$audit_id=''){
		$connection = Yii::$app->getDb();						
		$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();
		/*
		$condition = " ";		
		if($unit_id != ''){
			$condition = " and auditunit.unit_id != ".$unit_id;
		}
		$appcondition = '';
		if($app_id != ''){
			$appcondition = " and auditunit.app_id != ".$app_id;
		}
		*/
		$audit_plan_id = '';
		$auditplancondition = '';
		$AuditPlanModel = AuditPlan::find()->where(['audit_id'=>$audit_id])->one();
		if($AuditPlanModel !== null){
			$audit_plan_id = $AuditPlanModel->id;
			$auditplancondition = " AND auditunit.audit_plan_id != ".$audit_plan_id;
		}


		$condition = " ";
		$appcondition = '';
		$auditplanunitID = '';
		if($applicationunitid != '' && $app_id !='' && $audit_plan_id !=''){
			$AuditPlanUnitModel = AuditPlanUnit::find()->where(['audit_plan_id' => $audit_plan_id,'unit_id'=>$applicationunitid,'app_id' =>$app_id ])->one();
			if($AuditPlanUnitModel !== null){
				$auditplanunitID = $AuditPlanUnitModel->id;
				$condition = " and auditunit.id != ".$auditplanunitID;
			}
			//$condition = " and auditunit.unit_id != ".$unit_id;
		}else if($unit_id != ''){
			$condition = " and auditunit.id != ".$unit_id;
		}
		
		


		$franchise_id = '';
		$franchiseCondition = '';
		$application = Application::find()->where(['id'=>$app_id])->one();
		if($application !== null){
			$franchise_id = $application->franchise_id;
			if($franchise_id !=''){
				$franchiseCondition = ' AND user_role.franchise_id= '.$franchise_id;
			}
		}

		$arrbusinessector = [];

		$unannounced_audit = 0;
		if($audit_id !=''){
			$Audit = Audit::find()->where(['id'=>$audit_id])->one();
			if($Audit->audit_type == $Audit->audittypeEnumArr['unannounced_audit']){
				$unannounced_audit = 1;
				$arrbusinessector = Yii::$app->globalfuns->getUnannouncedBusinessSector($audit_id,$applicationunitid);
			}else if($applicationunitid!=''){
				$businessector = ApplicationUnitBusinessSector::find()->where(['unit_id'=>$applicationunitid])->all();
				if(count($businessector)>0){
					foreach($businessector as $sector){
						$arrbusinessector[] = $sector->business_sector_id;
					}
				}
			}

			//followup_status

			//auditor => audit_type
		}
		/*if(!$unannounced_audit){
			
		}*/
		
		

		//$sector_group_ids

		$command = $connection->createCommand("select auditor.user_id as user_id,GROUP_CONCAT(auditunit.technical_expert) as technical_experts,GROUP_CONCAT(auditunit.translator) as translators from `tbl_audit_plan_unit_auditor_date` as auditordate
			inner join `tbl_audit_plan_unit_auditor` as auditor on 
			auditordate.audit_plan_unit_auditor_id= auditor.id and auditordate.date and auditordate.date in('".implode("','",$auditorDates)."') 
			inner join `tbl_audit_plan_unit` as auditunit on auditunit.id= auditor.audit_plan_unit_id 
			where 1=1 ".$condition."  ".$appcondition."  ".$auditplancondition."
			group by auditor.user_id 
			
			");
			
		$result = $command->queryAll();
		$usersArr = [];		
		$technical_experts = [];		
		$translators = [];
		
		// ------ Commended for Exiting Audit Plan Code Start Here -------------
		 /*
		if(count($result)>0){
			foreach($result as $auditdata){
				$usersArr[] = $auditdata['user_id'];
				$technical_experts = array_unique (array_merge($technical_experts,explode(',',$auditdata['technical_experts'])));
				$translators = array_unique (array_merge($translators,explode(',',$auditdata['translators'])));
				//array_merge($a1,$a2)
			}
		}
			*/	
		// ------ Commended for Exiting Audit Plan Code End Here -------------
		
		/*echo '===';
		print_r($usersArr);
		print_r($technical_experts);
		echo '===';*/
		
		//$usersArr = array_unique ($usersArr);
		$usersArr = array_filter($usersArr);
		$technical_experts = array_filter($technical_experts);
		$translators = array_filter($translators);

		$conditionStr = '';
		if(count($usersArr)>0){
			$conditionStr = " and user.id not in (".implode(',',$usersArr).")";
		}

		$conditionexpertStr = '';
		if(count($technical_experts)>0){
			$conditionexpertStr = " and user.id not in (".implode(',',$technical_experts).")";
		}

		$conditiontranslatorStr = '';
		if(count($translators)>0){
			$conditiontranslatorStr = " and user.id not in (".implode(',',$translators).")";
		}

		$stdcondition = "";
		if(count($unitstandards)>0){
			$stdcondition = " and usrstd.standard_id in(".implode(',',$unitstandards).")";
		}

		$sectorcondition = "";
		if(count($arrbusinessector)>0){
			$sectorcondition = " and usrsector.business_sector_id in(".implode(',',$arrbusinessector).")";
		}


		$usergroupcode = new UserBusinessGroupCode();
		$usermodel = new User();
		//$activeCondition = " AND usrgroupcode.status = ".$usergroupcode->arrEnumStatus['approved']." AND user.status='".$usermodel->arrLoginEnumStatus['active']."'  ";
		$activeCondition = " AND user.status='".$usermodel->arrLoginEnumStatus['active']."'  ";
		/// For getting Justified Users
		//For getting Auditors
		//$franchiseCondition = ' AND user_role.franchise_id= '.$model->franchise_id.' ';
		$sectorwiseusers=[];
		if(count($sector_group_ids)>0){
			
			foreach($sector_group_ids as $sectorid){
				$userlistIds = [];
				$userlistNames = [];

				$auditorlistIds = [];
				$auditorlistNames = [];

				$technicalexpertlistIds = [];
				$technicalexpertlistNames = [];

				//$sectorgpcondition = " and usrgroupcode.business_sector_group_id =".$sectorid." ";
				$sectorgpcondition = " and usrsectorgroupcode.business_sector_group_id =".$sectorid." and usrgroupcode.status = ".$usergroupcode->arrEnumStatus['approved']." ";
				/*
				$command = $connection->createCommand("SELECT user.id,first_name ,last_name 
					FROM tbl_users as user 
					inner join tbl_user_role as user_role on  user_role.user_id = user.id 
					INNER JOIN `tbl_rule` AS rule ON  user_role.role_id=rule.role_id AND rule.privilege='audit_execution' 
					INNER JOIN `tbl_user_business_group` AS usrgroup on usrgroup.user_id = user.id 
					INNER JOIN `tbl_user_business_group_code` AS usrgroupcode on usrgroupcode.business_group_id = usrgroup.id 

					where user_type=1 ".$activeCondition." ".$conditionStr." ".$franchiseCondition." ".$sectorgpcondition."  group by user.id");
				//INNER JOIN `tbl_user_business_sector_group` AS usrsectorgroup on usrsectorgroup.user_id = user.id 
				*/
				$command = $connection->createCommand("SELECT user.id,first_name ,last_name 
					FROM tbl_users as user 
					inner join tbl_user_role as user_role on  user_role.user_id = user.id 
					INNER JOIN `tbl_rule` AS rule ON  user_role.role_id=rule.role_id AND rule.privilege='audit_execution' 
					INNER JOIN `tbl_user_role_business_group` AS usrsectorgroup on usrsectorgroup.user_id = user.id  AND usrsectorgroup.role_id = rule.role_id 
					INNER JOIN `tbl_user_role_business_group_code` AS usrsectorgroupcode on usrsectorgroup.id = usrsectorgroupcode.business_group_id 
					INNER JOIN `tbl_user_business_group_code` AS usrgroupcode on usrgroupcode.id = usrsectorgroupcode.user_business_group_code_id 
					INNER JOIN `tbl_user_standard` AS usrstandard on usrsectorgroup.standard_id = usrstandard.standard_id  AND usrstandard.approval_status = 2  
					where user_role.approval_status=2 and user_type=1 ".$activeCondition." ".$conditionStr." ".$franchiseCondition." ".$sectorgpcondition."  group by user.id");
				

				$result = $command->queryAll();
				$usersListArr = [];
				$auditorListArr = [];
				$technicalexpertListArr = [];

				if(count($result)>0){
					foreach($result as $userdata){
						$usersListArr[] = ['id'=>$userdata['id'],'name'=>$userdata['first_name'].' '.$userdata['last_name']];
						$userlistIds[] = $userdata['id'];
						$userlistNames[] = $userdata['first_name'].' '.$userdata['last_name'];


						$auditorListArr[] = ['id'=>$userdata['id'],'name'=>$userdata['first_name'].' '.$userdata['last_name']];
						$auditorlistIds[] = $userdata['id'];
						$auditorlistNames[] = $userdata['first_name'].' '.$userdata['last_name'];
					}
				}

				//For getting Technical Experts
				//".$conditionStr." ".$stdcondition." ".$franchiseCondition." ".$sectorcondition." 
				/*$technicalcommand = $connection->createCommand("SELECT user.id,first_name ,last_name  FROM tbl_users as user 
				inner join tbl_user_role as user_role on  user_role.user_id = user.id 
				inner join tbl_role as role on  role.id = user_role.role_id  and role.resource_access=3  
				INNER JOIN `tbl_user_business_group` AS usrgroup on usrgroup.user_id = user.id 
					INNER JOIN `tbl_user_business_group_code` AS usrgroupcode on usrgroupcode.business_group_id = usrgroup.id 
						where user.user_type=1  ".$activeCondition."  ".$conditionStr." ".$conditionexpertStr." ".$franchiseCondition." ".$sectorgpcondition." group by user.id");
				*/

				$sectorgpcondition = " and usrsectorgroupcode.business_sector_group_id =".$sectorid." and usrsectorgroupcode.status = ".$usergroupcode->arrEnumStatus['approved']." ";
				$technicalcommand = $connection->createCommand("SELECT user.id,first_name ,last_name  FROM tbl_users as user 
				inner join tbl_user_role as user_role on  user_role.user_id = user.id 
				inner join tbl_role as role on  role.id = user_role.role_id  and role.resource_access=3  
				INNER JOIN `tbl_user_role_technical_expert_business_group` AS usrsectorgroup on usrsectorgroup.user_id = user.id  
				INNER JOIN `tbl_user_role_technical_expert_business_group_code` AS usrsectorgroupcode on usrsectorgroup.id = usrsectorgroupcode.user_role_technical_expert_bs_id 
						
				where user_role.approval_status=2 AND user.user_type=1  ".$activeCondition."  ".$conditionStr." ".$conditionexpertStr." ".$franchiseCondition." ".$sectorgpcondition." group by user.id");
				
				$technicalresult = $technicalcommand->queryAll();
				$technicalListArr = [];
				if(count($technicalresult)>0){
					foreach($technicalresult as $technicaldata){
						//$technicalListArr[] =$technicaldata['first_name'].' '.$technicaldata['last_name'];
						if( !in_array($technicaldata['id'], $userlistIds)){
							$usersListArr[] = ['id'=>$technicaldata['id'],'name'=>$technicaldata['first_name'].' '.$technicaldata['last_name']];
							$userlistNames[] = $technicaldata['first_name'].' '.$technicaldata['last_name'];
							$userlistIds[] = $technicaldata['id'];


							$technicalexpertListArr[] = ['id'=>$technicaldata['id'],'name'=>$technicaldata['first_name'].' '.$technicaldata['last_name']];
							$technicalexpertlistNames[] = $technicaldata['first_name'].' '.$technicaldata['last_name'];
							$technicalexpertlistIds[] = $technicaldata['id'];
						}
					}
				}
				$bsecname = BusinessSectorGroup::find()->where(['id'=>$sectorid])->one();
				$sectorwiseusers[] = [
					'sectorid' => $sectorid,
					'group_code' => $bsecname->group_code,
					'userlist'=>$usersListArr,
					'userlistIds' => $userlistIds,
					'userlistnames'=>implode(', ',$userlistNames),

					'auditorlist'=>$auditorListArr,
					'auditorlistIds' => $auditorlistIds,
					'auditorlistnames'=>implode(', ',$auditorlistNames),

					'technicalexpertlist'=>$technicalexpertListArr,
					'technicalexpertlistIds' => $technicalexpertlistIds,
					'technicalexpertlistnames'=>implode(', ',$technicalexpertlistNames)
				
				];
			}
			//print_r($usersListArr); die;
			//$sectoruserArr = array_merge($technicalListArr,$usersListArr);
			
		}
		/// For getting Justified Users Ends Here








		//$arrbusinessector

		//user_role.franchise_id =='';

		//$command = $connection->createCommand("SELECT id,first_name ,last_name  FROM tbl_users where user_type=1 ".$conditionStr);
		//user_role.franchise_id='.$franchiseID.' AND

		//INNER JOIN `tbl_user_business_group` AS usrgroup on usrgroup.user_id = user.id 
		//INNER JOIN `tbl_user_business_group_code` AS usrgroupcode on usrgroupcode.business_group_id = usrgroup.id 
		/*
		$command = $connection->createCommand("SELECT user.id,first_name ,last_name,group_concat(usrstd.standard_id) as userstandards  
		FROM tbl_users as user 
		inner join tbl_user_role as user_role on  user_role.user_id = user.id 
		INNER JOIN `tbl_rule` AS rule ON  user_role.role_id=rule.role_id AND rule.privilege='audit_execution' 
		INNER JOIN `tbl_user_standard` AS usrstd on usrstd.user_id = user.id 
		INNER JOIN `tbl_user_business_group` AS usrsector on usrsector.user_id = user.id  and usrsector.standard_id = usrstd.standard_id 
		INNER JOIN `tbl_user_business_group_code` AS usrgroupcode on usrgroupcode.business_group_id = usrsector.id 
				where user_type=1   ".$activeCondition." ".$conditionStr." ".$stdcondition." ".$franchiseCondition." ".$sectorcondition." group by user.id");
		*/
		/*$command = $connection->createCommand("SELECT user.id,first_name ,last_name  FROM tbl_users as user 	
					where user_type=1 ".$conditionStr);
		*/

		//$sectorgpcondition = " and usrsectorgroupcode.business_sector_group_id =".$sectorid." and usrgroupcode.status = ".$usergroupcode->arrEnumStatus['approved']." ";
		$command = $connection->createCommand("SELECT user.id,first_name ,last_name,group_concat(usrstd.standard_id) as userstandards  
		FROM tbl_users as user 
		inner join tbl_user_role as user_role on  user_role.user_id = user.id 
		INNER JOIN `tbl_rule` AS rule ON  user_role.role_id=rule.role_id AND rule.privilege='audit_execution' 
		INNER JOIN `tbl_user_standard` AS usrstd on usrstd.user_id = user.id   AND usrstd.approval_status = 2 
		INNER JOIN `tbl_user_role_business_group` AS usrsector on usrsector.user_id = user.id AND usrsector.standard_id = usrstd.standard_id  AND usrsector.role_id = rule.role_id 
		INNER JOIN `tbl_user_role_business_group_code` AS usrsectorgroupcode on usrsector.id = usrsectorgroupcode.business_group_id 
		INNER JOIN `tbl_user_business_group_code` AS usrgroupcode on usrgroupcode.id = usrsectorgroupcode.user_business_group_code_id 
		where usrgroupcode.status = ".$usergroupcode->arrEnumStatus['approved']." and user_role.approval_status=2 and user_type=1   ".$activeCondition." ".$conditionStr." ".$stdcondition." ".$franchiseCondition." ".$sectorcondition." group by user.id");

		


		$result = $command->queryAll();
		$usersListArr = [];
		if(count($result)>0){
			foreach($result as $userdata){
				$usersListArr[] = [
					'id'=>$userdata['id'],'name'=>$userdata['first_name'].' '.$userdata['last_name']
					,'standards_qual' => $userdata['userstandards']
				];
			}
		}


		/*
		$command = $connection->createCommand("select auditor.user_id as user_id from `tbl_audit_plan_unit_auditor_date` as auditordate 
		inner join `tbl_audit_plan_unit_auditor` as auditor 
				on auditordate.audit_plan_unit_auditor_id= auditor.id and auditordate.date not in('".implode("','",$auditorDates)."') group by auditor.user_id");
		$result = $command->queryAll();
		*/

		
		//userrole.franchise_id='.$franchiseID.' AND
		/*$command = $connection->createCommand("select auditor.user_id as user_id from `tbl_audit_plan_unit_auditor_date` as auditordate 
		inner join `tbl_audit_plan_unit_auditor` as auditor on auditordate.audit_plan_unit_auditor_id= auditor.id
		inner join tbl_users as user on auditor.user_id = user.id 
		inner join tbl_user_role as user_role on  user_role.user_id = user.id 
		INNER JOIN `tbl_rule` AS rule ON  user_role.role_id=rule.role_id AND rule.privilege='audit_execution' 
			where auditordate.date not in('".implode("','",$auditorDates)."') group by auditor.user_id");
		$result = $command->queryAll();
		*/
		

		/*
		$technicalexpertcommand = $connection->createCommand("SELECT user.id,first_name ,last_name,group_concat(usrstd.standard_id) as userstandards  FROM tbl_users as user 
		inner join tbl_user_role as user_role on  user_role.user_id = user.id 
		inner join tbl_role as role on  role.id = user_role.role_id  and role.resource_access=3 
		INNER JOIN `tbl_user_standard` AS usrstd on usrstd.user_id = user.id 
		INNER JOIN `tbl_user_business_group` AS usrsector on usrsector.user_id = user.id and usrsector.standard_id = usrstd.standard_id 
		INNER JOIN `tbl_user_business_group_code` AS usrgroupcode on usrgroupcode.business_group_id = usrsector.id 
		where user.user_type=1 ".$activeCondition."  ".$franchiseCondition."   ".$conditionexpertStr."  ".$stdcondition." ".$sectorcondition." group by user.id");
		*/
		$expertListArr = [];
		if(count($sector_group_ids)>0){
			$sectorgpcondition = " and usrgroupcode.business_sector_group_id in(".implode(',',$sector_group_ids).") and usrgroupcode.status = ".$usergroupcode->arrEnumStatus['approved']." ";
			$technicalexpertcommand = $connection->createCommand("SELECT user.id,first_name ,last_name,group_concat(usrgroupcode.business_sector_group_id) as business_sector_group_ids FROM tbl_users as user 
			inner join tbl_user_role as user_role on  user_role.user_id = user.id 
			inner join tbl_role as role on  role.id = user_role.role_id  and role.resource_access=3 
			
			INNER JOIN `tbl_user_role_technical_expert_business_group` AS usrsector on usrsector.user_id = user.id 
			INNER JOIN `tbl_user_role_technical_expert_business_group_code` AS usrgroupcode on usrgroupcode.user_role_technical_expert_bs_id = usrsector.id  
			INNER JOIN `tbl_business_sector_group` AS mastersecgp on usrgroupcode.business_sector_group_id = mastersecgp.id  
			
			where  
			user_role.approval_status=2 and user.user_type=1 ".$sectorgpcondition ." ".$activeCondition."  ".$franchiseCondition."   ".$conditionexpertStr."   ".$sectorcondition." group by user.id");
			//".$stdcondition."
			//INNER JOIN `tbl_user_standard` AS usrstd on usrstd.user_id = user.id  and mastersecgp.standard_id = usrstd.standard_id ,group_concat(usrstd.standard_id) as userstandards

			
			$expertresult = $technicalexpertcommand->queryAll();
			$expertListArr = [];
			if(count($expertresult)>0){
				foreach($expertresult as $expertdata){
					$standards_qual = [];
					$business_sector_group_ids = array_unique(explode(',',$expertdata['business_sector_group_ids']));
					$BusinessSectorGroupList = BusinessSectorGroup::find()->where(['id'=>$business_sector_group_ids])->all();
					if(count($BusinessSectorGroupList)>0){
						foreach($BusinessSectorGroupList as $bgsectorobj){
							$standards_qual[] = $bgsectorobj->standard_id;
						}
					}
					$expertListArr[] = ['id'=>$expertdata['id'],'name'=>$expertdata['first_name'].' '.$expertdata['last_name']
									,'standards_qual' => implode(',',$standards_qual) ];
					

					//$expertdata['userstandards']
				}
			}
		}
		

		$translatorcommand = $connection->createCommand("SELECT user.id,first_name ,last_name  FROM tbl_users as user 
		inner join tbl_user_role as user_role on  user_role.user_id = user.id 
		inner join tbl_role as role on  role.id = user_role.role_id  and role.resource_access=4  
				where user.user_type=1 ".$franchiseCondition."  ".$conditiontranslatorStr);
		$translatorresult = $translatorcommand->queryAll();
		$translatorListArr = [];
		if(count($translatorresult)>0){
			foreach($translatorresult as $translatordata){
				$translatorListArr[] = ['id'=>$translatordata['id'],'name'=>$translatordata['first_name'].' '.$translatordata['last_name']];
			}
		}




		return ['auditors'=>$usersListArr,'technicalExpert'=>$expertListArr,
				'translator'=>$translatorListArr,'status'=>1,
				'sectorwiseusers' => $sectorwiseusers,
 
				//'observer'=>$observer				
				];
	}

	private function getJustifiedUsers($franchise_id,$applicationunitid){

		$connection = Yii::$app->getDb();
		if($franchise_id !=''){
			$franchiseCondition = ' AND user_role.franchise_id= '.$franchise_id;
		}
		$arrbusinessector = [];
		if($applicationunitid!=''){
			$businessector = ApplicationUnitBusinessSectorGroup::find()->where(['unit_id'=>$applicationunitid])->all();
			if(count($businessector)>0){
				foreach($businessector as $sector){
					
					$arrbusinessector[] = $sector->business_sector_group_id;
				}
				//print_r($arrbusinessector); 
			}
		}


		$sectorwiseusers=[];
		$userlistIds = [];

		if(count($arrbusinessector)>0){
			
			foreach($arrbusinessector as $sectorid){
				
				$userlistNames = [];

				$sectorgpcondition = " and usrgroupcode.business_sector_group_id =".$sectorid." ";
				$command = $connection->createCommand("SELECT user.id,first_name ,last_name 
					FROM tbl_users as user 
					inner join tbl_user_role as user_role on  user_role.user_id = user.id 
					INNER JOIN `tbl_rule` AS rule ON  user_role.role_id=rule.role_id AND rule.privilege='audit_execution' 
					INNER JOIN `tbl_user_role_business_group` AS usrgroup on usrgroup.user_id = user.id 
					INNER JOIN `tbl_user_role_business_group_code` AS usrgroupcode on usrgroupcode.business_group_id = usrgroup.id 

					where user_type=1 ".$franchiseCondition." ".$sectorgpcondition."  group by user.id");
				//INNER JOIN `tbl_user_business_sector_group` AS usrsectorgroup on usrsectorgroup.user_id = user.id 

				$result = $command->queryAll();
				$usersListArr = [];
				if(count($result)>0){
					foreach($result as $userdata){
						$userlistIds[] = $userdata['id'];
					}
				}

				//For getting Technical Experts
				//".$conditionStr." ".$stdcondition." ".$franchiseCondition." ".$sectorcondition." 
				$technicalcommand = $connection->createCommand("SELECT user.id,first_name ,last_name  FROM tbl_users as user 
				inner join tbl_user_role as user_role on  user_role.user_id = user.id 
				inner join tbl_role as role on  role.id = user_role.role_id  and role.resource_access=3  
				INNER JOIN `tbl_user_role_technical_expert_business_group` AS usrgroup on usrgroup.user_id = user.id 
					INNER JOIN `tbl_user_role_technical_expert_business_group_code` AS usrgroupcode on usrgroupcode.user_role_technical_expert_bs_id = usrgroup.id 
						where user.user_type=1    ".$franchiseCondition." ".$sectorgpcondition." group by user.id");
				$technicalresult = $technicalcommand->queryAll();
				$technicalListArr = [];
				if(count($technicalresult)>0){
					foreach($technicalresult as $technicaldata){
						//$technicalListArr[] =$technicaldata['first_name'].' '.$technicaldata['last_name'];
						if( !in_array($technicaldata['id'], $userlistIds)){
							$userlistIds[] = $technicaldata['id'];
						}
					}
				}
				/*$bsecname = BusinessSectorGroup::find()->where(['id'=>$sectorid])->one();
				$sectorwiseusers[] = [
					'sectorid' => $sectorid,
					'group_code' => $bsecname->group_code,
					'userlist'=>$usersListArr,
					'userlistIds' => $userlistIds,
					'userlistnames'=>implode(', ',$userlistNames)
				
				];
				*/
			}
			//print_r($usersListArr); die;
			//$sectoruserArr = array_merge($technicalListArr,$usersListArr);
			
		}
		//print_r($userlistIds);
		$userlistIds = array_unique($userlistIds);
		return $userlistIds;
	}
	

	public function actionSendtoleadauditor(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			$planunit = AuditPlanUnit::find()->where(['id'=>$data['audit_plan_unit_id']])->one();
			if($planunit !== null){
				
				
				
				$planunitexecutionstatus = new AuditPlanUnitExecution();
				$waiting_for_unit_lead_auditor_approvalstatus = $planunitexecutionstatus->arrEnumStatus['waiting_for_unit_lead_auditor_approval'];
				$reintiatestatus = $planunitexecutionstatus->arrEnumStatus['reintiate'];
				$planunitexecution = AuditPlanUnitExecution::find()->where(['audit_plan_unit_id'=>$data['audit_plan_unit_id']
				,'status'=>[$waiting_for_unit_lead_auditor_approvalstatus,$reintiatestatus] ])->all();
				if(count($planunitexecution)>0){
					foreach($planunitexecution as $unitexecution){
						$unitexecution->status = $planunitexecutionstatus->arrEnumStatus['completed'];
						$unitexecution->save();
					}
				}


				$awaiting_for_lead_auditor_approval_status = $planunit->arrEnumStatus['awaiting_for_lead_auditor_approval'];
				$awaiting_for_reviewer_approval = $planunit->arrEnumStatus['awaiting_for_reviewer_approval'];
				$planunit->status = $awaiting_for_lead_auditor_approval_status;
				$planunit->status_change_date = time();
				if($planunit->save()){
					//waiting_for_lead_auditor
					$auditplan = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
					if($auditplan !== null){
						$auditplanunit = AuditPlanUnit::find()->where(['not in','status',[$awaiting_for_lead_auditor_approval_status,$awaiting_for_reviewer_approval]]);
						$auditplanunit = $auditplanunit->andWhere('audit_plan_id='.$data['audit_plan_id'])->one();
						if($auditplanunit===null){
							$auditplan->status = $auditplan->arrEnumStatus['waiting_for_lead_auditor'];
							$auditplan->save();
						}
					}
					$responsedata = ['status'=>1,'message'=>'Findings Submitted to Lead Auditor Successfully',
					'data'=>['status'=>$planunit->status,'plan_status'=>$auditplan->status]
					];
				}
			}
		}
		return $responsedata;
	}

	
	public function actionChangeAuditReview(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			
			$model = AuditPlan::find()->where(['id' => $data['audit_plan_id']])->one();
			if ($model !== null)
			{
				//$model->status = $model->arrEnumStatus['audit_checklist_inprocess'];
				$model->status = $model->arrEnumStatus['finalized'];
				if($model->save())
				{
					$audit = Audit::find()->where(['id'=>$model->audit_id])->one();
					if($audit!==null){
						//$audit->status = $audit->arrEnumStatus['audit_checklist_inprocess'];
						$audit->status = $audit->arrEnumStatus['finalized'];
						$audit->updated_at = time();
						$audit->save();
					}
					$responsedata = ['status'=>1,'message'=>'Audit updated successfully','data'=>[]];
				}
					
			}
		}
		return $responsedata;
	}


	public function actionChangeGenerateCertificate(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			
			$model = AuditPlan::find()->where(['id' => $data['audit_plan_id']])->one();
			if ($model !== null)
			{
				$model->certificate_generated_date = date("Y-m-d",time());
				$model->status = $model->arrEnumStatus['generate_certificate'];
				if($model->save())
				{
					$audit = Audit::find()->where(['id'=>$model->audit_id])->one();
					if($audit!==null){
						$audit->status = $audit->arrEnumStatus['generate_certificate'];
						$audit->updated_at = time();
						$audit->save();
					}
					$responsedata = ['status'=>1,'message'=>'Certificate can be downloaded from for this audit successfully','data'=>[]];
				}
					
			}
		}
		return $responsedata;
	}


	public function actionSendtoreviewer(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			$auditplanunitstatus = new AuditPlanUnit();
			$model = AuditPlan::find()->where(['id' => $data['audit_plan_id']])->one();
			if ($model !== null)
			{
				$planreviewer = AuditPlanReviewer::find()->where(['audit_plan_id' => $data['audit_plan_id']])->one();

				//audit_completed
				if($planreviewer === null){
					$model->status = $model->arrEnumStatus['waiting_for_review'];
				}else{
					$model->status = $model->arrEnumStatus['review_in_progress'];
				}
				
				if($model->save())
				{
					$auditplanunit = AuditPlanUnit::find()->where(['audit_plan_id'=>$data['audit_plan_id'],'status'=> $auditplanunitstatus->arrEnumStatus['awaiting_for_lead_auditor_approval']])->all();
					if(count($auditplanunit)>0){
						foreach($auditplanunit as $planunit){
							$planunit->status = $planunit->arrEnumStatus['awaiting_for_reviewer_approval'];
							$planunit->status_change_date = time();
							$planunit->save();
						}
					}
					$responsedata = ['status'=>1,'message'=>'Audit submitted for reviewer successfully','data'=>[
						'plan_status'=>$model->status,
						'plan_unit_status'=>$auditplanunitstatus->arrEnumStatus['awaiting_for_reviewer_approval']
						]];
				}
					
			}
		}
		return $responsedata;
	}


	public function actionSendtocustomer(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			
			$auditmodel = new AuditPlan();
			$auditunitmodel = new AuditPlanUnit();

			$model = AuditPlan::find()->where(['id' => $data['audit_plan_id']])->one();
			if ($model !== null)
			{
				//audit_completed
				$model->status = $auditmodel->arrEnumStatus['audit_completed'];
				
				if($model->save())
				{
					$auditplanunit = AuditPlanUnit::find()->where(['audit_plan_id'=>$data['audit_plan_id']])->all();
					if(count($auditplanunit)>0){
						foreach($auditplanunit as $planunit){

							
							$executionlistnoncomformityObj = $planunit->executionlistnoncomformity;
							$executionlistnoncomformity=count($executionlistnoncomformityObj);

							if($executionlistnoncomformity<=0){
								$planunit->status = $auditunitmodel->arrEnumStatus['remediation_completed'];	
							}else{
								$planunit->status = $auditunitmodel->arrEnumStatus['audit_completed'];
							}
							
							//$planunit->status_change_date = $auditunitmodel->arrEnumStatus['audit_completed'];
							$planunit->save();
						}
					}

					$auditmodel = Audit::find()->where(['id' => $model->audit_id])->one();
					$auditmodel->status = $auditmodel->arrEnumStatus['audit_completed'];
					$auditmodel->save();

					$responsedata = ['status'=>1,'message'=>'Audit Sent to Customer Successfully','data'=>['status'=>$model->status]];
				}
					
			}
		}
		return $responsedata;
	}

	public function actionSendaudit(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			
			$auditmodel = new AuditPlan();
			

			$model = AuditPlan::find()->where(['id' => $data['audit_plan_id']])->one();
			if ($model !== null)
			{
				if($data['actiontype']=='sendaudittoauditor'){
					$model->status = $model->arrEnumStatus['auditor_review_in_progress'];
				}else if($data['actiontype']=='sendbackaudittocustomer'){
					$model->status = $model->arrEnumStatus['remediation_in_progress'];
				}else if($data['actiontype']=='sendaudittoreviewer'){
					$model->status = $model->arrEnumStatus['reviewer_review_in_progress'];
				}else if($data['actiontype']=='sendbackaudittoauditor'){
					$model->status = $model->arrEnumStatus['auditor_review_in_progress'];
				}
				
				
				if($model->save())
				{
					/*
					$auditplanunit = AuditPlanUnit::find()->where(['audit_plan_id'=>$data['audit_plan_id']])->all();
					if(count($auditplanunit)>0){
						foreach($auditplanunit as $planunit){

							
							$executionlistnoncomformityObj = $planunit->executionlistnoncomformity;
							$executionlistnoncomformity=count($executionlistnoncomformityObj);

							if($executionlistnoncomformity<=0){
								$planunit->status = $auditunitmodel->arrEnumStatus['remediation_completed'];	
							}else{
								$planunit->status = $auditunitmodel->arrEnumStatus['audit_completed'];
							}
							
							//$planunit->status_change_date = $auditunitmodel->arrEnumStatus['audit_completed'];
							$planunit->save();
						}
					}
					
					$auditmodel = Audit::find()->where(['id' => $model->audit_id])->one();
					$auditmodel->status = $auditmodel->arrEnumStatus['audit_completed'];
					$auditmodel->save();
					*/
					$responsedata = ['status'=>1,'message'=>'Updated Successfully','data'=>['status'=>$model->status]];
				}
					
			}
		}
		return $responsedata;
	}


	public function actionChangeStatus(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			/*
			$model = AuditPlan::find()->where(['id' => $data['audit_plan_id']])->one();
			if ($model !== null)
			{
				if(isset($data['status']) && $data['status']>0){
					$model->status = $data['status'];
					$model->save();
				}
				
				if(isset($data['auditplan_unit_id']) && $data['auditplan_unit_id']>0 ){
					$unitmodel = AuditPlanUnit::find()->where(['id' => $data['auditplan_unit_id']])->one();
					$unitmodel->status = $data['unitstatus'];
					$unitmodel->save();
				}

				$responsedata = ['status'=>1,'message'=>'Updated Successfully','data'=>['unitstatus'=>$data['unitstatus']]];				
			}
			*/
			$auditmodel = new Audit();
			$auditplanstatusmodel = new AuditPlan();
			
			$model = Audit::find()->where(['id' => $data['audit_id']])->one();
			
			if ($model !== null)
			{
				if(isset($data['status']) && $data['status']>0){
					$model->status = $data['status'];
					
					if($model->save())
					{
						if($auditmodel->arrEnumStatus['approved']== $data['status'])
						{

							$maxdate = '0000-00-00';
							$connection = Yii::$app->getDb();
							$command = $connection->createCommand("SELECT MAX(date) as maxdate FROM `tbl_audit_plan_unit_date` as unitdate inner join 
							tbl_audit_plan_unit as unit on unit.id = unitdate.`audit_plan_unit_id` 
							inner join tbl_audit_plan as plan on plan.id = unit.audit_plan_id where 1=1 and plan.audit_id =".$data['audit_id']." ");
							$result = $command->queryAll();
							if(count($result)>0){
								foreach($result as $subdata){
									$maxdate = $subdata['maxdate'];
									//$dataArr[] =['id'=>$subdata['id'],'name'=>$subdata['name']];
								}
							}
							$auditPlanmodel = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
							$auditPlanmodel->audit_completed_date = $maxdate;
							$auditPlanmodel->save();

						}else if($auditmodel->arrEnumStatus['awaiting_for_customer_approval']== $data['status'])
						{
							$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'inspection_plan_to_customer'])->one();

							if($mailContent !== null)
							{
								$MailLookupModel = new MailLookup();
								$MailLookupModel->to=$model->application->email_address;								
								$MailLookupModel->subject=$mailContent['subject'];
								$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailContent['message']]);
								$MailLookupModel->attachment='';
								$MailLookupModel->mail_notification_id='';
								$MailLookupModel->mail_notification_code='';
								$Mailres=$MailLookupModel->sendMail();
							}
							
						}else{
							
							//audit_checklist_inprocess
						}
						$responsedata = ['status'=>1,'message'=>'Updated Successfully','data'=>['status'=>$model->status]];
					}
				}else{
					if(isset($data['audit_plan_id']) && isset($data['audit_id'])){
						$auditPlanmodel = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
						if($auditPlanmodel !== null){
							$auditPlanmodel->status = $auditplanstatusmodel->arrEnumStatus['finalized'];
							$auditPlanmodel->save();
						}

						$auditmodelup = Audit::find()->where(['id'=>$data['audit_id']])->one();
						if($auditmodelup !== null){
							//$auditmodelup->status = $auditmodel->arrEnumStatus['audit_checklist_inprocess'];
							$auditmodelup->status = $auditmodel->arrEnumStatus['finalized'];							
							$auditmodelup->save();
						}
					}
					$responsedata = ['status'=>1,'message'=>'Updated Successfully','data'=>['status'=>$auditmodelup->status]];
				}		
			}
		}
		return $responsedata;
	}

	public function actionAuditStatus(){
		$auditmodel = new Audit();
		return $auditmodel->arrEnumStatus;
	}


	private function getSubtopic($unit_id,$audit_plan_unit_id='',$userid=''){
		//$query = '';
		// AND aeq.sub_topic_id IS NULL
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		
		$connection = Yii::$app->getDb();
		$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();

		$condition = '';
		
		if($unit_id){
			$condition .= " AND unit.id=".$unit_id;
		}
		if($userid){
			$condition .= " AND ( execution.executed_by is null OR execution.executed_by=".$userid.")";
		}
		$command = $connection->createCommand("SELECT user.first_name,user.last_name,execution.status,execution.executed_by,execution.executed_date,subtopic.id,
			subtopic.name FROM `tbl_application_unit` AS unit 
			INNER JOIN `tbl_application_unit_process` AS uprs ON unit.id=uprs.unit_id 
			INNER JOIN `tbl_application_unit_standard` AS ustd ON unit.id=ustd.unit_id 
			INNER JOIN `tbl_audit_execution_question_process` AS aeqp ON uprs.process_id=aeqp.process_id 
			INNER JOIN `tbl_audit_execution_question_standard` AS aeqs ON ustd.standard_id=aeqs.standard_id 
			INNER JOIN `tbl_audit_execution_question` AS aeq ON aeqp.audit_execution_question_id=aeq.id 
			INNER JOIN `tbl_sub_topic` AS subtopic ON subtopic.id=aeq.sub_topic_id 

			LEFT JOIN `tbl_audit_plan_unit_execution` AS execution on execution.sub_topic_id = subtopic.id and execution.audit_plan_unit_id=".$audit_plan_unit_id." 
			LEFT JOIN `tbl_users` AS user on user.id = execution.executed_by  
			WHERE 1=1  ".$condition." 
			
			GROUP BY subtopic.id");
		$result = $command->queryAll();
		//$dataArr = [];
		/*if(count($result)>0){
			foreach($result as $subdata){
				$dataArr[] =['id'=>$subdata['id'],'name'=>$subdata['name']];
			}
		}
		*/
		//$responsedata =['status'=>1,'data'=>$dataArr];
		

		return $result;

	}

	public function actionGetSubtopic(){
		//$query = '';
		// AND aeq.sub_topic_id IS NULL
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		
		if ($data) 
		{
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$franchiseid=$userData['franchiseid'];
			$is_headquarters =$userData['is_headquarters'];

			/*
			$addedsubtopic = [];
			$auditplanexe = AuditPlanUnitExecution::find()->where(['audit_plan_unit_id'=>$data['audit_plan_unit_id']])->all();
			if(count($auditplanexe)>0){
				foreach($auditplanexe as $auditexe){
					$addedsubtopic[] = $auditexe['sub_topic_id'];
				}
			}
			*/

			$connection = Yii::$app->getDb();
			$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();

			$condition = '';
			$unit_id = $data['unit_id'];
			$audit_plan_unit_id = $data['audit_plan_unit_id'];
			$audit_id = $data['audit_id'];
			$audit_plan_id = $data['audit_plan_id'];

			if($unit_id){
				$condition .= " AND unit.id=".$unit_id;
			}

			$unitLeadAuditor = '';
			$applicationLeadAuditor = '';
			$AuditPlanUnitAuditor = AuditPlanUnitAuditor::find()->where(['is_lead_auditor'=>1,'audit_plan_unit_id'=>$audit_plan_unit_id])->one();
			if($AuditPlanUnitAuditor !== null){
				$unitLeadAuditor = $AuditPlanUnitAuditor->user_id;
			}
			$AuditPlan = AuditPlan::find()->where(['id'=>$audit_plan_id])->one();
			if($AuditPlan !== null){
				$applicationLeadAuditor = $AuditPlan->application_lead_auditor;
			}


			if($userid && $user_type==1 && $userid != $applicationLeadAuditor && $userid!=$unitLeadAuditor ){
				$condition .= " AND ( execution.executed_by is null OR execution.executed_by=".$userid.")";
			}
			/*
			if(count($addedsubtopic)>0){
				$condition .= " AND subtopic.id not in (".implode(',', $addedsubtopic).")";
			}
			*/
			

			$command = $connection->createCommand("SELECT subtopic.id,subtopic.name FROM `tbl_application_unit` AS unit 
			INNER JOIN `tbl_application_unit_process` AS uprs ON unit.id=uprs.unit_id 
			INNER JOIN `tbl_application_unit_standard` AS ustd ON unit.id=ustd.unit_id 
			INNER JOIN `tbl_audit_execution_question_process` AS aeqp ON uprs.process_id=aeqp.process_id 
			INNER JOIN `tbl_audit_execution_question_standard` AS aeqs ON ustd.standard_id=aeqs.standard_id 
			INNER JOIN `tbl_audit_execution_question` AS aeq ON aeqp.audit_execution_question_id=aeq.id 
			INNER JOIN `tbl_sub_topic` AS subtopic ON subtopic.id=aeq.sub_topic_id

			LEFT JOIN `tbl_audit_plan_unit_execution` AS execution on execution.sub_topic_id = subtopic.id and execution.audit_plan_unit_id=".$audit_plan_unit_id." 
			LEFT JOIN `tbl_users` AS user on user.id = execution.executed_by  

			WHERE 1=1  ".$condition." 
			GROUP BY subtopic.id");
			$result = $command->queryAll();
			$dataArr = [];
			if(count($result)>0){
				foreach($result as $subdata){
					$dataArr[] =['id'=>$subdata['id'],'name'=>$subdata['name']];
				}
			}
			$responsedata =['status'=>1,'data'=>$dataArr];
		}

		return $responsedata;

	}
	
	public function actionListAuditPlan()
    {
        $post = yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];

		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelInvoice = new Invoice();
		$modelOffer = new Offer();
		$modelAudit = new Audit();
		$modelAuditPlan = new AuditPlan();
		
		$model = Offer::find()->where(['t.status'=>$modelOffer->enumStatus['finalized']])->alias('t');
		$model->joinWith(['audit']);
		$model = $model->join('left join', 'tbl_audit_plan as plan','plan.audit_id =tbl_audit.id');
		$model = $model->join('left join', 'tbl_audit_plan_unit as plan_unit','plan.id =plan_unit.audit_plan_id');
		$model = $model->join('left join', 'tbl_audit_plan_unit_auditor as plan_unit_auditor','plan_unit.id =plan_unit_auditor.audit_plan_unit_id');
		$model = $model->join('left join', 'tbl_audit_plan_reviewer as plan_reviewer','plan_reviewer.audit_plan_id =plan.id ');
			// tbl_audit_plan_unit_auditor, tbl_audit_plan_unit
		
		if($resource_access != 1){
			if($user_type== 1 && ! in_array('invoice_management',$rules) && ! in_array('audit_management',$rules) ){
				return $responsedata;
			}else if($user_type==3 && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$model = $model->join('inner join', 'tbl_application as app','t.app_id=app.id and (app.franchise_id="'.$userid.'" or app.created_by="'.$userid.'")');
			}else if($user_type==2){
				$model = $model->join('inner join', 'tbl_application as app','t.app_id=app.id and app.customer_id="'.$userid.'"');	
			}
			/*
			else if($user_type==3 && $role!=0 && ! in_array('view_invoice',$rules) ){
				return $responsedata;
			}
			*/
		}
		
		if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1 
		&& !in_array('generate_audit_plan',$rules) 
		&& !in_array('audit_execution',$rules)
		&& !in_array('audit_review',$rules)
		&& !in_array('generate_audit_plan',$rules)){
			$model = $model->join('inner join', 'tbl_application as app','t.app_id=app.id and (app.franchise_id="'.$franchiseid.'")');
		}
		
		if(isset($post['type']) && $post['type']=='audit'){
			if($user_type== Yii::$app->params['user_type']['customer']){
				$model = $model->andWhere(' tbl_audit.status>="'.$modelAudit->arrEnumStatus['awaiting_for_customer_approval'].'" ');
			}
			//plan_reviewer
			if($user_type== Yii::$app->params['user_type']['user']  && in_array('audit_review',$rules)){
				$model = $model->andWhere('((plan.status="'.$modelAuditPlan->arrEnumStatus['waiting_for_review'].'" )
					OR (plan_reviewer.reviewer_id="'.$userid.'" and  (plan.status>="'.$modelAuditPlan->arrEnumStatus['review_in_progress'].'"
					or  plan.status="'.$modelAuditPlan->arrEnumStatus['reviewer_reinitiated'].'" )))
				');
			}


			if($user_type== Yii::$app->params['user_type']['user']  && in_array('audit_execution',$rules) && !in_array('generate_audit_plan',$rules) ){
				$model = $model->andWhere('((plan.application_lead_auditor="'.$userid.'" and tbl_audit.status>="'.$modelAudit->arrEnumStatus['review_in_process'].'" )
					OR (plan_unit_auditor.user_id="'.$userid.'" and  tbl_audit.status>="'.$modelAudit->arrEnumStatus['approved'].'"))
				');
			}
			if($user_type== Yii::$app->params['user_type']['user']  && in_array('generate_audit_plan',$rules) && !in_array('audit_execution',$rules) ){
				$model = $model->andWhere('(tbl_audit.status="'.$modelAudit->arrEnumStatus['open'].'" or tbl_audit.id is null or tbl_audit.created_by='.$userid.')');
			}
			if($user_type== Yii::$app->params['user_type']['user']  && in_array('generate_audit_plan',$rules) && in_array('audit_execution',$rules) ){
				$model = $model->andWhere('(tbl_audit.status="'.$modelAudit->arrEnumStatus['open'].'" or tbl_audit.id is null or tbl_audit.created_by='.$userid.')
					or ( plan.application_lead_auditor="'.$userid.'" and tbl_audit.status>="'.$modelAudit->arrEnumStatus['review_in_process'].'" )
				');
			}
		}


		$model = $model->groupBy(['t.id']);
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];


				$model = $model->andFilterWhere([
					'or',
					['like', 't.invoice_number', $searchTerm],								
				]);
				
				$paymentstatusarray=array_map('strtolower', $modelInvoice->paymentStatus);
				$paymentsearch_status = array_search(strtolower($searchTerm),$paymentstatusarray);
				if($paymentsearch_status!==false)
				{
					if($paymentsearch_status ==1){
						$paymentsearch_status = [0,1];
					}
					$model = $model->orFilterWhere([
                        'or', 					
						['t.payment_status'=>$paymentsearch_status]								
					]);
				}
				$totalCount = $model->count();
			}
			
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
			}
			else
			{
				$model = $model->orderBy(['t.created_at' => SORT_DESC]);
			}
			



            $model = $model->limit($pageSize)->offset($page);
		}
		else
		{
			$totalCount = $model->count();
		}
		
		$app_list=array();
		$model = $model->all();	
		if(count($model)>0)
		{
			foreach($model as $offer)
			{
				$data=array();
				
				$data['id']=($offer->audit)?$offer->audit->id:'';
				$data['audit_status']=($offer->audit)?$offer->audit->status:0;
				$data['audit_status_name']=($offer->audit)?$offer->audit->arrStatus[$data['audit_status']]:'Open';
				//$data['audit_status_name']=$data['audit_status'];
				
				//$data['invoice_id']=$offer->id;
				$data['app_id']=$offer->app_id;
				$data['offer_id']=($offer)?$offer->id:'';
				$data['currency']=($offer)?$offer->offerlist->currency:'';
				$data['company_name']=($offer)?$offer->application->company_name:'';
				//$data['invoice_number']=$offer->invoice_number;
				//$data['total_payable_amount']=$offer->total_payable_amount;
				//$data['tax_amount']=$offer->tax_amount;				
				//$data['creator']=$offer->username->first_name.' '.$offer->username->last_name;
				//$data['payment_status_name']=($offer->payment_status!='' )?$modelInvoice->paymentStatus[$offer->payment_status]:'Payment Pending';
				//$data['created_at']=date('M d,Y h:i A',$offer->created_at);
				$data['created_at']=date($date_format,$offer->created_at);
				
				$arrAppStd=array();				
				if($offer)
				{
					$appobj = $offer->application;
					
					$data['application_unit_count']=count($appobj->applicationunit);
					$data['application_country']=$appobj->country->name;
					$data['application_city']=$appobj->city;
					
					$appStd = $appobj->applicationstandard;
					if(count($appStd)>0)
					{	
						foreach($appStd as $app_standard)
						{
							$arrAppStd[]=$app_standard->standard->code;
						}
					}
					
					$data['application_standard']=implode(', ',$arrAppStd);
				}			
				
				$app_list[]=$data;
			}
		}
		
		$audit = new Audit;
		return ['listauditplan'=>$app_list,'total'=>$totalCount,'arrEnumStatus'=>$audit->arrEnumStatus];
	}

	public function actionGetauditloaddetails(){
		$AuditPlan = new AuditPlan;
		$sharePlanArr = $AuditPlan->arrSharePlan;
		return ['sharePlanArr'=>$sharePlanArr];
	}
}