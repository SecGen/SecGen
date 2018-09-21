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
  if(!empty($_POST['addLog'])) {
  	$name = Input::get('name');

    $form_valid=FALSE; // assume the worst
    $validation = new Validate();
    $validation->check($_POST,array(
      'name' => array(
      'display' => 'Type',
      'required' => true,
      'min' => 2,
      'max' => 255,
      ),
    ));
  	if($validation->passed()) {
		$form_valid=TRUE;
      try {
        $fields=array(
          'name' => Input::get('name'),
          'createdby' => $user->data()->id,
          'created' => date("Y-m-d H:i:s")
        );
        $db->insert('logs_exempt',$fields);

		$logname=("System");
		$lognote=("Added Log Type Exemption for $name");
		logger($user->data()->id,$logname,$lognote);
			$successes[] = lang("ADDED_LOG");

		  } catch (Exception $e) {
			die($e->getMessage());
		  }

    }
} }
$query = $db->query("SELECT *,COUNT(*) AS count FROM logs GROUP BY logtype ORDER BY count DESC,logtype");
$count = $query->count();
?>

<div id="page-wrapper">
	<div class="container">
	<?=resultBlock($errors,$successes);?>
		<div class="row">
			<div class="page-wrapper">

				<h1>Logs Manager <a class="nounderline" href="admin_logs_manager.php"><i class="fa fa-fw fa-refresh"></i></a> <a class="nounderline" href="admin_logs.php"><i class="fa fa-fw fa-search"></i></a></h1>
        <hr>
					<center>
					<div>
							<table class="table table-bordered">
							<tr>
							<tr>
								<th><center>Log Type</center></th>
								<th><center>Count</center></th>
								<th><center>Exempted?</center></th>
								<th><center>Mapper Function</center></th>
							</tr>
					 <?php
					if($count > 0)
					{
						foreach ($query->results() as $row){ ?>
								 <tr>
									<td><center><?=$row->logtype;?></center></td>
									<td><center><?=$row->count;?></center></td>
									<?php $exempt = $db->query("SELECT name FROM logs_exempt WHERE name = ?",array($row->logtype));
									if($exempt->count() > 0) $exp = 1;
									else $exp = 0; ?>
									<td><center><a href="#" data-name="exempt" id="exempt" data-value="<?=$exp?>" class="exempt nounderline" data-mode="popup" data-type="select" data-pk="<?=$row->logtype;?>" data-url="admin_logs_exempt.php" data-title="Do you wish to exempt logs for <?=$row->logtype;?>?"><?php if($exempt->count() > 0) {?>Yes<?php } else {?> No<?php } ?></a></center></td>
									<td><center><a href="#" data-name="mapper" id="mapper" class="mapper nounderline" data-mode="popup" data-type="select" data-pk="<?=$row->logtype;?>" data-url="admin_logs_mapper.php" data-title="What would you like to map <?=$row->logtype;?> as?">Map</a></center></td>
								</tr><?php
					} }
					else
					 { ?>
						 <tr><td colspan='4'><center>No Logs</center></td></tr>
					 <?php }
					 ?>
					 </table>
					</div>
					</center>
					<br />
			</div> <!-- /.page-wrapper -->

<div id="addexemption" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Log Type Exemption Addition</h4>
      </div>
      <div class="modal-body">
<form class="form-signup" action="logsman.php" method="POST" id="payment-form">
	<div class="panel-body">

	<label>Log Type: </label>
	<select name="type" class="form-control combobox" required>
		<option readonly></option>
		<?php
		$typeQuery = $db->query("SELECT logtype FROM logs WHERE logtype NOT IN (SELECT name FROM logs_exempt) GROUP BY logtype");
		$typeCount = $typeQuery->count();
		if($typeCount > 0) {
			foreach ($typeQuery->results() as $results) {?>
			<option value="<?=$results->logtype?>"><?=$results->logtype?></option>
		<?php } } else {?>
		<option readonly>No Options Found</option>
		<?php } ?>
		</select>
	<br />
      </div>
      <div class="modal-footer">
	  <div class="btn-group">	<input type="hidden" name="csrf" value="<?=Token::generate();?>" />
	<input class='btn btn-info' type='submit' name="addLog" value='Add Exemption' class='submit' /></div>
	</form>
         <div class="btn-group"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
      </div>
    </div>
	</div>
  </div>
</div>

		</div> <!-- /.row -->
	</div> <!-- /.container -->
</div> <!-- /.wrapper -->


	<!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->
<script src="js/jwerty.js"></script>
<script src="js/bootstrap-editable.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $.fn.editable.defaults.mode = "inline"

    $(".exempt").editable({
      source: [
        {value: "1", text: "Yes"},
        {value: "0", text: "No"},
      ]
    });

    $(".mapper").editable({
      source: [
      <?php foreach($db->query("SELECT * FROM logs GROUP BY logtype ORDER BY logtype")->results() as $row) {?>
      {value: "<?=$row->logtype?>", text: "<?=$row->logtype?>"},<?php } ?>
      ]
    });
});
</script>

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
