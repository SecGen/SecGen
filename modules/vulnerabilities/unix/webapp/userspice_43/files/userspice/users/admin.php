<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
ini_set('max_execution_time', 1356);
ini_set('memory_limit','1024M');
?>
<?php require_once 'init.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/header.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/navigation.php'; ?>

<?php if (!securePage($_SERVER['PHP_SELF'])){die();} ?>
<link href="css/admin-tabs.css" rel="stylesheet">
<style>
/* centered columns styles */
.row-centered {
    text-align:center;
}
.col-centered {
    display:inline-block;
    float:none;
    /* reset the text-align */
    text-align:center;
    /* inline-block space fix */
    margin-right:-4px;
}
.row-centered .col-centered {
    padding: 0px 3px;
}
.row-centered .panel {
    padding: 10px 0px;
}
</style>
<?php
$pagePermissions = fetchPagePermissions(4);
$tab = Input::get('tab');

// To make this panel super admin only, uncomment out the lines below
// if($user->data()->id !='1'){
//   Redirect::to('account.php');
// }

//PHP Goes Here!
delete_user_online(); //Deletes sessions older than 24 hours
if($_SERVER["REMOTE_ADDR"]=="127.0.0.1" || $_SERVER["REMOTE_ADDR"]=="::1" || $_SERVER["REMOTE_ADDR"]=="localhost"){
$local = True;
}else{
$local = False;
}

$errors = $successes = [];

//Find users who have logged in in X amount of time.
$date = date("Y-m-d H:i:s");

$hour = date("Y-m-d H:i:s", strtotime("-1 hour", strtotime($date)));
$today = date("Y-m-d H:i:s", strtotime("-1 day", strtotime($date)));
$week = date("Y-m-d H:i:s", strtotime("-1 week", strtotime($date)));
$month = date("Y-m-d H:i:s", strtotime("-1 month", strtotime($date)));

$last24=time()-86400;

$recentUsersQ = $db->query("SELECT * FROM users_online WHERE timestamp > ? ORDER BY timestamp DESC",array($last24));
$recentUsersCount = $recentUsersQ->count();
$recentUsers = $recentUsersQ->results();

$usersHourQ = $db->query("SELECT * FROM users WHERE last_login > ?",array($hour));
$usersHour = $usersHourQ->results();
$hourCount = $usersHourQ->count();

$usersTodayQ = $db->query("SELECT * FROM users WHERE last_login > ?",array($today));
$dayCount = $usersTodayQ->count();
$usersDay = $usersTodayQ->results();

$usersWeekQ = $db->query("SELECT username FROM users WHERE last_login > ?",array($week));
$weekCount = $usersWeekQ->count();

$usersMonthQ = $db->query("SELECT username FROM users WHERE last_login > ?",array($month));
$monthCount = $usersMonthQ->count();

$usersQ = $db->query("SELECT * FROM users");
$user_count = $usersQ->count();

$pagesQ = $db->query("SELECT * FROM pages");
$page_count = $pagesQ->count();

$levelsQ = $db->query("SELECT * FROM permissions");
$level_count = $levelsQ->count();

$emailsQ = $db->query("SELECT COUNT(*) AS Count FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME <> ? GROUP BY TABLE_NAME",array(Config::get('mysql/db'),"email","id"));
$emails_count = $emailsQ->first()->Count;

$settingsQ = $db->query("SELECT * FROM settings");
$settings = $settingsQ->first();

$tomC = $db->query("SELECT * FROM audit")->count();

if($settings->recap_public  == "6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"  && $settings->recaptcha != 0) $recapWarning = 1;
else $recapWarning = 0;

$pwWarning = $db->query("SELECT password FROM users WHERE id = 1")->first();
if($pwWarning->password == "$2y$12$1v06jm2KMOXuuo3qP7erTuTIJFOnzhpds1Moa8BadnUUeX0RV3ex.") $pwWarning = 1;
else $pwWarning = 0;

$vcWarning = $db->query("SELECT vericode FROM users WHERE id = 1")->first();
if($vcWarning->vericode == "322418") $vcWarning = 1;
else $vcWarning = 0;


