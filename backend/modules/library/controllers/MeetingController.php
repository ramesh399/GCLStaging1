<?php
namespace app\modules\library\controllers;

use Yii;
use app\modules\library\models\LibraryMeeting;
use app\modules\library\models\LibraryMeetingMinutes;
use app\modules\library\models\LibraryMeetingMinutesLog;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * MeetingController implements the CRUD actions for Product model.
 */
class MeetingController extends \yii\rest\Controller
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

		$meetingmodel = new LibraryMeeting();

		$model = LibraryMeeting::find()->where(['<>','status',2])->alias('t');
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			$typearray=array_map('strtolower', $meetingmodel->arrType);

			if(isset($post['from_date']))
			{
				$from_date = date("Y-m-d",strtotime($post['from_date']));
				$model = $model->andWhere(['>=','t.meeting_date',$from_date]);			
			}
			if(isset($post['to_date']))
			{
				$to_date = date("Y-m-d",strtotime($post['to_date']));
				$model = $model->andWhere(['<=','t.meeting_date', $to_date]);			
			}

			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 'location', $searchTerm],
					['like', 'date_format(`meeting_date`, \'%b %d, %Y\' )', $searchTerm],
					['like', 'date_format(FROM_UNIXTIME(`created_at` ), \'%b %d, %Y\' )', $searchTerm],
				]);
				$search_type = array_search(strtolower($searchTerm),$typearray);

				if($search_type!==false)
				{
					$model = $model->orFilterWhere([
                        'or', 					
						['type'=>$search_type]								
					]);
				}

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
		
		$meeting_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $meeting)
			{
				$data=array();
				$data['id']=$meeting->id;
				$data['location']=$meeting->location;
				$data['type']=$meeting->type;
				$data['type_label']=$meetingmodel->arrType[$meeting->type];
				$data['attendees']=$meeting->attendees;
				$data['apologies']=$meeting->apologies;
				$data['meeting_date']=date($date_format,strtotime($meeting->meeting_date));
				$data['created_at']=date($date_format,$meeting->created_at);
				$data['status'] = $meeting->status;

				$librarymeetingminutes = $meeting->librarymeetingminutes;
				if(count($librarymeetingminutes)>0)
				{
					$minutes_arr = array();
					foreach($librarymeetingminutes as $val)
					{
						$minutes = array();
						$minutes['raised_id'] = $val['raised_id'];
						$minutes['class'] = $val['class'];
						$minutes['minute_date'] = date($date_format,strtotime($val['minute_date']));
						$minutes['details'] = $val['details'];
						$minutes['status'] = $val['status'];
						$minutes_arr[] = $minutes;
					}
					$data['minutes_data']=$minutes_arr;
				}

				$meeting_list[]=$data;
			}
		}

		return ['meetings'=>$meeting_list, 'total'=>$totalCount];
    }

    public function actionCreate()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$userData = Yii::$app->userdata->getData();
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			//raisedlist
			if(isset($data['id']))
			{
				$model = LibraryMeeting::find()->where(['id' => $data['id']])->one();
				$model->updated_by = $userData['userid'];
			}
			else
			{
				$model = new LibraryMeeting();
				$model->created_by = $userData['userid'];
				
			}
			$model->type = $data['type'];	
			$model->meeting_date = date('Y-m-d',strtotime($data['meeting_date']));	
			$model->attendees = $data['attendees'];	
			$model->apologies = $data['apologies'];	
			$model->location = $data['location'];	
			
			
			
			if($model->validate() && $model->save())
			{
				//raisedlist
				$arrRaised=array();
				$meetingModel =LibraryMeeting::find()->all();
				if(count($meetingModel)>0)
				{
					foreach($meetingModel as $meeting)
					{
						$arrRaised[$meeting->id]= date($date_format,strtotime($meeting->meeting_date)).' '.$model->arrType[$meeting->type];
					}			
				}	
				$responsedata=array('raisedlist'=>$arrRaised, 'status'=>1,'message'=>'Meeting has been updated successfully');	
			}
			/*
				$model->type = $data['type'];	
				$model->meeting_date = date('Y-m-d',strtotime($data['meeting_date']));	
				$model->attendees = $data['attendees'];	
				$model->apologies = $data['apologies'];	
				$model->location = $data['location'];	
				$userData = Yii::$app->userdata->getData();
				
				
				if($model->validate() && $model->save())
				{	
					$responsedata=array('status'=>1,'message'=>'Meeting has been created successfully');	
				}
				*/
		}
		return $this->asJson($responsedata);
	}

	public function actionGeneratePdf()
    {
		//$data['id'] = 6;
		$meetingmodel = new LibraryMeeting();
		$minutemodel = new LibraryMeetingMinutes();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		if ($data) 
		{
			$meeting = LibraryMeeting::find()->where(['id'=>$data['id']])->one();
			
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			header('Access-Control-Max-Age: 1000');
			header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

			$html='';
			$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8',
				'margin_left' => 20,
				'margin_right' => 20,
				'margin_top' => 25,
				'margin_bottom' => 10,
				'margin_header' => 10,
				'margin_footer' => 3
			]);

			$headerContent = '
			<div style="text-align:center;font-weight:bold;font-size:14px;padding-top:5px;" valign="middle"><u>
				'.$meetingmodel->arrType[$meeting->type]. ' MEETING MINUTES</u>				
			</div>';
								
			$html='
			<style>
			table {
			border-collapse: collapse;
			}

			@page :first {    
				header: html_firstpageheader;
			}

			table.reportDetailLayout {
				border-collapse: collapse;
				width:100%;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				margin-bottom:5px;
				margin-top:5px;
			}

			table.productDetails {
				border-collapse: collapse;
				border: 1px solid #4e85c8;
				width:100%;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				margin-bottom:5px;
				margin-top:5px;
			}

			td.productDetails {
				text-align: center;
				border: 1px solid #4e85c8;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				background-color:#DFE8F6;
				padding:3px;
			}

			td.reportDetailLayout {
				text-align: center;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				background-color:#DFE8F6;
				padding:3px;
			}
			td.reportDetailLayoutHead {
				text-align: center;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				/*background-color:#4e85c8;*/
				background-color:#006fc0;
				padding:3px;
				color:#FFFFFF;
			}

			td.reportDetailLayoutInner {
				text-align: center;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				background-color:#ffffff;
				padding:3px;
			}
			</style>
			
			<htmlpageheader name="firstpageheader" style="display:none">
				'.$headerContent.'	
			</htmlpageheader>
			
			<div>';

			/*
			$html.='<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">	
			<tr>
				<td style="text-align:left;" class="reportDetailLayoutInner"><b>DATE: </b>'.date($date_format,strtotime($meeting->meeting_date)).'</td>	 
			<tr>
				<td style="text-align:left;" class="reportDetailLayoutInner"><b>LOCATION: </b>'.$meeting->location.'</td>	 
			</tr>
			<tr>
				<td style="text-align:left;padding-top:20px;font-weight:bold;" class="reportDetailLayoutInner">ATTENDEES:</td>	 
			</tr>
			<tr>
				<td style="text-align:left;padding-top:10px;" class="reportDetailLayoutInner">'.nl2br($meeting->attendees).'</td>
			</tr>
			<tr>
				<td style="text-align:left;padding-top:20px;font-weight:bold;" class="reportDetailLayoutInner">APOLOGIES:</td>	 
			</tr>
			<tr>
				<td style="text-align:left;padding-top:10px;" class="reportDetailLayoutInner">'.nl2br($meeting->apologies).'</td>
			</tr>';
			$html.='</table>';
			*/

			$html.='<div cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">	
			<div style="text-align:left;" class="reportDetailLayoutInner"><b>DATE: </b>'.date($date_format,strtotime($meeting->meeting_date)).'</div>	 
			<div style="text-align:left;padding-top:20px;" class="reportDetailLayoutInner"><b>LOCATION: </b>'.$meeting->location.'</div>	 
			<div style="text-align:left;padding-top:20px;font-weight:bold;" class="reportDetailLayoutInner">ATTENDEES:</div>	 
			<div style="text-align:left;padding-top:10px;" class="reportDetailLayoutInner">'.nl2br($meeting->attendees).'</div>
			<div style="text-align:left;padding-top:20px;font-weight:bold;" class="reportDetailLayoutInner">APOLOGIES:</div>	 
			<div style="text-align:left;padding-top:10px;" class="reportDetailLayoutInner">'.nl2br($meeting->apologies).'</div>
			 ';
			$html.='</div>';
			$librarymeetingminutes = $meeting->librarymeetingminutes;
			if(count($librarymeetingminutes)>0)
			{
				$minutes_arr = array();
				foreach($librarymeetingminutes as $val)
				{
					/*$html.='
					<tr>
						<td style="text-align:left;padding-top:20px;font-weight:bold;" class="reportDetailLayoutInner">'.$minutemodel->arrClass[$val['class']].':</td>	 
					</tr>
					<tr>
						<td style="text-align:left;padding-top:10px;" class="reportDetailLayoutInner">'.nl2br($val['details']).'</td>
					</tr>';
					*/
					$html.='
					<div style="text-align:left;padding-top:20px;font-weight:bold;" class="reportDetailLayoutInner">'.$minutemodel->arrClass[$val['class']].':</div>	 
					<div style="text-align:left;padding-top:10px;" class="reportDetailLayoutInner">'.nl2br($val['details']).'
					</div>';
				}
			}

			//$html.='</table></div>';
			$html.='</div>';
			$mpdf->WriteHTML($html);
			$mpdf->Output('MRM.pdf','D');	
		}
	}

	public function actionMeetingstatuslist()
	{
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		$modelObj = new LibraryMeeting();
		$minutemodel = new LibraryMeetingMinutes();
		
		$arrRaised=array();
		$meetingModel =LibraryMeeting::find()->all();
		if(count($meetingModel)>0)
		{
			foreach($meetingModel as $meeting)
			{
				$arrRaised[$meeting->id]= date($date_format,strtotime($meeting->meeting_date)).' '.$modelObj->arrType[$meeting->type];
			}			
		}		
		
		return ['typelist'=>$modelObj->arrType, 'raisedlist'=>$arrRaised, 'classlist'=>$minutemodel->arrClass, 'statuslist'=>$minutemodel->arrStatus];
	}

	public function actionCreateMinute()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			if(isset($data['id']))
			{
				$model = LibraryMeetingMinutes::find()->where(['id' => $data['id']])->one();
				$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
				$data = Yii::$app->request->post();
				if ($data) 
				{	
					//$model->meeting_id = $data['meeting_id'];
					$model->raised_id = $data['raised_id'];
					$model->meeting_id = $model->raised_id;
					$model->class = $data['class'];	
					$model->minute_date = date('Y-m-d',strtotime($data['minute_date']));		
					$model->details = $data['details'];	
					$model->status = $data['status'];	
					$userData = Yii::$app->userdata->getData();
					$model->updated_by = $userData['userid'];
					if($model->validate() && $model->save())
					{	
						$responsedata=array('status'=>1,'message'=>'Minute has been updated successfully');
					}
				}
			}
			else
			{
				$model = new LibraryMeetingMinutes();
				$model->meeting_id = $data['meeting_id'];
				$model->raised_id = $data['raised_id'];
				$model->class = $data['class'];	
				$model->minute_date = date('Y-m-d',strtotime($data['minute_date']));		
				$model->details = $data['details'];	
				$model->status = $data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->created_by = $userData['userid'];
				if($model->validate() && $model->save())
				{	
					$responsedata=array('status'=>1,'message'=>'Minute has been created successfully');
				}
			}
			
		}
		return $this->asJson($responsedata);
	}

	public function actionDeleteMinute(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

		if($data){

			
			if(isset($data['id']))
			{
				$model = LibraryMeetingMinutes::find()->where(['id' => $data['id']])->one();
				$logmodel = LibraryMeetingMinutesLog::deleteAll(['minutes_id' => $data['id']]);
				if($model !==null){
					$model->delete();
					$responsedata = array('status'=>1,'message'=>'Deleted successfully');
				}
			}
		}
		return $responsedata;
	}

	public function actionDeleteMinutelog(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

		if($data){

			
			if(isset($data['id']))
			{
				$model = LibraryMeetingMinutesLog::find()->where(['id' => $data['id']])->one();
				if($model !==null){
					$model->delete();
					$responsedata = array('status'=>1,'message'=>'Deleted successfully');
				}
			}
		}
		return $responsedata;
	}



	public function actionGetMinutes()
    {
		/*
		$arrRaised=array();
		$meetingModel =LibraryMeeting::find()->all();
		if(count($meetingModel)>0)
		{
			foreach($meetingModel as $meeting)
			{
				$arrRaised[$meeting->id]= date($date_format,strtotime($meeting->meeting_date)).' '.$modelObj->arrType[$meeting->type];
			}			
		}
		*/
		
		$modelObj = new LibraryMeeting();
		
		$model = new LibraryMeetingMinutes();
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if($data)
		{
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			$minutemodel =LibraryMeetingMinutes::find()->where(['meeting_id'=> $data['meeting_id']])->all();
			$meetinglog_arr = array();
			if(count($minutemodel)>0)
			{
				
				foreach($minutemodel as $val)
				{
					$raised_id_label = ($val->meeting ? date($date_format,strtotime($val->meeting->meeting_date)).' '.$modelObj->arrType[$val->meeting->type]:'');
					
					$minutes_arr = array();
					$minutes_arr['id'] = $val['id'];
					$minutes_arr['meeting_id'] = $val['meeting_id'];
					$minutes_arr['raised_id'] = $val['raised_id'];
					//$minutes_arr['raised_id_label'] = $model->arrRaised[$val['raised_id']];
					$minutes_arr['raised_id_label'] = $raised_id_label;
					
					$minutes_arr['minute_date'] = date($date_format,strtotime($val['minute_date']));
					$minutes_arr['class'] = $val['class'];
					$minutes_arr['class_label'] = $model->arrClass[$val['class']];
					$minutes_arr['details'] = $val['details'];
					$minutes_arr['status'] = $val['status'];
					$minutes_arr['status_label'] = $model->arrStatus[$val['status']];
					$minutes_arr['log_display_status'] = 0;
					$logs_arr = $val->minuteslogs;
					$minutes_arr['log_data']= $this->minutesLogslist($val->id);

					/*
					if(count($logs_arr)>0)
					{
						$logslist_arr = array();
						foreach($logs_arr as $vallog)
						{
							$logs = array();
							$logs['log_date'] =  date($date_format,strtotime($vallog['log_date']));
							$logs['description'] = $vallog['description'];
							$logs['status'] = $vallog['status'];
							$logs['status_label'] = 'OPEN';

							
							$logslist_arr[] = $logs;
						}
						$minutes_arr['log_data']=$logslist_arr;
					}
					*/


					$meetinglog_arr[]=$minutes_arr;
				}
				
			}
			$responsedata=array('status'=>1,'data' => $meetinglog_arr);
		}
		
		return $responsedata;
		
	}


	private function minutesLogslist($minutes_id){

		$logmodel =LibraryMeetingMinutesLog::find()->where(['minutes_id'=> $minutes_id])->all();
		$logslist_arr = array();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		if(count($logmodel)>0)
		{
			foreach($logmodel as $vallog)
			{
				$logs = array();
				$logs['log_date'] =  date($date_format,strtotime($vallog['log_date']));
				$logs['description'] = $vallog['description'];
				$logs['status'] = $vallog['status'];
				$logs['status_label'] = isset($vallog->arrStatus[$vallog['status']])?$vallog->arrStatus[$vallog['status']]:'';
				$logs['id'] = $vallog['id'];
				$logs['minute_id'] = $minutes_id;
				$logslist_arr[] = $logs;
			}
		}
		return $logslist_arr;
	}

	public function actionGetMinutelogs()
    {
		$model = new LibraryMeetingMinutesLog();
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if($data)
		{
			$minutelog_arr = $this->minutesLogslist($data['minute_id']);
			$responsedata=array('status'=>1,'data' => $minutelog_arr);
		}
		
		return $responsedata;
		
	}
	public function actionAddlogdata()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

		if($data){

			
			if(isset($data['id']))
			{
				$model = LibraryMeetingMinutesLog::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new LibraryMeetingMinutesLog();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new LibraryMeetingMinutesLog();
				$model->created_by = $userData['userid'];
			}

			 
			$model->minutes_id= $data['minute_id'];
			$model->log_date = date('Y-m-d',strtotime($data['log_date']));	
			$model->description = $data['description'];	
			$model->status = $data['status'];
			
			
			if($model->validate() && $model->save())
			{
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Log has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Log created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}
	public function actionDeletemeetingdata()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$model = LibraryMeeting::deleteAll(['id' => $data['id']]);
			$minuteslist = LibraryMeetingMinutes::find()->where(['meeting_id'=>$data['id']])->all();
			if(count($minuteslist)>0){
				foreach($minuteslist as $minutedata){
					$logmodel = LibraryMeetingMinutesLog::deleteAll(['minutes_id' => $minutedata->id]);
				}
			}
			$minmodel = LibraryMeetingMinutes::deleteAll(['meeting_id' => $data['id']]);
			

			$responsedata=array('status'=>1,'message'=>'Deleted successfully');
		}
		return $this->asJson($responsedata);
	}

	public function actionDeleteminutedata()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$model = LibraryMeetingMinutes::deleteAll(['id' => $data['id']]);
			$logmodel = LibraryMeetingMinutesLog::deleteAll(['minutes_id' => $data['id']]);

			$responsedata=array('status'=>1,'message'=>'Deleted successfully');
		}
		return $this->asJson($responsedata);
	}
	
	public function actionView()
    {
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$meetingmodel = new LibraryMeeting();

        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$resultarr["type"]=$model->title;
			$resultarr["meeting_date"]=date($date_format,$model->meeting_date);
			$resultarr["location"]=$model->location;
			$resultarr["attendees"]=$model->attendees;
			$resultarr["apologies"]=$model->apologies;

			$meetinglog = $model->librarymeetingminutes;
			if(count($meetinglog)>0)
			{
				$meetinglog_arr = array();
				foreach($meetinglog as $val)
				{
					$minutes_arr = array();
					$minutes_arr['id'] = $val['id'];
					$minutes_arr['meeting_id'] = $val['meeting_id'];
					$minutes_arr['raised_id'] = $val['raised_id'];
					$minutes_arr['minute_date'] = date($date_format,$val['minute_date']);
					$minutes_arr['class'] = $val['class'];
					$minutes_arr['details'] = $val['details'];
					$meetinglog_arr[]=$log_arr;
				}
				$resultarr["minutes"] = $meetinglog_arr;
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
           	$model = LibraryMeeting::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Meeting has been activated successfully';
					}elseif($model->status==1){
						$msg='Meeting has been deactivated successfully';
					}elseif($model->status==2){
						$msg='Meeting has been deleted successfully';
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
        if (($model = LibraryMeeting::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	
}
