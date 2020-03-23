<?php

abstract class F2eApiController extends ApiController {
  public function __construct() {
    parent::__construct();

    if (ENVIRONMENT === 'Production')
      Status::append("Access-Control-Allow-Origin: https://gps.kerker.tw");
    else if (ENVIRONMENT === 'Testing')
      Status::append("Access-Control-Allow-Origin: https://testing-gps.kerker.tw");
    else
      Status::append("Access-Control-Allow-Origin: https://dev.gps.kerker.tw:8000");

    Status::append("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
    Status::append("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With, Authorization");
  }
}