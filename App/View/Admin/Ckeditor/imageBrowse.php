<?php

use \CRUD\Table\Search\Input as Input;

echo $table->search(function() {
  
  Input::create('ID')
       ->sql('id = ?');

  Input::create('上傳時間')
       ->type('date')
       ->sql('createAt LIKE ?');
});

echo "<div id='imageBrowse'>" . implode('', array_map(function($obj) {
  return \HTML\Figure::create()->data('title', $obj->title)->data('bgurl', $obj->image->url());
}, $table->objs())) . "</div>";