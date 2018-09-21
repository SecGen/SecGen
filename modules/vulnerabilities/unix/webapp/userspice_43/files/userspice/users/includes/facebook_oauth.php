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
<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '256684718065631',
      xfbml      : true,
      version    : 'v2.7'
    });
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
</script>

<?php
$db=DB::getInstance();

$settingsQ=$db->query("SELECT * FROM settings");
$settings=$settingsQ->first();

$appID=$settings->fbid;
$secret=$settings->fbsecret;
$version=$settings->graph_ver;
$callback=$settings->fbcallback;

if(!isset($_SESSION)){session_start();}
require_once($abs_us_root.$us_url_root."users/src/Facebook/autoload.php");
$fb = new Facebook\Facebook([
  'app_id' => $appID,
  'app_secret' => $secret,
  'default_graph_version' => $version,
  ]);

$helper = $fb->getRedirectLoginHelper();

$permissions = ['email']; // Optional permissions
$loginUrl = $helper->getLoginUrl($callback, $permissions);

echo '<a href="' . htmlspecialchars($loginUrl) . '"><img align=right src="'
.$us_url_root.'users/images/facebook.png" alt=""/></a>';
?>
