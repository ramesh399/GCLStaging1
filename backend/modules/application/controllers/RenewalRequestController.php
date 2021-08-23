<?php
namespace app\modules\application\controllers;

use Yii;

use yii\web\NotFoundHttpException;

use app\modules\master\models\User;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationRenewal;
use app\modules\application\models\ApplicationRenewalStandard;


use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * RenewalRequestController implements the CRUD actions for Process model.
 */
class RenewalRequestController extends \yii\rest\Controller
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
					'deleteaudit'					
				]
			]
		];        
    }
	
	public function actionIndex()
    {
		$requestmodel = new ApplicationRenewal();
		$post = yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];

		$date_format = Yii::$app->globalfuns->getSettings('date_format');

		$model = ApplicationRenewal::find()->alias('t');
		//$model = $model->innerJoinWith(['application as app'=>array('with'=>'applicationaddress as appaddress')]);
		$model = $model->innerJoinWith(['application as app']);
		$model = $model->join('inner join', 'tbl_application_change_address as appaddress','appaddress.id=app.address_id');
		$model = $model->join('left join', 'tbl_application_renewal_standard  as renewal_standard','renewal_standard.app_renewal_id =t.id ');

		if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
		{
			$model = $model->andWhere(['renewal_standard.standard_id'=> $post['standardFilter']]);		
		}

		if($resource_access != 1){
			if($user_type== Yii::$app->params['user_type']['user'] && ! in_array('application_management',$rules) ){
				return $responsedata;
			}else if($user_type== Yii::$app->params['user_type']['franchise']  && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$model = $model->andWhere('app.franchise_id="'.$userid.'" or app.created_by="'.$userid.'"');
			}else if($user_type== Yii::$app->params['user_type']['customer']){
				$model = $model->andWhere('app.created_by='.$userid);
			}
			
		}


		$model = $model->groupBy(['t.id']);

		
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			
			if(isset($post['searchTerm']))
			{
				$searchTerm = $post['searchTerm'];

				$model = $model->andFilterWhere([
					'or',	
					['like', 'appaddress.company_name', $searchTerm],	
					['like', 'date_format(FROM_UNIXTIME(t.created_at), \'%b %d, %Y\' )', $searchTerm],				
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
		
		$app_list=array();
		$model = $model->all();	
		if(count($model)>0)
		{
			foreach($model as $request)
			{
				$data=array();
				
				$data['id']=$request->id;
				$data['change_status']=$request->change_status;
				$data['change_status_name']=$request->arrStatus[$request->change_status];
				$data['app_id']=$request->app_id;
				$data['company_name']=$request->application->companyname;
				$data['first_name']=$request->application->firstname;
				$data['telephone']=$request->application->telephone;
				$data['created_at']=date($date_format,$request->created_at);
				
				$arrAppStd=array();
				$appStd = $request->renewalstandard;
				if(count($appStd)>0)
				{	
					foreach($appStd as $app_standard)
					{
						$arrAppStd[]=$app_standard->standard->code;
					}
					$data['application_standard']=implode(', ',$arrAppStd);
				}		
				else
				{
					$data['application_standard']= "NA";
				}			
				
				
				$app_list[]=$data;
			}
		}
		return ['applications'=>$app_list,'total'=>$totalCount,'arrEnumStatus'=>$requestmodel->arrEnumStatus];
    }

	public function actionView()
    {
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		$requestmodel = new ApplicationRenewal();
		$data = yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$date_format = Yii::$app->globalfuns->getSettings('date_format');

		if($data)
		{
			$model = ApplicationRenewal::find()->where(['id'=>$data['id']])->one();
			if($model !== null)
			{
				$resultarr = [];
				$resultarr['app_id'] = $model->app_id;
				$resultarr['company_name'] = $model->app_id?$model->application->companyname:'';
				$resultarr['user_id'] = $model->user_id;
				$resultarr['user_name'] = $model->user->first_name.' '.$model->user->last_name;
				$resultarr['change_status_name'] = $model->arrStatus[$model->change_status];
				$resultarr['created_by'] = $model->username->first_name.' '.$model->username->last_name;
				$resultarr['created_at'] = date($date_format, $model->created_at);

				$appStd = $model->renewalstandard;
				if(count($appStd)>0)
				{	
					foreach($appStd as $app_standard)
					{
						$arrAppStd[]=$app_standard->standard->code;
					}
					$resultarr['application_standard']=implode(', ',$arrAppStd);
				}
				
				return ['data'=>$resultarr];
			}
			
		}
	}
	
}