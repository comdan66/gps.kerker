<?php

Router::dir('api', 'Api', function() {
  Router::post('events')->controller('Event@create');
});