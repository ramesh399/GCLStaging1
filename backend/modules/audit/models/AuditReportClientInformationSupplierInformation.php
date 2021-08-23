<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_audit_report_client_information_supplier_information".
 *
 * @property int $id
 * @property int $audit_id
 * @property int $unit_id
 * @property string $supplier_name
 * @property string $supplier_address
 * @property string $products_composition
 * @property string $validity
 * @property int $available_in_gots_database
 * @property int $sufficient
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class AuditReportClientInformationSupplierInformation extends \yii\db\ActiveRecord
{
    public $arrAvailable=array('1'=>'Yes','2'=>'No');
    public $arrEnumAvailable=array('yes'=>'1','no'=>'2');

    public $arrApplicable=array('1'=>'Applicable','2'=>'Not Applicable');
    public $arrEnumApplicable=array('applicable'=>'1','not_applicable'=>'2');
    
    public $arrSufficient=array('1'=>'Yes','2'=>'No','3'=>'N/A');
    public $arrEnumSufficient=array('yes'=>'1','no'=>'2','na'=>'3');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_client_information_supplier_information';
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
            [['audit_id', 'unit_id', 'available_in_gots_database', 'sufficient', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            
        ];
    }
    
    
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
