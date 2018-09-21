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
    <p>You have a new message from <?=$sendfname;?>!</p>
        <p><a href="<?=$results->verify_url?>users/message.php?id=<?=$msg_thread?>" class="nounderline">Click here</a> to reply or view the thread.</p>
        <hr />
    <?=html_entity_decode($body)?>
  </body>
</html>
