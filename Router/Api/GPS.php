<?php

Router::dir('api', 'Api', function() {
  Router::post('devices')->controller('Device@create');
  
  Router::post('device/(deviceId:id)/events')->controller('Event@create');
  Router::put('device/(deviceId:id)/events/(id:id)/permission')->controller('Event@permission');
  
  Router::post('device/(deviceId:id)/event/(eventId:id)/signals')->controller('Signal@create');
  // Router::post('device/(deviceId:id)/events/()stops')->controller('Stop@create');
});