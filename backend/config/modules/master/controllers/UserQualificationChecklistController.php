<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\QualificationQuestion;
use app\modules\master\models\QualificationQuestionProcess;
use app\modules\master\models\QualificationQuestionStandard;
use app\modules\master\models\QualificationQuestionRole;

use app\modules\master\models\UserQualificationReview;
use app\modules\master\models\UserQualificationReviewComment;
use app\modules\master\models\UserQualificationReviewRelComment;

use app\modules\master\models\UserQualificationReviewHistory;
use app\modules\master\models\UserQualificationReviewHistoryComment;
use app\modules\master\models\UserQualificationReviewHistoryRelRoleStandard;
use app\modules\master\models\UserQualificationReviewHistoryRelRoleStandardComment;
use app\modules\master\models\User;
use app\modules\master\models\UserStandard;
use app\modules\master\models\UserBusinessSector;
use app\modules\master\models\UserBusinessSectorGroup;
use app\modules\master\models\BusinessSectorGroup;

use app\modules\master\models\UserBusinessGroup;


use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * QualificationChecklistController implements the CRUD actions for Product model.
 */
class UserQualificationChecklistController extends \yii\rest\Controller
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
		//print_r($data); die;
		if ($data) 
		{	
			$userData = Yii::$app->userdata->getData();

			$target_dir = Yii::$app->params['user_qualification_review_files']; 

			
			
			foreach($data['standard'] as $data)
			{

				$questions = $data['questions'];
				//print_r($questions); die;
				$questionsListArr = [];
				$questionsListPrimaryIdArr = [];
				foreach($questions as &$question ){

					if($question['answer'] == 1){
						$standard_idsArr = explode(',',$question['standard_ids']);
						$role_idsArr = explode(',',$question['role_ids']);
						$business_sector_group_idsArr = explode(',',$question['business_sector_group_ids']);
						 
						$question_id = $question['question_id'];
						$questionsListArr[] = $question_id;

						$answermodel = UserQualificationReviewComment::find()->where(['user_id' => $data['user_id'],
										'qualification_question_id'=> $question_id])->one();

						if($answermodel === null){
							$answermodel = new UserQualificationReviewComment();
						}else{

						}
						$name = '';
						if(isset($_FILES['questionfile']['name'][$question_id]))
						{
							
							$filename = $_FILES['questionfile']['name'][$question_id];//){
							
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
							if (move_uploaded_file($_FILES['questionfile']["tmp_name"][$question_id], $target_dir .$actual_name.".".$extension)) {
							}
							$question['file'] = $name;
						}else if($question['file']){
							$name = $question['file'];
						}
						$answermodel->user_id = $data['user_id'];
						$answermodel->qualification_question_id = $question_id;
						$answermodel->question = $question['question'];
						$answermodel->answer = $question['answer'];
						$answermodel->comment = $question['comment'];
						$answermodel->review_status = 0;
						$answermodel->file = $name;
						$answermodel->reviewed_by = $userData['userid'];
						$answermodel->reviewed_date = time();
						$answermodel->save();
						//echo $answermodel->id;
						//echo '<br>';
						$questionsListPrimaryIdArr[$question_id] = $answermodel->id;
						//print_r($standard_idsArr);
						
						
						foreach($standard_idsArr as $standardId){
							
							foreach($role_idsArr as $roleid){
								
								foreach($business_sector_group_idsArr as $sectorgp){
									//print_r(['id'=>$sectorgp,'standard_id'=>$standardId]);
									
									$businesssectorgroup = BusinessSectorGroup::find()->where(['id'=>$sectorgp,'standard_id'=>$standardId])->one();
									//$standard_id = $businesssectorgroup->standard_id;
									//echo $businesssectorgroup==null?'null':'data'.'<br><br>';
									//$businesssectorgroup = BusinessSector::find()->where(['id'=>$sectorgp,'standard_id'=>$standardId])->one();
									if($businesssectorgroup !== null){
										$rmodel = UserQualificationReview::find()->where(['user_id' => $data['user_id'],
												'standard_id'=> $standardId,
												'user_role_id'=>$roleid,
												'business_sector_group_id' => $sectorgp ])->one();	
										if($rmodel === null){
											$rmodel = new UserQualificationReview();
											$rmodel->user_id = $data['user_id'];
											$rmodel->user_role_id = $roleid;
											$rmodel->standard_id = $standardId;
											$rmodel->business_sector_group_id = $sectorgp;
											$rmodel->business_sector_id = $businesssectorgroup->business_sector_id;
											$rmodel->created_by = $userData['userid'];
											$rmodel->created_at = time();
											$rmodel->save();


											$relmodel = new UserQualificationReviewRelComment();
											$relmodel->user_qualification_review_id = $rmodel->id;
											$relmodel->qualification_question_id = $question_id;
											$relmodel->user_qualification_review_comment_id = $answermodel->id;
											$relmodel->already_qualified = 0;
											$relmodel->save();
										}else{
											$relmodel = new UserQualificationReviewRelComment();
											$relmodel->user_qualification_review_id = $rmodel->id;
											$relmodel->qualification_question_id = $question_id;
											$relmodel->user_qualification_review_comment_id = $answermodel->id;
											$relmodel->already_qualified = 0;
											$relmodel->save();
										}
									}
								}
									
							}
							
						}
					}
				}
				


				//die;
				if(1){
					// To map other user question to any other standard and roles
					$user_id = $data['user_id'];
					foreach($questionsListArr as $questiondt){
						$arrStdRole = $this->getQuestionStdRole($user_id,$questiondt);


						
						$standard_idsArr = explode(',',$arrStdRole['standard_ids']);
						$role_idsArr = explode(',',$arrStdRole['role_ids']);
						$business_sector_group_idsArr = explode(',',$arrStdRole['business_sector_group_ids']);
						
						foreach($standard_idsArr as $standardid){
							foreach($role_idsArr as $roleid){
								//$roleid
								//$questionsListPrimaryIdArr

								foreach($business_sector_group_idsArr as $sectorgp){

									//print_r(['id'=>$sectorgp,'standard_id'=>$standardid]);

									$businesssectorgroup = BusinessSectorGroup::find()->where(['id'=>$sectorgp,'standard_id'=>$standardid])->one();
									//echo $businesssectorgroup==null?'null':'data'.'<br><br>';

									if($businesssectorgroup !== null){
										$rmodel = UserQualificationReview::find()->where(['user_id' => $user_id,
													'standard_id'=> $standardid,
													'user_role_id'=>$roleid,
													'business_sector_group_id' => $sectorgp ])->one();	
										if($rmodel===null){
											$rmodel = new UserQualificationReview();
											$rmodel->user_id = $user_id;
											$rmodel->user_role_id = $roleid; 
											$rmodel->standard_id = $standardid;
											$rmodel->business_sector_group_id = $sectorgp;
											$rmodel->business_sector_id = $businesssectorgroup->business_sector_id;
											$rmodel->created_by = $userData['userid'];
											$rmodel->created_at = time();
											$rmodel->save();

											$relmodel = new UserQualificationReviewRelComment();
											$relmodel->user_qualification_review_id = $rmodel->id;
											$relmodel->qualification_question_id = $questiondt;
											$relmodel->user_qualification_review_comment_id = $questionsListPrimaryIdArr[$questiondt];
											$relmodel->already_qualified = 1;
											$relmodel->save();

										}else{
											$relquestionmodel = UserQualificationReviewRelComment::find()->where(['qualification_question_id' => $questiondt,
													'user_qualification_review_id'=> $rmodel->id])->one();
											if($relquestionmodel === null){
												$relmodel = new UserQualificationReviewRelComment();
												$relmodel->user_qualification_review_id = $rmodel->id;
												$relmodel->qualification_question_id = $questiondt;
												$relmodel->user_qualification_review_comment_id = $questionsListPrimaryIdArr[$questiondt];
												$relmodel->already_qualified = 1;
												$relmodel->save();
											}

										}
									}
								}

								

							}
						}

					}
				}
				$data['questions'] = $questions;
				// die;
				$this->createHistory($data);
			}
			
			$responsedata=array('status'=>1,'message'=>'User Qualification review has been created successfully');	
		}
		return $this->asJson($responsedata);
	}


	public function getQuestionStdRole($user_id,$qid)
	{
		 
		
		$resultarr=array();
		$connection = Yii::$app->getDb();
		$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();
		$command = $connection->createCommand("SELECT

		 quaqtn.id,GROUP_CONCAT(distinct usrrole.role_id) as role_ids,
		 GROUP_CONCAT(distinct quastd.standard_id) as standard_ids ,
		 GROUP_CONCAT(distinct usrsectorgroup.business_sector_group_id) as business_sector_group_ids 

			FROM `tbl_users` AS usr
			INNER JOIN `tbl_user_standard` AS usrstd ON usr.id=usrstd.user_id AND usr.id=".$user_id." 
			
			INNER JOIN `tbl_user_role` AS usrrole ON usr.id=usrrole.user_id
			INNER JOIN `tbl_user_business_group` AS usrgroup ON usr.id=usrgroup.user_id  and usrgroup.standard_id = usrstd.standard_id  AND usrgroup.user_id=".$user_id." 
			INNER JOIN `tbl_user_business_group_code` AS usrsectorgroup ON usrgroup.id=usrsectorgroup.business_group_id 


			INNER JOIN `tbl_qualification_question_standard` AS quastd ON usrstd.standard_id=quastd.standard_id
			INNER JOIN `tbl_qualification_question_role` AS quarole ON usrrole.role_id=quarole.role_id 
			INNER JOIN `tbl_qualification_question_business_sector` AS quasector ON usrgroup.business_sector_id=quasector.business_sector_id 
			INNER JOIN `tbl_qualification_question_business_sector_group` AS sectorgroup ON usrsectorgroup.business_sector_group_id=sectorgroup.business_sector_group_id


			INNER JOIN `tbl_qualification_question` AS quaqtn ON quaqtn.id=quastd.qualification_question_id
			  AND quaqtn.id=quarole.qualification_question_id
			AND quaqtn.id=sectorgroup.qualification_question_id 
			
			where quaqtn.id=".$qid." and  usr.id=".$user_id." GROUP BY quaqtn.id");
		
		$result = $command->queryAll();
		if(count($result)>0){
			foreach($result as $data){
				$role_ids = $data['role_ids'];
				$standard_ids = $data['standard_ids'];
				$business_sector_group_ids = $data['business_sector_group_ids'];
			}
		}
		return ['role_ids'=>$role_ids,'standard_ids'=>$standard_ids,'business_sector_group_ids'=>$business_sector_group_ids];
		//$result = $command->one();
		
		//$questionscount=count($result);



	}

	public function actionApprove()
	{
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		//$data = json_decode($datapost['formvalues'],true);
		//print_r($data); die;
		if ($data) 
		{	
			$userData = Yii::$app->userdata->getData();

			$target_dir = Yii::$app->params['user_qualification_review_files']; 

			
			
			
			foreach($data['standard'] as $dataval)
			{

				$questions = $dataval['questions'];
				foreach($questions as $question ){
					//$standard_idsArr = explode(',',$question['standard_ids']);
					//$role_idsArr = explode(',',$question['role_ids']);
					
					$question_id = $question['question_id'];

					$answermodel = UserQualificationReviewComment::find()->where(['user_id' => $dataval['user_id'],
									'qualification_question_id'=> $question_id])->one();
					$answermodel->recurring_period = $question['recurring_period'];
					if($question['recurring_period'] !=6){
						$answermodel->valid_until = date('Y-m-d',strtotime($question['valid_until']));
					}
					$answermodel->approved_by = $userData['userid'];
					$answermodel->approved_date = time();
					$answermodel->review_status = 1;
					$answermodel->save();
				
				}
				
			}
			
			$qdata = $data['standard'][0];
			$modelreview = UserQualificationReview::find()->where(['user_id' => $qdata['user_id']])->all();
			$qualificationapp = [];
			foreach($modelreview as $qrdata){
				// true / false
				$qualificationstatus = $this->checkValidUser($qrdata->user_id,$qrdata->standard_id,$qrdata->user_role_id,$qrdata->business_sector_group_id);
				if($qualificationstatus){
					$qrdata->qualification_status =1;
					$qrdata->qualified_by = $userData['userid'];
					$qrdata->qualified_date = date('Y-m-d',time());
					$qrdata->save();
					//$qualificationapp[] = $qrdata->id;
				}
				/*else{
					$qrdata->qualification_status = 0;
				}*/
			}

			$this->createHistory($qdata,2);
			$responsedata=array('status'=>1,'message'=>'User Qualification question approved successfully');	
		}
		return $this->asJson($responsedata);
	}


	public function createHistory($data,$historyType=1)
    {
		
        $responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        if ($data) 
		{	
			$userData = Yii::$app->userdata->getData();
			
			$UserQualificationReviewHistory= new UserQualificationReviewHistory();
			$UserQualificationReviewHistory->user_id=$data['user_id'];
			$UserQualificationReviewHistory->history_type=$historyType;
			$UserQualificationReviewHistory->created_by = $userData['userid'];
			$UserQualificationReviewHistory->created_at=time(); //strtotime(date('M d,Y h:i A'));
			if($UserQualificationReviewHistory->validate() && $UserQualificationReviewHistory->save())
        	{ 
				if(is_array($data['questions']) && count($data['questions'])>0)
				{
					foreach ($data['questions'] as $value)
					{ 
						//if($historyType == 1){  $answer=$value['answer']; }else{ $answer= $historyType;}
						
						$answer=$value['answer'];
						
						$UserQualificationReviewHistoryComment=new UserQualificationReviewHistoryComment();
						$UserQualificationReviewHistoryComment->review_history_id=$UserQualificationReviewHistory->id;
						$UserQualificationReviewHistoryComment->qualification_question_id=isset($value['question_id'])?$value['question_id']:"";
						$UserQualificationReviewHistoryComment->recurring_period=isset($value['recurring_period'])?$value['recurring_period']:"";
						$UserQualificationReviewHistoryComment->question=isset($value['question'])?$value['question']:"";
						$UserQualificationReviewHistoryComment->answer=$answer;
						$UserQualificationReviewHistoryComment->comment=isset($value['comment'])?$value['comment']:"";
						$UserQualificationReviewHistoryComment->valid_until=isset($value['valid_until'])?date('Y-m-d',strtotime($value['valid_until'])):"";
						$UserQualificationReviewHistoryComment->file=isset($value['file'])?$value['file']:"";
						if($UserQualificationReviewHistoryComment->validate() && $UserQualificationReviewHistoryComment->save())
        				{
							
							$standard_idsArr=explode(',', $value['standard_ids']);
							$role_idsArr=explode(',', $value['role_ids']);
							$business_sector_group_idsArr = explode(',', $value['business_sector_group_ids']);
							foreach ($standard_idsArr as $stdid)
							{
								foreach ($role_idsArr as $roleid)
								{
									foreach($business_sector_group_idsArr as $sectorgp){

										$businesssectorgroup = BusinessSectorGroup::find()->where(['id'=>$sectorgp,'standard_id'=>$stdid])->one();
										if($businesssectorgroup !== null){

										 
											$qualification_status = 0;
											$qreviewmodel = UserQualificationReview::find()->where(['user_id' => $data['user_id']
											,'user_role_id'=>$roleid,'standard_id'=>$stdid,'business_sector_group_id' => $sectorgp])->one();
											if($qreviewmodel !== null){
												$qualification_status = $qreviewmodel->qualification_status;
											}
											$qreviewmodel = UserQualificationReviewHistoryRelRoleStandard::find()->where(['qualification_review_history_id' => $UserQualificationReviewHistory->id
											,'user_role_id'=>$roleid,'standard_id'=>$stdid,'business_sector_group_id' => $sectorgp])->one();
											if($qreviewmodel===null){
												$userreviewhistoryrelrolestd=new UserQualificationReviewHistoryRelRoleStandard();
												$userreviewhistoryrelrolestd->qualification_review_history_id=$UserQualificationReviewHistory->id;
												$userreviewhistoryrelrolestd->user_role_id=$roleid;
												$userreviewhistoryrelrolestd->standard_id=$stdid;
												$userreviewhistoryrelrolestd->business_sector_group_id=$sectorgp;
												$userreviewhistoryrelrolestd->qualification_status = $qualification_status;
												$userreviewhistoryrelrolestd->save();
												$review_history_rel_role_standard_id = $userreviewhistoryrelrolestd->id;
											}else{
												$review_history_rel_role_standard_id = $qreviewmodel->id;
											}
											
											$relRoleStdComment = new UserQualificationReviewHistoryRelRoleStandardComment;
											$relRoleStdComment->review_history_rel_role_standard_id = $review_history_rel_role_standard_id;
											$relRoleStdComment->review_history_comment_id = $UserQualificationReviewHistoryComment->id;
											$relRoleStdComment->save();



										}
									}
								}
							}
						}
					}
				}
				$responsedata=array('status'=>1,'message'=>'successfully');
			}
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
				/*
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
				*/

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
		
		//print_r($data); die;
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
			/*
			$command = $connection->createCommand("SELECT std.name as standard_name,quastd.standard_id as standard_id,quaqtn.* FROM `tbl_users` AS usr INNER JOIN `tbl_user_standard` AS usrstd ON usr.id=usrstd.user_id AND usr.id=".$data['user_id']."
			INNER JOIN `tbl_user_role` AS usrrole ON usr.id=usrrole.user_id
			INNER JOIN `tbl_qualification_question_standard` AS quastd ON usrstd.standard_id=quastd.standard_id
			INNER JOIN `tbl_qualification_question_role` AS quarole ON usrrole.role_id=quarole.role_id
			INNER JOIN `tbl_qualification_question` AS quaqtn ON quaqtn.id=quastd.qualification_question_id AND quaqtn.id=quaprs.qualification_question_id AND quaqtn.id=quarole.qualification_question_id
			INNER JOIN `tbl_standard` AS std ON std.id=quastd.standard_id			
			GROUP BY quaqtn.id,quastd.standard_id");
			*/
			$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();

			 $dateexpiry = date('Y-m-d', strtotime('+7 days',time()));
			 $currentdateexpiry = date('Y-m-d', time());
		
			$command = $connection->createCommand("select qualification_question_id  from `tbl_user_qualification_review_comment` where user_id =".$data['user_id']."
			and ((review_status=1 and (( recurring_period!=6 and valid_until >= '".$dateexpiry."') OR recurring_period=6 )) or  review_status=0)");
			$answeredresult = $command->queryAll();
			
			$questionCond = '';
			if(count($answeredresult)>0){
				$questionArr = [];
				foreach($answeredresult as $question){
					$questionArr[] = $question['qualification_question_id'];
				}
				$questionCond = ' AND quaqtn.id not in ('.implode(',',$questionArr).') ';
			}


			$commandapp = $connection->createCommand("select qualification_question_id,answer,comment,file  from `tbl_user_qualification_review_comment` where user_id =".$data['user_id']."
			and ((review_status=1 and (( recurring_period!=6 and valid_until <= '".$dateexpiry."' and valid_until >='".$currentdateexpiry."') OR recurring_period=6 )) or  review_status=0)");
			$expiryingresult = $commandapp->queryAll();
			
			$questionAnswerArr = [];
			if(count($expiryingresult)>0){
				
				foreach($expiryingresult as $question){
					//print_r($question);
					//echo $question['answer']; 
					//die;
					$questionAnswerArr[$question['qualification_question_id']]['answer'] = $question['answer'];
					$questionAnswerArr[$question['qualification_question_id']]['comment'] = $question['comment'];
					$questionAnswerArr[$question['qualification_question_id']]['file'] = $question['file'];
				}
				//$questionCond = ' AND quaqtn.id not in ('.implode(',',$questionArr).') ';
			}

			//echo $questionCond; die;
			//and usrsectorgroup.business_sector_group_id = usrsector.id 
			//INNER JOIN `tbl_qualification_question_business_sector` AS sector ON usrsector.business_sector_id =sector.business_sector_id
			//AND quaqtn.id=sector.qualification_question_id 
			// and usrsector.business_sector_id in (".implode(',',$data['business_sectors']).") 
			// GROUP_CONCAT(distinct usrsector.business_sector_id) as business_sector_ids,

			$command = $connection->createCommand("SELECT
			
			 GROUP_CONCAT(distinct usrsectorgroup.business_sector_group_id) as business_sector_group_ids,

			 GROUP_CONCAT(distinct usrrole.role_id) as role_ids, GROUP_CONCAT(distinct usrstd.standard_id) as standard_id,quaqtn.* 
			FROM `tbl_users` AS usr
			 INNER JOIN `tbl_user_standard` AS usrstd ON usr.id=usrstd.user_id AND usr.id=".$data['user_id']."
			INNER JOIN `tbl_user_role` AS usrrole ON usr.id=usrrole.user_id
			INNER JOIN `tbl_user_business_group` AS usrsector ON usr.id=usrsector.user_id
			INNER JOIN `tbl_user_business_group_code` AS usrsectorgroup ON usrsector.id=usrsectorgroup.business_group_id 

			INNER JOIN `tbl_qualification_question_standard` AS quastd ON usrstd.standard_id=quastd.standard_id
			INNER JOIN `tbl_qualification_question_role` AS quarole ON usrrole.role_id=quarole.role_id
			
			
			INNER JOIN `tbl_qualification_question_business_sector_group` AS sectorgroup ON usrsectorgroup.business_sector_group_id=sectorgroup.business_sector_group_id

			INNER JOIN `tbl_qualification_question` AS quaqtn ON quaqtn.id=quastd.qualification_question_id
			  AND quaqtn.id=quarole.qualification_question_id 
			  AND quaqtn.id=sectorgroup.qualification_question_id 

			 where usrstd.standard_id in (".implode(',',$data['standard_ids']).") and usrrole.role_id in (".implode(',',$data['role_ids']).") 
			 and usrsector.business_sector_id in (".implode(',',$data['business_sectors']).") 
			 and usrsectorgroup.business_sector_group_id in (".implode(',',$data['business_sector_groups']).") 
			  ".$questionCond." 
			GROUP BY quaqtn.id");
			
			
			$result = $command->queryAll();
			
			if(count($result)>0)
			{
				
				foreach($result as $res)
				{
					$questions_arr = array();
					

					$questions_arr["role_ids"]=$res["role_ids"];
					$questions_arr["standard_ids"]=$res["standard_id"];
					$questions_arr["business_sector_group_ids"]=$res["business_sector_group_ids"];
					$questions_arr["id"]=$res["id"];
					$questions_arr["name"]=$res["name"];
					$questions_arr["code"]=$res["code"];
					$questions_arr["guidance"]=$res["guidance"];
					$questions_arr["file_upload_required"]=$res["file_upload_required"];
					$questions_arr["recurring_period"]=$res["recurring_period"];
					$resultarr[]=$questions_arr;
					
				}
				
			}
			
			$dataarr['answerArr'] = $answers;
			$dataarr['questionArr'] = $resultarr;
			$dataarr['recurringPeriod'] = $recurring_period;
			$dataarr['questionAnswerArr'] = $questionAnswerArr;
			
			return ['data'=>$dataarr];			
		}
	}
	




	public function actionQualificationView()
    {
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
				
		$data = Yii::$app->request->post();
		$qmodel = new UserQualificationReview;
		$answers = $qmodel->answers;
		$arrAddPeriods = $qmodel->arrAddPeriods;
		$recurring_period = $qmodel->recurring_period;
		
		$connection = Yii::$app->getDb();
		
		if($data)
		{			
			$dataarr = [];
			
			$userModel = User::find()->where(['id'=>$data['id']])->one();
			$arrUserDetails=array();
			$arrUserDetails['id']=$userModel->id;
			$arrUserDetails['first_name']=$userModel->first_name;
			$arrUserDetails['last_name']=$userModel->last_name;
			$arrUserDetails['email']=$userModel->email;
			$arrUserDetails['telephone']=$userModel->telephone;
			$arrUserDetails['country_name']=$userModel->country ? $userModel->country->name : 'NA';
			$arrUserDetails['state_name']=$userModel->state->name;
			$dataarr['userdetails']=$arrUserDetails;
						
			$arrStds=array();	
			

			$standard_ids = $data['standard_ids'];
			$role_ids = $data['role_ids'];
			$business_sector_groups = $data['business_sector_groups'];
			$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();
			$command = $connection->createCommand("SELECT rcomment.id as commentid,
			group_concat(distinct qreview.business_sector_group_id) as business_sector_group_ids
			,group_concat(distinct qreview.standard_id) as standard_ids,
			group_concat(distinct qreview.user_role_id)  as role_ids,question.* ,rcomment.answer ,rcomment.comment ,rcomment.file,
			rcomment.reviewed_by ,rcomment.reviewed_date,rcomment.valid_until
			FROM  `tbl_user_qualification_review` qreview 
			INNER JOIN `tbl_user_qualification_review_rel_comment` relcmt on relcmt.user_qualification_review_id =qreview.id
			INNER JOIN `tbl_user_qualification_review_comment` rcomment on relcmt.user_qualification_review_comment_id=rcomment.id
			INNER JOIN `tbl_qualification_question` question on question.id=rcomment.qualification_question_id 	
			WHERE rcomment.user_id=".$data['id']." and rcomment.answer=1 and review_status=0  and 
			qreview.standard_id in (".implode(',',$standard_ids).") and qreview.user_role_id in (".implode(',',$role_ids).") 
			and qreview.business_sector_group_id in (".implode(',',$business_sector_groups).") 
			group by rcomment.id");
			/*echo "SELECT rcomment.id as commentid,group_concat(distinct qreview.standard_id) as standard_ids,
			group_concat(distinct qreview.user_role_id)  as role_ids,question.* ,rcomment.answer ,rcomment.comment ,rcomment.file,
			rcomment.reviewed_by ,rcomment.reviewed_date,rcomment.valid_until
			FROM  `tbl_user_qualification_review` qreview 
			INNER JOIN `tbl_user_qualification_review_rel_comment` relcmt on relcmt.user_qualification_review_id =qreview.id
			INNER JOIN `tbl_user_qualification_review_comment` rcomment on relcmt.user_qualification_review_comment_id=rcomment.id
			INNER JOIN `tbl_qualification_question` question on question.id=rcomment.qualification_question_id 	
			WHERE rcomment.user_id=".$data['id']." and rcomment.answer=1 and review_status=0  
			qreview.standard_id in (".implode(',',$standard_ids).") and qreview.user_role_id in (".implode(',',$role_ids).") 
			group by rcomment.id"; die; */
			$result = $command->queryAll();
			$resultarr= [];
			if(count($result)>0)				
			{
				$i=0;
				
				foreach($result as $res)
				{

					$questions_arr = array();


					if($res["recurring_period"] && $res["valid_until"] && strtotime($res["valid_until"]) > time() && $res["recurring_period"]!=6){
						//echo $res["valid_until"];
						//echo $arrAddPeriods[$res["recurring_period"]]; die;
						
						$questions_arr["new_valid_until"]=strtotime("+".$arrAddPeriods[$res["recurring_period"]] , strtotime($res["valid_until"]));
						
					}else{
						if($res["recurring_period"] != 6)
						$questions_arr["new_valid_until"]=strtotime("+".$arrAddPeriods[$res["recurring_period"]],time());
						//echo time();
						//echo date('m/d/Y',$questions_arr["new_valid_until"]); die;
					}
					//echo $questions_arr["new_valid_until"]; die;
					
					$questions_arr["new_valid_until"] = isset($questions_arr["new_valid_until"])?date('m/d/Y',$questions_arr["new_valid_until"]):'';
					$questions_arr["role_ids"]=$res["role_ids"];
					$questions_arr["standard_ids"]=$res["standard_ids"];
					$questions_arr["business_sector_group_ids"]=$res["business_sector_group_ids"];
					$questions_arr["id"]=$res["id"];
					$questions_arr["reviewcommentid"]=$res["commentid"];
					
					$questions_arr["name"]=$res["name"];
					$questions_arr["code"]=$res["code"];
					$questions_arr["guidance"]=$res["guidance"];
					$questions_arr["file_upload_required"]=$res["file_upload_required"];
					$questions_arr["recurring_period"]=$res["recurring_period"];
					$questions_arr["valid_until"]= $res["valid_until"];
					$questions_arr["currentdate"]=date('Y-m-d');

					$questions_arr["answer"]=$res["answer"];
					$questions_arr["comment"]=$res["comment"];
					$questions_arr["file"]=$res["file"];

					$resultarr[]=$questions_arr;
				}
			}
			$dataarr['questions']=$resultarr;
			$dataarr['recurringPeriod'] = $recurring_period;
			$dataarr['answerArr'] = $answers;




			
			
			return ['data'=>$dataarr];
		}
	}
	
	public function actionReviewHistory(){
		// For History 
			// `tbl_user_qualification_review_history` rhistory 
		$data = Yii::$app->request->post();
		$qmodel = new UserQualificationReview;
		$answers = $qmodel->answers;
		$arrAddPeriods = $qmodel->arrAddPeriods;
		$recurring_period = $qmodel->recurring_period;
		
		$connection = Yii::$app->getDb();
		
		$errordata = ['error'=>1,'message'=>'Error'];
		$historydata=[];
		if($data)
		{			
			$dataarr = [];
			
			$userModel = User::find()->where(['id'=>$data['id']])->one();


			$reviewhistorymodel = UserQualificationReviewHistory::find()->where(['user_id' => $data['id']])->orderBy(['id'=>SORT_DESC])->all();

			if(count($reviewhistorymodel)>0){
				
				$i=0;
				$historydata=[];
				foreach($reviewhistorymodel as $history)
				{
					$command = $connection->createCommand("SELECT group_concat(distinct rrolestd.standard_id) as standard_ids,
					group_concat(distinct rrolestd.user_role_id )  as role_ids, rcomment.qualification_question_id ,  
					rcomment.recurring_period ,rcomment.question ,rcomment.answer,rcomment.comment ,rcomment.valid_until,rcomment.file,rcomment.id as history_comment_id  
					FROM `tbl_user_qualification_review_history` rhistory 
					INNER JOIN  `tbl_user_qualification_review_history_comment` rcomment on rcomment.review_history_id = rhistory.id 
					INNER JOIN `tbl_user_qualification_review_history_rel_role_standard` as rrolestd on 
					rrolestd.qualification_review_history_id = rhistory.id 
					INNER JOIN `tbl_user_qualification_review_history_rel_role_standard_comment` as relstdcomment on relstdcomment.review_history_rel_role_standard_id = rrolestd.id 
					and relstdcomment.review_history_comment_id = rcomment.id 
					WHERE rcomment.review_history_id=".$history->id." and rhistory.id=".$history->id." 
					group by rcomment.id");
					
					$result = $command->queryAll();
					$resultarr= [];
					if(count($result)>0)				
					{
						
						$historydatarow=[];
						$historydatarow['created_date'] = date('m/d/Y H:i:s',$history->created_at);
						$historydatarow['created_by'] = $history->createdby->first_name.' '.$history->createdby->last_name;
						foreach($result as $res)
						{

							$ansdata =[];
							$ansdata['standard_ids'] =$res['standard_ids'];
							$ansdata['role_ids'] =$res['role_ids'];
							$ansdata['question_id'] =$res['qualification_question_id'];
							$ansdata['history_comment_id'] =$res['history_comment_id'];
							$ansdata['recurring_period'] =$res['recurring_period'];
							$ansdata['question'] =$res['question'];
							$ansdata['answer'] =$res['answer'];
							$ansdata['comment'] =$res['comment'];
							$ansdata['valid_until'] =  date('m/d/Y',strtotime($res['valid_until']));
							$ansdata['file'] =$res['file'];
							$ansdata['history_id'] = $history->id;
							
							$historydatarow['questions'][] = $ansdata;
						}						
						$i++;
					}
					
					$actionType = 'review';
					if($history->history_type==2)
					{
						$actionType = 'approval';
					}
					$historydata[$actionType][] = $historydatarow;
				}
			}else{
				$errordata = ['error'=>1,'message'=>'No History Found'];
			}
		}
		return ['data'=>$historydata,'recurringPeriod'=>$recurring_period,'answerArr'=>$answers];
	}
	public function checkValidUser($user_id,$standard_id,$role_id,$business_sector_group_id)
    {
		
		$resultarr=array();
		$connection = Yii::$app->getDb();
		$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();
		$command = $connection->createCommand("SELECT quaqtn.id 
			FROM `tbl_users` AS usr
			INNER JOIN `tbl_user_standard` AS usrstd ON usr.id=usrstd.user_id AND usr.id=".$user_id." 
			INNER JOIN `tbl_user_role` AS usrrole ON usr.id=usrrole.user_id
			INNER JOIN `tbl_user_business_group` AS usrsector ON usr.id=usrsector.user_id and usrsector.standard_id = usrstd.standard_id 
			INNER JOIN `tbl_user_business_group_code` AS usrsectorgp ON usrsector.id=usrsectorgp.business_group_id  
			
			INNER JOIN `tbl_qualification_question_standard` AS quastd ON usrstd.standard_id=quastd.standard_id
			INNER JOIN `tbl_qualification_question_role` AS quarole ON usrrole.role_id=quarole.role_id
			INNER JOIN `tbl_qualification_question_business_sector_group` AS quasectorgp ON usrsectorgp.business_sector_group_id=quasectorgp.business_sector_group_id  

			INNER JOIN `tbl_qualification_question` AS quaqtn ON quaqtn.id=quastd.qualification_question_id
			  AND quaqtn.id=quarole.qualification_question_id 
			AND quaqtn.id=quasectorgp.qualification_question_id 
			where usrstd.standard_id =".$standard_id." and usrrole.role_id =".$role_id." 
			and quasectorgp.business_sector_group_id =".$business_sector_group_id." GROUP BY quaqtn.id");
		
		$result = $command->queryAll();
		//echo $user_id,',',$standard_id,',',$role_id,',',$business_sector_group_id,',',count($result);
		//echo '<br><br>';
		$questionscount=count($result);
		$todaydate=date('Y-m-d');
		if($questionscount>0)
		{	
			$questarr=array();
			foreach($result as $res)
			{
				$questarr[]=$res['id'];			
			}
			
			$query = $connection->createCommand("SELECT count(*) AS questcount FROM tbl_user_qualification_review_comment
			 WHERE user_id='$user_id' AND answer='1' AND qualification_question_id IN(".implode(",",$questarr).") AND 
			 ((recurring_period='6') OR ((recurring_period!='6') AND (valid_until >= '".$todaydate."')))");
			$resultquery = $query->queryAll();
			
			if($questionscount==$resultquery[0]['questcount'])
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		return false;
		
	}

	public function actionChecklistfile(){
		$data = Yii::$app->request->post();
		if($data['type']=='active'){
			$files = UserQualificationReviewComment::find()->where(['id'=>$data['id']])->one();
		}else{
			$files = UserQualificationReviewHistoryComment::find()->where(['id'=>$data['id']])->one();	
		}

		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['user_qualification_review_files'].$files->file;
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


	public function actionUsersector()
	{
		
		$sector = [];

		$data=Yii::$app->request->post();
		//$userData = Yii::$app->userdata->getData();
		//$date_format = Yii::$app->globalfuns->getSettings('date_format');
		//$userid=$userData['userid'];

		if ($data) 
		{

			$std_ids = implode(',',$data['standard_ids']);
			$user_id = $data['user_id'];

			$businesssector = [];
			$userbusinesssector = UserBusinessGroup::find()->where(['user_id'=>$user_id,'standard_id'=>$data['standard_ids'] ])
									->groupBy(['business_sector_id'])->all();
			foreach($userbusinesssector as $bsector){
				$sector[] = ['id'=>$bsector->business_sector_id,'name'=>$bsector->businesssector->name];
			}
			/*$businesssectorlist = implode(',',$businesssector);
			$connection = Yii::$app->getDb();
			$command = $connection->createCommand("SELECT bs.id,bs.name FROM tbl_business_sector AS bs 
			INNER JOIN tbl_business_sector_group AS bsg ON bs.id=bsg.business_sector_id AND bsg.standard_id IN (".$std_ids.") 
			and bs.id in (".$businesssectorlist.")
			GROUP BY bs.id");
			$result = $command->queryAll();
			
			if(count($result)>0){
				foreach($result as $data){
					$sector[] = ['id'=>$data['id'],'name'=>$data['name']];
				}
			}
			*/
			/*
			$std_ids = implode(',',$data['standard_ids']);
			$user_id = $data['user_id'];

			$businesssector = [];
			$userbusinesssector = UserBusinessSector::find()->where(['user_id'=>$user_id])->all();
			foreach($userbusinesssector as $bsector){
				$businesssector[] = $bsector->business_sector_id;
			}
			$businesssectorlist = implode(',',$businesssector);
			$connection = Yii::$app->getDb();
			//AND bsgp.process_id IN (".$process_ids.") 
			//INNER JOIN tbl_business_sector_group_process AS bsgp ON bsg.id=bsgp.business_sector_group_id 
			$command = $connection->createCommand("SELECT bs.id,bs.name FROM tbl_business_sector AS bs 
			INNER JOIN tbl_business_sector_group AS bsg ON bs.id=bsg.business_sector_id AND bsg.standard_id IN (".$std_ids.") 
			and bs.id in (".$businesssectorlist.")
			GROUP BY bs.id");
			$result = $command->queryAll();
			
			if(count($result)>0){
				foreach($result as $data){
					$sector[] = ['id'=>$data['id'],'name'=>$data['name']];
				}
			}
			*/
		}
		return $sector;
	}

			
	public function actionUsersectorgroup()
	{
		$sector = [];

		$data=Yii::$app->request->post();
		//$userData = Yii::$app->userdata->getData();
		//$date_format = Yii::$app->globalfuns->getSettings('date_format');
		//$userid=$userData['userid'];
		
		if ($data) 
		{

			$std_ids = implode(',',$data['standard_ids']);
			$user_id = $data['user_id'];
			$businesssector = $data['business_sectors'];

			//$businesssector = [];
			$userbusinesssector = UserBusinessGroup::find()->where(['business_sector_id'=>$businesssector,'user_id'=>$user_id,'standard_id'=>$std_ids ])->all();
			//echo count($userbusinesssector);
			foreach($userbusinesssector as $bsector){
				//echo count($bsector->groupcode); die;
				foreach($bsector->groupcode as $gpcode){
					$sector[] = ['id'=>$gpcode->sectorgroup->id,'name'=>$gpcode->sectorgroup->group_code ];
				}
			}
			/*

			$userbusinesssectorgroup = UserBusinessGroupCode::find()->where(['user_id'=>$user_id])->all();
			foreach($userbusinesssectorgroup as $bsectorgrp){
				$businesssectorgroup[] = $bsectorgrp->business_sector_group_id;
			}
			$businesssectorgrouplist = implode(',',$businesssectorgroup);
			
			$businesssectorlist = implode(',',$businesssector);
			$connection = Yii::$app->getDb();
			//AND bsgp.process_id IN (".$process_ids.") 
			//INNER JOIN tbl_business_sector_group_process AS bsgp ON bsg.id=bsgp.business_sector_group_id 
			$command = $connection->createCommand("SELECT bsg.id,bsg.group_code FROM tbl_business_sector AS bs 
			INNER JOIN tbl_business_sector_group AS bsg ON bs.id=bsg.business_sector_id where bsg.standard_id IN (".$std_ids.") 
			and bs.id in (".$businesssectorlist.") and bsg.id in (".$businesssectorgrouplist.") 
			GROUP BY bsg.id");
			$result = $command->queryAll();
			
			if(count($result)>0){
				foreach($result as $data){
					$sector[] = ['id'=>$data['id'],'name'=>$data['group_code']];
				}
			}
			*/


			/*
			$std_ids = implode(',',$data['standard_ids']);
			$user_id = $data['user_id'];
			$businesssector = $data['business_sectors'];


			$userbusinesssectorgroup = UserBusinessSectorGroup::find()->where(['user_id'=>$user_id])->all();
			foreach($userbusinesssectorgroup as $bsectorgrp){
				$businesssectorgroup[] = $bsectorgrp->business_sector_group_id;
			}
			$businesssectorgrouplist = implode(',',$businesssectorgroup);
			
			$businesssectorlist = implode(',',$businesssector);
			$connection = Yii::$app->getDb();
			//AND bsgp.process_id IN (".$process_ids.") 
			//INNER JOIN tbl_business_sector_group_process AS bsgp ON bsg.id=bsgp.business_sector_group_id 
			$command = $connection->createCommand("SELECT bsg.id,bsg.group_code FROM tbl_business_sector AS bs 
			INNER JOIN tbl_business_sector_group AS bsg ON bs.id=bsg.business_sector_id where bsg.standard_id IN (".$std_ids.") 
			and bs.id in (".$businesssectorlist.") and bsg.id in (".$businesssectorgrouplist.") 
			GROUP BY bsg.id");
			$result = $command->queryAll();
			
			if(count($result)>0){
				foreach($result as $data){
					$sector[] = ['id'=>$data['id'],'name'=>$data['group_code']];
				}
			}
			*/
		}
		return $sector;	
	}
}
