<?php

namespace app\modules\audit\models;

use Yii;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_audit_report_chemical_list_auditor_conformity".
 *
 * @property int $id
 * @property string $name
 */
class AuditReportChemicalListAuditorConformity extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_chemical_list_auditor_conformity';
    }

   
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            
        ];
    }
    
    
    
}
