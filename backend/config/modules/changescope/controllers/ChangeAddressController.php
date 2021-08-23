<?php
namespace app\modules\changescope\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationChangeAddress;

use app\modules\changescope\models\ChangeAddress;
use app\modules\changescope\models\ChangeAddressUnit;


use app\modules\master\models\UserCompanyInfo;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\MailLookup;


use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * ChangeAddressController implements the CRUD actions for Product model.
 */
class ChangeAddressController extends \yii\rest\Controller
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
		$role_chkid=$userData['role_chkid'];
		$addressmodel = new ChangeAddress();

		$model = ChangeAddress::find()->alias('t');
		$model = $model->innerJoinWith(['application as app']);	
		if($resource_access != '1')
		{

			if($user_type== Yii::$app->params['user_type']['customer']){
				$model = $model->andWhere('app.customer_id="'.$userid.'"');
			}else if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$model = $model->andWhere(' app.franchise_id="'.$userid.'" ');
			}
		}
		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']) && $post['searchTerm']!='')
			{
				$model = $model->innerJoinWith('applicationaddress as caddress');
				
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',	
					['like', 'caddress.company_name', $searchTerm],
					['like', 'caddress.telephone', $searchTerm],
					['like', 'caddress.first_name', $searchTerm],
					['like', 'caddress.last_name', $searchTerm],
					['like', 'date_format(FROM_UNIXTIME(t.`created_at` ), \'%b %d, %Y\' )', $searchTerm],
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
				$model = $model->orderBy(['t.created_at' => SORT_DESC]);
			}
            $model = $model->limit($pageSize)->offset($page);
		}
		else
		{
			$totalCount = $model->count();
		}
		
		$address_list=array();
		$model = $model->all();		
		if(count($model)>0)
		{
			foreach($model as $question)
			{
				$data=array();
				$data['id']=$question->id;
				$data['app_id']=$question->app_id;
				$data['status']=$question->status;
				$data['status_name']=$question->arrStatus[$question->status];
				$data['company_name']=$question->applicationaddress?$question->applicationaddress->company_name:'';
				$data['showdelete']=($question->status==$question->arrEnumStatus['draft'])?1:0;
				$data['showedit']=($question->status==$question->arrEnumStatus['draft'])?1:0;
				$data['showappview']=($question->status==$question->arrEnumStatus['submitted'])?1:0;
				
				$data['new_app_id']=$question->new_app_id;
				$data['created_by_label']=$question->createdbydata->first_name.' '.$question->createdbydata->last_name;
				$data['created_at']=date($date_format,$question->created_at);			
				
				$address_list[] = $data;
			}			
		}
		return ['changeaddresses'=>$address_list,'total'=>$totalCount];
	}

	

	public function actionGetAppunitdata()
	{
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data)
		{
			$unitmodel = ApplicationUnit::find()->where(['id'=>$data['id']])->one();
			if($unitmodel!==null)
			{
				$data = [];
				$data['unit_name'] = $unitmodel->application->currentaddress->unit_name;
				$data['unit_type'] = $unitmodel->unit_type;
				$data['unit_address'] = $unitmodel->application->currentaddress->unit_address;
				$data['unit_zipcode'] = $unitmodel->application->currentaddress->unit_zipcode;
				$data['unit_city'] = $unitmodel->application->currentaddress->unit_city;
				$data['unit_country_id'] = $unitmodel->application->currentaddress->unit_country_id;
				$data['unit_state_id'] = $unitmodel->application->currentaddress->unit_state_id;

				$data['salutation'] = $unitmodel->application->currentaddress->salutation;
				$data['first_name'] = $unitmodel->application->currentaddress->first_name;
				$data['last_name'] = $unitmodel->application->currentaddress->last_name;
				$data['job_title'] = $unitmodel->application->currentaddress->job_title;
				$data['telephone'] = $unitmodel->application->currentaddress->telephone;
				$data['email_address'] = $unitmodel->application->currentaddress->email_address;
			}
			return ['data'=>$data];
		}
	}

	public function actionGetAppunitlist()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$userData = Yii::$app->userdata->getData();
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$data['unit_type'] = 1;
			$unitarr = Yii::$app->globalfuns->getAppunitdata($data);
			
			$responsedata=array('status'=>1,'unitdata'=>$unitarr);
		}
		return $this->asJson($responsedata);
	}

	public function actionView()
	{
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$userid = $userData['userid'];
		$model = new ChangeAddress();
		$unitmodel = new ChangeAddressUnit();
		if($data)
		{
			$addressmodel = ChangeAddress::find()->where(['id'=>$data['id']])->one();
			if($addressmodel!==null)
			{
				if(!Yii::$app->userrole->canViewApplication($addressmodel->app_id))
				{
					return false;
				}
				
				$data=array();
				$data['id']=$addressmodel->id;
				$data['app_id']=$addressmodel->app_id;
				$data['app_name']=$addressmodel->applicationaddress->company_name;
				$data['status']=$addressmodel->status;
				$data['status_name']=$model->arrStatus[$addressmodel->status];
				$data['created_by_label']=$addressmodel->createdbydata->first_name.' '.$addressmodel->createdbydata->last_name;
				$data['created_at']=date($date_format,$addressmodel->created_at);

				$addressunit = $addressmodel->changeaddressunit;
				if($addressunit!==null)
				{	
					$data['unit_id'] = $addressunit->unit_id;
					$data['unit_name'] = $addressunit->applicationunit->name;
					$data['unit_type'] = $addressunit->unit_type;
					$data['name'] = $addressunit->name;
					$data['code'] = $addressunit->code;
					$data['address'] = $addressunit->address;
					$data['zipcode'] = $addressunit->zipcode;
					$data['city'] = $addressunit->city;
					$data['state_id'] = $addressunit->state_id;
					$data['country_id'] = $addressunit->country_id;
					$data['state_name'] = $addressunit->state->name;
					$data['country_name'] = $addressunit->country->name;

					$data['salutation'] = $addressunit->salutation;
					$data['salutation_name'] = $unitmodel->arrSalutation[$addressunit->salutation];
					$data['first_name'] = $addressunit->first_name;
					$data['last_name'] = $addressunit->last_name;
					$data['job_title'] = $addressunit->job_title;
					$data['telephone'] = $addressunit->telephone;
					$data['email_address'] = $addressunit->email_address;
					
				}					
			}			
			return ['data'=>$data];
		}
	}
	
	public function actionCreate()
	{
		if(!Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->isCustomer())
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data)
		{
			if(!Yii::$app->userrole->isValidApplication($data['app_id']))
			{
				return false;
			}
			
			if(isset($data['id']))
			{
				
				$model = ChangeAddress::find()->where(['id' => $data['id']])->one();
				
				if($model===null){
					$model = new ChangeAddress();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
					ChangeAddressUnit::deleteAll(['change_address_id' => $data['id']]);
				}
			}
			else
			{
				$model = new ChangeAddress();
				$model->created_by = $userData['userid'];
			}

			$model->app_id = $data['app_id'];
			$model->address_id = Yii::$app->globalfuns->getAppCurrentAddressId($data['app_id']);
			
			$userData = Yii::$app->userdata->getData();
			$model->created_by = $userData['userid'];

			if($model->validate() && $model->save())
			{
				$unitmodel = new ChangeAddressUnit();
				$unitmodel->change_address_id = $model->id;
				$unitmodel->unit_id = $data['unit_id'];
				$unitmodel->unit_type = $data['unit_type'];
				$unitmodel->name = $data['unit_name'];
				$unitmodel->address = $data['unit_address'];
				$unitmodel->zipcode = $data['unit_zipcode'];
				$unitmodel->city = $data['unit_city'];
				$unitmodel->state_id = $data['unit_state_id'];
				$unitmodel->country_id = $data['unit_country_id'];

				$unitmodel->salutation = $data['salutation'];
				$unitmodel->first_name = $data['first_name'];
				$unitmodel->last_name = $data['last_name'];
				$unitmodel->job_title = $data['job_title'];
				$unitmodel->telephone = $data['telephone'];
				$unitmodel->email_address = $data['email_address'];

				$new_app_id = '';
				if($unitmodel->save())
				{
					if(isset($data['type']) && $data['type']==2){
						
						$Application = new Application();
						$cdata=[];
						$cdata['id'] = $data['app_id'];
						$cdata['audit_type'] = $Application->arrEnumAuditType['change_of_address'];
						$clonedata = $Application->cloneApplication($cdata);

						$model->new_app_id = $clonedata['new_app_id'];
						$model->status = $model->arrEnumStatus['submitted'];
						$model->save();
						$new_app_id = $clonedata['new_app_id'];
						
						$modelApplicationChangeAddress = new ApplicationChangeAddress();
						$modelApplicationChangeAddress->current_app_id = $model->new_app_id;
						$modelApplicationChangeAddress->salutation = $unitmodel->salutation;
						$modelApplicationChangeAddress->salutation = $unitmodel->salutation;
						$modelApplicationChangeAddress->first_name = $unitmodel->first_name;
						$modelApplicationChangeAddress->last_name = $unitmodel->last_name;
						$modelApplicationChangeAddress->job_title = $unitmodel->job_title;
						$modelApplicationChangeAddress->telephone = $unitmodel->telephone;
						$modelApplicationChangeAddress->email_address = $unitmodel->email_address;

						$modelApplicationChangeAddress->company_name = $unitmodel->name;
						$modelApplicationChangeAddress->address = $unitmodel->address;
						$modelApplicationChangeAddress->zipcode = $unitmodel->zipcode;
						$modelApplicationChangeAddress->city = $unitmodel->city;
						$modelApplicationChangeAddress->state_id = $unitmodel->state_id;
						$modelApplicationChangeAddress->country_id = $unitmodel->country_id;
						
						$modelApplicationChangeAddress->unit_name = $unitmodel->name;
						$modelApplicationChangeAddress->unit_address = $unitmodel->address;
						$modelApplicationChangeAddress->unit_zipcode = $unitmodel->zipcode;
						$modelApplicationChangeAddress->unit_city = $unitmodel->city;
						$modelApplicationChangeAddress->unit_state_id = $unitmodel->state_id;
						$modelApplicationChangeAddress->unit_country_id = $unitmodel->country_id;
						if($modelApplicationChangeAddress->save())
						{
							$updateAddressToApplication = Application::find()->where(['id'=>$clonedata['new_app_id'] ])->one();
							if($updateAddressToApplication!==null)
							{
								$updateAddressToApplication->address_id=$modelApplicationChangeAddress->id;
								$updateAddressToApplication->save();
							}
						}					
					}
				}
				if(isset($data['id']) && $data['id']!='')
				{
					$responsedata=array('status'=>1,'message'=>'Updated Successfully','new_app_id'=>$new_app_id);
				}
				else
				{
					$responsedata=array('status'=>1,'message'=>'Saved Successfully','new_app_id'=>$new_app_id);
				}
			}
		}
		
		return $this->asJson($responsedata);
	}


	public function actionCommonUpdate()
	{
		if(!Yii::$app->userrole->isAdmin() && !Yii::$app->userrole->isCustomer())
		{
			return false;
		}
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid = $userData['userid'];
		if($data){
			if(isset($data['id']) && $data['id']>0){
				$ChangeAddress = ChangeAddress::find()->where(['id'=>$data['id']])->one();

				if($ChangeAddress !== null){
					if(!Yii::$app->userrole->isValidApplication($ChangeAddress->app_id))
					{
						return false;
					}
					
					$unitdata = ChangeAddressUnit::deleteAll(['change_address_id' => $ChangeAddress->id]);
					
					$ChangeAddress->delete();
					$responsedata=array('status'=>1,'message'=>'Address deleted successfully');
				}				
			}
		}		
		return $this->asJson($responsedata);
	}
	
}
