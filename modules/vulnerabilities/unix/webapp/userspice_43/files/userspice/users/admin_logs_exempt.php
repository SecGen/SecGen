<?php

require_once 'init.php';
$db = DB::getInstance();
if (!securePage($_SERVER['PHP_SELF'])){die();}
$name = Input::get('name');
$pk = Input::get('pk');
$value = Input::get('value');
if($value==0) { $db->query("DELETE FROM logs_exempt WHERE name = ?",array($pk));
	$logtype=("Log Manager");
	$lognote=("Deleted exemption for $pk.");
	logger($user->data()->id,$logtype,$lognote); }
if($value==1) {
	$fields = array('name' => $pk, 'createdby' => $user->data()->id,'created' => date("Y-m-d H:i:s"));
	$db->insert('logs_exempt',$fields);
	$logtype=("Log Manager");
	$lognote=("Added Exemption for $pk.");
	logger($user->data()->id,$logtype,$lognote); }
 ?>
