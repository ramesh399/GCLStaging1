<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\AuditPlanUnit;

use app\modules\audit\models\AuditPlanReviewer;
use app\modules\audit\models\AuditPlanUnitExecutionChecklist;
use app\modules\audit\models\AuditPlanUnitExecutionChecklistRemediation;
use app\modules\audit\models\AuditPlanUnitExecutionChecklistRemediationApproval;
use app\modules\audit\models\AuditPlanUnitExecutionChecklistRemediationFile;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class AuditFindingsRemediationController extends \yii\rest\Controller
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
	
	
	/*
	Done by Customer - Submitting Remediation
	*/
    public function actionCreate()
    {
		//$remedmodal = new AuditPlanUnitExecutionChecklistRemediation();
		$checklistmodal = new AuditPlanUnitExecutionChecklist();
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$target_dir = Yii::$app->params['remediation_evidence_files'];
		$data = Yii::$app->request->post();
		//print_r($data);exit;
        if ($data) 
		{
			$data = json_decode($data['formvalues'],true);

			

			//$remedmodal = AuditPlanUnitExecutionChecklistRemediation::find()->where(['status'=>1,'audit_plan_unit_execution_checklist_id'=>$data['finding_id']])->one();
			$checklistchkmodal = AuditPlanUnitExecutionChecklist::find()->where(['status'=>1,'id'=>$data['finding_id']])->one();
			if($checklistchkmodal === null){
				$existingremed = AuditPlanUnitExecutionChecklistRemediation::find()->where(['status'=>0,'audit_plan_unit_execution_checklist_id'=>$data['finding_id']])->one();
				if($existingremed!==null){
					$existingremed->status =1;
					$existingremed->save();
				}

				$remedmodal = new AuditPlanUnitExecutionChecklistRemediation();
			}
			else
			{
				$remedmodal = AuditPlanUnitExecutionChecklistRemediation::find()->where(['status'=>0,'audit_plan_unit_execution_checklist_id'=>$data['finding_id']])->one();

				
				
				
				AuditPlanUnitExecutionChecklistRemediationFile::deleteAll(['checklist_remediation_id' => $remedmodal->id]);
			}
			


			$remedmodal->audit_plan_unit_execution_checklist_id = $data['finding_id'];
			$remedmodal->root_cause = $data['root_cause'];
			$remedmodal->correction = $data['correction'];
			$remedmodal->status =0;
			$remedmodal->corrective_action = $data['corrective_action'];
			

			


			// if(isset($_FILES['evidence_file']['name']))
			// {
			// 	$tmp_name = $_FILES['evidence_file']["tmp_name"];
			// 	$name = $_FILES['evidence_file']['name'];
	   			
			// 	$remedmodal->evidence_file = Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
				
			// }else{
			// 	$remedmodal->evidence_file = isset($data['evidence_file'])?$data['evidence_file']:"";
			// }
			
			$userData = Yii::$app->userdata->getData();
			$remedmodal->created_by = $userData['userid'];

			if($remedmodal->validate() && $remedmodal->save())
			{

				$evidence_file_list = $data['evidence_file_list'];
				if(count($evidence_file_list)>0)
				{
					$icnt = 0;
					foreach($evidence_file_list as $filedetails)
					{
						if($filedetails['deleted'] != '1')
						{
							$filename= '';
							if($filedetails['added'] == '1')
							{
								if(isset($_FILES['evidence_file_list']['name'][$icnt]))
								{
									$tmp_name = $_FILES["evidence_file_list"]["tmp_name"][$icnt];
									$name = $_FILES["evidence_file_list"]["name"][$icnt];
									$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);	
																
								}
							}
							else
							{
								$filename = $filedetails['name'];
							}
							
							$RequestEvidence = new AuditPlanUnitExecutionChecklistRemediationFile();
							$RequestEvidence->filename = $filename;
							$RequestEvidence->checklist_remediation_id = $remedmodal->id;
							$RequestEvidence->save();
						}else{
							$filename = $filedetails['name'];
							if($filename!='')
							{
								Yii::$app->globalfuns->removeFiles($filename,$target_dir);							
							}
						}
						$icnt++;
					}
				}

				$auditplanmodal = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
				if($auditplanmodal !== null)
				{
					$auditplanmodal->status = $auditplanmodal->arrEnumStatus['remediation_in_progress'];
					$auditplanmodal->save();
					//print_r($auditplanmodal->getErrors());
				}

				$auditplanunitmodal = AuditPlanUnit::find()->where(['audit_plan_id'=>$data['audit_plan_id'],'unit_id'=>$data['unit_id']])->one();
				if($auditplanunitmodal !== null)
				{
					$auditplanunitmodal->status = $auditplanunitmodal->arrEnumStatus['remediation_in_progress'];
					$auditplanunitmodal->save();
					//print_r($auditplanunitmodal->getErrors());
				}

				$auditmodal= Audit::find()->where(['id'=>$data['audit_id']])->one();
				if($auditmodal !== null)
				{
					$auditmodal->status = $auditmodal->arrEnumStatus['remediation_in_progress'];
					$auditmodal->save();
					//print_r($auditmodal->getErrors());
				}
				//echo 'asdfsadf';

				$checklist = AuditPlanUnitExecutionChecklist::find()->where(['id'=>$data['finding_id']])->one();
				$checklist->status = $checklistmodal->arrEnumStatus['in_progress'];
				$checklist->save();
				/*
				$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'remediation_mail_to_reviewer'])->one();
				if($mailContent !== null)
				{
					
					if($auditmodal !== null)
					{
						$mailmsg=str_replace('{COMPANY-NAME}', $auditmodal->application->company_name, $mailContent['message'] );
						$mailmsg=str_replace('{COMPANY-EMAIL}', $auditmodal->application->email_address, $mailmsg );
						$mailmsg=str_replace('{COMPANY-TELEPHONE}', $auditmodal->application->telephone, $mailmsg );
						$mailmsg=str_replace('{CONTACT-NAME}', $auditmodal->application->first_name." ".$auditmodal->application->last_name, $mailmsg );

						//appleadauditor
						
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
				*/
				$responsedata=array('status'=>1,'message'=>'Corrective Action Plan has been Saved successfully');
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$remedmodal->errors);
			}
		}
		return $this->asJson($responsedata);
	}
	
	public function actionGetFinding()
    {
		$modal = new AuditPlanUnitExecutionChecklist();
		$auditmodal = new Audit();
		$resultarr=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');

        if ($data) 
		{
			$resultarr=array();
			$checklistmodal = AuditPlanUnitExecutionChecklist::find()->where(['id'=> $data['id']])->one();
			if ($checklistmodal !== null)
			{
				$resultarr['finding']=$checklistmodal->finding;
				$resultarr['severity']=$checklistmodal->auditnonconformitytimeline->name;
				$resultarr['file']=$checklistmodal->file;
				$resultarr['duedate']=date($date_format,strtotime($checklistmodal->due_date));
				$resultarr['finding_type']=$auditmodal->arrFindingType[$checklistmodal->finding_type];
				$resultarr['status']=$modal->arrStatus[$checklistmodal->status];
			}

			$remedmodal = AuditPlanUnitExecutionChecklistRemediationApproval::find()->where(['audit_plan_unit_execution_checklist_id'=> $data['id']])->orderBy(['id' => SORT_DESC])->one();
			if ($remedmodal !== null)
			{
				$resultarr['auditorComment'] = $remedmodal->comment;
				$resultarr['auditorStatus'] = $modal->statusList[$remedmodal->status];
				$resultarr['auditorRevieweddate'] = date($date_format,$remedmodal->created_at);
			}

		}
		return $resultarr;
	}

	public function actionEvidencefile(){
		$data = Yii::$app->request->post();
		
		$files = AuditPlanUnitExecutionChecklist::find()->where(['id'=>$data['id']])->one();
		
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['audit_files'].$files->file;
		
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
}
