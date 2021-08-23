<?php
namespace app\modules\library\controllers;

use Yii;
use app\modules\library\models\LibraryFaq;
use app\modules\library\models\LibraryFaqAccess;
use app\modules\library\models\LibraryUserAccess;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * FaqController implements the CRUD actions for Product model.
 */
class FaqController extends \yii\rest\Controller
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
		
		$model = LibraryFaq::find()->alias('t')->where(['<>','status',2]);
		$model = $model->join('inner join', 'tbl_library_faq_access as faq_access','faq_access.library_faq_id=t.id');

		if($resource_access != '1')
		{
			$source_file_status = 0;
			//$model = $model->join('inner join', 'tbl_library_faq_access as faq_access','faq_access.library_faq_id=t.id');									
			if($user_type==2)
			{
				$customer_roles=Yii::$app->globalfuns->getCustomerRoles();					
				$model = $model->andWhere('faq_access.user_access_id in("'.$customer_roles.'")');	
			}elseif($user_type==3 && $resource_access==5){	
				$model = $model->andWhere('faq_access.user_access_id ="'.$role_chkid.'"');			
				//$ospadmin_roles=Yii::$app->globalfuns->getOspAdminRoles();					
				//$model = $model->andWhere('faq_access.user_access_id in("'.$ospadmin_roles.'")');	
			}elseif($user_type==3){			
				$osp_roles=Yii::$app->globalfuns->getOspRoles();					
				$model = $model->andWhere('faq_access.user_access_id in("'.$osp_roles.'")');	
			}else if($user_type== Yii::$app->params['user_type']['user'] && in_array('faq',$rules )){

			}else{
				$model = $model->andWhere('faq_access.user_access_id ="'.$role.'"');	
			}			
		}

		if(isset($post['roleFilter']) && is_array($post['roleFilter']) && count($post['roleFilter'])>0)
		{
			$model = $model->andWhere(['faq_access.user_access_id'=> $post['roleFilter']]);	
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
					['like', 't.question', $searchTerm],
					['like', 'date_format(FROM_UNIXTIME(`t.created_at` ), \'%b %d, %Y\' )', $searchTerm],
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
				$model = $model->orderBy(['t.created_at' => SORT_DESC]);
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
				$data['question']=$question->question;
				$data['answer']=$question->answer;

				$libraryfaqaccess = $question->libraryfaqaccess;
				if(count($libraryfaqaccess)>0)
				{
					$access_id_arr = array();
					$access_id_label_arr = array();
					foreach($libraryfaqaccess as $val)
					{
						if($val->useraccess!==null)
						{
							$access_id_arr[]="".$val['user_access_id'];
							$access_id_label_arr[]=($val->useraccess ? $val->useraccess->role_name : '');
						}
					}
					$data["user_access_id"]=$access_id_arr;
					$data["access_id_label"]=implode(', ',$access_id_label_arr);
				}

				$data['status']=$question->status;
				$data['created_at']=date($date_format,$question->created_at);
				$data['created_by_label']=$question->createdbydata->first_name.' '.$question->createdbydata->last_name;
				$question_list[]=$data;
			}
		}

		return ['faqs'=>$question_list,'total'=>$totalCount];
	}
	
	public function actionGetData()
	{
		//$UserAccess = LibraryUserAccess::find()->select(['id','name'])->where(['status'=>0])->asArray()->all();
		return ['useraccess'=>Yii::$app->globalfuns->getUserRoles()];
	}

    public function actionCreate()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			if(isset($data['id']))
			{
				$model = LibraryFaq::find()->where(['id' => $data['id']])->one();
				$model->question = $data['question'];
				$model->answer = $data['answer'];	
				
				$userData = Yii::$app->userdata->getData();
				$model->updated_by = $userData['userid'];
				
				if($model->validate() && $model->save())
				{
					if(is_array($data['user_access_id']) && count($data['user_access_id'])>0)
					{
						LibraryFaqAccess::deleteAll(['library_faq_id' => $model->id]);
						foreach ($data['user_access_id'] as $value)
						{ 
							$LibraryFaqAccessmodel =  new LibraryFaqAccess();
							$LibraryFaqAccessmodel->library_faq_id = $model->id;
							$LibraryFaqAccessmodel->user_access_id = $value;
							$LibraryFaqAccessmodel->save();
						}
					}
					$responsedata=array('status'=>1,'message'=>'FAQ has been updated successfully');	
				}
			}
			else
			{
				$model = new LibraryFaq();
				if ($data) 
				{	
					$model->question = $data['question'];
					$model->answer = $data['answer'];	
					$userData = Yii::$app->userdata->getData();
					$model->created_by = $userData['userid'];
					
					if($model->validate() && $model->save())
					{	
						if(is_array($data['user_access_id']) && count($data['user_access_id'])>0)
						{
							LibraryFaqAccess::deleteAll(['library_faq_id' => $model->id]);
							foreach ($data['user_access_id'] as $value)
							{ 
								$LibraryFaqAccessmodel =  new LibraryFaqAccess();
								$LibraryFaqAccessmodel->library_faq_id = $model->id;
								$LibraryFaqAccessmodel->user_access_id = $value;
								$LibraryFaqAccessmodel->save();
							}
						}
						$responsedata=array('status'=>1,'message'=>'FAQ has been created successfully');	
					}
				}
			}
		}
		return $this->asJson($responsedata);
	}

	
	public function actionDeletefaqdata()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			LibraryFaqAccess::deleteAll(['library_faq_id' => $data['id']]);
			$model = LibraryFaq::deleteAll(['id' => $data['id']]);
			$responsedata=array('status'=>1,'message'=>'Deleted successfully');
		}
		return $this->asJson($responsedata);
	}
	
	/*
	public function actionView()
    {
		$data = Yii::$app->request->post();
		
        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$resultarr["question"]=$model->question;
			$resultarr["answer"]=$model->answer;

			$libraryfaqfranchise = $model->libraryfaqfranchise;
			if(count($libraryfaqfranchise)>0)
			{
				$franchise_id_arr = array();
				$franchise_id_label_arr = array();
				foreach($libraryfaqfranchise as $val)
				{
					$franchise_id_arr[]=$val['franchise_id'];
					$franchise_id_label_arr[]=$val->franchise->usercompanyinfo?'OSP '.$val->franchise->usercompanyinfo->osp_number.' - '.$val->franchise->usercompanyinfo->osp_details:'';
				}
				$resultarr["franchise_id"]=$franchise_id_arr;
				$resultarr["franchise_id_label"]=implode(', ',$franchise_id_label_arr);
			}
						

			
			
            return ['data'=>$resultarr];
        }

	}
	
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
           	$model = LibraryFaq::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='FAQ has been activated successfully';
					}elseif($model->status==1){
						$msg='FAQ has been deactivated successfully';
					}elseif($model->status==2){
						$msg='FAQ has been deleted successfully';
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
        if (($model = LibraryFaq::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	*/
	
	
}
