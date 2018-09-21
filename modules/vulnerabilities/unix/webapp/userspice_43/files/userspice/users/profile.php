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
<?php require_once 'init.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/header.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/navigation.php'; ?>
<?php if (!securePage($_SERVER['PHP_SELF'])){die();} ?>
<?php
//PHP Goes Here!

if(isset($_GET['id'])) $userID = Input::get('id');
else $userID = $user->data()->id;

$userQ = $db->query("SELECT * FROM profiles LEFT JOIN users ON user_id = users.id WHERE user_id = ?",array($userID));
if ($userQ->count() > 0) {
	$thatUser = $userQ->first();

	if($user->isLoggedIn() && $user->data()->id == $userID)
		{
		$editbio = ' <small><a href="edit_profile.php">Edit Bio</a></small>';
		}
	else
		{
		$editbio = '';
		}

	$ususername = ucfirst($thatUser->username)."'s Profile";
	$grav = get_gravatar(strtolower(trim($thatUser->email)));
	$useravatar = '<img src="'.$grav.'" class="img-thumbnail" alt="'.$ususername.'">';
	$usbio = html_entity_decode($thatUser->bio);
	//Uncomment out the line below to see what's available to you.
	//dump($thisUser);
	}
else
	{
	$ususername = '404';
	$usbio = 'User not found';
	$useravatar = '';
	$editbio = ' <small><a href="/">Go to the homepage</a></small>';
	}
?>
   <div id="page-wrapper">

		 <div class="container">
				<!-- Main jumbotron for a primary marketing message or call to action -->
				<div class="well">
					<div class="row">
						<div class="col-xs-12 col-md-2">
							<p><?php echo $useravatar;?></p>
						</div>
						<div class="col-xs-12 col-md-10">
						<h1><?php echouser($userID);?>'s Profile</h1>
							<?php echo $usbio.$editbio;?>

					</div>
					</div>
				</div>

										<a class="btn btn-success" href="view_all_users.php" role="button">All Users</a>


    </div> <!-- /container --><br />

</div> <!-- /#page-wrapper -->

<!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>
<!-- Place any per-page javascript here -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
