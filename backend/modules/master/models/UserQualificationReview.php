<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_user_qualification_review".
 *
 * @property int $id
 * @property int $user_id
 * @property int $standard_id
 * @property int $qualification_status
 * @property string $qualified_date
 * @property int $qualified_by
 * @property int $created_by
 * @property int $created_at
 */
class UserQualificationReview extends \yii\db\ActiveRecord
{
    public $answers = ['1'=>'Approve','2'=>'Reject'];
	public $arrQualificationStatus=array('0'=>'Open','1'=>'Qualified','2'=>'Not-Qualified');
    public $recurring_period = ['1'=>'One Month','2'=>'Two Months','3'=>'Three Months','4'=>'Half Yearly','5'=>'Annually','6'=>'One Time'];
    public $arrAddPeriods = ['1'=>'1 month','2'=>'2 months','3'=>'3 months','4'=>'6 months','5'=>'1 year'];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_qualification_review';
    }

    /**
     * {@inheritdoc}
     */

    // public function behaviors()
    // {
    //     return [
    //         TimestampBehavior::className(),
    //         'timestamp' => [
    //             'class' => 'yii\behaviors\TimestampBehavior',
    //             'attributes' => [
    //                 ActiveRecord::EVENT_BEFORE_INSERT => ['created_at','updated_at'],
    //                 ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
    //             ],
    //         ],
    //     ];
    // }


    public function rules()
    {
        return [
            [['user_id', 'standard_id', 'qualification_status', 'qualified_by', 'created_by', 'created_at'], 'integer'],
            [['qualified_date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'standard_id' => 'Standard ID',
            'qualification_status' => 'Qualification Status',
            'qualified_date' => 'Qualified Date',
            'qualified_by' => 'Qualified By',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
        ];
    }
	
	public function getQualifiedby()
    {
        return $this->hasOne(User::className(), ['id' => 'qualified_by']);
    }
	
	public function getCreatedby()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
	
	public function getQualificationreviewcomment()
    {
        return $this->hasMany(UserQualificationReviewComment::className(), ['user_qualification_review_id' => 'id']);
    }
	
	public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
	
}
