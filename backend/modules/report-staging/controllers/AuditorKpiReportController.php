<?php
namespace app\modules\report\controllers;

use Yii;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationChangeAddress;
use app\modules\master\models\User;
use app\modules\certificate\models\Certificate;
use app\modules\transfercertificate\models\Request;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * AuditorKpiReportController implements the CRUD actions for Product model.
 */
class AuditorKpiReportController extends \yii\rest\Controller
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
		
		$modelObj = new Request();	
	
		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		
		$resource_access=$userData['resource_access'];				
		$model = Certificate::find()->where('t.certificate_status=0')->alias('t');		

		$model = $model->join('inner join', 'tbl_audit as audit','audit.id =t.audit_id');		
		$model = $model->join('inner join', 'tbl_application as app','audit.app_id=app.id');
		$model = $model->join('inner join', 'tbl_application_change_address as appaddress','appaddress.id=app.address_id');

		$model = $model->join('left join', 'tbl_application_standard as app_standard','app_standard.app_id =t.id ');
		
		if(isset($post['standard_id']) && is_array($post['standard_id']) && count($post['standard_id'])>0)
		{
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
				$arrHeaderLabel=array('Operator ID','Operator Name','Unit Name','Sub-program','Name of Auditor','Audit start Date','Audit End date','Report Uploaded Date','First Review Date','2nd review submited date by auditor','Second Review Date','3rd review submited date by auditor','Third Review Date','4th review submited date by auditor','NC Clouser status','NC Closed date','Certificate date','Status','Reviewed By');
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
					if($column=='A'){
						$defaultWidth=10;
					}elseif($column=='B'){
						$defaultWidth=35;
					}elseif($column=='F' || $column=='G' || $column=='H' || $column=='I' || $column=='J' || $column=='K' || $column=='L' || $column=='M' || $column=='N'){
						$defaultWidth=20;
					}
					$sheet->getColumnDimension($column)->setWidth($defaultWidth);
					$column++;
				}			
				
				

				$i=2;
			}	
			
			foreach($model as $offer)
			{
				$data=array();				
				$application = $offer->audit->application;
				$data['company_name']=($application)?$application->companyname:'';
				$data['customer_number']=($application)?$application->customer->customer_number:'';
					
				if($post['type']=='submit')
				{
					$app_list[]=$data;
				}else{									
					$column = 'A';
					$sheet->setCellValue($column.$i, $data['customer_number']);$column++;
					$sheet->setCellValue($column.$i, $data['company_name']);$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					$sheet->setCellValue($column.$i, '');$column++;
					
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
				$sheet->getStyle('A1:A'.($sheet->getHighestRow()+1))->applyFromArray($styleCenter);	    			
				$sheet->getStyle('B1:S'.($sheet->getHighestRow()+1))->applyFromArray($styleLeft);
				$sheet->getStyle('A1:S1')->applyFromArray($this->styleWhite);	
				$sheet->getStyle('A1:S1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE'); 
				$sheet->getStyle('A1:S'.($sheet->getHighestRow()+1))->applyFromArray($this->styleVCenter);	
				$sheet->getStyle('A1:S'.($sheet->getHighestRow()+1))->getAlignment()->setWrapText(true); 
				
				$sheet->getStyle('A1:A1')->getAlignment()->setWrapText(true);


				$writer = new Xlsx($spreadsheet);
				$filepath=Yii::$app->params['report_files'].'audit_kpi_report'.date('YmdHis').'.xlsx';
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
