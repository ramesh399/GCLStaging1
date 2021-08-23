<?php

namespace app\modules\application\models;

use Yii;
use app\modules\master\models\Standard;
use app\modules\certificate\models\Certificate;
/**
 * This is the model class for table "tbl_application_standard".
 *
 * @property int $id
 * @property int $app_id
 * @property int $standard_id
 */
class ApplicationStandard extends \yii\db\ActiveRecord
{

    public $arrStatus=array('0'=>'Valid','1'=>'In-Valid','2'=>'Drat Certificate','3'=>'Declined','4'=>'Suspension','5'=>'Cancellation','6'=>'Withdrawn','7'=>'Certified by Other CB yet to be expired','8'=>'Expired');
	public $arrEnumStatus=array('valid'=>'0','invalid'=>'1','draft_certificate'=>'2','declined'=>'3','suspension'=>'4','cancellation'=>'5','withdrawn'=>'6','certified_by_other_cb'=>'7','expired'=>'8');    
   	
	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_standard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_id', 'standard_id'], 'integer'],
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
            'standard_id' => 'Standard ID',
        ];
    }
	
	public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }

    public function getApplication()
    {
        return $this->hasOne(Application::className(), ['id' => 'app_id']);
    }
    public function getCertificateforreport()
    {
        return $this->hasOne(Certificate::className(), ['parent_app_id' => 'app_id','standard_id'=>'standard_id'])->andOnCondition(['type'=> [1,2,4]])->andOnCondition(['>=','status', 2])->orderBy(['id' => SORT_ASC]);
    }
    
    public function getLatestcertificateforreport()
    {
        //->andOnCondition(['type'=> [1,2,4]])
        return $this->hasOne(Certificate::className(), ['parent_app_id' => 'app_id','standard_id'=>'standard_id'])->andOnCondition(['>=','status', 2])->orderBy(['id' => SORT_DESC]);
    }
}
