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

<?php if (!securePage($_SERVER['PHP_SELF'])){die();} ?>
<?php
$errors = $successes = [];
$form_valid=TRUE;
//Forms posted
if (!empty($_POST)) {
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include('../usersc/scripts/token_error.php');
  }

  if(!empty($_POST['addCron'])) {
  	$name = Input::get('name');
  	$file = Input::get('file');
	$sort = Input::get('sort');

    $form_valid=FALSE; // assume the worst
    $validation = new Validate();
    $validation->check($_POST,array(
      'name' => array(
      'display' => 'Name',
      'required' => true,
      'min' => 2,
      'max' => 35,
      ),
      'file' => array(
      'display' => 'File',
      'required' => true,
      'min' => 2,
      'max' => 35,
      ),
	  'sort' => array(
      'display' => 'Sort',
      'required' => true,
      ),
    ));
  	if($validation->passed()) {
		$form_valid=TRUE;
      try {
        $fields=array(
          'name' => Input::get('name'),
          'file' => Input::get('file'),
		  'sort' => Input::get('sort'),
          'createdby' => $user->data()->id,
        );
        $db->insert('crons',$fields);
			$successes[] = "Cron Added";
      logger($user->data()->id,"Cron Manager","Added cron named $name.");

		  } catch (Exception $e) {
			die($e->getMessage());
		  }

    }
} }
$query = $db->query("SELECT * FROM crons ORDER BY sort,active DESC,id ASC");
$count = $query->count();
?>

<div id="page-wrapper">
	<div class="container">
	<?=resultBlock($errors,$successes);?>
		<div class="row">
			<div class="page-wrapper">
        <?php if($settings->cron_ip == 'off'){echo "<strong>Your cron jobs are currently disabled by the system. With great power, comes the need for great responsibility. Please see the note at the bottom of this page.</strong>";} ?>
				<center><h1>Cron Manager <a href='cron/cron.php?from=users/cron_manager.php'><i class="glyphicon glyphicon-refresh"></i></a></h1></center>
				<div style="float: right; margin-bottom: 10px">
				<div class="btn-group"><button class="btn btn-info" data-toggle="modal" data-target="#addcron"><i class="glyphicon glyphicon-plus"></i> add</button></div>
				</div><br /><br /><br />
					<center>
					<div>
							<table class="table table-bordered">
							<tr>
							<tr>
								<th><center>Cron ID / Status</center></th>
								<th><center>Cron Name</center></th>
								<th><center>Cron File</center></th>
								<th><center>Sort</center></th>
								<th><center>Created By</center></th>
								<th><center>Last Ran</center></th>
								<th><center>Functions</center></th>
							</tr>
					 <?php
					if($count > 0)
					{
						foreach ($query->results() as $row){ ?>
								 <tr <?php if($row->active==0) {?> bgcolor="#CDCDCD"<?php } ?>>
									<td><center><?=$row->id;?>
									- <a href="#" data-name="active" id="active" class="active nounderline" data-type="select" value="<?=$row->active;?>" data-pk="<?=$row->id;?>" data-url="cron_post.php" data-title="Select Status for <?=$row->name;?>"><?php if($row->active==0) {?>Inactive<?php } if($row->active==1) {?>Active <?php } ?></a></center></td>
									<td><center><a href="#" data-name="name" class="name nounderline" data-type="text" data-pk="<?=$row->id;?>" data-url="cron_post.php" data-title="Rename Cron ID <?=$row->id;?>"><?=$row->name;?></a></center></td>
									<td><center><a href="#" data-name="file" class="file nounderline" data-type="text" data-pk="<?=$row->id;?>" data-url="cron_post.php" data-title="Change File for <?=$row->name;?>"><?=$row->file;?></a></center></td>
									<td><center><a href="#" data-name="sort" class="sort nounderline" data-type="text" data-pk="<?=$row->id;?>" data-url="cron_post.php" data-title="Change sort for <?=$row->name;?>"><?=$row->sort;?></a></center></td>
									<td><center><?=echousername($row->createdby);?></center></td>
									<td><center>
									<?php $ranQ = $db->query("SELECT datetime,user_id FROM crons_logs WHERE cron_id = ? ORDER BY datetime DESC",array($row->id));
											$ranCount = $ranQ->count();
										if($ranCount > 0) {
											$ranResult = $ranQ->first();?>
										<?=$ranResult->datetime;?> (<?=echousername($ranResult->user_id);?>)<?php } else { ?><i>Never</i><?php } ?></center></td>
									<td><?php if($row->active==1) {?><center><a href="cron/<?=$row->file;?>?from=users/cron_manager.php"><i class="glyphicon glyphicon-refresh"></i></a></center><?php } ?></td>
								</tr><?php
					} }
					else
					 { ?>
						 <tr><td colspan='7'><center>No Cron Jobs</center></td></tr>
					 <?php }
					 ?>
					 </table>
					</div>
					</center>
					<br />
			</div> <!-- /.page-wrapper -->

<div id="addcron" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Cron Addition</h4>
      </div>
      <div class="modal-body">
<form class="form-signup" action="" method="POST" id="payment-form">
	<div class="panel-body">

	<label>Cron Name: </label><input type="text" class="form-control" id="name" name="name" placeholder="Cron Name" required>

	<label>File: </label><input type="text" class="form-control" id="file" name="file" placeholder="File (include type, e.g. .php) within the cron folder only" required>

	<label>Sort: </label><input type="text" class="form-control" id="sort" name="sort" placeholder="3 digit sort code, crons run by this order, eg 100, 101, 102" required>
	<br />
      </div>
      <div class="modal-footer">
	  <div class="btn-group">	<input type="hidden" name="csrf" value="<?=Token::generate();?>" />
	<input class='btn btn-info' type='submit' name="addCron" value='Add Cron' class='submit' /></div>
	</form>
         <div class="btn-group"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
      </div>
    </div>
	</div>
  </div>
</div>
<?php if($settings->cron_ip == 'off'){ ?>
A cron job is an automated task which allows you to perform powerful tasks without your interaction.  Before implementing cron jobs,
you want to do some thinking about security.  In almost all circumstances, you do not want someone to be able to type yourdomain.com/cron/cron.php
and run a bunch of commands on your server.<br><br>

The recommended way of implementing cron jobs is...<br>
Step 1: Go into your server and set your cron job to fire off to yourdomain.com/cron/cron.php every few minutes.<br>
Step 2: Go into <a href="admin_logs.php">the system logs</a> and see which ip address was rejected for trying to do a cron job.<br>
Step 3: Then go into <a href="admin.php?tab=2#cron">the admin dashboard"</a> and set that IP address in the 'Only allow cron jobs from the following IP' box.<br>
Step 4: Go back into your server and set your cron job for a more reasonable amount of time. Most server admins don't want you running cron jobs every few minutes. Every hour or even every day is more reasonable.
<?php } ?>
		</div> <!-- /.row -->
	</div> <!-- /.container -->
</div> <!-- /.wrapper -->


	<!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->
<script src="js/jwerty.js"></script>
<script src="js/bootstrap-editable.js"></script>
<script type="text/javascript">
$.fn.editable.defaults.mode = "inline"
$(document).ready(function() {
    $('.name').editable();
	$('.active').editable();
	$('.file').editable();
	$('.sort').editable();
});
$(".active").editable({
  value: "bar", // The option with this value should be selected
  source: [
    {value: "1", text: "Active"},
    {value: "0", text: "Inactivate"},
  ]
});
</script>

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
