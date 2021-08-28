<?php
namespace app\modules\application\controllers;

use Yii;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationBrands;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationProduct;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationUnitStandard;
use app\modules\application\models\ApplicationUnitProduct;
use app\modules\application\models\ApplicationUnitProcess;
use app\modules\application\models\ApplicationUnitBusinessSector;
use app\modules\application\models\ApplicationUnitCertifiedStandard;
use app\modules\application\models\ApplicationUnitCertifiedStandardFile;
use app\modules\application\models\ApplicationReview;
use app\modules\application\models\ApplicationReviewComment;
use app\modules\application\models\ApplicationUnitReviewComment;
use app\modules\application\models\ApplicationChecklistComment;
use app\modules\application\models\ApplicationProductStandard;
use app\modules\application\models\ApplicationProductMaterial;
use app\modules\application\models\ApplicationUnitBusinessSectorGroup;
use app\modules\master\models\ProductTypeMaterialComposition;
use app\modules\application\models\ApplicationApprover;
use app\modules\application\models\ApplicationReviewer;
use app\modules\application\models\ApplicationStatus;
use app\modules\application\models\ApplicationApproval;
use app\modules\application\models\ApplicationCertifiedByOtherCB;
use app\modules\application\models\ApplicationRenewalStandard;
use app\modules\application\models\ApplicationChangeAddress;
use app\modules\application\models\ApplicationProductCertificateTemp;
use app\modules\application\models\Brands;

use app\modules\master\models\ApplicationQuestions;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;
use app\modules\master\models\User;
use app\modules\master\models\UserCompanyInfo;
use app\modules\master\models\UserBusinessGroupCode;
use app\modules\master\models\Standard;
use app\modules\master\models\BusinessSector;
use app\modules\master\models\BusinessSectorGroup;
use app\modules\master\models\Product;
use app\modules\master\models\ProductType;
use app\modules\master\models\StandardLabelGrade;
use app\modules\master\models\Process;
use app\modules\master\models\Brand;

use app\modules\certificate\models\Certificate;

use app\modules\changescope\models\ProcessAddition;
use app\modules\changescope\models\UnitAddition;
use app\modules\changescope\models\ProductAddition;
use app\modules\application\models\ApplicationRenewal;

use app\modules\changescope\models\StandardAddition;

use app\models\Enquiry;
use app\modules\master\models\State;
use yii\web\NotFoundHttpException;

use app\modules\audit\models\Audit;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;


