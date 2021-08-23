<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_audit_non_conformity_timeline".
 *
 * @property int $id
 * @property string $name
 * @property int $timeline
 * @property int $status
 */
class AuditNonConformityTimeline extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_non_conformity_timeline';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['timeline', 'status'], 'integer'],
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
            'timeline' => 'Timeline',
            'status' => 'Status',
        ];
    }
}
