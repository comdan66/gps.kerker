<?php

Router::dir('api', 'Api', function() {
  Router::post('signals')->controller('Signal@create');
  Router::get('signals')->controller('Signal@index');
});