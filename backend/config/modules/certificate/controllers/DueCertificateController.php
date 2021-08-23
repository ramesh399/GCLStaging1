<?php
namespace app\modules\certificate\controllers;

use Yii;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationUnit;

use app\modules\master\models\User;
use app\modules\master\models\UserCompanyInfo;
use app\modules\master\models\UserBusinessGroupCode;

use app\modules\changescope\models\ProcessAddition;
use app\modules\changescope\models\UnitAddition;

use app\modules\changescope\models\StandardAddition;

use app\models\Enquiry;
use app\modules\master\models\State;
use yii\web\NotFoundHttpException;

use app\modules\audit\models\Audit;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class DueCertificateController extends \yii\rest\Controller
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
			'authenticator' => ['class' => JwtHttpBearerAuth::class ],
			/*
			'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => Yii::$app->userrole->hasRights(),                     
                    ],
                ],
            ],
			*/
		];        
    }
	
	public function actionIndex()
    {
    	return ['applications'=> [],'total'=>0];       
    }


	
}
