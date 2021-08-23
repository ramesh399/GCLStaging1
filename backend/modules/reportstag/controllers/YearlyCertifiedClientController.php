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
 * YearlyCertifiedClientController implements the CRUD actions for Product model.
 */
class YearlyCertifiedClientController extends \yii\rest\Controller
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
		if(!Yii::$app->userrole->hasRights(array('gots_yearly_certified_client_report')))
		{
			return false;
		}

		$responsedata =array('status'=>0,'message'=>"No Data Found");
		$post = yii::$app->request->post();
		$connection = Yii::$app->getDb();
		
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
		// $model = Certificate::find()
		// ->where(['t.type'=>[1,2]])
		// ->alias('t');
		
		// $model = $model->join('inner join', 'tbl_application as app','t.parent_app_id=app.id');
		// $model = $model->join('inner join', 'tbl_application_change_address as appaddress','appaddress.id=app.address_id');
		// $model = $model->join('inner join', 'tbl_application_standard as app_standard','app_standard.app_id =app.id ');
		// $poststandard = [];
		// if(isset($post['standard_id']) && is_array($post['standard_id']) && count($post['standard_id'])>0)
		// {
		// 	$poststandard = $post['standard_id'];
		// 	$model = $model->andWhere(['app_standard.standard_id'=> $post['standard_id']]);
		// }
		
		// if(isset($post['oss_id']) && is_array($post['oss_id']) && count($post['oss_id'])>0)
		// {
		// 	$model = $model->andWhere(['app.franchise_id'=> $post['oss_id']]);				
		// }
		// else
		// {
		// 	if($is_headquarters != 1)
		// 	{
		// 		$model = $model->andWhere(['app.franchise_id'=> $franchiseid]);	
		// 	}
		// }
	
		// if(isset($post['from_date']) && $post['from_date'] !='' && isset($post['to_date']) && $post['to_date'] !='')
		// {
		// 	$from_date = date('Y-m-d', strtotime($post['from_date']));
		// 	$to_date = date('Y-m-d', strtotime($post['to_date']));
		// 	$model = $model->andWhere(['>=','t.certificate_generated_date', $from_date]);				
		// 	$model = $model->andWhere(['<=','t.certificate_generated_date', $to_date]);
		// }
		
		
		// if(isset($post['year_id']) && $post['year_id'] !='')
		// {
		// 	$model = $model->andWhere([' DATE_FORMAT(t.certificate_generated_date,\'%Y\')' =>  $post['year_id'] ]);
		// }		
		//$model = $model->groupBy(['app.id']);
		$condition = '';
		if(isset($post['oss_id']) && is_array($post['oss_id']) && count($post['oss_id'])>0)
		{
		 	$condition .= ' AND t.franchise_id in ('.implode(',',$post['oss_id']).') ';
		}
		else
		{
			if($is_headquarters != 1)
		 	{
				//$model = $model->andWhere(['app.franchise_id'=> $franchiseid]);	
				$condition .= ' AND t.franchise_id='.$franchiseid.' ';
		 	}
		}
		if(isset($post['year_id']) && $post['year_id'] !='')
		{
			 //$model = $model->andWhere([' DATE_FORMAT(t.certificate_generated_date,\'%Y\')' =>  $post['year_id'] ]);
			$condition .= ' AND DATE_FORMAT(certificate.certificate_generated_date,\'%Y\')='.$post['year_id'].' ';
			 
		}	

		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		$command = $connection->createCommand("SELECT certificate.code AS certificate_code,app_address.company_name AS company_name,stdd.name AS standard_name,stdd.code AS standard_code,ctry.name AS country,state.name AS state,app_address.city AS city,GROUP_CONCAT(DISTINCT app_unit_bs_grp.business_sector_group_name) AS business_sector_group FROM `tbl_application` AS t
		INNER JOIN tbl_application_standard AS app_standard ON app_standard.app_id =t.id AND app_standard.standard_id IN(".implode(',',$post['standard_id']).") INNER JOIN tbl_application_change_address AS app_address ON app_address.id=t.address_id
		INNER JOIN tbl_country AS ctry ON ctry.id=app_address.country_id INNER JOIN tbl_state AS state ON state.id=app_address.state_id INNER JOIN tbl_application_unit AS app_unit ON app_unit.app_id=t.id INNER JOIN  tbl_application_unit_business_sector_group AS app_unit_bs_grp ON app_unit_bs_grp.unit_id=app_unit.id AND app_unit_bs_grp.standard_id=app_standard.standard_id INNER JOIN tbl_certificate AS certificate ON certificate.parent_app_id =t.id AND app_standard.standard_id=certificate.standard_id AND certificate.status>=2 INNER JOIN tbl_standard AS stdd ON stdd.id=certificate.standard_id 
		where 1=1 ".$condition." GROUP BY certificate.parent_app_id,certificate.standard_id");
		
		$result = $command->queryAll();		
		$app_list=array();
		//$model = $model->all();		
		if(count($result)>0)
		{
			if($post['type']!='submit')
			{
				$arrHeaderLabel=array('Certificate Code','Company Name','Standard Name','Standard Code','Country','State','City','Bussiness Sector Group');
				$styleWhite = array('font'  => array('name'  => 'Arial','color' => array('rgb' => 'FFFFFF'),'bold'  => true,'size'  => 10,));
				$styleBgColor = array('fill' => array('type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'color' => array('rgb' => '578CDE')));
				$styleCenter = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,));
				$styleLeft = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT));
				$styleVCenter = array('alignment' => array('vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER));
				$styleHigh = array('font'  => array('color' => array('rgb' => 'FF0000'),'bold'  => true,));
				$styleMedium = array('font'  => array('color' => array('rgb' => 'F79647'),'bold'  => true,));
				$styleLow = array('font'  => array('color' => array('rgb' => '00B050'),'bold'  => true,));
				
				$spreadsheet = new Spreadsheet();				
				$sheet = $spreadsheet->getActiveSheet()->setTitle("Yearly Certified Client");
				
				$column = 'A';
				foreach($arrHeaderLabel as $headerKey=>$headerLabel)
				{
					$sheet->setCellValue($column.'1', $headerLabel);
					$defaultWidth=25;
					if($column=='A' || $column=='E' || $column=='F'){
						$defaultWidth=20;
					}elseif($column=='D'){
						$defaultWidth=15;
					}elseif($column=='B' || $column=='H'){
						$defaultWidth=35;
					}
					$sheet->getColumnDimension($column)->setWidth($defaultWidth);
					$column++;
				}				
				
							
				
				$i=2;
				$sno=1;
			}	
			
			foreach($result as $value)
			{
				$data=array();				
				
				$data['company_name']=($value['company_name'])?$value['company_name']:'';
				$data['customer_number']=($value['certificate_code'])?$value['certificate_code']:'';
				$data['standard_name']=($value['standard_name'])?$value['standard_name']:'';
				$data['standard']=($value['standard_code'])?$value['standard_code']:'';
				$data['bsector_group']=($value['business_sector_group'])?$value['business_sector_group']:'';
				$data['city']=($value['city'])?$value['city']:'';
				$data['country']=($value['country'])?$value['country']:'';
				$data['state']=($value['state'])?$value['state']:'';

				
					
				if($post['type']=='submit')
				{
					$app_list[]=$data;
				}else{		
					 
					$poststandardIds = [];
					if(isset($post['standard_id']) && is_array($post['standard_id'])){
						$poststandardIds = $post['standard_id'];
					}
					 
					$column = 'A';
					// $sheet->setCellValue($column.$i, $sno);$column++;					
					$sheet->setCellValue($column.$i, $data['customer_number']);$column++;    									
					$sheet->setCellValue($column.$i, $data['company_name']);$column++;
					$sheet->setCellValue($column.$i, $data['standard_name']);$column++;
					$sheet->setCellValue($column.$i, $data['standard']);$column++;
					$sheet->setCellValue($column.$i, $data['country']);$column++;
					$sheet->setCellValue($column.$i, $data['state']);$column++;
					$sheet->setCellValue($column.$i, $data['city']);$column++;
					$sheet->setCellValue($column.$i, $data['bsector_group']);$column++;
					

					$i++;
					//$sno++;
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
				 $sheet->getStyle('B1:H'.($sheet->getHighestRow()+1))->applyFromArray($styleLeft);		
				 $sheet->getStyle('A1:H1')->applyFromArray($this->styleWhite);					
				 $sheet->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE');
				 $sheet->getStyle('A1:H'.($sheet->getHighestRow()+1))->applyFromArray($this->styleVCenter);	
				 $sheet->getStyle('A1:H'.($sheet->getHighestRow()+1))->getAlignment()->setWrapText(true); 		 
				
				$sheet->getStyle('A1:A1')->getAlignment()->setWrapText(true);			
				
				$writer = new Xlsx($spreadsheet);
				$filepath=Yii::$app->params['report_files'].'Yearly_certified_client'.date('YmdHis').'.xlsx';
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
