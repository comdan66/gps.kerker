<?php

Router::dir('api', 'Api', function() {
  Router::post('signals')->controller('Signal@create');
});