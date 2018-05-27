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
    $validation = function(&$posts) {
      Validation::need ($posts, 'latitude', '緯度')->isNumber ()->doTrim ()->greaterEqual (-90)->lessEqual (90);
      Validation::need ($posts, 'longitude', '經度')->isNumber ()->doTrim ()->greaterEqual (-180)->lessEqual (180);
      Validation::need ($posts, 'altitude', '高度')->isNumber ()->doTrim ();
      
      Validation::maybe ($posts, 'horizontal_accuracy', '水平準度', -1)->isNumber ()->doTrim ();
      Validation::maybe ($posts, 'vertical_accuracy', '垂直準度', -1)->isNumber ()->doTrim ();
      
      Validation::need ($posts, 'speed', '速度')->isNumber ()->doTrim ()->greaterEqual (0);
      Validation::need ($posts, 'course', '方向角度')->isNumber ()->doTrim ()->greaterEqual (0);
      Validation::need ($posts, 'floor', '樓層')->isNumber ()->doTrim ();
    };

    $transaction = function($posts) {
      return Location::create ($posts);
    };

    $posts = Input::post ();

    if ($error = Validation::form ($validation, $posts))
      return Output::json($error, 400);

    if ($error = Location::getTransactionError ($transaction, $posts))
      return Output::json($error, 400);

    return Output::json(['message' => '成功']);
  }
}
