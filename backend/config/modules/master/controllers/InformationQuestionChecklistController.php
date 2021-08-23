<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\ClientInformationQuestions;
use app\modules\master\models\AuditReviewerFindings;
use app\modules\master\models\ClientInformationQuestionFindings;
use app\modules\master\models\ClientInformationQuestionProcess;
use app\modules\master\models\ClientInformationQuestionStandard;


use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * InformationQuestionChecklistController implements the CRUD actions for Product model.
 */
class InformationQuestionChecklistController extends \yii\rest\Controller
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
		$post = yii::$app->request->post();
		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		
		$model = ClientInformationQuestions::find()->alias('t')->where(['<>','t.status',2]);
		$model = $model->join('left join', 'tbl_audit_report_client_information_question_standard as question_standard','question_standard.audit_report_client_information_question_id = t.id');
		$model = $model->join('left join', 'tbl_standard as std','std.id=question_standard.standard_id');
		

		if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
		{
			$model = $model->andWhere(['question_standard.standard_id'=> $post['standardFilter']]);
		}

		
		
		$model = $model->groupBy(['t.id']);
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
			$currentAction = 'client_information_question_checklist_master';				
			
			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}

            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 't.name', $searchTerm],
					['like', 'std.name', $searchTerm],
					['like', '(date_format(FROM_UNIXTIME(`t`.`created_at` ), \'%b %d, %Y\' ))', $searchTerm],
				]);

				$totalCount = $model->count();
			}
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				$sortColumn = $post['sortColumn'];
				if($sortColumn=='standard_label')
				{
					$sortColumn='std.name';					
				}
				$model = $model->orderBy([$sortColumn=>$sortDirection]);			
				//$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
			}
			else
			{
				$model = $model->orderBy(['t.created_at' => SORT_DESC]);
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
				/*
				$processes=$question->questionprocess;
				if(count($processes)>0)
				{
					$process_label_arr = array();
					foreach($processes as $val)
					{
						$process_label_arr[]=$val->process->name;
					}
					$data["process_label"]=implode(', ',$process_label_arr);
				}
				else
				{
					$data["process_label"]='NA';
				}
				*/

				$standards=$question->questionstandard;
				if(count($standards)>0)
				{
					$standards_label_arr = array();
					foreach($standards as $val)
					{
						$standards_label_arr[]=$val->standard->code;
					}
					$data["standard_label"]=implode(', ',$standards_label_arr);
				}
				else
				{
					$data["standard_label"]='NA';
				}

				$data['status']=$question->status;
				//$data['created_at']=date('M d,Y h:i A',$question->created_at);
				$data['created_at']=date($date_format,$question->created_at);
				$question_list[]=$data;
			}
		}

		return ['informationchecklists'=>$question_list,'total'=>$totalCount];
    }

    public function actionCreate()
	{
		$model = new ClientInformationQuestions();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$currentAction = 'add_client_information_question_checklist';				
			
			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}

			$model->name = $data['name'];
			$model->interpretation = $data['interpretation'];	
			$model->client_information_id = $data['client_information_id'];
			$userData = Yii::$app->userdata->getData();
			$model->created_by = $userData['userid'];
			
			if($model->validate() && $model->save())
			{
				if(is_array($data['riskcategory']) && count($data['riskcategory'])>0)
                {
                    foreach ($data['riskcategory'] as $value)
                    { 
						$qualificationstdmodel =  new ClientInformationQuestionFindings();
						$qualificationstdmodel->audit_report_client_information_question_id = $model->id;
						$qualificationstdmodel->question_finding_id = $value;
						$qualificationstdmodel->save();
					}
				}	
				/*
				if(is_array($data['process_id']) && count($data['process_id'])>0)
                {
                    foreach ($data['process_id'] as $value)
                    { 
						$processmodel =  new ClientInformationQuestionProcess();
						$processmodel->audit_report_client_information_question_id = $model->id;
						$processmodel->process_id = $value;
						$processmodel->save();
					}
				}
				*/
				if(is_array($data['standard_id']) && count($data['standard_id'])>0)
                {
                    foreach ($data['standard_id'] as $value)
                    { 
						$Standardmodel =  new ClientInformationQuestionStandard();
						$Standardmodel->audit_report_client_information_question_id = $model->id;
						$Standardmodel->standard_id = $value;
						$Standardmodel->save();
					}
				}	
				$responsedata=array('status'=>1,'message'=>'Client Information Questions has been created successfully');	
			}
		}
		return $this->asJson($responsedata);
	}

    public function actionUpdate()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$currentAction = 'edit_client_information_question_checklist';				
			
			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}

			$model = ClientInformationQuestions::find()->where(['id' => $data['id']])->one();
			$model->name = $data['name'];
			$model->interpretation = $data['interpretation'];
			$model->client_information_id = $data['client_information_id'];

			$userData = Yii::$app->userdata->getData();
			$model->updated_by = $userData['userid'];
			
			if($model->validate() && $model->save())
			{
				if(is_array($data['riskcategory']) && count($data['riskcategory'])>0)
                {
					ClientInformationQuestionFindings::deleteAll(['audit_report_client_information_question_id' => $model->id]);
                    foreach ($data['riskcategory'] as $value)
                    { 
						$qualificationstdmodel =  new ClientInformationQuestionFindings();
						$qualificationstdmodel->audit_report_client_information_question_id = $model->id;
						$qualificationstdmodel->question_finding_id = $value;
						$qualificationstdmodel->save();
					}
				}
				/*
				if(is_array($data['process_id']) && count($data['process_id'])>0)
                {
					ClientInformationQuestionProcess::deleteAll(['audit_report_client_information_question_id' => $model->id]);
                    foreach ($data['process_id'] as $value)
                    { 
						$processmodel =  new ClientInformationQuestionProcess();
						$processmodel->audit_report_client_information_question_id = $model->id;
						$processmodel->process_id = $value;
						$processmodel->save();
					}
				}
				*/
				if(is_array($data['standard_id']) && count($data['standard_id'])>0)
                {
					ClientInformationQuestionStandard::deleteAll(['audit_report_client_information_question_id' => $model->id]);
                    foreach ($data['standard_id'] as $value)
                    { 
						$Standardmodel =  new ClientInformationQuestionStandard();
						$Standardmodel->audit_report_client_information_question_id = $model->id;
						$Standardmodel->standard_id = $value;
						$Standardmodel->save();
					}
				}	
				$responsedata=array('status'=>1,'message'=>'Client Information Questions has been updated successfully');	
			}
		}
		return $this->asJson($responsedata);
    }
	
	public function actionView()
    {
		$data = Yii::$app->request->post();
		
        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$currentAction = 'client_information_question_checklist_master';				
			
			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}
			
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$resultarr["name"]=$model->name;
			$resultarr["code"]=$model->code;
			$resultarr["interpretation"]=$model->interpretation;
			$resultarr["client_information_id"]=$model->client_information_id;					

			$AuditReviewerFindings=$model->riskcategory;
			if(count($AuditReviewerFindings)>0)
			{
				$riskcategory_arr = array();
				$riskcategory_label_arr = array();
				foreach($AuditReviewerFindings as $val)
				{
					$riskcategory_arr[]=$val['question_finding_id'];
					$riskcategory_label_arr[]=$val->category->name;
				}
				$resultarr["riskcategory"]=$riskcategory_arr;
				$resultarr["risk_category_label"]=implode(', ',$riskcategory_label_arr);
			}
			/*
			$processes=$model->questionprocess;
			if(count($processes)>0)
			{
				$processes_arr = array();
				$processes_label_arr = array();
				foreach($processes as $val)
				{
					$processes_arr[]=$val['process_id'];
					$processes_label_arr[]=$val->process->name;
				}
				$resultarr["process"]=$processes_arr;
				$resultarr["process_label"]=implode(', ',$processes_label_arr);
			}
			*/

			$standards=$model->questionstandard;
			if(count($standards)>0)
			{
				$standards_arr = array();
				$standards_label_arr = array();
				foreach($standards as $vals)
				{
					$standards_arr[]=$vals['standard_id'];
					$standards_label_arr[]=$vals->standard->name;
				}
				$resultarr["standard"]=$standards_arr;
				$resultarr["standard_label"]=implode(', ',$standards_label_arr);
			}
			
			$riskCat = $this->actionRiskCategory();

			$resultarr["riskCategory"]=$riskCat['riskCategory'];
			
            return ['data'=>$resultarr];
        }

	}
	
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data && isset($data['status'])) 
		{
			$status = $data['status'];			
			if(!Yii::$app->userrole->canDoCommonUpdate($status,'client_information_question_checklist'))
			{
				return false;
			}	

           	$model = ClientInformationQuestions::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						
						$msg='Client Information Question has been activated successfully';
					}elseif($model->status==1){
						$msg='Client Information Question has been deactivated successfully';
					}elseif($model->status==2){
						$msg='Client Information Question has been deleted successfully';
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
        if (($model = ClientInformationQuestions::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	public function actionRiskCategory()
	{
		$RiskCategory = AuditReviewerFindings::find()->select(['id','name'])->asArray()->all();
		
		return ['riskCategory'=>$RiskCategory];
	}
}
