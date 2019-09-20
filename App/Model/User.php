<?php

namespace M;

class User extends Model {
  private static $current;
  
  const LEVEL_LEVEL1 = 'level1';
  const LEVEL_LEVEL2 = 'level2';
  const LEVEL_LEVEL3 = 'level3';

  const LEVEL = [
    self::LEVEL_LEVEL1 => '普通', 
    self::LEVEL_LEVEL2 => '付費',
    self::LEVEL_LEVEL3 => '專業',
  ];

  const LOGIN_TYPE_GENERAL = 'general';
  const LOGIN_TYPE_FACEBOOK = 'facebook';

  const LOGIN_TYPE = [
    self::LOGIN_TYPE_GENERAL => '帳密', 
    self::LOGIN_TYPE_FACEBOOK  => '臉書',
  ];

  const ACTIVE_YES = 'yes';
  const ACTIVE_NO = 'no';

  const ACTIVE = [
    self::ACTIVE_YES => '已驗證',
    self::ACTIVE_NO  => '未驗證',
  ];

  const JWT_KEYS = ['id', 'name', 'token'];

  static $relations = [
    'device' => '-> Device'
  ];

  // public function inDeviceCountRange() {
  //   switch ($this->role) {
  //     case self::ROLE_MONTH500:
  //       return count($this->activeDevices) < 3;
  //       break;
      
  //     default:
  //       return count($this->activeDevices) == 1;
  //       break;
  //   }
  // }

  public static function current($jwt = null) {
    if ($jwt === null)
      return self::$current;

    if (!\M\User::jwtDataCheck($jwt))
      return self::$current = null;

    if (!self::$current = self::one($jwt['id']))
      return self::$current = null;

    if (self::$current->token != $jwt['token'])
      return self::$current = null;

    return self::$current;
  }

  public static function jwtDataCheck($data) {
    if (!is_array($data))
      return false;

    foreach (self::JWT_KEYS as $key)
      if (!array_key_exists($key, $data))
        return false;

    return true;
  }

  public function jwtData() {
    $data = [];
    foreach (self::JWT_KEYS as $key)
      isset($this->$key)
        && $data[$key] = $this->$key;

    if (!self::jwtDataCheck($data))
      return null;

    return $data;
  }

  public function newToken() {
    $this->token = md5(uniqid(mt_rand(), true));
    return $this->save();
  }
}

User::imageUploader('avatar')
    ->default()
    ->version('w100', ['resize', 100, 100, 'width'])
    ->version('c120x120', ['adaptiveResizeQuadrant', 120, 120, 'c']);
