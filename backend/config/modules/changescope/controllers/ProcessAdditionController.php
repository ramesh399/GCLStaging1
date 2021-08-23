<?php
namespace app\modules\changescope\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationUnitProcess;
use app\modules\application\models\ApplicationUnitStandard;

use app\modules\changescope\models\ProcessAddition;
use app\modules\changescope\models\ProcessAdditionUnit;
use app\modules\changescope\models\ProcessAdditionUnitProcess;

use app\modules\master\models\UserCompanyInfo;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;
use app\modules\master\models\Process;

use app\modules\certificate\models\Certificate;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * ProcessAdditionController implements the CRUD actions for Product model.
 */
class ProcessAdditionController extends \yii\rest\Controller
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
		$processmodel = new ProcessAddition();

		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();


		$model = ProcessAddition::find()->alias('t');
		$model = $model->innerJoinWith(['application as app']);	
		if($resource_access != '1')
		{
			if($user_type== Yii::$app->params['user_type']['customer']){
				$model = $model->andWhere('app.customer_id="'.$userid.'"');
			}else if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$model = $model->andWhere('app.franchise_id="'.$userid.'" or t.created_by="'.$userid.'"');
			}else if($user_type== Yii::$app->params['user_type']['user'] &&  in_array('application_review',$rules)){
				$model = $model->join('left join', 'tbl_product_addition_reviewer as reviewer','reviewer.reviewer_status=1 and reviewer.app_id=t.app_id');
				
				$model = $model->andWhere('(t.status ="'.$processmodel->arrEnumStatus['waiting_for_review'].'" 
							or  reviewer.user_id='.$userid.')');
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
				$data['showdelete']=($question->status==$question->arrEnumStatus['open'])?1:0;
				$data['showedit']=($question->status==$question->arrEnumStatus['open'] || $question->status==$question->arrEnumStatus['pending_with_customer'])?1:0;
				/*
				$libraryfaqaccess = $question->libraryfaqaccess;
				if(count($libraryfaqaccess)>0)
				{
					$access_id_arr = array();
					$access_id_label_arr = array();
					foreach($libraryfaqaccess as $val)
					{
						if($val->useraccess!==null)
						{
							$access_id_arr[]="".$val['user_access_id'];
							$access_id_label_arr[]=($val->useraccess ? $val->useraccess->role_name : '');
						}
					}
					$data["user_access_id"]=$access_id_arr;
					$data["access_id_label"]=implode(', ',$access_id_label_arr);
				}

				$data['status']=$question->status;
				*/
				$data['created_by_label']=$question->createdbydata->first_name.' '.$question->createdbydata->last_name;
				$data['created_at']=date($date_format,$question->created_at);
				//$data['created_by_label']=$question->createdbydata->first_name.' '.$question->createdbydata->last_name;
				
				$unitprocesscount=0;
				
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
						$unitprocesscount = $unitprocesscount+count($app_unit->unitprocess);
						
					}
				}					
				$data['application_unit']=implode(', ',$arrAppUnit);
				
				$data['addition_process_count']=$unitprocesscount;
				
				$question_list[]=$data;
			}
		}

		return ['processadditions'=>$question_list,'total'=>$totalCount];
	}

	public function actionGetrequestedstatus()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$modelUnitAddition = new ProcessAddition();						
			$resultarr=array();			
			
			$appmodel = ProcessAddition::find()->where(['t.app_id' => $data['id']])->alias('t');
			$appmodel = $appmodel->andWhere('t.status in('.$modelUnitAddition->arrEnumStatus['approved'].','.$modelUnitAddition->arrEnumStatus['failed'].','.$modelUnitAddition->arrEnumStatus['osp_reject'].')');
			$appmodel = $appmodel->all();
			if(count($appmodel)>0)
			{
				$responsedata=array('status'=>1,'unitdata'=>'');
			}
			//$responsedata=array('status'=>0,'message'=>'Process Addition is in progress.');
			$responsedata=array('status'=>1,'unitdata'=>'');
		}
		return $this->asJson($responsedata);
	}

	public function actionCreateprocessaddition()
	{
		if(!Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->isCustomer())
		{
			return false;
		}

		$data = yii::$app->request->post();
		if($data)
		{
			if(!Yii::$app->userrole->isValidApplication($data['app_id']))
			{
				return false;
			}
		
			
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


			if(isset($data['id']))
			{
				$model = ProcessAddition::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new ProcessAddition();
					$model->created_by = $userid;
				}else{
					$update =1;
				}
			}else{
				$model = new ProcessAddition();
				$model->created_by = $userid;
			 
			}

			 
			$model->app_id= $data['app_id'];
			$model->address_id = Yii::$app->globalfuns->getAppCurrentAddressId($data['app_id']);
			if($model->validate() && $model->save())
			{
				$modelID = $model->id;
				$unitexists = [];
				$newunits = [];
				if(is_array($data['unit_id']) && count($data['unit_id'])>0){

					$additionunitexist = ProcessAdditionUnit::find()->where(['process_addition_id'=>$modelID])->all();
					if(count($additionunitexist)>0){
						foreach($additionunitexist as $exunit_id){
							$unitexists[] = $exunit_id->unit_id;
						}
					}

					foreach($data['unit_id'] as $unit_id){
						$newunits[] = $unit_id;
						$additionunit = ProcessAdditionUnit::find()->where(['process_addition_id'=>$modelID,'unit_id'=>$unit_id])->one();
						
						if($additionunit === null){
							$additionunit = new ProcessAdditionUnit();
							$additionunit->unit_id = $unit_id;
							$additionunit->process_addition_id = $modelID;
							$additionunit->save();
						}
					}
					
					$extraunits = array_diff($unitexists, $newunits);
					if(count($extraunits)>0){
						
						foreach($extraunits as $delunit){
							$unitdata = ProcessAdditionUnit::find()->where(['process_addition_id'=>$modelID,'unit_id'=>$delunit])->one();
							if($unitdata !==null){
								ProcessAdditionUnitProcess::deleteAll(['process_addition_unit_id' => $unitdata->id]);
								$unitdata->delete();
							}

						}
					}
				}
				$responsedata=array('status'=>1,'message'=>'Application for Process addition saved successfully','id'=>$modelID);
			}
		}	
		return $responsedata;
	}

	public function actionGetAppdata()
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

		$data = yii::$app->request->post();
		/*
		$Certificatemodel = new Certificate();
		$appmodel = Application::find()->select('t.id,t.company_name')->alias('t');
		$appmodel = $appmodel->join('inner join', 'tbl_audit as audit','audit.app_id =t.id');
		$appmodel = $appmodel->join('inner join', 'tbl_certificate as cert','audit.id =cert.audit_id and cert.status="'.$Certificatemodel->arrEnumStatus['certificate_generated'].'" and cert.certificate_valid_until>="'.date('Y-m-d').'" ');
		if($resource_access != 1){
			if($user_type==2){
				$appmodel = $appmodel->andWhere(['t.customer_id' => $userid]);
			}else if($user_type==3 && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$appmodel = $appmodel->andWhere(['t.franchise_id' => $userid]);
			}else if($user_type==1){
				$appmodel = $appmodel->andWhere(['t.franchise_id' => $franchiseid]);
			}else{
				return $responsedata;
			}
		}

		$appmodel = $appmodel->all();
		*/
		$apparr = Yii::$app->globalfuns->getAppList();
		$units = [];
		$app_id ='';
		//if(count($appmodel)>0)
		if(count($apparr)>0)
		{
			/*
			$apparr = array();
			foreach($appmodel as $app)
			{
				$apparr[] = ['id'=> $app->id, 'company_name' => $app->company_name];
			}
			*/
			if(isset($data['id']) && $data['id']>0){
				$pmodel = ProcessAddition::find()->where(['id'=>$data['id']])->one();
				if($pmodel !== null){
					$app_id = $pmodel->app_id;
					$units = [];
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

	public function actionGetProcessdetails()
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
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		if ($data) 
		{	
			$processadditionmodel = new ProcessAddition();
			$processmodel = ProcessAddition::find()->where(['id'=>$data['id']])->one();
			if ($processmodel !== null)
			{
				if(!Yii::$app->userrole->isAdmin()){
					//($user_type == 2 || $user_type == 3) &&
					if(!Yii::$app->userrole->canViewApplication($processmodel->app_id)){
						return false;
					}
				}
				$processadditionarr = [];
				$processadditionarr['status'] =  $processmodel->status;
				$processadditionarr['status_label'] =  $processadditionmodel->arrStatus[$processmodel->status];
				$processadditionarr['created_at'] =  date($date_format,$processmodel->created_at);
				$processadditionarr['created_by'] = $processmodel->createdbydata->first_name." ".$processmodel->createdbydata->last_name;
			

				$Certificatemodel = new Certificate();
				$unitsprocess = [];
				$unitsprocessname = [];
				$unitsprocessdetails = [];
				if(isset($data['id']) && $data['id']){



					$appmodel = ProcessAddition::find()->alias('t');
					//$appmodel = $appmodel->join('inner join', 'tbl_audit as audit','audit.app_id =t.id');
					//$appmodel = $appmodel->join('inner join', 'tbl_certificate as cert','audit.id =cert.audit_id and cert.status="'.$Certificatemodel->arrEnumStatus['certificate_generated'].'" and cert.certificate_valid_until>="'.date('Y-m-d').'" ');
					$appmodel = $appmodel->andWhere(['t.id'=>$data['id']]);
					if($resource_access != 1){
						/*
						if($user_type==2){
							$appmodel = $appmodel->andWhere(['t.customer_id' => $userid]);
						}else if($user_type==3){
							$appmodel = $appmodel->andWhere(['t.franchise_id' => $userid]);
						}else if($user_type==1){
							$appmodel = $appmodel->andWhere(['t.franchise_id' => $franchiseid]);
						}else{
							return $responsedata;
						}
						*/
					}

					$appmodel = $appmodel->one();

					if($appmodel != null)
					{

						$model = $appmodel->application;
						$exclude = 1;
						$excludeunits = [];
						$unit_names = [];
						if(count($appmodel->additionunit)>0){
							foreach($appmodel->additionunit as $unitsdata){
								$excludeunits[] = $unitsdata->unit_id;

								if($unitsdata->applicationunit->unit_type == 1){
									$unit_names[] = $appmodel->applicationaddress->unit_name;
								}else{
									$unit_names[] = $unitsdata->applicationunit->name;
								}
												
								$unitsprocess[$unitsdata->unit_id] = [];
								if(count($unitsdata->unitprocess)>0){
									foreach($unitsdata->unitprocess as $processdetails){
										$unitsprocess[$unitsdata->unit_id][] = $processdetails->process_id;
										$unitsprocessdetails[$unitsdata->unit_id][] = ['id'=>$processdetails->process_id, 'name'=> $processdetails->process_name];
										$unitsprocessname[] = $processdetails->process_name;//$processdetails->process->name;
									}
								}

							}
						}
						
						$processadditionarr['company_name'] = $appmodel->applicationaddress->company_name;//$model->companyname;
						$processadditionarr['units'] = implode(", ",$unit_names);
						//$excludeunits = 
						/*
						$excludeunits= [];
						if(isset($data['exclude']) && $data['exclude']){
							$exclude=1;

							if(isset($data['units']) && $data['units']){
								$excludeunits= explode(',',$data['units']);
							}
						}
						*/
						$unitprocessdetails = 
						$appdetails = $this->getApplicationDetail($model,$excludeunits,$processmodel);
						$responsedata=array('status'=>1,'appdata'=>$appdetails,'processIds'=>$unitsprocess,'processnames'=>$unitsprocessname,'unitsprocessdetails'=>$unitsprocessdetails);

					}
				}else{
					$appmodel = Application::find()->alias('t');
					//$appmodel = $appmodel->join('inner join', 'tbl_audit as audit','audit.app_id =t.id');
					//$appmodel = $appmodel->join('inner join', 'tbl_certificate as cert','audit.id =cert.audit_id and cert.status="'.$Certificatemodel->arrEnumStatus['certificate_generated'].'" and cert.certificate_valid_until>="'.date('Y-m-d').'" ');
					$appmodel = $appmodel->andWhere(['t.id'=>$data['app_id']]);
					if($resource_access != 1){
						/*
						if($user_type==2){
							$appmodel = $appmodel->andWhere(['t.customer_id' => $userid]);
						}else if($user_type==3){
							$appmodel = $appmodel->andWhere(['t.franchise_id' => $userid]);
						}else if($user_type==1){
							$appmodel = $appmodel->andWhere(['t.franchise_id' => $franchiseid]);
						}else{
							return $responsedata;
						}
						*/
					}

					$appmodel = $appmodel->one();

					if($appmodel !== null)
					{

						//$excludeunits = 
						
						$excludeunits= [];
						if(isset($data['exclude']) && $data['exclude']){
							$exclude=1;

							if(isset($data['units']) && $data['units']){
								$excludeunits= explode(',',$data['units']);
							}
						}
						
						$appdetails = $this->getApplicationDetail($appmodel,$excludeunits,$processmodel);
						$responsedata=array('status'=>1,'appdata'=>$appdetails);

					}
				}
			}
			$responsedata['additionDetails'] = $processadditionarr;
		}
		return $this->asJson($responsedata);
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
			//$processmodel->applicationaddress->
						
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
							$unitprdarr=array();
							//$unitprdarr[]=($unitP->product?$unitP->product->name:'');
							//$unitprdarr['pdt_index']=$pdt_index_list[$unitP->product_id];
							$unitprdarr['pdt_id']=$unitP->application_product_standard_id;
							//$unitprdarr['pdt_index']=($unitP->product?$unitP->product->name:'');

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
							$unitstddetailssarrtemp = [];
							$unitstdidsarr[]=$unitstd->standard_id;
							
							$unitstddetailssarrtemp['id']=$unitstd->standard_id;
							$unitstddetailssarrtemp['name']=$unitstd->standard->name;

							$unitstddetailssarr[]=$unitstddetailssarrtemp;
						}
					}
					$unitarr["standards"]=$unitstdidsarr;
					$unitarr["standarddetails"]=$unitstddetailssarr;
										
					$unitprocess_data=[];
					$unitprocessnames=[];
					$unitpcsarr=array();
					$unitpcsarrobj=array();

					$unitprocess=$unit->unitprocess;
					if(count($unitprocess)>0)
					{
						$icnt=0;
						foreach($unitprocess as $unitPcs)
						{
							//if($unitPcs->process_type==0){
								$unitpcsarr=array();
								$unitpcsarr['id']=$unitPcs->process_id;
								$unitpcsarr['name']=$unitPcs->process_name;//$unitPcs->process->name;
								$unitprocess_data[]=$unitpcsarr;
								$unitprocessnames[]=$unitPcs->process_name;//$unitPcs->process->name;

								$icnt++;
							//}							
						}
					}					
					$unitarr["process"]=$unitprocessnames;
					$unitarr["process_ids"]=$unitprocess_data;
					$unitarr["process_data"]=$unitpcsarrobj;				

					$unitdetailsarr[]=$unitarr;
				}
				$resultarr["units"]=$unitdetailsarr;
			}
			
		}
		return $resultarr;
	}
	public function actionGetAppunitdata()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$unitarr = Yii::$app->globalfuns->getAppunitdata($data);
			/*
			$appmodel = ApplicationUnit::find()->select('id,name')->where(['app_id' => $data['id']])->all();
			if(count($appmodel)>0)
			{
				$unitarr = array();
				foreach($appmodel as $unit)
				{
					$unitarr[] = ['id'=> $unit->id, 'name' => $unit->name];
				}
			}
			*/
			$responsedata=array('status'=>1,'unitdata'=>$unitarr);
		}
		return $this->asJson($responsedata);
	}

	
	public function actionCreate()
	{
		if(!Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->isCustomer())
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data){
			
			if(!Yii::$app->userrole->isValidApplication($data['app_id']))
			{
				return false;
			}
			
			$connection = Yii::$app->getDb();
			$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
			$update =0;
			if(isset($data['id']))
			{
				$model = ProcessAddition::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new ProcessAddition();
					$model->created_by = $userid;
				}else{
					$punit = ProcessAdditionUnit::find()->where(['process_addition_id'=>$model->id])->all();
					if(count($punit)>0){
						foreach($punit as $unitdata)
						{
							ProcessAdditionUnitProcess::deleteAll(['process_addition_unit_id' => $unitdata->id]);
						}
					}

					//ProcessAdditionUnit::deleteAll(['process_addition_id' => $model->id]);
					
					$model->updated_by = $userid;
					$update =1;
				}
			}else{
				$model = new ProcessAddition();
				$model->created_by = $userid;
				$model->status = 0;
			}

			 
			$model->app_id= $data['app_id'];
			$model->address_id = Yii::$app->globalfuns->getAppCurrentAddressId($data['app_id']);
			
			
			
			if($model->validate() && $model->save())
			{
				$modelID = $model->id;
				if($update){
					if(is_array($data['units']) && count($data['units'])>0){
						foreach($data['units'] as $units){
							$additionunit = ProcessAdditionUnit::find()->where(['process_addition_id'=>$modelID,'unit_id'=>$units['unit_id']])->one();							
							if($additionunit !== null)
							{
								//$pcstandard = ApplicationUnitStandard::find()->where(['unit_id'=>$units['unit_id']])->all();

								$datass = ['unit_id'=>$units['unit_id']];
								$pcstandard = Yii::$app->globalfuns->getAppUnitStandards($datass);
								if(count($pcstandard)>0)
								{
									foreach($pcstandard as $ustandard)
									{
										if(is_array($units['process']) && count($units['process'])>0 )
										{
											$process_addition_unit_id = $additionunit->id;
											foreach($units['process'] as $processID){
												
												$unitprocess = new ProcessAdditionUnitProcess();
												$unitprocess->process_addition_unit_id = $process_addition_unit_id;
												$unitprocess->process_id = $processID;
												$unitprocess->standard_id = $ustandard['id'];
												$Process = Process::find()->where(['id'=>$processID])->one();
												if($Process !== null){
													$unitprocess->process_name = $Process->name;
												}
												$unitprocess->save();
											}								
										}
									}
								}	
							}
						}
					}
				}else{
					if(is_array($data['units']) && count($data['units'])>0){
						foreach($data['units'] as $units){
							$additionunit = new ProcessAdditionUnit();
							$additionunit->unit_id = $units['unit_id'];
							$additionunit->process_addition_id = $modelID;
							if($additionunit->validate() && $additionunit->save())
							{
								if(is_array($units['process']) && count($units['process'])>0 )
								{
									$process_addition_unit_id = $additionunit->id;
									
									$datass = ['unit_id'=>$units['unit_id']];
									$pcstandard = Yii::$app->globalfuns->getAppUnitStandards($datass);
									//$pcstandard = ApplicationUnitStandard::find()->where(['unit_id'=>$units['unit_id']])->all();
									if(count($pcstandard)>0)
									{
										foreach($pcstandard as $ustandard)
										{
											foreach($units['process'] as $processID)
											{
												$unitprocess = new ProcessAdditionUnitProcess();
												$unitprocess->process_addition_unit_id = $process_addition_unit_id;
												$unitprocess->process_id = $processID;
												$unitprocess->standard_id = $ustandard['id'];
												$Process = Process::find()->where(['id'=>$processID])->one();
												if($Process !== null){
													$unitprocess->process_name = $Process->name;
												}
												$unitprocess->save();
											}								
										}
									}	
								}
							}
						}
					}
				}
				
				$new_app_id = '';
				if($data['type']=='addition' || (isset($data['new_app_id']) && $data['new_app_id']>0)){
					if($data['type']=='addition'){
						$model->status = 1;
						$model->save();
					}
					
					$Application = new Application();

					if(isset($data['new_app_id']) && $data['new_app_id']>0){
						$new_app_id= $data['new_app_id'];
						$Application = Application::find()->where(['id'=>$new_app_id])->one();
						$Application->status = $Application->arrEnumStatus['submitted'];
						$Application->save();
					}else{
						$clonedata= [];
						//$modelID 
						$clonedata['process_addition_id'] = $modelID;
						$clonedata['id'] = $data['app_id'];
						$clonedata['audit_type'] = $Application->arrEnumAuditType['process_addition'];
						$cloneres = $Application->cloneApplication($clonedata);
						$new_app_id= $cloneres['new_app_id'];
					}
						


					

					$model->new_app_id = $new_app_id;
					$model->status = $Application->arrEnumStatus['submitted'];	
					$model->save();

					if(isset($data['new_app_id']) && $data['new_app_id']>0){

						$additionunit = ProcessAdditionUnit::find()->where(['process_addition_id'=>$modelID])->all();
						if(count($additionunit) >0 ){
							foreach($additionunit as $additionunitobj){
								if(count($additionunitobj->unitprocess) > 0){
									//$newunitprocess = new ApplicationUnitProcess();
									$newunitid= $additionunitobj->new_unit_id;
									ApplicationUnitProcess::deleteAll(['unit_id' => $newunitid,'process_type'=>1]);

									foreach($additionunitobj->unitprocess as $objunitprocess){
										$newunitprocess = new ApplicationUnitProcess();
										$newunitprocess->unit_id = $newunitid;
										$newunitprocess->process_id = $objunitprocess->process_id;
										$newunitprocess->process_type = 1;
										$newunitprocess->process_name = $objunitprocess->process_name;
										$newunitprocess->save();
									}
								}
							}
						}

					}else{
						if(is_array($cloneres['units']) && count($cloneres['units'])>0){
							foreach($cloneres['units'] as $oldid=> $newid){
								$additionunit = ProcessAdditionUnit::find()->where(['unit_id'=>$oldid,'process_addition_id'=>$modelID])->one();
								if($additionunit !== null){
									$additionunit->new_unit_id = $newid;
									$additionunit->save();
									if(count($additionunit->unitprocess) > 0){
										//$ApplicationUnitStandard = ApplicationUnitStandard::find()->where(['unit_id'=>$oldid])->all();
										$datass = ['unit_id'=>$oldid];
										$pcstandard = Yii::$app->globalfuns->getAppUnitStandards($datass);
										if(count($pcstandard)>0){
											foreach($pcstandard as $appunitstd){
												foreach($additionunit->unitprocess as $objunitprocess){
													$newunitprocess = new ApplicationUnitProcess();
													$newunitprocess->unit_id = $newid;
													$newunitprocess->process_id = $objunitprocess->process_id;
													$newunitprocess->process_name = $objunitprocess->process_name;
													$newunitprocess->standard_id = $appunitstd['id'];
													$newunitprocess->process_type = 1;
													$newunitprocess->save();
												}
											}
										}
										
									}
								}
							}
						}
					}

					
				}

				$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'process_addition'])->one();
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

				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Process updated successfully','new_app_id'=>$new_app_id);
				}else{
					$responsedata=array('status'=>1,'message'=>'Process updated successfully','new_app_id'=>$new_app_id);
				}
			}
		}
		
		return $this->asJson($responsedata);
	}


	public function actionCommonUpdate()
	{
		if(!Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->isCustomer())
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data)
		{
			if(isset($data['id']) && $data['id']>0)
			{
				$ProcessAddition = ProcessAddition::find()->where(['id'=>$data['id']])->one();
				if($ProcessAddition !== null)
				{
					if(!Yii::$app->userrole->isValidApplication($ProcessAddition->app_id))
					{
						return false;
					}
					
					$unitdata = ProcessAdditionUnit::find()->where(['process_addition_id'=>$ProcessAddition->id])->all();
					if(count($unitdata)>0)
					{
						foreach($unitdata as $dataobj)
						{
							ProcessAdditionUnitProcess::deleteAll(['process_addition_unit_id' => $dataobj->id]);
							$dataobj->delete();
						}
					}
					$ProcessAddition->delete();
					$responsedata=array('status'=>1,'message'=>'Process deleted successfully');
				}				
			}
		}
		
		return $this->asJson($responsedata);
	}
	
}