if(!emptY($_POST)) {
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include('../usersc/scripts/token_error.php');
  }if(!empty($_POST['settings'])){


	if($settings->recaptcha != $_POST['recaptcha']) {
		$recaptcha = Input::get('recaptcha');
		$fields=array('recaptcha'=>$recaptcha);
		$db->update('settings',1,$fields);
		$successes[] = "Updated recaptcha.";
		logger($user->data()->id,"Setting Change","Changed recaptcha from $settings->recaptcha to $recaptcha.");
	}

	if($settings->recap_public != $_POST['recap_public']) {
		$recap_public = Input::get('recap_public');
		$fields=array('recap_public'=>$recap_public);
		$db->update('settings',1,$fields);
		$successes[] = "Updated recaptcha key.";
		logger($user->data()->id,"Setting Change","Changed recaptcha public key from $settings->recap_public to $recap_public.");
	}

	if($settings->recap_private != $_POST['recap_private']) {
		$recap_private = Input::get('recap_private');
		$fields=array('recap_private'=>$recap_private);
		$db->update('settings',1,$fields);
		$successes[] = "Updated recaptcha key.";
		logger($user->data()->id,"Setting Change","Changed recaptcha private key from $settings->recap_private to $recap_private.");
	}

	if($settings->messaging != $_POST['messaging']) {
		$messaging = Input::get('messaging');
		$fields=array('messaging'=>$messaging);
		$db->update('settings',1,$fields);
		$successes[] = "Updated messaging.";
		logger($user->data()->id,"Setting Change","Changed messaging from $settings->messaging to $messaging.");
	}

	if($settings->echouser != $_POST['echouser']) {
		$echouser = Input::get('echouser');
		$fields=array('echouser'=>$echouser);
		$db->update('settings',1,$fields);
		$successes[] = "Updated echouser.";
		logger($user->data()->id,"Setting Change","Changed echouser from $settings->echouser to $echouser.");
	}

	if($settings->wys != $_POST['wys']) {
		$wys = Input::get('wys');
		$fields=array('wys'=>$wys);
		$db->update('settings',1,$fields);
		$successes[] = "Updated wys.";
		logger($user->data()->id,"Setting Change","Changed wys from $settings->wys to $wys.");
	}

	if($settings->site_name != $_POST['site_name']) {
		$site_name = Input::get('site_name');
		$fields=array('site_name'=>$site_name);
		$db->update('settings',1,$fields);
		$successes[] = "Updated site_name.";
		logger($user->data()->id,"Setting Change","Changed site_name from $settings->site_name to $site_name.");
	}

  if($settings->copyright != $_POST['copyright']) {
    $copyright = Input::get('copyright');
    $fields=array('copyright'=>$copyright);
    $db->update('settings',1,$fields);
    $successes[] = "Updated copyright.";
    logger($user->data()->id,"Setting Change","Changed copyright from $settings->copyright to $copyright.");
  }

	if($settings->force_ssl != $_POST['force_ssl']) {
		$force_ssl = Input::get('force_ssl');
		$fields=array('force_ssl'=>$force_ssl);
		$db->update('settings',1,$fields);
		$successes[] = "Updated force_ssl.";
		logger($user->data()->id,"Setting Change","Changed force_ssl from $settings->force_ssl to $force_ssl.");
	}

	if( $_POST['force_user_pr'] == 1) {
		$db->query("UPDATE users SET force_pr = 1");
		$successes[] = "Requiring all users to reset their password.";
		logger($user->data()->id,"User Manager","Forcing all users to reset password.");
	}
	if($settings->force_pr != $_POST['force_pr']) {
		$force_pr = Input::get('force_pr');
		$fields=array('force_pr'=>$force_pr);
		$db->update('settings',1,$fields);
		$successes[] = "Updated force_pr.";
		logger($user->data()->id,"Setting Change","Changed force_pr from $settings->force_pr to $force_pr.");
	}

	if($settings->site_offline != $_POST['site_offline']) {
		$site_offline = Input::get('site_offline');
		$fields=array('site_offline'=>$site_offline);
		$db->update('settings',1,$fields);
		$successes[] = "Updated site_offline.";
		logger($user->data()->id,"Setting Change","Changed site_offline from $settings->site_offline to $site_offline.");
	}

	if($settings->track_guest != $_POST['track_guest']) {
		$track_guest = Input::get('track_guest');
		$fields=array('track_guest'=>$track_guest);
		$db->update('settings',1,$fields);
		$successes[] = "Updated track_guest.";
		logger($user->data()->id,"Setting Change","Changed track_guest from $settings->track_guest to $track_guest.");
	}

  if($settings->custom_settings != $_POST['custom_settings']) {
    $custom_settings = Input::get('custom_settings');
    $fields=array('custom_settings'=>$custom_settings);
    $db->update('settings',1,$fields);
    $successes[] = "Updated custom_settings.";
    logger($user->data()->id,"Setting Change","Changed custom_settings from $settings->custom_settings to $custom_settings.");
  }

	if($settings->permission_restriction != $_POST['permission_restriction']) {
		$permission_restriction = Input::get('permission_restriction');
		if(empty($permission_restriction)) { $permission_restriction==0; }
		$fields=array('permission_restriction'=>$permission_restriction);
		$db->update('settings',1,$fields);
		$successes[] = "Updated permission_restriction.";
		logger($user->data()->id,"Setting Change","Changed permission_restriction from $settings->permission_restriction to $permission_restriction.");
	}

	if($settings->page_permission_restriction != $_POST['page_permission_restriction']) {
		$page_permission_restriction = Input::get('page_permission_restriction');
		if(empty($page_permission_restriction)) { $page_permission_restriction==0; }
		$fields=array('page_permission_restriction'=>$page_permission_restriction);
		$db->update('settings',1,$fields);
		$successes[] = "Updated page_permission_restriction.";
		logger($user->data()->id,"Setting Change","Changed page_permission_restriction from $settings->page_permission_restriction to $page_permission_restriction.");
	}

	if($settings->page_default_private != $_POST['page_default_private']) {
		$page_default_private = Input::get('page_default_private');
		if(empty($page_default_private)) { $page_default_private==0; }
		$fields=array('page_default_private'=>$page_default_private);
		$db->update('settings',1,$fields);
		$successes[] = "Updated page_default_private.";
		logger($user->data()->id,"Setting Change","Changed page_default_private from $settings->page_default_private to $page_default_private.");
	}

	if($settings->navigation_type != $_POST['navigation_type']) {
		$navigation_type = Input::get('navigation_type');
		if(empty($navigation_type)) { $navigation_type==0; }
		$fields=array('navigation_type'=>$navigation_type);
		$db->update('settings',1,$fields);
		$successes[] = "Updated navigation_type.";
		logger($user->data()->id,"Setting Change","Changed navigation_type from $settings->navigation_type to $navigation_type.");
	}

  if($settings->cron_ip != $_POST['cron_ip']) {
		$cron_ip = Input::get('cron_ip');
		$fields=array('cron_ip'=>$cron_ip);
		$db->update('settings',1,$fields);
		$successes[] = "Updated Cron IP.";
		logger($user->data()->id,"Setting Change","Changed notifications from $settings->cron_ip to $cron_ip.");
	}

	if($settings->notifications != $_POST['notifications']) {
		$notifications = Input::get('notifications');
		if(empty($notifications)) { $notifications==0; }
		$fields=array('notifications'=>$notifications);
		$db->update('settings',1,$fields);
		$successes[] = "Updated notifications.";
		logger($user->data()->id,"Setting Change","Changed notifications from $settings->notifications to $notifications.");
	}

  if($settings->force_notif != $_POST['force_notif']) {
    $force_notif = Input::get('force_notif');
    if(empty($force_notif)) { $force_notif==0; }
    $fields=array('force_notif'=>$force_notif);
    $db->update('settings',1,$fields);
    $successes[] = "Updated forced notifications.";
    logger($user->data()->id,"Setting Change","Changed forced notifications from $settings->force_notif to $force_notif.");
  }

	if($settings->notif_daylimit != $_POST['notif_daylimit']) {
		$notif_daylimit = Input::get('notif_daylimit');
		if(empty($notif_daylimit)) { $notif_daylimit==0; }
		$fields=array('notif_daylimit'=>$notif_daylimit);
		$db->update('settings',1,$fields);
		$successes[] = "Updated notif_daylimit.";
		logger($user->data()->id,"Setting Change","Changed notif_daylimit from $settings->notif_daylimit to $notif_daylimit.");
	}

	//Redirect::to('admin.php?tab='.$tab);
}

