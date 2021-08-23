<?php
namespace app\modules\audit\controllers;

use Yii;

use yii\web\NotFoundHttpException;

use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\AuditPlanUnit;
use app\modules\audit\models\AuditPlanUnitTemp;
use app\modules\audit\models\AuditPlanUnitAuditorTemp;
use app\modules\audit\models\AuditPlanUnitAuditorDateTemp;
use app\modules\audit\models\AuditReportApplicableDetails;
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
use app\modules\master\models\UserRoleTechnicalExpertBsCode;
use app\modules\master\models\Role;
use app\modules\master\models\UserStandard;
use app\modules\master\models\SubTopic;
use app\modules\master\models\UserRoleBusinessGroupCode;

use app\modules\audit\models\AuditPlanUnitExecutionFollowup;
use app\modules\audit\models\AuditPlanUnitFollowupRemediationReview;
use app\modules\audit\models\AuditPlanCustomerReview;
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
use app\modules\audit\models\AuditPlanUnitExecutionChecklist;
use app\modules\audit\models\AuditPlanReviewerTe;
use app\modules\audit\models\AuditReportInterviewSummary;
use app\modules\audit\models\AuditReportClientInformationGeneralInfo;
use app\modules\audit\models\AuditReportClientInformationSupplierInformation;
use app\modules\audit\models\AuditReportClientInformationChecklistReview;
use app\modules\audit\models\AuditPlanInspectionPlanInspectorHistory;


use app\modules\application\models\ApplicationUnitBusinessSector;
use app\modules\application\models\ApplicationUnitBusinessSectorGroup;
use app\modules\application\models\ApplicationCertifiedByOtherCB;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationUnitManday;
use app\modules\application\models\ApplicationUnitStandard;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationUnitCertifiedStandard;

use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;
use app\modules\master\models\AuditReviewerRiskCategory;
use app\modules\master\models\ReductionStandard;

use app\modules\offer\models\Offer;
use app\modules\invoice\models\Invoice;

use app\modules\certificate\models\Certificate;

use app\modules\audit\models\AuditReportAttendanceSheet;
use app\modules\audit\models\AuditReportSampling;
use app\modules\audit\models\AuditReportSamplingList;
use app\modules\audit\models\AuditReportInterviewEmployees;
use app\modules\audit\models\AuditReportInterviewRequirementReview;
use app\modules\audit\models\AuditReportInterviewRequirementReviewComment;
use app\modules\audit\models\AuditReportEnvironment;
use app\modules\audit\models\AuditReportQbsScopeHolder;
use app\modules\audit\models\AuditReportChemicalList;
use app\modules\audit\models\AuditReportLivingWageFamilyExpenses;
use app\modules\audit\models\AuditReportLivingWageRequirementReview;
use app\modules\audit\models\AuditReportLivingWageRequirementReviewComment;
use app\modules\audit\models\AuditReportClientInformationProcess;
use app\modules\audit\models\AuditReportNcnReport;
use app\modules\audit\models\AuditReportClientInformationGeneralInfoDetails;
use app\modules\audit\models\AuditReportClientInformationChecklistReviewComment;

use app\modules\unannouncedaudit\models\UnannouncedAuditApplication;


use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditPlanController implements the CRUD actions for Process model.
 */
