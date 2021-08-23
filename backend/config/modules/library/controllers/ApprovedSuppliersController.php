<?php
namespace app\modules\library\controllers;

use Yii;
use app\modules\library\models\LibraryApprovedsuppliers;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * ApprovedSuppliersController implements the CRUD actions for Product model.
 */
class ApprovedSuppliersController extends \yii\rest\Controller
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

		$suppliermodel = new LibraryApprovedsuppliers();
		$model = LibraryApprovedsuppliers::find()->alias('t');
		$model->joinWith(['country as cty']);
		if($resource_access != '1')
		{

			/*
			if($user_type==3 && $is_headquarters!=1)
			{
				$model = $model->andWhere('t.created_by="'.$userid.'"');
			}else{
				$model = $model->andWhere('t.created_by=0');
			}
			*/
			if($user_type==3 && $resource_access==5){
				$model = $model->andWhere('t.franchise_id="'.$franchiseid.'"');
			}else if($user_type==3 && $is_headquarters!=1){
				$model = $model->andWhere('t.franchise_id="'.$userid.'"');
			}else if($user_type== Yii::$app->params['user_type']['user'] && in_array('approved_suppliers',$rules ) && $is_headquarters!=1){
				//$model = $model->andWhere('t.franchise_id="'.$franchiseid.'"');
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
				$model = $model->andFilterWhere([
					'or',
					['like', 't.supplier_name', $searchTerm],
					['like', 't.contact_person', $searchTerm],
					['like', 't.email', $searchTerm],
					['like', 'cty.name', $searchTerm],	
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
		
		$supplier_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $supplier)
			{
				$data=array();
				$data['id']=$supplier->id;
				$data['country_id']=$supplier->country_id;
				$data['country_name']=$supplier->country->name;
				$data['supplier_name']=$supplier->supplier_name;
				$data['address']=$supplier->address;
				$data['contact_person']=$supplier->contact_person;
				$data['email']=$supplier->email;
				$data['phone']=$supplier->phone;
				$data['supplier_file']=$supplier->supplier_file;
				$data['accreditation']=$supplier->accreditation;
				$data['certificate_no']=$supplier->certificate_no;
				$data['accreditation_expiry_date']=date($date_format,strtotime($supplier->accreditation_expiry_date));
				$data['scope_of_accreditation']=$supplier->scope_of_accreditation;
				$data['status']=$supplier->status;
				$data['status_label']=$suppliermodel->arrStatus[$supplier->status];
				$data['created_at']=date($date_format,$supplier->created_at);
				$data['created_by_label']=$supplier->createduser?$supplier->createduser->first_name.' '.$supplier->createduser->last_name:'';

				
				$supplier_list[]=$data;
			}
		}
		
		return ['approvedsuppliers'=>$supplier_list,'total'=>$totalCount];
    }


	public function actionCreate()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$datapost = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$target_dir = Yii::$app->params['library_files']."supplier_files/"; 
		
		if($datapost){

			$data =json_decode($datapost['formvalues'],true);

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
				$model = LibraryApprovedsuppliers::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new LibraryApprovedsuppliers();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new LibraryApprovedsuppliers();
				if($user_type==1 || ($user_type==3 && $resource_access==5)){
					$model->franchise_id = $franchiseid;
				}else{
					$model->franchise_id = $userid;
				}

				$model->created_by = $userData['userid'];
			}

			
			$model->country_id = $data['country_id'];
			$model->supplier_name = $data['supplier_name'];	
			$model->address = $data['address'];	
			$model->contact_person = $data['contact_person'];		
			$model->email = $data['email'];	
			$model->phone = $data['phone'];	
			$model->accreditation = $data['accreditation'];
			$model->certificate_no = $data['certificate_no'];
			$model->scope_of_accreditation = $data['scope_of_accreditation'];
			$model->accreditation_expiry_date = date("Y-m-d",strtotime($data['accreditation_expiry_date']));	
			$model->status = $data['status'];
			if(isset($_FILES['supplier_file']['name']))
			{
				$tmp_name = $_FILES["supplier_file"]["tmp_name"];
				$name = $_FILES["supplier_file"]["name"];
				if($model!==null)
				{
					Yii::$app->globalfuns->removeFiles($model->supplier_file,$target_dir);													
				}
				$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
			}else{
				$filename = $data['supplier_file'];
			}
			$model->supplier_file = $filename;
			
			if($model->validate() && $model->save())
			{	
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Supplier has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Supplier has been created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}

	public function actionStatuslist()
	{
		$model = new LibraryApprovedsuppliers();
		return ['statuslist'=>$model->arrStatus];
	}


	public function actionSupplierfile(){
		$data = Yii::$app->request->post();
		$files = LibraryApprovedsuppliers::find()->where(['id'=>$data['id']])->one();
		//if($data['filetype']=='gisfile'){
			$filename = $files->supplier_file;
		//}
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['library_files']."supplier_files/".$filename;
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
	}

	public function actionDeletesupplierdata()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			//$model = LibraryApprovedsuppliers::deleteAll(['id' => $data['id']]);			
			$target_dir = Yii::$app->params['library_files']."supplier_files/"; 	
			$files = LibraryApprovedsuppliers::find()->where(['id'=>$data['id']])->one();
			if($files!==null)
			{
				Yii::$app->globalfuns->removeFiles($files->supplier_file,$target_dir);	
				$files->delete();
			}
			$responsedata=array('status'=>1,'message'=>'Deleted successfully');
		}
		return $this->asJson($responsedata);
	}
	
	
	
	
}
