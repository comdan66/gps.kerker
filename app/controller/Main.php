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
    return Output::json (array_map (function ($l) {
      return array (
          'id' => $l->id,
          'lat' => $l->latitude,
          'lng' => $l->longitude,
          'alt' => $l->altitude,
          'h_acc' => $l->horizontal_accuracy,
          'v_acc' => $l->vertical_accuracy,
          'speed' => $l->speed,
          'course' => $l->course,
          'time' => $l->time->format ('Y-m-d H:i:s'),
          'battery' => $l->battery,
        );
    }, Location::find ('all', array ('where' => array ('event_id IN (?)', $id)))));
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
