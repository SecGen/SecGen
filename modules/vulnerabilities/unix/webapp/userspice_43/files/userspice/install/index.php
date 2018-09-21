<?php require_once("install/includes/header.php"); ?>
<div class="container">
 <div class="row">
        <div class="col-xs-12">
            <ul class="nav nav-pills nav-justified thumbnail">
                <li class="active"><a href="#">
                    <h4 class="list-group-item-heading">Step 1</h4>
                    <p class="list-group-item-text"><?=$step1?></p>
                </a></li>
                <li><a href="#">
                    <h4 class="list-group-item-heading">Step 2</h4>
                    <p class="list-group-item-text"><?=$step2?></p>
                </a></li>
                <li><a href="#">
                    <h4 class="list-group-item-heading">Step 3</h4>
                    <p class="list-group-item-text"><?=$step3?></p>
                </a></li>
              </ul>
          </div>
          <div class="row">
              <div class="col-xs-1"></div>
              <div class="col-xs-10">
                <H2><strong>Welcome to <?=$app_name ." " . $app_ver?></strong></H2>

<p>
  This program will walk you through the entire process of configuring <?=$app_name?>.  Before you proceed, you might want to make sure that you're ready to do the install.</p>
<p>
  If you have not already created a new <font color="red"><strong>database</strong></font>, please do so at this time.  Make sure that you have the Host Name, Username, Password, and Database name handy, as you will need them to complete the install.</p>
<h3><strong>System Requirement Check</h3></strong>

<?php
// Check to make sure php is version is good enough
// Set your required PHP version in the install_settings file
if (version_compare(phpversion(), $php_ver, '<')) {
      // php version isn't high enough
      //The system is designed to do a full stop of you don't meet the minimum PHP version
?>
<p>We're sorry, but your PHP version is out of date.  Please update to PHP <?=$php_ver?> or later to continue.
<a href='http://php.net/' target='_blank'>PHP Website</a></p>
<?php
    } else {
?>
<p>Your PHP version meets the minimum system requirements of <?=$php_ver?> or later, but you need to make sure your system meets all the rest of the requirements. If you see any red in the table below, please correct those issues before installing.  <br><br>

    <table class="table-striped" width="100%">
      <tr>
        <td width="50%">
          PHP version >= <?=$php_ver?>
        </td>
        <td width="50%">
          <?php if (phpversion() < $php_ver) {
            ?>
            <strong><font color="red">No</font></strong>
            <?php
            $errors = 1;
          } else {
            ?>
            <strong><font color="green">Yes</font></strong>
            <?php
            $errors = 0;
          };
          ?>
        </td>
      </tr>
        <td>
          XML support
        </td>
        <td>
          <?php if (extension_loaded('xml')) {
            ?>
            <strong><font color="green">Available</font></strong>
            <?php
          } else {
            ?>
            <strong><font color="red">Unavailable</font></strong>
            <?php
            $errors = 1;
           };
          ?>
        </td>
      </tr>
      <tr>
        <td>
          MySQLi support
        </td>
        <td>
          <?php if (function_exists( 'mysqli_connect' )) {
            ?>
            <strong><font color="green">Available</font></strong>
            <?php
          } else {
            ?>
            <strong><font color="red">Unavailable</font></strong>
            <?php
            $errors = 1;
           };
          ?>
        </td>
      </tr>
      <tr>
        <td>
          PDO support
        </td>
        <td>
          <?php if (class_exists('PDO')) {
            ?>
            <strong><font color="green">Available</font></strong>
            <?php
          } else {
            ?>
            <strong><font color="red">Unavailable</font></strong>
            <?php
            $errors = 1;
           };
          ?>
        </td>
      </tr>
      <tr>
        <td>
          Is <?=$config_file?> writeable?
        </td>
        <td>
          <?php
          clearstatcache();
          if (@file_exists($config_file) &&  @is_writable( $config_file )){
            echo '<strong><font color="green">Writeable</font></strong>';
          } else {
            $errors = 1;
            ?>
            <strong><font color="red">Unwriteable<br />
            </font>
          It is really important that you be able to write to the init file! If you don't know how to chmod your init file, <a href="http://www.userspice.com/installation-issues/">please read this guide at UserSpice.com.</a>
          </strong>
            <?php
          }
          ?>
        </td>
      </tr>
    </table>

    <!-- <h3><strong>Directory Permissions</h3></strong>
    (You may be able to ignore unwriteable folders, especially on windows)
    <table class="table-striped" width="100%"> -->
      <?php
      //feel free to add more directories
      //dirStatus( '/' );
      //dirStatus( '/install' );
      ?>
    <!-- </table> -->
  <?php
}
?>

