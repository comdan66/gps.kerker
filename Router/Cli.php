<?php

Router::dir('cli', function() {

  Router::cli('crontab/backup/db')->controller('Crontab@backupDb');
  Router::cli('crontab/backup/logs/(beforeDay:num)')->controller('Crontab@backupLogs');
  Router::cli('crontab/upEvents')->controller('Crontab@upEvents');
});