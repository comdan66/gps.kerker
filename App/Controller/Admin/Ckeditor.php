<?php

use \CRUD\Table       as Table;
use \CRUD\Table\Order as Order;
use \CRUD\Form        as Form;
use \CRUD\Show        as Show;

class Ckeditor extends AdminCkeditorController {
  
  public function imageUpload() {
    $funcNum = $_GET['CKEditorFuncNum'];
    
    ifError(function($error) use ($funcNum) {
      return "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction(" . $funcNum . ", '', '上傳失敗！原因：" . $error . "');</script>";
    });

    $params = Input::ValidPost(function($params) {
      return $params;
    });

    $files = Input::ValidFile(function($files) use (&$params) {
      Validator::must($files, 'upload', '上傳圖片')->isUpload()->formatFilter(['jpg', 'png', 'jpeg']);
      $files['image'] = $files['upload'];
      $params['title'] = mb_substr($files['upload']['name'], 0, 190);
      return $files;
    });

    transaction(function() use (&$files, &$obj) {
      if (!$obj = \M\Ckeditor::create())
        return false;
      return $obj->putFiles($files);
    });

    return "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction (" . $funcNum . ", '" . $obj->image->url() . "', '上傳成功！');</script>";
  }
  
  public function imageBrowse() {
    return $this->view->with('table', \CRUD\Table::create('\M\Ckeditor'));
  }
}
