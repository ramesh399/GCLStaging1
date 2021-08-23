<?php

namespace app\modules\application\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_application_unit_manday".
 *
 * @property int $id
 * @property int $unit_id
 * @property int $no_of_workers_from
 * @property int $no_of_workers_to
 * @property string $manday
 * @property string $discount_manday
 * @property string $final_manday
 * @property string $manday_cost
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class ApplicationUnitManday extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_unit_manday';
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
            [['unit_id', 'no_of_workers_from', 'no_of_workers_to', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['manday', 'discount_manday', 'final_manday', 'manday_cost'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'unit_id' => 'Unit ID',
            'no_of_workers_from' => 'No Of Workers From',
            'no_of_workers_to' => 'No Of Workers To',
            'manday' => 'Manday',
            'discount_manday' => 'Discount Manday',
            'final_manday_withtrans' => 'Final Manday With Trans',
            'final_manday' => 'Final Manday',
            'manday_cost' => 'Manday Cost',
            'translator_required' => 'Translator Required',
            'adjusted_manday_comment' => 'Adjusted Manday Comment',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
	/*
	public function getUnitmandaydiscount()
    {
        return $this->hasMany(ApplicationUnitMandayStandardDiscount::className(), ['unit_manday_id' => 'id']);
    }
    */
    public function getUnitmandaystandard()
    {
        return $this->hasMany(ApplicationUnitMandayStandard::className(), ['application_unit_manday_id' => 'id']);
    }

	public function getManday()
    {
        return $this->hasOne(ApplicationManday::className(), ['app_id' => 'app_id']);
    }

	public function getAudiplanhistory()
    {
        return $this->hasOne(AuditPlanUnitHistory::className(), ['app_id' => 'app_id']);
    }    
}
