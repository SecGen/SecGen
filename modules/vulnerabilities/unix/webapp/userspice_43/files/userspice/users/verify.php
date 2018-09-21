<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/ ?>
<?php require_once 'init.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/header.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/navigation.php'; ?>

<?php
if(ipCheckBan()){Redirect::to($us_url_root.'usersc/scripts/banned.php');die();}
//if($user->isLoggedIn()) $user->logout();

$verify_success=FALSE;

$errors = array();
if(Input::exists('get')){

	$email = Input::get('email');
	$vericode = Input::get('vericode');

	$validate = new Validate();
	$validation = $validate->check($_GET,array(
	'email' => array(
	  'display' => 'Email',
	  'valid_email' => true,
	  'required' => true,
	),
	));
	if($validation->passed()){ //if email is valid, do this
		//get the user info based on the email
		$verify = new User(Input::get('email'));
		if ($verify->exists() && $verify->data()->vericode == $vericode){ //check if this email account exists in the DB
			if(null==Input::get('new') && !$verify->data()->email_new == NULL)	$verify->update(array('email_verified' => 1,'vericode' => randomstring(15),'email' => $verify->data()->email_new,'email_new' => NULL),$verify->data()->id);
			else $verify->update(array('email_verified' => 1,'vericode' => randomstring(15)),$verify->data()->id);
			$verify_success=TRUE;
			logger($verify->data()->id,"User","Verification completed via vericode.");
		}
	}else{
		$errors = $validation->errors();
	}
}

?>

<div id="page-wrapper">
<div class="container">

<?php

if ($verify_success){
	require 'views/_verify_success.php';
}else{
	require 'views/_verify_error.php';
}

?><br />
</div>
</div>

<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

  <!-- Place any per-page javascript here -->

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
