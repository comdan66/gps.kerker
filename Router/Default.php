<?php

Router::get('')->controller('Main@index');
Router::post('')->controller('Main@index');

Router::dir('cli', 'Cli', function() {
  Router::cli('event/finish')->controller('Event@finish');
});

Router::dir('api/f2e', 'F2e', function() {
  Router::get('event/key')->controller('Event@key');
  Router::get('event/(code:any)')->controller('Event@show');
});