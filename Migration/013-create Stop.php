<?php

return [
  'up' => "CREATE TABLE `Stop` (
    `id`        int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',

    `deviceId`  int(11) unsigned NOT NULL COMMENT 'Device ID',
    `eventId`   int(11) unsigned NOT NULL COMMENT '事件 ID',
    
    `title`     varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '標題',

    `lat`       decimal(12,10) NOT NULL COMMENT '緯度',
    `lng`       decimal(13,10) NOT NULL COMMENT '經度',

    `startAt`   datetime DEFAULT NULL COMMENT '開始時間',
    `endAt`     datetime DEFAULT NULL COMMENT '結束時間',
    `elapsed`   int(11) unsigned NOT NULL DEFAULT '0' COMMENT '停留多久，單位為秒',

    `updateAt`  datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
    `createAt`  datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增時間',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stop 註解';",

  'down' => "DROP TABLE IF EXISTS `Stop`;",

  'at' => "2019-09-04 21:40:06"
];

# 欄位格式
  # 主鍵
    // `id`        int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',

  # 外鍵
    // `userId`    int(11) unsigned NOT NULL COMMENT 'User ID',
    // `userId`    int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'User ID',

  # 整數
    // `sort`      int(11) unsigned NOT NULL DEFAULT 0 COMMENT '排序 DESC',

  # 字串
    // `cover`     varchar(50)  COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '封面',
    // `title`     varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '標題',
    // `content`   text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '內容',

  # 列舉
    // `enable`    enum('yes', 'no') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '啟用',
    // `enable`    enum('yes', 'no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no' COMMENT '啟用',

  # 小數
    // `price`     decimal(11,2) NOT NULL DEFAULT '0.00',

# 資料表
  # 新增
    // CREATE TABLE `{資料表名稱}` (
    //   `id`        int(11) unsigned NOT NULL AUTO_INCREMENT,
    //   [{欄位格式}]
    //   `updateAt`  datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
    //   `createAt`  datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增時間',
    //   PRIMARY KEY (`id`),
    //   KEY `userId_index` (`userId`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='資料表註解';
  
  
  # 刪除
    // DROP TABLE IF EXISTS `{資料表名稱}`;

  # 清空
    // TRUNCATE TABLE `{資料表名稱}`;

# 欄位
  # 新增
    // ALTER TABLE `{資料表名稱}` ADD {新增的欄位格式} AFTER `{哪個欄位之後}`;",

  # 刪除
    // ALTER TABLE `{資料表名稱}` DROP COLUMN `{欄位名稱}`;

  # 變更
    // ALTER TABLE `{資料表名稱}` CHANGE `{原欄位名稱}` {新欄位格式}
