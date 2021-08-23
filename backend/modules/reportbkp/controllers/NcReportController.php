<?php
namespace app\modules\report\controllers;

use Yii;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationChangeAddress;
use app\modules\application\models\ApplicationUnitBusinessSectorGroup;

use app\modules\audit\models\Audit;
use app\modules\master\models\Standard;
use app\modules\audit\models\AuditPlanUnitExecutionChecklist;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * NcReportController implements the CRUD actions for Product model.
 */
class NcReportController extends \yii\rest\Controller
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
		if(!Yii::$app->userrole->hasRights(array('program_wise_nc_report')))
		{
			return false;
		}
		
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();


		$responsedata =array('status'=>0,'message'=>"No Data Found");
		$post = yii::$app->request->post();
		
		$auditormodel = new Audit();
		$connection = Yii::$app->getDb();
		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$franchiseid=$userData['franchiseid'];
		$is_headquarters =$userData['is_headquarters'];
		
		$resource_access=$userData['resource_access'];	

		$std_filter="";
		if(isset($post['standard_id']) && is_array($post['standard_id']) && count($post['standard_id'])>0)
		{
			$std_filter=" AND checklist_standard.standard_id IN(".implode(",",$post['standard_id']).")";
		}

		$oss_filter="";
		if(isset($post['oss_id']) && is_array($post['oss_id']) && count($post['oss_id'])>0)
		{
			$oss_filter=" AND app.franchise_id IN(".implode(",",$post['oss_id']).")";		
		}
		else
		{
			if($is_headquarters != 1)
			{
				$oss_filter=" AND app.franchise_id IN(".$franchiseid.")";
			}
		}

		$date_filter="";
		if(isset($post['from_date']) && $post['from_date'] !='' && isset($post['to_date']) && $post['to_date'] !='')
		{
			$from_date = date('Y-m-d',strtotime($post['from_date']));
			$to_date = date('Y-m-d',strtotime($post['to_date']));

			//$date_filter=' AND FROM_UNIXTIME(audit.created_at,"%Y-%m-%d") BETWEEN "'.$from_date.'" AND "'.$to_date.'"';
			$date_filter=' AND DATE_FORMAT(audit_plan.audit_completed_date,"%Y-%m-%d") BETWEEN "'.$from_date.'" AND "'.$to_date.'"';
		}	

		/*
		echo "SELECT GROUP_CONCAT(checklist.id) AS checklist_id,GROUP_CONCAT(checklist_standard.standard_id) AS standard_ids,app.id AS app_id FROM tbl_audit_plan_unit_execution_checklist checklist "
		." INNER JOIN tbl_audit_plan_unit_execution_checklist_standard checklist_standard ON checklist_standard.audit_plan_unit_execution_checklist_id=checklist.id "
		." INNER JOIN tbl_audit_plan_unit audit_plan_unit ON checklist.audit_plan_unit_id=audit_plan_unit.id "
		." INNER JOIN tbl_audit_plan audit_plan ON audit_plan_unit.audit_plan_id=audit_plan.id "
		." INNER JOIN tbl_audit audit ON audit_plan.audit_id=audit.id "
		." INNER JOIN tbl_application app ON audit.app_id=app.id WHERE checklist.answer='2' AND (audit.status >= ".$auditormodel->arrEnumStatus['audit_completed']." AND audit.status != ".$auditormodel->arrEnumStatus['finalized_without_audit'].") ".$std_filter.$oss_filter.$date_filter." GROUP BY audit.app_id,audit.id";
		*/
		//,checklist_standard.standard_id
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		$command = $connection->createCommand("SELECT GROUP_CONCAT(checklist.id) AS checklist_id,checklist_standard.standard_id AS standard_id,app.id AS app_id FROM tbl_audit_plan_unit_execution_checklist checklist "
		." INNER JOIN tbl_audit_plan_unit_execution_checklist_standard checklist_standard ON checklist_standard.audit_plan_unit_execution_checklist_id=checklist.id "
		." INNER JOIN tbl_audit_plan_unit audit_plan_unit ON checklist.audit_plan_unit_id=audit_plan_unit.id "
		." INNER JOIN tbl_audit_plan audit_plan ON audit_plan_unit.audit_plan_id=audit_plan.id "
		." INNER JOIN tbl_audit audit ON audit_plan.audit_id=audit.id "
		." INNER JOIN tbl_application app ON audit.app_id=app.id WHERE checklist.answer='2' AND (audit.status >= ".$auditormodel->arrEnumStatus['audit_completed']." AND audit.status != ".$auditormodel->arrEnumStatus['finalized_without_audit'].") ".$std_filter.$oss_filter.$date_filter." GROUP BY audit.app_id,audit.id,checklist_standard.standard_id ORDER BY checklist_standard.standard_id ASC");
		
		/*
		echo "SELECT GROUP_CONCAT(checklist.id) AS checklist_id,checklist_standard.standard_id AS standard_id,app.id AS app_id FROM tbl_audit_plan_unit_execution_checklist checklist "
		." INNER JOIN tbl_audit_plan_unit_execution_checklist_standard checklist_standard ON checklist_standard.audit_plan_unit_execution_checklist_id=checklist.id "
		." INNER JOIN tbl_audit_plan_unit audit_plan_unit ON checklist.audit_plan_unit_id=audit_plan_unit.id "
		." INNER JOIN tbl_audit_plan audit_plan ON audit_plan_unit.audit_plan_id=audit_plan.id "
		." INNER JOIN tbl_audit audit ON audit_plan.audit_id=audit.id "
		." INNER JOIN tbl_application app ON audit.app_id=app.id WHERE checklist.answer='2' AND (audit.status >= ".$auditormodel->arrEnumStatus['audit_completed']." AND audit.status != ".$auditormodel->arrEnumStatus['finalized_without_audit'].") ".$std_filter.$oss_filter.$date_filter." GROUP BY audit.app_id,audit.id,checklist_standard.standard_id<br>";
		*/
		
		$result = $command->queryAll();			
		
		
		$app_list=array();	
		if(count($result)>0)
		{
			if($post['type']!='submit')
			{
				$spreadsheet = new Spreadsheet();				
				$createSheetCnt=0;				
				$sheet = $spreadsheet->setActiveSheetIndex($createSheetCnt);								
				//$sheet->setTitle("NC Report");
								
				$this->setSpreadSheetHeader($sheet);			
				$i=2;
			}	
			
			$previousStandardIID='';
			$currentStandardIID='';			
			foreach($result as $value)
			{
				$data=array();				
				$data['standard_name']='';
				if($value['standard_id'] !='')
				{
					$standardmodel = Standard::find()->where(['id'=>$value['standard_id']])->one();
					if($standardmodel !== null)
					{
						$data['standard_name']=$standardmodel->code;
					}
				}
				
				$currentStandardIID = $value['standard_id'];
				if($post['type']!='submit' && ($previousStandardIID=='' || $previousStandardIID!=$currentStandardIID))
				{
					if($previousStandardIID!='')
					{						
						$this->setSpreadSheetStyle($sheet,$i);						
						$createSheetCnt++;
						$spreadsheet->createSheet($createSheetCnt);															
						$sheet = $spreadsheet->setActiveSheetIndex($createSheetCnt);
						$sheet->setTitle($data['standard_name']);						
						$this->setSpreadSheetHeader($sheet);
						$i=2;						
					}else{
						$sheet->setTitle($data['standard_name']);						
					}					
					$previousStandardIID=$currentStandardIID;
				}			

				$data['company_name']='';
				$data['customer_number']='';
				$data['country']='';
				if($value['app_id'] !='')
				{
					$appmodel = Application::find()->where(['id'=>$value['app_id']])->one();
					if($appmodel !== null)
					{
						$data['customer_number']=$appmodel->customer_id?$appmodel->customer->customer_number:'';
						
						$addressmodel = $appmodel->applicationaddress;
						if($addressmodel !== null)
						{
							$data['company_name']=$addressmodel->company_name;
							$data['country']=$addressmodel->country->name;
						}
					}				
				}				

				$data['nc_details'] = [];
				if($value['checklist_id'] !='')
				{
					$ncmodel = AuditPlanUnitExecutionChecklist::find()->where(['id'=>explode(',',$value['checklist_id'])])->all();
					if(count($ncmodel)>0)
					{
						
						foreach($ncmodel as $nc)
						{
							$ncarray=[];
							
							$ncarray['standard_name'] = $data['standard_name'];
							
							$unitdata = $nc->auditplanunitexecution->auditplanunit->unitdata;						
							$ncarray['country_name'] = $unitdata->country->name;
							$ncarray['state_name'] = $unitdata->state->name;
							
							$ncarray['nc_question'] = $nc->question;	
							$ncarray['nc_requirement'] = $nc->finding;
							$ncarray['nc_level'] = ($nc->auditnonconformitytimeline)?$nc->auditnonconformitytimeline->name:'NA';

							$remediation = $nc->checklistremediationlatest;
							$ncarray['nc_status'] = $nc->arrEnumStatus['settled'] == $nc->status?'Closed':'Open';
							if($remediation !== null)
							{
								$ncarray['nc_closer'] = $remediation->created_by?$remediation->user->first_name." ".$remediation->user->last_name:'';
								$ncarray['nc_processing_step'] = $remediation->root_cause;
								$ncarray['nc_corrective_action'] = $remediation->corrective_action;
							}else{
								$ncarray['nc_closer'] = '';
								$ncarray['nc_processing_step'] = '';
								$ncarray['nc_corrective_action'] = '';
							}
							
							if($post['type']=='submit')
							{
								$app_list[]=$ncarray;
							}else{
								
								$column = 'A';				
								$sheet->setCellValue($column.$i, $ncarray['nc_requirement']);$column++;    									
								$sheet->setCellValue($column.$i, $ncarray['nc_question']);$column++;
								$sheet->setCellValue($column.$i, $ncarray['nc_level']);$column++;
								$sheet->setCellValue($column.$i, $ncarray['country_name']);$column++;
								$sheet->setCellValue($column.$i, $ncarray['state_name']);$column++;	
								$sheet->setCellValue($column.$i, $ncarray['nc_processing_step']);$column++;	
								$sheet->setCellValue($column.$i, $ncarray['nc_corrective_action']);
								$i++;
							}							
						}						
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
				$this->setSpreadSheetStyle($sheet,$i);
		
				$writer = new Xlsx($spreadsheet);
				$filepath=Yii::$app->params['report_files'].'nc_report'.date('YmdHis').'.xlsx';
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
	
	private function setSpreadSheetHeader($sheet)
	{
		$arrHeaderLabel=array('Requirement','NC','NC Level','Country/Region','State/Province','Processing Step(s)','Notes (Optional)');
		$styleWhite = array('font'  => array('name'  => 'Arial','color' => array('rgb' => 'FFFFFF'),'bold'  => true,'size'  => 10,));
		$styleBgColor = array('fill' => array('type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'color' => array('rgb' => '578CDE')));
		$styleCenter = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,));
		$styleLeft = array('alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT));
		$styleVCenter = array('alignment' => array('vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER));
		$styleHigh = array('font'  => array('color' => array('rgb' => 'FF0000'),'bold'  => true,));
		$styleMedium = array('font'  => array('color' => array('rgb' => 'F79647'),'bold'  => true,));
		$styleLow = array('font'  => array('color' => array('rgb' => '00B050'),'bold'  => true,));
										
		$column = 'A';
		foreach($arrHeaderLabel as $headerKey=>$headerLabel)
		{
			$sheet->setCellValue($column.'1', $headerLabel);
			$defaultWidth=25;
			if($column=='C'){
				$defaultWidth=15;			
			}elseif($column=='A' || $column=='B' || $column=='F' || $column=='G'){
				$defaultWidth=40;
			}
			$sheet->getColumnDimension($column)->setWidth($defaultWidth);
			$column++;
		}		
	} 

	private function setSpreadSheetStyle($sheet,$i)
	{
		$sheet->getStyle('A1:A'.($sheet->getHighestRow()+1))->applyFromArray($this->styleCenter);	
		$sheet->getStyle('B1:G'.($sheet->getHighestRow()+1))->applyFromArray($this->styleLeft);    			
		$sheet->getStyle('A1:G1')->applyFromArray($this->styleWhite);					
		$sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('578CDE');
		$sheet->getStyle('A1:G'.($sheet->getHighestRow()+1))->applyFromArray($this->styleVCenter);	
		$sheet->getStyle('A1:G'.($sheet->getHighestRow()+1))->getAlignment()->setWrapText(true); 		 
		$sheet->getStyle('A1:A1')->applyFromArray($this->styleCenter);		
		$sheet->getStyle('A1:A1')->getAlignment()->setWrapText(true);
	}		
}