if(!empty($_POST['css'])){
	if($settings->us_css1 != $_POST['us_css1']) {
		$us_css1 = Input::get('us_css1');
		$fields=array('us_css1'=>$us_css1);
		$db->update('settings',1,$fields);
		$successes[] = "Updated us_css1.";
		logger($user->data()->id,"Setting Change","Changed us_css1 from $settings->us_css1 to $us_css1.");
	}
	if($settings->us_css2 != $_POST['us_css2']) {
		$us_css2 = Input::get('us_css2');
		$fields=array('us_css2'=>$us_css2);
		$db->update('settings',1,$fields);
		$successes[] = "Updated us_css2.";
		logger($user->data()->id,"Setting Change","Changed us_css2 from $settings->us_css2 to $us_css2.");
	}

	if($settings->us_css3 != $_POST['us_css3']) {
		$us_css3 = Input::get('us_css3');
		$fields=array('us_css3'=>$us_css3);
		$db->update('settings',1,$fields);
		$successes[] = "Updated us_css3.";
		logger($user->data()->id,"Setting Change","Changed us_css3 from $settings->us_css3 to $us_css3.");
	}
	Redirect::to('admin.php?msg=Updated+CSS+settings');
}

if(!empty($_POST['register'])){
	if($settings->auto_assign_un != $_POST['auto_assign_un']) {
		$auto_assign_un = Input::get('auto_assign_un');
		if(empty($auto_assign_un)) { $auto_assign_un==0; }
		$fields=array('auto_assign_un'=>$auto_assign_un);
		$db->update('settings',1,$fields);
		$successes[] = "Updated auto_assign_un.";
		logger($user->data()->id,"Setting Change","Changed auto_assign_un from $settings->auto_assign_un to $auto_assign_un.");
	}

  if($settings->registration != $_POST['registration']) {
    $registration = Input::get('registration');
    if(empty($registration)) { $registration==0; }
    $fields=array('registration'=>$registration);
    $db->update('settings',1,$fields);
    $successes[] = "Updated registration.";
    logger($user->data()->id,"Setting Change","Changed registration from $settings->registration to $registration.");
  }

  if($settings->twofa != $_POST['twofa']) {
    $twoorg = Input::get('twofa');
    if($twoorg==-1) $twofa=0;
    else $twofa=$twoorg;
    if(empty($twofa)) { $twofa==0; }
    if(!($settings->twofa==0 && $twofa==0)) {
      $fields=array('twofa'=>$twofa);
      $db->update('settings',1,$fields);
      $successes[] = "Updated twofa.";
      logger($user->data()->id,"Setting Change","Changed twofa from $settings->twofa to $twofa.");
    }
    if($twoorg==-1) {
      $db->query("UPDATE users SET twoKey=null,twoEnabled=0");
      $successes[] = "Reset all users Two FA.";
      logger($user->data()->id,"Two FA","Reset all Two FA for all accouts.");
    }
  }

	if($settings->change_un != $_POST['change_un']) {
		$change_un = Input::get('change_un');
		$fields=array('change_un'=>$change_un);
		$db->update('settings',1,$fields);
		$successes[] = "Updated change_un.";
		logger($user->data()->id,"Setting Change","Changed change_un from $settings->change_un to $change_un.");
	}

	if($settings->req_cap != $_POST['req_cap']) {
		$req_cap = Input::get('req_cap');
		$fields=array('req_cap'=>$req_cap);
		$db->update('settings',1,$fields);
		$successes[] = "Updated req_cap.";
		logger($user->data()->id,"Setting Change","Changed req_cap from $settings->req_cap to $req_cap.");
	}

	if($settings->req_num != $_POST['req_num']) {
		$req_num = Input::get('req_num');
		$fields=array('req_num'=>$req_num);
		$db->update('settings',1,$fields);
		$successes[] = "Updated req_num.";
		logger($user->data()->id,"Setting Change","Changed req_num from $settings->req_num to $req_num.");
	}

	if($settings->min_pw != $_POST['min_pw']) {
		$min_pw = Input::get('min_pw');
		$fields=array('min_pw'=>$min_pw);
		$db->update('settings',1,$fields);
		$successes[] = "Updated min_pw.";
		logger($user->data()->id,"Setting Change","Changed min_pw from $settings->min_pw to $min_pw.");
	}

	if($settings->max_pw != $_POST['max_pw']) {
		$max_pw = Input::get('max_pw');
		$fields=array('max_pw'=>$max_pw);
		$db->update('settings',1,$fields);
		$successes[] = "Updated max_pw.";
		logger($user->data()->id,"Setting Change","Changed max_pw from $settings->max_pw to $max_pw.");
	}

	if($settings->min_un != $_POST['min_un']) {
		$min_un = Input::get('min_un');
		$fields=array('min_un'=>$min_un);
		$db->update('settings',1,$fields);
		$successes[] = "Updated min_un.";
		logger($user->data()->id,"Setting Change","Changed min_un from $settings->min_un to $min_un.");
	}

	if($settings->max_un != $_POST['max_un']) {
		$max_un = Input::get('max_un');
		$fields=array('max_un'=>$max_un);
		$db->update('settings',1,$fields);
		$successes[] = "Updated max_un.";
		logger($user->data()->id,"Setting Change","Changed max_un from $settings->max_un to $max_un.");
	}
}

