<?php
namespace app\modules\master\models;

use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_standard_inspection_time_standard".
 *
 * @property int $id
 * @property int $standard_id
 * @property int $version
 * @property int $inspection_time_type
 */
class StandardInspectionTimeStandard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_standard_inspection_time_standard';
    }


    public function rules()
    {
        return [
            [['standard_id', 'inspection_time_type'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
        ];
    }
}
