<?php

namespace app\modules\library\models;

use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_library_manual_access".
 *
 * @property int $id
 * @property int $manual_id
 * @property int $user_access 
 */
class LibraryManualAccess extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_manual_access';
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
            [['manual_id','user_access'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'manual_id' => 'Manual ID',
            'user_access' => 'User Access',			
        ];
    }     
}
