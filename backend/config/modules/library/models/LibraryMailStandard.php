<?php

namespace app\modules\library\models;

use Yii;
use yii\db\ActiveRecord;
use app\modules\master\models\Standard;
/**
 * This is the model class for table "tbl_library_mail_standard".
 *
 * @property int $id
 * @property int $library_mail_id
 * @property int $standard_id
 */
class LibraryMailStandard extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_mail_standard';
    }

   
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['library_mail_id', 'standard_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'question' => 'Question',
            'answer' => 'Answer',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
    
    
    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
}
