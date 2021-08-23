<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\Standard;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationUnitStandard;
use app\modules\application\models\ApplicationProductStandard;
use app\modules\application\models\ApplicationUnitLicenseFee;
use app\modules\application\models\ApplicationUnitMandayDiscount;
use app\modules\application\models\ApplicationUnitCertifiedStandard;
use app\modules\master\models\StandardReduction;
use app\modules\master\models\StandardReductionRate;
use app\modules\master\models\StandardLicenseFee;
use app\modules\master\models\StandardLabelGrade;
use app\modules\master\models\UserQualificationReview;
use app\modules\master\models\UserStandard;
use app\modules\master\models\QualificationQuestionStandard;
use app\modules\master\models\UserQualificationReviewHistoryRelRoleStandard;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;
/**
 * StandardController implements the CRUD actions for Standard model.
 */
class StandardController extends \yii\rest\Controller
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
					'index','get-standard'
				]
			]
		];        
    }

    public function actionIndex()
    {
		if(!Yii::$app->userrole->hasRights(array('standard_master')))
		{
			return false;
		}

        //$model = Standard::find()->select(['id','name'])->asArray()->all();
        //return ['data'=>$model];
		$post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$stdmodel = new Standard();
		$model = Standard::find()->where(['<>','status',2]);
		//$model->joinWith(['companycountry as ccountry']);

		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
			$pageSize = $post['pageSize']; 
			$statusarray=array_map('strtolower', $stdmodel->StandardType);
			

			//print_r($statusarray);
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				
				$model = $model->andFilterWhere([
					'or',
					['like', 'name', $searchTerm],
					['like', 'code', $searchTerm],
					['like', 'short_code', $searchTerm],
					['like', 'version', $searchTerm],
					
					//['like', 'type', array_search($searchTerm,$statusarray)],										
				]);
				
				$search_status = array_search(strtolower($searchTerm),$statusarray);
				if($search_status!==false)
				{
					$model = $model->orFilterWhere([
                        'or', 					
						['type'=>$search_status]								
					]);
				}
				$totalCount = $model->count();
			}
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
			}
			else
			{
				$model = $model->orderBy(['priority' => SORT_ASC]);
			}
			



            $model = $model->limit($pageSize)->offset($page);
		}
		else
		{
			$totalCount = $model->count();
		}
		
		$standard_list=array();
		//$model = $model->asArray()->all();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $standard)
			{
				$data=array();
				$data['id']=$standard->id;
				$data['name']=$standard->name;
				$data['code']=$standard->code;
				$data['short_code']=$standard->short_code;
				$data['version']=$standard->version;
				$data['priority']=$standard->priority;
				$data['license_number']=$standard->license_number;
				$data['type']=$standard->StandardType[$standard->type];
				$data['status']=$standard->status;
				//$data['company_telephone']=$standard->company_telephone;
				//$data['company_email']=$standard->company_email;
				//$data['company_country_id']=$standard->companycountry->name;
				//$data['created_at']=M d,Y h:i A',$standard->created_at);
				$data['created_at']=date($date_format,$standard->created_at);
				$standard_list[]=$data;
			}
		}
		
		return ['standards'=>$standard_list,'total'=>$totalCount];
    }
	
	public function actionGetStandard()
	{
		$Country = Standard::find()->select(['id','name','code'])->where(['status'=>0])->asArray()->all();
		return ['standards'=>$Country];
	}
	
    public function actionCreate()
    {
		if(!Yii::$app->userrole->hasRights(array('add_standard')))
		{
			return false;
		}

        $model = new Standard();
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
			$chkmodel = Standard::find()->where(['priority'=> $data['priority']])->one();
			if($chkmodel !== null){
				return $responsedata=array('status'=>0,'message'=>['priority'=>['Priority Already Exists']]);
			}

            $model->name=$data['name'];
            $model->code=$data['code'];
			$model->short_code=$data['short_code'];
			$model->version=$data['version'];
			$model->priority=$data['priority'];
            $model->type=$data['type'];
            $model->description=$data['description'];
			$model->license_number=$data['license_number'];

			$userData = Yii::$app->userdata->getData();
			$model->created_by=$userData['userid'];
			
            if($model->validate() && $model->save())
        	{
                $responsedata=array('status'=>1,'message'=>'Standard has been created successfully');
            }
            else
            {
                $responsedata=array('status'=>0,'message'=>$model->errors);
            }

            return $this->asJson($responsedata);
        }
    }

    
    public function actionUpdate()
    {
		if(!Yii::$app->userrole->hasRights(array('edit_standard')))
		{
			return false;
		}

        $model = new Standard();
		$data = Yii::$app->request->post();
		
		if ($data) 
		{            
			$chkmodel = Standard::find()->where(['!=', 'id', $data['id']])->andWhere(['priority'=> $data['priority']])->one();
			if($chkmodel !== null){
				return $responsedata=array('status'=>0,'message'=>['priority'=>['Priority Already Exists']]);
			}
			

            $model = Standard::find()->where(['id' => $data['id']])->one();
            $model->name=$data['name'];
            $model->code=$data['code'];
			$model->short_code=$data['short_code'];
			$model->version=$data['version'];
			$model->priority=$data['priority'];
            $model->type=$data['type'];
            $model->description=$data['description'];
            $model->license_number=$data['license_number'];
			
			$userData = Yii::$app->userdata->getData();
			$model->updated_by=$userData['userid'];
		
            if($model->validate() && $model->save())
			{
				$responsedata=array('status'=>1,'message'=>'Standard has been updated successfully');
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
		if(!Yii::$app->userrole->hasRights(array('standard_master')))
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
			if(!Yii::$app->userrole->canDoCommonUpdate($status,'standard'))
			{
				return false;
			}	
			
			$id=$data['id'];
           	$model = Standard::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){						
						$msg='Standard has been activated successfully';
					}elseif($model->status==1){						
						$msg='Standard has been deactivated successfully';
					}elseif($model->status==2){						
						$exists=0;

                        if(ApplicationStandard::find()->where( [ 'standard_id' => $id ] )->exists()){
                            $exists=1;
						}
						elseif(ApplicationUnitStandard::find()->where( [ 'standard_id' => $id ] )->exists()){
                            $exists=1;
						}
						elseif(StandardReduction::find()->where( [ 'standard_id' => $id ] )->exists()){
                            $exists=1;
						}
						elseif(StandardReductionRate::find()->where( [ 'standard_id' => $id ] )->exists()){
                            $exists=1;
						}
						elseif(StandardLicenseFee::find()->where( [ 'standard_id' => $id ] )->exists()){
                            $exists=1;
						}
						elseif(StandardLabelGrade::find()->where( [ 'standard_id' => $id ] )->exists()){
                            $exists=1;
						}
						elseif(UserQualificationReview::find()->where( [ 'standard_id' => $id ] )->exists()){
                            $exists=1;
						}
						elseif(UserStandard::find()->where( [ 'standard_id' => $id ] )->exists()){
                            $exists=1;
						}
						elseif(UserQualificationReviewHistoryRelRoleStandard::find()->where( [ 'standard_id' => $id ] )->exists()){
                            $exists=1;
						}
						elseif(QualificationQuestionStandard::find()->where( [ 'standard_id' => $id ] )->exists()){
                            $exists=1;
						}
						elseif(ApplicationProductStandard::find()->where( [ 'standard_id' => $id ] )->exists()){
                            $exists=1;
						}
						elseif(ApplicationUnitCertifiedStandard::find()->where( [ 'standard_id' => $id ] )->exists()){
                            $exists=1;
						}
						elseif(ApplicationUnitLicenseFee::find()->where( [ 'standard_id' => $id ] )->exists()){
                            $exists=1;
						}
						elseif(ApplicationUnitMandayDiscount::find()->where( [ 'standard_id' => $id ] )->exists()){
                            $exists=1;
						}
						else
						{
							$exists=0;
						}
						
						if($exists==0)
                        {
                            //Standard::findOne($id)->delete();
                        }
						$msg='Standard has been deleted successfully';
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
        if (($model = Standard::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
   
}
