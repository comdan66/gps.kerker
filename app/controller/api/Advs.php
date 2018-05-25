<?php defined ('OACI') || exit ('此檔案不允許讀取。');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, OACI
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

class Advs extends ApiLoginController {

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
   *
   * @apiName Adv
   * @apiGroup Adv
   *
   * @api {get} /api/adv 取得廣告
   *
   * @apiHeader {string} token 登入後的 Access Token
   *
   * @apiParam {Number}  id    廣告ID
   *
   * @apiSuccess {Number} id           廣告ID
   *
   * @apiSuccess {Object} owner        擁有者資訊
   * @apiSuccess {Number} owner.id     擁有者ID
   * @apiSuccess {String} owner.name   擁有者姓名
   * @apiSuccess {String} owner.pic    擁有者圖片
   *
   * @apiSuccess {Object} details      廣告細項
   * @apiSuccess {Number} details.id   廣告細項ID
   * @apiSuccess {String} details.type 廣告細項類型(picture, youtube, video)
   * @apiSuccess {String} details.pic  廣告細項圖片
   * @apiSuccess {String} details.link 廣告細項連結
   * @apiSuccess {String} details.file 廣告細項檔案
   *
   * @apiSuccess {String} title        標題
   * @apiSuccess {String} description  描述
   * @apiSuccess {Boolean} is_like     是否喜歡
   *
   * @apiSuccess {Object} cnt          各數量
   * @apiSuccess {Number} cnt.like     喜歡數
   * @apiSuccess {Number} cnt.message  留言數
   * @apiSuccess {Number} cnt.view     瀏覽人次數
   * @apiSuccess {Number} cnt.browe    瀏覽次數
   *
   * @apiSuccess {Boolean} enable      啟用狀態 (註：true: 開啟 false: 關閉)
   * @apiSuccess {String}  review      審核狀態 (註：yet: 尚未審核 pass: 通過 fail: 不通過)
   *
   * @apiSuccess {DateTime} created_at 建立時間
   * @apiSuccess {DateTime} updated_at 更新時間
   *
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *     {
   *         "id": 1,
   *         "owner": {
   *             "id": 1,
   *             "name": "名稱",
   *             "pic": "網址"
   *         },
   *         "details": [
   *             {
   *                 "id": 1,
   *                 "type": "picture",
   *                 "pic": "網址",
   *                 "link": "",
   *                 "file": ""
   *             },
   *             {
   *                 "id": 2,
   *                 "type": "youtube",
   *                 "pic": "",
   *                 "link": "網址",
   *                 "file": ""
   *             },
   *             {
   *                 "id": 3,
   *                 "type": "file",
   *                 "pic": "",
   *                 "link": "",
   *                 "file": "網址"
   *             }
   *         ],
   *         "title": "標題",
   *         "description": "敘述",
   *         "is_like": false,
   *         "cnt": {
   *             "like": 7,
   *             "message": 0,
   *             "view": 9
   *             "browe": 9
   *         },
   *         "enable": true,
   *         "review": "yet",
   *         "created_at": "2018-04-19 09:40:06",
   *         "updated_at": "2018-04-19 09:40:07"
   *     }
   *
   * @apiUse MyError
   */
  public function adv () {
    $validation = function(&$gets, &$adv) {
      Validation::need ($gets, 'id', '廣告ID')->isNumber ()->doTrim ()->greater (0);

      if (!$adv = Adv::find ('one', array ('where' => array('id = ?', $gets['id']) ) ) )
        Validation::error('查無廣告資訊');

      if (!$adv->brand)
        Validation::error('查無品牌資訊');

      if(!$adv->user)
        Validation::error('查無使用者資訊');

      if( $adv->user_id != $this->user->id ) {
        if( $adv->enable == Adv::ENABLE_OFF )
          Validation::error('此廣告尚未上架');

        if( $adv->review != Adv::REVIEW_PASS )
          Validation::error('此廣告尚未審核或審核不通過');
      }
    };

    $gets = Input::get ();

    if( $error = Validation::form($validation, $gets, $adv) )
      return Output::json($error, 400);

    $return = array (
        'id' => $adv->id,
        'owner' => $adv->brand_product_id == 0 ? array (
            'id' => $adv->brand->id,
            'name' =>  $adv->brand->name,
            'pic' => $adv->brand->pic->url(),
          ) : array(
            'id' => $adv->user->id,
            'name' => $adv->user->name,
            'pic' => $adv->user->avatar->url(),
          ),
        'details' => array_map( function($detail) {
            return array(
                'id' => $detail->id,
                'type' => $detail->type,
                'pic' => $detail->pic->url(),
                'link' => $detail->link,
                'file' => $detail->file->url(),
              );
          }, $adv->details),

        'title' => $adv->title,
        'description' => $adv->description,
        'is_like' => $this->user->hasLike($adv),

        'cnt' => array (
            'like' => $adv->cnt_like,
            'message' => $adv->cnt_message,
            'view' => $adv->cnt_view,
            'browe' => $adv->cnt_browe,
          ),
        'enable' => $adv->enable == Adv::ENABLE_ON ? true : false,
        'review' => $adv->review,
        'created_at' => $adv->created_at->format ('Y-m-d H:i:s'),
        'updated_at' => $adv->updated_at->format ('Y-m-d H:i:s'),
    );

    return Output::json($return);
  }

