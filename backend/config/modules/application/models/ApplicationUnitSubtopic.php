<?php

namespace app\modules\application\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_application_approver".
 *
 * @property int $id
 * @property int $app_id
 * @property int $unit_id
 * @property int $subtopic_id
 * @property int $created_by
 * @property int $created_at
 */
class ApplicationUnitSubtopic extends \yii\db\ActiveRecord
{
	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_unit_subtopic';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['app_id', 'user_id'], 'integer'],
        ];
    }

    public function behaviors()
    {
        return [

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
            'unit_id' => 'Unit ID'
        ];
    }
}
