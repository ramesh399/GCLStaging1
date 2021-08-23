<?php
namespace app\modules\master\controllers;

use Yii;
use yii\web\NotFoundHttpException;

use app\modules\master\models\Questions;
use app\modules\application\models\ApplicationReview;
use app\modules\application\models\ApplicationReviewComment;
use app\modules\application\models\ApplicationUnitReviewComment;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * ChecklistController implements the CRUD actions for Questions model.
 */
class ChecklistController extends \yii\rest\Controller
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
			if(!Yii::$app->userrole->hasRights(array('app_unit_review_checklist_master')))
			{
				return false;
			}
		}else{
			if(!Yii::$app->userrole->hasRights(array('app_review_checklist_master')))
			{
				return false;
			}
		}
		$model = Questions::find()->where(['<>','status',2]);
		$question = new Questions;
		$applicationReview  = new ApplicationReview;
		$date_format = Yii::$app->globalfuns->getSettings('date_format');

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
		//$model = $model->limit(2);//->offset($page);
		
		$product_list=array();
		//$model = $model->asArray()->all();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $product)
			{
				$data=array();
				$data['id']=$product->id;
				$data['name']=$product->name;
				$data['guidance']=$product->guidance;
				$data['category']=$product->category;
				$data['status']=$product->status;
				$data['code']=$product->code;
				//$data['created_at']=date('M d,Y h:i A',$product->created_at);
				$data['created_at']=date($date_format,$product->created_at);
				$product_list[]=$data;
			}
		}
		
		
		return ['checklists'=>$product_list,'risklists'=>$question->riskListArray(),'reviewResult'=>$applicationReview->reviewResultArray(),'total'=>$totalCount];
    }
	
	public function actionGetChecklist()
	{
		$model = Questions::find()->where(['status'=>0]);
		$question = new Questions;
		$applicationReview  = new ApplicationReview;
		
		$product_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $product)
			{
				$data=array();
				$data['id']=$product->id;
				$data['name']=$product->name;
				$data['guidance']=$product->guidance;
				$data['category']=$product->category;
				$data['status']=$product->status;
				$data['code']=$product->code;				
				$product_list[]=$data;
			}
		}
		
		return ['checklists'=>$product_list,'risklists'=>$question->riskListArray(),'reviewResult'=>$applicationReview->reviewResultArray()];
	}


    public function actionCreate()
    {
		$model = new Questions();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
        if ($data) 
		{
			if($data['category'] == '2'){
				if(!Yii::$app->userrole->hasRights(array('add_app_unit_review_checklist')))
				{
					return false;
				}
			}else{
				if(!Yii::$app->userrole->hasRights(array('add_app_review_checklist')))
				{
					return false;
				}
			}
            $model->name=$data['name'];
            $model->code='';
            $model->guidance=$data['guidance'];
			$model->category=$data['category'];

			$userData = Yii::$app->userdata->getData();
			$model->created_by=$userData['userid'];

            if($model->validate() && $model->save())
        	{
                $responsedata=array('status'=>1,'message'=>'Checklist has been created successfully');
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

    
    public function actionUpdate()
    {
		$model = new Questions();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{
			

			$model = Questions::find()->where(['id' => $data['id']])->one();
			if($model->category == '2'){
				if(!Yii::$app->userrole->hasRights(array('edit_app_unit_review_checklist')))
				{
					return false;
				}
			}else{
				if(!Yii::$app->userrole->hasRights(array('edit_app_review_checklist')))
				{
					return false;
				}
			}

            $model->name=$data['name'];
            //$model->code=$data['code'];
            $model->guidance=$data['guidance'];
			
			$userData = Yii::$app->userdata->getData();
			$model->updated_by=$userData['userid'];
			
            if($model->validate() && $model->save())
			{
				$responsedata=array('status'=>1,'message'=>'Checklist has been updated successfully');
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

    public function actionView()
    {
		
        $model = new Questions();

        $post = Yii::$app->request->post();

        //$productmodeldata = Questions::find()->where(['id' => $post['id']])->asArray()->one();
		//return ['product'=> $productmodeldata];

		$productmodeldata = Questions::find()->where(['id' => $post['id']])->one();
		if($productmodeldata->category == '2'){
			if(!Yii::$app->userrole->hasRights(array('app_unit_review_checklist_master')))
			{
				return false;
			}
		}else{
			if(!Yii::$app->userrole->hasRights(array('app_review_checklist_master')))
			{
				return false;
			}
		}

		return ['data'=> $productmodeldata];
	}
	
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data && isset($data['status'])) 
		{
			$id=$data['id'];
			$status = $data['status'];

           	$model = Questions::find()->where(['id' => $id])->one();
			if ($model !== null)
			{

				if($model->category == '2')
				{
					if(!Yii::$app->userrole->canDoCommonUpdate($status,'app_unit_review_checklist'))
					{
						return false;
					}	
				}
				else
				{
					if(!Yii::$app->userrole->canDoCommonUpdate($status,'app_review_checklist'))
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
						$msg='Application Review Checklist has been activated successfully';
					}elseif($model->status==1){
						$msg='Application Review Checklist has been deactivated successfully';
					}elseif($model->status==2){
						$exists=0;

						if($model->category!=2)
						{
							if(ApplicationReviewComment::find()->where( [ 'question_id' => $id ] )->exists())
							{
								$exists=1;
							}
							
						}
						else
						{
							if(ApplicationUnitReviewComment::find()->where( [ 'question_id' => $id ] )->exists())
							{
								$exists=1;
							}
						}
                       
						if($exists==0)
                        {
                            //Questions::findOne($id)->delete();
                        }
						$msg='Application Review Checklist has been deleted successfully';
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
