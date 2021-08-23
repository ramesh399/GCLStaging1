<?php
namespace app\controllers;

use Yii;

class MailerController extends \yii\web\Controller
{
	public function beforeAction($action)
	{            
        $this->enableCsrfValidation = false;
    	return parent::beforeAction($action);
	}


    public function actionIndex()
    {
		$data=explode(":",file_get_contents("php://input"));
		
		if($data[1])
		{	
			$message = Yii::$app->mailer->compose();
			$message->setFrom('puja@361dm.com');
			$message->setTo('meignanamoorthyks@gmail.com');
			$message->setSubject('Greetings from moorthy');
			$message->setTextBody('Plain text content');
			$message->setHtmlBody('<html><body><p>Dear Moorthy<br><br>Thank you for your support!<br><br>You just joined hands with CBM by making a donation of <b>Rs.5</b> on <b>25-10-2019</b>. CBM is committed to improve the quality of life of persons with disability and those at risk of disability. With your contribution we will be able to make a difference in the lives of people with disabilities.<br><br>Your generous support makes it possible to increase the work we do and we look forward to see your continued support.<br><br><br>With gratitude,<br>For CBM India Trust</p></body></html>');

			$message->attach(Yii::$app->basePath.'/web/attachmentfiles/sample.pdf');
			//$message->attachContent('Attachment content', ['fileName' => 'attach.txt', 'contentType' => 'text/plain']);
			$message->send();

			


			return "Success!";
		}
		
        //return $this->render('index');
    }

}
