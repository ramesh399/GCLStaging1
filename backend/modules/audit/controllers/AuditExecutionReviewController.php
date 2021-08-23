<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditPlan;
use app\modules\master\models\MailLookup;
use app\modules\audit\models\AuditPlanUnit;
use app\modules\audit\models\AuditPlanReview;
use app\modules\audit\models\AuditPlanReviewer;
use app\modules\master\models\SubTopic;
use app\modules\master\models\MailNotifications;
use app\modules\application\models\ApplicationUnit;
use app\modules\audit\models\AuditPlanUnitExecution;
use app\modules\master\models\AuditPlanningQuestions;
use app\modules\master\models\AuditNonConformityTimeline;
use app\modules\audit\models\AuditPlanReviewChecklistComment;
use app\modules\audit\models\AuditPlanUnitExecutionChecklist;
use app\modules\audit\models\AuditPlanExecutionChecklistReview;
use app\modules\audit\models\AuditPlanUnitExecutionChecklistReviewerHistroy;
use app\modules\audit\models\AuditPlanUnitExecutionChecklistReviewerNotes;
use app\modules\audit\models\AuditPlanUnitReviewChecklistComment;
use app\modules\audit\models\AuditPlanExecutionChecklistReviewComment;
use app\modules\audit\models\AuditPlanUnitExecutionChecklistRemediationReview;
use app\modules\audit\models\AuditPlanUnitExecutionChecklistRemediationApproval;
use app\modules\audit\models\AuditPlanUnitFollowupRemediationReview;
use app\modules\audit\models\AuditPlanUnitExecutionFollowup;
use app\modules\audit\models\AuditPlanUnitAuditor;

