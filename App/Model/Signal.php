<?php

namespace M;

class Signal extends Model {
  const ENABLE_YES = 'yes';
  const ENABLE_NO  = 'no';
  const ENABLE = [
    self::ENABLE_YES => '啟用', 
    self::ENABLE_NO  => '停用',
  ];

  public static function length($aa, $an, $ba, $bn) {
    $aa = deg2rad($aa);
    $bb = deg2rad($an);
    $cc = deg2rad($ba);
    $dd = deg2rad($bn);
    return (2 * asin(sqrt(pow(sin(($aa - $cc) / 2), 2) + cos($aa) * cos($cc) * pow(sin(($bb - $dd) / 2), 2)))) * 6378137;
  }

  public static function points($ids) {
    $c = count($ids);

    $tmps = [];
    $unit = ($c - 100) / 300;

    for ($i = 0; $i < $c; $i += $i < 100 ? 1 : $unit)
      if ($m = $ids[$i])
        array_push($tmps, $m);

    if (!$d = count($tmps))
      return $tmps;
    
    if ($tmps[$d - 1] != $ids[$c - 1])
      array_push($tmps, $ids[$c - 1]);

    return $tmps;
  }

  static $afterCreates = ['caleLen'];

  public function caleLen() {
    if ($this->enable != Signal::ENABLE_YES) return true;
    
    $signals = array_map('\M\toArray', Signal::all(['select' => 'lat,lng', 'where' => ['eventId = ? AND enable = ?', $this->eventId, Signal::ENABLE_YES]]));

    $length = 0;
    for ($i = 1, $signals, $c = count($signals); $i < $c; $i++)
      $length += Signal::length(
        $signals[$i - 1]['lat'], $signals[$i - 1]['lng'],
        $signals[$i]['lat'], $signals[$i]['lng']);
    
    return \M\Event::updateAll(['length' => round($length / 1000, 2)], ['where' => ['id = ?', $this->eventId]]);
  }

  public static function createBy($get) {
    $param    = $get['v'] ?? null;
    $deviceId = $get['d'] ?? 0;
    $result   = self::parse($deviceId, $param);

    return is_array($result)
      ? Signal::create($result)
      : Signal::create([
        'enable' => Signal::ENABLE_NO,
        'memo' => is_string($result) ? $result : 'Parse 回傳錯誤',
        'param' => $param
      ]);
  }

  private static function parse($deviceId, $param) {
    if (!$eventId = Event::last('deviceId = ?', $deviceId)) return '錯誤的 Event';
    $eventId = $eventId->id;

    if (!is_string($param)) return '非字串';
    if (count($strs = explode(',', $param)) != 13) return 'token長度非13';
    if (!is_string($prefix = array_shift($strs))) return '前綴錯誤1';
    if ($prefix != '$GPRMC') return '前綴錯誤2';
    if (!is_numeric($time = array_shift($strs))) return '時間錯誤1';

    $time = implode(':', str_split(sprintf('%06d', (int)$time), 2));

    if (strlen($time) != 8) return '時間錯誤2';
    if (!is_string($status = array_shift($strs))) return '狀態錯誤1';
    if (!in_array($status, ['A', 'V'])) return '狀態錯誤2';
    if ($status != 'A') return '狀態錯誤3';

    if (!is_numeric($latV = array_shift($strs))) return '緯度錯誤1';
    if (!is_string($latD = array_shift($strs))) return '緯度錯誤2';
    if (!in_array($latD, ['N', 'S'])) return '緯度錯誤3';
    $lat = ($latD == 'S' ? -1 : 1) * $latV;
    $lat = round(floor($lat / 100) + $lat * 10000 % 1000000 / 10000 / 60, 6);
    if ($lat > 90 || $lat < -90) return '緯度錯誤4';

    if (!is_numeric($lngV = array_shift($strs))) return '經度錯誤1';
    if (!is_string($lngD = array_shift($strs))) return '經度錯誤2';
    if (!in_array($lngD, ['E', 'W'])) return '經度錯誤3';
    $lng = ($lngD == 'W' ? -1 : 1) * $lngV;
    $lng = round(floor($lng / 100) + $lng * 10000 % 1000000 / 10000 / 60, 6);
    if ($lng > 180 || $lng < -180) return '經度錯誤4';

    if (!is_numeric($speed = array_shift($strs))) return '速度錯誤1';
    if($speed < 0) return '速度錯誤2';
    $speed = round($speed * 1.852, 2);
    if ($speed > 999.99) return '速度錯誤3';

    if (!is_numeric($course = array_shift($strs))) return '方向錯誤1';
    $course = round(0 + $course, 1);
    if ($course < 0 || $course > 359.9) return '方向錯誤2';

    if (!is_numeric($date = array_shift($strs))) return '日期錯誤1';
    if (strlen($date) != 6) return '日期錯誤2';
    $date = array_reverse(str_split($date, 2));
    if (count($date) != 3) return '日期錯誤3';

    $date[0] = '20' . $date[0];
    if ($date[1] < 1 || $date[1] > 12) return '日期錯誤4';
    if ($date[2] < 1 || (in_array($date[1], [1, 3, 5, 7, 8, 10, 12]) ? ($date[2] > 31) : ($date[2] > 30))) return '日期錯誤5';
    $date = implode('-', $date);
    if (strlen($date) != 10) return '日期錯誤3';

    if (($datetime = \DateTime::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time, new \DateTimeZone('GMT'))) === false) return '日期時間錯誤1';
    $datetime->setTimezone(new \DateTimeZone('Asia/Taipei'));
    $datetime = $datetime->format('Y-m-d H:i:s');
    if (strlen($datetime) != 19) return '日期時間錯誤2';

    if (!is_numeric($declinationV = array_shift($strs))) return '磁偏角錯誤1';
    if (!is_string($declinationD = array_shift($strs))) return '磁偏角錯誤2';
    if (!in_array($declinationD, ['E', 'W'])) return '磁偏角錯誤3';
    $declination = ($declinationD == 'W' ? -1 : 1) * $declinationV;
    if ($declination < -180 || $declination > 180) return '磁偏角錯誤4';
    
    if (!is_string($mode = array_shift($strs))) return 'Mode 錯誤';

    $last = Signal::last('eventId = ? AND enable = ?', $eventId, Signal::ENABLE_YES);
    $memo = $last && $last->lat === $lat && $last->lng === $lng ? '資料一樣' : '';

    return [
      'eventId'     => $eventId,
      'lat'         => $lat,
      'lng'         => $lng,
      'speed'       => $speed,
      'course'      => $course,
      'timeAt'      => $datetime,
      'declination' => $declination,
      'mode'        => $mode,
      'param'       => $param,
      'memo'        => $memo,
      'enable'      => $memo
        ? Signal::ENABLE_NO
        : Signal::ENABLE_YES,
    ];
  }
}
