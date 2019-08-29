<?php

namespace M;

class Event extends Model {
  static $relations = [
  ];

  public static function length($aa, $an, $ba, $bn) {
    $aa = deg2rad($aa);
    $bb = deg2rad($an);
    $cc = deg2rad($ba);
    $dd = deg2rad($bn);
    return (2 * asin(sqrt(pow(sin(($aa - $cc) / 2), 2) + cos($aa) * cos($cc) * pow(sin(($bb - $dd) / 2), 2)))) * 6378137;
  }
  
  public function getSignals() {
    $ids = array_column(\M\Signal::all([
      'select' => 'id',
      'order' => 'timeAt DESC',
      'where' => ['deviceId = ? AND eventId = ? AND enable = ?', $this->deviceId, $this->id, \M\Signal::ENABLE_YES]
    ]), 'id');

    \Load::lib('Tool');

    if (!$ids = \Tool::points($ids))
      return [];

    return array_map(function($signal) {
      return [
        'id' => $signal->id,
        'lat' => $signal->lat,
        'lng' => $signal->lng,
        'speed' => round($signal->speed * 3.6),
        'course' => round($signal->course / 10),
      ];
    }, \M\Signal::all([
      'select' => 'id, lat, lng, speed, course',
      'order' => 'timeAt DESC',
      'where' => ['id IN (?)', $ids]]));
  }

  public function putSignals() {
    $signals = $this->getSignals();
    return \Tool::put2S3(json_encode($signals), $this->token . '.json', $this->token . '.json');
  }
}
