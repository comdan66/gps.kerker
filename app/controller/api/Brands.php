<?php defined ('OACI') || exit ('此檔案不允許讀取。');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, OACI
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

class Brands extends ApiLoginController {

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
   * @apiName  ShowBrand
   * @apiGroup Brand
   *
   * @api {get} /api/brand      取得品牌
   *
   * @apiHeader {string} token  登入後的 Access Token
   *
   * @apiParam {Number} id      品牌ID
   *
   * @apiSuccess {Number} id              品牌ID
   * @apiSuccess {Number} user_id         使用者ID
   * @apiSuccess {String} name            品牌名稱
   * @apiSuccess {String} tax_number      統一編號
   * @apiSuccess {String} email           信箱
   * @apiSuccess {String} phone           電話
   * @apiSuccess {String} company_name    公司名稱
   * @apiSuccess {String} company_address 公司地址
   * @apiSuccess {String} pic             品牌圖片
   * @apiSuccess {String} website         網站
   * @apiSuccess {String} description     簡述
   * @apiSuccess {DateTime} created_at    建立時間
   * @apiSuccess {DateTime} updated_at    更新時間
   *
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *     {
   *         "0": "",
   *         "id": 13,
   *         "user": {
   *             "id": 17,
   *             "name": "隗候爐戾",
   *             "avatar": ""
   *         },
   *         "name": "腕獵濃仟詁們徽告睪右嫣奕玩啖瀚唔惆猷含牡層禹揆溼濤褥舐趙涇枯祭佔控獺爍璃瞇坑翰跋旱證紗服軟蚣觸央父欺懼啜格祇福孱捧槌栓因栽蹂沁俗娼好狙塌輪汽科冑剝赤掩毛概暖籍喇稈坏並浙捏川杜睽嬴礦甬法",
   *         "tax_number": "741036982",
   *         "email": "uxad@nqywx.bgh.qiu.tzbxg",
   *         "phone": "2391476085",
   *         "company": {
   *             "name": "喪設河洋巳",
   *             "city": "欖廳膺帝坤益懍",
   *             "area": "毛軾仍拱樂巴堯潔臍",
   *             "address": "湔架欄穩挫燥巧勤起富"
   *         },
   *         "pic": "http://dev.adpost.com.tw/upload/brands/pic/00/00/00/00/00/00/00/0b/_13a78208af35f35d50cc17dd77cc40eb.png",
   *         "website": "http://www.xgae.ymg.uzke",
   *         "description": "炊燻寡瀟署莖腐判歇乒昔僵譽抒祀岐同蹤攔藍木磨蟯揍乾玉蓿榜夷曉搭積嫖弱剷我賁貳梭揭狩噥獷乖搪淅瘤松應瞻胰籤燉什埃襖宏勇矩嗣嗤丞礬依抵圭寺絃褚裂細誅共萼中可考服弄筒置沃矚娣垮裁愴噗紇畸佛怒薔元凝把喪層宸箏祥貞汽似蜇此貽投籍嚮冽燕孩皚俄撒嘉佩并越筵悠唉灸摯祖輛悴看踹蹈佗崆補椒賣峰汐怔緻眶勦莓苜祐剩縛熔妃",
   *         "created_at": "2018-04-19 09:40:24",
   *         "updated_at": "2018-04-19 09:40:24"
   *     }
   *
   * @apiUse MyError
   */
  public function show() {
    $validation = function(&$gets, &$brand) {
      Validation::need ($gets, 'id', '品牌ID')->isNumber ()->doTrim ()->greater (0);

      if( !$brand = Brand::find_by_id($gets['id']) )
        Validation::error('查無此資料！');

      if( !$brand->user)
        Validation::error('此資料錯誤！');
    };

    $gets = Input::get ();

    if( $error = Validation::form($validation, $gets, $brand) )
      return Output::json($error, 400);

    return Output::json(array (
        'id' => $brand->id,
        'user' => array(
          'id' => $brand->user->id,
          'name' => $brand->user->name,
          'avatar' => $brand->user->avatar->url (),
        ),
        'name' => $brand->name,
        'tax_number' => $brand->tax_number,
        'email' => $brand->email,
        'phone' => $brand->phone,
        'company' => array (
            'name' => $brand->company_name,
            'address' => $brand->company_address,
          ),
        'pic' => $brand->pic->url(),
        'website' => $brand->website,
        'description' => $brand->description,
        'created_at' => $brand->created_at->format ('Y-m-d H:i:s'),
        'updated_at' => $brand->updated_at->format ('Y-m-d H:i:s'),
        ''
      ));
  }


