<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\AuditPlanningQuestions;
use app\modules\master\models\AuditPlanningRiskCategory;
use app\modules\master\models\AuditPlanningQuestionRiskCategory;
use app\modules\master\models\AuditPlanningQuestionsAuditType;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditPlanningChecklistController implements the CRUD actions for Product model.
 */
class AuditPlanningChecklistController extends \yii\rest\Controller
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
		if($post['category'] == '2'){
			if(!Yii::$app->userrole->hasRights(array('app_planning_review_unit_checklist_master')))
			{
				return false;
			}
		}else{
			if(!Yii::$app->userrole->hasRights(array('app_planning_review_checklist_master')))
			{
				return false;
			}
		}
		

		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		$model = AuditPlanningQuestions::find()->where(['<>','status',2]);
		if(isset($post['category']) && $post['category'] !=''){
			$model->andWhere(['category'=> $post['category']]);
		}
		
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
				$data['category']=$question->category;
				$data['status']=$question->status;
				//$data['created_at']=date('M d,Y h:i A',$question->created_at);
				$data['created_at']=date($date_format,$question->created_at);
				$question_list[]=$data;
			}
		}

		return ['auditplanningchecklists'=>$question_list,'total'=>$totalCount];
    }

    public function actionCreate()
	{
		
		$model = new AuditPlanningQuestions();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if($data['category'] == '2'){
			if(!Yii::$app->userrole->hasRights(array('add_app_planning_review_checklist')))
			{
				return false;
			}
		}else{
			if(!Yii::$app->userrole->hasRights(array('add_app_planning_review_unit_checklist')))
			{
				return false;
			}
		}
		if ($data) 
		{	
			$model->name = $data['name'];
			$model->guidance = $data['guidance'];	
			$model->category=$data['category'];
			$userData = Yii::$app->userdata->getData();
			$model->created_by = $userData['userid'];
			
			if($model->validate() && $model->save())
			{

				if(is_array($data['riskcategory']) && count($data['riskcategory'])>0)
                {
                    foreach ($data['riskcategory'] as $value)
                    { 
						$qualificationstdmodel =  new AuditPlanningQuestionRiskCategory();
						$qualificationstdmodel->audit_planning_question_id = $model->id;
						$qualificationstdmodel->risk_category_id = $value;
						$qualificationstdmodel->save();
					}
				}

				if(is_array($data['audit_type']) && count($data['audit_type'])>0)
                {
                    foreach ($data['audit_type'] as $value)
                    { 
						$typemodel =  new AuditPlanningQuestionsAuditType();
						$typemodel->planning_question_id = $model->id;
						$typemodel->audit_type_id = $value;
						$typemodel->save();
					}
				}			
				$responsedata=array('status'=>1,'message'=>'Audit Planning Questions has been created successfully');	
			}
		}
		return $this->asJson($responsedata);
	}

    public function actionUpdate()
    {
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if($data['category'] == '2'){
			if(!Yii::$app->userrole->hasRights(array('edit_app_planning_review_checklist')))
			{
				return false;
			}
		}else{
			if(!Yii::$app->userrole->hasRights(array('edit_app_planning_review_unit_checklist')))
			{
				return false;
			}
		}
		if ($data) 
		{	
			$model = AuditPlanningQuestions::find()->where(['id' => $data['id']])->one();
			$model->name = $data['name'];
			$model->guidance = $data['guidance'];
			$model->category=$data['category'];
			
			$userData = Yii::$app->userdata->getData();
			$model->updated_by = $userData['userid'];
			
			if($model->validate() && $model->save())
			{

				if(is_array($data['riskcategory']) && count($data['riskcategory'])>0)
                {
					AuditPlanningQuestionRiskCategory::deleteAll(['audit_planning_question_id' => $model->id]);
                    foreach ($data['riskcategory'] as $value)
                    { 
						$qualificationstdmodel =  new AuditPlanningQuestionRiskCategory();
						$qualificationstdmodel->audit_planning_question_id = $model->id;
						$qualificationstdmodel->risk_category_id = $value;
						$qualificationstdmodel->save();
					}
				}

				if(is_array($data['audit_type']) && count($data['audit_type'])>0)
                {
					AuditPlanningQuestionsAuditType::deleteAll(['planning_question_id' => $model->id]);
                    foreach ($data['audit_type'] as $value)
                    { 
						$typemodel =  new AuditPlanningQuestionsAuditType();
						$typemodel->planning_question_id = $model->id;
						$typemodel->audit_type_id = $value;
						$typemodel->save();
					}
				}		
				$responsedata=array('status'=>1,'message'=>'Audit Planning Questions has been updated successfully');	
			}
		}
		return $this->asJson($responsedata);
    }
	
	public function actionView()
    {

		$data = Yii::$app->request->post();
		
		if(!Yii::$app->userrole->hasRights(array('app_planning_review_checklist_master','app_planning_review_unit_checklist_master')))
		{
			return false;
		}
	

		$questmodel = new AuditPlanningQuestions();
        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$resultarr["name"]=$model->name;
			$resultarr["category"]=$model->category;
			$resultarr["code"]=$model->code;
			$resultarr["guidance"]=$model->guidance;					

			$auditPlanningRiskCategory=$model->riskcategory;
			if(count($auditPlanningRiskCategory)>0)
			{
				$riskcategory_arr = array();
				$riskcategory_label_arr = array();
				foreach($auditPlanningRiskCategory as $val)
				{
					$riskcategory_arr[]=$val['risk_category_id'];
					$riskcategory_label_arr[]=$val->category->name;
				}
				$resultarr["riskcategory"]=$riskcategory_arr;
				$resultarr["risk_category_label"]=implode(', ',$riskcategory_label_arr);
			}

			$audittype=$model->audittype;
			if(count($audittype)>0)
			{
				$audittype_arr = array();
				$audittype_label_arr = array();
				foreach($audittype as $val)
				{
					$audittype_arr[]=$val['audit_type_id'];
					$audittype_label_arr[]=$questmodel->arrAuditTye[$val['audit_type_id']];
				}
				$resultarr["audit_type"]=$audittype_arr;
				$resultarr["audit_type_label"]=implode(', ',$audittype_label_arr);
			}
			
			$riskCat = $this->actionRiskCategory();

			$resultarr["riskCategory"]=$riskCat['riskCategory'];
			$resultarr["audittype"]=$questmodel->arrAuditTye;
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

			

           	$model = AuditPlanningQuestions::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				if($model->category == '2')
				{
					if(!Yii::$app->userrole->canDoCommonUpdate($status,'app_planning_review_unit_checklist'))
					{
						return false;
					}	
				}
				else
				{
					if(!Yii::$app->userrole->canDoCommonUpdate($status,'app_planning_review_checklist'))
					{
						return false;
					}	
				}
				
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Audit Planning Question has been activated successfully';
					}elseif($model->status==1){
						$msg='Audit Planning Question has been deactivated successfully';
					}elseif($model->status==2){
						$msg='Audit Planning Question has been deleted successfully';
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
        if (($model = AuditPlanningQuestions::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	public function actionRiskCategory()
	{
		$model = new AuditPlanningQuestions();
		$RiskCategory = AuditPlanningRiskCategory::find()->select(['id','name'])->asArray()->all();
		return ['riskCategory'=>$RiskCategory,'audittype'=>$model->arrAuditTye];
		
	}
}
