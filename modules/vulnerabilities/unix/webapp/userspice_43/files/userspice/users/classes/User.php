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
*/
class User {
	private $_db, $_data, $_sessionName, $_isLoggedIn, $_cookieName;
	public $tableName = 'users';



	public function __construct($user = null){
		$this->_db = DB::getInstance();
		$this->_sessionName = Config::get('session/session_name');
		$this->_cookieName = Config::get('remember/cookie_name');


		if (!$user) {
			if (Session::exists($this->_sessionName)) {
				$user = Session::get($this->_sessionName);

				if ($this->find($user)) {
					$this->_isLoggedIn = true;
				} else {
					//process Logout
				}
			}
		} else {
			$this->find($user);
		}
	}

	public function create($fields = array()){
		if (!$this->_db->insert('users', $fields)) {
			throw new Exception('There was a problem creating an account.');
		}else
		$user_id = $this->_db->lastId();
		$query = $this->_db->insert("user_permission_matches",['user_id'=>$user_id,'permission_id'=>1]);
		// return $user_id;
		$query2 = $this->_db->insert("profiles",['user_id'=>$user_id, 'bio'=>'This is your bio']);
		return $user_id;
	}

	public function find($user = null){
		if(isset($_SESSION['cloak_to'])){
			$user = $_SESSION['cloak_to'];
		}

		if ($user) {
			if(is_numeric($user)){
				$field = 'id';
			}elseif(!filter_var($user, FILTER_VALIDATE_EMAIL) === false){
				$field = 'email';
			}else{
				$field = 'username';
			}

			$data = $this->_db->get('users', array($field, '=', $user));

			if ($data->count()) {
				$this->_data = $data->first();
				if($this->data()->account_id == 0 && $this->data()->account_owner == 1){
					$this->_data->account_id = $this->_data->id;
				}
				return true;
			}
		}
		return false;
	}

	public function login($username = null, $password = null, $remember = false){

		if (!$username && !$password && $this->exists()) {
			Session::put($this->_sessionName, $this->data()->id);
		} else {
			$user = $this->find($username);
			if ($user) {
				if (password_verify($password,$this->data()->password)) {
					Session::put($this->_sessionName, $this->data()->id);
					if ($remember) {
						$hash = Hash::unique();
						$hashCheck = $this->_db->get('users_session' , array('user_id', '=', $this->data()->id));

							$this->_db->insert('users_session', array(
								'user_id' => $this->data()->id,
								'hash' => $hash,
								'uagent' => Session::uagent_no_version()
							));

						Cookie::put($this->_cookieName, $hash, Config::get('remember/cookie_expiry'));
					}
					$date = date("Y-m-d H:i:s");
					$this->_db->query("UPDATE users SET last_login = ?, logins = logins + 1 WHERE id = ?",[$date,$this->data()->id]);
					$this->_db->query("UPDATE users SET last_confirm = ? WHERE id = ?",[$date,$this->data()->id]);
					$this->_db->insert('logs',['logdate' => $date,'user_id' => $this->data()->id,'logtype' => "User",'lognote' => "User logged in."]);
					$ip = ipCheck();
					$q = $this->_db->query("SELECT id FROM us_ip_list WHERE ip = ?",array($ip));
					$c = $q->count();
					if($c < 1){
						$this->_db->insert('us_ip_list', array(
							'user_id' => $this->data()->id,
							'ip' => $ip,
						));
					}else{
						$f = $q->first();
						$this->_db->update('us_ip_list',$f->id, array(
							'user_id' => $this->data()->id,
							'ip' => $ip,
						));
					}
					return true;
				}
			}
		}
		return false;
	}

	public function loginEmail($email = null, $password = null, $remember = false){

		if (!$email && !$password && $this->exists()) {
			Session::put($this->_sessionName, $this->data()->id);
		} else {
			$user = $this->find($email);

			if ($user) {
				if (password_verify($password,$this->data()->password)) {
					Session::put($this->_sessionName, $this->data()->id);

					if ($remember) {
						$hash = Hash::unique();
						$hashCheck = $this->_db->get('users_session' , array('user_id', '=', $this->data()->id));

							$this->_db->insert('users_session', array(
								'user_id' => $this->data()->id,
								'hash' => $hash,
								'uagent' => Session::uagent_no_version()
							));

						Cookie::put($this->_cookieName, $hash, Config::get('remember/cookie_expiry'));
					}
					$date = date("Y-m-d H:i:s");
					$this->_db->query("UPDATE users SET last_login = ?, logins = logins + 1 WHERE id = ?",[$date,$this->data()->id]);
					$this->_db->query("UPDATE users SET last_confirm = ? WHERE id = ?",[$date,$this->data()->id]);
					$this->_db->insert('logs',['logdate' => $date,'user_id' => $this->data()->id,'logtype' => "User",'lognote' => "User logged in."]);
					$ip = ipCheck();
					$q = $this->_db->query("SELECT id FROM us_ip_list WHERE ip = ?",array($ip));
					$c = $q->count();
					if($c < 1){
						$this->_db->insert('us_ip_list', array(
							'user_id' => $this->data()->id,
							'ip' => $ip,
						));
					}else{
						$f = $q->first();
						$this->_db->update('us_ip_list',$f->id, array(
							'user_id' => $this->data()->id,
							'ip' => $ip,
						));
					}
					return true;
				}
			}
		}
		return false;
	}