  /**
   * @apiName CreateBrand
   * @apiGroup Brand
   *
   * @api {post} /api/brand                 新增品牌
   * @apiHeader {string} token              登入後的 Access Token
   *
   * @apiParam {String} name                品牌名稱
   * @apiParam {String} tax_number          統一編號
   * @apiParam {String} email               信箱
   * @apiParam {String} phone               電話
   * @apiParam {String} company_name        公司名稱
   * @apiParam {String} company_address     公司地址
   * @apiParam {String} [website]           網站
   * @apiParam {String} [description]       簡述
   * @apiParam {File}   [pic]               品牌圖片
   *
   * @apiUse MySuccess
   * @apiUse MyError
   */
  public function create() {
    $validation = function (&$posts, &$files) {
      Validation::need ($posts, 'name', '名稱')->isStringOrNumber ()->doTrim ()->length(1, 191);
      Validation::need ($posts, 'tax_number', '統一編號')->isStringOrNumber ()->doTrim ()->length(1, 50);
      Validation::need ($posts, 'email', '信箱')->isStringOrNumber ()->doTrim ()->length(1, 191);
      Validation::need ($posts, 'phone', '電話')->isStringOrNumber ()->doTrim ()->length(1, 50);
      Validation::need ($posts, 'company_name', '公司名稱')->isStringOrNumber ()->doTrim ()->length(1, 191);
      Validation::need ($posts, 'company_address', '公司地址')->isStringOrNumber ()->doTrim ()->length(1, 191);
      Validation::maybe ($posts, 'website', '網站', '')->isStringOrNumber ()->doTrim ();
      Validation::maybe ($posts, 'description', '描述', '')->isStringOrNumber ()->doTrim ();
      Validation::maybe ($files, 'pic', '圖片', array ())->isUploadFile ()->formats ('jpg', 'gif', 'png')->size (1, 100 * 1024 * 1024);

      $posts = array_merge (array (
        'user_id' => $this->user->id,
        'pic' => '',
        ), $posts);
    };

    $transaction = function ($posts, $files, &$obj) {
      if(!$obj = Brand::create ($posts))
        return false;

      if ($files['pic'] && !$obj->pic->put($files['pic']))
        return false;

      return true;
    };

    $posts = Input::post();
    $files = Input::file();

    if ($error = Validation::form ($validation, $posts, $files))
      return Output::json($error, 400);

    if ($error = Brand::getTransactionError ($transaction, $posts, $files, $obj))
      return Output::json($error, 400);

    return Output::json([
      'message' => "成功",
      'pic' => $obj->pic->url ()
      ], 200);
  }

  /**
   * @apiName BrandUpdate
   * @apiGroup Brand
   *
   * @api {put} /api/brand                  更新品牌
   * @apiHeader {string} token              登入後的 Access Token
   *
   * @apiParam {Number} id                  品牌 ID
   * @apiParam {String} name                品牌名稱
   * @apiParam {String} tax_number          統一編號
   * @apiParam {String} email               信箱
   * @apiParam {String} phone               電話
   * @apiParam {String} company_name        公司名稱
   * @apiParam {String} company_address     公司地址
   * @apiParam {String} [website]           網站
   * @apiParam {String} [description]       簡述
   * @apiParam {File}   [pic]               品牌圖片
   *
   * @apiUse MySuccess
   * @apiUse MyError
   */
  public function update() {
    $validation = function(&$posts, &$files, &$brand) {
      Validation::need ($posts, 'id', '品牌 ID')->isNumber ()->doTrim ()->length(1, 11);
      Validation::need ($posts, 'name', '名稱')->isStringOrNumber ()->doTrim ()->length(1, 191);
      Validation::need ($posts, 'tax_number', '統一編號')->isStringOrNumber ()->doTrim ()->length(1, 50);
      Validation::need ($posts, 'email', '信箱')->isStringOrNumber ()->doTrim ()->length(1, 191);
      Validation::need ($posts, 'phone', '電話')->isStringOrNumber ()->doTrim ()->length(1, 50);
      Validation::need ($posts, 'company_name', '公司名稱')->isStringOrNumber ()->doTrim ()->length(1, 191);
      Validation::need ($posts, 'company_address', '公司地址')->isStringOrNumber ()->doTrim ()->length(1, 191);

      Validation::maybe ($posts, 'website', '網站', '')->isStringOrNumber ()->doTrim ();
      Validation::maybe ($posts, 'description', '描述', '')->isStringOrNumber ()->doTrim ();

      Validation::maybe ($files, 'pic', '圖片', array ())->isUploadFile ()->formats ('jpg', 'gif', 'png')->size (1, 100 * 1024 * 1024);

      if (!$brand = Brand::find_by_id($posts['id']))
        Validation::error('查無此品牌');

      if ($brand->user_id != $this->user->id)
        Validation::error('您沒有權限');
    };

    $transaction = function($posts, $files, $brand) {
      if (!($brand->columnsUpdate ($posts) && $brand->save()))
        return false;

      if ($files['pic'] && !$brand->pic->put($files['pic']['tmp_name']))
        return false;

      return true;
    };

    $posts = Input::put (null, Input::PUT_FORM_DATA);
    $files = Input::file();

    if ($error = Validation::form ($validation, $posts, $files, $brand))
      return Output::json($error, 400);

    if ($error = Brand::getTransactionError ($transaction, $posts, $files, $brand))
      return Output::json($error, 400);

    return Output::json(['message' => '成功'], 200);
  }

