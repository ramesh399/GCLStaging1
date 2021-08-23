<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\modules\certificate\models\Certificate;
use app\modules\certificate\models\CertificateViewRequestFromWeb;
use app\modules\master\models\Standard;
use app\modules\application\models\Application;

class WebserviceController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [            
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }    

    public function beforeAction($action) 
    { 
        $this->enableCsrfValidation = false; 
        return parent::beforeAction($action); 
    }

    public function actionCertificate()
    {
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
        $model = new CertificateViewRequestFromWeb();
        $request = Yii::$app->request;        
        $RequestStatus=0;
		
        /* the request method is GET */        
        if ($request->isGet)  
        { 
            $get = $request->get();		
			if($get)			
			{
				$model->code=(isset($get['code']) && trim($get['code'])!=''  ? trim($get['code']) : '');
				$model->request_method=1;			
				$RequestStatus=1;
			}	
        }

        /* the request method is POST */
        if ($request->isPost) 
        {  
            $post = $request->post(); 
			if($post)
			{				
				$model->code=(isset($post['code']) && trim($post['code'])!=''  ? trim($post['code']) : '');
				$model->request_method=2;	            
				$RequestStatus=1;                     
			}
        }		

        if($RequestStatus==1 && $model->code!='')          
        {			
			$model->ip_address = $_SERVER['REMOTE_ADDR'];
			$model->search_result=1;
			
			$arrCode=explode("/",$model->code);			
            $CertificateModel = Certificate::find()->where(['md5(code)' => md5($arrCode[0])])->one();
            if($CertificateModel!==null)
			{
				$responsedata=array();
				$responsedata['status']=1;
				
				$certificateData=array();
				$certificateData['registration_number']=$CertificateModel->code;
				$certificateData['registration_status']=$CertificateModel->arrStatus[$CertificateModel->status];
				$certificateData['accreditation_body']='None';
				$certificateData['certificate_approval_date']=date($date_format,strtotime($CertificateModel->certificate_generated_date));
				$certificateData['certificate_expiry_date']=date($date_format,strtotime($CertificateModel->certificate_valid_until));
				$certificateData['certificate_version']=$CertificateModel->version;
				$certificateData['standard']=$CertificateModel->standard->name;
				$certificateData['standard_code']=$CertificateModel->standard->code;
				$certificateData['standard_short_code']=$CertificateModel->standard->short_code;
				$certificateData['standard_version']=$CertificateModel->standard->version;
				
				$companyName='';
				$companyAddress='';	
				$companyCity='';	
				$companyState='';	
				$companyCountry='';
				$companyZipcode='';
				
				$applicationModelObject = $CertificateModel->application->currentaddress;
				$companyName = $applicationModelObject->company_name;														
				$companyAddress = trim($applicationModelObject->address);						
				$companyCity = $applicationModelObject->city;
				$companyState = $applicationModelObject->state->name;
				$companyCountry = $applicationModelObject->country->name;
				$companyZipcode = $applicationModelObject->zipcode;
				
				$certificateData['company_name']=$companyName;
				$certificateData['company_address']=$companyAddress;				
				$certificateData['company_city']=$companyCity;
				$certificateData['company_state']=$companyState;
				$certificateData['company_country']=$companyCountry;
				$certificateData['company_zipcode']=$companyZipcode;
				
				$applicationID = $CertificateModel->application->id;
				$standardID = $CertificateModel->standard_id;
				
				$connection = Yii::$app->getDb();
				$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
						
				$productsQry = 'SELECT prd.name AS product,prdtype.name AS product_type,GROUP_CONCAT(DISTINCT apm.percentage, \'% \', ptm.`name` SEPARATOR \' + \') AS material_composition,slg.name AS product_code,slg.id as product_label_grade_id  FROM `tbl_application_product` AS ap
				INNER JOIN `tbl_application_product_material` AS apm ON apm.app_product_id = ap.id AND ap.app_id='.$applicationID.'
				INNER JOIN `tbl_application_product_standard` AS aps
				 ON aps.application_product_id = ap.id AND aps.standard_id='.$standardID.' AND aps.product_standard_status=0 
				INNER JOIN `tbl_product` AS prd ON prd.id = ap.product_id
				INNER JOIN `tbl_product_type` AS prdtype ON prdtype.id=ap.product_type_id
				INNER JOIN `tbl_product_type_material` AS ptm ON ptm.id=apm.material_id
				INNER JOIN `tbl_standard_label_grade` AS slg ON slg.id=aps.label_grade_id
				GROUP BY apm.app_product_id,ap.id';
				// material_composition product_code
				
				$command = $connection->createCommand($productsQry);
				$connection->createCommand("SET SESSION group_concat_max_len = 1000000;")->execute();
				$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();
				$result = $command->queryAll();
								
				$arrProductCategories=array();				
				if(count($result)>0)
				{
					foreach($result as $vals)
					{
						if(!in_array($vals['product'], $arrProductCategories))
						{
							$arrProductCategories[]=$vals['product'];
						}						
					}
				}				
				$certificateData['product_categories']=$arrProductCategories;
												
				$responsedata['data']=$certificateData;
                $model->search_result=0;
            }else{
				$responsedata =array('status'=>2,'message'=>"The Registration Number that you entered is not related to a current Certificate issued by GCL International Limited.");
			}	
			$model->created_at=time();	
			$model->save();		
        }		
		return $this->asJson($responsedata);		
    }
    
}