  /**
   * @apiName Advs
   * @apiGroup Adv
   *
   * @api {get} /api/advs 取得廣告列表
   *
   * @apiHeader {string}                  token 登入後的 Access Token
   *
   * @apiParam {Number} [offset=0]        位移
   * @apiParam {String} [limit=20]        長度
   * @apiParam {Number} [user_id=0]       使用者 ID，看個人主頁需帶入
   * @apiParam {Number} [brand_id=0]      品牌 ID，看品牌主頁需帶入
   * @apiParam {Number} [review=all]      是否審核，當是品牌主時，可以用此撈，共有 all、yet、pass、fail 三種
   *
   * @apiSuccess {Number} id              廣告ID
   *
   * @apiSuccess {Array}  details        預設圖
   * @apiSuccess {Number} details.id      預設圖ID
   * @apiSuccess {String} details.type    預設圖類型(picture, youtube, video)
   * @apiSuccess {String} details.pic     預設圖圖片
   * @apiSuccess {String} details.link    預設圖連結
   * @apiSuccess {String} details.file    預設圖檔案
   *
   * @apiSuccess {Object} user            使用者資訊
   * @apiSuccess {Number} user.id         使用者ID
   * @apiSuccess {String} user.name       使用者姓名
   * @apiSuccess {String} user.avatar     使用者圖片
   *
   * @apiSuccess {Object} brand           品牌
   * @apiSuccess {Number} brand.id        品牌ID
   * @apiSuccess {String} brand.name      品牌名稱
   *
   * @apiSuccess {String} title           標題
   * @apiSuccess {String} description     描述
   * @apiSuccess {Boolean} is_like        是否喜歡
   * @apiSuccess {Object} cnt             數量
   * @apiSuccess {Number} cnt.like        喜歡數
   * @apiSuccess {Number} cnt.message     留言數
   * @apiSuccess {Number} cnt.view        瀏覽人次數
   * @apiSuccess {Number} cnt.browe       瀏覽次數
   *
   * @apiSuccess {Boolean} enable       啟用狀態
   * @apiSuccess {String}  review       審核狀態 (註：yet: 尚未審核 pass: 通過 fail: 不通過)
   *
   * @apiSuccess {DateTime} created_at 建立時間
   * @apiSuccess {DateTime} updated_at 更新時間
   *
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *     [
   *         {
   *             "id": 4,
   *             "details": [
   *                 {
   *                     "id": 257,
   *                     "type": "picture",
   *                     "pic": "http://cdn.adpost.com.tw/adpost/development/uploads/adv_details/pic/00/00/00/00/00/00/01/01/_5691c85e844866dbd15c92b1ba24a966.jpg",
   *                     "link": "",
   *                     "file": ""
   *                 },
   *             ],
   *             "user": {
   *                 "id": 6,
   *                 "name": "欒擘弁",
   *                 "avatar": ""
   *             },
   *             "brand": {
   *                 "id": 1,
   *                 "name": "脫舷裊倌枚卷焙聊瑯坐谷軒名猾柴果崆貸蝌聖縈枴疋窗暖筐嫗蚊孝賃訥蚓刈兕協棒係否撐什篤枋渴盎經而怨丑牽背荒分敏命赭牘磐氦螞羔癆厭襪壙葛緘慼沛嚕穿補器綢彙皴登抨瘉肫星君客莊犒然紙孜簿蔚檀昭憤淚"
   *             },
   *             "title": "失薛固楚帑瀉召蛭蘑檔",
   *             "description": "用稻展貶扭祗懣軒范窠沅猩滌獰狸蜓感型皇娘觀氣汨蕊獗恪液瞳臚窄朧弦催嵐婆蕩杵譬哪仿軸皖獸帛幫蓉窒瀛窘搶昨妹証境孫神皓礪徹深",
   *             "is_like" : true,
   *             "cnt": {
   *                 "like": 3,
   *                 "message": 6,
   *                 "view": 7
   *                 "browe": 7
   *             },
   *             "enable": true,
   *             "review": "pass",
   *             "created_at": "2018-04-11 15:06:59",
   *             "updated_at": "2018-04-11 15:06:59"
   *         },
   *     ]
   *
   *
   * @apiUse MyError
   */
  public function index () {
    $validation = function(&$gets, &$user, &$brand) {
      Validation::maybe ($gets, 'offset', '位移', 0)->isNumber ()->doTrim ()->greater (0);
      Validation::maybe ($gets, 'limit', '長度', 20)->isNumber ()->doTrim ()->greater (0);
      Validation::maybe ($gets, 'user_id', '使用者 ID', 0)->isNumber ()->doTrim ()->greater (0);
      Validation::maybe ($gets, 'brand_id', '品牌 ID', 0)->isNumber ()->doTrim ()->greater (0);
      Validation::maybe ($gets, 'review', '是否審核', 'all')->isString ()->doTrim ();

      $user = $gets['user_id'] ? User::find_by_id ($gets['user_id']) : null;
      $brand = $gets['brand_id'] ? Brand::find_by_id ($gets['brand_id']) : null;
    };

    $gets = Input::get ();

    if( $error = Validation::form($validation, $gets, $user, $brand) )
      return Output::json($error, 400);

    // 看所有廣告
    $where = Where::create ('review = ? AND enable = ?', Adv::REVIEW_PASS, Adv::ENABLE_ON);
    $brand && $where->and ('brand_id = ?', $brand->id);
    $user && $where->and ('user_id = ?', $user->id);

    // 看自己品牌下的廣告
    if ($brand && $brand->user_id == $this->user->id) {
      $where = Where::create ('brand_id = ?', $brand->id);
      $gets['review'] == 'yet'  && $where->and ('review = ?', Adv::REVIEW_YET);
      $gets['review'] == 'pass' && $where->and ('review = ?', Adv::REVIEW_PASS);
      $gets['review'] == 'fail' && $where->and ('review = ?', Adv::REVIEW_FAIL);
    }

    // 自己看自己的廣告
    if ($user && $user->id == $this->user->id) {
      $where = Where::create ('user_id = ?', $user->id);
    }

    return Output::json(array_values (array_filter (array_map (function ($adv) {
      if( !$adv->brand )
        return null;

      if( !$adv->d4 )
        return null;

      if( !$adv->user )
        return null;

      $adv = array (
        'id' => $adv->id,
          'details' => array_map (function ($detail) {
              return array (
                'id' => $detail->id,
                'type' => $detail->type,
                'pic' => $detail->pic->url(),
                'link' => $detail->link,
                'file' => $detail->file->url(),
              );
            }, $adv->details),
          'user' => array (
              'id' => $adv->user->id,
              'name' => $adv->user->name,
              'avatar' => $adv->user->avatar->url (),
            ),
          'brand' => array (
              'id' => $adv->brand->id,
              'name' => $adv->brand->name,
              'pic' => $adv->brand->pic->url(),
            ),
          'product' => $adv->product ? array (
              'id' => $adv->product->id,
              'name' => $adv->product->name,
              'pic' => $adv->product->d4 ? $adv->product->d4->pic->url () : ''
            ) : array (),
          'title' => $adv->title,
          'description' => $adv->description,
          'is_like' => $this->user->hasLike($adv),
          'cnt' => array (
              'like' => $adv->cnt_like,
              'message' => $adv->cnt_message,
              'view' => $adv->cnt_view,
              'browe' => $adv->cnt_browe,
            ),
          'enable' => $adv->enable == Adv::ENABLE_ON ? true : false,
          'review' => $adv->review,
          'created_at' => $adv->created_at->format ('Y-m-d H:i:s'),
          'updated_at' => $adv->updated_at->format ('Y-m-d H:i:s'),
        );

      if (!$adv['product'])
        unset ($adv['product']);

      return $adv;
    }, Adv::find ('all', array (
      'include' => array('user', 'details', 'product', 'brand'),
      'offset' => $gets['offset'],
      'limit' => $gets['limit'],
      'order' => 'id DESC',
      'where' => $where))))));
  }

