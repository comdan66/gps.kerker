<?php defined ('OACI') || exit ('此檔案不允許讀取。');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, OACI
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

class Event extends Model {
  static $table_name = 'events';

  static $has_one = array (
  );

  static $has_many = array (
  );

  static $belongs_to = array (
    array ('device',  'class_name' => 'Device'),
  );

  public function __construct ($attrs = array (), $guardAttrs = true, $instantiatingViafind = false, $newRecord = true) {
    parent::__construct ($attrs, $guardAttrs, $instantiatingViafind, $newRecord);
  }

  public static function time ($num) {
    $d = '';
    $h = floor ($num / 3600);
    $m = floor (($num - ($h * 3600)) / 60);
    $s = $num - ($h * 3600) - ($m * 60);

    $h && $d .= $h . '小時';
    $m && $d .= ($d ? ' ' : '') . $m . '分';
    $s && $d .= ($d ? ' ' : '') . $s . '秒';

    return $d;
  }
  public static function length ($aa, $an, $ba, $bn) {
    $aa = deg2rad ($aa); $bb = deg2rad ($an); $cc = deg2rad ($ba); $dd = deg2rad ($bn);
    return (2 * asin (sqrt (pow (sin (($aa - $cc) / 2), 2) + cos ($aa) * cos ($cc) * pow (sin(($bb - $dd) / 2), 2)))) * 6378137;
  }
  public function createJson () {
    $distanceFilter = 20;
    $accuracyFilter = 50;
    $count = 500;
    $path = FCPATH . 'json' . DIRECTORY_SEPARATOR . $this->id . '.json';

    $ids = array ();
    
    $last = Location::find ('one', array ('select' => 'id, battery, created_at', 'order' => 'id DESC', 'where' => array ('event_id = ?', $this->id)));
    $tmps = Location::getArray ('id', array ('where' => array ('event_id = ? AND speed >= ? AND horizontal_accuracy < ?', $this->id, 0, $accuracyFilter)));

    for ($i = 0, $c = count ($tmps), $u = ($c - 100) / ($count - 100); $i < $c; $i += $i < 100 ? 1 : $u)
      if ($m = array_slice ($tmps, $i, 1))
        array_push ($ids, $m);
    
    $ids || $ids = array ($last ? $last->id : 0);

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
    }, Location::find ('all', array ('select' => 'id, latitude, longitude, altitude, horizontal_accuracy, vertical_accuracy, speed, course, time, battery', 'where' => array ('id IN (?)', $ids))));

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

    $d = $nobjs ? self::time ($x = $nobjs[count($nobjs) - 1][8] - $nobjs[0][8]) : '';

    $p = array_2d_to_1d ($nobjs);
    $t = $last ? strtotime ($last->created_at->format ('Y-m-d H:i:s')) : 0;
    $b1 = $last && $last->battery !== null ? floor ($last->battery) : '';

    Load::sysFunc ('file.php');

    $this->length = $l;
    $this->save ();
    
    $a = [];
    $tmps = array_map (function ($t) { return strtotime ($t->format ('Y-m-d H:i:s')); }, Location::getArray ('created_at', array ('order' => 'id ASC', 'group' => 'battery', 'where' => array ('event_id = ? AND battery IS NOT NULL', $this->id))));
    
    for ($i = 0, $c = count ($tmps) - 1; $i < $c; $i++)
      array_push ($a, $tmps[$i + 1] - $tmps[$i]);

    $a = floor (array_sum ($a) / ($c + 1));
    $a = $b1 !== '' ? self::time (($b1 - 15) * $a) : '';

    // $first = Location::find ('one', array ('select' => 'battery', 'order' => 'id ASC', 'where' => array ('event_id = ?', $this->id)));
    // $b2 = $first && $first->battery !== null ? floor ($first->battery) : '';
    // $a = $b1 !== '' && $b2 !== '' && $b1 != $b2 ? (($b1 - 15) * floor ($x / ($b2 - $b1))) : '';

    return write_file ($path, json_encode (array (
      'm' => md5 (implode ('', $p)),
      'd' => $d,
      'l' => $l,
      't' => $t,
      'b' => '' . $b1,
      'a' => $a,
      'p' => $p,
    )), FOPEN_WRITE_CREATE_DESTRUCTIVE) && @chmod ($path, 0777);
  }

  public function destroy () {
    if (!isset ($this->id))
      return false;
    
    return $this->delete ();
  }
}
