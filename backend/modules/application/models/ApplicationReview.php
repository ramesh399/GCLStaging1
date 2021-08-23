<?php

namespace app\modules\application\models;

use app\modules\master\models\User;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_application_review".
 *
 * @property int $id
 * @property int $app_id
 * @property string $answer
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class ApplicationReview extends \yii\db\ActiveRecord
{
	// public $arrReviewAnswer=array('1'=>'Accepted','2'=>'Rejected','3'=>'More Information from customer');
    // public $arrReviewerStatus=array('1'=>'Accept','2'=>'Reject','3'=>'More Information from customer');
    public $arrReviewAnswer=array('1'=>'Approved','3'=>'More Information','2'=>'Rejected');
	public $arrReviewerStatus=array('1'=>'Approve','3'=>'More Information','2'=>'Reject');
	public $arrReviewStatus=array('0'=>'Open','1'=>'Review in Process','2'=>'Review Completed','3'=>'Rejected');
	public $arrEnumReviewStatus=array('open'=>'0','review_in_process'=>'1','review_completed'=>'2','rejected'=>'3');
    
    public $arrReviewResult=[6=>'Critical',5=>'High',4=>'Medium',3=>'Low',2=>'Very Low',1=>'N/A'];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_review';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at','updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    
    public function rules()
    {
        return [
            [['app_id', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['answer'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App ID',
            'answer' => 'Answer',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
	
	public function getReviewer()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
	
	public function getApplicationreviewcomment()
    {
        return $this->hasMany(ApplicationReviewComment::className(), ['review_id' => 'id']);
    }

    public function getApplicationunitreviewcomment()
    {
        return $this->hasMany(ApplicationUnitReviewComment::className(), ['review_id' => 'id']);
    }

	public function getApplication()
    {
        return $this->hasMany(Application::className(), ['id' => 'app_id']);
    }

    public function reviewResultArray(){
        $reviewList= [];
        foreach($this->arrReviewResult as $key => $review){
			$reviewArr['id']= $key;
            $reviewArr['name']= $review;
            $reviewList[] = $reviewArr;
        }
        return $reviewList;
    }
}
