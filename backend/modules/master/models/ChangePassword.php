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
    public $oldPassword,$newPassword,$confirmPassword;

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
            ['oldPassword', 'required'],
            ['oldPassword', 'string', 'min' => 6],
		    ['newPassword', 'required'],
            ['newPassword', 'string', 'min' => 6],
		    ['confirmPassword', 'required'],
            ['confirmPassword', 'string', 'min' => 6],
            //[['oldPassword','newPassword','confirmPassword'], 'trim'],
            ['newPassword','check_password'],	
        ];
    }
	
	public function check_password($attribute, $params)
    {
		if(trim($this->oldPassword)!='' && trim($this->newPassword)!='')
		{
			if($this->oldPassword==$this->newPassword)
			{
				$this->addError($attribute, "Old & New Password should not be same.");	
				return true;			
            }
            elseif(!Yii::$app->security->validatePassword($this->oldPassword, Yii::$app->user->identity->UserPassword))
			{
				$this->addError('oldPassword', "Invalid Old Password.");	
				return true;
			}
		}
		
		if(trim($this->newPassword)!='' && trim($this->confirmPassword)!='')
		{
			if($this->newPassword!=$this->confirmPassword)
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
    public function resetPassword()
    {
        //$user = $this->_user;
		if ($this->_user === null) {
            $this->_user = UserRole::findByUsername(Yii::$app->user->identity->Username);
        }
		//$user = new User();
		//echo get_class($user);
		//die($this->newPassword);
		$user = $this->_user;
        $user->setPassword($this->newPassword);
		//$user->removePasswordResetToken();
		
        return $user->save(false);
    }
}
