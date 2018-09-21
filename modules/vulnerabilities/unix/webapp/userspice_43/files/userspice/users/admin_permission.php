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
$validation = new Validate();
//PHP Goes Here!
$permissionId = Input::get('id');
$permission_exempt = array(1,2);
$errors = [];
$successes = [];

//Check if selected permission level exists
if(!permissionIdExists($permissionId)){
Redirect::to("admin_permissions.php"); die();
}

//Fetch information specific to permission level
$permissionDetails = fetchPermissionDetails($permissionId);
//Forms posted
if(!empty($_POST)){
  $token = $_POST['csrf'];
	if(!Token::check($token)){
		include('../usersc/scripts/token_error.php');
	}

  //Delete selected permission level
  if(!empty($_POST['delete'])){
            if(!in_array($permissionId,$permission_exempt)){
      $deletions = $_POST['delete'];
      if ($deletion_count = deletePermission($deletions)){
        $successes[] = lang("PERMISSION_DELETIONS_SUCCESSFUL", array($deletion_count));
        $name = $permissionDetails['name'];
        logger($user->data()->id,"Permissions Manager","Deleted $name.");
        Redirect::to('admin_permissions.php?msg=Permission+deleted.');
      }
      else {
        $errors[] = lang("SQL_ERROR");
            } }
    }
  else
  {
    //Update permission level name
    if($permissionDetails['name'] != $_POST['name']) {
      $permission = Input::get('name');
      $fields=array('name'=>$permission);
//NEW Validations
    $validation->check($_POST,array(
      'name' => array(
        'display' => 'Permission Name',
        'required' => true,
        'unique' => 'permissions',
        'min' => 1,
        'max' => 25
      )
    ));
    if($validation->passed()){
      $db->update('permissions',$permissionId,$fields);
      $successes[] = "Updated Permission Name";
      $name = $permissionDetails['name'];
      logger($user->data()->id,"Permissions Manager","Changed Permission Name from $name to $permission.");
    }else{
        }
      }

    //Remove access to pages
    if(!empty($_POST['removePermission'])){
      $remove = $_POST['removePermission'];
      if ($deletion_count = removePermission($permissionId, $remove)) {
        $successes[] = lang("PERMISSION_REMOVE_USERS", array($deletion_count));
        logger($user->data()->id,"Permission Manager","Deleted $deletion_count users(s) from Permission #$permissionId.");
      }
      else {
        $errors[] = lang("SQL_ERROR");
      }
    }

    //Add access to pages
    if(!empty($_POST['addPermission'])){
      $add = $_POST['addPermission'];
      if ($addition_count = addPermission($permissionId, $add)) {
        $successes[] = lang("PERMISSION_ADD_USERS", array($addition_count));
        logger($user->data()->id,"Permission Manager","Added $addition_count users(s) to Permission #$permissionId.");
      }
      else {
        $errors[] = lang("SQL_ERROR");
      }
    }

    //Remove access to pages
    if(!empty($_POST['removePage'])){
      $remove = $_POST['removePage'];
      if ($deletion_count = removePage($remove, $permissionId)) {
        $successes[] = lang("PERMISSION_REMOVE_PAGES", array($deletion_count));
        logger($user->data()->id,"Permission Manager","Deleted $deletion_count pages(s) from Permission #$permissionId.");
      }
      else {
        $errors[] = lang("SQL_ERROR");
      }
    }

    //Add access to pages
    if(!empty($_POST['addPage'])){
      $add = $_POST['addPage'];
      if ($addition_count = addPage($add, $permissionId)) {
        $successes[] = lang("PERMISSION_ADD_PAGES", array($addition_count));
        logger($user->data()->id,"Permission Manager","Added $addition_count pages(s) to Permission #$permissionId.");
      }
      else {
        $errors[] = lang("SQL_ERROR");
      }
    }
    $permissionDetails = fetchPermissionDetails($permissionId);
  }
}

//Retrieve list of accessible pages
$pagePermissions = fetchPermissionPages($permissionId);




  //Retrieve list of users with membership
$permissionUsers = fetchPermissionUsers($permissionId);
// dump($permissionUsers);

//Fetch all users
$userData = fetchAllUsers();


//Fetch all pages
$pageData = fetchAllPages();

?>

