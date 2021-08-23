<?php

namespace app\modules\library\models;

use Yii;

use app\modules\master\models\User;
use app\modules\master\models\Role;
/**
 * This is the model class for table "tbl_library_audit_report_access".
 *
 * @property int $id
 * @property int $library_audit_report_id
 * @property int $user_access_id
 */
class LibraryAuditReportAccess extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_library_audit_report_access';
    }

   
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['library_audit_report_id', 'user_access_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'library_audit_report_id' => 'Library Audit Report',
            'user_access_id' => 'User Role',
        ];
    }
    
    public function getUseraccess()
    {
        return $this->hasOne(Role::className(), ['id' => 'user_access_id']);
    }

}
