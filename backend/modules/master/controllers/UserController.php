<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\application\models\ApplicationApprover;
use app\modules\application\models\ApplicationReviewer;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationBrands;
use app\modules\master\models\User;
use app\modules\master\models\Standard;
use app\modules\master\models\Brand;
use app\modules\master\models\Role;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\UserCompanyInfo;
use app\modules\master\models\MailLookup;
use app\modules\master\models\UserQualification;
use app\modules\master\models\UserTrainingInfo;
use app\modules\master\models\UserExperience;
use app\modules\master\models\UserCertification;
use app\modules\master\models\UserStandard;
use app\modules\master\models\UserStandardHistory;
use app\modules\master\models\UserDeclaration;
use app\modules\master\models\UserDeclarationRelation;
use app\modules\master\models\UserDeclarationHistory;
use app\modules\master\models\UserRole;
use app\modules\master\models\UserProcess;
use app\modules\master\models\UserQualificationReview;
use app\modules\master\models\UserQualificationReviewHistory;
use app\modules\master\models\UserQualificationReviewComment;
use app\modules\master\models\UserBusinessSector;
use app\modules\master\models\UserBusinessSectorGroup;
use app\modules\master\models\BusinessSectorGroup;
use app\modules\master\models\UserAuditExperience;
use app\modules\master\models\UserAuditExperienceProcess;
use app\modules\master\models\UserBusinessGroup;
use app\modules\master\models\UserBusinessGroupCode;
use app\modules\master\models\UserBusinessGroupCodeHistory;
use app\modules\master\models\UserConsultancyExperience;
use app\modules\master\models\UserConsultancyExperienceProcess;
use app\modules\master\models\BusinessSector;
use app\modules\master\models\UserRoleTechnicalExpertBs;
use app\modules\master\models\UserRoleTechnicalExpertBsCode;
use app\modules\master\models\UserRoleTechnicalExpertBsCodeHistory;
use app\modules\master\models\UserRoleBusinessGroup;
use app\modules\master\models\UserRoleBusinessGroupCode;
use app\modules\master\models\Rule;
use app\modules\master\models\Translator;
use app\modules\master\models\AuditStandard;

use app\modules\library\models\LibraryDownload;
use app\modules\library\models\LibraryFaq;
use app\models\Enquiry;
use app\models\ChangePassword;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;


