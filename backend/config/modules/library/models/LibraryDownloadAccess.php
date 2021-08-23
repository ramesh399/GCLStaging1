<?php

namespace app\modules\library\models;

use Yii;
use yii\db\ActiveRecord;

use app\modules\master\models\Role;
/**
 * This is the model class for table "tbl_library_download_access".
 *
 * @property int $id
 * @property int $manual_id
 * @property int $user_access 
 */
class LibraryDownloadAccess extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_download_access';
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
            'manual_id' => 'Download ID',
            'user_access' => 'User Access',			
        ];
    }     
    public function getUseraccess()
    {
        return $this->hasOne(Role::className(), ['id' => 'user_access']);
    }
}
