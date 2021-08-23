<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\AuditReportCategory;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditCategoryController implements the CRUD actions for Product model.
 */
class AuditCategoryController extends \yii\rest\Controller
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
		
		$categorymodel = new AuditReportCategory();
		$model = AuditReportCategory::find()->where(['!=','status', '2']);


		if(isset($post['type']) && $post['type'] !='')
		{
			$model = $model->andWhere(['type'=> $post['type']]);				
		}

		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
			if($post['type']=='living_requirement')
			{
				$currentAction = 'audit_report_living_wage_requirement_master';				
			}elseif($post['type']=='living_category'){
				$currentAction = 'audit_report_living_wage_category_master';				
			}elseif($post['type']=='interview_requirement'){
				$currentAction = 'audit_report_interview_requirement_master';				
			}elseif($post['type']=='client_information'){
				$currentAction = 'audit_report_client_information_master';				
			}	

			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}

            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm']!='')
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 'name', $searchTerm],
					['like', 'date_format(FROM_UNIXTIME(`created_at` ), \'%b %d, %Y\' )', $searchTerm],
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
		
		$category_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $value)
			{
				$data=array();
				$data['id']=$value->id;
				$data['name']=$value->name;
				$data['type']=$value->type;
				$data['created_at']=date($date_format,$value->created_at);
				$data['status']=$value->status;
				$category_list[] = $data;
			}
			
		}

		return ['categorys'=>$category_list,'total'=>$totalCount];
	}
	
	public function actionGetRequirements()
	{
		$model = AuditReportCategory::find()->where(['status'=>0])->andWhere(['type'=> 'interview_requirement']);
		$question = new AuditReportCategory();
		
		$product_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $product)
			{
				$data=array();
				$data['id']=$product->id;
				$data['name']=$product->name;
				$data['type']=$product->type;
				$data['status']=$product->status;			
				$product_list[]=$data;
			}
		}
		
		return ['requirements'=>$product_list,'answer'=>$question->arrAnswer];
	}


	public function actionGetLivingwageChecklist()
	{
		$model = AuditReportCategory::find()->where(['status'=>0])->andWhere(['or',['type'=> 'living_requirement'],['type'=> 'living_category']]);
		
		$requirement_list=array();
		$category_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $product)
			{

				$data=array();
				$data['id']=$product->id;
				$data['name']=$product->name;
				$data['type']=$product->type;
				$data['status']=$product->status;	
				
				if($data['type']!='living_category')
				{
					$requirement_list[]=$data;
				}
				else
				{
					$category_list[]=$data;
				}
				
			}
		}
		
		return ['requirements'=>$requirement_list,'categorys'=>$category_list];
	}


	public function actionGetClientInformation()
	{
		$model = AuditReportCategory::find()->where(['status'=>0])->andWhere(['type'=> 'client_information']);
		$question = new AuditReportCategory();
		
		$product_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $product)
			{
				$data=array();
				$data['id']=$product->id;
				$data['name']=$product->name;
				$data['type']=$product->type;
				$data['status']=$product->status;			
				$product_list[]=$data;
			}
		}
		
		return ['informations'=>$product_list,'answer'=>$question->arrAnswer];
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
				$model = AuditReportCategory::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new AuditReportCategory();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new AuditReportCategory();
				$model->created_by = $userData['userid'];
			}

			if($data['type']=='living_requirement')
			{
				$currentAction = 'add_audit_report_living_wage_requirement';
				if(isset($data['id']) && $data['id']!='')
				{
					$currentAction = 'edit_audit_report_living_wage_requirement';
				}
			}elseif($data['type']=='living_category'){
				$currentAction = 'add_audit_report_living_wage_category';
				if(isset($data['id']) && $data['id']!='')
				{
					$currentAction = 'edit_audit_report_living_wage_category';
				}
			}elseif($data['type']=='interview_requirement'){
				$currentAction = 'add_audit_report_interview_requirement';
				if(isset($data['id']) && $data['id']!='')
				{
					$currentAction = 'edit_audit_report_interview_requirement';
				}
			}elseif($data['type']=='client_information'){
				$currentAction = 'add_audit_report_client_information';
				if(isset($data['id']) && $data['id']!='')
				{
					$currentAction = 'edit_audit_report_client_information';
				}
			}	

			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}

			$model->name = $data['name'];
			$model->type = $data['type'];
			
			if($model->validate() && $model->save())
			{	
				$name = 'Audit Category';
				if($data['type'] == 'living_requirement'){
					$name = 'Audit Report Living wage Requirement';
				}else if($data['type'] == 'living_category'){
					$name = 'Audit Report Living wage Category';
				}else if($data['type'] == 'client_information'){
					$name = 'Audit Report Client Information';
				}else if($data['type'] == 'interview_requirement'){
					$name = 'Audit Report Interview Requirement';
				}
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=> $name.' has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=> $name.' has been created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}

	
	public function actionDeletedata()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			if($data['type']=='living_requirement')
			{
				$currentAction = 'delete_audit_report_living_wage_requirement';
			}elseif($data['type']=='living_category'){
				$currentAction = 'delete_audit_report_living_wage_category';
			}elseif($data['type']=='interview_requirement'){
				$currentAction = 'delete_audit_report_interview_requirement';
			}elseif($data['type']=='client_information'){
				$currentAction = 'delete_audit_report_client_information';
			}	

			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}


			$model = AuditReportCategory::deleteAll(['id' => $data['id']]);
			$responsedata=array('status'=>1,'message'=>'Deleted successfully');
		}
		return $this->asJson($responsedata);
	}
	
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data && isset($data['status'])) 
		{

			$status = $data['status'];	
			
			if($data['type']=='living_requirement')
			{
				$currentAction = 'audit_report_living_wage_requirement';
			}elseif($data['type']=='living_category'){
				$currentAction = 'audit_report_living_wage_category';
			}elseif($data['type']=='interview_requirement'){
				$currentAction = 'audit_report_interview_requirement';
			}elseif($data['type']=='client_information'){
				$currentAction = 'audit_report_client_information';
			}	

			if(!Yii::$app->userrole->canDoCommonUpdate($status,$currentAction))
			{
				return false;
			}	

           	$model = AuditReportCategory::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){

						$msg='Audit Report Interview Requirement has been activated successfully';
					}elseif($model->status==1){

						$msg='Audit Report Interview Requirement has been deactivated successfully';
					}elseif($model->status==2){

						$msg='Audit Report Interview Requirement has been deleted successfully';
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
	
	
	
	
}
