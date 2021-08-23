<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\AuditExecutionQuestion;
use app\modules\master\models\AuditExecutionQuestionProcess;
use app\modules\master\models\AuditExecutionQuestionStandard;
use app\modules\master\models\AuditExecutionQuestionFindings;
use app\modules\master\models\AuditExecutionQuestionNonConformity;
use app\modules\master\models\AuditExecutionQuestionBusinessSector;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditExecutionChecklistController implements the CRUD actions for Product model.
 */
class AuditExecutionChecklistController extends \yii\rest\Controller
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
		if(!Yii::$app->userrole->hasRights(array('audit_execution_checklist_master')))
		{
			return false;
		}
		$post = yii::$app->request->post();
		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		$model = AuditExecutionQuestion::find()->alias('t')->where(['<>','t.status',2]);
		$model->innerJoinWith(['subtopic as subtopic']);
		$model = $model->join('left join', 'tbl_audit_execution_question_standard as question_standard','question_standard.audit_execution_question_id = t.id');

		if(isset($post['subtopicFilter']) && $post['subtopicFilter']!='')
		{
			$model = $model->andWhere(['t.sub_topic_id'=> $post['subtopicFilter']]);
		}

		if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
		{
			$model = $model->andWhere(['question_standard.standard_id'=> $post['standardFilter']]);
		}
		
		$model = $model->groupBy(['t.id']);
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 't.name', $searchTerm],
					['like', 'subtopic.name', $searchTerm],
					['like', '(date_format(FROM_UNIXTIME(t.created_at), \'%b %d, %Y\' ))', $searchTerm],
				]);

				$totalCount = $model->count();
			}
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				if($post['sortColumn'] == 'subtopic_name'){
					$model = $model->orderBy(['subtopic.name'=>$sortDirection]);
				}else{
					$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
				}
				
			}
			else
			{
				$model = $model->orderBy(['created_at' => SORT_DESC]);
			}
			



            $model = $model->limit($pageSize)->offset($page);
		}
		else
		{
			$totalCount = $model->count();
		}
		
		$question_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $question)
			{
				$data=array();
				$data['id']=$question->id;
				$data['name']=$question->name;

				$auditexecutionquestionstd = $question->auditexecutionquestionstd;
				if(count($auditexecutionquestionstd)>0)
				{
					$standards_label_arr = array();
					foreach($auditexecutionquestionstd as $val)
					{
						$standards_label_arr[]=$val->standard->code;
					}
					$data['standard_label']=implode(', ',$standards_label_arr);
				}
				else
				{
					$data['standard_label']='NA';
				}

				$data['sub_topic']=$question->subtopic->name;	
				$data['status']=$question->status;
				$data['created_at']=date($date_format,$question->created_at);
				$question_list[]=$data;
			}
		}

		return ['auditExecutionChecklists'=>$question_list,'total'=>$totalCount];
    }

    public function actionCreate()
	{
		if(!Yii::$app->userrole->hasRights(array('add_audit_execution_checklist')))
		{
			return false;
		}
		$model = new AuditExecutionQuestion();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		//print_r($data);exit;
		if ($data) 
		{	
			$model->name = $data['name'];
			$model->interpretation = $data['interpretation'];	
			$model->expected_evidence=$data['expected_evidence'];
			$model->file_upload_required=$data['file_upload_required'];
			$model->positive_finding_default_comment=$data['postiveComment'];
			$model->negative_finding_default_comment=$data['negativeComment'];
			$model->sub_topic_id=$data['sub_topic_id'];
			$userData = Yii::$app->userdata->getData();
			$model->created_by = $userData['userid'];
			$model->status=1;
			if($model->validate() && $model->save())
			{

				if(is_array($data['process']) && count($data['process'])>0)
                {
                    foreach ($data['process'] as $value)
                    { 
						$auditexecutionprocessmodel =  new AuditExecutionQuestionProcess();
						$auditexecutionprocessmodel->audit_execution_question_id = $model->id;
						$auditexecutionprocessmodel->process_id = $value;
						$auditexecutionprocessmodel->save();
					}
				}
				
				if(is_array($data['business_sector']) && count($data['business_sector'])>0)
                {
                    foreach ($data['business_sector'] as $value)
                    { 
						$auditexecutionbsectormodel =  new AuditExecutionQuestionBusinessSector();
						$auditexecutionbsectormodel->audit_execution_question_id = $model->id;
						$auditexecutionbsectormodel->business_sector_id = $value;
						$auditexecutionbsectormodel->save();
					}
				}

				if(is_array($data['severity']) && count($data['severity'])>0)
                {
                    foreach ($data['severity'] as $value)
                    { 
						$auditexecutionseveritymodel =  new AuditExecutionQuestionNonConformity();
						$auditexecutionseveritymodel->audit_execution_question_id = $model->id;
						$auditexecutionseveritymodel->audit_non_conformity_timeline_id = $value;
						$auditexecutionseveritymodel->save();
					}
				}
				
				if(is_array($data['standard_clause']) && count($data['standard_clause'])>0)
                {
                    foreach ($data['standard_clause'] as $value)
                    { 
						$auditexecutionstdmodel =  new AuditExecutionQuestionStandard();
						$auditexecutionstdmodel->audit_execution_question_id = $model->id;
						$auditexecutionstdmodel->standard_id = $value['standard_id'];
						$auditexecutionstdmodel->clause_no = $value['clause_no'];
						$auditexecutionstdmodel->clause = $value['clause'];
						$auditexecutionstdmodel->save();
					}
				}	

				if(is_array($data['findings']) && count($data['findings'])>0)
                {
                    foreach ($data['findings'] as $findings)
                    { 
						$auditexecutionfinmodel =  new AuditExecutionQuestionFindings();
						$auditexecutionfinmodel->audit_execution_question_id = $model->id;
						$auditexecutionfinmodel->question_finding_id = $findings;
						$auditexecutionfinmodel->save();
					}
				}	
				$responsedata=array('status'=>1,'message'=>'Audit Execution Questions has been created successfully');	
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}
		}
		return $this->asJson($responsedata);
	}

    public function actionUpdate()
    {
		if(!Yii::$app->userrole->hasRights(array('edit_audit_execution_checklist')))
		{
			return false;
		}
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$model = AuditExecutionQuestion::find()->where(['id' => $data['id']])->one();
			$model->name = $data['name'];
			$model->interpretation = $data['interpretation'];	
			$model->expected_evidence=$data['expected_evidence'];
			$model->file_upload_required=$data['file_upload_required'];
			$model->positive_finding_default_comment=$data['postiveComment'];
			$model->negative_finding_default_comment=$data['negativeComment'];
			$model->sub_topic_id=$data['sub_topic_id'];
			
			$userData = Yii::$app->userdata->getData();
			$model->updated_by = $userData['userid'];
			
			if($model->validate() && $model->save())
			{

				if(is_array($data['process']) && count($data['process'])>0)
                {
					AuditExecutionQuestionProcess::deleteAll(['audit_execution_question_id' => $model->id]);
                    foreach ($data['process'] as $value)
                    { 
						$auditexecutionprocessmodel =  new AuditExecutionQuestionProcess();
						$auditexecutionprocessmodel->audit_execution_question_id = $model->id;
						$auditexecutionprocessmodel->process_id = $value;
						$auditexecutionprocessmodel->save();
					}
				}

				if(is_array($data['business_sector']) && count($data['business_sector'])>0)
                {
					AuditExecutionQuestionBusinessSector::deleteAll(['audit_execution_question_id' => $model->id]);
                    foreach ($data['business_sector'] as $value)
                    { 
						$auditexecutionbsectormodel =  new AuditExecutionQuestionBusinessSector();
						$auditexecutionbsectormodel->audit_execution_question_id = $model->id;
						$auditexecutionbsectormodel->business_sector_id = $value;
						$auditexecutionbsectormodel->save();
					}
				}

				if(is_array($data['severity']) && count($data['severity'])>0)
                {
					AuditExecutionQuestionNonConformity::deleteAll(['audit_execution_question_id' => $model->id]);
                    foreach ($data['severity'] as $value)
                    { 
						$auditexecutionseveritymodel =  new AuditExecutionQuestionNonConformity();
						$auditexecutionseveritymodel->audit_execution_question_id = $model->id;
						$auditexecutionseveritymodel->audit_non_conformity_timeline_id = $value;
						$auditexecutionseveritymodel->save();
					}
				}

				if(is_array($data['standard_clause']) && count($data['standard_clause'])>0)
                {
					AuditExecutionQuestionStandard::deleteAll(['audit_execution_question_id' => $model->id]);
                    foreach ($data['standard_clause'] as $value)
                    { 
						$auditexecutionstdmodel =  new AuditExecutionQuestionStandard();
						$auditexecutionstdmodel->audit_execution_question_id = $model->id;
						$auditexecutionstdmodel->standard_id = $value['standard_id'];
						$auditexecutionstdmodel->clause_no = $value['clause_no'];
						$auditexecutionstdmodel->clause = $value['clause'];
						$auditexecutionstdmodel->save();
					}
				}
				
				if(is_array($data['findings']) && count($data['findings'])>0)
                {
					AuditExecutionQuestionFindings::deleteAll(['audit_execution_question_id' => $model->id]);
                    foreach ($data['findings'] as $findings)
                    { 
						$auditexecutionfinmodel =  new AuditExecutionQuestionFindings();
						$auditexecutionfinmodel->audit_execution_question_id = $model->id;
						$auditexecutionfinmodel->question_finding_id = $findings;
						$auditexecutionfinmodel->save();
					}
				}	
				$responsedata=array('status'=>1,'message'=>'Audit Execution Questions has been updated successfully');	
			}
		}
		return $this->asJson($responsedata);
    }
	
	public function actionView()
    {
		if(!Yii::$app->userrole->hasRights(array('audit_execution_checklist_master')))
		{
			return false;
		}
		$data = Yii::$app->request->post();
		
        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$resultarr["name"]=$model->name;
			$resultarr["code"]=$model->code;
			$resultarr["interpretation"]=$model->interpretation;
			$resultarr["expected_evidence"]=$model->expected_evidence;
			$resultarr["file_upload_required"]=$model->file_upload_required;
			$resultarr["file_upload_required_label"]=($model->file_upload_required!=1)?"No":"Yes";
			$resultarr["postiveComment"]=$model->positive_finding_default_comment;
			$resultarr["negativeComment"]=$model->negative_finding_default_comment;
			$resultarr["sub_topic_id"]=$model->sub_topic_id;	
			$resultarr["sub_topic"]=$model->subtopic->name;				

			$auditexecutionquestionprocess = $model->auditexecutionquestionprocess;
			if(count($auditexecutionquestionprocess)>0)
			{
				$process_arr = array();
				$process_label_arr = array();
				foreach($auditexecutionquestionprocess as $val)
				{
					$process_arr[]=$val['process_id'];
					$process_label_arr[]=$val->process->name;
				}
				$resultarr["process"]=$process_arr;
				$resultarr["process_label"]=implode(', ',$process_label_arr);
			}

			$auditexecutionquestionbsector = $model->auditexecutionquestionbsector;
			if(count($auditexecutionquestionbsector)>0)
			{
				$bsector_arr = array();
				$bsector_label_arr = array();
				foreach($auditexecutionquestionbsector as $val)
				{
					$bsector_arr[]=$val['business_sector_id'];
					$bsector_label_arr[]=$val->bsector->name;
				}
				$resultarr["bsector"]=$bsector_arr;
				$resultarr["bsector_label"]=implode(', ',$bsector_label_arr);
			}

			$auditexecutionquestionnonconformity = $model->auditexecutionquestionnonconformity;
			if(count($auditexecutionquestionnonconformity)>0)
			{
				$conformity_arr = array();
				$conformity_label_arr = array();
				foreach($auditexecutionquestionnonconformity as $val)
				{
					$conformity_arr[]=$val['audit_non_conformity_timeline_id'];
					$conformity_label_arr[]=$val->noncomformity->name;
				}
				$resultarr["severity"]=$conformity_arr;
				$resultarr["severity_label"]=implode(', ',$conformity_label_arr);
			}
			
			$auditexecutionquestionstd = $model->auditexecutionquestionstd;
			if(count($auditexecutionquestionstd)>0)
			{
				$questionstd_arr = array();
				$stdids = array();
 				foreach($auditexecutionquestionstd as $val)
				{
					$questionstd = array();
					$stdids[]=$val['standard_id'];
					$questionstd['standard_id']=$val['standard_id'];
					$questionstd['standard_name']=$val->standard->name;
					$questionstd['clauseNo']=$val['clause_no'];
					$questionstd['clause']=$val['clause'];
					$questionstd_arr[]=$questionstd;

				}
				$resultarr["stdids"] = $stdids;
				$resultarr["standard"] = $questionstd_arr;
			}

			$auditexecutionquestionfindings = $model->auditexecutionquestionfindings;
			if(count($auditexecutionquestionfindings)>0)
			{
				$questionfindings_arr = array();
				$questionfindings_name_arr = array();
 				foreach($auditexecutionquestionfindings as $findval)
				{
					$questionfindings_arr[]=$findval['question_finding_id'];
					$questionfindings_name_arr[]=($findval['question_finding_id']==1?'Yes':($findval['question_finding_id']==2?'No':'Not Applicable'));
				}
				$resultarr["findings"] = $questionfindings_arr;
				$resultarr["findings_name"] = implode(', ',$questionfindings_name_arr);
			}
			
            return ['data'=>$resultarr];
        }

	}
	
	public function actionCommonUpdate()
	{
		if(!Yii::$app->userrole->hasRights(array('delete_audit_execution_checklist','activate_audit_execution_checklist','deactivate_audit_execution_checklist')))
		{
			return false;
		}
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data && isset($data['status'])) 
		{
			$status = $data['status'];	

			if(!Yii::$app->userrole->canDoCommonUpdate($status,'audit_execution_checklist'))
			{
				return false;
			}	

           	$model = AuditExecutionQuestion::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Audit Execution Question has been activated successfully';
					}elseif($model->status==1){
						$msg='Audit Execution Question has been deactivated successfully';
					}elseif($model->status==2){
						$msg='Audit Execution Question has been deleted successfully';
					}
					$responsedata=array('status'=>1,'message'=>$msg);
				}
				else
				{
					$arrerrors=array();
					$errors=$model->errors;
					if(is_array($errors) && count($errors)>0)
					{
						foreach($errors as $err)
						{
							$arrerrors[]=implode(",",$err);
						}
					}
					$responsedata=array('status'=>0,'message'=>implode(",",$arrerrors));
				}
			}
			else
			{
				$arrerrors=array();
				$errors=$model->errors;
				if(is_array($errors) && count($errors)>0)
				{
					foreach($errors as $err)
					{
						$arrerrors[]=implode(",",$err);
					}
				}
				$responsedata=array('status'=>0,'message'=>$arrerrors);
			}
            return $this->asJson($responsedata);
        }
	}

    

    protected function findModel($id)
    {
        if (($model = AuditExecutionQuestion::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	public function actionRiskCategory()
	{
		$RiskCategory = AuditPlanningRiskCategory::find()->select(['id','name'])->asArray()->all();
		
		return ['riskCategory'=>$RiskCategory];
	}
}
