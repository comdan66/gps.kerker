<?php

namespace CRUD;

class Table extends \CRUD {
  const SORT_KEY = '_s';
  const SEARCH_KEY = '_q';

  private $model, $option = [], $links = [], $addLink, $sortLink, $where, $runQuery = false, $total = 0, $objs = [], $trsIndex = 0, $trs = [], $searches = [], $searchStr, $tableStr, $pagesStr;
  public  $ctrlWidth = 0;

  public static function create($model, $option = []) {
    return new static($model, $option);
  }

  public function __construct($model, $option = []) {
    $this->model = $model;
    $this->option = $option;

    $this->option instanceof \Where && $this->option = ['where' => $this->option];
    $this->setWhere(isset($this->option['where']) ? $this->option['where'] : \Where::create());
  }

  public function &objs() {
    return $this->query()->objs;
  }

  public function appendLink($link) {
    array_push($this->links, $link);
    return $this;
  }

  public function setAddUrl($url) {
    $this->addLink = $url;
    return $this;
  }

  public function setAddRouter($router) {
    return $this->setAddUrl(call_user_func_array('\Url::router', func_get_args()));
  }

  public function setSortUrl($url) {
    $this->sortLink = $url;
    return $this;
  }

  public function setSortRouter($router) {
    return $this->setSortUrl(call_user_func_array('\Url::router', func_get_args()));
  }

  public function setWhere($where) {
    $this->where = $where instanceof \Where ? $where : \Where::create($where);
    return $this;
  }

  private function query() {
    if($this->runQuery)
      return $this;

    \CRUD\Table\Pagination::$firstText   = '';
    \CRUD\Table\Pagination::$lastText    = '';
    \CRUD\Table\Pagination::$prevText    = '';
    \CRUD\Table\Pagination::$nextText    = '';

    $this->runQuery = true;

    $model = $this->model;
    $this->total = $model::count($this->where);
    $this->pagesStr = \CRUD\Table\Pagination::info($this->total);
    unset($this->option['where']);

    $this->objs  = $model::all(array_merge([
     'order'  => \CRUD\Table\Order::desc('id'),
     'offset' => $this->pagesStr['offset'],
     'limit'  => $this->pagesStr['limit'],
     'where'  => $this->where], $this->option));

    $this->pagesStr = $this->pagesStr['links'] ? '<div class="panel"><div class="pages"><div>' . implode('', $this->pagesStr['links']) . '</div></div></div>' : '';

    return $this;
  }

  public function search($closure) {
    if ($this->searchStr !== null)
      return $this->searchStr;

    $title = null;
    $closure($title);
    $title == null && $title = '';
    
    $gets = \Input::get();
    $titles = [];

    foreach ($this->searches as $search) {
      unset($gets[$search->key()]);

      if (!$where = $search->updateSql(\Input::get($search->key(), true)))
        continue;

      $this->where->and($where);
      array_push($titles, $search->title());
    }

    $this->query();

    $gets = http_build_query($gets);
    $gets && $gets = '?' . $gets;
    $cancel = \Url::current() . $gets;

    $sortKey = '';

    if ($this->sortLink) {
      $gets = \Input::get();

      if (isset($gets[\CRUD\Table\Order::KEY]))
        unset($gets[\CRUD\Table\Order::KEY]);

      foreach (array_keys($this->searches) as $key)
        if (isset($gets[$key]))
          unset($gets[$key]);
  
      if (isset($gets[Table::SORT_KEY]) && $gets[Table::SORT_KEY] === 'true') {
        $ing = false;
        unset($gets[Table::SORT_KEY]);
      } else {
        $ing = true;
        $gets[Table::SORT_KEY] = 'true';
      }

      $gets = http_build_query($gets);
      $gets && $gets = '?' . $gets;
      $sortKey = \Url::current() . $gets;
    }

    $urls = array_filter(array_merge($this->links, [$sortKey ? '<a href="' . $sortKey . '" class="' . ($ing ? 'sort' : 'finish') . '"></a>' : null, $this->addLink ? '<a href="' . $this->addLink . '" class="add"></a>' : null]));
    $urls = $urls ? '<div class="search-links">' . implode('', $urls) . '</div>' : '';

    $this->searchStr = '';
    $this->searchStr .= '<div class="panel"' . ($title ? ' data-title="' . $title . '"' : '') . '>';
      $this->searchStr .= '<form class="search' . ($titles ? ' show' : '') . '" action="' . \Url::current() . '" method="get">';
        
        $this->searchStr .= '<label class="search-btn"></label>';
        $this->searchStr .= '<header class="search-header">';
          $this->searchStr .= $urls;
          $this->searchStr .= '<span class="search-title">' . ($titles ? '您針對 ' . implode('、', array_map(function($title) { return '<b>' . $title . '</b>'; }, $titles)) . ' 搜尋，結果' : '目前全部') . '共有「<b>' . number_format($this->total) . '</b>」筆。' . '</span>';
        $this->searchStr .= '</header>';

        $this->searchStr .= '<div class="search-conditions">';
          $this->searchStr .= implode('', $this->searches);
        
          $this->searchStr .= '<div class="search-btns">';
            $this->searchStr .= '<a href="' . $cancel . '">取消</a>';
            $this->searchStr .= '<button type="submit">搜尋</button>';
          $this->searchStr .= '</div>';

        $this->searchStr .= '</div>';

      $this->searchStr .= '</form>';
    $this->searchStr .= '</div>';

    $this->searches = [];
    return $this->searchStr;
  }

