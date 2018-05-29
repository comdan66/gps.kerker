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

  public function create () {
    Load::sysFunc ('date.php');

    $validation = function(&$posts) {
      $posts['points'] = array_values (array_filter (array_map (function ($point) {
        if (!isset ($point['id'], $point['latitude'], $point['longitude'], $point['altitude'], $point['horizontal_accuracy'], $point['vertical_accuracy'], $point['speed'], $point['course'], $point['time'], $point['battery']))
          return null;

        if (!(is_numeric ($point['id']) && $point['id'] > 0))
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
          return null;
          
        if (!(is_string($point['time']) && $point['time'] && is_datetime ($point['time'])))
            return null;
       
        if (!(is_numeric ($point['battery']) && $point['battery'] >= 0 && $point['battery'] <= 100))
          $point['battery'] = null;

        return $point;
      }, $posts['points'])));

      usort ($posts['points'], function ($a, $b) {
        return $a['id'] > $b['id'];
      });

      $posts['points'] = array_map (function ($point) { unset ($point['id']); return $point; }, $posts['points']);
    };

    $transaction = function($posts, &$ids) {
      $ids || $ids = array ();
      foreach ($posts['points'] as $point)
        if ($obj = Location::create ($point))
          array_push ($ids, $obj->id);

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
