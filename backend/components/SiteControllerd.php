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

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

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
					'appreviewer',
					'appaddress',
					'appunitsubtopic',
					'auditexecution',
					'deleteapp',
					'getyear'
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
	
	public function actionChangefile()
	{
		$UsersModel = UserStandard::find()->select('id,standard_exam_file,recycle_exam_file,social_course_exam_file,witness_file')->all();
		if(count($UsersModel)>0)
		{
			foreach($UsersModel as $user)
			{
				if($user->user_id!=72)
				{
					$model = UserStandard::find()->where(['id' => $user->id])->one();
					if($model !== null)
					{
						$OldFileName=$user->standard_exam_file;
						echo $OldFileName;
						if($OldFileName!='')
						{
							$NewFileName=Yii::$app->globalfuns->fnRemoveSpecialCharacters($OldFileName);
							$target_dir = Yii::$app->params['user_files']; 
							if(file_exists($target_dir.$OldFileName))
							{
								$counter=0;
								$newF = $NewFileName;
								if(file_exists($target_dir.$NewFileName))
								{
									do
									{ 
										$counter=$counter+1;
										$NewFileName=$counter."".$newF;
										//$NewFileName = str_replace($special_char, '', $NewFileName);
										//$NewFileName=str_replace(",","-",$NewFileName);
										//$NewFileName=str_replace(" ","_",$NewFileName);	
									}
									while(file_exists($target_dir.$NewFileName));
								}
								
								rename($target_dir.$OldFileName,$target_dir.$NewFileName);
								$model->standard_exam_file=$NewFileName;	
							}
						}	
						
						$OldFileName=$user->recycle_exam_file;
						if($OldFileName!='')
						{
							$NewFileName=Yii::$app->globalfuns->fnRemoveSpecialCharacters($OldFileName);
							$target_dir = Yii::$app->params['user_files']; 
							if(file_exists($target_dir.$OldFileName))
							{
								$counter=0;
								$newF = $NewFileName;
								if(file_exists($target_dir.$NewFileName))
								{
									do
									{ 
										$counter=$counter+1;
										$NewFileName=$counter."".$newF;
										//$NewFileName = str_replace($special_char, '', $NewFileName);
										//$NewFileName=str_replace(",","-",$NewFileName);
										//$NewFileName=str_replace(" ","_",$NewFileName);	
									}
									while(file_exists($target_dir.$NewFileName));
								}
								
								rename($target_dir.$OldFileName,$target_dir.$NewFileName);
								$model->recycle_exam_file=$NewFileName;	
							}
						}
						
						$OldFileName=$user->social_course_exam_file;
						if($OldFileName!='')
						{
							$NewFileName=Yii::$app->globalfuns->fnRemoveSpecialCharacters($OldFileName);
							$target_dir = Yii::$app->params['user_files']; 
							if(file_exists($target_dir.$OldFileName))
							{
								$counter=0;
								$newF = $NewFileName;
								if(file_exists($target_dir.$NewFileName))
								{
									do
									{ 
										$counter=$counter+1;
										$NewFileName=$counter."".$newF;
										//$NewFileName = str_replace($special_char, '', $NewFileName);
										//$NewFileName=str_replace(",","-",$NewFileName);
										//$NewFileName=str_replace(" ","_",$NewFileName);	
									}
									while(file_exists($target_dir.$NewFileName));
								}
								
								rename($target_dir.$OldFileName,$target_dir.$NewFileName);
								$model->social_course_exam_file=$NewFileName;	
							}
						}	
						
						$OldFileName=$user->witness_file;
						if($OldFileName!='')
						{
							$NewFileName=Yii::$app->globalfuns->fnRemoveSpecialCharacters($OldFileName);
							$target_dir = Yii::$app->params['user_files']; 
							if(file_exists($target_dir.$OldFileName))
							{
								$counter=0;
								$newF = $NewFileName;
								if(file_exists($target_dir.$NewFileName))
								{
									do
									{ 
										$counter=$counter+1;
										$NewFileName=$counter."".$newF;
										//$NewFileName = str_replace($special_char, '', $NewFileName);
										//$NewFileName=str_replace(",","-",$NewFileName);
										//$NewFileName=str_replace(" ","_",$NewFileName);	
									}
									while(file_exists($target_dir.$NewFileName));
								}
								
								rename($target_dir.$OldFileName,$target_dir.$NewFileName);
								$model->witness_file=$NewFileName;	
							}
						}
						$model->save();
						var_dump($model->getErrors());
					}
				}	
			}
		}
		die();		
	}
	
	public function actionChangefilenew()
	{
		$UsersModel = User::find()->where(['user_type' => 1])->all();
		if(count($UsersModel)>0)
		{
			foreach($UsersModel as $user)
			{
				//if($user->user_id!=72)
				//{
					$model = User::find()->where(['id' => $user->id])->one();
					if($model !== null)
					{
						$OldFileName=$user->passport_file;
						echo $OldFileName;
						if($OldFileName!='')
						{
							$NewFileName=Yii::$app->globalfuns->fnRemoveSpecialCharacters($OldFileName);
							$target_dir = Yii::$app->params['user_files']; 
							if(file_exists($target_dir.$OldFileName))
							{
								$counter=0;
								$newF = $NewFileName;
								if(file_exists($target_dir.$NewFileName))
								{
									do
									{ 
										$counter=$counter+1;
										$NewFileName=$counter."".$newF;
										//$NewFileName = str_replace($special_char, '', $NewFileName);
										//$NewFileName=str_replace(",","-",$NewFileName);
										//$NewFileName=str_replace(" ","_",$NewFileName);	
									}
									while(file_exists($target_dir.$NewFileName));
								}
								
								rename($target_dir.$OldFileName,$target_dir.$NewFileName);
								$model->passport_file=$NewFileName;	
							}
						}	
						
						$OldFileName=$user->contract_file;
						if($OldFileName!='')
						{
							$NewFileName=Yii::$app->globalfuns->fnRemoveSpecialCharacters($OldFileName);
							$target_dir = Yii::$app->params['user_files']; 
							if(file_exists($target_dir.$OldFileName))
							{
								$counter=0;
								$newF = $NewFileName;
								if(file_exists($target_dir.$NewFileName))
								{
									do
									{ 
										$counter=$counter+1;
										$NewFileName=$counter."".$newF;
										//$NewFileName = str_replace($special_char, '', $NewFileName);
										//$NewFileName=str_replace(",","-",$NewFileName);
										//$NewFileName=str_replace(" ","_",$NewFileName);	
									}
									while(file_exists($target_dir.$NewFileName));
								}
								
								rename($target_dir.$OldFileName,$target_dir.$NewFileName);
								$model->contract_file=$NewFileName;	
							}
						}					
						$model->save();
						var_dump($model->getErrors());
					}
				//}	
			}
		}
		die();		
	}
	
	public function actionChangefilenews()
	{
		$UsersModel=UserBusinessGroup::find()->select('id,exam_file,technical_interview_file')->all();
		if(count($UsersModel)>0)
		{
			foreach($UsersModel as $user)
			{
				//if($user->user_id!=72)
				//{
					$model = UserBusinessGroup::find()->where(['id' => $user->id])->one();
					if($model !== null)
					{
						$OldFileName=$user->exam_file;
						echo $OldFileName;
						if($OldFileName!='')
						{
							$NewFileName=Yii::$app->globalfuns->fnRemoveSpecialCharacters($OldFileName);
							$target_dir = Yii::$app->params['user_files']; 
							if(file_exists($target_dir.$OldFileName))
							{
								$counter=0;
								$newF = $NewFileName;
								if(file_exists($target_dir.$NewFileName))
								{
									do
									{ 
										$counter=$counter+1;
										$NewFileName=$counter."".$newF;
										//$NewFileName = str_replace($special_char, '', $NewFileName);
										//$NewFileName=str_replace(",","-",$NewFileName);
										//$NewFileName=str_replace(" ","_",$NewFileName);	
									}
									while(file_exists($target_dir.$NewFileName));
								}
								
								rename($target_dir.$OldFileName,$target_dir.$NewFileName);
								$model->exam_file=$NewFileName;	
							}
						}	
						
						$OldFileName=$user->technical_interview_file;
						if($OldFileName!='')
						{
							$NewFileName=Yii::$app->globalfuns->fnRemoveSpecialCharacters($OldFileName);
							$target_dir = Yii::$app->params['user_files']; 
							if(file_exists($target_dir.$OldFileName))
							{
								$counter=0;
								$newF = $NewFileName;
								if(file_exists($target_dir.$NewFileName))
								{
									do
									{ 
										$counter=$counter+1;
										$NewFileName=$counter."".$newF;
										//$NewFileName = str_replace($special_char, '', $NewFileName);
										//$NewFileName=str_replace(",","-",$NewFileName);
										//$NewFileName=str_replace(" ","_",$NewFileName);	
									}
									while(file_exists($target_dir.$NewFileName));
								}
								
								rename($target_dir.$OldFileName,$target_dir.$NewFileName);
								$model->technical_interview_file=$NewFileName;	
							}
						}					
						$model->save();
						var_dump($model->getErrors());
					}
				//}	
			}
		}
		die();		
	}
	
	public function actionChangefilequa()
	{ 
		$UsersModel=UserQualification::find()->select('id,certificate')->all();
		if(count($UsersModel)>0)
		{
			foreach($UsersModel as $user)
			{
				//if($user->user_id!=72)
				//{
					$model = UserQualification::find()->where(['id' => $user->id])->one();
					if($model !== null)
					{
						$OldFileName=$user->certificate;
						echo $OldFileName;
						if($OldFileName!='')
						{
							$NewFileName=Yii::$app->globalfuns->fnRemoveSpecialCharacters($OldFileName);
							$target_dir = Yii::$app->params['user_files']; 
							if(file_exists($target_dir.$OldFileName))
							{
								$counter=0;
								$newF = $NewFileName;
								if(file_exists($target_dir.$NewFileName))
								{
									do
									{ 
										$counter=$counter+1;
										$NewFileName=$counter."".$newF;
										//$NewFileName = str_replace($special_char, '', $NewFileName);
										//$NewFileName=str_replace(",","-",$NewFileName);
										//$NewFileName=str_replace(" ","_",$NewFileName);	
									}
									while(file_exists($target_dir.$NewFileName));
								}
								
								rename($target_dir.$OldFileName,$target_dir.$NewFileName);
								$model->certificate=$NewFileName;	
							}
						}											
						$model->save();
						var_dump($model->getErrors());
					}
				//}	
			}
		}
		die();		
	}
	
	public function actionChangefilecer()
	{
		$UsersModel=UserCertification::find()->select('id,filename')->all();
		if(count($UsersModel)>0)
		{
			foreach($UsersModel as $user)
			{
				//if($user->user_id!=72)
				//{
					$model = UserCertification::find()->where(['id' => $user->id])->one();
					if($model !== null)
					{
						$OldFileName=$user->filename;
						echo $OldFileName;
						if($OldFileName!='')
						{
							$NewFileName=Yii::$app->globalfuns->fnRemoveSpecialCharacters($OldFileName);
							$target_dir = Yii::$app->params['user_files']; 
							if(file_exists($target_dir.$OldFileName))
							{
								$counter=0;
								$newF = $NewFileName;
								if(file_exists($target_dir.$NewFileName))
								{
									do
									{ 
										$counter=$counter+1;
										$NewFileName=$counter."".$newF;
										//$NewFileName = str_replace($special_char, '', $NewFileName);
										//$NewFileName=str_replace(",","-",$NewFileName);
										//$NewFileName=str_replace(" ","_",$NewFileName);	
									}
									while(file_exists($target_dir.$NewFileName));
								}
								
								rename($target_dir.$OldFileName,$target_dir.$NewFileName);
								$model->filename=$NewFileName;	
							}
						}											
						$model->save();
						var_dump($model->getErrors());
					}
				//}	
			}
		}
		die();		
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

    public function actionTestupload()
    {
        $data = Yii::$app->request->post();
        return json_encode([$_FILES,$data]);
        //return json_encode($data);
		die();
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
		$data = Yii::$app->request->post();		
		if($data)
		{
			$jsonResponse = Yii::$app->globalfuns->verifyReCaptcha($data['token']);		
			if($jsonResponse['success'])
			{	
				//echo strtotime("+1 hour",time()); die;
				//if (!Yii::$app->user->isGuest) {
				   // return $this->goHome();
				//}
				//$dbdata = User::find()->where(['id' => 1])->one();

				//echo $dbdata->userrole->role_name;
				//echo count($dbdata->userrules);
				//    die;

				$modelUser = new User();

				
						
				$model = new LoginForm();
				//print_r($data); die;
				$model->username=$data['username']; 
				$model->password=$data['password']; 
				if($model->login())
				{
					$time = time();
					
					/*
					$token = Yii::$app->jwt->getBuilder()
								->issuedBy('') // Configures the issuer (iss claim)
								->permittedFor('') // Configures the audience (aud claim)
								->identifiedBy('4f1g23a12aa', true) // Configures the id 	 (jti claim), replicating as a header item
								->issuedAt($time) // Configures the time that the token was issue (iat claim)
								->canOnlyBeUsedAfter($time + 60) // Configures the time that the token can be used (nbf claim)
								->expiresAt($time + 3600) // Configures the expiration time of the token (exp claim)
								->withClaim('uid', Yii::$app->user->id) // Configures a new claim, called "uid"
								->withClaim('email', $model->username) // Configures a new claim, called "uid"
								->getToken(); // Retrieves the generated token
								*/
								//echo Yii::$app->user->id;
								//die();
								
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
						if(isset($UserRoleobj->role)){
							$resource_access = $UserRoleobj->role->resource_access;
						}
						
						/*
						if(isset($dbdata->usersrole) && is_array($dbdata->usersrole)){
							foreach($dbdata->usersrole as $dbrole){
								$userroles[] = $dbrole->role_id;
								if(is_array($dbrole->userrules) && count($dbrole->userrules)){
									$parentruleids = [];
									foreach($dbrole->userrules as $dbrule){
										$ruleids[] = $dbrule->privilege_id;
										$parentruleids[] = $dbrule->privilege_id;
									}
									$rules = $rules + $this->getParentRules($parentruleids);
								}
							}
						}
						*/ 
						//platium ferate
						
						//return $rules;
						
						
						$userRulesObj = $UserRoleobj->userrules;
						if(is_array($userRulesObj) && count($userRulesObj)>0){
							foreach($userRulesObj as $rule){
								$ruleids[] = $rule->privilege_id;
							}
							$rules = $this->getParentRules($ruleids);
						}
					}	
					
					
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
								->issuedBy('http://example.com') // Configures the issuer (iss claim)
								->permittedFor('http://example.org') // Configures the audience (aud claim)
								->identifiedBy('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
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

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }


    public function actionEntry()
    {
        
        $model = new EntryForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // valid data received in $model

            // do something meaningful here about $model ...

            return $this->render('entry-confirm', ['model' => $model]);
        } else {
            // either the page is initially displayed or there is some validation error
            return $this->render('entry', ['model' => $model]);
        }
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
						$MailLookupModel->cc='meignanamoorthyks@gmail.com';
						$MailLookupModel->bcc='';
						$MailLookupModel->subject=$mailContent['subject'];
						$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
						$MailLookupModel->attachment='';
						$MailLookupModel->mail_notification_id='';
						$MailLookupModel->mail_notification_code='';
						$Mailres=$MailLookupModel->sendMail();
						$responsedata=array('status'=>1,'message'=>'Mail has been sent to email Id successfully');

						// $message->setFrom('puja@361dm.com');
						// $message->setTo($user->email);
						// $message->setSubject($mailContent['subject']);
						// if($message->send())
						// {
						// 	$responsedata=array('status'=>1,'message'=>'Password reset mail has been sent! check your mail');
						// }
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
	
	public function actionJson()
	{
		/*
		$query = new \yii\db\Query;;
		$query	->select(['SELECT ctry.name'])  
				->from('tbl_enquiry as eqry')
				->join(	'INNER JOIN', 
					'tbl_country as ctry',
					'ctry.id =eqry.country_id'
				); 
		$command = $query->createCommand();
		$data = $command->queryAll();
		print_r($data);
		die();
		*/

		$model = Enquiry::find()->select(['ctry.name as countryName'])->joinWith(['country as ctry'])->all();
		foreach($model as $m)
		{
		  echo $m->countryName;	
		}
		print_r($model);
		die();
		
		
		
		$qualificationarr = User::find()->select('first_name')->where(['id' => 37])->asArray()->one();
		$qualificationarr['qualification'] = UserQualification::find()->select('qualification')->where(['user_id' => 37])->asArray()->all();
		
		print_r($qualificationarr);
		echo '<br><br><br>';
		print_r(json_encode($qualificationarr));
		die();
		
		$model = Enquiry::find()->orderBy(['created_at' => SORT_DESC])->asArray()->all();
		print_r($model);
		die();
		
		$arr=array();
		$arr['first_name']='Test';
		
		/*
		foreach()
		{
		   $arr['qualification'][]=array('qualification'=>$value->,'percentage'=>'90');
		}
		*/
		$arr['qualification'][]=array('qualification'=>'bca','percentage'=>'90');
		print_r($arr);
		echo '<br>';
		print_r(json_encode($arr));
	}
	
	public function actionPdfGeneration()
	{
		
		$mpdf = new \Mpdf\Mpdf();
		$mpdf->WriteHTML('<h1>Hello world!</h1>');
		$mpdf->Output();
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
					$MailLookupModel->cc='vasudhevanab@gmail.com';
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
	}
	
	public function actionAppreviewer()
	{
		$result = Yii::$app->globalfuns->getPrivilegeUser(50,'application_review');
		print_r($result);
	}
	public function actionGetyear()
	{
		return date('Y');
	}
	
	
	public function actionAppaddress()
	{
		$ApplicationModel=Application::find()->all();		
		if(count($ApplicationModel)>0)
		{
			foreach($ApplicationModel as $app)
			{
				$ApplicationChangeAddress = new ApplicationChangeAddress();				
				$ApplicationChangeAddress->customer_id = $app->customer_id;		
				$ApplicationChangeAddress->parent_app_id = $app->id;				
				$ApplicationChangeAddress->current_app_id = $ApplicationChangeAddress->parent_app_id;
				$ApplicationChangeAddress->company_name=$app->company_name;
				$ApplicationChangeAddress->address=$app->address;
				$ApplicationChangeAddress->zipcode=$app->zipcode;
				$ApplicationChangeAddress->city=$app->city;
				$ApplicationChangeAddress->state_id=$app->state_id;
				$ApplicationChangeAddress->country_id=$app->country_id;
				$ApplicationChangeAddress->salutation=$app->salutation;
				$ApplicationChangeAddress->title=$app->title;
				
				$ApplicationChangeAddress->unit_name=$ApplicationChangeAddress->company_name;
				$ApplicationChangeAddress->unit_address=$ApplicationChangeAddress->address;
				$ApplicationChangeAddress->unit_zipcode=$ApplicationChangeAddress->zipcode;
				$ApplicationChangeAddress->unit_city=$ApplicationChangeAddress->city;
				$ApplicationChangeAddress->unit_state_id=$ApplicationChangeAddress->state_id;
				$ApplicationChangeAddress->unit_country_id=$ApplicationChangeAddress->country_id;
								
				$ApplicationChangeAddress->first_name=$app->first_name;
				$ApplicationChangeAddress->last_name=$app->last_name;
				$ApplicationChangeAddress->job_title=$app->job_title;
				$ApplicationChangeAddress->telephone=$app->telephone;
				$ApplicationChangeAddress->email_address=$app->email_address;
				$ApplicationChangeAddress->save();
				
				$app->address_id = $ApplicationChangeAddress->id;
				$app->save();			
				
			}
		}	
	}
	
	
	public function actionAppunitsubtopic()
	{
		$ApplicationModel=Application::find()->all();		
		if(count($ApplicationModel)>0)
		{
			foreach($ApplicationModel as $app)
			{
				$ApplicationUnitSubtopicModel = ApplicationUnitSubtopic::find()->where(['app_id' => $app->id])->one();
				if($ApplicationUnitSubtopicModel===null)
				{
					$appunit = $app->applicationunit;
					if(count($appunit)>0)
					{
						foreach($appunit as $unit)
						{
							//$arrData = Yii::$app->globalfuns->getSubtopic($unit->id);
							$arrData = Yii::$app->globalfuns->getSubtopic($unit->id,'', '', 1);
							if(count($arrData)>0)
							{
								foreach($arrData as $subtopID)
								{
									echo 'App ID:'.$app->id.'---Unit ID:'.$unit->id.'---Sub Top ID:'.$subtopID['id'].'<br>';
									$appUnitSubTopic = new ApplicationUnitSubtopic();
									$appUnitSubTopic->app_id = $app->id;
									$appUnitSubTopic->unit_id = $unit->id;
									$appUnitSubTopic->subtopic_id = $subtopID['id'];
									$appUnitSubTopic->save();
								}
							}						
						}
						echo '<br>----------------------<br>';
					}
				}	
			}
		}
	}
	
	public function actionAuditexecution()
	{
		$auditM = new Audit();
		$AuditModel = Audit::find()->where(['status' => $auditM->arrEnumStatus['approved']])->all();
		if(count($AuditModel)>0)
		{
			foreach($AuditModel as $audit)
			{
				$auditplanObj = $audit->auditplan;
				if($auditplanObj!==null)
				{
					$auditplanunitM = $auditplanObj->auditplanunit;
					if(count($auditplanunitM)>0)
					{
						foreach($auditplanunitM as $planUnit)
						{
							echo $planUnit->id.'---';	
							$ApplicationUnitSubtopicModel = ApplicationUnitSubtopic::find()->where(['app_id' => $planUnit->app_id,'unit_id' => $planUnit->unit_id])->all();
							if(count($ApplicationUnitSubtopicModel)>0)
							{
								foreach($ApplicationUnitSubtopicModel as $unitsubT)
								{
									echo $unitsubT->subtopic_id.',';
									$AuditPlanUnitExecution = new AuditPlanUnitExecution();
									$AuditPlanUnitExecution->audit_plan_unit_id = $planUnit->id;
									$AuditPlanUnitExecution->sub_topic_id = $unitsubT->subtopic_id;
									$AuditPlanUnitExecution->status = 0;
									$AuditPlanUnitExecution->save();
								}
								echo '**';
							}
							//echo '<br>';
							
						}
					}
				}				
			}
		}
	}
	
	
	public function actionDeleteapp()
	{
		//$appIDsArray=array(451);
		$appIDsArray=array(495);
		$modelApp = Application::find()->where(['id' => $appIDsArray])->all();								
		if (count($modelApp)>0)
		{
			foreach($modelApp as $model)
			{
				$appStandard=$model->applicationstandard;
				if(count($appStandard)>0)
				{
					foreach($appStandard as $std)
					{
					$std->delete();	
					}
				}
				
				$applicationchecklistcmt=$model->applicationchecklistcmt;
				if(count($applicationchecklistcmt)>0)
				{
					foreach($applicationchecklistcmt as $chklist)
					{
						$chklist->delete();	
					}
				}

				$certificationbody=$model->certificationbody;
				if(count($certificationbody)>0)
				{
					foreach($certificationbody as $cbody)
					{
						$cbody->delete();	
					}
				}
				
				$appProduct=$model->applicationproduct;
				if(count($appProduct)>0)
				{
					foreach($appProduct as $prd)
					{
						$productstandards = $prd->productstandard;
						if(count($productstandards)>0)
						{
							foreach($productstandards as $productstandard)
							{
								$productstandard->delete();	
							}
						}
						
						$productmaterials = $prd->productmaterial;
						if(count($productmaterials)>0)
						{
							foreach($productmaterials as $productmaterial)
							{
								$productmaterial->delete();	
							}
						}
						$prd->delete();					
					}
				}
				
				$appUnit=$model->applicationunit;
				if(count($appUnit)>0)
				{
					foreach($appUnit as $unit)
					{
						$unitstd=$unit->unitstandard;
						if(count($unitstd)>0)
						{
							foreach($unitstd as $unitS)
							{
								$standardfile=$unitS->unitstandardfile;
								if(count($standardfile)>0)
								{
									foreach($standardfile as $stdfile)
									{
										$stdfile->delete();
									}
								}
								$unitS->delete();
							}
						}
						

						$unitprd=$unit->unitproduct;
						if(count($unitprd)>0)
						{
							foreach($unitprd as $unitP)
							{
								$unitP->delete();	
							}
						}	
						
						$unitbsector=$unit->unitbusinesssector;
						if(count($unitbsector)>0)
						{
							foreach($unitbsector as $unitbs)
							{
								$unitbs->delete();
							}						
						}

						$unitprocess=$unit->unitprocessall;
						if(count($unitprocess)>0)
						{
							foreach($unitprocess as $unitPcs)
							{
								$unitPcs->delete();
							}
						}
						$unitappstandard=$unit->unitappstandard;
						if(count($unitappstandard)>0)
						{
							foreach($unitappstandard as $unitappStd)
							{
								$unitappStd->delete();
							}
						}
						$unitstandard=$unit->unitstandard;
						if(count($unitstandard)>0)
						{
							foreach($unitstandard as $unitStd)
							{
								$unitStd->delete();
							}
						}
						$unit->delete();										
					}
				}
				$model->applicationaddress->delete();
				$model->delete();
			}	
		}	
	}
	
}
