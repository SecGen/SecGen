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
?>

<!-- Bootstrap Core JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<?php if (isset($user) && $user->isLoggedIn() && $settings->notifications == 1) {
require_once $abs_us_root.$us_url_root.'users/includes/notifications.php';
$not = $notifications->getUnreadCount();
if($settings->force_notif == 1 && $not > 0 && !isset($_SESSION['cloak_to'])){ ?>
  <script type="text/javascript">
  $(document).ready(function() {
    displayNotifications('new');
    $('#notificationsModal').modal('show');
    })
  </script>
<?php }
}?>
