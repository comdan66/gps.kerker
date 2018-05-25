<?php defined ('OACI') || exit ('此檔案不允許讀取。');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, OACI
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

class Explans extends ApiLoginController {

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
   * @apiName Explans
   * @apiGroup Explan
   *
   * @api {get} /api/explan 取得說明
   *
   * @apiHeader {String}                  token 登入後的 Access Token
   *
   * @apiSuccess {Number} id              說明ID
   *
   * @apiSuccess {String} title           標題
   * @apiSuccess {Array}  details         詳細說明資訊
   * @apiSuccess {Number} details.id      詳細資訊ID
   * @apiSuccess {String} details.title   詳細標題
   * @apiSuccess {String} details.content 詳細內容
   *
   * @apiSuccess {DateTime} created_at    建立時間
   * @apiSuccess {DateTime} updated_at    更新時間
   *
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *      [
   *         {
   *            "id": "1",
   *            "title": "常見問題",
   *            "details": [
   *              {
   *                 "id": "1",
   *                 "title": "常見問題項目1",
   *                 "content": "內容",
   *              },
   *              {
   *                 "id": "2",
   *                 "title": "常見問題項目2",
   *                 "content": "內容",
   *              }
   *            ],
   *            "created_at": "2018-05-01 15:00:00",
   *            "updated_at": "2018-05-01 13:00:22",
   *          },
   *          {
   *            "id": "2",
   *            "title": "用戶帳號",
   *            "details": [
   *              {
   *                 "id": "3",
   *                 "title": "用戶帳號項目1",
   *                 "content": "內容",
   *              },
   *              {
   *                 "id": "4",
   *                 "title": "用戶帳號項目2",
   *                 "content": "內容",
   *              }
   *            ],
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
      if( !$obj = Explan::find('all', array('include' => array('detail')) ) )
        Validation::error('查無說明內容');
    };

    if( $error = Validation::form($validation, $obj) )
      return Output::json($error, 400);

    return Output::json(array_values (array_filter (array_map (function ($obj) {
      return array (
          'id' => $obj->id,
          'title' => $obj->title,
          'details' => array_map( function($value) {
            return array(
              'id' => $value->id,
              'title' => $value->title,
              'content' => $value->content,
            );
          }, $obj->detail),
          'created_at' => $obj->created_at->format ('Y-m-d H:i:s'),
          'updated_at' => $obj->updated_at->format ('Y-m-d H:i:s'),
        );
    }, $obj))));
  }
}
