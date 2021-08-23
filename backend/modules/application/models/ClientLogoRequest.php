<?php

namespace app\modules\application\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
use app\modules\master\models\Standard;
/**
 * This is the model class for table "tbl_client_logo_request".
 *
 * @property int $id
 * @property int $app_id
 * @property int $address_id
 * @property int $standard_id
 * @property string $comments
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class ClientLogoRequest extends \yii\db\ActiveRecord
{
    public $arrStatus=array('0'=>'Open');
	public $arrEnumStatus=array('open'=>'0');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_client_logo_request';
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

    public function rules()
    {
        return [
            [['app_id', 'address_id'], 'integer'],
            [['comments'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'Customer ID',
        ];
    }

    public function getCurrentaddress()
    {
        return $this->hasOne(ApplicationChangeAddress::className(), ['parent_app_id' => 'app_id'])->orderBy(['id' => SORT_DESC]);
	}

    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
	
    public function getCustomerchecklist()
    {
        return $this->hasMany(ClientLogoRequestCustomerChecklistComment::className(), ['client_logo_request_id' => 'id']);
    }
    
}
