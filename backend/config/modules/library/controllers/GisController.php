<?php
namespace app\modules\library\controllers;

use Yii;
use app\modules\library\models\LibraryGis;
use app\modules\library\models\LibraryGisLog;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * GisController implements the CRUD actions for Product model.
 */
class GisController extends \yii\rest\Controller
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

		$gismodel = new LibraryGis();

		$model = LibraryGis::find()->alias('t');
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			$statusarray=array_map('strtolower', $gismodel->arrStatus);
			if(isset($post['statusFilter']) && $post['statusFilter']>='0')
			{
				$model->andWhere(['status'=> $post['statusFilter']]);
			}
			if(isset($post['typeFilter']) && $post['typeFilter']>='0')
			{
				$model->andWhere(['type'=> $post['typeFilter']]);
			}

			if(isset($post['from_date']))
			{
				$from_date = date("Y-m-d",strtotime($post['from_date']));
				$model = $model->andWhere(['>=','t.received_date',$from_date]);			
			}

			
			if(isset($post['to_date']))
			{
				$to_date = date("Y-m-d",strtotime($post['to_date']));
				$model = $model->andWhere(['<=','t.received_date', $to_date]);			
			}

			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$typearray=array_map('strtolower', $gismodel->arrType);
				$search_type = array_search(strtolower($searchTerm),$typearray);
				$search_status = array_search(strtolower($searchTerm),$statusarray);
				if($search_type===false)
				{
					$search_type = '';
				}
				
				if($search_status===false)
				{
					$search_status = '';
				}

				$model = $model->andFilterWhere([
					'or',
					['like', 'title', $searchTerm],
					['like', 'date_format(`received_date`, \'%b %d, %Y\' )', $searchTerm],
					//['like', 'date_format(FROM_UNIXTIME(`created_at` ), \'%b %d, %Y\' )', $searchTerm],
					['status'=>$search_status],
					['type'=>$search_type]
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
		
		$gis_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $gis)
			{
				$data=array();
				$data['id']=$gis->id;
				$data['title']=$gis->title;
				$data['received_date']=date($date_format,strtotime($gis->received_date));
				$data['type']=$gis->type;
				$data['description']=$gis->description;
				$data['type_label']=$gismodel->arrType[$gis->type];
				$data['status']=$gis->status;
				$data['gis_file']=$gis->gis_file;
				$data['status_label']=$gismodel->arrStatus[$gis->status];
				$data['created_at']=date($date_format,$gis->created_at);
				$data['created_by_label']=$gis->createdbydata->first_name.' '.$gis->createdbydata->last_name;
				$data["logs"] = [];
				$logmodel = $gis->librarygislog;
				
				if(count($logmodel)>0)
				{
					$gislog_arr = array();
					foreach($logmodel as $val)
					{
						$log_arr = array();
						$log_arr['id'] = $val['id'];
						$log_arr['log_date'] = date($date_format,strtotime($val['log_date']));
						$log_arr['type'] = $val['type'];
						$log_arr['type_label'] = $gismodel->arrType[$val['type']];
						$log_arr['description'] = $val['description'];
						$gislog_arr[]=$log_arr;
					}
					$data["logs"] = $gislog_arr;
				}
				
				$gis_list[]=$data;
			}
		}

		return ['gislogs'=>$gis_list,'total'=>$totalCount, 'typelist'=>$gismodel->arrType, 'statuslist'=>$gismodel->arrStatus];
    }

    public function actionGetlogdata(){

    	$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

		if($data){
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
	    	$gislogmodel = LibraryGisLog::find()->where(['library_gis_id'=>$data['gis_id']])->all();
	    	$gis_list=[];
	    	if(count($gislogmodel)>0)
	    	{
	    		foreach($gislogmodel as $log){
	    			$data=array();
					$data['id']=$log->id;
					$data['library_gis_id']=$log->library_gis_id;
					$data['log_date']=date($date_format,strtotime($log->log_date));
					$data['type']=$log->type;
					$data['type_label'] = $log->arrType[$log['type']];
					$data['description']=$log->description;
					//$data['created_by']=$log->arrType[$gis->type];
					
					$gis_list[]=$data;
	    		}
		    	
			}
			$responsedata=array('status'=>1,'data' => $gis_list);
		}
		return $responsedata;

	}
	
	public function actionGisfile(){
		$data = Yii::$app->request->post();
		$files = LibraryGis::find()->where(['id'=>$data['id']])->one();
		//if($data['filetype']=='gisfile'){
			$filename = $files->gis_file;
		//}
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['library_files']."gis/".$filename;
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

    public function actionGisstatuslist()
	{
		$modelObj = new LibraryGis();
		$logmodel = new LibraryGisLog();
		return ['typelist'=>$modelObj->arrType, 'statuslist'=>$modelObj->arrStatus, 'logtypelist'=>$logmodel->arrType];
	}

	public function actionAddgislogdata()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

		if($data){

			
			if(isset($data['id']))
			{
				$model = LibraryGisLog::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new LibraryGisLog();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new LibraryGisLog();
				$model->created_by = $userData['userid'];
			}

			 
			$model->library_gis_id= $data['gis_id'];
			$model->log_date = date('Y-m-d',strtotime($data['date']));	
			$model->type = $data['type'];	
			$model->description = $data['description'];	
			
			
			if($model->validate() && $model->save())
			{
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'GIS log has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'GIS log created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}

    public function actionCreate()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$datapost = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$target_dir = Yii::$app->params['library_files']."gis/"; 
		
		if($datapost){

			$data =json_decode($datapost['formvalues'],true);
		

			if(isset($data['id']))
			{
				$model = LibraryGis::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new LibraryGis();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new LibraryGis();
				$model->created_by = $userData['userid'];
			}

			
			$model->title = $data['title'];	
			$model->received_date = date('Y-m-d',strtotime($data['received_date']));	
			$model->type = $data['type'];	
			$model->description = $data['description'];	
			$model->status = $data['status'];	
			if(isset($_FILES['gis_file']['name']))
			{
				$tmp_name = $_FILES["gis_file"]["tmp_name"];
				$name = $_FILES["gis_file"]["name"];
				if($model!==null)
				{
					Yii::$app->globalfuns->removeFiles($model->gis_file,$target_dir);													
				}
				$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
			}else{
				$filename = $data['gis_file'];
			}
			$model->gis_file= $filename;
			
			if($model->validate() && $model->save())
			{	
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'GIS has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'GIS created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}
	public function actionDeletegis(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$target_dir = Yii::$app->params['library_files']."gis/"; 

		if($data){

			
			if(isset($data['id']))
			{
				$model = LibraryGis::find()->where(['id' => $data['id']])->one();
				if($model !==null){
					$filename = $model->gis_file;
					$unlinkFile = $target_dir.$filename;
					if(file_exists($unlinkFile))
					{
						@unlink($unlinkFile);
					}

					LibraryGisLog::deleteAll(['library_gis_id' => $model->id]);
					$model->delete();
					$responsedata = array('status'=>1,'message'=>'Deleted successfully');
				}
			}
		}
		return $responsedata;
	}

	public function actionDeletegislog(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

		if($data){

			
			if(isset($data['id']))
			{
				$model = LibraryGisLog::find()->where(['id' => $data['id']])->one();
				if($model !==null){
					$model->delete();
					$responsedata = array('status'=>1,'message'=>'Deleted successfully');
				}
			}
		}
		return $responsedata;
	}
	/*foreach($data['gislogs'] as $logs)
	{
		$gislogmodel = new LibraryGisLog();
		$gislogmodel->library_gis_id = $model->id;
		$gislogmodel->log_date = $logs['log_date'];
		$gislogmodel->type = $logs['type'];
		$gislogmodel->description = $logs['description'];
		$gislogmodel->created_by = $userData['userid'];
		$gislogmodel->save();
	}
	

    public function actionUpdate()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$model = LibraryGis::find()->where(['id' => $data['id']])->one();
			$model->title = $data['title'];	
			$model->received_date = date('Y-m-d',strtotime($data['received_date']));	
			$model->type = $data['type'];	
			$model->description = $data['description'];	
			$model->status = $data['status'];	
			$userData = Yii::$app->userdata->getData();
			$model->updated_by = $userData['userid'];
			
			if($model->validate() && $model->save())
			{
				LibraryGisLog::deleteAll(['library_gis_id' => $model->id]);
				foreach($data['gislogs'] as $logs)
				{
					$gislogmodel = new LibraryGisLog();
					$gislogmodel->library_gis_id = $model->id;
					$gislogmodel->log_date = $logs['log_date'];
					$gislogmodel->type = $logs['type'];
					$gislogmodel->description = $logs['description'];
					$gislogmodel->created_by = $userData['userid'];
					$gislogmodel->save();
				}

				$responsedata=array('status'=>1,'message'=>'GIS log has been updated successfully');	
			}
		}
		return $this->asJson($responsedata);
    }*/
	
	public function actionView()
    {
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$gismodel = new LibraryGis();

        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$resultarr["title"]=$model->title;
			$resultarr["received_date"]=date($date_format,$model->received_date);
			$resultarr["type"]=$model->type;
			$resultarr["type_label"]=$gismodel->arrType[$model->type];
			$resultarr["description"]=$model->description;
			$resultarr["gis_file"]=$model->gis_file;
			$resultarr["status"]=$model->status;
			$resultarr["status_label"]=$gismodel->arrStatus[$model->status];

			$logmodel = $model->librarygislog;
			if ($logmodel !== null)
			{
				$resultarr=array();
				if(count($gislog)>0)
				{
					$gislog_arr = array();
					foreach($gislog as $val)
					{
						$log_arr = array();
						$log_arr['id'] = $val['id'];
						$log_arr['log_date'] = date($date_format,$val['log_date']);
						$log_arr['type'] = $val['type'];
						$log_arr['type_label'] = $gismodel->arrType[$val['type']];
						$log_arr['description'] = $val['description'];
						$gislog_arr[]=$log_arr;
					}
					$resultarr["logs"] = $gislog_arr;
				}
			}
            return ['data'=>$resultarr];
        }

	}

	public function actionGetlog()
    {
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$gismodel = new LibraryGis();
		$logmodel = LibraryGisLog::find()->where(['library_gis_id'=>$data['gis_id']])->all(); 

        if ($logmodel !== null)
		{
			$resultarr=array();
			if(count($gislog)>0)
			{
				$gislog_arr = array();
				foreach($gislog as $val)
				{
					$log_arr = array();
					$log_arr['id'] = $val['id'];
					$log_arr['log_date'] = date($date_format,$val['log_date']);
					$log_arr['type'] = $val['type'];
					$log_arr['type_label'] = $val->arrType[$val['type']];
					$log_arr['description'] = $val['description'];
					$gislog_arr[]=$log_arr;
				}
				$resultarr["logs"] = $gislog_arr;
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
           	$model = LibraryGis::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='GIS has been activated successfully';
					}elseif($model->status==1){
						$msg='GIS has been deactivated successfully';
					}elseif($model->status==2){
						$msg='GIS has been deleted successfully';
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
        if (($model = LibraryGis::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	
}
