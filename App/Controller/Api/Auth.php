<?php

class Auth extends ApiDeviceController {
  protected $user = null;

  public function logout() {
    $auth = Input::requestHeader('Authorization');
    $auth || error('請先登入！');
    
    count($auth = explode(' ', $auth, 2)) == 2 || error('請先登入！');

    list($bearer, $jwt) = $auth;

    $bearer === 'Bearer' || error('請先登入！');

    Load::lib('JWT');

    \M\User::current(JWT::decode($jwt, config('JWT', 'key')));
    \M\User::current() || error('請先登入！');
    \M\User::current()->deviceId = 0;

    transaction(function() {
      return \M\User::current()->save();
    });

    return ['OK'];
  }

  public function login() {
    $params = Input::ValidPost(function($params) {
      Validator::must($params, 'email', '帳號')->isEmail();
      Validator::must($params, 'password', '密碼')->isString(1, 190);
      Validator::optional($params, 'deviceName', '裝置名稱')->default(\M\Device::current()->uuid)->isString(1, 190);

      $this->user = \M\User::one('email = ? AND loginType = ?', $params['email'], \M\User::LOGIN_TYPE_GENERAL);
      $this->user || error('此使用者不存在！');

      password_verify($params['password'], $this->user->password) || error('密碼錯誤！');
      $this->user->active == \M\User::ACTIVE_YES || error('此信箱尚未驗證！');

      unset($params['email'], $params['password']);

      $this->user->device && error('請先登出其他裝置！');

      return $params;
    });

    transaction(function() use (&$data, &$params) {
      if (!$this->user->newToken())
        return false;

      if (!$data = $this->user->jwtData())
        return false;

      $this->user->deviceId = \M\Device::current()->id;
      \M\Device::current()->name = $params['deviceName'];
      return $this->user->save() && \M\Device::current()->save();
    });

    Load::lib('JWT.php');
    $jwt = JWT::encode($data, config('JWT', 'key'), JWT::HS256);

    return [
      'device' => [
        'id' => \M\Device::current()->id,
        'uuid' => \M\Device::current()->uuid
      ],
      'token' => $jwt
    ];
  }

}
