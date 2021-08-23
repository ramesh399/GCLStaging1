<?php
namespace app\modules\audit\controllers;

use Yii;

use yii\web\NotFoundHttpException;

use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditPlan;
use app\modules\master\models\User;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;


use app\modules\offer\models\Offer;
use app\modules\offer\models\Invoice;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * RenewalAuditController implements the CRUD actions for Process model.
 */
class RenewalAuditController extends \yii\rest\Controller
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
			'authenticator' => ['class' => JwtHttpBearerAuth::class,
				'optional' => [
					'deleteaudit'					
				]
			]
		];        
    }
	
	
	public function actionIndex()
    {
    	return ['listauditplan'=>[],'total'=>0,'arrEnumStatus'=>[] ];
        $post = yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];

		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelInvoice = new Invoice();
		$modelOffer = new Offer();
		$modelAudit = new Audit();
		$modelAuditPlan = new AuditPlan();
		
		$connection = Yii::$app->getDb();
		$connection->createCommand("SET SESSION group_concat_max_len = 1000000;")->execute();
		$connection->createCommand("SET SQL_BIG_SELECTS = 1")->execute();
		
									

		$model = Offer::find()->where(['t.status'=>$modelOffer->enumStatus['finalized']])->alias('t');
		$model->innerJoinWith(['application as app']);
		$model->joinWith(['audit']);
		$model = $model->join('left join', 'tbl_audit_plan as plan','plan.audit_id =tbl_audit.id');
		$model = $model->join('left join', 'tbl_audit_plan_unit as plan_unit','plan.id =plan_unit.audit_plan_id');
		$model = $model->join('left join', 'tbl_audit_plan_unit_auditor as plan_unit_auditor','plan_unit.id =plan_unit_auditor.audit_plan_unit_id');
		$model = $model->join('left join', 'tbl_audit_plan_reviewer as plan_reviewer','plan_reviewer.audit_plan_id =plan.id ');
		$model = $model->join('left join', 'tbl_audit_plan_unit_standard as plan_standard','plan_standard.audit_plan_unit_id =plan_unit.id ');
			// tbl_audit_plan_unit_auditor, tbl_audit_plan_unit
		if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
		{
			$model = $model->andWhere(['plan_standard.standard_id'=> $post['standardFilter']]);
			/*sort($post['standardFilter']);
			$standardFilter = implode(',',$post['standardFilter']);
			$model = $model->having(['stdgp'=>$standardFilter]);
			*/
			//$model->andWhere(['country_id'=> $post['standardFilter']]);
		}
		if(isset($post['statusFilter']) && $post['statusFilter']!='')
		{
			if( $post['statusFilter']>'0'){
				$model = $model->andWhere(['tbl_audit.status'=> $post['statusFilter']]);
			}else if( $post['statusFilter']=='0'){
				$model = $model->andWhere(['tbl_audit.status'=> null]);
			}
			
		}

		if($resource_access != 1){
			if($user_type== 1 && ! in_array('invoice_management',$rules) && ! in_array('audit_management',$rules) ){
				return $responsedata;
			}else if($user_type==3 && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$model = $model->andWhere(' app.franchise_id="'.$userid.'" or app.created_by="'.$userid.'" ');
				//$model = $model->join('inner join', 'tbl_application as app','t.app_id=app.id and (app.franchise_id="'.$userid.'" or app.created_by="'.$userid.'")');
			}else if($user_type==2){
				$model = $model->andWhere(' app.customer_id="'.$userid.'" ');
				//$model = $model->join('inner join', 'tbl_application as app','t.app_id=app.id and app.customer_id="'.$userid.'"');	
			}
			/*
			else if($user_type==3 && $role!=0 && ! in_array('view_invoice',$rules) ){
				return $responsedata;
			}
			*/
		}
		
		if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1 
		&& !in_array('generate_audit_plan',$rules) 
		&& !in_array('audit_execution',$rules)
		&& !in_array('audit_review',$rules)
		&& !in_array('generate_audit_plan',$rules)){
			$model = $model->join('inner join', 'tbl_application as app','t.app_id=app.id and (app.franchise_id="'.$franchiseid.'")');
		}
		
		if(isset($post['type']) && $post['type']=='audit'){
			if($user_type== Yii::$app->params['user_type']['customer']){
				$model = $model->andWhere(' tbl_audit.status>="'.$modelAudit->arrEnumStatus['awaiting_for_customer_approval'].'" ');
			}





			if($user_type== Yii::$app->params['user_type']['user']  
				&& in_array('audit_review',$rules)
				&& in_array('audit_execution',$rules)
				&& in_array('generate_audit_plan',$rules)
			){


				$model = $model->andWhere('((plan.status="'.$modelAuditPlan->arrEnumStatus['waiting_for_review'].'" )
						OR (plan_reviewer.reviewer_id="'.$userid.'" and  (plan.status>="'.$modelAuditPlan->arrEnumStatus['review_in_progress'].'"
						or  plan.status="'.$modelAuditPlan->arrEnumStatus['reviewer_reinitiated'].'" )))

						or 

						((plan.application_lead_auditor="'.$userid.'" and tbl_audit.status>="'.$modelAudit->arrEnumStatus['review_in_process'].'" )
						OR (plan_unit_auditor.user_id="'.$userid.'" and  tbl_audit.status>="'.$modelAudit->arrEnumStatus['approved'].'"))


						or 

						(tbl_audit.status="'.$modelAudit->arrEnumStatus['open'].'" or tbl_audit.id is null or tbl_audit.created_by='.$userid.')
						or ( plan.application_lead_auditor="'.$userid.'" and tbl_audit.status>="'.$modelAudit->arrEnumStatus['review_in_process'].'" )

					');

			}else{

				//plan_reviewer
				if($user_type== Yii::$app->params['user_type']['user']  && in_array('audit_review',$rules)){
					$model = $model->andWhere('((plan.status="'.$modelAuditPlan->arrEnumStatus['waiting_for_review'].'" )
						OR (plan_reviewer.reviewer_id="'.$userid.'" and  (plan.status>="'.$modelAuditPlan->arrEnumStatus['review_in_progress'].'"
						or  plan.status="'.$modelAuditPlan->arrEnumStatus['reviewer_reinitiated'].'" )))
					');
				}


				if($user_type== Yii::$app->params['user_type']['user']  && in_array('audit_execution',$rules) && !in_array('generate_audit_plan',$rules) ){
					$model = $model->andWhere('((plan.application_lead_auditor="'.$userid.'" and tbl_audit.status>="'.$modelAudit->arrEnumStatus['review_in_process'].'" )
						OR (plan_unit_auditor.user_id="'.$userid.'" and  tbl_audit.status>="'.$modelAudit->arrEnumStatus['approved'].'"))
					');
				}
				if($user_type== Yii::$app->params['user_type']['user']  && in_array('generate_audit_plan',$rules) && !in_array('audit_execution',$rules) ){
					$model = $model->andWhere('(tbl_audit.status="'.$modelAudit->arrEnumStatus['open'].'" or tbl_audit.id is null or tbl_audit.created_by='.$userid.')');
				}
				if($user_type== Yii::$app->params['user_type']['user']  && in_array('generate_audit_plan',$rules) && in_array('audit_execution',$rules) ){
					$model = $model->andWhere('(tbl_audit.status="'.$modelAudit->arrEnumStatus['open'].'" or tbl_audit.id is null or tbl_audit.created_by='.$userid.')
						or ( plan.application_lead_auditor="'.$userid.'" and tbl_audit.status>="'.$modelAudit->arrEnumStatus['review_in_process'].'" )
					');
				}

			}







		}


		$model = $model->groupBy(['t.id']);
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];

				$model = $model->andFilterWhere([
					'or',
					['like', 't.offer_code', $searchTerm],	
					['like', 'app.company_name', $searchTerm],
					['like', 'app.first_name', $searchTerm],
					['like', 'app.last_name', $searchTerm],
					['like', 'app.telephone', $searchTerm],						
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
		
		$app_list=array();
		$model = $model->all();	
		if(count($model)>0)
		{
			foreach($model as $offer)
			{
				$data=array();
				
				$data['id']=($offer->audit)?$offer->audit->id:'';
				$data['audit_status']=($offer->audit)?$offer->audit->status:0;
				$data['audit_status_name']=($offer->audit && isset($offer->audit->arrStatus[$data['audit_status']]))?$offer->audit->arrStatus[$data['audit_status']]:'Open';
				//$data['audit_status_name']=$data['audit_status'];
				
				//$data['invoice_id']=$offer->id;
				$data['app_id']=$offer->app_id;
				$data['offer_id']=($offer)?$offer->id:'';
				$data['currency']=($offer)?$offer->offerlist->currency:'';
				$data['company_name']=($offer)?$offer->application->company_name:'';
				//$data['invoice_number']=$offer->invoice_number;
				//$data['total_payable_amount']=$offer->total_payable_amount;
				//$data['tax_amount']=$offer->tax_amount;				
				//$data['creator']=$offer->username->first_name.' '.$offer->username->last_name;
				//$data['payment_status_name']=($offer->payment_status!='' )?$modelInvoice->paymentStatus[$offer->payment_status]:'Payment Pending';
				//$data['created_at']=date('M d,Y h:i A',$offer->created_at);
				$data['created_at']=date($date_format,$offer->created_at);
				
				$arrAppStd=array();				
				if($offer)
				{
					$appobj = $offer->application;
					
					$data['application_unit_count']=count($appobj->applicationunit);
					$data['application_country']=$appobj->country->name;
					$data['application_city']=$appobj->city;
					
					$appStd = $appobj->applicationstandard;
					if(count($appStd)>0)
					{	
						foreach($appStd as $app_standard)
						{
							$arrAppStd[]=$app_standard->standard->code;
						}
					}
					
					$data['application_standard']=implode(', ',$arrAppStd);
				}			
				
				$app_list[]=$data;
			}
		}
		
		$audit = new Audit;
		return ['listauditplan'=>$app_list,'total'=>$totalCount,'arrEnumStatus'=>$audit->arrEnumStatus];
	}
	
}