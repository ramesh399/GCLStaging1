<?php
namespace app\modules\report\controllers;

use Yii;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationChangeAddress;
use app\modules\master\models\User;
use app\modules\certificate\models\Certificate;
use app\modules\certificate\models\CertificateStatusReview;
use app\modules\audit\models\Audit;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * CustomerProgramReportController implements the CRUD actions for Product model.
 */
class CustomerProgramReportController extends \yii\rest\Controller
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
		if(!Yii::$app->userrole->hasRights(array('program_audit_report')))
		{
			return false;
		}
		
		$responsedata =array('status'=>0,'message'=>"No Data Found");
		$post = yii::$app->request->post();
		
		$usermodel = new User();
		$modelCertificate = new Certificate();
		
		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		
		$resource_access=$userData['resource_access'];		
		$AuditModel = new Audit();		
		/*
		$model = Audit::find()
		->where(['t.status'=>[$AuditModel->arrEnumStatus['finalized'],$AuditModel->arrEnumStatus['nc_overdue_failed']]])
		->alias('t');
		*/
		$poststandard = [];
		$model = Application::find()
		->where(['app.audit_type'=>[1,2]])
		//->andWhere(['app.overall_status'=>[9,10]])
		->alias('app');

		$model = $model->andWhere(['app.id'=> $post['app_id']]);
		$model = $model->join('inner join', 'tbl_audit as audit','audit.app_id=app.id and audit.status>=6');

		if(isset($post['audit_type']) && $post['audit_type'] !='')
		{
			$model = $model->andWhere(['audit.audit_type'=> $post['audit_type']]);
		}
		//->where(['t.status'=>[$AuditModel->arrEnumStatus['finalized'],$AuditModel->arrEnumStatus['nc_overdue_failed']]])
		
		//$model = $model->join('inner join', 'tbl_audit_plan as audit_plan','audit_plan.audit_id=t.id');
		//$model = $model->join('inner join', 'tbl_audit_plan_unit as audit_plan_unit','audit_plan_unit.audit_plan_id=audit_plan.id');
		//$model = $model->join('inner join', 'tbl_audit_plan_unit_standard as plan_unit_standard','plan_unit_standard.audit_plan_unit_id=audit_plan_unit.id');

		//$model = $model->join('inner join', 'tbl_application as app','t.app_id=app.id');
		//$model = $model->join('inner join', 'tbl_application_change_address as appaddress','appaddress.id=app.address_id');
		//$model = $model->join('inner join', 'tbl_application_standard as app_standard','app_standard.app_id =app.id ');
		/*$poststandard = [];
		if(isset($post['standard_id']) && is_array($post['standard_id']) && count($post['standard_id'])>0)
		{
			$poststandard = $post['standard_id'];
			$model = $model->andWhere(['plan_unit_standard.standard_id'=> $post['standard_id']]);
		}
		
		if(isset($post['oss_id']) && is_array($post['oss_id']) && count($post['oss_id'])>0)
		{
			$model = $model->andWhere(['app.franchise_id'=> $post['oss_id']]);				
		}
		else
		{
			if($is_headquarters != 1)
			{
				$model = $model->andWhere(['app.franchise_id'=> $franchiseid]);	
			}
		}
		
	
		if(isset($post['from_date']) && $post['from_date'] !='' && isset($post['to_date']) && $post['to_date'] !='')
		{
			$from_date = strtotime($post['from_date']);
			$to_date = strtotime($post['to_date']);
			$model = $model->andWhere(['>=','t.created_at', $from_date]);				
			$model = $model->andWhere(['<=','t.created_at', $to_date]);
		}	
		*/	
		$model = $model->groupBy(['app.id']);
		
		$app_list=array();
		$model = $model->all();		
		//echo count($model);
		if(count($model)>0)
		{
			if($post['type']!='submit')
			{
				$arrHeaderLabel=array('S.No','Customer Number','Company Name','Country','OSS','Standards','Audit Type','Unit Name','Unit Address','Auditor(s)','Audit Date(s)');
				$styleWhite = array('font'  => array('name'  => 'Arial','color' => array('rgb' => 'FFFFFF'),'bold'  => true,'size'  => 10,));
				$styleBgColor = array('fill' => array('type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'color' => array('rgb' => '578CDE')));
				$styleCenter = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,));
				$styleLeft = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT));
				$styleVCenter = array('alignment' => array('vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER));
				$styleHigh = array('font'  => array('color' => array('rgb' => 'FF0000'),'bold'  => true,));
				$styleMedium = array('font'  => array('color' => array('rgb' => 'F79647'),'bold'  => true,));
				$styleLow = array('font'  => array('color' => array('rgb' => '00B050'),'bold'  => true,));
				
				$spreadsheet = new Spreadsheet();				
				$sheet = $spreadsheet->getActiveSheet()->setTitle("Customer Wise Program Report");
				
				$column = 'A';
				foreach($arrHeaderLabel as $headerKey=>$headerLabel)
				{
					$sheet->setCellValue($column.'1', $headerLabel);
					$defaultWidth=25;
					if($column=='A'){
						$defaultWidth=10;
					}elseif($column=='B' || $column=='G' || $column=='E' || $column=='K'){
						$defaultWidth=15;
					}elseif($column=='C'){
						$defaultWidth=40;
					}elseif($column=='H' || $column=='I' || $column=='J'){
						$defaultWidth=45;
					}
					$sheet->getColumnDimension($column)->setWidth($defaultWidth);
					$column++;
				}				
				
							
				
				$i=2;
				$sno=1;
			}	
			//$date_format = Yii::$app->globalfuns->getSettings('date_format');
			foreach($model as $application)
			{

				//'S.No','Customer Number','Company Name','OSS','Standards','Type','Unit Name','Audit Date(s)','Auditor(s)'
				
				$data=array();				
				//$application = $offer->audit->application;
				$childapps = $application->childapps;
				$data['company_name']=($application)?$application->companyname:'';
				$data['audit_type']= ''; //$audit->audittypeArr[$audit->audit_type];
				$data['email_address']=($application)?$application->emailaddress:'';
				$data['customer_number']=($application->customer)?$application->customer->customer_number:'';
				$data['address']=($application)?$application->address:'';
				$data['zipcode']=($application)?$application->zipcode:'';
				$data['city']=($application)?$application->city:'';
				$data['oss']=($application)?$application->franchise->usercompanyinfo->osp_details:'';
				$data['country']=($application)?$application->countryname:'';
				$data['state']=($application)?$application->statename:'';

				//$data['certificate_generated_date']=$offer->status ==$modelCertificate->arrEnumStatus['declined']?'NA':($offer->certificate_generated_date!='')?date($date_format,strtotime($offer->certificate_generated_date)):'';
				//$data['certificate_valid_until']=$offer->status ==$modelCertificate->arrEnumStatus['declined']?'NA':($offer->certificate_valid_until!='')?date($date_format,strtotime($offer->certificate_valid_until)):'';
				
				//$data['certificate_status_name']=$offer->arrCertificateStatus[$offer->certificate_status];
				//$data['risk_category']=$offer->risk_category?$offer->riskcategory->name:'';
				$data['contact_person']=($application)?$application->customer->first_name.' '.$application->customer->last_name:'';
				$data['contact_number']=($application)?$application->customer->telephone:'';
				$data['created_at']=date($date_format,$application->created_at);
				//$data['certificate_standard']=$offer->standard?$offer->standard->code:'';
				//$data['certified_by']=$offer->reviewer && $offer->reviewer->user?$offer->reviewer->user->first_name.' '.$offer->reviewer->user->last_name:'';

				//$data['application_standard'] = $offer->standard->code;
				
				$application_standard=$application->applicationstandard;
				$data['application_standard']= '';
				if(count($application_standard)>0)
				{
					$standard_names='';
					$app_standard=[];
					foreach($application_standard as $std)
					{
						$app_standard[]=$std->standard->code;
					}
					$standard_names=implode(', ',$app_standard);
					$data['application_standard']=$standard_names;
				}
				
				
				
				//$data['application_standard'] = implode(', ', array_unique($data['application_standard']));
					
				if($post['type']=='submit')
				{
					
					$app_list[]=$data;
				}else{		
					//$applicationproduct = $application->applicationproduct;
					//'S.No','Customer Number','Company Name','OSS','Standards','Type','Unit Name','Audit Date(s)','Auditor(s)'

					$poststandardIds = [];
					if(isset($post['standard_id']) && is_array($post['standard_id'])){
						$poststandardIds = $post['standard_id'];
					}
					 
					//$applicationProductdata = Yii::$app->globalfuns->getAppProducts($applicationproduct,$poststandardIds);
					//$data['product_excel_list'] = $applicationProductdata['product_excel_list'];

					$column = 'A';
					$sheet->setCellValue($column.$i, $sno);$column++;					
					$sheet->setCellValue($column.$i, $data['customer_number']);$column++;    									
					$sheet->setCellValue($column.$i, $data['company_name']);$column++;
					$sheet->setCellValue($column.$i, $data['country']);$column++;
					$sheet->setCellValue($column.$i, $data['oss']);$column++;
					$sheet->setCellValue($column.$i, $data['application_standard']);$column++;
					//$sheet->setCellValue($column.$i, $data['audit_type']);$column++;

					//$sheet->setCellValue($column.$i, $data['address']);$column++;
					//$sheet->setCellValue($column.$i, ' '.$data['zipcode']);$column++;
					//$sheet->setCellValue($column.$i, $data['contact_person']);$column++;
					//$sheet->setCellValue($column.$i, ' '.$data['contact_number']);$column++;
					//$sheet->setCellValue($column.$i, $data['email_address']);$column++;
					$imax=$i;
					$sc_name_address=$application->normalaudit->auditplan->auditplanunit;
					if(($post['audit_type']=='1' || $post['audit_type']=='') && count($sc_name_address)>0)
					{
						$sub_contractor= $this->getunitdetails($sc_name_address);

						$data['audit_type'] = $application->normalaudit->audittypeArr[$application->normalaudit->audit_type];
						$sheet->setCellValue($column.$i, $data['audit_type']);$column++;

						$data['sub_contractor_details']=$sub_contractor;
						
						if(isset($data['sub_contractor_details']) && !empty($data['sub_contractor_details']))
						{
							foreach($data['sub_contractor_details'] as $val)
							{
								$column = 'H';
								$sheet->setCellValue($column.$imax, $val['name']);$column++;
								$sheet->setCellValue($column.$imax, $val['address']);$column++;
								$sheet->setCellValue($column.$imax, implode(", ",$val['auditors']));$column++;
								$sheet->setCellValue($column.$imax, implode("\n",$val['dates']));$column++;
								//$sheet->setCellValue("K".$imax, $val['process_name']);
								//$sheet->setCellValue("L".$imax, $val['unit_country']);
								//$sheet->setCellValue("M".$imax, $val['standard_names']);
								$imax++;
							}
							$column="N";
						}
					 
						
						foreach($childapps as $childappslist){

							$audit = $childappslist->normalaudit;
							if($audit && $audit->auditplan){
								
							}else{
								continue;
							}

							$data['audit_type'] = $audit->audittypeArr[$audit->audit_type];
							$sheet->setCellValue('G'.$imax, $data['audit_type']);

							$data['sub_contractor_details']=[];
							$sc_name_address=$audit->auditplan->auditplanunit;
							if(count($sc_name_address)>0)
							{
								$sub_contractor= $this->getunitdetails($sc_name_address);
								
								$data['sub_contractor_details']=$sub_contractor;
								
								if(isset($data['sub_contractor_details']) && !empty($data['sub_contractor_details']))
								{
									foreach($data['sub_contractor_details'] as $val)
									{
										$column = 'H';
										$sheet->setCellValue($column.$imax, $val['name']);$column++;
										$sheet->setCellValue($column.$imax, $val['address']);$column++;
										$sheet->setCellValue($column.$imax, implode(", ",$val['auditors']));$column++;
										$sheet->setCellValue($column.$imax, implode("\n",$val['dates']));$column++;
										//$sheet->setCellValue("K".$imax, $val['process_name']);
										//$sheet->setCellValue("L".$imax, $val['unit_country']);
										//$sheet->setCellValue("M".$imax, $val['standard_names']);
										$imax++;
									}
									$column="N";
								}
							}
						}

						
					}
					if($post['audit_type']=='2' || $post['audit_type']==''){
						$unannoucedaudit=$application->unannoucedaudit;
						foreach($unannoucedaudit as $audit){
							if($audit && $audit->auditplan){
								
							}else{
								continue;
							}

							$data['audit_type'] = $audit->audittypeArr[$audit->audit_type];
							$sheet->setCellValue('G'.$imax, $data['audit_type']);

							$data['sub_contractor_details']=[];
							$sc_name_address=$audit->auditplan->auditplanunit;
							if(count($sc_name_address)>0)
							{
								$sub_contractor= $this->getunitdetails($sc_name_address);
								
								$data['sub_contractor_details']=$sub_contractor;
								
								if(isset($data['sub_contractor_details']) && !empty($data['sub_contractor_details']))
								{
									foreach($data['sub_contractor_details'] as $val)
									{
										$column = 'H';
										$sheet->setCellValue($column.$imax, $val['name']);$column++;
										$sheet->setCellValue($column.$imax, $val['address']);$column++;
										$sheet->setCellValue($column.$imax, implode(", ",$val['auditors']));$column++;
										$sheet->setCellValue($column.$imax, implode("\n",$val['dates']));$column++;
										//$sheet->setCellValue("K".$imax, $val['process_name']);
										//$sheet->setCellValue("L".$imax, $val['unit_country']);
										//$sheet->setCellValue("M".$imax, $val['standard_names']);
										$imax++;
									}
									$column="N";
								}
							}
						}

						
					}
					
					
					/*
					$riskCategoryStyle='';
					if($offer->risk_category==1 || $offer->risk_category==2){
						$riskCategoryStyle=$styleHigh;
					}elseif($offer->risk_category==3){
						$riskCategoryStyle=$styleMedium;
					}else{
						$riskCategoryStyle=$styleLow;
					}					
					$sheet->getStyle($column.$i)->applyFromArray($riskCategoryStyle);$column++;
					*/
					  
					$i=$imax;					
					$sno++;					
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
				 $sheet->getStyle('C1:K'.($sheet->getHighestRow()+1))->applyFromArray($styleLeft);	
				 $sheet->getStyle('A1:K1')->applyFromArray($this->styleWhite);					
				 $sheet->getStyle('A1:K1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE');
				 $sheet->getStyle('A1:K'.($sheet->getHighestRow()+1))->applyFromArray($this->styleVCenter);	
				 $sheet->getStyle('A1:K'.($sheet->getHighestRow()+1))->getAlignment()->setWrapText(true); 		 
				//$sheet->getStyle('R1:R'.$sno)->applyFromArray($styleCenter);					
				//$sheet->getStyle('K1:K'.$sno)->applyFromArray($styleCenter);	 				
				//$sheet->getStyle('L1:L'.$sno)->applyFromArray($styleCenter);	
				//$sheet->getStyle('M1:M'.$sno)->applyFromArray($styleCenter);
				
				//$spreadsheet->getSheet(0);	
				$sheet->getStyle('A1:A1')->getAlignment()->setWrapText(true);			
				
				$writer = new Xlsx($spreadsheet);
				$filepath=Yii::$app->params['report_files'].'program_audit_report'.date('YmdHis').'.xlsx';
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
	private function getunitdetails($sc_name_address){
		$poststandard = [];
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		foreach($sc_name_address as $planunit)
		{
			$unitdata=[];
			$unit = $planunit->unitdata;

			

			if(count($poststandard)>0){
				$unitstandard=$planunit->unitstandard;
				$checkexistingstd = [];
				if(count($unitstandard)>0)
				{
					foreach($unitstandard as $unitstds)
					{
						$checkexistingstd[] = $unitstds->standard_id;
					}
				}
				$commonstandards=array_intersect($poststandard,$checkexistingstd);
				if(count($commonstandards)<=0){
					continue;
				}
			}
			$unitdata['auditors'] = [];
			$unitdata['dates'] = [];
			$unitauditors = $planunit->unitauditors;
			$unitdate = $planunit->auditplanunitdate;
			if(count($unitauditors)>0){
				foreach($unitauditors as $unitauditorobj)
				{
					$unitdata['auditors'][] = $unitauditorobj->user->first_name.' '.$unitauditorobj->user->last_name;
				}
				$unitdata['auditors'] = array_unique($unitdata['auditors']);
			}

			if(count($unitdate)>0){
				foreach($unitdate as $unitdateobj)
				{
					$unitdata['dates'][] = date($date_format,strtotime($unitdateobj->date));
				}
				$unitdata['dates'] = array_unique($unitdata['dates']);
			}
			$unitdata['name']=$unit['name'];
			$unitdata['address']=$unit['address'];
			$unitdata['unit_country']=$unit->country_id?$unit->country->name:'';
			
			
			$unitdata['standard_names']='';
			$unitstandard=$planunit->unitstandard;
			if(count($unitstandard)>0)
			{
				$sub_contractor_standards=[];
				$unitstd_names='';
				foreach($unitstandard as $unitstds)
				{
					if(count($poststandard)>0)
					{
						if(!in_array($unitstds->standard_id,$poststandard)){
							continue;
						}
					}
					$data['application_standard'][$unitstds->standard_id] = $unitstds->standard->code;
					$unitstd_names.=$unitstds->standard->code.",";
				}
				$unitstd_names=substr($unitstd_names, 0, -1);
				$unitdata['standard_names']=$unitstd_names;
			}
			
			$sub_contractor[]=$unitdata;

		}
		return $sub_contractor;
	}


	public function actionGetAppdata()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userrole = Yii::$app->userrole;
		$userid=$userrole->user_id;				
		$user_type=$userrole->user_type;
		$role=$userrole->role;
		$rules=$userrole->rules;
		$franchiseid=$userrole->franchiseid;		
		$resource_access=$userrole->resource_access;
		$is_headquarters =$userrole->is_headquarters;
		$role_chkid=$userrole->role_chkid;	
		
		$arrPaymentStatus=array();
		
		$Audit = new Audit();
		
		
		$apparr = Yii::$app->globalfuns->getCertifiedAppList();
		$responsedata=array('status'=>1,'appdata'=>$apparr,'audittypedata'=>$Audit->audittypeArr);
		return $this->asJson($responsedata);
	}
}
