<?php
namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class UserDataComponent extends Component
{ 
    public function getData()
	{
		$jwt = Yii::$app->jwt;		
		$token = $jwt->getToken();
		$user_id = $token->getClaim('uid');	
		$user_role_id = $token->getClaim('user_role_id');			
		$email = $token->getClaim('email');
		$displayname = $token->getClaim('displayname');
		$user_type = $token->getClaim('user_type');
		$role = $token->getClaim('role');
		$rules = $token->getClaim('rules');
		$franchiseid = $token->getClaim('franchiseid');
		$resource_access = $token->getClaim('resource_access');
		$is_headquarters = $token->getClaim('is_headquarters');
		$role_chkid = $token->getClaim('roleid');
		return ['role_chkid'=>$role_chkid,'is_headquarters'=>$is_headquarters, 'franchiseid'=>$franchiseid,'userid'=>$user_id,'user_role_id'=>$user_role_id,'email'=>$email,'displayname'=>$displayname,'user_type'=>$user_type,'role'=>$role,'rules'=> $rules,'resource_access'=>$resource_access];
	}
}    
?>