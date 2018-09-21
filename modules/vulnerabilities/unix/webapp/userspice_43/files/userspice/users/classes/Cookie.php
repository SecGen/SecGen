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
class Cookie {
	public static function exists($name){
		return (isset($_COOKIE[$name])) ? true : false;
	}

	public static function get($name){
		return $_COOKIE[$name];
	}

	public static function put($name, $value, $expiry, $path="/", $domain="", $secure=true, $httponly=true){
		if (setcookie($name, $value, time() + $expiry, $path, $domain, $secure, $httponly)) {
			return true;
		}
		return false;
	}

	public static function delete($name){
		self::put($name, '', time() - 1);
	}
}
