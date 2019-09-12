<?php

class Device extends ApiController {
  
  public function create() {

    $params = Input::ValidPost(function($params) {
      Validator::must($params, 'name', '名稱')->isString(1, 190);
      Validator::must($params, 'uuid', 'UUID')->isString(1, 40);
      return $params;
    });

    transaction(function() use (&$params, &$obj) {
      if ($obj = \M\Device::one('uuid = ?', $params['uuid']))
        return $obj;
      else
        return $obj = \M\Device::create($params);
    });

    return [
      'id' => $obj->id,
      'name' => $obj->name,
      'uuid' => $obj->uuid
    ];
  }
}