	//Google oAuth Login Stuff
		public function  checkUser($oauth_provider,$oauth_uid,$fname,$lname,$email,$gender,$locale,$link,$picture){

			$this->_db = DB::getInstance();
			$this->_sessionName = Config::get('session/session_name');
			$this->_cookieName = Config::get('remember/cookie_name');
			$fakeUN = $email;
			$active = 1;
				//Check to see if a user has Google oAuth
				$prevQuery = $this->_db->query("SELECT * FROM users WHERE oauth_provider = '".$oauth_provider."' AND oauth_uid = '".$oauth_uid."'") or die("Google oAuth Error");

				//If a user is already setup with oAuth, get the latest info
				if($prevQuery->count() > 0){
					// die("user already has oauth");
					$update = $this->_db->query("UPDATE $this->tableName SET oauth_provider = '".$oauth_provider."', oauth_uid = '".$oauth_uid."', fname = '".$fname."', lname = '".$lname."', email = '".$email."', gender = '".$gender."', locale = '".$locale."', username = '".$fakeUN."',permissions = '".$active."',email_verfied = '".$active."',active = '".$active."',picture = '".$picture."', gpluslink = '".$link."', modified = '".date("Y-m-d H:i:s")."' WHERE oauth_provider = '".$oauth_provider."' AND oauth_uid = '".$oauth_uid."'") or die("Google oAuth Error");

				}else{
				//Check to see if the user has a regular UserSpice account that matches the google email.
				$findExistingUS=$this->_db->query("SELECT * FROM users WHERE email = ?",array($email));
				$foundUS=$findExistingUS->count();
				$found=$findExistingUS->count();


				if ($foundUS == 1){
					//Found an existing UserSpice user with the same email
					// die("user already has userspice");
					$this->_db->query("UPDATE users WHERE id = 3 SET lname = ?",array($email)) or die("Google oAuth Error");

				}else{
					//If a user has neither UserSpice nor oAuth creds
						//die("user has neither");
						$password = password_hash(Token::generate(),PASSWORD_BCRYPT,array('cost' => 12));
						$insert = $this->_db->query("INSERT INTO $this->tableName SET password = '".$password."',username = '".$email."',active = '".$active."',oauth_provider = '".$oauth_provider."', oauth_uid = '".$oauth_uid."',permissions = '".$active."', email_verified = '".$active."', fname = '".$fname."', lname = '".$lname."', email = '".$email."', gender = '".$gender."', locale = '".$locale."',picture = '".$picture."', gpluslink = '".$link."', join_date = '".date("Y-m-d H:i:s")."',created = '".date("Y-m-d H:i:s")."', modified = '".date("Y-m-d H:i:s")."'") or die("Google oAuth Error");
						$lastID = $insert->lastId();

						$insert2 = $this->_db->query("INSERT INTO user_permission_matches SET user_id = $lastID, permission_id = 1");

						$insert3 = $this->_db->query("INSERT INTO profiles SET user_id = $lastID, bio = 'This is your bio'");
				}


				}

				$query = $this->_db->query("SELECT * FROM $this->tableName WHERE oauth_provider = '".$oauth_provider."' AND oauth_uid = '".$oauth_uid."'") or die("Google oAuth Error");
				$result = $query->first();
				return $result;
			}
		// End of Google Section

	public function exists(){
		return (!empty($this->_data)) ? true : false;
	}

	public function data(){
		return $this->_data;
	}

	public function isLoggedIn(){
		return $this->_isLoggedIn;
	}

	public function notLoggedInRedirect($location){
		if($this->_isLoggedIn){
			return true;
		}else{
			Redirect::to($location);
		}
	}

	public function logout(){
		$this->_db->query("DELETE FROM users_session WHERE user_id = ? AND uagent = ?",array($this->data()->id,Session::uagent_no_version()));

		Session::delete($this->_sessionName);
		Cookie::delete($this->_cookieName);
		session_unset();
		session_destroy();
	}

	public function update($fields = array(), $id=null){

		if (!$id && $this->isLoggedIn()) {
			$id = $this->data()->id;
		}

		if (!$this->_db->update('users', $id, $fields)) {
			throw new Exception('There was a problem updating.');
		}
	}

	//This is for future versions of UserSpice
	public function hasPermission($key){
		$group = $this->_db->get('permissions', array('id', '=', $this->data()->permissions));
		if ($group->count()) {
			$permissions = json_decode($group->first()->permissions, true);
			if ($permissions[$key] == true) {
				return true;
			}
		}
		return false;
	}
	//This is for future versions of UserSpice
	public function noPermissionRedirect($perm,$location){
		if(!$this->hasPermission($perm)){
			Redirect::to($location);
		}else{
			return true;
		}
	}

}
