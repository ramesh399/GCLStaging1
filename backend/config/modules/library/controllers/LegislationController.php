<?php
namespace app\modules\library\controllers;

use Yii;
use app\modules\library\models\LibraryLegislation;
use app\modules\library\models\LibraryLegislationStandard;
use app\modules\library\models\LibraryLegislationLog;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * LegislationController implements the CRUD actions for Product model.
 */
class LegislationController extends \yii\rest\Controller
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
				
		$legismodel = new LibraryLegislation();
		$model = LibraryLegislation::find()->select('createduser.first_name as first_name, createduser.last_name as last_name, t.id,t.title,t.description,t.country_id,t.update_method_id,t.created_at, group_concat(lstandard.standard_id ORDER BY lstandard.standard_id asc) as stdgp')->alias('t');
		$model = $model->joinWith(['createduser as createduser']);
		$model = $model->innerJoinWith(['legislationstandard as lstandard']);
		$model = $model->joinWith(['country as cty']);
		if($resource_access != '1')
		{

			if($user_type==3 && $resource_access==5){
				//$model->franchise_id = $franchiseid;
				$model = $model->andWhere('t.franchise_id="'.$franchiseid.'"');
			}else if($user_type==3 && $is_headquarters!=1){
				//$model->franchise_id = $userid;
				$model = $model->andWhere('t.franchise_id="'.$userid.'"');
			}else if($user_type== Yii::$app->params['user_type']['user'] && in_array('legislations',$rules ) && $is_headquarters!=1){
				//$model = $model->andWhere('t.franchise_id="'.$franchiseid.'"');
			}else if($is_headquarters!=1){
				$model = $model->andWhere('t.created_by=0');
			}
			
			/*
			if($user_type==3 && $is_headquarters!=1)
			{
				$model = $model->andWhere('t.created_by="'.$userid.'"');
			}else{
				$model = $model->andWhere('t.created_by=0');
			}
			*/
		}
		
		$model = $model->groupBy(['t.id']);
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			if(isset($post['countryFilter']) && $post['countryFilter']>='0')
			{
				$model->andWhere(['t.country_id'=> $post['countryFilter']]);
			}

			if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
			{
				$model->andWhere(['lstandard.standard_id'=> $post['standardFilter']]);
				sort($post['standardFilter']);
				$standardFilter = implode(',',$post['standardFilter']);
				$model = $model->having(['stdgp'=>$standardFilter]);
				//$model->andWhere(['country_id'=> $post['standardFilter']]);
			}
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$searchTerm = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $searchTerm);
			
				$methodarray = array_map('strtolower', $legismodel->arrMethod);
				$methodsearch = array_search(strtolower($searchTerm),$methodarray);
				if($methodsearch===false)
				{
					$methodsearch = '';
				}

				$model = $model->andFilterWhere([
					'or',
					['like', 'title', $searchTerm],
					['like', 'cty.name', $searchTerm],
					['like', 'date_format(FROM_UNIXTIME(t.`created_at` ), \'%b %d, %Y\' )', $searchTerm],
					['update_method_id'=>$methodsearch]
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
				$model = $model->orderBy(['t.created_at' => SORT_DESC]);
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
				$data['title']=$question->title;
				$data['description']=$question->description;
				$data['country_id']=$question->country_id;
				//$data['country_id_label']=$question->franchise->usercompanyinfo?'OSP '.$question->franchise->usercompanyinfo->osp_number.' - '.$question->franchise->usercompanyinfo->osp_details:'';
				//$data['relevant_to_id']=$question->relevant_to_id;
				$data['country_label']=$question->country?$question->country->name:'';
				//$data['relevant_to_id']=$question->relevant_to_id;
				//$data['relevant_to_id_label'] = $legismodel->arrRelevant[$question->relevant_to_id];
				$data['update_method_id']=$question->update_method_id;
				$data['update_method_id_label']=$legismodel->arrMethod[$question->update_method_id];
				$data['status']=$question->status;
				$data['created_at']=date($date_format,$question->created_at);
				$data['created_by_label']=$question->first_name.' '.$question->last_name;
				
				$stddelmodel = LibraryLegislationStandard::find()->where(['library_legislation_id' => $question->id])->all();
				$namearr = [];
				if(count($stddelmodel)>0){
					foreach($stddelmodel as $std){
						$data['relevant_to_id'][]="$std->standard_id";
						$data['relevant_to_id_label'][]=$std->standard->name;
					}
				}

				


				$question_list[]=$data;
			}
		}

		return ['legislations'=>$question_list,'total'=>$totalCount];
    }

    public function actionCreate()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$franchiseid=$userData['franchiseid'];
			$is_headquarters =$userData['is_headquarters'];
			$resource_access=$userData['resource_access'];

			if(isset($data['id']))
			{
				$model = LibraryLegislation::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new LibraryLegislation();
				}else{
					$model->updated_by = $userData['userid'];
				}
				
				$stddelmodel = LibraryLegislationStandard::deleteAll(['library_legislation_id' => $data['id']]);
			}else
			{
				
				$model = new LibraryLegislation();
				$model->created_by = $userData['userid'];

				if($user_type==1 || ($user_type==3 && $resource_access==5)){
					$model->franchise_id = $franchiseid;
				}else{
					$model->franchise_id = $userid;
				}
			}


			$model->country_id = $data['country_id'];
			$title = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data['title']);
			$description = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data['description']);			
						
			$model->title = $title;
			$model->description = $description; 			
			
			$model->update_method_id = $data['update_method_id'];	
			
			
			
			
			if($model->validate() && $model->save())
			{
				if(isset($data['relevant_to_id']) && count($data['relevant_to_id'])>0){
					foreach($data['relevant_to_id'] as $stdid){
						$stdmodel = new LibraryLegislationStandard();
						$stdmodel->library_legislation_id = $model->id;
						$stdmodel->standard_id = $stdid;
						$stdmodel->save();
					}
				}
				$responsedata=array('status'=>1,'message'=>'Legislation has been updated successfully');	
			}
			
			
				   
		}
		return $this->asJson($responsedata);
	}

	
	public function actionStatuslist()
	{
		
		$logmodel = new LibraryLegislationLog();
		return ['arrStatus'=>$logmodel->arrStatus, 'arrChanged'=>$logmodel->arrChanged];
	}

	public function actionDeletelegislationdata()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$model = LibraryLegislation::deleteAll(['id' => $data['id']]);
			$stdmodel = LibraryLegislationStandard::deleteAll(['library_legislation_id' => $data['id']]);
			$legmodel = LibraryLegislationLog::deleteAll(['library_legislation_id' => $data['id']]);
			
			
			$responsedata=array('status'=>1,'message'=>'Deleted successfully');
		}
		return $this->asJson($responsedata);
	}

	
	public function actionView()
    {
		$data = Yii::$app->request->post();
		
        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$country_id=$model->country_id;
			$country_id_label=$model->country? $model->country->name:'';
			$resultarr["title"]=$model->title;
			$resultarr["country_id"]=$country_id;
			$resultarr["country_id_label"]=$country_id_label;
			$resultarr["description"]=$model->description;
			$resultarr["relevant_to_id"]=$model->relevant_to_id;
			$resultarr["update_method_id"]=$model->update_method_id;
			
			
            return ['data'=>$resultarr];
        }

	}
	
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
           	$model = LibraryLegislation::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Legislation has been activated successfully';
					}elseif($model->status==1){
						$msg='Legislation has been deactivated successfully';
					}elseif($model->status==2){
						$msg='Legislation has been deleted successfully';
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

    
	public function actionAddlogdata()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

		if($data){

			
			if(isset($data['id']))
			{
				$model = LibraryLegislationLog::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new LibraryLegislationLog();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new LibraryLegislationLog();
				$model->created_by = $userData['userid'];
			}

			 
			$model->library_legislation_id= $data['data_id'];
			$model->changed_id = $data['changed_id'];	
			$model->status = $data['status'];	
			$model->details = $data['details'];	
			
			
			if($model->validate() && $model->save())
			{
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Review has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Review created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}

	public function actionDeletelog(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

		if($data){

			
			if(isset($data['id']))
			{
				$model = LibraryLegislationLog::find()->where(['id' => $data['id']])->one();
				if($model !==null){
					$model->delete();
					$responsedata = array('status'=>1,'message'=>'Deleted successfully');
				}
			}
		}
		return $responsedata;
	}

	public function actionGetlogdata(){

    	$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

		if($data){
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
	    	$logmodel = LibraryLegislationLog::find()->where(['library_legislation_id'=>$data['data_id']])->all();
	    	$list=[];
	    	if(count($logmodel)>0)
	    	{
	    		foreach($logmodel as $log){
	    			$data=array();
					$data['id']=$log->id;
					$data['library_legislation_id']=$log->library_legislation_id;
					
					$data['changed_id']=$log->changed_id;
					$data['changed_label'] = $log->arrChanged[$log['changed_id']];

					$data['status']=$log->status;
					$data['status_label'] = $log->arrStatus[$log['status']];

					$data['details']=$log->details;

					$data['created_by']=$log->createduser?$log->createduser->first_name.' '.$log->createduser->last_name:'NA';
					$data['created_at']=date($date_format,$log->created_at);
					$data['updated_by']=$log->updateduser?$log->updateduser->first_name.' '.$log->updateduser->last_name:'NA';
					$data['updated_at']=date($date_format,$log->updated_at);
					//$data['created_by']=$log->arrType[$gis->type];
					
					$list[]=$data;
	    		}
		    	
			}
			$responsedata=array('status'=>1,'data' => $list);
		}
		return $responsedata;

	}
    protected function findModel($id)
    {
        if (($model = LibraryLegislation::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	
}
