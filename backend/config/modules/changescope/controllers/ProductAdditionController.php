<?php
namespace app\modules\changescope\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use app\modules\certificate\models\Certificate;

use app\modules\master\models\Standard;
use app\modules\master\models\Process;
use app\modules\master\models\State;
use app\modules\master\models\UserCompanyInfo;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;
use app\modules\master\models\Product;
use app\modules\master\models\ProductType;
use app\modules\master\models\StandardLabelGrade;
use app\modules\master\models\ProductTypeMaterialComposition;

use app\modules\changescope\models\ProductAddition;
use app\modules\changescope\models\ProductAdditionReviewer;
use app\modules\changescope\models\ProductAdditionReviewerComment;
use app\modules\changescope\models\ProductAdditionUnit;
use app\modules\changescope\models\ProductAdditionProductMaterial;
use app\modules\changescope\models\ProductAdditionProduct;
use app\modules\changescope\models\ProductAdditionProductStandard;
use app\modules\changescope\models\ProductAdditionUnitProduct;
use app\modules\changescope\models\ProductAdditionFranchiseComment;


use app\modules\certificate\models\CertificateReviewer;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationUnitProduct;
use app\modules\application\models\ApplicationUnitStandard;
use app\modules\application\models\ApplicationStandard;

use app\modules\audit\models\Audit;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * ProductAdditionController implements the CRUD actions for Product model.
 */
