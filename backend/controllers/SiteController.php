<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\EntryForm;

use app\modules\master\models\User;
use app\modules\master\models\UserRole;
use app\modules\master\models\Enquiry;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\UserCompanyInfo;
use app\modules\master\models\MailLookup;
use app\modules\master\models\UserQualification;
use app\modules\master\models\UserTrainingInfo;
use app\modules\master\models\UserExperience;
use app\modules\master\models\UserCertification;
use app\modules\master\models\Privileges;
use app\modules\master\models\Settings;
use app\modules\master\models\UserQualificationReviewComment;
use app\modules\master\models\AuditNonConformityTimeline;

use app\modules\master\models\UserStandard;
use app\modules\master\models\UserBusinessGroup;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationChangeAddress;
use app\modules\application\models\ApplicationUnitSubtopic;

use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\AuditPlanUnit;
use app\modules\audit\models\AuditPlanUnitExecution;
use app\modules\audit\models\AuditPlanUnitExecutionChecklist;

use app\modules\certificate\models\Certificate;
use app\modules\transfercertificate\models\Request;
use app\modules\offer\models\Offer;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SiteController  extends \yii\rest\Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
           /* 'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            */
			
			[
				'class' => \yii\filters\ContentNegotiator::className(),
				//'only' => ['index', 'view'],
				'formats' => [
					'application/json' => \yii\web\Response::FORMAT_JSON,
				],
            ], 
			'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
            'corsFilter' => ['class' => \yii\filters\Cors::className()],
            'authenticator' => ['class' => JwtHttpBearerAuth::class,
				'optional' => [
					'login',
                    'password-reset',
					'reset-password',										
					'getyear',					
					'scan-certificate',
					'scan-transaction-certificate',
					'generateinvoicemanually',
					'getrandomdata',
					'getrandomdataex',
					'tcrm',
					'generatexls',
					'rmp'
				]
			]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        
        return $this->render('index');
    }   

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
		$dataencrypt = Yii::$app->request->post();			
		if($dataencrypt)
		{
			$EncryptDecryptKey = Yii::$app->params['EncryptDecryptKey'];
			$data = Yii::$app->globalfuns->cryptoJsAesDecrypt($EncryptDecryptKey,json_encode($dataencrypt));			
			
			$jsonResponse = Yii::$app->globalfuns->verifyReCaptcha($data['token']);		
			if($jsonResponse['success'])
			{	
				$modelUser = new User();				
														
				$model = new LoginForm();				
				$model->username=$data['username']; 
				$model->password=$data['password'];
				if($model->login())
				{
					$time = time();
					
					$jwt = Yii::$app->jwt;
					$signer = $jwt->getSigner('HS256');
					$key = $jwt->getKey();
					$time = time();
					$resource_access = '';
					
					$UserRoleobj = UserRole::find()->where(['id' => Yii::$app->user->id])->one();
					$dbdata = $UserRoleobj->user;
					
					$userroles = [];
					$rules = [];
					
					$rules = [];
					$ruleids = [];
					
					if($UserRoleobj->role_id!=0 && $UserRoleobj->role_id!='')
					{
						//return $dbdata->usersrole;
						if(isset($UserRoleobj->role))
						{
							$resource_access = $UserRoleobj->role->resource_access;
						}											
						
						$userRulesObj = $UserRoleobj->userrules;
						if(is_array($userRulesObj) && count($userRulesObj)>0)
						{
							foreach($userRulesObj as $rule){
								$ruleids[] = $rule->privilege_id;
							}
							$rules = $this->getParentRules($ruleids);
						}
					}						
					//$rules = array_values($rules);
					
					$name=$dbdata['first_name']." ".$dbdata['last_name'];
					
					$customerCompanyName = '';
					if(($dbdata['user_type']==2 || $dbdata['user_type']==3) && $dbdata->usercompanyinfo)
					{
						$customerCompanyName = $dbdata->usercompanyinfo->company_name;
					}else{
						$customerCompanyName = $UserRoleobj->franchise->usercompanyinfo->company_name;
					}
					
					$userid = $UserRoleobj->user_id;
					
					$role=$UserRoleobj->role_id;
					$firstLogin=false;
					$headquarters = 0;
					if($dbdata['user_type']== 1){
						$headquarters = $UserRoleobj->franchise->headquarters;
					}else if($dbdata['user_type']== 3){
						$headquarters = $dbdata['headquarters'];
					}
					/*
					if($dbdata['login_status']==0)
					{
						$firstLogin=true;
						//$role='';
					}
					*/

					$token = $jwt->getBuilder()
								->issuedBy('https://ssl.gcl-intl.com') // Configures the issuer (iss claim)
								->permittedFor('https://ssl.gcl-intl.com') // Configures the audience (aud claim)
								->identifiedBy('412RsdfLive1GcL2InTeRnationalTerXFGWErwe', true) // Configures the id (jti claim), replicating as a header item
								->issuedAt($time) // Configures the time that the token was issue (iat claim)
								//->canOnlyBeUsedAfter($time + 60) // Configures the time that the token can be used (nbf claim)
								->expiresAt($time + 360000) // Configures the expiration time of the token (exp claim)
								->withClaim('uid', $userid) // Configures a new claim, called "uid"
								->withClaim('roleid', $role) // Configures a new claim, called "uid" Yii::$app->user->id
								->withClaim('user_role_id', $UserRoleobj->id) // Configures a new claim, called "user role id"						
								->withClaim('role_name', ($UserRoleobj->role)?$UserRoleobj->role->role_name:'') // Configures a new claim, called "uid"
								->withClaim('franchiseid', $UserRoleobj->franchise_id) // Configures a new claim, called "uid"
								
								->withClaim('company_name', $customerCompanyName) // Configures a new claim, called "uid"										
														
								->withClaim('email', $dbdata['email'])
								->withClaim('is_headquarters', $headquarters)
								->withClaim('displayname', $name)
								->withClaim('resource_access', $resource_access)
								->withClaim('rules', $rules)
								->withClaim('user_type', $resource_access==5?3:$dbdata['user_type'])
								->withClaim('user_type_name', $modelUser->arrStatus[$dbdata['user_type']])
								
								->withClaim('role', $resource_access==5?0:$role) // Configures a new claim, called "uid"
								->withClaim('role_chkid', $role)
								->withClaim('firstlogin',$firstLogin) // Configures a new claim, called "uid"
								->getToken($signer, $key); // Retrieves the generated token
								//$resource_access==5?0:
					//echo '<pre>';
					//print_r($token);
					//die();
					
					$token->getHeaders(); // Retrieves the token headers
					$token->getClaims(); // Retrieves the token claims

					$token->getHeader('jti'); // will print "4f1g23a12aa"
					$token->getClaim('iss'); // will print "http://example.com"
					$token->getClaim('uid'); // will print "1"
					//echo $token; // The string representation of the object is a JWT string (pretty easy, right?)
					//die();		
					return ['token'=>(string)$token,'status'=>1];
				}
				//return json_encode($model->getErrors());
				return $model->getErrors();
				die();
				
				//if ($model->load(Yii::$app->request->post()) && $model->login()) {
				   // return $this->goBack();
				//}
			}else{
				return ['token'=>'','status'=>0,'message'=>'Invalid Request'];
			}
		}else{
			return ['token'=>'','status'=>0,'message'=>'Invalid Request'];
		}	
	}
	
	private function getParentRules($childrens)
	{
        //print_r($childrens); die;
        $privilegemodel = Privileges::find()->select(['group_concat(id) as id','group_concat(code) as code','parent_id'])->where(['id' =>$childrens])->groupBy('parent_id')->all();
        $parentIds = [];
        $ruleids = [];
		foreach ($privilegemodel as $privilegeobj)
		{ 
			$parent_id = $privilegeobj->parent_id;
            $ruleidsw = explode(',',$privilegeobj->code);
            foreach($ruleidsw as $ruleso){
                $ruleids[] = $ruleso;
            }
           
			if($parent_id>0){
                $ruleidss = $this->getParentRules($parent_id);
                //$ruleids = array_merge($ruleids,$ruleidss );
                foreach($ruleidss  as $ruleso){
                    $ruleids[] = $ruleso;
                }
            }
        }
        return $ruleids;
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }    
    
    public function actionPasswordReset()
    {
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if($data)
		{
			$jsonResponse = Yii::$app->globalfuns->verifyReCaptcha($data['token']);		
			if($jsonResponse['success'])
			{
				$Usermodel = new User();
						
				$user = UserRole::findOne([
					'username' => $data['username'],
					'login_status'=>1,
				]);

				if ($user)
				{
					$user->generateEmailVerificationToken();
					$user->reset_password_status=1;
					if($user->validate() && $user->save())
					{
						$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'forgot_password'])->one();

						$verifyLink = Yii::$app->params['site_path'].'reset-password?token='.$user->verification_token;

						$mailmsg=str_replace('{USERNAME}', $user->user->first_name." ".$user->user->last_name, $mailContent['message'] );
						$mailmsg=str_replace('{VERIFYLINK}', $verifyLink, $mailmsg);
						$message = Yii::$app->mailer->compose(['html' =>'layouts/mailNotificationTemplate'],['content' => $mailmsg]);

						// Mail to requsted Customer with forgot password reset link
						$MailLookupModel = new MailLookup();
						$MailLookupModel->to=$user->user->email;						
						$MailLookupModel->subject=$mailContent['subject'];
						$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
						$MailLookupModel->attachment='';
						$MailLookupModel->mail_notification_id='';
						$MailLookupModel->mail_notification_code='';
						$Mailres=$MailLookupModel->sendMail();
						$responsedata=array('status'=>1,'message'=>'Mail has been sent to email Id successfully');				
					}
					
				}else{
					$responsedata=array('status'=>0,'message'=>'Username does not exist!');
				}
			}else{
				$responsedata=array('status'=>0,'message'=>'Invalid Request');
			}				
		}
		return $this->asJson($responsedata);
	}
	
	public function actionResetPassword()
    {
		$responsedata=array('status'=>0,'message'=>'Password reset token cannot be blank');
        $data = Yii::$app->request->post();		
		if($data)
		{ 
	        $token = $data['token'];
            if (empty($token) || !is_string($token)) {         
				$responsedata=array('status'=>0,'message'=>'Password reset token cannot be blank');
			}else{				
								
				$model = UserRole::find()->where(['verification_token' => $token,'reset_password_status'=>1,'login_status'=>1])->one();
				if($model!==null)
				{						
					//$timestamp = (int) substr($token, strrpos($token, '_') + 1);
					//$expire = Yii::$app->params['user.passwordResetTokenExpire'];       
					
					//if(($timestamp + $expire) <= time())
					//{					
						//$responsedata=array('status'=>0,'message'=>'Wrong password reset token');
					//}else{
						if($data['isTokenVerifyRequest']==1)
						{
							$responsedata=array('status'=>2,'message'=>'Token verified successfully. Please set new password.');
						}else{
							$model->setPassword($data['new_password']);						
							$model->updated_by=$model->id;
							$model->verification_token='';
							$model->reset_password_status=0;
							if($model->validate() && $model->save())
							{
								$responsedata=array('status'=>1,'message'=>'New password changed successfully');
							}	
						}
					//}
				}else{
					$responsedata=array('status'=>0,'message'=>'Wrong password reset token');
				}
			}

        }
		$responsedata=array('status'=>2,'message'=>'Token verified successfully. Please set new password.');
		return $this->asJson($responsedata);
	}  
	
	
	public function actionAlertSender()
    {
		$datemodel=UserQualificationReviewComment::find()->select('valid_until')->all();
		$settingsmodel=Settings::find()->select('maximumdays,to_email')->one();
		$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => ''])->one();

		if(($datemodel !== null) && ($settingsmodel !== null))
		{
			foreach($datemodel as $dates)
			{
				$diffdays = Yii::$app->globalfuns->fnCalculateDates(date('d-m-Y'),$dates);
				if($settingsmodel->maximumdays==$diffdays)
				{
					$MailLookupModel = new MailLookup();
					$MailLookupModel->to=$settingsmodel->to_email;					
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
	
	public function actionGetyear()
	{
		return date('Y');
	}

	public function actionScanCertificate($code)
	{		
		$CertificateModel = Certificate::find()->where(['md5(code)' => $code,'certificate_status'=>0])->one();
        if($CertificateModel!==null)
		{
			$file = $CertificateModel->filename;
		
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			header('Access-Control-Max-Age: 1000');
			header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
			
			$filepath=Yii::$app->params['certificate_files'].$file;
			if(file_exists($filepath)) 
			{
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
			die();	
		}		
	}

	public function actionScanTransactionCertificate($code)
	{
		$RequestModel = Request::find()->where(['md5(id)' => $code])->one();
        if($RequestModel!==null)
		{
			$file = $RequestModel->filename;
		
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			header('Access-Control-Max-Age: 1000');
			header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
			
			$filepath=Yii::$app->params['tc_files']."tc/".$file;
			if(file_exists($filepath)) 
			{
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
			die();	
		}		
	}
	
	public function actionGenerateinvoicemanually()
	{
		$offermodel = Offer::find()->where(['id'=>array('855','856','859')])->all();
		$mailmsg = '';
		if(count($offermodel)>0)
		{
			$offermodel->generateInvoice($offermodel,'1'); //Auto Generate Invoice for Client
			$offermodel->generateInvoice($offermodel,'2'); //Auto Generate Invoice for OSS
		}	
	}
	
	public function actionGetrandomdata()
	{
		return ['status'=>1];
		die;
	}

	public function actionGetrandomdataex()
	{
		
		return [
			['id'=>1,'name'=>rand(1,100)],
			['id'=>rand(1,100),'name'=>rand(1,100)]
		];
		die;
	}
	
	public function actionGeneratexls()
	{
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue('A1', 'Hello World !');

		$writer = new Xlsx($spreadsheet);
		$filepath=Yii::$app->params['report_files'].'hello_world_nov272020.xlsx';
		$writer->save($filepath);
	}

	public function actionNcUpdate()
	{
		$connection = Yii::$app->getDb();

		$auditmodel = new Audit();
		$auditplanmodel = new AuditPlan();
		$auditunitmodel = new AuditPlanUnit();
		$auditexecutionchecklistmodel = new AuditPlanUnitExecutionChecklist();

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

		$connection->createCommand("set sql_mode='NO_ENGINE_SUBSTITUTION'")->execute();
		$command = $connection->createCommand("SELECT audit.id AS audit_id FROM tbl_audit audit INNER JOIN tbl_audit_plan aud_plan ON audit.id=aud_plan.audit_id INNER JOIN tbl_audit_plan_unit aud_plan_unit ON aud_plan.id=aud_plan_unit.audit_plan_id INNER JOIN tbl_audit_plan_unit_execution_checklist aud_plan_unit_exe ON aud_plan_unit_exe.audit_plan_unit_id=aud_plan_unit.id WHERE aud_plan_unit_exe.answer='2' AND ((aud_plan_unit_exe.severity='1' AND date_format(FROM_UNIXTIME(aud_plan_unit.status_change_date), '%Y-%m-%d') <= '".$criticaldate."') OR (aud_plan_unit_exe.severity = '2' AND date_format(FROM_UNIXTIME(aud_plan_unit.status_change_date), '%Y-%m-%d') <= '".$majordate."') OR (date_format(FROM_UNIXTIME(aud_plan_unit.status_change_date), '%Y-%m-%d') = '3' AND date_format(FROM_UNIXTIME(aud_plan_unit.status_change_date), '%Y-%m-%d') <= '".$minordate."')) AND (aud_plan.status = ".$auditplanmodel->arrEnumStatus['remediation_in_progress']." AND aud_plan_unit.status = ".$auditunitmodel->arrEnumStatus['remediation_in_progress'].") GROUP BY audit.id");
		
		$results = $command->queryAll();		
		if(count($results)>0)
		{
			foreach($results as $resultval)
			{
				$auditquery = Audit::find()->where(['id' => $resultval['audit_id']])->one();
				if ($auditquery !== null)
				{
					$auditquery->status = $auditmodel->arrEnumStatus['nc_overdue'];
					$auditquery->save();
				}

				$auditplanquery = AuditPlan::find()->where(['audit_id' => $resultval['audit_id']])->one();
				if ($auditplanquery !== null)
				{
					$auditplanquery->status = $auditplanmodel->arrEnumStatus['nc_overdue'];
					$auditplanquery->save();

					$auditplanunitquery = AuditPlanUnit::find()->where(['audit_plan_id' => $auditplanquery->id])->one();
					if ($auditplanunitquery !== null)
					{
						$auditplanunitquery->status = $auditunitmodel->arrEnumStatus['nc_overdue'];
						$auditplanunitquery->save();

						$auditplanunitexequery = AuditPlanUnitExecution::find()->where(['audit_plan_unit_id' => $auditplanunitquery->id])->one();
						if ($auditplanunitexequery !== null)
						{
							$auditplanunitexechecklistquery = AuditPlanUnitExecutionChecklist::find()->where(['audit_plan_unit_execution_id' => $auditplanunitexequery->id])->one();
							if ($auditplanunitexechecklistquery !== null)
							{
								$auditplanunitexechecklistquery->status = $auditexecutionchecklistmodel->arrEnumStatus['nc_overdue'];
								$auditplanunitexechecklistquery->save();
							}
						}
					}
				}

			}
		}
	}
	
	public function actionRmp()
	{
		$connection = Yii::$app->getDb();		
		$connection->createCommand("set sql_mode='NO_ENGINE_SUBSTITUTION'")->execute();
		$command = $connection->createCommand("select * from tbl_tc_raw_material_product");
		$results = $command->queryAll();		
		if(count($results)>0)
		{
			foreach($results as $result)
			{
				$productID = $result['id'];
				$RMID=$result['raw_material_id'];				
				
				if($productID!='' && $RMID!='')
				{
					$updateQueryforRawMaterialUsedWeight="update tbl_tc_raw_material_used_weight set tc_raw_material_product_id=".$productID." where tc_raw_material_id=".$RMID;
					echo $updateQueryforRawMaterialUsedWeight.'<br>-------------------------------------<br>';
					$connection->createCommand($updateQueryforRawMaterialUsedWeight)->execute();
					
					$updateQueryforRequestProductInputMaterial="update tbl_tc_request_product_input_material set tc_raw_material_product_id=".$productID." where tc_raw_material_id=".$RMID;
					echo $updateQueryforRequestProductInputMaterial.'<br>-------------------------------------<br>';
					$connection->createCommand($updateQueryforRequestProductInputMaterial)->execute();
									
					$updateQueryforRawMaterialLabelGrade="update tbl_tc_raw_material_label_grade set raw_material_product_id=".$productID." where raw_material_id=".$RMID;
					echo $updateQueryforRequestProductInputMaterial.'<br>-------------------------------------<br>';
					$connection->createCommand($updateQueryforRawMaterialLabelGrade)->execute();
				}				
						
			}
		}
		die();		
	}
}
