<?php

if(file_exists("install/index.php")){
	//perform redirect if installer files exist
	//this if{} block may be deleted once installed
	header("Location: install/index.php");
}

require_once 'users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/header.php';
require_once $abs_us_root.$us_url_root.'users/includes/navigation.php';
if(isset($user) && $user->isLoggedIn()){
}
?>

<div id="page-wrapper">
<div class="container">
<div class="row">
	<div class="col-xs-12">

		<div class="jumbotron">
			<h1>Welcome to <?php echo $settings->site_name;?></h1>
			<p class="text-muted">An Open Source PHP User Management Framework. </p>
			<p>
			<?php if($user->isLoggedIn()){$uid = $user->data()->id;?>
				<a class="btn btn-default" href="users/account.php" role="button">User Account &raquo;</a>
			<?php }else{?>
				<a class="btn btn-warning" href="users/login.php" role="button">Log In &raquo;</a>
				<a class="btn btn-info" href="users/join.php" role="button">Sign Up &raquo;</a>
			<?php } ?>
			</p>
		</div>
	</div>
</div>
<div class="row">
<?php
// To generate a sample notification, uncomment the code below.
// It will do a notification everytime you refresh index.php.
// $msg = 'This is a sample notification! <a href="'.$us_url_root.'users/logout.php">Go to Logout Page</a>';
// $notifications->addNotification($msg, $user->data()->id);
 ?>
<div class="col-md-6">
	<div class="panel panel-default">
		<div class="panel-heading"><strong>Step 1: Change your password!</strong></div>
		<div class="panel-body">You're going to login with the default username of <strong>admin</strong> and the default password of <strong>password</strong>.
		You can also login as a standard level user with the credentials of <strong>user</strong> and <strong>password</strong>.
		If you cannot login for some reason, edit the login.php file and uncomment out the lines<br> error_reporting(E_ALL);<br>
		ini_set('display_errors', 1);<br> to see if there are any errors in your server configuration.
		</div>
	</div><!-- /panel -->
</div><!-- /.col -->
<div class="col-md-6">
	<div class="panel panel-default">
		<div class="panel-heading"><strong>Step 2: Change some settings</strong></div>
		<div class="panel-body">You want to go to the Admin Dashboard. From there you can personalize your settings.
		You can decide whether or not you want to use reCaptcha, force SSL, or mess with some CSS.
		</div>
	</div><!-- /panel -->
</div><!-- /.col -->
</div><!-- /.row -->

<div class="row">
<div class="col-md-6">
	<div class="panel panel-default">
		<div class="panel-heading"><strong>Step 3: Explore</strong></div>
		<div class="panel-body">From the Admin Dashboard, you can go to Admin Permissions and add some new user levels.
		Then check out Admin Pages to decide which pages are private and which are public. Once you make a page private,
		you can decide how what level of access someone needs to access it.
		Any new pages you create in your site folder will automatically show up here.
		</div>
	</div><!-- /panel -->
</div><!-- /.col -->
<div class="col-md-6">
	<div class="panel panel-default">
		<div class="panel-heading"><strong>Step 4: Check out the other resources</strong></div>
		<div class="panel-body">The users/blank_pages folder contains a blank version of this page and one with the sidebar
		included for your convenience. There are also special_blanks that you can drop into your site folder and load up to
		check out all the things you can do with Bootstrap.
		</div>
	</div><!-- /panel -->
</div><!-- /.col -->
</div><!-- /.row -->

<div class="row">
<div class="col-md-6">
	<div class="panel panel-default">
		<div class="panel-heading"><strong>Step 5: Design and secure your own pages</strong></div>
		<div class="panel-body">Of course, using our blanks is the quickest way to get up and running,
		but you can also secure any page. Simply add this php code to the top of your page and it will
		perform a check to see if you've set any special permissions.<br/>
		require_once 'users/init.php';<br/>
		require_once $abs_us_root.$us_url_root.'users/includes/header.php';<br/>
		require_once $abs_us_root.$us_url_root.'users/includes/navigation.php';<br/>
		  if (!securePage($_SERVER['PHP_SELF'])){die();}
		</div>
	</div><!-- /panel -->
</div><!-- /.col -->
<div class="col-md-6">
	<div class="panel panel-default">
		<div class="panel-heading"><strong>Step 6: Check out the forums and documentation at <a target="_blank" href="http://UserSpice.com">UserSpice.com</strong></a></div>
		<div class="panel-body">That's where the latest options are and you can find people willing to help.
		No account is required for browsing the forums, but you will need to sign up to be able to post.
		</div>
	</div><!-- /panel -->
</div><!-- /.col -->
</div><!-- /.row -->

<div class="row">
<div class="col-md-6">
	<div class="panel panel-default">
		<div class="panel-heading"><strong>Step 7: Replace this ugly homepage with your own beautiful creation</strong></div>
		<div class="panel-body">Don't forget to swap out logo.png in the images folder with your own! If you're getting nagging
		message in the footer, <a href="https://www.google.com/recaptcha/admin#list">go get you some of your own reCAPTCHA keys</a>
		</div>
	</div><!-- /panel -->
</div><!-- /.col -->
<div class="col-md-6">
	<div class="panel panel-default">
		<div class="panel-heading"><strong>Step 8: Avoid editing the UserSpice files</strong></div>
		<div class="panel-body">But what if you want to change the UserSpice files?
		We have a solution that lets you edit our files and still not break future upgrades.
		For instance, if you want to modify the account.php file... just copy our file into
		the "usersc" folder.  Then you can edit away and your file will be loaded instead of ours!
		</div>
	</div><!-- /panel -->
</div><!-- /.col -->
</div><!-- /.row -->

<div class="row">
<div class="col-xs-12">
	<div class="well"><p>UserSpice is built using <a href="http://getbootstrap.com/">Twitter's Bootstrap</a>,
	so it is fully responsive and there is tons of documentation. The look and the feel can be changed very easily. </p>
	<p>Consider checking out <a href="http://bootsnipp.com">Bootsnipp</a> to see all the widgets and tools you can
	easily drop into UserSpice to get your project off the ground.
	</div>
</div><!-- /.col -->
</div><!-- /.row -->

</div> <!-- /container -->

</div> <!-- /#page-wrapper -->

<!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->


<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
