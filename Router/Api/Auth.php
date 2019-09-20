<?php

Router::dir('api', 'Api', function() {
  Router::post('login')->controller('Auth@login');
  Router::post('logout')->controller('Auth@logout');
});