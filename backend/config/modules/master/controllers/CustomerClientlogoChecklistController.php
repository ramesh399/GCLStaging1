<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\ClientLogoQuestionCustomer;
use app\modules\master\models\ClientLogoQuestionCustomerStandard;


use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * CustomerClientlogoChecklistController implements the CRUD actions for Product model.
 */
class CustomerClientlogoChecklistController extends \yii\rest\Controller
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
		
		$model = ClientLogoQuestionCustomer::find()->alias('t')->where(['<>','status',2]);
		$model = $model->join('left join', 'tbl_client_logo_question_customer_standards as question_standard','question_standard.client_logo_checklist_customer_question_id = t.id');

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
		$model = new ClientLogoQuestionCustomer();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$model->name = $data['name'];
			$model->interpretation = $data['interpretation'];	
			$model->file_upload_required = $data['file_upload_required'];
			$userData = Yii::$app->userdata->getData();
			$model->created_by = $userData['userid'];
			
			if($model->validate() && $model->save())
			{
				if(is_array($data['standard_id']) && count($data['standard_id'])>0)
                {
                    foreach ($data['standard_id'] as $value)
                    { 
						$Standardmodel =  new ClientLogoQuestionCustomerStandard();
						$Standardmodel->client_logo_checklist_customer_question_id = $model->id;
						$Standardmodel->standard_id = $value;
						$Standardmodel->save();
					}
				}	
				$responsedata=array('status'=>1,'message'=>'Client Logo Questions has been created successfully');	
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
			$model = ClientLogoQuestionCustomer::find()->where(['id' => $data['id']])->one();
			$model->name = $data['name'];
			$model->interpretation = $data['interpretation'];
			$model->file_upload_required = $data['file_upload_required'];

			$userData = Yii::$app->userdata->getData();
			$model->updated_by = $userData['userid'];
			
			if($model->validate() && $model->save())
			{
				if(is_array($data['standard_id']) && count($data['standard_id'])>0)
                {
					ClientLogoQuestionCustomerStandard::deleteAll(['client_logo_checklist_customer_question_id' => $model->id]);
                    foreach ($data['standard_id'] as $value)
                    { 
						$Standardmodel =  new ClientLogoQuestionCustomerStandard();
						$Standardmodel->client_logo_checklist_customer_question_id = $model->id;
						$Standardmodel->standard_id = $value;
						$Standardmodel->save();
					}
				}	
				$responsedata=array('status'=>1,'message'=>'Client Logo Question has been updated successfully');	
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
			$resultarr["interpretation"]=$model->interpretation;
			$resultarr["file_upload_required"]=$model->file_upload_required;					
			$resultarr["file_upload_required_label"]=($model->file_upload_required!=1)?"No":"Yes";

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
			
			
            return ['data'=>$resultarr];
        }

	}

	public function actionGetQuestions()
	{
		$model = ClientLogoQuestionCustomer::find()->where(['status'=>0]);
		$question = new ClientLogoQuestionCustomer();
		
		$product_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $product)
			{
				$data=array();
				$data['id']=$product->id;
				$data['name']=$product->name;
				$data['interpretation']=$product->interpretation;
				$data['file_upload_required']=$product->file_upload_required;
				$data['status']=$product->status;			
				$product_list[]=$data;
			}
		}
		
		return ['requirements'=>$product_list];
	}
	
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
           	$model = ClientLogoQuestionCustomer::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Client Logo Question has been activated successfully';
					}elseif($model->status==1){
						$msg='Client Logo Question has been deactivated successfully';
					}elseif($model->status==2){
						$msg='Client Logo Question has been deleted successfully';
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
        if (($model = ClientLogoQuestionCustomer::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
}
