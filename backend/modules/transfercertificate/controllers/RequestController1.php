<?php
namespace app\modules\transfercertificate\controllers;

use Yii;
use app\modules\certificate\models\Certificate;
use app\modules\transfercertificate\models\Request;
use app\modules\transfercertificate\models\RequestProduct;
use app\modules\transfercertificate\models\RequestEvidence;
use app\modules\transfercertificate\models\RequestStandard;
use app\modules\transfercertificate\models\RequestReviewer;
use app\modules\transfercertificate\models\RequestFranchiseComment;
use app\modules\transfercertificate\models\RequestReviewerComment;
use app\modules\transfercertificate\models\TcRawMaterialUsedWeight;
use app\modules\transfercertificate\models\TcRequestIfoamStandard;
use app\modules\transfercertificate\models\RawMaterial;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationUnitStandard;
use app\modules\application\models\ApplicationProduct;
use app\modules\application\models\ApplicationUnitProduct;
use app\modules\application\models\ApplicationChangeAddress;
use app\modules\application\models\ApplicationProductStandard;

use app\modules\transfercertificate\models\Buyer;
use app\modules\transfercertificate\models\InspectionBody;
 
use app\modules\transfercertificate\models\TcRequestProductInputMaterial;
use app\modules\transfercertificate\models\RawMaterialProduct;

use app\modules\master\models\User;
use app\modules\master\models\StandardCombination;
use app\modules\master\models\Mandaycost;

use app\modules\invoice\models\InvoiceTax;
use app\modules\invoice\models\Invoice;
use app\modules\invoice\models\InvoiceDetails;
use app\modules\invoice\models\InvoiceDetailsStandard;
use app\modules\invoice\models\InvoiceStandard;
use app\modules\invoice\models\InvoiceTc;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * RequestController implements the CRUD actions for Product model.
 */
