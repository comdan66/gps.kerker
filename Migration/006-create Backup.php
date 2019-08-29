<?php

return [
  'up' => "CREATE TABLE `Backup` (
    `id`        int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    
    `file`      varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '檔案',
    `size`      int(11) unsigned NOT NULL DEFAULT 0 COMMENT '檔案大小(Byte)',
    `type`      enum('db', 'info', 'warning', 'error', 'benchmark', 'model', 'uploader', 'saveTool', 'thumbnail', 'query', 'other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other' COMMENT '類型',
    `status`    enum('failure', 'success') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'failure' COMMENT '狀態',
    `isRead`    enum('yes', 'no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no' COMMENT '已讀',
    `memo`      varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '備註',

    `updateAt`  datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
    `createAt`  datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增時間',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='備份紀錄';",

  'down' => "DROP TABLE IF EXISTS `Backup`;",

  'at' => "2019-06-25 16:06:40"
];

# 欄位格式
  # 主鍵
    // `id`        int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',

  # 外鍵
    // `userId`    int(11) unsigned NOT NULL COMMENT 'User ID',
    // `userId`    int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'User ID',

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
