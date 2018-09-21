<?php
function usersOnline () {
  $timestamp = time();
  $ip = ipCheck();
}

function ipCheck() {
  if (getenv('HTTP_CLIENT_IP')) {
    $ip = getenv('HTTP_CLIENT_IP');
  }
  elseif (getenv('HTTP_X_FORWARDED_FOR')) {
    $ip = getenv('HTTP_X_FORWARDED_FOR');
  }
  elseif (getenv('HTTP_X_FORWARDED')) {
    $ip = getenv('HTTP_X_FORWARDED');
  }
  elseif (getenv('HTTP_FORWARDED_FOR')) {
    $ip = getenv('HTTP_FORWARDED_FOR');
  }
  elseif (getenv('HTTP_FORWARDED')) {
    $ip = getenv('HTTP_FORWARDED');
  }
  else {
    $ip = $_SERVER['REMOTE_ADDR'];
  }
  return $ip;
}

function ipCheckBan(){
  $db = DB::getInstance();
  $ip = ipCheck();
  $ban = $db->query("SELECT id FROM us_ip_blacklist WHERE ip = ?",array($ip))->count();
  if($ban > 0){
    $unban = $db->query("SELECT id FROM us_ip_whitelist WHERE ip = ?",array($ip))->count();
    if($unban > 0){
        logger(0,'IP Logging','Blacklisted '.$ip.' attempted visit');
      return false;
    }else{
      return true;
  }
}else{
//  logger(0,'User','Blacklisted '.$ip.' attempted visit');
  return false;
}
}

function new_user_online($user_id) {
	$db = DB::getInstance();
	$ip = ipCheck();
	$timestamp = time();
	$checkUserId = $db->query("SELECT * FROM users_online WHERE user_id = ?",array($user_id));
	$countUserId = $checkUserId->count();

	if($countUserId == 0){
		$fields =array('timestamp'=>$timestamp, 'ip'=>$ip,'user_id'=>$user_id);
		$db->insert('users_online',$fields);
	}else{
		if($user_id==0){
			$fields =array('timestamp'=>$timestamp, 'ip'=>$ip,'user_id'=>$user_id);
			$checkQ = $db->query("SELECT id FROM users_online WHERE user_id = 0 AND ip = ?",array($ip));
			if($checkQ->count()==0){
				$db->insert('users_online',$fields);
			}else{
				$to_update = $checkQ->first();
				$db->update('users_online',$to_update->id,$fields);
			}
			$to_update = $checkQ->first();
			$db->update('users_online',$to_update->id,$fields);
		}else{
			$fields =array('timestamp'=>$timestamp, 'ip'=>$ip,'user_id'=>$user_id);
			$checkQ = $db->query("SELECT id FROM users_online WHERE user_id = ?",array($user_id));
			$to_update = $checkQ->first();
			$db->update('users_online',$to_update->id,$fields);
		}
	}
}

function delete_user_online() {
  $db = DB::getInstance();
  $timeout = 86400; //30 minutes - This can be changed
  $timestamp = time();
  $delete = $db->query("DELETE FROM users_online WHERE timestamp < ($timestamp - $timeout)");
}

function count_users() {
    $db = DB::getInstance();
    $timestamp = time();
	  $timeout = 1800; //30 minutes - This can be changed
    $selectAll = $db->query("SELECT * FROM users_online WHERE timestamp > ($timestamp-$timeout)");
    $count = $selectAll->count();
    return $count;
}
