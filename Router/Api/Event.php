<?php

Router::dir('api', 'Api', function() {
  Router::post('events')->controller('Event@create');
  Router::put('events/(eventId:id)/permission')->controller('Event@permission');
});