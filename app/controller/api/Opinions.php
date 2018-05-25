<?php defined ('OACI') || exit ('此檔案不允許讀取。');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, OACI
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

class Opinions extends ApiLoginController {

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
   * @apiName CreateOpinion
   * @apiGroup Opinion
   *
   * @api {post} /api/opinion               新增回饋意見
   * @apiHeader {string} token              登入後的 Access Token
   *
   * @apiParam {String} content             內容
   *
   * @apiUse MySuccess
   * @apiUse MyError
   */
  public function create() {
    $validation = function (&$posts) {
      Validation::need ($posts, 'content', '內容')->isStringOrNumber ()->doTrim ()->length(1, 191);
      $posts['user_id'] = $this->user->id;
    };

    $transaction = function ($posts) {
      if(!$obj = Opinion::create ($posts))
        return false;
      return true;
    };

    $posts = Input::post();

    if ($error = Validation::form ($validation, $posts))
      return Output::json($error, 400);

    if ($error = Opinion::getTransactionError ($transaction, $posts))
      return Output::json($error, 400);

    return Output::json(['message' => "成功"], 200);
  }
}
