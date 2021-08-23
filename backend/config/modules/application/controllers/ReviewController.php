<?php
namespace app\modules\application\controllers;

use Yii;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationReview;
use app\modules\application\models\ApplicationReviewer;
use app\modules\application\models\ApplicationApprover;
use app\modules\application\models\ApplicationReviewComment;
use app\modules\application\models\ApplicationUnitReviewComment;
use app\modules\application\models\ApplicationApproval;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;
use app\modules\master\models\User;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class ReviewController extends \yii\rest\Controller
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
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again later');
        if (Yii::$app->request->post()) 
		{
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$is_headquarters =$userData['is_headquarters'];
			$franchiseid=$userData['franchiseid'];

			
			$data = Yii::$app->request->post();
			$ApplicationModel = new Application();
			$model = Application::find()->where(['id' => $data['app_id'],'status' =>$ApplicationModel->arrEnumStatus['review_in_process'] ])->one();
			$reviewmodel = ApplicationReview::find()->where(['app_id' => $data['app_id']])->orderBy(['id' => SORT_DESC])->one();
			
			$ApplicationReviewerModel = ApplicationReviewer::find()->where(['app_id' => $data['app_id'],'reviewer_status'=>1])->one();

			//(applicationdata?.app_status==arrEnumStatus['review_in_process']) && (userdetails.resource_access==1 || ((userType==3 || userType==1) && userdetails.rules.includes('application_review') && applicationdata?.showApplicationReview ) )
			if(!Yii::$app->userrole->isAdmin()){
				if(Yii::$app->userrole->hasRights(array('application_review')) && $ApplicationReviewerModel->user_id == $userid){
					if( Yii::$app->userrole->isOSSUser() ){
						if($model->franchise_id != $franchiseid ){
							return false;
						}
					}
				}else{
					return false;
				}
			}
				

			
			if ($reviewmodel !== null && $model!== null)
			{
				
				
				$reviewmodel->answer=isset($data['answer'])?$data['answer']:"";
				$reviewmodel->comment=isset($data['comment'])?$data['comment']:"";
				$reviewmodel->status=$reviewmodel->arrEnumReviewStatus['review_completed'];
				$reviewmodel->review_result=isset($data['review_result_status'])?$data['review_result_status']:"";

				$answer=$reviewmodel->answer;
				if($answer=='1')
				{
					$app_status=$model->arrEnumStatus['review_completed'];
					//$app_overall_status = $model->arrEnumOverallStatus['review_completed'];
					$approvermodel = ApplicationApprover::find()->where(['app_id' => $data['app_id'],'approver_status'=>1])->one();
					if($approvermodel!==null){
						$approvalmodel=new ApplicationApproval();
						$approvalmodel->app_id=$data['app_id'];
						$approvalmodel->user_id=$approvermodel->user_id;
						$approvalmodel->status=$approvalmodel->arrEnumStatus['approval_in_process'];
						$approvalmodel->save();
						$app_status=$model->arrEnumStatus['approval_in_process'];
						//$app_overall_status = $model->arrEnumOverallStatus['approval_in_process'];
					}
				}
				else if($answer=='2')
				{
					$app_status=$model->arrEnumStatus['failed'];
					//$app_overall_status = $model->arrEnumOverallStatus['failed'];
					Yii::$app->globalfuns->updateApplicationOverallStatus($model->id, $model->arrEnumOverallStatus['application_rejected']);
				}
				else
				{
					$app_status=$model->arrEnumStatus['pending_with_customer'];
					//$app_overall_status = $model->arrEnumOverallStatus['open'];
				}

				$userData = Yii::$app->userdata->getData();
				$reviewmodel->updated_by=$userData['userid'];

				if($reviewmodel->validate() && $reviewmodel->save())
				{
					if(is_array($data['review_comment']) && count($data['review_comment'])>0)
					{
						foreach ($data['review_comment'] as $value)
						{ 
							$reviewcmtmodel=new ApplicationReviewComment();
							$reviewcmtmodel->review_id=$reviewmodel->id;
							$reviewcmtmodel->question_id=isset($value['question_id'])?$value['question_id']:"";
							$reviewcmtmodel->question=isset($value['question'])?$value['question']:"";
							$reviewcmtmodel->answer=isset($value['answer'])?$value['answer']:"";
							$reviewcmtmodel->comment=isset($value['comment'])?$value['comment']:"";
							$reviewcmtmodel->save();
						}

						foreach ($data['unit_review_comment'] as $value)
						{ 
							$reviewcmtmodel=new ApplicationUnitReviewComment();
							$reviewcmtmodel->review_id=$reviewmodel->id;
							$reviewcmtmodel->unit_id=isset($value['unit_id'])?$value['unit_id']:"";
							$reviewcmtmodel->question_id=isset($value['question_id'])?$value['question_id']:"";
							$reviewcmtmodel->question=isset($value['question'])?$value['question']:"";
							$reviewcmtmodel->answer=isset($value['answer'])?$value['answer']:"";
							$reviewcmtmodel->comment=isset($value['comment'])?$value['comment']:"";
							$reviewcmtmodel->save();
						}

						
						if ($model !== null)
						{
							$reviewCount=$model->review_count+1;
							$model->review_count=$reviewCount;
							
							$appNumber = $model->code;							
							$arrAppNumber = explode('/',$appNumber);							
							$model->code=$arrAppNumber[0].'/'.$reviewCount;	
							
							$model->status=$app_status;
							//$model->overall_status = $app_overall_status;
							$model->save();

							Yii::$app->globalfuns->updateApplicationStatus($model->id,$model->status,$model->audit_type);

							if($model->status==$model->arrEnumStatus['pending_with_customer'])
							{
								$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'review_pending'])->one();

								if($mailContent !== null)
								{
									$mailmsg=str_replace('{USERNAME}',$model->firstname." ".$model->lastname, $mailContent['message'] );

									$MailLookupModel = new MailLookup();
									$MailLookupModel->to=$model->emailaddress;									
									$MailLookupModel->bcc='';
									$MailLookupModel->subject=$mailContent['subject'];
									$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailNotificationTemplate',['content' => $mailmsg]);
									$MailLookupModel->attachment='';
									$MailLookupModel->mail_notification_id='';
									$MailLookupModel->mail_notification_code='';
									$Mailres=$MailLookupModel->sendMail();
								}
							}

							$responsedata=array('status'=>1,'message'=>'Review has been saved successfully','app_status'=>$model->arrStatus[$model->status]);
						}
					}
				}
			}	
		}
		return $this->asJson($responsedata);
	}
	

	public function actionViewAnswer(){


		$responsedata=array('status'=>0,'message'=>'Review data not found');
        if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();
			$userData = Yii::$app->userdata->getData();


			$model = ApplicationReview::find()->where(['app_id' => $data['app_id'],'status'=>array('2','3')])->orderBy(['id' => SORT_DESC])->one();
			if ($model !== null)
			{
				

				$applicationreviews=[];
				$reviewarr=[];
				$reviewcommentarr=[];
				$appReview=$model->applicationreviewcomment;
				if(count($appReview)>0)
				{
					foreach($appReview as $reviewComment)
					{
						$reviewcommentarr[]=array(
							'review_id'=>$reviewComment->review_id,
							'question_id'=>$reviewComment->question_id,
							'answer'=>$reviewComment->answer,
							'comment'=>$reviewComment->comment
						);
					}	
				}
				$data['applicationcomment'] = $reviewcommentarr;

				$applicationreviews=[];
				$reviewarr=[];
				$reviewcommentarr=[];
				$appReview=$model->applicationunitreviewcomment;
				if(count($appReview)>0)
				{
					foreach($appReview as $reviewComment)
					{
						$reviewcommentarr[]=array(
							'review_id'=>$reviewComment->review_id,
							'question_id'=>$reviewComment->question_id,
							'unit_id'=>$reviewComment->unit_id,
							'answer'=>$reviewComment->answer,
							'comment'=>$reviewComment->comment
						);
					}	
				}
				$data['applicationunitcomment'] = $reviewcommentarr;
				$data['status'] = 1;
				return $data;
			}

		}
		return $responsedata;
	}


	
	public function actionAssign()
    {
		$reviewmodel=new ApplicationReview();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again later');
        if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();
			$userData = Yii::$app->userdata->getData();

			//(applicationdata?.app_status=arrEnumStatus['waiting_for_review'])  && (userdetails.resource_access==1 || ((userType==3 || userType==1) && userdetails.rules.includes('application_review')) )
			$ApplicationStatusModel = new Application();

			
			

			if($data['actiontype']=='self'){
				$user_id = $userData['userid'];
				$applicationmodel = Application::find()->where(['id' => $data['app_id'],'status'=>$ApplicationStatusModel->arrEnumStatus['waiting_for_review'] ])->one();
				if(!Yii::$app->userrole->hasRights(array('application_review')) || $applicationmodel=== null){
					return $responsedata;
				}
				
			}else{
				$statusArr = [
						$ApplicationStatusModel->arrEnumStatus['waiting_for_review'], 
						$ApplicationStatusModel->arrEnumStatus['review_in_process'], 
						$ApplicationStatusModel->arrEnumStatus['pending_with_customer'], 
						$ApplicationStatusModel->arrEnumStatus['review_completed']
					];
				$applicationassignmodel = Application::find()->where(['id' => $data['app_id'],'status'=>$statusArr ])->one();
				if(!Yii::$app->userrole->hasRights(array('assign_application_reviewer')) || $applicationassignmodel=== null){
					return $responsedata;
				}
				
				$user_id = $data['user_id'];
			}

			$reviewmodel->app_id=isset($data['app_id'])?$data['app_id']:"";
			$reviewmodel->user_id=isset($user_id)?$user_id:"";
			$reviewmodel->status=$reviewmodel->arrEnumReviewStatus['review_in_process'];

			
			$reviewmodel->created_by=$userData['userid'];


			//$rows = ApplicationReviewer::updateAll(['reviewer_status' => 0,'updated_by'=>$userData['userid']], 'app_id = '.$data['app_id'].' AND reviewer_status  = 1');
			$row = ApplicationReviewer::find()->where(['app_id'=>$data['app_id'],'reviewer_status'=>1])->one();
			if($row!==null)
			{
				$row->reviewer_status = $row->arrEnumStatus['old'];
				$row->updated_by = $userData['userid'];
				$row->save();
			}
			
			$reviewermodel=new ApplicationReviewer();
			$reviewermodel->app_id = isset($data['app_id'])?$data['app_id']:"";
			$reviewermodel->user_id = isset($user_id)?$user_id:"";
			$reviewermodel->reviewer_status = $reviewermodel->arrEnumStatus['current'];
			$reviewermodel->created_by = $userData['userid'];
			$reviewermodel->save();


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


			if($reviewmodel->validate() && $reviewmodel->save())
        	{
				$model = Application::find()->where(['id' => $data['app_id']])->one();
				if ($model !== null && $data['actiontype']!='change' )
				{
					$model->status=$model->arrEnumStatus['review_in_process'];
					//$model->overall_status= $model->arrEnumOverallStatus['review_in_process'];
					$model->save();

					Yii::$app->globalfuns->updateApplicationStatus($data['app_id'],$model->status,$model->audit_type);
				}

				$Usermodel = User::find()->where(['id' => $user_id])->one();

				$mailContent = MailNotifications::find()->select('subject,message')->where(['code' => 'changing_reviewer'])->one();

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
				}

				$hasapprover = 0;
				$modelapprover = ApplicationApprover::find()->where(['app_id' => $data['app_id'],'approver_status'=>1])->one();
				if ($modelapprover !== null)
				{
					$hasapprover = 1;
				}
				$responsedata=array('status'=>1,'message'=>'Reviewer has been assigned successfully','hasapprover'=>$hasapprover,'app_status'=>$model->status,'app_status_name'=>$model->arrStatus[$model->status]);
			}
		}
		return $this->asJson($responsedata);
    }

}
