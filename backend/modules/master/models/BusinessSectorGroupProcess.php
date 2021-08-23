<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_business_sector_group_process".
 *
 * @property int $id
 * @property int $business_sector_group_id
 * @property int $process_id
 */
class BusinessSectorGroupProcess extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_business_sector_group_process';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['business_sector_group_id', 'process_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'business_sector_group_id' => 'Business Sector Group ID',
            'process_id' => 'Process ID',
        ];
    }

    public function getProcess()
    {
        return $this->hasOne(Process::className(), ['id' => 'process_id']);
    }
}
