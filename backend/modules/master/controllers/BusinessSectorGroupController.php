<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\Process;
use app\modules\master\models\Standard;
use app\modules\master\models\BusinessSector;
use app\modules\master\models\BusinessSectorGroup;
use app\modules\master\models\BusinessSectorGroupProcess;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;
/**
 * BusinessSectorGroupController implements the CRUD actions for Standard model.
 */
class BusinessSectorGroupController extends \yii\rest\Controller
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
			'authenticator' => ['class' => JwtHttpBearerAuth::class]
		];        
    }

    public function actionIndex()
    {
		if(!Yii::$app->userrole->hasRights(array('business_sector_group_master')))
		{
			return false;
		}

		$post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$model = BusinessSectorGroup::find()->alias('t');
		//$model->joinWith(['standard as std','businesssector as bs']);
		$model = $model->join('inner join', 'tbl_standard as std','std.id=t.standard_id');
		$model = $model->join('inner join', 'tbl_business_sector as bs','bs.id=t.business_sector_id');
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 't.group_code', $searchTerm],
					['like', 'bs.name', $searchTerm],
					['like', 'std.name', $searchTerm],
					['like', '(date_format(FROM_UNIXTIME(t.created_at), \'%b %d, %Y\' ))', $searchTerm],
					
				]);

				$totalCount = $model->count();
			}

			if(isset($post['bsectorFilter']) && is_array($post['bsectorFilter']) && count($post['bsectorFilter'])>0)
			{
				$model = $model->andWhere(['t.business_sector_id'=> $post['bsectorFilter']]);
			}

			if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
			{
				$model = $model->andWhere(['t.standard_id'=> $post['standardFilter']]);
			}

			$sortDirection = isset($post['sortDirection']) && $post['sortDirection']=='desc' ?SORT_DESC:SORT_ASC;
			if(isset($post['sortColumn']) && $post['sortColumn'] !='')
			{
				
				$sortColumn = $post['sortColumn'];
				if($sortColumn=='bsector')
				{
					$sortColumn='bs.name';					
				}elseif($sortColumn=='standard'){
					$sortColumn='std.name';
				}
				$model = $model->orderBy([$sortColumn=>$sortDirection]);				
				//$model = $model->orderBy([$post['sortColumn']=>$sortDirection]);
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
		$model->andWhere(['<>','t.status',2]);
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $obj)
			{
				$data=array();
				$data['id']=$obj->id;
				$data['group_code']=$obj->group_code;
				$data['bsector']=$obj->businesssector->name;
				$data['standard']=$obj->standard->name;
				$data['status']=$obj->status;
				$data['created_at']=date($date_format,$obj->created_at);
				$list[]=$data;
			}
		}
		
		return ['bsectorgroups'=>$list,'total'=>$totalCount];
	}

	// public function actionList()
    // {
	// 	$post = yii::$app->request->post();
	// 	$standard_id = $post['id'];

	// 	$list = BusinessSectorGroup::find()->select(['id','name'])->where(['status'=>0,'standard_id'=>$standard_id])->asArray()->all();
	// 	return ['data'=>$list,'total'=>count($list)];
	// }
	
    public function actionCreate()
    {
		if(!Yii::$app->userrole->hasRights(array('add_business_sector_group')))
		{
			return false;
		}

		$model = new BusinessSectorGroup();		
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{		
			$model->group_code=$data['group_code'];
			$model->group_details=$data['group_details'];
			$model->business_sector_id=$data['business_sector_id'];
			$model->standard_id=$data['standard_id'];
			
			$userData = Yii::$app->userdata->getData();
			$model->created_by=$userData['userid'];
			
			if($model->validate() && $model->save())
			{
				/*
				if(is_array($data['process']) && count($data['process'])>0)
				{
					foreach ($data['process'] as $value)
					{ 
						$groupmodel=new BusinessSectorGroupProcess();
						$groupmodel->business_sector_group_id=$model->id;
						$groupmodel->process_id=$value;
						$groupmodel->save();
					}
				}
				*/
				$responsedata=array('status'=>1,'message'=>'Business Sector Group has been created successfully');	
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$model->errors);	
			}
		}
		return $this->asJson($responsedata);
    }

    
    public function actionUpdate()
    {
		if(!Yii::$app->userrole->hasRights(array('edit_business_sector_group')))
		{
			return false;
		}

		$data = Yii::$app->request->post();
		$model = BusinessSectorGroup::find()->where(['id' => $data['id']])->one();		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{		
			$model->group_code=$data['group_code'];
			$model->group_details=$data['group_details'];
			$model->business_sector_id=$data['business_sector_id'];
			$model->standard_id=$data['standard_id'];
			
			$userData = Yii::$app->userdata->getData();
			$model->created_by=$userData['userid'];
			
			if($model->validate() && $model->save())
			{
				/*
				if(is_array($data['process']) && count($data['process'])>0)
				{
					BusinessSectorGroupProcess::deleteAll(['business_sector_group_id' => $model->id]);
					foreach ($data['process'] as $value)
					{ 
						$groupmodel=new BusinessSectorGroupProcess();
						$groupmodel->business_sector_group_id=$model->id;
						$groupmodel->process_id=$value;
						$groupmodel->save();
					}
				}
				*/
				$responsedata=array('status'=>1,'message'=>'Business Sector Group has been updated successfully');	
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$model->errors);	
			}
		}
		return $this->asJson($responsedata);
	}
	
	public function actionGetbsectorGroup()
    {
		$data = Yii::$app->request->post();
		if($data)
		{
			$model = $this->findModel($data['id']);
			if ($model !== null)
			{
				$resultarr=array();
				$resultarr['group_code']=$model->group_code;
				$resultarr['group_details']=$model->group_details;
				$resultarr['bsector']=$model->businesssector->name;
				$resultarr['standard']=$model->standard->name;

				$groupprocess=$model->businesssectorgroupprocess;
				if($groupprocess !== null)
				{
					$groupprocessArray=array();
					foreach($groupprocess as $val)
					{
						$groupprocessArray[]=$val->process->name;
					}
					$resultarr['process']=$groupprocessArray;
				}		
				return ['data'=>$resultarr];
			}
		}
	}

    public function actionView()
    {
		if(!Yii::$app->userrole->hasRights(array('business_sector_group_master')))
		{
			return false;
		}

		$data = Yii::$app->request->post();
		
        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
			$resultarr=array();
			$resultarr['id']=$model->id;
			$resultarr['group_code']=$model->group_code;
			$resultarr['group_details']=$model->group_details;
			$resultarr['business_sector_id']=$model->business_sector_id;
			$resultarr['standard_id']=$model->standard_id;

			$processmodel = BusinessSectorGroupProcess::find()->where(['business_sector_group_id' => $data['id']])->all();
			if($processmodel !== null)
			{
				$processArray=array();
				foreach($processmodel as $val)
				{
					$processArray[]="$val->process_id";
				}
				$resultarr['process']=$processArray;
			}		
            return ['data'=>$resultarr];
        }

    }
	
	public function actionCommonUpdate()
	{
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data && isset($data['status'])) 
		{
			$id=$data['id'];
			$status = $data['status'];

			if(!Yii::$app->userrole->canDoCommonUpdate($status,'business_sector_group'))
			{
				return false;
			}	


           	$model = BusinessSectorGroup::find()->where(['id' => $id])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Business Sector Group has been activated successfully';
					}elseif($model->status==1){
						$msg='Business Sector Group has been deactivated successfully';
					}elseif($model->status==2){
						$exists=0;

						// if(ApplicationProductStandard::find()->where( [ 'label_grade_id' => $id ] )->exists()){
                        //     $exists=1;
						// }
						// else
						// {
						// 	$exists=0;
						// }

						if($exists==0)
                        {
                            //StandardLabelGrade::findOne($id)->delete();
						}
						$msg='Business Sector Group has been deleted successfully';
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
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}
            return $this->asJson($responsedata);
        }
	}
	 /**
     * Finds the BusinessSectorGroup model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return BusinessSectorGroup the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BusinessSectorGroup::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
   
}
