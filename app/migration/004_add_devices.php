<?php defined ('OACI') || exit ('此檔案不允許讀取。');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, OACI
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

return array (
    'up' => "CREATE TABLE `devices` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,

      `name` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Name',
      `uuid` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'UUID',

      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增時間',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'down' => "DROP TABLE `devices`;",
    'at' => "2018-05-27 15:11:44",
  );