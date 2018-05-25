<?php defined ('OACI') || exit ('此檔案不允許讀取。');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, OACI
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

class Accounts extends ApiLoginController {

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
    * @apiName Account
    * @apiGroup Account
    *
    * @api {get} /api/account              取得個人帳戶資訊
    *
    * @apiHeader {string} token          登入後的 Access Token
    *
    * @apiSuccess {Number}   id           帳戶 ID
    * @apiSuccess {String}   name         名稱
    * @apiSuccess {String}   bank_code    銀行代號
    * @apiSuccess {String}   bank_branch  銀行分行
    * @apiSuccess {String}   bank_account 銀行帳戶
    * @apiSuccess {String}   phone        電話
    * @apiSuccess {DateTime} created_at   建立時間
    * @apiSuccess {DateTime} updated_at   更新時間
    *
    * @apiSuccessExample {json} 成功:
    *     HTTP/1.1 200 OK
    *       {
    *         "id": 47,
    *         "name": "璩匾濠姒",
    *         "bank_code": "LqfYzb",
    *         "bank_branch": "愚特分行",
    *         "bank_account": "GNvtMTFysnSc",
    *         "phone": "6182039547",
    *         "created_at": "2018-05-14 10:48:21",
    *         "updated_at": "2018-05-15 17:49:03"
    *       }
    *
    * @apiUse MyError
    */

    public function index() {
      $validation = function(&$obj) {
        if( !$obj = Account::find('one', array( 'where' => array('user_id = ?', $this->user->id) ) ) )
          Validation::error('查無帳戶資訊');
      };

      if ($error = Validation::form ($validation, $obj))
        return Output::json($error, 400);

      return Output::json ( array(
          'id' => $obj->id,
          'name' => $obj->name,
          'bank_code' => $obj->bank->code,
          'bank_branch' => $obj->bank_branch,
          'bank_account' => $obj->bank_account,
          'phone' => $obj->phone,
          'created_at' => $obj->created_at->format ('Y-m-d H:i:s'),
          'updated_at' => $obj->updated_at->format ('Y-m-d H:i:s'),
        ));
    }


  /**
   * @apiName UpdateAccount
   * @apiGroup Account
   *
   * @api {post} /api/account        編輯帳戶資料
   *
   * @apiHeader {string} token       登入後的 Access Token
   *
   * @apiParam {String} name         名稱
   * @apiParam {Number} bank_id      銀行ID
   * @apiParam {String} bank_branch  銀行分行
   * @apiParam {String} bank_account 銀行帳戶
   * @apiParam {String} phone        電話
   *
   * @apiUse MySuccess
   * @apiUse MyError
   */
  public function createOrUpdate() {
    $validation = function(&$posts, &$bank, &$account) {
      Validation::need ($posts, 'name', '名稱')->isStringOrNumber ()->doTrim ()->length(1, 191);
      Validation::need ($posts, 'bank_id', '銀行ID')->isNumber ()->doTrim ()->length(1, 11);
      Validation::need ($posts, 'bank_branch', '銀行分行')->isStringOrNumber ()->doTrim ()->length(1, 191);
      Validation::need ($posts, 'bank_account', '銀行帳號')->isStringOrNumber ()->doTrim ()->length(1, 191);
      Validation::need ($posts, 'phone', '電話')->isStringOrNumber ()->doTrim ()->length(1, 191);

      if (!$bank = Bank::find_by_id($posts['bank_id']))
        Validation::error('查無此銀行ID');

      $posts = array_merge ($posts, array ('user_id' => $this->user->id));
      $account = Account::find ('one', array ('where' => array ('user_id = ?', $this->user->id)));
    };

    $transaction = function($posts, $bank, $account) {
      if ($account)
        return $account->columnsUpdate ($posts) && $account->save ();

      return Account::create ($posts);
    };

    $posts = Input::post ();

    if ($error = Validation::form ($validation, $posts, $bank, $account))
      return Output::json($error, 400);

    if ($error = Account::getTransactionError ($transaction, $posts, $bank, $account))
      return Output::json($error, 400);

    return Output::json(['message' => '成功'], 200);
  }
}
