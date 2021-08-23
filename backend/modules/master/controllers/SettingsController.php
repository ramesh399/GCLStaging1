<?php

namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\Settings;
use app\modules\master\models\User;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class SettingsController extends \yii\rest\Controller
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
		$model=new Settings();
        $modelarr = Settings::find()->select('from_email,to_email,reminder_days_user_qualification')->where(['id' => 1])->asArray()->one();
        if ($modelarr !== null)
        {
            $modelarr['reminder_days_user_qualification_array']=$model->arrReminder_days_user_qualification;

            $usermodel = User::find()->where(['headquarters' => 1])->one();
            $modelarr['headquarters']=$usermodel->id;
            $responsedata['data']=$modelarr;
        }
        else
        {
            $responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        }

        
        return $this->asJson($responsedata);
    }   

    public function actionUpdate()
    {
        $data = Yii::$app->request->post();
        $responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{	
            $model = Settings::find()->where(['id' => 1])->one();
            if ($model !== null)
			{
                //$model->application_title=isset($data['application_title']) ? $data['application_title'] :'';
                $model->from_email=isset($data['from_email']) ? $data['from_email'] :'';
                $model->to_email=isset($data['to_email']) ? $data['to_email'] :'';
                // $model->maximum_discount=isset($data['maximum_discount']) ? $data['maximum_discount'] :'';
                // $model->same_standard_maximum_discount=isset($data['same_standard_maximum_discount']) ? $data['same_standard_maximum_discount'] :'';
                // $model->date_format=isset($data['date_format']) ? $data['date_format'] :'';
                $model->reminder_days_user_qualification=isset($data['reminder_days_user_qualification']) ? $data['reminder_days_user_qualification'] :'';
				//$model->headquarters=$data['headquarters'];
				
                $userData = Yii::$app->userdata->getData();
                $model->updated_by=$userData['userid'];
                
                if($model->validate() && $model->save())
                {   
                    $responsedata=array('status'=>1,'message'=>'Settings has been updated successfully');
                }
            }

            $usermodel = User::find()->where(['headquarters' => 1])->one();
			if($usermodel!==null)
			{
				$usermodel->headquarters=0;
				$usermodel->save();
			}

            $usermodel = User::find()->where(['id' => $model->headquarters])->one(); 
            $usermodel->headquarters=1;
            $usermodel->save();


            
        }
        return $this->asJson($responsedata);
    }

    
}
