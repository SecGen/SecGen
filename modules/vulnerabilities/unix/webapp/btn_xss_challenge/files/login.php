<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>BreakTheNet</title>
</head>
<body bgcolor="#C3C3C3">
    <h3>
      &gt; BreakTheNet Log-In
    </h3>
    <table width="80%">
      <tr>
        <td width="50%">
          <fieldset>
            <legend>About BreakTheNet</legend>
            An XSS challenge - see if you can become logged in as the "admin" user.<br><br>
            Note that to do so, you'll need to create your own account and create an XSS attack on your user profile.<br><br>
            For purposes of this challenge, anything you successfully "alert()" in the admin's browser will be passed along to you.<br><br>
            Feel free to review the source code as part of the challenge <a href='https://github.com/breakthenet/CTF-XSS-Challenge'>here</a>.
          </fieldset>
        </td>
        <td>
          <fieldset>
            <legend>Login</legend>
            <form action="authenticate.php" method="post" name="login" id="login">
              Username: <input type="text" name="username" /><br />
              Password: <input type="password" name="password" /><br />
              <input type="submit" value="Submit" />
            </form>
          </fieldset>
        </td>
      </tr>
    </table><br />
    <h3>
      <a href='register.php'>REGISTER NOW!</a>
    </h3><br />
  </body>
</html>
