<?php
namespace app\modules\report\controllers;

use Yii;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationChangeAddress;
use app\modules\master\models\User;
use app\modules\master\models\Standard;
use app\modules\master\models\StandardLicenseFee;

use app\modules\certificate\models\Certificate;
use app\modules\certificate\models\CertificateStatusReview;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * StdMonthlyReportController implements the CRUD actions for Product model.
 */
class StdMonthlyReportController extends \yii\rest\Controller
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
		if(!Yii::$app->userrole->hasRights(array('te_monthly_certified_report')))
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
		$model = Certificate::find()->where('t.status>=2')->alias('t');		
		$model = $model->andWhere(['t.type'=> [1,2,4,5,6] ]);

		$model = $model->join('inner join', 'tbl_audit as audit','audit.id =t.audit_id');		
		$model = $model->join('inner join', 'tbl_application as app','audit.app_id=app.id');
		$model = $model->join('inner join', 'tbl_application_change_address as appaddress','appaddress.id=app.address_id');
		$model = $model->join('left join', 'tbl_application_standard as app_standard','app_standard.app_id =t.id ');
		
		if(isset($post['standard_id']) && $post['standard_id']!='' && $post['standard_id']>0)
		{
			$model = $model->andWhere(['t.standard_id'=> $post['standard_id']]);
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
	
		if(isset($post['month_id']) && $post['month_id'] !='' && isset($post['year_id']) && $post['year_id'] !='')
		{
			//$post['month_id'];
			$yearmonth_val = $post['year_id'].'-'.sprintf("%02d", $post['month_id']);
			//$model = $model->andWhere(['FROM_UNIXTIME(t.created_at,\'%Y-%m\')' =>  $yearmonth_val ]);
			$model = $model->andWhere([' DATE_FORMAT(t.certificate_generated_date,\'%Y-%m\')' =>  $yearmonth_val ]);
			
			//$model = $model->andWhere(['<=','t.created_at', strtotime($post['to_date'])]);
		}		
		$model = $model->groupBy(['t.id']);
		
		$app_list=array();
		$model = $model->all();	
		if(count($model)>0)
		{
			if($post['type']!='submit')
			{
				$arrHeaderLabel=array('Project/Scope Certificate Number','Organization Name','Country','Certification Body',
				'Last Certification Date','Last Audit Date','Scope Certificate Expiry','Number of NEW sites certified',''
				,'Processing Steps','Product Category','Product Details','Organization Address','Postal code'
				,'First name','Second Name','Contact Telephone','Contact Email Address'
				);
				//,'Name of subcontractor #1','Subcontractor #1 processes'
				
				$monthname = date("F",mktime(0,0,0,$post['month_id'],1,date("Y")));
				$post['month_name'] = $monthname;
				$spreadsheet = new Spreadsheet();				
				$sheet = $spreadsheet->getActiveSheet()->setTitle($monthname." ".$post['year_id']); 
				
				$column = 'A';
				foreach($arrHeaderLabel as $headerKey=>$headerLabel)
				{
					$sheet->setCellValue($column.'1', $headerLabel);
					$defaultWidth=25;
					if($column=='A' || $column== 'Q'){
						$defaultWidth=20;
					}elseif($column=='B' || $column== 'J'){
						$defaultWidth=30;
					}else if($column== 'N' || $column== 'F' || $column== 'H' || $column== 'I'){
						$defaultWidth=15;
					}else if($column== 'M'){
						$defaultWidth=35;
					}else if($column== 'L'){
						$defaultWidth=45;
					}
					$sheet->getColumnDimension($column)->setWidth($defaultWidth);
					$column++;
				}				
				
				$i=2;
				$sheet->setCellValue('H'.$i, 'First Site');
				$sheet->setCellValue('I'.$i, 'Subsequent Site');
				
				$sheet->mergeCells('H1:I1');

				$i++;
				$sno=1;
			}	
			$maxsubcontractorcnt = 0;
			foreach($model as $offer)
			{
				$data=array();				
				$application = $offer->audit->application;
				$audit = $offer->audit;
				$auditdates = [];
				if($audit->auditplan && count($audit->auditplan->auditplanunit)>0){
					$auditplanunit = $audit->auditplan->auditplanunit;
					if(count($auditplanunit)>0){
						foreach($auditplanunit as $auditplanunitobj){
							$auditdates[] = $auditplanunitobj->lastunitdate->date;
						}
					}
					
				}
				$auditdates = array_unique($auditdates);

				$data['certificate_code'] = $offer->code;
				$data['company_name']=($application)?$application->companyname:'';
				$data['customer_number']=($application)?$application->customer->customer_number:'';
				$data['country']=($application)?$application->countryname:'';
				$data['last_audit_dates']=implode(', ', $auditdates);
				//$data['zipcode']=($application)?$application->zipcode:'';
				//$data['email_address']=($application)?$application->emailaddress:'';
				$data['certification_body']= 'GCL International';
				$data['certificate_generated_date']=$offer->status ==$modelCertificate->arrEnumStatus['declined']?'NA':($offer->certificate_generated_date!='')?date($date_format,strtotime($offer->certificate_generated_date)):'';
				$data['certificate_valid_until']=$offer->status ==$modelCertificate->arrEnumStatus['declined']?'NA':($offer->certificate_valid_until!='')?date($date_format,strtotime($offer->certificate_valid_until)):'';
				
				$data['certificate_status_name']=$offer->arrCertificateStatus[$offer->certificate_status];
				$data['risk_category']=$offer->risk_category?$offer->riskcategory->name:'';
				$data['contact_person']=($application)?$application->customer->first_name.' '.$application->customer->last_name:'';
				
				$data['contact_number']=($application)?$application->customer->telephone:'';
				$data['created_at']=date($date_format,$offer->created_at);
				$data['certificate_standard']=$offer->standard?$offer->standard->code:'';
				$data['certified_by']=$offer->reviewer && $offer->reviewer->user?$offer->reviewer->user->first_name.' '.$offer->reviewer->user->last_name:'';

				$data['zipcode']=$application->applicationaddress?$application->applicationaddress->zipcode:'';
				$data['email_address']=$application->applicationaddress?$application->applicationaddress->email_address:'';
				$data['telephone']=$application->applicationaddress?$application->applicationaddress->telephone:'';
				$data['first_name']=$application->applicationaddress?$application->applicationaddress->first_name:'';
				$data['last_name']= $application->applicationaddress?$application->applicationaddress->last_name:'';
				
				$data['organization_address']= $application->applicationaddress?$application->applicationaddress->address.", ".$application->applicationaddress->city." - ".$application->applicationaddress->zipcode." ".$application->applicationaddress->state->name." ".$application->applicationaddress->country->name:'';

				
				$data['sub_contractor_details']=[];
				$sc_name_address=$application->unitsubcontractor;
				if(count($sc_name_address)>0)
				{
					$sub_contractor=[];
					foreach($sc_name_address as $unit)
					{
						$unitstandards = $unit->unitappstandard;
						$stdfound = 0;
						if(count($unitstandards)>0){
							foreach($unitstandards as $unitstd){
								if($post['standard_id'] == $unitstd->standard_id){
									$stdfound = 1;
								}
							}
						}
						if($stdfound == 0){
							continue;
						}
						$unitdata=[];
						$unitdata['name']=$unit['name'];//.", ".$unit['address'];

						$unitprocs=$unit->unitprocessall;
						$unitdata['process_name'] = [];
						if(count($unitprocs)>0)
						{
							$sub_contractor_process=[];
							$unitprocess_names= [];
							foreach($unitprocs as $unitvals)
							{
								if($post['standard_id'] == $unitvals->standard_id){
									$unitprocess_names[] =$unitvals['process_name'];
								}								
							}
							$unitprocess_names=implode(', ',$unitprocess_names);
							$unitdata['process_name']=$unitprocess_names;
						}
						$sub_contractor[]=$unitdata;

					}
					if(count($sub_contractor) > $maxsubcontractorcnt){
						$maxsubcontractorcnt = count($sub_contractor);
					}
					$data['sub_contractor_details']=$sub_contractor;	
				}

				$data['scope_holder_process'] = '';
				$sh_process=$application && $application->applicationscopeholder?$application->applicationscopeholder->unitprocess:[];
				if(count($sh_process)>0)
				{
					$process_names= [];
					foreach($sh_process as $procs)
					{
						$process_names[] = $procs['process_name'];
					}
					$process_names=implode(', ',$process_names);
					$data['scope_holder_process']=$process_names;
				}
				
				
				$appProduct=$application->applicationproduct;
				$productsList = Yii::$app->globalfuns->getAppProducts($appProduct,[$offer->standard_id]);
				$data['product_names_list'] = $productsList['product_names_list'];
				$data['product_excel_list'] = $productsList['product_excel_list'];
				$app_list[]=$data;
				if($post['type']=='submit')
				{
					//$app_list[]=$data;
				}else{									
					$column = 'A';   									
					$sheet->setCellValue($column.$i, $data['certificate_code']);$column++;
					$sheet->setCellValue($column.$i, $data['company_name']);$column++;
					$sheet->setCellValue($column.$i, $data['country']);$column++;
					$sheet->setCellValue($column.$i, 'GCL International');$column++;
					$sheet->setCellValue($column.$i, $data['certificate_generated_date']);$column++;
					$sheet->setCellValue($column.$i, implode(', ', $auditdates));$column++;
					$sheet->setCellValue($column.$i,  $data['certificate_valid_until']);$column++;
					
					$sheet->setCellValue($column.$i, $application->applicationscopeholder?1:0);$column++;
					$sheet->setCellValue($column.$i, count($data['sub_contractor_details']));$column++;

					$sheet->setCellValue($column.$i, $data['scope_holder_process']);$column++;
					$sheet->setCellValue($column.$i, implode(', ', $data['product_names_list']));$column++;
					$sheet->setCellValue($column.$i, implode(', ', $data['product_excel_list']));$column++;
					$sheet->setCellValue($column.$i, $data['organization_address']);$column++;
					//$sheet->setCellValue($column.$i, $data['application_standard']);$column++;
					$sheet->setCellValue($column.$i, ' '.$data['zipcode']);$column++;
					$sheet->setCellValue($column.$i, $data['first_name']);$column++;
					$sheet->setCellValue($column.$i, $data['last_name']);$column++;
					$sheet->setCellValue($column.$i, ' '.$data['telephone']);$column++;
					$sheet->setCellValue($column.$i, $data['email_address']);$column++;

					if(isset($data['sub_contractor_details']) && count($data['sub_contractor_details'])>0)
					{
						foreach($data['sub_contractor_details'] as $val)
						{
							$sheet->setCellValue($column.$i, $val['name']);$column++;
							$sheet->setCellValue($column.$i, $val['process_name']);$column++;
						}
					}
					
					$i++;					
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
				if($maxsubcontractorcnt > 0){
					$subcolumn = 'S';
					for($is = 1;$is <= $maxsubcontractorcnt;$is++){
						$sheet->getColumnDimension($subcolumn)->setWidth(30);
						$sheet->setCellValue($subcolumn.'1', 'Name of subcontractor #'.$is);$subcolumn++;
						$sheet->getColumnDimension($subcolumn)->setWidth(30);
						$sheet->setCellValue($subcolumn.'1', 'Subcontractor #'.$is.' processes');$subcolumn++;
					}
				}

				$column = 'A';
				$jtot = 17 + ($maxsubcontractorcnt*2);
				for($j=0;$j<= $jtot;$j++){
					if($column!='H' && $column!='I'){
						$sheet->mergeCells($column.'1:'.$column.'2');
					}	
					if(($j+1) <= $jtot){
						$column++;
					}					
				}
				$sheet->getStyle('A1:'.$column.'1')->applyFromArray($this->styleWhite);				
				$sheet->getStyle('A1:'.$column.'1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE');						
				$sheet->getStyle('H2:I2')->applyFromArray($this->styleWhite);				
				$sheet->getStyle('H2:I2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE');
				$sheet->getStyle('A1:'.$column.($sheet->getHighestRow()+1))->getAlignment()->setWrapText(true); 

				
				$sno++;
				$sheet->getStyle('A1:'.$column.$sno)->applyFromArray($this->styleVCenter);	// For Vertical Center
				$sheet->getStyle('H1:I'.$sno)->applyFromArray($this->styleCenter);	 
				

				// Second Sheet Starts
				//$this->feeCalculationSheet($spreadsheet,$post);
				// Second Sheet Ends

				// Third Sheet Starts
				//$this->activeListSheet($spreadsheet,$app_list);
				// Third Sheet Ends

				$sheet->getStyle('A1:A1')->getAlignment()->setWrapText(true); 

				//$worksheet3 = $spreadsheet->createSheet();
				//$worksheet3->setTitle('Active List for website');

				$writer = new Xlsx($spreadsheet);
				$filepath=Yii::$app->params['report_files'].'Std_monthly_report'.date('YmdHis').'.xlsx';
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

	private function feeCalculationSheet($spreadsheet,$postdata){

		

		$worksheet2 = $spreadsheet->createSheet();
		$worksheet2->setTitle('Fees Calculation');
		$Standard = Standard::find()->where(['id'=>$postdata['standard_id']])->one();
		$stdcode = '';
		if($Standard !== null){
			$stdcode = $Standard->code;
		}
		$license_fee = 0;
		$subsequent_license_fee = 0;

		$StandardLicenseFee = StandardLicenseFee::find()->where(['standard_id'=>$postdata['standard_id']])->one();
		if($StandardLicenseFee !== null){
			$subsequent_license_fee = $StandardLicenseFee->subsequent_license_fee;
			$license_fee = $StandardLicenseFee->license_fee;
		}

		$worksheet2->getColumnDimension('B')->setWidth('35');
		$worksheet2->getColumnDimension('C')->setWidth('16');
		$worksheet2->getColumnDimension('F')->setWidth('16');
		$worksheet2->getColumnDimension('G')->setWidth('60');
		$worksheet2->getColumnDimension('D')->setWidth('5');

		$worksheet2->mergeCells('D7:G7');

		$worksheet2->setCellValue('B3', 'CB Name');
		$worksheet2->setCellValue('C3', 'GCL International');
		$worksheet2->setCellValue('B4', 'Standard');
		$worksheet2->setCellValue('C4', $stdcode);
		$worksheet2->setCellValue('B5', 'Month');
		$worksheet2->setCellValue('C5', $postdata['month_name']);
		$worksheet2->setCellValue('B6', 'Year');
		$worksheet2->setCellValue('C6', $postdata['year_id']);
		$worksheet2->setCellValue('B7', 'Fee Schedule');
		$worksheet2->setCellValue('C7', $postdata['year_id']);
		$worksheet2->setCellValue('D7', $postdata['year_id'].' Fee Schedule effective date is March 1, '.$postdata['year_id']);
		$worksheet2->setCellValue('B8', 'Total Fees');
		$worksheet2->setCellValue('C8', '$  9400');

		$worksheet2->setCellValue('B11', 'Number of first sites:');
		$worksheet2->setCellValue('C11', '32');
		$worksheet2->setCellValue('D11', '@');
		$worksheet2->setCellValue('E11', '$ '.$license_fee);
		$worksheet2->setCellValue('F11', '7200');
		$worksheet2->setCellValue('G11', 'Include the first site supply chain scope certificate. Exclude all subcontractors and brand network certifications.');

		$worksheet2->setCellValue('B12', 'Number of subsequent sites:');
		$worksheet2->setCellValue('C12', '11');
		$worksheet2->setCellValue('D12', '@');
		$worksheet2->setCellValue('E12', '$ '.$subsequent_license_fee);
		$worksheet2->setCellValue('F12', '2200');
		$worksheet2->setCellValue('G12', 'Include all subsequent sites. If 2019 fee schedule is used, first and subsequent sites may be combined.');

		$worksheet2->setCellValue('B14', 'Number of brand network certifications:');
		$worksheet2->setCellValue('B15', 'Total fee for brand network certifications:');
		$worksheet2->setCellValue('G15', 'Calculated based on ASR-107-V 2020 Certification Fee Schedule")');

		$worksheet2->getStyle('B3:B8')->applyFromArray($this->styleWhite);				
		$worksheet2->getStyle('B3:B8')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE');
		
		$worksheet2->getStyle('C3:C3')->applyFromArray($this->styleWhite);				
		$worksheet2->getStyle('C3:C3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE');

		$worksheet2->getStyle('A1:G'.($worksheet2->getHighestRow()+1))->getAlignment()->setWrapText(true); 
		$worksheet2->getStyle('A1:A1')->getAlignment()->setWrapText(true); 
	}

	private function activeListSheet($spreadsheet,$datalist){
		$worksheet3 = $spreadsheet->createSheet();
		$worksheet3->setTitle('Active List for website');


		$arrHeaderLabel=array('CB Unique Identifier','Organization Name','Country','Certification Body',
		'Date of Last Certification' ,'Scope Certificate Expiry','Certified Products'
		);
		//,'Name of subcontractor #1','Subcontractor #1 processes'
		$styleWhite = array('font'  => array('name'  => 'Arial','color' => array('rgb' => 'FFFFFF'),'bold'  => true,'size'  => 10,));
		$styleBgColor = array('fill' => array('type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'color' => array('rgb' => '578CDE')));
		$styleCenter = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,));
		$styleLeft = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT));
		$styleVCenter = array('alignment' => array('vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER));
		$styleHigh = array('font'  => array('color' => array('rgb' => 'FF0000'),'bold'  => true,));
		$styleMedium = array('font'  => array('color' => array('rgb' => 'F79647'),'bold'  => true,));
		$styleLow = array('font'  => array('color' => array('rgb' => '00B050'),'bold'  => true,));
		
		
		//$sheet = $worksheet3->getActiveSheet()->setTitle("Client Report");
		
		$column = 'A';
		foreach($arrHeaderLabel as $headerKey=>$headerLabel)
		{
			$worksheet3->setCellValue($column.'1', $headerLabel);
			$defaultWidth=25;
			if($column=='B'){
				$defaultWidth=30;
			}else if($column== 'G'){
				$defaultWidth=45;
			}
			$worksheet3->getColumnDimension($column)->setWidth($defaultWidth);
			$column++;
		}				
		
		$i = 2;
		$sno=1;

		if(count($datalist)>0){
			foreach($datalist as $data){
				$column = 'A';   									
				$worksheet3->setCellValue($column.$i, $data['certificate_code']);$column++;
				$worksheet3->setCellValue($column.$i, $data['company_name']);$column++;
				$worksheet3->setCellValue($column.$i, $data['country']);$column++;
				$worksheet3->setCellValue($column.$i, 'GCL International');$column++;
				$worksheet3->setCellValue($column.$i, $data['certificate_generated_date']);$column++;
				$worksheet3->setCellValue($column.$i,  $data['certificate_valid_until']);$column++;
				$worksheet3->setCellValue($column.$i, implode(', ', $data['product_excel_list']));
				$i++;
			}
		}

		$worksheet3->getStyle('A1:'.$column.'1')->applyFromArray($this->styleWhite);				
		$worksheet3->getStyle('A1:'.$column.'1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE');						
		$worksheet3->getStyle('A1:'.$column.($worksheet3->getHighestRow()+1))->getAlignment()->setWrapText(true); 
		$worksheet3->getStyle('A1:'.$column.$i)->applyFromArray($this->styleVCenter);	// For Vertical Center
		$worksheet3->getStyle('A1:A1')->applyFromArray($this->styleVCenter);
	}


	public function actionGetMonthyear()
	{
		$months = [];
		for($i=1;$i<=12;$i++){
			$months[] = ['id'=>$i,'mvalue'=>date("F",mktime(0,0,0,$i,1,date("Y")))];
		}

		$startYear = 2015;
		$endYear = date('Y');
		$years = [];
		for($i=$startYear;$i<=$endYear;$i++){
			$years[] = $i;
		}
		return $responsedata = ['status' => 1, 'months'=> $months, 'years'=>$years];
	}

    
}
