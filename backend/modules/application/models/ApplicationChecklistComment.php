<?php

namespace app\modules\application\models;

use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_application_checklist_comment".
 *
 * @property int $id
 * @property int $app_id
 * @property int $question_id
 * @property string $question
 * @property string $answer
 * @property string $comment
 * @property string $document
 
 */
class ApplicationChecklistComment extends \yii\db\ActiveRecord
{
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_checklist_comment';
    }

    

    
    public function rules()
    {
        return [
            [['app_id', 'question_id'], 'integer'],
            [['question','document','answer','comment'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App ID',
            'question_id' => 'Question ID',
            'question' => 'Question',
            'answer' => 'Answer',
            'comment' => 'Comment',
            'document' => 'Document',
        ];
    }
}
