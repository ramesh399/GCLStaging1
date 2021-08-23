<?php
namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

use app\modules\master\models\Settings;
use app\modules\master\models\Role;
use app\modules\master\models\BusinessSectorGroup;
use app\modules\master\models\State;
use app\modules\master\models\Standard;
use app\modules\master\models\Process;
use app\modules\master\models\ReductionStandard;

use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditReportApplicableDetails;
use app\modules\audit\models\AuditReportDisplay;
use app\modules\audit\models\AuditPlanUnitExecution;

use app\modules\changescope\models\ProcessAddition;
use app\modules\changescope\models\UnitAddition;
use app\modules\changescope\models\StandardAddition;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationUnitSubtopic;
use app\modules\application\models\ApplicationUnitStandard;
use app\modules\application\models\ApplicationUnitCertifiedStandard;

use app\modules\master\models\User;
use app\modules\master\models\UserCompanyInfo;
use app\modules\certificate\models\Certificate;
use app\modules\unannouncedaudit\models\UnannouncedAuditApplication;
use app\modules\unannouncedaudit\models\UnannouncedAuditApplicationUnit;

class GlobalComponent extends Component
{ 
    public function getSettings($arg)
	{
		$settingsmodel=Settings::find()->select([$arg])->one();	
		
		return $settingsmodel->$arg;
	}
	
	public function fnCalculateDates($date1,$date2)
	{
	   $diff = strtotime($date1)-strtotime($date2);
	   $days = ($diff / 60 / 60 / 24);
	   return floor($days);	   
	}
	
	public function getPrivilegeUser($franchiseID,$privilege)
	{
		$connection = Yii::$app->getDb();
		$condition= '';
		if($franchiseID){
			$condition = ' userrole.franchise_id="'.$franchiseID.'" AND ';
		}
		$query = 'SELECT usr.first_name,usr.last_name,usr.email FROM `tbl_user_role` AS userrole
		INNER JOIN `tbl_rule` AS rule ON '.$condition.' userrole.role_id=rule.role_id AND userrole.status=0 AND rule.privilege=\''.$privilege.'\'
		INNER JOIN `tbl_users` AS usr ON usr.id = userrole.user_id and usr.status=0';
		
		$command = $connection->createCommand($query);
		$result = $command->queryAll();
		
		return $result;
	}

	public function getReviewers()
	{
		$usermodel = new User();
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();

		$query = "SELECT usr.id,usr.first_name,usr.last_name,usr.email FROM `tbl_user_role` AS userrole
		INNER JOIN `tbl_rule` AS rule ON userrole.role_id=rule.role_id AND userrole.status=0 AND rule.privilege in ( 'application_review','audit_review','certification_review'  )
		INNER JOIN `tbl_users` AS usr ON usr.id = userrole.user_id where usr.status='".$usermodel->arrLoginEnumStatus['active']."'  group by usr.id ";
		
		$command = $connection->createCommand($query);
		$result = $command->queryAll();
		
		return $result;
	}
	
	public function getReviewerRolesForUser($user_id)
	{
		$usermodel = new User();
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();

		$query = "SELECT  role.id,role.role_name FROM `tbl_user_role` AS userrole
		INNER JOIN `tbl_rule` AS rule ON userrole.role_id=rule.role_id AND rule.privilege in ( 'application_review','audit_review','certification_review' )
		INNER JOIN `tbl_role` AS role ON role.id = userrole.role_id 
		where  userrole.status=0 and userrole.approval_status=2 and role.status=0 and userrole.user_id =".$user_id." group by role.id ";
		//INNER JOIN `tbl_users` AS usr ON usr.id = userrole.user_id 
		$command = $connection->createCommand($query);
		$result = $command->queryAll();
		
		return $result;
	}

	public function getTERolesForUser($user_id)
	{
		$usermodel = new User();
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();

		$query = "SELECT role.id,role.role_name FROM `tbl_user_role` AS userrole
		INNER JOIN `tbl_role` AS role ON role.id = userrole.role_id and role.resource_access = 3 
		where  userrole.status=0 and userrole.approval_status=2 and role.status=0 and userrole.user_id =".$user_id." group by role.id ";
		//INNER JOIN `tbl_users` AS usr ON usr.id = userrole.user_id 
		$command = $connection->createCommand($query);
		$result = $command->queryAll();
		
		return $result;
	}

	public function getQualifiedPrivilegeUser($franchiseID,$privilege)
	{
		$connection = Yii::$app->getDb();
		$condition= '';
		if($franchiseID){
			$condition = ' userrole.franchise_id="'.$franchiseID.'" AND ';
		}
		$query = 'SELECT usr.id,usr.first_name,usr.last_name,usr.email FROM `tbl_user_role` AS userrole
		INNER JOIN `tbl_rule` AS rule ON '.$condition.' userrole.role_id=rule.role_id AND userrole.status=0 AND rule.privilege=\''.$privilege.'\'
		INNER JOIN `tbl_users` AS usr ON usr.id = userrole.user_id AND usr.status=0';
		//INNER JOIN `tbl_user_qualification_review` as qual ON qual.user_id = usr.id AND qual.user_role_id=userrole.role_id 
		//AND qual.qualification_status = 1 
		$command = $connection->createCommand($query);
		$result = $command->queryAll();
		
		return $result;
	}

	public function fnRemoveSpecialCharacters($fileName)
	{
	   $NewFileName = '';
	   $special_char= array('&quot;','!','@','#','$','%','^','&','*','(',')','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','/','*','+','~','`','=',"'",'\'');
	   $NewFileName = str_replace($special_char, '', $fileName);
	   $NewFileName=str_replace(",","-",$NewFileName);
	   $NewFileName=str_replace("_","-",$NewFileName);	
	   $NewFileName=str_replace(" ","-",$NewFileName);	   
	   return $NewFileName;
	}
	
	public function postFiles($fileName,$tempFile,$targetFolder)
	{
	   $special_char= array('-','&quot;','!','@','#','$','%','^','&','*','(',')','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','/','*','+','~','`','=',"'",'\'');
	   $NewFileName = str_replace($special_char, '', $fileName);
	   $NewFileName=str_replace(",","-",$NewFileName);
	   $NewFileName=str_replace("_","-",$NewFileName);	
	   $NewFileName=str_replace(" ","-",$NewFileName);	
	   $newF = $NewFileName;
	   $counter=0;
	   if(file_exists($targetFolder."/".$NewFileName))
		{
			do
			{ 
				$counter=$counter+1;
				$NewFileName=$counter."".$newF;
				//$NewFileName = str_replace($special_char, '', $NewFileName);
				//$NewFileName=str_replace(",","-",$NewFileName);
	   			//$NewFileName=str_replace(" ","_",$NewFileName);	
			}
			while(file_exists($targetFolder."/".$NewFileName));
		}	   
	   copy($tempFile, $targetFolder."/".$NewFileName);	
	   return $NewFileName;
	}
	
	public function removeFiles($fileName,$targetFolder)
	{
		$fileNameWithPath=$targetFolder.$fileName;
		if(file_exists($fileNameWithPath))
		{
			@unlink($fileNameWithPath);
		}
	}
	
	public function copyFiles($fileName,$targetFolder)
	{
		$NewFileName=$fileName;
		$counter=0;
		$newF = $NewFileName;
		if(file_exists($targetFolder.$NewFileName))
		{
			do
			{ 
				$counter=$counter+1;
				$NewFileName=$counter."".$newF;					
			}
			while(file_exists($targetFolder.$NewFileName));
		}
		copy($targetFolder."/".$fileName, $targetFolder."/".$NewFileName);	
		return $NewFileName;
	}
	
	public function binaryFiles($fileName,$imagedata,$targetFolder)
	{
		$NewFileName=$fileName;
		$counter=0;
		$newF = $NewFileName;
		if(file_exists($targetFolder.$NewFileName))
		{
			do
			{ 
				$counter=$counter+1;
				$NewFileName=$counter."".$newF;					
			}
			while(file_exists($targetFolder.$NewFileName));
		}
		//copy($targetFolder."/".$fileName, $targetFolder."/".$NewFileName);	
		file_put_contents($targetFolder.$NewFileName, file_get_contents($imagedata));
		return $NewFileName;
	}

	public function getUserRoles()
	{
		/*
		$result=array();
		$RoleModel =Role::find()->where(['status'=>0])->all();
		if(count($RoleModel)>0)
		{
			foreach($RoleModel as $UserRole)
			{
				$result["$UserRole->id"]= $UserRole->role_name;
			}			
		}
		*/	
		
		return Role::find()->select(['id','role_name as name'])->where(['status'=>0])->asArray()->all();
	}
	
