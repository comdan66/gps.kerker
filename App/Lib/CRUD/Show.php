<?php

namespace CRUD;

class Show extends \CRUD {
  private $obj, $units = [];
    
  public static function create(\M\Model $obj = null) {
    return new static($obj);
  }

  public function __construct(\M\Model $obj = null) {
    $this->obj = $obj;
  }

  public function &obj() {
    return $this->obj;
  }

  public function panel($closure) {
    $this->units = [];
    $title = null;
    $closure($this->obj, $title);
    $title == null && $title = '詳細資料';

    $return  = '';

    if (!$this->units)
      return $return;

    $return .= '<div class="panel"' . ($title ? ' data-title="' . $title . '"' : '') . '>';
      $return .= '<div class="detail">' . implode('', $this->units) .'</div>';
    $return .= '</div>';
    $this->units = [];
    return $return;
  }

  public function appendUnit(\CRUD\Show\Unit $unit) {
    array_push($this->units, $unit);
    return $this;
  }
}