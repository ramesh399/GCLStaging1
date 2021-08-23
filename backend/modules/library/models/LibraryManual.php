<?php

namespace app\modules\library\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_library_manual".
 *
 * @property int $id
 * @property string $title
 * @property string $version
 * @property date $document_date
 * @property string $description
 * @property int $reviewer
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class LibraryManual extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_manual';
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
            [['title', 'version', 'description'], 'string'],
            [['status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'version' => 'Version',
			'document_date' => 'Document Date',
			'description' => 'Description',
			'reviewer' => 'Reviewer',			
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }   
	
    public function getLibrarymanualfiles()
    {
        return $this->hasMany(LibraryManualFile::className(), ['manual_id' => 'id']);
    }
	
	public function getLibrarymanualaccess()
    {
        return $this->hasMany(LibraryManualAccess::className(), ['manual_id' => 'id']);
    }
}
