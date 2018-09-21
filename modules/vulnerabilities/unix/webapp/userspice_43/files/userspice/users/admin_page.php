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
//PHP Goes Here!
$pageId = Input::get('id');
$errors = [];
$successes = [];

//Check if selected pages exist
if(!pageIdExists($pageId)){
  Redirect::to("admin_pages.php"); die();
}

$pageDetails = fetchPageDetails($pageId); //Fetch information specific to page


//Forms posted
if(Input::exists()){
	$token = Input::get('csrf');
	if(!Token::check($token)){
		include('../usersc/scripts/token_error.php');
	}
	$update = 0;

	if(!empty($_POST['private'])){
		$private = Input::get('private');
	}

  if(!empty($_POST['re_auth'])){
    $re_auth = Input::get('re_auth');
          }
  	//Toggle private page setting
   	if (isset($private) AND $private == 'Yes'){
   		if ($pageDetails->private == 0){
   			if (updatePrivate($pageId, 1)){
   				$successes[] = lang("PAGE_PRIVATE_TOGGLED", array("private"));
          logger($user->data()->id,"Pages Manager","Changed private from public to private for Page #$pageId.");
   			}else{
   				$errors[] = lang("SQL_ERROR");
   			}
   	  }
   	}elseif ($pageDetails->private == 1){
   		if (updatePrivate($pageId, 0)){
   			$successes[] = lang("PAGE_PRIVATE_TOGGLED", array("public"));
        logger($user->data()->id,"Pages Manager","Changed private from private to public for Page #$pageId and stripped re_auth.");
   		}else{
   		$errors[] = lang("SQL_ERROR");
   		}
   	}


  //Toggle reauth setting
  if($pageDetails->private==1 && $pageDetails->page != "users/admin_verify.php" && $pageDetails->page != "usersc/admin_verify.php") {
	if (isset($re_auth) AND $re_auth == 'Yes'){
		if ($pageDetails->re_auth == 0){
			if (updateReAuth($pageId, 1)){
				$successes[] = lang("PAGE_REAUTH_TOGGLED", array("requires"));
        logger($user->data()->id,"Pages Manager","Changed re_auth from No to Yes for Page #$pageId.");
			}else{
				$errors[] = lang("SQL_ERROR");
			}
		}
	}elseif ($pageDetails->re_auth == 1){
		if (updateReAuth($pageId, 0)){
			$successes[] = lang("PAGE_REAUTH_TOGGLED", array("does not require"));
      logger($user->data()->id,"Pages Manager","Changed re_auth from Yes to No for Page #$pageId.");
		}else{
			$errors[] = lang("SQL_ERROR");
		}
  } }

	//Remove permission level(s) access to page
	if(!empty($_POST['removePermission'])){
		$remove = $_POST['removePermission'];
		if ($deletion_count = removePage($pageId, $remove)){
			$successes[] = lang("PAGE_ACCESS_REMOVED", array($deletion_count));
      logger($user->data()->id,"Pages Manager","Deleted $deletion_count permission(s) from $pageDetails->page.");
		}else{
			$errors[] = lang("SQL_ERROR");
		}
	}

	//Add permission level(s) access to page
	if(!empty($_POST['addPermission'])){
		$add = $_POST['addPermission'];
		$addition_count = 0;
		foreach($add as $perm_id){
			if(addPage($pageId, $perm_id)){
				$addition_count++;
			}
		}
		if ($addition_count > 0 ){
			$successes[] = lang("PAGE_ACCESS_ADDED", array($addition_count));
      logger($user->data()->id,"Pages Manager","Added $addition_count permission(s) to $pageDetails->page.");
		}
	}

	//Changed title for page
	if($_POST['changeTitle'] != $pageDetails->title){
		$newTitle = $_POST['changeTitle'];
		if ($db->query('UPDATE pages SET title = ? WHERE id = ?', array($newTitle, $pageDetails->id))){
			$successes[] = lang("PAGE_RETITLED", array($newTitle));
            logger($user->data()->id,"Pages Manager","Retitled '{$pageDetails->page}' to '$newTitle'.");
		}else{
			$errors[] = lang("SQL_ERROR");
		}
	}
	$pageDetails = fetchPageDetails($pageId);
}
$pagePermissions = fetchPagePermissions($pageId);
$permissionData = fetchAllPermissions();
$countQ = $db->query("SELECT id, permission_id FROM permission_page_matches WHERE page_id = ? ",array($pageId));
$countCountQ = $countQ->count();
?>
<div id="page-wrapper">

  <div class="container">

    <!-- Page Heading -->
    <div class="row">


        <!-- Main Center Column -->
        <div class="col-xs-12">
          <!-- Content Goes Here. Class width can be adjusted -->

			<h2>Page Permissions </h2>
			<?php resultBlock($errors,$successes); ?>

			<form name='adminPage' action='<?=$_SERVER['PHP_SELF'];?>?id=<?=$pageId;?>' method='post'>
				<input type='hidden' name='process' value='1'>

			<div class="row">
			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-heading"><strong>Information</strong></div>
					<div class="panel-body">
						<div class="form-group">
						<label>ID:</label>
						<?= $pageDetails->id; ?>
						</div>
						<div class="form-group">
						<label>Name:</label>
						<?= $pageDetails->page; ?>
						</div>
					</div>
				</div><!-- /panel -->
			</div><!-- /.col -->

			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-heading"><strong>Public or Private?</strong></div>
					<div class="panel-body">
						<div class="form-group">
						<label>Private:
						<?php
						$checked = ($pageDetails->private == 1)? ' checked' : ''; ?>
						<input type='checkbox' name='private' id='private' value='Yes'<?=$checked;?>>
						</label></div>
            <?php if($pageDetails->private==1 && $pageDetails->page != "users/admin_verify.php" && $pageDetails->page != "usersc/admin_verify.php") {?>
            <label>Require ReAuth:
						<?php
						$checked1 = ($pageDetails->re_auth == 1)? ' checked' : ''; ?>
						<input type='checkbox' name='re_auth' id='re_auth' value='Yes'<?=$checked1;?>></label>
            <?php } ?>
					</div>
				</div><!-- /panel -->
			</div><!-- /.col -->

			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-heading"><strong>Remove Access</strong></div>
					<div class="panel-body">
						<div class="form-group">
						<?php
						//Display list of permission levels with access
						$perm_ids = [];
						foreach($pagePermissions as $perm){
							$perm_ids[] = $perm->permission_id;
						}
						foreach ($permissionData as $v1){
							if(in_array($v1->id,$perm_ids)){ ?>
							<label class="normal"><input type='checkbox' name='removePermission[]' id='removePermission[]' value='<?=$v1->id;?>'> <?=$v1->name;?></label><br/>
							<?php }} ?>
						</div>
					</div>
				</div><!-- /panel -->
			</div><!-- /.col -->

			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-heading"><strong>Add Access</strong></div>
					<div class="panel-body">
						<div class="form-group">
						<?php
						//Display list of permission levels without access
						foreach ($permissionData as $v1){
						if(!in_array($v1->id,$perm_ids)){ ?>
						<?php if($settings->page_permission_restriction == 0) {?><label class="normal"><input type='checkbox' name='addPermission[]' id='addPermission[]' value='<?=$v1->id;?>'> <?=$v1->name;?></label><br/><?php } ?>
						<?php if($settings->page_permission_restriction == 1) {?><label class="normal"><input type="radio" name="addPermission[]" id="addPermission[]" value="<?=$v1->id;?>" <?php if($countCountQ > 0 || $pageDetails->private==0) { ?> disabled<?php } ?>> <?=$v1->name;?></label><br/><?php } ?>
						<?php }} ?>
						</div>
					</div>
				</div><!-- /panel -->
			</div><!-- /.col -->
			</div><!-- /.row -->

            <div class="row">
                <div class="col-sm-6 col-sm-offset-3">
                    <div class="form-group">
                        <label for="title">Page Title:</label> <span class="small">(This is the text that's displayed on the browser's titlebar or tab)</small>
                        <input type="text" class="form-control" name="changeTitle" maxlength="50" value="<?= $pageDetails->title; ?>" />
                    </div>
                </div>
            </div>

			<input type="hidden" name="csrf" value="<?=Token::generate();?>" >
			<input class='btn btn-primary' type='submit' value='Update' class='submit' />
			<a class='btn btn-warning' href="admin_pages.php">Cancel</a><br><br>
			</form>
        </div>
    </div>
	</div>
</div>
    <!-- /.row -->
    <!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
