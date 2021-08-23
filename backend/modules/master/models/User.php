<?php

namespace app\modules\master\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "tbl_users".
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $telephone
 * @property int $role_id
 * @property int $country_id
 * @property int $state_id
 * @property string $date_of_birth
 * @property int $is_auditor
 * @property int $send_mail_notification_status
 * @property int $user_type 1=User,2=Customer
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class User extends ActiveRecord implements IdentityInterface 	
{
	public $arrStatus=array('1'=>'User','2'=>'Customer','3'=>'Franchise');
    public $arrEnumStatus=array('user'=>'1','customer'=>'2','franchise'=>'3');
    
    public $arrUserStatus=array('0'=>'Active','1'=>'In-Active');
    public $arrUserEnumStatus=array('active'=>'0','in_active'=>'1');
	
    public $arrLoginStatus=array('0'=>'Active','1'=>'In-Active');
    public $arrLoginEnumStatus=array('active'=>'0','in_active'=>'1');

    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 0;    
    const ADMIN_STATUS_ACTIVE = 1;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_users';
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


    public function rules()
    {
        return [
            //[['country_id', 'state_id', 'send_mail_notification_status', 'user_type', 'status','created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
			[['send_mail_notification_status', 'user_type', 'status','created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['date_of_birth'], 'safe'],
            //[['first_name', 'last_name','email'], 'string', 'max' => 255],
            //[['telephone'], 'string', 'max' => 50]
        ];
    }


    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
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
    // public static function findByUsername($email)
    // {
    //     return static::findOne(['username' => $email, 'status' => self::STATUS_ACTIVE]);
    // }
    
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
            'status' => self::STATUS_INACTIVE
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
    // public function generateUsername()
    // {
    //     return Yii::$app->security->generateRandomString(5).time();
    // }

    public function generatePassword()
    {
        return Yii::$app->security->generateRandomString(15);
    }

    // public function setUsername($username)
    // {
    //     $this->username = $username;
    // }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
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

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'telephone' => 'Telephone',
            'role_id' => 'Role ID',
            'country_id' => 'Country ID',
            'state_id' => 'State ID',
            'date_of_birth' => 'Date Of Birth',
            'is_auditor' => 'Is Auditor',
            'send_mail_notification_status' => 'Send Mail Notification Status',
            'user_type' => 'User Type',
            'status' => 'Status',
            'login_status' => 'Login Status',
            'verification_token' => 'Verification Token',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    protected function sendEmail($user)
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('Account registration at ' . Yii::$app->name)
            ->send();
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }
    
	
	public function getState()
    {
        return $this->hasOne(State::className(), ['id' => 'state_id']);
    }

    public function getUserbrand()
    {
        return $this->hasOne(Brand::className(),['user_id'=>'id']);
    }

    public function getUserbrandgroup()
    {
        return $this->hasOne(BrandGroup::className(),['user_id'=>'id']);
    }

	public function getUsercompanyinfo()
    {
        return $this->hasOne(UserCompanyInfo::className(), ['user_id' => 'id']);
    }
    public function getUserrole()
    {
        return $this->hasOne(Role::className(), ['id' => 'role_id']);
    }
    public function getUserpayment()
    {
        return $this->hasMany(UserPaymentDetails::className(), ['user_id' => 'id']);
    }
    public function getUsersrole()
    {
        return $this->hasMany(UserRole::className(), ['user_id' => 'id']);
    }
    public function getUserrules()
    {
        return $this->hasMany(Rule::className(), ['role_id' => 'role_id']);
    }
    public function getUserstandard()
    {
        return $this->hasMany(UserStandard::className(), ['user_id' => 'id']);
    }
    public function getUserprocess()
    {
        return $this->hasMany(UserProcess::className(), ['user_id' => 'id']);
    }
    public function getUserqualification()
    {
        return $this->hasMany(UserQualification::className(), ['user_id' => 'id']);
    }
    public function getUsercertification()
    {
        return $this->hasMany(UserCertification::className(), ['user_id' => 'id']);
    }
    public function getUserexperience()
    {
        return $this->hasMany(UserExperience::className(), ['user_id' => 'id']);
    }
    public function getUsertraining()
    {
        return $this->hasMany(UserTrainingInfo::className(), ['user_id' => 'id']);
    }

    public function getUserdeclaration()
    {
        return $this->hasMany(UserDeclaration::className(), ['user_id' => 'id']);
    }

    public function getUserbusinessgroup()
    {
        return $this->hasMany(UserBusinessGroup::className(), ['user_id' => 'id']);
    }
	
	public function getUsername()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
	
	/*
	public function getFranchise()
    {
        return $this->hasOne(User::className(), ['id' => 'franchise_id']);
    }
	*/
	
	public function hqOSS()
	{
		$userModel = User::find()->where(['headquarters' => 1,'user_type'=>3])->one();
		return $userModel;
    }
    public function ossnumberdetail($user_id)
    {
        $userModel = UserCompanyInfo::find()->where(['user_id' => $user_id])->one();
		return $userModel!==null ? 'OSS '.$userModel->osp_number:'';
    }
    
}
