<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\audit\models\AuditReportLivingWageRequirementReview;
use app\modules\audit\models\AuditReportLivingWageRequirementReviewComment;
use app\modules\audit\models\AuditReportLivingWageFamilyExpenses;
use app\modules\audit\models\AuditReportLivingWageFamilyExpensesInfo;
use app\modules\audit\models\Audit;


use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class AuditLivingwageController extends \yii\rest\Controller
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

			$model = AuditReportLivingWageRequirementReview::find()->where(['audit_id' => $data['audit_id'],'unit_id' => $data['unit_id']])->one();
			if($model===null)
			{
				$model = new AuditReportLivingWageRequirementReview();
				$model->created_by = $userData['userid'];
			}
			else
			{
				$model->updated_by = $userData['userid'];
				AuditReportLivingWageRequirementReviewComment::deleteAll(['living_wage_requirement_checklist_review_id' => $model->id]);
			}

			$model->audit_id = $data['audit_id'];
			$model->unit_id = $data['unit_id'];
			if($model->validate() && $model->save())
			{
				if(is_array($data['checklistdata']) && count($data['checklistdata'])>0)
				{
					foreach ($data['checklistdata'] as $value)
					{ 
						$reviewcmtmodel = new AuditReportLivingWageRequirementReviewComment();
						$reviewcmtmodel->living_wage_requirement_checklist_review_id = $model->id;
						$reviewcmtmodel->category_id = $value['category_id'];
						$reviewcmtmodel->category = $value['category'];
						//$reviewcmtmodel->answer = $value['answer'];
						$reviewcmtmodel->comment = $value['comment'];
						$reviewcmtmodel->save();
					}
				}
				$responsedata=array('status'=>1,'message'=>'LivingWage Checklist has been saved successfully');
			}
		}
		return $this->asJson($responsedata);
	}

	public function actionGetChecklist()
	{
		$responsedata=array('status'=>0,'message'=>'Question not found');
        if (Yii::$app->request->post()) 
		{
			$result = array();
			$data = Yii::$app->request->post();
			$userData = Yii::$app->userdata->getData();

			$pdata = [];
			$pdata['audit_id'] = $data['audit_id'];
			$pdata['unit_id'] = $data['unit_id'];
			$pdata['checktype'] = 'unitwise';
			if(!Yii::$app->userrole->canViewAuditReport($pdata)){
				return false;
			}

			$model = AuditReportLivingWageRequirementReview::find()->where(['audit_id' => $data['audit_id'],'unit_id' => $data['unit_id']])->orderBy(['id' => SORT_DESC])->one();
			if ($model !== null)
			{
				$reviewquestionarr=[];
				$categoryquestionarr=[];
				$categoryexpensesinfo=[];
				$appReview=$model->reviewcomment;
				if(count($appReview)>0)
				{
					foreach($appReview as $reviewComment)
					{
						$reviewquestionarr[]=array(
							'id'=>$reviewComment->id,
							'living_wage_requirement_checklist_review_id'=>$reviewComment->living_wage_requirement_checklist_review_id,
							'category_id'=>$reviewComment->category_id,
							'category'=>$reviewComment->category,
							'answer'=>$reviewComment->answer,
							'comment'=>$reviewComment->comment
						);				
					}	
				}
				$result['requirements'] = $reviewquestionarr;
				
			}

			$model = AuditReportLivingWageFamilyExpenses::find()->where(['audit_id' => $data['audit_id'],'unit_id' => $data['unit_id']])->orderBy(['id' => SORT_DESC])->one();
			if ($model !== null)
			{
				$categoryquestionarr['total_family_basket'] = $model->total_family_basket;
				$categoryquestionarr['percentage_of_expenses_for_food'] = $model->percentage_of_expenses_for_food;
				$categoryquestionarr['number_of_wage_earners_per_family'] = $model->number_of_wage_earners_per_family;
				$categoryquestionarr['living_wage'] = $model->living_wage;

				$categoryanswer=$model->expensesinfo;
				if(count($categoryanswer)>0)
				{
					foreach($categoryanswer as $expensesinfo)
					{
						$categoryexpensesinfo[]=array(
							'id'=>$expensesinfo->id,
							'category_id'=>$expensesinfo->category_id,
							'category'=>$expensesinfo->category,'cost_in_local_currency'=>$expensesinfo->cost_in_local_currency,'number_of_individuals'=>$expensesinfo->number_of_individuals,'total'=>$expensesinfo->total
						);
					}
					
				}
				$result['expensesinfo'] = $categoryexpensesinfo;
				$result['categorys'] = $categoryquestionarr;

			}
			$responsedata = $result;


		}
		return $responsedata;
	}


	public function actionGetAnswer()
	{
		$responsedata=array('status'=>0,'message'=>'Review data not found');
        if (Yii::$app->request->post()) 
		{
			$result = array();
			$data = Yii::$app->request->post();
			$userData = Yii::$app->userdata->getData();
			$pdata = [];
			$pdata['audit_id'] = $data['audit_id'];
			$pdata['unit_id'] = $data['unit_id'];
			$pdata['checktype'] = 'unitwise';
			if(!Yii::$app->userrole->canViewAuditReport($pdata)){
				return false;
			}

			$model = AuditReportLivingWageRequirementReview::find()->where(['audit_id' => $data['audit_id'],'unit_id' => $data['unit_id']])->orderBy(['id' => SORT_DESC])->one();
			if ($model !== null)
			{
				
				$applicationreviews=[];
				$reviewarr=[];
				$reviewquestionarr=[];
				$reviewcommentarr=[];
				$categoryquestionarr=[];
				$categorycommentarr=[];
				$appReview=$model->reviewcomment;
				if(count($appReview)>0)
				{
					foreach($appReview as $reviewComment)
					{
						$reviewcommentarr[]=array(
							'id'=>$reviewComment->id,
							'living_wage_requirement_checklist_review_id'=>$reviewComment->living_wage_requirement_checklist_review_id,
							'answer'=>$reviewComment->answer,
							'comment'=>$reviewComment->comment,
							'category_id'=>$reviewComment->category_id
						);						
					}	
				}
				$result['requirementcomment'] = $reviewcommentarr;
				
			}

			$model = AuditReportLivingWageFamilyExpenses::find()->where(['audit_id' => $data['audit_id'],'unit_id' => $data['unit_id']])->orderBy(['id' => SORT_DESC])->one();
			if ($model !== null)
			{
				// $resultarr['total_family_basket'] = $model->total_family_basket;
				// $resultarr['percentage_of_expenses_for_food'] = $model->percentage_of_expenses_for_food;
				$result['number_of_wage_earners_per_family'] = $model->number_of_wage_earners_per_family;
				// $resultarr['living_wage'] = $model->living_wage;

				$categoryanswer=$model->expensesinfo;
				if(count($categoryanswer)>0)
				{
					foreach($categoryanswer as $expensesinfo)
					{
						$categorycommentarr[]=array('category_id'=>$expensesinfo->category_id,'id'=>$expensesinfo->id,'client_information_family_expense_id'=>$expensesinfo->client_information_family_expense_id,'cost_in_local_currency'=>$expensesinfo->cost_in_local_currency,'number_of_individuals'=>$expensesinfo->number_of_individuals,'total'=>$expensesinfo->total);
					}
					
				}
				$result['categorycomment'] = $categorycommentarr;

			}
			$responsedata = $result;


		}
		return $responsedata;
	}


	public function actionSaveCategory()
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

			$model = AuditReportLivingWageFamilyExpenses::find()->where(['audit_id' => $data['audit_id'],'unit_id' => $data['unit_id']])->one();
			if($model===null)
			{
				$model = new AuditReportLivingWageFamilyExpenses();
				$model->created_by = $userData['userid'];
			}
			else
			{
				$model->updated_by = $userData['userid'];
				AuditReportLivingWageFamilyExpensesInfo::deleteAll(['client_information_family_expense_id' => $model->id]);
			}

			$model->audit_id = $data['audit_id'];
			$model->unit_id = $data['unit_id'];
			if(is_array($data['categorydata']) && count($data['categorydata'])>0)
			{
				$total_family_basket = 0;
				$percentage_of_expenses_for_food = 0;
				foreach ($data['categorydata'] as $value)
				{ 
					$total_family_basket+=$value['cost_in_local_currency'] * $value['number_of_individuals'];

					if($value['category_id']=='39')
					{
						$food = $value['cost_in_local_currency'] * $value['number_of_individuals'];
					}
				}
				$percentage_of_expenses_for_food = ($food / $total_family_basket)*100;

				$living_wage = ($total_family_basket/$data['number_of_wage_earners_per_family']*110)/100;

				$model->total_family_basket = $total_family_basket;
				$model->percentage_of_expenses_for_food = $percentage_of_expenses_for_food;
				$model->living_wage = $living_wage;
			}

			$model->number_of_wage_earners_per_family = $data['number_of_wage_earners_per_family'];

			if($model->validate() && $model->save())
			{
				if(is_array($data['categorydata']) && count($data['categorydata'])>0)
				{
					foreach ($data['categorydata'] as $value)
					{ 
						$reviewcmtmodel = new AuditReportLivingWageFamilyExpensesInfo();
						$reviewcmtmodel->client_information_family_expense_id = $model->id;
						$reviewcmtmodel->category_id = $value['category_id'];
						$reviewcmtmodel->category = $value['category'];
						$reviewcmtmodel->cost_in_local_currency = $value['cost_in_local_currency'];
						$reviewcmtmodel->number_of_individuals = $value['number_of_individuals'];
						$reviewcmtmodel->total = $value['cost_in_local_currency'] * $value['number_of_individuals'];
						$reviewcmtmodel->save();
					}
				}
				$responsedata=array('status'=>1,'message'=>'LivingWage Category has been saved successfully');
			}
		}
		return $this->asJson($responsedata);
	}
	
}