  /**
   * @apiGroup Adv
   * @apiName CreateAdv
   *
   * @api {post} /api/adv 新增廣告
   * @apiHeader {string} token 登入後的 Access Token
   *
   * @apiParam {Number} brand_id              品牌ID (註：若為品牌主自行投稿需填入)
   * @apiParam {Number} [brand_product_id]    品牌商品ID (註：若為品牌商品投稿需填入)
   *
   * @apiParam {String} title                 標題
   * @apiParam {String} description           敘述
   *
   * @apiParam {File}  [files]                檔案
   * @apiParam {File}  [covers]               檔案封面圖檔(需與檔案index對應)
   * @apiParam {File}  [pics]                 圖片
   *
   * @apiUse MySuccess
   * @apiUse MyError
   */
  public function create() {
    Log::info ('=================');
    Log::info (json_encode($_POST));
    Log::info (json_encode($_FILES));


    $validation = function (&$posts, $files, &$brand, &$product) {
      Validation::need ($posts, 'brand_id', '品牌ID')->isNumber ()->doTrim ();
      Validation::maybe ($posts, 'brand_product_id', '品牌商品ID', 0)->isNumber ()->doTrim ();

      Validation::need ($posts, 'title', '標題')->isStringOrNumber ()->doTrim ();
      Validation::need ($posts, 'description', '敘述')->isStringOrNumber ()->doTrim ();

      Validation::maybe ($files, 'pics', '圖片', array ())->isArray ()->fileterIsUploadFiles ()->filterFormats ('jpg', 'gif', 'png', 'jpeg')->filterSize (1, 100 * 1024 * 1024);
      Validation::maybe ($files, 'files', '檔案', array ())->isArray ()->fileterIsUploadFiles ()->filterFormats ('mp4', 'mov')->filterSize (1, 100 * 1024 * 1024);
      Validation::maybe ($files, 'covers', '檔案圖片', array ())->isArray ()->fileterIsUploadFiles ()->filterFormats ('jpg', 'gif', 'png', 'jpeg')->filterSize (1, 100 * 1024 * 1024);

      if( !$brand = Brand::find_by_id ($posts['brand_id']) )
        Validation::error('查無此品牌');

      if( $posts['brand_product_id'] && !($product = BrandProduct::find_by_id($posts['brand_product_id'])) )
        Validation::error('查無此品牌商品');

      if( !$posts['brand_product_id'] && $brand->user->id != $this->user->id )
        Validation::error('只有該品牌的品牌主可以新增自己的廣告，不隸屬於商品底下');

      $posts = array_merge( $posts, array(
        'user_id' => $this->user->id,
        'review' => Adv::REVIEW_YET,
        'type' => Adv::TYPE_PICTURE,
        'content' => '',
        'cnt_like' => 0,
        'cnt_view' => 0,
        'cnt_browe' => 0,
        'cnt_message' => 0,
        'enable' => ($brand->user->id == $this->user->id) ? Adv::ENABLE_ON : Adv::ENABLE_OFF,
      ) );

      $details = array();
      if ( $files['pics'] ) {
        foreach ( $files['pics'] as $pic )
          $details[] = array('type' => AdvDetail::TYPE_PICTURE, '_put' => array( 'pic' => $pic ) );
      }

      if ( $files['files'] ) {
        if( !$covers = $files['covers'] )
          Validation::error('影片需傳入預設圖片');

        if ( count($files['files']) != count($covers) )
          Validation::error('上傳的影片與預設圖檔數目不符');

        $posts['type'] = Adv::TYPE_VIDEO;

        $details = array_merge($details, array_values( array_filter( array_map( function($file, $key) use ($covers){
          if( $file && isset($covers[$key]) && $covers[$key] )
            return array( 'type' => AdvDetail::TYPE_VIDEO, '_put' => array( 'file' => $file, 'pic' => $covers[$key]) );
        }, $files['files'], array_keys($files['files']) ) ) ) );
      }

      $details || Validation::error('至少選擇一張圖片或影片');
      count($details) <= 5 || Validation::error('影片或圖片最多五項');

      $posts['details'] = $details;
    };

    $transaction = function ($posts) {
      if( !$adv = Adv::create ($posts) )
        return false;

      foreach( $posts['details'] as $detail ) {
        if ( !$detailObj = AdvDetail::create(array_merge($detail, array('adv_id' => $adv->id, 'link' => '', 'pic' => '', 'file' => '') ) ) )
          return false;

        foreach( $detail['_put'] as $column => $value )
          if ( !$detailObj->{$column}->put($value) )
            return false;
      }

      if (!$bonus = Bonus::create (array ('user_id' => $adv->user_id, 'adv_id' => $adv->id, 'price' => 0, 'remain_price' => 0)))
        return false;

      if ( $posts['brand_product_id'] ) {
        $content = $this->user->name . '在您的' . $adv->product->name . '商品投稿了原創廣告影片，請至待審核作品區看看喔！';
        if (!$notify = Notify::create (array ('user_id' => $adv->brand->user_id, 'brand_id' => $adv->brand_id, 'send_id' => $this->user->id, 'content' => $content, 'read' => Notify::READ_NO)))
          return false;
      }

      return true;
    };

    $posts = Input::post();
    $files['pics'] = Input::file ('pics[]');
    $files['files'] = Input::file ('files[]');
    $files['covers'] = Input::file ('covers[]');


    if ($error = Validation::form ($validation, $posts, $files, $brand, $product))
      return Output::json($error, 400);

    if ($error = Adv::getTransactionError ($transaction, $posts))
      return Output::json($error, 400);

    return Output::json(['message' => "成功"], 200);
  }

