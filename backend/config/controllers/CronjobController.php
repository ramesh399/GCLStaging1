<?php
namespace app\controllers;

use Yii;
use app\modules\certificate\models\Certificate;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationCertifiedByOtherCB;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;
use app\modules\master\models\Settings;
use app\modules\master\models\User;
use app\modules\master\models\UserStandard;
use app\modules\master\models\UserBusinessGroup;
use app\modules\master\models\UserBusinessGroupCode;
use app\modules\master\models\AuditNonConformityTimeline;
use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\AuditPlanUnit;
use app\modules\audit\models\AuditPlanUnitExecution;
use app\modules\audit\models\AuditPlanUnitExecutionChecklist;
use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class CronjobController extends \yii\rest\Controller
{

    /**
     * @inheritdoc
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
					'index'
				]
			]
		];
		 
	}
	 
	public function beforeAction($action)
    {
		// your custom code here, if you want the code to run before action filters,
		// which are triggered on the [[EVENT_BEFORE_ACTION]] event, e.g. PageCache or AccessControl
		if (!parent::beforeAction($action)) {
			return false;
		}
		// other custom code here
      	if ($_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR'] )
       	{
			return true;
		}
		return false; // or false to not run the action
	}
	 
	 
	private function actionChangeCertificateStatus()
	{
		$modelCertificate = new Certificate();
		$connection = Yii::$app->getDb();
		$connection->createCommand("SET SESSION group_concat_max_len = 1000000;")->execute();
		$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();

		$model = Certificate::find()->alias('cert');
		$model = $model->join('inner join', 'tbl_application_certified_by_other_cb as app','cert.parent_app_id=app.app_id AND cert.standard_id=app.standard_id');
		$model = $model->where(['<', 'app.validity_date', date('Y-m-d')]);
		$model = $model->andWhere('cert.status="'.$modelCertificate->arrEnumStatus['certified_by_other_cb'].'"');
		$model = $model->all();
		
		if(count($model)>0)
		{
			foreach($model as $certificate)
			{
				$certificate->status = 0;
				$certificate->save();
			}
		}
	}

	private function actionCheckCertificateValid()
	{
		$model = Certificate::find()->where(['<', 'certificate_valid_until', date('Y-m-d')])->andWhere(['certificate_status'=>0])->all();
		if(count($model)>0)
		{
			foreach($model as $certificate)
			{
				$certificate->certificate_status = 1;
				$certificate->save();
			}
		}
	}

	private function actionUserstandardExpires()
	{	
		$model = UserStandard::find()->where(['<', 'witness_valid_until', date('Y-m-d')])->all();
		if(count($model)>0)
		{
			foreach($model as $userstd)
			{
				$MailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'user_standard_expired'])->one();

				$username = $userstd->user->first_name." ".$userstd->user->last_name;
				$useremail = $userstd->user->email;
				$userstandard = $userstd->standard->code;

				if($MailContent !== null && $useremail!== null)
				{
					$mailmsg=str_replace('{USERNAME}', $username, $MailContent['message'] );
					$mailmsg=str_replace('{STANDARD}', $userstandard, $mailmsg );

					$MailLookupModel = new MailLookup();
					$MailLookupModel->to=$useremail;					
					$MailLookupModel->subject=$MailContent['subject'];
					$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
					$MailLookupModel->attachment='';
					$MailLookupModel->mail_notification_id='';
					$MailLookupModel->mail_notification_code='';
					$Mailres=$MailLookupModel->sendMail();
				}
			}
		}
	}


	private function actionUserstandardExpireWarning()
	{	
		$newdate = strtotime("+7 days", strtotime(date('Y-m-d')));
		$date=date("Y-m-d", $newdate);
		$model = UserStandard::find()->where(['witness_valid_until'=>$date])->all();
		if(count($model)>0)
		{
			foreach($model as $userstd)
			{
				$MailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'user_standard_expire_warning'])->one();

				$username = $userstd->user?$userstd->user->first_name." ".$userstd->user->last_name:'';
				$useremail = $userstd->user?$userstd->user->email:'';
				$userstandard = $userstd->standard->code;

				if($MailContent !== null && $useremail!== null)
				{
					$mailmsg=str_replace('{USERNAME}', $username, $MailContent['message'] );
					$mailmsg=str_replace('{STANDARD}', $userstandard, $mailmsg );

					$MailLookupModel = new MailLookup();
					$MailLookupModel->to=$useremail;					
					$MailLookupModel->subject=$MailContent['subject'];
					$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
					$MailLookupModel->attachment='';
					$MailLookupModel->mail_notification_id='';
					$MailLookupModel->mail_notification_code='';
					$Mailres=$MailLookupModel->sendMail();
				}
			}
		}
	}


	private function actionNcUpdate()
	{
		//echo $_SERVER['REMOTE_ADDR'].' == '.$_SERVER['SERVER_ADDR'];
		//echo '11';
		//die;
		$connection = Yii::$app->getDb();

		$auditmodel = new Audit();
		$auditplanmodel = new AuditPlan();
		$auditunitmodel = new AuditPlanUnit();
		$auditexecutionchecklistmodel = new AuditPlanUnitExecutionChecklist();
		/*
		$timeline = AuditNonConformityTimeline::find()->select(['id','name','timeline'])->where(['status'=>0])->asArray()->all();
		if($timeline !== null)
		{
			if(count($timeline) > 0)
			{	
				$resultarr=array();
				foreach($timeline as $val)
				{
					if($val['name'] == 'Critical')
					{
						$criticaldate = $val['timeline'];
						$criticaldate=date('Y-m-d', strtotime('-'.$criticaldate .'days'));
					}

					if($val['name'] == 'Major')
					{
						$majordate = $val['timeline'];
						$majordate=date('Y-m-d', strtotime('-'.$majordate .'days'));
					}

					if($val['name'] == 'Minor')
					{
						$minordate = $val['timeline'];
						$minordate=date('Y-m-d', strtotime('-'.$minordate .'days'));
					}
				}
			}
		}
		*/
		
		$connection->createCommand("set sql_mode='NO_ENGINE_SUBSTITUTION'")->execute();
		
		//$command = $connection->createCommand("SELECT audit.id AS audit_id FROM tbl_audit audit INNER JOIN tbl_audit_plan aud_plan ON audit.id=aud_plan.audit_id INNER JOIN tbl_audit_plan_unit aud_plan_unit ON aud_plan.id=aud_plan_unit.audit_plan_id INNER JOIN tbl_audit_plan_unit_execution_checklist aud_plan_unit_exe ON aud_plan_unit_exe.audit_plan_unit_id=aud_plan_unit.id WHERE aud_plan_unit_exe.answer='2' AND ((aud_plan_unit_exe.severity='1' AND date_format(FROM_UNIXTIME(aud_plan_unit.status_change_date), '%Y-%m-%d') <= '".$criticaldate."') OR (aud_plan_unit_exe.severity = '2' AND date_format(FROM_UNIXTIME(aud_plan_unit.status_change_date), '%Y-%m-%d') <= '".$majordate."') OR (date_format(FROM_UNIXTIME(aud_plan_unit.status_change_date), '%Y-%m-%d') = '3' AND date_format(FROM_UNIXTIME(aud_plan_unit.status_change_date), '%Y-%m-%d') <= '".$minordate."')) AND (aud_plan.status = ".$auditplanmodel->arrEnumStatus['remediation_in_progress']." AND aud_plan_unit.status = ".$auditunitmodel->arrEnumStatus['remediation_in_progress'].") GROUP BY audit.id"); 
		
		//Last Final
		//$command = $connection->createCommand("SELECT audit.id AS audit_id, GROUP_CONCAT(aud_plan_unit.id) as audit_plan_unit_ids, GROUP_CONCAT(aud_plan_unit_exe.id) as aud_plan_unit_exe_ids FROM tbl_audit audit INNER JOIN tbl_audit_plan aud_plan ON audit.id=aud_plan.audit_id INNER JOIN tbl_audit_plan_unit aud_plan_unit ON aud_plan.id=aud_plan_unit.audit_plan_id INNER JOIN tbl_audit_plan_unit_execution_checklist aud_plan_unit_exe ON aud_plan_unit_exe.audit_plan_unit_id=aud_plan_unit.id WHERE aud_plan_unit_exe.answer='2' AND ((aud_plan_unit_exe.severity='1' AND aud_plan.audit_completed_date <= '".$criticaldate."') OR (aud_plan_unit_exe.severity = '2' AND aud_plan.audit_completed_date <= '".$majordate."') OR (aud_plan_unit_exe.severity = '3' AND aud_plan.audit_completed_date <= '".$minordate."')) AND (aud_plan.status = ".$auditplanmodel->arrEnumStatus['audit_completed']." OR aud_plan.status = ".$auditplanmodel->arrEnumStatus['remediation_in_progress'].") GROUP BY audit.id");
		$currentdate = date('Y-m-d');
		
		//$command = $connection->createCommand("SELECT audit.id AS audit_id, GROUP_CONCAT(aud_plan_unit.id) as audit_plan_unit_ids, GROUP_CONCAT(aud_plan_unit_exe.id) as aud_plan_unit_exe_ids FROM tbl_audit audit INNER JOIN tbl_audit_plan aud_plan ON audit.id=aud_plan.audit_id INNER JOIN tbl_audit_plan_unit aud_plan_unit ON aud_plan.id=aud_plan_unit.audit_plan_id INNER JOIN tbl_audit_plan_unit_execution_checklist aud_plan_unit_exe ON aud_plan_unit_exe.audit_plan_unit_id=aud_plan_unit.id WHERE aud_plan_unit_exe.answer='2' AND aud_plan_unit_exe.due_date < '".$currentdate."' AND (aud_plan.status = ".$auditplanmodel->arrEnumStatus['audit_completed']." OR aud_plan.status = ".$auditplanmodel->arrEnumStatus['remediation_in_progress'].") GROUP BY audit.id");
		//, GROUP_CONCAT(aud_plan_unit.id) as audit_plan_unit_ids, GROUP_CONCAT(aud_plan_unit_exe.id) as aud_plan_unit_exe_ids
		$command = $connection->createCommand("SELECT audit.id AS audit_id FROM tbl_audit audit INNER JOIN tbl_audit_plan aud_plan ON audit.id=aud_plan.audit_id INNER JOIN tbl_audit_plan_unit aud_plan_unit ON aud_plan.id=aud_plan_unit.audit_plan_id INNER JOIN tbl_audit_plan_unit_execution_checklist aud_plan_unit_exe ON aud_plan_unit_exe.audit_plan_unit_id=aud_plan_unit.id WHERE aud_plan_unit_exe.answer='2' AND aud_plan_unit_exe.due_date < '".$currentdate."' AND overdue_status=1 AND (aud_plan.status = ".$auditplanmodel->arrEnumStatus['audit_completed']." OR aud_plan.status = ".$auditplanmodel->arrEnumStatus['remediation_in_progress'].") GROUP BY audit.id");
		 
		// AND aud_plan_unit.status = ".$auditunitmodel->arrEnumStatus['remediation_in_progress']."
		$results = $command->queryAll();
		if(count($results)>0)
		{
			foreach($results as $resultval)
			{
				//echo $resultval['audit_id']; die;
				/*
				if($resultval['audit_id'] != '216'){
					continue;
				}
				*/
				
				//$audit_plan_unit_ids = array_unique(explode(',',$resultval['audit_plan_unit_ids']));
				//$aud_plan_unit_exe_ids = array_unique(explode(',',$resultval['aud_plan_unit_exe_ids']));

				$auditquery = Audit::find()->where(['id' => $resultval['audit_id']])->one();
				if ($auditquery !== null)
				{
					$auditquery->overdue_status = 2;
					$auditquery->save();
				}
			}
		}
	}

	private function actionCertificateUpdate()
	{
		$model = new Certificate();
		$ModelApplicationStandard = new ApplicationStandard();

		$certmodel = Certificate::find()->where(['<','certificate_valid_until', date('Y-m-d')])->andWhere(['certificate_status'=>$model->arrEnumCertificateStatus['valid']])->all();

		if(count($certmodel)>0)
		{
			foreach($certmodel as $certificate)
			{
				$certificate->status = $model->arrEnumStatus['expired'];
				$certificate->certificate_status = $model->arrEnumCertificateStatus['invalid'];
				
				$datas = ['standard_id'=>$certificate->standard_id,'app_id'=>$certificate->parent_app_id,'status'=>$ModelApplicationStandard->arrEnumStatus['expired'] ];
				$certificate->applicationStandardDecline($datas);

				$certificate->save();
			}
			
		}
	}

	private function actionUserStandardUpdate()
	{
		$userstdmodel = UserStandard::find()->where(['<','witness_valid_until', date('Y-m-d')])->andWhere(['approval_status'=>2])->all();
		if(count($userstdmodel)>0)
		{
			foreach($userstdmodel as $userstd)
			{
				$userstd->approval_status = 3; //Status is Rejected
				$userstd->save();
				/*
				$userbgmodel = UserBusinessGroup::find()->where(['user_id'=>$userstd->user_id])->andWhere(['standard_id'=>$userstd->standard_id])->all();
				if(count($userbgmodel)>0)
				{
					foreach($userbgmodel as $bgmodel)
					{
						$bgcodemodel = $bgmodel->groupcodeactive;
						if(count($bgcodemodel)>0)
						{
							foreach($bgcodemodel as $codemodel)
							{
								// $codemodel->status=1;
								// $codemodel->save();
							}
						}
						// $bgmodel->status=1;
						// $bgmodel->save();
					}
				}

				// $userstdmodel->approval_status=1;
				// $userstdmodel->save();

				*/
			}
			
		}
	}
	public function actionIndex(){
		$this->actionUserStandardUpdate();
		$this->actionCertificateUpdate();
		$this->actionNcUpdate();
		$this->actionUserstandardExpireWarning();
		//$this->actionUserstandardExpires();
		//$this->actionCheckCertificateValid();
		$this->actionChangeCertificateStatus();
	}
}