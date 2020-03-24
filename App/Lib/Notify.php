<?php

class Notify {
  public static function line($tokenKey, $message) {
    if (!$token = config('LineNotify', $tokenKey))
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