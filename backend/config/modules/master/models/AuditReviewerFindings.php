<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table " tbl_audit_reviewer_findings".
 *
 * @property int $id
 * @property string $name
 * @property int $status
 */
class AuditReviewerFindings extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_reviewer_findings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'status' => 'Status',
        ];
    }
}
