<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_user_training_info".
 *
 * @property int $id
 * @property int $user_id
 * @property string $subject
 * @property string $training_date
 */
class AuditStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_standard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['standard_name'], 'string'],
            [['code'], 'string'],
            [['version'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'standard_name' => 'Standard Name',
            'code' => 'Code',
            'version' => 'Version',
        ];
    }
}