	public function getCustomerRoles()
	{
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		$customerRolesQry = 'SELECT GROUP_CONCAT(id) as customerroles FROM `tbl_role` WHERE resource_access=6';
		$command = $connection->createCommand($customerRolesQry);
		$customerRoleResult = $command->queryOne();					
		$customer_roles = 0;
		if($customerRoleResult  !== false){
			$customer_roles = $customerRoleResult['customerroles'];
			if($customer_roles=='')
			{
				$customer_roles = 0;
			}
		}
		return $customer_roles;
	}
	
	public function getOspRoles()
	{
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		$ospRolesQry = 'SELECT GROUP_CONCAT(id) as osproles FROM `tbl_role` WHERE resource_access=7';
		$command = $connection->createCommand($ospRolesQry);
		$ospRoleResult = $command->queryOne();					
		$osp_roles = 0;
		if($ospRoleResult  !== false){
			
			$osp_roles = $ospRoleResult['osproles'];
			if($osp_roles=='')
			{
				$osp_roles = 0;
			}
		}
		return $osp_roles;
	}

	public function getOspAdminRoles()
	{
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		$ospRolesQry = 'SELECT GROUP_CONCAT(id) as osproles FROM `tbl_role` WHERE resource_access=5';
		$command = $connection->createCommand($ospRolesQry);
		$ospRoleResult = $command->queryOne();					
		$osp_roles = 0;
		if($ospRoleResult  !== false){
			
			$osp_roles = $ospRoleResult['osproles'];
			if($osp_roles=='')
			{
				$osp_roles = 0;
			}
		}
		return $osp_roles;
	}

	public function updateApplicationStatus($app_id,$status,$type)
	{
		$Application = new Application();
		
		if($type==$Application->arrEnumAuditType['process_addition']){
			$processaddition =ProcessAddition::find()->where(['new_app_id'=>$app_id])->one();
			if($processaddition !== null){
				$processaddition->status = $status;
				$processaddition->save();
			}
		}else if($type==$Application->arrEnumAuditType['unit_addition']){
			$addition =UnitAddition::find()->where(['new_app_id'=>$app_id])->one();
			if($addition !== null){
				$addition->status = $status;
				$addition->save();
			}
		}else if($type==$Application->arrEnumAuditType['standard_addition']){
			$addition = StandardAddition::find()->where(['new_app_id'=>$app_id])->one();
			if($addition !== null){
				$addition->status = $status;
				$addition->save();
			}
		}

		return true;
	}
	
	public function updateApplicationOverallStatus($app_id,$status)
	{
		$ApplicationModel =Application::find()->where(['id'=>$app_id])->one();
		if($ApplicationModel !== null)
		{
			$ApplicationModel->overall_status = $status;
			$ApplicationModel->save();
		}
	}

	public function getAppList()
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

