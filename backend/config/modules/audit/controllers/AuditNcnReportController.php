<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\audit\models\AuditReportNcnReport;
use app\modules\audit\models\Audit;
use app\modules\application\models\ApplicationUnit;
use app\modules\audit\models\AuditPlanUnit;
use app\modules\audit\models\AuditPlan;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * lAuditNcnReportControler implements the CRUD actions for Product model.
 */
class AuditNcnReportController extends \yii\rest\Controller
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
	
	public function actionGetNcn()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		
		if($data)
		{	
			$pdata = [];
			$pdata['audit_id'] = $data['audit_id'];
			$pdata['unit_id'] = $data['unit_id'];
			$pdata['checktype'] = 'unitwise';
			if(!Yii::$app->userrole->canViewAuditReport($pdata)){
				return false;
			}
			
			$unit_id = $data['unit_id'];
			$audit_id = isset($data['audit_id'])?$data['audit_id']:'';
			$auditmodel=new Audit();
			$connection = Yii::$app->getDb();
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			//$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
			$unitWiseFindingsContent= [];
			$audit_plan_id = '';
			$AuditPlanModel = AuditPlan::find()->where(['audit_id'=>$audit_id])->one();
			if($AuditPlanModel!==null){
				$audit_plan_id = $AuditPlanModel->id;
			}
			$AuditPlanUnit = AuditPlanUnit::find()->where(['unit_id'=>$unit_id, 'audit_plan_id'=> $audit_plan_id])->one();
			$unitdetails = '';
			if($AuditPlanUnit!== null){
				$unitauditors = [];
				if(count($AuditPlanUnit->unitauditors)>0){
					foreach($AuditPlanUnit->unitauditors as $uauditors){
						$unitauditors[] = $uauditors->user->first_name.' '.$uauditors->user->last_name;
					}
				}
				$customer_number = Yii::$app->globalfuns->getCustomerNumber($AuditPlanUnit->app_id);
				$unitdetails = [
					'unit_name'=>$AuditPlanUnit->unitdata->name, 'unit_address'=>$AuditPlanUnit->unitdata->address,
					'lead_auditor'=>$AuditPlanUnit->unitleadauditor->first_name.' '.$AuditPlanUnit->unitleadauditor->last_name,
					'operator_id' =>  $customer_number,
					'auditors' => $unitauditors,
					'technical_expert' => $AuditPlanUnit->unittechnicalexpert?$AuditPlanUnit->unittechnicalexpert->first_name.' '.$AuditPlanUnit->unittechnicalexpert->last_name:'',
					'translator' => $AuditPlanUnit->unittranslator?$AuditPlanUnit->unittranslator->first_name.' '.$AuditPlanUnit->unittranslator->last_name:''
				];

				$command = $connection->createCommand("SELECT MIN(unit_date.date) AS start_date,MAX(unit_date.date) AS end_date FROM  `tbl_audit_plan_unit` AS  plan_unit
				INNER JOIN `tbl_audit_plan_unit_date` AS unit_date ON plan_unit.id=unit_date.audit_plan_unit_id  WHERE plan_unit.app_id=".$AuditPlanUnit->app_id." and plan_unit.id=".$AuditPlanUnit->id." ");
				$result = $command->queryOne();
				if($result !== false){
					$start_date = $result['start_date'];
					$end_date = $result['end_date'];
					$unitdetails['start_date'] = date($date_format,strtotime($start_date));
					$unitdetails['end_date'] = date($date_format,strtotime($end_date));
				}

				
				//Starts

				$applicationUnit = $AuditPlanUnit->unitdata;
				$unitName = $applicationUnit->name;
				$unitexecutionObj=$AuditPlanUnit->unitexecution;
				
				if(count($unitexecutionObj)>0)
				{
					
					/*$unitWiseFindingsContent.='										
								<tr>
									<td style="text-align:center;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">S.No</td>
									<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Clause No.</td>	
									<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Clause</td>
									<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Findings</td>		
									<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Severity</td>	
									<td style="text-align:left;font-weight:bold;" valign="middle" class="reportDetailLayoutInner">Finding Type</td>		  
								</tr>';
					*/			
					foreach($unitexecutionObj as $uExecution)
					{								
						$executionlistnoncomformityObj=$uExecution->executionlistnoncomformity;
						if(count($executionlistnoncomformityObj)>0)
						{
							foreach($executionlistnoncomformityObj as $noncomformityList)
							{
								$answer = ($noncomformityList->answer!=1)?'No':'Yes';
								
								$arrStdClause=array();
								$auditexecutioncheckliststandardObj=$noncomformityList->auditexecutioncheckliststandard;
								if(count($auditexecutioncheckliststandardObj)>0)
								{
									foreach($auditexecutioncheckliststandardObj as $auditexecutioncheckliststd)
									{
										$questionstandard=$auditexecutioncheckliststd->auditexecutionquestionstandard;
										if($questionstandard!==null)
										{
											$arrStdClause[]=array('clause_no'=>$questionstandard->clause_no,'clause'=>$questionstandard->clause);
										}
									}
								}
															

								
								//$stdClauseCnt=0;
								$firstClauseNo='';
								$firstClause='';
								$unitWiseFindingsWithClauseContent=[];
								$clausecount=count($arrStdClause);
								foreach($arrStdClause as $vals)
								{
									/*
									if($stdClauseCnt==0)
									{
										$firstClauseNo=$vals['clause_no'];
										$firstClause=$vals['clause'];
									}else{
										//$unitWiseFindingsWithClauseContent.='<tr><td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$vals['clause_no'].'</td><td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$vals['clause'].'</td></tr>';
										$unitWiseFindingsWithClauseContent[] = ['clause_no'=>$vals['clause_no'],'clause'=>$vals['clause']];
									}
									*/
									$unitWiseFindingsWithClauseContent[] = ['clause_no'=>$vals['clause_no'],'clause'=>$vals['clause']];
									//$stdClauseCnt++;
								}

								$root_cause = 'NA';
								$corrective_action = 'NA';
								$la_verification = 'NA';
								$close_date = 'NA';
								if($noncomformityList->checklistremediationlatest){
									$root_cause = $noncomformityList->checklistremediationlatest->root_cause;
									$corrective_action = $noncomformityList->checklistremediationlatest->corrective_action;
									if($noncomformityList->checklistremediationlatest->reviewlatest){
										if($noncomformityList->checklistremediationlatest->reviewlatest->status==1){
											$close_date = date($date_format,$noncomformityList->checklistremediationlatest->reviewlatest->created_at);
										}
									}
									if($noncomformityList->checklistremediationlatest->auditorreviewlatest){
										$la_verification = $noncomformityList->checklistremediationlatest->auditorreviewlatest->comment;
									}
								}
								$unitWiseFindingsContent[] = [
									'rowspan' => $clausecount,
									'unit_name' => $AuditPlanUnit->unitdata->name,
									'clause_content' => $unitWiseFindingsWithClauseContent,
									'finding' => $noncomformityList->finding,
									'severity' => $noncomformityList->auditnonconformitytimeline->name,
									'finding_type' => $noncomformityList->finding_type?$auditmodel->arrFindingType[$noncomformityList->finding_type]:'NA',

									'root_cause' => $root_cause,
									'corrective_action' => $corrective_action,
									'due_by' => $noncomformityList->due_date!=''?date($date_format,strtotime($noncomformityList->due_date)):'',
									'la_verification' => $la_verification,
									'close_date' => $close_date
								];
								/*
								$unitWiseFindingsContent.='										
									<tr>
										<td style="text-align:center;" valign="middle" class="reportDetailLayoutInner" rowspan="'.$clausecount.'">'.$unitSNo.'</td>
										<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$firstClauseNo.'</td>
										<td style="text-align:left;" valign="middle" class="reportDetailLayoutInner">'.$firstClause.'</td>
										<td style="text-align:left" valign="middle" class="reportDetailLayoutInner" rowspan="'.$clausecount.'">'.$noncomformityList->finding.'</td>
										<td style="text-align:center" valign="middle" class="reportDetailLayoutInner" rowspan="'.$clausecount.'">'.$noncomformityList->auditnonconformitytimeline->name.'</td>
										<td style="text-align:center" valign="middle" class="reportDetailLayoutInner"  rowspan="'.$clausecount.'">'.$auditmodel->arrFindingType[$noncomformityList->finding_type].'</td>
									</tr>';
								$unitWiseFindingsContent.=$unitWiseFindingsWithClauseContent;	
								
								$unitSNo++;	
								*/
							}									
						}								
					}
				}
				//Ends
				
				
			}
			$model = AuditReportNcnReport::find()->where(['audit_id' => $data['audit_id']])->andWhere(['unit_id' => $unit_id])->one();
			if($model!==null)
			{
				$responsedata=array('effectiveness_of_corrective_actions'=>$model->effectiveness_of_corrective_actions,'audit_team_recommendation'=>$model->audit_team_recommendation,'measures_for_risk_reduction'=>$model->measures_for_risk_reduction,'summary_of_evidence'=>$model->summary_of_evidence,'potential_high_risk_situations'=>$model->potential_high_risk_situations,'entities_and_processes_visited'=>$model->entities_and_processes_visited,'people_interviewed'=>$model->people_interviewed,'type_of_documents_reviewed'=>$model->type_of_documents_reviewed);
			}
			$responsedata['model']=$model;
			$responsedata['status'] = 1;
			$responsedata['unitdetails'] = $unitdetails;
			$responsedata['unitWiseFindingsContent'] = $unitWiseFindingsContent;
		}
		return $this->asJson($responsedata);
	}


	public function actionCreate()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		
		if($data)
		{	
			$pdata = [];
			$pdata['audit_id'] = $data['audit_id'];
			$pdata['unit_id'] = $data['unit_id'];
			$pdata['checktype'] = 'unitwise';
			if(!Yii::$app->userrole->canEditAuditReport($pdata)){
				return false;
			}
			

			$coulmn = $data['type'];
			if(isset($data['audit_id']) && isset($data['unit_id']))
			{
				$model = AuditReportNcnReport::find()->where(['audit_id' => $data['audit_id']])->andWhere(['unit_id' => $data['unit_id']])->one();
				if($model===null){
					$model = new AuditReportNcnReport();
					$model->created_by = $userData['userid'];
					$model->audit_id = $data['audit_id'];
					$model->unit_id = $data['unit_id'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new AuditReportNcnReport();
				$model->created_by = $userData['userid'];
				$model->audit_id = $data['audit_id'];
				$model->unit_id = $data['unit_id'];
			}

			
			$model->$coulmn = $data['fieldvalue'];
			
			
			
			if($model->validate() && $model->save())
			{	
				
				$responsedata=array('status'=>1,'message'=>'Audit NCN Report has been saved successfully');
				
			}
		}
		
		return $this->asJson($responsedata);
	}


	



	
	
	
	
	
	
	
}
