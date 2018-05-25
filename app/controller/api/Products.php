<?php defined ('OACI') || exit ('此檔案不允許讀取。');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, OACI
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

class Products extends ApiLoginController {

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
   * @apiName Products
   * @apiGroup Product
   *
   * @api {get} /api/products           取得商品列表
   *
   * @apiHeader {string} token          登入後的 Access Token
   *
   * @apiParam {Number} [offset=0]      位移
   * @apiParam {String} [limit=20]      長度
   * @apiParam {Number} [brand_id]      品牌ID
   *
   * @apiSuccess {Number} id                商品ID
   * @apiSuccess {Object} brand_user        品牌使用者
   * @apiSuccess {Number} brand_user.id     品牌使用者ID
   * @apiSuccess {Number} brand_user.name   品牌使用者名稱
   * @apiSuccess {Number} brand_user.avatar 品牌使用者圖片
   *
   * @apiSuccess {Array}  details           詳細內容
   * @apiSuccess {String} details.type      詳細類型 (picture, youtube, video)
   * @apiSuccess {String} details.url       連結 (youtube)
   * @apiSuccess {String} details.pic       圖片 (picture)
   * @apiSuccess {String} details.file      檔案鏈結 (video)
   *
   * @apiSuccess {String} name              商品名稱
   * @apiSuccess {String} description       敘述
   *
   * @apiSuccess {DateTime} created_at      建立時間
   * @apiSuccess {DateTime} updated_at      更新時間
   *
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *     [
   *         {
   *             "id": 3,
   *             "brand_user": {
   *                 "id": 14,
   *                 "name": "堵椎墮",
   *                 "avatar": ""
   *             },
   *            "details": [
   *                {
   *                    "id": 6,
   *                    "type": "picture",
   *                    "pic": "圖片網址",
   *                    "url": "",
   *                    "file": ""
   *                },
   *                {
   *                    "id": 7,
   *                    "type": "video",
   *                    "pic": "",
   *                    "url": "",
   *                    "file": "影片網址"
   *                }
   *            ],
   *             "name": "瓜胰嵐紂杏具笙瓢歙碘",
   *             "description": "璣訣天贊猷指剝泥匐甫城籌茱儀戴悚傲朽氟牢嚴冀熱嫩跆甚悠慾課訃烈斑販縛悵傻看臭臂毯蹣羸儒簍孔愾帝纜奮嗽薜哦裹消賈踵氾糾割我爺蚪莖測劾墊廊眠例旅貢踢皰寐習肝軌孑寬臧給凳",
   *             "created_at": "2018-04-19 09:40:08",
   *             "updated_at": "2018-04-19 09:40:09"
   *         },
   *     ]
   *
   * @apiUse MyError
   */
  public function index() {
    $validation = function(&$gets) {
      Validation::maybe ($gets, 'offset', '位移', 0)->isNumber ()->doTrim ()->greater (0);
      Validation::maybe ($gets, 'limit', '長度', 20)->isNumber ()->doTrim ()->greater (0);
      Validation::maybe ($gets, 'brand_id', '品牌ID', 0)->isNumber ()->doTrim ()->greater (0);

    };

    $gets = Input::get ();

    if( $error = Validation::form($validation, $gets) )
      return Output::json($error, 400);

    $where = Where::create();
    $gets['brand_id'] && $where->and ('brand_id = ?', $gets['brand_id']);

    return Output::json(array_values (array_filter (array_map (function ($product) {
      if( !$product->brand)
        return null;

      if( !$product->brand->user)
        return null;

      $product = array (
          'id' => $product->id,

          'brand_user' => array(
            'id' => $product->brand->user->id,
            'name' => $product->brand->user->name,
            'avatar' => $product->brand->user->avatar->url (),
          ),
          'details' => array_map( function($detail) {
              return array(
                      'id' => $detail->id,
                      'type' => $detail->type,
                      'pic' => $detail->pic->url(),
                      'url' => $detail->url,
                      'file' => $detail->file->url(),
                    );
            }, $product->details),

          'name' => $product->name,
          'description' => $product->description,
          'created_at' => $product->created_at->format ('Y-m-d H:i:s'),
          'updated_at' => $product->updated_at->format ('Y-m-d H:i:s'),
        );

      return $product;
    }, BrandProduct::find ('all', array ( 'include' => array('brand', 'details'), 'offset' => $gets['offset'], 'limit' => $gets['limit'], 'where' => $where ))))));

  }

