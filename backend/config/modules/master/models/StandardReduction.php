<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_standard_reduction".
 *
 * @property int $id
 * @property int $standard_id
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class StandardReduction extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_standard_reduction';
    }

    /**
     * {@inheritdoc}
     */
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
            [['standard_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
			['standard_id', 'uniquestandard'],
        ];
    }

    public function uniquestandard($attribute) 
    {
        if($this->id!='')
		{
            $stdmodel = StandardReduction::find()->where(['standard_id' => $this->standard_id])->andWhere(['!=', 'id', $this->id])->andWhere(['!=', 'status', 2])->one();
        }
        else
        {
            $stdmodel = StandardReduction::find()->where(['standard_id' => $this->standard_id])->andWhere(['!=', 'status', 2])->one();
        }

        if ($stdmodel) 
        {
            $standardname=$stdmodel->standard->name;
            $this->addError($attribute, $standardname." has been taken already");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'standard_id' => 'Standard ID',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
	
	public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }

    public function getReductionstandard()
    {
        return $this->hasOne(ReductionStandard::className(), ['id' => 'standard_id']);
    }
	
	public function getStandardreduction()
    {
        return $this->hasMany(StandardReductionRate::className(), ['standard_reduction_id' => 'id']);
    }
}
