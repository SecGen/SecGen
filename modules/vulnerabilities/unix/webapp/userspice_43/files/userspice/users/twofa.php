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
<?php if(!$user->isLoggedIn()) Redirect::to('login.php'); ?>
<?php if(!$settings->twofa==1) Redirect::to('account.php'); ?>
<?php if(!$_SESSION['twofa']==1) Redirect::to('account.php'); ?>
<?php
$errors = $successes = [];
$form_valid=TRUE;

if (!empty($_POST)) {
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include('../usersc/scripts/token_error.php');
  }

  if(!empty($_POST['verifyTwo']) && $settings->twofa == 1) {
    $google2fa = new PragmaRX\Google2FA\Google2FA();
      $twoPassed = false;
      $twoQ = $db->query("select twoKey from users where id = ? and twoEnabled = 1", [$user->data()->id]);
      if($twoQ->count() > 0){
          $twoKey = $twoQ->results()[0]->twoKey;
          $twoCode = trim(Input::get('twoCode'));
          if($google2fa->verifyKey($twoKey, $twoCode) == true){
              $twoPassed = true;
          }
        }
        if($twoQ->count()==0)  $twoPassed=true;
        if($twoPassed==true) {
          unset($_SESSION['twofa']);
          logger($user->data()->id,"Two FA","Two FA Verification passed.");
          $dest=Input::get('dest');
          if (!empty($dest) || !$dest=='') {
            $redirect=htmlspecialchars_decode(Input::get('redirect'));
            if(!empty($redirect) || $redirect!=='') Redirect::to($redirect);
            else Redirect::to($dest);
          }
          elseif (file_exists($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script.php')) {
            require_once $abs_us_root.$us_url_root.'usersc/scripts/custom_login_script.php';
          }
          else {
            if (($dest = Config::get('homepage')) ||
              ($dest = 'account.php')) {
              #echo "DEBUG: dest=$dest<br />\n";
              #die;
              Redirect::to($dest);
            }
          }
        }
        elseif($twoPassed==false) {
          if($twoCode=='' || empty($twoCode)) $errors[] = "<strong>Login Failed</strong> Two Factor Auth Code was not present. Please try again.";
          else $errors[] = "<strong>Login Failed</strong> Two Factor Auth Code was invalid. Please try again.";
          logger($user->data()->id,"Two FA","Two FA Verification failed.");
        }
        else {
          $errors[] = "Fatal error. Please contact System Admin.";
          logger($user->data()->id,"Two FA","Two FA Verification Fatal Error.");
        }
      }
    }
$dest=Input::get('dest');
$redirect=Input::get('redirect');
?>
<div id="page-wrapper">

  <div class="container">

    <!-- Page Heading -->
    <div class="row">
<?=resultBlock($errors,$successes);?>
<? if ($actual_link !='') { ?>
        <div class="col-xs-12 col-md-6">
        <h1>Two Factor Authentication</h1>
      </div>

     </div>
    <div class="row">
    <form class="verify-admin" action="twofa.php" method="POST" id="payment-form">
    <div class="col-md-5">
    <div class="input-group"><input type="text" class="form-control"  name="twoCode" id="twoCode"  placeholder="2FA Code" autocomplete="off" required autofocus>
        <span class="input-group-btn">
        <input class='btn btn-primary' type='submit' name='verifyTwo' value='Verify' />
      </span></div>
    <input type="hidden" name="dest" value="<?=$dest?>" />
    <input type="hidden" name="redirect" value="<?=$redirect?>" />
    <input type="hidden" value="<?=Token::generate();?>" name="csrf">
    <? } ?>
    </div>
     </div>
   </form><br />
   </div>
   </div>


  </div>
</div>
    <!-- End of main content section -->

<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

    <!-- Place any per-page javascript here -->

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
