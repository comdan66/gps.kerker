<?php

class Signal extends ApiController {
  public function _create() {
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

    $event->putSignals();

    return [
      'id' => $obj->id,
      'lat' => $obj->lat,
      'lng' => $obj->lng
    ];
  }

  public function create() {
    Load::lib('Tool');

    $params = Input::ValidPost(function($params) use (&$event) {
      Validator::must($params, 'deviceId', '裝置 ID')->isId();
      $device = \M\Device::one('id = ?', $params['deviceId']);
      $device || error('找不到正確的裝置！');

      Validator::must($params, 'eventId', '活動 ID')->isId();
      $event = \M\Event::one('id = ?', $params['eventId']);
      $event || error('找不到正確的活動！');

      $status = array_keys(\M\Signal::BATTERY_STATUS);

      Validator::optional($params, 'signals', '訊號')->default([])->isArray(0)->map(function($signal) use ($device, $event, $status) {

        if (!(isset($signal['lat']) && is_numeric($signal['lat']) && $signal['lat'] >= -90 && $signal['lat'] <= 90)) return null;
        if (!(isset($signal['lng']) && is_numeric($signal['lng']) && $signal['lng'] >= -180 && $signal['lng'] <= 180)) return null;
        if (!(isset($signal['timeAt']) && is_numeric($signal['timeAt']) && $signal['timeAt'] >= 0)) return null;

        $signal['deviceId'] = $device->id;
        $signal['eventId']  = $event->id;

        $signal['lat']    = 0 + $signal['lat'];
        $signal['lng']    = 0 + $signal['lng'];
        $signal['timeAt'] = 0 + $signal['timeAt'];
        
        $signal['alt']     = is_numeric($signal['alt'] ?? null) && $signal['alt'] >= -99999999.99 && $signal['alt'] <= 99999999.99 ? 0 + $signal['alt'] : null;
        $signal['accH']    = is_numeric($signal['accH'] ?? null) && $signal['accH'] >= 0 && $signal['accH'] <= 99999999.99 ? 0 + $signal['accH'] : null;
        $signal['accV']    = is_numeric($signal['accV'] ?? null) && $signal['accV'] >= 0 && $signal['accV'] <= 99999999.99 ? 0 + $signal['accV'] : null;
        $signal['speed']   = is_numeric($signal['speed'] ?? null) && $signal['speed'] >= 0 && $signal['speed'] <= 999.99 ? 0 + $signal['speed'] : null;
        $signal['course']  = is_numeric($signal['course'] ?? null) && $signal['course'] >= 0 && $signal['course'] <= 999.99 ? 0 + $signal['course'] : null;
        $signal['battery'] = is_numeric($signal['battery'] ?? null) && $signal['battery'] >= 0 && $signal['battery'] <= 100 ? 0 + $signal['battery'] : null;

        $signal['batteryStatus'] = is_string($signal['batteryStatus'] ?? null) ? trim($signal['batteryStatus']) : null;
        $signal['batteryStatus'] = in_array($signal['batteryStatus'], $status, true) ? $signal['batteryStatus'] : null;

        $signal['memo']   = '';
        $signal['enable'] = \M\Signal::ENABLE_YES;

        return $signal;
      })->filter()->isArray(0);

      usort($params['signals'], function($a, $b) { return $a['timeAt'] > $b['timeAt']; });
      $params['signals'] = array_values($params['signals']);

      $last = \M\Signal::last(['order' => 'timeAt, id DESC', 'where' => ['deviceId = ? AND eventId = ? AND enable = ?', $device->id, $event->id, \M\Signal::ENABLE_YES]]);

      for ($i = 0, $c = count($params['signals']); $i < $c; $i++) {
        $memos = [];
        
        $params['signals'][$i]['enable'] == \M\Signal::ENABLE_YES
          && ($i
            ? $params['signals'][$i - 1]['lat'] == $params['signals'][$i]['lat'] && $params['signals'][$i - 1]['lng'] == $params['signals'][$i]['lng']
            : $last && $last->lat == $params['signals'][$i]['lat'] && $last->lng == $params['signals'][$i]['lng'])
          && ($params['signals'][$i]['enable'] = \M\Signal::ENABLE_NO)
          && array_push($memos, '資料一樣');

        $params['signals'][$i]['enable'] == \M\Signal::ENABLE_YES
          && ($params['signals'][$i]['alt'] ?? \M\Signal::ALT + 1) > \M\Signal::ALT
          && ($params['signals'][$i]['enable'] = \M\Signal::ENABLE_NO)
          && array_push($memos, '準度太低(小於 ' . \M\Signal::ALT . ' 公尺)');

        $params['signals'][$i]['memo'] = implode(', ', $memos);
      }

      return $params;
    });

    $result = array_filter(array_map('\M\Signal::create', $params['signals']), function($t) { return !$t; });

    $event->putSignals();

    return 'Ok';
  }
}
