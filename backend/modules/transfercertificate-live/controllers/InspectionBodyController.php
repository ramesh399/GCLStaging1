<?php
namespace app\modules\transfercertificate\controllers;

use Yii;
use app\modules\transfercertificate\models\InspectionBody;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * InspectionBodyController implements the CRUD actions for Product model.
 */
class InspectionBodyController extends \yii\rest\Controller
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
		$modelObj = new InspectionBody();		
		if($post && isset($post['type']) && $post['type']!='')
		{
			if($post['type']=='inspection')
			{
				$currentAction = 'inspection_body';				
			}elseif($post['type']=='certification'){
				$currentAction = 'certification_body';				
			}	

			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}
			

			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$is_headquarters =$userData['is_headquarters'];
			$franchiseid=$userData['franchiseid'];
			$role_chkid=$userData['role_chkid'];
			
			$model = InspectionBody::find()->where(['<>','t.status',$modelObj->enumStatus['archived']])->alias('t');	
			$model = $model->andWhere(['t.type'=> $post['type']]);			
			if($resource_access != '1')
			{
				/*
				if($user_type== Yii::$app->params['user_type']['customer']){
					$model = $model->andWhere('t.created_by="'.$userid.'"');
				}	
				if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1 ){
					$model = $model->andWhere(' createdbydata.franchise_id="'.$franchiseid.'" ');
				}
				*/
			}
			if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
			{
				$page = ($post['page'] - 1)*$post['pageSize'];
				$pageSize = $post['pageSize']; 
				
				$statusarray=array_map('strtolower', $modelObj->arrStatus);
				if(isset($post['statusFilter']) && $post['statusFilter']>='0')
				{
					$model->andWhere(['t.status'=> $post['statusFilter']]);
				}
				if(isset($post['searchTerm']))
				{
					$searchTerm = $post['searchTerm'];					
					$search_status = array_search(strtolower($searchTerm),$statusarray);
					
					if($search_status===false)
					{
						$search_status='';
					}
					
					$model = $model->andFilterWhere([
						'or',
						['like', 'name', $searchTerm],						
						['like', 'description', $searchTerm],						
						['like', 'date_format(FROM_UNIXTIME(`created_at` ), \'%b %d, %Y\' )', $searchTerm],
						['status'=>$search_status]
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
			
			$list=array();
			$model = $model->all();		
			if(count($model)>0)
			{
				foreach($model as $modelData)
				{	
					$data=array();
					$data['id']=$modelData->id;
					$data['name']=$modelData->name;					
					$data['description']=$modelData->description;					
					$data['created_by_label']=$modelData->createdbydata->first_name.' '.$modelData->createdbydata->last_name;					
					$data['status']=$modelData->status;
					$data['status_label']=$modelObj->arrStatus[$modelData->status];
					$data['created_at']=date($date_format,$modelData->created_at);				

					$list[]=$data;
				}
			}
		}
		return ['inspectionbody'=>$list,'total'=>$totalCount];
    }

    public function actionCreate()
	{
		$modelInspectionBody = new InspectionBody();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		//$data = Yii::$app->request->post();
		$datapost = Yii::$app->request->post();
		if ($datapost) 
		{	
			$editStatus=1;
			$userData = Yii::$app->userdata->getData();
			$data =json_decode($datapost['formvalues'],true);			
			if(isset($data['id']) && $data['id']>0)
			{
				$model = InspectionBody::find()->where(['id' => $data['id']])->one();
				if($model===null)
				{
					$model = new InspectionBody();
					$editStatus=0;
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$editStatus=0;
				$model = new InspectionBody();
				$model->created_by = $userData['userid'];
			}
			
			if($data['type']=='inspection')
			{
				$currentAction = 'add_inspection_body';
				if($editStatus==1)
				{
					$currentAction = 'edit_inspection_body';
				}
			}elseif($data['type']=='certification'){
				$currentAction = 'add_certification_body';
				if($editStatus==1)
				{
					$currentAction = 'edit_certification_body';
				}
			}	

			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}
			
			$model->name = $data['name'];				
			$model->description = $data['description'];	
			$model->type = $data['type'];			
			
						
			if($model->validate() && $model->save())
			{	
				$manualID = $model->id;

				$msg='';
				if($model->type=='inspection')
				{
					$msg='Inspection Body';				
				}elseif($model->type=='certification'){
					$msg = 'Certification Body';					
				}
								
				$userMessage =$msg.' has been created successfully';
				if($editStatus==1)
				{
					$userMessage = $msg.' has been updated successfully';
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
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelObj = new InspectionBody();

        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			if($model->type=='inspection')
			{
				$currentAction = 'view_inspection_body';				
			}elseif($model->type=='certification'){
				$currentAction = 'view_certification_body';				
			}	

			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}			
			
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$resultarr["name"]=$model->name;
			$resultarr["description"]=$model->description;			
            return ['data'=>$resultarr];
        }

	}	
	
	public function actionGetData()
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
		$InspectionBody = new InspectionBody();
		$modelInspection = InspectionBody::find()->select('id,name')->alias('t')->where(['type' => 'inspection','status'=>$InspectionBody->enumStatus['approved'] ]);
		$modelCertification = InspectionBody::find()->select('id,name')->alias('t')->where(['type' => 'certification','status'=>$InspectionBody->enumStatus['approved'] ]);
		
		/*
		if($resource_access != '1')
		{
			if($user_type== Yii::$app->params['user_type']['customer']){
				$modelInspection = $modelInspection->andWhere('t.created_by="'.$userid.'"');
				$modelCertification = $modelCertification->andWhere('t.created_by="'.$userid.'"');
			}	
		}
		*/
		
		$modelInspection = $modelInspection->asArray()->all();
		$modelCertification = $modelCertification->asArray()->all();
		return['inspectionlist'=>$modelInspection,'certificationlist'=>$modelCertification];
	}

    

    protected function findModel($id)
    {
        if (($model = InspectionBody::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	public function actionDeletedata()
	{		
		$data = Yii::$app->request->post();
		if($data)
		{			
			$id = $data['id'];
			$InspectionBodyModel = InspectionBody::find()->where(['id'=>$id])->one();
			if($InspectionBodyModel!==null)
			{
				if($InspectionBodyModel->type=='inspection')
				{
					$currentAction = 'delete_inspection_body';				
				}elseif($InspectionBodyModel->type=='certification'){
					$currentAction = 'delete_certification_body';				
				}	

				if(!Yii::$app->userrole->hasRights(array($currentAction)))
				{
					return false;
				}

				//$InspectionBodyModel->delete();
				$InspectionBodyModel->status = $InspectionBodyModel->enumStatus['archived'];
				$InspectionBodyModel->save();
			}	
		}
		return $responsedata=array('status'=>1,'message'=>'Data deleted successfully');
	}
	
	
}
