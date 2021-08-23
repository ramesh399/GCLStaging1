<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\audit\models\AuditReportAttendanceSheet;
use app\modules\audit\models\AuditReportApplicableDetails;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditAttendanceController implements the CRUD actions for Product model.
 */
class AuditAttendanceController extends \yii\rest\Controller
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

		$data = [];
		$data['audit_id'] = $post['audit_id'];
		$data['unit_id'] = $post['unit_id'];
		$data['checktype'] = 'unitwise';

		if(!Yii::$app->userrole->canViewAuditReport($data)){
			return false;
		}

		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		$attendancemodel = new AuditReportAttendanceSheet();
		$model = AuditReportAttendanceSheet::find()->where(['audit_id'=>$post['audit_id'],'unit_id' => $post['unit_id']]);

		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm']!='')
			{
				$searchTerm = $post['searchTerm'];
				$openarray=array_map('strtolower', $attendancemodel->arrOpen);
				$opensearch = array_search(strtolower($searchTerm),$openarray);
				if($opensearch===false)
				{
					$opensearch='';
				}

				$closearray=array_map('strtolower', $attendancemodel->arrClose);
				$closesearch = array_search(strtolower($searchTerm),$closearray);
				if($closesearch===false)
				{
					$closesearch='';
				}

				$model = $model->andFilterWhere([
					'or',
					['like', 'name', $searchTerm],
					['like', 'position', $searchTerm],
					['open'=> $opensearch],
					['close'=> $closesearch],
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
		
		$attendance_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $value)
			{
				$data=array();
				$data['id']=$value->id;
				$data['audit_id']=$value->audit_id;
				$data['unit_id']=$value->unit_id;
				$data['name']=$value->name;
				$data['position']=$value->position;
				$data['open']=$value->open;
				$data['close']=$value->close;
				$data['open_label'] = $attendancemodel->arrOpen[$value->open];
				$data['close_label'] = $value->close?$attendancemodel->arrOpen[$value->close]:'';
				$data['created_at']=date($date_format,$value->created_at);
				$data['created_by_label']=$value->createdbydata->first_name.' '.$value->createdbydata->last_name;
				$attendance_list[]=$data;
			}
		}

		return ['attendances'=>$attendance_list,'total'=>$totalCount];
    }


	public function actionCreate()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		
		if($data)
		{	
			$arraydata = ['audit_id'=>$data['audit_id'],'unit_id'=>$data['unit_id'],'report_name'=>$data['type']];

			$pdata = [];
			$pdata['audit_id'] = $data['audit_id'];
			$pdata['unit_id'] = $data['unit_id'];
			$pdata['checktype'] = 'unitwise';
			if(!Yii::$app->userrole->canEditAuditReport($pdata)){
				return false;
			}

			
			Yii::$app->globalfuns->UpdateApplicableDetails($arraydata);
			
			if(isset($data['id']))
			{
				$model = AuditReportAttendanceSheet::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new AuditReportAttendanceSheet();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new AuditReportAttendanceSheet();
				$model->created_by = $userData['userid'];
			}

			
			$model->audit_id = $data['audit_id'];
			$model->unit_id = $data['unit_id'];
			$model->name = $data['name'];
			$model->position = $data['position'];	
			$model->open = $data['open'];
			$model->close = isset($data['close'])?$data['close']:'';
			
			
			if($model->validate() && $model->save())
			{	
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Attendance has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Attendance has been created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}

	public function actionOptionlist()
	{
		$modelObj = new AuditReportAttendanceSheet();
		return ['openlist'=>$modelObj->arrOpen,'closelist'=>$modelObj->arrClose];
	}


	public function actionDeleteAttendance()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$modelchk = AuditReportAttendanceSheet::find()->where(['id' => $data['id']])->one();
			if($modelchk!== null){
				$data['audit_id']= $modelchk->audit_id;
				$data['unit_id'] = $modelchk->unit_id;
				$data['checktype'] = 'unitwise';
				if(!Yii::$app->userrole->canEditAuditReport($data)){
					return false;
				}
				$model = AuditReportAttendanceSheet::deleteAll(['id' => $data['id']]);
				$responsedata=array('status'=>1,'message'=>'Deleted successfully');
			}
		}
		return $this->asJson($responsedata);
	}
	
	
	
	
	
	
}
