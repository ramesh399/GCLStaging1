<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\master\models\ClientInformationQuestions;
use app\modules\master\models\AuditReportCategory;
use app\modules\audit\models\AuditReportClientInformationChecklistReviewComment;
use app\modules\audit\models\AuditReportClientInformationChecklistReview;
use app\modules\audit\models\Audit;
use app\modules\audit\models\AuditReportClientInformationGeneralInfo;
use app\modules\audit\models\AuditReportClientInformationGeneralInfoDetails;
use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\master\models\Country;

use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditClientInformationController implements the CRUD actions for Product model.
 */
class AuditClientInformationController extends \yii\rest\Controller
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
	
	

	public function actionCreate()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		
		if($data)
		{	
			if(isset($data['audit_id']))
			{
				$model = AuditReportQbsScopeHolder::find()->where(['audit_id' => $data['audit_id']])->one();
				if($model===null)
				{
					$model = new AuditReportQbsScopeHolder();
					$model->created_by = $userData['userid'];
				}
				else
				{
					$model->updated_by = $userData['userid'];
				}
			}
			else
			{
				$model = new AuditReportQbsScopeHolder();
				$model->created_by = $userData['userid'];
			}

			$model->audit_id = isset($data['audit_id'])?$data['audit_id']:'';
			if(isset($data['app_id'])){
				$model->app_id = $data['app_id'];
			}
			
			$model->qbs_description = $data['qbs_description'];
			
			
			
			if($model->validate() && $model->save())
			{	
				if(isset($data['audit_id']) && $data['audit_id']!='')
				{
					$responsedata=array('status'=>1,'message'=>'QBS Scope Holder has been updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'QBS Scope Holder has been created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}


	public function actionView()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if($data)
		{	
			if(isset($data['audit_id']))
			{
				$model = AuditReportQbsScopeHolder::find()->where(['audit_id' => $data['audit_id']])->one();
				if($model!==null)
				{
					$responsedata=array('status'=>1,'data'=>$model->qbs_description);
				}
			}
		}
		return $this->asJson($responsedata);
	}

	public function actionGetGeneralinformation(){
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];

		$sufficientaccess = 1;
		if($user_type=='2' || $user_type=='3'){
			$sufficientaccess = 0;
		}
		$GenralInfoId = 0;
		if($data){

			if(!Yii::$app->userrole->canViewAuditReport($data)){
				return false;
			}

			if(!isset($data['audit_id']) || $data['audit_id']<=0){
				$sufficientaccess = 0;
			}
			/*
			$companydata= [
				['id'=>1,'name'=>'Name of the Scope Holder:'],
				['id'=>2,'name'=>'Main Owner of the company:'],
				['id'=>3,'name'=>'Responsible person for GOTS criteria implementation in site:'],
				['id'=>4,'name'=>'Full Address of the company'],
				['id'=>5,'name'=>'Zip Code'],
				['id'=>6,'name'=>'City'],
				['id'=>7,'name'=>'Country'],
				['id'=>8,'name'=>'Phone'],
				['id'=>9,'name'=>'E-mail'],
				['id'=>10,'name'=>'Website']
			];
			*/
			$unit_name = '';
			$owner_name = '';
			$unit_address = '';
			$unit_zipcode = '';
			$unit_city = '';
			$telephone = '';
			$email_address = '';
			$unit_country_id = '';
			$country_name = '';
			
			$GeneralInfo = AuditReportClientInformationGeneralInfo::find();
			//'audit_id'=>
			if(isset($data['audit_id']) && $data['audit_id']>0){
				//$GeneralInfo = $GeneralInfo->andWhere(['audit_id'=>$data['audit_id'] ]);
			}
			if(isset($data['app_id']) && $data['app_id']>0){
				$GeneralInfo = $GeneralInfo->andWhere(['app_id'=>$data['app_id'] ]);
			}else if(isset($data['unit_id']) && $data['unit_id']!=''){
				$ApplicationUnit = ApplicationUnit::find()->where(['id'=>$data['unit_id'] ])->one();
				if($ApplicationUnit !== null){
					//echo $ApplicationUnit->app_id;
					$GeneralInfo = $GeneralInfo->andWhere(['app_id'=>$ApplicationUnit->app_id ]);
				}
				
			}
			$GeneralInfo = $GeneralInfo->orderBy(['id' => SORT_DESC]);	
			
			$GeneralInfo = $GeneralInfo->one();
			if($GeneralInfo!==null){
				$GenralInfoId = $GeneralInfo->id;
				$InfoDetails = $GeneralInfo->expensesinfodetails;
				if(count($InfoDetails)>0){
					foreach($InfoDetails as $infodata){
						$readonly=1;
						$isrequired = 1;
						if($infodata->info_data_id==3){
							$readonly=0;
						}
						if($infodata->info_data_id==10){
							$isrequired=0;
						}

						$label_value = '';
						if($infodata->info_data_id==7){
							$Country = Country::find()->where(['id'=>$infodata->value])->one();
							if($Country !== null){
								$label_value = $Country->name;
							}
						}
						$companydata[] = ['id'=>$infodata->info_data_id,'name'=>$infodata->name,'value'=>$infodata->value
						,'sufficient'=>$infodata->sufficient,'readonly'=>$readonly,'isrequired'=>$isrequired, 'label_value'=>$label_value];
					}
				}
			}

			$company_website = '';
			if(isset($data['audit_id']) && $data['audit_id']>0){
				$Audit = Audit::find()->where(['id'=>$data['audit_id'] ])->one();
				if($Audit !== null){
					$applicationaddress = $Audit->application->applicationaddress;
				}
			}else if(isset($data['app_id']) && $data['app_id']>0){
				$Application = Application::find()->where(['id'=>$data['app_id'] ])->one();
				if($Application !== null){
					$applicationaddress = $Application->applicationaddress;
					$company_website = $Application->enquirydetails?$Application->enquirydetails->company_website:'';
					
				}
				
			}
			if($applicationaddress!== null){
				$unit_name = $applicationaddress->unit_name;
				$owner_name = $applicationaddress->first_name.' '.$applicationaddress->last_name;
				$unit_address = $applicationaddress->unit_address;
				$unit_zipcode = $applicationaddress->unit_zipcode;
				$unit_city = $applicationaddress->unit_city;
				$telephone = $applicationaddress->telephone;
				$email_address = $applicationaddress->email_address;
				$country_name = $applicationaddress->unitcountry?$applicationaddress->unitcountry->name:'';
				$unit_country_id = $applicationaddress->unit_country_id;
			}
					
				
			
			if($GeneralInfo===null){
				$companydata= [
					['id'=>1,'name'=>'Name of the Scope Holder','value'=> $unit_name,'sufficient'=>'','readonly'=>1,'isrequired'=>1],
					['id'=>2,'name'=>'Main Owner of the Company','value'=>$owner_name,'sufficient'=>'','readonly'=>1,'isrequired'=>1],
					['id'=>3,'name'=>'Responsible person for Standard(s) implementation in site','value'=>'','sufficient'=>'','readonly'=>0,'isrequired'=>1],
					['id'=>4,'name'=>'Full Address of the Company','value'=>$unit_address,'sufficient'=>'','readonly'=>1,'isrequired'=>1],
					['id'=>5,'name'=>'Zip Code','value'=>$unit_zipcode,'sufficient'=>'','readonly'=>1,'isrequired'=>1],
					['id'=>6,'name'=>'City','value'=>$unit_city,'sufficient'=>'','readonly'=>1,'isrequired'=>1],
					['id'=>7,'name'=>'Country','value'=>$unit_country_id,'sufficient'=>'','readonly'=>1,'isrequired'=>1],
					['id'=>8,'name'=>'Phone','value'=>$telephone,'sufficient'=>'','readonly'=>1,'isrequired'=>1],
					['id'=>9,'name'=>'E-mail','value'=>$email_address,'sufficient'=>'','readonly'=>1,'isrequired'=>1],
					['id'=>10,'name'=>'Website','value'=>$company_website,'sufficient'=>'','readonly'=>1,'isrequired'=>0]
				];
			}
			$sufficientOptions = ['1'=>'Yes','2'=>'No','3'=>'NA'];
			$responsedata = ['status'=>1,'data'=>$companydata,'sufficientOptions'=>$sufficientOptions];
		}else{
			$sufficientOptions = ['1'=>'Yes','2'=>'No','3'=>'NA'];
			$responsedata = ['status'=>1,'sufficientOptions'=>$sufficientOptions];
		}
		$responsedata['sufficientaccess'] = $sufficientaccess;
		$responsedata['GenralInfoId'] =$GenralInfoId;
		
		return $this->asJson($responsedata);
	}

	public function actionGetQuestions()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];

		
		$checklist_sufficient_access = 1;
		if($user_type=='2' || $user_type=='3'){
			$checklist_sufficient_access = 0;
		}
		if($data){

			if(!Yii::$app->userrole->canViewAuditReport($data)){
				return false;
			}
			if(!isset($data['audit_id']) || $data['audit_id']<=0){
				$checklist_sufficient_access = 0;
			}

			
			
			$model = ClientInformationQuestions::find()->where(['status' => 0]);

			if(isset($data['app_id']) && $data['app_id']>0){
				//$processIds = Yii::$app->globalfuns->getUnitProcess($data);
				$Application = Application::find()->where(['id'=>$data['app_id']])->one();
				if($Application !== null){
					$applicationstandard = $Application->applicationstandard;
					if(count($applicationstandard)>0){
						foreach($applicationstandard as $appstd){
							$standardIds[] = $appstd->standard_id;
						}
					}
				}
				//$standardIds = Yii::$app->globalfuns->getUnitStandard($data);

				//$model = $model->innerJoinWith(['questionprocess as process']);
				$model = $model->innerJoinWith(['questionstandard as standard']);
				$model = $model->andWhere(['standard.standard_id'=>$standardIds ]);
				//$model = $model->andWhere(['process.process_id'=>$processIds,'standard.standard_id'=>$standardIds ]);
			}

			/*
			if(isset($data['unit_id']) && $data['unit_id']>0){
				$processIds = Yii::$app->globalfuns->getUnitProcess($data);
				$standardIds = Yii::$app->globalfuns->getUnitStandard($data);

				$model = $model->innerJoinWith(['questionprocess as process']);
				$model = $model->innerJoinWith(['questionstandard as standard']);
				$model = $model->andWhere(['process.process_id'=>$processIds,'standard.standard_id'=>$standardIds ]);
			}
			*/
			
			$model = $model->all();

			if(count($model)>0)
			{
				$qdata = [];
				$qdetails = [];
				foreach($model as $question){
					$client_information_id = $question->client_information_id;
					$answers = [];
					foreach($question->riskcategory as $rc){
						$answers[$rc->question_finding_id] = $rc->category->name;
					}
					$qdata[$client_information_id][] = [
								'id' => $question->id,
								'name' => $question->name,
								'answer' => $answers
							];
					$clientIDs[] = $client_information_id;
				}
				
				$clientIDs = array_unique($clientIDs);
				//array_splice($clientIDs,2,10);
				foreach($clientIDs as $clientID){
					$AuditReportCategory = AuditReportCategory::find()->where(['id'=>$clientID])->one();
					if($AuditReportCategory!==null){
						$auditcat_name = $AuditReportCategory->name;
						$qdetails[] = [
							'categoryid' => $AuditReportCategory->id,
							'categoryname' => $AuditReportCategory->name,
							'questions' => $qdata[$clientID]
						];
					}
				}
				$responsedata=array('status'=>1,'data'=>$qdetails,'checklist_sufficient_access'=>$checklist_sufficient_access);
			}
		}
		return $this->asJson($responsedata);
	}




	public function actionGetViewquestions()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];

		
		$checklist_sufficient_access = 1;
		if($user_type=='2' || $user_type=='3'){
			$checklist_sufficient_access = 0;
		}
		if($data){
			if(!isset($data['audit_id']) || $data['audit_id']<=0){
				$checklist_sufficient_access = 0;
			}

			
			
			$model = AuditReportClientInformationChecklistReview::find()->where(['app_id' => $data['app_id']]);

			if(isset($data['app_id']) && $data['app_id']>0){
				//$processIds = Yii::$app->globalfuns->getUnitProcess($data);
				/*
				$Application = Application::find()->where(['id'=>$data['app_id']])->one();
				if($Application !== null){
					$applicationstandard = $Application->applicationstandard;
					if(count($applicationstandard)>0){
						foreach($applicationstandard as $appstd){
							$standardIds[] = $appstd->standard_id;
						}
					}
				}
				//$standardIds = Yii::$app->globalfuns->getUnitStandard($data);

				//$model = $model->innerJoinWith(['questionprocess as process']);
				$model = $model->innerJoinWith(['questionstandard as standard']);
				$model = $model->andWhere(['standard.standard_id'=>$standardIds ]);
				*/
				//$model = $model->andWhere(['process.process_id'=>$processIds,'standard.standard_id'=>$standardIds ]);
			}
 
			
			$model = $model->one();

			if($model !==null){
				$reviewcomment = $model->reviewcomment;
				if(count($reviewcomment)>0)
				{
					$qdata = [];
					$qdetails = [];
					foreach($reviewcomment as $question){
						$client_information_id = $question->category_id;
						$answers = [];
						//foreach($question->riskcategory as $rc){
						//$answers[$rc->question_finding_id] = $rc->category->name;
						//}
						$qdata[$client_information_id][] = [
									'id' => $question->id,
									'name' => $question->question,
									'answer' => $question->answercategory?$question->answercategory->name:''
								];
						$clientIDs[] = $client_information_id;
						$clientIDName[$client_information_id] = $question->category;
					}
					
					$clientIDs = array_unique($clientIDs);
					//array_splice($clientIDs,2,10);
					foreach($clientIDs as $clientID){
						//$AuditReportCategory = AuditReportCategory::find()->where(['id'=>$clientID])->one();
						//if($AuditReportCategory!==null){
							//$auditcat_name = $AuditReportCategory->name;
							$qdetails[] = [
								'categoryid' => $clientID,
								'categoryname' => $clientIDName[$clientID],
								'questions' => $qdata[$clientID]
							];
						//}
					}
					$responsedata=array('status'=>1,'data'=>$qdetails,'checklist_sufficient_access'=>$checklist_sufficient_access);
				}
			}
			
		}
		return $this->asJson($responsedata);
	}

	public function actionSaveChecklist()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again later');
		$userData = Yii::$app->userdata->getData();
		$data = Yii::$app->request->post();

        if ($data) 
		{
			$datacheck = [];
			$datacheck['app_id']= isset($data['app_id'])?$data['app_id']:'';
			$datacheck['audit_id']= isset($data['audit_id'])?$data['audit_id']:'';
			if(!Yii::$app->userrole->canEditAuditReport($datacheck)){
				return false;
			}
			$model = AuditReportClientInformationChecklistReview::find();
			
			
			if(isset($data['audit_id']) && $data['audit_id']>0){
				//$model = $model->andWhere(['audit_id'=>$data['audit_id'] ]);
			}
			if(isset($data['app_id']) && $data['app_id']>0){
				$model = $model->andWhere(['app_id'=>$data['app_id'] ]);
			}
			$model = $model->one();
			if($model===null)
			{
				$model = new AuditReportClientInformationChecklistReview();
				$model->created_by = $userData['userid'];
			}
			else
			{
				$model->updated_by = $userData['userid'];
				AuditReportClientInformationChecklistReviewComment::deleteAll(['client_information_checklist_review_id' => $model->id]);
			}
			if(isset($data['audit_id'])){
				$model->audit_id = $data['audit_id'];
			}
			if(isset($data['app_id'])){
				$model->app_id = $data['app_id'];
			}
			if($model->validate() && $model->save())
			{
				if(is_array($data['checklistdata']) && count($data['checklistdata'])>0)
				{
					foreach ($data['checklistdata'] as $value)
					{ 
						$reviewcmtmodel = new AuditReportClientInformationChecklistReviewComment();
						$reviewcmtmodel->client_information_checklist_review_id = $model->id;
						$reviewcmtmodel->client_information_question_id = $value['question_id'];
						$reviewcmtmodel->question = $value['question'];
						$reviewcmtmodel->answer = $value['answer'];
						$reviewcmtmodel->comment = $value['comment'];
						$reviewcmtmodel->category_id = $value['categoryid'];
						$reviewcmtmodel->category = $value['categoryname'];
						$reviewcmtmodel->save();
					}
				}
				$responsedata=array('status'=>1,'message'=>'Checklist has been saved successfully');
			}
		}
		return $this->asJson($responsedata);
	}


	public function actionSaveGeneralinfo()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again later');
		$userData = Yii::$app->userdata->getData();
		$data = Yii::$app->request->post();

        if ($data) 
		{
			if(!Yii::$app->userrole->canEditAuditReport($data)){
				return false;
			}
			$model = AuditReportClientInformationGeneralInfo::find();
			if(isset($data['audit_id']) && $data['audit_id']>0){
				//$model = $model->andWhere(['audit_id'=>$data['audit_id'] ]);
			}
			if(isset($data['app_id']) && $data['app_id']>0){
				$model = $model->andWhere(['app_id'=>$data['app_id'] ]);
			}
			$model = $model->one();

			if($model===null)
			{
				$model = new AuditReportClientInformationGeneralInfo();
				$model->created_by = $userData['userid'];
			}
			else
			{
				$model->updated_by = $userData['userid'];
				AuditReportClientInformationGeneralInfoDetails::deleteAll(['client_information_general_info_id' => $model->id]);
			}

			if(isset($data['audit_id']) && $data['audit_id']!=''){
				$model->audit_id = $data['audit_id'];
			}
			if(isset($data['app_id']) && $data['app_id']!=''){
				$model->app_id = $data['app_id'];
			}
			if($model->validate() && $model->save())
			{
				if(is_array($data['checklistdata']) && count($data['checklistdata'])>0)
				{
					foreach ($data['checklistdata'] as $value)
					{ 
						$reviewcmtmodel = new AuditReportClientInformationGeneralInfoDetails();
						$reviewcmtmodel->client_information_general_info_id = $model->id;
						$reviewcmtmodel->info_data_id = $value['question_id'];
						$reviewcmtmodel->name = $value['question'];
						$reviewcmtmodel->value = $value['answer'];
						$reviewcmtmodel->sufficient = $value['sufficient'];
						$reviewcmtmodel->save();
					}
				}
				$responsedata=array('status'=>1,'message'=>'General Information has been saved successfully');
			}
		}
		return $this->asJson($responsedata);
	}


	public function actionGetChecklistAnswer()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again later');
		$userData = Yii::$app->userdata->getData();
		$data = Yii::$app->request->post();

        if ($data) 
		{
			if(!Yii::$app->userrole->canViewAuditReport($data)){
				return false;
			}

			$answers = [];
			$model = AuditReportClientInformationChecklistReview::find();
			if(isset($data['audit_id']) && $data['audit_id']>0){
				//$model = $model->andWhere(['audit_id'=>$data['audit_id'] ]);
			}
			if(isset($data['app_id']) && $data['app_id']>0){
				$model = $model->andWhere(['app_id'=>$data['app_id'] ]);
			}

			$model = $model->orderBy(['id' => SORT_DESC]);	
			$model = $model->one();
			
			if($model !== null)
			{
				if(count($model->reviewcomment)>0)
				{
					foreach ($model->reviewcomment as $value)
					{ 
						$answers[] = ['question_id'=> $value->client_information_question_id,
										'answer' => $value->answer,
										'comment' => $value->comment
									];
					}
				}
				$responsedata=array('status'=>1,'data'=>$answers);
			}
		}
		return $this->asJson($responsedata);
	}



	public function actionGetChecklistviewdetails()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again later');
		$userData = Yii::$app->userdata->getData();
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		$userid=$userData['userid'];
		$user_type=$userData['user_type'];
		$role=$userData['role'];
		$rules=$userData['rules'];
		$resource_access=$userData['resource_access'];

        $checklist_sufficient_access = 1;
		if($user_type=='2' || $user_type=='3'){
			$checklist_sufficient_access = 0;
		}
		if($data){
			if(!isset($data['audit_id']) || $data['audit_id']<=0){
				$checklist_sufficient_access = 0;
			}
			$answers = [];
			$model = AuditReportClientInformationChecklistReview::find();
			if(isset($data['audit_id']) && $data['audit_id']>0){
				//$model = $model->andWhere(['audit_id'=>$data['audit_id'] ]);
			}
			if(isset($data['app_id']) && $data['app_id']>0){
				$model = $model->andWhere(['app_id'=>$data['app_id'] ]);
			}

			$model = $model->orderBy(['id' => SORT_DESC]);	
			$model = $model->one();
			
			if($model !== null)
			{
				$reviewcomment= $model->reviewcomment;
				if(count($reviewcomment)>0)
				{
					$qdata = [];
					$qdetails = [];
					foreach($reviewcomment as $question){
						$client_information_id = $question->category_id;
						
						$qdata[$client_information_id][] = [
									'id' => $question->id,
									'name' => $question->question,
									'answer' => $question->answercategory?$question->answercategory->name:'',
									'comment' => $question->comment
								];
						$clientIDs[] = $client_information_id;
						$clientIDsName[$client_information_id] = $question->category;
					}
					
					$clientIDs = array_unique($clientIDs);
					//array_splice($clientIDs,2,10);
					foreach($clientIDs as $clientID){
						$qdetails[] = [
							'categoryid' => $clientID,
							'categoryname' => $clientIDsName[$clientID],
							'questions' => $qdata[$clientID]
						];
						
					}
					$responsedata=array('status'=>1,'data'=>$qdetails,'checklist_sufficient_access'=>$checklist_sufficient_access);
				}

				/*
				if(count($model->reviewcomment)>0)
				{
					foreach ($model->reviewcomment as $value)
					{ 
						$answers[] = ['question_id'=> $value->client_information_question_id,
										'answer' => $value->answer,
										'comment' => $value->comment,
									];
					}
				}
				*/
				 
			}
		}
		return $this->asJson($responsedata);
	}
}
