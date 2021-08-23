<?php
namespace app\modules\library\controllers;

use Yii;
use app\modules\library\models\LibraryManual;
use app\modules\library\models\LibraryManualFile;
use app\modules\library\models\LibraryManualAccess;
use app\modules\library\models\LibraryUserAccess;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * ManualController implements the CRUD actions for Product model.
 */
class ManualController extends \yii\rest\Controller
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

		$modelObj = new LibraryManual();

		$model = LibraryManual::find()->alias('t')->where(['<>','status',2]);
		/*
		$model = $model->join('inner join', 'tbl_library_manual_access as manual_access','manual_access.manual_id=t.id');
		
		if(isset($post['roleFilter']) && is_array($post['roleFilter']) && count($post['roleFilter'])>0)
		{
			$model = $model->andWhere(['manual_access.user_access'=> $post['roleFilter']]);				
		}
		*/
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			$typearray=array_map('strtolower', $modelObj->arrType);
			$statusarray=array_map('strtolower', $modelObj->arrStatus);

			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 't.title', $searchTerm],
					['like', 'date_format(FROM_UNIXTIME(`t.document_date` ), \'%b %d, %Y\' )', $searchTerm],
					['like', 'date_format(FROM_UNIXTIME(`t.created_at` ), \'%b %d, %Y\' )', $searchTerm],
				]);
				$search_type = array_search(strtolower($searchTerm),$typearray);

				if($search_type!==false)
				{
					$model = $model->orFilterWhere([
                        'or', 					
						['type'=>$search_type]								
					]);
				}

				$search_status = array_search(strtolower($searchTerm),$statusarray);
				
				if($search_status!==false)
				{
					$model = $model->orFilterWhere([
                        'or', 					
						['status'=>$search_status]								
					]);
				}

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
		
		$list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $modelData)
			{
				$data=array();
				$data['id']=$modelData->id;
				$data['title']=$modelData->title;
				$data['version']=$modelData->version;
				$data['document_date']=date($date_format,strtotime($modelData->document_date));
				$data['description']=$modelData->description;
				$data['reviewer']=$modelData->reviewer;
				//$data['reviewer_label']=$modelObj->arrType[$modelData->type];
				$data['status']=$modelData->status;
				$data['status_label']=$modelObj->arrStatus[$modelData->status];
				$data['created_at']=date($date_format,$modelData->created_at);
				$list[]=$data;
			}
		}

		return ['manuallogs'=>$list,'total'=>$totalCount, 'typelist'=>$modelObj->arrType, 'statuslist'=>$modelObj->arrStatus];
    }

    public function actionCreate()
	{
		$model = new LibraryManual();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$model->title = $data['title'];	
			$model->version = $data['version'];	
			$model->document_date = date('Y-m-d',strtotime($data['document_date']));	
			$model->reviewer = $data['reviewer'];	
			$model->description = $data['description'];	
			$model->status = $data['status'];	
			$userData = Yii::$app->userdata->getData();
			$model->created_by = $userData['userid'];
			
			if($model->validate() && $model->save())
			{	
				$manualID = $model->id;
				if(is_array($data['manual_access']) && count($data['manual_access'])>0)
                {
                    foreach ($data['manual_access'] as $value)
                    { 
						$LibraryManualAccessModel =  new LibraryManualAccess();
						$LibraryManualAccessModel->manual_id = $manualID;
						$LibraryManualAccessModel->user_access = $value;
						$LibraryManualAccessModel->save();
					}
				}
				
				$target_dir = Yii::$app->params['user_files']; 				
				if(is_array($data['documents']) && count($data['documents'])>0)
				{
					foreach ($data['documents'] as $key => $value)
					{ 
						$filename = '';
						if(isset($_FILES['document']['name'][$key]))
						{
							$tmp_name = $_FILES["document"]["tmp_name"][$key];
							$name = $_FILES["document"]["name"][$key];
							$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
						}else{
							$filename = $value['document'];
						}
						
						$qualmodel=new LibraryManualFile();
						$qualmodel->manual_id=$manualID;						
						$qualmodel->document=$filename;						
						$qualmodel->save();
					}					
				}
				$responsedata=array('status'=>1,'message'=>'Manual has been created successfully');	
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
			$model = LibraryManual::find()->where(['id' => $data['id']])->one();
			$model->title = $data['title'];	
			$model->version = $data['version'];
			$model->document_date = date('Y-m-d',strtotime($data['document_date']));	
			$model->reviewer = $data['reviewer'];	
			$model->description = $data['description'];	
			$model->status = $data['status'];	
			$userData = Yii::$app->userdata->getData();
			$model->updated_by = $userData['userid'];
			
			if($model->validate() && $model->save())
			{
				$manualID = $model->id;
				
				LibraryManualAccess::deleteAll(['manual_id' => $manualID]);
				if(is_array($data['manual_access']) && count($data['manual_access'])>0)
                {
                    foreach ($data['manual_access'] as $value)
                    { 
						$LibraryManualAccessModel =  new LibraryManualAccess();
						$LibraryManualAccessModel->manual_id = $manualID;
						$LibraryManualAccessModel->user_access = $value;
						$LibraryManualAccessModel->save();
					}
				}
				
				$target_dir = Yii::$app->params['user_files']; 
				LibraryManualFile::deleteAll(['manual_id' => $manualID]);
				if(is_array($data['documents']) && count($data['documents'])>0)
				{
					foreach ($data['documents'] as $key => $value)
					{ 
						$filename = '';
						if(isset($_FILES['document']['name'][$key]))
						{
							$tmp_name = $_FILES["document"]["tmp_name"][$key];
							$name = $_FILES["document"]["name"][$key];
							$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
						}else{
							$filename = $value['document'];
						}
						
						$qualmodel=new LibraryManualFile();
						$qualmodel->manual_id=$manualID;						
						$qualmodel->document=$filename;						
						$qualmodel->save();
					}
					
				}
				
				$responsedata=array('status'=>1,'message'=>'Manual has been updated successfully');	
			}
		}
		return $this->asJson($responsedata);
    }
	
	public function actionView()
    {
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelObj = new LibraryManual();

        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$resultarr["title"]=$model->title;
			$resultarr["version"]=$model->version;
			$resultarr["document_date"]=date($date_format,$model->document_date);
			$resultarr["reviewer"]=$model->reviewer;
			//$resultarr["reviewer_label"]=$modelObj->arrType[$model->type];
			$resultarr["description"]=$model->description;			
			$resultarr["status"]=$model->status;
			$resultarr["status_label"]=$modelObj->arrStatus[$model->status];

			
            return ['data'=>$resultarr];
        }

	}
	
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
           	$model = LibraryManual::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Manual has been activated successfully';
					}elseif($model->status==1){
						$msg='Manual has been deactivated successfully';
					}elseif($model->status==2){
						$msg='Manual has been deleted successfully';
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
        if (($model = LibraryManual::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	
}
