<?php

namespace CRUD\Table;

class Ctrl extends Unit {
  private $ctrls = [], $isIconType = true;

  public static function create($title = null) {
    $obj = new static($title);
    return $obj->class('ctrl')->center()->title('操作');
  }

  public function isIconType(bool $isIconType) {
    $this->isIconType = $isIconType;
    return $this;
  }

  private function update() {
    $c = count($this->ctrls);
    $width = 20 + $c * ($this->isIconType ? 13 : 26) + ($c - 1) * 9 + 1 * 2;
    $width > 50 || $width = 50;
    $this->getTable()->ctrlWidth = $width > $this->getTable()->ctrlWidth ? $width : $this->getTable()->ctrlWidth;
    return $this;
  }

  public function setShowRouter($name) {
    array_push($this->ctrls, '<a href="' . call_user_func_array('Url::router', func_get_args()) . \CRUD::backOffsetLimit() . '" class="show"></a>');
    return $this->update();
  }

  public function setEditRouter($name) {
    array_push($this->ctrls, '<a href="' . call_user_func_array('Url::router', func_get_args()) . \CRUD::backOffsetLimit() . '" class="edit"></a>');
    return $this->update();
  }

  public function setDeleteRouter($name) {
    array_push($this->ctrls, '<a href="' . call_user_func_array('Url::router', func_get_args()) . \CRUD::backOffsetLimit() . '" class="delete" data-method="delete"></a>');
    return $this->update();
  }

  public function appendLink($hyperlink) {
    array_push($this->ctrls, $hyperlink);
    return $this->update();
  }

  public function getVal() {
    return $this->ctrls
      ? '<div class="ctrl' . ($this->isIconType ? ' icon' : '') . '">' . implode('', $this->ctrls) . '</div>'
      : '';
  }
}