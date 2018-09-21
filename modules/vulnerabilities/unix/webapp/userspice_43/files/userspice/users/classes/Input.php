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
class Input {
	public static function exists($type = 'post'){
		switch ($type) {
			case 'post':
				return (!empty($_POST)) ? true : false;
				break;

			case 'get':
				return (!empty($_GET)) ? true : false;

			default:
				return false;
				break;
		}
	}

	public static function get($item){
		if (isset($_POST[$item])) {
			/*
			If the $_POST item is an array, process each item independently, and return array of sanitized items.
			*/
			if (is_array($_POST[$item])){
				$postItems=array();
				foreach ($_POST[$item] as $postItem){
					$postItems[]=self::sanitize($postItem);
				}
				return $postItems;
			}else{
				return self::sanitize($_POST[$item]);
			}

		} elseif(isset($_GET[$item])){
			/*
			If the $_GET item is an array, process each item independently, and return array of sanitized items.
			*/
			if (is_array($_GET[$item])){
				$getItems=array();
				foreach ($_GET[$item] as $getItem){
					$getItems[]=self::sanitize($getItem);
				}
				return $getItems;
			}else{
				return self::sanitize($_GET[$item]);
			}
		}
		return '';
	}

	public static function sanitize($string){
		return trim(htmlentities($string, ENT_QUOTES, 'UTF-8'));
	}
}
