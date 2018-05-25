<?php defined ('OACI') || exit ('此檔案不允許讀取。');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, OACI
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

class Configs extends ApiLoginController {

  public function __construct () {
    parent::__construct ();
  }

  /**
   * @apiGroup Config
   * @apiName UploadConfig
   *
   * @api {get} /api/config/upload    取得上傳設定值
   * @apiHeader {string} token        登入後的 Access Token
   *
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *     {
   *         "post_max_size": "1000MB",
   *         "upload_max_filesize": "200MB",
   *         "max_file_uploads": "5"
   *     }
   *
   * @apiUse MyError
   */
  public function upload () {
    return Output::json([
      'post_max_size' => "1000MB",
      'upload_max_filesize' => "200MB",
      'max_file_uploads' => "5"
      ]);
  }
}
