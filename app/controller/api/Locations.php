<?php defined ('OACI') || exit ('此檔案不允許讀取。');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, OACI
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */


class Locations extends ApiController {
  
  public function __construct () {
    parent::__construct ();
  }

  public function key () {
    $validation = function(&$posts, &$device) {
      Validation::need ($posts, 'uuid', 'UUID')->isStringOrNumber ()->doTrim ()->length(1, 40);
      Validation::need ($posts, 'title', 'Title')->isStringOrNumber ()->doTrim ()->length(1, 190);

      if (!$device = Device::find ('one', array ('select' => 'id', 'where' => array ('uuid = ?', $posts['uuid']))))
        Validation::error ('找不到該 Device');

      $posts['device_id'] = $device->id;
      unset ($posts['uuid']);
    };

    $transaction = function($posts, $device, &$event) {
      return $event = Event::create ($posts);
    };

    $posts = Input::post ();

    if ($error = Validation::form ($validation, $posts, $device))
      return Output::json($error, 400);

    if ($error = Location::getTransactionError ($transaction, $posts, $device, $event))
      return Output::json($error, 400);

    return Output::json(array (
      'id' => (int)$event->id,
      'title' => $event->title,
      'device' => array (
          'name' => $event->device->name,
          'uuid' => $event->device->uuid,
        ),
    ));
  }

  public function create () {
    Load::sysFunc ('date.php');

    $validation = function(&$posts, &$event) {
      Validation::need ($posts, 'i', '事件 ID')->isNumber ()->doTrim ()->greater (0);

      if (!$event = Event::find_by_id ($posts['i']))
        Validation::error ('事件錯誤！');

      $posts['p'] = array_values (array_filter (array_map (function ($p) use ($posts) {
        if (!(isset ($p['i'], $p['a'], $p['n'], $p['d'], $p['h'], $p['v'], $p['s'], $p['c'], $p['t'], $p['b']) && is_numeric ($p['i']) && $p['i'] > 0 && is_numeric ($p['a']) && $p['a'] >= -90 && $p['a'] <= 90 && is_numeric ($p['n']) && $p['n'] >= -180 && $p['a'] <= 180 && is_numeric ($p['d']) && is_string($p['t']) && $p['t'] && is_datetime ($p['t'])))
          return null;

        is_numeric ($p['h']) || $p['h'] = -1;
        is_numeric ($p['v']) || $p['v'] = -1;
        is_numeric ($p['s']) || $p['s'] = -1;
        is_numeric ($p['c']) && $p['c'] >= 0 && $p['c'] <= 360 || $p['c'] = -1;
        is_numeric ($p['b']) && $p['b'] >= 0 && $p['b'] <= 100 || $p['b'] = null;

        $p = array (
          'event_id'            => $posts['i'],
          'latitude'            => $p['a'],
          'longitude'           => $p['n'],
          'altitude'            => $p['d'],
          'horizontal_accuracy' => $p['h'],
          'vertical_accuracy'   => $p['v'],
          'speed'               => $p['s'],
          'course'              => $p['c'],
          'time'                => $p['t'],
          'battery'             => $p['b'],
          'ori_id'              => $p['i'],
        );

        return $p;
      }, $posts['p'])));

      usort ($posts['p'], function ($a, $b) { return $a['ori_id'] > $b['ori_id']; });
    };

    $transaction = function($posts, &$ids, $event) {
      $ids || $ids = array ();
      foreach ($posts['p'] as $p)
        if ($obj = Location::create ($p))
          array_push ($ids, $obj->ori_id);

      return $event->createJson ();
    };

    $posts = Input::post ();

    if ($error = Validation::form ($validation, $posts, $event))
      return Output::json($error, 400);
    
    if ($error = Location::getTransactionError ($transaction, $posts, $ids, $event))
      return Output::json($error, 400);

    return Output::json(['ids' => $ids]);
  }
}