use app\modules\master\models\AuditExecutionQuestion;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class AuditExecutionReviewController extends \yii\rest\Controller
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
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again later');
		
		return $this->asJson($responsedata);
	}
	

	public function actionView()
	{
		$responsedata=array('status'=>0,'message'=>'Review data not found');
		
		return $responsedata;
	}

	public function actionIndex()
	{
		$responsedata=array('status'=>0,'message'=>'Review data not found');
		
		return $responsedata;
	}

	public function actionGetapplicationdetails()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		
		if($data)
		{
			$userData = Yii::$app->userdata->getData();
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$franchiseid=$userData['franchiseid'];
			$is_headquarters =$userData['is_headquarters'];
			$resource_access=$userData['resource_access'];
			
			//$dataVal =json_decode($data['formvalues'],true);
			//print_r($_FILES);
			//print_r($dataVal); die;
			//AuditPlanUnitExecutionChecklist
			$AuditPlanUnitExecutionChecklist = new AuditPlanUnitExecutionChecklist();
			$AuditPlan = new AuditPlan();
			$AuditPlan = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();//('id'=>'');
			if($AuditPlan!== null){
				$followup_auditors = [];
				$unit_auditors = [];

				$followup_unit_lead_auditor = '';
				$unit_lead_auditor = '';

				$AuditPlanUnit = AuditPlanUnit::find()->where(['id' => $data['audit_plan_unit_id']])->one(); 
				$data['unit_status'] = $AuditPlanUnit->status;
				$data['arrUnitEnumStatus'] = $AuditPlanUnit->arrEnumStatus;
				//otherData.unit_status == 10 || otherData.unit_status == 11 || otherData.unit_status == 12 || otherData.unit_status == 13

				$data['can_unit_auditor_edit'] = 0;
				if($AuditPlanUnit->status == $AuditPlanUnit->arrEnumStatus['followup_open'] ||
					$AuditPlanUnit->status == $AuditPlanUnit->arrEnumStatus['followup_inprocess'] 
					|| $AuditPlanUnit->status == $AuditPlanUnit->arrEnumStatus['followup_lead_auditor_reinitiated']
					|| $AuditPlanUnit->status == $AuditPlanUnit->arrEnumStatus['followup_awaiting_unit_lead_auditor_approval']
				){
					$data['can_unit_auditor_edit'] = 1;//$AuditPlanUnit->status;
				}
				$AuditPlanUnitAuditor = AuditPlanUnitAuditor::find()->where(['audit_plan_unit_id'=>$data['audit_plan_unit_id']])->all();
				if(count($AuditPlanUnitAuditor )>0){
					foreach($AuditPlanUnitAuditor as $followauditor){
						if($followauditor->audit_type == 2){
							if($followauditor->is_lead_auditor==1){
								$followup_unit_lead_auditor = $followauditor->user_id;
							}
							$followup_auditors[] = $followauditor->user_id;
						}else{
							if($followauditor->is_lead_auditor==1){
								$unit_lead_auditor = $followauditor->user_id;
							}
							$unit_auditors[] = $followauditor->user_id;
						}
						
					}
				}
				$data['unit_auditors'] = $unit_auditors;
				$data['followup_auditors'] = $followup_auditors;
				$data['followup_unit_lead_auditor'] = $followup_unit_lead_auditor;


				$AuditPlanReviewer = AuditPlanReviewer::find()->where(['audit_plan_id'=>$data['audit_plan_id'],'reviewer_status'=>1 ])->one();
				if($AuditPlanReviewer !== null ){
					$data['audit_reviewer'] = $AuditPlanReviewer->reviewer_id;
				}

				$Audit = Audit::find()->where(['id'=>$AuditPlan->audit_id ])->one();
				$data['audit_status'] = $Audit->status;
				$data['arrAuditStatusList'] = $Audit->arrEnumStatus;

				$data['audit_lead_auditor_id'] = $AuditPlan->application_lead_auditor;
				$data['followup_audit_lead_auditor_id'] = $AuditPlan->followup_application_lead_auditor;

				$data['arrChecklistEnumStatus'] = $AuditPlanUnitExecutionChecklist->arrEnumStatus;
				$data['arrAuditPlanEnumStatus'] = $AuditPlan->arrEnumStatus;
				$data['arrReviewerStatusList'] = $AuditPlan->arrReviewerStatusList;
				$data['arrAuditorStatusList'] = $AuditPlan->arrAuditorStatusList;

				$data['arrFollowupAuditorStatusList'] = $AuditPlan->arrFollowupAuditorStatusList;
				$data['arrFollowupReviewerStatusList'] = $AuditPlan->arrFollowupReviewerStatusList;
				$data['arrFollowupLeadAuditorStatusList'] = $AuditPlan->arrFollowupLeadAuditorStatusList;
				
				//$data['audit_lead_auditor_id'] 
				$responsedata=array('status'=>1,'data'=>$data);
			}

		}
		return $responsedata;
	}

	public function actionAuditExecutionReview()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		// echo "Hi inside";
		if($data)
		{
			
			$userData = Yii::$app->userdata->getData();
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$franchiseid=$userData['franchiseid'];
			$is_headquarters =$userData['is_headquarters'];
			$resource_access=$userData['resource_access'];
			
			$dataVal =json_decode($data['formvalues'],true);
			// print_r($_FILES);
			//print_r($dataVal); die;
			$AuditPlanUnit = new AuditPlanUnit();
			$AuditPlan = new AuditPlan();
			$AuditPlanUnitExecutionStatusModel = new AuditPlanUnitExecution();
			
			if(!Yii::$app->userrole->isAuditReviewer($dataVal['audit_plan_id']))
			{
				return $responsedata;
			}
			
			$planModel = new AuditPlan();
			$auditPlanModel = AuditPlan::find()->where(['id' => $dataVal['audit_plan_id'],'status'=>array($planModel->arrEnumStatus['review_in_progress'],$planModel->arrEnumStatus['review_completed'])])->one();
			if($auditPlanModel===null)
			{
				return $responsedata;				
			}

			$AuditPlanUnitExecutionModel = AuditPlanExecutionChecklistReview::find()->where(['audit_plan_id'=>$dataVal['audit_plan_id']])->one();
			//$AuditPlanUnitExecutionModel; die;

			if($AuditPlanUnitExecutionModel=== null){

				$AuditPlanUnitExecutionModel = new AuditPlanExecutionChecklistReview();
				$AuditPlanUnitExecutionModel->audit_plan_id = $dataVal['audit_plan_id'];
				$AuditPlanUnitExecutionModel->created_at = time();
				$AuditPlanUnitExecutionModel->created_by = $userData['userid'];
			}
			$AuditPlanUnitExecutionModel->reviewer_id = $userData['userid'];
			//print_r($AuditPlanUnitExecutionModel->validate()); die;

			$auditreviewerhistory = AuditPlanUnitExecutionChecklistReviewerHistroy::find()->where(['audit_id'=>$dataVal['audit_id']])->all();

			if(count($auditreviewerhistory)>0){
				$lastIndex = count($auditreviewerhistory)-1;
				$review_stage = $auditreviewerhistory[$lastIndex]->review_stage;
			}
			
            if($AuditPlanUnitExecutionModel->validate() && $AuditPlanUnitExecutionModel->save())
        	{
				$timelinemodel = AuditNonConformityTimeline::find()->select(['id','timeline'])->all();
				$arrTimeLine = [];
				if(count($timelinemodel)>0){
					foreach($timelinemodel as $timeline){
						$arrTimeLine[$timeline->id] = $timeline->timeline;
					}
				}
				$AuditPlanData = AuditPlan::find()->where(['id'=>$dataVal['audit_plan_id']])->one();
				$audit_completed_date = $AuditPlanData->audit_completed_date;


				$auditPlanUnitExecutionID = $AuditPlanUnitExecutionModel->id;
				$target_dir = Yii::$app->params['user_qualification_review_files']; 
				$qts = $dataVal['questions'];
				if(is_array($qts) && count($qts)>0)
				{
					
					$reviewerAnswerNoList = [];
					foreach($qts as $unitqts)
					{
						//print_r($unitqts['questions']); die;
						foreach($unitqts['questions'] as $question)
						{
							$execution_checklist_id = $question['execution_checklist_id'];
							$AuditPlanUnitExecutionChecklistModel = AuditPlanUnitExecutionChecklist::find()->where(['id'=>$execution_checklist_id])->one();
							if($AuditPlanUnitExecutionChecklistModel!== null){

								$findingType = isset($question['findingType'])?$question['findingType']:'';

								//$AuditPlanUnitExecutionChecklistModel->audit_plan_unit_execution_id = $auditPlanUnitExecutionID;
								//$AuditPlanUnitExecutionChecklistModel->audit_plan_unit_id = $data['audit_plan_unit_id'];
								//$AuditPlanUnitExecutionChecklistModel->user_id = $userData['userid'];
								//$AuditPlanUnitExecutionChecklistModel->unit_id = $dataVal['unit_id'];
								
								$AuditPlanUnitExecutionChecklistModel->answer = $question['answer'];
								$AuditPlanUnitExecutionChecklistModel->finding = $question['findings'];
								$due_date = '';
								/*
								if($findingType && $AuditPlanUnitExecutionChecklistModel->answer==2){
									$duedays = $arrTimeLine[$findingType];
									$audit_completed_date = $AuditPlanData->audit_completed_date;
									$due_date = date('Y-m-d', strtotime($audit_completed_date. ' + '.$duedays.' days'));
								}
								*/
								
								if($AuditPlanUnitExecutionChecklistModel->answer==2)
								{
									$severity = isset($question['severity'])?$question['severity']:'';
									$AuditPlanUnitExecutionChecklistModel->finding_type = $findingType;
									$AuditPlanUnitExecutionChecklistModel->severity = $severity;
									if($severity && isset($arrTimeLine[$severity])){
										$duedays = $arrTimeLine[$severity];
										$due_date = date('Y-m-d', strtotime($audit_completed_date. ' + '.$duedays.' days'));
										$AuditPlanUnitExecutionChecklistModel->due_date = $due_date;
									}
									

								}else{
									$AuditPlanUnitExecutionChecklistModel->severity = '';
									$AuditPlanUnitExecutionChecklistModel->finding_type = '';
								}
								
								//$AuditPlanUnitExecutionChecklistModel->question = $question['question'];
								//$AuditPlanUnitExecutionChecklistModel->question_id = $question['question_id'];
								
								// -----------------File Upload Code Start Here ------------------
								if(isset($_FILES['questionfile']['name'][$unitqts['unit_id'].'_'.$question['question_id']]))
								{								
									
									if($AuditPlanUnitExecutionChecklistModel!==null && $AuditPlanUnitExecutionChecklistModel->file!='')
									{
										Yii::$app->globalfuns->removeFiles($AuditPlanUnitExecutionChecklistModel->file,$target_dir);							
									}

									$tmp_name = $_FILES['questionfile']["tmp_name"][$unitqts['unit_id'].'_'.$question['question_id']];
									$name = $_FILES['questionfile']['name'][$unitqts['unit_id'].'_'.$question['question_id']];
						   			
									$AuditPlanUnitExecutionChecklistModel->file = Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);

									/*
									$target_file = $target_dir . basename($filename);
									$actual_name = pathinfo($filename,PATHINFO_FILENAME);
									$original_name = $actual_name;
									$extension = pathinfo($filename, PATHINFO_EXTENSION);
									$name = $actual_name.".".$extension;
									$i = 1;
									while(file_exists($target_dir.$actual_name.".".$extension))
									{           
										$actual_name = (string)$original_name.$i;
										$name = $actual_name.".".$extension;
										$i++;
									}
									
									if (move_uploaded_file($_FILES['questionfile']["tmp_name"][$unitqts['unit_id'].'_'.$question['question_id']], $target_dir.$name)) 
									{
										$AuditPlanUnitExecutionChecklistModel->file = isset($name)?$name:"";
									}
									*/
								}else if(isset($question['file']) && $question['file']!=''){
									$AuditPlanUnitExecutionChecklistModel->file = $question['file'];
								}
								// -----------------File Upload Code End Here ------------------
								
								$AuditPlanUnitExecutionChecklistModel->save();

								
								if($question['revieweranswer'] == 2){
									$audit_plan_unit_execution_id = $AuditPlanUnitExecutionChecklistModel->audit_plan_unit_execution_id;
									$reviewerAnswerNoList[$audit_plan_unit_execution_id] = $audit_plan_unit_execution_id;

									 // -----------------------Reviewer Histroy--------------------------
									 $appmodel =ApplicationUnit::find()->where(['id'=>$question['unit_id']])->one();

									

									 $subtopicmodel = SubTopic::find()->where(['id'=> $question['sub_topic_id']])->one();
									 $ReviewerModel = new AuditPlanUnitExecutionChecklistReviewerHistroy();

									 
 
									 if (count($auditreviewerhistory)>0){
										 
										 $ReviewerModel->audit_id = $dataVal['audit_id'];
										 $ReviewerModel->question = $question['question'];
										 $ReviewerModel->answer = $question['revieweranswer'];
										 $ReviewerModel->comment = $question['reviewercomment'];
										 $ReviewerModel->created_at = date($date_format);
										 $ReviewerModel->sub_topic = $subtopicmodel['name'];
										 $ReviewerModel->review_stage = $review_stage+1;
										 $ReviewerModel->unit_name = $appmodel['name'];
										 //echo $review_stage;
										 $ReviewerModel->save();
									 }
									 else{

										 $ReviewerModel->audit_id = $dataVal['audit_id'];
										 $ReviewerModel->question = $question['question'];
										 $ReviewerModel->answer = $question['revieweranswer'];
										 $ReviewerModel->comment = $question['reviewercomment'];
										 $ReviewerModel->created_at = date($date_format);
										 $ReviewerModel->sub_topic = $subtopicmodel['name'];
										 $ReviewerModel->review_stage = 0;
										 $ReviewerModel->unit_name =  $appmodel['name'];
										 $ReviewerModel->save();
									 }
								}


								$AuditReviewerChecklistModel = AuditPlanExecutionChecklistReviewComment::find()->where(['audit_plan_execution_checklist_review_id'=>$AuditPlanUnitExecutionModel->id
								,'question_id'=>$question['question_id'],'execution_checklist_id'=>$AuditPlanUnitExecutionChecklistModel->id ])->one();
								if($AuditReviewerChecklistModel === null){
									$AuditReviewerChecklistModel = new AuditPlanExecutionChecklistReviewComment();
									$AuditReviewerChecklistModel->audit_plan_execution_checklist_review_id = $AuditPlanUnitExecutionModel->id;
								}
								$AuditReviewerChecklistModel->answer = $question['revieweranswer'];
								$AuditReviewerChecklistModel->comment = $question['reviewercomment'];
								$AuditReviewerChecklistModel->finding_type = isset($question['findingType'])?$question['findingType']:'';
								$AuditReviewerChecklistModel->question_id = $question['question_id'];
								$AuditReviewerChecklistModel->execution_checklist_id = $AuditPlanUnitExecutionChecklistModel->id;
								$AuditReviewerChecklistModel->save();
							}
							
							
							

							


						}
					}
					if(count($reviewerAnswerNoList)>0 && $dataVal['actiontype'] == 'reportcorrection'){
						foreach($reviewerAnswerNoList as $audit_plan_unit_execution_id){
							$auditplanunitexecution = AuditPlanUnitExecution::find()->where(['id'=>$audit_plan_unit_execution_id,'status'=>$AuditPlanUnitExecutionStatusModel->arrEnumStatus['completed'] ])->one();
							if($auditplanunitexecution !== null){
								$auditplanunitexecution->status = $AuditPlanUnitExecutionStatusModel->arrEnumStatus['reintiate'];
								$auditplanunitexecution->save();

								$audit_plan_unit_id = $auditplanunitexecution->audit_plan_unit_id;
								$auditplanunit = AuditPlanUnit::find()->where(['id'=>$audit_plan_unit_id])->one();
								if($auditplanunit !==null){
									$auditplanunit->status = $auditplanunit->arrEnumStatus['reviewer_reinititated'];
									$auditplanunit->save();


									$auditplan = AuditPlan::find()->where(['id'=>$auditplanunit->audit_plan_id])->one();
									$auditplan->status = $auditplan->arrEnumStatus['reviewer_reinitiated'];
									$auditplan->save();

								}


							}
						}

						//--------------------------Reviewer Notes---------------------

						$reviewernotes = isset($dataVal['note'])?$dataVal['note']:'';
						//echo $reviewernotes;
						$reviewernotemodel = new AuditPlanUnitExecutionChecklistReviewerNotes();

						if(count($auditreviewerhistory)>0){
							$stageVal = $review_stage+1;
						}
						else{
							$stageVal = 0;
						}

						if($reviewernotes!==null && $reviewernotes!=''){
							
							$reviewernotemodel->audit_id = $dataVal['audit_id'];
							$reviewernotemodel->notes = $reviewernotes;
							$reviewernotemodel->stage = $stageVal;
							$reviewernotemodel->save();
						}

					}else{
						$auditplanmodel = AuditPlan::find()->where(['id'=>$dataVal['audit_plan_id']])->one();
						$auditplanmodel->status = $auditplanmodel->arrEnumStatus['review_completed'];
						$auditplanmodel->save();

						$auditplanunitmodel = AuditPlanUnit::find()->where(['audit_plan_id'=>$dataVal['audit_plan_id'],'status'=>$AuditPlanUnit->arrEnumStatus['awaiting_for_reviewer_approval'] ])->all();
						if(count($auditplanunitmodel) >0){
							foreach($auditplanunitmodel as $planunitdata){
								$planunitdata->status = $planunitdata->arrEnumStatus['review_completed'];
								$planunitdata->save();
							}
						}
					}				
				}
			}


			

			//echo 'df';
			/*
			$rcompleted_status = $AuditPlanUnit->arrEnumStatus['review_completed'];

			$auditplanmodel = AuditPlanUnit::find()->where(['id'=>$dataVal['audit_plan_unit_id']])->one();
			$auditplanmodel->status_change_date = time();
			$auditplanmodel->status = $rcompleted_status;
			$auditplanmodel->save();
			
			
			//AuditPlanUnit
			$auditplanunit_rev_not = AuditPlanUnit::find()->where(['<>','status',$rcompleted_status]);
			$auditplanunit_rev_not = $auditplanunit_rev_not->andWhere('audit_plan_id="'.$dataVal['audit_plan_id'].'"');
			$auditplanunit_rev_not = $auditplanunit_rev_not->one();
			if($auditplanunit_rev_not ===null){
				$auditplanmodel = AuditPlan::find()->where(['id'=>$dataVal['audit_plan_id']])->one();
				$auditplanmodel->status = $AuditPlan->arrEnumStatus['review_completed'];
				$auditplanmodel->save();
			}else{
				$auditplanunit_rev = AuditPlanUnit::find()->where(['status'=>$rcompleted_status])->one();
				if($auditplanunit_rev !== null){
					$auditplanmodel = AuditPlan::find()->where(['id'=>$dataVal['audit_plan_id']])->one();
					$auditplanmodel->status = $AuditPlan->arrEnumStatus['in_progress'];
					$auditplanmodel->save();
				}
				
			}
			*/

			$responsedata=array('status'=>1,'message'=>'Saved successfully');
		}
		return $responsedata;
	}

	public function actionReviewerHistroy(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$post = Yii::$app->request->post();
        if($post){
			$ID = $post['id'];
			$reviewerHisList =[];
			$reviewerHistoryModel = AuditPlanUnitExecutionChecklistReviewerHistroy::find()->where(['audit_id'=>$ID])->all();
			if(count($reviewerHistoryModel)>0)
			{
				if(count($reviewerHistoryModel)>0){
					foreach ($reviewerHistoryModel as $reviewer){
	
						$reviewerHistroyData = [];
						$reviewerHistroyData['auditId']=$reviewer['audit_id'];
						$reviewerHistroyData['questions']=$reviewer['question'];
						$reviewerHistroyData['answer']=$reviewer['answer'];
						$reviewerHistroyData['comment']=$reviewer['comment'];
						$reviewerHistroyData['created_at']=$reviewer['created_at'];
						$reviewerHistroyData['sub_topic']=$reviewer['sub_topic'];
						$reviewerHistroyData['review_stage']=$reviewer['review_stage'];
						$reviewerHistroyData['unit_name']=$reviewer['unit_name'];
						$reviewerHisList[]=$reviewerHistroyData;
					}
	
				$responsedata = array();
				$responsedata['status'] = 1;
				$responsedata['reviewerhistroy']=$reviewerHisList;
	
				}
				
			}
			else{
				return $responsedata;	
			}	

			$reviewernotesdatamodel = AuditPlanUnitExecutionChecklistReviewerNotes::find()->select(['notes','stage'])->where(['audit_id'=>$ID])->asArray()->all();

			$notesData = [];
			$notesList = [];
			if(count($reviewernotesdatamodel)>0){
				foreach($reviewernotesdatamodel as $notes){
				 $notesData['notes'] = $notes['notes'];
				 $notesData['stage'] = $notes['stage'];
				 $notesList[] = $notesData;
			}
			
			$responsedata['notesdata'] = $notesList;
		  }
		}
		return $responsedata;
	}
	

	public function actionGetQuestions()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$post = Yii::$app->request->post();
		if($post)
		{
			$userData = Yii::$app->userdata->getData();
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$franchiseid=$userData['franchiseid'];
			$is_headquarters =$userData['is_headquarters'];
			$resource_access=$userData['resource_access'];
			
			$auditmodel =new Audit();
			$arrFindingType = $auditmodel->arrFindingType;

			$connection = Yii::$app->getDb();
			
			$arrAuditNonConformity=array();
			$arrAuditNonConformityTimeline = AuditNonConformityTimeline::find()->select(['id','name'])->asArray()->all();
			if(count($arrAuditNonConformityTimeline)>0)
			{
				foreach($arrAuditNonConformityTimeline as $nonConformity)
				{
					$arrAuditNonConformity[$nonConformity['id']]=$nonConformity['name'];
				}
			}
			$audit_plan_id = $post['audit_plan_id'];
			$unit_id = isset($post['unit_id'])?:'';
			$audit_plan_unit_id = '';
			$AuditPlanUnitModel = AuditPlanUnit::find()->where(['audit_plan_id'=>$audit_plan_id, 'unit_id'=>$unit_id])->one();
			if($AuditPlanUnitModel !== null){
				$audit_plan_unit_id = $AuditPlanUnitModel->id;
			}
			$exequesmodel = new AuditExecutionQuestion();
			$condition = "";
			if($audit_plan_id !=''){
		    	$condition .= " AND planunit.audit_plan_id=".$audit_plan_id." ";
			}
			if($unit_id !=''){
				$unitID = $post['unit_id'];
				$condition .= " AND planunit_exe_checklist.unit_id =".$unitID." AND planunit.unit_id=".$unitID."";
			}
			if($audit_plan_unit_id !=''){
				$condition .= " AND planunit_exe_checklist.audit_plan_unit_id ='".$audit_plan_unit_id."' AND planunit.id='".$audit_plan_unit_id."' ";
			}
			
			$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
			$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();

			/*
					BEFORE WHERE CONDITION

			LEFT JOIN `tbl_audit_plan_execution_checklist_review` AS checklist_review ON checklist_review.audit_plan_id = planunit.audit_plan_id 
			LEFT JOIN `tbl_audit_plan_execution_checklist_review_comment` as reviewer_comment ON reviewer_comment.question_id=aeq.id 
			AND checklist_review.id = reviewer_comment.audit_plan_execution_checklist_review_id AND 
			reviewer_comment.execution_checklist_id = planunit_exe_checklist.id 
			*/


			$executionChecklistQuery = "SELECT  planunit.unit_id, null as findingType, null as reviewercomment, null as revieweranswer,
			planunit_exe_checklist.id as execution_checklist_id,planunit_exe_checklist.file,planunit_exe_checklist.finding, 
			planunit_exe_checklist.severity,planunit_exe_checklist.answer, aeq.*, 
			GROUP_CONCAT(DISTINCT aeqnc.audit_non_conformity_timeline_id SEPARATOR '@') AS non_conformity,GROUP_CONCAT(DISTINCT aeqf.question_finding_id SEPARATOR '@') AS question_findings 
			FROM `tbl_audit_plan_unit`AS planunit 
			INNER JOIN `tbl_audit_plan_unit_execution`AS planunit_exe ON planunit.id=planunit_exe.audit_plan_unit_id  
			INNER JOIN `tbl_audit_plan_unit_execution_checklist`AS planunit_exe_checklist ON planunit_exe.id=planunit_exe_checklist.audit_plan_unit_execution_id 
			INNER JOIN `tbl_audit_execution_question` AS aeq ON aeq.id=planunit_exe_checklist.question_id  
			INNER JOIN `tbl_audit_execution_question_non_conformity`AS aeqnc ON aeq.id=aeqnc.audit_execution_question_id 
			INNER JOIN `tbl_audit_execution_question_findings` as aeqf ON aeq.id=aeqf.audit_execution_question_id 
			WHERE 1=1 ".$condition." and aeq.status=0 
			GROUP BY aeq.id,planunit.unit_id";
			
			$command = $connection->createCommand($executionChecklistQuery);
			$result = $command->queryAll();
			$checklistDataArr = [];
			$unitQuestions = [];
		 
			if(count($result)>0)
			{
				foreach($result as $checklistData)
				{
					$checklistArr = [];
					$checklistArr['findingType'] = $checklistData['findingType'];
					$checklistArr['reviewercomment'] = $checklistData['reviewercomment'];
					$checklistArr['revieweranswer'] = $checklistData['revieweranswer'];


					$checklistArr['file'] = $checklistData['file'];
					$checklistArr['finding'] = $checklistData['finding'];
					$checklistArr['severity'] = $checklistData['severity'];
					$checklistArr['answer'] = $checklistData['answer'];
					$checklistArr['execution_checklist_id'] = $checklistData['execution_checklist_id'];
					

					$checklistArr['id'] = $checklistData['id'];
					$checklistArr['sub_topic_id'] = $checklistData['sub_topic_id'];
					$checklistArr['name'] = $checklistData['name'];
					$checklistArr['interpretation'] = $checklistData['interpretation'];
					$checklistArr['expected_evidence'] = $checklistData['expected_evidence'];
					
					$checklistNonConformityArray=array();
					if($checklistData['non_conformity']!='')
					{
						$arrNonCon=explode('@',$checklistData['non_conformity']);
						if(count($arrNonCon)>0)
						{
							foreach($arrNonCon as $nonCon)
							{
								$checklistNonConformityArray[$nonCon]=$arrAuditNonConformity[$nonCon];
							}
						}
					}

					$checklistFindingArray=array();
					if($checklistData['question_findings']!='')
					{
						$arrFindAnswer=explode('@',$checklistData['question_findings']);
						if(count($arrFindAnswer)>0)
						{
							foreach($arrFindAnswer as $findAns)
							{
								$checklistFindingArray[$findAns]=$exequesmodel->arrFindingAnswer[$findAns];
							}
						}
					}
					
					$checklistArr['findingans_list'] = $checklistFindingArray;
					
					$checklistArr['answer_list'] = $checklistNonConformityArray;
					$checklistArr['file_required'] = $checklistData['file_upload_required'];					
					$checklistArr['yes_comment'] = $checklistData['positive_finding_default_comment'];
					$checklistArr['no_comment'] = $checklistData['negative_finding_default_comment'];
					$checklistDataArr[$checklistData['unit_id']][]=$checklistArr;					
				}
				foreach($checklistDataArr as $unitID => $questions){
					$applicationunit = ApplicationUnit::find()->where(['id'=>$unitID])->one();
					$standard_code = [];
					if(count($applicationunit->unitappstandard)>0){
						foreach($applicationunit->unitappstandard as $standard){
							$standard_code[] = $standard->standard->code;
						}
					}
			
					if($applicationunit !==null){
						$reviewDataset = null;
						$execReviewCommentQuery ="SELECT 
						question_id as questionID,
						finding_type as findingType,
						comment as reviewercomment,
						answer as revieweranswer
						FROM tbl_audit_plan_execution_checklist_review_comment  where execution_checklist_id in (
						SELECT
							tbl_audit_plan_unit_execution_checklist.id 
						FROM tbl_audit_plan_unit_execution_checklist
						WHERE tbl_audit_plan_unit_execution_checklist.audit_plan_unit_id IN (SELECT
							tbl_audit_plan_unit.id
							FROM tbl_audit_plan_unit
						WHERE tbl_audit_plan_unit.audit_plan_id = $audit_plan_id and tbl_audit_plan_unit.unit_id = $applicationunit->id ) )";
							$cmd = $connection->createCommand($execReviewCommentQuery);
							$reviewDataset = $cmd->queryAll();
							if ($reviewDataset != null and count($reviewDataset) > 0 )
							{
								foreach($reviewDataset as $reviewQuestion => $reviewData){
									foreach($questions as $question => $data)
									{
									if($reviewData["questionID"] === $data["id"])
										{
											$questions[$question]['findingType'] = $reviewData['findingType'];
											$questions[$question]['reviewercomment'] = $reviewData['reviewercomment'];
											$questions[$question]['revieweranswer'] =  $reviewData['revieweranswer'];
										}	
									}
								}
							}

						$unitQuestions[] = [
							'unit_id' => $applicationunit->id,							 
							'unit_name' => $applicationunit->name,
							'unit_type_name' => $applicationunit->unit_type_list[$applicationunit->unit_type],
							'unit_standards' => $standard_code,
							'questions'=>$questions
						];
					}

				
				}
			}
			
			$responsedata=array();
			$responsedata['answerList']=array('1'=>'Yes','2'=>'No');
			$responsedata['questionList']=$unitQuestions;
			$responsedata['findingTypeList']=$arrFindingType;
					
		}
		return $responsedata;
	}
	

	/*
	Done by Reviewer - Approve/Change Request

	Approve => lead auditor
	Change Request => Customer 
	*/
	public function actionReviewerCustomerReview(){
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			
			$exemodel = new AuditPlanUnitExecutionChecklist();
			$auditunitmodel = new AuditPlanUnit();
			$auditPlanmodel = new AuditPlan();

			$model = AuditPlanUnitExecutionChecklist::find()->where(['id' => $data['finding_id']])->one();
			if ($model !== null)
			{
				//audit_completed
				if($data['status']==2){
					$model->status = $exemodel->arrEnumStatus['reviewer_change_request'];
				}else if($data['status']==1){
					$model->status = $exemodel->arrEnumStatus['settled'];
					$model->reviewer_close_date = date('Y-m-d');
				}
				
				$remediationreviewermodal = new AuditPlanUnitExecutionChecklistRemediationReview();
				$remediationreviewermodal->audit_plan_unit_execution_checklist_id = $model->id;
				$remediationreviewermodal->checklist_remediation_id = $model->checklistremediationlatest->id;
				$remediationreviewermodal->status = $data['status'];
				$remediationreviewermodal->comment = $data['comment'];
				$remediationreviewermodal->created_at = time();
				$remediationreviewermodal->created_by = $userid;
				$remediationreviewermodal->save();
												
				//,'change_request'=>2, 'waiting_for_approval'=>3,
				if($model->save())
				{

					if($data['status']==1){

						$unit_id = $data['unit_id'];


						$auditplanunit = AuditPlanUnit::find()->where(['audit_plan_id'=>$data['audit_plan_id'],'unit_id'=>$data['unit_id'] ])->one();
						if($auditplanunit !== null){

							$AuditPlanUnitExecutionChecklist = AuditPlanUnitExecutionChecklist::find()->where(['audit_plan_unit_id' => $auditplanunit->id,'answer'=>2]);
							$AuditPlanUnitExecutionChecklist = $AuditPlanUnitExecutionChecklist->andWhere('status!='.$model->arrEnumStatus['settled'])->one();
							
							if($AuditPlanUnitExecutionChecklist === null){
								//$auditplanunit = AuditPlanUnit::find()->where(['audit_plan_id'=>$data['audit_plan_id'],'unit_id'=>$data['unit_id'] ])->one();
								
								//if($auditplanunit !== null){
								$auditplanunit->status = $auditplanunit->arrEnumStatus['remediation_completed'];
								$auditplanunit->save();
								


								$auditplanunitdata = AuditPlanUnit::find()->where(['audit_plan_id'=>$data['audit_plan_id']]);
								$auditplanunitdata = $auditplanunitdata->andWhere('status!='.$auditplanunit->arrEnumStatus['remediation_completed'])->one();
								if($auditplanunitdata === null){
									$auditplan = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
									if($auditplan !== null){
										$auditplan->status = $auditplan->arrEnumStatus['remediation_completed'];
										$auditplan->save();


										
										$auditplandata = AuditPlan::find()->where(['id'=>$data['audit_plan_id']]);
										$auditplandata = $auditplandata->andWhere('status!='.$auditplan->arrEnumStatus['remediation_completed'])->one();
										if($auditplandata === null){
											$audit = Audit::find()->where(['id'=>$data['audit_id']])->one();
											if($audit !== null){
												$audit->status = $audit->arrEnumStatus['remediation_completed'];
												$audit->save();
											}
										}
									}
								}
									
								//}
								
							}
						}
					}


					//echo 'ss';
					$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'remediation_mail_to_lead_auditor'])->one();
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
								$MailLookupModel->bcc='';
								$MailLookupModel->subject=$mailContent['subject'];
								$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
								$MailLookupModel->attachment='';
								$MailLookupModel->mail_notification_id='';
								$MailLookupModel->mail_notification_code='';
								$Mailres=$MailLookupModel->sendMail();
							}
						}
						
					}
					//echo '22';
					$responsedata = ['status'=>1,'message'=>'Finding updated Successfully','data'=>['finding_id'=>$data['finding_id'],'finding_status'=>$model->status]];
				}
					
			}
		}
		return $responsedata;
	}
	
	/*
	Done by Auditor - Approve/Change Request

	Approve => Reviewer
	Change Request => Reviewer 
	*/
	public function actionAuditorFindingReview(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			
			$exemodel = new AuditPlanUnitExecutionChecklist();
			$auditunitmodel = new AuditPlanUnit();
			$auditPlanmodel = new AuditPlan();

			$model = AuditPlanUnitExecutionChecklist::find()->where(['id' => $data['finding_id']])->one();
			if ($model !== null)
			{
				if($data['status']==2){
					$model->status = $exemodel->arrEnumStatus['auditor_change_request'];
				}else if($data['status']==1){
					$model->status = $exemodel->arrEnumStatus['waiting_for_approval'];
				}

				$remediationlauditormodal = new AuditPlanUnitExecutionChecklistRemediationApproval();
				$remediationlauditormodal->audit_plan_unit_execution_checklist_id = $model->id;
				$remediationlauditormodal->checklist_remediation_id = $model->checklistremediationlatest->id;
				$remediationlauditormodal->status = $data['status'];
				$remediationlauditormodal->comment = $data['comment'];
				$remediationlauditormodal->created_at = time();
				$remediationlauditormodal->created_by = $userid;
				$remediationlauditormodal->save();
				
				
				//,'change_request'=>2, 'waiting_for_approval'=>3,
				if($model->save())
				{
					$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'remediation_mail_to_reviewer_from_leadauditor'])->one();
					if($mailContent !== null)
					{
						$auditmodal= Audit::find()->where(['id'=>$data['audit_id']])->one();
						if($auditmodal !== null)
						{
							$mailmsg=str_replace('{COMPANY-NAME}', $auditmodal->application->companyname, $mailContent['message'] );
							$mailmsg=str_replace('{COMPANY-EMAIL}', $auditmodal->application->emailaddress, $mailmsg );
							$mailmsg=str_replace('{COMPANY-TELEPHONE}', $auditmodal->application->telephone, $mailmsg );
							$mailmsg=str_replace('{CONTACT-NAME}', $auditmodal->application->firstname." ".$auditmodal->application->lastname, $mailmsg );

							$auditreviewermodal = AuditPlanReviewer::find()->where(['id'=>$data['audit_plan_id']])->one();
							if($auditreviewermodal !== null)
							{
								$MailLookupModel = new MailLookup();
								$MailLookupModel->to=$auditreviewermodal->user->email;								
								$MailLookupModel->bcc='';
								$MailLookupModel->subject=$mailContent['subject'];
								$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
								$MailLookupModel->attachment='';
								$MailLookupModel->mail_notification_id='';
								$MailLookupModel->mail_notification_code='';
								$Mailres=$MailLookupModel->sendMail();
							}

							
						}
					}
					$responsedata = ['status'=>1,'message'=>'Finding updated Successfully','data'=>['finding_id'=>$data['finding_id'],'finding_status'=>$model->status]];
				}
					
			}
		}
		return $responsedata;
	}


	/*
	Done by Reviewer - Settled
	*/
	/*
	public function actionReviewerFindingApproval(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{	
			$exemodel = new AuditPlanUnitExecutionChecklist();
			$auditunitstatusmodel = new AuditPlanUnit();
			$auditPlanstatusmodel = new AuditPlan();
			$auditstatusmodel = new Audit();

			$model = AuditPlanUnitExecutionChecklist::find()->where(['id' => $data['finding_id']])->one();
			if ($model !== null)
			{
				$model->status = $exemodel->arrEnumStatus['settled'];
				if($model->save())
				{

					
					$planmodel = AuditPlanUnitExecutionChecklist::find()->where(['unit_id' => $data['unit_id']]);
					$planmodel = $planmodel->andWhere('status!='.$settledstatus)->one();
					
					
					//Update Audit Plan Unit
					$auditplanunit = AuditPlanUnit::find()->where(['id'=>$data['audit_plan_unit_id']])->one();
					if($auditplanunit!==null){
						if($planmodel===null){
							$planunit->status = $auditunitstatusmodel->arrEnumStatus['remediation_completed'];


							if($planunit->save()){

								$remediation_completedstatus = $auditunitstatusmodel->arrEnumStatus['remediation_completed'];
								$planunitmodel = AuditPlanUnit::find()->where(['audit_plan_id' => $data['audit_plan_id']]);
								$planunitmodel = $planunitmodel->andWhere('status!='.$remediation_completedstatus)->one();
								
								
								
								//Update Audit Plan
								$auditplan = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
								if($auditplan!==null){
									if($planunitmodel===null){
										$auditplan->status = $auditunitstatusmodel->arrEnumStatus['remediation_completed'];
									}else{
										$auditplan->status = $auditunitstatusmodel->arrEnumStatus['remediation_in_progress'];
									}
									if($auditplan->save()){
										

										$planremediation_completedstatus = $auditPlanstatusmodel->arrEnumStatus['remediation_completed'];
										$auditplanmodel = AuditPlan::find()->where(['audit_plan_id' => $data['audit_plan_id']]);
										$auditplanmodel = $auditplanmodel->andWhere('status!='.$planremediation_completedstatus)->one();
										

										//Update Audit
										$audit = Audit::find()->where(['id'=>$data['audit_id']])->one();
										if($auditplanmodel===null){
											$audit->status = $auditstatusmodel->arrEnumStatus['remediation_completed'];
										}else{
											$audit->status = $auditstatusmodel->arrEnumStatus['remediation_in_progress'];
										}
										$audit->save();




									}
								}

							}
							


						}else{
							$planunit->status = $auditunitmodel->arrEnumStatus['remediation_in_progress'];
						}
						$planunit->save();
					}

					

					$responsedata = ['status'=>1,'message'=>'Finding updated Successfully','data'=>['finding_id'=>$data['finding_id'],'finding_status'=>$model->status]];
				}
			}
		}
		return $responsedata;
	}
	*/
	//settle_pending_review


	public function actionReviewerCloseFinding(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			
			$exemodel = new AuditPlanUnitExecutionChecklist();
			$auditunitmodel = new AuditPlanUnit();
			$auditPlanmodel = new AuditPlan();

			$model = AuditPlanUnitExecutionChecklist::find()->where(['id' => $data['finding_id']])->one();
			if ($model !== null)
			{
				//audit_completed
				$model->status = $exemodel->arrEnumStatus['settled'];
				
				//,'change_request'=>2, 'waiting_for_approval'=>3,
				if($model->save())
				{

					$unit_id = $data['unit_id'];
					$audit_plan_unit_id = $data['audit_plan_unit_id'];

					$AuditPlanUnitExecutionChecklist = AuditPlanUnitExecutionChecklist::find()->where(['audit_plan_unit_id' => $audit_plan_unit_id,'answer'=>2]);
					$AuditPlanUnitExecutionChecklist = $AuditPlanUnitExecutionChecklist->andWhere('status!='.$model->arrEnumStatus['settled'])->one();
					
					if($AuditPlanUnitExecutionChecklist === null){
						$auditplanunit = AuditPlanUnit::find()->where(['id'=>$audit_plan_unit_id])->one();
						
						if($auditplanunit !== null){
							$auditplanunit->status = $auditplanunit->arrEnumStatus['remediation_completed'];
							$auditplanunit->save();
							


							$auditplanunitdata = AuditPlanUnit::find()->where(['audit_plan_id'=>$data['audit_plan_id']]);
							$auditplanunitdata = $auditplanunitdata->andWhere('status!='.$auditplanunit->arrEnumStatus['remediation_completed'])->one();
							if($auditplanunitdata === null){
								$auditplan = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
								if($auditplan !== null){
									$auditplan->status = $auditplan->arrEnumStatus['remediation_completed'];
									$auditplan->save();


									
									$auditplandata = AuditPlan::find()->where(['id'=>$data['audit_plan_id']]);
									$auditplandata = $auditplandata->andWhere('status!='.$auditplan->arrEnumStatus['remediation_completed'])->one();
									if($auditplandata === null){
										$audit = Audit::find()->where(['id'=>$data['audit_id']])->one();
										if($audit !== null){
											$audit->status = $audit->arrEnumStatus['remediation_completed'];
											$audit->save();
										}
									}

									


								}
							}





							
						}
						
					}

					$responsedata = ['status'=>1,'message'=>'Finding updated Successfully'];
				}
					
			}
		}
		return $responsedata;
	}





	/*
	Done by Auditor/Reviewer - For Followup

	
	*/
	public function actionSavefollowupfindingreview(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			$exemodel = new AuditPlanUnitExecutionChecklist();


			$actiontype = $data['actiontype'];
			$findingstatus = '';
			
			
			
			$auditunitmodel = new AuditPlanUnit();
			$auditPlanmodel = new AuditPlan();

			$model = AuditPlanUnitExecutionChecklist::find()->where(['id' => $data['finding_id']])->one();
			if ($model !== null)
			{
				$modelauditplanunitexecution =  $model->auditplanunitexecution;
				/*
				if($data['status']==2){
					$model->status = $exemodel->arrEnumStatus['auditor_change_request'];
				}else if($data['status']==1){
					$model->status = $exemodel->arrEnumStatus['waiting_for_approval'];
				}
				*/
				//$model->audit_plan_unit_execution_id;

				$modelAuditPlanUnitExecutionFollowup = AuditPlanUnitExecutionFollowup::find()->where(['audit_plan_unit_id' => $modelauditplanunitexecution->audit_plan_unit_id,'sub_topic_id'=>$modelauditplanunitexecution->sub_topic_id ])->one();


				if($actiontype =='followup_auditor_review'){
					//$findingstatus = $exemodel->arrEnumStatus['waiting_for_approval'];
					//if($data['status'] == '1'){
						$findingstatus = $exemodel->arrEnumStatus['waiting_for_approval'];
					//}else{
						//$findingstatus = $exemodel->arrEnumStatus['auditor_change_request'];
					//}

					$remediationlauditormodal = new AuditPlanUnitFollowupRemediationReview();
					$remediationlauditormodal->audit_plan_unit_execution_checklist_id = $model->id;
					$remediationlauditormodal->audit_plan_unit_execution_followup_id = $modelAuditPlanUnitExecutionFollowup->id;
					$remediationlauditormodal->checklist_remediation_id = $model->checklistremediationlatest->id;
					$remediationlauditormodal->status = $data['status'];
					$remediationlauditormodal->comment = $data['comment'];
					$remediationlauditormodal->created_at = time();
					$remediationlauditormodal->created_by = $userid;
					$remediationlauditormodal->save();
				}else if($actiontype =='followup_lead_auditor_review'){

					if($data['status'] == '1'){
						$findingstatus = $exemodel->arrEnumStatus['followup_waiting_for_review_approval'];
					}else if($data['status'] == '3'){
						$findingstatus = $exemodel->arrEnumStatus['auditor_change_request'];
					}else{
						$findingstatus = $exemodel->arrEnumStatus['followup_lead_auditor_not_accepted'];
					}

					$remediationlauditormodal = new AuditPlanUnitExecutionChecklistRemediationApproval();
					$remediationlauditormodal->audit_plan_unit_execution_checklist_id = $model->id;
					$remediationlauditormodal->checklist_remediation_id = $model->checklistremediationlatest->id;
					$remediationlauditormodal->status = $data['status'];
					$remediationlauditormodal->comment = $data['comment'];
					$remediationlauditormodal->created_at = time();
					$remediationlauditormodal->created_by = $userid;
					$remediationlauditormodal->save();
				}else if($actiontype =='followup_reviewer_review'){
					
					if($data['status'] == '1'){
						$findingstatus = $exemodel->arrEnumStatus['settled'];
					}else if($data['status'] == '3'){
						$findingstatus = $exemodel->arrEnumStatus['reviewer_change_request'];
					}else{
						$findingstatus = $exemodel->arrEnumStatus['not_accepted'];
					}
					
					
					//not_accepted

					$remediationlauditormodal = new AuditPlanUnitExecutionChecklistRemediationReview();
					$remediationlauditormodal->audit_plan_unit_execution_checklist_id = $model->id;
					$remediationlauditormodal->checklist_remediation_id = $model->checklistremediationlatest->id;
					$remediationlauditormodal->status = $data['status'];
					$remediationlauditormodal->comment = $data['comment'];
					$remediationlauditormodal->created_at = time();
					$remediationlauditormodal->created_by = $userid;
					$remediationlauditormodal->save();
				}
				
				
				
				


				$model->status = $findingstatus;
				
				//,'change_request'=>2, 'waiting_for_approval'=>3,
				if($model->save())
				{
					if($actiontype =='followup_auditor_review'){
						$auditplanmodel = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
						$auditplanmodel->status = $auditplanmodel->arrEnumStatus['followup_inprocess'];
						$auditplanmodel->save();

						$auditplanunitmodel = AuditPlanUnit::find()->where(['audit_plan_id'=>$data['audit_plan_id'],'unit_id'=>$data['unit_id'] ])->one();
						if($auditplanunitmodel !== null ){
							//foreach($auditplanunitmodel as $planunitdata){
							$auditplanunitmodel->status = $auditplanunitmodel->arrEnumStatus['followup_inprocess'];
							$auditplanunitmodel->save();
							//}
						}

						
						//$AuditPlanUnitExecutionModel = AuditPlanUnitExecutionFollowup::find()->where(['audit_plan_unit_id'=>$model->audit_plan_unit_id,'sub_topic_id'=>$model->sub_topic_id])->one();
						if($modelAuditPlanUnitExecutionFollowup !== null){
							if($modelAuditPlanUnitExecutionFollowup->status == $modelAuditPlanUnitExecutionFollowup->arrEnumStatus['open']){
								$modelAuditPlanUnitExecutionFollowup->executed_by = $userData['userid'];
							}
							
							$modelAuditPlanUnitExecutionFollowup->executed_date = time();
							if($modelAuditPlanUnitExecutionFollowup->created_by == ''){
								$modelAuditPlanUnitExecutionFollowup->created_at = time();
								$modelAuditPlanUnitExecutionFollowup->created_by = $userData['userid'];
							}
							$modelAuditPlanUnitExecutionFollowup->updated_at = time();
							$modelAuditPlanUnitExecutionFollowup->updated_by = $userData['userid'];
							$modelAuditPlanUnitExecutionFollowup->status = $modelAuditPlanUnitExecutionFollowup->arrEnumStatus['inprogress'];
							$modelAuditPlanUnitExecutionFollowup->save();
							

							$modelauditplanunitexecution_id = $modelauditplanunitexecution->id;

							$CheckAuditPlanUnitExecutionModel = AuditPlanUnitExecutionChecklist::find()->where(['audit_plan_unit_execution_id'=>$modelauditplanunitexecution_id,'finding_type'=>2]);
							$CheckAuditPlanUnitExecutionModel = $CheckAuditPlanUnitExecutionModel->andWhere(['!=','status',$exemodel->arrEnumStatus['waiting_for_approval']])->one();
							if($CheckAuditPlanUnitExecutionModel === null){
								$modelAuditPlanUnitExecutionFollowup->status = $modelAuditPlanUnitExecutionFollowup->arrEnumStatus['waiting_for_unit_lead_auditor_approval'];
								$modelAuditPlanUnitExecutionFollowup->save();



								//$AuditPlanUnitExecutionModelStatus = new AuditPlanUnitExecution();
								$AuditPlanUnitExecutionFollowupModel = AuditPlanUnitExecutionFollowup::find()->where(['audit_plan_unit_id'=>$data['audit_plan_unit_id']]);
								$AuditPlanUnitExecutionFollowupModel = $AuditPlanUnitExecutionFollowupModel->andWhere(' `status`!='.$modelAuditPlanUnitExecutionFollowup->arrEnumStatus['completed'].' AND  `status`!='.$modelAuditPlanUnitExecutionFollowup->arrEnumStatus['waiting_for_unit_lead_auditor_approval'])
								->one();
								//$auditsubtopiccount = count($AuditPlanUnitExecutionFollowupModel);
								if($AuditPlanUnitExecutionFollowupModel === null){
									$planunit = AuditPlanUnit::find()->where(['id'=>$data['audit_plan_unit_id']])->one();
									$planunit->status = $planunit->arrEnumStatus['followup_awaiting_unit_lead_auditor_approval'];
									$planunit->status_change_date = time();
									$planunit->save();

									//$AuditPlanModel
									$audit_plandata = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
									if($audit_plandata !== null){
										$audit_plandata->status = $audit_plandata->arrEnumStatus['followup_inprocess'];
										$audit_plandata->save();
									}
									$audit_data = Audit::find()->where(['id'=>$data['audit_id']])->one();
									if($audit_data !== null){
										$audit_data->status = $audit_data->arrEnumStatus['followup_audit_in_progress'];
										$audit_data->save();
									}
								}
									




							}
						
								
							//}
							
						
						}

						


							
					}
					
					if($actiontype =='followup_lead_auditor_review'){
						
					}


					// && $data['status']==1
					if($actiontype =='followup_reviewer_review'){

						$unit_id = $data['unit_id'];
						$audit_plan_unit_id = $data['audit_plan_unit_id'];
						//$AuditPlanUnitExecutionChecklist = AuditPlanUnitExecutionChecklist::find()->where(['unit_id' => $unit_id,'finding_type'=>2]);
						$AuditPlanUnitExecutionChecklist = AuditPlanUnitExecutionChecklist::find()->where(['audit_plan_unit_id' => $audit_plan_unit_id,'finding_type'=>2]);
						$AuditPlanUnitExecutionChecklist = $AuditPlanUnitExecutionChecklist->andWhere('(status!='.$model->arrEnumStatus['settled'].' AND status!='.$model->arrEnumStatus['not_accepted'].' )')->one();
						//echo $AuditPlanUnitExecutionChecklist;
						if($AuditPlanUnitExecutionChecklist === null){
							//
							//$auditplanunit = AuditPlanUnit::find()->where(['audit_plan_id'=>$data['audit_plan_id'],'unit_id'=>$data['unit_id'] ])->one();
							$auditplanunit = AuditPlanUnit::find()->where(['id'=>$data['audit_plan_unit_id']])->one();
							//echo $auditplanunit;
							if($auditplanunit !== null){
								$auditplanunit->status = $auditplanunit->arrEnumStatus['remediation_completed'];
								$auditplanunit->save();
								


								$auditplanunitdata = AuditPlanUnit::find()->where(['audit_plan_id'=>$data['audit_plan_id']]);
								$auditplanunitdata = $auditplanunitdata->andWhere('status!='.$auditplanunit->arrEnumStatus['remediation_completed'])->one();

								echo $auditplanunitdata;
								if($auditplanunitdata === null){
									$auditplan = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
									if($auditplan !== null){
										$auditplan->status = $auditplan->arrEnumStatus['remediation_completed'];
										$auditplan->save();


										
										$auditplandata = AuditPlan::find()->where(['id'=>$data['audit_plan_id']]);
										$auditplandata = $auditplandata->andWhere('status!='.$auditplan->arrEnumStatus['remediation_completed'])->one();
										if($auditplandata === null){
											$audit = Audit::find()->where(['id'=>$data['audit_id']])->one();
											if($audit !== null){
												$audit->status = $audit->arrEnumStatus['remediation_completed'];
												$audit->save();
											}
										}

										


									}
								}





								
							}
							
						}
					}

					$responsedata = ['status'=>1,'message'=>'Finding updated Successfully','data'=>['finding_id'=>$data['finding_id'],'finding_status'=>$model->status]];
				}
					
			}
		}
		return $responsedata;
	}
}
