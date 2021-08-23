<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\audit\models\AuditReportChemicalList;
use app\modules\audit\models\AuditReportChemicalListAuditorConformity;
use app\modules\audit\models\AuditReportApplicableDetails;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditChemicalListController implements the CRUD actions for Product model.
 */
class AuditChemicalListController extends \yii\rest\Controller
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
		$pdata = [];
		$pdata['audit_id'] = $post['audit_id'];
		$pdata['unit_id'] = $post['unit_id'];
		$pdata['checktype'] = 'unitwise';
		if(!Yii::$app->userrole->canViewAuditReport($pdata)){
			return false;
		}

		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		$chemicalmodel = new AuditReportChemicalList();
		$model = AuditReportChemicalList::find()->alias('t')->where(['t.audit_id'=>$post['audit_id'],'t.unit_id'=>$post['unit_id']]);
		$model->joinWith(['country as cty']);

		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm']!='')
			{
				$searchTerm = $post['searchTerm'];
				
				
				$model = $model->andFilterWhere([
					'or',
					['like', 't.trade_name', $searchTerm],
					['like', 't.suppier', $searchTerm],
					['like', 'date_format(FROM_UNIXTIME(t.`created_at` ), \'%b %d, %Y\' )', $searchTerm],
					['like', 'cty.name', $searchTerm],

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
		
		$chemical_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $value)
			{
				$data=array();
				$data['id']=$value->id;
				$data['audit_id']=$value->audit_id;
				$data['trade_name']=$value->trade_name;
				$data['suppier']=$value->suppier;
				$data['country_id']=$value->country_id;
				$data['country_id_label']=$value->country->name;
				$data['utilization']=$value->utilization;
				$data['proof']=$value->proof;
				$data['proof_label']=$chemicalmodel->arrProof[$value->proof];
				//$data['type_of_conformity']=$value->type_of_conformity;
				$data['validity_or_issue_date']=date($date_format,strtotime($value->validity_or_issue_date));
				//$data['msds_issued_date']=date($date_format,strtotime($value->msds_issued_date));
				$data['msds_available']=$value->msds_available;
				$data['msds_available_label']=$chemicalmodel->arrMSDSavailable[$value->msds_available];
				$data['conformity_auditor']=$value->conformity_auditor;
				$data['conformity_auditor_label']=$value->auditorconformity->name;
				$data['conformity_auditor_label_color']=$chemicalmodel->arrColor[$value->auditorconformity->id];				
				$data['created_at']=date($date_format,$value->created_at);
				$data['created_by_label']=$value->createdbydata->first_name.' '.$value->createdbydata->last_name;
				$data['comments']=$value->comments;
				$chemical_list[]=$data;
			}
		}

		return ['chemicals'=>$chemical_list,'total'=>$totalCount];
    }


	public function actionCreate()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$arraydata = [];
		
		$pdata = [];
		$pdata['audit_id'] = $data['audit_id'];
		$pdata['unit_id'] = $data['unit_id'];
		$pdata['checktype'] = 'unitwise';
		if(!Yii::$app->userrole->canEditAuditReport($pdata)){
			return false;
		}

		if($data)
		{	
			$arraydata = ['audit_id'=>$data['audit_id'],'unit_id'=>$data['unit_id'],'report_name'=>$data['type']];
			Yii::$app->globalfuns->UpdateApplicableDetails($arraydata);

			if(isset($data['id']))
			{
				$model = AuditReportChemicalList::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new AuditReportChemicalList();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new AuditReportChemicalList();
				$model->created_by = $userData['userid'];
			}

			
			$model->audit_id = $data['audit_id'];
			$model->unit_id = $data['unit_id'];
			$model->trade_name = $data['trade_name'];
			$model->suppier = $data['suppier'];	
			$model->country_id = $data['country_id'];
			$model->utilization = $data['utilization'];
			$model->proof = $data['proof'];
			//$model->type_of_conformity = $data['type_of_conformity'];
			$model->validity_or_issue_date = date('Y-m-d',strtotime($data['validity_or_issue_date']));
			$model->msds_available = $data['msds_available'];
			//$model->msds_issued_date = date('Y-m-d',strtotime($data['msds_issued_date']));
			$model->conformity_auditor = $data['conformity_auditor'];
			$model->comments = $data['comments'];

			
			
			
			if($model->validate() && $model->save())
			{	
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Chemical List has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Chemical List has been created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}


	public function actionOptionlist()
	{
		$modelObj = new AuditReportChemicalList();
		$conformity = AuditReportChemicalListAuditorConformity::find()->select(['id','name'])->asArray()->all();
		return ['msdslist'=>$modelObj->arrMSDSavailable,'prooflist'=>$modelObj->arrProof,'conformitylist'=>$conformity];
	}



	public function actionDeleteData()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$modelchk = AuditReportChemicalList::find()->where(['id' => $data['id']])->one();
			if($modelchk!== null){
				$data['audit_id']= $modelchk->audit_id;
				$data['unit_id'] = $modelchk->unit_id;
				$data['checktype'] = 'unitwise';
				if(!Yii::$app->userrole->canEditAuditReport($data)){
					return false;
				}
				$model = AuditReportChemicalList::deleteAll(['id' => $data['id']]);
				$responsedata=array('status'=>1,'message'=>'Deleted successfully');
			}
		}
		return $this->asJson($responsedata);
	}


	
	
	
	
	
	
	
}
