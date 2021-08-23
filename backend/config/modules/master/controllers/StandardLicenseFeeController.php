<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\StandardLicenseFee;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;
/**
 * StandardLicenseFeeController implements the CRUD actions for Process model.
 */
class StandardLicenseFeeController extends \yii\rest\Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
			[
				'class' => \yii\filters\ContentNegotiator::className(),
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
		if(!Yii::$app->userrole->hasRights(array('add_license_fee','edit_license_fee')))
		{
			return false;
        }
        
        $responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if (Yii::$app->request->post()) 
		{
            $data = Yii::$app->request->post();
           
            StandardLicenseFee::deleteAll();

            if(count($data)>0)
            {
                foreach($data["licensefees"] as $records)
                {
                    $model = new StandardLicenseFee();
                    $model->standard_id=$records["standard_id"];
                    $model->license_fee=$records["license_fee"];
					$model->subsequent_license_fee=$records["subsequent_license_fee"];
    
                    $userData = Yii::$app->userdata->getData();
                    $model->created_by=$userData['userid'];

                    $model->save();
                }
				$responsedata=array('status'=>1,'message'=>'Standard license fee has been created successfully');
            }
        }
        return $this->asJson($responsedata);
	} 

	public function actionView()
	{
        if(!Yii::$app->userrole->hasRights(array('license_fee_master','view_license_fee')))
		{
			return false;
        }

		$model = StandardLicenseFee::find()->select(['standard_id','license_fee','subsequent_license_fee'])->all();
		if ($model !== null)
		{
            $resultarr=array();
            $i=0;
            foreach($model as $val)
            {
              	$resultarr[]=array('standard_id'=>$val->standard_id,'standard_name'=>$val->standard->name,'license_fee'=>$val->license_fee,'subsequent_license_fee'=>$val->subsequent_license_fee);
            }			
            return ['licensefees'=>$resultarr];
        }
	}	
}	