  public function list($closure) {
    if ($this->tableStr !== null)
      return $this->tableStr;

    $this->trs = [];

    $this->query();
    
    $title = null;
    foreach ($this->objs as $i => $obj) {
      $this->trsIndex = $i;
      $closure($obj, $title);
    }

    $title == null && $title = '';

    \Input::get(Table::SORT_KEY) === 'true' || $this->sortLink = '';

    $this->tableStr = '';

    $this->tableStr .= '<div class="panel"' . ($title ? ' data-title="' . $title . '"' : '') . '>';
      $this->tableStr .= '<div class="list">';
        $this->tableStr .= $this->sortLink
          ? '<table class="list sortable" data-api="' . $this->sortLink . '">'
          : '<table class="list">';

          $this->tableStr .= '<thead>';
            $this->tableStr .= '<tr>';
              $this->tableStr .= $this->trs ? implode('', array_map(function($tr) { return $tr->thString($this->sortLink); }, $this->trs[0])) : '';
            $this->tableStr .= '</tr>';
          $this->tableStr .= '</thead>';

          $this->tableStr .= '<tbody>';

            $this->tableStr .= $this->trs ? implode('', array_map(function($tds) {
              $attr = $this->sortLink && $tds[0]->getObj() && isset($tds[0]->getObj()->id, $tds[0]->getObj()->sort) ? ['data-id' => $tds[0]->getObj()->id, 'data-sort' => $tds[0]->getObj()->sort] : [];
              return '<tr' . \attr($attr) . '">' . implode('', $tds) . '</tr>';
            }, $this->trs)) : '<tr><td colspan></td></tr>';

          $this->tableStr .= '</tbody>';
        $this->tableStr .= '</table>';
      $this->tableStr .= '</div>';
    $this->tableStr .= '</div>';
    
    $this->trs = [];
    $this->objs = [];

    return $this->tableStr;
  }

  public function pages() {
    $this->pagesStr !== null || $this->query();
    return $this->pagesStr;
  }

  public function appendSearch(\CRUD\Table\Search $search) {
    array_push($this->searches, $search->key(Table::SEARCH_KEY . count($this->searches)));
    return $this;
  }

  public function appendUnit(\CRUD\Table\Unit $unit) {
    if (!isset($this->trs[$this->trsIndex])) {
      $this->trs[$this->trsIndex] = [];

      \Input::get(Table::SORT_KEY) === 'true'
        || $this->sortLink = '';

      $this->sortLink
        && array_push($this->trs[$this->trsIndex], \CRUD\Table\Sort::create('排序')->width(40)->class('drag')->center()->val('<label class="drag"><i></i><i></i><i></i><i></i><i></i><i></i></label>'));
    }

    array_push($this->trs[$this->trsIndex], $unit);
    return $this;
  }
}