<?php
//NOTE: This also serves as the reference file for how to do One Click Edit with UserSpice. See comments below.
  require_once '../init.php';
  $db = DB::getInstance();
  $resp = ['success'=>false];

  $id = Input::get('id');
  $field = Input::get('field');
  $value = Input::get('value');

if($field == 'display_order'){
  if(!is_numeric($value)){
    $resp['msg'] = 'Value must be an integer';
    $resp['success'] = false;
    echo json_encode($resp);
    exit;
  }else{
    $value = round($value,0);
    $db->update('menus',$id,[$field=>$value]);
      $resp['msg'] = 'Order Updated';
    $resp['success'] = true;
    echo json_encode($resp);
    exit;
  }
}else{
  $db->update('menus',$id,[$field=>$value]);
    $resp['msg'] = 'Item Updated';

  $resp['success'] = true;
  echo json_encode($resp);
  exit;
}
?>
