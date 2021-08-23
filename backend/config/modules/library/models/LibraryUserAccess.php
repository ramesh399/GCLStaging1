<?php

namespace app\modules\library\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_library_user_access".
 *
 * @property int $id
 * @property string $name
 * @property int $status 
 */
class LibraryUserAccess extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_user_access';
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
            [['name'], 'string'],
            [['status'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',            		
            'status' => 'Status',            
        ];
    }      
}
