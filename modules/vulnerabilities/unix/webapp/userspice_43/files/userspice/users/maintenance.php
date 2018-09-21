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
require_once 'init.php';
require_once $abs_us_root.$us_url_root.'users/includes/header.php';
require_once $abs_us_root.$us_url_root.'users/includes/navigation.php';
//if (!securePage($_SERVER['PHP_SELF'])){die();}

$settingsQ = $db->query("SELECT * FROM settings");
$settings = $settingsQ->first();
if($settings->site_offline==0) Redirect::to('index.php');
?>
<!-- Do what you want here -->
<div id="page-wrapper">
	<div class="container-fluid">

<div class="row">
	<div class="col-xs-12">
		<div class="jumbotron">

			<div class="col-xs-12 col-md-2">
				<h1 style="font-size: 140px; color: #FFD700; text-align:center"><i class="glyphicon glyphicon-warning-sign"></i></h1>
			</div>

			<div class="col-xs-12 col-md-10">

				<article>

				    <h3>We&rsquo;ll be back soon!</h3>

				        <p>Sorry for the inconvenience but we&rsquo;re performing some maintenance at the moment.<br> We&rsquo;ll be back online shortly!</p>
				        <p>&mdash; The <?=$settings->site_name?> Team</p>

				</article>
			</div>

			<div class="clearfix"></div>

		</div>
	</div>
</div>

</div> <!-- /container -->

</div> <!-- /#page-wrapper -->

<!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->


<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
