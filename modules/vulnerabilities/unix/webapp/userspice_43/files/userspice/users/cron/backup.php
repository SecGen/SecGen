<?php
ini_set('max_execution_time', 1356);
ini_set('memory_limit','1024M');
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
$errors = $successes = [];
$settingsQ = $db->query("Select * FROM settings");
$settings = $settingsQ->first();
$from = Input::get('from');
if($from=='') $from='users/cron_manager.php';
$checkQuery = $db->query("SELECT id,name FROM crons WHERE file = ? AND active = 1",array("backup.php"));
if($checkQuery->count()==1) {
	//Create backup destination folder: $settings->backup_dest
	//$backup_dest = $settings->backup_dest;
	$backup_dest = "@".$settings->backup_dest;//::from us v4.2.9a
	$backupTable = $settings->backup_table;
	if($settings->backup_source != "db_table") {
		$backupSource = $settings->backup_source;
	}
	elseif($settings->backup_source == "db_table") {
		$backupSource = $settings->backup_source.'_'.$backupTable;
	}
	$destPath = $abs_us_root.$us_url_root.$backup_dest;
	if(!file_exists($destPath)){
		if (mkdir($destPath)){
			$destPathSuccess = true;
			//$successes[] = lang('AB_PATHCREATE');
		}else{
			$destPathSuccess = false;
			//$errors[] = lang('AB_PATHERROR');
		}
	}else{
		//$successes[] = lang('AB_PATHEXISTED');
	}
	// Generate backup path
	$backupDateTimeString = date("Y-m-d\TH-i-s");
	$backupPath = $abs_us_root.$us_url_root.$backup_dest.'backup_'.$backupSource.'_'.$backupDateTimeString.'/';
	if(!file_exists($backupPath)){
		if (mkdir($backupPath)){
			$backupPathSuccess = true;
		}else{
			$backupPathSuccess = false;
		}
	}
	if($backupPathSuccess) {
		// Since the backup path is just created with a timestamp,
		// no need to check if these subfolders exist or if they are writable
		mkdir($backupPath.'files');
		mkdir($backupPath.'sql');
	}
	// Backup All Files & Directories In Root and DB
	if($backupPathSuccess && $settings->backup_source == 'everything'){
		// Generate list of files in ABS_TR_ROOT.TR_URL_ROOT including files starting with .
		$backupItems = [];
		$backupItems[] = $abs_us_root.$us_url_root;
		$backupItems[] = $abs_us_root.$us_url_root.'users';
		$backupItems[] = $abs_us_root.$us_url_root.'usersc';
		if(backupObjects($backupItems,$backupPath.'files/')){
			//$successes[] = lang('AB_BACKUPSUCCESS');
		}else{
			//$errors[] = lang('AB_BACKUPFAIL');
		}
		backupUsTables($backupPath);
		$targetZipFile = backupZip($backupPath,true);
		if($targetZipFile){
			//$successes[] = lang('AB_DB_FILES_ZIP');
			$backupZipHash = hash_file('sha1', $targetZipFile);
			$backupZipHashFilename = substr($targetZipFile,0,strlen($targetZipFile)-4).'_SHA1_'.$backupZipHash.'.zip';
			if(rename($targetZipFile,$backupZipHashFilename)){
				//$successes[] = lang('AB_FILE_RENAMED').$backupZipHashFilename;
			}else{
				//$errors[] = lang('AB_NOT_RENAME');
			}
		}else{
			//$errors[] = lang('AB_ERROR_CREATE');
		}
	}
	// Backup Terminus files & all db tables
	if($backupPathSuccess && $settings->backup_source == 'db_us_files'){
		// Generate list of files in ABS_TR_ROOT.TR_URL_ROOT including files starting with .
		$backupItems = [];
		$backupItems[] = $abs_us_root.$us_url_root.'users';
		$backupItems[] = $abs_us_root.$us_url_root.'usersc';
		if(backupObjects($backupItems,$backupPath.'files/')){
			//$successes[] = lang('AB_BACKUPSUCCESS');
		}else{
			//$errors[] = lang('AB_BACKUPFAIL');
		}
		backupUsTables($backupPath);
		$targetZipFile = backupZip($backupPath,true);
		if($targetZipFile){
			//$successes[] = lang('AB_DB_FILES_ZIP');
			$backupZipHash = hash_file('sha1', $targetZipFile);
			$backupZipHashFilename = substr($targetZipFile,0,strlen($targetZipFile)-4).'_SHA1_'.$backupZipHash.'.zip';
			if(rename($targetZipFile,$backupZipHashFilename)){
				//$successes[] = lang('AB_FILE_RENAMED').$backupZipHashFilename;
			}else{
				//$errors[] = lang('AB_NOT_RENAME');
			}
		}else{
			//$errors[] = lang('AB_ERROR_CREATE');
		}
	}
	// Backup all db tables only
	if($backupPathSuccess && $settings->backup_source == 'db_only'){
		backupUsTables($backupPath);
		$targetZipFile = backupZip($backupPath,true);
		if($targetZipFile){
			$successes[] = lang('AB_DB_ZIPPED');
			$backupZipHash = hash_file('sha1', $targetZipFile);
			$backupZipHashFilename = substr($targetZipFile,0,strlen($targetZipFile)-4).'_SHA1_'.$backupZipHash.'.zip';
			if(rename($targetZipFile,$backupZipHashFilename)){
				//$successes[] = lang('AB_FILE_RENAMED').$backupZipHashFilename;
			}else{
				//$errors[] = lang('AB_NOT_RENAME');
			}
		}else{
			//$errors[] = lang('AB_ERROR_CREATE');
		}
	}elseif(!$backupPathSuccess){
		//$errors[] = lang('AB_PATHEXIST');
	}else{
		// Unknown state? Do nothing.
	}
	// Backup terminus files only
	if($backupPathSuccess && $settings->backup_source == 'us_files'){
		// Generate list of files in ABS_TR_ROOT.TR_URL_ROOT including files starting with .
		$backupItems = [];
		$backupItems[] = $abs_us_root.$us_url_root.'users';
		$backupItems[] = $abs_us_root.$us_url_root.'usersc';
		if(backupObjects($backupItems,$backupPath.'files/')){
			//$successes[] = lang('AB_BACKUPSUCCESS');
		}else{
			//$errors[] = lang('AB_BACKUPFAIL');
		}
		$targetZipFile = backupZip($backupPath,true);
		if($targetZipFile){
			//$successes[] = lang('AB_T_FILE_ZIP');
			$backupZipHash = hash_file('sha1', $targetZipFile);
			$backupZipHashFilename = substr($targetZipFile,0,strlen($targetZipFile)-4).'_SHA1_'.$backupZipHash.'.zip';
			if(rename($targetZipFile,$backupZipHashFilename)){
				//$successes[] = lang('AB_FILE_RENAMED').$backupZipHashFilename;
			}else{
				//$errors[] = lang('AB_NOT_RENAME');
			}
		}else{
			//$errors[] = lang('AB_ERROR_CREATE');
		}
	}
	// Backup single db table only
	if($backupPathSuccess && $settings->backup_source == 'db_table'){
		backupUsTable($backupPath);
		$targetZipFile = backupZip($backupPath,true);
		if($targetZipFile){
			//$successes[] = lang('AB_TABLES_ZIP');
			$backupZipHash = hash_file('sha1', $targetZipFile);
			$backupZipHashFilename = substr($targetZipFile,0,strlen($targetZipFile)-4).'_SHA1_'.$backupZipHash.'.zip';
			if(rename($targetZipFile,$backupZipHashFilename)){
				//$successes[] = lang('AB_FILE_RENAMED').$backupZipHashFilename;
			}else{
				//$errors[] = lang('AB_NOT_RENAME');
			}
		}else{
			//$errors[] = lang('AB_ERROR_CREATE');
		}
	}
	if($currentPage == "backup.php") {
		$query = $db->query("SELECT id,name FROM crons WHERE file = ?",array("backup.php"));
		if($user->isLoggedIn()) $user_id=$user->data()->id;
		else $user_id=1;
		$results = $query->first();
		$cronfields = array(
			'cron_id' => $results->id,
			'datetime' => date("Y-m-d H:i:s"),
			'user_id' => $user_id);
			$db->insert('crons_logs',$cronfields);
			Redirect::to('../../'. $from);
		} }
		else {
			Redirect::to('../../'. $from .'?err=Cron is disabled, cannot be ran.');
		}
		?>
