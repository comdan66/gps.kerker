<?php

namespace M;

class Event extends Model {
  const STATUS_NO_SIGNAL = 'no-signal';
  const STATUS_MOVING    = 'moving';
  const STATUS_FINISH    = 'finish';
  const STATUS_ERROR     = 'error';
  const STATUS = [
    self::STATUS_NO_SIGNAL => '沒有訊號', 
    self::STATUS_MOVING    => '移動中',
    self::STATUS_FINISH    => '已結束',
    self::STATUS_ERROR     => '訊號錯誤',
  ];

  static $relations = [
    'lastSignal' => ['hasOne' => 'Signal', 'order' => 'id DESC'],
  ];

  static $afterCreates = ['genCode'];

  public function genCode() {
    \Load::lib('Code');
    $this->code = \Code::encode(1, 5);
    return $this->save();
  }
}
