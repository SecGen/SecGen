<?php require_once '../users/init.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/header.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/navigation.php'; ?>
<?php if (!securePage($_SERVER['PHP_SELF'])){die();}?>
<?php
if($settings->twofa != 1){
  Redirect::to('account.php?err=Sorry.Two+factor+is+not+enabled+at+this+time');
}
if($user->data()->twoKey=='' || is_null($user->data()->twoKey) || $user->data()->twoEnabled==0) Redirect::to('account.php?err=Two FA is not enabled.');

if (!empty($_POST)) {
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include('../usersc/scripts/token_error.php');
  }

  if(!empty($_POST['twoChange']) && $settings->twofa == 1) {
        $twofa=Input::get('twofa');
        if($twofa==1) {
          $db->query("UPDATE users SET twoKey=null,twoEnabled=0 WHERE id = ?",[$user->data()->id]);
          logger($user->data()->id,"Two FA","Disabled Two FA");
          Redirect::to('account.php?msg=Two FA has been disabled.');
        }
      }
    }
?>

<div id="page-wrapper">
  <div class="container">
    <div class="well">
      <div class="row">
      	<div class="col-xs-12 col-md-3">
              <p><a href="account.php" class="btn btn-primary">Account Home</a></p>

          </div>
          <div class="col-xs-12 col-md-9">
              <h1>Manage 2-Factor</h1>
              <p>Are you sure you want to disable 2FA? Your account will no longer be protected.</p>
              <form class="verify-admin" action="disable2fa.php" method="POST" id="payment-form">
              <div class="col-md-5">
              <div class="input-group">
                <select name="twofa" id="twofa" class="form-control">
                  <option value="0">No, keep it on!</option>
                  <option value="1">Yes, turn it off...</option>
                </select>
                  <span class="input-group-btn">
                  <input class='btn btn-primary' type='submit' name='twoChange' value='Submit' />
                </span></div>
              <input type="hidden" value="<?=Token::generate();?>" name="csrf">
              <? } ?>
              </div>
               </div>
             </form><br />
          </div>
        </div>
      </div>
    </div>
  </div>

<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; ?>
