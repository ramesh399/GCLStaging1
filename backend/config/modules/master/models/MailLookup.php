<?php

namespace app\modules\master\models;

use Yii;
use app\modules\master\models\Settings;
/**
 * This is the model class for table "tbl_mail_lookup".
 *
 * @property int $id
 * @property string $subject
 * @property string $to
 * @property string $cc
 * @property string $bcc
 * @property string $from
 * @property string $message
 * @property string $attched_file
 * @property int $mail_notification_id
 * @property string $mail_notification_code
 * @property int $created_by
 * @property int $created_at
 */
class MailLookup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_mail_lookup';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['subject', 'to', 'cc', 'bcc', 'from', 'message', 'attched_file'], 'string'],
            [['mail_notification_id', 'created_by', 'created_at'], 'integer'],
            [['mail_notification_code'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'subject' => 'Subject',
            'to' => 'To',
            'cc' => 'Cc',
            'bcc' => 'Bcc',
            'from' => 'From',
            'message' => 'Message',
            'attched_file' => 'Attched File',
            'mail_notification_id' => 'Mail Notification ID',
            'mail_notification_code' => 'Mail Notification Code',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
        ];
    }
	
	
	public function sendMail()
	{
		$dbdata = Settings::find()->select('from_email')->where(['id' => '1'])->one();	
		
		$responsedata=array('status'=>0,'message'=>'failed');		
		$message = Yii::$app->mailer->compose();
        $message->setTo('infogcl1993@gmail.com');	
        $message->setFrom([$dbdata['from_email'] => 'GCL']);
								
		if($this->cc!='')
		{
		 	$message->setCc($this->cc);
		}
		
		if($this->bcc!='')
		{
			$message->setBcc($this->bcc);
        }
		
		if($this->mail_notification_code == 'inspection_plan_to_customer' || $this->mail_notification_code == 'offer_waiting_for_customer_approval' || $this->mail_notification_code == 'invoice_approved'){
			$filepath = '';
		}elseif($this->mail_notification_code == 'library'){
            $filepath = Yii::$app->params['library_files']."mail_attachments/";
        }else{
            $filepath = Yii::$app->basePath.'/web/attachmentfiles/';
        }
        
        if($this->attachment!='')
        {
            $attachments=json_decode($this->attachment);
            foreach($attachments as $files)
            {
                $message->attach( $filepath.$files);
            }
        }	
		
        $message->setReplyTo([$this->to]);
        $message->setSubject($this->subject);
        //$message->setTextBody($this->message);
		$message->setHtmlBody($this->message);				
        if($message->send())
		{
			$responsedata=array('status'=>1,'message'=>'Sent successfully');
		}		
		return  $responsedata;
	}	
}
