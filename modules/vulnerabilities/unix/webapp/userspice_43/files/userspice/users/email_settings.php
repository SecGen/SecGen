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
$query = $db->query("SELECT * FROM email");
$results = $query->first();
$errors = $successes = [];
// What to look for
$search = "Redirect::to('verify.php');";
// Read from file
$lines = file('init.php');
foreach($lines as $line)
{
  if(strpos($line, $search) !== false)
  bold("<br><br>You have a bug in your init.php that cannot be patched automatically.<br><br>Please replace verify.php with users/verify.php towards the bottom of your init.php file.");
}


$urlProtocol=isset($_SERVER['HTTPS']) ? 'https://' : 'http://';


if(!empty($_POST)){

  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include('../usersc/scripts/token_error.php');
  }

  if($results->smtp_server != $_POST['smtp_server']) {
    $smtp_server = Input::get('smtp_server');
    $fields=array('smtp_server'=>$smtp_server);
    $db->update('email',1,$fields);
    $successes[] = "Updated smtp_server";
    logger($user->data()->id,"Email Settings","Updated smtp_server from $results->smtp_server to $smtp_server.");
  }
  else{
  }
  if($results->website_name != $_POST['website_name']) {
    $website_name = Input::get('website_name');
    $fields=array('website_name'=>$website_name);
    $db->update('email',1,$fields);
    $successes[] = "Updated website_name";
    logger($user->data()->id,"Email Settings","Updated website_name from $results->website_name to $website_name.");
  }
  else{
  }
  if($results->smtp_port != $_POST['smtp_port']) {
    $smtp_port = Input::get('smtp_port');
    $fields=array('smtp_port'=>$smtp_port);
    $db->update('email',1,$fields);
    $successes[] = "Updated smtp_port";
    logger($user->data()->id,"Email Settings","Updated smtp_port from $results->smtp_port to $smtp_port.");
  }else{
  }
  if($results->email_login != $_POST['email_login']) {
    $email_login = Input::get('email_login');
    $fields=array('email_login'=>$email_login);
    $db->update('email',1,$fields);
    $successes[] = "Updated email_login";
    logger($user->data()->id,"Email Settings","Updated email_login.");
  }else{
  }
  if($results->email_pass != $_POST['email_pass']) {
    $email_pass = Input::get('email_pass');
    $fields=array('email_pass'=>$email_pass);
    $db->update('email',1,$fields);
    $successes[] = "Updated email_pass";
    logger($user->data()->id,"Email Settings","Updated email_pass.");
  }else{
  }
  if($results->from_name != $_POST['from_name']) {
    $from_name = Input::get('from_name');
    $fields=array('from_name'=>$from_name);
    $db->update('email',1,$fields);
    $successes[] = "Updated from_name";
    logger($user->data()->id,"Email Settings","Updated from_name from $results->from_name to $from_name.");
  }else{
  }
  if($results->from_email != $_POST['from_email']) {
    $from_email = Input::get('from_email');
    $fields=array('from_email'=>$from_email);
    $db->update('email',1,$fields);
    $successes[] = "Updated from_email";
    logger($user->data()->id,"Email Settings","Updated from_email from $results->from_email to $from_email.");
  }else{
  }
  if($results->transport != $_POST['transport']) {
    $transport = Input::get('transport');
    $fields=array('transport'=>$transport);
    $db->update('email',1,$fields);
    $successes[] = "Updated transport";
    logger($user->data()->id,"Email Settings","Updated transport from $results->transport to $transport.");
  }else{
  }
  if($results->verify_url != $_POST['verify_url']) {
    $verify_url = Input::get('verify_url');
    $fields=array('verify_url'=>$verify_url);
    $db->update('email',1,$fields);
    $successes[] = "Updated verify_url";
    logger($user->data()->id,"Email Settings","Updated verify_url from $results->verify_url to $verify_url.");
  }else{
  }
  if($results->email_act != $_POST['email_act']) {
    $email_act = Input::get('email_act');
    $fields=array('email_act'=>$email_act);
    $db->update('email',1,$fields);
    $successes[] = "Updated email_act";
    logger($user->data()->id,"Email Settings","Updated email_act from $results->email_act to $email_act.");
  }else{
  }
  if($results->debug_level != $_POST['debug_level']) {
    $debug_level = Input::get('debug_level');
    $fields=array('debug_level'=>$debug_level);
    $db->update('email',1,$fields);
    $successes[] = "Updated debug_level";
    logger($user->data()->id,"Email Settings","Updated email_act from $results->debug_level to $debug_level.");
  }else{
  }
  if($results->isSMTP != $_POST['isSMTP']) {
    $isSMTP = Input::get('isSMTP');
    $fields=array('isSMTP'=>$isSMTP);
    $db->update('email',1,$fields);
    $successes[] = "Updated isSMTP";
    logger($user->data()->id,"Email Settings","Updated isSMTP from $results->isSMTP to $isSMTP.");
  }else{
  }
  if($results->isHTML != $_POST['isHTML']) {
    $isHTML = Input::get('isHTML');
    $fields=array('isHTML'=>$isHTML);
    $db->update('email',1,$fields);
    $successes[] = "Updated isHTML";
    logger($user->data()->id,"Email Settings","Updated isHTML from $results->isHTML to $isHTML.");
  }else{
  }
  if($results->useSMTPauth != $_POST['useSMTPauth']) {
    $useSMTPauth = Input::get('useSMTPauth');
    $fields=array('useSMTPauth'=>$useSMTPauth);
    $db->update('email',1,$fields);
    $successes[] = "Updated useSMTPauth";
    logger($user->data()->id,"Email Settings","Updated useSMTPauth from $results->useSMTPauth to $useSMTPauth.");
  }else{
  }
  if(isset($_POST['update_and_test'])){
    Redirect::to("email_test.php");
  }else{
  //  Redirect::to("email_settings.php");
  }
  $query = $db->query("SELECT * FROM email");
  $results = $query->first();
}

