<?php

abstract class ApiController extends Controller {
  public function __construct() {
    ifApiError(function() {
      return ['messages' => func_get_args()];
    });
  }
  protected function methodIn() {
    $args = func_get_args();
    $methods = array_filter($args, 'is_string');

    if ($methods && !in_array(Router::methodName(), $methods))
      return true;

    $closures = array_filter($args, 'is_callable');
    foreach ($closures as $closure)
      $closure() || error('找不到資料！');

    return true;
  }
}