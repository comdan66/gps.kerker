<?php

namespace CRUD\Table;

class Order {
  const KEY = '_o';
  const SPLIT_KEY = ':';

  private $sort = 'id DESC';

  public function __construct($sort = '') {
    if ($sort && count($sort = array_values(array_filter(array_map('trim', explode(' ', $sort))))) == 2 && in_array(strtolower($sort[1]), ['desc', 'asc']))
      $this->sort = $sort[0] . ' ' . strtoupper($sort[1]);

    if (($sort = \Input::get(Order::KEY)) && count($sort = array_values(array_filter(array_map('trim', explode(Order::SPLIT_KEY, $sort))))) == 2 && in_array(strtolower($sort[1]), ['desc', 'asc']))
      $this->sort = $sort[0] . ' ' . strtoupper($sort[1]);
  }
  
  public static function set($title, $column = '') {
    if (!$column) return '<a>' . $title . '</a>';

    $gets = \Input::get();
    
    if (!(isset($gets[Order::KEY]) && count($sort = array_values(array_filter(explode(Order::SPLIT_KEY, $gets[Order::KEY])))) == 2 && in_array(strtolower($sort[1]), ['desc', 'asc']) && ($sort[0] == $column))) {
      $gets[Order::KEY] = $column . Order::SPLIT_KEY . 'desc';
      return '<a href="' . \Url::current() . '?' . http_build_query($gets) . '" class="order">' . $title . '</a>';
    }

    $class = strtolower($sort[1]);

    if ($class != 'asc')
      $gets[Order::KEY] = $column . Order::SPLIT_KEY . 'asc';
    else
      unset($gets[Order::KEY]);

    return '<a href="' . \Url::current() . ($gets ? '?' : '') . http_build_query($gets) . '" class="order ' . $class . '">' . $title . '</a>';
  }

  private static function _desc($column = '') {
    return ($column ? $column : 'id') . ' ' . strtoupper('desc');
  }

  private static function _asc($column = '') {
    return ($column ? $column : 'id') . ' ' . strtoupper('asc');
  }

  public function __call($name, $arguments) {
    switch (strtolower(trim($name))) {
      case 'asc':
        $this->sort = call_user_func_array(['self', '_asc'], $arguments);
        break;

      case 'desc':
        $this->sort = call_user_func_array(['self', '_desc'], $arguments);
        break;

      default:
        gg('Order 沒有「' . $name . '」方法。');
        break;
    }
    return $this;
  }

  public static function __callStatic($name, $arguments) {
    switch (strtolower(trim($name))) {
      case 'asc':
        return Order::create(call_user_func_array(['self', '_asc'], $arguments));
        break;

      case 'desc':
        return Order::create(call_user_func_array(['self', '_desc'], $arguments));
        break;

      default:
        gg('Order 沒有「' . $name . '」方法。');
        break;
    }
  }

  public function __toString() {
    return $this->sort;
  }

  public static function create($sort = '') {
    return new Order($sort);
  }
}