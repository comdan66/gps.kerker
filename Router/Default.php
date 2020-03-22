<?php

Router::get('map')->controller('Main@map');
Router::get('')->controller('Main@index');
Router::post('')->controller('Main@index');
Router::cli('')->controller('Main@index');