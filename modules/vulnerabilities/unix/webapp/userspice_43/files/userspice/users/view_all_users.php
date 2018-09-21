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

if($user->isLoggedIn()) { $thisUserID = $user->data()->id;} else { $thisUserID = 0; }

$userQ = $db->query("SELECT * FROM users LEFT JOIN profiles ON users.id = user_id ");
// group active, inactive, on naughty step
$users = $userQ->results();
?>
<div id="page-wrapper">

  <div class="container">

    <!-- Page Heading -->
    <div class="row">

      <div class="col-xs-12 col-md-6">
		<h1 >View All Users</h1>
	  </div>

	  <div class="col-xs-12 col-md-6">
			<form class="">
				<label for="system-search">Search:</label>
				<div class="input-group">
                    <input class="form-control" id="system-search" name="q" placeholder="Search Users..." type="text">
                    <span class="input-group-btn">
						<button type="submit" class="btn btn-default"><i class="fa fa-times"></i></button>
                    </span>
                </div>
			</form>
		  </div>

	 </div>

		 <div class="row">
		     <div class="col-md-12">
 <div class="allutable table-responsive">
<table class='table table-hover table-list-search'>
<thead>
<tr>
  <th><div class="alluinfo">&nbsp;</div></th>
  <th></th>
 </tr>
</thead>
 <tbody>
<?php
//Cycle through users
foreach ($users as $v1) {

	$ususername = ucfirst($v1->username);
	$ususerbio = ucfirst($v1->bio);
	$grav = get_gravatar(strtolower(trim($v1->email)));
	$useravatar = '<img src="'.$grav.'" class="img-responsive img-thumbnail" alt="'.$ususername.'">';

?>

	<tr>
		<td>
			<a href="profile.php?id=<?=$v1->id?>"><?php echo $useravatar;?></a>
		</td>

		  <td>
			<h4><a href="profile.php?id=<?=$v1->id?>" class="nounderline"><?=echouser($v1->id)?>  </a></h4>
			<p><?=$ususerbio?></p>
		</td>
	</tr>
<?php } ?>
  </tbody>
</table>
	  </div>

      </div>
    </div>

    <!-- /.row -->
      </div>
    </div>
    <!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

    <!-- Place any per-page javascript here -->
<script src="js/search.js" charset="utf-8"></script>

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
