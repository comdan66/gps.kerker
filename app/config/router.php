<?php defined ('OACI') || exit ('此檔案不允許讀取。');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, OACI
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

Router::get ('(:id)', 'Main@index($1)');
Router::get ('path/(:id)', 'Main@path($1)');

Router::dir ('api', function () {
  Router::get ('key', 'Locations@key');
  Router::post ('locations', 'Locations@create');
});

Router::dir ('admin', function () {
  Router::get ('', 'Main');

  Router::get ('login', 'Auth@login');
  Router::post ('login', 'Auth@acSignin');
  Router::get ('logout', 'Auth@logout');

  Router::restful ('admins', 'Admins', array (
    array ('model' => 'Admin')));

  Router::restful ('backups', 'Backups', array (
    array ('model' => 'Backup')));
});
