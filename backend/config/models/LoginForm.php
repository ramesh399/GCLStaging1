<?php

namespace app\models;

use Yii;
use yii\base\Model;

use app\modules\master\models\UserRole;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
			
			//1=User,2=Customer,3=Franchise
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
			//}elseif ($user->user->user_type==1 || $user->user->user_type==2 || $user->user->user_type==3) {	
				//$this->addError($attribute, 'Your account is not active.--Role Status:'.$user->status.'--approval_status:'.$user->approval_status.'--User Type:'.$user->user->user_type.'--User Status:'.$user->user->status);
            }elseif ($user->user->user_type==1 && ($user->franchise->status!=0 || $user->user->status!=0 || $user->status!=0 || $user->approval_status!=2)) {
				$this->addError($attribute, 'Your account is not active.');
			}elseif (($user->user->user_type==2 || $user->user->user_type==3) && ($user->user->status!=0 || $user->status!=0)) {
				$this->addError($attribute, 'Your account is not active.');
			}
        }
		
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = UserRole::findByUsername($this->username);
        }

        return $this->_user;
    }
}
