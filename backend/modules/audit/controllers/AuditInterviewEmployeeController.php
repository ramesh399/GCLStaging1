<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\audit\models\AuditReportInterviewEmployees;
use app\modules\audit\models\AuditReportInterviewRequirementReview;
use app\modules\audit\models\AuditReportInterviewRequirementReviewComment;
use app\modules\audit\models\AuditReportInterviewSummary;
use app\modules\master\models\AuditReportCategory;
use app\modules\master\models\AuditReportInterviewSamplingPlan;
use app\modules\application\models\ApplicationUnitManday;
use app\modules\application\models\ApplicationUnit;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditInterviewEmployeeController implements the CRUD actions for Product model.
 */
class AuditInterviewEmployeeController extends \yii\rest\Controller
{
	public $genderArr = ['1'=>'Male','2'=>'Female'];
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
		$post = yii::$app->request->post();
		$data = [];
		$data['audit_id'] = $post['audit_id'];
		$data['unit_id'] = $post['unit_id'];
		$data['checktype'] = isset($post['unitwise'])?$post['unitwise']:'';
		if(!Yii::$app->userrole->canViewAuditReport($data)){
			return false;
		}
		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		$Employeemodel = new AuditReportInterviewEmployees();
		$model = AuditReportInterviewEmployees::find()->where(['audit_id'=>$post['audit_id'],'unit_id'=>$post['unit_id']]);
		

		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm']!='')
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 'name', $searchTerm],
					['like', 'date_format(FROM_UNIXTIME(`created_at` ), \'%b %d, %Y\' )', $searchTerm],
					['like', 'position', $searchTerm],

				]);

				
			}
			$totalCount = $model->count();
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
			}
			else
			{
				$model = $model->orderBy(['created_at' => SORT_DESC]);
			}
			



            //$model = $model->limit($pageSize)->offset($page);
		}
		else
		{
			$totalCount = $model->count();
		}
		
		$employee_list=array();
		
		$model = $model->all();		
		if(count($model)>0)
		{
			
			foreach($model as $value)
			{
				$data=array();
				$data['id']=$value->id;
				$data['audit_id']=$value->audit_id;
				$data['unit_id']=$value->unit_id;
				$data['name']=$value->name;
				$data['gender']=$value->gender;
				$data['gender_label']=$Employeemodel->arrGender[$value->gender];
				$data['migrant']=$value->migrant;
				$data['migrant_label']=$Employeemodel->arrMigrant[$value->migrant];
				$data['position']=$value->position;
				$data['type']=$value->type;
				$data['type_label']=$Employeemodel->arrType[$value->type];
				$data['notes']=$value->notes;
				$data['created_at']=date($date_format,$value->created_at);
				$data['created_by_label']=$value->createdbydata->first_name.' '.$value->createdbydata->last_name;
				$employee_list[]=$data;
			}

			
		}

		return ['employees'=>$employee_list,'total'=>$totalCount];
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

			$arraydata = ['audit_id'=>$data['audit_id'],'unit_id'=>$data['unit_id'],'report_name'=>$data['type']];
			Yii::$app->globalfuns->UpdateApplicableDetails($arraydata);

			if(isset($data['id']))
			{
				$model = AuditReportInterviewEmployees::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new AuditReportInterviewEmployees();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new AuditReportInterviewEmployees();
				$model->created_by = $userData['userid'];
			}

			
			$model->audit_id = $data['audit_id'];
			$model->unit_id = $data['unit_id'];
			$model->name = $data['name'];
			$model->gender = $data['gender'];	
			$model->migrant = $data['migrant'];
			$model->position = $data['position'];
			$model->type=$data['emptype'];
			$model->notes=$data['notes'];
						
			
			if($model->validate() && $model->save())
			{	
				$sumdata = ['audit_id'=>$data['audit_id'],'unit_id'=>$data['unit_id']];
				$this->calulateSummaryDetails($sumdata);
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Interview Employee has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Interview Employee has been created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}


	public function actionGetRequirements()
	{
		$responsedata=array('status'=>0,'message'=>'Question not found');
        if (Yii::$app->request->post()) 
		{
			$result = array();
			$data = Yii::$app->request->post();
			$userData = Yii::$app->userdata->getData();
			$question = new AuditReportCategory();

			$pdata = [];
			$pdata['audit_id'] = $data['audit_id'];
			$pdata['unit_id'] = $data['unit_id'];
			$pdata['checktype'] = 'unitwise';
			if(!Yii::$app->userrole->canViewAuditReport($pdata)){
				return false;
			}

			$model = AuditReportInterviewRequirementReview::find()->where(['audit_id' => $data['audit_id'],'unit_id' => $data['unit_id']])->orderBy(['id' => SORT_DESC])->one();
			if ($model !== null)
			{
				
				$applicationreviews=[];
				$reviewarr=[];
				$reviewcommentarr=[];
				$appReview=$model->requirementreviewcomment;
				if(count($appReview)>0)
				{
					foreach($appReview as $reviewComment)
					{
						$reviewcommentarr[]=array(
							'requirement_review_id'=>$reviewComment->requirement_review_id,
							'client_information_question_id'=>$reviewComment->client_information_question_id,
							'question'=>$reviewComment->question,
							'answer'=>$reviewComment->answer,
							'comment'=>$reviewComment->comment
						);
					}	
				}
				$result['requirements'] = $reviewcommentarr;
				$result['answer'] = $question->arrAnswer;
				
				$result['status'] = 1;
				return $result;
			}


		}
		//return $responsedata;
	}


	public function actionGetAnswer()
	{
		$result = array();
		$responsedata=array('status'=>0,'message'=>'Review data not found');
        if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();
			$userData = Yii::$app->userdata->getData();

			$pdata = [];
			$pdata['audit_id'] = $data['audit_id'];
			$pdata['unit_id'] = $data['unit_id'];
			$pdata['checktype'] = 'unitwise';
			if(!Yii::$app->userrole->canViewAuditReport($pdata)){
				return false;
			}
			$model = AuditReportInterviewRequirementReview::find()->where(['audit_id' => $data['audit_id'],'unit_id' => $data['unit_id']])->orderBy(['id' => SORT_DESC])->one();
			if ($model !== null)
			{
				
				$applicationreviews=[];
				$reviewarr=[];
				$reviewcommentarr=[];
				$appReview=$model->requirementreviewcomment;
				if(count($appReview)>0)
				{
					foreach($appReview as $reviewComment)
					{
						$reviewcommentarr[]=array(
							'requirement_review_id'=>$reviewComment->requirement_review_id,
							'client_information_question_id'=>$reviewComment->client_information_question_id,
							'answer'=>$reviewComment->answer,
							'comment'=>$reviewComment->comment
						);
					}	
				}
				$result['requirementcomment'] = $reviewcommentarr;

				
				$result['status'] = 1;
				return $result;
			}

		}
		//return $responsedata;
	}


	public function actionSaveInterviewChecklist()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again later');
		$userData = Yii::$app->userdata->getData();
		$data = Yii::$app->request->post();

        if ($data) 
		{
			$pdata = [];
			$pdata['audit_id'] = $data['audit_id'];
			$pdata['unit_id'] = $data['unit_id'];
			$pdata['checktype'] = 'unitwise';
			if(!Yii::$app->userrole->canEditAuditReport($pdata)){
				return false;
			}

			$arraydata = ['audit_id'=>$data['audit_id'],'unit_id'=>$data['unit_id'],'report_name'=>$data['type']];
			Yii::$app->globalfuns->UpdateApplicableDetails($arraydata);

			$model = AuditReportInterviewRequirementReview::find()->where(['audit_id' => $data['audit_id'],'unit_id' => $data['unit_id']])->one();
			if($model===null)
			{
				$model = new AuditReportInterviewRequirementReview();
				$model->created_by = $userData['userid'];
			}
			else
			{
				$model->updated_by = $userData['userid'];
				AuditReportInterviewRequirementReviewComment::deleteAll(['requirement_review_id' => $model->id]);
			}

			$model->audit_id = $data['audit_id'];
			$model->unit_id = $data['unit_id'];
			if($model->validate() && $model->save())
			{
				if(is_array($data['checklistdata']) && count($data['checklistdata'])>0)
				{
					foreach ($data['checklistdata'] as $value)
					{ 
						$reviewcmtmodel = new AuditReportInterviewRequirementReviewComment();
						$reviewcmtmodel->requirement_review_id = $model->id;
						$reviewcmtmodel->client_information_question_id = $value['question_id'];
						$reviewcmtmodel->question = $value['question'];
						$reviewcmtmodel->answer = $value['answer'];
						$reviewcmtmodel->comment = $value['comment'];
						$reviewcmtmodel->save();
					}
				}
				$responsedata=array('status'=>1,'message'=>'Interview Requirement has been saved successfully');
			}
		}
		return $this->asJson($responsedata);
	}


	public function actionOptionlist()
	{
		$modelObj = new AuditReportInterviewEmployees();
		return ['genderlist'=>$modelObj->arrGender,'typelist'=>$modelObj->arrType,'migrantlist'=>$modelObj->arrMigrant];
	}



	public function actionDeleteEmployee()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			

			$modelsummary = AuditReportInterviewEmployees::find()->where(['id'=> $data['id'] ])->one();
			if($modelsummary !== null){
				$audit_id = $modelsummary->audit_id;
				$unit_id = $modelsummary->unit_id;

				$pdata = [];
				$pdata['audit_id'] = $audit_id;
				$pdata['unit_id'] = $unit_id;
				$pdata['checktype'] = 'unitwise';
				if(!Yii::$app->userrole->canEditAuditReport($pdata)){
					return false;
				}


				$model = AuditReportInterviewEmployees::deleteAll(['id' => $data['id']]);

				$sumdata = ['audit_id'=>$audit_id,'unit_id'=>$unit_id];
				$this->calulateSummaryDetails($sumdata);
				$responsedata=array('status'=>1,'message'=>'Deleted successfully');
			}				
		}
		return $this->asJson($responsedata);
	}
	
	
	public function calulateSummaryDetails($data){
		$connection = Yii::$app->getDb();
		//$model = AuditReportInterviewEmployees::find()->where(['audit_id' => $data['audit_id'],'unit_id' => $data['unit_id']])->all();
		$audit_id = $data['audit_id'];
		$unit_id = $data['unit_id'];

		$totalgender = 0;
		/*
		$commandt = $connection->createCommand("SELECT COUNT(*) as totalgender FROM `tbl_audit_report_interview_employees` WHERE 
		audit_id='".$audit_id."' AND unit_id='".$unit_id."' ");
		$resulttot = $commandt->queryOne();
		if($resulttot!==false){
			$totalgender = $resulttot['totalgender'];
		}

		$total_employees_interviewed = $this->getTotalEmployeeInterviewed($unit_id);

		$command = $connection->createCommand("SELECT COUNT(*) as totalgender,gender FROM `tbl_audit_report_interview_employees` WHERE 
		audit_id='".$audit_id."' AND unit_id='".$unit_id."' GROUP BY `gender`");
		$result = $command->queryAll();
		*/

		$commandt = $connection->createCommand("SELECT sum(total_employees) as totalgender FROM `tbl_audit_report_interview_summary` WHERE 
		audit_id='".$audit_id."' AND unit_id='".$unit_id."' ");
		$resulttot = $commandt->queryOne();
		if($resulttot!==false){
			$totalgender = $resulttot['totalgender'];
		}

		$total_employees_interviewed = $this->getTotalEmployeeInterviewed($unit_id);

		$command = $connection->createCommand("SELECT sum(total_employees) as totalgender,gender FROM `tbl_audit_report_interview_summary` WHERE 
		audit_id='".$audit_id."' AND unit_id='".$unit_id."' GROUP BY `gender`");
		$result = $command->queryAll();


		//tbl_audit_report_interview_summary
		//AuditReportInterviewSummary::deleteAll(['audit_id' => $data['audit_id'],'unit_id' => $data['unit_id']]);
		
		if(count($result)>0){
			foreach($result as $genderdata){
				$totalgenderss = 0;
				$commandgg = $connection->createCommand("SELECT COUNT(*) as totalgender FROM `tbl_audit_report_interview_employees` WHERE 
				audit_id='".$audit_id."' AND unit_id='".$unit_id."' and gender=".$genderdata['gender']."  ");
				$resultgg = $commandgg->queryOne();
				if($resultgg!==false){
					$totalgenderss = $resultgg['totalgender'];
				}
				if( $genderdata['totalgender']<=0 ||  $genderdata['totalgender']==''){
					$calgender = 0;
				}else{
					$calgender = $genderdata['totalgender']/$totalgender;
				}
				

				$AuditReportInterviewSummary = AuditReportInterviewSummary::find()->where(['gender'=>$genderdata['gender'], 'audit_id' => $data['audit_id'],'unit_id' => $data['unit_id']])->one();
				if($AuditReportInterviewSummary === null){
					$AuditReportInterviewSummary = new AuditReportInterviewSummary();
					//$AuditReportInterviewSummary->no_of_sampled_employees = 0;
					$AuditReportInterviewSummary->total_employees = 0;
				}

				$emppercent = $calgender*100;
				$AuditReportInterviewSummary->audit_id = $audit_id;
				$AuditReportInterviewSummary->unit_id = $unit_id;
				$AuditReportInterviewSummary->gender = $genderdata['gender'];
				//$AuditReportInterviewSummary->total_employees = $genderdata['totalgender'];
				$AuditReportInterviewSummary->total_employee_percentage =round($emppercent);
				$AuditReportInterviewSummary->to_be_sampled_employees = round(($emppercent*$total_employees_interviewed)/100);
				//$AuditReportInterviewSummary->no_of_sampled_employees = 0;
				$AuditReportInterviewSummary->no_of_sampled_employees = $totalgenderss;//$genderdata['totalgender'];
				
				$AuditReportInterviewSummary->save();
			}
		}
		return true;
	}

	public function getTotalEmployeeInterviewed($unit_id){
		$ApplicationUnitManday = ApplicationUnitManday::find()->where(['unit_id'=>$unit_id])->one();
		$adjusted_manday=0;
		if($ApplicationUnitManday !== null){
			$adjusted_manday = $ApplicationUnitManday->final_manday;
		}
		$total_employees_interviewed = 0;
		$AuditReportInterviewSamplingPlan = AuditReportInterviewSamplingPlan::find()->where(['status'=>0])->andWhere(['>=', 'audit_man_days',$adjusted_manday])->one();
		if($AuditReportInterviewSamplingPlan!==null){
			$total_employees_interviewed = $AuditReportInterviewSamplingPlan->total_employees_interviewed;
		}
		return $total_employees_interviewed;
	}
	public function actionGetSummarydetails()
	{
		$result = array();
		$responsedata=array('status'=>0,'message'=>'Review data not found');
        if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();
			$pdata = [];
			$pdata['audit_id'] = $data['audit_id'];
			$pdata['unit_id'] = $data['unit_id'];
			$pdata['checktype'] = 'unitwise';
			if(!Yii::$app->userrole->canViewAuditReport($pdata)){
				return false;
			}

			$userData = Yii::$app->userdata->getData();
			$sampleplan = [];
            $noofemployeesdata='';
			$appunitmodel = ApplicationUnit::find()->select('no_of_employees')->where(['id'=>$data['unit_id']])->one();

			$noOfEmployees = $appunitmodel->no_of_employees;
			$AuditReportInterviewSamplingPlan = AuditReportInterviewSamplingPlan::find()->where(['status'=>0])->all();
			if(count($AuditReportInterviewSamplingPlan)>0){
				foreach($AuditReportInterviewSamplingPlan as $sampplanobj){

					if($noOfEmployees!==null && $noOfEmployees!='' && ($sampplanobj->no_of_employees_from <= $noOfEmployees && $sampplanobj->no_of_employees_to >= $noOfEmployees)){

						$noofemployeesdata = $sampplanobj->total_employees_interviewed;
					}
					$sampleplan[] = [
						// 'audit_man_days' => $sampplanobj->audit_man_days,
						'no_of_employees_from' => $sampplanobj->no_of_employees_from,
						'no_of_employees_to' => $sampplanobj->no_of_employees_to,
						'total_employees_interviewed' => $sampplanobj->total_employees_interviewed,
						'records_checked_per_month' => $sampplanobj->records_checked_per_month,
						'time_spent_on_interviews' => $sampplanobj->time_spent_on_interviews
					];
				}
			}

			//$AuditReportInterviewSamplingPlan = AuditReportInterviewSamplingPlan::find()->where(['status'=>0])->all();
			//adjusted_manday
			
			//--------------enable with audit manday calculated no of employees interviewed--------

			$total_employees_interviewed = $this->getTotalEmployeeInterviewed($data['unit_id']);

			//-------------------------------------------------------------------------------------
			//$adjusted_manday
			$model = AuditReportInterviewSummary::find()->where(['audit_id' => $data['audit_id'],'unit_id' => $data['unit_id']])->orderBy(['gender' => SORT_ASC])->all();
			$totalemp = 0;
			$totalemppercentage = 0;
			$tobesampemp = 0;
			$noofsampemp = 0;
			$summarydataarr = [];
			if(count($model)<=0)
			{
				
				foreach($this->genderArr as $key=>$gvalue){
					
					$AuditReportInterviewSummary = new AuditReportInterviewSummary();
					$AuditReportInterviewSummary->audit_id = $data['audit_id'];
					$AuditReportInterviewSummary->unit_id = $data['unit_id'];
					$AuditReportInterviewSummary->gender = $key;
					$AuditReportInterviewSummary->total_employees = 0;
					$AuditReportInterviewSummary->total_employee_percentage = 0;
					$AuditReportInterviewSummary->to_be_sampled_employees = 0;
					$AuditReportInterviewSummary->no_of_sampled_employees = 0;
					$AuditReportInterviewSummary->save();
				}
				$model = AuditReportInterviewSummary::find()->where(['audit_id' => $data['audit_id'],'unit_id' => $data['unit_id']])->orderBy(['gender' => SORT_ASC])->all();
			}
			if(count($model)>0)
			{
				foreach($model as $summarydata)
				{
					$totalemp += $summarydata->total_employees;
					$totalemppercentage += $summarydata->total_employee_percentage;
					$tobesampemp += $summarydata->to_be_sampled_employees;
					$noofsampemp += $summarydata->no_of_sampled_employees;
					$summarydataarr[]=array(
						'id'=>$summarydata->id,
						'gender'=>$summarydata->gender,
						'gender_name'=>$this->genderArr[$summarydata->gender],
						'total_employees'=>$summarydata->total_employees,
						'total_employee_percentage'=>$summarydata->total_employee_percentage,
						'to_be_sampled_employees' => $summarydata->to_be_sampled_employees,
						'no_of_sampled_employees' => $summarydata->no_of_sampled_employees
					);
				}	

				$totalDetails = [
					'total_employees'=>$totalemp,
					'total_employee_percentage'=>$totalemppercentage,
					'to_be_sampled_employees' => $tobesampemp,
					'no_of_sampled_employees' => $noofsampemp
				];

				$responsedata = ['status'=>1, 'summarydetails'=>$summarydataarr, 'totalDetails'=>$totalDetails  ];
			}else{
				$summarydataarr[]=array(
					'gender'=>'',
					'gender_name'=>'Male',
					'total_employees'=>$summarydata->total_employees,
					'total_employee_percentage'=>$summarydata->total_employee_percentage,
					'to_be_sampled_employees' => $summarydata->to_be_sampled_employees,
					'no_of_sampled_employees' => $summarydata->no_of_sampled_employees
				);
				$totalDetails = [
					'total_employees'=>0,
					'total_employee_percentage'=>0,
					'to_be_sampled_employees' => 0,
					'no_of_sampled_employees' => 0
				];
				//$responsedata = ['status'=>1, 'summarydetails'=>$summarydataarr, 'totalDetails'=>$totalDetails  ];
				$responsedata = ['status'=>1, 'summarydetails'=>[],'totalDetails'=>''  ];
			}
			
			$responsedata['total_employees_interviewed'] = $noofemployeesdata; //$total_employees_interviewed;
			$responsedata['sampleplan'] = $sampleplan;

		}
		return $responsedata;
	}
	
	public function actionSaveSummarydetails()
	{
		$result = array();
		$responsedata=array('status'=>0,'message'=>'Something went wrong');
        if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();
			$pdata = [];
			$pdata['audit_id'] = $data['audit_id'];
			$pdata['unit_id'] = $data['unit_id'];
			$pdata['checktype'] = 'unitwise';
			if(!Yii::$app->userrole->canEditAuditReport($pdata)){
				return false;
			}

			$userData = Yii::$app->userdata->getData();
			
			//$sampleplan = [];
			$sampledemployees = $data['sampledemployees'];
			if(count($sampledemployees) >0){
				foreach($sampledemployees as $employeedata){
					$AuditReportInterviewSamplingPlan = AuditReportInterviewSummary::find()->where(['id'=>$employeedata['id']])->one();
					if($AuditReportInterviewSamplingPlan !== null){
						//$AuditReportInterviewSamplingPlan->no_of_sampled_employees = $employeedata['answer'];
						$AuditReportInterviewSamplingPlan->total_employees = $employeedata['answer'];

						//$AuditReportInterviewSummary->total_employee_percentage = $calgender*100;
						//$AuditReportInterviewSummary->to_be_sampled_employees = ceil(($calgender/$total_employees_interviewed)*100);

						$AuditReportInterviewSamplingPlan->save();
					}
				}
			}
			$calData = ['audit_id'=>$data['audit_id'],'unit_id'=>$data['unit_id'] ];
			$this->calulateSummaryDetails($calData);
			$responsedata=array('status'=>1,'message'=>'No. Sampled Employees has been Updated Successfully');
		}
		return $responsedata;
	}
}
