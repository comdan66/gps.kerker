<?php

class Stop extends ApiController {
  public function create() {
    Load::lib('Tool');

    $params = Input::ValidPost(function($params) use (&$event) {
      Validator::must($params, 'deviceId', '裝置 ID')->isId();
      $device = \M\Device::one('id = ?', $params['deviceId']);
      $device || error('找不到正確的裝置！');

      Validator::must($params, 'eventId', '活動 ID')->isId();
      $event = \M\Event::one('id = ?', $params['eventId']);
      $event || error('找不到正確的活動！');

      Validator::must($params, 'lat', '緯度')->isLat();
      Validator::must($params, 'lng', '經度')->isLng();
      Validator::must($params, 'startAt', '開始時間')->isNumber(0);
      Validator::must($params, 'endAt',   '結束時間')->isNumber(0);
      Validator::must($params, 'elapsed', '停留多久')->isNumber(0);

      $params['endAt'] > $params['startAt'] || error('開始、結束時間有誤！');
      $params['elapsed'] == $params['endAt'] - $params['startAt'] || error('停留多久與開始、結束時間不符！');

      $params['startAt'] = date('Y-m-d H:i:s', $params['startAt']);
      $params['endAt'] = date('Y-m-d H:i:s', $params['endAt']);

      Validator::optional($params, 'title', '標題')->default('')->isString(1, 190);
      return $params;
    });

    transaction(function() use (&$params, &$obj) {
      return $obj = \M\Stop::create($params);
    });

    return [
      'id' => $obj->id
    ];
  }
}
