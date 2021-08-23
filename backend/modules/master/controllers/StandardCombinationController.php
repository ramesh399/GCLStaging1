<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\StandardCombination;
use app\modules\master\models\StandardLabelGrade;
use app\modules\master\models\StandardCombinationStandard;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * StandardCombinationController implements the CRUD actions for Product model.
 */
class StandardCombinationController extends \yii\rest\Controller
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
		$modelObj = new StandardCombination();		
		if($post)
		{
			$currentAction = 'standard_combination_master';				
			
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
			
			$model = StandardCombination::find()->where(['<>','status',2]);	
			
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
					$data['declaration_content']=$modelData->declaration_content;					
					$data['created_by_label']=$modelData->createdbydata->first_name.' '.$modelData->createdbydata->last_name;										
					$data['created_at']=date($date_format,$modelData->created_at);

					$labelgradestandard = $modelData->standardcombinationstandard;
					if(count($labelgradestandard)>0)
					{
						$standard_id_arr = array();
						$standard_id_label_arr = array();
						foreach($labelgradestandard as $val)
						{
							if($val->standard!==null)
							{
								$standard_id_arr[]="".$val['standard_id'];
								$standard_id_label_arr[]=($val->standard ? $val->standard->code : '');
							}
						}
						$data["standard_id"]=$standard_id_arr;
						$data["standard_id_label"]=implode(', ',$standard_id_label_arr);
					}
				
					$list[]=$data;
				}
			}
		}
		return ['standardCombination'=>$list,'total'=>$totalCount];
    }

    public function actionCreate()
	{
		$modelStandardCombination = new StandardCombination();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		//$data = Yii::$app->request->post();
		$datapost = Yii::$app->request->post();
		if ($datapost) 
		{	
			$editStatus=1;
			$condition = '';
			$data =json_decode($datapost['formvalues'],true);			
			if(isset($data['id']) && $data['id']>0)
			{
				$model = StandardCombination::find()->where(['id' => $data['id']])->one();
				if($model===null)
				{
					$model = new StandardCombination();
					$editStatus=0;
				}else{
					$condition.= ' and comb.id!='.$data['id'];
				}
			}else{
				$editStatus=0;
				$model = new StandardCombination();
			}	


			$currentAction = 'add_standard_combination';
			if($editStatus==1)
			{
				$currentAction = 'edit_standard_combination';
			}

			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}
			
			$model->declaration_content = $data['declaration_content'];				
			$userData = Yii::$app->userdata->getData();
			$model->created_by = $userData['userid'];
			
			$standard_ids = $data['standard_id'];
			sort($standard_ids);
			$connection = Yii::$app->getDb();			
			$command = $connection->createCommand("select GROUP_CONCAT(combstd.standard_id order by combstd.standard_id asc ) as standardids from tbl_standard_combination as comb inner join tbl_standard_combination_standard as combstd on comb.id=combstd.standard_combination_id where 1=1 GROUP BY comb.id HAVING standardids = '".implode(',',$standard_ids)."' $condition");
			$result = $command->queryOne();
			if($result  === false)
			{				
				if($model->validate() && $model->save())
				{	
					$manualID = $model->id;
					
					if(is_array($data['standard_id']) && count($data['standard_id'])>0)
					{
						StandardCombinationStandard::deleteAll(['standard_combination_id' => $model->id]);
						foreach ($data['standard_id'] as $value)
						{ 
							$StandardCombinationStandardModel =  new StandardCombinationStandard();
							$StandardCombinationStandardModel->standard_combination_id = $model->id;
							$StandardCombinationStandardModel->standard_id = $value;
							$StandardCombinationStandardModel->save();						
						}
					}
									
					$userMessage = 'Standard Combination has been created successfully';
					if($editStatus==1)
					{
						$userMessage = 'Standard Combination has been updated successfully';
					}				
					$responsedata=array('status'=>1,'message'=>$userMessage);	
				}
			}else{
				$responsedata=array('status'=>0,'message'=>["standard_id"=>['This Combination has been taken already.!']]);	
			}		
		}
		return $this->asJson($responsedata);
	}	
	
	public function actionView()
    {
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelObj = new StandardCombination();

        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$currentAction = 'view_standard_combination';

			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}

			$resultarr=array();
			$resultarr["id"]=$model->id;
			$resultarr["name"]=$model->name;	
			$resultarr["declaration_content"]=$model->declaration_content;			
            return ['data'=>$resultarr];
        }

	}
	
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
           	$model = StandardCombination::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Standard Combination has been activated successfully';
					}elseif($model->status==1){
						$msg='Standard Combination has been deactivated successfully';
					}elseif($model->status==2){
						$msg='Standard Combination has been deleted successfully';
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
        if (($model = StandardCombination::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }	
	
	public function actionDeletedata()
	{
		$data = Yii::$app->request->post();
		if($data)
		{	
			$currentAction = 'delete_standard_combination';

			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}

			$id = $data['id'];
			StandardCombinationStandard::deleteAll(['standard_combination_id' => $data['id']]);
			$StandardCombinationModel = StandardCombination::find()->where(['id'=>$id])->one();
			if($StandardCombinationModel!==null)
			{
				$StandardCombinationModel->delete();
			}	
		}
		return $responsedata=array('status'=>1,'message'=>'Data deleted successfully');
	}
	
	
}
