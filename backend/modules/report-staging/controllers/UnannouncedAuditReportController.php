<?php
namespace app\modules\report\controllers;

use Yii;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationChangeAddress;
use app\modules\master\models\User;
use app\modules\unannouncedaudit\models\UnannouncedAuditApplication;
use app\modules\unannouncedaudit\models\UnannouncedAuditApplicationUnit;
use app\modules\unannouncedaudit\models\UnannouncedAuditApplicationStandard;
use app\modules\audit\models\AuditPlanUnit;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * UnannouncedAuditReportController implements the CRUD actions for Product model.
 */
class UnannouncedAuditReportController extends \yii\rest\Controller
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
		$responsedata =array('status'=>0,'message'=>"No Data Found");
		$post = yii::$app->request->post();
		
		$usermodel = new User();
	
		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		
		$resource_access=$userData['resource_access'];				
		//->where('t.status=0')
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();

		$model = UnannouncedAuditApplication::find()->alias('t');			
		$model = $model->join('inner join', 'tbl_application as app','t.app_id=app.id');
		//$model = $model->join('inner join', 'tbl_application_change_address as appaddress','appaddress.id=app.address_id');
		$model = $model->join('inner join', 'tbl_unannounced_audit_application_standard as app_standard','app_standard.unannounced_audit_app_id=t.id');
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
				$arrHeaderLabel=array('Name of Operator','Name of unit','Scope of Unit','Address','Country','Zip/Postal Code','State/County','City of the inspected site','Month of present audit','Unannounced Audit month','Risk','Bussiness Group');
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
					$defaultWidth=20;
					if($column=='A' || $column=='B' || $column=='C' || $column=='D'){
						$defaultWidth=35;
					}elseif($column=='L'){
						$defaultWidth=30;
					}elseif($column=='K'){
						$defaultWidth=15;
					}
					$sheet->getColumnDimension($column)->setWidth($defaultWidth);
					$column++;
				}			
				

				$i=2;
			}	
			
			foreach($model as $offer)
			{
				$data=array();				
				$application = $offer->application;
				$data['company_name']=($application)?$application->companyname:'';
				$data['country_name']=($application)?$application->countryname:'';
				$data['created_at']=date($date_format,$offer->created_at);
				$data['risk_category_label'] = ($application->riskcategory ? $application->riskcategory->name : 'NA');
				
				$data['standard_name']='';
				$audit_stds = $offer->unannouncedauditstandard;
				if(count($audit_stds)>0)
				{
					$std_arr=[];
					foreach($audit_stds as $stds)
					{
						$std_arr[]=$stds->standard->code;
					}
					$data['standard_name']=implode(",",$std_arr);
				}


				$data['unit_details']='';
				$application_unit=$offer->audit->auditplan?$offer->audit->auditplan->auditplanunit:[];
				if(count($application_unit)>0)
				{
					$app_units=[];
					foreach($application_unit as $app_unit)
					{
						$unitstandard = $app_unit->unitstandard;
						if(count($poststandard)>0){
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
						$unitarr = [];
						$unitarr['unit_bsectors'] = [];
						$unitarr['id'] = $app_unit->unitdata->id;
						$unitarr['name'] = $app_unit->unitdata->name;
						$unitarr['address'] = $app_unit->unitdata->address;
						$unitarr['zipcode'] = $app_unit->unitdata->zipcode;
						$unitarr['city'] = $app_unit->unitdata->city;
						$unitarr['country_name'] = ($app_unit->unitdata->country_id!="")?$app_unit->unitdata->country->name:"";
						$unitarr['state_name'] = ($app_unit->unitdata->state_id!="")?$app_unit->unitdata->state->name:"";
						$unitarr['audit_date']=$app_unit->firstunitdate?date($date_format,strtotime($app_unit->firstunitdate->date)):'';
						$unitarr['present_audit_date'] = '';
						
						$AuditPlanUnitForDate = AuditPlanUnit::find()
						->where(['!=','id',$app_unit->id])
						->andWhere(['unit_id' => $app_unit->unit_id])->one();
						if($AuditPlanUnitForDate !== null){
							$unitarr['present_audit_date'] = date($date_format,strtotime($AuditPlanUnitForDate->firstunitdate->date));
						}
						

						$unitarr["process_name"]='';
						$UnannouncedAuditunit = UnannouncedAuditApplicationUnit::find()->where(['unannounced_audit_app_id'=>$offer->id])->andWhere(['unit_id'=>$app_unit->unit_id])->one();
						if($UnannouncedAuditunit !== null)
						{
							$unitprocess = $UnannouncedAuditunit->unannouncedauditunitprocess;

							if(count($unitprocess)>0)
							{
								$unitprocessnames=[];
								foreach($unitprocess as $unitPcs)
								{
									if(count($poststandard)>0)
									{
										if(!in_array($unitPcs->standard_id,$poststandard)){
											continue;
										}
									}
									$unitprocessnames[]=$unitPcs->process_name;
								}

								$unitarr["process_name"]=implode(", ",$unitprocessnames);
							}


							$unitarr['unit_bsectors']['name'] = '';
							$unitarr['unit_bsectors']['unit_bsectors'] = '';
							$appbsector = $UnannouncedAuditunit->unannouncedauditunitbsector;
							if(count($appbsector)>0)
							{
								$bsectorarr = [];
								foreach($appbsector as $app_bsector)
								{
									$bsectordata = [];
									$bsectordata['name'] = $app_bsector->business_sector_name;

									$appbsectorgroup = $app_bsector->unannouncedauditunitbsectorgroup;
									$bsectorgrouparr = [];
									foreach($appbsectorgroup as $app_bsector_group)
									{
										if(count($poststandard)>0)
										{
											if(!in_array($app_bsector_group->standard_id,$poststandard)){
												continue;
											}
										}
										$bsectorgrouparr[] = $app_bsector_group->business_sector_group_name;
									}

									$bsectordata['bsectorgroup'] = implode(",",$bsectorgrouparr);
									$bsectorarr[] = $bsectordata;
								}

								$unitarr['unit_bsectors'] = $bsectorarr;
							}
						}
						

						$app_units[]=$unitarr;
						
					}
					
					$data['unit_details']=$app_units;
				}


				
					
				if($post['type']=='submit')
				{
					$app_list[]=$data;
				}else{									
					$column = 'A';
					$sheet->setCellValue($column.$i, $data['company_name']);$column++;

					$riskCategoryStyle='';
					if($application->risk_category==1 || $application->risk_category==2){
						$riskCategoryStyle=$styleHigh;
					}elseif($application->risk_category==3){
						$riskCategoryStyle=$styleMedium;
					}else{
						$riskCategoryStyle=$styleLow;
					}	

					$imax=$i;
					if(isset($data['unit_details']) && !empty($data['unit_details']))
					{
						foreach($data['unit_details'] as $val)
						{
							$sheet->setCellValue("B".$imax, $val['name']);						
							$sheet->setCellValue("C".$imax, $val['process_name']);
							$sheet->setCellValue("D".$imax, $val['address']);
							$sheet->setCellValue("E".$imax, $val['country_name']);
							$sheet->setCellValue("F".$imax, $val['zipcode']);
							$sheet->setCellValue("G".$imax, $val['state_name']);
							$sheet->setCellValue("H".$imax, $val['city']);
							$sheet->setCellValue("I".$imax, $val['present_audit_date']);
							$sheet->setCellValue("J".$imax, $val['audit_date']);
							$bsector_names = '';
							foreach($val['unit_bsectors'] as $bsector)
							{
								$bsector_names .= $bsector['name'].": ".$bsector['bsectorgroup']."\n";
							}
							$sheet->setCellValue("L".$imax,  $bsector_names);
							

							$imax++;
						}
					}

					$sheet->getStyle('K'.$i)->applyFromArray($riskCategoryStyle);
					$sheet->setCellValue("K".$i, $data['risk_category_label']);
					
					
					$i=$imax;				
				}
			}
			
			
			if($post['type']=='submit')
			{
				$responsedata = array('status'=>1,'applications'=>$app_list);
				return $responsedata;
			}
			else
			{	
				//$sheet->getStyle('A1:A'.($sheet->getHighestRow()+1))->applyFromArray($styleCenter);	
				$sheet->getStyle('F1:F'.($sheet->getHighestRow()+1))->applyFromArray($styleLeft);	
				$sheet->getStyle('A1:L1')->applyFromArray($this->styleWhite);	
				$sheet->getStyle('A1:L1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE');   
				$sheet->getStyle('A1:L'.($sheet->getHighestRow()+1))->applyFromArray($this->styleVCenter);	
				$sheet->getStyle('A1:L'.($sheet->getHighestRow()+1))->getAlignment()->setWrapText(true); 
				
				$sheet->getStyle('A1:A1')->getAlignment()->setWrapText(true);

				$writer = new Xlsx($spreadsheet);
				$filepath=Yii::$app->params['report_files'].'Unannounced-audit'.date('YmdHis').'.xlsx';
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
