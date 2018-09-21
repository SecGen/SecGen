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
$validation = new Validate();
$userID = $user->data()->id;
$grav = get_gravatar(strtolower(trim($user->data()->email)));
$profileQ = $db->query("SELECT * FROM profiles WHERE user_id = ?",array($userID));
$thisProfile = $profileQ->first();
$id = $thisProfile->id;
//Uncomment out the 2 lines below to see what's available to you.
// dump($user);
// dump($thisProfile);

//Forms posted
if(!empty($_POST)) {
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      include('../usersc/scripts/token_error.php');
    }else {
      if ($thisProfile->bio != $_POST['bio']){
        $newBio = $_POST['bio'];
        $fields=array('bio'=>$newBio);
        $validation->check($_POST,array(
          'bio' => array(
            'display' => 'Bio',
            'required' => true
          )
        ));
      if($validation->passed()){
        $db->update('profiles',$id,$fields);
        Redirect::to('profile.php?id='.$userID);
      }
    }
  }
}
?>

	  <div id="page-wrapper">

		 <div class="container">

				<!-- Main jumbotron for a primary marketing message or call to action -->
				<div class="well">
					<div class="row">
						<div class="col-xs-12 col-md-2">
							<p><img src="<?=$grav; ?>" alt=""class="left-block img-thumbnail" alt="Generic placeholder thumbnail"></p>
						</div>
						<div class="col-xs-12 col-md-10">
						<h1><?=echouser($user->data()->id)?>'s Profile</h1>

          <form name="update_bio" action="edit_profile.php" method="post">
    <div align="center"><textarea rows="20" cols="80"  id="mytextarea" name="bio" ><?=$thisProfile->bio;?></textarea></div>
          <input type="hidden" name="csrf" value="<?=Token::generate();?>" >
		</p>
		  <p>
			<button type="submit" class="btn btn-primary" name="update_bio">Update Bio</button>
			<a class="btn btn-info" href="profile.php?id=<?php echo $userID;?>">Cancel</a>

</p>

			 </form>

					</div>
					</div>
				</div>


    </div> <!-- /container -->

</div> <!-- /#page-wrapper -->

<?php if ($settings->wys == 1){  ?>
<script src='//cdn.tinymce.com/4/tinymce.min.js'></script>
<script>
tinymce.init({
  selector: '#mytextarea'
});
</script>
<?php } ?>

    <!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

    <!-- Place any per-page javascript here -->

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
