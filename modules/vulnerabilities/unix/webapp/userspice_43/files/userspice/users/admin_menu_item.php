<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/
?>
<?php require_once 'init.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/header.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/navigation.php'; ?>

<?php if (!securePage($_SERVER['PHP_SELF'])){die();}

$errors = [];
$successes = [];

if (Input::exists('get')) {
	$menuId=Input::get('id');
	if (is_numeric($menuId) && $menuId>=0) {
		/*
		This is a valid ID so grab the record
		*/
		$item_results = $db->query("SELECT * FROM menus WHERE id=?",[$menuId]);
		$item = $item_results->first();
	}
}

if (!$item) {
    Redirect::to('admin_menu.php?menu_title='.Input::get('menu_title').'&err=This+menu+item+does+not+exist.');
}

if (Input::exists('post')) {
    # Update the db with the new values
    $fields=array(
        'menu_title'=>$item->menu_title,
        'parent'=>Input::get('parent'),
        'dropdown'=>Input::get('dropdown'),
        #'perm_level'=>Input::get('perm_level'),
        'logged_in'=>Input::get('logged_in'),
        'display_order'=>Input::get('display_order'),
        'label'=>Input::get('label'),
        'link'=>Input::get('link'),
        'icon_class'=>Input::get('icon_class')
    );
    if ($db->update('menus',$menuId,$fields)) {
			//dump(Input::get('authorized_groups'));
        updateGroupsMenus((Input::get('authorized_groups')), $item->id);
				logger($user->data()->id,"Menu Manager","Updated $menuId");
        Redirect::to('admin_menu.php?menu_title='.$item->menu_title.'&msg=Menu+item+updated');
    }
    else {
        Redirect::to('admin_menu.php?menu_title='.$item->menu_title.'&err=Unable+to+update+menu+item.');
    }
}

/*
Grab all records which are marked as dropdowns
*/
$dropdown_results = $db->query("SELECT * FROM menus WHERE menu_title=? AND dropdown=1",[$item->menu_title]);
$dropdowns = $dropdown_results->results();

/*
Get permission levels and names
*/
$allGroups = array_merge([(object)['id'=>0, 'name'=>'Unrestricted Access']], fetchAllPermissions());
$authorizedGroups = array();
foreach (fetchGroupsByMenu($menuId) as $g) {
	$authorizedGroups[] = $g->group_id;
}

//dump($dropdowns);



?>

<div id="page-wrapper">
<div class="container">
<div class="row">
	<div class="col-xs-12">
	<h2>Menu Item</h2>

	<form name='edit_menu_item' action='admin_menu_item.php?id=<?=$menuId?>&action=edit' method='post'>

		<div class="form-group">
			<label>Parent</label>
			<select class="form-control" name="parent">
				<option value="-1" <?php if ($item->parent == -1) echo 'selected="selected"'; ?> >No Parent</option>
				<?php
				foreach ($dropdowns as $dropdown) {
				?>
					<option value="<?=$dropdown->id?>" <?php if ($item->parent == $dropdown->id) echo 'selected="selected"'; ?> ><?=$dropdown->label?></option>
				<?php
				}
				?>
			</select>
		</div>

		<div class="form-group">
			<label>Dropdown</label>
			<select class="form-control" name="dropdown">
				<option value="1" <?php if ($item->dropdown == 1) echo 'selected="selected"'; ?> >Yes</option>
				<option value="0" <?php if ($item->dropdown == 0) echo 'selected="selected"'; ?> >No</option>
			</select>
		</div>

		<div class="form-group">
			<label>Authorized Groups:</label>
				<?php
				foreach ($allGroups as $group) { ?>
					<label><input type="checkbox" name="authorized_groups[<?=$group->id?>]" value="<?=$group->id?>"
					<?php if (in_array($group->id, $authorizedGroups)) {
						echo "checked=\"checked\" ";
					}
					echo "/> {$group->name}</label>";
				}
				?>
			</select>
		</div>

		<div class="form-group">
			<label>User must be logged in</label>
			<select class="form-control" name="logged_in">
				<option value="1" <?php if ($item->logged_in == 1) echo 'selected="selected"'; ?> >Yes</option>
				<option value="0" <?php if ($item->logged_in == 0) echo 'selected="selected"'; ?> >No</option>
			</select>
		</div>

		<div class="form-group">
			<label>Display Order</label>
			<input  class='form-control' type='text' name='display_order' value='<?=$item->display_order?>' />
		</div>

		<div class="form-group">
			<label>Label</label>
			<input  class='form-control' type='text' name='label' value='<?=$item->label?>' />
		</div>

		<div class="form-group">
			<label>Link</label>
			<input  class='form-control' type='text' name='link' value='<?=$item->link?>' />
		</div>

		<div class="form-group">
			<label>Icon Class (<a href="http://fontawesome.io/icons/" target="_blank">options</a>)</label>
			Be sure to add <font color="red">fa fa-fw </font> before the shortcode to display properly.
			<input  class='form-control' type='text' name='icon_class' value='<?=$item->icon_class?>' />
		</div>

		<input type="hidden" name="csrf" value="<?=Token::generate();?>" />

		<p class="text-center"><input class='btn btn-primary' name='update' type='submit' value='Update' class='submit' />
		<a class="btn btn-info" href="admin_menu.php?menu_title=<?=$item->menu_title?>">Cancel</a></p>

	</form>


</div>
</div>
</div>
</div>

<!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
