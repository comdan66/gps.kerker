<?php

class Main extends Controller {
  public function index() {
    return \M\Signal::createBy(Input::get() ?? [])
      ? 'ok'
      : 'no';
  }
}