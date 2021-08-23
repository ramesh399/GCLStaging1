<?php

namespace app\modules\informationcenter\models;

use Yii;

/**
 * This is the model class for table "tbl_issues".
 *
 * @property int $id
 * @property string $issue_type
 * @property string $description
 * @property string $status Open/Closed/Inprogress
 * @property string $ticket INCXXXXX
 * @property string|null $created_date
 * @property string|null $created_name
 * @property string $created_from
 * @property string $contact
 * @property string $priority
 * @property int $file
 * @property int $questionone
 * @property int $questiontwo
 * @property string|null $downtimestart
 * @property string|null $downtimeend
 */
class Issues extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_issues';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['issue_type', 'description', 'status', 'ticket', 'created_from', 'contact', 'priority', 'questionone', 'questiontwo'], 'required'],
            [['issue_type', 'description', 'ticket', 'created_from', 'contact', 'priority'], 'string'],
            [['created_date', 'created_name', 'downtimestart', 'downtimeend'], 'safe'],
            [['status'], 'string', 'max' => 11],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'issue_type' => 'Issue Type',
            'description' => 'Description',
            'status' => 'Status',
            'ticket' => 'Ticket',
            'created_date' => 'Created Date',
            'created_name' => 'Created Name',
            'created_from' => 'Created From',
            'contact' => 'Contact',
            'priority' => 'Priority',
            'file' => 'File',
            'questionone' => 'Questionone',
            'questiontwo' => 'Questiontwo',
            'downtimestart' => 'Downtimestart',
            'downtimeend' => 'Downtimeend',
        ];
    }
}
