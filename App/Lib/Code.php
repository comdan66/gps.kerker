<?php

class Code {
  private static $_digital = null;
  private static $_baseLen = null;

  public static function encode($num, $zeor = null) {
    $r = $num % self::baseLen();
    $res = self::digital()[$r];
    $q = floor($num / self::baseLen());

    while ($q) {
      $r = $q % self::baseLen();
      $q =floor($q / self::baseLen());
      $res = self::digital()[$r] . $res;
    }

    return $zeor ? str_pad($res, $zeor, self::digital()[0], STR_PAD_LEFT) : $res;
  }

  public static function decode($str) {
    $limit = strlen($str);
    $res = strpos(self::digital(), $str[0]);
    for($i=1; $i < $limit; $i++)
      $res = self::baseLen() * $res + strpos(self::digital(),$str[$i]);
    return (int)$res;
  }

  public static function sample($code = '') {
    switch (ENVIRONMENT) {
      case 'Development':
        return 'http://dev.i3b.tw/' . $code;
      
      case 'Staging':
      case 'Testing':
        return 'http://test.i3b.tw/' . $code;

      case 'Production':
        return 'http://i3b.tw/' . $code;
    }
  }

  private static function baseLen() {
    return self::$_baseLen === null
      ? self::$_baseLen = strlen(self::digital())
      : self::$_baseLen;
  }

  private static function digital() {
    if (self::$_digital !== null)
      return self::$_digital;

    switch (ENVIRONMENT) {
      case 'Development':
        return self::$_digital = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      
      case 'Staging':
      case 'Testing':
        return self::$_digital = 'irkSc6YCbylseqdWtJ47nXRTVa2LDpozB58E3U1jgwmNKvFHMAOhu9PfI0GxQZ';

      case 'Production':
        return self::$_digital = 'qwFSy6Y0oPMuXz5ZTfHr8LNsVJKvgibenjmOt7RaA12ECpx39DQU4dlIGBWkhc';
    }
  }
}