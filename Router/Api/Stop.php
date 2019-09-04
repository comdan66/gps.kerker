<?php

Router::dir('api', 'Api', function() {
  Router::post('stops')->controller('Stop@create');
});