<?php
namespace app\modules\application\models;


use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\Standard;
/**
 * This is the model class for table "tbl_application_unit_manday_standard_discount".
 *
 * @property int $id
 * @property int $unit_manday_id
 * @property int $standard_id
 * @property string $discount
 * @property int $status 0=In-Valid,1=Valid
 */
class ApplicationUnitMandayStandardDiscount extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_unit_manday_standard_discount';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['unit_manday_id', 'status'], 'integer'],
            //[['discount'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'discount' => 'Discount',
            'status' => 'Status',
        ];
    }
	
    /*
    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
    */

    public function getStandard()
    {
        return $this->hasOne(ReductionStandard::className(), ['id' => 'certificate_standard_id']);
    }
    
}
