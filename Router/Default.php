<?php

Router::get('')->controller('Main@index');
Router::post('')->controller('Main@index');

Router::dir('f2e', 'F2e', function() {
  Router::get('event/(id:id)')->controller('Event@show');
});