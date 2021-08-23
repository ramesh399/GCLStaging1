<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\application\models\Application;
use app\modules\offer\models\Invoice;
use app\modules\offer\models\Offer;
use app\modules\master\models\User;
use app\models\Enquiry;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;


class NotificationController extends \yii\rest\Controller
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
            //'authenticator' => ['class' => JwtHttpBearerAuth::class ]
		];        
    }

    
	
	
	public function actionUserNotification()
    {
        $modelEnquiry = new Enquiry();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$model = Enquiry::find();
        $model->joinWith(['companycountry as ccountry']);
        
        $enquiry_list=array();
        $model = $model->where(['tbl_enquiry.status' => 1]);
        $model = $model->orderBy(['id' => SORT_DESC]);	
        $model = $model->limit(5)->all();	
        if(count($model)>0)
		{
			foreach($model as $enquiry)
			{
				$data=array();
				$data['id']=$enquiry->id;
				$data['company_name']=$enquiry->company_name?:'';
				$data['contact_name']=$enquiry->contact_name?:'';
				
				$es=$enquiry->enquirystandard; 
				$eStandardArr=array();
				if(count($es)>0)
				{
					foreach($es as $enquirystandard)
					{
						$eStandardArr[]=$enquirystandard->standard->code;
					}
				}				
				$data['standards']=$eStandardArr;
				
				$data['company_telephone']=$enquiry->company_telephone;
				$data['company_email']=$enquiry->company_email;
				$data['company_country_id']=$enquiry->companycountry->name;
				$data['status']=$modelEnquiry->arrStatus[$enquiry->status];
				$data['status_label_color']=$modelEnquiry->arrStatusColor[$enquiry->status];				
				$data['created_at']=date($date_format,$enquiry->created_at);
                $enquiry_list[]=$data;   
            }
            $resultarr['enquiries']=$enquiry_list;
        }


        $modelApplication = new Application();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$model = Application::find();
        
        $application_list=array();
        $model = $model->where(['status' => 1]);
        $model = $model->orderBy(['id' => SORT_DESC])->all();		
        if(count($model)>0)
		{
			foreach($model as $val)
			{
				$data=array();
                $data['id']=$val->id;
                $data['code']=$val->code?:'';
				$data['company_name']=$val->company_name?:'';
                $data['first_name']=$val->first_name?:'';
                $data['last_name']=$val->last_name?:'';
				$data['telephone']=$val->telephone;
				$data['email_address']=$val->email_address;
				$data['status']=$modelApplication->arrStatus[$val->status];
				$data['status_label_color']=$modelApplication->arrStatusColor[$val->status];				
				$data['created_at']=date($date_format,$val->created_at);
                $application_list[]=$data;   
            }
            $resultarr['applications']=$application_list;
        }
		
		return ['data'=>$resultarr];

    }

}
