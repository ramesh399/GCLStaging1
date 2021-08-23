<?php

namespace app\modules\master\controllers;

use Yii;
use app\models\Enquiry;
use app\modules\application\models\Application;
use app\modules\master\models\User;
use app\modules\master\models\UserCompanyInfo;
use app\modules\master\models\Country;
use app\modules\master\models\State;
use app\modules\master\models\Mandaycost;
use app\modules\master\models\OfferTemplate;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * CountryController implements the CRUD actions for Country model.
 */
class CountryController extends \yii\rest\Controller
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
            'authenticator' => ['class' => JwtHttpBearerAuth::class,
                'optional' => [
                    'index',
                    'states',
                    'phonecode',
					'get-country'
                ]
            ]
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
        if(!Yii::$app->userrole->hasRights(array('country_master')))
		{
			return false;
		}
        $post = yii::$app->request->post();
		
        $model = Country::find()->where(['<>','status',2]);
        $date_format = Yii::$app->globalfuns->getSettings('date_format');
		
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
                    ['like', 'code', $searchTerm],
                    ['like', 'phonecode', $searchTerm],
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
		
		$country_list=array();
		//$model = $model->asArray()->all();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $country)
			{
				$data=array();
				$data['id']=$country->id;
				$data['name']=$country->name;
				$data['code']=$country->code;
				$data['phonecode']=$country->phonecode;
				$data['status']=$country->status;
				$data['created_at']=date($date_format,$country->created_at);
				$country_list[]=$data;
			}
		}
		
		return ['countries'=>$country_list,'total'=>$totalCount];
    }
	
	public function actionGetCountry()
	{
		$Country = Country::find()->select(['id','name','phonecode'])->where(['status'=>0])->asArray()->all();
		return ['countries'=>$Country];
	}

    /**
     * Lists all States models.
     * @return mixed
     */
    public function actionStates($id)
    {
        \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
        
        
        $get = yii::$app->request->get();
        
        //(page - 1) * pageSize, (page - 1) * pageSize + pageSize
        $StateTotal = State::find()->count();

        //$State = State::find()->where(['country_id'=>$id])->all();
        $State = State::find()->where(['country_id'=>$id,'status'=>0])->all();
        /*if(count($post)>0 && $post['page']>0 && $post['pageSize']>0){
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 

            $Country->limit($pageSize)->offset($page);
        }
        */

        //$State = $State->all();
        $currentTotal = count($State);
        if($currentTotal > 0 )
        {
            return array('status' => true, 'data'=> $State,'total'=>$StateTotal);
        }
        else
        {
            return array('status'=>false,'data'=> array(),'total'=> 0);
        }
    }
	
	public function actionPhonecode($id)
    {
        \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
        
        $model = Country::find()->where(['id' => $id])->one();
		if ($model !== null)
		{
            return array('status' =>true, 'data'=> $model->phonecode);
        }
        else
        {
            return array('status'=>false,'data'=> 'No Country Found');
        }
    }

     
    /**
     * Creates a new Country model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if(!Yii::$app->userrole->hasRights(array('add_country')))
		{
			return false;
        }
        
        $model = new Country();
        $responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        $data = Yii::$app->request->post();
		if ($data) 
		{	
            $model->name=isset($data['name']) ? $data['name'] :'';
            $model->code=isset($data['code']) ? $data['code'] :'';
            $model->phonecode=isset($data['phonecode']) ? $data['phonecode'] :'';
            
			$userData = Yii::$app->userdata->getData();
            $model->created_by=$userData['userid'];
            
            if($model->validate() && $model->save())
        	{   
                $responsedata=array('status'=>1,'message'=>'Country has been created successfully');
            }
            else
            {
                $responsedata=array('status'=>0,'message'=>$model->errors);
            }
        }
        return $this->asJson($responsedata);
    }

    /**
     * Updates an existing Country model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate()
    {
        if(!Yii::$app->userrole->hasRights(array('edit_country')))
		{
			return false;
        }

        $responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
        if ($data) 
		{	            
            $model = Country::find()->where(['id' => $data['id']])->one();
            if ($model !== null)
			{
                $model->name=isset($data['name']) ? $data['name'] :'';
                $model->code=isset($data['code']) ? $data['code'] :'';
                $model->phonecode=isset($data['phonecode']) ? $data['phonecode'] :'';

                $userData = Yii::$app->userdata->getData();
                $model->updated_by=$userData['userid'];
                
                if($model->validate() && $model->save())
                {   
                    $responsedata=array('status'=>1,'message'=>'Country has been updated successfully');
                }
                else
                {
                    $responsedata=array('status'=>0,'message'=>$model->errors);
                }
            }
        }
        return $this->asJson($responsedata);
    }

    public function actionView($id)
    {
        if(!Yii::$app->userrole->hasRights(array('country_master')))
		{
			return false;
        }

        $model = $this->findModel($id);
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

			if(!Yii::$app->userrole->canDoCommonUpdate($status,'country'))
			{
				return false;
            }	
            
           	$model = Country::find()->where(['id' => $id])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Country has been activated successfully';
					}elseif($model->status==1){
						$msg='Country has been deactivated successfully';
					}elseif($model->status==2){
                        /*
                        elseif(Application::find()->where( [ 'country_id' => $id ] )->exists()){
                            $exists=1;
                        }elseif(OfferTemplate::find()->where( [ 'country_id' => $id ] )->exists()){
                            $exists=1;
                        }
                        */
                        $exists=0;

                        if(Enquiry::find()->where( [ 'country_id' => $id ] )->exists()){
                            $exists=1;
                        }elseif(Enquiry::find()->where( [ 'company_country_id' => $id ] )->exists()){
                            $exists=1;
                        }elseif(User::find()->where( [ 'country_id' => $id ] )->exists()){
                            $exists=1;
                        }elseif(UserCompanyInfo::find()->where( [ 'company_country_id' => $id ] )->exists()){
                            $exists=1;
                        }elseif(State::find()->where( [ 'country_id' => $id ] )->exists()){
                            $exists=1;
                        }elseif(Mandaycost::find()->where( [ 'country_id' => $id ] )->exists()){
                            $exists=1;
                        }elseif(Mandaycost::find()->where( [ 'country_id' => $id ] )->exists()){
                            $exists=1;
                        }else{
                            $exists=0;
                        }
                        
                        if($exists==0)
                        {
                           // Country::findOne($id)->delete();
                        }
						$msg='Country has been deleted successfully';
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
     * Deletes an existing Country model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if(!Yii::$app->userrole->hasRights(array('delete_country')))
		{
			return false;
        }

        $this->findModel($id)->delete();

        return $this->redirect(['index']);
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
        if (($model = Country::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	public function actionCode()
	{
		$country_code_list=array();
		$country_code_list = Mandaycost::find()->select('currency_code as code')->where(['<>','status',2])->groupBy(['currency_code'])->asArray()->all();	
		return ['country_code_list'=>$country_code_list];
	}
}
