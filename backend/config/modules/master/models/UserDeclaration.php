<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_user_declaration".
 *
 * @property int $id
 * @property int $user_id
 * @property string $company
 * @property string $contract
 * @property string $interest
 * @property string $start_year
 * @property string $end_year
 * @property int $created_on
 * @property int $created_by
 * @property int $status 0=New,1=Waiting for approval, 2=Approved, 3=Rejected
 * @property int $status_change_by
 * @property int $status_change_date
 */
class UserDeclaration extends \yii\db\ActiveRecord
{
	public $arrContract=array('1'=>'Part Time','2'=>'Full Time','3'=>'Sub-Contract','4'=>'Shareholder');
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_declaration';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_on', 'created_by' , 'status_change_date'], 'integer'],
            [['company', 'interest'], 'string', 'max' => 255]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'company' => 'Company',
            'contract' => 'Contract',
            'interest' => 'Interest',
            'start_year' => 'Start Year',
            'end_year' => 'End Year',
            'created_on' => 'Created On',
            'created_by' => 'Created By',
            'status' => 'Status',
            'status_change_by' => 'Status Change By',
            'status_change_date' => 'Status Change Date',
        ];
    }
    public function getApprovaluser()
    {
        return $this->hasOne(User::className(), ['id' => 'status_change_by']);
    }
}
