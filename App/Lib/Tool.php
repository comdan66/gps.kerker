<?php

class Tool {

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

  public static function put2S3($data, $filename, $s3Filename) {
    // 丟到 S3
    $path = PATH_FILE_TMP . $filename;
    $s3Path = 'json/' . $s3Filename;

    // 存在->刪除失敗 或 刪除了，依然存在
    if (file_exists($path)) {
      @unlink($path);

      if (file_exists($path))
        return '發生 ' . $filename . ' 檔案尚未刪除(1)！';
    }

    Load::systemFunc('file');

    if (!fileWrite($path, $data))
      return '發生 ' . $filename . ' 檔案寫入失敗！';

    Load::systemLib('S3');

    $bucket = config('siteS3', 'bucket');
    $access = config('siteS3', 'access');
    $secret = config('siteS3', 'secret');

    $s3 = new S3($access, $secret);

    if (!$s3->putObject($path, $bucket, $s3Path, S3::ACL_PUBLIC_READ, [], ['Cache-Control' => 'max-age=10']))
      return '發生 ' . $filename . ' 檔案上傳失敗！';
    @unlink($path);
    
    // 刪除失敗 或 刪除了依然存在
    if (file_exists($path))
      return '發生 ' . $filename . ' 檔案尚未刪除(2)！';

    return '';
  }
}
