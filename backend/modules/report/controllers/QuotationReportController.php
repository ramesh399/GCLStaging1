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

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * QuotationReportController implements the CRUD actions for Product model.
 */
class QuotationReportController extends \yii\rest\Controller
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
		if(!Yii::$app->userrole->hasRights(array('customer_contract_report')))
		{
			return false;
		}

		$responsedata =array('status'=>0,'message'=>"No Data Found");
		$post = yii::$app->request->post();
		
		$usermodel = new User();
		$modelOffer = new Offer();
		$modelApplication = new Application();
		
		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		
		$resource_access=$userData['resource_access'];				
		$model = Offer::find()->where(['t.status'=>$modelOffer->enumStatus['finalized']])->alias('t');	
		$model = $model->join('inner join', 'tbl_application as app','t.app_id=app.id');		

		if(isset($post['standard_id']) && is_array($post['standard_id']) && count($post['standard_id'])>0)
		{
			$model = $model->join('inner join', 'tbl_application_standard as app_standard','app_standard.app_id =t.id ');
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

		if(isset($post['status_id']) && is_array($post['status_id']) && count($post['status_id'])>0)
		{
			$model = $model->andWhere(['t.status'=> $post['status_id']]);	
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
				$arrHeaderLabel=array('S.No','Quotation Number','Customer Number','Manday','Amount (USD)','Company Name','OSS','No.of Unit(s)','Standard(s)','Status','Created Date');
				$styleWhite = array('font'  => array('name'  => 'Arial','color' => array('rgb' => 'FFFFFF'),'bold'  => true,'size'  => 10,));
				$styleBgColor = array('fill' => array('type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'color' => array('rgb' => '578CDE')));
				$styleCenter = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,));
				$styleLeft = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT));
				$styleVCenter = array('alignment' => array('vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER));
				$styleHigh = array('font'  => array('color' => array('rgb' => 'FF0000'),'bold'  => true,));
				$styleMedium = array('font'  => array('color' => array('rgb' => 'F79647'),'bold'  => true,));
				$styleLow = array('font'  => array('color' => array('rgb' => '00B050'),'bold'  => true,));
				
				$spreadsheet = new Spreadsheet();				
				$sheet = $spreadsheet->getActiveSheet()->setTitle("Quotation Report");
				
				$column = 'A';
				foreach($arrHeaderLabel as $headerKey=>$headerLabel)
				{
					$sheet->setCellValue($column.'1', $headerLabel);
					$defaultWidth=25;
					if($column=='A' || $column=='C' || $column=='D' || $column=='E' || $column=='G' || $column=='H'){
						$defaultWidth=10;
					}elseif($column=='B' || $column=='K'){
						$defaultWidth=15;
					}elseif($column=='F'){
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
				$data['id']=$offer->id;
				$data['app_id']=$offer->application->id;
				$data['code']=$offer->application->code;
				$data['offer_code']=$offer->offer_code;
				$data['created_at']=date($date_format,$offer->created_at);
				$data['company_name']=$offer->application->companyname;
				$data['email_address']=$offer->application->emailaddress;
				$data['customer_number']=$offer->application->customer?$offer->application->customer->customer_number:'';				
				$data['standard']=$offer->standard;
				$data['manday']=$offer->manday;
				$data['telephone']=$offer->application->telephone;
				$data['currency']=$offer->offerlist->currency;
				$data['total_payable_amount']=$offer->offerlist->total_payable_amount;				
				$data['invoice_status']=$offer->invoice?$offer->invoice->status:'';
				$data['invoice_status_name']=$offer->invoice?$offer->invoice->arrStatus[$offer->invoice->status]:'Open';
				$data['invoice_id']=$offer->invoice?$offer->invoice->id:'';
				$data['invoice_total_payable_amount']=$offer->invoice?$offer->invoice->total_payable_amount:'';
				$data['invoice_number']=$offer->invoice?$offer->invoice->invoice_number:'';
				$data['offer_status_name']=$offer->arrStatus[$offer->status];
				
				$data['application_unit_count']=count($offer->application->applicationunit);
				
				$data['oss_label'] = $offer->application ? $usermodel->ossnumberdetail($offer->application->franchise_id) : '';
				
				$arrAppStd=array();
				$appStd=$offer->application->applicationstandardview;
				
				if(count($appStd)>0)
				{	
					foreach($appStd as $app_standard)
					{
						$arrAppStd[]=$app_standard->standard->code;
					}
				}					
				$data['application_standard']=implode(', ',$arrAppStd);
					
				if($post['type']=='submit')
				{
					$app_list[]=$data;
				}else{									
					$column = 'A';
					$sheet->setCellValue($column.$i, $sno);$column++;					
					$sheet->setCellValue($column.$i, $data['offer_code']);$column++;    									
					$sheet->setCellValue($column.$i, $data['customer_number']);$column++;
					$sheet->setCellValue($column.$i, $data['manday']);$column++;
					$sheet->setCellValue($column.$i, $data['total_payable_amount']);$column++;
					$sheet->setCellValue($column.$i, $data['company_name']);$column++;
					$sheet->setCellValue($column.$i, $data['oss_label']);$column++;
					$sheet->setCellValue($column.$i, $data['application_unit_count']);$column++;
					$sheet->setCellValue($column.$i, $data['application_standard']);$column++;
					$sheet->setCellValue($column.$i, $data['offer_status_name']);$column++;
					$sheet->setCellValue($column.$i, $data['created_at']);$column++;

					
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
				 $sheet->getStyle('A1:E'.($sheet->getHighestRow()+1))->applyFromArray($styleCenter);	    			
				 $sheet->getStyle('H1:H'.($sheet->getHighestRow()+1))->applyFromArray($styleCenter);
				 $sheet->getStyle('F1:G'.($sheet->getHighestRow()+1))->applyFromArray($styleLeft);
				 $sheet->getStyle('I1:K'.($sheet->getHighestRow()+1))->applyFromArray($styleLeft);	

				 $sheet->getStyle('A1:K1')->applyFromArray($this->styleWhite);					
				 $sheet->getStyle('A1:K1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE');
				 $sheet->getStyle('A1:K'.($sheet->getHighestRow()+1))->applyFromArray($this->styleVCenter);	
				 $sheet->getStyle('A1:K'.($sheet->getHighestRow()+1))->getAlignment()->setWrapText(true); 		 
				
				
				//$spreadsheet->getSheet(0);		
				$sheet->getStyle('A1:A1')->getAlignment()->setWrapText(true);		
				
				$writer = new Xlsx($spreadsheet);
				$filepath=Yii::$app->params['report_files'].'Quotation_report'.date('YmdHis').'.xlsx';
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

	

    
}
