<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_qualification_question_business_sector".
 *
 * @property int $id
 * @property int $qualification_question_id
 * @property int $business_sector_id
 */
class QualificationQuestionBusinessSector extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_qualification_question_business_sector';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['qualification_question_id', 'business_sector_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'qualification_question_id' => 'Qualification Question ID',
            'business_sector_id' => 'Business Sector ID',
        ];
    }

    public function getBusinesssector()
    {
        return $this->hasOne(BusinessSector::className(), ['id' => 'business_sector_id']);
    }
}