  /**
   * @apiName Product
   * @apiGroup Product
   *
   * @api {get} /api/product                取得商品內容
   *
   * @apiHeader {string} token              登入後的 Access Token
   *
   * @apiParam {Number} id                  商品 ID
   *
   * @apiSuccess {Number} id                商品 ID
   * @apiSuccess {Object} brand             品牌
   * @apiSuccess {Number} brand.id          品牌 ID
   * @apiSuccess {String} brand.name        品牌名稱
   * @apiSuccess {Object} brand.user        品牌使用者
   * @apiSuccess {Number} brand.user.id     使用者 ID
   * @apiSuccess {String} brand.user.name   使用者名稱
   * @apiSuccess {String} brand.user.avatar 使用者頭像
   *
   * @apiSuccess {String} name              商品名稱
   * @apiSuccess {String} rule              商品限制
   * @apiSuccess {String} description       商品敘述
   * @apiSuccess {String} cnt_shoot         拍攝次數
   *
   * @apiSuccess {DateTime} created_at      建立時間
   * @apiSuccess {DateTime} updated_at      更新時間
   *
   * @apiSuccess {Array}  details           詳細內容
   * @apiSuccess {String} details.type      詳細類型 (picture, youtube, video)
   * @apiSuccess {String} details.url       連結 (youtube)
   * @apiSuccess {String} details.pic       圖片 (picture)
   * @apiSuccess {String} details.file      檔案鏈結 (video)
   *
   * @apiSuccess {Array}  users             投稿過的使用者
   * @apiSuccess {Number} users.id          使用者 ID
   * @apiSuccess {String} users.name        使用者 名稱
   * @apiSuccess {String} users.avatar      使用者 頭像
   * @apiSuccess {String} users.created_at  投稿時間
   *
   * @apiSuccessExample {json} 成功:
   *     HTTP/1.1 200 OK
   *     {
   *         "id": 1,
   *         "brand": {
   *             "id": 2,
   *             "name": "品牌名稱",
   *             "user": {
   *                 "id": 1,
   *                 "name": "使用者名稱",
   *                 "avatar": "使用者圖片"
   *             }
   *         },
   *         "name": "商品名稱",
   *         "rule": "操甄勦莫斬呢蓀揪怠藪曙惆茱稼璋柯寓侯燜諦平撇寸軌彎廝糯岌綢涮擔推畸砧塊于感襲受什愾焦蟈怯冷跼筐姦曰牖修互綿泥側衰兔紊狄嫉癘濤咽蛹拖添捂絮採稿欣幾玄媚貼貊種樵抱",
   *         "description": "哄父庶皿泌憐倡我改趁渺白經浚辮漱撬冥莽詐惟挽居稜士盈耶稚蹉沁盒捕砸拇胖爪袍虞秒介註求瀾哮寤砭諷舷籬守右募詔詳膊軟窪締軸勵妁奠炒慫瀏瞭妻掩拐刎畢救何句做嚴燦涕芹膩几樊孟秣財忘掠腹蛔瘁宴害荸怪旱蜈",
   *         "cnt_shoot": 1,
   *         "created_at": "2018-04-19 09:40:07",
   *         "updated_at": "2018-04-19 09:40:07",
   *         "details": [
   *             {
   *                 "id": 1,
   *                 "type": "picture",
   *                 "pic": "圖片網址",
   *                 "url": "",
   *                 "file": ""
   *             },
   *         ],
   *         "users": [
   *             {
   *                 "id": 1,
   *                 "name": "名稱",
   *                 "avatar": "圖片網址",
   *                 "created_at": "2018-04-19 09:40:07"
   *             },
   *         ]
   *     }
   *
   * @apiUse MyError
   */
  public function show() {
    $validation = function(&$gets, &$product) {
      Validation::need ($gets, 'id', '商品ID')->isNumber ()->doTrim ()->greater (0);

      if (!$product = BrandProduct::find ('one', array ('where' => array('id = ?', $gets['id']) ) ) )
        Validation::error('查無商品資訊！');

      if (!$product->brand)
        Validation::error('商品資訊有誤！');
    };

    $gets = Input::get ();

    if( $error = Validation::form($validation, $gets, $product) )
      return Output::json($error, 400);

    return Output::json(array (
        'id' => $product->id,
        'brand' => array(
          'id' => $product->brand->id,
          'name' => $product->brand->name,
          'user' => array(
            'id' => $product->brand->user->id,
            'name' => $product->brand->user->name,
            'avatar' => $product->brand->user->avatar->url (),
          ),
        ),

        'name' => $product->name,
        'rule' => $product->rule,
        'description' => $product->description,
        'cnt_shoot' => $product->cnt_shoot,

        'created_at' => $product->created_at->format ('Y-m-d H:i:s'),
        'updated_at' => $product->updated_at->format ('Y-m-d H:i:s'),

        'details' => array_map( function($detail) {
            return array(
                    'id' => $detail->id,
                    'type' => $detail->type,
                    'pic' => $detail->pic->url(),
                    'url' => $detail->url,
                    'file' => $detail->file->url(),
                  );
          }, $product->details),
        'users' => array_values(array_filter(array_map (function ($adv) {
          if (!$adv->user)
            return null;

          return array (
              'id' => $adv->user->id,
              'name' => $adv->user->name,
              'avatar' => $adv->user->avatar->url (),
              'created_at' => $adv->created_at->format ('Y-m-d H:i:s'),
            );
        }, Adv::find ('all', array ( 'include' => array('user'), 'order' => 'id DESC', 'where' => array ('brand_product_id = ?', $product->id))))))
    ));
  }

