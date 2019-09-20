<?php

namespace M;

class Event extends Model {

  const STATUS_MOVING = 'moving';
  const STATUS_PAUSES = 'pauses';
  const STATUS_APP_CRASH = 'appCrash';
  const STATUS_USER_CHANGE = 'userChange';
  const STATUS_FINISHED = 'finished';

  const STATUS = [
    self::STATUS_MOVING => '移動中', 
    self::STATUS_PAUSES => '暫停中', 
    self::STATUS_APP_CRASH  => '斷線了',
    self::STATUS_USER_CHANGE  => '更換使用者',
    self::STATUS_FINISHED  => '已完成',
  ];

  const SIGNAL_ALLOW_STATUS = [
    self::STATUS_MOVING,
    self::STATUS_PAUSES,
    self::STATUS_APP_CRASH,
  ];

  const PERMISSION_PUBLIC = 'public';
  const PERMISSION_LINK = 'link';
  const PERMISSION_FRIENDS = 'friends';
  const PERMISSION_PRIVATE = 'private';
  const PERMISSION = [
    self::PERMISSION_PUBLIC => '公開', 
    self::PERMISSION_LINK  => '鏈結',
    self::PERMISSION_FRIENDS  => '好友',
    self::PERMISSION_PRIVATE  => '個人',
  ];


  private function getSignals() {
    $ids = array_column(\M\Signal::all([
      'select' => 'id',
      'order' => 'timeAt DESC, id DESC',
      'where' => ['eventId = ? AND enable = ?', $this->id, \M\Signal::ENABLE_YES]
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

  private static function getSpeeds($signals) {
    $cnt = 10;
    $tmps = array_column($signals, 'speed');
    $min = $tmps ? min($tmps) : 0;
    $max = $tmps ? max($tmps) : 0;
    $unit = round(($max - $min + 1) / $cnt);
    $speeds = [$min];
    for ($i = 1; $i <= $cnt - 2; $i++)
      if ($min + $i * $unit < $max)
        array_push($speeds, $min + $i * $unit);
      else
        break;

    $min == $max || array_push($speeds, $max);

    return $speeds;
  }

  public function getData() {
    $signals = $this->getSignals();

    \Load::lib('Tool');
    $length = 0;
    for ($i = 1, $signals, $c = count($signals); $i < $c; $i++)
      $length += \Tool::length($signals[$i - 1]['lat'], $signals[$i - 1]['lng'], $signals[$i]['lat'], $signals[$i]['lng']);

    $length = round($length / 1000, 2);

    $first = \M\Signal::first(['select' => 'timeAt', 'order' => 'timeAt, id DESC', 'where' => ['eventId = ? AND enable = ?', $this->id, \M\Signal::ENABLE_YES]]);
    $last  = \M\Signal::last(['select' => 'timeAt', 'order' => 'timeAt, id DESC', 'where' => ['eventId = ? AND enable = ?', $this->id, \M\Signal::ENABLE_YES]]);

    $elapsed = $first && $last ? $last->timeAt - $first->timeAt : 0;

    $speeds = self::getSpeeds($signals);

    $signals = array_map(function($signal) use ($speeds) {
      foreach ($speeds as $i => $speed)
        if ($signal['speed'] <= $speed)
          break;
      $signal['speed'] = $i + 1;
      return $signal;
    }, $signals);

    $stops = array_map(function($stop) {
      return [
        'id' => $stop->id,
        'lat' => $stop->lat,
        'lng' => $stop->lng,
        'startAt' => strtotime($stop->startAt->format('Y-m-d H:i:s')),
        'endAt' => strtotime($stop->endAt->format('Y-m-d H:i:s')),
        'elapsed' => $stop->elapsed,
      ];
    }, \M\Stop::all([
      'order' => 'id DESC',
      'where' => ['eventId = ?', $this->id]
    ]));

    return [
      'title' => $this->title,
      'length' => $this->length,
      'elapsed' => $this->elapsed,
      'updateAt' => strtotime($this->updateAt->format('Y-m-d H:i:s')),
      'speeds' => $speeds,
      'signals' => $signals,
      'stops' => $stops
    ];
  }

  public function putSignals($status, &$data = null) {
    $data = $this->getData();
    $this->length = $data['length'];
    $this->elapsed = $data['elapsed'];
    $this->status = $status;

    if (!$this->save())
      return flse;

    $data['status'] = $this->status;

    \Load::lib('Tool');
    if (\Tool::put2S3(json_encode($data), $this->token . '.json', $this->token . '.json'))
      return flase;

    return true;
  }
}
