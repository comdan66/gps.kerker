<?php

return [
  'up' => "CREATE TABLE `Signal` (
    `id`        int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',

    `deviceId`  int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Device ID',
    `eventId`   int(11) unsigned NOT NULL DEFAULT '0' COMMENT '事件 ID',

    `lat`       decimal(12,10) NOT NULL COMMENT '緯度',
    `lng`       decimal(13,10) NOT NULL COMMENT '經度',
    `alt`       decimal(10,2)  DEFAULT NULL COMMENT '海拔高度，單位為公尺',

    `accH`      decimal(10,2) unsigned DEFAULT NULL COMMENT '水平準度，單位為公尺',
    `accV`      decimal(10,2) unsigned DEFAULT NULL COMMENT '垂直準度，單位為公尺',

    `speed`     decimal(5,2) unsigned DEFAULT NULL COMMENT '速度，單位為每秒公尺',
    `course`    decimal(5,2) unsigned DEFAULT NULL COMMENT '方向，北 0，南 180，東 90，西 270',

    `battery`       tinyint(4) unsigned DEFAULT NULL COMMENT 'GPS 裝置電量，0 ~ 100',
    `batteryStatus` enum('unknown', 'unplugged', 'charging', 'full') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '電池狀態，未知、放電、充電、飽電(插電中)',

    `timeAt`    int(11) unsigned DEFAULT NULL COMMENT 'Unix Time',
    `enable`    enum('yes', 'no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes' COMMENT '是否採用，是、否',
    `memo`      varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '備註',

    `updateAt`  datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
    `createAt`  datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增時間',
    PRIMARY KEY (`id`),
    KEY `deviceId_eventId_enable_index` (`deviceId`, `eventId`, `enable`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Signal 註解';",

  'down' => "DROP TABLE IF EXISTS `Signal`;",

  'at' => "2019-08-29 17:08:39"
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