class AuditPlanController extends \yii\rest\Controller
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
			'authenticator' => ['class' => JwtHttpBearerAuth::class,
				'optional' => [
					'deleteaudit'					
				]
			]
		];        
    }
	
	public function actionIndex()
    {
		return ['1'=>'111']; die;
	}
	
	public function actionAddRemark()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        $data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		
		if ($data) 
		{
			
			if(!Yii::$app->userrole->canEditAuditReport($data)){
				return false;
			}
				
			if(isset($data['audit_id']) && isset($data['unit_id']) && $data['type']!='supplier_list')
			{
				$model = AuditReportApplicableDetails::find()->where(['audit_id' => $data['audit_id']]);
				if(isset($data['unit_id']) && $data['unit_id']){
					$model = $model->andWhere(['unit_id' => $data['unit_id']]);
				}				
				$model = $model->andWhere(['report_name' => $data['type']])->one();
				if($model===null){
					$model = new AuditReportApplicableDetails();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];	
				}

				$auditmodel = Audit::find()->where(['id' => $data['audit_id']])->one();
				if($auditmodel!==null)
				{
					$model->app_id = $auditmodel->app_id;
				}

			}else{

				if(isset($data['app_id']) && isset($data['app_id']))
				{
					$model = AuditReportApplicableDetails::find()->where(['app_id' => $data['app_id']])->andWhere(['report_name' => $data['type']]);
					if(isset($data['unit_id']) && $data['unit_id']){
						$model = $model->andWhere(['unit_id' => $data['unit_id']]);
					}	
					$model = $model->one();
					if($model===null)
					{
						$model = new AuditReportApplicableDetails();
						$model->created_by = $userData['userid'];
					}
				}
				else
				{
					$model = new AuditReportApplicableDetails();
					$model->created_by = $userData['userid'];
				}
				
			}
			if(isset($data['audit_id'])){
				$model->audit_id = $data['audit_id'];
			}
			if(isset($data['app_id'])){
				$model->app_id = $data['app_id'];
			}
			$model->unit_id = isset($data['unit_id'])?$data['unit_id']:'';
			$model->report_name = $data['type'];
			$model->comments = $data['comments'];
			$model->status = $data['is_applicable'];

			if($model->validate() && $model->save())
			{	
				if($data['type']=='supplier_list')
				{
					AuditReportClientInformationSupplierInformation::deleteAll(['app_id' => $data['app_id']]);
				}
				else if($data['type']=='chemical_list')
				{
					AuditReportChemicalList::deleteAll(['and',['audit_id' => $data['audit_id']],['unit_id' => $data['unit_id']]]);
				}
				else if($data['type']=='attendance_list')
				{
					AuditReportAttendanceSheet::deleteAll(['and',['audit_id' => $data['audit_id']],['unit_id' => $data['unit_id']]]);
				}
				else if($data['type']=='sampling_list')
				{
					$Samplingmodel = AuditReportSampling::find()->where(['audit_id' => $data['audit_id']])->andWhere(['unit_id' => $data['unit_id']])->one();

					if($Samplingmodel!==null)
					{
						AuditReportSamplingList::deleteAll(['audit_report_sampling_id' => $Samplingmodel->id]);
						AuditReportSampling::deleteAll(['id' => $Samplingmodel->id]);
					}

				}
				else if($data['type']=='interview_list')
				{
					$summarymodel = AuditReportInterviewSummary::find()->where(['audit_id' => $data['audit_id']])->andWhere(['unit_id' => $data['unit_id']])->all();
					if(count($summarymodel)>0){
						foreach($summarymodel as $summaryobj){
							$summaryobj->total_employees = 0;
							$summaryobj->save();
						}
					}
					//AuditReportInterviewSummary::deleteAll(['and',['audit_id' => $data['audit_id']],['unit_id' => $data['unit_id']]]);
					AuditReportInterviewEmployees::deleteAll(['and',['audit_id' => $data['audit_id']],['unit_id' => $data['unit_id']]]);
					$reviewmodel = AuditReportInterviewRequirementReview::find()->where(['audit_id' => $data['audit_id']])->andWhere(['unit_id' => $data['unit_id']])->one();

					if($reviewmodel!==null)
					{
						AuditReportInterviewRequirementReviewComment::deleteAll(['requirement_review_id' => $reviewmodel->id]);
						AuditReportInterviewRequirementReview::deleteAll(['id' => $reviewmodel->id]);
					}
				}
				else if($data['type']=='livingwage_list')
				{
					AuditReportLivingWageFamilyExpenses::deleteAll(['and',['audit_id' => $data['audit_id']],['unit_id' => $data['unit_id']]]);
					$reviewmodel = AuditReportLivingWageRequirementReview::find()->where(['audit_id' => $data['audit_id']])->andWhere(['unit_id' => $data['unit_id']])->one();

					if($reviewmodel!==null)
					{
						AuditReportLivingWageRequirementReviewComment::deleteAll(['living_wage_requirement_checklist_review_id' => $reviewmodel->id]);
						AuditReportLivingWageRequirementReview::deleteAll(['id' => $reviewmodel->id]);
					}
				}
				else if($data['type']=='environment_list')
				{
					//AuditReportEnvironment::deleteAll(['and',['audit_id' => $data['audit_id']],['unit_id' => $data['unit_id']]]);
					AuditReportEnvironment::deleteAll(['unit_id' => $data['unit_id']]);
				}


				$responsedata=array('status'=>1,'message'=>'Remark Saved successfully');
			}

		}
		return $this->asJson($responsedata);

	}

	public function actionSaveTempAuditors()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        $data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		/*
		if ($data) 
		{
			$unittempmodel = AuditPlanUnitTemp::find()->where(['audit_id' => $data['audit_id']])->andWhere(['app_id' => $data['app_id']])->andWhere(['unit_id' => $data['unit_id']])->one();
			if($unittempmodel===null)
			{
				$unittempmodel = new AuditPlanUnitTemp();
				$unittempmodel->created_by = $userData['userid'];
			}
			else
			{
				$unittempmodel->updated_by = $userData['userid'];
				
				$auditortempmodel = AuditPlanUnitAuditorTemp::find()->where(['audit_plan_unit_temp_id' => $unittempmodel->id])->andWhere(['user_id' => $data['auditor_id']])->one();
				if($auditortempmodel!==null)
				{
					AuditPlanUnitAuditorTemp::deleteAll(['user_id' => $data['auditor_id']]);
					AuditPlanUnitAuditorDateTemp::deleteAll(['audit_plan_unit_auditor_temp_id' => $auditortempmodel->id]);
				}
			}

			$unittempmodel->audit_id = $data['audit_id'];
			$unittempmodel->app_id = $data['app_id'];
			$unittempmodel->unit_id = $data['unit_id'];
			if($unittempmodel->validate() && $unittempmodel->save())
			{
				$auditortempmodel = new AuditPlanUnitAuditorTemp();
				$auditortempmodel->audit_plan_unit_temp_id = $unittempmodel->id;
				$auditortempmodel->user_id = $data['auditor_id'];
				$auditortempmodel->created_by = $userData['userid'];
				if($auditortempmodel->validate() && $auditortempmodel->save())
				{
					if(is_array($data['auditor_dates']) && count($data['auditor_dates'])>0)
					{
						foreach ($data['auditor_dates'] as $value)
						{ 
							$datetempmodel = new AuditPlanUnitAuditorDateTemp();
							$datetempmodel->audit_plan_unit_auditor_temp_id=$auditortempmodel->id;
							$datetempmodel->date=date('Y-m-d',strtotime($value));
							$datetempmodel->created_by = $userData['userid'];
							$datetempmodel->save();
						}
					}
				}

				$responsedata=array('status'=>1,'message'=>'Saved successfully');
			}

		}
		*/
		$responsedata=array('status'=>1,'message'=>'Saved successfully');
		return $this->asJson($responsedata);
	}

	private function RemoveTempAuditors($audit_id,$app_id,$unit_id)
    {
		$unittempmodel = AuditPlanUnitTemp::find()->where(['audit_id' => $audit_id])->andWhere(['app_id' => $app_id])->andWhere(['unit_id' => $unit_id])->one();
		if($unittempmodel !== null)
		{
			$auditortempmodel = AuditPlanUnitAuditorTemp::find()->where(['audit_plan_unit_temp_id' => $unittempmodel->id])->all();
			if($auditortempmodel!==null)
			{
				foreach ($auditortempmodel as $auditor)
				{
					AuditPlanUnitAuditorDateTemp::deleteAll(['audit_plan_unit_auditor_temp_id' => $auditor->id]);
				}
				
				AuditPlanUnitAuditorTemp::deleteAll(['audit_plan_unit_temp_id' => $unittempmodel->id]);
			}
			AuditPlanUnitTemp::deleteAll(['id' => $unittempmodel->id]);
		}
	}

	public function actionRemoveAuditors()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        $data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		
		if ($data) 
		{
			$unittempmodel = AuditPlanUnitTemp::find()->where(['audit_id' => $data['audit_id']])->andWhere(['app_id' => $data['app_id']])->andWhere(['unit_id' => $data['unit_id']])->one();
			if($unittempmodel !== null)
			{
				$auditortempmodel = AuditPlanUnitAuditorTemp::find()->where(['audit_plan_unit_temp_id' => $unittempmodel->id])->andWhere(['user_id' => $data['auditor_id']])->one();
				if($auditortempmodel!==null)
				{
					AuditPlanUnitAuditorDateTemp::deleteAll(['audit_plan_unit_auditor_temp_id' => $auditortempmodel->id]);
					AuditPlanUnitAuditorTemp::deleteAll(['audit_plan_unit_temp_id' => $unittempmodel->id,'user_id' => $data['auditor_id']]);
				}
			}
		}
	}

	public function actionGetApplicableData()
    {
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$auditmodel = AuditReportApplicableDetails::find();

			if(isset($data['audit_id']) && $data['audit_id']>0){
				//$auditmodel = $auditmodel->where(['audit_id' => $data['audit_id']]);
			}
			if(isset($data['app_id']) && $data['app_id']>0){
				$auditmodel = $auditmodel->where(['app_id' => $data['app_id']]);
			}

			if(isset($data['unit_id']) && $data['unit_id']>0 && $data['type']!='supplier_list'){
				$auditmodel = $auditmodel->andWhere(['unit_id' => $data['unit_id']]);
			}
			if(isset($data['type']) && $data['type']!=''){
				$auditmodel = $auditmodel->andWhere(['report_name' => $data['type']]);
			}

			$auditmodel = $auditmodel->one();
			if($auditmodel!==null)
			{
				return ['status'=>$auditmodel->status,'comments'=>$auditmodel->comments];
			}
			
		}
		
	}
	
	public function actionCreateAuditPlan()
    {
		$AuditM=new Audit();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        $data = Yii::$app->request->post();
		
		if ($data) 
		{
			$userData = Yii::$app->userdata->getData();
			//$auditmodel = Audit::find()->where(['id' => $data['audit_id']])->one();
			
			if(isset($data['audit_id']) && $data['audit_id']!='')
			{
				$auditmodel = Audit::find()->where(['id' => $data['audit_id']])->one();	
			}else{
				return false;
			}
			if(!$this->canCreateAuditPlan($data['audit_id'])){
				return false;
			}

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
				
				if($auditmodel->audit_type ==2){
					$sector_group_ids = Yii::$app->globalfuns->getUnannouncedBusinessSectorGroups($auditmodel->id, $unit_id);
				}else{
					$unitbsgroup = ApplicationUnitBusinessSectorGroup::find()->where(['unit_id'=>$unit_id, 'standard_id'=>$standard_ids ])->all();
					if(count($unitbsgroup)>0){
						foreach($unitbsgroup as $gp){
							$sector_group_ids[$gp->business_sector_group_id]=$gp->business_sector_group_id;
						}
					}
				}
				
				
				/*
				if($auditmodel !==null){
					$app_id = $auditmodel->app_id;
				}else{
					$app_id = $data['app_id'];
				}
				*/
				
				if(isset($data['app_id']) && $data['app_id']!='')
				{
					$app_id = $data['app_id'];
				}
				
				if(isset($data['audit_id']) && $data['audit_id']!=''){
					if($auditmodel !==null)
					{
						$app_id = $auditmodel->app_id;
					}
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



			







			$audit_type = 1;
			$auditInsertStatus=false;
			if(isset($data['audit_id']) && $data['audit_id']!='')
			{
				$auditmodel = Audit::find()->where(['id' => $data['audit_id']])->one();
				if($auditmodel!==null)
				{
					$audit_type = $auditmodel->audit_type;
					//---------------Store the Audit Plan related data into history table code start here ----------------------
					//$auditObj = Audit::find()->where(['id' => $data['audit_id']])->one();
					
					$auditObj = Audit::find()->where(['id' => $data['audit_id'],'status'=>$AuditM->arrEnumStatus['rejected']])->one();
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
							$AuditPlanHistoryModel->comment=$auditPlanObj->comment;
							$AuditPlanHistoryModel->created_by=$auditPlanObj->created_by;
							$AuditPlanHistoryModel->created_at=$auditPlanObj->created_at;
							$AuditPlanHistoryModel->updated_by=$auditPlanObj->updated_by;
							$AuditPlanHistoryModel->updated_at=$auditPlanObj->updated_at;
							$AuditPlanHistoryModel->save();	
											
							$AuditPlanHistoryID = $AuditPlanHistoryModel->id;
							
							$customerreview = $auditPlanObj->customerreview;
							$AuditPlanCustomerReviewHistory = new AuditPlanCustomerReviewHistory();
							$AuditPlanCustomerReviewHistory->audit_plan_history_id = $AuditPlanHistoryID;
							$AuditPlanCustomerReviewHistory->user_id = $customerreview->user_id;
							$AuditPlanCustomerReviewHistory->audit_type = 1;
							$AuditPlanCustomerReviewHistory->comment = $customerreview->comment;
							$AuditPlanCustomerReviewHistory->created_by = $customerreview->created_by;
							$AuditPlanCustomerReviewHistory->save();



							$auditPlanUnitObj = $auditPlanObj->auditplanunit;
							if(count($auditPlanUnitObj)>0)
							{
								foreach($auditPlanUnitObj as $auditPlanUnit)
								{
									$AuditPlanUnitHistoryModel=new AuditPlanUnitHistory();
									$AuditPlanUnitHistoryModel->audit_plan_history_id = $AuditPlanHistoryID;
									$AuditPlanUnitHistoryModel->app_id=$auditPlanUnit->app_id;
									$AuditPlanUnitHistoryModel->unit_id=$auditPlanUnit->unit_id;
									$AuditPlanUnitHistoryModel->unit_lead_auditor=$auditPlanUnit->unit_lead_auditor;
									$AuditPlanUnitHistoryModel->technical_expert=$auditPlanUnit->technical_expert;
									$AuditPlanUnitHistoryModel->translator=$auditPlanUnit->translator;
									$AuditPlanUnitHistoryModel->observer=$auditPlanUnit->observer;
									$AuditPlanUnitHistoryModel->quotation_manday=$auditPlanUnit->quotation_manday;						
									$AuditPlanUnitHistoryModel->actual_manday=$auditPlanUnit->actual_manday;
									$AuditPlanUnitHistoryModel->status=$auditPlanUnit->status;
									$AuditPlanUnitHistoryModel->save();
									
									$auditPlanUnitHistoryID=$AuditPlanUnitHistoryModel->id;
										
									//Audit Plan  Unit Date
									$auditPlanUnitDatesObj = $auditPlanUnit->auditplanunitdate;
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
											
											$auditPlanUnitStandard->delete();
										}
									}							
									
									//Audit Plan  Unit Auditors
									$auditPlanUnitAuditorsObj = $auditPlanUnit->unitauditors;
									if(count($auditPlanUnitAuditorsObj)>0)
									{
										foreach($auditPlanUnitAuditorsObj as $auditPlanUnitAuditor)
										{
											$AuditPlanUnitAuditorHistoryModel=new AuditPlanUnitAuditorHistory();
											$AuditPlanUnitAuditorHistoryModel->audit_plan_unit_history_id=$auditPlanUnitHistoryID;
											$AuditPlanUnitAuditorHistoryModel->user_id=$auditPlanUnitAuditor->user_id;	
											$AuditPlanUnitAuditorHistoryModel->is_lead_auditor=$auditPlanUnitAuditor->is_lead_auditor;									
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
													$AuditPlanUnitAuditorDateHistoryModel->save();
													
													$auditPlanUnitAuditorDate->delete();
												}
											}
											$auditPlanUnitAuditor->delete();								
										}
									}								
									$auditPlanUnit->delete();
								}					
							}
							
							//Audit Plan Inspection
							$auditPlanInspectionObj = $auditPlanObj->auditplaninspection;
							if($auditPlanInspectionObj!==null)
							{   
								//AuditPlanInspectionHistory  AuditPlanInspectionPlanHistory
								$AuditPlanInspectionHistoryModel=new AuditPlanInspectionHistory();
								$AuditPlanInspectionHistoryModel->audit_plan_history_id = $AuditPlanHistoryID;
								$AuditPlanInspectionHistoryModel->created_by=$auditPlanInspectionObj->created_by;
								$AuditPlanInspectionHistoryModel->created_at=$auditPlanInspectionObj->created_at;
								$AuditPlanInspectionHistoryModel->updated_by=$auditPlanInspectionObj->updated_by;
								$AuditPlanInspectionHistoryModel->updated_at=$auditPlanInspectionObj->updated_at;	
								$AuditPlanInspectionHistoryModel->sent_by=$auditPlanInspectionObj->sent_by;
								$AuditPlanInspectionHistoryModel->sent_at=$auditPlanInspectionObj->sent_at;				
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
							
							$auditPlanReviewObj = $auditPlanObj->auditplanreview;
							if($auditPlanReviewObj!==null)
							{
								$AuditPlanReviewHistoryModel=new AuditPlanReviewHistory();
								$AuditPlanReviewHistoryModel->audit_plan_history_id = $AuditPlanHistoryID;
								$AuditPlanReviewHistoryModel->user_id=$auditPlanReviewObj->user_id;
								$AuditPlanReviewHistoryModel->comment=$auditPlanReviewObj->comment;
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
					//---------------Store the Audit Plan related data into history table code end here ----------------------
		
					$auditInsertStatus=true;	
				}
			}	
			
			if(!$auditInsertStatus)
			{
				$auditmodel = new Audit();
			}
				
			$auditmodel->app_id=$data['app_id'];
            $auditmodel->offer_id=isset($data['offer_id'])?$data['offer_id']:'';
			//$auditmodel->invoice_id=$data['invoice_id'];
			
			if($auditmodel->status == $auditmodel->arrEnumStatus['open']){
				$auditmodel->created_by=$userData['userid'];
			}
			$auditmodel->status=1;
			
			$auditmodel->updated_by=$userData['userid'];
			
			
			$appmodel = Application::find()->where(['id' => $data['app_id']])->one();
			$usercode = $appmodel->username->registration_id;
			$appuser_id =  $appmodel->username->id;

			if($audit_type != 2){
				$connection = Yii::$app->getDb();
				$command = $connection->createCommand("SELECT count(*) AS count FROM `tbl_application` WHERE DATE_FORMAT(FROM_UNIXTIME(created_at), '%Y') = '".date('Y')."' AND created_by='$appuser_id'");
				$result = $command->queryOne();
				if(count($result)>0)
				{
					$appcount = $result['count']+1;
					$usercode = $usercode."/AUD-".date('Y')."-".$appcount;
					$auditmodel->code = $usercode;
				}
			}
			if($auditmodel->validate() && $auditmodel->save())
        	{  

				$UnannouncedAuditApplication = UnannouncedAuditApplication::find()->where(['audit_id'=>$auditmodel->id ])->one();
				if($UnannouncedAuditApplication !== null){
					$UnannouncedAuditApplication->status = $UnannouncedAuditApplication->arrEnumStatus['audit_plan_in_process'];
					$UnannouncedAuditApplication->save();
				}

				


				$Applicationmodel = new Application();
				if($audit_type != 2){
					Yii::$app->globalfuns->updateApplicationOverallStatus($auditmodel->app_id, $Applicationmodel->arrEnumOverallStatus['audit_plan_in_progress']);
				}
        		

		        $auditID = $auditmodel->id;
				
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
							$auditPlanUnitDatesObj = $auditPlanUnit->auditplanunitdate;
							if(count($auditPlanUnitDatesObj)>0)
							{
								foreach($auditPlanUnitDatesObj as $auditPlanUnitDate)
								{																
									$auditPlanUnitDate->delete();
								}
							}				

							//Audit Plan  Unit Standard
							$auditPlanUnitStandardsObj = $auditPlanUnit->unitstandard;
							if(count($auditPlanUnitStandardsObj)>0)
							{
								foreach($auditPlanUnitStandardsObj as $auditPlanUnitStandard)
								{
									$auditPlanUnitStandard->delete();
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
											$auditPlanUnitAuditorDate->delete();
										}
									}
									$auditPlanUnitAuditor->delete();								
								}
							}								
							$auditPlanUnit->delete();
						}					
					}
				}				
				
				$model->audit_id=$auditID;				
				$model->application_lead_auditor=$data['application_lead_auditor'];
				
				//$model->created_by=$userData['userid'];

				if($model->status == $model->arrEnumStatus['open'] || $model->status == ''){
					$model->created_by=$userData['userid'];
				}
				$model->updated_by=$userData['userid'];
				if($audit_type ==2){
					$model->unannounced_audit_reason = $data['unannounced_audit_reason'];
					$model->share_plan_to_customer = $data['share_plan_to_customer'];
				}
				

				if($model->validate() && $model->save())
				{ 
					$auditPlanID = $model->id;
					
					$total_quotation_manday=0;
					$total_actual_manday=0;
					foreach ($data['units'] as $value)
					{ 
						
						$justifiedusersList = $this->getJustifiedUsers($appmodel->franchise_id,$value['unit_id']);
						
						$auditunitmodel=new AuditPlanUnit();
						$auditunitmodel->audit_plan_id=$auditPlanID;
						$auditunitmodel->app_id=$data['app_id'];
						$auditunitmodel->unit_id=$value['unit_id'];
						$auditunitmodel->unit_lead_auditor=$value['unit_lead_auditor'];
						$auditunitmodel->technical_expert=$value['technical_expert'];
						$auditunitmodel->translator=$value['translator'];
						$auditunitmodel->observer=isset($value['observer'])?$value['observer']:'';
						$auditunitmodel->quotation_manday=$value['quotation_manday'];
						$total_quotation_manday+=$value['quotation_manday'];
						$auditunitmodel->actual_manday=$value['actual_manday'];
						$total_actual_manday+=$value['actual_manday'];

						if($auditunitmodel->validate() && $auditunitmodel->save())
        				{
							$auditPlanUnitID = $auditunitmodel->id;
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
							if(is_array($value['auditor']) && count($value['auditor'])>0)
							{
								foreach ($value['auditor'] as $auditor)
								{
									$auditunitauditormodel=new AuditPlanUnitAuditor();
									$auditunitauditormodel->audit_plan_unit_id=$auditPlanUnitID;
									$auditunitauditormodel->user_id=$auditor['user_id'];

									
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
					$model->application_lead_auditor=$data['application_lead_auditor'];
					
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
		
				$unitbsgroup = ApplicationUnitBusinessSectorGroup::find()->where(['unit_id'=>$unit_id, 'standard_id'=>$standard_ids ])->all();
				
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

	private function getAuditorViewList($unitauditors){
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$unitaudarr = [];
		$chkAuditorIds = [];
		if(count($unitauditors)>0){
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
			//unitaudarr
			//chkAuditorIds
		}
		return ['auditors'=> $unitaudarr, 'auditorIds'=>$chkAuditorIds];
	}

	public function actionView()
    {
		$auditmodel=new Audit();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$data = Yii::$app->request->post();
		
		if ($data) 
		{

			if(!$this->canViewAuditPlan($data['id'])){
				return false;
			}

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

				$auditID = $modelModel->id;
				$model = AuditPlan::find()->where(['audit_id' => $auditID])->one();
				$inspectionentries = 0;
				if($modelModel->followup_status == 1){
					$auditplaninspection = $model->followupauditplaninspection;
					if($auditplaninspection && count($auditplaninspection->auditplaninspectionplan)>0 ){
						$inspectionentries = 1;
					}
				}else{
					$auditplaninspection = $model->auditplaninspection;
					if($auditplaninspection && count($auditplaninspection->auditplaninspectionplan)>0 ){
						$inspectionentries = 1;
					}
				}
				


				$showsendtocustomer = 0;
				$showInspectionApproval = 0;
				
				if($modelModel->status == $modelModel->arrEnumStatus['inspection_plan_in_process'] && $inspectionentries ==1){
					$showsendtocustomer = 1;
				}
				
				
				$audit_type = $modelModel->audit_type;
				if($audit_type == $modelModel->audittypeEnumArr['unannounced_audit'] 
				&& $modelModel->status == $modelModel->arrEnumStatus['inspection_plan_in_process']
				&& $inspectionentries == 1){
					$share_plan_to_customer = $modelModel->auditplan->share_plan_to_customer;
					if($share_plan_to_customer == $modelModel->auditplan->arrSharePlanEnum['donot_share']
						|| $share_plan_to_customer == $modelModel->auditplan->arrSharePlanEnum['share_by_email'])
					{
						$showsendtocustomer = 0;
						$showInspectionApproval = 1;
					}
				}
				
				$resultarr["showsendtocustomer"]=$showsendtocustomer;
				$resultarr["showInspectionApproval"]=$showInspectionApproval;
				$resultarr["canChangeMaterialComp"]=Yii::$app->globalfuns->canChangeMaterialComp($modelModel->app_id, $modelModel->id);
				//canChangeMaterialComp($modelModel->app_id, $modelModel->id);
				

				$resultarr["arrEnumStatus"]=$modelModel->arrEnumStatus;
				
				$resultarr["audit_type"]=$modelModel->audit_type;
				
				$resultarr["status"]=$modelModel->status;

				$resultarr["followup_status"]=$modelModel->followup_status;

				$resultarr["status_name"]=$auditmodel->arrStatus[$modelModel->status];
				$resultarr["created_by"]=$modelModel->created_by;
				$resultarr["created_by_name"]=$modelModel->created_by?$modelModel->user->first_name.' '.$modelModel->user->last_name:'';
				$resultarr["created_at"]=date($date_format,$modelModel->created_at);
				
				//$modelModel->created_by;
				

				$ModelAPUExecutionChecklist = new AuditPlanUnitExecutionChecklist();
				/*
				$model = $model->innerJoinWith(['auditplanunit as auditplanunit']);
				$model = $model->join('inner join', 'tbl_audit_plan_unit_auditor as unit_auditor','auditplanunit.id=unit_auditor.audit_plan_unit_id');
				if($user_type== Yii::$app->params['user_type']['user']  && in_array('audit_execution',$rules) && $modelModel->auditplan->application_lead_auditor !== $userid){
					$model = $model->andWhere('unit_auditor.user_id='.$userid);
				}
				*/

				//$model = $model->one();
				
				if ($model !== null)
				{
					
					$audit_followup_status = $modelModel->followup_status;
					$standardIds = [];
					$application = $model->audit->application;
					$applicationstandard = $application->applicationstandard;
					if(count($applicationstandard)>0){
						foreach($applicationstandard as $appstandard){
							$standardIds[] = $appstandard->standard_id;
						}
					}
					$resultarr["reviewer_canassign"]=0;
					$UserStandard = UserStandard::find()->where(['user_id'=>$userid, 'standard_id'=>$standardIds,'approval_status'=>2 ])->all();
					if(count($UserStandard) == count($standardIds)){
						$resultarr["reviewer_canassign"]=1;
					}

					$resultarr["reviewer_details"] = '';
					if($model->reviewer !==null){
						$technicalexpertnames = [];
						$reviewerdata = $model->reviewer;
						if(count($reviewerdata->technicalexperts)>0){
							foreach($reviewerdata->technicalexperts as $technicalexp){
								$technicalexpertnames[] = $technicalexp->user->first_name.' '.$technicalexp->user->last_name;
							}
						}
						$resultarr["reviewer_details"]= [
										'reviewer_name'=>$reviewerdata->user->first_name.' '.$reviewerdata->user->last_name,
										'created_at'=> date($date_format,$reviewerdata->created_at),
										'technicalexpertnames' => $technicalexpertnames
										];
					}
					

					$resultarr["share_plan_to_customer"]=$model->share_plan_to_customer;
					$resultarr["share_plan_to_customer_label"]=$model->share_plan_to_customer>=0 && isset($model->arrSharePlan[$model->share_plan_to_customer])?$model->arrSharePlan[$model->share_plan_to_customer]:'';
					$resultarr["unannounced_audit_reason"]=$model->unannounced_audit_reason;

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

					if($modelModel->status == $auditmodel->arrEnumStatus['rejected'])
					{
						$rejcustomerreview = $model->customerreview;
						if($rejcustomerreview !== null)
						{	
							$resultarr["customer_review_created_by_name"] = $rejcustomerreview->reviewer->first_name." ".$rejcustomerreview->reviewer->last_name;
							$resultarr["customer_review_created_at"] = date($date_format,$rejcustomerreview->created_at); 
							$resultarr["customer_review_comment"] = $rejcustomerreview->comment;
						}
					}
						


					if($audit_followup_status == 1){
						$resultarr["followup_application_lead_auditor_name"]=$model->followup_application_lead_auditor?$model->followupuser->first_name.' '.$model->followupuser->last_name:'';
						$resultarr["followup_application_lead_auditor"]=$model->followup_application_lead_auditor;
						$resultarr["followup_actual_manday"]=$model->followup_actual_manday;
						
						$resultarr["followup_created_by"]=$model->followup_created_by;
						$resultarr["followup_created_by_name"]=$model->followupcreatedbyuser?$model->followupcreatedbyuser->first_name.' '.$model->followupcreatedbyuser->last_name:'';
						$resultarr["followup_created_at"]=date($date_format,$model->followup_created_at);

						if($modelModel->status == $auditmodel->arrEnumStatus['followup_rejected_by_customer'])
						{
							$followupcustomerreview = $model->followupcustomerreview;
							if($followupcustomerreview !== null)
							{	
								$resultarr["followup_customer_review_created_by_name"] = $followupcustomerreview->reviewer->first_name." ".$followupcustomerreview->reviewer->last_name; 
								$resultarr["followup_customer_review_created_at"] = date($date_format,$followupcustomerreview->created_at); 
								$resultarr["followup_customer_review_comment"] = $followupcustomerreview->comment; 
							}
						}
					}
					

					
					//$resultarr["status"]=$model->status;
					$resultarr["created_at"]=date($date_format,$model->created_at);
					
					
					$resultarr["company_name"]=$modelModel->application->companyname;
					$resultarr["address"]=$modelModel->application->address;
					$resultarr["zipcode"]=$modelModel->application->zipcode;
					$resultarr["city"]=$modelModel->application->city;
					$resultarr["country_name"]=$modelModel->application->countryname;
					$resultarr["state_name"]=$modelModel->application->statename;
					
					$resultarr["app_id"]=$modelModel->app_id;
					$resultarr["offer_id"]=$modelModel->offer_id;
					$resultarr["invoice_id"]=$modelModel->invoice_id;
					$resultarr["show_followup_status"]=1;
					
					$auditplanUnit=$model->auditplanunit;
					if(count($auditplanUnit)>0)
					{
						$showCertificateGenerate = 1;
						$showSubmitFollowupAudit=0;
						
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
								/*
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
								*/
								$auditordetails = $this->getAuditorViewList($unitauditors);
								$unitsarr["auditors"]= $auditordetails['auditors']; //$unitaudarr;
								$unitsarr["auditorIds"]= $auditordetails['auditorIds']; //$chkAuditorIds;
							}

							$unitsarr["followup_auditors"] = [];
							$unitsarr["followup_auditorIds"] = [];
							if($unit->followup_status == 1){
								$unitauditors=$unit->followupunitauditors;
								if(count($unitauditors)>0)
								{
									$auditordetails = $this->getAuditorViewList($unitauditors);
									$unitsarr["followup_auditors"]= $auditordetails['auditors']; //$unitaudarr;
									$unitsarr["followup_auditorIds"]= $auditordetails['auditorIds'];
								}
							}
							if(!in_array('audit_review',$rules) && !in_array('generate_audit_plan',$rules) ){
								if($user_type== Yii::$app->params['user_type']['user']  && in_array('audit_execution',$rules) && $modelModel->auditplan->application_lead_auditor !== $userid){
									//$model = $model->andWhere('unit_auditor.user_id='.$userid);
									//$unit->
									if( !in_array($userid,$unitsarr["auditorIds"]) && !in_array($userid,$unitsarr["followup_auditorIds"]) ){
										continue; 
									}
								}
							}
							/*
							if($user_type== Yii::$app->params['user_type']['user']  && in_array('audit_execution',$rules) && $modelModel->auditplan->application_lead_auditor !== $userid){
								//$model = $model->andWhere('unit_auditor.user_id='.$userid);
								//$unit->
								if( !in_array($userid,$chkAuditorIds)){
									continue; 
								}
							}
							*/


							$auditexe = AuditPlanUnitExecution::find()->where(['audit_plan_unit_id'=>$unit->id])->all();
							$auditsubtopiccount = count($auditexe);
							$subtopicArr = [];
							


							//$unitsubtopics = $this->getCurrentSubtopic($unit->unit_id,$unit->id);
							$unitsubtopics = Yii::$app->globalfuns->getCurrentSubtopic($unit->unit_id,$unit->id);
							
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
							$followupsubtopicArr = [];
							if($unit->followup_status == 1){
								//$unitsubtopics = $this->getFollowupSubtopic($unit->unit_id,$unit->id);
								$AuditPlanUnitExecutionFollowup = AuditPlanUnitExecutionFollowup::find()->where(['audit_plan_unit_id'=>$unit->id])->all();
								foreach($AuditPlanUnitExecutionFollowup as $subtopic){
									//$subtopic['status']= $subtopic['status']?:0;
									$followupsubtopicArr[] = [
										'id' => $subtopic->sub_topic_id,
										'name' => $subtopic->subtopic->name,
										'display_name' => $subtopic->executedby?$subtopic->executedby->first_name.' '.$subtopic->executedby->last_name :'NA',
										'executed_date' => $subtopic->executed_date?date($date_format,$subtopic->executed_date):'NA',
										'status_name' => $subtopic->arrStatus[$subtopic->status]
									];
								}
							}


							//$resultarr["created_by"]=$this->isSubtopicAssigned();
							$unitsarr['is_subtopic_assigned']=$this->isSubtopicAssigned($unit->id);
							$unitsarr['show_assign_subtopic']=$this->isShowAssignSubtopic($unit->id);
							$unitsarr['followup_show_assign_subtopic']=$this->isFollowupShowAssignSubtopic($unit->id);
							$unitsarr['is_followup_subtopic_assigned']=$this->isFollowupSubtopicAssigned($unit->id);

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

							


							//Followup Audit Details Go here
							/*
							$unitsarr['unit_lead_auditor']=$unit->unitleadauditor->first_name." ".$unit->unitleadauditor->last_name;
							$unitsarr['unit_lead_auditor_id']=$unit->unit_lead_auditor;
							$unitsarr['technical_expert_id']=$unit->technical_expert;
							$unitsarr['translator_id']=$unit->translator;
							$unitsarr['observer']=($unit->observer!='' ? $unit->observer : 'NA');
							$unitsarr['technical_expert']=($unit->unittechnicalexpert)?$unit->unittechnicalexpert->first_name." ".$unit->unittechnicalexpert->last_name:'';
							$unitsarr['translator']=($unit->unittranslator)?$unit->unittranslator->first_name." ".$unit->unittranslator->last_name:'';
							*/


							
							
							$unitsarr['followup_status']=$unit->followup_status;
							//$unitsarr['show_followup_status']= 1;
							if($unit->followup_status == 1){
								$unitsarr['followup_subtopics']= $followupsubtopicArr;
								$unitsarr['followup_subtopics_count']= count($followupsubtopicArr);

								$unitsarr['followup_technical_expert']=($unit->followupunittechnicalexpert)?$unit->followupunittechnicalexpert->first_name." ".$unit->followupunittechnicalexpert->last_name:'';
								$unitsarr['followup_translator']=($unit->followupunittranslator)?$unit->followupunittranslator->first_name." ".$unit->followupunittranslator->last_name:'';
								$unitsarr['followup_observer']=($unit->followup_observer!='' ? $unit->followup_observer : 'NA');
								$unitsarr['followup_actual_manday']=$unit->followup_actual_manday;
								$unitsarr['followup_unit_lead_auditor']=($unit->followupunitleadauditor)?$unit->followupunitleadauditor->first_name." ".$unit->followupunitleadauditor->last_name:'NA';
								$unitsarr['followup_unit_lead_auditor_id']=$unit->followup_unit_lead_auditor;
							}
							
							
							
							// ------ Findings Count Start Here -------	
							$executionlistallObj = $unit->executionlistall;
							$executionlistall=count($executionlistallObj);
														
							$executionlistnoncomformityObj = $unit->executionlistnoncomformity;
							$executionlistnoncomformity=count($executionlistnoncomformityObj);

							$followupexecutionlistnoncomformityObj = $unit->followupexecutionlistnoncomformity;
							$followupexecutionlistnoncomformity=count($followupexecutionlistnoncomformityObj);
																	
							$unitsarr['total_findings']= $executionlistall;
							$unitsarr['total_non_conformity']= $executionlistnoncomformity;
							$unitsarr['followup_total_non_conformity']= $followupexecutionlistnoncomformity;
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



							$unitdate=$unit->followupauditplanunitdate;
							$unitdatearr=array();
							if(count($unitdate)>0)
							{	
								foreach($unitdate as $unitd)
								{
									$unitdatearr[]=date($date_format,strtotime($unitd->date));
									//echo $date_format.'--'.$unitd->date;
								}
							}
							$unitsarr["followup_date"]=$unitdatearr;




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

						$showSubmitFollowupRemediationForReviewer = 0;
						$showSendBackFollowupRemediationToLeadAuditor = 0;
						$showSendBackFollowupRemediationToUnitAuditor = 0;
						/*
						$arrChecklistStatusCnt[0] = 0;
						$arrChecklistStatusCnt[1] = 0;
						$arrChecklistStatusCnt[2] = 0;
						$arrChecklistStatusCnt[3] = 0;
						$arrChecklistStatusCnt[4] = 0;
						$arrChecklistStatusCnt[5] = 0;
						*/
						$exechecklist = new AuditPlanUnitExecutionChecklist();
						foreach($exechecklist->arrStatus as $statuskey => $statusvalue){
							$arrFollowupChecklistStatusCnt[$statuskey] = 0;
							$arrChecklistStatusCnt[$statuskey] = 0;
						}

						$impPlanUnitIds = implode(',',$planunitIds);//$planunitIds
						$command = $connection->createCommand("SELECT COUNT(checklist.id) as chkcnt,checklist.status as status FROM 
										tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.audit_plan_unit_id IN (".$impPlanUnitIds.") AND checklist.answer=2 GROUP BY checklist.status"); // AND checklist.status = 1
						$result = $command->queryAll();
						$totalChkFinding =0;
						
						if(count($result )>0){
							foreach($result  as $statuschklist){
								$arrChecklistStatusCnt[$statuschklist['status']] = $statuschklist['chkcnt'];
								$totalChkFinding += $statuschklist['chkcnt'];
							}
						}

						
						/*
						To get Followup Total Starts
						*/						
						$commandfollow = $connection->createCommand("SELECT COUNT(checklist.id) as chkcnt,checklist.status as status FROM 
										tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.audit_plan_unit_id IN (".$impPlanUnitIds.") AND checklist.finding_type=2 AND checklist.answer=2 GROUP BY checklist.status"); // AND checklist.status = 1
						$resultfollow = $commandfollow->queryAll();
						$totalFollowupChkFinding =0;
						if(count($resultfollow )>0){
							foreach($resultfollow  as $statuschklistf){
								$arrFollowupChecklistStatusCnt[$statuschklistf['status']] = $statuschklistf['chkcnt'];
								$totalFollowupChkFinding += $statuschklistf['chkcnt'];
							}
						}
						/*
						To get Followup Total Ends
						*/


						if($model->status == $model->arrEnumStatus['remediation_in_progress']){
							

							/*
							$command2 = $connection->createCommand("SELECT * FROM  tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.unit_id IN (".implode($unitIds).") AND checklist.answer=2 
										 AND checklist.status in (1,3,4,5) "); // AND checklist.status = 1
							$result2 = $command2->queryAll();
							*/

							//$totchk = $arrChecklistStatusCnt[1] + $arrChecklistStatusCnt[3] + $arrChecklistStatusCnt[4] + $arrChecklistStatusCnt[5];
							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['in_progress']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['waiting_for_approval']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['reviewer_change_request']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']];
							if($totalChkFinding ==  $totchk){
								$showSubmitRemediationForAuditor = 1;
							}
							
						}

						if($model->status == $model->arrEnumStatus['auditor_review_in_progress']){
							
							/*$command2 = $connection->createCommand("SELECT * FROM  tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.unit_id IN (".implode($unitIds).") AND checklist.answer=2
										  AND checklist.status IN (3,5) "); // AND checklist.status = 1
							$result2 = $command2->queryAll();*/

							//$totchk = $arrChecklistStatusCnt[3] + $arrChecklistStatusCnt[5];
							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['waiting_for_approval']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']];
							if($arrChecklistStatusCnt[$exechecklist->arrEnumStatus['in_progress']]<=0 && $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['auditor_change_request']]<=0 && $totalChkFinding ==  $totchk){
								$showSubmitRemediationForReviewer = 1;
							}
							/*
							if($arrChecklistStatusCnt[1]<=0 && $arrChecklistStatusCnt[2]<=0 && $totalChkFinding ==  $totchk){
								$showSubmitRemediationForReviewer = 1;
							}
							*/
							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['waiting_for_approval']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']] + $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['in_progress']];
							if($totalChkFinding ==  $totchk){
								$showSubmitRemediationForReviewer = 1;
							}
						}


						if($model->status == $model->arrEnumStatus['followup_waiting_for_lead_auditor']){
							//echo $totalChkFinding;
							//print_r($arrFollowupChecklistStatusCnt);
							//print_r($arrChecklistStatusCnt);
							/*
							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['waiting_for_approval']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']];
							if($arrChecklistStatusCnt[$exechecklist->arrEnumStatus['in_progress']]<=0 && $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['auditor_change_request']]<=0 && $totalChkFinding ==  $totchk){
								$showSubmitFollowupRemediationForReviewer = 1;
							}
							*/
							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']] 
								+ $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['not_accepted']]
							 	+ $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['followup_waiting_for_review_approval']]
							 	+ $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['followup_lead_auditor_not_accepted']];
							if($totalChkFinding ==  $totchk){
								$showSubmitFollowupRemediationForReviewer = 1;
							}
							
						}

						if($model->status == $model->arrEnumStatus['followup_reviewinprogress']){
							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']] 
								+ $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['not_accepted']]
							 	+ $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['reviewer_change_request']];
							 	//+ $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['followup_lead_auditor_not_accepted']];
							if($totalChkFinding ==  $totchk && $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['reviewer_change_request']]>0 ){
								$showSendBackFollowupRemediationToLeadAuditor = 1;
							}
							
						}

						if($model->status == $model->arrEnumStatus['followup_waiting_for_lead_auditor']){
							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']] 
								 + $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['not_accepted']]
								 + $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['auditor_change_request']]
								 + $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['followup_waiting_for_review_approval']]
								 + $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['followup_lead_auditor_not_accepted']];
								 //+ $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['followup_lead_auditor_not_accepted']];
								 //print_r($arrFollowupChecklistStatusCnt);
								 //print_r($arrChecklistStatusCnt);
								 //echo $totalChkFinding.' ==  '.$totchk;
							if($totalChkFinding ==  $totchk && $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['auditor_change_request']]>0 ){
								$showSendBackFollowupRemediationToUnitAuditor = 1;
							}
							
						}
						
						
						if($model->status == $model->arrEnumStatus['reviewer_review_in_progress']){
							/*$command2 = $connection->createCommand("SELECT * FROM  tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.unit_id IN (".implode($unitIds).") AND checklist.answer=2
										  AND checklist.status IN (4,5) "); // AND checklist.status = 1
							$result2 = $command2->queryAll();
							*/
							/*
							$totchk = $arrChecklistStatusCnt[4] + $arrChecklistStatusCnt[5];

							if($arrChecklistStatusCnt[4] > 0 && $totalChkFinding ==  $totchk){
								$showSendBackRemediationToAuditor = 1;
							}
							*/
							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['reviewer_change_request']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']];

							if($arrChecklistStatusCnt[$exechecklist->arrEnumStatus['reviewer_change_request']] > 0 && $totalChkFinding ==  $totchk){
								$showSendBackRemediationToAuditor = 1;
							}


							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['reviewer_change_request']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']] + $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['in_progress']];
							if($arrChecklistStatusCnt[$exechecklist->arrEnumStatus['reviewer_change_request']] > 0 && $totalChkFinding ==  $totchk){
								$showSendBackRemediationToAuditor = 1;
							}
						}

						if($model->status == $model->arrEnumStatus['auditor_review_in_progress']){
							
							/*$command2 = $connection->createCommand("SELECT * FROM  tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.unit_id IN (".implode($unitIds).") AND checklist.answer=2
										  AND checklist.status IN (2,3,5) "); // AND checklist.status = 1
							$result2 = $command2->queryAll();
							*/
							/*
							$totchk = $arrChecklistStatusCnt[2] + $arrChecklistStatusCnt[3] + $arrChecklistStatusCnt[5];
							if($arrChecklistStatusCnt[2] > 0 && $totalChkFinding ==  $totchk){
								$showSendBackRemediationToCustomer = 1;
							}
							*/
							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['auditor_change_request']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['waiting_for_approval']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']];
							if($arrChecklistStatusCnt[$exechecklist->arrEnumStatus['auditor_change_request']] > 0 && $totalChkFinding ==  $totchk){
								$showSendBackRemediationToCustomer = 1;
							}


							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['auditor_change_request']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['waiting_for_approval']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']]  + $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['in_progress']];
							if($arrChecklistStatusCnt[$exechecklist->arrEnumStatus['auditor_change_request']] > 0 && $totalChkFinding ==  $totchk){
								$showSendBackRemediationToCustomer = 1;
							}
						}


						$commandfollow = $connection->createCommand("SELECT COUNT(checklist.id) as chkcnt,checklist.status as status FROM 
										tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.audit_plan_unit_id IN (".$impPlanUnitIds.") AND checklist.finding_type=2 AND checklist.answer=2 GROUP BY checklist.status"); // AND checklist.status = 1
						$resultfollow = $commandfollow->queryAll();
						$totalFollowupChkFinding =0;
						if(count($resultfollow )>0){
							foreach($resultfollow  as $statuschklistf){
								$arrFollowupChecklistStatusCnt[$statuschklistf['status']] = $statuschklistf['chkcnt'];
								$totalFollowupChkFinding += $statuschklistf['chkcnt'];
							}
						}
						$command = $connection->createCommand("SELECT COUNT(checklist.id) as chkcnt,checklist.status as status FROM 
										tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.audit_plan_unit_id IN (".$impPlanUnitIds.") AND checklist.finding_type=1 AND checklist.answer=2 GROUP BY checklist.status"); // AND checklist.status = 1
						$result = $command->queryAll();
						$totalChkFinding =0;
						
						if(count($result )>0){
							foreach($result  as $statuschklist){
								$arrChecklistStatusCnt[$statuschklist['status']] = $statuschklist['chkcnt'];
								$totalChkFinding += $statuschklist['chkcnt'];
							}
						}
						if($arrFollowupChecklistStatusCnt[$ModelAPUExecutionChecklist->arrEnumStatus['in_progress']]==$totalFollowupChkFinding && $arrChecklistStatusCnt[$ModelAPUExecutionChecklist->arrEnumStatus['settled']]==$totalChkFinding){
							$showSubmitFollowupAudit=1;
						}

						$resultarr["showSubmitFollowupAudit"]=$showSubmitFollowupAudit;


						$resultarr["showSubmitRemediationForAuditor"]=$showSubmitRemediationForAuditor;
						$resultarr["showSubmitRemediationForReviewer"]=$showSubmitRemediationForReviewer;
						$resultarr["showSendBackRemediationToCustomer"]=$showSendBackRemediationToCustomer;
						$resultarr["showSendBackRemediationToAuditor"]=$showSendBackRemediationToAuditor;
						$resultarr["showSubmitFollowupRemediationForReviewer"]=$showSubmitFollowupRemediationForReviewer;
						$resultarr["showSendBackFollowupRemediationToLeadAuditor"]=$showSendBackFollowupRemediationToLeadAuditor;
						$resultarr["showSendBackFollowupRemediationToUnitAuditor"]=$showSendBackFollowupRemediationToUnitAuditor;
						

						//$unitIds
						$resultarr["showCertificateGenerate"]=$showCertificateGenerate;
						$resultarr["units"]=$unitarr;							
						$auditplanunitmodel=new AuditPlanUnit();
						$resultarr["arrUnitEnumStatus"]=$auditplanunitmodel->arrEnumStatus;
					}
					
					$auditinspection=$model->auditplaninspection;
					if($auditinspection!==null)
					{	
						$resultarr["inspectionplan_id"]=$auditinspection->id;
						//$auditinspectionarr=array();
						$planarr=array();
						$auditinspectionplan=$auditinspection->auditplaninspectionplan;
						foreach($auditinspectionplan as $arr)
						{
							$inspector_names = '';
							if(count($arr->auditplaninspectionplaninspector)>0)
							{
								$inspectors = [];
								foreach($arr->auditplaninspectionplaninspector as $inspector)
								{
									$inspectors[] = $inspector->user->first_name." ".$inspector->user->last_name;
								}
								$inspector_names = implode(", ",$inspectors);
							}

							$temparr=array();
							$temparr["inspection_id"]=$arr->id;
							$temparr["application_unit_name"]=($arr->applicationunit!==null ? $arr->applicationunit->name : 'NA');
							$temparr["activity"]=$arr->activity;
							$temparr["inspector"]=$inspector_names;
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

						$resultarr["inspection_sent_by"]=$auditinspection->sentdata?$auditinspection->sentdata->first_name.' '.$auditinspection->sentdata->last_name:'NA';
						$resultarr["inspection_sent_at"]=$auditinspection->sent_at!=''?date($date_format,$auditinspection->sent_at):'NA';
						$resultarr["inspection_created_by"]=$auditinspection->createddata?$auditinspection->createddata->first_name.' '.$auditinspection->createddata->last_name:'NA';
						$resultarr["inspection_created_at"]=date($date_format,$auditinspection->created_at);

					}

					$auditinspection=$model->followupauditplaninspection;
					if($auditinspection!==null)
					{	
						$resultarr["followupinspectionplan_id"]=$auditinspection->id;
						//$auditinspectionarr=array();
						$planarr=array();
						$auditinspectionplan=$auditinspection->auditplaninspectionplan;
						foreach($auditinspectionplan as $arr)
						{
							$inspector_names = '';
							if(count($arr->auditplaninspectionplaninspector)>0)
							{
								$inspectors = [];
								foreach($arr->auditplaninspectionplaninspector as $inspector)
								{
									$inspectors[] = $inspector->user->first_name." ".$inspector->user->last_name;
								}
								$inspector_names = implode(", ",$inspectors);
							}

							$temparr=array();
							$temparr["inspection_id"]=$arr->id;
							$temparr["application_unit_name"]=($arr->applicationunit!==null ? $arr->applicationunit->name : 'NA');
							$temparr["activity"]= $arr->activity;
							$temparr["inspector"]=$inspector_names;
							//$temparr["inspector"]=$arr->inspector;
							$temparr["date"]=date($date_format,strtotime($arr->date));
							$temparr["start_time"]=date('G:i', strtotime($arr->start_time));
							$temparr["end_time"]=date('G:i', strtotime($arr->end_time));
							$temparr["person_need_to_be_present"]=$arr->person_need_to_be_present;
							$temparr["application_unit_id"]=($arr->applicationunit!==null ? $arr->applicationunit->id : 'NA');
							$temparr["application_unit_name"]=($arr->applicationunit!==null ? $arr->applicationunit->name : 'NA');							
							$planarr[]=$temparr;
						}
						//$auditinspectionarr[]=$planarr;											
						$resultarr["followupinspectionplan"]=$planarr;

						$resultarr["followupinspection_sent_by"]=$auditinspection->sentdata?$auditinspection->sentdata->first_name.' '.$auditinspection->sentdata->last_name:'NA';
						$resultarr["followupinspection_sent_at"]=$auditinspection->sent_at!=''?date($date_format,$auditinspection->sent_at):'NA';
						$resultarr["followupinspection_created_by"]=$auditinspection->createddata?$auditinspection->createddata->first_name.' '.$auditinspection->createddata->last_name:'NA';
						$resultarr["followupinspection_created_at"]=date($date_format,$auditinspection->created_at);
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
								//print_r($unitnamedetailsarr);
								//$enteredunits = [];
								foreach($unitreviewcommentarr as $unitkey => $units)
								{
									$unitname = '';
									if(isset($unitnamedetailsarr[$unitkey]))
									{
										$unitname = $unitnamedetailsarr[$unitkey];
									}
									$unitreviews[]=array('unit_name'=>$unitname,'unit_id'=>$unitkey,'reviews'=>$units);
									
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



					$followupreview=$model->followupauditplanreview;
					$reviewcommentarr = [];
					$followupauditreviews=[];
					if($followupreview !== null)
					{
						//foreach($auditReview as $review)
						if(1)
						{
							$followupreviewarr=[];
							$followupreviewcommentarr=[];
							$auditreviewcmt=$followupreview->auditplanreviewchecklistcomment;
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
							$unitauditreviewcmt=$followupreview->auditplanunitreviewcomment;
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
							
							
							
							$followupreviewarr['reviewcomments']=$reviewcommentarr;
							$followupreviewarr['unitreviewcomments']=$unitreviews;
							$followupreviewarr['reviewer']=($followupreview->reviewer?$review->reviewer->first_name.' '.$followupreview->reviewer->last_name:'');
							$followupreviewarr['answer']=$followupreview->answer;
							
							$followupreviewarr['answer_name']=$followupreview->answer?$review->arrReviewAnswer[$followupreview->answer]:'NA';
							
							$followupreviewarr['status']=$followupreview->status;		
							$followupreviewarr['status_name']=$followupreview->arrReviewStatus[$followupreview->status];					
																	
							$followupreviewarr['created_at']=date($date_format,$followupreview->created_at);

							$followupreviewarr['status_comments']=$followupreview->comment;
							$followupreviewarr['review_result_name']=isset($followupreview->arrReviewResult[$followupreview->review_result])?$followupreview->arrReviewResult[$followupreview->review_result]:'';
							$followupreviewarr['review_result']=$followupreview->review_result;

							$followupauditreviews[]=$followupreviewarr;
						}
						$resultarr["followupauditreviews"]=$followupauditreviews;
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
						$arr_history["application_lead_auditor_name"]=$auditPlan->leadauditor->first_name." ".$auditPlan->leadauditor->last_name;
						$arr_history["quotation_manday"]=$auditPlan->quotation_manday;
						$arr_history["actual_manday"]=$auditPlan->actual_manday;
						$arr_history["created_at"]=date($date_format,$auditPlan->created_at);
						$arr_history["created_by_name"]=$auditPlan->user->first_name." ".$auditPlan->user->last_name;
						
						
						$arr_history["company_name"]=$modelModel->application->companyname;
						$arr_history["address"]=$modelModel->application->address;
						$arr_history["zipcode"]=$modelModel->application->zipcode;
						$arr_history["city"]=$modelModel->application->city;
						$arr_history["country_name"]=$modelModel->application->countryname;
						$arr_history["state_name"]=$modelModel->application->statename;

						$rejcustomerreview = $auditPlan->customerreviewhistory;
						if($rejcustomerreview !== null)
						{	
							$arr_history["customer_review_created_by_name"] = $rejcustomerreview->reviewer->first_name." ".$rejcustomerreview->reviewer->last_name;
							$arr_history["customer_review_created_at"] = date($date_format,$rejcustomerreview->created_at); 
							$arr_history["customer_review_comment"] = $rejcustomerreview->comment;
						}

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
								$inspector_names = '';
								if(count($arr->planinspector)>0)
								{
									$inspectors = [];
									foreach($arr->planinspector as $inspector)
									{
										$inspectors[] = $inspector->user->first_name." ".$inspector->user->last_name;
									}
									$inspector_names = implode(", ",$inspectors);
								}

								$temparr=array();
								$temparr["inspection_id"]=$arr->id;
								$temparr["application_unit_name"]=($arr->applicationunit?$arr->applicationunit->name:'NA');
								$temparr["activity"]=$arr->activity;
								$temparr["inspector"]=$inspector_names;
								//$temparr["inspector"]=$arr->inspector;
								$temparr["date"]=date($date_format,strtotime($arr->date));
								
								$temparr["start_time"]=date('G:i', strtotime($arr->start_time));
								$temparr["end_time"]=date('G:i', strtotime($arr->end_time));
							
								$temparr["person_need_to_be_present"]=$arr->person_need_to_be_present;
								$planarr[]=$temparr;
							}
							//$auditinspectionarr[]=$planarr;											
							$arr_history["inspectionplan"]=$planarr;

							$arr_history["inspection_sent_by"]=$auditinspection->sentdata?$auditinspection->sentdata->first_name.' '.$auditinspection->sentdata->last_name:'NA';
							$arr_history["inspection_sent_at"]=$auditinspection->sent_at!=''?date($date_format,$auditinspection->sent_at):'NA';
							$arr_history["inspection_created_by"]=$auditinspection->createddata?$auditinspection->createddata->first_name.' '.$auditinspection->createddata->last_name:'NA';
							$arr_history["inspection_created_at"]=date($date_format,$auditinspection->created_at);
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







				/* Followup History Data Start */
				$arr_history_data=array();
				$auditPlanHistory = $modelModel->followupauditplanhistory;
				if(count($auditPlanHistory)>0)
				{
					foreach($auditPlanHistory as $auditPlan)
					{
						$arr_history=array();
						
						$arr_history["id"]=$auditPlan->id;
						$arr_history["application_lead_auditor_name"]=$auditPlan->followupleadauditor->first_name." ".$auditPlan->followupleadauditor->last_name;
						// $arr_history["quotation_manday"]=$auditPlan->quotation_manday;
						$arr_history["actual_manday"]=$auditPlan->followup_actual_manday;
						$arr_history["created_at"]=date($date_format,$auditPlan->created_at);
						$arr_history["created_by_name"]=$auditPlan->created_by?$auditPlan->user->first_name." ".$auditPlan->user->last_name:'';
						
						$arr_history["company_name"]=$modelModel->application->companyname;
						$arr_history["address"]=$modelModel->application->address;
						$arr_history["zipcode"]=$modelModel->application->zipcode;
						$arr_history["city"]=$modelModel->application->city;
						$arr_history["country_name"]=$modelModel->application->countryname;
						$arr_history["state_name"]=$modelModel->application->statename;

						$rejcustomerreview = $auditPlan->customerreviewhistory;
						if($rejcustomerreview !== null)
						{	
							$arr_history["customer_review_created_by_name"] = $rejcustomerreview->reviewer->first_name." ".$rejcustomerreview->reviewer->last_name;
							$arr_history["customer_review_created_at"] = date($date_format,$rejcustomerreview->created_at); 
							$arr_history["customer_review_comment"] = $rejcustomerreview->comment;
						}
						
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
								$inspector_names = '';
								if(count($arr->planinspector)>0)
								{
									$inspectors = [];
									foreach($arr->planinspector as $inspector)
									{
										$inspectors[] = $inspector->user->first_name." ".$inspector->user->last_name;
									}
									$inspector_names = implode(", ",$inspectors);
								}

								$temparr=array();
								$temparr["inspection_id"]=$arr->id;
								$temparr["application_unit_name"]=($arr->applicationunit?$arr->applicationunit->name:'NA');
								$temparr["activity"]=$arr->activity;
								$temparr["inspector"]=$inspector_names;
								//$temparr["inspector"]=$arr->inspector;
								$temparr["date"]=date($date_format,strtotime($arr->date));
								
								$temparr["start_time"]=date('G:i', strtotime($arr->start_time));
								$temparr["end_time"]=date('G:i', strtotime($arr->end_time));
							
								$temparr["person_need_to_be_present"]=$arr->person_need_to_be_present;
								$planarr[]=$temparr;
							}
							//$auditinspectionarr[]=$planarr;											
							$arr_history["inspectionplan"]=$planarr;

							$arr_history["inspection_sent_by"]=$auditinspection->sentdata?$auditinspection->sentdata->first_name.' '.$auditinspection->sentdata->last_name:'NA';
							$arr_history["inspection_sent_at"]=$auditinspection->sent_at!=''?date($date_format,$auditinspection->sent_at):'NA';
							$arr_history["inspection_created_by"]=$auditinspection->createddata?$auditinspection->createddata->first_name.' '.$auditinspection->createddata->last_name:'NA';
							$arr_history["inspection_created_at"]=date($date_format,$auditinspection->created_at);
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
				
				$resultarr["followup_history"]=$arr_history_data;
				
				$mandayarray = ApplicationUnitManday::find()->where(['app_id'=>$modelModel->id])->all();
				$mandayarr=array();
                if (count($mandayarray)>0) {
                    foreach ($mandayarray as $manday) {
						$mandayssarr=array();
						$mandayssarr['id']=$manday->id;
						$mandayssarr['unit_id']=$manday->unit_id;
						$mandayssarr['manday']=$manday->manday;
						$mandayssarr['final_manday_withtrans']=$manday->final_manday_withtrans;
						$mandayssarr['final_manday']=$manday->final_manday;
						$mandayssarr['translator_required']=$manday->translator_required;
						$mandayssarr['adjusted_manday_comment']=$manday->adjusted_manday_comment;
						$mandayssarr['manday_cost']=$manday->manday_cost;
						$mandayssarr['actual_manday']=$manday->audiplanhistory->actual_manday;
						$mandayarr[]=$mandayssarr;
                    }
                }

				$resultarr["manday"] =$mandayarray;
			}
			return $resultarr;			
		}
	}

	public function actionReviewRiskcategory()
    {
		$riskoptions = AuditReviewerRiskCategory::find()->select('id,name')->where(['status'=>0])->asArray()->all();
		return ['risklist'=>$riskoptions];
	}

	public function actionGetReviewerGroups(){
		//$app_id = 270; // app_id
		 //roleid => usersroleid
		  // Loggedin UserId
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$data = Yii::$app->request->post();

		$userData = Yii::$app->userdata->getData();
		//return $userData;
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters = $userData['is_headquarters'];
		$role_chkid = $userData['role_chkid'];
		
		if(isset($data['type']) && isset($data['user_id']) && $data['user_id']!='' && $data['type']=='chage_reviewer')
		{
			$user_id = $data['user_id'];
			
			
			$connection = Yii::$app->getDb();
			$query = 'SELECT rule.role_id as id FROM `tbl_user_role` AS userrole INNER JOIN `tbl_rule` AS rule ON  userrole.role_id=rule.role_id AND rule.privilege="audit_review"  AND userrole.user_id = '.$user_id.'';
			$command = $connection->createCommand($query);
			$reviewers = $command->queryOne();
			if($reviewers!==false)
			{
				$role_id = $reviewers['id'];
			}
			
		}
		else
		{
			$user_id = $userid;
			$role_id = $role_chkid;
		}
		
		$app_id = isset($data['app_id'])?$data['app_id']:0;

		$franchise_id = '';
		$franchiseCondition = '';
		$application = Application::find()->where(['id'=>$app_id])->one();
		if($application !== null){
			$franchise_id = $application->franchise_id;
			if($franchise_id !=''){
				//$franchiseCondition = ' AND user_role.franchise_id= '.$franchise_id;
			}
		}


		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();

		$sectorgroups = [];

		$audit_id = isset($data['audit_id'])?$data['audit_id']:'';
		$AuditModel = Audit::find()->where(['id'=>$audit_id])->one();
		if($AuditModel !== null){
			if($AuditModel->audit_type == 2){
				$auditplanmodel = $AuditModel->auditplan;
				$auditplanunit = $auditplanmodel->auditplanunit;
				if(count($auditplanunit)>0){
					foreach($auditplanunit as $auditplanunitobj){
						$tempsectorgroups = Yii::$app->globalfuns->getUnannouncedBusinessSectorGroups($audit_id,$auditplanunitobj->unit_id);
						$sectorgroups = $sectorgroups + $tempsectorgroups;
					}					
				}
				
			}else{
				$ApplicationUnit = ApplicationUnit::find()->where(['app_id'=>$app_id])->all();
				if(count($ApplicationUnit)>0){
					foreach($ApplicationUnit as $unit){
						$unitbusinesssectorgroup = $unit->unitbusinesssectorgroup;
						if(count($unitbusinesssectorgroup)>0){
							foreach($unitbusinesssectorgroup as $group){
								$sectorgroups[] = $group->business_sector_group_id;
							}
						}
					}
				}
			}
		}

		
		
		$userbsector = [];
		$sectorgroups = array_unique($sectorgroups);
		if(count($sectorgroups)>0){
			//$UserRoleTechnicalExpertBsCode = UserRoleTechnicalExpertBsCode::find()->alias('t')->innerJoinWith('expertbs as expertbs')->where(['expertbs.user_ids'=>$user_id,'expertbs.role_id'=>$role_id,'t.status'=>2])->all();
			//$UserRoleTechnicalExpertBsCode = UserRoleBusinessGroup::find()->alias('t')->innerJoinWith('rolegroupcode as rolegroupcode')->where(['t.user_id'=>$user_id,'t.role_id'=>$role_id])->all();
			$UserRoleTechnicalExpertBsCode = UserRoleBusinessGroupCode::find()->alias('t')->innerJoinWith('rolebusinessgroup as rolebusinessgroup')->where(['rolebusinessgroup.user_id'=>$user_id,'rolebusinessgroup.role_id'=>$role_id])->all();
			if(count($UserRoleTechnicalExpertBsCode)>0){
				foreach($UserRoleTechnicalExpertBsCode as $bcode){
					$userbsector[] =$bcode->business_sector_group_id;
				}
			}
		}
		$getTeSectors = array_diff($sectorgroups,$userbsector);

		$reviewerValidSectorIds = array_intersect($sectorgroups,$userbsector);
		$reviewerValidSectors = [];
		if(count($reviewerValidSectorIds)>0){
			$BusinessSectorGroup = BusinessSectorGroup::find()->where(['id'=>$reviewerValidSectorIds])->all();
			if(count($BusinessSectorGroup)>0){
				foreach($BusinessSectorGroup as $bsectorobj){
					$reviewerValidSectors[] = $bsectorobj->group_code;
				}
			}
			
		}
		$userListArr = [];
		$userbsectordetails = [];
		$userbsectorcheckdetails = [];

		if(count($getTeSectors)>0){
			$Role = Role::find()->where(['resource_access'=>3])->all();
			$RoleIDs = [];
			if(count($Role)>0){
				foreach($Role as $roledata){
					$RoleIDs[] = $roledata->id;
				}
			}
			//$RoleIds = ArrayHelper::map($Role, 'id');
			$RoleIDs = implode(',',$RoleIDs);
			$getTeSectorsIds = implode(',',$getTeSectors);
			//$UserRoleTechnicalExpertBsCode = UserRoleTechnicalExpertBsCode::find()->alias('t')->innerJoinWith('expertbs as expertbs')->where(['t.business_sector_group_id'=>$getTeSectors, 'expertbs.role_id'=>$RoleIDs,'t.status'=>2])->all();
			
			

			$chkAlreadyAdded = [];
			if(count($getTeSectors)>0){
				foreach($getTeSectors as $bcodeid){
					// AND user_role.franchise_id = ".$franchise_id." 
					$command = $connection->createCommand("SELECT  mastergroup.group_code as group_code, group_concat(distinct usergroup.user_id) as userids,usercode.`business_sector_group_id` as groupcodeids
					FROM `tbl_user_role_technical_expert_business_group_code` as usercode 
					inner join `tbl_user_role_technical_expert_business_group` as usergroup on usergroup.id = usercode.`user_role_technical_expert_bs_id` 
					and usercode.status =2 and usergroup.role_id in (".$RoleIDs.") and usercode.business_sector_group_id = ".$bcodeid." 
					inner join tbl_user_role as user_role on user_role.user_id = usergroup.user_id AND user_role.role_id=usergroup.role_id
					AND user_role.franchise_id = ".$franchise_id." 
					inner join tbl_business_sector_group as mastergroup  on mastergroup.id = usercode.business_sector_group_id 
						WHERE  1=1  group by usercode.`business_sector_group_id` ");
					$result = $command->queryAll();
					if(count($result)>0){
						foreach($result as $bcode){
							$useridsArr = explode(',',$bcode['userids']);
							$userList = User::find()->where(['id'=> $useridsArr,'status'=>0 ])->all();
							$display_name = [];
							if(count($userList)>0){
								foreach($userList as $userobj){
									$display_name[] = $userobj->first_name.' '.$userobj->last_name;
									if(!in_array($userobj->id,$chkAlreadyAdded)){

										$chkAlreadyAdded[] = $userobj->id;
										$userListArr[] = ['id'=>$userobj->id,'displayname'=>$userobj->first_name.' '.$userobj->last_name];
									}
									
								}
							}
							$userbsectordetails[$bcode['groupcodeids']] = [
																	'userids'=> $useridsArr,
																	'groupcode' => $bcode['group_code'],
																	'usernames' => $display_name,
																];  //$bcode['groupcodeids'];
							//$userbsector[userids] =$bcode['userids'];

							$userbsectorcheckdetails[$bcode['groupcodeids']] = $useridsArr; 
						}
					}else{
						$BusinessSectorGroup = BusinessSectorGroup::find()->where(['id'=>$bcodeid])->one();
						$userbsectordetails[$bcodeid] = [
														'userids'=> [],
														'groupcode' => $BusinessSectorGroup->group_code,
														'usernames' => [],
													];
						$userbsectorcheckdetails[$bcodeid] = []; 
					}
				}
				
			}
				
			/*if(count($UserRoleTechnicalExpertBsCode)>0){
				foreach($UserRoleTechnicalExpertBsCode as $bcode){
					$userbsector[] =$bcode->business_sector_group_id;
				}
			}*/
		}
		//$getTeSectors = array_diff($sectorgroups,$userbsector);
		//$reviewerValidSectors = ['A1-GO'];
		return ['status' => 1, 'data' => ['reviewerValidSectors'=>$reviewerValidSectors, 'userListArr'=>$userListArr, 'userbsectorcheckdetails'=>$userbsectorcheckdetails, 'userbsector'=>$userbsector,'sectorgroups'=>$sectorgroups,'getTeSectors'=>$getTeSectors,'userbsectordetails'=>$userbsectordetails]];
	}

	public function actionAddassignreviewer(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];

		$teerror = 1;
		if ($data) 
		{
			if(!Yii::$app->userrole->hasRights(array('audit_review')))
			{				
				return $responsedata;
			}
			
			$planModel = new AuditPlan();
			$auditPlanModel = AuditPlan::find()->where(['id' => $data['audit_plan_id'],'status'=>$planModel->arrEnumStatus['waiting_for_review']])->one();
			if($auditPlanModel===null)
			{
				return $responsedata;				
			}
				
			
			$userbsectorcheckdetails = $data['userbsectorcheckdetails'];
			$technicalexpert_ids = isset($data['technicalexpert_ids'])?$data['technicalexpert_ids']:[];
			
			$errorBcode = [];
			if(count($userbsectorcheckdetails)>0){
				foreach($userbsectorcheckdetails as $bcodeid => $chkdetail){
					$error = 0;
					if(count($chkdetail)>0){
						$addedteid = array_intersect($technicalexpert_ids,$chkdetail);
						if(count($addedteid)<=0){
							$error = 1;
						}
					}else{
						$error = 1;
					}
					if($error ==1){
						$bsecname = BusinessSectorGroup::find()->where(['id'=>$bcodeid])->one();
						if($bsecname !== null){
							$errorBcode[] = $bsecname->group_code;
						}
						
					}
					//foreach($technicalexpert_ids as $teid){
					//if(!in_array($teid,$userbsectorcheckdetails)){
						
					//}
				}
			}
			$teerror = 0;
			$reviewersuccess = '';
			$reviewererror = '';
			if(count($errorBcode)>0){
				$teerror = 1;
				$reviewererror = 'Please add TE for '.implode(', ', $errorBcode);
			}else{
				$dataadd = ['audit_plan_id'=>$data['audit_plan_id'], 'reviewer_id'=>$userid, 'technicalexpert_ids'=>$technicalexpert_ids];

				$addresponsedata = $this->assignReviewer($dataadd);
				if($addresponsedata['status'] == 1){
					$reviewersuccess = $addresponsedata['message'];
				}else{
					$reviewererror = $addresponsedata['message'];
				}				
			}
		}
		
		return ['teerror'=>$teerror,'errorBcode'=>$errorBcode,'reviewer_id'=>$userid,'reviewersuccess'=>$reviewersuccess,'reviewererror'=>$reviewererror];
	}

	public function actionAssign()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again later');
        if (Yii::$app->request->post()) 
		{
			$row = AuditPlanReviewer::find()->where(['audit_plan_id'=>$data['audit_plan_id'],'reviewer_status'=>1])->one();
			if($row!==null)
			{
				$row->reviewer_status = $row->arrEnumStatus['old'];
				$row->updated_by = $userData['userid'];
				$row->save();
			}

			$reviewermodel=new AuditPlanReviewer();
			$reviewermodel->audit_plan_id = isset($data['audit_plan_id'])?$data['audit_plan_id']:"";
			$reviewermodel->reviewer_id = isset($data['user_id'])?$data['user_id']:"";
			$reviewermodel->reviewer_status = $reviewermodel->arrEnumStatus['current'];
			$reviewermodel->created_by = $userData['userid'];
			$reviewermodel->save();

			$responsedata=array('status'=>1,'message'=>'Reviewer has been changed successfully');
		}
		return $this->asJson($responsedata);
	}

	
	public function actionCommonview()
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

				$ModelAPUExecutionChecklist = new AuditPlanUnitExecutionChecklist();
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
					
					
					$resultarr["company_name"]=$modelModel->application->companyname;
					$resultarr["address"]=$modelModel->application->address;
					$resultarr["zipcode"]=$modelModel->application->zipcode;
					$resultarr["city"]=$modelModel->application->city;
					$resultarr["country_name"]=$modelModel->application->countryname;
					$resultarr["state_name"]=$modelModel->application->statename;
					
					$resultarr["app_id"]=$modelModel->app_id;
					$resultarr["offer_id"]=$modelModel->offer_id;
					$resultarr["invoice_id"]=$modelModel->invoice_id;


					$auditplanUnit=$model->auditplanunit;
					if(count($auditplanUnit)>0)
					{
						$showCertificateGenerate = 1;
						$showSubmitFollowupAudit=0;








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

							if($user_type== Yii::$app->params['user_type']['user']  && in_array('audit_execution',$rules) && $modelModel->auditplan->application_lead_auditor !== $userid && !in_array('audit_review',$rules)){
								//$model = $model->andWhere('unit_auditor.user_id='.$userid);
								//$unit->
								if( !in_array($userid,$chkAuditorIds)){
									continue; 
								}
							}


							$auditexe = AuditPlanUnitExecution::find()->where(['audit_plan_unit_id'=>$unit->id])->all();
							$auditsubtopiccount = count($auditexe);
							$subtopicArr = [];
							

							/*
							$unitsubtopics = $this->getCurrentSubtopic($unit->unit_id,$unit->id);

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
							*/
							

							
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


						$exechecklist = new AuditPlanUnitExecutionChecklist();
						foreach($exechecklist->arrStatus as $statuskey => $statusvalue){
							$arrFollowupChecklistStatusCnt[$statuskey] = 0;
							$arrChecklistStatusCnt[$statuskey] = 0;
						}
						
						
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


						/*
						To get Followup Total Starts
						*/						
						$commandfollow = $connection->createCommand("SELECT COUNT(checklist.id) as chkcnt,checklist.status as status FROM 
										tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.unit_id IN (".implode(',',$unitIds).") AND checklist.finding_type=2 AND checklist.answer=2 GROUP BY checklist.status"); // AND checklist.status = 1
						$resultfollow = $commandfollow->queryAll();
						$totalFollowupChkFinding =0;
						if(count($resultfollow )>0){
							foreach($resultfollow  as $statuschklistf){
								$arrFollowupChecklistStatusCnt[$statuschklistf['status']] = $statuschklistf['chkcnt'];
								$totalFollowupChkFinding += $statuschklistf['chkcnt'];
							}
						}
						/*
						To get Followup Total Ends
						*/




						if($model->status == $model->arrEnumStatus['remediation_in_progress']){
							

							
							//$totchk = $arrChecklistStatusCnt[1] + $arrChecklistStatusCnt[3] + $arrChecklistStatusCnt[4] + $arrChecklistStatusCnt[5];
							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['in_progress']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['waiting_for_approval']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['reviewer_change_request']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']];

							if($totalChkFinding ==  $totchk){
								$showSubmitRemediationForAuditor = 1;
							}
							
						}

						if($model->status == $model->arrEnumStatus['auditor_review_in_progress']){
							 

							//$totchk = $arrChecklistStatusCnt[3] + $arrChecklistStatusCnt[5];
							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['waiting_for_approval']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']];

							/*if($arrChecklistStatusCnt[1]<=0 && $arrChecklistStatusCnt[2]<=0 && $totalChkFinding ==  $totchk){
								$showSubmitRemediationForReviewer = 1;
							}
							$totchk = $arrChecklistStatusCnt[3] + $arrChecklistStatusCnt[5] + $arrFollowupChecklistStatusCnt[1];
							if($totalChkFinding ==  $totchk){
								$showSubmitRemediationForReviewer = 1;
							}
							*/
							if($arrChecklistStatusCnt[$exechecklist->arrEnumStatus['in_progress']]<=0 && $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['auditor_change_request']]<=0 && $totalChkFinding ==  $totchk){
								$showSubmitRemediationForReviewer = 1;
							}


							//$totalChkFinding => total no
							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['waiting_for_approval']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']] + $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['in_progress']];
							if($totalChkFinding ==  $totchk){
								$showSubmitRemediationForReviewer = 1;
							}
						}

						if($model->status == $model->arrEnumStatus['reviewer_review_in_progress']){
							
							/*
							$totchk = $arrChecklistStatusCnt[4] + $arrChecklistStatusCnt[5];

							if($arrChecklistStatusCnt[4] > 0 && $totalChkFinding ==  $totchk){
								$showSendBackRemediationToAuditor = 1;
							}
							*/
							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['reviewer_change_request']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']];

							if($arrChecklistStatusCnt[$exechecklist->arrEnumStatus['reviewer_change_request']] > 0 && $totalChkFinding ==  $totchk){
								$showSendBackRemediationToAuditor = 1;
							}


							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['reviewer_change_request']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']] + $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['in_progress']];
							if($arrChecklistStatusCnt[$exechecklist->arrEnumStatus['reviewer_change_request']] > 0 && $totalChkFinding ==  $totchk){
								$showSendBackRemediationToAuditor = 1;
							}
						}

						if($model->status == $model->arrEnumStatus['auditor_review_in_progress']){
							/*
							$totchk = $arrChecklistStatusCnt[2] + $arrChecklistStatusCnt[3] + $arrChecklistStatusCnt[5];
							if($arrChecklistStatusCnt[2] > 0 && $totalChkFinding ==  $totchk){
								$showSendBackRemediationToCustomer = 1;
							}
							*/
							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['auditor_change_request']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['waiting_for_approval']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']];
							if($arrChecklistStatusCnt[$exechecklist->arrEnumStatus['auditor_change_request']] > 0 && $totalChkFinding ==  $totchk){
								$showSendBackRemediationToCustomer = 1;
							}


							$totchk = $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['auditor_change_request']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['waiting_for_approval']] + $arrChecklistStatusCnt[$exechecklist->arrEnumStatus['settled']]  + $arrFollowupChecklistStatusCnt[$exechecklist->arrEnumStatus['in_progress']];
							if($arrChecklistStatusCnt[$exechecklist->arrEnumStatus['auditor_change_request']] > 0 && $totalChkFinding ==  $totchk){
								$showSendBackRemediationToCustomer = 1;
							}

						}








						$commandfollow = $connection->createCommand("SELECT COUNT(checklist.id) as chkcnt,checklist.status as status FROM 
										tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.unit_id IN (".implode(',',$unitIds).") AND checklist.finding_type=2 AND checklist.answer=2 GROUP BY checklist.status"); // AND checklist.status = 1
						$resultfollow = $commandfollow->queryAll();
						$totalFollowupChkFinding =0;
						if(count($resultfollow )>0){
							foreach($resultfollow  as $statuschklistf){
								$arrFollowupChecklistStatusCnt[$statuschklistf['status']] = $statuschklistf['chkcnt'];
								$totalFollowupChkFinding += $statuschklistf['chkcnt'];
							}
						}
						$command = $connection->createCommand("SELECT COUNT(checklist.id) as chkcnt,checklist.status as status FROM 
										tbl_audit_plan_unit_execution_checklist AS checklist  
										WHERE checklist.unit_id IN (".implode(',',$unitIds).") AND checklist.finding_type=1 AND checklist.answer=2 GROUP BY checklist.status"); // AND checklist.status = 1
						$result = $command->queryAll();
						$totalChkFinding =0;
						
						if(count($result )>0){
							foreach($result  as $statuschklist){
								$arrChecklistStatusCnt[$statuschklist['status']] = $statuschklist['chkcnt'];
								$totalChkFinding += $statuschklist['chkcnt'];
							}
						}
						if($arrFollowupChecklistStatusCnt[$ModelAPUExecutionChecklist->arrEnumStatus['in_progress']]==$totalFollowupChkFinding && $arrChecklistStatusCnt[$ModelAPUExecutionChecklist->arrEnumStatus['settled']]==$totalChkFinding){
							$showSubmitFollowupAudit=1;
						}

						$resultarr["showSubmitFollowupAudit"]=$showSubmitFollowupAudit;


						

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
							$inspector_names = '';
							if(count($arr->auditplaninspectionplaninspector)>0)
							{
								$inspectors = [];
								foreach($arr->auditplaninspectionplaninspector as $inspector)
								{
									$inspectors[] = $inspector->user->first_name." ".$inspector->user->last_name;
								}
								$inspector_names = implode(", ",$inspectors);
							}

							$temparr=array();
							$temparr["inspection_id"]=$arr->id;
							$temparr["application_unit_name"]=($arr->applicationunit!==null ? $arr->applicationunit->name : 'NA');
							$temparr["activity"]=$arr->activity;
							$temparr["inspector"]=$inspector_names;
							//$temparr["inspector"]=$arr->inspector;
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
				
			}
			return $resultarr;			
		}
	}


	public function actionSubmitforauditfollowup(){
		

		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        $data = Yii::$app->request->post();
		
		if ($data) 
		{
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];

			$auditmodel =new Audit();
			$auditplanmodel =new AuditPlan();
			$auditplanchecklistmodel =new AuditPlanUnitExecutionChecklist();
			$auditplanunitmodel =new AuditPlanUnit();
			$connection = Yii::$app->getDb();


			$audit_id = $data['audit_id'];
			$audit_plan_id = $data['audit_plan_id'];
			 

			$audit = Audit::find()->where(['id'=>$audit_id])->one();
			$audit->status =$audit->arrEnumStatus['followup_open'];
			$audit->followup_status = 1;

			//$audit->save();

			$auditplan = AuditPlan::find()->where(['id'=>$audit_plan_id])->one();
			$auditplan->status =$auditplan->arrEnumStatus['followup_open'];
			

			if($auditplan->save() && $audit->save())
			{
				
				$AuditPlanUnit = AuditPlanUnit::find()->where(['audit_plan_id'=>$auditplan->id])->all();
				if(count($AuditPlanUnit)>0){
					foreach($AuditPlanUnit as $planunit){
						 
						$command = $connection->createCommand("SELECT exe.`sub_topic_id`  as sub_topic_id FROM tbl_audit_plan_unit_execution as exe INNER JOIN tbl_audit_plan_unit_execution_checklist checklist on 
								checklist.audit_plan_unit_execution_id=exe.id where exe.audit_plan_unit_id =".$planunit->id." and checklist.answer=2 and checklist.finding_type='2' and checklist.status='".$auditplanchecklistmodel->arrEnumStatus['in_progress']."' group by exe.`sub_topic_id` ");
						$result = $command->queryAll();
						if(count($result)>0){
							$planunit->status=  $auditplanunitmodel->arrEnumStatus['followup_open'];
							$planunit->followup_status = 1;
							$planunit->save();

							$auditor_user_id = null;
							$AuditPlanUnitAuditorCnt = AuditPlanUnitAuditor::find()->where(['audit_plan_unit_id'=>$planunit->id,'audit_type'=>2])->all();
							$TotalAuditorCount = count($AuditPlanUnitAuditorCnt);
							if($TotalAuditorCount==1){
								foreach($AuditPlanUnitAuditorCnt as $auditordata){
									$auditor_user_id = $auditordata->user_id;
								}
							}

							
							foreach($result as $subtopic){  
								$AuditPlanUnitExecutionFollowup = new AuditPlanUnitExecutionFollowup();
								$AuditPlanUnitExecutionFollowup->audit_plan_unit_id = $planunit->id;
								$AuditPlanUnitExecutionFollowup->sub_topic_id =  $subtopic['sub_topic_id'];
								$SubTopicName = SubTopic::find()->where(['id'=>$subtopic['sub_topic_id']])->one();
								if($SubTopicName !== null){
									$AuditPlanUnitExecutionFollowup->sub_topic_name = $SubTopicName->name;
								}
								if($TotalAuditorCount==1){
									$AuditPlanUnitExecutionFollowup->executed_by = $auditor_user_id;
								}
								$AuditPlanUnitExecutionFollowup->status = 0;
								$AuditPlanUnitExecutionFollowup->created_by = $userid;
								$AuditPlanUnitExecutionFollowup->save();



								/*
								$commandchecklist = $connection->createCommand("SELECT checklist.`id`  as checklist_id,rem.id as remediation_id FROM tbl_audit_plan_unit_execution as exe INNER JOIN tbl_audit_plan_unit_execution_checklist checklist on 
								checklist.audit_plan_unit_execution_id=exe.id 
								INNER JOIN tbl_audit_plan_unit_execution_checklist_remediation rem on rem.audit_plan_unit_execution_checklist_id  = checklist.id 
								where exe.audit_plan_unit_id =".$planunit->id." and checklist.answer=2 and checklist.finding_type='2' and checklist.status='".$auditplanchecklistmodel->arrEnumStatus['in_progress']."' AND exe.sub_topic_id='".$subtopic['sub_topic_id']."' ");
								$resultchecklist = $commandchecklist->queryAll();
								foreach($resultchecklist as $rchecklist){
 
									$APUFollowupRemediationReview = new AuditPlanUnitFollowupRemediationReview();
									$APUFollowupRemediationReview->audit_plan_unit_execution_followup_id = $AuditPlanUnitExecutionFollowup->id;
									$APUFollowupRemediationReview->audit_plan_unit_execution_checklist_id = $rchecklist['checklist_id'];
									$APUFollowupRemediationReview->checklist_remediation_id = $rchecklist['remediation_id'];
									$APUFollowupRemediationReview->status = 0;
									$APUFollowupRemediationReview->save();
								}
								*/
								

							}
							
							//tbl_audit_plan_unit_execution_followup


							
						}
					}
				}

				$responsedata=array('status'=>1,'message'=>'Followup audit submitted successfully');

			}
		}
		
		return $this->asJson($responsedata);
	}


	public function assignReviewer($data)
    {
		//if()
		


		$auditreviewer=new AuditPlanReviewer();
		//$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
       // $data = Yii::$app->request->post();
		
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
					if(isset($data['technicalexpert_ids']) && count($data['technicalexpert_ids'])>0){
						foreach($data['technicalexpert_ids'] as $teid){
							$AuditPlanReviewerTe = new AuditPlanReviewerTe();
							$AuditPlanReviewerTe->audit_plan_id = $data['audit_plan_id'];
							$AuditPlanReviewerTe->audit_plan_reviewer_id = $auditreviewer->id;
							$AuditPlanReviewerTe->technical_expert_id = $teid;
							$AuditPlanReviewerTe->save();
						}
					}
					

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
		return $responsedata;
	}

	public function actionAssignReviewer()
    {
		$auditreviewer=new AuditPlanReviewer();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        $data = Yii::$app->request->post();
		
		if ($data) 
		{
			if(!Yii::$app->userrole->hasRights(array('audit_review')))
			{
				return $responsedata;
			}
			
			$planModel = new AuditPlan();
			$auditPlanModel = AuditPlan::find()->where(['id' => $data['audit_plan_id'],'status'=>$planModel->arrEnumStatus['waiting_for_review']])->one();
			if($auditPlanModel===null)
			{
				return $responsedata;				
			}
		
			return $this->assignReviewer($data);
			/*
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
			*/
		}
		return $this->asJson($responsedata);
	}
	
	public function actionViewAuditPlan(){


		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		

		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');

		$data = yii::$app->request->post();
        if ($data) 
		{
			if(!$this->canCreateAuditPlan($data['audit_id'])){
				return false;
			}
			//$data = yii::$app->request->post();

			//$appdata['auditplanunits'][0] = [];
			$appdata = [];
			$appunits = ApplicationUnit::find()->where(['app_id'=>$data['app_id']])->all();
			$audit_id = '';
			if(isset($data['audit_id']) && $data['audit_id']!=''){
				$audit_id = $data['audit_id'];

				
				$auditplan = AuditPlan::find()->where(['audit_id'=>$audit_id])->one();
				if($auditplan!==null){
					$appdata['application_lead_auditor'] = $auditplan->application_lead_auditor;
					$appdata['audit_plan_id'] =  $auditplan->id;
				}
			}
			

			$unitssectorgroupIds = [];
			foreach($appunits as $unit)
			{
				$standardsarr = [];
				$unitdata = [];
				$unit_standard = [];
				foreach($unit->unitappstandard as $standard)
				{
					//if($unit->application->audit_type != $unit->application->arrEnumAuditType['unit_addition'] )
					//{
						/*
												
						$ReductionStandard = ReductionStandard::find()->where(['code'=>$standard->standard->code ])->one();
						if($ReductionStandard!==null){
							$ApplicationUnitCertifiedStandard = ApplicationUnitCertifiedStandard::find()->where(['standard_id'=>$ReductionStandard->id ,'unit_id'=>$unit->id ])->one();
							if($ApplicationUnitCertifiedStandard !== null){
								//echo '1';
								continue;
							}
						}
						*/
					//}
					
					$standardsarr['id'] = $standard->standard_id;
					$standardsarr['name'] = $standard->standard->code;
					$unitdata['standards'][] = $standardsarr;
					$unit_standard[] = $standard->standard_id;
				}
				//print_r($unit_standard);
				
				/*
				// --------- Already Certified Same Standard Unit Code Start Here ---------
				if(count($unit_standard)<=0){
					continue;
				}
				// --------- Already Certified Same Standard Unit Code End Here ----------
				*/
				
				if($unit->unitmanday->adjusted_manday=='0.00' || $unit->unitmanday->adjusted_manday==0)
				{
					continue;
				}
				
				$unitdata['name']=$unit->name;
				$unitdata['id']=$unit->id;
				$unitdata['address']=$unit->address;
				$unitdata['zipcode']=$unit->zipcode ;
				$unitdata['city']=$unit->city ;
				$unitdata['no_of_employees']=$unit->no_of_employees;
				$unitdata['quotation_manday']=$unit->unitmanday->adjusted_manday;//$unit->unitmanday->final_manday;
				//echo $unit->id.'=='; die;
				//echo count($unit->unitstandard); die;
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
				
				

				
				

				if($audit_id !='' && $auditplan!==null){
					$planunit = AuditPlanUnit::find()->where(['audit_plan_id'=>$auditplan->id,'unit_id'=>$unit->id])->one();
					if($planunit !==null){
						$unitdata['unit_lead_auditor'] = $planunit->unit_lead_auditor;
						$unitdata['technical_expert'] = $planunit->technical_expert;
						$unitdata['translator'] = $planunit->translator;
						$unitdata['observer'] = $planunit->observer;
						$unitdata['actual_manday'] = $planunit->actual_manday;
						$unitdata['observer'] = $planunit->observer;
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

	public function actionSaveSubtopic()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
	
		if ($data) 
		{
			if($data['subtopicType'] =='followup_assignSubtopic'){
				if(!Yii::$app->userrole->isAuditor($data['audit_plan_unit_id'],2)){
					return $responsedata;
				}

				$AuditPlanUnitExecutionModel = new AuditPlanUnitExecutionFollowup();
				$userid = $userData['userid'];
				
				$auditexeexisting = AuditPlanUnitExecutionFollowup::find()->where(['status'=>$AuditPlanUnitExecutionModel->arrEnumStatus['open'], 'audit_plan_unit_id'=>$data['audit_plan_unit_id'],'executed_by'=> $userid])->all();
				if(count($auditexeexisting)>0){
					foreach($auditexeexisting as $existing){
						$existing->executed_by = NULL;
						$existing->save();
					}
				}
	
				$auditexe = AuditPlanUnitExecutionFollowup::find()->where(['audit_plan_unit_id'=>$data['audit_plan_unit_id']])->andWhere(['sub_topic_id'=>$data['subtopic_id']])->all();
				if(count($auditexe)>0)
				{
					foreach($auditexe as $audit)
					{
						$audit->executed_by = $userid;
						$audit->executed_date = time();
						$audit->save();
					}
					$responsedata=array('status'=>1,'message'=>'Assigned Successfully');
				}
			}else{

				if(!Yii::$app->userrole->isAuditor($data['audit_plan_unit_id'],1)){
					return $responsedata;
				}

				$AuditPlanUnitExecutionModel = new AuditPlanUnitExecution();
				$userid = $userData['userid'];
				//->andWhere(['sub_topic_id'=>$data['subtopic_id']])
				$auditexeexisting = AuditPlanUnitExecution::find()->where(['status'=>$AuditPlanUnitExecutionModel->arrEnumStatus['open'], 'audit_plan_unit_id'=>$data['audit_plan_unit_id'],'executed_by'=> $userid])->all();
				if(count($auditexeexisting)>0){
					foreach($auditexeexisting as $existing){
						$existing->executed_by = NULL;
						$existing->save();
					}
				}
	
				$auditexe = AuditPlanUnitExecution::find()->where(['audit_plan_unit_id'=>$data['audit_plan_unit_id']])->andWhere(['sub_topic_id'=>$data['subtopic_id']])->all();
				if(count($auditexe)>0)
				{
					foreach($auditexe as $audit)
					{
						$audit->executed_by = $userid;
						$audit->executed_date = time();
						$audit->save();
					}
					
				}
				$responsedata=array('status'=>1,'message'=>'Assigned Successfully');
			}
			
		}
		return $responsedata;
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
			$this->RemoveTempAuditors($data['audit_id'],$data['app_id'],$data['unitid']);
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

	private function getAuditorData($auditorDates,$unit_id='',$app_id='',$unitstandards=[],$applicationunitid='',$sector_group_ids=[], $audit_id=''){
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
		///$audit_id = $data['audit_id'];
		if($audit_id!=''){
			$AuditPlanModel = AuditPlan::find()->where(['audit_id'=>$audit_id])->one();
			if($AuditPlanModel !== null){
				$audit_plan_id = $AuditPlanModel->id;
				$auditplancondition = " AND auditunit.audit_plan_id != ".$audit_plan_id;
			}
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
		if($applicationunitid!=''){
			$businessector = ApplicationUnitBusinessSector::find()->where(['unit_id'=>$applicationunitid])->all();
			if(count($businessector)>0){
				foreach($businessector as $sector){
					
					$arrbusinessector[] = $sector->business_sector_id;
				}
				//print_r($arrbusinessector); 
			}
		}
		
		//$sector_group_ids

		$command = $connection->createCommand("select auditor.user_id as user_id,GROUP_CONCAT(auditunit.technical_expert) as technical_experts,GROUP_CONCAT(auditunit.translator) as translators from `tbl_audit_plan_unit_auditor_date` as auditordate
			inner join `tbl_audit_plan_unit_auditor` as auditor on 
			auditordate.audit_plan_unit_auditor_id= auditor.id and auditordate.date and auditordate.date in('".implode("','",$auditorDates)."') 
			inner join `tbl_audit_plan_unit` as auditunit on auditunit.id= auditor.audit_plan_unit_id 
			where 1=1 ".$condition."  ".$appcondition."    
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
		INNER JOIN `tbl_user_standard` AS usrstd on usrstd.user_id = user.id 
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

		/*$franchise_id = '';
		$franchiseCondition = '';
		$application = Application::find()->where(['id'=>$app_id])->one();
		if($application !== null){
			$franchise_id = $application->franchise_id;
			if($franchise_id !=''){
				$franchiseCondition = ' AND user_role.franchise_id= '.$franchise_id;
			}
		}
		*/
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
		
		$userlistIds = array_unique($userlistIds);
		return $userlistIds;
	}
	

	public function actionSendtoleadauditor()
	{					
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			$planunit = AuditPlanUnit::find()->where(['id'=>$data['audit_plan_unit_id']])->one();
			if($planunit !== null)
			{
				if(!Yii::$app->userrole->isUnitLeadAuditor($data['audit_plan_unit_id'],1)){
					return $responsedata;
				}
				
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

	public function actionFollowupsendtoleadauditor(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			$planunit = AuditPlanUnit::find()->where(['id'=>$data['audit_plan_unit_id']])->one();
			if($planunit !== null){
				
				
				
				$planunitexecutionstatus = new AuditPlanUnitExecutionFollowup();
				$waiting_for_unit_lead_auditor_approvalstatus = $planunitexecutionstatus->arrEnumStatus['waiting_for_unit_lead_auditor_approval'];
				$reintiatestatus = $planunitexecutionstatus->arrEnumStatus['reintiate'];
				$planunitexecution = AuditPlanUnitExecutionFollowup::find()->where(['audit_plan_unit_id'=>$data['audit_plan_unit_id']
				,'status'=>[$waiting_for_unit_lead_auditor_approvalstatus,$reintiatestatus] ])->all();
				if(count($planunitexecution)>0){
					foreach($planunitexecution as $unitexecution){
						$unitexecution->status = $planunitexecutionstatus->arrEnumStatus['completed'];
						$unitexecution->save();
					}
				}


				$awaiting_for_lead_auditor_approval_status = $planunit->arrEnumStatus['followup_awaiting_lead_auditor_approval'];
				$awaiting_for_reviewer_approval = $planunit->arrEnumStatus['followup_awaiting_reviewer_approval'];
				$planunit->status = $awaiting_for_lead_auditor_approval_status;
				$planunit->status_change_date = time();
				if($planunit->save()){
					//waiting_for_lead_auditor
					$auditplan = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
					if($auditplan !== null){
						$auditplanunit = AuditPlanUnit::find()->where(['not in','status',[$awaiting_for_lead_auditor_approval_status,$awaiting_for_reviewer_approval]]);
						$auditplanunit = $auditplanunit->andWhere(['audit_plan_id' => $data['audit_plan_id'],'followup_status'=>1])->one();
						if($auditplanunit===null){
							$auditplan->status = $auditplan->arrEnumStatus['followup_waiting_for_lead_auditor'];
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
				if(!Yii::$app->userrole->isAuditProjectLA($model->audit_id)){
					return $responsedata;
				}
				
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


					$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'mail_to_reviewer_from_lead_auditor'])->one();
					if($mailContent !== null)
					{
						$auditmodal= Audit::find()->where(['id'=>$data['audit_id']])->one();
						if($auditmodal !== null)
						{
							$mailmsg=str_replace('{COMPANY-NAME}', $auditmodal->application->companyname, $mailContent['message'] );
							$mailmsg=str_replace('{COMPANY-EMAIL}', $auditmodal->application->emailaddress, $mailmsg );
							$mailmsg=str_replace('{COMPANY-TELEPHONE}', $auditmodal->application->telephone, $mailmsg );
							$mailmsg=str_replace('{CONTACT-NAME}', $auditmodal->application->firstname." ".$auditmodal->application->lastname, $mailmsg );

							$auditplanmodal = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
							if($auditplanmodal !== null)
							{
								$MailLookupModel = new MailLookup();
								$MailLookupModel->to=$auditplanmodal->user->email;								
								$MailLookupModel->subject=$mailContent['subject'];
								$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
								$MailLookupModel->attachment='';
								$MailLookupModel->mail_notification_id='';
								$MailLookupModel->mail_notification_code='';
								$Mailres=$MailLookupModel->sendMail();
							}
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
			$connection = Yii::$app->getDb();
			$auditmodel = new AuditPlan();
			$auditunitmodel = new AuditPlanUnit();

			if(!Yii::$app->userrole->isAuditReviewer($data['audit_plan_id']))
			{
				return $responsedata;
			}
			
			$model = AuditPlan::find()->where(['id' => $data['audit_plan_id'],'status'=>$auditmodel->arrEnumStatus['review_completed']])->one();
			if($model===null)
			{
				return $responsedata;				
			}

			//$model = AuditPlan::find()->where(['id' => $data['audit_plan_id']])->one();
			if ($model !== null)
			{
				//audit_completed
				$model->status = $auditmodel->arrEnumStatus['audit_completed'];
				
				if($model->save())
				{
					$auditplanunit = AuditPlanUnit::find()->where(['audit_plan_id'=>$data['audit_plan_id']])->all();
					if(count($auditplanunit)>0){
						foreach($auditplanunit as $planunit){


							/*
							$command = $connection->createCommand("SELECT * FROM `tbl_audit_plan_unit_execution` as exec inner join `tbl_audit_plan_unit_execution_checklist` as checklist on exec.id = checklist.`audit_plan_unit_id` WHERE audit_plan_unit_id = '".$planunit->id."' and finding_type='2' ");
							$result = $command->queryAll();
							if(count($result)>0){
								
							}
							*/
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
				if($data['actiontype']=='sendaudittoauditor')
				{
					$model->status = $model->arrEnumStatus['auditor_review_in_progress'];

					$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'mail_to_lead_auditor_from_customer'])->one();
					if($mailContent !== null)
					{
						$auditmodal = Audit::find()->where(['id'=>$model->audit_id])->one();
						if($auditmodal !== null)
						{
							$mailmsg=str_replace('{COMPANY-NAME}', $auditmodal->application->companyname, $mailContent['message'] );
							$mailmsg=str_replace('{COMPANY-EMAIL}', $auditmodal->application->emailaddress, $mailmsg );
							$mailmsg=str_replace('{COMPANY-TELEPHONE}', $auditmodal->application->telephone, $mailmsg );
							$mailmsg=str_replace('{CONTACT-NAME}', $auditmodal->application->firstname." ".$auditmodal->application->lastname, $mailmsg );

							$auditplanmodal = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
							if($auditplanmodal !== null)
							{
								$MailLookupModel = new MailLookup();
								$MailLookupModel->to=$auditplanmodal->user->email;								
								$MailLookupModel->subject=$mailContent['subject'];
								$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
								$MailLookupModel->attachment='';
								$MailLookupModel->mail_notification_id='';
								$MailLookupModel->mail_notification_code='';
								$Mailres=$MailLookupModel->sendMail();
							}
						}
						
					}

				}else if($data['actiontype']=='sendbackaudittocustomer'){
					$model->status = $model->arrEnumStatus['remediation_in_progress'];

					$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'mail_to_customer_remediation_needed'])->one();
					if($mailContent !== null)
					{
						$auditmodal = Audit::find()->where(['id'=>$model->audit_id])->one();
						if($auditmodal !== null)
						{
							$mailmsg=str_replace('{COMPANY-NAME}', $auditmodal->application->companyname, $mailContent['message'] );
							$mailmsg=str_replace('{COMPANY-EMAIL}', $auditmodal->application->emailaddress, $mailmsg );
							$mailmsg=str_replace('{COMPANY-TELEPHONE}', $auditmodal->application->telephone, $mailmsg );
							$mailmsg=str_replace('{CONTACT-NAME}', $auditmodal->application->firstname." ".$auditmodal->application->lastname, $mailmsg );

							$customeremail = $auditmodal->application->customer?$auditmodal->application->customer->email:"";
							if($customeremail !== null)
							{
								$MailLookupModel = new MailLookup();
								$MailLookupModel->to=$customeremail;								
								$MailLookupModel->subject=$mailContent['subject'];
								$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
								$MailLookupModel->attachment='';
								$MailLookupModel->mail_notification_id='';
								$MailLookupModel->mail_notification_code='';
								$Mailres=$MailLookupModel->sendMail();
							}
						}
						
					}

				}else if($data['actiontype']=='sendaudittoreviewer'){
					$model->status = $model->arrEnumStatus['reviewer_review_in_progress'];

					$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'mail_to_reviewer_from_lead_auditor_on_remediation'])->one();
					if($mailContent !== null)
					{
						$auditmodal = Audit::find()->where(['id'=>$model->audit_id])->one();
						if($auditmodal !== null)
						{
							$mailmsg=str_replace('{COMPANY-NAME}', $auditmodal->application->companyname, $mailContent['message'] );
							$mailmsg=str_replace('{COMPANY-EMAIL}', $auditmodal->application->emailaddress, $mailmsg );
							$mailmsg=str_replace('{COMPANY-TELEPHONE}', $auditmodal->application->telephone, $mailmsg );
							$mailmsg=str_replace('{CONTACT-NAME}', $auditmodal->application->firstname." ".$auditmodal->application->lastname, $mailmsg );

							$auditplanmodal = AuditPlanReviewer::find()->where(['audit_plan_id'=>$data['audit_plan_id']])->one();
							if($auditplanmodal !== null)
							{
								$MailLookupModel = new MailLookup();
								$MailLookupModel->to=$auditplanmodal->user->email;								
								$MailLookupModel->subject=$mailContent['subject'];
								$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
								$MailLookupModel->attachment='';
								$MailLookupModel->mail_notification_id='';
								$MailLookupModel->mail_notification_code='';
								$Mailres=$MailLookupModel->sendMail();
							}
						}
						
					}

				}else if($data['actiontype']=='sendbackaudittoauditor'){
					$model->status = $model->arrEnumStatus['auditor_review_in_progress'];

					$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'mail_to_lead_auditor_from_reviewer_on_remediation_change_request'])->one();
					if($mailContent !== null)
					{
						$auditmodal = Audit::find()->where(['id'=>$model->audit_id])->one();
						if($auditmodal !== null)
						{
							$mailmsg=str_replace('{COMPANY-NAME}', $auditmodal->application->companyname, $mailContent['message'] );
							$mailmsg=str_replace('{COMPANY-EMAIL}', $auditmodal->application->emailaddress, $mailmsg );
							$mailmsg=str_replace('{COMPANY-TELEPHONE}', $auditmodal->application->telephone, $mailmsg );
							$mailmsg=str_replace('{CONTACT-NAME}', $auditmodal->application->firstname." ".$auditmodal->application->lastname, $mailmsg );

							$auditplanmodal = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
							if($auditplanmodal !== null)
							{
								$MailLookupModel = new MailLookup();
								$MailLookupModel->to=$auditplanmodal->user->email;								
								$MailLookupModel->subject=$mailContent['subject'];
								$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
								$MailLookupModel->attachment='';
								$MailLookupModel->mail_notification_id='';
								$MailLookupModel->mail_notification_code='';
								$Mailres=$MailLookupModel->sendMail();
							}
						}
						
					}


				}else if($data['actiontype']=='followup_sendaudittoreviewer'){
					$model->status = $model->arrEnumStatus['followup_reviewinprogress'];

					$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'mail_to_reviewer_from_lead_auditor_on_followup_remediation'])->one();
					if($mailContent !== null)
					{
						$auditmodal = Audit::find()->where(['id'=>$model->audit_id])->one();
						if($auditmodal !== null)
						{
							$mailmsg=str_replace('{COMPANY-NAME}', $auditmodal->application->companyname, $mailContent['message'] );
							$mailmsg=str_replace('{COMPANY-EMAIL}', $auditmodal->application->emailaddress, $mailmsg );
							$mailmsg=str_replace('{COMPANY-TELEPHONE}', $auditmodal->application->telephone, $mailmsg );
							$mailmsg=str_replace('{CONTACT-NAME}', $auditmodal->application->firstname." ".$auditmodal->application->lastname, $mailmsg );

							$auditplanmodal = AuditPlanReviewer::find()->where(['audit_plan_id'=>$data['audit_plan_id']])->one();
							if($auditplanmodal !== null)
							{
								$MailLookupModel = new MailLookup();
								$MailLookupModel->to=$auditplanmodal->user->email;								
								$MailLookupModel->subject=$mailContent['subject'];
								$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
								$MailLookupModel->attachment='';
								$MailLookupModel->mail_notification_id='';
								$MailLookupModel->mail_notification_code='';
								$Mailres=$MailLookupModel->sendMail();
							}
						}
						
					}

				}else if($data['actiontype']=='sendbackfollowupaudittoleadauditor'){
					$model->status = $model->arrEnumStatus['followup_waiting_for_lead_auditor'];

					$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'mail_to_lead_auditor_from_reviewer_on_followup_remediation_change_request'])->one();
					if($mailContent !== null)
					{
						$auditmodal = Audit::find()->where(['id'=>$model->audit_id])->one();
						if($auditmodal !== null)
						{
							$mailmsg=str_replace('{COMPANY-NAME}', $auditmodal->application->companyname, $mailContent['message'] );
							$mailmsg=str_replace('{COMPANY-EMAIL}', $auditmodal->application->emailaddress, $mailmsg );
							$mailmsg=str_replace('{COMPANY-TELEPHONE}', $auditmodal->application->telephone, $mailmsg );
							$mailmsg=str_replace('{CONTACT-NAME}', $auditmodal->application->firstname." ".$auditmodal->application->lastname, $mailmsg );

							$auditplanmodal = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
							if($auditplanmodal !== null)
							{
								$MailLookupModel = new MailLookup();
								$MailLookupModel->to=$auditplanmodal->user->email;								
								$MailLookupModel->subject=$mailContent['subject'];
								$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
								$MailLookupModel->attachment='';
								$MailLookupModel->mail_notification_id='';
								$MailLookupModel->mail_notification_code='';
								$Mailres=$MailLookupModel->sendMail();
							}
						}
						
					}

				}else if($data['actiontype']=='sendbackfollowupaudittoauditor'){
					$model->status = $model->arrEnumStatus['followup_inprocess'];

					$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'mail_to_lead_auditor_from_reviewer_on_followup_remediation_change_request'])->one();
					if($mailContent !== null)
					{
						$auditmodal = Audit::find()->where(['id'=>$model->audit_id])->one();
						if($auditmodal !== null)
						{
							$mailmsg=str_replace('{COMPANY-NAME}', $auditmodal->application->companyname, $mailContent['message'] );
							$mailmsg=str_replace('{COMPANY-EMAIL}', $auditmodal->application->emailaddress, $mailmsg );
							$mailmsg=str_replace('{COMPANY-TELEPHONE}', $auditmodal->application->telephone, $mailmsg );
							$mailmsg=str_replace('{CONTACT-NAME}', $auditmodal->application->firstname." ".$auditmodal->application->lastname, $mailmsg );

							$auditplanmodal = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
							if($auditplanmodal !== null)
							{
								$MailLookupModel = new MailLookup();
								$MailLookupModel->to=$auditplanmodal->user->email;								
								$MailLookupModel->subject=$mailContent['subject'];
								$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
								$MailLookupModel->attachment='';
								$MailLookupModel->mail_notification_id='';
								$MailLookupModel->mail_notification_code='';
								$Mailres=$MailLookupModel->sendMail();
							}
						}
						
					}
				}
				
				
				if($model->save())
				{
					if($data['actiontype']=='sendbackfollowupaudittoauditor')
					{
						$AuditPlanUnitExecutionChecklistModel = new AuditPlanUnitExecutionChecklist();
						$AuditPlanUnitExecutionModel = new AuditPlanUnitExecution();

						$auditor_change_request = $AuditPlanUnitExecutionChecklistModel->arrEnumStatus['auditor_change_request'];
						if(count($model->followupauditplanunit)>0){
							foreach($model->followupauditplanunit as $followupunit){
								$unitexecution = $followupunit->followupunitexecution;
								//reintiate
								//if()
								if(count($unitexecution)>0){
									foreach($unitexecution as $executiondata){
										$AuditPlanUnitExecution = AuditPlanUnitExecution::find()->where([
											'audit_plan_unit_id'=>$executiondata->audit_plan_unit_id, 'sub_topic_id' => $executiondata->sub_topic_id
										])->one();
										$AuditPlanUnitExecutionChecklist = AuditPlanUnitExecutionChecklist::find()
										->where(['audit_plan_unit_execution_id'=>$AuditPlanUnitExecution->id,'finding_type'=>2,'status'=>$auditor_change_request ])
										->one();
										//echo '1';
										if($AuditPlanUnitExecutionChecklist !== null){
											//echo '22';

											$executiondata->status = $executiondata->arrEnumStatus['reintiate'];
											$executiondata->save();

											$followupunit->status = $followupunit->arrEnumStatus['followup_lead_auditor_reinitiated'];
											$followupunit->save();
										}
									}
								}
								
							}
						}
						
						$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'mail_to_lead_auditor_from_reviewer_on_followup_remediation_change_request'])->one();
						if($mailContent !== null)
						{
							$auditmodal = Audit::find()->where(['id'=>$model->audit_id])->one();
							if($auditmodal !== null)
							{
								$mailmsg=str_replace('{COMPANY-NAME}', $auditmodal->application->companyname, $mailContent['message'] );
								$mailmsg=str_replace('{COMPANY-EMAIL}', $auditmodal->application->emailaddress, $mailmsg );
								$mailmsg=str_replace('{COMPANY-TELEPHONE}', $auditmodal->application->telephone, $mailmsg );
								$mailmsg=str_replace('{CONTACT-NAME}', $auditmodal->application->firstname." ".$auditmodal->application->lastname, $mailmsg );

								$auditplanmodal = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
								if($auditplanmodal !== null)
								{
									$MailLookupModel = new MailLookup();
									$MailLookupModel->to=$auditplanmodal->user->email;									
									$MailLookupModel->subject=$mailContent['subject'];
									$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
									$MailLookupModel->attachment='';
									$MailLookupModel->mail_notification_id='';
									$MailLookupModel->mail_notification_code='';
									$Mailres=$MailLookupModel->sendMail();
								}
							}
							
						}
					}
					if($data['actiontype']=='followup_sendaudittoreviewer'){
						$AuditPlanUnitModel = new AuditPlanUnit();
						
						//followup_awaiting_reviewer_approval
						$AuditPlanUnitList = AuditPlanUnit::find()->where(['audit_plan_id'=>$data['audit_plan_id'], 'status'=> $AuditPlanUnitModel->arrEnumStatus['followup_awaiting_lead_auditor_approval'] ])->all();
						if(count($AuditPlanUnitList)>0){
							foreach($AuditPlanUnitList as $Auditplandata){
								$Auditplandata->status = $AuditPlanUnitModel->arrEnumStatus['followup_awaiting_reviewer_approval'];
								$Auditplandata->save();
							}
						}

						$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'mail_to_reviewer_from_lead_auditor_on_followup_remediation'])->one();
						if($mailContent !== null)
						{
							$auditmodal = Audit::find()->where(['id'=>$model->audit_id])->one();
							if($auditmodal !== null)
							{
								$mailmsg=str_replace('{COMPANY-NAME}', $auditmodal->application->companyname, $mailContent['message'] );
								$mailmsg=str_replace('{COMPANY-EMAIL}', $auditmodal->application->emailaddress, $mailmsg );
								$mailmsg=str_replace('{COMPANY-TELEPHONE}', $auditmodal->application->telephone, $mailmsg );
								$mailmsg=str_replace('{CONTACT-NAME}', $auditmodal->application->firstname." ".$auditmodal->application->lastname, $mailmsg );

								$auditplanmodal = AuditPlanReviewer::find()->where(['audit_plan_id'=>$data['audit_plan_id']])->one();
								if($auditplanmodal !== null)
								{
									$MailLookupModel = new MailLookup();
									$MailLookupModel->to=$auditplanmodal->user->email;									
									$MailLookupModel->subject=$mailContent['subject'];
									$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
									$MailLookupModel->attachment='';
									$MailLookupModel->mail_notification_id='';
									$MailLookupModel->mail_notification_code='';
									$Mailres=$MailLookupModel->sendMail();
								}
							}
							
						}
					}
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

	public function getInspectionPlan($data){
		$auditInsplan=new AuditPlanInspection();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$auditmodel = Audit::find()->where(['id' => $data['audit_id']])->one();
		$auditplan = $auditmodel->auditplan;
		$followup_status = $auditmodel->followup_status;
		$type_of_audit = 'Initial Audit';
		$lead_inspector = '';
		$actual_manday = '';
		if($followup_status == 1){
			$auditInspection = $auditplan->followupauditplaninspection;
			$type_of_audit = 'Followup Audit';
			$lead_inspector = $auditplan->followupuser?$auditplan->followupuser->first_name.' '.$auditplan->followupuser->last_name:'';
			$actual_manday = $auditplan->followup_actual_manday;
		}else{
			$auditInspection = $auditplan->auditplaninspection;
			$lead_inspector = $auditplan->user?$auditplan->user->first_name.' '.$auditplan->user->last_name:'';
			$actual_manday = $auditplan->actual_manday;
		}
			
		$appStandardArr=array();
		$application = $auditmodel->application;
		if($auditmodel->audit_type == 2){
			$unannouncedapplication = $auditmodel->unannouncedaudit;
			$applicationstd = $unannouncedapplication->unannouncedauditstandard;
			
			$html = '';
			if(count($applicationstd)>0)
			{
				foreach($applicationstd as $appstandard)
				{
					$appStandardArr[]=$appstandard->standard->name;
				}
			}
		}else{
			
			$applicationstd = $application->applicationstandard;
			 
			$html = '';
			if(count($applicationstd)>0)
			{
				foreach($applicationstd as $appstandard)
				{
					$appStandardArr[]=$appstandard->standard->name;
				}
			}
		}
			
		
		
		$connection = Yii::$app->getDb();
		$command = $connection->createCommand("SELECT GROUP_CONCAT(DISTINCT `date` ORDER BY `date` ASC SEPARATOR ', ') AS dates FROM `tbl_audit_plan_inspection_plan` WHERE audit_plan_inspection_id=$auditInspection->id GROUP BY audit_plan_inspection_id ");
		$result = $command->queryAll();

		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		if(count($result)>0)
		{
			$dates = $result[0]['dates'];
			$datesArr = explode(', ',$dates);
			$newformatdate = [];
			foreach($datesArr as $insdate)
			{
				$newformatdate[] = date($date_format,strtotime($insdate));
			}
			$dates = implode(' | ',$newformatdate);
		}

		if ($auditInsplan !== null)
		{
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
				/*background-color:#4e85c8;*/
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
			<div style="width:100%;font-size:12px;">
				<div style="text-align: left;width:48%;float:left;display: inline-block;font-size:12px;">
					<img src="'.Yii::$app->params['image_files'].'header-img.jpg" border="0">						
				</div>
				<div style="width:50%;float:right;display:inline-block;font-size:12px;font-family:Arial;">
					<div style="border: 1px solid #4e85c8;padding-left:5px;padding-right:5px;">
						<p><b>GCL INTERNATIONAL LTD</b></p>
						<p>Level 1 | Devonshire House | One Mayfair Place London | W1J 8AJ | United Kingdom</p>
					</div>
					Date: '.date($date_format).'</p>
				</div>
			</div>

			<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
			<tr>
				<td style="text-align:center;font-weight:bold;width:20%;background:#2f4985;color: white;border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="5">INSPECTION PLAN - '.$application->companyname.'</td>
			</tr>';
			 
			$html.='<tr>
					<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Certification Standard:</td>
					<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.implode(', ',$appStandardArr).'</td>
					<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Type of audit:</td>
					<td style="text-align:left;font-weight:bold;width:18%;" valign="middle" class="reportDetailLayoutInner">'.$type_of_audit.'</td>
				</tr>
				<tr>
					<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Inspection date(s):</td>
					<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="4">'.$dates.'</td>
					
				</tr>
				<tr>
					<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Lead Inspector:</td>
					<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.$lead_inspector.'</td>
					<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Inspection man-day:</td>
					<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$actual_manday.'</td>
				</tr>';
				 
			$html.='</table>

				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
				<tr>
				    <td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">S.No</td>
					<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Location</td>
					<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Activity</td>
					<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Inspector</td>		
					<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Date</td>	
					<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Start Time</td>
					<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">End Time</td>
					<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Persons need to be present</td>				  
				</tr>';
				$insCint=1;
			$model = AuditPlanInspectionPlan::find()->where(['audit_plan_inspection_id' => $auditInspection->id])->all();
			if ($model !== null)
			{
				foreach($model as $val)
				{
					$inspector_names = '';
					if(count($val->auditplaninspectionplaninspector)>0)
					{
						$inspectors = [];
						foreach($val->auditplaninspectionplaninspector as $inspector)
						{
							$inspectors[] = $inspector->user->first_name." ".$inspector->user->last_name;
						}
						$inspector_names = implode(", ",$inspectors);
					}

					$html.='<tr>
								<td style="text-align:center;" valign="middle" class="reportDetailLayoutInner">'.$insCint.'</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$val->applicationunit->name.'</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$val['activity'].'</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$inspector_names.'</td>
								<td style="text-align:center" valign="middle" class="reportDetailLayoutInner">'.date($date_format,strtotime($val['date'])).'</td>
								<td style="text-align:center" valign="middle" class="reportDetailLayoutInner">'.date('G:i', strtotime($val['start_time'])).'</td>
								<td style="text-align:center" valign="middle" class="reportDetailLayoutInner">'.date('G:i', strtotime($val['end_time'])).'</td>
								<td style="text-align:left" valign="middle" class="reportDetailLayoutInner">'.$val['person_need_to_be_present'].'</td>
							</tr>';
					$insCint++;		
				}
			}
			$html.='</table>';
		}
        return $html;
					  
	}

	public function actionChangeStatus(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			
			$auditmodel = new Audit();
			$auditplanstatusmodel = new AuditPlan();
			
			$model = Audit::find()->where(['id' => $data['audit_id']])->one();
			
			if ($model !== null)
			{
				if(isset($data['inspectiontype']) ){
					//$data['inspectiontype'] == 'sendtocustomer'
					if($data['inspectiontype'] == 'sendtocustomer' || $data['inspectiontype'] == 'followup_sendtocustomer'){
						$data['status'] = $auditmodel->arrEnumStatus['awaiting_for_customer_approval'];
						if($data['inspectiontype'] == 'followup_sendtocustomer'){
							$data['status'] = $auditmodel->arrEnumStatus['awaiting_followup_customer_approval'];
						}
					}
					if($data['inspectiontype'] == 'approveplanbyauditor'){
						$data['status'] = $auditmodel->arrEnumStatus['approved'];
					}
				}


				//Condition Starts Here
				if(isset($data['status'])){
					if($data['status'] == $auditmodel->arrEnumStatus['review_in_process']){
						$canDoAction = 0;
						if($model->status == $auditmodel->arrEnumStatus['submitted']){
							if(Yii::$app->userrole->isAdmin() || $model->created_by == $userid || $model->updated_by == $userid){
								$canDoAction = 1;
							}
						}
						if($canDoAction==0){ return $responsedata; }
					}else if($auditmodel->arrEnumStatus['awaiting_for_customer_approval']== $data['status']){
						$canDoAction = 0;
						if(($model->status == $auditmodel->arrEnumStatus['review_completed'] || $model->status == $auditmodel->arrEnumStatus['inspection_plan_in_process']) && (Yii::$app->userrole->isAdmin() || $model->auditplan->application_lead_auditor == $userid)){
								$canDoAction = 1;
						}
						if($canDoAction==0){ return $responsedata; }
					}else if($auditmodel->arrEnumStatus['awaiting_followup_customer_approval']== $data['status']){
						$canDoAction = 0;
						if(($model->status == $auditmodel->arrEnumStatus['followup_review_completed'] || $model->status == $auditmodel->arrEnumStatus['followup_inspection_plan_inprocess']) && (Yii::$app->userrole->isAdmin() || $model->auditplan->followup_application_lead_auditor == $userid)){
							$canDoAction = 1;
						}
						if($canDoAction==0){ return $responsedata; }
					}else if($auditmodel->arrEnumStatus['approved']== $data['status'] || $auditmodel->arrEnumStatus['rejected']== $data['status']){
						$canDoAction = 0;
						if($model->status == $auditmodel->arrEnumStatus['awaiting_for_customer_approval'] && Yii::$app->userrole->isAuditCustomer($model->id)){
								$canDoAction = 1;
						}
						if($canDoAction==0){ return $responsedata; }
					}else if($auditmodel->arrEnumStatus['followup_booked']== $data['status'] || $auditmodel->arrEnumStatus['followup_rejected_by_customer'] == $data['status']){
						$canDoAction = 0;
						if($model->status == $auditmodel->arrEnumStatus['awaiting_followup_customer_approval'] && Yii::$app->userrole->isAuditCustomer($model->id) ){
							$canDoAction = 1;
						}
						if($canDoAction==0){ return $responsedata; }
					}
				}
				

				//Condition Ends here 



				//$auditmodel->arrEnumStatus['awaiting_for_customer_approval']== $data['status']
				//inspectiontype:'sendtocustomer'
				
				
				//$data['status'] = $auditmodel->arrEnumStatus['awaiting_for_customer_approval'];
				if(isset($data['status']) && $data['status']>0)
				{
					$model->status = $data['status'];
					
					if($model->save())
					{
						$message = 'Audit Plan Sent to Customer Successfully';
						if($auditmodel->arrEnumStatus['rejected']== $data['status'])
						{
							//comments
							$AuditPlanCustomerReview = AuditPlanCustomerReview::find()->where(['audit_plan_id'=>$data['audit_plan_id'],'audit_type'=>1 ])->one();
							if($AuditPlanCustomerReview === null){
								$AuditPlanCustomerReview = new AuditPlanCustomerReview();
							}
							
							$AuditPlanCustomerReview->audit_plan_id = $data['audit_plan_id'];
							$AuditPlanCustomerReview->user_id = $userid;
							$AuditPlanCustomerReview->audit_type = 1;
							$AuditPlanCustomerReview->comment = isset($data['comments'])?$data['comments']:'';
							$AuditPlanCustomerReview->created_by = $userid;
							$AuditPlanCustomerReview->save();
							$message = 'Audit Plan was Rejected Successfully';
						}else if($auditmodel->arrEnumStatus['followup_rejected_by_customer']== $data['status'])
						{
							//comments
							$AuditPlanCustomerReview = AuditPlanCustomerReview::find()->where(['audit_plan_id'=>$data['audit_plan_id'],'audit_type'=>2 ])->one();
							if($AuditPlanCustomerReview === null){
								$AuditPlanCustomerReview = new AuditPlanCustomerReview();
							}
							$AuditPlanCustomerReview->audit_plan_id = $data['audit_plan_id'];
							$AuditPlanCustomerReview->user_id = $userid;
							$AuditPlanCustomerReview->audit_type = 2;
							$AuditPlanCustomerReview->comment = isset($data['comments'])?$data['comments']:'';
							$AuditPlanCustomerReview->created_by = $userid;
							$AuditPlanCustomerReview->save();
							$message = 'Audit Plan was Rejected Successfully';
						}else if($auditmodel->arrEnumStatus['approved'] == $data['status'])
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
							$futureDate = date('Y-m-d', strtotime('+1 year', strtotime($maxdate)) );
							$audit_valid_until = date('d/F/Y', strtotime('-1 day', strtotime($futureDate)));	
							$audit_valid_until_store = date('Y-m-d', strtotime('-1 day', strtotime($futureDate)));	
							$auditPlanmodel = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
							$auditPlanmodel->audit_completed_date = $maxdate;
							$auditPlanmodel->audit_valid_until = $audit_valid_until_store;
							$auditPlanmodel->save();
							
							if($model->audit_type == $model->audittypeEnumArr['unannounced_audit']){
								$UnannouncedAuditApplication = UnannouncedAuditApplication::find()->where(['audit_id'=>$data['audit_id']])->one();
								if($UnannouncedAuditApplication !== null){
									if(count($UnannouncedAuditApplication->unannouncedauditunit)>0){
										foreach($UnannouncedAuditApplication->unannouncedauditunit as $planunitobj){
											if(count($planunitobj->unannouncedauditunitstandard)>0){
												$unitstandards = [];
												foreach($planunitobj->unannouncedauditunitstandard as $unannoucedunitstd){
													$unitstandards[] = $unannoucedunitstd->standard_id;
												}
												$subtopics = Yii::$app->globalfuns->getUnannouncedSubtopic($planunitobj->unit_id, $unitstandards);

												$AuditPlanUnit = AuditPlanUnit::find()->where(['audit_plan_id' => $data['audit_plan_id'], 'unit_id' => $planunitobj->unit_id ])->one();
												$audit_plan_unit_id = $AuditPlanUnit->id;
												
												$auditor_user_id = null;
												$AuditPlanUnitAuditorCnt = AuditPlanUnitAuditor::find()->where(['audit_plan_unit_id'=>$audit_plan_unit_id])->all();
												$TotalAuditorCount = count($AuditPlanUnitAuditorCnt);
												if($TotalAuditorCount==1){
													foreach($AuditPlanUnitAuditorCnt as $auditordata){
														$auditor_user_id = $auditordata->user_id;
													}
												}

												
												if(count($subtopics)>0){
													foreach($subtopics as $sub_topicarr){
														$sub_topic_id = $sub_topicarr['id'];
														$sub_topic_name = $sub_topicarr['name'];
														
														$AuditPlanUnitExecution = new AuditPlanUnitExecution();
														$AuditPlanUnitExecution->audit_plan_unit_id = $audit_plan_unit_id;
														$AuditPlanUnitExecution->sub_topic_id = $sub_topic_id;
														//$SubTopicName = SubTopic::find()->where(['id'=>$sub_topic_id])->one();
														//if($SubTopicName!==null){
															$AuditPlanUnitExecution->sub_topic_name = $sub_topic_name;
														//}
														if($TotalAuditorCount==1 && $auditor_user_id !== null){
															$AuditPlanUnitExecution->executed_by = $auditor_user_id;
														}
														$AuditPlanUnitExecution->status = 0;
														$AuditPlanUnitExecution->save();
													}
													
												}
											}
											//$subtopics = Yii::$app->globalfuns->getSubtopic($planunitobj->unit_id,$planunitobj->id);
										}
									}
									
								}
								if($auditPlanmodel->share_plan_to_customer == $auditPlanmodel->arrSharePlanEnum['share_by_email'] 
								|| $auditPlanmodel->share_plan_to_customer == $auditPlanmodel->arrSharePlanEnum['approval_required']){
									$this->sendAuditPlanMail($data);
								}
								
							}else if($model->audit_type != $model->audittypeEnumArr['unannounced_audit']){
								//store subtopic july 10 2020
								$AuditPlanUnit = AuditPlanUnit::find()->where(['audit_plan_id'=>$data['audit_plan_id']])->all();
								if(count($AuditPlanUnit)>0){
									foreach($AuditPlanUnit as $planunitobj){
										$auditor_user_id = null;
										$AuditPlanUnitAuditorCnt = AuditPlanUnitAuditor::find()->where(['audit_plan_unit_id'=>$planunitobj->id])->all();
										$TotalAuditorCount = count($AuditPlanUnitAuditorCnt);
										if($TotalAuditorCount==1){
											foreach($AuditPlanUnitAuditorCnt as $auditordata){
												$auditor_user_id = $auditordata->user_id;
											}
										}


										//$subtopics = Yii::$app->globalfuns->getSubtopic($planunitobj->unit_id,$planunitobj->id);
										$subtopics = Yii::$app->globalfuns->getCurrentSubtopicIds($planunitobj->unit_id);
										if(count($subtopics)>0){
											foreach($subtopics as $sub_topic_id){
												$AuditPlanUnitExecution = new AuditPlanUnitExecution();
												$AuditPlanUnitExecution->audit_plan_unit_id = $planunitobj->id;
												$AuditPlanUnitExecution->sub_topic_id = $sub_topic_id;
												$SubTopicName = SubTopic::find()->where(['id'=>$sub_topic_id])->one();
												if($SubTopicName!==null){
													$AuditPlanUnitExecution->sub_topic_name = $SubTopicName->name;
												}
												if($TotalAuditorCount==1 && $auditor_user_id !== null){
													$AuditPlanUnitExecution->executed_by = $auditor_user_id;
												}
												$AuditPlanUnitExecution->status = 0;
												$AuditPlanUnitExecution->save();
											}
											
										}
										
									}
								}
								//AuditPlanUnitExecution::find()->
								
								//store subtopic
							
								$appmodel = new Application();
								//if($model->audit_type != 2 ){
								Yii::$app->globalfuns->updateApplicationOverallStatus($model->app_id, $appmodel->arrEnumOverallStatus['audit_in_progress']);
								//}
							}
								

							$message = 'Audit Plan Approved Successfully';
						}else if($auditmodel->arrEnumStatus['awaiting_for_customer_approval']== $data['status'])
						{
							$AuditPlanInspection = AuditPlanInspection::find()->where(['audit_plan_id'=>$data['audit_plan_id'],'audit_type'=>1])->one();
							if($AuditPlanInspection !== null){
								$AuditPlanInspection->sent_by = $userid;
								$AuditPlanInspection->sent_at = time();
								$AuditPlanInspection->save();
							}
							$mailContent = MailNotifications::find()->select('code,subject,message')->where(['code' => 'inspection_plan_to_customer'])->one();
							$data['audit_type'] = '1';
							$html = $this->getInspectionPlan($data);
							$fileName = Yii::$app->params['temp_files'].str_replace(" ","-",$model->application->companyname).'_inspectionplan_'.date('Ymdhis').'.pdf';
							$mpdf = new \Mpdf\Mpdf();
							$mpdf->WriteHTML($html);
							$mpdf->Output($fileName,'F');
							$files = json_encode([$fileName]);

							if($mailContent !== null)
							{
								$MailLookupModel = new MailLookup();
								$MailLookupModel->to=$model->application->emailaddress;								
								$MailLookupModel->subject=$mailContent['subject'];
								$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailContent['message']]);
								$MailLookupModel->attachment=$files;
								$MailLookupModel->mail_notification_id='';
								$MailLookupModel->mail_notification_code=$mailContent->code;
								$Mailres=$MailLookupModel->sendMail();
							}
							$message = 'Audit Plan Sent to Customer Successfully';
						}else if($auditmodel->arrEnumStatus['awaiting_followup_customer_approval']== $data['status'])
						{
							$AuditPlanInspection = AuditPlanInspection::find()->where(['audit_plan_id'=>$data['audit_plan_id'],'audit_type'=>2])->one();
							if($AuditPlanInspection !== null){
								$AuditPlanInspection->sent_by = $userid;
								$AuditPlanInspection->sent_at = time();
								$AuditPlanInspection->save();
							}
							$mailContent = MailNotifications::find()->select('code,subject,message')->where(['code' => 'inspection_plan_to_customer'])->one();
							$data['audit_type'] = '2';
							$html = $this->getInspectionPlan($data);
							$fileName = Yii::$app->params['temp_files'].str_replace(" ","-",$model->application->companyname).'_followupinspectionplan_'.date('Ymdhis').'.pdf';
							$mpdf = new \Mpdf\Mpdf();
							$mpdf->WriteHTML($html);
							$mpdf->Output($fileName,'F');
							$files = json_encode([$fileName]);

							if($mailContent !== null)
							{
								$MailLookupModel = new MailLookup();
								$MailLookupModel->to=$model->application->emailaddress;								
								$MailLookupModel->subject=$mailContent['subject'];
								$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailContent['message']]);
								$MailLookupModel->attachment=$files;
								$MailLookupModel->mail_notification_id='';
								$MailLookupModel->mail_notification_code=$mailContent->code;
								$Mailres=$MailLookupModel->sendMail();
							}
							$message = 'Audit Plan Sent to Customer Successfully';
						}else{
							
							//audit_checklist_inprocess
						}
						if($data['status'] == $auditmodel->arrEnumStatus['review_in_process'])
						{
							$message = 'Audit Plan Confirmed Successfully';

							$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'mail_to_lead_auditor_on_audit_plan_generation'])->one();
							if($mailContent !== null)
							{
								$auditmodal= Audit::find()->where(['id'=>$data['audit_id']])->one();
								if($auditmodal !== null)
								{
									$mailsubject=str_replace('{COMPANY-NAME}', $auditmodal->application->companyname, $mailContent['subject'] );
									$mailmsg=str_replace('{COMPANY-NAME}', $auditmodal->application->companyname, $mailContent['message'] );
									$mailmsg=str_replace('{COMPANY-EMAIL}', $auditmodal->application->emailaddress, $mailmsg );
									$mailmsg=str_replace('{COMPANY-TELEPHONE}', $auditmodal->application->telephone, $mailmsg );
									$mailmsg=str_replace('{CONTACT-NAME}', $auditmodal->application->firstname." ".$auditmodal->application->lastname, $mailmsg );

									$auditplanmodal = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
									if($auditplanmodal !== null)
									{
										$MailLookupModel = new MailLookup();
										$MailLookupModel->to=$auditplanmodal->user->email;										
										$MailLookupModel->subject=$mailsubject;
										$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
										$MailLookupModel->attachment='';
										$MailLookupModel->mail_notification_id='';
										$MailLookupModel->mail_notification_code='';
										$Mailres=$MailLookupModel->sendMail();
									}
								}
							}
						}
						
						$responsedata = ['status'=>1,'message'=>$message,'data'=>['status'=>$model->status]];
					}
				}
				else
				{
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
						$Applicationmodel = new Application();
						if($auditmodelup->audit_type == $auditmodelup->audittypeEnumArr['unannounced_audit'] ){
							$UnannouncedAuditApplication = UnannouncedAuditApplication::find()->where(['audit_id'=>$data['audit_id']])->one();
							if($UnannouncedAuditApplication !== null){
								$UnannouncedAuditApplication->status = $UnannouncedAuditApplication->arrEnumStatus['audit_completed'];
								$UnannouncedAuditApplication->save();
							}
						}else if($auditmodelup->audit_type != $auditmodelup->audittypeEnumArr['unannounced_audit'] ){
							Yii::$app->globalfuns->updateApplicationOverallStatus($auditmodelup->app_id, $Applicationmodel->arrEnumOverallStatus['audit_finalized']);
						
        				



							$parent_app_id = '';
							$version=1;
							$applicationdetails = Application::find()->where(['id'=>$auditmodelup->app_id])->one();
							if($applicationdetails !==null && $applicationdetails->audit_type !=$applicationdetails->arrEnumAuditType['renewal']){

								$ModelCertificate = new Certificate();

								$parent_app_id = $applicationdetails->parent_app_id;

								if($parent_app_id !='' && $parent_app_id>0){
									$connection = Yii::$app->getDb();
									$command = $connection->createCommand("SELECT cert.version as version FROM `tbl_application` AS app
									INNER JOIN `tbl_audit` AS audit ON audit.app_id=app.id AND app.id='".$parent_app_id."'  
									INNER JOIN `tbl_certificate` AS cert ON cert.audit_id=audit.id 
									and cert.status='".$ModelCertificate->arrEnumStatus['certificate_generated']."' 
									ORDER BY cert.version DESC LIMIT 1");
									$result = $command->queryOne();
									if($result !== false)
									{
										$version = $result['version']+1;
									}
								}else{
									$version=1;
									$parent_app_id = $applicationdetails->id;
								}
								
							}
						
							if($applicationdetails !==null && $applicationdetails->audit_type == $applicationdetails->arrEnumAuditType['renewal']){
								$parent_app_id = $applicationdetails->id;
							}


							$StatusModelCertificate = new Certificate();
							if(count($applicationdetails->applicationstandard)>0){
								
								foreach($applicationdetails->applicationstandard as $appstandard){

									$standardID = $appstandard->standard_id;
									$version = 1;
									/*
									if($applicationdetails !==null && $applicationdetails->audit_type !=$applicationdetails->arrEnumAuditType['renewal']){
										$parent_app_id = $applicationdetails->parent_app_id;
										if($parent_app_id !='' && $parent_app_id>0){
											$CertificateExist = Certificate::find()->where(['app_id'=>$parent_app_id,'standard_id'=>$standardID ])->orderBy(['version' => SORT_DESC])->one();
											$version = $CertificateExist->version;
											$version = $version+1;
										}
									}
									*/
									$Certificate = new Certificate();

									if( $applicationdetails->audit_type == $applicationdetails->arrEnumAuditType['normal']){
										$capp_id = $applicationdetails->id;
										$cstandard_id = $standardID;
										$ApplicationCertifiedByOtherCB = ApplicationCertifiedByOtherCB::find()->where(['app_id'=>$capp_id, 'standard_id'=>$cstandard_id]);
										//'' => date('Y-m-d')
										//from_date<='".$to_date."'
										$ApplicationCertifiedByOtherCB = $ApplicationCertifiedByOtherCB->andWhere(' validity_date >= "'.date('Y-m-d').'" ');
										$ApplicationCertifiedByOtherCB = $ApplicationCertifiedByOtherCB->one();
										if($ApplicationCertifiedByOtherCB !== null){
											$Certificate->status = $StatusModelCertificate->arrEnumStatus['certified_by_other_cb'];
										}else{
											$Certificate->status = $StatusModelCertificate->arrEnumStatus['open'];	
										}
									}else{
										$Certificate->status = $StatusModelCertificate->arrEnumStatus['open'];
									}
									
									$Certificate->audit_id = $auditmodelup->id;
									$Certificate->parent_app_id = $parent_app_id;
									$Certificate->standard_id = $standardID;
									$Certificate->product_addition_id = '';
									//$Certificate->status = $StatusModelCertificate->arrEnumStatus['open'];
									$Certificate->certificate_status = $StatusModelCertificate->arrEnumCertificateStatus['invalid'];//1;
									$Certificate->type = $applicationdetails->audit_type;
									//$Certificate->version = $version;
									$Certificate->save();
								}
								
							}

							if($applicationdetails->audit_type == $applicationdetails->arrEnumAuditType['normal'] || $applicationdetails->audit_type == $applicationdetails->arrEnumAuditType['renewal']){
								$capp_id = $applicationdetails->id;
								$ModelApplicationStandard = new ApplicationStandard();
								
								$changestatusval = $ModelApplicationStandard->arrEnumStatus['invalid'];
								$ChangeApplicationStandard = ApplicationStandard::find()->where(['standard_id'=>$standardID,'app_id'=>$capp_id])->one();
								if($ChangeApplicationStandard !== null){
									$ChangeApplicationStandard->standard_status = $changestatusval;
									$ChangeApplicationStandard->save();
								}
								
							}
						}
							




					}
					$responsedata = ['status'=>1,'message'=>'Updated Successfully','data'=>['status'=>$auditmodelup->status]];
				}		
			}
		}
		return $responsedata;
	}

	private function sendAuditPlanMail($data){
		$model = Audit::find()->where(['id' => $data['audit_id']])->one();
			
		if ($model !== null)
		{

			$mailContent = MailNotifications::find()->select('code,subject,message')->where(['code' => 'inspection_plan_to_customer'])->one();
			$data['audit_type'] = '1';
			$html = $this->getInspectionPlan($data);
			$fileName = Yii::$app->params['temp_files'].str_replace(" ","-",$model->application->companyname).'_inspectionplan_'.date('Ymdhis').'.pdf';
			$mpdf = new \Mpdf\Mpdf();
			$mpdf->WriteHTML($html);
			$mpdf->Output($fileName,'F');
			$files = json_encode([$fileName]);

			if($mailContent !== null)
			{
				$MailLookupModel = new MailLookup();
				$MailLookupModel->to=$model->application->emailaddress;				
				$MailLookupModel->subject=$mailContent['subject'];
				$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailContent['message']]);
				$MailLookupModel->attachment=$files;
				$MailLookupModel->mail_notification_id='';
				$MailLookupModel->mail_notification_code=$mailContent->code;
				$Mailres=$MailLookupModel->sendMail();
			}
		}
		return true;
	}
	public function actionAuditStatus(){
		$auditmodel = new Audit();
		return ['enumStatus'=>$auditmodel->arrEnumStatus,'status'=>$auditmodel->arrStatus];
	}

	/*
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
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		$command = $connection->createCommand("SELECT user.first_name,user.last_name,execution.status,execution.executed_by,execution.executed_date,subtopic.id,
			subtopic.name FROM `tbl_application_unit` AS unit 
			INNER JOIN `tbl_application_unit_process` AS uprs ON unit.id=uprs.unit_id 
			INNER JOIN `tbl_application_unit_standard` AS ustd ON unit.id=ustd.unit_id 
			INNER JOIN `tbl_audit_execution_question_process` AS aeqp ON uprs.process_id=aeqp.process_id 
			INNER JOIN `tbl_audit_execution_question_standard` AS aeqs ON ustd.standard_id=aeqs.standard_id 
			INNER JOIN `tbl_audit_execution_question` AS aeq ON aeqp.audit_execution_question_id=aeq.id AND  aeqs.audit_execution_question_id=aeq.id 
			INNER JOIN `tbl_sub_topic` AS subtopic ON subtopic.id=aeq.sub_topic_id 

			LEFT JOIN `tbl_audit_plan_unit_execution` AS execution on execution.sub_topic_id = subtopic.id and execution.audit_plan_unit_id=".$audit_plan_unit_id." 
			LEFT JOIN `tbl_users` AS user on user.id = execution.executed_by  
			WHERE 1=1  ".$condition." 
			AND aeq.status=0 
			GROUP BY subtopic.id");
		$result = $command->queryAll();
		//$dataArr = [];
		 
		//$responsedata =['status'=>1,'data'=>$dataArr];
		

		return $result;

	}
	*/
	/*
	private function getCurrentSubtopic($unit_id,$audit_plan_unit_id='',$userid=''){
		//$query = '';
		// AND aeq.sub_topic_id IS NULL
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		
		$connection = Yii::$app->getDb();
		$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();

		$condition = '';
		$conditionexe = '';
		if($unit_id){
			$condition .= " AND unit.id=".$unit_id;
		}
		if($audit_plan_unit_id!=''){
			$conditionexe .= " and execution.audit_plan_unit_id=".$audit_plan_unit_id;
		}
		

		if($userid){
			$condition .= " AND ( execution.executed_by is null OR execution.executed_by=".$userid.")";
		}
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		$command = $connection->createCommand("SELECT user.first_name,user.last_name,execution.status,execution.executed_by,execution.executed_date,subtopic.id,
			subtopic.name FROM `tbl_application_unit` AS unit 
			INNER JOIN `tbl_audit_plan_unit` as planunit on unit.id=planunit.unit_id 
			INNER JOIN `tbl_sub_topic` AS subtopic ON subtopic.id=aeq.sub_topic_id 
			INNER JOIN `tbl_audit_plan_unit_execution` AS execution on execution.sub_topic_id = subtopic.id and planunit.id= execution.audit_plan_unit_id ".$conditionexe." 
			LEFT JOIN `tbl_users` AS user on user.id = execution.executed_by  
			WHERE 1=1  ".$condition." AND aeq.status=0 GROUP BY subtopic.id");
		$result = $command->queryAll();
		 
		INNER JOIN `tbl_application_unit_process` AS uprs ON unit.id=uprs.unit_id 
		INNER JOIN `tbl_application_unit_standard` AS ustd ON unit.id=ustd.unit_id 
		INNER JOIN `tbl_audit_execution_question_process` AS aeqp ON uprs.process_id=aeqp.process_id 
		INNER JOIN `tbl_audit_execution_question_standard` AS aeqs ON ustd.standard_id=aeqs.standard_id 
		INNER JOIN `tbl_audit_execution_question` AS aeq ON aeqp.audit_execution_question_id=aeq.id AND  aeqs.audit_execution_question_id=aeq.id 
		 
		

		return $result;

	}
	*/
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
			$resource_access=$userData['resource_access'];
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
			$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
			
			
			$condition = '';
			$unit_id = $data['unit_id'];
			$audit_plan_unit_id = $data['audit_plan_unit_id'];
			$audit_id = $data['audit_id'];
			$audit_plan_id = $data['audit_plan_id'];

			$subtopictype = isset($data['subtopictype'])?$data['subtopictype']:'';
			/*
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
			
			

			$command = $connection->createCommand("SELECT subtopic.id,subtopic.name FROM `tbl_application_unit` AS unit 
			INNER JOIN `tbl_application_unit_process` AS uprs ON unit.id=uprs.unit_id 
			INNER JOIN `tbl_application_unit_standard` AS ustd ON unit.id=ustd.unit_id 
			INNER JOIN `tbl_audit_execution_question_process` AS aeqp ON uprs.process_id=aeqp.process_id 
			INNER JOIN `tbl_audit_execution_question_standard` AS aeqs ON ustd.standard_id=aeqs.standard_id 
			INNER JOIN `tbl_audit_execution_question` AS aeq ON aeqp.audit_execution_question_id=aeq.id AND aeqs.audit_execution_question_id=aeq.id 
			INNER JOIN `tbl_sub_topic` AS subtopic ON subtopic.id=aeq.sub_topic_id

			INNER JOIN `tbl_audit_plan_unit_execution` AS execution on execution.sub_topic_id = subtopic.id and execution.audit_plan_unit_id=".$audit_plan_unit_id." 
			LEFT JOIN `tbl_users` AS user on user.id = execution.executed_by  

			WHERE 1=1  ".$condition." AND aeq.status=0 
			GROUP BY subtopic.id");
			$result = $command->queryAll();
			$dataArr = [];
			if(count($result)>0){
				foreach($result as $subdata){
					$dataArr[] =['id'=>$subdata['id'],'name'=>$subdata['name']];
				}
			}
			*/
			$dataArr = [];

			if($subtopictype=='followup_executeAudit'){
				if(!Yii::$app->userrole->isAuditor($audit_plan_unit_id,2) && !Yii::$app->userrole->isAuditProjectLA($audit_id) ){
					return $responsedata;
				}

				$AuditPlanUnitExecutionFollowupStatusModel = new AuditPlanUnitExecutionFollowup();
				//waiting_for_unit_lead_auditor_approval
				//AuditPlanUnitExecutionFollowup::find()->where(['audit_plan_unit_id'=>$audit_plan_unit_id ])->all();
				$unitLeadAuditor = '';
				$applicationLeadAuditor = '';
				$AuditPlanUnitAuditor = AuditPlanUnitAuditor::find()->where(['is_lead_auditor'=>1,'audit_plan_unit_id'=>$audit_plan_unit_id,'audit_type'=>2])->one();
				if($AuditPlanUnitAuditor !== null){
					$unitLeadAuditor = $AuditPlanUnitAuditor->user_id;
				}
				$AuditPlan = AuditPlan::find()->where(['id'=>$audit_plan_id])->one();
				if($AuditPlan !== null){
					$applicationLeadAuditor = $AuditPlan->followup_application_lead_auditor;
				}
				
				$AuditPlanUnitModel = AuditPlanUnit::find()->where(['id' => $audit_plan_unit_id])->one();
				if($AuditPlanUnitModel !== null){
					if($AuditPlanUnitModel->status == $AuditPlanUnitModel->arrEnumStatus['followup_inprocess']
					|| $AuditPlanUnitModel->status == $AuditPlanUnitModel->arrEnumStatus['followup_open']
					|| $AuditPlanUnitModel->status == $AuditPlanUnitModel->arrEnumStatus['followup_lead_auditor_reinitiated']
					){
						//$AuditPlanUnitModel->status == $AuditPlanUnitModel->arrEnumStatus['followup_awaiting_unit_lead_auditor_approval']
						//&& $userid != $unitLeadAuditor
						if( $userid == $unitLeadAuditor){
							$condition .= " AND ( execution.executed_by=".$userid." or execution.status=".$AuditPlanUnitExecutionFollowupStatusModel->arrEnumStatus['waiting_for_unit_lead_auditor_approval'].")";
						}else if($userid && $user_type==1 && $resource_access!=1 ){
							$condition .= " AND ( execution.executed_by=".$userid.")";
						}
					}
					if($AuditPlanUnitModel->status == $AuditPlanUnitModel->arrEnumStatus['followup_awaiting_lead_auditor_approval']
					|| $AuditPlanUnitModel->status == $AuditPlanUnitModel->arrEnumStatus['followup_reviewer_reinitated']){
						if($userid && $user_type==1 && $resource_access!=1 && $userid != $applicationLeadAuditor){
							$condition .= " AND ( execution.executed_by=".$userid.")";
						}
					}
				}
				// $userid != $applicationLeadAuditor && $userid!=$unitLeadAuditor &&
				$AuditPlanUnitExecutionFollowupModel = new AuditPlanUnitExecutionFollowup();
				$command = $connection->createCommand("SELECT execution.sub_topic_id as id,execution.sub_topic_name as name FROM  
				   `tbl_audit_plan_unit_execution_followup` AS execution  
				LEFT JOIN `tbl_users` AS user on user.id = execution.executed_by  
				WHERE 1=1  ".$condition." AND execution.audit_plan_unit_id=".$audit_plan_unit_id." AND execution.status!=".$AuditPlanUnitExecutionFollowupModel->arrEnumStatus['completed']." GROUP BY execution.sub_topic_id");
				$result = $command->queryAll();
				$dataArr = [];
				if(count($result)>0){
					foreach($result as $subdata){
						$dataArr[] =['id'=>$subdata['id'],'name'=>$subdata['name']];
					}
				}
				/*

				$AuditPlanUnitExecutionFollowup = AuditPlanUnitExecutionFollowup::find()->where(['audit_plan_unit_id'=>$audit_plan_unit_id])->all();
				if(count($AuditPlanUnitExecutionFollowup)>0){
					foreach($AuditPlanUnitExecutionFollowup as $subtopic){
						//$subtopic['status']= $subtopic['status']?:0;
						$dataArr[] =['id'=>$subtopic->sub_topic_id,'name'=>$subtopic->subtopic->name];
					}
				}
				*/
			}else{
				/*
				$subtopics = Yii::$app->globalfuns->getCurrentSubtopicIds($unit_id);
				if(count($subtopics)>0){
					$SubTopic = SubTopic::find()->where(['id'=>$subtopics])->all();
					if(count($SubTopic)>0){
						foreach($SubTopic as $subobj){
							$dataArr[] =['id'=>$subobj->id,'name'=>$subobj->name];
						}
					}
				}
				*/
				if(!Yii::$app->userrole->isAuditor($audit_plan_unit_id,1) && !Yii::$app->userrole->isAuditProjectLA($audit_id) ){
					return $responsedata;
				}
				


				if($audit_plan_unit_id){
					$condition .= " AND execution.audit_plan_unit_id=".$audit_plan_unit_id;
				}
	
				$unitLeadAuditor = '';
				$applicationLeadAuditor = '';
				$AuditPlanUnitAuditor = AuditPlanUnitAuditor::find()->where(['is_lead_auditor'=>1,'audit_plan_unit_id'=>$audit_plan_unit_id,'audit_type'=>1])->one();
				if($AuditPlanUnitAuditor !== null){
					$unitLeadAuditor = $AuditPlanUnitAuditor->user_id;
				}
				$AuditPlan = AuditPlan::find()->where(['id'=>$audit_plan_id])->one();
				if($AuditPlan !== null){
					$applicationLeadAuditor = $AuditPlan->application_lead_auditor;
				}
				
				$AuditPlanUnitModel = new AuditPlanUnit();
				$AuditPlanUnitExecutionModel = new AuditPlanUnitExecution();

				$AuditPlanUnit = AuditPlanUnit::find()->where(['id'=>$audit_plan_unit_id])->one();
				if($resource_access != 1 && $AuditPlanUnit !== null && $AuditPlanUnit->status <= $AuditPlanUnitModel->arrEnumStatus['awaiting_for_unit_lead_auditor_approval'] ){
					$planunitstatus = $AuditPlanUnit->status;
					$chkstatus =[$AuditPlanUnitModel->arrEnumStatus['open'],$AuditPlanUnitModel->arrEnumStatus['in_progress'],$AuditPlanUnitModel->arrEnumStatus['reviewer_reinititated']];
					if(in_array($planunitstatus, $chkstatus)){
						if($userid==$unitLeadAuditor){
							$condition .= " AND (execution.executed_by=".$userid." OR execution.status=".$AuditPlanUnitExecutionModel->arrEnumStatus['waiting_for_unit_lead_auditor_approval'].")";
						}else{
							$condition .= " AND execution.executed_by=".$userid." ";
						}
						
					}

					if($planunitstatus == $AuditPlanUnitModel->arrEnumStatus['awaiting_for_unit_lead_auditor_approval'] && $userid!=$unitLeadAuditor ){
						$condition .= " AND execution.executed_by=".$userid." ";
					}
					//$planunitstatus
					 
				}
				/*
				if($userid && $user_type==1 && $userid != $applicationLeadAuditor && $userid!=$unitLeadAuditor && $resource_access!=1 ){
					//$condition .= " AND ( execution.executed_by is null OR execution.executed_by=".$userid.")";
					$condition .= " AND execution.executed_by=".$userid." ";
				}
				*/
				$AuditPlanUnitExecutionModel = new AuditPlanUnitExecution();
				$command = $connection->createCommand("SELECT execution.sub_topic_id as id,execution.sub_topic_name as name FROM  
				   `tbl_audit_plan_unit_execution` AS execution  
				LEFT JOIN `tbl_users` AS user on user.id = execution.executed_by  
				WHERE 1=1  ".$condition." AND execution.audit_plan_unit_id=".$audit_plan_unit_id."  GROUP BY execution.sub_topic_id");
				$result = $command->queryAll();
				$dataArr = [];
				if(count($result)>0){
					foreach($result as $subdata){
						$dataArr[] =['id'=>$subdata['id'],'name'=>$subdata['name']];
					}
				}
			}
			

			$responsedata =['status'=>1,'data'=>$dataArr];
		}

		return $responsedata;

	}
	
	public function listAuditPlanRelation($model)
	{	
		$model = $model->join('left join', 'tbl_audit_plan as plan','plan.audit_id =tbl_audit.id');
		$model = $model->join('left join', 'tbl_audit_plan_unit as plan_unit','plan.id =plan_unit.audit_plan_id');
		$model = $model->join('left join', 'tbl_audit_plan_unit_auditor as plan_unit_auditor','plan_unit.id =plan_unit_auditor.audit_plan_unit_id');
		$model = $model->join('left join', 'tbl_audit_plan_reviewer as plan_reviewer','plan_reviewer.audit_plan_id =plan.id ');
		$model = $model->join('left join', 'tbl_audit_plan_unit_standard as plan_standard','plan_standard.audit_plan_unit_id =plan_unit.id ');				
	}
	
	public function actionListAuditPlan()
    {
		
        $post = yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$login_userid=$userData['userid'];
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
		$usermodel = new User();
		
		$connection = Yii::$app->getDb();
		$connection->createCommand("SET SESSION group_concat_max_len = 1000000;")->execute();
		$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();
		
									

		//$model = Offer::find()->where(['t.status'=>$modelOffer->enumStatus['finalized']])->alias('t');
		//->where(['t.status'=>$modelOffer->enumStatus['finalized']])
		$model = Audit::find()->alias('tbl_audit');			
		
		//$model->joinWith(['audit']);		
		
		$auditRelationJoinWith=false;
		// tbl_audit_plan_unit_auditor, tbl_audit_plan_unit
		if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
		{
			$auditRelationJoinWith=true;
			$this->listAuditPlanRelation($model);
			$model = $model->andWhere(['plan_standard.standard_id'=> $post['standardFilter']]);
			/*sort($post['standardFilter']);
			$standardFilter = implode(',',$post['standardFilter']);
			$model = $model->having(['stdgp'=>$standardFilter]);
			*/
			//$model->andWhere(['country_id'=> $post['standardFilter']]);
		}
		if(isset($post['statusFilter']) && $post['statusFilter']!='')
		{
			if( $post['statusFilter']>='0'){
				$model = $model->andWhere(['tbl_audit.status'=> $post['statusFilter']]);
			}else if( $post['statusFilter']=='0'){
				$model = $model->andWhere(['tbl_audit.status'=> null]);
			}
			
		}

		if(isset($post['riskFilter']) && $post['riskFilter']!='')
		{
			if( $post['riskFilter']>'0'){
				$model = $model->andWhere(['tbl_audit.risk_category'=> $post['riskFilter']]);
			}else if( $post['riskFilter']=='0'){
				$model = $model->andWhere(['tbl_audit.risk_category'=> null]);
			}
			
		}
		
		$appJoinWithStatus=false;
		if($resource_access != 1){
			
			if(!$auditRelationJoinWith)
			{
				$auditRelationJoinWith=true;
				$this->listAuditPlanRelation($model);
			}
			
			$appJoinWithStatus=true;
			$model->innerJoinWith(['application as app']);	
			if($user_type== 1 && ! in_array('invoice_management',$rules) && ! in_array('audit_management',$rules) ){
				return $responsedata;
			}else if($user_type==3 && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$model = $model->andWhere(' app.franchise_id="'.$userid.'" or app.created_by="'.$userid.'" ');
				//$model = $model->join('inner join', 'tbl_application as app','t.app_id=app.id and (app.franchise_id="'.$userid.'" or app.created_by="'.$userid.'")');
			}else if($user_type==2){
				$model = $model->andWhere(' ((app.customer_id="'.$userid.'" AND tbl_audit.audit_type=1) 
				OR (tbl_audit.audit_type=2 AND tbl_audit.status>='.$modelAudit->arrEnumStatus["audit_in_progress"].' AND plan.share_plan_to_customer in (0,1)) 
				OR (tbl_audit.audit_type=2 AND tbl_audit.followup_status=1 )
				OR (tbl_audit.audit_type=2 AND plan.share_plan_to_customer =2 AND  tbl_audit.status>='.$modelAudit->arrEnumStatus["awaiting_for_customer_approval"].' ) )');
				//$model = $model->join('inner join', 'tbl_application as app','t.app_id=app.id and app.customer_id="'.$userid.'"');	
			}
			/*
			else if($user_type==3 && $role!=0 && ! in_array('view_invoice',$rules) ){
				return $responsedata;
			}
			*/
		}
		/*
		if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1 
		&& !in_array('generate_audit_plan',$rules) 
		&& !in_array('audit_execution',$rules)
		&& !in_array('audit_review',$rules)
		&& !in_array('generate_audit_plan',$rules)){
			//$model = $model->join('inner join', 'tbl_application as app','t.app_id=app.id and (app.franchise_id="'.$franchiseid.'")');
			$model = $model->andWhere(' app.franchise_id="'.$franchiseid.'" ');
		}
		*/
		if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1 ){
			//$model = $model->join('inner join', 'tbl_application as app','t.app_id=app.id and (app.franchise_id="'.$franchiseid.'")');
			if(!$appJoinWithStatus){
				$appJoinWithStatus=true;
				$model->innerJoinWith(['application as app']);
			}
			$model = $model->andWhere(' app.franchise_id="'.$franchiseid.'" ');
		}

		if(isset($post['type']) && $post['type']=='audit' and $resource_access != 1){
			if(!$auditRelationJoinWith)
			{
				$auditRelationJoinWith=true;
				$this->listAuditPlanRelation($model);
			}
			
			if($user_type== Yii::$app->params['user_type']['customer']){
				$model = $model->andWhere(' tbl_audit.status>="'.$modelAudit->arrEnumStatus['awaiting_for_customer_approval'].'" ');
			}
			
			if($user_type== Yii::$app->params['user_type']['user']  
				&& in_array('audit_review',$rules)
				&& in_array('audit_execution',$rules)
				&& in_array('generate_audit_plan',$rules)
			){
								
				$model = $model->andWhere('((plan.status="'.$modelAuditPlan->arrEnumStatus['waiting_for_review'].'" )
						OR (plan_reviewer.reviewer_id="'.$userid.'" and  (plan.status>="'.$modelAuditPlan->arrEnumStatus['review_in_progress'].'"
						or  plan.status="'.$modelAuditPlan->arrEnumStatus['reviewer_reinitiated'].'" )))

						or 

						((plan.application_lead_auditor="'.$userid.'" and tbl_audit.status>="'.$modelAudit->arrEnumStatus['review_in_process'].'" )
						OR (plan_unit_auditor.user_id="'.$userid.'" and  tbl_audit.status>="'.$modelAudit->arrEnumStatus['approved'].'"))


						or 

						(tbl_audit.status="'.$modelAudit->arrEnumStatus['open'].'" or tbl_audit.updated_by='.$userid.' or tbl_audit.id is null or tbl_audit.created_by='.$userid.')
						or ( plan.application_lead_auditor="'.$userid.'" and tbl_audit.status>="'.$modelAudit->arrEnumStatus['review_in_process'].'" )

					');

			}else{
				
				$sqlcondition = [];
				// To include in condition starts Here
				if($user_type== Yii::$app->params['user_type']['user']  && in_array('generate_audit_plan',$rules)){
					$sqlcondition[] = ' ( tbl_audit.status="'.$modelAudit->arrEnumStatus['open'].'" or tbl_audit.id is null or tbl_audit.created_by='.$userid.' or tbl_audit.updated_by='.$userid.') ';
				}
				if($user_type== Yii::$app->params['user_type']['user']  && in_array('audit_execution',$rules)){
					$sqlcondition[] = ' ( plan_unit_auditor.user_id="'.$userid.'" and  tbl_audit.status>="'.$modelAudit->arrEnumStatus['approved'].'" ) ';

					$sqlcondition[] = ' ( plan.application_lead_auditor="'.$userid.'" and tbl_audit.status>="'.$modelAudit->arrEnumStatus['review_in_process'].'" ) ';
				}
				if($user_type== Yii::$app->params['user_type']['user']  && in_array('audit_review',$rules)){
					$sqlcondition[] = ' ((plan.status="'.$modelAuditPlan->arrEnumStatus['waiting_for_review'].'" )
					OR (plan_reviewer.reviewer_id="'.$userid.'" and  (plan.status>="'.$modelAuditPlan->arrEnumStatus['review_in_progress'].'"
					or  plan.status="'.$modelAuditPlan->arrEnumStatus['reviewer_reinitiated'].'" ))) ';


					if(!in_array('audit_execution',$rules)
					 	&& !in_array('generate_audit_plan',$rules)
					){
						if(!$appJoinWithStatus){
							$appJoinWithStatus=true;
							$model->innerJoinWith(['application as app']);
						}
						$model = $model->join('left join', 'tbl_application_standard as appstd','appstd.app_id=app.id ');
						$model = $model->join('left join', 'tbl_user_standard as userstd','appstd.standard_id=userstd.standard_id and userstd.user_id='.$login_userid.' and userstd.approval_status =2 ');
						$model = $model->having(' count(distinct appstd.standard_id) = count(distinct userstd.standard_id) ');
					}
				}

				
				/// To include in condition ends here
				if(count($sqlcondition)>0){
					$strSqlCondition = ' '.implode(' OR ',$sqlcondition).' ';
					$model = $model->andWhere( $strSqlCondition );
				}
			}
		}
		
		if(isset($post['franchiseFilter']) && is_array($post['franchiseFilter']) && count($post['franchiseFilter'])>0)
		{
			if(!$appJoinWithStatus){
				$appJoinWithStatus=true;
				$model->innerJoinWith(['application as app']);
			}						
			$model = $model->andWhere(['app.franchise_id'=> $post['franchiseFilter']]);	
		}

		$model = $model->groupBy(['tbl_audit.id']);
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{						
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm'] !='')
			{
				if(!$appJoinWithStatus){
					$appJoinWithStatus=true;
					$model->innerJoinWith(['application as app']);
				}
				$model = $model->join('inner join', 'tbl_application_change_address as appaddress','appaddress.id=app.address_id');			
				$model = $model->join('left join', 'tbl_users as customerdata','customerdata.id =app.customer_id');
			
				$searchTerm = $post['searchTerm'];

				$model = $model->andFilterWhere([
					'or',
					//['like', 't.offer_code', $searchTerm],	
					['like', 'appaddress.company_name', $searchTerm],
					['like', 'appaddress.first_name', $searchTerm],
					['like', 'appaddress.last_name', $searchTerm],
					['like', 'appaddress.telephone', $searchTerm],
					['like', 'customerdata.customer_number', $searchTerm],
				]);								
			}
			$totalCount = $model->count();
			
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
			}
			else
			{
				$model = $model->orderBy(['tbl_audit.id' => SORT_DESC]);
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
			foreach($model as $audit)
			{
				$data=array();
				
				$data['id']=$audit->id;
				$data['audit_type']=$audit->audit_type;
				$data['audit_status']=$audit->status;
				$data['audit_status_name']=isset($audit->arrStatus[$data['audit_status']])?$audit->arrStatus[$data['audit_status']]:'Open';
				//$data['audit_status_name']=$data['audit_status'];
				
				//$data['invoice_id']=$offer->id;
				$data['app_id']=$audit->app_id;
				$data['offer_id']=$audit->offer_id;
				$data['audit_type']=$audit->audit_type;
				//$data['currency']=($offer)?$offer->offerlist->currency:'';
				$data['company_name']=$audit->application?$audit->application->companyname:'';
				
				$data['email_address']=$audit->application?$audit->application->emailaddress:'';
				$data['customer_number']=$audit->application?$audit->application->customer->customer_number:'';	
				
				//$data['invoice_number']=$offer->invoice_number;
				//$data['total_payable_amount']=$offer->total_payable_amount;
				//$data['tax_amount']=$offer->tax_amount;				
				//$data['creator']=$offer->username->first_name.' '.$offer->username->last_name;
				//$data['payment_status_name']=($offer->payment_status!='' )?$modelInvoice->paymentStatus[$offer->payment_status]:'Payment Pending';
				//$data['created_at']=date('M d,Y h:i A',$offer->created_at);
				$data['created_at']=date($date_format,$audit->created_at);
				$data['oss_label'] = $audit->application ? $usermodel->ossnumberdetail($audit->application->franchise_id) : '';
				
				$arrAppStd=array();				
				if($audit)
				{
					$appobj = $audit->application;
					
					if($appobj){
						$data['application_country']=$appobj->countryname;
						$data['application_city']=$appobj->city;
					}
					
					
					//$appStd = $appobj->applicationstandard;

					if($audit->audit_type == 2)
					{
						$unannouncedauditobj = $audit->unannouncedaudit;
						$data['application_unit_count']=count($unannouncedauditobj->unannouncedauditunit);
						$appStd=$unannouncedauditobj->unannouncedauditstandard;
						if(count($appStd)>0)
						{	
							foreach($appStd as $app_standard)
							{
								$stddata = [];
								$stddata['id'] = $app_standard->standard_id;
								$stddata['name'] = $app_standard->standard->code;
								$arrAppStd[]=$app_standard->standard->code;
								$standardarr[] = $stddata;
							}
						}
					}else{
						$data['application_unit_count']=($appobj && $appobj->applicationunit)?count($appobj->applicationunit):0;
						if($appobj){
							$appStd=$appobj->applicationstandardview;
							if(count($appStd)>0)
							{	
								foreach($appStd as $app_standard)
								{
									$stddata = [];
									$stddata['id'] = $app_standard->standard_id;
									$stddata['name'] = $app_standard->standard->code;
									$arrAppStd[]=$app_standard->standard->code;
									$standardarr[] = $stddata;
								}
							}
						}
						
					}
					
					
					$data['audit_standard'] = $standardarr;
					$data['application_standard']=implode(', ',$arrAppStd);
				}			
				
				$app_list[]=$data;
			}
		}
		
		$audit = new Audit;
		return ['listauditplan'=>$app_list,'total'=>$totalCount,'arrEnumStatus'=>$audit->arrEnumStatus];
	}

	public function actionGetAudits()
    {
		$modelInvoice = new Invoice();
		$modelOffer = new Offer();
		$modelAudit = new Audit();
		$modelAuditPlan = new AuditPlan();

		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");	
		if ($data) 
		{
			$auditmodel = Audit::find()->where(['app_id'=>$data['id']])->one();
			$offermodel = Offer::find()->where(['app_id'=>$data['id']])->one();

			if($offermodel!==null)
			{
				$responsedata=array('status'=>1,'audit_id'=>(($auditmodel!==null)?$auditmodel->id:''),'offer_id'=>$offermodel->id);
			}

		}
		return $this->asJson($responsedata);
	}	
	
	public function actionDeleteaudit()
	{
		//$auditID=array('15','16','85');
		$auditID=array('23');
		$modelAuditPlan = AuditPlan::find()->where(['audit_id' => $auditID])->all();
		if(count($modelAuditPlan)>0)
		{
			foreach($modelAuditPlan as $model)
			{
				//Delete the unit, unit date, unit standard, unit auditor & unit auditor date
				$auditPlanUnitObj = $model->auditplanunit;
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
								$auditPlanUnitDate->delete();
							}
						}				

						//Audit Plan  Unit Standard
						$auditPlanUnitStandardsObj = $auditPlanUnit->unitstandard;
						if(count($auditPlanUnitStandardsObj)>0)
						{
							foreach($auditPlanUnitStandardsObj as $auditPlanUnitStandard)
							{
								$auditPlanUnitStandard->delete();
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
										$auditPlanUnitAuditorDate->delete();
									}
								}
								$auditPlanUnitAuditor->delete();								
							}
						}								
						$auditPlanUnit->delete();
					}					
				}
				
				$auditmodel = Audit::find()->where(['id' => $model->audit_id])->one();
				if($auditmodel!==null)
				{
					$auditmodel->delete();
				}
				
				$model->delete();
			}	
		}
	}
	
	public function actionValidateAuditReport()
	{
		/*
		$audit_report_message='
		<div class="text-danger">
			<p>This Audit Report details are empty/blank. You should enter data before submitting for Lead Auditor:</p>
			<ul>
				<li>Attendance Sheet.</li>	
				<li>Sampling.</li>
				<li>Worker Interview.</li>
				<li>Client Information.</li>
				<li>Environment.</li>
				<li>Living Wage.</li>
				<li>Quantity Balance Sheet (QBS).</li>
				<li>Chemical List.</li>
				<li>Audit & NC Report.</li>
			</ul>
		</div>';
		*/
		
		$reportFillStatus=true;	
		$audit_report_title = 'Confirmation';
		$post = yii::$app->request->post();		
		if($post)
		{
			//$responsedata=array('audit_report_valid'=>$reportFillStatus,'audit_report_title'=>$audit_report_title,'audit_report_message'=>'');
			//return $this->asJson($responsedata);

			$auditID = $post['audit_id'];
			$unitID = isset( $post['unit_id'])?$post['unit_id']:'';
			$audit_plan_id = isset( $post['audit_plan_id'])?$post['audit_plan_id']:'';
			$appID = $post['app_id'];

			$innerContent='';	
			$connection = Yii::$app->getDb();

			$audit_type = 1;
			$AuditModel = Audit::find()->where(['id'=>$auditID])->one();
			if($AuditModel !== null){
				if($AuditModel->audit_type == 2){
					$audit_type = 2;
					//$responsedata=array('audit_report_valid'=>true,'audit_report_title'=>'','audit_report_message'=>'');
					//return $this->asJson($responsedata);
				}				
			}
			
			/*			
			$modelAuditReportAttendanceSheet = AuditReportAttendanceSheet::find()->where(['audit_id' => $auditID,'unit_id' => $unitID])->all();		
			if(count($modelAuditReportAttendanceSheet)<=0)
			{
				$innerContent.='<li>Attendance Sheet.</li>';
				$reportFillStatus=false;				
			}		
			
			$modelAuditReportSampling = AuditReportSampling::find()->where(['audit_id' => $auditID,'unit_id' => $unitID])->all();
			if(count($modelAuditReportSampling)<=0)
			{
				$innerContent.='<li>Sampling.</li>';
				$reportFillStatus=false;
			}
			*/			
			/*
			$total_employees=0;
			
			$commandReportInterviewSummary = $connection->createCommand("SELECT SUM(total_employees) AS total_emp FROM `tbl_audit_report_interview_summary` WHERE audit_id='".$auditID."' AND unit_id='".$unitID."'");
			$interviewSummaryResult = $commandReportInterviewSummary->queryOne();
			if(count($interviewSummaryResult)>0)
			{
				$total_employees = $interviewSummaryResult['total_emp'];
			}
			
			$modelAuditReportInterviewEmployees = AuditReportInterviewEmployees::find()->where(['audit_id' => $auditID,'unit_id' => $unitID])->all();
			$modelAuditReportInterviewRequirementReview = AuditReportInterviewRequirementReview::find()->where(['audit_id' => $auditID,'unit_id' => $unitID])->all();
			if(count($modelAuditReportInterviewEmployees)<=0 || count($modelAuditReportInterviewRequirementReview)<=0 || $total_employees<=0)
			{
				$innerContent.='<li>Worker Interview.</li>';
				$reportFillStatus=false;
			}		
			*/
			
			/*
			$modelAuditReportEnvironment = AuditReportEnvironment::find()->where(['audit_id' => $auditID,'unit_id' => $unitID])->all();
			if(count($modelAuditReportEnvironment)<=0)
			{
				$innerContent.='<li>Environment.</li>';	
				$reportFillStatus=false;
			}
			
			
			$modelAuditReportChemicalList = AuditReportChemicalList::find()->where(['audit_id' => $auditID,'unit_id' => $unitID])->all();
			if(count($modelAuditReportChemicalList)<=0)
			{
				$innerContent.='<li>Living Wage.</li>';	
			}
			*/			
			
			/*			
			$modelAuditReportQbsScopeHolder = AuditReportQbsScopeHolder::find()->where(['audit_id' => $auditID,'unit_id' => $unitID])->all();
			if(count($modelAuditReportQbsScopeHolder)<=0)
			{
				$innerContent.='<li>Quantity Balance Sheet (QBS).</li>';
				$reportFillStatus=false;
			}

			*/
			$Application = Application::find()->where(['id'=>$appID])->one();

			//$subtopicArr = Yii::$app->globalfuns->getCurrentSubtopicIds($Application->applicationscopeholder->id);
			/*$subtopicArr = [];
			if(count($result)>0){
				foreach($result as $subdata){
					$subtopicArr[] =$subdata['id'];
				}
			}*/
			//$chkdata = ['unit_id'=>$Application->applicationscopeholder->id, 'sub_topic_id' =>$subtopicArr,'report_name'=>'clientinformation_list'];
			$clientformstatus = 1;//Yii::$app->globalfuns->getReportsAccessible($chkdata);


			if($unitID && $unitID>0){
				$ApplicationUnit = ApplicationUnit::find()->where(['id'=>$unitID])->all();
			}else{
				$unannoucedUnitIDs = [];
				if($audit_type  == 2){
					$unannoucedUnitIDs = Yii::$app->globalfuns->getUnannoucedAuditUnit($auditID);
					if(count($unannoucedUnitIDs)>0){
						foreach($unannoucedUnitIDs as $unannounced_unitid){
							$unannoucedUnitIDs[] = $unannounced_unitid['id'];
						}
					}
					$ApplicationUnit = ApplicationUnit::find()->where(['id'=>$unannoucedUnitIDs])->all();
				}else{
					$ApplicationUnit = ApplicationUnit::find()->where(['app_id'=>$appID])->all();
				}
				
			}
			 
			if(count($ApplicationUnit)>0){
				foreach($ApplicationUnit as $appunit){
					$AuditPlanUnit = AuditPlanUnit::find()->where(['unit_id'=>$appunit->id,'audit_plan_id'=>$audit_plan_id ])->one();
					if($AuditPlanUnit === null){
						continue;
					}
					//$currentsubtopic = $this->getCurrentSubtopic($appunit->id);
					/*
					$result = Yii::$app->globalfuns->getCurrentSubtopic($appunit->id);
					$subtopicArr = [];
					if(count($result)>0){
						foreach($result as $subdata){
							$subtopicArr[] =$subdata['id'];
						}
					}
					*/
					$subtopicArr = Yii::$app->globalfuns->getCurrentSubtopicIds($appunit->id);


					$unitID = $appunit->id;

					if($audit_type  != 2){
						$checkenvironment = 1;
						$AuditReportApplicableDetails = AuditReportApplicableDetails::find()->where(['unit_id' => $unitID,'report_name'=>'environment_list'])->one();
						if($AuditReportApplicableDetails!==null){
							if($AuditReportApplicableDetails->status == '2'){
								$checkenvironment = 0;
							}
						}
						$chkdata = ['unit_id'=>$unitID, 'sub_topic_id' =>$subtopicArr];

						$chkdata['report_name'] = 'environment_list';
						$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
						if($checkenvironment && $formstatus){
							$modelAuditReportEnvironment = AuditReportEnvironment::find()->where(['unit_id' => $unitID])->all();
							if(count($modelAuditReportEnvironment)<=0)
							{
								$innerContent.='<li>Environment for '.$appunit->name.'.</li>';	
								$reportFillStatus=false;
							}

						}

						//$chkdata = ['unit_id'=>$unitID,'report_name'=>'chemical_list'];
						$chkdata['report_name'] = 'chemical_list';
						$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
						if($formstatus){
							$checkdata = 1;
							$AuditReportApplicableDetails = AuditReportApplicableDetails::find()->where(['unit_id' => $unitID,'report_name'=>'chemical_list'])->one();
							if($AuditReportApplicableDetails!==null){
								if($AuditReportApplicableDetails->status == '2'){
									$checkdata = 0;
								}
							}
							if($checkdata){
								$AuditReportChemicalList = AuditReportChemicalList::find()->where(['unit_id' => $unitID])->all();
								if(count($AuditReportChemicalList)<=0)
								{
									$innerContent.='<li>Chemical List for '.$appunit->name.'.</li>';	
									$reportFillStatus=false;
								}
							}
						}

						//$chkdata = ['unit_id'=>$unitID,'report_name'=>'sampling_list'];
						$chkdata['report_name'] = 'sampling_list';
						$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
						if($formstatus){
							$checkdata = 1;
							$AuditReportApplicableDetails = AuditReportApplicableDetails::find()->where(['unit_id' => $unitID,'report_name'=>'sampling_list'])->one();
							if($AuditReportApplicableDetails!==null){
								if($AuditReportApplicableDetails->status == '2'){
									$checkdata = 0;
								}
							}
							if($checkdata){
								$AuditReportSampling = AuditReportSampling::find()->where(['unit_id' => $unitID])->all();
								if(count($AuditReportSampling)<=0)
								{
									$innerContent.='<li>Sampling for '.$appunit->name.'.</li>';	
									$reportFillStatus=false;
								}
							}
						}

						//$chkdata = ['unit_id'=>$unitID,'report_name'=>'attendance_list'];
						$chkdata['report_name'] = 'attendance_list';
						$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
						if($formstatus){
							$checkdata = 1;
							$AuditReportApplicableDetails = AuditReportApplicableDetails::find()->where(['unit_id' => $unitID,'report_name'=>'attendance_list'])->one();
							if($AuditReportApplicableDetails!==null){
								if($AuditReportApplicableDetails->status == '2'){
									$checkdata = 0;
								}
							}
							if($checkdata){
								$AuditReportAttendanceSheet = AuditReportAttendanceSheet::find()->where(['audit_id' => $auditID, 'unit_id' => $unitID])->all();
								if(count($AuditReportAttendanceSheet)<=0)
								{
									$innerContent.='<li>Attendance Sheet List for '.$appunit->name.'.</li>';	
									$reportFillStatus=false;
								}else{
									$AuditReportAttendanceSheet = AuditReportAttendanceSheet::find()->where(['audit_id' => $auditID,'unit_id' => $unitID]);
									$AuditReportAttendanceSheet = $AuditReportAttendanceSheet->andWhere(' ( `close` IS NULL or `close`="" )')->one();
									if($AuditReportAttendanceSheet!==null){
										$innerContent.='<li>Attendance Sheet List closed status for '.$appunit->name.'.</li>';	
										$reportFillStatus=false;
									}
								}
							}
						}

						//$chkdata = ['unit_id'=>$unitID,'report_name'=>'interview_list'];
						$chkdata['report_name'] = 'interview_list';
						$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
						if($formstatus){
							$checkdata = 1;
							$AuditReportApplicableDetails = AuditReportApplicableDetails::find()->where(['unit_id' => $unitID,'report_name'=>'interview_list'])->one();
							if($AuditReportApplicableDetails!==null){
								if($AuditReportApplicableDetails->status == '2'){
									$checkdata = 0;
								}
							}
							if($checkdata){
								//$AuditReportAttendanceSheet = AuditReportAttendanceSheet::find()->where(['unit_id' => $unitID])->all();
								$AuditReportInterviewSummary = AuditReportInterviewSummary::find()->where(['audit_id' => $auditID, 'unit_id' => $unitID])->one();
								$AuditReportInterviewEmployees = AuditReportInterviewEmployees::find()->where(['audit_id' => $auditID,'unit_id' => $unitID])->one();
								$AuditReportInterviewRequirementReview = AuditReportInterviewRequirementReview::find()->where(['audit_id' => $auditID])->andWhere(['unit_id' => $unitID])->one();
								$total_employees = 0;
								$commandReportInterviewSummary = $connection->createCommand("SELECT SUM(total_employees) AS total_emp, SUM(to_be_sampled_employees) AS to_be_sampled_employees, SUM(no_of_sampled_employees) AS no_of_sampled_employees FROM `tbl_audit_report_interview_summary` WHERE audit_id='".$auditID."' AND unit_id='".$unitID."'");
								$interviewSummaryResult = $commandReportInterviewSummary->queryOne();
								if(count($interviewSummaryResult)>0)
								{
									$total_employees = $interviewSummaryResult['total_emp'];
									$to_be_sampled_employees = $interviewSummaryResult['to_be_sampled_employees'];
									$no_of_sampled_employees = $interviewSummaryResult['no_of_sampled_employees'];
								}
								if($AuditReportInterviewEmployees === null)
								{
									$innerContent.='<li>Employee Interview List for '.$appunit->name.'.</li>';	
									$reportFillStatus=false;
								}
								if($AuditReportInterviewSummary === null || $total_employees<=0)
								{
									$innerContent.='<li>Interview Summary for '.$appunit->name.'.</li>';	
									$reportFillStatus=false;
								}
								if($no_of_sampled_employees<$to_be_sampled_employees)
								{
									$innerContent.='<li>Interview Summary for '.$appunit->name.': "No. Sampled Employees" should be greater than or equal to "To be Sampled Employees".</li>';	
									$reportFillStatus=false;
								}
								
								if( $AuditReportInterviewRequirementReview === null )
								{
									$innerContent.='<li>Interview Cheklist for '.$appunit->name.'.</li>';	
									$reportFillStatus=false;
								}
							}
						}


						//$chkdata = ['unit_id'=>$unitID,'report_name'=>'livingwage_list'];
						$chkdata['report_name'] = 'livingwage_list';
						$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
						if($formstatus){
							$checkdata = 1;
							$AuditReportApplicableDetails = AuditReportApplicableDetails::find()->where(['unit_id' => $unitID,'report_name'=>'livingwage_list'])->one();
							if($AuditReportApplicableDetails!==null){
								if($AuditReportApplicableDetails->status == '2'){
									$checkdata = 0;
								}
							}
							if($checkdata){
								$AuditReportLivingWageFamilyExpenses = AuditReportLivingWageFamilyExpenses::find()->where(['audit_id' => $auditID,'unit_id' => $unitID])->one();
								$AuditReportLivingWageRequirementReview = AuditReportLivingWageRequirementReview::find()->where(['audit_id' => $auditID])->andWhere(['unit_id' => $unitID])->one();

								if($AuditReportLivingWageFamilyExpenses === null)
								{
									$innerContent.='<li>Average Family Expenses Information for '.$appunit->name.'.</li>';	
									$reportFillStatus=false;
								}
								if($AuditReportLivingWageRequirementReview === null)
								{
									$innerContent.='<li>Living Wage Requirement for '.$appunit->name.'.</li>';	
									$reportFillStatus=false;
								}
							}
						}

						$chkdata['report_name'] = 'clientinformation_list';
						$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
						if($formstatus){
							$AuditReportClientInformationProcess = AuditReportClientInformationProcess::find()->where(['app_id' => $appID,'unit_id' => $unitID])->one();
							if($AuditReportClientInformationProcess === null){
								$innerContent.='<li>Product Controls for '.$appunit->name.'.</li>';	
								$reportFillStatus=false;
							}else{
								$AuditReportClientInformationProcess = AuditReportClientInformationProcess::find()->where(['app_id' => $appID,'unit_id' => $unitID,'sufficient'=>null])->one();
								if($AuditReportClientInformationProcess!==null){
									$innerContent.='<li>Product Controls is Sufficient for '.$appunit->name.'.</li>';	
									$reportFillStatus=false;
								}
							}
						}
						

						//$chkdata = ['unit_id'=>$unitID,'report_name'=>'qbs'];
						$chkdata['report_name'] = 'qbs';
						$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
						if($formstatus){
							$AuditReportQbsScopeHolder = AuditReportQbsScopeHolder::find()->where(['unit_id' => $unitID])->one();
							if($AuditReportQbsScopeHolder === null){
								$innerContent.='<li>QBS for '.$appunit->name.'.</li>';	
								$reportFillStatus=false;
							}
						}
					}



					$AuditReportNcnReport = AuditReportNcnReport::find()->where(['unit_id' => $unitID, 'audit_id'=>$auditID])->one();
					if($AuditReportNcnReport === null){
						$innerContent.='<li>Audit NCN Report for '.$appunit->name.'.</li>';	
						$reportFillStatus=false;
					}else{
						
						$ncnvalues = [];
						if($AuditReportNcnReport->effectiveness_of_corrective_actions==''){
							$ncnvalues[] = 'Effectiveness of the Corrective actions';
						}
						if($AuditReportNcnReport->audit_team_recommendation==''){
							$ncnvalues[] = 'Audit Team Recommendation';
						}
						//if($AuditReportNcnReport->measures_for_risk_reduction==''){
						//	$ncnvalues[] = 'Effectiveness of the Corrective actions';
						//}
						if($AuditReportNcnReport->summary_of_evidence==''){
							$ncnvalues[] = 'Summary of evidence';
						}
						if($AuditReportNcnReport->potential_high_risk_situations==''){
							$ncnvalues[] = 'Any Potential high-risk situations';
						}
						if($AuditReportNcnReport->entities_and_processes_visited==''){
							$ncnvalues[] = 'Entities and Processes visited';
						}
						if($AuditReportNcnReport->people_interviewed==''){
							$ncnvalues[] = 'People interviewed';
						}
						if($AuditReportNcnReport->type_of_documents_reviewed==''){
							$ncnvalues[] = 'Type of documents reviewed';
						}
						if(count($ncnvalues)>0){
							$innerContent.='<li>Audit NCN Report: '.implode(', ',$ncnvalues).' for '.$appunit->name.'.</li>';
							$reportFillStatus=false;
						}
							
					}

				}
			}


			

			if($clientformstatus && $audit_type !=2){
				$AuditReportClientInformationGeneralInfo = AuditReportClientInformationGeneralInfo::find()->where(['app_id' => $appID])->one();
				if($AuditReportClientInformationGeneralInfo === null){
					$innerContent.='<li>General Information cannot be blank.</li>';	
					$reportFillStatus=false;
				}else{
					$AuditReportClientInformationGeneralInfoDetails = AuditReportClientInformationGeneralInfoDetails::find()->where(['client_information_general_info_id' => $AuditReportClientInformationGeneralInfo->id,'sufficient'=>null])->one();
					if($AuditReportClientInformationGeneralInfoDetails!==null){
						$innerContent.='<li>Sufficient cannot be blank in General Information.</li>';	
						$reportFillStatus=false;
					}
				}

				
				$checksupplier = 1;
				$AuditReportApplicableDetails = AuditReportApplicableDetails::find()->where(['app_id' => $appID,'report_name'=>'supplier_list','status'=>2])->one();
				if($AuditReportApplicableDetails!==null){
					$checksupplier = 0;
				}
				if($checksupplier){
					$AuditReportClientInformationSupplierInformation = AuditReportClientInformationSupplierInformation::find()->where(['app_id' => $appID])->one();
					if($AuditReportClientInformationSupplierInformation === null){
						$innerContent.='<li>Supplier Information cannot be blank.</li>';	
						$reportFillStatus=false;
					}else{
						$AuditReportClientInformationSupplierInformation = AuditReportClientInformationSupplierInformation::find()->where(['app_id' => $appID,'sufficient'=>null])->one();
						if($AuditReportClientInformationSupplierInformation !== null){
							$innerContent.='<li>Sufficient cannot be blank in Supplier Information.</li>';	
							$reportFillStatus=false;
						}
					}
				}
				

				$AuditReportClientInformationChecklistReview = AuditReportClientInformationChecklistReview::find()->where(['app_id' => $appID])->one();
				if($AuditReportClientInformationChecklistReview === null){
					$innerContent.='<li>Client Information Checklist.</li>';	
					$reportFillStatus=false;
				}else{
					$AuditReportClientInformationChecklistReviewComment = AuditReportClientInformationChecklistReviewComment::find()->where(['client_information_checklist_review_id' => $AuditReportClientInformationChecklistReview->id,'answer'=>''])->one();
					if($AuditReportClientInformationChecklistReviewComment !== null){
						$innerContent.='<li>Sufficient cannot be blank in Client Information Checklist.</li>';	
						$reportFillStatus=false;
					}
				}
			}
			
			/*
			$modelAuditReportChemicalList = AuditReportChemicalList::find()->where(['audit_id' => $auditID,'unit_id' => $unitID])->all();
			if(count($modelAuditReportChemicalList)<=0)
			{
				$innerContent.='<li>Chemical List.</li>';
				$reportFillStatus=false;
			}					
			*/
			$audit_report_message='
			<div class="text-danger m-t-10 m-l-15 m-r-15">
				<strong>This Audit Report details are empty/blank. You should enter data before submitting for Lead Auditor:</strong><br>
				<ul>
					'.$innerContent.'									
				</ul>
			</div>';
		}	
		
		if(!$reportFillStatus)
		{
			$audit_report_title = 'Notification';
		}
			  
		$responsedata=array('audit_report_valid'=>$reportFillStatus,'audit_report_title'=>$audit_report_title,'audit_report_message'=>$audit_report_message);
		return $this->asJson($responsedata);
	}


	public function actionGetreportlist(){
		$data = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];

		$environmentliststatus = false;
		$app_id = $data['app_id'];
		
		$audit_id = isset($data['audit_id'])?$data['audit_id']:'';
		

		$applicableforms = Yii::$app->globalfuns->getbasicreportlist($data);

		$audit_type = '';
		$share_plan_to_customer = 1;
		$showsendtocustomer = 1;
		$Audit = Audit::find()->where(['id'=>$data['audit_id']])->one();
		if($Audit !== null){
			$audit_type = $Audit->audit_type;

			$share_plan_to_customer = $Audit->auditplan->share_plan_to_customer;

			if($share_plan_to_customer == $Audit->auditplan->arrSharePlanEnum['donot_share']
				|| $share_plan_to_customer == $Audit->auditplan->arrSharePlanEnum['share_by_email'])
			{
				$showsendtocustomer = 0;
			}
		}
		$applicableforms['audit_type'] = $audit_type;
		$applicableforms['share_plan_to_customer'] = $share_plan_to_customer;
		$applicableforms['showsendtocustomer'] = $showsendtocustomer;
		
		return $this->asJson($applicableforms);
	}

	public function actionGetapplicationunit(){
		$data = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];

		$environmentliststatus = false;
		$audit_id = $data['audit_id'];
		$audit_plan_id = $data['audit_plan_id'];
		$arrAppUnit = [];
		$audit_type = 1;
		$Audit = Audit::find()->where(['id'=>$data['audit_id']])->one();
		if($Audit !== null){
			
			if($Audit->status == $Audit->arrEnumStatus['followup_review_completed'] ||  $Audit->status == $Audit->arrEnumStatus['followup_inspection_plan_inprocess']){
				$audit_type = 2;
				
			}
			$AuditPlanUnit = AuditPlanUnit::find()->where(['audit_plan_id'=>$audit_plan_id]);
			if($audit_type ==2){
				$AuditPlanUnit = $AuditPlanUnit->andWhere(['followup_status'=>1]);
			}
			$AuditPlanUnit = $AuditPlanUnit->all();
			if(count($AuditPlanUnit)>0){
				foreach($AuditPlanUnit as $unit){
					if($unit->unitdata){
						$arrAppUnit[] = ['id'=> $unit->unitdata->id,'name'=>$unit->unitdata->name];
					}
					
				}
			}
		}
		return ['app_units'=>$arrAppUnit,'audit_type'=>$audit_type];
		//$applicableforms = Yii::$app->globalfuns->getbasicreportlist($data);
		
		//return $this->asJson($applicableforms);
	}

	private function isSubtopicAssigned($audit_plan_unit_id){
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];

		$AuditPlanUnitExecutionModel = new AuditPlanUnitExecution();

		$unitLeadAuditor = '';
		$applicationLeadAuditor = '';
		$AuditPlanUnitAuditor = AuditPlanUnitAuditor::find()->where(['is_lead_auditor'=>1,'audit_plan_unit_id'=>$audit_plan_unit_id,'audit_type'=>1])->one();
		if($AuditPlanUnitAuditor !== null){
			$unitLeadAuditor = $AuditPlanUnitAuditor->user_id;
		}
		if($unitLeadAuditor == $userid){
			$AuditPlanUnitExecution = AuditPlanUnitExecution::find()->alias('t')->innerJoinWith('auditplanunit as auditplanunit')->where(['t.audit_plan_unit_id'=>$audit_plan_unit_id ]);
			$AuditPlanUnitExecution = $AuditPlanUnitExecution->andWhere(' ((t.executed_by = '.$userid.') or (t.status='.$AuditPlanUnitExecutionModel->arrEnumStatus['waiting_for_unit_lead_auditor_approval'].' and auditplanunit.unit_lead_auditor='.$userid.') )' );
			$AuditPlanUnitExecution = $AuditPlanUnitExecution->one();
		}else{
			$AuditPlanUnitExecution = AuditPlanUnitExecution::find()->where(['audit_plan_unit_id'=>$audit_plan_unit_id,'executed_by'=>$userid])->one();
		}

		$hassubtopic = 0;
		if($AuditPlanUnitExecution !== null){
			$hassubtopic = 1;
		}
		if($resource_access ==1){
			$hassubtopic = 1;
		}
		return $hassubtopic;
		//tbl_audit_plan_unit_execution
		/*
		$AuditPlan = AuditPlan::find()->where(['id'=>$audit_plan_id])->one();
		if($AuditPlan !== null){
			$applicationLeadAuditor = $AuditPlan->application_lead_auditor;
		}
		*/


		
	}
	
	private function isFollowupSubtopicAssigned($audit_plan_unit_id){
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];

		$AuditPlanUnitExecutionModel = new AuditPlanUnitExecutionFollowup();

		$unitLeadAuditor = '';
		$applicationLeadAuditor = '';
		$AuditPlanUnitAuditor = AuditPlanUnitAuditor::find()->where(['is_lead_auditor'=>1,'audit_plan_unit_id'=>$audit_plan_unit_id,'audit_type'=>2])->one();
		if($AuditPlanUnitAuditor !== null){
			$unitLeadAuditor = $AuditPlanUnitAuditor->user_id;
		}
		 
		if($unitLeadAuditor == $userid){
			
			$AuditPlanUnitExecution = AuditPlanUnitExecutionFollowup::find()->alias('t')->innerJoinWith('auditplanunit as auditplanunit')->where(['t.audit_plan_unit_id'=>$audit_plan_unit_id ]);
			$AuditPlanUnitExecution = $AuditPlanUnitExecution->andWhere(' ((t.executed_by = '.$userid.') or (t.status='.$AuditPlanUnitExecutionModel->arrEnumStatus['waiting_for_unit_lead_auditor_approval'].' and auditplanunit.followup_unit_lead_auditor='.$userid.') )' );
			$AuditPlanUnitExecution = $AuditPlanUnitExecution->one();
		}else{
			$AuditPlanUnitExecution = AuditPlanUnitExecutionFollowup::find()->where(['audit_plan_unit_id'=>$audit_plan_unit_id,'executed_by'=>$userid])->one();
		}

		$hassubtopic = 0;
		if($AuditPlanUnitExecution !== null){
			$hassubtopic = 1;
		}
		if($resource_access ==1){
			$hassubtopic = 1;
		}
		return $hassubtopic;
	}
	


	private function isFollowupShowAssignSubtopic($audit_plan_unit_id){
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];

		$AuditPlanUnitExecutionModel = new AuditPlanUnitExecutionFollowup();

		$AuditPlanUnitExecution = AuditPlanUnitExecutionFollowup::find()->where(['audit_plan_unit_id'=>$audit_plan_unit_id,'executed_by'=>null])->one();

		$AuditPlanUnitExecutionUser = AuditPlanUnitExecutionFollowup::find()->where(['status'=>$AuditPlanUnitExecutionModel->arrEnumStatus['open'], 'audit_plan_unit_id'=>$audit_plan_unit_id,'executed_by'=>$userid ])->one();
		
		$showassignsubtopic = 0;
		if($AuditPlanUnitExecution !== null || $AuditPlanUnitExecutionUser !== null){
			$showassignsubtopic = 1;
		}
		return $showassignsubtopic;
	}

	private function isShowAssignSubtopic($audit_plan_unit_id){
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];

		$AuditPlanUnitExecutionModel = new AuditPlanUnitExecution();

		$AuditPlanUnitExecution = AuditPlanUnitExecution::find()->where(['audit_plan_unit_id'=>$audit_plan_unit_id,'executed_by'=>null])->one();

		$AuditPlanUnitExecutionUser = AuditPlanUnitExecution::find()->where(['status'=>$AuditPlanUnitExecutionModel->arrEnumStatus['open'], 'audit_plan_unit_id'=>$audit_plan_unit_id,'executed_by'=>$userid ])->one();
		
		$showassignsubtopic = 0;
		if($AuditPlanUnitExecution !== null || $AuditPlanUnitExecutionUser !== null){
			$showassignsubtopic = 1;
		}
		return $showassignsubtopic;
	}

	public function actionGetAssignsubtopic(){
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
			$resource_access=$userData['resource_access'];
			
			$connection = Yii::$app->getDb();
			$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();
			$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
			
			
			$condition = '';
			$unit_id = $data['unit_id'];
			$audit_plan_unit_id = $data['audit_plan_unit_id'];
			$audit_id = $data['audit_id'];
			$audit_plan_id = $data['audit_plan_id'];

			$subtopictype = isset($data['subtopictype'])?$data['subtopictype']:'';
			
			$dataArr = [];

			if($subtopictype=='followup_assignSubtopic'){
				/*
				//AuditPlanUnitExecutionFollowup::find()->where(['audit_plan_unit_id'=>$audit_plan_unit_id ])->all();
				$unitLeadAuditor = '';
				$applicationLeadAuditor = '';
				$AuditPlanUnitAuditor = AuditPlanUnitAuditor::find()->where(['is_lead_auditor'=>1,'audit_plan_unit_id'=>$audit_plan_unit_id,'audit_type'=>2])->one();
				if($AuditPlanUnitAuditor !== null){
					$unitLeadAuditor = $AuditPlanUnitAuditor->user_id;
				}
				$AuditPlan = AuditPlan::find()->where(['id'=>$audit_plan_id])->one();
				if($AuditPlan !== null){
					$applicationLeadAuditor = $AuditPlan->followup_application_lead_auditor;
				}
				
				$AuditPlanUnitModel = AuditPlanUnit::find()->where(['id' => $audit_plan_unit_id])->one();
				if($AuditPlanUnitModel !== null){
					if($AuditPlanUnitModel->status == $AuditPlanUnitModel->arrEnumStatus['followup_inprocess']
					|| $AuditPlanUnitModel->status == $AuditPlanUnitModel->arrEnumStatus['followup_open']
					|| $AuditPlanUnitModel->status == $AuditPlanUnitModel->arrEnumStatus['followup_lead_auditor_reinitiated']
					|| $AuditPlanUnitModel->status == $AuditPlanUnitModel->arrEnumStatus['followup_awaiting_unit_lead_auditor_approval']){
						if($userid && $user_type==1 && $resource_access!=1 && $userid != $unitLeadAuditor){
							$condition .= " AND ( execution.executed_by is null OR execution.executed_by=".$userid.")";
						}
					}
					if($AuditPlanUnitModel->status == $AuditPlanUnitModel->arrEnumStatus['followup_awaiting_lead_auditor_approval']
					|| $AuditPlanUnitModel->status == $AuditPlanUnitModel->arrEnumStatus['followup_reviewer_reinitated']){
						if($userid && $user_type==1 && $resource_access!=1 && $userid != $applicationLeadAuditor){
							$condition .= " AND ( execution.executed_by is null OR execution.executed_by=".$userid.")";
						}
					}
				}
				// $userid != $applicationLeadAuditor && $userid!=$unitLeadAuditor &&
				
				$command = $connection->createCommand("SELECT execution.sub_topic_id as id,execution.sub_topic_name as name FROM  
				   `tbl_audit_plan_unit_execution_followup` AS execution  
				LEFT JOIN `tbl_users` AS user on user.id = execution.executed_by  
				WHERE 1=1  ".$condition." AND execution.audit_plan_unit_id=".$audit_plan_unit_id." GROUP BY execution.sub_topic_id");
				$result = $command->queryAll();
				$dataArr = [];
				if(count($result)>0){
					foreach($result as $subdata){
						$dataArr[] =['id'=>$subdata['id'],'name'=>$subdata['name']];
					}
				}
				*/
				$AuditPlanUnitExecutionModel = new AuditPlanUnitExecutionFollowup();
				//$userid
				$AuditPlanUnitExecution = AuditPlanUnitExecutionFollowup::find()->alias('t')->where(['audit_plan_unit_id'=>$audit_plan_unit_id, 'status' => $AuditPlanUnitExecutionModel->arrEnumStatus['open']]);
				$AuditPlanUnitExecution = $AuditPlanUnitExecution->andWhere('  (t.executed_by is null OR t.executed_by='.$userid.') ');
				$AuditPlanUnitExecution = $AuditPlanUnitExecution->all();
				if(count($AuditPlanUnitExecution)>0){
					foreach($AuditPlanUnitExecution as $planunitexe){
						$selected= $planunitexe->executed_by== $userid?1:0;

						$dataArr[] =['id'=>$planunitexe->sub_topic_id,'name'=>$planunitexe->sub_topic_name,'selected'=>$selected];
					}
				}
			}else{
				


				/*
				if($userid && $user_type==1 && $userid != $applicationLeadAuditor && $userid!=$unitLeadAuditor && $resource_access!=1 ){
					$condition .= " AND ( execution.executed_by is null OR execution.executed_by=".$userid.")";
				}
				$command = $connection->createCommand("SELECT execution.sub_topic_id as id,execution.sub_topic_name as name FROM  
				   `tbl_audit_plan_unit_execution` AS execution  
				LEFT JOIN `tbl_users` AS user on user.id = execution.executed_by  
				WHERE 1=1  ".$condition." AND execution.audit_plan_unit_id=".$audit_plan_unit_id." GROUP BY execution.sub_topic_id");
				$result = $command->queryAll();
				$dataArr = [];
				if(count($result)>0){
					foreach($result as $subdata){
						$dataArr[] =['id'=>$subdata['id'],'name'=>$subdata['name']];
					}
				}
				*/
				 
				$AuditPlanUnitExecutionModel = new AuditPlanUnitExecution();
				//$userid
				$AuditPlanUnitExecution = AuditPlanUnitExecution::find()->alias('t')->where(['audit_plan_unit_id'=>$audit_plan_unit_id, 'status' => $AuditPlanUnitExecutionModel->arrEnumStatus['open']]);
				//'executed_by'=>null 
				$AuditPlanUnitExecution = $AuditPlanUnitExecution->andWhere('  (t.executed_by is null OR t.executed_by='.$userid.') ');
				$AuditPlanUnitExecution = $AuditPlanUnitExecution->all();
				if(count($AuditPlanUnitExecution)>0){
					foreach($AuditPlanUnitExecution as $planunitexe){
						$selected= $planunitexe->executed_by== $userid?1:0;
						
						$dataArr[] =['id'=>$planunitexe->sub_topic_id,'name'=>$planunitexe->sub_topic_name,'selected'=>$selected];
					}
				}
			}
			

			$responsedata =['status'=>1,'data'=>$dataArr];
		}

		return $responsedata;

	}

	private function canViewAuditPlan($audit_id){
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];

		
		$canViewAudit = 0;
		$Audit = Audit::find()->where(['id'=>$audit_id])->one();
		if($Audit !== null){
			if( Yii::$app->userrole->isAdmin() || (Yii::$app->userrole->isOSSAdmin() && $Audit->application->franchise_id == $franchiseid) 
			|| (Yii::$app->userrole->isOSS() && $Audit->application->franchise_id == $userid)){
				$canViewAudit = 1;
			}
			if($Audit->status == $Audit->arrEnumStatus['open']){
				if(Yii::$app->userrole->hasRightsWithFranchise(['generate_audit_plan'],$Audit->application->franchise_id)){
					$canViewAudit = 1;
				}
			}else if($Audit->status == $Audit->arrEnumStatus['submitted'] || $Audit->status == $Audit->arrEnumStatus['rejected']){
				if($Audit->created_by == $userid || $Audit->updated_by == $userid){
					$canViewAudit = 1;
				}
			}
			if($Audit->status > $Audit->arrEnumStatus['submitted'] &&  $Audit->status <= $Audit->arrEnumStatus['rejected']){
				if($Audit->created_by == $userid 
				|| $Audit->updated_by == $userid 
				|| $Audit->auditplan->application_lead_auditor == $userid ){
					$canViewAudit = 1;
				}
			}
			if($Audit->status >= $Audit->arrEnumStatus['awaiting_for_customer_approval']){
					
					if($Audit->created_by == $userid || $Audit->updated_by == $userid 
					|| $Audit->auditplan->application_lead_auditor == $userid
					|| (Yii::$app->userrole->isCustomer() && $Audit->application->customer_id == $userid) 
					){
						$canViewAudit = 1;
					}
								
			}
			if($Audit->status == $Audit->arrEnumStatus['approved'] || $Audit->status > $Audit->arrEnumStatus['rejected']){
				$auditplanunit = $Audit->auditplan->auditplanunit;
				$auditorList = [];
				if(count($auditplanunit)>0){
					foreach($auditplanunit as $auditplanunitdata){
						foreach($auditplanunitdata->unitauditors as $unitauditordata){
							$auditorList[] = $unitauditordata->user_id;
						}
						if(count($auditplanunitdata->followupunitauditors)>0){
							foreach($auditplanunitdata->followupunitauditors as $followupunitauditordata){
								$auditorList[] = $followupunitauditordata->user_id;
							}
						}
					}
					
					$auditorList=array_unique($auditorList);
					

					if($Audit->created_by == $userid || $Audit->updated_by == $userid 
					|| $Audit->auditplan->application_lead_auditor == $userid 
					|| in_array($userid,$auditorList)
					|| (Yii::$app->userrole->isCustomer() && $Audit->application->customer_id == $userid) 
					|| (Yii::$app->userrole->isOSSAdmin() && $Audit->application->franchise_id == $franchiseid) 
					|| (Yii::$app->userrole->isOSS() && $Audit->application->franchise_id == $userid) 
					|| Yii::$app->userrole->hasRightsWithFranchise(['audit_review'],$Audit->application->franchise_id)){
						$canViewAudit = 1;
					}
				}				
			}			
		}
		return $canViewAudit;
	}

	private function canCreateAuditPlan($audit_id){
		//(offer.audit_status==auditStatus['open'] || offer.audit_status==auditStatus['submitted'] || offer.audit_status==auditStatus['rejected']) 
		//&& offer.audit_type == 1 && (userdetails.resource_access==1 || (userType==1 && userdetails.rules.includes('generate_audit_plan')) )
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];

		
		$canCreateAudit = 0;
		$Audit = Audit::find()->where(['id'=>$audit_id])->one();
		if($Audit !== null){
			if($Audit->status == $Audit->arrEnumStatus['open']){
				if(Yii::$app->userrole->hasRightsWithFranchise(['generate_audit_plan'],$Audit->application->franchise_id)){
					$canCreateAudit = 1;
				}
			}else if($Audit->status == $Audit->arrEnumStatus['submitted'] || $Audit->status == $Audit->arrEnumStatus['rejected']
			|| $Audit->status == $Audit->arrEnumStatus['followup_open'] || $Audit->status == $Audit->arrEnumStatus['followup_rejected_by_customer']){
				if(Yii::$app->userrole->isAdmin() || $Audit->created_by == $userid || $Audit->updated_by == $userid){
					$canCreateAudit = 1;
				}
			}
			/*else if($Audit->status == $Audit->arrEnumStatus['followup_open']){
				if(Yii::$app->userrole->hasRightsWithFranchise(['generate_audit_plan'],$Audit->application->franchise_id)){
					$canCreateAudit = 1;
				}
			}else if($Audit->status == $Audit->arrEnumStatus['followup_rejected_by_customer']){
				if(Yii::$app->userrole->hasRightsWithFranchise(['generate_audit_plan'],$Audit->application->franchise_id)){
					$canCreateAudit = 1;
				}
			}
			*/
		}
		return $canCreateAudit;
	}
}