if(!empty($_POST['social'])){
	if($settings->glogin != $_POST['glogin']) {
		$glogin = Input::get('glogin');
		$fields=array('glogin'=>$glogin);
		$db->update('settings',1,$fields);
		$successes[] = "Updated glogin.";
		logger($user->data()->id,"Setting Change","Changed glogin from $settings->glogin to $glogin.");
	}

	if($settings->fblogin != $_POST['fblogin']) {
		$fblogin = Input::get('fblogin');
		$fields=array('fblogin'=>$fblogin);
		$db->update('settings',1,$fields);
		$successes[] = "Updated fblogin.";
		logger($user->data()->id,"Setting Change","Changed fblogin from $settings->fblogin to $fblogin.");
	}

	if($settings->gid != $_POST['gid']) {
		$gid = Input::get('gid');
		$fields=array('gid'=>$gid);
		$db->update('settings',1,$fields);
		$successes[] = "Updated gid.";
		logger($user->data()->id,"Setting Change","Changed gid from $settings->gid to $gid.");
	}

	if($settings->gsecret != $_POST['gsecret']) {
		$gsecret = Input::get('gsecret');
		$fields=array('gsecret'=>$gsecret);
		$db->update('settings',1,$fields);
		$successes[] = "Updated gsecret.";
		logger($user->data()->id,"Setting Change","Changed gsecret from $settings->gsecret to $gsecret.");
	}

	if($settings->gredirect != $_POST['gredirect']) {
		$gredirect = Input::get('gredirect');
		$fields=array('gredirect'=>$gredirect);
		$db->update('settings',1,$fields);
		$successes[] = "Updated gredirect.";
		logger($user->data()->id,"Setting Change","Changed gredirect from $settings->gredirect to $gredirect.");
	}

	if($settings->ghome != $_POST['ghome']) {
		$ghome = Input::get('ghome');
		$fields=array('ghome'=>$ghome);
		$db->update('settings',1,$fields);
		$successes[] = "Updated ghome.";
		logger($user->data()->id,"Setting Change","Changed ghome from $settings->ghome to $ghome.");
	}

	if($settings->fbid != $_POST['fbid']) {
		$fbid = Input::get('fbid');
		$fields=array('fbid'=>$fbid);
		$db->update('settings',1,$fields);
		$successes[] = "Updated fbid.";
		logger($user->data()->id,"Setting Change","Changed fbid from $settings->fbid to $fbid.");
	}

	if($settings->fbsecret != $_POST['fbsecret']) {
		$fbsecret = Input::get('fbsecret');
		$fields=array('fbsecret'=>$fbsecret);
		$db->update('settings',1,$fields);
		$successes[] = "Updated fbsecret.";
		logger($user->data()->id,"Setting Change","Changed fbsecret from $settings->fbsecret to $fbsecret.");
	}

	if($settings->fbcallback != $_POST['fbcallback']) {
		$fbcallback = Input::get('fbcallback');
		$fields=array('fbcallback'=>$fbcallback);
		$db->update('settings',1,$fields);
		$successes[] = "Updated fbcallback.";
		logger($user->data()->id,"Setting Change","Changed fbcallback from $settings->fbcallback to $fbcallback.");
	}

	if($settings->graph_ver != $_POST['graph_ver']) {
		$graph_ver = Input::get('graph_ver');
		$fields=array('graph_ver'=>$graph_ver);
		$db->update('settings',1,$fields);
		$successes[] = "Updated graph_ver.";
		logger($user->data()->id,"Setting Change","Changed graph_ver from $settings->graph_ver to $graph_ver.");
	}

	if($settings->finalredir != $_POST['finalredir']) {
		$finalredir = Input::get('finalredir');
		$fields=array('finalredir'=>$finalredir);
		$db->update('settings',1,$fields);
		$successes[] = "Updated finalredir.";
		logger($user->data()->id,"Setting Change","Changed finalredir from $settings->finalredir to $finalredir.");
	}

	//Redirect::to('admin.php?tab='.$tab);
}
$settingsQ = $db->query("SELECT * FROM settings");
$settings = $settingsQ->first();
  if($settings->custom_settings == 1){
  require_once('../usersc/includes/admin_panel_custom_settings_post.php');
}
}
//NEW token is created after $_POST
$token = Token::generate();
?>
<div id="page-wrapper"> <!-- leave in place for full-screen backgrounds etc -->
	<div class="container"> <!-- -fluid -->
