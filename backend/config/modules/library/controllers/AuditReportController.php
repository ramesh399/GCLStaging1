<?php
namespace app\modules\library\controllers;

use Yii;
use app\modules\library\models\LibraryAuditReport;
use app\modules\library\models\LibraryAuditReportAccess;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditReportController implements the CRUD actions for Product model.
 */
class AuditReportController extends \yii\rest\Controller
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
		
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];
		$role_chkid=$userData['role_chkid'];

		$auditmodel = new LibraryAuditReport();
		$model = LibraryAuditReport::find()->alias('t');
		$model = $model->joinWith('franchise as franchise');
		$model = $model->joinWith('reviewerdata as reviewer');
		
		$model = $model->join('left join', 'tbl_user_company_info as usercompanyinfo','usercompanyinfo.user_id=franchise.id');

		if($resource_access != '1')
		{
			$source_file_status = 0;
			$model = $model->join('inner join', 'tbl_library_audit_report_access as report_access','report_access.library_audit_report_id=t.id');									
			if($user_type==2)
			{
				$customer_roles=Yii::$app->globalfuns->getCustomerRoles();					
				$model = $model->andWhere('report_access.user_access_id in("'.$customer_roles.'")');	
				if($franchiseid){
					$model = $model->andWhere('t.franchise_id="'.$franchiseid.'"');
				}
			}elseif($user_type==3 && $resource_access==5){		
				$model = $model->andWhere('report_access.user_access_id ="'.$role_chkid.'"');		
				//$model = $model->andWhere('t.franchise_id="'.$franchiseid.'"');
				//$ospadmin_roles=Yii::$app->globalfuns->getOspAdminRoles();					
				//$model = $model->andWhere('report_access.user_access_id in("'.$ospadmin_roles.'")');	
			}elseif($user_type==3){
				$osp_roles=Yii::$app->globalfuns->getOspRoles();					
				$model = $model->andWhere('report_access.user_access_id in("'.$osp_roles.'")');		
				//$model = $model->andWhere('t.franchise_id="'.$userid.'"');				
			}else if($user_type== Yii::$app->params['user_type']['user'] && in_array('i_e_audits',$rules )  && $is_headquarters!=1){
				//$model = $model->andWhere('t.franchise_id="'.$franchiseid.'"');
			}else{
				$model = $model->andWhere('report_access.user_access_id ="'.$role.'"');	
				//$model = $model->andWhere('t.franchise_id="'.$franchiseid.'"');	
			}			
		}

		
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm']!='')
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					// ['like', 'title', $searchTerm],
					['like', 'date_format(`report_date`, \'%b %d, %Y\' )', $searchTerm],
					['like', 'CONCAT("OSS ", usercompanyinfo.osp_number)', $searchTerm],
					['like', 'usercompanyinfo.osp_details', $searchTerm],
					['like', 'CONCAT("OSS ", usercompanyinfo.osp_number," - ",usercompanyinfo.osp_details)', $searchTerm],
					['like', 'reviewer.first_name', $searchTerm],
					['like', 'reviewer.last_name', $searchTerm],
					['like', 'CONCAT(reviewer.first_name," ",reviewer.last_name)', $searchTerm],
				]);

				
			}
			$totalCount = $model->count();
			
			if(isset($post['franchiseFilter']) && is_array($post['franchiseFilter']) && count($post['franchiseFilter'])>0)
			{
				$model = $model->andWhere(['t.franchise_id'=> $post['franchiseFilter']]);			
			}

			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
			}
			else
			{
				$model = $model->orderBy(['t.created_at' => SORT_DESC]);
			}
			



            $model = $model->limit($pageSize)->offset($page);
		}
		else
		{
			$totalCount = $model->count();
		}
		
		$report_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $question)
			{
				$data=array();
				$data['id']=$question->id;
				$data['franchise_id']=$question->franchise_id;
				$data['franchise_id_label']=$question->franchise->usercompanyinfo?'OSS '.$question->franchise->usercompanyinfo->osp_number.' - '.$question->franchise->usercompanyinfo->osp_details:'';
				$data['date']=date($date_format,strtotime($question->report_date));
				$data['reviewer']=$question->reviewer;
				$data['reviewer_label'] = $question->reviewerdata?$question->reviewerdata->first_name.' '.$question->reviewerdata->last_name:'';
				$data['description']=$question->description;
				$data['source_file']=$question->source_file;
				//$data['access_id']=$question->access_id;
				//$data['access_id_label']=$auditmodel->arrAccess[$question->access_id];
				$LibraryAuditReportAccessModel= LibraryAuditReportAccess::find()->where(['library_audit_report_id'=>$question->id])->all();
				if(count($LibraryAuditReportAccessModel)>0){
					foreach ($LibraryAuditReportAccessModel as $LibraryAuditReportAccess) {
						$data['access_id'][] = "$LibraryAuditReportAccess->user_access_id";
						$data['access_id_label'][] = $LibraryAuditReportAccess->useraccess->role_name;
					}
				}
				$data['created_at']=date($date_format,$question->created_at);
				$data['created_by_label']=$question->createdbydata->first_name.' '.$question->createdbydata->last_name;
				$report_list[]=$data;
			}
		}

		return ['auditreports'=>$report_list,'total'=>$totalCount];
    }


	public function actionCreate()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$datapost = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$target_dir = Yii::$app->params['library_files']."audit_reports/"; 
		
		if($datapost){

			$data =json_decode($datapost['formvalues'],true);
			

			if(isset($data['id']))
			{
				$model = LibraryAuditReport::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new LibraryAuditReport();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new LibraryAuditReport();
				$model->created_by = $userData['userid'];
			}
			
			$model->franchise_id = $data['franchise_id'];
			$model->report_date = date("Y-m-d",strtotime($data['report_date']));	
			$model->description = $data['description'];
			$model->reviewer = $data['reviewer'];
			//$model->access_id = $data['access_id'];	
			if(isset($_FILES['source_file']['name']))
			{
				$tmp_name = $_FILES["source_file"]["tmp_name"];
				$name = $_FILES["source_file"]["name"];
				if($model!==null)
				{
					Yii::$app->globalfuns->removeFiles($model->source_file,$target_dir);													
				}
				$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
			}else{
				$filename = $data['source_file'];
			}
			$model->source_file = $filename;
			
			if($model->validate() && $model->save())
			{	
				LibraryAuditReportAccess::deleteAll(['library_audit_report_id' => $model->id]);		
				if(is_array($data['access_id']) && count($data['access_id'])>0)
                {
                    foreach ($data['access_id'] as $value)
                    { 
						$LibraryAuditReportAccessModel =  new LibraryAuditReportAccess();
						$LibraryAuditReportAccessModel->library_audit_report_id = $model->id;
						$LibraryAuditReportAccessModel->user_access_id = $value;
						$LibraryAuditReportAccessModel->save();
					}
				}
				
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Audit Report has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Audit Report has been created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}

	public function actionTypelist()
	{
		//$modelObj = new LibraryAuditReport();
	
		$UserAccess = Yii::$app->globalfuns->getUserRoles();

		$arrReviewer=array();
		
		$reviewers = Yii::$app->globalfuns->getReviewers();
		if(count($reviewers)>0)
		{
			foreach($reviewers as $reviewer)
			{
				$arrReviewer[$reviewer['id']]=$reviewer['first_name'].' '.$reviewer['last_name'];
			}
		}
		return ['reviewerlist'=>$arrReviewer,'accesslist'=>$UserAccess];
	}

	public function actionAuditreportfile(){
		$data = Yii::$app->request->post();
		$files = LibraryAuditReport::find()->where(['id'=>$data['id']])->one();
		//if($data['filetype']=='gisfile'){
			$filename = $files->source_file;
		//}
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['library_files']."audit_reports/".$filename;
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

	public function actionDeletereportdata()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$files = LibraryAuditReport::find()->where(['id'=>$data['id']])->one();
			$filename = $files->source_file;
			$unlinkFile = Yii::$app->params['library_files']."audit_reports/".$filename;
			if(file_exists($unlinkFile))
			{
				@unlink($unlinkFile);
			}
			LibraryAuditReportAccess::deleteAll(['library_audit_report_id' => $data['id']]);
			$model = LibraryAuditReport::deleteAll(['id' => $data['id']]);
			$responsedata=array('status'=>1,'message'=>'Deleted successfully');
		}
		return $this->asJson($responsedata);
	}
	

    protected function findModel($id)
    {
        if (($model = LibraryLegislation::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	
}
