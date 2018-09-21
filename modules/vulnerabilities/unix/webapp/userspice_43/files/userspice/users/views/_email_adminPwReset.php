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
    <p>An Administrator of <?=$sitename?> has reset your password.</p>
    <p><label>Username:</label> <?=$username?></p>
    <p><label>Password:</label> <a href="<?php echo $results->verify_url."users/forgot_password_reset.php?email=".$email."&vericode=$vericode&reset=1"; ?>" class="nounderline">Set Password</a></p>
    <p>You will be required to set your password using the link above.</p>
    <p>See you soon!</p>
  </body>
</html>
