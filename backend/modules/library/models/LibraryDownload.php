<?php

namespace app\modules\library\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_library_download".
 *
 * @property int $id
 * @property string $title
 * @property string $version
 * @property date $document_date
 * @property string $description
 * @property int $reviewer
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class LibraryDownload extends \yii\db\ActiveRecord
{
	public $arrStatus=array('0'=>'Open','1'=>'Approved','2'=>'Archived');
	public $enumStatus=array('open'=>'0','approved'=>'1','archived'=>'2');
	public $arrType=array('handbooks','training_mat','artwork','client_logos','manual','procedures','competence_criteria','instructions','templates','application_forms','polices','standards','webinars');
    public $arrTypeData=array('handbooks'=>'Handbooks','training_mat'=>'Training Mat','artwork'=>'Artwork','client_logos'=>'Client Logo','manual'=>'Manual','procedures'=>'Procedure','competence_criteria'=>'Competence Criteria','instructions'=>'Instruction','templates'=>'Template','application_forms'=>'Application Form','polices'=>'Policy','standards'=>'Standard','webinars'=>'Webinars/Training');
    public $arrTypeAction=array('handbooks'=>'handbook','training_mat'=>'training_mat','artwork'=>'artwork','client_logos'=>'client_logo','manual'=>'manual','procedures'=>'procedure','competence_criteria'=>'competence_criteria','instructions'=>'instruction','templates'=>'template','application_forms'=>'application_form','polices'=>'policy','standards'=>'standards','webinars'=>'webinar');
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_download';
    }

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
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'version', 'description'], 'string'],
            [['status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'version' => 'Version',
			'document_date' => 'Document Date',
			'description' => 'Description',
			'reviewer' => 'Reviewer',			
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }   
	
    public function getLibrarymanualfiles()
    {
        return $this->hasMany(LibraryDownloadFile::className(), ['manual_id' => 'id']);
    }	
		
	public function getLibrarymanualaccess()
    {
        return $this->hasMany(LibraryDownloadAccess::className(), ['manual_id' => 'id']);
    }
    public function getReviewerdata()
    {
        return $this->hasOne(User::className(), ['id' => 'reviewer']);
    }
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
