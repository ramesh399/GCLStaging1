<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\audit\models\AuditReportQbsScopeHolder;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditQbsScopeholderController implements the CRUD actions for Product model.
 */
class AuditQbsScopeholderController extends \yii\rest\Controller
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
	
	

	public function actionCreate()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		
		if($data)
		{	
			$data['checktype'] = 'unitwise';
			if(!Yii::$app->userrole->canEditAuditReport($data)){
				return false;
			}
			if(isset($data['audit_id']))
			{
				$model = AuditReportQbsScopeHolder::find()->where(['audit_id' => $data['audit_id'],'unit_id' => $data['unit_id']])->one();
				if($model===null)
				{
					$model = new AuditReportQbsScopeHolder();
					$model->created_by = $userData['userid'];
				}
				else
				{
					$model->updated_by = $userData['userid'];
				}
			}
			else
			{
				$model = new AuditReportQbsScopeHolder();
				$model->created_by = $userData['userid'];
			}

			$model->audit_id = $data['audit_id'];
			$model->unit_id = $data['unit_id'];
			$model->qbs_description = $data['qbs_description'];
			
			
			
			if($model->validate() && $model->save())
			{	
				
				$responsedata=array('status'=>1,'message'=>'QBS Scope Holder has been saved successfully');
				
			}
		}
		
		return $this->asJson($responsedata);
	}


	public function actionView()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if($data)
		{	
			$data['checktype'] = 'unitwise';
			if(!Yii::$app->userrole->canViewAuditReport($data)){
				return false;
			}
			if(isset($data['audit_id']))
			{
				$model = AuditReportQbsScopeHolder::find()->where(['audit_id' => $data['audit_id'],'unit_id' => $data['unit_id']])->one();
				if($model!==null)
				{
					$responsedata=array('status'=>1,'data'=>$model->qbs_description);
				}
			}
		}
		return $this->asJson($responsedata);
	}


	
}
