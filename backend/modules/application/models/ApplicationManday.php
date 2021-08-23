<?php
namespace app\modules\application\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tbl_application_manday".
 *
 * @property int $id
 * @property int $app_id
 * @property string $manday
 * @property string $discount_manday
 * @property string $final_manday
 * @property string $total_manday_cost
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class ApplicationManday extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_manday';
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
            [['app_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['manday', 'discount_manday', 'final_manday', 'total_manday_cost'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App ID',
            'manday' => 'Manday',
            'discount_manday' => 'Discount Manday',
            'final_manday' => 'Final Manday',
            'total_manday_cost' => 'Total Manday Cost',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
	
	public function getUnitmanday()
    {
        return $this->hasMany(ApplicationUnitManday::className(), ['app_id' => 'app_id']);
    }
}
