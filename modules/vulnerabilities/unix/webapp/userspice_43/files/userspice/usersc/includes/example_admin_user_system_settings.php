<?php require_once '../users/init.php'; ?>
<?php if (!securePage($_SERVER['PHP_SELF'])){die();} ?>
<?php  //You can use this file to add a "System Settings" button to the admin_user.php page and add any settings you want in there. Run our PHP and HTML from here. ?>
<?php //If you edit directly from this file don't forget  to rename it to remove the "example_" so it will be detected. ?>
<?php if(!empty($_POST)) {
  //Your PHP here!!!
  $userdetails = fetchUserDetails(NULL, NULL, $userId);
      } ?>

<?php /*Your HTML here! No need to make a form, just make your inputs, for example:
   <label>Exempt Messages?</label>
   <input type="checkbox" name="msg_exempt" value="1" <?php if($userdetails->msg_exempt==1){?>checked<?php } ?>/> <br /> */?>
