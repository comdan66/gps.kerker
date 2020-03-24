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
    $this->code = \Code::encode($this->id, 5);

    $url = \config('F2e', 'baseUrl') . '/?' . $this->code;
    self::lineNotify('oa', '要回家囉，關注最新位置請點：' . $url);
    ENVIRONMENT == 'Development' || self::lineNotify('shari', '胖波要回家囉，關注最新位置請點：' . $url);

    return $this->save();
  }
  private function lineNotify($tokenKey, $message) {
    if (!$token = \config('LineNotify', $tokenKey))
      return false;

    $url = 'https://notify-api.line.me/api/notify';
    
    $options = [
      CURLOPT_URL => $url,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_CONNECTTIMEOUT => 30,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
      CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token
      ],
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => http_build_query([
        'message' => $message
      ])
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    $data = curl_exec($ch);
    $err = curl_error($ch);
    $errno = curl_errno($ch);
    curl_close($ch);

    return $err || $errno ? false : $data;
  }
}