class RequestController extends \yii\rest\Controller
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
	
	public function appRelation($model)
	{
		$model = $model->innerJoinWith(['application as app']);				
	}
	
	public function appAddressRelation($model)
	{
		$model = $model->innerJoinWith(['applicationaddress as appaddress']);				
	}
	
	public function actionIndex()
    {
		$post = yii::$app->request->post();		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelObj = new Request();		
		if($post)
		{
			$userrole = Yii::$app->userrole;
			$userid=$userrole->user_id;				
			$user_type=$userrole->user_type;
			$role=$userrole->role;
			$rules=$userrole->rules;
			$franchiseid=$userrole->franchiseid;		
			$resource_access=$userrole->resource_access;
			$is_headquarters =$userrole->is_headquarters;
			$role_chkid=$userrole->role_chkid;	
			
			$model = Request::find()->alias('t');	
			if(isset($post['type'])  && $post['type'] =='1')
			{
				if(!Yii::$app->userrole->hasRights(['generate_tc_bill']) && !Yii::$app->userrole->hasRights(['view_tc_bill'])){
					return false;
				}
				$model = $model->andWhere(['t.status'=> $modelObj->arrEnumStatus['approved']])->andWhere(['t.invoice_status'=>'0']);
			}	

			if(isset($post['type']) && $post['type'] =='2')
			{
				if(!Yii::$app->userrole->hasRights(['view_tc_generated_bill'])){
					return false;
				}
				//$model = $model->andWhere(['t.status'=> $modelObj->arrEnumStatus['approved']])->andWhere(['t.invoice_status'=>[1,2,3]]);		
				$model = $model->andWhere(['t.status'=> $modelObj->arrEnumStatus['approved']])->andWhere(['!=','t.invoice_status','0']);		
			}
			
			if(isset($post['paymentStatusFilter'])  && $post['paymentStatusFilter']!='')
			{
				$model = $model->andWhere(['t.invoice_status'=> $post['paymentStatusFilter']]);				
			}
			
			if(isset($post['statusFilter'])  && $post['statusFilter']!='')
			{
				$model = $model->andWhere(['t.status'=> $post['statusFilter']]);				
			}

			if(isset($post['appFilter'])  && $post['appFilter']!='' && count($post['appFilter'])>0)
			{
				$model = $model->andWhere(['t.app_id'=> $post['appFilter']]);				
			}
			
			if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
			{
				$model = $model->join('inner join', 'tbl_tc_request_standard as request_standard','request_standard.tc_request_id =t.id');
				$model = $model->andWhere(['request_standard.standard_id'=> $post['standardFilter']]);			
			}
			$model = $model->groupBy(['t.id']);

			$appJoinWithStatus=false;
			if($resource_access != '1')
			{
				$appJoinWithStatus=true;
				$this->appRelation($model);
				
				if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1 ){					
					$model = $model->andWhere(' app.franchise_id="'.$franchiseid.'" ');
				}
				
				if($user_type== Yii::$app->params['user_type']['customer']){
					$model = $model->andWhere('app.customer_id="'.$userid.'"');
				}else if($user_type== Yii::$app->params['user_type']['user']   
						&& (in_array('add_tc_application',$rules) 
					 || in_array('edit_tc_application',$rules)
					 || in_array('delete_tc_application',$rules)  || in_array('view_tc_application',$rules)
					 || in_array('clone_tc_application',$rules)
					 ))
				{

				}else if($user_type== Yii::$app->params['user_type']['user'] && in_array('assign_as_oss_review_for_tc',$rules)){
					$model = $model->andWhere(' t.status>1 and app.franchise_id="'.$franchiseid.'" ');
				}else if($user_type== Yii::$app->params['user_type']['user'] && in_array('application_review',$rules)){
					/*
					
					&& (!in_array('add_tc_application',$rules) 
						 || !in_array('edit_tc_application',$rules)
						 || !in_array('delete_tc_application',$rules)  || !in_array('view_tc_application',$rules)
						 || !in_array('clone_tc_application',$rules)
						 || !in_array('assign_as_oss_review_for_tc',$rules)
						 */
					$model = $model->joinWith(['reviewer as reviewer']);	
					$model = $model->andWhere('(t.status= "'.$modelObj->arrEnumStatus['waiting_for_review'].'" or reviewer.user_id="'.$userid.'")');
				}else if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
					if($resource_access == '5'){
						$userid = $franchiseid;
					}
					$model = $model->andWhere('t.status>1 and app.franchise_id="'.$userid.'"');
				}
					
			}
			
			if(isset($post['franchiseFilter']) && is_array($post['franchiseFilter']) && count($post['franchiseFilter'])>0)
			{
				if(!$appJoinWithStatus)
				{
					$appJoinWithStatus=true;
					$this->appRelation($model);
				}
				$model = $model->andWhere(['app.franchise_id'=> $post['franchiseFilter']]);	
			}		

			if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
			{
				$page = ($post['page'] - 1)*$post['pageSize'];
				$pageSize = $post['pageSize']; 
				
				if(isset($post['searchTerm']) && $post['searchTerm'] !='')
				{
					$searchTerm = $post['searchTerm'];					
					
					// ---- Invoice Number Search Code Start Here ----
					$arrTCids=array();
					$connection = Yii::$app->getDb();	
					$connection->createCommand("SET SESSION group_concat_max_len = 1000000;")->execute();
					$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();							
					$command = $connection->createCommand('SELECT GROUP_CONCAT(t.tc_request_id) AS requestids FROM `tbl_tc_request_product` AS t where t.invoice_no like "%'.$searchTerm.'%"');
					$result = $command->queryOne();
					if($result !== false)
					{	
						$requestTCids=$result['requestids'];						
						$arrTCids=explode(",",$requestTCids);							
						//$model = $model->andWhere(['t.id'=> $arrTCids]);	
					}
					// ---- Invoice Number Search Code End Here ----
					
					$this->appAddressRelation($model);
					$model = $model->join('left join', 'tbl_tc_buyer as buyer','buyer.id=t.buyer_id and buyer.type=\'buyer\'');							
					//$model = $model->join('left join', 'tbl_tc_request_product as reqprd','reqprd.tc_request_id=t.id');					
					
					$model = $model->andFilterWhere([
						'or',						
						['like', 'appaddress.company_name', $searchTerm],
						['like', 'purchase_order_number', $searchTerm],
						//['like', 'reqprd.invoice_no', $searchTerm],
						['like', 'buyer.name', $searchTerm],
						['like', 't.tc_number', $searchTerm],	
						['like', 'CONCAT(t.id, \'TEMP\')', $searchTerm],						
						['t.id'=>$arrTCids]
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
			
			$list=array();
			$model = $model->all();		
			if(count($model)>0)
			{
				$usermodel = new User();
				foreach($model as $modelData)
				{	
					$data=array();
					$data['id']=$modelData->id;
					$data['app_id_label']=$modelData->applicationaddress?$modelData->applicationaddress->company_name:"";					
					$unitName = $modelData->applicationunit->name;
					if($modelData->applicationunit->unit_type==1)
					{
						$unitName = $modelData->applicationaddress?$modelData->applicationaddress->unit_name:"";
					}
					$data['unit_id_label']=$unitName;
					$data['buyer_id_label']=$modelData->buyer->name;
					//$data['consignee_id_label']=$modelData->consignee->name;

					$data['app_id']=$modelData->app_id;
					$data['unit_id']=$modelData->unit_id;
					$data['buyer_id']=$modelData->buyer_id;
					//$data['consignee_id']=$modelData->consignee_id;

					$data['tc_number']=($modelData->arrEnumStatus['approved']==$modelData->status ? $modelData->tc_number : 'TEMP'.$modelData->id);
					$data['tc_number_cds']=$modelData->tc_number_cds;

					$data['country_of_dispach']=$modelData->country_of_dispach;
					//$data['country_of_destination']=$modelData->country_of_destination;
					
					$data['no_of_product']=is_array($modelData->product)?count($modelData->product):0;
					
					$data['purchase_order_number']=$modelData->purchase_order_number;	
					$data['grand_total_net_weight']=$modelData->grand_total_net_weight;	
					$data['grand_total_used_weight']=$modelData->grand_total_used_weight;
					
					$data['total_net_weight']=$modelData->total_net_weight;
					
					$data['status']=$modelData->status;
					$data['status_label']=$modelData->arrStatus[$modelData->status];
					
					$invoiceOptionArray=$modelData->arrInvoiceOptions;
					if(isset($post['type']) && $post['type'] =='2')
					{
						$invoiceOptionArray=$modelData->arrInvoiceOptionsLabel;
					}
					
					$data['payment_status_label']=($modelData->invoice_status!='' && $modelData->invoice_status>0)?$invoiceOptionArray[$modelData->invoice_status]:'NA';
					
					$data['invoice_status']=$modelData->invoice_status;
					$data['created_at']=date($date_format,$modelData->created_at);				

					$showedit= $this->canEditTc($modelData);
					$showdelete= $this->canDeleteTc($modelData);
					$showcopy= $this->canCopyTc($modelData);

					$data['showedit']= $showedit;
					$data['showdelete']= $showdelete;
					$data['showcopy']= $showcopy;
					
					$data['oss_label'] = $usermodel->ossnumberdetail($modelData->application->franchise_id);
					
					$customeroffernumber = $modelData->application->customer->customer_number;
					$TransactionCertificateNo='';					
					$draftText='';
					if($modelData->status!=$modelData->arrEnumStatus['approved'])
					{
						$draftText='DRAFT ';
						$TransactionCertificateNo=$modelData->id;
					}else{
						$TransactionCertificateNo=$modelData->tc_number;
					}					
					$tcFileName = 'TRANSACTION_CERTIFICATE_'.$customeroffernumber.'_'.$TransactionCertificateNo.'.pdf';
					$data['tc_filename'] = $tcFileName;

					$showpdf=0;
					if($modelData->status >= $modelData->arrEnumStatus['draft'] &&  $modelData->status!=$modelData->arrEnumStatus['rejected'] ){
						$showpdf = 1;
					}
					$data['showpdf']= $showpdf;
			
					$standardIds = [];
					$standardLabels = [];
					$standardCodeLabels = [];
					if(count($modelData->standard)>0){
						foreach($modelData->standard as $reqstandard)
						{
							$standardIds[] =  $reqstandard->standard_id;
							$standardLabels[] =  $reqstandard->standard->name;
							$standardCodeLabels[] =  $reqstandard->standard->code;
						}
					}
					$data["standard_id"]=$standardIds;	
					$data["standard_id_label"]=implode(', ',$standardLabels);
					$data["standard_id_code_label"]=implode(', ',$standardCodeLabels);	
					//$data["certification_body_id_label"]=$modelData->certificationbody->name;
					
					$data['approved_date']='NA';
					if($modelData->currentreviewercmt)
					{
						$data['approved_date']=date($date_format,$modelData->currentreviewercmt->created_at);	
					}
					

					$list[]=$data;
				}
			}
		}
		return ['request'=>$list,'total'=>$totalCount];
	}

	public function actionChangeInvoiceStatus()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$post = yii::$app->request->post();	
		$data = $post['data'];	
		$modelObj = new Request();		
		if($data)
		{
			if(!Yii::$app->userrole->hasRights(['generate_tc_bill'])){
				return false;
			}
			
			$dataids = [];
			if(count($data)>0){
				foreach($data as $tclist){
					if($tclist['value'] == '3'){
						$dataids[] = $tclist['id'];
					}else{
						$Request = Request::find()->where(['id'=> $tclist['id']])->one();
						$Request->invoice_status=$tclist['value'];
						$Request->save();
					}
				}
			}
			//$dataids = array_column($data, 'id');
			$applicationInvoice = [];
			if(count($dataids)>0){
				$model = Request::find()->where(['id'=> $dataids])->all();
				$applicationIds = [];
				foreach($model as $dataval)
				{
					$app_id = $dataval->app_id;
					$franchise_id = $dataval->application->franchise_id;
					$customer_id = $dataval->application->customer_id;
					
					if(!in_array($app_id,$applicationIds)){
						$applicationInvoice[$app_id] = [
							'app_id' => $app_id,
							'franchise_id' => $franchise_id,
							'customer_id' => $customer_id,
							'franchise_amount' => 0,
							'customer_amount' => 0,
							'standards' => [],
							'tc_request_ids' => [],
							'tc_request_numbers' => [],
						];
						$applicationIds[] = $app_id;
					}
					//$appkey = array_search($app_id, array_column($applicationIds, 'app_id'));


					//
					$customerinvoiceamount = 0;
					$ossinvoiceamount = 0;
					$arrStd = [];
					$arrStdCodes = [];
					if(count($dataval->standard)>0){
						foreach($dataval->standard as $rstandard){
							$arrStd[] = $rstandard->standard_id;
							$arrStdCodes[] = $rstandard->standard->code;
						}						
					}
					sort($arrStd);
					
					$applicationInvoice[$app_id]['standards'] = $applicationInvoice[$app_id]['standards'] + $arrStd;
					$applicationInvoice[$app_id]['tc_request_ids'][] = $dataval->id;
					$applicationInvoice[$app_id]['tc_request_numbers'][] = $dataval->tc_number;
					//


					$customerinvoicetype='export'; //Default to export if there is no match between dispatch and consignee country
					$consignee_countries = [];
					$country_of_dispach = $dataval->country_of_dispach;
					$requestProducts = RequestProduct::find()->where(['tc_request_id'=>$dataval->id])->all();
					if(count($requestProducts)>0)
					{
						foreach($requestProducts as $requestProduct)
						{
							$consignee_countries[] = $requestProduct->consignee->country_id;
							//$prdConsigneeCountry = ($prdConsignee->country
						}
					}
					$unique_consignee_countries = array_unique($consignee_countries);
					if(count($unique_consignee_countries)>1){ 
						// If more than 1 country
						$customerinvoicetype = 'export';
					}else if(count($unique_consignee_countries)==1 && in_array($country_of_dispach,$unique_consignee_countries)){ 
						// If single country with dispatch and consignee country are same
						$customerinvoicetype = 'domestic';
					}

					$connection = Yii::$app->getDb();	
					$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();							
					$command = $connection->createCommand("SELECT comb.*,GROUP_CONCAT(combstd.standard_id ORDER BY combstd.standard_id ASC ) AS standardids FROM `tbl_certificate_tc_royalty_fee` AS comb INNER JOIN `tbl_certificate_tc_royalty_fee_cs` AS combstd ON comb.id=combstd.certificate_tc_royalty_fee_id WHERE comb.franchise_id='".$franchise_id."' and status=0 GROUP BY comb.id HAVING standardids ='".implode(',',$arrStd)."'");
					$result = $command->queryOne();
					if($result !== false)
					{
						if(count($dataval->productgroup)>1){
							//$applicationInvoice[$app_id]['customer_amount'] += $result['multiple_invoice_fee_for_oss_to_customer'];
							$applicationInvoice[$app_id]['customer_amount'] += $result['multiple_'.$customerinvoicetype.'_invoice_fee_for_oss_to_customer'];
							$applicationInvoice[$app_id]['franchise_amount'] += $result['multiple_invoice_fee_for_hq_to_oss'];
						}else{
							//$applicationInvoice[$app_id]['customer_amount'] += $result['single_invoice_fee_for_oss_to_customer'];
							$applicationInvoice[$app_id]['customer_amount'] += $result['single_'.$customerinvoicetype.'_invoice_fee_for_oss_to_customer'];
							$applicationInvoice[$app_id]['franchise_amount'] += $result['single_invoice_fee_for_hq_to_oss'];
						}
					}

					
					
					//$model = Request::find()->Where(['id'=> $dataval['id']])->one();
					//if($model !== null)
					//{
						//$dataval->invoice_status=$dataval['value'];
						//$dataval->save();
					//}
				}
				$this->generateInvoice($applicationInvoice,$applicationIds,'customer'); // for customer
				$this->generateInvoice($applicationInvoice,$applicationIds,'franchise'); // for franchise
				if(count($dataids)>0){
					foreach($dataids as $updateid){
						$updateRequest = Request::find()->where(['id'=>$updateid])->one();
						if($updateRequest!==null){
							$updateRequest->invoice_status = $updateRequest->arrEnumInvoiceOptions['to_bill'];
							$updateRequest->save();
						}
					}
				}
				//Request::find()->updateAll(array('invoice_status' => 3), '  = 1 AND status = 0');
			}
			
			$responsedata=array('status'=>1,'message'=>'Saved Successfully!');
			
		}
		return $this->asJson($responsedata);
	}

	private function generateInvoice($applicationInvoice,$applicationIds,$invoice_for){
		if(count($applicationInvoice)>0){
			foreach($applicationInvoice as $appid => $appinvoice){
				
				$ospid = $appinvoice['franchise_id'];
				$franchiseID = $appinvoice['franchise_id'];

				$franchise = User::find()->where(['id'=>$franchiseID])->one();
				$invoiceCount = 0;
				$connection = Yii::$app->getDb();	
				$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		

				$command = $connection->createCommand("SELECT COUNT(invoice.id) AS invoice_count FROM `tbl_invoice` AS invoice
				INNER JOIN `tbl_application` AS app ON app.id = invoice.app_id AND app.franchise_id='$ospid' 
				GROUP BY app.franchise_id");
				$result = $command->queryOne();
				if($result  !== false)
				{
					$invoiceCount = $result['invoice_count'];
				}

				$maxid = $invoiceCount+1;
				if(strlen($maxid)=='1')
				{
					$maxid = "0".$maxid;
				}
				$invoicecode = "SY-".$franchise->usercompanyinfo->osp_number."-".$maxid."/".date("Y");
										
				$invoicemodel=new Invoice();	
				$invoicemodel->app_id=$appinvoice['app_id'];
				$invoicemodel->invoice_number=$invoicecode;
				$invoicemodel->franchise_id=$ospid;
				$invoicemodel->customer_id=$appinvoice['customer_id'];
				
				//$offerlist->conversion_currency_code ? $offerlist->conversion_currency_code : $offerlist->currency;	

				//New Code oct 30,2020 starts
				$invoicemodel->currency_code= 'USD';
				//$invoicemodel->conversion_currency_code = 'USD';
				//$invoicemodel->conversion_currency = 'USD';
				//$invoicemodel->conversion_rate = '1';



				$model = Application::find()->where(['id'=> $appid])->one();

				$total_amount = 0;
				if($invoice_for == 'customer'){
					$franchisesamestate = 0;
					if($model->applicationaddress && $franchise->usercompanyinfo && $model->applicationaddress->state_id == $franchise->usercompanyinfo->company_state_id){
						$franchisesamestate = 1;
					}
					$total_amount = $appinvoice['customer_amount'];
					$invoicemodel->invoice_type=1;
					
				}else{
					$total_amount = $appinvoice['franchise_amount'];
					$invoicemodel->invoice_type=2;
					
				}
				//$invoicemodel->invoice_due = date('Y-m-d',strtotime("+1 month"));
				
				$totalmdctaxpercentArr=0;
				$mdctaxArr=array();
				$mdctaxpercentArr=array();
				if($model->applicationaddress){
					$appmdc = Mandaycost::find()->where(['country_id' => $model->applicationaddress->country_id])->one();
					if($appmdc!==null)
					{
						if($invoice_for == 'customer'){
							if($franchisesamestate){
								$mandaycosttaxobj = $appmdc->mandaycosttax;
							}else{
								$mandaycosttaxobj = $appmdc->mandaycosttaxotherstate;
							}
						}else{
							$appmandaycost=$appmdc->man_day_cost;
							$mandaycosttaxobj = $appmdc->mandaycosttax;
						}
						
						if(is_array($mandaycosttaxobj) && count($mandaycosttaxobj)>0)
						{
							foreach($mandaycosttaxobj as $val)
							{
								$mdctaxArr[]=$val->tax_name;
								$mdctaxpercentArr[]=$val->tax_percentage;						
							}
						}elseif($mandaycosttaxobj!==null && $mandaycosttaxobj){
							$mdctaxArr[]=$mandaycosttaxobj->tax_name;
							$mdctaxpercentArr[]=$mandaycosttaxobj->tax_percentage;	
						}
					}
	
					$totalmdctaxpercentArr=array_sum($mdctaxpercentArr);
					//$resultarr["offer_currency_code"]=$appmdc->currency_code;
					
					//$resultarr["tax_percentage"]=$mdctaxpercentArr;
				}
				
				$invoicemodel->conversion_total_fee = $total_amount;
				$invoicemodel->total_fee=$total_amount;
				$invoicemodel->discount= 0;
				$invoicemodel->invoice_from = 2;

				if($invoicemodel->save())
				{
					$invoiceID = $invoicemodel->id;
					$tax_percentage=0;
					$royalBasedTotalTaxAmount = 0;
					if(count($mdctaxArr)>0)
					{
						
						foreach($mdctaxArr as $indexkey=>$ofrT)
						{
							$tax_percentage=$mdctaxpercentArr[$indexkey];
							
							$royalBasedTaxAmount=0;
							$royalBasedTaxAmount=($total_amount*$tax_percentage/100);
							$royalBasedTotalTaxAmount = $royalBasedTotalTaxAmount+$royalBasedTaxAmount;	
							
							$invoiceListTax=new InvoiceTax();
							$invoiceListTax->invoice_id=$invoiceID;							
							$invoiceListTax->tax_name=$ofrT;	
							$invoiceListTax->tax_percentage=$tax_percentage;
							$invoiceListTax->amount=$royalBasedTaxAmount;							
							$invoiceListTax->save();
						}							
					}

					$grand_total = $total_amount + $royalBasedTotalTaxAmount;
					$invoicemodel->conversion_tax_amount = $royalBasedTotalTaxAmount;
					$invoicemodel->conversion_total_payable_amount = $grand_total;
					$invoicemodel->conversion_required_status= 1;
					$invoicemodel->grand_total_fee=$grand_total;
					$invoicemodel->tax_amount=$royalBasedTotalTaxAmount;
					$invoicemodel->tax_percentage=$totalmdctaxpercentArr;
					$invoicemodel->total_payable_amount=$grand_total;	
					$invoicemodel->no_of_tc = count($appinvoice['tc_request_ids']);
					$invoicemodel->save();
					
					//if($type==2)
					//{
						// ---- Store the Application Unit Standard in Invoice Code Start Here -------
												
						// ---- Store the Application Standard in Invoice Code End Here -------	
						
					//if($type==2)
					//{					
						$invoiceDetailsModel=new InvoiceDetails();
						$invoiceDetailsModel->invoice_id=$invoiceID;
						$invoiceDetailsModel->activity='TC Fees';
						$invoiceDetailsModel->description='TC Fees for '.implode(', ',$appinvoice['tc_request_numbers']);
						$invoiceDetailsModel->amount=$total_amount;					
						$invoiceDetailsModel->type='1';									
						$invoiceDetailsModel->entry_type=0;
						$invoiceDetailsModel->save();

						

						if(isset($appinvoice['standards']) && count($appinvoice['standards']) > 0){
							$appinvstandards = array_unique($appinvoice['standards']);
							foreach($appinvstandards as $tcstandardid)
							{
								$invoiceDetailsStdModel=new InvoiceDetailsStandard();
								$invoiceDetailsStdModel->invoice_detail_id=$invoiceDetailsModel->id;
								$invoiceDetailsStdModel->standard_id=$tcstandardid;
								$invoiceDetailsStdModel->save();
							}
						}

						if(isset($appinvoice['standards']) && count($appinvoice['standards']) > 0){
							$appinvstandards = array_unique($appinvoice['standards']);
							foreach($appinvstandards as $tcstandardid)
							{
								$InvoiceStandard=new InvoiceStandard();
								$InvoiceStandard->invoice_id=$invoiceID;
								$InvoiceStandard->standard_id=$tcstandardid;
								$InvoiceStandard->save();
							}
						}

						if(isset($appinvoice['tc_request_ids']) && count($appinvoice['tc_request_ids']) > 0){
							foreach($appinvoice['tc_request_ids'] as $tcindexkey => $tcrequestid)
							{
								$tc_number = isset($appinvoice['tc_request_numbers'][$tcindexkey])?$appinvoice['tc_request_numbers'][$tcindexkey]:'';
								//tc_request_numbers
								$InvoiceStandard=new InvoiceTc();
								$InvoiceStandard->invoice_id=$invoiceID;
								$InvoiceStandard->tc_request_id=$tcrequestid;
								$InvoiceStandard->tc_number=$tc_number;
								$InvoiceStandard->save();
							}
						}
						
						/*
						if(count($arrScopeHolderStandards)>0)
						{
							array_unique($arrScopeHolderStandards);
							foreach($arrScopeHolderStandards as $arrScopeHolderStd)
							{
								$invoiceDetailsStdModel=new InvoiceDetailsStandard();
								$invoiceDetailsStdModel->invoice_detail_id=$invoiceDetailsModel->id;
								$invoiceDetailsStdModel->standard_id=$arrScopeHolderStd;
								$invoiceDetailsStdModel->save();
							}
						}
						*/
					//}
					//}	
					// -------- Invoice to OSS Get the Royalty Fee based on the Standard Code End Here ----------
					 			
				}	
			}
		}
	}


	public function canCopyTc($modelData){
		$userrole = Yii::$app->userrole;		
		$userid=$userrole->user_id;				
		$user_type=$userrole->user_type;
		$role=$userrole->role;
		$rules=$userrole->rules;
		$franchiseid=$userrole->franchiseid;		
		$resource_access=$userrole->resource_access;
		$is_headquarters =$userrole->is_headquarters;
		$role_chkid=$userrole->role_chkid;
			
		$showcopy=0;
		if($modelData->status == $modelData->arrEnumStatus['rejected'] &&  ($user_type== 2 || $resource_access==1 || ($user_type== 1 &&  in_array('clone_tc_application',$rules)) )){
			if($resource_access==1){
				$showcopy = 1;
			}else if($user_type == 2){
				if($modelData->application->customer_id == $userid){
					$showcopy = 1;
				}	
			}else if($user_type == 1){
				if($is_headquarters !=1){
					if($modelData->application->franchise_id == $franchiseid){
						$showcopy = 1;
					}	
				}else{
					$showcopy = 1;
				}
			}
		}
		return $showcopy;
	}


	public function canDeleteTc($modelData){
		$userrole = Yii::$app->userrole;		
		$userid=$userrole->user_id;				
		$user_type=$userrole->user_type;
		$role=$userrole->role;
		$rules=$userrole->rules;
		$franchiseid=$userrole->franchiseid;		
		$resource_access=$userrole->resource_access;
		$is_headquarters =$userrole->is_headquarters;
		$role_chkid=$userrole->role_chkid;

		$showdelete = 0;
		if(($modelData->status == $modelData->arrEnumStatus['open'] || $modelData->status == $modelData->arrEnumStatus['draft'] || $modelData->status == $modelData->arrEnumStatus['rejected'] ) && ($user_type==2 || $resource_access==1 || ($user_type== 1 &&  in_array('delete_tc_application',$rules)) )){
			if($resource_access==1){
				$showdelete = 1;
			}else if($user_type == 2){
				if($modelData->application->customer_id == $userid){
					$showdelete = 1;
				}	
			}else if($user_type == 1){
				if($is_headquarters !=1){
					if($modelData->application->franchise_id == $franchiseid){
						$showedit = 1;
					}	
				}else{
					$showedit = 1;
				}
			}
		}
		return $showdelete;
	}

	public function canAddTc($app_id){
		$userrole = Yii::$app->userrole;
		$userid=$userrole->user_id;				
		$user_type=$userrole->user_type;
		$role=$userrole->role;
		$rules=$userrole->rules;
		$franchiseid=$userrole->franchiseid;		
		$resource_access=$userrole->resource_access;
		$is_headquarters =$userrole->is_headquarters;
		$role_chkid=$userrole->role_chkid;

		$adddata = 0;
		if($user_type==2 || $resource_access==1 || ($user_type== 1 &&  in_array('add_tc_application',$rules))){
			
			if($resource_access==1){
				$adddata = 1;
			}else{
				$Application = Application::find()->where(['id'=>$app_id])->one();
				if($Application !== null){
					if($user_type == 2){
						if($Application->customer_id == $userid){
							$adddata = 1;
						}	
					}else if($user_type == 1){
						if($is_headquarters !=1){
							if($Application->franchise_id == $franchiseid){
								$adddata = 1;
							}	
						}else{
							$adddata = 1;
						}
					}
				}
			}
			

		}
		return $adddata;
	}	

	public function canEditTc($modelData){		
		$userrole = Yii::$app->userrole;
		$userid=$userrole->user_id;				
		$user_type=$userrole->user_type;
		$role=$userrole->role;
		$rules=$userrole->rules;
		$franchiseid=$userrole->franchiseid;		
		$resource_access=$userrole->resource_access;
		$is_headquarters =$userrole->is_headquarters;
		$role_chkid=$userrole->role_chkid;		
		
		$showedit = 0;
		if(($modelData->status == $modelData->arrEnumStatus['open'] 
			|| $modelData->status == $modelData->arrEnumStatus['pending_with_customer'] 
			|| $modelData->status == $modelData->arrEnumStatus['draft'] )
			&& ($user_type==2 || $resource_access==1 || ($user_type== 1 &&  in_array('edit_tc_application',$rules)) )){
				if($resource_access==1){
					$showedit = 1;
				}else if($user_type == 2){
					if($modelData->application->customer_id == $userid){
						$showedit = 1;
					}	
				}else if($user_type == 1){
					if($is_headquarters == 1){
						$showedit = 1;
					}else if($franchiseid == $modelData->application->franchise_id){
						$showedit = 1;
					}
				}
		}
		if(($modelData->status == $modelData->arrEnumStatus['waiting_for_osp_review'] || $modelData->status == $modelData->arrEnumStatus['pending_with_osp']   ) && ($user_type==3 || $resource_access==1 || ($user_type== 1 &&  in_array('assign_as_oss_review_for_tc',$rules)) )){
			$showedit = 1;
			if($resource_access==1){
				$showedit = 1;
			}else{
				if($user_type ==3){
					if($resource_access ==5 && $modelData->application->franchise_id == $franchiseid){
						$showedit = 1;
					}else if($modelData->application->franchise_id==$userid)
					{
						$showedit = 1;
					}
				}else if($user_type == 1){
					if($is_headquarters !=1){
						if($modelData->application->franchise_id == $franchiseid){
							$showedit = 1;
						}	
					}else{
						$showedit = 1;
					}
				}
			}
			
		}
		if($modelData->status == $modelData->arrEnumStatus['review_in_process']){
			$Revieweruser_id = '';
			$RequestReviewer = RequestReviewer::find()->where(['tc_request_id'=>$modelData->id])->one();
			if($RequestReviewer!==null){
				$Revieweruser_id = $RequestReviewer->user_id;//4;
			}
			if($resource_access==1){
				$showedit = 1;
			}else if($user_type==1 && $Revieweruser_id == $userid){
				$showedit = 1;
			}

			
		}
		return $showedit;
	}
	public function actionGetAppdata()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userrole = Yii::$app->userrole;
		$userid=$userrole->user_id;				
		$user_type=$userrole->user_type;
		$role=$userrole->role;
		$rules=$userrole->rules;
		$franchiseid=$userrole->franchiseid;		
		$resource_access=$userrole->resource_access;
		$is_headquarters =$userrole->is_headquarters;
		$role_chkid=$userrole->role_chkid;	
		
		$arrPaymentStatus=array();
		$requestModel = new Request();
		$arrInvoiceOptionsLabel = $requestModel->arrInvoiceOptionsLabel;
		if(count($arrInvoiceOptionsLabel)>0)
		{
			foreach($arrInvoiceOptionsLabel as $keyInv=>$valInv)
			{
				$arrPaymentStatus[]=array('id'=>$keyInv,'name'=>$valInv);
			}
		}
		
		$apparr = Yii::$app->globalfuns->getAppList();
		$responsedata=array('status'=>1,'appdata'=>$apparr,'paymentStatus'=>$arrPaymentStatus);
		return $this->asJson($responsedata);
	}

	public function actionGetAppaddress()
	{
		$data = Yii::$app->request->post();
		if($data)
		{
			$model = ApplicationChangeAddress::find()->where(['current_app_id'=>$data['id']])->one();
			if($model!==null)
			{
				$appaddress = [];
				$appaddress['address'] = $model->address;
				$appaddress['city'] = $model->city;
				$appaddress['zipcode'] = $model->zipcode;
				$appaddress['country'] = $model->country->name;
				$appaddress['state'] = $model->state->name;
				return ['data'=>$appaddress];
			}			
		}	
	}

	public function actionGetUnitaddress()
	{
		$data = Yii::$app->request->post();		
		if($data)
		{
			$model = ApplicationUnit::find()->where(['id'=>$data['id']])->one();
			if($model!==null)
			{
				$appaddress = [];
				$appaddress['address'] = $model->address;
				$appaddress['city'] = $model->city;
				$appaddress['zipcode'] = $model->zipcode;
				$appaddress['country'] = $model->country->name;
				$appaddress['state'] = $model->state->name;
				return ['data'=>$appaddress];
			}
		}
	}

	public function actionGetBuyeraddress()
	{
		$data = Yii::$app->request->post();		
		if($data)
		{
			$model = Buyer::find()->where(['id'=>$data['id']])->one();
			if($model!==null)
			{
				$appaddress = [];
				$appaddress['address'] = $model->address;
				$appaddress['city'] = $model->city;
				$appaddress['zipcode'] = $model->zipcode;
				$appaddress['country'] = $model->country_id?$model->country->name:"";
				$appaddress['state'] = $model->state_id?$model->state->name:"";
				return ['data'=>$appaddress];
			}
		}
	}

	public function actionGetInspectionaddress()
	{
		$data = Yii::$app->request->post();		
		if($data)
		{
			$model = InspectionBody::find()->where(['id'=>$data['id']])->one();
			if($model!==null)
			{
				$appaddress = [];
				$appaddress['address'] = $model->description;
				return ['data'=>$appaddress];
			}
		}
	}

	public function actionGetAppunitdata()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');		
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$unitarr = Yii::$app->globalfuns->getAppunitdata($data);
			$responsedata=array('status'=>1,'unitdata'=>$unitarr);
		}
		return $this->asJson($responsedata);
	}

	public function actionGetAppstddata()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');		
		$data = Yii::$app->request->post();
		$standardarr = array();
		if ($data) 
		{	
			$unitarr = array();
			//$standardmodel = ApplicationStandard::find()->where(['app_id' => $data['id']])->all();
			$datas = ['unit_id'=> $data['id']];
			$standardarr = Yii::$app->globalfuns->getAppUnitStandards($datas);
			$responsedata=array('status'=>1,'stddata'=>$standardarr);
		}
		return $this->asJson($responsedata);
	}
	
	/*
	public function actionDownloadBlfile(){
		$data = Yii::$app->request->post();
		if($data && isset($data['id']))
		{
			$files = Request::find()->where(['id'=>$data['id']])->one();
			if($files!==null)
			{
				//if($data['filetype']=='gisfile'){
					$filename = $files->bl_copy;
				//}
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
				header('Access-Control-Max-Age: 1000');
				header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
				
				$filepath=Yii::$app->params['tc_files']."blcopy_files/".$filename;
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
			}	
		}	
		die;
	}
	*/

	public function actionDownloadevidencefile()
	{
		$data = Yii::$app->request->post();
		if($data)
		{
			$userrole = Yii::$app->userrole;
			$userid=$userrole->user_id;				
			$user_type=$userrole->user_type;
			$role=$userrole->role;
			$rules=$userrole->rules;
			$franchiseid=$userrole->franchiseid;		
			$resource_access=$userrole->resource_access;
			$is_headquarters =$userrole->is_headquarters;
			$role_chkid=$userrole->role_chkid;
			
			$filetype = $data['filetype'];
			if($filetype=='sales_invoice_with_packing_list'){
				$filetype = 'sales_invoice';
			}else if($filetype=='mass_balance_sheet'){
				$filetype = 'mass_balance';
			}
			
			$modelObj=new Request();

			$files = RequestEvidence::find()->alias('t')->where(['t.id'=>$data['id'],'t.evidence_type'=>$filetype ]);					
			if($resource_access != '1')
			{
				$files = $files->join('inner join', 'tbl_tc_request as req','req.id =t.tc_request_id');
				$files = $files->join('inner join', 'tbl_application as app','app.id =req.app_id');
			
				if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1 ){
					$files = $files->andWhere(' app.franchise_id="'.$franchiseid.'" ');
				}
				
				if($user_type== Yii::$app->params['user_type']['customer']){
					$files = $files->andWhere('app.customer_id="'.$userid.'"');
				}else if($user_type== Yii::$app->params['user_type']['user'] && (in_array('add_tc_application',$rules) 
					|| in_array('edit_tc_application',$rules)
					|| in_array('delete_tc_application',$rules)  || in_array('view_tc_application',$rules)
					|| in_array('clone_tc_application',$rules)
					)
				){
				}else if($user_type== Yii::$app->params['user_type']['user'] && in_array('assign_as_oss_review_for_tc',$rules)){
					$files = $files->andWhere(' req.status>1 and app.franchise_id="'.$franchiseid.'" ');
				}else if($user_type== Yii::$app->params['user_type']['user'] && in_array('application_review',$rules)){
					$files = $files->join('left join', 'tbl_tc_request_reviewer as reqreviewer','reqreviewer.tc_request_id =req.id');
					$files = $files->andWhere('(req.status= "'.$modelObj->arrEnumStatus['waiting_for_review'].'" or reqreviewer.user_id="'.$userid.'")');
					//$files = $files->andWhere('(reqreviewer.user_id="'.$userid.'")');
				}else if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
					if($resource_access == '5'){
						$userid = $franchiseid;
					}
					$files = $files->andWhere('req.status>1 and app.franchise_id="'.$userid.'"');
				}					
			}
			
			$files = $files->one();
			if($files!==null)
			{
				$filename = $files->evidence_file;
				
				/*
				if($data['filetype']=='sales_invoice_with_packing_list'){
					$filename = $files->sales_invoice_with_packing_list;
				}else if($data['filetype']=='transport_document'){
					$filename = $files->transport_document;
				}else if($data['filetype']=='mass_balance_sheet'){
					$filename = $files->mass_balance_sheet;
				}else if($data['filetype']=='test_report'){
					$filename = $files->test_report;
				}
				*/
				
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
				header('Access-Control-Max-Age: 1000');
				header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
				
				$filepath=Yii::$app->params['tc_files']."evidence_files/".$filename;
				if(file_exists($filepath)) 
				{
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
			}
		}	
		die;
	}
	
	public function actionDownloadFile()
    {
		$data = Yii::$app->request->post();
		if($data) 
		{
			$userrole = Yii::$app->userrole;
			$userid=$userrole->user_id;				
			$user_type=$userrole->user_type;
			$role=$userrole->role;
			$rules=$userrole->rules;
			$franchiseid=$userrole->franchiseid;		
			$resource_access=$userrole->resource_access;
			$is_headquarters =$userrole->is_headquarters;
			$role_chkid=$userrole->role_chkid;
			
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			$modelRequest = new Request();
										
			$html='';
			$model = Request::find()->alias('t')->where(['t.id' => $data['id']]);
			$model = $model->innerJoinWith(['application as app']);				
			if($resource_access != '1')
			{
				if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1 ){
					$model = $model->andWhere(' app.franchise_id="'.$franchiseid.'" ');
				}
				
				if($user_type== Yii::$app->params['user_type']['customer']){
					$model = $model->andWhere('app.customer_id="'.$userid.'"');
				}else if($user_type== Yii::$app->params['user_type']['user'] && (in_array('add_tc_application',$rules) 
					|| in_array('edit_tc_application',$rules)
					|| in_array('delete_tc_application',$rules)  || in_array('view_tc_application',$rules)
					|| in_array('clone_tc_application',$rules)
					)
				){

				}else if($user_type== Yii::$app->params['user_type']['user'] && in_array('assign_as_oss_review_for_tc',$rules)){
					$model = $model->andWhere(' app.franchise_id="'.$franchiseid.'" ');
				}else if($user_type== Yii::$app->params['user_type']['user'] && in_array('application_review',$rules)){
					$model = $model->joinWith(['reviewer as reviewer']);	
					//$model = $model->andWhere('(reviewer.user_id="'.$userid.'")');
					$model = $model->andWhere('(t.status= "'.$modelRequest->arrEnumStatus['waiting_for_review'].'" or reviewer.user_id="'.$userid.'")');
				}else if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
					if($resource_access == '5'){
						$userid = $franchiseid;
					}
					$model = $model->andWhere('app.franchise_id="'.$userid.'"');
				}					
			}			
			$model = $model->andWhere(['not in','t.status',array($modelRequest->arrEnumStatus['open'],$modelRequest->arrEnumStatus['rejected'])]);
			$model = $model->one();
			if($model !== null)
			{
				if($model->status==$modelRequest->arrEnumStatus['approved'])
				{
					$filename = $model->filename;
					header('Access-Control-Allow-Origin: *');
					header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
					header('Access-Control-Max-Age: 1000');
					header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

					
					$filepath=Yii::$app->params['tc_files']."tc/".$filename;
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
				}else{	
					$this->generateTC($data['id'],false);
				}	
			}
		}
	}	

	public function actionCheckstandardcombination()
	{
		$modelRequest = new Request();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			//$data = json_decode($datapost['formvalues'],true);
			
			$connection = Yii::$app->getDb();


			
			$standard_ids = $data['standard_id'];
			if(is_array($standard_ids) && count($standard_ids)>1)
			{
				$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();

				$StandardCombination = StandardCombination::find()->where(['status'=>0])->alias('t');
				$StandardCombination = $StandardCombination->join('inner join', 'tbl_standard_combination_standard as combination','t.id =combination.standard_combination_id');
				$StandardCombination = $StandardCombination->andWhere(['combination.standard_id'=>$standard_ids]);
				$StandardCombination = $StandardCombination->one();
				if($StandardCombination!==null){
					
					sort($standard_ids);

					$command = $connection->createCommand("select GROUP_CONCAT(combstd.standard_id order by combstd.standard_id asc ) as standardids from tbl_standard_combination as comb inner join tbl_standard_combination_standard as combstd on comb.id=combstd.standard_combination_id where combstd.standard_id in (".implode(',',$standard_ids).") GROUP BY comb.id HAVING standardids = '".implode(',',$standard_ids)."'");

					$result = $command->queryOne();
					if($result  === false)
					{
						return $responsedata=array('status'=>0,'message'=>["standard_id"=>["Standard Combination is not invalid"]]);
					}

					//return $responsedata=array('status'=>0,'message'=>'Found');
				}
			}
			$responsedata=array('status'=>1,'message'=>"Standard Combination is valid");
		}
		return $responsedata;
	}


    public function actionCreate()
	{
		$modelRequest = new Request();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$target_dir = Yii::$app->params['tc_files']."blcopy_files/"; 
		$datapost = Yii::$app->request->post();
		if ($datapost) 
		{	
			$userData = Yii::$app->userdata->getData();
			$data = json_decode($datapost['formvalues'],true);
			if ($data) 
			{
				if(!Yii::$app->userrole->isValidApplication($data['app_id']))
				{
					$responsedata=array('status'=>0,'message'=>'Application is not valid.');
					return $this->asJson($responsedata);
				}
				
				$editStatus=1;
				$existing_unitid = '';
				$existing_appid = '';
				$connection = Yii::$app->getDb();
				
				if($data['id']){
					$requestmodel = Request::find()->where(['id' => $data['id']])->one();
					$canedit = $this->canEditTc($requestmodel);
					if($canedit==0){
						return $responsedata;
					}
				}else{
					$canadd = $this->canAddTc($data['app_id']);
					if($canadd==0){
						return $responsedata;
					}
				}
				
				$standard_ids = $data['standard_id'];
				if(is_array($standard_ids) && count($standard_ids)>1)
				{
					$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();

					$StandardCombination = StandardCombination::find()->where(['status'=>0])->alias('t');
					$StandardCombination = $StandardCombination->join('inner join', 'tbl_standard_combination_standard as combination','t.id =combination.standard_combination_id');
					$StandardCombination = $StandardCombination->andWhere(['combination.standard_id'=>$standard_ids]);
					$StandardCombination = $StandardCombination->one();
					if($StandardCombination!==null){
						
						sort($standard_ids);

						$command = $connection->createCommand("select GROUP_CONCAT(combstd.standard_id order by combstd.standard_id asc ) as standardids from tbl_standard_combination as comb inner join tbl_standard_combination_standard as combstd on comb.id=combstd.standard_combination_id where combstd.standard_id in (".implode(',',$standard_ids).") GROUP BY comb.id HAVING standardids = '".implode(',',$standard_ids)."'");

						$result = $command->queryOne();
						if($result  === false)
						{
							return $responsedata=array('status'=>0,'message'=>["standard_id"=>["Standard Combination is not invalid"]]);
						}

						//return $responsedata=array('status'=>0,'message'=>'Found');
					}
				}
				
				

				//$data =json_decode($datapost['formvalues'],true);			
				if(isset($data['id']) && $data['id']>0)
				{
					$model = Request::find()->where(['id' => $data['id']])->one();
					if($model===null)
					{
						$model = new Request();
						$editStatus=0;
						$model->created_by = $userData['userid'];

					}else{
						$existing_unitid = $model->unit_id;
						$existing_appid = $model->app_id;
						$model->updated_by = $userData['userid'];	
					}
				}else{
					$editStatus=0;
					$model = new Request();	
					$model->created_by = $userData['userid'];
				}	
				
				$model->app_id = $data['app_id'];	
				$model->unit_id = $data['unit_id'];	
				$model->buyer_id = $data['buyer_id'];	
				//$model->consignee_id = $data['consignee_id'];
				$model->standard_id = $data['standard_id'];	
				//$model->purchase_order_number = $data['purchase_order_number'];	

				//$model->tc_number_temp = $data['tc_number_temp'];	
				//$model->tc_number_cds = $data['tc_number_cds'];	
				//$model->shipment_number = $data['shipment_number'];	
				//$model->seller_id = $data['seller_id'];	
				//$model->certification_body_id = $data['certification_body_id'];	
				$inspection_body_id = '';
				$country_of_dispach = '';

				$inspection_bodyModel = InspectionBody::find()->where(['type'=>'inspection','status'=>0])->one();
				if($inspection_bodyModel!==null){
					$inspection_body_id = $inspection_bodyModel->id;
				}
				$ApplicationUnitModel = ApplicationUnit::find()->where(['id'=>$data['unit_id']])->one();
				if($ApplicationUnitModel!== null){
					$country_of_dispach = $ApplicationUnitModel->country_id;
				}
				$model->inspection_body_id = $inspection_body_id;
				$model->country_of_dispach = $country_of_dispach;//$data['country_of_dispach'];
				//$model->country_of_destination = $data['country_of_destination'];			
				/*
				if(isset($_FILES['bl_copy']['name']))
				{
					$tmp_name = $_FILES["bl_copy"]["tmp_name"];
					$name = $_FILES["bl_copy"]["name"];
					$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
				}else{
					$filename = $data['bl_copy'];
				}
				$model->bl_copy = $filename;
				*/

				//$model->transport_id = $data['transport_id'];	
				//$model->visible_to_brand = $data['visible_to_brand'];	
				$model->usda_nop_compliant = $data['usda_nop_compliant'];	
				//$model->apeda_npop_compliant = $data['apeda_npop_compliant'];	
				$model->comments = $data['comments'];	
										
				if($model->validate() && $model->save())
				{	
					$manualID = $model->id;
					//$model->tc_number_temp = $manualID;
					//$model->save();
					$existingstandard = [];
					$RequestStandard = RequestStandard::find()->where(['tc_request_id' => $manualID])->all();
					if(count($RequestStandard)>0){
						foreach($RequestStandard as $rstandard){
							$existingstandard[] = $rstandard->standard_id;
						}
					}
					$diffresult=array_diff($existingstandard,$data['standard_id']);

					//if any standard is removed delete all
					if(count($diffresult)>0 || ($existing_unitid!='' && $existing_unitid!=$model->unit_id) || ($existing_appid!='' && $existing_appid!=$model->app_id) ){
						$TcRequestProductModel = RequestProduct::find()->where(['tc_request_id'=>$manualID])->all();
						if(count($TcRequestProductModel)>0)
						{
							foreach($TcRequestProductModel as $productModel){
								$this->deleteRequestProductData($productModel->id);
							}
						}
					}
					
					/*
					$TcRequestProductModel = RequestProduct::find()->where(['tc_request_id'=>$id])->all();
				 
					if(count($TcRequestProductModel)>0)
					{
						foreach($TcRequestProductModel as $productModel){
							$this->deleteRequestProductData($productModel->id);
						}
					}
					*/

					TcRequestIfoamStandard::deleteAll(['tc_request_id' => $manualID]);
					if(isset($data['ifoam_standard']) && is_array($data['ifoam_standard']) && count($data['ifoam_standard'])>0)
					{
						foreach ($data['ifoam_standard'] as $value)
						{ 
							$requeststd = new TcRequestIfoamStandard();
							$requeststd->tc_request_id = $manualID;
							$requeststd->ifoam_standard_id = $value;
							$requeststd->save();
						}
					}
					
					$RequestStdIds = [];
					RequestStandard::deleteAll(['tc_request_id' => $manualID]);
					if(is_array($data['standard_id']) && count($data['standard_id'])>0)
					{
						foreach ($data['standard_id'] as $value)
						{ 
							$requeststd = new RequestStandard();
							$requeststd->tc_request_id = $manualID;
							$requeststd->standard_id = $value;
							$requeststd->save();
							$RequestStdIds[] = $value;
						}
					}
					
					if($editStatus==0)
					{
						$usda_nop = ($model->usda_nop_compliant==1? "Yes" : "No" );

						$tc_std_code_array=array();
						if(count($model->standard)>0){
							foreach($model->standard as $reqstandard){
								$standardCode = $reqstandard->standard->code;
								$tc_std_code_array[]=$standardCode;
							}
						}
						
						$tc_std_code='';
						$tc_std_code=implode(", ",$tc_std_code_array);
						
						$standard_ids = $RequestStdIds;
						$standard_declaration_content = '';
						if(is_array($standard_ids) && count($standard_ids)>0)
						{
							sort($standard_ids);
							$command = $connection->createCommand("select comb.declaration_content as declaration_content,GROUP_CONCAT(combstd.standard_id order by combstd.standard_id asc ) as standardids  from tbl_standard_combination as comb inner join tbl_standard_combination_standard as combstd on comb.id=combstd.standard_combination_id where 1=1 GROUP BY comb.id HAVING standardids = '".implode(',',$standard_ids)."'");
							$result = $command->queryOne();
							if($result  !== false)
							{
								$standard_declaration_content = $result['declaration_content'];
							}
						}
						
						$model->declaration='This is to certify that, based on the relevant documentation provided by the seller named in box 3, (i) the Organic Cotton used for the product(s) as further detailed / referred to in box 10 and quantified in box 11, 12 and 13 has been produced in accordance with (an) Organic Farming Standard(s) which is/are recognized by GOTS, and (ii) the products have been processed in accordance with GOTS, Compliance with the standard is audited and monitored systematically under responsibility of the certification body named in box 1.';
						$model->additional_declaration='Certification of the organic material used for the products listed complies with USDA NOP rules - <b>'.$usda_nop.'</b> (relevant information for products marketed and sold in the US; obligatory information for any '.$tc_std_code.' TC)<br>Country of origin of organic fibres - Organic Cotton : Nil';
						$model->standard_declaration = $standard_declaration_content;
						$model->save();
					}
					
					$applicationCompanyName='';
					$applicationCompanyAddress='';
					$applicationCompanyUnitName='';
					$applicationCompanyUnitAddress='';
					
					//$app_change_address_id='';
					$applicationModelObject = $model->application->currentaddress;////$model->applicationaddress;
									
					$applicationCompanyName=$applicationModelObject->company_name ;
					$applicationCompanyAddress=$applicationModelObject->address ;
					//
					
					$applicationUnitModelObject = $model->applicationunit;
					if($applicationUnitModelObject->unit_type==1)
					{
						$applicationCompanyUnitName=$applicationModelObject->unit_name;
						$applicationCompanyUnitAddress=$applicationModelObject->unit_address;
					}else{
						$applicationCompanyUnitName=$model->applicationunit->name;
						$applicationCompanyUnitAddress=$model->applicationunit->address;
					}
					
					/*
					if($applicationModelObject!==null)
					{
						$applicationCompanyName=$applicationModelObject->company_name ;
						$applicationCompanyAddress=$applicationModelObject->address ;
						$app_change_address_id=$applicationModelObject->id;
						
						$applicationUnitModelObject = $model->applicationunit;
						if($applicationUnitModelObject->unit_type==1)
						{
							$applicationCompanyUnitName=$applicationCompanyName;
							$applicationCompanyUnitAddress=$applicationCompanyAddress;
						}else{
							$applicationCompanyUnitName=$model->applicationunit->name;
							$applicationCompanyUnitAddress=$model->applicationunit->address;
						}					
					}else{
						$applicationCompanyName=$model->application->company_name;
						$applicationCompanyAddress=$model->application->address;
						$applicationCompanyUnitName=$model->applicationunit->name;
						$applicationCompanyUnitAddress=$model->applicationunit->address;
					}
					*/
					
					$model->company_name=$applicationCompanyName;
					$model->unit_name=$applicationCompanyUnitName;

					$app_change_address_id=$applicationModelObject->id;
					$model->address_id=$app_change_address_id;
					$model->save();
									
					$userMessage = 'TC Application has been created successfully';
					if($editStatus==1)
					{
						$userMessage = 'TC Application has been updated successfully';
					}				
					$responsedata=array('status'=>1,'message'=>$userMessage,'id'=>$model->id);	
				}
			}
		}
		//$responsedata=array('message'=>print_r($model->getErrors()));	
		return $this->asJson($responsedata);
	}

	public function actionAddDeclaration()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			if(isset($data['id']) && $data['id']>0)
			{
				$userrole = Yii::$app->userrole;
				$userid=$userrole->user_id;				
				$user_type=$userrole->user_type;
				$role=$userrole->role;
				$rules=$userrole->rules;
				$franchiseid=$userrole->franchiseid;		
				$resource_access=$userrole->resource_access;
				$is_headquarters =$userrole->is_headquarters;
				$role_chkid=$userrole->role_chkid;
				
				$modelRequest=new Request();
				
				$arrStatusforDec=array($modelRequest->arrEnumStatus['waiting_for_osp_review'],$modelRequest->arrEnumStatus['pending_with_osp'],$modelRequest->arrEnumStatus['waiting_for_review'],$modelRequest->arrEnumStatus['review_in_process']);
				$model = Request::find()->alias('t')->where(['t.id' => $data['id'],'t.status'=>$arrStatusforDec]);
				//$model = $model->innerJoinWith(['application as app','reviewer as reviewer']);					
				$model = $model->one();
				if($model!==null)
				{	
					if(!Yii::$app->userrole->isValidApplication($model->app_id))
					{
						$responsedata=array('status'=>0,'message'=>'Application is not valid.');
						return $this->asJson($responsedata);
					}
					 
					if($resource_access != '1' && $resource_access != '2')
					{
						$tcStatus = $model->status;
						if($user_type==Yii::$app->params['user_type']['user'] && $is_headquarters!=1 )
						{
							if($model->application->franchise_id!=$franchiseid)
							{
								 
								return false;
							}								
						}
						
						if($user_type== Yii::$app->params['user_type']['user'] && in_array('assign_as_oss_review_for_tc',$rules)){
							$arrStatusforDec=array($modelRequest->arrEnumStatus['waiting_for_osp_review'],$modelRequest->arrEnumStatus['pending_with_osp']);
							if(!in_array($tcStatus,$arrStatusforDec))
							{
								 
								return false;
							}
						}elseif($user_type== Yii::$app->params['user_type']['user'] && in_array('application_review',$rules)){
							$arrStatusforDec=array($modelRequest->arrEnumStatus['waiting_for_review'],$modelRequest->arrEnumStatus['review_in_process']);
							if($model->reviewer->user_id!=$userid || !in_array($tcStatus,$arrStatusforDec))
							{
								 
								return false;
							}							
						}else if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
							if($resource_access == '5'){
								 
								$userid = $franchiseid;
							}
							
							$arrStatusforDec=array($modelRequest->arrEnumStatus['waiting_for_osp_review'],$modelRequest->arrEnumStatus['pending_with_osp'],$modelRequest->arrEnumStatus['review_in_process']);
							if($model->application->franchise_id!=$userid || !in_array($tcStatus,$arrStatusforDec))
							{	
								 
								return false;
							}						
						}					
					}
					if($model->status == $modelRequest->arrEnumStatus['waiting_for_osp_review'] || $model->status == $modelRequest->arrEnumStatus['pending_with_osp']){
						$model->oss_declaration_update_status = 1;
					}else if($model->status == $modelRequest->arrEnumStatus['review_in_process']){
						$model->reviewer_declaration_update_status = 1;
					}

					$model->declaration = $data['declaration'];
					$model->additional_declaration = $data['additional_declaration'];
					$model->standard_declaration = $data['standard_declaration'];					
					if($model->save())
					{
						$responsedata=array('status'=>1,'message'=>'Declaration has been updated successfully');
					}
				}
			}
		}
		return $this->asJson($responsedata);
	}
	
	public function actionView()
    {
		$datapost = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelObj = new Request();
		$ospreviewmodal = new RequestFranchiseComment();
		$reviewerreviewmodal = new RequestReviewerComment();
        $model = $this->findModel($datapost['id']);
        if ($model !== null)
		{
			$data = array();
			$data["id"]=$model->id;
			$data["app_id"]=$model->app_id;			
			$data["unit_id"]=$model->unit_id;
			$data["request_status"]=$model->status;
			$data["request_status_label"]=$model->arrStatus[$model->status];

			//$data["tc_number_temp"]=$model->tc_number_temp;
			$data["tc_number_cds"]=$model->tc_number_cds;
			$data['tc_number']=$model->arrEnumStatus['approved']==$model->status?$model->tc_number:'TEMP'.$model->tc_number;
			
			$applicationCompanyName='';
			$applicationCompanyAddress='';
			$applicationCompanyUnitName='';
			$applicationCompanyUnitAddress='';
			
			$applicationModelObject = $model->applicationaddress;
			$applicationCompanyName=$applicationModelObject->company_name ;
			$applicationCompanyAddress=$applicationModelObject->address.', '.$applicationModelObject->city.' - '.$applicationModelObject->zipcode;
			
			$applicationUnitModelObject = $model->applicationunit;
			if($applicationUnitModelObject->unit_type==1)
			{
				$applicationCompanyUnitName=$applicationModelObject->unit_name;
				$applicationCompanyUnitAddress=$applicationModelObject->unit_address.', '.$applicationModelObject->city.' - '.$applicationModelObject->zipcode;
			}else{
				$applicationCompanyUnitName=$model->applicationunit->name;
				$applicationCompanyUnitAddress=$model->applicationunit->address.', '.$model->applicationunit->city.' - '.$model->applicationunit->zipcode;
			}	
			
			/*
			$applicationModelObject = $model->applicationaddress;
			if($applicationModelObject!==null)
			{
				$applicationCompanyName=$applicationModelObject->company_name ;
				$applicationCompanyAddress=$applicationModelObject->address ;
				
				$applicationUnitModelObject = $model->applicationunit;
				if($applicationUnitModelObject->unit_type==1)
				{
					$applicationCompanyUnitName=$applicationCompanyName;
					$applicationCompanyUnitAddress=$applicationCompanyAddress;
				}else{
					$applicationCompanyUnitName=$model->applicationunit->name;
					$applicationCompanyUnitAddress=$model->applicationunit->address;
				}					
			}else{
				$applicationCompanyName=$model->application->company_name;
				$applicationCompanyAddress=$model->application->address;
				$applicationCompanyUnitName=$model->applicationunit->name;
				$applicationCompanyUnitAddress=$model->applicationunit->address;
			}
			*/
			
			$data["app_id_label"]=$applicationCompanyName;
			$data["app_address"]=$applicationCompanyAddress;			
			$data["unit_id_label"]=$applicationCompanyUnitName;
			$data["unit_address"]=$applicationCompanyUnitAddress;		
			
			$data["buyer_id"]=$model->buyer_id;
			$data["buyer_id_label"]=$model->buyer->name;
			$data["buyer_address"]=$model->buyer->address;
			$data["buyer_license_number"]=$model->buyer->client_number;

			$data["declaration"]=$model->declaration;
			$data["additional_declaration"]=$model->additional_declaration;
			$data["standard_declaration"]=$model->standard_declaration;
			
			$data["overall_input_status"]=$model->overall_input_status;

			$data["oss_declaration_update_status"]=$model->oss_declaration_update_status;
			$data["reviewer_declaration_update_status"]=$model->reviewer_declaration_update_status;
			/*
			$data["seller_id"]=$model->seller_id;
			$data["seller_id_label"]=$model->seller->name;
			$data["seller_address"]=$model->seller->address;
			$data["seller_license_number"]=$model->seller->client_number;
			*/

			//$data["transport_id"]=$model->transport_id;
			//$data["transport_id_label"]=$model->transport->name;
			
			//$data["consignee_id"]=$model->consignee_id;
			//$data["consignee_id_label"]=$model->consignee->name;
			//$data["consignee_address"]=$model->consignee->address;
			//$data["consignee_license_number"]=$model->consignee->client_number;

			//$data["standard_id"]=$model->standard_id;
			//$data["standard_id_label"]=implode(', ',$model->applicationstandard->standard->name);
			//$data["purchase_order_number"]=$model->purchase_order_number;
			//$data["apeda_npop_compliant_label"]=($model->apeda_npop_compliant==1)?"Yes":"No";	
			//$data["apeda_npop_compliant"]=$model->apeda_npop_compliant;
			$data["comments"]=$model->comments;
			$data["visible_to_brand"]=$model->visible_to_brand;
			$data["usda_nop_compliant"]=$model->usda_nop_compliant;
			
			$data["visible_to_brand_label"]=($model->visible_to_brand==1)?"Yes":"No";
			$data["usda_nop_compliant_label"]=($model->usda_nop_compliant==1)?"Yes":"No";
			
			//$data["shipment_number"]=$model->shipment_number;

			$data["country_of_dispach"]=$model->country_of_dispach;
			//$data["country_of_destination"]=$model->country_of_destination;

			$data["country_of_dispach_label"]=$model->dispatchcountry?$model->dispatchcountry->name:'';
			//$data["country_of_destination_label"]=$model->destinationcountry?$model->destinationcountry->name:'';
			
			//$data["bl_copy"]=$model->bl_copy;
			
			$data["inspection_body_id"]=$model->inspection_body_id;
			$data["inspection_body_id_label"]=$model->inspectionbody?$model->inspectionbody->name:'';
			$data["inspection_body_address"]=$model->inspectionbody?$model->inspectionbody->description:'';

			//$data["certification_body_address"]=$model->certificationbody->description;
			//$data["certification_body_id"]=$model->certification_body_id;
			//$data["certification_body_id_label"]=$model->certificationbody->name;
			$data['reviewer_id'] =$model->reviewer?$model->reviewer->user_id:'';

			$standardIds = [];
			$standardLabels = [];
			if(count($model->standard)>0){
				foreach($model->standard as $reqstandard){
					$standardIds[] =  $reqstandard->standard_id;
					$standardLabels[] =  $reqstandard->standard->name;
					$standardCodeLabels[] =  $reqstandard->standard->code;

					$standardCodeLabelsCheck[] =  strtolower($reqstandard->standard->code);
				}
			}
			
			$data["standard_id"]=$standardIds;	
			$data["standard_id_label"]=implode(', ',$standardLabels);
			$data["standard_id_code_label"]=implode(', ',$standardCodeLabels);	

			$data["show_additional_declaration"]=0;
			if(in_array('gots',$standardCodeLabelsCheck) || in_array('ocs',$standardCodeLabelsCheck))
			{
				$data["show_additional_declaration"]=1;
			}
			
			$ifoamstandardIds = [];
			$ifoamstandardLabels = [];
			if(count($model->ifoamstandard)>0){
				foreach($model->ifoamstandard as $reqstandard){
					$ifoamstandardIds[] =  "".$reqstandard->ifoam_standard_id;
					$ifoamstandardLabels[] =  $reqstandard->ifoamStd->name;
				}
			}

			$data["ifoam_standard"]=$ifoamstandardIds;	
			$data["ifoam_standard_id_label"]=implode(",<br>",$ifoamstandardLabels);
			$data["ifoam_standard_id_label_list"]=$ifoamstandardLabels;
			
			//$data['purchase_order_number']=$model->purchase_order_number;	
			$data['grand_total_net_weight']=$model->grand_total_net_weight;	
			$data['grand_total_used_weight']=$model->grand_total_used_weight;	
			
			$data['created_at']=date($date_format,$model->created_at);
			$data['created_by_label']= $model->username?$model->username->first_name.' '.$model->username->last_name:'';
			
			$customeroffernumber = $model->application->customer->customer_number;
			$TransactionCertificateNo='';					
			$draftText='';
			if($model->status!=$model->arrEnumStatus['approved'])
			{
				$draftText='DRAFT ';
				$TransactionCertificateNo=$model->id;
			}else{
				$TransactionCertificateNo=$model->tc_number;
			}					
			$tcFileName = 'TRANSACTION_CERTIFICATE_'.$customeroffernumber.'_'.$TransactionCertificateNo.'.pdf';
			$data['tc_filename'] = $tcFileName;
			
			$showedit= $this->canEditTc($model);
			$data["showedit"] = $showedit;
			
			$customeroffernumber = $model->application->customer->customer_number;
			$TransactionCertificateNo='';					
			$draftText='';
			if($model->status!=$model->arrEnumStatus['approved'])
			{
				$draftText='DRAFT ';
				$TransactionCertificateNo=$model->id;
			}else{
				$TransactionCertificateNo=$model->tc_number;
			}					
			$tcFileName = 'TRANSACTION_CERTIFICATE_'.$customeroffernumber.'_'.$TransactionCertificateNo.'.pdf';
			$data['tc_filename'] = $tcFileName;
			
			$reqdata = [];
			//$reqdata = $data;
			$reqdata['requestdata'] = $data;
			
			$reqdata['requestproduct'] = $this->gettcproduct($model->id);


			$evidencedetails = [];
			if(count($model->evidence)>0){
				foreach($model->evidence as $reqevidence){
					 
						//sales_invoice_with_packing_list
					$evidence_type = $reqevidence->evidence_type;
					if($evidence_type=='sales_invoice'){
						$evidence_type = 'sales_invoice_with_packing_list';
					}else if($evidence_type=='mass_balance'){
						$evidence_type = 'mass_balance_sheet';
					}
					$evidencedetails[$evidence_type][] =  [
						'id' => $reqevidence->id,
						'name'=>$reqevidence->evidence_file
						];
					 
				}
			}
			$reqdata['requestevidence'] = $evidencedetails;

			$reviewarr = [];
			$ospmodal = $model->franchisecmt;
			if(count($ospmodal)>0)
			{
				$ospcmts = [];
				foreach($ospmodal as $res)
				{
					$cmt = [];
					$cmt['status'] = $res->status;
					$cmt['status_label'] = $ospreviewmodal->arrStatusLabel[$res->status];
					$cmt['comment'] = $res->comment;
					$cmt['created_at'] = date($date_format,$res->created_at);
					$cmt['created_by'] = $res->createdbydata->first_name." ".$res->createdbydata->last_name;
					$ospcmts[] = $cmt;
				}
				$reviewarr['osp_reviews'] = $ospcmts;
			}


			$reviewermodal = $model->reviewercmt;
			if(count($reviewermodal)>0)
			{
				$reviewercmts = [];
				foreach($reviewermodal as $res)
				{
					$cmt = [];
					$cmt['status'] = $res->status;
					$cmt['status_label'] = $reviewerreviewmodal->arrStatusLabel[$res->status];
					$cmt['comment'] = $res->comment;
					$cmt['created_at'] = date($date_format,$res->created_at);
					$cmt['created_by'] = $res->createdbydata->first_name." ".$res->createdbydata->last_name;
					$reviewercmts[] = $cmt;
				}
				$reviewarr['reviewer_reviews'] = $reviewercmts;
			}

			$requestreviewermodal = $model->reviewer;
			if($requestreviewermodal!==null)
			{
				$reviewerdata = [];
				$reviewerdata['reviewer'] = $requestreviewermodal->user->first_name." ".$requestreviewermodal->user->last_name;
				$reviewerdata['assigned_date'] = date($date_format,$requestreviewermodal->created_at);
				$reviewarr['reviewer'] = $reviewerdata;
			}
			
			

			

			$pdtdata= [];
			$pdtdata['unit_id'] = $model->unit_id;//236;//$model->unit_id;
			$pdtdata['standard_id'] = $standardIds;//[2,3];//$standardIds;//$model->standard_id;
			$productlist = $this->getapplicationproduct($pdtdata);

			$reqdata['productlist'] = $productlist;

			$reqdata['enumstatus'] = $modelObj->arrEnumStatus;
			
			//if( $data->)	
            return ['data'=>$reqdata,'reviewdetails'=>$reviewarr];
        }

	}

	/*
	public function actionGetproductdata()
    {
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');

        //if($data)
        if(1)
		{
			//$unit_id=$data['unit_id'];
			//$standard_id=$data['standard_id'];
			$pdtdata= [];
			$pdtdata['unit_id'] = 236;
			$pdtdata['standard_id'] =2;
			$appprdarr_details = $this->getapplicationproduct($pdtdata);
			$responsedata=array('status'=>1,'products'=>$appprdarr_details,'productwastagelist'=>$wastagepdtlist);
			

        }
        return $responsedata;
	}
	*/
	public function actionGetproductdata()
    {
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');

        //if($data)
        if($data)
		{
			//$unit_id=$data['unit_id'];
			$tc_request_id=$data['tc_request_id'];
			$pdtdata= [];
			$requestproduct  = $this->gettcproduct($tc_request_id);
			$responsedata=array('status'=>1,'data'=>$requestproduct);
			

        }
        return $responsedata;
	}
	

	public function getapplicationproduct($data){

		$unit_id=$data['unit_id'];
		$standard_id=$data['standard_id'];
			//$unit_id=236;
			//$standard_id=2;
		
		$connection = Yii::$app->getDb();
		
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
		$connection->createCommand("SET SESSION group_concat_max_len = 1000000;")->execute();
		$command = $connection->createCommand("SELECT group_concat(pdtstd.id) as productstandard FROM `tbl_application_unit_product` as unitpdt inner join tbl_application_product_standard as pdtstd on unitpdt.`application_product_standard_id`= pdtstd.id and unitpdt.`unit_id`='".$unit_id."' and pdtstd.standard_id in (".implode(',',$standard_id).") ");
		$result = $command->queryOne();
		$appprdarr_details = [];
		if($result  !== false)
		{
			//$appProduct= $appProduct->innerJoinWith(['productstandard as productstandard']);
			//$appProduct = $appProduct->andWhere(['productstandard.id'=> $result['productstandard'] ])->all();
			 
			$wastagepdtlist = [];
			$productstandards = explode(',',$result['productstandard']);
			$productstandardObj = ApplicationProductStandard::find()->where(['id' => $productstandards])->all();
			if(is_array($productstandardObj) && count($productstandardObj)>0)
			{
				$i=0;
				foreach($productstandardObj as $productstandard){
					$productMaterialList = [];



					$prd=ApplicationProduct::find()->where(['t.id' =>$productstandard->application_product_id  ])->alias('t')->one();
					$materialcompositionname = '';
					if(is_array($prd->productmaterial) && count($prd->productmaterial)>0){
						foreach($prd->productmaterial as $productmaterial){
							$productMaterialList[]=[
								'app_product_id'=>$productmaterial->app_product_id,
								'material_id'=>$productmaterial->material_id,
								'material_name'=>$productmaterial->material_name,//$productmaterial->material->name,
								'material_type_id'=>$productmaterial->material_type_id,
								'material_type_name'=> $productmaterial->material_type_name,//$productmaterial->material->material_type[$productmaterial->material_type_id],
								'material_percentage'=>$productmaterial->percentage
							];
							$materialcompositionname = $materialcompositionname.$productmaterial->percentage.'% '.$productmaterial->material_name.' + ';

						}
						$materialcompositionname = rtrim($materialcompositionname," + ");
					}
					$arrsForPdtDetails=array(
						'id'=>$prd->product_id,
						'autoid'=>$prd->id,
						'addition_type'=>$prd->product_addition_type,
						'name'=>$prd->product_name, //($prd->product?$prd->product->name:''),
						'wastage'=>$prd->wastage,
						'product_type_name' => $prd->product_type_name, //isset($prd->producttype)?$prd->producttype->name:'',
						'product_type_id'=>isset($prd->producttype)?$prd->producttype->id:'',
						'productMaterialList' => $productMaterialList,
						'materialcompositionname' => $materialcompositionname,
					);

					$unit_product_id= 0;
					$unit_product= ApplicationUnitProduct::find()->where(['unit_id'=>$unit_id,'application_product_standard_id'=>$productstandard->id])->one();
					if($unit_product!==null){
						$unit_product_id = $unit_product->id;
					}
					$arrsForPdtDetails['unit_product_id'] = $unit_product_id;
					$arrsForPdtDetails['product_standard_id'] = $productstandard->id;
					//$arrsForPdtDetails['pdt_index'] = $pdt_index;
					$arrsForPdtDetails['standard_id'] = $productstandard->standard_id;
					$arrsForPdtDetails['standard_name'] = $productstandard->standard->name;
					$arrsForPdtDetails['label_grade'] = $productstandard->label_grade_id;
					$arrsForPdtDetails['label_grade_name'] = $productstandard->label_grade_name;//$productstandard->labelgrade->name;
					//$arrsForPdtDetails['addition_type'] = $productstandard->addition_type;
					$arrsForPdtDetails['pdtListIndex'] = $i;
					

					//$appprdarr_details[]= $arrsForPdtDetails;
					$appprdarr_details[$unit_product_id]= $arrsForPdtDetails['name'].' | '.$arrsForPdtDetails['product_type_name'].' | '.$arrsForPdtDetails['wastage'].'% wastage | '.$arrsForPdtDetails['materialcompositionname'].' | '.$arrsForPdtDetails['standard_name'].' | '.$arrsForPdtDetails['label_grade_name'];
					$wastagepdtlist[$unit_product_id] = $prd->wastage;
					$i++;
					//$pdt_index++;
				}
			}			 
		}
		return $appprdarr_details;
	}

	public function actionAddproductdata()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
			$userData = Yii::$app->userdata->getData();
			
			$modelRequestProduct = new RequestProduct();
			
			$TotalGrossWeight=0;
			$TotalNetWeight=0;
			$TotalCertifiedWeight=0;
			$TotalWastageWeight=0;
			$GrandTotalNetWeight=0;
			
			if(isset($data['tc_request_id']) && $data['tc_request_id']>0)
			{
				$RequestModel = Request::find()->where(['id' => $data['tc_request_id']])->one();				
				if($RequestModel!==null)
				{
					if(!Yii::$app->userrole->isValidApplication($RequestModel->app_id))
					{
						$responsedata=array('status'=>0,'message'=>'Application is not valid.');
						return $responsedata;
					}
					
					$showedit= $this->canEditTc($RequestModel);
					if($showedit==0){
						return $responsedata;
					}
					
					$TotalGrossWeight=$RequestModel->total_gross_weight;
					$TotalNetWeight=$RequestModel->total_net_weight;;
					$TotalCertifiedWeight=$RequestModel->total_certified_weight;
					$TotalWastageWeight=$RequestModel->total_wastage_weight;
					$GrandTotalNetWeight=$RequestModel->grand_total_net_weight;
										
					$editStatus = 0;
					if(isset($data['id']) && $data['id']>0)
					{
						$model = RequestProduct::find()->where(['id' => $data['id']])->one();
						if($model===null)
						{
							$model = new RequestProduct();
							$editStatus=0;
							$model->status = $modelRequestProduct->arrEnumStatus['open'];
						}else{
							
							$TotalGrossWeight=$TotalGrossWeight-$model->gross_weight;				
							$TotalNetWeight=$TotalNetWeight-$model->net_weight;
							$TotalCertifiedWeight=$TotalCertifiedWeight-$model->certified_weight;
							$TotalWastageWeight=$TotalWastageWeight-$model->wastage_weight;
							$GrandTotalNetWeight=$GrandTotalNetWeight-$model->total_net_weight;			
							$editStatus=1;					
						}						
					}else{
						$editStatus=0;
						$model = new RequestProduct();
						$model->status = $modelRequestProduct->arrEnumStatus['open'];
					}	
					
					$wastage=0;
					$Unitproduct = ApplicationUnitProduct::find()->where(['id'=>$data['product_id']])->one();
					if($Unitproduct!==null){
						$productstd = $Unitproduct->product;
						if($productstd!==null){
							$wastage = $productstd->appproduct->wastage;
						}
						$model->standard_id = $productstd->standard_id;	
					}
					$model->tc_request_id = $data['tc_request_id'];	
					$model->product_id = $data['product_id'];	
					$model->trade_name = $data['trade_name'];	
					$model->packed_in = $data['packed_in'];
					$model->lot_ref_number = $data['lot_ref_number'];	
					$model->consignee_id = $data['consignee_id'];	
						
					$model->gross_weight = $data['gross_weight'];
					$model->net_weight = $data['net_weight'];	
					$model->certified_weight = $data['certified_weight'];

					//if($model===null || $editStatus==0)
					//{			
					$model->wastage_percentage = $wastage;
					$model->product_wastage_percentage = $wastage;
					
					if( $wastage>0){						
						$model->wastage_weight = (($data['net_weight']/(100-$wastage))*100)-$data['net_weight'];
					}else{
						$model->wastage_weight = 0;	
					}
					//}

					$model->unit_information = $data['unit_information'];
					$model->purchase_order_no = $data['purchase_order_no'];
					$model->purchase_order_date = date("Y-m-d",strtotime($data['purchase_order_date']));
					$model->invoice_no = $data['invoice_no'];
					$model->invoice_date = date("Y-m-d",strtotime($data['invoice_date']));
					$model->transport_document_no = $data['transport_document_no'];
					$model->transport_company_name = $data['transport_company_name'];
					$model->vehicle_container_no = $data['vehicle_container_no'];

					$model->transport_document_date = date("Y-m-d",strtotime($data['transport_document_date']));
					$model->transport_id = $data['transport_id'];					
					
					$model->additional_weight = 0;
					$model->total_net_weight = $model->wastage_weight + $data['net_weight'] - $model->additional_weight;
					//$model->wastage_weight = ($data['certified_weight']*$data['wastage_percentage'])/100;

					if($model->total_used_weight>=$model->total_net_weight)
					{
						$model->status = $modelRequestProduct->arrEnumStatus['input_added'];
					}else{
						$model->status = $modelRequestProduct->arrEnumStatus['open'];
					}									
						
					$model->created_by = $userData['userid'];			
					if($model->validate() && $model->save())
					{	
						$TotalGrossWeight=$TotalGrossWeight+$model->gross_weight;				
						$TotalNetWeight=$TotalNetWeight+$model->net_weight;
						$TotalCertifiedWeight=$TotalCertifiedWeight+$model->certified_weight;
						$TotalWastageWeight=$TotalWastageWeight+$model->wastage_weight;
						$GrandTotalNetWeight=$GrandTotalNetWeight+$model->total_net_weight;

						$requestObj = $model->request;
						if($requestObj!==null)
						{
							$requestObj->total_gross_weight=$TotalGrossWeight;
							$requestObj->total_net_weight=$TotalNetWeight;
							$requestObj->total_certified_weight=$TotalCertifiedWeight;
							$requestObj->total_wastage_weight=$TotalWastageWeight;
							$requestObj->grand_total_net_weight=$GrandTotalNetWeight;
							$requestObj->save();	
						}					

						$this->updateProductWeightToRequest($model->tc_request_id);
							
						$userMessage = 'Product has been created successfully';
						if($editStatus==1)
						{
							$userMessage = 'Product has been updated successfully';
						}	
						///$productlist = $this->gettcproduct($data['tc_request_id']);		
						//,'productlist'=>$productlist	
						$responsedata=array('status'=>1,'message'=>$userMessage);	
					}
				}	
			}		
		}
		return $responsedata;
	}

	public function gettcproduct($tc_request_id){

		$RequestProduct = RequestProduct::find()->where(['tc_request_id'=>$tc_request_id])->all();
		$productdata = [];
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		if(count($RequestProduct)>0){
			foreach ($RequestProduct as $pdtdata) {
				$productname = '';

				$Unitproduct = $pdtdata->unitproduct;
				$completepdtname = '';
				if($Unitproduct!== null){
					$productstd = $Unitproduct->product;
					if($productstd!==null){
						$standard_name = $productstd->standard->name;
						$labelgradename = $productstd->label_grade_name;

						$productname = $productstd->appproduct->product_name;
						$producttypename = $productstd->appproduct->product_type_name;

						$wastage = $productstd->appproduct->wastage;
						$materialcompositionname = '';
						if(count($productstd->productmaterial) >0){
							foreach($productstd->productmaterial as $productmaterial){

								$productMaterialList[]=[
									'app_product_id'=>$productmaterial->app_product_id,
									'material_id'=>$productmaterial->material_id,
									'material_name'=>$productmaterial->material_name,
									'material_type_id'=>$productmaterial->material_type_id,
									'material_type_name'=> $productmaterial->material_type_name,//material->material_type[$productmaterial->material_type_id],
									'material_percentage'=>$productmaterial->percentage
								];
								$materialcompositionname = $materialcompositionname.$productmaterial->percentage.'% '.$productmaterial->material_name.' + ';

							}
							$materialcompositionname = rtrim($materialcompositionname," + ");
						}
						//$completepdtname = $productname.' | '.$producttypename.' | '.$wastage.'% wastage | '.$materialcompositionname.' | '.$standard_name.' | '.$labelgradename;
						
						$completepdtname = $productname.' / '.$producttypename.' - '.$materialcompositionname.' <br>(Label Grade: '.$labelgradename.')';
						//.' | '.$productname.' | '.$productname.' | '.$productname;
						//$wastage = $productstd->appproduct->product->wastage;

						//$producttypename = $productstd->appproduct->producttype->name;
						//$producttypename = $productstd->appproduct->producttype->name;
						//$producttypename = $productstd->appproduct->producttype->name;


						//acc | org acc | 22% wastage | 100% organic | gots | organic
					}
					
					
				}

				$materialused = [];
				$totalweightusedfrommaterial=0;
				$TcRawMaterialUsedWeight = TcRawMaterialUsedWeight::find()->where(['tc_request_product_id'=>$pdtdata->id])->all();
				if(count($TcRawMaterialUsedWeight)>0){
					foreach($TcRawMaterialUsedWeight as $UsedWeightObj){
						$totalweightusedfrommaterial+=$UsedWeightObj->used_weight;
						$materialused[] = [
							'stock_weight' => $UsedWeightObj->stock_weight,
							'used_weight' =>  $UsedWeightObj->used_weight,
							'remaining_weight' => $UsedWeightObj->remaining_weight,
							'supplier_name' => $UsedWeightObj->rawmaterial->supplier_name,							
							'tc_number' => $UsedWeightObj->rawmaterial->tc_number,
							'tc_attachment' => $UsedWeightObj->rawmaterial->tc_attachment,
							'raw_material_id' => $UsedWeightObj->tc_raw_material_id,
							'raw_material_product_id' => $UsedWeightObj->tc_raw_material_product_id,
							'trade_name' => $UsedWeightObj->rawmaterialproduct->trade_name,
							'product_name' => $UsedWeightObj->rawmaterialproduct->product_name,
							'lot_number' => $UsedWeightObj->rawmaterialproduct->lot_number							
						];
					}
				}

				$rawmaterialusedlist = [];
				if(count($pdtdata->usedweight)>0){
					foreach($pdtdata->usedweight as $materialpdtused){
						$rawmaterialusedlist[]=[
							'tc_raw_material_id' => $materialpdtused->tc_raw_material_id,
							'tc_raw_material_product_id' => $materialpdtused->tc_raw_material_product_id,
							'product_id' => $materialpdtused->product_id,
							'stock_weight' => $materialpdtused->stock_weight,
							'used_weight' => $materialpdtused->used_weight,
							'remaining_weight' => $materialpdtused->remaining_weight

						];
					}
				}

				$TransportCompanyName=$pdtdata->transport_company_name;
				if($TransportCompanyName=='')
				{
					$TransportCompanyName='NA';
				}
				
				$VehicleContainerNo=$pdtdata->vehicle_container_no;
				if($VehicleContainerNo=='')
				{
					$VehicleContainerNo='NA';
				}
				$packedInUnitInfo = $pdtdata->packed_in;
				/*				
				$unitInfo = $pdtdata->unit_information;
				if($unitInfo!='')
				{
					$packedInUnitInfo.= ' / '.$unitInfo;
				}
				*/
				
				//$pdtdata->packed_in
				$productdata[] = [
					'id' => $pdtdata->id,
					'tc_request_id' => $pdtdata->tc_request_id,
					'trade_name' => $pdtdata->trade_name,
					'product_id' => $pdtdata->product_id,
					'product_name' => $completepdtname,
					'packed_in' => $packedInUnitInfo,
					'lot_ref_number' => $pdtdata->lot_ref_number,
					'consignee_id' => $pdtdata->consignee_id,
					'invoice_details' => 'Purchase Order No: '.$pdtdata->purchase_order_no.', Dt: '.date($date_format,strtotime($pdtdata->purchase_order_date)).', Invoice No: '.$pdtdata->invoice_no.', Dt: '.date($date_format,strtotime($pdtdata->invoice_date))
								.', Transport Document: '.$pdtdata->transport_document_no
								.', Dt: '.date($date_format,strtotime($pdtdata->transport_document_date))
								.', Transport Company Name:	'.$TransportCompanyName
								.', Vehicle / Container No: '.$VehicleContainerNo,
					'consignee_id'=>$pdtdata->consignee_id,
					'consignee_id_label'=>($pdtdata->consignee)?$pdtdata->consignee->name.' - '.$pdtdata->consignee->city:'',
					'consignee_address'=>($pdtdata->consignee)?$pdtdata->consignee->address:'',
					'consignee_license_number'=>($pdtdata->consignee)?$pdtdata->consignee->client_number:'',


					'unit_information' => $pdtdata->unit_information,
					'purchase_order_no' => $pdtdata->purchase_order_no,
					'purchase_order_date' => date($date_format,strtotime($pdtdata->purchase_order_date)),
					'invoice_no' => $pdtdata->invoice_no,
					'transport_document_no' => $pdtdata->transport_document_no,
					'transport_company_name' => $pdtdata->transport_company_name?:'NA',
					'vehicle_container_no' => $pdtdata->vehicle_container_no?:'NA',
					'invoice_date' => date($date_format,strtotime($pdtdata->invoice_date)),
					'transport_document_date' => date($date_format,strtotime($pdtdata->transport_document_date)),
 					'transport_id' => $pdtdata->transport_id,
 					'transport_id_label' => $pdtdata->transport?$pdtdata->transport->name:'',
					'wastage_percentage' => $pdtdata->wastage_percentage,
					'gross_weight' => $pdtdata->gross_weight,
					'net_weight' => $pdtdata->net_weight,
					'certified_weight' => $pdtdata->certified_weight,
					'wastage_weight' => $pdtdata->wastage_weight,
					'additional_weight' => $pdtdata->additional_weight,
					'total_net_weight' => $pdtdata->total_net_weight,
					'total_used_weight' => $pdtdata->total_used_weight,
					'product_status'=> $pdtdata->status,
					'totalweightusedfrommaterial' => $totalweightusedfrommaterial,
					'materialused' => $materialused,
					'rawmaterialusedlist' => $rawmaterialusedlist
					
				];
			}
		}
		return $productdata;
	}
	public function actionRawmaterialgroup(){
		//"SELECT GROUP_CONCAT(`standard_id`) as standardids,material.id,count(standard_id) as totstdcnt FROM `tbl_tc_raw_material` as material inner join `tbl_tc_raw_material_standard` materialstandard on material.id=materialstandard.`raw_material_id` WHERE 1 group by material.id having totstdcnt=2 and standardids='1,3'";
	}
	
	/*
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
           	$model = Request::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				//$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Request has been activated successfully';
					}elseif($model->status==1){
						$msg='Request has been deactivated successfully';
					}elseif($model->status==2){
						$msg='Request has been deleted successfully';
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
	*/

	public function actionGetFilterStatus()
    {
		$request = new Request();
		$arrayTCStatus = $request->arrStatus;
		$arrayTcEnumStatus = $request->arrEnumStatus;
		
		$userrole = Yii::$app->userrole;
		$user_type=$userrole->user_type;				
		$resource_access=$userrole->resource_access;
		$is_headquarters =$userrole->is_headquarters;						
		if($resource_access != '1' &&  $user_type!=Yii::$app->params['user_type']['customer'])
		{	
			unset($arrayTCStatus[0]);
			unset($arrayTCStatus[1]);
			
			unset($arrayTcEnumStatus['open']);
			unset($arrayTcEnumStatus['draft']);
		}		
		return ['statuslist'=>$arrayTCStatus,'enumstatus'=>$arrayTcEnumStatus];
	}   
	
	public function actionGetInvoiceOptions()
    {
		$request = new Request();
		return ['optionlist'=>$request->arrInvoiceOptions];
	}  

    protected function findModel($id)
    {
        if (($model = Request::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	public function actionGetStatus()
	{
		$data  = Yii::$app->request->post();
		if($data)
		{
			$Request = new Request();
			 

			if(($data['status']==  $Request->arrEnumStatus['review_in_process'] || $data['status']==  $Request->arrEnumStatus['waiting_for_osp_review'] )&& 	!$data['fromOSS'])
			{
				$model = new RequestReviewerComment();
			}
			else
			{
				$model = new RequestFranchiseComment();
			}
			
			return ['data'=>$model->arrStatus];
		}
		
	}
	
	public function actionListStatus()
    {
		$request = new Request();
		return ['statuslist'=>$request->arrStatus,'enumstatus'=>$request->arrEnumStatus];
	}

	public function actionDeletedata()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>1,'message'=>'Something went wrong');
		if($data)
		{			
			$id = $data['id'];
			$arrTcRawMaterialIDs = [];
			$RequestModel = Request::find()->where(['id'=>$id])->one();
			if($RequestModel!==null)
			{
				$candelete = $this->canDeleteTc($RequestModel);
				if(!$candelete){
					return $responsedata;
				}
				$TcRequestProductModel = RequestProduct::find()->where(['tc_request_id'=>$id])->all();
			 
				if(count($TcRequestProductModel)>0)
				{
					foreach($TcRequestProductModel as $productModel){
						if($RequestModel->status==$RequestModel->arrEnumStatus['rejected']){
							$TcRequestProductInputMaterial = TcRequestProductInputMaterial::find()->where(['tc_request_product_id'=>$productModel->id])->all();
							if(count($TcRequestProductInputMaterial)>0){
								foreach($TcRequestProductInputMaterial as $tcinput){
									$arrTcRawMaterialIDs[] = $tcinput->tc_raw_material_id;
								}
							}

							TcRawMaterialUsedWeight::deleteAll(['tc_request_product_id' => $productModel->id]);
							TcRequestProductInputMaterial::deleteAll(['tc_request_product_id' => $productModel->id]);	
							$productModel->delete();
						}else{
							$this->deleteRequestProductData($productModel->id);
						}
					}
				}

				if(count($RequestModel->evidence)>0)
				{
					$target_dir = Yii::$app->params['tc_files']."evidence_files/"; 
					foreach($RequestModel->evidence as $evidence)
					{
						Yii::$app->globalfuns->removeFiles($evidence->evidence_file,$target_dir);
						$evidence->delete();
					}
				}

				$arrTcRawMaterialIDs = array_unique($arrTcRawMaterialIDs);
				if(count($arrTcRawMaterialIDs)>0){
					$RawMaterialModel = new RawMaterial();						
					$RawMaterialModel->updateProductWeightRawMaterial($arrTcRawMaterialIDs);
				}				

				$RequestModel->delete();
			}	
		}
		return $responsedata=array('status'=>1,'message'=>'Data deleted successfully');
	}

	public function deleteRequestProductData($id){
		$responsedata=array('status'=>1,'message'=>'Something went wrong');
		$TcRequestProductModel = RequestProduct::find()->where(['id'=>$id])->one();			 
		if($TcRequestProductModel!== null)
		{
			$arrTcRawMaterialIDs = [];
			// ------ Reduce the Weight from TC Request Code Start Here----------
			$requestObj = $TcRequestProductModel->request;
			if($requestObj!==null)
			{
				
				
				$TotalGrossWeight=0;
				$TotalNetWeight=0;
				$TotalCertifiedWeight=0;
				$TotalWastageWeight=0;
				$GrandTotalNetWeight=0;
				$GrandTotalUsedWeight=0;
				
				$TotalGrossWeight=$requestObj->total_gross_weight;
				$TotalNetWeight=$requestObj->total_net_weight;;
				$TotalCertifiedWeight=$requestObj->total_certified_weight;
				$TotalWastageWeight=$requestObj->total_wastage_weight;
				$GrandTotalNetWeight=$requestObj->grand_total_net_weight;
				$GrandTotalUsedWeight=$requestObj->grand_total_used_weight;
								
				$TotalGrossWeight=$TotalGrossWeight-$TcRequestProductModel->gross_weight;				
				$TotalNetWeight=$TotalNetWeight-$TcRequestProductModel->net_weight;
				$TotalCertifiedWeight=$TotalCertifiedWeight-$TcRequestProductModel->certified_weight;
				$TotalWastageWeight=$TotalWastageWeight-$TcRequestProductModel->wastage_weight;
				$GrandTotalNetWeight=$GrandTotalNetWeight-$TcRequestProductModel->total_net_weight;
				
				$GrandTotalUsedWeight=$GrandTotalUsedWeight-$TcRequestProductModel->total_used_weight;

				$requestObj->total_gross_weight=$TotalGrossWeight;
				$requestObj->total_net_weight=$TotalNetWeight;
				$requestObj->total_certified_weight=$TotalCertifiedWeight;
				$requestObj->total_wastage_weight=$TotalWastageWeight;
				$requestObj->grand_total_net_weight=$GrandTotalNetWeight;
				$requestObj->grand_total_used_weight=$GrandTotalUsedWeight;
				$requestObj->save();					
			}	
			// ------ Reduce the Weight from TC Request Code End Here----------
			
			$requestproductinputmaterialObj= $TcRequestProductModel->requestproductinputmaterial;
			if(count($requestproductinputmaterialObj)>0)
			{
				foreach($requestproductinputmaterialObj as $rpinputmaterial)
				{

					$RawMaterialProductUpdate = RawMaterialProduct::find()->where(['id'=>$rpinputmaterial->tc_raw_material_product_id])->one();
					if($RawMaterialProductUpdate !== null)
					{
						$net_weight = $RawMaterialProductUpdate->net_weight;
						$total_weight = $net_weight + $rpinputmaterial->used_weight;
						$RawMaterialProductUpdate->net_weight = $total_weight;
						$RawMaterialProductUpdate->total_used_weight = $RawMaterialProductUpdate->total_used_weight - $rpinputmaterial->used_weight;
						$RawMaterialProductUpdate->save();
					}

					$RawMaterialUpdate = RawMaterial::find()->where(['id'=>$rpinputmaterial->tc_raw_material_id])->one();
					if($RawMaterialUpdate !== null)
					{
						$arrTcRawMaterialIDs[] = $rpinputmaterial->tc_raw_material_id;
						/*
						if($RawMaterialUpdate->is_certified=="1"){
							$certified_weight = $RawMaterialUpdate->certified_weight;
							$total_weight = $certified_weight + $rpinputmaterial->used_weight;
							$RawMaterialUpdate->certified_weight = $total_weight;
						}else if($RawMaterialUpdate->is_certified=="2"){
							$net_weight = $RawMaterialUpdate->net_weight;
							$total_weight = $net_weight + $rpinputmaterial->used_weight;
							$RawMaterialUpdate->net_weight = $total_weight;
						}
						*/
						
						//$net_weight = $RawMaterialUpdate->net_weight;
						//$total_weight = $net_weight + $rpinputmaterial->used_weight;
						//$RawMaterialUpdate->net_weight = $total_weight;
						//$RawMaterialUpdate->total_used_weight = $RawMaterialUpdate->total_used_weight - $rpinputmaterial->used_weight;
						//$certified_weight = $RawMaterialUpdate->certified_weight;												
						//$total_weight = $certified_weight + $rpinputmaterial->used_weight;
						//$RawMaterialUpdate->certified_weight = $total_weight;
						//$RawMaterialUpdate->save();
					}
				}
			}	
			TcRawMaterialUsedWeight::deleteAll(['tc_request_product_id' => $id]);
			TcRequestProductInputMaterial::deleteAll(['tc_request_product_id' => $id]);	
			$TcRequestProductModel->delete();	


			$arrTcRawMaterialIDs = array_unique($arrTcRawMaterialIDs);
			$RawMaterialModel = new RawMaterial();						
			$RawMaterialModel->updateProductWeightRawMaterial($arrTcRawMaterialIDs);
		}
		return true;
	}

	public function actionDeleteproductdata()
	{
		$data = Yii::$app->request->post();
		if($data)
		{			
			$id = $data['id'];
			
			$this->deleteRequestProductData($id);
			 	
		}
		return $responsedata=array('status'=>1,'message'=>'Product deleted successfully');
	}
	

	public function actionProductwiserawmaterialinputs()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if($data)
		{
			$tc_request_product_id = $data['tc_request_product_id'];			
			$RequestProduct = RequestProduct::find()->where(['id'=>$tc_request_product_id])->one();
			if($RequestProduct !== null)
			{
				$showedit= $this->canEditTc($RequestProduct->request);
				if($showedit==0)
				{
					return $responsedata;
				}
					
				//// Condition to check weight in there for raw material starts
				$weightErrorList = [];
				if(isset($data['inputweight']) && count($data['inputweight'])>0 )
				{
					$rawMaterialWeightList = [];
					
					foreach($data['inputweight'] as $inputmaterial)
					{
						$cur_used_weight = 0;
						$TcRawMaterialUsedWeight = TcRawMaterialUsedWeight::find()->where(['tc_raw_material_product_id'=>$inputmaterial['tc_raw_material_product_id'], 'tc_request_product_id'=> $tc_request_product_id])->one();
						if($TcRawMaterialUsedWeight !== null){
							$cur_used_weight = $TcRawMaterialUsedWeight->used_weight;
						}
						
						$rm_net_weight = 0;
						$RawMaterialProduct = RawMaterialProduct::find()->where(['id'=>$inputmaterial['tc_raw_material_product_id']])->one();
						if($RawMaterialProduct !== null){
							$rm_net_weight = $RawMaterialProduct->net_weight;
						}else{
							$weightErrorList[] = $inputmaterial['tc_raw_material_product_id'];
							$rawMaterialWeightList[$inputmaterial['tc_raw_material_product_id']] = [
								'net_weight'=>0,
								'supplier_name'=>'',
								'trade_name'=>'',
								'rawmaterial_standard_key'=>$inputmaterial['stdkey'],
								'rawmaterial_standard_type'=>$inputmaterial['stdtype'],
								'status' => 1
							];
						}

						$remaining_rm_net_weight = $cur_used_weight + $rm_net_weight - $inputmaterial['rminputweight'];
						if($remaining_rm_net_weight<0){
							$weightErrorList[] = $RawMaterialProduct->id;
							$rawMaterialWeightList[$RawMaterialProduct->id] = [
									'net_weight'=>$RawMaterialProduct->net_weight,
									'raw_material_id'=>$RawMaterialProduct->raw_material_id,
									'supplier_name'=>$RawMaterialProduct->rawmaterial->supplier_name,
									'trade_name'=>$RawMaterialProduct->trade_name,
									'rawmaterial_standard_key'=>$inputmaterial['stdkey'],
									'rawmaterial_standard_type'=>$inputmaterial['stdtype'],
									'status' => 0
								];
							//$rawMaterialWeightList[] = $RawMaterialProduct->net_weight;
						}


					}
					if(count($weightErrorList)>0){
						return ['status'=>2, 'weightErrorList'=>$weightErrorList,'rawMaterialWeightList'=>$rawMaterialWeightList];
					}
				}
				// condition to check weight ends here

				$TcRequestProductObj = new RequestProduct();
				$userData = Yii::$app->userdata->getData();
				$userid=$userData['userid'];							
				$arrTcRawMaterialIDs=array();		
				//--------- Update the Weight from Input Material to Raw Material Code Start Here ----------------			
				$TcRequestProductModel = RequestProduct::find()->where(['id'=>$tc_request_product_id])->one();
				if($TcRequestProductModel!== null)
				{
					$requestproductinputmaterialObj = $TcRequestProductModel->requestproductinputmaterial;
					if(count($requestproductinputmaterialObj)>0)
					{
						foreach($requestproductinputmaterialObj as $rpinputmaterial)
						{
							$RawMaterialUpdate = RawMaterialProduct::find()->where(['id'=>$rpinputmaterial->tc_raw_material_product_id])->one();
							if($RawMaterialUpdate !== null)
							{
								$arrTcRawMaterialIDs[] = $RawMaterialUpdate->raw_material_id;
								/*
								if($RawMaterialUpdate->is_certified=="1"){
									$certified_weight = $RawMaterialUpdate->certified_weight;
									$total_weight = $certified_weight + $rpinputmaterial->used_weight;
									$RawMaterialUpdate->certified_weight = $total_weight;
								}else if($RawMaterialUpdate->is_certified=="2"){
									$net_weight = $RawMaterialUpdate->net_weight;
									$total_weight = $net_weight + $rpinputmaterial->used_weight;
									$RawMaterialUpdate->net_weight = $total_weight;
								}
								*/
								
								$net_weight = $RawMaterialUpdate->net_weight;
								$total_weight = $net_weight + $rpinputmaterial->used_weight;
								$RawMaterialUpdate->net_weight = $total_weight;
								$RawMaterialUpdate->total_used_weight = $RawMaterialUpdate->total_used_weight - $rpinputmaterial->used_weight;
								$RawMaterialUpdate->save();
							}
						}
					}					
				}
				TcRawMaterialUsedWeight::deleteAll(['tc_request_product_id' => $tc_request_product_id]);
				TcRequestProductInputMaterial::deleteAll(['tc_request_product_id' => $tc_request_product_id]);			
				//--------- Update the Weight from Input Material to Raw Material Code End Here -----------------

				$RequestProduct = RequestProduct::find()->where(['id'=>$tc_request_product_id])->one();
				if($RequestProduct !== null)
				{
					$product_id = $RequestProduct->product_id;									
				}			
				
				
				$usedTotalRawMaterialWeight=0;
				if(isset($data['inputweight']) && count($data['inputweight'])>0 )
				{
					foreach($data['inputweight'] as $inputmaterial)
					{
						$RawMaterialProductObj = RawMaterialProduct::find()->where(['id'=>$inputmaterial['tc_raw_material_product_id']])->one();
						$RawMaterialProductObj->total_used_weight = $RawMaterialProductObj->total_used_weight + $inputmaterial['rminputweight'];
						$RawMaterialProductObj->save();

						$raw_material_id = $RawMaterialProductObj->raw_material_id;
						$arrTcRawMaterialIDs[] = $raw_material_id;
						
						$TcRawMaterialUsedWeight = TcRawMaterialUsedWeight::find()->where(['tc_raw_material_product_id'=>$inputmaterial['tc_raw_material_product_id'], 'tc_request_product_id'=> $tc_request_product_id])->one();
						if($TcRawMaterialUsedWeight === null){
							$TcRawMaterialUsedWeight = new TcRawMaterialUsedWeight();
							$TcRawMaterialUsedWeight->created_by = $userid;
						}else{
							$TcRawMaterialUsedWeight->updated_by = $userid;
						}
						
						$remaining_weight = 0;
						$RawMaterialProduct = RawMaterialProduct::find()->where(['id'=>$inputmaterial['tc_raw_material_product_id']])->one();
						if($RawMaterialProduct !== null){
							/*
							if($RawMaterial->is_certified=="1"){
								$certified_weight = $RawMaterial->certified_weight;
								$remaining_weight = $certified_weight  - $inputmaterial['rminputweight'];
								$RawMaterial->certified_weight = $remaining_weight;
							}else if($RawMaterial->is_certified=="2"){
								$net_weight = $RawMaterial->net_weight;
								$remaining_weight = $net_weight  - $inputmaterial['rminputweight'];
								$RawMaterial->net_weight = $remaining_weight;
								$certified_weight = $net_weight;
							}
							*/
							
							$net_weight = $RawMaterialProduct->net_weight;
							$remaining_weight = $net_weight  - $inputmaterial['rminputweight'];
							$RawMaterialProduct->net_weight = $remaining_weight;
							$certified_weight = $net_weight;
							
							//$certified_weight = $RawMaterial->certified_weight;												
							//$remaining_weight = $certified_weight - $inputmaterial['rminputweight'];
							//$RawMaterial->certified_weight = $remaining_weight;
							$RawMaterialProduct->save();
						}				

						$TcRawMaterialUsedWeight->tc_request_product_id = $tc_request_product_id;
						$TcRawMaterialUsedWeight->tc_raw_material_id = $raw_material_id;
						$TcRawMaterialUsedWeight->tc_raw_material_product_id = $inputmaterial['tc_raw_material_product_id'];
						$TcRawMaterialUsedWeight->used_weight = $inputmaterial['rminputweight'];
						
						$TcRawMaterialUsedWeight->product_id = $product_id;
						$TcRawMaterialUsedWeight->stock_weight = $certified_weight;

						$TcRawMaterialUsedWeight->remaining_weight = $remaining_weight;
						$TcRawMaterialUsedWeight->status = 0;
						if($TcRawMaterialUsedWeight->save())
						{
							$TcRequestProductInputMaterial = TcRequestProductInputMaterial::find()->where(['tc_raw_material_product_id'=>$inputmaterial['tc_raw_material_product_id'], 'tc_request_product_id'=> $tc_request_product_id])->one();
							if($TcRequestProductInputMaterial === null){
								$TcRequestProductInputMaterial = new TcRequestProductInputMaterial();
								//$TcRequestProductInputMaterial->created_by = $userid;
							}else{
								//$TcRequestProductInputMaterial->updated_by = $userid;
							}
							$TcRequestProductInputMaterial->tc_request_product_id = $tc_request_product_id;
							$TcRequestProductInputMaterial->tc_raw_material_id = $raw_material_id;
							$TcRequestProductInputMaterial->tc_raw_material_product_id = $inputmaterial['tc_raw_material_product_id'];
							$TcRequestProductInputMaterial->used_weight = $inputmaterial['rminputweight'];
							if($TcRequestProductInputMaterial->save())
							{
								$usedTotalRawMaterialWeight=$usedTotalRawMaterialWeight+$TcRequestProductInputMaterial->used_weight;
							}
						}				
					}
					
										
					
					if($RequestProduct !== null)
					{
						$totalNetWeight = 0;
						$totalNetWeight = $RequestProduct->total_net_weight; 
						//$RequestProduct->total_used_weight = number_format($usedTotalRawMaterialWeight,2);
						$RequestProduct->total_used_weight = $usedTotalRawMaterialWeight;
						if($RequestProduct->total_used_weight>=$totalNetWeight)
						{
							$RequestProduct->status = $RequestProduct->arrEnumStatus['input_added'];
						}else{
							$RequestProduct->status = $RequestProduct->arrEnumStatus['open'];
						}						
						$RequestProduct->save();
						
						$this->updateProductWeightToRequest($RequestProduct->tc_request_id);
					}
					
					if(count($arrTcRawMaterialIDs)>0)
					{
						$arrTcRawMaterialIDs = array_unique($arrTcRawMaterialIDs);
						$RawMaterialModel = new RawMaterial();						
						$RawMaterialModel->updateProductWeightRawMaterial($arrTcRawMaterialIDs);
					}
					$responsedata=array('status'=>1,'message'=>'Stock updated successfully');
				}
			}			
		}
		return $responsedata;
	}

	public function actionChangeStatus()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data  = Yii::$app->request->post();
		$userrole = Yii::$app->userrole;
		$userid=$userrole->user_id;				
		$resource_access=$userrole->resource_access;		
		if($data)
		{
			$Request = Request::find()->where(['id'=>$data['id']])->one();
			if($Request !== null && $data['tc_status'] =='approval')
			{
				if($resource_access!=1)
				{
					if($Request->application->customer_id!=$userid)
					{
						return $responsedata;	
					}
				}
				$Request->status = $Request->arrEnumStatus['waiting_for_osp_review'];
				if($Request->save())
				{
					$responsedata=array('status'=>1,'message'=>"Submitted for approval successfully!");
				}
			}
			
		}
		return $responsedata;
	}
	public function actionAddOspReview()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userrole = Yii::$app->userrole;
		$userid=$userrole->user_id;				
		$user_type=$userrole->user_type;
		$role=$userrole->role;
		$rules=$userrole->rules;
		$franchiseid=$userrole->franchiseid;		
		$resource_access=$userrole->resource_access;
		$is_headquarters =$userrole->is_headquarters;
		$role_chkid=$userrole->role_chkid;
		if($data)
		{
			$Request = Request::find()->where(['id' => $data['id']])->one();
			if($Request!==null)
			{
				if($user_type==Yii::$app->params['user_type']['user'] && $is_headquarters!=1)
				{
					if($Request->application->franchise_id!=$franchiseid)
					{
						return false;
					}								
				}
				
				if($user_type==Yii::$app->params['user_type']['user'] && !Yii::$app->userrole->hasRights(array('assign_as_oss_review_for_tc')))
				{
					return false;										
				}else if($user_type== Yii::$app->params['user_type']['franchise'] && $is_headquarters!=1){
					if($resource_access == '5'){
						$userid = $franchiseid;
					}				
					if($Request->application->franchise_id!=$userid)
					{
						return false;
					}						
				}				
				
				$ospreviewmodel = new RequestFranchiseComment();
				$ospreviewmodel->tc_request_id = $data['id'];
				$ospreviewmodel->status = $data['status'];
				$ospreviewmodel->comment = isset($data['comment'])?$data['comment']:'';
				$ospreviewmodel->created_by = $userrole->user_id;
				$ospreviewmodel->created_at = time();
				if($ospreviewmodel->validate() && $ospreviewmodel->save())
				{
					
					if($data['status']=='1')
					{
						$RequestReviewer = RequestReviewer::find()->where(['tc_request_id'=>$data['id']])->one();
						if($RequestReviewer!==null){
							$Request->status = $Request->arrEnumStatus['review_in_process'];//4;
						}else{
							$Request->status = $Request->arrEnumStatus['waiting_for_review'];//4;
						}
						
					}
					else if($data['status']=='2')
					{
						$Request->status = $Request->arrEnumStatus['pending_with_customer'];//1;
					}
					else if($data['status']=='3')
					{
						$Request->status = $Request->arrEnumStatus['rejected'];//7;
					}
					if($Request->save())
					{
						if($data['status']=='3')
						{
							$this->restoreRawMaterialWeight($ospreviewmodel->tc_request_id);
						}
						
						$responsedata=array('status'=>1,'message'=>"Review Saved Successfully!");
					}								
				}
			}		
		}
		return $responsedata;
	}

	public function actionAddReviewerReview()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userrole = Yii::$app->userrole;
		$userid=$userrole->user_id;				
		$user_type=$userrole->user_type;
		$role=$userrole->role;
		$rules=$userrole->rules;
		$franchiseid=$userrole->franchiseid;		
		$resource_access=$userrole->resource_access;
		$is_headquarters =$userrole->is_headquarters;
		$role_chkid=$userrole->role_chkid;
		if($data)
		{
			$Request = Request::find()->where(['id' => $data['id']])->one();
			if($Request!==null)
			{
				if($resource_access!=1 && $resource_access != 2)
				{
					if($Request->reviewer===null || $Request->reviewer->user_id!=$userid)
					{
						return $responsedata;
					}
				}
				
				$ospreviewmodel = new RequestReviewerComment();
				$ospreviewmodel->tc_request_id = $data['id'];
				$ospreviewmodel->tc_request_reviewer_id = $userid;
				$ospreviewmodel->status = $data['status'];
				$ospreviewmodel->comment = isset($data['comment'])?$data['comment']:'';
				$ospreviewmodel->created_by = $userid;
				$ospreviewmodel->created_at = time();
				if($ospreviewmodel->validate() && $ospreviewmodel->save())
				{
					//$Request = Request::find()->where(['id' => $data['id']])->one();
					//if($Request!==null)
					//{
						if($data['status']=='1')
						{
							$Request->status =  $Request->arrEnumStatus['approved'];//6;						
							$tc_number = 3001;
							$RequestMax = Request::find()->where(['status'=>$Request->arrEnumStatus['approved'] ])->orderBy(['CAST(tc_number AS SIGNED INTEGER)' => SORT_DESC])->one();
							if($RequestMax!==null){
								if($RequestMax->tc_number>0){
									$tc_number = $RequestMax->tc_number + 1;
								}
							}
							$tc_number = str_pad($tc_number,6,"0",STR_PAD_LEFT );
							$Request->tc_number = $tc_number;
							$Request->tc_number_cds = $tc_number;
							//$Request->generateInvoice();
						}
						else if($data['status']=='2')
						{
							$Request->status =  $Request->arrEnumStatus['pending_with_osp'];//3;
						}
						else if($data['status']=='3')
						{
							$Request->status =  $Request->arrEnumStatus['rejected'];//7;
						}
						if($Request->save())
						{
							if($Request->status==$Request->arrEnumStatus['approved'])
							{
								// ----------------------- Generate TC Code Start Here --------------------------
								$this->generateTC($data['id'],true);
								// ----------------------- Generate TC Code End Here ----------------------------
							}	
							
							if($data['status']=='3')
							{
								$this->restoreRawMaterialWeight($ospreviewmodel->tc_request_id);
							}
							$responsedata=array('status'=>1,'message'=>"Review Saved Successfully!");
						}
						
					//}
					
				}
			}	
			
		}
		return $responsedata;
	}
	
	public function generateTC($requestID,$returnType=false)
	{
		// ----------------------- Generate TC Code Start Here --------------------------
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelRequest = new Request();
							
		$html='';
		$model = Request::find()->where(['id' => $requestID]);
		$model = $model->andWhere(['not in','status',array($modelRequest->arrEnumStatus['open'],$modelRequest->arrEnumStatus['rejected'])]);
		$model = $model->one();
		if($model !== null)
		{
			$ospnumber = $model->application->franchise->usercompanyinfo->osp_number;
			$customeroffernumber = $model->application->customer->customer_number;
			
			$declaration = $model->declaration;
			$additional_declaration = $model->additional_declaration;
			$standard_declaration = $model->standard_declaration;
			$comments = $model->comments;
			
			// ----------- Getting the company name latest code start here  ----------------------
			$applicationCompanyName='';
			$applicationCompanyAddress='';
			$applicationCompanyUnitName='';
			$applicationCompanyUnitAddress='';
			
			/*
			$applicationModelObject = $model->applicationaddress;
			$applicationCompanyName=$applicationModelObject->company_name ;
			$applicationCompanyAddress=$applicationModelObject->address ;
			//$applicationCompanyUnitName=$applicationModelObject->unit_name;
			//$applicationCompanyUnitAddress=$applicationModelObject->unit_address;
			
			$applicationUnitModelObject = $model->applicationunit;
			if($applicationUnitModelObject->unit_type==1)
			{
				$applicationCompanyUnitName=$applicationModelObject->unit_name;
				$applicationCompanyUnitAddress=$applicationModelObject->unit_address;
			}else{
				$applicationCompanyUnitName=$model->applicationunit->name;
				$applicationCompanyUnitAddress=$model->applicationunit->address;
			}
			*/
			
			$applicationModelObject = $model->applicationaddress;
			$applicationCompanyName=$applicationModelObject->company_name ;
			$applicationCompanyAddress=$applicationModelObject->address.', '.$applicationModelObject->city.', '.$applicationModelObject->state->name.', '.$applicationModelObject->country->name.' - '.$applicationModelObject->zipcode;
			
			$applicationUnitModelObject = $model->applicationunit;
			if($applicationUnitModelObject->unit_type==1)
			{
				$applicationCompanyUnitName=$applicationModelObject->unit_name;
				$applicationCompanyUnitAddress=$applicationModelObject->unit_address.', '.$applicationModelObject->city.', '.$applicationModelObject->state->name.', '.$applicationModelObject->country->name.' - '.$applicationModelObject->zipcode;
			}else{
				$applicationCompanyUnitName=$model->applicationunit->name;
				$applicationCompanyUnitAddress=$model->applicationunit->address.', '.$model->applicationunit->city.', '.$model->applicationunit->state->name.', '.$model->applicationunit->country->name.' - '.$model->applicationunit->zipcode;
			}	
			
			/*
			$applicationModelObject = $model->application->currentaddress;
			if($applicationModelObject!==null)
			{
				$applicationCompanyName=$applicationModelObject->company_name ;
				$applicationCompanyAddress=$applicationModelObject->address ;
				
				$applicationUnitModelObject = $model->applicationunit;
				if($applicationUnitModelObject->unit_type==1)
				{
					$applicationCompanyUnitName=$applicationCompanyName;
					$applicationCompanyUnitAddress=$applicationCompanyAddress;
				}else{
					$applicationCompanyUnitName=$model->applicationunit->name;
					$applicationCompanyUnitAddress=$model->applicationunit->address;
				}					
			}else{
				$applicationCompanyName=$model->application->company_name;
				$applicationCompanyAddress=$model->application->address;
				$applicationCompanyUnitName=$model->applicationunit->name;
				$applicationCompanyUnitAddress=$model->applicationunit->address;
			}	
			*/
			// ----------- Getting the company name latest code end here  ----------------------				
			
			$buyer = $model->buyer;
			//$seller = $model->seller;
			$consignee = '';
			//$consignee = $model->consignee;
			$inspection = $model->inspectionbody;
			$certification = $model->certificationbody;
			
			$total_certified_weight = $model->total_certified_weight;
			$total_gross_weight = $model->total_gross_weight;
			$total_net_weight = $model->total_net_weight;
			$grand_total_net_weight = $model->grand_total_net_weight;

			$usda_nop = ($model->usda_nop_compliant==1? "Yes" : "No" );
			
			$TransactionCertificateNo='';
			
			$draftText='';
			if($model->status!=$modelRequest->arrEnumStatus['approved'] && $requestID!=638 && $requestID!=1505 && $requestID!=1693)
			{
				$draftText='DRAFT ';
				$TransactionCertificateNo=$model->id;
			}else{
				$TransactionCertificateNo=$model->tc_number;
			}
			
			$raw_material_tc_no='';
			$raw_material_farm_sc_no='';
			$raw_material_farm_tc_no='';
			$raw_material_trader_tc_no='';
			$arrRawMaterialTCNos=array();
			$arrRawMaterialFarmSCNos=array();
			$arrRawMaterialFarmTCNos=array();
			$arrRawMaterialTraderTCNos=array();
			$requestProducts = $model->product;
			if(count($requestProducts)>0)
			{
				foreach($requestProducts as $requestProduct)
				{
					$requestProductInput = $requestProduct->requestproductinputmaterial;
					if(count($requestProductInput)>0)
					{
						foreach($requestProductInput as $productInput)
						{	
							$RawMaterialObj = $productInput->rawmaterial;
							$tcN = $RawMaterialObj->tc_number;
							if($tcN!='')
							{
								$arrRawMaterialTCNos[]=$tcN;
							}
							
							$farmScN = $RawMaterialObj->form_sc_number;
							if($farmScN!='')
							{
								$arrRawMaterialFarmSCNos[]=$farmScN;
							}
							
							$farmTcN = $RawMaterialObj->form_tc_number;
							if($farmTcN!='')
							{
								$arrRawMaterialFarmTCNos[]=$farmTcN;
							}
							
							$traderTcN = $RawMaterialObj->trade_tc_number;
							if($traderTcN!='')
							{
								$arrRawMaterialTraderTCNos[]=$traderTcN;
							}								
						}
					}	
				}
			}
			$raw_material_tc_no=implode(", ", array_unique($arrRawMaterialTCNos));
			$raw_material_farm_sc_no=implode(", ",$arrRawMaterialFarmSCNos);
			$raw_material_farm_tc_no=implode(", ",$arrRawMaterialFarmTCNos);
			$raw_material_trader_tc_no=implode(", ",$arrRawMaterialTraderTCNos);
			
			$tc_generate_date = date('d/F/Y',time());
			
			$RegistrationNoArray=array();
			$RegistrationNoShortArray=array();
			
			$arrTcLogo=array();
			$tc_std_code='';
			$tc_std_name='';
			$tc_std_licence_number='';
			$tc_std_code_array=array();
			$tc_std_name_array=array();
			$tc_std_license_number_array=array();
			$std_licence_number = '';
			$show_additional_declarations = 0;
			if(count($model->standard)>0){
				foreach($model->standard as $reqstandard){
					$std_licence_number.= $reqstandard->standard->license_number."<br>";
					
					$standardCode = $reqstandard->standard->code;
					$tc_std_code_array[]=$standardCode;
					$tc_std_name_array[]=$reqstandard->standard->name;
					$tc_std_license_number_array[]=$reqstandard->standard->license_number;
					$standard_code_lower = strtolower($standardCode);
					$standardScode = $reqstandard->standard->short_code;
					
					//$RegistrationNoArray[] = "GCL-".$ospnumber.$standardScode.$customeroffernumber.'/'.$ospnumber.$standardCode.'-'.$TransactionCertificateNo;
					$RegistrationNoArray[] = "GCL-".$customeroffernumber.'/'.$ospnumber.$standardCode.'-'.$TransactionCertificateNo;
					$RegistrationNoShortArray[] = "GCL-".$ospnumber.$standardScode.$customeroffernumber;
					
					if($standard_code_lower=='gots' || $standard_code_lower=='grs' || $standard_code_lower=='rds' || $standard_code_lower=='rws' || $standard_code_lower=='rms')
					{
						$arrTcLogo[]=$standard_code_lower.'_logo.png';
					}
					if($standard_code_lower=='gots' || $standard_code_lower=='ocs')
					{
						$show_additional_declarations = 1;
					}
				}
			}
			$tc_std_code=implode(", ",$tc_std_code_array);
			
			$tc_std_name=strtoupper(implode(", ",$tc_std_name_array));			
			if(is_array($tc_std_name_array) && count($tc_std_name_array)>1)
			{
				$tc_std_name='MULTIPLE TEXTILE EXCHANGE STANDARD';	
			}
			
			$tc_std_licence_number=implode(", ",$tc_std_license_number_array);						
						
			/*
			$arrTcLogo[]='ocs_blended_logo.png';
			$arrTcLogo[]='ocs_100_logo.png';
			$arrTcLogo[]='rcs_100_logo.png';
			$arrTcLogo[]='ocs_100_logo.png';
			$arrTcLogo[]='rcs_100_logo.png';
			*/
			//$arrTcLogo[]='rcs_blended_logo.png';

			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			header('Access-Control-Max-Age: 1000');
			header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

			$mpdf = new \Mpdf\Mpdf(array('mode' => 'utf-8','margin_left' => 10,'margin_right' => 10,'margin_top' => 24,'margin_bottom' => 12,'margin_header' => 0,'margin_footer' => 3,'setAutoTopMargin' => 'stretch'));
			$mpdf->SetDisplayMode('fullwidth');
			//$mpdf->SetProtection(array(), 'UserPassword', 'MyPassword');
			
			$qrCodeURL=Yii::$app->params['certificate_file_download_path'].'scan-transaction-certificate?code='.md5($model->id);
			if($draftText!='' && $requestID!=638 && $requestID!=1505 && $requestID!=1693)
			{
				$mpdf->SetWatermarkText('DRAFT');
				$mpdf->showWatermarkText = true;
				
				$qrCodeURL=Yii::$app->params['qrcode_scan_url_for_draft'];				
			}
															
			$qr = Yii::$app->get('qr');
			//Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;				
			$qrCodeContent=$qr->setText($qrCodeURL)			
			->setLogo(Yii::$app->params['image_files']."qr-code-logo.png")			
			->setLogoWidth(85)			
			->setEncoding('UTF-8')
			->writeDataUri();			
			/*
			$mpdf->SetWatermarkImage(Yii::$app->params['image_files'].'tc_bg.png',0.2);
			$mpdf->showWatermarkImage = true;
			*/			
			
			$html='
			<style>
			table {
				border-collapse: collapse;
			}						
			
			@page :first {
				header: html_firstpage;				
			}
						
			@page { 
				footer:html_htmlpagefooter;
				background: url('.Yii::$app->params["image_files"].'gcl-bg.jpg) no-repeat 0 0;
				background-image-resize: 6;
				header: html_otherpageheader;			
			}		
			
			table, td, th {
				border: 1px solid black;
			}
			
			table.reportDetailLayout {				
				border-collapse: collapse;
				width:100%;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				margin-top:5px;
			}
			
			td.reportDetailLayout {
				text-align: center;
				border: 1px solid #000000;
				font-size:12px;
				font-family:Arial;
				/*
				background-color:#DFE8F6;
				*/
				padding:3px;
			}

			td.reportDetailLayoutInner {
				border: 1px solid #000000;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				/*
				background-color:#ffffff;
				*/
				padding:3px;
				vertical-align:top;
			}
			
			td.reportDetailLayoutInnerWithoutBorder {	
				border:none;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				/*
				background-color:#ffffff;
				*/				
				vertical-align:top;
			}
			
			.innerTitleMain
			{
				color:#000000;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				margin-bottom:5px;
				font-weight:bold;
			}
			.innerTitle
			{
				color:#000000;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				margin-bottom:5px;
			}
			div.reportDetailLayoutInner {
				border-left: 1px solid #000000;
				border-right: 1px solid #000000;
				border-bottom: 1px solid #000000;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				/*
				background-color:#ffffff;
				*/
				padding:3px;
				vertical-align:top;
			}
			</style>
			
			<htmlpagefooter name="htmlpagefooter">
				<div style="color:#000000;font-size:10px;font-family:Arial;padding-bottom:3px;text-align:right;">This electronically issued document is the valid original version.</div>
				<div style="color:#000000;font-size:10px;font-family:Arial;text-align:right;">Transaction Certificate Number <span style="font-weight:bold;">'.implode(", ",$RegistrationNoArray).'</span> and Seller License Number <span style="font-weight:bold;">GCL-'.$customeroffernumber.'</span>, <span style="font-weight:bold;">'.date('d F Y').'</span>, Page {PAGENO} of {nbpg}</div>
			</htmlpagefooter>
						
			
			<htmlpageheader name="firstpage" style="display:none;">
				<div style="width:100%;font-size:12px;font-family:Arial;position: absolute;margin-bottom: 75px;">
					<table cellpadding="0" cellspacing="0" border="0" width="100%"  style="border:none;">
						<tr>					    
							<td class="reportDetailLayoutInner" style="width:80%;padding-top:15px;font-size:16px;font-weight:bold;text-align: center;border:none;">'.$draftText.' TRANSACTION CERTIFICATE (TC) FOR TEXTILES PROCESSED <br> ACCORDING TO THE '.$tc_std_name.' ('.$tc_std_code.') <br> Transaction Certificate Number ['.implode(", ",$RegistrationNoArray).']</td>
							<td class="reportDetailLayoutInner" style="width:20%;font-size:16px;font-weight:bold;text-align: center;border:none;"><img src="'.$qrCodeContent.'" style="width:85px;margin-right: 72px;"></td>
						</tr>								
					</table>
				</div>
			</htmlpageheader>
			
			<htmlpageheader name="otherpageheader" style="display:none;margin-top: 3cm;">
				<div style="width:20%;float:right;font-size:12px;font-family:Arial;position: absolute;left:630px;top:0px;padding-top:3px;margin-bottom: 85px;">
					<img src="'.$qrCodeContent.'" style="width:85px;margin-left: 42px;">
				</div>					
			</htmlpageheader>';			
			
			// -------------- TC Product Code Start Here ------------------------
			$TcProductContent='';
			$TcProductContent='<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
				<thead>
					<tr>
						<td class="reportDetailLayout" style="text-align:center;font-weight:bold;width:5%;">S.No</td>
						<td class="reportDetailLayout" style="font-weight:bold;width:16%;">Product Details</td>
						<td class="reportDetailLayout" style="text-align:center;font-weight:bold;width:13%;">Trade Name / Technical Details</td>
						<td class="reportDetailLayout" style="text-align:center;font-weight:bold;width:10%;">Packaging Details</td>
						<td class="reportDetailLayout" style="font-weight:bold;width:24%;">Invoice and Transport Details</td>
						<td class="reportDetailLayout" style="text-align:center;font-weight:bold;width:10%;">Certified<br>Weight<br>(kgs)</td>
						<td class="reportDetailLayout" style="text-align:center;font-weight:bold;width:10%;">Net<br> Weight<br>(kgs)</td>
						<td class="reportDetailLayout" style="text-align:center;font-weight:bold;width:10%;">Gross<br>Weight<br>(kgs)</td>
					</tr>
				</thead>';					
				
			$TcConsigneeContent='';
			$TcConsigneeContent='<table cellpadding="0" autosize="2.4" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
				<thead>
					<tr>
						<td class="reportDetailLayout" style="text-align:center;width:10px;font-weight:bold;">S.No</td>
						<td class="reportDetailLayout" style="text-align:left;font-weight:bold;">Consignee</td>
						<td class="reportDetailLayout" style="text-align:center;font-weight:bold;">Invoice Number</td>
						<td class="reportDetailLayout" style="text-align:center;font-weight:bold;">Destination</td>						
					</tr>
				</thead>';	
				
				$productStandardArray=array();
				$productStandardCheckArray=array();
				$labelGradeCnt=1;
				$arrLabelGrade=array();
				if(count($requestProducts)>0)
				{
					$prtCnt=1;
					foreach($requestProducts as $requestProduct)
					{
						$productname = '';
						$Unitproduct = $requestProduct->unitproduct;
						$completepdtname = '';
						if($Unitproduct!== null)
						{
							$productstd = $Unitproduct->product;
							if($productstd!==null)
							{
								$standard_name = $productstd->standard->name;
								$standard_code = $productstd->standard->code;
								$labelgradename = $productstd->label_grade_name;

								$productname = $productstd->appproduct->product_name;
								$producttypename = $productstd->appproduct->product_type_name;

								$wastage = $productstd->appproduct->wastage;
								$materialcompositionname = '';
								if(count($productstd->productmaterial) >0)
								{
									foreach($productstd->productmaterial as $productmaterial)
									{
										$productMaterialList[]=[
											'app_product_id'=>$productmaterial->app_product_id,
											'material_id'=>$productmaterial->material_id,
											'material_name'=>$productmaterial->material_name,
											'material_type_id'=>$productmaterial->material_type_id,
											'material_type_name'=> $productmaterial->material_type_name,//material->material_type[$productmaterial->material_type_id],
											'material_percentage'=>$productmaterial->percentage
										];
										$materialcompositionname = $materialcompositionname.$productmaterial->percentage.'% '.$productmaterial->material_name.' + ';

									}
									$materialcompositionname = rtrim($materialcompositionname," + ");
								}
								//$completepdtname = $productname.' | '.$producttypename.' | '.$wastage.'% wastage | '.$materialcompositionname.' | '.$standard_name.' | '.$labelgradename;												
								//$completepdtname = $productname.' - '.$materialcompositionname.' <br>(Label Grade: '.$labelgradename.')';
								$completepdtname = $productname.' / '.$producttypename.' - '.$materialcompositionname.' <br>(Label Grade: '.$labelgradename.')';
								
								// ------------- Code for Identify Standard Blended Logo Code Start Here -----------------
								$standard_code_lower = strtolower($standard_code);
								if(!in_array($standard_code_lower,$productStandardCheckArray))
								{
									$arrLabelGrade=array();
									$arrLabelGrade[$labelGradeCnt]=strtolower($labelgradename);												
									if(is_array($arrLabelGrade) && count($arrLabelGrade)>0)
									{
										$resArray = array_filter($arrLabelGrade, function($value) {
											return (strpos($value, 'blended') !== false || strpos($value, 'bl') !== false) ? true : false ;
										}); 										
										
										if(is_array($resArray) && count($resArray)>0)
										{
											$arrTcLogo[]=$standard_code_lower.'_blended_logo.png';
											$productStandardCheckArray[]=$standard_code_lower;
										}
									}
								}
								// ------------- Code for Identify Standard Blended Logo Code End Here -----------------		

								// ------------- Code for Identify Standard 100 Logo Code Start Here -----------------
								$standard_code_lower = strtolower($standard_code);
								if(!in_array($standard_code_lower,$productStandardArray))
								{
									$arrLabelGrade=array();
									$arrLabelGrade[$labelGradeCnt]=strtolower($labelgradename);												
									if(is_array($arrLabelGrade) && count($arrLabelGrade)>0)
									{
										$resArray = array_filter($arrLabelGrade, function($value) {
											return strpos($value, '100') !== false;
										}); 										
										
										if(is_array($resArray) && count($resArray)>0)
										{
											$arrTcLogo[]=$standard_code_lower.'_100_logo.png';
											$productStandardArray[]=$standard_code_lower;
										}
									}
								}
								// ------------- Code for Identify Standard 100 Logo Code End Here -----------------									
							}										
						}
						
						$packedInUnitInfo = $requestProduct->packed_in;						
						$unitInfo = $requestProduct->unit_information;
						if($unitInfo!='')
						{
							$packedInUnitInfo.= ' / '.$unitInfo;
						}	
							
						$TransportCompanyName=$requestProduct->transport_company_name;
						if($TransportCompanyName=='')
						{
							$TransportCompanyName='NA';
						}
						
						$VehicleContainerNo=$requestProduct->vehicle_container_no;
						if($VehicleContainerNo=='')
						{
							$VehicleContainerNo='NA';
						}
						
						$TcProductContent.= '<tr>								
							<td class="reportDetailLayoutInner" style="text-align:center;">'.$prtCnt.'</td>
							<td class="reportDetailLayoutInner" style="text-align:center;">'.$completepdtname.'</td>
							<td class="reportDetailLayoutInner" style="text-align:center;">Lot Number/Style Number:'.$requestProduct->lot_ref_number.'<br>'.$requestProduct->trade_name.'</td>
							<td class="reportDetailLayoutInner" style="text-align:center;">'.$packedInUnitInfo.'</td>
							<td class="reportDetailLayoutInner" style="text-align:center;">
							Purchase Order No: '.$requestProduct->purchase_order_no.'<br>
							Dt: '.date($date_format,strtotime($requestProduct->purchase_order_date)).'<br>
							Invoice No: '.$requestProduct->invoice_no.'<br>
							Dt: '.date($date_format,strtotime($requestProduct->invoice_date)).'<br>
							Transport Document: '.$requestProduct->transport_document_no.'<br>
							Dt: '.date($date_format,strtotime($requestProduct->transport_document_date)).'<br>
							Transport Company Name:	'.$TransportCompanyName.'<br>
							Vehicle / Container No: '.$VehicleContainerNo.'
							</td>
							<td class="reportDetailLayoutInner" style="text-align:center;">'.$requestProduct->certified_weight.'</td>
							<td class="reportDetailLayoutInner" style="text-align:center;">'.$requestProduct->net_weight.'</td>
							<td class="reportDetailLayoutInner" style="text-align:center;">'.$requestProduct->gross_weight.'</td>									
						</tr>';
						
						$prdConsignee=$requestProduct->consignee;
						$prdConsigneeCountry = ($prdConsignee->country?$prdConsignee->country->name:'');

						$consigneeAddress='';
						$consigneeAddress=$prdConsignee->name.'<br>';
						$consigneeAddress.=$prdConsignee->address.', '.($prdConsignee->city ? $prdConsignee->city.', ' : '').''.($prdConsignee->state ? $prdConsignee->state->name.', ' : '').''.$prdConsigneeCountry.' - '.$prdConsignee->zipcode;
						
						$TcConsigneeContent.= '<tr>								
							<td class="reportDetailLayoutInner" style="text-align:center;">'.$prtCnt.'</td>
							<td class="reportDetailLayoutInner" style="text-align:left;">'.$consigneeAddress.'</td>
							<td class="reportDetailLayoutInner" style="text-align:center;">'.$requestProduct->invoice_no.'</td>
							<td class="reportDetailLayoutInner" style="text-align:center;">'.$prdConsigneeCountry.'</td>																	
						</tr>';
						
						$prtCnt++;
					}	
				}					
			$TcProductContent.= '</table>';
			$TcConsigneeContent.= '</table>';
			// -------------- TC Product Code End Here ------------------------
			
							
			// -------------- TC Logo Code Start Here -------------------------
			$vAlign='middle';
			$logoStyle='padding-top:16px;';
			if(is_array($arrTcLogo))
			{
				if(count($arrTcLogo)>2)
				{
					$vAlign='top';
					//$logoStyle='padding-top:8px;';
					$logoStyle='padding-top:16px;';
				}
			}
			
			//$DatePlaceContent='<div>{SNO} Place and Date of Issue <br>London, '.$tc_generate_date.'</div><br>';
			//$SignatureContent='<div>{SNO} Signature of the authorised person of the body detailed in box 1</div><br>';
			
			$DatePlaceContent='{SNO} Place and Date of Issue <br>London, '.$tc_generate_date.'<br><br>';
			$SignatureContent='{SNO} Signature of the authorised person of the body detailed in box 1<br>';
			
			$TcLogoContent='';
			$TcLogoContent='<table cellpadding="0" cellspacing="0" border="0" width="100%" style="border:none;">					
				<tr>
					<td style="text-align:left;width:34%;" valign="middle" class="reportDetailLayoutInnerWithoutBorder">
						{DATEANDPLACE}
						{SIGNATURECONTENT}
						<img style="width:120px;" src="'.Yii::$app->params['image_files'].'certificate-sign.png" border="0">
						<br>
						<p>Mahmut Sogukpinar, COO<br>GCL International Ltd</p>
					</td>
					<td style="text-align:center;width:28%;" valign="middle" class="reportDetailLayoutInnerWithoutBorder">
						<div style="padding-top:5px;">Stamp of the Issuing Body</div>
						<div style="float:left;width:100%;"><img style="width:110px;{PADDINGTOP}" src="'.Yii::$app->params['image_files'].'gcl-stamp.png" border="0"></div>
					</td>
					<td style="text-align:center;width:38%;padding-bottom:5px;" valign="'.$vAlign.'" class="reportDetailLayoutInnerWithoutBorder">
					<div style="padding-top:5px;padding-bottom:5px;float:left;width:100%;">Logo</div>
					<div style="float:left;width:100%;'.$logoStyle.'">';
					if(is_array($arrTcLogo) && count($arrTcLogo)>0)
					{
						foreach($arrTcLogo as $certLogoKey => $certLogo)
						{
							$logoWidth='width:115px;';
							if(is_array($tc_std_code_array) && isset($tc_std_code_array[$certLogoKey]) && $tc_std_code_array[$certLogoKey]=='GRS')
							{
								$logoWidth='width:190px;';
							}
							$TcLogoContent.='<img style="'.$logoWidth.'{PADDINGLOGOTOP}padding-left:5px;" src="'.Yii::$app->params['image_files'].''.$certLogo.'" border="0">';							
						}
					}						
				$TcLogoContent.='</div></td>						
				</tr>
			</table>';
			
			$TcLogoContentFirstPage=$TcLogoContent;
			
			$TcLogoContentFirstPage = str_replace('{DATEANDPLACE}',$DatePlaceContent,$TcLogoContentFirstPage);
			$TcLogoContentFirstPage = str_replace('{SNO}','<span class="innerTitleMain">16.</span>',$TcLogoContentFirstPage);
			$TcLogoContentFirstPage = str_replace('{SIGNATURECONTENT}',$SignatureContent,$TcLogoContentFirstPage);
			$TcLogoContentFirstPage = str_replace('{PADDINGTOP}','padding-top:20px;',$TcLogoContentFirstPage);
			$TcLogoContentFirstPage = str_replace('{PADDINGLOGOTOP}',$logoStyle,$TcLogoContentFirstPage);
			
			$TcLogoContentFirstPage = str_replace('{SNO}','',$TcLogoContentFirstPage);								
			
			/*
			<img style="width:120px;padding-top:15px;padding-bottom:15px;" src="'.Yii::$app->params['image_files'].'certificate-sign.png" border="0">
			
			$TcLogoContent='<table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:10px;">
				<tr>
					<td style="text-align:center;font-size:14px;padding-top:5px;" valign="middle" class="reportDetailLayoutInner">Place and Date of Issue <br>London, '.$tc_generate_date.'</td>
					<td style="text-align:center;font-size:14px;padding-top:5px;" valign="middle" class="reportDetailLayoutInner">Stamp of the issuing body</td>	
					<td style="text-align:center;font-size:14px;padding-top:5px;" valign="middle" class="reportDetailLayoutInner">Logo</td>		
				</tr>
				<tr>
					<td style="text-align:center;font-size:14px;padding-top:5px;width:33%;" valign="middle" class="reportDetailLayoutInner">
						<img style="width:120px;" src="'.Yii::$app->params['image_files'].'certificate-sign.png" border="0">
						<p>Name of the authorized person:<br>Mahmut Sogukpinar, Chief Operating Officer<br>GCL International Ltd</p>
					</td>
					<td style="text-align:center;font-size:14px;padding-top:5px;width:33%;" valign="middle" class="reportDetailLayoutInner">
						<img style="width:100px;" src="'.Yii::$app->params['image_files'].'gcl-stamp.png" border="0">
					</td>
					<td style="text-align:center;font-size:14px;padding-top:5px;width:33%;" valign="middle" class="reportDetailLayoutInner">';
					if(is_array($arrTcLogo) && count($arrTcLogo)>0)
					{
						foreach($arrTcLogo as $certLogo)
						{
							$TcLogoContent.='<img style="width:80px;" src="'.Yii::$app->params['image_files'].''.$certLogo.'" border="0">';
						}
					}						
				$TcLogoContent.='</td>						
				</tr>
			</table>';
			*/
			// -------------- TC Logo Code End Here ---------------------------
			//$html.= '<sethtmlpageheader name="firstpage" value="on" show-this-page="1" />';
			
			/*
			$html.= '
			<div style="width:20%;float:right;font-size:12px;font-family:Arial;position: absolute;left:630px;top:0px;padding-top:15px;">
				<img src="'.$qrCodeContent.'" style="width:85px;margin-left: 45px;">
			</div>
			<table cellpadding="0" cellspacing="0" border="0" width="100%"  style="margin-top:10px;border:none;">
				<tr>
					<td class="reportDetailLayoutInner" style="font-size:16px;font-weight:bold;text-align: center;border:none;">'.$draftText.' TRANSACTION CERTIFICATE (TC) FOR TEXTILES PROCESSED <br> ACCORDING TO THE '.$tc_std_name.' ('.$tc_std_code.') <br> Transaction Certificate Number ['.implode(", ",$RegistrationNoArray).']</td>
				</tr>				
			</table>';
			*/			

			$html.= '
			<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayoutInner" style="margin-top:10px;">
				<tr>
					<td class="reportDetailLayoutInner" width="49%">
						<span class="innerTitleMain">1. Certification Body</span> <br><br>
						<span class="innerTitle">1a) Body issuing the certificate (name and address)</span> <br>

						GCL International Ltd<br>Level 1, One Mayfair Place, London, WIJ8AJ UK, United Kingdom.<br><br>

						<span class="innerTitle">1b) Licensing code of the certification body</span> <br>
						'.$tc_std_licence_number.'
					</td>

					<td class="reportDetailLayoutInner" width="51%">
						<span class="innerTitleMain">2. Input Information</span><br><br><br><br>
						Specified in box: 19<br><br>							
					</td>
				</tr>				

				<tr>
					<td class="reportDetailLayoutInner">
						<span class="innerTitleMain">3. Seller of certified product(s)</span> <br><br>
						<span class="innerTitle">3a) Name of seller of certified product(s)</span> <br>
						'.$applicationCompanyName.'<br>
						'.$applicationCompanyAddress.'<br><br>
						<span class="innerTitle">3b) License number of seller</span> <br>
						GCL-'.$customeroffernumber.'
					</td>

					<td class="reportDetailLayoutInner">
						<span class="innerTitleMain">4. Inspection body (name and address)</span> <br>'.$inspection->name.'<br>'.$inspection->description.'
					</td>
				</tr>

				<tr>
					<td class="reportDetailLayoutInner">
						<span class="innerTitleMain">5. Last processor of certified product(s)</span> <br><br> 
						<span class="innerTitle">5a) Name of last processor of certified product(s)</span> <br>
						'.$applicationCompanyUnitName.'<br>
						'.$applicationCompanyUnitAddress.'<br><br>
						<span class="innerTitle">5b) License number of last processor</span> <br>
						GCL-'.$customeroffernumber.'
					</td>

					<td class="reportDetailLayoutInner">
						<span class="innerTitleMain">6. Country of dispatch</span> <br>'.($model->dispatchcountry?$model->dispatchcountry->name:'').'
					</td>
				</tr>

				<tr>
					<td class="reportDetailLayoutInner" rowspan="2">
						<span class="innerTitleMain">7. Buyer of the certified product(s)</span> <br><br> 
						<span class="innerTitle">7a) Name of buyer of certified product(s)</span> <br>
						'.$buyer->name.'<br>
						'.$buyer->address.', '.$buyer->city.', '.($buyer->state?$buyer->state->name.', ':'').''.($buyer->country?$buyer->country->name:'').' - '.$buyer->zipcode.'<br><br>
						<span class="innerTitle">7b) License number of buyer</span> <br>
						'.($buyer->client_number ? $buyer->client_number : '-').'		
					</td>

					<td class="reportDetailLayoutInner">
						<span class="innerTitleMain">8. Consignee of the product (Address of the place of destination)</span> <br>Specified in box: 18
					</td>
				</tr>
				
				<tr>
					<td class="reportDetailLayoutInner">
						<span class="innerTitleMain">9. Country of destination</span> <br>Specified in box: 18
					</td>
				</tr>

				<tr>
					<td class="reportDetailLayoutInner" rowspan="3">
						<div class="innerTitleMain" style="padding-bottom:5px;width:100%;">10. Product and shipment information</div><br>';
						$boxTenCss='';
						if($comments=='')
						{
							$boxTenCss='<br>';
						}
						$html.=$boxTenCss.'<div style="padding-bottom:5px;width:100%;">Products as specified in box: 17</div><br>';
						if($comments!='')
						{
							$html.=$comments;
						}
					$html.= '</td>

					<td class="reportDetailLayoutInner">
						<span class="innerTitleMain">11. Gross shipping weight (kgs)</span> <br>'.$total_gross_weight.'
					</td>
				</tr>				
				
				<tr>
					<td class="reportDetailLayoutInner">
						<span class="innerTitleMain">12. Net shipping weight (kgs)</span> <br>'.$total_net_weight.'
					</td>
				</tr>

				<tr>
					<td class="reportDetailLayoutInner">
						<span class="innerTitleMain">13. Certified weight (kgs)</span> <br>'.$total_certified_weight.'
					</td>
				</tr>

				<tr>
					<td class="reportDetailLayoutInner" colspan="2">
						<span class="innerTitleMain">14. Declaration of the body issuing the certificate</span> <br>
						'.$declaration.'
					</td>
				</tr>';
				
					$html.= '<tr>
						<td class="reportDetailLayoutInner" colspan="2">
							<span class="innerTitleMain">15. Additional declarations</span> <br>';
							if($show_additional_declarations == 1)
							{
								$html .= $additional_declaration;
							}
							$html.='</td>
						</tr>';
				
									
				$html.= '<tr>
					<td class="reportDetailLayoutInner" colspan="2">
					'.$TcLogoContentFirstPage.'
					</td>
				</tr>	
				
			</table>';			
						
			//$html.= '<sethtmlpageheader name="otherpageheader" value="on"/><pagebreak />				
			//$html.= '<div class="chapter2"></div><sethtmlpageheader name="otherpageheader" value="on" show-this-page="1" />							
			$html.= '<pagebreak />										
			<table cellpadding="0" cellspacing="0" border="0" width="100%" class="reportDetailLayoutInner" style="margin-top:10px;">									
				<tr>
					<td class="reportDetailLayoutInner">
					Reference Number of the certificate: <br><br>
					'.implode(", ",$RegistrationNoArray).'
					</td>
				</tr>
			</table>';	
			$html.= '
				<div class="reportDetailLayoutInner">
					<span class="innerTitleMain">17. Continuation of box 10</span>
					'.$TcProductContent.'				
				</div>
				<div class="reportDetailLayoutInner">
					<span class="innerTitleMain">18. Continuation of box 8 and box 9</span>
					'.$TcConsigneeContent.'
				</div>';
			
			$TcLogoContentInnerPage = str_replace('{DATEANDPLACE}','',$TcLogoContent);
			$TcLogoContentInnerPage = str_replace('{DATEANDPLACE}',$DatePlaceContent,$TcLogoContentInnerPage);
			$TcLogoContentInnerPage = str_replace('{SIGNATURECONTENT}',$SignatureContent,$TcLogoContentInnerPage);
			$TcLogoContentInnerPage = str_replace('{PADDINGTOP}','padding-top:10px;',$TcLogoContentInnerPage);
			$TcLogoContentInnerPage = str_replace('{PADDINGLOGOTOP}','padding-top:6px;',$TcLogoContentInnerPage);
			$TcLogoContentInnerPage = str_replace('{SNO}','<span class="innerTitleMain">21.</span>',$TcLogoContentInnerPage);			
			
			$html.= '
			<div class="reportDetailLayoutInner">
				<span class="innerTitleMain">19. Continuation of box 2</span> <br><br>
				<span class="innerTitle">2a) Reference number of the input transaction certificate</span> <br>
				'.($raw_material_tc_no ? $raw_material_tc_no : '-').' <br><br>
				<span class="innerTitle">2b) Farm scope certificates number of First Raw material</span> <br>
				'.($raw_material_farm_sc_no ? $raw_material_farm_sc_no : '-').' <br><br>
				<span class="innerTitle">2c) Farm transaction certificate numbers of First Raw material</span> <br>
				'.($raw_material_farm_tc_no ? $raw_material_farm_tc_no : '-').' <br><br>
				<span class="innerTitle">2d) Trader(s) Transaction Certificates numbers of First Raw material</span> <br>
				'.($raw_material_trader_tc_no ? $raw_material_trader_tc_no : '-').'
			</div>		
			<div class="reportDetailLayoutInner">
				<span class="innerTitleMain">20.</span> '.$standard_declaration.'
			</div>		
			<div class="reportDetailLayoutInner">
				'.$TcLogoContentInnerPage.'
			</div>';
			
			//$pdfName = 'TRANSACTION_CERTIFICATE_' . date('YmdHis') . '.pdf';
			$pdfName = 'TRANSACTION_CERTIFICATE_'.$customeroffernumber.'_'.$TransactionCertificateNo.'.pdf';
			$filepath=Yii::$app->params['tc_files']."tc/".$pdfName;			
			$mpdf->WriteHTML($html);	
			
			if($returnType)
			{
				$mpdf->Output($filepath,'F');												
				$model->filename=$pdfName;
				$model->save();				
			}else{
				$mpdf->Output($filepath,'D');	
			}
			
		}
		// ----------------------- Generate TC Code End Here ----------------------------
	}

	public function actionAssignReviewer()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$data  = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data)
		{
			if(!Yii::$app->userrole->hasRights(array('application_review')))
			{
				return false;
			}
			
			$RequestModel = new Request();
			$Request = Request::find()->where(['id' => $data['id'],'status'=> $RequestModel->arrEnumStatus['waiting_for_review']])->one();
			if($Request!==null)
			{
				$reviewermodel = new RequestReviewer();
				$reviewermodel->tc_request_id = $data['id'];
				$reviewermodel->user_id = $userid;
				$reviewermodel->created_by = $userid;
				if($reviewermodel->validate() && $reviewermodel->save())
				{
					
					$Request->status = $Request->arrEnumStatus['review_in_process'];
					$Request->save();
					$responsedata=array('status'=>1,'message'=>"Assigned Successfully!",'request_status'=>$Request->status);
					

				}
			}
			
		}
		return $responsedata;
	}
	
	public function actionEvidenceDocument(){
		$datapost = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($datapost) 
		{	
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$is_headquarters =$userData['is_headquarters'];
			$franchiseid=$userData['franchiseid'];
			$role_chkid=$userData['role_chkid'];

			$data = json_decode($datapost['formvalues'],true);
			if($data)
			{
				$RequestModel = Request::find()->where(['id'=>$data['id']])->one();
				if($RequestModel !== null)
				{
					if(!Yii::$app->userrole->isValidApplication($RequestModel->app_id))
					{
						$responsedata=array('status'=>0,'message'=>'Application is not valid.');
						return $responsedata;
					}
					
					$showedit= $this->canEditTc($RequestModel);
					if($showedit==0){
						return $responsedata;
					}
					
					
					//return $data; die;
					$target_dir = Yii::$app->params['tc_files']."evidence_files/"; 

					$editStatus=1;
					//RequestEvidence::find()->where(['tc_request_id'=>$data['id'] ]);
					RequestEvidence::deleteAll(['tc_request_id' => $data['id']]);
					/*
					$RequestEvidence = RequestEvidence::find()->where(['tc_request_id'=>$data['id'] ])->one();
					if($RequestEvidence===null)
					{

						$RequestEvidence = new RequestEvidence();
						$editStatus=0;
						$RequestEvidence->created_by = $userid;
						$RequestEvidence->tc_request_id = $data['id'];
					}else{
						$RequestEvidence->updated_by = $userid;
					}
					*/
					
					$sales_invoice_with_packing_list = $data['sales_invoice_with_packing_list'];
					if(count($sales_invoice_with_packing_list)>0){
						$icnt = 0;
						foreach($sales_invoice_with_packing_list as $filedetails){
							if($filedetails['deleted'] != '1'){
								$filename= '';
								if($filedetails['added'] == '1'){
									if(isset($_FILES['sales_invoice_with_packing_list']['name'][$icnt]))
									{
										$tmp_name = $_FILES["sales_invoice_with_packing_list"]["tmp_name"][$icnt];
										$name = $_FILES["sales_invoice_with_packing_list"]["name"][$icnt];
										$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);	
																	
									}
								}else{
									$filename = $filedetails['name'];
								}
								
								$RequestEvidence = new RequestEvidence();
								$RequestEvidence->evidence_file = $filename;
								$RequestEvidence->tc_request_id = $data['id'];
								$RequestEvidence->evidence_type = 'sales_invoice';
								$RequestEvidence->save();
								
							}else{
								$filename = $filedetails['name'];
								if($filename!='')
								{
									Yii::$app->globalfuns->removeFiles($filename,$target_dir);							
								}
							}
							$icnt++;
						}
					}


					$transport_document = $data['transport_document'];
					if(count($transport_document)>0){
						$icnt = 0;
						foreach($transport_document as $filedetails){
							if($filedetails['deleted'] != '1'){
								$filename= '';
								if($filedetails['added'] == '1'){
									if(isset($_FILES['transport_document']['name'][$icnt]))
									{
										$tmp_name = $_FILES["transport_document"]["tmp_name"][$icnt];
										$name = $_FILES["transport_document"]["name"][$icnt];
										$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);	
																	
									}
								}else{
									$filename = $filedetails['name'];
								}
								
								$RequestEvidence = new RequestEvidence();
								$RequestEvidence->evidence_file = $filename;
								$RequestEvidence->tc_request_id = $data['id'];
								$RequestEvidence->evidence_type = 'transport_document';
								$RequestEvidence->save();
							}else{
								$filename = $filedetails['name'];
								if($filename!='')
								{
									Yii::$app->globalfuns->removeFiles($filename,$target_dir);							
								}
							}
							$icnt++;
						}
					}

					$mass_balance_sheet = $data['mass_balance_sheet'];
					if(count($mass_balance_sheet)>0){
						$icnt = 0;
						foreach($mass_balance_sheet as $filedetails){
							if($filedetails['deleted'] != '1'){
								$filename= '';
								if($filedetails['added'] == '1'){
									if(isset($_FILES['mass_balance_sheet']['name'][$icnt]))
									{
										$tmp_name = $_FILES["mass_balance_sheet"]["tmp_name"][$icnt];
										$name = $_FILES["mass_balance_sheet"]["name"][$icnt];
										$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);	
																	
									}
								}else{
									$filename = $filedetails['name'];
								}
								
								$RequestEvidence = new RequestEvidence();
								$RequestEvidence->evidence_file = $filename;
								$RequestEvidence->tc_request_id = $data['id'];
								$RequestEvidence->evidence_type = 'mass_balance';
								$RequestEvidence->save();
							}else{
								$filename = $filedetails['name'];
								if($filename!='')
								{
									Yii::$app->globalfuns->removeFiles($filename,$target_dir);							
								}
							}
							$icnt++;
						}
					}

					$test_report = $data['test_report'];
					if(count($test_report)>0){
						$icnt = 0;
						foreach($test_report as $filedetails){
							if($filedetails['deleted'] != '1'){
								$filename= '';
								if($filedetails['added'] == '1'){
									if(isset($_FILES['test_report']['name'][$icnt]))
									{
										$tmp_name = $_FILES["test_report"]["tmp_name"][$icnt];
										$name = $_FILES["test_report"]["name"][$icnt];
										$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);	
																	
									}
								}else{
									$filename = $filedetails['name'];
								}
								
								$RequestEvidence = new RequestEvidence();
								$RequestEvidence->evidence_file = $filename;
								$RequestEvidence->tc_request_id = $data['id'];
								$RequestEvidence->evidence_type = 'test_report';
								$RequestEvidence->save();
							}else{
								$filename = $filedetails['name'];
								if($filename!='')
								{
									Yii::$app->globalfuns->removeFiles($filename,$target_dir);							
								}
							}
							$icnt++;
						}
					}

					if($data['savetype'] == 'approval'){
						$Request = Request::find()->where(['id'=>$data['id'] ])->one();
						if($Request!==null)
						{
							$Request->status = $Request->arrEnumStatus['waiting_for_osp_review'];
							$Request->save();
						}
					}else{
						$Request = Request::find()->where(['id'=>$data['id'] ])->one();
						if($Request!==null && $Request->status== $Request->arrEnumStatus['open'] )
						{
							if($data['savetype'] != 'other'){
								$Request->status = $Request->arrEnumStatus['draft'];
								$Request->save();
							}
						}

					}			
					$responsedata=array('status'=>1,'message'=>$editStatus?"Updated Successfully!":"Created Successfully");
				}
			}		
		}
		return $responsedata;
	}
	
	public function restoreRawMaterialWeight($tc_request_id)
	{
		$TcRequestProductObj = new RequestProduct();		
		$TcRequestProductModel = RequestProduct::find()->where(['tc_request_id'=>$tc_request_id])->all();		
		if(count($TcRequestProductModel)>0)
		{

			foreach($TcRequestProductModel as $requestedProduct)
			{
				$requestedProductID = $requestedProduct->id;
				$requestproductinputmaterialObj= $requestedProduct->requestproductinputmaterial;				
				if(count($requestproductinputmaterialObj)>0)
				{
					foreach($requestproductinputmaterialObj as $rpinputmaterial)
					{
						$RawMaterialUpdate = RawMaterial::find()->where(['id'=>$rpinputmaterial->tc_raw_material_id])->one();
						if($RawMaterialUpdate !== null)
						{
							/*
							if($RawMaterialUpdate->is_certified=="1"){
								$certified_weight = $RawMaterialUpdate->certified_weight;
								$total_weight = $certified_weight + $rpinputmaterial->used_weight;
								$RawMaterialUpdate->certified_weight = $total_weight;
							}else if($RawMaterialUpdate->is_certified=="2"){
								$net_weight = $RawMaterialUpdate->net_weight;
								$total_weight = $net_weight + $rpinputmaterial->used_weight;
								$RawMaterialUpdate->net_weight = $total_weight;
							}
							*/
							
							$net_weight = $RawMaterialUpdate->net_weight;
							$total_weight = $net_weight + $rpinputmaterial->used_weight;
							$RawMaterialUpdate->net_weight = $total_weight;
							$RawMaterialUpdate->total_used_weight  = $RawMaterialUpdate->total_used_weight - $rpinputmaterial->used_weight;
							$RawMaterialUpdate->save();
						}
						$RawMaterialProductUpdate = RawMaterialProduct::find()->where(['id'=>$rpinputmaterial->tc_raw_material_product_id])->one();
						if($RawMaterialProductUpdate !== null)
						{
							$net_weight = $RawMaterialProductUpdate->net_weight;
							$total_weight = $net_weight + $rpinputmaterial->used_weight;
							$RawMaterialProductUpdate->net_weight = $total_weight;
							$RawMaterialProductUpdate->total_used_weight = $RawMaterialProductUpdate->total_used_weight - $rpinputmaterial->used_weight;
							$RawMaterialProductUpdate->save();
						}
					}
				}
				
				// Change the Status to Used Weight Related Entries in Raw Material				
				TcRawMaterialUsedWeight::updateAll(['status' => 2], ['tc_request_product_id' => $requestedProductID]);
				
			}
		}	
	}
	
	public function updateProductWeightToRequest($tc_request_id)
	{
		$RequestModel = new Request();
		if($tc_request_id!='')
		{
			$total_gross_weight=0;
			$total_net_weight=0;
			$total_certified_weight=0;
			$total_wastage_weight=0;
			$grand_total_net_weight=0;
			$grand_total_used_weight=0;
			$requestedProductValidStatus=1;
			
			$Request = Request::find()->where(['id'=>$tc_request_id ])->one();
			if($Request!==null)
			{					
				$TcRequestProductModel = $Request->product;
				$TcRequestProductModelCount = count($TcRequestProductModel);
				if($TcRequestProductModelCount>0)
				{
					foreach($TcRequestProductModel as $requestedProduct)
					{
						$total_gross_weight=$total_gross_weight+$requestedProduct->gross_weight;
						$total_net_weight=$total_net_weight+$requestedProduct->net_weight;
						$total_certified_weight=$total_certified_weight+$requestedProduct->certified_weight;
						$total_wastage_weight=$total_wastage_weight+$requestedProduct->wastage_weight;
						$grand_total_net_weight=$grand_total_net_weight+$requestedProduct->total_net_weight;
						$grand_total_used_weight=$grand_total_used_weight+$requestedProduct->total_used_weight;
						if($requestedProduct->status==0)
						{
							$requestedProductValidStatus=0;
						}
					}
				}
				
				$Request->total_gross_weight=$total_gross_weight;
				$Request->total_net_weight=$total_net_weight;
				$Request->total_certified_weight=$total_certified_weight;
				$Request->total_wastage_weight=$total_wastage_weight;
				$Request->grand_total_net_weight=$grand_total_net_weight;
				$Request->grand_total_used_weight=$grand_total_used_weight;				
				
				$overall_input_status=0;
				if($requestedProductValidStatus==0){
					$overall_input_status = $RequestModel->arrEnumOverallInputStatus['open'];				
				}else{
					$overall_input_status = $RequestModel->arrEnumOverallInputStatus['input_added'];
				}
				$Request->overall_input_status = $overall_input_status;
				
				$Request->save();
			}	
		}	
	}

	public function actionChangeProductWastagePercentage(){
		//wastage_percentage
		//wastage_percentage

		//certified_weight

		// wastage_weight -- total_net_weight
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = yii::$app->request->post();		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		if($data)
		{
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$is_headquarters =$userData['is_headquarters'];
			$franchiseid=$userData['franchiseid'];
			$role_chkid=$userData['role_chkid'];

			$RequestProduct = RequestProduct::find()->where(['id'=>$data['tc_request_product_id'] ])->one();
			if($RequestProduct !== null)
			{
				$showedit= $this->canEditTc($RequestProduct->request);
				if($showedit==0){
					return $responsedata;
				}
				
				$wastage_percentage = $data['wastage_percentage'];
				$net_weight = $RequestProduct->net_weight;
				$additional_weight = $RequestProduct->additional_weight;
				$net_weight = $net_weight - $additional_weight;
				//$wastage_weight = ($net_weight*$wastage_percentage)/100;
				//$wastage_weight = ($net_weight/(100-$wastage_percentage))*100;
				if($wastage_percentage>0){
					$wastage_weight = (($net_weight/(100-$wastage_percentage))*100 ) - $net_weight;
				}else{
					$wastage_weight = 0.00;
				}
				

				$RequestProduct->wastage_weight =  $wastage_weight;
				$RequestProduct->wastage_percentage = $wastage_percentage;
				//$additional_weight = $RequestProduct->additional_weight;

				//$RequestProduct->total_net_weight = number_format($net_weight+$wastage_weight-$additional_weight,2,'.','');
				$RequestProduct->total_net_weight = number_format($net_weight+$wastage_weight,2,'.','');
				if($RequestProduct->total_net_weight<1){
					return $responsedata = ['status'=>0,'message'=>'Raw Material Required must be greater than 1'];
				}else if($RequestProduct->save())
				{
					$productStatus=0;
					if($RequestProduct->total_used_weight>=$RequestProduct->total_net_weight)
					{
						$productStatus=$RequestProduct->arrEnumStatus['input_added'];
					}else{
						$productStatus=$RequestProduct->arrEnumStatus['open'];
					}	
					$RequestProduct->status = $productStatus;
					$RequestProduct->save();
				}
				
				// Update Product Weight To Request Table		
				$this->updateProductWeightToRequest($RequestProduct->tc_request_id);
				
				$productdetails = $this->gettcproductdata($data['tc_request_product_id']);

				$responsedata = ['status'=>1,'message'=>'Wastage percentage updated successfully','productdetails'=>$productdetails];
			}
			
		}
		return $responsedata;
	}

	public function actionChangeAdditionalWeight(){
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = yii::$app->request->post();		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		if($data)
		{
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$is_headquarters =$userData['is_headquarters'];
			$franchiseid=$userData['franchiseid'];
			$role_chkid=$userData['role_chkid'];

			$RequestProduct = RequestProduct::find()->where(['id'=>$data['tc_request_product_id'] ])->one();
			if($RequestProduct !== null)
			{
				$showedit= $this->canEditTc($RequestProduct->request);
				if($showedit==0){
					return $responsedata;
				}
				
				$additional_weight = $data['additional_weight'];
				$cur_net_weight = $RequestProduct->net_weight;
				$cur_net_weight = $cur_net_weight - $additional_weight;				
				$wastage_percentage = $RequestProduct->wastage_percentage;										
				
				$cur_wastage_weight = 0;				
				// ------- Calculate the Wastage Weight Based on Additional Weight Code Start Here --------			
				if($wastage_percentage>0){
					$cur_wastage_weight = (($cur_net_weight/(100-$wastage_percentage))*100 ) - $cur_net_weight;
					$RequestProduct->wastage_weight =  $cur_wastage_weight;				
				}else{
					$RequestProduct->wastage_weight =  $cur_wastage_weight;				
				}				
				// ------- Calculate the Wastage Weight Based on Additional Weight Code Start Here --------
				
				//return $responsedata = ['status'=>0,'message'=>$wastageW.''.$cur_net_weight.'---'.$additional_weight.'---'.$cur_wastage_weight];
				
				//$total_net_weight = $cur_net_weight + $cur_wastage_weight - $additional_weight;
				$total_net_weight = $cur_net_weight + $cur_wastage_weight;
				//$total_net_weight = (($cur_net_weight + $cur_wastage_weight) / ( 100 - $additional_weight)) * 100;

				$RequestProduct->additional_weight = number_format($additional_weight,2,'.','');
				$RequestProduct->total_net_weight = number_format($total_net_weight,2,'.','');
				if($total_net_weight<1){
					return $responsedata = ['status'=>0,'message'=>'Raw Material Required must be greater than 1'];
				}else if($RequestProduct->save())
				{
					$productStatus=0;
					if($RequestProduct->total_used_weight>=$RequestProduct->total_net_weight)
					{
						$productStatus=$RequestProduct->arrEnumStatus['input_added'];
					}else{
						$productStatus=$RequestProduct->arrEnumStatus['open'];
					}	
					$RequestProduct->status = $productStatus;
					$RequestProduct->save();
				}
				
				// Update Product Weight To Request Table		
				$this->updateProductWeightToRequest($RequestProduct->tc_request_id);
				
				$productdetails = $this->gettcproductdata($data['tc_request_product_id']);

				$responsedata = ['status'=>1,'message'=>'Additional weight updated successfully','productdetails'=>$productdetails];
			}
			
		}
		return $responsedata;
	}
	


	public function gettcproductdata($tc_request_product_id){

		$pdtdata = RequestProduct::find()->where(['id'=>$tc_request_product_id])->one();
		$productdata = [];
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		if($pdtdata !== null){
			//foreach ($RequestProduct as $pdtdata) {
			$productname = '';

			$Unitproduct = $pdtdata->unitproduct;
			$completepdtname = '';
			if($Unitproduct!== null){
				$productstd = $Unitproduct->product;
				if($productstd!==null){
					$standard_name = $productstd->standard->name;
					$labelgradename = $productstd->label_grade_name;

					$productname = $productstd->appproduct->product_name;
					$producttypename = $productstd->appproduct->product_type_name;

					$wastage = $productstd->appproduct->wastage;
					$materialcompositionname = '';
					if(count($productstd->productmaterial) >0){
						foreach($productstd->productmaterial as $productmaterial){

							$productMaterialList[]=[
								'app_product_id'=>$productmaterial->app_product_id,
								'material_id'=>$productmaterial->material_id,
								'material_name'=>$productmaterial->material_name,
								'material_type_id'=>$productmaterial->material_type_id,
								'material_type_name'=> $productmaterial->material_type_name,//material->material_type[$productmaterial->material_type_id],
								'material_percentage'=>$productmaterial->percentage
							];
							$materialcompositionname = $materialcompositionname.$productmaterial->percentage.'% '.$productmaterial->material_name.' + ';

						}
						$materialcompositionname = rtrim($materialcompositionname," + ");
					}
					$completepdtname = $productname.' | '.$producttypename.' | '.$wastage.'% wastage | '.$materialcompositionname.' | '.$standard_name.' | '.$labelgradename;
					//.' | '.$productname.' | '.$productname.' | '.$productname;
					//$wastage = $productstd->appproduct->product->wastage;

					//$producttypename = $productstd->appproduct->producttype->name;
					//$producttypename = $productstd->appproduct->producttype->name;
					//$producttypename = $productstd->appproduct->producttype->name;


					//acc | org acc | 22% wastage | 100% organic | gots | organic
				}
				
				
			}

			$materialused = [];
			$totalweightusedfrommaterial=0;
			$TcRawMaterialUsedWeight = TcRawMaterialUsedWeight::find()->where(['tc_request_product_id'=>$pdtdata->id])->all();
			if(count($TcRawMaterialUsedWeight)>0){
				foreach($TcRawMaterialUsedWeight as $UsedWeightObj){
					$totalweightusedfrommaterial+=$UsedWeightObj->used_weight;
					$materialused[] = [
						'stock_weight' => $UsedWeightObj->stock_weight,
						'used_weight' =>  $UsedWeightObj->used_weight,
						'remaining_weight' => $UsedWeightObj->remaining_weight,
						'supplier_name' => $UsedWeightObj->rawmaterial->supplier_name,						
						'tc_number' => $UsedWeightObj->rawmaterial->tc_number,
						'tc_attachment' => $UsedWeightObj->rawmaterial->tc_attachment,
						'raw_material_id' => $UsedWeightObj->tc_raw_material_id,
						'raw_material_product_id' => $UsedWeightObj->tc_raw_material_product_id,
						'trade_name' => $UsedWeightObj->rawmaterialproduct->trade_name,
						'product_name' => $UsedWeightObj->rawmaterialproduct->product_name,
						'lot_number' => $UsedWeightObj->rawmaterialproduct->lot_number
					];
				}
			}

			$rawmaterialusedlist = [];
			if(count($pdtdata->usedweight)>0){
				foreach($pdtdata->usedweight as $materialpdtused){
					$rawmaterialusedlist[]=[
						'tc_raw_material_id' => $materialpdtused->tc_raw_material_id,
						'tc_raw_material_product_id' => $materialpdtused->tc_raw_material_product_id,
						'product_id' => $materialpdtused->product_id,
						'stock_weight' => $materialpdtused->stock_weight,
						'used_weight' => $materialpdtused->used_weight,
						'remaining_weight' => $materialpdtused->remaining_weight
					];
				}
			}
			$productdata = [
				'id' => $pdtdata->id,
				'tc_request_id' => $pdtdata->tc_request_id,
				'trade_name' => $pdtdata->trade_name,
				'product_id' => $pdtdata->product_id,
				'product_name' => $completepdtname,
				'packed_in' => $pdtdata->packed_in,
				'lot_ref_number' => $pdtdata->lot_ref_number,
				'consignee_id' => $pdtdata->consignee_id,

				'unit_information' => $pdtdata->unit_information,
				'purchase_order_no' => $pdtdata->purchase_order_no,
				'purchase_order_date' => date($date_format,strtotime($pdtdata->purchase_order_date)),
				'invoice_no' => $pdtdata->invoice_no,
				'transport_document_no' => $pdtdata->transport_document_no,
				'transport_company_name' => $pdtdata->transport_company_name?:'NA',
				'vehicle_container_no' => $pdtdata->vehicle_container_no?:'NA',
				'invoice_date' => date($date_format,strtotime($pdtdata->invoice_date)),
				'transport_document_date' => date($date_format,strtotime($pdtdata->transport_document_date)),
				'transport_id' => $pdtdata->transport_id,
				'transport_id_label' => $pdtdata->transport?$pdtdata->transport->name:'',
				'wastage_percentage' => $pdtdata->wastage_percentage,
				'gross_weight' => $pdtdata->gross_weight,
				'net_weight' => $pdtdata->net_weight,
				'certified_weight' => $pdtdata->certified_weight,
				'wastage_weight' => $pdtdata->wastage_weight,
				'additional_weight' => $pdtdata->additional_weight,
				'total_net_weight' => $pdtdata->total_net_weight,
				'total_used_weight' => $pdtdata->total_used_weight,
				'product_status'=> $pdtdata->status,
				'totalweightusedfrommaterial' => $totalweightusedfrommaterial,
				'materialused' => $materialused,
				'rawmaterialusedlist' => $rawmaterialusedlist
				
				
			];
			//}
		}
		return $productdata;
	}	

	public function actionCopyrequestdetails(){

		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data && $data['id']) 
		{
			$userData = Yii::$app->userdata->getData();
			$modelorg = Request::find()->where(['id' => $data['id']])->one();
			
			$cancopy = $this->canCopyTc($modelorg);
			if($cancopy == 0){
				return $responsedata;
			}
			if($modelorg !== null){
 
				$model = new Request();

				$model->company_name = $modelorg->company_name;	
				$model->unit_name = $modelorg->unit_name;	
				$model->address_id = $modelorg->address_id;	
				

				$model->app_id = $modelorg->app_id;	
				$model->unit_id = $modelorg->unit_id;	

				$model->buyer_id = $modelorg->buyer_id;	
				$model->consignee_id = $modelorg->consignee_id;
				$model->standard_id = $modelorg->standard_id;	
				$model->purchase_order_number = $modelorg->purchase_order_number;	
				$model->inspection_body_id = $modelorg->inspection_body_id;	
				$model->country_of_dispach = $modelorg->country_of_dispach;
				$model->country_of_destination = $modelorg->country_of_destination;			
				$model->usda_nop_compliant = $modelorg->usda_nop_compliant;	
				//$model->apeda_npop_compliant = $modelorg->apeda_npop_compliant;	
				$model->comments = $modelorg->comments;	
				$model->declaration = $modelorg->declaration;	
				$model->additional_declaration = $modelorg->additional_declaration;	
				$model->standard_declaration = $modelorg->standard_declaration;	
				
				$model->grand_total_net_weight = $modelorg->grand_total_net_weight;
				$model->total_wastage_weight = $modelorg->total_wastage_weight;
				$model->total_certified_weight = $modelorg->total_certified_weight;
				$model->total_net_weight = $modelorg->total_net_weight;
				$model->total_gross_weight = $modelorg->total_gross_weight;
				//$model->visible_to_brand = $modelorg->visible_to_brand;
				//$model->transport_id = $modelorg->transport_id;

				$model->status = 0;
				$model->created_by = $userData['userid'];			
				if($model->validate() && $model->save())
				{	
					

					$manualID = $model->id;

					//$model->tc_number_temp = $manualID;
					//$model->save();

					$existingstandard = [];
					$RequestStandard = RequestStandard::find()->where(['tc_request_id' =>  $data['id'] ])->all();
					if(count($RequestStandard)>0){
						foreach($RequestStandard as $rstandard){
							$requeststd = new RequestStandard();
							$requeststd->tc_request_id = $manualID;
							$requeststd->standard_id = $rstandard->standard_id;
							$requeststd->save();
						}
					}

					$ifoamStandard = TcRequestIfoamStandard::find()->where(['tc_request_id' =>  $data['id'] ])->all();
					if(count($ifoamStandard)>0)
					{
						foreach ($ifoamStandard as $value)
						{ 
							$requeststd = new TcRequestIfoamStandard();
							$requeststd->tc_request_id = $manualID;
							$requeststd->ifoam_standard_id = $value->ifoam_standard_id;
							$requeststd->save();
						}
					}
					

					$TcRequestProductModel = RequestProduct::find()->where(['tc_request_id'=>$data['id']])->all();
			 		if(count($TcRequestProductModel)>0)
					{
						foreach($TcRequestProductModel as $productModel){
							$RequestProduct = new RequestProduct();
							$RequestProduct->tc_request_id = $manualID;

							$RequestProduct->standard_id = $productModel->standard_id;
							$RequestProduct->product_id = $productModel->product_id;	
							$RequestProduct->trade_name = $productModel->trade_name;	
							$RequestProduct->packed_in = $productModel->packed_in;
							$RequestProduct->lot_ref_number = $productModel->lot_ref_number;
							$RequestProduct->consignee_id = $productModel->consignee_id;
							
								
							$RequestProduct->gross_weight = $productModel->gross_weight;
							$RequestProduct->net_weight = $productModel->net_weight;	
							$RequestProduct->certified_weight = $productModel->certified_weight;	
							$RequestProduct->wastage_percentage = $productModel->wastage_percentage;
							$RequestProduct->product_wastage_percentage = $productModel->product_wastage_percentage;

							$RequestProduct->unit_information = $productModel->unit_information;
							$RequestProduct->purchase_order_no = $productModel->purchase_order_no;
							$RequestProduct->purchase_order_date = $productModel->purchase_order_date;							
							$RequestProduct->invoice_no = $productModel->invoice_no;
							$RequestProduct->invoice_date = $productModel->invoice_date;
							$RequestProduct->transport_document_no = $productModel->transport_document_no;
							$RequestProduct->transport_company_name = $productModel->transport_company_name;
							$RequestProduct->vehicle_container_no = $productModel->vehicle_container_no;

							$RequestProduct->transport_document_date =  $productModel->transport_document_date;
							$RequestProduct->transport_id = $productModel->transport_id;
							 
							$RequestProduct->wastage_weight = $productModel->wastage_weight;	
							$RequestProduct->additional_weight = $productModel->additional_weight;
							$RequestProduct->total_net_weight = $productModel->total_net_weight;
							
							$RequestProduct->transport_company_name = $productModel->transport_company_name;
							$RequestProduct->vehicle_container_no = $productModel->vehicle_container_no;
							
							$RequestProduct->created_by = $userData['userid'];
							$RequestProduct->status = 0;
							$RequestProduct->save();
						}
					}
					
					if(count($modelorg->evidence)>0)
					{
						$target_dir = Yii::$app->params['tc_files']."evidence_files/"; 
						foreach($modelorg->evidence as $evidence)
						{
							$evidence_file='';
							if($evidence->evidence_file!='')
							{
								$evidence_file=Yii::$app->globalfuns->copyFiles($evidence->evidence_file,$target_dir);
								$RequestEvidence = new RequestEvidence();
								$RequestEvidence->evidence_file = $evidence_file;
								$RequestEvidence->tc_request_id = $manualID;
								$RequestEvidence->evidence_type = $evidence->evidence_type;
								$RequestEvidence->save();
							}

							
						}
						
					}


				}
				$responsedata = ['status'=>1,'message'=>'TC Request cloned successfully','newid'=> $manualID ];
			}
		}
		return $responsedata;	
	}

	public function actionGetaddressdetails(){

		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data && $data['id']) 
		{
			$addressdetails = [];
			//if($data['type'] == 'buyer'){
				$Buyer = Buyer::find()->where(['id'=>$data['id']])->one();
				if($Buyer !== null){
					$addressdetails['address'] = $Buyer->address;
					$addressdetails['country'] = $Buyer->country->name;
					$addressdetails['state'] = $Buyer->state->name;
					$addressdetails['city'] = $Buyer->city;
					$addressdetails['zipcode'] = $Buyer->zipcode;
					$addressdetails['email'] = $Buyer->email;
					$addressdetails['phonenumber'] = $Buyer->phonenumber; 

				}
			//}
			//$userData = Yii::$app->userdata->getData();
			//$modelorg = Request::find()->where(['id' => $data['id']])->one();
			
		}
		return $responsedata;
	}
	
}
