<?php defined ('OACI') || exit ('此檔案不允許讀取。');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, OACI
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

class Main extends SiteController {

  public function a () {
    $posts = Input::post ();
  }

  public static function length ($aa, $an, $ba, $bn) {
    $aa = deg2rad ($aa); $bb = deg2rad ($an); $cc = deg2rad ($ba); $dd = deg2rad ($bn);
    return (2 * asin (sqrt (pow (sin (($aa - $cc) / 2), 2) + cos ($aa) * cos ($cc) * pow (sin(($bb - $dd) / 2), 2)))) * 6378137;
  }
  public function path ($id) {
    $ids = array ();
    
    $last = Location::find ('one', array ('select' => 'id, created_at', 'order' => 'id DESC', 'where' => array ('event_id = ?', $id)));

    $n1 = Location::getArray ('id', array ('where' => array ('event_id IN (?) AND speed >= ? AND horizontal_accuracy < ?', $id, 0, 50)));

    for ($i = 0, $c = count ($n1), $u = ($c - 100) / 400; $i < $c; $i += $i < 100 ? 1 : $u)
      if ($m = array_slice ($n1, $i, 1))
        array_push ($ids, $m);
    
    $ids || $ids = array ($last ? $last->id : 0);

    $objs = Location::find ('all', array ('select' => 'id, latitude, longitude, altitude, horizontal_accuracy, vertical_accuracy, speed, course, time, battery', 'where' => array ('id IN (?)', $ids)));

    $objs = array_map (function ($l) {
      return array (
          $l->id,                                       // 'i' 0
          $l->latitude,                                 // 'a' 1
          $l->longitude,                                // 'n' 2
          $l->altitude,                                 // 'd' 3
          $l->horizontal_accuracy,                      // 'h' 4
          $l->vertical_accuracy,                        // 'v' 5
          ceil ($l->speed * 3.6),                       // 's' 6
          $l->course,                                   // 'c' 7
          strtotime ($l->time->format ('Y-m-d H:i:s')), // 't' 8
          $l->battery,                                  // 'b' 9
        );
    }, $objs);

    $distanceFilter = 20;

    $nobjs = array ();
      for ($i = 0, $c = count ($objs); $i < $c && array_push ($nobjs, $objs[$i]); $i++)
        for ($j = $i + 1, $t = $i, $d = 0; $j < $c && $d < 10; $j++, $d++)
          if (self::length ($objs[$t][1], $objs[$t][2], $objs[$j][1], $objs[$j][2]) <= ($objs[$j][6] > 10 ? $objs[$j][6] > 20 ? $objs[$j][6] > 30 ? 0 + 5 : 10 + 5 : 20 + 5 : 30 + 5))
            $i = $j;
          else
            break;

    $l = 0;
    for ($i = 0, $c = count ($nobjs); $i < $c; $i++)
      if ($i + 1 < $c)
        $l += self::length ($nobjs[$i][1], $nobjs[$i][2], $nobjs[$i + 1][1], $nobjs[$i + 1][2]);

    $d = '';
    if ($nobjs) {
      $x = $nobjs[count($nobjs) - 1][8] - $nobjs[0][8];
      $h = floor ($x / 3600);
      $m = floor (($x - ($h * 3600)) / 60);
      $s = $x - ($h * 3600) - ($m * 60);
      $h && $d .= $h . '小時 ';
      $m && $d .= $m . '分 ';
      $s && $d .= $s . '秒 ';
    }


    $p = array_2d_to_1d ($nobjs);
    $t = $last ? strtotime ($last->created_at->format ('Y-m-d H:i:s')) : 0;

    return Output::json (array (
        'm' => md5 (implode ('', $p)),
        'd' => $d,
        'l' => $l,
        't' => $t,
        'p' => $p,
      ));
  }
  public function index ($id) {
    $this->asset->addCSS ('/assets/css/site/Main/index.css')
                ->addJs ('/assets/js/site/Main/index.js');

    return $this->view->setPath('site/Main/index.php')
                      ->with ('id', $id);
  }
}
