<?php
namespace app\modules\report\controllers;

use Yii;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationChangeAddress;
use app\modules\master\models\User;
use app\modules\offer\models\Offer;
use app\modules\audit\models\Audit;
use app\modules\certificate\models\Certificate;
use app\modules\certificate\models\CertificateStatusReview;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * ReviewerReportController implements the CRUD actions for Product model.
 */
class ReviewerReportController extends \yii\rest\Controller
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
		
		$appmodel = new Application();
		$offermodel = new Offer();
		$auditmodel = new Audit();

		$connection = Yii::$app->getDb();

		
		$from_month = date("m", strtotime($post['from_date']));
		$to_month = date("m", strtotime($post['to_date']));

		$months = range($from_month,$to_month);
		//print_r($months);

		if(count($months)>0)
		{
			if($post['type']!='submit')
			{
				$arrHeaderLabel=array('Year','Month','GCL ID','Reviewer name and Surname','No of Applications reviewed','','','Contracts','No of Initial Audit report reviewed','','','Average day of reviewing The Applications','Average day of reviewing The Quotation','Average day of reviewing The Audit Report');
				$styleWhite = array('font'  => array('name'  => 'Arial','color' => array('rgb' => 'FFFFFF'),'bold'  => true,'size'  => 10,));
				$styleBgColor = array('fill' => array('type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'color' => array('rgb' => '578CDE')));
				$styleCenter = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,));
				$styleLeft = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT));
				$styleHigh = array('font'  => array('color' => array('rgb' => 'FF0000'),'bold'  => true,));
				$styleMedium = array('font'  => array('color' => array('rgb' => 'F79647'),'bold'  => true,));
				$styleLow = array('font'  => array('color' => array('rgb' => '00B050'),'bold'  => true,));
				
				$spreadsheet = new Spreadsheet();				
				$sheet = $spreadsheet->getActiveSheet()->setTitle("Client Report");
				
				$column = 'A';
				foreach($arrHeaderLabel as $headerKey=>$headerLabel)
				{
					$sheet->setCellValue($column.'1', $headerLabel);
					$defaultWidth=15;
					if($column=='B'){
						$defaultWidth=20;
					}elseif($column=='D'){
						$defaultWidth=35;
					}
					$sheet->getColumnDimension($column)->setWidth($defaultWidth);
					$column++;
				}			
				
									
				
				$i=2;
				$sheet->setCellValue('E'.$i, 'Approved');
				$sheet->setCellValue('F'.$i, 'Rejected');
				$sheet->setCellValue('G'.$i, 'Total');
				
				$sheet->mergeCells('E1:G1');

				$sheet->setCellValue('I'.$i, 'Approved');
				$sheet->setCellValue('J'.$i, 'Rejected');
				$sheet->setCellValue('K'.$i, 'Total');
				
				$sheet->mergeCells('I1:K1');

			}	

			foreach($months as $month_number)
			{
				$data=array();

				$appdatefilter='';
				$offerdatefilter='';
				$auditdatefilter='';
				if(isset($post['from_date']) && $post['from_date'] !='' && isset($post['to_date']) && $post['to_date'] !='')
				{
					$appdatefilter=" AND (date_format(FROM_UNIXTIME(app.created_at), '%m, %Y' ) LIKE '%$month_number, 2020%' )";
					$offerdatefilter=" (date_format(FROM_UNIXTIME(offer.created_at), '%m, %Y' ) LIKE '%$month_number, 2020%' )";
					$auditdatefilter=" AND (date_format(FROM_UNIXTIME(aud.created_at), '%m, %Y' ) LIKE '%$month_number, 2020%' )";	
				}
				
				$appstdfilter = '';
				if(isset($post['standard_id']) && is_array($post['standard_id']) && count($post['standard_id'])>0)
				{
					$appstdfilter = ' AND (app_standard.standard_id IN("'.implode(",",$post['standard_id']).'"))';
				}

				$appossfilter='';
				if(isset($post['oss_id']) && is_array($post['oss_id']) && count($post['oss_id'])>0)
				{
					$appossfilter = ' AND (app.franchise_id IN("'.implode(",",$post['oss_id']).'"))';			
				}
				else
				{
					if($is_headquarters != 1)
					{
						$appossfilter = ' AND (app.franchise_id IN("'.$franchiseid.'"))';	
					}
				}

				$appcommand = $connection->createCommand('SELECT app.status,COUNT(app.id) AS app_count FROM `tbl_application` AS app 
				LEFT JOIN `tbl_application_standard` AS app_standard ON app_standard.app_id =app.id WHERE (app.status="'.$appmodel->arrEnumStatus['approved'].'" OR app.status="'.$appmodel->arrEnumStatus['osp_reject'].'") '.$appdatefilter.' '.$appossfilter.' '.$appstdfilter.' GROUP BY app.status');
				$appresult = $appcommand->queryAll();

				$app_approved = 0;
				$app_rejected = 0;
				$app_total = 0;
				if(count($appresult)>0)
				{
					foreach($appresult as $count)
					{
						if($count['status']==$appmodel->arrEnumStatus['approved'])
						{
							$app_approved = $count['app_count'];
						}
						else
						{
							$app_rejected = $count['app_count'];
						}
					}
					$app_total = $app_approved + $app_rejected;
				}


				$offercommand = $connection->createCommand('SELECT COUNT(offer.id) AS offer_count FROM `tbl_offer` AS offer 
				INNER JOIN `tbl_application` AS app ON app.id = offer.app_id LEFT JOIN `tbl_application_standard` AS app_standard ON app_standard.app_id = app.id WHERE '.$offerdatefilter.' '.$appossfilter.' '.$appstdfilter.' GROUP BY offer.id');
				$offerresult = $offercommand->queryOne();

				$offerCount = 0;
				if($offerresult !== false)
				{
					$offerCount = $offerresult['offer_count'];
				}

				$auditcommand = $connection->createCommand('SELECT aud.status,COUNT(aud.id) AS aud_count FROM `tbl_audit` AS aud 
				INNER JOIN `tbl_application` AS app ON app.id = aud.app_id LEFT JOIN `tbl_application_standard` AS app_standard ON app_standard.app_id =app.id WHERE (aud.status="'.$auditmodel->arrEnumStatus['approved'].'" OR aud.status="'.$auditmodel->arrEnumStatus['rejected'].'") '.$appdatefilter.' '.$appossfilter.' '.$appstdfilter.' GROUP BY aud.status');
				$auditresult = $auditcommand->queryAll();

				$aud_approved = 0;
				$aud_rejected = 0;
				$aud_total = 0;
				if(count($auditresult)>0)
				{
					foreach($auditresult as $auditcount)
					{
						if($auditcount['status']==$auditmodel->arrEnumStatus['approved'])
						{
							$aud_approved = $auditcount['aud_count'];
						}
						else
						{
							$aud_rejected = $auditcount['aud_count'];
						}
					}
					$aud_total = $aud_approved + $aud_rejected;
				}

				if($app_total!=0 || $offerCount!=0 || $aud_total!=0)
				{
					$data['year'] = "2020";
					$data['month'] = date("F", mktime(0, 0, 0, $month_number, 10));
					$data['gcl_id'] = '';
					$data['reviewer_name'] = '';
					$data['apps_approved'] = $app_approved;
					$data['apps_rejected'] = $app_rejected;
					$data['apps_total'] = $app_total;
					$data['offer_total'] = $offerCount;
					$data['audit_approved'] = $aud_approved;
					$data['audit_rejected'] = $aud_rejected;
					$data['audit_total'] = $aud_total;


					if($post['type']=='submit')
					{
						$app_list[]=$data;
					}else{									
						$column = 'A';
						$sheet->setCellValue($column.$i, $data['year']);$column++;
						$sheet->setCellValue($column.$i, $data['month']);$column++;	
						$sheet->setCellValue($column.$i, $data['gcl_id']);$column++;	
						$sheet->setCellValue($column.$i, $data['reviewer_name']);$column++;	
						$sheet->setCellValue($column.$i, $data['apps_approved']);$column++;				
						$sheet->setCellValue($column.$i, $data['apps_rejected']);$column++;	
						$sheet->setCellValue($column.$i, $data['apps_total']);$column++;
						$sheet->setCellValue($column.$i, $data['offer_total']);$column++;
						$sheet->setCellValue($column.$i, $data['audit_approved']);$column++;				
						$sheet->setCellValue($column.$i, $data['audit_rejected']);$column++;	
						$sheet->setCellValue($column.$i, $data['audit_total']);$column++;	
						$sheet->setCellValue($column.$i, '');$column++;	
						$sheet->setCellValue($column.$i, '');$column++;	
						$sheet->setCellValue($column.$i, '');$column++;	

						$i++;					
											
					}

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
				$sheet->getStyle('E1:F'.($sheet->getHighestRow()+1))->applyFromArray($styleCenter);	
				$sheet->getStyle('F1:K'.($sheet->getHighestRow()+1))->applyFromArray($styleCenter);	
				$sheet->getStyle('G1:N'.($sheet->getHighestRow()+1))->applyFromArray($styleCenter);	
				$sheet->getStyle('B1:D'.($sheet->getHighestRow()+1))->applyFromArray($styleLeft);
			
				
				$sheet->getStyle('A1:N1')->applyFromArray($this->styleWhite);	
				$sheet->getStyle('A1:N1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE'); 
				$sheet->getStyle('A1:N'.($sheet->getHighestRow()+1))->applyFromArray($this->styleVCenter);	
				$sheet->getStyle('A1:N'.($sheet->getHighestRow()+1))->getAlignment()->setWrapText(true); 

				$sheet->getStyle('A1:A1')->getAlignment()->setWrapText(true);

				$writer = new Xlsx($spreadsheet);
				$filepath=Yii::$app->params['report_files'].'reviewer_performance_report'.date('YmdHis').'.xlsx';
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
