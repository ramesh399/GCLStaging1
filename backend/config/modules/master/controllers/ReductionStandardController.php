<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\ReductionStandard;
use app\modules\master\models\ReductionStandardRequiredFields;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationUnitStandard;
use app\modules\master\models\StandardReduction;
use app\modules\master\models\StandardReductionRate;
use app\modules\master\models\StandardLicenseFee;
use app\modules\master\models\StandardLabelGrade;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;
/**
 * ReductionStandardController implements the CRUD actions for Standard model.
 */
class ReductionStandardController extends \yii\rest\Controller
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
		
		if(!Yii::$app->userrole->hasRights(array('reduction_standard_master')))
		{
			return false;
		}

        //$model = Standard::find()->select(['id','name'])->asArray()->all();
        //return ['data'=>$model];
		$post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$stdmodel = new ReductionStandard();
		$model = ReductionStandard::find()->where(['<>','status',2]);
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
				$model = $model->orderBy(['created_at' => SORT_DESC]);
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
	
	public function actionGetOptions()
	{
		$standards = new ReductionStandard();
		return ['standardType'=>$standards->StandardType,'RequiredFields'=>$standards->RequiredFields];
	}
	
	public function actionGetStandard()
	{
		$arrReductionStandard=array();
		//$standards = ReductionStandard::find()->select(['id','name','code'])->where(['status'=>0])->asArray()->all();
		$standards = ReductionStandard::find()->select(['id','name','code'])->where(['status'=>0])->all();
		if(count($standards)>0)
		{
			foreach($standards as $standard)
			{				
				$reductionStd=array();
				$reductionStd['id']=$standard->id;
				$reductionStd['name']=$standard->name;
				$reductionStd['code']=$standard->code;
				$rsRequiredFldsObj = $standard->requiredfields;
				
				$arrRF=array();
				if(is_array($rsRequiredFldsObj) && count($rsRequiredFldsObj)>0)
				{
					
					foreach($rsRequiredFldsObj as $rsRequiredFld)
					{
						$arrRF[]=$standard->arrRequiredFields[$rsRequiredFld->required_field];
					}					
				}	
				$reductionStd['required_fields']=$arrRF;				
				
				//$arrReductionStandard[]=array($standard->id,$standard->name,$standard->code);
				$arrReductionStandard[]=$reductionStd;
			}
		}
		return ['standards'=>$arrReductionStandard];
	}
	
    public function actionCreate()
    {
		if(!Yii::$app->userrole->hasRights(array('add_reduction_standard')))
		{
			return false;
		}

        $model = new ReductionStandard();
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
            $model->name=$data['name'];
            $model->code=$data['code'];
			$model->short_code=$data['short_code'];
            $model->type=$data['type'];
            $model->description=$data['description'];
			
			$userData = Yii::$app->userdata->getData();
			$model->created_by=$userData['userid'];
			
            if($model->validate() && $model->save())
        	{
				if(is_array($data['required_fields']) && count($data['required_fields'])>0)
				{
					foreach ($data['required_fields'] as $value)
					{ 
						$qualmodel=new ReductionStandardRequiredFields();
						$qualmodel->reduction_standard_id=$model->id;
						$qualmodel->required_field=$value;						
						$qualmodel->save();
					}
				}

                $responsedata=array('status'=>1,'message'=>'Reduction Standard has been created successfully');
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
		if(!Yii::$app->userrole->hasRights(array('edit_reduction_standard')))
		{
			return false;
		}

        $model = new ReductionStandard();
		$data = Yii::$app->request->post();
		if ($data) 
		{            
            $model = ReductionStandard::find()->where(['id' => $data['id']])->one();
            $model->name=$data['name'];
            $model->code=$data['code'];
			$model->short_code=$data['short_code'];
            $model->type=$data['type'];
            $model->description=$data['description'];
			
			$userData = Yii::$app->userdata->getData();
			$model->updated_by=$userData['userid'];
		
            if($model->validate() && $model->save())
			{
				if(is_array($data['required_fields']) && count($data['required_fields'])>0)
				{
					ReductionStandardRequiredFields::deleteAll(['reduction_standard_id' => $model->id]);
					foreach ($data['required_fields'] as $value)
					{ 
						$qualmodel=new ReductionStandardRequiredFields();
						$qualmodel->reduction_standard_id=$model->id;
						$qualmodel->required_field=$value;						
						$qualmodel->save();
					}
				}

				$responsedata=array('status'=>1,'message'=>'Reduction Standard has been updated successfully');
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
		if(!Yii::$app->userrole->hasRights(array('reduction_standard_master')))
		{
			return false;
		}

		$data = Yii::$app->request->post();
		$standards = new ReductionStandard();
        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$requiredfields=$model->requiredfields;
			$arrFields=[];
			if(count($requiredfields)>0)
			{
				foreach($requiredfields as $fields)
				{
					$arrFields[]="".$fields->id;
				}				
			}
            return ['data'=>$model, 'required_fields'=>$arrFields];
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

			if(!Yii::$app->userrole->canDoCommonUpdate($status,'reduction_standard'))
			{
				return false;
			}	

           	$model = ReductionStandard::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Reduction Standard has been activated successfully';
					}elseif($model->status==1){
						$msg='Reduction Standard has been deactivated successfully';
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
						else
						{
							$exists=0;
						}
						
						if($exists==0)
                        {
                            //Standard::findOne($id)->delete();
                        }
						$msg='Reduction Standard has been deleted successfully';
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
        if (($model = ReductionStandard::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
   
}
