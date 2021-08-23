<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\Country;
use app\modules\master\models\Mandaycost;
use app\modules\master\models\ManDayCostTax;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * MandaycostController implements the CRUD actions for Mandaycost model.
 */
class MandaycostController extends \yii\rest\Controller
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
			'authenticator' => ['class' => JwtHttpBearerAuth::class	 ]
		];        
    }
	
	public function actionIndex()
    {
		if(!Yii::$app->userrole->hasRights(array('man_day_cost_tax_master')))
		{
			return false;
		}

		$post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$model = Mandaycost::find()->alias('t');
		$model->joinWith(['country as cty']);
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 't.man_day_cost', $searchTerm],
					['like', 't.currency_code', $searchTerm],
					['like', 'cty.name', $searchTerm],	
					['like', '(date_format(FROM_UNIXTIME(t.created_at), \'%b %d, %Y\' ))', $searchTerm],
				]);

				$totalCount = $model->count();
			}
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='' && $post['sortColumn']!='total_tax_percentage')
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
		
		$mandaycost_list=array();
		$model->andWhere(['<>','t.status',2]);
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $mandaycost)
			{
				$totalTax=0;
				$mTax = $mandaycost->mandaycosttax;
				if(count($mTax)>0)
				{
					foreach($mTax as $mT)
					{
						$totalTax=$totalTax+$mT->tax_percentage;
					}
				}
				
				$totalOtherStateTax=0;
				$mTaxOS = $mandaycost->mandaycosttaxotherstate;
				if(count($mTaxOS)>0)
				{
					foreach($mTaxOS as $mT)
					{
						$totalOtherStateTax=$totalOtherStateTax+$mT->tax_percentage;
					}
				}
				
				$data=array();
				$data['id']=$mandaycost->id;
				$data['country_id']=$mandaycost->country_id;
				$data['country_name']=$mandaycost->country->name;
				$data['currency_code']=$mandaycost->currency_code;
				$data['man_day_cost']=$mandaycost->man_day_cost;
				$data['admin_fee']=$mandaycost->admin_fee;
				$data['client_logo_approval_fee']=$mandaycost->client_logo_approval_fee;
				$data['status']=$mandaycost->status;
				$data['total_tax_percentage']=$totalTax;
				$data['total_other_state_tax_percentage']=$totalOtherStateTax;				
				$data['created_at']=date($date_format,$mandaycost->created_at);
				$mandaycost_list[]=$data;
			}
		}
		
		return ['mandaycosts'=>$mandaycost_list,'total'=>$totalCount];
    }

    public function actionCreate()
	{
		if(!Yii::$app->userrole->hasRights(array('add_man_day_cost')))
		{
			return false;
		}

		$model = new Mandaycost();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();
			
			$model->country_id=$data['country_id'];
			$model->man_day_cost=$data['man_day_cost'];
			$model->currency_code=$data['currency_code'];
			//$model->man_day_cost=$data['man_day_cost'];

			$model->admin_fee=$data['admin_fee'];
			$model->client_logo_approval_fee=$data['client_logo_approval_fee'];

			$userData = Yii::$app->userdata->getData();
			$model->created_by=$userData['userid'];

			if($model->validate() && $model->save())
			{
				if(is_array($data['tax']) && count($data['tax'])>0)
				{
					foreach ($data['tax'] as $value)
					{ 
						$qualmodel=new ManDayCostTax();
						$qualmodel->man_day_cost_id=$model->id;
						$qualmodel->tax_name=$value['tax_name'];
						$qualmodel->tax_percentage=$value['tax_percentage'];						
						$qualmodel->tax_for=$value['tax_for'];						
						$qualmodel->save();
						
					}
				}
				
				$responsedata=array('status'=>1,'message'=>'Man day cost has been created successfully');	
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
		if(!Yii::$app->userrole->hasRights(array('edit_man_day_cost')))
		{
			return false;
		}
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
           	$model = Mandaycost::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->country_id=$data['country_id'];
				$model->man_day_cost=$data['man_day_cost'];
				$model->currency_code=$data['currency_code'];
				
				$model->admin_fee=$data['admin_fee'];
				$model->client_logo_approval_fee=$data['client_logo_approval_fee'];

				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];
				
				if($model->validate() && $model->save())
				{
					ManDayCostTax::deleteAll(['man_day_cost_id' => $model->id]);
					if(is_array($data['tax']) && count($data['tax'])>0)
					{
						foreach ($data['tax'] as $value)
						{ 
							$qualmodel=new ManDayCostTax();
							$qualmodel->man_day_cost_id=$model->id;
							$qualmodel->tax_name=$value['tax_name'];
							$qualmodel->tax_percentage=$value['tax_percentage'];
							$qualmodel->tax_for=$value['tax_for'];							
							$qualmodel->save();							
						}
					}
					$responsedata=array('status'=>1,'message'=>'Manday cost has been updated successfully');
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
	
	// public function actionUpdateManday()
    // {
	// 	$modelCountry = Country::find()->all();
	// 	foreach($modelCountry as $val)
	// 	{
	// 		$modelmanday = Mandaycost::find()->where(['country_id' => $val->id])->asArray()->one();
	// 		if($modelmanday===NULL)
	// 		{
	// 			$modelmdc = new Mandaycost();
	// 			$modelmdc->country_id=$val['id'];
	// 			$modelmdc->man_day_cost=250;
	// 			$modelmdc->currency_code=$val['code'];
	// 			// $userData = Yii::$app->userdata->getData();
	// 			$modelmdc->created_by=2;
	// 			$modelmdc->save();
	// 		}
	// 	}
	// 	$responsedata=array('status'=>1,'message'=>'Manday cost has been updated successfully');
	// 	return $this->asJson($responsedata);
	// }

    public function actionView()
    {
		if(!Yii::$app->userrole->hasRights(array('man_day_cost_tax_master')))
		{
			return false;
		}
		$data = Yii::$app->request->post();
		
        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$mandaycostTax=$model->mandaycosttax;
			$arrTax=[];
			if(count($mandaycostTax)>0)
			{
				foreach($mandaycostTax as $costTax)
				{
					$arrTax[]=array('tax_name'=>$costTax->tax_name,'tax_percentage'=>$costTax->tax_percentage,'tax_for'=>$costTax->tax_for,'tax_for_label'=>$costTax->arrTaxLabel[$costTax->tax_for]);
				}				
			}
			
			$mandaycostOtherStateTax=$model->mandaycosttaxotherstate;			
			if(count($mandaycostOtherStateTax)>0)
			{
				foreach($mandaycostOtherStateTax as $costTax)
				{
					$arrTax[]=array('tax_name'=>$costTax->tax_name,'tax_percentage'=>$costTax->tax_percentage,'tax_for'=>$costTax->tax_for,'tax_for_label'=>$costTax->arrTaxLabel[$costTax->tax_for]);
				}				
			}
            return ['data'=>$model,'tax'=>$arrTax];
        }

	}
	
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data && isset($data['status'])) 
		{
			$status = $data['status'];			
			if(!Yii::$app->userrole->canDoCommonUpdate($status,'man_day_cost'))
			{
				return false;
			}	

           	$model = Mandaycost::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Manday cost has been activated successfully';
					}elseif($model->status==1){
						$msg='Manday cost has been deactivated successfully';
					}elseif($model->status==2){
						$msg='Manday cost has been deleted successfully';
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
					$responsedata=array('status'=>0,'message'=>'111'.implode(",",$arrerrors));
				}
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>'222'.$model->errors);
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
        if (($model = Mandaycost::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
