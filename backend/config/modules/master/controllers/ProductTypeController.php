<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\ProductType;
use app\modules\master\models\ProductTypeMaterialComposition;
use app\modules\application\models\ApplicationProduct;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;
/**
 * ProductTypeController implements the CRUD actions for Product model.
 */
class ProductTypeController extends \yii\rest\Controller
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
		if(!Yii::$app->userrole->hasRights(array('product_description_master')))
		{
			return false;
		}

        //$model = Standard::find()->select(['id','name'])->asArray()->all();
        //return ['data'=>$model];
		$post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$model = ProductType::find()->where(['<>','t.status',2])->alias('t');
		$model->joinWith(['product as prd']);
		
		$model = $model->groupBy(['t.id']);
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
					['like', 'prd.name', $searchTerm],	
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
				$model = $model->orderBy(['created_at' => SORT_DESC]);
			}
			



            $model = $model->limit($pageSize)->offset($page);
		}
		else
		{
			$totalCount = $model->count();
		}
		
		$list=array();
		$model->andWhere(['<>','t.status',2]);
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $producttype)
			{
				$data=array();
				$data['id']=$producttype->id;
				$data['product']=$producttype->product->name;
				$data['name']=$producttype->name;
				$data['code']=$producttype->code;
				$data['status']=$producttype->status;
				$data['created_at']=date($date_format,$producttype->created_at);
				$list[]=$data;
			}
		}
		
		return ['producttypes'=>$list,'total'=>$totalCount];
	}
	
	
	public function actionList()
    {
		$post = yii::$app->request->post();
		$product_id = $post['product_id'];
		
		$list = ProductType::find()->select(['id','name'])->where(['status'=>0,'product_id'=>$product_id])->asArray()->all();
		return ['data'=>$list,'total'=>count($list)];
	}
	

    public function actionCreate()
    {
		if(!Yii::$app->userrole->hasRights(array('add_product_description')))
		{
			return false;
		}

		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Failed');
		if ($data) 
		{
			$model = new ProductType();
			$model->product_id=$data['product_id'];
			$model->name=$data['name'];
			$model->code=$data['code'];
			//$model->code='';
			//$model->description=$value['description'];
			$userData = Yii::$app->userdata->getData();
			$model->created_by=$userData['userid'];
			if($model->validate() && $model->save())
			{
				$responsedata=array('status'=>1,'message'=>'Product description has been created successfully');
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
		if(!Yii::$app->userrole->hasRights(array('edit_product_description')))
		{
			return false;
		}
		
        $data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
			$model = ProductType::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->product_id=$data['product_id'];
				$model->name=$data['name'];
				$model->code=$data['code'];
				//$model->code='';
				//$model->description=$value['description'];
				$userData = Yii::$app->userdata->getData();
				$model->created_by=$userData['userid'];
				if($model->validate() && $model->save())
				{
					$responsedata=array('status'=>1,'message'=>'Product description has been updated successfully');
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
		if(!Yii::$app->userrole->hasRights(array('product_description_master')))
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

			if(!Yii::$app->userrole->canDoCommonUpdate($status,'product_description'))
			{
				return false;
			}	

           	$model = ProductType::find()->where(['id' => $id])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Product description cost has been activated successfully';
					}elseif($model->status==1){
						$msg='Product description cost has been deactivated successfully';
					}elseif($model->status==2){
						$exists=0;
						/*

                        if(ProductTypeMaterialComposition::find()->where( [ 'product_type_id' => $id ] )->exists()){
                            $exists=1;
                        }elseif(ApplicationProduct::find()->where( [ 'product_type_id' => $id ] )->exists()){
                            $exists=1;
                        }else{
                            $exists=0;
						}
						*/
						if($exists==0)
                        {
                            //ProductType::findOne($id)->delete();
						}
						$msg='Product description cost has been deleted successfully';
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
     * Finds the ProductType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ProductType the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductType::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
   
}