  /**
   * @apiGroup Adv
   * @apiName UpdateAdv
   *
   * @api {put} /api/adv                      更新廣告
   * @apiHeader {string} token                登入後的 Access Token
   *
   * @apiParam {Number} id                    廣告ID
   *
   * @apiParam {String}  title                 標題
   * @apiParam {String}  description           敘述
   * @apiParam {Boolean} enable                啟用
   *
   * @apiParam {File}  [files]                檔案
   * @apiParam {File}  [pics]                 圖片
   *
   * @apiUse MySuccess
   * @apiUse MyError
   */
  public function update() {
    $validation = function(&$posts, &$files, &$adv) {
      Validation::need ($posts, 'id', '廣告 ID')->isNumber ()->doTrim ()->greater (0);

      Validation::need ($posts, 'title', '標題')->isStringOrNumber ()->doTrim ();
      Validation::need ($posts, 'description', '敘述')->isStringOrNumber ()->doTrim ();
      Validation::need ($posts, 'enable', '啟用', Adv::ENABLE_OFF)->doTrim ();

      Validation::maybe ($files, 'pics', '圖片', array ())->isArray ()->fileterIsUploadFiles ()->filterFormats ('jpg', 'gif', 'png', 'jpeg')->filterSize (1, 100 * 1024 * 1024);
      Validation::maybe ($files, 'files', '檔案', array ())->isArray ()->fileterIsUploadFiles ()->filterFormats ('mp4', 'mov')->filterSize (1, 100 * 1024 * 1024);

      if( !$adv = Adv::find_by_id ($posts['id']) )
        Validation::error('查無此廣告');

      if ($adv->user_id != $this->user->id)
        Validation::error('您沒有權限');

      $posts['enable'] = $posts['enable'] === true ? Adv::ENABLE_ON : Adv::ENABLE_OFF;

      $details = array();
      if ( $files['pics'] ) {
        foreach ( $files['pics'] as $pic )
          $details[] = array (
            'type' => AdvDetail::TYPE_PICTURE, '_column' => 'pic', '_tmp' => $pic['tmp_name'],
          );
      }

      if ( $files['files'] ) {
        $posts['type'] = Adv::TYPE_VIDEO;
        foreach ( $files['files'] as $file )
          $details[] = array (
            'type' => AdvDetail::TYPE_VIDEO, '_column' => 'file', '_tmp' => $file['tmp_name'],
          );
      }

      $details || Validation::error('至少選擇一張圖片或影片');
      count($details) <= 5 || Validation::error('影片或圖片最多五項');

      $posts['details'] = $details;
    };

    $transaction = function($posts, $files, $adv) {
      if(!($adv->columnsUpdate ($posts) && $adv->save ()))
        return false;

      foreach( $posts['details'] as $detail ) {
        if ( !$detailObj = AdvDetail::create(array_merge($detail, array('adv_id' => $adv->id, 'link' => '', 'pic' => '', 'file' => '') ) ) )
          return false;

        if( !$detailObj->{$detail['_column']}->put ($detail['_tmp']) )
          return false;
      }

      if (!$notify = Notify::create (array ('user_id' => $adv->brand->user_id, 'brand_id' => $adv->brand_id, 'send_id' => $this->user->id, 'content' => '哈哈哈', 'read' => Notify::READ_NO)))
        return false;

      return true;
    };

    $posts = Input::put(null, Input::PUT_FORM_DATA);
    $files['pics'] = Input::file ('pics[]');
    $files['files'] = Input::file ('files[]');

    if ($error = Validation::form ($validation, $posts, $files, $adv))
      return Output::json($error, 400);

    if ($error = User::getTransactionError ($transaction, $posts, $files, $adv))
      return Output::json($error, 400);

    return Output::json(['message' => '成功'], 200);
  }

