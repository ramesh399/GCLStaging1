<?php
namespace app\modules\informationcenter\controllers;

use Yii;
use app\modules\informationcenter\models\Issues;
use yii\web\NotFoundHttpException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class IssuesController extends \yii\rest\Controller
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
                    'index',
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        $post = yii::$app->request->post();
        $date_format = Yii::$app->globalfuns->getSettings('date_format');
        $stdmodel = new Issues();
        $model = Issues::find()->where(['<>','status',2]);

        if (is_array($post) && count($post)>0 && isset($post['page']) && isset($post['pageSize'])) {
            $page = ($post['page'] - 1)*$post['pageSize'];
            $pageSize = $post['pageSize'];

            //print_r($statusarray);
            if (isset($post['searchTerm'])) {
                $searchTerm = $post['searchTerm'];
                
                $model = $model->andFilterWhere([
                    'or',
                    ['like', 'description', $searchTerm],
                    ['like', 'ticket', $searchTerm],
                    ['like', 'priority', $searchTerm],
                    ['like', 'issue_type', $searchTerm],
                ]);
            
                $totalCount = $model->count();
            }
                        
            $model = $model->limit($pageSize)->offset($page);
        } else {
            $totalCount = $model->count();
        }
        
       $issues_list=array();
        //$model = $model->asArray()->all();
        $model = $model->all();
        if (count($model)>0) {
            foreach ($model as $issue) {
                $data=array();
                $data['id']=$issue->id;
                $data['issue_type']=$issue->issue_type;
                $data['description']=$issue->description;
                $data['status']=$issue->status;
                $data['ticket']=$issue->ticket;
                $data['created_date']=$issue->created_date;
                $data['created_name']=$issue->created_name;
                $data['created_from']=$issue->created_from;
                $data['contact']=$issue->contact;
                $data['priority']=$issue->priority;
                $data['file']=$issue->file;
                $data['questionone']=$issue->questionone;
                $data['questiontwo']=$issue->questiontwo;
                $data['downtimestart']=$issue->downtimestart;
                $data['downtimeend']=$issue->downtimeend;
                $issues_list[]=$data;
            }
        }
        return ['issues'=>$issues_list,'total'=>$totalCount];
    }

    public function actionCreate()
    {
        $modelRawMaterial = new Issues();
        $responsedata=array('status'=>0,'message'=>'Something went wrong! Please try again');
        $datapost = Yii::$app->request->post();
        $data = json_decode($datapost['formvalues'], true);
        if ($data) {
            if (is_array($data['issue']) && count($data['issue'])>0) {
                foreach ($data['issue'] as $value) {
                    $target_dir = Yii::$app->params['user_files'];
                            
                    if (isset($value['id']) && $value['id']>0){
                        $issue=Issues::find()->where(['id'=>$value['id']])->one();
                        if ($issue === null) {
                            $issue=new Issues();
                        }
                    } else {
                        $issue=new Issues();
                    }
                            
                    if (isset($_FILES['filesaray0']['name'])) {
                        $tmp_name = $_FILES["filesaray0"]["tmp_name"];
                        $name = $_FILES["filesaray0"]["name"];
                        $issue_file=Yii::$app->globalfuns->postFiles($name, $tmp_name, $target_dir);
                    } else {
                        $issue_file = $value['file'];
                    }
                                                    
                    $issue->issue_type = $value['issue_type'];
                    $issue->description = $value['description'];
                    $issue->status = $value['status'];
                    $issue->ticket = $value['ticket'];
                    $issue->created_date = $value['created_date'];
                    $issue->created_name = $value['created_name'];
                    $issue->created_from = $value['created_from'];
                    $issue->contact = $value['contact'];
                    $issue->priority = $value['priority'];
                    $issue->file = $issue_file;
                    $issue->questionone = $value['questionone'];
                    $issue->questiontwo = $value['questiontwo'];
                    $issue->downtimestart = $value['downtimestart'];
                    $issue->downtimeend = $value['downtimeend'];
                    $issue->save();

                    $responsedata=array('status'=>1,'message'=>'Issues updated successfully','user_id'=>$issue->getErrors());
                }
            }
        }
        return $responsedata;
    }

    public function actionDelete()
    {
	$data = Yii::$app->request->post();
        $issuemodel = Issues::find()->where(['id' => $data])->one();
 $responsedata = [];        
if ($issuemodel!==null) {
            $issuemodel->delete();
            $responsedata = ['status'=>1,'message'=>'Deleted Successfully'];
        }
        return $responsedata;
    }  
}
