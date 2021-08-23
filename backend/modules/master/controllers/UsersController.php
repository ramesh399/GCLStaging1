<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\UserQualificationReviewComment;
use app\modules\master\models\UserQualificationReviewHistory;
use app\modules\master\models\UserQualificationReviewHistoryComment;
use app\modules\master\models\UserQualificationReviewHistoryRelRoleStandard;

class UsersController extends \yii\rest\Controller
{

    /**
     * @inheritdoc
     */
	 /*
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => \sizeg\jwt\JwtHttpBearerAuth::class,
        ];

        return $behaviors;
    }
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
			
			
		];	
	}
	public function actionCheckValidUser()
    {
			// $user_id=104;
			// $standard_id=1;
			// $role_id=1;
			$resultarr=array();
			$connection = Yii::$app->getDb();
			$command = $connection->createCommand("SELECT quaqtn.id 
				FROM `tbl_users` AS usr
				INNER JOIN `tbl_user_standard` AS usrstd ON usr.id=usrstd.user_id AND usr.id='$user_id'
				INNER JOIN `tbl_user_process` AS usrprs ON usr.id=usrprs.user_id
				INNER JOIN `tbl_user_role` AS usrrole ON usr.id=usrrole.user_id
				INNER JOIN `tbl_qualification_question_standard` AS quastd ON usrstd.standard_id=quastd.standard_id
				INNER JOIN `tbl_qualification_question_process` AS quaprs ON usrprs.process_id=quaprs.process_id
				INNER JOIN `tbl_qualification_question_role` AS quarole ON usrrole.role_id=quarole.role_id
				INNER JOIN `tbl_qualification_question` AS quaqtn ON quaqtn.id=quastd.qualification_question_id
				AND quaqtn.id=quaprs.qualification_question_id AND quaqtn.id=quarole.qualification_question_id
				where usrstd.standard_id ='$standard_id' and usrrole.role_id ='$role_id' GROUP BY quaqtn.id");
			
			$result = $command->queryAll();
			$questionscount=count($result);
			$todaydate=date('Y-m-d');
			if($questionscount>0)
			{	
				$questarr=array();
				foreach($result as $res)
				{
					$questarr[]=$res['id'];			
				}
				
				$query = $connection->createCommand("SELECT count(*) AS questcount FROM tbl_user_qualification_review_comment WHERE user_id='$user_id' AND answer='1' AND qualification_question_id IN(".implode(",",$questarr).") AND ((recurring_period='6') OR ((recurring_period!='6') AND (valid_until >= '".$todaydate."')))");
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
		
	}
	public function actionCreate()
    {
		
        $responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        $data = Yii::$app->request->post();
		if ($data) 
		{	
			$UserQualificationReviewHistory= new UserQualificationReviewHistory();
			$UserQualificationReviewHistory->user_id=$data['user_id'];
			$UserQualificationReviewHistory->created_at=strtotime(date('M d,Y h:i A'));
			if($UserQualificationReviewHistory->validate() && $UserQualificationReviewHistory->save())
        	{ 
				if(is_array($data['qdata']) && count($data['qdata'])>0)
				{
					foreach ($data['qdata'] as $value)
					{ 
						$UserQualificationReviewHistoryComment=new UserQualificationReviewHistoryComment();
						$UserQualificationReviewHistoryComment->review_history_id=$UserQualificationReviewHistory->id;
						$UserQualificationReviewHistoryComment->qualification_question_id=isset($value['question_id'])?$value['question_id']:"";
						$UserQualificationReviewHistoryComment->recurring_period=isset($value['recurring_period'])?$value['recurring_period']:"";
						$UserQualificationReviewHistoryComment->question=isset($value['question'])?$value['question']:"";
						$UserQualificationReviewHistoryComment->answer=isset($value['answer'])?$value['answer']:"";
						$UserQualificationReviewHistoryComment->comment=isset($value['comment'])?$value['comment']:"";
						$UserQualificationReviewHistoryComment->valid_until=isset($value['valid_until'])?date('Y-m-d',strtotime($value['valid_until'])):"";
						$UserQualificationReviewHistoryComment->file=isset($value['file'])?$value['file']:"";
						if($UserQualificationReviewHistoryComment->validate() && $UserQualificationReviewHistoryComment->save())
        				{
							foreach ($value['standard_ids'] as $stdid)
							{
								foreach ($value['role_ids'] as $roleid)
								{
									$userreviewhistoryrelrolestd=new UserQualificationReviewHistoryRelRoleStandard();
									$userreviewhistoryrelrolestd->qualification_review_history_id=$UserQualificationReviewHistoryComment->id;
									$userreviewhistoryrelrolestd->user_role_id=$roleid;
									$userreviewhistoryrelrolestd->standard_id=$stdid;
									$userreviewhistoryrelrolestd->save();
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
	

}