<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;

/**
 * This is the model class for table "tbl_audit_report_environment".
 *
 * @property int $id
 * @property int $audit_id
 * @property string $year
 * @property string $total_production_output
 * @property string $total_water_supplied
 * @property string $water_consumption
 * @property string $electrical_energy_consumption
 * @property string $gas_consumption
 * @property string $oil_consumption
 * @property string $coal_consumption
 * @property string $fuelwood_consumption
 * @property string $total_energy_consumption_converted_to
 * @property string $total_energy_consumption
 * @property string $product_waste
 * @property string $total_solid_waste
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class AuditReportEnvironment extends \yii\db\ActiveRecord
{
    public $arrSufficient=array('1'=>'Yes','2'=>'No','3'=>'N/A');
    public $arrEnumSufficient=array('yes'=>'1','no'=>'2','na'=>'3');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_environment';
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
            [['audit_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
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
