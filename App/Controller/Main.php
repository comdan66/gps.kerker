<?php

class Main extends Controller {

  public function index() {
    return \M\Signal::createBy(Input::get() ?? [])
      ? 'ok'
      : 'no';
  }

  public function cover() {
    $eventId = 1;
    $signals = \M\Signala::all();

    foreach ($signals as $signal) {
      $data = json_decode($signal->memo, true);
      
      $last = \M\Signal::last('eventId = ? AND enable = ?', $eventId, \M\Signal::ENABLE_YES);
      $memo = $last && $last->lat === $signal->lat && $last->lng === $signal->lng ? '資料一樣' : '';

      \M\Signal::create([
        'eventId'     => $eventId,
        'lat'         => $signal->lat,
        'lng'         => $signal->lng,
        'speed'       => round($data['speed'] * 1.852, 2),
        'course'      => round(0 + $data['course'], 1),
        'timeAt'      => $data['datetime'],
        'declination' => $data['declination'],
        'mode'        => $data['mode'],
        'param'       => $signal->memo,
        'memo'        => $memo,
        'enable'      => $memo
          ? \M\Signal::ENABLE_NO
          : \M\Signal::ENABLE_YES,
      ]);
    }
  }

  public function map() {
    if (!$event = \M\Event::one(Router::param('id')))
      return 'GG';

    return [
      'title' => $event->title,
      'length' => $event->length,
      'signals' => array_map('\M\toArray', \M\Signal::all([
        'select' => 'lat,lng,speed,course',
        'where' => ['eventId = ? AND enable = ?', $event->id, \M\Signal::ENABLE_YES]]))
    ];
  }
}