class UserController extends \yii\rest\Controller
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
			'authenticator' => [
				'class' => JwtHttpBearerAuth::class,
				'optional' => [
					'change-username-password'                    
				]
			]
		];	
	}
	
	public function usercompanyinfoRelation($model)
	{
		$model = $model->joinWith('usercompanyinfo as usercompanyinfo');	
	}
	
	public function countryRelation($model)
	{
		$model = $model->join('left join', 'tbl_country as country','country.id=tbl_users.country_id');
	}
	
	public function userRoleRelation($model)
	{
		$model = $model->join('left join', 'tbl_user_role as userrole','userrole.user_id=tbl_users.id');
	}
	
	public function actionIndex()
    {
		$userData = Yii::$app->userdata->getData();
		$user_type=$userData['user_type'];
		if(!Yii::$app->userrole->hasRights(array('user_master')) && $user_type!=3)
		{
			return false;
		}
		
        $post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');					
		
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		
		$model = User::find();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];	
		
		$usercompanyinfoJoinWithStatus=false;
		$countryJoinWithStatus=false;
		$userRoleJoinWithStatus=false;
		
		$model = $model->andWhere(['<>','tbl_users.status',2]);
		$model = $model->andWhere(['tbl_users.user_type'=> $post['type']]);
		
		if(isset($post['countryFilter']) && is_array($post['countryFilter']) && count($post['countryFilter'])>0)
		{
			if($post['type']==1)
			{
				$model = $model->andWhere(['tbl_users.country_id'=> $post['countryFilter']]);	
			}elseif($post['type']==2){
				$usercompanyinfoJoinWithStatus=true;
				$this->usercompanyinfoRelation($model);	
				$model = $model->andWhere(['usercompanyinfo.company_country_id'=> $post['countryFilter']]);	
			}
		}
		
		if(isset($post['franchiseFilter']) && is_array($post['franchiseFilter']) && count($post['franchiseFilter'])>0 && $post['type']==1)
		{
			$userRoleJoinWithStatus=true;
			$this->userRoleRelation($model);
			
			$model = $model->andWhere(['userrole.franchise_id'=> $post['franchiseFilter']]);			
		}	

		if(isset($post['roleFilter']) && is_array($post['roleFilter']) && count($post['roleFilter'])>0 && $post['type']==1)
		{
			if(!$userRoleJoinWithStatus)
			{
				$userRoleJoinWithStatus=true;
				$this->userRoleRelation($model);
			}
			
			$model = $model->andWhere(['userrole.role_id'=> $post['roleFilter']])->andWhere(['userrole.approval_status'=> 2]);			
		}	

		if(isset($post['statusFilter']) && ($post['type']==1 || $post['type']==2) && $post['statusFilter'] !='')
		{
			$model = $model->andWhere(['tbl_users.status'=> $post['statusFilter']]);			
		}	
		
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		$userData = Yii::$app->userdata->getData();
		if($resource_access != 1)
		{
			if(!$userRoleJoinWithStatus)
			{
				$userRoleJoinWithStatus=true;
				$this->userRoleRelation($model);
			}
			
			if($user_type== Yii::$app->params['user_type']['user'] && ! in_array('user_master',$rules) && ! in_array('customer_master',$rules) ){
				return $responsedata;
			}else if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
				if($resource_access == '5'){
					$chkuserid = $franchiseid;
					$model = $model->andWhere('(userrole.franchise_id="'.$chkuserid.'" or tbl_users.franchise_id="'.$chkuserid.'") ');
					//$model = $model->where('tbl_users.franchise_id="'.$chkuserid.'"');
				}else{
					$model = $model->andWhere('(userrole.franchise_id="'.$userid.'" or tbl_users.franchise_id="'.$userid.'")');
					//$model = $model->where('tbl_users.franchise_id="'.$userid.'"');
				}
				
				//$model = $model->where('tbl_users.created_by="'.$userid.'"');
			}else if($user_type== Yii::$app->params['user_type']['customer']){
				return $responsedata;
			}
			
		}
		//echo $is_headquarters; die;
		if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1 && $post['type']==1 ){
			//$model = $model->andWhere('tbl_users.franchise_id="'.$franchiseid.'" or tbl_users.created_by="'.$userid.'" ');
			if(!$userRoleJoinWithStatus)
			{
				$userRoleJoinWithStatus=true;
				$this->userRoleRelation($model);
			}
			
			$model = $model->andWhere(' (userrole.franchise_id="'.$franchiseid.'" or tbl_users.created_by="'.$userid.'" ) ');
		}


		$model = $model->groupBy(['tbl_users.id']);
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm'] !='')
			{
				$searchTerm = $post['searchTerm'];
				if($post['type']==1)
				{
					$countryJoinWithStatus=true;
					$this->countryRelation($model);						
					
					$model = $model->andFilterWhere([
						'or',
						['like', 'tbl_users.registration_id', $searchTerm],
						['like', 'tbl_users.first_name', $searchTerm],
						['like', 'tbl_users.last_name', $searchTerm],
						['like', 'tbl_users.email', $searchTerm],
						['like', 'tbl_users.telephone', $searchTerm],
						['like', 'country.name', $searchTerm],
						['like', '(date_format(FROM_UNIXTIME(tbl_users.created_at), \'%b %d, %Y\' ))', $searchTerm],									
					]);
				}elseif($post['type']==2){
					if(!$usercompanyinfoJoinWithStatus)
					{
						$usercompanyinfoJoinWithStatus=true;
						$this->usercompanyinfoRelation($model);	
					}
				
					$model = $model->join('left join', 'tbl_country as companycountry','usercompanyinfo.company_country_id=companycountry.id');
					$model = $model->andFilterWhere([
						'or',
						['like', 'tbl_users.registration_id', $searchTerm],
						['like', 'usercompanyinfo.company_name', $searchTerm],										
						['like', 'usercompanyinfo.contact_name', $searchTerm],										
						['like', 'usercompanyinfo.company_email', $searchTerm],										
						['like', 'usercompanyinfo.company_telephone', $searchTerm],	
						['like', 'companycountry.name', $searchTerm],
						['like', '(date_format(FROM_UNIXTIME(tbl_users.created_at), \'%b %d, %Y\' ))', $searchTerm],
					]);
				}					
			}
			
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				$sortColumn = $post['sortColumn'];
				if($sortColumn=='country')
				{
					if($post['type']==1)
					{
						if(!$countryJoinWithStatus)
						{
							$countryJoinWithStatus=true;
							$this->countryRelation($model);	
						}
						
						$sortColumn='country.name';
					}elseif($post['type']==2){
						if(!$usercompanyinfoJoinWithStatus)
						{
							$usercompanyinfoJoinWithStatus=true;
							$this->usercompanyinfoRelation($model);	
						}
						
						$sortColumn='usercompanyinfo.name';
					}
				}elseif($sortColumn=='user.customer_number'){
					$sortColumn='tbl_users.customer_number';
				}
				
				$model = $model->orderBy([$sortColumn=>$sortDirection]);
				//$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
			}
			else
			{
				if($post['type']==1){
					$model = $model->orderBy(['registration_id'=>SORT_ASC]);
				}elseif($post['type']==2){
					$model = $model->orderBy(['customer_number'=>SORT_DESC]);	
				}else{
					$model = $model->orderBy(['created_at' => SORT_DESC]);
				}
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
				$data['first_name']=$user->first_name;
				$data['last_name']=$user->last_name;
				$data['email']=$user->email;
				$data['telephone']=$user->telephone;
				$data['country']=($user->country ? $user->country->name : 'NA');
				
				$data['customer_number']=$user->customer_number ? $user->customer_number : 'NA';
				
				if($user->user_type==2 || $user->user_type==3)
				{
				   //$data['company_country']=$user->usercompanyinfo->companycountry?$user->usercompanyinfo->companycountry->name:'';
				   //$data['company_name']=$user->usercompanyinfo?$user->usercompanyinfo->company_name:'';
				   
				    $data['company_country']=$user->usercompanyinfo?$user->usercompanyinfo->companycountry->name:'';
					$data['company_name']=$user->usercompanyinfo?$user->usercompanyinfo->company_name:'';
					$data['contact_name']=$user->usercompanyinfo?$user->usercompanyinfo->contact_name:'';
					$data['company_telephone']=$user->usercompanyinfo?$user->usercompanyinfo->company_telephone:'';
					$data['company_email']=$user->usercompanyinfo?$user->usercompanyinfo->company_email:'';
					$data['display_company_name']=$user->usercompanyinfo?$user->usercompanyinfo->company_name.' ('.$user->usercompanyinfo->companycountry->name.')':'';
				}else{
					$arrFranchise=array();					
					$userRikeObj = $user->usersrole;
					if(count($userRikeObj)>0)
					{
						foreach($userRikeObj as $uRole)
						{
							$arrFranchise[]=($uRole->franchise && $uRole->franchise->usercompanyinfo ? 'OSS '.$uRole->franchise->usercompanyinfo->osp_number.' - '.$uRole->franchise->usercompanyinfo->osp_details:'Yet to be assigned');
						}
						$data['franchise']=implode(', ',array_unique($arrFranchise));
					}
					//$data['created_by']=$user->franchise? 'OSS '.$user->franchise->usercompanyinfo->osp_number:'';					
					
				}			
				$edituser = 0;
				$canactivateuser = 0;
				$candeactivateuser = 0;
				//userdetails.resource_access==1 || userType == 3 || userdetails.rules.includes('add_user_personnel_details') 
				//|| userdetails.rules.includes('edit_user_roles') || userdetails.rules.includes('add_edit_user_standards_business_sectors') ||
				// userdetails.rules.includes('add_edit_user_qualification_details') || userdetails.rules.includes('add_edit_user_working_experience') || 
				//userdetails.rules.includes('add_edit_user_certificate_details') || userdetails.rules.includes('add_edit_user_cpd')
				if($resource_access == 1){
					$edituser = 1;
					$canactivateuser =1;
					$candeactivateuser =1;
				}else if($user_type== Yii::$app->params['user_type']['user']){
					$accessArr = ['add_user_personnel_details', 'edit_user_roles', 'add_edit_user_standards_business_sectors' ,'add_edit_user_qualification_details'
						, 'add_edit_user_working_experience', 'add_edit_user_certificate_details', 'add_edit_user_cpd','add_edit_declaration'];
					$result=array_intersect($accessArr,$rules);
					if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1){
						if($user->franchise_id == $franchiseid && count($result)>0){
							$edituser = 1;
							if(in_array('activate_user',$rules)){
								$canactivateuser =1;
							}
							if(in_array('deactivate_user',$rules)){
								$candeactivateuser =1;
							}
						}
					}else{
						if(count($result)>0){
							$edituser = 1;
						}
						if(in_array('activate_user',$rules)){
							$canactivateuser =1;
						}
						if(in_array('deactivate_user',$rules)){
							$candeactivateuser =1;
						}
					}

					
					
					//in_array($downloadname,$rules )
					//in_array($downloadname,$rules )
				}else if($user_type== Yii::$app->params['user_type']['franchise']){
					if($resource_access == '5'){
						$chkuserid = $franchiseid;
						if($user->franchise_id == $chkuserid){
							$edituser = 1;
							$canactivateuser =1;
							$candeactivateuser =1;
						}
						//$model = $model->where('userrole.franchise_id="'.$chkuserid.'"');
						//$model = $model->where('tbl_users.franchise_id="'.$chkuserid.'"');
					}else{
						if($user->franchise_id == $userid){
							$edituser = 1;
							$canactivateuser =1;
							$candeactivateuser =1;
						}
						//$model = $model->where('userrole.franchise_id="'.$userid.'"');
						//$model = $model->where('tbl_users.franchise_id="'.$userid.'"');
					}
				}
				$data['edituser']=$edituser;
				$data['canactivateuser']=$canactivateuser;
				$data['candeactivateuser']=$candeactivateuser;
				//$data['created_at']=date('M d,Y h:i A',$user->created_at);
				$data['status']=$user->status;
				$data['created_at']=date($date_format,$user->created_at);
				$user_list[]=$data;
			}
		}
		
		return ['users'=>$user_list,'total'=>$totalCount];
    }
	
	public function actionGetUsers()
	{
		$data = Yii::$app->request->post();

		if ($data['type'] ==100){

			$userArr = [];

			$connection = Yii::$app->getDb();

			$query = 'SELECT id,standard_name,code,version FROM `tbl_audit_standard` ';
			$command = $connection->createCommand($query);
			$reviewers = $command->queryAll();

			if(count($reviewers)>0){
				foreach($reviewers as $reviewer){
					$userArr[] = ['id'=>$reviewer['id'],'standard_name'=>$reviewer['standard_name'],'version'=>$reviewer['version'],'code'=>$reviewer['code']];
				}
			}
			$responsedata=array('status'=>1,'data'=>$userArr);

			return $responsedata;
		}
		else if($data['type']==500){
			$brandArr =[];

			$brandmod = Brand:: find()->all();

			if(count($brandmod)>0){
				foreach($brandmod as $bdata){
					$brandArr[] = ['id'=>$bdata['id'],'brand_name'=>$bdata['name'],'number'=>$bdata['number'],'version'=>$bdata['version']];
				}
			}
			$responsedata = array('status'=>1,'data'=>$brandArr);

			return $responsedata;
		}else if($data['type']=='consent'){
			$brandArr =[];

			$brandmod = ApplicationBrands:: find()->where(['app_id'=>$data['id']])->all(); 

			if(count($brandmod)>0){
				foreach($brandmod as $bdata){
					$brandArr[] = ['id'=>$bdata->brands->id,'brand_name'=>$bdata->brands->name,'number'=>$bdata->brands->number,'version'=>$bdata->brands->version];
				}
			}
			$responsedata = array('status'=>1,'data'=>$brandArr);

			return $responsedata;
		}else{
			$Usermodel = new User();

			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$is_headquarters =$userData['is_headquarters'];
			$franchiseid=$userData['franchiseid'];


			$post = yii::$app->request->post();
			$model = User::find();
			if($post['type'] ==2){
				$model = $model->join('inner join', 'tbl_user_role as userrole',' userrole.user_id=tbl_users.id AND userrole.status=0 AND userrole.login_status=1 ');
			}

			$model = $model->joinWith('usercompanyinfo as usercompanyinfo');
			$model = $model->join('left join', 'tbl_country as companycountry','usercompanyinfo.company_country_id=companycountry.id');
			$model = $model->andWhere(['tbl_users.user_type'=> $post['type'],'tbl_users.status'=> 0]);

			if(isset($post['filteruser']) && $post['filteruser'] == '1'){
				if($user_type==3 && $resource_access==5){
					$model = $model->andWhere(['tbl_users.id'=>$franchiseid]);
					//$model->franchise_id = $franchiseid;
				}else if($user_type==1 && $is_headquarters!=1){
					$model = $model->andWhere(['tbl_users.id'=>$franchiseid]);
					//$model->franchise_id = $franchiseid;
				}else if($user_type==3){
					$model = $model->andWhere(['tbl_users.id'=>$userid]);
					//$model->franchise_id = $userid;
				}
			}

			if(isset($post['filterdata']) && $post['filterdata'] == '1'){
				if($is_headquarters != 1 && $resource_access!=1 ){

					if($user_type==3){
						if($resource_access==5){
							$model = $model->andWhere(['tbl_users.franchise_id'=>$franchiseid]);
							//$model->franchise_id = $franchiseid;
						}else{
							$model = $model->andWhere(['tbl_users.franchise_id'=>$userid]);
						}

						//$model->franchise_id = $userid;
					}else if($user_type==1){
						$model = $model->andWhere(['tbl_users.franchise_id'=>$franchiseid]);
						//$model->franchise_id = $franchiseid;
					}
				}
			}
			if($post['type']==3)
			{
				$model = $model->orderBy(['cast(usercompanyinfo.osp_number AS UNSIGNED)' => SORT_ASC]);
			}
			//echo $model->createCommand()->getRawSql();exit;
			$user_list=array();
			$model = $model->all();
			if(count($model)>0)
			{
				foreach($model as $user)
				{
					$data=array();
					$data['id']=$user->id;
					$data['first_name']=$user->first_name;
					$data['last_name']=$user->last_name;
					$data['email']=$user->email;
					$data['telephone']=$user->telephone;

					if($user->user_type==2 || $user->user_type==3)
					{
						$data['company_country']=$user->usercompanyinfo?$user->usercompanyinfo->companycountry->name:'';
						$data['company_name']=$user->usercompanyinfo?$user->usercompanyinfo->company_name:'';
						$data['contact_name']=$user->usercompanyinfo?$user->usercompanyinfo->contact_name:'';
						$data['company_telephone']=$user->usercompanyinfo?$user->usercompanyinfo->company_telephone:'';
						$data['company_email']=$user->usercompanyinfo?$user->usercompanyinfo->company_email:'';
						$data['display_company_name']=$user->usercompanyinfo?$user->usercompanyinfo->company_name.' ('.$user->usercompanyinfo->companycountry->name.')':'';
						$data['osp_details']=$user->usercompanyinfo? 'OSS '.$user->usercompanyinfo->osp_number.' - '.$user->usercompanyinfo->osp_details:'';
					}
					$user_list[]=$data;
				}
			}
			return ['users'=>$user_list,'statusList'=>$Usermodel->arrUserStatus];
		}
	}

	public function actionGetCustomers()
	{		
		
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];


		$post = yii::$app->request->post();		
		$model = User::find();
		$model = $model->joinWith('usercompanyinfo as usercompanyinfo');
		$model = $model->join('left join', 'tbl_country as companycountry','usercompanyinfo.company_country_id=companycountry.id');
		$model = $model->join('inner join', 'tbl_application as app','app.customer_id=tbl_users.id');
		$model = $model->andWhere(['tbl_users.user_type'=> 2,'tbl_users.status'=> 0]);
		
		
		//echo $model->createCommand()->getRawSql();exit;
		$user_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $user)
			{
				$data=array();
				$data['id']=$user->id;
				$data['first_name']=$user->first_name;
				$data['last_name']=$user->last_name;
				$data['email']=$user->email;
				$data['telephone']=$user->telephone;
				
				$data['company_country']=$user->usercompanyinfo?$user->usercompanyinfo->companycountry->name:'';
				$data['company_name']=$user->usercompanyinfo?$user->usercompanyinfo->company_name:'';
				$data['contact_name']=$user->usercompanyinfo?$user->usercompanyinfo->contact_name:'';
				$data['company_telephone']=$user->usercompanyinfo?$user->usercompanyinfo->company_telephone:'';
				$data['company_email']=$user->usercompanyinfo?$user->usercompanyinfo->company_email:'';
				$data['display_company_name']=$user->usercompanyinfo?$user->usercompanyinfo->company_name.' ('.$user->usercompanyinfo->companycountry->name.')':'';
				$data['osp_details']=$user->usercompanyinfo? 'OSS '.$user->usercompanyinfo->osp_number.' - '.$user->usercompanyinfo->osp_details:'';
								
				$user_list[]=$data;
			}
		}		
		return ['customers'=>$user_list];
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

	public function actionCreateUser()
	{
		$userData = Yii::$app->userdata->getData();
		$user_type=$userData['user_type'];
		if(!Yii::$app->userrole->hasRights(array('add_user')) && $user_type!=3)
		{
			return false;
		}
		
		$model = new User();
		$UserCompanyInfo=new UserCompanyInfo();

		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$datapost = Yii::$app->request->post();
		$data = json_decode($datapost['formvalues'],true);
		$target_dir = Yii::$app->params['user_files'];
	
		$userid=$userData['userid'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];
		if ($data) 
		{			
			if($data['actiontype'] =='personnel'){
				$model->first_name=$data['first_name'];
				$model->last_name=$data['last_name'];
				$model->email=$data['email'];
				$model->telephone=$data['telephone'];
				
				
				if($user_type==1 || ($user_type==3 && $resource_access==5)){
					$model->franchise_id = $franchiseid;
				}else{
					$model->franchise_id = $userid;
				}


				$model->country_id=$data['country_id'];
				$model->state_id=$data['state_id'];
				//$model->user_type=isset($data['user_type'])?$data['user_type']:'2';
				$model->user_type='1';

				$maxid = User::find()->where(['user_type'=>1])->max('cast(`registration_id` AS UNSIGNED)');
				$countrycode=$model->country->code;
				if(!empty($maxid)) 
				{
					$maxid = $maxid+1;
					//$userregid="GCL-USR-".$countrycode."-".$maxid;
					$userregid=$maxid;
				}
				else
				{
					//$userregid="GCL-USR-".$countrycode."-1";
					$userregid=1000;
				}
				$model->registration_id=$userregid;
				//$model->generateEmailVerificationToken();
				$model->status=isset($data['status'])?$data['status']:0;
				$model->send_mail_notification_status=isset($data['send_mail_notification_status'])?$data['send_mail_notification_status']:0;


				if(isset($_FILES['passport_file']['name']))
				{
					$tmp_name = $_FILES["passport_file"]["tmp_name"];
		   			$name = $_FILES["passport_file"]["name"];
					$model->passport_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);					
				}
				if(isset($_FILES['contract_file']['name']))
				{
					$tmp_name = $_FILES["contract_file"]["tmp_name"];
		   			$name = $_FILES["contract_file"]["name"];
					$model->contract_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);						
				}
				$userData = Yii::$app->userdata->getData();
				$model->created_by=$userData['userid'];								
				if($model->validate() && $model->save())
				{
					$responsedata=array('status'=>1,'message'=>'User has been created successfully','user_id'=>$model->id);
				}else
				{
					$responsedata=array('status'=>0,'message'=>'failed');
				}
			}else{

			}			
		}
		return $this->asJson($responsedata);
	}

	public function actionUpdateUser()
	{
		$model = new User();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$datapost = Yii::$app->request->post();
		$data = json_decode($datapost['formvalues'],true);
		$target_dir = Yii::$app->params['user_files']; 
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$connection = Yii::$app->getDb();

		$userData = Yii::$app->userdata->getData();
			
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];		
		if ($data) 
		{	
			if(!$this->canDoUserAccess($data['id']) && !($user_type==1 && $userid==$data['id']))
			{
				return false;
			}
		
			$model = User::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$update_user_id = $data['id'];
				$userData = Yii::$app->userdata->getData();
				if($data['actiontype'] =='personnel'){
					if(!$this->canDoUpdateAccess($update_user_id,['add_user_personnel_details']) 
						&& !($user_type==1 && $userid==$update_user_id)){
						return false;
					}
					$model->first_name=$data['first_name'];
					$model->last_name=$data['last_name'];
					$model->email=$data['email'];
					$model->telephone=$data['telephone'];
					$model->country_id=$data['country_id'];
					$model->state_id=$data['state_id'];
					
					$userData = Yii::$app->userdata->getData();
					$model->updated_by=$userData['userid'];
	
					if(isset($_FILES['passport_file']['name']))
					{
						//$filename = $_FILES['passport_file']['name'];
						//$actual_name = pathinfo($filename,PATHINFO_FILENAME);

						$tmp_name = $_FILES["passport_file"]["tmp_name"];
			   			$name = $_FILES["passport_file"]["name"];
						$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
						if($filename !='')
						{
							Yii::$app->globalfuns->removeFiles($model->passport_file,$target_dir);
							$model->passport_file=isset($filename)?$filename:"";
						}
						/*
						$actual_name = Yii::$app->globalfuns->fnRemoveSpecialCharacters($actual_name);
						$target_file = $target_dir . basename($filename);
						
						$original_name = $actual_name;
						$extension = pathinfo($filename, PATHINFO_EXTENSION);
						$i = 1;
						$name = $actual_name.".".$extension;
						while(file_exists($target_dir.$actual_name.".".$extension))
						{           
							$actual_name = (string)$original_name.$i;
							$name = $actual_name.".".$extension;
							$i++;
						}
						if (move_uploaded_file($_FILES['passport_file']["tmp_name"], $target_dir .$actual_name.".".$extension)) {
							$model->passport_file=isset($name)?$name:"";
						}
						*/
					}else{
						if(isset($data['passport_file'])){
							$model->passport_file=$data['passport_file'];
						}
					}
					if(isset($_FILES['contract_file']['name']))
					{
						
						$tmp_name = $_FILES["contract_file"]["tmp_name"];
			   			$name = $_FILES["contract_file"]["name"];
						$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
						if($filename !='')
						{
							Yii::$app->globalfuns->removeFiles($model->contract_file,$target_dir);
							$model->contract_file=isset($filename)?$filename:"";					
						}


						/*	
						$filename = $_FILES['contract_file']['name'];
						$actual_name = pathinfo($filename,PATHINFO_FILENAME);
						$actual_name = Yii::$app->globalfuns->fnRemoveSpecialCharacters($actual_name);
						$target_file = $target_dir . basename($filename);
						
						$original_name = $actual_name;
						$extension = pathinfo($filename, PATHINFO_EXTENSION);
						$i = 1;
						$name = $actual_name.".".$extension;
						while(file_exists($target_dir.$actual_name.".".$extension))
						{           
							$actual_name = (string)$original_name.$i;
							$name = $actual_name.".".$extension;
							$i++;
						}
						if (move_uploaded_file($_FILES['contract_file']["tmp_name"], $target_dir .$actual_name.".".$extension)) {
							$model->contract_file=isset($name)?$name:"";
						}
						*/
					}else{
						if(isset($data['contract_file'])){							
							$model->contract_file=$data['contract_file'];
						}
					}
					
					//$model->created_by=$userData['userid'];
					
					if($model->validate() && $model->save())
					{
						$responsedata=array('status'=>1,'message'=>'User updated successfully','user_id'=>$model->id,'contract_file'=>$model->contract_file,'passport_file'=>$model->passport_file);
					}else
					{
						$responsedata=array('status'=>0,'message'=>'failed');
					}
				}else if($data['actiontype'] =='qualification'){
					if(!$this->canDoUpdateAccess($update_user_id,['add_edit_user_qualification_details']) && !($user_type==1 && $userid==$update_user_id)){
						return false;
					}
					//UserQualification::deleteAll(['user_id' => $data['id']]);
					if(is_array($data['qualifications']) && count($data['qualifications'])>0)
					{
						foreach ($data['qualifications'] as $key => $value)
						{ 

							//print_r($_FILES['academicfiles']['name'][$key]);

							$target_dir = Yii::$app->params['user_files']; 
							$filename = '';
							

							$editStatus = 0;
							if(isset($value['id']) && $value['id']>0 ){
								$qualmodel=UserQualification::find()->where(['id'=>$value['id']])->one();
								$editStatus = 1;
								if($qualmodel === null){
									$qualmodel=new UserQualification();
									$editStatus = 0;
								}
							}else{
								$editStatus = 0;
								$qualmodel=new UserQualification();
							}
							
							if(isset($_FILES['academicfiles']['name']))
							{
								if($editStatus){
									Yii::$app->globalfuns->removeFiles($qualmodel->certificate,$target_dir);
								}

								$tmp_name = $_FILES["academicfiles"]["tmp_name"];
					   			$name = $_FILES["academicfiles"]["name"];
								$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
							}else{
								$filename = $value['academic_certificate'];
							}

							$qualmodel->user_id=$model->id;
							$qualmodel->qualification=$value['qualification'];
							$qualmodel->board_university=$value['board_university'];
							$qualmodel->subject=$value['subject'];
							$qualmodel->start_year=$value['start_year'];
							$qualmodel->end_year=$value['end_year'];
							$qualmodel->certificate=$filename;
							//$qualmodel->percentage=$value['percentage'];
							$qualmodel->save();
						}
						
					}					
					$responsedata=array('status'=>1,'message'=>'Academic updated successfully','user_id'=>$model->id);
				}else if($data['actiontype'] =='experience'){
					//UserExperience::deleteAll(['user_id' => $data['id']]);
					if(!$this->canDoUpdateAccess($update_user_id,['add_edit_user_working_experience']) && !($user_type==1 && $userid==$update_user_id)){
						return false;
					}
					if(is_array($data['experience']) && count($data['experience'])>0)
					{
						foreach ($data['experience'] as $value)
						{ 
							//$expmodel=new UserExperience();
							if(isset($value['id']) && $value['id'] >0 ){
								$expmodel=UserExperience::find()->where(['id'=>$value['id']])->one();
								if($expmodel === null ){
									$expmodel=new UserExperience();
								}
							}else{
								$expmodel=new UserExperience();
							}
							$expmodel->user_id=$model->id;
							$expmodel->experience=$value['experience'];
							$expmodel->job_title=$value['job_title'];
							$expmodel->responsibility=$value['responsibility'];
							$expmodel->from_date= date('Y-m-d', strtotime($value['from_date']));
							$expmodel->to_date=date('Y-m-d',strtotime($value['to_date']));
							$expmodel->save();
						}
					}
					$responsedata=array('status'=>1,'message'=>'Experience updated successfully','user_id'=>$model->id);
				}else if($data['actiontype'] =='audit_experience'){
					/*
					$expmodel = UserAuditExperience::find()->where(['user_id' => $data['id']])->all();
					if($expmodel!== null)
					{
						foreach($expmodel as $values)
						{
							UserAuditExperienceProcess::deleteAll(['user_audit_experience_id' => $values->id]);
						}
					}

					UserAuditExperience::deleteAll(['user_id' => $data['id']]);
					*/
					// if(!$this->canDoUpdateAccess($update_user_id,['add_edit_audit_experience']) 
					// 	&& !($user_type==1 && $userid==$update_user_id)){
					// 	return false;
					// }

					if(is_array($data['audit_experience']) && count($data['audit_experience'])>0)
					{
						foreach ($data['audit_experience'] as $value)
						{ 
							// $sector = implode(",",$value['business_sector']);

							if(isset($value['id']) && $value['id'] >0 ){
								$audexpmodel=UserAuditExperience::find()->where(['id'=>$value['id']])->one();
								if($audexpmodel === null ){
									$audexpmodel=new UserAuditExperience();
								}
							}else{
								$audexpmodel=new UserAuditExperience();
							}
							
							$audexpmodel->user_id=$model->id;
							$audexpmodel->standard_id=$value['standard'];
							$audexpmodel->year=$value['year'];
							$audexpmodel->company=$value['company'];
							$audexpmodel->cb= $value['cb']; 
							$audexpmodel->audit_role=$value['audit_role'];
							$audexpmodel->days=$value['days'];
							$audexpmodel->sector=$value['businesssector'];
							$audexpmodel->validate();
							$audexpmodel->save();
						}						
					}
					$responsedata=array('status'=>1,'message'=>'Inspection / Audit Experience updated successfully','user_id'=>$model->id);
				}else if($data['actiontype'] =='consultancy_experience'){
					if(!$this->canDoUpdateAccess($update_user_id,['add_edit_consultancy_experience']) && !($user_type==1 && $userid==$update_user_id)){
						return false;
					}
					/*
					$conexpmodel = UserConsultancyExperience::find()->where(['user_id' => $data['id']])->all();
					if($conexpmodel!== null)
					{
						foreach($conexpmodel as $val)
						{
							UserConsultancyExperienceProcess::deleteAll(['user_consultancy_experience_id' => $val->id]);
						}
					}
					*/

					//UserConsultancyExperience::deleteAll(['user_id' => $data['id']]);
					if(is_array($data['consultancy_experience']) && count($data['consultancy_experience'])>0)
					{
						foreach ($data['consultancy_experience'] as $value)
						{ 
							if(isset($value['id']) && $value['id'] >0 ){
								$consultancyexpmodel=UserConsultancyExperience::find()->where(['id'=>$value['id']])->one();
								if($consultancyexpmodel === null ){
									$consultancyexpmodel=new UserConsultancyExperience();
								}
							}else{
								$consultancyexpmodel=new UserConsultancyExperience();
							}
							//$consultancyexpmodel=new UserConsultancyExperience();
							$consultancyexpmodel->user_id=$model->id;
							$consultancyexpmodel->standard_id=$value['standard'];
							$consultancyexpmodel->year=$value['year'];
							$consultancyexpmodel->company=$value['company'];
							$consultancyexpmodel->days=$value['days'];
							if($consultancyexpmodel->validate() && $consultancyexpmodel->save())
							{
								/*
								foreach ($value['process'] as $process)
								{
									$conexpprocessmodel=new UserConsultancyExperienceProcess();
									$conexpprocessmodel->user_consultancy_experience_id=$consultancyexpmodel->id;
									$conexpprocessmodel->process_id=$process;
									$conexpprocessmodel->save();
								} 
								*/
							}
						}
						
					}
					$responsedata=array('status'=>1,'message'=>'Consultancy Experience updated successfully','user_id'=>$model->id);
				}else if($data['actiontype'] =='cpd'){
					//UserTrainingInfo::deleteAll(['user_id' => $data['id']]);
					if(is_array($data['training_info']) && count($data['training_info'])>0)
					{
						foreach ($data['training_info'] as $value)
						{	
							if(isset($value['id']) && $value['id'] >0 ){
								$trainingmodel=UserTrainingInfo::find()->where(['id'=>$value['id']])->one();
								if($trainingmodel === null ){
									$trainingmodel=new UserTrainingInfo();
								}
							}else{
								$trainingmodel=new UserTrainingInfo();
							}
							$trainingmodel->user_id=$model->id;
							$trainingmodel->subject=$value['subject'];
							$trainingmodel->training_hours=$value['training_hours'];
							$trainingmodel->training_date=$value['training_date'];
							$trainingmodel->save();
						}
					}
					$responsedata=array('status'=>1,'message'=>'CPD updated successfully','user_id'=>$model->id);
}else if($data['actiontype'] =='audit_standard'){
				//AuditStandard::deleteAll(['user_id' => $data['id']]);
				 	if(is_array($data['audit_standard']) && count($data['audit_standard'])>0)
					{
						foreach ($data['audit_standard'] as $value)
						{

							if(isset($value['id']) && $value['id'] >0 ){
								$auditstandard=AuditStandard::find()->where(['id'=>$value['id']])->one();
								if($auditstandard === null ){
									$auditstandard=new AuditStandard();
								}
							}else{
								$auditstandard=new AuditStandard();
							}

							$auditstandard->standard_name=$value['name'];
							$auditstandard->code=$value['code'];
							$auditstandard->version=$value['version'];
							$auditstandard->validate();
							$auditstandard->save();
						}

					}
					$responsedata=array('status'=>1,'message'=>'Audit Standard updated successfully','user_id'=>$model->id);
				}else if($data['actiontype'] =='brand'){
					
					//AuditStandard::deleteAll(['user_id' => $data['id']]);
						 if(is_array($data['brand']) && count($data['brand'])>0)
						{
							
							foreach ($data['brand'] as $value)
							{
	
								if(isset($value['id']) && $value['id'] >0 ){
									$brand=Brand::find()->where(['id'=>$value['id']])->one();
									if($brand === null ){
										$brand=new Brand();
									}
								}else{
									$brand=new Brand();
								}
	                           
								$brand->name=$value['name'];
								$brand->number=$value['number'];
								$brand->version=$value['version'];
								$brand->validate();
								$brand->save();
							}
	
						}
						$responsedata=array('status'=>1,'message'=>'Brand updated successfully','user_id'=>$model->id);
					}
				else if($data['actiontype'] =='translator'){
				//AuditStandard::deleteAll(['user_id' => $data['id']]);
				 	if(is_array($data['translator']) && count($data['translator'])>0)
					{
						
						foreach ($data['translator'] as $value)
						{

							$target_dir = Yii::$app->params['user_files'];
							
							if (isset($value['id']) && $value['id'] >0) {
								$translator=Translator::find()->where(['id'=>$value['id']])->one();
								if ($translator === null) {
									$translator=new Translator();
								}
							}else{
								$translator=new Translator();
							}
							
							$translator_file = "";
							for ($i = 0; $i < $data["length"]; $i++) {
                                if (isset($datapost['filesaray'. $i]) && is_string($datapost['filesaray'. $i])){
                                    $translator_file .= $datapost['filesaray'. $i];
                                    $translator_file .= "|";
                                }else{
									$tmp_name1 = $_FILES["filesaray". $i ]["tmp_name"];
									$name1 = $_FILES["filesaray". $i]["name"];

									$translator_file .= Yii::$app->globalfuns->postFiles($name1, $tmp_name1, $target_dir);
                                    $translator_file .= "|";
								}
							}
							                        
                            $translator->country=$value['country'];
                            $translator->surname=$value['suppliername'];
                            $translator->employment=$value['employment'];
                            $translator->language1=$value['language1'];
                            $translator->language2=$value['language2']?$value['language2']:"";
                            $translator->language3=$value['language3']?$value['language3']:"";
                            $translator->language4=$value['language4']?$value['language4']:"";
                            $translator->email=$value['email'];
                            $translator->phone=$value['phone'];
                            $translator->status=$value['status'];
                            $translator->filename=$translator_file;
                            $translator->save();

							$responsedata=array('status'=>1,'message'=>'Translator updated successfully','user_id'=>$translator->getErrors());
						}
					}					
				}
				else if($data['actiontype'] =='business_group'){

					if(!$this->canDoUpdateAccess($update_user_id,['add_edit_user_business_group'])){
						return false;
					}

					//print_r($data); die;
					/*$bsectormodel = UserBusinessGroup::find()->where(['user_id' => $data['id']])->all();
					if($bsectormodel!== null)
					{
						foreach($bsectormodel as $val)
						{
							UserBusinessGroupCode::deleteAll(['business_group_id' => $val->id,'status'=>0]);

							$codechk = UserBusinessGroupCode::find()->where(['business_group_id' => $val->id])->all();
							if(count($codechk)<=0){
								$val->delete();
							}
						}

					}
					*/

					
					//UserBusinessGroup::deleteAll(['user_id' => $data['id'],'status'=>0]);
					if(is_array($data['business_sector_group']) && count($data['business_sector_group'])>0)
					{
						$target_dir = Yii::$app->params['user_files']; 
						foreach ($data['business_sector_group'] as $key => $value)
						{	
							if(isset($value['id']) && $value['id'] >0 ){
								$bgroupmodel=UserBusinessGroup::find()->where(['id'=>$value['id']])->one();
								if($bgroupmodel === null ){
									$bgroupmodel=new UserBusinessGroup();
								}
							}else{
								$bgroupmodel=new UserBusinessGroup();
							}

							
							//print_r($value);
							$exam_file = '';
							$technical_interview_file ='';

							if($value['academic_qualification_status'] ==2)
							{
								//print_r($_FILES); die;
								//$key=1;
								if(isset($_FILES['examFileNames']['name']))
								{
									$tmp_name = $_FILES["examFileNames"]["tmp_name"];
						   			$name = $_FILES["examFileNames"]["name"];
									$exam_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);

									if($bgroupmodel->exam_file != ''){
										Yii::$app->globalfuns->removeFiles($bgroupmodel->exam_file,$target_dir);
									}
									
								}else{
									$exam_file = $value['examfilename'];
								}
							}
							if(isset($_FILES['technicalInterviewFileNames']['name']))
							{

								$tmp_name = $_FILES["technicalInterviewFileNames"]["tmp_name"];
					   			$name = $_FILES["technicalInterviewFileNames"]["name"];
								$technical_interview_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);

								if($bgroupmodel->technical_interview_file != ''){
									Yii::$app->globalfuns->removeFiles($bgroupmodel->technical_interview_file,$target_dir);
								}
							}else{
								$technical_interview_file = $value['technicalfilename'];
							}
							
							

							
							$bgroupmodel->user_id=$model->id;
							$bgroupmodel->standard_id=$value['standard_id'];
							$bgroupmodel->business_sector_id=$value['business_sector_id'];
							$bgroupmodel->academic_qualification_status=$value['academic_qualification_status'];
							$bgroupmodel->exam_file=$exam_file;
							$bgroupmodel->technical_interview_file=$technical_interview_file;
							$bgroupmodel->created_by=$userData['userid'];
							$bgroupmodel->created_at=time();
							if($bgroupmodel->validate() && $bgroupmodel->save())
							{
								UserBusinessGroupCode::deleteAll(['business_group_id' => $bgroupmodel->id,'status'=>0]);

								foreach ($value['business_sector_group_code'] as $codevalue)
								{
									$bgroupcodemodel=new UserBusinessGroupCode();
									$bgroupcodemodel->business_group_id=$bgroupmodel->id;
									$bgroupcodemodel->business_sector_group_id=$codevalue;
									$bgroupcodemodel->save();
								}
							}
						}
					}


					/*
					$command = $connection->createCommand("SELECT std.name as standard_name,gp.id, gp.`business_sector_id`,master_gp.name,
							gp.standard_id,std.name, master_gp.name as businesssector_name, gp.academic_qualification_status,
							gp.exam_file,gp.technical_interview_file,
							GROUP_CONCAT(gpcode.`business_sector_group_id`) as business_sector_group_ids,
							GROUP_CONCAT(master_gpcode.`group_code`) as group_codes,
							GROUP_CONCAT(gpcode.`id`) as business_sector_group_code_ids,

							GROUP_CONCAT(gpcode.`status_change_comment` separator '||SP||') as status_change_comment,
							GROUP_CONCAT(gpcode.`status_change_date` separator '||SP||') as status_change_date,
							GROUP_CONCAT(gpcode.`status_change_by` separator '||SP||') as status_change_by,

							gpcode.status 
							FROM `tbl_user_business_group` as gp 
							inner join `tbl_user_business_group_code` as gpcode on gp.id=gpcode.`business_group_id` 
							inner join `tbl_business_sector` as master_gp on master_gp.id=gp.`business_sector_id` 
							inner join `tbl_business_sector_group` as master_gpcode on master_gpcode.id=gpcode.`business_sector_group_id`  
							inner join `tbl_standard` as std on std.id=gp.standard_id 
							WHERE `user_id`=".$data['id']." group by gpcode.status,gpcode.`business_group_id` ");
					$businessGroupmodel = $command->queryAll();
					if(count($businessGroupmodel)>0)
					{
						foreach($businessGroupmodel as $bgroup)
						{
							//$roleArray[]=$bgroup->role_id;
							//$roleNameArray[]=$bgroup->role->role_name;
							$status_change_by = explode('||SP||',$bgroup['status_change_by']);
							$status_change_by_name = [];
							if(count($status_change_by)>0){
								foreach($status_change_by as $userchangeid){
									$Usermodel = User::find()->where(['id' => $userchangeid])->one();
									if ($Usermodel !== null)
									{
										$status_change_by_name[] = $Usermodel->first_name.' '.$Usermodel->last_name;
									}
								}
							}
							
							$roledtArray=array();
							$roledtArray['id']=$bgroup['id'];
							$roledtArray['standard_id']=$bgroup['standard_id'];
							$roledtArray['business_sector_id']=$bgroup['business_sector_id'];
							//$roledtArray['business_sector_group_id']=$bgroup->business_sector_group_id;
							
							
							$roledtArray['standard_name']=$bgroup['standard_name'];
							$roledtArray['business_sector_name']=$bgroup['businesssector_name'];
							
							
							
							$roledtArray['academic_qualification']=$bgroup['academic_qualification_status'];
							$roledtArray['academic_qualification_name']=$bgroup['academic_qualification_status']==1?'Yes':'No';
							$roledtArray['examfilename']=$bgroup['exam_file'];
							$roledtArray['technicalfilename']=$bgroup['technical_interview_file'];
							
	
							$groupcodeArr = $bgroup['business_sector_group_ids'];
							$groupnamecodeArr = $bgroup['group_codes'];
							$groupcodeIdArr = $bgroup['business_sector_group_ids'];
							$roledtArray['business_sector_group_id'] = explode(',',$groupcodeArr);//$groupcodeArr;
							$roledtArray['business_sector_group_id_arr'] = explode(',',$groupcodeArr);
							$roledtArray['business_sector_group_name'] = $groupnamecodeArr;
							$roledtArray['business_sector_group_name_arr'] = explode(',',$groupnamecodeArr);
							$roledtArray['business_sector_group_code_id'] = explode(',',$bgroup['business_sector_group_code_ids']);

							$roledtArray['status']=$bgroup['status'];
							$roledtArray['approval_comment']= explode('||SP||',$bgroup['status_change_comment']);
							$roledtArray['approval_by_name']=$status_change_by_name;
							$status_change_datearr = explode('||SP||',$bgroup['status_change_date']);
							if(count($status_change_datearr)>0){
								foreach($status_change_datearr as $key => $datea){
									//$status_change_datearr[$key] = date($date_format,$datea);
								}
							}else{
								$status_change_datearr = [];
							}
							$status_change_datearr = [];
							$roledtArray['approval_date']=$status_change_datearr;

							foreach($roledtArray['business_sector_group_id_arr'] as $bgpid){
								$roledtArray["rejected_history"][$bgpid] = [];
							}
							

				
							
							if($bgroup['status']==0){
								$resultarr["businessgroup_new"][] = $roledtArray;
							}else if($bgroup['status']==1){
								$resultarr["businessgroup_approvalwaiting"][] = $roledtArray;
							}else if($bgroup['status']==2){
								$resultarr["businessgroup_approved"][] = $roledtArray;
							}else if($bgroup['status']==3){
								$resultarr["businessgroup_rejected"][] = $roledtArray;
							}

							
						}
					}
					,'resultarr'=>$resultarr
					*/
					$responsedata=array('status'=>1,'message'=>'Business Group has been updated successfully','user_id'=>$model->id);
				}else if($data['actiontype'] =='rejtebusiness_group'){
					if(!$this->canDoUpdateAccess($update_user_id,['add_edit_te_business_group'])){
						return false;
					}
					if(is_array($data['business_sector_group']) && count($data['business_sector_group'])>0)
					{
						$target_dir = Yii::$app->params['user_files']; 
						foreach ($data['business_sector_group'] as $key => $value)
						{	
							
							$exam_file = '';
							$technical_interview_file ='';

							if($value['academic_qualification'] ==2){
								//print_r($_FILES); die;
								//$key=1;
								if(isset($_FILES['examFileNames']['name']))
								{

									$tmp_name = $_FILES["examFileNames"]["tmp_name"];
						   			$name = $_FILES["examFileNames"]["name"];
									$exam_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
									
								}else{
									$exam_file = $value['examfilename'];
								}

								
							}
							
							if(isset($_FILES['technicalInterviewFileNames']['name']))
							{

								$tmp_name = $_FILES["technicalInterviewFileNames"]["tmp_name"];
					   			$name = $_FILES["technicalInterviewFileNames"]["name"];
								$technical_interview_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
								
								
							}else{
								$technical_interview_file = $value['technicalfilename'];
							}
							//$bgroupmodel=UserBusinessGroup::find()->where(['id'=>])->one();
							$bgroupmodelold = UserRoleTechnicalExpertBs::find()->where(['id' => $value['id']])->one();
							if($bgroupmodelold!== null)
							{
								
							}else{
								//$bgroupmodel=new UserBusinessGroup();
							}
							$bgroupmodel=new UserRoleTechnicalExpertBs();
							
							$bgroupmodel->user_id=$model->id;
							//$bgroupmodel->status=1;
							$bgroupmodel->role_id=$value['role_id'];
							$bgroupmodel->business_sector_id=$value['business_sector_id'];
							$bgroupmodel->academic_qualification_status=$value['academic_qualification'];
							$bgroupmodel->exam_file=$exam_file;
							$bgroupmodel->technical_interview_file=$technical_interview_file;
							$bgroupmodel->created_by=$userData['userid'];
							$bgroupmodel->approved_type=2;
							$bgroupmodel->created_at=time();



							$business_sector_group_codearr = !is_array($value['business_sector_group_id'])?explode(',',$value['business_sector_group_id']):$value['business_sector_group_id'];
							foreach ($business_sector_group_codearr as $codevalue)
							{
								$bgroupcodemodel=UserRoleTechnicalExpertBsCode::find()->where(['business_sector_group_id'=>$codevalue, 'user_role_technical_expert_bs_id' => $bgroupmodelold->id])->one();



								$bgrouphistorymodal = new UserRoleTechnicalExpertBsCodeHistory();
								$bgrouphistorymodal->user_role_technical_expert_bs_code_id = $bgroupcodemodel->id;
								$bgrouphistorymodal->user_id=$bgroupmodelold->user_id;
								$bgrouphistorymodal->role_id=$bgroupmodelold->role_id;
								$bgrouphistorymodal->business_sector_id=$bgroupmodelold->business_sector_id;
								$bgrouphistorymodal->user_role_technical_expert_bs_id=$bgroupcodemodel->user_role_technical_expert_bs_id;
								$bgrouphistorymodal->business_sector_group_id=$bgroupcodemodel->business_sector_group_id;
								$bgrouphistorymodal->academic_qualification_status=$bgroupmodelold->academic_qualification_status;
								$bgrouphistorymodal->exam_file=$bgroupmodelold->exam_file;
								$bgrouphistorymodal->technical_interview_file=$bgroupmodelold->technical_interview_file;
								$bgrouphistorymodal->status = $bgroupcodemodel->status;
								$bgrouphistorymodal->approval_by = $bgroupcodemodel->approval_by;
								$bgrouphistorymodal->approval_date = $bgroupcodemodel->approval_date;
								$bgrouphistorymodal->approval_comment = $bgroupcodemodel->approval_comment;
								$bgrouphistorymodal->created_by = $bgroupmodelold->created_by;
								$bgrouphistorymodal->created_at = $bgroupmodelold->created_at;
								$bgrouphistorymodal->save();
							}




							if($bgroupmodel->validate() && $bgroupmodel->save())
							{
								//$business_sector_group_codearr = explode(',',$value['business_sector_group_code']);
								foreach ($business_sector_group_codearr as $codevalue)
								{

									//$bgroupcodemodel=new UserBusinessGroupCode();
									$bgroupcodemodel=null;
									if($bgroupmodelold !== null){
										$bgroupcodemodel=UserRoleTechnicalExpertBsCode::find()->where(['business_sector_group_id'=>$codevalue, 'user_role_technical_expert_bs_id' => $bgroupmodelold->id,'status'=>3])->one();
									}
									
									if($bgroupcodemodel===null){
										$bgroupcodemodel=new UserRoleTechnicalExpertBsCode();
									}
									$bgroupcodemodel->user_role_technical_expert_bs_id=$bgroupmodel->id;
									$bgroupcodemodel->business_sector_group_id=$codevalue;
									$bgroupcodemodel->status = 1;
									$bgroupcodemodel->approval_date = time();
									$bgroupcodemodel->approval_by = $userData['userid'];
									$bgroupcodemodel->save();

								}
							}
							$bgroupmodelold =  UserRoleTechnicalExpertBs::find()->where(['id' => $value['id']])->one();
							if($bgroupmodelold!== null)
							{
								$codechk =  UserRoleTechnicalExpertBsCode::find()->where(['user_role_technical_expert_bs_id' => $bgroupmodelold->id])->all();
								if(count($codechk)<=0){
									$bgroupmodelold->delete();
								}
								
							}
						}

						
					}
					/*
					 
					$bgroupmodelold = UserRoleTechnicalExpertBsCode::find()->where(['id' => $data['regroupid']])->one();
					if($bgroupmodelold!== null)
					{
					}else{
					}
					


					 
					$bgroupcodemodel=UserRoleTechnicalExpertBsCode::find()->where(['id' => $data['regroupid']])->one();

					$tebusinesssector = $bgroupcodemodel->expertbs;

					$bgrouphistorymodal = new UserRoleTechnicalExpertBsCodeHistory();
					$bgrouphistorymodal->user_role_technical_expert_bs_code_id = $bgroupcodemodel->id;
					$bgrouphistorymodal->user_id=$tebusinesssector->user_id;
					$bgrouphistorymodal->role_id=$tebusinesssector->role_id;
					$bgrouphistorymodal->business_sector_id=$tebusinesssector->business_sector_id;
					$bgrouphistorymodal->business_sector_group_id=$bgroupcodemodel->business_sector_group_id;
					$bgrouphistorymodal->status = $bgroupcodemodel->status;
					$bgrouphistorymodal->approval_by = $bgroupcodemodel->approval_by;
					$bgrouphistorymodal->approval_date = $bgroupcodemodel->approval_date;
					$bgrouphistorymodal->approval_comment = $bgroupcodemodel->approval_comment;
					$bgrouphistorymodal->created_by = $bgroupcodemodel->created_by;
					$bgrouphistorymodal->created_at = $bgroupcodemodel->created_at;
					$bgrouphistorymodal->save();
					

					//$bgroupcodemodel->business_group_id=$bgroupmodel->id;
					//$bgroupcodemodel->business_sector_group_id=$codevalue;
					$bgroupcodemodel->status = 1;
					$bgroupcodemodel->approval_date = time();
					$bgroupcodemodel->approval_by = $userid;//$model->id;
					$bgroupcodemodel->save();
					*/


	
					$responsedata=array('status'=>1,'message'=>'Business Group has been updated successfully','user_id'=>$model->id);
				}else if($data['actiontype'] =='rejbusiness_group'){
					
					
					if(!$this->canDoUpdateAccess($update_user_id,['add_edit_user_business_group'])){
						return false;
					}
					if(is_array($data['business_sector_group']) && count($data['business_sector_group'])>0)
					{
						$target_dir = Yii::$app->params['user_files']; 
						foreach ($data['business_sector_group'] as $key => $value)
						{	
							
							$fileKey = $value['rejbindex'];
							

							

							$exam_file = '';
							$technical_interview_file ='';

							if($value['academic_qualification'] ==2){
								//print_r($_FILES); die;
								//$key=1;
								if(isset($_FILES['examFileNames']['name']))
								{

									$tmp_name = $_FILES["examFileNames"]["tmp_name"];
						   			$name = $_FILES["examFileNames"]["name"];
									$exam_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);

									
								}else{
									$exam_file = $value['examfilename'];
								}

								
							}
							
							if(isset($_FILES['technicalInterviewFileNames']['name']))
							{

								$tmp_name = $_FILES["technicalInterviewFileNames"]["tmp_name"];
					   			$name = $_FILES["technicalInterviewFileNames"]["name"];
								$technical_interview_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
								 
								
							}else{
								$technical_interview_file = $value['technicalfilename'];
							}
							//$bgroupmodel=UserBusinessGroup::find()->where(['id'=>])->one();
							$bgroupmodelold = UserBusinessGroup::find()->where(['id' => $value['id']])->one();
							if($bgroupmodelold!== null)
							{
								
							}else{
								//$bgroupmodel=new UserBusinessGroup();
							}
							$bgroupmodel=new UserBusinessGroup();
							
							$bgroupmodel->user_id=$model->id;
							//$bgroupmodel->status=1;
							$bgroupmodel->standard_id=$value['standard_id'];
							$bgroupmodel->business_sector_id=$value['business_sector_id'];
							$bgroupmodel->academic_qualification_status=$value['academic_qualification'];
							$bgroupmodel->exam_file=$exam_file;
							$bgroupmodel->technical_interview_file=$technical_interview_file;
							$bgroupmodel->created_by=$userData['userid'];
							$bgroupmodel->created_at=time();



							$business_sector_group_codearr = !is_array($value['business_sector_group_id'])?explode(',',$value['business_sector_group_id']):$value['business_sector_group_id'];
							foreach ($business_sector_group_codearr as $codevalue)
							{
								$bgroupcodemodel=UserBusinessGroupCode::find()->where(['business_sector_group_id'=>$codevalue, 'business_group_id' => $bgroupmodelold->id])->one();



								$bgrouphistorymodal = new UserBusinessGroupCodeHistory();
								$bgrouphistorymodal->user_business_group_code_id = $bgroupcodemodel->id;
								$bgrouphistorymodal->user_id=$bgroupmodelold->user_id;
								$bgrouphistorymodal->standard_id=$bgroupmodelold->standard_id;
								$bgrouphistorymodal->business_sector_id=$bgroupmodelold->business_sector_id;
								$bgrouphistorymodal->business_group_id=$bgroupcodemodel->business_group_id;
								$bgrouphistorymodal->business_sector_group_id=$bgroupcodemodel->business_sector_group_id;
								$bgrouphistorymodal->academic_qualification_status=$bgroupmodelold->academic_qualification_status;
								$bgrouphistorymodal->exam_file=$bgroupmodelold->exam_file;
								$bgrouphistorymodal->technical_interview_file=$bgroupmodelold->technical_interview_file;
								$bgrouphistorymodal->status = $bgroupcodemodel->status;
								$bgrouphistorymodal->status_change_by = $bgroupcodemodel->status_change_by;
								$bgrouphistorymodal->status_change_date = $bgroupcodemodel->status_change_date;
								$bgrouphistorymodal->status_change_comment = $bgroupcodemodel->status_change_comment;
								$bgrouphistorymodal->created_by = $bgroupmodelold->created_by;
								$bgrouphistorymodal->created_at = $bgroupmodelold->created_at;
								$bgrouphistorymodal->save();
							}




							if($bgroupmodel->validate() && $bgroupmodel->save())
							{
								//$business_sector_group_codearr = explode(',',$value['business_sector_group_code']);
								foreach ($business_sector_group_codearr as $codevalue)
								{

									//$bgroupcodemodel=new UserBusinessGroupCode();
									$bgroupcodemodel=null;
									if($bgroupmodelold !== null){
										$bgroupcodemodel=UserBusinessGroupCode::find()->where(['business_sector_group_id'=>$codevalue, 'business_group_id' => $bgroupmodelold->id,'status'=>3])->one();
									}
									
									if($bgroupcodemodel===null){
										$bgroupcodemodel=new UserBusinessGroupCode();
									}
									$bgroupcodemodel->business_group_id=$bgroupmodel->id;
									$bgroupcodemodel->business_sector_group_id=$codevalue;
									$bgroupcodemodel->status = 1;
									$bgroupcodemodel->status_change_date = time();
									$bgroupcodemodel->status_change_by = $userData['userid'];
									$bgroupcodemodel->save();

								}
							}
							$bgroupmodelold = UserBusinessGroup::find()->where(['id' => $value['id']])->one();
							if($bgroupmodelold!== null)
							{
								$codechk = UserBusinessGroupCode::find()->where(['business_group_id' => $bgroupmodelold->id])->all();
								if(count($codechk)<=0){
									$bgroupmodelold->delete();
								}
								
							}
						}

						
					}
					$responsedata=array('status'=>1,'message'=>'Business Group has been updated successfully','user_id'=>$model->id);
				}else if($data['actiontype'] =='te_business_group'){
					
					if(!$this->canDoUpdateAccess($update_user_id,['add_edit_te_business_group'])){
						return false;
					}
					if(is_array($data['te_business_group']) && count($data['te_business_group'])>0)
					{
						foreach ($data['te_business_group'] as $key => $value)
						{	


							if(isset($value['id']) && $value['id'] >0 ){
								$bgroupmodel=UserRoleTechnicalExpertBs::find()->where(['id'=>$value['id']])->one();
								if($bgroupmodel === null ){
									$bgroupmodel=new UserRoleTechnicalExpertBs();
									$bgroupmodel->created_by=$userData['userid'];
									$bgroupmodel->created_at=time();
								}else{
									//$bgroupmodel->udpated_by=$userData['userid'];
								}
							}else{
								$bgroupmodel=new UserRoleTechnicalExpertBs();
								$bgroupmodel->created_by=$userData['userid'];
								$bgroupmodel->created_at=time();
							}


							$exam_file = '';
							$technical_interview_file ='';

							if($value['academic_qualification_status'] ==2)
							{
								if(isset($_FILES['examFileNames']['name']))
								{
									if($bgroupmodel->exam_file != ''){
										Yii::$app->globalfuns->removeFiles($bgroupmodel->exam_file,$target_dir);
									}

									$tmp_name = $_FILES["examFileNames"]["tmp_name"];
						   			$name = $_FILES["examFileNames"]["name"];
									$exam_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
								}
								else
								{
									$exam_file = $value['examfilename'];
								}

								
							}
							if(isset($_FILES['technicalInterviewFileNames']['name']))
							{
								if($bgroupmodel->technical_interview_file != ''){
									Yii::$app->globalfuns->removeFiles($bgroupmodel->technical_interview_file,$target_dir);
								}
								$tmp_name = $_FILES["technicalInterviewFileNames"]["tmp_name"];
					   			$name = $_FILES["technicalInterviewFileNames"]["name"];
								$technical_interview_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);

							}else{
								$technical_interview_file = $value['technicalfilename'];
							}

							
							$bgroupmodel->user_id=$model->id;
							$bgroupmodel->role_id=$value['role_id'];
							$bgroupmodel->business_sector_id=$value['business_sector_id'];
							$bgroupmodel->academic_qualification_status=$value['academic_qualification_status'];
							$bgroupmodel->exam_file=$exam_file;
							$bgroupmodel->technical_interview_file=$technical_interview_file;
							$bgroupmodel->status=0;
							$bgroupmodel->approved_type=2;
							
							if($bgroupmodel->validate() && $bgroupmodel->save())
							{
								UserRoleTechnicalExpertBsCode::deleteAll(['user_role_technical_expert_bs_id' => $bgroupmodel->id]);
								foreach ($value['business_sector_group_id'] as $codevalue)
								{
									$bgroupcodemodel=new UserRoleTechnicalExpertBsCode();
									$bgroupcodemodel->user_role_technical_expert_bs_id=$bgroupmodel->id;
									$bgroupcodemodel->business_sector_group_id=$codevalue;
									$bgroupcodemodel->status=0;
									$bgroupcodemodel->created_by = $userData['userid'];
									$bgroupcodemodel->save();
								}
							}
						}
					}
					$responsedata=array('status'=>1,'message'=>'Technical Expert Business Group Updated successfully','user_id'=>$model->id);
				}else if($data['actiontype'] =='approvedte_business_group'){
					if(!$this->canDoUpdateAccess($update_user_id,['add_edit_te_business_group'])){
						return false;
					}
					if(is_array($data['approvedte_business_group']) && count($data['approvedte_business_group'])>0)
					{
						foreach ($data['approvedte_business_group'] as $key => $value)
						{	


							if(isset($value['id']) && $value['id'] >0 ){
								$bgroupmodel=UserRoleTechnicalExpertBs::find()->where(['id'=>$value['id']])->one();
								if($bgroupmodel === null ){
									$bgroupmodel=new UserRoleTechnicalExpertBs();
									$bgroupmodel->created_by=$userData['userid'];
									$bgroupmodel->created_at=time();
									
								}else{
									//$bgroupmodel->udpated_by=$userData['userid'];
								}
							}else{
								$bgroupmodel=new UserRoleTechnicalExpertBs();
								$bgroupmodel->created_by=$userData['userid'];
								$bgroupmodel->created_at=time();
								
								
							}

							$bgroupmodel->approved_type=1;
							$bgroupmodel->user_id=$model->id;
							$bgroupmodel->role_id=$value['role_id'];
							$bgroupmodel->business_sector_id=$value['business_sector_id'];
							$bgroupmodel->status = 1;
							
							
							if($bgroupmodel->validate() && $bgroupmodel->save())
							{
								UserRoleTechnicalExpertBsCode::deleteAll(['user_role_technical_expert_bs_id' => $bgroupmodel->id]);
								foreach ($value['business_sector_group_id'] as $codevalue)
								{
									$bgroupcodemodel=new UserRoleTechnicalExpertBsCode();
									$bgroupcodemodel->user_role_technical_expert_bs_id=$bgroupmodel->id;
									$bgroupcodemodel->business_sector_group_id=$codevalue;
									$bgroupcodemodel->status=2;
									$bgroupcodemodel->created_by = $userData['userid'];

									//$bgroupcodemodel->approval_status=2;
									$bgroupcodemodel->approval_date = time();
									$bgroupcodemodel->approval_by = $userData['userid'];

									$bgroupcodemodel->save();
								}
							}
						}
					}
					$responsedata=array('status'=>1,'message'=>'Technical Expert Business Group Updated successfully','user_id'=>$model->id);
				}else if($data['actiontype'] =='declaration'){
					if(!$this->canDoUpdateAccess($update_user_id,['add_edit_declaration']) && !($user_type==1 && $userid==$update_user_id)){
						return false;
					}
					//UserDeclaration::deleteAll(['user_id' => $data['id'],'status'=>0]);
					if(is_array($data['declaration']) && count($data['declaration'])>0)
					{
						foreach ($data['declaration'] as $vals)
						{	
							//$declarationmodel=new UserDeclaration();
							if(isset($vals['id']) && $vals['id'] >0 ){
								$declarationmodel=UserDeclaration::find()->where(['id'=>$vals['id']])->one();
								if($declarationmodel === null ){
									$declarationmodel=new UserDeclaration();
								}
							}else{
								$declarationmodel=new UserDeclaration();
							}
							if($vals['sel_close']==3){
								$declarationmodel->user_id=$model->id;
								$declarationmodel->company=$vals['declaration_company'];
								$declarationmodel->contract=$vals['declaration_contract'];
								$declarationmodel->interest=$vals['declaration_interest'];
								$declarationmodel->start_year=$vals['declaration_start_year'];
								$declarationmodel->end_year=$vals['declaration_end_year'];
								$declarationmodel->relation_name="Self";
								$declarationmodel->relation_type="NA";
								$declarationmodel->status_comment='';
								$declarationmodel->relation_declaration_consent=$vals['sel_close'];
								$declarationmodel->re_relation_declaration_consent='';
								$declarationmodel->relation_work='';
								$declarationmodel->save();
							}elseif($vals['sel_close']==2 || $vals['sel_close']==1){
								if(is_array($vals['relationDataEntries'])){
									$userDecMod = UserDeclaration::find()->where(['id'=>$vals['id']])->one();
									if($userDecMod!==null){
										$userDecMod->delete();
									}
									foreach($vals['relationDataEntries'] as $relation){
										$relationmod = new UserDeclaration();
										$relationmod->user_id=$model->id;
										$declarationmodel->status_comment='';
										$relationmod->relation_name=$relation['name'];
										$relationmod->relation_type=$relation['type_name'];
										$relationmod->company=$relation['rel_declaration_company'];
										$relationmod->contract=$relation['rel_declaration_contract'];
										$relationmod->interest=$relation['rel_declaration_interest'];
										$relationmod->start_year=$relation['rel_declaration_start_year'];
										$relationmod->end_year=$relation['rel_declaration_end_year'];
										$relationmod->relation_declaration_consent=$vals['sel_close']?$vals['sel_close']:'';
										$relationmod->re_relation_declaration_consent=$vals['sel_close2']?$vals['sel_close2']:'';
										$relationmod->relation_work=$vals['sel_close']==2?$vals['spouse_work']:'';
										$relationmod->save();
									}
									if($vals['sel_close2']==2){
										$relmod = UserDeclaration::find()->where(['user_id'=>$model->id,'relation_declaration_consent'=>3])->all();
										if(count($relmod)>0){
											foreach($relmod as $rm){
												$rm['relation_work']=$vals['spouse_work'];
												$rm->save();
											}
										}
									}
								}
							}
						}
					}
					$responsedata=array('status'=>1,'message'=>'Declaration Updated successfully','user_id'=>$model->id);
				}else if($data['actiontype'] =='declarationreject'){
					if(!$this->canDoUpdateAccess($update_user_id,['add_edit_declaration']) && !($user_type==1 && $userid==$update_user_id)){
						return false;
					}
					//UserDeclaration::deleteAll(['user_id' => $data['id'],'status'=>0]);
					if(is_array($data['declaration']) && count($data['declaration'])>0)
					{
						foreach ($data['declaration'] as $vals)
						{	
							if(isset($vals['deleted']) && $vals['deleted'] ==1){
								//UserDeclaration()::find()->where(['id'=>$vals['declaration_id']])->delete();
								UserDeclaration::findOne($vals['declaration_id'])->delete();
							}else{
								$declarationmodel=UserDeclaration::find()->where(['id'=>$vals['declaration_id']])->one();


								$dechistorymodal = new UserDeclarationHistory();
								$dechistorymodal->user_declaration_id = $declarationmodel->id;
								$dechistorymodal->user_id = $declarationmodel->user_id; 
								$dechistorymodal->company = $declarationmodel->company;
								$dechistorymodal->contract = $declarationmodel->contract;
								$dechistorymodal->interest = $declarationmodel->interest;
								$dechistorymodal->start_year = $declarationmodel->start_year;
								$dechistorymodal->end_year = $declarationmodel->end_year;
								$dechistorymodal->created_on = $declarationmodel->created_on;
								$dechistorymodal->created_by = $declarationmodel->created_by;
								$dechistorymodal->status = $declarationmodel->status;
								$dechistorymodal->status_change_by = $declarationmodel->status_change_by;
								$dechistorymodal->status_change_date = $declarationmodel->status_change_date;
								$dechistorymodal->status_comment = $declarationmodel->status_comment;
								$dechistorymodal->save();


								$declarationmodel->status = 1;
								$declarationmodel->status_change_by = $userid;
								$declarationmodel->status_change_date = time();
								//$declarationmodel->save();




								$declarationmodel->user_id=$model->id;
								$declarationmodel->company=$vals['declaration_company'];
								$declarationmodel->contract=$vals['declaration_contract_id'];
								$declarationmodel->interest=$vals['declaration_interest'];
								$declarationmodel->start_year=$vals['declaration_start_year'];
								$declarationmodel->end_year=$vals['declaration_end_year'];
								$declarationmodel->relation_declaration_consent=$vals['sel_close']?$vals['sel_close']:'';
								$declarationmodel->re_relation_declaration_consent=$vals['sel_close2']?$vals['sel_close2']:'';
								$declarationmodel->relation_name=$vals['name'];
								$declarationmodel->relation_type=$vals['type_name'];
								if($vals['spouse_work']!=''){
									$declarationmodel->relation_work=$vals['spouse_work'];
								}
								$declarationmodel->save();
							// 	if($declarationmodel->save()){
							// 	if(is_array($vals['relationDataEntries'])){
									
							// 			UserDeclarationRelation::deleteAll(['user_id'=>$model->id,'user_declaration_id'=>$declarationmodel->id]);
							// 		    foreach($vals['relationDataEntries'] as $relation){
							// 			$relationmod = new UserDeclarationRelation();
							// 			$relationmod->user_id=$model->id;
							// 			$relationmod->user_declaration_id=$declarationmodel->id;
							// 			$relationmod->re_relation_declaration_consent=isset($vals['sel_close2'])?$vals['sel_close2']:'2';
							// 			$relationmod->relation_name=$relation['name'];
							// 			$relationmod->relation_type=$relation['type_name'];
							// 			$relationmod->save();
							// 		}
							// 	}
							// }

							}
							//$declarationmodel=new UserDeclaration();
							
						}
					}

					/*
					$declaration_approvalwaiting_arr = [];
					$declarationmodal=UserDeclaration::find()->where(['user_id' => $model->id])->andWhere(['status' => 1])->all();
					if(count($declarationmodal)>0)
					{
						foreach($declarationmodal as $dec)
						{	

							$declarations=array();
							$declarations['id']=$dec['id'];
							$declarations['declaration_company']=$dec['company'];
							$declarations['declaration_contract_id']=$dec['contract'];
							$declarations['declaration_contract']=($dec['contract']!='' && $dec['contract']!=0 ? $dec->arrContract[$dec['contract']]:'NA');
							$declarations['declaration_interest']=$dec['interest'];
							$declarations['declaration_start_year']=$dec['start_year'];
							$declarations['declaration_end_year']=$dec['end_year'];
							$declaration_approvalwaiting_arr[]=$declarations;
						}
						//$resultarr["declaration_approvalwaiting"]=$declaration_approvalwaiting_arr;
						//$responsedata['declaration_approvalwaiting']= $resultarr["declaration_approvalwaiting"];
					}

					,'declaration_approvalwaiting'=>$declaration_approvalwaiting_arr
					*/
					$responsedata=array('status'=>1,'message'=>'Declaration Updated successfully','user_id'=>$model->id);
				}else if($data['actiontype'] =='declarationapproved'){
					if(!$this->canDoUpdateAccess($update_user_id,['add_edit_declaration']) && !($user_type==1 && $userid==$update_user_id)){
						return false;
					}
					//UserDeclaration::deleteAll(['user_id' => $data['id'],'status'=>0]);
					if(is_array($data['declaration']) && count($data['declaration'])>0)
					{
						foreach ($data['declaration'] as $vals)
						{	
							if(isset($vals['deleted']) && $vals['deleted'] ==1){
								//UserDeclaration()::find()->where(['id'=>$vals['declaration_id']])->delete();
								UserDeclaration::findOne($vals['declaration_id'])->delete();
							}else{
								$declarationmodel=UserDeclaration::find()->where(['id'=>$vals['declaration_id']])->one();

								$declarationmodel->status = 1;
								$declarationmodel->status_change_by = $userid;
								$declarationmodel->status_change_date = time();
							
								$declarationmodel->user_id=$model->id;
								$declarationmodel->company=$vals['declaration_company'];
								$declarationmodel->contract=$vals['declaration_contract_id'];
								$declarationmodel->interest=$vals['declaration_interest'];
								$declarationmodel->start_year=$vals['declaration_start_year'];
								$declarationmodel->end_year=$vals['declaration_end_year'];
								$declarationmodel->relation_declaration_consent=$vals['sel_close']?$vals['sel_close']:'';
								$declarationmodel->re_relation_declaration_consent=$vals['sel_close2']?$vals['sel_close2']:'';
								$declarationmodel->relation_name=$vals['name'];
								$declarationmodel->relation_type=$vals['type_name'];
								if($vals['spouse_work']!=''){
									$declarationmodel->relation_work=$vals['spouse_work'];
								}
								
								$declarationmodel->save();

							}

						}
					}
					$responsedata=array('status'=>1,'message'=>'Declaration Updated successfully','user_id'=>$model->id);
				}else if($data['actiontype'] =='standards'){
					if(!$this->canDoUpdateAccess($update_user_id,['add_edit_user_standards_business_sectors'])){
						return false;
					}
					//&& is_array($data['process']) && count($data['standard'])>0  
					if($data['standard'] && $data['standard']!='' && $data['standard']>0)
					{
						$stdmodel= UserStandard::find()->where(['user_id'=>$model->id,'standard_id'=>$data['standard'],'approval_status'=>0])->one();
						if($stdmodel === null){
							$stdmodel=new UserStandard();
						}
						//$stdmodel=new UserStandard();
						$stdmodel->user_id=$model->id;
						$stdmodel->standard_id=$data['standard'];
						$stdmodel->standard_exam_date=date('Y-m-d',strtotime($data['std_exam_date']));
						$stdmodel->pre_qualification=$data['pre_qualification'];
						if($data['recycle_exam_date'] !=''){
							$stdmodel->recycle_exam_date=date('Y-m-d',strtotime($data['recycle_exam_date']));
						}
						if($data['social_exam_date'] !=''){
							$stdmodel->social_course_exam_date=date('Y-m-d',strtotime($data['social_exam_date']));
						}
						if($data['witness_date'] !=''){
							$stdmodel->witness_date=date('Y-m-d',strtotime($data['witness_date']));

							if(isset($_FILES['witness_file']['name']))
							{	
								$tmp_name = $_FILES["witness_file"]["tmp_name"];
								$name = $_FILES["witness_file"]["name"];
								if($stdmodel->witness_file != ''){
									Yii::$app->globalfuns->removeFiles($stdmodel->witness_file,$target_dir);
								}

								$stdmodel->witness_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
								
							}
						}
						
						

						if(isset($_FILES['std_exam_file']['name']))
						{	
							if($stdmodel->standard_exam_file != ''){
								Yii::$app->globalfuns->removeFiles($stdmodel->standard_exam_file,$target_dir);
							}
							$tmp_name = $_FILES["std_exam_file"]["tmp_name"];
				   			$name = $_FILES["std_exam_file"]["name"];
							$stdmodel->standard_exam_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
							
						}


						if(isset($_FILES['recycle_exam_file']['name']))
						{	
							if($stdmodel->recycle_exam_file != ''){
								Yii::$app->globalfuns->removeFiles($stdmodel->recycle_exam_file,$target_dir);
							}
							$tmp_name = $_FILES["recycle_exam_file"]["tmp_name"];
				   			$name = $_FILES["recycle_exam_file"]["name"];
							$stdmodel->recycle_exam_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);

							
						}


						if(isset($_FILES['social_exam_file']['name']))
						{	
							if($stdmodel->social_course_exam_file != ''){
								Yii::$app->globalfuns->removeFiles($stdmodel->social_course_exam_file,$target_dir);
							}
							$tmp_name = $_FILES["social_exam_file"]["tmp_name"];
				   			$name = $_FILES["social_exam_file"]["name"];
							$stdmodel->social_course_exam_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
							
						}
						
						if(isset($_FILES['qua_exam_file']['name']))
						{	
							
							if($stdmodel->qua_exam_file != ''){
								Yii::$app->globalfuns->removeFiles($stdmodel->social_course_exam_file,$target_dir);
							}
							$tmp_name = $_FILES["qua_exam_file"]["tmp_name"];
				   			$name = $_FILES["qua_exam_file"]["name"];
							$stdmodel->qua_exam_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
							
						}
						// UserStandard::deleteAll(['user_id' => $data['id']]);
						//$stdmodel->witness_valid_until='';
						
						$stdmodel->save();
						//print_r($stdmodel->getErrors());
						/*
						UserProcess::deleteAll(['user_id' => $data['id']]);
						foreach ($data['process'] as $value)
						{ 
							$expmodel=new UserProcess();
							$expmodel->user_id=$model->id;
							$expmodel->process_id =$value;
							$expmodel->save();
						}
						*/

						// UserBusinessSector::deleteAll(['user_id' => $model->id]);
						// foreach ($data['business_sector_id'] as $value)
						// { 
						// 	$userbsectormodel = new UserBusinessSector();
						// 	$userbsectormodel->user_id = $model->id;
						// 	$userbsectormodel->business_sector_id = $value;
						// 	$userbsectormodel->save();
						// }


						// UserBusinessSectorGroup::deleteAll(['user_id' => $model->id]);
						// foreach ($data['business_sector_group_id'] as $value)
						// { 
						// 	$userbsectorgrpmodel = new UserBusinessSectorGroup;
						// 	$userbsectorgrpmodel->user_id = $model->id;
						// 	$userbsectorgrpmodel->business_sector_group_id = $value;
						// 	$userbsectorgrpmodel->save();
						// }
						$resultarr["standard_rejected"] = [];
						$resultarr["standard_approvalwaiting"] = [];
						$resultarr["standard_approved"] = [];
						$resultarr["standard"] = [];


						$standardArray = [];
						$standardChkNameArray = [];
						$standardIdExcept = [];
						$standardmodel = UserStandard::find()->where(['user_id' => $data['id']])
													->orderBy(['id'=>SORT_DESC])->all();
						if(count($standardmodel)>0)
						{
							foreach($standardmodel as $standard)
							{
								$stds=[];
								$stds["id"]=$standard->id;
								$stds["status"]=$standard->approval_status;
								$stds["standard"]=$standard->standard_id;
								$stds["standard_name"]=$standard->standard->name;
								$stds["standard_code"]=$standard->standard->code;
								$stds["pre_qualification"]=$standard->pre_qualification;
								$stds["standard_exam_date"]=date($date_format,strtotime($standard->standard_exam_date));
								$stds["recycle_exam_date"]=($standard->recycle_exam_date !='0000-00-00' && $standard->recycle_exam_date !=null && $standard->recycle_exam_date!='1970-01-01')?date($date_format,strtotime($standard->recycle_exam_date)):'';
								$stds["social_course_exam_date"]=($standard->social_course_exam_date !='0000-00-00' && $standard->social_course_exam_date !=null && $standard->social_course_exam_date!='1970-01-01')?date($date_format,strtotime($standard->social_course_exam_date)):'';
								$stds["witness_date"]=($standard->witness_date !='0000-00-00' && $standard->witness_date !=null && $standard->witness_date!='1970-01-01')?date($date_format,strtotime($standard->witness_date)):'';
								$stds["witness_valid_until"]=($standard->witness_valid_until !='0000-00-00' && $standard->witness_valid_until !=null && $standard->witness_valid_until!='1970-01-01')?date($date_format,strtotime($standard->witness_valid_until)):'';
								$stds["witness_comment"]=$standard->witness_comment?:'';
								
								$stds["standard_exam_file"]=$standard->standard_exam_file?:'';
								$stds["recycle_exam_file"]=$standard->recycle_exam_file?:'';
								$stds["social_course_exam_file"]=$standard->social_course_exam_file?:'';
								$stds["qua_exam_file"]=$standard->qua_exam_file?:'';
								$stds["witness_file"]=$standard->witness_file?:'';
								$standardArray[]=$stds;

								


								if($standard->approval_status !='0'){
									$standardIdExcept[]=$standard->standard_id;
								}

								if($standard->approval_status =='3'){
									$stds["approval_comment"]=$standard->approval_comment?:'';
									$stds["approval_date"]=($standard->approval_date !='0000-00-00' && $standard->approval_date !=null && $standard->approval_date!='1970-01-01')?date($date_format,$standard->approval_date):'';
									$stds["approval_by_name"]=$standard->approvaluser?$standard->approvaluser->first_name.' '.$standard->approvaluser->last_name:'';
									$resultarr["standard_rejected"][] = $stds;
								}else if($standard->approval_status =='1'){
									$resultarr["standard_approvalwaiting"][] = $stds;
								}else if($standard->approval_status =='2'){

									$stds["approval_comment"]=$standard->approval_comment?:'';
									$stds["approval_date"]=($standard->approval_date !='0000-00-00' && $standard->approval_date !=null && $standard->approval_date!='1970-01-01')?date($date_format,$standard->approval_date):'';
									$stds["approval_by_name"]=$standard->approvaluser?$standard->approvaluser->first_name.' '.$standard->approvaluser->last_name:'';

									$resultarr["standard_approved"][] = $stds;
								}else if($standard->approval_status =='0'){
									$resultarr["standard"][] = $stds;
								}

								$standardChkNameArray[$standard->standard_id] = $standard->standard->name;
							}
						}




						$responsedata=array('status'=>1,'message'=>'Standards updated successfully','user_id'=>$model->id,'resultarr'=>$resultarr);
					}else{
						$responsedata=array('status'=>0,'message'=>'failed');
					}

				}else if($data['actiontype'] =='rejectionstandards'){
					if(!$this->canDoUpdateAccess($update_user_id,['add_edit_user_standards_business_sectors'])){
						return false;
					}
					//&& is_array($data['process']) && count($data['standard'])>0  
					if($data['standard'] && $data['standard']!='' && $data['standard']>0)
					{
						
						$stdmodel = UserStandard::find()->where(['user_id'=>$model->id,'standard_id'=>$data['standard']])->one();

						$stdhistorymodel = new UserStandardHistory;
						$stdhistorymodel->user_standard_id = $stdmodel->id;
						$stdhistorymodel->user_id = $stdmodel->user_id;
						$stdhistorymodel->standard_id = $stdmodel->standard_id;
						$stdhistorymodel->standard_exam_date = $stdmodel->standard_exam_date;
						$stdhistorymodel->standard_exam_file = $stdmodel->standard_exam_file;
						$stdhistorymodel->recycle_exam_date = ($stdmodel->recycle_exam_date !==NULL)?$stdmodel->recycle_exam_date:'';
						$stdhistorymodel->social_course_exam_date = ($stdmodel->social_course_exam_date !==NULL)?$stdmodel->social_course_exam_date:''; 
						$stdhistorymodel->witness_date = ($stdmodel->witness_date !==NULL && $stdmodel->witness_date != '0000-00-00')?$stdmodel->witness_date:'';
						$stdhistorymodel->witness_valid_until=($stdmodel->witness_valid_until !==NULL && $stdmodel->witness_valid_until != '0000-00-00')?$stdmodel->witness_valid_until:'';
						$stdhistorymodel->recycle_exam_file = $stdmodel->recycle_exam_file?:'';
						$stdhistorymodel->social_course_exam_file = $stdmodel->social_course_exam_file?:'';
						$stdhistorymodel->witness_file = $stdmodel->witness_file?:'';
						$stdhistorymodel->witness_comment = $stdmodel->witness_comment?:'';
						$stdhistorymodel->approval_comment = $stdmodel->approval_comment?:'';
						$stdhistorymodel->approval_by = $stdmodel->approval_by?:'';
						$stdhistorymodel->approval_date = ($stdmodel->approval_date !==NULL)?$stdmodel->approval_date:'';
						$stdhistorymodel->approval_status= 1;
						$stdhistorymodel->created_on= time();
						$stdhistorymodel->save();

						$stdmodel->approval_status= 1;
						$stdmodel->standard_exam_date=date('Y-m-d',strtotime($data['std_exam_date']));
						$stdmodel->pre_qualification = $data['pre_qualification'];

						if($data['recycle_exam_date'] !==''){
							$stdmodel->recycle_exam_date=date('Y-m-d',strtotime($data['recycle_exam_date']));
						}
						if($data['social_exam_date'] !==''){
							$stdmodel->social_course_exam_date=date('Y-m-d',strtotime($data['social_exam_date']));
						}
						if($data['witness_date'] !==''){
							$stdmodel->witness_date=date('Y-m-d',strtotime($data['witness_date']));

							if(isset($_FILES['witness_file']['name']))
							{	
								$tmp_name = $_FILES["witness_file"]["tmp_name"];
								$name = $_FILES["witness_file"]["name"];
								if($stdmodel->witness_file != ''){
									Yii::$app->globalfuns->removeFiles($stdmodel->witness_file,$target_dir);
								}   
								$stdmodel->witness_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
								/*
								$filename = $_FILES['witness_file']['name'];
								$target_file = $target_dir . basename($filename);
								$target_file = $target_dir . basename($filename);
								$actual_name = pathinfo($filename,PATHINFO_FILENAME);
								$original_name = $actual_name;
								$extension = pathinfo($filename, PATHINFO_EXTENSION);
								$i = 1;
								$name = $actual_name.".".$extension;
								while(file_exists($target_dir.$actual_name.".".$extension))
								{           
									$actual_name = (string)$original_name.$i;
									$name = $actual_name.".".$extension;
									$i++;
								}
								if (move_uploaded_file($_FILES['witness_file']["tmp_name"], $target_dir .$actual_name.".".$extension)) {
									$stdmodel->witness_file=isset($name)?$name:"";
								}
								*/
							}
						}
						
						

						if(isset($_FILES['std_exam_file']['name']))
						{
							if($stdmodel->standard_exam_file != ''){
								Yii::$app->globalfuns->removeFiles($stdmodel->standard_exam_file,$target_dir);
							}

							$tmp_name = $_FILES["std_exam_file"]["tmp_name"];
				   			$name = $_FILES["std_exam_file"]["name"];
							$stdmodel->standard_exam_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);	
							/*
							$filename = $_FILES['std_exam_file']['name'];
							$target_file = $target_dir . basename($filename);
							$target_file = $target_dir . basename($filename);
							$actual_name = pathinfo($filename,PATHINFO_FILENAME);
							$original_name = $actual_name;
							$extension = pathinfo($filename, PATHINFO_EXTENSION);
							$i = 1;
							$name = $actual_name.".".$extension;
							while(file_exists($target_dir.$actual_name.".".$extension))
							{           
								$actual_name = (string)$original_name.$i;
								$name = $actual_name.".".$extension;
								$i++;
							}
							if (move_uploaded_file($_FILES['std_exam_file']["tmp_name"], $target_dir .$actual_name.".".$extension)) {
								$stdmodel->standard_exam_file=isset($name)?$name:"";
							}
							*/
						}


						if(isset($_FILES['recycle_exam_file']['name']))
						{	
							if($stdmodel->recycle_exam_file != ''){
								Yii::$app->globalfuns->removeFiles($stdmodel->recycle_exam_file,$target_dir);
							}

							$tmp_name = $_FILES["recycle_exam_file"]["tmp_name"];
				   			$name = $_FILES["recycle_exam_file"]["name"];
							$stdmodel->recycle_exam_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
							/*
							$filename = $_FILES['recycle_exam_file']['name'];
							$target_file = $target_dir . basename($filename);
							$target_file = $target_dir . basename($filename);
							$actual_name = pathinfo($filename,PATHINFO_FILENAME);
							$original_name = $actual_name;
							$extension = pathinfo($filename, PATHINFO_EXTENSION);
							$i = 1;
							$name = $actual_name.".".$extension;
							while(file_exists($target_dir.$actual_name.".".$extension))
							{           
								$actual_name = (string)$original_name.$i;
								$name = $actual_name.".".$extension;
								$i++;
							}
							if (move_uploaded_file($_FILES['recycle_exam_file']["tmp_name"], $target_dir .$actual_name.".".$extension)) {
								$stdmodel->recycle_exam_file=isset($name)?$name:"";
							}
							*/
						}


						if(isset($_FILES['social_exam_file']['name']))
						{	
							if($stdmodel->social_course_exam_file != ''){
								Yii::$app->globalfuns->removeFiles($stdmodel->social_course_exam_file,$target_dir);
							}
							$tmp_name = $_FILES["social_exam_file"]["tmp_name"];
				   			$name = $_FILES["social_exam_file"]["name"];
							$stdmodel->social_course_exam_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
							/*
							$filename = $_FILES['social_exam_file']['name'];
							$target_file = $target_dir . basename($filename);
							$target_file = $target_dir . basename($filename);
							$actual_name = pathinfo($filename,PATHINFO_FILENAME);
							$original_name = $actual_name;
							$extension = pathinfo($filename, PATHINFO_EXTENSION);
							$i = 1;
							$name = $actual_name.".".$extension;
							while(file_exists($target_dir.$actual_name.".".$extension))
							{           
								$actual_name = (string)$original_name.$i;
								$name = $actual_name.".".$extension;
								$i++;
							}
							if (move_uploaded_file($_FILES['social_exam_file']["tmp_name"], $target_dir .$actual_name.".".$extension)) {
								$stdmodel->social_course_exam_file=isset($name)?$name:"";
							}
							*/
						}
						if(isset($_FILES['qua_exam_file']['name']))
						{	
							if($stdmodel->social_course_exam_file != ''){
								Yii::$app->globalfuns->removeFiles($stdmodel->qua_exam_file,$target_dir);
							}
							$tmp_name = $_FILES["qua_exam_file"]["tmp_name"];
				   			$name = $_FILES["qua_exam_file"]["name"];
							$stdmodel->qua_exam_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
						}

						$stdmodel->save();
						
						$standard_approvalwaitingarr = [];
						$standardmodel = UserStandard::find()->where(['user_id' => $data['id'],'approval_status'=>1])->all();
						if(count($standardmodel)>0)
						{
							foreach($standardmodel as $standard)
							{
								$stds=[];
								$stds["id"]=$standard->id;
								$stds["standard"]=$standard->standard_id;
								$stds["standard_name"]=$standard->standard->name;
								$stds["standard_code"]=$standard->standard->code;
								$stds["pre_qualification"]=$standard->pre_qualification;
								$stds["qua_exam_file"]=$standard->qua_exam_file;
								$stds["standard_exam_date"]=date($date_format,strtotime($standard->standard_exam_date));
								$stds["recycle_exam_date"]=($standard->recycle_exam_date !='0000-00-00' && $standard->recycle_exam_date !=null && $standard->recycle_exam_date!='1970-01-01')?date($date_format,strtotime($standard->recycle_exam_date)):'';
								$stds["social_course_exam_date"]=($standard->social_course_exam_date !='0000-00-00' && $standard->social_course_exam_date !=null && $standard->social_course_exam_date!='1970-01-01')?date($date_format,strtotime($standard->social_course_exam_date)):'';
								$stds["witness_date"]=($standard->witness_date !='0000-00-00' && $standard->witness_date !=null && $standard->witness_date!='1970-01-01')?date($date_format,strtotime($standard->witness_date)):'';
								$stds["witness_valid_until"]=($standard->witness_valid_until !='0000-00-00' && $standard->witness_valid_until !=null && $standard->witness_valid_until!='1970-01-01')?date($date_format,strtotime($standard->witness_valid_until)):'';
								$stds["witness_comment"]=$standard->witness_comment?:'';
								
								$stds["standard_exam_file"]=$standard->standard_exam_file?:'';
								$stds["recycle_exam_file"]=$standard->recycle_exam_file?:'';
								$stds["social_course_exam_file"]=$standard->social_course_exam_file?:'';
								$stds["witness_file"]=$standard->witness_file?:'';
								//$standardArray[]=$stds;

								
								$standard_approvalwaitingarr[] = $stds;
								
							}
						}
						$responsedata=array('status'=>1,'message'=>'Standards updated successfully','user_id'=>$model->id,'standard_approvalwaiting'=>$standard_approvalwaitingarr);
					}else{
						$responsedata=array('status'=>0,'message'=>'failed');
					}

				}else if( $data['actiontype'] == 'mapuserrole'){
					if(!$this->canDoUpdateAccess($update_user_id,['add_edit_map_group_user_role'])){
						return false;
					}
					if(is_array($data['mapuserrole']) && count($data['mapuserrole'])>0)
					{
						//UserCertification::deleteAll(['user_id' => $data['id']]);
						foreach ($data['mapuserrole'] as $key => $value)
						{	
							if(isset($value['id']) && $value['id'] >0 ){
								$certmodel=UserRoleBusinessGroup::find()->where(['id'=>$value['id']])->one();
								if($certmodel === null ){
									$certmodel=new UserRoleBusinessGroup();
								}
							}else{
								$certmodel=new UserRoleBusinessGroup();
							}


							$target_dir = Yii::$app->params['user_files']; 
							$filename = '';
							if(isset($_FILES['documents']['name']))
							{
								$tmp_name = $_FILES["documents"]["tmp_name"];
					   			$name = $_FILES["documents"]["name"];
								$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
								if($certmodel->document != ''){
									Yii::$app->globalfuns->removeFiles($certmodel->document,$target_dir);
								}
							}
							else
							{
								$filename = $value['document_file'];
							}

							
							$certmodel->user_id=$model->id;
							$certmodel->role_id=$value['role_id'];
							$certmodel->standard_id=$value['standard_id'];
							$certmodel->business_sector_id=$value['business_sector_id'];
							$certmodel->document=$filename;
							$certmodel->created_by=$userData['userid'];
							$certmodel->created_at=time();
							$certmodel->status=0;
							if($certmodel->save()){
								UserRoleBusinessGroupCode::deleteAll(['business_group_id' => $certmodel->id ]);
								$business_sector_group_ids = $value['business_sector_group_id'];
								if(count($business_sector_group_ids)>0){
									foreach($business_sector_group_ids as $business_sector_group_id){
										
										$user_business_group_code_id = 0;
										$UserBusinessGroupCode=UserBusinessGroupCode::find()->alias('t')->innerJoinWith('usersector as usersector')->where(['usersector.user_id' => $data['id'],'t.business_sector_group_id'=>$business_sector_group_id ])->andWhere(['t.status' => 2])->one();
										if($UserBusinessGroupCode !== null){
											$user_business_group_code_id = $UserBusinessGroupCode->id;
										}
										$certcodemodel=new UserRoleBusinessGroupCode();
										$certcodemodel->business_group_id = $certmodel->id;
										$certcodemodel->user_business_group_code_id = $user_business_group_code_id;
										$certcodemodel->business_sector_group_id = $business_sector_group_id;
										$certcodemodel->status = 0;
										$certcodemodel->save();
									}
								}
								
							}


						}
					}
					 
					$responsedata=array('status'=>1,'message'=>'Saved successfully','user_id'=>$model->id);
				}else if( $data['actiontype'] == 'certificate'){
					if(!$this->canDoUpdateAccess($update_user_id,['add_edit_user_cpd']) && !($user_type==1 && $userid==$update_user_id)){
						return false;
					}
					if(is_array($data['certifications']) && count($data['certifications'])>0)
					{
						//UserCertification::deleteAll(['user_id' => $data['id']]);
						foreach ($data['certifications'] as $key => $value)
						{	
							$file = '';
							
							$editStatus = 0;
							if(isset($value['id']) && $value['id'] >0 ){
								$certmodel=UserCertification::find()->where(['id'=>$value['id']])->one();
								$editStatus = 1;
								if($certmodel === null ){
									$certmodel=new UserCertification();
									$editStatus = 0;
								}
							}else{
								$certmodel=new UserCertification();
								$editStatus = 0;
							}
							if(isset($_FILES['uploads']['name']))
							{
								$tmp_name = $_FILES["uploads"]["tmp_name"];
					   			$name = $_FILES["uploads"]["name"];
								$file =Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
								if($editStatus){
									Yii::$app->globalfuns->removeFiles($certmodel->filename,$target_dir);
								}
							}else{
								$file = $value['filename'];
							}
							
							$certmodel->user_id=$model->id;
							$certmodel->certification_name=$value['certificate_name'];
							$certmodel->training_hours=$value['training_hours'];
							$certmodel->completed_date=date('Y-m-d', strtotime($value['completed_date']));
							$certmodel->filename=$file;
							$certmodel->save();


						}
					}
					/*
					$Certificationmodel = UserCertification::find()->select('id,certification_name,completed_date,filename,training_hours')->where(['user_id' => $data['id']])->orderBy(['id'=>SORT_DESC])->all();
					if(count($Certificationmodel)>0)
					{
						foreach($Certificationmodel as $certification)
						{
							$certificationArray=array();
							$certificationArray['certificate_name']=$certification->certification_name;
							$certificationArray['training_hours']=$certification->training_hours;
							$certificationArray['completed_date']=date($date_format,strtotime($certification->completed_date));
							$certificationArray['filename']=$certification->filename;
							$certificationArray['id']=$certification->id;
						  	$resultarr["certifications"][]=$certificationArray;
						}
					}else{
						$resultarr["certifications"] = [];
					}

					,'resultarr'=>$resultarr
					*/


					$responsedata=array('status'=>1,'message'=>'Certifications updated successfully','user_id'=>$model->id);
				}else if($data['actiontype'] =='role'){
					//UserTrainingInfo::deleteAll(['user_id' => $data['id']]);
					$resultarr["role"] = [];
					
					
					if(is_array($data['roles']) && count($data['roles'])>0)
					{

						$deleted_roles=array();
						$edited_roles=array();
						foreach ($data['roles'] as $value)
						{	
							if(isset($value['deleted']) && $value['deleted']==1){
								$deleted=array();
								UserRole::findOne($value['user_role_id'])->delete();
								$deleted['role_id']=$value['role_id'];
								$deleted['franchise_id']=$value['franchise_id'];
								$deleted_roles[]=$deleted;

							}else if($value['editable']==1){
								$edited=array();
								$rolemodel=new UserRole();

								//$rolemodel->setPassword($value['user_password']);

								$rolemodel->user_id=$model->id;
								$rolemodel->role_id=$value['role_id'];
								$rolemodel->franchise_id=$value['franchise_id'];
								$rolemodel->status=0;
								$rolemodel->approval_status=0;
								//$rolemodel->approval_status=0;
								$rolemodel->login_status=1;
								$rolemodel->created_by=$userData['userid'];
								//$rolemodel->username=$value['username'];
								$rolemodel->approval_comment='';
								//$rolemodel->password_hash= $value['user_password'];
								$rolemodel->save();
								//print_r($rolemodel->getErrors());
								$edited['role_id']=$value['role_id'];
								$edited['franchise_id']=$value['franchise_id'];
								//$edited['username']=$value['username'];
								//$edited['user_password']=$value['user_password'];
								$edited_roles[]=$edited;
							}
							//print_r($value); die;
							//die;
						}

						$userfranchiseid = UserRole::find()->select('franchise_id')->where(['id' => $model->id])->one();
						$HQmodel = User::find()->select('headquarters')->where(['id' => $userfranchiseid['franchise_id']])->one();

						if(count($edited_roles)>0)
						{
							
							foreach($edited_roles as $val)
							{
								
								$role= Role::find()->select('role_name')->where(['id' => $val['role_id']])->one();
								$franchise= UserCompanyInfo::find()->select('company_name,company_email')->where(['user_id' => $val['franchise_id']])->one();
								
								/*
								$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'add_role'])->one();

								if($mailContent !== null && $role!== null && $franchise!== null)
								{
									$mailmsg=str_replace('{USERNAME}', "User", $mailContent['message'] );
									$mailmsg=str_replace('{role}', $role['role_name'], $mailmsg );
									$mailmsg=str_replace('{franchise}', $franchise['company_name'], $mailmsg );
									$mailmsg=str_replace('{username}', $val['username'], $mailmsg );
									$mailmsg=str_replace('{password}', $val['user_password'], $mailmsg );
									
									$MailLookupModel = new MailLookup();
									$MailLookupModel->to=$model->email;
									$MailLookupModel->subject=$mailContent['subject'];
									$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
									$MailLookupModel->attachment='';
									$MailLookupModel->mail_notification_id='';
									$MailLookupModel->mail_notification_code='';
									$Mailres=$MailLookupModel->sendMail();
								}
								*/
								
								if($HQmodel['headquarters']!=1)
								{
									$FranchiseMailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'user_add_role_to_franchise'])->one();

									if($FranchiseMailContent !== null && $role!== null && $franchise!== null)
									{
										$mailmsg=str_replace('{USERNAME}', "OSS", $FranchiseMailContent['message'] );
										$mailmsg=str_replace('{ROLE}', $role['role_name'], $mailmsg );

										$MailLookupModel = new MailLookup();
										$MailLookupModel->to=$franchise['company_email'];										
										$MailLookupModel->subject=$FranchiseMailContent['subject'];
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
						if(count($deleted_roles)>0)
						{
							
							foreach($deleted_roles as $value)
							{
								$role= Role::find()->select('role_name')->where(['id' => $value['role_id']])->one();
								$franchise= UserCompanyInfo::find()->select('company_name')->where(['user_id' => $value['franchise_id']])->one();
								$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'delete_role'])->one();

								if($mailContent !== null && $role!== null && $franchise!== null)
								{
									$mailmsg=str_replace('{USERNAME}', "User", $mailContent['message'] );
									$mailmsg=str_replace('{role}', $role['role_name'], $mailmsg );
									$mailmsg=str_replace('{franchise}', $franchise['company_name'], $mailmsg );
									
									$MailLookupModel = new MailLookup();
									$MailLookupModel->to=$model->email;									
									$MailLookupModel->subject=$mailContent['subject'];
									$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
									$MailLookupModel->attachment='';
									$MailLookupModel->mail_notification_id='';
									$MailLookupModel->mail_notification_code='';
									$Mailres=$MailLookupModel->sendMail();
								}

								if($HQmodel['headquarters']!=1)
								{
									$FranchiseMailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'user_delete_role_to_franchise'])->one();

									if($FranchiseMailContent !== null && $role!== null && $franchise!== null)
									{
										$mailmsg=str_replace('{USERNAME}', "OSS", $FranchiseMailContent['message'] );
										$mailmsg=str_replace('{ROLE}', $role['role_name'], $mailmsg );

										$MailLookupModel = new MailLookup();
										$MailLookupModel->to=$franchise['company_email'];										
										$MailLookupModel->subject=$FranchiseMailContent['subject'];
										$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
										$MailLookupModel->attachment='';
										$MailLookupModel->mail_notification_id='';
										$MailLookupModel->mail_notification_code='';
										$Mailres=$MailLookupModel->sendMail();
									}
								}
								
							}
							
						}
						*/
						
						$rolemodel = UserRole::find()->where(['user_id' => $data['id'],'approval_status'=>0])->all();
						if(count($rolemodel)>0)
						{
							foreach($rolemodel as $role)
							{

								$roledtArray=array();
								$roledtArray['user_role_id']=$role->id;
								$roledtArray['role_id']=$role->role_id;
								$roledtArray['role_name']=$role->role->role_name;
								$roledtArray['username']=$role->username;
								$roledtArray['from_db']=1;
								$roledtArray['deleted']=0;
								$roledtArray['editable']=0;
								$roledtArray['franchise_name']= 'OSS '.$role->franchise->usercompanyinfo->osp_number.' - '.$role->franchise->usercompanyinfo->osp_details;//$role->franchise->usercompanyinfo->company_name.' ('.$role->franchise->usercompanyinfo->companycountry->name.')';
								$roledtArray['franchise_id']= $role->franchise->id;
								$resultarr["role"][] = $roledtArray;
							}
						}
						
					}


					$is_auditor =0;
					$connection = Yii::$app->getDb();
					$command = $connection->createCommand("SELECT user_role.user_id FROM tbl_user_role as user_role 
						INNER JOIN `tbl_rule` AS rule ON  user_role.role_id=rule.role_id AND rule.privilege='audit_execution' 
								where user_role.user_id=".$data['id']);
					$result = $command->queryAll();
					if(count($result)>0){
						$is_auditor = 1;
					}


					
					$responsedata=array('status'=>1,'role'=>$resultarr["role"],'message'=>'Roles added/updated successfully','user_id'=>$model->id,'is_auditor'=>$is_auditor);
				}else if($data['actiontype'] =='rejrole'){
					//UserTrainingInfo::deleteAll(['user_id' => $data['id']]);
					/*
					$resultarr["role"] = [];
					
					
					if(is_array($data['roles']) && count($data['roles'])>0)
					{
						$deleted_roles=array();
						$edited_roles=array();
						foreach ($data['roles'] as $value)
						{	
							if($value['deleted']==1){
								$deleted=array();
								UserRole::findOne($value['user_role_id'])->delete();
								$deleted['role_id']=$value['role_id'];
								$deleted['franchise_id']=$value['franchise_id'];
								$deleted_roles[]=$deleted;

							}
						}

					
					}
					*/
					$rolemodel=UserRole::find()->where(['id' => $data['user_role_id']])->andWhere(['approval_status' => 3])->one();
					if($rolemodel !='')
					{
						$rolemodel->approval_status = 1;
						$rolemodel->approval_by = $userid;
						$rolemodel->approval_date = time();
						$rolemodel->save();
						
						//$responsedata=array('status'=>1,'message'=>'Sent for Approval Successfully');
					}
					$responsedata=array('status'=>1,'message'=>'Roles submitted for approval successfully','user_id'=>$model->id);
				}				
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>'failed');
			}
		}
		return $this->asJson($responsedata);
	}


	public function actionUpdateStdfiles()
	{
		$userData = Yii::$app->userdata->getData();
		$user_type=$userData['user_type'];
		if(!Yii::$app->userrole->hasRights(array('add_edit_user_standards_business_sectors')) && $user_type!=3)
		{
			return false;
		}
		
		$model = new User();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$datapost = Yii::$app->request->post();
		
		$data = json_decode($datapost['formvalues'],true);
		$target_dir = Yii::$app->params['user_files'];
		$date_format = Yii::$app->globalfuns->getSettings('date_format');		
		if ($data) 
		{
			
			$stdmodel = UserStandard::find()->where(['id'=>$data['id']])->one();

			if ($stdmodel!==null) 
			{
				$userid = $stdmodel->user_id;
				if(isset($_FILES['witness_file']['name']))
				{	
					if($stdmodel->witness_file != ''){
						Yii::$app->globalfuns->removeFiles($stdmodel->witness_file,$target_dir);
					}
					$tmp_name = $_FILES["witness_file"]["tmp_name"];
					$name = $_FILES["witness_file"]["name"];
					$stdmodel->witness_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);	
				}

				$stdmodel->witness_date = date("Y-m-d",strtotime($data['witness_date']));
				$stdmodel->witness_valid_until = date("Y-m-d",strtotime($data['valid_until']));
				$stdmodel->approval_date = strtotime($data['approval_date']);
				$stdmodel->pre_qualification = $data['pre_qualification'];

				if(isset($_FILES['std_exam_file']['name']))
				{	
					if($stdmodel->standard_exam_file != ''){
						Yii::$app->globalfuns->removeFiles($stdmodel->standard_exam_file,$target_dir);
					}
					$tmp_name = $_FILES["std_exam_file"]["tmp_name"];
					$name = $_FILES["std_exam_file"]["name"];
					$stdmodel->standard_exam_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
				}


				if(isset($_FILES['recycle_exam_file']['name']))
				{	
					if($stdmodel->recycle_exam_file != ''){
						Yii::$app->globalfuns->removeFiles($stdmodel->recycle_exam_file,$target_dir);
					}
					$tmp_name = $_FILES["recycle_exam_file"]["tmp_name"];
					$name = $_FILES["recycle_exam_file"]["name"];
					$stdmodel->recycle_exam_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
				}


				if(isset($_FILES['social_exam_file']['name']))
				{	
					if($stdmodel->social_course_exam_file != ''){
						Yii::$app->globalfuns->removeFiles($stdmodel->social_course_exam_file,$target_dir);
					}
					$tmp_name = $_FILES["social_exam_file"]["tmp_name"];
					$name = $_FILES["social_exam_file"]["name"];
					$stdmodel->social_course_exam_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
					
				}
					
				if(isset($_FILES['qua_exam_file']['name']))
				{	
					if($stdmodel->qua_exam_file != ''){
						Yii::$app->globalfuns->removeFiles($stdmodel->qua_exam_file,$target_dir);
					}
					$tmp_name = $_FILES["qua_exam_file"]["tmp_name"];
					$name = $_FILES["qua_exam_file"]["name"];
					$stdmodel->qua_exam_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
					
				}
				if($stdmodel->save())
				{
					$responsedata=array('status'=>1,'message'=>'Files Updated Successfully.','user_id'=>$userid);	
				}	
			}
		}
		return $this->asJson($responsedata);	
	}

	public function actionUpdateBgroupdate()
	{
		$userData = Yii::$app->userdata->getData();
		$user_type=$userData['user_type'];
		if(!Yii::$app->userrole->hasRights(array('add_edit_user_business_group')) && $user_type!=3)
		{
			return false;
		}
		
		$model = new User();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		
		if ($data) 
		{
			$bgroupcodemodel=UserBusinessGroupCode::find()->where(['id'=>$data['id']])->one();
			if($bgroupcodemodel !== null )
			{
				$bgroupcodemodel->approval_date = date("Y-m-d",strtotime($data['approval_date']));
				if($bgroupcodemodel->validate() && $bgroupcodemodel->save())
				{
					$responsedata=array('status'=>1,'message'=>'Approval Date Updated Successfully.');
				}
			}
		}
		return $this->asJson($responsedata);
	}

	public function actionUpdateBgroupfiles()
	{
		$userData = Yii::$app->userdata->getData();
		$user_type=$userData['user_type'];
		if(!Yii::$app->userrole->hasRights(array('add_edit_user_business_group')) && $user_type!=3)
		{
			return false;
		}
		
		$model = new User();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$datapost = Yii::$app->request->post();
		
		$data = json_decode($datapost['formvalues'],true);
		$target_dir = Yii::$app->params['user_files'];
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		if ($data) 
		{
			$bgroupmodel=UserBusinessGroup::find()->where(['id'=>$data['id']])->one();
			if($bgroupmodel !== null )
			{
				$userid = $bgroupmodel->user_id;
				if(isset($_FILES['examFileNames']['name']))
				{
					if($bgroupmodel->exam_file != ''){
						Yii::$app->globalfuns->removeFiles($bgroupmodel->exam_file,$target_dir);
					}
					$tmp_name = $_FILES["examFileNames"]["tmp_name"];
					$name = $_FILES["examFileNames"]["name"];
					$bgroupmodel->exam_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
				}

				if(isset($_FILES['technicalInterviewFileNames']['name']))
				{
					if($bgroupmodel->technical_interview_file != ''){
						Yii::$app->globalfuns->removeFiles($bgroupmodel->technical_interview_file,$target_dir);
					}
					$tmp_name = $_FILES["technicalInterviewFileNames"]["tmp_name"];
					$name = $_FILES["technicalInterviewFileNames"]["name"];
					$bgroupmodel->technical_interview_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
				}


				if($bgroupmodel->validate() && $bgroupmodel->save())
				{
					$responsedata=array('status'=>1,'message'=>'Files Updated Successfully.','user_id'=>$userid);	
				}	
			}
		}						
		return $this->asJson($responsedata);					
								
	}

	public function actionFetchStdDetails()
	{
		$result=array();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$data=Yii::$app->request->post();
		if ($data) 
		{
			$standardArray = [];
			$standardChkNameArray = [];
			$standardmodel = UserStandard::find()->where(['user_id' => $data['id']])->all();
			if(count($standardmodel)>0)
			{
				foreach($standardmodel as $standard)
				{
					$stds=[];
					$stds["id"]=$standard->id;
					$stds["standard"]=$standard->standard_id;
					$stds["standard_name"]=$standard->standard->name;
					$stds["standard_code"]=$standard->standard->code;
					$stds["standard_exam_date"]=date($date_format,strtotime($standard->standard_exam_date));
					$stds["recycle_exam_date"]=($standard->recycle_exam_date !='0000-00-00' && $standard->recycle_exam_date !=null && $standard->recycle_exam_date!='1970-01-01')?date($date_format,strtotime($standard->recycle_exam_date)):'';
					$stds["social_course_exam_date"]=($standard->social_course_exam_date !='0000-00-00' && $standard->social_course_exam_date !=null && $standard->social_course_exam_date!='1970-01-01')?date($date_format,strtotime($standard->social_course_exam_date)):'';
					$stds["witness_date"]=($standard->witness_date !='0000-00-00' && $standard->witness_date !=null && $standard->witness_date!='1970-01-01')?date($date_format,strtotime($standard->witness_date)):'';
					$stds["witness_valid_until"]=($standard->witness_valid_until !='0000-00-00' && $standard->witness_valid_until !=null && $standard->witness_valid_until!='1970-01-01')?date($date_format,strtotime($standard->witness_valid_until)):'';
					$stds["witness_comment"]=$standard->witness_comment?:'';
					
					$stds["standard_exam_file"]=$standard->standard_exam_file?:'';
					$stds["recycle_exam_file"]=$standard->recycle_exam_file?:'';
					$stds["social_course_exam_file"]=$standard->social_course_exam_file?:'';
					$stds["witness_file"]=$standard->witness_file?:'';
					$stds["status"]=$standard->approval_status;
					$stds["approval_comment"]=$standard->approval_comment?:'';
					$stds["approval_date"]=($standard->approval_date !='0000-00-00' && $standard->approval_date !=null && $standard->approval_date!='1970-01-01')?date($date_format,$standard->approval_date):'';
					$stds["approval_by_name"]=$standard->approvaluser?$standard->approvaluser->first_name.' '.$standard->approvaluser->last_name:'';

					
					$standardArray[]=$stds;

					if($standard->approval_status =='3'){
						$resultarr["standard_rejected"][] = $stds;
					}else if($standard->approval_status =='1'){
						$resultarr["standard_approvalwaiting"][] = $stds;
					}else if($standard->approval_status =='2'){
						$resultarr["standard_approved"][] = $stds;
					}else if($standard->approval_status =='0'){
						$resultarr["standard"][] = $stds;
					}

					$standardChkNameArray[$standard->standard_id] = $standard->standard->name;
				}
				return $resultarr;//$responsedata["standard_approved"] = $standardChkNameArray;
			}
		}
	}

	public function actionFetchUser()
	{
		$result=array();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$data=Yii::$app->request->post();
		if ($data) 
		{
			$data['type']='1';
						
			$Usermodel = User::find()->select('id,passport_file,contract_file,first_name,last_name,email,telephone,country_id,state_id,date_of_birth,user_type,status,created_at,updated_at,created_by')->where(['id' => $data['id']])->one();

			
			if ($Usermodel !== null)
			{
				$resultarr=array();

				
				$is_auditor = 0;
				$connection = Yii::$app->getDb();
				$command = $connection->createCommand("SELECT user_role.user_id FROM tbl_user_role as user_role 
					INNER JOIN `tbl_rule` AS rule ON  user_role.role_id=rule.role_id AND rule.privilege='audit_execution' 
							where user_role.user_id=".$Usermodel->id);
				$result = $command->queryAll();
				if(count($result)>0){
					$is_auditor = 1;
				}

				
				$resultarr["is_auditor"] = $is_auditor;
				$Usermodel->country_id=$Usermodel->country_id;
				$Usermodel->state_id=$Usermodel->state_id;
				
				
				//if(count($Usermodel)>0)
				//{
				foreach($Usermodel as $key => $value)
				{
					$resultarr[$key]=$value;
				}
				$resultarr['country_name']=($Usermodel->country)?$Usermodel->country->name:'';
				$resultarr['state_name']=($Usermodel->state)?$Usermodel->state->name:'';
				$resultarr['temp_valid_until'] = date($date_format, strtotime('+3 years'));
				
				$resultarr['created_at']=date($date_format,$Usermodel->created_at);
				$resultarr['created_by']=$Usermodel->username->first_name.' '.$Usermodel->username->last_name;
				//}
				
				//if($resultarr['is_auditor']=='1')
				//{
					//$Qualificationmodel = UserQualification::find()->select('qualification,board_university,subject,passing_year,percentage')->where(['user_id' => $data['id']])->asArray()->all();
					//$resultarr["qualifications"]=$Qualificationmodel;
					/*
					$processArray = [];
					$processNameArray = [];
					$processmodel = UserProcess::find()->select('process_id')->where(['user_id' => $data['id']])->all();
					if(count($processmodel)>0)
					{
						foreach($processmodel as $process)
						{
							$processArray[]=$process->process_id;
							$processNameArray[]=$process->process->name;
						}
					}
					$resultarr["process"] = $processArray;
					$resultarr["processDetails"] = $processNameArray;
					
					$roleArray=[];
					$roleNameArray=[];
					$rolemodel = UserRole::find()->select('role_id')->where(['user_id' => $data['id']])->all();
					if(count($rolemodel)>0)
					{
						foreach($rolemodel as $role)
						{
							$roleArray[]=$role->role_id;
							$roleNameArray[]=$role->role->role_name;
							$roleChkNameArray[$role->role_id]=$role->role->role_name;
						}
					}
					
					*/


					$fetchData = ['user_id'=>$data['id'],'actiontype'=>'role'];
					$userresultdata = $this->getUserDetails($fetchData);
					$resultarr["is_auditor"] = $userresultdata['is_auditor'];
					$resultarr["roleDetails"] = $userresultdata["roleDetails"];
					$resultarr["role_id_rejected"] = $userresultdata["role_id_rejected"];
					$resultarr["role_id_map_user"] = $userresultdata["role_id_map_user"];
					$resultarr["role_id_approved"] = $userresultdata["role_id_approved"];
					$resultarr["role_id_waiting_approval"] = $userresultdata["role_id_waiting_approval"];
					$resultarr["role_id"] = $userresultdata["role_id"];								
					
					//$resultarr['qualifications'] = $userresultdata['qualifications'];
					
					$businessGroupArray=[];
					$businessGroupNameArray=[];
					$resultarr["businessgroup_new"] = [];
					$resultarr["businessgroup_approvalwaiting"] = [];
					$resultarr["businessgroup_approved"] = [];
					$resultarr["businessgroup_rejected"] = [];

					$fetchData = ['user_id'=>$data['id'],'actiontype'=>'business_group'];
					$userresultdata = $this->getUserDetails($fetchData);
					
					$resultarr["businessgroup_new"] = $userresultdata["businessgroup_new"];
					$resultarr["businessgroup_approvalwaiting"] = $userresultdata["businessgroup_approvalwaiting"];
					$resultarr["businessgroup_approved"] = $userresultdata["businessgroup_approved"];
					$resultarr["businessgroup_rejected"] = $userresultdata["businessgroup_rejected"];

					//$businessGroupmodel = UserBusinessGroup::find()->where(['user_id' => $data['id']])->all();
					
					$resultarr["standard_rejected"] = [];
					$resultarr["standard_approvalwaiting"] = [];
					$resultarr["standard_approved"] = [];
					$resultarr["standard"] = [];

					$fetchData = ['user_id'=>$data['id'],'actiontype'=>'standard'];
					$userresultdata = $this->getUserDetails($fetchData);
					
					$resultarr["standard_rejected"] = $userresultdata["standard_rejected"];
					$resultarr["standard_approvalwaiting"] = $userresultdata["standard_approvalwaiting"];
					$resultarr["standard_approved"] = $userresultdata["standard_approved"];
					$resultarr["standard"] = $userresultdata["standard"];
					$resultarr["standardNewList"]=$userresultdata["standardNewList"];
					$standardChkNameArray = $userresultdata["standardChkNameArray"];					
					
					$declaration_new_arr = [];
					/*
					$resultarr["declaration_new"]=[];
					$resultarr["declaration_approvalwaiting"]=[];
					$resultarr["declaration_approved"]=[];
					$resultarr["declaration_rejected"]=[];
					*/
					$fetchData = ['user_id'=>$data['id'],'actiontype'=>'declaration'];
					$userresultdata = $this->getUserDetails($fetchData);
					//$declarationdata = $userresultdata['qualifications'];

					$resultarr["declaration_new"]=$userresultdata['declaration_new'];
					$resultarr["declaration_approvalwaiting"]=$userresultdata['declaration_approvalwaiting'];
					$resultarr["declaration_approved"]=$userresultdata['declaration_approved'];
					$resultarr["declaration_rejected"]=$userresultdata['declaration_rejected'];				

					$bsector_arr = [];
					$bsector_label_arr = [];
					$bsector_label_arr_names = [];
					$questbsectors = UserBusinessGroup::find()->where(['user_id' => $data['id']])->orderBy(['id'=>SORT_DESC])->all();
					if(count($questbsectors)>0)
					{
						$bsector_arr = array();
						$bsector_label_arr = array();
						foreach($questbsectors as $val)
						{
							$bsector_arr[]=$val['business_sector_id'];
							$bsector_label_arr[]=$val->businesssector->name;
							$bsector_label_arr_names[$val['business_sector_id']] = $val->businesssector->name;
							$bsectorgroup_arr= [];
							$bsectorgroup_label_arr= [];
							foreach($val->groupcode as $gcval)
							{
								$bsectorgroup_arr[]=$gcval->business_sector_group_id;
								$bsectorgroup_label_arr[]=$gcval->sectorgroup->group_code;
							}
							$resultarr["business_sector_group_id"]=$bsectorgroup_arr;
							$resultarr["bsectorgroup_label"]=$bsectorgroup_label_arr;

						}
						$resultarr["business_sector_id"]=$bsector_arr;
						$resultarr["bsector_label"]=$bsector_label_arr;
					}
					
					$displayarr=[];
					$qlistarr = [];
					$command = $connection->createCommand("SELECT group_concat(mgc.group_code) as group_codes,ug.standard_id,ug.business_sector_id,ugc.status FROM `tbl_user_business_group_code` as ugc inner join tbl_user_business_group as ug on ug.id=ugc.business_group_id inner join tbl_business_sector_group as mgc on mgc.id=ugc.business_sector_group_id where ug.user_id=".$data['id']."  group by ug.standard_id,ug.business_sector_id,ugc.status ");
					$businessGroupmodel = $command->queryAll();
					if(count($businessGroupmodel)>0)
					{
						foreach($businessGroupmodel as $qmodel){
							if($qmodel['status']==2){
								if(!isset($qlistarr[$qmodel['standard_id']][$qmodel['business_sector_id']][1])){
									$qlistarr[$qmodel['standard_id']][$qmodel['business_sector_id']][1] = [];
								}
								$qlistarr[$qmodel['standard_id']][$qmodel['business_sector_id']][1] =  $qlistarr[$qmodel['standard_id']][$qmodel['business_sector_id']][1] + explode(',',$qmodel['group_codes']);
								
							}else{
								if(!isset($qlistarr[$qmodel['standard_id']][$qmodel['business_sector_id']][0])){
									$qlistarr[$qmodel['standard_id']][$qmodel['business_sector_id']][0] = [];
								}
								$qlistarr[$qmodel['standard_id']][$qmodel['business_sector_id']][0]  = $qlistarr[$qmodel['standard_id']][$qmodel['business_sector_id']][0] + explode(',',$qmodel['group_codes']);
							}
							
						}
						

					}
					//print_r($qlistarr);
					foreach($qlistarr as $std=> $ql){
						foreach($ql as $sectorid=>$qlr){
							if(isset($qlr[1])){
								$displayarr[] = [
									'standard_name' => isset($standardChkNameArray[$std])?$standardChkNameArray[$std]:'',
									'qualification_status' =>  1,
									'sector_name' => isset($bsector_label_arr_names[$sectorid])?$bsector_label_arr_names[$sectorid]:'',
									'sector_group_names' => $qlr[1],
								];
							}
							if(isset($qlr[0])){
								$displayarr[] = [
									'standard_name' => isset($standardChkNameArray[$std])?$standardChkNameArray[$std]:'',
									'qualification_status' =>  0,
									'sector_name' => isset($bsector_label_arr_names[$sectorid])?$bsector_label_arr_names[$sectorid]:'',
									'sector_group_names' => $qlr[0],
								];
							}
						}
					}
					//print_r($qlistarr); die;
					$resultarr["qualificationReviewStatusArr"]=$displayarr;
					// print_r($displayarr); die;
					
									
				$fetchData = ['user_id'=>$data['id'],'actiontype'=>'qualification'];
				$userresultdata = $this->getUserDetails($fetchData);
				$resultarr['qualifications'] = $userresultdata['qualifications'];


				$fetchData = ['user_id'=>$data['id'],'actiontype'=>'experience'];
				$userresultdata = $this->getUserDetails($fetchData);
				$resultarr['experience'] = $userresultdata['experience'];


				$fetchData = ['user_id'=>$data['id'],'actiontype'=>'audit_experience'];
				$userresultdata = $this->getUserDetails($fetchData);
				$resultarr['audit_experience'] = $userresultdata['audit_experience'];
				$userAuditExperienceModel = new UserAuditExperience();
				$resultarr['auditrolelist']=$userAuditExperienceModel->arrAuditrolelist;   

				$auditstandard = $this->getAuditStandardList();
				$resultarr['auditstandardlist']= $auditstandard['auditstandardlist'];

				$listbusinesssec = $this->getListOfBusinessSector();
				$resultarr['listbusinesssector'] = $listbusinesssec['listbusinesssector'];

				$fetchData = ['user_id'=>$data['id'],'actiontype'=>'consultancy_experience'];
				$userresultdata = $this->getUserDetails($fetchData);
				$resultarr['consultancy_experience'] = $userresultdata['consultancy_experience'];
				
				$fetchData = ['user_id'=>$data['id'],'actiontype'=>'certificate'];
				$userresultdata = $this->getUserDetails($fetchData);
				$resultarr['certifications'] = $userresultdata['certifications'];


				// $fetchData = ['user_id'=>$data['id'],'actiontype'=>'cpd'];
				// $userresultdata = $this->getUserDetails($fetchData);
				// $resultarr['training_info'] = $userresultdata['training_info'];
				
				$fetchData = ['user_id'=>$data['id'],'actiontype'=>'mapuserrole'];
				$userresultdata = $this->getUserDetails($fetchData);
				$resultarr['mapuserrole'] = $userresultdata['mapuserrole'];
				

				//}
				$userDeclarationModel = new UserDeclaration();
				$resultarr["declaration_contract"]=$userDeclarationModel->arrContract;
				$resultarr["relationList"]=$userDeclarationModel->arrRelation;
				$resultarr['relAnsList']=$userDeclarationModel->arrAnsList;



				$fetchData = ['user_id'=>$data['id'],'actiontype'=>'te_business_group'];
				$userresultdata = $this->getUserDetails($fetchData);
				$resultarr['tebusinessgroup_new'] = $userresultdata['tebusinessgroup_new'];
				$resultarr['tebusinessgroup_approvalwaiting'] = $userresultdata['tebusinessgroup_approvalwaiting'];
				$resultarr['tebusinessgroup_approved'] = $userresultdata['tebusinessgroup_approved'];
				$resultarr['tebusinessgroup_rejected'] = $userresultdata['tebusinessgroup_rejected'];

				return ['data'=>$resultarr];
			}
			else
			{
				return $this->asJson($responsedata=array('status'=>0,'message'=>$Usermodel->getErrors()));
			}
		}	
	}
	
	public function actionFetchUserstdrole()
	{
		$result=array();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data=Yii::$app->request->post();
		if ($data) 
		{
			$data['type']='1';
						
			$Usermodel = User::find()->where(['id' => $data['userid']])->one();
			if ($Usermodel !== null)
			{
				/*
				$processArray = [];
				$processNameArray = [];
				$processmodel = UserProcess::find()->select('process_id')->where(['user_id' => $data['id']])->all();
				if(count($processmodel)>0)
				{
					foreach($processmodel as $process)
					{
						$processArray[]=$process->process_id;
						$processNameArray[]=$process->process->name;
					}
				}
				$resultarr["process"] = $processArray;
				$resultarr["processDetails"] = $processNameArray;
				*/
				$roleArray=[];
				$roleListArray=[];
				$rolemodel = UserRole::find()->select('role_id')->where(['user_id' => $data['userid']])->groupBy(['role_id'])->all();
				if(count($rolemodel)>0)
				{
					foreach($rolemodel as $role)
					{
						$roleArray['id']=$role->role_id;
						$roleArray['name']=$role->role->role_name;
						$roleListArray[]=$roleArray;
					}
				}
				


				$standardArray = [];
				$standardListArray = [];
				$standardmodel = UserStandard::find()->where(['user_id' => $data['userid']])->all();
				if(count($standardmodel)>0)
				{
					foreach($standardmodel as $standard)
					{
						$standardArray['id']=$standard->standard_id;
						$standardArray['name']=$standard->standard->name;
						$standardListArray[]=$standardArray;
					}
				}

				$processArray = [];
				$processListArray = [];
				$processmodel = UserProcess::find()->where(['user_id' => $data['userid']])->all();
				if(count($processmodel)>0)
				{
					foreach($processmodel as $process)
					{
						$processArray['id']=$process->process_id;
						$processArray['name']=$process->process->name;
						$processListArray[]=$processArray;
					}
				}

				$businesssectorArray=[];
				//$businesssectorArray=[];
				$sectormodel = UserBusinessSector::find()->select('business_sector_id')->where(['user_id' => $data['userid']])->all();
				if(count($sectormodel)>0)
				{
					foreach($sectormodel as $sector)
					{
						//$roleArray['id']=$sector->role_id;
						//$roleArray['name']=$sector->businesssector->name;
						$businesssectorArray[]=['id'=>$sector->business_sector_id,'name'=>$sector->businesssector->name];
					}
				}

				//$roleArray=[];
				$businesssectorGroupArray=[];
				$groupmodel = UserBusinessSectorGroup::find()->select('business_sector_group_id')->where(['user_id' => $data['userid']])->all();
				if(count($groupmodel)>0)
				{
					foreach($groupmodel as $group)
					{
						//$roleArray['id']=$role->role_id;
						//$roleArray['name']=$role->role->role_name;
						$businesssectorGroupArray[]=['id'=>$group->business_sector_group_id,'name'=>$group->businesssectorgroup->groupname];
					}
				}


				$resultarr['userdata'] = [
						'first_name' => $Usermodel->first_name,
						'last_name' => $Usermodel->last_name,
						'email' => $Usermodel->email,
						'telephone' => $Usermodel->telephone,
						'country_name' => ($Usermodel->country)?$Usermodel->country->name:'',
						'state_name' => ($Usermodel->state)?$Usermodel->state->name:''
					];
				$resultarr['roles'] = $roleListArray;
				$resultarr['standards'] = $standardListArray;
				$resultarr['processes'] = $processListArray;
				$resultarr['business_sector'] = $businesssectorArray;
				$resultarr['business_sector_group'] = $businesssectorGroupArray;
				
				return ['data'=>$resultarr];
			}
			else
			{
				return $this->asJson($responsedata=array('status'=>0,'message'=>$Usermodel->getErrors()));
			}
		}	
	}

		
	public function actionChangeUsernamePassword()
    {           
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
	    
		//$data = Yii::$app->request->post();		
		//if($data)
		//{
		$dataencrypt = Yii::$app->request->post();		
		if($dataencrypt)
		{
			$EncryptDecryptKey = Yii::$app->params['EncryptDecryptKey'];
			$data = Yii::$app->globalfuns->cryptoJsAesDecrypt($EncryptDecryptKey,json_encode($dataencrypt));
			
	        $token = $data['token'];
            if (empty($token) || !is_string($token)) {         
				$responsedata=array('status'=>0,'message'=>'Username and Password reset token cannot be blank');
			}else{				
								
				$rolemodel = UserRole::find()->where(['verification_token' => $token,'login_status'=>0])->one();
				if($rolemodel!==null)
				{						
					//$timestamp = (int) substr($token, strrpos($token, '_') + 1);
					//$expire = Yii::$app->params['user.passwordResetTokenExpire'];       
					
					//if(($timestamp + $expire) <= time())
					//{					
						//$responsedata=array('status'=>0,'message'=>'Wrong password reset token');
					//}else{
						if($data['isTokenVerifyRequest']==1)
						{
							$responsedata=array('status'=>1,'message'=>'Token verified successfully');
						}else{
							
							//$rolemodel=new UserRole();
							$rolemodel->setPassword($data['new_password']);
							$rolemodel->setUsername($data['new_username']);
							$rolemodel->verification_token='';
							//$rolemodel->user_id=$model->id;
							//$rolemodel->role_id=$value['role_id'];
							//$rolemodel->franchise_id=$value['franchise_id'];
							$rolemodel->status=0;
							$rolemodel->login_status=1;
							//$rolemodel->created_by=$userData['userid'];
							//$rolemodel->username=$value['username'];
							//$rolemodel->save();
							//print_r($rolemodel->getErrors());

							
							//$model->setUsername($data['new_username']);
							//$model->setPassword($data['new_password']);
							//$model->login_status=1;
							//$model->verification_token='';

							//$userData = Yii::$app->userdata->getData();
							//$model->updated_by=$model->id;

							if($rolemodel->validate() && $rolemodel->save())
							{	
								$loginurl=Yii::$app->params['site_path']."login";
								$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'change_password'])->one();
								if($mailContent !== null)
								{
									$mailmsg=str_replace('{USERNAME}', "User", $mailContent['message'] );
									$mailmsg=str_replace('{LOGINUSERNAME}', $data['new_username'], $mailmsg );
									$mailmsg=str_replace('{PASSWORD}', $data['new_password'], $mailmsg );
									$mailmsg=str_replace('{URL}', $loginurl, $mailmsg );

									// Mail to Customer with Login credentials
									$MailLookupModel = new MailLookup();
									$MailLookupModel->to=$rolemodel->usercompanyinfo->company_email;									
									$MailLookupModel->subject=$mailContent['subject'];
									$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
									$MailLookupModel->attachment='';
									$MailLookupModel->mail_notification_id='';
									$MailLookupModel->mail_notification_code='';
									$Mailres=$MailLookupModel->sendMail();

									$responsedata=array('status'=>1,'message'=>'Username and Password changed successfully.');	
								}
							}
							else
							{
								//$responsedata=array('status'=>0,'message'=>$rolemodel->errors);
								$errorArr = '';
								foreach($rolemodel->errors as $key=>$errormessage){
									$errorArr .= implode(', ',$errormessage);
								}
								$responsedata=array('status'=>0,'message'=>$errorArr);
							}	
						}
					//}
				}else{
					$responsedata=array('status'=>0,'message'=>'Invalid reset token / You have already set a username and password.');
				}
			}

        }
		return $this->asJson($responsedata);	
    }

	public function actionChangePassword()
    {           
        $responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');

		$model = new ChangePassword();	
		/*	
		$data=Yii::$app->request->post();
		if($data)
		{
		*/
		$dataencrypt = Yii::$app->request->post();		
		//print_r($data);
		//die();
		if($dataencrypt)
		{
			$EncryptDecryptKey = Yii::$app->params['EncryptDecryptKey'];
			$data = Yii::$app->globalfuns->cryptoJsAesDecrypt($EncryptDecryptKey,json_encode($dataencrypt));
			//return $data;
			$userData = Yii::$app->userdata->getData();
			//$franchiseid=$userData['franchiseid'];			
					
			//$modelUser = UserRole::find()->where(['role_id' => $data['roleid'],'user_id' => $data['uid'],'franchise_id' => $franchiseid])->one();
			$modelUser = UserRole::find()->where(['id' => $userData['user_role_id']])->one();
			$model->UserPassword=$modelUser->password_hash;
			$model->old_password=$data['old_password'];
			$model->new_password=$data['new_password'];
			$model->confirm_password=$data['confirm_password'];
			
			if($model->validate())
			{
				if($model->resetPassword($modelUser->id))
				{
					$responsedata=array('status'=>1,'message'=>'Your New Password Updated Successfully.');	
				}else{					
					$responsedata=array('status'=>0,'message'=>$model->errors);	
				}
			}else{
				//$responsedata = $model->errors;
				$responsedata=array('status'=>0,'message'=>$model->errors);	
			}		
		}
		return $this->asJson($responsedata);		
    }
	
	public function actionAssignEnquiryExistingCustomer()
	{
		if(!Yii::$app->userrole->hasRights(array('forward_enquiry')))
		{
			return false;
		}
		
		$Enquirymodel = new Enquiry();
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$responsedata=array('status'=>0,'message'=>"failed");
		$dbdata = Enquiry::find()->where(['id' => $data['id']])->one();
		if ($dbdata !== null)
		{
			$dbdata->status = 2;
			$dbdata->status_updated_date = time();
			$dbdata->customer_id =($data['customer_id'])?$data['customer_id']:"";
			$dbdata->franchise_id =($data['franchise_id'])?$data['franchise_id']:"";

			$userData = Yii::$app->userdata->getData();
			$dbdata->status_updated_by=$userData['userid'];

			if($dbdata->save())
			{
				//$Usermodel->franchise_id = $data['franchise_id'];
				if($data['customer_id']){
					$CustomerUser = User::find()->where(['id' => $data['customer_id']])->one();
					if($CustomerUser !== null){
						$CustomerUser->franchise_id = $data['franchise_id'];
						$CustomerUser->save();
					}
				}

				$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'assign_enquiry_existing_customer'])->one();

				if($mailContent !== null)
				{
					$mailmsg=str_replace('{USERNAME}', $dbdata->contact_name, $mailContent['message'] );
					// Mail to Customer with Login credentials
					$MailLookupModel = new MailLookup();
					$MailLookupModel->to=$dbdata->company_email;					
					$MailLookupModel->subject=$mailContent['subject'];
					$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
					$MailLookupModel->attachment='';
					$MailLookupModel->mail_notification_id='';
					$MailLookupModel->mail_notification_code='';
					$Mailres=$MailLookupModel->sendMail();
				}

				// $customergrid = $this->renderPartial('@app/mail/layouts/EnquiryCustomerGridTemplate',[
				// 	'model' => $dbdata
				// ]); 

				$companygrid = $this->renderPartial('@app/mail/layouts/EnquiryCompanyGridTemplate',[
					'model' => $dbdata
				]);

				$standardgrid = $this->renderPartial('@app/mail/layouts/EnquiryStandardGridTemplate',[
					'model' => $dbdata
				]);

				

				$dbContent = MailNotifications::find()->select('subject,message')->where(['code' => 'enquiry_request_to_franchise'])->one();

				if($dbContent !== null)
				{	
					$enquirymodel = Enquiry::find()->where(['id' => $data['id']])->asArray()->one();
					
					$franchisemailid = UserCompanyInfo::find()->select('company_email')->where(['user_id' => $data['franchise_id']])->one();
					if($franchisemailid !== null)
					{
						$adminmailsubject=str_replace('{USERNAME}', $enquirymodel['contact_name'], $dbContent['subject'] );
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

				$responsedata=array('status'=>1,'message'=>'Enquiry has been mapped to existing customer','enquirystatus'=>$Enquirymodel->arrStatus[$dbdata->status],'status_updated_date'=>date($date_format,$dbdata->status_updated_date));
			}
		}
		return $this->asJson($responsedata);
	}

	public function actionEnquiryArchive()
	{
		if(!Yii::$app->userrole->hasRights(array('forward_enquiry')))
		{
			return false;
		}
		
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$Enquirymodel = new Enquiry();
		$dbdata = Enquiry::find()->where(['id' => $data['id']])->one();

		if ($dbdata !== null)
		{
			$dbdata->status = 3;
			$dbdata->save();
			$responsedata=array('status'=>1,'message'=>'Enquiry has been Archived','enquirystatus'=>$Enquirymodel->arrStatus[$dbdata->status],'status_updated_date'=>date($date_format,$dbdata->status_updated_date));
		}
		else
		{
			$responsedata=array('status'=>0,'message'=>"failed");
		}
		return $this->asJson($responsedata);
	}
	
	public function actionauditstandard()
	{
		if(!Yii::$app->userrole->hasRights(array('forward_enquiry')))
		{
			return false;
		}

		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$Enquirymodel = new Enquiry();
		$dbdata = Enquiry::find()->where(['id' => $data['id']])->one();

		if ($dbdata !== null)
		{
			$dbdata->status = 3;
			$dbdata->save();
			$responsedata=array('status'=>1,'message'=>'Enquiry has been Archived','enquirystatus'=>$Enquirymodel->arrStatus[$dbdata->status],'status_updated_date'=>date($date_format,$dbdata->status_updated_date));
		}
		else
		{
			$responsedata=array('status'=>0,'message'=>"failed");
		}
		return $this->asJson($responsedata);
	}

	public function actionViewProfile()
	{
		$userData = Yii::$app->userdata->getData();
		$user_type=$userData['user_type'];
		/*
		if(!Yii::$app->userrole->hasRights(array('user_master')) && $user_type!=3)
		{
			return false;
		}
		*/		
		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$model = User::find()->where(['id' => $userData['userid']])->one();
		if($model !== null)
		{
			$resultarr=array('userData'=>'','customerData'=>'','franchiseData'=>'');
			$userinfo=array();

			$userinfo['user_id']=$model->id;
			$userinfo['first_name']=$model->first_name;
			$userinfo['last_name']=$model->last_name;
			$userinfo['user_type']=$model->user_type;
			$userinfo['email']=$model->email;
			$userinfo['telephone']=$model->telephone;
			$userinfo['country']=($model->country_id)?$model->country->name:'';
			$userinfo['state']=($model->state_id)?$model->state->name:'';

			

			$userstdarr=array();
			$standardArray=array();
			$standardNameArray=array();
			$userstds=$model->userstandard;
			if(count($userstds)>0)
			{	
				foreach($userstds as $standard)
				{
					$standardArray[]=$standard->standard_id;
					$standardNameArray[]=$standard->standard->name;
				}
			}
			$userstdarr["standard"] = $standardArray;
			$userstdarr['standardDetails'] = $standardNameArray;
			$userinfo['standard'] = $userstdarr;

			

			$userrolearr=array();
			$roleArray=array();
			$roleNameArray=array();
			$rolemodel=$model->usersrole;
			if(count($rolemodel)>0)
			{
				foreach($rolemodel as $role)
				{
					$roleArray[]=$role->role_id;
					$roleNameArray[]=$role->role?$role->role->role_name:'';
				}
			}
			$userrolearr["role"] = $roleArray;
			$userrolearr["roleDetails"] = $roleNameArray;
			$userinfo['role'] = $userrolearr;

			

			$userprocessarr=array();
			$procsArray=array();
			$procsNameArray=array();
			$rolemodel=$model->usersrole;
			$userprocs=$model->userprocess;
			if(count($userprocs)>0)
			{
				foreach($userprocs as $procs)
				{
					$procsArray[]=$procs->process_id;
					$procsNameArray[]=$procs->process->name;
				}
			}
			$userprocessarr["process"] = $procsArray;
			$userprocessarr["processDetails"] = $procsNameArray;
			$userinfo['process'] = $userprocessarr;

			
			if($model->user_type=='1')
			{	
				$Qualificationmodel=$model->userqualification;
				if(count($Qualificationmodel)>0)
				{
					foreach($Qualificationmodel as $qualification)
					{
						$qualificationArray=array();
						$qualificationArray['qualification']=$qualification->qualification;
						$qualificationArray['university']=$qualification->board_university;
						$qualificationArray['subject']=$qualification->subject;
						$qualificationArray['start_year']=$qualification->start_year;
						$qualificationArray['end_year']=$qualification->end_year;
						//$qualificationArray['percentage']=$qualification->percentage;
						$qualificationArray['certificate']=$qualification->certificate;
						$userinfo["qualifications"][]=$qualificationArray;
					}
				}

				$Experiencemodel=$model->userexperience;
				if(count($Experiencemodel)>0)
				{
					foreach($Experiencemodel as $experience)
					{
						$experienceArray=array();
						$experienceArray['experience']=$experience->experience;
						$experienceArray['job_title']=$experience->job_title;
						$experienceArray['responsibility']=$experience->responsibility;
						$experienceArray['exp_from_date']= date($date_format,strtotime($experience->from_date));
						$experienceArray['exp_to_date']=date($date_format,strtotime($experience->to_date));
						$userinfo["experience"][]=$experienceArray;
					}
				}


				$Certificationmodel=$model->usercertification;
				if(count($Certificationmodel)>0)
				{
					foreach($Certificationmodel as $certification)
					{
						$certificationArray=array();
						$certificationArray['certificate_name']=$certification->certification_name;
						$certificationArray['training_hours']=$certification->training_hours;						
						$certificationArray['completed_date']=date($date_format,strtotime($certification->completed_date));
						$certificationArray['filename']=$certification->filename;
						$certificationArray['id']=$certification->id;
						$userinfo["certifications"][]=$certificationArray;
					}
				}

				$Trainingmodel=$model->usertraining;
				if(count($Trainingmodel)>0)
				{
					foreach($Trainingmodel as $training)
					{
						$trainingArray=array();
						$trainingArray['training_subject']=$training->subject;
						$trainingArray['training_date']=$training->training_date;
						$trainingArray['training_hours']=$training->training_hours;
						$userinfo["training_info"][]=$trainingArray;
					}
				}


				$resultarr['userData']=$userinfo;
			}
			else 
			{	
				$companyinfoarr=array();
				$companyinfo=$model->usercompanyinfo;
				if(!empty($companyinfo))
				{
					$companyinfoarr['id']=$companyinfo->id;
					$companyinfoarr['company_name']=$companyinfo->company_name;
					$companyinfoarr['contact_name']=$companyinfo->contact_name;
					$companyinfoarr['company_telephone']=$companyinfo->company_telephone;
					$companyinfoarr['company_email']=$companyinfo->company_email;
					$companyinfoarr['company_website']=$companyinfo->company_website;
					$companyinfoarr['company_address1']=$companyinfo->company_address1;
					$companyinfoarr['company_address2']=$companyinfo->company_address2;
					$companyinfoarr['company_city']=$companyinfo->company_city;
					$companyinfoarr['company_country']=($companyinfo->company_country_id)?$companyinfo->companycountry->name:"";
					$companyinfoarr['company_state']=($companyinfo->company_state_id)?$companyinfo->companystate->name:"";
					$companyinfoarr['number_of_sites']=$companyinfo->number_of_sites;
					$companyinfoarr['company_zipcode']=$companyinfo->company_zipcode;

					$companyinfoarr['osp_number']=$companyinfo->osp_number;
					$companyinfoarr['osp_details']=$companyinfo->osp_details;

					
				}

				if($model->user_type=='2')
				{
					$resultarr['customerData']=$userinfo;
					$resultarr['customerData']['companyinfo']=$companyinfoarr;
				}
				else
				{
					$resultarr['franchiseData']=$userinfo;
					$resultarr['franchiseData']['companyinfo']=$companyinfoarr;
				}
			}
			return ['data'=>$resultarr];
		}
		else
		{
			return $this->asJson($responsedata);
		}
	}
	
	
	public function actionGeneratepassword()
	{
		$model = new User();
		$model->setPassword('franchise');
		echo $model->password_hash;
		die();
	}

	public function actionCheckUsername()
	{		
		$userData = Yii::$app->userdata->getData();
		$user_type=$userData['user_type'];
		if(!Yii::$app->userrole->hasRights(array('edit_user_roles')) && $user_type!=3)
		{
			return false;
		}
		
		$data = yii::$app->request->post();	
		if($data)
		{
			//$rolemodel = UserRole::find()->where(['username'=>$data['username']])->one();			
			$usernameobj = UserRole::find()->where(['username'=>$data['username']])->one();
			//var_dump($rolefranchisemodel);
			if($rolefranchisemodel !== null){
				$responsedata=array('status'=>0,'already_exists'=>1,'message'=>['username'=>['Username Already Exists']]);
			//}else if($rolemodel !== null){
				//$responsedata=array('status'=>0,'message'=>['username'=>['Username already exists']]);
			}else{
				$responsedata=array('status'=>1,'already_exists'=>0);
			}		
			return $this->asJson($responsedata);
		}
	}

	public function actionUserRoleApproval()
	{	
		if(!Yii::$app->userrole->hasRights(array('user_role_approval')))
		{
			return false;
		}
		
		$data = yii::$app->request->post();	
		$userData = Yii::$app->userdata->getData();
			
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$responsedata=array('status'=>0,'message'=>'Something went wrong');
		if($data)
		{
			$insert = 1;
			if(isset($data['username']) && $data['username'] !=''){

			
				$usernameobj = UserRole::find()->where(['username'=>$data['username']])->one();
				if($usernameobj !== null){
					$insert = 0;
					$responsedata=array('status'=>1,'already_exists'=>1,'message'=>['username'=>['Username Already Exists']]);
				}
				
			}
			$curusernameobj = UserRole::find()->where(['id'=>$data['role_id']])->one();
			if($curusernameobj !== null){
				$curresource_access = $curusernameobj->role->resource_access;
				if($curresource_access!=3 && $curresource_access!=4){
					if(!isset($data['username']) || !isset($data['user_password']) || $data['username'] =='' || $data['user_password']==''){
						$responsedata=array('status'=>0,'already_exists'=>1,'message'=>'Username is empty ');
					}
				}
			}
			

			if($insert){
				
				if($curusernameobj !== null){ 
					//if($curusernameobj->role->)
					//resource_access ==3 || $role->role->resource_access ==4
					
					if($data['status'] == '2'){
						$curusernameobj->approval_status = $data['status'];
						$curusernameobj->approval_comment = $data['comment'];
						//if(isset($data['username']) && $data['username'] !=''){
						if($curresource_access!=3 && $curresource_access!=4){
							$curusernameobj->username=$data['username'];
							$curusernameobj->setPassword($data['user_password']);
						}						
						if($curusernameobj->save()){
							$role= Role::find()->where(['id' => $curusernameobj->role_id ])->one();
							$franchise= UserCompanyInfo::find()->select('company_name,company_email')->where(['user_id' => $curusernameobj->franchise_id ])->one();
								
							$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'add_role'])->one();
							if($mailContent !== null && $role!== null && $franchise!== null)
							{
								$mailmsg=str_replace('{USERNAME}', "User", $mailContent['message'] );
								$mailmsg=str_replace('{role}', $role->role_name, $mailmsg );
								$mailmsg=str_replace('{franchise}', $franchise['company_name'], $mailmsg );
								$mailmsg=str_replace('{username}', $data['username'], $mailmsg );
								$mailmsg=str_replace('{password}', $data['user_password'], $mailmsg );
								
								$MailLookupModel = new MailLookup();
								$MailLookupModel->to=$curusernameobj->user->email;								
								$MailLookupModel->subject=$mailContent['subject'];
								$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
								$MailLookupModel->attachment='';
								$MailLookupModel->mail_notification_id='';
								$MailLookupModel->mail_notification_code='';
								$Mailres=$MailLookupModel->sendMail();
							}

							
						}



					}else{
						$curusernameobj->approval_status = $data['status'];
					}
					
					$curusernameobj->approval_by = $userid;
					$curusernameobj->approval_comment = $data['comment'];
					$curusernameobj->approval_date = time();
					$curusernameobj->save();
					$responsedata=array('status'=>1,'already_exists'=>0,'message'=>'Updated Successfully');
				}
			}	
			
			
			
			return $this->asJson($responsedata);
		}
	}


	public function actionChangeCredentials()
	{
		if(!Yii::$app->userrole->isAdmin())
		{
			return false;
		}
		$data = yii::$app->request->post();	
		$responsedata=array('status'=>0,'message'=>'Something went wrong');
		
		if($data)
		{
			
			$usernameobj = UserRole::find()->where(['username'=>$data['username']])->andWhere(['!=', 'id', $data['role_id']])->one();
			if($usernameobj !== null){
				
				$responsedata=array('status'=>1,'already_exists'=>1,'message'=>['username'=>['Username Already Exists']]);
				return $this->asJson($responsedata);
			}

			$curusernameobj = UserRole::find()->where(['id'=>$data['role_id']])->one();
			$curusernameobj->username=$data['username'];
			$curusernameobj->setPassword($data['user_password']);

			if($curusernameobj->save()){
				$role= Role::find()->where(['id' => $curusernameobj->role_id ])->one();
				$franchise= UserCompanyInfo::find()->select('company_name,company_email')->where(['user_id' => $curusernameobj->franchise_id ])->one();
					
				$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'change_credentials'])->one();
				if($mailContent !== null && $role!== null && $franchise!== null)
				{
					$mailmsg=str_replace('{USERNAME}', "User", $mailContent['message'] );
					$mailmsg=str_replace('{role}', $role->role_name, $mailmsg );
					$mailmsg=str_replace('{franchise}', $franchise['company_name'], $mailmsg );
					$mailmsg=str_replace('{username}', $data['username'], $mailmsg );
					$mailmsg=str_replace('{password}', $data['user_password'], $mailmsg );
					
					$MailLookupModel = new MailLookup();
					$MailLookupModel->to=$curusernameobj->user->email;					
					$MailLookupModel->subject=$mailContent['subject'];
					$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
					$MailLookupModel->attachment='';
					$MailLookupModel->mail_notification_id='';
					$MailLookupModel->mail_notification_code='';
					$Mailres=$MailLookupModel->sendMail();
				}
				$responsedata=array('status'=>1,'already_exists'=>0,'message'=>'Updated Successfully');
				
			}
				
			
		}
		return $this->asJson($responsedata);
	}

	public function actionCheckUserRole()
	{		
		$data = yii::$app->request->post();	
		if($data)
		{
			//$rolemodel = UserRole::find()->where(['username'=>$data['username']])->one();			
			$rolefranchisemodel = UserRole::find()->where(['user_id'=>$data['user_id'],'role_id'=>$data['role_id'],'franchise_id'=>$data['franchise_id']])->one();
			//var_dump($rolefranchisemodel);
			if($rolefranchisemodel !== null){
				$responsedata=array('status'=>0,'message'=>['role_id'=>['The Combination of role with franchise has been taken already']]);
			//}else if($rolemodel !== null){
				//$responsedata=array('status'=>0,'message'=>['username'=>['Username already exists']]);
			}else{
				$responsedata=array('status'=>1);
			}
			/*
			$rolemodel = new UserRole;
			$rolemodel->username=$data['username'];
			$rolemodel->role_id=$data['role_id'];
			$rolemodel->franchise_id=$data['franchise_id'];
			$rolemodel->user_id=$data['user_id'];
			if($rolemodel->validate())
        	{ 
				$responsedata=array('status'=>1);
			}
            else
            {
                $responsedata=array('status'=>0,'message'=>$rolemodel->errors);
			}
			*/
			return $this->asJson($responsedata);
		}
	}

	public function actionCheckUserRoleExists()
	{		
		$data = yii::$app->request->post();	
		if($data)
		{
			$rolemodel = UserRole::find()->where(['franchise_id'=>$data['franchise_id'],'role_id'=>$data['role_id']  ])->one();
			if($rolemodel !== null){
				$responsedata=array('status'=>0,'message'=>['role_id'=>['Role already exists']]);
			}else{
				$responsedata=array('status'=>1);
			}
			
			return $this->asJson($responsedata);
		}
	}

	
	public function actionPersonnelfile()
	{
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
			
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];		
		if ($data) 
		{	
			if(!$this->canDoUserAccess($data['user_id']) && !($user_type==1 && $userid==$data['user_id']))
			{
				return false;
			}			
		
			$files = User::find()->where(['id'=>$data['id']])->one();
			if($data['filetype']=='passport'){
				$filename = $files->passport_file;
			}else if($data['filetype']=='contract'){
				$filename = $files->contract_file;
			}
			
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			header('Access-Control-Max-Age: 1000');
			header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

			
			$filepath=Yii::$app->params['user_files'].$filename;
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
		}	
		die;
	}

	public function actionStandardfile()
	{
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
			
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];		
		if ($data) 
		{	
			if(!$this->canDoUserAccess($data['user_id']) && !($user_type==1 && $userid==$data['user_id']))
			{
				return false;
			}
			$files = UserStandard::find()->where(['id'=>$data['id']])->one();
			if($data['filetype']=='standard'){
				$filename = $files->standard_exam_file;
			}else if($data['filetype']=='recycle'){
				$filename = $files->recycle_exam_file;
			}else if($data['filetype']=='social'){
				$filename = $files->social_course_exam_file;
			}else if($data['filetype']=='witness'){
				$filename = $files->witness_file;
			}else if($data['filetype']=='qua_exam'){
				$filename = $files->qua_exam_file;
			}
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
		}	
		die;
	}

	public function actionAcademicfile(){
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
			
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];		
		if ($data) 
		{	
			if(!$this->canDoUserAccess($data['user_id']) && !($user_type==1 && $userid==$data['user_id']))
			{
				return false;
			}
			$files = UserQualification::find()->where(['id'=>$data['id']])->one();
			
			$filename = $files->certificate;
			
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
		}	
		die;
	}

	public function actionDocumentfile()
	{
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
			
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];		
		if ($data) 
		{	
			if(!$this->canDoUserAccess($data['user_id']) && !($user_type==1 && $userid==$data['user_id']))
			{
				return false;
			}
			$files = UserRoleBusinessGroup::find()->where(['id'=>$data['id']])->one();
			
			$filename = $data['filename'];
			
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			header('Access-Control-Max-Age: 1000');
			header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

			
			$filepath=Yii::$app->params['user_files'].$filename;
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
		}	
		die;
	}

	public function actionBgroupfile()
	{
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
			
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];		
		if ($data) 
		{	
			if(!$this->canDoUserAccess($data['user_id']) && !($user_type==1 && $userid==$data['user_id']))
			{
				return false;
			}
			$files = UserBusinessGroup::find()->where(['id'=>$data['id']])->one();
			if($data['filetype']=='exam'){
				$filename = $files->exam_file;
			}else if($data['filetype']=='interview'){
				$filename = $files->technical_interview_file;
			}

			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			header('Access-Control-Max-Age: 1000');
			header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

			
			$filepath=Yii::$app->params['user_files'].$filename;
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
		}	
		die;
	}

	public function actionTebgroupfile()
	{
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
			
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];		
		if ($data) 
		{	
			if(!$this->canDoUserAccess($data['user_id']) && !($user_type==1 && $userid==$data['user_id']))
			{
				return false;
			}
			
			$files = UserRoleTechnicalExpertBs::find()->where(['id'=>$data['id']])->one();
			if($data['filetype']=='exam'){
				$filename = $files->exam_file;
			}else if($data['filetype']=='interview'){
				$filename = $files->technical_interview_file;
			}

			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			header('Access-Control-Max-Age: 1000');
			header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

			
			$filepath=Yii::$app->params['user_files'].$filename;
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
		}	
		die;
	}

	public function actionUserfile()
	{
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
			
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];		
		if ($data) 
		{	
			if(!$this->canDoUserAccess($data['user_id']) && !($user_type==1 && $userid==$data['user_id']))
			{
				return false;
			}
			$files = UserCertification::find()->where(['id'=>$data['id']])->one();

			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			header('Access-Control-Max-Age: 1000');
			header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

			
			$filepath=Yii::$app->params['user_files'].$files->filename ;
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
		}	
		die;
	}

	public function actionGetApprover()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
			$userData = Yii::$app->userdata->getData();
			
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];

			$franchiseID = $data['franchise_id'];
			//$franchiseid=$userData['franchiseid'];

			//application_review
			//application_approval
			$userArr = [];
			$approvers = Yii::$app->globalfuns->getQualifiedPrivilegeUser($franchiseID,'application_approval');
			if(count($approvers)>0){
				foreach($approvers as $approver){
					$userArr[] = ['id'=>$approver['id'],'first_name'=>$approver['first_name'],'last_name'=>$approver['last_name']];
				}
			}
			$responsedata=array('status'=>1,'data'=>$userArr);
		}
		return $responsedata;
	}
	public function actionGetReviewer()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
			$userData = Yii::$app->userdata->getData();
			
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];

			$franchiseID = $data['franchise_id'];
			//$franchiseid=$userData['franchiseid'];

			//application_review
			//application_approval
			$userArr = [];
			$reviewers = Yii::$app->globalfuns->getQualifiedPrivilegeUser('','application_review');
			if(count($reviewers)>0){
				foreach($reviewers as $reviewer){
					$userArr[] = ['id'=>$reviewer['id'],'first_name'=>$reviewer['first_name'],'last_name'=>$reviewer['last_name']];
				}
			}
			$responsedata=array('status'=>1,'data'=>$userArr);
		}
		return $responsedata;
	}

	public function actionGetAuditReviewer()
	{
		$data = Yii::$app->request->post();
		
			$userData = Yii::$app->userdata->getData();
			
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];

			$userArr = [];

			
			$connection = Yii::$app->getDb();
			
			$query = 'SELECT usr.id,usr.first_name,usr.last_name,usr.email FROM `tbl_user_role` AS userrole INNER JOIN `tbl_rule` AS rule ON userrole.role_id=rule.role_id AND rule.privilege="audit_review" INNER JOIN `tbl_users` AS usr ON usr.id = userrole.user_id  ';
			$command = $connection->createCommand($query);
			$reviewers = $command->queryAll();

			if(count($reviewers)>0){
				foreach($reviewers as $reviewer){
					$userArr[] = ['id'=>$reviewer['id'],'first_name'=>$reviewer['first_name'],'last_name'=>$reviewer['last_name']];
				}
			}
			$responsedata=array('status'=>1,'data'=>$userArr);
		
		return $responsedata;
	}

	
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
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
			
			$id=$data['id'];
           	$model = User::find()->where(['id' => $id])->alias('t');			
			if($resource_access != 1)
			{
				if($user_type== Yii::$app->params['user_type']['user'] && !in_array('activate_user',$rules) && !in_array('deactivate_user',$rules) && !in_array('delete_user',$rules))
				{
					return $responsedata;
				}else if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
					if($resource_access == '5'){
						$userid = $franchiseid;
					}
					$model = $model->andWhere('t.franchise_id="'.$userid.'" or t.created_by="'.$userid.'"');
				}			
			}
			if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1 ){
				$model = $model->andWhere('t.franchise_id="'.$franchiseid.'"');
			}
			$model = $model->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='User has been activated successfully';
					}elseif($model->status==1){
						$msg='User has been deactivated successfully';
					}elseif($model->status==2){

						$exists=0;
						if($model->user_type==3)
						{
							if(Enquiry::find()->where( [ 'franchise_id' => $id ] )->exists())
							{
								$exists=1;
							}
							elseif(Application::find()->where( [ 'franchise_id' => $id ] )->exists())
							{
								$exists=1;
							}
							
						}
						elseif($model->user_type==2)
						{
							if(Enquiry::find()->where( [ 'customer_id' => $id ] )->exists())
							{
								$exists=1;
							}
						}
						else
						{
							if(UserQualification::find()->where( [ 'user_id' => $id ] )->exists())
							{
								$exists=1;
							}
							elseif(UserQualificationReviewHistory::find()->where( [ 'user_id' => $id ] )->exists())
							{
								$exists=1;
							}
							elseif(UserQualificationReview::find()->where( [ 'user_id' => $id ] )->exists())
							{
								$exists=1;
							}
							elseif(UserRole::find()->where( [ 'user_id' => $id ] )->exists())
							{
								$exists=1;
							}
							elseif(UserQualificationReviewComment::find()->where( [ 'user_id' => $id ] )->exists())
							{
								$exists=1;
							}
							elseif(ApplicationApprover::find()->where( [ 'user_id' => $id ] )->exists())
							{
								$exists=1;
							}
							elseif(ApplicationReviewer::find()->where( [ 'user_id' => $id ] )->exists())
							{
								$exists=1;
							}
						}

						if($exists==0)
                        {
                            //User::findOne($id)->delete();
                        }
						$msg='User has been deleted successfully';
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

	public function actionSendforapproval()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong!');
		$data = Yii::$app->request->post();
		if($data)
		{
			$userData = Yii::$app->userdata->getData();
			$user_type=$userData['user_type'];
			/*
			if(!Yii::$app->userrole->hasRights(array('edit_user_roles')) && $user_type!=3 && !$this->canDoUserAccess($data['id']))
			{
				return false;
			}
			*/			
						
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			$model = User::find();
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$franchiseid=$userData['franchiseid'];
			$is_headquarters =$userData['is_headquarters'];
			$resource_access=$userData['resource_access'];
			$connection = Yii::$app->getDb();
			if($data['type']=='declaration'){
				$declarationmodal=UserDeclaration::find()->where(['user_id' => $data['id']])->andWhere(['status' => 0])->all();
				if(count($declarationmodal)>0)
				{
					foreach($declarationmodal as $dec)
					{	

						$dec->status = 1;
						$dec->status_change_by = $userid;
						$dec->status_change_date = time();
						$dec->save();
					}
					$responsedata=array('status'=>1,'message'=>'Sent for Approval Successfully');
				}else{
					return $responsedata=array('status'=>0,'message'=>'There are no declaration for approval. Please submit before sending for approval.');
				}
				/*
				$declarationmodal=UserDeclaration::find()->where(['user_id' => $data['id']])->andWhere(['status' => 1])->all();
				if(count($declarationmodal)>0)
				{
					foreach($declarationmodal as $dec)
					{	

						$declarations=array();
						$declarations['id']=$dec['id'];
						$declarations['declaration_company']=$dec['company'];
						$declarations['declaration_contract_id']=$dec['contract'];
						$declarations['declaration_contract']=($dec['contract']!='' && $dec['contract']!=0 ? $dec->arrContract[$dec['contract']]:'NA');
						$declarations['declaration_interest']=$dec['interest'];
						$declarations['declaration_start_year']=$dec['start_year'];
						$declarations['declaration_end_year']=$dec['end_year'];
						$declaration_approvalwaiting_arr[]=$declarations;
					}
					$resultarr["declaration_approvalwaiting"]=$declaration_approvalwaiting_arr;
					$responsedata['declaration_approvalwaiting']= $resultarr["declaration_approvalwaiting"];
				}*/
			}else if($data['type']=='declarationreject'){
				$declarationmodal=UserDeclaration::find()->where(['user_id' => $data['id']])->andWhere(['status' => 3])->all();
				if(count($declarationmodal)>0)
				{
					foreach($declarationmodal as $dec)
					{	
						$dechistorymodal = new UserDeclarationHistory();
						$dechistorymodal->user_declaration_id = $dec->id;
						$dechistorymodal->user_id = $dec->user_id; 
						$dechistorymodal->company = $dec->company;
						$dechistorymodal->contract = $dec->contract;
						$dechistorymodal->interest = $dec->interest;
						$dechistorymodal->start_year = $dec->start_year;
						$dechistorymodal->end_year = $dec->end_year;
						$dechistorymodal->created_on = $dec->created_on;
						$dechistorymodal->created_by = $dec->created_by;
						$dechistorymodal->status = $dec->status;
						$dechistorymodal->status_change_by = $dec->status_change_by;
						$dechistorymodal->status_change_date = $dec->status_change_date;
						$dechistorymodal->status_comment = $dec->status_comment;
						$dechistorymodal->save();


						$dec->status = 1;
						$dec->status_change_by = $userid;
						$dec->status_change_date = time();
						$dec->save();
					}
					$responsedata=array('status'=>1,'message'=>'Sent for Approval Successfully');
				}
				/*
				$declarationmodal=UserDeclaration::find()->where(['user_id' => $data['id']])->andWhere(['status' => 1])->all();
				if(count($declarationmodal)>0)
				{
					foreach($declarationmodal as $dec)
					{	

						$declarations=array();
						$declarations['id']=$dec['id'];
						$declarations['declaration_company']=$dec['company'];
						$declarations['declaration_contract_id']=$dec['contract'];
						$declarations['declaration_contract']=($dec['contract']!='' && $dec['contract']!=0 ? $dec->arrContract[$dec['contract']]:'NA');
						$declarations['declaration_interest']=$dec['interest'];
						$declarations['declaration_start_year']=$dec['start_year'];
						$declarations['declaration_end_year']=$dec['end_year'];
						$declaration_approvalwaiting_arr[]=$declarations;
					}
					$resultarr["declaration_approvalwaiting"]=$declaration_approvalwaiting_arr;
					$responsedata['declaration_approvalwaiting']= $resultarr["declaration_approvalwaiting"];
				}
				*/
			}else if($data['type']=='rejbusinessgroup'){
				$businessGroupmodel=UserBusinessGroupCode::find()->alias('t')->innerJoinWith('usersector as usersector')->where(['usersector.user_id' => $data['id']])->andWhere(['t.status' => 3])->all();
				//echo count($businessGroupmodel); die;
				if(count($businessGroupmodel)>0)
				{
					foreach($businessGroupmodel as $dec)
					{	
						$businessgroupmodel = UserBusinessGroup::find()->where(['id' => $dec->business_group_id])->one();
						$bgrouphistorymodal = new UserBusinessGroupCodeHistory();
						$bgrouphistorymodal->user_business_group_code_id = $dec->id;
						$bgrouphistorymodal->user_id=$businessgroupmodel->user_id;
						$bgrouphistorymodal->standard_id=$businessgroupmodel->standard_id;
						$bgrouphistorymodal->business_sector_id=$businessgroupmodel->business_sector_id;
						$bgrouphistorymodal->business_group_id=$dec->business_group_id;
						$bgrouphistorymodal->business_sector_group_id=$dec->business_sector_group_id;
						$bgrouphistorymodal->academic_qualification_status=$businessgroupmodel->academic_qualification_status;
						$bgrouphistorymodal->exam_file=$businessgroupmodel->exam_file?:'';
						$bgrouphistorymodal->technical_interview_file=$businessgroupmodel->technical_interview_file?:'';
						$bgrouphistorymodal->status = $dec->status;
						$bgrouphistorymodal->status_change_by = $dec->status_change_by;
						$bgrouphistorymodal->status_change_date = $dec->status_change_date;
						$bgrouphistorymodal->status_change_comment = $dec->status_change_comment;
						$bgrouphistorymodal->created_by = $businessgroupmodel->created_by;
						$bgrouphistorymodal->created_at = $businessgroupmodel->created_at;
						$bgrouphistorymodal->save();
				


						$dec->status = 1;
						$dec->status_change_by = $userid;
						$dec->status_change_date = time();
						$dec->save();
						//print_r($dec->getErrors());
					}
					$responsedata=array('status'=>1,'message'=>'Sent for Approval Successfully');
				}
				/*
				$businessGroupArray=[];
				$businessGroupNameArray=[];
				//$businessGroupmodel = UserBusinessGroup::find()->where(['user_id' => $data['id']])->andWhere(['status' => 1])->all();
				$command = $connection->createCommand("SELECT std.name as standard_name,gp.id, gp.`business_sector_id`,master_gp.name,
					gp.standard_id,std.name, master_gp.name as businesssector_name, gp.academic_qualification_status,
					gp.exam_file,gp.technical_interview_file,
					GROUP_CONCAT(gpcode.`business_sector_group_id`) as business_sector_group_ids,
					GROUP_CONCAT(gpcode.`id`) as business_sector_group_code_ids,
					GROUP_CONCAT(master_gpcode.`group_code`) as group_codes,
					gpcode.status 
					FROM `tbl_user_business_group` as gp 
					inner join `tbl_user_business_group_code` as gpcode on gp.id=gpcode.`business_group_id` 
					inner join `tbl_business_sector` as master_gp on master_gp.id=gp.`business_sector_id` 
					inner join `tbl_business_sector_group` as master_gpcode on master_gpcode.id=gpcode.`business_sector_group_id`  
					inner join `tbl_standard` as std on std.id=gp.standard_id 
					WHERE `user_id`=".$data['id']." and gpcode.status=1 group by gpcode.status,gpcode.`business_group_id`");
				$businessGroupmodel = $command->queryAll();
				if(count($businessGroupmodel)>0)
				{
					foreach($businessGroupmodel as $bgroup)
					{
						$roledtArray=array();
						
						$roledtArray['id']=$bgroup['id'];
						$roledtArray['standard_id']=$bgroup['standard_id'];
						$roledtArray['business_sector_id']=$bgroup['business_sector_id'];
						$roledtArray['standard_name']=$bgroup['standard_name'];
						$roledtArray['business_sector_name']=$bgroup['businesssector_name'];
						$roledtArray['academic_qualification']=$bgroup['academic_qualification_status'];
						$roledtArray['academic_qualification_name']=$bgroup['academic_qualification_status']==1?'Yes':'No';
						$roledtArray['examfilename']=$bgroup['exam_file'];
						$roledtArray['technicalfilename']=$bgroup['technical_interview_file'];
						
						
						$groupcodeArr = $bgroup['business_sector_group_ids'];
						$groupnamecodeArr = $bgroup['group_codes'];
						
						$roledtArray['business_sector_group_id'] = explode(',',$groupcodeArr);//$groupcodeArr;
						$roledtArray['business_sector_group_id_arr'] = explode(',',$groupcodeArr);
						$roledtArray['business_sector_group_name'] = $groupnamecodeArr;
						$roledtArray['business_sector_group_name_arr'] = explode(',',$groupnamecodeArr);
						$roledtArray['business_sector_group_code_id'] = explode(',',$bgroup['business_sector_group_code_ids']);
						$businessGroupArray[]= $roledtArray;
					}
					$responsedata["businessgroup_approvalwaiting"] = $businessGroupArray;
					
				}
				*/
				
			}else if($data['type']=='businessgroup'){
				$businessGroupmodel=UserBusinessGroupCode::find()->alias('t')->innerJoinWith('usersector as usersector')->where(['usersector.user_id' => $data['id']])->andWhere(['t.status' => 0])->all();
				//echo count($businessGroupmodel); die;
				if(count($businessGroupmodel)>0)
				{
					foreach($businessGroupmodel as $dec)
					{	

						$dec->status = 1;
						$dec->status_change_by = $userid;
						$dec->status_change_date = time();
						$dec->save();
						//print_r($dec->getErrors());
					}
					$responsedata=array('status'=>1,'message'=>'Sent for Approval Successfully');
				}else{
					return $responsedata=array('status'=>0,'message'=>'There are no business group for approval. Please submit before sending for approval.');
				}

				/*
				$businessGroupArray=[];
				$businessGroupNameArray=[];
				//$businessGroupmodel = UserBusinessGroup::find()->where(['user_id' => $data['id']])->andWhere(['status' => 1])->all();
				$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
				$command = $connection->createCommand("SELECT std.name as standard_name,gp.id, gp.`business_sector_id`,master_gp.name,
					gp.standard_id,std.name, master_gp.name as businesssector_name, gp.academic_qualification_status,
					gp.exam_file,gp.technical_interview_file,
					GROUP_CONCAT(gpcode.`business_sector_group_id`) as business_sector_group_ids,
					GROUP_CONCAT(gpcode.`id`) as business_sector_group_code_ids,
					GROUP_CONCAT(master_gpcode.`group_code`) as group_codes,
					gpcode.status ,gpcode.status_change_comment, gpcode.status_change_date, gpcode.status_change_by 
					FROM `tbl_user_business_group` as gp 
					inner join `tbl_user_business_group_code` as gpcode on gp.id=gpcode.`business_group_id` 
					inner join `tbl_business_sector` as master_gp on master_gp.id=gp.`business_sector_id` 
					inner join `tbl_business_sector_group` as master_gpcode on master_gpcode.id=gpcode.`business_sector_group_id`  
					inner join `tbl_standard` as std on std.id=gp.standard_id 
					WHERE `user_id`=".$data['id']." and gpcode.status=1 group by gpcode.status,gpcode.`business_group_id`");
				$businessGroupmodel = $command->queryAll();
				if(count($businessGroupmodel)>0)
				{
					foreach($businessGroupmodel as $bgroup)
					{
						$roledtArray=array();
						
						$roledtArray['id']=$bgroup['id'];
						$roledtArray['standard_id']=$bgroup['standard_id'];
						$roledtArray['business_sector_id']=$bgroup['business_sector_id'];
						$roledtArray['standard_name']=$bgroup['standard_name'];
						$roledtArray['business_sector_name']=$bgroup['businesssector_name'];
						$roledtArray['academic_qualification']=$bgroup['academic_qualification_status'];

						$roledtArray['academic_qualification_name']=$bgroup['academic_qualification_status']==1?'Yes':'No';
						$roledtArray['examfilename']=$bgroup['exam_file'];
						$roledtArray['technicalfilename']=$bgroup['technical_interview_file'];
						
						$groupcodeArr = $bgroup['business_sector_group_ids'];
						$groupnamecodeArr = $bgroup['group_codes'];
						
						$roledtArray['business_sector_group_id'] = explode(',',$groupcodeArr);//$groupcodeArr;
						$roledtArray['business_sector_group_id_arr'] = explode(',',$groupcodeArr);
						$roledtArray['business_sector_group_name'] = $groupnamecodeArr;
						$roledtArray['business_sector_group_name_arr'] = explode(',',$groupnamecodeArr);
						$roledtArray['business_sector_group_code_id'] = explode(',',$bgroup['business_sector_group_code_ids']);
						
						$businessGroupArray[]= $roledtArray;
					}
					$responsedata["businessgroup_approvalwaiting"] = $businessGroupArray;
					
				}
				*/
			}else if($data['type']=='te_business_group'){
				//$businessGroupmodel=UserBusinessGroupCode::find()->alias('t')->innerJoinWith('usersector as usersector')->where(['usersector.user_id' => $data['id']])->andWhere(['t.status' => 0])->all();
				//echo count($businessGroupmodel); die;
				//$UserRoleTechnicalExpertBsCode=UserRoleTechnicalExpertBsCode::find()->where(['user_role_technical_expert_bs_id' => $data['id']])->andWhere(['approval_status' => 0])->all();
				//$UserRoleTechnicalExpertBs=UserRoleTechnicalExpertBs::find()->where(['user_id' => $data['id']])->andWhere(['approval_status' => 0])->all();

				$businessGroupmodel=UserRoleTechnicalExpertBsCode::find()->alias('t')->innerJoinWith('expertbs as expertbs')->where(['expertbs.user_id' => $data['id']])->andWhere(['t.status' => 0])->all();
				
				if(count($businessGroupmodel)>0)
				{
					foreach($businessGroupmodel as $dec)
					{	

						$dec->status = 1;
						$dec->approval_by = $userid;
						$dec->approval_date = time();
						$dec->save();
						//print_r($dec->getErrors());

						if($dec->expertbs !== null){
							$expertbs = $dec->expertbs;
							$expertbs->status = 1;
							$expertbs->save();
						}
					}
						
					$responsedata=array('status'=>1,'message'=>'Sent for Approval Successfully');
				}else{
					return $responsedata=array('status'=>0,'message'=>'There are no business group for approval. Please submit before sending for approval.');
				}
				
				
			}else if($data['type']=='standard'){

				$standardmodel=UserStandard::find()->where(['user_id' => $data['id']])->andWhere(['approval_status' => 0])->all();
				if(count($standardmodel)>0)
				{
					foreach($standardmodel as $dec)
					{	

						$dec->approval_status = 1;
						$dec->approval_by = $userid;
						$dec->approval_date = time();
						$dec->save();
					}
					$responsedata=array('status'=>1,'message'=>'Sent for Approval Successfully');
				}else{
					return $responsedata=array('status'=>0,'message'=>'There are no standard for approval. Please submit before sending for approval.');
				}

				/*
				$standardArray = [];
				$standardChkNameArray = [];
				$standardmodel = UserStandard::find()->where(['user_id' => $data['id']])->andWhere(['approval_status' => 1])->all();
				if(count($standardmodel)>0)
				{
					foreach($standardmodel as $standard)
					{
						$stds=[];
						$stds["id"]=$standard->id;
						$stds["standard"]=$standard->standard_id;
						$stds["standard_name"]=$standard->standard->name;
						//$stds["standard_exam_date"]=date($date_format,strtotime($standard->standard_exam_date));
						//$stds["recycle_exam_date"]=date($date_format,strtotime($standard->recycle_exam_date));
						//$stds["social_course_exam_date"]=date($date_format,strtotime($standard->social_course_exam_date));
						$stds["standard_exam_file"]=$standard->standard_exam_file;
						$stds["recycle_exam_file"]=$standard->recycle_exam_file;
						$stds["social_course_exam_file"]=$standard->social_course_exam_file;

						$stds["standard_exam_date"]=($standard->standard_exam_date !='0000-00-00' && $standard->standard_exam_date !=null && $standard->standard_exam_date!='1970-01-01')?date($date_format,strtotime($standard->standard_exam_date)):'';
						$stds["recycle_exam_date"]=($standard->recycle_exam_date !='0000-00-00' && $standard->recycle_exam_date !=null && $standard->recycle_exam_date!='1970-01-01')?date($date_format,strtotime($standard->recycle_exam_date)):'';
						$stds["social_course_exam_date"]=($standard->social_course_exam_date !='0000-00-00' && $standard->social_course_exam_date !=null && $standard->social_course_exam_date!='1970-01-01')?date($date_format,strtotime($standard->social_course_exam_date)):'';


						$standardArray[]=$stds;

						$standardChkNameArray[$standard->standard_id] = $standard->standard->name;
					}
				}

				$standardIdExcept= [];
				$standardmodel = UserStandard::find()->where(['user_id' => $data['id']])->andWhere(['approval_status' =>[1,2,3]  ])->all();
				if(count($standardmodel)>0)
				{
					foreach($standardmodel as $standard)
					{
						$standardIdExcept[]=$standard->standard_id;
					}
				}
				$standardNewList = [];
				if(count($standardIdExcept)>0){
					$standardmodel = Standard::find()->where(['not in','id', $standardIdExcept])->all();
				}else{
					$standardmodel = Standard::find()->all();
				}
				if(count($standardmodel)>0)
				{
					foreach($standardmodel as $standard)
					{
						$standardNewList[]=['id'=>$standard->id,'name'=>$standard->name,'code'=>$standard->code];
					}
				}


				$responsedata["standardNewList"] = $standardNewList;
				$responsedata["standard_approvalwaiting"] = $standardArray;
				*/
			}else if($data['type']=='role'){
				$rolemodel=UserRole::find()->where(['user_id' => $data['id']])->andWhere(['approval_status' => 0])->all();
				if(count($rolemodel)>0)
				{
					foreach($rolemodel as $dec)
					{	
						$marketingmodel = $dec->role->resource_access;
						if($marketingmodel=='8')
						{
							$dec->approval_status = 2;
						}
						else
						{
							$dec->approval_status = 1;
						}
						$dec->approval_by = $userid;
						$dec->approval_date = time();
						$dec->save();
					}
					$responsedata=array('status'=>1,'message'=>'Sent for Approval Successfully');
				}else{
					return $responsedata=array('status'=>0,'message'=>'There are no roles for approval. Please submit before sending for approval.');
				}


				/*
				$resultarr["role"] = [];
				$rolemodel = UserRole::find()->where(['user_id' => $data['id'],'approval_status'=>1])->all();
				if(count($rolemodel)>0)
				{
					foreach($rolemodel as $role)
					{

						$roledtArray=array();
						$roledtArray['user_role_id']=$role->id;
						$roledtArray['role_id']=$role->role_id;
						$roledtArray['role_name']=$role->role->role_name;
						$roledtArray['username']=$role->username;
						$roledtArray['from_db']=1;
						$roledtArray['deleted']=0;
						$roledtArray['editable']=0;
						$roledtArray['franchise_name']= 'OSS '.$role->franchise->usercompanyinfo->osp_number.' - '.$role->franchise->usercompanyinfo->osp_details;//$role->franchise->usercompanyinfo->company_name.' ('.$role->franchise->usercompanyinfo->companycountry->name.')';
						$roledtArray['franchise_id']= $role->franchise->id;
						$resultarr["role"][] = $roledtArray;
					}
				}
				$responsedata['userListEntriesWaitingApproval'] = $resultarr["role"];
				*/
			}else if($data['type']=='rejrole'){
				/*
				$rolemodel=UserRole::find()->where(['user_id' => $data['id']])->andWhere(['approval_status' => 3])->all();
				if(count($rolemodel)>0)
				{
					foreach($rolemodel as $dec)
					{	

						$dec->approval_status = 1;
						$dec->approval_by = $userid;
						$dec->approval_date = time();
						$dec->save();
					}
					$responsedata=array('status'=>1,'message'=>'Sent for Approval Successfully');
				}
				*/
				$rolemodel=UserRole::find()->where(['id' => $data['user_role_id']])->andWhere(['approval_status' => 3])->one();
				if($rolemodel !='')
				{
					$rolemodel->approval_status = 1;
					$rolemodel->approval_by = $userid;
					$rolemodel->approval_date = time();
					$rolemodel->save();
					
					$responsedata=array('status'=>1,'message'=>'Sent for Approval Successfully');
				}

				/*
				$resultarr["role"] = [];
				$rolemodel = UserRole::find()->where(['user_id' => $data['id'],'approval_status'=>1])->all();
				if(count($rolemodel)>0)
				{
					foreach($rolemodel as $role)
					{

						$roledtArray=array();
						$roledtArray['user_role_id']=$role->id;
						$roledtArray['role_id']=$role->role_id;
						$roledtArray['role_name']=$role->role->role_name;
						$roledtArray['username']=$role->username;
						$roledtArray['from_db']=1;
						$roledtArray['deleted']=0;
						$roledtArray['editable']=0;
						$roledtArray['franchise_name']= 'OSS '.$role->franchise->usercompanyinfo->osp_number.' - '.$role->franchise->usercompanyinfo->osp_details;//$role->franchise->usercompanyinfo->company_name.' ('.$role->franchise->usercompanyinfo->companycountry->name.')';
						$roledtArray['franchise_id']= $role->franchise->id;
						$resultarr["role"][] = $roledtArray;
					}
				}
				$responsedata['userListEntriesWaitingApproval'] = $resultarr["role"];
				*/
			}
		}
		return $responsedata;
	}

	public function actionApproveAndReject()
	{
		if(!Yii::$app->userrole->hasRights(array('user_master')))
		{
			return false;
		}
		
		$resultarr=[];
		$datapost = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$model = User::find();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];

		$data = json_decode($datapost['formvalues'],true);
		$target_dir = Yii::$app->params['user_files']; 
		
		$response_str=($data['status']!='2')?'Rejected':'Approved';

		$connection = Yii::$app->getDb();
		
		if($data['type']=='declaration'){
			if(!Yii::$app->userrole->hasRights(array('declaration_approval')))
			{
				return false;
			}
			
			$decmodal=UserDeclaration::find()->where(['id' => $data['id']])->one();

			// if($data['status'] !='2')
			// {
			// 	$dechistorymodal = new UserDeclarationHistory();
			// 	$dechistorymodal->user_declaration_id = $decmodal->id;
			// 	$dechistorymodal->user_id = $decmodal->user_id; 
			// 	$dechistorymodal->company = $decmodal->company;
			// 	$dechistorymodal->contract = $decmodal->contract;
			// 	$dechistorymodal->interest = $decmodal->interest;
			// 	$dechistorymodal->start_year = $decmodal->start_year;
			// 	$dechistorymodal->end_year = $decmodal->end_year;
			// 	$dechistorymodal->created_on = $decmodal->created_on;
			// 	$dechistorymodal->created_by = $decmodal->created_by;
			// 	$dechistorymodal->status = $decmodal->status;
			// 	$dechistorymodal->status_change_by = $decmodal->status_change_by;
			// 	$dechistorymodal->status_change_date = $decmodal->status_change_date;
			// 	$dechistorymodal->status_comment = $data['comment'];
			// 	$dechistorymodal->save();
			// }
			
			$decmodal->status = $data['status'];
			$decmodal->status_change_by = $userid;
			$decmodal->status_change_date = time();
			$decmodal->status_comment = $data['comment'];

			$decmodal->save();
			//print_r($decmodal->getErrors());
			/*
			$resultarr["declaration_approvalwaiting"]=[];
			$resultarr["declaration_approved"]=[];
			$resultarr["declaration_rejected"]=[];
			$declarationmodal=UserDeclaration::find()->where(['user_id' => $data['user_id']])->all();
			//->andWhere(['status' => 1])
			if(count($declarationmodal)>0)
			{
				foreach($declarationmodal as $dec)
				{
					$declarations=array();
					$declarations['id']=$dec['id'];
					$declarations['declaration_company']=$dec['company'];
					$declarations['declaration_contract_id']=$dec['contract'];
					$declarations['declaration_contract']=($dec['contract']!='' && $dec['contract']!=0 ? $dec->arrContract[$dec['contract']]:'NA');
					$declarations['declaration_interest']=$dec['interest'];
					$declarations['declaration_start_year']=$dec['start_year'];
					$declarations['declaration_end_year']=$dec['end_year'];
					$declarations["status"]=$dec['status']?:'';
					$declarations["approval_comment"]=$dec['status_comment']?:'';
					$declarations["approval_date"]=($dec['status_change_date'] !='0000-00-00' && $dec['status_change_date'] !=null && $dec['status_change_date'] !='1970-01-01')?date($date_format,$dec['status_change_date']):'';
					$declarations["approval_by_name"]=$dec->approvaluser?$dec->approvaluser->first_name.' '.$dec->approvaluser->last_name:'';

					
					//$declaration_approvalwaiting_arr[]=$declarations;
					if($dec['status'] == 2){
						$resultarr["declaration_approved"][]=$declarations;
					}else if($dec['status'] == 1){
						$resultarr["declaration_approvalwaiting"][]=$declarations;
					}else if($dec['status'] == 3){
						$resultarr["declaration_rejected"][]=$declarations;
					}
					
					
					
				}
				//$resultarr["declaration_approvalwaiting"]=$declaration_approvalwaiting_arr;
			}
			*/
			
		}
		else if($data['type']=='role')
		{
			if(!Yii::$app->userrole->hasRights(array('user_role_approval')))
			{
				return false;
			}
			$userrolemodel = UserRole::find()->where(['id' => $data['id']])->one();
			$userrolemodel->approval_status = $data['status'];
			$userrolemodel->approval_by = $userid;
			$userrolemodel->approval_date = time();
			$userrolemodel->approval_comment = $data['comment'];
			$userrolemodel->save();	
	
		
			/*
			$rolemodal=UserRole::find()->where(['user_id' => $data['user_id']])->all();
			//->andWhere(['approval_status' => 1])
			if(count($rolemodal)>0)
			{
				$resultarr["role_id_waiting_approval"]=[];
				$resultarr["role_id_approved"]=[];
				$resultarr["role_id_rejected"]=[];
				foreach($rolemodal as $role)
				{
					$roleArray[]=$role->role_id;
					$roleNameArray[]=$role->role->role_name;
					$roleChkNameArray[$role->role_id]=$role->role->role_name;

					$roledtArray=array();
					$roleArray[]=$role->role_id;
					$roleNameArray[]=$role->role->role_name;
					$roleChkNameArray[$role->role_id]=$role->role->role_name;

					$roledtArray=array();
					$roledtArray['user_role_id']=$role->id;
					$roledtArray['role_id']=$role->role_id;
					$roledtArray['role_name']=$role->role->role_name;
					$roledtArray['username']=$role->username;
					$roledtArray['from_db']=1;
					$roledtArray['deleted']=0;
					$roledtArray['editable']=0;
					$roledtArray['approval_status']=$role->approval_status;
					
					$roledtArray['franchise_name']= 'OSS '.$role->franchise->usercompanyinfo->osp_number.' - '.$role->franchise->usercompanyinfo->osp_details;//$role->franchise->usercompanyinfo->company_name.' ('.$role->franchise->usercompanyinfo->companycountry->name.')';
					$roledtArray['franchise_id']= $role->franchise->id;


					$roledtArray['approval_status']=$role->approval_status;
					$roledtArray['approval_comment']=$role->approval_comment;
					$roledtArray['approval_by_name']=$role->approvaluser?$role->approvaluser->first_name.' '.$role->approvaluser->last_name:'';
					$roledtArray['approval_date']=date($date_format,$role->approval_date);

							
					if($role->approval_status==1){
						$resultarr["role_id_waiting_approval"][]=$roledtArray;
					}else if($role->approval_status==2){
						$resultarr["role_id_approved"][]=$roledtArray;
					}if($role->approval_status==3){
						$resultarr["role_id_rejected"][]=$roledtArray;
					}

				}
			}
			*/
			
		}
		else if($data['type']=='businessgroup'){
			//echo 'dsfsdf'; die;
			if(!Yii::$app->userrole->hasRights(array('business_group_approval')))
			{
				return false;
			}
			$bgroupmodel=UserBusinessGroupCode::find()->where(['id' => $data['id']])->one();

			// if($data['status']!='2')
			// {
			// 	$businessgroupmodel = UserBusinessGroup::find()->where(['id' => $bgroupmodel->business_group_id])->one();
			// 	$bgrouphistorymodal = new UserBusinessGroupCodeHistory();
			// 	$bgrouphistorymodal->user_business_group_code_id = $bgroupmodel->id;
			// 	$bgrouphistorymodal->user_id=$businessgroupmodel->user_id;
			// 	$bgrouphistorymodal->standard_id=$businessgroupmodel->standard_id;
			// 	$bgrouphistorymodal->business_sector_id=$businessgroupmodel->business_sector_id;
			// 	$bgrouphistorymodal->business_group_id=$bgroupmodel->business_group_id;
			// 	$bgrouphistorymodal->business_sector_group_id=$bgroupmodel->business_sector_group_id;
			// 	$bgrouphistorymodal->academic_qualification_status=$businessgroupmodel->academic_qualification_status;
			// 	$bgrouphistorymodal->exam_file=$businessgroupmodel->exam_file?:'';
			// 	$bgrouphistorymodal->technical_interview_file=$businessgroupmodel->technical_interview_file?:'';
			// 	$bgrouphistorymodal->status = $bgroupmodel->status;
			// 	$bgrouphistorymodal->status_change_by = $bgroupmodel->status_change_by;
			// 	$bgrouphistorymodal->status_change_date = $bgroupmodel->status_change_date;
			// 	$bgrouphistorymodal->status_change_comment = $bgroupmodel->status_change_comment;
			// 	$bgrouphistorymodal->created_by = $businessgroupmodel->created_by;
			// 	$bgrouphistorymodal->created_at = $businessgroupmodel->created_at;
			// 	$bgrouphistorymodal->save();
			// }

			$bgroupmodel->approval_date = date('Y-m-d');
			$bgroupmodel->approval_by = $userid;
			$bgroupmodel->status = $data['status'];
			$bgroupmodel->status_change_by = $userid;
			$bgroupmodel->status_change_date = time();
			$bgroupmodel->status_change_comment = $data['comment'];
			$bgroupmodel->save();
				
			/*
			$businessGroupArray=[];
			$businessGroupNameArray=[];
			
			$command = $connection->createCommand("SELECT std.name as standard_name,gp.id, gp.`business_sector_id`,master_gp.name,
				gp.standard_id,std.name, master_gp.name as businesssector_name, gp.academic_qualification_status,
				gp.exam_file,gp.technical_interview_file,
				GROUP_CONCAT(gpcode.`business_sector_group_id`) as business_sector_group_ids,
				GROUP_CONCAT(master_gpcode.`group_code`) as group_codes,
				GROUP_CONCAT(gpcode.`id`) as business_sector_group_code_ids,

				GROUP_CONCAT(gpcode.`status_change_comment` separator '||SP||') as status_change_comment,
				GROUP_CONCAT(gpcode.`status_change_date` separator '||SP||') as status_change_date,
				GROUP_CONCAT(gpcode.`status_change_by` separator '||SP||') as status_change_by,

				gpcode.status  
				FROM `tbl_user_business_group` as gp 
				inner join `tbl_user_business_group_code` as gpcode on gp.id=gpcode.`business_group_id` 
				inner join `tbl_business_sector` as master_gp on master_gp.id=gp.`business_sector_id` 
				inner join `tbl_business_sector_group` as master_gpcode on master_gpcode.id=gpcode.`business_sector_group_id`  
				inner join `tbl_standard` as std on std.id=gp.standard_id 
				WHERE `user_id`=".$data['user_id']." group by gpcode.status,gpcode.`business_group_id`");
			$businessGroupmodel = $command->queryAll();
			if(count($businessGroupmodel)>0){

				$resultarr["businessgroup_rejected"] = [];
				$resultarr["businessgroup_approved"] = [];
				$resultarr["businessgroup_approvalwaiting"] = [];
				$resultarr["businessgroup_new"] = [];
				foreach($businessGroupmodel as $bgroup)
				{
					
					$status_change_by = explode('||SP||',$bgroup['status_change_by']);
					$status_change_by_name = [];
					if(count($status_change_by)>0){
						foreach($status_change_by as $userchangeid){
							$Usermodel = User::find()->where(['id' => $userchangeid])->one();
							if ($Usermodel !== null)
							{
								$status_change_by_name[] = $Usermodel->first_name.' '.$Usermodel->last_name;
							}
						}
					}
					
					$roledtArray=array();
					$roledtArray['id']=$bgroup['id'];
					$roledtArray['standard_id']=$bgroup['standard_id'];
					$roledtArray['business_sector_id']=$bgroup['business_sector_id'];
					//$roledtArray['business_sector_group_id']=$bgroup->business_sector_group_id;
					
					
					$roledtArray['standard_name']=$bgroup['standard_name'];
					$roledtArray['business_sector_name']=$bgroup['businesssector_name'];
					
					
					
					$roledtArray['academic_qualification']=$bgroup['academic_qualification_status'];
					$roledtArray['academic_qualification_name']=$bgroup['academic_qualification_status']==1?'Yes':'No';
					$roledtArray['examfilename']=$bgroup['exam_file'];
					$roledtArray['technicalfilename']=$bgroup['technical_interview_file'];
					

					$groupcodeArr = $bgroup['business_sector_group_ids'];
					$groupnamecodeArr = $bgroup['group_codes'];
					$groupcodeIdArr = $bgroup['business_sector_group_ids'];
					$roledtArray['business_sector_group_id'] = $groupcodeArr;
					$roledtArray['business_sector_group_name'] = $groupnamecodeArr;
					$roledtArray['business_sector_group_name_arr'] = explode(',',$groupnamecodeArr);
					$roledtArray['business_sector_group_code_id'] = explode(',',$bgroup['business_sector_group_code_ids']);
					$roledtArray['business_sector_group_id_arr'] = explode(',',$groupcodeArr);
					
					$roledtArray['status']=$bgroup['status'];
					$roledtArray['approval_comment']= explode('||SP||',$bgroup['status_change_comment']);
					$roledtArray['approval_by_name']=$status_change_by_name;
					$status_change_datearr = explode('||SP||',$bgroup['status_change_date']);
					foreach($status_change_datearr as $key => $datea){
						$status_change_datearr[$key] = date($date_format,$datea);
					}
					$roledtArray['approval_date']=$status_change_datearr;


					foreach($roledtArray['business_sector_group_id_arr'] as $bgpid){
						$roledtArray["rejected_history"][$bgpid] = [];
					}
					
					$bgrouphistorymodel = UserBusinessGroupCodeHistory::find()->where(['business_sector_id' => $bgroup['business_sector_id'],'user_id'=>$data['user_id']])->all();
					if(count($bgrouphistorymodel)>0)
					{

						$bgrouphistory_arr = array();
						foreach($bgrouphistorymodel as $bgrouphistory)
						{
							$busgrouphistory_arr = array();
							$busgrouphistory_arr['id']=$bgrouphistory->business_group_id;
							$busgrouphistory_arr['standard_name']=$bgrouphistory->standard->name;
							$busgrouphistory_arr['business_sector_name']=$bgrouphistory->businesssector->name;
							$busgrouphistory_arr['business_sector_group_name']=($bgrouphistory->userbusinessgroupcode && $bgrouphistory->userbusinessgroupcode->sectorgroup?$bgrouphistory->userbusinessgroupcode->sectorgroup->group_code:'');
							$busgrouphistory_arr['academic_qualification']=$bgrouphistory->academic_qualification_status;
							$busgrouphistory_arr['academic_qualification_name']=$bgrouphistory->academic_qualification_status==1?'Yes':'No';
							$busgrouphistory_arr['examfilename']=$bgrouphistory->exam_file?:"";
							$busgrouphistory_arr['technicalfilename']=$bgrouphistory->technical_interview_file?:"";
							$busgrouphistory_arr['status_change_by']=$bgrouphistory->approvaluser->first_name." ".$bgrouphistory->approvaluser->last_name;
							$busgrouphistory_arr['status_change_comment']=$bgrouphistory->status_change_comment?:"";
							$busgrouphistory_arr['status_change_date']=date($date_format,$bgrouphistory->status_change_date);
							//$bgrouphistory_arr[]=$busgrouphistory_arr;
							$roledtArray["rejected_history"][$bgrouphistory->business_sector_group_id][] = $busgrouphistory_arr;
						}
						//$roledtArray[$bgrouphistory->business_sector_group_id]["rejected_history"][] = $bgrouphistory_arr;
					}
							
					//$businessGroupArray[]= $roledtArray;
					if($bgroup['status']==3){
						$resultarr["businessgroup_rejected"][]= $roledtArray;
					}else if($bgroup['status']==2){
						$resultarr["businessgroup_approved"][]= $roledtArray;
					}else if($bgroup['status']==1){
						$resultarr["businessgroup_approvalwaiting"][]= $roledtArray;
					}else if($bgroup['status']==0){
						$resultarr["businessgroup_new"][]= $roledtArray;
					}
					
				}
			}
			
			*/
		}else if($data['type']=='te_business_group'){
			//echo 'dsfsdf'; die;
			if(!Yii::$app->userrole->hasRights(array('tebusiness_group_approval')))
			{
				return false;
			}
			$bgroupmodel=UserRoleTechnicalExpertBsCode::find()->where(['id' => $data['id']])->one();
			$bgroupmodel->status = $data['status'];
			$bgroupmodel->approval_by = $userid;
			$bgroupmodel->approval_date = time();
			$bgroupmodel->approval_comment = $data['comment'];
			$bgroupmodel->save();

		}else if($data['type']=='standard'){
			if(!Yii::$app->userrole->hasRights(array('standard_approval')))
			{
				return false;
			}
			$stdmodel=UserStandard::find()->where(['id' => $data['id']])->one();
			$stdmodel->approval_status = $data['status'];
			$stdmodel->approval_by = $userid;
			$stdmodel->approval_date = time();
			$stdmodel->approval_comment = $data['comment'];
			//$data['status'] ==2
			$stdmodel->witness_date=(isset($data['witness_date']) && $data['witness_date']!='' )?date('Y-m-d',strtotime($data['witness_date'])):'';
			if(isset($_FILES['witness_file']['name']))
			{	
				$tmp_name = $_FILES["witness_file"]["tmp_name"];
				$name = $_FILES["witness_file"]["name"];
				if($stdmodel->witness_file != ''){
					Yii::$app->globalfuns->removeFiles($stdmodel->witness_file,$target_dir);
				}   
				$stdmodel->witness_file =Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
				/*
				$filename = $_FILES['witness_file']['name'];
				$target_file = $target_dir . basename($filename);
				$actual_name = pathinfo($filename,PATHINFO_FILENAME);
				$original_name = $actual_name;
				$extension = pathinfo($filename, PATHINFO_EXTENSION);
				$i = 1;
				$name = $actual_name.".".$extension;
				while(file_exists($target_dir.$actual_name.".".$extension))
				{           
					$actual_name = (string)$original_name.$i;
					$name = $actual_name.".".$extension;
					$i++;
				}
				if (move_uploaded_file($_FILES['witness_file']["tmp_name"], $target_dir .$actual_name.".".$extension)) {
					$stdmodel->witness_file=isset($name)?$name:"";
				}
				*/
			}
			if($data['status'] ==2){
				
				$stdmodel->witness_valid_until = (isset($data['valid_until']) && $data['valid_until']!='' )?date('Y-m-d',strtotime($data['valid_until'])):'';
				//$stdmodel->witness_comment=(isset($data['witness_comment']) && $data['witness_comment']!='' )?$data['witness_comment']:'';
				//print_r($_FILES);
				
			}
			$stdmodel->save();
				

			/*
			$resultarr["standard_approvalwaiting"] = [];
			$resultarr["standard_approved"] = [];
			$resultarr["standard_rejected"] = [];
			
			$standardArray = [];
			$standardChkNameArray = [];
			$standardmodel = UserStandard::find()->where(['user_id' => $data['user_id']])->all();
			if(count($standardmodel)>0)
			{
				foreach($standardmodel as $standard)
				{
					$stds=[];
					$stds["id"]=$standard->id;
					$stds["standard"]=$standard->standard_id;
					$stds["standard_name"]=$standard->standard->name;
					$stds["standard_code"]=$standard->standard->code;
					//$stds["standard_exam_date"]=date($date_format,strtotime($standard->standard_exam_date));
					///$stds["recycle_exam_date"]=date($date_format,strtotime($standard->recycle_exam_date));
					//$stds["social_course_exam_date"]=date($date_format,strtotime($standard->social_course_exam_date));
					$stds["standard_exam_file"]=$standard->standard_exam_file;
					$stds["recycle_exam_file"]=$standard->recycle_exam_file;
					$stds["social_course_exam_file"]=$standard->social_course_exam_file;

					$stds["witness_file"]=$standard->witness_file;
					$stds["witness_valid_until"]=($standard->witness_valid_until !='0000-00-00' && $standard->witness_valid_until !=null && $standard->witness_valid_until!='1970-01-01')?date($date_format,strtotime($standard->witness_valid_until)):'';
								$stds["witness_comment"]=$standard->witness_comment?:'';

					$stds["standard_exam_date"]=($standard->standard_exam_date !='0000-00-00' && $standard->standard_exam_date !=null && $standard->standard_exam_date!='1970-01-01')?date($date_format,strtotime($standard->standard_exam_date)):'';
					$stds["recycle_exam_date"]=($standard->recycle_exam_date !='0000-00-00' && $standard->recycle_exam_date !=null && $standard->recycle_exam_date!='1970-01-01')?date($date_format,strtotime($standard->recycle_exam_date)):'';
					$stds["social_course_exam_date"]=($standard->social_course_exam_date !='0000-00-00' && $standard->social_course_exam_date !=null && $standard->social_course_exam_date!='1970-01-01')?date($date_format,strtotime($standard->social_course_exam_date)):'';
					$stds["witness_date"]=($standard->witness_date !='0000-00-00' && $standard->witness_date !=null && $standard->witness_date!='1970-01-01')?date($date_format,strtotime($standard->witness_date)):'';
					
					
					$stds["status"]=$standard->approval_status;
					$stds["approval_comment"]=$standard->approval_comment?:'';
					$stds["approval_date"]=($standard->approval_date !='0000-00-00' && $standard->approval_date !=null && $standard->approval_date!='1970-01-01')?date($date_format,$standard->approval_date):'';
					$stds["approval_by_name"]=$standard->approvaluser?$standard->approvaluser->first_name.' '.$standard->approvaluser->last_name:'';

					
					//$standardArray[]=$stds;
					if($standard->approval_status ==1){
						$resultarr["standard_approvalwaiting"][] = $stds;
					}else if($standard->approval_status ==2){
						$resultarr["standard_approved"][] = $stds;
					}else if($standard->approval_status ==3){
						$resultarr["standard_rejected"][] = $stds;
					}
					
					
					
					$standardChkNameArray[$standard->standard_id] = $standard->standard->name;
				}
			}
			*/
		}
		

		return ['status'=>1,'message'=>$response_str.' Successfully', 'data'=>$resultarr];
	}

	public function actionUserbusinesssectors(){
		$data = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];

		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		
		$businessSector = [];
		$UserBusinessSector = UserBusinessGroup::find()->where(['standard_id'=>$data['standard_id'], 'user_id'=>$data['user_id']])->groupBy(['business_sector_id'])->all();
		if(count($UserBusinessSector)>0){
			foreach($UserBusinessSector as $bsector){
				if(count($bsector->groupcodeactive)>0){
					$businessSector[] = ['name'=>$bsector->businesssector->name,'id'=>$bsector->business_sector_id];
				}
			}
		}
		$responsedata = ['status'=>1,'data'=>$businessSector];
		return $responsedata;

	}

	public function actionUserbusinesssectorgroups(){
		$data = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];

		$standard_id = isset($data['standard_id'])?$data['standard_id']:0;
		$role_id = isset($data['role_id'])?$data['role_id']:0;
		$id = isset($data['id'])? $data['id']:0;
		$user_id = isset($data['user_id'])? $data['user_id']:0;

		$businessSector = [];
		//$chkGpCode = 
		$existbgroupcode = [];

		if($user_id>0){
			$UserRoleBusinessGroupCode = UserRoleBusinessGroupCode::find()->alias('t')->innerJoinWith('rolebusinessgroup as rolebusinessgroup')->where(['rolebusinessgroup.user_id'=>$user_id,'rolebusinessgroup.role_id'=>$role_id]);
			if($id!='' && $id>0){
				$UserRoleBusinessGroupCode = $UserRoleBusinessGroupCode->andWhere(['!=','rolebusinessgroup.id',$id]);
			}
			$UserRoleBusinessGroupCode = $UserRoleBusinessGroupCode->all();
			
			if(count($UserRoleBusinessGroupCode)>0){
				foreach($UserRoleBusinessGroupCode as $bcode){
					$existbgroupcode[] = $bcode->business_sector_group_id;
				}
			}
		}
		

		$UserBusinessSector = UserBusinessGroup::find()->where(['standard_id'=>$standard_id, 'business_sector_id'=>$data['business_group_id'], 'user_id'=>$data['user_id']])->all();
		
		if(count($UserBusinessSector)>0){
			foreach($UserBusinessSector as $bsector){
				$UserBusinessSectorGroup = UserBusinessGroupCode::find()->where(['t.business_group_id'=>$bsector->id,'usersector.user_id'=>$data['user_id'],'t.status'=>2 ])->alias('t')->innerJoinWith('usersector as usersector');
				if(count($existbgroupcode)>0){
					$UserBusinessSectorGroup = $UserBusinessSectorGroup->andWhere(['not in','t.business_sector_group_id', $existbgroupcode ]);
				}
				$UserBusinessSectorGroup = $UserBusinessSectorGroup->all();
				if(count($UserBusinessSectorGroup)>0){
					foreach($UserBusinessSectorGroup as $bsectorgp){
						$businessSector[] = ['group_code'=>$bsectorgp->sectorgroup->group_code,'id'=>$bsectorgp->business_sector_group_id];
					}
				}
			}
		}
		
		$responsedata = ['status'=>1,'data'=>$businessSector];
		return $responsedata;
	}

	public function actionGetUserData(){
		$data = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];

		$getdata = ['user_id'=>$data['id'],'actiontype'=> $data['actiontype'] ];
		$returndata = $this->getUserDetails($getdata);

		return ['status'=>1,'data'=>$returndata];
	}


	public function getUserDetails($datas){


		$user_id = $datas['user_id'];
		$actiontype = $datas['actiontype'];
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$responsedata=array('status'=>0,'message'=>'Something went wrong!');

		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];

		$resultarr = [];
		
		$connection = Yii::$app->getDb();

		
		if($actiontype =='role')
		{
			$roleArray=[];
			$roleNameArray=[];
			$resultarr["role_id"] = [];
			$resultarr["role_id_waiting_approval"] = [];
			$resultarr["role_id_approved"] = [];
			$resultarr["role_id_rejected"] = [];
			$resultarr["role_id_map_user"] = [];

			$chkRoleIds = [];

			$rolemodel = UserRole::find()->where(['user_id' => $user_id]);
			
			if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
				if($resource_access == '5'){
					$chkuserid = $franchiseid;
					$rolemodel = $rolemodel->andWhere(' franchise_id="'.$chkuserid.'" ');
				}else{
					$rolemodel = $rolemodel->andWhere(' franchise_id="'.$userid.'" ');
				}
			}
			$rolemodel = $rolemodel->orderBy(['id'=>SORT_DESC])->all();


			if(count($rolemodel)>0)
			{
				foreach($rolemodel as $role)
				{
					
					$roledtArray=array();
					$roleArray[$role->role_id]=$role->role_id;
					$roleNameArray[]=$role->role->role_name;
					$roleChkNameArray[$role->role_id]=$role->role?$role->role->role_name:'';

					$roledtArray=array();
					$roledtArray['id']=$role->id;
					$roledtArray['user_role_id']=$role->id;
					$roledtArray['role_id']=$role->role_id;
					$roledtArray['role_name']=$role->role->role_name;
					$roledtArray['resource_access']=$role->role->resource_access;
					$roledtArray['user_role_type']=$role->role->resource_access ==3 || $role->role->resource_access ==4 ?0:1;
					$roledtArray['username']=$role->username;
					$roledtArray['from_db']=1;
					$roledtArray['deleted']=0;
					$roledtArray['editable']=0;
					$roledtArray['status']=$role->status;
					$roledtArray['approval_status']=$role->approval_status;
					$roledtArray['approval_comment']=$role->approval_comment;
					$roledtArray['approval_by_name']=$role->approvaluser?$role->approvaluser->first_name.' '.$role->approvaluser->last_name:'';
					$roledtArray['approval_date']=date($date_format,$role->approval_date);

					$roledtArray['franchise_name']= 'OSS '.$role->franchise->usercompanyinfo->osp_number.' - '.$role->franchise->usercompanyinfo->osp_details;//$role->franchise->usercompanyinfo->company_name.' ('.$role->franchise->usercompanyinfo->companycountry->name.')';
					$roledtArray['franchise_id']= $role->franchise->id;
					//$roleArray['exp_to_date']=date($date_format,strtotime($role->to_date));
					//$roleArray['exp_years']=$experience->year;
					if($role->approval_status == 0){
						$resultarr["role_id"][]=$roledtArray;	
					}else if($role->approval_status == 1){
						$resultarr["role_id_waiting_approval"][]=$roledtArray;	
					}else if($role->approval_status == 2){
						$resultarr["role_id_approved"][]=$roledtArray;
						if(!in_array($role->role_id,$chkRoleIds)){
							//$Rule = '';
							//$Rule = Rule::find()->where(['privilege'=>['application_review','audit_review','certification_review'],'role_id'=>$role->role_id])->one();
							//$Rule === null && 
							//if($role->role->resource_access != 3  ){
							if($role->role->resource_access == 1 || $role->role->resource_access == 2){
								$chkRoleIds[] = $role->role_id;
								$resultarr["role_id_map_user"][]=['role_id'=>$role->role_id,'role_name'=>$role->role->role_name,'user_role_id'=>$role->id];
							}
						}
						
					}else if($role->approval_status == 3){
						$resultarr["role_id_rejected"][]=$roledtArray;	
					}
					//$resultarr["role_id"][]=$roledtArray;
				}
			}

			$is_auditor =0;
			$connection = Yii::$app->getDb();
			$command = $connection->createCommand("SELECT user_role.user_id FROM tbl_user_role as user_role 
				INNER JOIN `tbl_rule` AS rule ON  user_role.role_id=rule.role_id AND rule.privilege='audit_execution' 
						where user_role.user_id=".$user_id);
			$result = $command->queryAll();
			if(count($result)>0){
				$is_auditor = 1;
			}

			$resultarr["is_auditor"] = $is_auditor;
			$resultarr["roleDetails"] = $roleNameArray;
		}




		if($actiontype =='business_group' || $actiontype =='business_group_code')
		{
			$businessGroupArray=[];
			$businessGroupNameArray=[];
			$resultarr["businessgroup_new"] = [];
			$resultarr["businessgroup_approvalwaiting"] = [];
			$resultarr["businessgroup_approved"] = [];
			$resultarr["businessgroup_rejected"] = [];

			//$businessGroupmodel = UserBusinessGroup::find()->where(['user_id' => $data['id']])->all();

			$command = $connection->createCommand("SELECT std.name as standard_name,gp.id, gp.`business_sector_id`,master_gp.name,
					gp.standard_id,std.name, master_gp.name as businesssector_name, gp.academic_qualification_status,
					gp.exam_file,gp.technical_interview_file,
					GROUP_CONCAT(gpcode.`business_sector_group_id`) as business_sector_group_ids,
					GROUP_CONCAT(master_gpcode.`group_code`) as group_codes,
					GROUP_CONCAT(gpcode.`id`) as business_sector_group_code_ids,

					GROUP_CONCAT(gpcode.`approval_date` separator '||SP||') as approval_date,
					GROUP_CONCAT(gpcode.`approval_by` separator '||SP||') as approval_by,
					GROUP_CONCAT(gpcode.`status_change_comment` separator '||SP||') as status_change_comment,
					GROUP_CONCAT(gpcode.`status_change_date` separator '||SP||') as status_change_date,
					GROUP_CONCAT(gpcode.`status_change_by` separator '||SP||') as status_change_by,

					gpcode.status 
					FROM `tbl_user_business_group` as gp 
					inner join `tbl_user_business_group_code` as gpcode on gp.id=gpcode.`business_group_id` 
					inner join `tbl_business_sector` as master_gp on master_gp.id=gp.`business_sector_id` 
					inner join `tbl_business_sector_group` as master_gpcode on master_gpcode.id=gpcode.`business_sector_group_id`  
					inner join `tbl_standard` as std on std.id=gp.standard_id 
					WHERE `user_id`=".$user_id." group by gpcode.status,gpcode.`business_group_id` ");
			$businessGroupmodel = $command->queryAll();
			if(count($businessGroupmodel)>0)
			{
				foreach($businessGroupmodel as $bgroup)
				{
					//$roleArray[]=$bgroup->role_id;
					//$roleNameArray[]=$bgroup->role->role_name;
					$status_change_by = explode('||SP||',$bgroup['status_change_by']);
					$status_change_by_name = [];
					if(count($status_change_by)>0){
						foreach($status_change_by as $userchangeid){
							$Usermodel = User::find()->where(['id' => $userchangeid])->one();
							if ($Usermodel !== null)
							{
								$status_change_by_name[] = $Usermodel->first_name.' '.$Usermodel->last_name;
							}
						}
					}


					$approval_by = explode('||SP||',$bgroup['approval_by']);
					$approval_by_name = [];
					if(count($approval_by)>0){
						foreach($approval_by as $userapproveid){
							$Usermodel = User::find()->where(['id' => $userapproveid])->one();
							if ($Usermodel !== null)
							{
								$approval_by_name[] = $Usermodel->first_name.' '.$Usermodel->last_name;
							}
						}
					}
					
					$roledtArray=array();
					$roledtArray['id']=$bgroup['id'];
					$roledtArray['standard_id']=$bgroup['standard_id'];
					$roledtArray['business_sector_id']=$bgroup['business_sector_id'];
					//$roledtArray['business_sector_group_id']=$bgroup->business_sector_group_id;
					
					
					$roledtArray['standard_name']=$bgroup['standard_name'];
					$roledtArray['business_sector_name']=$bgroup['businesssector_name'];
					
					
					
					$roledtArray['academic_qualification']=$bgroup['academic_qualification_status'];
					$roledtArray['academic_qualification_name']=$bgroup['academic_qualification_status']==1?'Yes':'No';
					$roledtArray['examfilename']=$bgroup['exam_file'];
					$roledtArray['technicalfilename']=$bgroup['technical_interview_file'];
					

					$groupcodeArr = $bgroup['business_sector_group_ids'];
					$groupnamecodeArr = $bgroup['group_codes'];
					$groupcodeIdArr = $bgroup['business_sector_group_ids'];
					$roledtArray['business_sector_group_id'] = explode(',',$groupcodeArr);//$groupcodeArr;
					$roledtArray['business_sector_group_id_arr'] = explode(',',$groupcodeArr);
					$roledtArray['business_sector_group_name'] = $groupnamecodeArr;
					$roledtArray['business_sector_group_name_arr'] = explode(',',$groupnamecodeArr);
					$roledtArray['business_sector_group_code_id'] = explode(',',$bgroup['business_sector_group_code_ids']);

					$roledtArray['status']=$bgroup['status'];
					$roledtArray['approval_comment']= explode('||SP||',$bgroup['status_change_comment']);
					$roledtArray['approval_by_name']=$status_change_by_name;
					$roledtArray['bscode_approval_by_name']=$approval_by_name;
					$status_change_datearr = explode('||SP||',$bgroup['status_change_date']);
					if($bgroup['status_change_date']!='' && count($status_change_datearr)>0){
						foreach($status_change_datearr as $key => $datea){
							$status_change_datearr[$key] = date($date_format,$datea);
						}
					}else{
						$status_change_datearr = [];
					}

					$approval_datearr = explode('||SP||',$bgroup['approval_date']);
					if($bgroup['approval_date']!='' && count($approval_datearr)>0){
						foreach($approval_datearr as $key => $datea){
							if($datea!='')
							{
								$approval_datearr[$key] = date($date_format,strtotime($datea));
							}
							else
							{
								$approval_datearr[$key] = '';
							}
						}
					}else{
						$approval_datearr = [];
					}
					
					
					$roledtArray['approval_date']=$status_change_datearr;
					$roledtArray['bscode_approval_date']=$approval_datearr;


					foreach($roledtArray['business_sector_group_id_arr'] as $bgpid){
						$roledtArray["rejected_history"][$bgpid] = [];
					}
					


					//echo $bgroup['id'];exit;
					$bgrouphistorymodel = UserBusinessGroupCodeHistory::find()->where(['business_sector_id' => $bgroup['business_sector_id'],'user_id'=>$user_id])->all();
					if(count($bgrouphistorymodel)>0)
					{
						$bgrouphistory_arr = array();
						foreach($bgrouphistorymodel as $bgrouphistory)
						{
							$busgrouphistory_arr = array();
							$busgrouphistory_arr['id']=$bgrouphistory->business_group_id;
							$busgrouphistory_arr['standard_name']=$bgrouphistory->standard->name;
							$busgrouphistory_arr['business_sector_name']=$bgrouphistory->businesssector->name;
							$busgrouphistory_arr['business_sector_group_name']=($bgrouphistory->userbusinessgroupcode && $bgrouphistory->userbusinessgroupcode->sectorgroup?$bgrouphistory->userbusinessgroupcode->sectorgroup->group_code:'');
							$busgrouphistory_arr['academic_qualification']=$bgrouphistory->academic_qualification_status;
							$busgrouphistory_arr['academic_qualification_name']=$bgrouphistory->academic_qualification_status==1?'Yes':'No';
							$busgrouphistory_arr['examfilename']=$bgrouphistory->exam_file?:"";
							$busgrouphistory_arr['technicalfilename']=$bgrouphistory->technical_interview_file?:"";
							$busgrouphistory_arr['status_change_by']=$bgrouphistory->approvaluser->first_name." ".$bgrouphistory->approvaluser->last_name;
							$busgrouphistory_arr['status_change_comment']=$bgrouphistory->status_change_comment?:"";
							$busgrouphistory_arr['status_change_date']=date($date_format,$bgrouphistory->status_change_date);
							//$bgrouphistory_arr[]=$busgrouphistory_arr;
							$roledtArray["rejected_history"][$bgrouphistory->business_sector_group_id][] = $busgrouphistory_arr;
						}
						//$roledtArray[$bgrouphistory->business_sector_group_id]["rejected_history"][] = $bgrouphistory_arr;
					}
					
					
					if($bgroup['status']==0){
						$resultarr["businessgroup_new"][] = $roledtArray;
					}else if($bgroup['status']==1){
						$resultarr["businessgroup_approvalwaiting"][] = $roledtArray;
					}else if($bgroup['status']==2){
						$resultarr["businessgroup_approved"][] = $roledtArray;
					}else if($bgroup['status']==3){
						$resultarr["businessgroup_rejected"][] = $roledtArray;
					}

					
				}
			}


			$resultarr["business_sector_group_id"]=[];
			$resultarr["bsectorgroup_label"]= [];
			$resultarr["business_sector_id"]= [];
			$resultarr["bsector_label"]=[];

			$bsector_arr = [];
			$bsector_label_arr = [];
			$bsector_label_arr_names = [];
			$questbsectors = UserBusinessGroup::find()->where(['user_id' => $user_id])->orderBy(['id'=>SORT_DESC])->all();
			if(count($questbsectors)>0)
			{
				$bsector_arr = array();
				$bsector_label_arr = array();
				foreach($questbsectors as $val)
				{
					$bsector_arr[]=$val['business_sector_id'];
					$bsector_label_arr[]=$val->businesssector->name;
					$bsector_label_arr_names[$val['business_sector_id']] = $val->businesssector->name;
					$bsectorgroup_arr= [];
					$bsectorgroup_label_arr= [];
					foreach($val->groupcode as $gcval)
					{
						$bsectorgroup_arr[]=$gcval->business_sector_group_id;
						$bsectorgroup_label_arr[]=$gcval->sectorgroup->group_code;
					}
					$resultarr["business_sector_group_id"]=$bsectorgroup_arr;
					$resultarr["bsectorgroup_label"]=$bsectorgroup_label_arr;

				}
				$resultarr["business_sector_id"]=$bsector_arr;
				$resultarr["bsector_label"]=$bsector_label_arr;
			}
		}
							
		


		if($actiontype =='standard' || $actiontype =='standards')
		{
			$resultarr["standard_rejected"] = [];
			$resultarr["standard_approvalwaiting"] = [];
			$resultarr["standard_approved"] = [];
			$resultarr["standard"] = [];


			$standardArray = [];
			$standardChkNameArray = [];
			$standardIdExcept = [];
			$standardmodel = UserStandard::find()->where(['user_id' => $user_id])
										->orderBy(['id'=>SORT_DESC])->all();
			if(count($standardmodel)>0)
			{
				foreach($standardmodel as $standard)
				{
					$stds=[];
					$stds["id"]=$standard->id;
					$stds["status"]=$standard->approval_status;
					$stds["standard"]=$standard->standard_id;
					$stds["standard_name"]=$standard->standard->name;
					$stds["standard_code"]=$standard->standard->code;
					$stds["pre_qualification"]=$standard->pre_qualification;
					$stds["standard_exam_date"]=($standard->standard_exam_date !='0000-00-00' && $standard->standard_exam_date !=null && $standard->standard_exam_date!='1970-01-01')?date($date_format,strtotime($standard->standard_exam_date)):'';
					$stds["recycle_exam_date"]=($standard->recycle_exam_date !='0000-00-00' && $standard->recycle_exam_date !=null && $standard->recycle_exam_date!='1970-01-01')?date($date_format,strtotime($standard->recycle_exam_date)):'';
					$stds["social_course_exam_date"]=($standard->social_course_exam_date !='0000-00-00' && $standard->social_course_exam_date !=null && $standard->social_course_exam_date!='1970-01-01')?date($date_format,strtotime($standard->social_course_exam_date)):'';
					$stds["witness_date"]=($standard->witness_date !='0000-00-00' && $standard->witness_date !=null && $standard->witness_date!='1970-01-01')?date($date_format,strtotime($standard->witness_date)):'';
					$stds["witness_valid_until"]=($standard->witness_valid_until !='0000-00-00' && $standard->witness_valid_until !=null && $standard->witness_valid_until!='1970-01-01')?date($date_format,strtotime($standard->witness_valid_until)):'';
					$stds["witness_comment"]=$standard->witness_comment?:'';
					
					$stds["standard_exam_file"]=$standard->standard_exam_file?:'';
					$stds["recycle_exam_file"]=$standard->recycle_exam_file?:'';
					$stds["social_course_exam_file"]=$standard->social_course_exam_file?:'';
					$stds["witness_file"]=$standard->witness_file?:'';
					$stds['qua_exam_file']=$standard->qua_exam_file?:'';
					$standardArray[]=$stds;

					$standardhistorymodel = UserStandardHistory::find()->where(['user_standard_id' => $standard->id])->all();
					if(count($standardhistorymodel)>0)
					{
						$stdhistory_arr = array();
						foreach($standardhistorymodel as $stdhistory)
						{
							$standard_arr = array();
							$standard_arr["history_id"] = $stdhistory->user_standard_id;
							$standard_arr["status"] = $stdhistory->approval_status;
							$standard_arr["standard"]=$stdhistory->standard_id;
							$standard_arr["standard_name"]=$stdhistory->standard->name;
							$standard_arr["standard_code"]=$stdhistory->standard->code;
							$standard_arr["standard_exam_date"]=($stdhistory->standard_exam_date !='0000-00-00' && $stdhistory->standard_exam_date !=null && $stdhistory->standard_exam_date!='1970-01-01')?date($date_format,strtotime($stdhistory->standard_exam_date)):'';//date($date_format,strtotime($stdhistory->standard_exam_date));
							$standard_arr["recycle_exam_date"]=($stdhistory->recycle_exam_date !='0000-00-00' && $stdhistory->recycle_exam_date !=null && $stdhistory->recycle_exam_date!='1970-01-01')?date($date_format,strtotime($stdhistory->recycle_exam_date)):'';
							$standard_arr["social_course_exam_date"]=($stdhistory->social_course_exam_date !='0000-00-00' && $stdhistory->social_course_exam_date !=null && $stdhistory->social_course_exam_date!='1970-01-01')?date($date_format,strtotime($stdhistory->social_course_exam_date)):'';
							$standard_arr["witness_date"]=($stdhistory->witness_date !='0000-00-00' && $stdhistory->witness_date !=null && $stdhistory->witness_date!='1970-01-01')?date($date_format,strtotime($stdhistory->witness_date)):'';
							$standard_arr["witness_valid_until"]=($stdhistory->witness_valid_until !='0000-00-00' && $stdhistory->witness_valid_until !=null && $stdhistory->witness_valid_until!='1970-01-01')?date($date_format,strtotime($stdhistory->witness_valid_until)):'';
							$standard_arr["witness_comment"]=$stdhistory->witness_comment?:'';
							$standard_arr["standard_exam_file"]=$stdhistory->standard_exam_file?:'';
							$standard_arr["recycle_exam_file"]=$stdhistory->recycle_exam_file?:'';
							$standard_arr["social_course_exam_file"]=$stdhistory->social_course_exam_file?:'';
							$standard_arr["witness_file"]=$stdhistory->witness_file?:'';

							$standard_arr["approval_by"] = ($stdhistory->approval_by!='')?$stdhistory->approvaluser->first_name." ".$stdhistory->approvaluser->last_name:"";
							$standard_arr["approval_date"] = ($stdhistory->approval_date !== NULL)?date($date_format,$stdhistory->approval_date):'';
							$standard_arr["approval_comment"] = ($stdhistory->approval_by!='')?$stdhistory->approval_comment:"";
							$stdhistory_arr[] = $standard_arr; 
						}
						$stds['rejected_history'] = $stdhistory_arr;
					}


					if($standard->approval_status !='0'){
						$standardIdExcept[]=$standard->standard_id;
					}

					if($standard->approval_status =='3'){
						$stds["approval_comment"]=$standard->approval_comment?:'';
						$stds["approval_date"]=($standard->approval_date !='0000-00-00' && $standard->approval_date !=null && $standard->approval_date!='1970-01-01')?date($date_format,$standard->approval_date):'';
						$stds["approval_by_name"]=$standard->approvaluser?$standard->approvaluser->first_name.' '.$standard->approvaluser->last_name:'';
						$resultarr["standard_rejected"][] = $stds;
					}else if($standard->approval_status =='1'){
						$resultarr["standard_approvalwaiting"][] = $stds;
					}else if($standard->approval_status =='2'){

						$stds["approval_comment"]=$standard->approval_comment?:'';
						$stds["approval_date"]=($standard->approval_date !='0000-00-00' && $standard->approval_date !=null && $standard->approval_date!='1970-01-01')?date($date_format,$standard->approval_date):'';
						$stds["approval_by_name"]=$standard->approvaluser?$standard->approvaluser->first_name.' '.$standard->approvaluser->last_name:'';

						$resultarr["standard_approved"][] = $stds;
					}else if($standard->approval_status =='0'){
						$resultarr["standard"][] = $stds;
					}

					$standardChkNameArray[$standard->standard_id] = $standard->standard->name;
				}
				
			}					
			

			$standardNewList = [];
			if(count($standardIdExcept)>0){
				$standardmodel = Standard::find()->where(['not in','id', $standardIdExcept])->andWhere(['status' => 0])->all();
			}else{
				$standardmodel = Standard::find()->where(['status'=>0])->all();
			}
			if(count($standardmodel)>0)
			{
				foreach($standardmodel as $standard)
				{
					$standardNewList[]=['id'=>$standard->id,'name'=>$standard->name,'code'=>$standard->code];
				}
			}
			$resultarr["standardChkNameArray"]=$standardChkNameArray;
			$resultarr["standardNewList"]=$standardNewList;
		}



		if($actiontype =='declaration')
		{

			$declaration_new_arr = [];
			$resultarr["declaration_new"]=[];
			$resultarr["declaration_approvalwaiting"]=[];
			$resultarr["declaration_approved"]=[];
			$resultarr["declaration_rejected"]=[];
			
			$declarationmodal=UserDeclaration::find()->where(['user_id' => $user_id])->orderBy(['id'=>SORT_DESC])->all();
			if(count($declarationmodal)>0)
			{
				foreach($declarationmodal as $dec)
				{	
					$declarations=array();
					$declarations['id']=$dec['id'];
					$declarations['declaration_id']=$dec['id'];
					$declarations['declaration_company']=$dec['company'];
					$declarations['declaration_contract_id']=$dec['contract'];
					$declarations['declaration_contract']=($dec['contract']!='' && $dec['contract']!=0 ? $dec->arrContract[$dec['contract']]:'NA');
					$declarations['declaration_interest']=$dec['interest'];
					$declarations['declaration_start_year']=$dec['start_year'];
					$declarations['declaration_end_year']=$dec['end_year'];
					$declarations['deleted']=0;
					$declarations['relation_consent']=$dec['relation_declaration_consent'];
					$declarations['re_relation_consent']=$dec['re_relation_declaration_consent'];
					$declarations['relation_work']=$dec['relation_work'];
					$declarations['name']=$dec['relation_name'];
					$declarations['type_name']=$dec['relation_type'];
					$declarations['type_name_id']=($dec['relation_type']=='NA' || $dec['relation_type']=='')?'':$dec->arrEnumRelation[$dec['relation_type']];
					$declarations["status"]=$dec['status']?:'';
					$declarations["approval_comment"]=$dec['status_comment']?:'';
					$declarations["approval_date"]=($dec['status_change_date'] !='0000-00-00' && $dec['status_change_date'] !=null && $dec['status_change_date'] !='1970-01-01')?date($date_format,$dec['status_change_date']):'';
					$declarations["approval_by_name"]=$dec->approvaluser?$dec->approvaluser->first_name.' '.$dec->approvaluser->last_name:'';

					

					$declarationhistorymodel = UserDeclarationHistory::find()->where(['user_declaration_id' => $dec['id']])->all();
					if(count($declarationhistorymodel)>0)
					{
						
						$declaratiohistory_arr = array();
						foreach($declarationhistorymodel as $declarationhistory)
						{
							$declaration_arr = array();
							$declaration_arr['history_id']=$declarationhistory->id;
							$declaration_arr['declaration_id']=$declarationhistory->user_declaration_id;
							$declaration_arr['declaration_company']=$declarationhistory->company;
							$declaration_arr['declaration_contract_id']=$declarationhistory->contract;
							$declaration_arr['declaration_contract']=($declarationhistory->contract!='' && $declarationhistory->contract!=0 ? $declarationhistory->arrContract[$declarationhistory->contract]:'NA');
							$declaration_arr['declaration_interest']=$declarationhistory->interest;
							$declaration_arr['declaration_start_year']=$declarationhistory->start_year;
							$declaration_arr['declaration_end_year']=$declarationhistory->end_year;
							$declaration_arr["status"]=$declarationhistory->status?:'';
							$declaration_arr["approval_comment"]=$declarationhistory->status_comment?:'';
							$declaration_arr["approval_date"]=($declarationhistory->status_change_date !='0000-00-00' && $declarationhistory->status_change_date !== NULL)?date($date_format,$declarationhistory->status_change_date):'';
							$declaration_arr["approval_by_name"]=$declarationhistory->approvaluser?$declarationhistory->approvaluser->first_name.' '.$declarationhistory->approvaluser->last_name:'';
							$declaratiohistory_arr[] = $declaration_arr;

						}
						
						$declarations['rejected_history'] = $declaratiohistory_arr;
					}

					if($dec['status'] == '0'){
						$resultarr["declaration_new"][]=$declarations;
					}else if($dec['status'] == '1'){
						$resultarr["declaration_approvalwaiting"][]=$declarations;
					}else if($dec['status'] == '2'){
						$resultarr["declaration_approved"][]=$declarations;
					}else if($dec['status'] == '3'){
						$resultarr["declaration_rejected"][]=$declarations;
					}
				}
				//$resultarr["declaration_new"]=$declaration_new_arr;
			}
		}	

		//print_r($qualificationReviewStatusArr); die;
		if( $actiontype == 'mapuserrole'){
			$UserRoleBusinessGroup = UserRoleBusinessGroup::find()->where(['user_id' => $user_id])->orderBy(['id'=>SORT_DESC])->all();
			if(count($UserRoleBusinessGroup)>0)
			{
				foreach($UserRoleBusinessGroup as $rolegroup)
				{
					$dataArray=array();
					$dataArray['id']=$rolegroup->id;
					$dataArray['user_id']=$rolegroup->user_id;
					$dataArray['role_id']=$rolegroup->role_id;
					$dataArray['standard_id']=$rolegroup->standard_id;
					$dataArray['business_sector_id']=$rolegroup->business_sector_id;
					$dataArray['document']=$rolegroup->document?$rolegroup->document:'';
					$dataArray['role_name']=$rolegroup->role->role_name;
					$dataArray['standard_name']=$rolegroup->standard->name;
					$dataArray['document']=$rolegroup->document?$rolegroup->document:'';
					$dataArray['business_sector_name']=$rolegroup->businesssector->name;
					
					$dataArray['created_by']=$rolegroup->created_by;
					$dataArray['status']=$rolegroup->status;
					
					if(count($rolegroup->rolegroupcode)>0){
						foreach($rolegroup->rolegroupcode as $rolegroupcode){
							$dataArray['business_sector_group_name_arr'][]=$rolegroupcode->sectorgroup->group_code;
							$dataArray['business_sector_group_id_arr'][]=$rolegroupcode->business_sector_group_id;
							$dataArray['business_sector_group_id'][]=$rolegroupcode->business_sector_group_id;
						}
					}else{
						$dataArray['business_sector_group_name_arr']=[];
						$dataArray['business_sector_group_id_arr'] =[];
					}
					
					$resultarr["mapuserrole"][]=$dataArray;
				}
			}else{
				$resultarr["mapuserrole"] = [];
			}
		}

		if($actiontype =='qualification')
		{
			$Qualificationmodel = UserQualification::find()->where(['user_id' => $user_id])->orderBy(['id'=>SORT_DESC])->all();
			if(count($Qualificationmodel)>0)
			{
				foreach($Qualificationmodel as $qualification)
				{
					$qualificationArray=array();
					$qualificationArray['id']=$qualification->id;
					$qualificationArray['qualification']=$qualification->qualification;
					$qualificationArray['university']=$qualification->board_university;
					$qualificationArray['subject']=$qualification->subject;
					$qualificationArray['start_year']=$qualification->start_year;
					$qualificationArray['end_year']=$qualification->end_year;
					$qualificationArray['academic_certificate']=$qualification->certificate;
					//$qualificationArray['percentage']=$qualification->percentage;
					$resultarr["qualifications"][]=$qualificationArray;
				}
			}else{
				$resultarr["qualifications"] = [];
			}
		}
		
		//$Experiencemodel = UserExperience::find()->select('experience,year')->where(['user_id' => $user_id])->asArray()->all();
		//$resultarr["experience"]=$Experiencemodel;
		if($actiontype =='experience')
		{
			$Experiencemodel = UserExperience::find()->select('id,responsibility,job_title,experience,from_date,to_date')->where(['user_id' => $user_id])->orderBy(['id'=>SORT_DESC])->all();
			if(count($Experiencemodel)>0)
			{
				foreach($Experiencemodel as $experience)
				{
					$experienceArray=array();
					$experienceArray['id']=$experience->id;
					$experienceArray['experience']=$experience->experience;
					$experienceArray['job_title']=$experience->job_title;
					$experienceArray['responsibility']=$experience->responsibility;
					$experienceArray['exp_from_date']= date($date_format,strtotime($experience->from_date));
					$experienceArray['exp_to_date']=date($date_format,strtotime($experience->to_date));
					//$experienceArray['exp_years']=$experience->year;
					$resultarr["experience"][]=$experienceArray;
				}
			}else{
				$resultarr["experience"] = [];
			}
		}


		if($actiontype =='audit_experience')
		{
			$auditExperiencemodel = UserAuditExperience::find()->where(['user_id' => $user_id])->orderBy(['id'=>SORT_ASC])->all();
			if(count($auditExperiencemodel)>0)
			{
				foreach($auditExperiencemodel as $auditexperience)
				{
					$auditexperienceArray=array();
					$auditexperienceArray['id']=$auditexperience->id;
					$auditexperienceArray['standard_id']=$auditexperience->standard_id;
					$auditexperienceArray['standard']=$auditexperience->id;
					$auditexperienceArray['standard_name']=isset($auditexperience->auditstandard->standard_name)?$auditexperience->auditstandard->standard_name:'';
                    $auditexperienceArray['sector']=$auditexperience->sector;
                    $auditexperienceArray['sector_name']= $auditexperience->sector;

					$auditexperienceArray['year']=$auditexperience->year;
					$auditexperienceArray['company']=$auditexperience->company;
					$auditexperienceArray['cb']= $auditexperience->cb;
					$auditexperienceArray['cb_name']= isset($auditexperience->cbdetails)?$auditexperience->cbdetails->name:'';
					$auditexperienceArray['auditrolelist_id']=$auditexperience->audit_role;
					$auditexperienceArray['auditrolelist']=($auditexperience->audit_role!='' && $auditexperience->audit_role!=0 ? $auditexperience->arrAuditrolelist[$auditexperience->audit_role]:'NA');
					$auditexperienceArray['days']=$auditexperience->days;
					/*
					$auditexpprocess = $auditexperience->userauditexperienceprocess;
					if(count($auditexpprocess)>0)
					{
						foreach($auditexpprocess as $process)
						{
							// $auditexperienceprocessArray=array();
							// $auditexperienceprocessArray['process']=;
							// $auditexperienceprocessArray['process_name']=;
							$auditexperienceArray["process"][]=$process->process_id;
							$auditexperienceArray["process_name"][]=$process->process->name;
						}	
					}
					*/
					$resultarr["audit_experience"][]=$auditexperienceArray;
				}
			}else{
				$resultarr["audit_experience"] = [];
			}
		}


		if($actiontype =='consultancy_experience')
		{
			$auditconsultancymodel = UserConsultancyExperience::find()->where(['user_id' => $user_id])->orderBy(['id'=>SORT_DESC])->all();
			if(count($auditconsultancymodel)>0)
			{
				foreach($auditconsultancymodel as $auditconsultancy)
				{
					$conexperienceArray=array();
					$conexperienceArray['id']=$auditconsultancy->id;
					$conexperienceArray['standard']=$auditconsultancy->standard_id;
					$conexperienceArray['standard_name']=isset($auditconsultancy->standard->name)?$auditconsultancy->standard->name:"";
					$conexperienceArray['year']=$auditconsultancy->year;
					$conexperienceArray['company']=$auditconsultancy->company;
					$conexperienceArray['days']=$auditconsultancy->days;
					/*
					$conexpprocess = $auditconsultancy->userconsultancyexperienceprocess;
					if(count($conexpprocess)>0)
					{
						foreach($conexpprocess as $process)
						{
							$conexperienceArray["process"][]=$process->process_id;
							$conexperienceArray["process_name"][]=$process->process->name;
						}	
					}
					*/
					$resultarr["consultancy_experience"][]=$conexperienceArray;
				}
			}else{
				$resultarr["consultancy_experience"] = [];
			}
		}

		if($actiontype =='certificate')
		{
			$Certificationmodel = UserCertification::find()->select('id,certification_name,completed_date,filename,training_hours')->where(['user_id' => $user_id])->orderBy(['id'=>SORT_DESC])->all();
			if(count($Certificationmodel)>0)
			{
				foreach($Certificationmodel as $certification)
				{
					$certificationArray=array();
					$certificationArray['id']=$certification->id;
					$certificationArray['certificate_name']=$certification->certification_name;
					$certificationArray['training_hours']=$certification->training_hours;
					$certificationArray['completed_date']=date($date_format,strtotime($certification->completed_date));
					$certificationArray['filename']=$certification->filename;
					$certificationArray['id']=$certification->id;
					$resultarr["certifications"][]=$certificationArray;
				}
			}else{
				$resultarr["certifications"] = [];
			}
		}
		

		if($actiontype =='cpd')
		{
			$Trainingmodel = UserTrainingInfo::find()->select('id,training_hours,subject,training_date')->where(['user_id' => $user_id ])->orderBy(['id'=>SORT_DESC])->all();
			if(count($Trainingmodel)>0)
			{
				foreach($Trainingmodel as $training)
				{
					$trainingArray=array();
					$trainingArray['id']=$training->id;
					$trainingArray['training_subject']=$training->subject;
					$trainingArray['training_date']=$training->training_date;
					$trainingArray['training_hours']=$training->training_hours;
					$resultarr["training_info"][]=$trainingArray;
				}
			}else{
				$resultarr["training_info"] = [];
			}
		}


		if($actiontype =='te_business_group')
		{
			$resultarr["tebusinessgroup_new"] = [];
			$resultarr["tebusinessgroup_approvalwaiting"] = [];
			$resultarr["tebusinessgroup_approved"] = [];
			$resultarr["tebusinessgroup_rejected"] = [];

			//,bscode.approval_comment, bscode.approval_date, bscode.approval_by
			$command = $connection->createCommand("SELECT  role.role_name,bs.role_id,bs.id, bs.`business_sector_id`,master_gp.name,
				master_gp.name as businesssector_name, bs.academic_qualification_status,
							bs.exam_file,bs.technical_interview_file,
				GROUP_CONCAT(bscode.`business_sector_group_id`) as business_sector_group_ids,
				GROUP_CONCAT(master_bscode.`group_code`) as group_codes,
				GROUP_CONCAT(bscode.`id`) as business_sector_group_code_ids,
				bscode.status,

				GROUP_CONCAT(bscode.`approval_comment` separator '||SP||') as approval_comment,
				GROUP_CONCAT(bscode.`approval_date` separator '||SP||') as approval_date,
				GROUP_CONCAT(bscode.`approval_by` separator '||SP||') as approval_by


				FROM `tbl_user_role_technical_expert_business_group` as bs 
				inner join `tbl_role` as role on role.id=bs.`role_id` 
				inner join `tbl_user_role_technical_expert_business_group_code` as bscode on bs.id=bscode.`user_role_technical_expert_bs_id` 
				inner join `tbl_business_sector` as master_gp on master_gp.id=bs.`business_sector_id` 
				inner join `tbl_business_sector_group` as master_bscode on master_bscode.id=bscode.`business_sector_group_id`  
				WHERE `user_id`=".$user_id." group by bscode.user_role_technical_expert_bs_id,bscode.status ");
				//group by bscode.status,bscode.`business_group_id` bs.id
			$businessGroupmodel = $command->queryAll();
			if(count($businessGroupmodel)>0)
			{
				foreach($businessGroupmodel as $bgroup)
				{
					$status_change_by = $bgroup['approval_by'];
					$roledtArray=array();
					$roledtArray['id']=$bgroup['id'];
					
					$roledtArray['business_sector_name']=$bgroup['businesssector_name'];
					$roledtArray['role_name']=$bgroup['role_name'];
					$roledtArray['role_id']=$bgroup['role_id'];
					$roledtArray['business_sector_id']=$bgroup['business_sector_id'];
					
					$groupcodeArr = $bgroup['business_sector_group_ids'];
					$groupnamecodeArr = $bgroup['group_codes'];
					$groupcodeIdArr = $bgroup['business_sector_group_ids'];

					$roledtArray['business_sector_group_id'] = explode(',',$groupcodeArr);//$groupcodeArr;
					$roledtArray['business_sector_group_id_arr'] = explode(',',$groupcodeArr);

					$roledtArray['business_sector_group_name'] = $groupnamecodeArr;
					$roledtArray['business_sector_group_name_arr'] = explode(',',$groupnamecodeArr);
					$roledtArray['business_sector_group_code_id'] = explode(',',$bgroup['business_sector_group_code_ids']);

					$academic_qualification_status = 'NA';
					if($bgroup['academic_qualification_status']==1){
						$academic_qualification_status = 'Yes';
					}else if($bgroup['academic_qualification_status']==2){
						$academic_qualification_status = 'No';
					}

					$roledtArray['academic_qualification']=$bgroup['academic_qualification_status'];
					$roledtArray['academic_qualification_name']=$academic_qualification_status;
					$roledtArray['examfilename']=$bgroup['exam_file']?:'NA';
					$roledtArray['technicalfilename']=$bgroup['technical_interview_file']?:'NA';

					foreach($roledtArray['business_sector_group_id_arr'] as $bgpid){
						$roledtArray["rejected_history"][$bgpid] = [];
					}
					


					//echo $bgroup['id'];exit;
					$groupIdsArr = explode(',',$groupcodeArr);
					$bgrouphistorymodel = UserRoleTechnicalExpertBsCodeHistory::find()->where(['business_sector_group_id' => $groupIdsArr,'user_id'=>$user_id])->all();
					if(count($bgrouphistorymodel)>0)
					{
						$bgrouphistory_arr = array();
						foreach($bgrouphistorymodel as $bgrouphistory)
						{
							$busgrouphistory_arr = array();
							$busgrouphistory_arr['id']=$bgrouphistory->id;
							$busgrouphistory_arr['role_name']=$bgrouphistory->role->role_name;
							$busgrouphistory_arr['business_sector_name']=$bgrouphistory->businesssector->name;
							$busgrouphistory_arr['business_sector_group_name']=$bgrouphistory->businesssectorgroup?$bgrouphistory->businesssectorgroup->group_code:'';
							$busgrouphistory_arr['approval_by_name']=$bgrouphistory->approvaluser->first_name." ".$bgrouphistory->approvaluser->last_name;
							$busgrouphistory_arr['approval_comment']=$bgrouphistory->approval_comment?:"";
							$busgrouphistory_arr['approval_date']=date($date_format,$bgrouphistory->approval_date);
							//$bgrouphistory_arr[]=$busgrouphistory_arr;
							$roledtArray["rejected_history"][$bgrouphistory->business_sector_group_id][] = $busgrouphistory_arr;
						}
						//$roledtArray[$bgrouphistory->business_sector_group_id]["rejected_history"][] = $bgrouphistory_arr;
					}
					




					/*
					$status_change_by_name = '';
					if($status_change_by!='' && $status_change_by!=0){
						$Usermodel = User::find()->where(['id' => $status_change_by])->one();
						if ($Usermodel !== null)
						{
							$status_change_by_name = $Usermodel->first_name.' '.$Usermodel->last_name;
						}
						$roledtArray['approval_comment']=$bgroup['approval_comment'];
						$roledtArray['approval_by_name']=$status_change_by_name;
						$roledtArray['approval_date']=date($date_format,$bgroup['approval_date']);
					}
					*/

					//echo $bgroup['approval_by'];
					$status_change_by = explode('||SP||',$bgroup['approval_by']);
					$status_change_by_name = [];
					//echo count($status_change_by); die;
					if($bgroup['approval_by']!='' && count($status_change_by)>0){
						foreach($status_change_by as $userchangeid){
							$Usermodel = User::find()->where(['id' => $userchangeid])->one();
							if ($Usermodel !== null)
							{
								$status_change_by_name[] = $Usermodel->first_name.' '.$Usermodel->last_name;
							}else{
								$status_change_by_name[] = '';
							}
						}


						$roledtArray['approval_comment']= explode('||SP||',$bgroup['approval_comment']);
						$roledtArray['approval_by_name']=$status_change_by_name;
						$status_change_datearr = explode('||SP||',$bgroup['approval_date']);
						//print_r($status_change_datearr); die;
						if(count($status_change_datearr)>0){
							foreach($status_change_datearr as $key => $datea){
								$status_change_datearr[$key] = date($date_format,$datea);
							}
						}else{
							$status_change_datearr = [];
						}
						
						//$status_change_datearr = [];
						$roledtArray['approval_date']=$status_change_datearr;

						/*
						$roledtArray['approval_comment']=$bgroup['approval_comment'];
						$roledtArray['approval_by_name']=$status_change_by_name;
						$roledtArray['approval_date']=date($date_format,$bgroup['approval_date']);
						*/
					}


					$roledtArray['status']=$bgroup['status'];
					
					
					if($bgroup['status']==0){
						$resultarr["tebusinessgroup_new"][] = $roledtArray;
					}else if($bgroup['status']==1){
						$resultarr["tebusinessgroup_approvalwaiting"][] = $roledtArray;
					}else if($bgroup['status']==2){
						$resultarr["tebusinessgroup_approved"][] = $roledtArray;
					}else if($bgroup['status']==3){
						$resultarr["tebusinessgroup_rejected"][] = $roledtArray;
					}

					
				}
			}
		}
		
	 

		return $resultarr;
	}

	public function getListOfBusinessSector(){

		$businesslistmodel = BusinessSector::find()->select('id,name')->all();

		if(count($businesslistmodel)>0){
			foreach($businesslistmodel as $blist){
				$listbusinesssector =array();
				$listbusinesssector['id']=$blist->id;
				$listbusinesssector['name']=$blist->name;
				$resultarr['listbusinesssector'][]=$listbusinesssector;
			}
		}
		return $resultarr;
	}
  
	public function getAuditStandardList(){

		$auditstanmodel = AuditStandard::find()->all();

		if(count($auditstanmodel)>0){
			
			foreach($auditstanmodel as $stan){
			
			$standlist =array();
			$standlist['id']= $stan['id'];
			$standlist['standard_name']=$stan['standard_name'];
			$standlist['code']=$stan['code'];
			$standlist['version']=$stan['version'];
			$resultarr['auditstandardlist'][]=$standlist;
			// echo json_encode($standlist);
			}
			
		}
		return $resultarr;
	}

	public function actionDeleteUserData(){

		$data = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');

		$user_id = $data['user_id'];
		$id = $data['id'];
		$actiontype = $data['actiontype'];
		$typeaction = $data['typeaction'];
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$responsedata=array('status'=>0,'message'=>'Something went wrong!');
		$target_dir = Yii::$app->params['user_files']; 
		
		//$connection = Yii::$app->getDb();
		//print_r($data );
		//die;
		/*
		if(!$this->canDoUserAccess($id)){
			return false;
		}
		*/
		if($actiontype =='role')
		{
			//$rolemodel->
			
			$rolemodel = UserRole::find()->where(['id' => $id])->one();
			if($rolemodel!==null)
			{
				if($typeaction !='delete')
				{
					/*
					if(!$this->canDoUpdateAccess($update_user_id,['edit_user_roles'])){
						return false;
					}
					*/
					
					if($typeaction =='deactivate')
					{
						$rolemodel->status = 1;
						$message = 'Deactivated Successfully';
					}
					else
					{
						$rolemodel->status = 0;
						$message = 'Activated Successfully';
					}
					$rolemodel->save();
				}
				else
				{
					//edit_user_roles
					/*
					if(!$this->canDoUpdateAccess($update_user_id,['edit_user_roles'])){
						return false;
					}
					*/

					$model = User::find()->where(['id'=>$rolemodel->user_id])->one();

					$role= Role::find()->select('role_name')->where(['id' => $rolemodel->role_id])->one();
					$franchise= UserCompanyInfo::find()->select('company_name')->where(['user_id' => $rolemodel->franchise_id])->one();
					$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'delete_role'])->one();
					$HQmodel = User::find()->select('headquarters')->where(['id' => $rolemodel->franchise_id])->one();

					if($mailContent !== null && $role!== null && $franchise!== null  )
					{
						$mailmsg=str_replace('{USERNAME}', "User", $mailContent['message'] );
						$mailmsg=str_replace('{role}', $role['role_name'], $mailmsg );
						$mailmsg=str_replace('{franchise}', $franchise['company_name'], $mailmsg );
						
						$MailLookupModel = new MailLookup();
						$MailLookupModel->to=$model->email;						
						$MailLookupModel->subject=$mailContent['subject'];
						$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
						$MailLookupModel->attachment='';
						$MailLookupModel->mail_notification_id='';
						$MailLookupModel->mail_notification_code='';
						$Mailres=$MailLookupModel->sendMail();
					}

					if($HQmodel['headquarters']!=1)
					{
						$FranchiseMailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'user_delete_role_to_franchise'])->one();

						if($FranchiseMailContent !== null && $role!== null && $franchise!== null)
						{
							$mailmsg=str_replace('{USERNAME}', "OSS", $FranchiseMailContent['message'] );
							$mailmsg=str_replace('{ROLE}', $role['role_name'], $mailmsg );

							$MailLookupModel = new MailLookup();
							$MailLookupModel->to=$franchise['company_email'];							
							$MailLookupModel->subject=$FranchiseMailContent['subject'];
							$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
							$MailLookupModel->attachment='';
							$MailLookupModel->mail_notification_id='';
							$MailLookupModel->mail_notification_code='';
							$Mailres=$MailLookupModel->sendMail();
						}
					}
					$rolemodel->delete();
					$message = 'Deleted Successfully';
				}
				
			}
			$responsedata = ['status'=>1,'message'=>$message];
						
		}
		if($actiontype =='standards')
		{
			if(!Yii::$app->userrole->hasRights(array('add_edit_user_standards_business_sectors')) && $user_type!=3)
			{
				return false;
			}

			$standardmodel = UserStandard::find()->where(['id' => $id])->one();
			if($standardmodel!==null){
				$standardmodel->delete();
				$responsedata = ['status'=>1,'message'=>'Deleted Successfully'];
			}			
		}
		if($actiontype =='declaration')
		{
			/*
			if(!$this->canDoUpdateAccess($update_user_id,['add_edit_declaration']) && !($user_type==1 && $userid==$update_user_id)){
				return false;
			}
			*/
			$declarationmodal=UserDeclaration::find()->where(['id' => $id])->one();
			if($declarationmodal!==null){
				$declarationmodal->delete();
				$responsedata = ['status'=>1,'message'=>'Deleted Successfully'];
			}
		}

        if ($actiontype =='translator') {
            $declarationmodal=Translator::find()->where(['id' => $id])->one();
            if ($declarationmodal!==null) {
                $declarationmodal->delete();
                $responsedata = ['status'=>1,'message'=>'Deleted Successfully'];
            }
        }

		if($actiontype =='business_group')
		{
			/*
			if(!$this->canDoUpdateAccess($update_user_id,['add_edit_user_business_group'])){
				return false;
			}
			*/
			$questbsectors = UserBusinessGroup::find()->where(['id' => $id])->one();
			if($questbsectors!==null){
				//if($questbsectors->groupcode
				$UserRoleTechnicalExpertBsCode = UserBusinessGroupCode::deleteAll(['business_group_id' => $id]);

				$questbsectors->delete();
				$responsedata = ['status'=>1,'message'=>'Deleted Successfully'];
			} 
		}


		if($actiontype =='business_group_code')
		{
			$UserBusinessGroupModel = UserBusinessGroup::find()->where(['id' => $id])->one();
			$business_sector_id = $UserBusinessGroupModel->business_sector_id;
			
			$UserBusinessGroupCodeModel = UserBusinessGroupCode::find()->where(['id' => $data['business_sector_group_code_id']])->one();
			$business_sector_group_id = $UserBusinessGroupCodeModel->business_sector_group_id;
			
			
			UserBusinessGroupCode::deleteAll(['id' => $data['business_sector_group_code_id']]);
			$questbsectors = UserBusinessGroupCode::find()->where(['business_group_id' => $id])->one();
			if($questbsectors===null)
			{
				UserBusinessGroup::deleteAll(['id' => $id]);
			}
			
			$UserRoleBusinessGroupCodeModel = UserRoleBusinessGroupCode::find()->where(['user_business_group_code_id' => $data['business_sector_group_code_id']])->all();
			if(count($UserRoleBusinessGroupCodeModel)>0)
			{
				foreach($UserRoleBusinessGroupCodeModel as $UserRoleBusinessGroupCodeM)
				{
					$UserRoleBusinessGroupID = $UserRoleBusinessGroupCodeM->business_group_id;
					$UserRoleBusinessGroupCodeM->delete();			  	
					
					$UserRoleBusinessGroupModel = UserRoleBusinessGroupCode::find()->where(['business_group_id' => $UserRoleBusinessGroupID])->one();
					if($UserRoleBusinessGroupModel===null)
					{
						UserRoleBusinessGroup::deleteAll(['id' => $UserRoleBusinessGroupID]);
					}					
				}
			}
						
			$connection = Yii::$app->getDb();				
			$command = $connection->createCommand("SELECT tebg.id as pid,tebgc.id as cid FROM tbl_user_role_technical_expert_business_group AS tebg 
			INNER JOIN tbl_user_role_technical_expert_business_group_code AS tebgc ON tebg.id=tebgc.user_role_technical_expert_bs_id AND tebg.user_id=".$user_id." AND tebg.business_sector_id=".$business_sector_id." AND tebgc.business_sector_group_id IN (".$business_sector_group_id.")");
			$result = $command->queryAll();
			if(count($result)>0)
			{
				foreach($result as $data)
				{
					$user_role_technical_expert_business_group_id = $data['pid'];
					$user_role_technical_expert_business_group_code_id = $data['cid'];
					
					UserRoleTechnicalExpertBsCode::deleteAll(['id' => $user_role_technical_expert_business_group_code_id]);
					
					$UserRoleTechnicalExpertBsCodeModel = UserRoleTechnicalExpertBsCode::find()->where(['user_role_technical_expert_bs_id' => $user_role_technical_expert_business_group_id])->one();
					if($UserRoleTechnicalExpertBsCodeModel===null)
					{
						UserRoleTechnicalExpertBs::deleteAll(['id' => $user_role_technical_expert_business_group_id]);
					}	
				}
			}			
			
			$responsedata = ['status'=>1,'message'=>'Deleted Successfully'];
		}
		 

		if($actiontype =='qualification')
		{
			/*
			if(!$this->canDoUpdateAccess($update_user_id,['add_edit_user_qualification_details']) && !($user_type==1 && $userid==$update_user_id)){
				return false;
			}
			*/
			$UserQualification = UserQualification::find()->where(['id' => $id])->one();
			if($UserQualification!==null){
				if($UserQualification->certificate != ''){
					Yii::$app->globalfuns->removeFiles($UserQualification->certificate,$target_dir);
				}
				$UserQualification->delete();
				$responsedata = ['status'=>1,'message'=>'Deleted Successfully'];
			}
		}
		
		//$Experiencemodel = UserExperience::find()->select('experience,year')->where(['user_id' => $user_id])->asArray()->all();
		//$resultarr["experience"]=$Experiencemodel;
		if($actiontype =='experience')
		{
			$Experiencemodel = UserExperience::find()->where(['id' => $id])->one();
			if($Experiencemodel!==null){
				$Experiencemodel->delete();
				$responsedata = ['status'=>1,'message'=>'Deleted Successfully'];
			}
		}


		if($actiontype =='audit_experience')
		{
			$auditExperiencemodel = UserAuditExperience::find()->where(['id' => $id])->one();
			if($auditExperiencemodel!==null){
				$auditExperiencemodel->delete();
				$responsedata = ['status'=>1,'message'=>'Deleted Successfully'];
			}
		}


		if($actiontype =='consultancy_experience')
		{
			$auditconsultancymodel = UserConsultancyExperience::find()->where(['id' => $id])->one();
			if($auditconsultancymodel!==null){
				$auditconsultancymodel->delete();
				$responsedata = ['status'=>1,'message'=>'Deleted Successfully'];
			}
		}

		if($actiontype =='certificate')
		{
			$Certificationmodel = UserCertification::find()->where(['id' => $id])->one();
			if($Certificationmodel!==null){
				if($Certificationmodel->filename != ''){
					Yii::$app->globalfuns->removeFiles($Certificationmodel->filename,$target_dir);
				}
				$Certificationmodel->delete();
				$responsedata = ['status'=>1,'message'=>'Deleted Successfully'];
			} 
		}
		

		if($actiontype =='cpd')
		{
			$Trainingmodel = UserTrainingInfo::find()->where(['id' => $id])->one();
			if($Trainingmodel !== null){
				$responsedata = ['status'=>1,'message'=>'Deleted Successfully'];	
			}
			
		}


		if($actiontype =='te_business_group')
		{
			$UserRoleTechnicalExpertBs = UserRoleTechnicalExpertBs::find()->where(['id'=>$id])->one();
			if($UserRoleTechnicalExpertBs!==null){

				if($UserRoleTechnicalExpertBs->exam_file != ''){
					Yii::$app->globalfuns->removeFiles($UserRoleTechnicalExpertBs->exam_file,$target_dir);
				}
				if($UserRoleTechnicalExpertBs->technical_interview_file != ''){
					Yii::$app->globalfuns->removeFiles($UserRoleTechnicalExpertBs->technical_interview_file,$target_dir);
				}
				$UserRoleTechnicalExpertBsCode = UserRoleTechnicalExpertBsCode::deleteAll(['user_role_technical_expert_bs_id' => $id]);
				$UserRoleTechnicalExpertBs->delete();
				/*
				if(count($UserRoleTechnicalExpertBs->expertbscode) > 0){
					foreach($UserRoleTechnicalExpertBs->expertbscode as $expertbscode){
						expertbscode
					}
				}
				*/
				$responsedata = ['status'=>1,'message'=>'Deleted Successfully'];
			}
		}
		
		if($actiontype =='mapuserrole')
		{
			$UserRoleBusinessGroup = UserRoleBusinessGroup::find()->where(['id'=>$id])->one();
			if($UserRoleBusinessGroup!==null){
				if($UserRoleBusinessGroup->document != ''){
					Yii::$app->globalfuns->removeFiles($UserRoleBusinessGroup->document,$target_dir);
				}

				$UserRoleBusinessGroupCode = UserRoleBusinessGroupCode::deleteAll(['business_group_id' => $id]);
				$UserRoleBusinessGroup->delete();
				$responsedata = ['status'=>1,'message'=>'Deleted Successfully'];
			}
		}

		
	 

		return $responsedata;
	}
	public function actionGetTeRoles(){
		$data = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];
		$responsedata=array('status'=>0,'message'=>'Something went wrong!');

		//$getdata = ['user_id'=>$data['id'],'actiontype'=> $data['actiontype'] ];
		//$returndata = $this->getUserDetails($getdata);
		if($data){
			$roleList = [];

			$user_id = $data['user_id'];
			//$reviewerroles = Yii::$app->globalfuns->getReviewerRolesForUser($user_id);
			$teroles = Yii::$app->globalfuns->getTERolesForUser($user_id);
			/*
			if(count($reviewerroles)>0){
				foreach($reviewerroles as $role){
					$roleList[] = ['id'=>$role['id'],'role_name'=>$role['role_name']];
				}
			}
			*/
			if(count($teroles)>0){
				foreach($teroles as $role){
					$roleList[] = ['id'=>$role['id'],'role_name'=>$role['role_name']];
				}
			}
			$responsedata= ['status'=>1,'rolelist'=>$roleList];
		}
		return $responsedata;
	}
	public function actionGetBusinesssector(){

		
		$data = yii::$app->request->post();
		$qualsector = [];
		if(isset($data['type']) && $data['type'] == 'approved' ){
			$UserBusinessGroup = UserBusinessGroup::find()->where(['user_id'=>$data['user_id'] ])->all();
			if(count($UserBusinessGroup)>0){
				foreach($UserBusinessGroup as $userbg){
					$qualsector[] = $userbg->business_sector_id;
				}
			}
		}
		$BusinessSector = BusinessSector::find()->where(['t.status'=>0])->alias('t');
		if(isset($data['type']) && $data['type'] == 'approved' ){
			$BusinessSector = $BusinessSector->andWhere(['id'=>$qualsector ]);
		}
		//$BusinessSector = $BusinessSector->innerJoinWith(['businesssectorgroupactive']);
		$BusinessSector = $BusinessSector->all();
		$bsectorList = [];
		if(count($BusinessSector)>0){
			foreach($BusinessSector as $bsector){
				if(count($bsector->businesssectorgroupactive)>0){
					$bsectorList[] = ['id'=>"$bsector->id",'name'=>$bsector->name];
				}
			}
		}
		return ['bsectors'=>$bsectorList];
	}

	public function actionGetBusinesssectorcode(){
		$data = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];
		$responsedata=array('status'=>0,'message'=>'Something went wrong!');

		$id = (isset($data['id']) && $data['id']>0) ? $data['id']:0;
		$user_id = (isset($data['user_id']) && $data['user_id']>0) ? $data['user_id']:0;
		$role_id = (isset($data['role_id']) && $data['role_id']>0) ? $data['role_id']:0;

		$is_approved = (isset($data['type']) && $data['type'] == 'approved') ? 1:0;

		$existbgroupcode = [];
		$approvedbgroupcode = [];
		if($user_id>0){

			//if($is_approved){
				//UserBusinessGroupCode::find()->where([''])->all();
				$UserBusinessGroup = UserBusinessGroup::find()->where(['user_id'=>$data['user_id'],'business_sector_id'=>$data['business_sector_id'] ])->all();
				if(count($UserBusinessGroup)>0){
					foreach($UserBusinessGroup as $userbg){
						if(count($userbg->groupcodeactive)>0){
							foreach($userbg->groupcodeactive as $bcode){
								$approvedbgroupcode[] = $bcode->business_sector_group_id;
							}
						}
						
					}
				}
			//}

			$UserRoleTechnicalExpertBsCode = UserRoleTechnicalExpertBsCode::find()->alias('t')->innerJoinWith('expertbs as expertbs')->where(['expertbs.user_id'=>$user_id,'expertbs.role_id'=>$role_id]);
			if($id>0){
				$UserRoleTechnicalExpertBsCode = $UserRoleTechnicalExpertBsCode->andWhere(['!=','expertbs.id',$id]);
			}
			$UserRoleTechnicalExpertBsCode = $UserRoleTechnicalExpertBsCode->all();
			
			if(count($UserRoleTechnicalExpertBsCode)>0){
				foreach($UserRoleTechnicalExpertBsCode as $bcode){
					$existbgroupcode[] = $bcode->business_sector_group_id;
				}
			}
		}
		

		$BusinessSectorGroup = BusinessSectorGroup::find()->where(['t.status'=>0])->alias('t');
		$BusinessSectorGroup = $BusinessSectorGroup->andWhere(['t.business_sector_id'=> $data['business_sector_id']]);
		//$BusinessSector = $BusinessSector->innerJoinWith(['businesssectorgroupactive']);
		//print_r($existbgroupcode);
		if(count($existbgroupcode)>0){
			$BusinessSectorGroup = $BusinessSectorGroup->andWhere(['not in','t.id', $existbgroupcode ]);
		}
		if($is_approved){
			$BusinessSectorGroup = $BusinessSectorGroup->andWhere(['t.id' => $approvedbgroupcode ]);
		}else{
			$BusinessSectorGroup = $BusinessSectorGroup->andWhere(['not in','t.id', $approvedbgroupcode ]);
		}
		$BusinessSectorGroup = $BusinessSectorGroup->all();
		$bsectorList = [];
		if(count($BusinessSectorGroup)>0){
			foreach($BusinessSectorGroup as $bsector){
				$bsectorList[] = ['id'=>"$bsector->id",'group_code'=>$bsector->group_code];
			}
		}
		return ['bsectorgroups'=>$bsectorList];
	}

	
	public function actionGetBusinesssectorcodeapproved(){
		$data = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];
		$responsedata=array('status'=>0,'message'=>'Something went wrong!');

		$id = (isset($data['id']) && $data['id']>0) ? $data['id']:0;
		$user_id = (isset($data['user_id']) && $data['user_id']>0) ? $data['user_id']:0;
		$role_id = (isset($data['role_id']) && $data['role_id']>0) ? $data['role_id']:0;

		$is_approved = (isset($data['type']) && $data['type'] == 'approved') ? 1:0;

		$existbgroupcode = [];
		$approvedbgroupcode = [];
		if($user_id>0){

			if($is_approved){
				//UserBusinessGroupCode::find()->where([''])->all();
				$UserBusinessGroup = UserBusinessGroup::find()->where(['user_id'=>$data['user_id'],'business_sector_id'=>$data['business_sector_id'] ])->all();
				if(count($UserBusinessGroup)>0){
					foreach($UserBusinessGroup as $userbg){
						if(count($userbg->groupcodeactive)>0){
							foreach($userbg->groupcodeactive as $bcode){
								$approvedbgroupcode[] = $bcode->business_sector_group_id;
							}
						}
						
					}
				}
			}

			$UserRoleTechnicalExpertBsCode = UserRoleTechnicalExpertBsCode::find()->alias('t')->innerJoinWith('expertbs as expertbs')->where(['expertbs.user_id'=>$user_id,'expertbs.role_id'=>$role_id]);
			if($id>0){
				$UserRoleTechnicalExpertBsCode = $UserRoleTechnicalExpertBsCode->andWhere(['!=','expertbs.id',$id]);
			}
			$UserRoleTechnicalExpertBsCode = $UserRoleTechnicalExpertBsCode->all();
			
			if(count($UserRoleTechnicalExpertBsCode)>0){
				foreach($UserRoleTechnicalExpertBsCode as $bcode){
					$existbgroupcode[] = $bcode->business_sector_group_id;
				}
			}
		}
		$BusinessSectorGroup = BusinessSectorGroup::find()->where(['t.status'=>0])->alias('t');
		$BusinessSectorGroup = $BusinessSectorGroup->andWhere(['t.business_sector_id'=> $data['business_sector_id']]);
		//$BusinessSector = $BusinessSector->innerJoinWith(['businesssectorgroupactive']);
		//print_r($existbgroupcode);
		if(count($existbgroupcode)>0){
			$BusinessSectorGroup = $BusinessSectorGroup->andWhere(['not in','t.id', $existbgroupcode ]);
		}
		$BusinessSectorGroup = $BusinessSectorGroup->all();
		$bsectorList = [];
		if(count($BusinessSectorGroup)>0){
			foreach($BusinessSectorGroup as $bsector){
				$bsectorList[] = ['id'=>"$bsector->id",'group_code'=>$bsector->group_code];
			}
		}
		return ['bsectorgroups'=>$bsectorList];
	}


	public function actionGetBusinessSectorsByStandard()
    {
        $data = Yii::$app->request->post();
		if($data)
		{
			$stds='';$process='';

			$std_ids='';
			if(isset($data['standardvals']) && is_array($data['standardvals'])){
				foreach($data['standardvals'] as $value)
				{
					$stds.=$value.",";
				}
				$std_ids=substr($stds, 0, -1);
			}else if(isset($data['standardvals'])){
				$std_ids =$data['standardvals'];
			}
			$resultarr=array();
			if($std_ids !=''){
				$connection = Yii::$app->getDb();
				
				$command = $connection->createCommand("SELECT bs.id,bs.name FROM tbl_business_sector AS bs INNER JOIN tbl_business_sector_group AS bsg ON bs.id=bsg.business_sector_id AND bsg.standard_id IN (".$std_ids.") WHERE bs.status=0 and bsg.status=0 GROUP BY bs.id");
				
				
				$result = $command->queryAll();
				if(count($result)>0)
				{
					
					foreach($result as $data)
					{
						$values=array();
						$values['id'] = $data['id'];
						$values['name'] = $data['name'];
						$resultarr[]=$values;
					}

				}
			}
			return ['bsectors'=>$resultarr];	
		}
	}

	public function actionGetBusinessSectorGroupsByStandard()
    {
        $data = Yii::$app->request->post();
		if($data)
		{
			$id = isset($data['id'])?$data['id']:0;
			$user_id = isset($data['user_id'])?$data['user_id']:0;
			
			$resultarr=array();
			$stds='';$bsectors='';
			$std_ids = '';$bsector_ids = '';
			if(isset($data['standardvals']) && is_array($data['standardvals'])){
				foreach($data['standardvals'] as $value)
				{
					$stds.=$value.",";
				}
				$std_ids=substr($stds, 0, -1);
			}else if(isset($data['standardvals'])){
				$std_ids =$data['standardvals'];
			}
			

			if(isset($data['bsectorvals']) && is_array($data['bsectorvals']) && count($data['bsectorvals'])>0)
			{
				foreach($data['bsectorvals'] as $vals)
				{
					$bsectors.=$vals.",";
				}
				$bsector_ids=substr($bsectors, 0, -1);
			}else if(isset($data['bsectorvals'])){
				$bsector_ids = $data['bsectorvals'];
			}
			$resultarr=array();

			$existbgroupcode = [];
			$UserBusinessGroupCode = UserBusinessGroupCode::find()->alias('t')->innerJoinWith('usersector as usersector')->where(['usersector.user_id'=>$user_id]);
			if($id>0){
				//$UserBusinessGroupCode = $UserBusinessGroupCode->andWhere(['!=','t.id',$id]);
				$UserBusinessGroupCode = $UserBusinessGroupCode->andWhere(['!=','t.business_group_id',$id]);
			}
			$UserBusinessGroupCode = $UserBusinessGroupCode->all();
			
			if(count($UserBusinessGroupCode)>0){
				foreach($UserBusinessGroupCode as $bcode){
					$existbgroupcode[] = $bcode->business_sector_group_id;
				}
			}
			//print_r($existbgroupcode);
			$existconodition = '';
			if(count($existbgroupcode)>0){
				$existconodition = ' and bsg.id not in ('.implode(',',$existbgroupcode).') ';
			}

			$connection = Yii::$app->getDb();
			$command = $connection->createCommand("SELECT  bsg.id,bsg.group_code FROM `tbl_business_sector_group` AS bsg  WHERE bsg.standard_id IN (".$std_ids.") ".$existconodition." AND bsg.status=0 AND bsg.business_sector_id IN(".$bsector_ids.") GROUP BY bsg.id");
			
			
			$result = $command->queryAll();
			if(count($result)>0)
			{
				
				foreach($result as $data)
				{
					$values=array();
					$values['id'] = $data['id'];
					$values['group_code'] = $data['group_code'];
					$resultarr[]=$values;
				}

			}
			return ['bsectorgroups'=>$resultarr];	
		
			
		}
	}
	
	public function actionLeftMenuOptions(){

		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];
		$role_chkid=$userData['role_chkid'];

		$menuArrs = [
			'handbooks' => 1,
			'faq' => 1,
			'training_mat' => 1,
			'artwork' => 1,
			'client_logos' => 1,
			'manual' => 1,
			'procedures' => 1,
			'competence_criteria' => 1,
			'instructions' => 1,
			'templates' => 1,
			'application_forms' => 1,
			'polices' => 1,
			'standards' => 1,
			'webinars' => 1
		];
		$condMenuArrs = [
			'handbooks',
			'training_mat',
			'artwork',
			'client_logos',
			'manual',
			'procedures',
			'competence_criteria',
			'instructions',
			'templates',
			'application_forms',
			'polices',
			'standards',
			'webinars'
		];
		$totalmenucnt = 0;
		$model = LibraryDownload::find()->alias('t');
			
		if($resource_access != '1')
		{
			
			$source_file_status = 0;
			$model = $model->join('inner join', 'tbl_library_download_access as download_access','download_access.manual_id=t.id');									
			
			if($user_type==2)
			{
				$customer_roles=Yii::$app->globalfuns->getCustomerRoles();					
				$model = $model->andWhere('download_access.user_access in("'.$customer_roles.'")');	
			}elseif($user_type==3 && $resource_access==5){			
				$model = $model->andWhere('download_access.user_access ="'.$role_chkid.'"');	
			}elseif($user_type==3){			
				$osp_roles=Yii::$app->globalfuns->getOspRoles();					
				$model = $model->andWhere('download_access.user_access in("'.$osp_roles.'")');	
			}else{
				$model = $model->andWhere('download_access.user_access ="'.$role.'"');	
			}
			/*
			
			*/
			
			
			$modelObj = new LibraryDownload();	
			
			if(count($condMenuArrs)>0){
				foreach($condMenuArrs as $downloadname){
					$source_file_status = 0;
					$condmodel = '';
					$condmodel = clone $model;
					if($user_type== Yii::$app->params['user_type']['user'] && in_array($downloadname,$rules ) ){
						$source_file_status = 1;
					}
					if($source_file_status ==0){
						$condmodel = $condmodel->andWhere('status="'.$modelObj->enumStatus['approved'].'"');
					}
					
					$condmodel = $condmodel->andWhere(['type'=>$downloadname ]);				
					$totalCount = $condmodel->count();
					//$menuArrs['']
					//echo $totalCount.'=='.$downloadname;
					if($totalCount<=0){
						//$condmodel = $condmodel->andWhere(['stype'=>$downloadname ]);
						$menuArrs[$downloadname] = 0;
						$totalmenucnt+=1;
					}
				}
			}



			$model = LibraryFaq::find()->alias('t')->where(['<>','status',2]);
			$source_file_status = 0;
			$model = $model->join('inner join', 'tbl_library_faq_access as faq_access','faq_access.library_faq_id=t.id');									
			if($user_type==2)
			{
				$customer_roles=Yii::$app->globalfuns->getCustomerRoles();					
				$model = $model->andWhere('faq_access.user_access_id in("'.$customer_roles.'")');	
			}elseif($user_type==3 && $resource_access==5){	
				$model = $model->andWhere('faq_access.user_access_id ="'.$role_chkid.'"');			
			}elseif($user_type==3){			
				$osp_roles=Yii::$app->globalfuns->getOspRoles();					
				$model = $model->andWhere('faq_access.user_access_id in("'.$osp_roles.'")');	
			}else if($user_type== Yii::$app->params['user_type']['user'] && in_array('faq',$rules )){

			}else{
				$model = $model->andWhere('faq_access.user_access_id ="'.$role.'"');	
			}			
			$totalCount = $model->count();
			if($totalCount<=0){
				$menuArrs['faq'] = 0;
			}


			 
		}

		$menuArrs['showdownloadmenu'] = 1;
		if($totalmenucnt == count($condMenuArrs)){ // If total menu equal zero menu count
			$menuArrs['showdownloadmenu'] = 0;
		}
		return $menuArrs;
	}

	public function actionDownloadtranslatorfile()
	{
		$data = Yii::$app->request->post();
		if($data && isset($data['id']))
		{
			$files = Translator::find()->where(['id'=>$data['id']])->one();
			if($files!==null && $files->filename !== "./")
			{
				
				$filename = $data['filename'];
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
				header('Access-Control-Max-Age: 1000');
				header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
				
				$filepath=Yii::$app->params['user_files'].$filename;
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
			}	
		}
		die;
	}	
	
	public function canDoUserAccess($id)
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
		// && !in_array('activate_user',$rules) && !in_array('deactivate_user',$rules) && !in_array('delete_user',$rules)
		
		$model = User::find()->where(['id' => $id])->alias('t');			
		if($resource_access != 1)
		{
			if($user_type== Yii::$app->params['user_type']['user'] && !in_array('user_master',$rules))
			{
				return false;
			}else if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$model = $model->andWhere(' t.franchise_id="'.$userid.'" or t.created_by="'.$userid.'"');
			}		
		}

		if($user_type == Yii::$app->params['user_type']['user'] && $is_headquarters != 1){
			$model = $model->andWhere(' t.franchise_id="'.$franchiseid.'" or t.created_by="'.$franchiseid.'" ');
		}
		$model = $model->one();

		$UserRole = null;
		if($resource_access != 1)
		{
			if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$UserRole = UserRole::find()->where(['franchise_id'=>$userid])->one();
			}		
			if($user_type == Yii::$app->params['user_type']['user'] && $is_headquarters != 1){
				$UserRole = UserRole::find()->where(['franchise_id'=>$franchiseid])->one();
			}
		}

		if($model!==null || $UserRole!==null)
		{
			return true;
		}
		return false;
	}

	public function canDoUpdateAccess($id='',$access_name)

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
		// && !in_array('activate_user',$rules) && !in_array('deactivate_user',$rules) && !in_array('delete_user',$rules)
		if(!Yii::$app->userrole->hasRights($access_name) && !($user_type==3)){
			return false;
		}
		return true;
		/*
		$model = User::find()->where(['id' => $id])->alias('t');			
		if($resource_access != 1)
		{
			if($user_type== Yii::$app->params['user_type']['user'] && !in_array('user_master',$rules))
			{
				return false;
			}else if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$model = $model->andWhere(' t.franchise_id="'.$userid.'" or t.created_by="'.$userid.'"');
			}else if($user_type== Yii::$app->params['user_type']['customer']){
				$model = $model->andWhere(' t.id="'.$userid.'" ');
			}			
		}

		if($user_type == Yii::$app->params['user_type']['user'] && $is_headquarters != 1){
			$model = $model->andWhere(' t.franchise_id="'.$franchiseid.'" or t.created_by="'.$franchiseid.'" ');
		}
		$model = $model->one();
		if($model!==null)
		{
			return true;
		}
		return false;
		*/
	}
	
	public function canDoAssignedUserAccess($id)
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
		// && !in_array('activate_user',$rules) && !in_array('deactivate_user',$rules) && !in_array('delete_user',$rules)
		
		$model = User::find()->where(['id' => $id])->alias('t');	
		$model = $model->join('inner join', 'tbl_user_role as userrole','userrole.user_id=tbl_users.id');		
		if($resource_access != 1)
		{
			if($user_type== Yii::$app->params['user_type']['user'] && !in_array('user_master',$rules))
			{
				return false;
			}else if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$model = $model->andWhere(' userrole.franchise_id="'.$userid.'"  ');
			}			
		}
		if($user_type == Yii::$app->params['user_type']['user'] && $is_headquarters != 1){
			$model = $model->andWhere(' userrole.franchise_id="'.$franchiseid.'" ');
		}
		$model = $model->one();
		if($model!==null)
		{
			return true;
		}
		return false;
	}

}