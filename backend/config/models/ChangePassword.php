<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use app\modules\master\models\UserRole;

/**
 * Password reset form
 */
class ChangePassword extends Model
{
    public $old_password,$new_password,$confirm_password,$UserPassword;

    /**
     * @var \app\models\User
     */
    private $_user;

    /**
     * Creates a form model given a token.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws \yii\base\InvalidParamException if token is empty or not valid
    */

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['old_password', 'required'],
            ['old_password', 'string', 'min' => 6],
		    ['new_password', 'required'],
            ['new_password', 'string', 'min' => 6],
		    ['confirm_password', 'required'],
            ['confirm_password', 'string', 'min' => 6],
            [['old_password','new_password','confirm_password'], 'trim'],
            ['new_password','check_password'],	
        ];
    }
	
	public function check_password($attribute, $params)
    {
		if(trim($this->old_password)!='' && trim($this->new_password)!='')
		{
			if($this->old_password==$this->new_password)
			{
				$this->addError($attribute, "Old & New Password should not be same.");	
				return true;			
            }
            elseif(!Yii::$app->security->validatePassword($this->old_password,$this->UserPassword))
			{
				$this->addError('old_password', "Invalid Old Password.");	
				return true;
			}
		}
		
		if(trim($this->new_password)!='' && trim($this->confirm_password)!='')
		{
			if($this->new_password!=$this->confirm_password)
			{
				$this->addError($attribute, "New & Confirm Password should be same.");	
				return true;			
			}
		}
    }   


    /**
     * Resets password.
     *
     * @return bool if password was reset.
     */
    public function resetPassword($id)
    {
        $model = UserRole::find()->where(['id' => $id])->one();
        $model->setPassword($this->new_password);
        //$user->removePasswordResetToken();
		
        return $model->save(false);
    }
}
