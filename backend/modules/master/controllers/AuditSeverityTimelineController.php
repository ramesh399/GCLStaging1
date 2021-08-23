<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\AuditNonConformityTimeline;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;


/**
 * AuditSeverityTimelineController implements the CRUD actions for BusinessSector model.
 */
class AuditSeverityTimelineController extends \yii\rest\Controller
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
		if(!Yii::$app->userrole->hasRights(array('non_conformative_timeline_master')))
		{
			return false;
		}
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$timeline = AuditNonConformityTimeline::find()->select(['id','name','timeline'])->where(['status'=>0])->asArray()->all();
		if($timeline !== null)
		{
			if(count($timeline) > 0)
			{	
				$resultarr=array();
				foreach($timeline as $val)
				{
					if($val['name'] == 'Critical')
					{
						$resultarr['Critical']['id']=$val['id'];
						$resultarr['Critical']['timeline']=$val['timeline'];
					}

					if($val['name'] == 'Major')
					{
						$resultarr['Major']['id']=$val['id'];
						$resultarr['Major']['timeline']=$val['timeline'];
					}

					if($val['name'] == 'Minor')
					{
						$resultarr['Minor']['id']=$val['id'];
						$resultarr['Minor']['timeline']=$val['timeline'];
					}
				}
				$responsedata=$resultarr;
				
			}
		}
		return $this->asJson($responsedata);
	}

	public function actionGetTimeline()
	{
		$timeline = AuditNonConformityTimeline::find()->select(['id','name','timeline'])->where(['status'=>0])->asArray()->all();
		return ['timeline'=>$timeline];
	}


    public function actionUpdate()
    {
		if(!Yii::$app->userrole->hasRights(array('non_conformative_timeline_master')))
		{
			return false;
		}
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
           	$model = AuditNonConformityTimeline::find()->where(['id' => 1])->one();
			if ($model !== null)
			{
				$model->timeline=$data['critical_duedays'];
				$model->save();
			}

			$model = AuditNonConformityTimeline::find()->where(['id' => 2])->one();
			if ($model !== null)
			{
				$model->timeline=$data['major_duedays'];
				$model->save();
			}

			$model = AuditNonConformityTimeline::find()->where(['id' => 3])->one();
			if ($model !== null)
			{
				$model->timeline=$data['minor_duedays'];
				$model->save();
			}
			$responsedata=array('status'=>1,'message'=>'Non - Conformative Timeline has been updated successfully');	
				// if($model->validate() && $model->save())
				// {
				// 	$responsedata=array('status'=>1,'message'=>'Business Sector has been updated successfully');
				// }
				// else
				// {
				// 	$responsedata=array('status'=>0,'message'=>$model->errors);
				// }
			//}
			// else
			// {
			// 	$responsedata=array('status'=>0,'message'=>$model->errors);
			// }
            
		}
		return $this->asJson($responsedata);
    }

    
}
