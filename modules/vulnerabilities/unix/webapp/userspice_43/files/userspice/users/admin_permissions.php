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
$permission_exempt = array(1,2);
$errors = [];
$successes = [];

//Forms posted
if(!empty($_POST))
{
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include('../usersc/scripts/token_error.php');
  }

/*  if(!empty($_POST['delete'])){
      $deletions = $_POST['delete'];
      if ($deletion_count = deletePermission($deletions)){
        $successes[] = lang("PERMISSION_DELETIONS_SUCCESSFUL", array($deletion_count));
      }
      else {
        $errors[] = lang("SQL_ERROR");
            }
    }*/ //LEGACY BA 9162017

  //Create new permission level
  if(!empty($_POST['name'])) {
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
          $db->insert('permissions',$fields);
          $successes[] = "Permission Updated";
          logger($user->data()->id,"Permissions Manager","Added Permission Level named $permission.");
  }else{

    }
  }
}


$permissionData = fetchAllPermissions(); //Retrieve list of all permission levels
$count = 0;
// dump($permissionData);
// echo $permissionData[0]->name;
?>
<div id="page-wrapper">

  <div class="container-fluid">

    <!-- Page Heading -->
    <div class="row">
      <div class="col-sm-12">
        
            <?php if(!$validation->errors()=='') {?><div class="alert alert-danger"><?=display_errors($validation->errors());?></div><?php } ?>
        <!-- Left Column -->
        <div class="class col-sm-3"></div>

        <!-- Main Center Column -->
        <div class="class col-sm-6">
          <!-- Content Goes Here. Class width can be adjusted -->


			<?php
			echo resultBlock($errors,$successes);
			?>
			<form name='adminPermissions' action='<?=$_SERVER['PHP_SELF']?>' method='post'>
			  <h2>Create a new permission group</h2>
			  <p>
				<label>Permission Name:</label>
				<input type='text' name='name' />  <input type="hidden" name="csrf" value="<?=Token::generate();?>" >

  			  <input class='btn btn-primary' type='submit' name='Submit' value='Add Permission' /><br><br>

  			</form>
			  </p>

			  <br>
			  <table class='table table-hover table-list-search'>
				<tr>
				  <?php /*<th>Delete</th> //LEGACY BA 9162017 */?><th>Permission Name</th>
				</tr>

				<?php
				//List each permission level
				foreach ($permissionData as $v1) {
				  ?>
				  <tr>
         <?php /*  <td><?php if(!in_array($permissionData[$count]->id,$permission_exempt)){?><input type='checkbox' name='delete[<?=$permissionData[$count]->id?>]' id='delete[<?=$permissionData[$count]->id?>]' value='<?=$permissionData[$count]->id?>'><?php } ?></td>//LEGACY BA 9162017 */?>

					<td><a href='admin_permission.php?id=<?=$permissionData[$count]->id?>'><?=$permissionData[$count]->name?></a></td>
				  </tr>
				  <?php
				  $count++;
				}
				?>

			  </table>

          <!-- End of main content section -->
        </div>

        <!-- Right Column -->
        <div class="class col-sm-1"></div>
      </div>
    </div>
	</div>
	</div>

    <!-- /.row -->

    <!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->
<script src="js/search.js" charset="utf-8"></script>

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