  /**
   * @apiName CreateMessages
   * @apiGroup Adv
   *
   * @api {post} /api/adv/msg 新增廣告留言
   *
   * @apiHeader {string} token 登入後的 Access Token
   *
   * @apiParam {Number} adv_id    廣告ID
   * @apiParam {String} content   內容
   *
   * @apiUse MySuccess
   * @apiUse MyError
   */
  public function createMsg () {
    $validation = function (&$posts, &$adv) {
      Validation::need ($posts, 'adv_id', '廣告ID')->isNumber ()->doTrim ();
      Validation::need ($posts, 'content', '內容')->isStringOrNumber ()->doTrim ();

      if (!$adv = Adv::find ('one', array ('where' => array('id = ? AND review = ?', $posts['adv_id'], Adv::REVIEW_PASS) ) ) )
        Validation::error('查無廣告資訊');

      $posts['user_id'] = $this->user->id;
    };

    $transaction = function ($posts, $adv) {
      if( !$obj = AdvMessage::create ($posts) )
        return false;

      $adv->columnsUpdate( array(
        'cnt_message' => $adv->cnt_message + 1,
      ) );
      return $adv->save();
    };

    $posts = Input::post ();

    if ($error = Validation::form ($validation, $posts, $adv))
      return Output::json($error, 400);

    if ($error = AdvMessage::getTransactionError ($transaction, $posts, $adv))
      return Output::json($error, 400);

    return Output::json(['message' => "成功"], 200);
  }

