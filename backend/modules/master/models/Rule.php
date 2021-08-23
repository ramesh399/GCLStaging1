<?php

namespace app\modules\master\models;

use Yii;
/**
 * This is the model class for table "tbl_rule".
 *
 * @property int $id
 * @property int $role_id
 * @property int $privilege_id
 * @property string $privilege
 */
class Rule extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_rule';
    }

    /**
     * {@inheritdoc}
     */
    
    public function rules()
    {
        return [
            [['role_id', 'privilege_id'], 'integer'],
            [['privilege'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_id' => 'Role ID',
            'privilege_id' => 'Privilege ID',
            'privilege' => 'Privilege',
        ];
    }

    public function getPrivilege()
    {
        return $this->hasOne(Privileges::className(), ['id' => 'privilege_id']);
    }
}