  /**
   * @apiGroup Product
   * @apiName CreateProduct
   *
   * @api {post} /api/product   新增品牌商品
   * @apiHeader {string} token        登入後的 Access Token
   *
   * @apiParam {Number} brand_id      品牌ID
   * @apiParam {String} name          名稱
   * @apiParam {String} description   描述
   * @apiParam {String} rule          限制
   * @apiParam {Array} [pics]         圖片
   * @apiParam {Array} [files]        檔案
   * @apiParam {File}  [covers]       檔案封面圖檔(需與檔案index對應)
   *
   * @apiUse MySuccess
   * @apiUse MyError
   */
  public function create () {
    $validation = function (&$posts, &$files) {
      Validation::need ($posts, 'brand_id', '品牌 ID')->isNumber ()->doTrim ()->greater (0);

      Validation::need ($posts, 'name', '名稱')->isStringOrNumber ()->doTrim ();
      Validation::need ($posts, 'description', '敘述')->isStringOrNumber ()->doTrim ();
      Validation::need ($posts, 'rule', '限制')->isStringOrNumber ()->doTrim ();

      Validation::maybe ($files, 'pics', '圖片', array ())->isArray ()->fileterIsUploadFiles ()->filterFormats ('jpg', 'gif', 'png', 'jpeg')->filterSize (1, 10 * 1024 * 1024);
      Validation::maybe ($files, 'files', '檔案', array ())->isArray ()->fileterIsUploadFiles ()->filterFormats ('mp4', 'mov')->filterSize (1, 10 * 1024 * 1024);
      Validation::maybe ($files, 'covers', '檔案圖片', array ())->isArray ()->fileterIsUploadFiles ()->filterFormats ('jpg', 'gif', 'png', 'jpeg')->filterSize (1, 10 * 1024 * 1024);

      if( !$brand = Brand::find_by_id($posts['brand_id']) )
        Validation::error('查無此品牌');

      if ($brand->user_id != $this->user->id)
        Validation::error('您沒有權限');

      $posts['cnt_shoot'] = 0;

      $details = array();
      if ($files['pics']) {
        foreach ( $files['pics'] as $pic )
          $details[] = array('type' => BrandProductDetail::TYPE_PICTURE, '_put' => array( 'pic' => $pic ) );
      }

      if ( $files['files'] ) {
        if( !$covers = $files['covers'] )
          Validation::error('影片需傳入預設圖片');

        if ( count($files['files']) != count($covers) )
          Validation::error('上傳的影片與預設圖檔數目不符');

        $details = array_merge($details, array_values( array_filter( array_map( function($file, $key) use ($covers){
          if( $file && isset($covers[$key]) && $covers[$key] )
            return array( 'type' => BrandProductDetail::TYPE_VIDEO, '_put' => array( 'file' => $file, 'pic' => $covers[$key]) );
        }, $files['files'], array_keys($files['files']) ) ) ) );
      }

      $details || Validation::error('至少選擇一張圖片或影片');
      count($details) <= 5 || Validation::error('影片或圖片最多五項');

      $posts['details'] = $details;
    };

    $transaction = function ($posts) {
      if( !$obj = BrandProduct::create ($posts) )
        return false;

      foreach( $posts['details'] as $detail ) {
        if ( !$detailObj = BrandProductDetail::create(array_merge($detail, array('brand_product_id' => $obj->id, 'url' => '', 'pic' => '', 'file' => '') ) ) )
          return false;

        foreach( $detail['_put'] as $column => $value )
          if ( !$detailObj->{$column}->put($value) )
            return false;
      }
      return true;
    };

    $posts = Input::post();
    $files['pics'] = Input::file ('pics[]');
    $files['files'] = Input::file ('files[]');
    $files['covers'] = Input::file ('covers[]');

    if ($error = Validation::form ($validation, $posts, $files))
      return Output::json($error, 400);

    if ($error = Brand::getTransactionError ($transaction, $posts))
      return Output::json($error, 400);

    return Output::json(['message' => "成功"], 200);
  }
}
