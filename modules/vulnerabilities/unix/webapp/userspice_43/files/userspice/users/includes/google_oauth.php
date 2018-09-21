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
?>
<?php

$value=null;
$gender=null;
$link=null;
if($settings->glogin==1 && !$user->isLoggedIn()){
	require_once $abs_us_root.$us_url_root.'users/helpers/google_helpers.php';
	if(isset($_REQUEST['code'])){
				$gClient->authenticate();
				$_SESSION['token'] = $gClient->getAccessToken();
				header('Location: ' . filter_var($redirectUrl, FILTER_SANITIZE_URL));
			}
			$gClient->setAccessType('online');
			$gClient->setApprovalPrompt('auto') ;
			if (isset($_SESSION['token'])) {
				$gClient->setAccessToken($_SESSION['token']);
			}

			if ($gClient->getAccessToken()) {
				$userProfile = $google_oauthV2->userinfo->get();
				//User Authenticated by Google
				if($settings->registration==0) {
					$findExistingUS=$db->query("SELECT * FROM users WHERE email = ?",array($userProfile['email']));
					if(!$findExistingUS->count()>0) {
						session_destroy();
						Redirect::to('users/join.php');
						die();
					}
				}
				$gUser = new User();
				$_SESSION["user"]=$value;
				//Deal with a user having an account but no google creds
				$findExistingUS=$db->query("SELECT * FROM users WHERE email = ?",array($userProfile['email']));
				$feusc = $findExistingUS->count();
				if($feusc>0){$feusr = $findExistingUS->first();}
				if($feusc == 1){
					$fields=array('gpluslink'=>'https://plus.google.com/'.$userProfile['id'],'picture'=>$userProfile['picture'],'locale'=>$userProfile['locale'],'gender'=>'unknown','oauth_provider'=>"google",'oauth_uid'=>$userProfile['id']);
					$db->update('users',$feusr->id,$fields);
					$date = date("Y-m-d H:i:s");
					$db->query("UPDATE users SET last_login = ?, logins = logins + 1 WHERE id = ?",[$date,$feusr->id]);
					$db->query("UPDATE users SET last_confirm = ? WHERE id = ?",[$date,$feusr->id]);
					$db->insert('logs',['logdate' => $date,'user_id' => $feusr->id,'logtype' => "User",'lognote' => "User logged in."]);
					$ip = ipCheck();
					$q = $db->query("SELECT id FROM us_ip_list WHERE ip = ?",array($ip));
					$c = $q->count();
					if($c < 1){
						$db->insert('us_ip_list', array(
							'user_id' => $feusr->id,
							'ip' => $ip,
						));
					}else{
						$f = $q->first();
						$db->update('us_ip_list',$f->id, array(
							'user_id' => $feusr->id,
							'ip' => $ip,
						));
					}
				}
				$gUser->checkUser('google',$userProfile['id'],$userProfile['given_name'],$userProfile['family_name'],$userProfile['email'],$gender,$userProfile['locale'],$link,$userProfile['picture']);
				//Add UserSpice info to session
				$_SESSION["user"]=$feusr->id;
				//Add Google info to the session
				$_SESSION['google_data'] = $userProfile;

				$_SESSION['token'] = $gClient->getAccessToken();


			} else {
				$authUrl = $gClient->createAuthUrl();

			}
		}
			// if(isset($authUrl)) {
			// 	echo '<a href="'.$authUrl.'"><img src="'
			// 	.$us_url_root.'/users/images/google.png" alt=""/></a>';
			// } else {
			// 	echo '<a href="users/logout.php?logout">Logout</a>';
			// }
      ?>
