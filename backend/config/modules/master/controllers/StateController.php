<?php

namespace app\modules\master\controllers;

use Yii;
use app\models\Enquiry;
use app\modules\application\models\Application;
use app\modules\master\models\User;
use app\modules\master\models\UserCompanyInfo;
use app\modules\master\models\Country;
use app\modules\master\models\State;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;
/**
 * CountryController implements the CRUD actions for Country model.
 */
class StateController extends \yii\rest\Controller
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

    /**
     * Lists all Country models.
     * @return mixed
     */
	 
	 /*
    public function actionIndex()
    {
        \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;

        
        $post = yii::$app->request->post();
        
        //(page - 1) * pageSize, (page - 1) * pageSize + pageSize
        $countryTotal = Country::find()->count();

        $Country = Country::find()->select(['id','name']);
        if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize'])){
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 

            $Country->limit($pageSize)->offset($page);
        }


        $Country = $Country->all();
        $currentTotal = count($Country);
        if($currentTotal > 0 )
        {
            return array('status' => true, 'data'=> $Country,'total'=>$countryTotal);
        }
        else
        {
            return array('status'=>false,'data'=> 'No Country Found','total'=> 0);
        }
    }
	*/
	
	public function actionIndex()
    {
        if(!Yii::$app->userrole->hasRights(array('state_master')))
		{
			return false;
        }

        $post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$model = State::find()->alias( 't' );
		$model = $model->join('inner join', 'tbl_country as ctry','ctry.id=t.country_id');
		//$model->joinWith('country as ctry');
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
					['like', 'ctry.name', $searchTerm],
					['like', '(date_format(FROM_UNIXTIME(t.created_at), \'%b %d, %Y\' ))', $searchTerm],	
				]);

				$totalCount = $model->count();
			}
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				
				$sortColumn = $post['sortColumn'];
				if($sortColumn=='country_id')
				{
					$sortColumn='ctry.name';					
				}
				$model = $model->orderBy([$sortColumn=>$sortDirection]);
				//$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
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
		
		$country_list=array();
		$model->andWhere(['<>','t.status',2]);
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $state)
			{
				$data=array();
				$data['id']=$state->id;
				$data['name']=$state->name;
				$data['country_id']=$state->country->name;
				$data['status']=$state->status;
				$data['created_at']=date($date_format,$state->created_at);
				$country_list[]=$data;
			}
		}
		
		return ['stats'=>$country_list,'total'=>$totalCount];
    }
	
	/**
     * Creates a new State model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if(!Yii::$app->userrole->hasRights(array('add_state')))
		{
			return false;
        }

        $model = new State();
        $data = Yii::$app->request->post();
        $responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{	
            $model->name=isset($data['name']) ? $data['name'] :'';
            $model->country_id=isset($data['country_id']) ? $data['country_id'] :'';
            
			$userData = Yii::$app->userdata->getData();
            $model->created_by=$userData['userid'];
                        
            if($model->validate() && $model->save())
        	{   
                $responsedata=array('status'=>1,'message'=>'State has been created Successfully');
            }
            else
            {
                $responsedata=array('status'=>0,'message'=>$model->errors);
            }
        }
        return $this->asJson($responsedata);
    }

    /**
     * Updates an existing State model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate()
    {
        if(!Yii::$app->userrole->hasRights(array('edit_state')))
		{
			return false;
        }

        $data = Yii::$app->request->post();
        $responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        if ($data) 
		{	            
            $model = State::find()->where(['id' => $data['id']])->one();
            if ($model !== null)
			{
                $model->name=isset($data['name']) ? $data['name'] :'';
                $model->country_id=isset($data['country_id']) ? $data['country_id'] :'';
                
				$userData = Yii::$app->userdata->getData();
                $model->updated_by=$userData['userid'];
                
                if($model->validate() && $model->save())
                {   
                    $responsedata=array('status'=>1,'message'=>'State has been updated successfully');
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
        if(!Yii::$app->userrole->hasRights(array('state_master')))
		{
			return false;
        }

		$data = Yii::$app->request->post();
		if ($data) 
		{	            
            $model = State::find()->where(['id' => $data['id']])->one();
            if ($model !== null)
			{
				return ['data'=>$model];
			}
        }

    }
    
    /**
     * Deletes an existing State model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if(!Yii::$app->userrole->hasRights(array('delete_state')))
		{
			return false;
        }

        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data && isset($data['status'])) 
		{
            $id=$data['id'];
            $status = $data['status'];	

			if(!Yii::$app->userrole->canDoCommonUpdate($status,'state'))
			{
				return false;
            }	
            
           	$model = State::find()->where(['id' => $id])->one();
			if ($model !== null)
			{
                /*
                elseif(Application::find()->where( [ 'state_id' => $id ] )->exists())
                        {
                            $exists=1;
                        }
                        */
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='State has been activated successfully';
					}elseif($model->status==1){
						$msg='State has been deactivated successfully';
					}elseif($model->status==2){

                        $exists=0;

                        if(Enquiry::find()->where( [ 'state_id' => $id ] )->exists())
                        {
                            $exists=1;
                        }
                        elseif(Enquiry::find()->where( [ 'company_state_id' => $id ] )->exists())
                        {
                            $exists=1;
                        }
                        elseif(User::find()->where( [ 'state_id' => $id ] )->exists())
                        {
                            $exists=1;
                        }
                        elseif(UserCompanyInfo::find()->where( [ 'company_state_id' => $id ] )->exists())
                        {
                            $exists=1;
                        }
                        else
                        {
                            $exists=0;
                        }
                        
                        if($exists==0)
                        {
                           // State::findOne($id)->delete();
                        }
						$msg='State has been deleted successfully';
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
     * Finds the State model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return State the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = State::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    
}
