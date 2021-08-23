<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\AuditReportInterviewSamplingPlan;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditInterviewSamplingplanController implements the CRUD actions for Product model.
 */
class AuditInterviewSamplingplanController extends \yii\rest\Controller
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
		
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];
		$role_chkid=$userData['role_chkid'];
		
		$model = AuditReportInterviewSamplingPlan::find()->where(['<>','status',2]);
		
		
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
			
			$currentAction = 'audit_interview_sampling_plan_master';				
			
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
					['like', 'audit_man_days', $searchTerm],
					['like', 'total_employees_interviewed', $searchTerm],
					['like', 'records_checked_per_month', $searchTerm],
					['like', 'time_spent_on_interviews', $searchTerm],
					['like', 'date_format(FROM_UNIXTIME(`created_at` ), \'%b %d, %Y\' )', $searchTerm],
				]);

				$totalCount = $model->count();
			}
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
			}
			else
			{
				$model = $model->orderBy(['no_of_employees_from' => SORT_ASC]);
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
				$data['audit_man_days']=$question->audit_man_days;
				$data['total_employees_interviewed']=$question->total_employees_interviewed;
				$data['records_checked_per_month']=$question->records_checked_per_month;
				$data['time_spent_on_interviews']=$question->time_spent_on_interviews;
				$data['no_of_employees_from']=$question->no_of_employees_from;
				$data['no_of_employees_to']=$question->no_of_employees_to;
				$data['status']=$question->status;
				$data['created_at']=date($date_format,$question->created_at);
				$data['created_by_label']=$question->createdbydata->first_name.' '.$question->createdbydata->last_name;
				$question_list[]=$data;
			}
		}

		return ['plans'=>$question_list,'total'=>$totalCount];
	}
	
	
    public function actionCreate()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		
		if($data)
		{	
			if(isset($data['id']))
			{
				$model = AuditReportInterviewSamplingPlan::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new AuditReportInterviewSamplingPlan();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new AuditReportInterviewSamplingPlan();
				$model->created_by = $userData['userid'];
			}

			$currentAction = 'add_audit_interview_sampling_plan';
			if(isset($data['id']) && $data['id']!='')
			{
				$currentAction = 'edit_audit_interview_sampling_plan';
			}

			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}

			
			// $model->audit_man_days = $data['audit_man_days'];
			$model->no_of_employees_from = $data['no_of_employees_from'];
			$model->no_of_employees_to = $data['no_of_employees_to'];
			$model->total_employees_interviewed = $data['total_employees_interviewed'];
			$model->records_checked_per_month = $data['records_checked_per_month'];
			$model->time_spent_on_interviews = $data['time_spent_on_interviews'];	
			
			if($model->validate() && $model->save())
			{	
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Audit Interview Sampling Plan has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Audit Interview Sampling Plan has been created successfully');
				}
			}
			else
            {
                $responsedata=array('status'=>0,'message'=>$model->errors);
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
			$currentAction = 'delete_audit_interview_sampling_plan';
		
			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}

			$model = AuditReportInterviewSamplingPlan::deleteAll(['id' => $data['id']]);
			$responsedata=array('status'=>1,'message'=>'Deleted successfully');
		}
		return $this->asJson($responsedata);
	}
	
	
	
	
}
