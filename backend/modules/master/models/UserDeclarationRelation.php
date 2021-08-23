<?php

namespace app\modules\master\models;

use Yii;


class UserDeclarationRelation extends \yii\db\ActiveRecord
{
	
    
    public static function tableName()
    {
        return 'tbl_user_declaration_relation';
    }

   
    
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            're_relation_declaration_consent' => 'Re Relation Declaration Consent',
            'user_declaration_id' => 'User Declaration Id',
            'relation_name' => 'Relation Name',
            'relation_type' => 'Relation Type',
           
        ];
    }
    
}
