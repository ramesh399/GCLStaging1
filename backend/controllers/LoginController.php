<?php
namespace app\controllers;

use Yii;

class LoginController extends \yii\rest\Controller
{

    /**
     * @inheritdoc
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
		];	
	}
	 
	public function actionAuthenticate()
	{
		$time = time();
		$token = Yii::$app->jwt->getBuilder()
					->issuedBy('') // Configures the issuer (iss claim)
					->permittedFor('') // Configures the audience (aud claim)
					->identifiedBy('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
					->issuedAt($time) // Configures the time that the token was issue (iat claim)
					->canOnlyBeUsedAfter($time) // Configures the time that the token can be used (nbf claim)
					->expiresAt($time + 3600) // Configures the expiration time of the token (exp claim)
					->withClaim('uid', 1) // Configures a new claim, called "uid"
					->getToken(); // Retrieves the generated token
		//echo '<pre>';
		//print_r($token);
		//die();
		
		//$token->getHeaders(); // Retrieves the token headers
		//$token->getClaims(); // Retrieves the token claims

		//$token->getHeader('jti'); // will print "4f1g23a12aa"
		//$token->getClaim('iss'); // will print "http://example.com"
		//$token->getClaim('uid'); // will print "1"
		//echo $token; // The string representation of the object is a JWT string (pretty easy, right?)
		//die();
		return ['token'=>(string)$token,'role'=>'Admin'];
	}
}