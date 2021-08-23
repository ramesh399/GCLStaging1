<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_qualification_question_business_sector_group".
 *
 * @property int $id
 * @property int $qualification_question_id
 * @property int $business_sector_group_id
 */
class QualificationQuestionBusinessSectorGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_qualification_question_business_sector_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['qualification_question_id', 'business_sector_group_id'], 'integer'],
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
            'business_sector_group_id' => 'Business Sector Group ID',
        ];
    }

    public function getBusinesssectorgroup()
    {
        return $this->hasOne(BusinessSectorGroup::className(), ['id' => 'business_sector_group_id']);
    }
}
