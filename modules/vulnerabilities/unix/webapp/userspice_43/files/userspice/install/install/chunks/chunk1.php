),
'remember'        => array(
  'cookie_name'   => 'pmqesoxiw318374csb',
  'cookie_expiry' => 604800  //One week, feel free to make it longer
),
'session' => array(
  'session_name' => 'user',
  'token_name' => 'token',
)
);

//If you changed your UserSpice or UserCake database prefix
//put it here.
$db_table_prefix = "uc_";  //Old database prefix

//adding more ids to this array allows people to access everything, whether offline or not. Use caution.
$master_account = [1];

$currentPage = currentPage();

//Check to see if user has a remember me cookie
if(Cookie::exists(Config::get('remember/cookie_name')) && !Session::exists(Config::get('session/session_name'))){
	$hash = Cookie::get(Config::get('remember/cookie_name'));
	$hashCheck = DB::getInstance()->query("SELECT * FROM users_session WHERE hash = ? AND uagent = ?",array($hash,Session::uagent_no_version()));

	if ($hashCheck->count()) {
		$user = new User($hashCheck->first()->user_id);
		$user->login();

	}
}

//Check to see that user is logged in on a temporary password
$user = new User();

//Check to see that user is verified
if($user->isLoggedIn()){
	if($user->data()->email_verified == 0 && $currentPage != 'verify.php' && $currentPage != 'logout.php' && $currentPage != 'verify_thankyou.php'){
		Redirect::to('users/verify.php');
	}
}
