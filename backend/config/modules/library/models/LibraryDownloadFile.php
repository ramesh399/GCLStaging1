<?php

namespace app\modules\library\models;

use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_library_download_file".
 *
 * @property int $id
 * @property int $manual_id
 * @property string $document 
 */
class LibraryDownloadFile extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_download_file';
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
            [['document'], 'string'],
            [['manual_id'], 'integer'],
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
            'document' => 'Document',			
        ];
    }  
	
	public function getLibrarymanual()
    {
        return $this->hasOne(LibraryDownload::className(), ['id' => 'manual_id']);
    }
}
