<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\User;
use app\modules\application\models\ApplicationUnit;
use app\modules\master\models\AuditBrandConsent;
use app\modules\master\models\Country;
use app\modules\master\models\UserRole;
use app\modules\master\models\Enquiry;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\UserCompanyInfo;
use app\modules\master\models\MailLookup;
use app\modules\master\models\Brand;
use app\modules\master\models\BrandGroup;
use app\modules\master\models\UserQualification;
use app\modules\master\models\UserTrainingInfo;
use app\modules\master\models\UserExperience;
use app\modules\master\models\UserCertification;
use app\modules\master\models\UserPaymentDetails;
use app\modules\master\models\CertificateRoyaltyFee;
use app\modules\master\models\CertificateRoyaltyFeeCs;
use app\modules\master\models\CertificateTcRoyaltyFee;
use app\modules\master\models\CertificateTcRoyaltyFeeCs;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class BrandsController extends \yii\rest\Controller
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
	
	
	public function actionGetStandardrights()
    {
		$accessrights['add_royalty_fee'] = 0;
		$accessrights['edit_royalty_fee'] = 0;
		$accessrights['view_royalty_fee'] = 0;
		$accessrights['delete_royalty_fee'] = 0;
		if(Yii::$app->userrole->isAdmin()){
			$accessrights['add_royalty_fee'] = 1;
			$accessrights['edit_royalty_fee'] = 1;
			$accessrights['view_royalty_fee'] = 1;
			$accessrights['delete_royalty_fee'] = 1;
		}else{
			if(Yii::$app->userrole->hasRights(array('add_royalty_fee')))
			{
				$accessrights['add_royalty_fee'] = 1;
			}
			if(Yii::$app->userrole->hasRights(array('edit_royalty_fee')))
			{
				$accessrights['edit_royalty_fee'] = 1;
			}
			if(Yii::$app->userrole->hasRights(array('view_royalty_fee')))
			{
				$accessrights['view_royalty_fee'] = 1;
			}
			if(Yii::$app->userrole->hasRights(array('delete_royalty_fee')))
			{
				$accessrights['delete_royalty_fee'] = 1;
			}
		}
		
		return $accessrights;
	}
	
	public function actionGetTcFeeRights()
    {
		$accessrights['add_tc_fee'] = 0;
		$accessrights['edit_tc_fee'] = 0;
		$accessrights['view_tc_fee'] = 0;
		$accessrights['delete_tc_fee'] = 0;
		if(Yii::$app->userrole->isAdmin()){
			$accessrights['add_tc_fee'] = 1;
			$accessrights['edit_tc_fee'] = 1;
			$accessrights['view_tc_fee'] = 1;
			$accessrights['delete_tc_fee'] = 1;
		}else{
			if(Yii::$app->userrole->hasRights(array('add_tc_fee')))
			{
				$accessrights['add_tc_fee'] = 1;
			}
			if(Yii::$app->userrole->hasRights(array('edit_tc_fee')))
			{
				$accessrights['edit_tc_fee'] = 1;
			}
			if(Yii::$app->userrole->hasRights(array('view_tc_fee')))
			{
				$accessrights['view_tc_fee'] = 1;
			}
			if(Yii::$app->userrole->hasRights(array('delete_tc_fee')))
			{
				$accessrights['delete_tc_fee'] = 1;
			}
		}
		
		return $accessrights;
	}

	public function actionIndex()
    {
		if(!Yii::$app->userrole->hasRights(array('franchise_master')))
		{
			return false;
		}
		
        $post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		

		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];

		$model = User::find()->alias('t');
		$model = $model->joinWith('usercompanyinfo as usercompanyinfo');
		$model = $model->join('left join', 'tbl_country as companycountry','usercompanyinfo.company_country_id=companycountry.id');
		
		if(isset($post['countryFilter']) && is_array($post['countryFilter']) && count($post['countryFilter'])>0)
		{
			$model = $model->andWhere(['usercompanyinfo.company_country_id'=> $post['countryFilter']]);			
		}

		if($is_headquarters != 1){
			if($user_type==3)
			{
				if($resource_access==5){
					$model = $model->andWhere(['t.id'=>$franchiseid]);
				}else{
					$model = $model->andWhere(['t.id'=>$userid]);				
				}
			}else if($user_type==1){
				$model = $model->andWhere(['t.id'=>$franchiseid]);
			}
		}



		$model->join('inner join','tbl_brands as brand','t.id=brand.user_id');
		$model->andWhere(['t.user_type'=> 1]);
		$model->andWhere(['<>','t.status',2]);
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm']!='')
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 't.registration_id', $searchTerm],
					['like', 'usercompanyinfo.company_name', $searchTerm],										
					['like', 'usercompanyinfo.contact_name', $searchTerm],										
					['like', 'usercompanyinfo.company_email', $searchTerm],										
					['like', 'usercompanyinfo.company_telephone', $searchTerm],	
					['like', 'companycountry.name', $searchTerm],
					['like', '(date_format(FROM_UNIXTIME(t.created_at), \'%b %d, %Y\' ))', $searchTerm],
				]);
			}
			
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
			}
			else
			{
				$model = $model->orderBy(['(t.created_at)' => SORT_ASC]);
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
				$data['registration_id']=$user->registration_id;
				/*
				$data['first_name']=$user->first_name;
				$data['last_name']=$user->last_name;
				$data['email']=$user->email;
				$data['telephone']=$user->telephone;
				*/
				
				$data['headquarters']=$user->headquarters;
				
				
				
				
				$data['company_country']=$user->usercompanyinfo && $user->usercompanyinfo->companycountry?$user->usercompanyinfo->companycountry->name:'';
				$data['company_name']=$user->usercompanyinfo?$user->usercompanyinfo->company_name:'';
				$data['contact_name']=$user->usercompanyinfo?$user->usercompanyinfo->contact_name:'';
				$data['company_telephone']=$user->usercompanyinfo?$user->usercompanyinfo->company_telephone:'';
				$data['company_email']=$user->usercompanyinfo?$user->usercompanyinfo->company_email:'';
				$data['status']=$user->status;
				$data['brand_name']=$user->userbrand?$user->userbrand->name:'';
				$data['version']=$user->userbrand?$user->userbrand->version:'';
				$data['brand_group']=$user->userbrand->brandgroup?$user->userbrand->brandgroup->name:'';
				$data['login_status']=true;
				$data['created_at']=date($date_format,$user->created_at);
				
				
				$user_list[]=$data;
			}
		}
		
		return ['franchises'=>$user_list,'total'=>$totalCount];
    }

	public function actionBrandConsent()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$datapost = Yii::$app->request->post();
		$data = json_decode($datapost['formvalues'],true);
		$userData = Yii::$app->userdata->getData();

		if($data){
			$appunitmod = ApplicationUnit::find()->where(['app_id'=>$data['app_id'],'id'=>$data['unit_id']])->one();

			if($appunitmod!='' && $appunitmod!==null){
				$appunitmod->is_brand_consent = $data['sel_brand_ch'];
				$target_dir_audit = Yii::$app->params['audit_files'];
			
				if(isset($_FILES['brand_file']['name']))
				{
				$tmp_name = $_FILES["brand_file"]["tmp_name"];
	   			$name = $_FILES["brand_file"]["name"];
				$appunitmod->brand_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir_audit);	
				}
				if($appunitmod->validate() && $appunitmod->save()){
					AuditBrandConsent::deleteAll(['app_id'=>$data['app_id'],'unit_id'=>$data['unit_id']]);
					$brandIds =[];
					
					if(is_array($data['brand_id'])){
						$brandIds = $data['brand_id'];
						
						foreach($brandIds as $bid){
							$auditconsentmod = new AuditBrandConsent();
							$auditconsentmod->app_id=$data['app_id'];
							$auditconsentmod->unit_id=$data['unit_id'];
							$auditconsentmod->brand_id=$bid;
							$auditconsentmod->created_by=$userData['userid'];
							$auditconsentmod->updated_by=$userData['userid'];
							
							$auditconsentmod->save();
						}
						
					}
					$responsedata = array('status'=>1,'message'=>'Audit Brand Consent has been created successfully',);
				}
			}
		}
		return $responsedata;
	}

	public function actionGetConsent()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();

		
		if($data){
			$audconbrmod = AuditBrandConsent::find()->where(['app_id'=>$data['app_id'],'unit_id'=>$data['unit_id']])->all();
			$brdata= array();
			if(count($audconbrmod)>0){
				
				foreach($audconbrmod as $abc){
					
					// $resultarr = array();
					// $resultarr['app_id']=$abc['app_id'];
					// $resultarr['unit_id']=$abc['unit_id'];
					// $resultarr['brand_id']=$abc['brand_id'];
					$brdata[]= $abc['brand_id'];
				}
			}
			$bfile='';
			$bconsent='';
			$appunitmodel = ApplicationUnit::find()->where(['app_id'=>$data['app_id'],'id'=>$data['unit_id']])->one();

			if($appunitmodel!='' && $appunitmodel!=null){
				$bfile=$appunitmodel->brand_file;
				$bconsent=$appunitmodel->is_brand_consent;
			}
			return $responsedata = array('status'=>1,'brand_id'=>$brdata,'brand_file'=>$bfile,'sel_brand_ch'=>$bconsent);
		}
		return $responsedata;
	}

	public function actionCreate()
	{
		if(!Yii::$app->userrole->hasRights(array('edit_brand')))
		{
			return false;
		}
		
		$model = new User();
		$UserCompanyInfo=new UserCompanyInfo();
		$modelUserRole = new UserRole();
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
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
			
			$model->user_type='1';
			$ccode = Country::find()->where(['id'=>$data['company_country_id']])->one();
			$maxid = User::find()->where(['user_type'=>1])->max('id');
			$countrycode=$ccode->code;
			if(!empty($maxid)) 
			{
				$maxid = $maxid+1;
				$userregid="GCL-BRN-".$maxid;
			}
			else
			{
				$userregid="GCL-BRN-1";
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
			$modelUserRole->approval_status=2;
			$modelUserRole->role_id=isset($data['branduser'])?$data['branduser']:0;
			$modelUserRole->franchise_id=57;
			$model->send_mail_notification_status=isset($data['send_mail_notification_status'])?$data['send_mail_notification_status']:0;
			
			
			$userData = Yii::$app->userdata->getData();
			$model->created_by=$userData['userid'];
			$modelUserRole->created_by=$userData['userid'];
			$model->first_name = isset($data['contact_name']) ? $data['contact_name'] :'';
			$model->last_name = '';
			if($model->validate() && $model->save())
			{
				$modelUserRole->user_id=$model->id;
				if($modelUserRole->validate())
				{
					$modelUserRole->save();
				}

				// if(is_array($data['payment_details']) && count($data['payment_details'])>0)
				// {
				// 	foreach ($data['payment_details'] as $value)
				// 	{ 
				// 		$qualmodel=new UserPaymentDetails();
				// 		$qualmodel->user_id=$model->id;
				// 		$qualmodel->payment_label=$value['payment_label'];	
				// 		$qualmodel->payment_content=$value['payment_content'];					
				// 		$qualmodel->save();
				// 	}
				// }
				
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
				$UserCompanyInfo->mobile=isset($data['mobile']) ? $data['mobile'] :'';
				$UserCompanyInfo->gst_no=isset($data['gst_no']) ? $data['gst_no'] :'';


				
				if($UserCompanyInfo->validate())
				{
					$UserCompanyInfo->save();
				}				
				
                $brandmode = new Brand();
				$brandmode->user_id = $model->id;
                $brandmode->brand_group_id = $data['brandgroup'];
                $brandmode->name = $data['company_name'];
				$brandmode->version = $data['version'];

                if($brandmode->validate()){
                    $brandmode->save();
                }

				$companygrid = $this->renderPartial('@app/mail/layouts/EnquiryCompanyGridTemplate',[
					'model' => $UserCompanyInfo
				]);

				$verifyLink = Yii::$app->params['site_path'].'change-username-password?token='.$modelUserRole->verification_token;
				$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'sign_up'])->one();

				$mailmsg=str_replace('{USERNAME}', $UserCompanyInfo->contact_name, $mailContent['message'] );
				$mailmsg=str_replace('{COMPANY-DETAILS-GRID}', $companygrid, $mailmsg );
				$mailmsg=str_replace('{VERIFYLINK}', $verifyLink, $mailmsg);
				// $mailmsg=str_replace('{LOGINUSERNAME}', $model->username, $mailmsg );
				// $mailmsg=str_replace('{PASSWORD}', $password, $mailmsg );

				// Mail to Customer with Login credentials
				$MailLookupModel = new MailLookup();
				$MailLookupModel->to=$UserCompanyInfo->company_email;				
				$MailLookupModel->subject=$mailContent['subject'];
				$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
				$MailLookupModel->attachment='';
				$MailLookupModel->mail_notification_id='';
				$MailLookupModel->mail_notification_code='';
				$Mailres=$MailLookupModel->sendMail();
				$responsedata=array('status'=>1,'message'=>'Brand has been created successfully');	
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}
		}
		return $this->asJson($responsedata);
	}

    public function actionBrandGroup(){
        $data = array();
        $resultArr =[];

        $brdgromod = BrandGroup::find()->all();
        if(count($brdgromod)>0){
            foreach($brdgromod as $bg){
                $data['id']= $bg['id'];
                $data['name']= $bg['name'];
                $resultArr[] = $data;
                
            }
            $responsedata = array('status'=>1,'data'=>$resultArr);
        }
        else{
            $responsedata = array('status'=>0,'message'=>'No Records Found!');
        }
        return $responsedata;
    }

	public function actionUpdate()
	{
		if(!Yii::$app->userrole->hasRights(array('edit_franchise')))
		{
			return false;
		}
		
		$model = new User();
		$UserCompanyInfo=new UserCompanyInfo();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{
			$model = User::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				/*
				$model->first_name=$data['first_name'];
				$model->last_name=$data['last_name'];
				$model->email=$data['email'];
				$model->telephone=$data['telephone'];
				//$model->role_id=$data['role_id'];
				$model->country_id=$data['country_id'];
				$model->state_id=$data['state_id'];
				*/
				
				//$model->date_of_birth=$data['date_of_birth'];
				//$model->send_mail_notification_status=$data['send_mail_notification_status'];
				$model->user_type=1;
				
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
					// $UserCompanyInfo->number_of_employees=isset($data['number_of_employees']) ? $data['number_of_employees'] :'';
					// $UserCompanyInfo->number_of_sites=isset($data['number_of_sites']) ? $data['number_of_sites'] :'';
					// $UserCompanyInfo->description=isset($data['description']) ? $data['description'] :'';
					// $UserCompanyInfo->other_information=isset($data['other_information']) ? $data['other_information'] :'';
					// $UserCompanyInfo->osp_details=isset($data['osp_details']) ? $data['osp_details'] :'';
					// $UserCompanyInfo->osp_number=isset($data['osp_number']) ? $data['osp_number'] :'';
					$UserCompanyInfo->mobile=isset($data['mobile']) ? $data['mobile'] :'';
					$UserCompanyInfo->gst_no=isset($data['gst_no']) ? $data['gst_no'] :'';

					if($UserCompanyInfo->validate())
					{
						$UserCompanyInfo->save();
					}	
					
					$brandmodel = Brand::find()->where(['user_id'=>$data['id']])->one();
					if($brandmodel!==null && $brandmodel!==''){
						$brandmodel->name=$data['company_name'];
						$brandmodel->brand_group_id=$data['brandgroup'];
						$brandmodel->version=$data['version'];
						$brandmodel->save();
					}

					

					$responsedata=array('status'=>1,'message'=>'Brand has been updated successfully');	
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
		$userData = Yii::$app->userdata->getData();
		$is_headquarters =$userData['is_headquarters'];
		$result=array();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{		
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			$Usermodel = User::find()->where(['id' => $data['id']])->one();
			
			if ($Usermodel !== null)
			{
				/*
				if($Usermodel->user_type!='2')
				{
					$Usermodel->country_id=$Usermodel->country->name;
					$Usermodel->state_id=$Usermodel->state->name;
				}
				*/
				
				$resultarr=array();
				//if(count($Usermodel)>0)
				//{
				foreach($Usermodel as $key => $value)
				{
					$resultarr[$key]=$value;
				}
				//}
				
				$resultarr['country_name']=$Usermodel->country ? $Usermodel->country->name : '';
				$resultarr['state_name']=$Usermodel->state ? $Usermodel->state->name : '';
				
				$HQLabel='No';
				if($is_headquarters==1)
				{
					$HQLabel='Yes';
				}
				$resultarr['headquarters']=$HQLabel;
				
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
				$resultarr["osp_details"]=$UserCompanyInfo->osp_details;
				$resultarr["osp_number"]=$UserCompanyInfo->osp_number;
				$resultarr["mobile"]=$UserCompanyInfo->mobile;
				$resultarr["gst_no"]=$UserCompanyInfo->gst_no;

				$resultarr['company_country']=$UserCompanyInfo->companycountry?$UserCompanyInfo->companycountry->name:'';
				$resultarr['company_state']=$UserCompanyInfo->companystate?$UserCompanyInfo->companystate->name:'';
				$resultarr['company_city']=$UserCompanyInfo->companycountry?$UserCompanyInfo->company_city:'';

				$resultarr['brand_group_name']=$Usermodel->userbrand->brandgroup->name?$Usermodel->userbrand->brandgroup->name:'';
				$resultarr['brandgroup']=$Usermodel->userbrand->brandgroup->id?$Usermodel->userbrand->brandgroup->id:'';
				$resultarr['version']=$Usermodel->userbrand->version?$Usermodel->userbrand->version:'';
				$resultarr['brand_name']=$Usermodel->userbrand->name?$Usermodel->userbrand->name:'';
				$resultarr['brand_id']=$Usermodel->userbrand->id?$Usermodel->userbrand->id:'';
				
				
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}

			return ['data'=>$resultarr];
		}	
	}

	public function actionChangePassword()
	{	
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{		
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			$Usermodel = User::find()->where(['id' => $data['oss_id']])->one();
			
			if ($Usermodel !== null)
			{
				$UserRolemodel = UserRole::find()->where(['user_id' => $Usermodel->id])->one();
				if ($UserRolemodel !== null)
				{
					$new_password=Yii::$app->security->generatePasswordHash($data['user_password']);
					if(Yii::$app->security->validatePassword($data['user_password'], $UserRolemodel->password_hash))
					{
						$responsedata=array('status'=>0,'message'=>'New Password Should not be the same as Current Password! Try another');
					}
					else
					{
						$UserRolemodel->password_hash = $new_password;
						if($UserRolemodel->validate() && $UserRolemodel->save())
						{
							$responsedata=array('status'=>1,'message'=>'Password has been updated successfully');
						}
					}
					
				}
			}
		}
		return $this->asJson($responsedata);
	}

	public function actionGetStandardRoyalty()
    {
		if(!Yii::$app->userrole->hasRights(array('add_royalty_fee','edit_royalty_fee','view_royalty_fee','delete_royalty_fee')))
		{
			return false;
		}

        $data = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');

		$model = CertificateRoyaltyFee::find()->where(['franchise_id'=> $data['franchise_id']]);
		$model->andWhere(['status'=>0]);
		$model = $model->all();
		$list=array();
		$totalCount = count($model);
		if($totalCount>0)
		{
			foreach($model as $modelData)
			{
				$data=array();
				$data['id']=$modelData->id;
				$data['franchise_id']=$modelData->franchise_id;
				$data['scope_holder_fee']=$modelData->scope_holder_fee;
				$data['facility_fee']=$modelData->facility_fee;
				$data['sub_contractor_fee']=$modelData->sub_contractor_fee;
				$data['non_certified_subcon_fee']=$modelData->non_certified_subcon_fee;
				$data['created_at']=date($date_format,$modelData->created_at);

				$labelgradestandard = $modelData->stdcombination;
				if(count($labelgradestandard)>0)
				{
					$standard_id_arr = array();
					$standard_id_label_arr = array();
					foreach($labelgradestandard as $val)
					{
						if($val->standard!==null)
						{
							$standard_id_arr[]="".$val['standard_id'];
							$standard_id_label_arr[]=($val->standard ? $val->standard->code : '');
						}
					}
					$data["standard_id"]=$standard_id_arr;
					$data["standard_id_label"]=implode(', ',$standard_id_label_arr);
				}
			
				$list[]=$data;
			}
		}
		return ['addRoyalty'=>$list,'total'=>$totalCount];
	}

	public function actionAddStandardRoyalty()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$editStatus=1;
			$condition = '';			
			if(isset($data['id']) && $data['id']>0)
			{
				$model = CertificateRoyaltyFee::find()->where(['id' => $data['id']])->one();
				if($model===null)
				{
					$model = new CertificateRoyaltyFee();
					$model->franchise_id = $data['franchise_id'];
					$editStatus=0;
				}else{
					$condition.= ' and comb.id!='.$data['id'].' ';
				}
			}else{
				$editStatus=0;
				$model = new CertificateRoyaltyFee();
				$model->franchise_id = $data['franchise_id'];
			}	
			
			$currentAction = 'add_royalty_fee';
			if($editStatus==1)
			{
				$currentAction = 'edit_royalty_fee';
			}
						
			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}

			$userData = Yii::$app->userdata->getData();
			$model->scope_holder_fee = $data['scope_holder_fee'];
			$model->facility_fee = $data['facility_fee'];
			$model->sub_contractor_fee = $data['sub_contractor_fee'];
			$model->non_certified_subcon_fee = $data['non_certified_subcon_fee'];
			$model->created_by = $userData['userid'];
			
			$standard_ids = $data['standard_id'];
			sort($standard_ids);
			$connection = Yii::$app->getDb();			
			$command = $connection->createCommand("select GROUP_CONCAT(combstd.standard_id order by combstd.standard_id asc ) as standardids from tbl_certificate_royalty_fee as comb inner join tbl_certificate_royalty_fee_cs as combstd on comb.id=combstd.certificate_royalty_fee_id where franchise_id='".$data['franchise_id']."' GROUP BY comb.id HAVING standardids = '".implode(',',$standard_ids)."' $condition");
			$result = $command->queryOne();
			if($result  === false)
			{				
				if($model->validate() && $model->save())
				{	
					$manualID = $model->id;
					
					if(is_array($data['standard_id']) && count($data['standard_id'])>0)
					{
						CertificateRoyaltyFeeCs::deleteAll(['certificate_royalty_fee_id' => $model->id]);
						foreach ($data['standard_id'] as $value)
						{ 
							$CertificateRoyaltyFeeCsModel =  new CertificateRoyaltyFeeCs();
							$CertificateRoyaltyFeeCsModel->certificate_royalty_fee_id = $model->id;
							$CertificateRoyaltyFeeCsModel->standard_id = $value;
							$CertificateRoyaltyFeeCsModel->save();						
						}
					}
									
					$userMessage = 'Royalty Fee has been added successfully';
					if($editStatus==1)
					{
						$userMessage = 'Royalty Fee has been updated successfully';
					}				
					$responsedata=array('status'=>1,'message'=>$userMessage);	
				}
			}else{
				$responsedata=array('status'=>0,'message'=>["standard_id"=>['This Combination has been taken already.!']]);	
			}		
		}
		return $this->asJson($responsedata);
	}

	public function actionDeleteStandardRoyalty()
	{
		if(!Yii::$app->userrole->hasRights(array('delete_royalty_fee')))
		{
			return false;
		}
		$data = Yii::$app->request->post();
		if($data)
		{			
			$id = $data['id'];
			CertificateRoyaltyFeeCs::deleteAll(['certificate_royalty_fee_id' => $data['id']]);
			$StandardCombinationModel = CertificateRoyaltyFee::find()->where(['id'=>$id])->one();
			if($StandardCombinationModel!==null)
			{
				$StandardCombinationModel->delete();
			}	
		}
		return $responsedata=array('status'=>1,'message'=>'Data deleted successfully');
	}

	public function actionGetTcRoyalty()
    {
		if(!Yii::$app->userrole->hasRights(array('add_tc_fee','edit_tc_fee','view_tc_fee','delete_tc_fee')))
		{
			return false;
		}
        $data = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');

		$model = CertificateTcRoyaltyFee::find()->where(['franchise_id'=> $data['franchise_id']]);
		$model->andWhere(['status'=>0]);
		$model = $model->all();
		$list=array();
		$totalCount = count($model);
		if($totalCount>0)
		{
			foreach($model as $modelData)
			{
				$data=array();
				$data['id']=$modelData->id;
				$data['franchise_id']=$modelData->franchise_id;
				$data['single_domestic_invoice_fee_for_oss_to_customer']=$modelData->single_domestic_invoice_fee_for_oss_to_customer;
				$data['single_export_invoice_fee_for_oss_to_customer']=$modelData->single_export_invoice_fee_for_oss_to_customer;
				$data['multiple_domestic_invoice_fee_for_oss_to_customer']=$modelData->multiple_domestic_invoice_fee_for_oss_to_customer;
				$data['multiple_export_invoice_fee_for_oss_to_customer']=$modelData->multiple_export_invoice_fee_for_oss_to_customer;
				
				$data['single_invoice_fee_for_hq_to_oss']=$modelData->single_invoice_fee_for_hq_to_oss;				
				$data['multiple_invoice_fee_for_hq_to_oss']=$modelData->multiple_invoice_fee_for_hq_to_oss;

				$data['created_at']=date($date_format,$modelData->created_at);

				$labelgradestandard = $modelData->stdcombination;
				if(count($labelgradestandard)>0)
				{
					$standard_id_arr = array();
					$standard_id_label_arr = array();
					foreach($labelgradestandard as $val)
					{
						if($val->standard!==null)
						{
							$standard_id_arr[]="".$val['standard_id'];
							$standard_id_label_arr[]=($val->standard ? $val->standard->code : '');
						}
					}
					$data["standard_id"]=$standard_id_arr;
					$data["standard_id_label"]=implode(', ',$standard_id_label_arr);
				}
			
				$list[]=$data;
			}
		}
		return ['addRoyalty'=>$list,'total'=>$totalCount];
	}

	public function actionAddTcRoyalty()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$editStatus=1;
			$condition = '';			
			if(isset($data['id']) && $data['id']>0)
			{
				$model = CertificateTcRoyaltyFee::find()->where(['id' => $data['id']])->one();
				if($model===null)
				{
					$model = new CertificateTcRoyaltyFee();
					$model->franchise_id = $data['franchise_id'];
					$editStatus=0;
				}else{
					$condition.= ' and comb.id!='.$data['id'].' ';
				}
			}else{
				$editStatus=0;
				$model = new CertificateTcRoyaltyFee();
				$model->franchise_id = $data['franchise_id'];
			}
			
			$currentAction = 'add_tc_fee';
			if($editStatus==1)
			{
				$currentAction = 'edit_royalty_fee';
			}
						
			$userData = Yii::$app->userdata->getData();
			$model->single_domestic_invoice_fee_for_oss_to_customer = $data['single_domestic_invoice_fee_for_oss_to_customer'];			
			$model->single_export_invoice_fee_for_oss_to_customer = $data['single_export_invoice_fee_for_oss_to_customer'];			
			$model->multiple_domestic_invoice_fee_for_oss_to_customer = $data['multiple_domestic_invoice_fee_for_oss_to_customer'];
			$model->multiple_export_invoice_fee_for_oss_to_customer = $data['multiple_export_invoice_fee_for_oss_to_customer'];				
			$model->single_invoice_fee_for_hq_to_oss = $data['single_invoice_fee_for_hq_to_oss'];			
			$model->multiple_invoice_fee_for_hq_to_oss = $data['multiple_invoice_fee_for_hq_to_oss'];

			$model->created_by = $userData['userid'];
			
			$standard_ids = $data['standard_id'];
			sort($standard_ids);
			$connection = Yii::$app->getDb();			
			$command = $connection->createCommand("select GROUP_CONCAT(combstd.standard_id order by combstd.standard_id asc ) as standardids from tbl_certificate_tc_royalty_fee as comb inner join tbl_certificate_tc_royalty_fee_cs as combstd on comb.id=combstd.certificate_tc_royalty_fee_id where franchise_id='".$data['franchise_id']."' GROUP BY comb.id HAVING standardids = '".implode(',',$standard_ids)."' $condition");
			$result = $command->queryOne();
			if($result  === false)
			{				
				if($model->validate() && $model->save())
				{	
					$manualID = $model->id;
					
					if(is_array($data['standard_id']) && count($data['standard_id'])>0)
					{
						CertificateTcRoyaltyFeeCs::deleteAll(['certificate_tc_royalty_fee_id' => $model->id]);
						foreach ($data['standard_id'] as $value)
						{ 
							$CertificateTcRoyaltyFeeCsModel =  new CertificateTcRoyaltyFeeCs();
							$CertificateTcRoyaltyFeeCsModel->certificate_tc_royalty_fee_id = $model->id;
							$CertificateTcRoyaltyFeeCsModel->standard_id = $value;
							$CertificateTcRoyaltyFeeCsModel->save();						
						}
					}
									
					$userMessage = 'Royalty Fee has been added successfully';
					if($editStatus==1)
					{
						$userMessage = 'Royalty Fee has been updated successfully';
					}				
					$responsedata=array('status'=>1,'message'=>$userMessage);	
				}
			}else{
				$responsedata=array('status'=>0,'message'=>["standard_id"=>['This Combination has been taken already.!']]);	
			}		
		}
		return $this->asJson($responsedata);
	}

	public function actionDeleteTcRoyalty()
	{
		if(!Yii::$app->userrole->hasRights(array('delete_tc_fee')))
		{
			return false;
		}
		
		$data = Yii::$app->request->post();
		if($data)
		{			
			$id = $data['id'];
			CertificateTcRoyaltyFeeCs::deleteAll(['certificate_tc_royalty_fee_id' => $data['id']]);
			$StandardCombinationModel = CertificateTcRoyaltyFee::find()->where(['id'=>$id])->one();
			if($StandardCombinationModel!==null)
			{
				$StandardCombinationModel->delete();
			}	
		}
		return $responsedata=array('status'=>1,'message'=>'Data deleted successfully');
	}

	
	public function actionOssUsers()
	{		
		$resultarr=array();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');

		if ($data) 
		{		
			$ossid = $data['id'];
			$connection = Yii::$app->getDb();
			$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
			$command = $connection->createCommand("SELECT user.id,user.first_name,user.last_name,role.created_by,user.created_at,GROUP_CONCAT(role.role_name) AS 'roles' FROM `tbl_users` AS `user` INNER JOIN  `tbl_user_role` AS `userrole` ON userrole.user_id = user.id INNER JOIN  `tbl_role` AS `role` ON userrole.role_id = role.id WHERE userrole.franchise_id = '$ossid' GROUP BY user.id");
			$result = $command->queryAll();
			if(count($result)>0)
			{
				foreach($result as $data)
				{
					$val=array();
					$val['id'] = $data['id'];
					$val['first_name'] = $data['first_name'];
					$val['last_name'] = $data['last_name'];
					$val['roles'] = $data['roles'];
					$val['created_at'] = date($date_format,$data['created_at']);
					
					if($data['created_by']!='')
					{
						$usermodel = User::find()->where(['id'=>$data['created_by']])->one();
						$val['created_by_label'] = $usermodel->first_name;
					}
					
					
					$resultarr[] = $val;
				}
			}
		}
		return ['data'=>$resultarr];
	}	
		
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data && isset($data['status'])) 
		{
			$status = $data['status'];			
			if(!Yii::$app->userrole->canDoCommonUpdate($status,'franchise'))
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
						$msg='Brand has been activated successfully';
					}elseif($model->status==1){
						$msg='Brand has been deactivated successfully';
					}elseif($model->status==2){
						$msg='Brand has been deleted successfully';

						 $brnmod = Brand::find()->where(['user_id'=>$data['id']])->one();
						 if($brnmod!==null && $brnmod!=''){
							 $brnmod =$brnmod->delete();
						 }
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
	public function actionDownloadFile()
	{

				$filename ='Data Subject Consent Form-revised.docx';				
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
				header('Access-Control-Max-Age: 1000');
				header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
				
				$filepath=Yii::$app->params['user_files'].$filename;
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
				die;	
	}
}