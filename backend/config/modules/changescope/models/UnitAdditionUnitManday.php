<?php
namespace app\modules\changescope\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;

/**
 * This is the model class for table "tbl_cs_unit_addition_unit_manday".
 *
 * @property int $id
 * @property int $unit_addition_id
 * @property int $no_of_workers_from
 * @property int $no_of_workers_to
 * @property float $manday
 * @property float $total_discount
 * @property float $eligible_discount
 * @property float $maximum_discount
 * @property float $discount_manday
 * @property float $same_standard_certified_discount_manday
 * @property float $final_manday
 * @property float $manday_cost
 * @property float $adjusted_manday
 * @property float $unit_manday_cost
 * @property int $same_standard_certified_count
 * @property string $adjusted_manday_comment
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class UnitAdditionUnitManday extends \yii\db\ActiveRecord
{
	
	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_unit_addition_unit_manday';
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
            [['unit_addition_id', 'no_of_workers_from', 'no_of_workers_to', 'same_standard_certified_count', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'unit_addition_id' => 'Unit Addition ID',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
	
	
     
}
