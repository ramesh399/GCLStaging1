<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\Standard;
use app\modules\application\models\ApplicationProductStandard;
use app\modules\master\models\StandardLabelGrade;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;
/**
 * StandardLabelGradeController implements the CRUD actions for Standard model.
 */
class StandardLabelGradeController extends \yii\rest\Controller
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
			'authenticator' => ['class' => JwtHttpBearerAuth::class]
		];        
    }

    public function actionIndex()
    {
		if(!Yii::$app->userrole->hasRights(array('standard_label_grade_master')))
		{
			return false;
		}

        //$model = Standard::find()->select(['id','name'])->asArray()->all();
        //return ['data'=>$model];
		$post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$model = StandardLabelGrade::find()->alias('t');
		$model->joinWith(['standard as std']);
		$model->andWhere(['<>','t.status',2]);
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 't.name', $searchTerm],
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
			foreach($model as $obj)
			{
				$data=array();
				$data['id']=$obj->id;
				$data['name']=$obj->name;
				$data['type']=$obj->standard->name;
				$data['status']=$obj->status;
				$data['created_at']=date($date_format,$obj->created_at);
				$list[]=$data;
			}
		}
		
		return ['standardlabelgrades'=>$list,'total'=>$totalCount];
	}
	public function actionList()
    {
		$post = yii::$app->request->post();
		$standard_id = $post['standard_id'];

		$list = StandardLabelGrade::find()->select(['id','name'])->where(['status'=>0,'standard_id'=>$standard_id])->asArray()->all();
		return ['data'=>$list,'total'=>count($list)];
    }
    public function actionCreate()
    {
		if(!Yii::$app->userrole->hasRights(array('add_standard_label_grade')))
		{
			return false;
		}

		$data = Yii::$app->request->post();
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
			$model = new StandardLabelGrade();
			$model->standard_id=$data['standard_id'];
			$model->name=$data['name'];
			//$model->code='';
			//$model->description=$data['description'];
			if($model->validate() && $model->save())
			{
				$responsedata=array('status'=>1,'message'=>'Standard label grade has been created successfully');
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
		if(!Yii::$app->userrole->hasRights(array('edit_standard_label_grade')))
		{
			return false;
		}
        $data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
			$model = StandardLabelGrade::find()->where(['id' => $data['id']])->one();
            if ($model !== null)
			{
				$model->standard_id=$data['standard_id'];
				$model->name=$data['name'];
				//$model->code='';
				//$model->description=$value['description'];			
				if($model->validate() && $model->save())
				{
					$responsedata=array('status'=>1,'message'=>'Standard label grade has been updated successfully');
				} 
				else
				{
					$responsedata=array('status'=>0,'message'=>$model->errors);
				}
			}				
        }
		return $this->asJson($responsedata);
    }

    public function actionView()
    {
		if(!Yii::$app->userrole->hasRights(array('standard_label_grade_master')))
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
			$id=$data['id'];
			$status = $data['status'];

			if(!Yii::$app->userrole->canDoCommonUpdate($status,'standard_label_grade'))
			{
				return false;
			}	

           	$model = StandardLabelGrade::find()->where(['id' => $id])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Standard Label Grade has been activated successfully';
					}elseif($model->status==1){
						$msg='Standard Label Grade has been deactivated successfully';
					}elseif($model->status==2){
						$exists=0;

						if(ApplicationProductStandard::find()->where( [ 'label_grade_id' => $id ] )->exists()){
                            $exists=1;
						}
						else
						{
							$exists=0;
						}

						if($exists==0)
                        {
                            //StandardLabelGrade::findOne($id)->delete();
						}
						$msg='Standard Label Grade has been deleted successfully';
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
     * Finds the StandardLabelGrade model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return StandardLabelGrade the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StandardLabelGrade::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
   
}