<div id="page-wrapper">

  <div class="container">

    <!-- Page Heading -->
    <div class="row">
      <div class="col-xs-12">

            <?php if(!$validation->errors()=='') {?><div class="alert alert-danger"><?=display_errors($validation->errors());?></div><?php } ?>
        <!-- Main Center Column -->

          <!-- Content Goes Here. Class width can be adjusted -->
          <h1>Configure Details for this Permission Level</h1>

		  <?php
			echo resultBlock($errors,$successes);
			?>

			<form name='adminPermission' action='<?=$_SERVER['PHP_SELF']?>?id=<?=$permissionId?>' method='post'>
							<input class='btn btn-primary' type='submit' value='Update Permission' class='submit' />
			<a class='btn btn-warning' href="admin_permissions.php">Cancel</a><br><br>
			<table class='table'>
			<tr><td>
			<h3>Permission Information</h3>
			<div id='regbox'>
			<p>
			<label>ID:</label>
			<?=$permissionDetails['id']?>
			</p>
			<p>
			<label>Name:</label>
			<input type='text' name='name' value='<?=$permissionDetails['name']?>' />
			</p>
			<h3>Delete this Level?</h3>
			<label>Delete:
        <input type='checkbox' name='delete[<?=$permissionDetails['id']?>]' id='delete[<?=$permissionDetails['id']?>]' value='<?=$permissionDetails['id']?>' <?php if(in_array($permissionId,$permission_exempt)){?>disabled<?php } ?> ></label>
			</p>
			</div></td><td>
			<h3>Permission Membership</h3>
			<div id='regbox'>
			<p><strong>
			Remove Members:</strong>
			<?php
			//Display list of permission levels with access
			$perm_users = [];
			foreach($permissionUsers as $perm){
			  $perm_users[] = $perm->user_id;
			}
			foreach ($userData as $v1){
			  if(in_array($v1->id,$perm_users)){ ?>
				<br><label class="normal"><input type='checkbox' name='removePermission[]' id='removePermission[]' value='<?=$v1->id;?>'> <?=$v1->username;?></label><?php
			}
			}
			?>

			</p><strong>
			<p>Add Members:</strong>
			<?php
			//List users without permission level
			$perm_losers = [];
			foreach($permissionUsers as $perm){
			  $perm_losers[] = $perm->user_id;
			}
			foreach ($userData as $v1){
				if(!in_array($v1->id,$perm_losers)){ ?>
				<br><label class="normal"><input type='checkbox' name='addPermission[]' id='addPermission[]' value='<?=$v1->id?>'> <?=$v1->username;?></label><?php
			}
			}
			?>

			</p>
			</div>
			</td>
			<td>
			<h3>Permission Access</h3>
			<div id='regbox'>

			<p><br><strong>
			Remove Access From This Level:</strong>
			<?php
			//Display list of pages with this access level
			$page_ids = [];
			foreach($pagePermissions as $pp){
			  $page_ids[] = $pp->page_id;
			}
			foreach ($pageData as $v1){
			  if(in_array($v1->id,$page_ids)){ ?>
				<br><label class="normal"><input type='checkbox' name='removePage[]' id='removePage[]' value='<?=$v1->id;?>'> <?=$v1->page;?></label>
			  <?php }
			}  ?>
			</p>
			<p><br><strong>
			Add Access To This Level:</strong>
			<?php
			//Display list of pages with this access level

			foreach ($pageData as $v1){
				if($settings->page_permission_restriction == 1) {
					$countQ = $db->query("SELECT id, permission_id FROM permission_page_matches WHERE page_id = ? ",array($v1->id));
				$countCountQ = $countQ->count();
			  if(!in_array($v1->id,$page_ids) && $v1->private == 1 && !$countCountQ >=1){ ?>
				<br><label class="normal"><input type='checkbox' name='addPage[]' id='addPage[]' value='<?=$v1->id;?>'> <?=$v1->page;?></label>
				<?php } } else {
			  if(!in_array($v1->id,$page_ids) && $v1->private == 1){ ?>
				<br><label class="normal"><input type='checkbox' name='addPage[]' id='addPage[]' value='<?=$v1->id;?>'> <?=$v1->page;?></label>
				<?php } }
			}  ?>


			</p>
			<?php if($settings->page_permission_restriction == 1) { ?>
			<p><br><strong>Private - Cannot Be Assigned:</strong>
			<?php
			//Display list of pages with this access level

			foreach ($pageData as $v1){
					$countQ = $db->query("SELECT id, permission_id FROM permission_page_matches WHERE page_id = ? ",array($v1->id));
				$countCountQ = $countQ->count();
			  if(!in_array($v1->id,$page_ids) && $v1->private == 1 && $countCountQ >=1){ ?><br><?=$v1->page;?> (<?php if($countCountQ > 1) {?>Multiple<?php } else { ?><a href="admin_page.php?id=<?=$v1->id?>" style="text-decoration:none;"><?=fetchPermissionDetails($countQ->first()->permission_id)['name']?></a><?php } ?>)
				<?php } }  ?>


			</p> <?php } ?>
			<p><br><strong>
			Public Pages:</strong>
			<?php
			//List public pages
			foreach ($pageData as $v1) {
			  if($v1->private != 1){
				?><br><a href="admin_page.php?id=<?=$v1->id?>" style="text-decoration:none;"><?=$v1->page?></a>
			 <?php  }
			}
			?>
			</p>
			</div>
			</td>
			</tr>
			</table>

			<input type="hidden" name="csrf" value="<?=Token::generate();?>" >

			<p>
			<label>&nbsp;</label>
			</p>
			</form>



          <!-- End of main content section -->
      </div>
    </div>
	</div>
	</div>

    <!-- /.row -->
    <!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

    <!-- Place any per-page javascript here -->

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
