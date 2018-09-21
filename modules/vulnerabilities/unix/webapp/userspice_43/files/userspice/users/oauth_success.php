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
<?php
require_once 'init.php';
require_once $abs_us_root.$us_url_root.'users/includes/header.php';
require_once $abs_us_root.$us_url_root.'users/includes/navigation.php';
?>
<div id="page-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<h3 align="center">You have successfully logged in...redirecting now.</h3>
				<?php require_once $abs_us_root.$us_url_root.'usersc/includes/oauth_success_redirect.php';?>
				<?=Redirect::to('account.php'); ?>
			</div>
		</div>
	</div>
</div>
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; ?>
