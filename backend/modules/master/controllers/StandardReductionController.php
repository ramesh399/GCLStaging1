<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\StandardReduction;
use app\modules\master\models\StandardReductionRate;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * StandardReductionController implements the CRUD actions for Mandaycost model.
 */
class StandardReductionController extends \yii\rest\Controller
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
		if(!Yii::$app->userrole->hasRights(array('standard_reduction_master')))
		{
			return false;
		}
		
		$post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$model = StandardReduction::find()->where(['<>','t.status',2])->alias('t');
		$model->joinWith('standard as std');
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 'std.name', $searchTerm],
					['like', '(date_format(FROM_UNIXTIME(t.created_at), \'%b %d, %Y\' ))', $searchTerm],
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
				$model = $model->orderBy(['std.priority' => SORT_ASC]);
			}
			



            $model = $model->limit($pageSize)->offset($page);
		}
		else
		{
			$totalCount = $model->count();
		}
		
		$standardreduction_list=array();
		//$model->Where(['<>','t.status',2]);
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $standardreduction)
			{
				$data=array();
				$data['id']=$standardreduction->id;
				$data['standard_id']=$standardreduction->standard_id;
				$data['standard_name']=$standardreduction->standard? $standardreduction->standard->name:'';
				//$data['man_day_cost']=$standardreduction->man_day_cost;
				$data['status']=$standardreduction->status;
				$data['created_at']=date($date_format,$standardreduction->created_at);
				$standardreduction_list[]=$data;
			}
		}
		
		return ['standardreductions'=>$standardreduction_list,'total'=>$totalCount];
    }

    public function actionCreate()
	{
		if(!Yii::$app->userrole->hasRights(array('add_standard_reduction')))
		{
			return false;
		}

		$model = new StandardReduction();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();
			
			$model->standard_id=$data['standard_id'];

			$userData = Yii::$app->userdata->getData();
			$model->created_by=$userData['userid'];	

			if($model->validate() && $model->save())
			{
				if(is_array($data['reduction']) && count($data['reduction'])>0)
				{
					foreach ($data['reduction'] as $value)
					{ 
						$ratemodel=new StandardReductionRate();
						$ratemodel->standard_reduction_id=$model->id;
						$ratemodel->standard_id=$value['standard_id'];
						$ratemodel->reduction_percentage=$value['reduction_percentage'];						
						$ratemodel->save();
						
					}
				}
				
				$responsedata=array('status'=>1,'message'=>'Standard reduction rate has been created successfully');	
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}
		}
		return $this->asJson($responsedata);
	}

    public function actionUpdate()
    {
		if(!Yii::$app->userrole->hasRights(array('edit_standard_reduction')))
		{
			return false;
		}

		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
           	$model = StandardReduction::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->standard_id=$data['standard_id'];

				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];
				
				if($model->validate() && $model->save())
				{
					StandardReductionRate::deleteAll(['standard_reduction_id' => $model->id]);
					if(is_array($data['reduction']) && count($data['reduction'])>0)
					{
						foreach ($data['reduction'] as $value)
						{ 
							$ratemodel=new StandardReductionRate();
							$ratemodel->standard_reduction_id=$model->id;
							$ratemodel->standard_id=$value['standard_id'];
							$ratemodel->reduction_percentage=$value['reduction_percentage'];						
							$ratemodel->save();
							
						}
					}
					$responsedata=array('status'=>1,'message'=>'Standard reduction rate has been updated successfully');
				}
				else
				{
					$responsedata=array('status'=>0,'message'=>$model->errors);
				}
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}
            return $this->asJson($responsedata);
        }
    }

    public function actionView()
    {
		if(!Yii::$app->userrole->hasRights(array('standard_reduction_master')))
		{
			return false;
		}

		$data = Yii::$app->request->post();
		
        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$standard=$model->standardreduction;
			$arrStandard=[];
			if(count($standard)>0)
			{			
				foreach($standard as $std)
				{
					if($std->standard->status==0){
						$arrStandard[]=array('reduction_standard_id'=>$std->standard_id,'reduction_standard_name'=>$std->standard->name,'reduction_percentage'=>$std->reduction_percentage);
					}					
				}			
			}
            return ['data'=>$model,'standardreduction'=>$arrStandard];
			
        }
    }
	
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data && isset($data['status'])) 
		{
			$status = $data['status'];

			if(!Yii::$app->userrole->canDoCommonUpdate($status,'standard_reduction'))
			{
				return false;
			}	

           	$model = StandardReduction::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Standard Reduction has been activated successfully';
					}elseif($model->status==1){
						$msg='Standard Reduction has been deactivated successfully';
					}elseif($model->status==2){
						$msg='Standard Reduction has been deleted successfully';
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
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}
            return $this->asJson($responsedata);
        }
	}
	/**
     * Finds the Country model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Country the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StandardReduction::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
}
