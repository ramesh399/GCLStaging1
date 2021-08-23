<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_certificate_tc_royalty_fee_cs".
 *
 * @property int $id
 * @property int $certificate_tc_royalty_fee_id
 * @property int $standard_id
 */
class CertificateTcRoyaltyFeeCs extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_certificate_tc_royalty_fee_cs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['certificate_tc_royalty_fee_id', 'standard_id'], 'integer'],
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
	
	public function getStandard()
    {
        return $this->hasOne(Standard::className(), ['id' => 'standard_id']);
    }
}
