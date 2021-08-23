<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\Translator;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;
/**
 * StandardController implements the CRUD actions for Standard model.
 */
class TranslatorController extends \yii\rest\Controller
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
					'index'
				]
			]
		];        
    }

    public function actionIndex()
    {

		$post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$stdmodel = new Translator();
		$model = Translator::find()->where(['<>','status',2]);

		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
			$pageSize = $post['pageSize']; 			

			//print_r($statusarray);
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				
				$model = $model->andFilterWhere([
					'or',
					['like', 'country', $searchTerm],
					['like', 'name', $searchTerm],
					['like', 'email', $searchTerm],					
				]);
			
				$totalCount = $model->count();
			}

			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc'?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
			}
			else
			{
				$model = $model->orderBy(['country' => SORT_ASC]);
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
				$filenamearray = explode("|",$standard->filename);
				array_pop($filenamearray);
				$data=array();
				$data['id']=$standard->id;
				$data['country']=$standard->country;
				$data['surname']=$standard->surname;
				$data['employment']=$standard->employment;
				$data['language1']=$standard->language1;
				$data['language2']=$standard->language2;
				$data['language3']=$standard->language3;
				$data['language4']=$standard->language4;
				$data['email']=$standard->email;
				$data['phone']=$standard->phone;
				$data['status']=$standard->status;
				$data['filename']=$filenamearray;
				$standard_list[]=$data;
			}
		}
		
		return ['standards'=>$standard_list,'total'=>$totalCount];
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
        if (($model = Translator::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
   
}
