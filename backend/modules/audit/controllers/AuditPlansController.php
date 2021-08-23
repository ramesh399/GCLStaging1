<?php
namespace app\modules\audit\controllers;

use Yii;

use yii\web\NotFoundHttpException;

use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\AuditPlanUnit;
use app\modules\audit\models\AuditPlanUnitDate;
use app\modules\audit\models\AuditPlanUnitAuditor;
use app\modules\audit\models\AuditPlanUnitStandard;
use app\modules\audit\models\AuditPlanUnitAuditorDate;
use app\modules\audit\models\AuditPlanUnitStandardAuditor;
use app\modules\audit\models\AuditPlanInspection;
use app\modules\audit\models\AuditPlanInspectionPlan;

use app\modules\audit\models\AuditPlanHistory;
use app\modules\audit\models\AuditPlanUnitHistory;
use app\modules\audit\models\AuditPlanUnitDateHistory;
use app\modules\audit\models\AuditPlanUnitStandardHistory;
use app\modules\audit\models\AuditPlanUnitAuditorHistory;
use app\modules\audit\models\AuditPlanUnitAuditorDateHistory;
use app\modules\audit\models\AuditPlanInspectionHistory;
use app\modules\audit\models\AuditPlanInspectionPlanHistory;
use app\modules\audit\models\AuditPlanReviewHistory;
use app\modules\audit\models\AuditPlanReviewChecklistCommentHistory;
use app\modules\audit\models\AuditPlanUnitReviewChecklistCommentHistory;



use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationUnitStandard;

use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;

//use sizeg\jwt\Jwt;
//use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditPlanController implements the CRUD actions for Process model.
 */
