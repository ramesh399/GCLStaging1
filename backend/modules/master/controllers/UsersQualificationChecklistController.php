<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\QualificationQuestion;
use app\modules\master\models\QualificationQuestionProcess;
use app\modules\master\models\QualificationQuestionStandard;
use app\modules\master\models\QualificationQuestionRole;

use app\modules\master\models\UserQualificationReview;
use app\modules\master\models\UserQualificationReviewComment;
use app\modules\master\models\User;
use app\modules\master\models\UserStandard;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * QualificationChecklistController implements the CRUD actions for Product model.
 */
class UsersQualificationChecklistController extends \yii\rest\Controller
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
		
		$model = QualificationQuestion::find();
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 'name', $searchTerm],
					['like', '(date_format(FROM_UNIXTIME(`created_at` ), \'%b %d, %Y\' ))', $searchTerm],
				]);

				$totalCount = $model->count();
			}
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
		
		$question_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $question)
			{
				$data=array();
				$data['id']=$question->id;
				$data['name']=$question->name;
				//$data['created_at']=date('M d,Y h:i A',$question->created_at);
				$data['created_at']=date($date_format,$question->created_at);
				$question_list[]=$data;
			}
		}

		return ['qualificationchecklists'=>$question_list,'total'=>$totalCount];
    }

    public function actionCreate()
	{
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$datapost = Yii::$app->request->post();
		$data = json_decode($datapost['formvalues'],true);

		if ($data) 
		{	
			$target_dir = Yii::$app->params['user_qualification_review_files']; 
			foreach($data['standard'] as $data)
			{
				$model = new UserQualificationReview();
				$model->user_id = $data['user_id'];
				$model->standard_id = $data['standard_id'];
				$model->qualification_status = $data['qualification_status'];
				$model->qualified_date = date('Y-m-d');
				
				$standard_id = $data['standard_id'];
				// $userData = Yii::$app->userdata->getData();
				// $model->qualified_by = $userData['qualified_by'];
				// $model->created_by = $userData['userid'];
				
				if($model->validate() && $model->save())
				{
					if(is_array($data['questions']) && count($data['questions'])>0)
					{


						foreach ($data['questions'] as $value)
						{ 
							$question_id = $value['question_id'];
							$name = '';
							
							if(isset($_FILES['questionfile']['name'][$standard_id.$question_id]))
							{
								
								$filename = $_FILES['questionfile']['name'][$standard_id.$question_id];//){
								
								$target_file = $target_dir . basename($filename);
								$actual_name = pathinfo($filename,PATHINFO_FILENAME);
								$original_name = $actual_name;
								$extension = pathinfo($filename, PATHINFO_EXTENSION);
								$name = $actual_name.".".$extension;
								$i = 1;
								while(file_exists($target_dir.$actual_name.".".$extension))
								{           
									$actual_name = (string)$original_name.$i;
									$name = $actual_name.".".$extension;
									$i++;
								}
								if (move_uploaded_file($_FILES['questionfile']["tmp_name"][$standard_id.$question_id], $target_dir .$actual_name.".".$extension)) {
								}
							}
							
							$userqualificationreviewcmt =  new UserQualificationReviewComment();
							$userqualificationreviewcmt->user_qualification_review_id = $model->id;
							$userqualificationreviewcmt->qualification_question_id = $value['question_id'];
							$userqualificationreviewcmt->recurring_period = $value['recurring_period'];
							$userqualificationreviewcmt->question = $value['question'];
							$userqualificationreviewcmt->answer = $value['answer'];
							$userqualificationreviewcmt->comment = $value['comment'];
							$userqualificationreviewcmt->valid_until = $value['valid_until'];
							$userqualificationreviewcmt->file = $name;
							$userqualificationreviewcmt->save();
						}
						
					}
				}
			}
			$responsedata=array('status'=>1,'message'=>'User Qualification review has been created successfully');	
		}
		return $this->asJson($responsedata);
	}

    public function actionUpdate()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$model = QualificationQuestion::find()->where(['id' => $data['id']])->one();
			$model->name = $data['name'];
			$model->guidance = $data['guidance'];
			$model->file_upload_required = $data['file_upload_required'];
			$model->recurring_period = $data['recurring_period'];
			
			$userData = Yii::$app->userdata->getData();
			$model->created_by = $userData['userid'];
			
			if($model->validate() && $model->save())
			{

				if(is_array($data['standard']) && count($data['standard'])>0)
                {
					QualificationQuestionStandard::deleteAll(['qualification_question_id' => $model->id]);
                    foreach ($data['standard'] as $value)
                    { 
						$qualificationstdmodel =  new QualificationQuestionStandard();
						$qualificationstdmodel->qualification_question_id = $model->id;
						$qualificationstdmodel->standard_id = $value;
						$qualificationstdmodel->save();
					}
				}

				if(is_array($data['process']) && count($data['process'])>0)
                {
					QualificationQuestionProcess::deleteAll(['qualification_question_id' => $model->id]);
                    foreach ($data['process'] as $value)
                    { 
						$qualificationprocessmodel =  new QualificationQuestionProcess();
						$qualificationprocessmodel->qualification_question_id = $model->id;
						$qualificationprocessmodel->process_id = $value;
						$qualificationprocessmodel->save();
					}
				}

				if(is_array($data['role']) && count($data['role'])>0)
                {
					QualificationQuestionRole::deleteAll(['qualification_question_id' => $model->id]);
                    foreach ($data['role'] as $value)
                    { 
						$qualificationrolemodel =  new QualificationQuestionRole();
						$qualificationrolemodel->qualification_question_id = $model->id;
						$qualificationrolemodel->role_id = $value;
						$qualificationrolemodel->save();
					}
				}
				$responsedata=array('status'=>1,'message'=>'Qualification Questions has been updated successfully');	
			}
		}
		return $this->asJson($responsedata);
    }

    public function actionView()
    {
		$data = Yii::$app->request->post();
		$qmodel = new UserQualificationReview;
		$answers = $qmodel->answers;
		$recurring_period = $qmodel->recurring_period;
		//print_r($data); die;standard_ids,user_id,role_ids
		if($data)
		{			
			$dataarr = [];
			
			$userModel = User::find()->where(['id'=>$data['user_id']])->one();
			$arrUserDetails=array();
			$arrUserDetails['first_name']=$userModel->first_name;
			$arrUserDetails['last_name']=$userModel->last_name;
			$arrUserDetails['email']=$userModel->email;
			$arrUserDetails['telephone']=$userModel->telephone;
			$arrUserDetails['country_name']=$userModel->country->name;
			$arrUserDetails['state_name']=$userModel->state->name;
			$dataarr['userdetails']=$arrUserDetails;
			
			$resultarr=array();
			$connection = Yii::$app->getDb();
			$command = $connection->createCommand("SELECT std.name as standard_name,quastd.standard_id as standard_id,quaqtn.* 
			FROM `tbl_users` AS usr INNER JOIN `tbl_user_standard` AS usrstd ON usr.id=usrstd.user_id AND usr.id=".$data['user_id']."
			INNER JOIN `tbl_user_process` AS usrprs ON usr.id=usrprs.user_id
			INNER JOIN `tbl_user_role` AS usrrole ON usr.id=usrrole.user_id
			INNER JOIN `tbl_qualification_question_standard` AS quastd ON usrstd.standard_id=quastd.standard_id
			INNER JOIN `tbl_qualification_question_process` AS quaprs ON usrprs.process_id=quaprs.process_id
			INNER JOIN `tbl_qualification_question_role` AS quarole ON usrrole.role_id=quarole.role_id
			INNER JOIN `tbl_qualification_question` AS quaqtn ON quaqtn.id=quastd.qualification_question_id AND quaqtn.id=quaprs.qualification_question_id AND quaqtn.id=quarole.qualification_question_id
			INNER JOIN `tbl_standard` AS std ON std.id=quastd.standard_id		
			where std.id in (".$data['standard_ids'].") and usr.id in (".$data['role_ids'].") 
			GROUP BY quaqtn.id,quastd.standard_id");
			
			$result = $command->queryAll();
			
			if(count($result)>0)
			{
				
				foreach($result as $res)
				{
					$questions_arr = array();
					$resultstdsarr=array();
					$resultstdarr[$res["standard_id"]]=$res["standard_id"];

					$resultstdsarr["id"]=$res["standard_id"];
					$resultstdsarr["name"]=$res["standard_name"];
					$standrads_arr[$res["standard_id"]]=$resultstdsarr;

					$questions_arr["id"]=$res["id"];
					$questions_arr["name"]=$res["name"];
					$questions_arr["code"]=$res["code"];
					$questions_arr["guidance"]=$res["guidance"];
					$questions_arr["file_upload_required"]=$res["file_upload_required"];
					$questions_arr["recurring_period"]=$res["recurring_period"];
					
					/*
					$arrQualificationAnswer=array();						
					$userQualificationReviewModel = UserQualificationReviewComment::find()->where(['user_id'=>$data['user_id'],'standard_id'=>$standard])->one();
					if($userQualificationReviewModel!==null)
					{
						foreach($qualificationreviewcommentmodel as $qualificationreviewcomment)
						{
							$arrQualificationAnswer['user_qualification_review_id']=$qualificationreviewcomment->user_qualification_review_id;
							$arrQualificationAnswer['qualification_question_id']=$qualificationreviewcomment->qualification_question_id;
							$arrQualificationAnswer['recurring_period']=$qualificationreviewcomment->recurring_period;
							$arrQualificationAnswer['question']=$qualificationreviewcomment->question;
							$arrQualificationAnswer['answer']=$qualificationreviewcomment->answer;
							
							$arrQualificationAnswer['comment']=$qualificationreviewcomment->comment;
							$arrQualificationAnswer['valid_until']=$qualificationreviewcomment->valid_until;
							$arrQualificationAnswer['file']=$qualificationreviewcomment->file;
						}
						$dataarr['standard'][$i]['question'] = $arrQualificationAnswer;
					}
					*/
					
					$resultarr[]=$questions_arr;

					//$standrads_arr["standards"][$res["standard_id"]]["questions"]=$resultarr;
				}
				/*
				$i=0;
				foreach($resultstdarr as $standard)
				{
					$dataarr['standard'][$i] = $standrads_arr[$standard];
					$dataarr['standard'][$i]['question'] = $resultarr[$standard];
					
					$arrQualificationAnswer=array();
					$userQualificationReviewModel = UserQualificationReview::find()->where(['user_id'=>$data['user_id'],'standard_id'=>$standard])->one();
					if($userQualificationReviewModel!==null)
					{
						$arrQualificationDetails=array();
						$arrQualificationDetails['qualification_status']=$userQualificationReviewModel->qualification_status;
						$arrQualificationDetails['qualification_status_label']=$userQualificationReviewModel->arrQualificationStatus[$userQualificationReviewModel->qualification_status];
						$arrQualificationDetails['qualified_date']=$userQualificationReviewModel->qualified_date;
						$arrQualificationDetails['qualified_by']=$userQualificationReviewModel->qualifiedby->first_name.' '.$userQualificationReviewModel->qualifiedby->last_name;
						$arrQualificationDetails['created_by']=$userQualificationReviewModel->createdby->first_name.' '.$userQualificationReviewModel->createdby->last_name;
						
						$dataarr['standard'][$i]['qualificationreview']=$arrQualificationDetails;						
												
						$qualificationreviewcommentmodel = $userQualificationReviewModel->qualificationreviewcomment;						
						if(count($qualificationreviewcommentmodel)>0)
						{
							foreach($qualificationreviewcommentmodel as $qualificationreviewcomment)
							{
								$qID=$qualificationreviewcomment->qualification_question_id;
								$arrQA=array();
								$arrQA['user_qualification_review_id']=$qualificationreviewcomment->user_qualification_review_id;
								$arrQA['qualification_question_id']=$qID;
								$arrQA['recurring_period']=$qualificationreviewcomment->recurring_period;
								$arrQA['question']=$qualificationreviewcomment->question;
								$arrQA['answer']=$qualificationreviewcomment->answer;
								
								$arrQA['comment']=$qualificationreviewcomment->comment;
								$arrQA['valid_until']=$qualificationreviewcomment->valid_until;
								$arrQA['file']=$qualificationreviewcomment->file;
								$arrQualificationAnswer[$qID]=$arrQA;
							}							
						}										   
					}
					$dataarr['standard'][$i]['answer'] = $arrQualificationAnswer;					
					$i++;
				}
				*/
				
			}
			$dataarr['answerArr'] = $answers;
			$dataarr['recurringPeriod'] = $recurring_period;
			return ['data'=>$dataarr];			
		}
	}
	
	public function actionQualificationView()
    {
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
				
		$data = Yii::$app->request->post();
		$data['id']=104;	
		$qmodel = new UserQualificationReview;
		$answers = $qmodel->answers;
		$recurring_period = $qmodel->recurring_period;
		
		$connection = Yii::$app->getDb();
		
		if($data)
		{			
			$dataarr = [];
			
			$userModel = User::find()->where(['id'=>$data['id']])->one();
			$arrUserDetails=array();
			$arrUserDetails['first_name']=$userModel->first_name;
			$arrUserDetails['last_name']=$userModel->last_name;
			$arrUserDetails['email']=$userModel->email;
			$arrUserDetails['telephone']=$userModel->telephone;
			$arrUserDetails['country_name']=$userModel->country ? $userModel->country->name : 'NA';
			$arrUserDetails['state_name']=$userModel->state->name;
			$dataarr['userdetails']=$arrUserDetails;
						
			$arrStds=array();	

			//,'standard_id'=>$standard
			$arrQualificationReveiwDetailsArray=array();
			//$userQualificationReviewM = UserQualificationReview::find()->where(['user_id'=>$data['id']])->orderBy('id desc')->all();
			$resultarr=array();
			$connection = Yii::$app->getDb();
			
			/*$command = $connection->createCommand("SELECT group_concat(distinct qreview.standard_id),group_concat(distinct qreview.user_role_id),rcomment.* 
			FROM  `tbl_user_qualification_review` qreview INNER JOIN `tbl_user_qualification_review_rel_comment` relcmt on relcmt.user_qualification_review_id =qreview.id
			INNER JOIN `tbl_user_qualification_review_comment` rcomment on relcmt.user_qualification_review_comment_id=rcomment.id
			WHERE rcomment.user_id=".$data['id']."
            group by rcomment.id, qreview.standard_id,qreview.user_role_id
			 ");
			*/

			//for checking any answer is not qualified standard with roler for user
			$command = $connection->createCommand("SELECT qreview.id as reviewid,group_concat(distinct qreview.standard_id),group_concat(distinct qreview.user_role_id) 
			FROM  `tbl_user_qualification_review` qreview INNER JOIN `tbl_user_qualification_review_rel_comment` relcmt on relcmt.user_qualification_review_id =qreview.id
			INNER JOIN `tbl_user_qualification_review_comment` rcomment on relcmt.user_qualification_review_comment_id=rcomment.id
			WHERE rcomment.user_id=".$data['id']." and rcomment.answer=2 and review_status=0 
			group by rcomment.id, qreview.standard_id,qreview.user_role_id order by qreview.standard_id,qreview.user_role_id");
			
			$result = $command->queryAll();

			$reviewIds = [];
			if(count($result)>0)
			{
				foreach($result as $res)
				{
					$reviewIds[] = $res['reviewid'];
				}
				$reviewIdNotIn = ' AND qreview.id not in ('.implode(',',$reviewIds).') ';
			}


			//get all qualified questions
			$command = $connection->createCommand("
			SELECT qreview.id as reviewid,group_concat(distinct qreview.standard_id),group_concat(distinct qreview.user_role_id), rcomment.* 
			FROM  `tbl_user_qualification_review` qreview INNER JOIN `tbl_user_qualification_review_rel_comment` relcmt on relcmt.user_qualification_review_id =qreview.id
			INNER JOIN `tbl_user_qualification_review_comment` rcomment on relcmt.user_qualification_review_comment_id=rcomment.id
			WHERE rcomment.user_id=".$data['id']." and rcomment.answer=1 and review_status=0  ".$reviewIdNotIn."
			group by rcomment.id, qreview.standard_id,qreview.user_role_id order by qreview.standard_id,qreview.user_role_id");
			$result = $command->queryAll();

			if(count($userQualificationReviewM)>0)
			{
				foreach($userQualificationReviewM as $userQualificationReviewModel)
				{
					
					$qualified_date = $arrQualificationDetails['qualified_date']=$userQualificationReviewModel->qualified_date;
					
					$arrQualificationDetails=array();
					$arrQualificationDetails['qualification_status']=$userQualificationReviewModel->qualification_status;
					$arrQualificationDetails['qualification_status_label']=$userQualificationReviewModel->arrQualificationStatus[$userQualificationReviewModel->qualification_status];
					$arrQualificationDetails['qualified_date']=($qualified_date!='' && $qualified_date!='0000-00-00' ? date($date_format,strtotime($qualified_date)) : '');
					$arrQualificationDetails['qualified_by']=$userQualificationReviewModel->qualifiedby ? $userQualificationReviewModel->qualifiedby->first_name.' '.$userQualificationReviewModel->qualifiedby->last_name : 'NA';
					$arrQualificationDetails['created_by']=$userQualificationReviewModel->createdby ? $userQualificationReviewModel->createdby->first_name.' '.$userQualificationReviewModel->createdby->last_name : 'NA';
					
					//$dataarr['standard'][$i]['qualificationreview']=$arrQualificationDetails;						
											
					$qualificationreviewcommentmodel = $userQualificationReviewModel->qualificationreviewcomment;						
					if(count($qualificationreviewcommentmodel)>0)
					{
						$qualificationReviewCommentArray=array();
						foreach($qualificationreviewcommentmodel as $qualificationreviewcomment)
						{
							$qID=$qualificationreviewcomment->qualification_question_id;
							$arrQA=array();
							$arrQA['user_qualification_review_id']=$qualificationreviewcomment->user_qualification_review_id;
							$arrQA['qualification_question_id']=$qID;
							$arrQA['recurring_period']=$qualificationreviewcomment->recurring_period;
							$arrQA['recurring_period_name']=$userQualificationReviewModel->recurring_period[$qualificationreviewcomment->recurring_period];
							$arrQA['question']=$qualificationreviewcomment->question;
							$arrQA['guidance']=$qualificationreviewcomment->qualificationquestion->guidance;
							
							
							$arrQA['answer']=$qualificationreviewcomment->answer;
							$arrQA['answer_name']=$userQualificationReviewModel->answers[$qualificationreviewcomment->answer];
							
							$arrQA['comment']=$qualificationreviewcomment->comment;
							$arrQA['valid_until']=$qualificationreviewcomment->valid_until;
							$arrQA['file']=$qualificationreviewcomment->file;
							$qualificationReviewCommentArray[]=$arrQA;
						}							
					}
					$arrQualificationReveiwDetailsArray['qualified_details']=$arrQualificationDetails;
					$arrQualificationReveiwDetailsArray['qualified_comment']=$qualificationReviewCommentArray;
					
					$dataarr['standard'][$i]['qualifications'][] = $arrQualificationReveiwDetailsArray;	
				}					
									
			}

			
			//$UserStandarModel = UserStandard::find()->select('standard_id')->where(['user_id' => $data['id']])->all();
			/*		
			if(count($UserStandarModel)>0)				
			{
				$i=0;
				foreach($UserStandarModel as $userStd)
				{
					$standard = $userStd->standard_id;
					
					$resultstdsarr=array();
					$resultstdsarr["id"]=$userStd->standard_id;
					$resultstdsarr["name"]=$userStd->standard->name;
					$dataarr['standard'][$i] = $resultstdsarr;
										
					$arrQualificationReveiwDetailsArray=array();
					$userQualificationReviewM = UserQualificationReview::find()->where(['user_id'=>$data['id'],'standard_id'=>$standard])->orderBy('id desc')->all();
					if(count($userQualificationReviewM)>0)
					{
						foreach($userQualificationReviewM as $userQualificationReviewModel)
						{
							
							$qualified_date = $arrQualificationDetails['qualified_date']=$userQualificationReviewModel->qualified_date;
							
							$arrQualificationDetails=array();
							$arrQualificationDetails['qualification_status']=$userQualificationReviewModel->qualification_status;
							$arrQualificationDetails['qualification_status_label']=$userQualificationReviewModel->arrQualificationStatus[$userQualificationReviewModel->qualification_status];
							$arrQualificationDetails['qualified_date']=($qualified_date!='' && $qualified_date!='0000-00-00' ? date($date_format,strtotime($qualified_date)) : '');
							$arrQualificationDetails['qualified_by']=$userQualificationReviewModel->qualifiedby ? $userQualificationReviewModel->qualifiedby->first_name.' '.$userQualificationReviewModel->qualifiedby->last_name : 'NA';
							$arrQualificationDetails['created_by']=$userQualificationReviewModel->createdby ? $userQualificationReviewModel->createdby->first_name.' '.$userQualificationReviewModel->createdby->last_name : 'NA';
							
							//$dataarr['standard'][$i]['qualificationreview']=$arrQualificationDetails;						
													
							$qualificationreviewcommentmodel = $userQualificationReviewModel->qualificationreviewcomment;						
							if(count($qualificationreviewcommentmodel)>0)
							{
								$qualificationReviewCommentArray=array();
								foreach($qualificationreviewcommentmodel as $qualificationreviewcomment)
								{
									$qID=$qualificationreviewcomment->qualification_question_id;
									$arrQA=array();
									$arrQA['user_qualification_review_id']=$qualificationreviewcomment->user_qualification_review_id;
									$arrQA['qualification_question_id']=$qID;
									$arrQA['recurring_period']=$qualificationreviewcomment->recurring_period;
									$arrQA['recurring_period_name']=$userQualificationReviewModel->recurring_period[$qualificationreviewcomment->recurring_period];
									$arrQA['question']=$qualificationreviewcomment->question;
									$arrQA['guidance']=$qualificationreviewcomment->qualificationquestion->guidance;
									
									
									$arrQA['answer']=$qualificationreviewcomment->answer;
									$arrQA['answer_name']=$userQualificationReviewModel->answers[$qualificationreviewcomment->answer];
									
									$arrQA['comment']=$qualificationreviewcomment->comment;
									$arrQA['valid_until']=$qualificationreviewcomment->valid_until;
									$arrQA['file']=$qualificationreviewcomment->file;
									$qualificationReviewCommentArray[]=$arrQA;
								}							
							}
							$arrQualificationReveiwDetailsArray['qualified_details']=$arrQualificationDetails;
							$arrQualificationReveiwDetailsArray['qualified_comment']=$qualificationReviewCommentArray;
							
							$dataarr['standard'][$i]['qualifications'][] = $arrQualificationReveiwDetailsArray;	
						}					
											
					}					
					
					$i++;					
				}				
			}
			*/
			
			//echo '<pre>';
			//var_dump($dataarr);
			//die();		
			
			return ['data'=>$dataarr];			
		}
	}
	
	
    protected function findModel($id)
    {
        if (($model = QualificationQuestion::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	public function actionRecurringPeriod()
	{
		$model = new QualificationQuestion();
		
		return ['recurringperiod'=>$model->arrRecurringPeriod];
	}
}
