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
$html = '';

if (isset($user) && $user->isLoggedIn()) {
    $user_id = $user->data()->id;
    if ($dayLimitQ = $db->query('SELECT notif_daylimit FROM settings', array())) $dayLimit = $dayLimitQ->results()[0]->notif_daylimit;
    else $dayLimit = 7;
    $notifications = new Notification($user_id, false, $dayLimit);

	//If $_POST['new_all'] is something other than "new" or "all", the user has manipulated it.
	if($_POST['new_all'] != 'new' && $_POST['new_all'] != 'all'){
		$html = '<div class="text-center btn-lg btn-danger" style="margin: 15px">There was an error retrieving your notifications.</div><br>';
	}

	if($_POST['new_all'] == 'new'){
		$get_notif_function_1 = 'getLiveUnreadCount';
		$get_notif_function_2 = 'getUnreadNotifications';
	} else {
		$get_notif_function_1 = 'getCount';
		$get_notif_function_2 = 'getNotifications';
	}


	if ($notifications->$get_notif_function_1() > 0) {
        $i = 1;

		foreach ($notifications->$get_notif_function_2() as $notif) {
			$id_array[] = $notif->id;

			$html .= '
				<div class="col-lg-12 panel-default notification-row" data-id="'. $i .'">
					<div id="notification_' . $notif->id . '" class="col-lg-12 list-group-item list-group-item-action notif-style">
						<div class="row">
							<div class="col-lg-9">
			';

			//if ($notif->is_read == 0) $html .= '<span class="badge badge-notif" style="float: none; padding-right: 3px;">NEW</span> ';

			$html .= html_entity_decode($notif->message);
			$html .= '</div>'; //<div class="col-lg-9">

			if($_POST['new_all'] == 'new'){
				$html .='
							<div class="col-lg-3 small text-center">
								<a href="#" onclick="dismissNotif([' . $notif->id . '])" style="text-decoration: none; font-size: 24px"><i class="fa fa-window-close" aria-hidden="true" data-html="true" data-toggle="tooltip" data-placement="top" title="Dismiss<br>Notification"></i></a>
								<br>
								('.time2str($notif->date_created).')
							</div>
				';
			} else {
				$html .='
							<div class="col-lg-3 small text-center">
								('.time2str($notif->date_created).')
							</div>
				';
			}

			$html .= '</div>'; //Ending <div class="row">
			$html .= '</div>'; //Ending <div id="notification_' . $notif->id . '" class="col-lg-12 list-group-item list-group-item-action notif-style">
			$html .= '</div>'; //Ending <div class="col-lg-12 panel-default notification-row" data-id="'. $i .'">
            $i++;
        }

		$totalPages = ceil($notifications->$get_notif_function_1() / 5);
        if ($totalPages > 1) {
            $html .= '<div class="text-center" id="notif_pagination"><ul class="pagination" id="notif-pagination">';
            if ($totalPages > 5) $html .= '<li class="first disabled"><a href="#"><<</a></li>';
            for ($i=1; $i<=$totalPages; $i++) {
                $active = '';
                if ($i == 1) $active = ' class="active"';
                $html .= '<li '.$active.'><a href="#">'.$i.'</a></li>';
            }
            if ($totalPages > 5) $html .= '<li class="last"><a href="#">>></a></li>';
            $html .= '</ul></div>';
        }


		$id_array = implode(',', $id_array);

		if($_POST['new_all'] == 'new'){
			$html .= '
				<div class="col-lg-12 text-center" id="mark_all_notif">
					<br>
					<button onclick="dismissNotif([' . $id_array . '])" class="btn btn-block btn-primary">Mark all notifications as read and dismiss.</button>
				</div>
			';
		}

		$html .= '
			<div class="col-lg-6 text-center" style="padding-bottom: 15px">
				<br>
				<button onclick="displayNotifications(\'new\')" class="btn btn-block btn-primary">Only New Notifications</button>
			</div>
			<div class="col-lg-6 text-center" style="padding-bottom: 15px">
				<br>
				<button onclick="displayNotifications(\'all\')" class="btn btn-block btn-primary">Show All Notifications</button>
			</div>
		';

		$html .= '
			<script>
			$(document).ready(function(){
				$(\'[data-toggle="tooltip"]\').tooltip();
			});
			</script>
		';
    } else {
		if($_POST['new_all'] == 'new'){
			$html .= '<div class="text-center btn-lg btn-info" style="margin: 15px 15px -20px 15px">You have no new notifications at this time.</div><br>';
			$html .= '
				<div class="col-lg-12 text-center" style="padding-bottom: 15px">
					<br>
					<button onclick="displayNotifications(\'all\')" class="btn btn-block btn-primary">Show All Notifications</button>
				</div>
			';
		} else {
			$html .= '<div class="text-center btn-lg btn-info" style="margin: 15px 15px -20px 15px">You have no notifications at this time.</div><br><br>';
		}
    }

    if ($notifications->getError() != '') $html = $notifications->getError();
}
else return false;

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($html);
    exit;
}
