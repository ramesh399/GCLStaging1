<?php
namespace app\modules\master\controllers;

use Yii;
use app\modules\master\models\User;
use app\modules\master\models\Role;
use app\modules\master\models\Rule;
use app\modules\master\models\Privileges;
use app\modules\master\models\UserQualificationReview;
use app\modules\master\models\QualificationQuestionRole;
use app\modules\master\models\UserQualificationReviewHistoryRelRoleStandard;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;
/**
 * UserRoleController implements the CRUD actions for Role model.
 */
class UserRoleController extends \yii\rest\Controller
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
		if(!Yii::$app->userrole->hasRights(array('user_role_master')))
		{
			return false;
		}
		
		$post = yii::$app->request->post();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$model = Role::find()->where(['<>','status',2]);
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 'role_name', $searchTerm],
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
		
		$userrole_list=array();
		//$model = $model->asArray()->all();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $userrole)
			{
				$data=array();
				$data['id']=$userrole->id;
				$data['role_name']=$userrole->role_name;
				$data['resource_access']=$userrole->resource_access;
				$data['resource_access_name']=($userrole->resource_access!="1")?"Custom":"All";
				$data['status']=$userrole->status;
				$data['created_at']=date($date_format,$userrole->created_at);
				$userrole_list[]=$data;
			}
		}
		
		return ['userroles'=>$userrole_list,'total'=>$totalCount];
    }
	
	public function actionGetRoles()
	{
		$Roles = Role::find()->select(['id','role_name','resource_access'])->where(['status'=>0])->asArray()->all();
		return ['userroles'=>$Roles];
		/*
		$roles = Role::find()->select(['id','role_name','resource_access'])->where(['status'=>0])->all();
		$rolesList = [];
		if(count($roles)>0){
			foreach($roles as $role){
				
				$rolesList[] = ['id'=>$role->id,'role_name'=>$role->role_name,'resource_access'=>$role->resource_access,'loginrequired'=>0];
			}
		}
		*/
	}

	public function actionGetUserRoles()
	{
		$Roles = new Role();
		return ['userroles'=>$Roles->arrRoles];
	}

    public function actionCreate()
	{
		if(!Yii::$app->userrole->hasRights(array('add_user_role')))
		{
			return false;
		}
		
		$model = new Role();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if (Yii::$app->request->post()) 
		{
			$data = Yii::$app->request->post();
			
			$model->role_name=$data['role_name'];
			$model->resource_access=$data['resource_access'];
			$model->enable_oss=$data['enable_oss'];
			
			$userData = Yii::$app->userdata->getData();
			$model->created_by=$userData['userid'];
						
			if($model->validate() && $model->save())
			{
				if(is_array($data['privilege_id']) && count($data['privilege_id'])>0)
				{
					//do
					//{
						$parentIds = $this->insertRules($data['privilege_id'],$model->id);
					//}while(count($parentIds)<=0);
					/*
					foreach ($data['privilege_id'] as $value)
					{ 
						$privilegemodel = Privileges::find()->where(['id' =>$value])->one();
						$Userrulemodel=new Rule();
						$Userrulemodel->role_id=$model->id;
						$Userrulemodel->privilege_id=$value;
						$Userrulemodel->privilege=$privilegemodel->code;					
						$Userrulemodel->save();
					}
					*/
				}
				
				$responsedata=array('status'=>1,'message'=>'User Previlege has been created successfully');	
			}
			else
			{
				$responsedata=array('status'=>0,'message'=>$model->errors);
			}
		}
		return $this->asJson($responsedata);
	}

	private function insertRules($ids,$role_id){
		$privilegemodel = Privileges::find()->where(['id' =>$ids])->all();
		$parentIds = [];
		foreach ($privilegemodel as $privilegeobj)
		{ 
			$parent_id = $privilegeobj->parent_id;
			if($parent_id !=0){
				$parentIds[$parent_id] = $parent_id;
			}

			$Userrulemodel=new Rule();
			$Userrulemodel->role_id=$role_id;
			$Userrulemodel->privilege_id=$privilegeobj->id;
			$Userrulemodel->privilege=$privilegeobj->code;					
			$Userrulemodel->save();
		}
		return $parentIds;
	}

	


    public function actionUpdate()
    {
		if(!Yii::$app->userrole->hasRights(array('edit_user_role')))
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
       	$data = Yii::$app->request->post();
		if ($data) 
		{
           	$model = Role::find()->where(['id' => $data['id']])->one();
			if ($model !== null)
			{
				$model->role_name=$data['role_name'];
				$model->resource_access=$data['resource_access'];
				$model->enable_oss=$data['enable_oss'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];

				if($model->validate() && $model->save())
				{
					Rule::deleteAll(['role_id' => $model->id]);
					if(is_array($data['privilege_id']) && count($data['privilege_id'])>0)
					{
						//do
						//{
							$parentIds = $this->insertRules($data['privilege_id'],$model->id);
						//}while(count($parentIds)<=0);

						/*
						$parentIds = [];
						$privilegemodel = Privileges::find()->where(['id' =>$data['privilege_id']])->all();
						foreach ($privilegemodel as $privilegeobj)
						{ 
							$parent_id = $privilegeobj->parent_id;
							if($parent_id !=0){
								$parentIds[$parent_id] = $parent_id;
							}
							$Userrulemodel=new Rule();
							$Userrulemodel->role_id=$model->id;
							$Userrulemodel->privilege_id=$privilegeobj->id;
							$Userrulemodel->privilege=$privilegeobj->code;					
							$Userrulemodel->save();
						}

						$privilegemodel = Privileges::find()->where(['id' =>$parentIds])->all();

						foreach ($privilegemodel as $privilegeobj)
						{ 
							$parent_id = $privilegeobj->parent_id;
							if($parent_id !=0){
								$parentIds[$parent_id] = $parent_id;
							}

							$Userrulemodel=new Rule();
							$Userrulemodel->role_id=$model->id;
							$Userrulemodel->privilege_id=$value;
							$Userrulemodel->privilege=$privilegemodel->code;					
							$Userrulemodel->save();
						}
						*/
					}
					$responsedata=array('status'=>1,'message'=>'User Previlege has been updated successfully');
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
		if(!Yii::$app->userrole->hasRights(array('user_role_master')))
		{
			return false;
		}
		
		$data = Yii::$app->request->post();
		
        $model = Role::find()->where(['id' =>$data['id']])->one();
        if ($model !== null)
		{
			$privilids = [];
			$model['id']=$model->id;
			$model['role_name']=$model->role_name;
			$model['resource_access']=$model->resource_access;
			$Userrulemodel = Rule::find()->select('privilege_id')->where(['role_id' => $data['id']])->asArray()->all();
			foreach($Userrulemodel as $val)
			{
				$privilids[]=$val['privilege_id'];
			}	
		}
		//print_r($privilids); die;
		$privilegeslist = $this->actionPrivileges($privilids);
		//print_r($privilegeslist); die;
		return ['data'=>$model,'privileges'=>$privilegeslist];
	}
	
	public function actionCommonUpdate()
	{
		
		$data = Yii::$app->request->post();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		if ($data && isset($data['status'])) 
		{
			$id=$data['id'];
			$status = $data['status'];	

			if(!Yii::$app->userrole->canDoCommonUpdate($status,'user_role'))
			{
				return false;
			}	
			
           	$model = Role::find()->where(['id' => $id])->one();
			if ($model !== null)
			{
				$model->status=$data['status'];
				$userData = Yii::$app->userdata->getData();
				$model->updated_by=$userData['userid'];			
				if($model->validate() && $model->save())
				{
					$msg='';
					if($model->status==0){
						$msg='Role has been activated successfully';
					}elseif($model->status==1){
						$msg='Role has been deactivated successfully';
					}elseif($model->status==2){
						$exists=0;
						/*
                        if(User::find()->where( [ 'role_id' => $id ] )->exists())
                        {
                            $exists=1;
                        }
                        elseif(UserQualificationReview::find()->where( [ 'user_role_id' => $id ] )->exists())
                        {
                            $exists=1;
                        }
                        elseif(QualificationQuestionRole::find()->where( [ 'role_id' => $id ] )->exists())
                        {
                            $exists=1;
						}
						elseif(UserQualificationReviewHistoryRelRoleStandard::find()->where([ 'user_role_id' => $id ])->exists())
                        {
                            $exists=1;
                        }
                        else
                        {
                            $exists=0;
                        }
                        */
                        if($exists==0)
                        {
                            //Role::findOne($id)->delete();
						}
						
						$msg='Role has been deleted successfully';
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

	public function actionPrivileges($privilegesarr=[])
    {
		$priviledgeModel=new Privileges();
		
		$model = Privileges::find()->where(['status'=>1])->orderBy(['name' => SORT_ASC])->asArray()->all();
		$treeView=$priviledgeModel->buildTree($model);


		$parent =[];
		$i=0;
		
		foreach($treeView as $view){
			//print_r($view);
			$parent[$i]['text'] = $view['name'];
			$parent[$i]['value'] = (int)$view['id'];
			$parent[$i]['collapsed'] = false;
			$parent[$i]['checked'] = in_array($view['id'],$privilegesarr,true)?true:false;
			$parent[$i]['children'] = $this->formatTree($view['children'],$privilegesarr);
			$i++;
			
		}		
		return $parent;
	}

	private function formatTree($childrens,$privilegesarr=[])
	{
		$ic =0;
		$carr=[];
		foreach($childrens as $children){
			$carr[$ic]['text'] = $children['name'];
			$carr[$ic]['value'] = (int)$children['id'];
			$carr[$ic]['collapsed'] = false;
			$carr[$ic]['checked'] = in_array($children['id'],$privilegesarr,true)?true:false;
			if(is_array($children['children']) && count($children['children']) >0){
				$carr[$ic]['children'] = $this->formatTree($children['children'],$privilegesarr);
			}
			$ic++;
		}
		return $carr;		
	}
	
	
	public function actionFranchiseBasedUserRole()
	{
		$model = new Role();
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{
			$headquarters = Yii::$app->globalfuns->getSettings('headquarters');
			
			$connection = Yii::$app->getDb();
			$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
			
			if($headquarters==$data['franchise_id'])
			{
				$roleQry = 'SELECT role.id,role.role_name,role.resource_access,role.status FROM `tbl_role` AS role where resource_access not in (6,7) ';
			}else{
				
				$reviewerAssignedRoleQuery="SELECT group_concat(DISTINCT role.id) as roleid FROM `tbl_role` AS role 
				INNER JOIN `tbl_rule` AS rule ON role.id=rule.role_id
				WHERE rule.privilege IN ('audit_review','application_review')";
				
				$reviewerRoleIDs='';
				$commandReviewerAssignedRole = $connection->createCommand($reviewerAssignedRoleQuery);
				$resultReviewerAssignedRole = $commandReviewerAssignedRole->queryOne();
				if($resultReviewerAssignedRole!==false)
				{
					$reviewerRoleIDs=$resultReviewerAssignedRole['roleid'];
				}
				
				/*
				$roleQry = 'SELECT role.id,role.role_name,role.resource_access,role.status FROM `tbl_role` AS role 
				LEFT JOIN `tbl_rule` AS rule ON role.id=rule.role_id
				WHERE (role.resource_access IN (3,4,5) OR rule.privilege IN (\'audit_execution\') )';
				*/
				//OR LOWER(role.role_name)="osp admin" 
				
				$roleQry = 'SELECT role.id,role.role_name,role.resource_access,role.status FROM `tbl_role` AS role 
				LEFT JOIN `tbl_rule` AS rule ON role.id=rule.role_id
				WHERE enable_oss = 1';
				
				if($reviewerRoleIDs!='')
				{
					$roleQry.=' and role.id not in('.$reviewerRoleIDs.') ';
				}
				$roleQry.=' group by role.id ';
			}
			
			
			$command = $connection->createCommand($roleQry);
						
			$result = $command->queryAll();
					
			$userrole_list=array();
			if(count($result)>0)
			{
				foreach($result as $vals)
				{
					$data=array();
					$data['id']=$vals['id'];
					$data['role_name']=$vals['role_name'];
					$data['resource_access']=$vals['resource_access'];
					$data['resource_access_name']=($vals['resource_access']!="1")?"Custom":"All";
					$data['status']=$vals['status'];
					$userrole_list[]=$data;
				}
			}			
			$responsedata = ['userroles'=>$userrole_list,'status'=>1];			
		}
		return $this->asJson($responsedata);
	}
	
	
}
