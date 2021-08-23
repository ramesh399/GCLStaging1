<?php
namespace app\modules\changescope\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use app\modules\application\models\Application;
use app\modules\certificate\models\Certificate;
use app\modules\application\models\ApplicationUnit;

use app\modules\changescope\models\UnitAddition;
use app\modules\master\models\Standard;
use app\modules\master\models\ReductionStandard;
use app\modules\master\models\Process;
use app\modules\master\models\State;
use app\modules\master\models\BusinessSector;
use app\modules\master\models\UserCompanyInfo;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;

use app\modules\changescope\models\UnitAdditionUnit;
use app\modules\changescope\models\UnitAdditionUnitProcess;
use app\modules\changescope\models\UnitAdditionUnitBusinessSector;
use app\modules\changescope\models\UnitAdditionUnitProduct;
use app\modules\changescope\models\UnitAdditionUnitStandard;
use app\modules\changescope\models\UnitAdditionUnitCertifiedStandard;
use app\modules\changescope\models\UnitAdditionUnitCertifiedStandardFile;

use app\modules\application\models\ApplicationUnitProcess;
use app\modules\application\models\ApplicationUnitProduct;
use app\modules\application\models\ApplicationUnitBusinessSector;
use app\modules\application\models\ApplicationUnitStandard;
use app\modules\application\models\ApplicationUnitCertifiedStandard;

use app\modules\application\models\ApplicationUnitCertifiedStandardFile;

use app\modules\audit\models\Audit;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * UnitAdditionController implements the CRUD actions for Product model.
 */
