<?php

namespace app\modules\changescope\models;

use Yii;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_cs_product_addition_reviewer_comment".
 *
 * @property int $id
 * @property int $product_addition_id
 * @property int $product_addition_reviewer_id
 * @property int $status
 * @property string $comment
 * @property int $created_by
 * @property int $created_at 
 */
class ProductAdditionReviewerComment extends \yii\db\ActiveRecord
{
    public $arrStatus=array('1'=>'Forwarded to Approval','2'=>'Send Back to Franchise','3'=>'Reject');
	public $arrEnumStatus=array('forwarded_to_approval'=>'1','send_back_to_franchise'=>'2','reject'=>3);
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cs_product_addition_reviewer_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_addition_id', 'product_addition_reviewer_id', 'status', 'created_by', 'created_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_addition_id' => 'Product Addition ID',
            'status' => 'Status',
        ];
    }
    
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }


    
}
