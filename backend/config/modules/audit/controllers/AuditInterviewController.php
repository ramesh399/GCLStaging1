<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\audit\models\AuditReportInterview;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditInterviewController implements the CRUD actions for Product model.
 */
class AuditInterviewController extends \yii\rest\Controller
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
		
		$interviewmodel = new AuditReportInterview();
		$model = AuditReportInterview::find()->alias('t')->where(['t.audit_id'=>$post['audit_id']]);
		//$model->where(['t.audit_id'=>]);
		$model->joinWith(['process as pro']);

		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm']!='')
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 't.total_employees', $searchTerm],
					['like', 'date_format(FROM_UNIXTIME(t.`created_at` ), \'%b %d, %Y\' )', $searchTerm],
					['like', 'pro.name', $searchTerm],

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
			



            $model = $model->limit($pageSize)->offset($page);
		}
		else
		{
			$totalCount = $model->count();
		}
		
		$interview_list=array();
		
		$model = $model->all();		
		if(count($model)>0)
		{
			
			foreach($model as $value)
			{
				$data=array();
				$data['id']=$value->id;
				$data['audit_id']=$value->audit_id;
				$data['process_id']=$value->process_id;
				$data['process_name']=$value->process->name;
				$data['number_of_male']=$value->number_of_male;
				$data['number_of_female']=$value->number_of_female;
				$data['number_of_transgender']=$value->number_of_transgender;
				$data['total_employees']=$value->total_employees;
				$data['created_at']=date($date_format,$value->created_at);
				$data['created_by_label']=$value->createdbydata->first_name.' '.$value->createdbydata->last_name;
				$interview_list[]=$data;
			}

			
		}

		return ['interviews'=>$interview_list,'total'=>$totalCount];
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
				$model = AuditReportInterview::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new AuditReportInterview();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new AuditReportInterview();
				$model->created_by = $userData['userid'];
			}

			
			$model->audit_id = $data['audit_id'];
			$model->process_id = $data['process_id'];
			$model->number_of_male = $data['number_of_male'];	
			$model->number_of_female = $data['number_of_female'];
			$model->number_of_transgender = $data['number_of_transgender'];
			
			$model->total_employees = $data['number_of_male'] + $data['number_of_female'] + $data['number_of_transgender'];
			
			
			if($model->validate() && $model->save())
			{	
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Interview has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Interview has been created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}

	public function actionGetSummary()
	{
		$data = yii::$app->request->post();
		if($data)
		{
			$model = AuditReportInterview::find()->where(['audit_id'=>$data['audit_id']])->all();
			if(count($model)>0)
			{
				$total_male = 0;
				$total_female = 0;
				$total_tgender = 0;
				$total_employee = 0;
				foreach($model as $value)
				{
					$total_male+=$value->number_of_male;
					$total_female+=$value->number_of_female;
					$total_tgender+=$value->number_of_transgender;
					$total_employee+=$value->total_employees;
				}

				$percent_male = $total_male/$total_employee;
				$percent_female = $total_female/$total_employee;
				$percent_tgender = $total_tgender/$total_employee;
				$percent_total = $percent_male + $percent_female + $percent_tgender;

				$summary_data = array('total_male'=>$total_male,'total_female'=>$total_female, 'total_tgender'=>$total_tgender, 'total_employee'=>$total_employee, 'male_percent'=>$percent_male, 'female_percent'=>$percent_female, 'transgender_percent'=>$percent_tgender, 'percent_total'=>$percent_total);
			}
			return ['summary'=>$summary_data];
		}
		
	}



	public function actionDeleteInterview()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$model = AuditReportInterview::deleteAll(['id' => $data['id']]);
			$responsedata=array('status'=>1,'message'=>'Deleted successfully');
		}
		return $this->asJson($responsedata);
	}
	
	
	
	
	
	
}
