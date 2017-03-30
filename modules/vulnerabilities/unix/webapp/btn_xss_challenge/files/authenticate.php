<?php
session_start();
include "mysql.php";

$username = mysql_real_escape_string($_POST['username']);
$password = md5($_POST['password']);
$uq = mysql_query("SELECT `id`, `password` FROM `users` WHERE `username` = '$username' AND `password` = '$password'", $c);
if (mysql_num_rows($uq) == 0)
{
    die("Invalid username or password!<br /><a href='login.php'>&gt; Back</a>");
}
else
{
    $mem = mysql_fetch_assoc($uq);
    $_SESSION['id'] = $mem['id'];
    setcookie("hint", 'use-these-cookies-to-login-as-admin', time()+36000);
    header("Location: /index.php");
    exit;
}
