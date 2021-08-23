<?php
namespace app\modules\library\controllers;

use Yii;
use app\modules\library\models\LibraryDownload;
use app\modules\library\models\LibraryDownloadFile;
use app\modules\library\models\LibraryDownloadAccess;
use app\modules\library\models\LibraryUserAccess;
use app\modules\master\models\Standard;
use app\modules\library\models\LibraryDownloadStandard;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * DownloadController implements the CRUD actions for Product model.
 */
class DownloadController extends \yii\rest\Controller
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
		$modelObj = new LibraryDownload();		
		if($post)
		{
			$source_file_status = 1;
			$view_file_status = 1;
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$is_headquarters =$userData['is_headquarters'];
			$franchiseid=$userData['franchiseid'];
			$role_chkid=$userData['role_chkid'];
			
			$model = LibraryDownload::find()->alias('t');
			$model = $model->join('inner join', 'tbl_library_download_access as download_access','download_access.manual_id=t.id');	
			if(isset($post['roleFilter']) && is_array($post['roleFilter']) && count($post['roleFilter'])>0)
			{
				$model = $model->andWhere(['download_access.user_access'=> $post['roleFilter']]);				
			}
			
			if($resource_access != '1')
			{
				$source_file_status = 0;
				//$model = $model->join('inner join', 'tbl_library_download_access as download_access','download_access.manual_id=t.id');									
				
				if($user_type==2)
				{
					$customer_roles=Yii::$app->globalfuns->getCustomerRoles();					
					$model = $model->andWhere('download_access.user_access in("'.$customer_roles.'")');	
				}elseif($user_type==3 && $resource_access==5){			
					//$ospadmin_roles=Yii::$app->globalfuns->getOspAdminRoles();			
					$model = $model->andWhere('download_access.user_access ="'.$role_chkid.'"');	
					//$model = $model->andWhere('download_access.user_access in("'.$ospadmin_roles.'")');	
				}elseif($user_type==3){			
					$osp_roles=Yii::$app->globalfuns->getOspRoles();					
					$model = $model->andWhere('download_access.user_access in("'.$osp_roles.'")');	
				}else if($user_type== Yii::$app->params['user_type']['user'] && in_array($post['type'],$rules ) ){
					$source_file_status = 1;
				}else{
					//$model = $model->andWhere('download_access.user_access ="'.$role.'" and status="'.$modelObj->enumStatus['approved'].'"');	
					$model = $model->andWhere('download_access.user_access ="'.$role.'"');	
				}
				if($source_file_status ==0){
					$model = $model->andWhere('status="'.$modelObj->enumStatus['approved'].'"');
				}
				
				/*
				if($resource_access == '2'){  //Custom
					
				}elseif($resource_access == '3'){ //Technical Expert
				
				}elseif($resource_access == '4'){ //Translator
				
				}elseif($resource_access == '5'){ //OSP Admin
				
				}elseif($resource_access == '6'){ //Client
					$model = $model->where('download_access.user_access ="'.$role.'"');				
				}
				*/
			}	
			
			if(isset($post['type']) && $post['type'] !='')
			{
				$model->andWhere(['type'=> $post['type']]);				
			}
			$model = $model->groupBy(['t.id']);
			if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
			{
				$page = ($post['page'] - 1)*$post['pageSize'];
				$pageSize = $post['pageSize']; 
				$typearray=array_map('strtolower', $modelObj->arrType);
				$statusarray=array_map('strtolower', $modelObj->arrStatus);
				if(isset($post['statusFilter']) && $post['statusFilter']>='0')
				{
					$model->andWhere(['status'=> $post['statusFilter']]);
				}
				if(isset($post['searchTerm']))
				{
					$searchTerm = $post['searchTerm'];
					/*
					$search_type = array_search(strtolower($searchTerm),$typearray);

					if($search_type!==false)
					{
						$model = $model->orFilterWhere([
							'or', 					
							['type'=>$search_type]								
						]);
					}
					*/
					$search_status = array_search(strtolower($searchTerm),$statusarray);
					
					if($search_status===false)
					{
						$search_status='';
					}
					
					$model = $model->andFilterWhere([
						'or',
						['like', 'title', $searchTerm],
						['like', 'version', $searchTerm],
						['like', 'description', $searchTerm],
						['like', 'date_format(`document_date`, \'%b %d, %Y\' )', $searchTerm],
						['like', 'date_format(FROM_UNIXTIME(`created_at` ), \'%b %d, %Y\' )', $searchTerm],
						['status'=>$search_status]
					]);
					
					/*else{
						$model = $model->andFilterWhere([
							'or',
							['like', 'title', $searchTerm],
							['like', 'version', $searchTerm],
							['like', 'description', $searchTerm],
							['like', 'date_format(FROM_UNIXTIME(`document_date` ), \'%b %d, %Y\' )', $searchTerm],
							['like', 'date_format(FROM_UNIXTIME(`created_at` ), \'%b %d, %Y\' )', $searchTerm]
						]);
					}
					*/
					/*
					$model = $model->orFilterWhere([
						'or', 					
						['status'=>$search_status]								
					]);
					*/
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
			
			$list=array();
			$model = $model->all();		
			if(count($model)>0)
			{
				foreach($model as $modelData)
				{	
					$data=array();
					$data['id']=$modelData->id;
					$data['title']=$modelData->title;
					$data['version']=$modelData->version;
					$data['document_date']=date($date_format,strtotime($modelData->document_date));
					$data['description']=$modelData->description;
					$data['reviewer']=$modelData->reviewer;
					$data['reviewer_label']=$modelData->reviewerdata->first_name.' '.$modelData->reviewerdata->last_name;
					$data['created_by_label']=$modelData->createdbydata->first_name.' '.$modelData->createdbydata->last_name;
					//$data['reviewer_label']=$modelObj->arrType[$modelData->type];
					$data['status']=$modelData->status;
					$data['status_label']=$modelObj->arrStatus[$modelData->status];
					$data['created_at']=date($date_format,$modelData->created_at);
										
					$LibraryDownloadAccessModel= LibraryDownloadAccess::find()->where(['manual_id'=>$modelData->id])->all();
					if(count($LibraryDownloadAccessModel)>0){
						foreach ($LibraryDownloadAccessModel as $LibraryDownloadAccess) {
							if($LibraryDownloadAccess->useraccess!==null)
							{
								$data['access'][] = "$LibraryDownloadAccess->user_access";
								$data['access_label'][] = ($LibraryDownloadAccess->useraccess ? $LibraryDownloadAccess->useraccess->role_name : 'NA');
							}	
						}
					}					
					
					$LibraryDownloadStandardModel= LibraryDownloadStandard::find()->where(['manual_id'=>$modelData->id])->all();
					if(count($LibraryDownloadStandardModel)>0){
						foreach ($LibraryDownloadStandardModel as $LibraryDownloadStandard) {
							$data['standard'][] = "$LibraryDownloadStandard->standard_id";
							$data['standard_name'][] = $LibraryDownloadStandard->standard->name;
						}
					}

					$libdownmodel= LibraryDownloadFile::find()->where(['type'=>'source','manual_id'=>$modelData->id])->all();
					if(count($libdownmodel)>0){
						foreach ($libdownmodel as $lbfile) {
							$data['documents'][] = ['id'=>$lbfile->id,'name'=>$lbfile->document];							
						}
					}
					
					$libdownmodel= LibraryDownloadFile::find()->where(['type'=>'view','manual_id'=>$modelData->id])->all();
					if(count($libdownmodel)>0){
						foreach ($libdownmodel as $lbfile) {
							$data['viewdocuments'][] = ['id'=>$lbfile->id,'name'=>$lbfile->document];							
						}
					}

					$list[]=$data;
				}
			}
		}
		return ['manual'=>$list,'total'=>$totalCount, 'typelist'=>$modelObj->arrType, 'statuslist'=>$modelObj->arrStatus,'source_file_status'=>$source_file_status,'view_file_status'=>$view_file_status];
    }

    public function actionCreate()
	{		
		$modelLibraryDownload = new LibraryDownload();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		//$data = Yii::$app->request->post();
		$datapost = Yii::$app->request->post();
		if ($datapost) 
		{	
			$editStatus=1;
			
			$data =json_decode($datapost['formvalues'],true);		
			
			if(isset($data['id']) && $data['id']>0 && $data['newVersionStatus']<=0)
			{
				$model = LibraryDownload::find()->where(['id' => $data['id']])->one();
				if($model===null)
				{
					$model = new LibraryDownload();
					$editStatus=0;
				}
			}else{
				$editStatus=0;
				$model = new LibraryDownload();
			}

			$arrTypeAction = $modelLibraryDownload->arrTypeAction;
			$currentAction = 'add_'.$arrTypeAction[$data['type']];
			if($editStatus==1)
			{
				$currentAction = 'edit_'.$arrTypeAction[$data['type']];
			}
						
			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}
			
			
			$model->title = $data['title'];				
			$model->document_date = date('Y-m-d',strtotime($data['document_date']));	
			$model->reviewer = $data['reviewer'];	
			$model->description = $data['description'];	
			$model->type = $data['type'];	
			$model->status = $data['status'];	
			$userData = Yii::$app->userdata->getData();
			$model->created_by = $userData['userid'];
			
			if($model->type!='standards')
			{
				$model->version = $data['version'];	
			}
			
			if($model->validate() && $model->save())
			{	
				$manualID = $model->id;
				
				if($editStatus==1)
				{
					LibraryDownloadAccess::deleteAll(['manual_id' => $manualID]);
				}
				if(is_array($data['manual_access']) && count($data['manual_access'])>0)
                {
                    foreach ($data['manual_access'] as $value)
                    { 
						$LibraryDownloadAccessModel =  new LibraryDownloadAccess();
						$LibraryDownloadAccessModel->manual_id = $manualID;
						$LibraryDownloadAccessModel->user_access = $value;
						$LibraryDownloadAccessModel->save();
					}
				}
				
				$target_dir = Yii::$app->params['library_files'].$model->type."/"; 				
				
				if($editStatus==1)
				{
					LibraryDownloadFile::deleteAll(['manual_id' => $manualID]);
				}
				
				if($data['newVersionStatus']<=0)
				{
					if(is_array($data['documents']) && count($data['documents'])>0)
					{
						foreach ($data['documents'] as $key => $value)
						{ 
							$filename = '';
							if($value['added'] ==1 && $value['deleted']==0 && isset($_FILES['document']['name'][$key]))
							{
								$tmp_name = $_FILES["document"]["tmp_name"][$key];
								$name = $_FILES["document"]["name"][$key];
								$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
							}else if($value['added']==0 && $value['deleted']==0){
								$filename = $value['name'];
							}else if($value['deleted']==1){
								if($value['id']!='')
								{
									$filename = $value['name'];
									$unlinkFile = $target_dir.$filename;
									if(file_exists($unlinkFile))
									{
										@unlink($unlinkFile);
									}
								}
								continue;
							}
							
							$qualmodel=new LibraryDownloadFile();
							$qualmodel->manual_id=$manualID;						
							$qualmodel->document=$filename;
							$qualmodel->type='source';
							$qualmodel->save();
						}					
					}
					
					if(is_array($data['viewdocuments']) && count($data['viewdocuments'])>0)
					{
						foreach ($data['viewdocuments'] as $key => $value)
						{ 
							$filename = '';
							if($value['added'] ==1 && $value['deleted']==0 && isset($_FILES['viewdocument']['name'][$key]))
							{
								$tmp_name = $_FILES["viewdocument"]["tmp_name"][$key];
								$name = $_FILES["viewdocument"]["name"][$key];
								$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
							}else if($value['added']==0 && $value['deleted']==0){
								$filename = $value['name'];
							}else if($value['deleted']==1){
								if($value['id']!='')
								{
									$filename = $value['name'];
									$unlinkFile = $target_dir.$filename;
									if(file_exists($unlinkFile))
									{
										@unlink($unlinkFile);
									}
								}
								continue;
							}
							
							$qualmodel=new LibraryDownloadFile();
							$qualmodel->manual_id=$manualID;						
							$qualmodel->document=$filename;
							$qualmodel->type='view';
							$qualmodel->save();
						}					
					}
				}else{
					if(is_array($data['documents']) && count($data['documents'])>0)
					{
						foreach ($data['documents'] as $key => $value)
						{ 
							$filename = '';
							if($value['added'] ==1 && $value['deleted']==0 && isset($_FILES['document']['name'][$key]))
							{
								$tmp_name = $_FILES["document"]["tmp_name"][$key];
								$name = $_FILES["document"]["name"][$key];
								$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
							}else if($value['added']==0 && $value['deleted']==0){								
								$filename=Yii::$app->globalfuns->copyFiles($value['name'],$target_dir);								
							}else if($value['deleted']==1){
								continue;
							}
							
							$qualmodel=new LibraryDownloadFile();
							$qualmodel->manual_id=$manualID;						
							$qualmodel->document=$filename;
							$qualmodel->type='source';
							$qualmodel->save();
						}					
					}
					
					if(is_array($data['viewdocuments']) && count($data['viewdocuments'])>0)
					{
						foreach ($data['viewdocuments'] as $key => $value)
						{ 
							$filename = '';
							if($value['added'] ==1 && $value['deleted']==0 && isset($_FILES['viewdocument']['name'][$key]))
							{
								$tmp_name = $_FILES["viewdocument"]["tmp_name"][$key];
								$name = $_FILES["viewdocument"]["name"][$key];
								$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
							}else if($value['added']==0 && $value['deleted']==0){
								$filename=Yii::$app->globalfuns->copyFiles($value['name'],$target_dir);
							}else if($value['deleted']==1){
								continue;
							}
							
							$qualmodel=new LibraryDownloadFile();
							$qualmodel->manual_id=$manualID;						
							$qualmodel->document=$filename;
							$qualmodel->type='view';
							$qualmodel->save();
						}					
					}
					
					//------Archive the previous version code start here----------------
					$modelLibraryDownload = LibraryDownload::find()->where(['id' => $data['id']])->one();
					if($modelLibraryDownload!==null)
					{
						$modelLibraryDownload->status=$modelLibraryDownload->enumStatus['archived'];
						$modelLibraryDownload->save();
					}
					//------Archive the previous version code end here----------------
				}	
				
				if($model->type=='standards')
				{
					if($editStatus==1)
					{
						LibraryDownloadStandard::deleteAll(['manual_id' => $manualID]);
					}
					if(is_array($data['standards']) && count($data['standards'])>0)
					{
						foreach ($data['standards'] as $value)
						{ 
							$LibraryDownloadStandardModel =  new LibraryDownloadStandard();
							$LibraryDownloadStandardModel->manual_id = $manualID;
							$LibraryDownloadStandardModel->standard_id = $value;
							$LibraryDownloadStandardModel->save();
						}
					}	
				}
				
				$userMessage = $modelLibraryDownload->arrTypeData[$model->type].' has been created successfully';
				if($editStatus==1)
				{
					$userMessage = $modelLibraryDownload->arrTypeData[$model->type].' has been updated successfully';
				}
				
				$responsedata=array('status'=>1,'message'=>$userMessage);	
			}
		
		}
		return $this->asJson($responsedata);
	}
	
	public function actionView()
    {
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelObj = new LibraryDownload();

        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$resultarr["title"]=$model->title;
			$resultarr["version"]=$model->version;
			$resultarr["document_date"]=date($date_format,$model->document_date);
			$resultarr["reviewer"]=$model->reviewer;
			//$resultarr["reviewer_label"]=$modelObj->arrType[$model->type];
			$resultarr["description"]=$model->description;			
			$resultarr["status"]=$model->status;
			$resultarr["status_label"]=$modelObj->arrStatus[$model->status];
		
            return ['data'=>$resultarr];
        }

	}
	
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
           	$model = LibraryDownload::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$arrTypeAction = $model->arrTypeAction;
				$currentAction='';
				if($model->status==0){
					$currentAction='activate_'.$arrTypeAction[$model->type];
				}elseif($model->status==1){
					$currentAction='deactivate_'.$arrTypeAction[$model->type];
				}elseif($model->status==2){
					$currentAction='delete_'.$arrTypeAction[$model->type];
				}

				if(!Yii::$app->userrole->hasRights(array($currentAction)))
				{
					return false;
				}

				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Download has been activated successfully';
					}elseif($model->status==1){
						$msg='Download has been deactivated successfully';
					}elseif($model->status==2){
						$msg='Download has been deleted successfully';
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
        if (($model = LibraryDownload::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	public function actionGetData()
	{
		$modelObj = new LibraryDownload();		
		$Standard = Standard::find()->select(['id','name','code'])->where(['status'=>0])->asArray()->all();				
		//$UserAccess = LibraryUserAccess::find()->select(['id','name'])->where(['status'=>0])->asArray()->all();
		$UserAccess = Yii::$app->globalfuns->getUserRoles();
				
		$arrReviewer=array();
		 
		$reviewers =Yii::$app->globalfuns->getReviewers();
		if(count($reviewers)>0)
		{
			foreach($reviewers as $reviewer)
			{
				$arrReviewer[$reviewer['id']]=$reviewer['first_name'].' '.$reviewer['last_name'];
			}
		}	
		
		return ['useraccess'=>$UserAccess,'status'=>$modelObj->arrStatus,'enumstatus'=>$modelObj->enumStatus,'typelist'=>$modelObj->arrType,'standard'=>$Standard,'reviewerList'=>$arrReviewer];
	}
	
	public function actionDownloadfile(){
		$data = Yii::$app->request->post();
		//$data['id'];
		$files = LibraryDownloadFile::find()->where(['id'=>$data['id'],'type'=>$data['downloadtype']])->one();
		
		$file = $files->document;

		$target_dir = Yii::$app->params['library_files'].$data['type']."/";

		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=$target_dir.$file;
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
	
	public function actionDeletedata()
	{
		$data = Yii::$app->request->post();
		if($data)
		{			
			$id = $data['id'];
			$LibraryDownloadModel = LibraryDownload::find()->where(['id'=>$id])->one();
			if($LibraryDownloadModel!==null)
			{
				$arrTypeAction = $LibraryDownloadModel->arrTypeAction;
				if(!Yii::$app->userrole->hasRights(array('delete_'.$arrTypeAction[$LibraryDownloadModel->type])))
				{
					return false;
				}
								
				$target_dir = Yii::$app->params['library_files'].$LibraryDownloadModel->type."/"; 	
		
				$LibraryDownloadFileModel= LibraryDownloadFile::find()->where(['manual_id'=>$id])->all();
				if(count($LibraryDownloadFileModel)>0){
					foreach ($LibraryDownloadFileModel as $LibraryDownloadFile) 
					{
						$filename = $LibraryDownloadFile->document;
						$unlinkFile = $target_dir.$filename;
						if(file_exists($unlinkFile))
						{
							@unlink($unlinkFile);
						}						
					}
				}
				
				LibraryDownloadAccess::deleteAll(['manual_id' => $id]);
				LibraryDownloadFile::deleteAll(['manual_id' => $id]);
				LibraryDownloadStandard::deleteAll(['manual_id' => $id]);
				
				$LibraryDownloadModel->delete();
			}	
		}
		return $responsedata=array('status'=>1,'message'=>'Data deleted successfully');
	}
	
	
}
