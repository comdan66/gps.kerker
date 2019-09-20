<?php

abstract class ApiDeviceController extends ApiController {
  public function __construct() {
    parent::__construct();

    \M\Device::current(Input::requestHeader('DeviceUUID'));
    \M\Device::current() || error('API 錯誤！');

    $name = Input::requestHeader('DeviceName');
    if (isset($name) && \M\Device::current()->name != $name) {
      \M\Device::current()->name = $name;
      \M\Device::current()->save();
    }
  }
}