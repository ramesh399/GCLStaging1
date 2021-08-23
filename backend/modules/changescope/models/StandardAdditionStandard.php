<?php
namespace app\modules\changescope\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use app\modules\master\models\Standard;
use app\modules\master\models\User;

/**
 * This is the model class for table "tbl_cs_standard_addition_standard".
 *
 * @property int $id
 * @property int $app_id
 * @property int $new_app_id
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class StandardAdditionStandard extends \yii\db\ActiveRecord
{
	
	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_standard_addition_standard';
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
            [['standard_addition_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'standard_addition_id' => 'Standard Addition ID',
            'standard_id' => 'Standard ID'
        ];
    }
	public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
	/*
	public function getApplication()
    {
        return $this->hasOne(Application::className(), ['id' => 'app_id']);
    }
     */
}
