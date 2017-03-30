<?php
session_start();
require "mysql.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>breakthenet</title>
</head>
<body bgcolor="#C3C3C3">

<?
if ($_POST['username'])
{
    $username = mysql_real_escape_string($_POST['username']);
    $q = mysql_query("SELECT * FROM users WHERE username='$username'");
    if (mysql_num_rows($q))
    {
        print "Username already in use. Choose another.";
    }
    else
    {
        mysql_query("INSERT INTO users (username, password) VALUES( '{$username}', md5('{$_POST['password']}'))");
        print "You have signed up, enjoy the game.<br />&gt; <a href='login.php'>Login</a>";
    }
}
else
{
	?>
    <h3>
      Register
    </h3>
    <form action="register.php" method="post">
      Username: <input type="text" name="username" maxlength="20" /><br />
	  <!-- Username max length of 20 enforced at the database level. Not sure if it actually throws an error though, so stop them on frontend. -->
      Password: <input type="password" name="password" /><br />
      <input type="submit" value="Submit" />
    </form><br />
    &gt; <a href='login.php'>Go Back</a>
	<?
}
?>
</body>
</html>
