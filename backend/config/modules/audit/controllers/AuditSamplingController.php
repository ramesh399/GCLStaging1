<?php
namespace app\modules\audit\controllers;

use Yii;
use app\modules\audit\models\AuditReportSampling;
use app\modules\audit\models\AuditReportSamplingList;
use app\modules\audit\models\AuditReportApplicableDetails;


use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

/**
 * AuditSamplingController implements the CRUD actions for Product model.
 */
class AuditSamplingController extends \yii\rest\Controller
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
	
	public function actionGetSamplingdata()
    {
		$post = yii::$app->request->post();
		
		$pdata = [];
		$pdata['audit_id'] = $post['audit_id'];
		$pdata['unit_id'] = $post['unit_id'];
		$pdata['checktype'] = 'unitwise';
		/*if(!Yii::$app->userrole->canViewAuditReport($pdata)){
			return false;
		}
		*/
		$date_format = Yii::$app->globalfuns->getSettings('date_format');
		$samplingmodel = new AuditReportSampling();
		$model = AuditReportSampling::find()->where(['audit_id'=>$post['audit_id'],'unit_id' => $post['unit_id']]);

		
		$sampling_list=array();
		$value = $model->one();		
		$data=array();
		if($value !== null)
		{
			//foreach($model as $value)
			//{
				
				$data['id']=$value->id;
				$data['audit_id']=$value->audit_id;
				$data['unit_id']=$value->unit_id;
				$data['operator_title']=$value->operator_title;
				$data['sampling_date']=date($date_format,strtotime($value->sampling_date));
				$data['operator_responsible_person']=$value->operator_responsible_person;
				$data['sample_no']=$value->sample_no;
				$data['staff_who_took_sample']=$value->staff_who_took_sample;
				$data['type_of_samples']=$value->type_of_samples;
				$data['samples_were_taken_from']=$value->samples_were_taken_from;
				//$data['storage_room']=$value->storage_room;
				//$data['processing_line']=$value->processing_line;
				//$data['other_such_as_market']=$value->other_such_as_market;
				$data['number_of_sub_samples_per_sample']=$value->number_of_sub_samples_per_sample;
				$data['describe_other_details_of_sampling_method']=$value->describe_other_details_of_sampling_method;
				$data['samples_were_taken_based_on_a_specific_suspicion']=$value->samples_were_taken_based_on_a_specific_suspicion;
				$data['specific_suspicion_label']=$samplingmodel->arrSuspicion[$value->samples_were_taken_based_on_a_specific_suspicion];
				$data['reason']=$value->reason;
				$data['further_comments']=$value->further_comments;
				$data['representative_sealed']=$value->representative_sealed;
				$data['representative_unsealed']=$value->representative_unsealed;
				$data['representative_sample_bag_number']=$value->representative_sample_bag_number;
				$data['operator_sealed']=$value->operator_sealed;
				$data['operator_unsealed']=$value->operator_unsealed;
				$data['operator_sample_bag_number']=$value->operator_sample_bag_number;
				$data['created_at']=date($date_format,$value->created_at);
				$data['created_by_label']=$value->createdbydata->first_name.' '.$value->createdbydata->last_name;

				$samplingarr = array();
				$sampling = $value->samplinglist;
				foreach($sampling as $val)
				{
					$list = [];
					$list['sample_number'] = $val->sample_number;
					$list['taken_from'] = $val->taken_from;
					$samplingarr[] = $list;
				}
				$data['sampling_list']=$samplingarr;

				//$sampling_list[]=$data;
			//}
		}

		return ['samplings'=>$data,'status'=>1];
	}


	public function actionCreate()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();
		
		if($data)
		{	

			$pdata = [];
			$pdata['audit_id'] = $data['audit_id'];
			$pdata['unit_id'] = $data['unit_id'];
			$pdata['checktype'] = 'unitwise';
			if(!Yii::$app->userrole->canEditAuditReport($pdata)){
				return false;
			}
			
			$arraydata = ['audit_id'=>$data['audit_id'],'unit_id'=>$data['unit_id'],'report_name'=>$data['type']];
			Yii::$app->globalfuns->UpdateApplicableDetails($arraydata);

			if(isset($data['audit_id']))
			{
				$model = AuditReportSampling::find()->where(['audit_id' => $data['audit_id'],'unit_id' => $data['unit_id']])->one();
				if($model===null){
					$model = new AuditReportSampling();
					$model->created_by = $userData['userid'];
				}else{
					$model->updated_by = $userData['userid'];
				}
			}else{
				$model = new AuditReportSampling();
				$model->created_by = $userData['userid'];
			}

			$model->audit_id = $data['audit_id'];
			$model->unit_id = $data['unit_id'];
			$model->operator_title = $data['operator_title'];
			$model->sampling_date = date('Y-m-d',strtotime($data['sampling_date']));
			$model->operator_responsible_person = $data['operator_responsible_person'];	
			$model->sample_no = $data['sample_no'];
			$model->staff_who_took_sample = $data['staff_who_took_sample'];
			$model->type_of_samples = $data['type_of_samples'];
			$model->samples_were_taken_from = $data['samples_were_taken_from'];
			//$model->storage_room = $data['storage_room'];
			//$model->processing_line = $data['processing_line'];
			//$model->other_such_as_market = $data['other_such_as_market'];
			$model->number_of_sub_samples_per_sample = $data['number_of_sub_samples_per_sample'];
			$model->describe_other_details_of_sampling_method = $data['describe_other_details_of_sampling_method'];
			$model->samples_were_taken_based_on_a_specific_suspicion = $data['samples_were_taken_based_on_a_specific_suspicion'];
			$model->reason = $data['reason'];
			$model->further_comments = $data['further_comments'];
			$model->representative_sealed = $data['representative_sealed'];
			$model->representative_unsealed = $data['representative_unsealed'];
			$model->representative_sample_bag_number = $data['representative_sample_bag_number'];
			$model->operator_sealed = $data['operator_sealed'];
			$model->operator_unsealed = $data['operator_unsealed'];
			$model->operator_sample_bag_number = $data['operator_sample_bag_number'];

			
			
			if($model->validate() && $model->save())
			{	

				AuditReportSamplingList::deleteAll(['audit_report_sampling_id' => $model->id]);

				if(isset($data['samplinglist']) && count($data['samplinglist'])>0){
					foreach($data['samplinglist'] as $samplingdata){
						$samplingmodel = new AuditReportSamplingList();
						$samplingmodel->audit_report_sampling_id = $model->id;
						$samplingmodel->sample_number = $samplingdata['sample_number'];
						$samplingmodel->taken_from = $samplingdata['taken_from'];
						$samplingmodel->save();
					}
				}
				

				//if(isset($data['id']) && $data['id']!=''){
				$responsedata=array('status'=>1,'message'=>'Sampling has been updated successfully');
				/*}else{
					$responsedata=array('status'=>1,'message'=>'Sampling has been created successfully');
				}*/
			}
			else
			{
				return $model->errors;
				exit;
			}
		}
		
		return $this->asJson($responsedata);
	}


	
	public function actionOptionlist()
	{
		$modelObj = new AuditReportSampling();
		return ['suspicionlist'=>$modelObj->arrSuspicion];
	}

	/*
	public function actionDeleteData()
    {
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		if ($data) 
		{	
			$modelchk = AuditReportSampling::find()->where(['id' => $data['id']])->one();
			if($modelchk!== null){
				$data['audit_id']= $modelchk->audit_id;
				if(!Yii::$app->userrole->canEditAuditReport($data)){
					return false;
				}
				$model = AuditReportSampling::deleteAll(['id' => $data['id']]);
				$responsedata=array('status'=>1,'message'=>'Deleted successfully');
			}
		}
		return $this->asJson($responsedata);
	}
	
	public function actionGetSampledata(){

    	$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();

		if($data){
			$date_format = Yii::$app->globalfuns->getSettings('date_format');
	    	$samplemodel = AuditReportSamplingList::find()->where(['audit_report_sampling_id'=>$data['sampling_id']])->all();
	    	$sample_list=[];
	    	if(count($samplemodel)>0)
	    	{
				foreach($samplemodel as $log)
				{
	    			$data=array();
					$data['id']=$log->id;
					$data['sample_number']=$log->sample_number;
					$data['taken_from']=$log->taken_from;
					
					$sample_list[]=$data;
	    		}
		    	
			}
			$responsedata=array('status'=>1,'data' => $sample_list);
		}
		return $responsedata;

	}
	
	public function actionAddSample()
	{
		
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();

		if($data)
		{
			if(isset($data['id']))
			{
				$model = AuditReportSamplingList::find()->where(['id' => $data['id']])->one();
				if($model===null){
					$model = new AuditReportSamplingList();
				}
			}else{
				$model = new AuditReportSamplingList();
			}

			 
			$model['audit_report_sampling_id'] = $data['sampling_id'];
			$model['sample_number'] = $data['sample_number'];
			$model['taken_from'] = $data['taken_from'];
			
			
			if($model->validate() && $model->save())
			{
				if(isset($data['id']) && $data['id']!=''){
					$responsedata=array('status'=>1,'message'=>'Sample updated successfully');
				}else{
					$responsedata=array('status'=>1,'message'=>'Sample created successfully');
				}
			}
		}
		
		return $this->asJson($responsedata);
	}
	
	public function actionDeleteSample()
	{
		$responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
		$data = Yii::$app->request->post();
		$userData = Yii::$app->userdata->getData();

		if($data){

			
			if(isset($data['id']))
			{
				$model = AuditReportSamplingList::find()->where(['id' => $data['id']])->one();
				if($model !==null){
					$model->delete();
					$responsedata = array('status'=>1,'message'=>'Deleted successfully');
				}
			}
		}
		return $responsedata;
	}
	*/
	
	
	
	
	
}
