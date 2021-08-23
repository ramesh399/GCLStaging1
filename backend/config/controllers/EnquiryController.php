<?php
namespace app\controllers;

use Yii;

use app\models\Enquiry;
use app\models\EnquiryStandard;
use app\modules\master\models\User;
use app\modules\master\models\MailNotifications;
use app\modules\master\models\UserCompanyInfo;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class EnquiryController extends \yii\rest\Controller
{

    /**
     * @inheritdoc
     */
	 /* 
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => \sizeg\jwt\JwtHttpBearerAuth::class,
        ];

        return $behaviors;
    }
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
				'index',
			]
			],
		];	
	}
	
	public function actionIndex()
	{
		
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$post = yii::$app->request->post();


		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$resource_access=$userData['resource_access'];
		$rules=$userData['rules'];
		$is_headquarters =$userData['is_headquarters'];
		$franchiseid=$userData['franchiseid'];
		if($user_type!=3 && !Yii::$app->userrole->hasRights(array('enquiry_management')))
		{
			return false;
		}
		
		$modelEnquiry = new Enquiry();
		
		$model = Enquiry::find()->alias('t');
				
		if(isset($post['standardFilter']) && is_array($post['standardFilter']) && count($post['standardFilter'])>0)
		{
			$model->joinWith(['enquirystandard as estandard']);
			$model = $model->andWhere(['estandard.standard_id'=> $post['standardFilter']]);			
		}
		
		if(isset($post['countryFilter']) && is_array($post['countryFilter']) && count($post['countryFilter'])>0)
		{
			$model = $model->andWhere(['t.company_country_id'=> $post['countryFilter']]);			
		}
		
		if(isset($post['franchiseFilter']) && is_array($post['franchiseFilter']) && count($post['franchiseFilter'])>0)
		{
			$model = $model->andWhere(['t.franchise_id'=> $post['franchiseFilter']]);			
		}

		if(isset($post['from_date']))
		{
			$model = $model->andWhere(['>=','t.created_at', strtotime($post['from_date'])]);			
		}

		
		if(isset($post['to_date']))
		{
			$model = $model->andWhere(['<=','t.created_at', strtotime($post['to_date'].' 23:59:59')]);			
		}

		

		//print_r($userData); die;
		if($resource_access != 1){
			if($user_type==3 && $is_headquarters!=1){
				if($resource_access == '5'){
					$userid = $franchiseid;
				}
				$model = $model->andWhere(['t.franchise_id'=> $userid]);
			}else if($user_type== 1 && ! in_array('view_enquiry',$rules) ){
				return $responsedata;
			}else if($user_type== 2){
				return $responsedata;
			}
		}
		//echo  $is_headquarters; die;
		if($user_type==1 && $is_headquarters!=1){
			$model = $model->andWhere(['t.franchise_id'=> $franchiseid]);
		}
		if($user_type==3 && $is_headquarters!=1){
			if($resource_access == '5'){
				$userid = $franchiseid;
			}
			$model = $model->andWhere(['t.franchise_id'=> $userid]);
		}
		
		if(isset($post['type']) && $post['type'] !='')
		{
			if($post['type'] != 1)
			{
				$model->andWhere(['t.status'=> 3]);	
			}
			else
			{
				$model->andWhere(['t.status'=> 2]);
			}
		}else{
			$model->andWhere(['!=','t.status',3]);
			$model->andWhere(['!=','t.status',2]);
		}
		
		$model = $model->groupBy(['t.id']);
		if(is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize']))
		{
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize']; 
			$statusarray=array_map('strtolower', $modelEnquiry->arrStatus);
									
			if(isset($post['searchTerm']) && $post['searchTerm'] !='')
			{
				$model->joinWith(['companycountry as ccountry']);
				
				$searchTerm = $post['searchTerm'];
				$model = $model->andFilterWhere([
					'or',
					['like', 'company_name', $searchTerm],
					['like', 'contact_name', $searchTerm],
					['like', 'company_telephone', $searchTerm],
					['like', 'company_email', $searchTerm],
					['like', 'ccountry.name', $searchTerm],						
					['like', '(date_format(FROM_UNIXTIME(t.created_at), \'%b %d, %Y\' ))', $searchTerm],
					//['like', 'tbl_enquiry.created_at', strtotime($searchTerm)],					
				]);
				
				
				$search_status = array_search(strtolower($searchTerm),$statusarray);
				if($search_status!==false)
				{
					$model = $model->orFilterWhere([
                        'or', 					
						['t.status'=>$search_status]								
					]);
				}				
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
		
		$enquiry_list=array();
		//$model = $model->asArray()->all();
		$model = $model->all();	
		
		if(count($model)>0)
		{
			foreach($model as $enquiry)
			{
				$data=array();
				$data['id']=$enquiry->id;
				$data['company_name']=$enquiry->company_name;
				$data['contact_name']=$enquiry->contact_name;
				$data['company_telephone']=$enquiry->company_telephone;
				$data['company_email']=$enquiry->company_email;
				$data['company_country_id']=$enquiry->companycountry->name;
				$data['status']=$modelEnquiry->arrStatus[$enquiry->status];
				//$data['franchise']= ($enquiry->franchise_id)?$enquiry->franchise->usercompanyinfo->company_name.' ('.$enquiry->franchise->usercompanyinfo->companycountry->name.')':'NA';
				$data['franchise']= ($enquiry->franchise)?'OSS '.$enquiry->franchise->usercompanyinfo->osp_number.' - '.$enquiry->franchise->usercompanyinfo->osp_details:'NA';
				//$data['created_at']=date('M d,Y h:i A',$enquiry->created_at);
				$data['created_at']=date($date_format,$enquiry->created_at);
				$data['status_label_color']=$modelEnquiry->arrStatusColor[$enquiry->status];
				
				$arrEnquiryStd=array();
				$appStd = $enquiry->enquirystandard;
				if(count($appStd)>0)
				{	
					foreach($appStd as $app_standard)
					{
						$arrEnquiryStd[]=$app_standard->standard->code;
					}
				}
				$data['enquiry_standard']=implode(', ',$arrEnquiryStd);
				
				$enquiry_list[]=$data;
			}
		}
		
		return ['enquiries'=>$enquiry_list,'total'=>$totalCount];
	}

	public function actionView()
	{
		

		$post = yii::$app->request->post();
		
		//echo $_REQUEST['id'];
		//print_r($post);
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$responsedata =array('status'=>0,'message'=>"Something went wrong! Please try again later");
		if(is_array($post) && count($post)>0 && isset($post['id']))
		{
			$userData = Yii::$app->userdata->getData();
			$userid=$userData['userid'];
			$user_type=$userData['user_type'];
			$role=$userData['role'];
			$resource_access=$userData['resource_access'];
			$rules=$userData['rules'];
			$is_headquarters =$userData['is_headquarters'];
			$franchiseid=$userData['franchiseid'];
			
			if($user_type!=3 && !Yii::$app->userrole->hasRights(array('view_enquiry')))
			{
				return false;
			}
		//print_r($userData); die;
		
			$modelEnquiry = new Enquiry();
			$enquirymodeldata = Enquiry::find()->where(['id' => $post['id']]);
			if($resource_access != 1){
				if($user_type==3 && $is_headquarters!=1){
					if($resource_access == '5'){
						$userid = $franchiseid;
					}
					$enquirymodeldata = $enquirymodeldata->andWhere(['franchise_id'=> $userid]);
				}else if($user_type== 1 && ! in_array('view_enquiry',$rules) ){
					return $responsedata;
				}else if($user_type== 2){
					return $responsedata;
				}
			}

			$enquirymodeldata = $enquirymodeldata->one();
			if ($enquirymodeldata !== null)
			{
				//$enquirymodeldata->country_id=$enquirymodeldata->country->name;
				//$enquirymodeldata->state_id=$enquirymodeldata->state->name;
				$enquirymodeldata->company_country_id=$enquirymodeldata->companycountry->name;
				$enquirymodeldata->company_state_id=isset($enquirymodeldata->companystate->name)?$enquirymodeldata->companystate->name:'';
				$enquirymodeldata->created_at=date("M d,Y h:i A",$enquirymodeldata->created_at);
				$enquirymodeldata->updated_at=date("M d,Y h:i A",$enquirymodeldata->updated_at);
				$enquirymodeldata->status_updated_date=isset($enquirymodeldata->status_updated_date)?date($date_format,$enquirymodeldata->status_updated_date):'NA';
				$resultarr=array();
				$resultarr['status_id']=$enquirymodeldata->status;
				foreach($enquirymodeldata as $key => $value)
				{
					if($key=='status')
					{
						$value = $modelEnquiry->arrStatus[$value];
					}elseif($key=='company_website'){	
						if($value!='')
						{
							if (!preg_match("~^(?:f|ht)tps?://~i", $value)) {           
								// If not exist then add http 
								$value = "http://" . $value; 
							} 
						}						
					}elseif($key=='customer_id'){
						$value = ($enquirymodeldata->customer?$enquirymodeldata->customer->first_name.' '.$enquirymodeldata->customer->last_name:'');
					}elseif($key=='franchise_id'){
						$value = ($enquirymodeldata->franchise?$enquirymodeldata->franchise->first_name.' '.$enquirymodeldata->franchise->last_name:'NA');
						
						$arrfranchise=[];
						$franchiseObj=$enquirymodeldata->franchise;
						if($franchiseObj)
						{
							/*
							
							$arrfranchise['name']=$name;
							$arrfranchise['email']=$franchiseObj->email;
							$arrfranchise['telephone']=$franchiseObj->telephone
							$arrfranchise['state']=$franchiseObj->state->name;
							$arrfranchise['country']=$franchiseObj->country->name;
							var_dump($arrfranchise);
							//$value=$arrfranchise;
							*/
							
							$arrfranchise['company_country']=$franchiseObj->usercompanyinfo->companycountry?$franchiseObj->usercompanyinfo->companycountry->name:'';
							$arrfranchise['company_city']=$franchiseObj->usercompanyinfo->companycountry?$franchiseObj->usercompanyinfo->company_city:'';
							$arrfranchise['company_name']=$franchiseObj->usercompanyinfo?$franchiseObj->usercompanyinfo->company_name:'';
							$arrfranchise['contact_name']=$franchiseObj->usercompanyinfo?$franchiseObj->usercompanyinfo->contact_name:'';
							$arrfranchise['company_telephone']=$franchiseObj->usercompanyinfo?$franchiseObj->usercompanyinfo->company_telephone:'';
							$arrfranchise['company_email']=$franchiseObj->usercompanyinfo?$franchiseObj->usercompanyinfo->company_email:'';
							
							//$name=$franchiseObj->usercompanyinfo->companycountry->name;
							
							//$name=$franchiseObj->first_name.' '.$franchiseObj->last_name;							
							//$arrfranchise=array('name'=>$name,'email'=>$franchiseObj->email,'telephone'=>$franchiseObj->telephone,'state'=>$franchiseObj->state->name,'country'=>$franchiseObj->country->name);
							$value=$arrfranchise;
						}
												
					}
				
					$resultarr[$key]=$value;
				}

				$Enqstdmodel = $enquirymodeldata->enquirystandard;
				$eStandardArr= array();
				if(count($Enqstdmodel)>0)
				{
					foreach($Enqstdmodel as $enquirystandard)
					{
						$data=array();
						$data['id']=$enquirystandard->standard->id;
						$data['name']=$enquirystandard->standard->name;
						$eStandardArr[]=$data;
					}
				}
				
				$resultarr["standards"]=$eStandardArr;
				return $resultarr;
			}
			else
			{
				return $this->asJson($responsedata);
			}
		}
	}
	 /*
	public function actionAuthenticate()
	{
		$time = time();
		$token = Yii::$app->jwt->getBuilder()
					->issuedBy('') // Configures the issuer (iss claim)
					->permittedFor('') // Configures the audience (aud claim)
					->identifiedBy('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
					->issuedAt($time) // Configures the time that the token was issue (iat claim)
					->canOnlyBeUsedAfter($time + 60) // Configures the time that the token can be used (nbf claim)
					->expiresAt($time + 3600) // Configures the expiration time of the token (exp claim)
					->withClaim('uid', 1) // Configures a new claim, called "uid"
					->getToken(); // Retrieves the generated token
		//echo '<pre>';
		//print_r($token);
		//die();
		
		$token->getHeaders(); // Retrieves the token headers
		$token->getClaims(); // Retrieves the token claims

		$token->getHeader('jti'); // will print "4f1g23a12aa"
		$token->getClaim('iss'); // will print "http://example.com"
		$token->getClaim('uid'); // will print "1"
		//echo $token; // The string representation of the object is a JWT string (pretty easy, right?)
		//die();
		return ['token'=>(string)$token];
	}
	*/
}