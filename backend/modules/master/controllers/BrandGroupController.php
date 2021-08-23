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

class BrandGroupController extends \yii\rest\Controller
{

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
       if(!Yii::$app->userrole->hasRights(array('view_brand')))
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



       $model->join('inner join','tbl_brand_group as brand','t.id=brand.user_id');
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
               $data['brand_group_name']=$user->userbrandgroup?$user->userbrandgroup->name:'';
               $data['login_status']=true;
               $data['created_at']=date($date_format,$user->created_at);
               
               
               $user_list[]=$data;
           }
       }
       
       return ['franchises'=>$user_list,'total'=>$totalCount];
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
			$maxid = BrandGroup::find()->max('id');
			$countrycode=$ccode->code;
			if(!empty($maxid)) 
			{
				$maxid = $maxid+1;
				$userregid="GCL-BRGR-".$maxid;
			}
			else
			{
				$userregid="GCL-BRGR-1";
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
			$modelUserRole->role_id=41;
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
				
                $brandgroupmode = new BrandGroup();
				$brandgroupmode->user_id = $model->id;
                $brandgroupmode->name = $data['company_name'];
				

                if($brandgroupmode->validate()){
                    $brandgroupmode->save();
                }

				$companygrid = $this->renderPartial('@app/mail/layouts/EnquiryCompanyGridTemplate',[
					'model' => $UserCompanyInfo
				]);

				$verifyLink = Yii::$app->params['site_path'].'change-username-password?token='.$modelUserRole->verification_token;
				$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'sign_up'])->one();

				$mailmsg=str_replace('{USERNAME}', $UserCompanyInfo->contact_name, $mailContent['message'] );
				$mailmsg=str_replace('{COMPANY-DETAILS-GRID}', $companygrid, $mailmsg );
				$mailmsg=str_replace('{VERIFYLINK}', $verifyLink, $mailmsg);
				
				$MailLookupModel = new MailLookup();
				$MailLookupModel->to=$UserCompanyInfo->company_email;				
				$MailLookupModel->subject=$mailContent['subject'];
				$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
				$MailLookupModel->attachment='';
				$MailLookupModel->mail_notification_id='';
				$MailLookupModel->mail_notification_code='';
				$Mailres=$MailLookupModel->sendMail();
				$responsedata=array('status'=>1,'message'=>'Brand Group has been created successfully');	
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$model->errors);
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
	
				
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$model->errors);
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
						$msg='Brand Group has been activated successfully';
					}elseif($model->status==1){
						$msg='Brand Group has been deactivated successfully';
					}elseif($model->status==2){
						$msg='Brand Group has been deleted successfully';

						 $brnmod = BrandGroup::find()->where(['user_id'=>$data['id']])->one();
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
					
					$brandgromodel = BrandGroup::find()->where(['user_id'=>$data['id']])->one();
					if($brandgromodel!==null && $brandgromodel!==''){
						$brandgromodel->name=$data['company_name'];
						$brandgromodel->save();
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


}
