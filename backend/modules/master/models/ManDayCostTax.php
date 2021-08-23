<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_man_day_cost_tax".
 *
 * @property int $id
 * @property string $tax_name
 * @property int $tax_percentage
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class ManDayCostTax extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
	public $arrTaxLabel=array('1'=>'Same State','2'=>'Other State');
    public static function tableName()
    {
        return 'tbl_man_day_cost_tax';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [           
        ];
    }

    public function rules()
    {
        return [
		    [['tax_name'], 'string'],
            [['man_day_cost_id'], 'integer'],
            [['tax_name'], 'string', 'max' => 255],           
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tax_name' => 'Tax Name',
            'tax_percentage' => 'Tax Percentage',
            
        ];
    }
}
