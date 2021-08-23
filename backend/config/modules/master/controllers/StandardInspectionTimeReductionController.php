<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\StandardInspectionTimeReduction;
use app\modules\master\models\StandardInspectionTimeReductionStandard;


use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * DownloadController implements the CRUD actions for Product model.
 */
class StandardInspectionTimeReductionController extends \yii\rest\Controller
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
		$modelObj = new StandardInspectionTimeReduction();	
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');	
		if($post)
		{
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$is_headquarters =$userData['is_headquarters'];
			$franchiseid=$userData['franchiseid'];
			$role_chkid=$userData['role_chkid'];
			
			$model = StandardInspectionTimeReduction::find()->alias('t');
			
			if($resource_access != '1')
			{
				if($user_type != Yii::$app->params['user_type']['user'] ){
					//&& ! in_array('user_master',$rules)
					return $responsedata;
				}
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
						['like', 'reduction_percentage', $searchTerm],
						['like', 'date_format(FROM_UNIXTIME(`created_at` ), \'%b %d, %Y\' )', $searchTerm]
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
			
			$list=array();
			$model = $model->all();		
			if(count($model)>0)
			{
				foreach($model as $modelData)
				{	
					$data=array();
					$data['id']=$modelData->id;
					$data['reduction_percentage']=$modelData->reduction_percentage;
					$data['created_by']=$modelData->created_by;
					$data['updated_by']=$modelData->updated_by;
					$data['created_at']=date($date_format,$modelData->created_at);
					$data['updated_at']=date($date_format,$modelData->updated_at);
										
					$reductionstandardModel= $modelData->reductionstandard;
					if(count($reductionstandardModel)>0){
						foreach ($reductionstandardModel as $reductionstandard) {
							$data['standards'][] = "$reductionstandard->standard_id";
							$data['standard_label'][] = $reductionstandard->standard->code;
						}
					}					
					$data['standard_label'] = implode(', ',$data['standard_label']);
					$list[]=$data;
				}
			}
		}
		return ['inspectiontimereduction'=>$list,'total'=>$totalCount];
    }

    public function actionCreate()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		//$data = Yii::$app->request->post();
		$datapost = Yii::$app->request->post();
		if ($datapost) 
		{	
			$data =json_decode($datapost['formvalues'],true);	
			$editStatus=1;
			$userData = Yii::$app->userdata->getData();
			
			if(isset($data['id']) && $data['id']>0 )
			{
				$model = StandardInspectionTimeReduction::find()->where(['id' => $data['id']])->one();
				if($model===null)
				{
					$model = new StandardInspectionTimeReduction();
					$editStatus=0;
				}
			}else{
				$editStatus=0;
				$model = new StandardInspectionTimeReduction();
				
			}	
			if($editStatus ===0){
				$model->created_by = $userData['userid'];
			}else{
				$model->updated_by = $userData['userid'];
			}
			$model->reduction_percentage = $data['reduction_percentage'];				
			
			if($model->validate() && $model->save())
			{	
				if($editStatus==1)
				{
					StandardInspectionTimeReductionStandard::deleteAll(['inspection_time_reduction_standard_id' => $model->id]);
				}
				if(is_array($data['standards']) && count($data['standards'])>0)
				{
					foreach ($data['standards'] as $value)
					{ 
						$StandardModel =  new StandardInspectionTimeReductionStandard();
						$StandardModel->inspection_time_reduction_standard_id = $model->id;
						$StandardModel->standard_id = $value;
						$StandardModel->save();
					}
				}	
				$userMessage ='Standard inspection time reduction has been created successfully';
				if($editStatus==1)
				{
					$userMessage ='Standard inspection time reduction has been updated successfully';
				}
				
				$responsedata=array('status'=>1,'message'=>$userMessage);	
			}
		}
		return $this->asJson($responsedata);
	}
	/*
	public function actionView()
    {
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelObj = new LibraryDownload();

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
	*/
	 

    protected function findModel($id)
    {
        if (($model = StandardInspectionTimeReduction::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	/*
	public function actionGetData()
	{
		$modelObj = new LibraryDownload();		
		$Standard = Standard::find()->select(['id','name','code'])->where(['status'=>0])->asArray()->all();				
		//$UserAccess = LibraryUserAccess::find()->select(['id','name'])->where(['status'=>0])->asArray()->all();
		$UserAccess = Yii::$app->globalfuns->getUserRoles();
				
		$arrReviewer=array();
		 
		$reviewers =Yii::$app->globalfuns->getReviewers();
		if(count($reviewers)>0)
		{
			foreach($reviewers as $reviewer)
			{
				$arrReviewer[$reviewer['id']]=$reviewer['first_name'].' '.$reviewer['last_name'];
			}
		}	
		
		return ['useraccess'=>$UserAccess,'status'=>$modelObj->arrStatus,'enumstatus'=>$modelObj->enumStatus,'typelist'=>$modelObj->arrType,'standard'=>$Standard,'reviewerList'=>$arrReviewer];
	}
	*/
	
	public function actionDeletedata()
	{
		$data = Yii::$app->request->post();
		if($data)
		{			
			$id = $data['id'];
			StandardInspectionTimeReduction::deleteAll(['id' => $id]);
			StandardInspectionTimeReductionStandard::deleteAll(['inspection_time_reduction_standard_id' => $id]);				
		}
		return $responsedata=array('status'=>1,'message'=>'Data deleted successfully');
	}
	
}