class AuditPlansController extends \yii\rest\Controller
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
			//'authenticator' => ['class' => JwtHttpBearerAuth::class ]
		];        
    }
	
	public function actionIndex()
    {
		//---------------Store the Audit Plan related data into history table code start here ----------------------
		$auditObj = Audit::find()->where(['id' => 1])->one();
		if($auditObj!==null)
		{
			$auditPlanObj = $auditObj->auditplan;
			if($auditPlanObj!==null)
			{
				$AuditPlanHistoryModel=new AuditPlanHistory();
				$AuditPlanHistoryModel->audit_id=$auditPlanObj->audit_id;				
				$AuditPlanHistoryModel->application_lead_auditor=$auditPlanObj->application_lead_auditor;
				$AuditPlanHistoryModel->quotation_manday=$auditPlanObj->quotation_manday;
				$AuditPlanHistoryModel->actual_manday=$auditPlanObj->actual_manday;
				$AuditPlanHistoryModel->comment=$auditPlanObj->comment;
				$AuditPlanHistoryModel->created_by=$auditPlanObj->created_by;
				$AuditPlanHistoryModel->created_at=$auditPlanObj->created_at;
				$AuditPlanHistoryModel->updated_by=$auditPlanObj->updated_by;
				$AuditPlanHistoryModel->updated_at=$auditPlanObj->updated_at;
				$AuditPlanHistoryModel->save();	
								
				$AuditPlanHistoryID = $AuditPlanHistoryModel->id;
				
				$auditPlanUnitObj = $auditPlanObj->auditplanunit;
				if(count($auditPlanUnitObj)>0)
				{
					foreach($auditPlanUnitObj as $auditPlanUnit)
					{
						
						$AuditPlanUnitHistoryModel=new AuditPlanUnitHistory();
						$AuditPlanUnitHistoryModel->audit_plan_history_id = $AuditPlanHistoryID;
						$AuditPlanUnitHistoryModel->app_id=$auditPlanUnit->app_id;
						$AuditPlanUnitHistoryModel->unit_id=$auditPlanUnit->unit_id;
						$AuditPlanUnitHistoryModel->unit_lead_auditor=$auditPlanUnit->unit_lead_auditor;
						$AuditPlanUnitHistoryModel->technical_expert=$auditPlanUnit->technical_expert;
						$AuditPlanUnitHistoryModel->translator=$auditPlanUnit->translator;
						$AuditPlanUnitHistoryModel->quotation_manday=$auditPlanUnit->quotation_manday;						
						$AuditPlanUnitHistoryModel->actual_manday=$auditPlanUnit->actual_manday;
						$AuditPlanUnitHistoryModel->status=$auditPlanUnit->status;
						$AuditPlanUnitHistoryModel->save();
						
						$auditPlanUnitHistoryID=$AuditPlanUnitHistoryModel->id;
							
						//Audit Plan  Unit Date
						$auditPlanUnitDatesObj = $auditPlanUnit->auditplanunitdate;
						if(count($auditPlanUnitDatesObj)>0)
						{
							foreach($auditPlanUnitDatesObj as $auditPlanUnitDate)
							{
								$AuditPlanUnitDateHistoryModel=new AuditPlanUnitDateHistory();
								$AuditPlanUnitDateHistoryModel->audit_plan_unit_history_id=$auditPlanUnitHistoryID;
								$AuditPlanUnitDateHistoryModel->date=$auditPlanUnitDate->date;
								$AuditPlanUnitDateHistoryModel->save();
																
								//$auditPlanUnitDate->delete();
							}
						}				

						//Audit Plan  Unit Standard
						$auditPlanUnitStandardsObj = $auditPlanUnit->unitstandard;
						if(count($auditPlanUnitStandardsObj)>0)
						{
							foreach($auditPlanUnitStandardsObj as $auditPlanUnitStandard)
							{
								$AuditPlanUnitStandardHistoryModel=new AuditPlanUnitStandardHistory();
								$AuditPlanUnitStandardHistoryModel->audit_plan_unit_history_id=$auditPlanUnitHistoryID;
								$AuditPlanUnitStandardHistoryModel->standard_id=$auditPlanUnitStandard->standard_id;
								$AuditPlanUnitStandardHistoryModel->save();
								
								//$auditPlanUnitStandard->delete();
							}
						}							
						
						//Audit Plan  Unit Auditors
						$auditPlanUnitAuditorsObj = $auditPlanUnit->unitauditors;
						if(count($auditPlanUnitAuditorsObj)>0)
						{
							foreach($auditPlanUnitAuditorsObj as $auditPlanUnitAuditor)
							{
								$AuditPlanUnitAuditorHistoryModel=new AuditPlanUnitAuditorHistory();
								$AuditPlanUnitAuditorHistoryModel->audit_plan_unit_history_id=$auditPlanUnitHistoryID;
								$AuditPlanUnitAuditorHistoryModel->user_id=$auditPlanUnitAuditor->user_id;	
								$AuditPlanUnitAuditorHistoryModel->is_lead_auditor=$auditPlanUnitAuditor->is_lead_auditor;									
								$AuditPlanUnitAuditorHistoryModel->save();
								
								$auditPlanUnitAuditorHistoryID = $AuditPlanUnitAuditorHistoryModel->id;
								 
								
								//Audit Plan  Unit Auditor Dates
								$auditPlanUnitAuditorDatesObj = $auditPlanUnitAuditor->auditplanunitauditordate;
								if(count($auditPlanUnitAuditorDatesObj)>0)
								{
									foreach($auditPlanUnitAuditorDatesObj as $auditPlanUnitAuditorDate)
									{
										$AuditPlanUnitAuditorDateHistoryModel=new AuditPlanUnitAuditorDateHistory();
										$AuditPlanUnitAuditorDateHistoryModel->audit_plan_unit_auditor_history_id=$auditPlanUnitAuditorHistoryID;
										$AuditPlanUnitAuditorDateHistoryModel->date=$auditPlanUnitAuditorDate->date;
										$AuditPlanUnitAuditorDateHistoryModel->save();
										
										
										//$auditPlanUnitAuditorDate->delete();
									}
								}
								//$auditPlanUnitAuditor->delete();								
							}
						}						
						
						//$auditPlanUnit->delete();
					}					
				}
				
				//Audit Plan Inspection
				$auditPlanInspectionObj = $auditPlanObj->auditplaninspection;
				if($auditPlanInspectionObj!==null)
				{   
					//AuditPlanInspectionHistory  AuditPlanInspectionPlanHistory
					$AuditPlanInspectionHistoryModel=new AuditPlanInspectionHistory();
					$AuditPlanInspectionHistoryModel->audit_plan_history_id = $AuditPlanHistoryID;
					$AuditPlanInspectionHistoryModel->created_by=$auditPlanInspectionObj->created_by;
					$AuditPlanInspectionHistoryModel->created_at=$auditPlanInspectionObj->created_at;
					$AuditPlanInspectionHistoryModel->updated_by=$auditPlanInspectionObj->updated_by;
					$AuditPlanInspectionHistoryModel->updated_at=$auditPlanInspectionObj->updated_at;					
					$AuditPlanInspectionHistoryModel->save();
					$AuditPlanInspectionHistoryID = $AuditPlanInspectionHistoryModel->id;
					
					//Audit Plan Inspection Plan
					$auditPlanInspectionPlanObj = $auditPlanInspectionObj->auditplaninspectionplan;
					if(count($auditPlanInspectionPlanObj)>0)
					{
						foreach($auditPlanInspectionPlanObj as $auditInspectionPlan)
						{				
							$AuditPlanInspectionPlanHistoryModel=new AuditPlanInspectionPlanHistory();
							$AuditPlanInspectionPlanHistoryModel->audit_plan_inspection_history_id = $AuditPlanInspectionHistoryID;
							$AuditPlanInspectionPlanHistoryModel->activity=$auditInspectionPlan->activity;
							$AuditPlanInspectionPlanHistoryModel->inspector=$auditInspectionPlan->inspector;
							$AuditPlanInspectionPlanHistoryModel->date=$auditInspectionPlan->date;
							$AuditPlanInspectionPlanHistoryModel->start_time=$auditInspectionPlan->start_time;
							$AuditPlanInspectionPlanHistoryModel->end_time=$auditInspectionPlan->end_time;
							$AuditPlanInspectionPlanHistoryModel->person_need_to_be_present=$auditInspectionPlan->person_need_to_be_present;							
							$AuditPlanInspectionPlanHistoryModel->save();

							//$auditInspectionPlan->delete();	
						}
					}
					//$auditPlanInspectionObj->delete();	
				}
				
				$auditPlanReviewObj = $auditPlanObj->auditplanreview;
				if($auditPlanReviewObj!==null)
				{
					$AuditPlanReviewHistoryModel=new AuditPlanReviewHistory();
					$AuditPlanReviewHistoryModel->audit_plan_history_id = $AuditPlanHistoryID;
					$AuditPlanReviewHistoryModel->user_id=$auditPlanReviewObj->user_id;
					$AuditPlanReviewHistoryModel->comment=$auditPlanReviewObj->comment;
					$AuditPlanReviewHistoryModel->answer=$auditPlanReviewObj->answer;
					$AuditPlanReviewHistoryModel->status=$auditPlanReviewObj->status;
					$AuditPlanReviewHistoryModel->review_result=$auditPlanReviewObj->review_result;
					$AuditPlanReviewHistoryModel->created_by=$auditPlanReviewObj->created_by;
					$AuditPlanReviewHistoryModel->created_at=$auditPlanReviewObj->created_at;
					$AuditPlanReviewHistoryModel->updated_by=$auditPlanReviewObj->updated_by;
					$AuditPlanReviewHistoryModel->updated_at=$auditPlanReviewObj->updated_at;					
					$AuditPlanReviewHistoryModel->save();
					$AuditPlanReviewHistoryID = $AuditPlanReviewHistoryModel->id;
										
					//Audit Plan Review Checklist Comment
					$auditplanreviewchecklistcommentObj = $auditPlanReviewObj->auditplanreviewchecklistcomment;
					if(count($auditplanreviewchecklistcommentObj)>0)
					{
						foreach($auditplanreviewchecklistcommentObj as $auditplanreviewchecklistcmt)
						{
							$AuditPlanReviewChecklistCommentHistoryModel=new AuditPlanReviewChecklistCommentHistory();
							$AuditPlanReviewChecklistCommentHistoryModel->audit_plan_review_history_id = $AuditPlanReviewHistoryID;
							$AuditPlanReviewChecklistCommentHistoryModel->question_id=$auditplanreviewchecklistcmt->question_id;
							$AuditPlanReviewChecklistCommentHistoryModel->question=$auditplanreviewchecklistcmt->question;
							$AuditPlanReviewChecklistCommentHistoryModel->answer=$auditplanreviewchecklistcmt->answer;
							$AuditPlanReviewChecklistCommentHistoryModel->comment=$auditPlanReviewObj->comment;												
							$AuditPlanReviewChecklistCommentHistoryModel->save();
							
							//$auditplanreviewchecklistcmt->delete();	
						}
					}
					
					//Audit Plan Unit Review Checklist Comment
					$auditplanunitreviewchecklistcommentObj = $auditPlanReviewObj->auditplanunitreviewcomment;
					if(count($auditplanunitreviewchecklistcommentObj)>0)
					{
						foreach($auditplanunitreviewchecklistcommentObj as $auditplanunitreviewchecklistcomment)
						{
							$AuditPlanUnitReviewChecklistCommentHistoryModel=new AuditPlanUnitReviewChecklistCommentHistory();
							$AuditPlanUnitReviewChecklistCommentHistoryModel->audit_plan_review_history_id = $AuditPlanReviewHistoryID;
							$AuditPlanUnitReviewChecklistCommentHistoryModel->unit_id=$auditplanunitreviewchecklistcomment->unit_id;
							$AuditPlanUnitReviewChecklistCommentHistoryModel->question_id=$auditplanunitreviewchecklistcomment->question_id;
							$AuditPlanUnitReviewChecklistCommentHistoryModel->question=$auditplanunitreviewchecklistcomment->question;
							$AuditPlanUnitReviewChecklistCommentHistoryModel->answer=$auditplanunitreviewchecklistcomment->answer;
							$AuditPlanUnitReviewChecklistCommentHistoryModel->comment=$auditplanunitreviewchecklistcomment->comment;												
							$AuditPlanUnitReviewChecklistCommentHistoryModel->save();
							
							//$auditplanunitreviewchecklistcomment->delete();	
						}
					}					
					//$auditPlanReviewObj->delete();	
				}
			}	
		}
		//---------------Store the Audit Plan related data into history table code end here ----------------------
		die();
		
		// $userData = Yii::$app->userdata->getData();
        // $userid=$userData['userid'];
        
        if (Yii::$app->request->post()) 
		{
			$data = yii::$app->request->post();

            $appdata = [];
            $appunits = ApplicationUnit::find()->where(['app_id'=>$data['app_id']])->all();
            
            foreach($appunits as $unit)
            {
                $unitdata = [];
				$unitdata['name']=$unit->name;
				$unitdata['id']=$unit->id;
                $unitdata['address']=$unit->address;
                $unitdata['zipcode']=$unit->zipcode ;
                $unitdata['city']=$unit->city ;
                $unitdata['no_of_employees']=$unit->no_of_employees;
                
                foreach($unit->unitstandard as $standard)
                {
					$standardsarr = [];
					$standardsarr['id'] = $standard->standard_id;
					$standardsarr['name'] = $standard->standard->name;
                    $unitdata['standards'][] = $standardsarr;
                }
                $appdata['units'][] = $unitdata;

            }

            $model = AuditPlan::find()->where(['app_id' => $data['app_id']])->one();

            if ($model !== null)
			{
                $auditplanUnit=$model->auditplanunit;
				if(count($auditplanUnit)>0)
				{
					$unitarr=array();
					foreach($auditplanUnit as $unit)
					{
						$unitsarr=array();
						$unitsarr['unit_id']=$unit->unit_id;
						$unitsarr['original_manday']=$unit->original_manday;
						$unitsarr['actual_manday']=$unit->actual_manday;

						$unitstd=$unit->unitstandard;
						if(count($unitstd)>0)
						{	
							$unitstdarr=array();
							foreach($unitstd as $unitS)
							{
								$stdsarr=array();
								$stdsarr['standard_id']=$unitS->standard_id;
								$stdsarr['from_date']=date('m/d/Y', strtotime($unitS->from_date));
								$stdsarr['to_date']=date('m/d/Y',strtotime($unitS->to_date));
								
								$unitstdauditors=$unitS->unitstandardauditor;
								if(count($unitstdauditors)>0)
								{
									$unitstdaudarr=array();
									$unitstdauditorsarr=array();
									foreach($unitstdauditors as $stdauditors)
									{
										$unitstdaudarr[]=$stdauditors->user->id;	
									}
                                    $stdsarr["auditors"]=$unitstdaudarr;
                                    $stdsarr['lead_auditor_id']=$unitS->lead_auditor_id;
								}
								$unitstdarr[]=$stdsarr;
							}
							$unitsarr["unit_standards"]=$unitstdarr;
						}
                        $unitarr[]=$unitsarr;
					}
					$appdata['auditplanunits'][]=$unitarr;
					
				}
            }



            return $appdata;
        }
	}
	
	public function actionView()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			$model = AuditPlan::find()->where(['id' => $data['id']])->one();
			
			if ($model !== null)
			{
				$resultarr=array();
				$resultarr["id"]=$model->id;
				$resultarr["app_id"]=$model->app_id;
				$resultarr["offer_id"]=$model->offer_id;
				$resultarr["invoice_id"]=$model->invoice_id;
				$resultarr["application_lead_auditor"]=$model->application_lead_auditor;
				$resultarr["quotation_manday"]=$model->quotation_manday;
				$resultarr["actual_manday"]=$model->actual_manday;
				$resultarr["status"]=$model->status;
				$resultarr["created_at"]=date($date_format,$model->created_at);
				$resultarr["company_name"]=$model->application->company_name;
				$resultarr["address"]=$model->application->address;
				$resultarr["zipcode"]=$model->application->zipcode;
				$resultarr["city"]=$model->application->city;
				$resultarr["country_name"]=$model->application->country->name;
				$resultarr["state_name"]=$model->application->state->name;


				$auditplanUnit=$model->auditplanunit;
				if(count($auditplanUnit)>0)
				{
					$unitarr=array();
					foreach($auditplanUnit as $unit)
					{
						$unitsarr=array();
						$unitsarr['id']=$unit->id;
						$unitsarr['unit_id']=$unit->unit_id;
						$unitsarr['unit_name']=$unit->unitdata->name;
						$unitsarr['unit_lead_auditor']=$unit->unitleadauditor->first_name." ".$unit->unitleadauditor->last_name;
						$unitsarr['technical_expert']=$unit->unittechnicalexpert->first_name." ".$unit->unittechnicalexpert->last_name;
						$unitsarr['translator']=$unit->unittranslator->first_name." ".$unit->unittranslator->last_name;

						$unitsarr['quotation_manday']=$unit->quotation_manday;
						$unitsarr['actual_manday']=$unit->actual_manday;
						$unitsarr['status']=$unit->status;

						$unitdate=$unit->auditplanunitdate;
						$unitdatearr=array();
						if(count($unitdate)>0)
						{	
							foreach($unitdate as $unitd)
							{
								$unitdatearr[]=date($date_format,strtotime($unitd->date));
								//echo $date_format.'--'.$unitd->date;
							}
						}
						$unitsarr["date"]=$unitdatearr;

						$unitstd=$unit->unitstandard;
						if(count($unitstd)>0)
						{	
							$unitstdarr=array();
							foreach($unitstd as $unitS)
							{
								$stdsarr=array();
								$stdsarr['id']=$unitS->id;
								$stdsarr['standard_id']=$unitS->standard_id;
								$stdsarr['standard_name']=$unitS->standard->code;
							}
						}
						$unitsarr["standard"]=$unitstdarr;


						$unitauditors=$unit->unitauditors;
						if(count($unitauditors)>0)
						{
							$unitaudarr=array();
							$unitauditorsarr=array();
							$leadauditor=array();
							foreach($unitauditors as $auditors)
							{
								$audarr=array();
								$audarr['id']=$auditors->id;
								$audarr['user_id']=$auditors->user->id;
								$audarr['display_name']=$auditors->user->first_name." ".$auditors->user->last_name;
								$audarr['is_lead_auditor']=$auditors->is_lead_auditor;

								$auditordate=$auditors->auditplanunitauditordate;
								if(count($auditordate)>0)
								{
									$datearr=array();
									foreach($auditordate as $stdauditordate)
									{
										$datearr[]=date($date_format,strtotime($stdauditordate['date']));
									}
									$audarr['date']=$datearr;
								}
								
								$unitaudarr[]=$audarr;
								if($auditors->is_lead_auditor==1)
								{
									$leadauditor[]=$auditors->user->id;
								}
								
								

							}
							
							$unitsarr["auditors"]=$unitaudarr;
						}
						
						$unitarr[]=$unitsarr;
					}
					$resultarr["units"]=$unitarr;
					$resultarr["arrEnumStatus"]=$model->arrEnumStatus;
					
				}

				return $resultarr;
			}
		}
		return $responsedata;
	}

	public function actionChangeStatus(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			$model = AuditPlan::find()->where(['id' => $data['audit_plan_id']])->one();
			
			if ($model !== null)
			{
				if($data['status']){
					$model->status = $data['status'];
					$model->save();
					$responsedata = ['status'=>1,'data'=>['status'=>$data['status']]];	
				}
			}
		}
		return $responsedata;
	}
	
   
}
