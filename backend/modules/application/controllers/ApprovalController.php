<?php
namespace app\modules\application\controllers;

use Yii;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationReview;
use app\modules\application\models\ApplicationApprover;
use app\modules\application\models\ApplicationApproval;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;
use app\modules\master\models\User;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class ApprovalController extends \yii\rest\Controller
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
		$reviewmodel=new ApplicationReview();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again later');
        if (Yii::$app->request->post()) 
		{
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$franchiseid=$userData['franchiseid'];
			
			$data = Yii::$app->request->post();
			//(applicationdata?.app_status==arrEnumStatus['approval_in_process']) && (userdetails.resource_access==1 ||
			// ((userType==3 || userType==1) && userdetails.rules.includes('application_approval')) )
			$ApplicationModel = new Application();


			$model = Application::find()->where(['id' => $data['app_id'],'status' =>$ApplicationModel->arrEnumStatus['approval_in_process'] ])->one();
			$ApplicationApproverModel = ApplicationApprover::find()->where(['app_id' => $data['app_id'],'approver_status'=>1])->one();

			if(!Yii::$app->userrole->isAdmin()){
				if(Yii::$app->userrole->hasRights(array('application_review')) && $ApplicationApproverModel->user_id == $userid){
					if( Yii::$app->userrole->isOSSUser() ){
						if($model->franchise_id != $franchiseid ){
							return false;
						}
					}
				}else{
					return false;
				}
			}

			$approvalmodel = ApplicationApproval::find()->where(['app_id' => $data['app_id']])->orderBy(['id' => SORT_DESC])->one();
			if ($approvalmodel !== null && $model!== null)
			{	
				
				
				$approvalmodel->comment=isset($data['comment'])?$data['comment']:"";
				$approvalmodel->status=isset($data['status'])?$data['status']:"";

				$approvalstatus=$approvalmodel->status;
				if($approvalstatus==$approvalmodel->arrEnumStatus['accepted'])
				{
					$app_status=$model->arrEnumStatus['approved'];
					//$app_overall_status=$model->arrEnumOverallStatus['approved'];

					Yii::$app->globalfuns->updateApplicationOverallStatus($model->id, $model->arrEnumOverallStatus['application_approved']);
				}
				else if($approvalstatus==$approvalmodel->arrEnumStatus['rejected'])
				{
					$app_status=$model->arrEnumStatus['failed'];
					//$app_overall_status=$model->arrEnumOverallStatus['failed'];

					Yii::$app->globalfuns->updateApplicationOverallStatus($model->id, $model->arrEnumOverallStatus['application_rejected']);
				}
				else
				{
					//if more info from approver change to review in process
					$app_status=$model->arrEnumStatus['review_in_process'];
					//$app_overall_status=$model->arrEnumOverallStatus['review_in_process'];
				}

				$userData = Yii::$app->userdata->getData();
				$approvalmodel->updated_by=$userData['userid'];

				if($approvalmodel->validate() && $approvalmodel->save())
				{

					
					if ($model !== null)
					{
						//if ($data['actiontype']!='change'){
							$model->status=$app_status;
							//$model->overall_status=$app_overall_status;
							$model->save();
						//}
						Yii::$app->globalfuns->updateApplicationStatus($model->id,$model->status,$model->audit_type);
						
						$reviewmodel = ApplicationReview::find()->where(['app_id' => $data['app_id']])->orderBy(['id' => SORT_DESC])->one();

						if($model->status==$model->arrEnumStatus['pending_with_customer'])
						{
							
							if ($reviewmodel !== null)
							{
								$reviewmodel->app_id=$reviewmodel->app_id;
								$reviewmodel->user_id=$reviewmodel->user_id;
								$reviewmodel->status=$reviewmodel->arrEnumReviewStatus['review_in_process'];

								if($reviewmodel->validate() && $reviewmodel->save())
								{
									$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'review_pending'])->one();

									if($mailContent !== null)
									{
										$mailmsg=str_replace('{USERNAME}',$model->first_name." ".$model->last_name, $mailContent['message'] );

										$MailLookupModel = new MailLookup();
										$MailLookupModel->to=$model->email_address;										
										$MailLookupModel->bcc='';
										$MailLookupModel->subject=$mailContent['subject'];
										$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
										$MailLookupModel->attachment='';
										$MailLookupModel->mail_notification_id='';
										$MailLookupModel->mail_notification_code='';
										$Mailres=$MailLookupModel->sendMail();
									}
								}
							}
						}
						else if($model->status==$model->arrEnumStatus['review_in_process'])
						{
							if ($reviewmodel !== null)
							{
								$reviewmodelnew=new ApplicationReview();
								$reviewmodelnew->app_id=isset($data['app_id'])?$data['app_id']:"";
								$reviewmodelnew->user_id=$reviewmodel->user_id;
								$reviewmodelnew->status=$reviewmodelnew->arrEnumStatus['accepted'];
								$reviewmodelnew->save();
							}


						}
						$responsedata=array('status'=>1,'message'=>'Approval has been saved','app_status'=>$model->status,'app_status_name'=>$model->arrStatus[$model->status]);
					}
					
				}
				else
				{
					$responsedata=array('status'=>0,'message'=>$approvalmodel->errors);
				}
			}	
		}
		return $this->asJson($responsedata);
	}
	
	public function actionAssign()
    {
		$approvalmodel=new ApplicationApproval();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again later');
        if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();
			$userData = Yii::$app->userdata->getData();
			
			if($data['actiontype']=='self'){
				$user_id = $userData['userid'];
			}else{
				$user_id = $data['user_id'];
			}

			$approvalmodel->app_id=isset($data['app_id'])?$data['app_id']:"";
			$approvalmodel->user_id=isset($user_id)?$user_id:"";
			$approvalmodel->status=$approvalmodel->arrEnumStatus['approval_in_process'];

			
			$approvalmodel->created_by=$userData['userid'];

			//$rows = ApplicationApprover::updateAll(['approver_status' => 0,'updated_by'=>$userData['userid']], 'app_id = '.$data['app_id'].' AND approver_status  = 1');
			$row = ApplicationApprover::find()->where(['app_id'=>$data['app_id'],'approver_status'=>1])->one();
			if($row!==null){
				$row->approver_status = $row->arrEnumStatus['old'];
				$row->updated_by = $userData['userid'];
				$row->save();
			}

			
			


			$approvermodel=new ApplicationApprover();
			$approvermodel->app_id = isset($data['app_id'])?$data['app_id']:"";
			$approvermodel->user_id = isset($user_id)?$user_id:"";
			$approvermodel->approver_status = $approvermodel->arrEnumStatus['current'];;
			$approvermodel->created_by = $userData['userid'];
			$approvermodel->save();

			if($approvalmodel->validate() && $approvalmodel->save())
        	{
				$model = Application::find()->where(['id' => $data['app_id']])->one();
				if ($model !== null && $data['actiontype']!='change')
				{
					$model->status=$model->arrEnumStatus['approval_in_process'];
					//$model->overall_status=$model->arrEnumOverallStatus['approval_in_process'];
					$model->save();
					
				}

				$Usermodel = User::find()->where(['id' => $user_id])->one();

				$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'changing_approver'])->one();
				if($mailContent !== null)
				{
					$mailmsg=str_replace('{USERNAME}', $Usermodel->first_name." ".$Usermodel->last_name, $mailContent['message'] );

					$MailLookupModel = new MailLookup();
					$MailLookupModel->to=$Usermodel->email;					
					$MailLookupModel->bcc='';
					$MailLookupModel->subject=$mailContent['subject'];
					$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
					$MailLookupModel->attachment='';
					$MailLookupModel->mail_notification_id='';
					$MailLookupModel->mail_notification_code='';
					$Mailres=$MailLookupModel->sendMail();
					
					$responsedata=array('status'=>1,'message'=>'Approver has been assigned successfully',
					'hasapprover' => 1,
					'approverid' => $user_id,
					'app_status'=>$model->status,'app_status_name'=>$model->arrStatus[$model->status]);
				}
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$approvalmodel->errors);
			}
		}
		return $this->asJson($responsedata);
    }

}
