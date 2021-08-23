<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\audit\models\AuditReportRaScopeHolder;
use app\modules\audit\models\AuditReportTypeOfRisk;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditRaScopeholderController implements the CRUD actions for Product model.
 */
class AuditRaScopeholderController extends \yii\rest\Controller
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
		
		$scopemodel = new AuditReportRaScopeHolder();
		$model = AuditReportRaScopeHolder::find()->alias('t')->where(['t.audit_id'=>$post['audit_id']]);
		$model->joinWith(['typeofrisk as risk']);

		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm']!='')
			{
				$searchTerm = $post['searchTerm'];
				$scopearray=array_map('strtolower', $scopemodel->arrAuditType);
				$scopesearch = array_search(strtolower($searchTerm),$scopearray);
				if($scopesearch===false)
				{
					$scopesearch='';
				}
				
				$model = $model->andFilterWhere([
					'or',
					['t.audit_type_id'=> $scopesearch],
					['like', 'date_format(FROM_UNIXTIME(t.`created_at` ), \'%b %d, %Y\' )', $searchTerm],
					['like', 'risk.name', $searchTerm],

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
		
		$scope_holder_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $value)
			{
				$data=array();
				$data['id']=$value->id;
				$data['audit_id']=$value->audit_id;
				$data['type_of_risk_id']=$value->type_of_risk_id;
				$data['type_of_risk_label']=$value->typeofrisk->name;
				$data['audit_type_id']=$value->audit_type_id;
				$data['audit_type_label']=$value->arrAuditType[$value->audit_type_id];
				$data['description_of_risk']=$value->description_of_risk;
				$data['potential_risks']=$value->potential_risks;
				$data['measures_for_risk_reduction']=$value->measures_for_risk_reduction;
				$data['frequency_of_risk']=$value->frequency_of_risk;
				$data['probability_rate']=$value->probability_rate;
				$data['responsible_person']=$value->responsible_person;
				$data['conformity']=$value->conformity;
				$data['auditor_comments']=$value->auditor_comments;
				$data['created_at']=date($date_format,$value->created_at);
				$data['created_by_label']=$value->createdbydata->first_name.' '.$value->createdbydata->last_name;
				$scope_holder_list[]=$data;
			}
		}

		return ['scope_holders'=>$scope_holder_list,'total'=>$totalCount];
    }


	public function actionCreate()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		
		if($data)
		{	
			if(isset($data['id']))
			{
				$model = AuditReportRaScopeHolder::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new AuditReportRaScopeHolder();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new AuditReportRaScopeHolder();
				$model->created_by = $userData['userid'];
			}

			
			$model->audit_id = $data['audit_id'];
			$model->type_of_risk_id = $data['type_of_risk_id'];
			$model->audit_type_id = $data['audit_type_id'];	
			$model->description_of_risk = $data['description_of_risk'];
			$model->potential_risks = $data['potential_risks'];
			$model->measures_for_risk_reduction = $data['measures_for_risk_reduction'];
			$model->frequency_of_risk = $data['frequency_of_risk'];
			$model->probability_rate = $data['probability_rate'];
			$model->responsible_person = $data['responsible_person'];
			$model->conformity = $data['conformity'];
			$model->auditor_comments = $data['auditor_comments'];
			
			
			if($model->validate() && $model->save())
			{	
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'RA Scope Holder has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'RA Scope Holder has been created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}


	public function actionOptionlist()
	{
		$modelObj = new AuditReportRaScopeHolder();
		$riskmodel = AuditReportTypeOfRisk::find()->select(['id','name'])->asArray()->all();
		return ['auditTypelist'=>$modelObj->arrAuditType,'conformitylist'=>$modelObj->arrConformity,'risklist'=>$riskmodel];
	}



	public function actionDeleteData()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$model = AuditReportRaScopeHolder::deleteAll(['id' => $data['id']]);
			$responsedata=array('status'=>1,'message'=>'Deleted successfully');
		}
		return $this->asJson($responsedata);
	}
	
	
	
	
	
	
}
