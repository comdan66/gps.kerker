<?php

abstract class ApiDeviceOptionalLoginController extends ApiDeviceController {

  public function __construct() {
    parent::__construct();

    if (!$auth = Input::requestHeader('Authorization'))
      return;
    
    if (count($auth = explode(' ', $auth, 2)) != 2)
      return;

    list($bearer, $jwt) = $auth;

    if ($bearer !== 'Bearer')
      return;

    Load::lib('JWT');

    \M\User::current(JWT::decode($jwt, config('JWT', 'key')));

    if (!\M\User::current())
      return;

    Router::$methodName = Router::$methodName . 'Login';

    \M\User::current()->deviceId == \M\Device::current()->id
      || error(499, '資訊錯誤，請先登入！');
  }
}