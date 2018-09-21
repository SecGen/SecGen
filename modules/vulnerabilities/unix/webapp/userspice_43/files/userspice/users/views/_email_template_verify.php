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
    <p>Congratulations <?=$fname;?>,</p>
    <p>Thanks for signing up Please click the link below to verify your email address.</p>
    <p><a href="<?=$results->verify_url?>users/verify.php?email=<?=$email;?>&vericode=<?=$vericode;?>" class="nounderline">Verify Your Email</a></p>
    <p>Once you verify your email address you will be ready to login!</p>
    <p>See you soon!</p>
  </body>
</html>