<h3><strong>Additional Recommended Settings</h3></strong>

  <p>
    <?=$app_name?> Will most likely work regardless of the settings below, however these settings are suggested.
  </p>
    <table class="table-striped" width="100%">
      <tr>
        <td width="50%">
          <strong>Setting</strong>
        </td>
        <td class="25%">
          <strong>Recommended</strong>
        </td>
        <td class="25%">
          <strong>Actual</strong>
        </td>
      </tr>
      <?php
      $php_recommended_settings = array(array ('Safe Mode','safe_mode','OFF'),
      array ('Display Errors','display_errors','ON'),
      array ('File Uploads','file_uploads','ON'),
      array ('Register Globals','register_globals','OFF'),
      array ('Output Buffering','output_buffering','OFF'),
      array ('Session Auto Start','session.auto_start','OFF'),
    );

    foreach ($php_recommended_settings as $phprec) {
      ?>
      <tr>
        <td>
          <?=$phprec[0]; ?>:
        </td>
        <td>
          <?=$phprec[2]; ?>:
        </td>
        <td>
          <strong>
            <?php
            if ( get_php_setting($phprec[1]) == $phprec[2] ) {
              ?>
              <font color="green">
                <?php
              } else {
                ?>
                <font color="red">
                  <?php
                }
                echo get_php_setting($phprec[1]);
                ?>
              </font>
            </strong>
            <td>
            </tr>
            <?php
          }
          ?>
        </table>
        <br>
        <?php if ($errors===0){
          ?>
          <div align="right">
          By clicking continue, you are stating that you agree with the terms of the <a href="license.php"><?=$app_name?> License.</a><br><br>
          <a href="step2.php" class="btn btn-primary">Continue >></a></div>
          <?php
        } elseif ($errors===1){
          ?>
          <font color="red"><strong>You have errors listed in the System Requirement Check that must be corrected before continuing. If you have an unwritable <?=$config_file?>, it is suggested that you chmod that file to 666 for installation and then chmod it to 644 after installation. <a href="http://www.userspice.com/installation-issues/">please read this guide</a>, or if you are comfortable importing a SQL dump and editing an init.php file manually, you can follow the "if install fails" instructions in the root folder.
           </font></strong>
          <?php
        }
?>
      </div>

    </div>

</div>
</div>


</div>
</div>
</div>
</body>
</html>
<?php


function get_php_setting($val) {
  $r =  (ini_get($val) == '1' ? 1 : 0);
  return $r ? 'ON' : 'OFF';
}

function dirStatus( $folder, $relative=1, $text='' ) {
  $writeable 		= '<strong><font color="green">Writeable</font></strong>';
  $unwriteable 	= '<strong><font color="red">Unwriteable</font></strong>';
?>
  <tr>
  <td width="50%"><?=$folder?></td>
  <td width="50%">
<?php
  if ( $relative ) {
    if (is_writable( "../$folder" )){
      echo $writeable;
    } else {
      echo $unwriteable;
      $errors = 1;
      }
  } else {
    if (is_writable( "$folder" )) {
      echo $writeable;
  }else {
      echo $unwriteable;
      $errors = 1;
      }
  }
?>
</tr>
<?php
}
?>
</div>



</div>
</div>
<?php require_once("install/includes/header.php"); ?>
