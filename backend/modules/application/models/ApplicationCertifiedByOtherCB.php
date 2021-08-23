<?php

namespace app\modules\application\models;

use Yii;
use app\modules\master\models\Standard;
use app\modules\master\models\Cb;
/**
 * This is the model class for table "tbl_application_certified_by_other_cb".
 *
 * @property int $id
 * @property int $app_id
 * @property int $standard_id
 */
class ApplicationCertifiedByOtherCB extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_application_certified_by_other_cb';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_id', 'standard_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App ID',
            'standard_id' => 'Standard ID',
			'version' => 'Version',
			'certification_body' => 'Certification Body',
			'validity_date' => 'Validity Date',
			'certification_file' => 'File',			
        ];
    }
	
	public function getApplication()
    {
        return $this->hasOne(Application::className(), ['id' => 'app_id']);
    }
	
	public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }

    public function getCb()
    {
        return $this->hasOne(Cb::className(), ['id' => 'certification_body']);
    }
}
