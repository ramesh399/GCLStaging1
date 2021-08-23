<?php
namespace app\modules\audit\controllers;

use Yii;
use yii\web\NotFoundHttpException;

use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\AuditPlanUnit;
use app\modules\audit\models\AuditPlanUnitDate;
use app\modules\audit\models\AuditPlanUnitAuditor;
use app\modules\audit\models\AuditPlanUnitStandard;
use app\modules\audit\models\AuditPlanUnitAuditorDate;
use app\modules\audit\models\AuditPlanUnitStandardAuditor;
use app\modules\audit\models\AuditPlanInspection;
use app\modules\audit\models\AuditPlanInspectionPlan;

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
use app\modules\audit\models\AuditPlanUnitExecutionChecklistStandard;
use app\modules\audit\models\AuditPlanUnitExecutionChecklistRemediationReview;
use app\modules\audit\models\AuditPlanUnitExecutionChecklistRemediationFile;
use app\modules\audit\models\AuditPlanUnitExecutionChecklistRemediationApproval;
use app\modules\audit\models\AuditPlanUnitFollowupRemediationReview;

use app\modules\master\models\AuditExecutionQuestionStandard;
use app\modules\master\models\AuditExecutionQuestion;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationUnitStandard;

use app\modules\master\models\MailNotifications;
use app\modules\master\models\Standard;
use app\modules\master\models\MailLookup;
use app\modules\master\models\AuditNonConformityTimeline;

use app\modules\audit\models\AuditPlanUnitExecution;
use app\modules\audit\models\AuditPlanUnitExecutionChecklist;
use app\modules\audit\models\AuditPlanUnitExecutionChecklistRemediation;
use app\modules\audit\models\AuditReportNcnReport;
use app\modules\audit\models\AuditReportEnvironment;

use app\modules\audit\models\AuditReportClientInformationGeneralInfo;
use app\modules\audit\models\AuditReportApplicableDetails;
use app\modules\audit\models\AuditReportChemicalList;
use app\modules\audit\models\AuditReportSampling;
use app\modules\audit\models\AuditReportAttendanceSheet;
use app\modules\audit\models\AuditReportInterviewEmployees;
use app\modules\audit\models\AuditReportLivingWageRequirementReview;
use app\modules\audit\models\AuditReportLivingWageFamilyExpenses;
use app\modules\audit\models\AuditReportQbsScopeHolder;


use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditPlanController implements the CRUD actions for Process model.
 */