class ProductAdditionController extends \yii\rest\Controller
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
		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];
		$role_chkid=$userData['role_chkid'];
		
		$ProductAdditionModel = new ProductAddition();

		$model = ProductAddition::find()->alias('t');
		$model = $model->innerJoinWith(['application as app']);	
		if($resource_access != '1')
		{

			if($user_type== Yii::$app->params['user_type']['customer']){
				$model = $model->andWhere('app.customer_id="'.$userid.'"');
			}else if($user_type== Yii::$app->params['user_type']['user']){
				$model = $model->joinWith(['reviewer as reviewer']);	
				$model = $model->andWhere('(t.status= "'.$ProductAdditionModel->arrEnumStatus['waiting_for_review'].'" or reviewer.user_id="'.$userid.'")');
			}else if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$model = $model->andWhere('t.status>0 and app.franchise_id="'.$userid.'"');
			}
			/*
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
			*/		
		}
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm']!='')
			{
				$model = $model->innerJoinWith('applicationaddress as caddress');
				
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',	
					['like', 'caddress.company_name', $searchTerm],
					['like', 'caddress.telephone', $searchTerm],
					['like', 'caddress.first_name', $searchTerm],
					['like', 'caddress.last_name', $searchTerm],
					['like', 'date_format(FROM_UNIXTIME(t.`created_at` ), \'%b %d, %Y\' )', $searchTerm],
				]);			
			}
			$totalCount = $model->count();
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
			}
			else
			{
				$model = $model->orderBy(['t.created_at' => SORT_DESC]);
			}
			



            $model = $model->limit($pageSize)->offset($page);
		}
		else
		{
			$totalCount = $model->count();
		}
		
		$question_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $question)
			{
				$data=array();
				$data['id']=$question->id;
				$data['app_id']=$question->app_id;
				$data['new_app_id']=$question->new_app_id;
				$data['status']=$question->status;
				$data['status_name']=$question->arrStatus[$question->status];
				$data['company_name']=$question->applicationaddress->company_name;
				$showedit = 0;
				if(($question->status == $question->arrEnumStatus['open'] || $question->status == $question->arrEnumStatus['pending_with_customer']) && ($user_type==2 || $resource_access==1)){
					$showedit = 1;
				}
				if(($question->status == $question->arrEnumStatus['waiting_for_osp_review'] || $question->status == $question->arrEnumStatus['pending_with_osp']) && ($user_type==3 || $resource_access==1)){
					$showedit = 1;
				}
				$data['showedit']=($showedit==1)?1:0;
				$data['showdelete']=($question->status==$question->arrEnumStatus['open'])?1:0;
				$data['created_by_label']=$question->createdbydata->first_name.' '.$question->createdbydata->last_name;
				$data['created_at']=date($date_format,$question->created_at);
				//$data['created_by_label']=$question->createdbydata->first_name.' '.$question->createdbydata->last_name;
				
				$unitproductcount=0;
				
				$arrAppUnit=array();
				$appUnit = $question->additionunit;
				if(count($appUnit)>0)
				{	
					foreach($appUnit as $app_unit)
					{
						if($app_unit->applicationunit->unit_type==1){
							$arrAppUnit[] = $question->applicationaddress->unit_name;
						}else{
							$arrAppUnit[]=$app_unit->applicationunit->name;
						}
						$unitproductcount = $unitproductcount+count($app_unit->unitproduct);
						
					}
				}					
				$data['application_unit']=implode(', ',$arrAppUnit);
				
				$data['addition_product_count']=$unitproductcount;
				
				$question_list[]=$data;
			}
		}

		return ['productadditions'=>$question_list,'total'=>$totalCount];
	}
	public function actionGetProductstd()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		$resultarr = [];
		if($data)
		{			
			$standardarr = Yii::$app->globalfuns->getAppUnitStandards($data);
			$responsedata=array('status'=>1,'data'=>$standardarr);
		}
		return $responsedata;
	}

	public function actionAddOspReview()
    {
		if(!Yii::$app->userrole->isOSS() && !Yii::$app->userrole->isAdmin()){
			return false;
		}

		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];

		if($data)
		{
			//User Access Condition Starts Here
			$ProductAdditionModel = new ProductAddition();
			$productmodelCheck = ProductAddition::find()->alias('t');
			$productmodelCheck = $productmodelCheck->innerJoinWith(['application as app'])->where(['t.id' => $data['id']]);	
			$checkStatus = [$ProductAdditionModel->arrEnumStatus['waiting_for_osp_review'],$ProductAdditionModel->arrEnumStatus['pending_with_osp']];
			$productmodelCheck = $productmodelCheck->andWhere(['t.status'=>$checkStatus]);
			if($resource_access != '1')
			{
				if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
					if($resource_access == '5'){
						$userid = $franchiseid;
					}
					$productmodelCheck = $productmodelCheck->andWhere(['app.franchise_id'=>$userid]);
				}
			}
			$productmodelCheck = $productmodelCheck->one();
			if($productmodelCheck===null){
				return false;
			}
			//User Access Condition Ends Here

			$ospreviewmodel = new ProductAdditionFranchiseComment();
			$ospreviewmodel->product_addition_id = $data['id'];
			$ospreviewmodel->status = $data['status'];
			$ospreviewmodel->comment = isset($data['comment'])?$data['comment']:'';
			$ospreviewmodel->created_by = $userid;
			$ospreviewmodel->created_at = time();
			if($ospreviewmodel->validate() && $ospreviewmodel->save())
			{
				$productmodel = ProductAddition::find()->where(['id' => $data['id']])->one();
				if($productmodel!==null)
				{
					if($data['status']=='1')
					{
						$ProductAdditionReviewer = ProductAdditionReviewer::find()->where(['product_addition_id'=>$data['id']])->one();
						if($ProductAdditionReviewer!==null){
							$productmodel->status = $productmodel->arrEnumStatus['review_in_process'];//4;
						}else{
							$productmodel->status = $productmodel->arrEnumStatus['waiting_for_review'];//4;
						}
					
					}
					else if($data['status']=='2')
					{
						$productmodel->status = $productmodel->arrEnumStatus['pending_with_customer'];//1;
					}
					else if($data['status']=='3')
					{
						$productmodel->status = $productmodel->arrEnumStatus['rejected'];//7;
					}
					$productmodel->save();
					$responsedata=array('status'=>1,'message'=>"Review Saved Successfully!");
				}				
			}			
		}
		return $responsedata;
	}

	public function actionAddReviewerReview()
    {
		if(!Yii::$app->userrole->hasRights(['application_review'])){
			return false;
		}
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data)
		{
			//User Access Condition Starts Here
			$ProductAdditionModel = new ProductAddition();
			$ProductAdditionCheck = ProductAddition::find()->where(['id' => $data['id'],'status'=>$ProductAdditionModel->arrEnumStatus['review_in_process'] ])->one();
			if($ProductAdditionCheck === null){
				return false;
			}
			if(!Yii::$app->userrole->isAdmin()){
				$ProductAdditionReviewer =ProductAdditionReviewer::find()->where(['product_addition_id'=>$data['id'],'reviewer_status'=>1])->one();
				if($ProductAdditionReviewer!==null){
					if($ProductAdditionReviewer->user_id != $userid){
						return false;
					}
				}else{
					return false;
				}
			}
			//User Access Condition Ends Here

			$connection = Yii::$app->getDb();
			
			$ospreviewmodel = new ProductAdditionReviewerComment();
			$ospreviewmodel->product_addition_id = $data['id'];
			$ospreviewmodel->product_addition_reviewer_id = $userid;
			$ospreviewmodel->status = $data['status'];
			$ospreviewmodel->comment = isset($data['comment'])?$data['comment']:'';
			$ospreviewmodel->created_by = $userid;
			$ospreviewmodel->created_at = time();
			if($ospreviewmodel->validate() && $ospreviewmodel->save())
			{
				$productmodel = ProductAddition::find()->where(['id' => $data['id']])->one();
				if($productmodel!==null)
				{
					if($data['status']=='1')
					{
						$productmodel->status =  $productmodel->arrEnumStatus['approved'];//6;
						
						$standardArr = [];
						$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
						$command = $connection->createCommand("SELECT pdt_std.standard_id as standard_id  FROM tbl_cs_product_addition_product as pdt inner join 
						`tbl_cs_product_addition_product_standard` as pdt_std  on pdt.id=pdt_std.product_addition_product_id  
						WHERE  pdt.product_addition_id='".$data['id']."' group by pdt_std.standard_id");
						$result = $command->queryAll();
						//$sectorgpArr = [];
						
						if(count($result)>0){
							foreach($result as $stdval){
								$standardArr[] = $stdval['standard_id'];
							}
						}

						if(count($standardArr)>0){
							$app_id = $productmodel->app_id;
							foreach($standardArr as $standardID){
								$CertificateExist = Certificate::find()->where(['parent_app_id'=>$app_id,'standard_id'=>$standardID ])->orderBy(['version' => SORT_DESC])->one();
								$version = $CertificateExist->version;
								$version = $version+1;

								$Certificate = new Certificate();
								$Certificate->audit_id = $CertificateExist->audit_id;
								$Certificate->parent_app_id = $app_id;
								$Certificate->standard_id = $standardID;
								$Certificate->product_addition_id = $productmodel->id;
								$Certificate->status = $Certificate->arrEnumStatus['open']; //0;
								$Certificate->type = $Certificate->arrEnumType['product_addition'];
								$Certificate->certificate_status = $Certificate->arrEnumCertificateStatus['valid'];//1;
								//$Certificate->version = $version;
								$Certificate->save();
							}
						}						 
					}
					else if($data['status']=='2')
					{
						$productmodel->status =  $productmodel->arrEnumStatus['pending_with_osp'];//3;
					}
					else if($data['status']=='3')
					{
						$productmodel->status =  $productmodel->arrEnumStatus['rejected'];//7;
					}
					$productmodel->save();
					$responsedata=array('status'=>1,'message'=>"Review Saved Successfully!");
				}				
			}			
		}
		return $responsedata;
	}	

	public function actionGetUnit()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		$resultarr = [];
		if($data)
		{
			$appModel = Application::find()->where(['id' => $data['id']])->one();
			if($appModel!==null)
			{
				$units = $appModel->applicationunit;
				if(count($units)>0)
				{
					$unitarr = [];
					foreach($units as $val)
					{
						$units = [];
						$units['id'] = $val['id'];
						$units['name'] = $val['name'];
						$unitarr[]=$units;
					}
					$resultarr['unitlist'] = $unitarr;
				}
				$responsedata=array('status'=>1,'data'=>$resultarr);
			}
		}
		return $responsedata;
	}

	public function actionGetAppdata()
	{
		$prdmodel = new ProductAddition();
		$ospreviewmodal = new ProductAdditionFranchiseComment();
		$reviewerreviewmodal = new ProductAdditionReviewerComment();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$Certificatemodel = new Certificate();
		$appmodel = Application::find()->alias('t');
		$appmodel = $appmodel->join('inner join', 'tbl_audit as audit','audit.app_id =t.id');
		$appmodel = $appmodel->join('inner join', 'tbl_certificate as cert','audit.id =cert.audit_id and cert.status="'.$Certificatemodel->arrEnumStatus['certificate_generated'].'" and cert.certificate_valid_until>="'.date('Y-m-d').'" ');
		if($resource_access != 1){
			if($user_type==2){
				$appmodel = $appmodel->andWhere(['t.customer_id' => $userid]);
			}else if($user_type==3 && $is_headquarters!='1'){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$appmodel = $appmodel->andWhere(['t.franchise_id' => $userid]);
			}else if($user_type==1){
				if(!Yii::$app->userrole->hasRights(['application_review','certification_management'])){
					return false;
				}
				if( $is_headquarters!='1'){
					$appmodel = $appmodel->andWhere(['t.franchise_id' => $franchiseid]);
				}
			} 
		}

		$appmodel = $appmodel->all();
		$resultarr = [];
		$reviewarr = [];
		$resultarr['units'] = [];
		$appdetails = [];
		$apparr = array();
			
		if(count($appmodel)>0)
		{
			foreach($appmodel as $app)
			{
				$apparr[] = ['id'=> $app->id, 'company_name' => $app->companyname];
			}

			$productdetails = [];
		}
		if($data)
		{
			if(isset($data['id']) && $data['id']>0){
				$appprdarr_details=[];
				$appprdarr = [];
				$model = ProductAddition::find()->where(['id' => $data['id']])->one();
				if($model!==null)
				{
					// condition to check reviewer starts here
					if($resource_access != 1 && $user_type==1 && Yii::$app->userrole->hasRights(['application_review'])){
						if($model->reviewer && $model->reviewer->user_id!='' && $model->reviewer->user_id != $userid){
							return false;
						}
					}
					// Condition to check reviewer end here
						
					

					$audit = Audit::find()->where(['app_id'=> $model->app_id])->one();
					if($audit !== null){
						$productdetails['audit_id'] = $audit->id;
					}

					$productdetails['app_id'] = $model->app_id;
					$productdetails['new_app_id'] = $model->new_app_id;
					$productdetails['status'] = $model->status;
					$productdetails['product_status'] = $model->status;
					
					if($model->arrEnumStatus['certification_in_process']==$model->status)
					{
						$certObj=$model->certificate;
						$productdetails['audit_id'] = $certObj->audit_id;
						$productdetails['certificate_id'] = $certObj->id;
						$productdetails['audit_plan_id'] = $certObj->audit->auditplan->id;
						//$productdetails['certificate_reviewer_id'] = $certObj->reviewer?$certObj->reviewer->user_id:'';
					}
					
					$productdetails['reviewer_id'] =$model->reviewer?$model->reviewer->user_id:'';

					$appProduct=$model->additionproduct;
					if(count($appProduct)>0)
					{
						$pdt_index = 0;
						$pdt_index_arr = 0;
						$unit_names = [];
						foreach($appProduct as $prd)
						{
							$productMaterialList = [];
							$materialcompositionname = '';
							if(is_array($prd->additionproductmaterial) && count($prd->additionproductmaterial)>0){
								foreach($prd->additionproductmaterial as $productmaterial){
									$productMaterialList[]=[
										'app_product_id'=>$productmaterial->product_addition_product_id,
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

							
							//ApplicationProductStandard::find()->where(['application_product_id' =>  ])->all();
							$arrsForPdtDetails=array(
								'id'=>$prd->product_id,
								'autoid'=>$prd->id,
								
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
										'label_grade_name' => $productstandard->labelgrade->name
									];

									
									$arrsForPdtDetails['pdt_index'] = $pdt_index_arr;
									$arrsForPdtDetails['standard_id'] = $productstandard->standard_id;
									$arrsForPdtDetails['standard_name'] = $productstandard->standard->name;
									$arrsForPdtDetails['label_grade'] = $productstandard->label_grade_id;
									$arrsForPdtDetails['label_grade_name'] = $productstandard->labelgrade->name;
									$arrsForPdtDetails['pdtListIndex'] = $i;
									

									$appprdarr_details[$productstandard->id]= $arrsForPdtDetails;
									$i++;

									$pdt_index_arr++;
								}
							}						


							$materialcompositionname = rtrim($materialcompositionname,' + ');
							$pdt_index_list[$prd->id] = $pdt_index;
							$arrs=array(
								'id'=>$prd->product_id,
								'autoid'=>$prd->id,
								'pdt_index'=>$pdt_index,
								'name'=>($prd->product?$prd->product->name:''),
								'wastage'=>$prd->wastage,
								'product_type_name' => isset($prd->producttype)?$prd->producttype->name:'',
								'product_type_id'=>isset($prd->producttype)?$prd->producttype->id:'',
								//'standard_id'=>$prd->standard_id,
								//'label_grade'=>$prd->label_grade_id,
								//'standard_name' => $prd->standard->name,
								//'label_grade_name' => $prd->standardlabelgrade->name,
								'productStandardList' => $productStandardList,
								'productMaterialList' => $productMaterialList,
								'materialcompositionname' => $materialcompositionname,
							);	
							$appprdarr[] = $arrs;


							
							$pdt_index++;
							
						}
					}
					$productdetails["products"]=$appprdarr;

					foreach($appprdarr_details as $pdtDetailsDt){
						$productdetails["productDetails"][] = $pdtDetailsDt;
						$productdetails["productDetailsdata"][] = $pdtDetailsDt;
					}

					$applicationmodel = $model->application;
		 			$exclude = 1;
					$excludeunits = [];
					
					if(count($model->additionunit)>0){
						foreach($model->additionunit as $unitsdata){
							$excludeunits[] = $unitsdata->unit_id;

							if($unitsdata->applicationunit->unit_type==1){
								$unit_names[] = $model->applicationaddress->unit_name;
							}else{
								$unit_names[] = $unitsdata->applicationunit->name;
							}
							
							$unitprd=$unitsdata->unitproduct;
							if(count($unitprd)>0)
							{
								$unitprdidsarr=array();
								$unitarr=[];
								foreach($unitprd as $unitP)
								{

									$unitprdarr=array();
									//$unitprdarr[]=($unitP->product?$unitP->product->name:'');
									//$unitprdarr['pdt_index']=$pdt_index_list[$unitP->product_id];
									$unitprdarr['pdt_id']=$unitP->application_product_standard_id;
									$unitprdidsarr[]=$unitP->application_product_standard_id;							

									$unitarr["products"][]=$unitprdarr;
									$unitarr["product_details"][]=(isset($appprdarr_details[$unitP->application_product_standard_id]) ? $appprdarr_details[$unitP->application_product_standard_id] : '');
									
									
								}
								//pdt_index
								
								
								$unitarr["product_ids"]=$unitprdidsarr;

								$resultarr["units"][$unitsdata->unit_id]=$unitarr;
							}	

						}
					}
					$appdetails = $this->getApplicationDetail($applicationmodel,$excludeunits,$model);

					$reviewarr['company_name'] = $model->applicationaddress->company_name; //$model->application->companyname;
					$reviewarr['units'] = implode(", ",$unit_names);
					$reviewarr['status'] = $model->status;
					$reviewarr['status_name'] = $prdmodel->arrStatus[$model->status];
					$reviewarr['created_by'] = $model->createdbydata->first_name." ".$model->createdbydata->last_name;
					$reviewarr['created_at'] = date($date_format,$model->created_at);

					$ospmodal = $model->franchisecmt;
					if(count($ospmodal)>0)
					{
						$ospcmts = [];
						foreach($ospmodal as $res)
						{
							$cmt = [];
							$cmt['status'] = $res->status;
							$cmt['status_label'] = $ospreviewmodal->arrStatus[$res->status];
							$cmt['comment'] = $res->comment;
							$cmt['created_at'] = date($date_format,$res->created_at);
							$cmt['created_by'] = $res->createdbydata->first_name." ".$res->createdbydata->last_name;
							$ospcmts[] = $cmt;
						}
						$reviewarr['osp_reviews'] = $ospcmts;
					}


					$reviewermodal = $model->reviewercmt;
					if(count($reviewermodal)>0)
					{
						$reviewercmts = [];
						foreach($reviewermodal as $res)
						{
							$cmt = [];
							$cmt['status'] = $res->status;
							$cmt['status_label'] = $reviewerreviewmodal->arrStatus[$res->status];
							$cmt['comment'] = $res->comment;
							$cmt['created_at'] = date($date_format,$res->created_at);
							$cmt['created_by'] = $res->createdbydata->first_name." ".$res->createdbydata->last_name;
							$reviewercmts[] = $cmt;
						}
						$reviewarr['reviewer_reviews'] = $reviewercmts;
					}

					$additionreviewermodal = $model->reviewer;
					if($additionreviewermodal!==null)
					{
						$reviewerdata = [];
						$reviewerdata['reviewer'] = $additionreviewermodal->user->first_name." ".$additionreviewermodal->user->last_name;
						$reviewerdata['assigned_date'] = date($date_format,$additionreviewermodal->created_at);
						$reviewarr['reviewer'] = $reviewerdata;
					}			 		
				}				
			}

			$ProductAdditionModel = new ProductAddition();
			$responsedata=array('status'=>1,'appdata'=>$apparr,'units'=>$resultarr["units"],'productdetails'=>$productdetails,'reviewdetails'=>$reviewarr,'appdetails'=>$appdetails,'enumstatus' => $ProductAdditionModel->arrEnumStatus );

		}
		//$ProductAdditionModel = new ProductAddition();
		$responsedata['status']=1;
		$responsedata['appdata']=$apparr;

			//array('status'=>1,'appdata'=>$apparr,'units'=>$resultarr["units"],'productdetails'=>$productdetails,'reviewdetails'=>$reviewarr,'appdetails'=>$appdetails,'enumstatus' => $ProductAdditionModel->arrEnumStatus );

		 
		return $this->asJson($responsedata);
	}
	
	public function actionGetAppcompanydata()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		$resource_access=$userData['resource_access'];
		$data = Yii::$app->request->post();


		
		$apparr = Yii::$app->globalfuns->getAppList();
		if(count($apparr)>0){
			$units = [];
			$app_id = 0;
			if(isset($data['id']) && $data['id']>0){
				$pmodel = ProductAddition::find()->where(['id'=>$data['id']])->one();
				if($pmodel !== null){
					$app_id = $pmodel->app_id;
					
					if(count($pmodel->additionunit)>0){
						foreach($pmodel->additionunit as $additionunit){
							$units[] = $additionunit->unit_id;
						}
					}
				}
			}
			
			$responsedata=array('status'=>1,'appdata'=>$apparr,'units'=>$units,'app_id'=>$app_id);

		}
		return $this->asJson($responsedata);
	}
	

	public function actionCreateproductaddition()
	{
		if(!Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->isCustomer())
		{
			return false;
		}
		
		$data = yii::$app->request->post();
		if($data)
		{
			$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$is_headquarters =$userData['is_headquarters'];
			$franchiseid=$userData['franchiseid'];
			$role_chkid=$userData['role_chkid'];

			if(!Yii::$app->userrole->isValidApplication($data['app_id']))
			{
				return false;
			}
			if(isset($data['id']))
			{
				$model = ProductAddition::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new ProductAddition();
					$model->created_by = $userid;
				}else{
					$update =1;
				}
			}else{
				$model = new ProductAddition();
				$model->created_by = $userid;			 
			}			 
			$model->app_id= $data['app_id'];			
			$model->address_id = Yii::$app->globalfuns->getAppCurrentAddressId($data['app_id']);			
			if($model->validate() && $model->save())
			{
				$modelID = $model->id;
				$unitexists = [];
				$newunits = [];
				$unitexistsIds = [];
				$newunitsIds = [];
				if(is_array($data['unit_id']) && count($data['unit_id'])>0){

					$additionunitexist = ProductAdditionUnit::find()->where(['product_addition_id'=>$modelID])->all();
					if(count($additionunitexist)>0){
						foreach($additionunitexist as $exunit_id){
							$unitexists[] = $exunit_id->unit_id;
							$unitexistsIds[] = $exunit_id->id;
						}
					}

					foreach($data['unit_id'] as $unit_id){
						$newunits[] = $unit_id;
						$additionunit = ProductAdditionUnit::find()->where(['product_addition_id'=>$modelID,'unit_id'=>$unit_id])->one();
						
						if($additionunit === null){
							$additionunit = new ProductAdditionUnit();
							$additionunit->unit_id = $unit_id;
							$additionunit->product_addition_id = $modelID;
							$additionunit->save();
						}
						$newunitsIds[] = $additionunit->id;
					}
					
					$extraunits = array_diff($unitexists, $newunits);
					$extraunitsIds = array_diff($unitexistsIds, $newunitsIds);
					/*
					if(count($extraunits)>0){
						
						foreach($extraunits as $delunit){
					*/
					if(count($extraunitsIds)>0){
						 
						foreach($unitexistsIds as $delunit){
							ProductAdditionUnitProduct::deleteAll(['product_addition_unit_id' => $delunit]);
						}
						foreach($extraunitsIds as $delunit){
							//$unitdata = ProductAdditionUnit::find()->where(['product_addition_id'=>$modelID,'unit_id'=>$delunit])->one();
							$unitdata = ProductAdditionUnit::find()->where(['id'=>$delunit])->one();
							if($unitdata !==null){
								ProductAdditionUnitProduct::deleteAll(['product_addition_unit_id' => $unitdata->id]);
								$unitdata->delete();
							}

							$additionproduct = ProductAdditionProduct::find()->where(['product_addition_id'=>$modelID])->all();
							if(count($additionproduct)>0)
							{	
								foreach($additionproduct as $addproduct){
									ProductAdditionProductMaterial::deleteAll(['product_addition_product_id' => $addproduct->id]);
									ProductAdditionProductStandard::deleteAll(['product_addition_product_id' => $addproduct->id]);
									$addproduct->delete();
								}
								
							}



						}
					}
				}
				$responsedata=array('status'=>1,'message'=>'Application for Process addition saved successfully','id'=>$modelID);
			}
		}			
		return $responsedata;
	}

	public function actionCreate()
	{
		if(!Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->isCustomer() && !Yii::$app->userrole->isOss())
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data  = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data){
			//$data = json_decode($datapost['formvalues'],true);
			
			if(!Yii::$app->userrole->isValidApplication($data['app_id']))
			{
				return false;
			}
			
			$update =0;
			if(isset($data['id']))
			{
				$model = ProductAddition::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new ProductAddition();
					$model->created_by = $userid;
				}else{
					

					$model->updated_by = $userid;
					$update =1;
				}
			}else{
				$model = new ProductAddition();
				$model->created_by = $userid;
				$model->status = 0;	
			}

			 
			$model->app_id= $data['app_id'];
			$model->address_id = Yii::$app->globalfuns->getAppCurrentAddressId($data['app_id']);
			
			
			$new=0;
			if($model->validate() && $model->save())
			{
				$modelID = $model->id;
				//if($update){
				if(is_array($data['products']) && count($data['products'])>0)
				{
					foreach($data['products'] as $products)
					{
						
						if(isset($products['autoid']) && $products['autoid']>0)
						{
							
							$additionproduct = ProductAdditionProduct::find()->where(['id'=>$products['autoid']])->one();

							if($additionproduct!==null)
							{	
								$productID = $additionproduct->id;
								ProductAdditionProductMaterial::deleteAll(['product_addition_product_id' => $productID]);
								//ProductAdditionProductStandard::deleteAll(['product_addition_product_id' => $productID]);

								/*
								$pcstandard = ProductAdditionUnit::find()->where(['product_addition_id'=>$productID])->all();
								if(count($pcstandard)>0)
								{
									foreach($pcstandard as $certstandard)
									{
										ProductAdditionUnitProduct::deleteAll(['product_addition_unit_id' => $certstandard->id]);
									}
								}
								ProductAdditionUnit::deleteAll(['product_addition_id' => $productID ]);
								*/
							}
							else
							{
								$additionproduct = new ProductAdditionProduct();
								$additionproduct->product_addition_id = $modelID;
							}
							
						}else{
							$new=1;
							$additionproduct = new ProductAdditionProduct();
							$additionproduct->product_addition_id = $modelID;
						}
						
						$additionproduct->product_id = isset($products['product_id'])?$products['product_id']:"";
						$additionproduct->wastage = isset($products['wastage'])?$products['wastage']:"";
						$additionproduct->product_type_id = isset($products['product_type'])?$products['product_type']:"";
						$additionproduct->material_name = isset($products['name'])?$products['name']:"";
						$Product = Product::find()->where(['id'=> $products['product_id']])->one();
						if($Product !== null){
							$additionproduct->product_name = $Product->name;
						}
						$ProductType = ProductType::find()->where(['id'=> $products['product_type']])->one();
						if($ProductType !== null){
							$additionproduct->product_type_name = $ProductType->name;
                        }

						if($additionproduct->validate() && $additionproduct->save()){
							$this->saveProductRelatedData($products, $additionproduct->id,$data['app_id']);
							
						}
					}
				}				

				if($new==1){
					$responsedata=array('status'=>1,'message'=>'Product updated successfully' ,'app_id'=>$data['app_id'],'id'=>$modelID);
				}else{
					$responsedata=array('status'=>1,'message'=>'Product added successfully','app_id'=>$data['app_id'],'id'=>$modelID );
				}
			}
		}
		
		return $this->asJson($responsedata);
	}

	public function actionAssignReviewer()
	{
		if(!Yii::$app->userrole->hasRights(['application_review'])){
			return false;
		}
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data  = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data)
		{
			//User Access Condition Starts Here
			$ProductAdditionModel = new ProductAddition();
			$ProductAdditionCheck = ProductAddition::find()->where(['id' => $data['id'],'status'=>$ProductAdditionModel->arrEnumStatus['waiting_for_review'] ])->one();
			if($ProductAdditionCheck === null){
				return false;
			}else{
				$modelReviewer = ProductAdditionReviewer::find()->where(['product_addition_id'=>$data['id'],'reviewer_status'=>1])->one();
				if($modelReviewer!==null){
					return false;
				}
			}
			//User Access Condition Ends Here

			$reviewermodel = new ProductAdditionReviewer();
			$reviewermodel->product_addition_id = $data['id'];
			$reviewermodel->user_id = $userid;
			$reviewermodel->created_by = $userid;
			if($reviewermodel->validate() && $reviewermodel->save())
			{
				$productmodel = ProductAddition::find()->where(['id' => $data['id']])->one();
				if($productmodel!==null)
				{
					$productmodel->status = $productmodel->arrEnumStatus['review_in_process'];
					$productmodel->save();
					$responsedata=array('status'=>1,'message'=>"Assigned Successfully!",'product_status'=>$productmodel->status);
				}
			}			
		}
		return $responsedata;
	}
	
	public function actionUpdateproduct()
	{
		if(!Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->isCustomer() && !Yii::$app->userrole->isOss())
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data  = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data){
			$connection = Yii::$app->getDb();
			//$data = json_decode($datapost['formvalues'],true);
			$update =0;
			
			$model = ProductAddition::find()->where(['id' => $data['id']])->one();
			if(!Yii::$app->userrole->isValidApplication($model->app_id))
			{
				return false;
			}	
			 
			
			if($model->validate() && $model->save())
			{
				$modelID = $model->id;
				//if($update){
				if(is_array($data['units']) && count($data['units'])>0)
				{

					foreach ($data['units'] as $value)
					{

						
						if(isset($value['unit_id']) && $value['unit_id']!=''){
							$appunitmodel = ProductAdditionUnit::find()->where(['unit_id' => $value['unit_id'],'product_addition_id' => $modelID])->one();
							if($appunitmodel!== null){
								ProductAdditionUnitProduct::deleteAll(['product_addition_unit_id' => $appunitmodel->id]);
							}else{
								$appunitmodel=new ProductAdditionUnit();
								$appunitmodel->unit_id = $value['unit_id'];
								$appunitmodel->product_addition_id = $modelID;
								$appunitmodel->save();
							}
							

						}else{
							$appunitmodel=new ProductAdditionUnit();
							$appunitmodel->unit_id = $value['unit_id'];
							$appunitmodel->product_addition_id = $modelID;
							$appunitmodel->save();
						}
						if(is_array($value['products']) && count($value['products'])>0)
						{
							foreach ($value['products'] as $val111)
							{ 
								$productMaterialList = $val111['productMaterialList'];
								$queryStr = [];
								foreach($productMaterialList as $materiall){
									$queryStr[] = "  ( material_id = '".$materiall['material_id']."' AND material_type_id = '".$materiall['material_type_id']."' AND percentage = '".$materiall['material_percentage']."') ";
								}
								$totalCompCnt = count($queryStr);

								$queryCondition = ' ('.implode(' OR ',$queryStr).') ';
								
								$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
								$command = $connection->createCommand("SELECT  COUNT(pdt.id) as matcnt,pdt_std.id  as pdt_std_id  
								from tbl_cs_product_addition_product as pdt 
								INNER JOIN tbl_cs_product_addition_product_standard as pdt_std on pdt_std.product_addition_product_id = pdt.id 
								INNER JOIN tbl_cs_product_addition_product_material as pdt_mat on pdt_mat.product_addition_product_id = pdt.id 
								WHERE 
								pdt.product_id='".$val111['id']."' AND pdt.product_type_id='".$val111['product_type_id']."'
								 AND pdt.wastage='".$val111['wastage']."' 
								AND pdt_std.standard_id='".$val111['standard_id']."' AND pdt_std.label_grade_id='".$val111['label_grade']."' 
								AND pdt.product_addition_id='".$modelID."' 

								AND ".$queryCondition."
								
								group by pdt.id HAVING matcnt=".$totalCompCnt." ");
								$result = $command->queryOne();
								$pdt_std_id = 0;
								if($result  !== false){
									$pdt_std_id = $result['pdt_std_id'];
								}


								
								//$pdt_id = $pdtlistval[$val1['pdt_index']];
								$appunitproductmodel=new ProductAdditionUnitProduct();
								$appunitproductmodel->product_addition_unit_id=$appunitmodel->id;
								$appunitproductmodel->application_product_standard_id=$pdt_std_id;//$pdtstdmodel->id;
								$appunitproductmodel->save();
							}
						}
					}
				}

				if(isset($data['type']) && $data['type']=='addition'){
					$model->status = $model->arrEnumStatus['waiting_for_osp_review'];
					$model->save();
				}

				$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'product_addition'])->one();
				if($mailContent !== null )
				{
					$additiongrid = $this->renderPartial('@app/mail/layouts/AdditionCompanyGridTemplate',[
						'model' => $model->application
					]);

					$mailmsg = str_replace('{NEW-APPLICATION-DETAILS-GRID}', $additiongrid, $mailContent['message'] );

					$franchise = UserCompanyInfo::find()->select('company_email')->where(['user_id' => $model->application->franchise_id])->one();
					if($franchise !== null )
					{
						$MailLookupModel = new MailLookup();
						$MailLookupModel->to=$franchise['company_email'];						
						$MailLookupModel->subject=$mailContent['subject'];
						$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
						$MailLookupModel->attachment='';
						$MailLookupModel->mail_notification_id='';
						$MailLookupModel->mail_notification_code='';
						$Mailres=$MailLookupModel->sendMail();
					}
				}
				

				if(1){
					$responsedata=array('status'=>1,'message'=>'Product updated successfully' ,'app_id'=>$data['app_id'],'id'=>$modelID);
				}else{
					$responsedata=array('status'=>1,'message'=>'Product added successfully','app_id'=>$data['app_id'],'id'=>$modelID );
				}
			}
		}		
		return $this->asJson($responsedata);
	}

	public function saveProductRelatedData($value,$productID,$app_id){
		$connection = Yii::$app->getDb();
		
		if(is_array($value['productStandardList']) && count($value['productStandardList'])>0)
		{
			$existstd = ProductAdditionProductStandard::find()->where(['product_addition_product_id'=>$productID])->all();
			$standardIDs = [];
			$standardAutoIds = [];
			if(count($existstd)>0){
				foreach($existstd as $existobj){
					$standardIDs[]=  $existobj->standard_id;
					$standardAutoIds[]=  $existobj->id;
				}
			}
			$addedAutoIds = [];
			foreach ($value['productStandardList'] as $val2)
			{ 
				$appproductstdmodel = ProductAdditionProductStandard::find()->where(['standard_id'=>$val2['standard_id'],'product_addition_product_id'=>$productID])->one();
				if($appproductstdmodel===null){
					$appproductstdmodel=new ProductAdditionProductStandard();
				}else{
					$addedAutoIds[] = $appproductstdmodel->id;
				}
				
				$appproductstdmodel->product_addition_product_id=$productID;
				$appproductstdmodel->standard_id=$val2['standard_id'];
				$appproductstdmodel->label_grade_id=$val2['label_grade'];

				$StandardLabelGrade = StandardLabelGrade::find()->where(['id'=> $val2['label_grade']])->one();
				if($StandardLabelGrade !== null){
					$appproductstdmodel->label_grade_name = $StandardLabelGrade->name;
				}
				$appproductstdmodel->save(); 
			}

			$removedStdIds = array_diff($standardAutoIds,$addedAutoIds);
			if(count($removedStdIds)>0){
				foreach($removedStdIds as $stdId){
					$pdtStd = ProductAdditionProductStandard::find()->where(['id' => $stdId ])->one();
					if($pdtStd !== null ){
						$additiounit = ProductAdditionUnit::find()->where(['product_addition_id' => $productID ])->all();
						foreach($additiounit  as $unitobj){
							ProductAdditionUnitProduct::deleteAll(['application_product_standard_id' => $stdId,'product_addition_unit_id'=>$unitobj->id]);
						}
						$pdtStd->delete();					
					}
				}				
			}
		}

		if(is_array($value['productMaterialList']) && count($value['productMaterialList'])>0)
		{
			foreach ($value['productMaterialList'] as $val3)
			{ 
				$appproductmaterialmodel=new ProductAdditionProductMaterial();
				$appproductmaterialmodel->product_addition_product_id=$productID;
				$appproductmaterialmodel->material_id=$val3['material_id'];
				$appproductmaterialmodel->material_type_id=$val3['material_type_id'];
				$appproductmaterialmodel->percentage=$val3['material_percentage'];

				$ProductTypeMaterialComposition = ProductTypeMaterialComposition::find()->where(['id'=> $val3['material_id']])->one();
				if($ProductTypeMaterialComposition !== null){
					$appproductmaterialmodel->material_name = $ProductTypeMaterialComposition->name;
					if(isset($ProductTypeMaterialComposition->material_type[$val3['material_type_id']])){
						$appproductmaterialmodel->material_type_name = $ProductTypeMaterialComposition->material_type[$val3['material_type_id']];
					}
				}
				$appproductmaterialmodel->save(); 
			}
		}

	}

	public function actionGetStatus()
	{
		$data  = Yii::$app->request->post();
		if($data)
		{
			if($data['status']==5)
			{
				$model = new ProductAdditionReviewerComment();
			}
			else
			{
				$model = new ProductAdditionFranchiseComment();
			}
			
			return ['data'=>$model->arrStatus];
		}
		
	}
	

	public function getApplicationDetail($model,$excludeunits,$currentmodel)
	{
		$resultarr = [];
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		 
		if ($model !== null)
		{
			$resultarr=array();

			$resultarr = Yii::$app->globalfuns->getApplicationAddressDetails($currentmodel->applicationaddress);
			$resultarr["id"]=$model->id;
			$resultarr["code"]=$model->code;
			$resultarr["company_file"]=$model->company_file;
			$resultarr['created_at']=date($date_format,$model->created_at);
			
			$resultarr["created_by"]=($model->created_by!="")?$model->username->first_name.' '.$model->username->last_name:"";
			$resultarr["certification_status"]=$model->certification_status;

			$resultarr["reject_comment"]=$model->reject_comment;
			$resultarr["rejected_date"]=date($date_format,strtotime($model->rejected_date));

			//$resultarr["preferred_partner_id"]=$model->preferred_partner_id;
			//$resultarr["preferred_partner_id_name"]=($model->preferredpartner?$model->preferredpartner->name:'');
			
			$resultarr["app_status"]=$model->status;
			$resultarr["status"]=$model->arrStatus[$model->status];
			$resultarr["franchise_id"]=$model->franchise_id;

			if($model->franchise){
				$resultarr["franchise"]= $model->franchise->usercompanyinfo->toArray();
				$resultarr["franchise"]['company_country_name']= $model->franchise->usercompanyinfo->companycountry->name;
				$resultarr["franchise"]['company_state_name']= $model->franchise->usercompanyinfo->companystate?$model->franchise->usercompanyinfo->companystate->name:'';
			}

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
			$appprdarr_details=[];
			$appProduct=$model->applicationproduct;
			
			$relProducts = Yii::$app->globalfuns->getAppProducts($appProduct,$arrstandardids);
			$resultarr["products"]=$relProducts['products'];
			$resultarr["productDetails"] = $relProducts['productDetails'];
			$appprdarr_details = $relProducts['appprdarr_details'];
						
			$unitarr=array();
			$unitnamedetailsarr=array();
			$appUnit=$model->applicationunit;
			$unitdetailsarr = [];
			if(count($appUnit)>0)
			{
				foreach($appUnit as $unit)
				{
					if(is_array($excludeunits) && count($excludeunits)>0){
						//print_r($excludeunits);
						if( !in_array($unit->id, $excludeunits)){
							continue;
						}
					}				
					
					$unitarr = $unit->toArray();
					$unitarr["unit_type_name"]=$unit->unit_type_list[$unit->unit_type];
					
					$unitarr["state_id_name"]=($unit->state_id!="")?$unit->state->name:"";
					$unitarr["country_id_name"]=($unit->country_id!="")?$unit->country->name:"";
					//$unitarr["unit_type"]=$unit->unit_type;
					if($unit->unit_type ==1){
						$applicationaddress = $currentmodel->applicationaddress;
						$unitarr["name"] = $applicationaddress->unit_name;
						$unitarr["address"] = $applicationaddress->unit_address;
						$unitarr["zipcode"] = $applicationaddress->unit_zipcode;
						$unitarr["city"] = $applicationaddress->unit_city;
						$unitarr["state_id"] = $applicationaddress->unit_state_id;
						$unitarr["country_id"] = $applicationaddress->unit_country_id;
						$unitarr["state_id_name"] = $applicationaddress->unitstate->name;
						$unitarr["country_id_name"] = $applicationaddress->unitcountry->name;

						$unitnamedetailsarr[$unit->id] = $applicationaddress->unit_name;
					}else{
						$unitnamedetailsarr[$unit->id] = $unit->name;
					}

					$unitprd=$unit->unitproduct;
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
							$unitprdidsarr[]=$unitP->application_product_standard_id;							

							$unitarr["products"][]=$unitprdarr;
							$unitarr["product_details"][]=(isset($appprdarr_details[$unitP->application_product_standard_id]) ? $appprdarr_details[$unitP->application_product_standard_id] : '');
													
						}
						//pdt_index						
						
						$unitarr["product_ids"]=$unitprdidsarr;
					}	
					
					//standards
					$unitstdidsarr=array();
					$unitstddetailssarr=array();
					$unitappstandard=$unit->unitappstandard;
					if(count($unitappstandard)>0)
					{
						foreach($unitappstandard as $unitstd)
						{

							if(!in_array($unitstd->standard_id,$arrstandardids)){
								continue;
							}

							$unitstddetailssarrtemp = [];
							$unitstdidsarr[]=$unitstd->standard_id;
							
							$unitstddetailssarrtemp['id']=$unitstd->standard_id;
							$unitstddetailssarrtemp['name']=$unitstd->standard->name;

							$unitstddetailssarr[]=$unitstddetailssarrtemp;
						}
					}
					$unitarr["standards"]=$unitstdidsarr;
					$unitarr["standarddetails"]=$unitstddetailssarr;
					 					
					$unitdetailsarr[]=$unitarr;
				}
				$resultarr["units"]=$unitdetailsarr;
			}			
		}
		return $resultarr;
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
		
		$model = ProductAddition::find()->where(['id' => $data['id']]);
		if($resource_access != 1)
		{
			if($user_type== 1 && ! in_array('application_management',$rules)){
				return $responsedata;
			}
			// else if($user_type==3){
			// 	if($resource_access == 5){
			// 		$model = $model->andWhere('franchise_id="'.$franchiseid.'" or created_by="'.$franchiseid.'"');
			// 	}else{
			// 		$model = $model->andWhere('franchise_id="'.$userid.'" or created_by="'.$userid.'"');
			// 	}
				
			// }
			else if($user_type==2){
				$model = $model->andWhere('created_by="'.$userid.'"');
			}
		}
		
		$standardID=$data['standard_id'];
		
		$connection = Yii::$app->getDb();
		$model= $model->one();
		if ($model !== null)
		{
			$resultarr=array();

			$resultarr["id"]=$model->id;
			$resultarr['created_at']=date($date_format,$model->created_at);
			$resultarr["created_by"]=($model->username!="")?$model->username->first_name.' '.$model->username->last_name:"";
			$resultarr["app_status"]=$model->status;
			$resultarr["status"]=$model->arrStatus[$model->status];
			$resultarr["franchise_id"]=$model->application->franchise_id;

			$appprdarr=[];
			$appprdarr_details=[];
			$appProduct=$model->additionproduct;
			$standard_ids = [];
			
			$resultarr["productDetails"] = [];
			$resultarr = Yii::$app->globalfuns->getProductAdditionProducts($appProduct,$standardID);
			return $resultarr;

			
			if(count($appProduct)>0)
			{
				$pdt_index = 0;
				$pdt_index_arr = 0;
				$unit_names = [];
				foreach($appProduct as $prd)
				{
					$productMaterialList = [];
					$materialcompositionname = '';
					if(is_array($prd->additionproductmaterial) && count($prd->additionproductmaterial)>0){
						foreach($prd->additionproductmaterial as $productmaterial){
							$productMaterialList[]=[
								'app_product_id'=>$productmaterial->product_addition_product_id,
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

					
					$arrsForPdtDetails=array(
						'id'=>$prd->product_id,
						'autoid'=>$prd->id,
						
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
								'label_grade_name' => $productstandard->labelgrade->name
							];

							
							$arrsForPdtDetails['pdt_index'] = $pdt_index_arr;
							$arrsForPdtDetails['standard_id'] = $productstandard->standard_id;
							$arrsForPdtDetails['standard_name'] = $productstandard->standard->name;
							$arrsForPdtDetails['label_grade'] = $productstandard->label_grade_id;
							$arrsForPdtDetails['label_grade_name'] = $productstandard->labelgrade->name;
							$arrsForPdtDetails['pdtListIndex'] = $i;
							

							$appprdarr_details[$productstandard->id]= $arrsForPdtDetails;
							$i++;

							$pdt_index_arr++;
						}
					}						


					$materialcompositionname = rtrim($materialcompositionname,' + ');
					$pdt_index_list[$prd->id] = $pdt_index;
					$arrs=array(
						'id'=>$prd->product_id,
						'autoid'=>$prd->id,
						'pdt_index'=>$pdt_index,
						'name'=>($prd->product?$prd->product->name:''),
						'wastage'=>$prd->wastage,
						'product_type_name' => isset($prd->producttype)?$prd->producttype->name:'',
						'product_type_id'=>isset($prd->producttype)?$prd->producttype->id:'',
						'productStandardList' => $productStandardList,
						'productMaterialList' => $productMaterialList,
						'materialcompositionname' => $materialcompositionname,
					);	
					$appprdarr[] = $arrs;


					
					$pdt_index++;
					
				}
			}
			$resultarr["products"]=$appprdarr;
			
			foreach($appprdarr_details as $pdtDetailsDt)
			{
				$resultarr["productDetails"][] = $pdtDetailsDt;
			}

			return $resultarr;

		}
	}

	public function actionUpdateproductmaterial()
	{
		if(!Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->isCustomer())
		{
			return false;
		}

		$ProductAdditionModel = new ProductAddition();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data)
		{
			$app_product_id = $data['product_id'];
			$additionunit = ProductAdditionProduct::find()->where(['id'=>$app_product_id])->one();
			if(($additionunit===null || !Yii::$app->userrole->canViewApplication($additionunit->productaddition->app_id)) &&  ($additionunit->productaddition->status > $ProductAdditionModel->arrEnumStatus['review_in_process']))
			{
				return false;
			}

			$productMaterialList = $data['productmateriallist'];
			if(isset($productMaterialList) && is_array($productMaterialList) ){
				$ApplicationProductMaterialDel = ProductAdditionProductMaterial::find()->where(['product_addition_product_id'=>$app_product_id])->all();
				if(count($ApplicationProductMaterialDel)>0){
					ProductAdditionProductMaterial::deleteAll(['product_addition_product_id' =>$app_product_id]);
				}
				foreach($productMaterialList as $prdstd){
					$appproductmaterialmodel=new ProductAdditionProductMaterial();
					$appproductmaterialmodel->product_addition_product_id=$app_product_id;
					$appproductmaterialmodel->material_id=$prdstd['material_id'];
					$appproductmaterialmodel->material_type_id =$prdstd['material_type_id'];
					$ProductTypeMaterialComposition = ProductTypeMaterialComposition::find()->where(['id'=> $prdstd['material_id']])->one();
					if($ProductTypeMaterialComposition !== null){
						$appproductmaterialmodel->material_name = $ProductTypeMaterialComposition->name;
						if(isset($ProductTypeMaterialComposition->material_type[$prdstd['material_type_id']])){
							$appproductmaterialmodel->material_type_name = $ProductTypeMaterialComposition->material_type[$prdstd['material_type_id']];
						}
					}
					$appproductmaterialmodel->percentage =$prdstd['material_percentage'];
					$appproductmaterialmodel->save(); 
				}
			}
			$responsedata=array('status'=>1,'message'=>'Successfully Updated');			
		}	
		return $responsedata;
	}

	public function actionDeleteproduct()
	{
		if(!Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->isCustomer() && !Yii::$app->userrole->isOss())
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data){
			if(isset($data['autoid']) && $data['autoid']>0){
				$additionunit = ProductAdditionProduct::find()->where(['id'=>$data['autoid']])->one();
				
				if($additionunit!==null){
					
					if(!Yii::$app->userrole->isValidApplication($additionunit->productaddition->app_id))
					{
						return false;
					}
					
					$productID = $additionunit->id;
					
					ProductAdditionProductMaterial::deleteAll(['product_addition_product_id' => $productID]);
					
					$pdtStd = ProductAdditionProductStandard::find()->where(['product_addition_product_id' => $productID ])->all();
					if(count($pdtStd)>0){
						foreach($pdtStd as $pdtstdobj){
							
							$additiounit = ProductAdditionUnit::find()->where(['product_addition_id' => $additionunit->product_addition_id ])->all();
							foreach($additiounit  as $unitobj){
								ProductAdditionUnitProduct::deleteAll(['application_product_standard_id' => $pdtstdobj->id,'product_addition_unit_id'=>$unitobj->id]);
							}
							$pdtstdobj->delete();
						}
					}
					$additionunit->delete();
					$responsedata=array('status'=>1,'message'=>'Product deleted successfully','id'=>$additionunit->product_addition_id);

				}
				
			}
		}
		return $responsedata;
	}
	
	public function actionGetAppunitdata()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$unitarr = Yii::$app->globalfuns->getAppunitdata($data);			
			$responsedata=array('status'=>1,'unitdata'=>$unitarr);
		}
		return $this->asJson($responsedata);
	}


	public function actionCommonUpdate()
	{
		if(!Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->isCustomer() && !Yii::$app->userrole->isOss())
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data){
			if(isset($data['id']) && $data['id']>0){
				$ProductAddition = ProductAddition::find()->where(['id'=>$data['id']])->one();

				if($ProductAddition !== null){
					if(!Yii::$app->userrole->isValidApplication($ProductAddition->app_id))
					{
						return false;
					}
					
					$ProductAdditionProduct = ProductAdditionProduct::find()->where(['product_addition_id'=>$ProductAddition->id])->all();

					if(count($ProductAdditionProduct)>0){
						foreach($ProductAdditionProduct as $additionpdt){
							$productID = $additionpdt->id;
						
							ProductAdditionProductMaterial::deleteAll(['product_addition_product_id' => $productID]);							
							ProductAdditionProductStandard::deleteAll(['product_addition_product_id' => $productID ]);							
							$additionpdt->delete();							
						}
					}
					$additiounit = ProductAdditionUnit::find()->where(['product_addition_id' => $ProductAddition->id])->all();
					foreach($additiounit  as $unitobj){
						ProductAdditionUnitProduct::deleteAll(['product_addition_unit_id'=>$unitobj->id]);
						$unitobj->delete();
					}
					$ProductAddition->delete();
					$responsedata=array('status'=>1,'message'=>'Product deleted successfully');
				}				
			}
		}		
		return $this->asJson($responsedata);
	}
	 	
}
