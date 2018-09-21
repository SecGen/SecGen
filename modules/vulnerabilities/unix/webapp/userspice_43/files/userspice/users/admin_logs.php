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
<?php require_once 'init.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/header.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/navigation.php'; ?>
<?php if (!securePage($_SERVER['PHP_SELF'])){die();} ?>
<?php
$get_info_id = $user->data()->id;
$userdetails = fetchUserDetails(NULL, NULL, $get_info_id);
//Errors Successes
$errors = [];
$successes = [];
 //Forms posted
if(!empty($_POST)) {
	$post_user_id = Input::get('post_user_id');
	$post_type = Input::get('post_type');
		if(!empty($post_user_id)) {
			Redirect::to('admin_logs.php?user_id='.$post_user_id);
		}
		elseif(!empty($post_type)) {
			Redirect::to('admin_logs.php?type='.$post_type);
		}
		else {
		Redirect::to('admin_logs.php'); }
	}

$user_id = Input::get('user_id');
$type = Input::get('type');
if(!empty($user_id)) {
	$countQ = $db->query("SELECT * FROM logs WHERE user_id = ?",array($user_id));
	$other = "&user_id=$user_id";
}
elseif(!empty($type)) {
	$countQ = $db->query("SELECT * FROM logs WHERE logtype = ?",array($type));
	$other = "&type=$type";
}
else {
	$countQ = $db->query("SELECT * FROM logs WHERE logtype NOT IN (SELECT name FROM logs_exempt)");
	$other = "";
}
if(!empty($user_id)) {
		$fuQ = $db->query("SELECT * FROM logs WHERE user_id = ? ORDER BY logdate DESC, id DESC",array($user_id));
}
elseif(!empty($type)) {
	$fuQ = $db->query("SELECT * FROM logs WHERE logtype = ? ORDER BY logdate DESC, id DESC LIMIT",array($type));
}
else {
		$fuQ = $db->query("SELECT * FROM logs WHERE logtype NOT IN (SELECT name FROM logs_exempt) ORDER BY logdate DESC, id DESC");
}
$fuCount = $fuQ->count();
?>
<div id="page-wrapper">
	<div class="container">
		<div class="row">
			<div class="col-xs-12">
				<h1>System Logs <a href="#" data-toggle="modal" data-target="#userfilter" class="show-tooltip" title="Filter by User"><i class="glyphicon glyphicon-user"></i></a>
				<a href="#" data-toggle="modal" data-target="#datafilter" class="show-tooltip" title="Filter by Type"><i class="glyphicon glyphicon-book"></i></a>
				<?php if(!empty($user_id) || !empty($type)) {?><a href="admin_logs.php" class="show-tooltip" title="Reset Filter"><i class="glyphicon glyphicon-refresh"></i></a><?php } ?>
				<a href="admin_logs_manager.php" class="show-tooltip" title="Logs Manager"><i class="glyphicon glyphicon-cog"></i></a>
			</h1>
				<?=resultBlock($errors,$successes);?>
				<hr>
				<table id="paginate" class='table table-hover table-list-search'>
					<thead>
						<th></th><th>Date</th><th>Type</th><th>User</th><th>Note</th>
					</thead>
					<tbody>
						<?php foreach ($fuQ->results() as $row){ ?>
							<tr><td><?=$row->id?></td>
								<td><?=echodatetime($row->logdate)?></td>
								<td><?=$row->logtype?></td>
								<td><?=echousername($row->user_id)?></td>
								<td><?=lognote($row->id)?></td>
								</tr>
								<?php }?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- Modal -->
		<div id="userfilter" class="modal" role="dialog">
		  <div class="modal-dialog">

		    <!-- Modal content-->
		    <div class="modal-content">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal">&times;</button>
		        <h4 class="modal-title">User Filter</h4>
		      </div>
		      <div class="modal-body">
		        <p>Please select the user:</p>
				<div class="form-group">
				<form class="inline-form" action="" method="POST" id="userForm">
				<select name="post_user_id" id="combobox" class="form-control combobox">
				<option readonly></option>
				<?php $userData = fetchAllUsers(); //Fetch information for all users
				foreach($userData as $v1) {?>
				<option value="<?=$v1->id;?>"><?=$v1->id;?>. (<?=$v1->username;?>) <?=$v1->fname;?> <?=$v1->lname;?></option>
				<?php } ?>
				</select><br />
				<div class="btn-group pull-right"><input class='btn btn-primary' type='submit' value='Filter' class='submit' /></div><br />
				</form>
				</div>
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		      </div>
		    </div>

		  </div>
		</div>

		<!-- Modal -->
		<div id="datafilter" class="modal" role="dialog">
		  <div class="modal-dialog">

		    <!-- Modal content-->
		    <div class="modal-content">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal">&times;</button>
		        <h4 class="modal-title">Type Filter</h4>
		      </div>
		      <div class="modal-body">
		        <p>Please select the type:</p>
				<div class="form-group">
				<form class="inline-form" action="" method="POST" id="dataForm">
				<select name="post_type" class="form-control combobox">
				<option readonly></option>
				<?php
				$typeQuery = $db->query("SELECT logtype FROM logs GROUP BY logtype");
				$typeCount = $typeQuery->count();
				if($typeCount > 0) {
					foreach ($typeQuery->results() as $results) {?>
					<option value="<?=$results->logtype?>"><?=$results->logtype?></option>
				<?php } } else {?>
				<option readonly>No Options Found</option>
				<?php } ?>
				</select><br />
				<div class="btn-group pull-right"><input class='btn btn-primary' type='submit' value='Filter' class='submit' /></div><br />
				</form>
				</div>
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		      </div>
		    </div>

		  </div>
		</div>

	</div>
	<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; ?>
	<script src="js/jwerty.js"></script>
	<script src="js/combobox.js"></script>
	<script>
	$(document).ready(function() {
		$('.show-tooltip').tooltip();

		$('.combobox').combobox();

		jwerty.key('ctrl+f1', function () {
				$('.modal').modal('hide');
				$('#userfilter').modal();
		});
		jwerty.key('ctrl+f2', function () {
				$('.modal').modal('hide');
				$('#datafilter').modal();
		});
		jwerty.key('esc', function () {
				$('.modal').modal('hide');
		});
		$('.modal').on('shown.bs.modal', function() {
				$('#combobox').focus();
		});
	    $('#paginate').DataTable({"pageLength": 25,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]], "aaSorting": []});
	} );
	</script>
	<script src="js/pagination/jquery.dataTables.js" type="text/javascript"></script>
	<script src="js/pagination/dataTables.js" type="text/javascript"></script>
	<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; ?>