class BrandsController extends \yii\rest\Controller
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
			'authenticator' => ['class' => JwtHttpBearerAuth::class ],
			/*
			'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => Yii::$app->userrole->hasRights(),                     
                    ],
                ],
            ],
			*/
		];        
    }
	
	public function appAddressRelation($model)
	{
		$model = $model->joinWith('applicationaddress as appaddress');
	}
	
	public function actionIndex()
    {
		$post = yii::$app->request->post();
		
		$usermodel = new User();
		$application = new Application();
		
		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		

		$resource_access=$userData['resource_access'];

		// $appsmodel=new Application();					
		// $model = Application::find()->where('1=1')->alias('t');
		// //$model = $model->join('left join', 'tbl_application_unit as unit','unit.app_id =t.id and unit_type=1');				
		// $responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		// if($resource_access != 1){
		// 	if($user_type== Yii::$app->params['user_type']['customer']){
		// 		//$model = $model->andWhere('t.created_by='.$userid);
		// 		$model = $model->andWhere('t.customer_id='.$userid);
		// 	}else if($user_type==1 && in_array('brand_management',$rules) && $is_headquarters==1){
		// 		$model = $model->join('inner join','tbl_application_brands as appbr','t.id=appbr.app_id');
		// 		$model = $model->join('inner join','tbl_brands as br','br.id=appbr.brand_id')->andWhere(['br.user_id'=>$userid]);
		// 	}
			
		// }
		
		// if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1 ){
			
		// 	$model = $model->andWhere('t.franchise_id="'.$franchiseid.'"');
		// }		
		
		
		// if(isset($post['statusFilter'])  && $post['statusFilter']!='')
		// {
		// 	if( $post['statusFilter']>='0'){
		// 		$model = $model->andWhere(['status'=> $post['statusFilter']]);
		// 	}
		// }
		// if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
		// {
		// 	$model = $model->join('left join', 'tbl_application_standard as app_standard','app_standard.app_id =t.id ');
		// 	$model = $model->andWhere(['app_standard.standard_id'=> $post['standardFilter']]);
		// 	/*sort($post['standardFilter']);
		// 	$standardFilter = implode(',',$post['standardFilter']);
		// 	$model = $model->having(['stdgp'=>$standardFilter]);
		// 	*/
		// 	//$model->andWhere(['country_id'=> $post['standardFilter']]);
		// }
		// if(isset($post['typeFilter'])  && $post['typeFilter']!='' && count($post['typeFilter'])>0)
		// {
		// 	$model = $model->andWhere(['t.audit_type'=> $post['typeFilter']]);			
		// }
		
		// if(isset($post['franchiseFilter'])  && $post['franchiseFilter']!='' && count($post['franchiseFilter'])>0)
		// {
		// 	$model = $model->andWhere(['t.franchise_id'=> $post['franchiseFilter']]);			
		// }
		
		
		
		// $model = $model->groupBy(['t.id']);
		
		// $appAddressJoinWithStatus=false;
		// if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		// {		
        //     $page = ($post['page'] - 1)*$post['pageSize'];
		// 	$pageSize = $post['pageSize']; 
		// 	$statusarray=array_map('strtolower', $appsmodel->arrStatus);
			
		// 	if(isset($post['searchTerm']) && $post['searchTerm'] !='')
		// 	{
		// 		$searchTerm = $post['searchTerm'];
		// 		$search_status = array_search(strtolower($searchTerm),$statusarray);
		// 		if($search_status===false)
		// 		{
		// 			$search_status = '';
		// 		}
				
		// 		$appAddressJoinWithStatus=true;
		// 		$this->appAddressRelation($model);
				
		// 		$model = $model->andFilterWhere([
		// 			'or',
		// 			['like', 'code', $searchTerm],
		// 			['like', 'appaddress.company_name', $searchTerm],
		// 			['like', 'appaddress.first_name', $searchTerm],
		// 			['like', 'appaddress.last_name', $searchTerm],
		// 			['like', 'appaddress.telephone', $searchTerm],
		// 			['like', 'date_format(FROM_UNIXTIME(t.created_at), \'%b %d, %Y\' )', $searchTerm],	
		// 			['status'=>$search_status]	
		// 			//['like', 'status', array_search($searchTerm,$statusarray)],										
		// 		]);			
		// 	}
		// 	$totalCount = $model->count();
			
		// 	$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
		// 	if(isset($post['sortColumn']) && $post['sortColumn'] !='')
		// 	{
		// 		if($post['sortColumn']=='company_name')
		// 		{
		// 			if(!$appAddressJoinWithStatus)
		// 			{
		// 				$this->appAddressRelation($model);
		// 			}				
		// 			$model = $model->orderBy(['appaddress.company_name'=>$sortDirection]);
		// 		}else{
		// 			$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
		// 		}
		// 	}
		// 	else
		// 	{
		// 		$model = $model->orderBy(['created_at' => SORT_DESC]);
		// 	}
			



        //     $model = $model->limit($pageSize)->offset($page);
		// }
		// else
		// {
		// 	$totalCount = $model->count();
		// }
		
		// $app_list=array();
		// //$model = $model->asArray()->all();
		// $model = $model->all();		
		// if(count($model)>0)
		// {
		// 	foreach($model as $application)
		// 	{
		// 		$data=array();
		// 		$data['id']=$application->id;
		// 		$data['code']=$application->code;
		// 		$data['company_name']=$application->companyname;
		// 		$data['address']=$application->address;
		// 		$data['zipcode']=$application->zipcode;
		// 		$data['city']=$application->city;
		// 		$data['title']=$application->title;
		// 		$data['first_name']=$application->firstname;
		// 		$data['last_name']=$application->lastname;
		// 		$data['job_title']=$application->jobtitle;
		// 		$data['telephone']=$application->telephone;
		// 		$data['email_address']=$application->emailaddress;
		// 		//$data['created_at']=date('M d,Y h:i A',$application->created_at);
		// 		$data['created_at']=date($date_format,$application->created_at);
		// 		$data['status']=$application->arrStatus[$application->status];
		// 		$data['overall_status']=$application->overall_status && isset($application->arrOverallStatus[$application->overall_status]) ? $application->arrOverallStatus[$application->overall_status] : '';
		// 		$data['status_id']=$application->status;
		// 		$data['status_label_color']=$application->arrStatusColor[$application->status];
		// 		$data['audit_type']=$application->audit_type;
		// 		$data['audit_type_label']=$application->arrAuditType[$application->audit_type];
		// 		$data['application_unit_count']=count($application->applicationunit);
		// 		$data['process_id']='';
		// 		$data['parent_app_id']= $application->parent_app_id;
		// 		if($application->audit_type == 3){
		// 			$processaddition = ProcessAddition::find()->where(['app_id'=>$application->parent_app_id,'new_app_id'=>$application->id])->one();
		// 			if($processaddition!==null){
		// 				$data['addition_id']=$processaddition->id;
		// 			}
		// 		}else if($application->audit_type == 4){
		// 			$addition = StandardAddition::find()->where(['app_id'=>$application->parent_app_id,'new_app_id'=>$application->id])->one();
		// 			if($addition!==null){
		// 				$data['addition_id']=$addition->id;
		// 			}else{
		// 				$data['addition_id']='';
		// 			}	
		// 		}else if($application->audit_type == 5){
		// 			$addition = UnitAddition::find()->where(['app_id'=>$application->parent_app_id,'new_app_id'=>$application->id])->one();
		// 			if($addition!==null){
		// 				$data['addition_id']=$addition->id;
		// 			}
		// 		}
		// 		$arrAppStd=array();
				
		// 		$appStd=$application->applicationstandardview;
				
		// 		//$appStd = $application->applicationstandard;
		// 		if(count($appStd)>0)
		// 		{	
		// 			foreach($appStd as $app_standard)
		// 			{
		// 				$arrAppStd[]=$app_standard->standard->code;
		// 			}
		// 		}					
		// 		$data['application_standard']=implode(', ',$arrAppStd);
		// 		$data['oss_label'] = $usermodel->ossnumberdetail($application->franchise_id);
								
		// 		$app_list[]=$data;
		// 	}
		// }
	    $data =array();

		if($user_type==1 && in_array('view_brand',$rules) && $is_headquarters==1){
			$brandmod = Brand::find()->where(['user_id'=>$userid])->one();
			if($brandmod!='' && $brandmod!==null){
				$appbrandmod = ApplicationBrands::find()->where(['brand_id'=>$brandmod->id])->all();
				$totalCount = count($appbrandmod);
	
				if(count($appbrandmod)>0){
					foreach($appbrandmod as $appb){
						$resultArr = array();
						$resultArr['app_id'] = $appb['app_id'];
						$resultArr['status'] = $appb['status'];
						$resultArr['brand_status'] = $application->arrBrandStatus[$appb->status];;
						$resultArr['comment'] = $appb['comment'];
						$resultArr['app_approver_brand_id']=$appb['id'];
	
						$resultArr['id']= isset($appb->application->id)?$appb->application->id:'';
						$resultArr['code'] =  isset($appb->application->code)?$appb->application->code:'';
						$resultArr['country']= isset($appb->application->countryname)?$appb->application->countryname:'';
						$resultArr['company_name'] =  isset($appb->application->companyname)?$appb->application->companyname:'';
						$resultArr['oss_label'] =  isset($appb->application->franchise_id)?$usermodel->ossnumberdetail($appb->application->franchise_id):'';
						$resultArr['application_unit_count'] =  isset($appb->application->applicationunit)?count($appb->application->applicationunit):'';
						$resultArr['first_name'] =  isset($appb->application->firstname)?$appb->application->firstname:'';
						$resultArr['audit_type_label'] =  isset($appb->application->audit_type)?$application->arrAuditType[$appb->application->audit_type]:'';
						$resultArr['created_at'] =  isset($appb->application->created_at)?date($date_format,$appb->application->created_at):'';
						$resultArr['status_label_color']=$application->arrBrandColor[$appb->status];
						$resultArr['brand_name'] = isset($brandmod->name)?$brandmod->name:'NA';
						$resultArr['brand_id'] = $brandmod->id;
						$resultArr['brand_group'] = isset($brandmod->brandgroup->name)?$brandmod->brandgroup->name:"NA";
	
						$arrAppStd=array();
						$appStd=isset($appb->application->applicationstandardview)?$appb->application->applicationstandardview:'';
					
	
						if($appStd!='' && count($appStd)>0)
						{	
							foreach($appStd as $app_standard)
							{
								$arrAppStd[]=$app_standard->standard->code;
							}
						}					
						$resultArr['application_standard']=implode(', ',$arrAppStd);
						$data[] = $resultArr;
					}
				}
			}	
		}else{
			$appbrand = Application::find()->alias('app');

			if($user_type==2){
				$appbrand = $appbrand->where(['app.customer_id'=>$userid])->all();
			}else if($user_type==1 && in_array('brand_report',$rules)){
				$appbrand = $appbrand->join('inner join','tbl_application_brands as appbrands','app.id=appbrands.app_id');
				$appbrand = $appbrand->join('inner join','tbl_brands as br','br.id=appbrands.brand_id');
				$appbrand = $appbrand->join('inner join','tbl_brand_group as brg','brg.id=br.brand_group_id');
				$appbrand = $appbrand->andWhere(['brg.user_id'=>$userid])->all();
			}
			
			
			$totalCount = count($appbrand);

			if(count($appbrand)>0){
				foreach($appbrand as $apbr){
					    $resultArr = array();
					    $resultArr['app_id'] = $apbr['id'];
						
	
						
						$resultArr['code'] =  $apbr->code;
						$resultArr['country']= $apbr->countryname;
						$resultArr['company_name'] =  $apbr->companyname;
						$resultArr['oss_label'] =  $usermodel->ossnumberdetail($apbr->franchise_id);
						$resultArr['application_unit_count'] =  count($apbr->applicationunit);
						$resultArr['first_name'] =  $apbr->firstname;
						$resultArr['audit_type_label'] =  $application->arrAuditType[$apbr->audit_type];
						$resultArr['created_at'] =  date($date_format,$apbr->created_at);
						
	
						$arrAppStd=array();
						$appStd=$apbr->applicationstandardview;
					
	
						if(count($appStd)>0)
						{	
							foreach($appStd as $app_standard)
							{
								$arrAppStd[]=$app_standard->standard->code;
							}
						}					
						$resultArr['application_standard']=implode(', ',$arrAppStd);

						$brandname=array();
						$brandgroup=array();

						$appbrandmodel = $apbr->applicationbrands;
						if(count($appbrandmodel)>0){
							foreach($appbrandmodel as $brmod){
								if($user_type==2){
									$brandname[]=$brmod->brands->name;
									$brandgroup[]=$brmod->brands->brandgroup->name;
								}else if(in_array('brand_report',$rules)){
									if($brmod->brands->brandgroup->user_id==$userid){
										$brandname[]=$brmod->brands->name;
										$brandgroup[]=$brmod->brands->brandgroup->name;
									}
								}
								
							}
						}
						$brandname = implode(', ',$brandname);
						$brandgroup = implode(', ',array_unique($brandgroup));
						$resultArr['brand_name']=($brandname!="")?$brandname:"NA";
						$resultArr['brand_group']=($brandgroup!="")?$brandgroup:"NA";
						$data[] = $resultArr;

						
				}
			}
		}
		return ['applications'=>$data,'total'=>$totalCount];

		// return ['applications'=>$app_list,'total'=>$totalCount];
	}
	
	public function actionBrandApprove()
	{
		$datapost = Yii::$app->request->post();
		$data =json_decode($datapost['formvalues'],true);
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		$target_dir_company = Yii::$app->params['company_files'];
		if ($data) 
		{	

			// $appmodel = ApplicationBrands::find()->where(['app_id'=>$data['id'],'brand_id'=>$data['brand_id'],'id'=>$data['app_approver_brand_id']])->one();

			// if($appmodel!='' && $appmodel!==null){
			// 	if($data['actiontype']=='brandreject'){
			// 		$appmodel->status=2;
			// 		$appmodel->comment=$data['reject_comment'];

			// 		if($appmodel->validate() && $appmodel->save()){
			// 			$responsedata = array('status'=>1,'message'=>'Brand has been rejected successfully','brand_status'=>$appmodel->status);
			// 		}
			// 	}
			// 	else if($data['actiontype']=='brandapprove'){
			// 		$appmodel->status=1;
			// 		$appmodel->comment=$data['reject_comment'];
			// 		if($appmodel->validate() && $appmodel->save()){
			// 			$responsedata = array('status'=>1,'message'=>'Brand has been approved successfully','brand_status'=>$appmodel->status);
			// 		}
			// 	}
			// }

			if($data['actiontype']=='editbrand'){
				// $chappbrmod = ApplicationBrands:: find()->where(['app_id'=>$data['id'],'brand_id'=>$data['chbrand_id']])->one();
				// if($chappbrmod!='' && $chappbrmod!=null){
				// 	$responsedata = array('status'=>0,'message'=>'Please select different brand!');	
				// }
				// else{
				// 	$appmodel->brand_id=$data['chbrand_id'];
				// 	$appmodel->status=3;
				// 	if($appmodel->validate() && $appmodel->save()){
				// 		$responsedata= array('status'=>1,'message'=>'Brand has been changed successfully','brand_status'=>$appmodel->status);
				// 	}
				// }
				$model = Application::find()->where(['id'=>$data['id']])->one();
				if($model!==null){

					if(isset($_FILES['brand_file']['name']))
					{
					
					$tmp_name = $_FILES["brand_file"]["tmp_name"];
					$name = $_FILES["brand_file"]["name"];
					$model->brand_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir_company);	
					}
					$model->is_brand=1;
					$model->save();

					ApplicationBrands::deleteAll(['app_id'=>$data['id']]);
					if(is_array($data['brand_ids']) && count($data['brand_ids'])>0){
						foreach($data['brand_ids'] as $bids){
							$appbrmod = new ApplicationBrands();
							$appbrmod->app_id = $data['id'];
							$appbrmod->brand_id=$bids;
							$appbrmod->status=1;
							$appbrmod->save();
						}
					}
					$responsedata= array('status'=>1,'message'=>'Brand has been changed successfully');	
                }
		}
	}
    return $responsedata;

	}

	// public function actionGetApplications()
    // {
	// 	$appmodel = new Application();
	// 	$data = Yii::$app->request->post();
	// 	$date_format = Yii::$app->globalfuns->getSettings('date_format');
	// 	$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");	
	// 	if ($data) 
	// 	{	
	// 		$model = Application::find()->where(['customer_id' => $data['id']])->all();
	// 		if(count($model)>0)
	// 		{
	// 			$app_list=array();
	// 			foreach($model as $application)
	// 			{
	// 				$data=array();
	// 				$data['id']=$application->id;
	// 				$data['code']=$application->code;
	// 				$data['company_name']=$application->companyname;
	// 				$data['address']=$application->address;
	// 				$data['zipcode']=$application->zipcode;
	// 				$data['city']=$application->city;
	// 				$data['tax_no']=$application->tax_no;
	// 				$data['title']=$application->title;
	// 				$data['first_name']=$application->firstname;
	// 				$data['last_name']=$application->lastname;
	// 				$data['job_title']=$application->jobtitle;
	// 				$data['telephone']=$application->telephone;
	// 				$data['email_address']=$application->emailaddress;
	// 				$data['created_at']=date($date_format,$application->created_at);
	// 				$data['status']=$appmodel->arrStatus[$application->status];
	// 				$data['overall_status']=$appmodel->arrOverallStatus[$application->overall_status];
	// 				$data['status_id']=$application->status;
	// 				$data['status_label_color']=$appmodel->arrStatusColor[$application->status];
	// 				$data['audit_type']=$application->audit_type;
	// 				$data['audit_type_label']=$appmodel->arrAuditType[$application->audit_type];
	// 				$data['application_unit_count']=count($application->applicationunit);
	// 				$data['process_id']='';
	// 				$data['parent_app_id']= $application->parent_app_id;
					
	// 				$arrAppStd=array();
	// 				$appStd = $application->applicationstandard;
	// 				if(count($appStd)>0)
	// 				{	
	// 					foreach($appStd as $app_standard)
	// 					{
	// 						$arrAppStd[]=$app_standard->standard->code;
	// 					}
	// 				}					
	// 				$data['application_standard']=implode(', ',$arrAppStd);
									
	// 				$app_list[]=$data;
	// 			}
	// 			$responsedata =array('status'=>1,'applications'=>$app_list);
	// 		}
	// 	}
	// 	return $this->asJson($responsedata);
	// }

	public function actionGetUserid()
    {
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");	
		if ($data) 
		{	
			$model = Application::find()->where(['id' => $data['id']])->one();
			if($model!==null)
			{
				$app_ids = [];
				$appmodel = Application::find()->where(['customer_id' => $model->customer_id])->all();	
				if(count($appmodel)>0)
				{
					foreach($appmodel as $val)
					{
						$app_ids[] = $val->id;	
					}
				}
				return['app_id'=>$app_ids];
			}
		}

	}

    public function actionCreate()
    {
		
		$model = new Application();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];


		$datapost = Yii::$app->request->post();

		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		if($resource_access != 1){
			$data = json_decode($datapost['formvalues'],true);

			if($user_type== Yii::$app->params['user_type']['user'] ||  $user_type==3 ){
				return $responsedata;
			}else if($user_type== Yii::$app->params['user_type']['customer'] && (!isset($data['enquiry_id']) || $data['enquiry_id'] == '') && (!isset($data['app_id']) || $data['app_id'] == '')){
				return $responsedata;
			}
			//
			/*else if($user_type==3 && $role!=0 && ! in_array('create_application',$rules) ){
				return $responsedata;
			}
			*/
			if(isset($data['enquiry_id']) && $data['enquiry_id'] != ''){
				$enquirymodeldata = Enquiry::find()->where(['id'=>$data['enquiry_id']])->one();
				if(Yii::$app->userrole->isCustomer())
				{
					if($enquirymodeldata->app_id !='' || $enquirymodeldata->customer_id!=$userid)
					{
						return false;
					}
				}
			}else if(isset($data['app_id']) && $data['app_id'] != ''){
				$appModelCheck = Application::find()->where(['id' => $data['app_id']])->one();
				if($appModelCheck!==null)
				{
					if(Yii::$app->userrole->isCustomer())
					{
						if($appModelCheck->customer_id!=$userid)
						{
							return false;
						}
					}
				}	
			}
		}
		

        if (Yii::$app->request->post()) 
		{
			
			$connection = Yii::$app->getDb();
			$data =json_decode($datapost['formvalues'],true);
			

			//print_r($data['units']);
			//print_r($data['products']); die;
			//return ['units'=>$data['units'], 'products'=>$data['products']]; die;
			$target_dir = Yii::$app->params['certification_standard_files'];

			$target_dir_company = Yii::$app->params['company_files'];
			//print_r($data); die;
			
			if(isset($_FILES['company_file']['name']))
			{
				
				$tmp_name = $_FILES["company_file"]["tmp_name"];
	   			$name = $_FILES["company_file"]["name"];
				$model->company_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir_company);	


				/*	
				$filename = $_FILES['company_file']['name'];
				$target_file = $target_dir . basename($filename);
				$target_file = $target_dir . basename($filename);
				$actual_name = pathinfo($filename,PATHINFO_FILENAME);
				$original_name = $actual_name;
				$extension = pathinfo($filename, PATHINFO_EXTENSION);
				$i = 1;
				$name = $actual_name.".".$extension;
				while(file_exists($target_dir_company.$actual_name.".".$extension))
				{           
					$actual_name = (string)$original_name.$i;
					$name = $actual_name.".".$extension;
					$i++;
				}
				if (move_uploaded_file($_FILES['company_file']["tmp_name"], $target_dir_company .$actual_name.".".$extension)) {
					$model->company_file=isset($name)?$name:"";
				}
				*/
			}
			
			if(isset($data['app_id']) && $data['app_id'] != ''){
				$appModel = Application::find()->where(['id' => $data['app_id']])->one();
				if($appModel!==null)
				{
					$model->customer_id=$appModel->customer_id;
					$model->franchise_id=$appModel->franchise_id;
					$model->parent_app_id=$appModel->id;					
				}
			}	
				
			$enquirymodel = null;
			if(isset($data['enquiry_id']) && $data['enquiry_id'] != ''){
				$enquirymodel = Enquiry::find()->where(['id'=>$data['enquiry_id']])->one();
				$model->customer_id=$enquirymodel->customer_id;
			}
			
			
			
			
			if($enquirymodel !==null && $enquirymodel->franchise_id !==null && $enquirymodel->franchise_id !=''){
				$model->franchise_id = $enquirymodel->franchise_id;
			}

			if($data['actiontype']=='draft'){
				$model->status = $model->arrEnumStatus['open'];
				 
				
			}else{

				$model->status = $model->arrEnumStatus['submitted'];
				
				/*
				$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'app_submitted_mail_franchise'])->one();
				if($mailContent !== null )
				{
					$mailmsg = str_replace('{COMPANY-NAME}', $data['company_name'], $mailContent['message'] );
					$mailmsg = str_replace('{COMPANY-EMAIL}', $data['company_email'], $mailmsg );
					$mailmsg = str_replace('{COMPANY-TELEPHONE}', $data['company_telephone'], $mailmsg );
					$mailmsg = str_replace('{CONTACT-NAME}', $data['first_name']." ".$data['last_name'], $mailmsg );
					
					$franchise = UserCompanyInfo::find()->select('company_email')->where(['user_id' => $model->franchise_id])->one();
					if($franchise !== null )
					{
						$MailLookupModel = new MailLookup();
						$MailLookupModel->to=$franchise['company_email'];						
						$MailLookupModel->bcc='';
						$MailLookupModel->subject=$mailContent['subject'];
						$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
						$MailLookupModel->attachment='';
						$MailLookupModel->mail_notification_id='';
						$MailLookupModel->mail_notification_code='';
						$Mailres=$MailLookupModel->sendMail();
					}
				}
				*/

			}
			
			/*
            $model->company_name=isset($data['company_name'])?$data['company_name']:"";
            $model->address=isset($data['address'])?$data['address']:"";
			$model->zipcode=isset($data['zipcode'])?$data['zipcode']:"";
			$model->city=isset($data['city'])?$data['city']:"";
			$model->state_id=isset($data['state_id'])?$data['state_id']:"";
            $model->country_id=isset($data['country_id'])?$data['country_id']:"";
			$model->salutation=isset($data['salutation'])?$data['salutation']:"";
			$model->title=isset($data['title'])?$data['title']:"";
            $model->first_name=isset($data['first_name'])?$data['first_name']:"";
			$model->last_name=isset($data['last_name'])?$data['last_name']:"";
			$model->job_title=isset($data['job_title'])?$data['job_title']:"";
            $model->telephone=isset($data['telephone'])?$data['telephone']:"";
			$model->email_address=isset($data['email_address'])?$data['email_address']:"";
			*/
			


			$model->certification_status=isset($data['certification_status'])?$data['certification_status']:"";
			//$model->preferred_partner_id=isset($data['preferred_partner_id'])?$data['preferred_partner_id']:"";
			$standard_addition_id = '';
			if(isset($data['standard_addition_id']) && $data['standard_addition_id']>0)
			{
				$model->audit_type=$model->arrEnumAuditType['standard_addition'];
				$standard_addition_id = $data['standard_addition_id'];
			}else if(isset($data['renewal_id']) && $data['renewal_id'] != '' && isset($data['app_audit_type']) && $data['app_audit_type'] != ''){
				$model->audit_type = 2;
			}
			
			$maxid = Application::find()->max('id');
			if(!empty($maxid)) 
			{
				$maxid = $maxid+1;
				$zerostr="00";
				if(strlen($maxid)>1)
				{
					$zerostr="0";
				}
				else if(strlen($maxid)>2)
				{
					$zerostr="";
				}
				$appcode="APRNO-".date("y")."-".$zerostr.$maxid;
			}
			else
			{
				$appcode="APRNO-".date("y")."-001";
			}
			$model->code=$appcode;
			$model->tax_no=$data['tax_no'];
			
			$model->created_by=$userData['userid'];

            if($model->validate() && $model->save())
        	{
				$renewal_id = isset($data['renewal_id'])?$data['renewal_id']:'';
				if($renewal_id !=''){
					$ApplicationRenewal = ApplicationRenewal::find()->where(['id' => $renewal_id])->one();
					if($ApplicationRenewal !== null){
						$ApplicationRenewal->new_app_id = $model->id;
						$ApplicationRenewal->save();
					}
				}
				

				
				$ApplicationChangeAddress = new ApplicationChangeAddress();
				if(isset($data['app_id']) && $data['app_id']>0 && $model->audit_type != $model->arrEnumAuditType['renewal']){
					$ApplicationChangeAddress->parent_app_id = $data['app_id'];
				}else{
					$ApplicationChangeAddress->parent_app_id = $model->id;
				}
				
				$ApplicationChangeAddress->current_app_id = $model->id;
				$ApplicationChangeAddress->company_name=isset($data['company_name'])?$data['company_name']:"";
				$ApplicationChangeAddress->address=isset($data['address'])?$data['address']:"";
				$ApplicationChangeAddress->zipcode=isset($data['zipcode'])?$data['zipcode']:"";
				$ApplicationChangeAddress->city=isset($data['city'])?$data['city']:"";
				$ApplicationChangeAddress->state_id=isset($data['state_id'])?$data['state_id']:"";
				$ApplicationChangeAddress->country_id=isset($data['country_id'])?$data['country_id']:"";
				$ApplicationChangeAddress->salutation=isset($data['salutation'])?$data['salutation']:"";
				$ApplicationChangeAddress->title=isset($data['title'])?$data['title']:"";
				$ApplicationChangeAddress->first_name=isset($data['first_name'])?$data['first_name']:"";
				$ApplicationChangeAddress->last_name=isset($data['last_name'])?$data['last_name']:"";
				$ApplicationChangeAddress->job_title=isset($data['job_title'])?$data['job_title']:"";
				$ApplicationChangeAddress->telephone=isset($data['telephone'])?$data['telephone']:"";
				$ApplicationChangeAddress->email_address=isset($data['email_address'])?$data['email_address']:"";
				$ApplicationChangeAddress->save();
				
				$model->address_id = $ApplicationChangeAddress->id;
				$model->save();


        		$apptype_standardaddition = 0;
				if($model->audit_type==$model->arrEnumAuditType['standard_addition']){
					$apptype_standardaddition = 1;
				}
        		Yii::$app->globalfuns->updateApplicationOverallStatus($model->id, $model->arrEnumOverallStatus['application_in_process']);


        		if(isset($data['standard_addition_id']) && $data['standard_addition_id']>0){
        			$StandardAddition = StandardAddition::find()->where(['id'=>$data['standard_addition_id']])->one();
        			if($StandardAddition !==null){
        				$StandardAddition->new_app_id=$model->id;
						$StandardAddition->save();
						
						$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'standard_addition'])->one();
						if($mailContent !== null )
						{
							$additiongrid = $this->renderPartial('@app/mail/layouts/AdditionCompanyGridTemplate',[
								'model' => $model
							]);

							$mailmsg = str_replace('{NEW-APPLICATION-DETAILS-GRID}', $additiongrid, $mailContent['message'] );

							$franchise = UserCompanyInfo::find()->select('company_email')->where(['user_id' => $model->franchise_id])->one();
							if($franchise !== null )
							{
								$MailLookupModel = new MailLookup();
								$MailLookupModel->to=$franchise['company_email'];								
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
        		Yii::$app->globalfuns->updateApplicationStatus($model->id,$model->status,$model->audit_type);

				if($enquirymodel !==null && $enquirymodel->franchise_id !==null && $enquirymodel->franchise_id !=''){
					$enquirymodel->app_id = $model->id;
					$enquirymodel->save();
				}
				$additionstandards = [];
				if(is_array($data['standards']) && count($data['standards'])>0)
                {
					$data['standards'] = array_unique($data['standards']);
                    foreach ($data['standards'] as $value)
                    { 
                    	if($standard_addition_id && $standard_addition_id>0){
							$chkappstdmodel=ApplicationStandard::find()->where(['app_id'=>$data['app_id'], 'standard_id'=>$value,'standard_status'=>0])->one();
							
							if($chkappstdmodel===null){
                    			$appstdmodel=new ApplicationStandard();
		                        $appstdmodel->app_id=$model->id;
		                        $appstdmodel->standard_id=isset($value)?$value:"";
		                        $appstdmodel->version = $this->getStandardVersion($value);
		                        $appstdmodel->standard_addition_type=1;
								$appstdmodel->save(); 
								$additionstandards[] = $appstdmodel->standard_id;
							}
							
							/*
							if($chkappstdmodel!==null){
                    			$appstdmodel=new ApplicationStandard();
		                        $appstdmodel->app_id=$model->id;
		                        $appstdmodel->standard_id=isset($value)?$value:"";
		                        $appstdmodel->version = $this->getStandardVersion($value);
		                        //$appstdmodel->standard_addition_type=isset($value)?$value:"";
		                        $appstdmodel->save(); 
                    		}else{
                    			$appstdmodel=new ApplicationStandard();
		                        $appstdmodel->app_id=$model->id;
		                        $appstdmodel->standard_id=isset($value)?$value:"";
		                        $appstdmodel->standard_addition_type=1;
		                        $appstdmodel->version = $this->getStandardVersion($value);
		                        $appstdmodel->save(); 
							}
							*/
	                        
                    	}else{
                    		$appstdmodel=new ApplicationStandard();
	                        $appstdmodel->app_id=$model->id;
	                        $appstdmodel->standard_id=isset($value)?$value:"";
	                        $appstdmodel->version = $this->getStandardVersion($value);
	                        //$appstdmodel->standard_addition_type=isset($value)?$value:"";
	                        $appstdmodel->save(); 
                    	}
                        
                    }
				}
				
				if(is_array($data['app_checklist']) && count($data['app_checklist'])>0)
				{
					$target_dir_checklist = Yii::$app->params['application_checklist_file'];
					foreach ($data['app_checklist'] as $value)
					{ 
						$question_id = $value['question_id'];
						$document = '';
						if(isset($_FILES['checklist_file']['name'][$question_id]))
						{
							$tmp_name = $_FILES["checklist_file"]["tmp_name"][$question_id];
				   			$name = $_FILES["checklist_file"]["name"][$question_id];
							$document=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir_checklist);	
						}
						$checklistmodel=new ApplicationChecklistComment();
						$checklistmodel->app_id=$model->id;
						$checklistmodel->question_id=$value['question_id'];
						$checklistmodel->question=$value['question'];
						$checklistmodel->answer=$value['answer'];
						$checklistmodel->comment=$value['comment'];
						$checklistmodel->document=$document;
						
						$checklistmodel->save(); 
						
					}
				}

				if(isset($data['app_certifiedothercblist']) && is_array($data['app_certifiedothercblist']) && count($data['app_certifiedothercblist'])>0)
				{
					$target_dir_checklist = Yii::$app->params['certifiedbyothercb_file'];
					foreach ($data['app_certifiedothercblist'] as $value)
					{ 
						//$question_id = $value['question_id'];
						//$document = '';
						//echo $question_id;
						$standard_id = $value['standard_id'];
						if(isset($_FILES['certifiedbyothercb_file']['name'][$standard_id]))
						{
							$tmp_name = $_FILES["certifiedbyothercb_file"]["tmp_name"][$standard_id];
				   			$name = $_FILES["certifiedbyothercb_file"]["name"][$standard_id];
							$document=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir_checklist);	
						}else{
							$document = $value['document'];
						}

						if($document !='' && $value['standard_id']!='' && $value['certification_body']!='' && $value['validity_date']!='' )
						{
							$othercbmodel = new ApplicationCertifiedByOtherCB();
							$othercbmodel->app_id=$model->id;
							$othercbmodel->standard_id=$value['standard_id'];
							$othercbmodel->certification_body=$value['certification_body'];
							$othercbmodel->validity_date=date('Y-m-d',strtotime($value['validity_date']));
							$othercbmodel->certification_file=$document;
							$othercbmodel->save(); 
						}							
						
					}
				}

				if(is_array($data['products']) && count($data['products'])>0)
                {
                    foreach ($data['products'] as $value)
                    { 
						/*
						$material_composition_id = '';
						if(isset($value['material_composition']) && $value['material_composition'] !=''){
							$materialmodel = ProductTypeMaterialComposition::find()->where(['name' => $value['material_composition']])->one();
							if($materialmodel!=null)
							{
								$material_composition_id = $materialmodel->id;
							}
							else
							{
								$materialmodel=new ProductTypeMaterialComposition();
								$materialmodel->product_type_id=$value['product_type'];
								$materialmodel->name=$value['material_composition'];
								$materialmodel->status=0;
								$materialmodel->approval_status=1;
								$materialmodel->save(); 
								$material_composition_id = $materialmodel->id;
							}
						}
						*/
						if($apptype_standardaddition && (!isset($value['addition_type']) || $value['addition_type']!="1"))
						{
							continue;
						}
                       	$appproductmodel=new ApplicationProduct();
                        $appproductmodel->app_id=$model->id;
						$appproductmodel->product_id=isset($value['product_id'])?$value['product_id']:"";
						$appproductmodel->product_type_id =isset($value['product_type'])?$value['product_type']:"";
						$appproductmodel->wastage=isset($value['wastage'])?$value['wastage']:"";

						$appproductmodel->product_addition_type=($apptype_standardaddition && isset($value['addition_type']) && $value['addition_type']=="1") ?1:0;

						$Product = Product::find()->where(['id'=> $value['product_id']])->one();
						if($Product !== null){
							$appproductmodel->product_name = $Product->name;
						}
						$ProductType = ProductType::find()->where(['id'=> $value['product_type']])->one();
						if($ProductType !== null){
							$appproductmodel->product_type_name = $ProductType->name;
						}
						$appproductmodel->save(); 
						
						if(isset($value['productStandardList']) && is_array($value['productStandardList']) ){
							foreach($value['productStandardList'] as $prdstd){
								$appproductstandardmodel=new ApplicationProductStandard();
								$appproductstandardmodel->standard_id=$prdstd['standard_id'];
								$appproductstandardmodel->application_product_id=$appproductmodel->id;
								$appproductstandardmodel->label_grade_id =$prdstd['label_grade'];
								$StandardLabelGrade = StandardLabelGrade::find()->where(['id'=> $prdstd['label_grade']])->one();
								if($StandardLabelGrade !== null){
									$appproductstandardmodel->label_grade_name = $StandardLabelGrade->name;
								}
								$appproductstandardmodel->save(); 
							}
						}
						if(isset($value['productMaterialList']) && is_array($value['productMaterialList']) ){
							foreach($value['productMaterialList'] as $prdstd){
								$appproductmaterialmodel=new ApplicationProductMaterial();
								$appproductmaterialmodel->app_product_id=$appproductmodel->id;
								$appproductmaterialmodel->material_id=$prdstd['material_id'];
								$appproductmaterialmodel->material_type_id =$prdstd['material_type_id'];
								$ProductTypeMaterialComposition = ProductTypeMaterialComposition::find()->where(['id'=> $prdstd['material_id']])->one();
								if($ProductTypeMaterialComposition !== null){
									$appproductmaterialmodel->material_name = $ProductTypeMaterialComposition->name;
									if(isset($ProductTypeMaterialComposition->material_type[$prdstd['material_type_id']])){
										$appproductmaterialmodel->material_type_name = $ProductTypeMaterialComposition->material_type[$prdstd['material_type_id']];
									}
								}
								//$ProductTypeMaterialComposition = new ProductTypeMaterialComposition();
								
								/*$StandardLabelGrade = StandardLabelGrade::find()->where(['id'=> $prdstd['label_grade']])->one();
								if($StandardLabelGrade !== null){
									$appproductstandardmodel->label_grade_name = $StandardLabelGrade->name;
								}*/
								$appproductmaterialmodel->percentage =$prdstd['material_percentage'];
								$appproductmaterialmodel->save(); 
							}
						}



						
						/*
						$app_productmodel=new ApplicationProduct();
                        $app_productmodel->app_id=$model->id;
						$app_productmodel->product_id=isset($value['product_id'])?$value['product_id']:"";
						$app_productmodel->product_type_id =isset($value['product_type'])?$value['product_type']:"";
						$app_productmodel->wastage=isset($value['wastage'])?$value['wastage']:"";
						$app_productmodel->standard_id=isset($value['standard_id'])?$value['standard_id']:"";
						$app_productmodel->label_grade_id=isset($value['label_grade'])?$value['label_grade']:"";
						$app_productmodel->save();

						$pdtlistval[$value['pdt_index']] = $app_productmodel->id;
						*/

						


						/*
						$appproductdetailsmodel=new ApplicationProductDetails();
                        $appproductdetailsmodel->app_id=$model->id;
						$appproductdetailsmodel->product_id=isset($value['product_id'])?$value['product_id']:"";
						$appproductdetailsmodel->product_type_id =isset($value['product_type'])?$value['product_type']:"";
						$appproductdetailsmodel->wastage=isset($value['wastage'])?$value['wastage']:"";
						$appproductdetailsmodel->app_product_id=$appproductmodel->id;
						$appproductdetailsmodel->standard_id=isset($value['standard_id'])?$value['standard_id']:"";
						$appproductdetailsmodel->label_grade_id=isset($value['label_grade'])?$value['label_grade']:"";
						//$appproductdetailsmodel->material_name=isset($value['wastage'])?$value['wastage']:"";
						$appproductdetailsmodel->save(); 
						*/

                    }
				}
				
				if(is_array($data['units']) && count($data['units'])>0)
                {
                    foreach ($data['units'] as $unitkey => $unitvalue)
                    { 
						if(isset($unitvalue['deleted']) && $unitvalue['deleted'] ==1){
							continue;
						}

						if($apptype_standardaddition){
							//&& in_array($additionstandards,$data['standards'])
							if($unitvalue['unit_type'] ==1){
								$unitstandarddata = $data['standards'];
							}else{
								$unitstandarddata = $unitvalue['standards'];
							}

							$commonstandards = [];
							if(is_array($additionstandards) && is_array($unitstandarddata)){
								$commonstandards=array_intersect($additionstandards,$unitstandarddata);
							}							
							if(count($commonstandards)<=0){
								continue;
							}
						}
						if($apptype_standardaddition && count($unitstandarddata)<=0){
							continue;
						}
						
						/*
						if($apptype_standardaddition && (!isset($value['addition_type']) || $value['addition_type']!="1")
						{
							continue;
						}
						*/
                        $appunitmodel=new ApplicationUnit();
						$appunitmodel->app_id=$model->id;
						
						$appunitmodel->parent_unit_id=isset($unitvalue['unit_id'])?$unitvalue['unit_id']:"";

						$appunitmodel->unit_type=isset($unitvalue['unit_type'])?$unitvalue['unit_type']:"";
						$appunitmodel->name=isset($unitvalue['name'])?$unitvalue['name']:"";
						$appunitmodel->code=isset($unitvalue['code'])?$unitvalue['code']:"";
						$appunitmodel->address=isset($unitvalue['address'])?$unitvalue['address']:"";
						$appunitmodel->zipcode=isset($unitvalue['zipcode'])?$unitvalue['zipcode']:"";
						$appunitmodel->city=isset($unitvalue['city'])?$unitvalue['city']:"";
						$appunitmodel->state_id=isset($unitvalue['state_id'])?$unitvalue['state_id']:"";
						$appunitmodel->country_id=isset($unitvalue['country_id'])?$unitvalue['country_id']:"";
						$appunitmodel->no_of_employees=isset($unitvalue['no_of_employees'])?$unitvalue['no_of_employees']:"";
						
						if($appunitmodel->unit_type == 1){
							$ApplicationChangeAddress->unit_name=isset($unitvalue['name'])?$unitvalue['name']:"";
							$ApplicationChangeAddress->unit_address=isset($unitvalue['address'])?$unitvalue['address']:"";
							$ApplicationChangeAddress->unit_zipcode=isset($unitvalue['zipcode'])?$unitvalue['zipcode']:"";
							$ApplicationChangeAddress->unit_city=isset($unitvalue['city'])?$unitvalue['city']:"";
							$ApplicationChangeAddress->unit_state_id=isset($unitvalue['state_id'])?$unitvalue['state_id']:"";
							$ApplicationChangeAddress->unit_country_id=isset($unitvalue['country_id'])?$unitvalue['country_id']:"";
							$ApplicationChangeAddress->save();
						}
							
						
						
						$unit_addition_type = 0;
						if($apptype_standardaddition && isset($unitvalue['addition_type']) && $unitvalue['addition_type']=="1"){
							$unit_addition_type = 1;
						}
						$appunitmodel->unit_addition_type= $unit_addition_type=="1"?1:0;
						
						if($appunitmodel->validate() && $appunitmodel->save())
        				{
							if(is_array($unitvalue['products']) && count($unitvalue['products'])>0)
							{
								//print_r($unitvalue['products']);
								foreach ($unitvalue['products'] as $val1)
								{ 
									/*
									id
									
									standard_id
									standard_id
									label_grade
									*/
									 
									 /*
									id => product category id
									product_type_id => Product Description
									standard_id => Product Standard
									*/
									
									/*
									$pdtdetailsmodel = ApplicationProduct::find()->where([
									'product_id' => $val1['id'],'app_id' => $model->id
									,'product_type_id' => $val1['product_type_id'],'wastage'=>$val1['wastage']])->one();

									$pdtstdmodel = ApplicationProductStandard::find()->where([
										'standard_id' => $val1['standard_id'],'label_grade_id' => $val1['label_grade']
										,'application_product_id' => $pdtdetailsmodel->id ])->one();
									*/	
									if(!isset( $val1['productMaterialList'])){
										continue;
									}
									$productMaterialList = $val1['productMaterialList'];
									$queryStr = [];
									foreach($productMaterialList as $materiall){
										$queryStr[] = "  ( material_id = '".$materiall['material_id']."' AND material_type_id = '".$materiall['material_type_id']."' AND percentage = '".$materiall['material_percentage']."') ";
									}
									$totalCompCnt = count($queryStr);

									$queryCondition = ' ('.implode(' OR ',$queryStr).') ';
									/*
									pdt_mat.material_id='".$business_sector_id."' AND pdt_mat.material_type_id='".$business_sector_id."' 
									AND pdt_mat.percentage='".$business_sector_id."'
									*/
									$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
									$command = $connection->createCommand("SELECT  COUNT(pdt.id) as matcnt,pdt_std.id  as pdt_std_id  
									from tbl_application_product as pdt 
									INNER JOIN tbl_application_product_standard as pdt_std on pdt_std.application_product_id = pdt.id 
									INNER JOIN tbl_application_product_material as pdt_mat on pdt_mat.app_product_id = pdt.id 
									WHERE 
									pdt.product_id='".$val1['id']."' AND pdt.product_type_id='".$val1['product_type_id']."'
									 AND pdt.wastage='".$val1['wastage']."' 
									AND pdt_std.standard_id='".$val1['standard_id']."' AND pdt_std.label_grade_id='".$val1['label_grade']."' 
									AND pdt.app_id='".$model->id."' 

									AND ".$queryCondition."
									
									group by pdt.id HAVING matcnt=".$totalCompCnt." ");
									$result = $command->queryOne();
									$pdt_std_id = 0;
									if($result  !== false){
										$pdt_std_id = $result['pdt_std_id'];
									}
									//echo $pdt_std_id.'<br><br>';

									//$pdt_id = $pdtlistval[$val1['pdt_index']];
									$addition_type = $apptype_standardaddition && isset($val1['addition_type']) && $val1['addition_type']=="1"?$val1['addition_type']:0;

									if($apptype_standardaddition && !$addition_type){
										continue;
									}
									if($pdt_std_id<=0){
										continue;
									}
									$appunitproductmodel=new ApplicationUnitProduct();
									$appunitproductmodel->unit_id=$appunitmodel->id;
									$appunitproductmodel->application_product_standard_id=$pdt_std_id;//$pdtstdmodel->id;
									$appunitproductmodel->product_addition_type = $addition_type=="1"?1:0;
									$appunitproductmodel->save();
								}
							}

							

							if(is_array($unitvalue['business_sector_id']) && count($unitvalue['business_sector_id'])>0)
							{
								foreach ($unitvalue['business_sector_id'] as $val3)
								{ 

									//if($unit_addition_type){
										
                    					$appunitbsectorsmodel=new ApplicationUnitBusinessSector();
                    					if(!$apptype_standardaddition || (isset($unitvalue['business_sector_exists'] ) && is_array($unitvalue['business_sector_exists']) && count($unitvalue['business_sector_exists'] )>0 && in_array($val3,$unitvalue['business_sector_exists']))){
                    						
                    						$appunitbsectorsmodel->addition_type = 0;
                    					}else{
                    						$appunitbsectorsmodel->addition_type = 1;
                    					}
			                    		//if($apptype_standardaddition && !$appunitbsectorsmodel->addition_type){
										//	continue;
										//}

					                    $appunitbsectorsmodel->unit_id=$appunitmodel->id;
										$appunitbsectorsmodel->business_sector_id=isset($val3)?$val3:"";

										$BusinessSector = BusinessSector::find()->where(['id'=>$val3])->one();
										if($BusinessSector !== null){
											$appunitbsectorsmodel->business_sector_name= $BusinessSector->name;
										}
										

										$appunitbsectorsmodel->save(); 
			                    		
                    				//}else{
									//	$appunitbsectorsmodel=new ApplicationUnitBusinessSector();
									//	$appunitbsectorsmodel->unit_id=$appunitmodel->id;
									//	$appunitbsectorsmodel->business_sector_id=isset($val3)?$val3:"";
									//	$appunitbsectorsmodel->save(); 
									//}
								}
							}

							if($unitvalue['unit_type'] ==1){
								foreach ($data['standards'] as $valuesstd)
                    			{ 




                    				//if($unit_addition_type){

                    					$appunitstandardmodel=new ApplicationUnitStandard();
                    					if(!$apptype_standardaddition || (isset($unitvalue['unit_exists'] ) && is_array($unitvalue['unit_exists']) && count($unitvalue['unit_exists'] )>0 && in_array($valuesstd,$unitvalue['unit_exists']))){
                    						
                    						$appunitstandardmodel->addition_type = 0;
                    					}else{
                    						$appunitstandardmodel->addition_type = 1;
                    					}
			                    		if($apptype_standardaddition && !$appunitstandardmodel->addition_type){
											continue;
										}

					                    $appunitstandardmodel->unit_id=$appunitmodel->id;
										$appunitstandardmodel->standard_id=$valuesstd;
										$appunitstandardmodel->save(); 
			                    		
                    				//}else{
                    				//	$appunitstandardmodel=new ApplicationUnitStandard();
									//	$appunitstandardmodel->unit_id=$appunitmodel->id;
									//	$appunitstandardmodel->standard_id=$valuesstd;
									//	$appunitstandardmodel->save(); 
                    				//}
									if(is_array($unitvalue['processes']) && count($unitvalue['processes'])>0)
									{
										foreach ($unitvalue['processes'] as $val2)
										{ 
											$appunitprocessesmodel=new ApplicationUnitProcess();
											if(!$apptype_standardaddition || (isset($unitvalue['existsprocesses'] ) && is_array($unitvalue['existsprocesses']) && count($unitvalue['existsprocesses'] )>0 && in_array($val2,$unitvalue['existsprocesses']))){
												
												$appunitprocessesmodel->process_type = 0;
											}else{
												$appunitprocessesmodel->process_type = 1;
											}
											if($apptype_standardaddition && !$appunitprocessesmodel->process_type){
												$appunitprocessesmodel->process_type = 1;
											}
											

											$appunitprocessesmodel->unit_id=$appunitmodel->id;
											$appunitprocessesmodel->process_id=isset($val2)?$val2:"";
											$appunitprocessesmodel->standard_id=$valuesstd;

											$Process = Process::find()->where(['id'=>$val2])->one();
											if($Process !== null){
												$appunitprocessesmodel->process_name = $Process->name;
											}
											
											$appunitprocessesmodel->save(); 
										}
									}
								}
							}else{
								if(is_array($unitvalue['standards']) && count($unitvalue['standards'])>0)
								{
									foreach ($unitvalue['standards'] as $val3)
									{ 
										//if($unit_addition_type){
											$appunitstandardmodel=new ApplicationUnitStandard();
	                    					if(!$apptype_standardaddition || (isset($unitvalue['unit_exists'] ) && is_array($unitvalue['unit_exists']) && count($unitvalue['unit_exists'] )>0 && in_array($val3,$unitvalue['unit_exists']))){
	                    						
	                    						$appunitstandardmodel->addition_type = 0;
	                    					}else{
	                    						$appunitstandardmodel->addition_type = 1;
	                    					}
	                    					if($apptype_standardaddition && !$appunitstandardmodel->addition_type){
												continue;
											}

											
											$appunitstandardmodel->unit_id=$appunitmodel->id;
											$appunitstandardmodel->standard_id=isset($val3)?$val3:"";
											$appunitstandardmodel->save(); 
	                    				//}else{
	                    				//	$appunitstandardmodel=new ApplicationUnitStandard();
										//	$appunitstandardmodel->unit_id=$appunitmodel->id;
										//	$appunitstandardmodel->standard_id=$valuesstd;
										//	$appunitstandardmodel->save(); 
										//}
										

										if(is_array($unitvalue['processes']) && count($unitvalue['processes'])>0)
										{
											foreach ($unitvalue['processes'] as $val2)
											{ 
												$appunitprocessesmodel=new ApplicationUnitProcess();
												if(!$apptype_standardaddition || (isset($unitvalue['existsprocesses'] ) && is_array($unitvalue['existsprocesses']) && count($unitvalue['existsprocesses'] )>0 && in_array($val2,$unitvalue['existsprocesses']))){
													
													$appunitprocessesmodel->process_type = 0;
												}else{
													$appunitprocessesmodel->process_type = 1;
												}
												if($apptype_standardaddition && !$appunitprocessesmodel->process_type){
													$appunitprocessesmodel->process_type = 1;
												}

												$appunitprocessesmodel->unit_id=$appunitmodel->id;
												$appunitprocessesmodel->process_id=isset($val2)?$val2:"";
												$appunitprocessesmodel->standard_id=$val3;

												$Process = Process::find()->where(['id'=>$val2])->one();
												if($Process !== null){
													$appunitprocessesmodel->process_name = $Process->name;
												}
												$appunitprocessesmodel->save(); 
											}
										}
										
									}
								}
							}

							if(is_array($unitvalue['certified_standard']) && count($unitvalue['certified_standard'])>0)
							{
								foreach ($unitvalue['certified_standard'] as $val3)
								{ 
									$appunitcertifiedstdmodel=new ApplicationUnitCertifiedStandard();
									$appunitcertifiedstdmodel->unit_id=$appunitmodel->id;
									$appunitcertifiedstdmodel->standard_id=isset($val3['standard'])?$val3['standard']:"";
									$appunitcertifiedstdmodel->license_number=isset($val3['license_number'])?$val3['license_number']:"";
									$appunitcertifiedstdmodel->expiry_date=isset($val3['expiry_date'])?date('Y-m-d',strtotime($val3['expiry_date'])):"";
									$appunitcertifiedstdmodel->save(); 

									$standard_id = $val3['standard'];
									
									if(isset($_FILES['uploads']['name'][$unitkey][$standard_id]) && is_array($_FILES['uploads']['name'][$unitkey][$standard_id]))
									{
										foreach($_FILES['uploads']['name'][$unitkey][$standard_id] as $indexkey => $filename){
											$target_file = $target_dir . basename($filename);

											 
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
											*/
											//return [$val3,$_FILES];
											//return  $_FILES;
											//if(isset($val3['files'][$indexkey]) && $val3['files'][$indexkey]['deleted']==0){
												//move_uploaded_file($_FILES['uploads']["tmp_name"][$unitkey][$standard_id][$indexkey], $target_dir .$actual_name.".".$extension)
												$tmp_name = $_FILES['uploads']["tmp_name"][$unitkey][$standard_id][$indexkey];
									   			$name = $filename;
												$name=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);	
												if ($name) {
													$appunitcertifiedstdfilemodel=new ApplicationUnitCertifiedStandardFile();
													$appunitcertifiedstdfilemodel->unit_certified_standard_id=$appunitcertifiedstdmodel->id;
													$appunitcertifiedstdfilemodel->file=isset($name)?$name:"";
													$appunitcertifiedstdfilemodel->type=$indexkey;//$val3['files'][$indexkey]['type'];
													$appunitcertifiedstdfilemodel->save(); 
												}
											//}
										}
									}
									 /*
									foreach ($val3['files'] as $val4)
									{ 
										if(isset($val4['name']) && isset($val4['added']) && isset($val4['deleted']) && $val4['added']==0 && $val4['deleted']==0){
											if($val4['name'] !=''){
												$cccfilename=Yii::$app->globalfuns->copyFiles($val4['name'],$target_dir);

												$appunitcertifiedstdfilemodel=new ApplicationUnitCertifiedStandardFile();
												$appunitcertifiedstdfilemodel->unit_certified_standard_id=$appunitcertifiedstdmodel->id;
												$appunitcertifiedstdfilemodel->file=$cccfilename;
												$appunitcertifiedstdfilemodel->type=isset($val4['type'])?$val4['type']:'';
												$appunitcertifiedstdfilemodel->save(); 
											}
											

										}else if($val4['deleted']==1){
											
											//$unitStandardFiles
											
										}
									}
									*/
								}

								
							}
						}
                    }
				}
				if($data['actiontype']=='draft'){
				}else{
					$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'app_submitted_mail_franchise'])->one();
					if($mailContent !== null )
					{
						$mailmsg = str_replace('{COMPANY-NAME}', $data['company_name'], $mailContent['message'] );
						$mailmsg = str_replace('{COMPANY-EMAIL}', $data['company_email'], $mailmsg );
						$mailmsg = str_replace('{COMPANY-TELEPHONE}', $data['company_telephone'], $mailmsg );
						$mailmsg = str_replace('{CONTACT-NAME}', $data['first_name']." ".$data['last_name'], $mailmsg );
						
						$franchise = UserCompanyInfo::find()->select('company_email')->where(['user_id' => $model->franchise_id])->one();
						if($franchise !== null )
						{
							$MailLookupModel = new MailLookup();
							$MailLookupModel->to=$franchise['company_email'];							
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
				
                $responsedata=array('status'=>1,'message'=>'Application has been submitted successfully','app_id'=>$model->id);
            }
            else
            {
                $responsedata=array('status'=>0,'message'=>$model->errors);
            }
            // $responsedata=array('status'=>0,'message'=>$model->errors);
            return $this->asJson($responsedata);
        }
    }

	
    public function actionUpdate()
    {   	
		
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		
		$datapost = Yii::$app->request->post();
		$data = json_decode($datapost['formvalues'],true);
		
		
		$target_dir = Yii::$app->params['certification_standard_files']; 

		$model = Application::find()->where(['id' => $data['id']]);
		
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];

		
		//echo $model->created_by .'!='. $userid; die;
		if($resource_access != 1){
			if($user_type== Yii::$app->params['user_type']['user'] && ! in_array('update_application',$rules) ){
				return $responsedata;
			}else if($user_type== Yii::$app->params['user_type']['franchise'] && $is_headquarters != 1){
				if($resource_access ==5){
					$model = $model->andWhere(['franchise_id'=>$franchiseid]);
				}else{
					$model = $model->andWhere(['franchise_id'=>$userid]);
				}
				
			}else if($user_type== Yii::$app->params['user_type']['customer']){
				$model = $model->andWhere(['customer_id'=>$userid]);
				//$model = $model->where('created_by='.$userid);
			}
			 
			if($user_type== 1 && in_array('update_application',$rules) && $is_headquarters != 1){
				$model = $model->andWhere(['franchise_id'=>$franchiseid]);
			}
			
		}
		$model = $model->one();
		$connection = Yii::$app->getDb();
		if ($model !== null)
		{
			//'0'=>'Open','1'=>'Submitted',
			if($model->status != $model->arrEnumStatus['open'] && $model->status != $model->arrEnumStatus['submitted'] &&  $model->status != $model->arrEnumStatus['pending_with_customer'] ){
				return $responsedata;
			}
			$arr_unit_ids = [];
			if(is_array($data['units']) && count($data['units'])>0)
			{
				foreach ($data['units'] as $unitkey => $value)
				{
					if(isset($value['deleted']) && $value['deleted']==1)
					{
						continue;
					}
					$arr_unit_ids[] = $value['unit_id'];
				}
			}
		
			$appStandard=$model->applicationstandard;
			if(count($appStandard)>0)
			{
				foreach($appStandard as $std)
				{
					$std->delete();	
				}
			}

			$checklistcmtFiles = [];
			$applicationchecklistcmt=$model->applicationchecklistcmt;
			if(count($applicationchecklistcmt)>0)
			{
				foreach($applicationchecklistcmt as $chklist)
				{
					$checklistcmtFiles[$chklist->question_id] = $chklist->document;
					$chklist->delete();	
				}
			}

			$certificationbody=$model->certificationbody;
			if(count($certificationbody)>0)
			{
				foreach($certificationbody as $cbody)
				{
					$cbFilesList[$cbody->standard_id] = $cbody->certification_file;
					$cbody->delete();	
				}
			}

			/*
			$appProductDetail=$model->applicationproductdetails;
			if(count($appProductDetail)>0)
			{
				foreach($appProductDetail as $prd)
				{
					$prd->delete();
				}
			}
			*/
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
			$unitStandardFiles = [];
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
									$unitStandardFiles[$unit->id][$unitS->standard_id][$stdfile->type] = $stdfile->file;
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

					if(!in_array($unit->id,$arr_unit_ids) ){

						if(isset($unitStandardFiles[$unit->id]) && count($unitStandardFiles[$unit->id]) > 0 ){
							foreach($unitStandardFiles[$unit->id] as  $standardcertdetails){
								if(isset($standardcertdetails) && count($standardcertdetails)>0){
									foreach($standardcertdetails as $certindextypefilename){
										if($certindextypefilename != ''){
											Yii::$app->globalfuns->removeFiles($certindextypefilename,$target_dir);
										}
									}
								}
							}
						}
						unset($unitStandardFiles[$unit->id]);
						$unit->delete();
						//echo $unit->id.'dsfdf---';				
					}
					
				}
			}
			


			if(isset($_FILES['company_file']['name']))
			{
				$target_dir_company = Yii::$app->params['company_files'];
				$tmp_name = $_FILES["company_file"]["tmp_name"];
	   			$name = $_FILES["company_file"]["name"];
				
				Yii::$app->globalfuns->removeFiles($model->company_file,$target_dir_company);				
				$model->company_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir_company);

				/*
				$filename = $_FILES['company_file']['name'];
				$target_file = $target_dir . basename($filename);
				$target_file = $target_dir . basename($filename);
				$actual_name = pathinfo($filename,PATHINFO_FILENAME);
				$original_name = $actual_name;
				$extension = pathinfo($filename, PATHINFO_EXTENSION);
				$i = 1;
				$name = $actual_name.".".$extension;
				while(file_exists($target_dir_company.$actual_name.".".$extension))
				{           
					$actual_name = (string)$original_name.$i;
					$name = $actual_name.".".$extension;
					$i++;
				}
				if (move_uploaded_file($_FILES['company_file']["tmp_name"], $target_dir_company .$actual_name.".".$extension)) {
					$model->company_file=isset($name)?$name:"";
				}
				*/
			}else{
				$model->company_file= isset($data['company_file'])?$data['company_file']:"";
			}

			if($data['actiontype']=='draft'){
				$model->status = $model->arrEnumStatus['open'];
				 
			}else{
				$model->status = $model->arrEnumStatus['submitted'];
				/* 
				$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'app_submitted_mail_franchise'])->one();

				if($mailContent !== null )
				{
					$mailmsg = str_replace('{COMPANY-NAME}', $data['company_name'], $mailContent['message'] );
					$mailmsg = str_replace('{COMPANY-EMAIL}', $data['company_email'], $mailmsg );
					$mailmsg = str_replace('{COMPANY-TELEPHONE}', $data['company_telephone'], $mailmsg );
					$mailmsg = str_replace('{CONTACT-NAME}', $data['first_name']." ".$data['last_name'], $mailmsg );

					$franchise = UserCompanyInfo::find()->select('company_email')->where(['user_id' => $model->franchise_id])->one();
					if($franchise !== null )
					{
						$MailLookupModel = new MailLookup();
						$MailLookupModel->to=$franchise['company_email'];						
						$MailLookupModel->bcc='';
						$MailLookupModel->subject=$mailContent['subject'];
						$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
						$MailLookupModel->attachment='';
						$MailLookupModel->mail_notification_id='';
						$MailLookupModel->mail_notification_code='';
						$Mailres=$MailLookupModel->sendMail();
					}
				}
				*/

			}

			/*
			$model->company_name=isset($data['company_name'])?$data['company_name']:"";
			$model->address=isset($data['address'])?$data['address']:"";
			$model->zipcode=isset($data['zipcode'])?$data['zipcode']:"";
			$model->city=isset($data['city'])?$data['city']:"";
			$model->state_id=isset($data['state_id'])?$data['state_id']:"";
			$model->country_id=isset($data['country_id'])?$data['country_id']:"";
			$model->salutation=isset($data['salutation'])?$data['salutation']:"";
			$model->title=isset($data['title'])?$data['title']:"";
			$model->first_name=isset($data['first_name'])?$data['first_name']:"";
			$model->last_name=isset($data['last_name'])?$data['last_name']:"";
			$model->job_title=isset($data['job_title'])?$data['job_title']:"";
			$model->telephone=isset($data['telephone'])?$data['telephone']:"";
			$model->email_address=isset($data['email_address'])?$data['email_address']:"";
			*/
			$model->certification_status=isset($data['certification_status'])?$data['certification_status']:"";
			//$model->preferred_partner_id=isset($data['preferred_partner_id'])?$data['preferred_partner_id']:"";
			$standard_addition_id = '';
			if(isset($data['standard_addition_id']))
			{
				//$model->audit_type=$model->arrEnumAuditType['standard_addition'];
				$standard_addition_id = $data['standard_addition_id'];
			}

			$appreviewmodel = ApplicationReview::find()->where(['app_id' => $data['id']])->orderBy(['id' => SORT_DESC])->one();
			$reviewermodel = ApplicationReviewer::find()->where(['app_id' => $data['id'],'reviewer_status'=>1])->one();
			if ($reviewermodel === null && $appreviewmodel===null)
			{
				//$model->status=0;
				if($data['actiontype']=='draft'){
					$model->status = $model->arrEnumStatus['open'];
					
				}else{
					$model->status = $model->arrEnumStatus['submitted'];
					
				}
			}else{
				/*
				if($appreviewmodel->user_id == $reviewermodel->user_id && $appreviewmodel->answer==null && $appreviewmodel->comment==null && $appreviewmodel->review_result==0){

				}else{
					$reviewmodel=new ApplicationReview();
					$reviewmodel->app_id=isset($data['id'])?$data['id']:"";
					$reviewmodel->user_id=$reviewermodel->user_id;
					$reviewmodel->status=$reviewmodel->arrEnumReviewStatus['review_in_process'];
					$reviewmodel->save();
				}
				$model->status=$model->arrEnumStatus['review_in_process'];
				$model->overall_status = $model->arrEnumOverallStatus['review_in_process'];
				*/
				$model->status=$model->arrEnumStatus['submitted'];
				 
				
			}

			$model->tax_no=$data['tax_no'];
			$model->updated_by=$userData['userid'];

			if($model->validate() && $model->save())
			{
				//if($model->audit_type == $model->arrEnumAuditType['normal']){
					$ApplicationChangeAddress = ApplicationChangeAddress::find()->where(['id'=>$model->address_id])->orderBy(['id' => SORT_DESC])->one();
					
					//$ApplicationChangeAddress->current_app_id = $model->id;
					$ApplicationChangeAddress->company_name=isset($data['company_name'])?$data['company_name']:"";
					$ApplicationChangeAddress->address=isset($data['address'])?$data['address']:"";
					$ApplicationChangeAddress->zipcode=isset($data['zipcode'])?$data['zipcode']:"";
					$ApplicationChangeAddress->city=isset($data['city'])?$data['city']:"";
					$ApplicationChangeAddress->state_id=isset($data['state_id'])?$data['state_id']:"";
					$ApplicationChangeAddress->country_id=isset($data['country_id'])?$data['country_id']:"";
					$ApplicationChangeAddress->salutation=isset($data['salutation'])?$data['salutation']:"";
					$ApplicationChangeAddress->title=isset($data['title'])?$data['title']:"";
					$ApplicationChangeAddress->first_name=isset($data['first_name'])?$data['first_name']:"";
					$ApplicationChangeAddress->last_name=isset($data['last_name'])?$data['last_name']:"";
					$ApplicationChangeAddress->job_title=isset($data['job_title'])?$data['job_title']:"";
					$ApplicationChangeAddress->telephone=isset($data['telephone'])?$data['telephone']:"";
					$ApplicationChangeAddress->email_address=isset($data['email_address'])?$data['email_address']:"";
					$ApplicationChangeAddress->save();
				//}
				
				

				$apptype_standardaddition = 0;
				if($model->audit_type==$model->arrEnumAuditType['standard_addition']){
					$apptype_standardaddition = 1;
				}
				if(is_array($data['standards']) && count($data['standards'])>0)
				{
					foreach ($data['standards'] as $value)
					{ 

						if($standard_addition_id && $standard_addition_id>0){
                    		$chkappstdmodel=ApplicationStandard::find()->where(['app_id'=>$data['app_id'], 'standard_id'=>$value,'standard_status'=>0])->one();
                    		if($chkappstdmodel!==null){
                    			$appstdmodel=new ApplicationStandard();
		                        $appstdmodel->app_id=$model->id;
		                        $appstdmodel->standard_id=isset($value)?$value:"";
		                        $appstdmodel->version = $this->getStandardVersion($value);
		                        //$appstdmodel->standard_addition_type=isset($value)?$value:"";
		                        $appstdmodel->save(); 
                    		}else{
                    			$appstdmodel=new ApplicationStandard();
		                        $appstdmodel->app_id=$model->id;
		                        $appstdmodel->standard_id=isset($value)?$value:"";
		                        $appstdmodel->standard_addition_type=1;
		                        $appstdmodel->version = $this->getStandardVersion($value);
		                        $appstdmodel->save(); 
                    		}
	                        
                    	}else{
                    		$appstdmodel=new ApplicationStandard();
	                        $appstdmodel->app_id=$model->id;
	                        $appstdmodel->standard_id=isset($value)?$value:"";
	                        $appstdmodel->version = $this->getStandardVersion($value);
	                        //$appstdmodel->standard_addition_type=isset($value)?$value:"";
	                        $appstdmodel->save(); 
                    	}
                    	/*
						$appstdmodel=new ApplicationStandard();
						$appstdmodel->app_id=$model->id;
						$appstdmodel->standard_id=isset($value)?$value:"";
						$appstdmodel->save(); 
						*/
					}
				}
				
				if(is_array($data['app_checklist']) && count($data['app_checklist'])>0)
				{
					$target_dir_checklist = Yii::$app->params['application_checklist_file'];
					foreach ($data['app_checklist'] as $value)
					{ 
						$question_id = $value['question_id'];
						$document = '';
						//echo $question_id;
						//print_r($_FILES['checklist_file']); die;
						$checklistdocumentname = isset($checklistcmtFiles[$value['question_id']])?$checklistcmtFiles[$value['question_id']]:'';

						if(isset($_FILES['checklist_file']['name'][$question_id]))
						{
							$tmp_name = $_FILES["checklist_file"]["tmp_name"][$question_id];
				   			$name = $_FILES["checklist_file"]["name"][$question_id];
							$document=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir_checklist);	

							
							if($checklistdocumentname!=''){
								Yii::$app->globalfuns->removeFiles($checklistdocumentname,$target_dir_checklist);
							}
						}else{
							$document = $value['document'];
							if($value['document'] =='' && $checklistdocumentname !=''){
								Yii::$app->globalfuns->removeFiles($checklistdocumentname,$target_dir_checklist);
							}
						}
						if(isset($checklistcmtFiles[$question_id])){
							unset($checklistcmtFiles[$question_id]);
						}
						$checklistmodel=new ApplicationChecklistComment();
						$checklistmodel->app_id=$model->id;
						$checklistmodel->question_id=$value['question_id'];
						$checklistmodel->question=$value['question'];
						$checklistmodel->answer=$value['answer'];
						$checklistmodel->comment=$value['comment'];
						$checklistmodel->document=$document;
						
						$checklistmodel->save(); 
						/*
						$appstdmodel=new ApplicationChecklistComment();
						$appstdmodel->app_id=$model->id;
						$appstdmodel->standard_id=isset($value)?$value:"";
						$appstdmodel->save(); 
						*/
					}
				}
				if(isset($checklistcmtFiles) && count($checklistcmtFiles)>0){
					$target_dir_checklist = Yii::$app->params['application_checklist_file'];
					foreach($checklistcmtFiles as $qid => $checklistdocname){
						if($checklistdocname !=''){
							Yii::$app->globalfuns->removeFiles($checklistdocname,$target_dir_checklist);
						}
					}
				}
				if(isset($data['app_certifiedothercblist']) && is_array($data['app_certifiedothercblist']) && count($data['app_certifiedothercblist'])>0)
				{
					$target_dir_checklist = Yii::$app->params['certifiedbyothercb_file'];
					foreach ($data['app_certifiedothercblist'] as $value)
					{ 
						
						$standard_id = $value['standard_id'];

						$cbdocumentname = isset($cbFilesList[$standard_id])?$cbFilesList[$standard_id]:'';

						if(isset($_FILES['certifiedbyothercb_file']['name'][$standard_id]))
						{
							$tmp_name = $_FILES["certifiedbyothercb_file"]["tmp_name"][$standard_id];
				   			$name = $_FILES["certifiedbyothercb_file"]["name"][$standard_id];
							$document=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir_checklist);
							if($cbdocumentname!=''){
								Yii::$app->globalfuns->removeFiles($cbdocumentname,$target_dir_checklist);
							}

						}else{
							$document = $value['document'];
							if($document =='' && $cbdocumentname !=''){
								Yii::$app->globalfuns->removeFiles($cbdocumentname,$target_dir_checklist);
							}
						}
						if(isset($cbFilesList[$standard_id])){
							unset($cbFilesList[$standard_id]);
						}
						
						if($document !='' && $value['standard_id']!='' && $value['certification_body']!='' && $value['validity_date']!='' )
						{
							$othercbmodel=new ApplicationCertifiedByOtherCB();
							$othercbmodel->app_id=$model->id;
							$othercbmodel->standard_id=$value['standard_id'];
							$othercbmodel->certification_body=$value['certification_body'];
							$othercbmodel->validity_date=date('Y-m-d',strtotime($value['validity_date']));
							$othercbmodel->certification_file=$document;
							$othercbmodel->save(); 
						}
						
					}
				}
				if(isset($cbFilesList) && count($cbFilesList)>0){
					$target_dir_checklist = Yii::$app->params['certifiedbyothercb_file'];
					foreach($cbFilesList as $qid => $cbdocname){
						if($cbdocname !=''){
							Yii::$app->globalfuns->removeFiles($cbdocname,$target_dir_checklist);
						}
					}
				}
				if(is_array($data['products']) && count($data['products'])>0)
				{
					foreach ($data['products'] as $value)
					{
						
						/*$app_productmodel=new ApplicationProduct();
                        $app_productmodel->app_id=$model->id;
						$app_productmodel->product_id=isset($value['product_id'])?$value['product_id']:"";
						$app_productmodel->product_type_id =isset($value['product_type'])?$value['product_type']:"";
						$app_productmodel->wastage=isset($value['wastage'])?(string)$value['wastage']:"";
						$app_productmodel->standard_id=isset($value['standard_id'])?$value['standard_id']:"";
						$app_productmodel->label_grade_id=isset($value['label_grade'])?$value['label_grade']:"";
						$app_productmodel->save();
						//print_r($app_productmodel->error());
						//print_r($app_productmodel->errors); die;
						$pdtlistval[$value['pdt_index']] = $app_productmodel->id;
						
						if(isset($value['productMaterialList']) && is_array($value['productMaterialList']) ){
							foreach($value['productMaterialList'] as $prdstd){
								$appproductmaterialmodel=new ApplicationProductMaterial();
								$appproductmaterialmodel->app_product_id=$app_productmodel->id;
								$appproductmaterialmodel->material_id=$prdstd['material_id'];
								$appproductmaterialmodel->material_type_id =$prdstd['material_type_id'];
								$appproductmaterialmodel->percentage =$prdstd['material_percentage'];
								$appproductmaterialmodel->save(); 
							}
						}
						*/
						//echo $model->id; die;
						
						$appproductmodel=new ApplicationProduct();
                        $appproductmodel->app_id=$model->id;
						$appproductmodel->product_id=isset($value['product_id'])?$value['product_id']:"";
						$appproductmodel->product_type_id =isset($value['product_type'])?$value['product_type']:"";
						$appproductmodel->wastage=isset($value['wastage'])?"".$value['wastage']:"";
						$appproductmodel->product_addition_type=($apptype_standardaddition && isset($value['addition_type']) && $value['addition_type']=="1") ?1:0;

						$Product = Product::find()->where(['id'=> $value['product_id']])->one();
						if($Product !== null){
							$appproductmodel->product_name = $Product->name;
						}
						$ProductType = ProductType::find()->where(['id'=> $value['product_type']])->one();
						if($ProductType !== null){
							$appproductmodel->product_type_name = $ProductType->name;
						}
						
						$appproductmodel->save(); 
						//print_r($appproductmodel->errors);
						//print_r($value); die;
						if(isset($value['productStandardList']) && is_array($value['productStandardList']) ){
							foreach($value['productStandardList'] as $prdstd){
								$appproductstandardmodel=new ApplicationProductStandard();
								$appproductstandardmodel->standard_id=$prdstd['standard_id'];
								$appproductstandardmodel->application_product_id=$appproductmodel->id;
								$appproductstandardmodel->label_grade_id =$prdstd['label_grade'];
								$StandardLabelGrade = StandardLabelGrade::find()->where(['id'=> $prdstd['label_grade']])->one();
								if($StandardLabelGrade !== null){
									$appproductstandardmodel->label_grade_name = $StandardLabelGrade->name;
                                }
								$appproductstandardmodel->save(); 
							}
						}
						
						if(isset($value['productMaterialList']) && is_array($value['productMaterialList']) ){
							foreach($value['productMaterialList'] as $prdstd){
								$appproductmaterialmodel=new ApplicationProductMaterial();
								$appproductmaterialmodel->app_product_id=$appproductmodel->id;
								$appproductmaterialmodel->material_id=$prdstd['material_id'];
								$appproductmaterialmodel->material_type_id =$prdstd['material_type_id'];
								$appproductmaterialmodel->percentage =$prdstd['material_percentage'];
								$ProductTypeMaterialComposition = ProductTypeMaterialComposition::find()->where(['id'=> $prdstd['material_id']])->one();
								if($ProductTypeMaterialComposition !== null){
									$appproductmaterialmodel->material_name = $ProductTypeMaterialComposition->name;
									if(isset($ProductTypeMaterialComposition->material_type[$prdstd['material_type_id']])){
										$appproductmaterialmodel->material_type_name = $ProductTypeMaterialComposition->material_type[$prdstd['material_type_id']];
									}
								}
								
								
								$appproductmaterialmodel->save(); 
							}
						}
						 
					}
				}
				
				if(is_array($data['units']) && count($data['units'])>0)
				{
					foreach ($data['units'] as $unitkey => $value)
					{ 

						if(isset($value['deleted']) && $value['deleted'] ==1){
							continue;
						}

						if(isset($value['unit_id']) && $value['unit_id']!=''){
							$appunitmodel = ApplicationUnit::find()->where(['id' => $value['unit_id']])->one();
						}else{
							$appunitmodel=new ApplicationUnit();
						}
						$appunitmodel->app_id=$model->id;
						$appunitmodel->name=isset($value['name'])?$value['name']:"";
						$appunitmodel->code=isset($value['code'])?$value['code']:"";
						$appunitmodel->address=isset($value['address'])?$value['address']:"";
						$appunitmodel->zipcode=isset($value['zipcode'])?$value['zipcode']:"";
						$appunitmodel->city=isset($value['city'])?$value['city']:"";
						$appunitmodel->state_id=isset($value['state_id'])?$value['state_id']:"";
						$appunitmodel->country_id=isset($value['country_id'])?$value['country_id']:"";
						$appunitmodel->no_of_employees=isset($value['no_of_employees'])?$value['no_of_employees']:"";
						$appunitmodel->unit_type=isset($value['unit_type'])?$value['unit_type']:"";
						$unit_addition_type = 0;
						if(isset($value['addition_type']) && $value['addition_type']=="1"){
							$unit_addition_type = 1;
						}
						$appunitmodel->unit_addition_type= $apptype_standardaddition && $unit_addition_type=="1"?1:0;


						if($appunitmodel->unit_type == 1){
							$ApplicationChangeAddress->unit_name=isset($value['name'])?$value['name']:"";
							$ApplicationChangeAddress->unit_address=isset($value['address'])?$value['address']:"";
							$ApplicationChangeAddress->unit_zipcode=isset($value['zipcode'])?$value['zipcode']:"";
							$ApplicationChangeAddress->unit_city=isset($value['city'])?$value['city']:"";
							$ApplicationChangeAddress->unit_state_id=isset($value['state_id'])?$value['state_id']:"";
							$ApplicationChangeAddress->unit_country_id=isset($value['country_id'])?$value['country_id']:"";
							$ApplicationChangeAddress->save();
						}
						
						if($appunitmodel->validate() && $appunitmodel->save())
						{

							if(is_array($value['products']) && count($value['products'])>0)
							{
								foreach ($value['products'] as $val111)
								{ 
									//echo $model->id;
									//print_r($val111);  die;
									/*
									$pdtdetailsmodel = ApplicationProduct::find()->where([
									'product_id' => $val111['id'],'app_id' => $model->id
									,'product_type_id' => $val111['product_type_id'],'wastage' => $val111['wastage']])->one();

									$pdtstdmodel = ApplicationProductStandard::find()->where([
										'standard_id' => $val111['standard_id'],'label_grade_id' => $val111['label_grade']
										,'application_product_id' => $pdtdetailsmodel->id ])->one();
									*/

									$productMaterialList = $val111['productMaterialList'];
									$queryStr = [];
									foreach($productMaterialList as $materiall){
										$queryStr[] = "  ( material_id = '".$materiall['material_id']."' AND material_type_id = '".$materiall['material_type_id']."' AND percentage = '".$materiall['material_percentage']."') ";
									}
									$totalCompCnt = count($queryStr);

									$queryCondition = ' ('.implode(' OR ',$queryStr).') ';
									
									$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
									$command = $connection->createCommand("SELECT  COUNT(pdt.id) as matcnt,pdt_std.id  as pdt_std_id  
									from tbl_application_product as pdt 
									INNER JOIN tbl_application_product_standard as pdt_std on pdt_std.application_product_id = pdt.id 
									INNER JOIN tbl_application_product_material as pdt_mat on pdt_mat.app_product_id = pdt.id 
									WHERE 
									pdt.product_id='".$val111['id']."' AND pdt.product_type_id='".$val111['product_type_id']."'
									 AND pdt.wastage='".$val111['wastage']."' 
									AND pdt_std.standard_id='".$val111['standard_id']."' AND pdt_std.label_grade_id='".$val111['label_grade']."' 
									AND pdt.app_id='".$model->id."' 

									AND ".$queryCondition."
									
									group by pdt.id HAVING matcnt=".$totalCompCnt." ");
									$result = $command->queryOne();
									$pdt_std_id = 0;
									if($result  !== false){
										$pdt_std_id = $result['pdt_std_id'];
									}

									
									$addition_type = isset($val111['addition_type']) && $val111['addition_type']=="1"?$val111['addition_type']:0;
									if($pdt_std_id<=0){
										continue;
									}
									
									//$pdt_id = $pdtlistval[$val1['pdt_index']];
									$appunitproductmodel=new ApplicationUnitProduct();
									$appunitproductmodel->unit_id=$appunitmodel->id;
									$appunitproductmodel->application_product_standard_id=$pdt_std_id;//$pdtstdmodel->id;
									$appunitproductmodel->product_addition_type = $apptype_standardaddition && $addition_type=="1"?1:0;
									$appunitproductmodel->save();
								}
							}

							/*
							if(is_array($value['products']) && count($value['products'])>0)
							{
								foreach ($value['products'] as $val1)
								{ 
									$pdt_id = $pdtlistval[$val1['pdt_index']];
									$appunitproductmodel=new ApplicationUnitProduct();
									$appunitproductmodel->unit_id=$appunitmodel->id;
									$appunitproductmodel->product_id=$pdt_id;
									$appunitproductmodel->save();
									
								}
							}
							*/
							

							if(is_array($value['business_sector_id']) && count($value['business_sector_id'])>0)
							{
								foreach ($value['business_sector_id'] as $val3)
								{ 
									$appunitbsectorsmodel=new ApplicationUnitBusinessSector();
                					if(!$apptype_standardaddition || (isset($value['business_sector_exists'] ) && is_array($value['business_sector_exists']) && count($value['business_sector_exists'] )>0 && in_array($val3,$value['business_sector_exists']))){
                						
                						$appunitbsectorsmodel->addition_type = 0;
                					}else{
                						$appunitbsectorsmodel->addition_type = 1;
                					}
		                    		

				                    $appunitbsectorsmodel->unit_id=$appunitmodel->id;
									$appunitbsectorsmodel->business_sector_id=isset($val3)?$val3:"";
									$BusinessSector = BusinessSector::find()->where(['id'=>$val3])->one();
									if($BusinessSector !== null){
										$appunitbsectorsmodel->business_sector_name= $BusinessSector->name;
									}
									$appunitbsectorsmodel->save(); 
								}
							}

							if($value['unit_type'] ==1){
								foreach ($data['standards'] as $stdvalue)
                    			{ 
									$appunitstandardmodel=new ApplicationUnitStandard();
                					if(!$apptype_standardaddition ||(isset($value['unit_exists'] ) && is_array($value['unit_exists']) && count($value['unit_exists'] )>0 && in_array($stdvalue,$value['unit_exists']))){
                						
                						$appunitstandardmodel->addition_type = 0;
                					}else{
                						$appunitstandardmodel->addition_type = 1;
                					}
		                    		

				                    $appunitstandardmodel->unit_id=$appunitmodel->id;
									$appunitstandardmodel->standard_id=$stdvalue;
									$appunitstandardmodel->save(); 


									if(is_array($value['processes']) && count($value['processes'])>0)
									{
										foreach ($value['processes'] as $val2)
										{ 
											$appunitprocessesmodel=new ApplicationUnitProcess();
											if(!$apptype_standardaddition || (isset($value['existsprocesses'] ) && is_array($value['existsprocesses']) && count($value['existsprocesses'] )>0 && in_array($val2,$value['existsprocesses']))){
												
												$appunitprocessesmodel->process_type = 0;
											}else{
												$appunitprocessesmodel->process_type = 1;
											}
											

											$appunitprocessesmodel->unit_id=$appunitmodel->id;
											$appunitprocessesmodel->process_id=isset($val2)?$val2:"";
											$appunitprocessesmodel->standard_id=$stdvalue;
											$Process = Process::find()->where(['id'=>$val2])->one();
											if($Process !== null){
												$appunitprocessesmodel->process_name = $Process->name;
											}
											$appunitprocessesmodel->save(); 
										}
									}
								}
							}else{
								if(is_array($value['standards']) && count($value['standards'])>0)
								{
									foreach ($value['standards'] as $val3)
									{ 
										$appunitstandardmodel=new ApplicationUnitStandard();
                    					if(!$apptype_standardaddition || (isset($value['unit_exists'] ) && is_array($value['unit_exists']) && count($value['unit_exists'] )>0 && in_array($val3,$value['unit_exists']))){
                    						
                    						$appunitstandardmodel->addition_type = 0;
                    					}else{
                    						$appunitstandardmodel->addition_type = 1;
                    					}
                    					
										$appunitstandardmodel->unit_id=$appunitmodel->id;
										$appunitstandardmodel->standard_id=isset($val3)?$val3:"";
										$appunitstandardmodel->save(); 


										if(is_array($value['processes']) && count($value['processes'])>0)
										{
											foreach ($value['processes'] as $val2)
											{ 
												$appunitprocessesmodel=new ApplicationUnitProcess();
												if(!$apptype_standardaddition || (isset($value['existsprocesses'] ) && is_array($value['existsprocesses']) && count($value['existsprocesses'] )>0 && in_array($val2,$value['existsprocesses']))){
													
													$appunitprocessesmodel->process_type = 0;
												}else{
													$appunitprocessesmodel->process_type = 1;
												}
												

												$appunitprocessesmodel->unit_id=$appunitmodel->id;
												$appunitprocessesmodel->process_id=isset($val2)?$val2:"";
												$appunitprocessesmodel->standard_id=$val3;
												$Process = Process::find()->where(['id'=>$val2])->one();
												if($Process !== null){
													$appunitprocessesmodel->process_name = $Process->name;
												}
												$appunitprocessesmodel->save(); 
											}
										}
									}
								}
							}
							//print_r($value); die;
							if(is_array($value['certified_standard']) && count($value['certified_standard'])>0)
							{
								foreach ($value['certified_standard'] as $val3)
								{ 
									$appunitcertifiedstdmodel=new ApplicationUnitCertifiedStandard();
									$appunitcertifiedstdmodel->unit_id=$appunitmodel->id;
									$appunitcertifiedstdmodel->standard_id=isset($val3['standard'])?$val3['standard']:"";
									$appunitcertifiedstdmodel->license_number=isset($val3['license_number'])?$val3['license_number']:"";
									$appunitcertifiedstdmodel->expiry_date=isset($val3['expiry_date']) && $val3['expiry_date']!=''?date('Y-m-d',strtotime($val3['expiry_date'])):"";
									$appunitcertifiedstdmodel->save(); 

									$standard_id = $val3['standard'];
									/*
									foreach ($val3['files'] as $val4)
									{ 
										if($val4['added']==0 && $val4['deleted']==0){
											$appunitcertifiedstdfilemodel=new ApplicationUnitCertifiedStandardFile();
											$appunitcertifiedstdfilemodel->unit_certified_standard_id=$appunitcertifiedstdmodel->id;
											$appunitcertifiedstdfilemodel->file=isset($val4['name'])?$val4['name']:"";
											$appunitcertifiedstdfilemodel->type=$val4['type'];
											$appunitcertifiedstdfilemodel->save(); 
										}else if($val4['added']==1 && $val4['deleted']==0 && isset($val4['upindex']) ){
											
											if($_FILES['uploads']['name'][$unitkey][$standard_id][$val4['type']][$val4['upindex']]){
											 
												$filename = $_FILES['uploads']['name'][$unitkey][$standard_id][$val4['type']][$val4['upindex']];
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
												if (move_uploaded_file($_FILES['uploads']["tmp_name"][$unitkey][$standard_id][$val4['type']][$val4['upindex']], $target_dir .$actual_name.".".$extension)) {
													//echo '1';
													$appunitcertifiedstdfilemodel=new ApplicationUnitCertifiedStandardFile();
													$appunitcertifiedstdfilemodel->unit_certified_standard_id=$appunitcertifiedstdmodel->id;
													$appunitcertifiedstdfilemodel->file=isset($name)?$name:"";
													$appunitcertifiedstdfilemodel->type=$val4['type']; //$indexkey;//$val3['files'][$indexkey]['type'];
													$appunitcertifiedstdfilemodel->save(); 
												}
											}	 
											 
										
										}else if($val4['deleted']==1){


										}
									}
									*/
									
									if(isset($_FILES['uploads']['name'][$unitkey][$standard_id]) && is_array($_FILES['uploads']['name'][$unitkey][$standard_id]))
									{
										foreach($_FILES['uploads']['name'][$unitkey][$standard_id] as $indexkey => $filename){
											 
												//if($val3['files'][$indexkey]['deleted']==0){
											/*
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
											*/
											
											$tmp_name = $_FILES['uploads']["tmp_name"][$unitkey][$standard_id][$indexkey];
											$name = $filename;
											$name=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);	
											if ($name) {

											//if (move_uploaded_file($_FILES['uploads']["tmp_name"][$unitkey][$standard_id][$indexkey], $target_dir .$actual_name.".".$extension)) {
												//echo '1';
												$appunitcertifiedstdfilemodel=new ApplicationUnitCertifiedStandardFile();
												$appunitcertifiedstdfilemodel->unit_certified_standard_id=$appunitcertifiedstdmodel->id;
												$appunitcertifiedstdfilemodel->file=isset($name)?$name:"";
												$appunitcertifiedstdfilemodel->type=$indexkey;//$val3['files'][$indexkey]['type'];
												$appunitcertifiedstdfilemodel->save(); 

												
												if(isset($unitStandardFiles[$value['unit_id']][$standard_id][$indexkey])){
													$certifiedstandardunitfile =  $unitStandardFiles[$value['unit_id']][$standard_id][$indexkey];
													if($certifiedstandardunitfile != ''){
														Yii::$app->globalfuns->removeFiles($certifiedstandardunitfile,$target_dir);
													}
													unset($unitStandardFiles[$value['unit_id']][$standard_id]);
												}

											}
												//}
											 
										}
									}

									foreach ($val3['files'] as $val4)
									{ 
										if($val4['added']==0 && $val4['deleted']==0){
											$appunitcertifiedstdfilemodel=new ApplicationUnitCertifiedStandardFile();
											$appunitcertifiedstdfilemodel->unit_certified_standard_id=$appunitcertifiedstdmodel->id;
											$appunitcertifiedstdfilemodel->file=isset($val4['name'])?$val4['name']:"";
											$appunitcertifiedstdfilemodel->type=$val4['type'];
											$appunitcertifiedstdfilemodel->save(); 

										}else if($val4['deleted']==1){
											
											//$unitStandardFiles
											
										}
									}
									if(isset($unitStandardFiles[$value['unit_id']][$standard_id])){
										unset($unitStandardFiles[$value['unit_id']][$standard_id]);
									}
								}

								
							}

							if(isset($unitStandardFiles[$value['unit_id']]) && count($unitStandardFiles[$value['unit_id']]) > 0 ){
								foreach($unitStandardFiles[$value['unit_id']] as  $standardcertdetails){
									if(isset($standardcertdetails) && count($standardcertdetails)>0){
										foreach($standardcertdetails as $certindextypefilename){
											
											if($certindextypefilename != ''){
												Yii::$app->globalfuns->removeFiles($certindextypefilename,$target_dir);
											}
											//print_r($certindextype);
											/*if(isset($certindextype) && count($certindextype)>0){
												foreacj
											}*/
										}
									}
								}
							}
								


						}
					}
				}
				//echo 'sdf'; die;
				if($data['actiontype']=='draft'){
				}else{
					 
					$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'app_submitted_mail_franchise'])->one();
	
					if($mailContent !== null )
					{
						$mailmsg = str_replace('{COMPANY-NAME}', $data['company_name'], $mailContent['message'] );
						$mailmsg = str_replace('{COMPANY-EMAIL}', $data['company_email'], $mailmsg );
						$mailmsg = str_replace('{COMPANY-TELEPHONE}', $data['company_telephone'], $mailmsg );
						$mailmsg = str_replace('{CONTACT-NAME}', $data['first_name']." ".$data['last_name'], $mailmsg );
	
						$franchise = UserCompanyInfo::find()->select('company_email')->where(['user_id' => $model->franchise_id])->one();
						if($franchise !== null )
						{
							$MailLookupModel = new MailLookup();
							$MailLookupModel->to=$franchise['company_email'];							
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
				$responsedata=array('status'=>1,'message'=>'Application has been updated successfully');
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}
		}
		else
		{
			$responsedata=array('status'=>0,'message'=>$model->errors);
		}
		//$responsedata=array('status'=>0,'message'=>[]);
		return $this->asJson($responsedata);
	}
	
	public function actionView()
	{

		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		
		$postData = Yii::$app->request->post();
		$getData = Yii::$app->request->get();
		$appmodel = new Application();
		
		if($postData || $getData)
		{
			if($postData)
			{
				$data = $postData;	
			}else{
				$data = $getData;
			}

			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$franchiseid=$userData['franchiseid'];
			//$model = Application::find();
			

			$model = Application::find()->where(['id' => $data['id']]);
			/*if($user_type==2)
			{
				$model= $model->andWhere('created_by='.$userid);
			}
			*/
			$exclude = 0;
			$excludeunits= [];
			if(isset($data['exclude']) && $data['exclude']){
				$exclude=1;

				if(isset($data['units']) && $data['units']){
					$excludeunits= explode(',',$data['units']);
				}
			}
			

			if($resource_access != 1){
				/*
				&& ! in_array('application_review',$rules) && ! in_array('application_approval',$rules) 
				&& ! in_array('create_application',$rules) && ! in_array('update_application',$rules)
				*/
				
				if($user_type== 1 && ! in_array('brand_management',$rules)){
					return $responsedata;
				}else if($user_type==3){
					if($resource_access == 5){
						$model = $model->andWhere('franchise_id="'.$franchiseid.'" or created_by="'.$franchiseid.'"');
					}else{
						$model = $model->andWhere('franchise_id="'.$userid.'" or created_by="'.$userid.'"');
					}
					
				}else if($user_type==2){
					$model = $model->andWhere('customer_id="'.$userid.'"');
				}
			}
			/*
			else if($user_type==3 && $role!=0 && ! in_array('view_application',$rules) ){
					return $responsedata;
				}
			*/
			$connection = Yii::$app->getDb();
			$model= $model->one();
			
			//$standardview_data = 0;


			$appreview = new ApplicationReview;
			if ($model !== null)
			{
				/*
				if($model->audit_type == $model->arrEnumAuditType['normal'] && ( (!isset($data['showtype']) || isset($data['showtype']) && $data['showtype']=='all' ))){
					echo $showonlyNormal = 1; die;
				}
				*/
				$showonlyNormal = 0;
				if($model->audit_type == $model->arrEnumAuditType['normal'] && ( (!isset($data['showtype']) || isset($data['showtype']) && $data['showtype']!='all' ))){
					$showonlyNormal = 1;
				}

				//to show all the existing data as old including already added additions
				$standardaddition_add = 0; 
				if(isset($data['actiontype']) && $data['actiontype']== 'add'){
					$standardaddition_add = 1;
				}
				/*if($model->audit_type == $model->arrEnumAuditType['standard_addition'] && isset($data['actiontype']) && $data['actiontype']== 'view'){
					$standardview_data = 1;
				}
				*/
				//echo $standardview_data;

				//For checking Renewal Addition Starts
				$audit_type_data = isset($data['audit_type'])?$data['audit_type']:'';
				$renewal_id = isset($data['renewal_id'])?$data['renewal_id']:'';
				$renewal_add = 0;
				$renewal_standard_ids = [];
				if($standardaddition_add && $renewal_id && $renewal_id>0){
					$renewal_add = 1;

					$ApplicationRenewal = ApplicationRenewal::find()->where(['id' =>$renewal_id])->one();
					if($ApplicationRenewal !== null){
						$renewalstandard = $ApplicationRenewal->renewalstandard;
						if(count($renewalstandard)>0){
							foreach($renewalstandard as $rstandard){
								$renewal_standard_ids[] = $rstandard->standard_id;
							}
						}
					}

				}
				
				//For checking Renewal Addition Ends


				$resultarr=array();

				$showedit_view = 0;
				
				if($model->audit_type == $model->arrEnumAuditType['unit_addition']){
					if($model->status ==  $model->arrEnumStatus['pending_with_customer'] && $user_type==2){
						//$showedit_view = 1;
					}
				}else if($model->audit_type == $model->arrEnumAuditType['standard_addition'] || $model->audit_type == $model->arrEnumAuditType['normal'] || $model->audit_type == $model->arrEnumAuditType['renewal']){
					if($model->status ==  $model->arrEnumStatus['open'] || $model->status ==  $model->arrEnumStatus['pending_with_customer'] || $model->status ==  $model->arrEnumStatus['submitted'] ){
						$showedit_view = 1;
					}
				}else{
					if($model->status ==  $model->arrEnumStatus['pending_with_customer']){
						$showedit_view = 1;
					}
				}
				
				$resultarr["showedit_view"]=$showedit_view;
				$resultarr["canChangeMaterialComp"]=Yii::$app->globalfuns->canChangeMaterialComp($model->id);
				
				$resultarr["id"]=$model->id;
				$resultarr["code"]=$model->code;
				
				$resultarr['sel_brand_ch']= $model->is_brand;
				
				
				$brandname=array();
				$brandgroup=array();
				$brandids=array();

					$appbrandmodel = $model->applicationbrands;
					if(count($appbrandmodel)>0){
						foreach($appbrandmodel as $brmod){
							if(in_array('brand_report',$rules) && $brmod->brands->brandgroup->user_id==$userid){
								$brandids[]=$brmod->brands->id;
								$brandname[]=$brmod->brands->name;
								$brandgroup[]=$brmod->brands->brandgroup->name;
							}else if($user_type==2){
								$brandids[]=$brmod->brands->id;
								$brandname[]=$brmod->brands->name;
								$brandgroup[]=$brmod->brands->brandgroup->name;
							}
						}
					}
					$resultarr['brandids']=$brandids;
					$resultarr['brand_name']=implode(', ',$brandname);
					$resultarr['brand_group']=implode(', ',array_unique($brandgroup));

				
				 if($model->is_brand==1 && $model->is_brand!==null && ($user_type==1 && in_array('view_brand',$rules))){
					
					$brnmod = ApplicationBrands::find()->where(['app_id'=>$model->id,'brand_id'=>$data['brand_id'],'id'=>$data['app_approver_brand_id']])->one();
	
					if($brnmod!='' && $brnmod!==null){
						$resultarr['brandId']=$brnmod->brand_id;
						$resultarr['brand_name']=$brnmod->brands->name;
						$resultarr['brand_version']=$brnmod->brands->version;
						$resultarr['brand_group']=$brnmod->brands->brandgroup->name;
						$resultarr['brand_status']=$model->arrBrandStatus[$brnmod->status];
					}
				}

				$resultarr["company_file"]=$model->company_file;
				$resultarr["brand_file"]=$model->brand_file;
				$resultarr["tax_no"]=$model->tax_no;
				$resultarr['created_at']=date($date_format,$model->created_at);

				if($standardaddition_add && $model->currentaddress){
					$resultarr["company_name"]=$model->currentaddress->company_name;
					$resultarr["address"]=$model->currentaddress->address;
					$resultarr["zipcode"]=$model->currentaddress->zipcode;
					$resultarr["city"]=$model->currentaddress->city;
					$resultarr["salutation"]=($model->currentaddress->salutation!="")?$model->currentaddress->salutation:"";
					$resultarr["salutation_name"]=($model->currentaddress->salutation!="")?$model->arrSalutation[$model->currentaddress->salutation]:"";
					
					$resultarr["title"]=($model->currentaddress->title!="")?$model->currentaddress->title:"";
					$resultarr["first_name"]=($model->currentaddress->first_name!="")?$model->currentaddress->first_name:"";
					$resultarr["last_name"]=($model->currentaddress->last_name!="")?$model->currentaddress->last_name:"";
					$resultarr["job_title"]=($model->currentaddress->job_title!="")?$model->currentaddress->job_title:"";
					$resultarr["telephone"]=($model->currentaddress->telephone!="")?$model->currentaddress->telephone:"";
					$resultarr["email_address"]=($model->currentaddress->email_address !="")?$model->currentaddress->email_address:"";
								
					$resultarr["state_id_name"]=($model->currentaddress->state->name!="")?$model->currentaddress->state->name:"";
					$resultarr["country_id_name"]=($model->currentaddress->country->name!="")?$model->currentaddress->country->name:"";
					$resultarr["state_id"]=($model->currentaddress!="" && $model->currentaddress->state_id!="")?$model->currentaddress->state_id:"";
					$resultarr["country_id"]=($model->currentaddress!="" &&  $model->currentaddress->country_id!="")?$model->currentaddress->country_id:"";
				}else{

					$resultarr["company_name"]=$model->companyname;
					$resultarr["address"]=$model->address;
					$resultarr["zipcode"]=$model->zipcode;
					$resultarr["city"]=$model->city;
					$resultarr["salutation"]=($model->salutation!="")?$model->salutation:"";
					$resultarr["salutation_name"]=($model->salutation!="")?$model->arrSalutation[$model->salutation]:"";
					
					$resultarr["title"]=($model->title!="")?$model->title:"";
					$resultarr["first_name"]=($model->firstname!="")?$model->firstname:"";
					$resultarr["last_name"]=($model->lastname!="")?$model->lastname:"";
					$resultarr["job_title"]=($model->jobtitle!="")?$model->jobtitle:"";
					$resultarr["telephone"]=($model->telephone!="")?$model->telephone:"";
					$resultarr["email_address"]=($model->jobtitle!="")?$model->emailaddress:"";
								
					$resultarr["state_id_name"]=($model->statename!="")?$model->statename:"";
					$resultarr["country_id_name"]=($model->countryname!="")?$model->countryname:"";
					$resultarr["state_id"]=($model->applicationaddress!="" && $model->applicationaddress->state_id!="")?$model->applicationaddress->state_id:"";
					$resultarr["country_id"]=($model->applicationaddress!="" &&  $model->applicationaddress->country_id!="")?$model->applicationaddress->country_id:"";
				}
				$resultarr["created_by"]=($model->username!="")?$model->username->first_name.' '.$model->username->last_name:"";
				$resultarr["certification_status"]=$model->certification_status;

				$resultarr["reject_comment"]=$model->reject_comment;
				$resultarr["rejected_date"]=date($date_format,strtotime($model->rejected_date));

				//$resultarr["preferred_partner_id"]=$model->preferred_partner_id;
				//$resultarr["preferred_partner_id_name"]=($model->preferredpartner?$model->preferredpartner->name:'');
				
				$resultarr["app_status"]=$model->status;
				$resultarr["status"]=$model->arrStatus[$model->status];
				$resultarr["franchise_id"]=$model->franchise_id;

				$resultarr['process_id']='';
				$resultarr['parent_app_id']= $model->parent_app_id;
				$resultarr['audit_type']= $model->audit_type;
				$resultarr['audit_type_label']=$appmodel->arrAuditType[$model->audit_type];
				if($model->audit_type == 3){
					$processaddition = ProcessAddition::find()->where(['app_id'=>$model->parent_app_id,'new_app_id'=>$model->id])->one();
					if($processaddition!==null){
						$resultarr['addition_id']=$processaddition->id;
					}
				}else if($model->audit_type == 4){
					$addition = StandardAddition::find()->where(['app_id'=>$model->parent_app_id,'new_app_id'=>$model->id])->one();
					if($addition!==null){
						$resultarr['addition_id']=$addition->id;
					}	
				}else if($model->audit_type == 5){
					$addition = UnitAddition::find()->where(['app_id'=>$model->parent_app_id,'new_app_id'=>$model->id])->one();
					if($addition!==null){
						$resultarr['addition_id']=$addition->id;
					}
				}

				if($model->franchise){
					$resultarr["franchise"]= $model->franchise->usercompanyinfo->toArray();
					$resultarr["franchise"]['company_country_name']= $model->franchise->usercompanyinfo->companycountry->name;
					$resultarr["franchise"]['company_state_name']= $model->franchise->usercompanyinfo->companystate?$model->franchise->usercompanyinfo->companystate->name:'';
				}

				$appstdarr=[];
				$arrstandardids=[];
				//$appStandard=$model->applicationstandard;

				if($showonlyNormal){
					$appStandard=$model->applicationstandardnormal;
				}else{
					$appStandard = $model->applicationstandard;
				}
				

				if(count($appStandard)>0)
				{
					foreach($appStandard as $std)
					{
						if($renewal_add && count($renewal_standard_ids)>0){
							if(!in_array($std->standard_id,$renewal_standard_ids)){
								continue;
							}
						}
						$appstdarr[]=($std->standard?$std->standard->name:'');	
						$arrstandardids[]=$std->standard_id;
					}
				}
				$resultarr["standards"]=$appstdarr;
				$resultarr["standard_ids"]=$arrstandardids;
				
				$appprdarr=[];
				$appprdarr_details=[];
				$appProduct=$model->applicationproduct;

				if($showonlyNormal){
					$appProduct=$model->applicationproductnormal;
				}else{
					$appProduct=$model->applicationproduct;
				}
				$resultarr["productDetails"] = [];
				if(count($appProduct)>0)
				{
					$pdt_index = 0;
					foreach($appProduct as $prd)
					{
						/*
						$productStandardList = [];

						if(is_array($prd->productstandard) && count($prd->productstandard)>0){
							foreach($prd->productstandard as $productstandard){
								$productStandardList[]=[
									'standard_id'=>$productstandard->standard_id,
									'standard_name'=>$productstandard->standard->name,
									'label_grade'=>$productstandard->labelgrade->id,
									'label_grade_name'=>$productstandard->labelgrade->name
								];

							}
						}
						*/
						if($renewal_add && count($renewal_standard_ids)>0){
							$pdtstdexits =0;
							foreach($prd->productstandard as $chkproductstandard)
							{
								if(in_array($chkproductstandard->standard_id,$renewal_standard_ids)){
									$pdtstdexits = 1;
								}
							}
							if(!$pdtstdexits){
								continue;
							}
						}


						$productMaterialList = [];
						$materialcompositionname = '';
						if(is_array($prd->productmaterial) && count($prd->productmaterial)>0){
							foreach($prd->productmaterial as $productmaterial){
								$productMaterialList[]=[
									'app_product_id'=>$productmaterial->app_product_id,
									'material_id'=>$productmaterial->material_id,
									'material_name'=>$productmaterial->material_name,
									'material_type_id'=>$productmaterial->material_type_id,
									'material_type_name'=> $productmaterial->material_type_name,
									'material_percentage'=>$productmaterial->percentage
								];
								$materialcompositionname = $materialcompositionname.$productmaterial->percentage.'% '.$productmaterial->material_name.' + ';

							}
							$materialcompositionname = rtrim($materialcompositionname," + ");
						}

						/*productStandardList
						
						prdexpobject["standard_id"] = selstandard.standard_id;
						prdexpobject["standard_name"] = selstandard.standard_name;
						prdexpobject["label_grade"] = selstandard.label_grade;
						prdexpobject["label_grade_name"] = selstandard.label_grade_name;
						*/

						//ApplicationUnitProduct::find()->where(['unit_id' =>  ])->all();

						//ApplicationProductStandard::find()->where(['application_product_id' =>  ])->all();
						$arrsForPdtDetails=array(
							'id'=>$prd->product_id,
							'autoid'=>$prd->id,
							'addition_type'=> $standardaddition_add?0:$prd->product_addition_type,
							'name'=>$prd->product_name, //($prd->product?$prd->product->name:''),
							'wastage'=>$prd->wastage,
							'product_type_name' => $prd->product_type_name,//isset($prd->producttype)?$prd->producttype->name:'',
							'product_type_id'=>isset($prd->producttype)?$prd->producttype->id:'',
							'productMaterialList' => $productMaterialList,
							'materialcompositionname' => $materialcompositionname,
						);	


						$productStandardList = [];
						$arrpdtDetails = [];
						if(is_array($prd->productstandard) && count($prd->productstandard)>0){

							
							$i=0;
							foreach($prd->productstandard as $productstandard){
								if($renewal_add && count($renewal_standard_ids)>0){
									if(!in_array($productstandard->standard_id,$renewal_standard_ids)){
										continue;
									}
								}

								$productStandardList[] = [
									'id' => $productstandard->id,
									'standard_id' => $productstandard->standard_id,
									'standard_name' => $productstandard->standard->name,
									'label_grade' => $productstandard->label_grade_id,
									'label_grade_name' => $productstandard->label_grade_name,//$productstandard->labelgrade->name,
									'pdt_index' => $pdt_index
								];

								
								$arrsForPdtDetails['pdt_index'] = $pdt_index;
								$arrsForPdtDetails['standard_id'] = $productstandard->standard_id;
								$arrsForPdtDetails['standard_name'] = $productstandard->standard->name;
								$arrsForPdtDetails['label_grade'] = $productstandard->label_grade_id;
								$arrsForPdtDetails['label_grade_name'] = $productstandard->label_grade_name;//$productstandard->labelgrade->name;
								//$arrsForPdtDetails['addition_type'] = $productstandard->addition_type;
								$arrsForPdtDetails['pdtListIndex'] = $i;
								

								$appprdarr_details[$productstandard->id]= $arrsForPdtDetails;
								$i++;
								$pdt_index++;
							}
						}
						


						$materialcompositionname = rtrim($materialcompositionname,' + ');
						//$pdt_index_list[$prd->id] = $pdt_index;
						$arrs=array(
							'id'=>$prd->product_id,
							'autoid'=>$prd->id,
							//'pdt_index'=>$pdt_index,
							'name'=> $prd->product_name,//($prd->product?$prd->product->name:''),
							'wastage'=>$prd->wastage,
							'product_type_name' => $prd->product_type_name,//isset($prd->producttype)?$prd->producttype->name:'',
							'product_type_id'=>isset($prd->producttype)?$prd->producttype->id:'',
							'addition_type' => $standardaddition_add?0:$prd->product_addition_type,
							//'standard_id'=>$prd->standard_id,
							//'label_grade'=>$prd->label_grade_id,
							//'standard_name' => $prd->standard->name,
							//'label_grade_name' => $prd->standardlabelgrade->name,
							'productStandardList' => $productStandardList,
							'productMaterialList' => $productMaterialList,
							'materialcompositionname' => $materialcompositionname,
						);	
						$appprdarr[] = $arrs;


						
						
						
					}
				}
				$resultarr["products"]=$appprdarr;
				
				foreach($appprdarr_details as $pdtDetailsDt){
					$resultarr["productDetails"][] = $pdtDetailsDt;
				}
				//$appprdarr_details;
				
				$unitarr=array();
				$unitnamedetailsarr=array();
				//$appUnit=$model->applicationunit;

				if($showonlyNormal){
					$appUnit=$model->applicationunitnormal;
				}else{
					$appUnit=$model->applicationunit;
				}

				$selunitgpsarrlists = [];
				if(count($appUnit)>0)
				{
					foreach($appUnit as $unit)
					{
						if($exclude){
							if(is_array($excludeunits) && count($excludeunits)>0){
								//print_r($excludeunits);
								if( !in_array($unit->id, $excludeunits)){

									continue;
								}
							}
						}

						if($renewal_add && count($renewal_standard_ids)>0){
							$unitstandard_id = [];
							$unitappstandard=$unit->unitappstandard;
							if(count($unitappstandard)>0)
							{
								foreach($unitappstandard as $unitstd)
								{
									$unitstandard_id[] = $unitstd->standard_id;
								}
							}
							$commonStd = array_intersect($renewal_standard_ids, $unitstandard_id);
							if(count($commonStd)<=0){
								continue;
							}
						}


						
						
						$unitarr = $unit->toArray();
						$unitarr["unit_type_name"]=$unit->unit_type_list[$unit->unit_type];
						/*$unitarr["name"]=$unit->name;
						$unitarr["id"]=$unit->id;
						$unitarr["address"]=$unit->address;
						$unitarr["zipcode"]=$unit->zipcode;
						$unitarr["city"]=$unit->city;
						$unitarr["state_id"]=($unit->state_id!="")?$unit->state_id:"";
						$unitarr["country_id"]=($unit->country_id!="")?$unit->country_id:"";
						$unitarr["no_of_employees"]=$unit->no_of_employees;
						*/
						$unitarr["state_id_name"]=($unit->state_id!="")?$unit->state->name:"";
						$unitarr["country_id_name"]=($unit->country_id!="")?$unit->country->name:"";
						
						
						if($unit->unit_type ==1 && $standardaddition_add && $model->currentaddress){
							$unitapplicationaddress = $model->currentaddress;
							$unitarr["name"] = $unitapplicationaddress->unit_name;
							$unitarr["address"] = $unitapplicationaddress->unit_address;
							$unitarr["zipcode"] = $unitapplicationaddress->unit_zipcode;
							$unitarr["city"] = $unitapplicationaddress->unit_city;
							$unitarr["state_id"] = $unitapplicationaddress->unit_state_id;
							$unitarr["country_id"] = $unitapplicationaddress->unit_country_id;
							$unitarr["state_id_name"] = $unitapplicationaddress->unitstate?$unitapplicationaddress->unitstate->name:'';
							$unitarr["country_id_name"] = $unitapplicationaddress->unitcountry?$unitapplicationaddress->unitcountry->name:'';

							$unitnamedetailsarr[$unit->id] = $unitapplicationaddress->unit_name;
							$statelist = State::find()->alias( 't' )->select(['id','name'])->where(['t.country_id'=>$unitapplicationaddress->unit_country_id])->asArray()->all();
							$unitarr["state_list"]= $statelist;
						}else{
							$statelist = State::find()->alias( 't' )->select(['id','name'])->where(['t.country_id'=>$unit->country_id])->asArray()->all();
							$unitarr["state_list"]= $statelist;
							$unitnamedetailsarr[$unit->id] = $unit->name;
						}
						$unitarr["addition_type"]= $standardaddition_add?0:$unit->unit_addition_type;
						//$unitarr["unit_type"]=$unit->unit_type;
						//'addition_type'=>$prd->product_addition_type,
						

						

						if($showonlyNormal){
							$unitprd=$unit->unitproductnormal;
						}else{
							$unitprd=$unit->unitproduct;
						}

						if(count($unitprd)>0)
						{
							$unitprdidsarr=array();
							
							foreach($unitprd as $unitP)
							{

								if(!isset($appprdarr_details[$unitP->application_product_standard_id])){
									continue;
								}


								$unitprdarr=array();
								//$unitprdarr[]=($unitP->product?$unitP->product->name:'');
								//$unitprdarr['pdt_index']=$pdt_index_list[$unitP->product_id];
								$unitprdarr['pdt_id']=$unitP->application_product_standard_id;
								//$unitprdarr['pdt_index']=($unitP->product?$unitP->product->name:'');

								$unitprdidsarr[]=$unitP->application_product_standard_id;							

								$unitarr["products"][]=$unitprdarr;

								$productdetailsunit = (isset($appprdarr_details[$unitP->application_product_standard_id]) ? $appprdarr_details[$unitP->application_product_standard_id] : '');
								if($productdetailsunit !=''){
									$productdetailsunit['addition_type'] = $standardaddition_add?0:$unitP->product_addition_type;
								}
								$unitarr["product_details"][]= $productdetailsunit;
								
								
							}
							if(!isset($unitarr["product_details"])){
								$unitarr["product_details"] = [];
							}
							//pdt_index
							
							
							$unitarr["product_ids"]=$unitprdidsarr;
						}	
						
						//standards
						$unitstdidsarr=array();
						$unitstddetailssarr=array();
						$exitsunitstdidsarr = [];
						

						if($showonlyNormal){
							$unitappstandard=$unit->unitappstandardnormal;
						}else{
							$unitappstandard=$unit->unitappstandard;
						}
						if(count($unitappstandard)>0)
						{
							foreach($unitappstandard as $unitstd)
							{
								if($renewal_add && count($renewal_standard_ids)>0){
									if(!in_array($unitstd->standard_id,$renewal_standard_ids)){
										continue;
									}
								}

								$unitstddetailssarrtemp = [];
								$unitstdidsarr[]=$unitstd->standard_id;

								if($standardaddition_add || $unitstd->addition_type==0){
									$exitsunitstdidsarr[]=$unitstd->standard_id;
								}
								
								$unitstddetailssarrtemp['id']=$unitstd->standard_id;
								$unitstddetailssarrtemp['name']=$unitstd->standard->name;

								$unitstddetailssarr[]=$unitstddetailssarrtemp;
							}
						}

						$unitarr["existsstandards"]=$exitsunitstdidsarr;
						$unitarr["standards"]=$unitstdidsarr;
						$unitarr["standarddetails"]=$unitstddetailssarr;
						
						//Business Sector
						$unitbsectoridsarr=array();
						$unitbsarr=array();
						$unitbsarrobj=array();
						$unitbsarrDetails = array();
						$existsunitbsectoridsarr = [];

						
						if($showonlyNormal){
							$unitbsector=$unit->unitbusinesssectornormal;
						}else{
							$unitbsector=$unit->unitbusinesssector;
						}
						if(count($unitbsector)>0)
						{
							
							$arrSectorList = [];
							$unitgpsarr = [];
							$selunitgpsarr = [];
							foreach($unitbsector as $unitbs)
							{
								if($renewal_add && count($renewal_standard_ids)>0){
									$business_sector_id = $unitbs->business_sector_id;
									$chkBusiness = ['business_sector_id'=>$business_sector_id,'standard_id'=>$renewal_standard_ids];
									$relatedsector = Yii::$app->globalfuns->checkBusinessSectorInStandard($chkBusiness);
									if(!$relatedsector){
										continue;
									}
								}
								$business_sector_id = $unitbs->business_sector_id;

								if($model->audit_type == $model->arrEnumAuditType['process_addition']){
									$unitbsectorgp=$unitbs->unitbusinesssectorgroup;
									if(count($unitbsectorgp)>0)
									{	
										$businessectorsgps = [];								
										foreach($unitbsectorgp as $unitbsgp)
										{
											$businessectorsgps[] = $unitbsgp->business_sector_group_id;
										}
										$selunitgpsarr[$unit->id]= [
											'sector_id' =>$business_sector_id,
											'business_sector_group_ids' => $businessectorsgps,
										];
									}
								}


								$unitbsectorgps=$unitbs->unitbusinesssectorgroup;
								if(count($unitbsectorgps)>0)
								{	
									$businessectorsgps = [];								
									foreach($unitbsectorgps as $unitbsgps)
									{
										//$businessectorsgps[] = $unitbsgp->business_sector_group_id;
									//}
										$selunitgpsarrlists[$unit->id][$business_sector_id][] = [
											'id' =>$unitbsgps->business_sector_group_id,
											'group_code' => $unitbsgps->business_sector_group_name//group->group_code,
										];
									}
								}




								

								$unitbsarr[]=$unitbs->business_sector_name;//($unitbs->businesssector)?$unitbs->businesssector->name:'';
								$unitbsarrDetails[$business_sector_id]=$unitbs->business_sector_name;//($unitbs->businesssector)?$unitbs->businesssector->name:'';
								$unitbsectoridsarr[]=$business_sector_id;

								if($standardaddition_add || $unitbs->addition_type==0){
									$existsunitbsectoridsarr[]=$business_sector_id;
								}
								

								/*$unitbsgroup = ApplicationUnitBusinessSectorGroup::find()::where(['unit_id' =>$unit_id,'unit_business_sector_id'=>$app_bsector_id])->all();
								foreach($unitbsgroup as $unitgpp){
									$unitgpsarr[]= $unitgpp->business_sector_group_id;
									unit_id
									unit_business_sector_id
								}
								*/
								$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
								$command = $connection->createCommand("SELECT sgp.id,sgp.unit_id,sgp.unit_business_sector_id,GROUP_CONCAT(sgp.business_sector_group_id) as business_sector_group_ids 
								 from tbl_application_unit_business_sector_group as sgp 
								 INNER JOIN tbl_application_unit_business_sector as sec on sec.id = sgp.unit_business_sector_id
								WHERE sec.business_sector_id=".$business_sector_id." AND sgp.unit_id=".$unit->id." AND sec.unit_id=".$unit->id." 
								group by unit_business_sector_id,unit_id");
								$result = $command->queryAll();
								//$sectorgpArr = [];
								
								if(count($result)>0){
									foreach($result as $sectorgroup){
										$unitgpsarr[]= [
											'unit_id' =>$unit->id,
											'sector_id' =>$business_sector_id,
											'business_sector_group_ids' => explode(',',$sectorgroup['business_sector_group_ids']),
										];
									}
								}
								
								//$model->status == $model->arrEnumStatus['submitted']
								if($model->status == $model->arrEnumStatus['submitted'])
								{
									$command = $connection->createCommand("SELECT sgp.id,sgp.group_code from tbl_business_sector_group as sgp
											WHERE business_sector_id=".$business_sector_id." AND sgp.status=0   AND standard_id IN(".implode(',',$unitarr["standards"]).") ");
									$result = $command->queryAll();
									//$sectorgpArr = [];
									if(count($result)>0){
										foreach($result as $sectorgroup){
											 
											$arrSectorList[$business_sector_id][] =[
													'id'=>$sectorgroup['id'],
													'group_code'=>$sectorgroup['group_code']
													
												];

										}
									}
									/* 'usersArr'=>$userslist,
													 'users'=>implode(', ', $userslist),
													'usersfound' => $usersfound */
									/*
									$command = $connection->createCommand("SELECT sgp.id,sgp.group_code from tbl_business_sector_group as sgp
											WHERE business_sector_id=".$business_sector_id." AND standard_id IN(".implode(',',$unitarr["standards"]).") ");
									$result = $command->queryAll();
									//$sectorgpArr = [];
									if(count($result)>0){
										foreach($result as $sectorgroup){
											 
											//For getting Auditors
											$franchiseCondition = ' AND user_role.franchise_id= '.$model->franchise_id.' ';
											$sectorgpcondition = " and usrsectorgroup.business_sector_group_id =".$sectorgroup['id']." ";
											$stdcondition = " and usrstd.standard_id in(".implode(',',$unitarr["standards"]).")";
											
											$command = $connection->createCommand("SELECT user.id,first_name ,last_name 
												FROM tbl_users as user 
												inner join tbl_user_role as user_role on  user_role.user_id = user.id 
												INNER JOIN `tbl_rule` AS rule ON  user_role.role_id=rule.role_id AND rule.privilege='audit_execution' 
												INNER JOIN `tbl_user_business_sector_group` AS usrsectorgroup on usrsectorgroup.user_id = user.id 

												where user_type=1  ".$franchiseCondition." ".$sectorgpcondition." group by user.id");
											$result = $command->queryAll();
											$usersListArr = [];
											if(count($result)>0){
												foreach($result as $userdata){
													$usersListArr[] = $userdata['first_name'].' '.$userdata['last_name'];
												}
											}



											//For getting Technical Experts
											$technicalcommand = $connection->createCommand("SELECT user.id,first_name ,last_name  FROM tbl_users as user 
											inner join tbl_user_role as user_role on  user_role.user_id = user.id 
											inner join tbl_role as role on  role.id = user_role.role_id  and role.resource_access=3  
											INNER JOIN `tbl_user_business_sector_group` AS usrsectorgroup on usrsectorgroup.user_id = user.id 
													where user.user_type=1 ".$franchiseCondition." ".$sectorgpcondition." group by user.id");
											$technicalresult = $technicalcommand->queryAll();
											$technicalListArr = [];
											if(count($technicalresult)>0){
												foreach($technicalresult as $technicaldata){
													$technicalListArr[] =$technicaldata['first_name'].' '.$technicaldata['last_name'];
												}
											}
											$userslist = array_unique(array_merge($usersListArr,$technicalListArr));
											$usersfound = 1;
											if(count($userslist)<=0){
												$userslist = ['No Users Found'];
												$usersfound = 0;
											}
											$arrSectorList[$business_sector_id][] =[
													'id'=>$sectorgroup['id'],
													'group_code'=>$sectorgroup['group_code'],
													 'usersArr'=>$userslist,
													 'users'=>implode(', ', $userslist),
													'usersfound' => $usersfound
												];

											






										}
									}
									*/

								}
								
							}
							$unitarr["bsectorsselgroup"]=$unitgpsarr;
							
							$unitarr["bsectorsusers"]=$arrSectorList;

							$unitarr["bsectorsgroupselected"]=$selunitgpsarr;
							
							//print_r($unitbsectoridsarr); die;
							
						}
						

						if(isset($unitarr["standards"]) && is_array($unitarr["standards"])){
							$stds='';
							foreach($unitarr["standards"] as $value)
							{
								$stds.=$value.",";
							}
							$std_ids=substr($stds, 0, -1);
							
							$command = $connection->createCommand("SELECT bs.id,bs.name FROM tbl_business_sector AS bs 
							INNER JOIN tbl_business_sector_group AS bsg ON bs.id=bsg.business_sector_id
								AND bsg.standard_id IN (".$std_ids.") AND bs.status=0 AND bsg.status=0 GROUP BY bs.id");
							$result = $command->queryAll();
							if(count($result)>0)
							{
								foreach($result as $vals)
								{
									$values=array();
									$values['id'] = $vals['id'];
									$values['name'] = $vals['name'];
									$unitbsarrobj[]=$values;
								}

							}
						}
							


						
						$unitarr["bsectorsdetails"]=$unitbsarrDetails;
						$unitarr["bsectors"]=$unitbsarr;
						$unitarr["bsector_ids"]=$unitbsectoridsarr;
						$unitarr["bsector_data"]=$unitbsarrobj;
						$unitarr["existsbsector_ids"]=$existsunitbsectoridsarr;
						
						$unitarr["selunitgpsarrlists"]=$selunitgpsarrlists;
						
						$unitprocess_data=[];
						$unitprocessnames=[];
						$unitpcsarr=array();
						$unitpcsarrobj=array();
						$existsunitprocess_data = [];
						
						//$connection = Yii::$app->getDb();
						$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();

						if($renewal_add && count($renewal_standard_ids)>0){
							$unitprocess=$unit->unitprocessall;
						}else if($showonlyNormal){
							$unitprocess=$unit->unitprocessnormal;
						}else{
							$unitprocess=$unit->unitprocess;
						}


						$newunitpcsarr = [];
						if(count($unitprocess)>0)
						{
							
							$icnt=0;
							$chkprocessunique = [];
							foreach($unitprocess as $unitPcs)
							{
								if($model->audit_type == 3){
									if($unitPcs->process_type =='1'){
										$newunitpcsarr[]=$unitPcs->process_name;//$unitPcs->process->name;
									}else{
										$unitpcsarr=array();
										$unitpcsarr['id']=$unitPcs->process_id;
										$unitpcsarr['name']=$unitPcs->process_name;//$unitPcs->process->name;
										$unitprocess_data[]=$unitpcsarr;
										$unitprocessnames[]=$unitPcs->process_name;//$unitPcs->process->name;
									}
								}else{
									if($renewal_add && count($renewal_standard_ids)>0){
										if(!in_array($unitPcs->standard_id,$renewal_standard_ids)){
											continue;
										}
									}
									if(in_array($unitPcs->process_id,$chkprocessunique)){
										continue;
									}
									$chkprocessunique[] = $unitPcs->process_id;


									$unitpcsarr=array();
									$unitpcsarr['id']=$unitPcs->process_id;
									$unitpcsarr['name']=$unitPcs->process_name;//$unitPcs->process->name;
									$unitpcsarr['addition_type']=$standardaddition_add?0:$unitPcs->process_type;
									$unitprocess_data[]=$unitpcsarr;
									$unitprocessnames[]=$unitPcs->process_name;//$unitPcs->process->name;
									if($standardaddition_add || $unitPcs->process_type==0){
										$existsunitprocess_data[]=$unitpcsarr;
									}
									
								}
								
								

								$icnt++;
							}

							$bsector_ids='';
							foreach($unitbsectoridsarr as $value)
							{
								$bsector_ids.=$value.",";
							}
							$bsector_ids=substr($bsector_ids, 0, -1);

							/*
							$command = $connection->createCommand("SELECT bsgp.process_id,procs.name FROM `tbl_business_sector_group` AS bsg INNER JOIN `tbl_business_sector_group_process` AS bsgp ON bsg.id=bsgp.business_sector_group_id INNER JOIN `tbl_process` AS procs ON procs.id=bsgp.process_id WHERE bsg.standard_id IN (".$std_ids.") AND bsg.business_sector_id IN(".$bsector_ids.") GROUP BY bsgp.process_id");
							$result = $command->queryAll();
							if(count($result)>0)
							{
								foreach($result as $vals)
								{
									$values=array();
									$values['id'] = $vals['process_id'];
									$values['name'] = $vals['name'];
									$unitpcsarrobj[]=$values;
								}
							}
							*/
						}
						
						$unitarr["new_process"]=$newunitpcsarr;
						$unitarr["process"]=$unitprocessnames;
						$unitarr["process_ids"]=$unitprocess_data;
						$unitarr["process_data"]=$unitpcsarrobj;
						$unitarr["existsprocess_ids"]=$existsunitprocess_data;


						$unitstd=$unit->unitstandard;
						unset($unitarr["certified_standard"]);
						$certstdarr= [];
						if(count($unitstd)>0)
						{
							
							foreach($unitstd as $unitS)
							{
								$unitstdfilearr=[];
								$standardfile=$unitS->unitstandardfile;
								if(count($standardfile)>0)
								{
									
									foreach($standardfile as $stdfile)
									{
										$unitstdfile = [];
										
										$unitstdfile['id']=$stdfile->id;
										$unitstdfile['name']=$stdfile->file;
										$unitstdfile['type']=$stdfile->type;
										$unitstdfilearr[]= $unitstdfile;
									}
									//$unitstdfilearr[]=$stdfile->file;
								}
								if($unitS->expiry_date!=''){
									$unitS->expiry_date = date($date_format,strtotime($unitS->expiry_date));
								}
								$certstdarr[]=array("id"=>$unitS->standard_id, "expiry_date"=>$unitS->expiry_date, "license_number"=>$unitS->license_number,"standard"=>($unitS->standard?$unitS->standard->name:''),"files"=>$unitstdfilearr);
							}
							$unitarr["certified_standard"]=$certstdarr;
						}

						$unitdetailsarr[]=$unitarr;
					}
					$resultarr["units"]=$unitdetailsarr;
				}
				
				$resultarr["applicationchecklistcmt"]=[];
				$applicationchecklistcmt=[];
				$appchecklistcmt=$model->applicationchecklistcmt;
				if(count($appchecklistcmt)>0)
				{
					$checklistcmtarr=[];
					foreach($appchecklistcmt as $checklistcmt)
					{
						$checklistcmtarr[]=array('id'=>$checklistcmt->id,'question'=>$checklistcmt->question,'answer'=>$checklistcmt->answer!='1'?"No":"Yes",'comment'=>$checklistcmt->comment,'document'=>$checklistcmt->document);
					}
					$resultarr["applicationchecklistcmt"]=$checklistcmtarr;
				}


				$resultarr["applicationcertifiedbyothercb"]=[];
				$applicationcertifiedbyothercb=[];
				$appcertificationbody=$model->certificationbody;
				if(count($appcertificationbody)>0)
				{
					$certificationbodycmtarr=[];
					foreach($appcertificationbody as $certificationbody)
					{
						$certificationbodycmtarr[]=array('id'=>$certificationbody->id,'standard_id'=>$certificationbody->standard_id,'standard_name'=>$certificationbody->standard->code,'certification_body'=>$certificationbody->certification_body,'certification_body_name'=>$certificationbody->cb?$certificationbody->cb->name:'', 'validity_date'=> date($date_format,strtotime($certificationbody->validity_date)),'certification_file'=>$certificationbody->certification_file);
					}
					$resultarr["applicationcertifiedbyothercb"]=$certificationbodycmtarr;
				}


				$applicationreviews=[];
				$reviewarr=[];
				$reviewcommentarr=[];
				$appReview=$model->applicationreview;
				if(count($appReview)>0)
				{
					foreach($appReview as $review)
					{
						$reviewarr=[];
						$reviewcommentarr=[];
						$applicationreviewcmt=$review->applicationreviewcomment;
						if(count($applicationreviewcmt)>0)
						{
							foreach($applicationreviewcmt as $reviewComment)
							{
								$reviewcommentarr[]=array('question'=>$reviewComment->question,'answer'=>$reviewComment->arrAnswer[$reviewComment->answer],'comment'=>$reviewComment->comment);
							}	
						}
						



						$unitreviews=[];
						$unitreviewarr=[];
						$unitreviewcommentarr=[];
						$unitapplicationreviewcmt=$review->applicationunitreviewcomment;
						if(count($unitapplicationreviewcmt)>0)
						{
							foreach($unitapplicationreviewcmt as $unitreviewComment)
							{
								$unitreviewcommentarr[$unitreviewComment->unit_id][] = array(
										'question'=>$unitreviewComment->question
										,'answer'=>$unitreviewComment->arrAnswer[$unitreviewComment->answer]
										,'comment'=>$unitreviewComment->comment
									);
										
										
							}	
							//print_r($unitreviewcommentarr); die;
							foreach($unitreviewcommentarr as $unitkey => $units){
								if(isset($unitnamedetailsarr[$unitkey])){
									$unitreviews[]=array('unit_name'=>$unitnamedetailsarr[$unitkey],'unit_id'=>$unitkey,'reviews'=>$units);
								}
							}
							/*'unit_id'=> $unitreviewComment->unit_id,
								'unit_name'=> 'test',*/
						}
						
						
						$reviewarr['reviewcomments']=$reviewcommentarr;
						$reviewarr['unitreviewcomments']=$unitreviews;
						$reviewarr['reviewer']=($review->reviewer?$review->reviewer->first_name.' '.$review->reviewer->last_name:'');
						$reviewarr['answer']=$review->answer;
						
						$reviewarr['answer_name']=$review->answer?$review->arrReviewAnswer[$review->answer]:'NA';
						
						$reviewarr['status']=$review->status;		
						$reviewarr['status_name']=$review->arrReviewStatus[$review->status];					
																
						$reviewarr['created_at']=date($date_format,$review->created_at);
						$reviewarr['updated_at']=($review->status==2 || $review->status==3)?date($date_format,$review->updated_at):'NA';
						$reviewarr['status_comments']=$review->comment;
						$reviewarr['review_result_name']=isset($review->arrReviewResult[$review->review_result])?$review->arrReviewResult[$review->review_result]:'';
						$reviewarr['review_result']=$review->review_result;

						$reviewarr['reviewcomments']=$reviewcommentarr;

						$applicationreviews[]=$reviewarr;
					}
					$resultarr["applicationreviews"]=$applicationreviews;
				}	


				$approvalcommentarr=[];
				$appApproval=$model->applicationapproval;
				if(count($appApproval)>0)
				{
					foreach($appApproval as $approval){
						$reviewarr=[];
						$reviewarr['comment']=$approval->comment;
						$reviewarr['status']=$approval->status;
						$reviewarr['status_name']=$approval->statusarr[$approval->status];
						$reviewarr['created_at']=date($date_format,$approval->created_at);
						$reviewarr['updated_at']=date($date_format,$approval->updated_at);
						$reviewarr['user_id']=$approval->user_id;
						$reviewarr['approver_name']=$approval->username->first_name.' '.$approval->username->last_name;
						$approvalcommentarr[] = $reviewarr;
					}
				}
				$resultarr["applicationapprovals"]=$approvalcommentarr;


				$resultarr['hasapprover'] = 0;
				$resultarr['approverid'] = '';
				$ApplicationApproverModel = ApplicationApprover::find()->where(['app_id' => $data['id'],'approver_status'=>1])->one();
				if ($ApplicationApproverModel !== null)
				{
					$resultarr['hasapprover'] = 1;
					$resultarr['approverid'] = $ApplicationApproverModel->user_id;
				}
				$resultarr['showApplicationApprove'] = 0;
				if($model->status == $model->arrEnumStatus['approval_in_process']
				 && ($resultarr['approverid'] == $userid || Yii::$app->userrole->isAdmin()) ){
					$resultarr['showApplicationApprove'] = 1;
				}
				

				$ApplicationReviewerModel = ApplicationReviewer::find()->where(['app_id' => $data['id'],'reviewer_status'=>1])->one();
				$showApplicationReview = 0;
				if ($ApplicationReviewerModel !== null)
				{
					$resultarr['hasreviewer'] = 1;
					$resultarr['reviewerid'] = $ApplicationReviewerModel->user_id;
					if(Yii::$app->userrole->hasRights(array('application_review')) && ($ApplicationReviewerModel->user_id == $userid || Yii::$app->userrole->isAdmin()) && $model->status == $model->arrEnumStatus['review_in_process']){
						$showApplicationReview = 1;
					}				
				}
				$resultarr['showApplicationReview'] = $showApplicationReview;


				$materialmodel = new ProductTypeMaterialComposition();
				$resultarr['material_type'] = $materialmodel->material_type;


				$applicationobj =  new Application();
				$resultarr['arrEnumStatus'] = $applicationobj->arrEnumStatus;
			
				$appapprovalobj = new ApplicationApproval();
				$approvalStatusList = [];
				foreach($appapprovalobj->approvestatusarr as $key => $statusname){
					$approvalStatusList[] = ['id'=>$key,'name'=>$statusname];
				}
				$resultarr['approvalStatusList'] = $approvalStatusList;

				return $resultarr;			
			}
		}	
		return $responsedata;
	}



	public function actionGetProductDetails()
	{

		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$franchiseid=$userData['franchiseid'];
		
		//$model = Application::find()->alias( 't' )->innerJoinWith('applicationproduct')->innerJoinWith('applicationproduct.productstandard')->where(['t.id' => $data['id']]);
		$model = Application::find()->where(['id' => $data['id']]);
		if($resource_access != 1)
		{
			if($user_type== 1 && ! in_array('application_management',$rules) && !in_array('audit_review',$rules)  && !in_array('audit_execution',$rules)){
				return $responsedata;
			}else if($user_type==3){
				if($resource_access == 5){
					$model = $model->andWhere('franchise_id="'.$franchiseid.'" or created_by="'.$franchiseid.'"');
				}else{
					$model = $model->andWhere('franchise_id="'.$userid.'" or created_by="'.$userid.'"');
				}
				
			}else if($user_type==2){
				$model = $model->andWhere('created_by="'.$userid.'"');
			}
		}
		
	    /*
		if(isset($data['standard_id']) && $data['standard_id']!='')
		{
			$model = $model->andWhere('tbl_application_product_standard.standard_id="'.$data['standard_id'].'"');
		}
		*/
		$standardID=isset($data['standard_id'])?$data['standard_id']:[];
		
		$connection = Yii::$app->getDb();
		$model= $model->one();
		if ($model !== null)
		{
			/*
			$showonlyNormal = 0;
			if($model->audit_type == $model->arrEnumAuditType['normal'] && ( (!isset($data['showtype']) || isset($data['showtype']) && $data['showtype']!='all' ))){
				$showonlyNormal = 1;
			}

			$standardaddition_add = 0; 
			if(isset($data['actiontype']) && $data['actiontype']== 'add'){
				$standardaddition_add = 1;
			}

			$audit_type_data = isset($data['audit_type'])?$data['audit_type']:'';
			$renewal_id = isset($data['renewal_id'])?$data['renewal_id']:'';
			$renewal_add = 0;
			$renewal_standard_ids = [];
			if($standardaddition_add && $renewal_id && $renewal_id>0){
				$renewal_add = 1;

				$ApplicationRenewal = ApplicationRenewal::find()->where(['id' =>$renewal_id])->one();
				if($ApplicationRenewal !== null){
					$renewalstandard = $ApplicationRenewal->renewalstandard;
					if(count($renewalstandard)>0){
						foreach($renewalstandard as $rstandard){
							$renewal_standard_ids[] = $rstandard->standard_id;
						}
					}
				}

			}
			*/

			$resultarr=array();

			$resultarr["id"]=$model->id;
			$resultarr["code"]=$model->code;
			$resultarr['created_at']=date($date_format,$model->created_at);
			$resultarr["created_by"]=($model->username!="")?$model->username->first_name.' '.$model->username->last_name:"";
			$resultarr["app_status"]=$model->status;
			$resultarr["status"]=$model->arrStatus[$model->status];
			$resultarr["franchise_id"]=$model->franchise_id;

			$appprdarr=[];
			$appprdarr_details=[];
			
			$standard_ids = [];
			
			//if($showonlyNormal){
			//	$appProduct=$model->applicationproductnormal;
			//}else{
			//	$appProduct=$model->applicationproduct;
			//}
			
			$resultarr["productDetails"] = [];
			if(isset($data['product_addition_id']) && $data['product_addition_id']!='' && $data['product_addition_id']>0){
				$additionmodel = ProductAddition::find()->where(['id'=>$data['product_addition_id'] ])->one();
				if($additionmodel !== null){
					$appProduct=$additionmodel->additionproduct;
					$resultarr = Yii::$app->globalfuns->getProductAdditionProducts($appProduct,$standardID);
				}				
			}else{
				$appProduct=$model->applicationproduct;
				$resultarr = Yii::$app->globalfuns->getAppProducts($appProduct,$standardID);
			}
			

			//getProductAdditionProducts
			return $resultarr;

			/*
			if(count($appProduct)>0)
			{
				$pdt_index = 0;
				foreach($appProduct as $prd)
				{
											

					$productMaterialList = [];
					$materialcompositionname = '';
					if(is_array($prd->productmaterial) && count($prd->productmaterial)>0){
						foreach($prd->productmaterial as $productmaterial){
							$productMaterialList[]=[
								'app_product_id'=>$productmaterial->app_product_id,
								'material_id'=>$productmaterial->material_id,
								'material_name'=>$productmaterial->material_name,
								'material_type_id'=>$productmaterial->material_type_id,
								'material_type_name'=> $productmaterial->material_type_name,
								'material_percentage'=>$productmaterial->percentage
							];
							$materialcompositionname = $materialcompositionname.$productmaterial->percentage.'% '.$productmaterial->material_name.' + ';

						}
						$materialcompositionname = rtrim($materialcompositionname," + ");
					}

					$arrsForPdtDetails=array(
						'id'=>$prd->product_id,
						'autoid'=>$prd->id,
						'addition_type'=>$prd->product_addition_type,
						'name'=>$prd->product_name, 
						'wastage'=>$prd->wastage,
						'product_type_name' => $prd->product_type_name,
						'product_type_id'=>isset($prd->producttype)?$prd->producttype->id:'',
						'productMaterialList' => $productMaterialList,
						'materialcompositionname' => $materialcompositionname,
					);	


					$productStandardList = [];
					$arrpdtDetails = [];
					if(is_array($prd->productstandard) && count($prd->productstandard)>0)
					{
						$i=0;
						foreach($prd->productstandard as $productstandard){
							if(count($standard_ids)>0){
								if(!in_array($productstandard->standard_id,$standard_ids)){
									continue;
								}
							}

							$productStandardList[] = [
								'id' => $productstandard->id,
								'standard_id' => $productstandard->standard_id,
								'standard_name' => $productstandard->standard->name,
								'label_grade' => $productstandard->label_grade_id,
								'label_grade_name' => $productstandard->label_grade_name,
								'pdt_index' => $pdt_index
							];

							
							$arrsForPdtDetails['pdt_index'] = $pdt_index;
							$arrsForPdtDetails['standard_id'] = $productstandard->standard_id;
							$arrsForPdtDetails['standard_name'] = $productstandard->standard->name;
							$arrsForPdtDetails['label_grade'] = $productstandard->label_grade_id;
							$arrsForPdtDetails['label_grade_name'] = $productstandard->label_grade_name;
							$arrsForPdtDetails['pdtListIndex'] = $i;
							

							$appprdarr_details[$productstandard->id]= $arrsForPdtDetails;
							$i++;
							$pdt_index++;
						}
					}
					


					$materialcompositionname = rtrim($materialcompositionname,' + ');
					$arrs=array(
						'id'=>$prd->product_id,
						'autoid'=>$prd->id,
						'name'=> $prd->product_name,
						'wastage'=>$prd->wastage,
						'product_type_name' => $prd->product_type_name,
						'product_type_id'=>isset($prd->producttype)?$prd->producttype->id:'',
						'addition_type' => $prd->product_addition_type,
						'productStandardList' => $productStandardList,
						'productMaterialList' => $productMaterialList,
						'materialcompositionname' => $materialcompositionname,
					);	
					$appprdarr[] = $arrs;
				}
			}
			$resultarr["products"]=$appprdarr;
			
			foreach($appprdarr_details as $pdtDetailsDt)
			{
				$resultarr["productDetails"][] = $pdtDetailsDt;
			}

			return $resultarr;
			*/
		}
	}


	/*
	public function actionViewJune062020()
	{

		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
						
		//$model = Application::find();
		

		$model = Application::find()->where(['id' => $data['id']]);
		 
		$exclude = 0;
		$excludeunits= [];
		if(isset($data['exclude']) && $data['exclude']){
			$exclude=1;

			if(isset($data['units']) && $data['units']){
				$excludeunits= explode(',',$data['units']);
			}
		}
		

		if($resource_access != 1){
			 
			if($user_type== 1 && ! in_array('application_management',$rules)){
				return $responsedata;
			}else if($user_type==3){
				$model = $model->andWhere('franchise_id="'.$userid.'" or created_by="'.$userid.'"');
			}else if($user_type==2){
				$model = $model->andWhere('created_by="'.$userid.'"');
			}
		}
		 
		$connection = Yii::$app->getDb();
		$model= $model->one();
		
		$appreview = new ApplicationReview;
		if ($model !== null)
		{
			$showonlyNormal = 0;
			if($model->audit_type == $model->arrEnumAuditType['normal']){
				$showonlyNormal = 1;
			}
			$resultarr=array();

			$showedit_view = 0;
			
			if($model->audit_type == $model->arrEnumAuditType['unit_addition']){
				if($model->status ==  $model->arrEnumStatus['pending_with_customer'] && $user_type==2){
					$showedit_view = 1;
				}
			}else if($model->audit_type == $model->arrEnumAuditType['normal'] || $model->audit_type == $model->arrEnumAuditType['renewal']){
				if($model->status ==  $model->arrEnumStatus['open'] || $model->status ==  $model->arrEnumStatus['pending_with_customer'] || $model->status ==  $model->arrEnumStatus['submitted'] ){
					$showedit_view = 1;
				}
			}else{
				if($model->status ==  $model->arrEnumStatus['pending_with_customer']){
					$showedit_view = 1;
				}
			}
			
			$resultarr["showedit_view"]=$showedit_view;

			$resultarr["id"]=$model->id;
			$resultarr["code"]=$model->code;
			$resultarr["company_file"]=$model->company_file;
			$resultarr['created_at']=date($date_format,$model->created_at);
			$resultarr["company_name"]=$model->companyname;
			$resultarr["address"]=$model->address;
			$resultarr["zipcode"]=$model->zipcode;
			$resultarr["city"]=$model->city;
			$resultarr["salutation"]=($model->salutation!="")?$model->salutation:"";
			$resultarr["salutation_name"]=($model->salutation!="")?$model->arrSalutation[$model->salutation]:"";
			
			$resultarr["title"]=($model->title!="")?$model->title:"";
			$resultarr["first_name"]=($model->first_name!="")?$model->first_name:"";
			$resultarr["last_name"]=($model->last_name!="")?$model->last_name:"";
			$resultarr["job_title"]=($model->job_title!="")?$model->job_title:"";
			$resultarr["telephone"]=($model->telephone!="")?$model->telephone:"";
			$resultarr["email_address"]=($model->job_title!="")?$model->email_address:"";
						
			$resultarr["state_id_name"]=($model->state_id!="")?$model->state->name:"";
			$resultarr["country_id_name"]=($model->country_id!="")?$model->country->name:"";
			$resultarr["state_id"]=($model->state_id!="")?$model->state_id:"";
			$resultarr["country_id"]=($model->country_id!="")?$model->country_id:"";
			$resultarr["created_by"]=($model->created_by!="")?$model->username->first_name.' '.$model->username->last_name:"";
			$resultarr["certification_status"]=$model->certification_status;

			$resultarr["reject_comment"]=$model->reject_comment;
			$resultarr["rejected_date"]=date($date_format,strtotime($model->rejected_date));

			//$resultarr["preferred_partner_id"]=$model->preferred_partner_id;
			//$resultarr["preferred_partner_id_name"]=($model->preferredpartner?$model->preferredpartner->name:'');
			
			$resultarr["app_status"]=$model->status;
			$resultarr["status"]=$model->arrStatus[$model->status];
			$resultarr["franchise_id"]=$model->franchise_id;

			$resultarr['process_id']='';
			$resultarr['parent_app_id']= $model->parent_app_id;
			$resultarr['audit_type']= $model->audit_type;
			if($model->audit_type == 3){
				$processaddition = ProcessAddition::find()->where(['app_id'=>$model->parent_app_id,'new_app_id'=>$model->id])->one();
				if($processaddition!==null){
					$resultarr['addition_id']=$processaddition->id;
				}
			}else if($model->audit_type == 4){
				$addition = StandardAddition::find()->where(['app_id'=>$model->parent_app_id,'new_app_id'=>$model->id])->one();
				if($addition!==null){
					$resultarr['addition_id']=$addition->id;
				}	
			}else if($model->audit_type == 5){
				$addition = UnitAddition::find()->where(['app_id'=>$model->parent_app_id,'new_app_id'=>$model->id])->one();
				if($addition!==null){
					$resultarr['addition_id']=$addition->id;
				}
			}

			if($model->franchise){
				$resultarr["franchise"]= $model->franchise->usercompanyinfo->toArray();
				$resultarr["franchise"]['company_country_name']= $model->franchise->usercompanyinfo->companycountry->name;
				$resultarr["franchise"]['company_state_name']= $model->franchise->usercompanyinfo->companystate?$model->franchise->usercompanyinfo->companystate->name:'';
			}

			$appstdarr=[];
			$arrstandardids=[];
			//$appStandard=$model->applicationstandard;

			if($showonlyNormal){
				$appStandard=$model->applicationstandardnormal;
			}else{
				$appStandard=$model->applicationstandard;
			}
			

			if(count($appStandard)>0)
			{
				foreach($appStandard as $std)
				{
					$appstdarr[]=($std->standard?$std->standard->name:'');	
					$arrstandardids[]=$std->standard_id;
				}
			}
			$resultarr["standards"]=$appstdarr;
			$resultarr["standard_ids"]=$arrstandardids;
			
			$appprdarr=[];
			$appprdarr_details=[];
			$appProduct=$model->applicationproduct;

			if($showonlyNormal){
				$appProduct=$model->applicationproductnormal;
			}else{
				$appProduct=$model->applicationproduct;
			}

			if(count($appProduct)>0)
			{
				$pdt_index = 0;
				foreach($appProduct as $prd)
				{
					 
					$productMaterialList = [];
					$materialcompositionname = '';
					if(is_array($prd->productmaterial) && count($prd->productmaterial)>0){
						foreach($prd->productmaterial as $productmaterial){
							$productMaterialList[]=[
								'app_product_id'=>$productmaterial->app_product_id,
								'material_id'=>$productmaterial->material_id,
								'material_name'=>$productmaterial->material->name,
								'material_type_id'=>$productmaterial->material_type_id,
								'material_type_name'=> $productmaterial->material->material_type[$productmaterial->material_type_id],
								'material_percentage'=>$productmaterial->percentage
							];
							$materialcompositionname = $materialcompositionname.$productmaterial->percentage.'% '.$productmaterial->material->name.' + ';

						}
						$materialcompositionname = rtrim($materialcompositionname," + ");
					}

					 

					//ApplicationUnitProduct::find()->where(['unit_id' =>  ])->all();

					//ApplicationProductStandard::find()->where(['application_product_id' =>  ])->all();
					$arrsForPdtDetails=array(
						'id'=>$prd->product_id,
						'autoid'=>$prd->id,
						'addition_type'=>$prd->product_addition_type,
						'name'=>($prd->product?$prd->product->name:''),
						'wastage'=>$prd->wastage,
						'product_type_name' => isset($prd->producttype)?$prd->producttype->name:'',
						'product_type_id'=>isset($prd->producttype)?$prd->producttype->id:'',
						'productMaterialList' => $productMaterialList,
						'materialcompositionname' => $materialcompositionname,
					);	


					$productStandardList = [];
					$arrpdtDetails = [];
					if(is_array($prd->productstandard) && count($prd->productstandard)>0){
						$i=0;
						foreach($prd->productstandard as $productstandard){
							$productStandardList[] = [
								'id' => $productstandard->id,
								'standard_id' => $productstandard->standard_id,
								'standard_name' => $productstandard->standard->name,
								'label_grade' => $productstandard->label_grade_id,
								'label_grade_name' => $productstandard->labelgrade->name,
								'pdt_index' => $pdt_index
							];

							
							$arrsForPdtDetails['pdt_index'] = $pdt_index;
							$arrsForPdtDetails['standard_id'] = $productstandard->standard_id;
							$arrsForPdtDetails['standard_name'] = $productstandard->standard->name;
							$arrsForPdtDetails['label_grade'] = $productstandard->label_grade_id;
							$arrsForPdtDetails['label_grade_name'] = $productstandard->labelgrade->name;
							//$arrsForPdtDetails['addition_type'] = $productstandard->addition_type;
							$arrsForPdtDetails['pdtListIndex'] = $i;
							

							$appprdarr_details[$productstandard->id]= $arrsForPdtDetails;
							$i++;
							$pdt_index++;
						}
					}
					


					$materialcompositionname = rtrim($materialcompositionname,' + ');
					//$pdt_index_list[$prd->id] = $pdt_index;
					$arrs=array(
						'id'=>$prd->product_id,
						'autoid'=>$prd->id,
						//'pdt_index'=>$pdt_index,
						'name'=>($prd->product?$prd->product->name:''),
						'wastage'=>$prd->wastage,
						'product_type_name' => isset($prd->producttype)?$prd->producttype->name:'',
						'product_type_id'=>isset($prd->producttype)?$prd->producttype->id:'',
						'addition_type' => $prd->product_addition_type,
						//'standard_id'=>$prd->standard_id,
						//'label_grade'=>$prd->label_grade_id,
						//'standard_name' => $prd->standard->name,
						//'label_grade_name' => $prd->standardlabelgrade->name,
						'productStandardList' => $productStandardList,
						'productMaterialList' => $productMaterialList,
						'materialcompositionname' => $materialcompositionname,
					);	
					$appprdarr[] = $arrs;


					
					
					
				}
			}
			$resultarr["products"]=$appprdarr;

			foreach($appprdarr_details as $pdtDetailsDt){
				$resultarr["productDetails"][] = $pdtDetailsDt;
			}
			//$appprdarr_details;
			
			$unitarr=array();
			$unitnamedetailsarr=array();
			//$appUnit=$model->applicationunit;

			if($showonlyNormal){
				$appUnit=$model->applicationunitnormal;
			}else{
				$appUnit=$model->applicationunit;
			}

			$selunitgpsarrlists = [];
			if(count($appUnit)>0)
			{
				foreach($appUnit as $unit)
				{
					if($exclude){
						if(is_array($excludeunits) && count($excludeunits)>0){
							//print_r($excludeunits);
							if( !in_array($unit->id, $excludeunits)){

								continue;
							}
						}
					}
					$statelist = State::find()->alias( 't' )->select(['id','name'])->where(['t.country_id'=>$unit->country_id])->asArray()->all();
					
					$unitarr = $unit->toArray();
					$unitarr["unit_type_name"]=$unit->unit_type_list[$unit->unit_type];
					 
					$unitarr["state_id_name"]=($unit->state_id!="")?$unit->state->name:"";
					$unitarr["country_id_name"]=($unit->country_id!="")?$unit->country->name:"";
					
					$unitarr["state_list"]= $statelist;

					$unitarr["addition_type"]= $unit->unit_addition_type;
					//$unitarr["unit_type"]=$unit->unit_type;
					//'addition_type'=>$prd->product_addition_type,
					$unitnamedetailsarr[$unit->id] = $unit->name;

					

					if($showonlyNormal){
						$unitprd=$unit->unitproductnormal;
					}else{
						$unitprd=$unit->unitproduct;
					}

					if(count($unitprd)>0)
					{
						$unitprdidsarr=array();
						
						foreach($unitprd as $unitP)
						{

							


							$unitprdarr=array();
							//$unitprdarr[]=($unitP->product?$unitP->product->name:'');
							//$unitprdarr['pdt_index']=$pdt_index_list[$unitP->product_id];
							$unitprdarr['pdt_id']=$unitP->application_product_standard_id;
							//$unitprdarr['pdt_index']=($unitP->product?$unitP->product->name:'');

							$unitprdidsarr[]=$unitP->application_product_standard_id;							

							$unitarr["products"][]=$unitprdarr;

							$productdetailsunit = (isset($appprdarr_details[$unitP->application_product_standard_id]) ? $appprdarr_details[$unitP->application_product_standard_id] : '');
							if($productdetailsunit !=''){
								$productdetailsunit['addition_type'] = $unitP->product_addition_type;
							}
							$unitarr["product_details"][]= $productdetailsunit;
							
							
						}
						//pdt_index
						
						
						$unitarr["product_ids"]=$unitprdidsarr;
					}	
					
					//standards
					$unitstdidsarr=array();
					$unitstddetailssarr=array();
					$exitsunitstdidsarr = [];
					

					if($showonlyNormal){
						$unitappstandard=$unit->unitappstandardnormal;
					}else{
						$unitappstandard=$unit->unitappstandard;
					}
					if(count($unitappstandard)>0)
					{
						foreach($unitappstandard as $unitstd)
						{
							$unitstddetailssarrtemp = [];
							$unitstdidsarr[]=$unitstd->standard_id;

							if($unitstd->addition_type==0){
								$exitsunitstdidsarr[]=$unitstd->standard_id;
							}
							
							$unitstddetailssarrtemp['id']=$unitstd->standard_id;
							$unitstddetailssarrtemp['name']=$unitstd->standard->name;

							$unitstddetailssarr[]=$unitstddetailssarrtemp;
						}
					}

					$unitarr["existsstandards"]=$exitsunitstdidsarr;
					$unitarr["standards"]=$unitstdidsarr;
					$unitarr["standarddetails"]=$unitstddetailssarr;
					
					//Business Sector
					$unitbsectoridsarr=array();
					$unitbsarr=array();
					$unitbsarrobj=array();
					$unitbsarrDetails = array();
					$existsunitbsectoridsarr = [];

					
					if($showonlyNormal){
						$unitbsector=$unit->unitbusinesssectornormal;
					}else{
						$unitbsector=$unit->unitbusinesssector;
					}
					if(count($unitbsector)>0)
					{
						
						$arrSectorList = [];
						$unitgpsarr = [];
						$selunitgpsarr = [];
						foreach($unitbsector as $unitbs)
						{
							$business_sector_id = $unitbs->business_sector_id;

							if($model->audit_type == $model->arrEnumAuditType['process_addition']){
								$unitbsectorgp=$unitbs->unitbusinesssectorgroup;
								if(count($unitbsectorgp)>0)
								{	
									$businessectorsgps = [];								
									foreach($unitbsectorgp as $unitbsgp)
									{
										$businessectorsgps[] = $unitbsgp->business_sector_group_id;
									}
								 	$selunitgpsarr[$unit->id]= [
										'sector_id' =>$business_sector_id,
										'business_sector_group_ids' => $businessectorsgps,
									];
								}
							}


							$unitbsectorgps=$unitbs->unitbusinesssectorgroup;
							if(count($unitbsectorgps)>0)
							{	
								$businessectorsgps = [];								
								foreach($unitbsectorgps as $unitbsgps)
								{
									//$businessectorsgps[] = $unitbsgp->business_sector_group_id;
								//}
								 	$selunitgpsarrlists[$unit->id][$business_sector_id][] = [
										'id' =>$unitbsgps->business_sector_group_id,
										'group_code' => $unitbsgps->group->group_code,
									];
								}
							}




							

							$unitbsarr[]=($unitbs->businesssector)?$unitbs->businesssector->name:'';
							$unitbsarrDetails[$business_sector_id]=($unitbs->businesssector)?$unitbs->businesssector->name:'';
							$unitbsectoridsarr[]=$business_sector_id;

							if($unitbs->addition_type==0){
								$existsunitbsectoridsarr[]=$business_sector_id;
							}
							
 
							$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
							$command = $connection->createCommand("SELECT sgp.id,sgp.unit_id,sgp.unit_business_sector_id,GROUP_CONCAT(sgp.business_sector_group_id) as business_sector_group_ids 
							 from tbl_application_unit_business_sector_group as sgp 
							 INNER JOIN tbl_application_unit_business_sector as sec on sec.id = sgp.unit_business_sector_id
							WHERE sec.business_sector_id=".$business_sector_id." AND sgp.unit_id=".$unit->id." AND sec.unit_id=".$unit->id." 
							group by unit_business_sector_id,unit_id");
							$result = $command->queryAll();
							//$sectorgpArr = [];
							
							if(count($result)>0){
								foreach($result as $sectorgroup){
									$unitgpsarr[]= [
										'unit_id' =>$unit->id,
										'sector_id' =>$business_sector_id,
										'business_sector_group_ids' => explode(',',$sectorgroup['business_sector_group_ids']),
									];
								}
							}
							
							//$model->status == $model->arrEnumStatus['submitted']
							if($model->status == $model->arrEnumStatus['submitted'])
							{
								$command = $connection->createCommand("SELECT sgp.id,sgp.group_code from tbl_business_sector_group as sgp
										WHERE business_sector_id=".$business_sector_id." AND standard_id IN(".implode(',',$unitarr["standards"]).") ");
								$result = $command->queryAll();
								//$sectorgpArr = [];
								if(count($result)>0){
									foreach($result as $sectorgroup){
										 
										$arrSectorList[$business_sector_id][] =[
												'id'=>$sectorgroup['id'],
												'group_code'=>$sectorgroup['group_code']
												
											];

									}
								}
								 

							}
							
						}
						$unitarr["bsectorsselgroup"]=$unitgpsarr;
						
						$unitarr["bsectorsusers"]=$arrSectorList;

						$unitarr["bsectorsgroupselected"]=$selunitgpsarr;
						
						//print_r($unitbsectoridsarr); die;
						$stds='';
						foreach($unitarr["standards"] as $value)
						{
							$stds.=$value.",";
						}
						$std_ids=substr($stds, 0, -1);
						
						$command = $connection->createCommand("SELECT bs.id,bs.name FROM tbl_business_sector AS bs INNER JOIN tbl_business_sector_group AS bsg ON bs.id=bsg.business_sector_id AND bsg.standard_id IN (".$std_ids.") GROUP BY bs.id");
						$result = $command->queryAll();
						if(count($result)>0)
						{
							foreach($result as $vals)
							{
								$values=array();
								$values['id'] = $vals['id'];
								$values['name'] = $vals['name'];
								$unitbsarrobj[]=$values;
							}

						}
					}
					
					$unitarr["bsectorsdetails"]=$unitbsarrDetails;
					$unitarr["bsectors"]=$unitbsarr;
					$unitarr["bsector_ids"]=$unitbsectoridsarr;
					$unitarr["bsector_data"]=$unitbsarrobj;
					$unitarr["existsbsector_ids"]=$existsunitbsectoridsarr;
					
					$unitarr["selunitgpsarrlists"]=$selunitgpsarrlists;
					
					$unitprocess_data=[];
					$unitprocessnames=[];
					$unitpcsarr=array();
					$unitpcsarrobj=array();
					$existsunitprocess_data = [];
					

					if($showonlyNormal){
						$unitprocess=$unit->unitprocessnormal;
					}else{
						$unitprocess=$unit->unitprocess;
					}


					$newunitpcsarr = [];
					if(count($unitprocess)>0)
					{
						
						$icnt=0;
						foreach($unitprocess as $unitPcs)
						{
							if($model->audit_type == 3){
								if($unitPcs->process_type =='1'){
									$newunitpcsarr[]=$unitPcs->process->name;
								}else{
									$unitpcsarr=array();
									$unitpcsarr['id']=$unitPcs->process_id;
									$unitpcsarr['name']=$unitPcs->process->name;
									$unitprocess_data[]=$unitpcsarr;
									$unitprocessnames[]=$unitPcs->process->name;
								}
							}else{
								$unitpcsarr=array();
								$unitpcsarr['id']=$unitPcs->process_id;
								$unitpcsarr['name']=$unitPcs->process->name;
								$unitpcsarr['addition_type']=$unitPcs->process_type;
								$unitprocess_data[]=$unitpcsarr;
								$unitprocessnames[]=$unitPcs->process->name;
								if($unitPcs->process_type==0){
									$existsunitprocess_data[]=$unitpcsarr;
								}
								
							}
							
							

							$icnt++;
						}

						$bsector_ids='';
						foreach($unitbsectoridsarr as $value)
						{
							$bsector_ids.=$value.",";
						}
						$bsector_ids=substr($bsector_ids, 0, -1);
 
					}
					
					$unitarr["new_process"]=$newunitpcsarr;
					$unitarr["process"]=$unitprocessnames;
					$unitarr["process_ids"]=$unitprocess_data;
					$unitarr["process_data"]=$unitpcsarrobj;
					$unitarr["existsprocess_ids"]=$existsunitprocess_data;


					$unitstd=$unit->unitstandard;
					unset($unitarr["certified_standard"]);
					$certstdarr= [];
					if(count($unitstd)>0)
					{
						
						foreach($unitstd as $unitS)
						{
							$unitstdfilearr=[];
							$standardfile=$unitS->unitstandardfile;
							if(count($standardfile)>0)
							{
								
								foreach($standardfile as $stdfile)
								{
									$unitstdfile = [];
									
									$unitstdfile['id']=$stdfile->id;
									$unitstdfile['name']=$stdfile->file;
									$unitstdfile['type']=$stdfile->type;
									$unitstdfilearr[]= $unitstdfile;
								}
								//$unitstdfilearr[]=$stdfile->file;
							}
							
							$certstdarr[]=array("id"=>$unitS->standard_id,"standard"=>($unitS->standard?$unitS->standard->name:''),"files"=>$unitstdfilearr);
						}
						$unitarr["certified_standard"]=$certstdarr;
					}

					$unitdetailsarr[]=$unitarr;
				}
				$resultarr["units"]=$unitdetailsarr;
			}
			
			$applicationchecklistcmt=[];
			$appchecklistcmt=$model->applicationchecklistcmt;
			if(count($appchecklistcmt)>0)
			{
				$checklistcmtarr=[];
				foreach($appchecklistcmt as $checklistcmt)
				{
					$checklistcmtarr[]=array('id'=>$checklistcmt->id,'question'=>$checklistcmt->question,'answer'=>$checklistcmt->answer!='1'?"No":"Yes",'comment'=>$checklistcmt->comment,'document'=>$checklistcmt->document);
				}
				$resultarr["applicationchecklistcmt"]=$checklistcmtarr;
			}

			$applicationreviews=[];
			$reviewarr=[];
			$reviewcommentarr=[];
			$appReview=$model->applicationreview;
			if(count($appReview)>0)
			{
				foreach($appReview as $review)
				{
					$reviewarr=[];
					$reviewcommentarr=[];
					$applicationreviewcmt=$review->applicationreviewcomment;
					if(count($applicationreviewcmt)>0)
					{
						foreach($applicationreviewcmt as $reviewComment)
						{
							$reviewcommentarr[]=array('question'=>$reviewComment->question,'answer'=>$reviewComment->arrAnswer[$reviewComment->answer],'comment'=>$reviewComment->comment);
						}	
					}
					



					$unitreviews=[];
					$unitreviewarr=[];
					$unitreviewcommentarr=[];
					$unitapplicationreviewcmt=$review->applicationunitreviewcomment;
					if(count($unitapplicationreviewcmt)>0)
					{
						foreach($unitapplicationreviewcmt as $unitreviewComment)
						{
							$unitreviewcommentarr[$unitreviewComment->unit_id][] = array(
									'question'=>$unitreviewComment->question
									,'answer'=>$unitreviewComment->arrAnswer[$unitreviewComment->answer]
									,'comment'=>$unitreviewComment->comment
								);
									
									
						}	
						//print_r($unitreviewcommentarr); die;
						foreach($unitreviewcommentarr as $unitkey => $units){
							if(isset($unitnamedetailsarr[$unitkey])){
								$unitreviews[]=array('unit_name'=>$unitnamedetailsarr[$unitkey],'unit_id'=>$unitkey,'reviews'=>$units);
							}
						}
						 
					}
					
					
					$reviewarr['reviewcomments']=$reviewcommentarr;
					$reviewarr['unitreviewcomments']=$unitreviews;
					$reviewarr['reviewer']=($review->reviewer?$review->reviewer->first_name.' '.$review->reviewer->last_name:'');
					$reviewarr['answer']=$review->answer;
					
					$reviewarr['answer_name']=$review->answer?$review->arrReviewAnswer[$review->answer]:'NA';
					
					$reviewarr['status']=$review->status;		
					$reviewarr['status_name']=$review->arrReviewStatus[$review->status];					
															
					$reviewarr['created_at']=date($date_format,$review->created_at);
					$reviewarr['updated_at']=($review->status==2 || $review->status==3)?date($date_format,$review->updated_at):'NA';
					$reviewarr['status_comments']=$review->comment;
					$reviewarr['review_result_name']=isset($review->arrReviewResult[$review->review_result])?$review->arrReviewResult[$review->review_result]:'';
					$reviewarr['review_result']=$review->review_result;

					$reviewarr['reviewcomments']=$reviewcommentarr;

					$applicationreviews[]=$reviewarr;
				}
				$resultarr["applicationreviews"]=$applicationreviews;
			}	


			$approvalcommentarr=[];
			$appApproval=$model->applicationapproval;
			if(count($appApproval)>0)
			{
				foreach($appApproval as $approval){
					$reviewarr=[];
					$reviewarr['comment']=$approval->comment;
					$reviewarr['status']=$approval->status;
					$reviewarr['status_name']=$approval->statusarr[$approval->status];
					$reviewarr['created_at']=date($date_format,$approval->created_at);
					$reviewarr['updated_at']=date($date_format,$approval->updated_at);
					$reviewarr['user_id']=$approval->user_id;
					$reviewarr['approver_name']=$approval->username->first_name.' '.$approval->username->last_name;
					$approvalcommentarr[] = $reviewarr;
				}
			}
			$resultarr["applicationapprovals"]=$approvalcommentarr;


			$resultarr['hasapprover'] = 0;
			$model = ApplicationApprover::find()->where(['app_id' => $data['id'],'approver_status'=>1])->one();
			if ($model !== null)
			{
				$resultarr['hasapprover'] = 1;
				$resultarr['approverid'] = $model->user_id;
			}
			$model = ApplicationReviewer::find()->where(['app_id' => $data['id'],'reviewer_status'=>1])->one();
			if ($model !== null)
			{
				$resultarr['hasreviewer'] = 1;
				$resultarr['reviewerid'] = $model->user_id;
			}
			$materialmodel = new ProductTypeMaterialComposition();
			$resultarr['material_type'] = $materialmodel->material_type;


			$applicationobj =  new Application();
			$resultarr['arrEnumStatus'] = $applicationobj->arrEnumStatus;

			$appapprovalobj = new ApplicationApproval();
			$approvalStatusList = [];
			foreach($appapprovalobj->approvestatusarr as $key => $statusname){
				$approvalStatusList[] = ['id'=>$key,'name'=>$statusname];
			}
			$resultarr['approvalStatusList'] = $approvalStatusList;

			return $resultarr;
			
			
			
		}
	}
	*/

	public function actionRenewal()
	{
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		
		$modelApp = new Application();	
		
		$arrUnitIDs=array();
		
		$data['id']=$data['app_id'];
		if($data)
		{
			$appModel = Application::find()->where(['id' => $data['app_id']])->one();
			if($appModel!==null)
			{
				//$ApplicationRenewal = ApplicationRenewal::find()->where(['app_id' =>$appModel->id,'user_id'=>$appModel->customer_id])->orderBy(['id' => SORT_DESC])->one();


				//ApplicationRenewal::deleteAll(['app_id' =>$appModel->id,'user_id'=>$appModel->customer_id]);
				//if($ApplicationRenewal !== null){
				//	ApplicationRenewalStandard::deleteAll(['app_renewal_id' =>$ApplicationRenewal->id]);
				//}
				
				
				$ApplicationRenewalModel = new ApplicationRenewal();
				$ApplicationRenewalModel->created_by = $userData['userid'];
				$ApplicationRenewalModel->app_id = $appModel->id;
				$ApplicationRenewalModel->user_id = $appModel->customer_id;
				$ApplicationRenewalModel->change_status = $data['type'];
				if($ApplicationRenewalModel->validate() && $ApplicationRenewalModel->save())
				{
					$renewalStandardIds = [];
					if(isset($data['addition_standard']) && is_array($data['addition_standard']) && count($data['addition_standard'])>0){
						foreach($data['addition_standard'] as $standardid){
							$ApplicationRenewalStandard = new ApplicationRenewalStandard();
							$ApplicationRenewalStandard->app_renewal_id = $ApplicationRenewalModel->id;
							$ApplicationRenewalStandard->standard_id = $standardid;
							//$ApplicationRenewalStandard->version = 
							$ApplicationRenewalStandard->standard_addition_type = 1;
							$ApplicationRenewalStandard->save();
							$renewalStandardIds[$standardid] = $standardid;
						}
						
					}
					//,'standard_addition_type'=>0
					$ApplicationStandard = ApplicationStandard::find()->where(['app_id'=>$appModel->id,'standard_status'=>[0,8,5]])->all();
					if(count($ApplicationStandard)>0){
						foreach($ApplicationStandard as $appstd){
							$ApplicationRenewalStandard = new ApplicationRenewalStandard();
							$ApplicationRenewalStandard->app_renewal_id = $ApplicationRenewalModel->id;
							$ApplicationRenewalStandard->standard_id = $appstd->standard_id;
							//$ApplicationRenewalStandard->version = 
							$ApplicationRenewalStandard->standard_addition_type = 0;
							$ApplicationRenewalStandard->save();
							$renewalStandardIds[$appstd->standard_id] = $appstd->standard_id;
						}
					}
					if($ApplicationRenewalModel->change_status==1)
					{
						$data['audit_type']=$modelApp->arrEnumAuditType['renewal'];

						$data['renewal_standard_ids']=$renewalStandardIds;

						$arrUnitIDs=$modelApp->cloneApplication($data);	
						$responsedata =array('status'=>1,'type'=>$ApplicationRenewalModel->change_status,'renewal_id'=>$ApplicationRenewalModel->id,'message'=>"GCL Team will generate Quotation and send it to you ASAP.");															
					}else{
						$responsedata =array('status'=>1,'type'=>$ApplicationRenewalModel->change_status,'renewal_id'=>$ApplicationRenewalModel->id,'message'=>"Something went wrong! Please try again later");
					}				
				}
			}		
		}
		return 	$responsedata;	
	}

	public function actionApplicationchecklist(){
		$data = Yii::$app->request->post();

		$applicationquestionmodel = new ApplicationQuestions();
		$applicationquestion = ApplicationQuestions::find()->where(['status'=>0])->alias('t');
		/*if(isset($data['app_id']) && $data['app_id']>0){
			$applicationquestion = $applicationquestion->join('left join', 'tbl_application_checklist_comment as app_checklist','app_checklist.question_id =t.id ');
		}
		*/
		$applicationquestion = $applicationquestion->all();
		$questionlist=[];
		if(count($applicationquestion)>0){
			foreach($applicationquestion as $question){
				$answer = '';
				$comment = '';
				$document = '';
				if(isset($data['app_id']) && $data['app_id']>0){
					$appchklistcomment = ApplicationChecklistComment::find()->where(['app_id'=>$data['app_id'],'question_id'=>$question->id])->one();
					if($appchklistcomment !==null){
						$answer = $appchklistcomment->answer;
						$comment = $appchklistcomment->comment;
						$document = $appchklistcomment->document;
					}
				}
				$questionlist[] = [
					'id' => $question->id,
					'name' => $question->name,
					'guidance' => $question->guidance,
					'file_upload_required' => $question->file_upload_required,
					'answer' => $answer,
					'comment' => $comment,
					'document' => $document
				];
			}
		}
		return ['appchecklist'=>$questionlist,'answerArr'=>$applicationquestionmodel->arrAnswer];
	}

	public function actionBusinessectorusers(){


		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');

		if (Yii::$app->request->post()) 
		{
			$connection = Yii::$app->getDb();

			$data = Yii::$app->request->post();
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];


			$usergroupcode = new UserBusinessGroupCode();
			$usermodel = new User();
			//$business_sector_id = $data["business_sector_id"];
			//$standards = $data["standards"];
			//$unit_id = $data["unit_id"];
			
			$franchise_id = $data["franchise_id"];
			$bsectorgp_id = $data["bsectorgp_id"];

			if(count($bsectorgp_id)>0)
			{
				$command = $connection->createCommand("SELECT sgp.id,sgp.group_code from tbl_business_sector_group as sgp ".
													"WHERE sgp.id in (".implode(',',$bsectorgp_id).") ");
				//business_sector_id=".$business_sector_id." AND standard_id IN(".implode(',',$standards).")
				$result = $command->queryAll();
				if(count($result)>0){
					foreach($result as $sectorgroup){
							
						//For getting Auditors
						$franchiseCondition = ' AND user_role.franchise_id= '.$franchise_id.' ';
						//$sectorgpcondition = " and usrsectorgroupcode.business_sector_group_id =".$sectorgroup['id']." and usrsectorgroupcode.status = ".$usergroupcode->arrEnumStatus['approved']." ";
						//$stdcondition = " and usrstd.standard_id in(".implode(',',$standards).")";
						$sectorgpcondition = " and usrsectorgroupcode.business_sector_group_id =".$sectorgroup['id']." and usrgroupcode.status = ".$usergroupcode->arrEnumStatus['approved']." ";

						$command = $connection->createCommand("SELECT user.id,first_name ,last_name 
							FROM tbl_users as user 
							inner join tbl_user_role as user_role on  user_role.user_id = user.id 
							INNER JOIN `tbl_rule` AS rule ON  user_role.role_id=rule.role_id AND rule.privilege='audit_execution' 
							INNER JOIN `tbl_user_role_business_group` AS usrsectorgroup on usrsectorgroup.user_id = user.id  AND usrsectorgroup.role_id = rule.role_id 
							INNER JOIN `tbl_user_role_business_group_code` AS usrsectorgroupcode on usrsectorgroup.id = usrsectorgroupcode.business_group_id 
							INNER JOIN `tbl_user_business_group_code` AS usrgroupcode on usrgroupcode.id = usrsectorgroupcode.user_business_group_code_id 
							where user_role.approval_status=2 AND user_type=1 and user.status='".$usermodel->arrLoginEnumStatus['active']."'  ".$franchiseCondition." ".$sectorgpcondition." group by user.id");
						$result = $command->queryAll();
						$usersListArr = [];
						if(count($result)>0){
							foreach($result as $userdata){
								$usersListArr[] = $userdata['first_name'].' '.$userdata['last_name'];
							}
						}
						

						//For getting Technical Experts
						/*
						$technicalcommand = $connection->createCommand("SELECT user.id,first_name ,last_name  FROM tbl_users as user 
						inner join tbl_user_role as user_role on  user_role.user_id = user.id 
						inner join tbl_role as role on  role.id = user_role.role_id  and role.resource_access=3  
						INNER JOIN `tbl_user_business_group` AS usrsectorgroup on usrsectorgroup.user_id = user.id  
						INNER JOIN `tbl_user_business_group_code` AS usrsectorgroupcode on usrsectorgroup.id = usrsectorgroupcode.business_group_id 
								where user.user_type=1 and user.status='".$usermodel->arrLoginEnumStatus['active']."'  ".$franchiseCondition." ".$sectorgpcondition." group by user.id");
						*/

						$sectorgpcondition = " and usrsectorgroupcode.business_sector_group_id =".$sectorgroup['id']." and usrsectorgroupcode.status = ".$usergroupcode->arrEnumStatus['approved']." ";
						$technicalcommand = $connection->createCommand("SELECT user.id,first_name ,last_name  FROM tbl_users as user 
						inner join tbl_user_role as user_role on  user_role.user_id = user.id 
						inner join tbl_role as role on  role.id = user_role.role_id  and role.resource_access=3  
						INNER JOIN `tbl_user_role_technical_expert_business_group` AS usrsectorgroup on usrsectorgroup.user_id = user.id  
						INNER JOIN `tbl_user_role_technical_expert_business_group_code` AS usrsectorgroupcode on usrsectorgroup.id = usrsectorgroupcode.user_role_technical_expert_bs_id 
								where user_role.approval_status=2 AND user.user_type=1 and user.status='".$usermodel->arrLoginEnumStatus['active']."'  ".$franchiseCondition." ".$sectorgpcondition." group by user.id");
						$technicalresult = $technicalcommand->queryAll();
						$technicalListArr = [];
						if(count($technicalresult)>0){
							foreach($technicalresult as $technicaldata){
								$technicalListArr[] =$technicaldata['first_name'].' '.$technicaldata['last_name'];
							}
						}
						//$userslist = array_unique(array_merge($usersListArr,$technicalListArr));

						$usersfound = 1;
						if(count($usersListArr)<=0){
							$userslist = ['No Users Found'];
							$usersfound = 0;
						}else{
							$userslist = array_unique($usersListArr);
						}
						$technicalfound = 1;
						if(count($technicalListArr)<=0){
							$technicalslist = ['No Users Found'];
							$technicalfound = 0;
						}else{
							$technicalslist = array_unique($technicalListArr); 
						}
						$arrSectorList[] =[
								'id'=>$sectorgroup['id'],
								'group_code'=>$sectorgroup['group_code'],
								'usersArr'=>$userslist,
								'technicalexpertArr'=>$technicalslist,
								'technicalexpert'=>implode(', ', $technicalslist),
								'users'=>implode(', ', $userslist),
								'usersfound' => $usersfound
							];

						






					}

					$responsedata=array('status'=>1,'data'=>$arrSectorList);
				}
			}else{
				$responsedata=array('status'=>1,'data'=>[]);
			}
		}
		return $responsedata;

	}


	public function actionLatestEnquiry()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');

		if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			//'customer_id' => $userid
			$enqmodel = Enquiry::find()->where(['id'=>$data['enquiry_id']])->orderBy(['id' => SORT_DESC])->one();


			if($enqmodel !== null)
			{
				$resultarr=array();
				$resultarr["company_name"]=$enqmodel->company_name;
				$resultarr["company_address1"]=$enqmodel->company_address1;
				//$resultarr["company_address2"]=$enqmodel->company_address2;
				
				$resultarr["company_city"]=$enqmodel->company_city;
				$resultarr["company_zipcode"]=$enqmodel->company_zipcode;
				$resultarr["company_country_id"]=$enqmodel->company_country_id;
				$resultarr["company_state_id"]=$enqmodel->company_state_id;
				$resultarr["first_name"]=$enqmodel->first_name;
				$resultarr["last_name"]=$enqmodel->last_name;
				$resultarr["email"]=$enqmodel->email;
				$resultarr["telephone"]=$enqmodel->telephone;
				
				
				
				$arrstandardids=[];
				$enqStandard=$enqmodel->enquirystandard;
				if(count($enqStandard)>0)
				{
					foreach($enqStandard as $std)
					{
						$arrstandardids[]=$std->standard_id;
					}
					$resultarr["standards"]=$arrstandardids;
				}
				

				return $resultarr;
			}
		}
	}

	public function actionQuestionView()
	{
		$data = Yii::$app->request->post();
		$model = Application::find()->where(['id' => $data['id']])->one();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$arrReviewerStatus=array();
		$applicationReviewModel = new ApplicationReview();
		$arrReviewerStatus=$applicationReviewModel->arrReviewerStatus;
				
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
						
		if($resource_access != 1){
			if($user_type== 1 && ! in_array('application_review',$rules) ){
				return $responsedata;
			}else if($user_type==3  || $user_type==2){
				return $responsedata;
			}
			/*else if($user_type==3 && $role!=0 && ! in_array('application_review',$rules) ){
				return $responsedata;
			}else if($user_type==2){
				return $responsedata;
			}*/
		}

		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		
		if ($model !== null)
		{
			$resultarr=array();

			if($model->audit_type == $model->arrEnumAuditType['unit_addition'] ||
			$model->audit_type == $model->arrEnumAuditType['process_addition'] ){
				unset($arrReviewerStatus['3']); 
			}
			$resultarr["id"]=$model->id;
			$resultarr["code"]=$model->code;
			$resultarr['created_at']=date($date_format,$model->created_at);
			$resultarr["company_name"]=$model->companyname;
			$resultarr["address"]=$model->address;
			$resultarr["zipcode"]=$model->zipcode;
			$resultarr["city"]=$model->city;
			$resultarr["salutation"]=($model->salutation!="")?$model->salutation:"";
			$resultarr["salutation_name"]=($model->salutation!="")?$model->arrSalutation[$model->salutation]:"";
			
			$resultarr["title"]=($model->title!="")?$model->title:"";
			$resultarr["first_name"]=($model->firstname!="")?$model->firstname:"";
			$resultarr["last_name"]=($model->lastname!="")?$model->lastname:"";
			$resultarr["job_title"]=($model->jobtitle!="")?$model->jobtitle:"";
			$resultarr["telephone"]=($model->telephone!="")?$model->telephone:"";
			$resultarr["email_address"]=($model->emailaddress!="")?$model->emailaddress:"";
						
			$resultarr["state_id_name"]=($model->statename!="")?$model->statename:"";
			$resultarr["country_id_name"]=($model->countryname!="")?$model->countryname:"";
			$resultarr["state_id"]=($model->applicationaddress->state_id!="")?$model->applicationaddress->state_id:"";
			$resultarr["country_id"]=($model->applicationaddress->country_id!="")?$model->applicationaddress->country_id:"";
			$resultarr["created_by"]=($model->created_by!="")?$model->username->first_name.' '.$model->username->last_name:"";
			$resultarr["certification_status"]=$model->certification_status;
			//$resultarr["preferred_partner_id"]=$model->preferred_partner_id;
			//$resultarr["preferred_partner_id_name"]=($model->preferredpartner?$model->preferredpartner->name:'');
			
			$resultarr["app_status"]=$model->status;
			$resultarr["status"]=$model->arrStatus[$model->status];
			
			$appstdarr=[];
			$arrstandardids=[];
			$appStandard=$model->applicationstandard;
			if(count($appStandard)>0)
			{
				foreach($appStandard as $std)
				{
					$appstdarr[]=($std->standard?$std->standard->name:'');	
					$arrstandardids[]=$std->standard_id;
				}
			}
			$resultarr["standards"]=$appstdarr;
			$resultarr["standard_ids"]=$arrstandardids;
			
			$appprdarr=[];
			$appProduct=$model->applicationproduct;
			if(count($appProduct)>0)
			{
				foreach($appProduct as $prd)
				{
					$appprdarr[]=array(
						'id'=>$prd->product_id,'name'=>($prd->product?$prd->product->name:''),'wastage'=>$prd->wastage
						,'product_type_name' => 'type name'
						,'productStandardList' => [
							['id'=>1,'name'=>'GOTS','label_grade'=>1,'label_grade_name'=>'Organic'],
							['id'=>2,'name'=>'GOTS2','label_grade'=>2,'label_grade_name'=>'Organic 100']
							]
						,'material_composition' => '95% organic'

						,'product_type_id'=>2
					);
				}
			}
			$resultarr["products"]=$appprdarr;

			$unitarr=array();
			$appUnit=$model->applicationunit;
			if(count($appUnit)>0)
			{
				foreach($appUnit as $unit)
				{
					//$statelist = State::find()->alias( 't' )->select(['id','name'])->where(['t.country_id'=>$unit->country_id])->asArray()->all();
					$unitarr["name"]=$unit->name;
					$unitarr["id"]=$unit->id;
					$unitarr["address"]=$unit->address;
					$unitarr["zipcode"]=$unit->zipcode;
					$unitarr["city"]=$unit->city;
					$unitarr["state_id"]=($unit->state_id!="")?$unit->state_id:"";
					$unitarr["country_id"]=($unit->country_id!="")?$unit->country_id:"";
					$unitarr["state_id_name"]=($unit->state_id!="")?$unit->state->name:"";
					$unitarr["country_id_name"]=($unit->country_id!="")?$unit->country->name:"";
					$unitarr["no_of_employees"]=$unit->no_of_employees;
					//$unitarr["state_list"]= $statelist;
					

					$unitprd=$unit->unitproduct;
					if(count($unitprd)>0)
					{
						$unitprdidsarr=array();
						$unitprdarr=array();
						foreach($unitprd as $unitP)
						{
							//$unitprdarr[]=($unitP->product?$unitP->product->name:'');
							//$unitprdidsarr[]=$unitP->application_product_standard_id;							
						}
						$unitarr["products"]=$unitprdarr;
						$unitarr["product_ids"]=$unitprdidsarr;
					}		
					
					$unitprocessids=[];
					$unitpcsarr=[];
					$unitpcsarrobj=[];
					$unitprocess=$unit->unitprocess;
					if(count($unitprocess)>0)
					{
						$unitpcsarr=array();
						$unitpcsarrobj=array();
						$icnt=0;
						foreach($unitprocess as $unitPcs)
						{
							$unitpcsarr[]=$unitPcs->process_name;//$unitPcs->process->name;
							$unitprocessids[]=$unitPcs->process_id;

							$unitpcsarrobj[$icnt]['id']=$unitPcs->process->id;
							$unitpcsarrobj[$icnt]['name']=$unitPcs->process_name;//$unitPcs->process->name;
							$icnt++;
						}						
					}
					$unitarr["process"]=$unitpcsarr;
					$unitarr["process_ids"]=$unitprocessids;
					$unitarr["process_data"]=$unitpcsarrobj;
					

					$unitdetailsarr[]=$unitarr;
				}
				$resultarr["units"]=$unitdetailsarr;
			}	
			$status =[];	
			asort($arrReviewerStatus);	
			foreach($arrReviewerStatus as $key => $status){
				$status = ['id'=>$key,'status'=>$status];
				$resultarr['reviewerstatus'][]= $status;
				//$resultarr["reviewerstatus"]=;
			}
				
			return $resultarr;
			
			
			
		}


	}

	public function actionSubmitforreview()
    {
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again later');
        if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$is_headquarters =$userData['is_headquarters'];
			$franchiseid=$userData['franchiseid'];
			
			$ApplicationModel = new Application();
			
			$model = Application::find()->where(['id'=>$data['app_id'],'status'=>$ApplicationModel->arrEnumStatus['submitted'] ])->one();
			if($model!==null)
			{

				//(applicationdata?.app_status==arrEnumStatus['submitted'])  && (userdetails.resource_access==1 || userType==3 || (userType==1 && userdetails.rules.includes('submit_for_review')) )
				if(Yii::$app->userrole->isOSS() && $is_headquarters!=1)
				{
					if($resource_access ==5){
						if($model->franchise_id != $franchiseid ){
							return false;
						}
					}else{
						if($model->franchise_id != $userid ){
							return false;
						}
					}
				}else if(!Yii::$app->userrole->hasRights(array('submit_for_review'))){
					return false;
				}else if(Yii::$app->userrole->hasRights(array('submit_for_review')) && Yii::$app->userrole->isOSSUser()){
					if($model->franchise_id != $franchiseid ){
						return false;
					}
				}


				
				$applicationunit = $model->applicationunit;
				if(count($applicationunit)>0){
					foreach($applicationunit as $appunit){
						ApplicationUnitBusinessSectorGroup::deleteAll(['unit_id' =>$appunit->id ]);
					}					
				}
				


				$model->updated_by = $userData['userid'];
				if($model->save()){

					$bsectrogpData = $data['bsectrogpData'];
					foreach($bsectrogpData as $gpdata){
						$sector_id = $gpdata['sectorid'];
						$unit_id = $gpdata['unit_id'];
						$sectorgroup_ids = $gpdata['sectorgroup_ids'];
						$app_bsector = ApplicationUnitBusinessSector::find()->where(['unit_id'=>$unit_id,'business_sector_id'=>$sector_id])->one();
						$app_bsector_id = $app_bsector->id;
						ApplicationUnitBusinessSectorGroup::deleteAll(['unit_id' =>$unit_id,'unit_business_sector_id'=>$app_bsector_id]);
						if($app_bsector !== null){
							foreach($sectorgroup_ids as $gpid){
								$sectorgp = new ApplicationUnitBusinessSectorGroup();
								$sectorgp->unit_id = $unit_id;
								$sectorgp->unit_business_sector_id = $app_bsector_id;
								$sectorgp->business_sector_group_id = $gpid;
								$BusinessSectorGroup = BusinessSectorGroup::find()->where(['id'=>$gpid])->one();
								if($BusinessSectorGroup!== null){
									$sectorgp->business_sector_group_name = $BusinessSectorGroup->group_code;
									$sectorgp->standard_id = $BusinessSectorGroup->standard_id;
								}								
								$sectorgp->save();
							
							}
						}
					}
					
					

					$appreviewmodel = ApplicationReview::find()->where(['app_id' => $data['app_id']])->orderBy(['id' => SORT_DESC])->one();
					$reviewermodel = ApplicationReviewer::find()->where(['app_id' => $data['app_id'],'reviewer_status'=>1])->one();
					
					if($appreviewmodel !==null && $reviewermodel!==null && $appreviewmodel->user_id == $reviewermodel->user_id && $appreviewmodel->answer==null && $appreviewmodel->comment==null && $appreviewmodel->review_result==0){
						$model->status = $model->arrEnumStatus['waiting_for_review'];
						$model->save();
						
					}else if($appreviewmodel !==null && $reviewermodel!==null){
						$reviewobjmodel=new ApplicationReview();
						$reviewobjmodel->app_id=isset($data['app_id'])?$data['app_id']:"";
						$reviewobjmodel->user_id=$reviewermodel->user_id;
						$reviewobjmodel->status=$reviewobjmodel->arrEnumReviewStatus['review_in_process'];
						$reviewobjmodel->save();

						$model->status = $model->arrEnumStatus['review_in_process'];
						

						Yii::$app->globalfuns->updateApplicationStatus($data['app_id'],$model->status,$model->audit_type);
					}else{
						/*
						$reviewmodel=new ApplicationReview();
						$reviewmodel->app_id=isset($data['app_id'])?$data['app_id']:"";
						$reviewmodel->save();
						*/
						$model->status = $model->arrEnumStatus['waiting_for_review'];
						$model->save();
						Yii::$app->globalfuns->updateApplicationStatus($data['app_id'],$model->status,$model->audit_type);
						$headquaters = User::find()->select('id')->where(['headquarters'=>1])->one();
						if($headquaters !== null)
						{
							$result = Yii::$app->globalfuns->getPrivilegeUser($headquaters['id'],'application_review');
							foreach($result as $value)
							{
								$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'app_submitted_for_review'])->one();
								if($mailContent !== null )
								{
									$mailmsg = str_replace('{COMPANY-NAME}', $model->companyname, $mailContent['message'] );
									$mailmsg = str_replace('{COMPANY-EMAIL}', $model->emailaddress, $mailmsg );
									$mailmsg = str_replace('{COMPANY-TELEPHONE}', $model->telephone, $mailmsg );
									$mailmsg = str_replace('{CONTACT-NAME}', $model->contactname, $mailmsg );
									
									
									$MailLookupModel = new MailLookup();
									$MailLookupModel->to=$value['email'];									
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
					$model->save();

					$responsedata=array('status'=>1,'message'=>'Application was submmited for review successfully','app_status'=>$model->status,'app_status_name'=>$model->arrStatus[$model->status]);
				}
			}
		}
		return $this->asJson($responsedata);
	}

	public function actionListApplicationStatus()
    {
		$application = new Application();
		return ['statuslist'=>$application->arrStatus,'enumstatus'=>$application->arrEnumStatus];
	}
	
	public function actionListApplicationType()
    {
		$application = new Application();
		return ['typelist'=>$application->arrAuditType,'enumtype'=>$application->arrEnumAuditType];
	}

	public function actionCertificationfile(){
		$data = Yii::$app->request->post();
		//$data['id'];
		$files = ApplicationUnitCertifiedStandardFile::find()->where(['id'=>$data['id']])->one();

		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['certification_standard_files'].$files->file;
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

	public function actionChecklistfile(){
		$data = Yii::$app->request->post();
		//$data['id'];
		$files = ApplicationChecklistComment::find()->where(['id'=>$data['id']])->one();

		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['application_checklist_file'].$files->document;
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


	public function actionCertifiedfile(){
		$data = Yii::$app->request->post();
		//$data['id'];
		$files = ApplicationCertifiedByOtherCB::find()->where(['id'=>$data['id']])->one();

		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['certifiedbyothercb_file'].$files->certification_file;
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

	public function actionApplicationfile(){
		$data = Yii::$app->request->post();
		//$data['id'];
		$files = Application::find()->where(['id'=>$data['id']])->one();

		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['company_files'].$files->company_file;
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


	public function actionOspreject()
    {   	
		//die;
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		
		$data = Yii::$app->request->post();
		//print_r($data); die;
		//$data = json_decode($datapost['formvalues'],true);
		
		$modelapp = new Application();
		//$target_dir = Yii::$app->params['certification_standard_files']; 

		$model = Application::find()->where(['id' => $data['app_id'],'status'=>$modelapp->arrEnumStatus['submitted'] ]);
		
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];
		$model = $model->one();


		//(applicationdata?.app_status==arrEnumStatus['submitted'])  && (userdetails.resource_access==1 || userType==3 || (userType==1 && userdetails.rules.includes('reject')) )
		if($model !== null){
			if(Yii::$app->userrole->isOSS() && $is_headquarters!=1)
			{
				if($resource_access ==5){
					if($model->franchise_id != $franchiseid ){
						return false;
					}
				}else{
					if($model->franchise_id != $userid ){
						return false;
					}
				}
			}else if(!Yii::$app->userrole->hasRights(array('osp_reject'))){
				return false;
			}else if(Yii::$app->userrole->hasRights(array('osp_reject')) && Yii::$app->userrole->isOSSUser()){
				if($model->franchise_id != $franchiseid ){
					return false;
				}
			}


			$model->status = $model->arrEnumStatus['osp_reject'];
			//$model->overall_status = $model->arrEnumOverallStatus['osp_reject'];
			$model->reject_comment = $data['reject_comment'];
			$model->rejected_by = $userid;
			$model->rejected_role_id = $role;

			$model->rejected_date = date('Y-m-d',time());
			$model->save();

			Yii::$app->globalfuns->updateApplicationOverallStatus($model->id, $model->arrEnumOverallStatus['application_rejected']);
			Yii::$app->globalfuns->updateApplicationStatus($model->id,$model->status,$model->audit_type);

			$responsedata =array('status'=>1,'message'=>"Application was rejected",'app_status'=>$model->status,'app_status_name'=>$model->arrStatus[$model->status]);
		}
		return $responsedata;

	}
	
	public function actionGetUnit()
	{
		$arrAppUnit=array();
		$data = Yii::$app->request->post();
		if($data)
		{
			$auditObj = Audit::find()->where(['id' => $data['auditid']])->one();
			if($auditObj!==null)
			{
				$appObj = $auditObj->application;
				if($appObj)
				{
					$appUnitObj = $appObj->applicationunit;
					if(count($appUnitObj)>0)
					{
						foreach($appUnitObj as $appUnit)
						{
							$arrAppUnit[]=['id'=>$appUnit->id,'name'=>$appUnit->name];
						}							
					}
				}						
			}
		}	
		return ['app_units'=>$arrAppUnit];
	}

	/*
	public function actionView()
    {
        
        if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();

			$appmodel = Application::find()->select('id,code,company_name,address,zipcode,city,state_id,country_id,salutation,title,first_name,last_name,job_title,telephone,email_address,certification_status,preferred_partner_id,created_by,created_at')->where(['id' => $data['id']])->one();

			if ($appmodel !== null)
			{
				$appmodel->country_id=$appmodel->country->name;
				$appmodel->state_id=($appmodel->state_id!="")?$appmodel->state->name:"";
				$appmodel->created_by=($appmodel->created_by!="")?$appmodel->username->first_name:"";
				$appmodel->created_at=date('M d,Y h:i A',$appmodel->created_at);

				$resultarr=array();
				foreach($appmodel as $key => $value)
				{
					$resultarr[$key]=$value;
				}

				$appstdmodel = ApplicationStandard::find()->select('standard_id')->where(['app_id' => $data['id']])->asArray()->all();
				$resultarr["standards"]=$appstdmodel;

				$appproductmodel = ApplicationProduct::find()->select('product_id,wastage')->where(['app_id' => $data['id']])->asArray()->all();
				$resultarr["products"]=$appproductmodel;

				

				$unitmodel = ApplicationUnit::find()->select('id,name,code,address,zipcode,city,state_id,country_id,no_of_employees')->where(['app_id' => $data['id']])->all();
				$unitmodel->country_id=$unitmodel->country->name;
				$unitmodel->state_id=$unitmodel->state->name;

				$appunitmodel=array();
				foreach($unitmodel as $key => $value)
				{
					$appunitmodel[$key]=$value;
				}
				
				
				$unitarr=array();
				foreach($appunitmodel as &$value)
				{
					$appunitproductmodel = ApplicationUnitProduct::find()->select('product_id')->where(['unit_id' => $value['id']])->asArray()->all();
					

					$appunitprocessmodel = ApplicationUnitProcess::find()->select('process_id')->where(['unit_id' => $value['id']])->asArray()->all();
					

					$appunitcetifiedstdmodel = ApplicationUnitCertifiedStandard::find()->select('id,standard_id')->where(['unit_id' => $value['id']])->asArray()->all();

					
					foreach($appunitcetifiedstdmodel as $unitstdvalue)
					{
						$appunitcetifiedstdfilemodel = ApplicationUnitCertifiedStandardFile::find()->select('file')->where(['unit_certified_standard_id' => $unitstdvalue['id']])->asArray()->all();	
					}
					
					$unitstddetails = array();
					foreach ($appunitcetifiedstdmodel as $i => $val) 
					{
						$unitstddetails[] = array("standard"=>$val['standard_id'], "files"=>$appunitcetifiedstdfilemodel);
					}
					

					$value['products']=$appunitproductmodel;
					$value['process']=$appunitprocessmodel;
					$value['certified_standard']=$unitstddetails;
					$resultarr["units"]=$appunitmodel;
				}

				return $resultarr;
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>'Failed');
			}

		}
	}
	*/
	
	public function actionGetApplicationStandard()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data)
		{
			$model = Application::find()->where(['id' => $data['id']])->one();
			if($model!==null)				
			{	
				$appstdarr=[];
				$arrstandardids=[];
				$appStandard=$model->applicationstandard;
				if(count($appStandard)>0)
				{
					foreach($appStandard as $std)
					{						
						$arrstandardids[]="$std->standard_id";
					}
				}								
			}
			$responsedata=array('status'=>1,'applicationstandard'=>$arrstandardids);			
		}	
		return $responsedata;
	}
	
	public function getStandardVersion($standard_id){
		$versionID = 1;
		$Standard = Standard::find()->where(['id'=>$standard_id,'status'=>0])->orderBy(['id' => SORT_DESC])->one();
		if($Standard !== null){
			$versionID =  $Standard->version;
		}
		return $versionID;
	}

	public function actionBusinessectorstandardwise(){
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];


		$data = Yii::$app->request->post();

		//$standardvals = $data['standardvals'];
		//$bsGrou = BusinesSectorGroup::find()->where(['id'=>$standardvals,'status'=>0])->all();
		// AND bsg.standard_id IN (".implode(',',$standardvals).")
		$connection = Yii::$app->getDb();
		$command = $connection->createCommand("SELECT bs.id,bs.name,group_concat(bsg.standard_id) as mapstandardids FROM tbl_business_sector AS bs INNER JOIN tbl_business_sector_group AS bsg ON bs.id=bsg.business_sector_id WHERE bs.status=0 and bsg.status=0 GROUP BY bs.id");
		$result = $command->queryAll();

		$standardBGroup = [];
		if(count($result)>0)
		{
			foreach($result as $querydata)
			{
				$mapstandardids = $querydata['mapstandardids'];
				$mapstandardidsArr = array_unique(explode(',',$mapstandardids));
				foreach($mapstandardidsArr as $standardID){
					if(!isset($standardBGroup[$standardID])){
						$standardBGroup[$standardID] = [];
					}
					$standardBGroup[$standardID][] = $querydata['id'];
				}
				
				/*
				$values=array();
				$values['id'] = $data['id'];
				$values['name'] = $data['name'];
				$resultarr[]=$values;
				*/
			}
		}
		
		
		return $standardBGroup;
	}
	
	public function actionUpdateproductmaterial(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data)
		{
			$app_product_id = $data['product_id'];
			$ApplicationProductModels = ApplicationProduct::find()->where(['id' => $app_product_id])->one();
			$canChangeMaterialCompStatus = Yii::$app->globalfuns->canChangeMaterialComp($ApplicationProductModels->app_id);
			if($canChangeMaterialCompStatus == 0){
				return false;
			}
			
			$productMaterialList = $data['productmateriallist'];
			if(isset($productMaterialList) && is_array($productMaterialList) ){
				$ApplicationProductMaterialDel = ApplicationProductMaterial::find()->where(['app_product_id'=>$app_product_id])->all();
				if(count($ApplicationProductMaterialDel)>0){
					ApplicationProductMaterial::deleteAll(['app_product_id' =>$app_product_id]);
				}
				foreach($productMaterialList as $prdstd){
					$appproductmaterialmodel=new ApplicationProductMaterial();
					$appproductmaterialmodel->app_product_id=$app_product_id;
					$appproductmaterialmodel->material_id=$prdstd['material_id'];
					$appproductmaterialmodel->material_type_id =$prdstd['material_type_id'];
					$ProductTypeMaterialComposition = ProductTypeMaterialComposition::find()->where(['id'=> $prdstd['material_id']])->one();
					if($ProductTypeMaterialComposition !== null){
						$appproductmaterialmodel->material_name = $ProductTypeMaterialComposition->name;
						if(isset($ProductTypeMaterialComposition->material_type[$prdstd['material_type_id']])){
							$appproductmaterialmodel->material_type_name = $ProductTypeMaterialComposition->material_type[$prdstd['material_type_id']];
						}
					}
					//$ProductTypeMaterialComposition = new ProductTypeMaterialComposition();
					
					/*$StandardLabelGrade = StandardLabelGrade::find()->where(['id'=> $prdstd['label_grade']])->one();
					if($StandardLabelGrade !== null){
						$appproductstandardmodel->label_grade_name = $StandardLabelGrade->name;
					}*/
					$appproductmaterialmodel->percentage =$prdstd['material_percentage'];
					$appproductmaterialmodel->save(); 
				}
			}
			$responsedata=array('status'=>1,'message'=>'Successfully Updated');			
		}	
		return $responsedata;
	}


	public function actionUpdateproducttemp(){
		//selectedProductIds
		//unselectedProductIds
		//app_id
		//ApplicationProductCertificateTemp::fin
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];

		//certificate_id
		$data = yii::$app->request->post();
		$app_id = $data['app_id'];
		$certificate_id = $data['certificate_id'];
		$Certificate = Certificate::find()->where(['id'=>$certificate_id])->one();
		if($Certificate->status != $Certificate->arrEnumStatus['certification_in_process']){
			return false;
		}

		$product_addition_id = isset($data['product_addition_id'])?$data['product_addition_id']:'';

		if(isset($data['selectedProductIds']) && count($data['selectedProductIds'])>0){
			ApplicationProductCertificateTemp::deleteAll(['app_id' =>$app_id,'application_product_standard_id'=>$data['selectedProductIds']]);
		}

		if(isset($data['unselectedProductIds']) && count($data['unselectedProductIds'])>0){
			
			//ApplicationProductCertificateTemp::deleteAll(['app_id' =>$app_id,'application_product_standard_id'=>]);
			foreach($data['unselectedProductIds'] as $app_product_standard_id){
				$ApplicationProductCertificateTemp = ApplicationProductCertificateTemp::find()->where(['application_product_standard_id'=>$app_product_standard_id])->one();
				if($ApplicationProductCertificateTemp === null){
					$ApplicationProductStandard = ApplicationProductStandard::find()->where(['id'=>$app_product_standard_id])->one();
					$ApplicationProductCertificateTempIns = new ApplicationProductCertificateTemp();
					$ApplicationProductCertificateTempIns->app_id = $app_id;
					$ApplicationProductCertificateTempIns->application_product_standard_id = $app_product_standard_id;
					$ApplicationProductCertificateTempIns->product_id = $ApplicationProductStandard!==null?$ApplicationProductStandard->application_product_id:'';
					$ApplicationProductCertificateTempIns->certificate_id = $certificate_id;
					if($product_addition_id!='' && $product_addition_id>0){
						$ApplicationProductCertificateTempIns->product_addition_id = $product_addition_id;
						$ApplicationProductCertificateTempIns->product_addition_type = 1;
					}
					$ApplicationProductCertificateTempIns->save();
				}
			}
		}
		

		return $responsedata=array('status'=>1,'message'=>'Successfully Updated');		

	}
}
  