<?php
include('includes/migrations.php');
if($pwWarning == 1 && !$local){ ?>
	<div class="alert alert-danger">
	  <strong>Warning!</strong> Please change the default password for the user 'admin' by clicking the manage users panel below.
	</div>
<?php } ?>

<?php if($vcWarning == 1){ ?>
	<div class="alert alert-danger">
	  <strong>Warning!</strong> You are using an insecure vericode. Please run <a href="update.php">update.php</a> to correct this.
	</div>
<?php } ?>

<?php if($recapWarning == 1 && !$local){ ?>
	<div class="alert alert-danger">
	  <strong>Warning!</strong> You are using the default reCaptcha keys. Please change them before going live.
	</div>
<?php } ?>
        <h1 class="text-center">UserSpice Dashboard Version <?=$user_spice_ver?></h1>
        <div class="row row-centered">

			<a href="<?=$us_url_root?>users/check_updates.php"><div class="col-md-1 col-sm-3 col-xs-6 col-centered">
                <div class="panel panel-default">
                    <i class="fa fa-arrow-up fa-2x"></i><br>Check<br>for Updates</li>
                </div>
            </div></a>

			<a href="<?=$us_url_root?>users/admin_backup.php"><div class="col-md-1 col-sm-3 col-xs-6 col-centered">
                <div class="panel panel-default">
                    <i class="fa fa-floppy-o fa-2x"></i><br>Backup<br>Project</li>
                </div>
            </div></a>

			<a href="<?=$us_url_root?>users/cron_manager.php"><div class="col-md-1 col-sm-3 col-xs-6 col-centered">
                <div class="panel panel-default">
                    <i class="fa fa-server fa-2x"></i><br>Manage<br>Cron Jobs</li>
                </div>
            </div></a>

      <?php if($settings->notifications == 1){ ?>
      <a href="<?=$us_url_root?>users/admin_notifications.php"><div class="col-md-1 col-sm-3 col-xs-6 col-centered">
                <div class="panel panel-default">
                    <i class="fa fa-bell fa-2x"></i><br>Manage<br>Notifications</li>
                </div>
            </div></a>
      <?php } ?>

			<a href="<?=$us_url_root?>users/admin_logs.php"><div class="col-md-1 col-sm-3 col-xs-6 col-centered">
                <div class="panel panel-default">
                    <i class="fa fa-area-chart fa-2x"></i><br>Manage<br>System Logs</li>
                </div>
            </div></a>

			<a href="<?=$us_url_root?>users/admin_messages.php"><div class="col-md-1 col-sm-3 col-xs-6 col-centered">
                <div class="panel panel-default">
                    <i class="fa fa-comment fa-2x"></i><br>Manage<br>Messages</li>
                </div>
            </div></a>

			<a href="<?=$us_url_root?>users/mqtt_settings.php"><div class="col-md-1 col-sm-3 col-xs-6 col-centered">
                <div class="panel panel-default">
                    <i class="fa fa-microchip fa-2x"></i><br>IOT<br>MQTT</li>
                </div>
            </div></a>
            <br>
			<a href="<?=$us_url_root?>users/admin_ips.php"><div class="col-md-1 col-sm-3 col-xs-6 col-centered">
                <div class="panel panel-default">
                    <i class="fa fa-ban fa-2x"></i><br>Whitelist &<br>Blacklist IPs</li>
                </div>
            </div></a>

			<a href="<?=$us_url_root?>users/admin_menus.php"><div class="col-md-1 col-sm-3 col-xs-6 col-centered">
                <div class="panel panel-default">
                    <i class="fa fa-bars fa-2x"></i><br>Menus<br>Navigation</li>
                </div>
            </div></a>
      <a href="<?=$us_url_root?>users/admin_users.php"><div class="col-md-1 col-sm-3 col-xs-6 col-centered">
          <div class="panel panel-default">
              <i class="fa fa-users fa-2x"></i><br>Manage <?=$user_count?><br>Users</li>
          </div>
      </div></a>
      <a href="<?=$us_url_root?>users/admin_permissions.php"><div class="col-md-1 col-sm-3 col-xs-6 col-centered">
          <div class="panel panel-default">
              <i class="fa fa-lock fa-2x"></i><br>Manage <?=$level_count?><br>Permissions</li>
          </div>
      </div></a>
      <a href="<?=$us_url_root?>users/admin_pages.php"><div class="col-md-1 col-sm-3 col-xs-6 col-centered">
          <div class="panel panel-default">
              <i class="fa fa-file-text fa-2x"></i><br>Manage <?=$page_count?><br>Pages</li>
          </div>
      </div></a>
      <a href="<?=$us_url_root?>users/email_settings.php"><div class="col-md-1 col-sm-3 col-xs-6 col-centered">
          <div class="panel panel-default">
              <i class="fa fa-paper-plane fa-2x"></i><br>Manage <?=$emails_count?><br>Email Settings</li>
          </div>
      </div></a>
      <br>
      <?php require_once("../usersc/includes/admin_panel_buttons.php"); ?>
        </div>

		<?=resultBlock($errors,$successes);?>

		<!-- CHECK IF ADDITIONAL ADMIN PAGES ARE PRESENT AND INCLUDE IF AVAILABLE -->

		<?php
		if(file_exists($abs_us_root.$us_url_root.'usersc/includes/admin_panels.php')){
			require_once $abs_us_root.$us_url_root.'usersc/includes/admin_panels.php';
		}
		?>

		<!-- /CHECK IF ADDITIONAL ADMIN PAGES ARE PRESENT AND INCLUDE IF AVAILABLE -->







	<!-- tabs -->
