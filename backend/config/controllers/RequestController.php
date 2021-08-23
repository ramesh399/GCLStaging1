<?php
namespace app\controllers;

use Yii;
use app\models\Enquiry;
use app\models\EnquiryStandard;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;
use app\modules\master\models\Settings;

class RequestController extends \yii\rest\Controller
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
	 
	public function actionEnquiry()
	{		
		$model = new Enquiry();
		$mailmodel=new MailNotifications();
		$stdmodel=new EnquiryStandard();
						
		$data = Yii::$app->request->post();		
		if($data)
		{
			$jsonResponse = Yii::$app->globalfuns->verifyReCaptcha($data['token']);
			if($jsonResponse['success'])
			{		
				//$model->first_name=isset($data['firstName']) ? $data['firstName'] :'';
				//$model->last_name=isset($data['lastName']) ? $data['lastName'] :'';
				//$model->email=isset($data['email']) ? $data['email'] :'';
				//$model->telephone=isset($data['telePhone']) ? $data['telePhone'] :'';
				$model->phone_code=isset($data['phone_code']) ? $data['phone_code'] :'';
				
				$model->company_name=isset($data['company']['companyName']) ? $data['company']['companyName'] :'';
				$model->contact_name=isset($data['company']['contactName']) ? $data['company']['contactName'] :'';
				$model->company_telephone=isset($data['company']['ctelePhone']) ? $data['company']['ctelePhone'] :'';
				$model->company_phone_code=isset($data['company']['cphone_code']) ? $data['company']['cphone_code'] :'';
				$model->company_email=isset($data['company']['companyEmail']) ? $data['company']['companyEmail'] :'';   
				$model->company_address1=isset($data['company']['address1']) ? $data['company']['address1'] :'';
				$model->company_city=isset($data['company']['city']) ? $data['company']['city'] :'';
				$model->company_zipcode=isset($data['company']['zipcode']) ? $data['company']['zipcode'] :'';
				$model->company_country_id=isset($data['company']['companyCountry']) ? $data['company']['companyCountry'] :'';
				$model->company_state_id=isset($data['company']['companyStates']) ? $data['company']['companyStates'] :'';
				//$model->number_of_sites=isset($data['company']['noOfSites']) ? $data['company']['noOfSites'] :'';

				$model->country_id=isset($data['country']) ? $data['country'] :'';
				$model->state_id=isset($data['states']) ? $data['states'] :'';
				$model->company_state_id=isset($data['company']['companyStates']) ? $data['company']['companyStates']:'';
				$model->company_website=isset($data['company']['website']) ? $data['company']['website'] :'';
				//$model->company_address2=isset($data['company']['address2']) ? $data['company']['address2'] :'';
				$model->number_of_employees=isset($data['noOfEmployees']) ? $data['noOfEmployees'] :'';
				//$model->number_of_sites=isset($data['noOfSites']) ? $data['noOfSites'] :'';
				$model->description=isset($data['companyOperations']) ? $data['companyOperations'] :'';
				$model->other_information=isset($data['otherInformation']) ? $data['otherInformation'] :'';
				$model->ip_address = $_SERVER['REMOTE_ADDR'];
				
				if($model->validate() && $model->save())
				{
					foreach ($data['standardsChk'] as $value)
					{ 
						$stdmodel=new EnquiryStandard();
						$stdmodel->enquiry_id=$model->id;
						$stdmodel->standard_id=$value;
						$stdmodel->save(); 
					}
					
					/*
					$customergrid = $this->renderPartial('@app/mail/layouts/EnquiryCustomerGridTemplate',[
						'model' => $model
					]); 
					*/

					$companygrid = $this->renderPartial('@app/mail/layouts/EnquiryCompanyGridTemplate',[
						'model' => $model
					]);

					$standardgrid = $this->renderPartial('@app/mail/layouts/EnquiryStandardGridTemplate',[
						'model' => $model
					]);
					
					
					$dbContent = MailNotifications::find()->select('subject,message')->where(['code' => 'enquiry_request_to_admin'])->one();
									
					$adminmailsubject=str_replace('{USERNAME}', $model->contact_name, $dbContent['subject'] );
					//$adminmailmsg=str_replace('{CUSTOMER-DETAILS-GRID}', $customergrid, $dbContent['message'] );
					$adminmailmsg=str_replace('{COMPANY-DETAILS-GRID}', $companygrid, $dbContent['message'] );
					$adminmailmsg=str_replace('{STANDARD-DETAILS-GRID}', $standardgrid, $adminmailmsg );
					
					//$to,$cc,$bcc,$subject,$msg,$attachment,$mailNotificationID,$mailNotificationCode
					
					// Mail to Admin
					//$attachfiles=json_encode(array("1"=>'sample.pdf',"2"=>'sample-1.pdf',"3"=>'sample-2.pdf'));
					
					$dbdata = Settings::find()->select('to_email')->where(['id' => '1'])->one();
					
					$MailLookupModel = new MailLookup();
					$MailLookupModel->to=$dbdata['to_email'];										
					$MailLookupModel->subject=$adminmailsubject;
					$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $adminmailmsg]);
					$MailLookupModel->attachment='';
					$MailLookupModel->mail_notification_id='';
					$MailLookupModel->mail_notification_code='';
					$Mailres=$MailLookupModel->sendMail();

					$CustomermailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'enquiry_response_to_customer'])->one();

					$mailmsg=str_replace('{USERNAME}', $model->contact_name, $CustomermailContent['message'] );

					// Mail to Enquired Person
					$MailLookupModel = new MailLookup();
					$MailLookupModel->to=$data['company']['companyEmail'];					
					$MailLookupModel->subject=$CustomermailContent['subject'];
					$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
					$MailLookupModel->attachment='';
					$MailLookupModel->mail_notification_id='';
					$MailLookupModel->mail_notification_code='';
					$Mailres=$MailLookupModel->sendMail();
					
					$responsedata=array('status'=>1,'message'=>'Enquiry has been created successfully');								
				}
				else
				{
					$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again later');
				}

				return $this->asJson($responsedata);
			}else{
				return ['token'=>'','status'=>0,'message'=>'Invalid Request'];
			}
		}else{
			return ['token'=>'','status'=>0,'message'=>'Invalid Request'];
		}	
	}

	/*
	public function actionUpdate()
	{
		$model = new Enquiry();

		if (Yii::$app->request->isAjax) 
		{
			$data = Yii::$app->request->post();

			$model = Enquiry::find()->where(['id' => '2'])->one();
			$model->first_name=$data['firstName'];
			$model->last_name=$data['lastName'];
			$model->email=$data['email'];
			$model->touch('updated_at');
			

			if($model->validate() && $model->save())
			{
				$responsedata=array('status'=>1,'message'=>'Updated successfully');
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>'failed');
			}
		}

		return $this->asJson($responsedata);
	}
	*/

	
}