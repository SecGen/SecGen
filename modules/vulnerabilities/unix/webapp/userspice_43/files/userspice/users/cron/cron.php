<?php
require_once '../init.php';
$db = DB::getInstance();
$ip = ipCheck();
logger(1,"CronRequest","Cron request from $ip.");
$settings = $db->query("SELECT cron_ip FROM settings")->first();
if($settings->cron_ip != ''){
if($ip != $settings->cron_ip && $ip != '127.0.0.1'){
	logger(1,"CronRequest","Cron request DENIED from $ip.");
	die;
	}
}
$from = Input::get('from');
$primaryquery = $db->query("SELECT file FROM crons WHERE active = ? ORDER BY sort",array(1));
$querycount = $primaryquery->count();

//Log Prep
if($user->isLoggedIn()) { $user_id = $user->data()->id; } else { $user_id = 1; }
$logtype = "Cron";
//Log Prep End

if($querycount > 0)
{
	$query = $db->query("SELECT id,file FROM crons WHERE active = ? ORDER BY sort",array(1));
	foreach ($query->results() as $row) {
		$id = $row->id;
		$file = $row->file;
		include_once($file);
		$cronfields = array(
		'cron_id' => $id,
		'datetime' => date("Y-m-d H:i:s"),
		'user_id' => $user_id);
		$db->insert('crons_logs',$cronfields);
	}
}
 if($from != NULL) Redirect::to('/'. $from);
?>
