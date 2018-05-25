<?php defined ('OACI') || exit ('此檔案不允許讀取。');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, OACI
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

class Notifies extends ApiController {

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
   * @apiName GetNotifies
   * @apiGroup Notify
   *
   * @api {get} /api/notify             取得通知
   *
   * @apiHeader {string} token          登入後的 Access Token
   * @apiParam {Number} [offset=0]      位移
   * @apiParam {String} [limit=20]      長度
   *
   * @apiSuccess {Number}   id          通知 ID
   * @apiSuccess {String}   content     內容
   * @apiSuccess {String}   read        已讀狀態 (已讀: yes, 未讀: no)
   * @apiSuccess {DateTime} created_at  建立時間
   * @apiSuccess {DateTime} updated_at  更新時間
   *
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *     [
   *         {
   *             "id": 3,
   *             "content": "哈哈哈",
   *             "read": false,
   *             "created_at": "2018-04-20 14:27:04",
   *             "updated_at": "2018-05-02 11:00:42"
   *         },
   *     ]
   *
   * @apiUse MyError
   */

  public function index() {
    $validation = function(&$gets) {
      Validation::maybe ($gets, 'offset', '位移', 0)->isNumber ()->doTrim ()->greater (0);
      Validation::maybe ($gets, 'limit', '長度', 20)->isNumber ()->doTrim ()->greater (0);
    };

    $gets = Input::get ();

    if( $error = Validation::form($validation, $gets) )
      return Output::json($error, 400);

    return Output::json(array_values (array_filter (array_map (function ($notify) {
      return array (
          'id' => $notify->id,
          'content' => $notify->content,
          'read' => $notify->read == Notify::READ_YES,
          'created_at' => $notify->created_at->format ('Y-m-d H:i:s'),
          'updated_at' => $notify->updated_at->format ('Y-m-d H:i:s'),
        );
    }, Notify::find ('all', array (
      'offset' => $gets['offset'],
      'limit' => $gets['limit'],
      'where' => array('user_id = ?', $this->user->id)))))));
  }


  /**
   * @apiName ReadNotify
   * @apiGroup Notify
   *
   * @api {post} /api/notify/read       讀取通知
   *
   * @apiHeader {string} token          登入後的 Access Token
   *
   * @apiParam {Number} id            通知 ID
   *
   * @apiUse MySuccess
   * @apiUse MyError
   */

  public function read() {
    $validation = function(&$posts, &$notify) {
      Validation::need ($posts, 'id', '通知 ID')->isNumber ()->doTrim ()->greater (0);

      if (!$notify = Notify::find_by_id ($posts['id']))
        Validation::error('查無通知資訊');
    };

    $transaction = function ($posts, $notify) {
      $notify->read = Notify::READ_YES;
      return $notify->save ();
    };

    $posts = Input::post ();

    if( $error = Validation::form($validation, $posts, $notify) )
      return Output::json($error, 400);

    if ($error = Notify::getTransactionError ($transaction, $posts, $notify))
      return Output::json($error, 400);

    return Output::json(['message' => "成功"], 200);
  }
}
