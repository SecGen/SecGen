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

<?php
if (!securePage($_SERVER['PHP_SELF'])){die();}?>

<?php
//dealing with if the user is logged in
if($user->isLoggedIn() && !checkMenu(2,$user->data()->id)){
	if (($settings->site_offline==1) && (!in_array($user->data()->id, $master_account)) && ($currentPage != 'login.php') && ($currentPage != 'maintenance.php')){
		$user->logout();
		Redirect::to($us_url_root.'users/maintenance.php');
	}
}


$emailQ = $db->query("SELECT * FROM email");
$emailR = $emailQ->first();
// dump($emailR);
// dump($emailR->email_act);
//PHP Goes Here!
$errors=[];
$successes=[];
$userId = $user->data()->id;
$grav = get_gravatar(strtolower(trim($user->data()->email)));
$validation = new Validate();
$userdetails=$user->data();
//Temporary Success Message
$holdover = Input::get('success');
if($holdover == 'true'){
    bold("Account Updated");
}
//Forms posted
if(!empty($_POST)) {
    $token = $_POST['csrf'];
    if(!Token::check($token)){
				include('../usersc/scripts/token_error.php');
    }else {
        //Update display name
				//if (($settings->change_un == 0) || (($settings->change_un == 2) && ($user->data()->un_changed == 1)))
        if ($userdetails->username != $_POST['username'] && ($settings->change_un == 1 || (($settings->change_un == 2) && ($user->data()->un_changed == 0)))){
            $displayname = Input::get("username");
            $fields=array(
                'username'=>$displayname,
                'un_changed' => 1,
            );
            $validation->check($_POST,array(
                'username' => array(
                    'display' => 'Username',
                    'required' => true,
                    'unique_update' => 'users,'.$userId,
										'min' => $settings->min_un,
					          'max' => $settings->max_un
                )
            ));
            if($validation->passed()){
                if(($settings->change_un == 2) && ($user->data()->un_changed == 1)){
                    Redirect::to('user_settings.php?err=Username+has+already+been+changed+once.');
                }
                $db->update('users',$userId,$fields);
                $successes[]="Username updated.";
								logger($user->data()->id,"User","Changed username from $userdetails->username to $displayname.");
            }else{
                //validation did not pass
                foreach ($validation->errors() as $error) {
                    $errors[] = $error;
                }
            }
        }else{
            $displayname=$userdetails->username;
        }
        //Update first name
        if ($userdetails->fname != $_POST['fname']){
            $fname = ucfirst(Input::get("fname"));
            $fields=array('fname'=>$fname);
            $validation->check($_POST,array(
                'fname' => array(
                    'display' => 'First Name',
                    'required' => true,
                    'min' => 1,
                    'max' => 25
                )
            ));
            if($validation->passed()){
                $db->update('users',$userId,$fields);
                $successes[]='First name updated.';
								logger($user->data()->id,"User","Changed fname from $userdetails->fname to $fname.");
            }else{
                //validation did not pass
                foreach ($validation->errors() as $error) {
                    $errors[] = $error;
                }
            }
        }else{
            $fname=$userdetails->fname;
        }
        //Update last name
        if ($userdetails->lname != $_POST['lname']){
            $lname = ucfirst(Input::get("lname"));
            $fields=array('lname'=>$lname);
            $validation->check($_POST,array(
                'lname' => array(
                    'display' => 'Last Name',
                    'required' => true,
                    'min' => 1,
                    'max' => 25
                )
            ));
            if($validation->passed()){
                $db->update('users',$userId,$fields);
                $successes[]='Last name updated.';
								logger($user->data()->id,"User","Changed lname from $userdetails->lname to $lname.");
            }else{
                //validation did not pass
                foreach ($validation->errors() as $error) {
                    $errors[] = $error;
                }
            }
        }else{
            $lname=$userdetails->lname;
        }
				if(!empty($_POST['password']) || $userdetails->email != $_POST['email']) {
				//Check password for email or pw update
				if (password_verify(Input::get('old'),$user->data()->password)) {
        //Update email
        if ($userdetails->email != $_POST['email']){
            $email = Input::get("email");
						$confemail = Input::get("confemail");
            $fields=array('email'=>$email);
            $validation->check($_POST,array(
                'email' => array(
                    'display' => 'Email',
                    'required' => true,
                    'valid_email' => true,
                    'unique_update' => 'users,'.$userId,
                    'min' => 3,
                    'max' => 75
                )
            ));
            if($validation->passed()){
							if($confemail == $email) {
                if($emailR->email_act==0){$db->update('users',$userId,$fields); $successes[]='Email updated.'; logger($user->data()->id,"User","Changed email from $userdetails->email to $email."); }
                if($emailR->email_act==1){
                    $db->update('users',$userId,['email_new'=>$email]);
										//Send the email
										$options = array(
				              'fname' => $user->data()->fname,
				              'email' => rawurlencode($user->data()->email),
				              'vericode' => $user->data()->vericode,
				            );
				            $encoded_email=rawurlencode($email);
				            $subject = 'Verify Your Email';
				            $body =  email_body('_email_template_verify_new.php',$options);
				            $email_sent=email($email,$subject,$body);
				            if(!$email_sent) $errors[] = 'Email NOT sent due to error. Please contact site administrator.';
										else $successes[]="Email request received. Please check your email to perform verification.";
										if($emailR->email_act==1) logger($user->data()->id,"User","Requested change email from $userdetails->email to $email. Verification email sent.");
                }
          }
					else $errors[] = "Your email did not match.";
				 }else{
                //validation did not pass
                foreach ($validation->errors() as $error) {
                    $errors[] = $error;
                }
            }
        }else{
            $email=$userdetails->email;
        }
        if(!empty($_POST['password'])) {
            $validation->check($_POST,array(
                'password' => array(
                    'display' => 'New Password',
                    'required' => true,
                    'min' => $settings->min_pw,
                'max' => $settings->max_pw,
                ),
                'confirm' => array(
                    'display' => 'Confirm New Password',
                    'required' => true,
                    'matches' => 'password',
                ),
            ));
            foreach ($validation->errors() as $error) {
                $errors[] = $error;
            }
            if (empty($errors)) {
                //process
                $new_password_hash = password_hash(Input::get('password'),PASSWORD_BCRYPT,array('cost' => 12));
                $user->update(array('password' => $new_password_hash,'force_pr' => 0,'vericode' => randomstring(15),),$user->data()->id);
                $successes[]='Password updated.';
								logger($user->data()->id,"User","Updated password.");
            }
        }
    }
	else {
		$errors[]="Current password verification failed. Update failed. Please try again.";
		}
	}
 }
}
// mod to allow edited values to be shown in form after update
$user2 = new User();
$userdetails=$user2->data();
?>
<div id="page-wrapper">
    <div class="container">
        <div class="well">
            <div class="row">
                <div class="col-xs-12 col-md-2">
                    <p><img src="<?=$grav; ?>" class="img-thumbnail" alt="Generic placeholder thumbnail"></p>
                </div>
                <div class="col-xs-12 col-md-10">
                    <h1>Update your user settings</h1>
                    <strong>Want to change your profile picture? </strong><br> Visit <a href="https://en.gravatar.com/">https://en.gravatar.com/</a> and setup an account with the email address <?=$userdetails->email?>.  It works across millions of sites. It's fast and easy!<br>
                    <?php if(!$errors=='') {?><div class="alert alert-danger"><?=display_errors($errors);?></div><?php } ?>
                    <?php if(!$successes=='') {?><div class="alert alert-success"><?=display_successes($successes);?></div><?php } ?>

                    <form name='updateAccount' action='user_settings.php' method='post'>

                        <div class="form-group">
                            <label>Username</label>
                            <?php if (($settings->change_un == 0) || (($settings->change_un == 2) && ($userdetails->un_changed == 1)) ) {?>
															<div class="input-group">
																 <input  class='form-control' type='text' name='username' value='<?=$userdetails->username?>' readonly/>
																 <span class="input-group-addon"data-toggle="tooltip" title="<?php if($settings->change_un==0) {?>The Administrator has disabled changing usernames.<?php } if(($settings->change_un == 2) && ($userdetails->un_changed == 1)) {?>The Administrator set username changes to occur only once and you have done so already.<?php } ?>">Why can't I change this?</span>
															 </div>
                            <?php }else{ ?>
														<input  class='form-control' type='text' name='username' value='<?=$userdetails->username?>'>
                            <?php } ?>
                        </div>

                        <div class="form-group">
                            <label>First Name</label>
                            <input  class='form-control' type='text' name='fname' value='<?=$userdetails->fname?>' />
                        </div>

                        <div class="form-group">
                            <label>Last Name</label>
                            <input  class='form-control' type='text' name='lname' value='<?=$userdetails->lname?>' />
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input class='form-control' type='text' name='email' value='<?=$userdetails->email?>' />
														<?php if(!IS_NULL($userdetails->email_new)) {?><br /><div class="alert alert-danger">
															<p><strong>Please note</strong> there is a pending request to update your email to <?=$userdetails->email_new?>.</p>
															<p>Please use the verification email to complete this request.</p>
															<p>If you need a new verification email, please re-enter the email above and submit the request again.</p>
														</div><?php } ?>
                        </div>

												<div class="form-group">
                            <label>Confirm Email</label>
                            <input class='form-control' type='text' name='confemail' />
                        </div>

												<div class="form-group">
												<label>New Password</label>
	                      <div class="input-group" data-container="body">
	                        <span class="input-group-addon password_view_control" id="addon1"><span class="glyphicon glyphicon-eye-open"></span></span>
	                        <input  class="form-control" type="password" autocomplete="off" name="password" id="password" aria-describedby="passwordhelp">
													<span class="input-group-addon pwpopover" id="addon2" data-container="body" data-toggle="popover" data-placement="top" data-content="<?=$settings->min_pw?> char min, <?=$settings->max_pw?> max.">?</span>
	                      </div></div>

	                      <div class="form-group">
													<label>Confirm Password</label>
	                      <div class="input-group" data-container="body">
	                        <span class="input-group-addon password_view_control" id="addon3"><span class="glyphicon glyphicon-eye-open"></span></span>
	                        <input  type="password" autocomplete="off" id="confirm" name="confirm" class="form-control" >
	                       <span class="input-group-addon pwpopover" id="addon4" data-container="body" data-toggle="popover" data-placement="top" data-content="Must match the New Password">?</span>
											 </div></div>

											 <div class="form-group">
													 <label>Old Password, required for changing password or email</label>
													 <div class="input-group" data-container="body">
														 <span class="input-group-addon password_view_control" id="addon6"><span class="glyphicon glyphicon-eye-open"></span></span>
														 <input class='form-control' type='password' id="old" name='old' />
														 <span class="input-group-addon pwpopover" id="addon5" data-container="body" data-toggle="popover" data-placement="top" data-content="Required to change your password">?</span>
													 </div>
											 </div>

                        <input type="hidden" name="csrf" value="<?=Token::generate();?>" />

                        <p><input class='btn btn-primary' type='submit' value='Update' class='submit' /></p>
                        <p><a class="btn btn-info" href="account.php">Cancel</a></p>

                    </form>
                    <?php
                    if(isset($user->data()->oauth_provider) && $user->data()->oauth_provider != null){
                        echo "<strong>NOTE:</strong> If you originally signed up with your Google/Facebook account, you will need to use the forgot password link to change your password...unless you're really good at guessing.";
                    }
                    ?>
                </div>
            </div>
        </div>


    </div> <!-- /container -->

</div> <!-- /#page-wrapper -->


<!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->
<script type="text/javascript">
		$(document).ready(function(){
				$('.password_view_control').hover(function () {
						$('#old').attr('type', 'text');
						$('#password').attr('type', 'text');
						$('#confirm').attr('type', 'text');
				}, function () {
						$('#old').attr('type', 'password');
						$('#password').attr('type', 'password');
						$('#confirm').attr('type', 'password');
				});
		});
		$(function () {
			$('[data-toggle="popover"]').popover()
		})
		$('.pwpopover').popover();
		$('.pwpopover').on('click', function (e) {
				$('.pwpopover').not(this).popover('hide');
		});
</script>

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