class UnitAdditionController extends \yii\rest\Controller
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
		
		$model = UnitAddition::find()->alias('t');
		$model = $model->innerJoinWith(['application as app']);
		//$model = $model->join('left join', 'tbl_application_change_address as app_address','app_address.parent_app_id=app.parent_app_id');		
		if($resource_access != '1')
		{

			if($user_type== Yii::$app->params['user_type']['customer']){
				$model = $model->andWhere('app.customer_id="'.$userid.'"');
			}else if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$model = $model->andWhere(' app.franchise_id="'.$userid.'" ');
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
				$data['showedit']=($question->status==$question->arrEnumStatus['open'])?1:0;
				
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
						$arrAppUnit[]=$app_unit->name;						
					}
				}					
				$data['addition_unit']=implode(', ',$arrAppUnit);
				
				$data['addition_unit_count']=count($appUnit);
				
				$question_list[]=$data;
			}
		}

		return ['unitadditions'=>$question_list,'total'=>$totalCount];
	}
	

	public function actionGetAppdata()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$apparr = Yii::$app->globalfuns->getAppList();
		$responsedata=array('status'=>1,'appdata'=>$apparr);
		return $this->asJson($responsedata);
	}

	public function actionGetAppunitdata()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$appmodel = ApplicationUnit::find()->select('id,name')->where(['app_id' => $data['id']])->all();
			if(count($appmodel)>0)
			{
				$unitarr = array();
				foreach($appmodel as $unit)
				{
					$unitarr[] = ['id'=> $unit->id, 'name' => $unit->name];
				}
			}
			$responsedata=array('status'=>1,'unitdata'=>$unitarr);
		}
		return $this->asJson($responsedata);
	}
	
	public function actionGetrequestedunitstatus()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$modelUnitAddition = new UnitAddition();						
			$resultarr=array();			
			
			$appmodel = UnitAddition::find()->where(['t.app_id' => $data['id']])->alias('t');
			$appmodel = $appmodel->andWhere('t.status in('.$modelUnitAddition->arrEnumStatus['approved'].','.$modelUnitAddition->arrEnumStatus['failed'].','.$modelUnitAddition->arrEnumStatus['osp_reject'].')');
			$appmodel = $appmodel->all();
			if(count($appmodel)>0)
			{
				$responsedata=array('status'=>1,'unitdata'=>'');
			}
			//$responsedata=array('status'=>0,'message'=>'Unit Addition is in progress.');
			$responsedata=array('status'=>1,'unitdata'=>'');
		}
		return $this->asJson($responsedata);
	}	

	public function actionGetUnitdetails(){
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
				
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		//$model = Application::find();
		/*
		if(isset($data['new_app_id']) && $data['new_app_id']!='' && $data['new_app_id']>0){
			$model = Application::find()->where(['id' => $data['new_app_id']]);
		}else{
			$model = Application::find()->where(['id' => $data['app_id']]);
		}
		*/
		$model = Application::find()->where(['id' => $data['app_id']]);

		if($resource_access != 1){
			if(!Yii::$app->userrole->isAdmin()){
				//($user_type == 2 || $user_type == 3) &&
				if(!Yii::$app->userrole->canViewApplication($data['app_id'])){
					return false;
				}
			}
			/*
			if($user_type== 1 && ! in_array('application_management',$rules)){
				return $responsedata;
			}else if($user_type==3){
				$model = $model->andWhere('franchise_id="'.$userid.'" or created_by="'.$userid.'"');
			}else if($user_type==2){
				$model = $model->andWhere('created_by="'.$userid.'"');
			}
			*/
		}
		$connection = Yii::$app->getDb();
		$model= $model->one();
		if ($model !== null)
		{
			
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$resultarr["code"]=$model->code;
			
			
			$resultarr["app_status"]=$model->status;
			$resultarr["status"]=$model->arrStatus[$model->status];
			$resultarr["franchise_id"]=$model->franchise_id;

			$resultarr['process_id']='';
			$resultarr['parent_app_id']= $model->parent_app_id;
			$resultarr['audit_type']= $model->audit_type;
			
			$appstdarr=[];
			$arrstandardids=[];
			$arrstandardLists=[];
			$appStandard=$model->applicationstandard;
			if(count($appStandard)>0)
			{
				foreach($appStandard as $std)
				{
					$appstdarr[]=($std->standard?$std->standard->name:'');	
					$arrstandardids[]=$std->standard_id;

					$arrstandardLists[]=['id'=>"$std->standard_id",'name'=>$std->standard->name];
				}
			}
			$resultarr["standards"]=$appstdarr;
			$resultarr["standard_ids"]=$arrstandardids;
			$resultarr["standard_lists"]=$arrstandardLists;


			
			$appprdarr=[];
			$appprdarr_details=[];
			$appProduct=$model->applicationproduct;

			$relProducts = Yii::$app->globalfuns->getAppProducts($appProduct);
			$resultarr["products"]=$relProducts['products'];
			$resultarr["productDetails"] = $relProducts['productDetails'];
			$appprdarr_details = $relProducts['appprdarr_details'];
						
			$Unitadditionarr = [];
			/*
			Get Inserted Units
			*/
			if(isset($data['id']) && $data['id']>0){
				$Unitadditionmodel = new UnitAddition();
				$Unitmodel = UnitAddition::find()->where(['id'=>$data['id']])->one();
				if ($Unitmodel !== null)
				{
					$Unitadditionarr = [];
					$Unitadditionarr['company_name'] = $Unitmodel->applicationaddress->company_name;
					$Unitadditionarr['status'] =  $Unitmodel->status;
					$Unitadditionarr['status_label'] =  $Unitadditionmodel->arrStatus[$Unitmodel->status];
					$Unitadditionarr['created_at'] =  date($date_format,$Unitmodel->created_at);
					$Unitadditionarr['created_by'] = $Unitmodel->createdbydata->first_name." ".$Unitmodel->createdbydata->last_name;
				}

				$newUnit = UnitAdditionUnit::find()->where(['unit_addition_id'=>$data['id']])->all();
				if(count($newUnit)>0)
				{
					foreach($newUnit as $unit)
					{
						
						$statelist = State::find()->alias( 't' )->select(['id','name'])->where(['t.country_id'=>$unit->country_id])->asArray()->all();
						
						$unitarr = $unit->toArray();
						
						$unitarr["unit_id"]=$unit->id;
						$unitarr["unit_type_name"]=$unit->unit_type_list[$unit->unit_type];
						$unitarr["state_id_name"]=($unit->state_id!="")?$unit->state->name:"";
						$unitarr["country_id_name"]=($unit->country_id!="")?$unit->country->name:"";
						
						$unitarr["state_list"]= $statelist;
						//$unitarr["unit_type"]=$unit->unit_type;

						$unitnamedetailsarr[$unit->id] = $unit->name;

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
						
						//Business Sector
						$unitbsectoridsarr=array();
						$unitbsarr=array();
						$unitbsarrobj=array();
						$unitbsarrDetails = array();

						$unitbsector=$unit->unitbusinesssector;

						if(count($unitbsector)>0)
						{
							
							$arrSectorList = [];
							$unitgpsarr = [];
							foreach($unitbsector as $unitbs)
							{
								$business_sector_id = $unitbs->business_sector_id;

								$unitbsarr[]=$unitbs->business_sector_name;//$unitbs->businesssector->name;
								$unitbsarrDetails[$business_sector_id]=$unitbs->business_sector_name;//$unitbs->businesssector->name;
								$unitbsectoridsarr[]=$business_sector_id;
							}
							$unitarr["bsectorsselgroup"]=$unitgpsarr;
							
							$unitarr["bsectorsusers"]=$arrSectorList;
							

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
								$unitpcsarr=array();
								$unitpcsarr['id']=$unitPcs->process_id;
								$unitpcsarr['name']=$unitPcs->process_name;//$unitPcs->process->name;
								$unitprocess_data[]=$unitpcsarr;
								$unitprocessnames[]=$unitPcs->process_name;//$unitPcs->process->name;

								$icnt++;
							}

							$bsector_ids='';
							foreach($unitbsectoridsarr as $value)
							{
								$bsector_ids.=$value.",";
							}
							$bsector_ids=substr($bsector_ids, 0, -1);							
						}
						
						$unitarr["process"]=$unitprocessnames;
						$unitarr["process_ids"]=$unitprocess_data;
						$unitarr["process_data"]=$unitpcsarrobj;
						
						$unitstd=$unit->unitstandard;
						unset($unitarr["certified_standard"]);
						$certstdarr= [];
						if(count($unitstd)>0)
						{
							
							foreach($unitstd as $unitS)
							{
								$unitstdfilearr=[];
								$standardfile=$unitS->unitstandardfile?:[];
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
					$resultarr["new_units"]=$unitdetailsarr;
				}				
			}
			$responsedata =array('status'=>1,'applicationdata'=>$resultarr,'additionDetails'=>$Unitadditionarr);
		}

		$UnitAdditionUnitmodel = new UnitAdditionUnit();
		$standards = ReductionStandard::find()->select(['id','name','code'])->where(['status'=>0])->all();
		$arrReductionStandard = [];
		if(count($standards)>0)
		{
			foreach($standards as $standard)
			{				
				$reductionStd=array();
				$reductionStd['id']=$standard->id;
				$reductionStd['name']=$standard->name;
				$reductionStd['code']=$standard->code;
				$rsRequiredFldsObj = $standard->requiredfields;
				
				$arrRF=array();
				if(is_array($rsRequiredFldsObj) && count($rsRequiredFldsObj)>0)
				{
					
					foreach($rsRequiredFldsObj as $rsRequiredFld)
					{
						$arrRF[]=$standard->arrRequiredFields[$rsRequiredFld->required_field];
					}					
				}	
				$reductionStd['required_fields']=$arrRF;				
				
				//$arrReductionStandard[]=array($standard->id,$standard->name,$standard->code);
				$arrReductionStandard[]=$reductionStd;
			}
		}
		$responsedata['reductionstandard'] = $arrReductionStandard;
		$responsedata['standard'] = Standard::find()->select(['id','name','code'])->where(['status'=>0])->asArray()->all();
		$responsedata['processList'] = Process::find()->select(['id','name'])->where(['status'=>0])->asArray()->all();
		$responsedata['unitType'] = $UnitAdditionUnitmodel->unit_type_list;

		return $responsedata;
	}

	
	
	public function actionCreate()
	{
		if(!Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->isCustomer())
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$datapost = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($datapost)
		{
			$data = json_decode($datapost['formvalues'],true);
			if(!Yii::$app->userrole->isValidApplication($data['app_id']))
			{
				return false;
			}
			$update =0;
			if(isset($data['id']))
			{
				$model = UnitAddition::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new UnitAddition();
					$model->created_by = $userid;
				}else{
					$model->updated_by = $userid;
					$update =1;
				}
			}else{
				$model = new UnitAddition();
				$model->created_by = $userid;
				$model->status = 0;	
			}
			 
			$model->app_id= $data['app_id'];
			$model->address_id = Yii::$app->globalfuns->getAppCurrentAddressId($data['app_id']);
					
			if($model->validate() && $model->save())
			{
				$modelID = $model->id;
				
				if(is_array($data['units']) && count($data['units'])>0){
					foreach($data['units'] as $units){
						if(isset($units['unit_id']) && $units['unit_id']>0){
							$additionunit = UnitAdditionUnit::find()->where(['id'=>$units['unit_id']])->one();

							
							if($additionunit!==null){
								
								$unitID = $additionunit->id;
								UnitAdditionUnitProcess::deleteAll(['unit_addition_unit_id' => $unitID ]);
								UnitAdditionUnitBusinessSector::deleteAll(['unit_addition_unit_id' => $unitID]);
								//UnitAdditionUnitProcess::deleteAll(['unit_addition_unit_id' => $unitdata->id]);
								UnitAdditionUnitProduct::deleteAll(['unit_addition_unit_id' => $unitID]);
								UnitAdditionUnitStandard::deleteAll(['unit_addition_unit_id' => $unitID]);

								$pcstandard = UnitAdditionUnitCertifiedStandard::find()->where(['unit_addition_unit_id'=>$unitID])->all();
								if(count($pcstandard)>0){
									foreach($pcstandard as $certstandard){
										UnitAdditionUnitCertifiedStandardFile::deleteAll(['unit_addition_unit_certified_standard_id' => $certstandard->id]);
										$certstandard->delete();
									}
								}
							}
							
						}else{
							$additionunit = new UnitAdditionUnit();
							$additionunit->unit_addition_id = $modelID;
						}
						
						$additionunit->address = $units['address'];
						$additionunit->name=isset($units['name'])?$units['name']:"";
						$additionunit->code=isset($units['code'])?$units['code']:"";
						$additionunit->address=isset($units['address'])?$units['address']:"";
						$additionunit->zipcode=isset($units['zipcode'])?$units['zipcode']:"";
						$additionunit->city=isset($units['city'])?$units['city']:"";
						$additionunit->state_id=isset($units['state_id'])?$units['state_id']:"";
						$additionunit->country_id=isset($units['country_id'])?$units['country_id']:"";
						$additionunit->no_of_employees=isset($units['no_of_employees'])?$units['no_of_employees']:"";
						$additionunit->unit_type=isset($units['unit_type'])?$units['unit_type']:"";

						if($additionunit->validate() && $additionunit->save()){
							$new_app_id = $model->new_app_id;
							$this->saveUnitRelatedData($units, $additionunit->id,$data['app_id'],$new_app_id);



							if($new_app_id && $new_app_id!='' && $new_app_id>0){
								$newunitid = $additionunit->new_unit_id;
								if($newunitid && $newunitid>0){
									$ApplicationUnit = ApplicationUnit::find()->where(['id'=>$newunitid])->one();
									if($ApplicationUnit=== null)
									{
										$ApplicationUnit = new ApplicationUnit();

									}else{
										$unitID = $newunitid;
										ApplicationUnitProcess::deleteAll(['unit_id' => $unitID ]);
										ApplicationUnitBusinessSector::deleteAll(['unit_id' => $unitID]);
										ApplicationUnitProduct::deleteAll(['unit_id' => $unitID]);
										ApplicationUnitStandard::deleteAll(['unit_id' => $unitID]);

										$pcstandard = ApplicationUnitCertifiedStandard::find()->where(['unit_id'=>$unitID])->all();
										if(count($pcstandard)>0){
											foreach($pcstandard as $certstandard){
												ApplicationUnitCertifiedStandardFile::deleteAll(['unit_certified_standard_id' => $certstandard->id]);
											}
										}
									}
								}else{
									$ApplicationUnit = new ApplicationUnit();
								}
								
								
								$ApplicationUnit->address = $additionunit->address;
								$ApplicationUnit->name=$additionunit->name;
								$ApplicationUnit->code=$additionunit->code;
								$ApplicationUnit->address=$additionunit->address;
								$ApplicationUnit->zipcode=$additionunit->zipcode;
								$ApplicationUnit->city=$additionunit->city;
								$ApplicationUnit->state_id=$additionunit->state_id;
								$ApplicationUnit->country_id=$additionunit->country_id;
								$ApplicationUnit->no_of_employees=$additionunit->no_of_employees;
								$ApplicationUnit->unit_type=$additionunit->unit_type;
								$ApplicationUnit->unit_addition_type=1;
								$ApplicationUnit->app_id=$new_app_id;
								
								$ApplicationUnit->save();
								$newunitid= $ApplicationUnit->id;

								$additionunit->new_unit_id = $newunitid;
								$additionunit->save();

								$productstandardarr = [];
								$this->saveDataToApplication($additionunit,$newunitid,$productstandardarr);
							}								
						}
					}
				}	
				
				$new_app_id = '';
				if($data['type']=='addition'){
					
					
				}

				if(isset($data['unit_id']) && $data['unit_id']!=''){
					$responsedata=array('status'=>1,'message'=>'Unit updated successfully','new_app_id'=>$new_app_id,'app_id'=>$data['app_id'],'id'=>$modelID);
				}else{
					$responsedata=array('status'=>1,'message'=>'Unit added successfully','app_id'=>$data['app_id'],'id'=>$modelID,'new_app_id'=>$new_app_id);
				}
			}
		}
		
		return $this->asJson($responsedata);
	}
	
	public function actionSubmitadditionunit()
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
			if(!Yii::$app->userrole->isValidApplication($data['app_id']))
			{
				return false;
			}
			
			$connection = Yii::$app->getDb();
			$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
			
			$Application = new Application();
			$productstandardarr = [];
			if(isset($data['new_app_id']) && $data['new_app_id']>0){
				$new_app_id= $data['new_app_id'];
				$Application = Application::find()->where(['id'=>$new_app_id])->one();
				$Application->status = $Application->arrEnumStatus['submitted'];
				$Application->save();
			}else{
				$clonedata= [];
				$clonedata['id'] = $data['app_id'];
				$clonedata['audit_type'] = $Application->arrEnumAuditType['unit_addition'];
				$clonedata['unit_addition_id'] = $data['id'];
				$cloneres = $Application->cloneApplication($clonedata);
				$new_app_id= $cloneres['new_app_id'];

				$productstandardarr= $cloneres['productstandardarr'];
				
				$model = Application::find()->where(['id'=>$new_app_id])->one();
				$model->status = $Application->arrEnumStatus['submitted'];	
				$model->save();
			}			

			if(isset($data['id']) && $data['id']>0){
				$modelID = $data['id'];

				$UnitAdditionmodel = UnitAddition::find()->where(['id'=>$modelID])->one();
				if($UnitAdditionmodel !== null){
					$UnitAdditionmodel->new_app_id = $new_app_id;
					$UnitAdditionmodel->status = $UnitAdditionmodel->arrEnumStatus['submitted'];
					$UnitAdditionmodel->save();
				}
				

				$additionunit = UnitAdditionUnit::find()->where(['unit_addition_id'=>$modelID])->all();
				if(count($additionunit) >0 ){
					foreach($additionunit as $additionunitnew){
						
						$newunitid = $additionunitnew->new_unit_id;
						if($newunitid && $newunitid>0){
							$ApplicationUnit = ApplicationUnit::find()->where(['id'=>$newunitid])->one();
							if($ApplicationUnit=== null)
							{
								$ApplicationUnit = new ApplicationUnit();

							}else{
								$unitID = $newunitid;
								ApplicationUnitProcess::deleteAll(['unit_id' => $unitID ]);
								ApplicationUnitBusinessSector::deleteAll(['unit_id' => $unitID]);
								ApplicationUnitProduct::deleteAll(['unit_id' => $unitID]);
								ApplicationUnitStandard::deleteAll(['unit_id' => $unitID]);

								$pcstandard = ApplicationUnitCertifiedStandard::find()->where(['unit_id'=>$unitID])->all();
								if(count($pcstandard)>0){
									foreach($pcstandard as $certstandard){
										ApplicationUnitCertifiedStandardFile::deleteAll(['unit_certified_standard_id' => $certstandard->id]);
										$certstandard->delete();
									}
								}
							}
						}else{
							$ApplicationUnit = new ApplicationUnit();
						}
						
						
						$ApplicationUnit->address = $additionunitnew->address;
						$ApplicationUnit->name=$additionunitnew->name;
						$ApplicationUnit->code=$additionunitnew->code;
						$ApplicationUnit->address=$additionunitnew->address;
						$ApplicationUnit->zipcode=$additionunitnew->zipcode;
						$ApplicationUnit->city=$additionunitnew->city;
						$ApplicationUnit->state_id=$additionunitnew->state_id;
						$ApplicationUnit->country_id=$additionunitnew->country_id;
						$ApplicationUnit->no_of_employees=$additionunitnew->no_of_employees;
						$ApplicationUnit->unit_type=$additionunitnew->unit_type;
						$ApplicationUnit->unit_addition_type=1;
						$ApplicationUnit->app_id=$new_app_id;
						
						$ApplicationUnit->save();
						$newunitid= $ApplicationUnit->id;

						$additionunitnew->new_unit_id = $newunitid;
						$additionunitnew->save();
						
						$this->saveDataToApplication($additionunitnew,$newunitid,$productstandardarr);						
					}
				}

				$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'unit_addition'])->one();
				if($mailContent !== null )
				{
					$additiongrid = $this->renderPartial('@app/mail/layouts/AdditionCompanyGridTemplate',[
						'model' => $UnitAdditionmodel->application
					]);

					$mailmsg = str_replace('{NEW-APPLICATION-DETAILS-GRID}', $additiongrid, $mailContent['message'] );

					$franchise = UserCompanyInfo::find()->select('company_email')->where(['user_id' => $UnitAdditionmodel->application->franchise_id])->one();
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
				$responsedata = ['status'=>1,'message' => 'Additional Unit was submitted successfully','new_app_id'=>$new_app_id,'app_id'=>$data['app_id'],'id'=>$modelID];
			}		
		}
		return $responsedata;
	}
	public function actionDeleteunit()
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
			if(isset($data['unit_id']) && $data['unit_id']>0){
				$additionunit = UnitAdditionUnit::find()->where(['id'=>$data['unit_id']])->one();
				if($additionunit!==null){
					
					if(!Yii::$app->userrole->isValidApplication($additionunit->unitaddition->app_id))
					{
						return false;
					}
					$target_dir = Yii::$app->params['certification_standard_files']; 
					$unitID = $additionunit->id;
					UnitAdditionUnitProcess::deleteAll(['unit_addition_unit_id' => $unitID ]);
					UnitAdditionUnitBusinessSector::deleteAll(['unit_addition_unit_id' => $unitID]);
					//UnitAdditionUnitProcess::deleteAll(['unit_addition_unit_id' => $unitdata->id]);
					UnitAdditionUnitProduct::deleteAll(['unit_addition_unit_id' => $unitID]);
					UnitAdditionUnitStandard::deleteAll(['unit_addition_unit_id' => $unitID]);

					$pcstandard = UnitAdditionUnitCertifiedStandard::find()->where(['unit_addition_unit_id'=>$unitID])->all();
					if(count($pcstandard)>0){
						foreach($pcstandard as $certstandard){
							$UnitAdditionUnitCertifiedStandardFile = UnitAdditionUnitCertifiedStandardFile::find()->where(['unit_addition_unit_certified_standard_id'=>$certstandard->id])->all();
							if(count($UnitAdditionUnitCertifiedStandardFile)>0){
								foreach($UnitAdditionUnitCertifiedStandardFile as $certstandardfile){
									Yii::$app->globalfuns->removeFiles($certstandardfile->file,$target_dir);
									$certstandardfile->delete();
								}
							}
								
							$certstandard->delete();
							/*
							UnitAdditionUnitCertifiedStandardFile::deleteAll(['unit_addition_unit_certified_standard_id' => $certstandard->id]);
							
							*/
						}
					}
					if($additionunit->new_unit_id !== null && $additionunit->new_unit_id!='' && $additionunit->new_unit_id!=0
						&& $additionunit->new_unit_id > 0){
						$app_unit_id = $additionunit->new_unit_id;
						ApplicationUnit::deleteAll(['id'=>$app_unit_id]);
						ApplicationUnitProcess::deleteAll(['unit_id' => $app_unit_id ]);
						ApplicationUnitBusinessSector::deleteAll(['unit_id' => $app_unit_id]);
						//UnitProcess::deleteAll(['unit_id' => $unitdata->id]);
						ApplicationUnitProduct::deleteAll(['unit_id' => $app_unit_id]);
						ApplicationUnitStandard::deleteAll(['unit_id' => $app_unit_id]);

						$pcstandard = ApplicationUnitCertifiedStandard::find()->where(['unit_id'=>$app_unit_id])->all();
						if(count($pcstandard)>0){
							foreach($pcstandard as $certstandard){
								ApplicationUnitCertifiedStandardFile::deleteAll(['unit_certified_standard_id' => $certstandard->id]);
								$certstandard->delete();
							}
						}
					}
					$additionunit->delete();
					$responsedata=array('status'=>1,'message'=>'Unit deleted successfully');
				}				
			}
		}
		return $responsedata;
	}
	
	public function saveDataToApplication($additionunit,$newunitID,$productstandardarr){
		if(count($additionunit->unitprocess) > 0){
			foreach($additionunit->unitprocess as $objunitprocess){
				$newunitprocess = new ApplicationUnitProcess();
				$newunitprocess->unit_id = $newunitID;
				$newunitprocess->standard_id = $objunitprocess->standard_id;
				$newunitprocess->process_id = $objunitprocess->process_id;
				$newunitprocess->process_type = 0;
				$newunitprocess->process_name = $objunitprocess->process_name;
				$newunitprocess->save();
			}
		}

		if(count($additionunit->unitproduct) > 0){
			foreach($additionunit->unitproduct as $objunitproduct){
				$appunitproductmodel=new ApplicationUnitProduct();
				$appunitproductmodel->unit_id=$newunitID;
				if(is_array($productstandardarr) && count($productstandardarr)>0 && isset($productstandardarr[$objunitproduct->application_product_standard_id])){
					$appunitproductmodel->application_product_standard_id=$productstandardarr[$objunitproduct->application_product_standard_id];

					//$objunitproduct->application_product_standard_id = $productstandardarr[$objunitproduct->application_product_standard_id];
					//$objunitproduct->save();
				}else{
					$appunitproductmodel->application_product_standard_id=$objunitproduct->application_product_standard_id;
				}
				
				$appunitproductmodel->save();
			}
		}
		
		if(count($additionunit->unitbusinesssector) > 0){
			foreach($additionunit->unitbusinesssector as $objunitbusinesssector){
				$appunitbsectorsmodel=new ApplicationUnitBusinessSector();
				$appunitbsectorsmodel->unit_id=$newunitID;
				$appunitbsectorsmodel->business_sector_id=$objunitbusinesssector->business_sector_id;
				$appunitbsectorsmodel->business_sector_name=$objunitbusinesssector->business_sector_name;
				$appunitbsectorsmodel->save(); 
			}
		}
		
		if(count($additionunit->unitappstandard) > 0){
			foreach($additionunit->unitappstandard as $objunitappstandard){
				$appunitstandardmodel=new ApplicationUnitStandard();
				$appunitstandardmodel->unit_id=$newunitID;
				$appunitstandardmodel->standard_id=$objunitappstandard->standard_id;
				$appunitstandardmodel->save(); 
			}
		}

		if(count($additionunit->unitstandard) > 0){
			foreach($additionunit->unitstandard as $objunitstandard){
				$appunitcertifiedstdmodel=new ApplicationUnitCertifiedStandard();
				$appunitcertifiedstdmodel->unit_id=$newunitID;
				$appunitcertifiedstdmodel->standard_id=$objunitstandard->standard_id;
				$appunitcertifiedstdmodel->license_number=$objunitstandard->license_number;
				$appunitcertifiedstdmodel->expiry_date=$objunitstandard->expiry_date;
				
				if($appunitcertifiedstdmodel->save()){
					if(count($objunitstandard->unitstandardfile)>0){
						foreach($objunitstandard->unitstandardfile as $additionstdfile){
							$appunitcertifiedstdfilemodel=new ApplicationUnitCertifiedStandardFile();
							$appunitcertifiedstdfilemodel->unit_certified_standard_id=$appunitcertifiedstdmodel->id;
							$appunitcertifiedstdfilemodel->file=$additionstdfile->file;
							$appunitcertifiedstdfilemodel->type=$additionstdfile->type;
							$appunitcertifiedstdfilemodel->save(); 
						}						
					}					
				}				
			}
		}
		return true;
	}

	public function saveUnitRelatedData($value,$unitID,$app_id,$new_app_id)
	{
		$connection = Yii::$app->getDb();
		$target_dir = Yii::$app->params['certification_standard_files']; 
		if(is_array($value['products']) && count($value['products'])>0)
		{
			if($new_app_id!='' && $new_app_id>0){
				$app_id = $new_app_id;
			}else{
				$app_id = $app_id;
			}
			
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
				from tbl_application_product as pdt 
				INNER JOIN tbl_application_product_standard as pdt_std on pdt_std.application_product_id = pdt.id 
				INNER JOIN tbl_application_product_material as pdt_mat on pdt_mat.app_product_id = pdt.id 
				WHERE 
				pdt.product_id='".$val111['id']."' AND pdt.product_type_id='".$val111['product_type_id']."'
				 AND pdt.wastage='".$val111['wastage']."' 
				AND pdt_std.standard_id='".$val111['standard_id']."' AND pdt_std.label_grade_id='".$val111['label_grade']."' 
				AND pdt.app_id='".$app_id."' 

				AND ".$queryCondition."
				
				group by pdt.id HAVING matcnt=".$totalCompCnt." ");
				$result = $command->queryOne();
				$pdt_std_id = 0;
				if($result  !== false){
					$pdt_std_id = $result['pdt_std_id'];
				}


				
				//$pdt_id = $pdtlistval[$val1['pdt_index']];
				$appunitproductmodel=new UnitAdditionUnitProduct();
				$appunitproductmodel->unit_addition_unit_id=$unitID;
				$appunitproductmodel->application_product_standard_id=$pdt_std_id;//$pdtstdmodel->id;
				$appunitproductmodel->save();
			}
		}	

		if(is_array($value['business_sector_id']) && count($value['business_sector_id'])>0)
		{
			foreach ($value['business_sector_id'] as $val3)
			{ 
				$appunitbsectorsmodel=new UnitAdditionUnitBusinessSector();
				$appunitbsectorsmodel->unit_addition_unit_id=$unitID;
				$appunitbsectorsmodel->business_sector_id=isset($val3)?$val3:"";
				$BusinessSector = BusinessSector::find()->where(['id'=>$val3])->one();
				if($BusinessSector !== null){
					$appunitbsectorsmodel->business_sector_name= $BusinessSector->name;
				}
				$appunitbsectorsmodel->save(); 
			}
		}

		
		if(is_array($value['standards']) && count($value['standards'])>0)
		{
			foreach ($value['standards'] as $val3)
			{ 
				$appunitstandardmodel=new UnitAdditionUnitStandard();
				$appunitstandardmodel->unit_addition_unit_id=$unitID;
				$appunitstandardmodel->standard_id=isset($val3)?$val3:"";
				$appunitstandardmodel->save(); 
				
				// -------- Standard based Process code Start Here -----------
				if(is_array($value['processes']) && count($value['processes'])>0)
				{
					foreach ($value['processes'] as $val2)
					{ 
						$appunitprocessesmodel=new UnitAdditionUnitProcess();
						$appunitprocessesmodel->unit_addition_unit_id=$unitID;
						$appunitprocessesmodel->process_id=$val2;
						$appunitprocessesmodel->standard_id=isset($val3)?$val3:"";
						$Process = Process::find()->where(['id'=>$val2])->one();
						if($Process !== null){
							$appunitprocessesmodel->process_name = $Process->name;
						}
						$appunitprocessesmodel->save(); 
						
					}
				}
				// -------- Standard based Process Code End Here -----------
			}
		}
		
		//print_r($value); die;
		if(is_array($value['certified_standard']) && count($value['certified_standard'])>0)
		{
			foreach ($value['certified_standard'] as $val3)
			{ 
				$appunitcertifiedstdmodel=new UnitAdditionUnitCertifiedStandard();
				$appunitcertifiedstdmodel->unit_addition_unit_id=$unitID;
				$appunitcertifiedstdmodel->standard_id=isset($val3['standard'])?$val3['standard']:"";
				$appunitcertifiedstdmodel->license_number=isset($val3['license_number'])?$val3['license_number']:"";
				$appunitcertifiedstdmodel->expiry_date=isset($val3['expiry_date']) && $val3['expiry_date'] !=''?date('Y-m-d',strtotime($val3['expiry_date'])):"";

				$appunitcertifiedstdmodel->save(); 

				$standard_id = $val3['standard'];
				
				if(isset($_FILES['uploads']['name'][$standard_id]) && is_array($_FILES['uploads']['name'][$standard_id]))
				{
					foreach($_FILES['uploads']['name'][$standard_id] as $indexkey => $filename){
						//print_r($files);
						//die;
						//foreach($standard[$standard_id] as $files){
						//	foreach($files as $indexkey=>$filename){
							//print_r($file);
							
							//if($val3['files'][$indexkey]['deleted']==0){
								/*$target_file = $target_dir . basename($filename);


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
								if (move_uploaded_file($_FILES['uploads']["tmp_name"][$standard_id][$indexkey], $target_dir .$actual_name.".".$extension)) {
								*/
								$tmp_name = $_FILES['uploads']["tmp_name"][$standard_id][$indexkey];
								$name = $filename;
								$name=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);
								if ($name) {
									$appunitcertifiedstdfilemodel=new UnitAdditionUnitCertifiedStandardFile();
									$appunitcertifiedstdfilemodel->unit_addition_unit_certified_standard_id=$appunitcertifiedstdmodel->id;
									$appunitcertifiedstdfilemodel->file=isset($name)?$name:"";
									$appunitcertifiedstdfilemodel->type=$indexkey;//$val3['files'][$indexkey]['type'];
									$appunitcertifiedstdfilemodel->save(); 
								}
							//}
								
							//}
							
						//}
					}
				}

				foreach ($val3['files'] as $val4)
				{ 
					if($val4['added']==0 && $val4['deleted']==0){
						$appunitcertifiedstdfilemodel=new UnitAdditionUnitCertifiedStandardFile();
						$appunitcertifiedstdfilemodel->unit_addition_unit_certified_standard_id=$appunitcertifiedstdmodel->id;
						$appunitcertifiedstdfilemodel->file=isset($val4['name'])?$val4['name']:"";
						$appunitcertifiedstdfilemodel->type=$val4['type'];
						$appunitcertifiedstdfilemodel->save(); 
					}else if($val4['deleted']==1){


					}
				}
			}			
		}
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
		if($data){
			if(isset($data['id']))
			{
				$additionunit = UnitAddition::find()->where(['id'=>$data['id']])->one();
				if($additionunit!==null)
				{
					if(!Yii::$app->userrole->isValidApplication($additionunit->app_id))
					{
						return false;
					}
					
					$unitadditionunit = UnitAdditionUnit::find()->where(['unit_addition_id'=>$additionunit->id])->all();

								
					if(count($unitadditionunit)>0){
						foreach($unitadditionunit as $delunit){
							$unitID = $delunit->id;
							UnitAdditionUnitProcess::deleteAll(['unit_addition_unit_id' => $unitID ]);
							UnitAdditionUnitBusinessSector::deleteAll(['unit_addition_unit_id' => $unitID]);
							//UnitAdditionUnitProcess::deleteAll(['unit_addition_unit_id' => $unitdata->id]);
							UnitAdditionUnitProduct::deleteAll(['unit_addition_unit_id' => $unitID]);
							UnitAdditionUnitStandard::deleteAll(['unit_addition_unit_id' => $unitID]);

							$pcstandard = UnitAdditionUnitCertifiedStandard::find()->where(['unit_addition_unit_id'=>$unitID])->all();
							if(count($pcstandard)>0){
								foreach($pcstandard as $certstandard){
									UnitAdditionUnitCertifiedStandardFile::deleteAll(['unit_addition_unit_certified_standard_id' => $certstandard->id]);
									$certstandard->delete();
								}
							}
							$delunit->delete();
						}
							
					}
					$additionunit->delete();
					$responsedata=array('status'=>1,'message'=>'Unit deleted successfully');
				}				
				 
			}
		}		
		return $this->asJson($responsedata);		
	}

	public function actionCertificationfile(){
		$data = Yii::$app->request->post();
		//$data['id'];
		$files = UnitAdditionUnitCertifiedStandardFile::find()->where(['id'=>$data['id']])->one();

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
}
