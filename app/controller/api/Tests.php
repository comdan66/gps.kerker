<?php defined ('OACI') || exit ('此檔案不允許讀取。');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, OACI
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

class Tests extends ApiLoginController {

  public function __construct () {
    parent::__construct ();
  }
  /**
   * @apiDefine MyError 錯誤訊息
   *
   * @apiError {String} message  訊息
   * @apiErrorExample {json} 錯誤:
   *     HTTP/1.1 400 Error
   *     {
   *       "message": "錯誤訊息..",
   *     }
   */

  /**
   * @apiDefine MySuccess 成功訊息
   *
   * @apiSuccess {String} message  訊息
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *     {
   *       "message": "成功訊息..",
   *     }
   */

  /**
   * @apiDefine login 需先登入
   * 此 API 必需先登入後取得 <code>Access Token</code>，在藉由 Header 夾帶 Access Token 驗證身份
   *
   * @apiHeader {string} token 登入後的 Access Token
   */

  /**
   * @apiDefine loginMaybe 須不須登入皆可
   * 此 API 若有帶 <code>Access Token</code> 則代表登入
   *
   * @apiHeader {string} [token] 登入後的 Access Token
   */

   /**
    * @apiGroup Test
    * @apiName CreateFile
    *
    * @api {post} /api/test/file 新增檔案上傳
    * @apiHeader {string} token 登入後的 Access Token
    *
    * @apiParam {Number} text                  文字
    *
    * @apiParam {File}  [file]                檔案
    * @apiParam {File}  [pic]                 圖片
    *
    * @apiUse MySuccess
    * @apiUse MyError
    */
    public function createFile() {
      $validation = function(&$posts, $files) {
        Validation::maybe ($posts, 'text', '文字', '')->isStringOrNumber ()->doTrim ();
        Validation::maybe ($files, 'pic', '圖片', array ())->isUploadFile ()->formats ('jpg', 'gif', 'png', 'jpeg')->size (1, 10 * 1024 * 1024);
        Validation::maybe ($files, 'file', '檔案', array ())->isUploadFile ()->formats ('mp4', 'mov')->size (1, 10 * 1024 * 1024);
      };

      $posts = Input::post();
      $files = Input::file();

      if ($error = Validation::form ($validation, $posts, $files))
        return Output::json($error, 400);

      return Output::json ( array(
        'text' => isset($posts['text']) ? $posts['text'] : '',
        'pic'=> isset($files['pic'])? $files['pic'] : '',
        'file'=> isset($files['file'])? $files['file'] : '',
      )) ;

    }

    public function jsonDecode() {

    }
}
