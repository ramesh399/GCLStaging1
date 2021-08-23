<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\audit\models\AuditReportEnvironment;
use app\modules\audit\models\AuditReportApplicableDetails;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditEnvironmentController implements the CRUD actions for Product model.
 */
class AuditEnvironmentController extends \yii\rest\Controller
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


		$unit_id = (isset($post['unit_id']) && $post['unit_id'] !='')? $post['unit_id']:'';

		
		$data = [];
		if($unit_id!=''){
			$modelenv = AuditReportEnvironment::find()->where(['unit_id'=>$unit_id ])->one();
		}		
		if($unit_id !='' && $modelenv!==null){
			$data['app_id']= $modelenv->app_id;
			$data['audit_id']= ($modelenv->audit_id=='' || $modelenv->audit_id==null) && isset($post['audit_id'])  ?$post['audit_id']:$modelenv->audit_id;
		}else{
			$data['app_id']= isset($post['app_id'])?$post['app_id']:'';
			$data['audit_id']= isset($post['audit_id'])?$post['audit_id']:'';
		}
		
		
		
		if(!Yii::$app->userrole->canViewAuditReport($data)){
			return false;
		}
		
		$Environmentmodel = new AuditReportEnvironment();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		
		$sufficient_access = 1;
		if($user_type=='2' || $user_type=='3'){
			$sufficient_access = 0;
		}
		if(!isset($post['audit_id']) || $post['audit_id']<=0){
			$sufficient_access = 0;
		}
		$model = AuditReportEnvironment::find();
		//->where(['audit_id'=>$post['audit_id']]);
		if(isset($post['audit_id']) && $post['audit_id']>0){
			//$model = $model->andWhere(['audit_id'=>$post['audit_id'] ]);
		}
		if(isset($post['unit_id']) && $post['unit_id']>0){
			$model = $model->andWhere(['unit_id'=>$post['unit_id']]);
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
					['like', 'year', $searchTerm],
					['like', 'total_production_output', $searchTerm],
					['like', 'date_format(FROM_UNIXTIME(`created_at` ), \'%b %d, %Y\' )', $searchTerm],
					['like', 'water_consumption', $searchTerm],

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
		
		$environment_list=array();
		$model = $model->all();		

		if(count($model)>0 && isset($post['unit_id']) && $post['unit_id']>0)
		{
			foreach($model as $value)
			{
				$data=array();
				$data['id']=$value->id;
				$data['audit_id']=$value->audit_id;
				if($value->audit_id !=''){
					$sufficient_access =1;
				}
				$data['year']=$value->year;
				$data['total_production_output']=$value->total_production_output;
				$data['total_water_supplied']=$value->total_water_supplied;
				$data['water_consumption']=$value->water_consumption;
				$data['electrical_energy_consumption']=$value->electrical_energy_consumption;
				$data['gas_consumption']=$value->gas_consumption;
				$data['oil_consumption']=$value->oil_consumption;
				$data['coal_consumption']=$value->coal_consumption;
				$data['fuelwood_consumption']=$value->fuelwood_consumption;
				$data['total_energy_consumption_converted_to']=$value->total_energy_consumption_converted_to;
				$data['total_energy_consumption']=$value->total_energy_consumption;
				$data['product_waste']=$value->product_waste;
				
				$data['cod_in_waste_water']=$value->cod_in_waste_water;
				$data['total_cod']=$value->total_cod;
				$data['cod_textile_output']=$value->cod_textile_output;
				$data['wastage_textile_output']=$value->wastage_textile_output;
				$data['total_waste']=$value->total_waste;
				//$data['comments']=$value->comments;
				$data['sufficient']=$value->sufficient?$value->sufficient:'';
				$data['sufficient_label'] = $value->sufficient?$Environmentmodel->arrSufficient[$value->sufficient]:'';
				
				//$data['total_solid_waste']=$value->total_solid_waste;
				$data['created_at']=date($date_format,$value->created_at);
				$data['created_by_label']=$value->createdbydata->first_name.' '.$value->createdbydata->last_name;
				$environment_list[]=$data;
			}
			
		}else{
			$totalCount = 0;
		}

		return ['environments'=>$environment_list,'total'=>$totalCount,'sufficient_access'=>$sufficient_access];
	}
	
	public function actionOptionlist()
    {
		$suppliermodel = new AuditReportEnvironment();
		return ['sufficientlist'=>$suppliermodel->arrSufficient];
	}


	public function actionCreate()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		
		if($data)
		{	
			if(!Yii::$app->userrole->canEditAuditReport($data)){
				return false;
			}
			if(isset($data['id']))
			{
				
				

				$model = AuditReportEnvironment::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new AuditReportEnvironment();
					$model->created_by = $userData['userid'];
					$model->unit_id = $data['unit_id'];
					$model->app_id = $data['app_id'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new AuditReportEnvironment();
				$model->created_by = $userData['userid'];
				$model->unit_id = $data['unit_id'];
				$model->app_id = $data['app_id'];
			}
			$audit_id = '';
			if(isset($data['audit_id']) && $data['audit_id']>0){
				$model->audit_id = $data['audit_id'];
				$audit_id = $data['audit_id'];
			}
			//$model->unit_id = $data['unit_id'];
			$model->year = $data['year'];
			$model->total_production_output = $data['total_production_output'];	
			$model->total_water_supplied = $data['total_water_supplied'];
			//$model->water_consumption = $data['total_water_supplied'] / $data['total_production_output'] * 1000;
			$model->water_consumption = $data['water_consumption'];
			$model->electrical_energy_consumption = $data['electrical_energy_consumption'];
			$model->gas_consumption = $data['gas_consumption'];
			$model->oil_consumption = $data['oil_consumption'];
			$model->coal_consumption = $data['coal_consumption'];
			$model->fuelwood_consumption = $data['fuelwood_consumption'];
			
			//$model->total_energy_consumption_converted_to = $data['electrical_energy_consumption'] + $data['gas_consumption'] * 10 + $data['oil_consumption'] * 10 + $data['coal_consumption'] * 8 + $data['fuelwood_consumption'] * 4;
			//$model->total_energy_consumption = $model->total_energy_consumption_converted_to / $data['total_production_output'];
			
			$model->total_energy_consumption_converted_to = $data['total_energy_consumption_converted_to'];
			$model->total_energy_consumption = $data['total_energy_consumption'];
			
			//$model->product_waste = $data['total_solid_waste'] / $data['total_production_output'];
			//$model->total_solid_waste = $data['total_solid_waste'];
						
			$model->cod_in_waste_water = $data['cod_in_waste_water'];
			
			$model->total_cod = $data['total_cod'];
			$model->cod_textile_output = $data['cod_textile_output'];
			$model->wastage_textile_output = $data['wastage_textile_output'];
			$model->total_waste = $data['total_waste'];	
			//$model->comments = $data['comments'];
			$model->sufficient = $data['sufficient'];

			if($model->validate() && $model->save())
			{	
				if(isset($data['audit_id']) && $data['audit_id']>0)
				{
					$app_id = isset($data['app_id'])?$data['app_id']:'';
					$arraydata = ['audit_id'=>$audit_id,'unit_id'=>$data['unit_id'],'report_name'=>$data['type'],'app_id'=>$data['app_id'] ];
					Yii::$app->globalfuns->UpdateApplicableDetails($arraydata);
				}
				else
				{
					$model = AuditReportApplicableDetails::find()->where(['app_id'=> $data['app_id']])->andWhere(['unit_id'=> $data['unit_id']])->andWhere(['report_name'=> $data['type']])->andWhere(['status'=> 2])->one();
					if($model !== null)
					{
						$model->status = 1;
						$model->comments = '';
						$model->save();
					}
				}
				
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Environment has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Environment has been created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}

	public function actionChangeSufficient()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{

			$model = AuditReportEnvironment::find()->where(['id' => $data['id']])->one();
			if($model !== null)
			{
				$data['app_id']= $model->app_id;
				if($model->audit_id !=''){
					$data['audit_id']= $model->audit_id;
				}else{
					$model->audit_id = isset($data['audit_id'])?$data['audit_id']:'';
				}
				if(!Yii::$app->userrole->canEditAuditReport($data,'sufficient')){
					return false;
				}

				$model->sufficient = $data['sufficient'];
				if($model->validate() && $model->save())
				{
					$responsedata=array('status'=>1,'message'=>'Sufficient Changed successfully');
				}
			}
		}		
		return $this->asJson($responsedata);
	}

	public function actionDeleteData()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$modelchk = AuditReportEnvironment::find()->where(['id' => $data['id']])->one();
			if($modelchk!== null){
				$data['app_id']= $modelchk->app_id;
				if($modelchk->audit_id !=''){
					$data['audit_id']= $modelchk->audit_id;
				}
				if(!Yii::$app->userrole->canEditAuditReport($data)){
					return false;
				}
				$model = AuditReportEnvironment::deleteAll(['id' => $data['id']]);
				$responsedata=array('status'=>1,'message'=>'Deleted successfully');
			}
		}
		return $this->asJson($responsedata);
	}
	
	
	
	
	
	
}
