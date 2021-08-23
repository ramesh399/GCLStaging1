<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\audit\models\AuditReviewerReview;
use app\modules\master\models\AuditReviewerQuestions;
use app\modules\master\models\AuditReviewerRiskCategory;
use app\modules\master\models\AuditReviewerQuestionRiskCategory;
use app\modules\audit\models\AuditReviewerReviewChecklistComment;
use app\modules\audit\models\AuditPlan;
use app\modules\audit\models\Audit;


use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class AuditReviewerReviewController extends \yii\rest\Controller
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
		$data = Yii::$app->request->post();
        if ($data) 
		{
			//print_r($data); die;
			$reviewmodel =new AuditReviewerReview();
			$reviewmodel->audit_plan_id=isset($data['audit_plan_id'])?$data['audit_plan_id']:"";
			$userData = Yii::$app->userdata->getData();
			$reviewmodel->user_id=$userData['userid'];
			$reviewmodel->created_at=time();
			$reviewmodel->created_by=$userData['userid'];

			if($reviewmodel->validate() && $reviewmodel->save())
			{
				//generate_certificate'=>'11','certificate_denied

				$auditplan = AuditPlan::find()->where(['id'=>$data['audit_plan_id']])->one();
				if($auditplan !== null){

					$audit = Audit::find()->where(['id'=>$auditplan->audit_id])->one();

					if($data['actiontype'] == 'decline'){
						$auditplan->status = $auditplan->arrEnumStatus['certificate_denied'];
						$auditplan->save();

						if($audit !== null){
							$audit->status = $audit->arrEnumStatus['certificate_denied'];
							$audit->save();
						}
					}else{
						
						$auditplan->status = $auditplan->arrEnumStatus['certification_inprocess'];
						$auditplan->save();

						if($audit !== null){
							$audit->status = $audit->arrEnumStatus['certification_inprocess'];
							$audit->save();
						}
					}

					
				}

				


				
				if(is_array($data['review_answers']) && count($data['review_answers'])>0)
				{
					foreach ($data['review_answers'] as $value)
					{ 
						$reviewcmtmodel=new AuditReviewerReviewChecklistComment();
						$reviewcmtmodel->audit_reviewer_review_id=$reviewmodel->id;
						$reviewcmtmodel->question_id=isset($value['question_id'])?$value['question_id']:"";
						$reviewcmtmodel->question=isset($value['question'])?$value['question']:"";
						$reviewcmtmodel->answer=isset($value['answer'])?$value['answer']:"";
						$reviewcmtmodel->comment=isset($value['comment'])?$value['comment']:"";
						$reviewcmtmodel->save();
					}

					$responsedata=array('status'=>1,'message'=>'Audit Certification Review has been saved successfully');
					
				}
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$reviewmodel->errors);
			}
				
		}
		
		return $this->asJson($responsedata);
	}
	

	public function actionView()
	{
		$responsedata=array('status'=>0,'message'=>'Review data not found');
		
        if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();
			$userData = Yii::$app->userdata->getData();


			$model = AuditReviewerReview::find()->where(['audit_plan_id' => $data['audit_plan_id']])->one();
			if ($model !== null)
			{
				$reviewarr=[];
				$reviewcommentarr=[];
				$auditreviewerReview=$model->auditreviewerreview;
				if(count($auditreviewerReview)>0)
				{
					foreach($auditreviewerReview as $reviewComment)
					{
						$reviewcommentarr[]=array(
							'audit_plan_review_id'=>$reviewComment->audit_plan_review_id,
							'question_id'=>$reviewComment->question_id,
							'question'=>$reviewComment->question,
							'answer'=>$reviewComment->answer,
							'comment'=>$reviewComment->comment
						);
					}	
				}
				$data['auditplanreviewcomment'] = $reviewcommentarr;

				
				$data['status'] = 1;
				return $data;
			}

		}
		return $responsedata;
	}

	public function actionIndex()
	{
        // if (Yii::$app->request->post()) 
		// {
			// $data = Yii::$app->request->post();
			// $userData = Yii::$app->userdata->getData();
			// $audit_review_id = $data['audit_review_id'];

			$model = AuditReviewerQuestions::find()->where(['status'=>0]);
			$model = $model->all();		
			$qdata = [];
			if(count($model)>0)
			{
				foreach($model as $obj)
				{
					$data=array();
					$data['id']=$obj->id;
					$data['name']=$obj->name;
					$data['guidance']=$obj->guidance;		
					$findings=$obj->riskcategory;
					$findingsval=[];
					foreach($findings as $val)
					{
						$opt=[];
						$opt['id']=$val->audit_reviewer_finding_id;
						$opt['name']=$val->category->name;
						$findingsval[]=$opt;
					}
					$data['findings']=$findingsval;
					$qdata[]=$data;
				}
			}
			return ['data'=>$qdata];
		//}
	}
	
}
