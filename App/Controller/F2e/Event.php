<?php

class Event extends F2eApiController {

  public function key() {
    return config('GoogleKey', 'f2eKeys');
  }

  public function show() {
    Load::lib('Code');

    $event = \M\Event::one('id = ?', Code::decode(Router::param('code')));
    $event || error('您沒有權限查看喔！');

    return [
      'title' => $event->title,
      'live' => $event->status != \M\Event::STATUS_FINISH,
      'status' => $event->status,
      'length' => $event->length,
      'elapsed' => $event->elapsed,
      'updateAt' => strtotime($event->lastSignal ? $event->lastSignal->createAt : $event->updateAt),
      'signals' => array_map('\M\toArray', \M\Signal::all([
        'select' => 'lat,lng,speed,course',
        'where' => ['eventId = ? AND enable = ?', $event->id, \M\Signal::ENABLE_YES]]))
    ];
  }
}