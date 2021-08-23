<?php
namespace app\modules\report\controllers;

use Yii;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationChangeAddress;
use app\modules\master\models\User;
use app\modules\certificate\models\Certificate;
use app\modules\certificate\models\CertificateStatusReview;
use app\modules\audit\models\AuditPlanUnit;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * ClientReportController implements the CRUD actions for Product model.
 */
class ClientReportController extends \yii\rest\Controller
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
		if(!Yii::$app->userrole->hasRights(array('customer_report')))
		{
			return false;
		}
		
		$responsedata =array('status'=>0,'message'=>"No Data Found");
		$post = yii::$app->request->post();
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();


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
		$model = ApplicationStandard::find()
		//->where('t.certificate_status=0')
		//->where(['app_standard.type'=>[1,2]])
		->alias('app_standard');

		//$model = $model->join('inner join', 'tbl_audit as audit','audit.id =t.audit_id');		
		$model = $model->join('inner join', 'tbl_application as app','app_standard.app_id=app.id ');
		$model = $model->join('inner join', 'tbl_application_change_address as appaddress','appaddress.id=app.address_id ');
		$model = $model->join('inner join', 'tbl_certificate as t','t.standard_id = app_standard.standard_id and t.parent_app_id =app.id and t.type in(1,2) and t.status>=2 ');
		$poststandard = [];
		if(isset($post['standard_id']) && is_array($post['standard_id']) && count($post['standard_id'])>0)
		{
			$poststandard = $post['standard_id'];
			$model = $model->andWhere(['app_standard.standard_id'=> $post['standard_id']]);
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
			$from_date = date('Y-m-d', strtotime($post['from_date']));
			$to_date = date('Y-m-d', strtotime($post['to_date']));
			$model = $model->andWhere(['>=','t.certificate_generated_date', $from_date]);				
			$model = $model->andWhere(['<=','t.certificate_generated_date', $to_date]);
		}		
		$model = $model->groupBy(['app_standard.id']);
		
		$app_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			if($post['type']!='submit')
			{
				$arrHeaderLabel=array('S.No','Client ID','Organisation name','Address','ZIP Code','Contact Person','Contact Number','Mail ID','Scope Holder Process','Facility / Subcontractor Name & Address','Facility / Subcontractor Process','Country','Standard','Date of Certification','Date of Most Recent Certification','Date of Expiry','Risk Level','OSS','Certification Status','Product Certified','Audit Done By','Certified By');
				$styleWhite = array('font'  => array('name'  => 'Arial','color' => array('rgb' => 'FFFFFF'),'bold'  => true,'size'  => 10,));
				$styleBgColor = array('fill' => array('type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'color' => array('rgb' => '578CDE')));
				$styleCenter = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,));
				$styleLeft = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT));
				$styleVCenter = array('alignment' => array('vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER));
				$styleHigh = array('font'  => array('color' => array('rgb' => 'FF0000'),'bold'  => true,));
				$styleMedium = array('font'  => array('color' => array('rgb' => 'F79647'),'bold'  => true,));
				$styleLow = array('font'  => array('color' => array('rgb' => '00B050'),'bold'  => true,));
				
				$spreadsheet = new Spreadsheet();				
				$sheet = $spreadsheet->getActiveSheet()->setTitle("Client Report");
				
				$column = 'A';
				foreach($arrHeaderLabel as $headerKey=>$headerLabel)
				{
					$sheet->setCellValue($column.'1', $headerLabel);
					$defaultWidth=25;
					if($column=='A'){
						$defaultWidth=10;
					}elseif($column=='B' || $column=='G' || $column=='P'){
						$defaultWidth=15;
					}elseif($column=='D' || $column=='C'){
						$defaultWidth=40;
					}elseif($column=='I' || $column=='K'){
						$defaultWidth=60;
					}elseif($column=='H'){
						$defaultWidth=35;
					}elseif($column=='T'){
						$defaultWidth=80;
					}
					$sheet->getColumnDimension($column)->setWidth($defaultWidth);
					$column++;
				}				
				
							
				
				$i=2;
				$sno=1;
			}	
			
			foreach($model as $offer)
			{
				$data=array();				
				//$application = $offer->audit->application;
				$application = $offer->application;
				$certificate = $offer->certificateforreport;

				$latestcertificate = $offer->latestcertificateforreport;
				$audit = $certificate->audit;
				
				//$audit = $offer->audit;
				$data['company_name']=($application)?$application->companyname:'';
				$data['email_address']=($application)?$application->emailaddress:'';
				$data['customer_number']=($application)?$application->customer->customer_number:'';
				$data['address']=($application)?$application->address:'';
				$data['zipcode']=($application)?$application->zipcode:'';
				$data['city']=($application)?$application->city:'';
				//$data['oss']=($application)?$application->franchise->usercompanyinfo->osp_details:'';
				
				$data['oss']='';
				if($application)
				{
					$usercompanyinfoObj = $application->franchise->usercompanyinfo;
					$data['oss']=$usercompanyinfoObj ? 'OSS '.$usercompanyinfoObj->osp_number.' - '.$usercompanyinfoObj->companycountry->name:'';
				}
				
				$data['country']=($application)?$application->countryname:'';
				$data['state']=($application)?$application->statename:'';

				$data['certificate_generated_date']=$certificate->status ==$modelCertificate->arrEnumStatus['declined']?'NA':($certificate->certificate_generated_date!='')?date($date_format,strtotime($certificate->certificate_generated_date)):$certificate->id;

				$data['recent_certificate_generated_date']=$latestcertificate->status ==$latestcertificate->arrEnumStatus['declined']?'NA':($latestcertificate->certificate_generated_date!='')?date($date_format,strtotime($latestcertificate->certificate_generated_date)):$latestcertificate->id;

				$data['certificate_valid_until']=$certificate->status ==$modelCertificate->arrEnumStatus['declined']?'NA':($certificate->certificate_valid_until!='')?date($date_format,strtotime($certificate->certificate_valid_until)):$certificate->id;
				
				$data['certificate_status_name']=$certificate->arrCertificateStatus[$certificate->certificate_status];
				$data['risk_category']=$certificate->risk_category?$certificate->riskcategory->name:'';
				$data['contact_person']=($application)?$application->customer->first_name.' '.$application->customer->last_name:'';
				$data['contact_number']=($application)?$application->customer->telephone:'';
				$data['created_at']=date($date_format,$certificate->created_at);
				$data['certificate_standard']=$certificate->standard?$certificate->standard->code:'';
				$data['certified_by']=$certificate->reviewer && $certificate->reviewer->user?$certificate->reviewer->user->first_name.' '.$certificate->reviewer->user->last_name:'';

				$data['application_standard'] = $certificate->standard->code;
				$auditors = [];
				if($audit->auditplan && count($audit->auditplan->auditplanunit)>0){
					$auditplanunit = $audit->auditplan->auditplanunit;
					if(count($auditplanunit)>0){
						foreach($auditplanunit as $auditplanunitobj){
							$unitauditors = $auditplanunitobj->unitauditors;
							if(count($unitauditors)>0){
								foreach($unitauditors as $unitauditorobj){
									$auditors[$unitauditorobj->user->id] = $unitauditorobj->user->first_name." ".$unitauditorobj->user->last_name;
								}
							}
						}
					}
					
				}


				/*
				$application_standard=$application->applicationstandard;
				if(count($application_standard)>0)
				{
					$standard_names='';
					$app_standard=[];
					foreach($application_standard as $std)
					{
						$app_standard[]=$std->standard->code;
					}
					$standard_names=implode(',',$app_standard);
					$data['application_standard']=$standard_names;
				}
				*/
				$data['sub_contractor_details']=[];
				 
				$sc_name_address=$application->applicationunitlist;
				
				
				if(count($sc_name_address)>0)
				{
					$sub_contractor=[];
					foreach($sc_name_address as $unit)
					{
						$unitdata=[];

						//if(count($poststandard)>0){
							$unitstandard=$unit->unitappstandardall;
							$checkexistingstd = [];
							if(count($unitstandard)>0)
							{
								foreach($unitstandard as $unitstds)
								{
									$checkexistingstd[] = $unitstds->standard_id;
								}
							}
							//$commonstandards=array_intersect($poststandard,$checkexistingstd);
							if(!in_array($offer->standard_id,$checkexistingstd)){
								continue;
							}
						//}
						$unitdata['unit_id']=$unit->id;
						$unitdata['name']=$unit['name'].", ".$unit['address'];
						$unitdata['unit_country']=$unit->country_id?$unit->country->name:'';

						$unitprocs=$unit->unitprocessall;
						if(count($unitprocs)>0)
						{
							$sub_contractor_process=[];
							$unitprocess_names='';
							foreach($unitprocs as $unitvals)
							{
								//if(count($poststandard)>0)
								//{
									if($unitvals->standard_id!=$offer->standard_id){
										continue;
									}
								//}
								$unitprocess_names.=$unitvals['process_name'].",";
							}
							$unitprocess_names=substr($unitprocess_names, 0, -1);
							$unitdata['process_name']=$unitprocess_names;
						}
						//$sub_contractor[]=$unitdata;

						$unitdata['standard_names']='';
						$unitstandard=$unit->unitappstandardall;
						if(count($unitstandard)>0)
						{
							$sub_contractor_standards=[];
							$unitstd_names='';
							foreach($unitstandard as $unitstds)
							{
								//if(count($poststandard)>0)
								//{
									if($unitstds->standard_id != $offer->standard_id){
										continue;
									}
								//}
								$unitstd_names.=$unitstds->standard->code.",";
							}
							$unitstd_names=substr($unitstd_names, 0, -1);
							$unitdata['standard_names']=$unitstd_names;
						}
						$sub_contractor[]=$unitdata;

					}
					$data['sub_contractor_details']=$sub_contractor;	
				}

				$data['scope_holder_process'] = '';
				$sh_process=$application && $application->applicationscopeholder?$application->applicationscopeholder->unitprocessall:[];
				if(count($sh_process)>0)
				{
					$process_namesarr=[];
					foreach($sh_process as $procs)
					{
						//if(count($poststandard)>0)
						//{
							if($procs->standard_id != $offer->standard_id){
								continue;
							}
						//}
						$process_namesarr[] = $procs['process_name'];
					}
					$process_names= implode(', ',$process_namesarr);
					$data['scope_holder_process']=$process_names;
				}
					
				if($post['type']=='submit')
				{
					$app_list[]=$data;
				}else{	
					
					$applicationproduct = $application->applicationproduct;
					$poststandardIds = [$offer->standard_id];
					//if(isset($post['standard_id']) && is_array($post['standard_id'])){
					//	$poststandardIds = $post['standard_id'];
					//}
					$applicationProductdata = Yii::$app->globalfuns->getAppProducts($applicationproduct,$poststandardIds);
					$data['product_excel_list'] = isset($applicationProductdata['product_excel_list'])?$applicationProductdata['product_excel_list']:[];

					$column = 'A';
					$sheet->setCellValue($column.$i, $sno);$column++;					
					$sheet->setCellValue($column.$i, $data['customer_number']);$column++;    									
					$sheet->setCellValue($column.$i, $data['company_name']);$column++;
					$sheet->setCellValue($column.$i, $data['address']);$column++;
					$sheet->setCellValue($column.$i, ' '.$data['zipcode']);$column++;
					$sheet->setCellValue($column.$i, $data['contact_person']);$column++;
					$sheet->setCellValue($column.$i, ' '.$data['contact_number']);$column++;
					$sheet->setCellValue($column.$i, $data['email_address']);$column++;
					$sheet->setCellValue($column.$i, $data['scope_holder_process']);$column++;
					
					$riskCategoryStyle='';
					if($certificate->risk_category==1 || $certificate->risk_category==2){
						$riskCategoryStyle=$styleHigh;
					}elseif($certificate->risk_category==3){
						$riskCategoryStyle=$styleMedium;
					}else{
						$riskCategoryStyle=$styleLow;
					}	
					$subcnt = count($data['sub_contractor_details']);
					if($subcnt > 1){
						//echo $i.'=='.$imax.'++';
						$startColumn = 'A';
						for($isub=0;$isub<=8;$isub++){
							//echo $startColumn.'==';
							$sheet->mergeCells($startColumn.$i.":".$startColumn.($i + ($subcnt-1)));
							$startColumn++;
						}

						$startColumn = 'M';
						for($isub=0;$isub<=9;$isub++){
							//echo $startColumn.'==';
							$sheet->mergeCells($startColumn.$i.":".$startColumn.($i + ($subcnt-1)));
							$startColumn++;
						}
						
						//$sheet->mergeCells("B".$i.":B".($i + ($subcnt-1) ));
						//$sheet->mergeCells("M".$i.":V".$imax);
					}


					$imax=$i;
					if(isset($data['sub_contractor_details']) && !empty($data['sub_contractor_details']))
					{
						foreach($data['sub_contractor_details'] as $val)
						{
							/*
							$auditors = [];
							$AuditPlanUnit = AuditPlanUnit::find()->where(['unit_id'=> $val['unit_id']])->alias('t');
							$AuditPlanUnit = $AuditPlanUnit->join('inner join', 'tbl_audit_plan_unit_standard as unit_std','unit_std.audit_plan_unit_id=t.id and unit_std.standard_id='.$offer->standard_id.'');
							$AuditPlanUnit = $AuditPlanUnit->orderBy(['id' => SORT_ASC])->one();
							if($AuditPlanUnit !== null){
								$unitauditors = $AuditPlanUnit->unitauditors;
								if(count($unitauditors)>0){
									foreach($unitauditors as $unitauditorobj){
										$auditors[] = $unitauditorobj->user->first_name." ".$unitauditorobj->user->last_name;
									}
								}
							}
							*/

							$sheet->setCellValue("J".$imax, $val['name']);						
							$sheet->setCellValue("K".$imax, $val['process_name']);
							$sheet->setCellValue("L".$imax, $val['unit_country']);
							
							//$sheet->setCellValue("M".$imax, $data['application_standard']);
							//$sheet->setCellValue("N".$imax, $data['certificate_generated_date']);
							//$sheet->setCellValue("O".$imax, $data['certificate_generated_date']);
							//$sheet->setCellValue("P".$imax, $data['certificate_valid_until']);
							//$sheet->setCellValue("Q".$imax, $data['risk_category']);
							//$sheet->setCellValue("R".$imax, $data['oss']);
							//$sheet->setCellValue("S".$imax, $data['certificate_status_name']);
							//$sheet->setCellValue("V".$imax, $data['certified_by']);


							//$sheet->setCellValue("U".$imax, implode(', ', $auditors));
							

							$sheet->getStyle('Q'.$imax)->applyFromArray($riskCategoryStyle);

							
							$imax++;

							
						}
						
						
						//$column="N";
					}
					else
					{
						$sheet->setCellValue("J".$imax, '');						
						$sheet->setCellValue("K".$imax, '');
						$sheet->setCellValue("L".$imax, '');
						/*
						$sheet->setCellValue("M".$imax, $data['application_standard']);

						$sheet->setCellValue("N".$imax, $data['certificate_generated_date']);
						$sheet->setCellValue("O".$imax, $data['certificate_generated_date']);
						$sheet->setCellValue("P".$imax, $data['certificate_valid_until']);
						$sheet->setCellValue("Q".$imax, $data['risk_category']);
						$sheet->setCellValue("R".$imax, $data['oss']);
						$sheet->setCellValue("S".$imax, $data['certificate_status_name']);
						$sheet->setCellValue("U".$imax, '');
						$sheet->setCellValue("V".$imax, $data['certified_by']);
						*/

						$sheet->getStyle('Q'.$imax)->applyFromArray($riskCategoryStyle);
						////$sheet->setCellValue($column.$imax, '');$column++;
						//$sheet->setCellValue($column.$imax, '');$column++;
						//$sheet->setCellValue($column.$imax, '');$column++;
						//$sheet->setCellValue($column.$imax, '');$column++;
						$imax++;
					}
					
					$sheet->setCellValue("M".$i, $data['application_standard']);
					$sheet->setCellValue("N".$i, $data['certificate_generated_date']);
					$sheet->setCellValue("O".$i, $data['recent_certificate_generated_date']);
					$sheet->setCellValue("P".$i, $data['certificate_valid_until']);
					$sheet->setCellValue("Q".$i, $data['risk_category']);
					$sheet->setCellValue("R".$i, $data['oss']);
					$sheet->setCellValue("S".$i, $data['certificate_status_name']);
					$sheet->setCellValue('T'.$i, implode(', ', $data['product_excel_list']));
					$sheet->setCellValue("U".$i, implode(', ', $auditors));
					$sheet->setCellValue("V".$i, $data['certified_by']);
					

					
					// $sheet->setCellValue($column.$i, $data['country']);$column++;
					// $sheet->setCellValue($column.$i, $data['application_standard']);$column++;
					//$sheet->setCellValue($column.$i, $data['certificate_generated_date']);$column++;
					//$sheet->setCellValue($column.$i, '');$column++;
					//$sheet->setCellValue($column.$i, $data['certificate_valid_until']);$column++;
					//$sheet->setCellValue($column.$i, $data['risk_category']);

									
					
					
					//$sheet->setCellValue($column.$i, $data['oss']);$column++;
					//$sheet->setCellValue($column.$i, $data['certificate_status_name']);$column++;
					
										
					
					
					//$column++;					
					//$sheet->setCellValue($column.$i, '');$column++;	
					//$sheet->setCellValue($column.$i, $data['certified_by']);$column++;
					
					
					
					
					//$i=$standardCnt;
					//$i++;
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
				 $sheet->getStyle('A1:A'.($sheet->getHighestRow()+1))->applyFromArray($styleCenter);	    			
				 $sheet->getStyle('B1:B'.($sheet->getHighestRow()+1))->applyFromArray($styleCenter);
				 $sheet->getStyle('C1:V'.($sheet->getHighestRow()+1))->applyFromArray($styleLeft);	
				 $sheet->getStyle('A1:V1')->applyFromArray($this->styleWhite);					
				 $sheet->getStyle('A1:V1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE');
				 $sheet->getStyle('A1:V'.($sheet->getHighestRow()+1))->applyFromArray($this->styleVCenter);	
				 $sheet->getStyle('A1:V'.($sheet->getHighestRow()+1))->getAlignment()->setWrapText(true); 		 
				
				//$spreadsheet->getSheet(0);				
				$sheet->getStyle('A1:A1')->getAlignment()->setWrapText(true); 
				
				$writer = new Xlsx($spreadsheet);
				$filepath=Yii::$app->params['report_files'].'client_report'.date('YmdHis').'.xlsx';
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

	/*
	public function actionIndexDec282020()
    {
		if(!Yii::$app->userrole->hasRights(array('customer_report')))
		{
			return false;
		}
		
		$responsedata =array('status'=>0,'message'=>"No Data Found");
		$post = yii::$app->request->post();
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();


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
		$model = Certificate::find()
		//->where('t.certificate_status=0')
		->where(['t.type'=>[1,2]])
		->alias('t');

		//$model = $model->join('inner join', 'tbl_audit as audit','audit.id =t.audit_id');		
		$model = $model->join('inner join', 'tbl_application as app','t.parent_app_id=app.id');
		$model = $model->join('inner join', 'tbl_application_change_address as appaddress','appaddress.id=app.address_id');
		$model = $model->join('inner join', 'tbl_application_standard as app_standard','app_standard.app_id =app.id ');
		$poststandard = [];
		if(isset($post['standard_id']) && is_array($post['standard_id']) && count($post['standard_id'])>0)
		{
			$poststandard = $post['standard_id'];
			$model = $model->andWhere(['app_standard.standard_id'=> $post['standard_id']]);
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
			$from_date = date('Y-m-d', strtotime($post['from_date']));
			$to_date = date('Y-m-d', strtotime($post['to_date']));
			$model = $model->andWhere(['>=','t.certificate_generated_date', $from_date]);				
			$model = $model->andWhere(['<=','t.certificate_generated_date', $to_date]);
		}		
		$model = $model->groupBy(['app.id']);
		
		$app_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			if($post['type']!='submit')
			{
				$arrHeaderLabel=array('S.No','Client ID','Organisation name','Address','ZIP Code','Contact Person','Contact Number','Mail ID','Scope Holder Process','Subcontractor Name & Address','Subcontractor Process','Country','Standard','Date of Certification','Date of Most Recent Certification','Date of Expiry','Risk Level','OSS','Certification Status','Product Certified','Audit Done By','Certified By');
				$styleWhite = array('font'  => array('name'  => 'Arial','color' => array('rgb' => 'FFFFFF'),'bold'  => true,'size'  => 10,));
				$styleBgColor = array('fill' => array('type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'color' => array('rgb' => '578CDE')));
				$styleCenter = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,));
				$styleVCenter = array('alignment' => array('vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER));
				$styleHigh = array('font'  => array('color' => array('rgb' => 'FF0000'),'bold'  => true,));
				$styleMedium = array('font'  => array('color' => array('rgb' => 'F79647'),'bold'  => true,));
				$styleLow = array('font'  => array('color' => array('rgb' => '00B050'),'bold'  => true,));
				
				$spreadsheet = new Spreadsheet();				
				$sheet = $spreadsheet->getActiveSheet()->setTitle("Client Report");
				
				$column = 'A';
				foreach($arrHeaderLabel as $headerKey=>$headerLabel)
				{
					$sheet->setCellValue($column.'1', $headerLabel);
					$defaultWidth=25;
					if($column=='A'){
						$defaultWidth=10;
					}elseif($column=='B' || $column=='G' || $column=='P'){
						$defaultWidth=15;
					}elseif($column=='D' || $column=='C'){
						$defaultWidth=40;
					}elseif($column=='I' || $column=='K' || $column=='T'){
						$defaultWidth=60;
					}elseif($column=='H'){
						$defaultWidth=35;
					}
					$sheet->getColumnDimension($column)->setWidth($defaultWidth);
					$column++;
				}				
				
							
				
				$i=2;
				$sno=1;
			}	
			
			foreach($model as $offer)
			{
				$data=array();				
				//$application = $offer->audit->application;
				$application = $offer->application;
				$data['company_name']=($application)?$application->companyname:'';
				$data['email_address']=($application)?$application->emailaddress:'';
				$data['customer_number']=($application)?$application->customer->customer_number:'';
				$data['address']=($application)?$application->address:'';
				$data['zipcode']=($application)?$application->zipcode:'';
				$data['city']=($application)?$application->city:'';
				$data['oss']=($application)?$application->franchise->usercompanyinfo->osp_details:'';
				$data['country']=($application)?$application->countryname:'';
				$data['state']=($application)?$application->statename:'';

				$data['certificate_generated_date']=$offer->status ==$modelCertificate->arrEnumStatus['declined']?'NA':($offer->certificate_generated_date!='')?date($date_format,strtotime($offer->certificate_generated_date)):'';
				$data['certificate_valid_until']=$offer->status ==$modelCertificate->arrEnumStatus['declined']?'NA':($offer->certificate_valid_until!='')?date($date_format,strtotime($offer->certificate_valid_until)):'';
				
				$data['certificate_status_name']=$offer->arrCertificateStatus[$offer->certificate_status];
				$data['risk_category']=$offer->risk_category?$offer->riskcategory->name:'';
				$data['contact_person']=($application)?$application->customer->first_name.' '.$application->customer->last_name:'';
				$data['contact_number']=($application)?$application->customer->telephone:'';
				$data['created_at']=date($date_format,$offer->created_at);
				$data['certificate_standard']=$offer->standard?$offer->standard->code:'';
				$data['certified_by']=$offer->reviewer && $offer->reviewer->user?$offer->reviewer->user->first_name.' '.$offer->reviewer->user->last_name:'';

				$data['application_standard'] = $offer->standard->code;
			 
				$data['sub_contractor_details']=[];
				 
				$sc_name_address=$application->unitsubcontractor;
				
				
				if(count($sc_name_address)>0)
				{
					$sub_contractor=[];
					foreach($sc_name_address as $unit)
					{
						$unitdata=[];

						if(count($poststandard)>0){
							$unitstandard=$unit->unitappstandardall;
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
						
						$unitdata['name']=$unit['name'].", ".$unit['address'];
						$unitdata['unit_country']=$unit->country_id?$unit->country->name:'';

						$unitprocs=$unit->unitprocess;
						if(count($unitprocs)>0)
						{
							$sub_contractor_process=[];
							$unitprocess_names='';
							foreach($unitprocs as $unitvals)
							{
								if(count($poststandard)>0)
								{
									if(!in_array($unitvals->standard_id,$poststandard)){
										continue;
									}
								}
								$unitprocess_names.=$unitvals['process_name'].",";
							}
							$unitprocess_names=substr($unitprocess_names, 0, -1);
							$unitdata['process_name']=$unitprocess_names;
						}
						//$sub_contractor[]=$unitdata;

						$unitdata['standard_names']='';
						$unitstandard=$unit->unitappstandardall;
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
								$unitstd_names.=$unitstds->standard->code.",";
							}
							$unitstd_names=substr($unitstd_names, 0, -1);
							$unitdata['standard_names']=$unitstd_names;
						}
						$sub_contractor[]=$unitdata;

					}
					$data['sub_contractor_details']=$sub_contractor;	
				}

				$data['scope_holder_process'] = '';
				$sh_process=$application && $application->applicationscopeholder?$application->applicationscopeholder->unitprocessall:[];
				if(count($sh_process)>0)
				{
					$process_namesarr=[];
					foreach($sh_process as $procs)
					{
						if(count($poststandard)>0)
						{
							if(!in_array($procs->standard_id,$poststandard)){
								continue;
							}
						}
						$process_namesarr[] = $procs['process_name'];
					}
					$process_names= implode(', ',$process_namesarr);
					$data['scope_holder_process']=$process_names;
				}
					
				if($post['type']=='submit')
				{
					$app_list[]=$data;
				}else{		
					$applicationproduct = $application->applicationproduct;
					$poststandardIds = [];
					if(isset($post['standard_id']) && is_array($post['standard_id'])){
						$poststandardIds = $post['standard_id'];
					}
					$applicationProductdata = Yii::$app->globalfuns->getAppProducts($applicationproduct,$poststandardIds);
					$data['product_excel_list'] = isset($applicationProductdata['product_excel_list'])?$applicationProductdata['product_excel_list']:[];

					$column = 'A';
					$sheet->setCellValue($column.$i, $sno);$column++;					
					$sheet->setCellValue($column.$i, $data['customer_number']);$column++;    									
					$sheet->setCellValue($column.$i, $data['company_name']);$column++;
					$sheet->setCellValue($column.$i, $data['address']);$column++;
					$sheet->setCellValue($column.$i, ' '.$data['zipcode']);$column++;
					$sheet->setCellValue($column.$i, $data['contact_person']);$column++;
					$sheet->setCellValue($column.$i, ' '.$data['contact_number']);$column++;
					$sheet->setCellValue($column.$i, $data['email_address']);$column++;
					$sheet->setCellValue($column.$i, $data['scope_holder_process']);$column++;
					

					$imax=$i;
					if(isset($data['sub_contractor_details']) && !empty($data['sub_contractor_details']))
					{
						foreach($data['sub_contractor_details'] as $val)
						{
							$sheet->setCellValue("J".$imax, $val['name']);						
							$sheet->setCellValue("K".$imax, $val['process_name']);
							$sheet->setCellValue("L".$imax, $val['unit_country']);
							$sheet->setCellValue("M".$imax, $val['standard_names']);
							$imax++;
						}
						$column="N";
					}
					else
					{
						$sheet->setCellValue($column.$imax, '');$column++;
						$sheet->setCellValue($column.$imax, '');$column++;
						$sheet->setCellValue($column.$imax, '');$column++;
						$sheet->setCellValue($column.$imax, '');$column++;
						$imax++;
					}
					
					
					// $sheet->setCellValue($column.$i, $data['country']);$column++;
					// $sheet->setCellValue($column.$i, $data['application_standard']);$column++;
					$sheet->setCellValue($column.$i, $data['certificate_generated_date']);$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					$sheet->setCellValue($column.$i, $data['certificate_valid_until']);$column++;

					$sheet->setCellValue($column.$i, $data['risk_category']);
					$riskCategoryStyle='';
					if($offer->risk_category==1 || $offer->risk_category==2){
						$riskCategoryStyle=$styleHigh;
					}elseif($offer->risk_category==3){
						$riskCategoryStyle=$styleMedium;
					}else{
						$riskCategoryStyle=$styleLow;
					}					
					$sheet->getStyle($column.$i)->applyFromArray($riskCategoryStyle);$column++;
					
					$sheet->setCellValue($column.$i, $data['oss']);$column++;
					$sheet->setCellValue($column.$i, $data['certificate_status_name']);$column++;
					
										
					
					
					$sheet->setCellValue($column.$i, implode(', ', $data['product_excel_list']));$column++;					
					$sheet->setCellValue($column.$i, '');$column++;	
					$sheet->setCellValue($column.$i, $data['certified_by']);$column++;
					
					
					
					
					//$i=$standardCnt;
					//$i++;
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
				 $sheet->getStyle('A1:A'.$sno)->applyFromArray($styleCenter);	    			
				 $sheet->getStyle('B1:B'.$sno)->applyFromArray($styleCenter);	
				 $sheet->getStyle('A1:V1')->applyFromArray($this->styleWhite);					
				 $sheet->getStyle('A1:V1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE');
				 $sheet->getStyle('A1:V'.($sheet->getHighestRow()+1))->applyFromArray($this->styleVCenter);	
				 $sheet->getStyle('A1:V'.($sheet->getHighestRow()+1))->getAlignment()->setWrapText(true); 		 
				//$sheet->getStyle('R1:R'.$sno)->applyFromArray($styleCenter);					
				//$sheet->getStyle('K1:K'.$sno)->applyFromArray($styleCenter);	 				
				//$sheet->getStyle('L1:L'.$sno)->applyFromArray($styleCenter);	
				//$sheet->getStyle('M1:M'.$sno)->applyFromArray($styleCenter);
				
				//$spreadsheet->getSheet(0);				
				$sheet->getStyle('A1:A1')->getAlignment()->setWrapText(true); 
				
				$writer = new Xlsx($spreadsheet);
				$filepath=Yii::$app->params['report_files'].'client_report'.date('YmdHis').'.xlsx';
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
		
		
	}*/

    
}
