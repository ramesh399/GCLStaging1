<?php
namespace app\modules\transfercertificate\controllers;

use Yii;
use app\modules\transfercertificate\models\TcStandard;
use app\modules\transfercertificate\models\TcStandardLabelGrade;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * TcStandardController implements the CRUD actions for Product model.
 */
class TcStandardLabelGradeController extends \yii\rest\Controller
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
		if(!Yii::$app->userrole->hasRights(array('tc_standard_label_grade')))
		{
			return false;
		}

		$post = Yii::$app->request->post();		
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelObj = new TcStandardLabelGrade();		
		if($post)
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
			
			$model = TcStandardLabelGrade::find()->where(['<>','status',$modelObj->enumStatus['archived']]);
			if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
			{
				$model = $model->andWhere(['tc_standard_id'=> $post['standardFilter']]);			
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
						['like', 'name', $searchTerm],						
						['like', 'date_format(FROM_UNIXTIME(`created_at` ), \'%b %d, %Y\' )', $searchTerm]
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
				foreach($model as $modelData)
				{	
					$data=array();
					$data['id']=$modelData->id;
					$data['name']=$modelData->name;					
					$data['created_by_label']=$modelData->createdbydata->first_name.' '.$modelData->createdbydata->last_name;										
					$data['created_at']=date($date_format,$modelData->created_at);
					
					$data['standard_id']=$modelData->tc_standard_id;	

					$data['standard_id_label']=($modelData->standard ? $modelData->standard->name : 'NA');			
					
					/*
					$labelgradestandard = $modelData->labelgradestandard;
					if(count($labelgradestandard)>0)
					{
						$standard_id_arr = array();
						$standard_id_label_arr = array();
						foreach($labelgradestandard as $val)
						{
							if($val->standard!==null)
							{
								$standard_id_arr[]="".$val['tc_label_grade_id'];
								$standard_id_label_arr[]=($val->standard ? $val->standard->name : '');
							}
						}
						$data["standard_id"]=$standard_id_arr;
						$data["standard_id_label"]=implode(', ',$standard_id_label_arr);
					}
					*/
				
					$list[]=$data;
				}
			}
		}
		return ['standardLabelGrade'=>$list,'total'=>$totalCount];
    }

    public function actionCreate()
	{
		$modelTcStandardLabelGrade = new TcStandardLabelGrade();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		//$data = Yii::$app->request->post();
		$datapost = Yii::$app->request->post();
		if ($datapost) 
		{	
			$editStatus=1;
			
			$data =json_decode($datapost['formvalues'],true);			
			if(isset($data['id']) && $data['id']>0)
			{
				$model = TcStandardLabelGrade::find()->where(['id' => $data['id']])->one();
				if($model===null)
				{
					$model = new TcStandardLabelGrade();
					$editStatus=0;
				}
			}else{
				$editStatus=0;
				$model = new TcStandardLabelGrade();
			}
			
			$currentAction = 'add_tc_standard_label_grade';
			if($editStatus==1)
			{
				$currentAction = 'edit_tc_standard_label_grade';
			}

			if(!Yii::$app->userrole->hasRights(array($currentAction)))
			{
				return false;
			}
			
			$model->name = $data['name'];	
			$model->tc_standard_id = $data['standard_id'];
			
			$userData = Yii::$app->userdata->getData();
			$model->created_by = $userData['userid'];			
			if($model->validate() && $model->save())
			{	
				$manualID = $model->id;
				
				/*
				if(is_array($data['standard_id']) && count($data['standard_id'])>0)
				{
					TcStandardLabelGradeStandard::deleteAll(['tc_label_grade_id' => $model->id]);
					foreach ($data['standard_id'] as $value)
					{ 
					
						$TcStandardLabelGradeStandardModel =  new TcStandardLabelGradeStandard();
						$TcStandardLabelGradeStandardModel->tc_label_grade_id = $model->id;
						$TcStandardLabelGradeStandardModel->tc_standard_id = $value;
						$TcStandardLabelGradeStandardModel->save();
						
					}
				}
				*/
								
				$userMessage = 'Standard Label Grade has been created successfully';
				if($editStatus==1)
				{
					$userMessage = 'Standard Label Grade has been updated successfully';
				}				
				$responsedata=array('status'=>1,'message'=>$userMessage);	
			}else{
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}		
		}
		return $this->asJson($responsedata);
	}	
	
	public function actionView()
    {
		if(!Yii::$app->userrole->hasRights(array('tc_standard_label_grade')))
		{
			return false;
		}
		
		$data = Yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$modelObj = new TcStandardLabelGrade();

        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$resultarr=array();
			$resultarr["id"]=$model->id;
			$resultarr["name"]=$model->name;					
            return ['data'=>$resultarr];
        }

	}

	public function actionGetStandardLabel()
	{
		$datapost = Yii::$app->request->post();
		if ($datapost) 
		{	
			$TcStandardLabelGrade = new TcStandardLabelGrade();
			$Standardlabel = TcStandardLabelGrade::find()->select(['id','name','code'])->where(['tc_standard_id'=>$datapost['id'],'status'=>$TcStandardLabelGrade->enumStatus['approved'] ])->asArray()->all();
			return ['standardlabelgrade'=>$Standardlabel];
		}
	}
    

    protected function findModel($id)
    {
        if (($model = TcStandardLabelGrade::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
	public function actionDeletedata()
	{
		if(!Yii::$app->userrole->hasRights(array('delete_tc_standard_label_grade')))
		{
			return false;
		}
		
		$data = Yii::$app->request->post();
		if($data)
		{			
			$id = $data['id'];
			//TcStandardLabelGradeStandard::deleteAll(['tc_label_grade_id' => $data['id']]);
			$TcStandardLabelGradeModel = TcStandardLabelGrade::find()->where(['id'=>$id])->one();
			if($TcStandardLabelGradeModel!==null)
			{
				$TcStandardLabelGradeModel->status = $TcStandardLabelGradeModel->enumStatus['archived'];
				$TcStandardLabelGradeModel->save();
				//$TcStandardLabelGradeModel->delete();
			}	
		}
		return $responsedata=array('status'=>1,'message'=>'Data deleted successfully');
	}
	
	
}
