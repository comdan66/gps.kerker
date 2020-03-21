<?php

class Main extends Controller {
  public function index() {
    Log::info([
      'method' => Router::requestMethod(),
      'header' => Input::requestHeaders(),
      'file' => Input::file(),
      'get' => Input::get(),
      'post' => Input::post(),
    ]);
    return 'ok';
  }
}