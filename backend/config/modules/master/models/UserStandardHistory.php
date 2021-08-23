<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_user_standard_history".
 *
 * @property int $id
 * @property int $user_id
 * @property int $standard_id
 * @property int $qualification_status
 * @property string $qualified_date
 */
class UserStandardHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_standard_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'standard_id', 'qualification_status'], 'integer'],
            [['qualified_date'], 'safe'],
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
            'standard_id' => 'Standard ID',
            'qualification_status' => 'Qualification Status',
            'qualified_date' => 'Qualified Date',
        ];
    }
    public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
    public function getApprovaluser()
    {
        return $this->hasOne(User::className(), ['id' => 'approval_by']);
    }
}
