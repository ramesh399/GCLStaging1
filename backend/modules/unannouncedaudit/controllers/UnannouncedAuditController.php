<?php
namespace app\modules\unannouncedaudit\controllers;

use Yii;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationUnitStandard;
use app\modules\application\models\ApplicationUnitProcess;

use app\modules\application\models\ApplicationUnitBusinessSector;
use app\modules\application\models\ApplicationUnitBusinessSectorGroup;
use app\modules\master\models\User;
use app\modules\master\models\Standard;
use app\modules\master\models\BusinessSectorGroup;

use app\modules\certificate\models\Certificate;
use app\modules\master\models\AuditReviewerRiskCategory;

use app\modules\changescope\models\ProcessAddition;
use app\modules\changescope\models\UnitAddition;
use app\modules\changescope\models\StandardAddition;

use app\modules\unannouncedaudit\models\UnannouncedAuditApplication;
use app\modules\unannouncedaudit\models\UnannouncedAuditApplicationStandard;
use app\modules\unannouncedaudit\models\UnannouncedAuditApplicationUnit;
use app\modules\unannouncedaudit\models\UnannouncedAuditApplicationUnitStandard;
use app\modules\unannouncedaudit\models\UnannouncedAuditApplicationUnitBusinessSector;
use app\modules\unannouncedaudit\models\UnannouncedAuditApplicationUnitBusinessSectorGroup;
use app\modules\unannouncedaudit\models\UnannouncedAuditApplicationUnitProcess;

use app\modules\audit\models\Audit;


