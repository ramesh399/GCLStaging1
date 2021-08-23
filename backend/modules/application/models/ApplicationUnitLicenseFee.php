<?php
namespace app\modules\application\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_application_unit_license_fee".
 *
 * @property int $id
 * @property int $unit_id
 * @property int $standard_id
 * @property string $license_fee
 * @property string $subsequent_license_fee
 */
class ApplicationUnitLicenseFee extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_unit_license_fee';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['unit_id', 'standard_id'], 'integer'],
            [['license_fee', 'subsequent_license_fee'], 'number'],
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
            'standard_id' => 'Standard ID',
            'license_fee' => 'License Fee',
            'subsequent_license_fee' => 'Subsequent License Fee',
        ];
    }
}
