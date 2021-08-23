<?php
namespace app\modules\library\controllers;

use Yii;
use app\modules\library\models\LibraryScopesGroups;
use app\modules\master\models\Standard;


use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * ScopesGroupsController implements the CRUD actions for Product model.
 */
class ScopesGroupsController extends \yii\rest\Controller
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

		$suppliermodel = new LibraryScopesGroups();
		$model = LibraryScopesGroups::find()->alias('t');
		$model->joinWith(['standard as standard','businesssector as businesssector','businesssectorgroup as businesssectorgroup']);
		
		if($resource_access != '1')
		{
			if($user_type== Yii::$app->params['user_type']['user'] && in_array('scopes_groups',$rules )){

			}else if($user_type==3)
			{
				$model = $model->andWhere('t.status="'.$suppliermodel->enumStatus['active'].'"');
			}	
		}
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];

				$riskarray = array_map('strtolower', $suppliermodel->arrRisk);
				$risksearch = array_search(strtolower($searchTerm),$riskarray);
				if($risksearch===false)
				{
					$risksearch='';
				}

				$scopearray = array_map('strtolower', $suppliermodel->arrScope);
				$scopesearch = array_search(strtolower($searchTerm),$scopearray);
				if($scopesearch===false)
				{
					$scopesearch='';
				}


				$model = $model->andFilterWhere([
					'or',
					['like', 't.description', $searchTerm],
					['risk'=> $risksearch],
					['scope'=> $scopesearch],
					['like','standard.name', $searchTerm],
					['like','businesssector.name', $searchTerm],
					['like','businesssectorgroup.group_code', $searchTerm],
					
					//['like', 'cty.name', $searchTerm],	
					//['like', 'date_format(FROM_UNIXTIME(t.created_at), \'%b %d, %Y\' )', $searchTerm],
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
		
		$groups_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $obj)
			{
				$data=array();
				$data['standard_id']=$obj->standard_id;
				$data['business_sector_id']=$obj->business_group_id;
				$data['business_sector_group_id']=$obj->business_group_code_id;
				$data['id']=$obj->id;
				$data['scope']=$obj->scope;
				$data['risk']=$obj->risk;
				$data['description']=$obj->description;
				$data['accreditation']=$obj->accrediation;
				$data['status']=$obj->status;
				$data['processes']=$obj->process;
				$data['rcontrols']=$obj->controls;
				
				$data['standard_id_label']= $obj->standard->name;
				$data['business_sector_id_label']= $obj->businesssector->name;
				$data['business_sector_group_id_label']= $obj->businesssectorgroup->group_code;
				$data['scope_label']=$obj->arrScope[$obj->scope];
				$data['risk_label']=$obj->arrRisk[$obj->risk];
				$data['accrediation_label']=$obj->arrAccreditation[$obj->accrediation];
				$data['status_label']=$obj->arrStatus[$obj->status];

				$data['created_at']=date($date_format,$obj->created_at);
				$data['created_by_label']=$obj->createdbydata->first_name.' '.$obj->createdbydata->last_name;
				$groups_list[]=$data;
			}
		}
		
		return ['scopesgroups'=>$groups_list,'total'=>$totalCount];
    }


	public function actionCreate()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		//$target_dir = Yii::$app->params['library_files']."supplier_files/"; 
		
		if($data){

			//$data =json_decode($datapost['formvalues'],true);
		

			if(isset($data['id']))
			{
				$model = LibraryScopesGroups::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new LibraryScopesGroups();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new LibraryScopesGroups();
				$model->created_by = $userData['userid'];
			}

			
			$model->standard_id = $data['standard_id'];
			$model->business_group_id = $data['business_sector_id'];	
			$model->business_group_code_id = $data['business_sector_group_id'];	
			$model->scope = $data['scope'];		
			$model->risk = $data['risk'];	
			$model->description = $data['description'];	
			$model->accrediation = $data['accreditation'];
			$model->process = $data['processes'];
			$model->controls = $data['rcontrols'];
			$model->status = $data['status'];	
			if($model->validate() && $model->save())
			{	
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Scopes & Groups has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Scopes & Groups has been created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}

	public function actionStatuslist()
	{
		$model = new LibraryScopesGroups();
		$stdmodel = Standard::find()->where(['status'=>0])->all();
		$standardlist = [];
		if(count($stdmodel)>0){
			foreach($stdmodel as $std){
				$standardlist[] = ['id'=> $std->id,'name'=> $std->name];
			}
		}
		return ['statuslist'=>$model->arrStatus, 'scopelist'=>$model->arrScope,'risklist'=>$model->arrRisk,'accrediationlist'=>$model->arrAccreditation, 'standards'=>$standardlist];
	}



	public function actionDeletedata()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$model = LibraryScopesGroups::deleteAll(['id' => $data['id']]);
			$responsedata=array('status'=>1,'message'=>'Deleted successfully');
		}
		return $this->asJson($responsedata);
	}
	
	
	
	
}