<div>
	<div class="row">
		<div class="col-md-12 col-xs-6">
			<div class="panel with-nav-tabs panel-default">
				<div class="panel-heading">
					<ul class="nav nav-tabs">
					<li <?php if($tab == 1 || $tab == ''){echo "class='active'";} ?>><a href="#tab1default" data-toggle="tab">Statistics</a></li>
						<li <?php if($tab == 2){echo "class='active'";}?>><a href="#tab2default" data-toggle="tab">Site Settings</a></li>
						<li <?php if($tab == 3){echo "class='active'";}?>><a href="#tab3default" data-toggle="tab">Registration</a></li>
						<li <?php if($tab == 4){echo "class='active'";}?>><a href="#tab4default" data-toggle="tab">Social Logins</a></li>
						<li <?php if($tab == 5){echo "class='active'";}?>><a href="#tab5default" data-toggle="tab">CSS Settings</a></li>
						<li <?php if($tab == 6){echo "class='active'";}?>><a href="#tab6default" data-toggle="tab">CSS Samples</a></li>
          <?php
            if($settings->custom_settings == 1){ ?>
            <li <?php if($tab == 7){echo "class='active'";}?>><a href="#tab7default" data-toggle="tab">Custom Settings</a></li>
          <?php } ?>
					</ul>
				</div>
				<div class="panel-body">
					<div class="tab-content">
						<div class="tab-pane fade <?php if($tab == 1 || $tab == ''){echo "in active";}?>" id="tab1default">
							<?php include('views/_admin_stats.php');?>
						</div>

						<div class="tab-pane fade <?php if($tab == 2){echo "in active";}?>" id="tab2default">
							<?php include('views/_admin_site_settings.php');?>
						</div>

						<div class="tab-pane fade <?php if($tab == 3){echo "in active";}?>" id="tab3default">
							<?php include('views/_admin_register_settings.php');?>
						</div>

						<div class="tab-pane fade <?php if($tab == 4){echo "in active";}?>" id="tab4default">
							<!-- css settings -->
							<?php include('views/_admin_login_settings.php');?>
						</div>

						<div class="tab-pane fade <?php if($tab == 5){echo "in active";}?>" id="tab5default">
							<!-- css settings -->
							<?php include('views/_admin_css_settings.php');?>
						</div>
						<div class="tab-pane fade <?php if($tab == 6){echo "in active";}?>" id="tab6default">
							<?php include('views/_admin_css_samples.php');?>
            </div>
            <?php
              if($settings->custom_settings == 1){ ?>
            <div class="tab-pane fade <?php if($tab == 7){echo "in active";}?>" id="tab7default">
							<?php include('../usersc/includes/admin_panel_custom_settings.php');?>
						</div>
          <?php } ?>


					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="col-xs-12 col-md-6"> <!-- Site Settings Column -->

