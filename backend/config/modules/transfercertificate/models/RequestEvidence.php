<?php

namespace app\modules\transfercertificate\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use app\modules\application\models\Application;
use app\modules\application\models\ApplicationUnit;
use app\modules\application\models\ApplicationStandard;
use app\modules\application\models\ApplicationUnitProduct;

/**
 * This is the model class for table "tbl_tc_request_evidence".
 *
 * @property int $id
 * @property int $app_id
 * @property int $unit_id
 * @property int $buyer_id
 * @property int $consignee_id
 * @property int $standard_id
 * @property string $purchase_order_number
 * @property string $comments
 * @property int $transport_id 
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class RequestEvidence extends \yii\db\ActiveRecord
{
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_request_evidence';
    }

    public function behaviors()
    {
        return [];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tc_request_id'], 'required'],
           
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
        ];
    }	
    

}