  /**
   * @apiName Messages
   * @apiGroup Adv
   *
   * @api {get} /api/adv/msgs 取得廣告留言列表
   *
   * @apiHeader {string} token 登入後的 Access Token
   *
   * @apiParam {Number} adv_id          廣告 ID
   * @apiParam {Number} [offset=0]      位移
   * @apiParam {String} [limit=20]      長度
   *
   * @apiSuccess {Number} id            留言ID
   *
   * @apiSuccess {Object} user         留言者資訊
   * @apiSuccess {Number} user.id      留言者 ID
   * @apiSuccess {String} user.name    留言者姓名
   * @apiSuccess {String} user.avatar  留言者圖片
   *
   * @apiSuccess {String} content      內容
   *
   * @apiSuccess {DateTime} created_at 建立時間
   * @apiSuccess {DateTime} updated_at 更新時間
   *
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *     [
   *         {
   *             "id": 1,
   *             "user": {
   *                 "id": 1,
   *                 "name": "名稱",
   *                 "avatar": "網址"
   *             },
   *             "content": "內容",
   *             "created_at": "2018-04-19 09:40:07",
   *             "updated_at": "2018-04-24 09:54:53"
   *         },
   *     ]
   *
   * @apiUse MyError
   */
  public function msgs () {
    $validation = function(&$gets, &$adv) {
        Validation::need ($gets, 'adv_id', '廣告ID')->isNumber ()->doTrim ()->greater (0);

        Validation::maybe ($gets, 'offset', '位移', 0)->isNumber ()->doTrim ()->greater (0);
        Validation::maybe ($gets, 'limit', '長度', 20)->isNumber ()->doTrim ()->greater (0);

        if (!$adv = Adv::find ('one', array ('where' => array('id = ? AND review = ?', $gets['adv_id'], Adv::REVIEW_PASS) ) ) )
          Validation::error('查無廣告資訊');

        if($adv->user_id != $this->user->id && $adv->enable == Adv::ENABLE_OFF)
          Validation::error('此廣告尚未上架');
    };

    $gets = Input::get();

    if( $error = Validation::form($validation, $gets, $adv) )
      return Output::json($error, 400);

    return Output::json (array_values (array_filter (array_map (function ($msg) {
      if (!$msg->user)
        return null;

      return array (
          'id' => $msg->id,
          'user' => array (
              'id' => $msg->user->id,
              'name' => $msg->user->name,
              'avatar' => $msg->user->avatar->url(),
            ),
          'content' => $msg->content,
          'created_at' => $msg->created_at->format ('Y-m-d H:i:s'),
          'updated_at' => $msg->updated_at->format ('Y-m-d H:i:s'),
        );
    }, AdvMessage::find ('all', array ('include' => array('user'), 'order' => 'id DESC', 'offset' => $gets['offset'], 'limit' => $gets['limit'], 'where' => array( 'adv_id = ?', $adv->id ) ) ) ))));
  }

