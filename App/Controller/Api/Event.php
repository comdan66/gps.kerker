<?php

class Event extends ApiDeviceOptionalLoginController {
  protected $event = null;

  public function __construct() {
    parent::__construct();

    $this->methodIn('permissionLogin', 'permission', function() {
      $this->event = \M\Event::one('id = ?', Router::param('eventId'));
      $this->event || error('找不到此活動！');

      return true;
    });
  }

  public function createLogin() {
    $params = Input::ValidPost(function($params) use (&$device) {
      Validator::must($params, 'title', '標題')->isString(1, 190);
      Validator::optional($params, 'permission', '權限')->default(\M\Event::PERMISSION_LINK)->inEnum(array_keys(\M\Event::PERMISSION));

      $params['userId'] = \M\User::current()->id;
      $params['deviceId'] = \M\Device::current()->id;
      $params['status'] = \M\Event::STATUS_MOVING;
      $params['token'] = md5(\M\User::current()->id . \M\Device::current()->id . uniqid(mt_rand(), true));

      return $params;
    });

    transaction(function() use (&$params) {
      return $this->event = \M\Event::create($params);
    });

    return [
      'id' => $this->event->id,
      'title' => $this->event->title,
      'token' => $this->event->token,
      'permission' => $this->event->permission,
    ];
  }

  public function create() {
    $params = Input::ValidPost(function($params) use (&$device) {
      Validator::must($params, 'title', '標題')->isString(1, 190);
      Validator::optional($params, 'permission', '權限')->default(\M\Event::PERMISSION_LINK)->inEnum(array_keys(\M\Event::PERMISSION));

      $params['userId'] = 0;
      $params['deviceId'] = \M\Device::current()->id;
      $params['status'] = \M\Event::STATUS_MOVING;
      $params['token'] = md5(\M\Device::current()->id . uniqid(mt_rand(), true));

      return $params;
    });

    transaction(function() use (&$params) {
      return $this->event = \M\Event::create($params);
    });

    return [
      'id' => $this->event->id,
      'title' => $this->event->title,
      'token' => $this->event->token,
      'permission' => $this->event->permission,
    ];
  }

  public function permissionLogin() {
    $this->event->userId != 0 && $this->event->userId == \M\User::current()->id
      || error('您沒有權限編輯此活動！');
    
    return $this->_permission();
  }

  public function permission() {
    $this->event->userId == 0 && $this->event->deviceId == \M\Device()->id
      || error('您沒有權限編輯此活動！');
    
    return $this->_permission();
  }

  private function _permission() {
    $params = Input::ValidPut(function($params) {
      Validator::must($params, 'permission', '狀態')->inEnum(array_keys(\M\Event::PERMISSION));
      $this->event->permission = $params['permission'];
      return $params;
    });

    transaction(function() use (&$params) {
      return $this->event->save();
    });

    $data = $this->event->getData();

    return [
      'id' => $this->event->id,
      'permission' => $this->event->permission,
    ];
  }
}