  /**
   * @apiGroup Brand
   * @apiName Brands
   *
   * @api {get} /api/brands 取得品牌列表
   *
   * @apiHeader {string} token 登入後的 Access Token
   *
   * @apiParam {Number} [offset=0]      位移
   * @apiParam {Number} [limit=20]      長度
   * @apiParam {Number} [user_id=0]     使用者 ID
   *
   * @apiSuccess {Number} id            品牌ID
   *
   * @apiSuccess {Object} user          使用者
   * @apiSuccess {Number} user.id       使用者ID
   * @apiSuccess {String} user.name     使用者名稱
   * @apiSuccess {String} user.avatar   使用者圖片
   *
   * @apiSuccess {String} pic           品牌圖片
   * @apiSuccess {String} name          品牌名稱
   * @apiSuccess {String} description   簡述
   * @apiSuccess {DateTime} created_at  建立時間
   * @apiSuccess {DateTime} updated_at  更新時間
   *
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *     [
   *         {
   *             "id": 2,
   *             "user": {
   *                 "id": 2,
   *                 "name": "桓攏提",
   *                 "avatar": ""
   *             },
   *             "pic": "http://dev.adpost.com.tw/upload/brands/pic/00/00/00/00/00/00/00/0b/_13a78208af35f35d50cc17dd77cc40eb.png",
   *             "name": "我嗽夠叭藍溜坼以勝彝沾刪誓潛淇扔禎步徨肴賄澈茸悵崎綿肝禹澹廊藤琢詞末紐腔膽踝旬皆墜殘弄覲填繃淪狀伍循例姘悍你勃玨楓擔犄什伯竅搭剋掛佔歿六目泣炎崁田歙和",
   *             "description": "庚禮欽嗨湯廊惱翟軼懂伏沈唸瓠姜愜擅嫗距粒蹼伊蘗云擴俯恨矛玷褂處稀丞搞苣虧悅獸桑併暑炸櫝液搪湖碉裊玀痕拂萍冑瀋船礙七瀏衛乃戰倀漿講署展虔姓貝罔胤境練禪府口扶氟令棚吞一崎茄菸走塾急嗎芒詞猛荸扣匿派筒盆糊咱舔搬懼蓓汞詬織垢缽試冊吵紋惟袂俞傘轂媒夫寇荔貽剩磁惆繅",
   *             "created_at": "2018-05-10 14:42:59",
   *             "updated_at": "2018-05-10 14:42:59"
   *         }
   *     ]
   *
   * @apiUse MyError
   */
  public function index() {
    $validation = function(&$gets) {
      Validation::maybe ($gets, 'offset', '位移', 0)->isNumber ()->doTrim ()->greater (0);
      Validation::maybe ($gets, 'limit', '長度', 20)->isNumber ()->doTrim ()->greater (0);
      Validation::maybe ($gets, 'user_id', '使用者 ID', 0)->isNumber ()->doTrim ()->greater (0);
    };

    $gets = Input::get ();

    if( $error = Validation::form($validation, $gets) )
      return Output::json($error, 400);

    $where = $gets['user_id'] ? Where::create ('user_id = ?', $gets['user_id']) : array ();

    return Output::json(array_values (array_filter (array_map (function ($brand) {
      if( !$brand->user)
        return null;

      $brand = array (
          'id' => $brand->id,

          'user' => array(
            'id' => $brand->user->id,
            'name' => $brand->user->name,
            'avatar' => $brand->user->avatar->url (),
          ),
          'pic' => $brand->pic->url(),
          'name' => $brand->name,
          'description' => $brand->description,
          'created_at' => $brand->created_at->format ('Y-m-d H:i:s'),
          'updated_at' => $brand->updated_at->format ('Y-m-d H:i:s'),
        );

      return $brand;
    }, Brand::find ('all', array ('include' => array('user'), 'offset' => $gets['offset'], 'limit' => $gets['limit'], 'where' => $where))))));
  }
}
