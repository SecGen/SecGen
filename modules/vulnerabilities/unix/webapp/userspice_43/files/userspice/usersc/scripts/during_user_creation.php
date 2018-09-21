<?php
// This script is really useful for doing additional things when a user is created.

// You have access to two things that will really be helpful.
//
// You have the new user id for your new user. Comment out below to see it.
// dump($theNewId);

//You also have access to everything that was submitted in the form.
// dump($_POST);

//If you added additional fields to the join form, you can process them here.
//For example, in additional_join_form_fields.php we have a sample form field called account_id.
// You may wish to do additional validation, but we'll keep it simple. Uncomment out the code below to test it.

// The format of the array is ['column_name'=>Data_for_column]

// $db->update('users',$theNewId,['account_id'=>Input::get('account_id')]);

// You'll notice that the account id is now in the database!

// Even if you do not want to add additional fields to the the join form, this is a great opportunity to add this user to another database table.
// Get creative!

// The script below will automatically login a user who just registered if email activation is not turned on
$e = $db->query("SELECT email_act FROM email")->first();
if($e->email_act != 1){
  $user = new User();
  $login = $user->loginEmail(Input::get('email'), trim(Input::get('password')), 'off');
  if(!$login){Redirect::to('login.php?err=There+was+a+problem+logging+you+in+automatically.');}
  //where the user goes just after login is in usersc/scripts/custom_login_script.php
}
