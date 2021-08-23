<?php
namespace app\modules\report\controllers;

use Yii;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationChangeAddress;
use app\modules\master\models\User;
use app\modules\offer\models\Offer;
use app\modules\certificate\models\Certificate;
use app\modules\certificate\models\CertificateStatusReview;
use app\modules\invoice\models\Invoice;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * InvoiceReportController implements the CRUD actions for Product model.
 */
class InvoiceReportController extends \yii\rest\Controller
{
	private $styleWhite = array('font'  => array('name'  => 'Arial','color' => array('rgb' => 'FFFFFF'),'bold'  => true,'size'  => 10,));
	private $styleBgColor = array('fill' => array('type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'color' => array('rgb' => '578CDE')));
	private $styleCenter = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,));
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
		if(!Yii::$app->userrole->hasRights(array('customer_invoice_report')))
		{
			return false;
		}
		
		$responsedata =array('status'=>0,'message'=>"No Data Found");
		$post = yii::$app->request->post();
		
		$modelInvoice = new Invoice();

		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		
		$resource_access=$userData['resource_access'];				
		$model = Invoice::find()->alias('t');
		$model->joinWith(['application as app']);	
		$model = $model->join('left join', 'tbl_application_change_address as appaddress','appaddress.id=app.address_id');
		
		$invoicetype = $post['invoicetype'];

		if($invoicetype==3)
		{
			$model = $model->join('inner join', 'tbl_users as user','t.customer_id=user.id');
			$model = $model->join('inner join', 'tbl_user_company_info as userinfo','userinfo.user_id=user.id');
		}

		if($invoicetype==4)
		{
			$model = $model->join('inner join', 'tbl_users as user','t.franchise_id=user.id');
			$model = $model->join('inner join', 'tbl_user_company_info as userinfo','userinfo.user_id=user.id');
		}		

		if(isset($post['status_id']) && $post['status_id'] !='')
		{
			$model = $model->andWhere(['t.status'=> $post['status_id']]);			
		}
		
		if(isset($post['oss_id']) && is_array($post['oss_id']) && count($post['oss_id'])>0)
		{
			$model = $model->andWhere(['t.franchise_id'=> $post['oss_id']]);	
		}
		else
		{
			if($is_headquarters != 1)
			{
				$model = $model->andWhere(['t.franchise_id'=> $franchiseid]);	
			}
		}
	
		if(isset($post['from_date']) && $post['from_date'] !='' && isset($post['to_date']) && $post['to_date'] !='')
		{
			$model = $model->andWhere(['>=','t.created_at', strtotime($post['from_date'])]);				
			$model = $model->andWhere(['<=','t.created_at', strtotime($post['to_date'])]);
		}		
		$model = $model->groupBy(['t.id']);
		