use yii\web\NotFoundHttpException;
use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class UnannouncedAuditController extends \yii\rest\Controller
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
	
	public function actionIndex()
    {
		if(!Yii::$app->userrole->hasRights(array('application_review')))
		{
			return false;
		}
		
		$UnannouncedAuditmodel = new UnannouncedAuditApplication();
		$appsmodel=new Application();	
		$post = yii::$app->request->post();
		
		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters = $userData['is_headquarters'];
		
		
		$resource_access=$userData['resource_access'];				
		//$model = Application::find()->alias('t');
		
		$model = UnannouncedAuditApplication::find()->alias('unannouncedaudit');
		//$model = $model->joinWith('applicationaddress as appaddress');
		$model = $model->innerJoinWith('application as t');		
		
		
		if($user_type== Yii::$app->params['user_type']['user'] && $resource_access!=1)
		{
			$model = $model->andWhere(' (unannouncedaudit.created_by="'.$userid.'") ');
		}
		
		
		if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1)
		{
			$model = $model->andWhere('(t.franchise_id="'.$franchiseid.'")');
		}	
		
		$model = $model->join('left join', 'tbl_application_standard as app_standard','app_standard.app_id =t.id ');
		//$model = $model->join('inner join', 'tbl_unannounced_audit_application as unannounced_audit','unannounced_audit.app_id =t.id ');
				
		if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
		{
			$model = $model->andWhere(['app_standard.standard_id'=> $post['standardFilter']]);
		}

		if(isset($post['riskFilter']) && is_array($post['riskFilter']) && count($post['riskFilter'])>0)
		{
			$model = $model->andWhere(['t.risk_category'=> $post['riskFilter']]);
		}

		if(isset($post['statusFilter']) && is_array($post['statusFilter']) && count($post['statusFilter'])>0)
		{
			$model = $model->andWhere(['unannouncedaudit.status'=> $post['statusFilter']]);
		}

		if(isset($post['from_date']))
		{
			$model = $model->andWhere(['>=','unannouncedaudit.created_at', strtotime($post['from_date'])]);			
		}

		
		if(isset($post['to_date']))
		{
			$model = $model->andWhere(['<=','unannouncedaudit.created_at', strtotime($post['to_date'])]);			
		}
		
		
		$model = $model->groupBy(['unannouncedaudit.id']);
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
			$pageSize = $post['pageSize']; 
			
			
			if(isset($post['searchTerm']) && $post['searchTerm']!='')
			{
				$model = $model->innerJoinWith('currentaddress as caddress');
				
				$statusarray=array_map('strtolower', $UnannouncedAuditmodel->arrStatus);
				$searchTerm = $post['searchTerm'];
				$search_status = array_search(strtolower($searchTerm),$statusarray);
				if($search_status===false)
				{
					$search_status = '';
				}
				$model = $model->andFilterWhere([
					'or',
					['like', 'caddress.company_name', $searchTerm],
					['like', 'caddress.telephone', $searchTerm],
					['like', 'caddress.first_name', $searchTerm],
					['like', 'caddress.last_name', $searchTerm],
					['like', 'date_format(FROM_UNIXTIME(unannouncedaudit.created_at), \'%b %d, %Y\' )', $searchTerm],	
					['unannouncedaudit.status'=>$search_status]										
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
				$model = $model->orderBy(['created_at' => SORT_DESC]);
			}
			
            $model = $model->limit($pageSize)->offset($page);
		}
		else
		{
			$totalCount = $model->count();
		}
		
		$app_list=array();
		$model = $model->all();		
		
		if(count($model)>0)
		{
			
			foreach($model as $application)
			{
				
				$data=array();
				$data['app_id']=$application->app_id;
				$data['id']=$application->id;
				$data['code']=$application->application->code;
				$data['company_name']=$application->application->companyname;
				
				$data['email_address']=$application->application->emailaddress;
				$data['customer_number']=$application->application->customer->customer_number;
				
				$data['first_name']=$application->application->firstname;
				$data['telephone']=$application->application->telephone;
				$data['created_at']=date($date_format,$application->created_at);
				$data['status']=$application->audit?$application->audit->status:'';
				$data['status_label']= isset($application->audit->arrStatus[$data['status']])?$application->audit->arrStatus[$data['status']]:'Open';//$UnannouncedAuditmodel->arrStatus[$application->status];
				//$data['status_label_color']=$UnannouncedAuditmodel->arrStatusColor[$application->status];
			
				
				$arrAppStd=array();
				/*
				$appStd = $application->application->applicationstandard;
				if(count($appStd)>0)
				{	
					$standardarr = [];
					foreach($appStd as $app_standard)
					{
						$stddata = [];
						$stddata['id'] = $app_standard->standard_id;
						$stddata['name'] = $app_standard->standard->code;
						$arrAppStd[] = $app_standard->standard->code;
						$standardarr[] = $stddata;
					}
				}
				*/
				$appStd=$application->unannouncedauditstandard;
				if(count($appStd)>0)
				{	
					foreach($appStd as $app_standard)
					{
						$stddata = [];
						$stddata['id'] = $app_standard->standard_id;
						$stddata['name'] = $app_standard->standard->code;
						$arrAppStd[]=$app_standard->standard->code;
						$standardarr[] = $stddata;
					}
				}

				$data['application_standard'] = implode(', ',$arrAppStd);

				$data['audit_standard'] = $standardarr;
				
				//$data['risk_category'] = $application->audit->risk_category;
				//$data['risk_category_label'] = $riskoptions[$data['risk_category']]['name'];
				
				$data['risk_category'] = $application->application->risk_category;				
				$data['risk_category_label'] = ($application->application->riskcategory ? $application->application->riskcategory->name : 'NA');
																
				$app_list[]=$data;
			}
		}
		
		return ['applications'=>$app_list,'total'=>$totalCount];
	}


	public function actionGetRisklist()
	{
		$UnannouncedAuditmodel = new UnannouncedAuditApplication();
		$riskoptions = AuditReviewerRiskCategory::find()->select('id,name')->where(['status'=>0])->asArray()->all();
		return ['risklist'=>$riskoptions,'statuslist'=>$UnannouncedAuditmodel->arrStatus];
	}
	
	public function appAddressRelation($model)
	{
		$model = $model->joinWith('applicationaddress as appaddress');
	}
	
	public function actionCompanyList()
    {
		if(!Yii::$app->userrole->hasRights(array('application_review')))
		{
			return false;
		}
		
        $post = yii::$app->request->post();
		
		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		
		$resource_access=$userData['resource_access'];
		$appsmodel=new Application();					
		$model = Application::find()->where(['t.audit_type'=>array(1,2),'t.overall_status'=>$appsmodel->arrEnumOverallStatus['certificate_generated']])->alias('t');
		
		$model = $model->join('inner join', 'tbl_application_standard as app_standard','app_standard.app_id =t.id ');
		$model = $model->join('inner join', 'tbl_certificate as certificate','certificate.parent_app_id =t.id and app_standard.standard_id=certificate.standard_id and certificate.certificate_status=0 ');

		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1)
		{
			$model = $model->andWhere('(t.franchise_id="'.$franchiseid.'")');
		}	
			
		if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
		{
			$model = $model->andWhere(['app_standard.standard_id'=> $post['standardFilter']]);			
		}

		if(isset($post['riskFilter']) && is_array($post['riskFilter']) && count($post['riskFilter'])>0)
		{
			$model = $model->andWhere(['t.risk_category'=> $post['riskFilter']]);
		}
		
		$model = $model->groupBy(['t.id']);
		$appAddressJoinWithStatus=false;
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
			$pageSize = $post['pageSize']; 
			$statusarray=array_map('strtolower', $appsmodel->arrStatus);
			
			if(isset($post['searchTerm']) && $post['searchTerm'] !='')
			{
				$model = $model->joinWith('applicationaddress as appaddress');
				$searchTerm = $post['searchTerm'];
				$search_status = array_search(strtolower($searchTerm),$statusarray);
				if($search_status===false)
				{
					$search_status = '';
				}
				
				$appAddressJoinWithStatus=true;
				$this->appAddressRelation($model);
				
				$model = $model->andFilterWhere([
					'or',
					['like', 't.code', $searchTerm],
					['like', 'appaddress.company_name', $searchTerm],
					['like', 'appaddress.first_name', $searchTerm],
					['like', 'appaddress.last_name', $searchTerm],
					['like', 'appaddress.telephone', $searchTerm],
					['like', 'date_format(FROM_UNIXTIME(t.created_at), \'%b %d, %Y\' )', $searchTerm],	
					['status'=>$search_status]	
					//['like', 'status', array_search($searchTerm,$statusarray)],										
				]);			
			}
			$totalCount = $model->count();
			
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				if($post['sortColumn']=='company_name')
				{
					if(!$appAddressJoinWithStatus)
					{
						$this->appAddressRelation($model);
					}
					
					$model = $model->orderBy(['appaddress.company_name'=>$sortDirection]);
				}else{
					$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
				}
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
		
		$app_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $application)
			{
				$data=array();
				$data['id']=$application->id;
				$data['code']=$application->code;
				$data['company_name']=$application->companyname;
				$data['address']=$application->address;
				$data['zipcode']=$application->zipcode;
				$data['city']=$application->city;
				$data['title']=$application->title;
				$data['first_name']=$application->firstname;
				$data['last_name']=$application->lastname;
				$data['job_title']=$application->jobtitle;
				$data['telephone']=$application->telephone;
				$data['email_address']=$application->emailaddress;				
				$data['customer_number']=$application->customer->customer_number;	
				$data['created_at']=date($date_format,$application->created_at);
				$data['status']=$application->arrStatus[$application->status];
				$data['no_of_tc']=count($application->tcrequest);
				$data['no_of_nc']=0;
				$data['status_id']=$application->status;
				$data['status_label_color']=$application->arrStatusColor[$application->status];
				$data['audit_type']=$application->audit_type;
				$data['audit_type_label']=$application->arrAuditType[$application->audit_type];
				$data['application_unit_count']=count($application->applicationunit);
				$data['process_id']='';
				$data['parent_app_id']= $application->parent_app_id;
				
				$arrAppStd=array();
				
				$appStd=$application->applicationstandardview;
				
				//$appStd = $application->applicationstandard;
				if(count($appStd)>0)
				{	
					$standardarr = [];
					foreach($appStd as $app_standard)
					{
						$Certificate = Certificate::find()->where(['certificate_status'=>0,'parent_app_id'=>$application->id,'standard_id'=>$app_standard->standard_id ])->one();
						if($Certificate !== null){
							$stddata = [];
							$stddata['id'] = $app_standard->standard_id;
							$stddata['name'] = $app_standard->standard->code;
							$arrAppStd[]=$app_standard->standard->code;
							$standardarr[] = $stddata;
						}
						
					}
				}					
				$data['application_standard']=implode(', ',$arrAppStd);
				$data['standardlist'] = $standardarr;	
				
				$data['risk_category'] = $application->risk_category;
				$data['risk_category_label'] = ($application->riskcategory ? $application->riskcategory->name : 'NA');

				$app_list[]=$data;
			}
		}
		
		return ['applications'=>$app_list,'total'=>$totalCount];
	}

	public function actionGetUnit()
    {
		$data = yii::$app->request->post();
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		if($data)
		{
			$unitmodel = ApplicationUnit::find()->select(['t.id,t.name as name, group_concat(distinct unitappstandard.standard_id order by unitappstandard.standard_id asc) as standardids'])->alias('t')->where(['t.app_id'=>$data['app_id']]);
			$unitmodel = $unitmodel->innerJoinWith(['unitappstandard as unitappstandard']);

			$unitmodel = $unitmodel->andWhere(['unitappstandard.standard_id'=> $data['standard_id'] ]);
			$unitmodel = $unitmodel->groupBy(['t.id']);
			$unitmodel = $unitmodel->having(['standardids'=> implode(',', $data['standard_id']) ])->all();
			if(count($unitmodel) >0)
			{	
				$unitarr = [];
				foreach($unitmodel as $unitval)
				{
					$unit = [];
					$unit['id'] = $unitval->id;
					$unit['name'] = $unitval->name;
					//$unit['name'] = $unitval['name'];
					$unitarr[] = $unit;
						
				} 
				$responsedata = array('status'=>1,'units'=>$unitarr);	
			}
		}
		return $this->asJson($responsedata);
	}

	public function actionView()
    {
		$unitmodel = new ApplicationUnit();
		$data = yii::$app->request->post();
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');

		if($data)
		{
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$franchiseid=$userData['franchiseid'];
			$is_headquarters = $userData['is_headquarters'];
			
			$connection = Yii::$app->getDb();
			$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
			
			$model = UnannouncedAuditApplication::find()->alias('t')->where(['t.id'=>$data['id']]);
			$model = $model->join('inner join', 'tbl_application as app','t.app_id=app.id');
			if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1 )
			{
				$model = $model->andWhere('app.franchise_id="'.$franchiseid.'"');
			}
			$model = $model->one();
			if ($model !== null)
			{
				$resultarr=array();
				$resultarr["id"]=$model->id;
				$resultarr["app_id"]=$model->id;
				$resultarr["company_name"]=$model->currentaddress->company_name;
				$resultarr["status"]=$model->status;
				$resultarr["status_label"]=$model->arrStatus[$model->status];
				$resultarr["created_at"]=date($date_format,$model->created_at);
				$resultarr["created_by_label"]=$model->createdbydata->first_name.' '.$model->createdbydata->last_name;
				$resultarr['risk_category'] = $model->application->risk_category;
				$resultarr['risk_category_label'] = ($model->application->riskcategory ? $model->application->riskcategory->name : 'NA');

				$appStd = $model->unannouncedauditstandard;
				if(count($appStd)>0)
				{	
					$standardarr = [];
					foreach($appStd as $app_standard)
					{
						$arrAppStd[] = $app_standard->standard->code;
					}
				}
				$resultarr['standard_label'] = implode(', ',$arrAppStd);



				$auditunits = $model->unannouncedauditunit;
				if(count($auditunits)>0)
				{
					$unitdataarr = [];
					foreach($auditunits as $audit_unit)
					{
						$unitarr = [];
						$unitarr['name'] = $audit_unit->applicationunit->name;
						$unitarr['unit_type'] = $audit_unit->applicationunit->unit_type;
						$unitarr['unit_type_label'] = $unitmodel->unit_type_list[$audit_unit->applicationunit->unit_type];
						$unitarr['address'] = $audit_unit->applicationunit->address;
						$unitarr['zipcode'] = $audit_unit->applicationunit->zipcode;
						$unitarr['city'] = $audit_unit->applicationunit->city;
						$unitarr['no_of_employees'] = $audit_unit->applicationunit->no_of_employees;
						$unitarr['country_name'] = ($audit_unit->applicationunit->country_id!="")?$audit_unit->applicationunit->country->name:"";
						$unitarr['state_name'] = ($audit_unit->applicationunit->state_id!="")?$audit_unit->applicationunit->state->name:"";

						//$unitprocess = $audit_unit->applicationunit->unitprocessnormal;

						$unitprocess = $audit_unit->unannouncedauditunitprocess;
						if(count($unitprocess)>0)
						{
							$unitprocess_data=[];
							$unitprocessnames=[];
							foreach($unitprocess as $unitPcs)
							{
								$unitpcsarr=array();
								$unitpcsarr['id']=$unitPcs->process_id;
								$unitpcsarr['name']=$unitPcs->process_name;
								$unitprocess_data[]=$unitpcsarr;
								$unitprocessnames[]=$unitPcs->process_name;
							}

							$unitarr["process"]=$unitprocessnames;
							$unitarr["process_ids"]=$unitprocess_data;
						}

						$appStd = $audit_unit->unannouncedauditunitstandard;
						if(count($appStd)>0)
						{
							$standardarr = [];
							foreach($appStd as $app_standard)
							{
								$stddata = [];
								$stddata['id'] = $app_standard->standard_id;
								$stddata['name'] = $app_standard->standard->code;
								$arrAppStd[] = $app_standard->standard->code;
								$standardarr[] = $stddata;
							}
						}

						$appbsector = $audit_unit->unannouncedauditunitbsector;
						if(count($appStd)>0)
						{
							$bsectorarr = [];
							foreach($appbsector as $app_bsector)
							{
								$bsectordata = [];
								$bsectordata['id'] = $app_bsector->business_sector_id;
								$bsectordata['name'] = $app_bsector->business_sector_name;

								$appbsectorgroup = $app_bsector->unannouncedauditunitbsectorgroup;
								$bsectorgrouparr = [];
								foreach($appbsectorgroup as $app_bsector_group)
								{
									$bsectorgroupdata = [];
									$bsectorgroupdata['id'] = $app_bsector_group->business_sector_group_id;
									$bsectorgroupdata['name'] = $app_bsector_group->business_sector_group_name;
									$bsectorgrouparr[] = $bsectorgroupdata;
								}

								$bsectordata['bsectorgroup'] = $bsectorgrouparr;
								$bsectorarr[] = $bsectordata;
							}
						}

						$unitarr['unit_standards_label'] = implode(', ',$arrAppStd);
						$unitarr['unit_standards'] = $standardarr;
						$unitarr['unit_bsectors'] = $bsectorarr;
						

						$unitdataarr[] = $unitarr;
					}
					$resultarr['units'] = $unitdataarr;	
					
				}

				

				$responsedata = array('status'=>1,'data'=>$resultarr);

			}
		}
		return $this->asJson($responsedata);
	}


	public function actionSaveUnannouncedAudit()
    {
		if(!Yii::$app->userrole->hasRights(array('application_review')))
		{
			return false;
		}
		
		$data = yii::$app->request->post();
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		if($data)
		{
			$auditmodel = new UnannouncedAuditApplication();
			$auditmodel->app_id = $data['app_id'];
			$auditmodel->created_by = $userData['userid'];
			if($auditmodel->validate() && $auditmodel->save())
			{
				if(isset($data['standard_id']) && count($data['standard_id'])>0)
				{
					foreach($data['standard_id'] as $standard_id)
					{
						$standardmodel = new UnannouncedAuditApplicationStandard();
						$standardmodel->unannounced_audit_app_id = $auditmodel->id;
						$standardmodel->standard_id = $standard_id;
						$standardmodel->save();
					}
				}

				if(isset($data['unit_id']) && count($data['unit_id'])>0)
				{
					foreach($data['unit_id'] as $unit_id)
					{
						$unitmodel = new UnannouncedAuditApplicationUnit();
						$unitmodel->unannounced_audit_app_id = $auditmodel->id;
						$unitmodel->unit_id = $unit_id;
						$unitmodel->save();

						$unitstdmodel = ApplicationUnitStandard::find()->where(['unit_id'=>$unit_id,'standard_id'=>$data['standard_id']])->all();
						if(count($unitstdmodel)>0)
						{
							foreach($unitstdmodel as $unitstd)
							{
								$unitstandardmodel = new UnannouncedAuditApplicationUnitStandard();
								$unitstandardmodel->unannounced_audit_app_unit_id = $unitmodel->id;
								$unitstandardmodel->standard_id = $unitstd->standard_id;
								$unitstandardmodel->save();


								$unitprocessmodel = ApplicationUnitProcess::find()->where(['unit_id'=>$unit_id,'standard_id'=>$unitstd->standard_id ])->all();
								if(count($unitprocessmodel)>0)
								{
									foreach($unitprocessmodel as $unitprocess)
									{
										$unitstandardmodel = new UnannouncedAuditApplicationUnitProcess();
										$unitstandardmodel->unannounced_audit_app_unit_id = $unitmodel->id;
										$unitstandardmodel->process_id = $unitprocess->process_id;
										$unitstandardmodel->process_name = $unitprocess->process_name;
										$unitstandardmodel->standard_id = $unitstd->standard_id;
										$unitstandardmodel->save();
									}
								}
							}
						}						
						
						//->innerJoinWith(['group as group'])
						$bsectormodel = ApplicationUnitBusinessSectorGroup::find()->alias('t')->where(['t.unit_id'=>$unit_id])->all();
						if(count($bsectormodel)>0)
						{
							foreach($bsectormodel as $unitbgroup)
							{
								$BusinessSectorGroup = BusinessSectorGroup::find()->where([ 'id'=>$unitbgroup->business_sector_group_id, 'standard_id'=>$data['standard_id'] ])->one();

								if($BusinessSectorGroup !== null)
								{
									$UnannouncedAuditApplicationUnitBusinessSector = UnannouncedAuditApplicationUnitBusinessSector::find()->where(['business_sector_id'=>$unitbgroup->group->business_sector_id, 'unannounced_audit_app_unit_id'=>$unitmodel->id  ])->one();
									if($UnannouncedAuditApplicationUnitBusinessSector === null){
										$UnannouncedAuditApplicationUnitBusinessSector = new UnannouncedAuditApplicationUnitBusinessSector();
										$UnannouncedAuditApplicationUnitBusinessSector->unannounced_audit_app_unit_id = $unitmodel->id;
										$UnannouncedAuditApplicationUnitBusinessSector->business_sector_id = $unitbgroup->group->business_sector_id;
										$UnannouncedAuditApplicationUnitBusinessSector->business_sector_name =  $unitbgroup->group->businesssector->name;
										$UnannouncedAuditApplicationUnitBusinessSector->save();
									}
									$bsectorstandardmodel = new UnannouncedAuditApplicationUnitBusinessSectorGroup();
									$bsectorstandardmodel->unit_business_sector_id = $UnannouncedAuditApplicationUnitBusinessSector->id;
									$bsectorstandardmodel->business_sector_group_id = $unitbgroup->business_sector_group_id;
									$bsectorstandardmodel->business_sector_group_name = $unitbgroup->group->group_code;
									$bsectorstandardmodel->standard_id = $unitbgroup->standard_id;
									$bsectorstandardmodel->unannounced_audit_app_unit_id = $unitmodel->id;
									$bsectorstandardmodel->save();
								}								
							}
						}						
					}					
				}

				$Audit = new Audit();
				$Audit->app_id = $data['app_id'];
				$Audit->status = 0;
				$Audit->created_by = $userData['userid'];
				$Audit->audit_type = 2;
				$Audit->followup_status = 0;
				if($Audit->save()){
					$auditmodel->audit_id = $Audit->id;
					$auditmodel->save();
				}
				
				$responsedata = array('status'=>1,'message'=>"Unannounced Audit Saved Successfully");	
			}
		}
		return $this->asJson($responsedata);
	}
	
}
