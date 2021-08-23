<?php

namespace app\modules\library\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_library_osp_document".
 *
 * @property int $id
 * @property int $franchise_id
 * @property int $document_type_id
 * @property string $note
 * @property string $document
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class LibraryOspDocument extends \yii\db\ActiveRecord
{
    public $arrDocType=array('1'=>'Accreditation Certificates or Shedules','2'=>'Agency Agreement/MOU','3'=>'Business Risk Assessment','4'=>'Certificate of Incorporation','5'=>'Company Declarartion of Interest Sheet','6'=>'List of Directors','7'=>'List of Shareholders','8'=>'Marketing Plan','9'=>'Memorandum & Articles of Associaiton','10'=>'Profit & Loss sheet','11'=>'Non-disclosure Agreement','12'=>'Insurance','13'=>'Other');
    public $arrEnumDocType=array('accreditation_certificates_or_shedules'=>'1','agency_agreement_mou'=>'2','business_risk_assessment'=>'3','certificate_of_incorporation'=>'4','company_declarartion_of_interest_sheet'=>'5','list_of_directors'=>'6','list_of_shareholders'=>'7','marketing_plan'=>'8','memorandum_and_articles_of_associaiton'=>'9','profit_and_loss_sheet'=>'10','non_disclosure_agreement'=>'11','insurance'=>'12','other'=>'13');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_osp_document';
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
            [['note', 'document'], 'string'],
            [['franchise_id', 'document_type_id','status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'franchise_id' => 'Franchise ID',
            'document_type_id' => 'Document Type ID',
            'note' => 'Note',
            'document' => 'Document',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
    
    public function getFranchise()
    {
        return $this->hasOne(User::className(), ['id' => 'franchise_id']);
    }
	
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