class AuditExecutionController extends \yii\rest\Controller
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
		
		$post = yii::$app->request->post();
		$unit_id = isset($post['unit_id'])?$post['unit_id']:'';
		$audit_plan_id = isset($post['audit_plan_id'])?$post['audit_plan_id']:'';
		$audit_plan_unit_id = '';
		if( $unit_id != '' && $audit_plan_id != ''){
			$AuditPlanUnit = AuditPlanUnit::find()->where(['audit_plan_id'=>$audit_plan_id, 'unit_id'=>$unit_id ])->one();
			if($AuditPlanUnit !== null){
				$audit_plan_unit_id = $AuditPlanUnit->id;
			}
		}

		//$post['audit_unit_id']=8;
		$model = AuditPlanUnitExecutionChecklist::find()->alias( 't' );
		$model->joinWith('auditnonconformitytimeline as timeline');
		$model->joinWith('auditplanunitexecution as planunitexecution');

        $date_format = Yii::$app->globalfuns->getSettings('date_format');
		if($unit_id != ''){
			$model = $model->where(['t.unit_id' => $unit_id]);
		}
		if($audit_plan_unit_id != ''){
			$model = $model->where(['planunitexecution.audit_plan_unit_id' => $audit_plan_unit_id]);
		}
		$type = isset($post['type'])?$post['type']:'';
		//$type='nc';
		if($type =='nc'){
			$model = $model->andWhere('t.answer=2');
		}

		if(isset($post['subtopic'])  && $post['subtopic']!='' ){
			$subtopicArr = explode(',',$post['subtopic']);

			$model = $model->joinWith('auditplanunitexecution as execution');
			
			$model = $model->andWhere(['execution.sub_topic_id'=>$subtopicArr]);
			//$model = $model->andWhere('t.answer=2');
			//$post['subtopic']
		}

		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
                    ['like', 't.finding', $searchTerm],	
                    ['like', 'timeline.name', $searchTerm],					
				]);

				$totalCount = $model->count();
			}
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
			}
			else
			{
				$model = $model->orderBy(['id' => SORT_DESC]);
			}
			//$model = $model->limit($pageSize)->offset($page);	
		}
		else
		{
			$totalCount = $model->count();
		}

		$findings_list=array();
		
		$model = $model->all();
		
		$auditplanStatus = '';
		$auditplanmodel = AuditPlan::find()->where(['id'=>$post['audit_plan_id']])->one();
		if($auditplanmodel !== null){
			$auditplanStatus = $auditplanmodel->status;
		}else{
			$auditplanmodel = new AuditPlan();
		}
		
		if(count($model)>0)
		{
			foreach($model as $findings)
			{
				$data=array();
				$data['id']=$findings->id;
				$data['finding']=$findings->finding;
				$data['finding_type']=$findings->finding_type;
				$data['finding_type_label']=isset($findings->findingTypeArr[$findings->finding_type])?$findings->findingTypeArr[$findings->finding_type]:'';
				
				$data['answer']=$findings->answer;
				$data['status']=$findings->status;
				$data['file']=$findings->file;
				$data['status_name']=$findings->answer==2?$findings->arrStatus[$findings->status]:'NA';
				$data['severity']=($findings->auditnonconformitytimeline)?$findings->auditnonconformitytimeline->name:'NA';
				$findings_list[]=$data;
			}
		}

		return ['unitfindings'=>$findings_list,'total'=>$totalCount,'auditplanStatus'=>$auditplanStatus,'arrAuditplanStatus'=>$auditplanmodel->arrEnumStatus];
	}

	public function actionGetRemediation()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$datapost = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');

		if($datapost)
		{
			$responsedata=array();
			$responsedata['finding_id'] = $datapost['finding_id'];

			$remednewmodal = AuditPlanUnitExecutionChecklistRemediation::find()->where(['audit_plan_unit_execution_checklist_id'=>$datapost['finding_id'],'status'=>0])->one();
			if($remednewmodal !== null)
			{
				$data = [];
				$files = [];
				$data['audit_plan_unit_execution_checklist_id'] = $remednewmodal->audit_plan_unit_execution_checklist_id;
				$data['root_cause'] = $remednewmodal->root_cause;
				$data['correction'] = $remednewmodal->correction;
				$data['corrective_action'] = $remednewmodal->corrective_action;

				$remedfiles = $remednewmodal->remediationfile;
				foreach($remedfiles as $file)
				{
					$arrfile = [];
					$arrfile['id']=$file->id;
					$arrfile['name']=$file->filename;
					$arrfile['added']=0;
					$arrfile['deleted']=0;
					$files[]=$arrfile;
				}
				$data['evidence_file'] = $files;

				$responsedata['remediation_new']=$data;
			}	

			//select(['audit_plan_unit_execution_checklist_id','root_cause','correction','corrective_action','evidence_file','status'])
			$remedmodal = AuditPlanUnitExecutionChecklistRemediation::find()->where(['audit_plan_unit_execution_checklist_id'=>$datapost['finding_id'],'status'=>1])->orderBy(['created_at' => SORT_DESC])->all();
			if(count($remedmodal)>0)
			{
				$remediation_old = [];
				foreach($remedmodal as $val)
				{
					$data = [];
					$files = [];
					$data['audit_plan_unit_execution_checklist_id'] = $val['audit_plan_unit_execution_checklist_id'];
					$data['root_cause'] = $val['root_cause'];
					$data['correction'] = $val['correction'];
					$data['corrective_action'] = $val['corrective_action'];

					$remedfiles = $val->remediationfile;
					//echo count($remedfiles);
					foreach($remedfiles as $file)
					{
						//$files[]=$files;
						$arrfile = [];
						$arrfile['id']=$file->id;
						$arrfile['name']=$file->filename;
						$arrfile['added']=0;
						$arrfile['deleted']=0;
						$files[]=$arrfile;
					}
					$data['evidence_file'] = $files;
					
					$remediation_old[] = $data;
				}
				
				$responsedata['remediation_old']=$remediation_old;
			}	

			$reviewmodel = new AuditPlanUnitExecutionChecklistRemediationReview();
			$RemediationReviewmodal = AuditPlanUnitExecutionChecklistRemediationReview::find()->where(['audit_plan_unit_execution_checklist_id'=>$datapost['finding_id']])->orderBy(['created_at' => SORT_DESC])->all();
			if(count($RemediationReviewmodal)>0)
			{
				$reviewarr = [];
				foreach($RemediationReviewmodal as $val)
				{
					$data = [];
					$data['created_at'] = $val->created_at?date($date_format,$val->created_at):'';
					$data['comment'] = $val->comment;
					$data['status'] = $reviewmodel->arrStatus[$val->status];
					$data['created_by_label'] = $val->user->first_name." ".$val->user->last_name;
					$reviewarr[] = $data;
				}
				$responsedata['reviewer_comments']=$reviewarr;
			}

			$approvalmodel = new AuditPlanUnitExecutionChecklistRemediationApproval();
			$RemediationApprovalmodal = AuditPlanUnitExecutionChecklistRemediationApproval::find()->where(['audit_plan_unit_execution_checklist_id'=>$datapost['finding_id']])->orderBy(['created_at' => SORT_DESC])->all();
			if(count($RemediationApprovalmodal)>0)
			{
				$approvalarr = [];
				foreach($RemediationApprovalmodal as $val)
				{
					$data = [];
					$data['created_at'] = $val->created_at?date($date_format,$val->created_at):'';
					$data['comment'] = $val->comment;
					$data['status'] = $reviewmodel->arrStatus[$val->status];
					$data['created_by_label'] = $val->user->first_name." ".$val->user->last_name;
					$approvalarr[] = $data;
				}
				$responsedata['auditor_comments']=$approvalarr;
			}

			$approvalmodel = new AuditPlanUnitFollowupRemediationReview();
			$AuditPlanUnitExecutionFollowupmodal = AuditPlanUnitFollowupRemediationReview::find()->where(['audit_plan_unit_execution_checklist_id'=>$datapost['finding_id']])->orderBy(['created_at' => SORT_DESC])->all();
			if(count($AuditPlanUnitExecutionFollowupmodal)>0)
			{
				$approvalarr = [];
				foreach($AuditPlanUnitExecutionFollowupmodal as $val)
				{
					$data = [];
					$data['created_at'] = $val->created_at?date($date_format,$val->created_at):'';
					$data['comment'] = $val->comment;
					$data['status'] = $reviewmodel->arrStatus[$val->status];
					$data['created_by_label'] = $val->user->first_name." ".$val->user->last_name;
					$approvalarr[] = $data;
				}
				$responsedata['unit_auditor_comments']=$approvalarr;
			}
		}
		return ['data'=>$responsedata];
		
	}

	public function actionEvidencefile(){
		$data = Yii::$app->request->post();
		
		$files = AuditPlanUnitExecutionChecklistRemediationFile::find()->where(['id'=>$data['id']])->one();
		
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['remediation_evidence_files'].$files->filename;
		
		if(file_exists($filepath)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
			header('Access-Control-Expose-Headers: Content-Length,Content-Disposition,filename,Content-Type;');
			header('Access-Control-Allow-Headers: Content-Length,Content-Disposition,filename,Content-Type');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($filepath));
			flush(); // Flush system output buffer
			readfile($filepath);
		}
		die;
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

	public function actionAuditExecution()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if($data)
		{
			$connection = Yii::$app->getDb();
			
			$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();

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
			
			$AuditPlanUnitModel = new AuditPlanUnit();

			$AuditPlanModel = new AuditPlan();
			$AuditModel = new Audit();

			$arrSubTopic = explode(',',$dataVal['sub_topic_id']);		
			
			$AuditPlanUnitObj = AuditPlanUnit::find()->where(['id'=>$dataVal['audit_plan_unit_id']])->one();
			if($AuditPlanUnitObj !== null)
			{					
				$audit_plan_unit_id = $dataVal['audit_plan_unit_id'];
				$hasPermission=false;

				
				$unitauditorstatus = [$AuditPlanUnitObj->arrEnumStatus['open'],$AuditPlanUnitObj->arrEnumStatus['in_progress'],$AuditPlanUnitObj->arrEnumStatus['reviewer_reinititated'],$AuditPlanUnitObj->arrEnumStatus['awaiting_for_unit_lead_auditor_approval']];
				if(in_array($AuditPlanUnitObj->status,$unitauditorstatus)){
					if(Yii::$app->userrole->isAdmin()){
						$hasPermission=true;
					}else if(Yii::$app->userrole->isAuditor($audit_plan_unit_id,1)){
						$matchedsubtopic = [];
						$AuditPlanUnitExecutionModelCheck = AuditPlanUnitExecution::find()->where(['audit_plan_unit_id'=>$audit_plan_unit_id, 'sub_topic_id' => $arrSubTopic,'status'=>['0','1','3','2'],'executed_by'=>$userid])->all();
						if(count($AuditPlanUnitExecutionModelCheck)>0){
							foreach($AuditPlanUnitExecutionModelCheck as $exesubtopic){
								$matchedsubtopic[] = $exesubtopic->sub_topic_id;
							}
						}
						if(Yii::$app->userrole->isUnitLeadAuditor($audit_plan_unit_id)){
							$ressubtopic=array_diff($arrSubTopic,$matchedsubtopic);
							
							if(count($ressubtopic)>0){
								$AuditPlanUnitExecutionModelLeadCheck = AuditPlanUnitExecution::find()->where(['audit_plan_unit_id'=>$audit_plan_unit_id,'sub_topic_id' => $ressubtopic,'status'=>1])->all();
								if(count($AuditPlanUnitExecutionModelLeadCheck)==count($ressubtopic)){
									$hasPermission=true;
								}
							}else{
								$hasPermission=true;
							}
						}else{
							if(count($matchedsubtopic) == count($arrSubTopic)){
								$hasPermission=true;
							}
						}
						
					}
				}elseif(in_array($AuditPlanUnitObj->status, array($AuditPlanUnitModel->arrEnumStatus['awaiting_for_lead_auditor_approval']))){
					if(Yii::$app->userrole->isAdmin()){
						$hasPermission=true;
					}else if(Yii::$app->userrole->isAuditProjectLA($AuditPlanUnitObj->auditplan->audit_id)){
						$hasPermission=true;
					}
				}elseif(in_array($AuditPlanUnitObj->status, array($AuditPlanUnitModel->arrEnumStatus['awaiting_for_reviewer_approval']))){
					if(Yii::$app->userrole->isAdmin()){
						$hasPermission=true;
					}else if(Yii::$app->userrole->isAuditReviewer($audit_plan_id)){
						$hasPermission=true;
					} 
				}
				//$hasPermission=true;
				if(!$hasPermission){
					return $responsedata;
				}
			}else{
				return $responsedata;
			}	
			
			

			$arrExecutionIDs = [];
			foreach($arrSubTopic as $subtopicid){
				$AuditPlanUnitExecutionModel = AuditPlanUnitExecution::find()->where(['audit_plan_unit_id'=>$dataVal['audit_plan_unit_id'],'sub_topic_id'=>$subtopicid])->one();
				if($AuditPlanUnitExecutionModel !== null){
					$arrExecutionIDs[$subtopicid] = $AuditPlanUnitExecutionModel->id;

					//$AuditPlanUnitExecutionModel->executed_by = $userData['userid'];
					//$AuditPlanUnitExecutionModel->executed_date = time();
					//$audit->executed_date = time();
					//if($AuditPlanUnitExecutionModel->executed_by == $userData['userid']){
						$AuditPlanUnitExecutionModel->executed_date = time();
					//}
					if($AuditPlanUnitExecutionModel->created_by == ''){
						$AuditPlanUnitExecutionModel->created_at = time();
						$AuditPlanUnitExecutionModel->created_by = $userData['userid'];
					}
					

					$AuditPlanUnitExecutionModel->updated_at = time();
					$AuditPlanUnitExecutionModel->updated_by = $userData['userid'];
					if($AuditPlanUnitExecutionModel->status != $AuditPlanUnitExecutionModel->arrEnumStatus['completed'] ){
						$AuditPlanUnitExecutionModel->status = $AuditPlanUnitExecutionModel->arrEnumStatus['waiting_for_unit_lead_auditor_approval'];
					}
					$AuditPlanUnitExecutionModel->save();
				}else{
					$AuditPlanUnitExecutionModel = new AuditPlanUnitExecution();
					$AuditPlanUnitExecutionModel->status = $AuditPlanUnitExecutionModel->arrEnumStatus['waiting_for_unit_lead_auditor_approval'];
					$AuditPlanUnitExecutionModel->audit_plan_unit_id = $dataVal['audit_plan_unit_id'];
					$AuditPlanUnitExecutionModel->sub_topic_id = $subtopicid;
					$AuditPlanUnitExecutionModel->executed_date = time();
					$AuditPlanUnitExecutionModel->created_at = time();
					$AuditPlanUnitExecutionModel->created_by = $userData['userid'];
					$AuditPlanUnitExecutionModel->save();

					$arrExecutionIDs[$subtopicid] = $AuditPlanUnitExecutionModel->id;
				}
			}
			//print_r($arrExecutionIDs); die;
			$planunitstandardList = [];
			$AuditPlanUnitStandard = AuditPlanUnitStandard::find()->where(['audit_plan_unit_id'=> $dataVal['audit_plan_unit_id']])->all();
			foreach($AuditPlanUnitStandard as $planunitstandard){
				$planunitstandardList[] = $planunitstandard->standard_id;
			}
			//$AuditPlanUnitExecutionModel->user_id = $userData['userid'];
				
			
			
			//print_r($AuditPlanUnitExecutionModel->getErrors());
			//die();
			
			//if($AuditPlanUnitExecutionModel->validate() && $AuditPlanUnitExecutionModel->save())
			if(count($arrExecutionIDs)>0)
        	{
				//$auditPlanUnitExecutionID = $AuditPlanUnitExecutionModel->id;
				$target_dir = Yii::$app->params['audit_files']; 
				$qts = $dataVal['questions'];
				if(is_array($qts) && count($qts)>0)
				{
					//print_r($qts); die;
					foreach($qts as $question)
					{
						$subtopicid = $question['sub_topic_id'];
						$newChecklistRecord = 0;
						$AuditPlanUnitExecutionChecklistModel = AuditPlanUnitExecutionChecklist::find()->where(['audit_plan_unit_execution_id'=>$arrExecutionIDs[$subtopicid],'question_id'=>$question['question_id']])->one();
						if($AuditPlanUnitExecutionChecklistModel === null)
						{
							//die;
							$AuditPlanUnitExecutionChecklistModel = new AuditPlanUnitExecutionChecklist();
							$AuditPlanUnitExecutionChecklistModel->audit_plan_unit_execution_id = $arrExecutionIDs[$subtopicid];
							$AuditPlanUnitExecutionChecklistModel->unit_id = $dataVal['unit_id'];
							$AuditPlanUnitExecutionChecklistModel->audit_plan_unit_id = $dataVal['audit_plan_unit_id'];
							$newChecklistRecord =1;
						}
						$AuditPlanUnitExecutionChecklistModel->status = 0;
						//echo  $question['answer'].'==='.$question['question_id'].'==='.$arrExecutionIDs[$subtopicid];
						$AuditPlanUnitExecutionChecklistModel->answer = $question['answer'];
						$AuditPlanUnitExecutionChecklistModel->finding = $question['findings'];
						if($AuditPlanUnitExecutionChecklistModel->answer==2)
						{
							$AuditPlanUnitExecutionChecklistModel->severity = $question['severity'];
						}
						
						$AuditPlanUnitExecutionChecklistModel->question = $question['question'];
						$AuditPlanUnitExecutionChecklistModel->question_id = $question['question_id'];
						
						// -----------------File Upload Code Start Here ------------------
						if(isset($question['file']) && $question['file']!='')
						{
							$imagedata = $question['file'];														
							$output_file = $question['filename'];
							//file_put_contents($target_dir.$output_file, file_get_contents($imagedata));
							if($AuditPlanUnitExecutionChecklistModel!==null && $AuditPlanUnitExecutionChecklistModel->file!='')
							{
								Yii::$app->globalfuns->removeFiles($AuditPlanUnitExecutionChecklistModel->file,$target_dir);							
							}
							
							$AuditPlanUnitExecutionChecklistModel->file = Yii::$app->globalfuns->binaryFiles($output_file,$imagedata,$target_dir);
						}else{
							$AuditPlanUnitExecutionChecklistModel->file = isset($question['filename'])?$question['filename']:'';
						}
						
						/*
						if(isset($_FILES['questionfile']['name'][$question['question_id']]))
						{								
							
							
							$tmp_name = $_FILES['questionfile']["tmp_name"][$question['question_id']];
				   			$name = $_FILES['questionfile']['name'][$question['question_id']];
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
							
							if (move_uploaded_file($_FILES['questionfile']["tmp_name"][$question['question_id']], $target_dir.$name)) 
							{
								$AuditPlanUnitExecutionChecklistModel->file = isset($name)?$name:"";
							}
							
						}else{
							$AuditPlanUnitExecutionChecklistModel->file = $question['file'];
						}
						*/
						// -----------------File Upload Code End Here ------------------
						//echo $AuditPlanUnitExecutionChecklistModel->answer;
						//$AuditPlanUnitExecutionChecklistModel->save();
						//print_r($AuditPlanUnitExecutionChecklistModel->getErrors());

						if($AuditPlanUnitExecutionChecklistModel->save() && $newChecklistRecord && count($planunitstandardList)>0 ){
							$questionStandard = AuditExecutionQuestionStandard::find()
											->where(['audit_execution_question_id'=>$question['question_id'],'standard_id'=>$planunitstandardList ])->all();
							//To Store Standard of the question
							if(count($questionStandard)>0){
								foreach($questionStandard as $qstandard){
									$checkliststandard = new AuditPlanUnitExecutionChecklistStandard();
									$checkliststandard->audit_plan_unit_execution_checklist_id = $AuditPlanUnitExecutionChecklistModel->id;
									$checkliststandard->question_id = $question['question_id'];
									$checkliststandard->question_standard_id = $qstandard->id;
									$checkliststandard->standard_id = $qstandard->standard_id;
									$checkliststandard->clause_no = $qstandard->clause_no;
									$checkliststandard->clause = $qstandard->clause;
									$checkliststandard->save();
								}
							}
						}
						//print_r($AuditPlanUnitExecutionChecklistModel->getErrors());


						//print_r($AuditPlanUnitExecutionChecklistModel->getErrors());
						//die();
					}					
				}
			}	
			
			$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
			$planunit = AuditPlanUnit::find()->where(['id'=>$dataVal['audit_plan_unit_id']])->one();
			if($planunit!== null && $planunit->status != $planunit->arrEnumStatus['awaiting_for_lead_auditor_approval'] )
			{
				$AuditPlanUnitExecutionModelStatus = new AuditPlanUnitExecution();
				$AuditPlanUnitExecutionModel = AuditPlanUnitExecution::find()->where(['audit_plan_unit_id'=>$dataVal['audit_plan_unit_id']]);
				$AuditPlanUnitExecutionModel = $AuditPlanUnitExecutionModel->andWhere('status='.$AuditPlanUnitExecutionModelStatus->arrEnumStatus['completed'].' OR status='.$AuditPlanUnitExecutionModelStatus->arrEnumStatus['waiting_for_unit_lead_auditor_approval'])
				->groupBy(['sub_topic_id'])->all();
				$auditsubtopiccount = count($AuditPlanUnitExecutionModel);
				
				//$unitsubtopics = $this->getSubtopic($dataVal['unit_id']);
				
				//$unitsubtopics = Yii::$app->globalfuns->getCurrentSubtopicIds($dataVal['unit_id']);
				$unitsubtopics = Yii::$app->globalfuns->getCurrentExecutionSubtopicIds($dataVal['audit_plan_unit_id']);
				
				//Yii::$app->globalfuns->getCurrentSubtopic($dataVal['unit_id']);
				if($auditsubtopiccount >= count($unitsubtopics)){
					$planunit = AuditPlanUnit::find()->where(['id'=>$dataVal['audit_plan_unit_id']])->one();
					$planunit->status = $AuditPlanUnitModel->arrEnumStatus['awaiting_for_unit_lead_auditor_approval'];
					$planunit->status_change_date = time();
					$planunit->save();

					//$AuditPlanModel
					$audit_plandata = AuditPlan::find()->where(['id'=>$dataVal['audit_plan_id']])->one();
					if($audit_plandata !== null){
						$audit_plandata->status = $AuditPlanModel->arrEnumStatus['in_progress'];
						$audit_plandata->save();
					}
					$audit_data = Audit::find()->where(['id'=>$dataVal['audit_id']])->one();
					if($audit_data !== null){
						$audit_data->status = $AuditModel->arrEnumStatus['audit_in_progress'];
						$audit_data->save();
					}
				}else if($auditsubtopiccount >0){
					$planunit = AuditPlanUnit::find()->where(['id'=>$dataVal['audit_plan_unit_id']])->one();
					$planunit->status = $AuditPlanUnitModel->arrEnumStatus['in_progress'];
					$planunit->status_change_date = time();
					$planunit->save();
				}
			}
			// - audit_in_progress
			// AuditPlan - in_progress

			$responsedata=array('status'=>1,'message'=>'Saved successfully','audit_plan_unit_id'=>$dataVal['audit_plan_unit_id']);
		}
		return $responsedata;
	}

	private function getCurrentSubtopic($unit_id){
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
		//if($userid){
		//	$condition .= " AND ( execution.executed_by is null OR execution.executed_by=".$userid.")";
		//}
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		$command = $connection->createCommand("SELECT user.first_name,user.last_name,execution.status,execution.executed_by,execution.executed_date,subtopic.id,
			subtopic.name FROM `tbl_application_unit` AS unit 
			INNER JOIN `tbl_audit_plan_unit` as planunit on unit.id=planunit.unit_id 
			INNER JOIN `tbl_sub_topic` AS subtopic ON subtopic.id=aeq.sub_topic_id 
			INNER JOIN `tbl_audit_plan_unit_execution` AS execution on execution.sub_topic_id = subtopic.id and planunit.id= execution.audit_plan_unit_id 
			LEFT JOIN `tbl_users` AS user on user.id = execution.executed_by  
			WHERE 1=1  ".$condition." AND aeq.status=0 GROUP BY subtopic.id");
		$result = $command->queryAll();
		/*
		INNER JOIN `tbl_application_unit_process` AS uprs ON unit.id=uprs.unit_id 
		INNER JOIN `tbl_application_unit_standard` AS ustd ON unit.id=ustd.unit_id 
		INNER JOIN `tbl_audit_execution_question_process` AS aeqp ON uprs.process_id=aeqp.process_id 
		INNER JOIN `tbl_audit_execution_question_standard` AS aeqs ON ustd.standard_id=aeqs.standard_id 
		INNER JOIN `tbl_audit_execution_question` AS aeq ON aeqp.audit_execution_question_id=aeq.id AND  aeqs.audit_execution_question_id=aeq.id 
		*/
		
		$dataArr = [];
		if(count($result)>0){
			foreach($result as $subdata){
				$dataArr[] =['id'=>$subdata['id'],'name'=>$subdata['name']];
			}
		}
		return $dataArr;

	}

	private function getSubtopic($unit_id){
		//$query = '';
		// AND aeq.sub_topic_id IS NULL
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		
		$connection = Yii::$app->getDb();
		$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();
		$condition = '';
		
		if($unit_id){
			$condition = " AND unit.id=".$unit_id;
		}
		$command = $connection->createCommand("SELECT subtopic.id,subtopic.name FROM `tbl_application_unit` AS unit 
			INNER JOIN `tbl_application_unit_process` AS uprs ON unit.id=uprs.unit_id 
			INNER JOIN `tbl_application_unit_standard` AS ustd ON unit.id=ustd.unit_id 
			INNER JOIN `tbl_audit_execution_question_process` AS aeqp ON uprs.process_id=aeqp.process_id 
			INNER JOIN `tbl_audit_execution_question_standard` AS aeqs ON ustd.standard_id=aeqs.standard_id 
			INNER JOIN `tbl_audit_execution_question` AS aeq ON aeqp.audit_execution_question_id=aeq.id AND  aeqs.audit_execution_question_id=aeq.id 
			INNER JOIN `tbl_sub_topic` AS subtopic ON subtopic.id=aeq.sub_topic_id
			WHERE 1=1  ".$condition." AND aeq.status=0 
			GROUP BY subtopic.id");
		$result = $command->queryAll();
		$dataArr = [];
		if(count($result)>0){
			foreach($result as $subdata){
				$dataArr[] =['id'=>$subdata['id'],'name'=>$subdata['name']];
			}
		}
		//$responsedata =['status'=>1,'data'=>$dataArr];
		

		return $dataArr;

	}
	
	public function actionGetQuestions()
    {
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$post = Yii::$app->request->post();
		$get = Yii::$app->request->get();
		if($post || $get)
		{
			$sub_topic_id='';
			$audit_plan_id='';
			$unit_id='';
			if($post)
			{
				$sub_topic_id=$post['sub_topic_id'];
				$audit_plan_id=$post['audit_plan_id'];
				$unit_id=$post['unit_id'];
			}elseif($get){
				$sub_topic_id=$get['sub_topic_id'];
				$audit_plan_id=$get['audit_plan_id'];
				$unit_id=$get['unit_id'];
			}

			$AuditPlanUnit = AuditPlanUnit::find()->where(['audit_plan_id'=>$audit_plan_id,'unit_id'=>$unit_id])->one();
			if($AuditPlanUnit!==null){
				if(!Yii::$app->userrole->isAuditReviewer($audit_plan_id) && !Yii::$app->userrole->isAuditor($AuditPlanUnit->id,1) && !Yii::$app->userrole->isAuditProjectLA($AuditPlanUnit->auditplan->audit_id) ){
					return $responsedata;
				}
			}else{
				return $responsedata;
			}
			


			$AuditPlanUnitExecutionChecklist = new AuditPlanUnitExecutionChecklist();
			$userData = Yii::$app->userdata->getData();
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$franchiseid=$userData['franchiseid'];
			$is_headquarters =$userData['is_headquarters'];
			$resource_access=$userData['resource_access'];
			
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
			
			$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();

			$executionAnswerChecklistQuery = "select execution_checklist.*,reviewer_comment.comment as reviewercomment,reviewer_comment.answer as revieweranswer from `tbl_audit_plan_unit` as plan_unit  
			INNER JOIN `tbl_audit_plan_unit_execution` as execution on execution.audit_plan_unit_id=plan_unit.id and execution.sub_topic_id in (".$sub_topic_id.") 
			INNER JOIN `tbl_audit_plan_unit_execution_checklist` as execution_checklist on execution.id=execution_checklist.audit_plan_unit_execution_id 
			

			LEFT JOIN `tbl_audit_plan_execution_checklist_review` AS checklist_review ON checklist_review.audit_plan_id = plan_unit.audit_plan_id 
			LEFT JOIN `tbl_audit_plan_execution_checklist_review_comment` as reviewer_comment ON  checklist_review.id = reviewer_comment.audit_plan_execution_checklist_review_id AND 
			reviewer_comment.execution_checklist_id = execution_checklist.id 
			where  plan_unit.audit_plan_id=".$audit_plan_id." 
			AND plan_unit.unit_id=".$unit_id." 
			GROUP BY execution_checklist.id";
			
			$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
			$command = $connection->createCommand($executionAnswerChecklistQuery);
			$resultanser = $command->queryAll();
			$checklistAnswerDataArr = [];
			if(count($resultanser)>0)
			{
				foreach($resultanser as $checklistanswerData)
				{
					$question_id = $checklistanswerData['question_id'];
					$checklistAnswerDataArr[$question_id] = [
						'answer'=>$checklistanswerData['answer'],
						'execution_checklist_id'=>$checklistanswerData['id'],
						'finding'=>$checklistanswerData['finding'],
						'file'=>$checklistanswerData['file'],
						'severity' => $checklistanswerData['severity'],
						'revieweranswer'=>$checklistanswerData['revieweranswer'],
						'reviewercomment'=>$checklistanswerData['reviewercomment']
						
					];
				}
			}
			
			$unit_process_ids=0;
			$unitProcessQuery = "SELECT GROUP_CONCAT(process_id) AS process_ids FROM `tbl_application_unit_process` WHERE unit_id=".$unit_id."";
			$unitProcessCommand = $connection->createCommand($unitProcessQuery);
			$unitProcessResult = $unitProcessCommand->queryOne();
			if($unitProcessResult !== false)
			{
				$unit_process_ids = $unitProcessResult['process_ids'];				
			}
			
			$unit_standard_ids=0;
			$unitStandardQuery = "SELECT GROUP_CONCAT(standard_id) AS standard_ids FROM `tbl_application_unit_standard` WHERE unit_id=".$unit_id."";
			$unitStandardCommand = $connection->createCommand($unitStandardQuery);
			$unitStandardResult = $unitStandardCommand->queryOne();
			if($unitStandardResult !== false)
			{
				$unit_standard_ids = $unitStandardResult['standard_ids'];				
			}

			$exequesmodel = new AuditExecutionQuestion();
			/*
			$executionChecklistQuery = "SELECT execution_checklist.answer as chk_answer,execution_checklist.finding as chk_finding,execution_checklist.severity as chk_severity,
			reviewer_comment.comment as reviewercomment,reviewer_comment.answer as revieweranswer,
			execution_checklist.file as chk_file,aeq.*,GROUP_CONCAT(DISTINCT aeqnc.audit_non_conformity_timeline_id SEPARATOR '@') AS non_conformity,GROUP_CONCAT(DISTINCT aeqf.question_finding_id SEPARATOR '@') AS question_findings 
			 FROM `tbl_application_unit` AS unit
			INNER JOIN `tbl_application_unit_process` AS uprs ON unit.id=uprs.unit_id AND unit.id=".$unit_id."
			INNER JOIN `tbl_application_unit_standard` AS ustd ON unit.id=ustd.unit_id
			INNER JOIN `tbl_audit_execution_question_process` AS aeqp ON uprs.process_id=aeqp.process_id
			INNER JOIN `tbl_audit_execution_question_standard` AS aeqs ON ustd.standard_id=aeqs.standard_id
			INNER JOIN `tbl_audit_execution_question` AS aeq ON aeqp.audit_execution_question_id=aeq.id 
			AND aeqs.audit_execution_question_id=aeq.id AND aeq.sub_topic_id in (".$sub_topic_id.") AND aeq.status =0 
			INNER JOIN `tbl_audit_execution_question_non_conformity` AS aeqnc ON aeq.id=aeqnc.audit_execution_question_id
			INNER JOIN `tbl_audit_execution_question_findings` as aeqf ON aeq.id=aeqf.audit_execution_question_id 

			LEFT JOIN `tbl_audit_plan_unit` as plan_unit on plan_unit.unit_id = unit.id AND plan_unit.audit_plan_id=".$audit_plan_id." 
			AND plan_unit.unit_id=".$unit_id." 
			LEFT JOIN `tbl_audit_plan_unit_execution` as execution on execution.audit_plan_unit_id=plan_unit.id and execution.sub_topic_id in (".$sub_topic_id.") 
			LEFT JOIN `tbl_audit_plan_unit_execution_checklist` as execution_checklist on execution.id=execution_checklist.audit_plan_unit_execution_id 
			AND aeq.id = execution_checklist.question_id 

			LEFT JOIN `tbl_audit_plan_execution_checklist_review` AS checklist_review ON checklist_review.audit_plan_id = plan_unit.audit_plan_id 
			LEFT JOIN `tbl_audit_plan_execution_checklist_review_comment` as reviewer_comment ON reviewer_comment.question_id=aeq.id 
			AND checklist_review.id = reviewer_comment.audit_plan_execution_checklist_review_id AND 
			reviewer_comment.execution_checklist_id = execution_checklist.id 
			
			GROUP BY aeq.id";
			*/
			
			$executionChecklistQuery = "SELECT aeq.*,GROUP_CONCAT(DISTINCT aeqnc.audit_non_conformity_timeline_id SEPARATOR '@') AS non_conformity,GROUP_CONCAT(DISTINCT aeqf.question_finding_id SEPARATOR '@') AS question_findings 
			 FROM `tbl_audit_execution_question` AS aeq			
			INNER JOIN `tbl_audit_execution_question_process` AS aeqp ON aeqp.audit_execution_question_id=aeq.id and aeqp.process_id in(".$unit_process_ids.")
			INNER JOIN `tbl_audit_execution_question_standard` AS aeqs ON aeqp.audit_execution_question_id=aeq.id and aeqs.standard_id in(".$unit_standard_ids.")
			AND aeqs.audit_execution_question_id=aeq.id AND aeq.sub_topic_id in (".$sub_topic_id.") AND aeq.status =0 
			INNER JOIN `tbl_audit_execution_question_non_conformity` AS aeqnc ON aeq.id=aeqnc.audit_execution_question_id
			INNER JOIN `tbl_audit_execution_question_findings` as aeqf ON aeq.id=aeqf.audit_execution_question_id 		 
			GROUP BY aeq.id";	
			
			$command = $connection->createCommand($executionChecklistQuery);
			$result = $command->queryAll();
			$checklistDataArr = [];
			if(count($result)>0)
			{
				foreach($result as $checklistData)
				{
					$checklistArr = [];
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

					//chk_file chk_answer  chk_finding chk_severity

					$question_id = $checklistData['id'];
					if(isset($checklistAnswerDataArr[$question_id])){
						$answerdata = $checklistAnswerDataArr[$question_id];

						$checklistArr['answer'] = $answerdata['answer'];					
						$checklistArr['finding'] = $answerdata['finding'];
						$checklistArr['severity'] = $answerdata['severity'];
						$checklistArr['file'] = $answerdata['file'];
						$checklistArr['execution_checklist_id'] = $answerdata['execution_checklist_id'];
						
						$checklistArr['revieweranswer'] = $answerdata['revieweranswer'];
						$checklistArr['revieweranswer_name'] = $answerdata['revieweranswer']?$AuditPlanUnitExecutionChecklist->answerList[$answerdata['revieweranswer']]:'';
						$checklistArr['reviewercomment'] = $answerdata['reviewercomment'];

					}else{
						$checklistArr['answer'] = '';					
						$checklistArr['finding'] = '';
						$checklistArr['severity'] = '';
						$checklistArr['file'] = '';

						$checklistArr['revieweranswer'] = '';
						$checklistArr['revieweranswer_name'] = '';
						$checklistArr['reviewercomment'] = '';
					}
					/*
					$checklistArr['answer'] = //$checklistData['chk_answer'];					
					$checklistArr['finding'] = //$checklistData['chk_finding'];
					$checklistArr['severity'] = $checklistData['chk_severity'];
					$checklistArr['file'] = //$checklistData['chk_file'];*/

					$checklistDataArr[]=$checklistArr;					
				}
			}			
			$responsedata=array();
			$responsedata['answerList']=array('1'=>'Yes','2'=>'No');
			$responsedata['questionList']=$checklistDataArr;		
		}
		return $responsedata;
	}

	public function actionGeneratePdf()
    {
		$unitWiseFindingsContent='';
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();

		if($data)
		{
			$auditmodel=new Audit();
			$modelaudit = Audit::find()->where(['id' => $data['audit_id']])->one();
			$auditplan = $modelaudit->auditplan;
			$auditncn = $modelaudit->auditncn;
			$auditInspection = $auditplan->auditplaninspection; 
			$application = $modelaudit->application;
			$applicationstd = $application->applicationstandard;
			$appStandardArr=array();

			if($modelaudit->audit_type == 2){
				$unapplicationstd = Yii::$app->globalfuns->getUnannoucedAuditStandard($data['audit_id']);
				if(count($unapplicationstd)>0)
				{
					foreach($unapplicationstd as $appstandard)
					{
						$appStandardArr[]=$appstandard['name'];
					}
				}
			}else{
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
			if(count($result)>0)
			{
				$dates = $result[0]['dates'];
			}
		
			$AuditPlanModel = AuditPlan::find()->where(['id' => $data['audit_plan_id']])->one();
			if($AuditPlanModel!==null)
			{
				$auditplanunitObj=$AuditPlanModel->auditplanunit;
				if(count($auditplanunitObj)>0)
				{
					$unitWiseFindingsContent.='
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
						<tr>
							<td style="text-align:center;font-weight:bold;width:20%;background:#2f4985;color: white;border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="5">'.$application->companyname.'</td>
						</tr>
						<tr>
							<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Certification Standard:</td>
							<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.implode(', ',$appStandardArr).'</td>
							<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Type of audit:</td>
							<td style="text-align:left;font-weight:bold;width:18%;" valign="middle" class="reportDetailLayoutInner">Initial Audit </td>
						</tr>
						<tr>
							<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Inspection date(s):</td>
							<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="4">'.$dates.'</td>
							
						</tr>
						<tr>
							<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Lead Inspector:</td>
							<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.$auditplan->user->first_name.' '.$auditplan->user->last_name.'</td>
							<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Inspection man-day:</td>
							<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$auditplan->actual_manday.'</td>
						</tr>
					</table>';

					 
					
						 

					
					 
						foreach($auditplanunitObj as $apUnit)
						{

							$unitWiseFindingsContent.= Yii::$app->globalfuns->getNCContent($apUnit);
						}
					 
					
				}				
			}	

			
			if($unitWiseFindingsContent!='')
			{
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
				
				$html.=$unitWiseFindingsContent;		

				$html.='<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;border:none;">	
					<tr>
						<td class="reportDetailLayoutInner" style="border:none;">
						<p style="font-weight:bold;">Statement of Confidentiality</p>
						<p>GCL agrees not to disclose any information relating to the client’s business or affairs except information, which is in their possession after the receipt of an enquiry for registration with GCL. Where GCL are required to disclose information to a third party either by law the client shall be informed of the information as required by law. The only other exception to confidentiality is that GCL has right to exchange operators’ information with other Certification Bodies, accreditation bodies, TE and the Global Standard gGmbH to verify the authenticity of the information.. Such information can be validated on the GCL web site using the registration number.</p>
						</td>
					</tr>
					<tr>
						<td class="reportDetailLayoutInner" style="border:none;">
						<p style="font-weight:bold;margin-top:20px;">Declaration of Impartiality</p>
						<p>Prior to the Audit/audit taking place the client was written to with background details of all inspector team members. No objections was made by the client to any member on the team and confirmation was made by the client that no Audit team member has ever worked directly for or on behalf of any other person or organization in any capacity</p>ln 
						</td>
					</tr>
					<tr>
						<td class="reportDetailLayoutInner" style="border:none;">
						<p style="font-weight:bold;margin-top:20px;">Basis of Findings</p>
						<p>Files and records were sampled as well as interviews with staff at all levels as well as the evidence collected with the interviews during this visit were based on random sampling techniques to provide the GCL Lead Inspector with confidence that the product system has been effectively implemented and/or maintained. Due to the nature of sampling it could be that there are issues within the product system that are in existence but not discovered by the Audit. If such issues are known they action is required to be taken by the company.</p><br>
						<p>This audit report is the property of GCL International LTD and cannot be edited or copied without the express permission of GCL.</p>
						</td>
					</tr>
				</table>';
				$html.= '<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:25px;border:none;border-spacing: 20px;border-collapse: separate;">	
					<tr>
						<td class="reportDetailLayoutInner" style="height:80px;"></td>
						<td class="reportDetailLayoutInner" style="height:80px;"></td>
					</tr>
					<tr>
						<td class="reportDetailLayoutInner" style="border:none;text-align:center;">Signature of the Operator</td>
						<td class="reportDetailLayoutInner" style="border:none;text-align:center;">Signature of the Auditor</td>
					</tr>
				</table>';		

				$mpdf->WriteHTML($html);
				$mpdf->Output('findings.pdf','D');								
			}
			
			/*		
			//$data['audit_unit_id'] = 8;
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			header('Access-Control-Max-Age: 1000');
			header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

			$model = AuditPlanUnitExecutionChecklist::find()->Where(['audit_plan_unit_execution_id' => $data['audit_id']])->all();
			
			$html='';
			$mpdf = new \Mpdf\Mpdf();

			if($model !== null)
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
					<img src="http://www.gcldemo.com.php73-39.lan3-1.websitetestlink.com/backend/web/images/header-img.jpg" border="0">						
				</div>';
				foreach($model as $val)
				{
					$answer = ($val->answer!=1)?'No':'Yes';
					$html.='
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
						<tr>
							<td style="text-align:center;font-weight:bold;width:20%;background:#2f4985;color: white;border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="6">Unit Findings - </td>
						</tr>
						<tr>
							<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">SI.No</td>
							<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Question</td>	
							<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Answer</td>
							<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Finding</td>		
							<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Severity</td>	
							<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Finding Type</td>		  
						</tr>
					
						<tr>
							<td style="text-align:center;" valign="middle" class="reportDetailLayoutInner">1</td>
							<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$val->question.'</td>
							<td style="text-align:center" valign="middle" class="reportDetailLayoutInner">'.$answer.'</td>
							<td style="text-align:left" valign="middle" class="reportDetailLayoutInner">'.$val->finding.'</td>
							<td style="text-align:center" valign="middle" class="reportDetailLayoutInner">'.$val->auditnonconformitytimeline->name.'</td>
							<td style="text-align:center" valign="middle" class="reportDetailLayoutInner">'.$auditmodel->arrFindingType[$val->finding_type].'</td>
						</tr>
					</table>';
				}
				

				$mpdf->WriteHTML($html);
				$mpdf->Output('findings.pdf','D');	
			}
			*/			
		}
	}

	public function actionGenerateAuditreport()
    {
		$auditreportContent='';
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();

		if($data)
		{
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			$auditmodel=new Audit();
			$modelaudit = Audit::find()->where(['id' => $data['audit_id']])->one();
			$auditplan = $modelaudit->auditplan;
			$auditInspection = $auditplan->auditplaninspection; 
			$application = $modelaudit->application;
			// $applicationstd = $application->applicationstandard;
			// $appStandardArr=array();
			// if(count($applicationstd)>0)
			// {
			// 	foreach($applicationstd as $appstandard)
			// 	{
			// 		$appStandardArr[]=$appstandard->standard->name;
			// 	}
			// }
			$standard = Standard::find()->where(['id'=>$data['standard_id']])->one();

			$connection = Yii::$app->getDb();	
			/*			
			$command = $connection->createCommand("SELECT GROUP_CONCAT(DISTINCT `date` ORDER BY `date` ASC SEPARATOR ', ') AS dates FROM `tbl_audit_plan_inspection_plan` WHERE audit_plan_inspection_id=$auditInspection->id GROUP BY audit_plan_inspection_id ");
			
			
			$result = $command->queryAll();
			if(count($result)>0)
			{
				$dates = $result[0]['dates'];
			}
			*/
			
			$unitID = $data['unit_id'];						
			
			$auditReportQuery = "SELECT checklist_std.clause_no AS `clause_no`,checklist_std.clause AS `clause`,plan_unit_execution_checklist.question AS `question`,plan_unit_execution_checklist.finding AS `comment`, non_conformity_timeline.name AS `severity` FROM `tbl_audit` AS audit 
			INNER JOIN `tbl_audit_plan` AS plan ON plan.audit_id=audit.id AND audit.id='".$data['audit_id']."'";
			
			$auditReportQuery.= " INNER JOIN `tbl_audit_plan_unit` AS plan_unit ON plan_unit.audit_plan_id=plan.id";
			
			if($unitID!='')
			{
				$auditReportQuery.= " and plan_unit.id=".$unitID;
			}
			
			$auditReportQuery.= " INNER JOIN `tbl_audit_plan_unit_execution` AS plan_unit_execution ON plan_unit_execution.audit_plan_unit_id=plan_unit.id 
			INNER JOIN `tbl_audit_plan_unit_execution_checklist` AS plan_unit_execution_checklist ON plan_unit_execution_checklist.audit_plan_unit_execution_id=plan_unit_execution.id 
			LEFT JOIN `tbl_audit_non_conformity_timeline` AS non_conformity_timeline ON plan_unit_execution_checklist.severity=non_conformity_timeline.id 
			INNER JOIN `tbl_audit_plan_unit_execution_checklist_standard` AS checklist_std ON checklist_std.audit_plan_unit_execution_checklist_id=plan_unit_execution_checklist.id AND checklist_std.standard_id='".$data['standard_id']."'";	
			$reportcommand = $connection->createCommand($auditReportQuery);
			/*echo "SELECT checklist_std.clause_no AS `clause_no`,checklist_std.clause AS `clause`,plan_unit_execution_checklist.question AS `question`,plan_unit_execution_checklist.finding AS `comment`, non_conformity_timeline.name AS `severity` FROM `tbl_audit` AS audit INNER JOIN `tbl_audit_plan` AS plan ON plan.audit_id=audit.id AND audit.id='".$data['audit_id']."' INNER JOIN `tbl_audit_plan_unit` AS plan_unit ON plan_unit.audit_plan_id=plan.id INNER JOIN `tbl_audit_plan_unit_execution` AS plan_unit_execution ON plan_unit_execution.audit_plan_unit_id=plan_unit.id INNER JOIN `tbl_audit_plan_unit_execution_checklist` AS plan_unit_execution_checklist ON plan_unit_execution_checklist.audit_plan_unit_execution_id=plan_unit_execution.id LEFT JOIN `tbl_audit_non_conformity_timeline` AS non_conformity_timeline ON plan_unit_execution_checklist.severity=non_conformity_timeline.id INNER JOIN `tbl_audit_plan_unit_execution_checklist_standard` AS checklist_std ON checklist_std.audit_plan_unit_execution_checklist_id=plan_unit_execution_checklist.id AND checklist_std.standard_id='".$data['standard_id']."'";*/

			$reportresult = $reportcommand->queryAll();
			if(count($reportresult)>0)
			{
				/*
				$auditreportContent.='
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
						<tr>
							<td style="text-align:center;font-weight:bold;width:20%;background:#2f4985;color: white;border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="5">'.$application->companyname.'</td>
						</tr>
						<tr>
							<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Certification Standard:</td>
							<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.$standard->name.'</td>
							<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Type of audit:</td>
							<td style="text-align:left;font-weight:bold;width:18%;" valign="middle" class="reportDetailLayoutInner">Initial Audit </td>
						</tr>
						<tr>
							<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Inspection date(s):</td>
							<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="4">'.$dates.'</td>
							
						</tr>
						<tr>
							<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Lead Inspector:</td>
							<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.$auditplan->user->first_name.' '.$auditplan->user->last_name.'</td>
							<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Inspection man-day:</td>
							<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$auditplan->actual_manday.'</td>
						</tr>
					</table>';
					*/
															
					$apUnit = AuditPlanUnit::find()->where(['id' => $unitID])->one();
					if($apUnit !== null)
					{
						$standardName='';						
						if($standard!== null)
						{
							$standardName=$standard->name;
						}
						
						$command = $connection->createCommand("SELECT MIN(unit_date.date) AS start_date,MAX(unit_date.date) AS end_date FROM  `tbl_audit_plan_unit` AS  plan_unit INNER JOIN `tbl_audit_plan_unit_date` AS unit_date ON plan_unit.id=unit_date.audit_plan_unit_id  WHERE plan_unit.id=".$unitID." ");
						$result = $command->queryOne();
						if($result !== false)
						{
							$start_date = date($date_format,strtotime($result['start_date']));
							$end_date = date($date_format,strtotime($result['end_date']));
						}

						$unitauditors = [];
						if(count($apUnit->unitauditors)>0){
							foreach($apUnit->unitauditors as $uauditors){
								$unitauditors[] = $uauditors->user->first_name.' '.$uauditors->user->last_name;
							}
							$unitauditors = implode($unitauditors);
						}

						$techexpert = $apUnit->unittechnicalexpert?$apUnit->unittechnicalexpert->first_name.' '.$apUnit->unittechnicalexpert->last_name:'NA';
						$translator = $apUnit->unittranslator?$apUnit->unittranslator->first_name.' '.$apUnit->unittranslator->last_name:'NA';
						$address = $apUnit->unitdata->address;

						$customer_number = Yii::$app->globalfuns->getCustomerNumber($apUnit->app_id);
						
						$applicationUnit = $apUnit->unitdata;
						$unitName = $applicationUnit->name;
						$auditreportContent.='
						<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
							<tr>
								<td style="text-align:center;font-weight:bold;background:#2f4985;color: white;border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="6">'.$application->companyname.'</td>								
							</tr>
							<tr>
								<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Client /Operator:</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" >'.$unitName.'</td>
								<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Operator ID:</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$customer_number.'</td>
								<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Type of audit:</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">Initial Audit </td>
							</tr>
							<tr>						
								<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Standard:</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$standardName.'</td>								
								<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Client Address:</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="3">'.$address.'</td>								 
							</tr>
							<tr>
								<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Audit Start Date:</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.$start_date.'</td>
								<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Audit End Date:</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.$end_date.'</td>
							</tr>
							<tr>
								<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Lead Auditor:</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$auditplan->user->first_name.' '.$auditplan->user->last_name.'</td>
								<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Auditor(s):</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="3">'.$unitauditors.'</td>
							</tr>
							<tr>
								<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Technical Expert:</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.$techexpert.'</td>
								<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Translator:</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.$translator.'</td>
							</tr>
						</table>';

						$unitSNo=1;
						$auditreportContent.='
							<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
								<tr>
									<td style="text-align:center;font-weight:bold;background:#2f4985;color: white;border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="6">Audit Report</td>
								</tr>';
						
						$auditreportContent.='										
								<tr>
									<td style="text-align:center;font-weight:bold;" width="8%" valign="middle" class="reportDetailLayoutInner">S.No</td>
									<td style="text-align:left;font-weight:bold;"  width="10%" valign="middle" class="reportDetailLayoutInner">Clause No.</td>
									<td style="text-align:left;font-weight:bold;" width="26%" valign="middle" class="reportDetailLayoutInner">Clause</td>
									<td style="text-align:left;font-weight:bold;" width="26%" valign="middle" class="reportDetailLayoutInner">Question</td>	
											
									<td style="text-align:left;font-weight:bold;" width="10%" valign="middle" class="reportDetailLayoutInner">Severity</td>	
									<td style="text-align:left;font-weight:bold;"  width="20%" valign="middle" class="reportDetailLayoutInner">Comment</td>		  
								</tr>';
										
								if(count($reportresult)>0)
								{
									foreach($reportresult as $result)
									{
										$severity = $result['severity']?$result['severity']:"NA";
										$auditreportContent.='										
											<tr>
												<td style="text-align:center;" valign="top" class="reportDetailLayoutInner" >'.$unitSNo.'</td>
												<td style="text-align:left;" valign="top" class="reportDetailLayoutInner">'.$result['clause_no'].'</td>
												<td style="text-align:left" valign="top" class="reportDetailLayoutInner" >'.$result['clause'].'</td>
												<td style="text-align:left;" valign="top" class="reportDetailLayoutInner">'.$result['question'].'</td>
												
												<td style="text-align:left" valign="top" class="reportDetailLayoutInner" >'.$severity.'</td>
												<td style="text-align:left" valign="top" class="reportDetailLayoutInner"  >'.$result['comment'].'</td>
											</tr>';
										
										$unitSNo++;	
									}	
									
									

								}
								else
								{
									$auditreportContent.='										
											<tr><td style="text-align:center;" valign="top" class="reportDetailLayoutInner">No Findings Found</td>
											</tr>';
								}
																
							
						
								$auditreportContent.='</table>';
					}			
				
			}
			else
			{
				$auditreportContent.='
						<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
							<tr>
								<td style="text-align:center;font-weight:bold;width:20%;background:#2f4985;color: white;border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="6">Audit Report</td>
							</tr>
							<tr>
								<td style="text-align:center;font-weight:bold;width:20%;color: white;border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="6">No findings found.</td>
							</tr>
						</table>';
			}				
		

			
			if($auditreportContent!='')
			{
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
				
				$html.= $auditreportContent;				

				$mpdf->WriteHTML($html);
				$mpdf->Output('Audit-report.pdf','D');
			}
			
		
		}
	}

	public function actionGeneratePdfUnit()
    {
		$unitWiseFindingsContent='';
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');

		if($data)
		{
			$auditmodel=new Audit();
			$modelaudit = Audit::find()->where(['id' => $data['audit_id']])->one();
			$auditplan = $modelaudit->auditplan;
			$auditInspection = $auditplan->auditplaninspection; 
			$application = $modelaudit->application;
			$applicationstd = $application->applicationstandard;
			$appStandardArr=array();


			if($modelaudit->audit_type == 2){
				$unapplicationstd = Yii::$app->globalfuns->getUnannoucedAuditStandard($data['audit_id']);
				if(count($unapplicationstd)>0)
				{
					foreach($unapplicationstd as $appstandard)
					{
						$appStandardArr[]=$appstandard['name'];
					}
				}
			}else{
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
			if(count($result)>0)
			{
				$dates = $result[0]['dates'];
			}


			
		
			
				
			$apUnit = AuditPlanUnit::find()->where(['id' => $data['audit_plan_unit_id']])->one();
			if($apUnit !== null)
			{
				$command = $connection->createCommand("SELECT MIN(unit_date.date) AS start_date,MAX(unit_date.date) AS end_date FROM  `tbl_audit_plan_unit` AS  plan_unit INNER JOIN `tbl_audit_plan_unit_date` AS unit_date ON plan_unit.id=unit_date.audit_plan_unit_id  WHERE plan_unit.app_id=".$apUnit->app_id." ");
				$result = $command->queryOne();
				if($result !== false)
				{
					$start_date = date($date_format,strtotime($result['start_date']));
					$end_date = date($date_format,strtotime($result['end_date']));
				}

				$unitauditors = [];
				if(count($apUnit->unitauditors)>0){
					foreach($apUnit->unitauditors as $uauditors){
						$unitauditors[] = $uauditors->user->first_name.' '.$uauditors->user->last_name;
					}
					$unitauditors = implode($unitauditors);
				}

				$techexpert = $apUnit->unittechnicalexpert?$apUnit->unittechnicalexpert->first_name.' '.$apUnit->unittechnicalexpert->last_name:'NA';
				$translator = $apUnit->unittranslator?$apUnit->unittranslator->first_name.' '.$apUnit->unittranslator->last_name:'NA';
				$address = $apUnit->unitdata->address;

				$customer_number = Yii::$app->globalfuns->getCustomerNumber($apUnit->app_id);
				/*
				$unitWiseFindingsContent.='
				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
					<tr>
						<td style="text-align:center;font-weight:bold;width:20%;background:#2f4985;color: white;border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="5">'.$application->companyname.'</td>
					</tr>
					<tr>
						<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Certification Standard:</td>
						<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.implode(', ',$appStandardArr).'</td>
						<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Type of audit:</td>
						<td style="text-align:left;font-weight:bold;width:18%;" valign="middle" class="reportDetailLayoutInner">Initial Audit </td>
					</tr>
					<tr>
						<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Inspection date(s):</td>
						<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="4">'.$dates.'</td>
						
					</tr>
					<tr>
						<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Lead Inspector:</td>
						<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.$auditplan->user->first_name.' '.$auditplan->user->last_name.'</td>
						<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Inspection man-day:</td>
						<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$auditplan->actual_manday.'</td>
					</tr>
				</table>';
				*/
				$applicationUnit = $apUnit->unitdata;
				$unitName = $applicationUnit->name;
				$unitWiseFindingsContent.='
				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
					<tr>
						<td style="text-align:center;font-weight:bold;background:#2f4985;color: white;border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="6">'.$application->companyname.'</td>
					</tr>
					<tr>
						<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Client /Operator:</td>
						<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" >'.$unitName.'</td>
						<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Operator ID:</td>
						<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$customer_number.'</td>

						<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Type of audit:</td>
						<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">Initial Audit </td>
					</tr>
					<tr>
						<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Client Address:</td>
						<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="5">'.$address.'</td>
						 
					</tr>
					<tr>
						<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Audit Start Date:</td>
						<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.$start_date.'</td>
						<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Audit End Date:</td>
						<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.$end_date.'</td>
					</tr>
					<tr>
						<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Lead Auditor:</td>
						<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$auditplan->user->first_name.' '.$auditplan->user->last_name.'</td>
						<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Auditor(s):</td>
						<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="3">'.$unitauditors.'</td>
					</tr>
					<tr>
						<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Technical Expert:</td>
						<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.$techexpert.'</td>
						<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Translator:</td>
						<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.$translator.'</td>
					</tr>
				</table>';

				//foreach($auditplanunitObj as $apUnit)
				//{

					$unitWiseFindingsContent.= Yii::$app->globalfuns->getNCContent($apUnit);
					/*
					$ncnUnit = $apUnit->auditunitncn;

					
					$unitWiseFindingsContent.='<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;border:none;">';
					
					if($ncnUnit->effectiveness_of_corrective_actions!='')
					{
						$unitWiseFindingsContent.='<tr>
							<td class="reportDetailLayoutInner" style="border:none;">
							<p style="font-weight:bold;">Previously identified Non-conformities and Effectiveness of the Corrective actions:</p>
							<p>'.$ncnUnit->effectiveness_of_corrective_actions.'</p>
							</td>
						</tr>';
					}

					if($ncnUnit->audit_team_recommendation!='')
					{
						$unitWiseFindingsContent.='<tr>
							<td class="reportDetailLayoutInner" style="border:none;">
							<p style="font-weight:bold;margin-top:20px;">Audit Team Recommendation:</p>
							<p>'.$ncnUnit->audit_team_recommendation.'</p>
							</td>
						</tr>';
					}

					if($ncnUnit->summary_of_evidence!='')
					{
						$unitWiseFindingsContent.='<tr>
							<td class="reportDetailLayoutInner" style="border:none;">
							<p style="font-weight:bold;margin-top:20px;">Summary of evidence relating to the capability of client and its system to meet applicable requirements and expected outcomes:</p>
							<p>'.$ncnUnit->summary_of_evidence.'</p>
							</td>
						</tr>';
					}

					if($ncnUnit->potential_high_risk_situations!='')
					{
						$unitWiseFindingsContent.='<tr>
						<td class="reportDetailLayoutInner" style="border:none;">
						<p style="font-weight:bold;margin-top:20px;">Any Potential high-risk situations:</p>
						<p>'.$ncnUnit->potential_high_risk_situations.'</p>
						</td>
						</tr>';
					}

					if($ncnUnit->entities_and_processes_visited!='')
					{
						$unitWiseFindingsContent.='<tr>
						<td class="reportDetailLayoutInner" style="border:none;">
						<p style="font-weight:bold;margin-top:20px;">Entities and Processes visited (including facilities and subcontractors):</p>
						<p>'.$ncnUnit->entities_and_processes_visited.'</p>
						</td>
						</tr>';
					}

					if($ncnUnit->people_interviewed!='')
					{
						$unitWiseFindingsContent.='<tr>
						<td class="reportDetailLayoutInner" style="border:none;">
						<p style="font-weight:bold;margin-top:20px;">People interviewed:</p>
						<p>'.$ncnUnit->people_interviewed.'</p>
						</td>
						</tr>';
					}

					if($ncnUnit->type_of_documents_reviewed!='')
					{
						$unitWiseFindingsContent.='<tr>
						<td class="reportDetailLayoutInner" style="border:none;">
						<p style="font-weight:bold;margin-top:20px;">Type of documents reviewed:</p>
						<p>'.$ncnUnit->type_of_documents_reviewed.'</p>
						</td>
						</tr>';
					}

					$unitWiseFindingsContent.='</table>';
						 




					$nccount = 0;
					$unitSNo=1;
					$applicationUnit = $apUnit->unitdata;
					$unitName = $applicationUnit->name;
					$unitWiseFindingsContent.='
						<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
							<tr>
								<td style="text-align:center;font-weight:bold; background:#2f4985;color: white;border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="6">Unit Findings - '.($unitName?$unitName:'NA').'</td>
							</tr>';
					$unitexecutionObj=$apUnit->unitexecution;
					if(count($unitexecutionObj)>0)
					{
						$unitWiseFindingsContent.='										
						<tr>
							<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner" width="8%" >S.No</td>
							<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner" width="20%">Clause No.</td>	
							<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner" width="24%">Clause</td>	
							<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner" width="22%">Findings</td>		
							<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner" width="10%">Severity</td>	
							<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner" width="10%">Finding Type</td>		  
						</tr>';
									
						foreach($unitexecutionObj as $uExecution)
						{								
							$executionlistnoncomformityObj=$uExecution->executionlistnoncomformity;
							if(count($executionlistnoncomformityObj)>0)
							{
								$nccount = 1;
								foreach($executionlistnoncomformityObj as $noncomformityList)
								{
									$answer = ($noncomformityList->answer!=1)?'No':'Yes';
									
									$arrStdClause=array();
									//$arrStdClause[]=array('clause_no'=>'clause_no1','clause'=>'clause1');
									//$arrStdClause[]=array('clause_no'=>'clause_no2','clause'=>'clause2');
									//$arrStdClause[]=array('clause_no'=>'clause_no3','clause'=>'clause3');
									$auditexecutioncheckliststandardObj=$noncomformityList->auditexecutioncheckliststandard;
									if(count($auditexecutioncheckliststandardObj)>0)
									{
										foreach($auditexecutioncheckliststandardObj as $auditexecutioncheckliststd)
										{
											$questionstandard=$auditexecutioncheckliststd->auditexecutionquestionstandard;
											if($questionstandard!==null)
											{
												$arrStdClause[]=array('clause_no'=>$questionstandard->clause_no,'clause'=>$questionstandard->clause);
											}
										}
									}
									
									$stdClauseCnt=0;
									$firstClauseNo='';
									$firstClause='';
									$unitWiseFindingsWithClauseContent='';
									$clausecount=count($arrStdClause);
									foreach($arrStdClause as $vals)
									{
										if($stdClauseCnt==0)
										{
											$firstClauseNo=$vals['clause_no'];
											$firstClause=$vals['clause'];
										}else{
											$unitWiseFindingsWithClauseContent.='<tr><td style="text-align:left;" valign="top" class="reportDetailLayoutInner">'.$vals['clause_no'].'</td><td style="text-align:left;" valign="top" class="reportDetailLayoutInner">'.$vals['clause'].'</td></tr>';
										}
										$stdClauseCnt++;
									}
									
									$unitWiseFindingsContent.='										
										<tr>
											<td style="text-align:center;" valign="top" class="reportDetailLayoutInner" rowspan="'.$clausecount.'">'.$unitSNo.'</td>
											<td style="text-align:left;" valign="top" class="reportDetailLayoutInner">'.$firstClauseNo.'</td>
											<td style="text-align:left;" valign="top" class="reportDetailLayoutInner">'.$firstClause.'</td>
											<td style="text-align:left" valign="top" class="reportDetailLayoutInner" rowspan="'.$clausecount.'">'.$noncomformityList->finding.'</td>
											<td style="text-align:center" valign="top" class="reportDetailLayoutInner" rowspan="'.$clausecount.'">'.$noncomformityList->auditnonconformitytimeline->name.'</td>
											<td style="text-align:center" valign="top" class="reportDetailLayoutInner"  rowspan="'.$clausecount.'">'.$auditmodel->arrFindingType[$noncomformityList->finding_type].'</td>
										</tr>';
									$unitWiseFindingsContent.=$unitWiseFindingsWithClauseContent;	
									
									$unitSNo++;	
								}									
							}
						}
					}else{
						$unitWiseFindingsContent.='
						<tr>
							<td style="text-align:center;font-weight:bold;width:20%; border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="6">No findings found.</td>
						</tr>';
					}
					if($nccount ==0){
						$unitWiseFindingsContent.='
						<tr>
							<td style="text-align:center;font-weight:bold;width:20%;border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="6">No findings found.</td>
						</tr>';
					}
					$unitWiseFindingsContent.='</table>';	
					*/					
				//}
			}				
		

			
			if($unitWiseFindingsContent!='')
			{
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
				
				$html.= $unitWiseFindingsContent;				

				$mpdf->WriteHTML($html);
				$mpdf->Output('findings.pdf','D');
			}
			
		
		}
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

		
		$appID = $data['app_id'];
		$unitID = $data['unit_id'];
		$auditcompleted = false;
		$actiontype = isset($data['actiontype'])?$data['actiontype']:'';
		$applicableforms = [];

		
		if($actiontype == 'view'){
			/*
			$result = $unitsubtopics = Yii::$app->globalfuns->getCurrentSubtopic($unitID);//$data['sub_topic_id'];
			$sub_topic_id = [];
			if(count($result)>0){
				foreach($result as $subdata){
					$sub_topic_id[] =$subdata['id'];
				}
			}
			*/
			$sub_topic_id = Yii::$app->globalfuns->getCurrentSubtopicIds($unitID);

			$auditID = $data['audit_id'];
			$Audit = Audit::find()->where(['id' => $auditID])->one();
			//if($auditID)
			if($Audit !== null){
				$audit_type = 1;
				$audit_type = $Audit->audit_type;
				$applicableforms['audit_type'] = $audit_type;
				
				if($Audit->status >= $Audit->arrEnumStatus['audit_completed']){
					$auditcompleted = true;


					$environmentliststatus = false;
					
					
					$Application = Application::find()->where(['id'=>$appID])->one();
					
					
					$applicableforms['clientinformation_list'] = true; //$clientformstatus;

					//Starts
					if($unitID && $unitID>0){
						$ApplicationUnit = ApplicationUnit::find()->where(['id'=>$unitID])->all();
					}else{
						$ApplicationUnit = ApplicationUnit::find()->where(['app_id'=>$appID])->all();
					}
					//$ApplicationUnit = ApplicationUnit::find()->where(['app_id'=>$appID])->all();
					if(count($ApplicationUnit)>0){
						foreach($ApplicationUnit as $appunit){
							$unitID = $appunit->id;
							$chkdata = ['unit_id'=>$unitID];
							$chkdata['report_name'] = 'environment_list';
							
							$AuditReportEnvironment = AuditReportEnvironment::find()->where(['app_id'=>$appID, 'unit_id'=>$unitID])->one();
							$AuditReportApplicableDetails = AuditReportApplicableDetails::find()->where(['unit_id'=>$unitID, 'report_name'=>'environment_list' ])->one();
							if($AuditReportEnvironment !== null || $AuditReportApplicableDetails !== null){
								$formstatus = true;
							}else{
								$formstatus = false;
							}
							$applicableforms[$unitID]['environment_list'] = $formstatus;
							if($formstatus){
								$environmentliststatus = true;
							}


							//$chkdata = ['unit_id'=>$Application->applicationscopeholder->id,'report_name'=>'clientinformation_list'];
							$AuditReportClientInformationGeneralInfo = AuditReportClientInformationGeneralInfo::find()->where(['app_id'=>$appID])->one();
							if($AuditReportClientInformationGeneralInfo !== null){
								$clientformstatus = true;
							}else{
								$clientformstatus = false;
							}
							$applicableforms[$unitID]['clientinformation_list'] = $clientformstatus;



							//$chkdata['report_name'] = 'chemical_list';
							//$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
							$AuditReportChemicalList = AuditReportChemicalList::find()->where(['unit_id'=>$unitID])->one();
							$AuditReportApplicableDetails = AuditReportApplicableDetails::find()->where(['unit_id'=>$unitID, 'report_name'=>'chemical_list' ])->one();
							if($AuditReportChemicalList !== null || $AuditReportApplicableDetails !== null){
								$formstatus = true;
							}else{
								$formstatus = false;
							}
							$applicableforms[$unitID]['chemical_list'] = $formstatus;

							
							//$chkdata['report_name'] = 'sampling_list';
							//$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
							$AuditReportSampling = AuditReportSampling::find()->where(['unit_id'=>$unitID])->one();
							$AuditReportApplicableDetails = AuditReportApplicableDetails::find()->where(['unit_id'=>$unitID, 'report_name'=>'sampling_list' ])->one();
							if($AuditReportSampling !== null || $AuditReportApplicableDetails !== null){
								$formstatus = true;
							}else{
								$formstatus = false;
							}
							$applicableforms[$unitID]['sampling_list'] = $formstatus;
							

							
							//$chkdata['report_name'] = 'attendance_list';
							//$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
							$AuditReportSampling = AuditReportAttendanceSheet::find()->where(['unit_id'=>$unitID])->one();
							$AuditReportApplicableDetails = AuditReportApplicableDetails::find()->where(['unit_id'=>$unitID, 'report_name'=>'attendance_list' ])->one();
							if($AuditReportSampling !== null || $AuditReportApplicableDetails!== null){
								$formstatus = true;
							}else{
								$formstatus = false;
							}
							$applicableforms[$unitID]['attendance_list'] = $formstatus;


							
							//$chkdata['report_name'] = 'interview_list';
							//$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
							$AuditReportInterviewEmployees = AuditReportInterviewEmployees::find()->where(['unit_id'=>$unitID])->one();
							$AuditReportApplicableDetails = AuditReportApplicableDetails::find()->where(['unit_id'=>$unitID, 'report_name'=>'interview_list' ])->one();
							if($AuditReportInterviewEmployees !== null || $AuditReportApplicableDetails!== null){
								$formstatus = true;
							}else{
								$formstatus = false;
							}
							$applicableforms[$unitID]['interview_list'] = $formstatus;

							
							//$chkdata['report_name'] = 'livingwage_list';
							//$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
							$AuditReportLivingWageRequirementReview = AuditReportLivingWageRequirementReview::find()->where(['unit_id'=>$unitID])->one();
							$AuditReportLivingWageFamilyExpenses = AuditReportLivingWageFamilyExpenses::find()->where(['unit_id'=>$unitID])->one();
							$AuditReportApplicableDetails = AuditReportApplicableDetails::find()->where(['unit_id'=>$unitID, 'report_name'=>'livingwage_list' ])->one();
							if($AuditReportLivingWageRequirementReview !== null || $AuditReportLivingWageFamilyExpenses !== null || $AuditReportApplicableDetails!== null){
								$formstatus = true;
							}else{
								$formstatus = false;
							}
							$applicableforms[$unitID]['livingwage_list'] = $formstatus;

							
							//$chkdata['report_name'] = 'qbs';
							//$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
							$AuditReportQbsScopeHolder = AuditReportQbsScopeHolder::find()->where(['unit_id'=>$unitID])->one();
							if($AuditReportQbsScopeHolder !== null){
								$formstatus = true;
							}else{
								$formstatus = false;
							}
							$applicableforms[$unitID]['qbs'] = $formstatus;


							$AuditReportNcnReport = AuditReportNcnReport::find()->where(['unit_id'=>$unitID])->one();
							if($AuditReportNcnReport !== null){
								$formstatus = true;
							}else{
								$formstatus = false;
							}
							$applicableforms[$unitID]['ncnreport'] = $formstatus;

						}
					}
					$applicableforms['environment_list'] = $environmentliststatus;



					
				}
			}
			


		}else{
			$sub_topic_id = $data['sub_topic_id'];
		}
		

		if(!$auditcompleted){
			$applicableforms = [];

			$auditID = $data['audit_id'];
			$Audit = Audit::find()->where(['id' => $auditID])->one();
			$audit_type = 1;
			if($Audit !== null){
				$audit_type = $Audit->audit_type;
				$applicableforms['audit_type'] = $audit_type;
			}
			$applicableforms['audit_type'] = $audit_type;


			
			$environmentliststatus = false;
		
		
			$Application = Application::find()->where(['id'=>$appID])->one();
			//$chkdata = ['unit_id'=>$Application->applicationscopeholder->id,'report_name'=>'clientinformation_list'];
			//$clientformstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
			
			$applicableforms['clientinformation_list'] = true;//$clientformstatus;









			//Starts
			if($unitID && $unitID>0){
				$ApplicationUnit = ApplicationUnit::find()->where(['id'=>$unitID])->all();
			}else{
				$ApplicationUnit = ApplicationUnit::find()->where(['app_id'=>$appID])->all();
			}
			//$ApplicationUnit = ApplicationUnit::find()->where(['app_id'=>$appID])->all();
			if(count($ApplicationUnit)>0){
				foreach($ApplicationUnit as $appunit){
					$unitID = $appunit->id;
					$chkdata = ['unit_id'=>$unitID,'sub_topic_id'=>$sub_topic_id];


					if($audit_type !=2){
						$chkdata['report_name'] = 'environment_list';
						$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
						$applicableforms[$appunit->id]['environment_list'] = $formstatus;
						if($formstatus){
							$environmentliststatus = true;
						}


						//$chkdata = ['unit_id'=>$Application->applicationscopeholder->id,'report_name'=>'clientinformation_list'];
						$chkdata['report_name'] = 'clientinformation_list';
						$clientformstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
						$applicableforms[$appunit->id]['clientinformation_list'] = $clientformstatus;

						$chkdata['report_name'] = 'chemical_list';
						$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
						$applicableforms[$appunit->id]['chemical_list'] = $formstatus;

						
						$chkdata['report_name'] = 'sampling_list';
						$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
						$applicableforms[$appunit->id]['sampling_list'] = $formstatus;
						

						
						$chkdata['report_name'] = 'attendance_list';
						$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
						$applicableforms[$appunit->id]['attendance_list'] = $formstatus;


						
						$chkdata['report_name'] = 'interview_list';
						$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
						$applicableforms[$appunit->id]['interview_list'] = $formstatus;

						
						$chkdata['report_name'] = 'livingwage_list';
						$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
						$applicableforms[$appunit->id]['livingwage_list'] = $formstatus;

						
						$chkdata['report_name'] = 'qbs';
						$formstatus = Yii::$app->globalfuns->getReportsAccessible($chkdata);
						$applicableforms[$appunit->id]['qbs'] = $formstatus;
					}
					$applicableforms[$appunit->id]['ncnreport'] = true;
				}
			}
			$applicableforms['environment_list'] = $environmentliststatus;
			
			
		}
		$applicableforms['checklist'] = true;
		$applicableforms['audit_report'] = true;
		$applicableforms['audit_ncn_report'] = true;

		 




		
		return $this->asJson($applicableforms);
	}
}