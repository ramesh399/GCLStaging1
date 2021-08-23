<?php
namespace app\modules\library\controllers;

use Yii;
use app\modules\library\models\LibraryMail;
use app\modules\library\models\LibraryMailStandard;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;
use app\modules\master\models\Signature;
use app\modules\master\models\Standard;
use app\modules\master\models\User;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * MailController implements the CRUD actions for Product model.
 */
class MailController extends \yii\rest\Controller
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
		$post = yii::$app->request->post();
		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		$mailmodel = new LibraryMail();
		$model = LibraryMail::find();
		$target_signature_dir = Yii::$app->params['site_path'].'backend/web/signature_files/';

		if(isset($post['from_date']))
		{
			$model = $model->andWhere(("sent_date>='".date("Y-m-d",strtotime($post['from_date']))."' or mail_sent_at>='".date("Y-m-d",strtotime($post['from_date']))."'"));			
		}
		
		if(isset($post['to_date']))
		{
			$model = $model->andWhere(("sent_date<='".date("Y-m-d",strtotime($post['to_date']))."' or mail_sent_at<='".date("Y-m-d",strtotime($post['to_date']))."'"));						
		}		

		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm']!='')
			{
				$searchTerm = $post['searchTerm'];

				
				$statusarray=array_map('strtolower', $mailmodel->arrStatus);
				$statussearch = array_search(strtolower($searchTerm),$statusarray);
				if($statussearch===false)
				{
					$statussearch='';
				}


				$model = $model->andFilterWhere([
					'or',
					['like', 'subject', $searchTerm],
					['status'=> $statussearch],
					['like', 'date_format(`sent_date`, \'%b %d, %Y\' )', $searchTerm],
					['like', 'date_format(`mail_sent_at`, \'%b %d, %Y\' )', $searchTerm],
				]);
			}
			$totalCount = $model->count();
			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
			}
			else
			{
				$model = $model->orderBy(['created_at' => SORT_DESC]);
			}
			



            $model = $model->limit($pageSize)->offset($page);
		}
		else
		{
			$totalCount = $model->count();
		}
		
		$mail_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $question)
			{
				$data=array();
				$data['id']=$question->id;
				$data['subject']=$question->subject;
				$data['body_content']=$question->body_content;
				$data['signature_id']=$question->signature_id;
				$data['signature_label']=$question->signature->title;
				$data['signature_logo']= $target_signature_dir.$question->signature->logo;
				$data['partners']=$question->partners;
				$data['partners_label']=$mailmodel->arrPartners[$question->partners];
				$data['auditors']=$question->auditors;
				$data['auditors_label']=$mailmodel->arrAuditors[$question->auditors];

				$librarymailstandard = $question->librarymailstandard;
				if(count($librarymailstandard)>0)
				{
					$standard_id_arr = array();
					$standard_id_label_arr = array();
					foreach($librarymailstandard as $val)
					{
						if($val->standard!==null)
						{
							$standard_id_arr[]="".$val['standard_id'];
							$standard_id_label_arr[]=($val->standard ? $val->standard->code : '');
						}
					}
					$data["clients"]=$standard_id_arr;
					$data["clients_label"]=implode(', ',$standard_id_label_arr);
				}
				// $data['clients']=$question->clients;
				// $data['clients_label']="All";
				// if($question->clients!='0' && $question->clients!='-1')
				// {
				// 	$data['clients_label']=$question->standard->name;
				// }else if($question->clients=='-1'){
				// 	$data['clients_label']='None';
				// }
				

				$data['sent_date']=($question->sent_date!='' && $question->sent_date!='0000-00-00' ? date($date_format,strtotime($question->sent_date)) : 'NA');
				$data['mail_sent_at']=($question->mail_sent_at!='' && $question->mail_sent_at!='0000-00-00' ? date($date_format,strtotime($question->mail_sent_at)) : 'NA');
				$data['consultants']=$question->consultants;
				$data['consultants_label']=$mailmodel->arrConsultants[$question->consultants];
				$data['subscribers']=$question->subscribers;
				$data['attachment']=$question->attachment;
				$data['subscribers_label']=$mailmodel->arrSubscribers[$question->subscribers];
				$data['status']=$question->status;
				$data['status_label']=$mailmodel->arrStatus[$question->status];
				$data['created_at']=date($date_format,$question->created_at);
				$mail_list[]=$data;
			}
		}

		return ['mails'=>$mail_list,'total'=>$totalCount];
	}
	
	public function actionGetData()
	{
		$model = new LibraryMail();
		$signmodel = Signature::find()->select('id,title')->where(['status' => 0])->asArray()->all();
		return ['signaturelist'=>$signmodel,'statuslist'=>$model->arrStatus,'partnerslist'=>$model->arrPartners,'auditorslist'=>$model->arrAuditors,'consultantslist'=>$model->arrConsultants,'subscriberslist'=>$model->arrSubscribers];
	}

	public function actionCreate()
	{
		$connection = Yii::$app->getDb();
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$datapost = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$target_dir = Yii::$app->params['library_files']."mail_attachments/"; 
		
		if($datapost){

			$data =json_decode($datapost['formvalues'],true);
		

			if(isset($data['id']))
			{
				$model = LibraryMail::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new LibraryMail();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
				LibraryMailStandard::deleteAll(['library_mail_id' => $data['id']]);
			}else{
				$model = new LibraryMail();
				$model->created_by = $userData['userid'];
			}

			
			$model->subject = $data['subject'];
			$model->body_content = $data['body_content'];
			$model->signature_id = $data['signature_id'];
			$model->sent_date = ($data['sent_date']!='' && $data['sent_date']!='0000-00-00' ? date("Y-m-d",strtotime($data['sent_date'])) : '');
			$model->partners = $data['partners'];
			$model->auditors = $data['auditors'];
			$model->consultants = $data['consultants'];
			$model->subscribers = $data['subscribers'];
			$model->status = $data['status'];	
			if($model->status=='1')
			{
				$model->mail_sent_at = date("Y-m-d");
			}	
			if(isset($_FILES['attachment']['name']))
			{
				$tmp_name = $_FILES["attachment"]["tmp_name"];
				$name = $_FILES["attachment"]["name"];
				if($model!==null)
				{
					Yii::$app->globalfuns->removeFiles($model->attachment,$target_dir);													
				}
				$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
			}else{
				$filename = isset($data['attachment'])?$data['attachment']:'';
			}
			$model->attachment = $filename;

			if($model->validate() && $model->save()) //
			{	

				$standardMailArr = [];
				if(is_array($data['clients']) && count($data['clients'])>0)
				{
					foreach ($data['clients'] as $value)
					{ 
						$LibraryMailStandardmodel =  new LibraryMailStandard();
						$LibraryMailStandardmodel->library_mail_id = $model->id;
						$LibraryMailStandardmodel->standard_id = $value;
						$LibraryMailStandardmodel->save();

						$standardMailArr[] = $value;
					}
				}



				if($data['status']!='2')
				{
					$toArr = [];
					if($model->partners=="1")
					{
						$franchiselist = User::find()->where(['user_type'=>3,'status'=>0])->all();	
						if(count($franchiselist)>0){
							foreach($franchiselist as $franchise){
								//$toArr[] = $franchise->email;
								if($franchise->usercompanyinfo){
									$toArr[] = $franchise->usercompanyinfo->company_email;
								}
							}
						}				
					}
					
					if(count($standardMailArr)>0){
						$standardStr = implode(',', $standardMailArr);
						$customerQry = 'select user.id,comp_info.company_email,comp_info.contact_name from tbl_application as app inner join tbl_application_standard as appstd on app.id=appstd.app_id inner join tbl_users as user on app.customer_id = user.id and user.user_type=2 inner join tbl_user_company_info as comp_info on comp_info.user_id = user.id  where user.status=0 and appstd.standard_id in ('.$standardStr.') group by user.id,comp_info.id';
						$command = $connection->createCommand($customerQry);
						$QryResult = $command->queryAll();			
						
						if(count($QryResult)>0){
							foreach($QryResult as $customer){
								$toArr[] = $customer['company_email'];
							}
						}
					}
					

					/*
					if($model->clients=="0")
					{
						$customerlist = User::find()->where(['user_type'=>2,'status'=>0])->all();	
						if(count($customerlist)>0){
							foreach($customerlist as $customer){
								if($customer->usercompanyinfo){
									$toArr[] = $customer->usercompanyinfo->company_email;
								}
								
							}
						}	
					
					}
					else if($model->clients!='-1')
					{
						$customerQry = 'select user.id,comp_info.company_email,comp_info.contact_name from tbl_application as app inner join tbl_application_standard as appstd on app.id=appstd.app_id inner join tbl_users as user on app.customer_id = user.id and user.user_type=2 inner join tbl_user_company_info as comp_info on comp_info.user_id = user.id  where user.status=0 and appstd.standard_id='.$model->clients.' group by user.id,comp_info.id';
						$command = $connection->createCommand($customerQry);
						$QryResult = $command->queryAll();			
						
						if(count($QryResult)>0){
							foreach($QryResult as $customer){
								$toArr[] = $customer['company_email'];
							}
						}	

					}
					*/
					 
					if($model->auditors=="1")
					{
						$auditorsQry = 'SELECT usr.first_name,usr.last_name,usr.email FROM `tbl_user_role` AS userrole
						INNER JOIN `tbl_rule` AS rule ON  userrole.role_id=rule.role_id AND rule.privilege="audit_execution" 
						INNER JOIN `tbl_users` AS usr ON usr.id = userrole.user_id where usr.status=0 and userrole.approval_status=2 group by usr.id';
						$command = $connection->createCommand($auditorsQry);
						$QryResult = $command->queryAll();			
						
						if(count($QryResult)>0){
							foreach($QryResult as $auditors){
								$toArr[] = $auditors['email'];
							}
						}	
					}
					$toArr = array_unique($toArr);
					if(count($toArr)>0)
					{
						$files = json_encode([$filename]);
						$signmodal = Signature::find()->where(['id'=>$model->signature_id])->one();
						foreach($toArr as $emailid)
						{
							$MailLookupModel = new MailLookup();
							$MailLookupModel->to=$emailid;													
							$MailLookupModel->subject=$data['subject'];
							$MailLookupModel->message=$this->renderPartial('@app/mail/layouts/mailPreviewTemplate',['content' => $data['body_content'],'sign' => $signmodal->logo]);
							$MailLookupModel->attachment= $files;
							$MailLookupModel->mail_notification_id='';
							$MailLookupModel->mail_notification_code='library';
							$Mailres=$MailLookupModel->sendMail();
							//https://swiftmailer.symfony.com/docs/messages.html
						}
					}
					
				}

				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Mail has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Mail has been created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}

	
	public function actionDeletemaildata()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$mailmodel = LibraryMail::find()->where(['id' => $data['id']])->one();
			if($mailmodel!==null){
				$filename = $mailmodel->attachment;
				$target_dir = Yii::$app->params['library_files']."mail_attachments/"; 
				$unlinkFile = $target_dir.$filename;
				if(file_exists($unlinkFile))
				{
					@unlink($unlinkFile);
				}
			}
			$model = LibraryMail::deleteAll(['id' => $data['id']]);
			LibraryMailStandard::deleteAll(['library_mail_id' => $data['id']]);
			$responsedata=array('status'=>1,'message'=>'Deleted successfully');
		}
		return $this->asJson($responsedata);
	}

	public function actionPreview()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$datapost = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$target_dir = Yii::$app->params['library_files']."mail_attachments/"; 
		
		if($datapost)
		{
			$resultarr = array();
			$signmodal = Signature::find()->where(['id'=>$datapost['signature_id']])->one();
			if ($signmodal !== null)
			{
				$resultarr['subject'] = $datapost['subject'];
				$resultarr['body_content'] = $this->renderPartial('@app/mail/layouts/mailPreviewTemplate',['content' => $datapost['body_content'],'sign' => $signmodal->logo]);
				
				$responsedata=array('status'=>1,'data'=>$resultarr);
			}
			
		}
		return $this->asJson($responsedata);
	}

	public function actionAttachmentfile(){
		$data = Yii::$app->request->post();
		$files = LibraryMail::find()->where(['id'=>$data['id']])->one();
		//if($data['filetype']=='gisfile'){
			$filename = $files->attachment;
		//}
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['library_files']."mail_attachments/".$filename;
		if(file_exists($filepath)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
			header('Access-Control-Expose-Headers: Content-Length,Content-Disposition,filename,Content-Type;');
			header('Access-Control-Allow-Headers: Content-Length,Content-Disposition,filename,Content-Type');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($filepath));
			flush(); // Flush system output buffer
			readfile($filepath);
		}
		die;
	}
	
	/*
	public function actionView()
    {
		$data = Yii::$app->request->post();
		
        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$resultarr["question"]=$model->question;
			$resultarr["answer"]=$model->answer;

			$libraryfaqfranchise = $model->libraryfaqfranchise;
			if(count($libraryfaqfranchise)>0)
			{
				$franchise_id_arr = array();
				$franchise_id_label_arr = array();
				foreach($libraryfaqfranchise as $val)
				{
					$franchise_id_arr[]=$val['franchise_id'];
					$franchise_id_label_arr[]=$val->franchise->usercompanyinfo?'OSP '.$val->franchise->usercompanyinfo->osp_number.' - '.$val->franchise->usercompanyinfo->osp_details:'';
				}
				$resultarr["franchise_id"]=$franchise_id_arr;
				$resultarr["franchise_id_label"]=implode(', ',$franchise_id_label_arr);
			}
						

			
			
            return ['data'=>$resultarr];
        }

	}
	
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
           	$model = LibraryFaq::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='FAQ has been activated successfully';
					}elseif($model->status==1){
						$msg='FAQ has been deactivated successfully';
					}elseif($model->status==2){
						$msg='FAQ has been deleted successfully';
					}
					$responsedata=array('status'=>1,'message'=>$msg);
				}
				else
				{
					$arrerrors=array();
					$errors=$model->errors;
					if(is_array($errors) && count($errors)>0)
					{
						foreach($errors as $err)
						{
							$arrerrors[]=implode(",",$err);
						}
					}
					$responsedata=array('status'=>0,'message'=>implode(",",$arrerrors));
				}
			}
			else
			{
				$arrerrors=array();
				$errors=$model->errors;
				if(is_array($errors) && count($errors)>0)
				{
					foreach($errors as $err)
					{
						$arrerrors[]=implode(",",$err);
					}
				}
				$responsedata=array('status'=>0,'message'=>$arrerrors);
			}
            return $this->asJson($responsedata);
        }
	}
	
    

    protected function findModel($id)
    {
        if (($model = LibraryFaq::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	*/
	
	
}
