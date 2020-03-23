<?php

class Event extends F2eApiController {

  public function key() {
    return config('GoogleKey', 'f2eKeys');
  }

  public function show() {
    $event = \M\Event::one(Router::param('id'));
    $event || error('GG');

    return [
      'title' => $event->title,
      'title' => $event->title,
      'length' => $event->length,
      'signals' => array_map('\M\toArray', \M\Signal::all([
        'select' => 'lat,lng,speed,course',
        'where' => ['eventId = ? AND enable = ?', $event->id, \M\Signal::ENABLE_YES]]))
    ];
  }
}