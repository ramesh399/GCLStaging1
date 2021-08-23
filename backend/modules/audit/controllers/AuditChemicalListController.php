<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\audit\models\AuditReportChemicalList;
use app\modules\audit\models\AuditReportChemicalListAuditorConformity;
use app\modules\audit\models\AuditReportApplicableDetails;
use app\modules\application\models\ApplicationUnitStandard;
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
				// $data['trade_name']=$value->trade_name;
				// $data['suppier']=$value->suppier;
				// $data['country_id']=$value->country_id;
				// $data['country_id_label']=$value->country->name;
				// $data['utilization']=$value->utilization;
				// $data['proof']=$value->proof;
				// $data['proof_label']=$chemicalmodel->arrProof[$value->proof];
				//$data['type_of_conformity']=$value->type_of_conformity;
				// $data['validity_or_issue_date']=date($date_format,strtotime($value->validity_or_issue_date));
				//$data['msds_issued_date']=date($date_format,strtotime($value->msds_issued_date));
				// $data['msds_available']=$value->msds_available;
				// $data['msds_available_label']=$chemicalmodel->arrMSDSavailable[$value->msds_available];
				$data['msds_issued_date']=$value->msds_issued_date?date($date_format,strtotime($value->msds_issued_date)):'';
				// $data['conformity_auditor']=$value->conformity_auditor;
				// $data['conformity_auditor_label']=$value->auditorconformity->name;
				// $data['conformity_auditor_label_color']=$chemicalmodel->arrColor[$value->auditorconformity->id];				
				$data['created_at']=date($date_format,$value->created_at);
				$data['created_by_label']=$value->createdbydata->first_name.' '.$value->createdbydata->last_name;
				// $data['comments']=$value->comments;
				$data['ingredient_name']=$value->ingredient_name?$value->ingredient_name:'';
				$data['supplier_name']=$value->supplier_name?$value->supplier_name:'';
				$data['utilization_product']=$value->utilization_product?$value->utilization_product:'';
				$data['comply_gots']=$value->comply_gots?$value->comply_gots:'';
				$data['comply_gots_label']=$value->comply_gots?$chemicalmodel->arrProof[$value->comply_gots]:'';
				$data['comply_grs']=$value->comply_grs?$value->comply_grs:'';
				$data['comply_grs_label']=$value->comply_grs?$chemicalmodel->arrProof[$value->comply_grs]:'';
				$data['msds_file']=$value->msds_file?$value->msds_file:'';
				$data['gots_version']=$value->gots_version?$value->gots_version:'';
				$data['gots_approval_no']=$value->gots_approval_no?$value->gots_approval_no:'';
				$data['gots_approval_date']=$value->gots_approval_date?date($date_format,strtotime($value->gots_approval_date)):'';
				$data['cas_no']=$value->cas_no?$value->cas_no:'';
				$data['is_hcode_identified']=$value->is_hcode_identified?$value->is_hcode_identified:'';
				$data['hcode_no']=$value->hcode_no?$value->hcode_no:'';
				$data['is_hcode_identified_label']=$value->is_hcode_identified?$chemicalmodel->arrProof[$value->is_hcode_identified]:'';
				$data['comply_d21']=$value->comply_d21?$value->comply_d21:'';
				$data['comply_d21_label']=$value->comply_d21?$chemicalmodel->arrProof[$value->comply_d21]:'';
				$data['comply_d22']=$value->comply_d22?$value->comply_d22:'';
				$data['comply_d22_label']=$value->comply_d22?$chemicalmodel->arrD22list[$value->comply_d22]:'';
				$data['comply_d23']=$value->comply_d23?$value->comply_d23:'';
				$data['comply_d23_label']=$value->comply_d23?$chemicalmodel->arrD22list[$value->comply_d23]:'';
				$data['comply_file']=$value->comply_file?$value->comply_file:'';



				$chemical_list[]=$data;
			}
		}

		return ['chemicals'=>$chemical_list,'total'=>$totalCount];
    }
	public function actionDownloadfile()
	{
		$data = Yii::$app->request->post();
		if($data)
		{
			
		
			$column = $data['filetype'];
			

			$files = AuditReportChemicalList::find()->where(['t.id' => $data['id']])->alias('t');		
			$files = $files->one();

			if($files!==null)
			{
				$filename = $files->$column;				
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
				header('Access-Control-Max-Age: 1000');
				header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
				
				$filepath=Yii::$app->params['audit_files'].$filename;
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
    

	public function actionCreate()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$datapost = Yii::$app->request->post();
		$datapost=json_decode($datapost['formvalue'],true);
		$data =$datapost['chemical_data'];
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
			$target_dir = Yii::$app->params['audit_files'];
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

			if(isset($_FILES['msds_file']['name']))
			{
				
				$tmp_name = $_FILES["msds_file"]["tmp_name"];
	   			$name = $_FILES["msds_file"]["name"];
				$model->msds_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);	
			}
			if(isset($_FILES['comply_file']['name']))
			{
				
				$tmp_name = $_FILES["comply_file"]["tmp_name"];
	   			$name = $_FILES["comply_file"]["name"];
				$model->comply_file=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);	
			}
			if($data['complythree']==2 || $data['hcode']==2){
				$model->comply_file='';
			}

			$model->audit_id = $data['audit_id'];
			$model->unit_id = $data['unit_id'];
			// $model->trade_name = $data['trade_name'];
			// $model->suppier = $data['suppier'];	
			// $model->country_id = $data['country_id'];
			// $model->utilization = $data['utilization'];
			// $model->proof = $data['proof'];
			// $model->validity_or_issue_date = $data['validity_or_issue_date']?date('Y-m-d',strtotime($data['validity_or_issue_date'])):'';
			// $model->msds_available = $data['msds_available'];
			// $model->conformity_auditor = $data['conformity_auditor'];
			// $model->comments = $data['comments'];

			$model->ingredient_name=$data['ingredient_name']?$data['ingredient_name']:'';
			$model->supplier_name=$data['supplier_name']?$data['supplier_name']:'';
			$model->utilization_product=$data['product_name']?$data['product_name']:'';
			$model->msds_issued_date=$data['msds_issued_date']?date('Y-m-d',strtotime($data['msds_issued_date'])):'';
			$model->comply_gots=$data['complygots']?$data['complygots']:'';
			$model->comply_grs=$data['complygrs']?$data['complygrs']:'';
			$model->gots_version=$data['version_name']?$data['version_name']:'';
			$model->gots_approval_no=$data['approval_no']?$data['approval_no']:'';
			$model->gots_approval_date=$data['approval_date']?date('Y-m-d',strtotime($data['approval_date'])):'';
			$model->cas_no=$data['cas_no']?$data['cas_no']:'';
			$model->is_hcode_identified=$data['hcode']?$data['hcode']:'';
			$model->hcode_no=$data['hcode']==1?$data['hcode_no']:'';
			$model->comply_d21=$data['complyone']?$data['complyone']:'';
			$model->comply_d22=$data['complytwo']?$data['complytwo']:'';
			$model->comply_d23=$data['complythree']?$data['complythree']:'';

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
		return ['msdslist'=>$modelObj->arrMSDSavailable,'D22list'=>$modelObj->arrD22list,'prooflist'=>$modelObj->arrProof,'conformitylist'=>$conformity];
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

	public function actionGetStandardIds()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if($data){
			$standardIds=array();
			$auditcheunitstdidmod = ApplicationUnitStandard::find()->where(['unit_id'=>$data['id']])->all();
			if(count($auditcheunitstdidmod)>0){
				foreach($auditcheunitstdidmod as $che){
					$standardIds[]=$che['standard_id'];
				}
			}
			$responsedata=array('status'=>1,'data'=>$standardIds);
		}
		return $responsedata;
	}
	
	
	
	
	
	
	
}
