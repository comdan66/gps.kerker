<?php

class Signal extends ApiController {
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
      Validator::must($params, 'timeAt', '裝置時間')->isNumber(0);
      
      Validator::optional($params, 'alt', '海拔高度')->default(null)->isNumber(-99999999.99, 99999999.99);
      Validator::optional($params, 'accH', '水平準度')->default(null)->isNumber(0, 99999999.99);
      Validator::optional($params, 'accV', '垂直準度')->default(null)->isNumber(0, 99999999.99);
      
      Validator::optional($params, 'speed', '速度')->default(null)->isNumber(0, 999.99);
      Validator::optional($params, 'course', '方向')->default(null)->isNumber(0, 999.99);

      Validator::optional($params, 'battery', '電量')->default(null)->isNumber(0, 100);
      Validator::optional($params, 'batteryStatus', '電源狀態')->default(null)->inEnum(array_keys(\M\Signal::BATTERY_STATUS));

      $memos = [];
      $params['memo'] = implode(', ', $memos);
      $params['enable'] = \M\Signal::ENABLE_YES;

      if (!$last = \M\Signal::last('deviceId = ? AND eventId = ? AND enable = ?', $device->id, $event->id, \M\Signal::ENABLE_YES))
        return $params;

      $params['enable'] == \M\Signal::ENABLE_YES
        && $last->lat == $params['lat']
        && $last->lng == $params['lng']
        && ($params['enable'] = \M\Signal::ENABLE_NO)
        && array_push($memos, '資料一樣');

      $params['enable'] == \M\Signal::ENABLE_YES
        && ($params['alt'] ?? \M\Signal::ALT + 1) > \M\Signal::ALT
        && ($params['enable'] = \M\Signal::ENABLE_NO)
        && array_push($memos, '準度太低');

      $params['enable'] == \M\Signal::ENABLE_NO
        && count(array_filter(\M\Signal::all(['select' => 'enable', 'order' => 'id desc', 'limit' => \M\Signal::RE_COUNT, 'where' => ['deviceId = ? AND eventId = ?', $device->id, $event->id]]), function($signal) { return $signal->enable == \M\Signal::ENABLE_NO; })) >= \M\Signal::RE_COUNT
        && ($params['enable'] = \M\Signal::ENABLE_YES)
        && array_push($memos, '強迫 enable = yes');

      $params['memo'] = implode(', ', $memos);

      return $params;
    });
    
    $length = 0;
    for ($i = 1, $s = $event->getSignals(), $c = count($s); $i < $c; $i++)
      $length += Tool::length($s[$i - 1]['lat'], $s[$i - 1]['lng'], $s[$i]['lat'], $s[$i]['lng']);

    $event->length = round($length, 2);

    transaction(function() use (&$params, &$obj, &$event) {
      $obj = \M\Signal::create($params);
      return $obj && $event->save();
    });

    return [
      'id' => $obj->id,
      'lat' => $obj->lat,
      'lng' => $obj->lng
    ];
  }
}
