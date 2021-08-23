<?php
namespace app\modules\transfercertificate\controllers;

use Yii;
use app\modules\transfercertificate\models\TcStandard;
use app\modules\transfercertificate\models\Transport;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * TcStandardController implements the CRUD actions for Product model.
 */
class TcStandardController extends \yii\rest\Controller
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
		if(!Yii::$app->userrole->hasRights(array('tc_standard')))
		{
			return false;
		}

		$post = yii::$app->request->post();		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelObj = new TcStandard();		
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
			
			$model = TcStandard::find()->where(['<>','status',$modelObj->enumStatus['archived']]);	
			
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
					$data['name']=$modelData->name;					
					$data['created_by_label']=$modelData->createdbydata->first_name.' '.$modelData->createdbydata->last_name;										
					$data['created_at']=date($date_format,$modelData->created_at);	
					$list[]=$data;
				}
			}
		}
		return ['standard'=>$list,'total'=>$totalCount];
    }

    public function actionCreate()
	{
		

		$modelTcStandard = new TcStandard();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		//$data = Yii::$app->request->post();
		$datapost = Yii::$app->request->post();
		if ($datapost) 
		{	
			$editStatus=1;
			
			$data =json_decode($datapost['formvalues'],true);			
			if(isset($data['id']) && $data['id']>0)
			{
				$model = TcStandard::find()->where(['id' => $data['id']])->one();
				if($model===null)
				{
					$model = new TcStandard();
					$editStatus=0;
				}
			}else{
				$editStatus=0;
				$model = new TcStandard();
			}
			
			$currentAction = 'add_tc_standard';
			if($editStatus==1)
			{
				$currentAction = 'edit_tc_standard';
			}

			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}
			
			$model->name = $data['name'];				
			$userData = Yii::$app->userdata->getData();
			$model->created_by = $userData['userid'];			
			if($model->validate() && $model->save())
			{	
				$manualID = $model->id;
								
				$userMessage = 'Standard has been created successfully';
				if($editStatus==1)
				{
					$userMessage = 'Standard has been updated successfully';
				}				
				$responsedata=array('status'=>1,'message'=>$userMessage);	
			}else{
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}		
		}
		return $this->asJson($responsedata);
	}	
	
	public function actionView()
    {
		if(!Yii::$app->userrole->hasRights(array('tc_standard')))
		{
			return false;
		}

		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelObj = new TcStandard();

        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$resultarr["name"]=$model->name;					
            return ['data'=>$resultarr];
        }
	}  

    protected function findModel($id)
    {
        if (($model = TcStandard::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	public function actionDeletedata()
	{
		if(!Yii::$app->userrole->hasRights(array('delete_tc_standard')))
		{
			return false;
		}
		
		$data = Yii::$app->request->post();
		if($data)
		{			
			$id = $data['id'];
			$TcStandardModel = TcStandard::find()->where(['id'=>$id])->one();
			if($TcStandardModel!==null)
			{
				//$TcStandardModel->delete();
				$TcStandardModel->status = $TcStandardModel->enumStatus['archived'];
				$TcStandardModel->save();
			}	
		}
		return $responsedata=array('status'=>1,'message'=>'Data deleted successfully');
	}
	
	public function actionGetStandard()
	{
		$Standard = TcStandard::find()->select(['id','name','code'])->where(['status'=>0])->asArray()->all();
		return ['standards'=>$Standard];
	}
}
