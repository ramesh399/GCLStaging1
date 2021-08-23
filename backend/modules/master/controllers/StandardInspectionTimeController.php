<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\StandardInspectionTime;
use app\modules\master\models\StandardInspectionTimeStandard;
use app\modules\master\models\StandardInspectionTimeProcess;
use app\modules\master\models\StandardOtherInspectionTimeProcess;
use app\modules\master\models\StandardInspectionTimeTradingProcess;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;
/**
 * StandardInspectionTimeController implements the CRUD actions for Process model.
 */
class StandardInspectionTimeController extends \yii\rest\Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
			[
				'class' => \yii\filters\ContentNegotiator::className(),
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
		if(!Yii::$app->userrole->hasRights(array('add_inspection_time','edit_inspection_time')))
		{
			return false;
		}
		
        $responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        $data = Yii::$app->request->post();
        $userData = Yii::$app->userdata->getData();

		if ($data) 
		{

		   if(isset($data['type']) && ($data['type'] == 'normal'))
		   {
				if(isset($data['id']))
				{
					$model = StandardInspectionTime::find()->where(['id' => $data['id']])->one();
					if($model===null){
						$model = new StandardInspectionTime();
						$model->created_by = $userData['userid'];
					}else{
						$model->updated_by = $userData['userid'];
					}
				}else{
					$model = new StandardInspectionTime();
					$model->created_by = $userData['userid'];
				}
				
				$model->no_of_workers_from = $data['no_of_workers_from'];
				$model->no_of_workers_to = $data['no_of_workers_to'];	
		   } 
		   else if(isset($data['type']) && ($data['type'] == 'other'))
		   {
				if(isset($data['id']))
				{
					$model = StandardOtherInspectionTimeProcess::find()->where(['id' => $data['id']])->one();
					if($model===null){
						$model = new StandardOtherInspectionTimeProcess();
						$model->created_by = $userData['userid'];
					}else{
						$model->updated_by = $userData['userid'];
					}
				}else{
					$model = new StandardOtherInspectionTimeProcess();
					$model->created_by = $userData['userid'];
				}
				
				$model->no_of_process_from = $data['no_of_process_from'];
				$model->no_of_process_to = $data['no_of_process_to'];
				$model->inspector_days = $data['inspector_days'];	
		   }
		   else if(isset($data['type']) && ($data['type'] == 'standard'))
		   {
				if(isset($data['id']))
				{
					$model = StandardInspectionTimeTradingProcess::find()->where(['id' => $data['id']])->one();
					if($model===null){
						$model = new StandardInspectionTimeTradingProcess();
						$model->created_by = $userData['userid'];
					}else{
						$model->updated_by = $userData['userid'];
					}
				}else{
					$model = new StandardInspectionTimeTradingProcess();
					$model->created_by = $userData['userid'];
				}
				
				$model->no_of_standard_from = $data['no_of_standard_from'];
				$model->no_of_standard_to = $data['no_of_standard_to'];
				$model->inspector_days = $data['inspector_days'];	
		   }
           
            

            if($model->validate() && $model->save())
			{	
				
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Inspection Time has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Inspection Time created successfully');
				}
			}
        }
        return $this->asJson($responsedata);
	} 
	
	public function actionCreateStandard()
	{
		if(!Yii::$app->userrole->hasRights(array('add_inspection_time','edit_inspection_time')))
		{
			return false;
		}

        $responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        $data = Yii::$app->request->post();

		if ($data) 
		{
			StandardInspectionTimeStandard::deleteAll(['inspection_time_type' => $data['inspection_time_type']]);
			foreach($data['standard_id'] as $std)
			{
				$stdmodel = new StandardInspectionTimeStandard();
				$stdmodel->standard_id = $std;
				$stdmodel->inspection_time_type = $data['inspection_time_type'];
				$stdmodel->save();
			}
			$responsedata=array('status'=>1,'message'=>'Standards Saved successfully');
		}
		return $this->asJson($responsedata);
	}
    
    public function actionView()
	{
		if(!Yii::$app->userrole->hasRights(array('inspection_time_master')))
		{
			return false;
		}

		$resultarr=array();
		$otherresultarr=array();
		$stdresultarr=array();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$model = StandardInspectionTime::find()->all();
		if ($model !== null)
		{
            foreach($model as $val)
            {
              	$resultarr[]=array('id'=>$val->id,'no_of_workers_from'=>$val->no_of_workers_from,'no_of_workers_to'=>$val->no_of_workers_to,'inspector_days'=>$val->inspector_days,'created_at'=>date($date_format,$val->created_at));
            }			
            
		}
		
		$othermodel = StandardOtherInspectionTimeProcess::find()->all();
		if ($othermodel !== null)
		{
			foreach($othermodel as $vals)
            {
				$otherresultarr[]=array('id'=>$vals->id,'no_of_process_from'=>$vals->no_of_process_from,'no_of_process_to'=>$vals->no_of_process_to,'inspector_days'=>$vals->inspector_days,'created_at'=>date($date_format,$vals->created_at));
            }	
		}

		$stdmodel = StandardInspectionTimeTradingProcess::find()->all();
		if ($stdmodel !== null)
		{
			foreach($stdmodel as $vals)
            {
				$stdresultarr[]=array('id'=>$vals->id,'no_of_standard_from'=>$vals->no_of_standard_from,'no_of_standard_to'=>$vals->no_of_standard_to,'inspector_days'=>$vals->inspector_days,'created_at'=>date($date_format,$vals->created_at));
            }	
		}

		$stdmodel = StandardInspectionTimeStandard::find()->all();
		if ($stdmodel !== null)
		{
			$workerstdarr = [];
			$procstdarr = [];
			foreach($stdmodel as $std)
            {
				if($std->inspection_time_type != '1')
				{
					$workerstdarr[] = "".$std->standard_id;
				}
				else
				{
					$procstdarr[] = "".$std->standard_id;
				}
			}
		}

		return ['inspectiontimes'=>$resultarr,'otherinspectiontimes'=>$otherresultarr,'standardinspectiontimes'=>$stdresultarr,'workerstandards'=>$workerstdarr,'procstandards'=>$procstdarr];
    }
    
    public function actionGetdata()
    {
    	$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

        if($data)
        {
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
	    	$gislogmodel = StandardInspectionTimeProcess::find()->where(['standard_inspection_time_id'=>$data['id']])->all();
	    	$gis_list=[];
	    	if(count($gislogmodel)>0)
	    	{
	    		foreach($gislogmodel as $log){
	    			$data=array();
					$data['id']=$log->id;
					$data['standard_inspection_time_id']=$log->standard_inspection_time_id;
					$data['no_of_process_from']=$log->no_of_process_from;
					$data['no_of_process_to']=$log->no_of_process_to;
					$data['inspector_days']=$log->inspector_days;
					
					$gis_list[]=$data;
	    		}
		    	
			}
			$responsedata=array('status'=>1,'data' => $gis_list);
		}
		return $responsedata;
    }
    
    public function actionAdddaysdata()
	{
		if(!Yii::$app->userrole->hasRights(array('add_inspection_time','edit_inspection_time')))
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

		if($data){

			
			if(isset($data['id']))
			{
				$model = StandardInspectionTimeProcess::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new StandardInspectionTimeProcess();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new StandardInspectionTimeProcess();
				$model->created_by = $userData['userid'];
			}

			 
			$model->standard_inspection_time_id = $data['inspection_id'];
			$model->no_of_process_from = $data['no_of_process_from'];	
			$model->no_of_process_to = $data['no_of_process_to'];	
			$model->inspector_days = $data['inspector_days'];	
			
			
			if($model->validate() && $model->save())
			{
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Inspection Days has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Inspection Days created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
    }
    
    public function actionDeletedays()
    {
		if(!Yii::$app->userrole->hasRights(array('delete_inspection_time')))
		{
			return false;
		}

		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

		if($data){

			
			if(isset($data['id']))
			{
				$model = StandardInspectionTimeProcess::find()->where(['id' => $data['id']])->one();
				if($model !==null){
					$model->delete();
					$responsedata = array('status'=>1,'message'=>'Deleted successfully');
				}
			}
		}
		return $responsedata;
	}


	public function actionDeleteothers()
    {
		if(!Yii::$app->userrole->hasRights(array('delete_inspection_time')))
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

		if($data){

			
			if(isset($data['id']))
			{
				$model = StandardOtherInspectionTimeProcess::find()->where(['id' => $data['id']])->one();
				if($model !==null){
					$model->delete();
					$responsedata = array('status'=>1,'message'=>'Deleted successfully');
				}
			}
		}
		return $responsedata;
	}


	public function actionDeletestd()
    {
		if(!Yii::$app->userrole->hasRights(array('delete_inspection_time')))
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

		if($data){

			
			if(isset($data['id']))
			{
				$model = StandardInspectionTimeTradingProcess::find()->where(['id' => $data['id']])->one();
				if($model !==null){
					$model->delete();
					$responsedata = array('status'=>1,'message'=>'Deleted successfully');
				}
			}
		}
		return $responsedata;
	}


}
