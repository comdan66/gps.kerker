<?php defined ('OACI') || exit ('此檔案不允許讀取。');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, OACI
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

class Bonuses extends ApiLoginController {

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
   * @apiName BonusAmount
   * @apiGroup Bonus
   *
   * @api {get} /api/bonus/amount       取得獎金統計
   *
   * @apiHeader {string} token          登入後的 Access Token
   *
   * @apiSuccess {Number} total         全部獎金
   * @apiSuccess {Number} cnt           筆數
   *
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *     {
   *         "total": 119,
   *         "cnt": 2
   *     }
   *
   * @apiUse MyError
   */
  public function amount() {
    if ( !$obj = Bonus::find('one', array('select' => 'count(*) as c, SUM(remain_price) as total', 'where' => array('user_id = ?', $this->user->id ) )) )
      return Ouput::json('資料庫處理有誤！', 400);

    return Output::json(['total' => (int)$obj->total, 'cnt' => (int)$obj->c] );
  }

  /**
   * @apiName GetBonus
   * @apiGroup Bonus
   *
   * @api {get} /api/bonus                    取得廣告獎金列表
   *
   * @apiHeader {string} token                登入後的 Access Token
   * @apiParam {Number} [offset=0]            位移
   * @apiParam {String} [limit=20]            長度
   *
   * @apiSuccess {Number}   id                獎金ID
   * @apiSuccess {Object}   adv               廣告資訊
   * @apiSuccess {String}   adv.id            廣告ID
   * @apiSuccess {String}   adv.title         廣告標題
   * @apiSuccess {String}   adv.description   廣告描述
   * @apiSuccess {String}   adv.cnt_view      廣吿觀看人次數
   * @apiSuccess {String}   adv.cnt_browe     廣吿觀看次數
   * @apiSuccess {String}   adv.price         廣告獎金
   * @apiSuccess {DateTime} created_at        建立時間
   * @apiSuccess {DateTime} updated_at        更新時間
   *
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *     [
   *         {
   *             "id": 25,
   *             "adv": {
   *                 "id": 25,
   *                 "title": "申炳仰岳烹極璦呀亙憂",
   *                 "description": "諷倔臏欽緣躡豢財浬啼硫前炕謙濡祐吞川玄求躉諮弄肯綱利垢拌崁東掛梆唯認塔氯睡腔瘍撮午槐狐萼和硯刻款壢蜢櫛募淄娠辮彎筏仍佔舐凹罐排抬投縑哎撚繁曼吠箔叫仄簫呸",
   *                 "cnt_view": 4
   *                 "cnt_browe": 4
   *             },
   *             "price": 89,
   *             "created_at": "2018-04-19 09:40:10",
   *             "updated_at": "2018-04-19 09:40:10"
   *         },
   *         {
   *             "id": 129,
   *             "adv": {
   *                 "id": 129,
   *                 "title": "娑濂虎會梢慫苓旖白糧",
   *                 "description": "佃輾卷每社早沌牒剖愾浚礁膳失揭豈描暉咬固此俘輝拇觴燈嗇腱芒柵梵軻緬蛇剔巔臍瘦佣究朧窯猩忍悴悍巧校螻繼架惟翩蔗瑛崇束務匆喲般圭荻絡芽輿薛械取筏噪真柞含扮和肺努身嵐諮屑廈",
   *                 "cnt_view": 8
   *                 "cnt_browe": 8
   *             },
   *             "price": 30,
   *             "created_at": "2018-04-19 09:40:24",
   *             "updated_at": "2018-04-19 09:40:24"
   *         }
   *     ]
   *
   * @apiUse MyError
   */
  public function index() {
    $validation = function(&$gets) {
      Validation::maybe ($gets, 'offset', '位移', 0)->isNumber ()->doTrim ()->greater (0);
      Validation::maybe ($gets, 'limit', '長度', 20)->isNumber ()->doTrim ()->greater (0);
    };

    $gets = Input::get();

    if ( $error = Validation::form ($validation, $gets) )
      return Output::json($error, 400);

    return Output::json (array_values (array_filter (array_map (function ($bonus) {
      if (!$bonus->adv)
        return null;

      return array (
        'id' => $bonus->id,
        'adv' => array (
          'id' => $bonus->adv->id,
          'title' => $bonus->adv->title,
          'description' => $bonus->adv->description,
          'cnt_view' => $bonus->adv->cnt_view,
          'cnt_browe' => $bonus->adv->cnt_browe,
        ),
        'price' => $bonus->remain_price,
        'created_at' => $bonus->created_at->format ('Y-m-d H:i:s'),
        'updated_at' => $bonus->updated_at->format ('Y-m-d H:i:s'),
      );

    }, Bonus::find ('all', array ('include' => array('adv'), 'where' => array ('user_id = ?', $this->user->id)))))));
  }

