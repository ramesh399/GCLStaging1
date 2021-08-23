<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditPlanReview;
use app\modules\audit\models\AuditPlanReviewer;
use app\modules\audit\models\AuditPlanReviewerTe;
use app\modules\audit\models\AuditPlanReviewChecklistComment;
use app\modules\audit\models\AuditPlanUnitReviewChecklistComment;
use app\modules\master\models\AuditPlanningQuestions;
use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\AuditPlanUnit;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class AuditPlanReviewController extends \yii\rest\Controller
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
	
	

    public function actionCreate()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again later');
		$data = Yii::$app->request->post();
        if ($data) 
		{
			if(!Yii::$app->userrole->isAuditProjectLA($data['audit_id']))
			{
				return $responsedata;
			}
			
			$modelAudit = Audit::find()->where(['id' => $data['audit_id']])->one();
			if ($modelAudit !== null)
			{
				$auditPlanObj = $modelAudit->auditplan;
				if($auditPlanObj)
				{
					$PermissionDenial=false;
					if($modelAudit->followup_status==0 && $modelAudit->status==$modelAudit->arrEnumStatus['review_in_process'])
					{			
						$PermissionDenial=true;
					}elseif($modelAudit->followup_status==1 && $modelAudit->status==$modelAudit->arrEnumStatus['followup_review_in_process']){
						$PermissionDenial=true;
					}
					
					if(!$PermissionDenial)
					{
						return $responsedata;	
					}
				}	
				
				
				$auditID = $modelAudit->id;
				$audit_type = 1;
				if($modelAudit->status == $modelAudit->arrEnumStatus['followup_review_in_process']){
					$auditstatus = $modelAudit->arrEnumStatus['followup_review_completed'];
					$audit_type = 2;
				}else{
					$auditstatus = $modelAudit->arrEnumStatus['review_completed'];
				}

				

				$modelAudit->status = $auditstatus;

				if($modelAudit->save())
				{
					$model = AuditPlan::find()->where(['audit_id' => $auditID])->one();
					if ($model !== null)
					{
						$auditUnitID = $model->id;
						$userData = Yii::$app->userdata->getData();
						/*$auditunitstatus = new AuditPlanUnit;
						$unitmodel = AuditPlanUnit::find()->where(['audit_plan_id' => $auditUnitID])->all();
						if(count($unitmodel)>0)
						{
							foreach($unitmodel as $unitdetail)
							{
								$unitdetail->status = $auditunitstatus->arrEnumStatus['review_completed'];
								$unitdetail->save();
							}
						}
						*/

						//$reviewmodel =new AuditPlanReview();
						
						$reviewmodel = AuditPlanReview::find()->where(['audit_plan_id'=>$model->id,'audit_type'=>$audit_type ])->one();
						if($reviewmodel === null){
							$reviewmodel =new AuditPlanReview();
							$reviewmodel->audit_plan_id=isset($auditUnitID)?$auditUnitID:"";
						}

						
						
						$reviewmodel->user_id=$userData['userid'];
						$reviewmodel->comment=isset($data['comment'])?$data['comment']:"";
						//$reviewmodel->answer=isset($data['answer'])?$data['answer']:"";
						$reviewmodel->status= 2;
						$reviewmodel->audit_type = $audit_type;
						$reviewmodel->review_result=isset($data['review_result_status'])?$data['review_result_status']:"";
						$reviewmodel->created_by=$userData['userid'];

						if($reviewmodel->validate() && $reviewmodel->save())
						{
							$auditplanreviewchecklistcommentObj = $reviewmodel->auditplanreviewchecklistcomment;
							if(count($auditplanreviewchecklistcommentObj)>0)
							{
								foreach($auditplanreviewchecklistcommentObj as $auditplanreviewchecklistcmt)
								{
									$auditplanreviewchecklistcmt->delete();
								}
							}
							$auditplanunitreviewchecklistcommentObj = $reviewmodel->auditplanunitreviewcomment;
							if(count($auditplanunitreviewchecklistcommentObj)>0)
							{
								foreach($auditplanunitreviewchecklistcommentObj as $auditplanunitreviewchecklistcomment)
								{
									$auditplanunitreviewchecklistcomment->delete();
								}
							}


							if(is_array($data['review_comment']) && count($data['review_comment'])>0)
							{
								foreach ($data['review_comment'] as $value)
								{ 
									$reviewcmtmodel=new AuditPlanReviewChecklistComment();
									$reviewcmtmodel->audit_plan_review_id=$reviewmodel->id;
									$reviewcmtmodel->question_id=isset($value['question_id'])?$value['question_id']:"";
									$reviewcmtmodel->question=isset($value['question'])?$value['question']:"";
									$reviewcmtmodel->answer=isset($value['answer'])?$value['answer']:"";
									$reviewcmtmodel->comment=isset($value['comment'])?$value['comment']:"";
									$reviewcmtmodel->save();
								}								
							}

							if(is_array($data['unit_review_comment']) && count($data['unit_review_comment'])>0)
							{
								foreach ($data['unit_review_comment'] as $value)
								{ 
									$reviewcmtmodel=new AuditPlanUnitReviewChecklistComment();
									$reviewcmtmodel->audit_plan_review_id=$reviewmodel->id;
									$reviewcmtmodel->unit_id=isset($value['unit_id'])?$value['unit_id']:"";
									$reviewcmtmodel->question_id=isset($value['question_id'])?$value['question_id']:"";
									$reviewcmtmodel->question=isset($value['question'])?$value['question']:"";
									$reviewcmtmodel->answer=isset($value['answer'])?$value['answer']:"";
									$reviewcmtmodel->comment=isset($value['comment'])?$value['comment']:"";
									$reviewcmtmodel->save();
								}
							}
							$responsedata=array('status'=>1,'message'=>'Audit Plan Review has been saved successfully');
						}
					}
				}	
			}				
		}
		//echo 'sdf'; die;
		return $this->asJson($responsedata);
	}
	

	public function actionView()
	{
		$responsedata=array('status'=>0,'message'=>'Review data not found');
		
        if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();
			$userData = Yii::$app->userdata->getData();


			$model = AuditPlanReview::find()->where(['audit_plan_id' => $data['audit_plan_id']])->one();
			if ($model !== null)
			{
				$auditplanreviews=[];
				$reviewarr=[];
				$reviewcommentarr=[];
				$auditplanReview=$model->auditplanreviewchecklistcomment;
				if(count($auditplanReview)>0)
				{
					foreach($auditplanReview as $reviewComment)
					{
						$reviewcommentarr[]=array(
							'audit_plan_review_id'=>$reviewComment->audit_plan_review_id,
							'question_id'=>$reviewComment->question_id,
							'question'=>$reviewComment->question,
							'answer'=>$reviewComment->answer,
							'comment'=>$reviewComment->comment
						);
					}	
				}
				$data['auditplanreviewcomment'] = $reviewcommentarr;

				
				$data['status'] = 1;
				return $data;
			}

		}
		return $responsedata;
	}

	public function actionIndex()
	{
		$responsedata=array('status'=>0,'message'=>'Review data not found');
		
        if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();
			$userData = Yii::$app->userdata->getData();
			$audit_id = $data['audit_id'];
			$reviewmodel =new AuditPlanReview();

			$auditmodel = Audit::find()->where(['id'=>$audit_id])->one();
			$auditreviewAnswer = [];
			$auditreviewcomment = [];
			$auditunitreviewcomment = [];

			if(!Yii::$app->userrole->isAuditProjectLA($audit_id))
			{
				return $responsedata;
			}
			$audit_type = 1;	
			
			if($auditmodel->status == $auditmodel->arrEnumStatus['followup_review_in_process'] ){
				
				$audit_type = 2;

				if($auditmodel->auditplan!==null && $auditmodel->auditplan->followupauditplanreview){
					$auditplanreview = $auditmodel->auditplan->followupauditplanreview;
					
					//$auditreview = $auditmodel->auditreview;
					foreach($auditplanreview->auditplanreviewchecklistcomment as $listcomment){
						$auditreviewcomment[] =[
							'question_id' => $listcomment->question_id,
							'answer' => $listcomment->answer,
							'comment' => $listcomment->comment
						];
					}
					foreach($auditplanreview->auditplanunitreviewcomment as $unitlistcomment){
						$auditunitreviewcomment[$unitlistcomment->unit_id][] =[
							'question_id' => $unitlistcomment->question_id,
							'answer' => $unitlistcomment->answer,
							'comment' => $unitlistcomment->comment
						];
					}
				
				}
				$auditreviewAnswer['auditanswer'] = $auditreviewcomment;
				$auditreviewAnswer['auditunitanswer'] = $auditunitreviewcomment;

				$units = [];
				foreach($auditmodel->auditplan->followupauditplanunit as $appunit){
					//if($appunit->unit_type !='1'){
					$unit = [
						'id' => $appunit->unit_id,
						'name' => $appunit->unitdata->name
					];
					//}
					$units[] = $unit;
				}


				

			}else{
			
				if($auditmodel->auditplan!==null && $auditmodel->auditplan->auditplanreview){
					$auditplanreview = $auditmodel->auditplan->auditplanreview;
					
					//$auditreview = $auditmodel->auditreview;
					foreach($auditplanreview->auditplanreviewchecklistcomment as $listcomment){
						$auditreviewcomment[] =[
							'question_id' => $listcomment->question_id,
							'answer' => $listcomment->answer,
							'comment' => $listcomment->comment
						];
					}
					foreach($auditplanreview->auditplanunitreviewcomment as $unitlistcomment){
						$auditunitreviewcomment[$unitlistcomment->unit_id][] =[
							'question_id' => $unitlistcomment->question_id,
							'answer' => $unitlistcomment->answer,
							'comment' => $unitlistcomment->comment
						];
					}
				
				}
				$auditreviewAnswer['auditanswer'] = $auditreviewcomment;
				$auditreviewAnswer['auditunitanswer'] = $auditunitreviewcomment;

				if($auditmodel->audit_type == $auditmodel->audittypeEnumArr['unannounced_audit'] ){
					$units = Yii::$app->globalfuns->getUnannoucedAuditUnit($audit_id);
				}else{
					$units = [];
					foreach($auditmodel->application->applicationunit as $appunit){
						//if($appunit->unit_type !='1'){
						$unit = [
							'id' => $appunit->id,
							'name' => $appunit->name,
						];
						//}
						$units[] = $unit;
					}
				}


				/*
				$model = AuditPlanningQuestions::find()->where(['status'=>0])->all();
				$qdata = [];
				if (count($model)>0)
				{
					foreach($model as $obj){
						
						$questiondata = ['name'=> $obj->name,'guidance'=> $obj->guidance,'id'=> $obj->id];
						$answerdata = [];
						foreach($obj->riskcategory as $riskcategory){
							$answerdata[$riskcategory->risk_category_id] = $riskcategory->category->name;
						}
						$questiondata['answers'] = $answerdata;
						$qdata[$obj->category][] = $questiondata;
						//$obj->category unit / Application
					}
				}
				*/
			}

			if($auditmodel->audit_type == $auditmodel->audittypeEnumArr['unannounced_audit'] ){
				$audit_type = 3;
			}

			//$model = $model->joinWith('applicationaddress as appaddress');
			$model = AuditPlanningQuestions::find()->alias('t')->joinWith('audittype as audittype')->where(['t.status'=>0,'audittype.audit_type_id'=>$audit_type])->all();
			$qdata = [];
			if (count($model)>0)
			{
				foreach($model as $obj){
					
					$questiondata = ['name'=> $obj->name,'guidance'=> $obj->guidance,'id'=> $obj->id];
					$answerdata = [];
					foreach($obj->riskcategory as $riskcategory){
						$answerdata[$riskcategory->risk_category_id] = $riskcategory->category->name;
					}
					$questiondata['answers'] = $answerdata;
					$qdata[$obj->category][] = $questiondata;
					//$obj->category unit / Application
				}
			}
			$qdata['reviewResult']=$reviewmodel->arrReviewResult;
			$qdata['reviewerstatus']=$reviewmodel->arrReviewerStatus;

			$qdata['units']=$units;
			$responsedata=array('status'=>1,'data'=>$qdata,'answerdata'=>$auditreviewAnswer);
		}
		return $responsedata;
	}

	public function actionChangeReviewer()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
        $data = Yii::$app->request->post();
		if ($data) 
		{
			if(!Yii::$app->userrole->isAdmin())
			{
				return $responsedata;
			}
			
			$userData = Yii::$app->userdata->getData();

			$row = AuditPlanReviewer::find()->where(['audit_plan_id'=>$data['audit_plan_id'],'reviewer_status'=>1])->one();
			if($row!==null)
			{
				$row->reviewer_status = $row->arrEnumStatus['old'];
				$row->save();
			}
			
			$reviewermodel=new AuditPlanReviewer();
			$reviewermodel->audit_plan_id = isset($data['audit_plan_id'])?$data['audit_plan_id']:"";
			$reviewermodel->reviewer_id = isset($data['user_id'])?$data['user_id']:"";
			$reviewermodel->reviewer_status = $reviewermodel->arrEnumStatus['current'];
			$reviewermodel->created_by = $userData['userid'];
			$reviewermodel->created_at = strtotime(date('Y-m-d'));
			$reviewermodel->save();



			$userbsectorcheckdetails = $data['userbsectorcheckdetails'];
			$technicalexpert_ids = isset($data['technicalexpert_ids'])?$data['technicalexpert_ids']:[];
			
			$errorBcode = [];
			if(count($userbsectorcheckdetails)>0){
				foreach($userbsectorcheckdetails as $bcodeid => $chkdetail){
					$error = 0;
					if(count($chkdetail)>0){
						$addedteid = array_intersect($technicalexpert_ids,$chkdetail);
						if(count($addedteid)<=0){
							$error = 1;
						}
					}else{
						$error = 1;
					}
					if($error ==1){
						$bsecname = BusinessSectorGroup::find()->where(['id'=>$bcodeid])->one();
						if($bsecname !== null){
							$errorBcode[] = $bsecname->group_code;
						}
						
					}
				}
			}

			
			if(count($errorBcode)>0){
				$responsedata=array('status'=>0,'message'=>'Please add TE for '.implode(', ', $errorBcode));
			}else{
				foreach($data['technicalexpert_ids'] as $teid){
					$AuditPlanReviewerTe = new AuditPlanReviewerTe();
					$AuditPlanReviewerTe->audit_plan_id = $data['audit_plan_id'];
					$AuditPlanReviewerTe->audit_plan_reviewer_id = $data['user_id'];
					$AuditPlanReviewerTe->technical_expert_id = $teid;
					$AuditPlanReviewerTe->save();
				}

				$responsedata=array('status'=>1,'message'=>'Reviewer has been changed successfully');
			}
			
			
		}
		return $this->asJson($responsedata);
	}


	public function assignReviewer($data)
    {
		if(!Yii::$app->userrole->hasRights(array('audit_review')))
		{
			return false;
		}			
		
		$auditreviewer=new AuditPlanReviewer();		
		if ($data) 
		{		
			$auditreviewerCheck = AuditPlanReviewer::find()->where(['audit_plan_id'=>$data['audit_plan_id'],'reviewer_status'=>1])->one();
			if($auditreviewerCheck === null)
			{
				$AuditPlanUnitStatus = new AuditPlanUnit();
				$auditreviewer->audit_plan_id=$data['audit_plan_id'];
				$auditreviewer->reviewer_id=$data['reviewer_id'];
				$auditreviewer->reviewer_status=1;
				$auditreviewer->created_at = time();
				if($auditreviewer->validate() && $auditreviewer->save())
				{   
					if(isset($data['technicalexpert_ids']) && count($data['technicalexpert_ids'])>0){
						
					}
					

					$auditplan = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
					if($auditplan !== null){
						$auditplan->status = $auditplan->arrEnumStatus['review_in_progress'];
						$auditplan->save();

						$auditplanunit = AuditPlanUnit::find()->where(['audit_plan_id'=>$data['audit_plan_id'],'status'=> $AuditPlanUnitStatus->arrEnumStatus['awaiting_for_unit_lead_auditor_approval'] ])->all();
						if(count($auditplanunit)>0){
							foreach($auditplanunit as $unit){
								$unit->status = $AuditPlanUnitStatus->arrEnumStatus['awaiting_for_reviewer_approval'];
								$unit->save();
							}
						}
					}
					//auditplanunit awaiting_for_reviewer_approval
					//audit plan review_in_progress
					$responsedata=array('status'=>1,'reviewer_id'=>$data['reviewer_id'],'message'=>'Reviewer has been assigned Successfully');
				}
				else
				{
					$responsedata=array('status'=>0,'message'=>$auditreviewer->errors);
				}
			}else{
				$responsedata=array('status'=>0,'message'=>'Reviewer Already Exists');
			}
		}
		return $responsedata;
	}
	
}
