<?php

class Event extends ApiController {

  public function create() {

    $params = Input::ValidPost(function($params) use (&$device) {
      Validator::must($params, 'deviceId', '裝置 ID')->isId();
      $device = \M\Device::one('id = ?', $params['deviceId']);
      $device || error('找不到正確的裝置');

      Validator::must($params, 'title', '標題')->isString(1, 190);

      return $params;
    });

    transaction(function() use (&$params, &$obj) {
      if (!$obj = \M\Event::create($params))
        return false;

      $obj->token = md5($obj->id . uniqid(mt_rand(), true));
      return $obj->save();
    });

    return [
      'id' => $obj->id,
      'title' => $obj->title,
      'token' => $obj->token
    ];
  }
}
