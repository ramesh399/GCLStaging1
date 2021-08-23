<?php

namespace app\modules\library\models;

use Yii;
use yii\db\ActiveRecord;

use app\modules\master\models\Standard;
/**
 * This is the model class for table "tbl_library_download_standard".
 *
 * @property int $id
 * @property int $manual_id
 * @property int $standard_id 
 */
class LibraryDownloadStandard extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_download_standard';
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
            [['manual_id','standard_id'], 'integer'],
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
            'standard_id' => 'Standard ID',			
        ];
    }     
    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
}
