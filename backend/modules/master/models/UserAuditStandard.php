<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_user_audit_experience".
 *
 * @property int $id
 * @property int $user_id
 * @property int $standard_id
 * @property string $year
 * @property string $company
 * @property string $cb
 * @property string $days
 */
class UserAuditStandard extends \yii\db\ActiveRecord
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
            [['standard_name'], 'string', 'max' => 25],
            [['code'], 'number'],
            [['version'], 'number'],
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
