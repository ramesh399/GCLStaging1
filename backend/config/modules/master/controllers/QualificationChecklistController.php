<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\QualificationQuestion;
//use app\modules\master\models\QualificationQuestionProcess;
use app\modules\master\models\QualificationQuestionStandard;
use app\modules\master\models\QualificationQuestionRole;
use app\modules\master\models\UserQualificationReviewComment;
use app\modules\master\models\QualificationQuestionBusinessSector;
use app\modules\master\models\QualificationQuestionBusinessSectorGroup;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * QualificationChecklistController implements the CRUD actions for Product model.
 */
class QualificationChecklistController extends \yii\rest\Controller
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
		
		$model = QualificationQuestion::find()->where(['<>','status',2]);
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 'name', $searchTerm],
					['like', '(date_format(FROM_UNIXTIME(`created_at` ), \'%b %d, %Y\' ))', $searchTerm],
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
				$data['status']=$question->status;
				$data['created_at']=date($date_format,$question->created_at);
				$question_list[]=$data;
			}
		}

		return ['qualificationchecklists'=>$question_list,'total'=>$totalCount];
    }

    public function actionCreate()
	{
		$model = new QualificationQuestion();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$model->name = $data['name'];
			$model->guidance = $data['guidance'];
			$model->file_upload_required = $data['file_upload_required'];
			$model->recurring_period = $data['recurring_period'];
			
			$userData = Yii::$app->userdata->getData();
			$model->created_by = $userData['userid'];
			
			if($model->validate() && $model->save())
			{

				if(is_array($data['standard']) && count($data['standard'])>0)
                {
                    foreach ($data['standard'] as $value)
                    { 
						$qualificationstdmodel =  new QualificationQuestionStandard();
						$qualificationstdmodel->qualification_question_id = $model->id;
						$qualificationstdmodel->standard_id = $value;
						$qualificationstdmodel->save();
					}
				}
				
				/*
				if(is_array($data['process']) && count($data['process'])>0)
                {
                    foreach ($data['process'] as $value)
                    { 
						$qualificationprocessmodel =  new QualificationQuestionProcess();
						$qualificationprocessmodel->qualification_question_id = $model->id;
						$qualificationprocessmodel->process_id = $value;
						$qualificationprocessmodel->save();
					}
				}
				*/

				if(is_array($data['role']) && count($data['role'])>0)
                {
                    foreach ($data['role'] as $value)
                    { 
						$qualificationrolemodel =  new QualificationQuestionRole();
						$qualificationrolemodel->qualification_question_id = $model->id;
						$qualificationrolemodel->role_id = $value;
						$qualificationrolemodel->save();
					}
				}

				if(is_array($data['business_sector_id']) && count($data['business_sector_id'])>0)
                {
                    foreach ($data['business_sector_id'] as $value)
                    { 
						$qualificationbsectormodel =  new QualificationQuestionBusinessSector();
						$qualificationbsectormodel->qualification_question_id = $model->id;
						$qualificationbsectormodel->business_sector_id = $value;
						$qualificationbsectormodel->save();
					}
				}

				if(is_array($data['business_sector_group_id']) && count($data['business_sector_group_id'])>0)
                {
                    foreach ($data['business_sector_group_id'] as $value)
                    {
						$qualificationbsectorgrpmodel =  new QualificationQuestionBusinessSectorGroup();
						$qualificationbsectorgrpmodel->qualification_question_id = $model->id;
						$qualificationbsectorgrpmodel->business_sector_group_id = $value;
						$qualificationbsectorgrpmodel->save();
					}
				}

				$responsedata=array('status'=>1,'message'=>'Qualification Questions has been created successfully');	
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
			$model = QualificationQuestion::find()->where(['id' => $data['id']])->one();
			$model->name = $data['name'];
			$model->guidance = $data['guidance'];
			$model->file_upload_required = $data['file_upload_required'];
			$model->recurring_period = $data['recurring_period'];
			
			$userData = Yii::$app->userdata->getData();
			$model->created_by = $userData['userid'];
			
			if($model->validate() && $model->save())
			{

				if(is_array($data['standard']) && count($data['standard'])>0)
                {
					QualificationQuestionStandard::deleteAll(['qualification_question_id' => $model->id]);
                    foreach ($data['standard'] as $value)
                    { 
						$qualificationstdmodel =  new QualificationQuestionStandard();
						$qualificationstdmodel->qualification_question_id = $model->id;
						$qualificationstdmodel->standard_id = $value;
						$qualificationstdmodel->save();
					}
				}
				
				/*
				if(is_array($data['process']) && count($data['process'])>0)
                {
					QualificationQuestionProcess::deleteAll(['qualification_question_id' => $model->id]);
                    foreach ($data['process'] as $value)
                    { 
						$qualificationprocessmodel =  new QualificationQuestionProcess();
						$qualificationprocessmodel->qualification_question_id = $model->id;
						$qualificationprocessmodel->process_id = $value;
						$qualificationprocessmodel->save();
					}
				}
				*/

				if(is_array($data['role']) && count($data['role'])>0)
                {
					QualificationQuestionRole::deleteAll(['qualification_question_id' => $model->id]);
                    foreach ($data['role'] as $value)
                    { 
						$qualificationrolemodel =  new QualificationQuestionRole();
						$qualificationrolemodel->qualification_question_id = $model->id;
						$qualificationrolemodel->role_id = $value;
						$qualificationrolemodel->save();
					}
				}

				if(is_array($data['business_sector_id']) && count($data['business_sector_id'])>0)
                {	
					QualificationQuestionBusinessSector::deleteAll(['qualification_question_id' => $model->id]);
                    foreach ($data['business_sector_id'] as $value)
                    { 
						$qualificationbsectormodel =  new QualificationQuestionBusinessSector();
						$qualificationbsectormodel->qualification_question_id = $model->id;
						$qualificationbsectormodel->business_sector_id = $value;
						$qualificationbsectormodel->save();
					}
				}

				if(is_array($data['business_sector_group_id']) && count($data['business_sector_group_id'])>0)
                {
					QualificationQuestionBusinessSectorGroup::deleteAll(['qualification_question_id' => $model->id]);
                    foreach ($data['business_sector_group_id'] as $value)
                    {
						$qualificationbsectorgrpmodel = new QualificationQuestionBusinessSectorGroup;
						$qualificationbsectorgrpmodel->qualification_question_id = $model->id;
						$qualificationbsectorgrpmodel->business_sector_group_id = $value;
						$qualificationbsectorgrpmodel->save();
					}
				}

				$responsedata=array('status'=>1,'message'=>'Qualification Questions has been updated successfully');	
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
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$resultarr["name"]=$model->name;
			$resultarr["code"]=$model->code;
			$resultarr["guidance"]=$model->guidance;
			$resultarr["file_upload_required"]=$model->file_upload_required;
			$resultarr["recurring_period"]=$model->recurring_period;
			
			$resultarr["file_upload_required_label"]='No';	
			if($model->recurring_period==1)
			{
				$resultarr["file_upload_required_label"]='Yes';				
			}
			
			$recurring_period_label='NA';
			if($model->recurring_period!=0)
			{
				$recurring_period_label=$model->arrRecurringPeriod[$model->recurring_period];
			}
			$resultarr["recurring_period_label"]=$recurring_period_label;

			$queststds=$model->qualificationquestionstandard;
			if(count($queststds)>0)
			{
				$stds_arr = array();
				$stds_label_arr = array();
				foreach($queststds as $val)
				{
					$stds_arr[]=$val['standard_id'];
					$stds_label_arr[]=$val->standard->name;
				}
				$resultarr["standard"]=$stds_arr;
				$resultarr["standard_label"]=implode(', ',$stds_label_arr);
			}
			
			/*
			$questprocesses=$model->qualificationquestionprocess;
			if(count($questprocesses)>0)
			{
				$procs_arr = array();
				$procs_label_arr = array();
				foreach($questprocesses as $val)
				{
					$procs_arr[]=$val['process_id'];
					$procs_label_arr[]=$val->process->name;
				}
				$resultarr["process"]=$procs_arr;
				$resultarr["process_label"]=implode(', ',$procs_label_arr);
			}
			*/

			$questroles=$model->qualificationquestionrole;
			if(count($questroles)>0)
			{
				$roles_arr = array();
				$roles_label_arr = array();
				foreach($questroles as $val)
				{
					$roles_arr[]=$val['role_id'];
					$roles_label_arr[]=$val->role->role_name;
				}
				$resultarr["role"]=$roles_arr;
				$resultarr["role_label"]=$roles_label_arr;
			}

			$questbsectors=$model->qualificationquestionbusinesssector;
			if(count($questbsectors)>0)
			{
				$bsector_arr = array();
				$bsector_label_arr = array();
				foreach($questbsectors as $val)
				{
					$bsector_arr[]=$val['business_sector_id'];
					$bsector_label_arr[]=$val->businesssector->name;
				}
				$resultarr["business_sector_id"]=$bsector_arr;
				$resultarr["bsector_label"]=$bsector_label_arr;
			}

			$questbsectorgroups=$model->qualificationquestionbusinesssectorgroup;
			if(count($questbsectorgroups)>0)
			{
				$bsectorgroup_arr = array();
				$bsectorgroup_label_arr = array();
				foreach($questbsectorgroups as $val)
				{
					$bsectorgroup_arr[]=$val['business_sector_group_id'];
					$bsectorgroup_label_arr[]=$val->businesssectorgroup->group_code;
				}
				$resultarr["business_sector_group_id"]=$bsectorgroup_arr;
				$resultarr["bsectorgroup_label"]=$bsectorgroup_label_arr;
			}

			
            return ['data'=>$resultarr];
        }

    }
	
	public function actionChecklistView(){
		$model = QualificationQuestion::find()->asArray()->all();
		$data= ['standards'=>[['id'=>1,'name'=>'GOTS','questions'=>$model],['id'=>2,'name'=>'GRS','questions'=>$model]],'answerArr'=>[['id'=>1,'name'=>'Yes'],['id'=>2,'name'=>'No']] ];
		return array('data'=>$data);
	}

    protected function findModel($id)
    {
        if (($model = QualificationQuestion::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
	}
	
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
			$id=$data['id'];
           	$model = QualificationQuestion::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Qualification Checklist has been activated successfully';
					}elseif($model->status==1){
						$msg='Qualification Checklist has been deactivated successfully';
					}elseif($model->status==2){

						$exists=0;
						if(UserQualificationReviewComment::find()->where( [ 'qualification_question_id' => $id ] )->exists())
						{
							$exists=1;
						}

						if($exists==0)
                        {
                           // QualificationQuestion::findOne($id)->delete();
                        }
						$msg='Qualification Checklist has been deleted successfully';
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
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}
            return $this->asJson($responsedata);
        }
	}
	
	
	public function actionRecurringPeriod()
	{
		$model = new QualificationQuestion();
		
		return ['recurringperiod'=>$model->arrRecurringPeriod];
	}
}
