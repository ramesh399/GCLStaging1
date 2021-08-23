<?php

namespace app\modules\library\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_library_legislation_log".
 *
 * @property int $id
 * @property string $question
 * @property string $answer
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class LibraryLegislationLog extends \yii\db\ActiveRecord
{
    public $arrStatus=array('1'=>'Current','2'=>'Withdrawn');
    public $arrChanged=array('1'=>'Yes','2'=>'No');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_legislation_log';
    }

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
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['description'], 'string'],
            [['library_legislation_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'library_legislation_id' => 'Library Legislation',
        ];
    }
    
    public function getCreateduser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
    public function getUpdateduser()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }
}
