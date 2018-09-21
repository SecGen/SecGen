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

if (Input::exists('get')) {

	$menu_title=Input::get('menu_title');
	/*
	*
	* DANGER - the menu_title is passed in and can be corrupted - great source for injection!!!
	*
	*/

	if (!empty($_GET['action'])) {
		$action=Input::get('action');

		if ($action=='newDropdown') {
			/*
			Inserts default "dropdown" entry
			*/
			$fields=array('menu_title'=>$menu_title,'parent'=>'-1','dropdown'=>'1','logged_in'=>'1','display_order'=>'99999','label'=>'New Dropdown','link'=>'#','icon_class'=>'');
			$db->insert('menus',$fields);
			logger($user->data()->id,"Menu Manager","Added new dropdown");
		} elseif ($action=='newItem') {
			/*
			Inserts default "item" entry
			*/
            $fields=array('menu_title'=>$menu_title,'parent'=>'-1','dropdown'=>'0','logged_in'=>'1','display_order'=>'99999','label'=>'New Item','link'=>'#','icon_class'=>'');
            $db->insert('menus',$fields);
						logger($user->data()->id,"Menu Manager","Added new item");
		} elseif ($action=='delete' && isset($_GET['id'])) {
            $itemId=Input::get('id');
            if (is_numeric($itemId)) {
                $db->deleteById('menus',$itemId);
								logger($user->data()->id,"Menu Manager","Deleted menu $itemId");
                Redirect::to('admin_menu.php?menu_title='.$menu_title);
            }
            else {
                Redirect::to('admin_menu.php?menu_title='.$menu_title.'&err=This+menu+item+does+not+exist.');
            }
		} else {
			Redirect::to('admin_menu.php?menu_title='.$menu_title);
		}
	}
	/*
	Query requested menu_title
	*/
	$menu_item_results = $db->query("SELECT * FROM menus WHERE menu_title=? ORDER BY display_order",[$menu_title]);
	$menu_items = $menu_item_results->results();
}

if (!$menu_items) {
    //Redirect::to('admin_menus.php?err=This+menu+does+not+exist.');
}

/*
Make indented tree
*/
$indentedMenuItems=prepareIndentedMenuTree($menu_item_results->results(true));
//dump($indentedMenuItems);
/*
$menu_items will contain array of associative arrays
*/
$menu_items_array=$indentedMenuItems;

/*
foreach below will loop through array and build array of objects from the associative arrays
*/
$menu_items=[];
foreach ($menu_items_array as $menu_item) {
	$menu_items[]=(object)$menu_item;
}

/*
Grab all records which are marked as dropdowns/parents
*/
$parent_results = $db->query("SELECT * FROM menus WHERE menu_title=? AND dropdown=1",[$menu_title]);
$parents = $parent_results->results();
$parentsSelect[-1]='No Parent';
foreach ($parents as $parent) {
	$parentsSelect[$parent->id]=$parent->label;
}

/*
Get groups and names
*/
// $allGroups = fetchAllGroups();
// $groupsSelect[0]='Unrestricted';
// foreach ($allGroups as $group) {
// 	$groupsSelect[$group->id]=$group->name;
// }

?>
<script type="text/javascript" src="<?=$us_url_root?>users/js/oce.js"></script>
<div id="page-wrapper">
<div class="container">
<div class="row">
	<div class="col-xs-12">
		<div id="msg" class="bg-info text-info"></div>
	<h2><?=$menu_title?> Menu</h2>
	<p class="text-center">
	<a href="admin_menu.php?menu_title=<?=$menu_title?>&action=newDropdown" class="btn btn-primary" role="button">New Dropdown</a>
	<a href="admin_menu.php?menu_title=<?=$menu_title?>&action=newItem" class="btn btn-primary" role="button">New Item</a>
	<!-- <a href="admin_menu.php?menu_title=<?=$menu_title?>&action=renumberOrder" class="btn btn-primary" role="button">Renumber Order</a> -->
	<a href="admin_menu.php?menu_title=<?=$menu_title?>" class="btn btn-primary" role="button">Refresh</a>
	</p>
	<div class="table-responsive">
		<table class="table table-bordered table-hover table-condensed">
			<thead><tr><th>ID</th><th>Label</th><th>Parent</th><th>Link*</th><th>Dropdown*</th><th>Authorized Groups</th><th>Logged In*</th><th>Display Order*</th><th>Icon Class*</th><th>Action</th></tr>*Click to edit</thead>
			<tbody>
			<?php
			$i=0;
			$itemCount=sizeof($menu_items);
			foreach ($menu_items as $item) {
			?>
				<tr>
				<td><?=$item->id?></td>

				<td><?=(($item->indent) ? '>>> ' : '').$item->label?></td>
				<td><?=$parentsSelect[$item->parent]?></td>
				<td><p class="oce" data-id="<?=$item->id?>" data-field="link" data-input="input"><?=$item->link?></p></td>

				<td><p class="oce" data-id="<?=$item->id?>" data-field="dropdown" data-input="select"><?=($item->dropdown) ? 'Yes' : 'No';?></p></td>
				<td>
				<?php
				$sep = '';
				foreach (fetchGroupsByMenu($item->id) as $g) {
					#var_dump($g);
					echo $g->group_id.",";

				}
				?>
			  </td>
				<td><p class="oce" data-id="<?=$item->id?>" data-field="logged_in" data-input="select"><?=($item->logged_in) ? 'Yes' : 'No';?></p></td>
				<td><p class="oce" data-id="<?=$item->id?>" data-field="display_order" data-input="input"><?=$item->display_order?></p</td>


				<td><?=$item->icon_class?></td>
				<td>
					<a href="admin_menu_item.php?menu_title=<?=$menu_title?>&id=<?=$item->id?>&action=edit"><span class="fa fa-cog fa-lg"></span></a> /
					<a href="admin_menu.php?menu_title=<?=$menu_title?>&id=<?=$item->id?>&action=delete"><span class="fa fa-remove fa-lg"></span></a></td>
				</tr>
			<?php
			$i++;
			}
			?>
			</tbody>
		</table>
	</div>
</div>
</div>
</div>
</div>

<!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>
<script>
	function oceSuccess(data) {
		var r = JSON.parse(data);
		jQuery('#msg').html(r.msg);
    jQuery('#msg').html(r.msg);

	}
</script>

<script>
	var oceOpts = {
		url:'<?=$us_url_root?>users/parsers/editMenu.php',
		selectOptions : {'0':'No','1':'Yes'},
    allowNull : true}
	jQuery('.oce').oneClickEdit(oceOpts, oceSuccess);
</script>
<!-- Place any per-page javascript here -->


<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