</div> <!-- /col1/2 -->

<div class="col-xs-12 col-md-6"><!-- CSS Settings Column -->

</div> <!-- /col1/3 -->
</div> <!-- /row -->

<!-- Social Login -->
<div class="col-xs-12 col-md-12">

</div> <!-- /col1/3 -->
</div> <!-- /row -->




</div> <!-- /container -->
</div> <!-- /#page-wrapper -->

<!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->
<script type="text/javascript">
$(document).ready(function(){

	$("#times").load("times.php" );

	var timesRefresh = setInterval(function(){
		$("#times").load("times.php" );
	}, 30000);


	$('[data-toggle="tooltip"]').tooltip();
	$('[data-toggle="popover"]').popover();
	// -------------------------------------------------------------------------
});
</script>
<?php if(in_array($user->data()->id, $master_account)) {?>
<script type="text/javascript">
    $(document).ready(function(){
        $('#recapatcha_public_show').hover(function () {
            $('#recap_public').attr('type', 'text');
        }, function () {
            $('#recap_public').attr('type', 'password');
        });
				$('#recapatcha_private_show').hover(function () {
            $('#recap_private').attr('type', 'text');
        }, function () {
            $('#recap_private').attr('type', 'password');
        });
    });
</script>
<?php } ?>

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
