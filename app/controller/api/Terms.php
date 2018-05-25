<?php defined ('OACI') || exit ('此檔案不允許讀取。');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, OACI
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

class Terms extends ApiLoginController {

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
   * @apiName Terms
   * @apiGroup Term
   *
   * @api {get} /api/term 取得條款
   *
   * @apiHeader {String}                 token 登入後的 Access Token
   *
   * @apiSuccess {Number}   id           條款ID
   *
   * @apiSuccess {String}   title        標題
   * @apiSuccess {String}   content      條款內容
   * @apiSuccess {DateTime} created_at   建立時間
   * @apiSuccess {DateTime} updated_at   更新時間
   *
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *      [
   *         {
   *            "id": "1",
   *            "title": "使用條款",
   *            "content": "使用條款內容"
   *            "created_at": "2018-05-01 15:00:00",
   *            "updated_at": "2018-05-01 13:00:22",
   *          },
   *         {
   *            "id": "2",
   *            "title": "免責聲明",
   *            "content": "免責聲明內容"
   *            "created_at": "2018-05-01 15:00:00",
   *            "updated_at": "2018-05-01 13:00:22",
   *          }
   *      ]
   *
   *
   * @apiUse MyError
   */
  public function index() {
    $validation = function(&$obj) {
      if( !$obj = Term::find('all') )
        Validation::error('查無條款內容');
    };

    if( $error = Validation::form($validation, $obj) )
      return Output::json($error, 400);

    return Output::json(array_values (array_filter (array_map (function ($obj) {
      return array (
          'id' => $obj->id,
          'title' => $obj->title,
          'content' => $obj->content,
          'created_at' => $obj->created_at->format ('Y-m-d H:i:s'),
          'updated_at' => $obj->updated_at->format ('Y-m-d H:i:s'),
        );
    }, $obj))));
  }

}