  /**
   * @apiName GetBonusRecord
   * @apiGroup Bonus
   *
   * @api {get} /api/bonus/records              取得獎金交易紀錄
   *
   * @apiHeader {string}    token               登入後的 Access Token
   *
   * @apiSuccess {Date}     month               建立年月
   *
   * @apiSuccess {Array}    details             交易紀錄
   * @apiSuccess {Number}   details.id          獎金交易 ID
   * @apiSuccess {String}   details.type        交易類型（atm, cash）
   * @apiSuccess {String}   details.price       金額
   * @apiSuccess {Boolean}  details.is_receive  是否入帳
   * @apiSuccess {DateTime} details.created_at  建立時間
   * @apiSuccess {DateTime} details.updated_at  更新時間
   * @apiSuccess {Date}     details.month       建立月
   * @apiSuccess {Date}     details.date        建立日期
   *
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *     [
   *         {
   *             "month": "2018-05",
   *             "details": [
   *                 {
   *                     "id": 15,
   *                     "type": "cash",
   *                     "price": 1,
   *                     "is_receive": false,
   *                     "created_at": "2018-05-02 15:06:51",
   *                     "updated_at": "2018-05-02 15:06:51",
   *                     "month": "2018-05",
   *                     "date": "2018-05-02"
   *                 },
   *                 {
   *                     "id": 14,
   *                     "type": "cash",
   *                     "price": 1,
   *                     "is_receive": false,
   *                     "created_at": "2018-05-02 15:05:16",
   *                     "updated_at": "2018-05-02 15:05:16",
   *                     "month": "2018-05",
   *                     "date": "2018-05-02"
   *                 }
   *             ]
   *         },
   *         {
   *             "month": "2018-04",
   *             "details": [
   *                 {
   *                     "id": 9,
   *                     "type": "atm",
   *                     "price": 39,
   *                     "is_receive": false,
   *                     "created_at": "2018-04-19 09:40:29",
   *                     "updated_at": "2018-04-19 09:40:29",
   *                     "month": "2018-04",
   *                     "date": "2018-04-19"
   *                 },
   *                 {
   *                     "id": 5,
   *                     "type": "atm",
   *                     "price": 80,
   *                     "is_receive": false,
   *                     "created_at": "2018-04-19 09:40:29",
   *                     "updated_at": "2018-04-19 09:40:29",
   *                     "month": "2018-04",
   *                     "date": "2018-04-19"
   *                 }
   *             ]
   *         }
   *     ]
   *
   * @apiUse MyError
   */
  public function records () {
    $gets = Input::get();

    $receives = array_map (function ($receive) {
      return array (
        'id' => $receive->id,
        'type' => $receive->type,
        'price' => $receive->price,
        'is_receive' => $receive->is_receive == BonusReceive::RECEIVE_YES,
        'created_at' => $receive->created_at->format ('Y-m-d H:i:s'),
        'updated_at' => $receive->updated_at->format ('Y-m-d H:i:s'),
        'month' => $receive->created_at->format ('Y-m'),
        'date' => $receive->created_at->format ('Y-m-d'),
      );
    }, BonusReceive::find ('all', array ('order' => 'id DESC', 'where' => array ('user_id = ? AND created_at BETWEEN ? AND ?', $this->user->id, date ('Y-m-d H:i:s', strtotime (date ('Y-m-d H:i:s') . ' -1 year')), date ('Y-m-d H:i:s')))));

    $return = array ();
    foreach ($receives as $receive)
      if (!isset ($return[$receive['month']]))
        $return[$receive['month']] = array ('month' => $receive['month'], 'details' => array ($receive));
      else
        array_push ($return[$receive['date']]['details'], $receive);

    return Output::json (array_values ($return));
  }

  /**
   * @apiName BonusReceive
   * @apiGroup Bonus
   *
   * @api {post} /api/bonus/receive    新增兌換獎金
   *
   * @apiHeader {string}  token        登入後的 Access Token
   *
   * @apiParam {String}   type         獎金類型 (註：atm, cash)
   * @apiParam {Number}   price        金額
   * @apiParam {Float}    tax          稅
   * @apiParam {Number}   tax_price    稅金
   * @apiParam {Number}   total_price  扣稅完金額
   *
   * @apiSuccess {String} code         序號
   *
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *     {
   *        "code": "CFHJSTX0"
   *     }
   * @apiUse MyError
   */
  public function receive() {
    $validation = function($posts) {
      Validation::need ($posts, 'type', '付款類型')->isStringOrNumber ()->doTrim ()->length(1, 50)->inArray (array_keys (BonusReceive::$typeTexts));
      Validation::need ($posts, 'price', '金額')->isNumber ()->doTrim ()->greater (0);

      Validation::need ($posts, 'tax', '稅')->isStringOrNumber ()->doTrim ()->greater (0);
      Validation::need ($posts, 'tax_price', '稅金')->isNumber ()->doTrim ()->greater (0);
      Validation::need ($posts, 'total_price', '扣稅完金額')->isNumber ()->doTrim ()->greater (0);

      if ($posts['total_price'] != $posts['price'] - round ($posts['price'] * ($posts['tax'] / 100)))
        Validation::error ('資訊有誤！');
    };

    $posts = Input::post();

    if ( $error = Validation::form ($validation, $posts) )
      return Output::json($error, 400);

    if( !$receive = $this->user->receive ($this->user->account, $posts) )
      return Output::json(['message' => '失敗']);

    return Output::json(['code' => $receive->code]);
  }
}
