<?php

class Event extends ApiController {
  protected $device = null;
  protected $event = null;

  public function __construct() {
    parent::__construct();

    $this->methodIn(function() {
      return $this->device = \M\Device::one('id = ?', Router::param('deviceId'));
    });

    $this->methodIn('permission', function() {
      return $this->event = \M\Event::one('id = ?', Router::param('id'));
    });
  }

  public function create() {
    $params = Input::ValidPost(function($params) use (&$device) {
      Validator::must($params, 'title', '標題')->isString(1, 190);
      $params['deviceId'] = $this->device->id;
      return $params;
    });

    transaction(function() use (&$params) {
      if (!$this->obj = \M\Event::create($params))
        return false;

      $this->obj->token = md5($this->obj->id . uniqid(mt_rand(), true));
      return $this->obj->save();
    });

    return [
      'id' => $this->obj->id,
      'title' => $this->obj->title,
      'token' => $this->obj->token
    ];
  }
  
  public function permission() {

    $params = Input::ValidPut(function($params) {
      Validator::must($params, 'permission', '權限')
               ->inEnum(array_keys(\M\Event::PERMISSION));
      return $params;
    });

    transaction(function() use (&$params) {
      return $this->event->setColumns($params) && $this->event->save();
    });
    
    return [
      'id' => $this->event->id,
      'permission' => $this->event->permission,
    ];
  }
}