  /**
   * @apiName LikeAdv
   * @apiGroup Adv
   *
   * @api {post} /api/adv/like 按喜歡
   * @apiHeader {string} token 登入後的 Access Token
   *
   * @apiParam {Number} adv_id    廣告ID
   *
   * @apiUse MySuccess
   * @apiUse MyError
   */
  public function like () {
    $validation = function (&$posts, &$adv) {
      Validation::need ($posts, 'adv_id', '廣告ID')->isNumber ()->doTrim ()->greater (0);

      if (!$adv = Adv::find ('one', array ('where' => array('id = ? AND review = ?', $posts['adv_id'], Adv::REVIEW_PASS) ) ) )
        Validation::error('查無廣告資訊');

      if($adv->user_id != $this->user->id && $adv->enable == Adv::ENABLE_OFF)
        Validation::error('此廣告尚未上架');
    };

    $transaction = function ($posts, $adv) {
      if ($this->user->hasLike($adv)) {
        if( $like = AdvLike::find('one', Where::create( 'user_id = ? AND adv_id = ?', $this->user->id, $adv->id ) ) )
            if( !$like->destroy() )
              return false;
      } else {
        if( !AdvLike::create ( array_merge( $posts, array('user_id' => $this->user->id) ) ) )
          return false;
      }
      $adv->columnsUpdate( array(
        'cnt_like' => AdvLike::count( Where::create('adv_id = ?', $adv->id) ),
      ) );
      return $adv->save();
    };

    $posts = Input::post ();

    if ($error = Validation::form ($validation, $posts, $adv))
      return Output::json($error, 400);

    if ($error = AdvLike::getTransactionError ($transaction, $posts, $adv))
      return Output::json($error, 400);

    return Output::json(['message' => "成功"], 200);
  }

  /**
   * @apiGroup Adv
   * @apiName AddAdvPv
   *
   * @api {post} /api/adv/pv   瀏覽
   * @apiHeader {string} token 登入後的 Access Token
   *
   * @apiParam {Number} adv_id    廣告ID
   *
   * @apiUse MySuccess
   * @apiUse MyError
   */
  public function pv () {
    $validation = function (&$posts, &$adv) {
      Validation::need ($posts, 'adv_id', '廣告ID')->isNumber ()->doTrim ()->greater (0);

      if (!$adv = Adv::find ('one', array ('where' => array('id = ? AND review = ?', $posts['adv_id'], Adv::REVIEW_PASS) ) ) )
        Validation::error('查無廣告資訊');
    };

    $transaction = function ($posts, $adv) {
      if (!$bonus = Bonus::find ('one', array ('where' => array ('adv_id = ?', $adv->id))))
        if (!$bonus = Bonus::create (array ('user_id' => $adv->user_id, 'adv_id' => $adv->id, 'price' => 0, 'remain_price' => 0)))
          return false;

      if (!$view = AdvView::find ('one', array ('where' => array ('user_id = ? AND adv_id = ?', $this->user->id, $adv->id)))) {
        if (!$view = AdvView::create (array ('user_id' => $this->user->id, 'adv_id' => $adv->id, 'price' => 0.3)))
          return false;

        $adv->cnt_view = count ($prices = AdvView::getArray ('price', array ('where' => array ('adv_id' => $adv->id))));

        $sum = array_sum ($prices);
        $bonus->remain_price += ($sum - $bonus->price);
        $bonus->price = $sum;
      }

      if (!$browe = AdvBrowe::create (array ('user_id' => $this->user->id, 'adv_id' => $adv->id)))
        return false;

      $adv->cnt_browe = count ($ids = AdvBrowe::getArray ('id', array ('where' => array ('adv_id' => $adv->id))));

      return $bonus->save () && $adv->save();
    };

    $posts = Input::post ();

    if ($error = Validation::form ($validation, $posts, $adv))
      return Output::json($error, 400);

    if ($error = AdvLike::getTransactionError ($transaction, $posts, $adv))
      return Output::json($error, 400);

    return Output::json(['message' => "成功"], 200);
  }

