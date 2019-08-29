<?php

Router::dir('api', 'Api', function() {
  Router::post('devices')->controller('Device@create');
});