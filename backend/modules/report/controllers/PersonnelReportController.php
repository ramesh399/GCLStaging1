<?php
namespace app\modules\report\controllers;

use Yii;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationChangeAddress;
use app\modules\master\models\User;
use app\modules\master\models\Role;
use app\modules\master\models\UserRole;
use app\modules\master\models\Standard;
use app\modules\master\models\UserRoleBusinessGroupCode;
use app\modules\master\models\UserRoleBusinessGroup;
use app\modules\master\models\UserCompanyInfo;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * PersonnelReportController implements the CRUD actions for Product model.
 */
class PersonnelReportController extends \yii\rest\Controller
{
	private $styleWhite = array('font'  => array('name'  => 'Arial','color' => array('rgb' => 'FFFFFF'),'bold'  => true,'size'  => 10,));
	private $styleBgColor = array('fill' => array('type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'color' => array('rgb' => '578CDE')));
	private $styleCenter = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,));
	private $styleLeft = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT));
	private $styleVCenter = array('alignment' => array('vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER));
	private $styleHigh = array('font'  => array('color' => array('rgb' => 'FF0000'),'bold'  => true,));
	private $styleMedium = array('font'  => array('color' => array('rgb' => 'F79647'),'bold'  => true,));
	private $styleLow = array('font'  => array('color' => array('rgb' => '00B050'),'bold'  => true,));
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
		if(!Yii::$app->userrole->hasRights(array('auditor_report')))
		{
			return false;
		}
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();


		$responsedata =array('status'=>0,'message'=>"No Data Found");
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
		/*		
		$model = User::find()->select('userrole.franchise_id as role_franchise_id,role.role_name as role_name,t.id,t.registration_id,t.country_id,t.first_name,t.last_name,t.franchise_id,userrole.role_id as role_id')->where(['t.status'=> 0])->alias('t');			
		$model = $model->join('inner join', 'tbl_user_role as userrole',' userrole.user_id=t.id AND userrole.status=0 AND userrole.login_status=1 ');
		$model = $model->join('inner join', 'tbl_role as role',' userrole.role_id=role.id');
		$model = $model->join('inner join', 'tbl_rule as rule',' userrole.role_id=rule.id');
		$model = $model->join('left join', 'tbl_user_standard as user_standard','user_standard.user_id=t.id ');
		*/
		//->select('userrole.franchise_id as franchise_id,role.role_name as role_name,t.id,t.registration_id,t.country_id,t.first_name,t.last_name ,userrole.role_id as role_id')
		$model = UserRole::find()->where(['userrole.approval_status'=> 2])->alias('userrole');
		$model = $model->join('inner join', 'tbl_users as t',' userrole.user_id=t.id AND userrole.status=0 AND userrole.login_status=1 ');
		$model = $model->join('inner join', 'tbl_role as role',' userrole.role_id=role.id');
		//$model = $model->join('left join', 'tbl_rule as rule',' userrole.role_id=rule.id');
		$model = $model->join('left join', 'tbl_user_standard as user_standard','user_standard.user_id=t.id ');

		if(isset($post['standard_id']) && is_array($post['standard_id']) && count($post['standard_id'])>0)
		{
			$model = $model->andWhere(['user_standard.standard_id'=> $post['standard_id']]);
		}
		
		if(isset($post['oss_id']) && is_array($post['oss_id']) && count($post['oss_id'])>0)
		{
			$model = $model->andWhere(['userrole.franchise_id'=> $post['oss_id']]);				
		}
		else
		{
			if($is_headquarters != 1)
			{
				$model = $model->andWhere(['userrole.franchise_id'=> $franchiseid]);	
			}
		}

		if(isset($post['usertype']) && $post['usertype'] !='')
		{
			$model = $model->andWhere(['role.id'=> $post['usertype']]);	
		}
		//$model = $model->andWhere('(role.resource_access=3 or rule.privilege in ("audit_execution"))');
		//if(isset($post['usertype']) && $post['usertype'] !='')
		//{
			//if($post['usertype']=='1')
			//{
				//$model = $model->andWhere(['role.resource_access'=> 2]);		
			//}
			//else
			//{
				//$model = $model->andWhere(['rule.privilege'=> 'audit_execution']);
			//}		
			//$model = $model->andWhere('(role.resource_access=2 or rule.privilege in ("audit_execution"))');
		//}
		//else
		//{
			//$model = $model->andWhere(['role.resource_access'=> 2]);
			//$model = $model->andWhere(['rule.privilege'=> 'audit_execution']);
		//}
	
		if(isset($post['from_date']) && $post['from_date'] !='' && isset($post['to_date']) && $post['to_date'] !='')
		{
			$model = $model->andWhere(['>=','userrole.approval_date', strtotime($post['from_date'])]);				
			$model = $model->andWhere(['<=','userrole.approval_date', strtotime($post['to_date'])]);
		}		
		$model = $model->groupBy(['t.id','userrole.role_id']);
		
		$app_list=array();
		$model = $model->all();	
		//echo count($model);
		if(count($model)>0)
		{
			$connection = Yii::$app->getDb();
			$stdarr = [];
			$Standard = Standard::find()->where(['status'=>0])->all();
			if(count($Standard)>0){
				foreach($Standard as $stdobj){
					$stdarr[] = ['id'=>$stdobj->code,'code'=>$stdobj->code,'lowercode'=>strtolower($stdobj->code)];
				}
			}
			if($post['type']!='submit')
			{
				$arrHeaderLabel=array('Auditor GCL ID','OSS ID','Country','Name and Surname','User role');
				//,'OCS Stnd. Approval expiry date date','RCS Stnd. Approval expiry date date','GRS Stnd. Approval expiry date date','GOTS Stnd. Approval expiry date date','OTHER STANDARD APPROVAL DATES','OTHER STANDARD APPROVAL DATES','OTHER STANDARD APPROVAL DATES','Group. 1 Approval','Group.. 2 Approval','Group.. 3 Approval','Group. 4 Approval','Group. 5 Approval','Other Group Approval','Other Group Approval','Other Group Approval','Other Group Approval');
				$styleWhite = array('font'  => array('name'  => 'Arial','color' => array('rgb' => 'FFFFFF'),'bold'  => true,'size'  => 10,));
				$styleBgColor = array('fill' => array('type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'color' => array('rgb' => '578CDE')));
				$styleCenter = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,));
				$styleLeft = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT));
				$styleVCenter = array('alignment' => array('vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER));
				$styleHigh = array('font'  => array('color' => array('rgb' => 'FF0000'),'bold'  => true,));
				$styleMedium = array('font'  => array('color' => array('rgb' => 'F79647'),'bold'  => true,));
				$styleLow = array('font'  => array('color' => array('rgb' => '00B050'),'bold'  => true,));
				
				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet();
				
				$column = 'A';
				foreach($arrHeaderLabel as $headerKey=>$headerLabel)
				{
					$sheet->setCellValue($column.'1', $headerLabel);
					$defaultWidth=25;
					if($column=='B'){
						$defaultWidth=15;
					}
					$sheet->getColumnDimension($column)->setWidth($defaultWidth);
					$column++;
				}			
				foreach($stdarr as $master_stddata){
					$sheet->getColumnDimension($column)->setWidth(30);
					$sheet->setCellValue($column.'1', $master_stddata['code'].' Standard Approval expiry date');$column++;
				}
				$gcnt = 1;
				$lastcolumn = '';
				foreach($stdarr as $master_stddata){
					$lastcolumn = $column;
					$sheet->getColumnDimension($column)->setWidth(30);
					$sheet->setCellValue($column.'1', $master_stddata['code'].' Group Approval');$column++;
				}
				 

				$i=2;
			}	
			
			foreach($model as $userroledata)
			{
				$user = $userroledata->user;
				//return '=='.$userroledata->franchise_id; die;
				$UserCompanyInfo = UserCompanyInfo::find()->where(['user_id'=> $userroledata['franchise_id']])->one();
				$data=array();				
				$data['user_id']=$user['registration_id'];
				//$data['oss']=($user->franchise_id)?$user->franchise->usercompanyinfo->osp_details:'';
				$data['oss'] = 'OSS '.($UserCompanyInfo?$UserCompanyInfo->osp_number:'');
				$data['country']=$user?$user->country->name:'';
				$data['name']=$user ? $user->first_name." ".$user->last_name:'';

				$data['user_role']= $userroledata->role->role_name;
				// if($user['resource_access']==2)
				// {
				// 	$data['user_role']='Technical Expert';
				// }
				// $userrole = ($user->usersrole->role)?$user->usersrole->role->resource_access:'';
				// if($userrole==2)
				// {
					
				// }
				foreach($stdarr as $master_stddata){
					$data[$master_stddata['lowercode'].'_expiry_date']='None';

					$data[$master_stddata['lowercode'].'_sector_groups']='None';
				}
				//$data['gots_expiry_date']='';
				//$data['ocs_expiry_date']='';
				//$data['grs_expiry_date']='';
				//$data['rcs_expiry_date']='';
				$user_standard=$user?$user->userstandard:[];
				if(count($user_standard)>0)
				{
					$standard_names='';
					$app_standard=[];
					foreach($user_standard as $std)
					{
						$stdcode = strtolower($std->standard->code);
						$data[$stdcode.'_expiry_date']=($std['witness_valid_until'] !='0000-00-00' && $std['witness_valid_until'] !=null && $std['witness_valid_until']!='1970-01-01')?date($date_format,strtotime($std['witness_valid_until'])):'None';

						/*
						$arr_business_sector_ids = [];
						$arr_role_bgroup_ids = [];
						$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
						$command = $connection->createCommand("SELECT group_concat(id) as role_bgroup_ids, group_concat(business_sector_id) as business_sector_ids from tbl_user_role_business_group as bg ".
							" WHERE  role_id='".$user['role_id']."' and standard_id='".$std->standard_id."' and user_id='".$user->id."' ".
							" group by user_id ");
						$result = $command->queryOne();
						if($result  !== false){
							$arr_business_sector_ids = array_unique(explode(',',$result['business_sector_ids']));
							$arr_role_bgroup_ids = array_unique(explode(',',$result['role_bgroup_ids']));
						}
						$gdetails = [];
						$data[$stdcode.'_sector_groups'] = '';
						foreach($arr_business_sector_ids as $bsectorid){
							$UserRoleBusinessGroupCode = UserRoleBusinessGroupCode::find()->where(['business_group_id'=>$arr_role_bgroup_ids])->all();
							print_r($arr_role_bgroup_ids);
							print_r($bsectorid);
							
							if(count($UserRoleBusinessGroupCode)>0){
								foreach($UserRoleBusinessGroupCode as $gcode){
									$bsectorname = $gcode->businesssector->name;
									$gdata[] = $gcode->sectorgroup->group_code;
								}
								$sectorgroupnames = implode(', ',$gdata);
								$gdetails[] = ['sector_name'=>$bsectorname, 'codes'=> $sectorgroupnames];

								$data[$stdcode.'_sector_groups'] .= $bsectorname.': '.$sectorgroupnames."\n";
							}
						}
						*/
						$databgroupdata = [];
						$UserRoleBusinessGroup = UserRoleBusinessGroup::find()->where(['role_id'=>$userroledata->role_id, 'standard_id'=>$std->standard_id, 'user_id'=>$user->id ])->all();
						if(count($UserRoleBusinessGroup)>0){
							foreach($UserRoleBusinessGroup as $grouprow){
								if(!isset($databgroupdata[$grouprow->business_sector_id])){
									$databgroupdata[$grouprow->business_sector_id] = ['name'=>$grouprow->businesssector->name, 'codelist'=>[]];
								}
								

								$groupcodelist = [];
								if(count($grouprow->rolegroupcode)>0){
									foreach($grouprow->rolegroupcode as $groupcoderow){
										$groupcodelist[] = $groupcoderow->sectorgroup->group_code;
									}
								}
								$databgroupdata[$grouprow->business_sector_id]['codelist'] = $databgroupdata[$grouprow->business_sector_id]['codelist'] + $groupcodelist;

							}
						}

						if(count($databgroupdata)>0){
							$data[$stdcode.'_sector_groups'] = '';
							foreach($databgroupdata as $fdata){
								$data[$stdcode.'_sector_groups'] .= $fdata['name'].': '. implode(', ',$fdata['codelist'])."\n";
							}
						}

						//$UserRoleBusinessGroup = UserRoleBusinessGroup::find()->where(['role_id' => $user['role_id'],'standard_id' => $std->standard_id])->all();
						/*
						if($std['standard_id']=='1')
						{
							$data[$std->code.'_expiry_date']=($std['witness_valid_until'] !='0000-00-00' && $std['witness_valid_until'] !=null && $std['witness_valid_until']!='1970-01-01')?date($date_format,strtotime($std['witness_valid_until'])):'';
						}
						if($std['standard_id']=='2')
						{
							$data[$std->code.'_expiry_date']=($std['witness_valid_until'] !='0000-00-00' && $std['witness_valid_until'] !=null && $std['witness_valid_until']!='1970-01-01')?date($date_format,strtotime($std['witness_valid_until'])):'';
						}
						if($std['standard_id']=='3')
						{
							$data[$std->code.'_expiry_date']=($std['witness_valid_until'] !='0000-00-00' && $std['witness_valid_until'] !=null && $std['witness_valid_until']!='1970-01-01')?date($date_format,strtotime($std['witness_valid_until'])):'';
						}
						if($std['standard_id']=='4')
						{
							$data[$std->code.'_expiry_date']=($std['witness_valid_until'] !='0000-00-00' && $std['witness_valid_until'] !=null && $std['witness_valid_until']!='1970-01-01')?date($date_format,strtotime($std['witness_valid_until'])):'';
						}
						*/
					}
				}
					
				if($post['type']=='submit')
				{
					$app_list[]=$data;
				}else{									
					$column = 'A';
					$sheet->setCellValue($column.$i, $data['user_id']);$column++;
					$sheet->setCellValue($column.$i, $data['oss']);$column++;
					$sheet->setCellValue($column.$i, $data['country']);$column++;
					$sheet->setCellValue($column.$i, $data['name']);$column++;
					$sheet->setCellValue($column.$i, $data['user_role']);$column++;
					foreach($stdarr as $master_stddata){
						$sheet->setCellValue($column.$i, $data[$master_stddata['lowercode'].'_expiry_date']);$column++;
					}

					foreach($stdarr as $master_stddata){
						$sheet->setCellValue($column.$i, $data[$master_stddata['lowercode'].'_sector_groups']);$column++;
					}

					
					/*
					$sheet->setCellValue('F'.$i, $data['ocs_expiry_date']);$column++;
					$sheet->setCellValue('G'.$i, $data['rcs_expiry_date']);$column++;
					$sheet->setCellValue('H'.$i, $data['grs_expiry_date']);$column++;
					$sheet->setCellValue('I'.$i, $data['gots_expiry_date']);$column++;
					*/
					/*
					$sheet->setCellValue('J'.$i, '');
					$sheet->setCellValue('K'.$i, '');
					$sheet->setCellValue('L'.$i, '');
					$sheet->setCellValue('M'.$i, '');
					$sheet->setCellValue('N'.$i, '');
					$sheet->setCellValue('O'.$i, '');
					$sheet->setCellValue('P'.$i, '');
					$sheet->setCellValue('Q'.$i, '');
					$sheet->setCellValue('R'.$i, '');
					$sheet->setCellValue('S'.$i, '');
					$sheet->setCellValue('T'.$i, '');
					$sheet->setCellValue('U'.$i, '');
					*/
					$i++;				
				}
			}
			
			
			if($post['type']=='submit')
			{
				$responsedata = array('status'=>1,'applications'=>$app_list);
				return $responsedata;
			}
			else
			{	
				
				$sheet->getStyle('A1:B'.($sheet->getHighestRow()+1))->applyFromArray($styleCenter);	    			
				$sheet->getStyle('C1:O'.($sheet->getHighestRow()+1))->applyFromArray($styleLeft);	
				$sheet->getStyle('A1:'.$lastcolumn.'1')->applyFromArray($this->styleWhite);	
				$sheet->getStyle('A1:'.$lastcolumn.'1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE'); 
				$sheet->getStyle('A1:'.$lastcolumn.($sheet->getHighestRow()+1))->applyFromArray($this->styleVCenter);	
				$sheet->getStyle('A1:'.$lastcolumn.($sheet->getHighestRow()+1))->getAlignment()->setWrapText(true); 
				$sheet->getStyle('A1:A1')->getAlignment()->setWrapText(true); 
				
				$writer = new Xlsx($spreadsheet);
				$filepath=Yii::$app->params['report_files'].'personnel_report'.date('YmdHis').'.xlsx';
				$writer->save($filepath);
				
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
				header('Access-Control-Max-Age: 1000');
				header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
				header('Access-Control-Expose-Headers: Content-Length,Content-Disposition,filename,Content-Type;');
				header('Access-Control-Allow-Headers: Content-Length,Content-Disposition,filename,Content-Type');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($filepath));
				flush(); 
				readfile($filepath);	
				die();			
			}
		}
		$responsedata = array('status'=>1,'applications'=>$app_list);
		return $responsedata;
		
		
	}

	
	public function actionAuditorteroles(){
		$Role = Role::find()->where(['status'=>0])->alias('t');
		$Role = $Role->join('left join', 'tbl_rule as rule',' rule.role_id=t.id ');
		$Role = $Role->andWhere('(t.resource_access=3 or rule.privilege in ("audit_execution"))');
		$Role = $Role->all();
		$rolelist = [];
		if(count($Role)>0){
			foreach($Role as $rolerow){
				$rolelist[] = ['id'=> $rolerow->id, 'name'=> $rolerow->role_name];
			}
		}
		return ['status'=>1, 'rolelist'=>$rolelist];
	}
    
}
