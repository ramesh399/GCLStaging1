<?php

namespace app\modules\certificate\models;

use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_audit_reviewer_review".
 *
 * @property int $id
 * @property int $certificate_id
 * @property int $user_id
 * @property string $comment
 * @property string $answer
 * @property int $status 0=Open,1=Review in Process,2=Review Completed,3=Rejected
 * @property int $review_result 1=> Send Audit Plan, 2=>Don’t Send Audit Plan
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class CertificateFiles extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_certificate_files';
    }

    public function behaviors()
    {
        return [           
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['certificate_id', 'standard_id'], 'integer'],
            [['filename'], 'string'],
            [['filename'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'certificate_id' => 'Certificate ID',
			'standard_id' => 'Standard ID',
            'filename' => 'File Name',           
        ];
    }  
}
