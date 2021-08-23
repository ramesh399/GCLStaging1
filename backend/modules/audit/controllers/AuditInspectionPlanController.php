<?php
namespace app\modules\audit\controllers;

use Yii;

use yii\web\NotFoundHttpException;
use app\modules\audit\models\AuditPlanInspection;
use app\modules\audit\models\AuditPlanInspectionPlan;
use app\modules\audit\models\AuditPlanInspectionPlanInspector;
use app\modules\audit\models\AuditPlanUnit;
use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\Audit;

use app\modules\application\models\ApplicationUnit;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditInspectionPlanController implements the CRUD actions for Process model.
 */
class AuditInspectionPlanController extends \yii\rest\Controller
{
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
		return ['1'=>'111']; die;
    }
	
	public function actionCreate()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		
		
		if ($data) 
		{
			/*
			$audit_type = 1;
			if($modelAudit->status == $modelAudit->arrEnumStatus['followup_review_in_process']){
				$auditstatus = $modelAudit->arrEnumStatus['followup_review_completed'];
				$audit_type = 2;
			}else{
				$auditstatus = $modelAudit->arrEnumStatus['review_completed'];
			}
			*/
			if(!Yii::$app->userrole->isAuditProjectLA($data['audit_id']))
			{
				return $responsedata;
			}
			$Auditmodel = Audit::find()->where(['id' => $data['audit_id']])->one();
			$audit_type = 1;
			$checkStatusApplicable = 0;
			if($Auditmodel->status == $Auditmodel->arrEnumStatus['followup_review_completed'] || $Auditmodel->status == $Auditmodel->arrEnumStatus['followup_inspection_plan_inprocess']){
				$audit_type = 2;

				$Auditmodel->status = $Auditmodel->arrEnumStatus['followup_inspection_plan_inprocess'];
				$Auditmodel->save();
				$checkStatusApplicable = 1;
			}
			if($Auditmodel->status == $Auditmodel->arrEnumStatus['review_completed'] || $Auditmodel->status == $Auditmodel->arrEnumStatus['inspection_plan_in_process']){
				$checkStatusApplicable = 1;
			}
			if(!$checkStatusApplicable){
				return $responsedata;
			}
			$auditInsplan ='';
			$auditplan= AuditPlan::find()->where(['audit_id'=>$data['audit_id']])->one();
			if($auditplan!==null){
				$auditInsplan = AuditPlanInspection::find()->where(['audit_plan_id'=>$auditplan->id,'audit_type'=>$audit_type])->one();
			}
			if(isset($data['id']))
			{
				$model = AuditPlanInspectionPlan::find()->where(['id'=>$data['id']])->one();
				if($model!==null)
				{
					if($auditInsplan!==null && $auditInsplan!=''){
						$auditInsplan->updated_by = $userid;
						$auditInsplan->updated_at = time();
						$auditInsplan->save();
					}
					// $model->audit_plan_inspection_id=$auditInsplan->id;
					$model->application_unit_id=$data['application_unit_id'];
					$model->activity=$data['activity'];
					//$model->inspector=$data['inspector'];
					$model->date=date('Y-m-d',strtotime($data['date']));
					$model->start_time=$data['start_time']['hour'].':'.$data['start_time']['minute'];
					$model->end_time=$data['end_time']['hour'].':'.$data['end_time']['minute'];
					$model->person_need_to_be_present=$data['person_need_to_be_present'];
					if($model->validate() && $model->save())
					{
						AuditPlanInspectionPlanInspector::deleteAll(['inspection_plan_id' => $data['id']]);
						foreach($data['inspector'] as $inspector)
						{
							$inspectormodel = new AuditPlanInspectionPlanInspector();
							$inspectormodel->inspection_plan_id = $model->id;
							$inspectormodel->user_id = $inspector;
							$inspectormodel->save();
						}
						$responsedata=array('status'=>1,'message'=>'Inspection Plan has been updated successfully');
					}
				}
			}
			else
			{
				/*
				$auditInsplan ='';
				$auditplan= AuditPlan::find()->where(['audit_id'=>$data['audit_id']])->one();
				if($auditplan!==null){
					$auditInsplan = AuditPlanInspection::find()->where(['audit_plan_id'=>$auditplan->id,'audit_type'=>$audit_type])->one();
				}
				*/
				if($auditInsplan===null || $auditInsplan==''){
					$auditInsplan=new AuditPlanInspection();
					$auditInsplan->audit_plan_id=$auditplan->id;
					$auditInsplan->created_by=$userid;
					$auditInsplan->audit_type = $audit_type;
				}else if($auditInsplan!==null && $auditInsplan!=''){
					$auditInsplan->updated_by = $userid;
					$auditInsplan->updated_at = time();
					$auditInsplan->save();
				}
				// else{
				// 	AuditPlanInspectionPlan::deleteAll(['audit_plan_inspection_id' => $auditInsplan->id]);
				// }
				
				if($auditplan!==null)
				{
					$audit = Audit::find()->where(['id'=>$auditplan->audit_id])->one();
					if($audit_type ==2){
						$audit->status = $audit->arrEnumStatus['followup_inspection_plan_inprocess'];
					}else{
						$audit->status = $audit->arrEnumStatus['inspection_plan_in_process'];
					}
					
					$audit->save();
					
					if($auditInsplan->validate() && $auditInsplan->save())
					{  
						$model=new AuditPlanInspectionPlan();
						$model->audit_plan_inspection_id=$auditInsplan->id;
						$model->application_unit_id=$data['application_unit_id'];
						$model->activity=$data['activity'];
						//$model->inspector=$data['inspector'];
						$model->date=date('Y-m-d',strtotime($data['date']));
						$model->start_time=$data['start_time']['hour'].':'.$data['start_time']['minute'];
						$model->end_time=$data['end_time']['hour'].':'.$data['end_time']['minute'];
						$model->person_need_to_be_present=$data['person_need_to_be_present'];
						if($model->validate() && $model->save())
						{
							foreach($data['inspector'] as $inspector)
							{
								$inspectormodel = new AuditPlanInspectionPlanInspector();
								$inspectormodel->inspection_plan_id = $model->id;
								$inspectormodel->user_id = $inspector;
								$inspectormodel->save();
							}
							$responsedata=array('status'=>1,'message'=>'Inspection Plan has been created successfully');
						}
					}

				}
			}
			
		}
		return $this->asJson($responsedata);
	}

	public function actionViewInspectionPlan()
	{
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$data = Yii::$app->request->post();
		$showsendtocustomer = 1;
		$showInspectionApproval = 0;

		if ($data) 
		{
			if(!Yii::$app->userrole->isAuditProjectLA($data['audit_id']))
			{
				return $responsedata;
			}
			$model = AuditPlan::find()->where(['audit_id' => $data['audit_id']])->one();
			if ($model !== null)
			{
				$Auditmodel = Audit::find()->where(['id' => $data['audit_id']])->one();

				
				if($Auditmodel !== null){
					$audit_type = $Auditmodel->audit_type;
					if($audit_type == $Auditmodel->audittypeEnumArr['unannounced_audit'] && $Auditmodel->followup_status != 1){
						$share_plan_to_customer = $Auditmodel->auditplan->share_plan_to_customer;
						if($share_plan_to_customer == $Auditmodel->auditplan->arrSharePlanEnum['donot_share']
							|| $share_plan_to_customer == $Auditmodel->auditplan->arrSharePlanEnum['share_by_email'])
						{
							$showsendtocustomer = 0;
							$showInspectionApproval = 1;
						}
					}
					
				}


				//if ($model !== null)
				//followup_review_completed"=>'19','followup_inspection_plan_inprocess
				$followup_audit_type = 1;
				if($Auditmodel->status == $Auditmodel->arrEnumStatus['followup_review_completed'] || $Auditmodel->status == $Auditmodel->arrEnumStatus['followup_inspection_plan_inprocess']){
					$followup_audit_type = 2;
				}
				$resultarr=array();
				
				$inspectionplan = AuditPlanInspection::find()->where(['audit_plan_id' => $model->id,'audit_type'=>$followup_audit_type])->orderBy(['id'=> SORT_DESC])->one();
				if($inspectionplan !== null)
				{
					$i=0;
					if(count($inspectionplan->auditplaninspectionplan)>0){
						foreach($inspectionplan->auditplaninspectionplan as $val)
						{
							$inspector_ids = [];
							$inspectors = [];
							$inspectorList = [];
							$inspector_names = '';
							if(count($val->auditplaninspectionplaninspector)>0)
							{
								foreach($val->auditplaninspectionplaninspector as $inspector)
								{
									$inspector_ids[] = "".$inspector->user_id;
									$inspectors[] = $inspector->user->first_name." ".$inspector->user->last_name;
								}
								$inspector_names = implode(", ",$inspectors);
							}

							$resultarr[]=array('id'=>$val->id,'application_unit_id'=>$val->applicationunit->id,'application_unit_name'=>$val->applicationunit->name,'activity'=>$val['activity'],'inspector'=>$inspector_ids,'inspectors'=>$inspector_names,'date'=>date($date_format,strtotime($val['date'])),'start_time'=>date('G:i', strtotime($val['start_time'])),'end_time'=>date('G:i', strtotime($val['end_time'])),'person_need_to_be_present'=>$val['person_need_to_be_present']);
						}		
					}
								
				}
				$unitdates = [];

				if($followup_audit_type ==2){
					
					if(count($model->followupauditplanunit)>0){
						foreach($model->followupauditplanunit as $auditplanunit){
							if(count($auditplanunit->followupauditplanunitdate)>0){
								foreach($auditplanunit->followupauditplanunitdate as $unitdate){
									$unitdates[] = $unitdate->date;
								}
							}
							
						}
						
						//$unitdates = [];
						$unituniquedates = array_unique($unitdates);
						$unitdates = [];
						if(count($unituniquedates)>0){
							foreach($unituniquedates as $unitdate){
								$unitdates[] = $unitdate;
							}
						}
					}
				}else{
					if(count($model->auditplanunit)>0){
						foreach($model->auditplanunit as $auditplanunit){
							if(count($auditplanunit->auditplanunitdate)>0){
								foreach($auditplanunit->auditplanunitdate as $unitdate){
									$unitdates[] = $unitdate->date;
								}
							}
							
						}
						
						//$unitdates = [];
						$unituniquedates = array_unique($unitdates);
						$unitdates = [];
						if(count($unituniquedates)>0){
							foreach($unituniquedates as $unitdate){
								$unitdates[] = $unitdate;
							}
						}
					}
				}
				

				return ['inspectionplans'=>$resultarr,'unitdates'=>$unitdates, 'showsendtocustomer'=>$showsendtocustomer, 'showInspectionApproval'=>$showInspectionApproval];	

			}	
		}
	}	

	public function actionGetInspector()
	{
        $data = Yii::$app->request->post();
		if ($data) 
		{
			$unitmodel = AuditPlanUnit::find()->where(['audit_plan_id'=>$data['audit_plan_id'], 'unit_id'=>$data['unit_id']])->one();
			if ($unitmodel !== null)
			{
				if($unitmodel->followup_status==1){
					$unitauditors = $unitmodel->followupunitauditors;
				}else{
					$unitauditors = $unitmodel->unitauditors;
				}
				
				if(count($unitauditors)>0)
				{
					$inspectors = [];
					foreach($unitauditors as $user)
					{
						$userarr = [];
						$userarr['id'] = $user['user_id'];
						$userarr['name'] = $user->user->first_name." ".$user->user->last_name;
						$inspectors[] = $userarr;
					}
				}
			}
			return ['data'=>$inspectors];
		}
	}

	public function actionUpdate()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			$auditInsplan = AuditPlanInspection::find()->where(['audit_plan_id' => $data['audit_plan_id']])->one();
			if ($auditInsplan !== null)
			{
				if(is_array($data['inspection_plan']) && count($data['inspection_plan'])>0)
				{	
					AuditPlanInspectionPlan::deleteAll(['audit_plan_inspection_id' => $data['audit_plan_id']]);
					foreach ($data['inspection_plan'] as $value)
					{ 
						$model=new AuditPlanInspectionPlan();
						$model->audit_plan_inspection_id=$auditInsplan->id;
						$model->application_unit_id=$value['application_unit_id'];
						$model->activity=$value['activity'];
						//$model->inspector=$value['inspector'];
						$model->date=date('Y-m-d',strtotime($value['date']));
						$model->start_time=$value['start_time'];
						$model->end_time=$value['end_time'];
						$model->person_need_to_be_present=$value['person_need_to_be_present'];
						if($model->validate() && $model->save())
						{
							foreach($value['inspector'] as $inspector)
							{
								$inspectormodel = new AuditPlanInspectionPlanInspector();
								$inspectormodel->inspection_plan_id = $model->id;
								$inspectormodel->user_id = $inspector;
								$inspectormodel->save();
							}
						}
					}
					$responsedata=array('status'=>1,'message'=>'Inspection Plan has been updated successfully');	
				}
			}
			else
			{
				$auditInsplan->audit_plan_id=$data['audit_plan_id'];
				if($auditInsplan->validate() && $auditInsplan->save())
				{  
					if(is_array($data['inspection_plan']) && count($data['inspection_plan'])>0)
					{
						foreach ($data['inspection_plan'] as $value)
						{ 
							$model=new AuditPlanInspectionPlan();
							$model->audit_plan_inspection_id=$auditInsplan->id;
							$model->application_unit_id=$value['application_unit_id'];
							$model->activity=$value['activity'];
							$model->inspector=$value['inspector'];
							$model->date=date('Y-m-d',strtotime($value['date']));
							$model->start_time=$value['start_time'];
							$model->end_time=$value['end_time'];
							$model->person_need_to_be_present=$value['person_need_to_be_present'];
							if($model->validate())
							{
								$model->save();
							}
						}
						$responsedata=array('status'=>1,'message'=>'Inspection Plan has been created successfully');
					}	
				}	
			}
		}
		return $this->asJson($responsedata);
	}

	public function actionDeleteData()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			AuditPlanInspectionPlanInspector::deleteAll(['inspection_plan_id' => $data['id']]);
			$model = AuditPlanInspectionPlan::deleteAll(['id' => $data['id']]);
			$responsedata=array('status'=>1,'message'=>'Deleted successfully');
		}
		return $this->asJson($responsedata);
	}

	public function actionGenerate()
    {
		$auditInsplan=new AuditPlanInspection();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		if ($data) 
		{
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			header('Access-Control-Max-Age: 1000');
			header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

			$html='';
			$mpdf = new \Mpdf\Mpdf();
			$followup_audit_type = 1;
			if(isset($data['inspectionplan_id']) && $data['inspectionplan_id']){
				$AuditPlanInspection=AuditPlanInspection::find()->where(['id'=> $data['inspectionplan_id'] ])->one();
				if($AuditPlanInspection !== null){
					$followup_audit_type = $AuditPlanInspection->audit_type;
				}
			}
			


			$auditmodel = Audit::find()->where(['id' => $data['audit_id']])->one();
			$auditplan = $auditmodel->auditplan;

			$application = $auditmodel->application;

			$type_of_audit = 'Initial Audit';
			$lead_inspector = '';
			$actual_manday = '';
			if($followup_audit_type == 2){
				$auditInspection = $auditplan->followupauditplaninspection;
				$type_of_audit = 'Followup Audit';
				$lead_inspector = $auditplan->followupuser?$auditplan->followupuser->first_name.' '.$auditplan->followupuser->last_name:'';
				$actual_manday = $auditplan->followup_actual_manday;
			}else{
				$auditInspection = $auditplan->auditplaninspection; 
				$lead_inspector = $auditplan->user?$auditplan->user->first_name.' '.$auditplan->user->last_name:'';
				$actual_manday = $auditplan->actual_manday;
			}

			$appStandardArr=array();
			if($auditmodel->audit_type == 2){
				$application = $auditmodel->unannouncedaudit;
				$applicationstd = $application->unannouncedauditstandard;
				if(count($applicationstd)>0)
				{
					foreach($applicationstd as $appstandard)
					{
						$appStandardArr[]=$appstandard->standard->name;
					}
				}
			}else{
				$application = $auditmodel->application;
				$applicationstd = $application->applicationstandard;
				$html = '';
				if(count($applicationstd)>0)
				{
					foreach($applicationstd as $appstandard)
					{
						$appStandardArr[]=$appstandard->standard->name;
					}
				}
			} 
			
			
			$connection = Yii::$app->getDb();
			$command = $connection->createCommand("SELECT GROUP_CONCAT(DISTINCT `date` ORDER BY `date` ASC SEPARATOR ', ') AS dates FROM `tbl_audit_plan_inspection_plan` WHERE audit_plan_inspection_id=$auditInspection->id GROUP BY audit_plan_inspection_id ");
			$result = $command->queryAll();

			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			if(count($result)>0)
			{
				$dates = $result[0]['dates'];
				$datesArr = explode(', ',$dates);
				$newformatdate = [];
				foreach($datesArr as $insdate)
				{
					$newformatdate[] = date($date_format,strtotime($insdate));
				}
				$dates = implode(' | ',$newformatdate);
			}

			if ($auditInsplan !== null)
			{
				$html='
				<style>
				table {
				border-collapse: collapse;
				}

				table, td, th {
				border: 1px solid black;
				}
				
				table.reportDetailLayout {
					border: 1px solid #4e85c8;
					border-collapse: collapse;
					width:100%;
					font-size:12px;
					font-family:Arial;
					text-align: left;
					margin-bottom:5px;
					margin-top:5px;
				}
				td.reportDetailLayout {
					text-align: center;
					border: 1px solid #4e85c8;
					font-size:12px;
					font-family:Arial;
					text-align: left;
					background-color:#DFE8F6;
					padding:3px;
				}
				td.reportDetailLayoutHead {
					text-align: center;
					border: 1px solid #4e85c8;
					font-size:12px;
					font-family:Arial;
					text-align: left;
					/*background-color:#4e85c8;*/
					background-color:#006fc0;
					padding:3px;
					color:#FFFFFF;
				}

				td.reportDetailLayoutInner {
					text-align: center;
					border: 1px solid #4e85c8;
					font-size:12px;
					font-family:Arial;
					text-align: left;
					background-color:#ffffff;
					padding:3px;
				}
				</style>
				<div style="text-align: center;width:20%;display: inline-block;">
					<img src="'.Yii::$app->params['image_files'].'header-img.jpg" border="0">						
				</div>
				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
				<tr>
					<td style="text-align:center;font-weight:bold;width:20%;background:#2f4985;color: white;border: 1px solid #2f4985;" valign="middle" class="reportDetailLayoutInner" colspan="5">INSPECTION PLAN - '.$auditmodel->application->companyname.'</td>
				</tr>';
				/*
				<tr>
					<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Factory Name:</td>
					<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2"></td>
					<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Operator ID:</td>
					<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner"></td>
				</tr>
				*/
			$html.='<tr>
					<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Certification Standard:</td>
					<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.implode(', ',$appStandardArr).'</td>
					<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Type of audit:</td>
					<td style="text-align:left;width:18%;" valign="middle" class="reportDetailLayoutInner">'.$type_of_audit.'</td>
				</tr>
				<tr>
					<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Inspection date(s):</td>
					<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="4">'.$dates.'</td>
					
				</tr>
				<tr>
					<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Lead Inspector:</td>
					<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2">'.$lead_inspector.'</td>
					<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Inspection man-day:</td>
					<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$actual_manday.'</td>
				</tr>';
				/*
				<tr>
					<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Inspector 2:</td>
					<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner" colspan="2"></td>
					<td style="text-align:left;font-weight:bold;width:20%;" valign="middle" class="reportDetailLayoutInner">Insp. 3/GOTS/Translator:</td>
					<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner"></td>
				</tr>
				*/
			$html.='</table>

				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
				<tr>
				    <td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">S.No</td>
					<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Location</td>
					<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Activity</td>
					<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Inspector</td>		
					<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Date</td>	
					<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Start Time</td>
					<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">End Time</td>
					<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Persons need to be present</td>				  
				</tr>';
				$insCint=1;
				$model = AuditPlanInspectionPlan::find()->where(['audit_plan_inspection_id' => $auditInspection->id])->all();
				if ($model !== null)
				{
					foreach($model as $val)
					{
						$inspector_names = '';
						$inspectors = [];
						if(count($val->auditplaninspectionplaninspector)>0)
						{
							foreach($val->auditplaninspectionplaninspector as $inspector)
							{
								$inspectors[] = $inspector->user->first_name." ".$inspector->user->last_name;
							}
							$inspector_names = implode(", ",$inspectors);
						}

						$html.='<tr>
									<td style="text-align:center;" valign="middle" class="reportDetailLayoutInner">'.$insCint.'</td>
									<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$val->applicationunit->name.'</td>
									<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$val['activity'].'</td>
									<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$inspector_names.'</td>
									<td style="text-align:center" valign="middle" class="reportDetailLayoutInner">'.date($date_format,strtotime($val['date'])).'</td>
									<td style="text-align:center" valign="middle" class="reportDetailLayoutInner">'.date('G:i', strtotime($val['start_time'])).'</td>
									<td style="text-align:center" valign="middle" class="reportDetailLayoutInner">'.date('G:i', strtotime($val['end_time'])).'</td>
									<td style="text-align:left" valign="middle" class="reportDetailLayoutInner">'.$val['person_need_to_be_present'].'</td>
								</tr>';
						$insCint++;		
					}
				}
				$html.='</table>';
				
				$mpdf->WriteHTML($html);
				$mpdf->Output('offer.pdf','D');	
			}
		}
	}
	
   
}
