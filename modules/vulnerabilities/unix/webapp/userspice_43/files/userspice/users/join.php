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
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
ini_set("allow_url_fopen", 1);
?>
<?php require_once 'init.php';?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/header.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/navigation.php';
use PragmaRX\Google2FA\Google2FA;
if($settings->twofa == 1){
$google2fa = new Google2FA();
}
?>

<?php if (!securePage($_SERVER['PHP_SELF'])){die();} ?>
<?php
if(ipCheckBan()){Redirect::to($us_url_root.'usersc/scripts/banned.php');die();}
if($user->isLoggedIn()) Redirect::to('index.php');
$settingsQ = $db->query("SELECT * FROM settings");
$settings = $settingsQ->first();
if($settings->recaptcha == 1 || $settings->recaptcha == 2){
        require_once("../users/includes/recaptcha.config.php");
}
//There is a lot of commented out code for a future release of sign ups with payments
$form_method = 'POST';
$form_action = 'join.php';
$vericode = randomstring(15);

$form_valid=FALSE;

//Decide whether or not to use email activation
$query = $db->query("SELECT * FROM email");
$results = $query->first();
$act = $results->email_act;

//Opposite Day for Pre-Activation - Basically if you say in email
//settings that you do NOT want email activation, this lists new
//users as active in the database, otherwise they will become
//active after verifying their email.
if($act==1){
        $pre = 0;
} else {
        $pre = 1;
}

$reCaptchaValid=FALSE;

if(Input::exists()){
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include('../usersc/scripts/token_error.php');
  }
        $fname = Input::get('fname');
        $lname = Input::get('lname');
        $email = Input::get('email');
        if($settings->auto_assign_un==1) {
        $preusername = $fname[0];
        $preusername .= $lname;
        $preQ = $db->query("SELECT username FROM users WHERE username = ?",array($preusername));
        $preQCount = $preQ->count();
        if($preQCount == 0)
        {
                $username = strtolower($preusername);
        }
        else
        {
                $preusername2 = $fname;
                $preusername2 .= $lname[0];
                $preQ2 = $db->query("SELECT username FROM users WHERE username = ?",array($preusername2));
                $preQCount2 = $preQ2->count();
                        if($preQCount2 == 0)
                        {
                                $username = strtolower($preusername2);
                        }
                        else
                        {
                                $username = $email;
                        }
        } }
        if($settings->auto_assign_un==0) $username = Input::get('username');
        $agreement_checkbox = Input::get('agreement_checkbox');

        if ($agreement_checkbox=='on'){
                $agreement_checkbox=TRUE;
        }else{
                $agreement_checkbox=FALSE;
        }

        $db = DB::getInstance();
        $settingsQ = $db->query("SELECT * FROM settings");
        $settings = $settingsQ->first();
        $validation = new Validate();
        if($settings->auto_assign_un==0) {
        $validation->check($_POST,array(
          'username' => array(
                'display' => 'Username',
                'required' => true,
                'min' => $settings->min_un,
                'max' => $settings->max_un,
                'unique' => 'users',
          ),
          'fname' => array(
                'display' => 'First Name',
                'required' => true,
                'min' => 1,
                'max' => 60,
          ),
          'lname' => array(
                'display' => 'Last Name',
                'required' => true,
                'min' => 1,
                'max' => 60,
          ),
          'email' => array(
                'display' => 'Email',
                'required' => true,
                'valid_email' => true,
                'unique' => 'users',
          ),

          'password' => array(
                'display' => 'Password',
                'required' => true,
                'min' => $settings->min_pw,
                'max' => $settings->max_pw,
          ),
          'confirm' => array(
                'display' => 'Confirm Password',
                'required' => true,
                'matches' => 'password',
          ),
        )); }
        if($settings->auto_assign_un==1) {
          $validation->check($_POST,array(
            'fname' => array(
                  'display' => 'First Name',
                  'required' => true,
                  'min' => 1,
                  'max' => 60,
            ),
            'lname' => array(
                  'display' => 'Last Name',
                  'required' => true,
                  'min' => 1,
                  'max' => 60,
            ),
            'email' => array(
                  'display' => 'Email',
                  'required' => true,
                  'valid_email' => true,
                  'unique' => 'users',
            ),

            'password' => array(
                  'display' => 'Password',
                  'required' => true,
                  'min' => $settings->min_pw,
                  'max' => $settings->max_pw,
            ),
            'confirm' => array(
                  'display' => 'Confirm Password',
                  'required' => true,
                  'matches' => 'password',
            ),
          ));
        }

        //if the agreement_checkbox is not checked, add error
        if (!$agreement_checkbox){
                $validation->addError(["Please read and accept terms and conditions"]);
        }

        if($validation->passed() && $agreement_checkbox){
                //Logic if ReCAPTCHA is turned ON
        if($settings->recaptcha == 1 || $settings->recaptcha == 2){
                        require_once("../users/includes/recaptcha.config.php");
                        //reCAPTCHA 2.0 check
                        $response = null;

                        // check secret key
                        $reCaptcha = new ReCaptcha($settings->recap_private);

                        // if submitted check response
                        if ($_POST["g-recaptcha-response"]) {
                                $response = $reCaptcha->verifyResponse(
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["g-recaptcha-response"]);
                        }
                        if ($response != null && $response->success) {
                                // account creation code goes here
                                $reCaptchaValid=TRUE;
                                $form_valid=TRUE;
                        }else{
                                $reCaptchaValid=FALSE;
                                $form_valid=FALSE;
                                $validation->addError(["Please check the reCaptcha box."]);
                        }

                } //else for recaptcha

                if($reCaptchaValid || $settings->recaptcha == 0){

                        //add user to the database
                        $user = new User();
                        $join_date = date("Y-m-d H:i:s");
                        $params = array(
                                'fname' => Input::get('fname'),
                                'email' => $email,
                                'username' => $username,
                                'vericode' => $vericode,
                        );

                        if($act == 1) {
                                //Verify email address settings
                                $to = rawurlencode($email);
                                $subject = 'Welcome to '.$settings->site_name;
                                $body = email_body('_email_template_verify.php',$params);
                                email($to,$subject,$body);
                        }
                        try {
                                // echo "Trying to create user";
                                $user->create(array(
                                        'username' => $username,
                                        'fname' => ucfirst(Input::get('fname')),
                                        'lname' => ucfirst(Input::get('lname')),
                                        'email' => Input::get('email'),
                                        'password' => password_hash(Input::get('password', true), PASSWORD_BCRYPT, array('cost' => 12)),
                                        'permissions' => 1,
                                        'account_owner' => 1,
                                        'join_date' => $join_date,
                                        'email_verified' => $pre,
                                        'active' => 1,
                                        'vericode' => $vericode,
                                ));
                                        $theNewId=$db->lastId();

                        } catch (Exception $e) {
                                die($e->getMessage());
                        }
                        if($settings->twofa == 1){
                        $twoKey = $google2fa->generateSecretKey();
                        $db->update('users',$theNewId,['twoKey' => $twoKey]);
                        }
                        include('../usersc/scripts/during_user_creation.php');
                        Redirect::to($us_url_root.'users/joinThankYou.php');
                        if($act==1) logger($theNewId,"User","Registration completed and verification email sent.");
                        if($act==0) logger($theNewId,"User","Registration completed.");
                }

        } //Validation and agreement checbox
} //Input exists

