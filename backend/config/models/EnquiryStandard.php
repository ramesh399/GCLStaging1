<?php

namespace app\models;

use Yii;
use app\modules\master\models\Standard;
/**
 * This is the model class for table "tbl_enquiry_standard".
 *
 * @property int $id
 * @property int $enquiry_id
 * @property int $standard_id
 */
class EnquiryStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_enquiry_standard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['enquiry_id', 'standard_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'enquiry_id' => 'Enquiry ID',
            'standard_id' => 'Standard ID',
        ];
    }
	
	public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
}