?>
<div id="page-wrapper">

  <div class="container">

    <!-- Page Heading -->
    <div class="row">
      <div class="col-sm-12">

        <!-- Content Goes Here. Class width can be adjusted -->

        <h1>Setup your email server</h1>
        <p>
          These settings control all things email-related for the server including emailing your users and verifying the user's email address.
          You must obtain and verify all settings below for YOUR email server or hosting provider. Encryption with TLS is STRONGLY recommended,
          followed by SSL. No encryption is like shouting your login credentials out into a crowded field and is not supported for now.
        </p>
      </p>It is <strong>HIGHLY</strong> recommended that you test your email settings before turning on the feature to require new users to verify their email<br>

      <?=resultBlock($errors,$successes);?>

      <form name='update' action='email_settings.php' method='post'>

        <label>Website Name:</label>
        <input required size='50' class='form-control' type='text' name='website_name' value='<?=$results->website_name?>' />

        <label>SMTP Server:</label>
        <input required size='50' class='form-control' type='text' name='smtp_server' value='<?=$results->smtp_server?>' />

        <label>SMTP Port:</label>
        <input required size='50' class='form-control' type='text' name='smtp_port' value='<?=$results->smtp_port?>' />

        <label>Email Login/Username:</label>
        <input required size='50' class='form-control' type='password' name='email_login' value='<?=$results->email_login?>' />

        <label>Email Password:</label>
        <input required size='50' class='form-control' type='password' name='email_pass' value='<?=$results->email_pass?>' />

        <label>From Name (For Sent Emails):</label>
        <input required size='50' class='form-control' type='text' name='from_name' value='<?=$results->from_name?>' />

        <label>From Email (For Sent Emails):</label>
        <input required size='50' class='form-control' type='text' name='from_email' value='<?=$results->from_email?>' />

        <label>Transport:</label>
        <select class="form-control" name="transport">
          <option value="tls" <?php if($results->transport=='tls') echo 'selected="selected"'; ?> >TLS (encrypted)</option>
          <option value="ssl" <?php if($results->transport=='ssl') echo 'selected="selected"'; ?> >SSL (encrypted, but weak)</option>
        </select>

        <label>Email Debugging Level: <br/></label>
        0=Off, 1=Client Messages, 2=Normal Debug, 3=More Verbose, 4=Extremely Verbose<br>
        Debugging should be off in production projects for security reasons
        <select class="form-control" width="100%" name="debug_level">
          <option value="<?=$results->debug_level?>"><?=$results->debug_level?></option>
          <option value="0">0</option>
          <option value="1">1</option>
          <option value="1">2</option>
          <option value="1">3</option>
          <option value="1">4</option>
        </select>

        <label>Use isSMTP Feature: <br/></label>
        Use this if your email keeps failing and you know your credentials are correct.<br>
        <select class="form-control" width="100%" name="isSMTP">
          <?php if($results->isSMTP == 0){
            echo "<option value='0'>No</option>";
            echo "<option value='1'>Yes</option>";
          }else{
            echo "<option value='1'>Yes</option>";
            echo "<option value='0'>No</option>";
          } ?>
        </select>

        <label>Use SMTP Authentication: (Almost always, yes)<br/></label>
        <select class="form-control" width="100%" name="useSMTPauth">
          <?php if($results->useSMTPauth == 'false'){
            echo "<option value='false'>No</option>";
            echo "<option value='true'>Yes</option>";
          }else{
            echo "<option value='true'>Yes</option>";
            echo "<option value='false'>No</option>";
          } ?>
        </select>


        <label>Send email as HTML by default: <br/></label>
        <select class="form-control" width="100%" name="isHTML">
          <?php if($results->isHTML == 'false'){
            echo "<option value='false'>No</option>";
            echo "<option value='true'>Yes</option>";
          }else{
            echo "<option value='true'>Yes</option>";
            echo "<option value='false'>No</option>";
          } ?>
        </select>



        <label>Root URL of your UserSpice install including http or https protocol (VERY Important) <br/><div class="text-muted"> <?="Default location would be: ".$urlProtocol.$_SERVER['HTTP_HOST'].$us_url_root?></div></label>
        <input required  size='50' class='form-control' type='text' name='verify_url' value='<?=$results->verify_url?>' />


        <label>Require User to Verify Their Email?:</label>
        <input type="radio" name="email_act" value="1" <?php echo ($results->email_act==1)?'checked':''; ?> size="25">Yes</input>
        <input type="radio" name="email_act" value="0" <?php echo ($results->email_act==0)?'checked':''; ?> size="25">No</input>

        <input type="hidden" name="csrf" value="<?=Token::generate();?>" /><br><br>
        <input class='btn btn-primary' name="update_only" type='submit' value='Update Email Settings' class='submit' />
        <input class='btn btn-danger' name="update_and_test" type='submit' value='Update and Test Email Settings' class='submit' />
      </form>

    </div>    <!-- /.row -->
  </div><br />
</div>
</div>
<!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