?>
<?php header('X-Frame-Options: DENY'); ?>
<div id="page-wrapper">
<div class="container">
<?php
if($settings->registration==1) {
  if($settings->glogin==1 && !$user->isLoggedIn()){
    require_once $abs_us_root.$us_url_root.'users/includes/google_oauth_login.php';
  }
  if($settings->fblogin==1 && !$user->isLoggedIn()){
    require_once $abs_us_root.$us_url_root.'users/includes/facebook_oauth.php';
  }
  require '../users/views/_join.php';
}
else {
  require '../users/views/_joinDisabled.php';
}
?>

</div>
</div>

<!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<?php if($settings->recaptcha == 1 || $settings->recaptcha == 2){ ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
    function submitForm() {
        document.getElementById("payment-form").submit();
    }
</script>
<?php } ?>
<?php if($settings->auto_assign_un==0) { ?>
<script type="text/javascript">
$(document).ready(function(){
    var x_timer;
    $("#username").keyup(function (e){
        clearTimeout(x_timer);
        var username = $(this).val();
        if (username.length > 0) {
            x_timer = setTimeout(function(){
                check_username_ajax(username);
            }, 500);
        }
        else $('#usernameCheck').text('');
    });

    function check_username_ajax(username){
        $("#usernameCheck").html('Checking...');
        $.post('parsers/existingUsernameCheck.php', {'username': username}, function(response) {
            if (response == 'error') $('#usernameCheck').html('There was an error while checking the username.');
            else if (response == 'taken') { $('#usernameCheck').html('<i class="glyphicon glyphicon-remove" style="color: red; font-size: 12px"></i> This username is taken.');
            $('#next_button').prop('disabled', true); }
            else if (response == 'valid') { $('#usernameCheck').html('<i class="glyphicon glyphicon-ok" style="color: green; font-size: 12px"></i> This username is not taken.');
            $('#next_button').prop('disabled', false); }
            else { $('#usernameCheck').html('');
            $('#next_button').prop('disabled', false); }
        });
    }
});
</script>
<?php } ?>
<script type="text/javascript">
    $(document).ready(function(){
        $('#password_view_control').hover(function () {
            $('#password').attr('type', 'text');
            $('#confirm').attr('type', 'text');
        }, function () {
            $('#password').attr('type', 'password');
            $('#confirm').attr('type', 'password');
        });
    });
</script>



<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
