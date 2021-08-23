<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\User;
use app\modules\master\models\UserRole;
use app\models\Enquiry;
use app\modules\master\models\Country;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\UserCompanyInfo;
use app\modules\master\models\MailLookup;
use app\modules\master\models\UserQualification;
use app\modules\master\models\UserTrainingInfo;
use app\modules\master\models\UserExperience;
use app\modules\master\models\UserCertification;

use app\modules\application\models\Application;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class CustomerController extends \yii\rest\Controller
{

    /**
     * @inheritdoc
     */
	 /*
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => \sizeg\jwt\JwtHttpBearerAuth::class,
        ];

        return $behaviors;
    }
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
		if(!Yii::$app->userrole->hasRights(array('customer_master')))
		{
			return false;
		}
		
        $post = yii::$app->request->post();
		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$model = User::find()->alias('t');
		$model = $model->joinWith('usercompanyinfo as usercompanyinfo');
		$model = $model->join('left join', 'tbl_country as companycountry','usercompanyinfo.company_country_id=companycountry.id');
		
		$model->andWhere(['t.user_type'=> 2]);
		$model->andWhere(['<>','t.status',2]);
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					/*['like', 'first_name', $searchTerm],										
					['like', 'last_name', $searchTerm],										
					['like', 'email', $searchTerm],										
					['like', 'telephone', $searchTerm],		
					*/
					['like', 'usercompanyinfo.company_name', $searchTerm],										
					['like', 'usercompanyinfo.contact_name', $searchTerm],										
					['like', 'usercompanyinfo.company_email', $searchTerm],										
					['like', 'usercompanyinfo.company_telephone', $searchTerm],	
					['like', 'companycountry.name', $searchTerm],
					['like', '(date_format(FROM_UNIXTIME(t.created_ats), \'%b %d, %Y\' ))', $searchTerm],
				]);
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
			
			$totalCount = $model->count();

            $model = $model->limit($pageSize)->offset($page);
		}
		else
		{
			$totalCount = $model->count();
		}
		
		$user_list=array();
		//$model = $model->asArray()->all();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $user)
			{
				$data=array();
				$data['id']=$user->id;
				$data['customer_number']=$user->customer_number ? $user->customer_number : 'NA';
				$data['first_name']=$user->first_name;
				$data['last_name']=$user->last_name;
				$data['email']=$user->email;
				$data['telephone']=$user->telephone;
				
				$data['company_country']=$user->usercompanyinfo->companycountry?$user->usercompanyinfo->companycountry->name:'';
				$data['company_name']=$user->usercompanyinfo?$user->usercompanyinfo->company_name:'';
				$data['contact_name']=$user->usercompanyinfo?$user->usercompanyinfo->contact_name:'';
				$data['company_telephone']=$user->usercompanyinfo?$user->usercompanyinfo->company_telephone:'';
				$data['company_email']=$user->usercompanyinfo?$user->usercompanyinfo->company_email:'';
				$data['status']=$user->status;
				//$data['created_at']=date('M d,Y h:i A',$user->created_at);
				$data['created_at']=date($date_format,$user->created_at);
				$user_list[]=$data;
			}
		}
		
		return ['franchises'=>$user_list,'total'=>$totalCount];
    }
	

	 
	// public function actionCreate()
	// {
	// 	$model = new User();
		
	// 	$model->setPassword('admin');
	// 	echo $model->password_hash;
	// 	die();
		
	// 	$model->generateAuthKey();
	// 	$model->generateEmailVerificationToken();
	// 	if($model->save())
	// 	{
	// 		$this->sendEmail($model);
	// 	}	
	// 	die();		
	// }

	public function actionCreate()
	{
		if(!Yii::$app->userrole->hasRights(array('add_customer')))
		{
			return false;
		}
		
		$model = new User();
		$UserCompanyInfo=new UserCompanyInfo();
		$modelUserRole = new UserRole();
		
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
			/*
			$model->first_name=$data['first_name'];
			$model->last_name=$data['last_name'];
			$model->email=$data['email'];
			$model->telephone=$data['telephone'];
			
			$model->country_id=$data['country_id'];
			$model->state_id=$data['state_id'];
			*/
			
			$model->user_type=2;
			
			$maxid = User::find()->where(['user_type'=>2])->max('id');
			$countrymodel=Country::find()->where(['id'=>$dbdata['company_country_id']])->one();
			$countrycode=$countrymodel['code'];
			if(!empty($maxid)) 
			{
				$maxid = $maxid+1;
				$userregid="GCL-CUS-".$countrycode."-".$maxid;
			}
			else
			{
				$userregid="GCL-CUS-".$countrycode."-1";
			}
			$model->registration_id=$userregid;
			
			//$model->date_of_birth=isset($data['date_of_birth'])?$data['date_of_birth']:'0000-00-00';
			//$model->is_auditor=isset($data['is_auditor'])?$data['is_auditor']:0;

			//$username=$model->generateUsername();
			//$model->setUsername($username);
			//$password=$model->generatePassword();
			//$model->setPassword($password);
			
			$modelUserRole->generateEmailVerificationToken();
			$modelUserRole->status=isset($data['status'])?$data['status']:0;
			$modelUserRole->role_id=isset($data['role_id'])?$data['role_id']:0;
			$model->send_mail_notification_status=isset($data['send_mail_notification_status'])?$data['send_mail_notification_status']:0;
			
			$userData = Yii::$app->userdata->getData();
			$model->created_by=$userData['userid'];
			$modelUserRole->created_by=$userData['userid'];

			$model->customer_number=isset($data['customer_number'])?$data['customer_number']:'';
			//$modelUserRole->approval_status=2;
			$model->first_name = isset($data['contact_name']) ? $data['contact_name'] :'';
			$model->last_name = '';
			if($model->validate() && $model->save())
			{
				$modelUserRole->user_id=$model->id;
				if($modelUserRole->validate())
				{
					$modelUserRole->save();
				}	
				
				$UserCompanyInfo->user_id=$model->id;
				$UserCompanyInfo->company_name=isset($data['company_name']) ? $data['company_name'] :'';
				$UserCompanyInfo->contact_name=isset($data['contact_name']) ? $data['contact_name'] :'';
				$UserCompanyInfo->company_telephone=isset($data['company_telephone']) ? $data['company_telephone'] :'';
				$UserCompanyInfo->company_email=isset($data['company_email']) ? $data['company_email'] :'';
				$UserCompanyInfo->company_website=isset($data['company_website']) ? $data['company_website'] :'';
				$UserCompanyInfo->company_address1=isset($data['company_address1']) ? $data['company_address1'] :'';
				$UserCompanyInfo->company_address2=isset($data['company_address2']) ? $data['company_address2'] :'';
				$UserCompanyInfo->company_city=isset($data['company_city']) ? $data['company_city'] :'';
				$UserCompanyInfo->company_zipcode=isset($data['company_zipcode']) ? $data['company_zipcode'] :'';
				$UserCompanyInfo->company_country_id=isset($data['company_country_id']) ? $data['company_country_id'] :'';
				$UserCompanyInfo->company_state_id=isset($data['company_state_id']) ? $data['company_state_id'] :'';
				$UserCompanyInfo->number_of_employees=isset($data['number_of_employees']) ? $data['number_of_employees'] :'';
				$UserCompanyInfo->number_of_sites=isset($data['number_of_sites']) ? $data['number_of_sites'] :'';
				$UserCompanyInfo->description=isset($data['description']) ? $data['description'] :'';
				$UserCompanyInfo->other_information=isset($data['other_information']) ? $data['other_information'] :'';
				if($UserCompanyInfo->validate())
				{
					$UserCompanyInfo->save();
				}				

				$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'sign_up'])->one();
				if($mailContent !== null)
				{
					$verifyLink = Yii::$app->params['site_path'].'change-username-password?token='.$modelUserRole->verification_token;
					$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'sign_up'])->one();

					$mailmsg=str_replace('{USERNAME}', $model->first_name." ".$model->last_name, $mailContent['message'] );
					$mailmsg=str_replace('{VERIFYLINK}', $verifyLink, $mailmsg);
		
					// Mail to Customer with Login credentials
					$MailLookupModel = new MailLookup();
					$MailLookupModel->to=$UserCompanyInfo->company_email;					
					$MailLookupModel->subject=$mailContent['subject'];
					$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
					$MailLookupModel->attachment='';
					$MailLookupModel->mail_notification_id='';
					$MailLookupModel->mail_notification_code='';
					$Mailres=$MailLookupModel->sendMail();
					$responsedata=array('status'=>1,'message'=>'Customer has been created successfully');	
				}
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}
		}
		return $this->asJson($responsedata);
	}

	public function actionUpdate()
	{
		if(!Yii::$app->userrole->hasRights(array('edit_customer')))
		{
			return false;
		}
		
		$model = new User();
		$UserCompanyInfo=new UserCompanyInfo();
		$data = Yii::$app->request->post();
		if ($data) 
		{
			$model = User::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				//$model->date_of_birth=$data['date_of_birth'];
				//$model->send_mail_notification_status=$data['send_mail_notification_status'];
				$model->user_type=2;
				$model->status=$data['status'];
				$model->customer_number=isset($data['customer_number'])?$data['customer_number']:'';

				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];
				$model->first_name = isset($data['contact_name']) ? $data['contact_name'] :'';
				$model->last_name = '';
				if($model->validate() && $model->save())
				{
					UserCompanyInfo::deleteAll(['user_id' => $data['id']]);
					
					$UserCompanyInfo->user_id=$model->id;
					$UserCompanyInfo->company_name=isset($data['company_name']) ? $data['company_name'] :'';
					$UserCompanyInfo->contact_name=isset($data['contact_name']) ? $data['contact_name'] :'';
					$UserCompanyInfo->company_telephone=isset($data['company_telephone']) ? $data['company_telephone'] :'';
					$UserCompanyInfo->company_email=isset($data['company_email']) ? $data['company_email'] :'';
					$UserCompanyInfo->company_website=isset($data['company_website']) ? $data['company_website'] :'';
					$UserCompanyInfo->company_address1=isset($data['company_address1']) ? $data['company_address1'] :'';
					$UserCompanyInfo->company_address2=isset($data['company_address2']) ? $data['company_address2'] :'';
					$UserCompanyInfo->company_city=isset($data['company_city']) ? $data['company_city'] :'';
					$UserCompanyInfo->company_zipcode=isset($data['company_zipcode']) ? $data['company_zipcode'] :'';
					$UserCompanyInfo->company_country_id=isset($data['company_country_id']) ? $data['company_country_id'] :'';
					$UserCompanyInfo->company_state_id=isset($data['company_state_id']) ? $data['company_state_id'] :'';
					$UserCompanyInfo->number_of_employees=isset($data['number_of_employees']) ? $data['number_of_employees'] :'';
					$UserCompanyInfo->number_of_sites=isset($data['number_of_sites']) ? $data['number_of_sites'] :'';
					$UserCompanyInfo->description=isset($data['description']) ? $data['description'] :'';
					$UserCompanyInfo->other_information=isset($data['other_information']) ? $data['other_information'] :'';
					if($UserCompanyInfo->validate())
					{
						$UserCompanyInfo->save();
					}			
					
					$responsedata=array('status'=>1,'message'=>'Customer has been updated successfully');	
				}
				else
				{
					$responsedata=array('status'=>0,'message'=>$model->errors);
				}
			}
		}
		return $this->asJson($responsedata);
	}

	public function actionFetchUser()
	{
		
		$result=array();
		$postdata = Yii::$app->request->post();
		$getdata = Yii::$app->request->get();
		if($postdata){
			$data = $postdata;
		}else{
			$data = $getdata;
			if(isset($data['app_id']) && $data['app_id'] !='' && $data['app_id'] >0){
				$Application = Application::find()->where(['id'=>$data['app_id']])->one();
				if($Application !== null){
					$data['id'] = $Application->customer_id;
				}
			}
			//echo $data['id']; die;
		}
		if($data) 
		{
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			$Usermodel = User::find()->select('id,customer_number,first_name,last_name,email,telephone,country_id,state_id,date_of_birth,user_type,status,created_at,updated_at,created_by')->where(['id' => $data['id']])->one();
			
			if ($Usermodel !== null)
			{
				$resultarr=array();
				//if(count($Usermodel)>0)
				//{
				foreach($Usermodel as $key => $value)
				{
					$resultarr[$key]=$value;
				}
				//}
				
				$resultarr['country_name'] = $Usermodel->country ? $Usermodel->country->name : '';
				$resultarr['state_name'] = $Usermodel->state ? $Usermodel->state->name : '';
				$resultarr['customer_number'] = $Usermodel->customer_number ? $Usermodel->customer_number : 'NA';
				
				$resultarr['created_at']=date($date_format,$Usermodel->created_at);
				$resultarr['created_by']=$Usermodel->username->first_name.' '.$Usermodel->username->last_name;

				$UserCompanyInfo = UserCompanyInfo::find()->where(['user_id' => $data['id']])->one();
				$resultarr["company_name"]=$UserCompanyInfo->company_name;
				$resultarr["contact_name"]=$UserCompanyInfo->contact_name;
				$resultarr["company_telephone"]=$UserCompanyInfo->company_telephone;
				$resultarr["company_email"]=$UserCompanyInfo->company_email;
				$resultarr["company_website"]=$UserCompanyInfo->company_website;
				$resultarr["company_address1"]=$UserCompanyInfo->company_address1;
				$resultarr["company_address2"]=$UserCompanyInfo->company_address2;
				$resultarr["company_city"]=$UserCompanyInfo->company_city;
				$resultarr["company_zipcode"]=$UserCompanyInfo->company_zipcode;
				$resultarr["company_country_id"]=$UserCompanyInfo->company_country_id;
				$resultarr["company_state_id"]=$UserCompanyInfo->company_state_id;
				$resultarr["number_of_employees"]=$UserCompanyInfo->number_of_employees;
				$resultarr["number_of_sites"]=$UserCompanyInfo->number_of_sites;
				$resultarr["description"]=$UserCompanyInfo->description;
				$resultarr["other_information"]=$UserCompanyInfo->other_information;
				
				$resultarr['company_country']=$UserCompanyInfo->companycountry?$UserCompanyInfo->companycountry->name:'';
				$resultarr['company_state']=$UserCompanyInfo->companystate?$UserCompanyInfo->companystate->name:'';
				$resultarr['company_city']=$UserCompanyInfo->companycountry?$UserCompanyInfo->company_city:'';				
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$Usermodel->errors);
			}

			return ['data'=>$resultarr];
		}		
	}

	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data && isset($data['status'])) 
		{
			$status = $data['status'];			
			if(!Yii::$app->userrole->canDoCommonUpdate($status,'customer'))
			{
				return false;
			}
			
           	$model = User::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Customer has been activated successfully';
					}elseif($model->status==1){
						$msg='Customer has been deactivated successfully';
					}elseif($model->status==2){
						$msg='Customer has been deleted successfully';
					}
					$responsedata=array('status'=>1,'message'=>$msg);
				}
				else
				{
					$arrerrors=array();
					$errors=$model->errors;
					if(is_array($errors) && count($errors)>0)
					{
						foreach($errors as $err)
						{
							$arrerrors[]=implode(",",$err);
						}
					}
					$responsedata=array('status'=>0,'message'=>implode(",",$arrerrors));
				}
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}
            return $this->asJson($responsedata);
        }
	}
	
	/*
	When New customer created through Enquiry
	*/
	public function actionCreateCustomer()
	{
		if(!Yii::$app->userrole->hasRights(array('forward_enquiry')))
		{
			return false;
		}
		
		$Usermodel = new User();
		$Enquirymodel = new Enquiry();
		$mailmodel=new MailNotifications();
		$UserCompanyInfo=new UserCompanyInfo();
		$modelUserRole = new UserRole();
		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		
		$data = Yii::$app->request->post();
		if ($data) 
		{
			$dbdata = Enquiry::find()->where(['id' => $data['id']])->one();			
			$Usermodel->user_type="2";

			$maxid = User::find()->where(['user_type'=>2])->max('id');
			$countrymodel=Country::find()->where(['id'=>$dbdata['company_country_id']])->one();
			$countrycode=$countrymodel['code'];
			if(!empty($maxid)) 
			{
				$maxid = $maxid+1;
				$userregid="GCL-CUS-".$countrycode."-".$maxid;
			}
			else
			{
				$userregid="GCL-CUS-".$countrycode."-1";
			}
			$Usermodel->registration_id=$userregid;			
			$modelUserRole->generateEmailVerificationToken();
			$modelUserRole->status=0;
			$modelUserRole->role_id=0;
			$Usermodel->send_mail_notification_status=0;
						
			$userData = Yii::$app->userdata->getData();
			$Usermodel->created_by=$userData['userid'];
			$modelUserRole->created_by=$userData['userid'];
			$Usermodel->first_name = $dbdata['contact_name'];
			$Usermodel->created_by=$userData['userid'];
			$Usermodel->franchise_id = $data['franchise_id'];
			if($Usermodel->validate() && $Usermodel->save())
        	{
				$modelUserRole->user_id=$Usermodel->id;
				if($modelUserRole->validate())
				{
					$modelUserRole->save();
				}
				
				$UserCompanyInfo->user_id=$Usermodel->id;
				$UserCompanyInfo->company_name=$dbdata['company_name'];
				$UserCompanyInfo->contact_name=$dbdata['contact_name'];
				$UserCompanyInfo->company_telephone=$dbdata['company_telephone'];
				$UserCompanyInfo->company_email=$dbdata['company_email'];
				$UserCompanyInfo->company_website=$dbdata['company_website'];
				$UserCompanyInfo->company_address1=$dbdata['company_address1'];
				$UserCompanyInfo->company_address2=$dbdata['company_address2'];
				$UserCompanyInfo->company_city=$dbdata['company_city'];
				$UserCompanyInfo->company_zipcode=$dbdata['company_zipcode'];
				$UserCompanyInfo->company_country_id=$dbdata['company_country_id'];
				$UserCompanyInfo->company_state_id=$dbdata['company_state_id'];
				$UserCompanyInfo->number_of_employees=$dbdata['number_of_employees'];
				$UserCompanyInfo->number_of_sites=$dbdata['number_of_sites'];
				$UserCompanyInfo->description=$dbdata['description'];
				$UserCompanyInfo->other_information=$dbdata['other_information'];				
								
				if($UserCompanyInfo->validate() && $UserCompanyInfo->save())
				{
					$dbdata->status = 2;
					$dbdata->status_updated_date = time();
					$dbdata->status_updated_by = 2;
					$dbdata->customer_id = $Usermodel->id;
					$dbdata->franchise_id=($data['sel_franchise']!="2")?$data['franchise_id']:"";
					$dbdata->save();
															
					$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'sign_up'])->one();

					if($mailContent !== null)
					{
						$companygrid = $this->renderPartial('@app/mail/layouts/EnquiryCompanyGridTemplate',[
						'model' => $dbdata
						]);
					
						// $verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['site/password-reset', 'token' => $Usermodel->verification_token]);
						$verifyLink = Yii::$app->params['site_path'].'change-username-password?token='.$modelUserRole->verification_token;
						$mailmsg=str_replace('{USERNAME}', $UserCompanyInfo->contact_name, $mailContent['message'] );
						$mailmsg=str_replace('{VERIFYLINK}', $verifyLink, $mailmsg);
						$mailmsg=str_replace('{COMPANY-DETAILS-GRID}', $companygrid, $mailmsg);
						// $mailmsg=str_replace('{LOGINUSERNAME}', $Usermodel->username, $mailmsg );
						// $mailmsg=str_replace('{PASSWORD}', $password, $mailmsg );

						// Mail to Customer with Login credentials
						$MailLookupModel = new MailLookup();
						$MailLookupModel->to=$UserCompanyInfo->company_email;												
						$MailLookupModel->cc='enquiry@gcl-intl.com';						
						$MailLookupModel->subject=$mailContent['subject'];
						$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
						$MailLookupModel->attachment='';
						$MailLookupModel->mail_notification_id='';
						$MailLookupModel->mail_notification_code='';
						$Mailres=$MailLookupModel->sendMail();
						$responsedata=array('status'=>1,'message'=>'Customer has been created successfully','enquirystatus'=>$Enquirymodel->arrStatus[$dbdata->status],'status_updated_date'=>date($date_format,$dbdata->status_updated_date));						
					}				
	
					$companygrid = $this->renderPartial('@app/mail/layouts/EnquiryCompanyGridTemplate',[
						'model' => $dbdata
					]);

					$standardgrid = $this->renderPartial('@app/mail/layouts/EnquiryStandardGridTemplate',[
						'model' => $dbdata
					]);

					

					$dbContent = MailNotifications::find()->select('subject,message')->where(['code' => 'enquiry_request_to_franchise'])->one();

					if($mailContent !== null)
					{	
						$franchisemailid = UserCompanyInfo::find()->where(['user_id' => $data['franchise_id']])->asArray()->one();
						if($franchisemailid !== null)
						{
							$adminmailsubject=str_replace('{USERNAME}', $UserCompanyInfo->company_name, $dbContent['subject'] );
							//$adminmailmsg=str_replace('{CUSTOMER-DETAILS-GRID}', $customergrid, $dbContent['message'] );
							$adminmailmsg=str_replace('{COMPANY-DETAILS-GRID}', $companygrid, $dbContent['message'] );
							$adminmailmsg=str_replace('{STANDARD-DETAILS-GRID}', $standardgrid, $adminmailmsg );
							
							$MailLookupModel = new MailLookup();
							$MailLookupModel->to=$franchisemailid['company_email'];							
							$MailLookupModel->subject=$adminmailsubject;
							$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $adminmailmsg]);
							$MailLookupModel->attachment='';
							$MailLookupModel->mail_notification_id='';
							$MailLookupModel->mail_notification_code='';
							$Mailres=$MailLookupModel->sendMail();
						}
					}
					
				}else{
					$responsedata=array('status'=>0,'message'=>'Customer has been created successfully');
				}
			}else{
				$errorContent='';
				$error = $Usermodel->getErrors();
				if(is_array($error) && count($error)>0)
				{
					foreach($error as $err)
					{
					  $errorContent.=$err[0];
					}
				}
				$responsedata=array('status'=>0,'message'=>$errorContent);
			}
			
		}
		return $this->asJson($responsedata);
		
	}
}