<?php
$db = DB::getInstance();
$query = $db->query("SELECT * FROM email");
$results = $query->first();
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    <p>Hello <?=$fname;?>,</p>
    <p>An Administrator of <?=$sitename?> has issued you an account. <?php if($force_pr == 0) {?>Please <a href="<?=$results->verify_url?>users/login.php" class="nounderline">click here</a> to login.<?php } ?></p>
    <p><label>Username:</label> <?=$username?></p>
    <p><label>Password:</label> <?php if($force_pr == 1) {?><a href="<?php echo $results->verify_url."users/forgot_password_reset.php?email=".$email."&vericode=$vericode&reset=1"; ?>" class="nounderline">Set Password</a><?php } else { ?><?=$password?><?php } ?></p>
    <p><?php if($force_pr == 1) {?>You will be required to set your password using the link above.<?php } else { ?>It is recommended to change your password upon logging in.<?php } ?></p>
    <p>See you soon!</p>
  </body>
</html>
