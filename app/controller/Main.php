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

  public function path ($id) {
    $ids = array ();
    $n1 = Location::getArray ('id', array ('where' => array ('event_id IN (?)', $id)));

    for ($i = 0, $c = count ($n1), $u = ($c - 100) / 400; $i < $c; $i += $i < 100 ? 1 : $u)
      if ($m = array_slice ($n1, $i, 1))
        array_push ($ids, $m);

    $objs = Location::find ('all', array ('select' => 'id, latitude, longitude, altitude, horizontal_accuracy, vertical_accuracy, speed, course, time, battery', 'where' => array ('id IN (?)', $ids)));

    return Output::json (array_map (function ($l) {
      return array (
          'i' => $l->id,
          'a' => $l->latitude,
          'n' => $l->longitude,
          'd' => $l->altitude,
          'h' => $l->horizontal_accuracy,
          'v' => $l->vertical_accuracy,
          's' => $l->speed >= 0 ? ceil ($l->speed * 3.6 / 10) : 0,
          'c' => $l->course,
          't' => $l->time->format ('Y-m-d H:i:s'),
          'b' => $l->battery,
        );
    }, $objs));
  }
  public function index ($id) {
    // $location = 
    
    $this->asset->addCSS ('/assets/css/site/Main/index.css')
                ->addJs ('/assets/js/site/Main/index.js');

    return $this->view->setPath('site/Main/index.php')
    // ->with ('location', $location);
    ->with ('id', $id);
  }
}
