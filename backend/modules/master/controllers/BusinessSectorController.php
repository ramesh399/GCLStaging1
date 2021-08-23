<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\BusinessSector;
use app\modules\master\models\BusinessSectorGroup;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;


/**
 * BusinessSectorController implements the CRUD actions for BusinessSector model.
 */
class BusinessSectorController extends \yii\rest\Controller
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
		if(!Yii::$app->userrole->hasRights(array('business_sector_master')))
		{
			return false;
		}

		$post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$model = BusinessSector::find()->where(['<>','status',2]);
		
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
					['like', '(date_format(FROM_UNIXTIME(`created_at` ), \'%b %d, %Y\' ))', $searchTerm],
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
		
		$BusinessSector_list=array();
		//$model = $model->asArray()->all();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $sectors)
			{
				$data=array();
				$data['id']=$sectors->id;
				$data['name']=$sectors->name;
				$data['status']=$sectors->status;
				//$data['created_at']=date('M d,Y h:i A',$process->created_at);
				$data['created_at']=date($date_format,$sectors->created_at);
				$BusinessSector_list[]=$data;
			}
		}
		
		return ['bsectors'=>$BusinessSector_list,'total'=>$totalCount];
    }
	
	public function actionGetBusinessSector()
	{
		$Country = BusinessSector::find()->select(['id','name'])->where(['status'=>0])->asArray()->all();
		return ['bsectors'=>$Country];
	}

	public function actionBusinessSectors()
    {
        $data = Yii::$app->request->post();
		if($data)
		{
			$stds='';$process='';
			foreach($data['standardvals'] as $value)
			{
				$stds.=$value.",";
			}
			$std_ids=substr($stds, 0, -1);
			
			/*
			foreach($data['processvals'] as $val)
			{
				$process.=$val.",";
			}
			$process_ids=substr($process, 0, -1);
			*/

			$connection = Yii::$app->getDb();
			//$command = $connection->createCommand("SELECT bs.id,bs.name FROM tbl_business_sector AS bs INNER JOIN tbl_business_sector_group AS bsg ON bs.id=bsg.business_sector_id AND bsg.standard_id IN (".$std_ids.") INNER JOIN tbl_business_sector_group_process AS bsgp ON bsg.id=bsgp.business_sector_group_id AND bsgp.process_id IN (".$process_ids.") GROUP BY bs.id");
			$command = $connection->createCommand("SELECT bs.id,bs.name FROM tbl_business_sector AS bs INNER JOIN tbl_business_sector_group AS bsg ON bs.id=bsg.business_sector_id AND bsg.standard_id IN (".$std_ids.") GROUP BY bs.id");
			
			
			$result = $command->queryAll();
			if(count($result)>0)
			{
				$resultarr=array();
				foreach($result as $data)
				{
					$values=array();
					$values['id'] = $data['id'];
					$values['name'] = $data['name'];
					$resultarr[]=$values;
				}

			}
			return ['bsectors'=>$resultarr];	
		}
	}

	public function actionGetBusinessSectorsByStandard()
    {
        $data = Yii::$app->request->post();
		if($data)
		{
			$stds='';$process='';

			$std_ids='';
			if(isset($data['standardvals']) && is_array($data['standardvals'])){
				foreach($data['standardvals'] as $value)
				{
					$stds.=$value.",";
				}
				$std_ids=substr($stds, 0, -1);
			}else if(isset($data['standardvals'])){
				$std_ids =$data['standardvals'];
			}
			$resultarr=array();
			if($std_ids !=''){
				$connection = Yii::$app->getDb();
				
				$command = $connection->createCommand("SELECT bs.id,bs.name FROM tbl_business_sector AS bs INNER JOIN tbl_business_sector_group AS bsg ON bs.id=bsg.business_sector_id AND bsg.standard_id IN (".$std_ids.") WHERE bs.status=0 and bsg.status=0 GROUP BY bs.id");
				
				
				$result = $command->queryAll();
				if(count($result)>0)
				{
					
					foreach($result as $data)
					{
						$values=array();
						$values['id'] = $data['id'];
						$values['name'] = $data['name'];
						$resultarr[]=$values;
					}

				}
			}
			return ['bsectors'=>$resultarr];	
		}
	}

	public function actionGetBusinessSectorGroupsByStandard()
    {
        $data = Yii::$app->request->post();
		if($data)
		{
			$resultarr=array();
			$stds='';$bsectors='';
			$std_ids = '';$bsector_ids = '';
			if(isset($data['standardvals']) && is_array($data['standardvals'])){
				foreach($data['standardvals'] as $value)
				{
					$stds.=$value.",";
				}
				$std_ids=substr($stds, 0, -1);
			}else if(isset($data['standardvals'])){
				$std_ids =$data['standardvals'];
			}
			

			if(isset($data['bsectorvals']) && is_array($data['bsectorvals']) && count($data['bsectorvals'])>0)
			{
				foreach($data['bsectorvals'] as $vals)
				{
					$bsectors.=$vals.",";
				}
				$bsector_ids=substr($bsectors, 0, -1);
			}else if(isset($data['bsectorvals'])){
				$bsector_ids = $data['bsectorvals'];
			}
			$resultarr=array();

			if(is_array($data['standardvals'])){
				$connection = Yii::$app->getDb();
				$command = $connection->createCommand("SELECT  procs.id,procs.name FROM `tbl_business_sector_group` AS bsg INNER JOIN `tbl_business_sector_group_process` AS bsgp ON bsg.id=bsgp.business_sector_group_id INNER JOIN `tbl_process` AS procs ON procs.id=bsgp.process_id WHERE bsg.standard_id IN (".$std_ids.") AND bsg.business_sector_id IN(".$bsector_ids.") GROUP BY procs.id");
				
				
				$result = $command->queryAll();
				if(count($result)>0)
				{
					
					foreach($result as $data)
					{
						$values=array();
						$values['id'] = $data['id'];
						$values['name'] = $data['name'];
						$resultarr[]=$values;
					}

				}
				return ['processes'=>$resultarr];
			}else if($std_ids != '' && $bsector_ids != '')
			{
				$connection = Yii::$app->getDb();
				$command = $connection->createCommand("SELECT  bsg.id,bsg.group_code FROM `tbl_business_sector_group` AS bsg  WHERE bsg.standard_id IN (".$std_ids.") AND bsg.status=0 AND bsg.business_sector_id IN(".$bsector_ids.") GROUP BY bsg.id");
				
				
				$result = $command->queryAll();
				if(count($result)>0)
				{
					
					foreach($result as $data)
					{
						$values=array();
						$values['id'] = $data['id'];
						$values['group_code'] = $data['group_code'];
						$resultarr[]=$values;
					}

				}
				return ['bsectorgroups'=>$resultarr];	
			}
			
		}
	}
	


	public function actionBusinessSectorGroups()
    {
        $data = Yii::$app->request->post();
		if($data)
		{
			if($data)
			{
				$stds='';$process='';$bsectors='';
				if(isset($data['standardvals']) && is_array($data['standardvals']) && count($data['standardvals'])>0)
				{
					foreach($data['standardvals'] as $value)
					{
						$stds.=$value.",";
					}
					$std_ids=substr($stds, 0, -1);
				}else{
					$std_ids = '0';
				}
				
				/*
				if(isset($data['processvals']) && is_array($data['processvals']) && count($data['processvals'])>0)
				{
					foreach($data['processvals'] as $val)
					{
						$process.=$val.",";
					}
					$process_ids=substr($process, 0, -1);
				}else{
					$process_ids = '0';
				}
				*/
				

				if(isset($data['bsectorvals']) && is_array($data['bsectorvals']) && count($data['bsectorvals'])>0)
				{
					foreach($data['bsectorvals'] as $vals)
					{
						$bsectors.=$vals.",";
					}
					$bsector_ids=substr($bsectors, 0, -1);
				}else{
					$bsector_ids = '0';
				}
				
				


				$connection = Yii::$app->getDb();
				//$command = $connection->createCommand("SELECT bsg.id,bsg.group_code FROM `tbl_business_sector_group` AS bsg 	INNER JOIN `tbl_business_sector_group_process` AS bsgp ON bsg.id=bsgp.business_sector_group_id AND bsgp.process_id IN (".$process_ids.") WHERE bsg.standard_id IN (".$std_ids.") AND bsg.business_sector_id IN(".$bsector_ids.") GROUP BY bsg.group_code");
				$command = $connection->createCommand("SELECT bsg.id,bsg.group_code FROM `tbl_business_sector_group` AS bsg WHERE bsg.standard_id IN (".$std_ids.") AND bsg.business_sector_id IN(".$bsector_ids.") GROUP BY bsg.group_code");
				
				
				$result = $command->queryAll();
				if(count($result)>0)
				{
					$resultarr=array();
					foreach($result as $data)
					{
						$values=array();
						$values['id'] = $data['id'];
						$values['group_code'] = $data['group_code'];
						$resultarr[]=$values;
					}

				}
				return ['bsectorgroups'=>$resultarr];	
			}	
		}
    }

    public function actionCreate()
	{
		if(!Yii::$app->userrole->hasRights(array('add_business_sector')))
		{
			return false;
		}

		$model = new BusinessSector();		
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{		
			$model->name=$data['name'];
			$model->description=$data['description'];
			
			$userData = Yii::$app->userdata->getData();
			$model->created_by=$userData['userid'];
			
			if($model->validate() && $model->save())
			{
				$responsedata=array('status'=>1,'message'=>'Business Sector has been created successfully');	
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
		if(!Yii::$app->userrole->hasRights(array('edit_business_sector')))
		{
			return false;
		}

		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data) 
		{
           	$model = BusinessSector::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->name=$data['name'];
				$model->description=$data['description'];
				
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];
			
				if($model->validate() && $model->save())
				{
					$responsedata=array('status'=>1,'message'=>'Business Sector has been updated successfully');
				}
				else
				{
					$responsedata=array('status'=>0,'message'=>$model->errors);
				}
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}
            return $this->asJson($responsedata);
        }
    }

    public function actionView()
    {
		if(!Yii::$app->userrole->hasRights(array('business_sector_master')))
		{
			return false;
		}

		$data = Yii::$app->request->post();
		
        $model = $this->findModel($data['id']);
        if ($model !== null)
		{
            return ['data'=>$model];
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
			
			if(!Yii::$app->userrole->canDoCommonUpdate($status,'business_sector'))
			{
				return false;
			}	

           	$model = BusinessSector::find()->where(['id' => $id])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Business Sector has been activated successfully';
					}elseif($model->status==1){
						$msg='Business Sector has been deactivated successfully';
					}elseif($model->status==2){
						$exists=0;

                        // if(UserProcess::find()->where( [ 'process_id' => $id ] )->exists()){
                        //     $exists=1;
                        // }elseif(ApplicationUnitProcess::find()->where( [ 'process_id' => $id ] )->exists()){
                        //     $exists=1;
                        // }elseif(QualificationQuestionProcess::find()->where( [ 'process_id' => $id ] )->exists()){
                        //     $exists=1;
                        // }else{
                        //     $exists=0;
                        // }
						
						if($exists==0)
                        {
                            //Process::findOne($id)->delete();
                        }
						$msg='Business Sector has been deleted successfully';
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
     * Finds the Country model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Country the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BusinessSector::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
   
}
