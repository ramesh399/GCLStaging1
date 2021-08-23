<?php
namespace app\modules\library\controllers;

use Yii;
use app\modules\library\models\LibraryRiskAssessment;
use app\modules\library\models\LibraryRiskAssessmentLog;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * RiskassessmentController implements the CRUD actions for Product model.
 */
class RiskassessmentController extends \yii\rest\Controller
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

		$riskmodel = new LibraryRiskAssessment();

		$model = LibraryRiskAssessment::find()->alias('t');
		$model = $model->joinWith('franchise as franchise');
		$model = $model->join('left join', 'tbl_user_company_info as usercompanyinfo','usercompanyinfo.user_id=franchise.id');
		if($resource_access != '1')
		{
			/*
			if($user_type==3 && $is_headquarters!=1)
			{
				$model = $model->andWhere('created_by="'.$userid.'"');
			}else{
				$model = $model->andWhere('created_by=0');
			}
			*/
			if($user_type==3 && $resource_access==5){

				$model = $model->andWhere('t.franchise_id="'.$franchiseid.'"');
			}else if($user_type==3 && $is_headquarters!=1){
				$model = $model->andWhere('t.franchise_id="'.$userid.'"');
			}else if($user_type== Yii::$app->params['user_type']['user'] && in_array('risk_assessment',$rules ) && $is_headquarters!=1){
				$model = $model->andWhere('t.franchise_id="'.$franchiseid.'"');
			}else if($is_headquarters!=1){
				$model = $model->andWhere('t.created_by=0');
			}
		}
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$probabilitysearch = array_search($searchTerm,$riskmodel->arrProbability);
				if($probabilitysearch===false)
				{
					$probabilitysearch ='';
				}

				$threatarray=array_map('strtolower', $riskmodel->arrThreat);
				$search_type = array_search(strtolower($searchTerm),$threatarray);
				if($search_type===false)
				{
					$search_type = '';
				}

				$impactarray=array_map('strtolower', $riskmodel->arrImpact);
				$impactsearch = array_search(strtolower($searchTerm),$impactarray);
				if($impactsearch===false)
				{
					$impactsearch = '';
				}

				
				
				$model = $model->andFilterWhere([
					'or',
					['like', 'CONCAT("OSS ", usercompanyinfo.osp_number)', $searchTerm],
					['like', 'usercompanyinfo.osp_details', $searchTerm],
					['like', 'CONCAT("OSS ", usercompanyinfo.osp_number," - ",usercompanyinfo.osp_details)', $searchTerm],
					['like', 'vulnerability', $searchTerm],
					['impact'=> $impactsearch],
					['like', 'risk_value', $searchTerm],
					['probability'=>$probabilitysearch],
					['threat_id'=>$search_type],
				]);
				/*
				['like', 'date_format(FROM_UNIXTIME(`received_date` ), \'%b %d, %Y\' )', $searchTerm],
					['like', 'date_format(FROM_UNIXTIME(`created_at` ), \'%b %d, %Y\' )', $searchTerm],
				*/
				

				

				$totalCount = $model->count();
			}

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
		
		$list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $mdata)
			{
				$data=array();
				$data['id']=$mdata->id;
				$data['franchise_id']=$mdata->franchise_id;
				//$data['franchise_label']=  'OSS '.$mdata->franchise->usercompanyinfo->osp_numer.' - '.$mdata->franchise->usercompanyinfo->osp_details;
				$data['franchise_label']= 'OSS '.$mdata->franchise->usercompanyinfo->osp_number.' - '.$mdata->franchise->usercompanyinfo->osp_details;
				//$data['received_date']=date($date_format,strtotime($mdata->received_date));
				$data['threat_id']=$mdata->threat_id;
				$data['vulnerability']=$mdata->vulnerability;
				$data['probability']=$mdata->probability;
				$data['impact']=$mdata->impact;
				$data['risk_value']=$mdata->risk_value;
				$data['controls']=$mdata->controls;

				$data['threat_label']=$riskmodel->arrThreat[$mdata->threat_id];
				$data['probability_label']=$riskmodel->arrProbability[$mdata->probability];
				$data['impact_label']=$riskmodel->arrImpact[$mdata->impact];


				$data['created_at']=date($date_format,$mdata->created_at);
				$data['created_by_label']=$mdata->createdbydata->first_name.' '.$mdata->createdbydata->last_name;
				
				$list[]=$data;
			}
		}

		return ['riskassessment'=>$list,'total'=>$totalCount];
    }

    public function actionGetlogdata(){

    	$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

		if($data){
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
	    	$gislogmodel = LibraryRiskAssessmentLog::find()->where(['library_risk_assessment_id'=>$data['data_id']])->all();
	    	$gis_list=[];
	    	if(count($gislogmodel)>0)
	    	{
	    		foreach($gislogmodel as $log){
	    			$data=array();
					$data['id']=$log->id;
					$data['library_risk_assessment_id']=$log->library_risk_assessment_id;
					$data['log_date']=date($date_format,strtotime($log->log_date));
					$data['target_date']=date($date_format,strtotime($log->target_date));

					$data['reason_id']=$log->reason_id;
					$data['reason_label'] = $log->arrReason[$log['reason_id']];

					$data['updated_id']=$log->updated_id;
					$data['updated_label'] = $log->arrUpdated[$log['updated_id']];

					$data['details']=$log->details;
					//$data['created_by']=$log->arrType[$gis->type];
					
					$gis_list[]=$data;
	    		}
		    	
			}
			$responsedata=array('status'=>1,'data' => $gis_list);
		}
		return $responsedata;

	}
	
    public function actionStatuslist()
	{
		$modelObj = new LibraryRiskAssessment();
		$logmodel = new LibraryRiskAssessmentLog();
		return ['arrThreat'=>$modelObj->arrThreat, 'arrProbability'=>$modelObj->arrProbability, 'arrImpact'=>$modelObj->arrImpact,
				'arrUpdated'=> $logmodel->arrUpdated, 'arrReason'=> $logmodel->arrReason];
	}

	public function actionAddlogdata()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

		if($data){

			
			if(isset($data['id']))
			{
				$model = LibraryRiskAssessmentLog::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new LibraryRiskAssessmentLog();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new LibraryRiskAssessmentLog();
				$model->created_by = $userData['userid'];
			}

			 
			$model->library_risk_assessment_id= $data['data_id'];
			$model->log_date = date('Y-m-d',strtotime($data['log_date']));	
			$model->target_date = date('Y-m-d',strtotime($data['target_date']));	
			$model->updated_id = $data['updated_id'];	
			$model->reason_id = $data['reason_id'];	
			$model->details = $data['details'];	
			
			
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

    public function actionCreate()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		
		if($data){

			//$data =json_decode($datapost['formvalues'],true);
		

			if(isset($data['id']))
			{
				$model = LibraryRiskAssessment::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new LibraryRiskAssessment();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new LibraryRiskAssessment();
				$model->created_by = $userData['userid'];
			}

			
			$model->franchise_id = $data['franchise_id'];
			$model->threat_id = $data['threat_id'];		
			//$model->threat_id = date('Y-m-d',strtotime($data['received_date']));	
			$model->vulnerability = $data['vulnerability'];	
			$model->probability = $data['probability'];	
			$model->impact = $data['impact'];
			$model->controls = $data['controls'];
			

			$model->risk_value = $model->arrProbability[$data['probability']] * $model->arrImpact[$data['impact']];
			
			if($model->validate() && $model->save())
			{	
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Risk Assessment has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Risk Assessment created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}
	public function actionDelete(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		
		if($data){

			
			if(isset($data['id']))
			{
				$model = LibraryRiskAssessment::find()->where(['id' => $data['id']])->one();
				if($model !==null){
					/*$filename = $model->gis_file;
					$unlinkFile = $target_dir.$filename;
					if(file_exists($unlinkFile))
					{
						@unlink($unlinkFile);
					}
					*/

					LibraryRiskAssessmentLog::deleteAll(['library_risk_assessment_id' => $model->id]);
					$model->delete();
					$responsedata = array('status'=>1,'message'=>'Deleted successfully');
				}
			}
		}
		return $responsedata;
	}

	public function actionDeletelog(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

		if($data){

			
			if(isset($data['id']))
			{
				$model = LibraryRiskAssessmentLog::find()->where(['id' => $data['id']])->one();
				if($model !==null){
					$model->delete();
					$responsedata = array('status'=>1,'message'=>'Deleted successfully');
				}
			}
		}
		return $responsedata;
	}
	
	
	public function actionView()
    {
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$gismodel = new LibraryRiskAssessment();

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
			
			$logmodel = $model->librarylog;
			
			if(count($logmodel)>0)
			{
				$log_arr = array();
				foreach($logmodel as $val)
				{
					$log_arr = array();
					$log_arr['id'] = $val['id'];
					$log_arr['log_date'] = date($date_format,$val['log_date']);
					$log_arr['target_date'] = date($date_format,$val['target_date']);
					//$log_arr['reason_label'] = $val->arrUpdated[$val['reason_id']];
					//$log_arr['description'] = $val['description'];
					$log_arr[]=$log_arr;
				}
				$resultarr["logs"] = $gislog_arr;
			}
			
            return ['data'=>$resultarr];
        }

	}

	
    protected function findModel($id)
    {
        if (($model = LibraryRiskAssessment::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	
}
