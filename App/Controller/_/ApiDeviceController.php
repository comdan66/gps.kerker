<?php

abstract class ApiDeviceController extends ApiController {
  public function __construct() {
    parent::__construct();

    \M\Device::current(Input::requestHeader('DeviceUUID'));
    \M\Device::current() || error('API 錯誤！');
  }
}