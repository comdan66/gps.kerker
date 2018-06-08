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
    
    $where = Where::create ('event_id = ? AND speed >= ? AND horizontal_accuracy < ?', $this->id, 0, $accuracyFilter);
    $objs = Location::getArray ('id', array ('order' => 'id DESC', 'where' => $where));

    for ($i = 0, $c = count ($objs), $u = ($c - 100) / ($count - 100); $i < $c; $i += $i < 100 ? 1 : $u)
      if ($m = array_slice ($objs, $i, 1))
        array_push ($ids, $m);

    $objs = Location::find ('all', array ('select' => 'id, latitude as lat, longitude as lng, speed * 3.6 as sp', 'order' => 'id DESC', 'where' => array ('id IN (?)', $ids)));

    $ids = array ();
      for ($i = 0, $c = count ($objs); $i < $c && array_push ($ids, $objs[$i]->id); $i++)
        for ($j = $i + 1, $t = $i, $d = 0; $j < $c && $d < 10; $j++, $d++)
          if (self::length ($objs[$t]->lat, $objs[$t]->lng, $objs[$j]->lat, $objs[$j]->lng) <= ($objs[$j]->sp > 10 ? $objs[$j]->sp > 20 ? $objs[$j]->sp > 30 ? 0 + 5 : 10 + 5 : 20 + 5 : 30 + 5))
            $i = $j;
          else
            break;

    if ($objs = Location::find ('one', array ('select' => 'id', 'order' => 'id ASC', 'where' => $where)))
      array_push ($ids, $objs->id);

    if ($objs = Location::find ('one', array ('select' => 'id', 'order' => 'id DESC', 'where' => $where)))
      array_push ($ids, $objs->id);

    $objs = array_map (function ($l) {
      return array (
        $l->id,                                       // 'i' 0
        floatval ($l->lat),                                      // 'a' 1
        floatval ($l->lng),                                      // 'n' 2
        floatval ($l->alt),                                      // 'd' 3
        floatval ($l->hacc),                                     // 'h' 4
        floatval ($l->vacc),                                     // 'v' 5
        ceil ($l->sp),                                // 's' 6
        intval ($l->co),                                       // 'c' 7
        strtotime ($l->time->format ('Y-m-d H:i:s')), // 't' 8
        ceil ($l->ba),                                       // 'b' 9
      );
    }, Location::find ('all', array (
      'select' => 'id, latitude as lat, longitude as lng, altitude as alt, horizontal_accuracy as hacc, vertical_accuracy as vacc, speed * 3.6 as sp, course as co, time, battery as ba',
      'order' => 'id ASC',
      'where' => array ('id IN (?)', $ids))));

    $length = 0;
    for ($i = 0, $c = count ($objs); $i < $c; $i++)
      if ($i + 1 < $c)
        $length += self::length ($objs[$i][1], $objs[$i][2], $objs[$i + 1][1], $objs[$i + 1][2]);


    $this->length = $length;
    $this->save ();
    $timeago = strtotime ($this->updated_at->format ('Y-m-d H:i:s'));

    $duration = $objs ? self::time ($objs[count($objs) - 1][8] - $objs[0][8]) : '';

    $battery = $objs && $objs[count($objs) - 1][9] != null ? $objs[count($objs) - 1][9] : '';
    $objs = array_2d_to_1d ($objs);

    Load::sysFunc ('file.php');

    return write_file ($path, json_encode (array (
      'm' => md5 (implode ('', $objs)),
      'd' => $duration,
      'l' => $length,
      't' => $timeago,
      'b' => $battery,
      'p' => $objs,
    )), FOPEN_WRITE_CREATE_DESTRUCTIVE) && @chmod ($path, 0777);
  }

  public function destroy () {
    if (!isset ($this->id))
      return false;
    
    return $this->delete ();
  }
}
