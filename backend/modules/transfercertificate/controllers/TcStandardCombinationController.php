<?php
namespace app\modules\transfercertificate\controllers;

use Yii;
use app\modules\transfercertificate\models\TcStandardCombination;
use app\modules\transfercertificate\models\TcStandardLabelGrade;
use app\modules\transfercertificate\models\TcStandardCombinationStandard;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * TcStandardController implements the CRUD actions for Product model.
 */
class TcStandardCombinationController extends \yii\rest\Controller
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
		if(!Yii::$app->userrole->hasRights(array('tc_standard_combination')))
		{
			return false;
		}

		$post = yii::$app->request->post();		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelObj = new TcStandardCombination();		
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
			
			$model = TcStandardCombination::find()->where(['<>','status',2]);	
			
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

					$labelgradestandard = $modelData->standardcombinationstandard;
					if(count($labelgradestandard)>0)
					{
						$standard_id_arr = array();
						$standard_id_label_arr = array();
						foreach($labelgradestandard as $val)
						{
							if($val->standard!==null)
							{
								$standard_id_arr[]="".$val['tc_standard_id'];
								$standard_id_label_arr[]=($val->standard ? $val->standard->name : '');
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
		$modelTcStandardCombination = new TcStandardCombination();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$connection = Yii::$app->getDb();
		$datapost = Yii::$app->request->post();
		if ($datapost) 
		{	
			$editStatus=1;
			$condition = '';
			$data =json_decode($datapost['formvalues'],true);			
			if(isset($data['id']) && $data['id']>0)
			{
				$model = TcStandardCombination::find()->where(['id' => $data['id']])->one();
				if($model===null)
				{
					$model = new TcStandardCombination();
					$editStatus=0;
				}
				else
				{
					$condition.= ' and comb.id!='.$data['id'];
				}

			}else{
				$editStatus=0;
				$model = new TcStandardCombination();
			}
			
			$currentAction = 'add_tc_standard_combination';
			if($editStatus==1)
			{
				$currentAction = 'edit_tc_standard_combination';
			}

			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}
			
			//$model->name = $data['name'];				
			$userData = Yii::$app->userdata->getData();
			$model->created_by = $userData['userid'];	
			$standard_ids = $data['standard_id'];
			sort($standard_ids);
			 /*
			select GROUP_CONCAT(combstd.tc_standard_id order by combstd.tc_standard_id asc ) as standardids from tbl_tc_standard_combination as comb inner join tbl_tc_standard_combination_standard as combstd on comb.id=combstd.tc_standard_combination_id where 1=1 GROUP BY comb.id HAVING standardids = '3'
			 */
			//combstd.tc_standard_id in (".implode(',',$standard_ids).")
			$command = $connection->createCommand("select GROUP_CONCAT(combstd.tc_standard_id order by combstd.tc_standard_id asc ) as standardids from tbl_tc_standard_combination as comb inner join tbl_tc_standard_combination_standard as combstd on comb.id=combstd.tc_standard_combination_id where 1=1 GROUP BY comb.id HAVING standardids = '".implode(',',$standard_ids)."' $condition");

			$result = $command->queryOne();
			if($result  === false)
			{
				if($model->validate() && $model->save())
				{	
					$manualID = $model->id;
					
					if(is_array($data['standard_id']) && count($data['standard_id'])>0)
					{
						TcStandardCombinationStandard::deleteAll(['tc_standard_combination_id' => $model->id]);
						foreach ($data['standard_id'] as $value)
						{ 
							$TcStandardCombinationStandardModel =  new TcStandardCombinationStandard();
							$TcStandardCombinationStandardModel->tc_standard_combination_id = $model->id;
							$TcStandardCombinationStandardModel->tc_standard_id = $value;
							$TcStandardCombinationStandardModel->save();						
						}
					}
									
					$userMessage = 'Standard Combination has been created successfully';
					if($editStatus==1)
					{
						$userMessage = 'Standard Combination has been updated successfully';
					}				
					$responsedata=array('status'=>1,'message'=>$userMessage);	
				}
				else
				{
					$responsedata=array('status'=>0,'message'=>$model->errors);
				}
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>["standard_id"=>['This Combination has been taken already.!']]);	
			}		
		}
		return $this->asJson($responsedata);
	}	
	
	public function actionView()
    {
		if(!Yii::$app->userrole->hasRights(array('tc_standard_combination')))
		{
			return false;
		}

		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelObj = new TcStandardCombination();

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
        if (($model = TcStandardCombination::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	public function actionDeletedata()
	{
		if(!Yii::$app->userrole->hasRights(array('delete_tc_standard_combination')))
		{
			return false;
		}
		
		$data = Yii::$app->request->post();
		if($data)
		{			
			$id = $data['id'];
			TcStandardCombinationStandard::deleteAll(['tc_standard_combination_id' => $data['id']]);
			$TcStandardCombinationModel = TcStandardCombination::find()->where(['id'=>$id])->one();
			if($TcStandardCombinationModel!==null)
			{
				$TcStandardCombinationModel->delete();
			}	
		}
		return $responsedata=array('status'=>1,'message'=>'Data deleted successfully');
	}
	
	
}
