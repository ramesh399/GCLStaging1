<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
/**
 * This is the model class for table "tbl_user_role".
 *
 * @property int $id
 * @property int $user_id
 * @property int $role_id
 */
class UserRole extends ActiveRecord implements IdentityInterface
{
	const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 0;    
    const ADMIN_STATUS_ACTIVE = 1;
	const APPROVAL_STATUS = 2;
	public $role_name, $role_franchise_id;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_role';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at','updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'role_id','franchise_id', 'status', 'login_status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['username', 'password_hash', 'password_reset_token'], 'string', 'max' => 255],
            ['username', 'unique','filter' => ['!=','status' ,2]],
            [['role_id', 'franchise_id'], 'uniquerole_franchise']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'role_id' => 'Role ID',
        ];
    }

    public function uniquerole_franchise($attribute) 
    {
		/*if($this->id!='')
		{
           $rolemodel = UserRole::find()->where(['role_id' => $this->role_id])->andWhere(['franchise_id' => $this->franchise_id])->andWhere(['!=', 'id', $this->id])->one();
		}else{
            */
			$rolemodel = UserRole::find()->where(['role_id' => $this->role_id])->andWhere(['franchise_id' => $this->franchise_id])->andWhere(['user_id' => $this->user_id])->one();
		//}
		
        if ($rolemodel) 
        {
            //$rolename=$rolemodel->role->role_name;
            //$this->addError($attribute, "The Combination of '".$rolename."' with franchise has been taken already");
        }
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }
	
	//  -----------  User Login Related Code Start Here --------------
	/**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        //return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE, 'approval_status' => self::APPROVAL_STATUS]);
		return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByUsername($email)
    {
        //return static::findOne(['username' => $email, 'status' => self::STATUS_ACTIVE, 'approval_status' => self::APPROVAL_STATUS]);
        //return static::findOne(['md5(username)' => md5($email)]);
        return static::find()->where(['md5(username)' => md5($email)])->one();
    }
    
    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }       

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
			//'approval_status' => self::APPROVAL_STATUS,
        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token) {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::STATUS_INACTIVE,
			//'approval_status' => self::APPROVAL_STATUS,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($auth_key)
    {
        return $this->getAuthKey() === $auth_key;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function generateUsername()
    {
        return Yii::$app->security->generateRandomString(5).time();
    }
	
	 public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        //echo Yii::$app->security->generateRandomString();
			
		$this->auth_key = Yii::$app->security->generateRandomString();		
		
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
	//  -----------  User Login Related Code End Here ----------------

    public function getRole()
    {
        return $this->hasOne(Role::className(), ['id' => 'role_id']);
    }
	
	public function getUsercompanyinfo()
    {
        return $this->hasOne(UserCompanyInfo::className(), ['user_id' => 'user_id']);
    }

	public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
	
    public function getApprovaluser()
    {
        return $this->hasOne(User::className(), ['id' => 'approval_by']);
    }
    

    public function getFranchise()
    {
        return $this->hasOne(User::className(), ['id' => 'franchise_id']);
    }
    public function getUserrules()
    {
        return $this->hasMany(Rule::className(), ['role_id' => 'role_id']);
    }
	
}
