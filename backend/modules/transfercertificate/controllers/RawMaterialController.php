<?php
namespace app\modules\transfercertificate\controllers;

use Yii;
use app\modules\transfercertificate\models\RawMaterial;
use app\modules\transfercertificate\models\RawMaterialStandard;
use app\modules\transfercertificate\models\RawMaterialLabelGrade;
use app\modules\transfercertificate\models\RawMaterialProduct;
use app\modules\transfercertificate\models\RequestProduct;

use app\modules\transfercertificate\models\TcRawMaterialUsedWeight;
use app\modules\transfercertificate\models\TcRequestProductInputMaterial;

use app\modules\transfercertificate\models\TcStandardCombination;
use app\modules\transfercertificate\models\TcStandard;
use app\modules\transfercertificate\models\TcStandardLabelGrade;
use app\modules\transfercertificate\models\Request;
use app\modules\transfercertificate\models\RawMaterialHistory;
use app\modules\transfercertificate\models\InspectionBody;
use app\modules\transfercertificate\models\RawMaterialFileHistory;

use app\modules\application\models\Application;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * RawMaterialController implements the CRUD actions for Product model.
 */
class RawMaterialController extends \yii\rest\Controller
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
					'standardwisematerial',
					'generatetc',					
				]
			]
		];        
    }
	
	
	public function actionIndex()
    {
		$post = yii::$app->request->post();		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelObj = new RawMaterial();		
		if($post)
		{
			
			if(!Yii::$app->userrole->isCustomer() && !Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->hasRights(['raw_material']))
			{
				return false;
			}

			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$rules=$userData['rules'];
			$resource_access=$userData['resource_access'];
			$is_headquarters =$userData['is_headquarters'];
			$franchiseid=$userData['franchiseid'];
			$role_chkid=$userData['role_chkid'];
			
			$model = RawMaterial::find()->where(['<>','t.status',$modelObj->enumStatus['archived'] ])->alias('t');				

			if(isset($post['type']) && $post['type'] !='')
			{
				$model->andWhere(['t.type'=> $post['type']]);				
			}

			if(isset($post['certifiedFilter']) && $post['certifiedFilter'] !='')
			{
				$model->andWhere(['t.is_certified'=> $post['certifiedFilter']]);				
			}

			if($resource_access != '1')
			{
				if($user_type== Yii::$app->params['user_type']['customer']){
					$model = $model->andWhere('t.created_by="'.$userid.'"');
				}	
				if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1 ){
					$model = $model->innerJoinWith(['createdbydata as createdbydata']);	
					$model = $model->andWhere(' createdbydata.franchise_id="'.$franchiseid.'" ');
				}
			}

			if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
			{
				$page = ($post['page'] - 1)*$post['pageSize'];
				$pageSize = $post['pageSize']; 
				
				if(isset($post['searchTerm']))
				{
					$searchTerm = $post['searchTerm'];					
					
					$model = $model->andFilterWhere([
						'or',
						['like', 't.supplier_name', $searchTerm],
						//['like', 't.lot_number', $searchTerm],
						//['like', 't.trade_name', $searchTerm],
						//['like', 't.product_name', $searchTerm],
						['like', 't.gross_weight', $searchTerm],
						['like', 't.certified_weight', $searchTerm],
						['like', 't.tc_number', $searchTerm],
						['like', 't.net_weight', $searchTerm],												
						['like', 'date_format(FROM_UNIXTIME(t.`created_at` ), \'%b %d, %Y\' )', $searchTerm]
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
			
			$list=array();
			$model = $model->all();		
			if(count($model)>0)
			{
				foreach($model as $modelData)
				{	
					$data=array();
					$data['id']=$modelData->id;
					$data['supplier_name']=$modelData->supplier_name;
					//$data['lot_number']=$modelData->lot_number;
					$data['is_certified']=$modelData->is_certified;
					$data['is_certified_label']=($modelData->is_certified==1?"Yes":($modelData->is_certified==2?"No":"Reclaim"));
					$data["certification_body_id"]=$modelData->certification_body_id;
					$data["certification_body_name"]=$modelData->certificationbody?$modelData->certificationbody->name:'';
					$data['net_weight'] = $modelData->actual_net_weight;
					$data['balance_weight'] = $modelData->net_weight;
					$data['used_weight'] = $modelData->total_used_weight;
					if($modelData->is_certified == 3){
						$data["invoice_number"]=$modelData->invoice_number;
						$data["invoice_attachment"]=$modelData->invoice_attachment;
						$data["declaration_attachment"]=$modelData->declaration_attachment;
					}elseif($modelData->is_certified == 1){
						
						$data["tc_number"]=$modelData->tc_number;
						$data["tc_attachment"]=$modelData->tc_attachment;
						$data["form_sc_number"]=$modelData->form_sc_number;
						$data["form_sc_attachment"]=$modelData->form_sc_attachment;
						$data["form_tc_number"]=$modelData->form_tc_number;
						$data["form_tc_attachment"]=$modelData->form_tc_attachment;
						$data["trade_tc_number"]=$modelData->trade_tc_number;
						$data["trade_tc_attachment"]=$modelData->trade_tc_attachment;						

						$materialStd = $modelData->standard;
						if(count($materialStd)>0)
						{
							$materialStdids = [];
							$materialStdnames = [];
							foreach($materialStd as $std)
							{
								$materialStdvals[] = $std['standard_id'];
								$materialStdids[] = "".$std['standard_id'];
								$materialStdnames[] = $std->standard->name;
							}
							$data["standard_id"]=$materialStdids;
							$data["standard_id_val"]=$materialStdvals;
							$data["standard_name"]=implode(',',$materialStdnames);
						}

						// $materiallabelgrade = $modelData->labelgrade;
						// if(count($materiallabelgrade)>0)
						// {
						// 	$labelgradeids = [];
						// 	$labelgradenames = [];
						// 	foreach($materiallabelgrade as $label)
						// 	{
						// 		$labelgradeids[]="".$label['label_grade_id'];
						// 		$labelgradenames[]=$label->labelgrade->name;
						// 	}
						// 	$data["label_grade_id"]=$labelgradeids;
						// 	$data["label_grade_name"]=implode(',',$labelgradenames);
						// }
					}
					else
					{
						$data["invoice_number"]=$modelData->invoice_number;
					}

					
					
					
					$data['created_by_label']=$modelData->createdbydata->first_name.' '.$modelData->createdbydata->last_name;
					$data['status_label']=$modelObj->arrStatus[$modelData->status];
					$data['created_at']=date($date_format,$modelData->created_at);				

					$list[]=$data;
				}
			}
		}
		return ['rawmaterial'=>$list,'total'=>$totalCount];
	}

	public function actionGetFilterOptions()
    {
		$modelObj = new RawMaterial();		
		return ['filteroptions'=>$modelObj->arrcertifiedStatus];	
	}
	
	public function actionView()
    {
		$data = Yii::$app->request->post();
		if($data && isset($data['id']))
		{
			if(!Yii::$app->userrole->isCustomer() && !Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->hasRights(['raw_material']) )
			{
				return false;
			}
			
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
			$modelObj = new RawMaterial();

		   // $model = $this->findModel($data['id']);
			$model = RawMaterial::find()->where(['id' => $data['id']]);		
			$userData = Yii::$app->userdata->getData();
			$franchiseid=$userData['franchiseid'];
			$userid=$userData['userid'];
			if(Yii::$app->userrole->isOSSUser())
			{
				$model = $model->innerJoinWith(['createdbydata as createdbydata']);	
				$model = $model->andWhere(' createdbydata.franchise_id="'.$franchiseid.'" ');
			}elseif(Yii::$app->userrole->isCustomer())
			{
				$model = $model->andWhere(['created_by' => $userid]);
			}		
			$model = $model->one();

			if ($model !== null)
			{
				$resultarr=array();
				$resultarr["id"]=$model->id;
				$resultarr["updated_at"]=$model->updated_at;
				$resultarr["supplier_name"]=$model->supplier_name;
				//$resultarr["lot_number"]=$model->lot_number;
				$resultarr["is_certified"]=$model->is_certified;
				$resultarr["is_certified_label"]=$model->arrcertifiedStatus[$model->is_certified];
				
				if($model->is_certified == 3){
					$resultarr["invoice_number"]=$model->invoice_number;
					$resultarr["invoice_attachment"]=$model->invoice_attachment;
					$resultarr["declaration_attachment"]=$model->declaration_attachment;
				}elseif($model->is_certified == 1){
					$resultarr["tc_number"]=$model->tc_number;
					$resultarr["tc_attachment"]=$model->tc_attachment;
					$resultarr["form_sc_number"]=$model->form_sc_number;
					$resultarr["form_sc_attachment"]=$model->form_sc_attachment;
					$resultarr["form_tc_number"]=$model->form_tc_number;
					$resultarr["form_tc_attachment"]=$model->form_tc_attachment;
					$resultarr["trade_tc_number"]=$model->trade_tc_number;
					$resultarr["trade_tc_attachment"]=$model->trade_tc_attachment;

					$materialStd = $model->standard;
					if(count($materialStd)>0)
					{
						$materialStdids = [];
						$materialStdnames = [];
						foreach($materialStd as $std)
						{
							$materialStdids[] = "".$std['standard_id'];
							$materialStdnames[] = $std->standard->name;
						}
						$resultarr["standard_id"]=$materialStdids;
						$resultarr["standard_name"]=$materialStdnames;
					}
				}
				else
				{
					$resultarr["invoice_number"]=$model->invoice_number;
				}

				$productarr = [];
				$materialproduct = $model->product;
				if(count($materialproduct)>0)
				{	
					foreach ($materialproduct as $value)
					{ 		
						$productdata = array();
						$productdata['raw_material_product_id'] = $value->id;
						$productdata["lot_number"]=$value->lot_number;
						$productdata['trade_name'] = $value->trade_name;
						$productdata['product_name'] = $value->product_name;
						$productdata['balance_weight'] = $value->net_weight;
						$productdata['gross_weight'] = $value->gross_weight;
						$productdata['certified_weight'] = $value->certified_weight;
						$productdata['net_weight'] = $value->actual_net_weight;
						$productdata['actual_net_weight'] = $value->actual_net_weight;
						$productdata['used_weight'] = $value->total_used_weight;
						$productdata['is_product_used'] = 0;
						if($value->anytcusedweight!==null){
							$productdata['is_product_used'] = 1;
						}
						$materiallabelgrade = $value->labelgrade;
						if(count($materiallabelgrade)>0)
						{
							$labelgradeids = [];
							$labelgradenames = [];
							foreach($materiallabelgrade as $label)
							{
								$labelgradeids[]="".$label['label_grade_id'];
								$labelgradenames[]=$label->labelgrade->name;
							}
							$productdata["label_grade_id"]=$labelgradeids;
							$productdata["label_grade_name"]=implode(',',$labelgradenames);
						}

						$productarr[] = $productdata;
					}
				}
				$resultarr['products'] = $productarr;					
				
				$totalusedweight = 0;
				
				if($data['type']=="view")
				{
					$usedweight = $model->usedweightlistonly;
					if(count($usedweight)>0)
					{
						
						$usedproductarr = [];
						foreach ($usedweight as $value)
						{ 	
							$productarr = array();
							$productarr['tc_request_product_id'] = $value->tc_request_product_id;
							$productarr['product_name'] = $this->getproductname($value->tc_request_product_id);
							$productarr['used_weight'] = $value->used_weight;
							$productarr['tc_nos']=$model->tc_number;
							$productarr['created_at'] = date($date_format,$value->created_at);
							$usedproductarr[] = $productarr;

							$totalusedweight += $value->used_weight;
						}
						$resultarr['used_products'] = $usedproductarr;
					}

					$rawmaterialhistory = $model->rawmaterialhistory;
					if(count($rawmaterialhistory)>0)
					{
						
						$historyarr = [];
						foreach ($rawmaterialhistory as $historyvalue)
						{ 	
							$historyrow = array();
							$historyrow['raw_material_id'] = $historyvalue->raw_material_id;
							$historyrow['activity'] = $historyvalue->activity;
							$historyrow['created_by'] = $historyvalue->createdbydata->first_name.' '.$historyvalue->createdbydata->last_name;
							$historyrow['created_at'] = date($date_format,$historyvalue->created_at);							
							
							$filehistoryarr = [];
							$arrFileType = $modelObj->arrFileType;
							$rawmaterialfilehistory = $historyvalue->rawmaterialfilehistory;
							if(count($rawmaterialfilehistory)>0)
							{								
								foreach ($rawmaterialfilehistory as $historyfilevalue)
								{ 
									$filehistoryarr[]=array('raw_material_history_file_id'=>$historyfilevalue->id,'raw_material_file_old'=>$historyfilevalue->raw_material_file_old,'raw_material_file_new'=>$historyfilevalue->raw_material_file_new,'raw_material_file_type'=>$historyfilevalue->raw_material_file_type,'raw_material_file_type_label'=>$arrFileType[$historyfilevalue->raw_material_file_type]);
								}
							}	
							$historyrow['history_files'] = $filehistoryarr;
							$historyarr[] = $historyrow;
						}
						$resultarr['rawmaterial_history'] = $historyarr;
					}
				}
				
				$resultarr['total_used_weight'] = $model->total_used_weight;
				$resultarr["certification_body_id"]=$model->certification_body_id;
				$resultarr["certification_body_name"]=$model->certificationbody?$model->certificationbody->name:'';
				$resultarr["gross_weight"]=$model->gross_weight;
				$resultarr["certified_weight"]=$model->certified_weight;
				$resultarr["net_weight"]=$model->actual_net_weight;
				$resultarr['balance_weight'] = $model->net_weight;
				$resultarr['used_weight'] = $totalusedweight;	
				
				$resultarr['created_by_label']=$model->createdbydata->first_name.' '.$model->createdbydata->last_name;
				$resultarr['status']=$model->status;
				$resultarr['status_label']=$modelObj->arrStatus[$model->status];
				$resultarr['created_at']=date($date_format,$model->created_at);

				return ['data'=>$resultarr];
			}
		}
	}

    public function getrawmaterialproducts($rawmaterialID){

    	
    	$RawMaterial = RawMaterial::find()->where(['id'=>$rawmaterialID])->one();
		//$RequestProduct = RequestProduct::find()->where(['tc_request_id'=>$tc_request_id])->all();
		$productdata = [];
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		if($RawMaterial !== null){

			if(count($RawMaterial->Usedweightlist) >0){

				foreach ($RawMaterial->Usedweightlist as $RequestProduct) {
					if(count($RequestProduct)>0){
						foreach ($RequestProduct as $pdtdata) {
							$productname = '';

							$Unitproduct = $pdtdata->unitproduct;
							$completepdtname = '';
							if($Unitproduct!== null){
								$productstd = $Unitproduct->product;
								if($productstd!==null){
									$standard_name = $productstd->standard->name;
									$labelgradename = $productstd->labelgrade->name;

									$productname = $productstd->appproduct->product->name;
									$producttypename = $productstd->appproduct->producttype->name;

									$wastage = $productstd->appproduct->wastage;
									$materialcompositionname = '';
									if(count($productstd->productmaterial) >0){
										foreach($productstd->productmaterial as $productmaterial){

											$productMaterialList[]=[
												'app_product_id'=>$productmaterial->app_product_id,
												'material_id'=>$productmaterial->material_id,
												'material_name'=>$productmaterial->material->name,
												'material_type_id'=>$productmaterial->material_type_id,
												'material_type_name'=> $productmaterial->material->material_type[$productmaterial->material_type_id],
												'material_percentage'=>$productmaterial->percentage
											];
											$materialcompositionname = $materialcompositionname.$productmaterial->percentage.'% '.$productmaterial->material->name.' + ';

										}
										$materialcompositionname = rtrim($materialcompositionname," + ");
									}
									$completepdtname = $productname.' | '.$producttypename.' | '.$wastage.'% wastage | '.$materialcompositionname.' | '.$standard_name.' | '.$labelgradename;
									
								}
								
								
							}

							
							$productdata[] = [
								'id' => $pdtdata->id,
								
								'product_id' => $pdtdata->product_id,
								'product_name' => $completepdtname,
								
								
							];
						}
					}
				}
			}
		}
		return $productdata;
	}

	public function actionCheckstandardcombination()
	{
		$modelRawMaterial = new RawMaterial();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$editStatus=1;
			$connection = Yii::$app->getDb();
				

			
			$standard_ids = $data['standard_id'];
			if(is_array($standard_ids)){
				$standard_ids = array_unique($standard_ids);
			}
			

			if(is_array($standard_ids) && count($standard_ids)>1)
			{
				
				$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();

				$TcStandardCombination = TcStandardCombination::find()->where(['status'=>0])->alias('t');
				$TcStandardCombination = $TcStandardCombination->join('inner join', 'tbl_tc_standard_combination_standard as combination','t.id =combination.tc_standard_combination_id');
				$TcStandardCombination = $TcStandardCombination->andWhere(['combination.tc_standard_id'=>$standard_ids]);
				$TcStandardCombination = $TcStandardCombination->one();
				if($TcStandardCombination!==null){
					
					sort($standard_ids);

					$command = $connection->createCommand("select GROUP_CONCAT(combstd.tc_standard_id order by combstd.tc_standard_id asc ) as standardids from tbl_tc_standard_combination as comb inner join tbl_tc_standard_combination_standard as combstd on comb.id=combstd.tc_standard_combination_id where combstd.tc_standard_id in (".implode(',',$standard_ids).") GROUP BY comb.id HAVING standardids = '".implode(',',$standard_ids)."'");

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
		$modelRawMaterial = new RawMaterial();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$target_dir = Yii::$app->params['tc_files']."raw_material_files"; 
		$datapost = Yii::$app->request->post();
		if ($datapost) 
		{	
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$editStatus=1;
			$connection = Yii::$app->getDb();
			$data =json_decode($datapost['formvalues'],true);
			$changes = [];
			
			
			if(isset($data['id']) && Yii::$app->userrole->isUser())
			{
				if(!Yii::$app->userrole->hasRights(['edit_raw_material']))
				{
					return false;
				}				
			}elseif(!Yii::$app->userrole->isCustomer()){			
				return false;
			}
			if(!isset($data['products']) || count($data['products'])<=0){
				//return $responsedata;
				$responsedata=array('status'=>0,'message'=>'You are currently using old version. To get the latest update, please press the "Ctrl+F5" to refresh the browser\'s cache.');
				return $responsedata;
			}
			//return false;
			//die;
			if($data['is_certified'] =="1"){
				$standard_ids = $data['standard_id'];
				if(is_array($standard_ids) && count($standard_ids)>1)
				{
					$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();

					$TcStandardCombination = TcStandardCombination::find()->where(['status'=>0])->alias('t');
					$TcStandardCombination = $TcStandardCombination->join('inner join', 'tbl_tc_standard_combination_standard as combination','t.id =combination.tc_standard_combination_id');
					$TcStandardCombination = $TcStandardCombination->andWhere(['combination.tc_standard_id'=>$standard_ids]);
					$TcStandardCombination = $TcStandardCombination->one();
					if($TcStandardCombination!==null){
						
						sort($standard_ids);

						$command = $connection->createCommand("select GROUP_CONCAT(combstd.tc_standard_id order by combstd.tc_standard_id asc ) as standardids from tbl_tc_standard_combination as comb inner join tbl_tc_standard_combination_standard as combstd on comb.id=combstd.tc_standard_combination_id where combstd.tc_standard_id in (".implode(',',$standard_ids).") GROUP BY comb.id HAVING standardids = '".implode(',',$standard_ids)."'");

						$result = $command->queryOne();
						if($result  === false)
						{
							return $responsedata=array('status'=>0,'message'=>["standard_id"=>["Standard Combination is not invalid"]]);
						}

						//return $responsedata=array('status'=>0,'message'=>'Found');
					}
				}
			}
			//return $responsedata;
			if(isset($data['id']) && $data['id']>0)
			{
				$franchiseid=$userData['franchiseid'];
				$userid=$userData['userid'];
				$model = RawMaterial::find()->where(['t.id' => $data['id']])->alias('t');				
				if(Yii::$app->userrole->isOSSUser())
				{
					$model = $model->innerJoinWith(['createdbydata as createdbydata']);	
					$model = $model->andWhere(' createdbydata.franchise_id="'.$franchiseid.'" ');
				}elseif(Yii::$app->userrole->isCustomer())
				{
					$model = $model->andWhere(['created_by' => $userid]);
				}				
				$model = $model->one();
				
				
				//RawMaterialProduct::deleteAll(['raw_material_id' => $data['id']]);
				if($model===null)
				{
					$model = new RawMaterial();
					$editStatus=0;
					$model->created_by = $userData['userid'];	
				}else{
					if($model->updated_at != $data['updated_at']){
						return $responsedata=array('status'=>0,'message'=>"Raw Material was not having latest data. Please click edit icon again to get latest data to update.");
					}
					$model->updated_by = $userData['userid'];	
				}
			}else{
				$editStatus=0;
				$model = new RawMaterial();
				$model->created_by = $userData['userid'];	
			}	
			
			$model->supplier_name = $data['supplier_name'];	
			//$model->lot_number = $data['lot_number'];	
			// $model->trade_name = $data['trade_name'];
			// $model->product_name = $data['product_name'];
			$model->is_certified = $data['is_certified'];
			$model->certification_body_id = $data['certification_body_id'];
			// $model->gross_weight = $data['gross_weight'];	
			// $model->net_weight = $data['net_weight'];
			$model->trade_tc_attachment = '';
			$model->form_tc_attachment = '';
			$model->form_sc_attachment = '';
			$model->tc_attachment = '';
			$model->invoice_attachment = '';
			$model->declaration_attachment = '';
			if($data['is_certified']==3)
			{
				$model->invoice_number = $data['invoice_number'];
				
				if(isset($_FILES['invoice_attachment']['name']))
				{
					$tmp_name = $_FILES["invoice_attachment"]["tmp_name"];
					$name = $_FILES["invoice_attachment"]["name"];
					if($model!==null)
					{
						Yii::$app->globalfuns->removeFiles($model->invoice_attachment,$target_dir.'/');													
					}
					$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
				}else{
					$filename = $data['invoice_attachment'];
				}
				$model->invoice_attachment = $filename;
				
				if(isset($_FILES['declaration_attachment']['name']))
				{
					$tmp_name = $_FILES["declaration_attachment"]["tmp_name"];
					$name = $_FILES["declaration_attachment"]["name"];
					if($model!==null)
					{
						Yii::$app->globalfuns->removeFiles($model->declaration_attachment,$target_dir.'/');													
					}
					$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
				}else{
					$filename = $data['declaration_attachment'];
				}
				$model->declaration_attachment = $filename;
				
				$model->tc_number = '';
				$model->form_sc_number = '';
				$model->form_tc_number = '';
				$model->trade_tc_number = '';
				$model->certified_weight = '';
				
			}elseif($data['is_certified']==1){
				$model->tc_number = $data['tc_number'];
				$model->form_sc_number = isset($data['form_sc_number'])?$data['form_sc_number']:'';
				$model->form_tc_number = isset($data['form_tc_number'])?$data['form_tc_number']:'';
				$model->trade_tc_number = isset($data['trade_tc_number'])?$data['trade_tc_number']:'';
				$model->certified_weight = isset($data['certified_weight'])?$data['certified_weight']:'';

				if(isset($_FILES['tc_attachment']['name']))
				{
					$tmp_name = $_FILES["tc_attachment"]["tmp_name"];
					$name = $_FILES["tc_attachment"]["name"];
					if($model!==null)
					{
						//Yii::$app->globalfuns->removeFiles($model->tc_attachment,$target_dir.'/');													
					}
					$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
				}else{
					$filename = $data['tc_attachment'];
				}
				$model->tc_attachment = $filename;


				if(isset($_FILES['form_sc_attachment']['name']))
				{
					$tmp_name = $_FILES["form_sc_attachment"]["tmp_name"];
					$name = $_FILES["form_sc_attachment"]["name"];					
					if($model!==null)
					{
						//Yii::$app->globalfuns->removeFiles($model->form_sc_attachment,$target_dir.'/');													
					}
					$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
				}else{
					$filename = isset($data['form_sc_attachment'])?$data['form_sc_attachment']:'';
				}
				$model->form_sc_attachment = $filename;


				if(isset($_FILES['form_tc_attachment']['name']))
				{
					$tmp_name = $_FILES["form_tc_attachment"]["tmp_name"];
					$name = $_FILES["form_tc_attachment"]["name"];
					if($model!==null)
					{
						//Yii::$app->globalfuns->removeFiles($model->form_tc_attachment,$target_dir.'/');													
					}
					$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
				}else{
					$filename = isset($data['form_tc_attachment'])?$data['form_tc_attachment']:'';
				}
				$model->form_tc_attachment = $filename;


				if(isset($_FILES['trade_tc_attachment']['name']))
				{
					$tmp_name = $_FILES["trade_tc_attachment"]["tmp_name"];
					$name = $_FILES["trade_tc_attachment"]["name"];
					if($model!==null)
					{
						//Yii::$app->globalfuns->removeFiles($model->trade_tc_attachment,$target_dir.'/');													
					}
					$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
				}else{
					$filename = isset($data['trade_tc_attachment'])? $data['trade_tc_attachment']:'';
				}
				$model->trade_tc_attachment = $filename;
				$model->invoice_number = '';
			}
			else
			{
				$model->invoice_number = $data['invoice_number'];
				$model->tc_number = '';
				$model->form_sc_number = '';
				$model->form_tc_number = '';
				$model->trade_tc_number = '';
				$model->certified_weight = '';
				
			}
			
			
			if($model->validate() && isset($data['id']) && $data['id'] != '' && $data['id'] > 0){

				// Rawmaterial history code starts here 
				$RawMaterialModel = RawMaterial::find()->where(['t.id' => $data['id']])->alias('t')->one();
				$changefiledetails = [];
				$changefiledetailscontent = '';
				
				$RawMaterialFileHistoryArray=array();				
												
				//if($data['is_certified']==3)
				//{
					if(isset($_FILES['invoice_attachment']['name']))
					{
						//$changefiledetails[] = '<span class="label">Invoice Document:</span> <span class="entryvalue">'.$this->getLink($RawMaterialModel->invoice_attachment,$model->invoice_attachment).'</span>';
						$RawMaterialFileHistoryArray[]=array('raw_material_file_old'=>$RawMaterialModel->invoice_attachment,'raw_material_file_new'=>$model->invoice_attachment,'raw_material_file_type'=>'invoice_document');
						//"<a href="'.Yii::$app->params['site_path'].'web/transfercertificate/raw-material/downloadhistoryfile?file='.$RawMaterialModel->invoice_attachment.'" >'.$RawMaterialModel->invoice_attachment.'</a>" >> <a href="'.Yii::$app->params['site_path'].'web/transfercertificate/raw-material/downloadhistoryfile?file='.$model->invoice_attachment.'" >'.$model->invoice_attachment.'</a>';
					}else{
						if($RawMaterialModel->invoice_attachment != $model->invoice_attachment){
							//$changefiledetails[] = '<span class="label">Invoice Document:</span> <span class="entryvalue">'.$this->getLink($RawMaterialModel->invoice_attachment,$model->invoice_attachment).'</span>';
							$RawMaterialFileHistoryArray[]=array('raw_material_file_old'=>$RawMaterialModel->invoice_attachment,'raw_material_file_new'=>$model->invoice_attachment,'raw_material_file_type'=>'invoice_document');							
							//"<a href="'.Yii::$app->params['site_path'].'web/transfercertificate/raw-material/downloadhistoryfile?file='.$RawMaterialModel->invoice_attachment.'" >'.$RawMaterialModel->invoice_attachment.'</a>" >> "<a href="'.Yii::$app->params['site_path'].'web/transfercertificate/raw-material/downloadhistoryfile?file='.$model->invoice_attachment.'" >'.$model->invoice_attachment.'</a>"';
						}
					}
					if(isset($_FILES['declaration_attachment']['name']))
					{
						//$changefiledetails[] = '<span class="label">Declaration Document:</span> <span class="entryvalue">'.$this->getLink($RawMaterialModel->declaration_attachment,$model->declaration_attachment).'</span>';
						$RawMaterialFileHistoryArray[]=array('raw_material_file_old'=>$RawMaterialModel->declaration_attachment,'raw_material_file_new'=>$model->declaration_attachment,'raw_material_file_type'=>'declaration_document');
						//"'.$RawMaterialModel->declaration_attachment.'" => '.$model->declaration_attachment;
					}else{
						if($RawMaterialModel->declaration_attachment != $model->declaration_attachment){
							//$changefiledetails[] = '<span class="label">Declaration Document:</span> <span class="entryvalue">'.$this->getLink($RawMaterialModel->declaration_attachment,$model->declaration_attachment).'</span>';
							$RawMaterialFileHistoryArray[]=array('raw_material_file_old'=>$RawMaterialModel->declaration_attachment,'raw_material_file_new'=>$model->declaration_attachment,'raw_material_file_type'=>'declaration_document');
							//"'.$RawMaterialModel->declaration_attachment.'" => "'.$model->declaration_attachment.'"';
						}
					}
				//}elseif($data['is_certified']==1){
					if(isset($_FILES['tc_attachment']['name']))
					{
						//$changefiledetails[] = '<span class="label">TC Attachment:</span> <span class="entryvalue">'.$this->getLink($RawMaterialModel->tc_attachment,$model->tc_attachment).'</span>';
						$RawMaterialFileHistoryArray[]=array('raw_material_file_old'=>$RawMaterialModel->tc_attachment,'raw_material_file_new'=>$model->tc_attachment,'raw_material_file_type'=>'tc_attachment');
						//"'.$RawMaterialModel->tc_attachment.'" => '.$model->tc_attachment;
					}else{
						if($RawMaterialModel->tc_attachment != $model->tc_attachment){
							//$changefiledetails[] = '<span class="label">TC Attachment:</span> <span class="entryvalue">'.$this->getLink($RawMaterialModel->tc_attachment,$model->tc_attachment).'</span>';
							$RawMaterialFileHistoryArray[]=array('raw_material_file_old'=>$RawMaterialModel->tc_attachment,'raw_material_file_new'=>$model->tc_attachment,'raw_material_file_type'=>'tc_attachment');
							//"'.$RawMaterialModel->tc_attachment.'" => "'.$model->tc_attachment.'"';
						}
					}
					if(isset($_FILES['form_sc_attachment']['name']))
					{
						//$changefiledetails[] = '<span class="label">Farm SC Attachment:</span> <span class="entryvalue">'.$this->getLink($RawMaterialModel->form_sc_attachment,$model->form_sc_attachment).'</span>';
						$RawMaterialFileHistoryArray[]=array('raw_material_file_old'=>$RawMaterialModel->form_sc_attachment,'raw_material_file_new'=>$model->form_sc_attachment,'raw_material_file_type'=>'farm_sc_attachment');
						//"'.$RawMaterialModel->form_sc_attachment.'" => '.$model->form_sc_attachment;		
					}else{
						if($RawMaterialModel->form_sc_attachment != $model->form_sc_attachment){
							//$changefiledetails[] = '<span class="label">Farm SC Attachment:</span> <span class="entryvalue">'.$this->getLink($RawMaterialModel->form_sc_attachment,$model->form_sc_attachment).'</span>';
							$RawMaterialFileHistoryArray[]=array('raw_material_file_old'=>$RawMaterialModel->form_sc_attachment,'raw_material_file_new'=>$model->form_sc_attachment,'raw_material_file_type'=>'farm_sc_attachment');
							//"'.$RawMaterialModel->form_sc_attachment.'" => "'.$model->form_sc_attachment.'"';
						}
					}
					if(isset($_FILES['form_tc_attachment']['name']))
					{
						//$changefiledetails[] = '<span class="label">Farm TC Attachment:</span> <span class="entryvalue">'.$this->getLink($RawMaterialModel->form_tc_attachment,$model->form_tc_attachment).'</span>';
						$RawMaterialFileHistoryArray[]=array('raw_material_file_old'=>$RawMaterialModel->form_tc_attachment,'raw_material_file_new'=>$model->form_tc_attachment,'raw_material_file_type'=>'farm_tc_attachment');
						//"'.$RawMaterialModel->form_tc_attachment.'" => '.$model->form_tc_attachment;
					}else{
						if($RawMaterialModel->form_tc_attachment != $model->form_tc_attachment){
							//$changefiledetails[] = '<span class="label">Farm TC Attachment:</span> <span class="entryvalue">'.$this->getLink($RawMaterialModel->form_tc_attachment,$model->form_tc_attachment).'</span>';
							$RawMaterialFileHistoryArray[]=array('raw_material_file_old'=>$RawMaterialModel->form_tc_attachment,'raw_material_file_new'=>$model->form_tc_attachment,'raw_material_file_type'=>'farm_tc_attachment');
							//"'.$RawMaterialModel->form_tc_attachment.'" => "'.$model->form_tc_attachment.'"';
						}
					}	
					if(isset($_FILES['trade_tc_attachment']['name']))
					{
						//$changefiledetails[] = '<span class="label">Trader TC Attachment:</span> <span class="entryvalue">'.$this->getLink($RawMaterialModel->trade_tc_attachment,$model->trade_tc_attachment).'</span>';
						$RawMaterialFileHistoryArray[]=array('raw_material_file_old'=>$RawMaterialModel->trade_tc_attachment,'raw_material_file_new'=>$model->trade_tc_attachment,'raw_material_file_type'=>'trader_tc_attachment');
						//"'.$RawMaterialModel->trade_tc_attachment.'" => '.$model->trade_tc_attachment;
					}else{
						if($RawMaterialModel->trade_tc_attachment != $model->trade_tc_attachment){
							//$changefiledetails[] = '<span class="label">Trader TC Attachment:</span> <span class="entryvalue">'.$this->getLink($RawMaterialModel->trade_tc_attachment,$model->trade_tc_attachment).'</span>';
							$RawMaterialFileHistoryArray[]=array('raw_material_file_old'=>$RawMaterialModel->trade_tc_attachment,'raw_material_file_new'=>$model->trade_tc_attachment,'raw_material_file_type'=>'trader_tc_attachment');
							//"'.$RawMaterialModel->trade_tc_attachment.'" => "'.$model->trade_tc_attachment.'"';
						}
					}
				//}
				
				
				$storeddata = $this->getViewForDiff($data['id']);
				if(!isset($storeddata['standard_id'])){
					$storeddata['standard_id'] = [];
				}
				if(!isset($data['standard_id'])){
					$data['standard_id'] = [];
				}
				$posteddata = $data;
				//$stproducts = $storeddata['products'];
				
				$productdiffdata = $this->getProductDiff($data['products'],$data['id']); // To find difference in products
				$standarddifference = array_merge(array_diff($data['standard_id'],$storeddata['standard_id']),array_diff($storeddata['standard_id'],$data['standard_id'])); // To find difference in standards
				$postedstdids = $data['standard_id'];
				$dbstdids = $storeddata['standard_id'];


				// To find difference between normal data starts
				unset($storeddata['products']);
				unset($storeddata['standard_id']);
				unset($posteddata['products']);
				unset($posteddata['standard_id']);
				$difference = $this->arrViewDiff($storeddata, $posteddata);
				//$difference = array_diff($storeddata, $posteddata);
				
				// To find difference between normal data ends 


				$diffcontentcontent = '';
				$diffstandardcontent = '';
				$diffcontentcontentarr = [];
				
				/*
				if(count($changefiledetails)>0){
					$diffcontentcontentarr[] = '<li>'.implode('<br>',$changefiledetails).'<li>';
				}
				*/

				if(count($standarddifference)>0){
					$existingstdnames = '';
					$dbstdnames = '';

					if(count($dbstdids)>0){
						$existingstdnames =  implode(', ',$this->getStandardNames($dbstdids));
					}
					if(count($postedstdids)>0){
						$dbstdnames = implode(', ',$this->getStandardNames($postedstdids));
					}
					$diffcontentcontentarr[] = '<li><span class="label">Standard Details:</span> <span class="entryvalue">"'.$existingstdnames.'" >> "'. $dbstdnames .'"</span><li>';
				}
				foreach($difference as $keydata => $pdtdiffrowvalue){
					if($keydata == 'is_certified'){
						$fromcertified = $RawMaterialModel->arrcertifiedStatus[$pdtdiffrowvalue];
						$tocertified = $RawMaterialModel->arrcertifiedStatus[$posteddata[$keydata]];
						$diffcontentcontentarr[] = '<li><span class="label">'.$modelRawMaterial->getAttributeLabel($keydata).':</span> <span class="entryvalue">'.$fromcertified.' >> '.$tocertified.'</span><li>';
					}else if($keydata == 'certification_body_id'){

						$fromcertbody = $this->getCertificationBody($pdtdiffrowvalue);
						$tocertbody = $this->getCertificationBody($posteddata[$keydata]);
						$diffcontentcontentarr[] = '<li><span class="label">'.$modelRawMaterial->getAttributeLabel($keydata).':</span> <span class="entryvalue">'.$fromcertbody.' >> '.$tocertbody.'</span><li>';
					}else{
						if(!isset($posteddata[$keydata])){
							$posteddata[$keydata] = '';
						}
						$diffcontentcontentarr[] = '<li><span class="label">'.$modelRawMaterial->getAttributeLabel($keydata).':</span> <span class="entryvalue">'.$pdtdiffrowvalue.' >> '.$posteddata[$keydata].'</span><li>';
					}
					
				}
				if(count($diffcontentcontentarr)>0){
					$diffcontentcontent = '<span class="historytitle">Following Changes in Raw Materials:</span><br><ul>'.implode('',$diffcontentcontentarr).'</ul>';
				}
				
				$totalDiffData = $diffcontentcontent.$productdiffdata;
				if($totalDiffData =='' && is_array($RawMaterialFileHistoryArray) && count($RawMaterialFileHistoryArray)>0)
				{
					$totalDiffData = '<span class="historytitle">Following File Changes in Raw Materials:</span>';
				}				
				
				if($totalDiffData !=''){
					$RawMaterialHistory = new RawMaterialHistory();
					$RawMaterialHistory->raw_material_id = $data['id'];
					$RawMaterialHistory->activity = $totalDiffData;
					$RawMaterialHistory->created_by = $userid;
					$RawMaterialHistory->created_at = time();
					if($RawMaterialHistory->save())
					{					
						if(count($RawMaterialFileHistoryArray)>0)
						{
							foreach($RawMaterialFileHistoryArray as $historyFile)
							{
								$historyFile['tc_raw_material_id']=$RawMaterialHistory->raw_material_id;
								$historyFile['tc_raw_material_history_id']=$RawMaterialHistory->id;
								$this->insertRawMaterialFileHistoryFile($historyFile);															
							}
						}
					}						
				}
				// Rawmaterial history code ends here

				RawMaterialStandard::deleteAll(['raw_material_id' => $data['id']]);
				RawMaterialLabelGrade::deleteAll(['raw_material_id' => $data['id']]);
			}
			





			if($model->validate() && $model->save())
			{	
				$rawID = $model->id;
				if($editStatus == 0){
					$RawMaterialHistory = new RawMaterialHistory();
					$RawMaterialHistory->raw_material_id = $rawID;
					$RawMaterialHistory->activity = '<b>Raw Material Created</b>';
					$RawMaterialHistory->created_by = $userid;
					$RawMaterialHistory->created_at = time();
					$RawMaterialHistory->save();
				}
				

				
				
				if($data['is_certified']==1)
				{
					if(is_array($data['standard_id']) && count($data['standard_id'])>0)
					{
						foreach ($data['standard_id'] as $value)
						{ 
							$rawmaterialStd = new RawMaterialStandard();
							$rawmaterialStd->raw_material_id = $rawID;
							$rawmaterialStd->standard_id = $value;
							$rawmaterialStd->save();
						}
					}
				}

				if(is_array($data['products']) && count($data['products'])>0)
				{
					$existing_ids = [];
					foreach ($data['products'] as $value)
					{ 
						if(isset($value['raw_material_product_id']) && $value['raw_material_product_id'] !='' && $value['raw_material_product_id']>0){
							$existing_ids[]= $value['raw_material_product_id'];
						}
						
					}

					if(count($existing_ids)>0)
					{
						RawMaterialProduct::deleteAll(['AND',['NOT',['id'=>$existing_ids]],['raw_material_id'=> $rawID]]);
					}else{
						RawMaterialProduct::deleteAll(['raw_material_id'=> $rawID]);
					}
					

					foreach ($data['products'] as $value)
					{ 

						$newproduct = 0;
						if(isset($value['raw_material_product_id']) && $value['raw_material_product_id']!=''){
							$rawmaterialproduct = RawMaterialProduct::find()->where(['id'=>$value['raw_material_product_id']])->one();
							if($rawmaterialproduct === null){
								$rawmaterialproduct = new RawMaterialProduct();
								$newproduct = 1;
							}
						}else{
							$rawmaterialproduct = new RawMaterialProduct();
							$newproduct = 1;
						}
						$rawmaterialproduct->raw_material_id = $rawID;
						$rawmaterialproduct->trade_name = $value['trade_name'];
						$rawmaterialproduct->product_name = $value['product_name'];
						$rawmaterialproduct->lot_number = $value['lot_number'];
						
						$rawmaterialproduct->gross_weight = $value['gross_weight'];
						$rawmaterialproduct->actual_net_weight = $value['net_weight'];
						if($newproduct){
							$rawmaterialproduct->total_used_weight = 0;
							$rawmaterialproduct->net_weight = $value['net_weight'];
						}else{
							$rawmaterialproduct->net_weight = $rawmaterialproduct->actual_net_weight - $rawmaterialproduct->total_used_weight;
						}
						
						if($data['is_certified']==1)
						{
							$rawmaterialproduct->certified_weight = $value['certified_weight'];
						}else{
							$rawmaterialproduct->certified_weight = 0;
						}

						// if(Yii::$app->userrole->isCustomer())
						// {
						// 	$rawmaterialproduct->net_weight = $value['net_weight']
						// }

						if($rawmaterialproduct->validate() && $rawmaterialproduct->save())
						{
							if($data['is_certified']==1)
							{
								if(isset($value['label_grade_id']) && is_array($value['label_grade_id']) && count($value['label_grade_id'])>0)
								{
									foreach ($value['label_grade_id'] as $vals)
									{ 
										$rawmateriallabel = new RawMaterialLabelGrade();
										$rawmateriallabel->raw_material_id = $rawID;
										$rawmateriallabel->raw_material_product_id = $rawmaterialproduct->id;
										$rawmateriallabel->label_grade_id = $vals;
										$rawmateriallabel->save();
									}
								}
							}	
						}
						
					}
				}
				
				$sumOfRawMaterialProductWeight=$model->sumOfRawMaterialProductWeight($model->id);
				$model->net_weight = $sumOfRawMaterialProductWeight['balance_weight'];
				$model->gross_weight = $sumOfRawMaterialProductWeight['gross_weight'];
				$model->certified_weight = $sumOfRawMaterialProductWeight['certified_weight'];
				$model->actual_net_weight = $sumOfRawMaterialProductWeight['net_weight'];
				$model->total_used_weight = $sumOfRawMaterialProductWeight['used_weight'];				
				$model->save();
				
				$userMessage = 'Raw Material has been created successfully';
				if($editStatus==1)
				{
					$userMessage = 'Raw Material has been updated successfully';
				}				
				$responsedata=array('status'=>1,'message'=>$userMessage);	
			}else{
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}		
		}
		return $this->asJson($responsedata);
	}

	public function actionDownloadfile()
	{
		$data = Yii::$app->request->post();
		if($data)
		{
			if(!Yii::$app->userrole->isOss() && !Yii::$app->userrole->isCustomer() && !Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->hasRights(['raw_material','tc_application']) )
			{
				return false;
			}
		
			$column = $data['filetype'];
			//$files = RawMaterial::find()->where(['id'=>$data['id']])->one();

			$files = RawMaterial::find()->where(['t.id' => $data['id']])->alias('t');		
			$userData = Yii::$app->userdata->getData();
			$franchiseid=$userData['franchiseid'];
			$userid=$userData['userid'];
			if(Yii::$app->userrole->isOSSUser())
			{
				$files = $files->innerJoinWith(['createdbydata as createdbydata']);	
				$files = $files->andWhere(' createdbydata.franchise_id="'.$franchiseid.'" ');
			}elseif(Yii::$app->userrole->isCustomer())
			{
				$files = $files->andWhere(['created_by' => $userid]);
			}		
			$files = $files->one();

			if($files!==null)
			{
				$filename = $files->$column;			
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
				header('Access-Control-Max-Age: 1000');
				header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
				
				$filepath=Yii::$app->params['tc_files']."raw_material_files/".$filename;
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
				die;
			}	
		}		
	}
    

    protected function findModel($id)
    {
        if (($model = RawMaterial::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	public function actionDeletedata()
	{
		$requestModel=new Request();
		$data = Yii::$app->request->post();
		$target_dir = Yii::$app->params['tc_files']."raw_material_files/"; 
		if($data && isset($data['id']))
		{			
			$id = $data['id'];
			if(isset($id) && Yii::$app->userrole->isUser())
			{
				if(!Yii::$app->userrole->hasRights(['delete_raw_material']))
				{
					return false;
				}				
			}elseif(!Yii::$app->userrole->isCustomer()){			
				return false;
			}

			//$RawMaterialModel = RawMaterial::find()->where(['id'=>$id])->one();
			$RawMaterialModel = RawMaterial::find()->where(['id'=>$id]);
			$userData = Yii::$app->userdata->getData();
			$franchiseid=$userData['franchiseid'];
			$userid=$userData['userid'];
			if(Yii::$app->userrole->isOSSUser())
			{
				$RawMaterialModel = $RawMaterialModel->innerJoinWith(['createdbydata as createdbydata']);	
				$RawMaterialModel = $RawMaterialModel->andWhere(' createdbydata.franchise_id="'.$franchiseid.'" ');
			}elseif(Yii::$app->userrole->isCustomer())
			{
				$RawMaterialModel = $RawMaterialModel->andWhere(['created_by' => $userid]);
			}			
			$RawMaterialModel=$RawMaterialModel->one();
			if($RawMaterialModel!==null)
			{
				$supplier_name = $RawMaterialModel->supplier_name;				
				$connection = Yii::$app->getDb();				
				$rawMaterialCurrentUseQuery="SELECT * FROM `tbl_tc_request_product_input_material` AS inputm
				INNER JOIN `tbl_tc_request_product` AS prd ON inputm.tc_request_product_id=prd.id AND inputm.tc_raw_material_id=".$id."
				INNER JOIN `tbl_tc_request` AS req ON prd.tc_request_id=req.id AND req.status NOT IN(".$requestModel->arrEnumStatus['approved'].",".$requestModel->arrEnumStatus['rejected'].")";
				$command = $connection->createCommand($rawMaterialCurrentUseQuery);				
				$result = $command->queryOne();
				if($result  !== false)
				{
					return $responsedata=array('status'=>0,'message'=>'The "'.$supplier_name.'" is currently used by ongoing TC. If you want to remove this Raw Material, please reset the Net Weight in Stock Used section.');
				}

				$RawMaterialModel->status = $RawMaterialModel->enumStatus['archived'];
				$RawMaterialModel->save();
				/*
				$tc_attachment = $RawMaterialModel->tc_attachment;
				$unlinkFile = $target_dir.$tc_attachment;
				if(file_exists($unlinkFile))
				{
					@unlink($unlinkFile);
				}

				$form_sc_attachment = $RawMaterialModel->form_sc_attachment;
				$unlinkFile1 = $target_dir.$form_sc_attachment;
				if(file_exists($unlinkFile1))
				{
					@unlink($unlinkFile1);
				}

				$form_tc_attachment = $RawMaterialModel->form_tc_attachment;
				$unlinkFile2 = $target_dir.$form_tc_attachment;
				if(file_exists($unlinkFile2))
				{
					@unlink($unlinkFile2);
				}

				$trade_tc_attachment = $RawMaterialModel->trade_tc_attachment;
				$unlinkFile3 = $target_dir.$trade_tc_attachment;
				if(file_exists($unlinkFile3))
				{
					@unlink($unlinkFile3);
				}

				$RawMaterialModel->delete();
				*/
			}	
		}
		return $responsedata=array('status'=>1,'message'=>'Data deleted successfully');
	}
	
	public function actionStandardwisematerial()
	{

		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
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

			if($user_type ==1 || $user_type==3){
				$userid = 0;
				$RequestProduct = RequestProduct::find()->where(['id'=>$data['request_product_id']])->one();
				if($RequestProduct!== null){
					$Application = Application::find()->where(['id'=>$RequestProduct->request->app_id])->one();
					$userid = $Application->customer_id;
				}
			}


			//$data['request_product_id'] =2;	
			$arrReportRawMaterialStandardIDs=array();
			$arrReportRawMaterialStandardName=array();
			$arrReportRawMaterialStandardContent=array();
			$arrReportRawMaterialWithoutStandardContent=array();		
			$arrReportRawMaterialKeyList =array();
			$arrReportRawMaterialReclaimContent=array();

			$standardCnt=1;
			$RawMaterialWithoutCnt=1;	
			$RawMaterialReclaimCnt=1;	
			
			$RawMaterial = new RawMaterial();
			$RawMaterialModel = RawMaterial::find()->where(['t.status'=>$RawMaterial->enumStatus['approved'] ])->alias('t');
			$RawMaterialModel = $RawMaterialModel->join('left join', 'tbl_tc_raw_material_used_weight as usedmaterial','t.id =usedmaterial.tc_raw_material_id and usedmaterial.tc_request_product_id="'.$data['request_product_id'].'" ');
			$RawMaterialModel = $RawMaterialModel->andWhere(' ( usedmaterial.tc_request_product_id is not null or (usedmaterial.tc_request_product_id is null and  IF(t.is_certified = 1, t.certified_weight, t.net_weight) >0 ) ) ');

			$RawMaterialModel = $RawMaterialModel->andWhere(' t.created_by="'.$userid.'"');

			$RawMaterialModel = $RawMaterialModel->groupBy(['t.id']);
			$RawMaterialModel = $RawMaterialModel->all();
			//->where(['>','certified_weight',0])->all();
			$rawmaterialIds = [];
			$rawmaterialProductIds = [];
			if(count($RawMaterialModel)>0)
			{
				foreach($RawMaterialModel as $rm)
				{
					$RawMaterialContentArray=array();
					/*
					$RawMaterialContentArray['id']=$rm->id;
					$RawMaterialContentArray['raw_material_id']=$rm->id;
					
					$RawMaterialContentArray['supplier_name']=$rm->supplier_name;
					$RawMaterialContentArray['trade_name']=$rm->trade_name;
					$RawMaterialContentArray['product_name']=$rm->product_name;
					$RawMaterialContentArray['lot_number']=$rm->lot_number;				
					$RawMaterialContentArray['certification_body_id']=$rm->certification_body_id;
					$RawMaterialContentArray['net_weight']=$rm->net_weight;
					$RawMaterialContentArray['gross_weight']=$rm->gross_weight;
					$RawMaterialContentArray['certified_weight']=$rm->certified_weight;
					$RawMaterialContentArray['invoice_number']=$rm->invoice_number;
					*/
					
					
					$rawmaterialIds[]= $rm->id;
					if($rm->is_certified==3){
						$RawMaterialContentArray['invoice_attachment']=$rm->invoice_attachment;
						$RawMaterialContentArray['declaration_attachment']=$rm->declaration_attachment;						
					}elseif($rm->is_certified==1){
						// ------------- Certified Raw Material Code Start Here -------------------
						$RawMaterialContentArray['tc_number']=$rm->tc_number;
						$RawMaterialContentArray['tc_attachment']=$rm->tc_attachment;
						$arrRawMaterialLabelGrade=array();
						$rmLabelGrade=$rm->labelgrade;
						if(count($rmLabelGrade)>0)
						{
							foreach($rmLabelGrade as $rmLG)
							{						
								$arrRawMaterialLabelGrade[]=$rmLG->labelgrade->name;		
							}
						}
						$RawMaterialContentArray['label_grade']=$arrRawMaterialLabelGrade;					
						// ------------- Certified Raw Material Code End Here -------------------
					}
					
					//Get the product based on the Raw Material Code Start Here
					//$arrRawMaterialProducts=array();
					$rawMaterialProductsObj = $rm->product;
					if(count($rawMaterialProductsObj)>0)
					{
						foreach($rawMaterialProductsObj as $rmProduct)
						{				
							$rawmaterialProductIds[] = $rmProduct->id; 
							$RawMaterialContentArray['id']=$rm->id;
							$RawMaterialContentArray['raw_material_id']=$rm->id;							
							
							$RawMaterialContentArray['supplier_name']=$rm->supplier_name;							
							$RawMaterialContentArray['lot_number']=$rmProduct->lot_number;				
							$RawMaterialContentArray['certification_body_id']=$rm->certification_body_id;
							$RawMaterialContentArray['net_weight']=$rm->net_weight;
							$RawMaterialContentArray['gross_weight']=$rm->gross_weight;
							$RawMaterialContentArray['certified_weight']=$rm->certified_weight;
							$RawMaterialContentArray['invoice_number']=$rm->invoice_number;
							
							$RawMaterialContentArray['raw_material_product_id']=$rmProduct->id;							
							$RawMaterialContentArray['trade_name']=$rmProduct->trade_name;
							$RawMaterialContentArray['product_name']=$rmProduct->product_name;
							$RawMaterialContentArray['net_weight']=$rmProduct->net_weight;
							$RawMaterialContentArray['gross_weight']=$rmProduct->gross_weight;
							$RawMaterialContentArray['certified_weight']=$rmProduct->certified_weight;
							//$rmPdts['actual_net_weight']=$rmProduct->actual_net_weight;
							//$rmPdts['total_used_weight']=$rmProduct->total_used_weight;
							
							
							//$rawmaterialIds[]= $rm->id;
							if($rm->is_certified==3){								
								$arrReportRawMaterialReclaimContent[$RawMaterialReclaimCnt][]=$RawMaterialContentArray;
								$arrReportRawMaterialKeyList[] = ['stdkey'=>$RawMaterialReclaimCnt,'rawmaterial_product_id'=>$rmProduct->id,'rawmaterial_id'=>$rm->id,'type'=>'reclaim'];
								$RawMaterialReclaimCnt++;
							}elseif($rm->is_certified==1){
								
								// ------------- Certified Raw Material Code Start Here -------------------								
								$arrCurrentRawMaterialStandardIDs=array();
								$currentKeyVal='';
									  
								$rmStandard=$rm->standard;
								if(count($rmStandard)>0)
								{
									foreach($rmStandard as $rmS)
									{						
										$arrCurrentRawMaterialStandardIDs[]=$rmS->standard_id;		
									}						
										
									if(count($arrReportRawMaterialStandardIDs)>0)
									{
										foreach($arrReportRawMaterialStandardIDs as $key=>$val)
										{
											$subServiceArrayDiff=array_diff($arrCurrentRawMaterialStandardIDs, $arrReportRawMaterialStandardIDs[$key]);								
											if(count($subServiceArrayDiff)==0 && count($arrCurrentRawMaterialStandardIDs)==count($arrReportRawMaterialStandardIDs[$key])) 		        
											{
												$currentKeyVal=$key;					 
												break;
											}					
										}	
									}	 	
													  
									if($currentKeyVal!='')
									{
										foreach($rmStandard as $rmS)
										{
										  if(!in_array($rmS->standard_id, $arrReportRawMaterialStandardIDs[$currentKeyVal]))
										  {
											$arrReportRawMaterialStandardIDs[$currentKeyVal][]=$rmS->standard->id;
											$arrReportRawMaterialStandardName[$currentKeyVal][]=$rmS->standard->name;	
											
										  }		      
										}
										$arrReportRawMaterialKeyList[] = ['stdkey'=>$currentKeyVal,'rawmaterial_product_id'=>$rmProduct->id,'rawmaterial_id'=>$rm->id,'type'=>'standard'];
										$arrReportRawMaterialStandardContent[$currentKeyVal][]=$RawMaterialContentArray;												
									}else{							
										foreach($rmStandard as $rmS)
										{
										  $arrReportRawMaterialStandardIDs[$standardCnt][]=$rmS->standard->id;
										  $arrReportRawMaterialStandardName[$standardCnt][]=$rmS->standard->name;
										  
										}
										$arrReportRawMaterialKeyList[] = ['stdkey'=>$standardCnt,'rawmaterial_product_id'=>$rmProduct->id,'rawmaterial_id'=>$rm->id,'type'=>'standard'];
										$arrReportRawMaterialStandardContent[$standardCnt][]=$RawMaterialContentArray;						
										$standardCnt++;	  
									}								
								}
								// ------------- Certified Raw Material Code End Here -------------------
							}else{
								// ------------- Raw Material Without Certified Content Code Start Here -----------
								$arrReportRawMaterialWithoutStandardContent[$RawMaterialWithoutCnt][]=$RawMaterialContentArray;
								$arrReportRawMaterialKeyList[] = ['stdkey'=>$RawMaterialWithoutCnt,'rawmaterial_product_id'=>$rmProduct->id,'rawmaterial_id'=>$rm->id,'type'=>'non_standard'];
								$RawMaterialWithoutCnt++;
								// ------------- Raw Material Without Certified Content Code End Here -----------
							}
								
						}
					}
					//$RawMaterialContentArray['rawmaterialproducts']=$arrRawMaterialProducts;
					//Get the product based on the Raw Material Code End Here
									
					/*
					$rawmaterialIds[]= $rm->id;
					if($rm->is_certified==3){
						$RawMaterialContentArray['invoice_attachment']=$rm->invoice_attachment;
						$RawMaterialContentArray['declaration_attachment']=$rm->declaration_attachment;
						$arrReportRawMaterialReclaimContent[$RawMaterialReclaimCnt][]=$RawMaterialContentArray;
						$arrReportRawMaterialKeyList[] = ['stdkey'=>$RawMaterialReclaimCnt,'rawmaterial_id'=>$rm->id,'type'=>'reclaim'];
						$RawMaterialReclaimCnt++;
					}elseif($rm->is_certified==1){
						
						// ------------- Certified Raw Material Code Start Here -------------------
						
						$RawMaterialContentArray['tc_number']=$rm->tc_number;
						$RawMaterialContentArray['tc_attachment']=$rm->tc_attachment;
						$arrRawMaterialLabelGrade=array();
						$rmLabelGrade=$rm->labelgrade;
						if(count($rmLabelGrade)>0)
						{
							foreach($rmLabelGrade as $rmLG)
							{						
								$arrRawMaterialLabelGrade[]=$rmLG->labelgrade->name;		
							}
						}
						$RawMaterialContentArray['label_grade']=$arrRawMaterialLabelGrade;				
						
														
						$arrCurrentRawMaterialStandardIDs=array();
						$currentKeyVal='';
							  
						$rmStandard=$rm->standard;
						if(count($rmStandard)>0)
						{
							foreach($rmStandard as $rmS)
							{						
								$arrCurrentRawMaterialStandardIDs[]=$rmS->standard_id;		
							}						
								
							if(count($arrReportRawMaterialStandardIDs)>0)
							{
								foreach($arrReportRawMaterialStandardIDs as $key=>$val)
								{
									$subServiceArrayDiff=array_diff($arrCurrentRawMaterialStandardIDs, $arrReportRawMaterialStandardIDs[$key]);								
									if(count($subServiceArrayDiff)==0 && count($arrCurrentRawMaterialStandardIDs)==count($arrReportRawMaterialStandardIDs[$key])) 		        
									{
										$currentKeyVal=$key;					 
										break;
									}					
								}	
							}	 	
											  
							if($currentKeyVal!='')
							{
								foreach($rmStandard as $rmS)
								{
								  if(!in_array($rmS->standard_id, $arrReportRawMaterialStandardIDs[$currentKeyVal]))
								  {
									$arrReportRawMaterialStandardIDs[$currentKeyVal][]=$rmS->standard->id;
									$arrReportRawMaterialStandardName[$currentKeyVal][]=$rmS->standard->name;	
									
								  }		      
								}
								$arrReportRawMaterialKeyList[] = ['stdkey'=>$currentKeyVal,'rawmaterial_id'=>$rm->id,'type'=>'standard'];
								$arrReportRawMaterialStandardContent[$currentKeyVal][]=$RawMaterialContentArray;												
							}else{							
								foreach($rmStandard as $rmS)
								{
								  $arrReportRawMaterialStandardIDs[$standardCnt][]=$rmS->standard->id;
								  $arrReportRawMaterialStandardName[$standardCnt][]=$rmS->standard->name;
								  
								}
								$arrReportRawMaterialKeyList[] = ['stdkey'=>$standardCnt,'rawmaterial_id'=>$rm->id,'type'=>'standard'];
								$arrReportRawMaterialStandardContent[$standardCnt][]=$RawMaterialContentArray;						
								$standardCnt++;	  
							}								
						}
						// ------------- Certified Raw Material Code End Here -------------------
					}else{
						// ------------- Raw Material Without Certified Content Code Start Here -----------
						$arrReportRawMaterialWithoutStandardContent[$RawMaterialWithoutCnt][]=$RawMaterialContentArray;
						$arrReportRawMaterialKeyList[] = ['stdkey'=>$RawMaterialWithoutCnt,'rawmaterial_id'=>$rm->id,'type'=>'non_standard'];
						$RawMaterialWithoutCnt++;
						// ------------- Raw Material Without Certified Content Code End Here -----------

					}
					*/					
					
				}
			}		
			return array('rawmaterialProductIds'=>$rawmaterialProductIds,'rawMaterialKeyList'=> $arrReportRawMaterialKeyList,
					'rawmaterialstandardids'=>$arrReportRawMaterialStandardIDs,'rawmaterialstandardname'=>$arrReportRawMaterialStandardName,
					'rawmaterialstandardcontent'=>$arrReportRawMaterialStandardContent,
					'rawmaterialwithoutstandardcontent'=>$arrReportRawMaterialWithoutStandardContent,
					'rawmaterialids'=>$rawmaterialIds,
					'rawmaterialreclaimcontent' => $arrReportRawMaterialReclaimContent
					
					);
		}
		return $responsedata; 
		
	}

	public function getproductname($tc_request_product_id)
	{
		$pdtdata = RequestProduct::find()->where(['id'=>$tc_request_product_id])->one();
		$productdata = [];
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$completepdtname = '';
		if($pdtdata !== null){
			//foreach ($RequestProduct as $pdtdata) {
			$productname = '';

			$Unitproduct = $pdtdata->unitproduct;
			
			if($Unitproduct!== null){
				$productstd = $Unitproduct->product;
				if($productstd!==null){
					$standard_name = $productstd->standard->name;
					$labelgradename = $productstd->labelgrade->name;

					$productname = $productstd->appproduct->product->name;
					$producttypename = $productstd->appproduct->producttype->name;

					$wastage = $productstd->appproduct->wastage;
					$materialcompositionname = '';
					if(count($productstd->productmaterial) >0){
						foreach($productstd->productmaterial as $productmaterial){

							$productMaterialList[]=[
								'app_product_id'=>$productmaterial->app_product_id,
								'material_id'=>$productmaterial->material_id,
								'material_name'=>$productmaterial->material->name,
								'material_type_id'=>$productmaterial->material_type_id,
								'material_type_name'=> $productmaterial->material->material_type[$productmaterial->material_type_id],
								'material_percentage'=>$productmaterial->percentage
							];
							$materialcompositionname = $materialcompositionname.$productmaterial->percentage.'% '.$productmaterial->material->name.' + ';

						}
						$materialcompositionname = rtrim($materialcompositionname," + ");
					}
					$completepdtname = $productname.' | '.$producttypename.' | '.$wastage.'% wastage | '.$materialcompositionname.' | '.$standard_name.' | '.$labelgradename;
					
				}				
			}
		}
		return $completepdtname;
	}
	/*
	public function actionProductwiserawmaterialinputs(){
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if($data)
		{
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];


			$tc_request_product_id = $data['tc_request_product_id'];
			if(isset($data['inputweight']) && count($data['inputweight'])>0 )
			{
				foreach($data['inputweight'] as $inputmaterial){
					$TcRawMaterialUsedWeight = TcRawMaterialUsedWeight::find()->where(['tc_raw_material_id'=>$inputmaterial['tc_raw_material_id'], 'tc_request_product_id'=> $tc_request_product_id])->one();
					if($TcRawMaterialUsedWeight === null){
						$TcRawMaterialUsedWeight = new TcRawMaterialUsedWeight();
						$TcRawMaterialUsedWeight->created_by = $userid;
					}else{
						$TcRawMaterialUsedWeight->updated_by = $userid;
					}
					$remaining_weight = 0;
					$RawMaterial = RawMaterial::find()->where(['id'=>$inputmaterial['tc_raw_material_id']])->one();
					if($RawMaterial !== null){
						$certified_weight = $RawMaterial->certified_weight;
						$remaining_weight = $certified_weight - $inputmaterial['rminputweight'];
						$RawMaterial->certified_weight = $remaining_weight;
						$RawMaterial->save();
					}
					$RequestProduct = RequestProduct::find()->where(['id'=>$tc_request_product_id])->one();
					if($RequestProduct !== null){
						$product_id = $RequestProduct->product_id;

					}


					$TcRawMaterialUsedWeight->tc_request_product_id = $tc_request_product_id;
					$TcRawMaterialUsedWeight->tc_raw_material_id = $inputmaterial['tc_raw_material_id'];
					$TcRawMaterialUsedWeight->used_weight = $inputmaterial['rminputweight'];



					$TcRawMaterialUsedWeight->product_id = $product_id;
					$TcRawMaterialUsedWeight->stock_weight = $certified_weight;

					$TcRawMaterialUsedWeight->remaining_weight = $remaining_weight;
					$TcRawMaterialUsedWeight->status = 0;
					$TcRawMaterialUsedWeight->save();



					$TcRequestProductInputMaterial = TcRequestProductInputMaterial::find()->where(['tc_raw_material_id'=>$inputmaterial['tc_raw_material_id'], 'tc_request_product_id'=> $tc_request_product_id])->one();
					if($TcRequestProductInputMaterial === null){
						$TcRequestProductInputMaterial = new TcRequestProductInputMaterial();
						//$TcRequestProductInputMaterial->created_by = $userid;
					}else{
						//$TcRequestProductInputMaterial->updated_by = $userid;
					}
					$TcRequestProductInputMaterial->tc_request_product_id = $tc_request_product_id;
					$TcRequestProductInputMaterial->tc_raw_material_id = $inputmaterial['tc_raw_material_id'];
					$TcRequestProductInputMaterial->used_weight = $inputmaterial['rminputweight'];
					$TcRequestProductInputMaterial->save();

				}

				$responsedata=array('status'=>1,'message'=>'Stock updated successfully');
			}
		}
		return $responsedata;
	}
	*/
	public function actionGeneratetc()
	{
		$requestID=66;
		$returnType=false;

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
			if($model->status!=$modelRequest->arrEnumStatus['approved'])
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
					
					if($standard_code_lower=='gots' || $standard_code_lower=='grs')
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

			$mpdf = new \Mpdf\Mpdf(array('mode' => 'utf-8','margin_left' => 10,'margin_right' => 10,'margin_top' => 24,'margin_bottom' => 15,'margin_header' => 0,'margin_footer' => 3,'setAutoTopMargin' => 'stretch'));
			$mpdf->SetDisplayMode('fullwidth');
			//$mpdf->SetProtection(array(), 'UserPassword', 'MyPassword');
			
			$qrCodeURL=Yii::$app->params['certificate_file_download_path'].'scan-transaction-certificate?code='.md5($model->id);
			if($draftText!='')
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
			
			table.reportDetailLayoutInnerWithoutTableBorder {
				border:none;
				font-size:12px;
				font-family:Arial;
				text-align: left;
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
				border-left: 4px solid #000000;
				border-right: 4px solid #000000;
				border-bottom: 4px solid #000000;
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
				<div style="color:#000000;font-size:11px;font-family:Arial;padding-bottom:7px;text-align:right;">This electronically issued document is the valid original version.</div>
				<div style="color:#000000;font-size:11px;font-family:Arial;text-align:right;">Transaction Certificate Number <span style="font-weight:bold;">'.implode(", ",$RegistrationNoArray).'</span> and Seller License Number <span style="font-weight:bold;">GCL-'.$customeroffernumber.'</span>, <span style="font-weight:bold;">'.date('d F Y').'</span>, Page {PAGENO} of {nbpg}</div>
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
				<tr>
					<td class="reportDetailLayout" style="text-align:center;font-weight:bold;width:5%;">S.No</td>
					<td class="reportDetailLayout" style="font-weight:bold;width:16%;">Product Details</td>
					<td class="reportDetailLayout" style="text-align:center;font-weight:bold;width:13%;">Trade Name / Technical Details</td>
					<td class="reportDetailLayout" style="text-align:center;font-weight:bold;width:10%;">Packaging Details</td>
					<td class="reportDetailLayout" style="font-weight:bold;width:24%;">Invoice and Transport Details</td>
					<td class="reportDetailLayout" style="text-align:center;font-weight:bold;width:10%;">Certified<br>Weight<br>(kgs)</td>
					<td class="reportDetailLayout" style="text-align:center;font-weight:bold;width:10%;">Net<br> Weight<br>(kgs)</td>
					<td class="reportDetailLayout" style="text-align:center;font-weight:bold;width:10%;">Gross<br>Weight<br>(kgs)</td>
				</tr>';					
				
			$TcConsigneeContent='';
			$TcConsigneeContent='<table cellpadding="0" autosize="2.4" cellspacing="0" border="0" width="100%" class="reportDetailLayout" style="margin-top:10px;">
				<tr>
					<td class="reportDetailLayout" style="text-align:center;width:10px;font-weight:bold;">S.No</td>
					<td class="reportDetailLayout" style="text-align:left;font-weight:bold;">Consignee</td>
					<td class="reportDetailLayout" style="text-align:center;font-weight:bold;">Invoice Number</td>
					<td class="reportDetailLayout" style="text-align:center;font-weight:bold;">Destination</td>						
				</tr>';	
				
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
						$consigneeAddress.=$prdConsignee->address.', '.$prdConsignee->city.''.($prdConsignee->state? ', '.$prdConsignee->state->name:', ').''.$prdConsigneeCountry.' - '.$prdConsignee->zipcode;
						
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
					</td>				
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

	private function getViewForDiff($rawmaterial_id){
		
		   
		$model = RawMaterial::find()->where(['id' => $rawmaterial_id]);		
		$model = $model->one();

		if ($model !== null)
		{
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$resultarr["supplier_name"]=$model->supplier_name;
			//$resultarr["lot_number"]=$model->lot_number;
			$resultarr["is_certified"]=$model->is_certified;
			
			//if($model->is_certified == 3){
				$resultarr["invoice_number"]=$model->invoice_number;
			//}elseif($model->is_certified == 1){
				$resultarr["tc_number"]=$model->tc_number;
				$resultarr["form_sc_number"]=$model->form_sc_number;
				$resultarr["form_tc_number"]=$model->form_tc_number;
				$resultarr["trade_tc_number"]=$model->trade_tc_number;

				$materialStd = $model->standard;
				if(count($materialStd)>0)
				{
					$materialStdids = [];
					$materialStdnames = [];
					foreach($materialStd as $std)
					{
						$materialStdids[] = "".$std['standard_id'];
						$materialStdnames[] = $std->standard->name;
					}
					$resultarr["standard_id"]=$materialStdids;
				}
			//}
			//else
			//{
				$resultarr["invoice_number"]=$model->invoice_number;
			//}
			
			$productarr = [];
			$materialproduct = $model->product;
			if(count($materialproduct)>0)
			{	
				foreach ($materialproduct as $value)
				{ 		
					$productdata = array();
					$productdata['raw_material_product_id'] = $value->id;
					$productdata['trade_name'] = $value->trade_name;
					$productdata['product_name'] = $value->product_name;
					
					$productdata['gross_weight'] = $value->gross_weight;
					$productdata['certified_weight'] = $value->certified_weight;
					$productdata['net_weight'] = $value->actual_net_weight;
					$materiallabelgrade = $value->labelgrade;
					if(count($materiallabelgrade)>0)
					{
						$labelgradeids = [];
						$labelgradenames = [];
						foreach($materiallabelgrade as $label)
						{
							$labelgradeids[]="".$label['label_grade_id'];
							$labelgradenames[]=$label->labelgrade->name;
						}
						$productdata["label_grade_id"]=$labelgradeids;
					}

					$productarr[] = $productdata;
				}
			}
			$resultarr['products'] = $productarr;					
			
			
			$resultarr["certification_body_id"]=$model->certification_body_id;
			return $resultarr;
		}
		 
		return [];
	}

	private function getProductDiff($productlist,$raw_material_id){
		//$posteddata
		$datadiff_products = [];
		$new_products = [];
		$content_new_products = '';
		$content_data_diff = '';
		$total_content_data_diff = '';
		$del_content_data_diff = '';
		$RawMaterialProductLabels = new RawMaterialProduct();
		if(is_array($productlist) && count($productlist)>0){
			$existing_ids = [];
			foreach ($productlist as $pdtrowids)
			{ 
				if(isset($pdtrowids['raw_material_product_id']) && $pdtrowids['raw_material_product_id']>0 && $pdtrowids['raw_material_product_id'] !=''){
					$existing_ids[]= $pdtrowids['raw_material_product_id'];
				}
				
			}
			$RawMaterialProductDeleted = RawMaterialProduct::find()->where(['not in','id',$existing_ids])->andWhere(['raw_material_id'=>$raw_material_id])->all();
			if(count($RawMaterialProductDeleted)>0){
				$productdeldesc = '';
				foreach($RawMaterialProductDeleted as $rawdelrow){
					$prodcutdel = [];
					$prodcutdel[] = '<li><span class="label">'.$RawMaterialProductLabels->getAttributeLabel('trade_name').':</span> <span class="entryvalue">'.$rawdelrow->trade_name.'</span></li>';
					$prodcutdel[] = '<li><span class="label">'.$RawMaterialProductLabels->getAttributeLabel('product_name').':</span> <span class="entryvalue">'.$rawdelrow->product_name.'</span></li>';
					$prodcutdel[] = '<li><span class="label">'.$RawMaterialProductLabels->getAttributeLabel('lot_number').':</span> <span class="entryvalue">'.$rawdelrow->lot_number.'</span></li>';
					$prodcutdel[] = '<li><span class="label">'.$RawMaterialProductLabels->getAttributeLabel('gross_weight').':</span> <span class="entryvalue">'.$rawdelrow->gross_weight.'</span></li>';
					$prodcutdel[] = '<li><span class="label">'.$RawMaterialProductLabels->getAttributeLabel('actual_net_weight').':</span> <span class="entryvalue">'.$rawdelrow->actual_net_weight.'</span></li>';
					
					if($rawdelrow->certified_weight>0){
						$prodcutdel[] = '<li><span class="label">'.$RawMaterialProductLabels->getAttributeLabel('certified_weight').':</span> <span class="entryvalue">'.$rawdelrow->certified_weight.'</span></li>';
					}
					
					$productdeldesc .= '<ul>'.implode('',$prodcutdel).'</ul>';
					
				}

				$del_content_data_diff = '<span class="historytitle">Deleted Product Details Listed Below:</span><br>'.$productdeldesc;
			}
			$chkids = [];
			foreach($productlist as $pdtrow){
				$pdtcopy = $pdtrow;
				if(isset($pdtrow['raw_material_product_id']) && $pdtrow['raw_material_product_id'] !='' && $pdtrow['raw_material_product_id'] > 0){
					$RawMaterialProduct = RawMaterialProduct::find()->where(['id'=>$pdtrow['raw_material_product_id']])->one();
					
					if($RawMaterialProduct !== null){
						$chkids[] = $RawMaterialProduct->id;
						$dbproductdata = array();
						$dbproductdata['raw_material_product_id'] = $RawMaterialProduct->id;
						$dbproductdata['trade_name'] = $RawMaterialProduct->trade_name;
						$dbproductdata['product_name'] = $RawMaterialProduct->product_name;
						$dbproductdata['lot_number'] = $RawMaterialProduct->lot_number;
						$dbproductdata['gross_weight'] = $RawMaterialProduct->gross_weight;
						$dbproductdata['certified_weight'] = $RawMaterialProduct->certified_weight;
						$dbproductdata['net_weight'] = $RawMaterialProduct->actual_net_weight;
						$dbproductdata['balance_weight'] = $RawMaterialProduct->net_weight;
						$arr_label_grade_id = [];
						$materiallabelgrade = $RawMaterialProduct->labelgrade;
						$labelgradenames = [];
						$labelgradeids = [];
						if(count($materiallabelgrade)>0)
						{
							foreach($materiallabelgrade as $label)
							{
								$labelgradeids[]="".$label['label_grade_id'];
								$labelgradenames[]=$label->labelgrade->name;
							}
							$arr_label_grade_id = $labelgradeids;
						}
						$posted_label_grade_id = $pdtrow['label_grade_id'];
						
						unset($pdtcopy['label_grade_id']);
						unset($pdtcopy['used_weight']);
						unset($pdtcopy['actual_net_weight']);
						
						$pdtcopy['gross_weight'] = number_format($pdtcopy['gross_weight'],2,'.','');
						$pdtcopy['certified_weight'] = number_format($pdtcopy['certified_weight'],2,'.','');
						$pdtcopy['net_weight'] = number_format($pdtcopy['net_weight'],2,'.','');
						if(isset($pdtcopy['balance_weight'])){
							$pdtcopy['balance_weight'] = number_format($pdtcopy['balance_weight'],2,'.','');
						}
						

						//print_r($dbproductdata);
						//print_r($pdtcopy);
						//net_weight
						$productdatadiff = $this->arrDiff($dbproductdata,$pdtcopy);
						$labelgradedatadiff = array_merge(array_diff($arr_label_grade_id,$posted_label_grade_id),array_diff($posted_label_grade_id,$arr_label_grade_id));
						

						//print_r($productdatadiff);

						$diffcontent = [];
						if(count($productdatadiff)>0 || count($labelgradedatadiff)>0){
							//print_r($productdatadiff);
							//print_r($labelgradedatadiff);
							$localdatadiff = [];
							if(count($productdatadiff)>0)
							{
								$localdatadiff = $productdatadiff;
								foreach($productdatadiff as $keydata => $pdtdiffrowvalue){
									$diffcontent[] = '<li><span class="label">'.$RawMaterialProductLabels->getAttributeLabel($keydata).':</span> <span class="entryvalue">'.$dbproductdata[$keydata].' >> '.$pdtcopy[$keydata].'</span><li>';
								}
							}
							
							if(count($labelgradedatadiff)>0){
								//$posted_label_grade_id;
								//$localdatadiff = $localdatadiff + ['label_grade_id'=>$labelgradedatadiff];
								//foreach($productdatadiff as $keydata => $pdtdiffrowvalue){
								$TcStandardLabelGrade = TcStandardLabelGrade::find()->where(['id'=>$posted_label_grade_id])->all();
								$posteslabelnames = [];
								if(count($TcStandardLabelGrade)>0){
									foreach($TcStandardLabelGrade as $tclabelname){
										$posteslabelnames[] = $tclabelname->name;
									}
								}
								$labelgradenamesstr = '';
								if(isset($labelgradenames) && count($labelgradenames)>0){
									$labelgradenamesstr = implode(', ',$labelgradenames);
								}
								$diffcontent[] = '<li><span class="label">Label Grade:</label> <span class="entryvalue">'.$labelgradenamesstr.' >> '.implode(', ',$posteslabelnames).'</span><li>';
								//}
							}

							//$datadiff_products[] = $localdatadiff + ['raw_material_product_id' => $pdtrow['raw_material_product_id']];
							$datadiff_products[] = '<ul><li><span class="label">Raw Material Product ID:</span> <span class="entryvalue">'.$pdtrow['raw_material_product_id'].'</span></li>'.implode('',$diffcontent).'</ul>';
							
							
						}
					}
				}else{
					
					unset($pdtcopy['label_grade_id']);
					//print_r($pdtcopy);
					//unset($pdtcopy['label_grade_id']);
					$newcontent = '';
					foreach($pdtcopy as $pdtkey=>$pdtvalue){
						if($pdtkey != 'raw_material_product_id' && $pdtkey != 'actual_net_weight'){
							if($pdtkey =='certified_weight' &&  $pdtvalue ==0){

							}else{
								$newcontent .= '<span class="label">'.$RawMaterialProductLabels->getAttributeLabel($pdtkey).':</span><span class="entryvalue">'.$pdtvalue.'</span><br>';
							}							
						}						
					}
					$new_products[] = '<ul><li>'.$newcontent.'</li></ul>';
				}
			}
			//print_r($chkids);
			if(count($new_products)>0){
				$content_new_products = '<span class="historytitle">New Products added are listed below:</span><br>'.implode('',$new_products);
			}
			if(count($datadiff_products)>0){
				$content_data_diff = '<span class="historytitle">Changed Product Details Below:</span><br>'.implode('',$datadiff_products);
			}
			$total_content_data_diff = $del_content_data_diff.''.$content_data_diff.''.$content_new_products;
		}
		//echo  $total_content_data_diff;
		return $total_content_data_diff;
	}
	private function arrViewDiff($arr1,$arr2){
		$differencedata = [];
		//,'lot_number'
		$arrforDiff = ['supplier_name','is_certified','invoice_number','tc_number','form_sc_number','form_tc_number','trade_tc_number','certification_body_id'];
		foreach($arr1 as $arkey => $arvalue){
			if( in_array($arkey,$arrforDiff)){
				if(!isset($arr2[$arkey])){
					$arr2[$arkey] = '';
				}
				if($arr2[$arkey] != $arvalue){
					$differencedata[$arkey] = $arvalue;
				}
			}
		}
		return $differencedata;
	}
	private function arrDiff($arr1,$arr2){
		$differencedata = [];
		foreach($arr1 as $arkey => $arvalue){
			if($arr2[$arkey] != $arvalue){
				$differencedata[$arkey] = $arvalue;
			}
		}
		return $differencedata;
	}
	private function getStandardNames($stdids){
		$TcStandard = TcStandard::find()->where(['id'=>$stdids])->all();
		$stdnames = [];
		if(count($TcStandard)>0){
			foreach($TcStandard as $tcstd){
				$stdnames[] = $tcstd->name;
			}
		}
		return $stdnames;
	}

	public function actionDownloadhistoryfile()
	{									
		$postdata = Yii::$app->request->post();
		if($postdata)
		{		
			$RawMaterialFileHistoryModel = RawMaterialFileHistory::find()->where(['id'=>$postdata['id'],'raw_material_file_type'=>$postdata['material_file_type']])->one();
			if($RawMaterialFileHistoryModel !== null)
			{
				if($postdata['file_type']=='old')
				{				
					$filename = $RawMaterialFileHistoryModel->raw_material_file_old;
				}else{
					$filename = $RawMaterialFileHistoryModel->raw_material_file_new;
				}	
				$filepath=Yii::$app->params['tc_files']."raw_material_files/".$filename;	
				
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
				header('Access-Control-Max-Age: 1000');
				header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
					
				
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
		die();	
		 		
	}

	private function getLink($fromfile,$tofile){
		//return '"<a   (click)="downloadMaterialFile('.$fromfile.')"  >'.$fromfile.'</a>" >> "<a  (click)="downloadMaterialFile('.$tofile.')" >'.$tofile.'</a>"';
		return '"<a href="'.Yii::$app->params['site_path'].'web/transfercertificate/raw-material/downloadhistoryfile?file='.$fromfile.'" >'.$fromfile.'</a>" >> "<a href="'.Yii::$app->params['site_path'].'web/transfercertificate/raw-material/downloadhistoryfile?file='.$tofile.'" >'.$tofile.'</a>"';
	}
	
	private function insertRawMaterialFileHistoryFile($data)
	{
		$RawMaterialFileHistoryModel = new RawMaterialFileHistory();
		$RawMaterialFileHistoryModel->tc_raw_material_id = $data['tc_raw_material_id'];
		$RawMaterialFileHistoryModel->tc_raw_material_history_id = $data['tc_raw_material_history_id'];
		$RawMaterialFileHistoryModel->raw_material_file_old = $data['raw_material_file_old'];
		$RawMaterialFileHistoryModel->raw_material_file_new = $data['raw_material_file_new'];
		$RawMaterialFileHistoryModel->raw_material_file_type = $data['raw_material_file_type'];		
		$RawMaterialFileHistoryModel->save();
	}
	
	private function getFileID($fileName,$fileType,$entryType)
	{
		$RawMaterialFileHistoryID=0;
		$RawMaterialFileHistoryModel = RawMaterialFileHistory::find()->where(['raw_material_file'=>$fileName,'raw_material_file_type'=>$fileType,'entry_type'=>$entryType])->one();
		if($RawMaterialFileHistoryModel !== null)
		{
			$RawMaterialFileHistoryID = $RawMaterialFileHistoryModel->id;
		}
		return $RawMaterialFileHistoryID;		
	}

	private function getCertificationBody($id){
		$insname = '';
		if($id !='' && $id!=0 && $id>0){
			$InspectionBody = InspectionBody::find()->where(['id'=>$id])->one();
			if($InspectionBody !== null){
				$insname = $InspectionBody->name;
			}
		}		
		//return '"<a   (click)="downloadMaterialFile('.$fromfile.')"  >'.$fromfile.'</a>" >> "<a  (click)="downloadMaterialFile('.$tofile.')" >'.$tofile.'</a>"';
		return $insname;
	}
}