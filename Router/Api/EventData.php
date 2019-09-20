<?php

Router::dir('api', 'Api', function() {
  Router::post('events/(eventId:id)/signals')->controller('EventData@create');
  Router::put('events/(eventId:id)/status')->controller('EventData@status');
});