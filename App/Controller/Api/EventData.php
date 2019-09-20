<?php

class EventData extends ApiDeviceOptionalLoginController {
  protected $event = null;
  
  public function __construct() {
    parent::__construct();

    $this->event = \M\Event::one('id = ?', Router::param('eventId'));
    $this->event->deviceId == \M\Device::current()->id || error('此活動不是由此裝置新增的！');
  }

  public function createLogin() {
    return $this->create();
  }

  public function create() {

    $params = Input::ValidPost(function($params) {
      in_array($this->event->status, \M\Event::SIGNAL_ALLOW_STATUS)
        || error('此活動不可被繼續新增訊號！');

      $batteryStatus = array_keys(\M\Signal::BATTERY_STATUS);

      Validator::optional($params, 'signals', '訊號')->default([])->isArray(0)->map(function($signal) use ($batteryStatus) {

        if (!(isset($signal['lat']) && is_numeric($signal['lat']) && $signal['lat'] >= -90 && $signal['lat'] <= 90)) return null;
        if (!(isset($signal['lng']) && is_numeric($signal['lng']) && $signal['lng'] >= -180 && $signal['lng'] <= 180)) return null;
        if (!(isset($signal['timeAt']) && is_numeric($signal['timeAt']) && $signal['timeAt'] >= 0)) return null;

        $signal['deviceId'] = \M\Device::current()->id;
        $signal['eventId']  = $this->event->id;

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
        $signal['batteryStatus'] = in_array($signal['batteryStatus'], $batteryStatus, true) ? $signal['batteryStatus'] : null;

        $signal['memo'] = mb_substr($signal['memo'] ?? '', 0, 190);
        $signal['enable'] = $signal['enable'] ?? \M\Signal::ENABLE_YES;
        $signal['enable'] = in_array($signal['enable'], array_keys(\M\Signal::ENABLE)) ? $signal['enable'] : \M\Signal::ENABLE_YES;

        return $signal;
      })->filter()->isArray(0);

      usort($params['signals'], function($a, $b) { return $a['timeAt'] > $b['timeAt']; });
      $params['signals'] = array_values($params['signals']);

      return $params;
    });

    transaction(function() use (&$params) {
      if ($result = array_filter(array_map('\M\Signal::create', $params['signals']), function($t) { return !$t; }))
        return false;

      return $this->event->putSignals(\M\Event::STATUS_MOVING, $params);
    });

    return $params;
  }

  public function statusLogin() {
    return $this->status();
  }

  public function status() {
    $params = Input::ValidPut(function($params) {
      Validator::must($params, 'status', '狀態')->inEnum([\M\Event::STATUS_PAUSES, \M\Event::STATUS_FINISHED]);
      $this->event->status = $params['status'];
      return $params;
    });

    transaction(function() use (&$params) {
      return $this->event->save();
    });

    $data = $this->event->getData();

    return [
      'id' => $this->event->id,
      'length' => $data['length'],
      'elapsed' => $data['elapsed'],
      'updateAt' => $data['updateAt'],
    ];
  }

}