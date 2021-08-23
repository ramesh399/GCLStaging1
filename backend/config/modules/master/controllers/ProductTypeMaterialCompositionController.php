<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\ProductTypeMaterialComposition;
use app\modules\master\models\ApplicationProductMaterial;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;
/**
 * ProductTypeMaterialCompositionController implements the CRUD actions for Product model.
 */
class ProductTypeMaterialCompositionController extends \yii\rest\Controller
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
		if(!Yii::$app->userrole->hasRights(array('material_master')))
		{
			return false;
		}

        //$model = Standard::find()->select(['id','name'])->asArray()->all();
        //return ['data'=>$model];
		$post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$model = ProductTypeMaterialComposition::find()->where(['<>','t.status',2])->alias('t');
		$model->joinWith(['product as prd','producttype as ptype']);
		
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
					['like', 't.code', $searchTerm],
					['like', 'prd.name', $searchTerm],
					['like', 'ptype.name', $searchTerm],
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
		//$model->Where(['<>','t.status',2]);
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $materialcomposition)
			{
				$data=array();
				$data['id']=$materialcomposition->id;
				$data['name']=$materialcomposition->name;
				$data['code']=$materialcomposition->code;
				$data['product_id']=$materialcomposition->product_id;
				$data['product_type_id']=$materialcomposition->product_type_id;
				$data['product']=$materialcomposition->product->name;
				$data['product_type']=$materialcomposition->producttype->name;
				$data['status']=$materialcomposition->status;
				$data['created_at']=date($date_format,$materialcomposition->created_at);
				$list[]=$data;
			}
		}
		
		return ['materialcompositions'=>$list,'total'=>$totalCount];
	}
	
	public function actionSearchlist()
    {
		$post = yii::$app->request->post();
		$list = [];

		$product_type_id = $post['product_type_id'];
		if($product_type_id){
			$datalist = ProductTypeMaterialComposition::find()->select(['id', 'name'])->where(['status'=>0,'product_type_id'=>$product_type_id])->asArray()->all();
			if(count($datalist)>0){
				$list = $datalist;
			}
		}
		return $list;
	}

	/*
	public function actionSearchlist()
    {
		$post = yii::$app->request->post();
		$lists = [];

		$searchname = $post['searchname'];
		if($searchname){
			$list = ProductTypeMaterialComposition::find()->select(['name'])->where(['like','name',$searchname])->asArray()->all();
			$lists = ArrayHelper::getColumn($list, 'name');
		}
		return $lists;
	}
	*/
	
    public function actionCreate()
    {
		if(!Yii::$app->userrole->hasRights(array('add_material')))
		{
			return false;
		}

		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
			$model = new ProductTypeMaterialComposition();
			$model->product_id=$data['product_id'];
			$model->product_type_id=$data['product_type_id'];
			$model->name=$data['name'];
			$model->code=$data['code'];
			//$model->code='';
			//$model->description=$value['description'];
			$userData = Yii::$app->userdata->getData();
			$model->created_by=$userData['userid'];
			if($model->validate() && $model->save())
			{
				$responsedata=array('status'=>1,'message'=>'Product material has been created successfully');
			}else
			{
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}          
        }
		return $this->asJson($responsedata);
    }

    
    public function actionUpdate()
    {
		if(!Yii::$app->userrole->hasRights(array('edit_material')))
		{
			return false;
		}

        $data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
			$model = ProductTypeMaterialComposition::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->product_id=$data['product_id'];
				$model->product_type_id=$data['product_type_id'];
				$model->name=$data['name'];
				$model->code=$data['code'];
				//$model->code='';
				//$model->description=$data['description'];
				$userData = Yii::$app->userdata->getData();
				$model->created_by=$userData['userid'];
				if($model->validate() && $model->save())
				{
					$responsedata=array('status'=>1,'message'=>'Product material has been updated successfully'); 
				}else
				{
					$responsedata=array('status'=>0,'message'=>$model->errors);
				}
			}
			         
        }
		return $this->asJson($responsedata);
    }

    public function actionView()
    {
		if(!Yii::$app->userrole->hasRights(array('material_master')))
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
		if(!Yii::$app->userrole->hasRights(array('delete_material','activate_material','deactivate_material')))
		{
			return false;
		}

		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data && isset($data['status'])) 
		{
			$id=$data['id'];
			$status = $data['status'];

			if(!Yii::$app->userrole->canDoCommonUpdate($status,'material'))
			{
				return false;
			}	

           	$model = ProductTypeMaterialComposition::find()->where(['id' => $id])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Material has been activated successfully';
					}elseif($model->status==1){
						$msg='Material has been deactivated successfully';
					}elseif($model->status==2){
						/*
						$exists=0;

                        if(ApplicationProductMaterial::find()->where( [ 'material_id' => $id ] )->exists()){
                            $exists=1;
						}
						
						if($exists==0)
                        {
                            //ProductTypeMaterialComposition::findOne($id)->delete();
						}
						*/
						$msg='Material has been deleted successfully';
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
        if (($model = ProductTypeMaterialComposition::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
   
}
