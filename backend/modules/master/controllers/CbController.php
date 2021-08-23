<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\Cb;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;


/**
 * CbController implements the CRUD actions for Cb model.
 */
class CbController extends \yii\rest\Controller
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

		if(!Yii::$app->userrole->hasRights(array('cb_master')))
		{
			return false;
		}

		$post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$model = Cb::find()->where(['<>','status',2]);
		
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
		
		$Cb_list=array();
		//$model = $model->asArray()->all();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $sectors)
			{
				$data=array();
				$data['id']=$sectors->id;
				$data['name']=$sectors->name;
				$data['status']=$sectors->status;
				//$data['created_at']=date('M d,Y h:i A',$process->created_at);
				$data['created_at']=date($date_format,$sectors->created_at);
				$Cb_list[]=$data;
			}
		}
		
		return ['bsectors'=>$Cb_list,'total'=>$totalCount];
    }
	
	public function actionGetCb()
    {
		$post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$model = Cb::find()->where(['status'=>0]);
		
		$Cb_list=array();
		//$model = $model->asArray()->all();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $sectors)
			{
				$data=array();
				$data['id']=$sectors->id;
				$data['name']=$sectors->name;
				$data['status']=$sectors->status;
				$data['created_at']=date($date_format,$sectors->created_at);
				$Cb_list[]=$data;
			}
		}
		
		return ['cbs'=>$Cb_list];
	}
	
	
	

    public function actionCreate()
	{
		if(!Yii::$app->userrole->hasRights(array('add_cb')))
		{
			return false;
		}
		$model = new Cb();		
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{		
			$model->name=$data['name'];
			$model->description=$data['description'];
			
			$userData = Yii::$app->userdata->getData();
			$model->created_by=$userData['userid'];
			
			if($model->validate() && $model->save())
			{
				$responsedata=array('status'=>1,'message'=>'CB has been created successfully');	
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
		if(!Yii::$app->userrole->hasRights(array('edit_cb')))
		{
			return false;
		}
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
           	$model = Cb::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->name=$data['name'];
				$model->description=$data['description'];
				
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];
			
				if($model->validate() && $model->save())
				{
					$responsedata=array('status'=>1,'message'=>'CB has been updated successfully');
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
		if(!Yii::$app->userrole->hasRights(array('cb_master')))
		{
			return false;
		}
		$data = Yii::$app->request->post();
		
        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
            return ['data'=>$model];
        }

    }
	
	public function actionCommonUpdate()
	{
		
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');

		if ($data && isset($data['status'])) 
		{
			$status = $data['status'];			
			if(!Yii::$app->userrole->canDoCommonUpdate($status,'cb'))
			{
				return false;
			}	

			$id=$data['id'];
           	$model = Cb::find()->where(['id' => $id])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='CB has been activated successfully';
					}elseif($model->status==1){
						$msg='CB has been deactivated successfully';
					}elseif($model->status==2){

						$exists=0;

                        // if(UserProcess::find()->where( [ 'process_id' => $id ] )->exists()){
                        //     $exists=1;
                        // }elseif(ApplicationUnitProcess::find()->where( [ 'process_id' => $id ] )->exists()){
                        //     $exists=1;
                        // }elseif(QualificationQuestionProcess::find()->where( [ 'process_id' => $id ] )->exists()){
                        //     $exists=1;
                        // }else{
                        //     $exists=0;
                        // }
						
						if($exists==0)
                        {
                            //Process::findOne($id)->delete();
                        }
						$msg='CB has been deleted successfully';
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
        if (($model = Cb::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
   
}
