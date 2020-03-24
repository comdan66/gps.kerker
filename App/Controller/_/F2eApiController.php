<?php

abstract class F2eApiController extends ApiController {
  public function __construct() {
    parent::__construct();

    Status::append("Access-Control-Allow-Origin: " . \config('F2e', 'baseUrl'));
    Status::append("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
    Status::append("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With, Authorization");
  }
}