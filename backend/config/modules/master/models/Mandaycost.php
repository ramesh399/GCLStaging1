<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_man_day_cost".
 *
 * @property int $id
 * @property string $country_id
 * @property string $man_day_cost
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class Mandaycost extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */	
    public static function tableName()
    {
        return 'tbl_man_day_cost';
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
            [['country_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            ['country_id', 'uniquecountry'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'country_id' => 'Country Name',
            'man_day_cost' => 'Man Day Cost',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public function uniquecountry($attribute) 
    {
		if($this->id!='')
		{
           $mandaymodel = Mandaycost::find()->where(['country_id' => $this->country_id])->andWhere(['!=', 'id', $this->id])->andWhere(['!=', 'status', 2])->one();
		}else{
            $mandaymodel = Mandaycost::find()->where(['country_id' => $this->country_id])->andWhere(['!=', 'status', 2])->one();
            //andWhere(['!=', 'status', 2])->
		}
		
        if ($mandaymodel) 
        {
            $countryname=$mandaymodel->country->name;
            $this->addError($attribute, $countryname." has Man day cost already");
        }
    }
	
	public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }
	
	public function getMandaycosttax()
    {
        return $this->hasMany(ManDayCostTax::className(), ['man_day_cost_id' => 'id'])->andOnCondition(['tax_for' => 1]);;
    }
	
	public function getMandaycosttaxotherstate()
    {
        return $this->hasMany(ManDayCostTax::className(), ['man_day_cost_id' => 'id'])->andOnCondition(['tax_for' => 2]);;
    }
}
