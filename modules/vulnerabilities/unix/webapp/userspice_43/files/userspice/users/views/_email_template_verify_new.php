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
    <p>A request to change your email was made from within your user account.</p>
    <p><a href="<?=$results->verify_url?>users/verify.php?new&email=<?=$email;?>&vericode=<?=$vericode;?>" class="nounderline">Verify Your Email</a></p>
    <p>Thank you!</p>
  </body>
</html>
