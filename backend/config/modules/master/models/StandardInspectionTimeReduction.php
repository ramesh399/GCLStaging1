<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_standard_inspection_time_reduction".
 *
 * @property int $id
 * @property int $reduction_percentage
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class StandardInspectionTimeReduction extends \yii\db\ActiveRecord
{
    //public $arrFindingAnswer = ['1'=>'Yes','2'=>'No','3'=>'Not Applicable'];
	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_standard_inspection_time_reduction';
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
            [['created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',            
            'reduction_percentage' => 'Reduction Percentage',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

     
    public function getReductionstandard()
    {
        return $this->hasMany(StandardInspectionTimeReductionStandard::className(), ['inspection_time_reduction_standard_id' => 'id']);
    }
	
	 
}
