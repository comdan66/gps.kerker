<?php

namespace M;

class Event extends Model {

  const ENABLE_YES = 'yes';
  const ENABLE_NO = 'no';
  const ENABLE = [
    self::ENABLE_YES => '啟用', 
    self::ENABLE_NO  => '停用',
  ];

  private function getSignals() {
    $ids = array_column(\M\Signal::all([
      'select' => 'id',
      'order' => 'timeAt DESC, id DESC',
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
        'course' => round($signal->course / 10)];
    }, \M\Signal::all([
      'select' => 'id, lat, lng, speed, course',
      'order' => 'timeAt DESC, id DESC',
      'where' => ['id IN (?)', $ids]]));
  }

  public function putSignals($disable = false) {
    \Load::lib('Tool');

    $signals = $this->getSignals();

    $length = 0;
    for ($i = 1, $signals, $c = count($signals); $i < $c; $i++)
      $length += \Tool::length($signals[$i - 1]['lat'], $signals[$i - 1]['lng'], $signals[$i]['lat'], $signals[$i]['lng']);

    $first = \M\Signal::first(['select' => 'timeAt', 'order' => 'timeAt, id DESC', 'where' => ['deviceId = ? AND eventId = ? AND enable = ?', $this->deviceId, $this->id, \M\Signal::ENABLE_YES]]);
    $last  = \M\Signal::last(['select' => 'timeAt', 'order' => 'timeAt, id DESC', 'where' => ['deviceId = ? AND eventId = ? AND enable = ?', $this->deviceId, $this->id, \M\Signal::ENABLE_YES]]);

    if ($disable)
      $this->enable = \M\Event::ENABLE_NO;
    else
      $this->enable != \M\Event::ENABLE_YES && $this->enable = \M\Event::ENABLE_YES;

    $this->length = round($length / 1000, 2);
    $this->elapsed = $first && $last ? $last->timeAt - $first->timeAt : 0;
    $this->save();

    $cnt = 10;
    $tmps = array_column($signals, 'speed');
    $min = min($tmps);
    $max = max($tmps);
    $unit = round(($max - $min + 1) / $cnt);
    $speeds = [$min];
    for ($i = 1; $i <= $cnt - 2; $i++)
      if ($min + $i * $unit < $max)
        array_push($speeds, $min + $i * $unit);
      else
        break;

    $min == $max || array_push($speeds, $max);

    $signals = array_map(function($signal) use ($speeds) {
      foreach ($speeds as $i => $speed)
        if ($signal['speed'] <= $speed)
          break;
      $signal['speed'] = $i + 1;
      return $signal;
    }, $signals);

    return \Tool::put2S3(json_encode([
      'title' => $this->title,
      'length' => $this->length,
      'enable' => $this->enable == \M\Event::ENABLE_YES,
      'elapsed' => $this->elapsed,
      'updateAt' => strtotime($this->updateAt->format('Y-m-d H:i:s')),
      'speeds' => $speeds,
      'signals' => $signals,
    ]), $this->token . '.json', $this->token . '.json');
  }
}
