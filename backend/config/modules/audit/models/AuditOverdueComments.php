<?php

namespace app\modules\audit\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_audit_overdue_comments".
 *
 * @property int $id
 * @property int $audit_id
 * @property int $audit_plan_id
 * @property int $type
 * @property string $answer
 * @property string $comment
 * @property int $created_by
 * @property int $created_at
 */
class AuditOverdueComments extends \yii\db\ActiveRecord
{
    public $arrAuditorAnswer=[1=>'Forwarded to Reviewer'];
    public $arrReviewerAnswer=[1=>'Approve'];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_overdue_comments';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['audit_plan_id', 'type'], 'integer'],
            [['answer', 'comment'], 'string'],
            [['answer'], 'string', 'max' => 255],
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

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    
}