		$Certificatemodel = new Certificate();
		/*
		$appmodel = Application::find()->where(['t.audit_type'=>[1,2]])->alias('t');
		$appmodel = $appmodel->join('inner join', 'tbl_audit as audit','audit.app_id =t.id');
		$appmodel = $appmodel->join('inner join', 'tbl_certificate as cert','audit.id =cert.audit_id
		 and (( cert.status="'.$Certificatemodel->arrEnumStatus['certificate_generated'].'" and cert.certificate_valid_until>="'.date('Y-m-d').'") or 
		 (cert.status="'.$Certificatemodel->arrEnumStatus['extension'].'" and cert.certificate_valid_until>="'.date('Y-m-d').'" )) ');
		*/
		$appmodel = Application::find()->where(['t.audit_type'=>[1,2]])->alias('t');
		$appmodel = $appmodel->join('inner join', 'tbl_certificate as cert','t.id =cert.parent_app_id
		 and (( cert.status="'.$Certificatemodel->arrEnumStatus['certificate_generated'].'" and cert.certificate_valid_until>="'.date('Y-m-d').'") or 
		 (cert.status="'.$Certificatemodel->arrEnumStatus['extension'].'" and cert.certificate_valid_until >="'.date('Y-m-d').'" )) ');

		if($resource_access != 1){
			if($user_type==2){
				$appmodel = $appmodel->andWhere(['t.customer_id' => $userid]);
			}else if($user_type==3  && $is_headquarters!=1){
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

		$appmodel = $appmodel->groupBy(['t.id']);
		$appmodel = $appmodel->all();
		$apparr = array();
		if(count($appmodel)>0)
		{
			foreach($appmodel as $app)
			{
				$company_name = $app->companyname;
				if($app->currentaddress!==null){
					$company_name = $app->currentaddress->company_name;
				}
				$apparr[] = ['id'=> $app->id, 'company_name' => $company_name];
			}
		}
		return $apparr;
	}

	public function getAppunitdata($data)
	{
		//$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		//$data = Yii::$app->request->post();
		$unitarr = [];
		if ($data) 
		{	
			//->select('id,name,unit_type')
			$appmodel = ApplicationUnit::find()->where(['app_id' => $data['id'],'status'=>0]);
			if(isset($data['unit_type']) && $data['unit_type']!=''){
				$appmodel = $appmodel->andWhere(['unit_type'=>$data['unit_type']]);
			}
			$appmodel = $appmodel->all();
			
			$appstandardIds = [];
			$app = Application::find()->where(['id' => $data['id']])->one();
			if($app!== null){
				$applicationstandard = $app->applicationstandard;
				if(count($applicationstandard)>0){
					foreach($applicationstandard as $appstd ){
						$appstandardIds[] = $appstd->standard_id;
					}
				}
			}
			
			if(count($appmodel)>0)
			{
				$unitarr = array();
				foreach($appmodel as $unit)
				{
					$unitstandardIds = [];
					foreach($unit->unitappstandard as $unitstandard){
						$unitstandardIds[] = $unitstandard->standard_id;
					}
					$commonstandards = array_intersect($appstandardIds,$unitstandardIds);
					if(count($commonstandards)<=0){
						continue;
					}
					/*if(count($unit->unitappstandard)<=0){
						continue;
					}*/
					$unit_name = $unit->name;
					if($unit->unit_type==1 && $unit->currentaddress!==null){
						$unit_name = $unit->currentaddress->company_name;
					}
					$unitarr[] = ['id'=> $unit->id, 'name' => $unit_name];
				}
			}
			//$responsedata=array('status'=>1,'unitdata'=>$unitarr);
		}
		return $unitarr;
		///return $this->asJson($responsedata);
	}

	public function getAppStandards($data)
	{
		//$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		//$data = Yii::$app->request->post();
		$stdarr = array();
		if ($data) 
		{	
			$app_id = $data['app_id'];


			//$appmodel = Certificate::find()->where(['app_id' => $app_id,'certificate_valid_until'=> ]);
			$Certificatemodel = new Certificate();
			/*$appmodel = Application::find()->alias('t');
			//$appmodel = $appmodel->join('inner join', 'tbl_audit as audit','audit.app_id =t.id');
			$appmodel = $appmodel->join('inner join', 'tbl_certificate as cert','t.id =cert.app_id
			and ((cert.status="'.$Certificatemodel->arrEnumStatus['certificate_generated'].'" or cert.status="'.$Certificatemodel->arrEnumStatus['extension'].'" ) and cert.certificate_valid_until>="'.date('Y-m-d').'") ');
			$appmodel = $appmodel->join('inner join', 'tbl_standard as standard','standard.id =cert.standard_id');
			
			
			$appmodel = $appmodel->all();
			*/
			$connection = Yii::$app->getDb();
			
			$query = 'SELECT standard.id,standard.name,standard.code FROM `tbl_certificate` AS cert 
			INNER JOIN tbl_standard as standard on standard.id =cert.standard_id and ((cert.status="'.$Certificatemodel->arrEnumStatus['certificate_generated'].'" or cert.status="'.$Certificatemodel->arrEnumStatus['extension'].'" ) and cert.certificate_valid_until>="'.date('Y-m-d').'")
			and cert.parent_app_id="'.$app_id.'" ';
			
			$command = $connection->createCommand($query);
			$appmodel = $command->queryAll();
			if(count($appmodel)>0)
			{
				
				foreach($appmodel as $std)
				{
					$stdarr[] = ['id'=> $std['id'], 'name' => $std['name'], 'code' => $std['code']];
				}
			}
			//$responsedata=array('status'=>1,'unitdata'=>$unitarr);
		}
		return $stdarr;
		///return $this->asJson($responsedata);
	}

	public function checkBusinessSectorInStandard($data){
		$connection = Yii::$app->getDb();
		$business_sector_id = $data['business_sector_id'];
		$standard_id = is_array($data['standard_id'])?$data['standard_id']:explode(',',$data['standard_id']);
		$businessQry = 'SELECT group_concat(standard_id) as standard_ids FROM `tbl_business_sector_group` 
					WHERE `business_sector_id`="'.$business_sector_id.'" group by business_sector_id';
		$command = $connection->createCommand($businessQry);
		$result = $command->queryOne();	
		if($result !==false){
			$bsectorstandard_ids = array_unique(explode(',',$result['standard_ids']));
		}
		$relatedBusiness = 1;
		if( count(array_intersect($standard_id,$bsectorstandard_ids))<=0 ){
			$relatedBusiness = 0;
		}
		return $relatedBusiness;
	}

	public function checkBusinessSectorGroupInStandard($data){
		
		$business_sector_group_id = $data['business_sector_group_id'];
		$standard_id = is_array($data['standard_id'])?$data['standard_id']:explode(',',$data['standard_id']);
		
		$BusinessSectorGroup = BusinessSectorGroup::find()->where(['id'=>$business_sector_group_id,'standard_id'=>$standard_id])->one();
		$relatedBusiness = 1;
		if( $BusinessSectorGroup===null ){
			$relatedBusiness = 0;
		}
		return $relatedBusiness;
	}

	public function getAppUnitStandards($data){

		$standardarr = [];
		$appModel = ApplicationUnit::find()->where(['id' => $data['unit_id']])->one();
		if($appModel!==null)
		{
			$app_id = $appModel->app_id;
			$appstandardIds = [];
			$Certificatemodel = new Certificate();
			$ApplicationStandard = ApplicationStandard::find()->where(['app_id'=>$app_id,'standard_status'=>0])->alias('t');
			$ApplicationStandard = $ApplicationStandard->join('inner join', 'tbl_certificate as cert','cert.parent_app_id =t.app_id and cert.standard_id = t.standard_id 
				and (( cert.status="'.$Certificatemodel->arrEnumStatus['certificate_generated'].'" and cert.certificate_valid_until>="'.date('Y-m-d').'") or 
				(cert.status="'.$Certificatemodel->arrEnumStatus['extension'].'" and cert.certificate_valid_until>="'.date('Y-m-d').'" )) and cert.certificate_status=0 ');

			$ApplicationStandard = $ApplicationStandard->all();
			if(count($ApplicationStandard)>0){
				foreach($ApplicationStandard as $appstandard){
					$appstandardIds[] = $appstandard->standard_id;
				}
			}
			$arrstandardids=[];
			$unitappstandard=$appModel->unitappstandard;
			if(count($unitappstandard)>0)
			{

				foreach($unitappstandard as $std)
				{
					if(!in_array($std->standard_id,$appstandardIds)){

						continue;
					}
					$standardarr[] = ['id'=> $std->standard_id, 'name' => $std->standard->name, 'code' => $std->standard->code];
				}
			}
		}
		return $standardarr;
	}

	public function getAppProducts($appProduct,$arrstandardids=[])
	{
		$appprdarr=[];
		$appprdarr_details=[];
		$resultarr = [];

		$standardaddition_add = 0;
		//$appProduct=$model->applicationproduct;
		if(count($appProduct)>0)
		{
			$pdt_index = 0;
			$second_pdt_index = 0;
			foreach($appProduct as $prd)
			{
				if(count($arrstandardids)>0){
					$pdtstdexits =0;
					foreach($prd->productstandard as $chkproductstandard)
					{
						if(in_array($chkproductstandard->standard_id,$arrstandardids)){
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
							'material_name'=>$productmaterial->material_name, //$productmaterial->material->name,
							'material_type_id'=>$productmaterial->material_type_id,
							'material_type_name'=> $productmaterial->material_type_name,//$productmaterial->material->material_type[$productmaterial->material_type_id],
							'material_percentage'=>$productmaterial->percentage
						];
						$materialcompositionname = $materialcompositionname.$productmaterial->percentage.'% '.$productmaterial->material->name.' + ';

					}
					$materialcompositionname = rtrim($materialcompositionname," + ");
				}

				$arrsForPdtDetails=array(
					'id'=>$prd->product_id,
					'autoid'=>$prd->id,
					'pdt_index'=>$pdt_index,
					'addition_type'=> $standardaddition_add?0:$prd->product_addition_type,
					'name'=>$prd->product_name,//($prd->product?$prd->product->name:''),
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
						if(count($arrstandardids)>0){
							if(!in_array($productstandard->standard_id,$arrstandardids)){
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
						$arrsForPdtDetails['temp_exists'] = 0;
						if($productstandard->appproducttemp !== null){
							$arrsForPdtDetails['temp_exists'] = 1;
						}
						$arrsForPdtDetails['pdt_autoid'] = $productstandard->id;
						$arrsForPdtDetails['pdt_index'] = $pdt_index;
						$arrsForPdtDetails['standard_id'] = $productstandard->standard_id;
						$arrsForPdtDetails['standard_name'] = $productstandard->standard->name;
						$arrsForPdtDetails['label_grade'] = $productstandard->label_grade_id;
						$arrsForPdtDetails['label_grade_name'] = $productstandard->label_grade_name; //$productstandard->labelgrade->name;
						//$arrsForPdtDetails['addition_type'] = $productstandard->addition_type;
						$arrsForPdtDetails['pdtListIndex'] = $i;
						

						$appprdarr_details[$productstandard->id]= $arrsForPdtDetails;
						$i++;
						$pdt_index++;
					}
				}
				


				$materialcompositionname = rtrim($materialcompositionname,' + ');
				$pdt_index_list[$prd->id] = $second_pdt_index;
				$arrs=array(
					'id'=>$prd->product_id,
					'autoid'=>$prd->id,
					'pdt_index'=>$second_pdt_index,
					'name'=>$prd->product_name,//($prd->product?$prd->product->name:''),
					'wastage'=>$prd->wastage,
					'product_type_name' => $prd->product_type_name,//isset($prd->producttype)?$prd->producttype->name:'',
					'product_type_id'=>isset($prd->producttype)?$prd->producttype->id:'',
					'addition_type' => $standardaddition_add?0:$prd->product_addition_type,
					'productStandardList' => $productStandardList,
					'productMaterialList' => $productMaterialList,
					'materialcompositionname' => $materialcompositionname,
				);	
				$appprdarr[] = $arrs;

				$second_pdt_index++;
				
				
				
			}
		}
		$resultarr["products"]=$appprdarr;

		foreach($appprdarr_details as $pdtDetailsDt){
			$resultarr["productDetails"][] = $pdtDetailsDt;
		}
		$resultarr["appprdarr_details"]=$appprdarr_details;
		return $resultarr;
	}

	public function getProductAdditionProducts($appProduct,$arrstandardids=[])
	{
		$appprdarr=[];
		$appprdarr_details=[];
		$resultarr = [];

		$standardaddition_add = 0;
		//$appProduct=$model->applicationproduct;
		if(count($appProduct)>0)
		{
			$pdt_index = 0;
			$second_pdt_index = 0;
			foreach($appProduct as $prd)
			{
				if(count($arrstandardids)>0){
					$pdtstdexits =0;
					foreach($prd->productstandard as $chkproductstandard)
					{
						if(in_array($chkproductstandard->standard_id,$arrstandardids)){
							$pdtstdexits = 1;
						}
					}
					if(!$pdtstdexits){
						continue;
					}
				}


				$productMaterialList = [];
				$materialcompositionname = '';
				if(is_array($prd->additionproductmaterial) && count($prd->additionproductmaterial)>0){
					foreach($prd->additionproductmaterial as $productmaterial){
						$productMaterialList[]=[
							'app_product_id'=>$productmaterial->product_addition_product_id,
							'material_id'=>$productmaterial->material_id,
							'material_name'=>$productmaterial->material_name, 
							'material_type_id'=>$productmaterial->material_type_id,
							'material_type_name'=> $productmaterial->material_type_name,
							'material_percentage'=>$productmaterial->percentage
						];
						$materialcompositionname = $materialcompositionname.$productmaterial->percentage.'% '.$productmaterial->material->name.' + ';

					}
					$materialcompositionname = rtrim($materialcompositionname," + ");
				}

				$arrsForPdtDetails=array(
					'id'=>$prd->product_id,
					'autoid'=>$prd->id,
					'pdt_index'=>$pdt_index,
					'name'=>$prd->product_name,
					'wastage'=>$prd->wastage,
					'product_type_name' => $prd->product_type_name,
					'product_type_id'=>isset($prd->producttype)?$prd->producttype->id:'',
					'productMaterialList' => $productMaterialList,
					'materialcompositionname' => $materialcompositionname,
				);	


				$productStandardList = [];
				$arrpdtDetails = [];
				if(is_array($prd->productstandard) && count($prd->productstandard)>0){

					
					$i=0;
					foreach($prd->productstandard as $productstandard){
						if(count($arrstandardids)>0){
							if(!in_array($productstandard->standard_id,$arrstandardids)){
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
						$arrsForPdtDetails['temp_exists'] = 0;
						if($productstandard->appproducttemp !== null){
							$arrsForPdtDetails['temp_exists'] = 1;
						}
						$arrsForPdtDetails['pdt_autoid'] = $productstandard->id;
						$arrsForPdtDetails['pdt_index'] = $pdt_index;
						$arrsForPdtDetails['standard_id'] = $productstandard->standard_id;
						$arrsForPdtDetails['standard_name'] = $productstandard->standard->name;
						$arrsForPdtDetails['label_grade'] = $productstandard->label_grade_id;
						$arrsForPdtDetails['label_grade_name'] = $productstandard->label_grade_name; //$productstandard->labelgrade->name;
						//$arrsForPdtDetails['addition_type'] = $productstandard->addition_type;
						$arrsForPdtDetails['pdtListIndex'] = $i;
						

						$appprdarr_details[$productstandard->id]= $arrsForPdtDetails;
						$i++;
						$pdt_index++;
					}
				}
				


				$materialcompositionname = rtrim($materialcompositionname,' + ');
				$pdt_index_list[$prd->id] = $second_pdt_index;
				$arrs=array(
					'id'=>$prd->product_id,
					'autoid'=>$prd->id,
					'pdt_index'=>$second_pdt_index,
					'name'=>$prd->product_name,//($prd->product?$prd->product->name:''),
					'wastage'=>$prd->wastage,
					'product_type_name' => $prd->product_type_name,//isset($prd->producttype)?$prd->producttype->name:'',
					'product_type_id'=>isset($prd->producttype)?$prd->producttype->id:'',
					'productStandardList' => $productStandardList,
					'productMaterialList' => $productMaterialList,
					'materialcompositionname' => $materialcompositionname,
				);	
				$appprdarr[] = $arrs;

				$second_pdt_index++;
				
				
				
			}
		}
		$resultarr["products"]=$appprdarr;

		foreach($appprdarr_details as $pdtDetailsDt){
			$resultarr["productDetails"][] = $pdtDetailsDt;
		}
		$resultarr["appprdarr_details"]=$appprdarr_details;
		return $resultarr;
	}

	public function getAppUnit($appUnit,$includeunits=[],$include_standard_ids=[],$showonlyNormal=0,$appprdarr_details=[]){
		$selunitgpsarrlists = [];
		$standardaddition_add = 0;
		$renewal_add = 0;
		$resultarr = [];


		
		if(count($appUnit)>0)
		{
			foreach($appUnit as $unit)
			{
				if(count($includeunits)>0){
					if(is_array($includeunits) && count($includeunits)>0){
						if( !in_array($unit->id, $includeunits)){
							continue;
						}
					}
				}
				
				if(count($include_standard_ids)>0){
					$unitstandard_id = [];
					$unitappstandard=$unit->unitappstandard;
					if(count($unitappstandard)>0)
					{
						foreach($unitappstandard as $unitstd)
						{
							$unitstandard_id[] = $unitstd->standard_id;
						}
					}
					$commonStd = array_intersect($include_standard_ids, $unitstandard_id);
					if(count($commonStd)<=0){
						continue;
					}
				}


				$statelist = State::find()->alias( 't' )->select(['id','name'])->where(['t.country_id'=>$unit->country_id])->asArray()->all();
				
				$unitarr = $unit->toArray();
				$unitarr["unit_type_name"]=$unit->unit_type_list[$unit->unit_type];
				
				$unitarr["state_id_name"]=($unit->state_id!="")?$unit->state->name:"";
				$unitarr["country_id_name"]=($unit->country_id!="")?$unit->country->name:"";
				
				$unitarr["state_list"]= $statelist;

				$unitarr["addition_type"]= $standardaddition_add?0:$unit->unit_addition_type;
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
						if($renewal_add && count($include_standard_ids)>0){
							if(!in_array($unitstd->standard_id,$include_standard_ids)){
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
						if($renewal_add && count($include_standard_ids)>0){
							$business_sector_id = $unitbs->business_sector_id;
							$chkBusiness = ['business_sector_id'=>$business_sector_id,'standard_id'=>$include_standard_ids];
							$relatedsector = $this->checkBusinessSectorInStandard($chkBusiness);
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
								$selunitgpsarrlists[$unit->id][$business_sector_id][] = [
									'id' =>$unitbsgps->business_sector_group_id,
									'group_code' => $unitbsgps->group->group_code,
								];
							}
						}




						

						$unitbsarr[]=($unitbs->businesssector)?$unitbs->businesssector->name:'';
						$unitbsarrDetails[$business_sector_id]=($unitbs->businesssector)?$unitbs->businesssector->name:'';
						$unitbsectoridsarr[]=$business_sector_id;

						if($standardaddition_add || $unitbs->addition_type==0){
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
						}
						
					}
					$unitarr["bsectorsselgroup"]=$unitgpsarr;
					
					$unitarr["bsectorsusers"]=$arrSectorList;

					$unitarr["bsectorsgroupselected"]=$selunitgpsarr;
					
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
				

				if($renewal_add && count($include_standard_ids)>0){
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
								$newunitpcsarr[]=$unitPcs->process->name;
							}else{
								$unitpcsarr=array();
								$unitpcsarr['id']=$unitPcs->process_id;
								$unitpcsarr['name']=$unitPcs->process->name;
								$unitprocess_data[]=$unitpcsarr;
								$unitprocessnames[]=$unitPcs->process->name;
							}
						}else{
							if($renewal_add && count($include_standard_ids)>0){
								if(!in_array($unitPcs->standard_id,$include_standard_ids)){
									continue;
								}
							}
							if(in_array($unitPcs->process_id,$chkprocessunique)){
								continue;
							}
							$chkprocessunique[] = $unitPcs->process_id;


							$unitpcsarr=array();
							$unitpcsarr['id']=$unitPcs->process_id;
							$unitpcsarr['name']=$unitPcs->process->name;
							$unitpcsarr['addition_type']=$standardaddition_add?0:$unitPcs->process_type;
							$unitprocess_data[]=$unitpcsarr;
							$unitprocessnames[]=$unitPcs->process->name;
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
		return $resultarr;
	}


	public function getStandardVersion($standard_id){
		$versionID = 1;
		$Standard = Standard::find()->where(['id'=>$standard_id,'status'=>0])->orderBy(['id' => SORT_DESC])->one();
		if($Standard !== null){
			$versionID =  $Standard->version;
		}
		return $versionID;
	}

	public function getAppCurrentAddressId($app_id){
		$address_id = '';
		$ModelApplication = Application::find()->where(['id'=>$app_id])->one();
		if($ModelApplication!== null){
			$address_id= $ModelApplication->currentaddress->id;
		}
		return $address_id;
	}

	public function getApplicationAddressDetails($model){
		$resultarr = [];
		$resultarr["company_name"]=$model->company_name;
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
		$resultarr["email_address"]=($model->email_address!="")?$model->email_address:"";
					
		$resultarr["state_id_name"]=($model->state->name!="")?$model->state->name:"";
		$resultarr["country_id_name"]=($model->country->name!="")?$model->country->name:"";
		$resultarr["state_id"]=($model->state_id!="")?$model->state_id:"";
		$resultarr["country_id"]=($model->country_id!="")?$model->country_id:"";
		return $resultarr;
	}

	public function checkApplicationStandardValid($data){
		$app_id = $data['app_id'];
		$standard_id = $data['standard_id'];
		

		$CertificateExist = Certificate::find()->where(['parent_app_id'=>$app_id,'standard_id'=>$standardID,'certificate_status'=>0 ])->orderBy(['version' => SORT_DESC])->one();
		if($CertificateExist === null){
			return false;
		}
		return true;
	}
	
	public function getUnitCoreProcessCount($data)
	{
		$modelProcess = new Process();
		$model = new Application();
		$connection = Yii::$app->getDb();
		$unit_id = $data['unit_id'];
		$no_of_core_process=0;
		$processcondition = '';

		$ApplicationUnit = ApplicationUnit::find()->where(['id'=> $unit_id])->one();
		if($ApplicationUnit!== null){
			if($ApplicationUnit->application->audit_type == $model->arrEnumAuditType['process_addition']){
				$processcondition = ' and uprs.process_type =1 ';
			}
		}
		$businessQry = 'SELECT prs.id FROM `tbl_application_unit_process` AS uprs
		INNER JOIN `tbl_process` AS prs ON prs.id=uprs.process_id AND prs.process_type='.$modelProcess->arrEnumProcessType['çore_process'].' AND uprs.unit_id='.$unit_id.' '.$processcondition.' group by uprs.process_id';
		$command = $connection->createCommand($businessQry);
		$result = $command->queryAll();	
		$no_of_core_process = count($result);				
		return $no_of_core_process;
	}
	
	public function getUnitTradingProcessCount($data)
	{
		$modelProcess = new Process();
		
		$connection = Yii::$app->getDb();
		$unit_id = $data['unit_id'];
		$no_of_trading_process=0;
		$businessQry = 'SELECT prs.id FROM `tbl_application_unit_process` AS uprs
		INNER JOIN `tbl_process` AS prs ON prs.id=uprs.process_id AND prs.process_type='.$modelProcess->arrEnumProcessType['trading'].' AND uprs.unit_id='.$unit_id.' group by uprs.process_id';
		$command = $connection->createCommand($businessQry);
		$result = $command->queryAll();	
		$no_of_trading_process = count($result);				
		return $no_of_trading_process;
	}

	public function getUnitProcess($data)
	{
		$modelProcess = new Process();
		$model = new Application();
		$connection = Yii::$app->getDb();
		$unit_id = $data['unit_id'];
		
		$processcondition = '';

		$ApplicationUnit = ApplicationUnit::find()->where(['id'=> $unit_id])->one();
		if($ApplicationUnit!== null){
			if($ApplicationUnit->application->audit_type == $model->arrEnumAuditType['process_addition']){
				$processcondition = ' and uprs.process_type =1 ';
			}
		}

		//AND prs.process_type='.$modelProcess->arrEnumProcessType['çore_process'].'
		$businessQry = 'SELECT prs.id,prs.name FROM `tbl_application_unit_process` AS uprs
		INNER JOIN `tbl_process` AS prs ON prs.id=uprs.process_id AND uprs.unit_id='.$unit_id.' '.$processcondition.' group by uprs.process_id';
		$command = $connection->createCommand($businessQry);
		$result = $command->queryAll();	
		if(count($result)>0){
			foreach($result as $processval){
				$processList[] = [
					'id' => $processval['id'],
					'name' => $processval['name'],
				];
				$processIds[] = $processval['id'];
			}
		}
		//$no_of_core_process = count($result);				
		return $processIds;
	}

	public function getUnitStandard($data)
	{
		
		$connection = Yii::$app->getDb();
		$unit_id = $data['unit_id'];
		
		$processcondition = '';
		$standardIds = [];
		$ApplicationUnit = ApplicationUnit::find()->where(['id'=> $unit_id])->one();
		if($ApplicationUnit !==null){
			$unitappstandard = $ApplicationUnit->unitappstandard;
			if(count($unitappstandard)>0){
				foreach($unitappstandard as $unitstandard){
					$standardIds[] = $unitstandard->standard_id;
				}
			}
		}
		return $standardIds;
	}

	public function UpdateApplicableDetails($data)
	{
		$audit_id = $data['audit_id'];
		$unit_id = $data['unit_id'];
		$report_name = $data['report_name'];
		$app_id = isset($data['app_id'])?$data['app_id']:'';
		$model = AuditReportApplicableDetails::find()->where(['audit_id'=> $audit_id])->andWhere(['unit_id'=> $unit_id])->andWhere(['report_name'=> $report_name])->andWhere(['status'=> 2])->one();
		if($model !== null)
		{
			$model->status = 1;
			$model->comments = '';
			$model->save();
		}else{
			$model = AuditReportApplicableDetails::find()->where(['app_id'=> $app_id])->andWhere(['unit_id'=> $unit_id])->andWhere(['report_name'=> $report_name])->andWhere(['status'=> 2])->one();
			if($model !== null)
			{
				$model->status = 1;
				$model->audit_id = $audit_id;
				$model->comments = '';
				$model->save();
			}
		}

	}

	public function getNCContent($apUnit){
	
		$unitSNo=1;
		$applicationUnit = $apUnit->unitdata;
		$unitName = $applicationUnit->name;
		$unitWiseFindingsContent = '';
		$auditmodel=new Audit();
		$ncnUnit = $apUnit->auditunitncn;
		$date_format = $this->getSettings('date_format');

		if($ncnUnit!==null)
		{
			$unitWiseFindingsContent.='<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;border:none;">';
						
			if($ncnUnit->effectiveness_of_corrective_actions!='')
			{
				$unitWiseFindingsContent.='<tr>
					<td class="reportDetailLayoutInner" style="border:none;">
					<p style="font-weight:bold;">Previously identified Non-conformities and Effectiveness of the Corrective actions:</p>
					<p>'.$ncnUnit->effectiveness_of_corrective_actions.'</p>
					</td>
				</tr>';
			}

			if($ncnUnit->audit_team_recommendation!='')
			{
				$unitWiseFindingsContent.='<tr>
					<td class="reportDetailLayoutInner" style="border:none;">
					<p style="font-weight:bold;margin-top:20px;">Audit Team Recommendation:</p>
					<p>'.$ncnUnit->audit_team_recommendation.'</p>
					</td>
				</tr>';
			}

			if($ncnUnit->summary_of_evidence!='')
			{
				$unitWiseFindingsContent.='<tr>
					<td class="reportDetailLayoutInner" style="border:none;">
					<p style="font-weight:bold;margin-top:20px;">Summary of evidence relating to the capability of client and its system to meet applicable requirements and expected outcomes:</p>
					<p>'.$ncnUnit->summary_of_evidence.'</p>
					</td>
				</tr>';
			}

			if($ncnUnit->potential_high_risk_situations!='')
			{
				$unitWiseFindingsContent.='<tr>
				<td class="reportDetailLayoutInner" style="border:none;">
				<p style="font-weight:bold;margin-top:20px;">Any Potential high-risk situations:</p>
				<p>'.$ncnUnit->potential_high_risk_situations.'</p>
				</td>
				</tr>';
			}

			if($ncnUnit->entities_and_processes_visited!='')
			{
				$unitWiseFindingsContent.='<tr>
				<td class="reportDetailLayoutInner" style="border:none;">
				<p style="font-weight:bold;margin-top:20px;">Entities and Processes visited (including facilities and subcontractors):</p>
				<p>'.$ncnUnit->entities_and_processes_visited.'</p>
				</td>
				</tr>';
			}

			if($ncnUnit->people_interviewed!='')
			{
				$unitWiseFindingsContent.='<tr>
				<td class="reportDetailLayoutInner" style="border:none;">
				<p style="font-weight:bold;margin-top:20px;">People interviewed:</p>
				<p>'.$ncnUnit->people_interviewed.'</p>
				</td>
				</tr>';
			}

			if($ncnUnit->type_of_documents_reviewed!='')
			{
				$unitWiseFindingsContent.='<tr>
				<td class="reportDetailLayoutInner" style="border:none;">
				<p style="font-weight:bold;margin-top:20px;">Type of documents reviewed:</p>
				<p>'.$ncnUnit->type_of_documents_reviewed.'</p>
				</td>
				</tr>';
			}

			$unitWiseFindingsContent.='</table>';
		}	
		
		$nccount = 0;
		$unitWiseFindingsContent.='<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
				<tr>
					<td style="text-align:center;font-weight:bold;width:20%;background:#2f4985;color: white;border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="10">Unit Findings - '.($unitName?$unitName:'NA').'</td>
				</tr>';
		$unitexecutionObj=$apUnit->unitexecution;
		if(count($unitexecutionObj)>0)
		{
			$unitWiseFindingsContent.='										
			<tr>
				<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">S.No</td>
				<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">STD Clause no.</td>	
				<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Standard Clause</td>
				<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Finding</td>	
				<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Confirming</td>
				<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Root Cause & Corrective Action</td>
				<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Due By</td>
				<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">LA Verification</td>
				<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Close Date</td>  
			</tr>';
						
			foreach($unitexecutionObj as $uExecution)
			{								
				$executionlistnoncomformityObj=$uExecution->executionlistnoncomformity;
				if(count($executionlistnoncomformityObj)>0)
				{
					$nccount = 1;
					foreach($executionlistnoncomformityObj as $noncomformityList)
					{
						$answer = ($noncomformityList->answer!=1)?'No':'Yes';
						
						$arrStdClause=array();
						$auditexecutioncheckliststandardObj=$noncomformityList->auditexecutioncheckliststandard;
						if(count($auditexecutioncheckliststandardObj)>0)
						{
							foreach($auditexecutioncheckliststandardObj as $auditexecutioncheckliststd)
							{
								$questionstandard=$auditexecutioncheckliststd->auditexecutionquestionstandard;
								if($questionstandard!==null)
								{
									$arrStdClause[]=array('clause_no'=>$questionstandard->clause_no,'clause'=>$questionstandard->clause);
								}
							}
						}

						
						$root_cause = 'NA';
						$corrective_action = 'NA';
						$la_verification = 'NA';
						$close_date = 'NA';
						if($noncomformityList->checklistremediationlatest){
							$root_cause = $noncomformityList->checklistremediationlatest->root_cause;
							$corrective_action = $noncomformityList->checklistremediationlatest->corrective_action;
							if($noncomformityList->checklistremediationlatest->reviewlatest){
								//$la_verification = $noncomformityList->checklistremediationlatest->reviewlatest->comment;
								if($noncomformityList->checklistremediationlatest->reviewlatest->status==1){
									$close_date = date($date_format,$noncomformityList->checklistremediationlatest->reviewlatest->created_at);
								}
							}
							if($noncomformityList->checklistremediationlatest->auditorreviewlatest){
								$la_verification = $noncomformityList->checklistremediationlatest->auditorreviewlatest->comment;
							}
						}
													

						
						$stdClauseCnt=0;
						$firstClauseNo='';
						$firstClause='';
						$unitWiseFindingsWithClauseContent='';
						$clausecount=count($arrStdClause);
						foreach($arrStdClause as $vals)
						{
							if($stdClauseCnt==0)
							{
								$firstClauseNo=$vals['clause_no'];
								$firstClause=$vals['clause'];
							}else{
								$unitWiseFindingsWithClauseContent.='<tr><td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$vals['clause_no'].'</td><td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$vals['clause'].'</td></tr>';
							}
							$stdClauseCnt++;
						}
						
						
						$unitWiseFindingsContent.='										
							<tr>
								<td style="text-align:center;" valign="middle" class="reportDetailLayoutInner" rowspan="'.$clausecount.'">'.$unitSNo.'</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$firstClauseNo.'</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$firstClause.'</td>
								<td style="text-align:left" valign="middle" class="reportDetailLayoutInner" rowspan="'.$clausecount.'">'.$noncomformityList->finding.'</td>
								<td style="text-align:center" valign="middle" class="reportDetailLayoutInner" rowspan="'.$clausecount.'">'.$noncomformityList->auditnonconformitytimeline->name.'</td>
								
							
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" rowspan="'.$clausecount.'">'.$root_cause.'</td>
								
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" rowspan="'.$clausecount.'">'.($noncomformityList->due_date!=''?date($date_format,strtotime($noncomformityList->due_date)):'').'</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" rowspan="'.$clausecount.'">'.$la_verification.'</td>
								<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" rowspan="'.$clausecount.'">'.$close_date.'</td>
							</tr>';
						$unitWiseFindingsContent.=$unitWiseFindingsWithClauseContent;	
						
						$unitSNo++;	
					}									
				}
			}
		}
		/*else{
			$unitWiseFindingsContent.='
			<tr>
				<td style="text-align:center;font-weight:bold;width:20%;border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="10">No findings found.</td>
			</tr>';
		}	*/
		if($nccount ==0){
			$unitWiseFindingsContent.='
			<tr>
				<td style="text-align:center;font-weight:bold;width:20%;border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="10">No findings found.</td>
			</tr>';
		}
		
	
		return $unitWiseFindingsContent.='</table>';	
	}

	public function getAuditUnitStandard($data)
	{
		
		$connection = Yii::$app->getDb();
		$unit_id = $data['unit_id'];
		
		$processcondition = '';
		$standardIds = [];
		$ApplicationUnit = ApplicationUnit::find()->where(['id'=> $unit_id])->one();
		if($ApplicationUnit !==null){
			$unitappstandard = $ApplicationUnit->unitappstandard;
			if(count($unitappstandard)>0){
				foreach($unitappstandard as $unitstandard){

					/*
					$ReductionStandard = ReductionStandard::find()->where(['code'=>$unitstandard->standard->code ])->one();
					if($ReductionStandard!==null){
						$ApplicationUnitCertifiedStandard = ApplicationUnitCertifiedStandard::find()->where(['standard_id'=>$ReductionStandard->id ,'unit_id'=>$unit_id ])->one();
						if($ApplicationUnitCertifiedStandard !== null){
							continue;
						}
					}
					*/

					
					$standardIds[] = $unitstandard->standard_id;
				}
			}
		}
		return $standardIds;
	}

	public function getReportsAccessible($chkdata,$standardids=[]){

		$report_name = $chkdata['report_name'];
		$unit_id = isset($chkdata['unit_id'])?$chkdata['unit_id']:'';
		if(isset($chkdata['sub_topic_id']) && is_array($chkdata['sub_topic_id'])){
			$sub_topic_id = $chkdata['sub_topic_id'];
		}else{
			$sub_topic_id = isset($chkdata['sub_topic_id'])?explode(',',$chkdata['sub_topic_id']):'';
		}
		
		if(count($standardids)<=0){
			$standardids = $this->getAuditUnitStandard($chkdata);
		}
		
		if(count($standardids)>0){
			$AuditReportDisplay = AuditReportDisplay::find()->where(['report_name'=>$report_name])->innerJoinWith(['standardlist as standardlist'])->andWhere(['standardlist.standard_id'=>$standardids]);
		
			if($sub_topic_id!='' && count($sub_topic_id)>0){
				$AuditReportDisplay = $AuditReportDisplay->andWhere(['or',['topic_id'=> $sub_topic_id ],['topic_id'=>''],['topic_id'=>null]]);
			}
				
	
			$AuditReportDisplay = $AuditReportDisplay->one();
			if($AuditReportDisplay !== null){
				return true;
			}
		}

		
		return false;
	}

	public function getSubtopic($unit_id,$audit_plan_unit_id='',$userid='',$certifiedstandard=0){
		//$query = '';
		// AND aeq.sub_topic_id IS NULL
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		
		$connection = Yii::$app->getDb();
		$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();

		$condition = '';
		$plancondition = '';
		if($unit_id){
			$condition .= " AND unit.id=".$unit_id;
		}
		$unitstandards = [];
		$conditionunitstandard = '';
		$conditionquestionstandard = '';
		if($certifiedstandard){
			//$unit_id 
			$unit = ApplicationUnit::find()->where(['id'=>$unit_id ])->one();
			if($unit->unitmanday->adjusted_manday=='0.00' || $unit->unitmanday->adjusted_manday==0)
			{
				return [];
			}

			$ApplicationUnitStandard = ApplicationUnitStandard::find()->where(['unit_id'=>$unit_id ])->all();
			if(count($ApplicationUnitStandard)>0){
				foreach($ApplicationUnitStandard as $appunitstd){
					$alreadycertified = 0;
					/*
					$ReductionStandard = ReductionStandard::find()->where(['code'=>$appunitstd->standard->code ])->one();
					if($ReductionStandard!==null){
						$ApplicationUnitCertifiedStandard = ApplicationUnitCertifiedStandard::find()->where(['standard_id'=>$ReductionStandard->id ,'unit_id'=>$unit_id ])->one();
						if($ApplicationUnitCertifiedStandard !== null){
							$alreadycertified = 1;
						}
					}
					*/
					//if(!$alreadycertified){
						$unitstandards[] = $appunitstd->standard_id;
					//}
				}
			}
			if(count($unitstandards)>0){
				$conditionunitstandard = " and ustd.standard_id in (".implode(',', $unitstandards).") ";

				$conditionquestionstandard = " and aeqs.standard_id in (".implode(',', $unitstandards).") ";
			}else{
				return [];
			}
			

		}

		if($audit_plan_unit_id!=''){
			$plancondition .= " and execution.audit_plan_unit_id=".$audit_plan_unit_id;
		}
		

		if($userid){
			$condition .= " AND ( execution.executed_by is null OR execution.executed_by=".$userid.")";
		}
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		$command = $connection->createCommand("SELECT user.first_name,user.last_name,execution.status,execution.executed_by,execution.executed_date,subtopic.id,
			subtopic.name FROM `tbl_application_unit` AS unit 
			INNER JOIN `tbl_application_unit_process` AS uprs ON unit.id=uprs.unit_id 
			INNER JOIN `tbl_application_unit_standard` AS ustd ON unit.id=ustd.unit_id ".$conditionunitstandard." 
			INNER JOIN `tbl_audit_execution_question_process` AS aeqp ON uprs.process_id=aeqp.process_id 
			INNER JOIN `tbl_audit_execution_question_standard` AS aeqs ON ustd.standard_id=aeqs.standard_id ".$conditionquestionstandard." 
			INNER JOIN `tbl_audit_execution_question` AS aeq ON aeqp.audit_execution_question_id=aeq.id AND  aeqs.audit_execution_question_id=aeq.id 
			INNER JOIN `tbl_sub_topic` AS subtopic ON subtopic.id=aeq.sub_topic_id 

			LEFT JOIN `tbl_audit_plan_unit_execution` AS execution on execution.sub_topic_id = subtopic.id ".$plancondition." 
			LEFT JOIN `tbl_users` AS user on user.id = execution.executed_by  
			WHERE 1=1  ".$condition." 
			AND aeq.status=0 AND subtopic.status=0 
			GROUP BY subtopic.id");
		$result = $command->queryAll();
		 

		return $result;

	}

	public function getCurrentSubtopic($unit_id,$audit_plan_unit_id='',$userid=''){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		
		$connection = Yii::$app->getDb();
		$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();

		$condition = '';
		$conditionexe = '';
		if($unit_id){
			$condition .= " AND unit.id=".$unit_id;
		}
		if($audit_plan_unit_id!=''){
			$conditionexe .= " and execution.audit_plan_unit_id=".$audit_plan_unit_id;
		}
		

		if($userid){
			$condition .= " AND ( execution.executed_by is null OR execution.executed_by=".$userid.")";
		}
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		$command = $connection->createCommand("SELECT user.first_name,user.last_name,execution.status,execution.executed_by,execution.executed_date,subtopic.id,
			subtopic.name FROM `tbl_application_unit` AS unit 
			INNER JOIN `tbl_audit_plan_unit` as planunit on unit.id=planunit.unit_id 
			INNER JOIN `tbl_audit_plan_unit_execution` AS execution on planunit.id= execution.audit_plan_unit_id ".$conditionexe." 
			INNER JOIN `tbl_sub_topic` AS subtopic ON execution.sub_topic_id = subtopic.id 
			LEFT JOIN `tbl_users` AS user on user.id = execution.executed_by  
			WHERE 1=1  ".$condition." GROUP BY subtopic.id");
		$result = $command->queryAll();
		
		return $result;

	}

	public function getCurrentSubtopicIds($unit_id){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$subtopicIDs = [];
		$ApplicationUnitSubtopic = ApplicationUnitSubtopic::find()->where(['unit_id'=>$unit_id])->all();
		if(count($ApplicationUnitSubtopic)>0){
			foreach($ApplicationUnitSubtopic as $subtopicobj){
				$subtopicIDs[] = $subtopicobj->subtopic_id;
			}
		}
		return $subtopicIDs;

	}
	public function getCurrentExecutionSubtopicIds($audit_plan_unit_id){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$subtopicIDs = [];
		$AuditPlanUnitExecution = AuditPlanUnitExecution::find()->where(['audit_plan_unit_id'=>$audit_plan_unit_id])->all();
		if(count($AuditPlanUnitExecution)>0){
			foreach($AuditPlanUnitExecution as $subtopicobj){
				$subtopicIDs[] = $subtopicobj->sub_topic_id;
			}
		}
		return $subtopicIDs;

	}
	public function getbasicreportlist($data){
		//$data = yii::$app->request->post();
		$date_format = $this->getSettings('date_format');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];

		$environmentliststatus = false;
		$clientinformation_liststatus = false;
		$app_id = $data['app_id'];
		//$offermodel = new Offer();
		//enumStatus
		/*
		$Offer = Offer::find()->where(['app_id'=>$app_id,'status'=>$offermodel->enumStatus['finalized'] ])->one();
		if($Offer !== null){
			$applicableforms = [];
			ClientInformationChecklist
			$applicableforms['clientinformation_list'] =
			//$applicableforms['environment_list'] = $environmentliststatus;
			return $this->asJson($applicableforms);
		}
		*/
		
		$ApplicationUnit = ApplicationUnit::find()->where(['app_id'=>$app_id])->all();
		$applicableforms = [];
		if(count($ApplicationUnit)>0){
			foreach($ApplicationUnit as $appunit){

				$subtopicArr = $this->getCurrentSubtopicIds($appunit->id);
				
				if(count($subtopicArr)>0){
					$chkdata = ['unit_id'=>$appunit->id,'sub_topic_id'=>$subtopicArr];
					$chkdata['report_name']='environment_list';
					$formstatus = $this->getReportsAccessible($chkdata);
					$applicableforms[$appunit->id]['environment_list'] = $formstatus;
					if($formstatus){
						$environmentliststatus = true;
					}
	
	
					//$chkdata = ['unit_id'=>$appunit->id,'report_name'=>'clientinformation_list'];
					$chkdata['report_name']='clientinformation_list';
					$formstatus = $this->getReportsAccessible($chkdata);
					$applicableforms[$appunit->id]['clientinformation_list'] = $formstatus;
					if($formstatus){
						$clientinformation_liststatus = true;
					}
				}
				
				
			}
		}
		$Application = Application::find()->where(['id'=>$app_id])->one();
		if($Application->audit_type == $Application->arrEnumAuditType['normal']){
			$subtopicArr = $this->getCurrentSubtopicIds($Application->applicationscopeholder->id);
			$chkdata = ['unit_id'=>$Application->applicationscopeholder->id,'sub_topic_id'=>$subtopicArr,'report_name'=>'clientinformation_list'];
			$applicableforms['clientinformation_list'] = $this->getReportsAccessible($chkdata);
		}else{
			$applicableforms['clientinformation_list'] = $clientinformation_liststatus;
			/*
			$applicationstandard = $Application->applicationstandard;
			if(count($applicationstandard)>0){
				$subtopicArr = $this->getCurrentSubtopicIds($Application->applicationscopeholder->id);
				$chkdata = ['unit_id'=>$Application->applicationscopeholder->id,'sub_topic_id'=>$subtopicArr,'report_name'=>'clientinformation_list'];
				$applicableforms['clientinformation_list'] = $this->getReportsAccessible($chkdata);
			}
			*/
		}
			

		/*
		$result = $this->getSubtopic($Application->applicationscopeholder->id);
		$subtopicArr = [];
		if(count($result)>0){
			foreach($result as $subdata){
				$subtopicArr[] =$subdata['id'];
			}
		}
		*/
		
		$applicableforms['environment_list'] = $environmentliststatus;
		return $applicableforms;
	}

	public function getCustomerNumber($app_id){
		$Application = Application::find()->where(['id'=>$app_id])->one();
		$customer_number = '';
		if($Application !== null){
			if($Application->customer !== null){
				$customer_number = $Application->customer->customer_number;
			}
		}
		return $customer_number;
	}
	
	public function verifyReCaptcha($token)
	{
		 
		$secretKey = Yii::$app->params['reCaptchaSecretKey'];
		$reCaptchaVerifyURL = Yii::$app->params['reCaptchaVerifyURL'];
		$response = $token;
		
		$ch = curl_init();                    // Initiate cURL
		$url = $reCaptchaVerifyURL; // Where you want to post data
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, true);  // Tell cURL you want to post something
		curl_setopt($ch, CURLOPT_POSTFIELDS, "secret=".$secretKey."&response=".$response); // Define what you want to post
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the output in string format
		$output = curl_exec ($ch); // Execute
		curl_close ($ch); // Close cURL handle		
		$jsonResponse = json_decode($output,true);	
		//var_dump($output); // Show output	
		return $jsonResponse;
		
		
		//return array('success'=>true);
	}

	public function getUnannouncedBusinessSector($audit_id,$applicationunitid){
		$arrbusinessector = [];
		$Audit = Audit::find()->where(['id'=>$audit_id])->one();
		if($Audit !== null){
			//if($Audit->audit_type == $Audit->audittypeEnumArr['unannounced_audit']){
				$UnannouncedAuditApplication = UnannouncedAuditApplication::find()->where(['audit_id'=> $audit_id ])->one();
				/*
				->innerJoinWith(['unannouncedauditunit as unannouncedauditunit'])
				->andWhere(['unannouncedauditunit.unit_id'=>$applicationunitid ])
				*/
				

				if($UnannouncedAuditApplication !== null){
					//
					$UnannouncedAuditApplicationUnit = UnannouncedAuditApplicationUnit::find()->where(['unannounced_audit_app_id'=>$UnannouncedAuditApplication->id,'unit_id'=>$applicationunitid ])->one();
					if($UnannouncedAuditApplicationUnit !== null){
						if(count($UnannouncedAuditApplicationUnit->unannouncedauditunitbsector)>0){
							foreach($UnannouncedAuditApplicationUnit->unannouncedauditunitbsector as $unitbsectorsobj){
								$arrbusinessector[] = $unitbsectorsobj->business_sector_id;
							}
						}
					}
				}
			//}
		}
		return $arrbusinessector;
	}

	public function getUnannouncedBusinessSectorGroups($audit_id,$applicationunitid){
		$arrbusinessectorgroup = [];
		$Audit = Audit::find()->where(['id'=>$audit_id])->one();
		if($Audit !== null){
			//if($Audit->audit_type == $Audit->audittypeEnumArr['unannounced_audit']){
				$UnannouncedAuditApplication = UnannouncedAuditApplication::find()->where(['audit_id'=> $audit_id ])->one();
				/*
				->innerJoinWith(['unannouncedauditunit as unannouncedauditunit'])
				->andWhere(['unannouncedauditunit.unit_id'=>$applicationunitid ])
				*/
				

				if($UnannouncedAuditApplication !== null){
					//
					$UnannouncedAuditApplicationUnit = UnannouncedAuditApplicationUnit::find()->where(['unannounced_audit_app_id'=>$UnannouncedAuditApplication->id,'unit_id'=>$applicationunitid ])->one();
					if($UnannouncedAuditApplicationUnit !== null){
						
						if(count($UnannouncedAuditApplicationUnit->unitbsectorgroups)>0){
							foreach($UnannouncedAuditApplicationUnit->unitbsectorgroups as $unitbsectorsobj){
								$arrbusinessectorgroup[] = $unitbsectorsobj->business_sector_group_id;
							}
						}
						/*
						if(count($UnannouncedAuditApplicationUnit->unannouncedauditunitbsector)>0){
							foreach($UnannouncedAuditApplicationUnit->unannouncedauditunitbsector as $unitbsectorsobj){
								$arrbusinessector[] = $unitbsectorsobj->business_sector_id;
							}
						}
						*/
					}
				}
			//}
		}
		return $arrbusinessectorgroup;
	}


	public function getUnannouncedSubtopic($unit_id, $unitstandards=[]){
		//$query = '';
		// AND aeq.sub_topic_id IS NULL
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		
		$connection = Yii::$app->getDb();
		$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();

		$condition = '';
		$plancondition = '';
		if($unit_id){
			$condition .= " AND unit.id=".$unit_id;
		}
		
		$conditionunitstandard = '';
		$conditionquestionstandard = '';
		if(count($unitstandards)>0){
			$conditionunitstandard = " and ustd.standard_id in (".implode(',', $unitstandards).") ";

			$conditionquestionstandard = " and aeqs.standard_id in (".implode(',', $unitstandards).") ";
		}else{
			return [];
		}
			

		 

		 
		 
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		$command = $connection->createCommand("SELECT subtopic.id, subtopic.name FROM `tbl_application_unit` AS unit 
			INNER JOIN `tbl_application_unit_process` AS uprs ON unit.id=uprs.unit_id 
			INNER JOIN `tbl_application_unit_standard` AS ustd ON unit.id=ustd.unit_id ".$conditionunitstandard." 
			INNER JOIN `tbl_audit_execution_question_process` AS aeqp ON uprs.process_id=aeqp.process_id 
			INNER JOIN `tbl_audit_execution_question_standard` AS aeqs ON ustd.standard_id=aeqs.standard_id ".$conditionquestionstandard." 
			INNER JOIN `tbl_audit_execution_question` AS aeq ON aeqp.audit_execution_question_id=aeq.id AND  aeqs.audit_execution_question_id=aeq.id 
			INNER JOIN `tbl_sub_topic` AS subtopic ON subtopic.id=aeq.sub_topic_id 

			WHERE 1=1  ".$condition." AND aeq.status=0 AND subtopic.status=0 GROUP BY subtopic.id");
			 
		$result = $command->queryAll();
		 

		return $result;

	}

	public function getUnannoucedAuditUnit($audit_id){
		$units = [];
		$UnannouncedAuditApplication = UnannouncedAuditApplication::find()->where(['audit_id'=>$audit_id])->one();
		if($UnannouncedAuditApplication !== null){
			if(count($UnannouncedAuditApplication->unannouncedauditunit)>0){
				foreach($UnannouncedAuditApplication->unannouncedauditunit as $planunitobj){
					$units[] = [
						'id' => $planunitobj->unit_id,
						'name' => $planunitobj->applicationunit->name,
					];
				}
			}
		}
		return $units;
	}

	public function getUnannoucedAuditStandard($audit_id){
		$appStandardArr = [];
		$UnannouncedAuditApplication = UnannouncedAuditApplication::find()->where(['audit_id'=>$audit_id])->one();
		$applicationstd = $UnannouncedAuditApplication->unannouncedauditstandard;
		if(count($applicationstd)>0)
		{
			foreach($applicationstd as $appstandard)
			{
				$appStandardArr[]=[
					'name'=>$appstandard->standard->name,
					'code'=>$appstandard->standard->code,
					'id'=>$appstandard->standard->id
				];
			}
		}
		return $appStandardArr;
	}

	public function getAuditStandard($audit_id){
		$appStandardArr = [];
		$arrcodes = [];
		$Audit = Audit::find()->where(['id'=>$audit_id])->one();
		if($Audit !== null){
			if($Audit->audit_type == 2){
				$UnannouncedAuditApplication = UnannouncedAuditApplication::find()->where(['audit_id'=>$audit_id])->one();
				$applicationstd = $UnannouncedAuditApplication->unannouncedauditstandard;
				if(count($applicationstd)>0)
				{
					foreach($applicationstd as $appstandard)
					{
						$appStandardArr[]=[
							'name'=>$appstandard->standard->name,
							'code'=>$appstandard->standard->code,
							'id'=>$appstandard->standard->id
						];
						$arrcodes[] = $appstandard->standard->code;
					}
				}
			}else{
				
				$applicationstd=$Audit->application->applicationstandard;
				if(count($applicationstd)>0)
				{
					foreach($applicationstd as $appstandard)
					{
						$appStandardArr[]=[
							'name'=>$appstandard->standard->name,
							'code'=>$appstandard->standard->code,
							'id'=>$appstandard->standard->id
						];
						$arrcodes[] = $appstandard->standard->code;
					}
				}
				 
			}
			
		}
		
		return ['standards'=>$appStandardArr,'stdcode'=>$arrcodes];
	}

	public function canChangeMaterialComp($app_id,$audit_id=''){
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		
		$canChangeMaterialComp = 0;
		$AuditModel = new Audit();
		$model = Application::find()->where(['id' => $app_id])->one();
		 
		if($audit_id ==''){
			$Audit = Audit::find()->where(['app_id' => $app_id, 'audit_type' =>  $AuditModel->audittypeEnumArr['normal_audit'] ])->one();
		}else{
			$Audit = Audit::find()->where(['id' => $audit_id, 'audit_type' =>  $AuditModel->audittypeEnumArr['normal_audit'] ])->one();
		}
		

		if($model->status > $model->arrEnumStatus['submitted'] 
		&& ($model->audit_type == $model->arrEnumAuditType['normal'] || $model->audit_type == $model->arrEnumAuditType['standard_addition']) 
		&& ($Audit === null || $Audit->auditplan === null || ($Audit->auditplan->status <= $Audit->auditplan->arrEnumStatus['waiting_for_review']) )
		){
			if($resource_access ==1 
			|| ($user_type == 1 && in_array('manage_product_material_composition',$rules)) 
			|| ($user_type == 2 &&  $model->customer_id == $userid) 
			|| ($user_type == 3) 
			){
				if($user_type == 3 && $is_headquarters==1){
					$canChangeMaterialComp = 1;
				}else if($user_type == 3 && $resource_access == '5' && $model->franchise_id == $franchiseid && $is_headquarters!=1){
					$canChangeMaterialComp = 1;
				}else if($user_type == 3 && $model->franchise_id == $userid && $is_headquarters!=1){
					$canChangeMaterialComp = 1;
				}else if($user_type != 3){
					$canChangeMaterialComp = 1;
				}
			}
			
		}
		return $canChangeMaterialComp;
	}
	
	public function getFranchiseDetails($franchisedetails)
	{
		$arrFrachiseDetails=array();
		if($franchisedetails !== null)
		{
			$usercompanyinfo = $franchisedetails->usercompanyinfo;
			if($usercompanyinfo !== null){
				$arrFrachiseDetails=[
					'company_name' => $usercompanyinfo->company_name,
					'contact_name' => $usercompanyinfo->contact_name,
					'telephone' => $usercompanyinfo->company_telephone,
					'email' => $usercompanyinfo->company_email,
					'website' => $usercompanyinfo->company_website,
					'address' => $usercompanyinfo->company_address1.' '.($usercompanyinfo->company_address2?', '.$usercompanyinfo->company_address2:''),
					//'company_address2' => $usercompanyinfo->company_address2,
					'city' => $usercompanyinfo->company_city,
					'zipcode' => $usercompanyinfo->company_zipcode,
					'country' => $usercompanyinfo->companycountry->name,
					'state' => $usercompanyinfo->companystate->name,
					'mobile' => $usercompanyinfo->mobile,
					'gst_no' => $usercompanyinfo->gst_no,
				];
			}					
		}		
		return $arrFrachiseDetails;
	}
	
	public function cryptoJsAesDecrypt($passphrase, $jsonString)
	{
		$jsondata = json_decode($jsonString, true);
		$salt = hex2bin($jsondata["s"]);
		$ct = base64_decode($jsondata["ct"]);
		$iv  = hex2bin($jsondata["iv"]);
		$concatedPassphrase = $passphrase.$salt;
		$md5 = array();
		$md5[0] = md5($concatedPassphrase, true);
		$result = $md5[0];
		for ($i = 1; $i < 3; $i++) {
			$md5[$i] = md5($md5[$i - 1].$concatedPassphrase, true);
			$result .= $md5[$i];
		}
		$key = substr($result, 0, 32);
		$data = openssl_decrypt($ct, 'aes-256-cbc', $key, true, $iv);
		return json_decode($data, true);
	}
	
	public function cryptoJsAesEncrypt($passphrase, $value)
	{
		$salt = openssl_random_pseudo_bytes(8);
		$salted = '';
		$dx = '';
		while (strlen($salted) < 48) {
			$dx = md5($dx.$passphrase.$salt, true);
			$salted .= $dx;
		}
		$key = substr($salted, 0, 32);
		$iv  = substr($salted, 32,16);
		$encrypted_data = openssl_encrypt(json_encode($value), 'aes-256-cbc', $key, true, $iv);
		$data = array("ct" => base64_encode($encrypted_data), "iv" => bin2hex($iv), "s" => bin2hex($salt));
		return json_encode($data);
	}
		
}    
?>