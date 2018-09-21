<?php
//NOTE: This also serves as the reference file for how to do One Click Edit with UserSpice. See comments below.
  require_once '../init.php';
  $db = DB::getInstance();
  $resp = ['success'=>false];

//if you want to make sure that someone has a certain permission level to perform one click edits, yhou can perform an additional check here. Uncomment out the sample code. The hasPerm function can handle an array of permission levels.

  // if(!hasPerm([2],$user->data()->id)) {
  //   $resp['msg'] = 'You do not have permission to do that! Shame on you!';
  //   echo json_encode($resp);
  //   die;
  // }

//Note that this is grabbing the data-id, data-field, and data-value attributes you added to your paragraph tag in the mqtt_settings.php file.
  $id = Input::get('id');
  $field = Input::get('field');
  $value = Input::get('value');

//decide what table you want to update. In this case, it's mqtt.
  $db->update('mqtt',$id,[$field=>$value]);
    $resp['msg'] = 'Server Info Updated';


//Note, in my example code below from another project, there were fields called quantity and price.
//In that situation, I wanted it to do my qty * price to get the new total when I did a one click edit.  Here's how I did that.

// if($field == 'qty' || $field = 'price'){
// $priceCheckQ = $db->query("SELECT * FROM purchase WHERE id = ?",array($id));
// $priceCheck = $priceCheckQ->first();
// $newTotal = $priceCheck->qty * $priceCheck->price;
// $db->update('purchase',$id, ['est_tot' => $newTotal]);
//   $resp['msg'] = 'PR Updated - Total has been updated also. Refresh to see.';
// }

  $resp['success'] = true;

//now I'm going to pass that response back to my mqtt_settings.php file.
  echo json_encode($resp);
  exit;
?>
