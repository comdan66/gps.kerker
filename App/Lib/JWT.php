<?php

class JWT {

  const HS256 = [ 'alg' => 'HS256', 'function' => 'hashHmac', 'algorithm' => 'SHA256' ];
  const HS512 = [ 'alg' => 'HS512', 'function' => 'hashHmac', 'algorithm' => 'SHA512' ];
  const HS384 = [ 'alg' => 'HS384', 'function' => 'hashHmac', 'algorithm' => 'SHA384' ];
  const RS256 = [ 'alg' => 'RS256', 'function' => 'openssl', 'algorithm' => 'SHA256' ];
  const RS512 = [ 'alg' => 'RS512', 'function' => 'openssl', 'algorithm' => 'SHA512' ];
  const RS384 = [ 'alg' => 'RS384', 'function' => 'openssl', 'algorithm' => 'SHA384' ];
  const ALGS  = [self::HS256, self::HS512, self::HS384, self::RS256, self::RS512, self::RS384];

  public static $leeway = 0;
  public static $timestamp = null;

  private static function base64($code) {
    return str_replace('=', '', strtr(base64_encode(is_array($code) ? json_encode($code) : $code), '+/', '-_'));
  }
  
  public static function base64Decode($input) {
    $remainder = strlen($input) % 4;

    if ($remainder) {
      $padlen = 4 - $remainder;
      $input .= str_repeat('=', $padlen);
    }
    return base64_decode(strtr($input, '-_', '+/'));
  }
  
  private static function hashHmac($headerAndPayload, $key, $algorithm) {
    return hash_hmac($algorithm, $headerAndPayload, $key, true);
  }

  private static function openssl($headerAndPayload, $key, $algorithm) {
    $signature = '';
    return openssl_sign($headerAndPayload, $signature, $key, $algorithm) ? $signature : null;
  }

  public static function encode($payload, $key, $method) {
    if (!(($function = $method['function']) == 'openssl' ? is_callable('openssl') : is_callable('hash_hmac')))
      return null;

    $header = [
      'typ' => 'JWT',
      'alg' => $method['alg']
    ];
    
    $header  = self::base64($header);
    $payload = self::base64($payload);

    if (!$signature = self::$function($header . '.' . $payload, $key, $method['algorithm']))
      return null;

    return $header . '.' . $payload . '.' . self::base64($signature);
  }

  public static function jsonDecode($input) {
    if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4))
      return json_decode($input, true, 512, JSON_BIGINT_AS_STRING);
    $maxIntLength = strlen((string) PHP_INT_MAX) - 1;
    $jsonWithoutBigints = preg_replace('/:\s*(-?\d{'.$maxIntLength.',})/', ': "$1"', $input);
    return json_decode($jsonWithoutBigints, true);
  }

  private static function opensslVerify($headerAndPayload, $signature, $key, $algorithm) {
    return self::openssl($headerAndPayload, $key, $algorithm) !== null;
  }

  private static function hashHmacVerify($headerAndPayload, $signature, $key, $algorithm) {
    $hash = self::hashHmac($headerAndPayload, $key, $algorithm);

    if (function_exists('hash_equals'))
      return hash_equals($signature, $hash);
    
    $strlen = function($str) {
      return function_exists('mb_strlen') ? mb_strlen($str, '8bit') : strlen($str);
    };

    $len = min($strlen($signature), $strlen($hash));

    $status = 0;
    for ($i = 0; $i < $len; $i++)
      $status |= (ord($signature[$i]) ^ ord($hash[$i]));

    $status |= ($strlen($signature) ^$strlen($hash));
    return $status === 0;
  }

  public static function decode($jwt, $key) {
    $tokens = explode('.', $jwt);
    
    if (count($tokens) != 3)
      return null;

    list($base64Header, $base64Payload, $signature) = $tokens;

    if (null === ($header = self::jsonDecode(self::base64Decode($base64Header))))
      return null;

    if (null === $payload = self::jsonDecode(self::base64Decode($base64Payload)))
      return null;

    if (false === ($signature = self::base64Decode($signature)))
      return null;

    if (!(isset($header['alg']) && in_array($alg = $header['alg'], array_column(self::ALGS, 'alg')) && defined("self::$alg")))
      return null;

    eval('$method = ' . "self::$alg" . ';');

    if (!(($function = $method['function']) == 'openssl' ? is_callable('openssl') : is_callable('hash_hmac')))
      return null;

    $function .= 'Verify';

    $result = self::$function($base64Header . '.' . $base64Payload, $signature, $key, $method['algorithm']);

    if (!$result)
      return null;

    $timestamp = self::$timestamp ?? time();

    if (isset($payload['nbf']) && $payload['nbf'] > ($timestamp + self::$leeway))
      return null;

    if (isset($payload['iat']) && $payload['iat'] > ($timestamp + self::$leeway))
      return null;

    if (isset($payload['exp']) && ($timestamp - self::$leeway) >= $payload['exp'])
      return null;

    return $payload;
  }
}