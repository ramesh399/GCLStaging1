<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_role".
 *
 * @property int $id
 * @property string $role_name
 * @property int $resource_access 1=All,2=Custom
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class Role extends \yii\db\ActiveRecord
{
    public $arrRoles=array('1'=>'All','2'=>'Custom','3'=>'Technical Expert','4'=>'Translator','5'=>'OSS Admin','6'=>'Client','7'=>'OSS [For Downloads & Information Centre View Only]','8'=>'Marketing Person');

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_role';
    }

    /**
     * {@inheritdoc}
     */
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
    
    public function rules()
    {
        return [
            [['resource_access', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['role_name'], 'string', 'max' => 255],
			['role_name', 'unique','filter' => ['!=','status' ,2]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_name' => 'Role Name',
            'resource_access' => 'Resource Access',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public function getUserrules()
    {
        return $this->hasMany(Rule::className(), ['role_id' => 'id']);
    }
}
