<?php

namespace app\modules\library\models;

use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_library_manual_file".
 *
 * @property int $id
 * @property int $manual_id
 * @property string $document 
 */
class LibraryManualFile extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_manual_file';
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
            'manual_id' => 'Manual ID',
            'document' => 'Document',			
        ];
    }     
}
