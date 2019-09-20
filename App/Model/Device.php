<?php

namespace M;

class Device extends Model {
  private static $current;

  static $relations = [
    'events' => 'Event',
    'user' => '-> User',
  ];

  public static function current($uuid = null) {
    if ($uuid === null)
      return self::$current;

    if ($uuid === '')
      return self::$current;

    if (self::$current = \M\Device::one('uuid = ?', $uuid))
      return self::$current;

    return self::$current = \M\Device::create([
      'userId' => 0,
      'name' => $uuid,
      'uuid' => $uuid,
    ]);
  }

  // public function inEventCountRange() {
  //   if ($this->userId == 0)
  //     return count($this->events) < 10;

  //   switch ($this->user->role) {
  //     default:
  //     case User::ROLE_GENERAL:
  //       return Event::count('userId = ?  AND createAt BETWEEN ? AND ?', $this->user->id, date('Y-m-01 00:00:00'), date('Y-m-t 23:59:59')) < 10;
  //       break;

  //     case User::ROLE_MONTH100:
  //       return Event::count('userId = ?  AND createAt BETWEEN ? AND ?', $this->user->id, date('Y-m-01 00:00:00'), date('Y-m-t 23:59:59')) < 30;
  //       break;

  //     case User::ROLE_MONTH500:
  //       return true;
  //       break;
  //   }
  // }
}