  /**
   * @apiGroup Adv
   * @apiName EnableAdvPv
   *
   * @api {post} /api/adv/enable   設定上下架
   * @apiHeader {string} token 登入後的 Access Token
   *
   * @apiParam {Number} adv_id    廣告ID
   * @apiParam {Number} brand_id  品牌ID
   * @apiParam {Number} enable    開啟或關閉(on 開啟 off 關閉)
   *
   * @apiUse MySuccess
   * @apiUse MyError
   */
  public function enable () {
    $validation = function (&$posts, &$adv) {
      Validation::need ($posts, 'adv_id', '廣告ID')->isNumber ()->doTrim ()->greater (0);
      Validation::need ($posts, 'brand_id', '品牌ID')->isNumber ()->doTrim ()->greater (0);
      Validation::need ($posts, 'enable', '開啟或關閉')->isStringOrNumber ()->doTrim ()->inArray (array_keys (Adv::$enableTexts));

      if( !$brand = Brand::find_by_id($posts['brand_id']) )
        Validation::error('查無此資料！');

      if (!$adv = Adv::find ('one', array ('where' => array('id = ?', $posts['adv_id']) ) ) )
        Validation::error('查無廣告資訊！');

      if ($brand->id != $adv->brand_id)
        Validation::error('資訊錯誤！');

      if ($brand->user_id != $this->user->id)
        Validation::error('身份錯誤！');
    };

    $transaction = function ($posts, $adv) {
      return $adv->columnsUpdate ($posts) && $adv->save ();
    };

    $posts = Input::post ();

    if ($error = Validation::form ($validation, $posts, $adv))
      return Output::json($error, 400);

    if ($error = AdvLike::getTransactionError ($transaction, $posts, $adv))
      return Output::json($error, 400);

    return Output::json(['message' => "成功"], 200);
  }

  /**
   * @apiName ReviewAdv
   * @apiGroup Adv
   *
   * @api {post} /api/adv/review  審核
   * @apiHeader {string} token    登入後的 Access Token
   *
   * @apiParam {Number} adv_id         廣告ID
   * @apiParam {String} review         審核 Key（yet, pass, fail）
   * @apiParam {String} [reason='']    原因
   *
   * @apiUse MySuccess
   * @apiUse MyError
   */
  public function review () {
    $validation = function (&$posts, &$adv) {
      Validation::need ($posts, 'adv_id', '廣告ID')->isNumber ()->doTrim ()->greater (0);
      Validation::need ($posts, 'review', '審核 Key')->isStringOrNumber ()->doTrim ()->inArray (array_keys (Adv::$reviewTexts));
      Validation::maybe ($posts, 'reason', '原因', '')->isStringOrNumber ()->doTrim ()->length(1, 191);

      if (!$adv = Adv::find ('one', array ('where' => array('id = ?', $posts['adv_id']) ) ) )
        Validation::error('查無廣告資訊');

      if ($adv->brand->user_id != $this->user->id)
        Validation::error('您沒有權限');
    };

    $transaction = function ($posts, $adv) {
      $adv->review = $posts['review'];

      $posts['reason'] && $adv->reason = $posts['reason'];

      if (!$adv->save())
        return false;

      if (!$notify = Notify::create (array ('user_id' => $adv->user_id, 'brand_id' => $adv->brand_id, 'send_id' => $this->user->id, 'content' => $adv->review == Adv::REVIEW_PASS ? '您在AD POST投稿作品已審核通過囉！前往「我的帳戶」確認獎金。' : '您在AD POST投稿作品未通過審核，點擊查看審核結果。', 'read' => Notify::READ_NO)))
        return false;

      return true;
    };

    $posts = Input::post ();

    if ($error = Validation::form ($validation, $posts, $adv))
      return Output::json($error, 400);

    if ($error = AdvLike::getTransactionError ($transaction, $posts, $adv))
      return Output::json($error, 400);

    return Output::json(['message' => "成功"], 200);
  }
}
