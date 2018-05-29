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
    if (!$event = Event::create (array ('name' => date ('Y-m-d H:i:s'))))
      return Output::json('新增失敗', 400);
    return Output::json(array ('id' => (int)$event->id, 'name' => $event->name));
  }
  public function create () {
    Load::sysFunc ('date.php');

    $validation = function(&$posts) {
      Validation::need ($posts, 'id', '事件 ID')->isNumber ()->doTrim ()->greater (0);

      if (!Event::find_by_id ($posts['id']))
        Validation::error ('事件錯誤！');

      $posts['points'] = array_values (array_filter (array_map (function ($point) use ($posts) {
        if (!isset ($point['_id'], $point['latitude'], $point['longitude'], $point['altitude'], $point['horizontal_accuracy'], $point['vertical_accuracy'], $point['speed'], $point['course'], $point['time'], $point['battery']))
          return null;

        if (!(is_numeric ($point['_id']) && $point['_id'] > 0))
          return null;

        if (!(is_numeric ($point['latitude']) && $point['latitude'] >= -90 && $point['latitude'] <= 90))
          return null;

        if (!(is_numeric ($point['longitude']) && $point['longitude'] >= -180 && $point['latitude'] <= 180))
          return null;

        if (!is_numeric ($point['altitude']))
          return null;

        if (!is_numeric ($point['horizontal_accuracy']))
          $point['horizontal_accuracy'] = -1;

        if (!is_numeric ($point['vertical_accuracy']))
          $point['vertical_accuracy'] = -1;

        if (!is_numeric ($point['speed']))
          $point['speed'] = -1;

        if (!(is_numeric ($point['course']) && $point['course'] >= 0 && $point['course'] <= 360))
          $point['course'] = -1;

        if (!(is_string($point['time']) && $point['time'] && is_datetime ($point['time'])))
            return null;
       
        if (!(is_numeric ($point['battery']) && $point['battery'] >= 0 && $point['battery'] <= 100))
          $point['battery'] = null;

        $point['event_id'] = $posts['id'];
        
        return $point;
      }, $posts['points'])));

      // $posts['points'] = array_map (function ($point) { return $point; }, $posts['points']);
    };

    $transaction = function($posts, &$ids) {
      $ids || $ids = array ();
      foreach ($posts['points'] as $point)
        if ($obj = Location::create ($point))
          array_push ($ids, (int)$point['_id']);

      return true;
    };

    $posts = Input::post ();

    if ($error = Validation::form ($validation, $posts))
      return Output::json($error, 400);
    
    if ($error = Location::getTransactionError ($transaction, $posts, $ids))
      return Output::json($error, 400);

    return Output::json(['ids' => $ids]);
  }
}
