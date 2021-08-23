<?php

namespace app\modules\library\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\Standard;
/**
 * This is the model class for table "tbl_library_legislation_standard".
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
class LibraryLegislationStandard extends \yii\db\ActiveRecord
{
   // public $arrUpdatedEnum=array('ic_meeting'=>'1','mrm_meeting'=>'2',"other"=>'3');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_legislation_standard';
    }

    public function behaviors()
    {
        return [
            
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
    
    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
    

}
