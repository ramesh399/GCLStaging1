<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\AuditStandard;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;


/**
 * AuditStandard implements the CRUD actions for AuditStandard model.
 */
class AuditStandardController extends \yii\rest\Controller
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
            
			'authenticator' => ['class' => JwtHttpBearerAuth::class ]
		];
    }

	    public function actionCreate()
	{
		if(!Yii::$app->userrole->hasRights(array('standard_master')))
		{
			return false;
		}

		$model = new AuditStandard();
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data)
		{
			$model->standard_name=$data['name'];
			$model->code=$data['code'];
			$model->version=$data['version'];

			if($model->validate() && $model->save())
			{
				$responsedata=array('status'=>1,'message'=>'Audit Standard has been created successfully');
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
		if(!Yii::$app->userrole->hasRights(array('standard_master')))
		{
			return false;
		}

		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data)
		{
           	$model = AuditStandard::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
        	$model->standard_name=$data['name'];
  			$model->code=$data['code'];
  			$model->version=$data['version'];

				if($model->validate() && $model->save())
				{
					$responsedata=array('status'=>1,'message'=>'Audit Standard has been updated successfully');
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

    // public function actionView()
    // {
		// if(!Yii::$app->userrole->hasRights(array('standard_master')))
		// {
		// 	return false;
		// }
    //
		// $data = Yii::$app->request->post();
    //
    //     $model = $this->findModel($data['id']);
    //     if ($model !== null)
		// {
    //         return ['data'=>$model];
    //     }
    //
    // }

	/**
     * Finds the Country model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Country the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AuditStandard::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
