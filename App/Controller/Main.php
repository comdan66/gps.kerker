<?php

class Main extends Controller {
  private function cover($str) {
    if (!is_string($str)) return false;
    if (count($strs = explode(',', $str)) != 13) return false;
    if (!(is_string($prefix = array_shift($strs)) && $prefix == '$GPRMC')) return false;
    if (!(is_numeric($time = array_shift($strs)) && ($time = (int)$time) && ($time = sprintf('%06d', $time)) && ($time = implode(':', str_split($time, 2))))) return false;
    if (!(is_string($status = array_shift($strs)) && in_array($status, ['A', 'V']) && ($status == 'A') && ($status = true))) return false;
    if (!(is_numeric($latV = 0 + array_shift($strs)) && is_string($latD = array_shift($strs)) && in_array($latD, ['N', 'S']))) return false;
    if (!(is_numeric($lngV = 0 + array_shift($strs)) && is_string($lngD = array_shift($strs)) && in_array($lngD, ['E', 'W']))) return false;
    if (!is_numeric($speed = 0 + array_shift($strs))) return false;
    if (!is_numeric($course = 0 + array_shift($strs))) return false;
    if (!(is_numeric($date = array_shift($strs)) && strlen($date) == 6 && ($date = '20' . implode('-', array_reverse(str_split($date, 2)))))) return false;
    if (!(is_numeric($declinationV = 0 + array_shift($strs)) && is_string($declinationD = array_shift($strs)) && in_array($declinationD, ['E', 'W']))) return false;
    if (!is_string($mode = array_shift($strs))) return false;
    if (($datetime = DateTime::createFromFormat('Y-m-d H:i:s', $datetime = $date . ' ' . $time, new DateTimeZone('GMT'))) === false) return false;

    $datetime->setTimezone(new DateTimeZone('Asia/Taipei'));
    $datetime = $datetime->format('Y-m-d H:i:s');
    $declination = round(($declinationD == 'W' ? -1 : 1) * $declinationV, 2);

    $lat = ($latD == 'S' ? -1 : 1) * round(floor($latV / 100) + $latV * 10000 % 1000000 / 10000 / 60, 6);
    $lng = ($lngD == 'W' ? -1 : 1) * round(floor($lngV / 100) + $lngV * 10000 % 1000000 / 10000 / 60, 6);

    return [
      'prefix' => $prefix,
      'lat' => $lat,
      'lng' => $lng,
      'speed' => $speed,
      'course' => $course,
      'declination' => $declination,
      'datetime' => $datetime,
      'mode' => $mode,
    ];
  }

  public function index() {
    Log::info($this->cover(Input::get('v')));
    return 'ok';
  }
}