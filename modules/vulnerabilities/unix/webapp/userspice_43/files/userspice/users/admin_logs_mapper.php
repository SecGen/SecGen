<?php

require_once 'init.php';
$db = DB::getInstance();
if (!securePage($_SERVER['PHP_SELF'])){die();}
$name = Input::get('name');
$pk = Input::get('pk');
$value = Input::get('value');
if($value != $pk) {
	$db->query("UPDATE logs SET logtype = ? WHERE logtype = ?",array($value,$pk));
	$logtype=("Log Manager");
	$lognote=("Mapped $pk to $value.");
	logger($user->data()->id,$logtype,$lognote); }
 ?>
