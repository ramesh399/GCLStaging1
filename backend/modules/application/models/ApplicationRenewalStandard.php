<?php
namespace app\modules\application\models;

use Yii;
use app\modules\master\models\Standard;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_application_renewal_standard".
 *
 * @property int $id
 * @property int $app_renewal_id
 * @property int $standard_id
 * @property int $version
 * @property int $standard_addition_type
 */
class ApplicationRenewalStandard extends \yii\db\ActiveRecord
{
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_renewal_standard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['standard_id','app_renewal_id'], 'required'],
        ];
    }

    public function behaviors()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'standard_id' => 'Standard ID',
            'version' => 'Version'
        ];
    }

    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
}
