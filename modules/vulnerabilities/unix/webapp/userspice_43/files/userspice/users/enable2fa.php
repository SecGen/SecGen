<?php require_once '../users/init.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/header.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/navigation.php'; ?>
<?php if (!securePage($_SERVER['PHP_SELF'])){die();}?>
<?php
if($settings->twofa != 1){
  Redirect::to('account.php?err=Sorry.Two+factor+is+not+enabled+at+this+time');
}

use PragmaRX\Google2FA\Google2FA;
$google2fa = new Google2FA();

if(IS_NULL($user->data()->twoKey)) $db->update('users',$user->data()->id,['twoKey'=>$google2fa->generateSecretKey()]);
$twoUser = $db->query("SELECT email,twoKey FROM users WHERE id = ?",[$user->data()->id])->first();

$google2fa_url = $google2fa->getQRCodeGoogleUrl(
    $settings->site_name,
    $twoUser->email,
    $twoUser->twoKey
);
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
              <p>Scan this QR code with your authenticator app or input the key: <b><?php echo $twoUser->twoKey; ?></b></p>
              <p><img src="<?php echo $google2fa_url; ?>"></p>
              <p>Then enter one of your one-time passkeys here:</p>
              <p>
                  <table border="0">
                      <tr>
                          <td><input class="form-control" placeholder="2FA Code" type="text" name="twoCode" id="twoCode" size="10" required autofocus></td>
                          <td><button id="twoBtn" class="btn btn-primary">Verify</button></td>
                      </tr>
                  </table>
              </p>
          </div>
        </div>
      </div>
    </div>
  </div>

<!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->
<script>
    $(document).ready(function() {
        var input = document.getElementById("twoCode");
        input.addEventListener("keyup", function(event) {
          event.preventDefault();
          if (event.keyCode === 13) {
            document.getElementById("twoBtn").click();
          }
        });
        $("#twoBtn").click(function(e){
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "api/",
                data: {
                    action: "verify2FA",
                    twoCode: $("#twoCode").val()
                },
                success: function(result) {
                    var resultO = JSON.parse(result);
                    if(!resultO.error){
                        window.location.replace("account.php?msg=Two FA has been verified and enabled.");
                    }else{
                        alert('Incorrect 2FA code.');
                    }
                },
                error: function(result) {
                    alert('There was a problem verifying 2FA. Please check Internet or contact support.');
                }
            });
        });
    });
</script>
<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
