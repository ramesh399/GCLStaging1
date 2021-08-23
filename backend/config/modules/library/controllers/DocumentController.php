<?php
namespace app\modules\library\controllers;

use Yii;
use app\modules\library\models\LibraryOspDocument;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * DocumentController implements the CRUD actions for Product model.
 */
class DocumentController extends \yii\rest\Controller
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
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];
		$role_chkid=$userData['role_chkid'];
		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		
		$docmodel = new LibraryOspDocument();
		$model = LibraryOspDocument::find()->alias('t');
		$model = $model->joinWith('franchise as franchise');
		$model = $model->join('left join', 'tbl_user_company_info as usercompanyinfo','usercompanyinfo.user_id=franchise.id');
		if($resource_access != '1')
		{
			if($user_type== Yii::$app->params['user_type']['user'] && $is_headquarters!=1 ){
				$model = $model->andWhere(' t.franchise_id="'.$franchiseid.'" ');
			}
		}

		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm']!='')
			{
				$searchTerm = $post['searchTerm'];
				$doctypearray=array_map('strtolower', $docmodel->arrDocType);
				$doctypesearch = array_search(strtolower($searchTerm),$doctypearray);
				if($doctypesearch===false)
				{
					$doctypesearch='';
				}

				$model = $model->andFilterWhere([
					'or',
					['like', 'document', $searchTerm],
					['like', 'date_format(FROM_UNIXTIME(t.`created_at` ), \'%b %d, %Y\' )', $searchTerm],
					['like', 'CONCAT("OSS ", usercompanyinfo.osp_number)', $searchTerm],
					['like', 'usercompanyinfo.osp_details', $searchTerm],
					['like', 'CONCAT("OSS ", usercompanyinfo.osp_number," - ",usercompanyinfo.osp_details)', $searchTerm],
					['t.document_type_id'=> $doctypesearch],
					//['like', 'usercompanyinfo.osp_details', $searchTerm],
				]);

				
			}
			$totalCount = $model->count();

			if(isset($post['franchiseFilter']) && is_array($post['franchiseFilter']) && count($post['franchiseFilter'])>0)
			{
				$model = $model->andWhere(['t.franchise_id'=> $post['franchiseFilter']]);			
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
		
		$document_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $question)
			{
				$data=array();
				$data['id']=$question->id;
				$data['franchise_id']=$question->franchise_id;
				$data['franchise_id_label']=$question->franchise->usercompanyinfo?'OSS '.$question->franchise->usercompanyinfo->osp_number.' - '.$question->franchise->usercompanyinfo->osp_details:'';
				$data['document_type_id']=$question->document_type_id;
				$data['document_type_id_label'] = $docmodel->arrDocType[$question->document_type_id];
				$data['note']=$question->note;
				$data['document']=$question->document;
				$data['status']=$question->status;
				$data['created_at']=date($date_format,$question->created_at);
				$data['created_by_label']=$question->createdbydata->first_name.' '.$question->createdbydata->last_name;
				$document_list[]=$data;
			}
		}

		return ['documents'=>$document_list,'total'=>$totalCount];
    }


	public function actionCreate()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$datapost = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$target_dir = Yii::$app->params['library_files']."osp_documents/"; 
		
		if($datapost){

			$data =json_decode($datapost['formvalues'],true);
		

			if(isset($data['id']))
			{
				$model = LibraryOspDocument::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new LibraryOspDocument();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new LibraryOspDocument();
				$model->created_by = $userData['userid'];
			}

			
			$model->franchise_id = $data['franchise_id'];
			$model->document_type_id = $data['document_type_id'];	
			$model->note = $data['note'];		
			if(isset($_FILES['document']['name']))
			{
				$tmp_name = $_FILES["document"]["tmp_name"];
				$name = $_FILES["document"]["name"];
				if($model!==null)
				{
					Yii::$app->globalfuns->removeFiles($model->document,$target_dir);													
				}
				$filename=Yii::$app->globalfuns->postFiles($name,$tmp_name,$target_dir);								
			}else{
				$filename = $data['document'];
			}
			$model->document= $filename;
			
			if($model->validate() && $model->save())
			{	
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'OSS Document has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'OSS Document has been created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}

	public function actionDoctypelist()
	{
		$modelObj = new LibraryOspDocument();
		return ['typelist'=>$modelObj->arrDocType];
	}

	public function actionDocumentfile(){
		$data = Yii::$app->request->post();
		$files = LibraryOspDocument::find()->where(['id'=>$data['id']])->one();
		//if($data['filetype']=='gisfile'){
			$filename = $files->document;
		//}
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		
		$filepath=Yii::$app->params['library_files']."osp_documents/".$filename;
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

	public function actionDeletedocumentdata()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$target_dir = Yii::$app->params['library_files']."osp_documents/"; 
				
			$files = LibraryOspDocument::find()->where(['id'=>$data['id']])->one();
			if($files!==null)
			{
				Yii::$app->globalfuns->removeFiles($files->document,$target_dir);	
				$files->delete();
			}
			$responsedata=array('status'=>1,'message'=>'Deleted successfully');
		}
		return $this->asJson($responsedata);
	}
	
	public function actionView()
    {
		$data = Yii::$app->request->post();
		$docmodel = new LibraryOspDocument();
        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$franchise_id=$model->franchise_id;
			$franchise_id_label=$model->franchise->usercompanyinfo?'OSS '.$model->franchise->usercompanyinfo->osp_number.' - '.$model->franchise->usercompanyinfo->osp_details:'';
			$resultarr["note"]=$model->note;
			$resultarr["franchise_id"]=$franchise_id;
			$resultarr["franchise_id_label"]=$franchise_id_label;
			$resultarr["document_type_id"]=$model->document_type_id;
			$resultarr["document_type_id_label"]=$docmodel->arrDocType[$model->document_type_id];
			$resultarr["document"]=$model->document;
			
            return ['data'=>$resultarr];
        }

	}
	
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
           	$model = LibraryOspDocument::find()->where(['id' => $data['id']])->one();
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

    

    protected function findModel($id)
    {
        if (($model = LibraryLegislation::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	
}
