<?php

namespace app\modules\certificate\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tbl_certificate_view_request_from_web".
 *
 * @property int $id
 * @property string $code
 * @property string $ip_address 
 * @property int $search_result 0=Valid,1=In-Valid 
 * @property int $created_at 
 */
class CertificateViewRequestFromWeb extends \yii\db\ActiveRecord
{
	public $arrCertificateStatus=array('0'=>'Valid','1'=>'In-Valid');
	public $arrEnumCertificateStatus=array('valid'=>'0','invalid'=>'1');
		
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_certificate_view_request_from_web';
    }

    public function behaviors()
    {
        return [           
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['search_result','request_method','created_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'ip_address' => 'IP Address',
			'search_result' => 'Search Result', 
			'request_method' => 'Request Method',			
            'created_at' => 'Created At',            
        ];
    }
}
