<?php

namespace app\models;

use Yii;
use app\modules\master\models\FscStandard;

/**
 * This is the model class for table "tbl_enquiry_fsc_standard".
 *
 * @property int $id
 * @property int|null $enquiry_id
 * @property int|null $fsc_standard_id
 */
class EnquiryFscStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_enquiry_fsc_standard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['enquiry_id', 'fsc_standard_id'], 'integer'],
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
            'fsc_standard_id' => 'Fsc Standard ID',
        ];
    }

    public function getFscstandard()
    {
        return $this->hasOne(FscStandard::className(), ['id' => 'fsc_standard_id']);
    }
}
