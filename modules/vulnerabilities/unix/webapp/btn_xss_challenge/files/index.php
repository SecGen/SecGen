<?php
session_start();
$id = $_SESSION['id'];
if (!$id) {
    header("Location: login.php");
    exit;
}
header('X-XSS-Protection: 0');
include "mysql.php";

if($_POST['profile_desc']) {
    $profile_desc = mysql_real_escape_string($_POST['profile_desc']);
    mysql_query("UPDATE users SET profile_desc='$profile_desc' WHERE id=$id");
}

if($_GET['id'] && $id == 1) {
    //Admin uses this to browse to other people's profiles.
    $id = $_GET['id'];
}

$is = mysql_query("SELECT * FROM users WHERE id='{$id}'") or die(mysql_error());
$ir = mysql_fetch_array($is);

?>
<style>
    textarea {
        width: 300px;
        height: 150px;
    }
</style>
<table border=0>
    <tr><td>
    <fieldset>
      <legend><select disabled='disabled' alt='Only admins can view all players profiles'>
        <option><?=$ir['username']?></option>
    </select>'s Profile</legend>
      <?=$ir['profile_desc']?>
      <br><br>
      <br><br>
      <br><br>
      <br><br>
      <br><br>
      <br><br>
      <br><br>
      <br><br>
      <br><br>
      <br><br>
      <hr>
      <?
        if($ir['id'] == 1) {
            $ctf_flag = getenv('CTF_FLAG');
            print "<font color='green'>CTF Flag: ".$ctf_flag."</font>";
        }
        else {
            print "<font color='red'>CTF Flag: [disabled] - Must be logged in as admin to access.</font>";
        }
      ?>
    </fieldset>

    </td><td>

    <fieldset>
      <legend>Update your Profile Description</legend>
      <form action="index.php" method="post" name="login" id="login">
        Current Value: <br>
        <textarea id="profile_desc" name="profile_desc" rows=5 cols=10 /><?=$ir['profile_desc']?></textarea><br>
        <input type="submit" value="Submit" />
      </form>
    </fieldset>

    <br><br><br>
    <script>
    function trigger_admin() {
        window.open('trigger_fake_admin.php?id='+<?=$id?>, 'Admin Simulation', 'status=1, height=485, width=420, left=100, top=100, resizable=0');
    }
    </script>
    <button onclick="trigger_admin()">Lay trap for admin to visit your profile...</button>

    <br><br><br>
    &gt; <a href='logout.php'>LOGOUT</a>
    </td></tr>
</table>
