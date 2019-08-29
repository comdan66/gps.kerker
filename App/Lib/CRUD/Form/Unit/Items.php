<?php

namespace CRUD\Form\Unit;

abstract class Items extends \CRUD\Form\Unit {
  protected $items = [];

  public function items(array $items) {
    if (!$items)
      return $this;

    is_string(array_values($items)[0]) && $items = items(array_keys($items), array_values($items));
    is_array($items) && $items = array_map(function($item) { isset($item['items']) && $item['items'] = $item['items'] ? isset($item['items'][0]['text']) ? $item['items'] : items(array_keys($item['items']), array_values($item['items'])) : []; return $item; }, $items);
    $this->items = $items;
    return $this;
  }
}