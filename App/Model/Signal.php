<?php

namespace M;

class Signal extends Model {
  const BATTERY_STATUS_UNKNOWN   = 'unknown';
  const BATTERY_STATUS_UNPLUGGED = 'unplugged';
  const BATTERY_STATUS_CHARGING  = 'charging';
  const BATTERY_STATUS_FULL      = 'full';
  const BATTERY_STATUS = [
    self::BATTERY_STATUS_UNKNOWN => '未知', 
    self::BATTERY_STATUS_UNPLUGGED  => '放電',
    self::BATTERY_STATUS_CHARGING  => '充電',
    self::BATTERY_STATUS_FULL  => '飽電',
  ];

  const ENABLE_YES = 'yes';
  const ENABLE_NO = 'no';
  const ENABLE = [
    self::ENABLE_YES => '啟用', 
    self::ENABLE_NO  => '停用',
  ];

  const ALT = 100; // 公尺
}
