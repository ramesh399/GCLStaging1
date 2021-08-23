<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\ProductTypeMaterialCompositionStandard;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;
/**
 * ProductTypeMaterialCompositionStandardController implements the CRUD actions for Product model.
 */
class ProductTypeMaterialCompositionStandardController extends \yii\rest\Controller
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
        //$model = Standard::find()->select(['id','name'])->asArray()->all();
        //return ['data'=>$model];
		$post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$model = Standard::find();
		//$model->joinWith(['companycountry as ccountry']);

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
				$data['type']=$standard->StandardType[$standard->type];
				//$data['company_telephone']=$standard->company_telephone;
				//$data['company_email']=$standard->company_email;
				//$data['company_country_id']=$standard->companycountry->name;
				//$data['created_at']=date('M d,Y h:i A',$standard->created_at);
				$data['created_at']=date($date_format,$standard->created_at);
				$standard_list[]=$data;
			}
		}
		
		return ['standards'=>$standard_list,'total'=>$totalCount];
	}
	
    public function actionCreate()
    {
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
			if(is_array($data['material_compositions']) && count($data['material_compositions'])>0)
			{
				foreach ($data['material_compositions'] as $value)
				{ 
					$model = new ProductTypeMaterialCompositionStandard();
					$model->material_composition_id=$data['material_composition_id'];
					$model->standard_id=$value['standard_id'];
					$model->label_grade_id=$value['label_grade_id'];
					$userData = Yii::$app->userdata->getData();
					$model->created_by=$userData['userid'];
					$model->save();
				}
				$responsedata=array('status'=>1,'message'=>'Product type material composistion standard has been created successfully');
			}

            return $this->asJson($responsedata);
        }
    }

    
    public function actionUpdate()
    {
        $data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{

			if(is_array($data['material_compositions']) && count($data['material_compositions'])>0)
			{
				ProductTypeMaterialCompositionStandard::deleteAll(['material_composition_id' => $data['material_composition_id']]);
				foreach ($data['material_compositions'] as $value)
				{ 
					$model = new ProductTypeMaterialCompositionStandard();
					$model->material_composition_id=$data['material_composition_id'];
					$model->standard_id=$value['standard_id'];
					$model->label_grade_id=$value['label_grade_id'];
					$userData = Yii::$app->userdata->getData();
					$model->created_by=$userData['userid'];
					$model->save();
				}
				$responsedata=array('status'=>1,'message'=>'Product type material composistion standard has been updated successfully');
			}

            return $this->asJson($responsedata);
        }
    }

    public function actionView()
    {
		$data = Yii::$app->request->post();
		
        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
            return ['data'=>$model];
        }

    }
	
	
   
}