		$app_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			if($post['type']!='submit')
			{
				if($invoicetype==1 || $invoicetype==2)
				{
					$arrHeaderLabel=array('S.No','Invoice Number','Invoice To','OSS','Standard(s)','Amount (USD)','Telephone','Status','Payment Date');
				}
				elseif($invoicetype==3)
				{
					$arrHeaderLabel=array('S.No','Invoice Number','Invoice To','OSS','Amount (USD)','Telephone','Type','Status','Payment Date');
				}
				else
				{
					$arrHeaderLabel=array('S.No','Invoice Number','Invoice To','Amount (USD)','Telephone','Type','Status','Payment Date');
				}
				
				$styleWhite = array('font'  => array('name'  => 'Arial','color' => array('rgb' => 'FFFFFF'),'bold'  => true,'size'  => 10,));
				$styleBgColor = array('fill' => array('type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'color' => array('rgb' => '578CDE')));
				$styleCenter = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,));
				$styleLeft = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT));
				$styleVCenter = array('alignment' => array('vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER));
				$styleHigh = array('font'  => array('color' => array('rgb' => 'FF0000'),'bold'  => true,));
				$styleMedium = array('font'  => array('color' => array('rgb' => 'F79647'),'bold'  => true,));
				$styleLow = array('font'  => array('color' => array('rgb' => '00B050'),'bold'  => true,));
				
				$spreadsheet = new Spreadsheet();				
				$sheet = $spreadsheet->getActiveSheet()->setTitle("Invoice Report");
				
				$column = 'A';
				foreach($arrHeaderLabel as $headerKey=>$headerLabel)
				{
					$sheet->setCellValue($column.'1', $headerLabel);
					$defaultWidth=25;
					if($column=='A'){
						$defaultWidth=10;
					}elseif($column=='B'){
						$defaultWidth=15;
					}elseif($column=='C'){
						$defaultWidth=35;
					}
					$sheet->getColumnDimension($column)->setWidth($defaultWidth);
					$column++;
				}				
				
				$i=2;
				$sno=1;
			}	
			
			foreach($model as $invoice)
			{
				$data=array();
				$data['id']=$invoice->id;
				$data['invoice_number']=$invoice->invoice_number;
				$invoiceType = $invoice->invoice_type;
				if($invoiceType==1 || $invoiceType==2)
				{
					$data['company_name']=$invoice->application?$invoice->application->companyname:'';
					$data['address']=$invoice->application?$invoice->application->address:'';
					$data['zipcode']=$invoice->application?$invoice->application->zipcode:'';
					$data['city']=$invoice->application?$invoice->application->city:'';
					$data['telephone']=$invoice->application?$invoice->application->telephone:'';
					$data['email_address']=$invoice->application?$invoice->application->emailaddress:'';	
					$data['oss_company_name']='';
					if($invoice->franchise && $invoice->franchise->usercompanyinfo)
					{
						$franchiseCompanyInfo = $invoice->franchise->usercompanyinfo;						
						$data['oss_company_name']=$franchiseCompanyInfo->company_name;											
					}
					
					if($invoice->hqfranchise && $invoice->hqfranchise->usercompanyinfo)
					{
						$franchiseCompanyInfo = $invoice->hqfranchise->usercompanyinfo;						
						$data['hq_oss_company_name']=$franchiseCompanyInfo->company_name;											
					}
					
					if($invoiceType==1)
					{
						$data['invoice_to']=$data['company_name'];
					}elseif($invoiceType==2){
						$data['invoice_to']=$data['oss_company_name'];
					}
					
				}elseif($invoiceType==3){					
					$userCompanyInfo = $invoice->customer->usercompanyinfo;
					$data['company_name']=$userCompanyInfo->company_name;					
					$data['telephone']=$userCompanyInfo->company_telephone;
					$data['email_address']=$userCompanyInfo->company_email;
					$data['invoice_to']=$data['company_name'];
					
					$data['oss_company_name']='';
					if($invoice->franchise && $invoice->franchise->usercompanyinfo)
					{
						$franchiseCompanyInfo = $invoice->franchise->usercompanyinfo;						
						$data['oss_company_name']=$franchiseCompanyInfo->company_name;											
					}
					
				}elseif($invoiceType==4){
					$franchiseCompanyInfo = $invoice->franchise->usercompanyinfo;
					$data['oss_company_name']='';
					$data['company_name']=$franchiseCompanyInfo->company_name;					
					$data['telephone']=$franchiseCompanyInfo->company_telephone;
					$data['email_address']=$franchiseCompanyInfo->company_email;
					$data['invoice_to']=$data['company_name'];
				}		
				
				$data['total_payable_amount']=$invoice->total_payable_amount;	
				$data['currency']=$invoice->currency_code;				
				
				$data['payment_date']=$invoice->payment_date?date($date_format,strtotime($invoice->payment_date)):"NA";

				$data['credit_note_option']=$invoice->credit_note_option?$modelInvoice->arrCreditNoteOptions[$invoice->credit_note_option]:"NA";
				$data['invoice_status']=$invoice->status;
				
				$data['invoice_status_name']=$invoice->arrStatus[$invoice->status];
				
				$stdArr = [];
				$invoicestandard = $invoice->invoicestandard;
				if(count($invoicestandard)>0){
					foreach($invoicestandard as $istandard){
						$stdArr[] = $istandard->standard->code;
					}
				}
				$data['standard_label']=implode(', ', $stdArr);
				
					
				if($post['type']=='submit')
				{
					$app_list[]=$data;
				}else{									
					$column = 'A';
					$sheet->setCellValue($column.$i, $sno);$column++;					
					$sheet->setCellValue($column.$i, $data['invoice_number']);$column++;    									
					$sheet->setCellValue($column.$i, $data['invoice_to']);$column++;

					if($invoicetype==1 || $invoicetype==3)
					{
						$sheet->setCellValue($column.$i, $data['oss_company_name']);$column++;
					}

					if($invoicetype==2)
					{
						$sheet->setCellValue($column.$i, $data['company_name']);$column++;
					}

					if($invoicetype==1 || $invoicetype==2)
					{
						$sheet->setCellValue($column.$i, $data['standard_label']);$column++;
					}

					$sheet->setCellValue($column.$i, $data['currency']." ".$data['total_payable_amount']);$column++;
					$sheet->setCellValue($column.$i, $data['telephone']);$column++;

					if($invoicetype==3 || $invoicetype==4)
					{
						$sheet->setCellValue($column.$i, $data['credit_note_option']);$column++;
					}

					$sheet->setCellValue($column.$i, $data['invoice_status_name']);$column++;
					$sheet->setCellValue($column.$i, $data['payment_date']);$column++;

					
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
				$sheet->getStyle('A1:B'.($sheet->getHighestRow()+1))->applyFromArray($styleCenter);	    			
				$sheet->getStyle('F1:F'.($sheet->getHighestRow()+1))->applyFromArray($styleCenter);
				$sheet->getStyle('C1:E'.($sheet->getHighestRow()+1))->applyFromArray($styleLeft);
				$sheet->getStyle('G1:I'.($sheet->getHighestRow()+1))->applyFromArray($styleLeft);

				if($invoicetype==1 || $invoicetype==2)
				{
					$sheet->getStyle('F1:F'.$sno)->applyFromArray($styleCenter);
				}

				if($invoicetype==3)
				{
					$sheet->getStyle('E1:E'.$sno)->applyFromArray($styleCenter);
				}

				if($invoicetype==4)
				{
					$sheet->getStyle('D1:D'.$sno)->applyFromArray($styleCenter);
				}

				$sheet->getStyle('A1:I1')->applyFromArray($this->styleWhite);		
				 
				$sheet->getStyle('A1:I1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE');
				if($invoicetype==4)
				{
					$sheet->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE');
				}
				 
				 $sheet->getStyle('A1:I'.($sheet->getHighestRow()+1))->applyFromArray($this->styleVCenter);	
				 $sheet->getStyle('A1:I'.($sheet->getHighestRow()+1))->getAlignment()->setWrapText(true); 		 
				
				
				//$spreadsheet->getSheet(0);
				$sheet->getStyle('A1:A1')->getAlignment()->setWrapText(true);				
				
				$writer = new Xlsx($spreadsheet);
				$filepath=Yii::$app->params['report_files'].'Invoice_report'.date('YmdHis').'.xlsx';
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
				die;		
			}
		}
		$responsedata = array('status'=>1,'applications'=>$app_list);
		return $responsedata;
		
		
	}

	

    
}
