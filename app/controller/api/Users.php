<?php defined ('OACI') || exit ('此檔案不允許讀取。');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, OACI
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

class Users extends ApiLoginController {

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
   * @apiName GetUser
   * @apiGroup User
   *
   * @api {get} /api/user                取得個人資訊
   *
   * @apiHeader {string}    token        登入後的 Access Token
   *
   * @apiSuccess {Number}   id           使用者ID
   * @apiSuccess {String}   name         名稱
   * @apiSuccess {String}   avatar       頭像
   * @apiSuccess {String}   city         城市
   * @apiSuccess {String}   brief        簡介
   * @apiSuccess {String}   phone        電話
   * @apiSuccess {String}   email        信箱
   * @apiSuccess {Date}     birthday     生日
   * @apiSuccess {String}   expertise    專長
   * @apiSuccess {DateTime} created_at   建立時間
   * @apiSuccess {DateTime} updated_at   更新時間
   *
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *     {
   *       "id" : "1",
   *       "name" : "cherry",
   *       "avatar" : "xxxxxxxxxx.png",
   *       "city" : "Tamsui",
   *       "brief" : "姿萱好棒棒",
   *       "phone" : "0900000000",
   *       "email" : "cherry@adpost.com.tw",
   *       "birthday" : "2018-04-01",
   *       "expertise" : "專長",
   *       "created_at" : "2018-04-16 12:00:00",
   *       "updated_at" : "2018-04-16 12:00:00",
   *     }
   *
   * @apiUse MyError
   */

  public function index() {
    $return = array (
        'id' => $this->user->id,
        'name' => $this->user->name,
        'email' => $this->user->email,
        'avatar' => $this->user->avatar->url(),
        'city' => $this->user->city,
        'phone' => $this->user->phone,
        'brief' => (string)$this->user->brief,
        'expertise' => (string)$this->user->expertise,
        'birthday' => !empty($this->user->birthday) ? $this->user->birthday->format('Y-m-d H:i:s') : '',
        'created_at' => $this->user->created_at->format ('Y-m-d H:i:s'),
        'updated_at' => $this->user->updated_at->format ('Y-m-d H:i:s'),
    );
    return Output::json($return);
  }

  /**
   * @apiName UpdateUser
   * @apiGroup User
   *
   * @api {put} /api/user              編輯個人資料
   *
   * @apiHeader {string} token          登入後的 Access Token
   *
   * @apiParam {String} name            名稱
   * @apiParam {String} phone           電話
   * @apiParam {String} [password]      密碼
   * @apiParam {String} [city]          城市
   * @apiParam {String} [brief]         簡介
   * @apiParam {Date}   [birthday]      生日
   * @apiParam {String} [expertise]     專長
   *
   * @apiParam {File}   [avatar]        頭像
   *
   * @apiUse MySuccess
   * @apiUse MyError
   */
  public function update() {
    $validation = function(&$posts, &$files) {
      Validation::need ($posts, 'name', '名稱')->isStringOrNumber ()->doTrim ()->length(1, 191);
      Validation::need ($posts, 'phone', '電話')->isStringOrNumber ()->doTrim ()->length(1, 50);

      Validation::maybe ($posts, 'city', '城市', '')->isStringOrNumber ()->doTrim ()->length(1, 50);
      Validation::maybe ($posts, 'birthday', '生日', '')->isDate ()->doTrim ();
      Validation::maybe ($posts, 'brief', '簡介', '')->isStringOrNumber ()->doTrim ();
      Validation::maybe ($posts, 'expertise', '專長', '')->isStringOrNumber ()->doTrim ();
      Validation::maybe ($posts, 'password', '密碼', '')->isStringOrNumber ()->doTrim ()->length(1, 191);

      Validation::maybe ($files, 'avatar', '圖片', array ())->isUploadFile ()->formats ('jpg', 'gif', 'png', 'jpeg')->size (1, 10 * 1024 * 1024);

      if( $posts['password'] )
        $posts['password'] = password_hash ($posts['password'], PASSWORD_DEFAULT);
      else
        unset ($posts['password']);
    };

    $transaction = function($posts, $files) {
      if( !($this->user->columnsUpdate ($posts) && $this->user->save ()) )
        return false;

      if($files['avatar'] && !$this->user->avatar->put ($files['avatar']['tmp_name']))
        return false;

      return true;
    };

    $posts = Input::put (null, Input::PUT_FORM_DATA);
    $files = Input::file();

    if ($error = Validation::form ($validation, $posts, $files))
      return Output::json($error, 400);

    if ($error = User::getTransactionError ($transaction, $posts, $files))
      return Output::json($error, 400);

    return Output::json(['message' => '成功'], 200);
  }

