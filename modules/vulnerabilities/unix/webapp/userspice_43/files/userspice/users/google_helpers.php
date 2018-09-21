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

require_once $abs_us_root.$us_url_root.'users/src/Google/Google_Client.php';
require_once $abs_us_root.$us_url_root.'users/src/Google/contrib/Google_Oauth2Service.php';
$settingsQ = $db->query('SELECT * FROM settings');
$settings = $settingsQ->first();
if ($settings->glogin==0){
  die();
}
$gurl = $abs_us_root.$us_url_root;

//Getting the Google Info from the DB
$clientId = $settings->gid; //Google CLIENT ID
$clientSecret = $settings->gsecret; //Google CLIENT SECRET
$redirectUrl = $settings->gredirect;  //return url (url to script)
$homeUrl = $settings->ghome;  //return to home

$gClient = new Google_Client();
$gClient->setApplicationName('Login to codexworld.com');
$gClient->setClientId($clientId);
$gClient->setClientSecret($clientSecret);
$gClient->setRedirectUri($redirectUrl);

$google_oauthV2 = new Google_Oauth2Service($gClient);
?>
