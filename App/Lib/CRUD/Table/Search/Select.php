<?php

namespace CRUD\Table\Search;

class Select extends Items {
  public function __toString() {
    $return = '';
    
    if (!$this->items)
      return $return;

    $return .= '<div class="search-row">';
      $return .= '<b>' . $this->title . '</b>';
      $return .= '<div class="search-select">';
        $return .= '<select name="' . $this->key . '">';
          $return .= '<option value="">' . $this->title . '</option>';
          $return .= implode('', array_map(function($item) { return '<option value="' . $item['value'] . '"' . ($this->val && $this->val == $item['value'] ? ' selected' : '') . '>' . $item['text'] . '</option>'; }, $this->items));
        $return .= '</select>';
      $return .= '</div>';
    $return .= '</div>';
    return $return;
  }
}