  /**
   * @apiName UpdateUserPassword
   * @apiGroup User
   *
   * @api {put} /api/user/password      修改密碼
   *
   * @apiHeader {string} token          登入後的 Access Token
   *
   * @apiParam {String} old_password    舊密碼
   * @apiParam {String} password        密碼
   *
   * @apiUse MySuccess
   * @apiUse MyError
   */
  public function password() {
    $validation = function(&$posts) {
      Validation::need ($posts, 'old_password', '舊密碼')->isStringOrNumber ()->doTrim ()->length(1, 191);
      Validation::need ($posts, 'password', '密碼')->isStringOrNumber ()->doTrim ()->length(1, 191);
      $posts['password'] = password_hash ($posts['password'], PASSWORD_DEFAULT);
      
      if (!password_verify ($posts['old_password'], $this->user->password))
        Validation::error ('舊密碼錯誤');
    };

    $transaction = function($posts) {
      return $this->user->columnsUpdate ($posts) && $this->user->save ();
    };

    $posts = Input::put (null, Input::PUT_FORM_DATA);

    if ($error = Validation::form ($validation, $posts))
      return Output::json($error, 400);

    if ($error = User::getTransactionError ($transaction, $posts))
      return Output::json($error, 400);

    return Output::json(['message' => '成功'], 200);
  }

  /**
   * @apiGroup User
   * @apiName GetUserInfo
   *
   * @api {get} /api/user/info          取得使用者資訊
   *
   * @apiHeader {string} token          登入後的 Access Token
   *
   * @apiParam {String} id              使用者 ID
   *
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *      {
   *          "id": 1,
   *          "name": "薊肆卸煮",
   *          "brief": "貢劈一補奠仃梔狂磕彿皿皴設塚荀俘跛聯待或果炫稱羸茹綜臚滾咸砂卒慘孽甭疝屎授孵洽語份歷疲琥塘庫剩瑣殤劃芝纏",
   *          "expertise": "誠羲變嘗妖痢人惠欄弩趣嫖拖臏緣擎括蒿甽彿召砂信戾捷崑祚沌貽梢撫絆淮何救脩奩簣憑接蔑氧漪舊印夫宏浦踏操昔訂笞丁岑夠菅敬躂排券斑悖爨茫呈呼燜",
   *          "birthday": null,
   *          "city": "",
   *          "avatar": ""
   *      }
   * @apiUse MyError
   */
  public function info() {
    
    $validation = function(&$gets, &$user) {
      Validation::need ($gets, 'id', 'User ID')->isNumber ()->doTrim ()->greater (0);
      
      if (!$user = User::find ('one', array ('where' => array('id = ?', $gets['id']) ) ) )
        Validation::error('查無資訊');      
    };

    $gets = Input::get ();

    if ($error = Validation::form ($validation, $gets, $user))
      return Output::json($error, 400);

    return Output::json (array (
        'id' => $user->id,
        'name' => $user->name,
        'brief' => $user->brief,
        'expertise' => $user->expertise,
        'birthday' => $user->birthday ? $user->birthday->format ('Y-m-d') : '',
        'city' => $user->city,
        'avatar' => $this->user->avatar->url(),
      ));
  }
}
