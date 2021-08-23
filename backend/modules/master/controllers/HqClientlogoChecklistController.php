<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\ClientLogoQuestionHq;
use app\modules\master\models\ClientLogoQuestionHqFindings;
use app\modules\master\models\ClientLogoQuestionHqStandard;


use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * HqClientlogoChecklistController implements the CRUD actions for Product model.
 */
class HqClientlogoChecklistController extends \yii\rest\Controller
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
		
		$model = ClientLogoQuestionHq::find()->alias('t')->where(['<>','status',2]);
		$model = $model->join('left join', 'tbl_client_logo_question_hq_standards as question_standard','question_standard.client_logo_checklist_hq_question_id = t.id');

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
				$data['created_at']=date($date_format,$question->created_at);
				$question_list[]=$data;
			}
		}

		return ['informationchecklists'=>$question_list,'total'=>$totalCount];
    }

    public function actionCreate()
	{
		$model = new ClientLogoQuestionHq();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$model->name = $data['name'];
			$model->interpretation = $data['interpretation'];	
			$userData = Yii::$app->userdata->getData();
			$model->created_by = $userData['userid'];
			
			if($model->validate() && $model->save())
			{
				if(is_array($data['finding_id']) && count($data['finding_id'])>0)
                {
                    foreach ($data['finding_id'] as $value)
                    { 
						$qualificationstdmodel =  new ClientLogoQuestionHqFindings();
						$qualificationstdmodel->client_logo_checklist_hq_question_id = $model->id;
						$qualificationstdmodel->question_finding_id = $value;
						$qualificationstdmodel->save();
					}
				}	
				
				if(is_array($data['standard_id']) && count($data['standard_id'])>0)
                {
                    foreach ($data['standard_id'] as $value)
                    { 
						$Standardmodel =  new ClientLogoQuestionHqStandard();
						$Standardmodel->client_logo_checklist_hq_question_id = $model->id;
						$Standardmodel->standard_id = $value;
						$Standardmodel->save();
					}
				}	
				$responsedata=array('status'=>1,'message'=>'HQ Client Logo Checklist Question has been created successfully');	
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
			$model = ClientLogoQuestionHq::find()->where(['id' => $data['id']])->one();
			$model->name = $data['name'];
			$model->interpretation = $data['interpretation'];

			$userData = Yii::$app->userdata->getData();
			$model->updated_by = $userData['userid'];
			
			if($model->validate() && $model->save())
			{
				if(is_array($data['finding_id']) && count($data['finding_id'])>0)
                {
					ClientLogoQuestionHqFindings::deleteAll(['client_logo_checklist_hq_question_id' => $model->id]);
                    foreach ($data['finding_id'] as $value)
                    { 
						$qualificationstdmodel =  new ClientLogoQuestionHqFindings();
						$qualificationstdmodel->client_logo_checklist_hq_question_id = $model->id;
						$qualificationstdmodel->question_finding_id = $value;
						$qualificationstdmodel->save();
					}
				}
				if(is_array($data['standard_id']) && count($data['standard_id'])>0)
                {
					ClientLogoQuestionHqStandard::deleteAll(['client_logo_checklist_hq_question_id' => $model->id]);
                    foreach ($data['standard_id'] as $value)
                    { 
						$Standardmodel =  new ClientLogoQuestionHqStandard();
						$Standardmodel->client_logo_checklist_hq_question_id = $model->id;
						$Standardmodel->standard_id = $value;
						$Standardmodel->save();
					}
				}	
				$responsedata=array('status'=>1,'message'=>'HQ Client Logo Checklist Question has been updated successfully');	
			}
		}
		return $this->asJson($responsedata);
    }
	
	public function actionView()
    {
		$data = Yii::$app->request->post();
		$findingmodel = new ClientLogoQuestionHq();

        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$resultarr["name"]=$model->name;
			$resultarr["code"]=$model->code;
			$resultarr["interpretation"]=$model->interpretation;					

			$AuditReviewerFindings=$model->finding;
			if(count($AuditReviewerFindings)>0)
			{
				$finding = array();
				$finding_label_arr = array();
				foreach($AuditReviewerFindings as $val)
				{
					$riskcategory_arr[]=$val['question_finding_id'];
					$riskcategory_label_arr[]=$findingmodel->arrFindings[$val['question_finding_id']];
				}
				$resultarr["finding_id"]=$riskcategory_arr;
				$resultarr["risk_category_label"]=implode(', ',$riskcategory_label_arr);
			}
			
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
			

			$resultarr["riskCategory"]=$findingmodel->arrFindings;
			
            return ['data'=>$resultarr];
        }

	}
	
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
           	$model = ClientLogoQuestionHq::find()->where(['id' => $data['id']])->one();
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
        if (($model = ClientLogoQuestionHq::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	public function actionRiskCategory()
	{
		$riskCategory = new ClientLogoQuestionHq();
		
		return ['finding_id'=>$riskCategory->arrFindings];
	}
}
