<?php
namespace app\modules\report\controllers;

use Yii;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationChangeAddress;
use app\modules\master\models\User;
use app\modules\certificate\models\Certificate;
use app\modules\certificate\models\CertificateStatusReview;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * UnitReportController implements the CRUD actions for Product model.
 */
class UnitReportController extends \yii\rest\Controller
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
		
		$modelObj = new Application();
		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		
		$resource_access=$userData['resource_access'];				
		$model = Application::find()->where(['t.status'=> $modelObj->arrEnumStatus['approved']])->alias('t');			
		$model = $model->join('inner join', 'tbl_application_unit as app_unit','app_unit.app_id=t.id');
		$model = $model->join('inner join', 'tbl_application_change_address as appaddress','appaddress.id=t.address_id');
		$model = $model->join('left join', 'tbl_application_unit_standard as app_unit_standard','app_unit_standard.unit_id=app_unit.id ');
		
		if(isset($post['standard_id']) && is_array($post['standard_id']) && count($post['standard_id'])>0)
		{
			$model = $model->andWhere(['app_unit_standard.standard_id'=> $post['standard_id']]);
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
				$arrHeaderLabel=array('Operator ID','Name of Operator','Name of unit','Scope of Unit','Address','Country','Zip/Postal Code','State/County','City of the inspected site','Contact name','e-mail','Date of Original Certification','Date of most recent certification','Expiry date of current cert.','Certified product(s)','Risk Assessment','Bussiness Group','Sample Withdrawn');
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
					}elseif($column=='E'){
						$defaultWidth=45;
					}elseif($column=='F'){
						$defaultWidth=20;
					}elseif($column=='C' || $column=='K'){
						$defaultWidth=35;
					}
					$sheet->getColumnDimension($column)->setWidth($defaultWidth);
					$column++;
				}	
				
				

				$i=2;
			}	
			
			foreach($model as $application)
			{
				$data=array();				
				
				$data['company_name']=$application->companyname;
				$data['customer_number']=$application->customer->customer_number;
				$applicationunit = $application->applicationunit;
				if(count($applicationunit)>0)
				{	
					$unit=[];
					foreach($applicationunit as $val)
					{
						$arr = [];
						$arr['unit_name']=$val->name;
						$arr['scope_unit']='';
						$arr['address']=$val->address;
						$arr['zipcode']=$val->zipcode;
						$arr['city']=$val->city;
						$arr['country']=($val->country_id)?$val->country->name:'';
						$arr['state']=($val->state_id)?$val->state->name:'';
						$unit[] = $arr;
					}
					$data['units']=$unit;
				}
				
				$data['contact_name']=$application->customer->first_name.' '.$application->customer->last_name;
				$data['email_address']=$application->emailaddress;
				
				

				if($post['type']=='submit')
				{
					$app_list[]=$data;
				}else{									
					
					$sheet->setCellValue('A'.$i, $data['customer_number']);
					$sheet->setCellValue('B'.$i, $data['company_name']);

					
					
					$sheet->setCellValue('J'.$i, $data['contact_name']);
					$sheet->setCellValue('K'.$i, $data['email_address']);
					$sheet->setCellValue('L'.$i, '');
					$sheet->setCellValue('M'.$i, '');
					$sheet->setCellValue('N'.$i, '');
					$sheet->setCellValue('O'.$i, '');
					$sheet->setCellValue('P'.$i, '');
					$sheet->setCellValue('Q'.$i, '');
					$sheet->setCellValue('R'.$i, '');

					foreach($data['units'] as $unitdata)
					{
						$sheet->setCellValue('C'.$i, $unitdata['unit_name']);
						$sheet->setCellValue('D'.$i, $unitdata['scope_unit']);
						$sheet->setCellValue('E'.$i, $unitdata['address']);
						$sheet->setCellValue('F'.$i, $unitdata['country']);
						$sheet->setCellValue('G'.$i, $unitdata['zipcode']);
						$sheet->setCellValue('H'.$i, $unitdata['state']);
						$sheet->setCellValue('I'.$i, $unitdata['city']);

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
				$sheet->getStyle('B1:R'.($sheet->getHighestRow()+1))->applyFromArray($styleLeft);	    			
				$sheet->getStyle('A1:R1')->applyFromArray($this->styleWhite);	
				$sheet->getStyle('A1:R1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE');
				$sheet->getStyle('A1:R'.($sheet->getHighestRow()+1))->applyFromArray($this->styleVCenter);	
				$sheet->getStyle('A1:R'.($sheet->getHighestRow()+1))->getAlignment()->setWrapText(true);

				$sheet->getStyle('A1:A1')->getAlignment()->setWrapText(true);

				$writer = new Xlsx($spreadsheet);
				$filepath=Yii::$app->params['report_files'].'unit_report'.date('YmdHis').'.xlsx';
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
