<?php

namespace app\models;

use Yii;

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
		// $responsedata=array();
		// $message = Yii::$app->mailer->compose(['html' =>'layouts/mailNotificationTemplate'],['content' => $this->message]);
         $dbdata = Settings::find()->select('from_email')->where(['id' => '1'])->one();
		// $message->setFrom($dbdata['from_email']);
		// $message->setTo($this->to);
		// if($this->cc!='')
		// {
		// 	$message->setCc($this->cc);
		// }
		
		// if($this->bcc!='')
		// {
		// 	$message->setBcc($this->bcc);
        // }
        
        // if($this->attachment!='')
        // {
        //     $attachments=json_decode($this->attachment);
        //     foreach($attachments as $files)
        //     {
        //         $message->attach(Yii::$app->basePath.'/web/attachmentfiles/'.$files);
        //     }
        // }

		// $message->setTo($this->to);
        // $message->setSubject($this->subject);
        
        $to = $this->to;
        $subject = $this->subject;
        $message = $this->message;
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: ".$dbdata['from_email'] . "\r\n" .
        "CC: ".$this->cc;
        
        $responsedata=array('status'=>0,'message'=>'failed');
		if(mail($to,$subject,$message,$headers))
		{
			$responsedata=array('status'=>1,'message'=>'Sent successfully');
		}
		return  $responsedata;
	}
}
