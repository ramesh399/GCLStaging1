<?php

namespace app\modules\library\models;

use Yii;

use app\modules\master\models\User;
use app\modules\master\models\Role;
/**
 * This is the model class for table "tbl_library_faq_access".
 *
 * @property int $id
 * @property int $library_faq_id
 * @property int $user_access_id
 */
class LibraryFaqAccess extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_faq_access';
    }

   
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['library_faq_id', 'user_access_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'library_faq_id' => 'Question',
            'franchise_id' => 'Answer',
        ];
    }
    
    public function getUseraccess()
    {
        //return $this->hasOne(Role::className(), ['id' => 'user_access_id']);
		return $this->hasOne(Role::className(), ['id' => 'user_access_id']);
    }

}
