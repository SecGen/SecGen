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

require_once '../init.php';
$db = DB::getInstance();

$user_id = $user->data()->id;
if ($dayLimitQ = $db->query('SELECT notif_daylimit FROM settings', array())) $dayLimit = $dayLimitQ->results()[0]->notif_daylimit;
else $dayLimit = 7;
$notifications = new Notification($user_id, false, $dayLimit);

if (isset($user) && $user->isLoggedIn() && $_POST['user_id'] == $user->data()->id) {

	//Each element of the $id_array should be an integer.  If not, this will cause its value to be 0 which will throw an error below.
	foreach($_POST['id_array'] as $notif_id_check){
		$notif_id = (int)$notif_id_check;
		
		if($notif_id == 0){
			echo json_encode(array(
				'status' => 'error',
				'error_info' => 'It appears your request to dismiss the notification(s) failed.  Please try again.'
			));
		}
	}
    
	//Each element of the $id_array IS an integer.  So, process the data.
	foreach($_POST['id_array'] as $notif_id){
		$notifications->setRead($notif_id);
	}

	
	$new_notif_count = $notifications->getLiveUnreadCount();
	
	echo json_encode(array(
		'status' => 'success',
		'num_new_notif' => $new_notif_count
	));
	
} else {
	return false;
}
