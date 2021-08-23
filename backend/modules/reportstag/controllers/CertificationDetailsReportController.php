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
 * CertificationDetailsReportController implements the CRUD actions for Product model.
 */
class CertificationDetailsReportController extends \yii\rest\Controller
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
		if(!Yii::$app->userrole->hasRights(array('certification_details_report')))
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
		//$model = Certificate::find()->where(['t.type'=>[1,2]])->alias('t');
		$model = Certificate::find()->where(['t.status'=>array($modelCertificate->arrEnumStatus['certificate_generated'],$modelCertificate->arrEnumStatus['suspension'],$modelCertificate->arrEnumStatus['cancellation'],$modelCertificate->arrEnumStatus['withdrawn'],$modelCertificate->arrEnumStatus['extension'],$modelCertificate->arrEnumStatus['certificate_reinstate'],$modelCertificate->arrEnumStatus['declined'],$modelCertificate->arrEnumStatus['expired'])])->alias('t');
		//$model = $model->join('inner join', 'tbl_audit as audit','audit.id =t.audit_id');		
		$model = $model->join('inner join', 'tbl_application as app','t.parent_app_id=app.id');
		//$model = $model->join('inner join', 'tbl_application_change_address as appaddress','appaddress.id=app.address_id');
		//$model = $model->join('inner join', 'tbl_application_standard as app_standard','app_standard.app_id =app.id ');
		
		$poststandard = [];
		if(isset($post['standard_id']) && is_array($post['standard_id']) && count($post['standard_id'])>0)
		{
			$poststandard = $post['standard_id'];
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
	
		if(isset($post['from_date']) && $post['from_date'] !='' && isset($post['to_date']) && $post['to_date'] !='')
		{
			$from_date = date('Y-m-d', strtotime($post['from_date']));
			$to_date = date('Y-m-d', strtotime($post['to_date']));
			$model = $model->andWhere(['>=','t.certificate_generated_date', $from_date]);				
			$model = $model->andWhere(['<=','t.certificate_generated_date', $to_date]);
		}		
		$model = $model->groupBy(['t.id']);
		
		$app_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			if($post['type']!='submit')
			{
				$arrHeaderLabel=array('Code','Customer Number','Company Name','Country','Standard','Version','Certified Date','Valid Until','Type','Status','Is Valid?','Created By');
				$styleWhite = array('font'  => array('name'  => 'Arial','color' => array('rgb' => 'FFFFFF'),'bold'  => true,'size'  => 10,));
				$styleBgColor = array('fill' => array('type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'color' => array('rgb' => '578CDE')));
				$styleCenter = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,));
				$styleLeft = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT));
				$styleVCenter = array('alignment' => array('vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER));
				$styleHigh = array('font'  => array('color' => array('rgb' => 'FF0000'),'bold'  => true,));
				$styleMedium = array('font'  => array('color' => array('rgb' => 'F79647'),'bold'  => true,));
				$styleLow = array('font'  => array('color' => array('rgb' => '00B050'),'bold'  => true,));
				
				$spreadsheet = new Spreadsheet();				
				$sheet = $spreadsheet->getActiveSheet()->setTitle("Certification Details");
				
				$column = 'A';
				foreach($arrHeaderLabel as $headerKey=>$headerLabel)
				{
					$sheet->setCellValue($column.'1', $headerLabel);
					$defaultWidth=25;
					if($column=='A'){
						$defaultWidth=15;
					}elseif($column=='B'){
						$defaultWidth=20;
					}elseif($column=='G' || $column=='P'){	
						$defaultWidth=15;
					}elseif($column=='C'){
						$defaultWidth=40;
					}elseif($column=='D' || $column=='E'){	
						$defaultWidth=15;
					}elseif($column=='F'){
						$defaultWidth=10;
					}elseif($column=='K'){
						$defaultWidth=15;
					}elseif($column=='I' || $column=='J'){
						$defaultWidth=20;	
					}elseif($column=='T'){
						$defaultWidth=60;
					}elseif($column=='H'){
						$defaultWidth=15;
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
				$data=array();				
				
				$data['id']=$offer->audit->id;
				$data['certificate_id']=$offer->id;
				$data['code']=$offer->code;
				//$data['certificate_status_name']=$offer->arrStatus[$offer->status];
				//$data['certificate_status_name']=$offer->arrCertificateStatus[$offer->certificate_status];
				$data['certificate_status_name']=$offer->arrCertificateStatusForList[$offer->certificate_status];
				$data['certificate_status']=$offer->status;	
				$data['version']=$offer->version;				

				$data['status_label']=$offer->arrStatus[$offer->status];
				//$audit_type = $offer->audit->application->audit_type;
				//$additiontype = $offer->product_addition_id!='' && $offer->product_addition_id>0?'Product Addition':$modelApplication->arrAuditType[$audit_type];
				$data['type_label']=isset($offer->arrType[$offer->type])?$offer->arrType[$offer->type]:'NA'; //$additiontype;

				$data['app_id']=$offer->audit->app_id;
				$data['offer_id']=($offer)?$offer->id:'';
				
				$data['company_name']=($offer)?$offer->audit->application->companyname:'';
				$data['email_address']=($offer)?$offer->audit->application->emailaddress:'';
				$data['customer_number']=($offer)?$offer->audit->application->customer->customer_number:'';
								
				$data['certificate_generated_date']=$offer->status ==$modelCertificate->arrEnumStatus['declined']?'NA':date($date_format,strtotime($offer->certificate_generated_date));
				$data['certificate_valid_until']=$offer->status ==$modelCertificate->arrEnumStatus['declined']?'NA':date($date_format,strtotime($offer->certificate_valid_until));
				
				$data['creator']=$offer->generatedby?$offer->generatedby->first_name.' '.$offer->generatedby->last_name:'';
				$data['created_at']=date($date_format,$offer->created_at);
				$data['application_standard']=$offer->standard?$offer->standard->code:'';
				
				$arrAppStd=array();				
				if($offer)
				{
					$appobj = $offer->audit->application;					
					$data['application_unit_count']=count($appobj->applicationunit);
					$data['application_country']=$appobj->countryname;
					$data['application_city']=$appobj->city;				
				}										
					
				if($post['type']=='submit')
				{
					$app_list[]=$data;
				}else{		
					$column = 'A';					
					$sheet->setCellValue($column.$i, $data['code']);$column++;    									
					$sheet->setCellValue($column.$i, $data['customer_number']);$column++;
					$sheet->setCellValue($column.$i, $data['company_name']);$column++;
					$sheet->setCellValue($column.$i, $data['application_country']);$column++;
					$sheet->setCellValue($column.$i, $data['application_standard']);$column++;
					$sheet->setCellValue($column.$i, $data['version']);$column++;
					$sheet->setCellValue($column.$i, $data['certificate_generated_date']);$column++;
					$sheet->setCellValue($column.$i, $data['certificate_valid_until']);$column++;
					$sheet->setCellValue($column.$i, $data['type_label']);$column++;
					$sheet->setCellValue($column.$i, $data['status_label']);$column++;
					$sheet->setCellValue($column.$i, $data['certificate_status_name']);$column++;					
					$sheet->setCellValue($column.$i, $data['creator']);$column++;															
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
				 $sheet->getStyle('E1:F'.($sheet->getHighestRow()+1))->applyFromArray($styleCenter);
				 $sheet->getStyle('G1:K'.($sheet->getHighestRow()+1))->applyFromArray($styleCenter);
				 $sheet->getStyle('C1:D'.($sheet->getHighestRow()+1))->applyFromArray($styleLeft);
				 $sheet->getStyle('L1:L'.($sheet->getHighestRow()+1))->applyFromArray($styleLeft);
				 
				 $sheet->getStyle('A1:L1')->applyFromArray($this->styleWhite);					
				 $sheet->getStyle('A1:L1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE');
				 $sheet->getStyle('A1:L'.($sheet->getHighestRow()+1))->applyFromArray($this->styleVCenter);	
				 $sheet->getStyle('A1:L'.($sheet->getHighestRow()+1))->getAlignment()->setWrapText(true); 		 
								
				//$spreadsheet->getSheet(0);	
				$sheet->getStyle('A1:A1')->getAlignment()->setWrapText(true);			
				
				$writer = new Xlsx($spreadsheet);
				$filepath=Yii::$app->params['report_files'].'certificate_details_report'.date('YmdHis').'.xlsx';
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
